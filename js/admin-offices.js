document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  let offices = [];

  function loadOffices() {
    fetch("../controllers/admin_api_offices.php?action=list")
      .then(res => res.json())
      .then(data => {
        offices = data;
        renderOffices();
      })
      .catch(err => console.error("Error loading offices:", err));
  }

  const officeModal = document.getElementById("officeModal");
  const officeForm = document.getElementById("officeForm");

  function renderOffices() {
    const tbody = document.querySelector("#officesTable tbody");
    tbody.innerHTML = offices.map(o => `
      <tr>
        <td>${o.name}</td>
        <td>
          <button class="btn-small btn-edit" onclick="window.editOffice(${o.id})">Edit</button>
          <button class="btn-small btn-delete" onclick="window.deleteOffice(${o.id})">Delete</button>
        </td>
      </tr>
    `).join("");

    if ($.fn.DataTable.isDataTable('#officesTable')) {
      $('#officesTable').DataTable().destroy();
    }
    $('#officesTable').DataTable();
  }

  window.closeModal = function(modalId) {
    document.getElementById(modalId).classList.remove('active');
  };

  window.openOfficeModal = function() {
    document.getElementById("officeModalTitle").textContent = "Add Office";
    officeForm.reset();
    document.getElementById("officeId").value = "";
    officeModal.classList.add('active');
  };

  window.editOffice = function(id) {
    const office = offices.find(o => o.id === id);
    if (!office) return;
    document.getElementById("officeModalTitle").textContent = "Edit Office";
    document.getElementById("officeId").value = office.id;
    document.getElementById("officeName").value = office.name;
    officeModal.classList.add('active');
  };

  window.deleteOffice = function(id) {
    if (confirm("Are you sure you want to delete this office?")) {
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("id", id);

      fetch("../controllers/admin_api_offices.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          loadOffices();
        } else {
          alert("Error: " + (data.error || "Failed to delete office"));
        }
      })
      .catch(err => console.error("Error deleting office:", err));
    }
  };

  officeForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("officeId").value;
    const name = document.getElementById("officeName").value.trim();

    const formData = new FormData();
    formData.append("action", id ? "update" : "create");
    if (id) formData.append("id", id);
    formData.append("name", name);

    fetch("../controllers/admin_api_offices.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        closeModal('officeModal');
        loadOffices();
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
      if (confirm("Are you sure you want to logout?")) {
        window.location.href = "login.php";
      }
    });
  }

  loadOffices();
});
