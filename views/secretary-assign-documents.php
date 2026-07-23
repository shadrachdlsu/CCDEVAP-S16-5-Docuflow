<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Secretary') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../controller/db.php';

$userId = (int) $_SESSION['user_id'];
$officeId = (int) ($_SESSION['office_id'] ?? 0);
$email = (string) ($_SESSION['email'] ?? '');

$officeStatement = $conn->prepare(
    'SELECT offices.office_name
     FROM office_secretaries
     INNER JOIN offices ON offices.office_id = office_secretaries.office_id
     WHERE office_secretaries.office_id = ?
       AND office_secretaries.secretary_user_id = ?
     LIMIT 1'
);
$officeStatement->bind_param('ii', $officeId, $userId);
$officeStatement->execute();
$office = $officeStatement->get_result()->fetch_assoc();
$officeStatement->close();

if (!$office) {
    http_response_code(403);
    die('Only the Secretary in Charge can assign this office\'s documents.');
}

$statement = $conn->prepare(
    'SELECT dr.route_id, dr.step_no, dr.status AS route_status,
            d.tracking_code, d.title, d.created_at,
            COALESCE(dt.type_name, "Unspecified") AS type_name,
            creator.full_name AS creator_name,
            assignee.full_name AS assignee_name
     FROM document_routes AS dr
     INNER JOIN documents AS d ON d.document_id = dr.document_id
     LEFT JOIN document_types AS dt ON dt.type_id = d.type_id
     INNER JOIN users AS creator ON creator.user_id = d.creator_id
     LEFT JOIN users AS assignee ON assignee.user_id = dr.signatory_user_id
     WHERE dr.office_id = ?
     ORDER BY d.created_at DESC'
);
$statement->bind_param('i', $officeId);
$statement->execute();
$documents = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
$statement->close();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Assign Documents - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
  </head>
  <body>
    <header class="member-header">
      <a class="web-logo" href="secretary-dashboard.php">Docuflow</a>
      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role">Secretary in Charge &middot; <?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <button id="themeToggle" class="icon-button" type="button" aria-label="Toggle dark or light mode"><i class="fas fa-sun" aria-hidden="true"></i></button>
        <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
          <button class="icon-button" type="submit" aria-label="Log out"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></button>
        </form>
      </div>
    </header>

    <main class="documents-page secretary-assignment-page">
      <a class="back-link page-back-link" href="secretary-dashboard.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="page-heading">
        <p class="brand"><?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?></p>
        <h1>Assign Documents</h1>
        <p class="welcome-message">View every document routed to your office and assign it to yourself or an office member.</p>
      </div>

      <section class="document-table-panel" aria-label="Documents assigned to this office">
        <div class="status-filters" role="group" aria-label="Filter office documents by status">
          <span class="status-filter-label">Filter by status:</span>
          <button class="status-filter active" type="button" data-status="">All</button>
          <button class="status-filter" type="button" data-status="Waiting">Waiting</button>
          <button class="status-filter" type="button" data-status="Received">Received</button>
          <button class="status-filter" type="button" data-status="Signed">Signed</button>
          <button class="status-filter" type="button" data-status="Rejected">Rejected</button>
          <button class="status-filter" type="button" data-status="Completed">Completed</button>
        </div>

        <div class="document-table-scroll">
          <table id="secretaryOfficeDocumentsTable" class="display member-documents-table">
            <thead>
              <tr>
                <th>Tracking Code</th>
                <th>Title</th>
                <th>Type</th>
                <th>Created By</th>
                <th>Step</th>
                <th>Assigned To</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $document): ?>
                <tr>
                  <td><span class="tracking-code"><?= htmlspecialchars((string) $document['tracking_code'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $document['type_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $document['creator_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int) $document['step_no'] === 0 ? 'Independent' : (int) $document['step_no'] ?></td>
                  <td><?= htmlspecialchars((string) ($document['assignee_name'] ?? 'Unassigned'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="status-pill"><?= htmlspecialchars((string) $document['route_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td data-order="<?= htmlspecialchars((string) $document['created_at'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars(date('M j, Y', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><a class="view-document-button table-action" href="secretary-assign-document.php?id=<?= (int) $document['route_id'] ?>">View / Assign</a></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/member-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/member-dashboard.js') ?>"></script>
    <script>
      const officeDocumentsTable = new DataTable('#secretaryOfficeDocumentsTable', {
        pageLength: 10,
        order: [[7, 'desc']],
        columnDefs: [{ targets: 8, orderable: false, searchable: false }],
        language: { search: 'Search office documents:', emptyTable: 'No documents are assigned to this office.' }
      });

      document.querySelectorAll('.status-filter').forEach((button) => {
        button.addEventListener('click', () => {
          const status = button.dataset.status;
          const searchValue = status ? `^${DataTable.util.escapeRegex(status)}$` : '';
          officeDocumentsTable.column(6).search(searchValue, true, false).draw();
          document.querySelectorAll('.status-filter').forEach((filter) => filter.classList.toggle('active', filter === button));
        });
      });
    </script>
  </body>
</html>
