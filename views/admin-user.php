<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

$userId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$userId) {
    http_response_code(404);
    die('User not found.');
}

require __DIR__ . '/../controller/db.php';

$statement = $conn->prepare(
    'SELECT user_id, full_name, email, role_id, office_id, is_active, registration_status, created_at
     FROM users WHERE user_id = ? LIMIT 1'
);
$statement->bind_param('i', $userId);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$user) {
    http_response_code(404);
    die('User not found.');
}

$roles = $conn->query('SELECT role_id, role_name FROM roles ORDER BY role_name')->fetch_all(MYSQLI_ASSOC);
$offices = $conn->query('SELECT office_id, office_name FROM offices ORDER BY office_name')->fetch_all(MYSQLI_ASSOC);
$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
$success = (string) ($_SESSION['admin_user_success'] ?? '');
$error = (string) ($_SESSION['admin_user_error'] ?? '');
$accountStatus = in_array($user['registration_status'], ['Pending', 'Rejected'], true)
    ? (string) $user['registration_status']
    : ((bool) $user['is_active'] ? 'Active' : 'Inactive');
unset($_SESSION['admin_user_success'], $_SESSION['admin_user_error']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit User - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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
          <button class="icon-btn toggle-theme" id="themeToggle" type="button" aria-label="Toggle dark/light mode"><i class="fas fa-moon"></i></button>
          <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
            <button class="icon-btn" type="submit" aria-label="Exit / Logout"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
      </div>
    </header>

    <main class="admin-page admin-user-page">
      <a class="admin-back-button" href="admin-manage-users.php">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Back to Users
      </a>

      <div class="admin-page-heading">
        <p>User #<?= (int) $user['user_id'] ?></p>
        <h1><?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <span>Created <?= htmlspecialchars(date('M j, Y', strtotime((string) $user['created_at'])), ENT_QUOTES, 'UTF-8') ?></span>
      </div>

      <section class="admin-form-panel">
        <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <form class="admin-user-form" method="post" action="../controller/admin_update_user.php">
          <input type="hidden" name="user_id" value="<?= (int) $user['user_id'] ?>" />

          <div class="admin-form-field">
            <label for="fullName">Full Name</label>
            <input id="fullName" name="full_name" type="text" maxlength="100" value="<?= htmlspecialchars((string) $user['full_name'], ENT_QUOTES, 'UTF-8') ?>" required />
          </div>

          <div class="admin-form-field">
            <label for="userEmail">Email Address</label>
            <input id="userEmail" name="email" type="email" maxlength="100" value="<?= htmlspecialchars((string) $user['email'], ENT_QUOTES, 'UTF-8') ?>" required />
          </div>

          <div class="admin-form-field">
            <label for="userRole">Role</label>
            <select id="userRole" name="role_id" required>
              <?php foreach ($roles as $role): ?>
                <option value="<?= (int) $role['role_id'] ?>" <?= (int) $user['role_id'] === (int) $role['role_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) $role['role_name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="admin-form-field">
            <label for="userOffice">Office</label>
            <select id="userOffice" name="office_id">
              <option value="0">No office</option>
              <?php foreach ($offices as $office): ?>
                <option value="<?= (int) $office['office_id'] ?>" <?= (int) ($user['office_id'] ?? 0) === (int) $office['office_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="admin-form-field">
            <label for="userStatus">Account Status</label>
            <select id="userStatus" name="account_status" required>
              <?php foreach (['Pending', 'Active', 'Inactive', 'Rejected'] as $status): ?>
                <option value="<?= $status ?>" <?= $accountStatus === $status ? 'selected' : '' ?>><?= $status ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="admin-form-actions">
            <button class="admin-save-button" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Save Changes</button>
          </div>
        </form>
      </section>
    </main>

    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
  </body>
</html>
