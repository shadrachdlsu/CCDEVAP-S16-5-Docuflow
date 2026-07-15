document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  let offices = [];
  let users = [];
  let roles = [];

  function loadRoles() {
    fetch("../controllers/admin_api_users.php?action=get_roles")
      .then(res => res.json())
      .then(data => {
        roles = data;
        const select = document.getElementById("userRole");
        select.innerHTML = roles.map(r => `<option value="${r.role_id}">${r.role_name}</option>`).join("");
      })
      .catch(err => console.error("Error loading roles:", err));
  }

  function loadOffices() {
    fetch("../controllers/admin_api_users.php?action=get_offices")
      .then(res => res.json())
      .then(data => {
        offices = data;
        const select = document.getElementById("userOffice");
        select.innerHTML = offices.map(o => `<option value="${o.office_id}">${o.office_name}</option>`).join("");
      })
      .catch(err => console.error("Error loading offices:", err));
  }

  function loadUsers() {
    fetch("../controllers/admin_api_users.php?action=list")
      .then(res => res.json())
      .then(data => {
        users = data;
        renderUsers();
      })
      .catch(err => console.error("Error loading users:", err));
  }

  // Modal references
  const userModal = document.getElementById("userModal");
  const userForm = document.getElementById("userForm");
  const userRoleSelect = document.getElementById("userRole");
  const officeGroup = document.getElementById("officeGroup");

  // Show/hide office dropdown based on role
  userRoleSelect.addEventListener("change", (e) => {
    const selectedText = e.target.options[e.target.selectedIndex].text;
    officeGroup.style.display = selectedText === "Secretary" ? "grid" : "none";
  });

  // Set office dropdown value
  function setOfficeDropdown(selectedValue) {
    const select = document.getElementById("userOffice");
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
        <td>${u.role === 'Secretary' ? u.office || '-' : '-'}</td>
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
    setOfficeDropdown("");
    userModal.classList.add('active');
  };

  window.editUser = function(id) {
    const user = users.find(u => u.id === id);
    if (!user) return;
    document.getElementById("userModalTitle").textContent = "Edit User";
    document.getElementById("userId").value = user.id;
    document.getElementById("userName").value = user.name;
    document.getElementById("userEmail").value = user.email;
    document.getElementById("userPassword").value = ""; // Don't show hash
    
    // Find role in dropdown to set it
    const roleOpt = Array.from(document.getElementById("userRole").options).find(opt => opt.text === user.role);
    if (roleOpt) document.getElementById("userRole").value = roleOpt.value;
    
    document.getElementById("userStatus").value = user.status;
    
    // Find office in dropdown
    const officeOpt = Array.from(document.getElementById("userOffice").options).find(opt => opt.text === user.office);
    if (officeOpt) setOfficeDropdown(officeOpt.value);
    else setOfficeDropdown("");
    
    officeGroup.style.display = user.role === "Secretary" ? "grid" : "none";
    userModal.classList.add('active');
  };

  window.deleteUser = function(id) {
    if (confirm("Are you sure you want to delete this user?")) {
      const formData = new FormData();
      formData.append("action", "delete");
      formData.append("id", id);

      fetch("../controllers/admin_api_users.php", {
        method: "POST",
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          loadUsers();
        } else {
          alert("Error: " + (data.error || "Failed to delete user"));
        }
      })
      .catch(err => console.error("Error deleting user:", err));
    }
  };

  // Form submission with email validation
  userForm.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("userId").value;
    const name = document.getElementById("userName").value.trim();
    const email = document.getElementById("userEmail").value.trim();
    const password = document.getElementById("userPassword").value;
    const roleId = document.getElementById("userRole").value;
    const status = document.getElementById("userStatus").value;
    
    const roleSelect = document.getElementById("userRole");
    const roleText = roleSelect.options[roleSelect.selectedIndex].text;
    const officeId = roleText === "Secretary" ? document.getElementById("userOffice").value : "";

    // Simple email validation per professor's requirement
    if (!(email.includes('@') && email.includes('.com'))) {
      alert("Please enter a valid email containing '@' and '.com'.");
      return;
    }

    if (!id && !password) {
      alert("Password is required for new users.");
      return;
    }

    const formData = new FormData();
    formData.append("action", id ? "update" : "create");
    if (id) formData.append("id", id);
    formData.append("name", name);
    formData.append("email", email);
    formData.append("password", password);
    formData.append("role_id", roleId);
    formData.append("office_id", officeId);
    formData.append("status", status);

    fetch("../controllers/admin_api_users.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        closeModal('userModal');
        loadUsers();
      } else {
        alert("Error: " + (data.error || "Failed to save user"));
      }
    })
    .catch(err => console.error("Error saving user:", err));
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
  loadRoles();
  loadOffices();
  loadUsers();
});
