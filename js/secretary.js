document.addEventListener("DOMContentLoaded", () => {
  //  DOM ELEMENTS
  const btnCreate = document.getElementById("show-create");
  const btnReceive = document.getElementById("show-receive");
  const btnRelease = document.getElementById("show-release");
  const panelCreate = document.getElementById("create-panel");
  const panelReceive = document.getElementById("receive-panel");
  const panelRelease = document.getElementById("release-panel");
  const uploadArea = document.getElementById("upload-area");
  const btnDashboard = document.getElementById("view-dashboard");
  const btnToggleTheme = document.querySelector(".toggle-theme");
  const btnLogout = document.querySelector(".logout-btn");
  const btnRouteDocument = document.querySelector(".btn-route-document");

  // HELPERS
  function hideAllPanels() {
    panelCreate.style.display = "none";
    panelReceive.style.display = "none";
    panelRelease.style.display = "none";
  }

  function removeActiveFromActionButtons() {
    btnCreate.classList.remove("active");
    btnReceive.classList.remove("active");
    btnRelease.classList.remove("active");
  }

  // PANEL TOGGLE
  function showPanel(panelToShow, activeBtn) {
    hideAllPanels();
    removeActiveFromActionButtons();
    panelToShow.style.display = "block";
    activeBtn.classList.add("active");
  }

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

  if (btnRelease) {
    btnRelease.addEventListener("click", () => {
      showPanel(panelRelease, btnRelease);
    });
  }

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

  // VIEW DASHBOARD
  if (btnDashboard) {
    btnDashboard.addEventListener("click", () => {
      // Placeholder: navigate to dashboard
      alert("Dashboard view will be available soon.");
    });
  }

  // LOGOUT
  if (btnLogout) {
    btnLogout.addEventListener("click", () => {
      const confirmed = confirm("Are you sure you want to logout?");
      if (confirmed) {
        alert("Logged out successfully.");
        window.location.href = "login.html";
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
