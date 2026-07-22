<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/documentRequest.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request method.");
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    exit("You are not logged in.");
}

$requestId =
    (int) ($_POST["request_id"] ?? 0);

$userId =
    (int) $_SESSION["user_id"];

if ($requestId <= 0) {
    http_response_code(400);
    exit("Invalid request.");
}

/*
Only allow the logged-in member to delete
their own request while it is still Pending.
*/

$requestModel = new DocumentRequest();

if (!$requestModel->deletePending($requestId, $userId)) {
    http_response_code(400);
    exit(
        "Request was not found or can no longer be deleted."
    );
}

echo "Request deleted successfully.";