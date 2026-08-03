<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json");

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["logged_in"]) || $_SESSION["role_id"] != 1) {
    echo json_encode(["error" => "Unauthorized"]);
    exit;
}

$userModel = new User();

/*
|--------------------------------------------------------------------------
| PROCESS POST ACTION
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $id = isset($_POST["id"]) ? (int) $_POST["id"] : 0;

    if ($id <= 0) {
        echo json_encode(["error" => "Invalid User ID"]);
        exit;
    }

    try {

        if ($action === "approve") {

            $userModel->approveUser($id);

            echo json_encode([
                "success" => true,
                "message" => "User approved successfully"
            ]);

            exit;

        } elseif ($action === "reject") {

            $userModel->delete($id);

            echo json_encode([
                "success" => true,
                "message" => "User registration rejected"
            ]);

            exit;

        } else {

            echo json_encode(["error" => "Invalid action specified"]);
            exit;

        }

    } catch (Exception $e) {

        echo json_encode(["error" => $e->getMessage()]);
        exit;

    }

} else {

    echo json_encode(["error" => "Invalid request method"]);
    exit;

}

?>
