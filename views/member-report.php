<?php

session_start();

if(!isset($_SESSION["user_id"]))
{
    header("Location: ../login.php");
    exit;
}

require_once "../controllers/MemberReportController.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>

        DocuFlow | Member Reports

    </title>

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
        href="../css/member-report.css">

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

                <?php if (!empty($user["office_name"])): ?>
                    - <?= htmlspecialchars($user["office_name"]) ?>
                <?php endif; ?>

            </span>

        </div>

        <div class="header-actions">

            <button
                id="themeToggle"
                class="icon-btn toggle-theme"
                type="button"
                title="Toggle dark mode">

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

        <a
            href="member-dashboard.php"
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

        <a href="member-report.php"
           class="nav-link active">

            <i class="fas fa-file-chart-column"></i>
            Reports

        </a>

    </div>

</nav>

<main class="member-main">

<section class="stats-row">

    <button class="stat-card">

        <span
            id="totalDocuments"
            class="stat-number">

            0

        </span>

        <span class="stat-label">

            Total Documents

        </span>

    </button>

    <button class="stat-card">

        <span
            id="pendingDocuments"
            class="stat-number">

            0

        </span>

        <span class="stat-label">

            Pending

        </span>

    </button>

    <button class="stat-card">

        <span
            id="signedDocuments"
            class="stat-number">

            0

        </span>

        <span class="stat-label">

            Signed

        </span>

    </button>

    <button class="stat-card">

        <span
            id="finishedDocuments"
            class="stat-number">

            0

        </span>

        <span class="stat-label">

            Finished

        </span>

    </button>

</section>

<section class="dashboard-grid">

<div class="panel-card">

<div class="panel-header">

<h2 class="section-title">

Document Statistics

</h2>

</div>

<div class="panel-body">

<canvas
    id="reportChart">

</canvas>

</div>

</div>

<div class="panel-card">

<div class="panel-header">

<h2 class="section-title">

Quick Report

</h2>

</div>

<div class="panel-body">

<button
    id="downloadPDF"
    class="action-btn">

<i class="fas fa-file-pdf"></i>

Export PDF

</button>

<button
    id="downloadCSV"
    class="action-btn mt-3">

<i class="fas fa-file-csv"></i>

Export CSV

</button>

</div>

</div>

</section>

<!-- ====================================== -->
<!-- REPORT TABLE -->
<!-- ====================================== -->

<section class="panel-card mt-4">

    <div class="panel-header">

        <div>

            <h2 class="section-title">

                Document Report

            </h2>

            <small>

                View all documents assigned to you

            </small>

        </div>

        <div class="report-actions">

            <button
                class="btn btn-primary"
                id="refreshReport">

                <i class="fas fa-rotate"></i>

                Refresh

            </button>

        </div>

    </div>

    <div class="table-responsive">

        <table
            id="reportTable"
            class="table table-hover align-middle">

            <thead>

                <tr>

                    <th>Tracking Code</th>

                    <th>Title</th>

                    <th>Document Type</th>

                    <th>Office</th>

                    <th>Date</th>

                    <th>Status</th>

                    <th width="220">

                        Actions

                    </th>

                </tr>

            </thead>

            <tbody>

                <!--

                Loaded from MySQL

                tracking_code
                title
                type_name
                office_name
                created_at
                status

                -->

            </tbody>

        </table>

    </div>

</section>

<!-- ====================================== -->
<!-- FILTERS -->
<!-- ====================================== -->

<section class="panel-card mt-4">

    <div class="panel-header">

        <h2 class="section-title">

            Report Filters

        </h2>

    </div>

    <div class="panel-body">

        <div class="row">

            <div class="col-md-4">

                <label>

                    Status

                </label>

                <select
                    id="statusFilter"
                    class="form-select">

                    <option value="">

                        All

                    </option>

                    <option value="Pending">

                        Pending

                    </option>

                    <option value="Signed">

                        Signed

                    </option>

                    <option value="Finished">

                        Finished

                    </option>

                </select>

            </div>

            <div class="col-md-4">

                <label>

                    Document Type

                </label>

                <select
                    id="typeFilter"
                    class="form-select">

                    <option value="">

                        All Types

                    </option>

                </select>

            </div>

            <div class="col-md-4">

                <label>

                    Date

                </label>

                <input
                    type="date"
                    id="dateFilter"
                    class="form-control">

            </div>

        </div>

    </div>

</section>

<!-- ====================================== -->
<!-- PREVIEW MODAL -->
<!-- ====================================== -->

<div
    class="modal fade"
    id="previewModal"
    tabindex="-1">

    <div class="modal-dialog modal-xl">

        <div class="modal-content">

            <div class="modal-header">

                <h5>

                    Document Preview

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

                    id="downloadDocument"

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
<!-- REPORT SUMMARY -->
<!-- ====================================== -->

<section class="panel-card mt-4">

    <div class="panel-header">

        <h2 class="section-title">

            Report Summary

        </h2>

    </div>

    <div class="panel-body">

        <div class="row text-center">

            <div class="col-md-3">

                <h3 id="summaryTotal">

                    0

                </h3>

                <p>

                    Total Documents

                </p>

            </div>

            <div class="col-md-3">

                <h3 id="summaryPending">

                    0

                </h3>

                <p>

                    Pending

                </p>

            </div>

            <div class="col-md-3">

                <h3 id="summarySigned">

                    0

                </h3>

                <p>

                    Signed

                </p>

            </div>

            <div class="col-md-3">

                <h3 id="summaryFinished">

                    0

                </h3>

                <p>

                    Finished

                </p>

            </div>

        </div>

    </div>

</section>

</main>

<!-- ====================================== -->
<!-- JAVASCRIPT -->
<!-- ====================================== -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Your JavaScript -->
<script src="../js/member-report.js"></script>

</body>

</html>