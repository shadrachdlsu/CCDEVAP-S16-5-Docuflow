<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/documentRoute.php";
require_once __DIR__ . "/../models/documentType.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$userModel = new User();
$routeModel = new DocumentRoute();
$docTypeModel = new DocumentType();

$action = $_GET["action"] ?? null;

if ($action !== null) {

    header("Content-Type: application/json; charset=utf-8");

    if ($action === "reports") {
        echo json_encode($routeModel->getRoutesForSignatory($userId));
        exit;
    }

    if ($action === "statistics") {
        echo json_encode($routeModel->getStatisticsForSignatory($userId));
        exit;
    }

    if ($action === "types") {
        echo json_encode($docTypeModel->getAllActive());
        exit;
    }

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid report action."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| MEMBER INFORMATION
|--------------------------------------------------------------------------
*/

$user = $userModel->findById($userId);

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

$reportDocuments = $routeModel->getRoutesForSignatory($userId);

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