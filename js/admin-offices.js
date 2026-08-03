"use strict";

document.addEventListener("DOMContentLoaded", () => {

    const themeToggle = document.getElementById("themeToggle");
    const logoutButton = document.querySelector(".logout-btn");

    /*
    |--------------------------------------------------------------------------
    | DATATABLE INITIALIZATION
    |--------------------------------------------------------------------------
    */

    if ($.fn.DataTable.isDataTable("#officesTable")) {
        $("#officesTable").DataTable().destroy();
    }

    $("#officesTable").DataTable({
        pageLength: 10,
        order: [[1, "asc"]],
        language: {
            search: "Filter Offices:",
            lengthMenu: "Display _MENU_ offices per page"
        }
    });

    /*
    |--------------------------------------------------------------------------
    | MODAL & FORM ELEMENTS
    |--------------------------------------------------------------------------
    */

    const officeModal = document.getElementById("officeModal");
    const officeForm = document.getElementById("officeForm");

    const reassignModal = document.getElementById("reassignModal");
    const reassignForm = document.getElementById("reassignForm");

    window.closeModal = function (modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove("active");
        }
    };

    window.openOfficeModal = function () {
        document.getElementById("officeModalTitle").textContent = "Add Office Department";
        officeForm.reset();
        document.getElementById("officeId").value = "";
        document.getElementById("officeIsActive").value = "1";
        officeModal.classList.add("active");
    };

    /*
    |--------------------------------------------------------------------------
    | TABLE ACTION BUTTON HANDLERS
    |--------------------------------------------------------------------------
    */

    document.querySelector("#officesTable tbody").addEventListener("click", function (e) {

        const editBtn = e.target.closest(".edit-btn");
        const reassignBtn = e.target.closest(".reassign-btn");
        const toggleBtn = e.target.closest(".toggle-btn");
        const deleteBtn = e.target.closest(".delete-btn");

        // EDIT OFFICE
        if (editBtn) {
            document.getElementById("officeModalTitle").textContent = "Edit Office Department";
            document.getElementById("officeId").value = editBtn.dataset.id;
            document.getElementById("officeName").value = editBtn.dataset.name || "";
            document.getElementById("officeCode").value = editBtn.dataset.code || "";
            document.getElementById("officeLocation").value = editBtn.dataset.location || "";
            document.getElementById("officeContactEmail").value = editBtn.dataset.email || "";
            document.getElementById("officeIsActive").value = editBtn.dataset.active || "1";
            officeModal.classList.add("active");
        }

        // REASSIGN SECRETARY
        if (reassignBtn) {
            const id = reassignBtn.dataset.id;
            const name = reassignBtn.dataset.name;
            const secId = reassignBtn.dataset.secretaryId;

            document.getElementById("reassignOfficeId").value = id;
            document.getElementById("reassignTargetOfficeName").textContent = name;
            document.getElementById("reassignSecretarySelect").value = secId || "";
            reassignModal.classList.add("active");
        }

        // TOGGLE STATUS
        if (toggleBtn) {
            const id = toggleBtn.dataset.id;
            const currentActive = parseInt(toggleBtn.dataset.active, 10);
            const newActive = currentActive === 1 ? 0 : 1;
            const actionText = newActive === 1 ? "activate" : "deactivate";

            if (confirm(`Are you sure you want to ${actionText} this office?`)) {
                const formData = new FormData();
                formData.append("action", "toggle_status");
                formData.append("id", id);
                formData.append("is_active", newActive);

                fetch("../controllers/AdminOfficesController.php", {
                    method: "POST",
                    body: formData
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert("Error: " + (data.error || "Failed to update office status"));
                        }
                    })
                    .catch(err => console.error("Error toggling office status:", err));
            }
        }

        // SAFE DELETE OFFICE
        if (deleteBtn) {
            const id = deleteBtn.dataset.id;
            const name = deleteBtn.dataset.name;

            const formData = new FormData();
            formData.append("action", "check_delete");
            formData.append("id", id);

            fetch("../controllers/AdminOfficesController.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        const deps = data.dependencies;

                        if (deps.user_count > 0 || deps.doc_count > 0) {
                            alert(`Cannot delete office "${name}".\n\nIt currently has ${deps.user_count} active user(s) and ${deps.doc_count} active document(s) assigned.\n\nPlease reassign these items or Deactivate the office instead.`);
                            return;
                        }

                        if (confirm(`Are you sure you want to permanently delete the office "${name}"?`)) {
                            const delData = new FormData();
                            delData.append("action", "delete");
                            delData.append("id", id);

                            fetch("../controllers/AdminOfficesController.php", {
                                method: "POST",
                                body: delData
                            })
                                .then(res => res.json())
                                .then(delRes => {
                                    if (delRes.success) {
                                        location.reload();
                                    } else {
                                        alert("Error: " + (delRes.error || "Failed to delete office"));
                                    }
                                })
                                .catch(err => console.error("Error deleting office:", err));
                        }
                    } else {
                        alert("Error checking office dependencies.");
                    }
                })
                .catch(err => console.error("Error checking dependencies:", err));
        }
    });

    /*
    |--------------------------------------------------------------------------
    | FORM SUBMISSION HANDLERS
    |--------------------------------------------------------------------------
    */

    // SAVE OFFICE FORM
    officeForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const id = document.getElementById("officeId").value;
        const name = document.getElementById("officeName").value.trim();
        const code = document.getElementById("officeCode").value.trim();
        const locationVal = document.getElementById("officeLocation").value.trim();
        const email = document.getElementById("officeContactEmail").value.trim();
        const isActive = document.getElementById("officeIsActive").value;

        const formData = new FormData();
        formData.append("action", id ? "update" : "create");

        if (id) {
            formData.append("id", id);
        }

        formData.append("name", name);
        formData.append("code", code);
        formData.append("location", locationVal);
        formData.append("contact_email", email);
        formData.append("is_active", isActive);

        fetch("../controllers/AdminOfficesController.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + (data.error || "Failed to save office department"));
                }
            })
            .catch(err => console.error("Error saving office:", err));
    });

    // REASSIGN SECRETARY FORM
    reassignForm.addEventListener("submit", (e) => {
        e.preventDefault();

        const officeId = document.getElementById("reassignOfficeId").value;
        const secretaryUserId = document.getElementById("reassignSecretarySelect").value;

        const formData = new FormData();
        formData.append("action", "assign_secretary");
        formData.append("office_id", officeId);
        formData.append("secretary_user_id", secretaryUserId);

        fetch("../controllers/AdminOfficesController.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + (data.error || "Failed to reassign secretary"));
                }
            })
            .catch(err => console.error("Error reassigning secretary:", err));
    });

    /*
    |--------------------------------------------------------------------------
    | THEME & LOGOUT HANDLERS
    |--------------------------------------------------------------------------
    */

    if (localStorage.getItem("docuflow-theme") === "dark") {
        document.body.classList.add("dark-mode");
    }

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

    if (logoutButton) {
        logoutButton.addEventListener("click", () => {
            window.location.href = "../controllers/LogoutController.php";
        });
    }
});
