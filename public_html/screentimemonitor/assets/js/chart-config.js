/**
 * ScreenMonitor — Chart.js Configuration
 * =======================================
 * Reusable chart factory functions with theme-aware colors.
 */

const ChartConfig = {
    // Get current theme colors
    getColors() {
        const style = getComputedStyle(document.documentElement);
        return {
            accent: style.getPropertyValue('--accent').trim() || '#6366f1',
            success: style.getPropertyValue('--success').trim() || '#22c55e',
            warning: style.getPropertyValue('--warning').trim() || '#f59e0b',
            danger: style.getPropertyValue('--danger').trim() || '#ef4444',
            info: style.getPropertyValue('--info').trim() || '#3b82f6',
            textPrimary: style.getPropertyValue('--text-primary').trim() || '#f1f5f9',
            textMuted: style.getPropertyValue('--text-muted').trim() || '#64748b',
            borderColor: style.getPropertyValue('--border-color').trim() || 'rgba(255,255,255,0.08)'
        };
    },

    // Common chart options
    getBaseOptions(colors) {
        return {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index'
            },
            plugins: {
                legend: {
                    labels: {
                        color: colors.textMuted,
                        font: { family: 'Inter', size: 12 },
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 16
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(0,0,0,0.8)',
                    titleFont: { family: 'Inter', size: 13 },
                    bodyFont: { family: 'Inter', size: 12 },
                    padding: 12,
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    cornerRadius: 8
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: colors.textMuted,
                        font: { family: 'Inter', size: 11 }
                    },
                    grid: {
                        color: colors.borderColor,
                        drawBorder: false
                    }
                },
                y: {
                    ticks: {
                        color: colors.textMuted,
                        font: { family: 'Inter', size: 11 }
                    },
                    grid: {
                        color: colors.borderColor,
                        drawBorder: false
                    }
                }
            }
        };
    },

    /**
     * Create Activity Line Chart (Mouse clicks + Key presses by hour)
     */
    createActivityChart(canvasId, hourlyData) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        // Destroy existing chart
        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();

        const colors = this.getColors();
        const hours = hourlyData.map(d => `${String(d.hour).padStart(2, '0')}:00`);

        return new Chart(canvas, {
            type: 'line',
            data: {
                labels: hours,
                datasets: [
                    {
                        label: 'Mouse Clicks',
                        data: hourlyData.map(d => d.total_clicks),
                        borderColor: colors.accent,
                        backgroundColor: colors.accent + '20',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2
                    },
                    {
                        label: 'Key Presses',
                        data: hourlyData.map(d => d.total_keys),
                        borderColor: colors.success,
                        backgroundColor: colors.success + '20',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                ...this.getBaseOptions(colors),
                plugins: {
                    ...this.getBaseOptions(colors).plugins,
                    legend: {
                        ...this.getBaseOptions(colors).plugins.legend,
                        position: 'top'
                    }
                }
            }
        });
    },

    /**
     * Create Idle Time Bar Chart
     */
    createIdleChart(canvasId, hourlyData) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();

        const colors = this.getColors();
        const hours = hourlyData.map(d => `${String(d.hour).padStart(2, '0')}:00`);

        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Idle Time (seconds)',
                    data: hourlyData.map(d => d.total_idle),
                    backgroundColor: hourlyData.map(d => {
                        if (d.total_idle > 1800) return colors.danger + '80';
                        if (d.total_idle > 600) return colors.warning + '80';
                        return colors.success + '80';
                    }),
                    borderRadius: 4,
                    borderSkipped: false,
                    barPercentage: 0.6
                }]
            },
            options: {
                ...this.getBaseOptions(colors),
                plugins: {
                    ...this.getBaseOptions(colors).plugins,
                    legend: { display: false }
                }
            }
        });
    },

    /**
     * Create Screenshots Bar Chart
     */
    createScreenshotChart(canvasId, screenshotsHourly) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return null;

        const existing = Chart.getChart(canvas);
        if (existing) existing.destroy();

        const colors = this.getColors();
        const hours = screenshotsHourly.map((_, i) => `${String(i).padStart(2, '0')}:00`);

        return new Chart(canvas, {
            type: 'bar',
            data: {
                labels: hours,
                datasets: [{
                    label: 'Screenshots',
                    data: screenshotsHourly,
                    backgroundColor: colors.info + '60',
                    borderColor: colors.info,
                    borderWidth: 1,
                    borderRadius: 4,
                    barPercentage: 0.5
                }]
            },
            options: {
                ...this.getBaseOptions(colors),
                plugins: {
                    ...this.getBaseOptions(colors).plugins,
                    legend: { display: false }
                },
                scales: {
                    ...this.getBaseOptions(colors).scales,
                    y: {
                        ...this.getBaseOptions(colors).scales.y,
                        beginAtZero: true,
                        ticks: {
                            ...this.getBaseOptions(colors).scales.y.ticks,
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
};
