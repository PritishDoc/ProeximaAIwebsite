<?php
/**
 * API: Get Latest Screenshots (Admin)
 * ------------------------------------
 * GET /api/get_latest_screenshots.php?minutes=30&user_id=0
 * Real-time feed of recent screenshots from all employees
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireAdmin();

$minutes = max(5, min(1440, intval($_GET['minutes'] ?? 60)));
$userId  = intval($_GET['user_id'] ?? 0);

try {
    $db = getDB();
    
    $sql = "
        SELECT s.id, s.user_id, s.image_path, s.file_size, s.captured_at,
               u.name as user_name, u.email as user_email, u.designation, u.status as user_status,
               p.name as project_name
        FROM screenshots s
        JOIN users u ON s.user_id = u.id
        LEFT JOIN projects p ON u.project_id = p.id
        WHERE s.captured_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)
    ";
    
    $params = [':minutes' => $minutes];
    
    if ($userId > 0) {
        $sql .= " AND s.user_id = :uid";
        $params[':uid'] = $userId;
    }
    
    $sql .= " ORDER BY s.captured_at DESC LIMIT 100";
    
    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    $screenshots = $stmt->fetchAll();
    
    // Add relative time
    $now = time();
    foreach ($screenshots as &$ss) {
        $capturedTime = strtotime($ss['captured_at']);
        $diff = $now - $capturedTime;
        
        if ($diff < 60) {
            $ss['relative_time'] = 'Just now';
        } elseif ($diff < 3600) {
            $ss['relative_time'] = floor($diff / 60) . ' min ago';
        } elseif ($diff < 86400) {
            $ss['relative_time'] = floor($diff / 3600) . ' hr ago';
        } else {
            $ss['relative_time'] = date('M j', $capturedTime);
        }
        
        // Day label
        $capturedDate = date('Y-m-d', $capturedTime);
        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        
        if ($capturedDate === $today) {
            $ss['day_label'] = 'Today';
        } elseif ($capturedDate === $yesterday) {
            $ss['day_label'] = 'Yesterday';
        } else {
            $ss['day_label'] = date('l, M j', $capturedTime);
        }
        
        $ss['time_label'] = date('h:i:s A', $capturedTime);
    }
    
    // Add full timeline data if a single user is selected for the 'Today' view
    $timeline = [];
    if ($userId > 0) {
        $stmtTime = $db->prepare("SELECT captured_at FROM screenshots WHERE user_id = :uid AND DATE(captured_at) = CURDATE() ORDER BY captured_at ASC");
        $stmtTime->execute([':uid' => $userId]);
        while ($tRow = $stmtTime->fetch()) {
            $timestamp = strtotime($tRow['captured_at']);
            $timeline[] = [
                'hour' => intval(date('H', $timestamp)),
                'minute' => intval(date('i', $timestamp))
            ];
        }
    }
    
    jsonResponse([
        'success'     => true,
        'screenshots' => $screenshots,
        'timeline'    => $timeline,
        'total'       => count($screenshots)
    ]);
    
} catch (PDOException $e) {
    jsonResponse(['error' => 'Failed to fetch screenshots'], 500);
}
