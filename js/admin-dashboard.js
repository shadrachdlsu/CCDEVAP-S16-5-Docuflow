document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Removed hardcoded dashboardViews array

  function renderPieView(view) {
    return `
      <div class="preview-layout">
        <div class="pie-chart" style="background: conic-gradient(${view.gradient})" aria-label="${view.title}">
          <span>${view.total}</span>
        </div>
        <div class="preview-list">
          ${view.rows.map(row => `
            <div class="preview-row">
              <span class="preview-swatch" style="background: ${row.color}"></span>
              <span>${row.label}</span>
              <strong>${row.value}</strong>
            </div>
          `).join("")}
        </div>
      </div>
    `;
  }

  function renderOfficeView(view) {
    return `
      <div class="office-grid">
        ${view.offices.map(office => `
          <div class="office-card">
            <strong>${office.name}</strong>
            <span>${office.detail}</span>
          </div>
        `).join("")}
      </div>
    `;
  }

  function renderPendingView(view) {
    return `
      <div class="pending-list">
        ${view.documents.map(doc => `
          <div class="pending-card">
            <div>
              <strong>${doc.title}</strong>
              <span>${doc.id} - ${doc.office}</span>
            </div>
            <span class="pending-status">Pending</span>
          </div>
        `).join("")}
      </div>
    `;
  }

  // Task 2: Fetch data from the PHP API and render a Chart.js pie chart
  const docDistContent = document.getElementById("doc-dist-content");
  if (docDistContent) {
    // 1. Write a basic fetch() request to call the PHP file
    fetch("../controllers/admin_api_dashboard_charts.php")
      .then(response => response.json()) // Parse the JSON response
      .then(data => {
        // Create a canvas element to hold the Chart.js pie chart
        docDistContent.innerHTML = '<canvas id="dynamicPieChart" style="max-height: 250px;"></canvas>';
        const ctx = document.getElementById("dynamicPieChart").getContext("2d");

        // 2. Take the JSON response and plug it into a standard Chart.js pie chart
        new Chart(ctx, {
          // Keep the Chart.js configuration very basic (type, data, labels, background colors).
          type: "pie",
          data: {
            labels: data.labels, // Data from our PHP API
            datasets: [{
              data: data.data,     // Numbers from our PHP API
              backgroundColor: [
                "#059669", // Green for Pending
                "#2563eb", // Blue for Signed
                "#4c1d95"  // Purple for Finished
              ]
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: {
                position: 'right'
              }
            }
          }
        });
      })
      .catch(error => {
        console.error("Error fetching chart data:", error);
        docDistContent.innerHTML = "<p>Error loading chart data.</p>";
      });
  }

  const userDistContent = document.getElementById("user-dist-content");
  if (userDistContent) {
    fetch("../controllers/admin_api_dashboard_stats.php?action=user_distribution")
      .then(res => res.json())
      .then(data => {
        userDistContent.innerHTML = renderPieView(data);
      });
  }

  const officesContent = document.getElementById("offices-content");
  if (officesContent) {
    fetch("../controllers/admin_api_dashboard_stats.php?action=office_directory")
      .then(res => res.json())
      .then(data => {
        officesContent.innerHTML = renderOfficeView(data);
      });
  }

  const pendingContent = document.getElementById("pending-content");
  if (pendingContent) {
    fetch("../controllers/admin_api_dashboard_stats.php?action=pending_documents")
      .then(res => res.json())
      .then(data => {
        pendingContent.innerHTML = renderPendingView(data);
      });
  }

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
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "login.php";
      }
    });
  }

  // REport Grid
  if (typeof Chart !== 'undefined') {
    // Office Bottlenecks
    const ctxBottleneck = document.getElementById("miniBottleneckChart");
    if (ctxBottleneck) {
      fetch("../controllers/admin_api_dashboard_stats.php?action=bottleneck_chart")
        .then(res => res.json())
        .then(data => {
          new Chart(ctxBottleneck.getContext("2d"), {
            type: "bar",
            data: {
              labels: data.labels,
              datasets: [{
                data: data.data,
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
        });
    }

    // 2. Volume Trends 
    const ctxTrends = document.getElementById("miniTrendsChart");
    if (ctxTrends) {
      fetch("../controllers/admin_api_dashboard_stats.php?action=volume_trends")
        .then(res => res.json())
        .then(data => {
          new Chart(ctxTrends.getContext("2d"), {
            type: "line",
            data: {
              labels: data.labels,
              datasets: [{
                data: data.data,
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
        });
    }

    // 3. Document Types
    const ctxTypes = document.getElementById("miniTypesChart");
    if (ctxTypes) {
      fetch("../controllers/admin_api_dashboard_stats.php?action=types_chart")
        .then(res => res.json())
        .then(data => {
          new Chart(ctxTypes.getContext("2d"), {
            type: "pie",
            data: {
              labels: data.labels,
              datasets: [{
                data: data.data,
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
        });
    }
  }
});
