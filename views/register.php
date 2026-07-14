<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Sign Up</title>
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
        <p class="subtitle">Create your account</p>
      </div>

      <?php if (isset($_GET['msg'])): ?>
        <div class="message <?php echo $_GET['type'] === 'success' ? 'success' : 'error'; ?>"
          style="font-weight: 700; text-align: center; margin-bottom: 15px;" >
          <?php
            if ($_GET['msg'] == 'mismatch') echo "Passwords do not match.";
            elseif ($_GET['msg'] == 'exists') echo "Email is already registered.";
            elseif ($_GET['msg'] == 'success') echo "Account created! You can now log in.";
            else echo "An error occured.";
            ?>
        </div>
      <?php endif; ?>


      <form action="../controllers/register_process.php" method="POST">
        <div class="form-group">
          <label for="full_name">Full Name</label>
          <input 
            type="text" 
            id="full_name" 
            name="full_name" 
            placeholder="Juan Dela Cruz" 
            required 
            />
        </div>

        <div class="form-group">
          <label for="email">Email Address</label>
          <input
            type="email"
            id="email"
            name="email"
            placeholder="your.email@office.com"
            required
          />
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input
            type="password"
            id="password"
            name="password"
            placeholder="••••••••"
            required
          />
        </div>

        <div class="form-group">
          <label for="confirmPassword">Confirm Password</label>
          <input
            type="password"
            id="confirmPassword"
            name="confirmPassword"
            placeholder="••••••••"
            required
          />
        </div>

        <div class="form-group">
          <label for="office_id">Office</label>
          <select id="office_id" name="office_id" required>
            <option value="" disabled selected>Select your office</option>
            <option value="1">Registar Office</option>
            <option value="2">Finance Office</option>
            <option value="3">Dean Office</option>
            <option value="4">IT Office</option>
          </select>
        </div>

        <button type="submit">Sign Up</button>
      </form>

      <a href="login.php" class="register-link"
        >Already have an account? Log in here</a
      >
    </main>

    <script src="../js/theme.js"></script>
  </body>
</html>
