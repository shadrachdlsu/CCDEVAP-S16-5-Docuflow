document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Initialize DataTable
  if ($.fn.DataTable.isDataTable('#officesTable')) {
    $('#officesTable').DataTable().destroy();
  }
  $('#officesTable').DataTable();

  const officeModal = document.getElementById("officeModal");
  const officeForm = document.getElementById("officeForm");

  window.closeModal = function (modalId) {
    document.getElementById(modalId).classList.remove('active');
  };

  window.openOfficeModal = function () {
    document.getElementById("officeModalTitle").textContent = "Add Office";
    officeForm.reset();
    document.getElementById("officeId").value = "";
    officeModal.classList.add('active');
  };

  // Edit and delete buttons
  document.querySelector('#officesTable tbody').addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-btn');
    const deleteBtn = e.target.closest('.delete-btn');

    if (editBtn) {
      document.getElementById("officeModalTitle").textContent = "Edit Office";
      document.getElementById("officeId").value = editBtn.dataset.id;
      document.getElementById("officeName").value = editBtn.dataset.name;
      officeModal.classList.add('active');
    }

    if (deleteBtn) {
      const id = deleteBtn.dataset.id;
      if (confirm("Are you sure you want to delete this office?")) {
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);

        fetch("../controllers/AdminOfficesController.php", {
          method: "POST",
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert("Error: " + (data.error || "Failed to delete office"));
            }
          })
          .catch(err => console.error("Error deleting office:", err));
      }
    }
  });

  officeForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("officeId").value;
    const name = document.getElementById("officeName").value.trim();

    const formData = new FormData();
    formData.append("action", id ? "update" : "create");
    if (id) formData.append("id", id);
    formData.append("name", name);

    fetch("../controllers/AdminOfficesController.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload();
        } else {
          alert("Error: " + (data.error || "Failed to save office"));
        }
      })
      .catch(err => console.error("Error saving office:", err));
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
