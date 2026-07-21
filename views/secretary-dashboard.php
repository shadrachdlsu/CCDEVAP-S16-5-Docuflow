<?php require_once '../controllers/SecretaryDashboardController.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Docuflow – Secretary Dashboard</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css" />
  <link rel="stylesheet" href="../css/secretary.css" />
  <style>
    .success-msg { color: #047857; background: #d1fae5; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-weight: 500; }
    .error-msg { color: #dc2626; background: #fee2e2; padding: 12px; border-radius: 6px; margin-bottom: 16px; font-weight: 500; }
    .page { display: none; }
    .page.active { display: block; }
  </style>
</head>
<body class="secretary-body">
  <!-- TOP HEADER -->
  <header class="secretary-header">
    <div class="header-left">
      <span class="web-logo">Docuflow</span>
    </div>
    <div class="header-right">
      <div class="user-info">
        <span class="user-email"><?= htmlspecialchars($userEmail) ?></span>
        <span class="user-role">Secretary</span>
      </div>
      <div class="header-actions">
        <button class="icon-btn toggle-theme" aria-label="Toggle dark/light mode">
          <i class="fas fa-moon"></i>
        </button>
        <a href="../controllers/LogoutController.php" class="icon-btn logout-btn" aria-label="Exit / Logout">
          <i class="fas fa-sign-out-alt"></i>
        </a>
      </div>
    </div>
  </header>

  <!-- SIDEBAR + MAIN LAYOUT -->
  <div class="app-layout">
    <!-- STICKY SIDEBAR NAVIGATION -->
    <nav class="sidebar-nav" id="sidebar-nav">
      <ul>
        <li><a href="#dashboard" class="nav-link active" data-target="dashboard"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
        <li><a href="#create" class="nav-link" data-target="create"><i class="fas fa-plus-circle"></i> Create Document</a></li>
        <li><a href="#documents" class="nav-link" data-target="documents"><i class="fas fa-file-alt"></i> All Documents</a></li>
        <li><a href="#receive" class="nav-link" data-target="receive"><i class="fas fa-inbox"></i> Receive</a></li>
        <li><a href="#pending" class="nav-link" data-target="pending"><i class="fas fa-clock"></i> Pending</a></li>
        <li><a href="#release" class="nav-link" data-target="release"><i class="fas fa-paper-plane"></i> Release / Forward</a></li>
        <li><a href="#types" class="nav-link" data-target="types"><i class="fas fa-tags"></i> Document Types</a></li>
      </ul>
    </nav>

    <!-- MAIN CONTENT AREA -->
    <main class="main-content" id="main-content">

      <?php if(isset($_SESSION['success'])): ?>
        <div class="success-msg"><?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
      <?php endif; ?>
      <?php if(isset($_SESSION['error'])): ?>
        <div class="error-msg"><?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
      <?php endif; ?>

      <!-- DASHBOARD PAGE -->
      <div class="page active" id="page-dashboard">
        <h2 class="page-title"><?= htmlspecialchars($officeName) ?> Dashboard</h2>
        <div class="stats-row" id="dashboard-stats">
          <div class="stat-card">
            <span class="stat-icon" style="color:var(--brand-600);"><i class="fas fa-folder-open"></i></span>
            <div class="stat-data"><h3>Total Documents</h3><p><?= $stats['total'] ?></p></div>
          </div>
          <div class="stat-card">
            <span class="stat-icon" style="color:var(--amber-600);"><i class="fas fa-clock"></i></span>
            <div class="stat-data"><h3>Pending / In Progress</h3><p><?= $stats['pending'] ?></p></div>
          </div>
          <div class="stat-card">
            <span class="stat-icon" style="color:var(--emerald-600);"><i class="fas fa-file-signature"></i></span>
            <div class="stat-data"><h3>Signed</h3><p><?= $stats['signed'] ?></p></div>
          </div>
          <div class="stat-card">
            <span class="stat-icon" style="color:var(--gray-600);"><i class="fas fa-check-double"></i></span>
            <div class="stat-data"><h3>Finished</h3><p><?= $stats['finished'] ?></p></div>
          </div>
        </div>
      </div>

      <!-- CREATE DOCUMENT PAGE -->
      <div class="page" id="page-create">
        <h2 class="page-title">Create New Document</h2>
        <form action="../controllers/SecretaryCreateController.php" method="POST" enctype="multipart/form-data" class="create-panel">
          <div class="form-field">
            <label class="field-label">Document Title <span class="required">*</span></label>
            <input type="text" name="title" class="field-input" placeholder="Enter document title" required />
          </div>
          <div class="form-field">
            <label class="field-label">Document Type <span class="required">*</span></label>
            <select name="type_id" class="field-select" required>
              <option value="">-- Select Type --</option>
              <?php foreach($documentTypes as $type): ?>
                <option value="<?= htmlspecialchars($type['type_id']) ?>"><?= htmlspecialchars($type['type_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-field">
            <label class="field-label">Upload Document (PDF)</label>
            <input type="file" name="document_file" accept=".pdf" class="field-input" style="padding: 10px;" />
          </div>
          <button type="submit" class="btn-primary">Create & Route Document</button>
        </form>
      </div>

      <!-- ALL DOCUMENTS PAGE -->
      <div class="page" id="page-documents">
        <h2 class="page-title">All Documents</h2>
        <table id="table-all-documents" class="display nowrap" width="100%">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Title</th>
              <th>Type</th>
              <th>Date Created</th>
              <th>Creator</th>
              <th>Status</th>
              <th>Current Office</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($allDocs as $doc): ?>
              <tr>
                <td><?= htmlspecialchars($doc['tracking_code']) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?></td>
                <td><?= htmlspecialchars($doc['type_name']) ?></td>
                <td><?= htmlspecialchars(date('M d, Y h:i A', strtotime($doc['created_at']))) ?></td>
                <td><?= htmlspecialchars($doc['creator_name']) ?></td>
                <td><?= htmlspecialchars($doc['status']) ?></td>
                <td><?= htmlspecialchars($doc['current_office_name']) ?></td>
                <td>
                  <?php if (!empty($doc['file_path'])): ?>
                    <button class="btn-small btn-view" data-file="<?= htmlspecialchars($doc['file_path']) ?>">View PDF</button>
                  <?php endif; ?>
                  <button class="btn-small btn-trail" data-id="<?= htmlspecialchars($doc['document_id']) ?>">Paper Trail</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- RECEIVE DOCUMENTS PAGE -->
      <div class="page" id="page-receive">
        <h2 class="page-title">Documents to Receive</h2>
        <table id="table-receive" class="display nowrap" width="100%">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Title</th>
              <th>Type</th>
              <th>Creator</th>
              <th>Status</th>
              <th>Assignees</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach(array_filter($allDocs, fn($d) => !in_array($d['status'], ['Completed', 'Recalled'])) as $doc): ?>
              <tr>
                <td><?= htmlspecialchars($doc['tracking_code']) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?></td>
                <td><?= htmlspecialchars($doc['type_name']) ?></td>
                <td><?= htmlspecialchars($doc['creator_name']) ?></td>
                <td><?= htmlspecialchars($doc['status']) ?></td>
                <td><?= htmlspecialchars($doc['assignee_names'] ?? 'None') ?></td>
                <td>
                  <button class="btn-small btn-trail" data-id="<?= htmlspecialchars($doc['document_id']) ?>">Paper Trail</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- PENDING DOCUMENTS PAGE -->
      <div class="page" id="page-pending">
        <h2 class="page-title">Pending Documents</h2>
        <table id="table-pending" class="display nowrap" width="100%">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Title</th>
              <th>Type</th>
              <th>Creator</th>
              <th>Status</th>
              <th>Assignees</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach(array_filter($allDocs, fn($d) => in_array($d['status'], ['Created','Pending','Received','Released','For Signature','Rejected'])) as $doc): ?>
              <tr>
                <td><?= htmlspecialchars($doc['tracking_code']) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?></td>
                <td><?= htmlspecialchars($doc['type_name']) ?></td>
                <td><?= htmlspecialchars($doc['creator_name']) ?></td>
                <td><span style="background:#fef3c7; padding:2px 8px; border-radius:12px; font-weight:600; font-size:0.8rem;">Pending</span></td>
                <td><?= htmlspecialchars($doc['assignee_names'] ?? 'None') ?></td>
                <td style="display: flex; gap: 4px;">
                  <button class="btn-primary btn-sm btn-assign" data-id="<?= htmlspecialchars($doc['document_id']) ?>" data-title="<?= htmlspecialchars($doc['title']) ?>">Assign</button>
                  <form method="POST" action="../controllers/SecretaryStatusController.php" onsubmit="return confirm('Mark as Finished?');">
                      <input type="hidden" name="action" value="finish">
                      <input type="hidden" name="document_id" value="<?= htmlspecialchars($doc['document_id']) ?>">
                      <button type="submit" class="btn-primary btn-sm">Finish</button>
                  </form>
                  <form method="POST" action="../controllers/SecretaryStatusController.php" onsubmit="return confirm('Cancel this document?');">
                      <input type="hidden" name="action" value="cancel">
                      <input type="hidden" name="document_id" value="<?= htmlspecialchars($doc['document_id']) ?>">
                      <button type="submit" class="btn-primary btn-sm">Cancel</button>
                  </form>
                  <button class="btn-primary btn-sm btn-trail" data-id="<?= htmlspecialchars($doc['document_id']) ?>">Trail</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- RELEASE / FORWARD PAGE -->
      <div class="page" id="page-release">
        <h2 class="page-title">Release / Forward Documents</h2>
        <table id="table-release" class="display nowrap" width="100%">
          <thead>
            <tr>
              <th>Tracking Code</th>
              <th>Title</th>
              <th>Type</th>
              <th>Creator</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($allDocs as $doc): ?>
              <tr>
                <td><?= htmlspecialchars($doc['tracking_code']) ?></td>
                <td><?= htmlspecialchars($doc['title']) ?></td>
                <td><?= htmlspecialchars($doc['type_name']) ?></td>
                <td><?= htmlspecialchars($doc['creator_name']) ?></td>
                <td><?= htmlspecialchars($doc['status']) ?></td>
                <td>
                  <button class="btn-primary btn-sm btn-forward" data-id="<?= htmlspecialchars($doc['document_id']) ?>" data-title="<?= htmlspecialchars($doc['title']) ?>">Forward</button>
                  <button class="btn-primary btn-sm btn-trail" data-id="<?= htmlspecialchars($doc['document_id']) ?>">Trail</button>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <!-- DOCUMENT TYPES PAGE -->
      <div class="page" id="page-types">
        <h2 class="page-title">My Office Document Types</h2>
        <button class="btn-primary" id="btn-add-type" style="margin-bottom: 16px;"><i class="fas fa-plus"></i> Add Type</button>
        <table id="table-types" class="display nowrap" width="100%">
          <thead>
            <tr>
              <th>Type Name</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($documentTypes as $type): ?>
              <tr>
                <td><?= htmlspecialchars($type['type_name']) ?></td>
                <td style="display: flex; gap: 4px;">
                  <button class="btn-primary btn-sm btn-edit-type" data-id="<?= htmlspecialchars($type['type_id']) ?>" data-name="<?= htmlspecialchars($type['type_name']) ?>">Edit</button>
                  <form method="POST" action="../controllers/SecretaryTypeActionController.php" onsubmit="return confirm('Delete this type?');">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="type_id" value="<?= htmlspecialchars($type['type_id']) ?>">
                      <button type="submit" class="btn-primary btn-sm">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </main>
  </div>

  <!-- Assign Modal -->
  <div class="modal-overlay" id="modal-assign">
    <div class="modal">
      <div class="modal-header">
        <h3>Assign Document</h3>
        <button class="modal-close" data-close="modal-assign">&times;</button>
      </div>
      <div class="modal-body">
        <form method="POST" action="../controllers/SecretaryAssignController.php">
            <input type="hidden" name="action" value="assign">
            <input type="hidden" name="document_id" id="assign-doc-id">
            <div class="form-field">
              <label>Document</label>
              <p id="assign-doc-title" class="static-field"></p>
            </div>
            <div class="form-field">
              <label>Select Members</label>
              <select name="member_ids[]" multiple required style="min-height: 120px; width: 100%; border: 1px solid var(--gray-300); border-radius: 4px; padding: 8px;">
                  <?php foreach($members as $m): ?>
                      <option value="<?= htmlspecialchars($m['user_id']) ?>"><?= htmlspecialchars($m['email']) ?> (<?= htmlspecialchars($m['full_name']) ?>)</option>
                  <?php endforeach; ?>
              </select>
              <p style="font-size: 0.8rem; color: var(--gray-500); margin-top: 4px;">Hold Ctrl (Windows) or Cmd (Mac) to select multiple members.</p>
            </div>
            <button type="submit" class="btn-primary">Confirm Assign</button>
        </form>
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
        <form method="POST" action="../controllers/SecretaryForwardController.php">
            <input type="hidden" name="action" value="forward">
            <input type="hidden" name="document_id" id="forward-doc-id">
            <div class="form-field">
                <label>Document</label>
                <p id="forward-doc-title" class="static-field"></p>
            </div>
            <div class="form-field">
                <label>Select Target Office</label>
                <select name="office_id" class="field-select" required>
                    <option value="">-- Select Office --</option>
                    <?php foreach($forwardableOffices as $office): ?>
                        <option value="<?= htmlspecialchars($office['office_id']) ?>"><?= htmlspecialchars($office['office_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary">Confirm Forward</button>
        </form>
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
        <form method="POST" action="../controllers/SecretaryTypeActionController.php">
            <input type="hidden" name="action" id="type-action" value="add">
            <input type="hidden" name="type_id" id="type-id">
            <div class="form-field">
                <label>Type Name <span class="required">*</span></label>
                <input type="text" name="type_name" id="type-name" class="field-input" placeholder="e.g. Internal Memo" required />
            </div>
            <button type="submit" class="btn-primary">Save</button>
        </form>
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

  <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
  <script src="../js/secretary.js"></script>
</body>
</html>
