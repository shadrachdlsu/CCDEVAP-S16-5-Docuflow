<?php
declare(strict_types=1);

session_start();

$role = (string) ($_SESSION['role'] ?? '');

if (!isset($_SESSION['user_id']) || !in_array($role, ['Member', 'Secretary'], true)) {
    header('Location: login.php');
    exit;
}

$dashboardPage = $role === 'Secretary' ? 'secretary-dashboard.php' : 'member-dashboard.php';
$roleLabel = $role === 'Secretary' ? 'Office Secretary' : 'Office Member';

require __DIR__ . '/../controller/db.php';

$userId = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');
$documentStatement = $conn->prepare(
    'SELECT status, created_at FROM documents WHERE creator_id = ? ORDER BY created_at'
);
$documentStatement->bind_param('i', $userId);
$documentStatement->execute();
$documents = $documentStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$documentStatement->close();

$routeStatement = $conn->prepare(
    'SELECT dr.status, COUNT(*) AS total
     FROM document_routes AS dr
     INNER JOIN documents AS d ON d.document_id = dr.document_id
     WHERE d.creator_id = ?
     GROUP BY dr.status
     ORDER BY dr.status'
);
$routeStatement->bind_param('i', $userId);
$routeStatement->execute();
$routeRows = $routeStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$routeStatement->close();

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
$totalRouteSteps = 0;

foreach ($routeRows as $route) {
    $routeStatusCounts[(string) $route['status']] = (int) $route['total'];
    $totalRouteSteps += (int) $route['total'];
}

$totalDocuments = count($documents);
$completedDocuments = $documentStatusCounts['Completed'] ?? 0;
$inactiveStatuses = ['Completed', 'Rejected', 'Recalled'];
$inactiveDocuments = 0;

foreach ($inactiveStatuses as $status) {
    $inactiveDocuments += $documentStatusCounts[$status] ?? 0;
}

$activeDocuments = max(0, $totalDocuments - $inactiveDocuments);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Member Reports - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
  </head>
  <body>
    <header class="member-header">
      <a class="web-logo" href="<?= $dashboardPage ?>">Docuflow</a>

      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= $roleLabel ?></span>
        </div>

        <button id="themeToggle" class="icon-button" type="button" aria-label="Toggle dark or light mode">
          <i class="fas fa-sun" aria-hidden="true"></i>
        </button>

        <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
          <button class="icon-button" type="submit" aria-label="Log out">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
          </button>
        </form>
      </div>
    </header>

    <main class="reports-page">
      <a class="back-link page-back-link" href="<?= $dashboardPage ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Go Back
      </a>

      <div class="page-heading">
        <p class="brand">Statistics</p>
        <h1>Document Reports</h1>
        <p class="welcome-message">A summary of documents you created and their routing activity.</p>
      </div>

      <section class="report-summary-grid" aria-label="Document statistics">
        <article class="report-summary-card">
          <span>Total Documents</span>
          <strong><?= $totalDocuments ?></strong>
        </article>
        <article class="report-summary-card">
          <span>In Progress</span>
          <strong><?= $activeDocuments ?></strong>
        </article>
        <article class="report-summary-card">
          <span>Completed</span>
          <strong><?= $completedDocuments ?></strong>
        </article>
        <article class="report-summary-card">
          <span>Total Route Steps</span>
          <strong><?= $totalRouteSteps ?></strong>
        </article>
      </section>

      <section class="report-chart-grid">
        <article class="report-chart-card report-chart-wide">
          <div class="report-chart-heading">
            <h2>Documents Created</h2>
            <p>Last six months</p>
          </div>
          <div class="report-chart-canvas"><canvas id="monthlyDocumentsChart"></canvas></div>
        </article>

        <article class="report-chart-card">
          <div class="report-chart-heading">
            <h2>Document Status</h2>
            <p>Current status distribution</p>
          </div>
          <div class="report-chart-canvas"><canvas id="documentStatusChart"></canvas></div>
        </article>

        <article class="report-chart-card">
          <div class="report-chart-heading">
            <h2>Route Status</h2>
            <p>Status of all office assignments</p>
          </div>
          <div class="report-chart-canvas"><canvas id="routeStatusChart"></canvas></div>
        </article>
      </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script src="../js/member-dashboard.js"></script>
    <script>
      document.addEventListener('DOMContentLoaded', () => {
        const monthlyLabels = <?= json_encode($monthLabels, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const monthlyValues = <?= json_encode(array_values($monthCounts)) ?>;
        const documentStatusLabels = <?= json_encode(array_keys($documentStatusCounts), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const documentStatusValues = <?= json_encode(array_values($documentStatusCounts)) ?>;
        const routeStatusLabels = <?= json_encode(array_keys($routeStatusCounts), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        const routeStatusValues = <?= json_encode(array_values($routeStatusCounts)) ?>;
        const chartColors = ['#4f46e5', '#06b6d4', '#22c55e', '#f59e0b', '#ef4444', '#a855f7', '#64748b', '#ec4899'];

        const themeColors = () => {
          const isLightMode = document.body.classList.contains('light-mode');
          return {
            text: isLightMode ? '#334155' : '#cbd5e1',
            grid: isLightMode ? 'rgba(148, 163, 184, 0.25)' : 'rgba(148, 163, 184, 0.14)'
          };
        };

        const colors = themeColors();
        const charts = [];
        charts.push(new Chart(document.getElementById('monthlyDocumentsChart'), {
          type: 'bar',
          data: {
            labels: monthlyLabels,
            datasets: [{
              label: 'Documents',
              data: monthlyValues,
              backgroundColor: '#4f46e5',
              borderRadius: 7
            }]
          },
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
          plugins: {
            legend: {
              position: 'bottom',
              labels: { color: colors.text, padding: 16, usePointStyle: true }
            }
          }
        });

        charts.push(new Chart(document.getElementById('documentStatusChart'), {
          type: 'doughnut',
          data: {
            labels: documentStatusLabels,
            datasets: [{ data: documentStatusValues, backgroundColor: chartColors, borderWidth: 0 }]
          },
          options: doughnutOptions()
        }));

        charts.push(new Chart(document.getElementById('routeStatusChart'), {
          type: 'doughnut',
          data: {
            labels: routeStatusLabels,
            datasets: [{ data: routeStatusValues, backgroundColor: chartColors.slice().reverse(), borderWidth: 0 }]
          },
          options: doughnutOptions()
        }));

        document.getElementById('themeToggle')?.addEventListener('click', () => {
          const updatedColors = themeColors();

          charts.forEach((chart) => {
            if (chart.options.plugins.legend) {
              chart.options.plugins.legend.labels.color = updatedColors.text;
            }

            if (chart.options.scales?.x) {
              chart.options.scales.x.ticks.color = updatedColors.text;
              chart.options.scales.y.ticks.color = updatedColors.text;
              chart.options.scales.y.grid.color = updatedColors.grid;
            }

            chart.update();
          });
        });
      });
    </script>
  </body>
</html>
