<?php
/**
 * API: Export Report (Admin)
 * -------------------------
 * GET /api/export_report.php?user_id=X&start_date=YYYY-MM-DD&end_date=YYYY-MM-DD
 * Generates CSV download for employee activity report
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

$userId    = intval($_GET['user_id'] ?? 0);
$startDate = $_GET['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
$endDate   = $_GET['end_date'] ?? date('Y-m-d');

if ($userId <= 0) {
    jsonResponse(['error' => 'Invalid user_id'], 400);
}

try {
    $db = getDB();
    
    // Get user info
    $stmt = $db->prepare("SELECT name, email FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $userId]);
    $user = $stmt->fetch();
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    // Get daily data
    $stmt = $db->prepare("
        SELECT 
            DATE(s.login_time) as date,
            MIN(s.login_time) as first_login,
            MAX(s.logout_time) as last_logout,
            SUM(TIMESTAMPDIFF(SECOND, s.login_time, COALESCE(s.logout_time, NOW()))) as total_seconds
        FROM sessions s
        WHERE s.user_id = :uid AND DATE(s.login_time) BETWEEN :start AND :end
        GROUP BY DATE(s.login_time)
        ORDER BY date ASC
    ");
    $stmt->execute([':uid' => $userId, ':start' => $startDate, ':end' => $endDate]);
    $sessionData = $stmt->fetchAll();
    
    // Get screenshot counts per day
    $stmt = $db->prepare("
        SELECT DATE(captured_at) as date, COUNT(*) as count
        FROM screenshots
        WHERE user_id = :uid AND DATE(captured_at) BETWEEN :start AND :end
        GROUP BY DATE(captured_at)
    ");
    $stmt->execute([':uid' => $userId, ':start' => $startDate, ':end' => $endDate]);
    $screenshotCounts = [];
    while ($row = $stmt->fetch()) {
        $screenshotCounts[$row['date']] = $row['count'];
    }
    
    // Get activity summary per day
    $stmt = $db->prepare("
        SELECT 
            DATE(period_start) as date,
            SUM(mouse_clicks) as clicks,
            SUM(key_presses) as keys_pressed,
            SUM(idle_seconds) as idle
        FROM activity_logs
        WHERE user_id = :uid AND DATE(period_start) BETWEEN :start AND :end
        GROUP BY DATE(period_start)
    ");
    $stmt->execute([':uid' => $userId, ':start' => $startDate, ':end' => $endDate]);
    $activityData = [];
    while ($row = $stmt->fetch()) {
        $activityData[$row['date']] = $row;
    }
    
    // Generate CSV
    $filename = 'report_' . preg_replace('/[^a-z0-9]/i', '_', $user['name']) . '_' . $startDate . '_to_' . $endDate . '.csv';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, [
        'Date', 'Employee', 'Email', 'First Login', 'Last Logout',
        'Total Hours', 'Screenshots', 'Mouse Clicks', 'Key Presses',
        'Idle Time (min)', 'Activity Score'
    ]);
    
    // Data rows
    foreach ($sessionData as $row) {
        $date = $row['date'];
        $totalHours = round(intval($row['total_seconds']) / 3600, 2);
        $screenshots = $screenshotCounts[$date] ?? 0;
        $activity = $activityData[$date] ?? ['clicks' => 0, 'keys_pressed' => 0, 'idle' => 0];
        $idleMin = round(intval($activity['idle']) / 60, 1);
        
        // Activity score: (clicks + keys) / total_minutes * 100 (rough metric)
        $totalMinutes = max(1, intval($row['total_seconds']) / 60);
        $activityScore = round((intval($activity['clicks']) + intval($activity['keys_pressed'])) / $totalMinutes * 10, 1);
        
        fputcsv($output, [
            $date,
            $user['name'],
            $user['email'],
            $row['first_login'] ? date('h:i A', strtotime($row['first_login'])) : '-',
            $row['last_logout'] ? date('h:i A', strtotime($row['last_logout'])) : 'Active',
            $totalHours,
            $screenshots,
            intval($activity['clicks']),
            intval($activity['keys_pressed']),
            $idleMin,
            $activityScore
        ]);
    }
    
    fclose($output);
    exit;
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to generate report'], 500);
}
