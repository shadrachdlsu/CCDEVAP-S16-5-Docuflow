<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/document.php';
require_once __DIR__ . '/../helpers/csrf.php';

$fullName = trim((string) ($_SESSION['full_name'] ?? 'Administrator'));
$nameParts = preg_split('/\s+/', $fullName);
$firstName = $nameParts[0] ?? 'Administrator';
$email = (string) ($_SESSION['email'] ?? '');
$success = (string) ($_SESSION['admin_dashboard_success'] ?? '');
$error = (string) ($_SESSION['admin_dashboard_error'] ?? '');
$csrfToken = docuflow_csrf_token();
unset($_SESSION['admin_dashboard_success'], $_SESSION['admin_dashboard_error']);

try {
    $userModel = new User();
    $documentModel = new Document();
    $pendingRegistrations = $userModel->getPendingRegistrations(5);
    $pendingRegistrationCount = $userModel->countPendingRegistrations();
    $stalledDocuments = $documentModel->getStalledDocuments(5);
    $stalledDocumentCount = $documentModel->countStalledDocuments();
} catch (Throwable $exception) {
    error_log('Admin dashboard queue error: ' . $exception->getMessage());
    $pendingRegistrations = [];
    $pendingRegistrationCount = 0;
    $stalledDocuments = [];
    $stalledDocumentCount = 0;
}
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

    <main class="admin-landing admin-dashboard-landing">
      <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <section class="admin-dashboard-overview" aria-labelledby="admin-overview-title">
        <div class="admin-dashboard-title">
          <div>
            <p class="admin-brand">Docuflow Admin</p>
            <h1 id="admin-overview-title">Welcome back, <?= htmlspecialchars($firstName, ENT_QUOTES, 'UTF-8') ?>.</h1>
            <p>Review items requiring administrative attention.</p>
          </div>
          <div class="admin-attention-total">
            <strong><?= number_format($pendingRegistrationCount + $stalledDocumentCount) ?></strong>
            <span>Items needing attention</span>
          </div>
        </div>

        <div class="admin-attention-grid">
          <article class="admin-attention-card">
            <div class="admin-attention-card-heading">
              <div>
                <span class="admin-attention-icon pending"><i class="fas fa-user-clock" aria-hidden="true"></i></span>
                <div>
                  <h2>Pending Registrations</h2>
                  <p>New accounts awaiting administrator approval</p>
                </div>
              </div>
              <span class="admin-count-pill"><?= number_format($pendingRegistrationCount) ?></span>
            </div>

            <div class="admin-attention-list">
              <?php if ($pendingRegistrations === []): ?>
                <div class="admin-attention-empty">
                  <i class="fas fa-circle-check" aria-hidden="true"></i>
                  <span>No pending registrations.</span>
                </div>
              <?php else: ?>
                <?php foreach ($pendingRegistrations as $pendingUser): ?>
                  <div class="admin-attention-row admin-registration-row">
                    <a class="admin-registration-user" href="admin-user.php?id=<?= (int) $pendingUser['user_id'] ?>">
                      <span class="admin-row-avatar"><?= htmlspecialchars(strtoupper(substr((string) $pendingUser['full_name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></span>
                      <span class="admin-row-copy">
                        <strong><?= htmlspecialchars((string) $pendingUser['full_name'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <small><?= htmlspecialchars((string) $pendingUser['role_name'], ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars((string) $pendingUser['office_name'], ENT_QUOTES, 'UTF-8') ?></small>
                      </span>
                    </a>
                    <div class="admin-registration-actions">
                      <form method="post" action="../controller/admin_registration_action.php">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                        <input type="hidden" name="user_id" value="<?= (int) $pendingUser['user_id'] ?>" />
                        <input type="hidden" name="decision" value="approve" />
                        <button class="admin-registration-button approve" type="submit" title="Approve registration"><i class="fas fa-check" aria-hidden="true"></i><span>Approve</span></button>
                      </form>
                      <form method="post" action="../controller/admin_registration_action.php" onsubmit="return confirm('Reject this registration?')">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />
                        <input type="hidden" name="user_id" value="<?= (int) $pendingUser['user_id'] ?>" />
                        <input type="hidden" name="decision" value="reject" />
                        <button class="admin-registration-button reject" type="submit" title="Reject registration"><i class="fas fa-xmark" aria-hidden="true"></i><span>Reject</span></button>
                      </form>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <a class="admin-card-link" href="admin-manage-users.php">Manage all users <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
          </article>

          <article class="admin-attention-card stalled">
            <div class="admin-attention-card-heading">
              <div>
                <span class="admin-attention-icon stalled"><i class="fas fa-triangle-exclamation" aria-hidden="true"></i></span>
                <div>
                  <h2>Stalled Documents</h2>
                  <p>Unresolved documents unchanged for over 48 hours</p>
                </div>
              </div>
              <span class="admin-count-pill danger"><?= number_format($stalledDocumentCount) ?></span>
            </div>

            <div class="admin-attention-list">
              <?php if ($stalledDocuments === []): ?>
                <div class="admin-attention-empty">
                  <i class="fas fa-circle-check" aria-hidden="true"></i>
                  <span>No stalled documents.</span>
                </div>
              <?php else: ?>
                <?php foreach ($stalledDocuments as $stalledDocument): ?>
                  <a class="admin-attention-row" href="admin-document.php?id=<?= (int) $stalledDocument['document_id'] ?>">
                    <span class="admin-row-copy">
                      <strong><?= htmlspecialchars((string) $stalledDocument['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                      <small><?= htmlspecialchars((string) $stalledDocument['tracking_code'], ENT_QUOTES, 'UTF-8') ?> &middot; <?= htmlspecialchars((string) $stalledDocument['current_office'], ENT_QUOTES, 'UTF-8') ?></small>
                    </span>
                    <span class="admin-stalled-age"><?= number_format((int) $stalledDocument['hours_stalled']) ?>h</span>
                  </a>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>

            <a class="admin-card-link" href="admin-documents.php">View all documents <i class="fas fa-arrow-right" aria-hidden="true"></i></a>
          </article>
        </div>
      </section>

      <section class="admin-welcome-card" aria-labelledby="admin-welcome-title">
        <p class="admin-brand">Management</p>
        <h2 id="admin-welcome-title">Administration tools</h2>
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

          <a class="admin-menu-button" href="admin-settings.php">
            <span class="admin-menu-icon"><i class="fas fa-sliders" aria-hidden="true"></i></span>
            <span>
              <strong>System Settings</strong>
              <small>Configure registration and access policies</small>
            </span>
          </a>
        </nav>
      </section>
    </main>

    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
  </body>
</html>
