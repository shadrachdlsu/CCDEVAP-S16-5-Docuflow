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
$documents = $conn->query(
    'SELECT d.document_id, d.tracking_code, d.title, d.status, d.created_at,
            COALESCE(dt.type_name, "Unspecified") AS type_name,
            COALESCE(creator.full_name, "Unknown user") AS creator_name,
            GROUP_CONCAT(DISTINCT office.office_name ORDER BY dr.step_no, office.office_name SEPARATOR ", ") AS route_offices,
            COUNT(dr.route_id) AS route_count
     FROM documents AS d
     LEFT JOIN document_types AS dt ON dt.type_id = d.type_id
     LEFT JOIN users AS creator ON creator.user_id = d.creator_id
     LEFT JOIN document_routes AS dr ON dr.document_id = d.document_id
     LEFT JOIN offices AS office ON office.office_id = dr.office_id
     GROUP BY d.document_id, d.tracking_code, d.title, d.status, d.created_at,
              dt.type_name, creator.full_name
     ORDER BY d.created_at DESC'
)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>View Documents - Docuflow</title>
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
      <a class="admin-back-button" href="admin-dashboard.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="admin-page-heading">
        <p>Administration</p>
        <h1>View Documents</h1>
        <span>Search, filter, and view every document in Docuflow.</span>
      </div>

      <section class="admin-table-panel" aria-label="All system documents">
        <div class="admin-status-filters" role="group" aria-label="Filter documents by status">
          <span>Filter by status:</span>
          <button class="admin-status-filter active" type="button" data-status="">All</button>
          <button class="admin-status-filter" type="button" data-status="Pending">Pending</button>
          <button class="admin-status-filter" type="button" data-status="Released">Released</button>
          <button class="admin-status-filter" type="button" data-status="Signed">Signed</button>
          <button class="admin-status-filter" type="button" data-status="Rejected">Rejected</button>
          <button class="admin-status-filter" type="button" data-status="Completed">Completed</button>
        </div>

        <div class="admin-table-scroll">
          <table id="adminDocumentsTable" class="display admin-data-table">
            <thead>
              <tr>
                <th>Tracking Code</th>
                <th>Title</th>
                <th>Type</th>
                <th>Created By</th>
                <th>Route Offices</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documents as $document): ?>
                <?php $statusClass = strtolower((string) $document['status']); ?>
                <tr>
                  <td><span class="admin-tracking-code"><?= htmlspecialchars((string) $document['tracking_code'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars((string) $document['title'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $document['type_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $document['creator_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($document['route_offices'] ?? 'Not routed'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="admin-status-pill <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars((string) $document['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td data-order="<?= htmlspecialchars((string) $document['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(date('M j, Y', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td><a class="admin-table-action" href="admin-document.php?id=<?= (int) $document['document_id'] ?>">View</a></td>
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
      const adminDocumentsTable = new DataTable('#adminDocumentsTable', {
        pageLength: 10,
        order: [[6, 'desc']],
        columnDefs: [{ targets: 7, orderable: false, searchable: false }],
        language: {
          search: 'Search documents:',
          emptyTable: 'No documents were found.'
        }
      });

      document.querySelectorAll('.admin-status-filter').forEach((button) => {
        button.addEventListener('click', () => {
          const status = button.dataset.status;
          const searchValue = status ? `^${DataTable.util.escapeRegex(status)}$` : '';
          adminDocumentsTable.column(5).search(searchValue, true, false).draw();

          document.querySelectorAll('.admin-status-filter').forEach((filter) => {
            filter.classList.toggle('active', filter === button);
          });
        });
      });
    </script>
  </body>
</html>
