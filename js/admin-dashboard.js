document.addEventListener("DOMContentLoaded", () => {
  const statCards = document.querySelectorAll(".stat-card");
  const actionCards = document.querySelectorAll(".action-card");
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

  const adminLists = {
    documentTypes: [
      {
        name: "Memorandum",
        subtypes: [
          "Office Memorandum",
          "Inter-Office Memorandum",
          "Administrative Memorandum",
          "Policy Memorandum",
        ],
      },
      { name: "Budget Proposal", subtypes: [] },
      { name: "Leave/Travel", subtypes: [] },
      { name: "Contracts", subtypes: [] },
    ],
    users: [
      {
        name: "Maria Santos",
        email: "maria.santos@office.gov",
        office: "Records Office",
      },
      {
        name: "Juan Dela Cruz",
        email: "juan.delacruz@office.gov",
        office: "Finance",
      },
      {
        name: "Ana Reyes",
        email: "ana.reyes@office.gov",
        office: "Legal",
      },
    ],
    offices: ["Finance", "Human Resources", "Administration", "Legal"],
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

    actionCards.forEach((card) => card.classList.remove("active"));
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

  function renderDocumentTypes() {
    previewTitle.textContent = "Document Types";
    previewDescription.textContent = "Frontend-only list of allowed document categories.";
    previewContent.innerHTML = `
      <div class="admin-management-layout">
        <form class="admin-form" id="document-type-form">
          <label class="admin-field">
            <span>New Document Type</span>
            <input id="document-type-input" type="text" placeholder="e.g. Board Resolution" />
          </label>
          <button class="admin-submit" type="submit">Add Document Type</button>
        </form>
        <div class="management-list">
          ${adminLists.documentTypes
            .map((type) =>
              type.subtypes.length
                ? `
                  <details class="management-row document-type-dropdown">
                    <summary>
                      <span>${type.name}</span>
                      <strong>${type.subtypes.length} subtypes</strong>
                    </summary>
                    <div class="subtype-list">
                      ${type.subtypes
                        .map((subtype) => `<span>${subtype}</span>`)
                        .join("")}
                    </div>
                  </details>
                `
                : `
                  <div class="management-row">
                    <span>${type.name}</span>
                    <strong>Active</strong>
                  </div>
                `,
            )
            .join("")}
        </div>
      </div>
    `;

    document
      .getElementById("document-type-form")
      .addEventListener("submit", (e) => {
        e.preventDefault();
        const input = document.getElementById("document-type-input");
        const value = input.value.trim();

        if (value) {
          adminLists.documentTypes.push({ name: value, subtypes: [] });
          renderDocumentTypes();
        }
      });
  }

  function renderUsers() {
    previewTitle.textContent = "Manage Users";
    previewDescription.textContent = "Add secretary users for offices.";
    previewContent.innerHTML = `
      <div class="admin-management-layout">
        <form class="admin-form" id="user-form">
          <label class="admin-field">
            <span>Secretary Name</span>
            <input id="user-name-input" type="text" placeholder="Full name" />
          </label>
          <label class="admin-field">
            <span>Email</span>
            <input id="user-email-input" type="email" placeholder="name@office.gov" />
          </label>
          <label class="admin-field">
            <span>Office</span>
            <select id="user-office-input">
              ${adminLists.offices
                .map((office) => `<option>${office}</option>`)
                .join("")}
            </select>
          </label>
          <button class="admin-submit" type="submit">Add Secretary</button>
        </form>
        <div class="management-list">
          ${adminLists.users
            .map(
              (user) => `
                <div class="management-row">
                  <span>
                    ${user.name}
                    <small>${user.email} - ${user.office}</small>
                  </span>
                  <strong>Secretary</strong>
                </div>
              `,
            )
            .join("")}
        </div>
      </div>
    `;

    document.getElementById("user-form").addEventListener("submit", (e) => {
      e.preventDefault();
      const name = document.getElementById("user-name-input").value.trim();
      const email = document.getElementById("user-email-input").value.trim();
      const office = document.getElementById("user-office-input").value;

      if (name && email) {
        adminLists.users.push({ name, email, office });
        renderUsers();
      }
    });
  }

  function renderOfficeManager() {
    previewTitle.textContent = "Manage Offices";
    previewDescription.textContent = "Add or remove offices from the frontend list.";
    previewContent.innerHTML = `
      <div class="admin-management-layout">
        <form class="admin-form" id="office-form">
          <label class="admin-field">
            <span>New Office</span>
            <input id="office-input" type="text" placeholder="e.g. Procurement" />
          </label>
          <button class="admin-submit" type="submit">Add Office</button>
        </form>
        <div class="management-list">
          ${adminLists.offices
            .map(
              (office, index) => `
                <div class="management-row">
                  <span>${office}</span>
                  <button class="admin-remove" type="button" data-office-index="${index}">
                    Remove
                  </button>
                </div>
              `,
            )
            .join("")}
        </div>
      </div>
    `;

    document.getElementById("office-form").addEventListener("submit", (e) => {
      e.preventDefault();
      const input = document.getElementById("office-input");
      const value = input.value.trim();

      if (value) {
        adminLists.offices.push(value);
        renderOfficeManager();
      }
    });

    document.querySelectorAll("[data-office-index]").forEach((button) => {
      button.addEventListener("click", () => {
        adminLists.offices.splice(Number(button.dataset.officeIndex), 1);
        renderOfficeManager();
      });
    });
  }

  statCards.forEach((card) => {
    card.addEventListener("click", () => {
      statCards.forEach((item) => item.classList.remove("active"));
      card.classList.add("active");
      renderView(card.dataset.view);
    });
  });

  actionCards.forEach((card) => {
    card.addEventListener("click", () => {
      statCards.forEach((item) => item.classList.remove("active"));
      actionCards.forEach((item) => item.classList.remove("active"));
      card.classList.add("active");

      if (card.dataset.action === "documentTypes") {
        renderDocumentTypes();
      } else if (card.dataset.action === "users") {
        renderUsers();
      } else {
        renderOfficeManager();
      }
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
