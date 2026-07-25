<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Secretary') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../controller/db.php';

$userId = (int) $_SESSION['user_id'];
$fullName = trim((string) ($_SESSION['full_name'] ?? 'Secretary'));
$nameParts = preg_split('/\s+/', $fullName);
$firstName = $nameParts[0] ?? 'Secretary';
$email = (string) ($_SESSION['email'] ?? '');
$officeId = (int) ($_SESSION['office_id'] ?? 0);
$officeName = 'No office assigned';
$isSecretaryInCharge = false;

if ($officeId > 0) {
    $officeStatement = $conn->prepare(
        'SELECT offices.office_name, office_secretaries.secretary_user_id
         FROM offices
         LEFT JOIN office_secretaries ON office_secretaries.office_id = offices.office_id
         WHERE offices.office_id = ?
         LIMIT 1'
    );
    $officeStatement->bind_param('i', $officeId);
    $officeStatement->execute();
    $office = $officeStatement->get_result()->fetch_assoc();
    $officeStatement->close();

    if ($office) {
        $officeName = (string) $office['office_name'];
        $isSecretaryInCharge = (int) ($office['secretary_user_id'] ?? 0) === $userId;
    }
}

$secretaryLabel = $isSecretaryInCharge ? 'Secretary in Charge' : 'Secretary';
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Secretary Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
  </head>
  <body>
    <header class="member-header">
      <a class="web-logo" href="secretary-dashboard.php">Docuflow</a>

      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= htmlspecialchars($secretaryLabel . ' · ' . $officeName, ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <button id="themeToggle" class="icon-button" type="button" aria-label="Toggle dark or light mode">
          <i class="fas fa-sun" aria-hidden="true"></i>
        </button>

        <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
          <button class="icon-button" type="submit" aria-label="Log out"><i class="fas fa-sign-out-alt" aria-hidden="true"></i></button>
        </form>
      </div>
    </header>

    <main class="landing-page">
      <section class="welcome-card" aria-labelledby="welcome-title">
        <p class="brand"><?= htmlspecialchars($officeName, ENT_QUOTES, 'UTF-8') ?></p>
        <h1 id="welcome-title">Hello, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>!</h1>
        <p class="welcome-message">What would you like to do today?</p>

        <nav class="dashboard-actions" aria-label="Secretary dashboard actions">
          <a class="action-button" href="member-create-document.php">
            <span class="action-icon" aria-hidden="true"><i class="fas fa-plus"></i></span>
            <span><strong>Create Document</strong><small>Create and route a new document</small></span>
          </a>

          <a class="action-button" href="member-documents.php">
            <span class="action-icon" aria-hidden="true"><i class="fas fa-envelope"></i></span>
            <span><strong>Documents Addressed to Me</strong><small>Review documents assigned to you</small></span>
          </a>

          <?php if ($isSecretaryInCharge): ?>
            <a class="action-button" href="secretary-assign-documents.php">
              <span class="action-icon" aria-hidden="true"><i class="fas fa-user-pen"></i></span>
              <span><strong>Assign Documents</strong><small>Assign office documents to a member</small></span>
            </a>
          <?php endif; ?>

          <a class="action-button" href="member-my-documents.php">
            <span class="action-icon" aria-hidden="true"><i class="fas fa-folder-open"></i></span>
            <span><strong>My Documents</strong><small>View documents you created and their routes</small></span>
          </a>

          <a class="action-button" href="member-reports.php">
            <span class="action-icon" aria-hidden="true"><i class="fas fa-chart-column"></i></span>
            <span><strong>View Reports</strong><small>Open your document and route reports</small></span>
          </a>
        </nav>
      </section>
    </main>

    <script src="../js/member-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/member-dashboard.js') ?>"></script>
  </body>
</html>
