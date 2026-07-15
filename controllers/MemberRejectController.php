<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]))
{
    echo json_encode(
        [
            "success" => false,
            "message" => "Not authenticated"
        ]
    );

    exit();
}


$userId = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| GET JSON DATA
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);


$documentId = $data["document_id"];

$reason = "";


if(isset($data["reason"]))
{
    $reason = $data["reason"];
}


/*
|--------------------------------------------------------------------------
| UPDATE ROUTE
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE document_routes

SET

status='Rejected',

remarks=?,

acted_at=NOW()

WHERE

document_id=?

AND

signatory_user_id=?

";


$stmt = $pdo->prepare($sql);


$stmt->execute(
    [
        $reason,

        $documentId,

        $userId
    ]
);



/*
|--------------------------------------------------------------------------
| UPDATE DOCUMENT STATUS
|--------------------------------------------------------------------------
*/

$update = $pdo->prepare("

UPDATE documents

SET

status='Rejected'

WHERE

document_id=?

");


$update->execute(
    [
        $documentId
    ]
);



/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode(
    [
        "success" => true,

        "message" => "Document rejected successfully"
    ]
);

?>