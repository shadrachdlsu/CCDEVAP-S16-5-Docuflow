$(document).ready(function () {
  // MOCK DATA 
  const currentOffice = "Finance"; // Secretary's office
  const offices = ["Finance", "HR", "Admin", "Legal", "Operations"];
  const members = [
    { email: "maria.santos@finance.gov", name: "Maria Santos", office: "Finance" },
    { email: "juan.delacruz@finance.gov", name: "Juan Dela Cruz", office: "Finance" },
    { email: "ana.reyes@hr.gov", name: "Ana Reyes", office: "HR" },
    { email: "carlos.mendoza@admin.gov", name: "Carlos Mendoza", office: "Admin" },
    { email: "member@office.gov", name: "Member", office: "Operations" },
  ];

  // Custom document types for this office
  let myOfficeTypes = ["Memorandum", "Budget Proposal", "Leave/Travel", "Contracts"];

  // Documents mock (statuses: Pending, Signed, Finished)
  let documents = [
    { id: "DOC-2024-001", title: "Budget Proposal FY 2024", type: "Budget Proposal", fromOffice: "HR", status: "Pending", assignees: ["maria.santos@finance.gov"], trail: ["Received from HR", "Assigned to Maria Santos"] },
    { id: "DOC-2024-002", title: "Employee Leave Request", type: "Leave/Travel", fromOffice: "Admin", status: "Signed", assignees: ["juan.delacruz@finance.gov"], trail: ["Received from Admin", "Assigned to Juan Dela Cruz", "Signed by Juan Dela Cruz"] },
    { id: "DOC-2024-003", title: "Supply Requisition Form", type: "Memorandum", fromOffice: "Operations", status: "Finished", assignees: ["maria.santos@finance.gov", "juan.delacruz@finance.gov"], trail: ["Received from Operations", "Assigned to Maria Santos, Juan Dela Cruz", "Signed by Maria Santos", "Signed by Juan Dela Cruz", "Marked as Finished"] },
    { id: "DOC-2024-004", title: "Procurement Review", type: "Contracts", fromOffice: "Legal", status: "Pending", assignees: ["ana.reyes@hr.gov"], trail: ["Received from Legal", "Assigned to Ana Reyes"] },
  ];

  // UTILS
  function getStatusBadge(status) {
    const colors = { Pending: "#fef3c7", Signed: "#d1fae5", Finished: "#dbeafe" };
    return `<span style="background:${colors[status]}; padding:2px 8px; border-radius:12px; font-weight:600; font-size:0.8rem;">${status}</span>`;
  }

  function openModal(id) {
    $(`#${id}`).addClass("active");
  }
  function closeModal(id) {
    $(`#${id}`).removeClass("active");
  }

  // NAVIGATION 
  $(".nav-link").click(function (e) {
    e.preventDefault();
    $(".nav-link").removeClass("active");
    $(this).addClass("active");
    const page = $(this).data("page");
    $(".page").removeClass("active");
    $(`#page-${page}`).addClass("active");
    if (page === "dashboard") renderDashboard();
  });

  // DASHBOARD 
  function renderDashboard() {
    const receivedCount = documents.length;
    const pending = documents.filter(d => d.status === "Pending").length;
    const signed = documents.filter(d => d.status === "Signed").length;
    const finished = documents.filter(d => d.status === "Finished").length;

    $("#dashboard-stats").html(`
      <div class="stat-card"><div class="stat-number">${receivedCount}</div><div class="stat-label">Total Documents</div></div>
      <div class="stat-card"><div class="stat-number">${pending}</div><div class="stat-label">Pending</div></div>
      <div class="stat-card"><div class="stat-number">${signed}</div><div class="stat-label">Signed</div></div>
      <div class="stat-card"><div class="stat-number">${finished}</div><div class="stat-label">Finished</div></div>
    `);

    const total = pending + signed + finished || 1;
    const pieData = [
      { label: "Pending", value: pending, color: "#f59e0b" },
      { label: "Signed", value: signed, color: "#059669" },
      { label: "Finished", value: finished, color: "#2563eb" },
    ];
    const gradient = pieData.map((d, i, arr) => {
      const startPercent = i === 0 ? 0 : arr.slice(0, i).reduce((sum, e) => sum + (e.value / total) * 100, 0);
      const endPercent = startPercent + (d.value / total) * 100;
      return `${d.color} ${startPercent}% ${endPercent}%`;
    }).join(", ");
    $("#dashboard-charts").html(`
      <div class="pie-preview-card">
        <div class="pie-chart" style="background: conic-gradient(${gradient})">
          <span>${total}</span>
        </div>
        <div class="pie-details">
          ${pieData.map(d => `<div class="pie-row"><span class="pie-swatch" style="background:${d.color}"></span><span>${d.label}</span><strong>${d.value}</strong></div>`).join("")}
        </div>
      </div>
    `);
  }

  // DATA TABLES 
  function buildDocRow(doc, actionsHtml) {
    return [
      doc.id,
      doc.title,
      doc.type,
      doc.fromOffice,
      getStatusBadge(doc.status),
      doc.assignees.join(", ") || "None",
      actionsHtml
    ];
  }

  // Generates action buttons based on document status
  function getActionsForDoc(doc) {
    let html = '';
    // Assign / reassign (always available except Finished)
    html += `<button class="btn-primary btn-sm btn-assign" data-id="${doc.id}">Assign</button> `;
    // Forward (available if not Finished)
    html += `<button class="btn-primary btn-sm btn-forward" data-id="${doc.id}">Forward</button> `;
    // Finish (only if status is Signed)
    if (doc.status === "Signed") {
      html += `<button class="btn-primary btn-sm btn-finish" data-id="${doc.id}">Finish</button> `;
    }
    // Trail
    html += `<button class="btn-primary btn-sm btn-trail" data-id="${doc.id}">Trail</button>`;
    return html;
  }

  function initDocTable(tableId, filterFn) {
    const filteredDocs = documents.filter(filterFn);
    const data = filteredDocs.map(doc => buildDocRow(doc, getActionsForDoc(doc)));
    if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
      $(`#${tableId}`).DataTable().clear().rows.add(data).draw();
    } else {
      $(`#${tableId}`).DataTable({
        data: data,
        columns: [
          { title: "ID" },
          { title: "Title" },
          { title: "Type" },
          { title: "From Office" },
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
    initDocTable("table-receive", d => d.status !== "Finished"); // still active
    initDocTable("table-pending", d => d.status === "Pending");
    initDocTable("table-release", d => true); // show all for forwarding
  }

  // ASSIGN MODAL 
  let selectedDocId = null;
  let selectedMembers = [];

  function renderSelectedMembers() {
    const list = $("#assign-selected-list");
    list.empty();
    selectedMembers.forEach(email => {
      list.append(`<li>${email} <button class="remove-member" data-email="${email}"><i class="fas fa-times"></i></button></li>`);
    });
  }

  $(document).on("click", ".btn-assign", function () {
    selectedDocId = $(this).data("id");
    const doc = documents.find(d => d.id === selectedDocId);
    $("#assign-doc-title").text(doc.title);
    $("#assign-member-search").val("");
    selectedMembers = [...doc.assignees];
    renderSelectedMembers();
    openModal("modal-assign");
  });

  $(document).on("click", ".remove-member", function () {
    const email = $(this).data("email");
    selectedMembers = selectedMembers.filter(e => e !== email);
    renderSelectedMembers();
  });

  // Add "Clear All" button inside the modal body 
  if ($("#clear-all-assignees").length === 0) {
    $("#assign-selected-list").after('<button id="clear-all-assignees" class="btn-primary btn-sm" style="margin-top:8px;">Clear All</button>');
  }
  $(document).on("click", "#clear-all-assignees", function () {
    selectedMembers = [];
    renderSelectedMembers();
  });

  // Autocomplete
  const memberEmails = members.map(m => m.email);
  $("#assign-member-search").on("input", function () {
    const query = $(this).val().toLowerCase();
    const filtered = memberEmails.filter(e => e.includes(query) && !selectedMembers.includes(e));
    const dropdown = $("#assign-member-dropdown");
    dropdown.empty();
    if (filtered.length && query) {
      filtered.forEach(email => {
        dropdown.append(`<div class="autocomplete-option">${email}</div>`);
      });
      dropdown.addClass("active");
    } else {
      dropdown.removeClass("active");
    }
  });

  $(document).on("click", ".autocomplete-option", function () {
    const email = $(this).text();
    if (!selectedMembers.includes(email)) {
      selectedMembers.push(email);
      renderSelectedMembers();
    }
    $("#assign-member-search").val("");
    $("#assign-member-dropdown").removeClass("active");
  });

  $("#btn-confirm-assign").click(function () {
    if (!selectedDocId) return;
    const doc = documents.find(d => d.id === selectedDocId);
    const oldAssignees = doc.assignees.slice();
    doc.assignees = [...selectedMembers];
    // Update status if assignment changed and doc isn't finished
    if (doc.status !== "Finished") {
      if (doc.assignees.length === 0) {
        // Cancel assignment: revert to Pending?
        doc.status = "Pending";
        doc.trail.push("All assignees removed (cancelled)");
      } else if (oldAssignees.length === 0 || JSON.stringify(oldAssignees) !== JSON.stringify(doc.assignees)) {
        doc.trail.push(`Assignees updated to ${doc.assignees.join(", ")}`);
      }
    }
    closeModal("modal-assign");
    refreshAllTables();
    renderDashboard();
  });

  // FORWARD MODAL 
  $(document).on("click", ".btn-forward", function () {
    selectedDocId = $(this).data("id");
    const doc = documents.find(d => d.id === selectedDocId);
    $("#forward-doc-title").text(doc.title);
    const container = $("#forward-offices-list");
    container.empty();
    offices.forEach(off => {
      container.append(`<label><input type="checkbox" value="${off}"> ${off}</label>`);
    });
    openModal("modal-forward");
  });

  $("#btn-confirm-forward").click(function () {
    const selectedOffices = $("#forward-offices-list input:checked").map((i, el) => el.value).get();
    if (selectedOffices.length === 0) return alert("Select at least one office.");
    const doc = documents.find(d => d.id === selectedDocId);
    doc.trail.push(`Forwarded to ${selectedOffices.join(", ")}`);
    alert(`Document forwarded to ${selectedOffices.join(", ")}`);
    closeModal("modal-forward");
    refreshAllTables();
  });

  // FINISH DOCUMENT 
  $(document).on("click", ".btn-finish", function () {
    const docId = $(this).data("id");
    const doc = documents.find(d => d.id === docId);
    if (doc.status !== "Signed") {
      alert("Only signed documents can be marked finished.");
      return;
    }
    if (confirm(`Mark "${doc.title}" as Finished?`)) {
      doc.status = "Finished";
      doc.trail.push("Marked as Finished by Secretary");
      refreshAllTables();
      renderDashboard();
    }
  });

  // PAPER TRAIL MODAL 
  $(document).on("click", ".btn-trail", function () {
    const doc = documents.find(d => d.id === $(this).data("id"));
    const list = $("#trail-list");
    list.empty();
    doc.trail.forEach(entry => {
      list.append(`<li>${entry}</li>`);
    });
    openModal("modal-trail");
  });

  // DOCUMENT TYPES 
  function refreshTypesTable() {
    const data = myOfficeTypes.map((type, idx) => [type, `<button class="btn-primary btn-sm btn-edit-type" data-idx="${idx}">Edit</button> <button class="btn-primary btn-sm btn-delete-type" data-idx="${idx}">Delete</button>`]);
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
    const idx = $(this).data("idx");
    $("#type-modal-title").text("Edit Document Type");
    $("#type-name").val(myOfficeTypes[idx]);
    $("#modal-type").data("editing", idx);
    openModal("modal-type");
  });

  $(document).on("click", ".btn-delete-type", function () {
    const idx = $(this).data("idx");
    if (confirm(`Delete type "${myOfficeTypes[idx]}"?`)) {
      myOfficeTypes.splice(idx, 1);
      refreshTypesTable();
    }
  });

  $("#btn-save-type").click(() => {
    const name = $("#type-name").val().trim();
    if (!name) return alert("Type name is required.");
    const editing = $("#modal-type").data("editing");
    if (editing !== null && editing !== undefined) {
      myOfficeTypes[editing] = name;
    } else {
      myOfficeTypes.push(name);
    }
    closeModal("modal-type");
    refreshTypesTable();
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
    const icon = $(".toggle-theme i");
    icon.toggleClass("fa-moon fa-sun");
  });

  $(".logout-btn").click(() => {
    if (confirm("Are you sure you want to logout?")) {
      alert("Logged out successfully.");
      window.location.href = "login.html";
    }
  });

  // INIT
  renderDashboard();
  refreshAllTables();
  refreshTypesTable();
  $(".nav-link[data-page='dashboard']").addClass("active");
  $("#page-dashboard").addClass("active");
});
