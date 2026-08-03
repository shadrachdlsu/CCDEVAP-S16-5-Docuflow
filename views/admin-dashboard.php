<?php

require_once "../controllers/AdminDashboardController.php";

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

    <title>DocuFlow - Admin Dashboard</title>

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

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
                   class="header-nav-item active">

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
        <!-- KPI CARDS GRID -->
        <!-- ====================================== -->

        <section class="dashboard-row kpi-grid">

            <div class="kpi-card">

                <div class="kpi-header">

                    <span class="kpi-badge badge-warning-subtle">
                        Pending
                    </span>

                </div>

                <div class="kpi-body">

                    <span
                        id="kpi-pending-actions"
                        class="kpi-value">

                        <?= number_format($kpi_data["pending_actions"]) ?>

                    </span>

                    <span class="kpi-label">
                        Pending Actions
                    </span>

                </div>

            </div>

            <div class="kpi-card">

                <div class="kpi-header">

                    <span class="kpi-badge badge-danger-subtle">
                        Delayed
                    </span>

                </div>

                <div class="kpi-body">

                    <span
                        id="kpi-stalled-docs"
                        class="kpi-value">

                        <?= number_format($kpi_data["stalled_docs"]) ?>

                    </span>

                    <span class="kpi-label">
                        Stalled Documents
                    </span>

                </div>

            </div>

            <div class="kpi-card">

                <div class="kpi-header">

                    <span class="kpi-badge badge-success-subtle">
                        Active
                    </span>

                </div>

                <div class="kpi-body">

                    <span class="kpi-value">

                        <?= number_format($kpi_data["active_users"]) ?>

                    </span>

                    <span class="kpi-label">
                        Active Users
                    </span>

                </div>

            </div>

            <div class="kpi-card">

                <div class="kpi-header">

                    <span class="kpi-badge badge-info-subtle">
                        Total
                    </span>

                </div>

                <div class="kpi-body">

                    <span class="kpi-value">

                        <?= number_format($kpi_data["total_docs"]) ?>

                    </span>

                    <span class="kpi-label">
                        Total Documents
                    </span>

                </div>

            </div>

        </section>

        <!-- ====================================== -->
        <!-- ACTION CENTERS GRID -->
        <!-- ====================================== -->

        <section class="dashboard-row action-centers-grid">

            <!-- Pending Registrations Panel -->
            <div class="card action-card-panel">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Pending Registrations
                        </h2>

                        <p class="card-subtitle">
                            Review and authorize new user registration requests
                        </p>

                    </div>

                    <span
                        id="pending-users-count"
                        class="count-pill">

                        <?= count($pending_users) ?>

                    </span>

                </div>

                <div
                    id="pending-users-list"
                    class="card-content">

                    <?php if (!empty($pending_users)): ?>

                        <?php foreach ($pending_users as $user): ?>

                            <div
                                class="user-row"
                                data-id="<?= $user["id"] ?>">

                                <div class="user-details">

                                    <strong class="user-name">
                                        <?= htmlspecialchars($user["name"]) ?>
                                    </strong>

                                    <span class="user-email">
                                        <?= htmlspecialchars($user["email"]) ?>
                                    </span>

                                    <div class="user-tags">

                                        <span class="tag tag-role">
                                            <?= htmlspecialchars($user["role"]) ?>
                                        </span>

                                        <span class="tag tag-office">
                                            <?= htmlspecialchars($user["office"]) ?>
                                        </span>

                                    </div>

                                </div>

                                <div class="user-actions">

                                    <button
                                        type="button"
                                        class="btn-action btn-approve"
                                        data-id="<?= $user["id"] ?>"
                                        title="Approve Registration">

                                        Approve

                                    </button>

                                    <button
                                        type="button"
                                        class="btn-action btn-reject"
                                        data-id="<?= $user["id"] ?>"
                                        title="Reject Registration">

                                        Reject

                                    </button>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    <?php else: ?>

                        <div class="empty-state-success">

                            <p>No pending registrations.</p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

            <!-- Stalled Documents Panel -->
            <div class="card action-card-panel">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Stalled Documents
                        </h2>

                        <p class="card-subtitle">
                            Documents delayed in routing for more than 48 hours
                        </p>

                    </div>

                    <span class="count-pill pill-danger">
                        <?= count($stalled_docs) ?>
                    </span>

                </div>

                <div class="card-content">

                    <?php if (!empty($stalled_docs)): ?>

                        <div class="stalled-list">

                            <?php foreach ($stalled_docs as $doc): ?>

                                <div class="stalled-row">

                                    <div class="stalled-details">

                                        <strong class="stalled-title">
                                            <?= htmlspecialchars($doc["title"]) ?>
                                        </strong>

                                        <span class="stalled-office">
                                            Current: <?= htmlspecialchars($doc["current_office"]) ?>
                                        </span>

                                    </div>

                                    <span class="warning-badge">

                                        <?= (int) $doc["days_stalled"] ?> days stalled

                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php else: ?>

                        <div class="empty-state-success">

                            <p>No stalled documents.</p>

                        </div>

                    <?php endif; ?>

                </div>

            </div>

        </section>

        <!-- ====================================== -->
        <!-- ANALYTICS GRID -->
        <!-- ====================================== -->

        <section class="dashboard-row analytics-grid">

            <!-- Office Bottlenecks Card -->
            <div class="card analytics-card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Office Bottlenecks
                        </h2>

                        <p class="card-subtitle">
                            Pending document workload distribution by office
                        </p>

                    </div>

                </div>

                <div class="card-content">

                    <div class="chart-canvas-container">

                        <canvas id="bottlenecksChart"></canvas>

                    </div>

                </div>

            </div>

            <!-- Processing Volume Card -->
            <div class="card analytics-card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Processing Volume
                        </h2>

                        <p class="card-subtitle">
                            30-day trend of document creation and routing throughput
                        </p>

                    </div>

                    <span class="trend-summary-pill">
                        <?= number_format($total_30_days) ?> Total (30d)
                    </span>

                </div>

                <div class="card-content">

                    <div class="chart-canvas-container">

                        <canvas id="volumeChart"></canvas>

                    </div>

                    <div class="trend-stats-row">

                        <div class="trend-stat-box">

                            <span class="trend-stat-num">
                                <?= number_format($total_30_days) ?>
                            </span>

                            <span class="trend-stat-lbl">
                                30-Day Volume
                            </span>

                        </div>

                        <div class="trend-stat-box">

                            <span class="trend-stat-num">
                                <?= number_format($max_daily) ?>
                            </span>

                            <span class="trend-stat-lbl">
                                Daily Peak
                            </span>

                        </div>

                        <div class="trend-stat-box">

                            <span class="trend-stat-num">
                                <?= round($total_30_days / 30, 1) ?>
                            </span>

                            <span class="trend-stat-lbl">
                                Avg / Day
                            </span>

                        </div>

                    </div>

                </div>

            </div>

            <!-- Average Office Turnaround Time Card -->
            <div class="card analytics-card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Average Office Turnaround Time
                        </h2>

                        <p class="card-subtitle">
                            Average document processing duration per office (in hours)
                        </p>

                    </div>

                </div>

                <div class="card-content">

                    <div class="chart-canvas-container">

                        <canvas id="avgTimeChart"></canvas>

                    </div>

                </div>

            </div>

            <!-- Document Breakdown by Type Card -->
            <div class="card analytics-card">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Document Breakdown by Type
                        </h2>

                        <p class="card-subtitle">
                            Volume of active and processed documents per category
                        </p>

                    </div>

                </div>

                <div class="card-content">

                    <div class="chart-canvas-container chart-doughnut-container">

                        <canvas id="typesChart"></canvas>

                    </div>

                </div>

            </div>

            <!-- Document Status Distribution Card -->
            <div class="card analytics-card full-width-analytics">

                <div class="card-header">

                    <div>

                        <h2 class="card-title">
                            Document Status Distribution
                        </h2>

                        <p class="card-subtitle">
                            Breakdown of documents by current lifecycle status
                        </p>

                    </div>

                    <span class="count-pill">
                        <?= number_format($total_status_docs) ?> Total Documents
                    </span>

                </div>

                <div class="card-content">

                    <div class="chart-canvas-container chart-doughnut-container">

                        <canvas id="statusChart"></canvas>

                    </div>

                </div>

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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    window.docuflowCharts = {
        bottlenecks: <?= $bottlenecksChartJson ?>,
        volume: <?= $volumeChartJson ?>,
        status: <?= $statusChartJson ?>,
        avgTime: <?= $avgTimeChartJson ?>,
        types: <?= $typesChartJson ?>
    };
</script>

<script src="../js/admin-dashboard.js?v=<?= time() ?>"></script>

</body>

</html>