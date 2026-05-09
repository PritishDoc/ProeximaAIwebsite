<?php
require_once '../includes/db.php';

// Handle Delete Quote
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM quotes WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: quotes.php");
    exit;
}

$quotes = $pdo->query("SELECT * FROM quotes ORDER BY created_at DESC")->fetchAll();

include 'admin_header.php';
?>

<div class="admin-layout">
    <div class="sidebar">
        <div class="sidebar-logo">Proexima <span class="accent">AI</span></div>
        <nav class="sidebar-nav">
            <a href="index.php">Dashboard</a>
            <a href="blogs.php">Manage Blogs</a>
            <a href="queries.php">Contact Queries</a>
            <a href="quotes.php" class="active">Quote Requests</a>
            <a href="logout.php" style="margin-top: auto; color: #ef4444;">Logout</a>
        </nav>
    </div>
    
    <div class="admin-content">
        <div class="header">
            <h2>Quote Requests</h2>
        </div>
        
        <div class="glass-panel">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Business Type</th>
                            <th>Service</th>
                            <th>Budget</th>
                            <th>Details/Email</th>
                            <th>Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($quotes as $q): ?>
                        <tr>
                            <td><?php echo $q['id']; ?></td>
                            <td><?php echo htmlspecialchars($q['business_type']); ?></td>
                            <td><?php echo htmlspecialchars($q['service']); ?></td>
                            <td><span style="color: #10b981; font-weight: 600;"><?php echo htmlspecialchars($q['budget']); ?></span></td>
                            <td style="max-width: 300px; white-space: pre-wrap; word-break: break-word;"><?php echo htmlspecialchars($q['message']); ?></td>
                            <td><?php echo date('M d, Y H:i', strtotime($q['created_at'])); ?></td>
                            <td>
                                <a href="quotes.php?delete=<?php echo $q['id']; ?>" style="color: #ef4444; font-size: 0.9rem;" onclick="return confirm('Delete this quote request?');">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(count($quotes) == 0): ?>
                        <tr><td colspan="7" style="text-align:center;">No quote requests found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

</body>
</html>
