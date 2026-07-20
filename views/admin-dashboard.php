<?php require_once '../controllers/AdminDashboardController.php'; ?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Docuflow - Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="../css/admin-dashboard.css" />
</head>

<body class="admin-body">
  <div class="admin-layout">
    <main class="admin-main">
      <header class="admin-header">
        <div class="header-left">
          <a href="admin-dashboard.php" class="logo-area">
            <span class="web-logo">Docuflow</span>
          </a>
        </div>
        <div class="header-right">
          <div class="user-info">
            <span class="user-email">admin@office.gov</span>
            <span class="user-role">Administrator</span>
          </div>
          <div class="header-actions">
            <button class="icon-btn toggle-theme" id="themeToggle" aria-label="Toggle dark/light mode">
              <i class="fas fa-moon"></i>
            </button>
            <button class="icon-btn logout-btn" aria-label="Exit / Logout">
              <i class="fas fa-sign-out-alt"></i>
            </button>
          </div>
        </div>
      </header>

      <!-- stats and actions -->
      <section class="stats-grid">
        <div class="stat-card">
          <span class="stat-icon"><i class="fas fa-file-alt"></i></span>
          <span class="stat-number"><?php echo $stats['total_docs']; ?></span>
          <span class="stat-label">Total Documents</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fas fa-users"></i></span>
          <span class="stat-number"><?php echo $stats['active_users']; ?></span>
          <span class="stat-label">Active Users</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fas fa-building"></i></span>
          <span class="stat-number"><?php echo $stats['total_offices']; ?></span>
          <span class="stat-label">Offices</span>
        </div>
        <div class="stat-card">
          <span class="stat-icon"><i class="fas fa-clock"></i></span>
          <span class="stat-number"><?php echo $stats['pending_docs']; ?></span>
          <span class="stat-label">Pending Documents</span>
        </div>
      </section>

      <!-- main system statistics -->
      <section class="dashboard-2x2-grid">
        <div class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">Document Distribution</h2>
            <p class="preview-description">Breakdown of documents by current status.</p>
          </div>
          <div class="admin-preview-content" id="doc-dist-content">
            <canvas id="dynamicPieChart" style="max-height: 250px;"></canvas>
            <script>
              const docDistChartData = <?= $docDistJson ?>;
            </script>
          </div>
        </div>

        <div class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">User Distribution</h2>
            <p class="preview-description">Percentage of users by role.</p>
          </div>
          <div class="admin-preview-content" id="user-dist-content">
            <div class="preview-layout">
              <div class="pie-chart" style="background: conic-gradient(<?= $userDistGradient ?>)" aria-label="User Distribution">
                <span><?= $userDistTotal ?></span>
              </div>
              <div class="preview-list">
                <?php foreach($formattedUserDistRows as $row): ?>
                  <div class="preview-row">
                    <span class="preview-swatch" style="background: <?= $row['color'] ?>"></span>
                    <span><?= htmlspecialchars($row['label']) ?></span>
                    <strong><?= $row['value'] ?></strong>
                  </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>

        <div class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">Office Directory</h2>
            <p class="preview-description">Registered offices and assigned document load.</p>
          </div>
          <div class="admin-preview-content" id="offices-content" style="max-height: 250px; overflow-y: auto;">
            <div class="office-grid">
              <?php foreach($officeDirectory as $office): ?>
                <div class="office-card">
                  <strong><?= htmlspecialchars($office['name']) ?></strong>
                  <span><?= htmlspecialchars($office['detail']) ?></span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>

        <div class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">Pending Documents</h2>
            <p class="preview-description">Documents waiting for action across offices.</p>
          </div>
          <div class="admin-preview-content" id="pending-content" style="max-height: 250px; overflow-y: auto;">
            <div class="pending-list">
              <?php foreach($pendingDocsList as $doc): ?>
                <div class="pending-card">
                  <div>
                    <strong><?= htmlspecialchars($doc['title']) ?></strong>
                    <span><?= htmlspecialchars($doc['id']) ?> - <?= htmlspecialchars($doc['office']) ?></span>
                  </div>
                  <span class="pending-status">Pending</span>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
        <a class="report-card" href="admin-chart-bottlenecks.php">
          <div class="report-card-header">
            <strong>Office Bottlenecks</strong>
            <span><?php echo htmlspecialchars($stats['bottleneck_text']); ?></span>
          </div>
          <div class="report-chart-container">
            <canvas id="miniBottleneckChart"></canvas>
          </div>
        </a>
        <a class="report-card" href="admin-chart-trends.php">
          <div class="report-card-header">
            <strong>Volume Trends</strong>
            <span><?php echo htmlspecialchars($stats['trend_text']); ?></span>
          </div>
          <div class="report-chart-container">
            <canvas id="miniTrendsChart"></canvas>
          </div>
        </a>
        <a class="report-card" href="admin-chart-types.php">
          <div class="report-card-header">
            <strong>Doc Types</strong>
            <span><?php echo htmlspecialchars($stats['types_text']); ?></span>
          </div>
          <div class="report-chart-container">
            <canvas id="miniTypesChart"></canvas>
          </div>
        </a>
      </section>

      <section class="action-grid" style="margin-top: 24px;">
        <a class="action-card" href="admin-document-types.php">
          <span class="action-icon"><i class="fas fa-search"></i></span>
          <span>Document Types</span>
        </a>
        <a class="action-card" href="admin-users.php">
          <span class="action-icon"><i class="fas fa-user-cog"></i></span>
          <span>Manage Users</span>
        </a>
        <a class="action-card" href="admin-offices.php">
          <span class="action-icon"><i class="fas fa-briefcase"></i></span>
          <span>Manage Offices</span>
        </a>
      </section>
    </main>
  </div>

  <script>
    const bottleneckChartData = <?= $bottleneckChartJson ?>;
    const trendsChartData = <?= $trendsChartJson ?>;
    const typesChartData = <?= $typesChartJson ?>;
  </script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="../js/admin-dashboard.js"></script>
</body>

</html>