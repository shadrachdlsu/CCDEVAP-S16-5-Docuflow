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
        echo json_encode($routeModel->getMemberReportStatistics($userId));
        exit;
    }

    if ($action === "officeTrends") {
        echo json_encode($routeModel->getOfficeStatusTrends($userId));
        exit;
    }

    if ($action === "routeStatus") {
        echo json_encode($routeModel->getRouteStatusDistribution($userId));
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

$statistics = $routeModel->getMemberReportStatistics($userId);

$totalRouteSteps = $statistics["total_route_steps"];
$totalDocuments = $statistics["total_documents"];
$rejectedRoutes = $statistics["rejected"];
$pendingDocuments = $statistics["pending"];
$signedDocuments = $statistics["signed"];
$finishedDocuments = $statistics["completed"];