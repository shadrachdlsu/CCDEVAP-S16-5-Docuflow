<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

$documentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$documentId) {
    http_response_code(404);
    die('Document not found.');
}

require __DIR__ . '/../controller/db.php';
require_once __DIR__ . '/../controller/document_duration.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
$documentStatement = $conn->prepare(
    'SELECT d.tracking_code, d.title, d.status, d.file_path, d.created_at,
            (SELECT MAX(completed_route.acted_at)
             FROM document_routes AS completed_route
             WHERE completed_route.document_id = d.document_id
               AND completed_route.status IN ("Signed", "Completed")) AS completed_at,
            COALESCE(dt.type_name, "Unspecified") AS type_name,
            COALESCE(creator.full_name, "Unknown user") AS creator_name
     FROM documents AS d
     LEFT JOIN document_types AS dt ON dt.type_id = d.type_id
     LEFT JOIN users AS creator ON creator.user_id = d.creator_id
     WHERE d.document_id = ?
     LIMIT 1'
);
$documentStatement->bind_param('i', $documentId);
$documentStatement->execute();
$document = $documentStatement->get_result()->fetch_assoc();
$documentStatement->close();

if (!$document) {
    http_response_code(404);
    die('Document not found.');
}

$routeStatement = $conn->prepare(
    'SELECT dr.step_no, office.office_name, signatory.full_name AS signatory_name,
            dr.status, dr.remarks, dr.acted_at
     FROM document_routes AS dr
     LEFT JOIN offices AS office ON office.office_id = dr.office_id
     LEFT JOIN users AS signatory ON signatory.user_id = dr.signatory_user_id
     WHERE dr.document_id = ?
     ORDER BY dr.step_no ASC, office.office_name ASC'
);
$routeStatement->bind_param('i', $documentId);
$routeStatement->execute();
$routes = $routeStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$routeStatement->close();
$isSimultaneous = $routes !== [] && array_reduce(
    $routes,
    static fn (bool $simultaneous, array $route): bool => $simultaneous && (int) $route['step_no'] === 0,
    true
);
$sendingMethod = $isSimultaneous ? 'Simultaneous' : 'Sequential';

$filePath = trim((string) ($document['file_path'] ?? ''));
$documentStatusClass = strtolower((string) $document['status']);
$isCompleted = (string) $document['status'] === 'Completed';
$completedAt = trim((string) ($document['completed_at'] ?? ''));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?> - Docuflow</title>
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

    <main class="admin-page admin-document-view-page">
      <a class="admin-back-button" href="admin-documents.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Documents</a>

      <section class="admin-document-summary">
        <div class="admin-document-title-row">
          <div>
            <span class="admin-tracking-code"><?= htmlspecialchars((string) $document['tracking_code'], ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></h1>
          </div>
          <span class="admin-status-pill <?= htmlspecialchars($documentStatusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $document['status'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <dl class="admin-document-meta">
          <div><dt>Document Type</dt><dd><?= htmlspecialchars((string) $document['type_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Created By</dt><dd><?= htmlspecialchars((string) $document['creator_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Created</dt><dd><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <?php if ($isCompleted && $completedAt !== ''): ?>
            <div><dt>Total Completion Time</dt><dd><?= htmlspecialchars(formatDocumentDuration((string) $document['created_at'], $completedAt), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <?php endif; ?>
          <div><dt>Sending Method</dt><dd><?= htmlspecialchars($sendingMethod, ENT_QUOTES, 'UTF-8') ?></dd></div>
        </dl>
      </section>

      <?php if ($filePath !== ''): ?>
        <section class="admin-document-preview">
          <div class="admin-document-section-heading">
            <h2>Document Preview</h2>
            <a class="admin-table-action" href="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="fas fa-external-link-alt" aria-hidden="true"></i> Open in New Tab</a>
          </div>
          <iframe src="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>" title="Preview of <?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?>"></iframe>
        </section>
      <?php else: ?>
        <section class="admin-document-empty"><i class="fas fa-file-circle-xmark" aria-hidden="true"></i><h2>No file uploaded</h2><p>This document record has no attached PDF.</p></section>
      <?php endif; ?>

      <section class="admin-table-panel" aria-label="Document routing trail">
        <div class="admin-document-section-heading"><div><h2>Routing Progress</h2><p><?= htmlspecialchars($sendingMethod, ENT_QUOTES, 'UTF-8') ?> send across <?= count($routes) ?> office assignment<?= count($routes) === 1 ? '' : 's' ?>.</p></div></div>
        <div class="admin-table-scroll">
          <table id="adminDocumentRoutesTable" class="display admin-data-table admin-routes-table">
            <thead><tr><th>Step</th><th>Office</th><th>Signatory</th><th>Status</th><th>Remarks</th><th>Action Date</th></tr></thead>
            <tbody>
              <?php foreach ($routes as $route): ?>
                <?php $routeStatusClass = strtolower(str_replace(' ', '-', (string) $route['status'])); ?>
                <tr>
                  <td><?= (int) $route['step_no'] === 0 ? 'Independent' : (int) $route['step_no'] ?></td>
                  <td><?= htmlspecialchars((string) ($route['office_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($route['signatory_name'] ?? 'Office Queue'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="admin-status-pill <?= htmlspecialchars($routeStatusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $route['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars((string) ($route['remarks'] ?: '—'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td data-order="<?= htmlspecialchars((string) ($route['acted_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"><?= $route['acted_at'] ? htmlspecialchars(date('M j, Y g:i A', strtotime((string) $route['acted_at'])), ENT_QUOTES, 'UTF-8') : 'Not yet acted on' ?></td>
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
      new DataTable('#adminDocumentRoutesTable', {
        paging: false,
        searching: false,
        info: false,
        order: [[0, 'asc']],
        language: { emptyTable: 'No route assignments were found.' }
      });
    </script>
  </body>
</html>
