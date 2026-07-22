<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "../config/connections.php";
require_once "../models/user.php";
require_once "../models/documentRoute.php";
require_once "../models/documentRequest.php";
require_once "../models/document.php";
require_once "../models/documentTrail.php";
require_once "../models/documentType.php";

/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit();
}

$userId = (int) $_SESSION["user_id"];

$userModel = new User();
$routeModel = new DocumentRoute();
$requestModel = new DocumentRequest();
$documentModel = new Document();
$trailModel = new DocumentTrail();
$docTypeModel = new DocumentType();

/*
|--------------------------------------------------------------------------
| USER INFORMATION
|--------------------------------------------------------------------------
*/

$user = $userModel->findById($userId);

/*
|--------------------------------------------------------------------------
| DASHBOARD COUNTS
|--------------------------------------------------------------------------
*/

$pending = $routeModel->countBySignatoryAndStatus($userId, 'Waiting');
$signed = $routeModel->countBySignatoryAndStatus($userId, 'Signed');
$finished = $documentModel->countByCreator($userId, 'Completed');
$requests = $requestModel->countByUser($userId);

/*
|--------------------------------------------------------------------------
| CHART DATA
|--------------------------------------------------------------------------
*/

$chartData = [
    $pending,
    $signed,
    $finished,
    $requests
];

/*
|--------------------------------------------------------------------------
| PENDING DOCUMENTS
|--------------------------------------------------------------------------
*/

$documents = $routeModel->getPendingForSignatory($userId);

/*
|--------------------------------------------------------------------------
| PAPER TRAIL
|--------------------------------------------------------------------------
*/

$trail = $trailModel->getRecent(20);

/*
|--------------------------------------------------------------------------
| DOCUMENT TYPES
|--------------------------------------------------------------------------
*/

$types = $docTypeModel->getAllActive();


/*
|--------------------------------------------------------------------------
| AJAX REQUEST HANDLER
|--------------------------------------------------------------------------
*/

if(isset($_GET["action"]))
{
    header("Content-Type: application/json");

    switch($_GET["action"])
    {
        case "documents":
            echo json_encode($documents);
            break;

        case "statistics":
            echo json_encode([
                "pending" => $pending,
                "signed" => $signed,
                "finished" => $finished,
                "requests" => $requests
            ]);
            break;

        case "profile":
            echo json_encode($user);
            break;

        case "paperTrail":
            echo json_encode($trail);
            break;

        default:
            echo json_encode(["success" => false]);
            break;
    }
    exit();
}

/*
|--------------------------------------------------------------------------
| MEMBER REQUEST LIST
|--------------------------------------------------------------------------
*/

$requestsList = $requestModel->getByUser($userId);

?>