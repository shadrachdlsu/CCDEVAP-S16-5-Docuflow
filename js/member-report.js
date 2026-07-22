/* ==========================================
   MEMBER REPORT
   CCDEVAP-MP1
========================================== */

"use strict";

/* ==========================================
   API ENDPOINTS
========================================== */

const API = {
    reports:
        "../controllers/MemberReportController.php?action=reports",

    statistics:
        "../controllers/MemberReportController.php?action=statistics",

    types:
        "../controllers/MemberReportController.php?action=types"
};

/* ==========================================
   GLOBAL VARIABLES
========================================== */

let reportTable = null;

let reportData = [];

let chart = null;

let reportChart = null;

/* ==========================================
   INITIALIZE REPORT CHART
========================================== */

function initializeReportChart() {

    const canvas = document.getElementById("reportChart");

    if (!canvas) return;

    reportChart = new Chart(canvas, {
        type: "doughnut",
        data: {
            labels: ["Pending", "Signed", "Finished"],
            datasets: [{
                data: [
                    reportChartData.pending,
                    reportChartData.signed,
                    reportChartData.finished
                ],
                backgroundColor: [
                    "#f59e0b",
                    "#22c55e",
                    "#5c4ae4"
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: "bottom",
                    labels: {
                        color: document.body.classList.contains("dark-mode")
                            ? "#ffffff"
                            : "#111827"
                    }
                }
            }
        }
    });

}

/* ==========================================
   PAGE LOAD
========================================== */

document.addEventListener("DOMContentLoaded", () => {

    if (localStorage.getItem("theme") === "dark") {
        document.body.classList.add("dark-mode");
    }

    // Create the chart
    initializeReportChart();

    const toggle = document.getElementById("themeToggle");

    if (toggle) {

        toggle.addEventListener("click", () => {

            document.body.classList.toggle("dark-mode");

            if (document.body.classList.contains("dark-mode")) {
                localStorage.setItem("theme", "dark");
            } else {
                localStorage.setItem("theme", "light");
            }

            // Update chart legend color
            if (reportChart) {

                reportChart.options.plugins.legend.labels.color =
                    document.body.classList.contains("dark-mode")
                        ? "#ffffff"
                        : "#111827";

                reportChart.update();
            }

        });

    }

});

/* ==========================================
   INITIALIZE
========================================== */

async function initializePage()
{

    initializeDataTable();

    initializeTheme();

    initializeEvents();

    await loadStatistics();

    await loadDocumentTypes();

    await loadReports();

}

/* ==========================================
   DATATABLE
========================================== */

function initializeDataTable()
{

    reportTable = $("#reportTable").DataTable({

        responsive: true,

        pageLength: 10,

        ordering: true,

        searching: true,

        autoWidth: false,

        lengthMenu: [

            [5,10,25,50],

            [5,10,25,50]

        ],

        language: {

            search: "Search:",

            lengthMenu: "Show _MENU_ entries",

            info: "Showing _START_ to _END_ of _TOTAL_ documents"

        }

    });

}

/* ==========================================
   LOAD STATISTICS
========================================== */

async function loadStatistics()
{

    try
    {

        const response =
            await fetch(API.statistics);

        const data =
            await response.json();

        document.getElementById("totalDocuments").textContent =
            data.total;

        document.getElementById("pendingDocuments").textContent =
            data.pending;

        document.getElementById("signedDocuments").textContent =
            data.signed;

        document.getElementById("finishedDocuments").textContent =
            data.finished;

        document.getElementById("summaryTotal").textContent =
            data.total;

        document.getElementById("summaryPending").textContent =
            data.pending;

        document.getElementById("summarySigned").textContent =
            data.signed;

        document.getElementById("summaryFinished").textContent =
            data.finished;

        loadChart(data);

    }

    catch(error)
    {

        console.error(error);

    }

}

/* ==========================================
   LOAD DOCUMENT TYPES
========================================== */

async function loadDocumentTypes()
{

    try
    {

        const response =
            await fetch(API.types);

        const types =
            await response.json();

        const select =
            document.getElementById("typeFilter");

        types.forEach(function(type)
        {

            select.innerHTML +=

                `<option value="${type.type_name}">

                    ${type.type_name}

                </option>`;

        });

    }

    catch(error)
    {

        console.error(error);

    }

}

/* ==========================================
   LOAD REPORTS
========================================== */

async function loadReports()
{

    try
    {

        const response =
            await fetch(API.reports);

        reportData =
            await response.json();

        renderReportTable(reportData);

    }

    catch(error)
    {

        console.error(error);

    }

}

/* ==========================================
   RENDER REPORT TABLE
========================================== */

function renderReportTable(data)
{

    reportTable.clear();

    data.forEach(function(document)
    {

        reportTable.row.add([

            document.tracking_code,

            document.title,

            document.type_name,

            document.office_name,

            formatDate(document.created_at),

            createStatusBadge(document.status),

            createActionButtons(document)

        ]);

    });

    reportTable.draw(false);

}

/* ==========================================
   STATUS BADGES
========================================== */

function createStatusBadge(status)
{

    if(status === "Pending")
    {

        return '<span class="status-badge status-pending">Pending</span>';

    }

    if(status === "Signed")
    {

        return '<span class="status-badge status-signed">Signed</span>';

    }

    if(status === "Finished")
    {

        return '<span class="status-badge status-finished">Finished</span>';

    }

    return '<span class="status-badge status-rejected">Rejected</span>';

}

/* ==========================================
   ACTION BUTTONS
========================================== */

function createActionButtons(document)
{

    return `

        <div class="action-buttons">

            <button
                class="btn btn-preview btn-sm"
                onclick="previewDocument(${document.document_id})">

                <i class="fas fa-eye"></i>

            </button>

            <a
                href="${document.file_path}"
                download
                class="btn btn-download btn-sm">

                <i class="fas fa-download"></i>

            </a>

        </div>

    `;

}

/* ==========================================
   FILTERS
========================================== */

document
.getElementById("statusFilter")
.addEventListener("change", filterReports);

document
.getElementById("typeFilter")
.addEventListener("change", filterReports);

document
.getElementById("dateFilter")
.addEventListener("change", filterReports);

function filterReports()
{

    const status =
        document.getElementById("statusFilter").value;

    const type =
        document.getElementById("typeFilter").value;

    const date =
        document.getElementById("dateFilter").value;

    const filtered = reportData.filter(function(document)
    {

        let valid = true;

        if(status !== "")
        {

            valid =
                valid &&
                document.status === status;

        }

        if(type !== "")
        {

            valid =
                valid &&
                document.type_name === type;

        }

        if(date !== "")
        {

            valid =
                valid &&
                document.created_at.startsWith(date);

        }

        return valid;

    });

    renderReportTable(filtered);

}

/* ==========================================
   PREVIEW DOCUMENT
========================================== */

function previewDocument(documentId)
{

    const documentData = reportData.find(function(document)
    {

        return document.document_id == documentId;

    });

    if(documentData == null)
    {

        return;

    }

    document.getElementById("previewFrame").src =
        documentData.file_path;

    document.getElementById("downloadDocument").href =
        documentData.file_path;

    const modal =
        new bootstrap.Modal(
            document.getElementById("previewModal")
        );

    modal.show();

}

/* ==========================================
   REPORT CHART
========================================== */

function loadChart(data)
{

    const canvas =
        document.getElementById("reportChart");

    if(canvas == null)
    {

        return;

    }

    if(chart != null)
    {

        chart.destroy();

    }

    chart = new Chart(canvas,
    {

        type: "doughnut",

        data:
        {

            labels:
            [

                "Pending",

                "Signed",

                "Finished"

            ],

            datasets:
            [

                {

                    data:
                    [

                        data.pending,

                        data.signed,

                        data.finished

                    ],

                    backgroundColor:
                    [

                        "#ffc107",

                        "#198754",

                        "#0d6efd"

                    ]

                }

            ]

        },

        options:
        {

            responsive: true,

            maintainAspectRatio: false

        }

    });

}

/* ==========================================
   EXPORT CSV
========================================== */

document
.getElementById("downloadCSV")
.addEventListener("click", exportCSV);

function exportCSV()
{

    let csv =
        "Tracking Code,Title,Type,Office,Date,Status\n";

    reportData.forEach(function(document)
    {

        csv +=

            `"${document.tracking_code}",` +

            `"${document.title}",` +

            `"${document.type_name}",` +

            `"${document.office_name}",` +

            `"${formatDate(document.created_at)}",` +

            `"${document.status}"\n`;

    });

    const blob =
        new Blob([csv],
        {

            type:"text/csv"

        });

    const url =
        window.URL.createObjectURL(blob);

    const link =
        document.createElement("a");

    link.href = url;

    link.download = "Member_Report.csv";

    link.click();

}

/* ==========================================
   EXPORT PDF
========================================== */

document
.getElementById("downloadPDF")
.addEventListener("click", function()
{

    window.print();

});

/* ==========================================
   REFRESH
========================================== */

document
.getElementById("refreshReport")
.addEventListener("click", async function()
{

    await loadStatistics();

    await loadReports();

});

/* ==========================================
   THEME
========================================== */

function initializeTheme()
{

    const toggle =
        document.getElementById("themeToggle");

    if(toggle)
    {

        toggle.addEventListener("click", function()
        {

            document.body.classList.toggle("dark-mode");

        });

    }

}

/* ==========================================
   EVENTS
========================================== */

function initializeEvents()
{

    const logout =
        document.querySelector(".logout-btn");

    if(logout)
    {

        logout.addEventListener("click", function(e)
        {

            window.location.href = "../controllers/LogoutController.php";

        });

    }

}

/* ==========================================
   FORMAT DATE
========================================== */

function formatDate(date)
{

    const formatted =
        new Date(date);

    return formatted.toLocaleDateString(

        "en-PH",

        {

            year: "numeric",

            month: "short",

            day: "numeric"

        }

    );

}

