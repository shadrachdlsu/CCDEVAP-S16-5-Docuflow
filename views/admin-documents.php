<?php require_once '../controllers/AdminDocumentsController.php'; ?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - List of Documents</title>
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
            <h2 class="section-title">List of Documents</h2>
            <p class="preview-description">Global view of all documents in the system.</p>
          </div>
          <div class="admin-preview-content" id="admin-preview-content">
            <table id="documentsTable" class="display" style="width:100%">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Title</th>
                  <th>Type</th>
                  <th>Current Office</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($documents as $doc): ?>
                  <tr>
                    <td><?= htmlspecialchars($doc['id']) ?></td>
                    <td><?= htmlspecialchars($doc['title']) ?></td>
                    <td><?= htmlspecialchars($doc['type']) ?></td>
                    <td><?= htmlspecialchars($doc['office']) ?></td>
                    <td>
                      <span class="status-badge <?= strtolower(str_replace(' ', '-', $doc['status'])) ?>-status">
                        <?= htmlspecialchars($doc['status']) ?>
                      </span>
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
    <script src="../js/admin-documents.js?v=<?= time() ?>"></script>
  </body>
</html>
