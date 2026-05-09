<?php 
require_once 'includes/db.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: blog.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM blogs WHERE id = ?");
$stmt->execute([$id]);
$blog = $stmt->fetch();

if (!$blog) {
    header("Location: blog.php");
    exit;
}

include 'header.php'; 
include 'nav.php';
?>

<section class="section" style="padding-top: 150px; min-height: 80vh;">
    <div class="container gsap-reveal" style="max-width: 800px; margin: 0 auto;">
        
        <a href="blog" style="color: var(--accent-primary); font-size: 0.9rem; margin-bottom: 20px; display: inline-block;">&larr; Back to Blog</a>
        
        <p style="color: var(--text-secondary); margin-bottom: 10px;"><?php echo date('F d, Y', strtotime($blog['created_at'])); ?></p>
        <h1 style="font-size: 3rem; margin-bottom: 30px; line-height: 1.2;"><?php echo htmlspecialchars($blog['title']); ?></h1>
        
        <?php if($blog['image']): ?>
            <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="Blog Image" style="width: 100%; border-radius: 20px; margin-bottom: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        <?php endif; ?>

        <div style="font-size: 1.1rem; line-height: 1.8; color: #ddd;">
            <!-- Note: Outputting raw HTML assuming admin is trusted. Real world might need purifier -->
            <?php echo nl2br(htmlspecialchars($blog['content'])); ?>
        </div>
        
    </div>
</section>

<?php include 'footer.php'; ?>
