        </div> <!-- end smooth-content -->
    </main> <!-- end smooth-wrapper -->

    <footer class="footer">
        <div class="container footer-container">
            <div class="footer-col brand-col">
                <a href="./">
                    <img src="Logo/2_TW_H.svg" alt="Proexima AI Logo" style="height: 50px; width: auto; object-fit: contain;">
                </a>
                <p style="margin-top: 15px; line-height: 1.6; color: var(--text-secondary);">Next-Gen AI Solutions for Smart Businesses. Empowering companies with intelligent automation.</p>
                <div class="social-links" style="margin-top: 25px; display: flex; gap: 15px;">
                    <a href="https://www.linkedin.com/company/proexima-ai/" aria-label="LinkedIn" style="width: 40px; height: 40px; border: 1px solid var(--border-glass); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.color='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-glass)'; this.style.color='inherit'"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" aria-label="Twitter" style="width: 40px; height: 40px; border: 1px solid var(--border-glass); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.color='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-glass)'; this.style.color='inherit'"><i class="fab fa-x-twitter"></i></a>
                    <a href="#" aria-label="Instagram" style="width: 40px; height: 40px; border: 1px solid var(--border-glass); border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: var(--transition-fast);" onmouseover="this.style.borderColor='var(--accent-primary)'; this.style.color='var(--accent-primary)'" onmouseout="this.style.borderColor='var(--border-glass)'; this.style.color='inherit'"><i class="fab fa-instagram"></i></a>
                </div>
            </div>
            <div class="footer-col">
                <h3 style="font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px; color: #fff;">Quick Links</h3>
                <div style="display: flex; flex-direction: column; gap: 12px; margin-top: 20px;">
                    <a href="about" style="color: var(--text-secondary); transition: var(--transition-fast);" onmouseover="this.style.color='var(--text-primary)'; this.style.paddingLeft='5px'" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0'">Our Journey</a>
                    <a href="./#products" style="color: var(--text-secondary); transition: var(--transition-fast);" onmouseover="this.style.color='var(--text-primary)'; this.style.paddingLeft='5px'" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0'">AI Products</a>
                    <a href="./#services" style="color: var(--text-secondary); transition: var(--transition-fast);" onmouseover="this.style.color='var(--text-primary)'; this.style.paddingLeft='5px'" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0'">Services</a>
                    <a href="contact" style="color: var(--text-secondary); transition: var(--transition-fast);" onmouseover="this.style.color='var(--text-primary)'; this.style.paddingLeft='5px'" onmouseout="this.style.color='var(--text-secondary)'; this.style.paddingLeft='0'">Contact Us</a>
                </div>
            </div>
            <div class="footer-col">
                <h3 style="font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px; color: #fff;">Contact</h3>
                <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 20px; color: var(--text-secondary); font-size: 0.95rem;">
                    <p>Email: <a href="mailto:info@proeximaai.com" style="color: inherit;">info@proeximaai.com</a></p>
                    <p>Phone: <br>+91 9777945470</p>
                    <p>Technology Corridor, Chandaka Industrial Estate, Patia, Chandrasekharpur, Bhubaneswar - 751024</p>
                </div>
            </div>
            <div class="footer-col newsletter-col">
                <h3 style="font-weight: 800; text-transform: uppercase; font-size: 1rem; letter-spacing: 1px; color: #fff;">Newsletter</h3>
                <p style="margin-top: 20px; color: var(--text-secondary); font-size: 0.95rem;">Subscribe to our AI newsletter.</p>
                <form id="newsletterForm" class="newsletter-form" style="margin-top: 20px; display: flex; flex-direction: column; gap: 15px;">
                    <input type="email" name="newsletter_email" placeholder="Your Email" required style="width: 100%; padding: 15px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-glass); border-radius: 12px; color: #fff; outline: none;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; border-radius: 12px; padding: 15px;">Subscribe</button>
                    <div id="newsletterMsg" style="margin-top: 10px; font-size: 0.8rem; display: none;"></div>
                </form>
            </div>
        </div>
        <div class="footer-bottom" style="margin-top: 80px; padding-top: 30px; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; color: var(--text-secondary); font-size: 0.85rem;">
            <p>&copy; <?php echo date('Y'); ?> Proexima AI. All Rights Reserved.</p>
        </div>
    </footer>

    <!-- GSAP & Plugins -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"></script>
    <script src="https://unpkg.com/@studio-freight/lenis@1.0.34/dist/lenis.min.js"></script>
    
    <!-- Custom JS -->
    <script src="js/main.js"></script>
    <script>
    document.getElementById('newsletterForm')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const msg = document.getElementById('newsletterMsg');
        const email = this.querySelector('input').value;
        
        fetch('newsletter-handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'email=' + encodeURIComponent(email)
        })
        .then(response => response.json())
        .then(data => {
            msg.style.display = 'block';
            msg.style.color = data.status === 'success' ? '#10b981' : '#ef4444';
            msg.innerText = data.message;
            if(data.status === 'success') this.reset();
        });
    });
    </script>
</body>
</html>
