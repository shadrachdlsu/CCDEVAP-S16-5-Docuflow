<?php require_once '../controllers/AdminDashboardController.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Document Volume Trends</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="../css/admin-dashboard.css" />
  </head>
  <body class="admin-body">
    <div class="admin-layout">
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <a href="admin-dashboard.php" class="logo-area">
              <span class="web-logo">Docuflow</span>
            </a>
            <a href="admin-dashboard.php" class="back-btn">
              <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
          </div>
          <div class="header-right">
            <div class="user-info">
              <span class="user-email">admin@office.gov</span>
              <span class="user-role">Administrator</span>
            </div>
            <div class="header-actions">
              <button class="icon-btn toggle-theme" id="themeToggle" aria-label="Toggle dark/light mode">
                <i class="fas fa-moon"></i>
              </button>
              <button class="icon-btn logout-btn" aria-label="Exit / Logout">
                <i class="fas fa-sign-out-alt"></i>
              </button>
            </div>
          </div>
        </header>

        <!-- Chart Content -->
        <section class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">Document Volume Trends</h2>
            <p class="preview-description">Number of documents processed (Finished) over the last 6 months.</p>
          </div>
          <div class="admin-preview-content">
            <canvas id="trendsChart"></canvas>
          </div>
        </section>
      </main>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // Theme and Logout
      document.addEventListener("DOMContentLoaded", () => {
        const themeToggle = document.getElementById("themeToggle");
        const logoutButton = document.querySelector(".logout-btn");

        // Load saved theme
        if (localStorage.getItem("docuflow-theme") === "dark") {
          document.body.classList.add("dark-mode");
        }

        if (themeToggle) {
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
            if (confirm("Are you sure you want to logout?")) {
              window.location.href = "../controllers/LogoutController.php";
            }
          });
        }

        // Documents Finished per Month
        const ctx = document.getElementById("trendsChart").getContext("2d");
        
        const chartData = <?= $trendsChartJson ?>;
        new Chart(ctx, {
          type: "line",
          data: {
            labels: chartData.labels,
            datasets: [{
              label: "Documents Finished",
              data: chartData.data,
              borderColor: "#5c4ae4",
              backgroundColor: "rgba(92, 74, 228, 0.1)",
              fill: true,
              tension: 0.3,
              pointBackgroundColor: "#5c4ae4",
              pointRadius: 5
            }]
          },
          options: {
            responsive: true,
            plugins: {
              legend: { position: "top" },
              title: {
                display: true,
                text: "Finished Documents Over the Last 6 Months",
                font: { size: 16 }
              }
            },
            scales: {
              y: {
                beginAtZero: true,
                title: { display: true, text: "Number of Documents" }
              },
              x: {
                title: { display: true, text: "Month" }
              }
            }
          }
        });
      });
    </script>
  </body>
</html>
