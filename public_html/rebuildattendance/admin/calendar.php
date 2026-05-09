<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// Selected month
$selectedMonth = $_GET['month'] ?? date('Y-m');
$selectedUserId = $_GET['user_id'] ?? null;

// Parse month
$year  = (int)substr($selectedMonth, 0, 4);
$month = (int)substr($selectedMonth, 5, 2);
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$firstDayOfWeek = (int)date('w', mktime(0, 0, 0, $month, 1, $year)); // 0=Sun

// Get all users
$usersStmt = $pdo->query("SELECT id, name FROM users WHERE is_admin = 0 ORDER BY name ASC");
$allUsers = $usersStmt->fetchAll();

// Get attendance for selected month
$attendanceDates = [];
$totalPresent = 0;
if ($selectedUserId) {
    $stmt = $pdo->prepare("
        SELECT date FROM attendance 
        WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?
    ");
    $stmt->execute([$selectedUserId, $selectedMonth]);
    $attendanceDates = $stmt->fetchAll(PDO::FETCH_COLUMN);
    $totalPresent = count($attendanceDates);
}

// Determine days passed
$today = date('Y-m-d');
$isCurrentMonth = ($selectedMonth === date('Y-m'));
$daysPassed = $isCurrentMonth ? (int)date('j') : $daysInMonth;
$totalAbsent = $daysPassed - $totalPresent;

// Month navigation
$prevMonth = date('Y-m', mktime(0, 0, 0, $month - 1, 1, $year));
$nextMonth = date('Y-m', mktime(0, 0, 0, $month + 1, 1, $year));
$monthLabel = date('F Y', mktime(0, 0, 0, $month, 1, $year));

// Generate month options
$months = [];
for ($i = 0; $i < 12; $i++) {
    $m = date('Y-m', strtotime("-$i months"));
    $months[] = $m;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar — Admin FitTrack</title>
    <meta name="theme-color" content="#0f0f23">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page admin-page">
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item" id="nav-admin-dash">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="users.php" class="nav-item" id="nav-admin-users">
            <span class="nav-icon">👥</span>
            <span class="nav-label">Users</span>
        </a>
        <a href="calendar.php" class="nav-item active" id="nav-admin-calendar">
            <span class="nav-icon">📅</span>
            <span class="nav-label">Calendar</span>
        </a>
        <a href="../logout.php" class="nav-item" id="nav-admin-logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-label">Logout</span>
        </a>
    </nav>

    <div class="dashboard-container">
        <div class="page-header">
            <h1>📅 Attendance Calendar</h1>
        </div>

        <!-- Filters -->
        <div class="filter-bar calendar-filters">
            <select id="userSelect" class="select-input" onchange="updateCalendar()">
                <option value="">Select a user</option>
                <?php foreach ($allUsers as $u): ?>
                    <option value="<?= $u['id'] ?>" <?= $selectedUserId == $u['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($u['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select id="monthSelect" class="select-input" onchange="updateCalendar()">
                <?php foreach ($months as $m): ?>
                    <option value="<?= $m ?>" <?= $m === $selectedMonth ? 'selected' : '' ?>>
                        <?= date('F Y', strtotime($m . '-01')) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selectedUserId): ?>
            <!-- Stats -->
            <div class="stats-grid">
                <div class="stat-card accent-green">
                    <div class="stat-value"><?= $totalPresent ?></div>
                    <div class="stat-label">Present</div>
                </div>
                <div class="stat-card accent-red">
                    <div class="stat-value"><?= max(0, $totalAbsent) ?></div>
                    <div class="stat-label">Absent</div>
                </div>
                <div class="stat-card accent-blue">
                    <div class="stat-value"><?= $daysPassed > 0 ? round(($totalPresent / $daysPassed) * 100) : 0 ?>%</div>
                    <div class="stat-label">Rate</div>
                </div>
            </div>

            <!-- Calendar Grid -->
            <div class="calendar-card">
                <div class="calendar-header">
                    <a href="?user_id=<?= $selectedUserId ?>&month=<?= $prevMonth ?>" class="cal-nav">◀</a>
                    <h3><?= $monthLabel ?></h3>
                    <a href="?user_id=<?= $selectedUserId ?>&month=<?= $nextMonth ?>" class="cal-nav">▶</a>
                </div>

                <div class="calendar-grid">
                    <div class="cal-day-header">Sun</div>
                    <div class="cal-day-header">Mon</div>
                    <div class="cal-day-header">Tue</div>
                    <div class="cal-day-header">Wed</div>
                    <div class="cal-day-header">Thu</div>
                    <div class="cal-day-header">Fri</div>
                    <div class="cal-day-header">Sat</div>

                    <?php 
                    // Empty cells for days before month starts
                    for ($i = 0; $i < $firstDayOfWeek; $i++): ?>
                        <div class="cal-day empty"></div>
                    <?php endfor; ?>

                    <?php for ($d = 1; $d <= $daysInMonth; $d++):
                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $d);
                        $isPresent = in_array($dateStr, $attendanceDates);
                        $isFuture = $dateStr > $today;
                        $isToday = $dateStr === $today;
                        
                        $class = 'cal-day';
                        if ($isPresent) $class .= ' present';
                        elseif (!$isFuture) $class .= ' absent';
                        if ($isFuture) $class .= ' future';
                        if ($isToday) $class .= ' today';
                    ?>
                        <div class="<?= $class ?>">
                            <span class="cal-day-num"><?= $d ?></span>
                        </div>
                    <?php endfor; ?>
                </div>

                <div class="calendar-legend">
                    <span class="legend-item"><span class="legend-dot present"></span> Present</span>
                    <span class="legend-item"><span class="legend-dot absent"></span> Absent</span>
                    <span class="legend-item"><span class="legend-dot future"></span> Upcoming</span>
                </div>
            </div>

        <?php else: ?>
            <div class="empty-state">
                <span class="empty-icon">👆</span>
                <p>Select a user to view their calendar.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function updateCalendar() {
            const userId = document.getElementById('userSelect').value;
            const month = document.getElementById('monthSelect').value;
            if (userId) {
                window.location.href = 'calendar.php?user_id=' + userId + '&month=' + month;
            }
        }
    </script>
    <script src="../assets/script.js"></script>
</body>
</html>
