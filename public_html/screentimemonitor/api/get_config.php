<?php
/**
 * API: Get Config
 * ---------------
 * GET /api/get_config.php
 * Returns application settings (screenshot interval, etc.)
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireLogin();

try {
    $db = getDB();
    
    $stmt = $db->query("SELECT setting_key, setting_value FROM settings");
    $settings = [];
    while ($row = $stmt->fetch()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    // Get user's total elapsed time for today
    $userId = $_SESSION['user_id'];
    $stmtActivity = $db->prepare("SELECT SUM(TIMESTAMPDIFF(SECOND, period_start, period_end) - idle_seconds) as total_active FROM activity_logs WHERE user_id = :uid AND DATE(period_end) = CURDATE()");
    $stmtActivity->execute([':uid' => $userId]);
    $activityRow = $stmtActivity->fetch();
    $todaySeconds = intval($activityRow['total_active'] ?? 0);
    
    // Get user's total active hours this month
    $stmtMonth = $db->prepare("SELECT SUM(TIMESTAMPDIFF(SECOND, period_start, period_end) - idle_seconds) as month_active FROM activity_logs WHERE user_id = :uid AND MONTH(period_end) = MONTH(CURDATE()) AND YEAR(period_end) = YEAR(CURDATE())");
    $stmtMonth->execute([':uid' => $userId]);
    $monthRow = $stmtMonth->fetch();
    $monthSeconds = intval($monthRow['month_active'] ?? 0);

    jsonResponse([
        'success'  => true,
        'today_seconds' => $todaySeconds,
        'month_seconds' => $monthSeconds,
        'config'   => [
            'screenshot_interval'   => intval($settings['screenshot_interval'] ?? 10),
            'retention_days'        => intval($settings['retention_days'] ?? 30),
            'idle_threshold'        => intval($settings['idle_threshold'] ?? 120),
            'activity_send_interval'=> intval($settings['activity_send_interval'] ?? 30)
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch config'], 500);
}
