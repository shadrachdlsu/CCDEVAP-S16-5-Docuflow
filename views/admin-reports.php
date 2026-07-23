<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../controller/db.php';
require_once __DIR__ . '/../controller/document_duration.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');

$summary = $conn->query(
    "SELECT
        (SELECT COUNT(*) FROM documents) AS total_documents,
        (SELECT COUNT(*) FROM document_routes) AS total_routes,
        (SELECT COUNT(*) FROM users WHERE is_active = 1) AS active_users,
        (SELECT COUNT(*) FROM offices) AS total_offices,
        (SELECT COUNT(*) FROM users WHERE registration_status = 'Pending') AS pending_registrations"
)->fetch_assoc();

$documents = $conn->query(
    'SELECT status, created_at FROM documents ORDER BY created_at'
)->fetch_all(MYSQLI_ASSOC);

$routeRows = $conn->query(
    'SELECT status, COUNT(*) AS total
     FROM document_routes
     GROUP BY status
     ORDER BY status'
)->fetch_all(MYSQLI_ASSOC);

$officeRows = $conn->query(
    'SELECT offices.office_name, COUNT(document_routes.route_id) AS total
     FROM offices
     LEFT JOIN document_routes ON document_routes.office_id = offices.office_id
     GROUP BY offices.office_id, offices.office_name
     ORDER BY total DESC, offices.office_name'
)->fetch_all(MYSQLI_ASSOC);

$officeCompletionRows = $conn->query(
    "SELECT office.office_name,
            COUNT(completed_route.route_id) AS completed_steps,
            AVG(completed_route.duration_seconds) AS average_seconds
     FROM offices AS office
     LEFT JOIN (
         SELECT dr.route_id, dr.office_id,
                TIMESTAMPDIFF(
                    SECOND,
                    CASE
                      WHEN dr.step_no > 0 THEN COALESCE(
                          (SELECT MAX(previous_route.acted_at)
                           FROM document_routes AS previous_route
                           WHERE previous_route.document_id = dr.document_id
                             AND previous_route.step_no > 0
                             AND previous_route.step_no < dr.step_no
                             AND previous_route.acted_at IS NOT NULL),
                          document.created_at
                      )
                      ELSE document.created_at
                    END,
                    dr.acted_at
                ) AS duration_seconds
         FROM document_routes AS dr
         INNER JOIN documents AS document ON document.document_id = dr.document_id
         WHERE document.status = 'Completed'
           AND dr.status IN ('Signed', 'Completed')
           AND dr.acted_at IS NOT NULL
     ) AS completed_route ON completed_route.office_id = office.office_id
     GROUP BY office.office_id, office.office_name
     ORDER BY average_seconds DESC, office.office_name"
)->fetch_all(MYSQLI_ASSOC);

$monthCounts = [];
$monthLabels = [];
$firstMonth = new DateTimeImmutable('first day of this month');

for ($monthsAgo = 5; $monthsAgo >= 0; $monthsAgo--) {
    $month = $firstMonth->modify("-{$monthsAgo} months");
    $monthCounts[$month->format('Y-m')] = 0;
    $monthLabels[] = $month->format('M Y');
}

$documentStatusCounts = [];

foreach ($documents as $document) {
    $status = (string) $document['status'];
    $monthKey = date('Y-m', strtotime((string) $document['created_at']));
    $documentStatusCounts[$status] = ($documentStatusCounts[$status] ?? 0) + 1;

    if (array_key_exists($monthKey, $monthCounts)) {
        $monthCounts[$monthKey]++;
    }
}

$routeStatusCounts = [];

foreach ($routeRows as $route) {
    $routeStatusCounts[(string) $route['status']] = (int) $route['total'];
}

$officeLabels = array_map(
    static fn (array $office): string => (string) $office['office_name'],
    $officeRows
);
$officeRouteCounts = array_map(
    static fn (array $office): int => (int) $office['total'],
    $officeRows
);
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
          <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
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
        <a class="admin-report-chart-card admin-report-chart-wide admin-report-chart-link" href="admin-documents-created-report.php" aria-label="Open documents created by office and user report">
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
            <div><h2>Route Status</h2><p>Current status of all office route steps.</p></div>
          </div>
          <div class="admin-report-chart-canvas"><canvas id="adminRouteStatusChart"></canvas></div>
        </article>

        <article class="admin-report-chart-card admin-report-chart-wide">
          <div class="admin-report-chart-heading">
            <div><h2>Routes by Office</h2><p>Total document route assignments per office.</p></div>
          </div>
          <div class="admin-report-chart-canvas admin-report-office-chart"><canvas id="adminOfficeRoutesChart"></canvas></div>
        </article>

        <article class="admin-report-chart-card admin-report-chart-wide">
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
      document.addEventListener('DOMContentLoaded', () => {
        const monthlyLabels = <?= json_encode($monthLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const monthlyValues = <?= json_encode(array_values($monthCounts)) ?>;
        const documentStatusLabels = <?= json_encode(array_keys($documentStatusCounts), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const documentStatusValues = <?= json_encode(array_values($documentStatusCounts)) ?>;
        const routeStatusLabels = <?= json_encode(array_keys($routeStatusCounts), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const routeStatusValues = <?= json_encode(array_values($routeStatusCounts)) ?>;
        const officeLabels = <?= json_encode($officeLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const officeValues = <?= json_encode($officeRouteCounts) ?>;
        const officeCompletionLabels = <?= json_encode($officeCompletionLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const officeCompletionHours = <?= json_encode($officeCompletionHours) ?>;
        const officeCompletionSeconds = <?= json_encode($officeCompletionSeconds) ?>;
        const officeCompletedSteps = <?= json_encode($officeCompletedSteps) ?>;
        const palette = ['#4f46e5', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#64748b', '#ec4899'];

        const themeColors = () => {
          const dark = document.body.classList.contains('dark-mode');
          return {
            text: dark ? '#cbd5e1' : '#334155',
            grid: dark ? 'rgba(148, 163, 184, 0.14)' : 'rgba(148, 163, 184, 0.25)'
          };
        };

        const colors = themeColors();
        const charts = [];
        charts.push(new Chart(document.getElementById('adminMonthlyDocumentsChart'), {
          type: 'bar',
          data: { labels: monthlyLabels, datasets: [{ label: 'Documents', data: monthlyValues, backgroundColor: '#4f46e5', borderRadius: 7 }] },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { ticks: { color: colors.text }, grid: { display: false } },
              y: { beginAtZero: true, ticks: { color: colors.text, precision: 0 }, grid: { color: colors.grid } }
            }
          }
        }));

        const doughnutOptions = () => ({
          responsive: true,
          maintainAspectRatio: false,
          cutout: '62%',
          plugins: { legend: { position: 'bottom', labels: { color: colors.text, padding: 16, usePointStyle: true } } }
        });

        charts.push(new Chart(document.getElementById('adminDocumentStatusChart'), {
          type: 'doughnut',
          data: { labels: documentStatusLabels, datasets: [{ data: documentStatusValues, backgroundColor: palette, borderWidth: 0 }] },
          options: doughnutOptions()
        }));

        charts.push(new Chart(document.getElementById('adminRouteStatusChart'), {
          type: 'doughnut',
          data: { labels: routeStatusLabels, datasets: [{ data: routeStatusValues, backgroundColor: palette.slice().reverse(), borderWidth: 0 }] },
          options: doughnutOptions()
        }));

        charts.push(new Chart(document.getElementById('adminOfficeRoutesChart'), {
          type: 'bar',
          data: { labels: officeLabels, datasets: [{ label: 'Route Steps', data: officeValues, backgroundColor: '#06b6d4', borderRadius: 7 }] },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
              x: { beginAtZero: true, ticks: { color: colors.text, precision: 0 }, grid: { color: colors.grid } },
              y: { ticks: { color: colors.text }, grid: { display: false } }
            }
          }
        }));

        const formatDuration = (totalSeconds) => {
          if (totalSeconds === null) return 'No completed route steps';
          if (totalSeconds < 60) return 'Less than a minute';

          const days = Math.floor(totalSeconds / 86400);
          const hours = Math.floor((totalSeconds % 86400) / 3600);
          const minutes = Math.floor((totalSeconds % 3600) / 60);
          const parts = [];

          if (days) parts.push(`${days} day${days === 1 ? '' : 's'}`);
          if (hours) parts.push(`${hours} hour${hours === 1 ? '' : 's'}`);
          if (minutes) parts.push(`${minutes} minute${minutes === 1 ? '' : 's'}`);
          return parts.join(', ');
        };

        charts.push(new Chart(document.getElementById('adminOfficeCompletionChart'), {
          type: 'bar',
          data: { labels: officeCompletionLabels, datasets: [{ label: 'Average Hours', data: officeCompletionHours, backgroundColor: '#22c55e', borderRadius: 7 }] },
          options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
              legend: { display: false },
              tooltip: {
                callbacks: {
                  label: (context) => {
                    const index = context.dataIndex;
                    const duration = formatDuration(officeCompletionSeconds[index]);
                    const steps = officeCompletedSteps[index];
                    return `${duration} (${steps} completed step${steps === 1 ? '' : 's'})`;
                  }
                }
              }
            },
            scales: {
              x: { beginAtZero: true, title: { display: true, text: 'Average hours', color: colors.text }, ticks: { color: colors.text }, grid: { color: colors.grid } },
              y: { ticks: { color: colors.text }, grid: { display: false } }
            }
          }
        }));

        document.getElementById('themeToggle')?.addEventListener('click', () => {
          const updated = themeColors();

          charts.forEach((chart) => {
            if (chart.options.plugins.legend) {
              chart.options.plugins.legend.labels.color = updated.text;
            }

            if (chart.options.scales?.x) {
              chart.options.scales.x.ticks.color = updated.text;
              chart.options.scales.y.ticks.color = updated.text;

              if (chart.config.type === 'bar' && chart.options.indexAxis === 'y') {
                chart.options.scales.x.grid.color = updated.grid;
              } else {
                chart.options.scales.y.grid.color = updated.grid;
              }
            }

            chart.update();
          });
        });
      });
    </script>
  </body>
</html>
