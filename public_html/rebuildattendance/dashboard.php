<?php
session_start();
require_once __DIR__ . '/config/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId   = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Check if already marked today
$today = date('Y-m-d');
$stmt  = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND date = ?");
$stmt->execute([$userId, $today]);
$todayAttendance = $stmt->fetch();

// Calculate current streak
$streakQuery = $pdo->prepare("
    SELECT date FROM attendance 
    WHERE user_id = ? 
    ORDER BY date DESC
");
$streakQuery->execute([$userId]);
$dates = $streakQuery->fetchAll(PDO::FETCH_COLUMN);

$streak = 0;
$checkDate = new DateTime();
// If today hasn't been marked yet, start checking from yesterday
if (!$todayAttendance) {
    $checkDate->modify('-1 day');
}
foreach ($dates as $d) {
    if ($d === $checkDate->format('Y-m-d')) {
        $streak++;
        $checkDate->modify('-1 day');
    } else {
        break;
    }
}

// If today is marked, include it
if ($todayAttendance) {
    // streak already counted today in the loop
} 

// Total attendance this month
$monthStart = date('Y-m-01');
$monthEnd   = date('Y-m-t');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND date BETWEEN ? AND ?");
$stmt->execute([$userId, $monthStart, $monthEnd]);
$monthlyCount = $stmt->fetchColumn();

// Total all-time
$stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ?");
$stmt->execute([$userId]);
$totalCount = $stmt->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — FitTrack Attendance</title>
    <meta name="description" content="Your FitTrack dashboard. Mark your daily attendance and track your fitness streak.">
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0f0f23">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="dashboard-page">
    <!-- Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item active" id="nav-dashboard">
            <span class="nav-icon">🏠</span>
            <span class="nav-label">Home</span>
        </a>
        <a href="leaderboard.php" class="nav-item" id="nav-leaderboard">
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

    <!-- Main Content -->
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dash-header">
            <div class="greeting">
                <p class="welcome-msg">Welcome to RebuildTime Attendance System</p>
                <h1>Hello, <span class="highlight"><?= htmlspecialchars($userName) ?></span></h1>
                <p class="date-display" id="currentDate"></p>
                <p class="time-display" id="liveTime"></p>
            </div>
        </div>

        <!-- Streak Banner -->
        <?php if ($streak > 0): ?>
        <div class="streak-banner animate-pulse">
            <span class="streak-fire">🔥</span>
            <span class="streak-count"><?= $streak ?> Day Streak</span>
            <span class="streak-fire">🔥</span>
        </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?= $monthlyCount ?></div>
                <div class="stat-label">This Month</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $totalCount ?></div>
                <div class="stat-label">Total Days</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?= $streak ?></div>
                <div class="stat-label">Streak</div>
            </div>
        </div>

        <!-- Attendance Button -->
        <div class="attendance-section">
            <?php if ($todayAttendance): ?>
                <button class="btn-mark-present marked" onclick="showAlreadyMarkedMessage()">
                    <span class="btn-inner">
                        <span class="mark-icon">✅</span>
                        <span class="mark-text">ATTENDANCE DONE</span>
                    </span>
                </button>
                <p class="attendance-hint">Marked at <?= date('h:i A', strtotime($todayAttendance['time'])) ?></p>
            <?php else: ?>
                <button class="btn-mark-present" id="markPresentBtn" onclick="markAttendance()">
                    <span class="pulse-ring"></span>
                    <span class="btn-inner">
                        <span class="mark-icon">✋</span>
                        <span class="mark-text">MARK PRESENT</span>
                    </span>
                </button>
                <p class="attendance-hint">Attendance window: 4:00 AM – 8:00 AM</p>
            <?php endif; ?>
        </div>

        <!-- Map Section (shows after marking) -->
        <?php if ($todayAttendance): ?>
        <div class="map-section">
            <h3>Your Attendance Location</h3>
            <div id="attendanceMap" class="map-container" 
                 data-lat="<?= $todayAttendance['latitude'] ?>" 
                 data-lng="<?= $todayAttendance['longitude'] ?>">
            </div>
        </div>
        <?php endif; ?>

        <!-- Attendance Result Messages -->
        <div id="attendanceMessage" class="attendance-message hidden"></div>

        <!-- Map container for after marking -->
        <div id="liveMapSection" class="map-section hidden">
            <h3>Your Attendance Location</h3>
            <div id="liveMap" class="map-container"></div>
        </div>
    </div>

    <!-- Daily Motivation Modal -->
    <div id="motivationModal" class="modal-overlay hidden">
        <div class="modal-content motivation-popup">
            <span class="close-modal" id="closeMotivationBtn">&times;</span>
            <div class="motivation-icon">🏃</div>
            <h2 class="motivation-title">Daily Motivation</h2>
            <p id="motivationQuote" class="motivation-quote"></p>
        </div>
    </div>

    <!-- PWA Install Banner -->
    <div class="pwa-install-banner hidden" id="pwaInstallBanner">
        <div class="pwa-content">
            <span class="pwa-icon">📲</span>
            <span class="pwa-text">Install FitTrack App</span>
        </div>
        <button class="btn-pwa-install" id="pwaInstallBtn">Install</button>
        <button class="btn-pwa-dismiss" id="pwaDismissBtn">✕</button>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="assets/script.js?v=2"></script>
    <script>
        // Initialize map if attendance already marked today
        <?php if ($todayAttendance): ?>
        document.addEventListener('DOMContentLoaded', function() {
            initMap(
                <?= $todayAttendance['latitude'] ?>, 
                <?= $todayAttendance['longitude'] ?>,
                'attendanceMap'
            );
        });
        <?php endif; ?>
    </script>
</body>
</html>
