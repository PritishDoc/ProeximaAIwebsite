<?php
/**
 * API: Invite Employee (Admin)
 * ----------------------------
 * POST /api/invite_employee.php
 * Body: { email, project_id }
 * Sends invitation email with registration link
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

$email = filter_var(trim($input['email'] ?? ''), FILTER_VALIDATE_EMAIL);
$projectId = intval($input['project_id'] ?? 0);

if (!$email) {
    jsonResponse(['error' => 'Invalid email address'], 400);
}

try {
    $db = getDB();
    
    // Check if user already exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = :email");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'An account with this email already exists'], 400);
    }
    
    // Check if pending invitation exists
    $stmt = $db->prepare("SELECT id FROM invitations WHERE email = :email AND status = 'pending' AND expires_at > NOW()");
    $stmt->execute([':email' => $email]);
    if ($stmt->fetch()) {
        jsonResponse(['error' => 'A pending invitation already exists for this email'], 400);
    }
    
    // Generate token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    // Validate project exists (if provided)
    if ($projectId > 0) {
        $stmt = $db->prepare("SELECT id FROM projects WHERE id = :id AND status = 'active'");
        $stmt->execute([':id' => $projectId]);
        if (!$stmt->fetch()) {
            $projectId = 0;
        }
    }
    
    // Insert invitation
    $stmt = $db->prepare("
        INSERT INTO invitations (email, token, project_id, invited_by, expires_at)
        VALUES (:email, :token, :pid, :admin, :expires)
    ");
    $stmt->execute([
        ':email'   => $email,
        ':token'   => $token,
        ':pid'     => $projectId > 0 ? $projectId : null,
        ':admin'   => $_SESSION['user_id'],
        ':expires' => $expiresAt
    ]);
    
    // Build registration URL
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $basePath = dirname(dirname($_SERVER['SCRIPT_NAME']));
    $registerUrl = rtrim($protocol . '://' . $host . $basePath, '/') . '/register.php?token=' . $token;
    
    // Send invitation email
    $subject = "You're invited to ScreenMonitor";
    $htmlBody = "
    <html>
    <body style='font-family: Arial, sans-serif; background: #0a0e1a; padding: 40px;'>
        <div style='max-width: 500px; margin: 0 auto; background: #111827; border-radius: 16px; padding: 40px; border: 1px solid rgba(255,255,255,0.08);'>
            <h1 style='color: #818cf8; margin: 0 0 8px;'>Proexima ScreenMonitor</h1>
            <p style='color: #94a3b8; margin: 0 0 24px;'>Employee Monitoring System</p>
            <p style='color: #f1f5f9; font-size: 16px;'>You've been invited to join the monitoring team.</p>
            <p style='color: #94a3b8;'>Click the button below to create your account and get started.</p>
            <a href='{$registerUrl}' style='display: inline-block; padding: 14px 32px; background: linear-gradient(135deg, #6366f1, #8b5cf6); color: white; text-decoration: none; border-radius: 10px; font-weight: 600; margin: 24px 0;'>Create Your Account</a>
            <p style='color: #64748b; font-size: 12px;'>This link expires in 7 days.</p>
            <p style='color: #64748b; font-size: 12px;'>If the button doesn't work, copy this URL:<br>{$registerUrl}</p>
        </div>
    </body>
    </html>";
    
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: ProeximaScreenMonitor <info@proeximaai.com>\r\n";
    $headers .= "Reply-To: info@proeximaai.com\r\n";
    
    $emailSent = @mail($email, $subject, $htmlBody, $headers);
    
    jsonResponse([
        'success'    => true,
        'email_sent' => $emailSent,
        'message'    => $emailSent 
            ? "Invitation sent to {$email}" 
            : "Invitation created but email delivery failed. Share this link manually: {$registerUrl}",
        'register_url' => $registerUrl
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to create invitation'], 500);
}
