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
$statement = $conn->prepare(
    'SELECT d.document_id, d.tracking_code, d.title, d.file_path,
            d.status AS document_status, d.created_at, dt.type_name,
            creator.full_name AS creator_name, dr.status AS route_status
     FROM document_routes AS dr
     INNER JOIN documents AS d ON d.document_id = dr.document_id
     INNER JOIN document_types AS dt ON dt.type_id = d.type_id
     INNER JOIN users AS creator ON creator.user_id = d.creator_id
     WHERE dr.signatory_user_id = ?
     ORDER BY d.created_at DESC'
);

if (!$statement) {
    die('Document query failed: ' . $conn->error);
}

$statement->bind_param('i', $userId);
$statement->execute();
$documents = $statement->get_result()->fetch_all(MYSQLI_ASSOC);
$statement->close();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Documents Addressed to Me - Docuflow</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
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

        <button
          id="themeToggle"
          class="icon-button"
          type="button"
          aria-label="Toggle dark or light mode"
        >
          <i class="fas fa-sun" aria-hidden="true"></i>
        </button>

        <form
          class="logout-form"
          method="post"
          action="../controller/logout.php"
          onsubmit="return confirm('Are you sure you want to logout?')"
        >
          <button class="icon-button" type="submit" aria-label="Log out">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
          </button>
        </form>
      </div>
    </header>

    <main class="documents-page">
      <a class="back-link page-back-link" href="<?= $dashboardPage ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Go Back
      </a>

      <div class="page-heading">
        <p class="brand">Member Inbox</p>
        <h1>Documents Addressed to Me</h1>
        <p class="welcome-message">Documents routed specifically to your account.</p>
      </div>

      <section class="document-table-panel" aria-label="Documents addressed to you">
        <div class="status-filters" role="group" aria-label="Filter documents by status">
          <span class="status-filter-label">Filter by status:</span>
          <button class="status-filter active" type="button" data-status="">All</button>
          <button class="status-filter" type="button" data-status="Waiting">Waiting</button>
          <button class="status-filter" type="button" data-status="Received">Received</button>
          <button class="status-filter" type="button" data-status="For Signature">For Signature</button>
          <button class="status-filter" type="button" data-status="Signed">Signed</button>
          <button class="status-filter" type="button" data-status="Rejected">Rejected</button>
          <button class="status-filter" type="button" data-status="Released">Released</button>
          <button class="status-filter" type="button" data-status="Skipped">Skipped</button>
          <button class="status-filter" type="button" data-status="Completed">Completed</button>
        </div>

        <div class="document-table-scroll">
          <table id="memberDocumentsTable" class="display member-documents-table">
            <thead>
              <tr>
                <th>Tracking Code</th>
                <th>Title</th>
                <th>Type</th>
                <th>From</th>
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
                <td><span class="status-pill"><?= htmlspecialchars((string) $document['route_status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                <td data-order="<?= htmlspecialchars((string) $document['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                  <?= htmlspecialchars(date('M j, Y', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                </td>
                <td>
                  <a class="view-document-button table-action" href="member-document.php?id=<?= (int) $document['document_id'] ?>">
                    View
                  </a>
                </td>
              </tr>
          <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/member-dashboard.js"></script>
    <script>
      const memberDocumentsTable = new DataTable('#memberDocumentsTable', {
        pageLength: 10,
        order: [[5, 'desc']],
        columnDefs: [
          { targets: 6, orderable: false, searchable: false }
        ],
        language: {
          search: 'Search documents:',
          emptyTable: 'No documents addressed to you yet.'
        }
      });

      document.querySelectorAll('.status-filter').forEach((button) => {
        button.addEventListener('click', () => {
          const status = button.dataset.status;
          const searchValue = status
            ? `^${DataTable.util.escapeRegex(status)}$`
            : '';

          memberDocumentsTable.column(4).search(searchValue, true, false).draw();

          document.querySelectorAll('.status-filter').forEach((filter) => {
            filter.classList.toggle('active', filter === button);
          });
        });
      });
    </script>
  </body>
</html>
