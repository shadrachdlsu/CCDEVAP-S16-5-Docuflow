<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/connections.php";


if(!isset($_SESSION["user_id"]))
{
    header("Location: ../login.php");
    exit();
}


$userId = $_SESSION["user_id"];


/*
==========================================
DOCUMENT SUMMARY
==========================================
*/

$sql = "

SELECT

    status,

    COUNT(*) total

FROM documents

WHERE creator_id = ?

GROUP BY status

";


$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);


$documentSummary = $stmt->fetchAll();



/*
==========================================
REQUEST SUMMARY
==========================================
*/

$sql = "

SELECT

    dr.status,

    COUNT(*) total

FROM document_requests dr

WHERE requested_by_id = ?

GROUP BY dr.status

";


$stmt = $pdo->prepare($sql);

$stmt->execute([$userId]);


$requestSummary = $stmt->fetchAll();

?>