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
    'SELECT d.document_id, d.tracking_code, d.title, d.status,
            d.created_at, dt.type_name,
            GROUP_CONCAT(DISTINCT office.office_name ORDER BY dr.step_no, office.office_name SEPARATOR ", ") AS route_offices,
            COUNT(dr.route_id) AS route_count
     FROM documents AS d
     INNER JOIN document_types AS dt ON dt.type_id = d.type_id
     LEFT JOIN document_routes AS dr ON dr.document_id = d.document_id
     LEFT JOIN offices AS office ON office.office_id = dr.office_id
     WHERE d.creator_id = ?
     GROUP BY d.document_id, d.tracking_code, d.title, d.status,
              d.created_at, dt.type_name
     ORDER BY d.created_at DESC'
);
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
    <title>My Documents - Docuflow</title>
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

    <main class="documents-page">
      <a class="back-link page-back-link" href="<?= $dashboardPage ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Go Back
      </a>

      <div class="page-heading">
        <p class="brand">Created by Me</p>
        <h1>My Documents</h1>
        <p class="welcome-message">Track the current status and complete route of documents you created.</p>
      </div>

      <section class="document-table-panel" aria-label="My created documents">
        <div class="document-table-scroll">
          <table id="myDocumentsTable" class="display member-documents-table">
            <thead>
              <tr>
                <th>Tracking Code</th>
                <th>Title</th>
                <th>Type</th>
                <th>Route Offices</th>
                <th>Status</th>
                <th>Routes</th>
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
                  <td><?= htmlspecialchars((string) ($document['route_offices'] ?? 'Not routed'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="status-pill"><?= htmlspecialchars((string) $document['status'], ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= (int) $document['route_count'] ?></td>
                  <td data-order="<?= htmlspecialchars((string) $document['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(date('M j, Y', strtotime((string) $document['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td>
                    <a class="view-document-button table-action" href="member-document-trail.php?id=<?= (int) $document['document_id'] ?>">
                      View Document / Remarks
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
      new DataTable('#myDocumentsTable', {
        pageLength: 10,
        order: [[6, 'desc']],
        columnDefs: [
          { targets: 7, orderable: false, searchable: false }
        ],
        language: {
          search: 'Search my documents:',
          emptyTable: 'You have not created any documents yet.'
        }
      });
    </script>
  </body>
</html>
