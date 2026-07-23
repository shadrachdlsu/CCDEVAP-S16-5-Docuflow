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
$success = (string) ($_SESSION['admin_office_success'] ?? '');
$error = (string) ($_SESSION['admin_office_error'] ?? '');
$oldOfficeName = (string) ($_SESSION['admin_office_name'] ?? '');
unset($_SESSION['admin_office_success'], $_SESSION['admin_office_error'], $_SESSION['admin_office_name']);

$offices = $conn->query(
    'SELECT office.office_id, office.office_name,
            secretary.full_name AS secretary_name,
            COUNT(DISTINCT office_user.user_id) AS user_count,
            COUNT(DISTINCT document_routes.route_id) AS route_count
     FROM offices AS office
     LEFT JOIN office_secretaries AS office_secretary ON office_secretary.office_id = office.office_id
     LEFT JOIN users AS secretary ON secretary.user_id = office_secretary.secretary_user_id
     LEFT JOIN users AS office_user ON office_user.office_id = office.office_id
     LEFT JOIN document_routes ON document_routes.office_id = office.office_id
     GROUP BY office.office_id, office.office_name, secretary.full_name
     ORDER BY office.office_name'
)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Offices - Docuflow</title>
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

    <main class="admin-page admin-offices-page">
      <a class="admin-back-button" href="admin-dashboard.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="admin-page-heading">
        <p>Administration</p>
        <h1>Manage Offices</h1>
        <span>Add offices and maintain the names used for users and document routing.</span>
      </div>

      <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <section class="admin-form-panel admin-office-create-panel" aria-labelledby="add-office-title">
        <div>
          <h2 id="add-office-title">Add Office</h2>
          <p>Create another destination for users and document routes.</p>
        </div>
        <form class="admin-office-create-form" method="post" action="../controller/admin_save_office.php">
          <div class="admin-form-field">
            <label for="officeName">Office Name</label>
            <input id="officeName" name="office_name" type="text" maxlength="100" value="<?= htmlspecialchars($oldOfficeName, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Records Office" required />
          </div>
          <button class="admin-save-button" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> Add Office</button>
        </form>
      </section>

      <section class="admin-table-panel" aria-label="System offices">
        <div class="admin-table-scroll">
          <table id="adminOfficesTable" class="display admin-data-table admin-offices-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Office Name</th>
                <th>Secretary in Charge</th>
                <th>Assigned Users</th>
                <th>Document Routes</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($offices as $office): ?>
                <tr>
                  <td><?= (int) $office['office_id'] ?></td>
                  <td><?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($office['secretary_name'] ?? 'Not assigned'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= (int) $office['user_count'] ?></td>
                  <td><?= (int) $office['route_count'] ?></td>
                  <td><a class="admin-table-action" href="admin-office.php?id=<?= (int) $office['office_id'] ?>">View / Edit</a></td>
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
      new DataTable('#adminOfficesTable', {
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [{ targets: 5, orderable: false, searchable: false }],
        language: {
          search: 'Search offices:',
          emptyTable: 'No offices were found.'
        }
      });
    </script>
  </body>
</html>
