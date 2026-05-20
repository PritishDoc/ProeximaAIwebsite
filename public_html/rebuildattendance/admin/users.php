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

$alertMessage = '';
$alertClass = '';

// Handle POST actions for Manual/Quick attendance and Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $userId = intval($_POST['user_id'] ?? 0);

    if ($action === 'add_manual_attendance') {
        $manualDate = trim($_POST['manual_date'] ?? '');
        $manualTime = trim($_POST['manual_time'] ?? '06:30');
        $manualLat = floatval($_POST['manual_lat'] ?? 20.24532);
        $manualLng = floatval($_POST['manual_lng'] ?? 85.81090);

        if ($userId && $manualDate && $manualTime) {
            try {
                $checkStmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
                $checkStmt->execute([$userId, $manualDate]);
                if ($checkStmt->fetch()) {
                    $alertMessage = "An attendance record already exists for " . date('M d, Y', strtotime($manualDate)) . ".";
                    $alertClass = "alert-error";
                } else {
                    $insStmt = $pdo->prepare("INSERT INTO attendance (user_id, date, time, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
                    $insStmt->execute([$userId, $manualDate, $manualTime . ':00', $manualLat, $manualLng]);
                    $alertMessage = "Attendance record added successfully for " . date('M d, Y', strtotime($manualDate)) . "!";
                    $alertClass = "alert-success";
                }
            } catch (PDOException $e) {
                $alertMessage = "Database error: " . $e->getMessage();
                $alertClass = "alert-error";
            }
        } else {
            $alertMessage = "Please fill in all required fields.";
            $alertClass = "alert-error";
        }
    } elseif ($action === 'quick_past_days_attendance') {
        if ($userId) {
            try {
                $addedCount = 0;
                $skippedCount = 0;
                $today = new DateTime();
                
                // Add attendance for past 4 days (not including today)
                for ($i = 1; $i <= 4; $i++) {
                    $dateObj = clone $today;
                    $dateObj->modify("-$i day");
                    $dateStr = $dateObj->format('Y-m-d');
                    
                    // Check if already exists
                    $checkStmt = $pdo->prepare("SELECT id FROM attendance WHERE user_id = ? AND date = ?");
                    $checkStmt->execute([$userId, $dateStr]);
                    if ($checkStmt->fetch()) {
                        $skippedCount++;
                    } else {
                        // Standard values: 06:30 AM, default coordinates
                        $insStmt = $pdo->prepare("INSERT INTO attendance (user_id, date, time, latitude, longitude) VALUES (?, ?, ?, ?, ?)");
                        $insStmt->execute([$userId, $dateStr, '06:30:00', 20.24532, 85.81090]);
                        $addedCount++;
                    }
                }
                
                if ($addedCount > 0) {
                    $alertMessage = "Successfully added attendance for $addedCount past day(s)!";
                    if ($skippedCount > 0) {
                        $alertMessage .= " (Skipped $skippedCount day(s) that already had records)";
                    }
                    $alertClass = "alert-success";
                } else {
                    $alertMessage = "No records were added. All of the past 4 days already have attendance records.";
                    $alertClass = "alert-info";
                }
            } catch (PDOException $e) {
                $alertMessage = "Database error: " . $e->getMessage();
                $alertClass = "alert-error";
            }
        }
    } elseif ($action === 'delete_attendance') {
        $attendanceId = intval($_POST['attendance_id'] ?? 0);
        if ($attendanceId) {
            try {
                $delStmt = $pdo->prepare("DELETE FROM attendance WHERE id = ?");
                $delStmt->execute([$attendanceId]);
                $alertMessage = "Attendance record deleted successfully.";
                $alertClass = "alert-success";
            } catch (PDOException $e) {
                $alertMessage = "Database error: " . $e->getMessage();
                $alertClass = "alert-error";
            }
        }
    }
}

// Fetch user data (with updated attendance if just modified)
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
        <?php if ($alertMessage): ?>
            <div class="alert <?= $alertClass ?>">
                <span><?= $alertClass === 'alert-success' ? '✅' : ($alertClass === 'alert-info' ? 'ℹ️' : '❌') ?></span>
                <p><?= htmlspecialchars($alertMessage) ?></p>
            </div>
        <?php endif; ?>

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

            <!-- Manage Attendance Section -->
            <div class="admin-section">
                <h2>⚙️ Manage Attendance</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px;">
                    
                    <!-- Quick Add Past 4 Days Card -->
                    <div class="stat-card accent-orange" style="text-align: left; padding: 20px;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--orange);">⚡ Quick Log Past 4 Days</h3>
                        <p class="text-muted" style="margin-bottom: 16px; font-size: 0.85rem; line-height: 1.4;">Automatically mark this user present for the last 4 calendar days. This will skip any days they have already marked.</p>
                        <form method="POST">
                            <input type="hidden" name="action" value="quick_past_days_attendance">
                            <input type="hidden" name="user_id" value="<?= $userDetail['id'] ?>">
                            <button type="submit" class="btn btn-small" style="background: var(--accent-gradient);">⚡ Quick Add Past 4 Days</button>
                        </form>
                    </div>

                    <!-- Manual Date Card -->
                    <div class="stat-card accent-blue" style="text-align: left; padding: 20px;">
                        <h3 style="font-size: 1.1rem; margin-bottom: 8px; display: flex; align-items: center; gap: 8px; color: var(--accent-primary);">📅 Manually Add Attendance</h3>
                        <form method="POST" style="display: flex; flex-direction: column; gap: 12px; margin-top: 10px;">
                            <input type="hidden" name="action" value="add_manual_attendance">
                            <input type="hidden" name="user_id" value="<?= $userDetail['id'] ?>">
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Date</label>
                                    <input type="date" name="manual_date" class="select-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-md); color: var(--text-primary); margin-top: 4px;" required value="<?= date('Y-m-d') ?>">
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Time</label>
                                    <input type="time" name="manual_time" class="select-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-md); color: var(--text-primary); margin-top: 4px;" required value="06:30">
                                </div>
                            </div>
                            
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Latitude</label>
                                    <input type="text" name="manual_lat" class="select-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-md); color: var(--text-primary); margin-top: 4px;" value="20.24532" required>
                                </div>
                                <div>
                                    <label style="font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase;">Longitude</label>
                                    <input type="text" name="manual_lng" class="select-input" style="width: 100%; padding: 8px 12px; background: var(--bg-secondary); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: var(--radius-md); color: var(--text-primary); margin-top: 4px;" value="85.81090" required>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-small" style="align-self: flex-start; background: var(--accent-gradient); margin-top: 4px;">➕ Add Record</button>
                        </form>
                    </div>

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
                                    <th>Actions</th>
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
                                        <td>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this attendance record?');">
                                                <input type="hidden" name="action" value="delete_attendance">
                                                <input type="hidden" name="attendance_id" value="<?= $a['id'] ?>">
                                                <input type="hidden" name="user_id" value="<?= $userDetail['id'] ?>">
                                                <button type="submit" style="background: none; border: none; color: var(--red); cursor: pointer; padding: 4px 8px; font-weight: 500; font-family: var(--font); font-size: 0.85rem;" title="Delete Record">
                                                    🗑️ Delete
                                                </button>
                                            </form>
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
