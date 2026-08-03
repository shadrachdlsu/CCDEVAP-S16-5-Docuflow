<?php

require_once "../controllers/SecretaryDashboardController.php";

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

    <title>DocuFlow – Secretary Dashboard</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <!-- DataTables -->
    <link
        rel="stylesheet"
        href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">

    <!-- Custom CSS -->
    <link
        rel="stylesheet"
        href="../css/admin-dashboard.css?v=<?= time() ?>">

    <link
        rel="stylesheet"
        href="../css/secretary.css?v=<?= time() ?>">

    <style>
        .success-msg {
            color: #047857;
            background: #d1fae5;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .error-msg {
            color: #dc2626;
            background: #fee2e2;
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-weight: 500;
        }

        .page {
            display: none;
        }

        .page.active {
            display: block;
        }
    </style>

</head>

<body class="admin-body secretary-body">

<!-- ====================================== -->
<!-- TOP HEADER -->
<!-- ====================================== -->

<header class="admin-header">

    <div class="header-left">

        <a href="secretary-dashboard.php" class="logo-area">
            <span class="web-logo">
                DocuFlow
            </span>
        </a>

        <nav class="header-nav">

            <a
                href="#dashboard"
                class="header-nav-item active"
                data-target="dashboard">

                Dashboard

            </a>

            <a
                href="#create"
                class="header-nav-item"
                data-target="create">

                Create Document

            </a>

            <a
                href="#documents"
                class="header-nav-item"
                data-target="documents">

                All Documents

            </a>

            <a
                href="#receive"
                class="header-nav-item"
                data-target="receive">

                Receive

            </a>

            <a
                href="#pending"
                class="header-nav-item"
                data-target="pending">

                Pending

            </a>

            <a
                href="#release"
                class="header-nav-item"
                data-target="release">

                Release / Forward

            </a>

            <a
                href="#types"
                class="header-nav-item"
                data-target="types">

                Document Types

            </a>

        </nav>

    </div>

    <div class="header-right">

        <div class="user-info">

            <span class="user-role">
                Secretary - <?= htmlspecialchars($officeName) ?>
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

<!-- ====================================== -->
<!-- MAIN CONTENT AREA -->
<!-- ====================================== -->

<main
    id="main-content"
    class="admin-main">

    <?php if (isset($_SESSION["success"])): ?>

        <div class="success-msg">
            <?= htmlspecialchars($_SESSION["success"]); unset($_SESSION["success"]); ?>
        </div>

    <?php endif; ?>

    <?php if (isset($_SESSION["error"])): ?>

        <div class="error-msg">
            <?= htmlspecialchars($_SESSION["error"]); unset($_SESSION["error"]); ?>
        </div>

    <?php endif; ?>

    <!-- ====================================== -->
    <!-- DASHBOARD PAGE -->
    <!-- ====================================== -->

    <div
        id="page-dashboard"
        class="page active">

        <div class="stats-row" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">

            <div class="stat-card">
                <h3 class="card-subtitle">Total Documents</h3>
                <p class="kpi-value" style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-top: 8px;"><?= $stats["total"] ?></p>
            </div>

            <div class="stat-card">
                <h3 class="card-subtitle">Pending / In Progress</h3>
                <p class="kpi-value" style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-top: 8px;"><?= $stats["pending"] ?></p>
            </div>

            <div class="stat-card">
                <h3 class="card-subtitle">Signed</h3>
                <p class="kpi-value" style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-top: 8px;"><?= $stats["signed"] ?></p>
            </div>

            <div class="stat-card">
                <h3 class="card-subtitle">Finished</h3>
                <p class="kpi-value" style="font-size: 2rem; font-weight: 700; color: var(--primary); margin-top: 8px;"><?= $stats["finished"] ?></p>
            </div>

        </div>

        </div>

        <!-- ====================================== -->
        <!-- CREATE DOCUMENT PAGE -->
        <!-- ====================================== -->

        <div
            id="page-create"
            class="page">

            <h2 class="page-title">
                Create New Document
            </h2>

            <form
                action="../controllers/SecretaryCreateController.php"
                method="POST"
                enctype="multipart/form-data"
                class="create-panel">

                <div class="form-field">

                    <label class="field-label">
                        Document Title <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="title"
                        class="field-input"
                        placeholder="Enter document title"
                        required>

                </div>

                <div class="form-field">

                    <label class="field-label">
                        Document Type <span class="required">*</span>
                    </label>

                    <select
                        name="type_id"
                        class="field-select"
                        required>

                        <option value="">-- Select Type --</option>

                        <?php foreach ($documentTypes as $type): ?>

                            <option value="<?= htmlspecialchars($type["type_id"]) ?>">
                                <?= htmlspecialchars($type["type_name"]) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="form-field">

                    <label class="field-label">
                        Upload Document (PDF)
                    </label>

                    <input
                        type="file"
                        name="document_file"
                        accept=".pdf"
                        class="field-input"
                        style="padding: 10px;">

                </div>

                <button
                    type="submit"
                    class="btn-primary">

                    Create & Route Document

                </button>

            </form>

        </div>

        <!-- ====================================== -->
        <!-- ALL DOCUMENTS PAGE -->
        <!-- ====================================== -->

        <div
            id="page-documents"
            class="page">

            <h2 class="page-title">
                All Documents
            </h2>

            <table
                id="table-all-documents"
                class="display nowrap"
                width="100%">

                <thead>

                    <tr>

                        <th>Tracking Code</th>

                        <th>Title</th>

                        <th>Type</th>

                        <th>Date Created</th>

                        <th>Creator</th>

                        <th>Status</th>

                        <th>Current Office</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($allDocs as $doc): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($doc["tracking_code"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["title"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["type_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(!empty($doc["created_at"]) ? date("M d, Y h:i A", strtotime($doc["created_at"])) : "N/A") ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["creator_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["status"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["current_office_name"]) ?>
                            </td>

                            <td>

                                <?php if (!empty($doc["file_path"])): ?>

                                    <button
                                        type="button"
                                        class="btn-small btn-view"
                                        data-file="<?= htmlspecialchars($doc["file_path"]) ?>">

                                        View PDF

                                    </button>

                                <?php endif; ?>

                                <button
                                    type="button"
                                    class="btn-small btn-trail"
                                    data-id="<?= htmlspecialchars($doc["document_id"]) ?>">

                                    Paper Trail

                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- ====================================== -->
        <!-- RECEIVE DOCUMENTS PAGE -->
        <!-- ====================================== -->

        <div
            id="page-receive"
            class="page">

            <h2 class="page-title">
                Documents to Receive
            </h2>

            <table
                id="table-receive"
                class="display nowrap"
                width="100%">

                <thead>

                    <tr>

                        <th>Tracking Code</th>

                        <th>Title</th>

                        <th>Type</th>

                        <th>Creator</th>

                        <th>Status</th>

                        <th>Assignees</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach (array_filter($allDocs, fn($d) => !in_array($d["status"], ["Completed", "Recalled"])) as $doc): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($doc["tracking_code"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["title"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["type_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["creator_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["status"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["assignee_names"] ?? "None") ?>
                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn-small btn-trail"
                                    data-id="<?= htmlspecialchars($doc["document_id"]) ?>">

                                    Paper Trail

                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- ====================================== -->
        <!-- PENDING DOCUMENTS PAGE -->
        <!-- ====================================== -->

        <div
            id="page-pending"
            class="page">

            <h2 class="page-title">
                Pending Documents
            </h2>

            <table
                id="table-pending"
                class="display nowrap"
                width="100%">

                <thead>

                    <tr>

                        <th>Tracking Code</th>

                        <th>Title</th>

                        <th>Type</th>

                        <th>Creator</th>

                        <th>Status</th>

                        <th>Assignees</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach (array_filter($allDocs, fn($d) => in_array($d["status"], ["Created", "Pending", "Received", "Released", "For Signature", "Rejected"])) as $doc): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($doc["tracking_code"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["title"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["type_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["creator_name"]) ?>
                            </td>

                            <td>

                                <span style="background:#fef3c7; padding:2px 8px; border-radius:12px; font-weight:600; font-size:0.8rem;">
                                    Pending
                                </span>

                            </td>

                            <td>
                                <?= htmlspecialchars($doc["assignee_names"] ?? "None") ?>
                            </td>

                            <td style="display: flex; gap: 4px;">

                                <button
                                    type="button"
                                    class="btn-primary btn-sm btn-assign"
                                    data-id="<?= htmlspecialchars($doc["document_id"]) ?>"
                                    data-title="<?= htmlspecialchars($doc["title"]) ?>">

                                    Assign

                                </button>

                                <form
                                    method="POST"
                                    action="../controllers/SecretaryStatusController.php"
                                    onsubmit="return confirm('Mark as Finished?');">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="finish">

                                    <input
                                        type="hidden"
                                        name="document_id"
                                        value="<?= htmlspecialchars($doc["document_id"]) ?>">

                                    <button
                                        type="submit"
                                        class="btn-primary btn-sm">

                                        Finish

                                    </button>

                                </form>

                                <form
                                    method="POST"
                                    action="../controllers/SecretaryStatusController.php"
                                    onsubmit="return confirm('Cancel this document?');">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="cancel">

                                    <input
                                        type="hidden"
                                        name="document_id"
                                        value="<?= htmlspecialchars($doc["document_id"]) ?>">

                                    <button
                                        type="submit"
                                        class="btn-primary btn-sm">

                                        Cancel

                                    </button>

                                </form>

                                <button
                                    type="button"
                                    class="btn-primary btn-sm btn-trail"
                                    data-id="<?= htmlspecialchars($doc["document_id"]) ?>">

                                    Trail

                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- ====================================== -->
        <!-- RELEASE / FORWARD PAGE -->
        <!-- ====================================== -->

        <div
            id="page-release"
            class="page">

            <h2 class="page-title">
                Release / Forward Documents
            </h2>

            <table
                id="table-release"
                class="display nowrap"
                width="100%">

                <thead>

                    <tr>

                        <th>Tracking Code</th>

                        <th>Title</th>

                        <th>Type</th>

                        <th>Creator</th>

                        <th>Status</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($allDocs as $doc): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($doc["tracking_code"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["title"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["type_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["creator_name"]) ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($doc["status"]) ?>
                            </td>

                            <td>

                                <button
                                    type="button"
                                    class="btn-primary btn-sm btn-forward"
                                    data-id="<?= htmlspecialchars($doc["document_id"]) ?>"
                                    data-title="<?= htmlspecialchars($doc["title"]) ?>">

                                    Forward

                                </button>

                                <button
                                    type="button"
                                    class="btn-primary btn-sm btn-trail"
                                    data-id="<?= htmlspecialchars($doc["document_id"]) ?>">

                                    Trail

                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

        <!-- ====================================== -->
        <!-- DOCUMENT TYPES PAGE -->
        <!-- ====================================== -->

        <div
            id="page-types"
            class="page">

            <h2 class="page-title">
                My Office Document Types
            </h2>

            <button
                id="btn-add-type"
                type="button"
                class="btn-primary"
                style="margin-bottom: 16px;">

                <i class="fas fa-plus"></i>
                Add Type

            </button>

            <table
                id="table-types"
                class="display nowrap"
                width="100%">

                <thead>

                    <tr>

                        <th>Type Name</th>

                        <th>Actions</th>

                    </tr>

                </thead>

                <tbody>

                    <?php foreach ($documentTypes as $type): ?>

                        <tr>

                            <td>
                                <?= htmlspecialchars($type["type_name"]) ?>
                            </td>

                            <td style="display: flex; gap: 4px;">

                                <button
                                    type="button"
                                    class="btn-primary btn-sm btn-edit-type"
                                    data-id="<?= htmlspecialchars($type["type_id"]) ?>"
                                    data-name="<?= htmlspecialchars($type["type_name"]) ?>">

                                    Edit

                                </button>

                                <form
                                    method="POST"
                                    action="../controllers/SecretaryTypeActionController.php"
                                    onsubmit="return confirm('Delete this type?');">

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="delete">

                                    <input
                                        type="hidden"
                                        name="type_id"
                                        value="<?= htmlspecialchars($type["type_id"]) ?>">

                                    <button
                                        type="submit"
                                        class="btn-primary btn-sm">

                                        Delete

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    </main>

</div>

<!-- ====================================== -->
<!-- ASSIGN MODAL -->
<!-- ====================================== -->

<div
    id="modal-assign"
    class="modal-overlay">

    <div class="modal">

        <div class="modal-header">

            <h3>Assign Document</h3>

            <button
                class="modal-close"
                type="button"
                data-close="modal-assign">

                &times;

            </button>

        </div>

        <div class="modal-body">

            <form
                method="POST"
                action="../controllers/SecretaryAssignController.php">

                <input
                    type="hidden"
                    name="action"
                    value="assign">

                <input
                    id="assign-doc-id"
                    type="hidden"
                    name="document_id">

                <div class="form-field">

                    <label>Document</label>

                    <p
                        id="assign-doc-title"
                        class="static-field">

                    </p>

                </div>

                <div class="form-field">

                    <label>Select Members</label>

                    <select
                        name="member_ids[]"
                        multiple
                        required
                        style="min-height: 120px; width: 100%; border: 1px solid var(--gray-300); border-radius: 4px; padding: 8px;">

                        <?php foreach ($members as $m): ?>

                            <option value="<?= htmlspecialchars($m["user_id"]) ?>">
                                <?= htmlspecialchars($m["email"]) ?> (<?= htmlspecialchars($m["full_name"]) ?>)
                            </option>

                        <?php endforeach; ?>

                    </select>

                    <p style="font-size: 0.8rem; color: var(--gray-500); margin-top: 4px;">
                        Hold Ctrl (Windows) or Cmd (Mac) to select multiple members.
                    </p>

                </div>

                <button
                    type="submit"
                    class="btn-primary">

                    Confirm Assign

                </button>

            </form>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- FORWARD MODAL -->
<!-- ====================================== -->

<div
    id="modal-forward"
    class="modal-overlay">

    <div class="modal">

        <div class="modal-header">

            <h3>Forward Document</h3>

            <button
                class="modal-close"
                type="button"
                data-close="modal-forward">

                &times;

            </button>

        </div>

        <div class="modal-body">

            <form
                method="POST"
                action="../controllers/SecretaryForwardController.php">

                <input
                    type="hidden"
                    name="action"
                    value="forward">

                <input
                    id="forward-doc-id"
                    type="hidden"
                    name="document_id">

                <div class="form-field">

                    <label>Document</label>

                    <p
                        id="forward-doc-title"
                        class="static-field">

                    </p>

                </div>

                <div class="form-field">

                    <label>Select Target Office</label>

                    <select
                        name="office_id"
                        class="field-select"
                        required>

                        <option value="">-- Select Office --</option>

                        <?php foreach ($forwardableOffices as $office): ?>

                            <option value="<?= htmlspecialchars($office["office_id"]) ?>">
                                <?= htmlspecialchars($office["office_name"]) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <button
                    type="submit"
                    class="btn-primary">

                    Confirm Forward

                </button>

            </form>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- DOCUMENT TYPE MODAL -->
<!-- ====================================== -->

<div
    id="modal-type"
    class="modal-overlay">

    <div class="modal">

        <div class="modal-header">

            <h3 id="type-modal-title">
                Add Document Type
            </h3>

            <button
                class="modal-close"
                type="button"
                data-close="modal-type">

                &times;

            </button>

        </div>

        <div class="modal-body">

            <form
                method="POST"
                action="../controllers/SecretaryTypeActionController.php">

                <input
                    id="type-action"
                    type="hidden"
                    name="action"
                    value="add">

                <input
                    id="type-id"
                    type="hidden"
                    name="type_id">

                <div class="form-field">

                    <label>
                        Type Name <span class="required">*</span>
                    </label>

                    <input
                        id="type-name"
                        type="text"
                        name="type_name"
                        class="field-input"
                        placeholder="e.g. Internal Memo"
                        required>

                </div>

                <button
                    type="submit"
                    class="btn-primary">

                    Save

                </button>

            </form>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- PAPER TRAIL MODAL -->
<!-- ====================================== -->

<div
    id="modal-trail"
    class="modal-overlay">

    <div class="modal">

        <div class="modal-header">

            <h3>Paper Trail</h3>

            <button
                class="modal-close"
                type="button"
                data-close="modal-trail">

                &times;

            </button>

        </div>

        <div class="modal-body">

            <ul
                id="trail-list"
                class="trail-list">

            </ul>

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
                        <?= htmlspecialchars($user["full_name"] ?? $userFullName) ?>
                    </div>

                </div>

                <div class="admin-field">

                    <span>Email</span>

                    <div id="profileEmail" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        <?= htmlspecialchars($user["email"] ?? $userEmail) ?>
                    </div>

                </div>

                <div class="admin-field">

                    <span>Office</span>

                    <div id="profileOffice" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        <?= htmlspecialchars($user["office_name"] ?? $officeName) ?>
                    </div>

                </div>

                <div class="admin-field">

                    <span>Role</span>

                    <div id="profileRole" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        <?= htmlspecialchars($user["role_name"] ?? "Secretary") ?>
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

<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<script src="../js/secretary.js"></script>

</body>

</html>
