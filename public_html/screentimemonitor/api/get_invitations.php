<?php
/**
 * API: Get Invitations (Admin)
 * ----------------------------
 * GET /api/get_invitations.php
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

try {
    $db = getDB();
    
    // Auto-expire old invitations
    $db->exec("UPDATE invitations SET status = 'expired' WHERE status = 'pending' AND expires_at < NOW()");
    
    $stmt = $db->query("
        SELECT i.*, p.name as project_name, u.name as invited_by_name
        FROM invitations i
        LEFT JOIN projects p ON i.project_id = p.id
        LEFT JOIN users u ON i.invited_by = u.id
        ORDER BY i.created_at DESC
        LIMIT 50
    ");
    
    $invitations = $stmt->fetchAll();
    
    jsonResponse([
        'success'     => true,
        'invitations' => $invitations
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch invitations'], 500);
}
