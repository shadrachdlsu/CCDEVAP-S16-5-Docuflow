<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

$officeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$officeId) {
    http_response_code(404);
    die('Office not found.');
}

require __DIR__ . '/../controller/db.php';

$statement = $conn->prepare(
    'SELECT office.office_id, office.office_name, office_secretary.secretary_user_id,
            COUNT(DISTINCT office_user.user_id) AS user_count,
            COUNT(DISTINCT document_routes.route_id) AS route_count
     FROM offices AS office
     LEFT JOIN office_secretaries AS office_secretary ON office_secretary.office_id = office.office_id
     LEFT JOIN users AS office_user ON office_user.office_id = office.office_id
     LEFT JOIN document_routes ON document_routes.office_id = office.office_id
     WHERE office.office_id = ?
     GROUP BY office.office_id, office.office_name, office_secretary.secretary_user_id
     LIMIT 1'
);
$statement->bind_param('i', $officeId);
$statement->execute();
$office = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$office) {
    http_response_code(404);
    die('Office not found.');
}

$secretaryStatement = $conn->prepare(
    "SELECT users.user_id, users.full_name, users.email
     FROM users
     INNER JOIN roles ON roles.role_id = users.role_id
     WHERE users.office_id = ?
       AND roles.role_name = 'Secretary'
       AND users.is_active = 1
       AND users.registration_status = 'Approved'
     ORDER BY users.full_name"
);
$secretaryStatement->bind_param('i', $officeId);
$secretaryStatement->execute();
$secretaries = $secretaryStatement->get_result()->fetch_all(MYSQLI_ASSOC);
$secretaryStatement->close();

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
$success = (string) ($_SESSION['admin_office_success'] ?? '');
$error = (string) ($_SESSION['admin_office_error'] ?? '');
unset($_SESSION['admin_office_success'], $_SESSION['admin_office_error']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Edit Office - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
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

    <main class="admin-page admin-user-page">
      <a class="admin-back-button" href="admin-manage-offices.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Back to Offices</a>

      <div class="admin-page-heading">
        <p>Office #<?= (int) $office['office_id'] ?></p>
        <h1><?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?></h1>
        <span><?= (int) $office['user_count'] ?> assigned user<?= (int) $office['user_count'] === 1 ? '' : 's' ?> &middot; <?= (int) $office['route_count'] ?> document route<?= (int) $office['route_count'] === 1 ? '' : 's' ?></span>
      </div>

      <section class="admin-form-panel">
        <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
        <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

        <form class="admin-user-form" method="post" action="../controller/admin_save_office.php">
          <input type="hidden" name="office_id" value="<?= (int) $office['office_id'] ?>" />
          <div class="admin-form-field">
            <label for="officeName">Office Name</label>
            <input id="officeName" name="office_name" type="text" maxlength="100" value="<?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>" required />
          </div>
          <div class="admin-form-field">
            <label for="secretaryUser">Secretary in Charge</label>
            <select id="secretaryUser" name="secretary_user_id">
              <option value="0">Not assigned</option>
              <?php foreach ($secretaries as $secretary): ?>
                <option value="<?= (int) $secretary['user_id'] ?>" <?= (int) ($office['secretary_user_id'] ?? 0) === (int) $secretary['user_id'] ? 'selected' : '' ?>>
                  <?= htmlspecialchars((string) $secretary['full_name'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars((string) $secretary['email'], ENT_QUOTES, 'UTF-8') ?>)
                </option>
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
