<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../controller/db.php';

$offices = $conn
    ->query('SELECT office_id, office_name FROM offices ORDER BY office_name')
    ->fetch_all(MYSQLI_ASSOC);
$error = (string) ($_SESSION['registration_error'] ?? '');
$success = (string) ($_SESSION['registration_success'] ?? '');
$fullName = (string) ($_SESSION['registration_full_name'] ?? '');
$email = (string) ($_SESSION['registration_email'] ?? '');
$officeId = (int) ($_SESSION['registration_office_id'] ?? 0);
unset(
    $_SESSION['registration_error'],
    $_SESSION['registration_success'],
    $_SESSION['registration_full_name'],
    $_SESSION['registration_email'],
    $_SESSION['registration_office_id']
);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Register</title>
    <link rel="stylesheet" href="../css/stylelogin.css?v=<?= filemtime(__DIR__ . '/../css/stylelogin.css') ?>" />
  </head>
  <body>
    <main class="login-card">
      <div class="top-icons">
        <button id="themeToggle" class="icon-button mode-icon" type="button" aria-label="Toggle Dark Mode">
          <span class="moon-icon">&#9790;</span>
          <span class="sun-icon">&#9728;</span>
        </button>
      </div>

      <div class="brand">
        <h1>Docuflow</h1>
        <p class="subtitle">Create your account</p>
      </div>

      <form method="post" action="../controller/register_process.php">
        <div class="form-group">
          <label for="fullName">Full Name</label>
          <input id="fullName" name="full_name" type="text" maxlength="100" value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>" autocomplete="name" required />
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" maxlength="100" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required />
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input id="confirmPassword" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required />
        </div>

        <div class="form-group">
          <label for="officeSelect">Office</label>
          <select id="officeSelect" name="office_id" required>
            <option value="">Select your office</option>
            <?php foreach ($offices as $office): ?>
              <option value="<?= (int) $office['office_id'] ?>" <?= $officeId === (int) $office['office_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <?php if ($error !== ''): ?>
          <div class="message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <button type="submit">Register</button>
      </form>

      <a href="login.php" class="register-link">Already have an account? Log in here</a>
    </main>

    <script src="../js/theme.js?v=<?= filemtime(__DIR__ . '/../js/theme.js') ?>"></script>
  </body>
</html>
