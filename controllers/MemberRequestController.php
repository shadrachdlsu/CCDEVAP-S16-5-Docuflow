<?php

session_start();

require_once "../config/database.php";


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


$userId =
    $_SESSION["user_id"];


$title =
    $_POST["title"];


$description =
    $_POST["description"];


$type =
    $_POST["type_id"];



/*
|--------------------------------------------------------------------------
| GET USER OFFICE
|--------------------------------------------------------------------------
*/

$getOffice =
    $pdo->prepare("

    SELECT office_id

    FROM users

    WHERE user_id=?

    ");


$getOffice->execute(
    [
        $userId
    ]
);


officeId =
    $getOffice->fetchColumn();



if($officeId == null)
{
    echo json_encode(
        [
            "success"=>false,
            "message"=>"No office assigned"
        ]
    );

    exit();
}



/*
|--------------------------------------------------------------------------
| INSERT REQUEST
|--------------------------------------------------------------------------
*/

$sql = "

INSERT INTO document_requests

(

requested_by_id,

office_id,

type_id,

title,

description,

status

)

VALUES

(

?,?,?,?,?,

'Pending'

)

";


$stmt =
    $pdo->prepare($sql);


$stmt->execute(
    [
        $userId,

        $officeId,

        $type,

        $title,

        $description
    ]
);



eecho json_encode([

    "success"=>true,

    "message"=>"Document request submitted successfully."

]);

?>