<?php

session_start();

if (!isset($_SESSION["user_id"]))
{
    header("Location: ../login.php");
    exit;
}

require_once "../controllers/MemberDashboardController.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>DocuFlow - Member Dashboard</title>

    <!-- Bootstrap 5 -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <!-- DataTables -->
    <link
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
        rel="stylesheet">
    <!-- Your CSS -->
    <link
        rel="stylesheet"
        href="../css/member-dashboard.css">

</head>

<body class="member-body">

<header class="member-header">

    <div class="header-left">

        <div class="logo-area">

            <span class="web-logo">

                DocuFlow

            </span>

        </div>

    </div>

    <div class="header-right">

        <div class="user-info">

            <span class="user-email">

                <?= htmlspecialchars($user["email"]) ?>

            </span>

            <span class="user-role">

                <?= htmlspecialchars($user["role_name"]) ?>

                -

                <?= htmlspecialchars($user["office_name"]) ?>

            </span>

        </div>

        <div class="header-actions">

            <button
                id="themeToggle"
                class="icon-btn toggle-theme"
                type="button">

                <i class="fas fa-moon"></i>

            </button>

            <a
                href="../controllers/logout.php"
                class="icon-btn logout-btn"
                title="Logout">

                <i class="fas fa-sign-out-alt"></i>

            </a>

        </div>

    </div>

</header>

<!-- ====================================== -->
<!-- MEMBER NAVBAR -->
<!-- ====================================== -->

<nav class="member-navbar">

    <div class="nav-left">

        <a href="member-dashboard.php"
           class="nav-link">

            <i class="fas fa-chart-line"></i>
            Dashboard

        </a>

        <a href="member-dashboard.php#documents-section"
           class="nav-link">

            <i class="fas fa-file-signature"></i>
            Pending Documents

        </a>

        <a href="member-dashboard.php#paperTrail-section"
           class="nav-link">

            <i class="fas fa-history"></i>
            Paper Trail

        </a>

        <a href="member-dashboard.php#submitRequestModal"
           class="nav-link">

            <i class="fas fa-plus-circle"></i>
            Submit Request

        </a>

        <a
            href="member-report.php"
            class="nav-link active">

            <i class="fas fa-chart-bar"></i>

            Reports

        </a>

    </div>

</nav>

<main class="member-main">

    <!-- Dashboard Statistics -->

    <section class="stats-row">

        <button
            class="stat-card"
            type="button">

            <span
                class="stat-number"
                id="pending-count">

                <?= $pending ?>

            </span>

            <span class="stat-label">

                Pending Documents

            </span>

        </button>

        <button
            class="stat-card"
            type="button">

            <span
                class="stat-number"
                id="signed-count">

                <?= $signed ?>

            </span>

            <span class="stat-label">

                Signed Documents

            </span>

        </button>

        <button
            class="stat-card"
            type="button">

            <span
                class="stat-number"
                id="finished-count">

                <?= $finished ?>

            </span>

            <span class="stat-label">

                Finished Documents

            </span>

        </button>

        <button
            class="stat-card"
            type="button">

            <span
                class="stat-number"
                id="request-count">

                <?= $requests ?>

            </span>

            <span class="stat-label">

                My Requests

            </span>

        </button>

    </section>

    <!-- Dashboard Grid -->

    <section class="dashboard-grid">

        <!-- Analytics -->

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

        <!-- Quick Actions -->

        <section class="panel-card">

            <div class="panel-header">

                <h2 class="section-title">

                    Quick Actions

                </h2>

            </div>

            <div class="quick-actions">

                <button
                    class="action-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#submitRequestModal">

                    <i class="fas fa-paper-plane"></i>

                    Submit Request

                </button>

                <button
                    class="action-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#uploadModal">

                    <i class="fas fa-upload"></i>

                    Upload Signed

                </button>

                <button
                    class="action-btn"
                    data-bs-toggle="modal"
                    data-bs-target="#profileModal">

                    <i class="fas fa-user"></i>

                    My Profile

                </button>

            </div>

        </section>

    </section>

    <!-- ====================================== -->
    <!-- Pending Documents -->
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

            <button
                class="view-all"
                type="button">

                View All

            </button>

        </div>

        <div class="table-responsive">

            <table
                id="documents-table"
                class="table table-hover align-middle">

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
                                class="btn btn-preview previewBtn"
                                data-file="<?= htmlspecialchars($doc["file_path"]) ?>">

                                <i class="fas fa-eye"></i>

                            </button>

                            <a
                                class="btn btn-download"
                                href="<?= htmlspecialchars($doc["file_path"]) ?>"
                                download>

                                <i class="fas fa-download"></i>

                            </a>

                            <button
                                class="btn btn-sign signBtn"
                                data-id="<?= $doc["document_id"] ?>">

                                <i class="fas fa-signature"></i>

                            </button>

                            <button
                                class="btn btn-reject rejectBtn"
                                data-id="<?= $doc["document_id"] ?>">

                                <i class="fas fa-times"></i>

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
    <!-- Document Preview -->
    <!-- ====================================== -->

    <div
        class="modal fade"
        id="previewModal"
        tabindex="-1">

        <div
            class="modal-dialog modal-xl">

            <div class="modal-content">

                <div class="modal-header">

                    <h5>

                        Preview Document

                    </h5>

                    <button
                        class="btn-close"
                        data-bs-dismiss="modal">

                    </button>

                </div>

                <div class="modal-body">

                    <iframe

                        id="previewFrame"

                        width="100%"

                        height="650"

                        style="border:none;">

                    </iframe>

                </div>

                <div class="modal-footer">

                    <a

                        id="downloadPreview"

                        class="btn btn-primary"

                        download>

                        <i class="fas fa-download"></i>

                        Download

                    </a>

                    <button

                        class="btn btn-secondary"

                        data-bs-dismiss="modal">

                        Close

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- ====================================== -->
    <!-- Sign Modal -->
    <!-- ====================================== -->

    <div
        class="modal fade"
        id="signModal"
        tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-success text-white">

                    <h5>

                        Sign Document

                    </h5>

                    <button
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                    </button>

                </div>

                <div class="modal-body">

                    <label>

                        Remarks (Optional)

                    </label>

                    <textarea

                        id="signRemarks"

                        class="form-control"

                        rows="5">

                    </textarea>

                </div>

                <div class="modal-footer">

                    <button

                        id="confirmSign"

                        class="btn btn-success">

                        <i class="fas fa-signature"></i>

                        Sign Document

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- ====================================== -->
    <!-- Reject Modal -->
    <!-- ====================================== -->

    <div
        class="modal fade"
        id="rejectModal"
        tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-danger text-white">

                    <h5>

                        Reject Document

                    </h5>

                    <button
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                    </button>

                </div>

                <div class="modal-body">

                    <label>

                        Reason

                    </label>

                    <textarea

                        id="rejectReason"

                        class="form-control"

                        rows="5"

                        required>

                    </textarea>

                </div>

                <div class="modal-footer">

                    <button

                        id="confirmReject"

                        class="btn btn-danger">

                        Reject

                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- ====================================== -->
    <!-- Upload Signed Document -->
    <!-- ====================================== -->

    <div
        class="modal fade"
        id="uploadModal"
        tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5>

                        Upload Signed Document

                    </h5>

                    <button
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                    </button>

                </div>

                <div class="modal-body">

                    <input
                        type="file"
                        id="signedFile"
                        class="form-control"
                        accept=".pdf">

                </div>

                <div class="modal-footer">

                    <button

                        id="uploadSigned"

                        class="btn btn-primary">

                        Upload

                    </button>

                </div>

            </div>

        </div>

    </div>

        <!-- ====================================== -->
    <!-- Paper Trail -->
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
                class="table table-hover align-middle">

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

                        <?= date("M d, Y h:i A", strtotime($row["created_at"])) ?>

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

    <section 
    id="requests-section"
    class="panel-card mt-4">

        <div class="panel-header">

            <h2 class="section-title">

                My Requests

            </h2>

        </div>


        <div class="table-responsive">

            <table class="table">

                <thead>

                    <tr>

                        <th>Title</th>

                        <th>Type</th>

                        <th>Status</th>

                        <th>Action</th>

                    </tr>

                </thead>


                <tbody>

                <?php foreach($requestsList as $request): ?>

                    <tr>

                        <td>
                            <?= htmlspecialchars($request["title"]) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($request["type_name"]) ?>
                        </td>


                        <td>
                            <?= htmlspecialchars($request["status"]) ?>
                        </td>


                        <td>

                            <button
                            class="btn btn-danger btn-sm deleteRequest"
                            data-id="<?= $request["request_id"] ?>">

                                <i class="fas fa-trash"></i>

                                Delete

                            </button>

                        </td>

                    </tr>

                <?php endforeach; ?>


                </tbody>

            </table>

        </div>

    </section>

    <!-- ====================================== -->
    <!-- Submit Request -->
    <!-- ====================================== -->

    <div
        class="modal fade"
        id="submitRequestModal"
        tabindex="-1">

        <div class="modal-dialog modal-lg">

            <div class="modal-content">

                <div class="modal-header bg-primary text-white">

                    <h5>

                        Submit Document Request

                    </h5>

                    <button
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                    </button>

                </div>

                <form id="requestForm">

                    <div class="modal-body">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Request Title

                                </label>

                                <input
                                    type="text"
                                    id="requestTitle"
                                    class="form-control"
                                    required>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">

                                    Document Type

                                </label>

                                <select
                                    id="requestType"
                                    name="type_id"
                                    class="form-select"
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

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Description

                            </label>

                            <textarea
                                id="requestDescription"
                                class="form-control"
                                rows="5"></textarea>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">

                                Secretary Email

                            </label>

                            <input
                                type="email"
                                id="secretaryEmail"
                                class="form-control"
                                placeholder="secretary@docuflow.com"
                                required>

                            <small class="text-muted">

                                Must contain @ and .com

                            </small>

                        </div>

                    </div>

                    <div class="modal-footer">

                        <button
                            type="submit"
                            class="btn btn-primary">

                            <i class="fas fa-paper-plane"></i>

                            Submit Request

                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

    <!-- ====================================== -->
    <!-- My Profile -->
    <!-- ====================================== -->

    <div
        class="modal fade"
        id="profileModal"
        tabindex="-1">

        <div class="modal-dialog">

            <div class="modal-content">

                <div class="modal-header bg-secondary text-white">

                    <h5>

                        My Profile

                    </h5>

                    <button
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal">

                    </button>

                </div>

                <div class="modal-body">

                    <table class="table">

                        <tr>

                            <th>Name</th>

                            <td id="profileName">

                                <?= htmlspecialchars($user["full_name"]) ?>

                            </td>

                        </tr>

                        <tr>

                            <th>Email</th>

                            <td id="profileEmail">

                                 <?= htmlspecialchars($user["email"]) ?>

                            </td>

                        </tr>

                        <tr>

                            <th>Office</th>

                            <td id="profileOffice">

                                <?= htmlspecialchars($user["office_name"]) ?>

                            </td>

                        </tr>

                        <tr>

                            <th>Role</th>

                            <td>

                                <?= htmlspecialchars($user["role_name"]) ?>

                            </td>

                        </tr>

                    </table>

                </div>

            </div>

        </div>

    </div>

</main>

<!-- ====================================== -->
<!-- JavaScript Libraries -->
<!-- ====================================== -->

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script> const chartData = <?= json_encode($chartData) ?>; </script>

<script src="../js/member-dashboard.js"></script>

</body>

</html>