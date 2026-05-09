<?php
/**
 * API: Get Projects
 * -----------------
 * GET /api/get_projects.php
 * Returns all active projects (available for both admin and registration)
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

// Allow access for registration (no login required) and admin
try {
    $db = getDB();
    
    $stmt = $db->query("
        SELECT p.id, p.name, p.description, p.status, p.created_at,
               (SELECT COUNT(*) FROM users u WHERE u.project_id = p.id) as employee_count
        FROM projects p
        WHERE p.status = 'active'
        ORDER BY p.name ASC
    ");
    
    $projects = $stmt->fetchAll();
    
    jsonResponse([
        'success'  => true,
        'projects' => $projects
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch projects'], 500);
}
