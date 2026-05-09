<?php
require_once '../includes/db.php';
include 'admin_header.php';

// Fetch stats
$blogs_count = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$queries_count = $pdo->query("SELECT COUNT(*) FROM contacts")->fetchColumn();
$quotes_count = $pdo->query("SELECT COUNT(*) FROM quotes")->fetchColumn();
?>

<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Proexima <span class="accent">AI</span></div>
        <nav class="sidebar-nav">
            <a href="index.php" class="active">Dashboard</a>
            <a href="blogs.php">Manage Blogs</a>
            <a href="queries.php">Contact Queries</a>
            <a href="quotes.php">Quote Requests</a>
            <a href="logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </nav>
    </div>
    
    <div class="admin-content">
        <div class="header">
            <h2>Dashboard Overview</h2>
            <div style="color: var(--text-secondary);">Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></div>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="glass-panel text-center">
                <h3 style="font-size: 2.5rem; color: var(--accent-primary);"><?php echo $blogs_count; ?></h3>
                <p style="color: var(--text-secondary);">Total Blogs Published</p>
                <a href="blogs.php" class="btn btn-secondary" style="margin-top: 15px; padding: 5px 15px; font-size: 0.8rem;">View All</a>
            </div>
            <div class="glass-panel text-center">
                <h3 style="font-size: 2.5rem; color: #10b981;"><?php echo $queries_count; ?></h3>
                <p style="color: var(--text-secondary);">Contact Queries</p>
                <a href="queries.php" class="btn btn-secondary" style="margin-top: 15px; padding: 5px 15px; font-size: 0.8rem;">View All</a>
            </div>
            <div class="glass-panel text-center">
                <h3 style="font-size: 2.5rem; color: #f59e0b;"><?php echo $quotes_count; ?></h3>
                <p style="color: var(--text-secondary);">Pending Quotes</p>
                <a href="quotes.php" class="btn btn-secondary" style="margin-top: 15px; padding: 5px 15px; font-size: 0.8rem;">View All</a>
            </div>
        </div>
    </div>
</div>

</body>
</html>
