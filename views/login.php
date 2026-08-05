<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    redirectForRole((string) $_SESSION['role']);
}

$error = (string) ($_SESSION['login_error'] ?? '');
$email = (string) ($_SESSION['login_email'] ?? '');
unset($_SESSION['login_error'], $_SESSION['login_email']);

function redirectForRole(string $role): never
{
    $destinations = [
        'Admin' => 'admin-dashboard.php',
        'Secretary' => 'secretary-dashboard.php',
        'Member' => 'member-dashboard.php',
    ];

    header('Location: ' . ($destinations[$role] ?? 'member-dashboard.php'));
    exit;
}
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow Login</title>
    <link rel="stylesheet" href="../css/stylelogin.css?v=<?= filemtime(__DIR__ . '/../css/stylelogin.css') ?>" />
  </head>
  <body>
    <main class="login-card">
      <div class="top-icons">
        <button id="themeToggle" class="icon-button mode-icon" type="button" aria-label="Switch to dark mode">
          <span class="moon-icon" aria-hidden="true">&#9790;</span>
          <span class="sun-icon" aria-hidden="true">&#9728;</span>
        </button>
      </div>

      <div class="brand">
        <h1>Docuflow</h1>
        <p class="subtitle">Document Tracking System</p>
      </div>

      <form method="post" action="../controller/login_process.php">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" placeholder="your.email@office.gov" value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" autocomplete="email" required />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" autocomplete="current-password" required />
        </div>

        <?php if ($error !== ''): ?>
          <div class="message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <button type="submit">Sign In</button>
      </form>

      <a href="register.php" class="register-link">Register here</a>
    </main>

    <script src="../js/theme.js?v=<?= filemtime(__DIR__ . '/../js/theme.js') ?>"></script>
  </body>
</html>
