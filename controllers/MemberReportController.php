<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| MEMBER INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.full_name,
        u.email,
        r.role_name,
        o.office_name
    FROM users u
    INNER JOIN roles r
        ON u.role_id = r.role_id
    LEFT JOIN offices o
        ON u.office_id = o.office_id
    WHERE u.user_id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../views/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| REPORT DOCUMENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT DISTINCT
        d.document_id,
        d.tracking_code,
        d.title,
        dt.type_name,
        COALESCE(o.office_name, 'No Office') AS office_name,
        d.created_at,
        d.file_path,
        dr.status AS route_status,
        d.status AS document_status
    FROM document_routes dr
    INNER JOIN documents d
        ON dr.document_id = d.document_id
    INNER JOIN document_types dt
        ON d.type_id = dt.type_id
    LEFT JOIN offices o
        ON dr.office_id = o.office_id
    WHERE dr.signatory_user_id = ?
    ORDER BY d.created_at DESC
");

$stmt->execute([$userId]);

$reportDocuments = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalDocuments = count($reportDocuments);
$pendingDocuments = 0;
$signedDocuments = 0;
$finishedDocuments = 0;

foreach ($reportDocuments as $document) {
    $routeStatus = $document["route_status"];
    $documentStatus = $document["document_status"];

    if (
        $routeStatus === "Waiting" ||
        $routeStatus === "Received" ||
        $routeStatus === "For Signature"
    ) {
        $pendingDocuments++;
    }

    if ($routeStatus === "Signed") {
        $signedDocuments++;
    }

    if (
        $routeStatus === "Completed" ||
        $documentStatus === "Completed"
    ) {
        $finishedDocuments++;
    }
}

$chartData = [
    $pendingDocuments,
    $signedDocuments,
    $finishedDocuments
];