document.addEventListener("DOMContentLoaded", () => {
  //  DOM ELEMENTS
  const btnCreate = document.getElementById("show-create");
  const btnReceive = document.getElementById("show-receive");
  const btnPending = document.getElementById("show-pending");
  const statCards = document.querySelectorAll(".stat-card");
  const statCreated = document.getElementById("stat-created");
  const statReceived = document.getElementById("stat-received");
  const statReleased = document.getElementById("stat-released");
  const panelCreate = document.getElementById("create-panel");
  const panelReceive = document.getElementById("receive-panel");
  const panelPending = document.getElementById("pending-panel");
  const panelReport = document.getElementById("report-panel");
  const actionContent = document.getElementById("action-content");
  const reportTitle = document.getElementById("report-title");
  const reportDescription = document.getElementById("report-description");
  const diagramPreview = document.getElementById("diagram-preview");
  const uploadArea = document.getElementById("upload-area");
  const btnDashboard = document.getElementById("view-dashboard");
  const btnToggleTheme = document.querySelector(".toggle-theme");
  const btnLogout = document.querySelector(".logout-btn");
  const btnRouteDocument = document.querySelector(".btn-route-document");
  const receiveDocumentCards = document.querySelectorAll(
    "#receive-panel .document-card",
  );
  const documentFileCards = document.querySelectorAll(
    ".document-card[data-document-file]",
  );

  // HELPERS
  function hideAllPanels() {
    panelCreate.style.display = "none";
    panelReceive.style.display = "none";
    panelPending.style.display = "none";
    panelReport.style.display = "none";
  }

  function removeActiveFromActionButtons() {
    btnCreate.classList.remove("active");
    btnReceive.classList.remove("active");
    btnPending.classList.remove("active");
  }

  function removeActiveFromStats() {
    statCards.forEach((card) => card.classList.remove("active"));
  }

  // PANEL TOGGLE
  function showPanel(panelToShow, activeBtn) {
    hideAllPanels();
    removeActiveFromActionButtons();
    removeActiveFromStats();
    actionContent.style.display = "block";
    panelToShow.style.display = "block";
    activeBtn.classList.add("active");
  }

  function showDiagram(config, activeCard) {
    hideAllPanels();
    removeActiveFromActionButtons();
    removeActiveFromStats();
    actionContent.style.display = "none";
    panelReport.style.display = "block";
    activeCard.classList.add("active");
    reportTitle.textContent = config.title;
    reportDescription.textContent = config.description;

    diagramPreview.innerHTML = `
      <div class="pie-preview-card">
        <div
          class="pie-chart"
          style="background: conic-gradient(${config.gradient})"
          aria-label="${config.title}"
        >
          <span>${config.total}</span>
        </div>
        <div class="pie-details">
          ${config.items
            .map(
              (item) => `
                <div class="pie-row">
                  <span class="pie-swatch" style="background: ${item.color}"></span>
                  <span>${item.label}</span>
                  <strong>${item.value}</strong>
                </div>
              `,
            )
            .join("")}
        </div>
      </div>
    `;
  }

  const diagramData = {
    created: {
      title: "Documents routed today",
      description: "Highest today: Finance",
      total: "23",
      gradient:
        "#4c1d95 0 39%, #2563eb 39% 65%, #059669 65% 83%, #f59e0b 83% 100%",
      items: [
        { label: "Finance", value: "9", color: "#4c1d95" },
        { label: "HR", value: "6", color: "#2563eb" },
        { label: "Admin", value: "4", color: "#059669" },
        { label: "Legal", value: "4", color: "#f59e0b" },
      ],
    },
    received: {
      title: "Received Today",
      description: "Highest today: Finance",
      total: "42",
      gradient:
        "#4c1d95 0 33%, #0f766e 33% 57%, #dc2626 57% 76%, #64748b 76% 100%",
      items: [
        { label: "Finance", value: "14", color: "#4c1d95" },
        { label: "Records Office", value: "10", color: "#0f766e" },
        { label: "HR", value: "8", color: "#dc2626" },
        { label: "Admin", value: "10", color: "#64748b" },
      ],
    },
    released: {
      title: "Returned Today",
      description: "Highest today: HR",
      total: "18",
      gradient:
        "#4c1d95 0 44%, #2563eb 44% 67%, #059669 67% 83%, #f59e0b 83% 100%",
      items: [
        { label: "HR", value: "8", color: "#4c1d95" },
        { label: "Admin", value: "4", color: "#2563eb" },
        { label: "Finance", value: "3", color: "#059669" },
        { label: "Legal", value: "3", color: "#f59e0b" },
      ],
    },
  };

  if (btnCreate) {
    btnCreate.addEventListener("click", () => {
      showPanel(panelCreate, btnCreate);
    });
  }

  if (btnReceive) {
    btnReceive.addEventListener("click", () => {
      showPanel(panelReceive, btnReceive);
    });
  }

  if (btnPending) {
    btnPending.addEventListener("click", () => {
      showPanel(panelPending, btnPending);
    });
  }

  if (statCreated) {
    statCreated.addEventListener("click", () => {
      showDiagram(diagramData.created, statCreated);
    });
  }

  if (statReceived) {
    statReceived.addEventListener("click", () => {
      showDiagram(diagramData.received, statReceived);
    });
  }

  if (statReleased) {
    statReleased.addEventListener("click", () => {
      showDiagram(diagramData.released, statReleased);
    });
  }

  documentFileCards.forEach((card) => {
    const documentIcon = card.querySelector(".doc-icon");

    if (documentIcon) {
      documentIcon.addEventListener("click", (e) => {
        e.stopPropagation();
        window.open(card.dataset.documentFile, "_blank");
      });
    }
  });

  receiveDocumentCards.forEach((card) => {
    card.addEventListener("click", () => {
      receiveDocumentCards.forEach((otherCard) => {
        if (otherCard !== card) {
          otherCard.classList.remove("expanded");
        }
      });
      card.classList.toggle("expanded");
    });

    const sendButton = card.querySelector(".btn-send-document");
    const recipientInput = card.querySelector(".recipient-input");

    if (sendButton && recipientInput) {
      sendButton.addEventListener("click", (e) => {
        e.stopPropagation();
        const recipient = recipientInput.value.trim();

        if (!recipient) {
          recipientInput.focus();
          return;
        }

        const status = card.querySelector(".doc-status");
        const office = card.querySelector(".doc-office");

        status.textContent = "Sent";
        status.classList.remove("status-pending");
        status.classList.add("status-sent");
        office.textContent = recipient;
        sendButton.textContent = "Sent";
        sendButton.disabled = true;
        recipientInput.disabled = true;
        card.classList.remove("expanded");
      });

      recipientInput.addEventListener("click", (e) => {
        e.stopPropagation();
      });
    }
  });

  // UPLOAD AREA
  if (uploadArea) {
    // Create hidden file input
    const fileInput = document.createElement("input");
    fileInput.type = "file";
    fileInput.accept = ".pdf";
    fileInput.style.display = "none";
    document.body.appendChild(fileInput);

    uploadArea.addEventListener("click", () => {
      fileInput.click();
    });

    fileInput.addEventListener("change", () => {
      if (fileInput.files.length > 0) {
        // For now just show an alert
        alert(`File selected: ${fileInput.files[0].name}`);
      }
    });

    // Drag & drop
    uploadArea.addEventListener("dragover", (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = "var(--primary)";
      uploadArea.style.borderStyle = "dashed";
    });

    uploadArea.addEventListener("dragleave", () => {
      uploadArea.style.borderColor = "";
      uploadArea.style.borderStyle = "";
    });

    uploadArea.addEventListener("drop", (e) => {
      e.preventDefault();
      uploadArea.style.borderColor = "";
      uploadArea.style.borderStyle = "";
      if (e.dataTransfer.files.length > 0) {
        // Assign files to hidden input
        alert(`File dropped: ${e.dataTransfer.files[0].name}`);
      }
    });
  }

  // DARK / LIGHT MODE TOGGLE
  if (btnToggleTheme) {
    btnToggleTheme.addEventListener("click", () => {
      document.body.classList.toggle("dark-mode");
      // Toggle icon between moon and sun
      const icon = btnToggleTheme.querySelector("i");
      if (document.body.classList.contains("dark-mode")) {
        icon.classList.remove("fa-moon");
        icon.classList.add("fa-sun");
      } else {
        icon.classList.remove("fa-sun");
        icon.classList.add("fa-moon");
      }
    });
  }

  // VIEW DASHBOARD opens an external reporting site
  if (btnDashboard) {
    btnDashboard.addEventListener("click", () => {
      hideAllPanels();
      actionContent.style.display = "none";
      btnCreate.classList.remove("active");
      btnReceive.classList.remove("active");
      btnPending.classList.remove("active");
      removeActiveFromStats();
    });
  }

  // LOGOUT
  if (btnLogout) {
    btnLogout.addEventListener("click", () => {
      const confirmed = confirm("Are you sure you want to logout?");
      if (confirmed) {
        alert("Logged out successfully.");
        window.location.href = "login.php";
      }
    });
  }

  // CREATE & ROUTE DOCUMENT
  if (btnRouteDocument) {
    btnRouteDocument.addEventListener("click", (e) => {
      e.preventDefault();
      alert("Document created and routing initiated.");
    });
  }
});
