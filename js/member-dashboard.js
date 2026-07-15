/* ==========================================
   MEMBER DASHBOARD
   CCDEVAP-MP1
========================================== */

"use strict";

/* ==========================================
   API ENDPOINTS
========================================== */

const API =
{
    documents:
        "../php/getMemberDocuments.php",

    paperTrail:
        "../php/getPaperTrail.php",

    requests:
        "../php/getRequests.php",

    statistics:
        "../php/getMemberStatistics.php",

    profile:
        "../php/getMemberProfile.php"
};

/* ==========================================
   GLOBAL VARIABLES
========================================== */

let documents = [];

let paperTrail = [];

let requests = [];

let currentDocument = null;

let documentTable = null;

let paperTrailTable = null;

let dashboardChart = null;

/* ==========================================
   PAGE LOAD
========================================== */

document.addEventListener(
    "DOMContentLoaded",
    initializeDashboard
);

/* ==========================================
   INITIALIZE DASHBOARD
========================================== */

async function initializeDashboard()
{
    initializeTheme();

    initializeTables();

    initializeButtons();

    initializeEvents();

    await loadStatistics();

    await loadDocuments();

    await loadPaperTrail();

    await loadProfile();

    loadChart();
}

/* ==========================================
   INITIALIZE DATATABLES
========================================== */

function initializeTables()
{
    documentTable =
        $("#documents-table").DataTable(
        {
            responsive: true,

            pageLength: 10,

            ordering: true,

            searching: true,

            autoWidth: false,

            lengthMenu:
            [
                [5,10,25,50],
                [5,10,25,50]
            ],

            language:
            {
                search: "Search:",

                lengthMenu:
                    "Show _MENU_ documents",

                info:
                    "Showing _START_ to _END_ of _TOTAL_ documents"
            }
        });

    paperTrailTable =
        $("#paperTrailTable").DataTable(
        {
            responsive: true,

            pageLength: 5,

            ordering: true,

            searching: true,

            info: false,

            autoWidth: false
        });
}

/* ==========================================
   INITIALIZE EVENTS
========================================== */

function initializeEvents()
{
    const requestForm =
        document.getElementById("requestForm");

    const confirmSign =
        document.getElementById("confirmSign");

    const confirmReject =
        document.getElementById("confirmReject");

    const uploadSigned =
        document.getElementById("uploadSigned");

    if(requestForm)
    {
        requestForm.addEventListener(
            "submit",
            submitRequest
        );
    }

    if(confirmSign)
    {
        confirmSign.addEventListener(
            "click",
            signDocument
        );
    }

    if(confirmReject)
    {
        confirmReject.addEventListener(
            "click",
            rejectDocument
        );
    }

    if(uploadSigned)
    {
        uploadSigned.addEventListener(
            "click",
            uploadSignedDocument
        );
    }
}

/* ==========================================
   LOAD DASHBOARD STATISTICS
========================================== */

async function loadStatistics()
{
    let response = null;

    let data = null;

    try
    {
        response =
            await fetch(API.statistics);

        if(!response.ok)
        {
            throw new Error(
                "Unable to load dashboard statistics."
            );
        }

        data =
            await response.json();

        document.getElementById("pending-count").textContent =
            data.pending;

        document.getElementById("signed-count").textContent =
            data.signed;

        document.getElementById("finished-count").textContent =
            data.finished;

        document.getElementById("request-count").textContent =
            data.requests;
    }

    catch(error)
    {
        console.error(error);

        alert(
            "Unable to load dashboard statistics."
        );
    }
}

/* ==========================================
   LOAD MEMBER DOCUMENTS
========================================== */

async function loadDocuments()
{
    let response = null;

    try
    {
        response =
            await fetch(API.documents);

        if(!response.ok)
        {
            throw new Error(
                "Unable to load documents."
            );
        }

        documents =
            await response.json();

        documentTable.clear();

        documents.forEach(function(document)
        {
            documentTable.row.add(
            [
                document.tracking_code,

                document.title,

                document.type_name,

                document.office_name,

                createStatusBadge(
                    document.status
                ),

                createActionButtons(
                    document
                )
            ]);
        });

        documentTable.draw(false);
    }

    catch(error)
    {
        console.error(error);

        alert(
            "Unable to load documents."
        );
    }
}

/* ==========================================
   STATUS BADGE
========================================== */

function createStatusBadge(status)
{
    let badge = "";

    switch(status)
    {
        case "Pending":

            badge =
                '<span class="badge bg-warning text-dark">Pending</span>';

            break;

        case "Signed":

            badge =
                '<span class="badge bg-success">Signed</span>';

            break;

        case "Finished":

            badge =
                '<span class="badge bg-primary">Finished</span>';

            break;

        case "Rejected":

            badge =
                '<span class="badge bg-danger">Rejected</span>';

            break;

        default:

            badge =
                '<span class="badge bg-secondary">Unknown</span>';
    }

    return badge;
}

/* ==========================================
   ACTION BUTTONS
========================================== */

function createActionButtons(document)
{
    let buttons = "";

    buttons +=
    `
    <div class="action-buttons">

        <button
            class="btn btn-outline-primary btn-sm"
            onclick="previewDocument(${document.document_id})"
            title="Preview">

            <i class="fas fa-eye"></i>

        </button>

        <a
            href="${document.file_path}"
            class="btn btn-outline-secondary btn-sm"
            download
            title="Download">

            <i class="fas fa-download"></i>

        </a>
    `;

    if(document.status === "Pending")
    {
        buttons +=
        `
        <button
            class="btn btn-success btn-sm"
            onclick="openSignModal(${document.document_id})"
            title="Sign">

            <i class="fas fa-signature"></i>

        </button>

        <button
            class="btn btn-danger btn-sm"
            onclick="openRejectModal(${document.document_id})"
            title="Reject">

            <i class="fas fa-times"></i>

        </button>

        <button
            class="btn btn-warning btn-sm"
            onclick="openUploadModal(${document.document_id})"
            title="Upload Signed">

            <i class="fas fa-upload"></i>

        </button>
        `;
    }

    buttons +=
    `
    </div>
    `;

    return buttons;
}

/* ==========================================
   FIND DOCUMENT
========================================== */

function findDocument(documentId)
{
    return documents.find(function(document)
    {
        return document.document_id == documentId;
    });
}

/* ==========================================
   PREVIEW DOCUMENT
========================================== */

function previewDocument(documentId)
{
    currentDocument =
        findDocument(documentId);

    if(currentDocument == null)
    {
        alert("Document not found.");

        return;
    }

    document.getElementById("previewFrame").src =
        currentDocument.file_path;

    document.getElementById("downloadPreview").href =
        currentDocument.file_path;

    const modal =
        new bootstrap.Modal(
            document.getElementById("previewModal")
        );

    modal.show();
}

/* ==========================================
   OPEN SIGN MODAL
========================================== */

function openSignModal(documentId)
{
    currentDocument =
        findDocument(documentId);

    if(currentDocument == null)
    {
        alert("Document not found.");

        return;
    }

    document.getElementById("signRemarks").value = "";

    const modal =
        new bootstrap.Modal(
            document.getElementById("signModal")
        );

    modal.show();
}

/* ==========================================
   OPEN REJECT MODAL
========================================== */

function openRejectModal(documentId)
{
    currentDocument =
        findDocument(documentId);

    if(currentDocument == null)
    {
        alert("Document not found.");

        return;
    }

    document.getElementById("rejectReason").value = "";

    const modal =
        new bootstrap.Modal(
            document.getElementById("rejectModal")
        );

    modal.show();
}

/* ==========================================
   OPEN UPLOAD MODAL
========================================== */

function openUploadModal(documentId)
{
    currentDocument =
        findDocument(documentId);

    if(currentDocument == null)
    {
        alert("Document not found.");

        return;
    }

    document.getElementById("signedFile").value = "";

    const modal =
        new bootstrap.Modal(
            document.getElementById("uploadModal")
        );

    modal.show();
}

/* ==========================================
   LOAD PAPER TRAIL
========================================== */

async function loadPaperTrail()
{
    let response = null;

    try
    {
        response =
            await fetch(API.paperTrail);

        if(!response.ok)
        {
            throw new Error(
                "Unable to load paper trail."
            );
        }

        paperTrail =
            await response.json();

        paperTrailTable.clear();

        paperTrail.forEach(function(log)
        {
            paperTrailTable.row.add(
            [
                formatDate(log.created_at),

                log.action_taken,

                log.action_by,

                createStatusBadge(log.status)
            ]);
        });

        paperTrailTable.draw(false);
    }

    catch(error)
    {
        console.error(error);

        alert("Unable to load paper trail.");
    }
}

/* ==========================================
   FORMAT DATE
========================================== */

function formatDate(date)
{
    let formattedDate =
        new Date(date);

    return formattedDate.toLocaleDateString(
        "en-PH",
        {
            year: "numeric",

            month: "short",

            day: "numeric"
        }
    );
}

/* ==========================================
   LOAD MEMBER PROFILE
========================================== */

async function loadProfile()
{
    let response = null;

    let profile = null;

    try
    {
        response =
            await fetch(API.profile);

        if(!response.ok)
        {
            throw new Error(
                "Unable to load profile."
            );
        }

        profile =
            await response.json();

        document.getElementById("profileName").textContent =
            profile.full_name;

        document.getElementById("profileEmail").textContent =
            profile.email;

        document.getElementById("profileOffice").textContent =
            profile.office_name;

        const memberEmail =
            document.getElementById("memberEmail");

        if(memberEmail)
        {
            memberEmail.textContent =
                profile.email;
        }
    }

    catch(error)
    {
        console.error(error);

        alert("Unable to load profile.");
    }
}

/* ==========================================
   SUBMIT REQUEST
========================================== */

async function submitRequest(event)
{
    let response = null;

    let requestData = null;

    event.preventDefault();

    requestData =
    {
        title:
            document.getElementById("requestTitle").value,

        type_id:
            document.getElementById("requestType").value,

        description:
            document.getElementById("requestDescription").value,

        secretary_email:
            document.getElementById("secretaryEmail").value
    };

    try
    {
        response =
            await fetch(API.requests,
            {
                method: "POST",

                headers:
                {
                    "Content-Type":
                        "application/json"
                },

                body:
                    JSON.stringify(requestData)
            });

        if(!response.ok)
        {
            throw new Error(
                "Unable to submit request."
            );
        }

        alert("Request submitted successfully.");

        bootstrap.Modal
        .getInstance(
            document.getElementById(
                "submitRequestModal"
            )
        )
        .hide();

        document
        .getElementById("requestForm")
        .reset();

        loadStatistics();
    }

    catch(error)
    {
        console.error(error);

        alert(
            "Unable to submit request."
        );
    }
}

/* ==========================================
   SIGN DOCUMENT
========================================== */

async function signDocument()
{
    if(currentDocument == null)
    {
        return;
    }

    alert(
        "Backend: signDocument.php"
    );

    bootstrap.Modal
    .getInstance(
        document.getElementById("signModal")
    )
    .hide();

    await loadDocuments();

    await loadStatistics();
}

/* ==========================================
   REJECT DOCUMENT
========================================== */

async function rejectDocument()
{
    if(currentDocument == null)
    {
        return;
    }

    alert(
        "Backend: rejectDocument.php"
    );

    bootstrap.Modal
    .getInstance(
        document.getElementById("rejectModal")
    )
    .hide();

    await loadDocuments();

    await loadStatistics();
}

/* ==========================================
   UPLOAD SIGNED DOCUMENT
========================================== */

async function uploadSignedDocument()
{
    let file = null;

    file =
        document.getElementById(
            "signedFile"
        ).files[0];

    if(file == null)
    {
        alert(
            "Please select a PDF file."
        );

        return;
    }

    alert(
        "Backend: uploadSignedDocument.php"
    );

    bootstrap.Modal
    .getInstance(
        document.getElementById("uploadModal")
    )
    .hide();

    document
    .getElementById("signedFile")
    .value = "";

    await loadDocuments();
}

/* ==========================================
   THEME
========================================== */

function initializeTheme()
{
    const toggle =
        document.getElementById(
            "themeToggle"
        );

    if(toggle)
    {
        toggle.addEventListener(
            "click",

            function()
            {
                document.body.classList.toggle(
                    "dark-mode"
                );
            }
        );
    }
}

/* ==========================================
   REPORT CHART
========================================== */

function loadChart()
{
    const canvas =
        document.getElementById(
            "documentChart"
        );

    if(canvas == null)
    {
        return;
    }

    if(dashboardChart != null)
    {
        dashboardChart.destroy();
    }

    dashboardChart =
        new Chart(canvas,
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
                            parseInt(
                                document.getElementById(
                                    "pending-count"
                                ).textContent
                            ),

                            parseInt(
                                document.getElementById(
                                    "signed-count"
                                ).textContent
                            ),

                            parseInt(
                                document.getElementById(
                                    "finished-count"
                                ).textContent
                            )
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
   BUTTONS
========================================== */

function initializeButtons()
{
    const logoutButton =
        document.querySelector(
            ".logout-btn"
        );

    if(logoutButton)
    {
        logoutButton.addEventListener(
            "click",

            function()
            {
                window.location.href =
                    "../index.html";
            }
        );
    }
}