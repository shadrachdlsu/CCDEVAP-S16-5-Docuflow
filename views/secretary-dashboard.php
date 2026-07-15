<?php
session_start();

// ACCESS CONTROL 
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header('Location: login.php');
    exit;
}

// LOAD MODELS 
require_once __DIR__ . '/models/document.php';
require_once __DIR__ . '/models/documentType.php';
require_once __DIR__ . '/models/office.php';

$documentModel = new Document();
$officeModel   = new Office();

$officeId      = $_SESSION['office_id'];
$officeName    = $_SESSION['office_name'] ?? 'My Office';
$userEmail     = $_SESSION['email'] ?? 'secretary@docuflow.local';
$userFullName  = $_SESSION['full_name'] ?? 'Secretary';

// DASHBOARD STATS 
$allDocs    = $documentModel->getDocumentsForOffice($officeId);
$pending    = count(array_filter($allDocs, fn($d) => in_array($d['status'], ['Created','Pending','Received','Released','For Signature','Rejected'])));
$signed     = count(array_filter($allDocs, fn($d) => $d['status'] === 'Signed'));
$finished   = count(array_filter($allDocs, fn($d) => in_array($d['status'], ['Completed','Recalled'])));

$initialStats = [
    'total'    => count($allDocs),
    'pending'  => $pending,
    'signed'   => $signed,
    'finished' => $finished,
];

// Office list for forward modal 
$offices = $officeModel->getAllOffices();
$forwardableOffices = array_filter($offices, fn($o) => $o['office_id'] != $officeId);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Docuflow – Secretary Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.3.6/css/buttons.dataTables.min.css" />
  <link rel="stylesheet" href="css/secretary.css" />
</head>
<body class="secretary-body">
  <!-- TOP HEADER -->
  <header class="secretary-header">
    <div class="header-left">
      <span class="web-logo">Docuflow</span>
    </div>
    <div class="header-right">
      <div class="user-info">
        <span class="user-email" id="current-user-email"><?= htmlspecialchars($userEmail) ?></span>
        <span class="user-role">Secretary</span>
      </div>
      <div class="header-actions">
        <button class="icon-btn toggle-theme" aria-label="Toggle dark/light mode">
          <i class="fas fa-moon"></i>
        </button>
        <button class="icon-btn logout-btn" aria-label="Exit / Logout">
          <i class="fas fa-sign-out-alt"></i>
        </button>
      </div>
    </div>
  </header>

  <!-- SIDEBAR + MAIN LAYOUT -->
  <div class="app-layout">
    <!-- STICKY SIDEBAR NAVIGATION -->
    <nav class="sidebar-nav" id="sidebar-nav">
      <ul>
        <li><a href="#" class="nav-link active" data-page="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="#" class="nav-link" data-page="create"><i class="fas fa-plus-circle"></i> Create Document</a></li>
        <li><a href="#" class="nav-link" data-page="documents"><i class="fas fa-file-alt"></i> All Documents</a></li>
        <li><a href="#" class="nav-link" data-page="receive"><i class="fas fa-inbox"></i> Receive</a></li>
        <li><a href="#" class="nav-link" data-page="pending"><i class="fas fa-clock"></i> Pending</a></li>
        <li><a href="#" class="nav-link" data-page="release"><i class="fas fa-paper-plane"></i> Release / Forward</a></li>
        <li><a href="#" class="nav-link" data-page="types"><i class="fas fa-tags"></i> Document Types</a></li>
      </ul>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content" id="main-content">
      <!-- Dashboard Page -->
      <div class="page active" id="page-dashboard">
        <h2 class="page-title"><?= htmlspecialchars($officeName) ?> Dashboard</h2>
        <div class="stats-row" id="dashboard-stats"></div>
        <div class="dashboard-charts" id="dashboard-charts"></div>
      </div>

      <!-- Create Document Page -->
      <div class="page" id="page-create">
        <h2 class="page-title">Create New Document</h2>
        <div class="create-panel">
          <div class="form-field">
            <label class="field-label">Document Title <span class="required">*</span></label>
            <input type="text" id="create-title" class="field-input" placeholder="Enter document title" />
          </div>
          <div class="form-field">
            <label class="field-label">Document Type <span class="required">*</span></label>
            <select id="create-type" class="field-select">
              <option value="">-- Select Type --</option>
              <!-- populated by JS from office types -->
            </select>
          </div>
          <div class="form-field">
            <label class="field-label">Upload Document (PDF)</label>
            <div class="upload-area" id="upload-area">
              <i class="fas fa-cloud-upload-alt upload-icon"></i>
              <p>Click to upload or drag and drop</p>
              <span class="upload-hint">PDF files only</span>
            </div>
            <input type="file" id="document-file" accept=".pdf" style="display:none;">
          </div>
          <button class="btn-primary btn-route-document">Create & Route Document</button>
        </div>
      </div>

      <!-- All Documents Page -->
      <div class="page" id="page-documents">
        <h2 class="page-title">All Documents</h2>
        <table id="table-all-documents" class="display nowrap" width="100%"></table>
      </div>

      <!-- Receive Page -->
      <div class="page" id="page-receive">
        <h2 class="page-title">Documents to Receive</h2>
        <table id="table-receive" class="display nowrap" width="100%"></table>
      </div>

      <!-- Pending Page -->
      <div class="page" id="page-pending">
        <h2 class="page-title">Pending Documents</h2>
        <table id="table-pending" class="display nowrap" width="100%"></table>
      </div>

      <!-- Release / Forward Page -->
      <div class="page" id="page-release">
        <h2 class="page-title">Release / Forward Documents</h2>
        <table id="table-release" class="display nowrap" width="100%"></table>
      </div>

      <!-- Document Types Page -->
      <div class="page" id="page-types">
        <h2 class="page-title">My Office Document Types</h2>
        <button class="btn-primary" id="btn-add-type"><i class="fas fa-plus"></i> Add Type</button>
        <table id="table-types" class="display nowrap" width="100%"></table>
      </div>
    </main>
  </div>

  <!-- MODALS -->
  <!-- Assign Modal -->
  <div class="modal-overlay" id="modal-assign">
    <div class="modal">
      <div class="modal-header">
        <h3>Assign Document</h3>
        <button class="modal-close" data-close="modal-assign">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-field">
          <label>Document</label>
          <p id="assign-doc-title" class="static-field"></p>
        </div>
        <div class="form-field">
          <label>Select Members (by email)</label>
          <div class="autocomplete-wrapper">
            <input type="text" id="assign-member-search" placeholder="Type to search..." />
            <div class="autocomplete-dropdown" id="assign-member-dropdown"></div>
          </div>
          <ul class="selected-list" id="assign-selected-list"></ul>
        </div>
        <button class="btn-primary" id="btn-confirm-assign">Assign</button>
      </div>
    </div>
  </div>

  <!-- Forward Modal -->
  <div class="modal-overlay" id="modal-forward">
    <div class="modal">
      <div class="modal-header">
        <h3>Forward Document</h3>
        <button class="modal-close" data-close="modal-forward">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-field">
          <label>Document</label>
          <p id="forward-doc-title" class="static-field"></p>
        </div>
        <div class="form-field">
          <label>Select Offices</label>
          <div class="checkbox-group" id="forward-offices-list">
            <?php foreach ($forwardableOffices as $office): ?>
              <label><input type="checkbox" value="<?= $office['office_id'] ?>"> <?= htmlspecialchars($office['office_name']) ?></label>
            <?php endforeach; ?>
          </div>
        </div>
        <button class="btn-primary" id="btn-confirm-forward">Forward</button>
      </div>
    </div>
  </div>

  <!-- Paper Trail Modal -->
  <div class="modal-overlay" id="modal-trail">
    <div class="modal">
      <div class="modal-header">
        <h3>Paper Trail</h3>
        <button class="modal-close" data-close="modal-trail">&times;</button>
      </div>
      <div class="modal-body">
        <ul class="trail-list" id="trail-list"></ul>
      </div>
    </div>
  </div>

  <!-- Document Type Modal -->
  <div class="modal-overlay" id="modal-type">
    <div class="modal">
      <div class="modal-header">
        <h3 id="type-modal-title">Add Document Type</h3>
        <button class="modal-close" data-close="modal-type">&times;</button>
      </div>
      <div class="modal-body">
        <div class="form-field">
          <label>Type Name <span class="required">*</span></label>
          <input type="text" id="type-name" class="field-input" placeholder="e.g. Internal Memo" />
        </div>
        <button class="btn-primary" id="btn-save-type">Save</button>
      </div>
    </div>
  </div>

  <!-- JS Libraries -->
  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.3.6/js/buttons.html5.min.js"></script>

  <!-- Pass backend data to JS -->
  <script>
    window.DOCUFLOW = {
      officeId: <?= (int)$officeId ?>,
      initialStats: <?= json_encode($initialStats) ?>,
      documentsEndPoint: 'controllers/get_documents.php',
      membersEndPoint: 'controllers/get_members.php',
      actionsEndPoint: 'controllers/secretary_actions.php',
      typesEndPoint: 'controllers/get_document_types.php',
      typesCrudEndPoint: 'controllers/document_type_crud.php',
      trailEndPoint: 'controllers/get_trail.php',
      createEndPoint: 'controllers/create_document.php'
    };
  </script>
  <script src="js/secretary.js"></script>
</body>
</html>
