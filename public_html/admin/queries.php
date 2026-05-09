<?php
require_once '../includes/db.php';

// Handle Delete Query
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM contacts WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: queries.php");
    exit;
}

$queries = $pdo->query("SELECT * FROM contacts ORDER BY created_at DESC")->fetchAll();

include 'admin_header.php';
?>

<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Proexima <span class="accent">AI</span></div>
        <nav class="sidebar-nav">
            <a href="index.php">Dashboard</a>
            <a href="blogs.php">Manage Blogs</a>
            <a href="queries.php" class="active">Contact Queries</a>
            <a href="quotes.php">Quote Requests</a>
            <a href="logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </nav>
    </div>
    
    <div class="admin-content">
        <div class="header">
            <h2>Contact Queries</h2>
        </div>
        
        <div class="glass-panel">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($queries as $q): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars($q['name']); ?></td>
                            <td><?php echo htmlspecialchars($q['email']); ?></td>
                            <td><?php echo htmlspecialchars($q['phone'] ?? 'N/A'); ?></td>
                            <td style="max-width: 300px; white-space: pre-wrap; word-break: break-word;"><?php echo htmlspecialchars($q['message']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($q['created_at'])); ?></td>
                            <td>
                                <a href="queries.php?delete=<?php echo $q['id']; ?>" style="color: #ef4444; font-size: 0.9rem;" onclick="return confirm('Delete this query?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($queries) == 0): ?>
                        <tr><td colspan="7" style="text-align:center;">No contact queries found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
