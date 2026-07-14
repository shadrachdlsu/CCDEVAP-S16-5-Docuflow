document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Hardcoded data arrays
  let offices = [
    { id: 1, name: "Finance" },
    { id: 2, name: "Human Resources" },
    { id: 3, name: "Administration" },
    { id: 4, name: "Legal" }
  ];

  let documentTypes = [
    { id: 1, name: "Memorandum", offices: ["Human Resources"] },
    { id: 2, name: "Budget Proposal", offices: ["Finance"] },
    { id: 3, name: "Leave/Travel", offices: ["Administration"] },
    { id: 4, name: "Contracts", offices: ["Legal"] },
  ];

  const docTypeModal = document.getElementById("docTypeModal");
  const docTypeForm = document.getElementById("docTypeForm");

  function populateOfficeDropdown(selectedValues) {
    const select = document.getElementById("docTypeOffices");
    select.innerHTML = offices.map(o => `<option value="${o.name}">${o.name}</option>`).join("");
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
      documentTypes = documentTypes.filter(d => d.id !== id);
      renderDocumentTypes();
    }
  };

  docTypeForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("docTypeId").value;
    const name = document.getElementById("docTypeName").value.trim();
    const select = document.getElementById("docTypeOffices");
    const selectedOffices = Array.from(select.selectedOptions).map(opt => opt.value);

    if (id) {
      const index = documentTypes.findIndex(d => d.id == id);
      if (index !== -1) {
        documentTypes[index].name = name;
        documentTypes[index].offices = selectedOffices;
      }
    } else {
      const newId = documentTypes.length ? Math.max(...documentTypes.map(d => d.id)) + 1 : 1;
      documentTypes.push({ id: newId, name, offices: selectedOffices });
    }
    closeModal('docTypeModal');
    renderDocumentTypes();
  });

  // Theme toggle
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

  // Logout
  if (logoutButton) {
    logoutButton.addEventListener("click", () => {
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "login.html";
      }
    });
  }

  renderDocumentTypes();
});
