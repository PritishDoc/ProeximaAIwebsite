<?php 
// Internal Routing for PHP Built-In Server or XAMPP wildcard fallbacks
$path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
// Base path setup if running from a subfolder like /ProeximaAI
$basePath = '/ProeximaAI'; 
if (strpos($path, $basePath) === 0) {
    $path = substr($path, strlen($basePath));
}
if ($path !== '/' && $path !== '/index.php' && $path !== '') {
    $file = __DIR__ . $path . '.php';
    if (file_exists($file)) {
        require $file;
        exit;
    }
}
?>
<?php include 'header.php'; ?>
<?php include 'nav.php'; ?>

<!-- HERO SECTION -->
<style>
    .hero {
        min-height: 100vh;
        display: flex;
        align-items: center;
        position: relative;
        overflow: hidden;
        padding-top: 100px;
    }
    .hero-bg {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: radial-gradient(circle at top center, rgba(99, 102, 241, 0.15) 0%, var(--bg-dark) 60%);
        z-index: -1;
    }
    .hero-bg::after {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg"><filter id="noiseFilter"><feTurbulence type="fractalNoise" baseFrequency="0.65" numOctaves="3" stitchTiles="stitch"/></filter><rect width="100%" height="100%" opacity="0.05" filter="url(%23noiseFilter)"/></svg>');
        opacity: 0.4;
        z-index: -1;
        pointer-events: none;
    }
    .hero-content {
        max-width: 900px;
        margin: 0 auto;
        text-align: center;
        position: relative;
        z-index: 1;
    }
    .hero-pretitle {
        color: var(--accent-primary);
        font-weight: 700;
        font-size: 0.75rem;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 30px;
        display: inline-flex;
        align-items: center;
        padding: 10px 24px;
        background: rgba(255, 77, 148, 0.05);
        border: 1px solid rgba(255, 77, 148, 0.15);
        backdrop-filter: blur(10px);
        border-radius: 50px;
    }
    .hero-title {
        font-size: 6rem;
        font-weight: 900;
        margin-bottom: 35px;
        line-height: 0.95;
        letter-spacing: -3px;
        text-transform: uppercase;
    }
    .hero-desc {
        font-size: 1.35rem;
        color: var(--text-secondary);
        margin-bottom: 50px;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        line-height: 1.5;
        font-weight: 500;
    }
    .hero-btns {
        display: flex;
        gap: 25px;
        justify-content: center;
        align-items: center;
    }
    /* Logos */
    .client-logos {
        margin-top: 100px;
        padding-top: 50px;
        border-top: 1px solid var(--border-glass);
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 60px;
        opacity: 0.3;
        flex-wrap: wrap;
    }
    .client-logos span { font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; color: #fff; letter-spacing: -0.5px; }
    
    @media (max-width: 768px) {
        .hero { padding-top: 120px; }
        .hero-btns { flex-direction: column; gap: 15px; }
        .hero-btns .btn { width: 100%; }
        .client-logos { gap: 30px; margin-top: 60px; }
        .client-logos span { font-size: 1.1rem; }
    }
</style>

<section class="hero" id="home">
    <div class="hero-bg"></div>
    <div class="container hero-content gsap-reveal">
        <div class="hero-pretitle">✨ Next-Generation AI Studio</div>
        <h1 class="hero-title">Empower the <br><span class="accent">AI Future</span></h1>
        <p class="hero-desc">We build enterprise-grade AI solutions that transform how modern businesses scale and operate in a digital-native world.</p>
        <div class="hero-btns">
            <a href="quote" class="btn btn-primary" style="padding: 18px 45px; font-size: 1rem;">Work With Us</a>
            <a href="#services" class="btn btn-secondary" style="padding: 18px 45px; font-size: 1rem;">Our Services</a>
        </div>
        
        <div class="client-logos gsap-reveal">
            <span>TECHCORP</span>
            <span>INNOVATE AI</span>
            <span>CLOUDSYNC</span>
            <span>DATAFLOW</span>
            <span>NEXTGEN</span>
        </div>
    </div>
</section>

<!-- MISSION SECTION (Teaser) -->
<style>
    .mission-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 80px;
        align-items: center;
    }
    .mission-subtitle {
        color: var(--accent-primary);
        font-weight: 800;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-size: 0.8rem;
        margin-bottom: 20px;
        display: block;
    }
    .mission-content h2 {
        font-size: 4rem;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 30px;
        text-transform: uppercase;
        letter-spacing: -2px;
    }
    .mission-feature {
        display: flex;
        gap: 25px;
        margin-top: 40px;
    }
    .mission-feature-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 77, 148, 0.1);
        border: 1px solid rgba(255, 77, 148, 0.2);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        color: var(--accent-primary);
        flex-shrink: 0;
    }
    .mission-feature-text h4 { margin-bottom: 8px; font-size: 1.25rem; font-weight: 700; text-transform: uppercase; }
    .mission-feature-text p { font-size: 0.95rem; color: var(--text-secondary); line-height: 1.6; }

    .mission-visual {
        position: relative;
        padding: 30px;
    }
    .mission-visual::before {
        content: '';
        position: absolute;
        top: 0; right: 0; width: 90%; height: 90%;
        background: var(--accent-gradient);
        filter: blur(120px);
        opacity: 0.15;
        z-index: -1;
    }
    .experience-badge {
        position: absolute;
        bottom: 50px;
        left: -30px;
        background: rgba(18, 18, 18, 0.9);
        backdrop-filter: blur(15px);
        border: 1px solid var(--border-glass);
        padding: 25px 35px;
        border-radius: 20px;
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }
    .experience-badge h3 { font-size: 2.5rem; color: var(--accent-primary); margin-bottom: 0; font-weight: 900; }
    .experience-badge p { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 2px; font-weight: 700; }

    @media (max-width: 991px) {
        .mission-grid { grid-template-columns: 1fr; gap: 50px; }
        .experience-badge { left: 20px; bottom: 20px; padding: 15px 25px; }
        .mission-content h2 { font-size: 2.8rem; }
        .mission-feature { flex-direction: column; text-align: center; align-items: center; }
        .mission-feature-icon { margin-bottom: 10px; }
    }
</style>

<section class="section" id="about">
    <div class="container mission-grid">
        <div class="mission-content gsap-reveal">
            <span class="mission-subtitle">Our Purpose</span>
            <h2 class="section-title" style="text-align: left; margin-bottom: 25px;">Pioneering the <span class="accent">AI Revolution</span></h2>
            <p style="color: var(--text-secondary); font-size: 1.1rem; line-height: 1.8; margin-bottom: 30px;">
                Proexima AI isn't just a development studio; we're architects of the future. We bridge the gap between complex AI research and practical, high-impact business solutions.
            </p>
            
            <div class="mission-feature">
                <div class="mission-feature-icon">🚀</div>
                <div class="mission-feature-text">
                    <h4>Rapid Transformation</h4>
                    <p>Deploy enterprise-grade AI systems in weeks, not months.</p>
                </div>
            </div>
            
            <div class="mission-feature">
                <div class="mission-feature-icon">🛡️</div>
                <div class="mission-feature-text">
                    <h4>Ethical & Secure</h4>
                    <p>Built-in privacy and security at the core of every model we train.</p>
                </div>
            </div>

            <div style="margin-top: 40px; text-align: center;">
                <a href="about" class="btn btn-primary">Learn Our Story</a>
            </div>
        </div>
        
        <div class="mission-visual gsap-reveal">
            <div class="glass-panel" style="padding: 0; overflow: hidden; border-radius: 30px;">
                 <img src="images/image2.avif" alt="AI Concept" style="width: 100%; height: 500px; object-fit: cover; opacity: 0.8;">
            </div>
            <div class="experience-badge">
                <h3 class="accent">05+</h3>
                <p>Years Excellence</p>
            </div>
        </div>
    </div>
</section>

<!-- PRODUCTS SECTION -->
<style>
    .products-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 30px;
    }
    .product-card {
        padding: 40px 30px;
        text-align: center;
        transition: var(--transition-medium);
        position: relative;
        overflow: hidden;
    }
    .product-card:hover {
        transform: translateY(-10px);
        background: var(--bg-card-hover);
        border-color: var(--accent-primary);
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.2);
    }
    .product-icon {
        font-size: 3rem;
        margin-bottom: 20px;
        color: var(--accent-primary);
    }
.product-card h3 {
    font-size: 1.5rem;
    margin-bottom: 15px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: -0.5px;
}
    .product-card p {
        color: var(--text-secondary);
        font-size: 0.95rem;
        margin-bottom: 20px;
    }
    .product-features {
        list-style: none;
        margin-bottom: 25px;
        text-align: left;
    }
    .product-features li {
        font-size: 0.85rem;
        color: var(--text-secondary);
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .product-features li::before {
        content: "✓";
        color: var(--accent-secondary);
        font-weight: bold;
    }
    .product-hidden {
        display: none !important;
    }
</style>

<section class="section" id="products">
    <div class="container">
        <div class="gsap-reveal">
            <h2 class="section-title">Our <span class="accent">AI Products</span></h2>
            <p class="section-desc">Discover our suite of premium AI-powered tools designed to scale your business operations seamlessly.</p>
        </div>
        
        <div class="products-grid gsap-stagger-container">
            <!-- Product 1 -->
            <div class="product-card glass-panel gsap-stagger-item">
                <div class="product-icon">📊</div>
                <h3>AI CRM System</h3>
                <p>Intelligent customer relationship management that predicts behavior and automates follow-ups.</p>
                <ul class="product-features">
                    <li>Predictive Analytics</li>
                    <li>Automated Workflows</li>
                    <li>Omnichannel Support</li>
                </ul>
                <a href="quote" class="btn btn-secondary" style="width: 100%; justify-content: center;">Learn More</a>
            </div>
            
            <!-- Product 2 -->
            <div class="product-card glass-panel gsap-stagger-item">
                <div class="product-icon">👤</div>
                <h3>Smart Attendance</h3>
                <p>Touchless, face-recognition and GPS-enabled attendance management for modern teams.</p>
                <ul class="product-features">
                    <li>Face Recognition</li>
                    <li>GPS Geofencing</li>
                    <li>Real-time Reports</li>
                </ul>
                <a href="quote" class="btn btn-secondary" style="width: 100%; justify-content: center;">Learn More</a>
            </div>
            
            <!-- Product 3 -->
            <div class="product-card glass-panel gsap-stagger-item">
                <div class="product-icon">🤖</div>
                <h3>AI Chatbot</h3>
                <p>Automate 80% of your customer support with native language processing bots.</p>
                <ul class="product-features">
                    <li>NLP Integration</li>
                    <li>24/7 Availability</li>
                    <li>Multi-language</li>
                </ul>
                <a href="quote" class="btn btn-secondary" style="width: 100%; justify-content: center;">Learn More</a>
            </div>
            
            <!-- Product 4 -->
            <div class="product-card glass-panel gsap-stagger-item product-collapsible">
                <div class="product-icon">✨</div>
                <h3 class="product-title">Logo Design AI</h3>
                <p class="product-desc">Generate stunning, brand-compliant logos instantly with our generative AI models.</p>
                <ul class="product-features">
                    <li>Instant Generation</li>
                    <li>Vector Export</li>
                    <li>Brand Consistency</li>
                </ul>
                <a href="quote" class="btn btn-secondary" style="width: 100%; justify-content: center;">Learn More</a>
            </div>
            
            <!-- Product 5 -->
            <div class="product-card glass-panel gsap-stagger-item product-collapsible">
                <div class="product-icon">🚦</div>
                <h3 class="product-title">Smart Vehicle QR</h3>
                <p class="product-desc">Intelligent AI-powered QR scanning system to streamline traffic flow, automate toll collection, and manage smart parking securely.</p>
                <ul class="product-features">
                    <li>Instant Vehicle ID</li>
                    <li>Automated Tolls</li>
                    <li>Traffic Analytics</li>
                </ul>
                <a href="quote" class="btn btn-secondary" style="width: 100%; justify-content: center;">Learn More</a>
            </div>
        </div>

        <div class="view-btn-container text-center" style="margin-top: 50px;" id="products-toggle-container">
            <button class="btn btn-primary" id="products-toggle-btn">View More &rarr;</button>
        </div>
    </div>
</section>

<!-- SERVICES SECTION -->
<style>
    .services-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }
    .service-card {
        display: flex;
        flex-direction: column;
        padding: 50px 40px;
        border-radius: var(--panel-radius);
        background: rgba(255, 255, 255, 0.02);
        border: 1px solid var(--border-glass);
        transition: var(--transition-medium);
        height: 100%;
    }
    .service-card:hover {
        background: rgba(255, 255, 255, 0.05);
        border-color: var(--accent-primary);
        transform: translateY(-10px);
        box-shadow: var(--accent-glow);
    }
    .service-icon {
        width: 70px;
        height: 70px;
        background: var(--accent-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: #fff;
        margin-bottom: 30px;
        box-shadow: var(--accent-glow);
    }
    .service-card h4 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: -1px;
        line-height: 1.1;
    }
    .service-card p {
        color: var(--text-secondary);
        font-size: 1.05rem;
        line-height: 1.7;
        margin-bottom: 30px;
        flex-grow: 1;
    }
    
    @media (max-width: 1100px) {
        .services-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
        .services-grid { grid-template-columns: 1fr; }
        .service-card { padding: 40px 30px; text-align: center; align-items: center; }
        .service-icon { margin-left: auto; margin-right: auto; }
    }
</style>

<section class="section" id="services" style="background: rgba(255,255,255,0.01); border-top: 1px solid var(--border-glass);">
    <div class="container">
        <div class="gsap-reveal" style="text-align: center; margin-bottom: 60px;">
            <div class="hero-pretitle" style="font-size: 0.8rem;">Our Capabilities</div>
            <h2 class="section-title">Advanced Technology <span class="accent">Solutions</span></h2>
            <p class="section-desc">Comprehensive digital transformation services powering next-generation enterprises.</p>
        </div>
        
        <div class="services-grid gsap-stagger-container">
            <!-- App Development -->
            <div class="service-card gsap-stagger-item">
                <div class="service-icon">📱</div>
                <h4>App Development</h4>
                <p>High-performance native and cross-platform mobile applications built with React Native and Flutter for seamless user experiences.</p>
                <a href="quote" class="btn btn-secondary">Get Started</a>
            </div>

            <!-- AI Integration -->
            <div class="service-card gsap-stagger-item">
                <div class="service-icon">🧠</div>
                <h4>AI Integration</h4>
                <p>Embed advanced machine learning models natively into your existing architecture to automate complex business logic.</p>
                <a href="quote" class="btn btn-secondary">Get Started</a>
            </div>

            <!-- Web Development -->
            <div class="service-card gsap-stagger-item">
                <div class="service-icon">💻</div>
                <h4>Web Solutions</h4>
                <p>Scalable, secure, and blazing fast web applications built on modern stacks like Next.js and MERN for high-velocity growth.</p>
                <a href="quote" class="btn btn-secondary">Get Started</a>
            </div>

            <!-- Cloud & Security -->
            <div class="service-card gsap-stagger-item mobile-collapsible-service">
                <div class="service-icon">☁️</div>
                <h4>Cloud & Security</h4>
                <p>Bespoke cloud solutions and pipelines on highly available AWS/Azure infrastructures with zero-trust security at the core.</p>
                <a href="quote" class="btn btn-secondary">Get Started</a>
            </div>

            <!-- Digital Marketing -->
            <div class="service-card gsap-stagger-item mobile-collapsible-service">
                <div class="service-icon">📈</div>
                <h4>Digital Growth</h4>
                <p>Data-driven SEO, PPC, and social media campaigns engineered for exponential brand growth and market dominance.</p>
                <a href="quote" class="btn btn-secondary">Get Started</a>
            </div>

            <!-- Custom AI Models -->
            <div class="service-card gsap-stagger-item mobile-collapsible-service">
                <div class="service-icon">🤖</div>
                <h4>Custom AI Models</h4>
                <p>Training and deploying proprietary LLMs and generative models tailored to your specific industry data and requirements.</p>
                <a href="quote" class="btn btn-secondary">Get Started</a>
            </div>
        </div>
        
        <div class="mobile-view-btn-container d-mobile-only" id="services-toggle-container">
            <button class="btn btn-primary" id="services-toggle-btn">View More &rarr;</button>
        </div>
    </div>
</section>

<!-- CTA SECTION -->
<style>
    .cta-section { text-align: center; padding: 120px 0; }
    .cta-title { font-size: 3.5rem; margin-bottom: 20px; }
    .cta-desc { color: var(--text-secondary); max-width: 600px; margin: 0 auto 40px; font-size: 1.1rem; }
    .cta-btn { padding: 15px 40px; font-size: 1.2rem; }
    @media (max-width: 768px) {
        .cta-section { padding: 80px 0; }
        .cta-title { font-size: 2.5rem; }
        .cta-desc { font-size: 1rem; }
        .cta-btn { padding: 12px 24px; font-size: 1rem; }
    }
</style>
<section class="section cta-section">
    <div class="container gsap-reveal">
        <h2 class="cta-title">Ready to Scale?</h2>
        <p class="cta-desc">Join top-tier enterprises leveraging Proexima AI to disrupt their industries.</p>
        <a href="quote" class="btn btn-primary cta-btn">Start Your Transformation</a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('services-toggle-btn');
    if(toggleBtn) {
        let isExpanded = false;
        const collapsibleCards = document.querySelectorAll('.mobile-collapsible-service');
        
        collapsibleCards.forEach(card => card.classList.add('mobile-hidden'));
        
        toggleBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            collapsibleCards.forEach(card => {
                if(isExpanded) {
                    card.classList.remove('mobile-hidden');
                } else {
                    card.classList.add('mobile-hidden');
                }
            });
            toggleBtn.innerHTML = isExpanded ? 'View Less &uarr;' : 'View More &rarr;';
            if (typeof ScrollTrigger !== 'undefined') {
                setTimeout(() => ScrollTrigger.refresh(), 100);
            }
        });
    }

    const prodToggleBtn = document.getElementById('products-toggle-btn');
    const prodContainer = document.getElementById('products');
    if(prodToggleBtn) {
        let prodExpanded = false;
        const collapsibleCards = document.querySelectorAll('.product-collapsible');
        
        collapsibleCards.forEach(card => card.classList.add('product-hidden'));
        
        prodToggleBtn.addEventListener('click', function() {
            prodExpanded = !prodExpanded;
            collapsibleCards.forEach(card => {
                if(prodExpanded) {
                    card.classList.remove('product-hidden');
                } else {
                    card.classList.add('product-hidden');
                }
            });
            prodToggleBtn.innerHTML = prodExpanded ? 'View Less &uarr;' : 'View More &rarr;';
            
            if (!prodExpanded && prodContainer) {
                prodContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            
            if (typeof ScrollTrigger !== 'undefined') {
                setTimeout(() => ScrollTrigger.refresh(), 100);
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>
