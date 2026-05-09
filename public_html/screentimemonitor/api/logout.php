<?php
/**
 * API: Logout
 * -----------
 * POST /api/logout.php
 * Records logout_time and destroys session
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

if (!isLoggedIn()) {
    jsonResponse(['error' => 'Not logged in'], 401);
}

try {
    $db = getDB();
    $userId = $_SESSION['user_id'];
    $sessionId = $_SESSION['session_record_id'] ?? null;
    
    // Update session logout time
    if ($sessionId) {
        $stmt = $db->prepare("UPDATE sessions SET logout_time = NOW() WHERE id = :sid AND user_id = :uid");
        $stmt->execute([':sid' => $sessionId, ':uid' => $userId]);
    }
    
    // Set user offline
    $stmt = $db->prepare("UPDATE users SET status = 'offline' WHERE id = :uid");
    $stmt->execute([':uid' => $userId]);
    
    // Destroy session
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    jsonResponse(['success' => true, 'message' => 'Logged out successfully']);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Server error'], 500);
}
