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
        "../controllers/MemberDashboardController.php?action=documents",

    statistics:
        "../controllers/MemberDashboardController.php?action=statistics",

    profile:
        "../controllers/MemberDashboardController.php?action=profile",

    paperTrail:
        "../controllers/MemberDashboardController.php?action=paperTrail",

    requests:
        "../controllers/MemberRequestController.php",

    sign:
        "../controllers/MemberSignController.php",

    reject:
        "../controllers/MemberRejectController.php",

    upload:
        "../controllers/MemberUploadController.php",
    deleteRequest:
        "../controllers/MemberDeleteRequestController.php"
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

let reportChart = null;

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
    initializeTables();
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

    document.addEventListener("click", function(event)
    {
        const button = event.target.closest(".deleteRequest");

        if(!button)
        {
            return;
        }

        deleteRequest(button.dataset.id);
    });

    const previewBtn =
        document.querySelector(".previewBtn");

    const signBtn =
        document.querySelector(".signBtn");

    const rejectBtn =
        document.querySelector(".rejectBtn");

    if(previewBtn)
    {
        previewBtn.addEventListener("click", function()
        {
            const file =
                formatFilePath(this.dataset.file);

            document.getElementById("previewFrame").src = file;
            document.getElementById("downloadPreview").href = file;

            openModal("previewModal");
        });
    }

    if(signBtn)
    {
        signBtn.addEventListener("click", function()
        {
            openSignModal(this.dataset.id);
        });
    }

    if(rejectBtn)
    {
        rejectBtn.addEventListener("click", function()
        {
            openRejectModal(this.dataset.id);
        });
    }
}

/* ==========================================
   LOAD STATISTICS
========================================== */

async function loadStatistics()
{
    let response = null;

    let statistics = null;

    try
    {
        response =
            await fetch(API.statistics);

        if(!response.ok)
        {
            throw new Error("Unable to load statistics.");
        }

        statistics =
            await response.json();

        document.getElementById("pending-count").textContent =
            statistics.pending ?? 0;

        document.getElementById("signed-count").textContent =
            statistics.signed ?? 0;

        document.getElementById("finished-count").textContent =
            statistics.finished ?? 0;

        document.getElementById("request-count").textContent =
            statistics.requests ?? 0;
    }

    catch(error)
    {
        console.error(error);

        alert("Unable to load statistics.");
    }
}

/* ==========================================
   LOAD DOCUMENTS
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
            throw new Error("Unable to load documents.");
        }

        documents =
            await response.json();

        documentTable.clear();

        documents.forEach(function(doc)
        {
            documentTable.row.add(
            [
                doc.tracking_code,

                doc.title,

                doc.type_name,

                doc.office_name,

                createStatusBadge(doc.status),

                createActionButtons(doc)
            ]);
        });

        documentTable.draw(false);
    }

    catch(error)
    {
        console.error(error);

        alert("Unable to load documents.");
    }
}

/* ==========================================
   STATUS BADGE CREATOR
========================================== */

function createStatusBadge(status)
{
    switch(status)
    {
        case "Waiting":
        case "Pending":
        case "For Signature":
            return '<span class="status-badge status-pending">Pending</span>';

        case "Signed":
            return '<span class="status-badge status-signed">Signed</span>';

        case "Completed":
        case "Finished":
            return '<span class="status-badge status-finished">Finished</span>';

        case "Rejected":
            return '<span class="status-badge status-rejected">Rejected</span>';

        default:
            return `<span class="status-badge status-pending">${status ?? "Unknown"}</span>`;
    }
}

/* ==========================================
/* ==========================================
   FORMAT FILE PATH
========================================== */

function formatFilePath(filePath)
{
    if (!filePath)
    {
        return "#";
    }

    let cleanPath = String(filePath).trim();

    cleanPath = cleanPath.replace(/^\/?CCDEVAP-MP1\//i, "");
    cleanPath = cleanPath.replace(/^\/+/, "");

    if (
        cleanPath.startsWith("pdfs/") ||
        cleanPath.startsWith("uploads/")
    ) {
        return "../" + cleanPath;
    }

    return cleanPath;
}

/* ==========================================
   ACTION BUTTONS
========================================== */

function createActionButtons(document)
{
    let filePath = formatFilePath(document.file_path);

    let buttons = `
        <div class="action-buttons">

            <button
                class="btn-small previewBtn"
                onclick="previewDocument(${document.document_id})"
                title="Preview">
                Preview
            </button>

            <a
                href="${filePath}"
                class="btn-small action-btn"
                download
                title="Download"
                style="text-decoration: none;">
                Download
            </a>
    `;

    if (
        document.status === "Pending" ||
        document.status === "Waiting"
    ) {

        buttons += `
            <button
                class="btn-small signBtn"
                onclick="openSignModal(${document.document_id})"
                title="Sign">
                Sign
            </button>

            <button
                class="btn-small rejectBtn"
                onclick="openRejectModal(${document.document_id})"
                title="Reject">
                Reject
            </button>

            <button
                class="btn-small action-btn"
                onclick="openUploadModal(${document.document_id})"
                title="Upload Signed">
                Upload
            </button>
        `;
    }

    buttons += `</div>`;

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
    currentDocument = findDocument(documentId);

    if(currentDocument == null)
    {
        alert("Document not found.");
        return;
    }

    const filePath = formatFilePath(currentDocument.file_path);

    document.getElementById("previewFrame").src = filePath;

    document.getElementById("downloadPreview").href = filePath;

    openModal("previewModal");
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

    openModal("signModal");
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

    openModal("rejectModal");
}

/* ==========================================
   OPEN UPLOAD MODAL
========================================== */

function openUploadModal(documentId)
{
    currentDocument = findDocument(documentId);

    if(!currentDocument)
    {
        alert("Document not found.");
        return;
    }

    const fileInput =
        document.getElementById("signedFile");

    if(fileInput)
    {
        fileInput.value = "";
    }

    openModal("uploadModal");
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

                log.action_taken ?? "Unknown action",

                log.full_name ?? "Unknown user",

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
    try
    {
        const response =
            await fetch(API.profile);

        if(!response.ok)
        {
            return;
        }

        const profile =
            await response.json();

        if(!profile)
        {
            return;
        }

        const profileName =
            document.getElementById("profileName");

        const profileEmail =
            document.getElementById("profileEmail");

        const profileOffice =
            document.getElementById("profileOffice");

        const profileRole =
            document.getElementById("profileRole");

        const memberEmail =
            document.getElementById("memberEmail");

        if(profileName)
        {
            profileName.textContent =
                profile.full_name || "N/A";
        }

        if(profileEmail)
        {
            profileEmail.textContent =
                profile.email || "N/A";
        }

        if(profileOffice)
        {
            profileOffice.textContent =
                profile.office_name || "Unassigned";
        }

        if(profileRole)
        {
            profileRole.textContent =
                profile.role_name || "Member";
        }

        if(memberEmail)
        {
            memberEmail.textContent =
                profile.email || "";
        }
    }

    catch(error)
    {
        console.error("Load profile error:", error);
    }
}

/* ==========================================
   SUBMIT REQUEST
========================================== */

async function submitRequest(event)
{
    event.preventDefault();

    const title =
        document.getElementById("requestTitle").value.trim();

    const typeId =
        document.getElementById("requestType").value;

    const description =
        document.getElementById("requestDescription").value.trim();

    const secretaryEmail =
        document.getElementById("secretaryEmail").value.trim();

    if(!title || !typeId || !secretaryEmail)
    {
        alert("Please complete all required fields.");
        return;
    }

    const formData = new FormData();

    formData.append("title", title);
    formData.append("type_id", typeId);
    formData.append("description", description);
    formData.append("secretary_email", secretaryEmail);

    try
    {
        const response = await fetch(
            API.requests,
            {
                method: "POST",
                body: formData
            }
        );

        const responseText =
            await response.text();

        let result;

        try
        {
            result =
                JSON.parse(responseText);
        }
        catch(error)
        {
            throw new Error(
                "Invalid server response: " +
                responseText
            );
        }

        if(!response.ok || !result.success)
        {
            throw new Error(
                result.message ||
                "Unable to submit request."
            );
        }

        alert(result.message);

        closeModal("submitRequestModal");

        document
            .getElementById("requestForm")
            .reset();

        location.reload();
    }
    catch(error)
    {
        console.error(
            "Submit request error:",
            error
        );

        alert(
            error.message ||
            "Unable to submit request."
        );
    }
}

/* ==========================================
   DELETE REQUEST
========================================== */

async function deleteRequest(requestId)
{
    const confirmDelete =
        confirm("Delete this request?");

    if(!confirmDelete)
    {
        return;
    }

    const formData = new FormData();

    formData.append(
        "request_id",
        requestId
    );

    try
    {
        const response =
            await fetch(
                API.deleteRequest,
                {
                    method: "POST",
                    body: formData
                }
            );

        const message =
            await response.text();

        if(!response.ok)
        {
            throw new Error(message);
        }

        alert(message);

        location.reload();
    }

    catch(error)
    {
        console.error(error);

        alert(
            error.message ||
            "Delete failed."
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

    let remarks =
        document.getElementById("signRemarks").value;

    try
    {
        let response =
            await fetch("../controllers/MemberSignController.php",
            {
                method: "POST",

                headers:
                {
                    "Content-Type":
                        "application/json"
                },

                body:
                    JSON.stringify(
                    {
                        document_id:
                            currentDocument.document_id,

                        remarks:
                            remarks
                    })
            });

        let result =
            await response.json();

        alert(result.message);

        if(result.success)
        {
            closeModal("signModal");

            await loadDocuments();

            await loadStatistics();

            await loadPaperTrail();
        }
    }

    catch(error)
    {
        console.error(error);

        alert("Unable to sign document.");
    }
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

    let reason =
        document.getElementById("rejectReason").value;

    try
    {
        let response =
            await fetch("../controllers/MemberRejectController.php",
            {
                method: "POST",

                headers:
                {
                    "Content-Type":
                        "application/json"
                },

                body:
                    JSON.stringify(
                    {
                        document_id:
                            currentDocument.document_id,

                        reason:
                            reason
                    })
            });

        let result =
            await response.json();

        alert(result.message);

        if(result.success)
        {
            closeModal("rejectModal");

            await loadDocuments();

            await loadStatistics();

            await loadPaperTrail();
        }
    }

    catch(error)
    {
        console.error(error);

        alert(
            error.message ||
            "Unable to reject document."
        );
    }
}

/* ==========================================
   UPLOAD SIGNED DOCUMENT
========================================== */

async function uploadSignedDocument()
{
    if(currentDocument == null)
    {
        alert("Please select a document first.");
        return;
    }

    const input =
        document.getElementById("signedFile");

    const file =
        input.files[0];

    if(!file)
    {
        alert("Please select a PDF file.");
        return;
    }

    if(file.type !== "application/pdf")
    {
        alert("Only PDF files are allowed.");
        return;
    }

    const formData = new FormData();

    formData.append(
        "document_id",
        currentDocument.document_id
    );

    formData.append(
        "signed_file",
        file
    );

    try
    {
        const response = await fetch(
            API.upload,
            {
                method: "POST",
                body: formData
            }
        );

        const message =
            await response.text();

        if(!response.ok)
        {
            throw new Error(message);
        }

        alert(message);

        closeModal("uploadModal");

        input.value = "";

        location.reload();
    }
    catch(error)
    {
        console.error(error);

        alert(
            error.message ||
            "Upload failed."
        );
    }
}

/* ==========================================
   REPORT CHART
========================================== */

function loadChart()
{
    const canvas =
        document.getElementById("documentChart");

    if(canvas == null)
    {
        return;
    }

    if(reportChart != null)
    {
        reportChart.destroy();
    }

    reportChart =
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
                            parseInt(document.getElementById("pending-count").textContent),
                            parseInt(document.getElementById("signed-count").textContent),
                            parseInt(document.getElementById("finished-count").textContent)
                        ],

                        backgroundColor:
                        [
                            "#d97706",
                            "#16a34a",
                            "#2563eb"
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
   DARK MODE
========================================== */

document.addEventListener("DOMContentLoaded", function ()
{
    const themeToggle =
        document.getElementById("themeToggle");

    if (!themeToggle)
    {
        return;
    }

    const themeIcon =
        themeToggle.querySelector("i");

    const savedTheme =
        localStorage.getItem("docuflow-theme");

    if (savedTheme === "dark" || localStorage.getItem("theme") === "dark")
    {
        document.documentElement.classList.add("dark-mode");
        document.body.classList.add("dark-mode");
        updateThemeIcon(true);
    }
    else
    {
        document.documentElement.classList.remove("dark-mode");
        document.body.classList.remove("dark-mode");
        updateThemeIcon(false);
    }

    themeToggle.addEventListener("click", function ()
    {
        const isDark = document.body.classList.toggle("dark-mode");
        document.documentElement.classList.toggle("dark-mode", isDark);

        localStorage.setItem(
            "docuflow-theme",
            isDark ? "dark" : "light"
        );
        localStorage.setItem(
            "theme",
            isDark ? "dark" : "light"
        );

        updateThemeIcon(isDark);
    });

    function updateThemeIcon(isDark)
    {
        if (!themeIcon)
        {
            return;
        }

        themeIcon.classList.toggle("fa-moon", !isDark);
        themeIcon.classList.toggle("fa-sun", isDark);
    }
});