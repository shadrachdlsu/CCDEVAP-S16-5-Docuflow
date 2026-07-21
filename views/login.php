<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow Login</title>
    <link rel="stylesheet" href="../css/stylelogin.css" />
  </head>
  <body>
    <main class="login-card">
      <div class="top-icons">
        <button
          id="themeToggle"
          class="icon-button mode-icon"
          type="button"
          aria-label="Toggle Dark Mode"
        >
          <span class="moon-icon">☾</span>
          <span class="sun-icon">☀</span>
        </button>
      </div>

      <div class="brand">
        <h1>Docuflow</h1>
        <p class="subtitle">Document Tracking System</p>
      </div>
      
      <?php if (isset($_GET['error'])): ?>
        <div class="message error" style="color: #dc2626; font-weight: 700; text-align: center; margin-bottom: 15px;">
          <?php
            if ($_GET['error'] == 'empty') echo "Please fill in all fields.";
            elseif ($_GET['error'] == 'invalid') echo "Invalid email or password.";
            elseif ($_GET['error'] == 'inactive') echo "Your account is inactive.";
            else echo "An error occured.";
            ?>
          </div>
      <?php endif; ?>

      <form action ="../controllers/LoginController.php" method="POST">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input id="email" name="email" type="email" placeholder="your.email@office.gov" required/>
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input id="password" name="password" type="password" placeholder="........" required />
        </div>

        <button type="submit">Sign In</button>
      </form>

      <a href="register.php" class="register-link">Register here</a>

      <p class="demo-users">
        Demo users: admin@office.gov, secretary@office.gov, member@office.gov
      </p>
    </main>

    <script src="../js/theme.js"></script>
  </body>
</html>
