<?php
declare(strict_types=1);

session_start();

$role = (string) ($_SESSION['role'] ?? '');

if (!isset($_SESSION['user_id']) || !in_array($role, ['Member', 'Secretary'], true)) {
    header('Location: login.php');
    exit;
}

$dashboardPage = $role === 'Secretary' ? 'secretary-dashboard.php' : 'member-dashboard.php';
$roleLabel = $role === 'Secretary' ? 'Office Secretary' : 'Office Member';

require __DIR__ . '/../controller/db.php';

$email = (string) ($_SESSION['email'] ?? '');
$error = (string) ($_SESSION['document_error'] ?? '');
$success = (string) ($_SESSION['document_success'] ?? '');
$oldTitle = (string) ($_SESSION['document_title'] ?? '');
$oldRoutingMode = (string) ($_SESSION['document_routing_mode'] ?? 'sequential');
$oldRoutingMode = in_array($oldRoutingMode, ['sequential', 'simultaneous'], true)
    ? $oldRoutingMode
    : 'sequential';
unset(
    $_SESSION['document_error'],
    $_SESSION['document_success'],
    $_SESSION['document_title'],
    $_SESSION['document_routing_mode']
);

$documentTypes = $conn
    ->query('SELECT type_id, type_name FROM document_types WHERE is_active = 1 ORDER BY type_name')
    ->fetch_all(MYSQLI_ASSOC);
$offices = $conn
    ->query('SELECT office_id, office_name FROM offices ORDER BY office_name')
    ->fetch_all(MYSQLI_ASSOC);
?>
<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Create Document - Docuflow</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link rel="stylesheet" href="../css/dashboard.css?v=<?= filemtime(__DIR__ . '/../css/dashboard.css') ?>" />
  </head>
  <body>
    <header class="member-header">
      <a class="web-logo" href="<?= $dashboardPage ?>">Docuflow</a>

      <div class="header-right">
        <div class="user-info">
          <span class="user-email"><?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="user-role"><?= $roleLabel ?></span>
        </div>

        <button id="themeToggle" class="icon-button" type="button" aria-label="Toggle dark or light mode">
          <i class="fas fa-sun" aria-hidden="true"></i>
        </button>

        <form class="logout-form" method="post" action="../controller/logout.php" onsubmit="return confirm('Are you sure you want to logout?')">
          <button class="icon-button" type="submit" aria-label="Log out">
            <i class="fas fa-sign-out-alt" aria-hidden="true"></i>
          </button>
        </form>
      </div>
    </header>

    <main class="create-document-page">
      <a class="back-link page-back-link" href="<?= $dashboardPage ?>">
        <i class="fas fa-arrow-left" aria-hidden="true"></i>
        Go Back
      </a>

      <div class="page-heading">
        <p class="brand">New Document</p>
        <h1>Create and Route Document</h1>
        <p class="welcome-message">Upload a PDF, select the route offices, and choose how they should sign.</p>
      </div>

      <section class="create-document-panel">
        <?php if ($error !== ''): ?>
          <div class="form-message error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
          <div class="form-message success" role="status"><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>

        <form class="create-document-form" method="post" action="../controller/member_create_document.php" enctype="multipart/form-data">
          <div class="form-field full-width">
            <label for="documentTitle">Document Title</label>
            <input id="documentTitle" name="title" type="text" maxlength="255" value="<?= htmlspecialchars($oldTitle, ENT_QUOTES, 'UTF-8') ?>" required />
          </div>

          <div class="form-field">
            <label for="documentType">Document Type</label>
            <select id="documentType" name="type_id" required>
              <option value="">Select document type</option>
              <?php foreach ($documentTypes as $type): ?>
                <option value="<?= (int) $type['type_id'] ?>"><?= htmlspecialchars((string) $type['type_name'], ENT_QUOTES, 'UTF-8') ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <fieldset class="form-field full-width routing-mode-field">
            <legend>Sending Method</legend>
            <div class="routing-mode-options">
              <label class="routing-mode-option">
                <input type="radio" name="routing_mode" value="sequential" <?= $oldRoutingMode === 'sequential' ? 'checked' : '' ?> />
                <span>
                  <strong>Sequential Send</strong>
                  <small>Offices sign one at a time following the order below.</small>
                </span>
              </label>
              <label class="routing-mode-option">
                <input type="radio" name="routing_mode" value="simultaneous" <?= $oldRoutingMode === 'simultaneous' ? 'checked' : '' ?> />
                <span>
                  <strong>Simultaneous Send</strong>
                  <small>All selected offices can receive and sign independently.</small>
                </span>
              </label>
            </div>
          </fieldset>

          <div class="form-field full-width route-offices-field">
            <label>Route Offices</label>
            <small id="routeModeHelp">Offices sign one at a time in the order shown.</small>

            <div id="routeOffices" class="route-offices">
              <div class="route-office-row">
                <span class="route-office-number">Step 1</span>
                <select name="office_ids[]" required>
                  <option value="">Select destination office</option>
                  <?php foreach ($offices as $office): ?>
                    <option value="<?= (int) $office['office_id'] ?>"><?= htmlspecialchars((string) $office['office_name'], ENT_QUOTES, 'UTF-8') ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="route-office-buttons">
              <button id="addRouteOffice" class="add-office-button" type="button">
                <i class="fas fa-plus" aria-hidden="true"></i>
                Add Another Office
              </button>
              <button id="removeRouteOffice" class="add-office-button" type="button">
                <i class="fas fa-minus" aria-hidden="true"></i>
                Remove One Office
              </button>
            </div>
          </div>

          <div class="form-field full-width">
            <label for="documentFile">PDF Document</label>
            <input id="documentFile" name="document_file" type="file" accept="application/pdf,.pdf" required />
            <small>PDF only, up to 10 MB.</small>
          </div>

          <div class="form-actions full-width">
            <button class="submit-document-button" type="submit">
              <i class="fas fa-paper-plane" aria-hidden="true"></i>
              Create and Route
            </button>
          </div>
        </form>
      </section>
    </main>

    <script src="../js/member-dashboard.js"></script>
    <script>
      const routeOffices = document.getElementById('routeOffices');
      const addRouteOffice = document.getElementById('addRouteOffice');
      const removeRouteOffice = document.getElementById('removeRouteOffice');
      const routeModeHelp = document.getElementById('routeModeHelp');
      const routingModeInputs = document.querySelectorAll('input[name="routing_mode"]');
      const maximumRoutes = 5;

      const selectedRoutingMode = () =>
        document.querySelector('input[name="routing_mode"]:checked').value;

      const updateRouteRows = () => {
        const rows = [...routeOffices.querySelectorAll('.route-office-row')];
        const isSequential = selectedRoutingMode() === 'sequential';

        rows.forEach((row, index) => {
          row.querySelector('.route-office-number').textContent = `${isSequential ? 'Step' : 'Office'} ${index + 1}`;
        });

        routeModeHelp.textContent = isSequential
          ? 'Offices sign one at a time in the order shown.'
          : 'All selected offices receive the document and can sign independently.';

        addRouteOffice.disabled = rows.length >= maximumRoutes;
        addRouteOffice.innerHTML = rows.length >= maximumRoutes
          ? '<i class="fas fa-check" aria-hidden="true"></i> Maximum 5 Offices'
          : '<i class="fas fa-plus" aria-hidden="true"></i> Add Another Office';
        removeRouteOffice.disabled = rows.length === 1;
      };

      const updateAvailableOffices = () => {
        const selects = [...routeOffices.querySelectorAll('select')];
        const selectedOfficeIds = new Set(
          selects.map((select) => select.value).filter(Boolean),
        );

        selects.forEach((select) => {
          [...select.options].forEach((option) => {
            option.disabled = option.value !== ''
              && option.value !== select.value
              && selectedOfficeIds.has(option.value);
          });
        });
      };

      routeOffices.addEventListener('change', updateAvailableOffices);

      addRouteOffice.addEventListener('click', () => {
        if (routeOffices.children.length >= maximumRoutes) {
          return;
        }

        const firstRow = routeOffices.querySelector('.route-office-row');
        const newRow = firstRow.cloneNode(true);
        newRow.querySelector('select').value = '';
        newRow.querySelector('select').setAttribute('required', '');
        routeOffices.appendChild(newRow);
        updateAvailableOffices();
        updateRouteRows();
      });

      removeRouteOffice.addEventListener('click', () => {
        if (routeOffices.children.length === 1) {
          return;
        }

        routeOffices.lastElementChild.remove();
        updateAvailableOffices();
        updateRouteRows();
      });

      routingModeInputs.forEach((input) => input.addEventListener('change', updateRouteRows));
      updateRouteRows();
    </script>
  </body>
</html>
