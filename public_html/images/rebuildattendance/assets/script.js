/* ================================================================
   FitTrack Attendance — Main JavaScript
   ================================================================ */

// ---------- Live Clock ----------
function updateClock() {
    const now = new Date();
    
    const dateEl = document.getElementById('currentDate');
    if (dateEl) {
        dateEl.textContent = now.toLocaleDateString('en-IN', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });
    }
    
    const timeEl = document.getElementById('liveTime');
    if (timeEl) {
        timeEl.textContent = now.toLocaleTimeString('en-IN', {
            hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true
        });
    }
}

setInterval(updateClock, 1000);
updateClock();

// ---------- Geolocation & Attendance ----------
function markAttendance() {
    const btn = document.getElementById('markPresentBtn');
    if (!btn) return;
    
    btn.disabled = true;
    btn.querySelector('.mark-text').textContent = 'LOCATING...';
    
    if (!navigator.geolocation) {
        showMessage('Geolocation is not supported by your browser.', 'error');
        resetButton(btn);
        return;
    }
    
    navigator.geolocation.getCurrentPosition(
        function(position) {
            const lat = position.coords.latitude;
            const lng = position.coords.longitude;
            
            btn.querySelector('.mark-text').textContent = 'MARKING...';
            
            fetch('mark_attendance', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ latitude: lat, longitude: lng })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showMessage(data.message, 'success');
                    
                    // Show map
                    const mapSection = document.getElementById('liveMapSection');
                    if (mapSection) {
                        mapSection.classList.remove('hidden');
                        initMap(lat, lng, 'liveMap');
                    }
                    
                    // Update streak if returned
                    if (data.streak) {
                        setTimeout(() => location.reload(), 2000);
                    }
                } else {
                    showMessage(data.message, 'error');
                    resetButton(btn);
                }
            })
            .catch(err => {
                showMessage('Network error. Please try again.', 'error');
                resetButton(btn);
            });
        },
        function(error) {
            let msg = 'Location access denied. Please enable GPS.';
            if (error.code === 2) msg = 'Location unavailable. Please try again.';
            if (error.code === 3) msg = 'Location request timed out. Please try again.';
            showMessage(msg, 'error');
            resetButton(btn);
        },
        {
            enableHighAccuracy: true,
            timeout: 15000,
            maximumAge: 0
        }
    );
}

function resetButton(btn) {
    btn.disabled = false;
    btn.querySelector('.mark-text').textContent = 'MARK PRESENT';
}

function showAlreadyMarkedMessage() {
    alert("Your attendance is done for today! Next attendance will be allowed tomorrow between 4:00 AM and 8:00 AM.");
}

function showMessage(text, type) {
    const msgEl = document.getElementById('attendanceMessage');
    if (!msgEl) return;
    
    msgEl.textContent = text;
    msgEl.className = 'attendance-message ' + type;
    msgEl.classList.remove('hidden');
    
    if (type === 'error') {
        setTimeout(() => {
            msgEl.classList.add('hidden');
        }, 5000);
    }
}

// ---------- Map (Leaflet / OpenStreetMap) ----------
function initMap(lat, lng, containerId) {
    const container = document.getElementById(containerId);
    if (!container || typeof L === 'undefined') return;
    
    // Fitness center location
    const centerLat = 20.24532;
    const centerLng = 85.81090;
    
    const map = L.map(containerId).setView([lat, lng], 14);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 18
    }).addTo(map);
    
    // User marker
    L.marker([lat, lng]).addTo(map)
        .bindPopup('<strong>Your Location</strong>')
        .openPopup();
    
    // Fitness center marker
    L.marker([centerLat, centerLng], {
        icon: L.divIcon({
            className: 'fitness-marker',
            html: '🏋️',
            iconSize: [30, 30],
            iconAnchor: [15, 15]
        })
    }).addTo(map)
        .bindPopup('<strong>Fitness Center</strong>');
    
    L.circle([centerLat, centerLng], {
        radius: 1000,
        color: '#00d4ff',
        fillColor: '#00d4ff',
        fillOpacity: 0.08,
        weight: 2
    }).addTo(map);
    
    // Fit bounds to show both markers
    const bounds = L.latLngBounds([lat, lng], [centerLat, centerLng]);
    map.fitBounds(bounds.pad(0.3));
}

// ---------- Tab Switcher ----------
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            
            // Update buttons
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            // Update content
            document.querySelectorAll('.tab-content').forEach(tc => {
                tc.classList.remove('active');
            });
            const target = document.getElementById('tab-' + tabName);
            if (target) target.classList.add('active');
        });
    });
});

// ---------- Progress Charts ----------
function initProgressCharts(monthlyData, streakData) {
    if (typeof Chart === 'undefined') return;
    
    // Chart.js defaults for dark theme
    Chart.defaults.color = '#a0aec0';
    Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.06)';
    
    // Monthly Attendance Bar Chart
    const monthlyCtx = document.getElementById('monthlyChart');
    if (monthlyCtx) {
        new Chart(monthlyCtx, {
            type: 'bar',
            data: {
                labels: monthlyData.map(d => d.label),
                datasets: [{
                    label: 'Present Days',
                    data: monthlyData.map(d => d.count),
                    backgroundColor: 'rgba(0, 212, 255, 0.6)',
                    borderColor: '#00d4ff',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1 },
                        grid: { color: 'rgba(255,255,255,0.04)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
    
    // Attendance Percentage Line Chart
    const pctCtx = document.getElementById('percentageChart');
    if (pctCtx) {
        new Chart(pctCtx, {
            type: 'line',
            data: {
                labels: monthlyData.map(d => d.label),
                datasets: [{
                    label: 'Attendance %',
                    data: monthlyData.map(d => d.percentage),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124, 58, 237, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#7c3aed',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: v => v + '%' },
                        grid: { color: 'rgba(255,255,255,0.04)' }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
}

// ---------- PWA Install ----------
let deferredPrompt;

window.addEventListener('beforeinstallprompt', (e) => {
    e.preventDefault();
    deferredPrompt = e;
    
    const banner = document.getElementById('pwaInstallBanner');
    if (banner) {
        banner.classList.remove('hidden');
    }
});

document.addEventListener('DOMContentLoaded', function() {
    const installBtn = document.getElementById('pwaInstallBtn');
    const dismissBtn = document.getElementById('pwaDismissBtn');
    const banner = document.getElementById('pwaInstallBanner');
    
    if (installBtn) {
        installBtn.addEventListener('click', async () => {
            if (deferredPrompt) {
                deferredPrompt.prompt();
                const result = await deferredPrompt.userChoice;
                deferredPrompt = null;
                if (banner) banner.classList.add('hidden');
            }
        });
    }
    
    if (dismissBtn) {
        dismissBtn.addEventListener('click', () => {
            if (banner) banner.classList.add('hidden');
        });
    }
});

// ---------- Register Service Worker ----------
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('service-worker.js')
            .then(reg => console.log('SW registered:', reg.scope))
            .catch(err => console.log('SW registration failed:', err));
    });
}

// ---------- Daily Motivation Popup ----------
document.addEventListener('DOMContentLoaded', function() {
    const motivationModal = document.getElementById('motivationModal');
    const closeBtn = document.getElementById('closeMotivationBtn');
    const quoteEl = document.getElementById('motivationQuote');
    
    if (!motivationModal || !quoteEl) return;

    const quotes = [
        "\"The miracle isn't that I finished. The miracle is that I had the courage to start.\" – John Bingham",
        "\"Run when you can, walk if you have to, crawl if you must; just never give up.\" – Dean Karnazes",
        "\"It does not matter how slowly you go as long as you do not stop.\" – Confucius",
        "\"Someone who is busier than you is running right now.\" – Nike",
        "\"A short run is better than no run.\" – Jinia Roy",
        "\"I don't run to add days to my life, I run to add life to my days.\" – Ronald Rook",
        "\"Your body can stand almost anything. It's your mind that you have to convince.\" – Jinia Roy",
        "\"The hardest part of any run is the first step out the door.\" – Jinia Roy",
        "\"Every run is a work of art, a drawing on the canvas of the earth.\" – Jinia Roy",
        "\"Running is about finding your inner peace, and so is a life well lived.\" – Dean Karnazes"
    ];

    const todayStr = new Date().toDateString();
    const lastMotivationDate = localStorage.getItem('lastMotivationDate');

    if (lastMotivationDate !== todayStr) {
        // Show random quote
        const randomQuote = quotes[Math.floor(Math.random() * quotes.length)];
        quoteEl.textContent = randomQuote;
        
        // Show modal after a short delay for better UX
        setTimeout(() => {
            motivationModal.classList.remove('hidden');
        }, 1000);

        // Save today's date so it doesn't show again
        localStorage.setItem('lastMotivationDate', todayStr);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            motivationModal.classList.add('hidden');
        });
    }

    // Close on outside click
    motivationModal.addEventListener('click', (e) => {
        if (e.target === motivationModal) {
            motivationModal.classList.add('hidden');
        }
    });
});
