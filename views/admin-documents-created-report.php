<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../controller/db.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');

$officeSummary = $conn->query(
    "SELECT office.office_name,
            COUNT(DISTINCT user.user_id) AS user_count,
            COUNT(document.document_id) AS document_count,
            COALESCE(SUM(document.status = 'Completed'), 0) AS completed_count
     FROM offices AS office
     LEFT JOIN users AS user ON user.office_id = office.office_id
     LEFT JOIN documents AS document ON document.creator_id = user.user_id
     GROUP BY office.office_id, office.office_name
     ORDER BY document_count DESC, office.office_name"
)->fetch_all(MYSQLI_ASSOC);

$userRows = $conn->query(
    "SELECT office.office_name, user.full_name, user.email, role.role_name,
            COUNT(document.document_id) AS document_count,
            COALESCE(SUM(document.status = 'Completed'), 0) AS completed_count,
            MAX(document.created_at) AS latest_document_at
     FROM offices AS office
     INNER JOIN users AS user ON user.office_id = office.office_id
     INNER JOIN roles AS role ON role.role_id = user.role_id
     LEFT JOIN documents AS document ON document.creator_id = user.user_id
     GROUP BY office.office_id, office.office_name, user.user_id, user.full_name, user.email, role.role_name
     ORDER BY office.office_name, document_count DESC, user.full_name"
)->fetch_all(MYSQLI_ASSOC);

$totalOfficeDocuments = array_sum(array_map(
    static fn (array $office): int => (int) $office['document_count'],
    $officeSummary
));
$totalOfficeUsers = array_sum(array_map(
    static fn (array $office): int => (int) $office['user_count'],
    $officeSummary
));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Documents Created by Office and User - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />
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

    <main class="admin-page admin-documents-page">
      <a class="admin-back-button" href="admin-reports.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Reports</a>

      <div class="admin-page-heading">
        <p>Documents Created</p>
        <h1>Documents per Office and User</h1>
        <span>Compare document creation activity across offices and individual users.</span>
      </div>

      <section class="admin-report-summary-grid" aria-label="Document creation summary">
        <article class="admin-report-summary-card"><span><i class="fas fa-file-alt" aria-hidden="true"></i> Office Documents</span><strong><?= $totalOfficeDocuments ?></strong></article>
        <article class="admin-report-summary-card"><span><i class="fas fa-users" aria-hidden="true"></i> Office Users</span><strong><?= $totalOfficeUsers ?></strong></article>
        <article class="admin-report-summary-card"><span><i class="fas fa-building" aria-hidden="true"></i> Offices</span><strong><?= count($officeSummary) ?></strong></article>
      </section>

      <section class="admin-table-panel" aria-label="Documents created by office">
        <div class="admin-document-section-heading"><div><h2>Office Totals</h2><p>Total documents created by users assigned to each office.</p></div></div>
        <div class="admin-table-scroll">
          <table id="adminOfficeDocumentReportTable" class="display admin-data-table">
            <thead><tr><th>Office</th><th>Users</th><th>Documents Created</th><th>Completed</th></tr></thead>
            <tbody>
              <?php foreach ($officeSummary as $office): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int) $office['user_count'] ?></td>
                  <td><?= (int) $office['document_count'] ?></td>
                  <td><?= (int) $office['completed_count'] ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="admin-table-panel admin-report-detail-panel" aria-label="Documents created by user">
        <div class="admin-document-section-heading"><div><h2>User Breakdown</h2><p>Search or sort users to compare document creation within each office.</p></div></div>
        <div class="admin-table-scroll">
          <table id="adminUserDocumentReportTable" class="display admin-data-table">
            <thead><tr><th>Office</th><th>User</th><th>Email</th><th>Role</th><th>Documents Created</th><th>Completed</th><th>Latest Document</th></tr></thead>
            <tbody>
              <?php foreach ($userRows as $user): ?>
                <tr>
                  <td><?= htmlspecialchars((string) $user['office_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $user['role_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int) $user['document_count'] ?></td>
                  <td><?= (int) $user['completed_count'] ?></td>
                  <td data-order="<?= htmlspecialchars((string) ($user['latest_document_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= $user['latest_document_at'] ? htmlspecialchars(date('M j, Y g:i A', strtotime((string) $user['latest_document_at'])), ENT_QUOTES, 'UTF-8') : 'No documents' ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
    <script>
      new DataTable('#adminOfficeDocumentReportTable', {
        paging: false,
        searching: false,
        info: false,
        order: [[2, 'desc']],
        language: { emptyTable: 'No offices were found.' }
      });

      new DataTable('#adminUserDocumentReportTable', {
        pageLength: 10,
        order: [[0, 'asc'], [4, 'desc']],
        language: {
          search: 'Search office or user:',
          emptyTable: 'No office users were found.'
        }
      });
    </script>
  </body>
</html>
