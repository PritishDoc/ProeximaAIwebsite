<?php
require_once '../includes/db.php';

// Handle Add Blog
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_blog'])) {
    $title = trim($_POST['title']);
    $preview = trim($_POST['preview']);
    $content = trim($_POST['content']);
    $image = trim($_POST['image']); // storing URL for simplicity
    
    $stmt = $pdo->prepare("INSERT INTO blogs (title, preview, content, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$title, $preview, $content, $image]);
    header("Location: blogs.php");
    exit;
}

// Handle Delete Blog
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM blogs WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: blogs.php");
    exit;
}

$blogs = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC")->fetchAll();

include 'admin_header.php';
?>

<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Proexima <span class="accent">AI</span></div>
        <nav class="sidebar-nav">
            <a href="index.php">Dashboard</a>
            <a href="blogs.php" class="active">Manage Blogs</a>
            <a href="queries.php">Contact Queries</a>
            <a href="quotes.php">Quote Requests</a>
            <a href="logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </nav>
    </div>
    
    <div class="admin-content">
        <div class="header">
            <h2>Manage Blogs</h2>
        </div>
        
        <div class="glass-panel" style="margin-bottom: 40px;">
            <h3>Add New Blog</h3>
            <form method="POST" style="margin-top: 20px;">
                <div style="margin-bottom: 15px;">
                    <input type="text" name="title" placeholder="Blog Title" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-glass); border-radius: 5px;" required>
                </div>
                <div style="margin-bottom: 15px;">
                    <input type="text" name="image" placeholder="Image URL (Optional)" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-glass); border-radius: 5px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <textarea name="preview" placeholder="Short Preview (max 200 chars)" rows="2" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-glass); border-radius: 5px;" required></textarea>
                </div>
                <div style="margin-bottom: 15px;">
                    <textarea name="content" placeholder="Full Blog Content (HTML supported)" rows="8" style="width: 100%; padding: 10px; background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border-glass); border-radius: 5px;" required></textarea>
                </div>
                <button type="submit" name="add_blog" class="btn btn-primary">Publish Blog</button>
            </form>
        </div>
        
        <div class="glass-panel">
            <h3>Published Blogs</h3>
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($blogs as $blog): ?>
                        <tr>
                            <td><?php echo $blog['id']; ?></td>
                            <td><?php echo htmlspecialchars($blog['title']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></td>
                            <td>
                                <a href="blogs.php?delete=<?php echo $blog['id']; ?>" style="color: #ef4444; font-size: 0.9rem;" onclick="return confirm('Delete this blog?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($blogs) == 0): ?>
                        <tr><td colspan="4" style="text-align:center;">No blogs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
