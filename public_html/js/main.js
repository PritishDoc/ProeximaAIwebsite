 // Initialize Lenis for Smooth Scrolling
const lenis = new Lenis({
  duration: 1.2,
  easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)),
  smooth: true
});

lenis.on('scroll', ScrollTrigger.update);

gsap.ticker.add((time)=>{
  lenis.raf(time * 1000);
});
gsap.ticker.lagSmoothing(0, 0);

// Initialize GSAP and ScrollTrigger
gsap.registerPlugin(ScrollTrigger);

document.addEventListener("DOMContentLoaded", (event) => {
    
    // 1. Sticky Navbar on Scroll
    const navbar = document.querySelector('.navbar');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 2. Mobile Menu Toggle
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const navLinks = document.querySelector('.nav-links');
    
    if(mobileMenuBtn && navLinks) {
        mobileMenuBtn.addEventListener('click', () => {
            mobileMenuBtn.classList.toggle('active');
            navLinks.classList.toggle('active');
            document.body.style.overflow = navLinks.classList.contains('active') ? 'hidden' : '';
        });
        
        navLinks.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                mobileMenuBtn.classList.remove('active');
                navLinks.classList.remove('active');
                document.body.style.overflow = '';
            });
        });
    }

    // 3. 3D Parallax on Hero Section Background
    const heroBg = document.querySelector('.hero-bg');
    if (heroBg) {
        gsap.to(heroBg, {
            yPercent: 40,
            scale: 1.1,
            ease: "none",
            scrollTrigger: {
                trigger: '.hero',
                start: "top top",
                end: "bottom top",
                scrub: true
            }
        });
    }

    // 3. 3D Float Effect on Hero Content
    const heroContent = document.querySelector('.hero-content');
    if(heroContent){
        gsap.to(heroContent, {
            yPercent: 20,
            rotationX: 5,
            opacity: 0,
            transformPerspective: 1000,
            scrollTrigger: {
                trigger: '.hero',
                start: "top top",
                end: "bottom top",
                scrub: true
            }
        });
    }

    // 4. 3D Reveal for About Section Visual
    const aboutVisual = document.querySelector('.about-visual .glass-panel');
    if (aboutVisual) {
        gsap.set(aboutVisual, { perspective: 1000 });
        gsap.fromTo(aboutVisual, 
            { rotationX: 20, rotationY: -30, scale: 0.8, opacity: 0, z: -200 },
            {
                rotationX: 0, rotationY: 0, scale: 1, opacity: 1, z: 0,
                duration: 1.5,
                ease: "expo.out",
                scrollTrigger: {
                    trigger: '.about-grid',
                    start: "top 75%"
                }
            }
        );
    }

    // 5. Global Scroll Reveal on all Panels (Optimized for performance)
    // 5. Optimized Staggered Reveals
    const staggerContainers = document.querySelectorAll('.gsap-stagger-container');
    staggerContainers.forEach((container) => {
        gsap.fromTo(container.querySelectorAll('.gsap-stagger-item, .glass-panel'), 
            { y: 50, opacity: 0, scale: 0.95 },
            {
                y: 0, opacity: 1, scale: 1,
                duration: 1,
                stagger: 0.2,
                ease: "power2.out",
                scrollTrigger: {
                    trigger: container,
                    start: "top 85%",
                    toggleActions: "play none none reverse"
                }
            }
        );
    });

    // 6. Individual Reveals (for non-staggered elements)
    const independentReveals = document.querySelectorAll('.gsap-reveal:not(.gsap-stagger-item):not(.glass-panel)');
    independentReveals.forEach((el) => {
        if (!el.closest('.gsap-stagger-container')) {
            gsap.fromTo(el, 
                { opacity: 0, y: 30 },
                {
                    opacity: 1, y: 0,
                    duration: 0.8,
                    ease: "power2.out",
                    scrollTrigger: {
                        trigger: el,
                        start: "top 90%",
                        toggleActions: "play none none reverse"
                    }
                }
            );
        }
    });

    // 7. Premium 3D Tilt Effect for Cards
    const cards = document.querySelectorAll('.glass-panel');
    cards.forEach(card => {
        card.addEventListener('mousemove', (e) => {
            const rect = card.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            const centerX = rect.width / 2;
            const centerY = rect.height / 2;
            
            const rotateX = (y - centerY) / 10;
            const rotateY = (centerX - x) / 10;
            
            gsap.to(card, {
                rotationX: rotateX,
                rotationY: rotateY,
                duration: 0.5,
                ease: "power2.out",
                transformPerspective: 1000,
                overwrite: true
            });
        });
        
        card.addEventListener('mouseleave', () => {
            gsap.to(card, {
                rotationX: 0,
                rotationY: 0,
                duration: 0.5,
                ease: "power2.out",
                overwrite: true
            });
        });
    });
});
