<?php
require_once '../db.php';

$success = false;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $college = trim($_POST['college'] ?? '');
    $qualification = trim($_POST['qualification'] ?? '');
    $course_branch = trim($_POST['course_branch'] ?? '');
    $semester = trim($_POST['semester'] ?? '');
    $program = trim($_POST['program'] ?? '');
    $terms = isset($_POST['terms']) ? 1 : 0;
    
    $resume_path = '';
    $payment_screenshot_path = '';

    if (isset($_FILES['resume'])) {
        $fileError = $_FILES['resume']['error'];
        if ($fileError === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['resume']['name']));
            $targetFile = $uploadDir . $fileName;
            $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
            
            if ($fileType === 'pdf') {
                if (move_uploaded_file($_FILES['resume']['tmp_name'], $targetFile)) {
                    $resume_path = 'uploads/' . $fileName;
                } else {
                    $error = 'Failed to save the uploaded resume to the server.';
                }
            } else {
                $error = 'Only PDF files are allowed. You uploaded a ' . strtoupper($fileType) . ' file.';
            }
        } elseif ($fileError === UPLOAD_ERR_INI_SIZE || $fileError === UPLOAD_ERR_FORM_SIZE) {
            $error = 'Your PDF is too large! Maximum allowed size is ' . ini_get('upload_max_filesize') . '.';
        } elseif ($fileError === UPLOAD_ERR_NO_FILE) {
            $error = 'Please select a PDF file to upload.';
        } else {
            $error = 'An unknown upload error occurred (Code: ' . $fileError . ').';
        }
    } else {
        $error = 'CV/Resume file input missing from the form.';
    }

    // Process payment screenshot
    if (!$error && isset($_FILES['payment_screenshot'])) {
        $imgError = $_FILES['payment_screenshot']['error'];
        if ($imgError === UPLOAD_ERR_OK) {
            $imgName = time() . '_pay_' . preg_replace("/[^a-zA-Z0-9.]/", "_", basename($_FILES['payment_screenshot']['name']));
            $targetImg = '../uploads/' . $imgName;
            $imgType = strtolower(pathinfo($targetImg, PATHINFO_EXTENSION));
            if (in_array($imgType, ['jpg', 'jpeg', 'png', 'webp', 'pdf'])) {
                if (move_uploaded_file($_FILES['payment_screenshot']['tmp_name'], $targetImg)) {
                    $payment_screenshot_path = 'uploads/' . $imgName;
                } else {
                    $error = 'Failed to save payment screenshot.';
                }
            } else {
                $error = 'Only images (JPG, PNG) or PDF are allowed for screenshot.';
            }
        } elseif ($imgError === UPLOAD_ERR_NO_FILE) {
            $error = 'Please attach the payment screenshot.';
        } else {
            $error = 'Payment screenshot upload error (Code: ' . $imgError . ').';
        }
    } else if (!$error) {
        $error = 'Payment screenshot input missing from the form.';
    }

    if (!$error && $full_name && $email && $phone && $college && $terms) {
        try {
            $stmt = $pdo->prepare("INSERT INTO applications (full_name, email, phone, gender, location, college, qualification, course_branch, semester, program, resume_path, payment_screenshot) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$full_name, $email, $phone, $gender, $location, $college, $qualification, $course_branch, $semester, $program, $resume_path, $payment_screenshot_path]);
            $success = true;
        } catch (PDOException $e) {
            $error = 'Failed to submit application. Please try again later.';
        }
    } else if (!$error) {
        $error = 'Please fill out all required fields and accept terms.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply Now | ProeximaLearning</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link rel="stylesheet" href="../styles.css">
    <style>
        .apply-header { padding-top: 8rem; padding-bottom: 2rem; text-align: center; }
        .form-container { max-width: 800px; margin: 0 auto 6rem; background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: 20px; padding: 3rem; backdrop-filter: blur(10px); }
        .form-section { margin-bottom: 2.5rem; padding-bottom: 2rem; border-bottom: 1px solid var(--glass-border); }
        .form-section:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .form-section h3 { font-family: var(--font-heading); color: var(--primary); margin-bottom: 1.5rem; font-size: 1.3rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }
        .grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.5rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; font-size: 0.95rem; color: var(--text-main); }
        .form-control { width: 100%; padding: 0.8rem 1rem; border-radius: 8px; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); color: white; font-family: var(--font-sans); font-size: 0.95rem; transition: var(--transition-normal); }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
        select.form-control { appearance: none; background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e"); background-repeat: no-repeat; background-position: right 1rem center; background-size: 1em; padding-right: 2.5rem; color: #fff;}
        select.form-control option { background: var(--bg-darker); color: white; }
        select.form-control optgroup { background: var(--bg-darker); color: var(--primary); font-weight: bold; }
        .phone-input { display: flex; align-items: center; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); border-radius: 8px; overflow: hidden; }
        .phone-input .country-code { padding: 0.8rem 1rem; background: rgba(255,255,255,0.05); border-right: 1px solid var(--glass-border); font-weight: 500; }
        .phone-input .form-control { border: none; background: transparent; border-radius: 0; }
        .phone-input .form-control:focus { box-shadow: none; }
        .phone-input:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
        .gender-options { display: flex; gap: 1rem; }
        .gender-pill { cursor: pointer; display: flex; align-items: center; justify-content: center; padding: 0.6rem 1.5rem; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--glass-border); border-radius: 9999px; font-size: 0.95rem; transition: var(--transition-normal); flex: 1; }
        .gender-pill input[type="radio"] { display: none; }
        .gender-pill:has(input:checked) { background: rgba(59, 130, 246, 0.15); border-color: var(--primary); color: #60a5fa; font-weight: 600; }
        .terms-group { display: flex; align-items: flex-start; gap: 0.8rem; }
        .terms-group input { margin-top: 0.3rem; accent-color: var(--primary); transform: scale(1.2); cursor:pointer; }
        .terms-text { font-size: 0.85rem; color: var(--text-muted); line-height: 1.5; cursor:pointer;}
        .text-danger { color: #ef4444; }
        .alert { padding: 1rem; border-radius: 10px; margin-bottom: 2rem; font-weight: 500; }
        .alert-success { background: rgba(52, 211, 153, 0.1); border: 1px solid #34d399; color: #34d399; }
        .alert-error { background: rgba(248, 113, 113, 0.1); border: 1px solid #f87171; color: #f87171; }
        .submit-btn { width: 100%; padding: 1.2rem; font-size: 1.1rem; margin-top: 2rem; border-radius: 12px;cursor:pointer; }
        .selected-program-banner { background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(147, 51, 234, 0.1)); border: 1px solid rgba(59, 130, 246, 0.3); border-radius: 12px; padding: 1.5rem; display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; }
        .selected-program-banner img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; }
        .selected-program-banner h4 { margin: 0; font-family: var(--font-heading); font-size: 1.2rem; color: white;}
        .selected-program-banner p { margin: 0; font-size: 0.9rem; color: var(--text-muted); }
        @media (max-width: 768px) { .grid-2, .grid-3 { grid-template-columns: 1fr; } .gender-options { flex-direction: column; } }
    </style>
</head>
<body>
    <nav class="navbar scrolled">
        <div class="nav-container">
            <a href="../index.html" class="logo">
                <span class="logo-icon"><i class="ph ph-cube-transparent"></i></span>
                Proexima<span class="gradient-text">Learning</span>
            </a>
            <div class="nav-actions">
                <a href="../index.html" class="btn btn-outline">Back to Home</a>
            </div>
        </div>
    </nav>

    <header class="apply-header">
        <h1 class="section-title">Application <span class="gradient-text">Form</span></h1>
        <p class="section-subtitle">Take the first step towards launching your career.</p>
    </header>

    <main class="form-container fade-up visible">
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="ph-bold ph-check-circle"></i> Application submitted successfully! We will contact you soon.</div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><i class="ph-bold ph-warning-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="" method="POST" enctype="multipart/form-data">
            <div class="selected-program-banner">
                <img src="https://images.unsplash.com/photo-1517694712202-14dd9538aa97?q=80&w=200&auto=format&fit=crop" alt="Program">
                <div style="width: 100%;">
                    <h4 style="margin-bottom: 0.5rem; color: white;">Program Applied For <span class="text-danger">*</span></h4>
                    <select name="program" class="form-control" required style="background: rgba(15, 23, 42, 0.8); border: 1px solid rgba(59, 130, 246, 0.4); font-weight: bold; padding: 1rem;">
                        <option value="" disabled selected>Choose a Program...</option>
                        <option value="AI-Powered Full Stack Developer Course">AI-Powered Full Stack Developer Course</option>
                        <option value="Full Stack Development">Full Stack Development</option>
                        <option value="C Programming">C Programming</option>
                        <option value="C++ Programming">C++ Programming</option>
                        <option value="Python Programming">Python Programming</option>
                        <option value="AIML">Artificial Intelligence & Machine Learning (AIML)</option>
                        <option value="Network Security">Network Security</option>
                        <option value="Frontend Development">Frontend Development</option>
                        <option value="Project Manager">Project Manager</option>
                        <option value="UI/UX Designer">UI/UX Designer</option>
                        <option value="Graphics Designer">Graphics Designer</option>
                        <option value="Android Development">Android Development</option>
                        <option value="Prompt Engineering">Prompt Engineering</option>
                    </select>
                </div>
            </div>
            <!-- Upload CV -->
            <div class="form-section">
                <h3>Upload CV / Resume <span class="text-danger">*</span></h3>
                <div class="form-group">
                    <label style="color:var(--text-muted); font-weight:normal; font-size:0.85rem; margin-bottom: 0.8rem">Submit your resume in pdf</label>
                    <input type="file" name="resume" accept="application/pdf" class="form-control" required style="padding: 0.6rem; cursor:pointer;">
                </div>
            </div>

            <!-- Basic Details -->
            <div class="form-section">
                <h3>Basic Details</h3>
                <div class="grid-3">
                    <div class="form-group">
                        <label>Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" placeholder="Enter full name" required>
                    </div>
                    <div class="form-group">
                        <label>Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" placeholder="Enter email id" required>
                    </div>
                    <div class="form-group">
                        <label>Phone Number <span class="text-danger">*</span></label>
                        <div class="phone-input">
                            <span class="country-code">+91</span>
                            <input type="text" name="phone" class="form-control" placeholder="10 digit number" pattern="[0-9]{10}" maxlength="10" title="Exactly 10 digits required" required>
                        </div>
                    </div>
                </div>

                <div class="grid-2" style="margin-top: 1rem;">
                    <div class="form-group">
                        <label>Gender <span class="text-danger">*</span></label>
                        <div class="gender-options">
                            <label class="gender-pill"><input type="radio" name="gender" value="Female" required> Female</label>
                            <label class="gender-pill"><input type="radio" name="gender" value="Male"> Male</label>
                            <label class="gender-pill"><input type="radio" name="gender" value="Others"> Others</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Location <span class="text-danger">*</span></label>
                        <input type="text" name="location" class="form-control" placeholder="Enter your city" required>
                    </div>
                </div>
            </div>

            <!-- Education -->
            <div class="form-section">
                <h3>Education</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label>College <span class="text-danger">*</span></label>
                        <select name="college" class="form-control" required>
                            <option value="" disabled selected>Select College</option>
                            <optgroup label="Engineering Colleges">
                                <option value="National Institute of Technology (NIT), Agartala">National Institute of Technology (NIT), Agartala</option>
                                <option value="Tripura Institute of Technology, Narsingarh">Tripura Institute of Technology, Narsingarh</option>
                                <option value="Techno India, Agartala">Techno India, Agartala</option>
                                <option value="ICFAI University, Tripura">ICFAI University, Tripura</option>
                                <option value="Tripura University">Tripura University (Engineering)</option>
                                <option value="Bhavans Tripura College">Bhavan's Tripura College</option>
                            </optgroup>
                            <optgroup label="Polytechnic Colleges">
                                <option value="Womens Polytechnic, Hapania">Women's Polytechnic, Hapania</option>
                                <option value="Government Polytechnic, Agartala">Government Polytechnic, Agartala</option>
                                <option value="Government Polytechnic, Ambassa">Government Polytechnic, Ambassa</option>
                                <option value="Government Polytechnic, Udaipur">Government Polytechnic, Udaipur</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Qualification <span class="text-danger">*</span></label>
                        <select name="qualification" class="form-control" required>
                            <option value="" disabled selected>Select Qualification</option>
                            <option value="Post Graduation">Post Graduation</option>
                            <option value="Graduation">Graduation</option>
                            <option value="Diploma">Diploma</option>
                            <option value="12th Pass">12th Pass</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Course / Branch <span class="text-danger">*</span></label>
                        <select name="course_branch" class="form-control" required>
                            <option value="" disabled selected>Select Course / Branch</option>
                            <optgroup label="Engineering">
                                <option value="B.Tech/BE">B.Tech/BE (Bachelor of Technology/Engineering)</option>
                                <option value="M.Tech/ME">M.Tech/ME (Master of Technology/Engineering)</option>
                                <option value="B.Tech - M.Tech Integrated">B.Tech - M.Tech Integrated/Dual Degree</option>
                                <option value="Engineering - Others">Engineering - Others</option>
                                <option value="Ph.D. in Engineering">Ph.D. in Engineering</option>
                                <option value="Diploma in Engineering">Diploma in Engineering</option>
                            </optgroup>
                            <optgroup label="Computer Applications">
                                <option value="BCA">BCA</option>
                                <option value="MCA (2-year)">MCA (2-year)</option>
                                <option value="MCA (3-year)">MCA (3-year)</option>
                                <option value="MCM">MCM</option>
                            </optgroup>
                            <optgroup label="Management">
                                <option value="1-Year MBA">1-Year MBA</option>
                                <option value="2-Year MBA">2-Year MBA</option>
                                <option value="BBA">BBA (Bachelor of Business Administration)</option>
                                <option value="PGDM">PGDM</option>
                                <option value="Executive MBA">Executive MBA</option>
                                <option value="IPM">IPM</option>
                                <option value="Ph.D in Management">Ph.D in Management</option>
                                <option value="Management - Others">Management - Others</option>
                            </optgroup>
                            <optgroup label="Commerce & Finance">
                                <option value="B.Com">B.Com</option>
                                <option value="M.Com">M.Com</option>
                                <option value="CA">CA</option>
                                <option value="CS">CS</option>
                            </optgroup>
                            <optgroup label="Arts & Science">
                                <option value="B.Sc">B.Sc</option>
                                <option value="M.Sc">M.Sc</option>
                                <option value="B.A">B.A</option>
                                <option value="M.A">M.A</option>
                                <option value="Arts & Science - Others">Arts & Science - Others</option>
                            </optgroup>
                            <optgroup label="Design & Architecture">
                                <option value="B.Des">B.Des</option>
                                <option value="M.Des">M.Des</option>
                                <option value="B.Arch">B.Arch</option>
                                <option value="M.Arch">M.Arch</option>
                                <option value="B.F.Tech">B.F.Tech</option>
                                <option value="M.F.Tech">M.F.Tech</option>
                                <option value="B.Plan">B.Plan</option>
                                <option value="M.Plan">M.Plan</option>
                            </optgroup>
                            <optgroup label="Fine Arts">
                                <option value="Bachelor of Fine Arts">Bachelor of Fine Arts</option>
                                <option value="Master of Fine Arts">Master of Fine Arts</option>
                                <option value="Ph.D in Arts">Ph.D in Arts</option>
                            </optgroup>
                            <optgroup label="Science (International)">
                                <option value="BS">BS (Bachelor of Science)</option>
                                <option value="MS">MS (Master of Science)</option>
                            </optgroup>
                            <optgroup label="Education">
                                <option value="B.Ed">B.Ed</option>
                                <option value="M.Ed">M.Ed</option>
                                <option value="B.El.Ed">B.El.Ed</option>
                                <option value="B.P.Ed">B.P.Ed</option>
                            </optgroup>
                            <optgroup label="Other Courses">
                                <option value="BHM (Hotel Management)">BHM (Hotel Management)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Semester <span class="text-danger">*</span></label>
                        <select name="semester" class="form-control" required>
                            <option value="" disabled selected>Select Semester</option>
                            <option value="1">1st Semester</option>
                            <option value="2">2nd Semester</option>
                            <option value="3">3rd Semester</option>
                            <option value="4">4th Semester</option>
                            <option value="5">5th Semester</option>
                            <option value="6">6th Semester</option>
                            <option value="7">7th Semester</option>
                            <option value="8">8th Semester</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Payment Details -->
            <div class="form-section">
                <h3>Payment Details <span class="text-danger">*</span></h3>
                <div style="text-align: center; margin-bottom: 2rem;">
                    <p style="margin-bottom: 1rem; color: var(--text-main);">Please scan the QR code below to complete your payment.</p>
                    <iframe src="../12320200028239.pdf" width="100%" height="500px" style="border: 1px solid var(--glass-border); border-radius: 12px; margin-bottom: 1rem;"></iframe>
                </div>
                <div class="form-group">
                    <label>Upload Payment Screenshot <span class="text-danger">*</span></label>
                    <label style="color:var(--text-muted); font-weight:normal; font-size:0.85rem; margin-bottom: 0.8rem">Attach the screenshot of your successful transaction. Image or PDF files are allowed.</label>
                    <input type="file" name="payment_screenshot" accept="image/*,application/pdf" class="form-control" required style="padding: 0.6rem; cursor:pointer;">
                </div>
            </div>

            <!-- Terms -->
            <div class="form-section">
                <h3>Terms & Conditions</h3>
                <div class="terms-group">
                    <input type="checkbox" name="terms" id="terms" required>
                    <label for="terms" class="terms-text">
                        By registering for this opportunity, you agree to share the data mentioned in this form or any form henceforth on this opportunity with the recruiter of this opportunity for further analysis, processing, and outreach. Your data will also be used for providing you regular and constant updates. You also agree to the privacy policy and terms of use.
                    </label>
                </div>
            </div>

            <button type="submit" class="btn btn-primary submit-btn">Submit Application <i class="ph-bold ph-paper-plane-right"></i></button>
        </form>
    </main>
    <footer class="footer">
        <div class="footer-bottom" style="text-align: center; justify-content: center; padding: 2rem; flex-direction: column; gap: 0.5rem;">
            <p>Copyright © ProeximaLearning - All rights reserved.</p>
            <p style="color: var(--text-muted); font-size: 0.9rem;">Design and developed by <a href="https://proeximaai.com" target="_blank" style="color: var(--primary); text-decoration: none; font-weight: 500;">ProeximaAI</a></p>
        </div>
    </footer>
</body>
</html>
