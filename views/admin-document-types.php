<?php

header("Location: admin-settings.php");
exit;

?>


<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>DocuFlow - Document Types</title>

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
                    id="themeToggle"
                    class="icon-btn toggle-theme"
                    type="button"
                    aria-label="Toggle dark/light mode"
                    title="Toggle theme">

                    <i class="fas fa-moon"></i>

                </button>

                <a
                    href="../controllers/LogoutController.php"
                    class="icon-btn logout-btn"
                    aria-label="Exit / Logout"
                    title="Logout">

                    <i class="fas fa-sign-out-alt"></i>

                </a>

            </div>

        </div>

    </header>

    <main class="admin-main">

        <!-- ====================================== -->
        <!-- DOCUMENT TYPES PANEL -->
        <!-- ====================================== -->

        <section class="card admin-preview-panel">

            <div class="card-header">

                <div>

                    <h2 class="card-title">
                        Document Types
                    </h2>

                    <p class="card-subtitle">
                        Create and edit system-wide document types.
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

                                    <?= htmlspecialchars(implode(", ", $type["offices"])) ?>

                                </td>

                                <td>

                                    <button
                                        class="btn-small edit-btn"
                                        type="button"
                                        title="Edit Document Type"
                                        data-id="<?= (int) $type["id"] ?>"
                                        data-name="<?= htmlspecialchars($type["name"]) ?>"
                                        data-offices="<?= htmlspecialchars(json_encode($type["offices"])) ?>">

                                        Edit

                                    </button>

                                    <button
                                        class="btn-small delete-btn"
                                        type="button"
                                        title="Delete Document Type"
                                        data-id="<?= (int) $type["id"] ?>">

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
                    style="min-height: 100px;">

                    <?php foreach ($officesList as $office): ?>

                        <option value="<?= htmlspecialchars($office["name"]) ?>">
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
<!-- JAVASCRIPT LIBRARIES & SCRIPTS -->
<!-- ====================================== -->

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script src="../js/admin-document-types.js?v=<?= time() ?>"></script>

</body>

</html>
