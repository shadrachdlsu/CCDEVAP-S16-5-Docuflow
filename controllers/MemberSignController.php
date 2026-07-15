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
| READ JSON DATA
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);


$documentId = $data["document_id"];

$remarks = "";


if(isset($data["remarks"]))
{
    $remarks = $data["remarks"];
}


/*
|--------------------------------------------------------------------------
| UPDATE DOCUMENT ROUTE
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE document_routes

SET

status='Signed',

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
        $remarks,
        $documentId,
        $userId
    ]
);


/*
|--------------------------------------------------------------------------
| CHECK IF ALL SIGNATURES ARE DONE
|--------------------------------------------------------------------------
*/

$check = $pdo->prepare("

SELECT COUNT(*)

FROM document_routes

WHERE

document_id=?

AND

status!='Signed'

");


$check->execute(
    [
        $documentId
    ]
);


$remaining =
    $check->fetchColumn();



if($remaining == 0)
{

    $update = $pdo->prepare("

    UPDATE documents

    SET

    status='Completed'

    WHERE

    document_id=?

    ");


    $update->execute(
        [
            $documentId
        ]
    );

}


/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode(
    [
        "success" => true,
        "message" => "Document signed successfully"
    ]
);

?>