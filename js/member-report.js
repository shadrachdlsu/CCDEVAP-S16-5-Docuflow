"use strict";

const API = {
    reports: "../controllers/MemberReportController.php?action=reports",
    statistics: "../controllers/MemberReportController.php?action=statistics",
    types: "../controllers/MemberReportController.php?action=types",
    officeTrends: "../controllers/MemberReportController.php?action=officeTrends",
    routeStatus: "../controllers/MemberReportController.php?action=routeStatus",
    profile: "../controllers/MemberDashboardController.php?action=profile"
};

let reportTable = null;
let reportData = [];
let officeLineChart = null;
let routeStatusPieChart = null;

const STATUS_ORDER = [
    "Waiting", "Received", "For Signature", "Signed",
    "Rejected", "Released", "Skipped", "Completed"
];

const STATUS_COLORS = {
    Waiting: "#d97706",
    Received: "#2563eb",
    "For Signature": "#8b5cf6",
    Signed: "#16a34a",
    Rejected: "#dc2626",
    Released: "#06b6d4",
    Skipped: "#9ca3af",
    Completed: "#4f46e5"
};

/* ==========================================
   MODAL CONTROLLERS
========================================== */

window.openModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add("active");
    }
};

window.closeModal = function (modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove("active");
    }
};

document.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal-overlay")) {
        e.target.classList.remove("active");
    }
});

document.addEventListener("DOMContentLoaded", initializePage);

async function initializePage() {
    applyStoredTheme();
    initializeDataTable();
    initializeEvents();
    loadProfile();

    await Promise.all([
        loadStatistics(),
        loadDocumentTypes(),
        loadReports(),
        loadOfficeTrends(),
        loadRouteStatusDistribution()
    ]);
}

function initializeDataTable() {
    reportTable = $("#reportTable").DataTable({
        responsive: true,
        pageLength: 10,
        ordering: true,
        searching: true,
        autoWidth: false,
        lengthMenu: [[5, 10, 25, 50], [5, 10, 25, 50]],
        language: {
            search: "Search:",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ route records"
        }
    });
}

async function fetchJson(url) {
    const response = await fetch(url, { headers: { Accept: "application/json" } });
    const text = await response.text();

    if (!response.ok) {
        throw new Error(`Request failed (${response.status}): ${text}`);
    }

    try {
        return JSON.parse(text);
    } catch {
        throw new Error(`Invalid JSON response: ${text.substring(0, 200)}`);
    }
}

async function loadStatistics() {
    try {
        const data = await fetchJson(API.statistics);
        setText("totalRouteSteps", data.total_route_steps);
        setText("rejectedRoutes", data.rejected);
        setText("totalDocuments", data.total_documents);
        setText("completedRoutes", data.completed);
        setText("summaryTotal", data.total_route_steps);
        setText("summaryPending", data.pending);
        setText("summarySigned", data.signed);
        setText("summaryRejected", data.rejected);
    } catch (error) {
        console.error("Statistics error:", error);
    }
}

async function loadDocumentTypes() {
    try {
        const types = await fetchJson(API.types);
        const select = document.getElementById("typeFilter");
        types.forEach(type => {
            const option = document.createElement("option");
            option.value = type.type_name;
            option.textContent = type.type_name;
            select.appendChild(option);
        });
    } catch (error) {
        console.error("Document types error:", error);
    }
}

async function loadReports() {
    try {
        reportData = await fetchJson(API.reports);
        renderReportTable(reportData);
    } catch (error) {
        console.error("Reports error:", error);
    }
}

function renderReportTable(data) {
    reportTable.clear();

    data.forEach(item => {
        reportTable.row.add([
            escapeHtml(item.tracking_code),
            escapeHtml(item.title),
            escapeHtml(item.type_name),
            escapeHtml(item.office_name),
            formatDate(item.created_at),
            createStatusBadge(item.computed_status || item.route_status),
            createActionButtons(item)
        ]);
    });

    reportTable.draw(false);
}

function createStatusBadge(status) {
    const css = String(status).toLowerCase().replaceAll(" ", "-");
    return `<span class="status-badge status-${css}">${escapeHtml(status)}</span>`;
}

function formatFilePath(filePath) {
    if (!filePath) return "#";
    let cleanPath = String(filePath).trim();
    cleanPath = cleanPath.replace(/^\/?CCDEVAP-MP1\//i, "");
    cleanPath = cleanPath.replace(/^\/+/, "");
    if (cleanPath.startsWith("pdfs/") || cleanPath.startsWith("uploads/")) {
        return "../" + cleanPath;
    }
    return cleanPath;
}

function createActionButtons(item) {
    const safePath = encodeURI(formatFilePath(item.file_path));
    return `
        <div class="action-buttons">
            <button class="btn-small previewBtn" type="button"
                onclick="previewDocument(${Number(item.document_id)})" title="Preview">
                Preview
            </button>
            <a href="${safePath}" download class="btn-small action-btn" style="text-decoration:none;" title="Download">
                Download
            </a>
        </div>`;
}

async function loadOfficeTrends() {
    try {
        const rows = await fetchJson(API.officeTrends);
        renderOfficeLineChart(rows);
    } catch (error) {
        console.error("Office trend error:", error);
    }
}

function renderOfficeLineChart(rows) {
    const canvas = document.getElementById("officeLineChart");
    if (!canvas) return;

    const offices = [...new Set(rows.map(row => row.office_name))];
    const colors = ["#2563eb", "#f97316", "#a855f7", "#06b6d4", "#eab308", "#64748b"];

    const datasets = offices.map((office, index) => {
        const normalized = office.toLowerCase();
        let color = colors[index % colors.length];

        if (normalized.includes("it") || normalized.includes("its")) color = "#dc2626";
        if (normalized.includes("hr") || normalized.includes("human resource")) color = "#16a34a";

        return {
            label: office,
            data: STATUS_ORDER.map(status => {
                const match = rows.find(row => row.office_name === office && row.route_status === status);
                return match ? Number(match.total) : 0;
            }),
            borderColor: color,
            backgroundColor: color,
            tension: 0.25,
            fill: false,
            pointRadius: 4
        };
    });

    if (officeLineChart) officeLineChart.destroy();

    officeLineChart = new Chart(canvas, {
        type: "line",
        data: { labels: STATUS_ORDER, datasets },
        options: commonChartOptions("Route Steps per Office")
    });
}

async function loadRouteStatusDistribution() {
    try {
        const rows = await fetchJson(API.routeStatus);
        renderRouteStatusPieChart(rows);
    } catch (error) {
        console.error("Route-status distribution error:", error);
    }
}

function renderRouteStatusPieChart(rows) {
    const canvas = document.getElementById("routeStatusPieChart");
    if (!canvas) return;

    const labels = rows.map(row => row.route_status);
    const values = rows.map(row => Number(row.total));
    const colors = labels.map(label => STATUS_COLORS[label] || "#64748b");

    if (routeStatusPieChart) routeStatusPieChart.destroy();

    routeStatusPieChart = new Chart(canvas, {
        type: "pie",
        data: {
            labels,
            datasets: [{ data: values, backgroundColor: colors, borderWidth: 1 }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: { display: true, text: "All Route Steps by Current Status" },
                legend: { position: "bottom" }
            }
        }
    });
}

function commonChartOptions(title) {
    return {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: "index", intersect: false },
        plugins: {
            title: { display: true, text: title },
            legend: { position: "bottom" }
        },
        scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
        }
    };
}

function filterReports() {
    const status = document.getElementById("statusFilter").value;
    const type = document.getElementById("typeFilter").value;
    const date = document.getElementById("dateFilter").value;

    const filtered = reportData.filter(item => {
        const itemStatus = item.computed_status || item.route_status;
        return (!status || itemStatus === status)
            && (!type || item.type_name === type)
            && (!date || String(item.created_at).startsWith(date));
    });

    renderReportTable(filtered);
}

function previewDocument(documentId) {
    const item = reportData.find(row => Number(row.document_id) === Number(documentId));
    if (!item || !item.file_path) return;
    const safePath = formatFilePath(item.file_path);
    const iframe = document.getElementById("previewFrame");
    const download = document.getElementById("downloadDocument");
    if (iframe) iframe.src = safePath;
    if (download) download.href = safePath;
    openModal("previewModal");
}

function exportCSV() {
    const rows = [["Tracking Code", "Title", "Type", "Office", "Date", "Route Status"]];
    reportData.forEach(item => rows.push([
        item.tracking_code,
        item.title,
        item.type_name,
        item.office_name,
        item.created_at,
        item.computed_status || item.route_status
    ]));

    const csv = rows.map(row => row.map(value => `"${String(value ?? "").replaceAll('"', '""')}"`).join(",")).join("\n");
    const blob = new Blob([csv], { type: "text/csv;charset=utf-8" });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    link.href = url;
    link.download = "member-route-report.csv";
    link.click();
    URL.revokeObjectURL(url);
}

function initializeEvents() {
    ["statusFilter", "typeFilter", "dateFilter"].forEach(id => {
        document.getElementById(id)?.addEventListener("change", filterReports);
    });

    document.getElementById("refreshReport")?.addEventListener("click", async () => {
        await Promise.all([loadStatistics(), loadReports(), loadOfficeTrends(), loadRouteStatusDistribution()]);
    });

    document.getElementById("downloadCSV")?.addEventListener("click", exportCSV);
    document.getElementById("downloadPDF")?.addEventListener("click", () => window.print());

    document.getElementById("themeToggle")?.addEventListener("click", () => {
        const isNowDark = document.body.classList.toggle("dark-mode");
        document.documentElement.classList.toggle("dark-mode", isNowDark);
        localStorage.setItem("docuflow-theme", isNowDark ? "dark" : "light");
        localStorage.setItem("theme", isNowDark ? "dark" : "light");

        const themeIcon = document.getElementById("themeToggle")?.querySelector("i");
        if (themeIcon) {
            themeIcon.classList.toggle("fa-moon", !isNowDark);
            themeIcon.classList.toggle("fa-sun", isNowDark);
        }

        updateChartsForTheme();
    });
}

function applyStoredTheme() {
    const themeToggle = document.getElementById("themeToggle");
    const themeIcon = themeToggle?.querySelector("i");
    const isDark = localStorage.getItem("docuflow-theme") === "dark" || localStorage.getItem("theme") === "dark";

    if (isDark) {
        document.documentElement.classList.add("dark-mode");
        document.body.classList.add("dark-mode");
        if (themeIcon) {
            themeIcon.classList.remove("fa-moon");
            themeIcon.classList.add("fa-sun");
        }
    } else {
        document.documentElement.classList.remove("dark-mode");
        document.body.classList.remove("dark-mode");
        if (themeIcon) {
            themeIcon.classList.remove("fa-sun");
            themeIcon.classList.add("fa-moon");
        }
    }
}

async function loadProfile() {
    try {
        const response = await fetch(API.profile);
        if (!response.ok) return;
        const profile = await response.json();
        if (!profile) return;

        const profileName = document.getElementById("profileName");
        const profileEmail = document.getElementById("profileEmail");
        const profileOffice = document.getElementById("profileOffice");
        const profileRole = document.getElementById("profileRole");

        if (profileName) profileName.textContent = profile.full_name || "N/A";
        if (profileEmail) profileEmail.textContent = profile.email || "N/A";
        if (profileOffice) profileOffice.textContent = profile.office_name || "Unassigned";
        if (profileRole) profileRole.textContent = profile.role_name || "Member";
    } catch (error) {
        console.error("Load profile error:", error);
    }
}

function updateChartsForTheme() {
    const color = document.body.classList.contains("dark-mode") ? "#f9fafb" : "#111827";
    [officeLineChart, routeStatusPieChart].forEach(instance => {
        if (!instance) return;
        if (instance.options.plugins?.legend?.labels) instance.options.plugins.legend.labels.color = color;
        if (instance.options.plugins?.title) instance.options.plugins.title.color = color;
        instance.update();
    });
}

function setText(id, value) {
    const element = document.getElementById(id);
    if (element) element.textContent = Number(value || 0);
}

function formatDate(date) {
    return new Date(date).toLocaleDateString("en-PH", {
        year: "numeric", month: "short", day: "numeric"
    });
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}
