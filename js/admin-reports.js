"use strict";

document.addEventListener("DOMContentLoaded", () => {
  if (typeof Chart === "undefined" || !window.docuflowAdminReportData) {
    return;
  }

  const data = window.docuflowAdminReportData;
  const palette = [
    "#5c4ae4",
    "#2563eb",
    "#16a34a",
    "#d97706",
    "#dc2626",
    "#8b5cf6",
    "#0891b2",
    "#db2777"
  ];
  const charts = [];

  const themeColors = () => {
    const dark = document.body.classList.contains("dark-mode");

    return {
      text: dark ? "#9ca3af" : "#6b7280",
      grid: dark ? "rgba(255, 255, 255, 0.08)" : "rgba(0, 0, 0, 0.06)",
      border: dark ? "#1f2937" : "#ffffff",
      tooltip: dark ? "#111827" : "#1f2937"
    };
  };

  const baseOptions = () => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: {
      duration: 450
    },
    plugins: {
      tooltip: {
        backgroundColor: themeColors().tooltip,
        padding: 12,
        cornerRadius: 8,
        displayColors: true
      }
    }
  });

  const monthlyCanvas = document.getElementById("adminMonthlyDocumentsChart");
  if (monthlyCanvas) {
    const colors = themeColors();
    const monthlyOptions = baseOptions();
    monthlyOptions.interaction = { mode: "index", intersect: false };
    monthlyOptions.plugins.legend = { display: false };
    monthlyOptions.scales = {
      x: {
        grid: { display: false },
        ticks: { color: colors.text }
      },
      y: {
        beginAtZero: true,
        grid: { color: colors.grid },
        ticks: { color: colors.text, precision: 0 }
      }
    };

    charts.push(new Chart(monthlyCanvas, {
      type: "line",
      data: {
        labels: data.monthlyLabels,
        datasets: [{
          label: "Documents Created",
          data: data.monthlyValues,
          fill: true,
          backgroundColor: "rgba(92, 74, 228, 0.14)",
          borderColor: "#5c4ae4",
          borderWidth: 3,
          tension: 0.4,
          pointBackgroundColor: "#5c4ae4",
          pointBorderColor: colors.border,
          pointBorderWidth: 2,
          pointRadius: 3,
          pointHoverRadius: 6
        }]
      },
      options: monthlyOptions
    }));
  }

  const statusCanvas = document.getElementById("adminDocumentStatusChart");
  if (statusCanvas) {
    const colors = themeColors();
    const statusOptions = baseOptions();
    statusOptions.cutout = "65%";
    statusOptions.plugins.legend = {
      position: "right",
      labels: {
        color: colors.text,
        padding: 16,
        usePointStyle: true,
        pointStyle: "circle",
        font: {
          size: 12,
          weight: "600"
        }
      }
    };

    charts.push(new Chart(statusCanvas, {
      type: "doughnut",
      data: {
        labels: data.documentStatusLabels,
        datasets: [{
          label: "Documents",
          data: data.documentStatusValues,
          backgroundColor: palette,
          borderColor: colors.border,
          borderWidth: 2,
          hoverOffset: 5
        }]
      },
      options: statusOptions
    }));
  }

  const officeTimelineCanvas = document.getElementById("adminOfficeDocumentsTimelineChart");
  if (officeTimelineCanvas) {
    const colors = themeColors();
    const timelineOptions = baseOptions();
    timelineOptions.interaction = { mode: "index", intersect: false };
    timelineOptions.plugins.legend = {
      position: "bottom",
      labels: {
        color: colors.text,
        padding: 14,
        usePointStyle: true,
        pointStyle: "circle",
        boxWidth: 8,
        font: { size: 11, weight: "600" }
      }
    };
    timelineOptions.scales = {
      x: {
        grid: { display: false },
        ticks: { color: colors.text }
      },
      y: {
        beginAtZero: true,
        grid: { color: colors.grid },
        ticks: { color: colors.text, precision: 0 }
      }
    };

    charts.push(new Chart(officeTimelineCanvas, {
      type: "line",
      data: {
        labels: data.monthlyLabels,
        datasets: data.officeDocumentSeries.map((series, index) => ({
          label: series.label,
          data: series.data,
          borderColor: palette[index % palette.length],
          backgroundColor: palette[index % palette.length],
          borderWidth: 2.5,
          pointRadius: 3,
          pointHoverRadius: 6,
          tension: 0.4,
          fill: false
        }))
      },
      options: timelineOptions
    }));
  }

  const formatDuration = (totalSeconds) => {
    if (totalSeconds === null) return "No completed route steps";
    if (totalSeconds < 60) return "Less than a minute";

    const days = Math.floor(totalSeconds / 86400);
    const hours = Math.floor((totalSeconds % 86400) / 3600);
    const minutes = Math.floor((totalSeconds % 3600) / 60);
    const parts = [];

    if (days) parts.push(`${days} day${days === 1 ? "" : "s"}`);
    if (hours) parts.push(`${hours} hour${hours === 1 ? "" : "s"}`);
    if (minutes) parts.push(`${minutes} minute${minutes === 1 ? "" : "s"}`);

    return parts.join(", ");
  };

  const officeCompletionCanvas = document.getElementById("adminOfficeCompletionChart");
  if (officeCompletionCanvas) {
    const colors = themeColors();
    const completionOptions = baseOptions();
    completionOptions.indexAxis = "y";
    completionOptions.plugins.legend = { display: false };
    completionOptions.plugins.tooltip.callbacks = {
      label: (context) => {
        const index = context.dataIndex;
        const duration = formatDuration(data.officeCompletionSeconds[index]);
        const steps = data.officeCompletedSteps[index];
        return `${duration} (${steps} completed step${steps === 1 ? "" : "s"})`;
      }
    };
    completionOptions.scales = {
      x: {
        beginAtZero: true,
        grid: { color: colors.grid },
        ticks: { color: colors.text },
        title: {
          display: true,
          text: "Average hours",
          color: colors.text
        }
      },
      y: {
        grid: { display: false },
        ticks: { color: colors.text }
      }
    };

    charts.push(new Chart(officeCompletionCanvas, {
      type: "bar",
      data: {
        labels: data.officeCompletionLabels,
        datasets: [{
          label: "Average Turnaround (Hours)",
          data: data.officeCompletionHours,
          backgroundColor: "#2563eb",
          borderRadius: 6,
          borderSkipped: false,
          maxBarThickness: 22
        }]
      },
      options: completionOptions
    }));
  }

  const updateChartThemes = () => {
    const colors = themeColors();

    charts.forEach((chart) => {
      if (chart.options.plugins.tooltip) {
        chart.options.plugins.tooltip.backgroundColor = colors.tooltip;
      }

      if (chart.options.plugins.legend?.labels) {
        chart.options.plugins.legend.labels.color = colors.text;
      }

      if (chart.options.scales?.x) {
        chart.options.scales.x.ticks.color = colors.text;
        chart.options.scales.y.ticks.color = colors.text;

        if (chart.options.scales.x.title) {
          chart.options.scales.x.title.color = colors.text;
        }

        if (chart.options.scales.x.grid.display !== false) {
          chart.options.scales.x.grid.color = colors.grid;
        }

        if (chart.options.scales.y.grid.display !== false) {
          chart.options.scales.y.grid.color = colors.grid;
        }
      }

      if (chart.config.type === "doughnut") {
        chart.data.datasets[0].borderColor = colors.border;
      }

      if (chart.canvas.id === "adminMonthlyDocumentsChart") {
        chart.data.datasets[0].pointBorderColor = colors.border;
      }

      chart.update();
    });
  };

  document.getElementById("themeToggle")?.addEventListener("click", updateChartThemes);
});
