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

$documentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$documentId) {
    http_response_code(404);
    die('Document not found.');
}

require __DIR__ . '/../controller/db.php';

$userId = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');
$documentStatement = $conn->prepare(
    'SELECT d.tracking_code, d.title, d.status, d.file_path,
            d.created_at, dt.type_name
     FROM documents AS d
     INNER JOIN document_types AS dt ON dt.type_id = d.type_id
     WHERE d.document_id = ? AND d.creator_id = ?
     LIMIT 1'
);
$documentStatement->bind_param('ii', $documentId, $userId);
$documentStatement->execute();
$document = $documentStatement->get_result()->fetch_assoc();
$documentStatement->close();

if (!$document) {
    http_response_code(404);
    die('Document not found or it does not belong to your account.');
}

$routeStatement = $conn->prepare(
    'SELECT dr.step_no, office.office_name,
            signatory.full_name AS signatory_name,
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
$routeOffices = array_map(
    static fn (array $route): string => (string) ($route['office_name'] ?? 'Unassigned'),
    $routes
);
$routeSeparator = $isSimultaneous ? ' • ' : ' → ';
$routePath = $routeOffices === [] ? 'No route assigned' : implode($routeSeparator, $routeOffices);
$filePath = trim((string) ($document['file_path'] ?? ''));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?> - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />
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

    <main class="document-view-page">
      <a class="back-link page-back-link" href="member-my-documents.php">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Back to My Documents
      </a>

      <section class="document-view-heading">
        <div class="document-view-title-row">
          <div>
            <span class="tracking-code"><?= htmlspecialchars((string) $document['tracking_code'], ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></h1>
          </div>
          <span class="status-pill"><?= htmlspecialchars((string) $document['status'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <dl class="document-view-meta">
          <div>
            <dt>Document Type</dt>
            <dd><?= htmlspecialchars((string) $document['type_name'], ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <div>
            <dt>Sending Method</dt>
            <dd><?= htmlspecialchars($sendingMethod, ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <div>
            <dt>Route Count</dt>
            <dd><?= count($routes) ?> office<?= count($routes) === 1 ? '' : 's' ?></dd>
          </div>
          <div>
            <dt>Created</dt>
            <dd><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <div class="document-route-path">
            <dt>Route Path</dt>
            <dd><?= htmlspecialchars($routePath, ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
        </dl>
      </section>

      <?php if ($filePath !== ''): ?>
        <section class="document-preview">
          <div class="document-preview-toolbar">
            <h2>Document Preview</h2>
            <a class="view-document-button" href="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener">
              <i class="fas fa-external-link-alt" aria-hidden="true"></i>
              Open in New Tab
            </a>
          </div>
          <iframe
            src="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>"
            title="Preview of <?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?>"
          ></iframe>
        </section>
      <?php else: ?>
        <section class="empty-state">
          <i class="fas fa-file-circle-xmark" aria-hidden="true"></i>
          <h2>No file uploaded</h2>
          <p>This document record does not have an attached PDF.</p>
        </section>
      <?php endif; ?>

      <section class="document-table-panel" aria-label="Document routing progress">
        <div class="trail-table-heading">
          <div>
            <h2>Routing Progress and Remarks</h2>
            <p>Review each office action and its saved remark.</p>
          </div>
        </div>

        <div class="document-table-scroll">
          <table id="documentTrailTable" class="display member-documents-table">
            <thead>
              <tr>
                <th>Step</th>
                <th>Office</th>
                <th>Signatory</th>
                <th>Status</th>
                <th>Action Date</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($routes as $route): ?>
                <tr>
                  <td><?= (int) $route['step_no'] === 0 ? 'Independent' : (int) $route['step_no'] ?></td>
                  <td><?= htmlspecialchars((string) ($route['office_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($route['signatory_name'] ?? 'Office Queue'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="status-pill"><?= htmlspecialchars((string) $route['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td data-order="<?= htmlspecialchars((string) ($route['acted_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                    <?= $route['acted_at'] ? htmlspecialchars(date('M j, Y g:i A', strtotime((string) $route['acted_at'])), ENT_QUOTES, 'UTF-8') : 'Not yet acted on' ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="document-table-panel route-remarks-panel" aria-label="Document route remarks">
        <div class="trail-table-heading">
          <div>
            <h2>Route Remarks</h2>
            <p>Remarks saved by each assigned signatory.</p>
          </div>
        </div>

        <div class="route-remarks-list">
          <?php foreach ($routes as $route): ?>
            <div class="route-remark-item">
              <label>
                <?= (int) $route['step_no'] === 0 ? 'Independent' : 'Step ' . (int) $route['step_no'] ?>
                &middot;
                <?= htmlspecialchars((string) ($route['office_name'] ?? 'Unassigned Office'), ENT_QUOTES, 'UTF-8') ?>
              </label>
              <span><?= htmlspecialchars((string) ($route['signatory_name'] ?? 'Office Queue'), ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars((string) $route['status'], ENT_QUOTES, 'UTF-8') ?></span>
              <textarea class="route-remarks-box" rows="4" readonly aria-label="Remarks for <?= htmlspecialchars((string) ($route['office_name'] ?? 'unassigned office'), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) ($route['remarks'] ?: 'No remarks'), ENT_QUOTES, 'UTF-8') ?></textarea>
            </div>
          <?php endforeach; ?>

          <?php if ($routes === []): ?>
            <p class="route-remarks-empty">No route remarks are available.</p>
          <?php endif; ?>
        </div>
      </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/member-dashboard.js"></script>
    <script>
      new DataTable('#documentTrailTable', {
        paging: false,
        searching: false,
        info: false,
        order: [[0, 'asc']],
        language: {
          emptyTable: 'No office assignments were found for this document.'
        }
      });
    </script>
  </body>
</html>
