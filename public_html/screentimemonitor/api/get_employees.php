<?php
/**
 * API: Get Employees (Admin)
 * --------------------------
 * GET /api/get_employees.php
 * Returns list of all employees with status and today's session info
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

try {
    $db = getDB();
    $today = date('Y-m-d');
    
    // Get all non-admin users with today's session info
    $stmt = $db->prepare("
        SELECT 
            u.id,
            u.name,
            u.email,
            u.role,
            u.designation,
            u.status,
            u.last_activity,
            u.created_at,
            u.project_id,
            p.name as project_name,
            (SELECT MIN(s.login_time) FROM sessions s WHERE s.user_id = u.id AND DATE(s.login_time) = :today1) as today_login,
            (SELECT MAX(s.logout_time) FROM sessions s WHERE s.user_id = u.id AND DATE(s.login_time) = :today2) as today_logout,
            (SELECT COUNT(*) FROM screenshots sc WHERE sc.user_id = u.id AND DATE(sc.captured_at) = :today3) as today_screenshots,
            (SELECT COALESCE(SUM(
                TIMESTAMPDIFF(SECOND, s2.login_time, COALESCE(s2.logout_time, NOW()))
            ), 0) FROM sessions s2 WHERE s2.user_id = u.id AND DATE(s2.login_time) = :today4) as today_seconds
        FROM users u
        LEFT JOIN projects p ON u.project_id = p.id
        WHERE u.role = 'employee'
        ORDER BY u.status DESC, u.name ASC
    ");
    $stmt->execute([
        ':today1' => $today,
        ':today2' => $today,
        ':today3' => $today,
        ':today4' => $today
    ]);
    
    $employees = $stmt->fetchAll();
    
    // Format the data
    foreach ($employees as &$emp) {
        $emp['today_hours'] = formatDuration(intval($emp['today_seconds']));
        $emp['last_activity_formatted'] = $emp['last_activity'] 
            ? date('h:i A', strtotime($emp['last_activity'])) 
            : 'Never';
        unset($emp['password']);
    }
    
    jsonResponse([
        'success'   => true,
        'employees' => $employees,
        'total'     => count($employees)
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch employees'], 500);
}
