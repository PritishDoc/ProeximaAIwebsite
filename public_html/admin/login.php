<?php
session_start();
require_once '../includes/db.php';

// Auto-seed admin if empty for testing purposes
$stmt = $pdo->query("SELECT COUNT(*) FROM admin");
if ($stmt->fetchColumn() == 0) {
    $hash = password_hash('password123', PASSWORD_DEFAULT);
    $pdo->query("INSERT INTO admin (username, password) VALUES ('admin', '$hash')");
}

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $admin['username'];
        header("Location: index.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - Proexima AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body { display: flex; align-items: center; justify-content: center; min-height: 100vh; background: var(--bg-dark); }
        .login-box { width: 100%; max-width: 400px; padding: 40px; text-align: center; }
        .form-control { width: 100%; padding: 12px 15px; margin-bottom: 20px; background: rgba(255,255,255,0.05); border: 1px solid var(--border-glass); color: #fff; border-radius: 8px; outline: none; }
        .form-control:focus { border-color: var(--accent-primary); }
    </style>
</head>
<body>

<div class="login-box glass-panel">
    <div class="logo" style="margin-bottom: 30px;">Proexima <span class="accent">AI</span> Panel</div>
    
    <?php if($error): ?>
        <div style="background: rgba(239, 68, 68, 0.2); color: #ef4444; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem;">
            <?php echo $error; ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" class="form-control" placeholder="Username" required>
        <input type="password" name="password" class="form-control" placeholder="Password" required>
        <button type="submit" name="login" class="btn btn-primary" style="width: 100%;">Login</button>
    </form>
    
</div>

</body>
</html>
