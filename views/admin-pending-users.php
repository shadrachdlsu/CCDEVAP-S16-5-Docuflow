<?php require_once '../controllers/AdminPendingUsersController.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Pending Users</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link rel="stylesheet" href="../css/admin-dashboard.css" />
  </head>
  <body class="admin-body">
    <div class="admin-layout">
      <main class="admin-main">
        <header class="admin-header">
          <div class="header-left">
            <a href="admin-dashboard.php" class="logo-area">
              <span class="web-logo">Docuflow</span>
            </a>
            <a href="admin-dashboard.php" class="back-btn">
              <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
          </div>
          <div class="header-right">
            <div class="user-info">
              <span class="user-role">Admin</span>
            </div>
            <div class="header-actions">
              <button class="icon-btn toggle-theme" id="themeToggle" aria-label="Toggle dark/light mode">
                <i class="fas fa-moon"></i>
              </button>
              <a href="../controllers/LogoutController.php" class="icon-btn logout-btn" aria-label="Exit / Logout">
                <i class="fas fa-sign-out-alt"></i>
              </a>
            </div>
          </div>
        </header>

        <section class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">Pending Users</h2>
            <p class="preview-description">Approve new users to activate their accounts.</p>
          </div>
          <div class="admin-preview-content" id="admin-preview-content">
            <table id="pendingUsersTable" class="display" style="width:100%">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>Role</th>
                  <th>Office</th>
                  <th>Status</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($pendingUsers as $user): ?>
                  <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['office'] ?? '') ?></td>
                    <td>
                      <span class="status-badge status-inactive">
                        Pending
                      </span>
                    </td>
                    <td>
                      <button class="btn-small approve-btn" style="background: #e0e7ff; color: #3730a3;" title="Approve User" data-id="<?= $user['id'] ?>">Approve</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/admin-pending-users.js?v=<?= time() ?>"></script>
  </body>
</html>
