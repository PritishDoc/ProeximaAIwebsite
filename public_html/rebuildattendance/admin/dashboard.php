<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// Stats
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_admin = 0")->fetchColumn();

$today = date('Y-m-d');
$todayAttendance = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE date = ?");
$todayAttendance->execute([$today]);
$todayCount = $todayAttendance->fetchColumn();

$currentMonth = date('Y-m');
$monthlyStmt = $pdo->prepare("SELECT COUNT(DISTINCT user_id) FROM attendance WHERE DATE_FORMAT(date, '%Y-%m') = ?");
$monthlyStmt->execute([$currentMonth]);
$monthlyActive = $monthlyStmt->fetchColumn();

$totalAttendance = $pdo->query("SELECT COUNT(*) FROM attendance")->fetchColumn();

// Today's attendance list
$todayList = $pdo->prepare("
    SELECT u.name, a.time, a.latitude, a.longitude 
    FROM attendance a 
    JOIN users u ON a.user_id = u.id 
    WHERE a.date = ? 
    ORDER BY a.time ASC
");
$todayList->execute([$today]);
$todayEntries = $todayList->fetchAll();

// Recent attendance (last 10)
$recentStmt = $pdo->query("
    SELECT u.name, a.date, a.time 
    FROM attendance a 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.created_at DESC 
    LIMIT 10
");
$recentEntries = $recentStmt->fetchAll();

// Global Settings
$overrideStmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_key = 'location_override'");
$overrideStmt->execute();
$locationOverride = $overrideStmt->fetchColumn() ?: 'off';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — FitTrack</title>
    <meta name="theme-color" content="#0f0f23">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body class="dashboard-page admin-page">
    <!-- Admin Navigation -->
    <nav class="bottom-nav">
        <a href="dashboard.php" class="nav-item active" id="nav-admin-dash">
            <span class="nav-icon">📊</span>
            <span class="nav-label">Dashboard</span>
        </a>
        <a href="users.php" class="nav-item" id="nav-admin-users">
            <span class="nav-icon">👥</span>
            <span class="nav-label">Users</span>
        </a>
        <a href="calendar.php" class="nav-item" id="nav-admin-calendar">
            <span class="nav-icon">📅</span>
            <span class="nav-label">Calendar</span>
        </a>
        <a href="../logout.php" class="nav-item" id="nav-admin-logout">
            <span class="nav-icon">🚪</span>
            <span class="nav-label">Logout</span>
        </a>
    </nav>

    <div class="dashboard-container">
        <div class="page-header admin-header-flex">
            <div>
                <h1>🛡️ Admin Dashboard</h1>
                <p class="admin-date"><?= date('l, F j, Y') ?></p>
            </div>
            <div class="header-actions">
                <div class="toggle-container">
                    <span class="toggle-label">Any Time/Location</span>
                    <label class="switch">
                        <input type="checkbox" id="locationOverrideToggle" <?= $locationOverride === 'on' ? 'checked' : '' ?> onchange="toggleLocationOverride(this)">
                        <span class="slider round"></span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid stats-grid-4">
            <div class="stat-card accent-blue">
                <div class="stat-value"><?= $totalUsers ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card accent-green">
                <div class="stat-value"><?= $todayCount ?></div>
                <div class="stat-label">Today</div>
            </div>
            <div class="stat-card accent-orange">
                <div class="stat-value"><?= $monthlyActive ?></div>
                <div class="stat-label">Active This Month</div>
            </div>
            <div class="stat-card accent-purple">
                <div class="stat-value"><?= $totalAttendance ?></div>
                <div class="stat-label">Total Records</div>
            </div>
        </div>

        <!-- Today's Attendance -->
        <div class="admin-section">
            <h2>Today's Attendance (<?= $todayCount ?>)</h2>
            <?php if (empty($todayEntries)): ?>
                <div class="empty-state">
                    <span class="empty-icon">📭</span>
                    <p>No attendance marked today yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Time</th>
                                <th>Location</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todayEntries as $idx => $entry): ?>
                                <tr>
                                    <td><?= $idx + 1 ?></td>
                                    <td><?= htmlspecialchars($entry['name']) ?></td>
                                    <td><?= date('h:i A', strtotime($entry['time'])) ?></td>
                                    <td>
                                        <a href="https://www.google.com/maps?q=<?= $entry['latitude'] ?>,<?= $entry['longitude'] ?>" 
                                           target="_blank" class="map-link">📍 View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Recent Activity -->
        <div class="admin-section">
            <h2>Recent Activity</h2>
            <div class="activity-list">
                <?php foreach ($recentEntries as $entry): ?>
                    <div class="activity-item">
                        <span class="activity-dot"></span>
                        <div class="activity-info">
                            <strong><?= htmlspecialchars($entry['name']) ?></strong>
                            <span class="activity-meta">
                                <?= date('M d', strtotime($entry['date'])) ?> at <?= date('h:i A', strtotime($entry['time'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script src="../assets/script.js"></script>
    <script>
        function toggleLocationOverride(checkbox) {
            const status = checkbox.checked ? 'on' : 'off';
            checkbox.disabled = true;

            fetch('update_settings', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ 
                    key: 'location_override', 
                    value: status 
                })
            })
            .then(res => res.json())
            .then(data => {
                checkbox.disabled = false;
                if (!data.success) {
                    checkbox.checked = !checkbox.checked;
                    alert('Failed to update setting: ' + data.message);
                }
            })
            .catch(err => {
                checkbox.disabled = false;
                checkbox.checked = !checkbox.checked;
                alert('Connection error. Please try again.');
            });
        }
    </script>
</body>
</html>
