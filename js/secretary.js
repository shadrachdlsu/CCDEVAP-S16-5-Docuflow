$(document).ready(function() {
    // Theme toggle
    $(".toggle-theme").click(() => {
        $("body").toggleClass("dark-mode");
        $(".toggle-theme i").toggleClass("fa-moon fa-sun");
    });

    // Tab Navigation
    $('.nav-link').on('click', function(e) {
        e.preventDefault();
        
        // Remove active class from all links and pages
        $('.nav-link').removeClass('active');
        $('.page').removeClass('active');
        
        // Add active class to clicked link
        $(this).addClass('active');
        
        // Show corresponding page
        const target = $(this).data('target');
        $('#page-' + target).addClass('active');
        
        // Store active tab in localStorage
        localStorage.setItem('secretaryActiveTab', target);
    });

    // Restore active tab on load
    const activeTab = localStorage.getItem('secretaryActiveTab');
    if (activeTab && $('#page-' + activeTab).length) {
        $('.nav-link').removeClass('active');
        $('.page').removeClass('active');
        $(`.nav-link[data-target="${activeTab}"]`).addClass('active');
        $('#page-' + activeTab).addClass('active');
    }

    // Initialize DataTables
    const dataTableOptions = { responsive: true, order: [[3, 'desc']] };
    $('#table-all-documents').DataTable(dataTableOptions);
    $('#table-receive').DataTable({ responsive: true });
    $('#table-pending').DataTable({ responsive: true });
    $('#table-release').DataTable({ responsive: true });
    $('#table-types').DataTable({ responsive: true });

    // Document View Handler
    $('.btn-view').on('click', function() {
        const file = $(this).data('file');
        if(file) {
            window.open(file, '_blank');
        }
    });

    // Assign Modal
    $('.btn-assign').on('click', function() {
        const docId = $(this).data('id');
        const title = $(this).data('title');
        $('#assign-doc-id').val(docId);
        $('#assign-doc-title').text(title);
        $('#modal-assign').addClass('active');
    });

    // Forward Modal
    $('.btn-forward').on('click', function() {
        const docId = $(this).data('id');
        const title = $(this).data('title');
        $('#forward-doc-id').val(docId);
        $('#forward-doc-title').text(title);
        $('#modal-forward').addClass('active');
    });

    // Document Type Modal
    $('#btn-add-type').on('click', function() {
        $('#type-action').val('add');
        $('#type-id').val('');
        $('#type-name').val('');
        $('#type-modal-title').text('Add Document Type');
        $('#modal-type').addClass('active');
    });

    $('.btn-edit-type').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        $('#type-action').val('edit');
        $('#type-id').val(id);
        $('#type-name').val(name);
        $('#type-modal-title').text('Edit Document Type');
        $('#modal-type').addClass('active');
    });

    // Paper Trail Handler
    $('.btn-trail').on('click', function() {
        const docId = $(this).data('id');
        $('#modal-trail').addClass('active');
        $('#trail-list').html('<li style="text-align:center;">Loading trail...</li>');
        
        fetch(`../controllers/SecretaryDashboardController.php?action=trail&document_id=${docId}`)
            .then(res => res.json())
            .then(data => {
                const ul = $('#trail-list');
                ul.empty();
                if(data.success && data.trail.length > 0) {
                    data.trail.forEach(t => {
                        const li = $('<li></li>');
                        let html = `<strong>${t.action}</strong> - ${t.remarks || ''}<br>`;
                        html += `<small>${t.from_office || 'System'} &rarr; ${t.to_office || 'N/A'}</small><br>`;
                        html += `<small class="timestamp">${t.action_date}</small>`;
                        if (t.action_by_name) html += `<br><small>by ${t.action_by_name}</small>`;
                        li.html(html);
                        ul.append(li);
                    });
                } else {
                    ul.html('<li>No trail data found.</li>');
                }
            })
            .catch(err => {
                console.error(err);
                $('#trail-list').html('<li style="color:red;">Error loading trail data.</li>');
            });
    });

    // Close Modals
    $('.modal-close').on('click', function() {
        $(this).closest('.modal-overlay').removeClass('active');
    });
    
    // Global Modal Close
    $(document).on("click", ".modal-overlay", function (e) {
      if (e.target === this) {
        $(this).removeClass("active");
      }
    });
});
