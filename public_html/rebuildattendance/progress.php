<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Monthly attendance for last 6 months
$monthlyData = [];
for ($i = 5; $i >= 0; $i--) {
    $m = date('Y-m', strtotime("-$i months"));
    $label = date('M Y', strtotime("-$i months"));
    $daysInMonth = (int)date('t', strtotime($m . '-01'));
    
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
    $stmt->execute([$userId, $m]);
    $count = (int)$stmt->fetchColumn();
    
    $monthlyData[] = [
        'label'      => $label,
        'month'      => $m,
        'count'      => $count,
        'total_days' => $daysInMonth,
        'percentage' => $daysInMonth > 0 ? round(($count / $daysInMonth) * 100, 1) : 0
    ];
}

// Streak history (last 30 days)
$streakHistory = [];
$checkDate = new DateTime();
for ($i = 0; $i < 30; $i++) {
    $d = $checkDate->format('Y-m-d');
    $stmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
    $stmt->execute([$userId, $d]);
    $streakHistory[] = [
        'date'    => $checkDate->format('M d'),
        'present' => $stmt->fetch() ? 1 : 0
    ];
    $checkDate->modify('-1 day');
}
$streakHistory = array_reverse($streakHistory);

// Current month stats
$currentMonth = date('Y-m');
$daysInCurrentMonth = (int)date('t');
$daysSoFar = (int)date('j');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND DATE_FORMAT(date, '%Y-%m') = ?");
$stmt->execute([$userId, $currentMonth]);
$currentMonthCount = (int)$stmt->fetchColumn();
$attendanceRate = $daysSoFar > 0 ? round(($currentMonthCount / $daysSoFar) * 100, 1) : 0;

// Total all time
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ?");
$stmt->execute([$userId]);
$totalAllTime = (int)$stmt->fetchColumn();

// Best streak ever
$allDates = $pdo->prepare("SELECT date FROM attendance WHERE user_id = ? ORDER BY date ASC");
$allDates->execute([$userId]);
$allDatesList = $allDates->fetchAll(PDO::FETCH_COLUMN);

$bestStreak = 0;
$currentStreak = 1;
for ($i = 1; $i < count($allDatesList); $i++) {
    $prev = new DateTime($allDatesList[$i - 1]);
    $curr = new DateTime($allDatesList[$i]);
    $diff = $prev->diff($curr)->days;
    if ($diff === 1) {
        $currentStreak++;
    } else {
        if ($currentStreak > $bestStreak) $bestStreak = $currentStreak;
        $currentStreak = 1;
    }
}
if ($currentStreak > $bestStreak) $bestStreak = $currentStreak;
if (empty($allDatesList)) $bestStreak = 0;

$jsonMonthly = json_encode($monthlyData);
$jsonStreak  = json_encode($streakHistory);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Progress — FitTrack Attendance</title>
    <meta name="description" content="View your personal attendance analytics, charts, and streak history.">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f0f23">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">
    <!-- Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item" id="nav-dashboard">
            <span class="nav-icon">🏠</span>
            <span class="nav-label">Home</span>
        </a>
        <a href="leaderboard.php" class="nav-item" id="nav-leaderboard">
            <span class="nav-icon">🏆</span>
            <span class="nav-label">Leaderboard</span>
        </a>
        <a href="progress.php" class="nav-item active" id="nav-progress">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Progress</span>
        </a>
        <a href="logout.php" class="nav-item" id="nav-logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-label">Logout</span>
        </a>
    </nav>

    <div class="dashboard-container">
        <div class="page-header">
            <h1>📊 My Progress</h1>
        </div>

        <!-- Summary Cards -->
        <div class="stats-grid">
            <div class="stat-card accent-green">
                <div class="stat-value"><?= $attendanceRate ?>%</div>
                <div class="stat-label">This Month</div>
            </div>
            <div class="stat-card accent-orange">
                <div class="stat-value"><?= $bestStreak ?></div>
                <div class="stat-label">Best Streak</div>
            </div>
            <div class="stat-card accent-blue">
                <div class="stat-value"><?= $totalAllTime ?></div>
                <div class="stat-label">Total Days</div>
            </div>
        </div>

        <!-- Monthly Attendance Chart -->
        <div class="chart-card">
            <h3>Monthly Attendance</h3>
            <canvas id="monthlyChart" height="200"></canvas>
        </div>

        <!-- Attendance Percentage Chart -->
        <div class="chart-card">
            <h3>Attendance Rate (%)</h3>
            <canvas id="percentageChart" height="200"></canvas>
        </div>

        <!-- 30-Day Activity -->
        <div class="chart-card">
            <h3>Last 30 Days</h3>
            <div class="activity-grid" id="activityGrid">
                <?php foreach ($streakHistory as $day): ?>
                    <div class="activity-cell <?= $day['present'] ? 'present' : 'absent' ?>" 
                         title="<?= $day['date'] ?>: <?= $day['present'] ? 'Present' : 'Absent' ?>">
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="activity-legend">
                <span class="legend-item"><span class="legend-dot present"></span> Present</span>
                <span class="legend-item"><span class="legend-dot absent"></span> Absent</span>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="assets/script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const monthlyData = <?= $jsonMonthly ?>;
            const streakData  = <?= $jsonStreak ?>;
            initProgressCharts(monthlyData, streakData);
        });
    </script>
</body>
</html>
