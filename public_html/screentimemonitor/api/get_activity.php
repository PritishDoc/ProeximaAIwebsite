<?php
/**
 * API: Get Activity Data (Admin)
 * ------------------------------
 * GET /api/get_activity.php?user_id=X&date=YYYY-MM-DD
 * Returns hourly aggregated activity data for Chart.js graphs
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

$userId = intval($_GET['user_id'] ?? 0);
$date   = $_GET['date'] ?? date('Y-m-d');

if ($userId <= 0) {
    jsonResponse(['error' => 'Invalid user_id'], 400);
}

try {
    $db = getDB();
    
    // Hourly aggregated activity
    $stmt = $db->prepare("
        SELECT 
            HOUR(period_start) as hour,
            SUM(mouse_clicks) as total_clicks,
            SUM(mouse_distance) as total_distance,
            SUM(key_presses) as total_keys,
            SUM(idle_seconds) as total_idle,
            COUNT(*) as log_count
        FROM activity_logs
        WHERE user_id = :uid AND DATE(period_start) = :date
        GROUP BY HOUR(period_start)
        ORDER BY hour ASC
    ");
    $stmt->execute([':uid' => $userId, ':date' => $date]);
    $hourlyData = $stmt->fetchAll();
    
    // Fill in all 24 hours
    $hours = array_fill(0, 24, [
        'hour'           => 0,
        'total_clicks'   => 0,
        'total_distance' => 0,
        'total_keys'     => 0,
        'total_idle'     => 0,
        'log_count'      => 0
    ]);
    
    foreach ($hourlyData as $row) {
        $h = intval($row['hour']);
        $hours[$h] = [
            'hour'           => $h,
            'total_clicks'   => intval($row['total_clicks']),
            'total_distance' => round(floatval($row['total_distance']), 1),
            'total_keys'     => intval($row['total_keys']),
            'total_idle'     => intval($row['total_idle']),
            'log_count'      => intval($row['log_count'])
        ];
    }
    
    // Day summary
    $stmt = $db->prepare("
        SELECT 
            SUM(mouse_clicks) as total_clicks,
            SUM(key_presses) as total_keys,
            SUM(idle_seconds) as total_idle,
            ROUND(AVG(mouse_distance), 1) as avg_distance
        FROM activity_logs
        WHERE user_id = :uid AND DATE(period_start) = :date
    ");
    $stmt->execute([':uid' => $userId, ':date' => $date]);
    $summary = $stmt->fetch();
    
    // Screenshot count per hour
    $stmt = $db->prepare("
        SELECT HOUR(captured_at) as hour, COUNT(*) as count
        FROM screenshots
        WHERE user_id = :uid AND DATE(captured_at) = :date
        GROUP BY HOUR(captured_at)
        ORDER BY hour ASC
    ");
    $stmt->execute([':uid' => $userId, ':date' => $date]);
    $screenshotHourly = $stmt->fetchAll();
    
    $screenshotsByHour = array_fill(0, 24, 0);
    foreach ($screenshotHourly as $row) {
        $screenshotsByHour[intval($row['hour'])] = intval($row['count']);
    }
    
    jsonResponse([
        'success' => true,
        'hourly'  => array_values($hours),
        'screenshots_hourly' => $screenshotsByHour,
        'summary' => [
            'total_clicks'  => intval($summary['total_clicks'] ?? 0),
            'total_keys'    => intval($summary['total_keys'] ?? 0),
            'total_idle'    => intval($summary['total_idle'] ?? 0),
            'avg_distance'  => floatval($summary['avg_distance'] ?? 0)
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch activity data'], 500);
}
