<?php 
require_once 'includes/db.php';

// Fetch blogs
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll();

include 'header.php'; 
include 'nav.php';
?>

<section class="section" style="padding-top: 150px; min-height: 80vh;">
    <div class="container">
        <div class="gsap-reveal" style="text-align: center; margin-bottom: 60px;">
            <h1 class="section-title">Latest <span class="accent">Insights</span></h1>
            <p class="section-desc">Stay updated with the latest in AI innovation, enterprise automation, and tech news.</p>
        </div>

        <div class="products-grid gsap-stagger-container">
            <?php if(count($blogs) > 0): ?>
                <?php foreach($blogs as $blog): ?>
                    <div class="product-card glass-panel gsap-stagger-item" style="text-align: left; padding: 25px;">
                        <?php if($blog['image']): ?>
                            <img src="<?php echo htmlspecialchars($blog['image']); ?>" alt="<?php echo htmlspecialchars($blog['title']); ?>" style="width: 100%; height: 200px; object-fit: cover; border-radius: 10px; margin-bottom: 20px;">
                        <?php else: ?>
                            <div style="width: 100%; height: 200px; background: rgba(255,255,255,0.05); border-radius: 10px; margin-bottom: 20px; display: flex; align-items:center; justify-content:center;">
                                <span style="color: var(--text-secondary)">No Image</span>
                            </div>
                        <?php endif; ?>
                        
                        <p style="font-size: 0.8rem; color: var(--accent-primary); margin-bottom: 10px;"><?php echo date('M d, Y', strtotime($blog['created_at'])); ?></p>
                        <h3 style="font-size: 1.3rem; margin-bottom: 15px;"><?php echo htmlspecialchars($blog['title']); ?></h3>
                        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 0.9rem;">
                            <?php echo htmlspecialchars($blog['preview']); ?>
                        </p>
                        <a href="blog-detail?id=<?php echo $blog['id']; ?>" class="btn btn-secondary" style="padding: 8px 20px; font-size: 0.9rem;">Read More</a>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; color: var(--text-secondary); padding: 50px;">
                    <p>No articles published yet. Check back later!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php include 'footer.php'; ?>
