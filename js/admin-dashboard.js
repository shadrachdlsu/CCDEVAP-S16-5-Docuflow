document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Load saved theme
  if (localStorage.getItem("docuflow-theme") === "dark") {
    document.body.classList.add("dark-mode");
  }

  if (themeToggle) {
    // Set initial icon state
    const icon = themeToggle.querySelector("i");
    if (document.body.classList.contains("dark-mode")) {
      icon.classList.remove("fa-moon");
      icon.classList.add("fa-sun");
    } else {
      icon.classList.remove("fa-sun");
      icon.classList.add("fa-moon");
    }

    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("dark-mode");
      const isDark = document.body.classList.contains("dark-mode");
      localStorage.setItem("docuflow-theme", isDark ? "dark" : "light");

      if (isDark) {
        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
      } else {
        icon.classList.remove("fa-sun");
        icon.classList.add("fa-moon");
      }
    });
  }

  if (logoutButton) {
    logoutButton.addEventListener("click", () => {
      window.location.href = "../controllers/LogoutController.php";
    });
  }

  // Charts
  if (typeof Chart !== 'undefined') {
    // Document Distribution Chart
    const docDistCanvas = document.getElementById("dynamicPieChart");
    if (docDistCanvas && typeof docDistChartData !== 'undefined') {
      new Chart(docDistCanvas.getContext("2d"), {
        type: "pie",
        data: {
          labels: docDistChartData.labels,
          datasets: [{
            data: docDistChartData.data,
            backgroundColor: ["#059669", "#2563eb", "#4c1d95"]
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: { position: 'right' }
          }
        }
      });
    }

    // Office Bottlenecks Chart
    const ctxBottleneck = document.getElementById("miniBottleneckChart");
    if (ctxBottleneck && typeof bottleneckChartData !== 'undefined') {
      new Chart(ctxBottleneck.getContext("2d"), {
        type: "bar",
        data: {
          labels: bottleneckChartData.labels,
          datasets: [{
            data: bottleneckChartData.data,
            backgroundColor: ["#5c4ae4", "#2563eb", "#059669", "#f59e0b", "#dc2626", "#0f766e"],
            borderRadius: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, title: { display: false } },
          scales: {
            y: { display: false },
            x: { display: false }
          },
          layout: { padding: 0 }
        }
      });
    }

    // Volume Trends Chart
    const ctxTrends = document.getElementById("miniTrendsChart");
    if (ctxTrends && typeof trendsChartData !== 'undefined') {
      new Chart(ctxTrends.getContext("2d"), {
        type: "line",
        data: {
          labels: trendsChartData.labels,
          datasets: [{
            data: trendsChartData.data,
            borderColor: "#5c4ae4",
            backgroundColor: "rgba(92, 74, 228, 0.1)",
            fill: true,
            tension: 0.3,
            pointRadius: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, title: { display: false } },
          scales: {
            y: { display: false },
            x: { display: false }
          },
          layout: { padding: 0 }
        }
      });
    }

    // Document Types Chart
    const ctxTypes = document.getElementById("miniTypesChart");
    if (ctxTypes && typeof typesChartData !== 'undefined') {
      new Chart(ctxTypes.getContext("2d"), {
        type: "pie",
        data: {
          labels: typesChartData.labels,
          datasets: [{
            data: typesChartData.data,
            backgroundColor: ["#5c4ae4", "#2563eb", "#059669", "#f59e0b", "#dc2626", "#0f766e"],
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false }, title: { display: false } },
          layout: { padding: 10 }
        }
      });
    }
  }
});
