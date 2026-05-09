<?php 
require_once 'includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if(empty($name) || empty($email) || empty($message)){
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO contacts (name, email, phone, message) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$name, $email, $phone, $message])){
            $success = "Thank you for reaching out! We'll get back to you soon.";
        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}

include 'header.php'; 
include 'nav.php';
?>

<style>
    .contact-grid {
        display: grid;
        grid-template-columns: 1.2fr 0.8fr;
        gap: 60px;
        align-items: start;
    }
    .form-group { margin-bottom: 25px; }
    .form-group label {
        display: block; 
        margin-bottom: 12px; 
        font-weight: 700; 
        font-size: 0.75rem; 
        color: var(--text-primary);
        text-transform: uppercase;
        letter-spacing: 1.5px;
    }
    .form-control {
        width: 100%;
        padding: 18px 24px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--border-glass);
        color: #fff;
        border-radius: 16px;
        font-family: inherit;
        outline: none;
        transition: var(--transition-medium);
        font-size: 1rem;
    }
    .form-control:focus {
        border-color: var(--accent-primary);
        background: rgba(255,255,255,0.06);
        box-shadow: 0 0 20px rgba(255, 77, 148, 0.1);
    }
    .map-container {
        width: 100%; height: 500px;
        border-radius: var(--panel-radius);
        overflow: hidden;
        border: 1px solid var(--border-glass);
        box-shadow: 0 20px 40px rgba(0,0,0,0.3);
    }
    .contact-info-list { 
        margin-top: 40px; 
        list-style: none;
    }
    .contact-info-list li { 
        margin-bottom: 20px; 
        display: flex; 
        align-items: center; 
        gap: 20px; 
        color: var(--text-secondary);
        font-weight: 500;
        font-size: 1.05rem;
    }
    .contact-info-icon {
        width: 50px;
        height: 50px;
        background: var(--accent-gradient);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #fff;
        flex-shrink: 0;
        box-shadow: var(--accent-glow);
    }
    
    @media (max-width: 991px) {
        .contact-grid { grid-template-columns: 1fr; gap: 40px; }
        .map-container { height: 350px; }
    }
</style>

<section class="section" style="padding-top: 150px; padding-bottom: 150px;">
    <div class="container">
        
        <div class="gsap-reveal" style="text-align: center; margin-bottom: 80px;">
            <h1 class="section-title">Get in <span class="accent">Touch</span></h1>
            <p class="section-desc">Have a question or want to discuss a customized AI solution? We're here to help.</p>
        </div>

        <div class="contact-grid">
            <div class="glass-panel gsap-reveal">
                <h3 style="margin-bottom: 30px; font-weight: 800; text-transform: uppercase;">Send a Message</h3>
                
                <?php if($success): ?>
                    <div style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3); color: #10b981; padding: 20px; border-radius: 12px; margin-bottom: 30px; font-weight: 600;">
                        ✨ <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                <?php if($error): ?>
                    <div style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #ef4444; padding: 20px; border-radius: 12px; margin-bottom: 30px; font-weight: 600;">
                        ⚠️ <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="contact" method="POST">
                    <div class="form-group">
                        <label>Your Name</label>
                        <input type="text" name="name" class="form-control" placeholder="Enter Your Name" required>
                    </div>
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="Enter Your Email" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="text" name="phone" class="form-control" placeholder="Enter Your Phone Number">
                    </div>
                    <div class="form-group">
                        <label>Your Message</label>
                        <textarea name="message" class="form-control" rows="5" placeholder="How can we help you?" required></textarea>
                    </div>
                    <button type="submit" name="submit_contact" class="btn btn-primary" style="width: 100%; padding: 20px;">Send Message</button>
                </form>
            </div>
            
            <div class="gsap-reveal">
                <div class="map-container">
                    <iframe src="https://www.google.com/maps?q=Technology+Corridor,+Chandaka+Industrial+Estate,+Patia,+Chandrasekharpur,+Bhubaneswar+751024&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
                
                <ul class="contact-info-list" style="margin-top: 50px;">
                    <li>
                        <div class="contact-info-icon">📍</div>
                        <div>
                            <div style="color: #fff; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 4px;">Location</div>
                           Technology Corridor, Chandaka Industrial Estate, Patia, Chandrasekharpur, Bhubaneswar - 751024
                        </div>
                    </li>
                    <li>
                        <div class="contact-info-icon">📧</div>
                        <div>
                            <div style="color: #fff; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 4px;">Email Us</div>
                            info@proeximaai.com
                        </div>
                    </li>
                    <li>
                        <div class="contact-info-icon">📞</div>
                        <div>
                            <div style="color: #fff; font-weight: 700; text-transform: uppercase; font-size: 0.7rem; letter-spacing: 1px; margin-bottom: 4px;">Call Us</div>
                            +91 9777945470
                        </div>
                    </li>
                </ul>
            </div>
        </div>
        
    </div>
</section>

<?php include 'footer.php'; ?>
