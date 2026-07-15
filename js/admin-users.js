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

  let users = [
    { id: 1, name: "Maria Santos", email: "maria.santos@office.gov", role: "Secretary", office: "Records Office", status: "Active" },
    { id: 2, name: "Juan Dela Cruz", email: "juan.delacruz@office.gov", role: "Admin", office: "", status: "Active" },
    { id: 3, name: "Ana Reyes", email: "ana.reyes@office.gov", role: "Member", office: "", status: "Inactive" },
  ];

  // Modal references
  const userModal = document.getElementById("userModal");
  const userForm = document.getElementById("userForm");
  const userRoleSelect = document.getElementById("userRole");
  const officeGroup = document.getElementById("officeGroup");

  // Show/hide office dropdown based on role
  userRoleSelect.addEventListener("change", (e) => {
    officeGroup.style.display = e.target.value === "Secretary" ? "grid" : "none";
  });

  // Populate office dropdown options
  function populateOfficeDropdown(selectedValue) {
    const select = document.getElementById("userOffice");
    select.innerHTML = offices.map(o => `<option value="${o.name}">${o.name}</option>`).join("");
    if (selectedValue) select.value = selectedValue;
  }

  // Render table
  function renderUsers() {
    const tbody = document.querySelector("#usersTable tbody");
    tbody.innerHTML = users.map(u => `
      <tr>
        <td>${u.name}</td>
        <td>${u.email}</td>
        <td>${u.role}</td>
        <td>${u.role === 'Secretary' ? u.office : '-'}</td>
        <td>${u.status}</td>
        <td>
          <button class="btn-small btn-edit" onclick="window.editUser(${u.id})">Edit</button>
          <button class="btn-small btn-delete" onclick="window.deleteUser(${u.id})">Delete</button>
        </td>
      </tr>
    `).join("");

    // Destroy old DataTable if it exists, then re-init
    if ($.fn.DataTable.isDataTable('#usersTable')) {
      $('#usersTable').DataTable().destroy();
    }
    $('#usersTable').DataTable();
  }

  // Modal helpers
  window.closeModal = function(modalId) {
    document.getElementById(modalId).classList.remove('active');
  };

  window.openUserModal = function() {
    document.getElementById("userModalTitle").textContent = "Add User";
    userForm.reset();
    document.getElementById("userId").value = "";
    officeGroup.style.display = "none";
    populateOfficeDropdown();
    userModal.classList.add('active');
  };

  window.editUser = function(id) {
    const user = users.find(u => u.id === id);
    if (!user) return;
    document.getElementById("userModalTitle").textContent = "Edit User";
    document.getElementById("userId").value = user.id;
    document.getElementById("userName").value = user.name;
    document.getElementById("userEmail").value = user.email;
    document.getElementById("userRole").value = user.role;
    document.getElementById("userStatus").value = user.status;
    populateOfficeDropdown(user.office);
    officeGroup.style.display = user.role === "Secretary" ? "grid" : "none";
    userModal.classList.add('active');
  };

  window.deleteUser = function(id) {
    if (confirm("Are you sure you want to delete this user?")) {
      users = users.filter(u => u.id !== id);
      renderUsers();
    }
  };

  // Form submission with email validation
  userForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("userId").value;
    const name = document.getElementById("userName").value.trim();
    const email = document.getElementById("userEmail").value.trim();
    const role = document.getElementById("userRole").value;
    const office = role === "Secretary" ? document.getElementById("userOffice").value : "";
    const status = document.getElementById("userStatus").value;

    // Simple email validation per professor's requirement
    if (!(email.includes('@') && email.includes('.com'))) {
      alert("Please enter a valid email containing '@' and '.com'.");
      return;
    }

    if (id) {
      const index = users.findIndex(u => u.id == id);
      if (index !== -1) users[index] = { id: Number(id), name, email, role, office, status };
    } else {
      const newId = users.length ? Math.max(...users.map(u => u.id)) + 1 : 1;
      users.push({ id: newId, name, email, role, office, status });
    }
    closeModal('userModal');
    renderUsers();
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

  // Initial render
  renderUsers();
});
