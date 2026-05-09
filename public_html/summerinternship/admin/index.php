<?php
session_start();

$admin_user = 'admin';
$admin_pass = 'admin123'; // Change this to a secure password later

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    if ($_POST['username'] === $admin_user && $_POST['password'] === $admin_pass) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Invalid username or password.";
    }
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Login | ProeximaLearning</title>
        <link rel="stylesheet" href="../styles.css">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
        <style>
            body { display: flex; align-items: center; justify-content: center; height: 100vh; background-color: var(--bg-dark); margin: 0; font-family: 'Inter', sans-serif; }
            .login-box { background: var(--bg-card); padding: 3rem; border-radius: 12px; border: 1px solid var(--glass-border); text-align: center; width: 100%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
            .login-box h2 { color: white; margin-bottom: 2rem; font-family: var(--font-heading); }
            .form-control { width: 100%; padding: 1rem; margin-bottom: 1.2rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); color: white; font-size: 1rem; box-sizing: border-box; }
            .btn { width: 100%; padding: 1rem; border-radius: 8px; font-weight: 600; cursor: pointer; background: #3b82f6; color: white; border: none; font-size: 1rem; transition: 0.3s; }
            .btn:hover { background: #2563eb; }
            .error { color: #f87171; margin-bottom: 1.5rem; font-size: 0.95rem; background: rgba(248, 113, 113, 0.1); padding: 0.8rem; border-radius: 8px; border: 1px solid rgba(248, 113, 113, 0.3); }
        </style>
    </head>
    <body>
        <div class="login-box">
            <h2>Admin Security</h2>
            <?php if (isset($login_error)) echo "<div class='error'>$login_error</div>"; ?>
            <form method="POST">
                <input type="text" name="username" class="form-control" placeholder="Username" required autofocus>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <button type="submit" name="login" class="btn">Access Dashboard</button>
            </form>
        </div>
    </body>
    </html>
    <?php
    exit;
}

require_once '../db.php';

try {
    $stmt = $pdo->query("SELECT * FROM applications ORDER BY created_at DESC");
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Could not retrieve applications.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel | ProeximaLearning</title>
    <link rel="stylesheet" href="../styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        body { padding: 0; margin: 0; background-color: var(--bg-dark); }
        .admin-header { background: var(--bg-darker); padding: 1.5rem 2rem; border-bottom: 1px solid var(--glass-border); display: flex; justify-content: space-between; align-items: center; }
        .admin-title { font-family: var(--font-heading); font-size: 1.5rem; font-weight: 700; }
        .admin-container { max-width: 1400px; margin: 3rem auto; padding: 0 2rem; }
        .table-responsive { overflow-x: auto; background: var(--bg-card); border-radius: 12px; border: 1px solid var(--glass-border); }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th, td { padding: 1.2rem 1.5rem; border-bottom: 1px solid var(--glass-border); vertical-align: middle; }
        th { background: rgba(0,0,0,0.2); font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.05em; white-space: nowrap; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: rgba(255,255,255,0.02); }
        .badge-prog { background: rgba(59, 130, 246, 0.1); color: #60a5fa; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.85rem; font-weight: 500; white-space: nowrap; display: inline-block;}
        .date { color: var(--text-muted); font-size: 0.85rem; white-space: nowrap; }
        .small-text { font-size: 0.85rem; color: var(--text-muted); margin-top: 4px; }
        .btn-view { display: inline-flex; align-items: center; gap: 0.3rem; padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 6px; background: rgba(16, 185, 129, 0.1); color: #10b981; text-decoration: none; border: 1px solid rgba(16, 185, 129, 0.3); transition: var(--transition-normal); white-space: nowrap; }
        .btn-view:hover { background: rgba(16, 185, 129, 0.2); }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="admin-title">Proexima<span class="gradient-text">Learning</span> Admin</div>
        <div style="display: flex; gap: 1rem;">
            <a href="../index.html" class="btn btn-outline" style="padding: 0.5rem 1rem;">View Site</a>
            <a href="index.php?logout=1" class="btn btn-outline" style="padding: 0.5rem 1rem; border-color: rgba(248, 113, 113, 0.3); color: #f87171;">Logout</a>
        </div>
    </header>

    <div class="admin-container">
        <h2 style="margin-bottom: 1.5rem;">Recent Applications</h2>
        
        <?php if (isset($error)): ?>
            <p style="color: #f87171;"><?php echo htmlspecialchars($error); ?></p>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Applicant</th>
                            <th>Contact</th>
                            <th>Demographics</th>
                            <th>Education</th>
                            <th>Program</th>
                            <th>Documents</th>
                            <th>Applied On</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($applications)): ?>
                            <tr>
                                <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 3rem;">No applications found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($applications as $app): ?>
                                <tr>
                                    <td style="color:var(--text-muted)">#<?php echo htmlspecialchars($app['id']); ?></td>
                                    <td style="font-weight: 500; white-space: nowrap;"><?php echo htmlspecialchars($app['full_name']); ?></td>
                                    <td>
                                        <div><?php echo htmlspecialchars($app['email']); ?></div>
                                        <div class="small-text"><i class="ph-fill ph-phone"></i> +91 <?php echo htmlspecialchars($app['phone']); ?></div>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <div><?php echo htmlspecialchars($app['location'] ?? 'N/A'); ?></div>
                                        <div class="small-text"><i class="ph-fill ph-user"></i> <?php echo htmlspecialchars($app['gender'] ?? '-'); ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 500;"><?php echo htmlspecialchars($app['college'] ?? ($app['education'] ?? 'N/A')); ?></div>
                                        <?php if (!empty($app['qualification'])): ?>
                                        <div class="small-text">
                                            <?php echo htmlspecialchars($app['qualification']); ?> 
                                            (<?php echo htmlspecialchars($app['course_branch']); ?>) - Sem <?php echo htmlspecialchars($app['semester']); ?>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-prog"><?php echo htmlspecialchars($app['program'] ?? 'AI Course'); ?></span></td>
                                    <td>
                                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                            <?php if (!empty($app['resume_path'])): ?>
                                                <a href="../<?php echo htmlspecialchars($app['resume_path']); ?>" target="_blank" class="btn-view">
                                                    <i class="ph-bold ph-file-pdf"></i> View CV
                                                </a>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">No CV</span>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($app['payment_screenshot'])): ?>
                                                <a href="../<?php echo htmlspecialchars($app['payment_screenshot']); ?>" target="_blank" class="btn-view" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6; border-color: rgba(59, 130, 246, 0.3);">
                                                    <i class="ph-bold ph-image"></i> Payment
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="date"><?php echo date('M d, Y', strtotime($app['created_at'])); ?><br><?php echo date('h:i A', strtotime($app['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <div style="text-align: center; padding: 2rem; color: var(--text-muted); font-size: 0.9rem;">
        Design and developed by <a href="https://proeximaai.com" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 500;">ProeximaAI</a>
    </div>
</body>
</html>
