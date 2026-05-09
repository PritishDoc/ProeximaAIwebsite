<?php
/**
 * API: Alert Close (Beacon)
 * -------------------------
 * POST /api/alert_close.php
 * Receives beacon when employee closes browser during monitoring
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Accept both POST and beacon (which sends as POST)
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Try to get session data
if (!isLoggedIn()) {
    // Beacon may not carry session — try to parse body
    http_response_code(200);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $db = getDB();
    
    // Log the alert
    $stmt = $db->prepare("
        INSERT INTO alerts (user_id, alert_type, message) 
        VALUES (:uid, 'browser_close', :msg)
    ");
    $stmt->execute([
        ':uid' => $userId,
        ':msg' => 'Employee closed the browser/tab while monitoring was active at ' . date('Y-m-d H:i:s')
    ]);
    
    // Update user status
    $stmt = $db->prepare("UPDATE users SET status = 'offline' WHERE id = :uid");
    $stmt->execute([':uid' => $userId]);
    
    // Update session logout time
    $sessionId = $_SESSION['session_record_id'] ?? null;
    if ($sessionId) {
        $stmt = $db->prepare("UPDATE sessions SET logout_time = NOW() WHERE id = :sid");
        $stmt->execute([':sid' => $sessionId]);
    }
    
    http_response_code(200);
    echo json_encode(['success' => true]);
    
} catch (PDOException $e) {
    http_response_code(200); // Beacon doesn't care about response
}
