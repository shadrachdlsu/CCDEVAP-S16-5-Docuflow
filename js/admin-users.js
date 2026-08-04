"use strict";

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

document.addEventListener("DOMContentLoaded", () => {

    const themeToggle = document.getElementById("themeToggle");
    const logoutButton = document.querySelector(".logout-btn");

    /*
    |--------------------------------------------------------------------------
    | DATATABLE INITIALIZATION
    |--------------------------------------------------------------------------
    */

    if ($.fn.DataTable.isDataTable("#usersTable")) {
        $("#usersTable").DataTable().destroy();
    }

    $("#usersTable").DataTable({
        order: [[5, "desc"]],
        columnDefs: [
            { orderable: false, targets: [0, 6] }
        ]
    });

    /*
    |--------------------------------------------------------------------------
    | MODAL & FORM ELEMENTS
    |--------------------------------------------------------------------------
    */

    const userModal = document.getElementById("userModal");
    const userForm = document.getElementById("userForm");
    const userRoleSelect = document.getElementById("userRole");
    const officeGroup = document.getElementById("officeGroup");

    userRoleSelect.addEventListener("change", (e) => {
        const selectedText = e.target.options[e.target.selectedIndex].text;
        officeGroup.style.display = (selectedText === "Secretary" || selectedText === "Member") ? "grid" : "none";
    });

    function setOfficeDropdown(selectedValue) {
        const select = document.getElementById("userOffice");
        if (selectedValue) {
            select.value = selectedValue;
        }
    }

    window.closeModal = function (modalId) {
        document.getElementById(modalId).classList.remove("active");
    };

    window.openUserModal = function () {
        document.getElementById("userModalTitle").textContent = "Add User";
        userForm.reset();
        document.getElementById("userId").value = "";
        officeGroup.style.display = "none";
        setOfficeDropdown("");
        userModal.classList.add("active");
    };

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW HANDOVER CHECK & TRIGGER
    |--------------------------------------------------------------------------
    */

    function triggerUserActionWithHandoverCheck(id, userName, pendingAction) {
        const checkData = new FormData();
        checkData.append("action", "check_workflows");
        checkData.append("id", id);

        fetch("../controllers/AdminUsersController.php", {
            method: "POST",
            body: checkData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.workflows && data.workflows.total > 0) {
                    document.getElementById("handoverFromUserId").value = id;
                    document.getElementById("handoverPendingAction").value = pendingAction;
                    document.getElementById("handoverUserName").textContent = userName || "User";

                    const wf = data.workflows;
                    let details = [];

                    if (wf.assignments > 0) details.push(`${wf.assignments} pending assignment(s)`);
                    if (wf.routes > 0) details.push(`${wf.routes} route step(s)`);
                    if (wf.secretary > 0) details.push("1 office secretary role");
                    if (wf.created > 0) details.push(`${wf.created} active created document(s)`);

                    document.getElementById("handoverSummaryText").textContent =
                        `${userName || "User"} currently has ${details.join(", ")}. Please select an active replacement member below to receive these active items before proceeding to ${pendingAction}.`;

                    document.querySelectorAll(".handover-opt").forEach(opt => {
                        opt.style.display = (opt.dataset.id === String(id)) ? "none" : "";
                    });

                    document.getElementById("handoverToUserId").value = "";
                    document.getElementById("handoverSubmitBtn").textContent = `Handover & ${pendingAction.charAt(0).toUpperCase() + pendingAction.slice(1)}`;
                    document.getElementById("handoverModal").classList.add("active");
                } else {
                    const actionText = pendingAction === "delete" ? "delete" : "deactivate";

                    if (confirm(`Are you sure you want to ${actionText} ${userName || "this user"}?`)) {
                        const formData = new FormData();
                        formData.append("action", pendingAction);
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
                                    alert(`Error: ${data.error || "Failed to " + actionText + " user"}`);
                                }
                            })
                            .catch(err => console.error(`Error during ${actionText}:`, err));
                    }
                }
            })
            .catch(err => console.error("Error checking workflows:", err));
    }

    /*
    |--------------------------------------------------------------------------
    | ACTION MENU & SLIDEOVER DRAWER
    |--------------------------------------------------------------------------
    */

    document.addEventListener("click", (e) => {
        const trigger = e.target.closest(".action-menu-trigger");
        const activeMenu = document.querySelector(".action-menu.active");

        if (trigger) {
            const wrapper = trigger.closest(".action-dropdown");
            const menu = wrapper ? wrapper.querySelector(".action-menu") : null;

            document.querySelectorAll(".action-menu.active").forEach(m => {
                if (m !== menu) {
                    m.classList.remove("active", "drop-up");
                    const parentDropdown = m.closest(".action-dropdown");
                    if (parentDropdown) {
                        parentDropdown.style.zIndex = "";
                    }
                }
            });

            if (menu) {
                const isOpening = !menu.classList.contains("active");

                if (isOpening) {
                    const rect = trigger.getBoundingClientRect();
                    const viewportHeight = window.innerHeight || document.documentElement.clientHeight;
                    const spaceBelow = viewportHeight - rect.bottom;
                    const spaceAbove = rect.top;
                    const estimatedMenuHeight = 210;

                    if (spaceBelow < estimatedMenuHeight && spaceAbove > spaceBelow) {
                        menu.classList.add("drop-up");
                    } else {
                        menu.classList.remove("drop-up");
                    }

                    menu.classList.add("active");
                    if (wrapper) {
                        wrapper.style.zIndex = "1000";
                    }
                } else {
                    menu.classList.remove("active", "drop-up");
                    if (wrapper) {
                        wrapper.style.zIndex = "";
                    }
                }
            }

            e.stopPropagation();
        } else if (activeMenu && !e.target.closest(".action-menu")) {
            activeMenu.classList.remove("active", "drop-up");
            const parentDropdown = activeMenu.closest(".action-dropdown");
            if (parentDropdown) {
                parentDropdown.style.zIndex = "";
            }
        }
    });

    window.closeSlideover = function (slideoverId) {
        const drawer = document.getElementById(slideoverId);
        if (drawer) {
            drawer.classList.remove("active");
        }
    };

    function openUserProfileDrawer(userId) {
        const slideover = document.getElementById("userProfileSlideover");
        const loading = document.getElementById("drawerActivityLoading");
        const content = document.getElementById("drawerActivityContent");

        if (!slideover) {
            return;
        }

        slideover.classList.add("active");
        loading.style.display = "block";
        content.style.display = "none";

        const formData = new FormData();
        formData.append("action", "get_user_activity");
        formData.append("id", userId);

        fetch("../controllers/AdminUsersController.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(res => {
                if (res.success && res.data) {
                    const p = res.data.profile;
                    const stats = res.data.stats;
                    const createdDocs = res.data.created_documents || [];
                    const assignedDocs = res.data.recent_assignments || [];

                    const initials = p.name ? p.name.split(" ").map(n => n[0]).join("").substring(0, 2).toUpperCase() : "U";
                    document.getElementById("drawerAvatar").textContent = initials;

                    document.getElementById("drawerName").textContent = p.name;
                    document.getElementById("drawerEmail").textContent = p.email;
                    document.getElementById("drawerRole").textContent = p.role;
                    document.getElementById("drawerOffice").textContent = p.office;

                    const statusBadge = document.getElementById("drawerStatus");
                    statusBadge.textContent = p.status;
                    statusBadge.className = `status-badge ${p.status === "Active" ? "status-active" : "status-inactive"}`;

                    document.getElementById("statCreated").textContent = stats.total_created;
                    document.getElementById("statPending").textContent = stats.pending_assignments;
                    document.getElementById("statRoutes").textContent = stats.routes;
                    document.getElementById("drawerRegisteredDate").textContent = p.created_at;

                    const createdUl = document.getElementById("drawerCreatedDocs");
                    if (createdDocs.length > 0) {
                        createdUl.innerHTML = createdDocs.map(doc => `
                            <li class="drawer-doc-item">
                                <div class="drawer-doc-info">
                                    <span class="drawer-doc-code">${escapeHtml(doc.tracking_code)}</span>
                                    <span class="drawer-doc-title" title="${escapeHtml(doc.title)}">${escapeHtml(doc.title)}</span>
                                </div>
                                <span class="status-badge status-active">${escapeHtml(doc.status)}</span>
                            </li>
                        `).join("");
                    } else {
                        createdUl.innerHTML = `<li style="font-size:0.82rem; color:var(--gray-500); padding:6px 0;">No documents created yet.</li>`;
                    }

                    const assignedUl = document.getElementById("drawerAssignedDocs");
                    if (assignedDocs.length > 0) {
                        assignedUl.innerHTML = assignedDocs.map(doc => `
                            <li class="drawer-doc-item">
                                <div class="drawer-doc-info">
                                    <span class="drawer-doc-code">${escapeHtml(doc.tracking_code)}</span>
                                    <span class="drawer-doc-title" title="${escapeHtml(doc.title)}">${escapeHtml(doc.title)}</span>
                                </div>
                                <span class="status-badge status-active">${escapeHtml(doc.assignment_status)}</span>
                            </li>
                        `).join("");
                    } else {
                        assignedUl.innerHTML = `<li style="font-size:0.82rem; color:var(--gray-500); padding:6px 0;">No document assignments.</li>`;
                    }

                    loading.style.display = "none";
                    content.style.display = "block";
                } else {
                    alert("Error: " + (res.error || "Failed to load profile"));
                    closeSlideover("userProfileSlideover");
                }
            })
            .catch(err => {
                console.error("Error loading user profile:", err);
                closeSlideover("userProfileSlideover");
            });
    }

    /*
    |--------------------------------------------------------------------------
    | TABLE ACTION BUTTON HANDLERS
    |--------------------------------------------------------------------------
    */

    document.querySelector("#usersTable tbody").addEventListener("click", function (e) {
        const editBtn = e.target.closest(".edit-btn");
        const deleteBtn = e.target.closest(".delete-btn");
        const approveBtn = e.target.closest(".approve-btn");
        const deactivateBtn = e.target.closest(".deactivate-btn");
        const resetPwdBtn = e.target.closest(".reset-pwd-btn");
        const viewProfileBtn = e.target.closest(".view-profile-btn");
        const userProfileLink = e.target.closest(".user-profile-link");

        if (userProfileLink) {
            e.preventDefault();
            openUserProfileDrawer(userProfileLink.dataset.id);
            return;
        }

        if (viewProfileBtn) {
            openUserProfileDrawer(viewProfileBtn.dataset.id);
        }

        if (deactivateBtn || deleteBtn || resetPwdBtn || approveBtn || editBtn || viewProfileBtn) {
            document.querySelectorAll(".action-menu.active").forEach(m => {
                m.classList.remove("active", "drop-up");
                const parentDropdown = m.closest(".action-dropdown");
                if (parentDropdown) {
                    parentDropdown.style.zIndex = "";
                }
            });
        }

        if (deactivateBtn) {
            triggerUserActionWithHandoverCheck(deactivateBtn.dataset.id, deactivateBtn.dataset.name, "deactivate");
        }

        if (deleteBtn) {
            triggerUserActionWithHandoverCheck(deleteBtn.dataset.id, deleteBtn.dataset.name, "delete");
        }

        if (resetPwdBtn) {
            document.getElementById("resetPwdUserId").value = resetPwdBtn.dataset.id;
            document.getElementById("resetPwdUserName").textContent = resetPwdBtn.dataset.name || "User";
            document.getElementById("resetPwdInput").value = "";
            document.getElementById("resetPwdModal").classList.add("active");
        }

        if (approveBtn) {
            const id = approveBtn.dataset.id;
            if (confirm("Are you sure you want to approve and activate this user?")) {
                const formData = new FormData();
                formData.append("action", "approve");
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
                            alert("Error: " + (data.error || "Failed to approve user"));
                        }
                    })
                    .catch(err => console.error("Error approving user:", err));
            }
        }

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

            officeGroup.style.display = (roleText === "Secretary" || roleText === "Member") ? "grid" : "none";
            userModal.classList.add("active");
        }
    });

    /*
    |--------------------------------------------------------------------------
    | USER FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

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
        const officeId = (roleText === "Secretary" || roleText === "Member") ? document.getElementById("userOffice").value : "";

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert("Please enter a valid email address.");
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
                    location.reload();
                } else {
                    alert("Error: " + (data.error || "Failed to save user"));
                }
            })
            .catch(err => console.error("Error saving user:", err));
    });

    /*
    |--------------------------------------------------------------------------
    | BULK ACTIONS & SELECTION
    |--------------------------------------------------------------------------
    */

    const selectAllUsers = document.getElementById("selectAllUsers");
    const bulkActionBar = document.getElementById("bulkActionBar");
    const bulkSelectedCount = document.getElementById("bulkSelectedCount");
    const bulkOfficeModal = document.getElementById("bulkOfficeModal");
    const bulkOfficeForm = document.getElementById("bulkOfficeForm");

    function getSelectedUserIds() {
        return Array.from(document.querySelectorAll(".user-select-cb:checked")).map(cb => cb.value);
    }

    function updateBulkActionBar() {
        const selected = getSelectedUserIds();
        if (selected.length > 0) {
            if (bulkSelectedCount) bulkSelectedCount.textContent = `${selected.length} selected`;
            if (bulkActionBar) bulkActionBar.style.display = "flex";
        } else {
            if (bulkActionBar) bulkActionBar.style.display = "none";
        }
    }

    if (selectAllUsers) {
        selectAllUsers.addEventListener("change", (e) => {
            const cbs = document.querySelectorAll(".user-select-cb");
            cbs.forEach(cb => cb.checked = e.target.checked);
            updateBulkActionBar();
        });
    }

    document.querySelector("#usersTable tbody").addEventListener("change", (e) => {
        if (e.target.classList.contains("user-select-cb")) {
            const cbs = document.querySelectorAll(".user-select-cb");
            const checkedCbs = document.querySelectorAll(".user-select-cb:checked");
            if (selectAllUsers) {
                selectAllUsers.checked = cbs.length > 0 && cbs.length === checkedCbs.length;
            }
            updateBulkActionBar();
        }
    });

    function sendBulkRequest(action, extraParams = {}) {
        const ids = getSelectedUserIds();
        if (ids.length === 0) {
            alert("Please select at least one user.");
            return;
        }

        const formData = new FormData();
        formData.append("action", action);
        ids.forEach(id => formData.append("ids[]", id));

        for (const [key, value] of Object.entries(extraParams)) {
            formData.append(key, value);
        }

        fetch("../controllers/AdminUsersController.php", {
            method: "POST",
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert("Error: " + (data.error || "Bulk action failed"));
                }
            })
            .catch(err => console.error("Bulk action error:", err));
    }

    document.getElementById("bulkApproveBtn")?.addEventListener("click", () => {
        const count = getSelectedUserIds().length;
        if (confirm(`Are you sure you want to approve and activate ${count} selected user(s)?`)) {
            sendBulkRequest("bulk_approve");
        }
    });

    document.getElementById("bulkActivateBtn")?.addEventListener("click", () => {
        const count = getSelectedUserIds().length;
        if (confirm(`Are you sure you want to set status to Active for ${count} selected user(s)?`)) {
            sendBulkRequest("bulk_status", { status: "Active" });
        }
    });

    document.getElementById("bulkDeactivateBtn")?.addEventListener("click", () => {
        const count = getSelectedUserIds().length;
        if (confirm(`Are you sure you want to set status to Inactive for ${count} selected user(s)?`)) {
            sendBulkRequest("bulk_status", { status: "Inactive" });
        }
    });

    document.getElementById("bulkReassignBtn")?.addEventListener("click", () => {
        const ids = getSelectedUserIds();
        if (ids.length === 0) {
            alert("Please select at least one user.");
            return;
        }
        const countSpan = document.getElementById("bulkOfficeUserCount");
        if (countSpan) countSpan.textContent = ids.length;
        const officeSelect = document.getElementById("bulkTargetOffice");
        if (officeSelect) officeSelect.value = "";
        if (bulkOfficeModal) bulkOfficeModal.classList.add("active");
    });

    if (bulkOfficeForm) {
        bulkOfficeForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const officeId = document.getElementById("bulkTargetOffice").value;
            if (!officeId) {
                alert("Please select an office.");
                return;
            }
            sendBulkRequest("bulk_office", { office_id: officeId });
        });
    }

    /*
    |--------------------------------------------------------------------------
    | WORKFLOW HANDOVER FORM SUBMISSION
    |--------------------------------------------------------------------------
    */

    const handoverForm = document.getElementById("handoverForm");
    if (handoverForm) {
        handoverForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const fromId = document.getElementById("handoverFromUserId").value;
            const toId = document.getElementById("handoverToUserId").value;
            const pendingAction = document.getElementById("handoverPendingAction").value;

            if (!toId) {
                alert("Please select a replacement user.");
                return;
            }

            const formData = new FormData();
            formData.append("action", pendingAction === "delete" ? "handover_and_delete" : "handover_and_deactivate");
            formData.append("from_id", fromId);
            formData.append("to_id", toId);

            fetch("../controllers/AdminUsersController.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert("Error: " + (data.error || "Failed to handover workflows"));
                    }
                })
                .catch(err => console.error("Handover error:", err));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | RESET PASSWORD GENERATOR & FORM
    |--------------------------------------------------------------------------
    */

    document.getElementById("generateRandomPwdBtn")?.addEventListener("click", () => {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%";
        let pwd = "Pass-";
        for (let i = 0; i < 8; i++) {
            pwd += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById("resetPwdInput").value = pwd;
    });

    const resetPwdForm = document.getElementById("resetPwdForm");
    if (resetPwdForm) {
        resetPwdForm.addEventListener("submit", (e) => {
            e.preventDefault();
            const id = document.getElementById("resetPwdUserId").value;
            const pwd = document.getElementById("resetPwdInput").value.trim();
            const userName = document.getElementById("resetPwdUserName").textContent;

            if (!pwd) {
                alert("Please enter a new password.");
                return;
            }

            const formData = new FormData();
            formData.append("action", "reset_password");
            formData.append("id", id);
            formData.append("password", pwd);

            fetch("../controllers/AdminUsersController.php", {
                method: "POST",
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        alert(`Password for ${userName} has been reset successfully!\n\nNew Temp Password: ${pwd}`);
                        location.reload();
                    } else {
                        alert("Error: " + (data.error || "Failed to reset password"));
                    }
                })
                .catch(err => console.error("Reset password error:", err));
        });
    }

    /*
    |--------------------------------------------------------------------------
    | KEYBOARD SHORTCUTS & THEME TOGGLE
    |--------------------------------------------------------------------------
    */

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") {
            closeSlideover("userProfileSlideover");
        }
    });

    document.getElementById("userProfileSlideover")?.addEventListener("click", (e) => {
        if (e.target.classList.contains("slideover-overlay")) {
            closeSlideover("userProfileSlideover");
        }
    });

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
