<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="ScreenMonitor — Create Your Account">
    <title>ScreenMonitor — Register</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-body">
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" title="Toggle theme">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
        </svg>
    </button>

    <!-- Animated Background -->
    <div class="bg-animation">
        <div class="bg-orb bg-orb-1"></div>
        <div class="bg-orb bg-orb-2"></div>
        <div class="bg-orb bg-orb-3"></div>
    </div>

    <!-- Register Container -->
    <div class="login-container" style="max-width: 520px;">
        <!-- Invalid Token State -->
        <div class="login-card" id="invalidCard" style="display:none;">
            <div class="login-header">
                <div class="logo-icon" style="background: var(--danger);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" width="32" height="32">
                        <circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6M9 9l6 6"/>
                    </svg>
                </div>
                <h1 style="-webkit-text-fill-color: var(--danger); color: var(--danger);">Invalid Invitation</h1>
                <p class="login-subtitle" id="invalidMsg">This invitation link is invalid or has expired.</p>
            </div>
            <a href="index.php" class="btn-login" style="text-align: center; display: block;">Go to Login</a>
        </div>

        <!-- Success State -->
        <div class="login-card" id="successCard" style="display:none;">
            <div class="login-header">
                <div class="logo-icon" style="background: var(--success);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" width="32" height="32">
                        <path d="M20 6L9 17l-5-5"/>
                    </svg>
                </div>
                <h1 style="-webkit-text-fill-color: var(--success); color: var(--success);">Account Created!</h1>
                <p class="login-subtitle">Your account has been set up. You can now log in.</p>
            </div>
            <a href="index.php" class="btn-login" style="text-align: center; display: block;">Go to Login →</a>
        </div>

        <!-- Registration Form -->
        <div class="login-card" id="registerCard">
            <div class="login-header">
                <div class="logo-icon">
                    <svg viewBox="0 0 40 40" fill="none">
                        <rect x="4" y="4" width="32" height="24" rx="3" stroke="currentColor" stroke-width="2.5"/>
                        <path d="M12 32h16M20 28v4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                    </svg>
                </div>
                <h1>Create Account</h1>
                <p class="login-subtitle">Complete your registration to get started</p>
            </div>

            <form id="registerForm" class="login-form" autocomplete="off">
                <input type="hidden" id="inviteToken" value="">

                <!-- Email (read-only, pre-filled) -->
                <div class="form-group">
                    <label for="regEmail">Email Address</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <path d="M22 6l-10 7L2 6"/>
                        </svg>
                        <input type="email" id="regEmail" readonly style="opacity: 0.6; cursor: not-allowed;">
                    </div>
                </div>

                <!-- Name -->
                <div class="form-group">
                    <label for="regName">Full Name</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input type="text" id="regName" placeholder="Enter your full name" required minlength="2">
                    </div>
                </div>

                <!-- Designation -->
                <div class="form-group">
                    <label for="regDesignation">Role / Designation</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/>
                        </svg>
                        <select id="regDesignation" style="width:100%; padding:14px 14px 14px 44px; background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); color:var(--text-primary); font-family:var(--font-family); font-size:0.9375rem; outline:none; -webkit-appearance:none;">
                            <option value="">Select your role</option>
                            <option value="Developer">Developer</option>
                            <option value="Designer">Designer</option>
                            <option value="QA Tester">QA Tester</option>
                            <option value="Project Manager">Project Manager</option>
                            <option value="Business Analyst">Business Analyst</option>
                            <option value="DevOps Engineer">DevOps Engineer</option>
                            <option value="Data Analyst">Data Analyst</option>
                            <option value="Support">Support</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>

                <!-- Project -->
                <div class="form-group">
                    <label for="regProject">Project</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/>
                        </svg>
                        <select id="regProject" style="width:100%; padding:14px 14px 14px 44px; background:var(--bg-input); border:1px solid var(--border-color); border-radius:var(--radius-md); color:var(--text-primary); font-family:var(--font-family); font-size:0.9375rem; outline:none; -webkit-appearance:none;">
                            <option value="">Loading projects...</option>
                        </select>
                    </div>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="regPassword">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="regPassword" placeholder="Create a password (min 6 chars)" required minlength="6">
                    </div>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="regConfirm">Confirm Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                        </svg>
                        <input type="password" id="regConfirm" placeholder="Confirm your password" required minlength="6">
                    </div>
                </div>

                <div class="form-error" id="formError"></div>

                <button type="submit" class="btn-login" id="registerBtn">
                    <span class="btn-text">Create Account</span>
                    <span class="btn-loader">
                        <svg class="spinner" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-linecap="round"/>
                        </svg>
                    </span>
                </button>
            </form>

            <div class="login-footer">
                <p>Already have an account? <a href="index.php" style="color: var(--accent);">Sign in</a></p>
            </div>
        </div>
    </div>

    <script>
        // Theme
        const savedTheme = localStorage.getItem('sm_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
        document.getElementById('themeToggle').addEventListener('click', () => {
            const next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('sm_theme', next);
        });

        // Get token from URL
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        if (!token) {
            document.getElementById('registerCard').style.display = 'none';
            document.getElementById('invalidCard').style.display = 'block';
            document.getElementById('invalidMsg').textContent = 'No invitation token provided. Please use the link from your invitation email.';
        } else {
            document.getElementById('inviteToken').value = token;

            // Validate token and load invitation data
            fetch('api/register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ token: token, validate_only: true })
            }); // We'll validate on submit — just load projects for now

            // Load projects
            fetch('api/get_projects.php')
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const sel = document.getElementById('regProject');
                        sel.innerHTML = '<option value="">Select your project</option>';
                        data.projects.forEach(p => {
                            sel.innerHTML += `<option value="${p.id}">${p.name}</option>`;
                        });
                    }
                })
                .catch(() => {});

            // Try to get email from token (we encode it in the invitation)
            // For now, we'll validate on submit and the server will use the token's email
            document.getElementById('regEmail').value = 'Loading...';
            document.getElementById('regEmail').placeholder = 'Will be filled from invitation';
            
            // Quick check - get invitations won't work without admin. 
            // Show email field as "from invitation"
            document.getElementById('regEmail').value = 'Email from your invitation';
        }

        // Form submit
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const formError = document.getElementById('formError');
            const btn = document.getElementById('registerBtn');
            formError.textContent = '';
            formError.classList.remove('visible');

            const password = document.getElementById('regPassword').value;
            const confirm = document.getElementById('regConfirm').value;

            if (password !== confirm) {
                formError.textContent = 'Passwords do not match';
                formError.classList.add('visible');
                return;
            }

            if (password.length < 6) {
                formError.textContent = 'Password must be at least 6 characters';
                formError.classList.add('visible');
                return;
            }

            btn.classList.add('loading');

            try {
                const res = await fetch('api/register.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        token: document.getElementById('inviteToken').value,
                        name: document.getElementById('regName').value.trim(),
                        designation: document.getElementById('regDesignation').value,
                        project_id: parseInt(document.getElementById('regProject').value) || 0,
                        password: password
                    })
                });

                const data = await res.json();

                if (data.success) {
                    document.getElementById('registerCard').style.display = 'none';
                    document.getElementById('successCard').style.display = 'block';
                } else {
                    formError.textContent = data.error || 'Registration failed';
                    formError.classList.add('visible');
                    btn.classList.remove('loading');

                    if (data.error && data.error.includes('expired')) {
                        document.getElementById('registerCard').style.display = 'none';
                        document.getElementById('invalidCard').style.display = 'block';
                        document.getElementById('invalidMsg').textContent = data.error;
                    }
                }
            } catch (err) {
                formError.textContent = 'Connection error. Please try again.';
                formError.classList.add('visible');
                btn.classList.remove('loading');
            }
        });
    </script>
</body>
</html>
