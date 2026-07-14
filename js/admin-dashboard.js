document.addEventListener("DOMContentLoaded", () => {
  const statCards = document.querySelectorAll(".stat-card");
  const previewTitle = document.getElementById("preview-title");
  const previewDescription = document.getElementById("preview-description");
  const previewContent = document.getElementById("admin-preview-content");
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  const dashboardViews = {
    documents: {
      title: "Document Distribution",
      description: "Breakdown of documents by current status.",
      total: "1,234",
      gradient:
        "#4c1d95 0 46%, #2563eb 46% 68%, #059669 68% 84%, #f59e0b 84% 100%",
      rows: [
        { label: "Completed", value: "568", color: "#4c1d95" },
        { label: "In Transit", value: "271", color: "#2563eb" },
        { label: "Pending", value: "198", color: "#059669" },
        { label: "Archived", value: "197", color: "#f59e0b" },
      ],
    },
    users: {
      title: "User Distribution",
      description: "Percentage of users by role.",
      total: "248",
      gradient:
        "#4c1d95 0 52%, #0f766e 52% 78%, #dc2626 78% 90%, #64748b 90% 100%",
      rows: [
        { label: "Members - 52%", value: "129", color: "#4c1d95" },
        { label: "Secretaries - 26%", value: "64", color: "#0f766e" },
        { label: "Admins - 12%", value: "30", color: "#dc2626" },
        { label: "Inactive - 10%", value: "25", color: "#64748b" },
      ],
    },
    offices: {
      title: "Office Directory",
      description: "Registered offices and assigned document load.",
      offices: [
        { name: "Finance", detail: "312 documents assigned" },
        { name: "Human Resources", detail: "184 documents assigned" },
        { name: "Administration", detail: "221 documents assigned" },
        { name: "Legal", detail: "97 documents assigned" },
        { name: "Operations", detail: "143 documents assigned" },
        { name: "Records Office", detail: "277 documents assigned" },
      ],
    },
    pending: {
      title: "Pending Documents",
      description: "Documents waiting for action across offices.",
      documents: [
        {
          title: "Budget Proposal FY 2024",
          id: "DOC-2024-001",
          office: "Finance",
        },
        {
          title: "Employee Leave Request",
          id: "DOC-2024-002",
          office: "HR",
        },
        {
          title: "Procurement Review",
          id: "DOC-2024-004",
          office: "Administration",
        },
        {
          title: "Contract Review Packet",
          id: "DOC-2024-012",
          office: "Legal",
        },
      ],
    },
  };

  function renderPieView(view) {
    return `
      <div class="preview-layout">
        <div
          class="pie-chart"
          style="background: conic-gradient(${view.gradient})"
          aria-label="${view.title}"
        >
          <span>${view.total}</span>
        </div>
        <div class="preview-list">
          ${view.rows
            .map(
              (row) => `
                <div class="preview-row">
                  <span class="preview-swatch" style="background: ${row.color}"></span>
                  <span>${row.label}</span>
                  <strong>${row.value}</strong>
                </div>
              `,
            )
            .join("")}
        </div>
      </div>
    `;
  }

  function renderOfficeView(view) {
    return `
      <div class="office-grid">
        ${view.offices
          .map(
            (office) => `
              <div class="office-card">
                <strong>${office.name}</strong>
                <span>${office.detail}</span>
              </div>
            `,
          )
          .join("")}
      </div>
    `;
  }

  function renderPendingView(view) {
    return `
      <div class="pending-list">
        ${view.documents
          .map(
            (doc) => `
              <div class="pending-card">
                <div>
                  <strong>${doc.title}</strong>
                  <span>${doc.id} - ${doc.office}</span>
                </div>
                <span class="pending-status">Pending</span>
              </div>
            `,
          )
          .join("")}
      </div>
    `;
  }

  function renderView(viewKey) {
    const view = dashboardViews[viewKey];

    previewTitle.textContent = view.title;
    previewDescription.textContent = view.description;

    if (view.rows) {
      previewContent.innerHTML = renderPieView(view);
      return;
    }

    if (view.offices) {
      previewContent.innerHTML = renderOfficeView(view);
      return;
    }

    previewContent.innerHTML = renderPendingView(view);
  }

  statCards.forEach((card) => {
    card.addEventListener("click", () => {
      statCards.forEach((item) => item.classList.remove("active"));
      card.classList.add("active");
      renderView(card.dataset.view);
    });
  });

  if (themeToggle) {
    themeToggle.addEventListener("click", () => {
      document.body.classList.toggle("dark-mode");
      const icon = themeToggle.querySelector("i");

      if (document.body.classList.contains("dark-mode")) {
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
        window.location.href = "login.html";
      }
    });
  }

  renderView("documents");
});
