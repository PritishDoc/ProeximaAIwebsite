<?php
/**
 * API: Login
 * ----------
 * POST /api/login.php
 * Body: { email, password }
 * Returns: { success, user: { id, name, email, role } }
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

// Only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$missing = validateRequired(['email', 'password'], $input);
if (!empty($missing)) {
    jsonResponse(['error' => 'Missing fields: ' . implode(', ', $missing)], 400);
}

$email = sanitize($input['email']);
$password = $input['password'];

try {
    $db = getDB();
    
    // Find user by email
    $stmt = $db->prepare("SELECT id, name, email, password, role FROM users WHERE email = :email LIMIT 1");
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();
    
    if (!$user || !password_verify($password, $user['password'])) {
        jsonResponse(['error' => 'Invalid email or password'], 401);
    }
    
    // Set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
    
    // Create session record
    $stmt = $db->prepare("INSERT INTO sessions (user_id, login_time) VALUES (:uid, NOW())");
    $stmt->execute([':uid' => $user['id']]);
    $_SESSION['session_record_id'] = $db->lastInsertId();
    
    // Update user status
    $stmt = $db->prepare("UPDATE users SET status = 'active', last_activity = NOW() WHERE id = :uid");
    $stmt->execute([':uid' => $user['id']]);
    
    jsonResponse([
        'success' => true,
        'user' => [
            'id'    => $user['id'],
            'name'  => $user['name'],
            'email' => $user['email'],
            'role'  => $user['role']
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Server error'], 500);
}
