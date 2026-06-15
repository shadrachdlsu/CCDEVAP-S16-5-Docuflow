
let selectedDocument = null;

fetch("../data/documents.json")
.then(function(response){

    if(!response.ok){
        throw new Error("Unable to load documents.");
    }

    return response.json();
})
.then(function(documents){

    let tableBody =
        document.getElementById(
            "documents-table-body"
        );

    let pendingCount = 0;
    let approvedCount = 0;
    let rejectedCount = 0;

    documents.forEach(function(documentItem,index){

        if(documentItem.status === "Pending"){
            pendingCount++;
        }
        else if(documentItem.status === "Approved"){
            approvedCount++;
        }
        else if(documentItem.status === "Rejected"){
            rejectedCount++;
        }

        tableBody.innerHTML += `
            <tr>
                <td>${documentItem.title}</td>
                <td>${documentItem.type}</td>
                <td>${documentItem.status}</td>
                <td>
                    <button
                        class="view-button"
                        data-index="${index}">
                        View
                    </button>
                </td>
            </tr>
        `;
    });

    document.getElementById(
        "pending-count"
    ).textContent = pendingCount;

    document.getElementById(
        "approved-count"
    ).textContent = approvedCount;

    document.getElementById(
        "rejected-count"
    ).textContent = rejectedCount;

    let viewButtons =
        document.querySelectorAll(
            ".view-button"
        );

    viewButtons.forEach(function(button){

        button.addEventListener(
            "click",
            function(){

                let documentIndex =
                    this.dataset.index;

                selectedDocument =
                    documents[documentIndex];

                displayDocumentDetails(
                    selectedDocument
                );
            }
        );
    });
})
.catch(function(error){

    console.error(error);

    alert(
        "Failed to load documents."
    );
});

function displayDocumentDetails(
    documentItem
){

    document.getElementById(
        "document-title"
    ).textContent =
        documentItem.title;

    document.getElementById(
        "document-type"
    ).textContent =
        documentItem.type;

    document.getElementById(
        "document-status"
    ).textContent =
        documentItem.status;
}

document
.getElementById(
    "approve-document-button"
)
.addEventListener(
    "click",
    function(){

        if(selectedDocument){

            document.getElementById(
                "document-status"
            ).textContent =
                "Approved";

            alert(
                "Document approved successfully."
            );
        }
        else{

            alert(
                "Select a document first."
            );
        }
    }
);

document
.getElementById(
    "reject-document-button"
)
.addEventListener(
    "click",
    function(){

        if(selectedDocument){

            document.getElementById(
                "document-status"
            ).textContent =
                "Rejected";

            alert(
                "Document rejected."
            );
        }
        else{

            alert(
                "Select a document first."
            );
        }
    }
);

document
.getElementById(
    "request-form"
)
.addEventListener(
    "submit",
    function(event){

        event.preventDefault();

        alert(
            "Request submitted successfully!"
        );

        this.reset();
    }
);