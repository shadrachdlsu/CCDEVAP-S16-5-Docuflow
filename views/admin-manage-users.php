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
$users = $conn->query(
    'SELECT u.user_id, u.full_name, u.email, u.is_active, u.registration_status, u.created_at,
            role.role_name, office.office_name
     FROM users AS u
     INNER JOIN roles AS role ON role.role_id = u.role_id
     LEFT JOIN offices AS office ON office.office_id = u.office_id
     ORDER BY u.full_name'
)->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Users - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="../css/admin-dashboard.css?v=<?= filemtime(__DIR__ . '/../css/admin-dashboard.css') ?>" />
  </head>
  <body class="admin-body">
    <header class="admin-header">
      <div class="header-left"><span class="web-logo">Docuflow</span></div>
      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?> &middot; Administrator</span>
        </div>
        <div class="header-actions">
          <button class="icon-btn toggle-theme" id="themeToggle" type="button" aria-label="Toggle dark/light mode">
            <i class="fas fa-moon"></i>
          </button>
          <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
            <button class="icon-btn" type="submit" aria-label="Exit / Logout"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
      </div>
    </header>

    <main class="admin-page">
      <a class="admin-back-button" href="admin-dashboard.php">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Go Back
      </a>

      <div class="admin-page-heading">
        <p>Administration</p>
        <h1>Manage Users</h1>
        <span>View and maintain Docuflow user accounts.</span>
      </div>

      <section class="admin-table-panel">
        <div class="admin-table-scroll">
          <table id="adminUsersTable" class="display admin-data-table">
            <thead>
              <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Office</th>
                <th>Status</th>
                <th>Created</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($users as $user): ?>
                <?php
                  $accountStatus = in_array($user['registration_status'], ['Pending', 'Rejected'], true)
                      ? (string) $user['registration_status']
                      : ((bool) $user['is_active'] ? 'Active' : 'Inactive');
                ?>
                <tr>
                  <td><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) $user['role_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($user['office_name'] ?? 'No office'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <span class="admin-status-pill <?= strtolower($accountStatus) ?>">
                      <?= htmlspecialchars($accountStatus, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                  </td>
                  <td data-order="<?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(date('M j, Y', strtotime((string) $user['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td>
                    <a class="admin-table-action" href="admin-user.php?id=<?= (int) $user['user_id'] ?>">View / Edit</a>
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
    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
    <script>
      new DataTable('#adminUsersTable', {
        pageLength: 10,
        order: [[0, 'asc']],
        columnDefs: [{ targets: 6, orderable: false, searchable: false }],
        language: {
          search: 'Search users:',
          emptyTable: 'No users were found.'
        }
      });
    </script>
  </body>
</html>
