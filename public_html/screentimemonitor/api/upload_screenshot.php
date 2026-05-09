<?php
/**
 * API: Upload Screenshot
 * ----------------------
 * POST /api/upload_screenshot.php
 * Body: { image: base64_string } OR multipart file upload
 * Saves screenshot to uploads/screenshots/{user_id}/{date}/
 */

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

requireLogin();

$userId = $_SESSION['user_id'];
$date = date('Y-m-d');
$timestamp = date('Y-m-d_H-i-s') . '_' . substr(microtime(true) * 1000 % 1000, 0, 3);

// Create upload directory
$uploadDir = __DIR__ . '/../uploads/screenshots/' . $userId . '/' . $date . '/';
ensureDirectory($uploadDir);

$imageData = null;

// Handle base64 input
$input = json_decode(file_get_contents('php://input'), true);
if ($input && isset($input['image'])) {
    // Strip data URI prefix if present
    $base64 = $input['image'];
    if (strpos($base64, 'data:image') !== false) {
        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', $base64);
    }
    $imageData = base64_decode($base64);
}

// Handle file upload
if (!$imageData && isset($_FILES['screenshot'])) {
    $file = $_FILES['screenshot'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        $imageData = file_get_contents($file['tmp_name']);
    }
}

if (!$imageData) {
    jsonResponse(['error' => 'No image data received'], 400);
}

// Validate it's actually an image
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mimeType = $finfo->buffer($imageData);
$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];

if (!in_array($mimeType, $allowedMimes)) {
    jsonResponse(['error' => 'Invalid image format. Allowed: JPEG, PNG, WebP'], 400);
}

// Size limit: 5MB
if (strlen($imageData) > 5 * 1024 * 1024) {
    jsonResponse(['error' => 'Image too large (max 5MB)'], 400);
}

$extension = str_replace('image/', '', $mimeType);
if ($extension === 'jpeg') $extension = 'jpg';
$filename = 'screen_' . $timestamp . '.' . $extension;
$filePath = $uploadDir . $filename;
$relativePath = 'uploads/screenshots/' . $userId . '/' . $date . '/' . $filename;

// Save file
if (file_put_contents($filePath, $imageData) === false) {
    jsonResponse(['error' => 'Failed to save screenshot'], 500);
}

$fileSize = strlen($imageData);

try {
    $db = getDB();
    
    // Insert screenshot record
    $stmt = $db->prepare("INSERT INTO screenshots (user_id, image_path, file_size, captured_at) VALUES (:uid, :path, :size, NOW())");
    $stmt->execute([
        ':uid'  => $userId,
        ':path' => $relativePath,
        ':size' => $fileSize
    ]);
    
    // Update user activity
    $stmt = $db->prepare("UPDATE users SET status = 'active', last_activity = NOW() WHERE id = :uid");
    $stmt->execute([':uid' => $userId]);
    
    jsonResponse([
        'success'    => true,
        'screenshot' => [
            'id'   => $db->lastInsertId(),
            'path' => $relativePath,
            'size' => $fileSize
        ]
    ]);
    
} catch (PDOException $e) {
    // Clean up file if DB insert fails
    @unlink($filePath);
    jsonResponse(['error' => 'Failed to record screenshot'], 500);
}
