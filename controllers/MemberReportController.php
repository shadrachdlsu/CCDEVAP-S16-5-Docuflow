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

$userId = (int) $_SESSION["user_id"];

$sql = "
    SELECT
        u.user_id,
        u.full_name,
        u.email,
        u.office_id,
        r.role_name,
        o.office_name
    FROM users u
    INNER JOIN roles r
        ON u.role_id = r.role_id
    LEFT JOIN offices o
        ON u.office_id = o.office_id
    WHERE u.user_id = ?
    LIMIT 1
";

$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);

$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../views/login.php");
    exit;
}

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