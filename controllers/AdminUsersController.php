<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/office.php";
require_once __DIR__ . "/../models/role.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["logged_in"]) || $_SESSION["role_id"] != 1) {

    if (isset($_POST["action"])) {
        header("Content-Type: application/json");
        echo json_encode(["error" => "Unauthorized"]);
        exit;
    }

    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$userModel = new User();
$officeModel = new Office();
$roleModel = new Role();

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

            $name = $_POST["name"] ?? "";
            $email = $_POST["email"] ?? "";
            $password = $_POST["password"] ?? "";
            $role_id = $_POST["role_id"] ?? 0;
            $office_id = $_POST["office_id"] ?? null;
            $status = $_POST["status"] ?? "Active";

            if (empty($name) || empty($email) || empty($password) || empty($role_id)) {
                throw new Exception("Name, Email, Password, and Role are required.");
            }

            $is_active = ($status === "Active") ? 1 : 0;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            if (empty($office_id)) {
                $office_id = null;
            }

            $userModel->create(
                $role_id,
                $office_id,
                $name,
                $email,
                $password_hash,
                $is_active,
                "Approved"
            );

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "update") {

            $id = $_POST["id"] ?? 0;
            $name = $_POST["name"] ?? "";
            $email = $_POST["email"] ?? "";
            $password = $_POST["password"] ?? "";
            $role_id = $_POST["role_id"] ?? 0;
            $office_id = $_POST["office_id"] ?? null;
            $status = $_POST["status"] ?? "Active";

            if (empty($id) || empty($name) || empty($email) || empty($role_id)) {
                throw new Exception("ID, Name, Email, and Role are required.");
            }

            $is_active = ($status === "Active") ? 1 : 0;

            if (empty($office_id)) {
                $office_id = null;
            }

            $password_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;

            $userModel->update(
                $id,
                $role_id,
                $office_id,
                $name,
                $email,
                $password_hash,
                $is_active
            );

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "delete") {

            $id = $_POST["id"] ?? 0;

            if (empty($id)) {
                throw new Exception("ID is required.");
            }

            $userModel->delete($id);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "approve") {

            $id = $_POST["id"] ?? 0;

            if (empty($id)) {
                throw new Exception("ID is required.");
            }

            $userModel->approveUser($id);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "deactivate") {

            $id = $_POST["id"] ?? 0;

            if (empty($id)) {
                throw new Exception("ID is required.");
            }

            $userModel->deactivateUser($id);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "check_workflows") {

            $id = (int) ($_POST["id"] ?? 0);

            if (empty($id)) {
                throw new Exception("ID is required.");
            }

            $counts = $userModel->getActiveWorkflowsCount($id);

            echo json_encode(["success" => true, "workflows" => $counts]);
            exit;

        } elseif ($action === "handover_and_deactivate") {

            $from_id = (int) ($_POST["from_id"] ?? 0);
            $to_id = (int) ($_POST["to_id"] ?? 0);

            if (empty($from_id) || empty($to_id)) {
                throw new Exception("Target replacement user is required.");
            }

            $userModel->reassignUserWorkflows($from_id, $to_id);
            $userModel->deactivateUser($from_id);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "handover_and_delete") {

            $from_id = (int) ($_POST["from_id"] ?? 0);
            $to_id = (int) ($_POST["to_id"] ?? 0);

            if (empty($from_id) || empty($to_id)) {
                throw new Exception("Target replacement user is required.");
            }

            $userModel->reassignUserWorkflows($from_id, $to_id);
            $userModel->delete($from_id);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "reset_password") {

            $id = (int) ($_POST["id"] ?? 0);
            $password = $_POST["password"] ?? "";

            if (empty($id) || empty($password)) {
                throw new Exception("User ID and Password are required.");
            }

            $userModel->resetPassword($id, $password);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "get_user_activity") {

            $id = (int) ($_POST["id"] ?? 0);

            if (empty($id)) {
                throw new Exception("User ID is required.");
            }

            $data = $userModel->getUserProfileAndActivity($id);

            if (!$data) {
                throw new Exception("User not found.");
            }

            echo json_encode(["success" => true, "data" => $data]);
            exit;

        } elseif ($action === "bulk_approve") {

            $ids = isset($_POST["ids"]) ? array_map("intval", $_POST["ids"]) : [];

            if (empty($ids)) {
                throw new Exception("No users selected.");
            }

            $userModel->bulkApprove($ids);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "bulk_status") {

            $ids = isset($_POST["ids"]) ? array_map("intval", $_POST["ids"]) : [];
            $status = $_POST["status"] ?? "Active";

            if (empty($ids)) {
                throw new Exception("No users selected.");
            }

            $userModel->bulkUpdateStatus($ids, $status);

            echo json_encode(["success" => true]);
            exit;

        } elseif ($action === "bulk_office") {

            $ids = isset($_POST["ids"]) ? array_map("intval", $_POST["ids"]) : [];
            $office_id = !empty($_POST["office_id"]) ? (int) $_POST["office_id"] : null;

            if (empty($ids)) {
                throw new Exception("No users selected.");
            }

            $userModel->bulkReassignOffice($ids, $office_id);

            echo json_encode(["success" => true]);
            exit;

        } else {

            echo json_encode(["error" => "Invalid action"]);
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

$users = $userModel->getAllWithRolesAndOffices();
$roles = $roleModel->getAll();
$offices = $officeModel->getAllOffices();

?>
