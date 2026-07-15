$(document).ready(function () {
  // GLOBAL STATE 
  const CONFIG = window.DOCUFLOW;
  let allDocs = [];       // full list of documents
  let members = [];       // office members (autocomplete)
  let officeTypes = [];   // office document types

  // HELPERS 
  function getDisplayStatus(doc) {
    if (['Created','Pending','Received','Released','For Signature','Rejected'].includes(doc.status)) return 'Pending';
    if (doc.status === 'Signed') return 'Signed';
    if (['Completed','Recalled'].includes(doc.status)) return 'Finished';
    return doc.status;
  }

  function getStatusBadge(displayStatus) {
    const colors = {
      Pending: "#fef3c7",
      Signed: "#d1fae5",
      Finished: "#dbeafe"
    };
    return `<span style="background:${colors[displayStatus]}; padding:2px 8px; border-radius:12px; font-weight:600; font-size:0.8rem;">${displayStatus}</span>`;
  }

  function openModal(id) { $(`#${id}`).addClass("active"); }
  function closeModal(id) { $(`#${id}`).removeClass("active"); }

  // NAVIGATION 
  $(".nav-link").click(function (e) {
    e.preventDefault();
    $(".nav-link").removeClass("active");
    $(this).addClass("active");
    const page = $(this).data("page");
    $(".page").removeClass("active");
    $(`#page-${page}`).addClass("active");
    if (page === "dashboard") renderDashboard();
    if (page === "create") loadCreateFormTypes();
  });

  // DASHBOARD (reactive) 
  function renderDashboard() {
    const total = allDocs.length;
    const pending = allDocs.filter(d => getDisplayStatus(d) === 'Pending').length;
    const signed = allDocs.filter(d => getDisplayStatus(d) === 'Signed').length;
    const finished = allDocs.filter(d => getDisplayStatus(d) === 'Finished').length;

    $("#dashboard-stats").html(`
      <div class="stat-card"><div class="stat-number">${total}</div><div class="stat-label">Total Documents</div></div>
      <div class="stat-card"><div class="stat-number">${pending}</div><div class="stat-label">Pending</div></div>
      <div class="stat-card"><div class="stat-number">${signed}</div><div class="stat-label">Signed</div></div>
      <div class="stat-card"><div class="stat-number">${finished}</div><div class="stat-label">Finished</div></div>
    `);

    const pieTotal = pending + signed + finished || 1;
    const pieData = [
      { label: "Pending", value: pending, color: "#f59e0b" },
      { label: "Signed", value: signed, color: "#059669" },
      { label: "Finished", value: finished, color: "#2563eb" },
    ];
    const gradient = pieData.map((d, i, arr) => {
      const startPercent = i === 0 ? 0 : arr.slice(0, i).reduce((sum, e) => sum + (e.value / pieTotal) * 100, 0);
      const endPercent = startPercent + (d.value / pieTotal) * 100;
      return `${d.color} ${startPercent}% ${endPercent}%`;
    }).join(", ");
    $("#dashboard-charts").html(`
      <div class="pie-preview-card">
        <div class="pie-chart" style="background: conic-gradient(${gradient})"><span>${pieTotal}</span></div>
        <div class="pie-details">
          ${pieData.map(d => `<div class="pie-row"><span class="pie-swatch" style="background:${d.color}"></span><span>${d.label}</span><strong>${d.value}</strong></div>`).join("")}
        </div>
      </div>
    `);
  }

  // DATA LOADING 
  function loadDocuments() {
    return $.getJSON(CONFIG.documentsEndPoint)
      .done(function (data) {
        allDocs = data;
        refreshAllTables();
        renderDashboard();
      })
      .fail(function () {
        alert("Failed to load documents.");
      });
  }

  function loadMembers() {
    $.getJSON(CONFIG.membersEndPoint)
      .done(function (data) { members = data; });
  }

  function loadOfficeTypes() {
    $.getJSON(CONFIG.typesEndPoint)
      .done(function (data) {
        officeTypes = data;
        refreshTypesTable();
        loadCreateFormTypes();
      });
  }

  function loadCreateFormTypes() {
    const select = $("#create-type");
    select.empty();
    select.append('<option value="">-- Select Type --</option>');
    officeTypes.forEach(t => {
      select.append(`<option value="${t.type_id}">${t.type_name}</option>`);
    });
  }

  // DATATABLES 
  function buildDocRow(doc, actionsHtml) {
    return [
      doc.tracking_code,
      doc.title,
      doc.type_name,
      doc.creator_name,
      getStatusBadge(getDisplayStatus(doc)),
      doc.assignee_names || "None",
      actionsHtml
    ];
  }

  function getActionsForDoc(doc) {
    let html = '';
    html += `<button class="btn-primary btn-sm btn-assign" data-id="${doc.document_id}">Assign</button> `;
    html += `<button class="btn-primary btn-sm btn-forward" data-id="${doc.document_id}">Forward</button> `;
    if (getDisplayStatus(doc) === 'Signed') {
      html += `<button class="btn-primary btn-sm btn-finish" data-id="${doc.document_id}">Finish</button> `;
    }
    if (getDisplayStatus(doc) !== 'Finished') {
      html += `<button class="btn-primary btn-sm btn-cancel-doc" data-id="${doc.document_id}">Cancel</button> `;
    }
    html += `<button class="btn-primary btn-sm btn-trail" data-id="${doc.document_id}">Trail</button>`;
    return html;
  }

  function initDocTable(tableId, filterFn) {
    const filtered = allDocs.filter(filterFn);
    const data = filtered.map(doc => buildDocRow(doc, getActionsForDoc(doc)));
    if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
      $(`#${tableId}`).DataTable().clear().rows.add(data).draw();
    } else {
      $(`#${tableId}`).DataTable({
        data: data,
        columns: [
          { title: "ID" },
          { title: "Title" },
          { title: "Type" },
          { title: "Creator" },
          { title: "Status" },
          { title: "Assignees" },
          { title: "Actions", orderable: false }
        ],
        dom: 'Bfrtip',
        buttons: ['copy', 'csv', 'excel', 'pdf'],
        destroy: true,
        responsive: true
      });
    }
  }

  function refreshAllTables() {
    initDocTable("table-all-documents", () => true);
    initDocTable("table-receive", d => !['Completed','Recalled'].includes(d.status));
    initDocTable("table-pending", d => getDisplayStatus(d) === 'Pending');
    initDocTable("table-release", d => true);
  }

  // ASSIGN MODAL 
  let selectedDocId = null;
  let selectedMemberIds = [];

  function renderSelectedMembers() {
    const list = $("#assign-selected-list");
    list.empty();
    selectedMemberIds.forEach(id => {
      const member = members.find(m => m.user_id == id);
      const email = member ? member.email : `User #${id}`;
      list.append(`<li>${email} <button class="remove-member" data-id="${id}"><i class="fas fa-times"></i></button></li>`);
    });
  }

  $(document).on("click", ".btn-assign", function () {
    selectedDocId = $(this).data("id");
    const doc = allDocs.find(d => d.document_id == selectedDocId);
    $("#assign-doc-title").text(doc ? doc.title : '');
    $("#assign-member-search").val("");
    selectedMemberIds = [];
    renderSelectedMembers();
    openModal("modal-assign");
  });

  $(document).on("click", ".remove-member", function () {
    const id = $(this).data("id");
    selectedMemberIds = selectedMemberIds.filter(i => i != id);
    renderSelectedMembers();
  });

  $("#assign-member-search").on("input", function () {
    const query = $(this).val().toLowerCase();
    const dropdown = $("#assign-member-dropdown");
    dropdown.empty();
    if (!query) { dropdown.removeClass("active"); return; }
    const filtered = members.filter(m => m.email.toLowerCase().includes(query) && !selectedMemberIds.includes(m.user_id));
    if (filtered.length) {
      filtered.forEach(m => {
        dropdown.append(`<div class="autocomplete-option" data-id="${m.user_id}">${m.email} (${m.full_name})</div>`);
      });
      dropdown.addClass("active");
    } else {
      dropdown.removeClass("active");
    }
  });

  $(document).on("click", ".autocomplete-option", function () {
    const id = parseInt($(this).data("id"));
    if (!selectedMemberIds.includes(id)) {
      selectedMemberIds.push(id);
      renderSelectedMembers();
    }
    $("#assign-member-search").val("");
    $("#assign-member-dropdown").removeClass("active");
  });

  $("#btn-confirm-assign").click(function () {
    if (!selectedDocId) return;
    if (selectedMemberIds.length === 0) {
      alert("Select at least one member.");
      return;
    }
    $.ajax({
      type: "POST",
      url: CONFIG.actionsEndPoint,
      contentType: "application/json",
      data: JSON.stringify({
        action: "assign",
        document_id: selectedDocId,
        member_ids: selectedMemberIds
      }),
      success: function (res) {
        if (res.success) {
          closeModal("modal-assign");
          loadDocuments();
        } else {
          alert(res.message);
        }
      },
      error: function () { alert("Action failed."); }
    });
  });

  // FORWARD MODAL
  $(document).on("click", ".btn-forward", function () {
    selectedDocId = $(this).data("id");
    const doc = allDocs.find(d => d.document_id == selectedDocId);
    $("#forward-doc-title").text(doc ? doc.title : '');
    openModal("modal-forward");
  });

  $("#btn-confirm-forward").click(function () {
    const checked = $("#forward-offices-list input:checked").val();
    if (!checked) { alert("Select an office."); return; }
    $.ajax({
      type: "POST",
      url: CONFIG.actionsEndPoint,
      contentType: "application/json",
      data: JSON.stringify({
        action: "forward",
        document_id: selectedDocId,
        office_id: parseInt(checked)
      }),
      success: function (res) {
        if (res.success) {
          closeModal("modal-forward");
          loadDocuments();
        } else {
          alert(res.message);
        }
      },
      error: function () { alert("Forward failed."); }
    });
  });

  // FINISH & CANCEL 
  $(document).on("click", ".btn-finish", function () {
    if (!confirm("Mark as Finished?")) return;
    const docId = $(this).data("id");
    $.ajax({
      type: "POST",
      url: CONFIG.actionsEndPoint,
      contentType: "application/json",
      data: JSON.stringify({ action: "finish", document_id: docId }),
      success: function (res) {
        if (res.success) loadDocuments();
        else alert(res.message);
      },
      error: function () { alert("Finish failed."); }
    });
  });

  $(document).on("click", ".btn-cancel-doc", function () {
    if (!confirm("Cancel this document?")) return;
    const docId = $(this).data("id");
    $.ajax({
      type: "POST",
      url: CONFIG.actionsEndPoint,
      contentType: "application/json",
      data: JSON.stringify({ action: "cancel", document_id: docId }),
      success: function (res) {
        if (res.success) loadDocuments();
        else alert(res.message);
      },
      error: function () { alert("Cancel failed."); }
    });
  });

  // TRAIL MODAL 
  $(document).on("click", ".btn-trail", function () {
    const docId = $(this).data("id");
    $.getJSON(`${CONFIG.trailEndPoint}?document_id=${docId}`)
      .done(function (trail) {
        const list = $("#trail-list");
        list.empty();
        if (!trail.length) {
          list.append("<li>No actions recorded yet.</li>");
        } else {
          trail.forEach(t => {
            list.append(`<li><strong>${t.action}</strong> by ${t.action_by_name} – ${t.remarks || ''} <br><small>${t.created_at}</small></li>`);
          });
        }
        openModal("modal-trail");
      });
  });

  // DOCUMENT TYPES CRUD 
  function refreshTypesTable() {
    const data = officeTypes.map(t => [
      t.type_name,
      `<button class="btn-primary btn-sm btn-edit-type" data-id="${t.type_id}" data-name="${t.type_name}">Edit</button>
       <button class="btn-primary btn-sm btn-delete-type" data-id="${t.type_id}">Delete</button>`
    ]);
    if ($.fn.DataTable.isDataTable("#table-types")) {
      $("#table-types").DataTable().clear().rows.add(data).draw();
    } else {
      $("#table-types").DataTable({
        data: data,
        columns: [
          { title: "Type Name" },
          { title: "Actions", orderable: false }
        ],
        dom: 't',
        destroy: true
      });
    }
  }

  $("#btn-add-type").click(() => {
    $("#type-modal-title").text("Add Document Type");
    $("#type-name").val("");
    $("#modal-type").data("editing", null);
    openModal("modal-type");
  });

  $(document).on("click", ".btn-edit-type", function () {
    const id = $(this).data("id");
    const name = $(this).data("name");
    $("#type-modal-title").text("Edit Document Type");
    $("#type-name").val(name);
    $("#modal-type").data("editing", id);
    openModal("modal-type");
  });

  $(document).on("click", ".btn-delete-type", function () {
    if (!confirm("Delete this type?")) return;
    const id = $(this).data("id");
    $.ajax({
      type: "POST",
      url: CONFIG.typesCrudEndPoint,
      contentType: "application/json",
      data: JSON.stringify({ action: "delete", type_id: id }),
      success: function (res) {
        if (res.success) loadOfficeTypes();
        else alert(res.message);
      },
      error: function () { alert("Delete failed."); }
    });
  });

  $("#btn-save-type").click(() => {
    const name = $("#type-name").val().trim();
    if (!name) { alert("Type name is required."); return; }
    const editing = $("#modal-type").data("editing");
    const payload = {
      action: editing ? "edit" : "add",
      type_name: name,
      type_id: editing || undefined
    };
    $.ajax({
      type: "POST",
      url: CONFIG.typesCrudEndPoint,
      contentType: "application/json",
      data: JSON.stringify(payload),
      success: function (res) {
        if (res.success) {
          closeModal("modal-type");
          loadOfficeTypes();
        } else {
          alert(res.message);
        }
      },
      error: function () { alert("Save failed."); }
    });
  });

  // CREATE DOCUMENT
  // Upload area click triggers hidden file input
  $("#upload-area").click(function () {
    $("#document-file").click();
  });
  $("#document-file").change(function () {
    const fileName = this.files[0] ? this.files[0].name : '';
    if (fileName) {
      $("#upload-area p").text(fileName);
    } else {
      $("#upload-area p").text("Click to upload or drag and drop");
    }
  });

  // Create & Route button
  $(".btn-route-document").click(function (e) {
    e.preventDefault();
    const title = $("#create-title").val().trim();
    const typeId = $("#create-type").val();
    if (!title) { alert("Please enter a document title."); return; }
    if (!typeId) { alert("Please select a document type."); return; }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('type_id', typeId);
    const fileInput = $("#document-file")[0];
    if (fileInput.files.length > 0) {
      formData.append('document_file', fileInput.files[0]);
    }

    $.ajax({
      type: 'POST',
      url: CONFIG.createEndPoint,
      data: formData,
      processData: false,
      contentType: false,
      success: function (res) {
        if (res && res.success) {
          alert("Document created successfully!");
          $("#create-title").val('');
          $("#create-type").val('');
          $("#document-file").val('');
          $("#upload-area p").text("Click to upload or drag and drop");
          loadDocuments();
        } else {
          alert(res ? res.message : "Creation failed. Please try again.");
        }
      },
      error: function () { alert("Creation failed. Server error."); }
    });
  });

  // GLOBAL MODAL CLOSE
  $(document).on("click", ".modal-close, .modal-overlay", function (e) {
    if (e.target === this) {
      const modalId = $(this).data("close") || $(this).attr("id");
      closeModal(modalId);
    }
  });

  // THEME & LOGOUT 
  $(".toggle-theme").click(() => {
    $("body").toggleClass("dark-mode");
    $(".toggle-theme i").toggleClass("fa-moon fa-sun");
  });

  $(".logout-btn").click(() => {
    if (confirm("Are you sure you want to logout?")) {
      window.location.href = "login.php";
    }
  });

  // INIT 
  loadDocuments();
  loadMembers();
  loadOfficeTypes();
  $(".nav-link[data-page='dashboard']").addClass("active");
  $("#page-dashboard").addClass("active");
});
