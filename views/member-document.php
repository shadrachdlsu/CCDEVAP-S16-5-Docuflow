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
require_once __DIR__ . '/../controller/document_duration.php';

$userId = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');
$actionSuccess = (string) ($_SESSION['document_action_success'] ?? '');
$actionError = (string) ($_SESSION['document_action_error'] ?? '');
unset($_SESSION['document_action_success'], $_SESSION['document_action_error']);
$statement = $conn->prepare(
    "SELECT dr.route_id, d.tracking_code, d.title, d.file_path,
            d.status AS document_status, d.created_at,
            (SELECT MAX(completed_route.acted_at)
             FROM document_routes AS completed_route
             WHERE completed_route.document_id = d.document_id
               AND completed_route.status IN ('Signed', 'Completed')) AS completed_at,
            dt.type_name, creator.full_name AS creator_name,
            office.office_name, dr.status AS route_status, dr.step_no, dr.remarks,
            CASE WHEN dr.step_no = 0 THEN 'Simultaneous' ELSE 'Sequential' END AS sending_method,
            CASE
              WHEN dr.step_no = 0 THEN 1
              WHEN NOT EXISTS (
                SELECT 1
                FROM document_routes AS earlier_route
                WHERE earlier_route.document_id = d.document_id
                  AND earlier_route.step_no > 0
                  AND earlier_route.step_no < dr.step_no
                  AND earlier_route.status IN ('Waiting', 'Received', 'For Signature')
              ) THEN 1
              ELSE 0
            END AS is_actionable
     FROM document_routes AS dr
     INNER JOIN documents AS d ON d.document_id = dr.document_id
     INNER JOIN document_types AS dt ON dt.type_id = d.type_id
     INNER JOIN users AS creator ON creator.user_id = d.creator_id
     LEFT JOIN offices AS office ON office.office_id = dr.office_id
     WHERE d.document_id = ? AND dr.signatory_user_id = ?
     LIMIT 1"
);
$statement->bind_param('ii', $documentId, $userId);
$statement->execute();
$document = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$document) {
    http_response_code(404);
    die('Document not found or it is not addressed to your account.');
}

$routeStatement = $conn->prepare(
    'SELECT dr.step_no, office.office_name
     FROM document_routes AS dr
     LEFT JOIN offices AS office ON office.office_id = dr.office_id
     WHERE dr.document_id = ?
     ORDER BY dr.step_no ASC, office.office_name ASC'
);

if (!$routeStatement) {
    die('Document route query failed: ' . $conn->error);
}

$routeStatement->bind_param('i', $documentId);
$routeStatement->execute();
$documentRoutes = $routeStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$routeStatement->close();

$routeOffices = array_map(
    static fn (array $route): string => (string) ($route['office_name'] ?? 'Unassigned'),
    $documentRoutes
);
$routeSeparator = (string) $document['sending_method'] === 'Simultaneous' ? ' • ' : ' → ';
$routePath = $routeOffices === [] ? 'No route assigned' : implode($routeSeparator, $routeOffices);

$filePath = trim((string) ($document['file_path'] ?? ''));
$terminalRouteStatuses = ['Signed', 'Rejected', 'Released', 'Skipped', 'Completed'];
$canEnterRemarks = (bool) $document['is_actionable']
    && !in_array((string) $document['route_status'], $terminalRouteStatuses, true);
$isCompleted = (string) $document['document_status'] === 'Completed';
$completedAt = trim((string) ($document['completed_at'] ?? ''));
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?> - Docuflow</title>
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

    <main class="document-view-page">
      <a class="back-link page-back-link" href="member-documents.php">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Back to Documents
      </a>

      <?php if ($actionSuccess !== ''): ?>
        <div class="form-message success" role="status"><?= htmlspecialchars($actionSuccess, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <?php if ($actionError !== ''): ?>
        <div class="form-message error" role="alert"><?= htmlspecialchars($actionError, ENT_QUOTES, 'UTF-8') ?></div>
      <?php endif; ?>

      <section class="document-view-heading">
        <div class="document-view-title-row">
          <div>
            <span class="tracking-code"><?= htmlspecialchars((string) $document['tracking_code'], ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></h1>
          </div>
          <span class="status-pill"><?= htmlspecialchars((string) $document['route_status'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <dl class="document-view-meta">
          <div>
            <dt>Document Type</dt>
            <dd><?= htmlspecialchars((string) $document['type_name'], ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <div>
            <dt>Created By</dt>
            <dd><?= htmlspecialchars((string) $document['creator_name'], ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <div>
            <dt>Route Office</dt>
            <dd><?= htmlspecialchars((string) ($document['office_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <div>
            <dt>Sending Method</dt>
            <dd><?= htmlspecialchars((string) $document['sending_method'], ENT_QUOTES, 'UTF-8') ?><?= (int) $document['step_no'] > 0 ? ' · Step ' . (int) $document['step_no'] : '' ?></dd>
          </div>
          <div>
            <dt>Created</dt>
            <dd><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?></dd>
          </div>
          <?php if ($isCompleted && $completedAt !== ''): ?>
            <div>
              <dt>Total Completion Time</dt>
              <dd><?= htmlspecialchars(formatDocumentDuration((string) $document['created_at'], $completedAt), ENT_QUOTES, 'UTF-8') ?></dd>
            </div>
          <?php endif; ?>
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

      <section class="document-remarks-panel">
        <label for="actionRemarks">Action Remarks</label>
        <textarea
          id="actionRemarks"
          name="remarks"
          form="documentDecisionForm"
          maxlength="1000"
          rows="4"
          placeholder="Add an optional remark before signing or rejecting..."
          <?= $canEnterRemarks ? '' : 'readonly' ?>
        ><?= htmlspecialchars((string) ($document['remarks'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
        <small><?= $canEnterRemarks ? 'Optional. This remark will be saved when you sign or reject the document.' : 'Remarks can only be entered before completing this route.' ?></small>
      </section>

      <section class="document-action-panel">
        <div>
          <h2>Document Actions</h2>
          <p>Update your office assignment after reviewing the document.</p>
        </div>

        <?php if (!(bool) $document['is_actionable']): ?>
          <span class="route-action-complete">
            Waiting for the previous office to complete its route step.
          </span>
        <?php elseif (in_array($document['route_status'], $terminalRouteStatuses, true)): ?>
          <span class="route-action-complete">
            This office assignment is already <?= htmlspecialchars(strtolower((string) $document['route_status']), ENT_QUOTES, 'UTF-8') ?>.
          </span>
        <?php else: ?>
          <div class="document-action-buttons">
            <?php if ($document['route_status'] === 'Waiting'): ?>
              <form method="post" action="../controller/member_document_action.php">
                <input type="hidden" name="route_id" value="<?= (int) $document['route_id'] ?>" />
                <input type="hidden" name="document_id" value="<?= $documentId ?>" />
                <button class="document-action-button receive" type="submit" name="action" value="receive">
                  <i class="fas fa-inbox" aria-hidden="true"></i>
                  Mark Received
                </button>
              </form>
            <?php endif; ?>

            <form id="documentDecisionForm" class="document-decision-form" method="post" action="../controller/member_document_action.php">
              <input type="hidden" name="route_id" value="<?= (int) $document['route_id'] ?>" />
              <input type="hidden" name="document_id" value="<?= $documentId ?>" />
              <button class="document-action-button sign" type="submit" name="action" value="sign" onclick="return confirm('Sign this document<?= (int) $document['step_no'] > 0 ? ' and advance it to the next route step' : '' ?>?')">
                <i class="fas fa-pen-nib" aria-hidden="true"></i>
                Sign Document
              </button>
              <button class="document-action-button reject" type="submit" name="action" value="reject" onclick="return confirm('Reject this document?')">
                <i class="fas fa-times" aria-hidden="true"></i>
                Reject
              </button>
            </form>
          </div>
        <?php endif; ?>
      </section>
    </main>

    <script src="../js/member-dashboard.js"></script>
  </body>
</html>
