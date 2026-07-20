document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Initialize DataTable
  if ($.fn.DataTable.isDataTable('#usersTable')) {
    $('#usersTable').DataTable().destroy();
  }
  $('#usersTable').DataTable();

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

  function setOfficeDropdown(selectedValue) {
    const select = document.getElementById("userOffice");
    if (selectedValue) select.value = selectedValue;
  }

  // Modal helpers
  window.closeModal = function (modalId) {
    document.getElementById(modalId).classList.remove('active');
  };

  window.openUserModal = function () {
    document.getElementById("userModalTitle").textContent = "Add User";
    userForm.reset();
    document.getElementById("userId").value = "";
    officeGroup.style.display = "none";
    setOfficeDropdown("");
    userModal.classList.add('active');
  };

  // Attach event listeners to dynamically edit/delete buttons
  document.querySelector('#usersTable tbody').addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-btn');
    const deleteBtn = e.target.closest('.delete-btn');

    if (editBtn) {
      document.getElementById("userModalTitle").textContent = "Edit User";
      document.getElementById("userId").value = editBtn.dataset.id;
      document.getElementById("userName").value = editBtn.dataset.name;
      document.getElementById("userEmail").value = editBtn.dataset.email;
      document.getElementById("userPassword").value = "";

      document.getElementById("userRole").value = editBtn.dataset.role;
      document.getElementById("userStatus").value = editBtn.dataset.status;

      const roleText = document.getElementById("userRole").options[document.getElementById("userRole").selectedIndex].text;
      if (editBtn.dataset.office) {
        setOfficeDropdown(editBtn.dataset.office);
      } else {
        setOfficeDropdown("");
      }

      officeGroup.style.display = roleText === "Secretary" ? "grid" : "none";
      userModal.classList.add('active');
    }

    if (deleteBtn) {
      const id = deleteBtn.dataset.id;
      if (confirm("Are you sure you want to delete this user?")) {
        const formData = new FormData();
        formData.append("action", "delete");
        formData.append("id", id);

        fetch("../controllers/AdminUsersController.php", {
          method: "POST",
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert("Error: " + (data.error || "Failed to delete user"));
            }
          })
          .catch(err => console.error("Error deleting user:", err));
      }
    }
  });

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

    fetch("../controllers/AdminUsersController.php", {
      method: "POST",
      body: formData
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          location.reload(); // Reload to reflect changes
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
});
