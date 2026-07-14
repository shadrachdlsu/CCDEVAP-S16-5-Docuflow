document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Hardcoded offices array
  let offices = [
    { id: 1, name: "Finance" },
    { id: 2, name: "Human Resources" },
    { id: 3, name: "Administration" },
    { id: 4, name: "Legal" }
  ];

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
      offices = offices.filter(o => o.id !== id);
      renderOffices();
    }
  };

  officeForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("officeId").value;
    const name = document.getElementById("officeName").value.trim();

    if (id) {
      const index = offices.findIndex(o => o.id == id);
      if (index !== -1) offices[index].name = name;
    } else {
      const newId = offices.length ? Math.max(...offices.map(o => o.id)) + 1 : 1;
      offices.push({ id: newId, name });
    }
    closeModal('officeModal');
    renderOffices();
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

  renderOffices();
});
