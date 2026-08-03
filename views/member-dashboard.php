<?php

session_start();

if (!isset($_SESSION["user_id"]))
{
    header("Location: ../views/login.php");
    exit;
}

require_once "../controllers/MemberDashboardController.php";

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

    <title>DocuFlow - Member Dashboard</title>

    <!-- DataTables -->
    <link
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">

    <!-- Custom CSS -->
    <link
        rel="stylesheet"
        href="../css/admin-dashboard.css?v=<?= time() ?>">

    <link
        rel="stylesheet"
        href="../css/member-dashboard.css?v=<?= time() ?>">

</head>

<body class="admin-body">

<div class="admin-layout">

    <!-- ====================================== -->
    <!-- NAVIGATION HEADER -->
    <!-- ====================================== -->

    <header class="admin-header">

        <div class="header-left">

            <a href="member-dashboard.php"
               class="logo-area">

                <span class="web-logo">
                    DocuFlow
                </span>

            </a>

            <nav class="header-nav">

                <a href="member-dashboard.php"
                   class="header-nav-item active">

                    Dashboard

                </a>

                <a href="member-report.php"
                   class="header-nav-item">

                    Reports

                </a>

            </nav>

        </div>

        <div class="header-right">

            <div class="user-info">

                <span class="user-role">
                    Member - <?= htmlspecialchars($user["office_name"] ?? "") ?>
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
        <!-- DASHBOARD STATISTICS -->
        <!-- ====================================== -->

        <section class="stats-row">

            <div class="stat-card">

                <span
                    class="stat-number"
                    id="pending-count">

                    <?= $pending ?>

                </span>

                <span class="stat-label">

                    Pending Documents

                </span>

            </div>

            <div class="stat-card">

                <span
                    class="stat-number"
                    id="signed-count">

                    <?= $signed ?>

                </span>

                <span class="stat-label">

                    Signed Documents

                </span>

            </div>

            <div class="stat-card">

                <span
                    class="stat-number"
                    id="finished-count">

                    <?= $finished ?>

                </span>

                <span class="stat-label">

                    Finished Documents

                </span>

            </div>

            <div class="stat-card">

                <span
                    class="stat-number"
                    id="request-count">

                    <?= $requests ?>

                </span>

                <span class="stat-label">

                    My Requests

                </span>

            </div>

        </section>

        <!-- ====================================== -->
        <!-- DASHBOARD GRID -->
        <!-- ====================================== -->

        <section class="dashboard-grid">

            <!-- Analytics Panel -->

            <section class="panel-card member-chart-panel">

                <div class="panel-header">

                    <h2 class="section-title">

                        Document Analytics

                    </h2>

                </div>

                <div class="member-chart-content">

                    <canvas
                        id="documentChart">

                    </canvas>

                </div>

            </section>

            <!-- Quick Actions Panel -->

            <section class="panel-card">

                <div class="panel-header">

                    <h2 class="section-title">

                        Quick Actions

                    </h2>

                </div>

                <div class="quick-actions">

                    <button
                        class="action-btn"
                        type="button"
                        onclick="openModal('submitRequestModal')">

                        Submit Request

                    </button>

                    <button
                        class="action-btn"
                        type="button"
                        onclick="document.getElementById('documents-section').scrollIntoView({ behavior: 'smooth' })">

                        Select Document to Upload

                    </button>

                    <button
                        class="action-btn"
                        type="button"
                        onclick="openModal('profileModal')">

                        My Profile

                    </button>

                </div>

            </section>

        </section>

        <!-- ====================================== -->
        <!-- PENDING DOCUMENTS -->
        <!-- ====================================== -->

        <section 
            id="documents-section"
            class="panel-card documents-panel mt-4">

            <div class="panel-header">

                <div>

                    <h2 class="section-title">

                        Pending Documents

                    </h2>

                    <small>

                        Documents waiting for your signature

                    </small>

                </div>

            </div>

            <div class="table-responsive">

                <table
                    id="documents-table"
                    class="display"
                    style="width: 100%;">

                    <thead>

                        <tr>

                            <th>Tracking Code</th>

                            <th>Title</th>

                            <th>Type</th>

                            <th>Office</th>

                            <th>Status</th>

                            <th width="280">

                                Actions

                            </th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach($documents as $doc): ?>

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

                            <?= htmlspecialchars($doc["office_name"]) ?>

                        </td>

                        <td>

                            <span class="status-badge status-pending">

                                <?= htmlspecialchars($doc["status"]) ?>

                            </span>

                        </td>

                        <td>

                            <div class="action-buttons">

                                <button
                                    class="btn-small previewBtn"
                                    type="button"
                                    data-file="<?= htmlspecialchars($doc["file_path"]) ?>">

                                    Preview

                                </button>

                                <a
                                    class="btn-small action-btn"
                                    href="<?= htmlspecialchars($doc["file_path"]) ?>"
                                    download
                                    style="text-decoration: none;">

                                    Download

                                </a>

                                <button
                                    class="btn-small signBtn"
                                    type="button"
                                    data-id="<?= $doc["document_id"] ?>">

                                    Sign

                                </button>

                                <button
                                    class="btn-small rejectBtn"
                                    type="button"
                                    data-id="<?= $doc["document_id"] ?>">

                                    Reject

                                </button>

                            </div>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- ====================================== -->
        <!-- PAPER TRAIL -->
        <!-- ====================================== -->

        <section 
            id="paperTrail-section"
            class="panel-card mt-4">

            <div class="panel-header">

                <div>

                    <h2 class="section-title">

                        Paper Trail

                    </h2>

                    <small>

                        Document history and activity log

                    </small>

                </div>

            </div>

            <div class="table-responsive">

                <table
                    id="paperTrailTable"
                    class="display"
                    style="width: 100%;">

                    <thead>

                        <tr>

                            <th>Date</th>

                            <th>Action</th>

                            <th>Performed By</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php foreach ($trail as $row): ?>

                    <tr>

                        <td>

                            <?= !empty($row["created_at"]) ? date("M d, Y h:i A", strtotime($row["created_at"])) : "N/A" ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row["action_taken"]) ?>

                        </td>

                        <td>

                            <?= htmlspecialchars($row["full_name"]) ?>

                        </td>

                        <td>

                            <?php

                            $statusClass = "status-pending";

                            if ($row["status"] == "Completed") {

                                $statusClass = "status-finished";

                            }

                            elseif ($row["status"] == "Signed") {

                                $statusClass = "status-signed";

                            }

                            elseif ($row["status"] == "Rejected") {

                                $statusClass = "status-rejected";

                            }

                            ?>

                            <span class="status-badge <?= $statusClass ?>">

                                <?= htmlspecialchars($row["status"]) ?>

                            </span>

                        </td>

                    </tr>

                    <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        </section>

        <!-- ====================================== -->
        <!-- MY REQUESTS -->
        <!-- ====================================== -->

        <section 
            id="requests-section"
            class="panel-card mt-4">

            <div class="panel-header">

                <h2 class="section-title">

                    My Requests

                </h2>

            </div>

            <div class="table-responsive">

                <table class="display" style="width: 100%;">

                    <thead>

                        <tr>

                            <th>Title</th>

                            <th>Type</th>

                            <th>Status</th>

                            <th>Action</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (!empty($requestsList)): ?>

                        <?php foreach ($requestsList as $request): ?>

                            <tr>
                                <td>
                                    <?= htmlspecialchars($request["title"]) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request["type_name"] ?? "Unknown") ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars($request["status"]) ?>
                                </td>

                                <td>
                                    <button
                                        class="btn-small danger-btn deleteRequest"
                                        type="button"
                                        data-id="<?= (int) $request["request_id"] ?>">

                                        Delete

                                    </button>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--gray-500);">
                                No requests found.
                            </td>
                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </section>

    </main>

</div>

<!-- ====================================== -->
<!-- PREVIEW MODAL -->
<!-- ====================================== -->

<div
    id="previewModal"
    class="modal-overlay">

    <div
        class="modal-content modal-xl"
        style="max-width: 900px; width: 95%;">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Preview Document

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('previewModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <div class="modal-body">

            <iframe
                id="previewFrame"
                width="100%"
                height="600"
                style="border: none; border-radius: var(--radius-sm);">

            </iframe>

        </div>

        <div
            class="modal-footer"
            style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">

            <a
                id="downloadPreview"
                class="btn-small action-btn"
                style="display: inline-flex; align-items: center; gap: 6px; text-decoration: none;"
                download>

                Download

            </a>

            <button
                class="btn-small cancel-btn"
                type="button"
                onclick="closeModal('previewModal')">

                Close

            </button>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- SIGN MODAL -->
<!-- ====================================== -->

<div
    id="signModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Sign Document

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('signModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <div class="modal-body">

            <div class="admin-field">

                <span>Remarks (Optional)</span>

                <textarea
                    id="signRemarks"
                    class="admin-textarea"
                    rows="4"
                    placeholder="Enter remarks...">

                </textarea>

            </div>

        </div>

        <div
            class="modal-footer"
            style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">

            <button
                class="btn-small cancel-btn"
                type="button"
                onclick="closeModal('signModal')">

                Cancel

            </button>

            <button
                id="confirmSign"
                class="btn-small submit-btn"
                type="button"
                style="background: var(--accent-green); color: white;">

                Sign Document

            </button>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- REJECT MODAL -->
<!-- ====================================== -->

<div
    id="rejectModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0; color: var(--accent-red);">

                Reject Document

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('rejectModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <div class="modal-body">

            <div class="admin-field">

                <span>Reason</span>

                <textarea
                    id="rejectReason"
                    class="admin-textarea"
                    rows="4"
                    placeholder="Enter reason..."
                    required>

                </textarea>

            </div>

        </div>

        <div
            class="modal-footer"
            style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">

            <button
                class="btn-small cancel-btn"
                type="button"
                onclick="closeModal('rejectModal')">

                Cancel

            </button>

            <button
                id="confirmReject"
                class="btn-small danger-btn"
                type="button"
                style="background: var(--accent-red); color: white;">

                Reject

            </button>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- UPLOAD SIGNED DOCUMENT MODAL -->
<!-- ====================================== -->

<div
    id="uploadModal"
    class="modal-overlay">

    <div class="modal-content">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Upload Signed Document

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('uploadModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <div class="modal-body">

            <div class="admin-field">

                <span>Select PDF File</span>

                <input
                    type="file"
                    id="signedFile"
                    class="admin-input"
                    accept=".pdf">

            </div>

        </div>

        <div
            class="modal-footer"
            style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">

            <button
                class="btn-small cancel-btn"
                type="button"
                onclick="closeModal('uploadModal')">

                Cancel

            </button>

            <button
                id="uploadSigned"
                class="btn-small submit-btn"
                type="button">

                Upload

            </button>

        </div>

    </div>

</div>

<!-- ====================================== -->
<!-- SUBMIT REQUEST MODAL -->
<!-- ====================================== -->

<div
    id="submitRequestModal"
    class="modal-overlay">

    <div
        class="modal-content"
        style="max-width: 550px;">

        <div class="modal-header">

            <h3
                class="card-title"
                style="margin:0;">

                Submit Document Request

            </h3>

            <button
                class="close-btn icon-btn"
                type="button"
                onclick="closeModal('submitRequestModal')">

                <i class="fas fa-times"></i>

            </button>

        </div>

        <form id="requestForm" class="admin-form">

            <div
                class="modal-body"
                style="display: grid; gap: 16px;">

                <div class="admin-field">

                    <span>Request Title</span>

                    <input
                        type="text"
                        id="requestTitle"
                        class="admin-input"
                        required>

                </div>

                <div class="admin-field">

                    <span>Document Type</span>

                    <select
                        id="requestType"
                        name="type_id"
                        class="admin-select"
                        required>

                        <option value="">
                            Select Type
                        </option>

                        <?php foreach ($types as $type): ?>

                            <option value="<?= $type["type_id"] ?>">
                                <?= htmlspecialchars($type["type_name"]) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

                <div class="admin-field">

                    <span>Description</span>

                    <textarea
                        id="requestDescription"
                        class="admin-textarea"
                        rows="4"></textarea>

                </div>

                <div class="admin-field">

                    <span>Secretary Email</span>

                    <input
                        type="email"
                        id="secretaryEmail"
                        class="admin-input"
                        placeholder="secretary@docuflow.com"
                        required>

                    <small style="color: var(--gray-500); font-size: 0.8rem;">
                        Must contain @ and .com
                    </small>

                </div>

            </div>

            <div
                class="modal-footer"
                style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 16px;">

                <button
                    class="btn-small cancel-btn"
                    type="button"
                    onclick="closeModal('submitRequestModal')">

                    Cancel

                </button>

                <button
                    type="submit"
                    class="btn-small submit-btn">

                    Submit Request

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
                        <?= htmlspecialchars($user["full_name"] ?? "") ?>
                    </div>

                </div>

                <div class="admin-field">

                    <span>Email</span>

                    <div id="profileEmail" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        <?= htmlspecialchars($user["email"] ?? "") ?>
                    </div>

                </div>

                <div class="admin-field">

                    <span>Office</span>

                    <div id="profileOffice" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        <?= htmlspecialchars($user["office_name"] ?? "") ?>
                    </div>

                </div>

                <div class="admin-field">

                    <span>Role</span>

                    <div id="profileRole" style="padding: 8px 12px; background: var(--gray-100); border-radius: var(--radius-sm); font-weight: 500;">
                        <?= htmlspecialchars($user["role_name"] ?? "") ?>
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
<!-- JAVASCRIPT LIBRARIES -->
<!-- ====================================== -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script> const chartData = <?= json_encode($chartData) ?>; </script>

<script src="../js/member-dashboard.js?v=<?= time() ?>"></script>

</body>

</html>