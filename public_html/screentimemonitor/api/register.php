<?php
/**
 * API: Register Employee
 * ----------------------
 * POST /api/register.php
 * Body: { token, name, designation, project_id, password }
 * Creates employee account from invitation
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    jsonResponse(['error' => 'Invalid JSON data'], 400);
}

$token       = trim($input['token'] ?? '');
$name        = trim($input['name'] ?? '');
$designation = trim($input['designation'] ?? '');
$projectId   = intval($input['project_id'] ?? 0);
$password    = $input['password'] ?? '';

// Validate
if (empty($token)) jsonResponse(['error' => 'Invalid invitation token'], 400);
if (strlen($name) < 2) jsonResponse(['error' => 'Name must be at least 2 characters'], 400);
if (strlen($password) < 6) jsonResponse(['error' => 'Password must be at least 6 characters'], 400);

try {
    $db = getDB();
    
    // Validate invitation
    $stmt = $db->prepare("
        SELECT id, email, project_id 
        FROM invitations 
        WHERE token = :token AND status = 'pending' AND expires_at > NOW()
    ");
    $stmt->execute([':token' => $token]);
    $invitation = $stmt->fetch();
    
    if (!$invitation) {
        jsonResponse(['error' => 'Invalid or expired invitation. Please request a new one.'], 400);
    }
    
    $email = $invitation['email'];
    
    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'An account with this email already exists'], 400);
    }
    
    // Use project from invitation if not specified
    if ($projectId <= 0 && $invitation['project_id']) {
        $projectId = $invitation['project_id'];
    }
    
    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Create user
    $stmt = $db->prepare("
        INSERT INTO users (name, email, password, role, designation, project_id)
        VALUES (:name, :email, :pass, 'employee', :designation, :pid)
    ");
    $stmt->execute([
        ':name'        => sanitize($name),
        ':email'       => $email,
        ':pass'        => $hashedPassword,
        ':designation' => $designation ?: null,
        ':pid'         => $projectId > 0 ? $projectId : null
    ]);
    
    // Mark invitation as accepted
    $stmt = $db->prepare("UPDATE invitations SET status = 'accepted' WHERE id = :id");
    $stmt->execute([':id' => $invitation['id']]);
    
    jsonResponse([
        'success' => true,
        'message' => 'Account created successfully! You can now log in.'
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to create account: ' . $e->getMessage()], 500);
}
