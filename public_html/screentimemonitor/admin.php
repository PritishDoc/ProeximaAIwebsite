<?php
require_once __DIR__ . '/includes/auth.php';
requireAdmin();
$user = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo isset($_COOKIE['sm_theme']) ? htmlspecialchars($_COOKIE['sm_theme']) : 'dark'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Dashboard - Employee Monitoring System">
    <title>ScreenMonitor — Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body class="admin-body">
    <!-- Theme Toggle -->
    <button class="theme-toggle" id="themeToggle" title="Toggle theme">
        <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>
        </svg>
        <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/>
        </svg>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="logo-small">
                <svg viewBox="0 0 40 40" fill="none">
                    <rect x="4" y="4" width="32" height="24" rx="3" stroke="currentColor" stroke-width="2.5"/>
                    <path d="M12 32h16M20 28v4" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
                </svg>
            </div>
            <h2>ScreenMonitor</h2>
            <span class="badge-admin">Admin</span>
        </div>

        <nav class="sidebar-nav">
            <a href="#" class="nav-item active" data-view="employees" id="navEmployees">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                <span>Employees</span>
            </a>
            <a href="#" class="nav-item" data-view="screenshots" id="navScreenshots">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                <span>Screenshots</span>
            </a>
            <a href="#" class="nav-item" data-view="activity" id="navActivity">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                <span>Activity</span>
            </a>
            <a href="#" class="nav-item" data-view="invite" id="navInvite">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="8.5" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                <span>Add Employee</span>
            </a>
            <a href="#" class="nav-item" data-view="projects" id="navProjects">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                <span>Projects</span>
            </a>
            <a href="#" class="nav-item" data-view="settings" id="navSettings">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 010 2.83 2 2 0 01-2.83 0l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-2 2 2 2 0 01-2-2v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 01-2.83 0 2 2 0 010-2.83l.06-.06A1.65 1.65 0 004.68 15a1.65 1.65 0 00-1.51-1H3a2 2 0 01-2-2 2 2 0 012-2h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 010-2.83 2 2 0 012.83 0l.06.06A1.65 1.65 0 009 4.68a1.65 1.65 0 001-1.51V3a2 2 0 012-2 2 2 0 012 2v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 012.83 0 2 2 0 010 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 012 2 2 2 0 01-2 2h-.09a1.65 1.65 0 00-1.51 1z"/></svg>
                <span>Settings</span>
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="admin-info">
                <div class="user-avatar"><?php echo strtoupper(substr($user['name'], 0, 1)); ?></div>
                <div>
                    <span class="admin-name"><?php echo htmlspecialchars($user['name']); ?></span>
                    <span class="admin-role">Administrator</span>
                </div>
            </div>
            <button class="btn-sidebar-logout" id="logoutBtn" title="Logout">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4M16 17l5-5-5-5M21 12H9"/></svg>
            </button>
        </div>
    </aside>

    <!-- Mobile Sidebar Toggle -->
    <button class="sidebar-toggle" id="sidebarToggle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
    </button>

    <!-- Main Content -->
    <main class="admin-main" id="adminMain">
        <!-- === EMPLOYEES VIEW === -->
        <section class="view-section active" id="viewEmployees">
            <div class="view-header">
                <div>
                    <h1>Employees</h1>
                    <p class="view-subtitle">Monitor all employee activity in real-time</p>
                </div>
                <div class="view-actions">
                    <button class="btn-primary" onclick="document.getElementById('navInvite').click()" style="margin-right: 12px; display: flex; align-items: center; gap: 6px;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add Employee
                    </button>
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                        <input type="text" placeholder="Search employees..." id="searchEmployees">
                    </div>
                    <button class="btn-refresh" id="refreshEmployees" title="Refresh">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M23 4v6h-6M1 20v-6h6"/><path d="M3.51 9a9 9 0 0114.85-3.36L23 10M1 14l4.64 4.36A9 9 0 0020.49 15"/></svg>
                    </button>
                </div>
            </div>

            <div class="admin-stats-row" id="quickStats">
                <div class="admin-stat">
                    <span class="admin-stat-value" id="totalEmployees">0</span>
                    <span class="admin-stat-label">Total</span>
                </div>
                <div class="admin-stat admin-stat-green">
                    <span class="admin-stat-value" id="activeEmployees">0</span>
                    <span class="admin-stat-label">Active</span>
                </div>
                <div class="admin-stat admin-stat-yellow">
                    <span class="admin-stat-value" id="idleEmployees">0</span>
                    <span class="admin-stat-label">Idle</span>
                </div>
                <div class="admin-stat admin-stat-red">
                    <span class="admin-stat-value" id="offlineEmployees">0</span>
                    <span class="admin-stat-label">Offline</span>
                </div>
            </div>

            <div class="employee-grid" id="employeeGrid">
                <div class="loading-state"><div class="loader"></div><p>Loading employees...</p></div>
            </div>
        </section>

        <!-- === SCREENSHOTS VIEW (Real-Time Feed) === -->
        <section class="view-section" id="viewScreenshots">
            <div class="view-header">
                <div>
                    <h1>Screenshots</h1>
                    <p class="view-subtitle">Real-time screenshot feed from all employees</p>
                </div>
                <div class="view-actions">
                    <select class="select-styled" id="screenshotUserSelect">
                        <option value="0">All Employees</option>
                    </select>
                    <input type="date" class="input-styled" id="screenshotDate" value="">
                    <button class="btn-primary" id="loadScreenshots">Load</button>
                    <div class="live-indicator" id="liveIndicator">
                        <span class="live-dot"></span>
                        <span>LIVE</span>
                    </div>
                </div>
            </div>

            <!-- 24-Hour Activity Timeline Indicator -->
            <div class="admin-activity-timeline" id="adminActivityTimeline" style="display:none; margin-bottom: 24px; padding: 16px; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg);">
                <div style="display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 12px;">
                    <span>Timeline (24 Hours)</span>
                    <span id="timelineStatus">Status: Unknown</span>
                </div>
                
                <div class="timeline-24h-bar" style="position: relative; width: 100%; height: 16px; background: rgba(239, 68, 68, 0.4); border-radius: 4px; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);" id="timeline24hBar">
                    <!-- Green active segments injected via JS -->
                </div>
                
                <div class="timeline-labels" style="display: flex; justify-content: space-between; font-size: 0.65rem; color: var(--text-muted); margin-top: 6px; font-weight: 500;">
                    <span>00:00</span>
                    <span>04:00</span>
                    <span>08:00</span>
                    <span>12:00</span>
                    <span>16:00</span>
                    <span>20:00</span>
                    <span>24:00</span>
                </div>
                <div style="display:flex; gap:12px; margin-top: 12px; font-size:0.7rem;">
                    <div style="display:flex; align-items:center; gap:4px;"><span style="width:10px;height:10px;background:var(--success);border-radius:2px;"></span> Active</div>
                    <div style="display:flex; align-items:center; gap:4px;"><span style="width:10px;height:10px;background:rgba(239, 68, 68, 0.4);border-radius:2px;"></span> Paused/Offline</div>
                </div>
            </div>

            <div class="screenshot-gallery" id="screenshotGallery">
                <div class="loading-state"><div class="loader"></div><p>Loading screenshots...</p></div>
            </div>

            <div class="pagination" id="screenshotPagination" style="display:none;"></div>
        </section>

        <!-- === ACTIVITY VIEW === -->
        <section class="view-section" id="viewActivity">
            <div class="view-header">
                <div>
                    <h1>Activity</h1>
                    <p class="view-subtitle">Detailed activity analytics and graphs</p>
                </div>
                <div class="view-actions">
                    <select class="select-styled" id="activityUserSelect">
                        <option value="">Select Employee</option>
                    </select>
                    <input type="date" class="input-styled" id="activityDate" value="">
                    <button class="btn-primary" id="loadActivity">Load</button>
                    <button class="btn-secondary" id="exportReport" title="Export CSV">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4M7 10l5 5 5-5M12 15V3"/></svg>
                        Export
                    </button>
                </div>
            </div>

            <div class="activity-summary" id="activitySummary" style="display:none;">
                <div class="summary-card">
                    <span class="summary-icon">🖱️</span>
                    <span class="summary-value" id="summaryClicks">0</span>
                    <span class="summary-label">Total Clicks</span>
                </div>
                <div class="summary-card">
                    <span class="summary-icon">⌨️</span>
                    <span class="summary-value" id="summaryKeys">0</span>
                    <span class="summary-label">Key Presses</span>
                </div>
                <div class="summary-card">
                    <span class="summary-icon">💤</span>
                    <span class="summary-value" id="summaryIdle">0m</span>
                    <span class="summary-label">Idle Time</span>
                </div>
                <div class="summary-card">
                    <span class="summary-icon">📸</span>
                    <span class="summary-value" id="summaryScreenshots">0</span>
                    <span class="summary-label">Screenshots</span>
                </div>
            </div>

            <div class="charts-grid" id="chartsGrid" style="display:none;">
                <div class="chart-card"><h3>Mouse &amp; Keyboard Activity</h3><canvas id="activityChart"></canvas></div>
                <div class="chart-card"><h3>Idle Time Distribution</h3><canvas id="idleChart"></canvas></div>
                <div class="chart-card chart-full"><h3>Screenshots Captured</h3><canvas id="screenshotChart"></canvas></div>
            </div>

            <div class="empty-state" id="activityEmptyState">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" width="64" height="64"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                <p>Select an employee and date to view activity charts</p>
            </div>
        </section>

        <!-- === INVITE VIEW === -->
        <section class="view-section" id="viewInvite">
            <div class="view-header">
                <div>
                    <h1>Add Employee</h1>
                    <p class="view-subtitle">Send invitation emails with secure registration links</p>
                </div>
            </div>

            <div class="invite-section">
                <div class="invite-form-card">
                    <h3>Invite a New Employee</h3>
                    <div class="invite-form">
                        <div class="form-group">
                            <label for="inviteEmail">Employee Email</label>
                            <div class="input-wrapper">
                                <svg class="input-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><path d="M22 6l-10 7L2 6"/>
                                </svg>
                                <input type="email" id="inviteEmail" placeholder="employee@company.com" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="inviteProject">Assign to Project</label>
                            <select id="inviteProject" class="select-styled">
                                <option value="0">No project assigned</option>
                            </select>
                        </div>
                        <button class="btn-primary btn-lg" id="sendInviteBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                            Send Invitation
                        </button>
                        <div class="invite-status" id="inviteStatus"></div>
                    </div>
                </div>

                <div class="invitations-list-card">
                    <h3>Sent Invitations</h3>
                    <div class="invitations-table-wrap">
                        <table class="invitations-table" id="invitationsTable">
                            <thead>
                                <tr>
                                    <th>Email</th>
                                    <th>Project</th>
                                    <th>Status</th>
                                    <th>Sent</th>
                                    <th>Expires</th>
                                </tr>
                            </thead>
                            <tbody id="invitationsBody">
                                <tr><td colspan="5" style="text-align:center; padding:24px; color:var(--text-muted);">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>

        <!-- === PROJECTS VIEW === -->
        <section class="view-section" id="viewProjects">
            <div class="view-header">
                <div>
                    <h1>Projects</h1>
                    <p class="view-subtitle">Manage projects and assign employees</p>
                </div>
            </div>

            <div class="projects-section">
                <div class="project-form-card">
                    <h3>Create New Project</h3>
                    <div class="invite-form">
                        <div class="form-group">
                            <label for="projectName">Project Name</label>
                            <input type="text" id="projectName" placeholder="e.g. Mobile App v2" class="input-styled" required>
                        </div>
                        <div class="form-group">
                            <label for="projectDesc">Description (optional)</label>
                            <textarea id="projectDesc" placeholder="Brief project description..." class="input-styled" rows="3" style="resize:vertical;"></textarea>
                        </div>
                        <button class="btn-primary btn-lg" id="createProjectBtn">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M12 5v14M5 12h14"/></svg>
                            Create Project
                        </button>
                        <div class="invite-status" id="projectStatus"></div>
                    </div>
                </div>

                <div class="projects-list-card">
                    <h3>All Projects</h3>
                    <div class="projects-grid" id="projectsGrid">
                        <div class="loading-state"><div class="loader"></div><p>Loading...</p></div>
                    </div>
                </div>
            </div>
        </section>

        <!-- === SETTINGS VIEW === -->
        <section class="view-section" id="viewSettings">
            <div class="view-header">
                <div>
                    <h1>Settings</h1>
                    <p class="view-subtitle">Configure system behavior and thresholds</p>
                </div>
            </div>

            <div class="settings-grid">
                <div class="settings-card">
                    <h3>Screenshot Settings</h3>
                    <div class="setting-item">
                        <label for="settingInterval">Capture Interval (seconds)</label>
                        <div class="setting-control">
                            <input type="range" id="settingInterval" min="5" max="120" value="10" class="range-slider">
                            <span class="range-value" id="intervalValue">10s</span>
                        </div>
                        <small>How often screenshots are captured (5s – 120s)</small>
                    </div>
                    <div class="setting-item">
                        <label for="settingRetention">Retention Period (days)</label>
                        <div class="setting-control">
                            <input type="range" id="settingRetention" min="1" max="90" value="30" class="range-slider">
                            <span class="range-value" id="retentionValue">30d</span>
                        </div>
                        <small>Screenshots older than this are auto-deleted</small>
                    </div>
                </div>

                <div class="settings-card">
                    <h3>Activity Settings</h3>
                    <div class="setting-item">
                        <label for="settingIdle">Idle Threshold (seconds)</label>
                        <div class="setting-control">
                            <input type="range" id="settingIdle" min="30" max="600" value="120" class="range-slider">
                            <span class="range-value" id="idleValue">120s</span>
                        </div>
                        <small>Time without input before marking as idle</small>
                    </div>
                    <div class="setting-item">
                        <label for="settingActivityInterval">Activity Report Interval (seconds)</label>
                        <div class="setting-control">
                            <input type="range" id="settingActivityInterval" min="10" max="120" value="30" class="range-slider">
                            <span class="range-value" id="activityIntervalValue">30s</span>
                        </div>
                        <small>How often activity data is sent to server</small>
                    </div>
                </div>

                <div class="settings-actions">
                    <button class="btn-primary btn-lg" id="saveSettings">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/></svg>
                        Save Settings
                    </button>
                    <span class="settings-status" id="settingsStatus"></span>
                </div>
            </div>
        </section>
    </main>

    <!-- Screenshot Lightbox -->
    <div class="lightbox" id="lightbox" style="display:none;">
        <button class="lightbox-close" id="lightboxClose">&times;</button>
        <button class="lightbox-nav lightbox-prev" id="lightboxPrev">&#8249;</button>
        <button class="lightbox-nav lightbox-next" id="lightboxNext">&#8250;</button>
        <div class="lightbox-content">
            <img id="lightboxImage" alt="Screenshot">
            <div class="lightbox-info" id="lightboxInfo"></div>
        </div>
    </div>

    <script>
        window.SM_USER = {
            id: <?php echo intval($user['id']); ?>,
            name: "<?php echo htmlspecialchars($user['name'], ENT_QUOTES); ?>",
            role: "admin"
        };
    </script>
    <script src="assets/js/chart-config.js"></script>
    <script src="assets/js/admin.js"></script>
</body>
</html>
