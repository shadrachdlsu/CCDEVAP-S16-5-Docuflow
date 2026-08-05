<?php
declare(strict_types=1);

session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/office.php';

$offices    = (new Office())->getActive();
$error      = (string) ($_SESSION['registration_error']    ?? '');
$success    = (string) ($_SESSION['registration_success']  ?? '');
$fullName   = (string) ($_SESSION['registration_full_name'] ?? '');
$email      = (string) ($_SESSION['registration_email']    ?? '');
$officeId   = (int)    ($_SESSION['registration_office_id'] ?? 0);

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
    <meta name="description" content="Create your Docuflow account to start tracking and managing documents across your organization." />
    <link rel="stylesheet" href="../css/stylelogin.css?v=<?= filemtime(__DIR__ . '/../css/stylelogin.css') ?>" />
  </head>
  <body class="register-page">

    <!-- ====================================== -->
    <!-- FULL-PAGE ARC BACKGROUND               -->
    <!-- ====================================== -->
    <div class="register-bg" aria-hidden="true">
      <svg class="register-arcs-svg" viewBox="0 0 900 900" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
        <circle cx="450" cy="900" r="200" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
        <circle cx="450" cy="900" r="300" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
        <circle cx="450" cy="900" r="400" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
        <circle cx="450" cy="900" r="500" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
        <circle cx="450" cy="900" r="600" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
        <circle cx="450" cy="900" r="700" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
        <circle cx="450" cy="900" r="800" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
      </svg>
    </div>

    <!-- ====================================== -->
    <!-- CENTERED REGISTER CARD                 -->
    <!-- ====================================== -->
    <main class="register-card-wrap">
      <div class="login-card">

        <!-- Dark mode toggle -->
        <div class="top-icons">
          <button
            id="themeToggle"
            class="icon-button mode-icon"
            type="button"
            aria-label="Toggle Dark Mode"
          >
            <span class="moon-icon" aria-hidden="true">&#9790;</span>
            <span class="sun-icon"  aria-hidden="true">&#9728;</span>
          </button>
        </div>

        <!-- Card heading -->
        <div class="brand">
          <h1>Create Account</h1>
          <p class="subtitle">Join <span class="brand-accent">Docuflow</span> to get started</p>
        </div>

        <!-- Error / success messages -->
        <?php if ($error !== ''): ?>
          <div class="message error" role="alert">
            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="message success" role="status">
            <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
          </div>
        <?php endif; ?>

        <!-- Registration form -->
        <form method="post" action="../controllers/UserRegisterController.php">

          <div class="form-group">
            <label for="fullName">Full Name</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="12" cy="8" r="4"/>
                  <path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
                </svg>
              </span>
              <input
                id="fullName"
                name="full_name"
                type="text"
                maxlength="100"
                placeholder="Enter your full name"
                value="<?= htmlspecialchars($fullName, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="name"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="5" width="18" height="14" rx="2"/>
                  <polyline points="3 7 12 13 21 7"/>
                </svg>
              </span>
              <input
                id="email"
                name="email"
                type="email"
                maxlength="100"
                placeholder="Enter your email"
                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                autocomplete="email"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </span>
              <input
                id="password"
                name="password"
                type="password"
                minlength="8"
                placeholder="At least 8 characters"
                autocomplete="new-password"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="confirmPassword">Confirm Password</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </span>
              <input
                id="confirmPassword"
                name="confirm_password"
                type="password"
                minlength="8"
                placeholder="Repeat your password"
                autocomplete="new-password"
                required
              />
            </div>
          </div>

          <div class="form-group">
            <label for="officeSelect">Office</label>
            <div class="input-wrapper">
              <span class="input-icon" aria-hidden="true">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="2" y="7" width="20" height="14" rx="2"/>
                  <path d="M16 7V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v2"/>
                </svg>
              </span>
              <select id="officeSelect" name="office_id" required>
                <option value="">Select your office</option>
                <?php foreach ($offices as $office): ?>
                  <option
                    value="<?= (int) $office['office_id'] ?>"
                    <?= $officeId === (int) $office['office_id'] ? 'selected' : '' ?>
                  >
                    <?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>

          <button type="submit">Create Account</button>

        </form>

        <a href="login.php" class="register-link">Already have an account? Sign in</a>

      </div>
    </main>

    <script src="../js/theme.js?v=<?= filemtime(__DIR__ . '/../js/theme.js') ?>"></script>
  </body>
</html>
