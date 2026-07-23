<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../controller/db.php';

$email = (string) ($_SESSION['email'] ?? '');
$fullName = (string) ($_SESSION['full_name'] ?? 'Administrator');
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

$documentTypes = $conn->query(
    'SELECT document_types.type_id, document_types.type_name,
            document_types.description, document_types.is_active,
            COUNT(documents.document_id) AS document_count
     FROM document_types
     LEFT JOIN documents ON documents.type_id = document_types.type_id
     GROUP BY document_types.type_id, document_types.type_name,
              document_types.description, document_types.is_active
     ORDER BY document_types.type_name'
)->fetch_all(MYSQLI_ASSOC);
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

    <main class="admin-page admin-document-types-page">
      <a class="admin-back-button" href="admin-dashboard.php"><i class="fas fa-arrow-left" aria-hidden="true"></i> Go Back</a>

      <div class="admin-page-heading">
        <p>Administration</p>
        <h1>Manage Document Types</h1>
        <span>View available types, create new ones, and activate or deactivate existing types.</span>
      </div>

      <?php if ($success !== ''): ?><div class="admin-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
      <?php if ($error !== ''): ?><div class="admin-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>

      <section class="admin-form-panel admin-document-type-create-panel" aria-labelledby="add-document-type-title">
        <div>
          <h2 id="add-document-type-title">Add Document Type</h2>
          <p>Create another option for the document creation form.</p>
        </div>
        <form class="admin-document-type-create-form" method="post" action="../controller/admin_save_document_type.php">
          <div class="admin-form-field">
            <label for="documentTypeName">Type Name</label>
            <input id="documentTypeName" name="type_name" type="text" maxlength="50" value="<?= htmlspecialchars($oldTypeName, ENT_QUOTES, 'UTF-8') ?>" placeholder="e.g. Clearance Form" required />
          </div>
          <div class="admin-form-field">
            <label for="documentTypeDescription">Description</label>
            <textarea id="documentTypeDescription" name="description" maxlength="1000" rows="1" placeholder="Describe when this type should be used."><?= htmlspecialchars($oldDescription, ENT_QUOTES, 'UTF-8') ?></textarea>
          </div>
          <button class="admin-save-button" type="submit"><i class="fas fa-plus" aria-hidden="true"></i> Add Type</button>
        </form>
      </section>

      <section class="admin-table-panel" aria-label="Document types">
        <div class="admin-table-scroll">
          <table id="adminDocumentTypesTable" class="display admin-data-table admin-document-types-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Type Name</th>
                <th>Description</th>
                <th>Status</th>
                <th>Documents</th>
                <th>Action</th>
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
                  <td><span class="admin-status-pill <?= $isActive ? 'active' : 'inactive' ?>"><?= $isActive ? 'Active' : 'Inactive' ?></span></td>
                  <td><?= $documentCount ?></td>
                  <td>
                    <form class="admin-inline-form" method="post" action="../controller/admin_delete_document_type.php" onsubmit="return confirm('<?= $isActive ? 'Deactivate' : 'Activate' ?> this document type?')">
                      <input type="hidden" name="type_id" value="<?= (int) $type['type_id'] ?>" />
                      <input type="hidden" name="is_active" value="<?= $isActive ? 0 : 1 ?>" />
                      <button class="<?= $isActive ? 'admin-delete-button' : 'admin-activate-button' ?>" type="submit">
                        <?= $isActive ? 'Deactivate' : 'Activate' ?>
                      </button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.8/js/dataTables.js"></script>
    <script src="../js/admin-dashboard.js?v=<?= filemtime(__DIR__ . '/../js/admin-dashboard.js') ?>"></script>
    <script>
      new DataTable('#adminDocumentTypesTable', {
        pageLength: 10,
        order: [[1, 'asc']],
        columnDefs: [{ targets: 5, orderable: false, searchable: false }],
        language: {
          search: 'Search document types:',
          emptyTable: 'No document types were found.'
        }
      });
    </script>
  </body>
</html>
