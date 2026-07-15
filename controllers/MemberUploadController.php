<?php

session_start();

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if(!isset($_SESSION["user_id"]))
{
    echo json_encode(
        [
            "success"=>false,
            "message"=>"Not authenticated"
        ]
    );

    exit();
}


$userId = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| CHECK FILE
|--------------------------------------------------------------------------
*/

if(!isset($_FILES["signed_file"]))
{
    echo json_encode([
        "success"=>false,
        "message"=>"No file uploaded."
    ]);

    exit();
}


$file=$_FILES["signed_file"];

$documentId =
    $_POST["document_id"];


/*
|--------------------------------------------------------------------------
| CHECK PDF
|--------------------------------------------------------------------------
*/

$extension =
    strtolower(
        pathinfo(
            $file["name"],
            PATHINFO_EXTENSION
        )
    );


if($extension != "pdf")
{
    echo json_encode(
        [
            "success"=>false,
            "message"=>"Only PDF files are allowed"
        ]
    );

    exit();
}



/*
|--------------------------------------------------------------------------
| UPLOAD FILE
|--------------------------------------------------------------------------
*/

$filename =
    time() . "_" . $file["name"];


$destination =
    "../uploads/" . $filename;



if(!move_uploaded_file(
    $file["tmp_name"],
    $destination
))
{
    echo json_encode(
        [
            "success"=>false,
            "message"=>"Upload failed"
        ]
    );

    exit();
}



$filePath =
    "/uploads/" . $filename;



/*
|--------------------------------------------------------------------------
| UPDATE DOCUMENT
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE documents

SET

file_path=?,

status='Completed'

WHERE

document_id=?

";


$stmt =
    $pdo->prepare($sql);


$stmt->execute(
    [
        $filePath,

        $documentId
    ]
);



/*
|--------------------------------------------------------------------------
| UPDATE ROUTE
|--------------------------------------------------------------------------
*/

$sql = "

UPDATE document_routes

SET

status='Completed',

acted_at=NOW()

WHERE

document_id=?

AND

signatory_user_id=?

";


$stmt =
    $pdo->prepare($sql);


$stmt->execute(
    [
        $documentId,

        $userId
    ]
);



/*
|--------------------------------------------------------------------------
| RESPONSE
|--------------------------------------------------------------------------
*/

echo json_encode([

    "success"=>true,

    "message"=>"Signed document uploaded successfully."

    ]);

?>