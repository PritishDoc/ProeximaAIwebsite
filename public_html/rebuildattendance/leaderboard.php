<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$currentMonth = date('Y-m');
$selectedMonth = $_GET['month'] ?? $currentMonth;

// Monthly leaderboard
$stmt = $pdo->prepare("
    SELECT u.name, COUNT(a.id) AS present_days
    FROM attendance a
    JOIN users u ON a.user_id = u.id
    WHERE DATE_FORMAT(a.date, '%Y-%m') = ?
    AND u.is_admin = 0
    GROUP BY a.user_id
    ORDER BY present_days DESC
    LIMIT 20
");
$stmt->execute([$selectedMonth]);
$monthlyBoard = $stmt->fetchAll();

// Calculate streaks for all users (longest current streak)
$allUsers = $pdo->query("SELECT id, name FROM users WHERE is_admin = 0")->fetchAll();
$streakBoard = [];

foreach ($allUsers as $u) {
    $dStmt = $pdo->prepare("SELECT date FROM attendance WHERE user_id = ? ORDER BY date DESC");
    $dStmt->execute([$u['id']]);
    $dates = $dStmt->fetchAll(PDO::FETCH_COLUMN);

    $streak = 0;
    $checkDate = new DateTime();
    foreach ($dates as $d) {
        if ($d === $checkDate->format('Y-m-d')) {
            $streak++;
            $checkDate->modify('-1 day');
        } else {
            // Also check if yesterday was the last entry (for people who haven't marked today yet)
            if ($streak === 0) {
                $checkDate2 = new DateTime();
                $checkDate2->modify('-1 day');
                if ($d === $checkDate2->format('Y-m-d')) {
                    $streak++;
                    $checkDate2->modify('-1 day');
                    // Continue checking from here
                    $remaining = array_slice($dates, 1);
                    foreach ($remaining as $rd) {
                        if ($rd === $checkDate2->format('Y-m-d')) {
                            $streak++;
                            $checkDate2->modify('-1 day');
                        } else {
                            break;
                        }
                    }
                }
            }
            break;
        }
    }

    if ($streak > 0) {
        $streakBoard[] = ['name' => $u['name'], 'streak' => $streak];
    }
}

usort($streakBoard, function($a, $b) {
    return $b['streak'] - $a['streak'];
});
$streakBoard = array_slice($streakBoard, 0, 20);

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
    <title>Leaderboard — FitTrack Attendance</title>
    <meta name="description" content="See the top fitness members with the best attendance and longest streaks.">
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
        <a href="leaderboard.php" class="nav-item active" id="nav-leaderboard">
            <span class="nav-icon">🏆</span>
            <span class="nav-label">Leaderboard</span>
        </a>
        <a href="progress.php" class="nav-item" id="nav-progress">
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
            <h1>🏆 Leaderboard</h1>
        </div>

        <!-- Tab Switcher -->
        <div class="tab-switcher">
            <button class="tab-btn active" data-tab="monthly" id="tabMonthly">Monthly</button>
            <button class="tab-btn" data-tab="streaks" id="tabStreaks">Streaks</button>
        </div>

        <!-- Monthly Tab -->
        <div class="tab-content active" id="tab-monthly">
            <div class="filter-bar">
                <select id="monthFilter" class="select-input" onchange="window.location.href='leaderboard.php?month='+this.value">
                    <?php foreach ($months as $m): ?>
                        <option value="<?= $m ?>" <?= $m === $selectedMonth ? 'selected' : '' ?>>
                            <?= date('F Y', strtotime($m . '-01')) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (empty($monthlyBoard)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>No attendance data for this month.</p>
                </div>
            <?php else: ?>
                <div class="leaderboard-list">
                    <?php foreach ($monthlyBoard as $idx => $entry): ?>
                        <div class="leader-card <?= $idx < 3 ? 'top-three rank-' . ($idx + 1) : '' ?>">
                            <div class="leader-rank">
                                <?php if ($idx === 0): ?>
                                    <span class="medal">🥇</span>
                                <?php elseif ($idx === 1): ?>
                                    <span class="medal">🥈</span>
                                <?php elseif ($idx === 2): ?>
                                    <span class="medal">🥉</span>
                                <?php else: ?>
                                    <span class="rank-num"><?= $idx + 1 ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="leader-info">
                                <span class="leader-name"><?= htmlspecialchars($entry['name']) ?></span>
                            </div>
                            <div class="leader-stat">
                                <span class="stat-number"><?= $entry['present_days'] ?></span>
                                <span class="stat-unit">days</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Streaks Tab -->
        <div class="tab-content" id="tab-streaks">
            <?php if (empty($streakBoard)): ?>
                <div class="empty-state">
                    <span class="empty-icon">🔥</span>
                    <p>No active streaks yet.</p>
                </div>
            <?php else: ?>
                <div class="leaderboard-list">
                    <?php foreach ($streakBoard as $idx => $entry): ?>
                        <div class="leader-card <?= $idx < 3 ? 'top-three rank-' . ($idx + 1) : '' ?>">
                            <div class="leader-rank">
                                <?php if ($idx === 0): ?>
                                    <span class="medal">🥇</span>
                                <?php elseif ($idx === 1): ?>
                                    <span class="medal">🥈</span>
                                <?php elseif ($idx === 2): ?>
                                    <span class="medal">🥉</span>
                                <?php else: ?>
                                    <span class="rank-num"><?= $idx + 1 ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="leader-info">
                                <span class="leader-name"><?= htmlspecialchars($entry['name']) ?></span>
                            </div>
                            <div class="leader-stat">
                                <span class="stat-number">🔥 <?= $entry['streak'] ?></span>
                                <span class="stat-unit">days</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="assets/script.js"></script>
</body>
</html>
