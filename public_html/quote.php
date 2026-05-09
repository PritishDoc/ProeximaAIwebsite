<?php 
require_once 'includes/db.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_quote'])) {
    $business_type = trim($_POST['business_type']);
    $service = trim($_POST['service']);
    $budget = trim($_POST['budget']);
    $message = trim($_POST['message']); // Contact email to reach back, adding field logically

    // Adding email to quotes logic 
    // Wait, the DB scheme only has business_type, service, budget, message. Let's append contact details to message for now or add email.

    if(empty($business_type) || empty($service) || empty($budget)){
        $error = "Please fill in all required fields.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO quotes (business_type, service, budget, message) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$business_type, $service, $budget, $message])){
            $success = "Quote Request submitted! Our team will contact you shortly.";
            
            // mail() function implementation (will work on actual server)
            $to = "admin@proexima.ai";
            $subject = "New Quote Request - Proexima AI";
            $body = "New quote requested.\nBusiness Type: $business_type\nService: $service\nBudget: $budget\nDetails: $message";
            $headers = "From: noreply@proexima.ai";
            @mail($to, $subject, $body, $headers); // Supress warnings on local

        } else {
            $error = "Something went wrong. Please try again later.";
        }
    }
}

include 'header.php'; 
include 'nav.php';
?>

<style>
    .quote-container {
        max-width: 700px;
        margin: 0 auto;
    }
    .form-group { margin-bottom: 25px; }
    .form-group label {
        display: block; margin-bottom: 10px; font-weight: 500; color: #fff;
    }
    .form-control {
        width: 100%;
        padding: 15px;
        background: rgba(255,255,255,0.03);
        border: 1px solid var(--border-glass);
        color: #fff;
        border-radius: 10px;
        font-family: inherit;
        outline: none;
        transition: var(--transition-fast);
        appearance: none; /* for selects */
    }
    select.form-control {
        background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        background-size: 1em;
    }
    select.form-control option { background: var(--bg-dark); color: #fff; }
    .form-control:focus {
        border-color: var(--accent-primary);
        box-shadow: 0 0 10px rgba(99, 102, 241, 0.3);
    }
</style>

<section class="section" style="padding-top: 150px; min-height: 80vh;">
    <div class="container quote-container">
        
        <div class="gsap-reveal" style="text-align: center; margin-bottom: 50px;">
            <h1 class="section-title">Request a <span class="accent">Custom Quote</span></h1>
            <p class="section-desc">Tell us about your business and project scope. Our experts will craft a tailored AI solution for you.</p>
        </div>

        <div class="glass-panel gsap-reveal">
            <?php if($success): ?>
                <div style="background: rgba(16, 185, 129, 0.2); border: 1px solid #10b981; color: #10b981; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                    <strong>Success!</strong> <?php echo $success; ?>
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div style="background: rgba(239, 68, 68, 0.2); border: 1px solid #ef4444; color: #ef4444; padding: 20px; border-radius: 10px; margin-bottom: 20px; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="quote" method="POST">
                
                <div class="form-group">
                    <label>Business Type</label>
                    <select name="business_type" class="form-control" required>
                        <option value="" disabled selected>Select Business Type</option>
                        <option value="Enterprise">Enterprise Corp</option>
                        <option value="Startup">Startup / SME</option>
                        <option value="Agency">Agency</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Primary Service Required</label>
                    <select name="service" class="form-control" required>
                        <option value="" disabled selected>Select Service</option>
                        <option value="AI CRM System">AI CRM System</option>
                        <option value="Smart Attendance System">Smart Attendance System</option>
                        <option value="AI Chatbot Integration">AI Chatbot Integration</option>
                        <option value="Custom AI Development">Custom AI Development</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Estimated Project Budget (USD)</label>
                    <select name="budget" class="form-control" required>
                        <option value="" disabled selected>Select Budget Range</option>
                        <option value="< $5k">Less than $5,000</option>
                        <option value="$5k - $20k">$5k - $20k</option>
                        <option value="$20k - $50k">$20k - $50k</option>
                        <option value="$50k+">$50k+</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Project Details / Your Contact Email *</label>
                    <textarea name="message" class="form-control" rows="6" placeholder="Please describe your requirements and leave your email so we can reach you..." required></textarea>
                </div>

                <button type="submit" name="submit_quote" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 15px;">Submit Request &rarr;</button>
            </form>
        </div>
        
    </div>
</section>

<?php include 'footer.php'; ?>
