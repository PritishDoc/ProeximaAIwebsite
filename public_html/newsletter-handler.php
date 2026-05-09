<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        try {
            $stmt = $pdo->prepare("INSERT IGNORE INTO newsletter (email) VALUES (?)");
            $stmt->execute([$email]);
            echo json_encode(['status' => 'success', 'message' => 'Thank you for subscribing!']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => 'Database error.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    }
    exit;
}
?>
