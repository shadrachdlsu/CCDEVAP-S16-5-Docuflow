<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Secretary') {
    header('Location: login.php');
    exit;
}

$routeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$routeId) {
    http_response_code(404);
    die('Office document assignment not found.');
}

require __DIR__ . '/../controller/db.php';

$userId = (int) $_SESSION['user_id'];
$officeId = (int) ($_SESSION['office_id'] ?? 0);
$email = (string) ($_SESSION['email'] ?? '');
$success = (string) ($_SESSION['secretary_assignment_success'] ?? '');
$error = (string) ($_SESSION['secretary_assignment_error'] ?? '');
unset($_SESSION['secretary_assignment_success'], $_SESSION['secretary_assignment_error']);

$statement = $conn->prepare(
    'SELECT dr.route_id, dr.step_no, dr.status AS route_status, dr.signatory_user_id,
            d.document_id, d.tracking_code, d.title, d.file_path, d.status AS document_status, d.created_at,
            COALESCE(dt.type_name, "Unspecified") AS type_name,
            creator.full_name AS creator_name, offices.office_name,
            assignee.full_name AS assignee_name, assignee.email AS assignee_email
     FROM document_routes AS dr
     INNER JOIN office_secretaries ON office_secretaries.office_id = dr.office_id
     INNER JOIN documents AS d ON d.document_id = dr.document_id
     LEFT JOIN document_types AS dt ON dt.type_id = d.type_id
     INNER JOIN users AS creator ON creator.user_id = d.creator_id
     INNER JOIN offices ON offices.office_id = dr.office_id
     LEFT JOIN users AS assignee ON assignee.user_id = dr.signatory_user_id
     WHERE dr.route_id = ?
       AND dr.office_id = ?
       AND office_secretaries.secretary_user_id = ?
     LIMIT 1'
);
$statement->bind_param('iii', $routeId, $officeId, $userId);
$statement->execute();
$document = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$document) {
    http_response_code(404);
    die('Document not found or you are not the Secretary in Charge for this office.');
}

$routeStatement = $conn->prepare(
    'SELECT dr.step_no, offices.office_name
     FROM document_routes AS dr
     LEFT JOIN offices ON offices.office_id = dr.office_id
     WHERE dr.document_id = ?
     ORDER BY dr.step_no ASC, offices.office_name ASC'
);

if (!$routeStatement) {
    die('Document route query failed: ' . $conn->error);
}

$documentId = (int) $document['document_id'];
$routeStatement->bind_param('i', $documentId);
$routeStatement->execute();
$documentRoutes = $routeStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$routeStatement->close();

$routeOffices = array_map(
    static fn (array $route): string => (string) ($route['office_name'] ?? 'Unassigned'),
    $documentRoutes
);
$routeSeparator = (int) $document['step_no'] === 0 ? ' • ' : ' → ';
$routePath = $routeOffices === [] ? 'No route assigned' : implode($routeSeparator, $routeOffices);

$memberStatement = $conn->prepare(
    "SELECT users.user_id, users.full_name, users.email
     FROM users
     INNER JOIN roles ON roles.role_id = users.role_id
     WHERE users.office_id = ?
       AND (roles.role_name = 'Member' OR users.user_id = ?)
       AND users.is_active = 1
       AND users.registration_status = 'Approved'
     ORDER BY users.full_name"
);
$memberStatement->bind_param('ii', $officeId, $userId);
$memberStatement->execute();
$members = $memberStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$memberStatement->close();

$memberOptions = array_map(
    static fn (array $member): array => [
        'id' => (int) $member['user_id'],
        'label' => (string) $member['full_name'] . ' — ' . (string) $member['email'],
    ],
    $members
);
$assignedMemberValue = !empty($document['signatory_user_id'])
    ? (string) $document['assignee_name'] . ' — ' . (string) $document['assignee_email']
    : '';

$filePath = trim((string) ($document['file_path'] ?? ''));
$terminalStatuses = ['Signed', 'Rejected', 'Released', 'Skipped', 'Completed'];
$canAssign = !in_array((string) $document['route_status'], $terminalStatuses, true);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assign <?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?> - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
  </head>
  <body>
    <header class="member-header">
      <a class="web-logo" href="secretary-dashboard.php">Docuflow</a>
      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role">Secretary in Charge &middot; <?= htmlspecialchars((string) $document['office_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <button id="themeToggle" class="icon-button" type="button" aria-label="Toggle dark or light mode"><i class="fas fa-sun" aria-hidden="true"></i></button>
        <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
          <button class="icon-button" type="submit" aria-label="Log out"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></button>
        </form>
      </div>
    </header>

    <main class="document-view-page">
      <a class="back-link page-back-link" href="secretary-assign-documents.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Office Documents</a>

      <?php if ($success !== ''): ?><div class="form-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="form-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <section class="document-view-heading">
        <div class="document-view-title-row">
          <div>
            <span class="tracking-code"><?= htmlspecialchars((string) $document['tracking_code'], ENT_QUOTES, 'UTF-8') ?></span>
            <h1><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></h1>
          </div>
          <span class="status-pill"><?= htmlspecialchars((string) $document['route_status'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <dl class="document-view-meta">
          <div><dt>Document Type</dt><dd><?= htmlspecialchars((string) $document['type_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Created By</dt><dd><?= htmlspecialchars((string) $document['creator_name'], ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Route Step</dt><dd><?= (int) $document['step_no'] === 0 ? 'Independent' : (int) $document['step_no'] ?></dd></div>
          <div><dt>Assigned To</dt><dd><?= htmlspecialchars((string) ($document['assignee_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Created</dt><dd><?= htmlspecialchars(date('M j, Y g:i A', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div class="document-route-path"><dt>Route Path</dt><dd><?= htmlspecialchars($routePath, ENT_QUOTES, 'UTF-8') ?></dd></div>
        </dl>
      </section>

      <?php if ($filePath !== ''): ?>
        <section class="document-preview">
          <div class="document-preview-toolbar">
            <h2>Document Preview</h2>
            <a class="view-document-button" href="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>" target="_blank" rel="noopener"><i class="fas fa-external-link-alt" aria-hidden="true"></i> Open in New Tab</a>
          </div>
          <iframe src="<?= htmlspecialchars($filePath, ENT_QUOTES, 'UTF-8') ?>" title="Preview of <?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?>"></iframe>
        </section>
      <?php endif; ?>

      <section class="document-action-panel assignment-panel">
        <div>
          <h2>Document Assignment</h2>
          <p>Choose yourself or an active member from <?= htmlspecialchars((string) $document['office_name'], ENT_QUOTES, 'UTF-8') ?>.</p>
        </div>

        <?php if (!$canAssign): ?>
          <span class="route-action-complete">This route is already <?= htmlspecialchars(strtolower((string) $document['route_status']), ENT_QUOTES, 'UTF-8') ?> and can no longer be reassigned.</span>
        <?php else: ?>
          <form class="secretary-assignment-form" method="post" action="../controller/secretary_assign_document.php">
            <input type="hidden" name="route_id" value="<?= (int) $document['route_id'] ?>" />
            <input id="assignedMemberId" type="hidden" name="member_user_id" value="<?= (int) ($document['signatory_user_id'] ?? 0) ?>" />
            <label for="assignedMemberSearch">Assign to Secretary or Member</label>
            <div class="assignment-search-field">
              <input
                id="assignedMemberSearch"
                type="text"
                list="officeMembers"
                value="<?= htmlspecialchars($assignedMemberValue, ENT_QUOTES, 'UTF-8') ?>"
                placeholder="Start typing your name or a member name"
                autocomplete="off"
              />
              <datalist id="officeMembers">
              <?php foreach ($members as $member): ?>
                  <option value="<?= htmlspecialchars((string) $member['full_name'] . ' — ' . (string) $member['email'], ENT_QUOTES, 'UTF-8') ?>"></option>
              <?php endforeach; ?>
              </datalist>
              <small>Select yourself or an office member, or clear the box to leave the document unassigned.</small>
            </div>
            <button class="document-action-button sign" type="submit"><i class="fas fa-user-check" aria-hidden="true"></i> Save Assignment</button>
          </form>
        <?php endif; ?>
      </section>
    </main>

    <script src="../js/member-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/member-dashboard.js') ?>"></script>
    <script>
      const memberOptions = <?= json_encode($memberOptions, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
      const assignmentForm = document.querySelector('.secretary-assignment-form');
      const memberSearch = document.getElementById('assignedMemberSearch');
      const memberId = document.getElementById('assignedMemberId');

      const findSelectedMember = () => {
        const searchValue = memberSearch.value.trim().toLocaleLowerCase();
        return memberOptions.find((member) => member.label.toLocaleLowerCase() === searchValue);
      };

      memberSearch?.addEventListener('input', () => {
        const selectedMember = findSelectedMember();
        memberId.value = selectedMember?.id ?? 0;
        memberSearch.setCustomValidity('');
      });

      assignmentForm?.addEventListener('submit', (event) => {
        const selectedMember = findSelectedMember();

        if (memberSearch.value.trim() !== '' && !selectedMember) {
          event.preventDefault();
          memberSearch.setCustomValidity('Select yourself or an office member from the suggestions.');
          memberSearch.reportValidity();
          return;
        }

        memberId.value = selectedMember?.id ?? 0;
      });
    </script>
  </body>
</html>
