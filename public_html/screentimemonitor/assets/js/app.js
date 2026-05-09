/**
 * ScreenMonitor — Employee Client App (v2)
 * ==========================================
 * Screen capture, mouse/keyboard tracking, idle detection,
 * AUTO-PAUSE after 2 min idle, close alert beacon, heartbeat.
 */

(function () {
    'use strict';

    // ── State ──
    const state = {
        monitoring: false,
        paused: false,
        manualPause: false,
        stream: null,
        captureInterval: null,
        configPollInterval: null,
        activitySendInterval: null,
        heartbeatInterval: null,
        timerInterval: null,
        idleCheckInterval: null, // NEW: idle check timer

        config: {
            screenshot_interval: 10,
            idle_threshold: 120,
            activity_send_interval: 30
        },

        activity: {
            mouseClicks: 0,
            mouseDistance: 0,
            keyPresses: 0,
            lastMouseX: 0,
            lastMouseY: 0,
            lastInputTime: Date.now(),
            periodStart: null
        },

        totals: {
            screenshots: 0,
            mouseClicks: 0,
            keyPresses: 0,
            idleSeconds: 0,
            activeSeconds: 0,
            todayInitialSeconds: 0
        },

        startTime: null,
        pausedElapsed: 0,       // NEW: accumulated time before pause
        pauseStartTime: null    // NEW: when pause started
    };

    const IDLE_PAUSE_MS = 120000; // 2 minutes = auto-pause

    // ── DOM References ──
    const $ = id => document.getElementById(id);

    const dom = {
        startBtn: $('startMonitorBtn'),
        endMonitorBtn: $('endMonitorBtn'),
        playIcon: $('playIcon'),
        pauseIcon: $('pauseIcon'),
        btnLabel: $('monitorBtnLabel'),
        btnSub: $('monitorBtnSub'),
        statusBanner: $('statusBanner'),
        statusText: $('statusText'),
        pulseDot: $('pulseDot'),
        elapsedTime: $('elapsedTime'),
        screenshotCount: $('screenshotCount'),
        activityScore: $('activityScore'),
        mouseClicks: $('mouseClicks'),
        keyPresses: $('keyPresses'),
        previewSection: $('previewSection'),
        previewImage: $('previewImage'),
        previewTime: $('previewTime'),
        activitySegment: $('activitySegment'),
        pauseModal: $('pauseModal'),
        realtimeClock: $('realtimeClock'),
        monthlyHours: $('monthlyHours'),
        logoutBtn: $('logoutBtn'),
        themeToggle: $('themeToggle')
    };

    // Keep track of the current day date to auto-reset at midnight
    state.currentDay = new Date().getDate();

    function formatElapsedTime(totalSeconds) {
        const h = String(Math.floor(totalSeconds / 3600)).padStart(2, '0');
        const m = String(Math.floor((totalSeconds % 3600) / 60)).padStart(2, '0');
        const s = String(totalSeconds % 60).padStart(2, '0');
        return `${h}:${m}:${s}`;
    }

    // ── Live Clock ──
    setInterval(() => {
        if(dom.realtimeClock) dom.realtimeClock.textContent = new Date().toLocaleTimeString();
    }, 1000);

    // ── Theme Toggle ──
    const savedTheme = localStorage.getItem('sm_theme') || 'dark';
    document.documentElement.setAttribute('data-theme', savedTheme);

    dom.themeToggle.addEventListener('click', () => {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('sm_theme', next);
    });

    // ── Logout ──
    dom.logoutBtn.addEventListener('click', async () => {
        if (state.monitoring) {
            stopMonitoring();
        }
        try {
            await fetch('api/logout.php', { method: 'POST' });
        } catch (e) { /* ignore */ }
        window.location.href = 'index.php';
    });

    // ── Load Config ──
    async function loadConfig() {
        try {
            const res = await fetch('api/get_config.php');
            const data = await res.json();
            if (data.success) {
                const oldInterval = state.config.screenshot_interval;
                state.config = data.config;
                if (data.today_seconds !== undefined) {
                    state.totals.todayInitialSeconds = data.today_seconds;
                    if (!state.monitoring) updateTimer(); // display immediately
                }
                if (data.month_seconds !== undefined && dom.monthlyHours) {
                    dom.monthlyHours.textContent = formatElapsedTime(data.month_seconds);
                }

                if (state.monitoring && !state.paused && oldInterval !== data.config.screenshot_interval) {
                    clearInterval(state.captureInterval);
                    state.captureInterval = setInterval(captureScreenshot, state.config.screenshot_interval * 1000);
                }
            }
        } catch (e) {
            console.warn('[Config] Failed to load:', e);
        }
    }

    // ── Start/Stop Toggle ──
    dom.startBtn.addEventListener('click', () => {
        if (!state.monitoring) {
            startMonitoring();
        } else if (!state.paused) {
            pauseMonitoring(true); // manual pause
        } else {
            resumeMonitoring(true); // manual resume
        }
    });

    dom.endMonitorBtn.addEventListener('click', () => {
        if (state.monitoring) {
            if (confirm("Are you sure you want to end your shift? Tracking will stop entirely.")) {
                stopMonitoring();
            }
        }
    });

    async function startMonitoring() {
        try {
            state.stream = await navigator.mediaDevices.getDisplayMedia({
                video: {
                    displaySurface: 'monitor',
                    width: { ideal: 1920 },
                    height: { ideal: 1080 },
                    frameRate: { ideal: 1 }
                }
            });

            state.stream.getVideoTracks()[0].addEventListener('ended', () => {
                stopMonitoring();
            });

            state.monitoring = true;
            state.paused = false;
            state.manualPause = false;
            state.startTime = Date.now();
            state.pausedElapsed = 0;
            state.pauseStartTime = null;
            state.activity.periodStart = new Date().toISOString().slice(0, 19).replace('T', ' ');

            updateUI_monitoring();
            await loadConfig();

            // Capture first screenshot immediately
            captureScreenshot();
            state.captureInterval = setInterval(captureScreenshot, state.config.screenshot_interval * 1000);

            state.configPollInterval = setInterval(loadConfig, 30000);
            state.activitySendInterval = setInterval(sendActivityData, state.config.activity_send_interval * 1000);
            state.heartbeatInterval = setInterval(sendHeartbeat, 15000);
            state.timerInterval = setInterval(updateTimer, 1000);

            // NEW: Idle check every second for auto-pause
            state.idleCheckInterval = setInterval(checkIdleForPause, 1000);

            startActivityTracking();
            console.log('[Monitor] Started — Interval:', state.config.screenshot_interval + 's');

        } catch (err) {
            console.error('[Monitor] Failed:', err);
            dom.statusText.textContent = err.name === 'NotAllowedError' ? 'Screen sharing denied' : 'Failed to start monitoring';
        }
    }

    function stopMonitoring() {
        state.monitoring = false;
        state.paused = false;
        state.manualPause = false;

        if (state.stream) {
            state.stream.getTracks().forEach(t => t.stop());
            state.stream = null;
        }

        clearInterval(state.captureInterval);
        clearInterval(state.configPollInterval);
        clearInterval(state.activitySendInterval);
        clearInterval(state.heartbeatInterval);
        clearInterval(state.timerInterval);
        clearInterval(state.idleCheckInterval);

        sendActivityData();
        stopActivityTracking();
        updateUI_stopped();
        console.log('[Monitor] Stopped');
    }

    // ════════════════════════════════════════════
    // NEW: AUTO-PAUSE AFTER 2 MINUTES IDLE
    // ════════════════════════════════════════════

    function checkIdleForPause() {
        if (!state.monitoring || state.paused) return;

        const idleMs = Date.now() - state.activity.lastInputTime;

        if (idleMs >= IDLE_PAUSE_MS) {
            pauseMonitoring(false); // auto pause
        }
    }

    function pauseMonitoring(isManual = false) {
        if (state.paused) return;
        state.paused = true;
        state.manualPause = isManual;

        if (!isManual && dom.pauseModal) {
            dom.pauseModal.style.display = 'flex';
        }

        // Stop screenshot capture (but keep stream alive)
        clearInterval(state.captureInterval);
        state.captureInterval = null;

        // Record when we paused for timer calculation
        state.pauseStartTime = Date.now();

        // Send idle status to server
        sendHeartbeat();

        updateUI_paused();
        console.log(isManual ? '[Monitor] Manually paused' : '[Monitor] Auto-paused — no activity for 2 minutes');
    }

    function resumeMonitoring(isManual = false) {
        if (!state.paused || !state.monitoring) return;
        
        // If it's a manual pause, don't auto-resume on mouse movement or random clicks
        if (state.manualPause && !isManual) return;

        state.paused = false;
        state.manualPause = false;

        if (dom.pauseModal) dom.pauseModal.style.display = 'none';

        // Accumulate paused duration
        if (state.pauseStartTime) {
            state.pausedElapsed += (Date.now() - state.pauseStartTime);
            state.pauseStartTime = null;
        }

        // Restart screenshot capture
        captureScreenshot(); // Immediate capture on resume
        state.captureInterval = setInterval(captureScreenshot, state.config.screenshot_interval * 1000);

        // Send active status
        sendHeartbeat();

        updateUI_monitoring();
        console.log(isManual ? '[Monitor] Manually resumed' : '[Monitor] Resumed from auto-pause');
    }

    // ── Screen Capture ──
    async function captureScreenshot() {
        if (!state.monitoring || !state.stream || state.paused) return;

        try {
            const track = state.stream.getVideoTracks()[0];
            if (!track || track.readyState !== 'live') {
                stopMonitoring();
                return;
            }

            const video = document.createElement('video');
            video.srcObject = state.stream;
            video.muted = true;

            await new Promise((resolve) => {
                video.onloadedmetadata = () => { video.play(); resolve(); };
            });
            await new Promise(r => requestAnimationFrame(r));

            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);

            const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.6));
            const reader = new FileReader();

            reader.onloadend = async () => {
                const base64 = reader.result;
                dom.previewImage.src = base64;
                dom.previewSection.style.display = 'block';
                dom.previewTime.textContent = new Date().toLocaleTimeString();

                try {
                    const res = await fetch('api/upload_screenshot.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ image: base64 })
                    });
                    const data = await res.json();
                    if (data.success) {
                        state.totals.screenshots++;
                        dom.screenshotCount.textContent = state.totals.screenshots;
                    }
                } catch (e) {
                    console.warn('[Screenshot] Upload failed:', e);
                }
            };
            reader.readAsDataURL(blob);

            video.pause();
            video.srcObject = null;

        } catch (err) {
            console.warn('[Screenshot] Capture failed:', err);
        }
    }

    // ── Activity Tracking ──
    function startActivityTracking() {
        document.addEventListener('mousemove', onMouseMove);
        document.addEventListener('click', onClick);
        document.addEventListener('keydown', onKeyDown);
    }

    function stopActivityTracking() {
        document.removeEventListener('mousemove', onMouseMove);
        document.removeEventListener('click', onClick);
        document.removeEventListener('keydown', onKeyDown);
    }

    function onMouseMove(e) {
        if (!state.monitoring) return;
        state.activity.lastInputTime = Date.now();

        const dx = e.clientX - state.activity.lastMouseX;
        const dy = e.clientY - state.activity.lastMouseY;
        state.activity.mouseDistance += Math.sqrt(dx * dx + dy * dy);
        state.activity.lastMouseX = e.clientX;
        state.activity.lastMouseY = e.clientY;

        // Resume if paused (will be ignored if manualPause is true)
        if (state.paused) resumeMonitoring(false);
    }

    function onClick() {
        if (!state.monitoring) return;
        state.activity.lastInputTime = Date.now();
        state.activity.mouseClicks++;
        state.totals.mouseClicks++;
        dom.mouseClicks.textContent = state.totals.mouseClicks;
        if (state.paused) resumeMonitoring(false);
    }

    function onKeyDown() {
        if (!state.monitoring) return;
        state.activity.lastInputTime = Date.now();
        state.activity.keyPresses++;
        state.totals.keyPresses++;
        dom.keyPresses.textContent = state.totals.keyPresses;
        if (state.paused) resumeMonitoring(false);
    }

    // ── Send Activity ──
    async function sendActivityData() {
        if (!state.monitoring && state.activity.mouseClicks === 0 && state.activity.keyPresses === 0) return;

        const now = new Date().toISOString().slice(0, 19).replace('T', ' ');
        const idleMs = Date.now() - state.activity.lastInputTime;
        const idleSeconds = Math.floor(idleMs / 1000);

        const payload = {
            mouse_clicks: state.activity.mouseClicks,
            mouse_distance: Math.round(state.activity.mouseDistance),
            key_presses: state.activity.keyPresses,
            idle_seconds: Math.min(idleSeconds, state.config.activity_send_interval),
            period_start: state.activity.periodStart || now,
            period_end: now
        };

        state.activity.mouseClicks = 0;
        state.activity.mouseDistance = 0;
        state.activity.keyPresses = 0;
        state.activity.periodStart = now;

        try {
            await fetch('api/activity_log.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
        } catch (e) {
            console.warn('[Activity] Send failed:', e);
        }
    }

    // ── Heartbeat ──
    async function sendHeartbeat() {
        if (!state.monitoring) return;

        try {
            await fetch('api/activity_log.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    heartbeat: true,
                    status: state.paused ? 'idle' : 'active'
                })
            });
        } catch (e) { /* ignore */ }
    }

    // ── Timer ──
    function updateTimer() {
        // Handle midnight rollover auto-reset
        if (new Date().getDate() !== state.currentDay) {
            state.currentDay = new Date().getDate();
            state.totals.todayInitialSeconds = 0;
            if (state.monitoring) {
                state.pausedElapsed = 0;
                state.startTime = Date.now();
                if (state.paused) {
                    state.pauseStartTime = Date.now();
                }
                // Clear local activity metrics for the new day
                state.totals.mouseClicks = 0;
                state.totals.keyPresses = 0;
                dom.mouseClicks.textContent = "0";
                dom.keyPresses.textContent = "0";
            }
        }

        let elapsed = state.totals.todayInitialSeconds || 0;

        if (state.startTime) {
            // Don't count paused time locally
            if (state.paused && state.pauseStartTime) {
                elapsed += Math.floor((state.pauseStartTime - state.startTime - state.pausedElapsed) / 1000);
            } else {
                elapsed += Math.floor((Date.now() - state.startTime - state.pausedElapsed) / 1000);
            }
        }

        dom.elapsedTime.textContent = formatElapsedTime(elapsed);

        // Activity percentage
        const idleMs = Date.now() - state.activity.lastInputTime;
        const idleRatio = Math.min(idleMs / IDLE_PAUSE_MS, 1);
        const activePercent = Math.round((1 - idleRatio) * 100);
        dom.activityScore.textContent = activePercent + '%';
        dom.activitySegment.style.width = activePercent + '%';
    }

    // ── UI Updates ──
    function updateUI_monitoring() {
        dom.startBtn.classList.add('monitoring');
        dom.startBtn.classList.remove('paused-state');
        dom.playIcon.style.display = 'none';
        dom.pauseIcon.style.display = 'block';
        dom.btnLabel.textContent = 'Pause';
        dom.btnSub.textContent = 'Temporarily stop capture';

        // Enable End Shift
        dom.endMonitorBtn.disabled = false;
        dom.endMonitorBtn.style.opacity = '1';
        dom.endMonitorBtn.style.cursor = 'pointer';
        dom.endMonitorBtn.style.borderColor = 'var(--error)';

        dom.pulseDot.classList.add('active');
        dom.pulseDot.classList.remove('idle');
        dom.statusText.textContent = 'Monitoring Active';
        dom.statusBanner.classList.remove('paused');
    }

    function updateUI_stopped() {
        dom.startBtn.classList.remove('monitoring', 'paused-state');
        dom.playIcon.style.display = 'block';
        dom.pauseIcon.style.display = 'none';
        dom.btnLabel.textContent = 'Start';
        dom.btnSub.textContent = 'Click to begin counting';

        // Disable End Shift
        dom.endMonitorBtn.disabled = true;
        dom.endMonitorBtn.style.opacity = '0.4';
        dom.endMonitorBtn.style.cursor = 'not-allowed';
        dom.endMonitorBtn.style.borderColor = 'transparent';

        dom.pulseDot.classList.remove('active', 'idle');
        dom.statusText.textContent = 'Monitoring Stopped';
        dom.statusBanner.classList.remove('paused');
    }

    function updateUI_paused() {
        dom.startBtn.classList.add('paused-state');
        dom.playIcon.style.display = 'block';
        dom.pauseIcon.style.display = 'none';
        dom.btnLabel.textContent = 'Resume';
        dom.btnSub.textContent = 'Continue tracking';

        dom.pulseDot.classList.remove('active');
        dom.pulseDot.classList.add('idle');
        dom.statusText.textContent = '⏸ Paused';
        dom.statusBanner.classList.add('paused');
    }

    // ── Init ──
    loadConfig();

    // ════════════════════════════════════════════
    // CLOSE ALERT — warn & send beacon
    // ════════════════════════════════════════════
    window.addEventListener('beforeunload', (e) => {
        if (state.monitoring) {
            // Send beacon to notify server
            const data = JSON.stringify({
                user_id: window.SM_USER?.id,
                timestamp: new Date().toISOString()
            });
            navigator.sendBeacon('api/alert_close.php', data);

            // Show browser confirmation dialog
            e.preventDefault();
            e.returnValue = '⚠️ Monitoring is active! Closing will log you out and send an alert to admin. Continue?';
        }
    });

})();
