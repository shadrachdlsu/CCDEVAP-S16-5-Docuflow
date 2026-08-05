<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../helpers/csrf.php';
require_once __DIR__ . '/../models/setting.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
$success = (string) ($_SESSION['admin_settings_success'] ?? '');
$error = (string) ($_SESSION['admin_settings_error'] ?? '');
$requiresAdminApproval = (new Setting())->requiresAdminApproval();
$csrfToken = docuflow_csrf_token();

unset($_SESSION['admin_settings_success'], $_SESSION['admin_settings_error']);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>System Settings - Docuflow</title>
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

    <main class="admin-page admin-settings-page">
      <a class="admin-back-button" href="admin-dashboard.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="admin-page-heading">
        <p>Administration</p>
        <h1>System Settings</h1>
        <span>Configure registration and account-access policies across Docuflow.</span>
      </div>

      <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <section class="admin-form-panel admin-settings-panel" aria-labelledby="registration-settings-title">
        <div class="admin-settings-heading">
          <div class="admin-settings-icon"><i class="fas fa-user-shield" aria-hidden="true"></i></div>
          <div>
            <h2 id="registration-settings-title">User Registration &amp; Access Controls</h2>
            <p>Configure user account registration policies and administrator approval requirements.</p>
          </div>
          <span class="admin-policy-badge <?= $requiresAdminApproval ? 'requires-approval' : 'auto-approve' ?>">
            <?= $requiresAdminApproval ? 'Approval Required' : 'Auto-Approve' ?>
          </span>
        </div>

        <form class="admin-settings-form" method="post" action="../controller/admin_save_settings.php">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>" />

          <fieldset class="admin-policy-options">
            <legend>Registration Approval Policy</legend>

            <label class="admin-policy-option">
              <input type="radio" name="require_admin_approval" value="1" <?= $requiresAdminApproval ? 'checked' : '' ?> />
              <span class="admin-policy-radio" aria-hidden="true"></span>
              <span class="admin-policy-option-marker"><i class="fas fa-user-check" aria-hidden="true"></i></span>
              <span>
                <strong>Require Admin Approval</strong>
                <small>Newly registered accounts remain pending until an administrator approves them.</small>
              </span>
            </label>

            <label class="admin-policy-option">
              <input type="radio" name="require_admin_approval" value="0" <?= !$requiresAdminApproval ? 'checked' : '' ?> />
              <span class="admin-policy-radio" aria-hidden="true"></span>
              <span class="admin-policy-option-marker"><i class="fas fa-bolt" aria-hidden="true"></i></span>
              <span>
                <strong>Auto-Approve Users</strong>
                <small>New accounts are approved and activated immediately after registration.</small>
              </span>
            </label>
          </fieldset>

          <div class="admin-settings-actions">
            <p><i class="fas fa-circle-info" aria-hidden="true"></i> This policy applies to future registrations and does not change existing users.</p>
            <button class="admin-save-button" type="submit"><i class="fas fa-save" aria-hidden="true"></i> Save Policy</button>
          </div>
        </form>
      </section>
    </main>

    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
  </body>
</html>
