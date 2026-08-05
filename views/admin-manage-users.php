<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/user.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
$success = (string) ($_SESSION['admin_user_success'] ?? '');
$error = (string) ($_SESSION['admin_user_error'] ?? '');
unset($_SESSION['admin_user_success'], $_SESSION['admin_user_error']);
$users = (new User())->getAdminList();
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
          <form class="logout-form" method="post" action="../controllers/UserLogoutController.php" onsubmit="return confirm('Are you sure you want to logout?')">
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

      <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

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
                    <?php $isCurrentAdmin = (int) $user['user_id'] === (int) $_SESSION['user_id']; ?>
                    <form class="admin-inline-form admin-status-toggle-form" method="post" action="../controllers/AdminToggleUserStatusController.php">
                      <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>" />
                      <input class="admin-status-toggle-value" type="hidden" name="is_active" value="<?= (bool) $user['is_active'] ? 1 : 0 ?>" />
                      <label class="admin-status-toggle<?= $isCurrentAdmin ? ' is-locked' : '' ?>">
                        <input
                          class="admin-status-toggle-input"
                          type="checkbox"
                          <?= (bool) $user['is_active'] ? 'checked' : '' ?>
                          <?= $isCurrentAdmin ? 'disabled' : '' ?>
                          aria-label="<?= (bool) $user['is_active'] ? 'Deactivate' : 'Activate' ?> <?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                        />
                        <span class="admin-status-toggle-track" aria-hidden="true">
                          <span class="admin-status-toggle-thumb"></span>
                        </span>
                        <span class="admin-status-toggle-label"><?= htmlspecialchars($accountStatus, ENT_QUOTES, 'UTF-8') ?></span>
                      </label>
                    </form>
                  </td>
                  <td data-order="<?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?>">
                    <?= htmlspecialchars(date('M j, Y', strtotime((string) $user['created_at'])), ENT_QUOTES, 'UTF-8') ?>
                  </td>
                  <td>
                    <div class="admin-action-group">
                      <a class="admin-table-action" href="admin-user.php?id=<?= (int) $user['user_id'] ?>">View / Edit</a>
                      <div class="admin-action-menu">
                        <button
                          class="admin-action-menu-trigger"
                          type="button"
                          aria-label="Quick actions for <?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                          aria-haspopup="menu"
                          aria-expanded="false"
                        >
                          <i class="fas fa-ellipsis-vertical" aria-hidden="true"></i>
                        </button>
                        <div class="admin-action-menu-panel" role="menu" hidden>
                          <button
                            class="admin-action-menu-item admin-reset-password-trigger"
                            type="button"
                            role="menuitem"
                            data-user-id="<?= (int) $user['user_id'] ?>"
                            data-user-name="<?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                          >
                            <i class="fas fa-key" aria-hidden="true"></i>
                            Reset password
                          </button>
                          <div class="admin-action-menu-divider" role="separator"></div>
                          <?php if ($isCurrentAdmin): ?>
                            <span class="admin-action-menu-item danger disabled" role="menuitem" aria-disabled="true" title="You cannot delete your own account">
                              <i class="fas fa-trash" aria-hidden="true"></i>
                              Delete user
                            </span>
                          <?php else: ?>
                            <form class="admin-delete-user-form" method="post" action="../controllers/AdminUserActionController.php">
                              <input type="hidden" name="action" value="delete" />
                              <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>" />
                              <button class="admin-action-menu-item danger" type="submit" role="menuitem" data-user-name="<?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?>">
                                <i class="fas fa-trash" aria-hidden="true"></i>
                                Delete user
                              </button>
                            </form>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <div class="admin-modal" id="resetPasswordModal" role="dialog" aria-modal="true" aria-labelledby="resetPasswordTitle" hidden>
      <div class="admin-modal-backdrop" data-close-modal></div>
      <div class="admin-modal-panel">
        <button class="admin-modal-close" type="button" data-close-modal aria-label="Close reset password dialog"><i class="fas fa-xmark" aria-hidden="true"></i></button>
        <span class="admin-modal-icon"><i class="fas fa-key" aria-hidden="true"></i></span>
        <h2 id="resetPasswordTitle">Reset password</h2>
        <p>Set a new password for <strong id="resetPasswordUserName"></strong>.</p>

        <form class="admin-modal-form" method="post" action="../controllers/AdminUserActionController.php">
          <input type="hidden" name="action" value="reset_password" />
          <input id="resetPasswordUserId" type="hidden" name="user_id" value="" />
          <div class="admin-form-field">
            <label for="newPassword">New password</label>
            <input id="newPassword" name="new_password" type="password" minlength="8" autocomplete="new-password" required />
          </div>
          <div class="admin-form-field">
            <label for="confirmPassword">Confirm password</label>
            <input id="confirmPassword" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required />
          </div>
          <div class="admin-modal-actions">
            <button class="admin-secondary-button" type="button" data-close-modal>Cancel</button>
            <button class="admin-save-button" type="submit"><i class="fas fa-key" aria-hidden="true"></i> Reset password</button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
    <script src="../js/admin-users.js?v=<?= filemtime(__DIR__ . '/../js/admin-users.js') ?>"></script>
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

      document.querySelector('#adminUsersTable').addEventListener('change', (event) => {
        const toggle = event.target.closest('.admin-status-toggle-input');
        if (!toggle) {
          return;
        }

        const form = toggle.closest('.admin-status-toggle-form');
        const nextStatus = toggle.checked ? 'activate' : 'deactivate';

        if (!window.confirm(`Are you sure you want to ${nextStatus} this user account?`)) {
          toggle.checked = !toggle.checked;
          return;
        }

        form.querySelector('.admin-status-toggle-value').value = toggle.checked ? '1' : '0';
        toggle.disabled = true;
        form.submit();
      });
    </script>
  </body>
</html>
