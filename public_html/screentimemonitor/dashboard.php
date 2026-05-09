<?php
require_once __DIR__ . '/includes/auth.php';
requireLogin();

// Redirect admin to admin panel
if ($_SESSION['user_role'] === 'admin') {
    header('Location: admin.php');
    exit;
}

$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_COOKIE['sm_theme']) ? htmlspecialchars($_COOKIE['sm_theme']) : 'dark'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Employee Dashboard - Screen Monitoring Active">
    <title>ScreenMonitor — Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="dashboard-body">
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" title="Toggle theme">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
        </svg>
    </button>

    <!-- Top Bar -->
    <header class="topbar">
        <div class="topbar-left">
            <div class="logo-small">
                <svg viewBox="0 0 40 40" fill="none">
                    <rect x="4" y="4" width="32" height="24" rx="3" stroke="currentColor" stroke-width="2.5"/>
                    <path d="M12 32h16M20 28v4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h2>ScreenMonitor</h2>
        </div>
        <div class="topbar-right">
            <div class="user-info">
                <div class="user-avatar" id="userAvatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
            </div>
            <button class="btn-logout" id="logoutBtn" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/>
                </svg>
                <span>Logout</span>
            </button>
        </div>
    </header>

    <!-- Main Dashboard -->
    <main class="dashboard-main">
        <!-- Monthly Summary -->
        <div class="monthly-summary" style="background:var(--bg-card); padding:1rem 1.5rem; border-radius:var(--radius-lg); border:1px solid var(--border-color); display:flex; justify-content:space-between; align-items:center; margin-bottom:1.5rem;">
            <div style="display:flex; align-items:center; gap:12px;">
                <div style="background:rgba(59, 130, 246, 0.1); color:var(--primary); padding:10px; border-radius:8px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div>
                    <h3 style="font-size:0.75rem; text-transform:uppercase; color:var(--text-muted); font-weight:600; margin-bottom:2px;">Total Hours This Month</h3>
                    <div style="font-size:1.5rem; font-weight:700; color:var(--text-primary);" id="monthlyHours">00:00:00</div>
                </div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; font-weight:600; margin-bottom:2px;">Auto-Reset</div>
                <div style="font-size:0.875rem; font-weight:500; color:var(--text-primary);">Next at 12:00 AM</div>
            </div>
        </div>

        <!-- Status Banner -->
        <div class="status-banner" id="statusBanner">
            <div class="status-indicator">
                <div class="pulse-dot" id="pulseDot"></div>
                <span id="statusText">Ready to Start</span>
                <span id="realtimeClock" style="margin-left: 12px; font-size: 0.875rem; color: var(--text-muted); font-weight: 500;"></span>
            </div>
            <div class="status-timer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18">
                    <circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>
                </svg>
                <span id="elapsedTime">00:00:00</span>
            </div>
        </div>

        <!-- Monitoring Control -->
        <div class="control-section" style="display: flex; gap: 1rem; align-items: stretch; margin-top: 1rem;">
            <button class="btn-monitor" id="startMonitorBtn" style="flex: 2; border: 2px solid var(--border-color);">
                <div class="btn-monitor-icon">
                    <svg id="playIcon" viewBox="0 0 24 24" fill="currentColor">
                        <polygon points="5,3 19,12 5,21"/>
                    </svg>
                    <svg id="pauseIcon" viewBox="0 0 24 24" fill="currentColor" style="display:none;">
                        <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                    </svg>
                </div>
                <div class="btn-monitor-text">
                    <span id="monitorBtnLabel">Start</span>
                    <small id="monitorBtnSub">Click to begin counting</small>
                </div>
            </button>
            <button class="btn-monitor" id="endMonitorBtn" disabled style="flex: 1; opacity: 0.4; cursor: not-allowed; border: 2px solid transparent;">
                <div class="btn-monitor-icon" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <rect x="4" y="4" width="16" height="16" rx="3"/>
                    </svg>
                </div>
                <div class="btn-monitor-text">
                    <span style="color: #ef4444;">End Shift</span>
                    <small>Stop totally</small>
                </div>
            </button>
        </div>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon stat-icon-blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-value" id="screenshotCount">0</span>
                    <span class="stat-label">Screenshots</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 20V10M18 20V4M6 20v-4"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-value" id="activityScore">0%</span>
                    <span class="stat-label">Activity</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"/>
                        <line x1="4" y1="22" x2="4" y2="15"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-value" id="mouseClicks">0</span>
                    <span class="stat-label">Clicks</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon stat-icon-orange">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                        <path d="M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                </div>
                <div class="stat-content">
                    <span class="stat-value" id="keyPresses">0</span>
                    <span class="stat-label">Key Presses</span>
                </div>
            </div>
        </div>

        <!-- Last Screenshot Preview -->
        <div class="preview-section" id="previewSection" style="display:none;">
            <div class="section-header">
                <h3>Last Screenshot</h3>
                <span class="preview-time" id="previewTime"></span>
            </div>
            <div class="screenshot-preview">
                <img id="previewImage" alt="Last screenshot" />
            </div>
        </div>

        <!-- Activity Indicator -->
        <div class="activity-bar" id="activityBar">
            <div class="activity-bar-inner">
                <div class="activity-segment" id="activitySegment"></div>
            </div>
            <div class="activity-labels">
                <span>Idle</span>
                <span>Active</span>
            </div>
        </div>
    </main>

    <!-- Pause Alert Modal -->
    <div class="pause-modal" id="pauseModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(10px); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:var(--bg-card); padding:2rem; border-radius:var(--radius-lg); border:1px solid var(--warning); box-shadow:0 0 40px rgba(245, 158, 11, 0.2); text-align:center; max-width:400px; animation: slideUp 0.3s ease-out;">
            <div style="width:64px; height:64px; border-radius:50%; background:var(--warning-bg); color:var(--warning); display:flex; align-items:center; justify-content:center; margin:0 auto 1rem;">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="32" height="32"><rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/></svg>
            </div>
            <h2 style="margin-bottom:0.5rem; font-size:1.25rem;">Monitoring Paused</h2>
            <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom:1.5rem;">Your screen monitoring has been automatically paused due to 2 minutes of inactivity.</p>
            <p style="font-size:0.875rem; font-weight:600; color:var(--text-primary);">Move your mouse or press a key to resume automatically.</p>
        </div>
    </div>

    <!-- User data for JS -->
    <script>
        window.SM_USER = {
            id: <?php echo intval($user['id']); ?>,
            name: "<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>",
            email: "<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>",
            role: "<?php echo htmlspecialchars($user['role'], ENT_QUOTES); ?>"
        };
    </script>
    <script src="assets/js/app.js?v=2.0.3"></script>
</body>
</html>
