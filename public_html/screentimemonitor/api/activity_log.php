<?php
/**
 * API: Activity Log
 * -----------------
 * POST /api/activity_log.php
 * Body: { mouse_clicks, mouse_distance, key_presses, idle_seconds, period_start, period_end }
 * Also handles heartbeat/status updates
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireLogin();

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['error' => 'Invalid JSON data'], 400);
}

$userId = $_SESSION['user_id'];

try {
    $db = getDB();
    
    // Handle heartbeat-only request
    if (isset($input['heartbeat']) && $input['heartbeat'] === true) {
        $status = isset($input['status']) ? sanitize($input['status']) : 'active';
        $validStatuses = ['active', 'idle'];
        $status = in_array($status, $validStatuses) ? $status : 'active';
        
        $stmt = $db->prepare("UPDATE users SET status = :status, last_activity = NOW() WHERE id = :uid");
        $stmt->execute([':status' => $status, ':uid' => $userId]);
        
        jsonResponse(['success' => true, 'status' => $status]);
    }
    
    // Validate activity data
    $mouseClicks   = max(0, intval($input['mouse_clicks'] ?? 0));
    $mouseDistance  = max(0, floatval($input['mouse_distance'] ?? 0));
    $keyPresses    = max(0, intval($input['key_presses'] ?? 0));
    $idleSeconds   = max(0, intval($input['idle_seconds'] ?? 0));
    $periodStart   = $input['period_start'] ?? date('Y-m-d H:i:s');
    $periodEnd     = $input['period_end'] ?? date('Y-m-d H:i:s');
    
    // Insert activity log
    $stmt = $db->prepare("
        INSERT INTO activity_logs (user_id, mouse_clicks, mouse_distance, key_presses, idle_seconds, period_start, period_end)
        VALUES (:uid, :clicks, :distance, :keys, :idle, :pstart, :pend)
    ");
    $stmt->execute([
        ':uid'      => $userId,
        ':clicks'   => $mouseClicks,
        ':distance' => $mouseDistance,
        ':keys'     => $keyPresses,
        ':idle'     => $idleSeconds,
        ':pstart'   => $periodStart,
        ':pend'     => $periodEnd
    ]);
    
    // Update user status based on idle time
    $idleThreshold = intval(getSetting('idle_threshold', '120'));
    $status = ($idleSeconds > $idleThreshold) ? 'idle' : 'active';
    
    $stmt = $db->prepare("UPDATE users SET status = :status, last_activity = NOW() WHERE id = :uid");
    $stmt->execute([':status' => $status, ':uid' => $userId]);
    
    jsonResponse([
        'success' => true,
        'logged'  => [
            'mouse_clicks'  => $mouseClicks,
            'key_presses'   => $keyPresses,
            'idle_seconds'  => $idleSeconds,
            'status'        => $status
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to log activity'], 500);
}
