<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/report.php';
require_once __DIR__ . '/../controllers/SharedDocumentDurationController.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');

$reportModel = new Report();
$summary = $reportModel->getAdminSummary();
$documents = $reportModel->getDocumentTimeline();
$officeCompletionRows = $reportModel->getOfficeCompletionDurations();

$monthCounts = [];
$monthLabels = [];
$firstMonth = new DateTimeImmutable('first day of this month');

for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
    $month = $firstMonth->modify("-{$monthsAgo} months");
    $monthCounts[$month->format('Y-m')] = 0;
    $monthLabels[] = $month->format('M Y');
}

$officeDocumentRows = $reportModel->getOfficeDocumentTimeline(
    $firstMonth->modify('-5 months')->format('Y-m-d'),
    $firstMonth->modify('+1 month')->format('Y-m-d')
);
$documentStatusCounts = [];

foreach ($documents as $document) {
    $status = (string) $document['status'];
    $monthKey = date('Y-m', strtotime((string) $document['created_at']));
    $documentStatusCounts[$status] = ($documentStatusCounts[$status] ?? 0) + 1;

    if (array_key_exists($monthKey, $monthCounts)) {
        $monthCounts[$monthKey]++;
    }
}

$officeDocumentCounts = [];

foreach ($officeDocumentRows as $row) {
    $officeName = (string) $row['office_name'];
    $monthKey = (string) ($row['month_key'] ?? '');

    if (!isset($officeDocumentCounts[$officeName])) {
        $officeDocumentCounts[$officeName] = array_fill_keys(array_keys($monthCounts), 0);
    }

    if (array_key_exists($monthKey, $officeDocumentCounts[$officeName])) {
        $officeDocumentCounts[$officeName][$monthKey] = (int) $row['total'];
    }
}

$officeDocumentSeries = [];

foreach ($officeDocumentCounts as $officeName => $counts) {
    $officeDocumentSeries[] = [
        'label' => $officeName,
        'data' => array_values($counts),
    ];
}

$officeCompletionLabels = array_map(
    static fn (array $office): string => (string) $office['office_name'],
    $officeCompletionRows
);
$officeCompletionHours = array_map(
    static fn (array $office): ?float => $office['average_seconds'] === null
        ? null
        : round((float) $office['average_seconds'] / 3600, 2),
    $officeCompletionRows
);
$officeCompletionSeconds = array_map(
    static fn (array $office): ?int => $office['average_seconds'] === null
        ? null
        : (int) round((float) $office['average_seconds']),
    $officeCompletionRows
);
$officeCompletedSteps = array_map(
    static fn (array $office): int => (int) $office['completed_steps'],
    $officeCompletionRows
);
$completedDocuments = $documentStatusCounts['Completed'] ?? 0;
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Reports - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../css/admin-dashboard.css?v=<?= filemtime(__DIR__ . '/../css/admin-dashboard.css') ?>" />
  </head>
  <body class="admin-body">
    <header class="admin-header">
      <div class="header-left"><a class="web-logo" href="admin-dashboard.php">Docuflow</a></div>
      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?> &middot; Administrator</span>
        </div>
        <div class="header-actions">
          <button class="icon-btn toggle-theme" id="themeToggle" type="button" aria-label="Toggle dark/light mode"><i class="fas fa-moon"></i></button>
          <form class="logout-form" method="post" action="../controllers/UserLogoutController.php">
            <button class="icon-btn" type="submit" aria-label="Exit / Logout"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
      </div>
    </header>

    <main class="admin-page admin-reports-page">
      <a class="admin-back-button" href="admin-dashboard.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="admin-page-heading">
        <p>System Analytics</p>
        <h1>Reports Dashboard</h1>
        <span>Live document, routing, office, and user activity across Docuflow.</span>
      </div>

      <section class="admin-report-summary-grid" aria-label="System summary">
        <article class="admin-report-summary-card">
          <span><i class="fas fa-file-alt" aria-hidden="true"></i> Total Documents</span>
          <strong><?= (int) ($summary['total_documents'] ?? 0) ?></strong>
        </article>
        <article class="admin-report-summary-card">
          <span><i class="fas fa-circle-check" aria-hidden="true"></i> Completed</span>
          <strong><?= $completedDocuments ?></strong>
        </article>
        <article class="admin-report-summary-card">
          <span><i class="fas fa-route" aria-hidden="true"></i> Route Steps</span>
          <strong><?= (int) ($summary['total_routes'] ?? 0) ?></strong>
        </article>
        <article class="admin-report-summary-card">
          <span><i class="fas fa-users" aria-hidden="true"></i> Active Users</span>
          <strong><?= (int) ($summary['active_users'] ?? 0) ?></strong>
        </article>
        <article class="admin-report-summary-card">
          <span><i class="fas fa-building" aria-hidden="true"></i> Offices</span>
          <strong><?= (int) ($summary['total_offices'] ?? 0) ?></strong>
        </article>
        <article class="admin-report-summary-card">
          <span><i class="fas fa-user-clock" aria-hidden="true"></i> Pending Approvals</span>
          <strong><?= (int) ($summary['pending_registrations'] ?? 0) ?></strong>
        </article>
      </section>

      <section class="admin-report-chart-grid" aria-label="System charts">
        <a class="admin-report-chart-card admin-report-chart-link" href="admin-documents-created-report.php" aria-label="Open documents created by office and user report">
          <div class="admin-report-chart-heading">
            <div><h2>Documents Created <i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i></h2><p>Documents added during the last six months. Click to view documents per office and user.</p></div>
          </div>
          <div class="admin-report-chart-canvas"><canvas id="adminMonthlyDocumentsChart"></canvas></div>
        </a>

        <article class="admin-report-chart-card">
          <div class="admin-report-chart-heading">
            <div><h2>Document Status</h2><p>Current system-wide document distribution.</p></div>
          </div>
          <div class="admin-report-chart-canvas"><canvas id="adminDocumentStatusChart"></canvas></div>
        </article>

        <article class="admin-report-chart-card">
          <div class="admin-report-chart-heading">
            <div><h2>Documents by Office Over Time</h2><p>Monthly documents created by users in each office during the last six months.</p></div>
          </div>
          <div class="admin-report-chart-canvas admin-report-office-chart"><canvas id="adminOfficeDocumentsTimelineChart"></canvas></div>
        </article>

        <article class="admin-report-chart-card">
          <div class="admin-report-chart-heading">
            <div><h2>Average Completion Time by Office</h2><p>Average time each office took to finish its route step on completed documents.</p></div>
          </div>
          <div class="admin-report-chart-canvas admin-report-office-chart"><canvas id="adminOfficeCompletionChart"></canvas></div>
        </article>
      </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
    <script>
      window.docuflowAdminReportData = <?= json_encode([
          'monthlyLabels' => $monthLabels,
          'monthlyValues' => array_values($monthCounts),
          'documentStatusLabels' => array_keys($documentStatusCounts),
          'documentStatusValues' => array_values($documentStatusCounts),
          'officeDocumentSeries' => $officeDocumentSeries,
          'officeCompletionLabels' => $officeCompletionLabels,
          'officeCompletionHours' => $officeCompletionHours,
          'officeCompletionSeconds' => $officeCompletionSeconds,
          'officeCompletedSteps' => $officeCompletedSteps,
      ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    </script>
    <script src="../js/admin-reports.js?v=<?= filemtime(__DIR__ . '/../js/admin-reports.js') ?>"></script>
  </body>
</html>
