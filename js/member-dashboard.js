/* ==========================================
   MEMBER DASHBOARD
========================================== */

let documents = [];
let paperTrail = [];
let documentChart = null;

/* ==========================================
   INITIALIZE DASHBOARD
========================================== */

document.addEventListener(

    "DOMContentLoaded",

    function(){

        loadDashboard();

        registerEvents();

    }

);

/* ==========================================
   LOAD DASHBOARD
========================================== */

async function loadDashboard(){

    try{

        let responses = await Promise.all([

            fetch("../data/documents.json"),

            fetch("../data/paper-trail.json")

        ]);

        documents =
            await responses[0].json();

        paperTrail =
            await responses[1].json();

        populateDocumentsTable();

        populatePaperTrailTable();

        initializeDataTables();

        updateStatistics();

        initializeChart();

    }

    catch(error){

        console.error(

            "Failed to load dashboard.",

            error

        );

    }

}

/* ==========================================
   DOCUMENT TABLE
========================================== */

function populateDocumentsTable(){

    let tableBody =

        document.querySelector(

            "#documents-table tbody"

        );

    tableBody.innerHTML = "";

    documents.forEach(function(documentItem){

        tableBody.innerHTML += `

        <tr>

            <td>

                ${documentItem.title}

            </td>

            <td>

                ${documentItem.type}

            </td>

            <td>

                <span class="status-${documentItem.status.toLowerCase()}">

                    ${documentItem.status}

                </span>

            </td>

            <td>

                <button

                    class="btn btn-primary btn-sm preview-button"

                    data-id="${documentItem.id}">

                    <i class="fas fa-eye"></i>

                </button>

            </td>

            <td>

                <a

                    href="${documentItem.file}"

                    download

                    class="btn btn-info btn-sm">

                    <i class="fas fa-download"></i>

                </a>

            </td>

            <td>

                <button

                    class="btn btn-success btn-sm sign-button"

                    data-id="${documentItem.id}">

                    <i class="fas fa-signature"></i>

                </button>

            </td>

            <td>

                <button

                    class="btn btn-danger btn-sm reject-button"

                    data-id="${documentItem.id}">

                    <i class="fas fa-times"></i>

                </button>

            </td>

            <td>

                <button

                    class="btn btn-secondary btn-sm upload-button"

                    data-id="${documentItem.id}">

                    <i class="fas fa-upload"></i>

                </button>

            </td>

        </tr>

        `;

    });

}

/* ==========================================
   PAPER TRAIL TABLE
========================================== */

function populatePaperTrailTable(){

    let tableBody =

        document.querySelector(

            "#paper-trail-table tbody"

        );

    tableBody.innerHTML = "";

    paperTrail.forEach(function(item){

        tableBody.innerHTML += `

        <tr>

            <td>${item.date}</td>

            <td>${item.action}</td>

            <td>${item.user}</td>

            <td>

                <span class="status-${item.status.toLowerCase()}">

                    ${item.status}

                </span>

            </td>

        </tr>

        `;

    });

}

/* ==========================================
   INITIALIZE DATATABLES
========================================== */

function initializeDataTables(){

    if($.fn.DataTable.isDataTable("#documents-table")){

        $("#documents-table")
        .DataTable()
        .destroy();

    }

    if($.fn.DataTable.isDataTable("#paper-trail-table")){

        $("#paper-trail-table")
        .DataTable()
        .destroy();

    }

    $("#documents-table").DataTable({

        pageLength:5,

        responsive:true,

        ordering:true,

        searching:true,

        info:true,

        lengthMenu:[
            [5,10,25,50,-1],
            [5,10,25,50,"All"]
        ]

    });

    $("#paper-trail-table").DataTable({

        pageLength:5,

        responsive:true,

        ordering:true,

        searching:true,

        info:true

    });

}

/* ==========================================
   UPDATE DASHBOARD STATISTICS
========================================== */

function updateStatistics(){

    let pending = 0;
    let signed = 0;
    let finished = 0;

    documents.forEach(function(documentItem){

        if(documentItem.status === "Pending"){

            pending++;

        }

        else if(documentItem.status === "Signed"){

            signed++;

        }

        else if(documentItem.status === "Finished"){

            finished++;

        }

    });

    document.getElementById(
        "pending-count"
    ).textContent = pending;

    document.getElementById(
        "signed-count"
    ).textContent = signed;

    document.getElementById(
        "finished-count"
    ).textContent = finished;

}

/* ==========================================
   CHART.JS
========================================== */

function initializeChart(){

    let chartCanvas =
        document.getElementById(
            "document-chart"
        );

    if(!chartCanvas){

        return;

    }

    let pending =
        documents.filter(function(documentItem){

            return documentItem.status === "Pending";

        }).length;

    let signed =
        documents.filter(function(documentItem){

            return documentItem.status === "Signed";

        }).length;

    let finished =
        documents.filter(function(documentItem){

            return documentItem.status === "Finished";

        }).length;

    if(documentChart !== null){

        documentChart.destroy();

    }

    documentChart = new Chart(chartCanvas,{

        type:"doughnut",

        data:{

            labels:[

                "Pending",

                "Signed",

                "Finished"

            ],

            datasets:[{

                data:[

                    pending,

                    signed,

                    finished

                ],

                backgroundColor:[

                    "#f59e0b",

                    "#3b82f6",

                    "#22c55e"

                ],

                borderWidth:0

            }]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{

                legend:{

                    position:"bottom"

                }

            }

        }

    });

}

/* ==========================================
   REFRESH DASHBOARD
========================================== */

function refreshDashboard(){

    populateDocumentsTable();

    populatePaperTrailTable();

    initializeDataTables();

    updateStatistics();

    initializeChart();

}

/* ==========================================
   REGISTER EVENTS
========================================== */

function registerEvents(){

    document.addEventListener(

        "click",

        handleDocumentActions

    );

    document

    .getElementById(

        "request-form"

    )

    .addEventListener(

        "submit",

        submitRequest

    );

}

/* ==========================================
   DOCUMENT ACTIONS
========================================== */

function handleDocumentActions(event){

    let button = event.target.closest("button");

    if(!button){

        return;

    }

    let documentId = button.dataset.id;

    if(button.classList.contains("preview-button")){

        previewDocument(documentId);

    }

    else if(button.classList.contains("sign-button")){

        signDocument(documentId);

    }

    else if(button.classList.contains("reject-button")){

        rejectDocument(documentId);

    }

    else if(button.classList.contains("upload-button")){

        uploadSignedDocument(documentId);

    }

}

/* ==========================================
   PREVIEW DOCUMENT
========================================== */

function previewDocument(documentId){

    let documentItem = documents.find(function(item){

        return item.id === documentId;

    });

    if(!documentItem){

        return;

    }

    document.getElementById(

        "preview-frame"

    ).src = documentItem.file;

    let modal = new bootstrap.Modal(

        document.getElementById(

            "previewModal"

        )

    );

    modal.show();

}

/* ==========================================
   SIGN DOCUMENT
========================================== */

function signDocument(documentId){

    let remarks = prompt(

        "Optional remarks before signing:"

    );

    documents.forEach(function(item){

        if(item.id === documentId){

            item.status = "Signed";

            item.remarks = remarks;

        }

    });

    paperTrail.unshift({

        date:new Date().toLocaleDateString(),

        action:"Document Signed",

        user:"Current Member",

        status:"Signed"

    });

    refreshDashboard();

    alert("Document signed successfully.");

}

/* ==========================================
   REJECT DOCUMENT
========================================== */

function rejectDocument(documentId){

    let reason = prompt(

        "Enter rejection reason:"

    );

    if(reason === null){

        return;

    }

    if(reason.trim() === ""){

        alert(

            "Rejection reason is required."

        );

        return;

    }

    documents.forEach(function(item){

        if(item.id === documentId){

            item.status = "Pending";

            item.rejectionReason = reason;

        }

    });

    paperTrail.unshift({

        date:new Date().toLocaleDateString(),

        action:"Document Rejected",

        user:"Current Member",

        status:"Pending"

    });

    refreshDashboard();

    alert(

        "Document returned to the secretary."

    );

}

/* ==========================================
   UPLOAD SIGNED DOCUMENT
========================================== */

function uploadSignedDocument(documentId){

    let fileInput = document.createElement("input");

    fileInput.type = "file";

    fileInput.accept = ".pdf";

    fileInput.onchange = function(){

        if(fileInput.files.length > 0){

            alert(

                "Signed document uploaded."

            );

            paperTrail.unshift({

                date:new Date().toLocaleDateString(),

                action:"Signed PDF Uploaded",

                user:"Current Member",

                status:"Signed"

            });

            refreshDashboard();

        }

    };

    fileInput.click();

}

/* ==========================================
   SUBMIT REQUEST
========================================== */

function submitRequest(event){

    event.preventDefault();

    let title =

        document.getElementById(

            "request-title"

        ).value.trim();

    let type =

        document.getElementById(

            "request-type"

        ).value;

    let email =

        document.getElementById(

            "secretary-email"

        ).value.trim();

    if(

        title === "" ||

        type === "" ||

        email === ""

    ){

        alert(

            "Please complete all required fields."

        );

        return;

    }

    if(

        !email.includes("@") ||

        !email.includes(".com")

    ){

        alert(

            "Email must contain @ and .com"

        );

        return;

    }

    alert(

        "Request submitted successfully."

    );

    event.target.reset();

}

/* ==========================================
   LOGOUT
========================================== */

function logout(){

    if(

        confirm(

            "Are you sure you want to logout?"

        )

    ){

        window.location.href =

        "login.html";

    }

}