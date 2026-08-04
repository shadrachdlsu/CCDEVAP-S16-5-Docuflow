<?php

require_once "../controllers/AdminOfficesController.php";

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

    <title>DocuFlow - Manage Offices</title>

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
                   class="header-nav-item active">

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
        <!-- METRIC CARDS OVERVIEW -->
        <!-- ====================================== -->

        <div
            class="kpi-grid"
            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 24px;">

            <div class="card stat-card" style="padding: 20px;">

                <span class="stat-title" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); font-weight: 600;">
                    Total Departments
                </span>

                <div class="stat-value" style="font-size: 2rem; font-weight: 700; color: var(--gray-900); margin-top: 8px;">
                    <?= (int) $totalOfficesCount ?>
                </div>

                <span class="stat-subtext" style="font-size: 0.8rem; color: var(--gray-500); margin-top: 4px; display: block;">
                    Registered system offices
                </span>

            </div>

            <div class="card stat-card" style="padding: 20px;">

                <span class="stat-title" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); font-weight: 600;">
                    Active Departments
                </span>

                <div class="stat-value" style="font-size: 2rem; font-weight: 700; color: var(--gray-900); margin-top: 8px;">
                    <?= (int) $activeOfficesCount ?>
                </div>

                <span class="stat-subtext" style="font-size: 0.8rem; color: var(--gray-500); margin-top: 4px; display: block;">
                    Operational & routing ready
                </span>

            </div>

            <div class="card stat-card" style="padding: 20px;">

                <span class="stat-title" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); font-weight: 600;">
                    Assigned Secretaries
                </span>

                <div class="stat-value" style="font-size: 2rem; font-weight: 700; color: var(--gray-900); margin-top: 8px;">
                    <?= (int) $assignedSecretariesCount ?>
                </div>

                <span class="stat-subtext" style="font-size: 0.8rem; color: var(--gray-500); margin-top: 4px; display: block;">
                    Offices with active leads
                </span>

            </div>

            <div class="card stat-card" style="padding: 20px;">

                <span class="stat-title" style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; color: var(--gray-500); font-weight: 600;">
                    In-Queue Documents
                </span>

                <div class="stat-value" style="font-size: 2rem; font-weight: 700; color: var(--gray-900); margin-top: 8px;">
                    <?= (int) $totalActiveDocs ?>
                </div>

                <span class="stat-subtext" style="font-size: 0.8rem; color: var(--gray-500); margin-top: 4px; display: block;">
                    Active across all queues
                </span>

            </div>

        </div>

        <!-- ====================================== -->
        <!-- MANAGE OFFICES PANEL -->
        <!-- ====================================== -->

        <section class="card admin-preview-panel">

            <div class="card-header">

                <div>

                    <h2 class="card-title">
                        Manage Office Departments
                    </h2>

                    <p class="card-subtitle">
                        Configure organizational departments, location metadata, assigned secretaries, and monitor document queue health.
                    </p>

                </div>

                <button
                    class="btn-primary"
                    type="button"
                    onclick="window.openOfficeModal()">

                    Add Office

                </button>

            </div>

            <div
                id="admin-preview-content"
                class="card-content">

                <table
                    id="officesTable"
                    class="display"
                    style="width:100%">

                    <thead>

                        <tr>

                            <th>Code</th>

                            <th>Office Department</th>

                            <th>Assigned Secretary</th>

                            <th>Personnel</th>

                            <th>Workload Queue</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($officesDetailed as $office): ?>

                            <tr>

                                <td>

                                    <span 
                                        class="office-code-badge"
                                        style="font-family: monospace; font-weight: 700; padding: 4px 8px; background: rgba(79, 70, 229, 0.1); color: #4f46e5; border-radius: 6px; font-size: 0.85rem;">
                                        <?= htmlspecialchars($office["code"]) ?>
                                    </span>

                                </td>

                                <td>

                                    <div style="display: flex; flex-direction: column;">

                                        <strong style="color: var(--gray-900); font-size: 0.95rem;">
                                            <?= htmlspecialchars($office["name"]) ?>
                                        </strong>

                                        <small style="color: var(--gray-500); margin-top: 2px;">
                                            <?= htmlspecialchars($office["location"]) ?> 
                                            <?php if (!empty($office["contact_email"]) && $office["contact_email"] !== "N/A"): ?>
                                                &bull; <?= htmlspecialchars($office["contact_email"]) ?>
                                            <?php endif; ?>
                                        </small>

                                    </div>

                                </td>

                                <td>

                                    <?php if (!empty($office["secretary_name"])): ?>

                                        <div style="display: flex; align-items: center; gap: 8px;">

                                            <span style="font-weight: 500; color: var(--gray-800);">
                                                <?= htmlspecialchars($office["secretary_name"]) ?>
                                            </span>

                                            <button
                                                class="btn-small reassign-btn"
                                                type="button"
                                                title="Reassign Secretary"
                                                style="padding: 2px 8px; font-size: 0.75rem; background: var(--gray-200); color: var(--gray-700);"
                                                data-id="<?= (int) $office["id"] ?>"
                                                data-name="<?= htmlspecialchars($office["name"]) ?>"
                                                data-secretary-id="<?= (int) $office["secretary_id"] ?>">

                                                Reassign

                                            </button>

                                        </div>

                                    <?php else: ?>

                                        <button
                                            class="btn-small reassign-btn"
                                            type="button"
                                            style="padding: 4px 10px; font-size: 0.8rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px dashed rgba(239, 68, 68, 0.3);"
                                            data-id="<?= (int) $office["id"] ?>"
                                            data-name="<?= htmlspecialchars($office["name"]) ?>"
                                            data-secretary-id="">

                                            Assign Secretary

                                        </button>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <span style="font-weight: 600; color: var(--gray-700);">
                                        <?= (int)$office["member_count"] ?> Members
                                    </span>

                                </td>

                                <td>

                                    <?php 
                                    $docCount = (int)$office["active_doc_count"];
                                    $badgeStyle = "background: rgba(16, 185, 129, 0.1); color: #10b981;"; // Normal
                                    $badgeText = $docCount . " Active Docs";

                                    if ($docCount > 15) {
                                        $badgeStyle = "background: rgba(239, 68, 68, 0.1); color: #ef4444;"; // Bottleneck / High
                                        $badgeText .= " (High Load)";
                                    } elseif ($docCount > 5) {
                                        $badgeStyle = "background: rgba(245, 158, 11, 0.1); color: #d97706;"; // Moderate
                                    }
                                    ?>

                                    <span 
                                        class="workload-badge"
                                        style="display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 0.82rem; font-weight: 600; <?= $badgeStyle ?>">
                                        <?= $badgeText ?>
                                    </span>

                                </td>

                                <td>

                                    <?php if ($office["is_active"] == 1): ?>

                                        <span 
                                            class="status-badge active"
                                            style="display: inline-block; padding: 3px 8px; background: rgba(16, 185, 129, 0.15); color: #059669; border-radius: 10px; font-size: 0.78rem; font-weight: 600;">
                                            Active
                                        </span>

                                    <?php else: ?>

                                        <span 
                                            class="status-badge inactive"
                                            style="display: inline-block; padding: 3px 8px; background: rgba(107, 114, 128, 0.15); color: #4b5563; border-radius: 10px; font-size: 0.78rem; font-weight: 600;">
                                            Inactive
                                        </span>

                                    <?php endif; ?>

                                </td>

                                <td>

                                    <div style="display: flex; gap: 4px; flex-wrap: wrap;">

                                        <button
                                            class="btn-small edit-btn"
                                            type="button"
                                            title="Edit Office"
                                            data-id="<?= (int) $office["id"] ?>"
                                            data-name="<?= htmlspecialchars($office["name"]) ?>"
                                            data-code="<?= htmlspecialchars($office["code"]) ?>"
                                            data-location="<?= htmlspecialchars($office["location"]) ?>"
                                            data-email="<?= htmlspecialchars($office["contact_email"]) ?>"
                                            data-active="<?= (int) $office["is_active"] ?>">

                                            Edit

                                        </button>

                                        <button
                                            class="btn-small toggle-btn"
                                            type="button"
                                            title="<?= $office["is_active"] == 1 ? 'Deactivate Office' : 'Activate Office' ?>"
                                            data-id="<?= (int) $office["id"] ?>"
                                            data-active="<?= (int) $office["is_active"] ?>">

                                            <?= $office["is_active"] == 1 ? "Deactivate" : "Activate" ?>

                                        </button>

                                        <button
                                            class="btn-small delete-btn"
                                            type="button"
                                            title="Delete Office"
                                            data-id="<?= (int) $office["id"] ?>"
                                            data-name="<?= htmlspecialchars($office["name"]) ?>">

                                            Delete

                                        </button>

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
<!-- OFFICE MODAL (ADD / EDIT) -->
<!-- ====================================== -->

<div
    id="officeModal"
    class="modal-overlay">

    <div class="modal-content" style="max-width: 500px;">

        <div class="modal-header">

            <h3
                id="officeModalTitle"
                class="card-title"
                style="margin:0;">

                Add Office Department

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('officeModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form
            id="officeForm"
            class="admin-form">

            <input
                id="officeId"
                type="hidden">

            <label class="admin-field" style="margin-bottom: 12px;">

                <span>
                    Office Name <span style="color: #ef4444">*</span>
                </span>

                <input
                    id="officeName"
                    type="text"
                    required
                    placeholder="e.g. Finance Office">

            </label>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">

                <label class="admin-field">

                    <span>
                        Office Code / Acronym
                    </span>

                    <input
                        id="officeCode"
                        type="text"
                        placeholder="e.g. FIN">

                </label>

                <label class="admin-field">

                    <span>
                        Status
                    </span>

                    <select id="officeIsActive">

                        <option value="1">Active</option>

                        <option value="0">Inactive</option>

                    </select>

                </label>

            </div>

            <label class="admin-field" style="margin-bottom: 12px;">

                <span>
                    Location / Building / Room
                </span>

                <input
                    id="officeLocation"
                    type="text"
                    placeholder="e.g. Main Administration Building, Room 204">

            </label>

            <label class="admin-field" style="margin-bottom: 16px;">

                <span>
                    Contact Email
                </span>

                <input
                    id="officeContactEmail"
                    type="email"
                    placeholder="e.g. finance@office.gov">

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('officeModal')">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="admin-submit">

                    Save Office

                </button>

            </div>

        </form>

    </div>

</div>

<!-- ====================================== -->
<!-- REASSIGN SECRETARY MODAL -->
<!-- ====================================== -->

<div
    id="reassignModal"
    class="modal-overlay">

    <div class="modal-content" style="max-width: 450px;">

        <div class="modal-header">

            <h3 class="card-title" style="margin:0;">
                Assign Office Secretary
            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('reassignModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form id="reassignForm" class="admin-form">

            <input id="reassignOfficeId" type="hidden">

            <p style="font-size: 0.9rem; color: var(--gray-600); margin-bottom: 16px;">
                Select a Secretary user to assign as the primary lead for <strong id="reassignTargetOfficeName"></strong>.
            </p>

            <label class="admin-field" style="margin-bottom: 16px;">

                <span>
                    Select Secretary <span style="color: #ef4444">*</span>
                </span>

                <select id="reassignSecretarySelect" required>

                    <option value="" disabled selected>-- Select Secretary User --</option>

                    <?php foreach ($availableSecretaries as $sec): ?>

                        <option value="<?= htmlspecialchars($sec["user_id"]) ?>">

                            <?= htmlspecialchars($sec["full_name"]) ?> (<?= htmlspecialchars($sec["email"]) ?>)

                            <?php if (!empty($sec["current_office"])): ?>

                                &bull; Currently assigned to <?= htmlspecialchars($sec["current_office"]) ?>

                            <?php endif; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">

                <button
                    type="button"
                    class="btn-small"
                    style="background:var(--gray-200); color:var(--gray-700); padding:8px 16px;"
                    onclick="closeModal('reassignModal')">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="admin-submit">

                    Confirm Assignment

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

<script src="../js/admin-offices.js?v=<?= time() ?>"></script>

</body>

</html>
