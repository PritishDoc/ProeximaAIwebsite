<?php
/**
 * API: Get Screenshots (Admin)
 * ----------------------------
 * GET /api/get_screenshots.php?user_id=X&date=YYYY-MM-DD&page=1
 * Returns paginated screenshots for a specific employee
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

$userId = intval($_GET['user_id'] ?? 0);
$date   = $_GET['date'] ?? date('Y-m-d');
$page   = max(1, intval($_GET['page'] ?? 1));
$limit  = 20;
$offset = ($page - 1) * $limit;

if ($userId <= 0) {
    jsonResponse(['error' => 'Invalid user_id'], 400);
}

// Validate date format
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    jsonResponse(['error' => 'Invalid date format (YYYY-MM-DD)'], 400);
}

try {
    $db = getDB();
    
    // Get total count
    $stmt = $db->prepare("
        SELECT COUNT(*) as total 
        FROM screenshots 
        WHERE user_id = :uid AND DATE(captured_at) = :date
    ");
    $stmt->execute([':uid' => $userId, ':date' => $date]);
    $total = $stmt->fetch()['total'];
    
    // Get screenshots
    $stmt = $db->prepare("
        SELECT id, image_path, file_size, captured_at
        FROM screenshots 
        WHERE user_id = :uid AND DATE(captured_at) = :date
        ORDER BY captured_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':date', $date, PDO::PARAM_STR);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    
    $screenshots = $stmt->fetchAll();
    
    // Get full timeline data
    $stmtTime = $db->prepare("SELECT captured_at FROM screenshots WHERE user_id = :uid AND DATE(captured_at) = :date ORDER BY captured_at ASC");
    $stmtTime->execute([':uid' => $userId, ':date' => $date]);
    $timeline = [];
    while ($tRow = $stmtTime->fetch()) {
        $timestamp = strtotime($tRow['captured_at']);
        $timeline[] = [
            'hour' => intval(date('H', $timestamp)),
            'minute' => intval(date('i', $timestamp))
        ];
    }
    
    // Get user info
    $stmt = $db->prepare("SELECT name, email FROM users WHERE id = :uid");
    $stmt->execute([':uid' => $userId]);
    $user = $stmt->fetch();
    
    jsonResponse([
        'success'     => true,
        'user'        => $user,
        'screenshots' => $screenshots,
        'timeline'    => $timeline,
        'pagination'  => [
            'page'       => $page,
            'per_page'   => $limit,
            'total'      => intval($total),
            'total_pages'=> ceil($total / $limit)
        ]
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch screenshots'], 500);
}
