<?php

require_once "../controllers/AdminSettingsController.php";

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

    <title>DocuFlow - System Settings</title>

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
                   class="header-nav-item">

                    Manage Users

                </a>

                <a href="admin-offices.php"
                   class="header-nav-item">

                    Manage Offices

                </a>

                <a href="admin-settings.php"
                   class="header-nav-item active">

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
        <!-- USER REGISTRATION SETTINGS PANEL -->
        <!-- ====================================== -->

        <section class="card admin-preview-panel" style="margin-bottom: 24px;">

            <div class="card-header">

                <div>

                    <h2 class="card-title">
                        User Registration &amp; Access Controls
                    </h2>

                    <p class="card-subtitle">
                        Configure user account registration policies and administrator approval requirements.
                    </p>

                </div>

            </div>

            <div class="card-content">

                <div style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.08); padding: 24px; border-radius: 8px;">

                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">

                        <div>

                            <strong style="font-size: 1.05rem; font-weight: 600; display: block; margin-bottom: 4px;">
                                Registration Approval Policy
                            </strong>

                            <p style="margin: 0; color: var(--gray-400, #9ca3af); font-size: 0.9rem; line-height: 1.4;">
                                Select how newly registered user accounts should be processed upon registration.
                            </p>

                        </div>

                        <span
                            id="approvalStatusBadge"
                            class="status-badge <?= $requireAdminApproval ? 'status-active' : 'status-inactive' ?>"
                            style="font-size: 0.78rem; padding: 4px 12px;">
                            <?= $requireAdminApproval ? 'Enabled' : 'Disabled (Auto-Approve)' ?>
                        </span>

                    </div>

                    <div style="display: flex; flex-direction: column; gap: 14px; padding-top: 16px; border-top: 1px solid var(--gray-200, rgba(255,255,255,0.08));">

                        <label style="display: inline-flex; align-items: flex-start; gap: 12px; cursor: pointer;">

                            <input
                                type="radio"
                                name="requireAdminApproval"
                                value="1"
                                class="approval-radio-option"
                                <?= $requireAdminApproval ? 'checked' : '' ?>
                                style="margin-top: 3px; width: 18px; height: 18px; cursor: pointer; accent-color: #4f46e5;">

                            <div>

                                <span style="font-weight: 600; font-size: 0.95rem;">
                                    Require Admin Approval
                                </span>

                                <p style="margin: 2px 0 0 0; font-size: 0.85rem; color: var(--gray-400, #9ca3af);">
                                    Newly registered users must be manually approved by an administrator before logging in.
                                </p>

                            </div>

                        </label>

                        <label style="display: inline-flex; align-items: flex-start; gap: 12px; cursor: pointer;">

                            <input
                                type="radio"
                                name="requireAdminApproval"
                                value="0"
                                class="approval-radio-option"
                                <?= !$requireAdminApproval ? 'checked' : '' ?>
                                style="margin-top: 3px; width: 18px; height: 18px; cursor: pointer; accent-color: #4f46e5;">

                            <div>

                                <span style="font-weight: 600; font-size: 0.95rem;">
                                    Auto-Approve Users
                                </span>

                                <p style="margin: 2px 0 0 0; font-size: 0.85rem; color: var(--gray-400, #9ca3af);">
                                    User accounts are automatically activated immediately upon registration.
                                </p>

                            </div>

                        </label>

                    </div>

                </div>

            </div>

        </section>

        <!-- ====================================== -->
        <!-- SYSTEM SETTINGS PANEL -->
        <!-- ====================================== -->

        <section class="card admin-preview-panel">

            <div class="card-header">

                <div>

                    <h2 class="card-title">
                        System Settings &amp; Configuration
                    </h2>

                    <p class="card-subtitle">
                        Manage document classification, assigned office routing, and system preferences.
                    </p>

                </div>

                <button
                    class="btn-primary"
                    type="button"
                    onclick="window.openDocTypeModal()">

                    Add Document Type

                </button>

            </div>

            <div
                id="admin-preview-content"
                class="card-content">

                <table
                    id="docTypesTable"
                    class="display"
                    style="width:100%">

                    <thead>

                        <tr>

                            <th>Type Name</th>

                            <th>Assigned Offices</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($docTypes as $type): ?>

                            <tr>

                                <td>

                                    <?= htmlspecialchars($type["name"]) ?>

                                </td>

                                <td>

                                    <?php if (!empty($type["offices"])): ?>

                                        <?php foreach ($type["offices"] as $offName): ?>

                                            <span class="office-badge" style="display:inline-block; background:rgba(79,70,229,0.1); color:#4f46e5; border:1px solid rgba(79,70,229,0.2); padding:3px 10px; border-radius:12px; font-size:0.82rem; font-weight:500; margin:2px 4px 2px 0;">
                                                <?= htmlspecialchars($offName) ?>
                                            </span>

                                        <?php endforeach; ?>

                                    <?php else: ?>

                                        <span style="color:var(--gray-400); font-style:italic;">
                                            No offices assigned
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <button
                                        class="btn-small edit-btn"
                                        type="button"
                                        title="Edit Document Type"
                                        data-id="<?= $type["id"] ?>"
                                        data-name="<?= htmlspecialchars($type["name"]) ?>"
                                        data-offices="<?= htmlspecialchars(json_encode($type["offices"])) ?>">

                                        Edit

                                    </button>

                                    <button
                                        class="btn-small delete-btn"
                                        type="button"
                                        title="Delete Document Type"
                                        data-id="<?= $type["id"] ?>">

                                        Delete

                                    </button>

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
<!-- DOCUMENT TYPE MODAL -->
<!-- ====================================== -->

<div
    id="docTypeModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                id="docTypeModalTitle"
                class="card-title"
                style="margin:0;">

                Add Document Type

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('docTypeModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form
            id="docTypeForm"
            class="admin-form">

            <input
                id="docTypeId"
                type="hidden">

            <label class="admin-field">

                <span>
                    Type Name <span style="color: #ef4444">*</span>
                </span>

                <input
                    id="docTypeName"
                    type="text"
                    required
                    placeholder="e.g. Board Resolution">

            </label>

            <label class="admin-field">

                <span>
                    Assign to Offices <span style="color: #ef4444">*</span>
                </span>

                <select
                    id="docTypeOffices"
                    multiple
                    required
                    style="min-height: 110px;">

                    <?php foreach ($officesList as $office): ?>

                        <option value="<?= htmlspecialchars($office["name"]) ?>">
                            <?= htmlspecialchars($office["name"]) ?>
                        </option>

                    <?php endforeach; ?>

                </select>

                <small style="color:var(--gray-500, #6b7280); display:block; margin-top:6px; font-size:0.8rem;">
                    Hold Ctrl (Windows) or Command (Mac) to select multiple offices for this document type.
                </small>

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('docTypeModal')">

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

<script src="../js/admin-document-types.js?v=<?= time() ?>"></script>

<script src="../js/admin-settings.js?v=<?= time() ?>"></script>

</body>

</html>
