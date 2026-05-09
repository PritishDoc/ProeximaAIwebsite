document.addEventListener('DOMContentLoaded', () => {
    // 1. Navbar Scroll Effect
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // 2. Intersection Observer for Scroll Animations (Fade Up)
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const observer = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                // Optional: Stop observing once animated to keep it visible
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const fadeElements = document.querySelectorAll('.fade-up');
    fadeElements.forEach(el => {
        observer.observe(el);
    });

    // 3. Initialize Swiper Slider
    const heroSwiper = new Swiper('.swiper', {
        loop: true,
        speed: 1000,
        autoplay: {
            delay: 5000,
            disableOnInteraction: false,
        },
        effect: 'slide',
        pagination: {
            el: '.swiper-pagination',
            clickable: true,
        },
        navigation: {
            nextEl: '.swiper-button-next',
            prevEl: '.swiper-button-prev',
        },
        on: {
            init: function () {
                revealSlideContent(this);
            },
            slideChangeTransitionStart: function () {
                // Reset animations for other slides if needed
                const allFadeElements = document.querySelectorAll('.hero .fade-up');
                allFadeElements.forEach(el => el.classList.remove('visible'));
            },
            slideChangeTransitionEnd: function () {
                revealSlideContent(this);
            }
        }
    });

    function revealSlideContent(swiper) {
        const activeSlide = swiper.slides[swiper.activeIndex];
        if (activeSlide) {
            const elements = activeSlide.querySelectorAll('.fade-up');
            elements.forEach((el, index) => {
                // Add a small delay for each element in the slide
                setTimeout(() => {
                    el.classList.add('visible');
                }, index * 100);
            });
        }
    }
    
    console.log("Swiper running");

    // 4. Mobile Menu Toggle 
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            const nav = document.querySelector('.nav-links');
            const actions = document.querySelector('.nav-actions');
            nav.classList.toggle('active');
            if(actions) actions.classList.toggle('active');
            
            // Toggle icon if phosphor icon is present
            const icon = mobileMenuBtn.querySelector('i');
            if(icon && nav.classList.contains('active')) {
                icon.classList.replace('ph-list', 'ph-x');
            } else if (icon) {
                icon.classList.replace('ph-x', 'ph-list');
            }
        });
    }

    // 5. Mouse Parallax Effect for Interactive Images
    document.addEventListener('mousemove', (e) => {
        const containers = document.querySelectorAll('[data-parallax-container]');
        
        containers.forEach(container => {
            const rect = container.getBoundingClientRect();
            
            // Check if container is in viewport or roughly nearby
            if (rect.top < window.innerHeight && rect.bottom > 0) {
                // Get mouse position relative to container center
                const x = e.clientX - rect.left - rect.width / 2;
                const y = e.clientY - rect.top - rect.height / 2;
                
                const img = container.querySelector('.parallax-img');
                if (img) {
                    const speed = img.getAttribute('data-speed') || 0.02;
                    const moveX = x * speed;
                    const moveY = y * speed;
                    img.style.transform = `translate(${moveX}px, ${moveY}px) scale(1.02)`;
                }
            }
        });
    });
});
