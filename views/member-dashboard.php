<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Member') {
    header('Location: login.php');
    exit;
}

$fullName = trim((string) ($_SESSION['full_name'] ?? 'Member'));
$nameParts = preg_split('/\s+/', $fullName);
$firstName = $nameParts[0] ?? 'Member';
$email = (string) ($_SESSION['email'] ?? '');
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Member Dashboard</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
  </head>
  <body>
    <header class="member-header">
      <a class="web-logo" href="member-dashboard.php">Docuflow</a>

      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role">Office Member</span>
        </div>

        <button
          id="themeToggle"
          class="icon-button"
          type="button"
          aria-label="Toggle dark or light mode"
        >
          <i class="fas fa-sun" aria-hidden="true"></i>
        </button>

        <form
          class="logout-form"
          method="post"
          action="../controller/logout.php"
          onsubmit="return confirm('Are you sure you want to logout?')"
        >
          <button class="icon-button" type="submit" aria-label="Log out">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
          </button>
        </form>
      </div>
    </header>

    <main class="landing-page">
      <section class="welcome-card" aria-labelledby="welcome-title">
        <p class="brand">Docuflow</p>
        <h1 id="welcome-title">Hello, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p class="welcome-message">What would you like to do today?</p>

        <nav class="dashboard-actions" aria-label="Member dashboard actions">
          <a class="action-button" href="member-create-document.php">
            <span class="action-icon" aria-hidden="true">+</span>
            <span>
              <strong>Create Document</strong>
              <small>Create and route a new document</small>
            </span>
          </a>

          <a class="action-button" href="member-documents.php">
            <span class="action-icon" aria-hidden="true">&#9993;</span>
            <span>
              <strong>Documents Addressed to Me</strong>
              <small>Read documents sent to you</small>
            </span>
          </a>

          <a class="action-button" href="member-my-documents.php">
            <span class="action-icon" aria-hidden="true"><i class="fas fa-folder-open"></i></span>
            <span>
              <strong>My Documents</strong>
              <small>View your documents and routing trail</small>
            </span>
          </a>

          <a class="action-button" href="member-reports.php">
            <span class="action-icon" aria-hidden="true">&#9776;</span>
            <span>
              <strong>View Reports</strong>
              <small>Open document reports</small>
            </span>
          </a>
        </nav>
      </section>
    </main>
    <script src="../js/member-dashboard.js"></script>
  </body>
</html>
