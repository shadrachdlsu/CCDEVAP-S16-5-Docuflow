<?php

require_once "../controllers/AdminPendingUsersController.php";

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

    <title>DocuFlow - Pending Users</title>

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
        href="../css/admin-dashboard.css">

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

            <a href="admin-dashboard.php"
               class="back-btn">

                <i class="fas fa-arrow-left"></i>
                Back to Dashboard

            </a>

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
        <!-- PENDING USERS PANEL -->
        <!-- ====================================== -->

        <section class="admin-preview-panel">

            <div class="preview-header">

                <h2 class="section-title">
                    Pending Users
                </h2>

                <p class="preview-description">
                    Approve new users to activate their accounts.
                </p>

            </div>

            <div
                id="admin-preview-content"
                class="admin-preview-content">

                <table
                    id="pendingUsersTable"
                    class="display"
                    style="width:100%">

                    <thead>

                        <tr>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Role</th>

                            <th>Office</th>

                            <th>Status</th>

                            <th>Actions</th>

                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($pendingUsers as $user): ?>

                            <tr>

                                <td>

                                    <?= htmlspecialchars($user["name"]) ?>

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

                                    <span class="status-badge status-inactive">

                                        Pending

                                    </span>

                                </td>

                                <td>

                                    <button
                                        class="btn-small approve-btn"
                                        type="button"
                                        style="background: #e0e7ff; color: #3730a3;"
                                        title="Approve User"
                                        data-id="<?= $user["id"] ?>">

                                        Approve

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

<script src="../js/admin-pending-users.js?v=<?= time() ?>"></script>

</body>

</html>
