<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

$fullName = trim((string) ($_SESSION['full_name'] ?? 'Administrator'));
$nameParts = preg_split('/\s+/', $fullName);
$firstName = $nameParts[0] ?? 'Administrator';
$email = (string) ($_SESSION['email'] ?? '');
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Admin</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="../css/admin-dashboard.css?v=<?= filemtime(__DIR__ . '/../css/admin-dashboard.css') ?>" />
  </head>
  <body class="admin-body">
    <header class="admin-header">
      <div class="header-left">
        <div class="logo-area">
          <span class="web-logo">Docuflow</span>
        </div>
      </div>
      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?> &middot; Administrator</span>
        </div>
        <div class="header-actions">
          <button
            class="icon-btn toggle-theme"
            id="themeToggle"
            type="button"
            aria-label="Toggle dark/light mode"
          >
            <i class="fas fa-moon"></i>
          </button>
          <form
            class="logout-form"
            method="post"
            action="../controller/logout.php"
            onsubmit="return confirm('Are you sure you want to logout?')"
          >
            <button class="icon-btn" type="submit" aria-label="Exit / Logout">
              <i class="fas fa-sign-out-alt"></i>
            </button>
          </form>
        </div>
      </div>
    </header>

    <main class="admin-landing">
      <section class="admin-welcome-card" aria-labelledby="admin-welcome-title">
        <p class="admin-brand">Docuflow Admin</p>
        <h1 id="admin-welcome-title">Hello, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p class="admin-welcome-message">Choose an area to manage.</p>

        <nav class="admin-menu-grid" aria-label="Administrator menu">
          <a class="admin-menu-button" href="admin-documents.php">
            <span class="admin-menu-icon"><i class="fas fa-folder-open" aria-hidden="true"></i></span>
            <span>
              <strong>View Documents</strong>
              <small>View every document in the system</small>
            </span>
          </a>

          <a class="admin-menu-button" href="admin-manage-users.php">
            <span class="admin-menu-icon"><i class="fas fa-users-cog" aria-hidden="true"></i></span>
            <span>
              <strong>Manage Users</strong>
              <small>Create and manage user accounts</small>
            </span>
          </a>

          <a class="admin-menu-button" href="admin-reports.php">
            <span class="admin-menu-icon"><i class="fas fa-chart-pie" aria-hidden="true"></i></span>
            <span>
              <strong>View Reports</strong>
              <small>Open document and office reports</small>
            </span>
          </a>

          <a class="admin-menu-button" href="admin-manage-offices.php">
            <span class="admin-menu-icon"><i class="fas fa-building" aria-hidden="true"></i></span>
            <span>
              <strong>Manage Offices</strong>
              <small>Add and maintain system offices</small>
            </span>
          </a>

          <a class="admin-menu-button" href="admin-manage-document-types.php">
            <span class="admin-menu-icon"><i class="fas fa-clipboard-list" aria-hidden="true"></i></span>
            <span>
              <strong>Document Types</strong>
              <small>View, add, activate, and deactivate types</small>
            </span>
          </a>
        </nav>
      </section>
    </main>

    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
  </body>
</html>
