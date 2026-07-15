document.addEventListener("DOMContentLoaded", () => {
  const themeToggle = document.getElementById("themeToggle");
  const logoutButton = document.querySelector(".logout-btn");

  // Hardcoded documents array — statuses: Pending, Signed, Finished
  // const documents = [
  //   { id: "DOC-2024-001", title: "Budget Proposal FY 2024", type: "Budget Proposal", office: "Finance", status: "Pending" },
  //   { id: "DOC-2024-002", title: "Employee Leave Request", type: "Leave/Travel", office: "Human Resources", status: "Signed" },
  //   { id: "DOC-2024-004", title: "Procurement Review", type: "Memorandum", office: "Administration", status: "Finished" },
  //   { id: "DOC-2024-012", title: "Contract Review Packet", type: "Contracts", office: "Legal", status: "Pending" },
  // ];

  function getStatusBadge(status) {
    const s = status.toLowerCase();
    if (s === 'pending') return `<span class="status-badge status-pending">Pending</span>`;
    if (s === 'signed') return `<span class="status-badge status-signed">Signed</span>`;
    if (s === 'finished') return `<span class="status-badge status-finished">Finished</span>`;
    return status;
  }

  async function renderDocuments() {
    try {
      // Fetch the JavaScript array from the backend
      const response = await fetch('../controllers/admin_api_get_documents.php');
      const documents = await response.json();

      if (documents.error) {
        console.error("Authentication or Server Error:", documents.error);
        return;
      }

      const tbody = document.querySelector("#documentsTable tbody");
      tbody.innerHTML = documents.map(d => `
        <tr>
          <td>${d.id}</td>
          <td>${d.title}</td>
          <td>${d.type}</td>
          <td>${d.office || 'N/A'}</td>
          <td>${getStatusBadge(d.status)}</td>
        </tr>
      `).join("");

      // Initialize DataTable AFTER the data is injected
      $('#documentsTable').DataTable();

    } catch (error) {
      console.error("Failed to load documents:", error);
    }
  }

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
        window.location.href = "../views/login.php";
      }
    });
  }

  renderDocuments();
});
