<?php
session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
    header("Location: login.php");
    exit;
}

// User detail view
$viewUserId = $_GET['user_id'] ?? null;
$userDetail = null;
$userAttendance = [];

if ($viewUserId) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ? AND is_admin = 0");
    $stmt->execute([$viewUserId]);
    $userDetail = $stmt->fetch();

    if ($userDetail) {
        $aStmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? ORDER BY date DESC LIMIT 50");
        $aStmt->execute([$viewUserId]);
        $userAttendance = $aStmt->fetchAll();
    }
}

// All users list
$search = trim($_GET['search'] ?? '');
if ($search) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE is_admin = 0 AND (name LIKE ? OR email LIKE ?) ORDER BY created_at DESC");
    $stmt->execute(["%$search%", "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT * FROM users WHERE is_admin = 0 ORDER BY created_at DESC");
}
$users = $stmt->fetchAll();

// Get attendance counts for each user
$attendanceCounts = [];
foreach ($users as $u) {
    $cStmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ?");
    $cStmt->execute([$u['id']]);
    $attendanceCounts[$u['id']] = $cStmt->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users — Admin FitTrack</title>
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
        <a href="users.php" class="nav-item active" id="nav-admin-users">
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
        <?php if ($userDetail): ?>
            <!-- User Detail View -->
            <div class="page-header">
                <a href="users.php" class="back-link">← Back to Users</a>
                <h1>👤 <?= htmlspecialchars($userDetail['name']) ?></h1>
                <p class="text-muted"><?= htmlspecialchars($userDetail['email']) ?> · Joined <?= date('M d, Y', strtotime($userDetail['created_at'])) ?></p>
            </div>

            <div class="stats-grid">
                <div class="stat-card accent-green">
                    <div class="stat-value"><?= count($userAttendance) ?></div>
                    <div class="stat-label">Total Present</div>
                </div>
            </div>

            <div class="admin-section">
                <h2>Attendance History</h2>
                <?php if (empty($userAttendance)): ?>
                    <div class="empty-state">
                        <span class="empty-icon">📭</span>
                        <p>No attendance records.</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($userAttendance as $idx => $a): ?>
                                    <tr>
                                        <td><?= $idx + 1 ?></td>
                                        <td><?= date('M d, Y', strtotime($a['date'])) ?></td>
                                        <td><?= date('h:i A', strtotime($a['time'])) ?></td>
                                        <td>
                                            <a href="https://www.google.com/maps?q=<?= $a['latitude'] ?>,<?= $a['longitude'] ?>" 
                                               target="_blank" class="map-link">📍 View</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        <?php else: ?>
            <!-- Users List -->
            <div class="page-header">
                <h1>👥 All Users</h1>
            </div>

            <div class="filter-bar">
                <form method="GET" class="search-form">
                    <input type="text" name="search" class="search-input" placeholder="Search by name or email..." 
                           value="<?= htmlspecialchars($search) ?>" id="userSearch">
                    <button type="submit" class="btn btn-small">Search</button>
                </form>
            </div>

            <?php if (empty($users)): ?>
                <div class="empty-state">
                    <span class="empty-icon">👥</span>
                    <p>No users found.</p>
                </div>
            <?php else: ?>
                <div class="user-list">
                    <?php foreach ($users as $u): ?>
                        <a href="users.php?user_id=<?= $u['id'] ?>" class="user-card">
                            <div class="user-avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                            <div class="user-info">
                                <span class="user-name"><?= htmlspecialchars($u['name']) ?></span>
                                <span class="user-email"><?= htmlspecialchars($u['email']) ?></span>
                            </div>
                            <div class="user-stat">
                                <span class="stat-number"><?= $attendanceCounts[$u['id']] ?></span>
                                <span class="stat-unit">days</span>
                            </div>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <script src="../assets/script.js"></script>
</body>
</html>
