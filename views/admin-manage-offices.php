<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/office.php';
require_once __DIR__ . '/../helpers/csrf.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
$success = (string) ($_SESSION['admin_office_success'] ?? '');
$error = (string) ($_SESSION['admin_office_error'] ?? '');
$oldOfficeName = (string) ($_SESSION['admin_office_name'] ?? '');
unset($_SESSION['admin_office_success'], $_SESSION['admin_office_error'], $_SESSION['admin_office_name']);

$offices = (new Office())->getAdminDirectory();
$csrfToken = docuflow_csrf_token();
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
                <th>Status</th>
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
                  <td>
                    <form class="admin-inline-form admin-office-status-form" method="post" action="../controller/admin_office_action.php">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                      <input type="hidden" name="action" value="toggle_status" />
                      <input type="hidden" name="office_id" value="<?= (int) $office['office_id'] ?>" />
                      <input class="admin-office-status-value" type="hidden" name="is_active" value="<?= (bool) $office['is_active'] ? 1 : 0 ?>" />
                      <label class="admin-status-toggle">
                        <input
                          class="admin-status-toggle-input admin-office-status-input"
                          type="checkbox"
                          <?= (bool) $office['is_active'] ? 'checked' : '' ?>
                          aria-label="<?= (bool) $office['is_active'] ? 'Deactivate' : 'Activate' ?> <?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>"
                        />
                        <span class="admin-status-toggle-track" aria-hidden="true"><span class="admin-status-toggle-thumb"></span></span>
                        <span class="admin-status-toggle-label"><?= (bool) $office['is_active'] ? 'Active' : 'Inactive' ?></span>
                      </label>
                    </form>
                  </td>
                  <td>
                    <div class="admin-action-group">
                      <a class="admin-table-action" href="admin-office.php?id=<?= (int) $office['office_id'] ?>">View / Edit</a>
                      <form class="admin-delete-office-form" method="post" action="../controller/admin_office_action.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                        <input type="hidden" name="action" value="delete" />
                        <input type="hidden" name="office_id" value="<?= (int) $office['office_id'] ?>" />
                        <button class="admin-icon-delete-button" type="submit" aria-label="Delete <?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>" data-office-name="<?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>">
                          <i class="fas fa-trash" aria-hidden="true"></i>
                        </button>
                      </form>
                    </div>
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
      new DataTable('#adminOfficesTable', {
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [{ targets: [5, 6], orderable: false, searchable: false }],
        language: {
          search: 'Search offices:',
          emptyTable: 'No offices were found.'
        }
      });

      document.querySelector('#adminOfficesTable').addEventListener('change', (event) => {
        const toggle = event.target.closest('.admin-office-status-input');
        if (!toggle) {
          return;
        }

        const form = toggle.closest('.admin-office-status-form');
        const officeName = toggle.getAttribute('aria-label').replace(/^(Activate|Deactivate)\s+/, '');
        const action = toggle.checked ? 'activate' : 'deactivate';

        if (!window.confirm(`Are you sure you want to ${action} ${officeName}?`)) {
          toggle.checked = !toggle.checked;
          return;
        }

        form.querySelector('.admin-office-status-value').value = toggle.checked ? '1' : '0';
        toggle.disabled = true;
        form.submit();
      });

      document.querySelector('#adminOfficesTable').addEventListener('submit', (event) => {
        const form = event.target.closest('.admin-delete-office-form');
        if (!form) {
          return;
        }

        const officeName = form.querySelector('button').dataset.officeName || 'this office';
        if (!window.confirm(`Delete ${officeName}? Deletion is allowed only when the office has no users, document routes, or other activity.`)) {
          event.preventDefault();
        }
      });
    </script>
  </body>
</html>
