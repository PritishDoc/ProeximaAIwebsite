<?php
/**
 * API: Update Config (Admin)
 * --------------------------
 * POST /api/update_config.php
 * Body: { screenshot_interval, retention_days, idle_threshold, activity_send_interval }
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['error' => 'Invalid JSON data'], 400);
}

$allowedKeys = ['screenshot_interval', 'retention_days', 'idle_threshold', 'activity_send_interval'];
$updated = [];

try {
    $db = getDB();
    
    foreach ($allowedKeys as $key) {
        if (isset($input[$key])) {
            $value = intval($input[$key]);
            
            // Validate ranges
            switch ($key) {
                case 'screenshot_interval':
                    $value = max(5, min(300, $value));    // 5s to 5min
                    break;
                case 'retention_days':
                    $value = max(1, min(365, $value));     // 1 to 365 days
                    break;
                case 'idle_threshold':
                    $value = max(30, min(600, $value));     // 30s to 10min
                    break;
                case 'activity_send_interval':
                    $value = max(10, min(120, $value));     // 10s to 2min
                    break;
            }
            
            updateSetting($key, strval($value));
            $updated[$key] = $value;
        }
    }
    
    if (empty($updated)) {
        jsonResponse(['error' => 'No valid settings provided'], 400);
    }
    
    jsonResponse([
        'success' => true,
        'updated' => $updated
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to update config'], 500);
}
