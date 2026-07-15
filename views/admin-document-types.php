<!doctype html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Docuflow - Document Types</title>
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
              <button class="icon-btn logout-btn" aria-label="Exit / Logout">
                <i class="fas fa-sign-out-alt"></i>
              </button>
            </div>
          </div>
        </header>

        <section class="admin-preview-panel">
          <div class="preview-header">
            <h2 class="section-title">Document Types</h2>
            <p class="preview-description">Create and edit system-wide document types.</p>
          </div>
          <div class="admin-preview-content" id="admin-preview-content">
            <div style="margin-bottom:16px;">
              <button class="btn-primary" onclick="window.openDocTypeModal()">Add Document Type</button>
            </div>
            <table id="docTypesTable" class="display" style="width:100%">
              <thead>
                <tr>
                  <th>Type Name</th>
                  <th>Assigned Offices</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </section>
      </main>
    </div>

    <!-- Document Type Modal -->
    <div id="docTypeModal" class="modal-overlay">
      <div class="modal-content">
        <div class="modal-header">
          <h3 id="docTypeModalTitle" class="section-title" style="margin:0;">Add Document Type</h3>
          <button class="close-btn icon-btn" type="button" onclick="closeModal('docTypeModal')"><i class="fas fa-times"></i></button>
        </div>
        <form id="docTypeForm" class="admin-form" style="grid-template-columns: 1fr;">
          <input type="hidden" id="docTypeId" />
          <label class="admin-field">
            <span>Type Name <span style="color: #ef4444">*</span></span>
            <input type="text" id="docTypeName" required placeholder="e.g. Board Resolution" />
          </label>
          <label class="admin-field">
            <span>Assign to Offices <span style="color: #ef4444">*</span></span>
            <select id="docTypeOffices" multiple required style="min-height: 80px;"></select>
          </label>
          <div style="display:flex; justify-content:flex-end; gap:8px; margin-top:16px;">
            <button type="button" class="admin-submit" style="background:var(--gray-300); color:var(--gray-700);" onclick="closeModal('docTypeModal')">Cancel</button>
            <button type="submit" class="admin-submit">Save</button>
          </div>
        </form>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/admin-document-types.js"></script>
  </body>
</html>
