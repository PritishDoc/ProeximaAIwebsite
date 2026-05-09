<?php include 'header.php'; ?>
<?php include 'nav.php'; ?>

<!-- ABOUT HERO -->
<style>
    .about-hero {
        padding: 180px 0 100px;
        background: radial-gradient(circle at 50% 0%, rgba(99, 102, 241, 0.15) 0%, var(--bg-dark) 70%);
        text-align: center;
    }

    .about-hero h1 {
        font-size: 5rem;
        margin-bottom: 25px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: -2px;
    }

    .about-hero p {
        font-size: 1.2rem;
        color: var(--text-secondary);
        max-width: 700px;
        margin: 0 auto;
    }

    .about-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 80px;
        align-items: center;
    }

    .about-grid img {
        width: 100%;
        height: 500px;
        object-fit: cover;
        opacity: 0.9;
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 140px 0 60px;
        }

        .about-hero h1 {
            font-size: 3rem;
        }

        .about-grid {
            grid-template-columns: 1fr;
            gap: 40px;
        }

        .about-grid img {
            height: 350px;
        }
    }
</style>

<section class="about-hero">
    <div class="container gsap-reveal">
        <div class="hero-pretitle">The Story Behind Proexima</div>
        <h1>Our <span class="accent">Journey</span></h1>
        <p>We are a team of visionaries, engineers, and creators dedicated to making the power of Artificial
            Intelligence accessible to every ambitious enterprise.</p>
    </div>
</section>

<!-- COMPANY STORY -->
<section class="section">
    <div class="container">
        <div class="about-grid">
            <div class="gsap-reveal">
                <h2 class="section-title" style="text-align: left;">From Vision to <span class="accent">Impact</span>
                </h2>
                <div style="color: var(--text-secondary); line-height: 1.8;">
                    <p style="margin-bottom: 20px; ">Founded in 2021 by a group of passionate data
                        scientists and software architects, Proexima AI began with a simple but powerful observation:
                        most businesses were struggling to keep up with the breakneck speed of AI advancement. The
                        barrier to entry was too high, and the solutions available were too rigid for the dynamic needs
                        of modern enterprise.</p>
                    <p class="about-collapsible" style="margin-bottom: 20px;">We set out to build a different kind of studio—one that combines the
                        agility of a specialized boutique with the robustness and security of a global enterprise
                        partner. Our early breakthrough in autonomous logistics processing set the stage for our
                        expansion into healthcare, finance, and creative technologies.</p>
                    <p class="about-collapsible" style="margin-bottom: 20px;">Today, Proexima AI serves a diverse portfolio of clients across
                        three continents, providing the underlying intelligence that fuels their proprietary growth
                        engines. We are more than just a vendor; we are an extension of your innovation team.</p>
                    <p class="about-collapsible">Our philosophy is simple: **Intelligence should be invisible.** It shouldn't be a hurdle to
                        overcome; it should be the silent force that empowers your people to do their best work and
                        focus on what truly matters.</p>
                        
                    <div class="mobile-view-btn-container d-mobile-only" id="about-toggle-container">
                        <button class="btn btn-primary" id="about-toggle-btn">View More &rarr;</button>
                    </div>
                </div>
            </div>
            <div class="gsap-reveal" style="position: relative;">
                <div class="glass-panel"
                    style="padding: 0; overflow: hidden; border-radius: 30px; transform: rotate(2deg);">
                    <img src="https://images.unsplash.com/photo-1522071820081-009f0129c71c?auto=format&fit=crop&q=80&w=800"
                        alt="Team Work">
                </div>
                <div class="glass-panel"
                    style="position: absolute; top: -30px; right: -30px; padding: 25px 35px; border-radius: 20px; border-color: rgba(0, 229, 255, 0.4); background: rgba(18, 18, 18, 0.95);">
                    <h4 class="accent" style="font-size: 2rem; margin-bottom: 0; font-weight: 900;">150+</h4>
                    <p
                        style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 2px; margin: 0; font-weight: 700;">
                        Global Partners</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FOUNDER & CEO SECTION -->
<style>
    .founder-section {
        background: rgba(255, 255, 255, 0.01);
        border-top: 1px solid var(--border-glass);
        border-bottom: 1px solid var(--border-glass);
        padding: 150px 0;
    }

    .founder-card {
        display: grid;
        grid-template-columns: 450px 1fr;
        gap: 80px;
        align-items: center;
    }

    .founder-image {
        position: relative;
    }

    .founder-image img {
        width: 100%;
        border-radius: 30px;
        filter: grayscale(10%) contrast(105%);
        transition: var(--transition-medium);
        box-shadow: 0 30px 60px rgba(0, 0, 0, 0.5);
    }

    .founder-image:hover img {
        filter: grayscale(0%) contrast(100%);
        transform: scale(1.02);
    }

    .founder-image::after {
        content: '';
        position: absolute;
        bottom: -25px;
        right: -25px;
        width: 100%;
        height: 100%;
        border: 2px solid var(--accent-gradient);
        border-radius: 30px;
        z-index: -1;
        opacity: 0.5;
    }

    .founder-info h3 {
        font-size: 3.5rem;
        margin-bottom: 10px;
        font-weight: 900;
        text-transform: uppercase;
        letter-spacing: -1px;
    }

    .founder-role {
        color: var(--accent-primary);
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-size: 0.8rem;
        margin-bottom: 30px;
        display: block;
    }

    .founder-quote {
        font-size: 1.6rem;
        font-weight: 600;
        line-height: 1.4;
        color: #fff;
        margin-bottom: 35px;
        position: relative;
        padding-left: 40px;
        border-left: 4px solid var(--accent-secondary);
    }

    .founder-bio {
        color: var(--text-secondary);
        line-height: 1.8;
        font-size: 1.05rem;
    }

    .founder-social {
        display: flex;
        gap: 15px;
        margin-top: 40px;
    }

    .founder-social a {
        width: 45px;
        height: 45px;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-glass);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        transition: var(--transition-fast);
        color: var(--text-secondary);
    }

    .founder-social a:hover {
        background: var(--accent-gradient);
        color: #fff;
        border-color: transparent;
        transform: translateY(-5px);
        box-shadow: var(--accent-glow);
    }

    @media (max-width: 991px) {
        .founder-card {
            grid-template-columns: 1fr;
            gap: 40px;
            text-align: center;
        }

        .founder-info h3 {
            font-size: 2.5rem;
        }

        .founder-quote {
            font-size: 1.2rem;
            border-left: none;
            padding-left: 0;
            text-align: center;
        }

        .founder-social {
            justify-content: center;
        }
    }
</style>

<section class="founder-section" id="founder">
    <div class="container">
        <div class="founder-card gsap-reveal">
            <div class="founder-image">
                <img src="images/images1.webp" alt="Pritish Kumar Ray - Founder & CEO">
            </div>
            <div class="founder-info">
                <span class="founder-role">Architect & Visionary</span>
                <h3>Pritish Kumar <span class="accent">Ray</span></h3>
                <p class="founder-quote">"Our mission is not to replace human potential, but to amplify it through the
                    most advanced cognitive tools ever created."</p>
                <div style="margin-bottom: 25px;">
                    <button class="btn btn-primary" id="founder-toggle-btn">Know More &rarr;</button>
                </div>
 <div class="founder-bio" id="founder-bio-content" style="display: none;">
    <p style="margin-bottom: 20px;">
        Pritish Kumar Ray is the founder of Proexima AI, established with a bold vision to make advanced, specialized AI accessible to businesses of all sizes. With over five years of experience in distributed systems and neural networks, he has built intelligent platforms designed to solve complex, real-world challenges with precision and scale.
    </p>
    <p style="margin-bottom: 20px;">
        Prior to founding Proexima, Pritish served as a Lead Architect at a Fortune 500 technology company, where he led the development of high-impact, AI-driven systems. Recognizing the gap between enterprise-grade capabilities and agile innovation, he launched Proexima to bridge that divide combining deep technical expertise with forward-thinking research.
    </p>
    <p>
        Under his leadership, Proexima AI is emerging as a next-generation AI studio focused on delivering meaningful, human-centric solutions. Pritish is also an advocate for ethical AI and frequently contributes to discussions on responsible technology, championing a “Human-First” approach where AI empowers creativity rather than replaces it.
    </p>
</div>
                <div class="founder-social">
                    <a href="https://www.linkedin.com/in/pritishray/" aria-label="LinkedIn"><i
                            class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Twitter"><i class="fab fa-x-twitter"></i></a>
                    <a href="mailto: info@proeximaai.com" aria-label="Email"><i class="fas fa-envelope"></i></a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- VALUES / STATS -->
<section class="section">
    <div class="container">
        <div style="text-align: center; margin-bottom: 80px;" class="gsap-reveal">
            <h2 class="section-title">Driven by <span class="accent">Excellence</span></h2>
            <p class="section-desc">Our core pillars that guide every line of code we write and every model we train.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px;"
            class="gsap-stagger-container">
            <div class="glass-panel gsap-stagger-item" style="padding: 60px 40px; text-align: center;">
                <div
                    style="width: 70px; height: 70px; background: var(--accent-gradient); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff; margin: 0 auto 30px; box-shadow: var(--accent-glow);">
                    💎</div>
                <h4 style="font-size: 1.6rem; font-weight: 800; text-transform: uppercase;">Premium Quality</h4>
                <p style="color: var(--text-secondary); font-size: 1rem; line-height: 1.7;">We don't do "minimum
                    viable." We build products that are polished, performant, and premium, ensuring every pixel serves a
                    purpose.</p>
            </div>
            <div class="glass-panel gsap-stagger-item" style="padding: 60px 40px; text-align: center;">
                <div
                    style="width: 70px; height: 70px; background: var(--accent-gradient); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff; margin: 0 auto 30px; box-shadow: var(--accent-glow);">
                    ⚡</div>
                <h4 style="font-size: 1.6rem; font-weight: 800; text-transform: uppercase;">Speed of Light</h4>
                <p style="color: var(--text-secondary); font-size: 1rem; line-height: 1.7;">In the competitive AI world,
                    speed is everything. Our proprietary stack is optimized for ultra-low latency and rapid massive
                    deployment.</p>
            </div>
            <div class="glass-panel gsap-stagger-item" style="padding: 60px 40px; text-align: center;">
                <div
                    style="width: 70px; height: 70px; background: var(--accent-gradient); border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: #fff; margin: 0 auto 30px; box-shadow: var(--accent-glow);">
                    🛡️</div>
                <h4 style="font-size: 1.6rem; font-weight: 800; text-transform: uppercase;">Trust & Privacy</h4>
                <p style="color: var(--text-secondary); font-size: 1rem; line-height: 1.7;">Your data is your most
                    valuable asset. We treat it with institutional-grade security standards and full transparency at
                    every turn.</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA -->
<style>
    .about-cta-title { font-size: 3rem; margin-bottom: 30px; }
    .about-cta-btn { padding: 16px 40px; font-size: 1.1rem; }
    @media (max-width: 768px) {
        .about-cta-title { font-size: 2.2rem; }
        .about-cta-btn { padding: 12px 24px; font-size: 1rem; }
    }
</style>
<section class="section" style="background: linear-gradient(0deg, rgba(99,102,241,0.05) 0%, transparent 100%);">
    <div class="container text-center gsap-reveal">
        <h2 class="about-cta-title">Ready to Write Your <span class="accent">Success Story?</span>
        </h2>
        <a href="quote" class="btn btn-primary about-cta-btn">Partner with Us</a>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const toggleBtn = document.getElementById('about-toggle-btn');
    if(toggleBtn) {
        let isExpanded = false;
        const collapsibleParagraphs = document.querySelectorAll('.about-collapsible');
        
        collapsibleParagraphs.forEach(p => p.classList.add('mobile-hidden'));
        
        toggleBtn.addEventListener('click', function() {
            isExpanded = !isExpanded;
            collapsibleParagraphs.forEach(p => {
                if(isExpanded) {
                    p.classList.remove('mobile-hidden');
                } else {
                    p.classList.add('mobile-hidden');
                }
            });
            toggleBtn.innerHTML = isExpanded ? 'View Less &uarr;' : 'View More &rarr;';
            if (typeof ScrollTrigger !== 'undefined') {
                setTimeout(() => ScrollTrigger.refresh(), 100);
            }
        });
    }

    const founderToggleBtn = document.getElementById('founder-toggle-btn');
    const founderBio = document.getElementById('founder-bio-content');
    if(founderToggleBtn && founderBio) {
        let founderExpanded = false;
        
        founderToggleBtn.addEventListener('click', function() {
            founderExpanded = !founderExpanded;
            if (founderExpanded) {
                founderBio.style.display = 'block';
            } else {
                founderBio.style.display = 'none';
            }
            founderToggleBtn.innerHTML = founderExpanded ? 'Show Less &uarr;' : 'Know More &rarr;';
            
            if (typeof ScrollTrigger !== 'undefined') {
                setTimeout(() => ScrollTrigger.refresh(), 100);
            }
        });
    }
});
</script>

<?php include 'footer.php'; ?>