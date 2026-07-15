document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  let offices = [];
  let documentTypes = [];

  function loadOffices() {
    fetch("../controllers/admin_api_document_types.php?action=get_offices")
      .then(res => res.json())
      .then(data => {
        offices = data;
        populateOfficeDropdown();
      })
      .catch(err => console.error("Error loading offices:", err));
  }

  function loadDocumentTypes() {
    fetch("../controllers/admin_api_document_types.php?action=list")
      .then(res => res.json())
      .then(data => {
        documentTypes = data;
        renderDocumentTypes();
      })
      .catch(err => console.error("Error loading doc types:", err));
  }

  const docTypeModal = document.getElementById("docTypeModal");
  const docTypeForm = document.getElementById("docTypeForm");

  function populateOfficeDropdown(selectedValues) {
    const select = document.getElementById("docTypeOffices");
    select.innerHTML = offices.map(o => `<option value="${o.office_name}">${o.office_name}</option>`).join("");
    if (selectedValues && Array.isArray(selectedValues)) {
      Array.from(select.options).forEach(opt => {
        if (selectedValues.includes(opt.value)) opt.selected = true;
      });
    }
  }

  function renderDocumentTypes() {
    const tbody = document.querySelector("#docTypesTable tbody");
    tbody.innerHTML = documentTypes.map(dt => `
      <tr>
        <td>${dt.name}</td>
        <td>${dt.offices.join(", ")}</td>
        <td>
          <button class="btn-small btn-edit" onclick="window.editDocType(${dt.id})">Edit</button>
          <button class="btn-small btn-delete" onclick="window.deleteDocType(${dt.id})">Delete</button>
        </td>
      </tr>
    `).join("");

    if ($.fn.DataTable.isDataTable('#docTypesTable')) {
      $('#docTypesTable').DataTable().destroy();
    }
    $('#docTypesTable').DataTable();
  }

  window.closeModal = function(modalId) {
    document.getElementById(modalId).classList.remove('active');
  };

  window.openDocTypeModal = function() {
    document.getElementById("docTypeModalTitle").textContent = "Add Document Type";
    docTypeForm.reset();
    document.getElementById("docTypeId").value = "";
    populateOfficeDropdown();
    docTypeModal.classList.add('active');
  };

  window.editDocType = function(id) {
    const dt = documentTypes.find(d => d.id === id);
    if (!dt) return;
    document.getElementById("docTypeModalTitle").textContent = "Edit Document Type";
    document.getElementById("docTypeId").value = dt.id;
    document.getElementById("docTypeName").value = dt.name;
    populateOfficeDropdown(dt.offices);
    docTypeModal.classList.add('active');
  };

  window.deleteDocType = function(id) {
    if (confirm("Are you sure you want to delete this document type?")) {
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("id", id);
      
      fetch("../controllers/admin_api_document_types.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) loadDocumentTypes();
        else alert("Error: " + (data.error || "Failed to delete"));
      })
      .catch(err => console.error("Error deleting:", err));
    }
  };

  docTypeForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("docTypeId").value;
    const name = document.getElementById("docTypeName").value.trim();
    const select = document.getElementById("docTypeOffices");
    const selectedOffices = Array.from(select.selectedOptions).map(opt => opt.value);

    const formData = new FormData();
    formData.append("action", id ? "update" : "create");
    if (id) formData.append("id", id);
    formData.append("name", name);
    formData.append("offices", JSON.stringify(selectedOffices));

    fetch("../controllers/admin_api_document_types.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        closeModal('docTypeModal');
        loadDocumentTypes();
      } else {
        alert("Error: " + (data.error || "Failed to save"));
      }
    })
    .catch(err => console.error("Error saving:", err));
  });

  // Load saved theme
  if (localStorage.getItem("docuflow-theme") === "dark") {
    document.body.classList.add("dark-mode");
  }

  // Theme toggle
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

  // Logout
  if (logoutButton) {
    logoutButton.addEventListener("click", () => {
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "login.php";
      }
    });
  }

  loadOffices();
  loadDocumentTypes();
});
