"use strict";

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

$(document).ready(function () {

    /*
    |--------------------------------------------------------------------------
    | THEME TOGGLE & INITIALIZATION
    |--------------------------------------------------------------------------
    */

    function updateThemeIcon(isDark) {
        const icon = $("#themeToggle i");
        if (icon.length) {
            icon.toggleClass("fa-moon", !isDark);
            icon.toggleClass("fa-sun", isDark);
        }
    }

    const savedTheme = localStorage.getItem("docuflow-theme") || localStorage.getItem("theme");
    const isDarkTheme = savedTheme === "dark";

    if (isDarkTheme) {
        document.documentElement.classList.add("dark-mode");
        document.body.classList.add("dark-mode");
        updateThemeIcon(true);
    } else {
        document.documentElement.classList.remove("dark-mode");
        document.body.classList.remove("dark-mode");
        updateThemeIcon(false);
    }

    $("#themeToggle").on("click", function () {
        const isDark = document.body.classList.toggle("dark-mode");
        document.documentElement.classList.toggle("dark-mode", isDark);

        localStorage.setItem("docuflow-theme", isDark ? "dark" : "light");
        localStorage.setItem("theme", isDark ? "dark" : "light");

        updateThemeIcon(isDark);
    });

    /*
    |--------------------------------------------------------------------------
    | MODAL HELPERS & PROFILE LOADER
    |--------------------------------------------------------------------------
    */

    window.openModal = function (modalId) {
        $("#" + modalId).addClass("active");
        if (modalId === "profileModal") {
            loadProfile();
        }
    };

    window.closeModal = function (modalId) {
        $("#" + modalId).removeClass("active");
    };

    async function loadProfile() {
        try {
            const res = await fetch("../controllers/SecretaryDashboardController.php?action=profile");
            if (!res.ok) return;
            const profile = await res.json();
            if (!profile) return;

            $("#profileName").text(profile.full_name || "N/A");
            $("#profileEmail").text(profile.email || "N/A");
            $("#profileOffice").text(profile.office_name || "Unassigned");
            $("#profileRole").text(profile.role_name || "Secretary");
        } catch (err) {
            console.error("Load profile error:", err);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | TAB NAVIGATION & PERSISTENCE
    |--------------------------------------------------------------------------
    */

    $(document).on("click", ".header-nav-item, .nav-link", function (e) {
        e.preventDefault();

        $(".header-nav-item, .nav-link").removeClass("active");
        $(".page").removeClass("active");

        const target = $(this).data("target");
        if (!target) return;

        $(`.header-nav-item[data-target="${target}"], .nav-link[data-target="${target}"]`).addClass("active");
        $("#page-" + target).addClass("active");

        localStorage.setItem("secretaryActiveTab", target);
    });

    const activeTab = localStorage.getItem("secretaryActiveTab");

    if (activeTab && $("#page-" + activeTab).length) {
        $(".header-nav-item, .nav-link").removeClass("active");
        $(".page").removeClass("active");
        $(`.header-nav-item[data-target="${activeTab}"], .nav-link[data-target="${activeTab}"]`).addClass("active");
        $("#page-" + activeTab).addClass("active");
    }

    /*
    |--------------------------------------------------------------------------
    | DATATABLE INITIALIZATIONS
    |--------------------------------------------------------------------------
    */

    const dataTableOptions = {
        responsive: true,
        order: [[3, "desc"]]
    };

    if ($("#table-all-documents").length) $("#table-all-documents").DataTable(dataTableOptions);
    if ($("#table-receive").length) $("#table-receive").DataTable({ responsive: true });
    if ($("#table-pending").length) $("#table-pending").DataTable({ responsive: true });
    if ($("#table-release").length) $("#table-release").DataTable({ responsive: true });
    if ($("#table-types").length) $("#table-types").DataTable({ responsive: true });

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT VIEW HANDLER
    |--------------------------------------------------------------------------
    */

    $(document).on("click", ".btn-view", function () {
        const file = $(this).data("file");
        if (file) {
            let clean = String(file).trim().replace(/^\/?CCDEVAP-MP1\//i, "").replace(/^\/+/, "");
            if (clean.startsWith("pdfs/") || clean.startsWith("uploads/")) {
                clean = "../" + clean;
            }
            window.open(clean, "_blank");
        }
    });

    /*
    |--------------------------------------------------------------------------
    | MODALS (ASSIGN, FORWARD, DOCUMENT TYPES)
    |--------------------------------------------------------------------------
    */

    $(document).on("click", ".btn-assign", function () {
        const docId = $(this).data("id");
        const title = $(this).data("title");

        $("#assign-doc-id").val(docId);
        $("#assign-doc-title").text(title);
        openModal("modal-assign");
    });

    $(document).on("click", ".btn-forward", function () {
        const docId = $(this).data("id");
        const title = $(this).data("title");

        $("#forward-doc-id").val(docId);
        $("#forward-doc-title").text(title);
        openModal("modal-forward");
    });

    $(document).on("click", "#btn-add-type", function () {
        $("#type-action").val("add");
        $("#type-id").val("");
        $("#type-name").val("");
        $("#type-modal-title").text("Add Document Type");
        openModal("modal-type");
    });

    $(document).on("click", ".btn-edit-type", function () {
        const id = $(this).data("id");
        const name = $(this).data("name");

        $("#type-action").val("edit");
        $("#type-id").val(id);
        $("#type-name").val(name);
        $("#type-modal-title").text("Edit Document Type");
        openModal("modal-type");
    });

    /*
    |--------------------------------------------------------------------------
    | PAPER TRAIL HANDLER (AJAX)
    |--------------------------------------------------------------------------
    */

    $(document).on("click", ".btn-trail", function () {
        const docId = $(this).data("id");

        openModal("modal-trail");
        $("#trail-list").html('<li style="text-align:center;">Loading trail...</li>');

        fetch(`../controllers/SecretaryDashboardController.php?action=trail&document_id=${docId}`)
            .then(res => res.json())
            .then(data => {
                const ul = $("#trail-list");
                ul.empty();

                if (data.success && data.trail && data.trail.length > 0) {
                    data.trail.forEach(t => {
                        const li = $("<li></li>");
                        let html = `<strong>${escapeHtml(t.action)}</strong> - ${escapeHtml(t.remarks || "")}<br>`;
                        html += `<small>${escapeHtml(t.from_office || "System")} &rarr; ${escapeHtml(t.to_office || "N/A")}</small><br>`;
                        html += `<small class="timestamp">${escapeHtml(t.action_date)}</small>`;

                        if (t.action_by_name) {
                            html += `<br><small>by ${escapeHtml(t.action_by_name)}</small>`;
                        }

                        li.html(html);
                        ul.append(li);
                    });
                } else {
                    ul.html("<li>No trail data found.</li>");
                }
            })
            .catch(err => {
                console.error(err);
                $("#trail-list").html('<li style="color:red;">Error loading trail data.</li>');
            });
    });

    /*
    |--------------------------------------------------------------------------
    | MODAL CLOSE HANDLERS
    |--------------------------------------------------------------------------
    */

    $(document).on("click", ".modal-close, [data-close]", function () {
        $(this).closest(".modal-overlay").removeClass("active");
    });

    $(document).on("click", ".modal-overlay", function (e) {
        if (e.target === this) {
            $(this).removeClass("active");
        }
    });

});
