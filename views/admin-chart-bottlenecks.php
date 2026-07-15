<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Office Bottlenecks</title>
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
            <h2 class="section-title">Office Bottlenecks</h2>
            <p class="preview-description">Number of "Pending" documents per office. Helps identify which department is slowing down the workflow.</p>
          </div>
          <div class="admin-preview-content">
            <canvas id="bottlenecksChart"></canvas>
          </div>
        </section>
      </main>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
      // --- Theme Toggle & Logout (shared across pages) ---
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
              window.location.href = "login.php";
            }
          });
        }

        // --- Bar Chart: Pending Documents per Office ---
        const ctx = document.getElementById("bottlenecksChart").getContext("2d");
        
        fetch("../controllers/api_dashboard_stats.php?action=bottleneck_chart")
          .then(res => res.json())
          .then(data => {
            new Chart(ctx, {
              type: "bar",
              data: {
                labels: data.labels,
                datasets: [{
                  label: "Pending Documents",
                  data: data.data,
                  backgroundColor: [
                    "#5c4ae4",
                    "#2563eb",
                    "#059669",
                    "#f59e0b",
                    "#dc2626",
                    "#0f766e"
                  ],
                  borderRadius: 6
                }]
              },
              options: {
                responsive: true,
                plugins: {
                  legend: { display: false },
                  title: {
                    display: true,
                    text: "Pending Documents by Office",
                    font: { size: 16 }
                  }
                },
                scales: {
                  y: {
                    beginAtZero: true,
                    title: { display: true, text: "Number of Documents" }
                  },
                  x: {
                    title: { display: true, text: "Office / Department" }
                  }
                }
              }
            });
          })
          .catch(err => console.error("Error loading bottleneck data:", err));
      });
    </script>
  </body>
</html>
