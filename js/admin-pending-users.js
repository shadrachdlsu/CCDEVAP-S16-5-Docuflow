document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Initialize DataTable
  if ($.fn.DataTable.isDataTable('#pendingUsersTable')) {
    $('#pendingUsersTable').DataTable().destroy();
  }
  $('#pendingUsersTable').DataTable();

  // Attach event listeners to dynamically approve buttons
  document.querySelector('#pendingUsersTable tbody').addEventListener('click', function (e) {
    const approveBtn = e.target.closest('.approve-btn');

    if (approveBtn) {
      const id = approveBtn.dataset.id;
      if (confirm("Are you sure you want to approve and activate this user?")) {
        const formData = new FormData();
        formData.append("action", "approve");
        formData.append("id", id);

        fetch("../controllers/AdminPendingUsersController.php", {
          method: "POST",
          body: formData
        })
          .then(res => res.json())
          .then(data => {
            if (data.success) {
              location.reload();
            } else {
              alert("Error: " + (data.error || "Failed to approve user"));
            }
          })
          .catch(err => console.error("Error approving user:", err));
      }
    }
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
