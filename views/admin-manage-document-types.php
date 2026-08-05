<?php
declare(strict_types=1);

session_start();

$role = (string) ($_SESSION['role'] ?? '');

if (!isset($_SESSION['user_id']) || !in_array($role, ['Admin', 'Secretary'], true)) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/../models/documentType.php';
require_once __DIR__ . '/../models/office.php';

$email = (string) ($_SESSION['email'] ?? '');
$isSecretary = $role === 'Secretary';
$fullName = (string) ($_SESSION['full_name'] ?? ($isSecretary ? 'Secretary' : 'Administrator'));
$officeId = (int) ($_SESSION['office_id'] ?? 0);
$office = $isSecretary && $officeId > 0
    ? (new Office())->getSecretaryContext($officeId)
    : null;
$officeName = (string) ($office['office_name'] ?? 'No office assigned');
$dashboardPage = $isSecretary ? 'secretary-dashboard.php' : 'admin-dashboard.php';
$roleLabel = $isSecretary ? 'Secretary - ' . $officeName : 'Administrator';
$success = (string) ($_SESSION['admin_document_type_success'] ?? '');
$error = (string) ($_SESSION['admin_document_type_error'] ?? '');
$oldTypeName = (string) ($_SESSION['admin_document_type_name'] ?? '');
$oldDescription = (string) ($_SESSION['admin_document_type_description'] ?? '');
unset(
    $_SESSION['admin_document_type_success'],
    $_SESSION['admin_document_type_error'],
    $_SESSION['admin_document_type_name'],
    $_SESSION['admin_document_type_description']
);

$documentTypes = $isSecretary
    ? ($officeId > 0 ? (new DocumentType())->getOfficeList($officeId) : [])
    : (new DocumentType())->getAdminList();
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Manage Document Types - Docuflow</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.8/css/dataTables.dataTables.css" />
    <link rel="stylesheet" href="../css/admin-dashboard.css?v=<?= filemtime(__DIR__ . '/../css/admin-dashboard.css') ?>" />
    <?php if ($isSecretary): ?>
      <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
    <?php endif; ?>
  </head>
  <body class="<?= $isSecretary ? 'secretary-document-types-body' : 'admin-body' ?>">
    <header class="<?= $isSecretary ? 'member-header' : 'admin-header' ?>">
      <div class="header-left"><a class="web-logo" href="<?= $dashboardPage ?>">Docuflow</a></div>
      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= htmlspecialchars($fullName . ' - ' . $roleLabel, ENT_QUOTES, 'UTF-8') ?></span>
        </div>
        <div class="header-actions">
          <button class="<?= $isSecretary ? 'icon-button' : 'icon-btn toggle-theme' ?>" id="themeToggle" type="button" aria-label="Toggle dark/light mode"><i class="fas <?= $isSecretary ? 'fa-sun' : 'fa-moon' ?>"></i></button>
          <form class="logout-form" method="post" action="../controllers/UserLogoutController.php" onsubmit="return confirm('Are you sure you want to logout?')">
            <button class="<?= $isSecretary ? 'icon-button' : 'icon-btn' ?>" type="submit" aria-label="Exit / Logout"><i class="fas fa-sign-out-alt"></i></button>
          </form>
        </div>
      </div>
    </header>

    <main class="<?= $isSecretary ? 'documents-page secretary-document-types-page' : 'admin-page admin-document-types-page' ?>">
      <a class="<?= $isSecretary ? 'back-link page-back-link' : 'admin-back-button' ?>" href="<?= $dashboardPage ?>"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="<?= $isSecretary ? 'page-heading' : 'admin-page-heading' ?>">
        <p<?= $isSecretary ? ' class="brand"' : '' ?>><?= $isSecretary ? htmlspecialchars($officeName, ENT_QUOTES, 'UTF-8') : 'Administration' ?></p>
        <h1>Manage Document Types</h1>
        <?php if ($isSecretary): ?>
          <p class="welcome-message">Create, view, activate, or deactivate document types belonging to your office.</p>
        <?php else: ?>
          <span>View available types, create new ones, and activate or deactivate existing types.</span>
        <?php endif; ?>
      </div>

      <?php if ($success !== ''): ?><div class="admin-message form-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="admin-message form-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <section class="admin-form-panel admin-document-type-create-panel<?= $isSecretary ? ' create-document-panel secretary-document-type-panel' : '' ?>" aria-labelledby="add-document-type-title">
        <div>
          <h2 id="add-document-type-title">Add Document Type</h2>
          <p><?= $isSecretary
              ? 'Create another option for document creators in your office.'
              : 'Create another option for the document creation form.' ?></p>
        </div>
        <form class="admin-document-type-create-form<?= $isSecretary ? ' create-document-form secretary-document-type-form' : '' ?>" method="post" action="../controllers/AdminSaveDocumentTypeController.php">
          <div class="admin-form-field<?= $isSecretary ? ' form-field' : '' ?>">
            <label for="documentTypeName">Type Name</label>
            <input id="documentTypeName" name="type_name" type="text" maxlength="50" value="<?= htmlspecialchars($oldTypeName, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Clearance Form" required />
          </div>
          <div class="admin-form-field<?= $isSecretary ? ' form-field' : '' ?>">
            <label for="documentTypeDescription">Description</label>
            <textarea id="documentTypeDescription" name="description" maxlength="1000" rows="1" placeholder="Describe when this type should be used."><?= htmlspecialchars($oldDescription, ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <button class="admin-save-button<?= $isSecretary ? ' submit-document-button' : '' ?>" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> Add Type</button>
        </form>
      </section>

      <section class="admin-table-panel<?= $isSecretary ? ' document-table-panel' : '' ?>" aria-label="Document types">
        <div class="admin-table-scroll<?= $isSecretary ? ' document-table-scroll' : '' ?>">
          <table id="adminDocumentTypesTable" class="display admin-data-table admin-document-types-table<?= $isSecretary ? ' member-documents-table' : '' ?>">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Documents</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($documentTypes as $type): ?>
                <?php $documentCount = (int) $type['document_count']; ?>
                <?php $isActive = (int) $type['is_active'] === 1; ?>
                <tr>
                  <td><?= (int) $type['type_id'] ?></td>
                  <td><?= htmlspecialchars((string) $type['type_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars((string) ($type['description'] ?: 'No description'), ENT_QUOTES, 'UTF-8') ?></td>
                  <td>
                    <form class="admin-inline-form admin-status-toggle-form" method="post" action="../controllers/AdminDeleteDocumentTypeController.php">
                      <input type="hidden" name="type_id" value="<?= (int) $type['type_id'] ?>" />
                      <input class="admin-status-toggle-value" type="hidden" name="is_active" value="<?= $isActive ? 1 : 0 ?>" />
                      <label class="admin-status-toggle">
                        <input
                          class="admin-status-toggle-input"
                          type="checkbox"
                          <?= $isActive ? 'checked' : '' ?>
                          aria-label="<?= $isActive ? 'Deactivate' : 'Activate' ?> <?= htmlspecialchars((string) $type['type_name'], ENT_QUOTES, 'UTF-8') ?>"
                        />
                        <span class="admin-status-toggle-track" aria-hidden="true">
                          <span class="admin-status-toggle-thumb"></span>
                        </span>
                        <span class="admin-status-toggle-label"><?= $isActive ? 'Active' : 'Inactive' ?></span>
                      </label>
                    </form>
                  </td>
                  <td><?= $documentCount ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/<?= $isSecretary ? 'member-dashboard.js' : 'admin-dashboard.js' ?>?v=<?= filemtime(__DIR__ . '/../js/' . ($isSecretary ? 'member-dashboard.js' : 'admin-dashboard.js')) ?>"></script>
    <script>
      new DataTable('#adminDocumentTypesTable', {
        pageLength: 10,
        order: [[1, 'asc']],
        language: {
          search: 'Search document types:',
          emptyTable: 'No document types were found.'
        }
      });

      document.querySelector('#adminDocumentTypesTable').addEventListener('change', (event) => {
        const toggle = event.target.closest('.admin-status-toggle-input');
        if (!toggle) {
          return;
        }

        const form = toggle.closest('.admin-status-toggle-form');
        const nextStatus = toggle.checked ? 'activate' : 'deactivate';

        if (!window.confirm(`Are you sure you want to ${nextStatus} this document type?`)) {
          toggle.checked = !toggle.checked;
          return;
        }

        form.querySelector('.admin-status-toggle-value').value = toggle.checked ? '1' : '0';
        toggle.disabled = true;
        form.submit();
      });
    </script>
  </body>
</html>
