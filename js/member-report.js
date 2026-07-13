/* ==========================================
   MEMBER REPORT
========================================== */

let documents = [];
let paperTrail = [];
let reportChart = null;

/* ==========================================
   INITIALIZE
========================================== */

document.addEventListener(

    "DOMContentLoaded",

    function(){

        loadReport();

        registerEvents();

    }

);

/* ==========================================
   LOAD REPORT DATA
========================================== */

async function loadReport(){

    try{

        let responses = await Promise.all([

            fetch("../data/documents.json"),

            fetch("../data/paper-trail.json")

        ]);

        documents =
            await responses[0].json();

        paperTrail =
            await responses[1].json();

        populateReportTable();

        populateActivityTable();

        initializeDataTables();

        updateStatistics();

        initializeChart();

    }

    catch(error){

        console.error(

            "Unable to load report.",

            error

        );

    }

}

/* ==========================================
   DOCUMENT REPORT TABLE
========================================== */

function populateReportTable(){

    let tableBody =

        document.querySelector(

            "#member-report-table tbody"

        );

    tableBody.innerHTML = "";

    documents.forEach(function(documentItem){

        tableBody.innerHTML += `

        <tr>

            <td>

                ${documentItem.docNumber}

            </td>

            <td>

                ${documentItem.title}

            </td>

            <td>

                ${documentItem.category}

            </td>

            <td>

                ${documentItem.department}

            </td>

            <td>

                <span class="status-${documentItem.status.toLowerCase()}">

                    ${documentItem.status}

                </span>

            </td>

            <td>

                ${new Date(

                    documentItem.uploadedAt

                ).toLocaleDateString()}

            </td>

        </tr>

        `;

    });

}

/* ==========================================
   PAPER TRAIL TABLE
========================================== */

function populateActivityTable(){

    let tableBody =

        document.querySelector(

            "#activity-table tbody"

        );

    tableBody.innerHTML = "";

    paperTrail.forEach(function(activity){

        tableBody.innerHTML += `

        <tr>

            <td>

                ${activity.date}

            </td>

            <td>

                ${activity.action}

            </td>

            <td>

                ${activity.user}

            </td>

            <td>

                <span class="status-${activity.status.toLowerCase()}">

                    ${activity.status}

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

    if($.fn.DataTable.isDataTable("#member-report-table")){

        $("#member-report-table")
        .DataTable()
        .destroy();

    }

    if($.fn.DataTable.isDataTable("#activity-table")){

        $("#activity-table")
        .DataTable()
        .destroy();

    }

    $("#member-report-table").DataTable({

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

    $("#activity-table").DataTable({

        pageLength:5,

        responsive:true,

        ordering:true,

        searching:true,

        info:true

    });

}

/* ==========================================
   UPDATE REPORT STATISTICS
========================================== */

function updateStatistics(){

    let pending = 0;

    let signed = 0;

    let finished = 0;

    let returned = 0;

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

    paperTrail.forEach(function(activity){

        if(activity.action.includes("Rejected")){

            returned++;

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

    document.getElementById(

        "received-count"

    ).textContent = documents.length;

    document.getElementById(

        "signed-report-count"

    ).textContent = signed;

    document.getElementById(

        "returned-count"

    ).textContent = returned;

    document.getElementById(

        "finished-report-count"

    ).textContent = finished;

}

/* ==========================================
   CHART
========================================== */

function initializeChart(){

    let canvas =

        document.getElementById(

            "member-report-chart"

        );

    if(!canvas){

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

    if(reportChart !== null){

        reportChart.destroy();

    }

    reportChart = new Chart(canvas,{

        type:"bar",

        data:{

            labels:[

                "Pending",

                "Signed",

                "Finished"

            ],

            datasets:[{

                label:"Documents",

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

                borderRadius:8

            }]

        },

        options:{

            responsive:true,

            maintainAspectRatio:false,

            plugins:{

                legend:{

                    display:false

                }

            },

            scales:{

                y:{

                    beginAtZero:true,

                    ticks:{

                        precision:0

                    }

                }

            }

        }

    });

}

/* ==========================================
   REFRESH REPORT
========================================== */

function refreshReport(){

    populateReportTable();

    populateActivityTable();

    initializeDataTables();

    updateStatistics();

    initializeChart();

}

/* ==========================================
   REGISTER EVENTS
========================================== */

function registerEvents(){

    let downloadButton =

        document.getElementById(

            "download-report"

        );

    if(downloadButton){

        downloadButton.addEventListener(

            "click",

            exportReport

        );

    }

}

/* ==========================================
   EXPORT REPORT
========================================== */

function exportReport(){

    let report =

`========================================
           DOCUFLOW MEMBER REPORT
========================================

Generated:
${new Date().toLocaleString()}

----------------------------------------
SUMMARY
----------------------------------------

Total Documents : ${documents.length}

Pending         : ${
documents.filter(function(documentItem){

    return documentItem.status === "Pending";

}).length}

Signed          : ${
documents.filter(function(documentItem){

    return documentItem.status === "Signed";

}).length}

Finished        : ${
documents.filter(function(documentItem){

    return documentItem.status === "Finished";

}).length}

========================================
DOCUMENTS
========================================

`;

    documents.forEach(function(documentItem){

        report +=

`Document No : ${documentItem.docNumber}
Title       : ${documentItem.title}
Type        : ${documentItem.category}
Department  : ${documentItem.department}
Status      : ${documentItem.status}
Uploaded    : ${new Date(
documentItem.uploadedAt
).toLocaleDateString()}

----------------------------------------

`;

    });

    let blob =

        new Blob(

            [report],

            {

                type:"text/plain"

            }

        );

    let url =

        URL.createObjectURL(

            blob

        );

    let link =

        document.createElement(

            "a"

        );

    link.href = url;

    link.download =

        "member-report.txt";

    document.body.appendChild(

        link

    );

    link.click();

    document.body.removeChild(

        link

    );

    URL.revokeObjectURL(

        url

    );

}

/* ==========================================
   FILTER DOCUMENTS
========================================== */

function filterDocuments(status){

    if(status === "All"){

        refreshReport();

        return;

    }

    let filteredDocuments =

        documents.filter(function(documentItem){

            return documentItem.status === status;

        });

    let tableBody =

        document.querySelector(

            "#member-report-table tbody"

        );

    tableBody.innerHTML = "";

    filteredDocuments.forEach(function(documentItem){

        tableBody.innerHTML += `

        <tr>

            <td>${documentItem.docNumber}</td>

            <td>${documentItem.title}</td>

            <td>${documentItem.category}</td>

            <td>${documentItem.department}</td>

            <td>

                <span class="status-${documentItem.status.toLowerCase()}">

                    ${documentItem.status}

                </span>

            </td>

            <td>

                ${new Date(
                    documentItem.uploadedAt
                ).toLocaleDateString()}

            </td>

        </tr>

        `;

    });

    initializeDataTables();

}

/* ==========================================
   HELPER FUNCTIONS
========================================== */

function countStatus(status){

    return documents.filter(function(documentItem){

        return documentItem.status === status;

    }).length;

}

/* ==========================================
   WINDOW RESIZE
========================================== */

window.addEventListener(

    "resize",

    function(){

        if(reportChart){

            reportChart.resize();

        }

    }

);

/* ==========================================
   REPORT READY
========================================== */

console.log(

    "Member Report Loaded Successfully."

);