<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/document.php";
require_once __DIR__ . "/../models/documentType.php";
require_once __DIR__ . "/../models/documentTrail.php";
require_once __DIR__ . "/../models/office.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["user_id"]) || $_SESSION["role_id"] != 2 || !isset($_SESSION["office_id"])) {
    header("Location: ../controllers/LogoutController.php");
    exit;
}

$userModel = new User();
$documentModel = new Document();
$docTypeModel = new DocumentType();
$trailModel = new DocumentTrail();
$officeModel = new Office();

$officeId = $_SESSION["office_id"];
$officeName = $_SESSION["office_name"] ?? "My Office";
$userEmail = $_SESSION["email"] ?? "secretary@docuflow.local";
$userFullName = $_SESSION["full_name"] ?? "Secretary";
$userId = $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| DASHBOARD STATS & DOCUMENTS
|--------------------------------------------------------------------------
*/

$allDocs = $documentModel->getDocumentsForOffice($officeId);

$pending = count(array_filter($allDocs, fn($d) => in_array($d["status"], ["Created", "Pending", "Received", "Released", "For Signature", "Rejected"])));
$signed = count(array_filter($allDocs, fn($d) => $d["status"] === "Signed"));
$finished = count(array_filter($allDocs, fn($d) => in_array($d["status"], ["Completed", "Recalled"])));

$stats = [
    "total"    => count($allDocs),
    "pending"  => $pending,
    "signed"   => $signed,
    "finished" => $finished,
];

/*
|--------------------------------------------------------------------------
| DOCUMENT TYPES & MEMBERS
|--------------------------------------------------------------------------
*/

$documentTypes = $docTypeModel->getTypesByOffice($officeId);
$members = $userModel->getMembersByOffice($officeId);

/*
|--------------------------------------------------------------------------
| FORWARDABLE OFFICES
|--------------------------------------------------------------------------
*/

$allOffices = $officeModel->getAllOffices();
$forwardableOffices = array_filter($allOffices, fn($o) => $o["id"] != $officeId);

$user = $userModel->findById((int) $userId);

/*
|--------------------------------------------------------------------------
| AJAX REQUEST HANDLERS
|--------------------------------------------------------------------------
*/

if (isset($_GET["action"])) {

    header("Content-Type: application/json");

    if ($_GET["action"] === "profile") {
        echo json_encode($user);
        exit;
    }

    if ($_GET["action"] === "trail") {

        $docId = $_GET["document_id"] ?? null;

        if ($docId) {
            $trail = $trailModel->getByDocument((int) $docId);

            foreach ($trail as &$t) {
                $t["action_date"] = date("M d, Y h:i A", strtotime($t["created_at"]));
            }

            echo json_encode([
                "success" => true,
                "trail"   => $trail
            ]);
        } else {
            echo json_encode([
                "success" => false,
                "message" => "Missing document ID"
            ]);
        }

    } else {

        echo json_encode([
            "success" => false,
            "message" => "Invalid action"
        ]);

    }

    exit;
}

?>
