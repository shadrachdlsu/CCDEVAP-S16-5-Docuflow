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

require_once __DIR__ . '/../models/documentRoute.php';
require_once __DIR__ . '/../models/documentTrail.php';
require_once __DIR__ . '/../controllers/SharedDocumentDurationController.php';
require_once __DIR__ . '/../helpers/document_files.php';

$userId = (int) $_SESSION['user_id'];
$email = (string) ($_SESSION['email'] ?? '');
$actionSuccess = (string) ($_SESSION['document_action_success'] ?? '');
$actionError = (string) ($_SESSION['document_action_error'] ?? '');
unset($_SESSION['document_action_success'], $_SESSION['document_action_error']);
$documentRouteModel = new DocumentRoute();
$document = $documentRouteModel->getAddressedDocument((int) $documentId, $userId);

if (!$document) {
    http_response_code(404);
    die('Document not found or it is not addressed to your account.');
}

$documentRoutes = $documentRouteModel->getOfficePath((int) $documentId);
$auditTrail = (new DocumentTrail())->getByDocument((int) $documentId);

$routeOffices = array_map(
    static fn (array $route): string => (string) ($route['office_name'] ?? 'Unassigned'),
    $documentRoutes
);
$routeSeparator = (string) $document['sending_method'] === 'Simultaneous' ? ' • ' : ' → ';
$routePath = $routeOffices === [] ? 'No route assigned' : implode($routeSeparator, $routeOffices);

$filePath = docuflow_document_file_url($document['file_path'] ?? null);
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

        <form class="logout-form" method="post" action="../controllers/UserLogoutController.php" onsubmit="return confirm('Are you sure you want to logout?')">
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
              <form method="post" action="../controllers/MemberDocumentActionController.php">
                <input type="hidden" name="route_id" value="<?= (int) $document['route_id'] ?>" />
                <input type="hidden" name="document_id" value="<?= $documentId ?>" />
                <button class="document-action-button receive" type="submit" name="action" value="receive">
                  <i class="fas fa-inbox" aria-hidden="true"></i>
                  Mark Received
                </button>
              </form>
            <?php endif; ?>

            <form id="documentDecisionForm" class="document-decision-form" method="post" action="../controllers/MemberDocumentActionController.php">
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

      <section class="audit-timeline-panel" aria-labelledby="audit-trail-title">
        <div class="trail-table-heading">
          <div>
            <h2 id="audit-trail-title">Audit Trail</h2>
            <p>Recorded document actions are permanent and separate from the planned route.</p>
          </div>
        </div>

        <ol class="audit-timeline">
          <?php foreach ($auditTrail as $entry): ?>
            <li>
              <span class="audit-timeline-icon"><i class="fas fa-check" aria-hidden="true"></i></span>
              <div class="audit-timeline-copy">
                <div>
                  <strong><?= htmlspecialchars((string) $entry['action'], ENT_QUOTES, 'UTF-8') ?></strong>
                  <time datetime="<?= htmlspecialchars((string) $entry['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $entry['created_at'])), ENT_QUOTES, 'UTF-8') ?></time>
                </div>
                <span><?= htmlspecialchars((string) $entry['action_by_name'], ENT_QUOTES, 'UTF-8') ?></span>
                <small><?= htmlspecialchars((string) ($entry['remarks'] ?: 'No remarks'), ENT_QUOTES, 'UTF-8') ?></small>
              </div>
            </li>
          <?php endforeach; ?>

          <?php if ($auditTrail === []): ?>
            <li class="audit-timeline-empty">No audit events have been recorded for this document.</li>
          <?php endif; ?>
        </ol>
      </section>
    </main>

    <script src="../js/member-dashboard.js"></script>
  </body>
</html>
