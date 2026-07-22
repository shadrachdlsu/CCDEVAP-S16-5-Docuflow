<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/documentRoute.php";
require_once __DIR__ . "/../models/documentTrail.php";
require_once __DIR__ . "/../models/document.php";

/*
|--------------------------------------------------------------------------
| CHECK REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);

    echo json_encode([
        "success" => false,
        "message" => "Your session has expired."
    ]);

    exit;
}

$userId = (int) $_SESSION["user_id"];

/*
|--------------------------------------------------------------------------
| READ JSON DATA
|--------------------------------------------------------------------------
*/

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$documentId = (int) ($data["document_id"] ?? 0);
$remarks = trim($data["remarks"] ?? "");

if ($documentId <= 0) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Invalid document."
    ]);

    exit;
}

try {
    global $pdo;
    $pdo->beginTransaction();

    $routeModel = new DocumentRoute();
    $trailModel = new DocumentTrail();
    $documentModel = new Document();

    /*
    |--------------------------------------------------------------------------
    | SIGN CURRENT USER'S ROUTE
    |--------------------------------------------------------------------------
    */

    if (!$routeModel->signRoute($documentId, $userId, $remarks)) {
        $pdo->rollBack();

        http_response_code(422);

        echo json_encode([
            "success" => false,
            "message" => "This document is already signed or is not assigned to you."
        ]);

        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | ADD PAPER TRAIL
    |--------------------------------------------------------------------------
    */

    $trailModel->addEntry(
        $documentId,
        $userId,
        null,
        null,
        'Signed',
        $remarks !== "" ? $remarks : "Document signed"
    );

    /*
    |--------------------------------------------------------------------------
    | CHECK REMAINING SIGNATURES
    |--------------------------------------------------------------------------
    */

    $remaining = $routeModel->countRemainingUnsigned($documentId);

    if ($remaining === 0) {
        $documentModel->updateStatus($documentId, 'Completed');
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Document signed successfully."
    ]);

} catch (Exception $e) {
    global $pdo;
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "Member sign error: " . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error while signing the document."
    ]);
}