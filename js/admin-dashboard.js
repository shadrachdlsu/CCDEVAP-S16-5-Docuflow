"use strict";

document.addEventListener("DOMContentLoaded", () => {

    const themeToggle = document.getElementById("themeToggle");
    const pendingList = document.getElementById("pending-users-list");
    const pendingCountBadge = document.getElementById("pending-users-count");
    const kpiPendingActions = document.getElementById("kpi-pending-actions");

    /*
    |--------------------------------------------------------------------------
    | THEME TOGGLE LOGIC
    |--------------------------------------------------------------------------
    */

    if (localStorage.getItem("docuflow-theme") === "dark") {
        document.body.classList.add("dark-mode");
    }

    if (themeToggle) {
        const icon = themeToggle.querySelector("i");

        const updateIcon = () => {
            if (document.body.classList.contains("dark-mode")) {
                icon.classList.remove("fa-moon");
                icon.classList.add("fa-sun");
            } else {
                icon.classList.remove("fa-sun");
                icon.classList.add("fa-moon");
            }
        };

        updateIcon();

        themeToggle.addEventListener("click", () => {
            document.body.classList.toggle("dark-mode");
            const isDark = document.body.classList.contains("dark-mode");
            localStorage.setItem("docuflow-theme", isDark ? "dark" : "light");
            updateIcon();

            if (typeof updateChartThemes === "function") {
                updateChartThemes();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CHART.JS ANALYTICS INITIALIZATION
    |--------------------------------------------------------------------------
    */

    let bottleneckChartInstance = null;
    let volumeChartInstance = null;
    let statusChartInstance = null;
    let avgTimeChartInstance = null;
    let typesChartInstance = null;

    const getThemeColors = () => {
        const isDark = document.body.classList.contains("dark-mode");
        return {
            textColor: isDark ? "#9ca3af" : "#6b7280",
            gridColor: isDark ? "rgba(255, 255, 255, 0.08)" : "rgba(0, 0, 0, 0.06)",
            borderColor: isDark ? "#1f2937" : "#ffffff",
            tooltipBg: isDark ? "#111827" : "#1f2937"
        };
    };

    const initDashboardCharts = () => {
        if (typeof Chart === "undefined" || !window.docuflowCharts) {
            return;
        }

        const theme = getThemeColors();

        // 1. Office Bottlenecks Chart (Horizontal Bar)
        const bottleneckCtx = document.getElementById("bottlenecksChart")?.getContext("2d");
        if (bottleneckCtx && window.docuflowCharts.bottlenecks) {
            bottleneckChartInstance = new Chart(bottleneckCtx, {
                type: "bar",
                data: {
                    labels: window.docuflowCharts.bottlenecks.labels,
                    datasets: [{
                        label: "Pending Documents",
                        data: window.docuflowCharts.bottlenecks.data,
                        backgroundColor: "#5c4ae4",
                        borderRadius: 6,
                        barThickness: 16
                    }]
                },
                options: {
                    indexAxis: "y",
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: theme.tooltipBg
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: theme.gridColor
                            },
                            ticks: {
                                color: theme.textColor,
                                precision: 0
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: theme.textColor
                            }
                        }
                    }
                }
            });
        }

        // 2. 30-Day Processing Volume Chart (Smooth Line/Area)
        const volumeCtx = document.getElementById("volumeChart")?.getContext("2d");
        if (volumeCtx && window.docuflowCharts.volume) {
            volumeChartInstance = new Chart(volumeCtx, {
                type: "line",
                data: {
                    labels: window.docuflowCharts.volume.labels,
                    datasets: [{
                        label: "Documents Processed",
                        data: window.docuflowCharts.volume.data,
                        fill: true,
                        backgroundColor: "rgba(92, 74, 228, 0.15)",
                        borderColor: "#5c4ae4",
                        borderWidth: 3,
                        tension: 0.4,
                        pointBackgroundColor: "#5c4ae4",
                        pointRadius: 3,
                        pointHoverRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: theme.tooltipBg
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: theme.textColor,
                                maxTicksLimit: 10
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: theme.gridColor
                            },
                            ticks: {
                                color: theme.textColor,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }

        // 3. Document Status Distribution Chart (Doughnut)
        const statusCtx = document.getElementById("statusChart")?.getContext("2d");
        if (statusCtx && window.docuflowCharts.status) {
            statusChartInstance = new Chart(statusCtx, {
                type: "doughnut",
                data: {
                    labels: window.docuflowCharts.status.labels,
                    datasets: [{
                        label: "Documents",
                        data: window.docuflowCharts.status.data,
                        backgroundColor: window.docuflowCharts.status.colors,
                        borderColor: theme.borderColor,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: "65%",
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                color: theme.textColor,
                                padding: 16,
                                font: {
                                    size: 12,
                                    weight: "600"
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: theme.tooltipBg
                        }
                    }
                }
            });
        }

        // 4. Average Office Processing Time Chart (Bar Chart)
        const avgTimeCtx = document.getElementById("avgTimeChart")?.getContext("2d");
        if (avgTimeCtx && window.docuflowCharts.avgTime) {
            avgTimeChartInstance = new Chart(avgTimeCtx, {
                type: "bar",
                data: {
                    labels: window.docuflowCharts.avgTime.labels,
                    datasets: [{
                        label: "Avg Turnaround (Hours)",
                        data: window.docuflowCharts.avgTime.data,
                        backgroundColor: "#2563eb",
                        borderRadius: 6,
                        barThickness: 20
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: theme.tooltipBg,
                            callbacks: {
                                label: (context) => `Avg Time: ${context.parsed.y} hours`
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                color: theme.textColor
                            }
                        },
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: theme.gridColor
                            },
                            ticks: {
                                color: theme.textColor
                            }
                        }
                    }
                }
            });
        }

        // 5. Document Breakdown by Type Chart (Pie Chart)
        const typesCtx = document.getElementById("typesChart")?.getContext("2d");
        if (typesCtx && window.docuflowCharts.types) {
            typesChartInstance = new Chart(typesCtx, {
                type: "pie",
                data: {
                    labels: window.docuflowCharts.types.labels,
                    datasets: [{
                        label: "Documents",
                        data: window.docuflowCharts.types.data,
                        backgroundColor: window.docuflowCharts.types.colors,
                        borderColor: theme.borderColor,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: "right",
                            labels: {
                                color: theme.textColor,
                                padding: 14,
                                font: {
                                    size: 12,
                                    weight: "600"
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: theme.tooltipBg
                        }
                    }
                }
            });
        }
    };

    const updateChartThemes = () => {
        const theme = getThemeColors();

        if (bottleneckChartInstance) {
            bottleneckChartInstance.options.scales.x.grid.color = theme.gridColor;
            bottleneckChartInstance.options.scales.x.ticks.color = theme.textColor;
            bottleneckChartInstance.options.scales.y.ticks.color = theme.textColor;
            bottleneckChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            bottleneckChartInstance.update();
        }

        if (volumeChartInstance) {
            volumeChartInstance.options.scales.y.grid.color = theme.gridColor;
            volumeChartInstance.options.scales.x.ticks.color = theme.textColor;
            volumeChartInstance.options.scales.y.ticks.color = theme.textColor;
            volumeChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            volumeChartInstance.update();
        }

        if (statusChartInstance) {
            statusChartInstance.data.datasets[0].borderColor = theme.borderColor;
            statusChartInstance.options.plugins.legend.labels.color = theme.textColor;
            statusChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            statusChartInstance.update();
        }

        if (avgTimeChartInstance) {
            avgTimeChartInstance.options.scales.y.grid.color = theme.gridColor;
            avgTimeChartInstance.options.scales.x.ticks.color = theme.textColor;
            avgTimeChartInstance.options.scales.y.ticks.color = theme.textColor;
            avgTimeChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            avgTimeChartInstance.update();
        }

        if (typesChartInstance) {
            typesChartInstance.data.datasets[0].borderColor = theme.borderColor;
            typesChartInstance.options.plugins.legend.labels.color = theme.textColor;
            typesChartInstance.options.plugins.tooltip.backgroundColor = theme.tooltipBg;
            typesChartInstance.update();
        }
    };

    initDashboardCharts();

    /*
    |--------------------------------------------------------------------------
    | PENDING REGISTRATION APPROVAL & REJECTION HANDLER
    |--------------------------------------------------------------------------
    */

    if (pendingList) {
        pendingList.addEventListener("click", async (e) => {
            const approveBtn = e.target.closest(".btn-approve");
            const rejectBtn = e.target.closest(".btn-reject");

            if (!approveBtn && !rejectBtn) {
                return;
            }

            const button = approveBtn || rejectBtn;
            const action = approveBtn ? "approve" : "reject";
            const userId = button.dataset.id;
            const userRow = button.closest(".user-row");

            if (!userId || !userRow) {
                return;
            }

            const confirmMessage = action === "approve"
                ? "Are you sure you want to approve this user's registration?"
                : "Are you sure you want to reject and remove this user registration request?";

            if (!confirm(confirmMessage)) {
                return;
            }

            const rowButtons = userRow.querySelectorAll("button");
            rowButtons.forEach(btn => btn.disabled = true);

            try {
                const formData = new FormData();
                formData.append("action", action);
                formData.append("id", userId);

                const response = await fetch("admin-users-action.php", {
                    method: "POST",
                    body: formData
                });

                const data = await response.json();

                if (data.success) {
                    userRow.classList.add("removing");

                    setTimeout(() => {
                        userRow.remove();

                        if (pendingCountBadge) {
                            const currentCount = parseInt(pendingCountBadge.textContent, 10) || 0;
                            const newCount = Math.max(0, currentCount - 1);
                            pendingCountBadge.textContent = newCount;
                        }

                        if (kpiPendingActions) {
                            const currentKpi = parseInt(kpiPendingActions.textContent, 10) || 0;
                            const newKpi = Math.max(0, currentKpi - 1);
                            kpiPendingActions.textContent = newKpi;
                        }

                        const remainingRows = pendingList.querySelectorAll(".user-row");
                        if (remainingRows.length === 0) {
                            pendingList.innerHTML = `
                                <div class="empty-state-success">
                                    <p>No pending registrations.</p>
                                </div>
                            `;
                        }
                    }, 400);
                } else {
                    alert("Error: " + (data.error || "Failed to process request."));
                    rowButtons.forEach(btn => btn.disabled = false);
                }
            } catch (err) {
                console.error("AJAX Action Error:", err);
                alert("An unexpected network error occurred while processing the action.");
                rowButtons.forEach(btn => btn.disabled = false);
            }
        });
    }
});
