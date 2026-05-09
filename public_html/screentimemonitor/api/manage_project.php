<?php
/**
 * API: Manage Projects (Admin)
 * ----------------------------
 * POST /api/manage_project.php
 * Body: { action: 'create'|'update'|'archive', name, description, id }
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

$action = $input['action'] ?? '';

try {
    $db = getDB();
    
    switch ($action) {
        case 'create':
            $name = trim($input['name'] ?? '');
            $desc = trim($input['description'] ?? '');
            
            if (strlen($name) < 2) {
                jsonResponse(['error' => 'Project name must be at least 2 characters'], 400);
            }
            
            $stmt = $db->prepare("INSERT INTO projects (name, description) VALUES (:name, :desc)");
            $stmt->execute([':name' => sanitize($name), ':desc' => $desc]);
            
            jsonResponse([
                'success' => true,
                'project' => ['id' => $db->lastInsertId(), 'name' => $name],
                'message' => "Project '{$name}' created"
            ]);
            break;
            
        case 'update':
            $id   = intval($input['id'] ?? 0);
            $name = trim($input['name'] ?? '');
            $desc = trim($input['description'] ?? '');
            
            if ($id <= 0) jsonResponse(['error' => 'Invalid project ID'], 400);
            if (strlen($name) < 2) jsonResponse(['error' => 'Project name required'], 400);
            
            $stmt = $db->prepare("UPDATE projects SET name = :name, description = :desc WHERE id = :id");
            $stmt->execute([':name' => sanitize($name), ':desc' => $desc, ':id' => $id]);
            
            jsonResponse(['success' => true, 'message' => "Project updated"]);
            break;
            
        case 'archive':
            $id = intval($input['id'] ?? 0);
            if ($id <= 0) jsonResponse(['error' => 'Invalid project ID'], 400);
            
            $stmt = $db->prepare("UPDATE projects SET status = 'archived' WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            jsonResponse(['success' => true, 'message' => "Project archived"]);
            break;
            
        default:
            jsonResponse(['error' => "Invalid action. Use: create, update, archive"], 400);
    }
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Operation failed'], 500);
}
