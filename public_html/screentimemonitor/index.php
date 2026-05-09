<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Employee Monitoring System - Secure Login Portal">
    <title>ScreenMonitor — Login</title>
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

    <!-- Login Container -->
    <div class="login-container">
        <div class="login-card" id="loginCard">
            <!-- Logo & Branding -->
            <div class="login-header">
                <div class="logo-icon">
                    <svg viewBox="0 0 40 40" fill="none">
                        <rect x="4" y="4" width="32" height="24" rx="3" stroke="currentColor" stroke-width="2.5"/>
                        <path d="M12 32h16M20 28v4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                        <circle cx="20" cy="16" r="4" fill="currentColor" opacity="0.3"/>
                        <path d="M14 16l3 3 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h1>ScreenMonitor</h1>
                <p class="login-subtitle">Employee Monitoring System</p>
            </div>

            <!-- Login Form -->
            <form id="loginForm" class="login-form" autocomplete="off">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <path d="M22 6l-10 7L2 6"/>
                        </svg>
                        <input type="email" id="email" name="email" placeholder="Enter your email" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="toggle-password" id="togglePassword" tabindex="-1">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-error" id="formError"></div>

                <button type="submit" class="btn-login" id="loginBtn">
                    <span class="btn-text">Sign In</span>
                    <span class="btn-loader">
                        <svg class="spinner" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" fill="none" stroke="currentColor" stroke-width="3" stroke-dasharray="31.4" stroke-linecap="round"/>
                        </svg>
                    </span>
                </button>
            </form>

            <div class="login-footer">
                <p>Secure monitoring portal · All sessions are recorded</p>
            </div>
        </div>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const savedTheme = localStorage.getItem('sm_theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);

        themeToggle.addEventListener('click', () => {
            const current = document.documentElement.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            localStorage.setItem('sm_theme', next);
        });

        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.classList.toggle('active');
        });

        // Login Form
        const loginForm = document.getElementById('loginForm');
        const loginBtn = document.getElementById('loginBtn');
        const formError = document.getElementById('formError');

        loginForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            formError.textContent = '';
            formError.classList.remove('visible');
            loginBtn.classList.add('loading');

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('api/login.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email, password })
                });

                const data = await response.json();

                if (data.success) {
                    loginBtn.classList.remove('loading');
                    loginBtn.classList.add('success');
                    document.getElementById('loginCard').classList.add('fade-out');

                    setTimeout(() => {
                        if (data.user.role === 'admin') {
                            window.location.href = 'admin.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }, 600);
                } else {
                    formError.textContent = data.error || 'Login failed';
                    formError.classList.add('visible');
                    loginBtn.classList.remove('loading');
                    document.getElementById('loginCard').classList.add('shake');
                    setTimeout(() => document.getElementById('loginCard').classList.remove('shake'), 500);
                }
            } catch (err) {
                formError.textContent = 'Connection error. Please try again.';
                formError.classList.add('visible');
                loginBtn.classList.remove('loading');
            }
        });
    </script>
</body>
</html>
