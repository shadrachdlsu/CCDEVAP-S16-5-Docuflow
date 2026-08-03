<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/office.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["logged_in"]) || $_SESSION["role_id"] != 1) {

    if (isset($_POST["action"])) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Unauthorized access."]);
        exit;
    }

    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$officeModel = new Office();

/*
|--------------------------------------------------------------------------
| ACTION HANDLERS (AJAX)
|--------------------------------------------------------------------------
*/

if (isset($_POST["action"])) {

    header("Content-Type: application/json");

    $action = $_POST["action"];

    try {

        if ($action === "create") {

            $name = trim($_POST["name"] ?? "");
            $code = trim($_POST["code"] ?? "");
            $location = trim($_POST["location"] ?? "");
            $contactEmail = trim($_POST["contact_email"] ?? "");
            $isActive = isset($_POST["is_active"]) ? (int)$_POST["is_active"] : 1;

            if (empty($name)) {
                throw new Exception("Office name is required.");
            }

            $officeModel->createOffice($name, $code, $location, $contactEmail, $isActive);

            echo json_encode(["success" => true, "message" => "Office created successfully."]);
            exit;

        } elseif ($action === "update") {

            $id = (int)($_POST["id"] ?? 0);
            $name = trim($_POST["name"] ?? "");
            $code = trim($_POST["code"] ?? "");
            $location = trim($_POST["location"] ?? "");
            $contactEmail = trim($_POST["contact_email"] ?? "");
            $isActive = isset($_POST["is_active"]) ? (int)$_POST["is_active"] : 1;

            if (empty($id) || empty($name)) {
                throw new Exception("ID and Office name are required.");
            }

            $officeModel->updateOffice($id, $name, $code, $location, $contactEmail, $isActive);

            echo json_encode(["success" => true, "message" => "Office updated successfully."]);
            exit;

        } elseif ($action === "toggle_status") {

            $id = (int)($_POST["id"] ?? 0);
            $isActive = (int)($_POST["is_active"] ?? 1);

            if (empty($id)) {
                throw new Exception("Office ID is required.");
            }

            $officeModel->toggleStatus($id, $isActive);

            echo json_encode(["success" => true, "message" => "Office status updated."]);
            exit;

        } elseif ($action === "assign_secretary") {

            $officeId = (int)($_POST["office_id"] ?? 0);
            $secretaryUserId = (int)($_POST["secretary_user_id"] ?? 0);

            if (empty($officeId) || empty($secretaryUserId)) {
                throw new Exception("Office ID and Secretary Selection are required.");
            }

            $officeModel->assignSecretary($officeId, $secretaryUserId);

            echo json_encode(["success" => true, "message" => "Secretary assigned successfully."]);
            exit;

        } elseif ($action === "check_delete") {

            $id = (int)($_POST["id"] ?? 0);

            if (empty($id)) {
                throw new Exception("Office ID is required.");
            }

            $dependencies = $officeModel->checkDependencies($id);

            echo json_encode(["success" => true, "dependencies" => $dependencies]);
            exit;

        } elseif ($action === "delete") {

            $id = (int)($_POST["id"] ?? 0);

            if (empty($id)) {
                throw new Exception("Office ID is required.");
            }

            $deps = $officeModel->checkDependencies($id);
            if ($deps["user_count"] > 0 || $deps["doc_count"] > 0) {
                throw new Exception("Cannot delete office with active users (" . $deps["user_count"] . ") or active documents (" . $deps["doc_count"] . "). Please reassign or deactivate instead.");
            }

            $officeModel->delete($id);

            echo json_encode(["success" => true, "message" => "Office deleted successfully."]);
            exit;

        } else {

            echo json_encode(["error" => "Invalid action specified."]);
            exit;

        }

    } catch (Exception $e) {

        echo json_encode(["error" => $e->getMessage()]);
        exit;

    }

}

/*
|--------------------------------------------------------------------------
| DATA RETRIEVAL FOR VIEW
|--------------------------------------------------------------------------
*/

$officesDetailed = $officeModel->getAllOfficesDetailed();
$offices = $officeModel->getAllOffices(); // Legacy compatibility
$availableSecretaries = $officeModel->getAvailableSecretaries();
$totalActiveDocs = $officeModel->getTotalActiveDocuments();

$totalOfficesCount = count($officesDetailed);
$activeOfficesCount = count(array_filter($officesDetailed, function ($o) {
    return $o["is_active"] == 1;
}));
$assignedSecretariesCount = count(array_filter($officesDetailed, function ($o) {
    return !empty($o["secretary_id"]);
}));

?>
