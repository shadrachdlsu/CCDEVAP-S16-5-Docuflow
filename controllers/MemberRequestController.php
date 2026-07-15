<?php

session_start();

header("Content-Type: application/json");

require_once __DIR__ . "/../config/connections.php";

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
    /*
    |--------------------------------------------------------------------------
    | GET MEMBER OFFICE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        SELECT office_id
        FROM users
        WHERE user_id = ?
          AND is_active = 1
          AND registration_status = 'Approved'
        LIMIT 1
    ");

    $stmt->execute([$userId]);

    $officeId = $stmt->fetchColumn();

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

    $stmt = $pdo->prepare("
        SELECT u.user_id
        FROM users u
        INNER JOIN roles r
            ON u.role_id = r.role_id
        WHERE u.email = ?
          AND r.role_name = 'Secretary'
          AND u.is_active = 1
          AND u.registration_status = 'Approved'
        LIMIT 1
    ");

    $stmt->execute([$secretaryEmail]);

    $secretaryId = $stmt->fetchColumn();

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

    $stmt = $pdo->prepare("
        SELECT type_id
        FROM document_types
        WHERE type_id = ?
          AND is_active = 1
        LIMIT 1
    ");

    $stmt->execute([$typeId]);

    if (!$stmt->fetchColumn())
    {
        http_response_code(422);
        sendResponse(false, "The selected document type is invalid.");
    }

    /*
    |--------------------------------------------------------------------------
    | CREATE REQUEST
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        INSERT INTO document_requests
        (
            requested_by_id,
            office_id,
            type_id,
            title,
            description,
            status,
            created_at
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            'Pending',
            NOW()
        )
    ");

    $stmt->execute([
        $userId,
        $officeId,
        $typeId,
        $title,
        $description
    ]);

    sendResponse(true, "Request submitted successfully.");
}
catch (PDOException $e)
{
    error_log("Member request error: " . $e->getMessage());

    http_response_code(500);

    sendResponse(
        false,
        "Database error while submitting the request."
    );
}