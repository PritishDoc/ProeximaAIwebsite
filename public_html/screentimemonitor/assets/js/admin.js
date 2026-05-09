/**
 * ScreenMonitor — Admin Dashboard (v2)
 * ======================================
 * SPA navigation, employee management, real-time screenshot feed,
 * invite system, project management, activity graphs, settings.
 */

(function () {
    'use strict';

    const $ = id => document.getElementById(id);
    let refreshTimer = null;
    let screenshotRefreshTimer = null;
    let currentScreenshots = [];
    let currentScreenshotIndex = 0;
    let employeeList = [];

    // ── Theme Toggle ──
    const savedTheme = localStorage.getItem('sm_theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    $('themeToggle').addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('sm_theme', next);
    });

    // ── Sidebar Navigation ──
    const navItems = document.querySelectorAll('.nav-item');
    const viewSections = document.querySelectorAll('.view-section');

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const view = item.dataset.view;

            navItems.forEach(n => n.classList.remove('active'));
            item.classList.add('active');

            viewSections.forEach(s => s.classList.remove('active'));
            $('view' + capitalize(view)).classList.add('active');

            $('sidebar').classList.remove('open');

            // Load data for the view
            if (view === 'screenshots') loadLiveScreenshots();
            if (view === 'invite') loadInvitations();
            if (view === 'projects') loadProjects();
        });
    });

    $('sidebarToggle').addEventListener('click', () => {
        $('sidebar').classList.toggle('open');
    });

    function capitalize(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // ── Logout ──
    $('logoutBtn').addEventListener('click', async () => {
        try {
            await fetch('api/logout.php', { method: 'POST' });
        } catch (e) { /* ignore */ }
        window.location.href = 'index.php';
    });

    // ══════════════════════════════════════════
    // EMPLOYEES
    // ══════════════════════════════════════════

    async function loadEmployees() {
        try {
            const res = await fetch('api/get_employees.php');
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            employeeList = data.employees;
            renderEmployees(data.employees);
            updateQuickStats(data.employees);
            populateEmployeeSelects(data.employees);
        } catch (e) {
            console.error('[Employees] Load failed:', e);
            $('employeeGrid').innerHTML = '<div class="empty-state"><p>Failed to load employees</p></div>';
        }
    }

    function renderEmployees(employees) {
        const grid = $('employeeGrid');
        const searchTerm = $('searchEmployees').value.toLowerCase();

        const filtered = employees.filter(emp =>
            emp.name.toLowerCase().includes(searchTerm) ||
            emp.email.toLowerCase().includes(searchTerm) ||
            (emp.project_name || '').toLowerCase().includes(searchTerm)
        );

        if (filtered.length === 0) {
            grid.innerHTML = '<div class="empty-state"><p>No employees found</p></div>';
            return;
        }

        grid.innerHTML = filtered.map(emp => `
            <div class="employee-card status-${emp.status}" data-user-id="${emp.id}" onclick="AdminApp.viewEmployee(${emp.id})">
                <div class="emp-card-header">
                    <div class="emp-info">
                        <div class="emp-avatar">${emp.name.charAt(0).toUpperCase()}</div>
                        <div>
                            <div class="emp-name">${escapeHtml(emp.name)}</div>
                            <div class="emp-email">${escapeHtml(emp.email)}</div>
                        </div>
                    </div>
                    <span class="emp-status-badge ${emp.status}">
                        <span class="status-dot"></span>
                        ${emp.status}
                    </span>
                </div>
                ${emp.project_name ? `<div class="emp-project-badge">${escapeHtml(emp.project_name)}</div>` : ''}
                ${emp.designation ? `<div class="emp-designation">${escapeHtml(emp.designation)}</div>` : ''}
                <div class="emp-card-details">
                    <div class="emp-detail">
                        <span class="emp-detail-value">${emp.today_login ? formatTime(emp.today_login) : '--:--'}</span>
                        <span class="emp-detail-label">Login</span>
                    </div>
                    <div class="emp-detail">
                        <span class="emp-detail-value">${emp.today_hours || '00:00:00'}</span>
                        <span class="emp-detail-label">Hours</span>
                    </div>
                    <div class="emp-detail">
                        <span class="emp-detail-value">${emp.today_screenshots || 0}</span>
                        <span class="emp-detail-label">Captures</span>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function updateQuickStats(employees) {
        $('totalEmployees').textContent = employees.length;
        $('activeEmployees').textContent = employees.filter(e => e.status === 'active').length;
        $('idleEmployees').textContent = employees.filter(e => e.status === 'idle').length;
        $('offlineEmployees').textContent = employees.filter(e => e.status === 'offline').length;
    }

    function populateEmployeeSelects(employees) {
        const selects = [$('screenshotUserSelect'), $('activityUserSelect')];
        selects.forEach(sel => {
            const current = sel.value;
            const isScreenshots = sel.id === 'screenshotUserSelect';
            sel.innerHTML = isScreenshots
                ? '<option value="0">All Employees</option>'
                : '<option value="">Select Employee</option>';
            employees.forEach(emp => {
                const opt = document.createElement('option');
                opt.value = emp.id;
                opt.textContent = emp.name + (emp.project_name ? ` (${emp.project_name})` : '');
                sel.appendChild(opt);
            });
            if (current) sel.value = current;
        });
    }

    $('searchEmployees').addEventListener('input', () => renderEmployees(employeeList));
    $('refreshEmployees').addEventListener('click', loadEmployees);

    // ── View Employee Details ──
    window.AdminApp = {
        viewEmployee(userId) {
            $('screenshotUserSelect').value = userId;
            $('screenshotDate').value = new Date().toISOString().slice(0, 10);
            document.querySelector('[data-view="screenshots"]').click();
        }
    };

    // ══════════════════════════════════════════
    // SCREENSHOTS — Real-Time Feed
    // ══════════════════════════════════════════

    $('screenshotDate').value = new Date().toISOString().slice(0, 10);
    $('activityDate').value = new Date().toISOString().slice(0, 10);

    $('loadScreenshots').addEventListener('click', () => {
        const userId = $('screenshotUserSelect').value;
        const date = $('screenshotDate').value;
        const today = new Date().toISOString().slice(0, 10);

        if (date === today) {
            loadLiveScreenshots();
        } else {
            loadHistoricalScreenshots(1);
        }
    });

    // LIVE screenshot feed
    async function loadLiveScreenshots() {
        const userId = $('screenshotUserSelect').value || 0;
        const liveInd = $('liveIndicator');
        liveInd.style.display = 'flex';

        try {
            const res = await fetch(`api/get_latest_screenshots.php?minutes=60&user_id=${userId}`);
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            currentScreenshots = data.screenshots;

            // --- Timeline Bar Logic ---
            const timeline = $('adminActivityTimeline');
            const timelineStat = $('timelineStatus');
            
            if (userId && userId !== '0' && typeof employeeList !== 'undefined') {
                const emp = employeeList.find(e => parseInt(e.id) === parseInt(userId));
                if (emp) {
                    timeline.style.display = 'block';
                    timelineStat.textContent = `Status: ${emp.status.toUpperCase()}`;
                    if (emp.status === 'active') timelineStat.style.color = 'var(--success)';
                    else if (emp.status === 'idle') timelineStat.style.color = 'var(--warning)';
                    else timelineStat.style.color = 'var(--text-muted)';
                    
                    if (data.timeline) {
                        AdminApp.renderTimeline24h(data.timeline);
                    }
                } else {
                    timeline.style.display = 'none';
                }
            } else {
                timeline.style.display = 'none';
            }
            // --------------------------

            if (data.screenshots.length === 0) {
                $('screenshotGallery').innerHTML = '<div class="empty-state"><p>No screenshots in the last hour. Waiting for captures...</p></div>';
                $('screenshotPagination').style.display = 'none';
                return;
            }

            // Group by day
            let html = '';
            let currentDay = '';

            data.screenshots.forEach((ss, index) => {
                if (ss.day_label !== currentDay) {
                    currentDay = ss.day_label;
                    html += `<div class="screenshot-day-header">${currentDay}</div>`;
                }

                html += `
                    <div class="screenshot-item live-item" onclick="AdminApp.openLightbox(${index})">
                        <img class="screenshot-thumb" src="${ss.image_path}" alt="Screenshot" loading="lazy"
                             onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 300 169%22><rect fill=%22%23111%22 width=%22300%22 height=%22169%22/><text fill=%22%23555%22 x=%22150%22 y=%2290%22 text-anchor=%22middle%22 font-size=%2214%22>Image not available</text></svg>'">
                        <div class="screenshot-meta">
                            <div class="screenshot-user">
                                <span class="screenshot-avatar">${ss.user_name.charAt(0).toUpperCase()}</span>
                                <span>${escapeHtml(ss.user_name)}</span>
                            </div>
                            <div class="screenshot-relative">${ss.relative_time}</div>
                            <div class="screenshot-size">${formatFileSize(ss.file_size)}</div>
                        </div>
                    </div>
                `;
            });

            $('screenshotGallery').innerHTML = html;
            $('screenshotPagination').style.display = 'none';

        } catch (e) {
            console.error('[Live Screenshots] Failed:', e);
            $('screenshotGallery').innerHTML = '<div class="empty-state"><p>Failed to load screenshots</p></div>';
        }

        // Auto-refresh every 10 seconds
        clearInterval(screenshotRefreshTimer);
        screenshotRefreshTimer = setInterval(loadLiveScreenshots, 10000);
    }
    
    AdminApp.renderTimeline24h = function(timelineData) {
        const bar = $('timeline24hBar');
        if (!bar) return;
        
        if (!timelineData || timelineData.length === 0) {
            bar.innerHTML = '';
            return;
        }
        
        let html = '';
        timelineData.forEach(t => {
            const minOfDay = t.hour * 60 + t.minute;
            const leftPercent = (minOfDay / 1440) * 100;
            // 0.2% width is approx 3 minutes wide on a 24h bar
            html += `<div style="position:absolute; left:${leftPercent}%; top:0; height:100%; width:0.25%; background:var(--success); border-radius:1px;"></div>`;
        });
        
        bar.innerHTML = html;
    };

    // Historical screenshots (by specific date)
    async function loadHistoricalScreenshots(page = 1) {
        const userId = $('screenshotUserSelect').value;
        const date = $('screenshotDate').value;
        $('liveIndicator').style.display = 'none';
        clearInterval(screenshotRefreshTimer);

        if (!userId || userId === '0') {
            $('screenshotGallery').innerHTML = '<div class="empty-state"><p>Select a specific employee for historical screenshots</p></div>';
            return;
        }

        $('screenshotGallery').innerHTML = '<div class="loading-state"><div class="loader"></div><p>Loading screenshots...</p></div>';

        try {
            const res = await fetch(`api/get_screenshots.php?user_id=${userId}&date=${date}&page=${page}`);
            const data = await res.json();

            if (!data.success) throw new Error(data.error);
            currentScreenshots = data.screenshots;
            
            // Render Timeline
            const timeline = $('adminActivityTimeline');
            if (data.timeline) {
                timeline.style.display = 'block';
                $('timelineStatus').textContent = `Date: ${date}`;
                $('timelineStatus').style.color = 'var(--text-muted)';
                AdminApp.renderTimeline24h(data.timeline);
            } else {
                timeline.style.display = 'none';
            }

            if (data.screenshots.length === 0) {
                $('screenshotGallery').innerHTML = '<div class="empty-state"><p>No screenshots found for this date</p></div>';
                $('screenshotPagination').style.display = 'none';
                return;
            }

            $('screenshotGallery').innerHTML = data.screenshots.map((ss, index) => `
                <div class="screenshot-item" onclick="AdminApp.openLightbox(${index})">
                    <img class="screenshot-thumb" src="${ss.image_path}" alt="Screenshot" loading="lazy"
                         onerror="this.src='data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 300 169%22><rect fill=%22%23111%22 width=%22300%22 height=%22169%22/><text fill=%22%23555%22 x=%22150%22 y=%2290%22 text-anchor=%22middle%22 font-size=%2214%22>Image not available</text></svg>'">
                    <div class="screenshot-meta">
                        <div class="screenshot-size">${formatFileSize(ss.file_size)}</div>
                    </div>
                </div>
            `).join('');

            renderPagination(data.pagination);

        } catch (e) {
            console.error('[Screenshots] Load failed:', e);
            $('screenshotGallery').innerHTML = '<div class="empty-state"><p>Failed to load screenshots</p></div>';
        }
    }

    function renderPagination(pagination) {
        const container = $('screenshotPagination');
        if (!pagination || pagination.total_pages <= 1) {
            container.style.display = 'none';
            return;
        }
        container.style.display = 'flex';
        let html = '';
        for (let i = 1; i <= pagination.total_pages; i++) {
            html += `<button class="${i === pagination.page ? 'active' : ''}" onclick="AdminApp.loadScreenshotPage(${i})">${i}</button>`;
        }
        container.innerHTML = html;
    }

    AdminApp.loadScreenshotPage = function (page) {
        loadHistoricalScreenshots(page);
    };

    // ── Lightbox ──
    AdminApp.openLightbox = function (index) {
        currentScreenshotIndex = index;
        const ss = currentScreenshots[index];
        if (!ss) return;

        $('lightboxImage').src = ss.image_path;
        const info = ss.user_name
            ? `${ss.user_name} — ${ss.time_label || formatDateTime(ss.captured_at)} — ${ss.relative_time || ''} — ${formatFileSize(ss.file_size)}`
            : `${formatDateTime(ss.captured_at)} — ${formatFileSize(ss.file_size)}`;
        $('lightboxInfo').textContent = info;
        $('lightbox').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    $('lightboxClose').addEventListener('click', closeLightbox);
    $('lightbox').addEventListener('click', (e) => {
        if (e.target === $('lightbox')) closeLightbox();
    });
    $('lightboxPrev').addEventListener('click', () => {
        if (currentScreenshotIndex > 0) AdminApp.openLightbox(currentScreenshotIndex - 1);
    });
    $('lightboxNext').addEventListener('click', () => {
        if (currentScreenshotIndex < currentScreenshots.length - 1) AdminApp.openLightbox(currentScreenshotIndex + 1);
    });
    document.addEventListener('keydown', (e) => {
        if ($('lightbox').style.display === 'flex') {
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowLeft') $('lightboxPrev').click();
            if (e.key === 'ArrowRight') $('lightboxNext').click();
        }
    });

    function closeLightbox() {
        $('lightbox').style.display = 'none';
        document.body.style.overflow = '';
    }

    // ══════════════════════════════════════════
    // ACTIVITY CHARTS
    // ══════════════════════════════════════════

    $('loadActivity').addEventListener('click', loadActivity);

    async function loadActivity() {
        const userId = $('activityUserSelect').value;
        const date = $('activityDate').value;
        if (!userId) return;

        $('activityEmptyState').style.display = 'none';
        $('activitySummary').style.display = 'grid';
        $('chartsGrid').style.display = 'grid';

        try {
            const res = await fetch(`api/get_activity.php?user_id=${userId}&date=${date}`);
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            $('summaryClicks').textContent = data.summary.total_clicks.toLocaleString();
            $('summaryKeys').textContent = data.summary.total_keys.toLocaleString();
            $('summaryIdle').textContent = Math.round(data.summary.total_idle / 60) + 'm';

            const totalScreenshots = data.screenshots_hourly.reduce((a, b) => a + b, 0);
            $('summaryScreenshots').textContent = totalScreenshots;

            ChartConfig.createActivityChart('activityChart', data.hourly);
            ChartConfig.createIdleChart('idleChart', data.hourly);
            ChartConfig.createScreenshotChart('screenshotChart', data.screenshots_hourly);

        } catch (e) {
            console.error('[Activity] Load failed:', e);
        }
    }

    // Export CSV
    $('exportReport').addEventListener('click', () => {
        const userId = $('activityUserSelect').value;
        if (!userId) { alert('Please select an employee first'); return; }
        const endDate = $('activityDate').value || new Date().toISOString().slice(0, 10);
        const startDate = new Date(new Date(endDate).getTime() - 7 * 86400000).toISOString().slice(0, 10);
        window.open(`api/export_report.php?user_id=${userId}&start_date=${startDate}&end_date=${endDate}`, '_blank');
    });

    // ══════════════════════════════════════════
    // INVITE EMPLOYEE
    // ══════════════════════════════════════════

    $('sendInviteBtn').addEventListener('click', async () => {
        const email = $('inviteEmail').value.trim();
        const projectId = parseInt($('inviteProject').value) || 0;
        const statusEl = $('inviteStatus');

        if (!email) {
            statusEl.textContent = '✗ Please enter an email address';
            statusEl.className = 'invite-status error';
            return;
        }

        $('sendInviteBtn').disabled = true;
        statusEl.textContent = 'Sending invitation...';
        statusEl.className = 'invite-status info';

        try {
            const res = await fetch('api/invite_employee.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, project_id: projectId })
            });
            const data = await res.json();

            if (data.success) {
                statusEl.innerHTML = `✓ ${data.message}` +
                    (data.register_url ? `<br><small style="word-break:break-all;color:var(--text-muted);">Link: ${data.register_url}</small>` : '');
                statusEl.className = 'invite-status success';
                $('inviteEmail').value = '';
                loadInvitations();
                loadEmployees();
            } else {
                statusEl.textContent = '✗ ' + (data.error || 'Failed to send');
                statusEl.className = 'invite-status error';
            }
        } catch (e) {
            statusEl.textContent = '✗ Network error';
            statusEl.className = 'invite-status error';
        }

        $('sendInviteBtn').disabled = false;
    });

    async function loadInvitations() {
        try {
            const res = await fetch('api/get_invitations.php');
            const data = await res.json();

            if (!data.success) throw new Error(data.error);

            const tbody = $('invitationsBody');
            if (data.invitations.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:24px;color:var(--text-muted);">No invitations sent yet</td></tr>';
                return;
            }

            tbody.innerHTML = data.invitations.map(inv => {
                const statusClass = inv.status === 'accepted' ? 'success' : inv.status === 'expired' ? 'danger' : 'warning';
                return `
                    <tr>
                        <td>${escapeHtml(inv.email)}</td>
                        <td>${inv.project_name ? escapeHtml(inv.project_name) : '<span style="color:var(--text-muted)">—</span>'}</td>
                        <td><span class="status-pill ${statusClass}">${inv.status}</span></td>
                        <td>${formatDate(inv.created_at)}</td>
                        <td>${formatDate(inv.expires_at)}</td>
                    </tr>
                `;
            }).join('');

        } catch (e) {
            console.error('[Invitations] Load failed:', e);
        }
    }

    // Populate invite project dropdown
    async function loadProjectSelects() {
        try {
            const res = await fetch('api/get_projects.php');
            const data = await res.json();
            if (!data.success) return;

            const selects = [$('inviteProject')];
            selects.forEach(sel => {
                sel.innerHTML = '<option value="0">No project assigned</option>';
                data.projects.forEach(p => {
                    sel.innerHTML += `<option value="${p.id}">${escapeHtml(p.name)}</option>`;
                });
            });
        } catch (e) { /* ignore */ }
    }

    // ══════════════════════════════════════════
    // PROJECTS
    // ══════════════════════════════════════════

    $('createProjectBtn').addEventListener('click', async () => {
        const name = $('projectName').value.trim();
        const desc = $('projectDesc').value.trim();
        const statusEl = $('projectStatus');

        if (name.length < 2) {
            statusEl.textContent = '✗ Project name must be at least 2 characters';
            statusEl.className = 'invite-status error';
            return;
        }

        try {
            const res = await fetch('api/manage_project.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'create', name, description: desc })
            });
            const data = await res.json();

            if (data.success) {
                statusEl.textContent = '✓ ' + data.message;
                statusEl.className = 'invite-status success';
                $('projectName').value = '';
                $('projectDesc').value = '';
                loadProjects();
                loadProjectSelects();
            } else {
                statusEl.textContent = '✗ ' + (data.error || 'Failed');
                statusEl.className = 'invite-status error';
            }
        } catch (e) {
            statusEl.textContent = '✗ Network error';
            statusEl.className = 'invite-status error';
        }
    });

    async function loadProjects() {
        try {
            const res = await fetch('api/get_projects.php');
            const data = await res.json();
            if (!data.success) throw new Error(data.error);

            const grid = $('projectsGrid');
            if (data.projects.length === 0) {
                grid.innerHTML = '<div class="empty-state"><p>No projects yet</p></div>';
                return;
            }

            grid.innerHTML = data.projects.map(p => `
                <div class="project-card">
                    <div class="project-card-header">
                        <h4>${escapeHtml(p.name)}</h4>
                        <span class="project-emp-count">${p.employee_count} employees</span>
                    </div>
                    ${p.description ? `<p class="project-desc">${escapeHtml(p.description)}</p>` : ''}
                    <div class="project-card-actions">
                        <button class="btn-sm btn-danger" onclick="AdminApp.archiveProject(${p.id}, '${escapeHtml(p.name)}')">Archive</button>
                    </div>
                </div>
            `).join('');

        } catch (e) {
            console.error('[Projects] Load failed:', e);
        }
    }

    AdminApp.archiveProject = async function (id, name) {
        if (!confirm(`Archive project "${name}"? Employees will be unassigned.`)) return;

        try {
            const res = await fetch('api/manage_project.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'archive', id })
            });
            const data = await res.json();
            if (data.success) {
                loadProjects();
                loadProjectSelects();
            }
        } catch (e) { /* ignore */ }
    };

    // ══════════════════════════════════════════
    // SETTINGS
    // ══════════════════════════════════════════

    async function loadSettings() {
        try {
            const res = await fetch('api/get_config.php');
            const data = await res.json();
            if (data.success) {
                $('settingInterval').value = data.config.screenshot_interval;
                $('intervalValue').textContent = data.config.screenshot_interval + 's';
                $('settingRetention').value = data.config.retention_days;
                $('retentionValue').textContent = data.config.retention_days + 'd';
                $('settingIdle').value = data.config.idle_threshold;
                $('idleValue').textContent = data.config.idle_threshold + 's';
                $('settingActivityInterval').value = data.config.activity_send_interval;
                $('activityIntervalValue').textContent = data.config.activity_send_interval + 's';
            }
        } catch (e) { /* ignore */ }
    }

    $('settingInterval').addEventListener('input', (e) => $('intervalValue').textContent = e.target.value + 's');
    $('settingRetention').addEventListener('input', (e) => $('retentionValue').textContent = e.target.value + 'd');
    $('settingIdle').addEventListener('input', (e) => $('idleValue').textContent = e.target.value + 's');
    $('settingActivityInterval').addEventListener('input', (e) => $('activityIntervalValue').textContent = e.target.value + 's');

    $('saveSettings').addEventListener('click', async () => {
        const payload = {
            screenshot_interval: parseInt($('settingInterval').value),
            retention_days: parseInt($('settingRetention').value),
            idle_threshold: parseInt($('settingIdle').value),
            activity_send_interval: parseInt($('settingActivityInterval').value)
        };

        try {
            const res = await fetch('api/update_config.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const data = await res.json();

            const status = $('settingsStatus');
            status.textContent = data.success ? '✓ Settings saved successfully' : '✗ Failed to save';
            status.style.color = data.success ? 'var(--success)' : 'var(--danger)';
            status.classList.add('visible');
            setTimeout(() => status.classList.remove('visible'), 3000);

        } catch (e) {
            console.error('[Settings] Save failed:', e);
        }
    });

    // ── Helper Functions ──
    function escapeHtml(str) {
        if (!str) return '';
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function formatTime(dateStr) {
        if (!dateStr) return '--:--';
        const d = new Date(dateStr);
        return d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true });
    }

    function formatDateTime(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    }

    function formatDate(dateStr) {
        if (!dateStr) return '';
        const d = new Date(dateStr);
        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 B';
        const sizes = ['B', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(1024));
        return (bytes / Math.pow(1024, i)).toFixed(1) + ' ' + sizes[i];
    }

    // ── Initialize ──
    loadEmployees();
    loadSettings();
    loadProjectSelects();

    refreshTimer = setInterval(loadEmployees, 10000);

})();
