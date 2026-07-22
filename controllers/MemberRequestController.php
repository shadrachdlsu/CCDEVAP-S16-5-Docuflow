<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/documentType.php";
require_once __DIR__ . "/../models/documentRequest.php";

function sendResponse(bool $success, string $message): void
{
    echo json_encode([
        "success" => $success,
        "message" => $message
    ]);

    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST")
{
    http_response_code(405);
    sendResponse(false, "Invalid request method.");
}

if (!isset($_SESSION["user_id"]))
{
    http_response_code(401);
    sendResponse(false, "Your session has expired. Please log in again.");
}

$userId = (int) $_SESSION["user_id"];

$title = trim($_POST["title"] ?? "");
$typeId = (int) ($_POST["type_id"] ?? 0);
$description = trim($_POST["description"] ?? "");
$secretaryEmail = trim($_POST["secretary_email"] ?? "");

if ($title === "" || $typeId <= 0)
{
    http_response_code(422);
    sendResponse(false, "Request title and document type are required.");
}

if (
    $secretaryEmail === "" ||
    !filter_var($secretaryEmail, FILTER_VALIDATE_EMAIL)
)
{
    http_response_code(422);
    sendResponse(false, "Enter a valid secretary email.");
}

try
{
    $userModel = new User();
    $docTypeModel = new DocumentType();
    $requestModel = new DocumentRequest();

    /*
    |--------------------------------------------------------------------------
    | GET MEMBER OFFICE
    |--------------------------------------------------------------------------
    */

    $officeId = $userModel->getUserOfficeId($userId);

    if (!$officeId)
    {
        http_response_code(422);
        sendResponse(false, "Your account is not assigned to an office.");
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY SECRETARY
    |--------------------------------------------------------------------------
    */

    $secretaryId = $userModel->findSecretaryByEmail($secretaryEmail);

    if (!$secretaryId)
    {
        http_response_code(422);
        sendResponse(
            false,
            "No active secretary was found with that email."
        );
    }

    /*
    |--------------------------------------------------------------------------
    | VERIFY DOCUMENT TYPE
    |--------------------------------------------------------------------------
    */

    if (!$docTypeModel->typeExists($typeId))
    {
        http_response_code(422);
        sendResponse(false, "The selected document type is invalid.");
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE REQUEST
    |--------------------------------------------------------------------------
    */

    $requestModel->create(
        $userId,
        $officeId,
        $typeId,
        $title,
        $description
    );

    sendResponse(true, "Request submitted successfully.");
}
catch (Exception $e)
{
    error_log("Member request error: " . $e->getMessage());

    http_response_code(500);

    sendResponse(
        false,
        "Database error while submitting the request."
    );
}