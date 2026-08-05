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
        'Admin'     => 'admin-dashboard.php',
        'Secretary' => 'secretary-dashboard.php',
        'Member'    => 'member-dashboard.php',
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
    <title>Docuflow - Sign In</title>
    <meta name="description" content="Sign in to Docuflow, the efficient way to track, approve, and manage documents across your organization." />
    <link rel="stylesheet" href="../css/stylelogin.css?v=<?= filemtime(__DIR__ . '/../css/stylelogin.css') ?>" />
  </head>
  <body>

    <!-- ====================================== -->
    <!-- PAGE WRAPPER (two-column split layout) -->
    <!-- ====================================== -->
    <div class="page-wrapper">

      <!-- ====================================== -->
      <!-- LEFT HERO PANEL                        -->
      <!-- ====================================== -->
      <div class="hero-panel" aria-hidden="true">
        <div class="hero-arcs">
          <svg class="arcs-svg" viewBox="0 0 600 600" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="xMidYMid slice">
            <circle cx="300" cy="600" r="160" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
            <circle cx="300" cy="600" r="230" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
            <circle cx="300" cy="600" r="300" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
            <circle cx="300" cy="600" r="370" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
            <circle cx="300" cy="600" r="440" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
            <circle cx="300" cy="600" r="510" stroke="#d1d5e8" stroke-width="1.2" fill="none" />
          </svg>
        </div>

        <div class="hero-content">
          <h2 class="hero-headline">
            Track, approve, and manage documents across your organization with ease.
          </h2>
          <p class="hero-sub">
            The efficient way to handle your <span class="hero-accent">workflows</span>.
          </p>
        </div>
      </div>

      <!-- ====================================== -->
      <!-- RIGHT CARD PANEL                       -->
      <!-- ====================================== -->
      <div class="card-panel">
        <main class="login-card">

          <!-- Dark mode toggle -->
          <div class="top-icons">
            <button
              id="themeToggle"
              class="icon-button mode-icon"
              type="button"
              aria-label="Switch to dark mode"
            >
              <span class="moon-icon" aria-hidden="true">&#9790;</span>
              <span class="sun-icon"  aria-hidden="true">&#9728;</span>
            </button>
          </div>

          <!-- Card heading -->
          <div class="brand">
            <h1>Sign In</h1>
            <p class="subtitle">Access your <span class="brand-accent">Docuflow</span> account</p>
          </div>

          <!-- Error message (shown above fields, matching screenshot) -->
          <?php if ($error !== ''): ?>
            <div class="message error" role="alert">
              <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
            </div>
          <?php endif; ?>

          <!-- Login form -->
          <form method="post" action="../controllers/UserLoginController.php">

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
                  placeholder="Enter your password"
                  autocomplete="current-password"
                  required
                />
              </div>
            </div>

            <button type="submit">Sign In</button>
          </form>

          <a href="register.php" class="register-link">Register here</a>

        </main>
      </div>

    </div><!-- /.page-wrapper -->

    <script src="../js/theme.js?v=<?= filemtime(__DIR__ . '/../js/theme.js') ?>"></script>
  </body>
</html>
