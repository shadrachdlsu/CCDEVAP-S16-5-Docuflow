document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Initialize DataTable
  if ($.fn.DataTable.isDataTable('#docTypesTable')) {
    $('#docTypesTable').DataTable().destroy();
  }
  $('#docTypesTable').DataTable();

  const docTypeModal = document.getElementById("docTypeModal");
  const docTypeForm = document.getElementById("docTypeForm");

  window.closeModal = function (modalId) {
    document.getElementById(modalId).classList.remove('active');
  };

  window.openDocTypeModal = function () {
    document.getElementById("docTypeModalTitle").textContent = "Add Document Type";
    docTypeForm.reset();
    document.getElementById("docTypeId").value = "";

    const select = document.getElementById("docTypeOffices");
    Array.from(select.options).forEach(opt => opt.selected = false);

    docTypeModal.classList.add('active');
  };

  // Edit and delete buttons
  document.querySelector('#docTypesTable tbody').addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-btn');
    const deleteBtn = e.target.closest('.delete-btn');

    if (editBtn) {
      document.getElementById("docTypeModalTitle").textContent = "Edit Document Type";
      document.getElementById("docTypeId").value = editBtn.dataset.id;
      document.getElementById("docTypeName").value = editBtn.dataset.name;

      const offices = JSON.parse(editBtn.dataset.offices || "[]");
      const select = document.getElementById("docTypeOffices");
      Array.from(select.options).forEach(opt => {
        opt.selected = offices.includes(opt.value);
      });

      docTypeModal.classList.add('active');
    }

    if (deleteBtn) {
      const id = deleteBtn.dataset.id;
      if (confirm("Are you sure you want to delete this document type?")) {
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);

        fetch("../controllers/AdminDocumentTypesController.php", {
          method: "POST",
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert("Error: " + (data.error || "Failed to delete document type"));
            }
          })
          .catch(err => console.error("Error deleting document type:", err));
      }
    }
  });

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

    fetch("../controllers/AdminDocumentTypesController.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert("Error: " + (data.error || "Failed to save document type"));
        }
      })
      .catch(err => console.error("Error saving document type:", err));
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
      window.location.href = "../controllers/LogoutController.php";
    });
  }
});
