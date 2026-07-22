<?php require_once '../controllers/AdminUsersController.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - List of Users</title>
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
              <span class="user-email">admin@office.gov</span>
              <span class="user-role">Administrator</span>
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
            <h2 class="section-title">List of Users</h2>
            <p class="preview-description">Manage members, secretaries, and admins.</p>
          </div>
          <div class="admin-preview-content" id="admin-preview-content">
            <div style="margin-bottom:16px;">
              <button class="btn-primary" onclick="window.openUserModal()">Add User</button>
            </div>
            <table id="usersTable" class="display" style="width:100%">
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
                <?php foreach($users as $user): ?>
                  <tr>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['role']) ?></td>
                    <td><?= htmlspecialchars($user['office'] ?? '') ?></td>
                    <td>
                      <span class="status-badge <?= $user['status'] === 'Active' ? 'status-active' : 'status-inactive' ?>">
                        <?= htmlspecialchars($user['status']) ?>
                      </span>
                    </td>
                    <td>
                      <button class="btn-small edit-btn" title="Edit User" data-id="<?= $user['id'] ?>" data-name="<?= htmlspecialchars($user['name']) ?>" data-email="<?= htmlspecialchars($user['email']) ?>" data-role="<?= $user['role_id'] ?>" data-office="<?= $user['office_id'] ?>" data-status="<?= htmlspecialchars($user['status']) ?>">Edit</button>
                      <button class="btn-small delete-btn" title="Delete User" data-id="<?= $user['id'] ?>">Delete</button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

    <!-- User Modal -->
    <div id="userModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3 id="userModalTitle" class="section-title" style="margin:0;">Add User</h3>
          <button class="close-btn icon-btn" type="button" onclick="closeModal('userModal')"><i class="fas fa-times"></i></button>
        </div>
        <form id="userForm" class="admin-form" style="grid-template-columns: 1fr;">
          <input type="hidden" id="userId" />
          <label class="admin-field">
            <span>Name <span style="color: #ef4444">*</span></span>
            <input type="text" id="userName" required placeholder="Full Name" />
          </label>
          <label class="admin-field">
            <span>Email <span style="color: #ef4444">*</span></span>
            <input type="email" id="userEmail" required placeholder="name@office.gov" />
          </label>
          <label class="admin-field">
            <span>Password <span style="color: #ef4444">*</span></span>
            <input type="password" id="userPassword" placeholder="Required for new users, leave blank to keep current" />
          </label>
          <label class="admin-field">
            <span>Role <span style="color: #ef4444">*</span></span>
            <select id="userRole" required>
              <option value="">Select Role</option>
              <?php foreach($roles as $role): ?>
                <option value="<?= $role['role_id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="admin-field" id="officeGroup" style="display: none;">
            <span>Office</span>
            <select id="userOffice">
              <option value="">Select Office</option>
              <?php foreach($offices as $office): ?>
                <option value="<?= $office['id'] ?>"><?= htmlspecialchars($office['name']) ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="admin-field">
            <span>Status <span style="color: #ef4444">*</span></span>
            <select id="userStatus" required>
              <option value="Active">Active</option>
              <option value="Inactive">Inactive</option>
            </select>
          </label>
          <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
            <button type="button" class="admin-submit" style="background:var(--gray-300); color:var(--gray-700);" onclick="closeModal('userModal')">Cancel</button>
            <button type="submit" class="admin-submit">Save</button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/admin-users.js?v=<?= time() ?>"></script>
  </body>
</html>
