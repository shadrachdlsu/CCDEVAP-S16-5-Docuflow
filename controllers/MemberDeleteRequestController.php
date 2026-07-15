<?php

session_start();

require_once "../config/database.php";


header("Content-Type: application/json");


if(!isset($_SESSION["user_id"]))
{
    echo json_encode(
    [
        "success"=>false,
        "message"=>"Not authenticated."
    ]);

    exit();
}


$userId = $_SESSION["user_id"];


$data = json_decode(
    file_get_contents("php://input"),
    true
);


$requestId = $data["request_id"];



$sql = "

DELETE FROM document_requests

WHERE request_id = ?

AND requested_by_id = ?

";


$stmt = $pdo->prepare($sql);


$result = $stmt->execute(
[
    $requestId,
    $userId
]);



if($result)
{
    echo json_encode(
    [
        "success"=>true,
        "message"=>"Request deleted successfully."
    ]);
}
else
{
    echo json_encode(
    [
        "success"=>false,
        "message"=>"Unable to delete request."
    ]);
}


?>