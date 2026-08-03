<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"]))
{
    header("Location: ../views/login.php");
    exit;
}

require_once "../controllers/MemberReportController.php";

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0">

    <title>DocuFlow - Member Reports</title>

    <!-- DataTables -->
    <link
        href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css"
        rel="stylesheet">

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Custom CSS -->
    <link
        rel="stylesheet"
        href="../css/admin-dashboard.css?v=<?= time() ?>">

    <link
        rel="stylesheet"
        href="../css/member-report.css?v=<?= time() ?>">

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
                   class="header-nav-item">

                    Dashboard

                </a>

                <a href="member-report.php"
                   class="header-nav-item active">

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
        <!-- REPORT STATISTICS -->
        <!-- ====================================== -->

        <section class="stats-row">

            <div class="stat-card">
                <span id="totalRouteSteps" class="stat-number"><?= (int) $totalRouteSteps ?></span>
                <span class="stat-label">Total Route Steps</span>
            </div>

            <div class="stat-card">
                <span id="rejectedRoutes" class="stat-number"><?= (int) $rejectedRoutes ?></span>
                <span class="stat-label">Rejected Routes</span>
            </div>

            <div class="stat-card">
                <span id="totalDocuments" class="stat-number"><?= (int) $totalDocuments ?></span>
                <span class="stat-label">Total Documents</span>
            </div>

            <div class="stat-card">
                <span id="completedRoutes" class="stat-number"><?= (int) $finishedDocuments ?></span>
                <span class="stat-label">Completed Routes</span>
            </div>

        </section>

        <!-- ====================================== -->
        <!-- CHARTS GRID -->
        <!-- ====================================== -->

        <section class="report-chart-grid">

            <div class="panel-card chart-panel chart-panel-wide">
                <div class="panel-header">
                    <div>
                        <h2 class="section-title">Office Route Trends</h2>
                        <small>Each line represents one office. IT/ITS is red and HR is green.</small>
                    </div>
                </div>
                <div class="panel-body chart-body">
                    <canvas id="officeLineChart"></canvas>
                </div>
            </div>

            <div class="panel-card chart-panel">
                <div class="panel-header">
                    <div>
                        <h2 class="section-title">Route Status Distribution</h2>
                        <small>All assigned route steps grouped by current status.</small>
                    </div>
                </div>
                <div class="panel-body chart-body">
                    <canvas id="routeStatusPieChart"></canvas>
                </div>
            </div>

        </section>

        <!-- ====================================== -->
        <!-- QUICK EXPORT ACTIONS -->
        <!-- ====================================== -->

        <section class="panel-card quick-report-card">
            <div class="panel-header">
                <h2 class="section-title">Quick Report</h2>
            </div>
            <div class="panel-body quick-report-actions">
                <button id="downloadPDF" class="action-btn" type="button">
                    Export / Print PDF
                </button>
                <button id="downloadCSV" class="action-btn" type="button">
                    Export CSV
                </button>
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
                        class="btn-small submit-btn"
                        type="button"
                        id="refreshReport">

                        Refresh

                    </button>

                </div>

            </div>

            <div class="table-responsive">

                <table
                    id="reportTable"
                    class="display"
                    style="width: 100%;">

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

                <div class="filter-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">

                    <div class="admin-field">

                        <span>Status</span>

                        <select
                            id="statusFilter"
                            class="admin-select">

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

                    <div class="admin-field">

                        <span>Document Type</span>

                        <select
                            id="typeFilter"
                            class="admin-select">

                            <option value="">
                                All Types
                            </option>

                        </select>

                    </div>

                    <div class="admin-field">

                        <span>Date</span>

                        <input
                            type="date"
                            id="dateFilter"
                            class="admin-input">

                    </div>

                </div>

            </div>

        </section>

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

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 16px; text-align: center;">

                    <div>

                        <h3 id="summaryTotal" style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">0</h3>
                        <p style="color: var(--gray-500); font-size: 0.875rem;">Total Route Steps</p>

                    </div>

                    <div>

                        <h3 id="summaryPending" style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">
                            0
                        </h3>

                        <p style="color: var(--gray-500); font-size: 0.875rem;">
                            Pending
                        </p>

                    </div>

                    <div>

                        <h3 id="summarySigned" style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">
                            0
                        </h3>

                        <p style="color: var(--gray-500); font-size: 0.875rem;">
                            Signed
                        </p>

                    </div>

                    <div>

                        <h3 id="summaryRejected" style="font-size: 1.75rem; font-weight: 700; color: var(--primary);">0</h3>
                        <p style="color: var(--gray-500); font-size: 0.875rem;">Rejected</p>

                    </div>

                </div>

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

                Document Preview

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
                id="downloadDocument"
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
<!-- JAVASCRIPT -->
<!-- ====================================== -->

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="../js/member-report.js?v=<?= time() ?>"></script>

</body>

</html>