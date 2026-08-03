<?php

require_once "../controllers/AdminUsersController.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <script>
        (function () {
            const theme = localStorage.getItem("docuflow-theme") || localStorage.getItem("theme");
            if (theme === "dark") {
                document.documentElement.classList.add("dark-mode");
                document.documentElement.style.backgroundColor = "#111827";
            }
        })();
    </script>

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>DocuFlow - Manage Users</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- DataTables -->
    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- Custom CSS -->
    <link
        rel="stylesheet"
        href="../css/admin-dashboard.css?v=<?= time() ?>">

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- ====================================== -->
    <!-- NAVIGATION HEADER -->
    <!-- ====================================== -->

    <header class="admin-header">

        <div class="header-left">

            <a href="admin-dashboard.php"
               class="logo-area">

                <span class="web-logo">
                    DocuFlow
                </span>

            </a>

            <nav class="header-nav">

                <a href="admin-dashboard.php"
                   class="header-nav-item">

                    Dashboard

                </a>

                <a href="admin-users.php"
                   class="header-nav-item active">

                    Manage Users

                </a>

                <a href="admin-offices.php"
                   class="header-nav-item">

                    Manage Offices

                </a>

                <a href="admin-settings.php"
                   class="header-nav-item">

                    System Settings

                </a>

            </nav>

        </div>

        <div class="header-right">

            <div class="user-info">

                <span class="user-role">
                    Admin
                </span>

            </div>

            <div class="header-actions">

                <button
                    id="profileBtn"
                    class="icon-btn"
                    type="button"
                    title="My Profile"
                    onclick="openModal('profileModal')">

                    <i class="fas fa-user-circle"></i>

                </button>

                <button
                    id="themeToggle"
                    class="icon-btn toggle-theme"
                    type="button"
                    title="Toggle theme">

                    <i class="fas fa-moon"></i>

                </button>

                <a
                    href="../controllers/LogoutController.php"
                    class="icon-btn logout-btn"
                    title="Logout">

                    <i class="fas fa-sign-out-alt"></i>

                </a>

            </div>

        </div>

    </header>

    <main class="admin-main">

        <!-- ====================================== -->
        <!-- USERS MANAGEMENT PANEL -->
        <!-- ====================================== -->

        <section class="card admin-preview-panel">

            <div class="card-header">

                <div>

                    <h2 class="card-title">
                        List of Users
                    </h2>

                    <p class="card-subtitle">
                        Manage members, secretaries, and admins.
                    </p>

                </div>

                <button
                    class="btn-primary"
                    type="button"
                    onclick="window.openUserModal()">

                    Add User

                </button>

            </div>

            <!-- Bulk Action Bar -->
            <div
                id="bulkActionBar"
                class="bulk-action-bar"
                style="display: none;">

                <span
                    id="bulkSelectedCount"
                    class="bulk-selected-count">

                    0 selected

                </span>

                <div class="bulk-btn-group">

                    <button
                        id="bulkApproveBtn"
                        type="button"
                        class="btn-small bulk-btn bulk-approve"
                        title="Approve Selected">

                        Bulk Approve

                    </button>

                    <button
                        id="bulkActivateBtn"
                        type="button"
                        class="btn-small bulk-btn bulk-activate"
                        title="Activate Selected">

                        Bulk Activate

                    </button>

                    <button
                        id="bulkDeactivateBtn"
                        type="button"
                        class="btn-small bulk-btn bulk-deactivate"
                        title="Deactivate Selected">

                        Bulk Deactivate

                    </button>

                    <button
                        id="bulkReassignBtn"
                        type="button"
                        class="btn-small bulk-btn bulk-reassign"
                        title="Reassign Office">

                        Reassign Office

                    </button>

                </div>

            </div>

            <div
                id="admin-preview-content"
                class="card-content">

                <table
                    id="usersTable"
                    class="display"
                    style="width:100%">

                    <thead>

                        <tr>

                            <th style="width: 32px; text-align: center;">
                                <input
                                    id="selectAllUsers"
                                    type="checkbox"
                                    title="Select All">
                            </th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Role</th>

                            <th>Office</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($users as $user): ?>

                            <tr>

                                <td style="text-align: center;">

                                    <input
                                        type="checkbox"
                                        class="user-select-cb"
                                        value="<?= $user["id"] ?>">

                                </td>

                                <td>

                                    <a
                                        href="#"
                                        class="user-profile-link"
                                        data-id="<?= $user["id"] ?>"
                                        style="font-weight:600; color:var(--primary); text-decoration:none;">

                                        <?= htmlspecialchars($user["name"]) ?>

                                    </a>

                                </td>

                                <td>

                                    <?= htmlspecialchars($user["email"]) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($user["role"]) ?>

                                </td>

                                <td>

                                    <?= htmlspecialchars($user["office"] ?? "") ?>

                                </td>

                                <td>

                                    <span class="status-badge <?= $user["status"] === "Active" ? "status-active" : "status-inactive" ?>">

                                        <?= htmlspecialchars($user["status"]) ?>

                                    </span>

                                </td>

                                <td class="action-cell">

                                    <div class="action-dropdown-wrapper">

                                        <button
                                            class="btn-small edit-btn"
                                            title="Edit User"
                                            type="button"
                                            data-id="<?= $user["id"] ?>"
                                            data-name="<?= htmlspecialchars($user["name"]) ?>"
                                            data-email="<?= htmlspecialchars($user["email"]) ?>"
                                            data-role="<?= $user["role_id"] ?>"
                                            data-office="<?= $user["office_id"] ?>"
                                            data-status="<?= htmlspecialchars($user["status"]) ?>">

                                            Edit

                                        </button>

                                        <div class="dropdown action-dropdown">

                                            <button
                                                class="action-menu-trigger"
                                                type="button"
                                                title="More Actions">

                                                <i class="fas fa-ellipsis-v"></i>

                                            </button>

                                            <div class="action-menu">

                                                <button
                                                    type="button"
                                                    class="dropdown-item view-profile-btn"
                                                    data-id="<?= $user["id"] ?>">

                                                    View Profile & Activity

                                                </button>

                                                <?php if ($user["status"] === "Active"): ?>

                                                    <button
                                                        type="button"
                                                        class="dropdown-item deactivate-btn"
                                                        data-id="<?= $user["id"] ?>"
                                                        data-name="<?= htmlspecialchars($user["name"]) ?>">

                                                        Deactivate

                                                    </button>

                                                <?php else: ?>

                                                    <button
                                                        type="button"
                                                        class="dropdown-item approve-btn"
                                                        data-id="<?= $user["id"] ?>">

                                                        Approve

                                                    </button>

                                                <?php endif; ?>

                                                <button
                                                    type="button"
                                                    class="dropdown-item reset-pwd-btn"
                                                    data-id="<?= $user["id"] ?>"
                                                    data-name="<?= htmlspecialchars($user["name"]) ?>">

                                                    Reset Password

                                                </button>

                                                <div class="dropdown-divider"></div>

                                                <button
                                                    type="button"
                                                    class="dropdown-item delete-btn text-danger"
                                                    data-id="<?= $user["id"] ?>"
                                                    data-name="<?= htmlspecialchars($user["name"]) ?>">

                                                    Delete User

                                                </button>

                                            </div>

                                        </div>

                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

<!-- ====================================== -->
<!-- USER MODAL (ADD / EDIT) -->
<!-- ====================================== -->

<div
    id="userModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                id="userModalTitle"
                class="card-title"
                style="margin:0;">

                Add User

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('userModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form
            id="userForm"
            class="admin-form">

            <input
                id="userId"
                type="hidden">

            <label class="admin-field">

                <span>
                    Name <span style="color: #ef4444">*</span>
                </span>

                <input
                    id="userName"
                    type="text"
                    required
                    placeholder="Full Name">

            </label>

            <label class="admin-field">

                <span>
                    Email <span style="color: #ef4444">*</span>
                </span>

                <input
                    id="userEmail"
                    type="email"
                    required
                    placeholder="name@office.gov">

            </label>

            <label class="admin-field">

                <span>
                    Password <span style="color: #ef4444">*</span>
                </span>

                <input
                    id="userPassword"
                    type="password"
                    placeholder="Required for new users, leave blank to keep current">

            </label>

            <label class="admin-field">

                <span>
                    Role <span style="color: #ef4444">*</span>
                </span>

                <select
                    id="userRole"
                    required>

                    <option value="">Select Role</option>

                    <?php foreach ($roles as $role): ?>

                        <option value="<?= $role["role_id"] ?>">
                            <?= htmlspecialchars($role["role_name"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <label
                id="officeGroup"
                class="admin-field"
                style="display: none;">

                <span>Office</span>

                <select id="userOffice">

                    <option value="">Select Office</option>

                    <?php foreach ($offices as $office): ?>

                        <option value="<?= $office["id"] ?>">
                            <?= htmlspecialchars($office["name"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <label class="admin-field">

                <span>
                    Status <span style="color: #ef4444">*</span>
                </span>

                <select
                    id="userStatus"
                    required>

                    <option value="Active">Active</option>

                    <option value="Inactive">Inactive</option>

                </select>

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('userModal')">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="admin-submit">

                    Save

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ====================================== -->
<!-- BULK REASSIGN OFFICE MODAL -->
<!-- ====================================== -->

<div
    id="bulkOfficeModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Reassign Office

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('bulkOfficeModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form
            id="bulkOfficeForm"
            class="admin-form">

            <p style="font-size:0.9rem; color:var(--gray-600); margin-bottom:12px;">

                Select the new office for the
                <span id="bulkOfficeUserCount">0</span>
                selected user(s).

            </p>

            <label class="admin-field">

                <span>
                    Target Office <span style="color: #ef4444">*</span>
                </span>

                <select
                    id="bulkTargetOffice"
                    required>

                    <option value="">Select Office</option>

                    <?php foreach ($offices as $office): ?>

                        <option value="<?= $office["id"] ?>">
                            <?= htmlspecialchars($office["name"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('bulkOfficeModal')">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="admin-submit">

                    Reassign

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ====================================== -->
<!-- WORKFLOW HANDOVER MODAL -->
<!-- ====================================== -->

<div
    id="handoverModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Reassign Active Workflows

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('handoverModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form
            id="handoverForm"
            class="admin-form">

            <input
                id="handoverFromUserId"
                type="hidden">

            <input
                id="handoverPendingAction"
                type="hidden">

            <div style="background:var(--accent-yellow-bg); border-left:4px solid var(--accent-yellow); padding:12px 16px; border-radius:8px; margin-bottom:12px;">

                <p style="font-size:0.9rem; color:var(--gray-800); font-weight:600; margin-bottom:4px;">

                    <span id="handoverUserName">User</span>
                    has active workflows!

                </p>

                <p
                    id="handoverSummaryText"
                    style="font-size:0.82rem; color:var(--gray-700); margin:0;">

                    Please select an active member to receive these active documents before proceeding.

                </p>

            </div>

            <label class="admin-field">

                <span>
                    Reassign Workflows To <span style="color: #ef4444">*</span>
                </span>

                <select
                    id="handoverToUserId"
                    required>

                    <option value="">Select Replacement Member</option>

                    <?php foreach ($users as $u): ?>

                        <?php if ($u["status"] === "Active"): ?>

                            <option
                                class="handover-opt"
                                value="<?= $u["id"] ?>"
                                data-id="<?= $u["id"] ?>">

                                <?= htmlspecialchars($u["name"]) ?>
                                (<?= htmlspecialchars($u["role"]) ?> - <?= htmlspecialchars($u["office"] ?? "No Office") ?>)

                            </option>

                        <?php endif; ?>

                    <?php endforeach; ?>

                </select>

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('handoverModal')">

                    Cancel

                </button>

                <button
                    id="handoverSubmitBtn"
                    type="submit"
                    class="admin-submit"
                    style="background:var(--accent-yellow); color:#fff;">

                    Handover & Continue

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ====================================== -->
<!-- RESET PASSWORD MODAL -->
<!-- ====================================== -->

<div
    id="resetPwdModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Reset Password

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('resetPwdModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form
            id="resetPwdForm"
            class="admin-form">

            <input
                id="resetPwdUserId"
                type="hidden">

            <p style="font-size:0.9rem; color:var(--gray-600); margin-bottom:8px;">

                Resetting password for:
                <strong id="resetPwdUserName">User</strong>

            </p>

            <label class="admin-field">

                <span>
                    New Password / Temp Credential <span style="color: #ef4444">*</span>
                </span>

                <div style="display:flex; gap:8px;">

                    <input
                        id="resetPwdInput"
                        type="text"
                        required
                        placeholder="Enter new password"
                        style="flex:1;">

                    <button
                        id="generateRandomPwdBtn"
                        type="button"
                        class="btn-small"
                        style="background:var(--primary-tint); color:var(--primary); padding:0 12px; white-space:nowrap;">

                        Generate

                    </button>

                </div>

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('resetPwdModal')">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="admin-submit">

                    Save Password

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ====================================== -->
<!-- USER PROFILE SLIDEOVER DRAWER -->
<!-- ====================================== -->

<div
    id="userProfileSlideover"
    class="slideover-overlay">

    <div class="slideover-panel">

        <div class="slideover-header">

            <div style="display:flex; align-items:center; gap:12px;">

                <div
                    id="drawerAvatar"
                    class="user-avatar-circle">

                    U

                </div>

                <div>

                    <h3
                        id="drawerName"
                        class="slideover-title">

                        User Profile

                    </h3>

                    <span
                        id="drawerEmail"
                        class="slideover-subtitle">

                        user@docuflow.local

                    </span>

                </div>

            </div>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeSlideover('userProfileSlideover')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <div class="slideover-body">

            <div class="drawer-meta-bar">

                <span
                    id="drawerRole"
                    class="tag tag-role">

                    Role

                </span>

                <span
                    id="drawerOffice"
                    class="tag tag-office">

                    Office

                </span>

                <span
                    id="drawerStatus"
                    class="status-badge">

                    Active

                </span>

            </div>

            <div class="drawer-stats-grid">

                <div class="drawer-stat-card">

                    <span
                        id="statCreated"
                        class="drawer-stat-num">

                        0

                    </span>

                    <span class="drawer-stat-lbl">
                        Created
                    </span>

                </div>

                <div class="drawer-stat-card">

                    <span
                        id="statPending"
                        class="drawer-stat-num">

                        0

                    </span>

                    <span class="drawer-stat-lbl">
                        Pending
                    </span>

                </div>

                <div class="drawer-stat-card">

                    <span
                        id="statRoutes"
                        class="drawer-stat-num">

                        0

                    </span>

                    <span class="drawer-stat-lbl">
                        Routes
                    </span>

                </div>

            </div>

            <div class="drawer-section">

                <h4 class="drawer-section-title">

                    Activity & Document History

                </h4>

                <div
                    id="drawerActivityLoading"
                    style="text-align:center; padding:24px; color:var(--gray-500);">

                    Loading activity...

                </div>

                <div
                    id="drawerActivityContent"
                    style="display:none;">

                    <h5 class="drawer-subheading">
                        Created Documents
                    </h5>

                    <ul
                        id="drawerCreatedDocs"
                        class="drawer-doc-list">

                    </ul>

                    <h5
                        class="drawer-subheading"
                        style="margin-top:16px;">

                        Assigned Documents

                    </h5>

                    <ul
                        id="drawerAssignedDocs"
                        class="drawer-doc-list">

                    </ul>

                </div>

            </div>

        </div>

        <div class="slideover-footer">

            <span style="font-size:0.78rem; color:var(--gray-500);">

                Registered:
                <strong id="drawerRegisteredDate">-</strong>

            </span>

            <button
                type="button"
                class="btn-small"
                style="background:var(--gray-200); color:var(--gray-700); padding:6px 14px;"
                onclick="closeSlideover('userProfileSlideover')">

                Close

            </button>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- MY PROFILE MODAL -->
<!-- ====================================== -->

<div
    id="profileModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                My Profile

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('profileModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <div class="modal-body">

            <div style="display: grid; gap: 12px;">

                <div class="admin-field">

                    <span>Name</span>

                    <div id="profileName" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        Administrator
                    </div>

                </div>

                <div class="admin-field">

                    <span>Email</span>

                    <div id="profileEmail" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        admin@docuflow.com
                    </div>

                </div>

                <div class="admin-field">

                    <span>Office</span>

                    <div id="profileOffice" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        System Administration
                    </div>

                </div>

                <div class="admin-field">

                    <span>Role</span>

                    <div id="profileRole" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        Administrator
                    </div>

                </div>

            </div>

        </div>

        <div
            class="modal-footer"
            style="display: flex; justify-content: flex-end; margin-top: 16px;">

            <button
                class="btn-small cancel-btn"
                type="button"
                onclick="closeModal('profileModal')">

                Close

            </button>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- JAVASCRIPT LIBRARIES & SCRIPTS -->
<!-- ====================================== -->

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="../js/admin-users.js?v=<?= time() ?>"></script>

</body>

</html>
