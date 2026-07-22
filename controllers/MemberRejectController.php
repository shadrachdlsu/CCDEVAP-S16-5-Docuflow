<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/documentRoute.php";
require_once __DIR__ . "/../models/document.php";
require_once __DIR__ . "/../models/documentTrail.php";

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

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$documentId = (int) ($data["document_id"] ?? 0);
$reason = trim($data["reason"] ?? "");

if ($documentId <= 0) {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Invalid document."
    ]);

    exit;
}

if ($reason === "") {
    http_response_code(422);

    echo json_encode([
        "success" => false,
        "message" => "Please provide a rejection reason."
    ]);

    exit;
}

try {
    global $pdo;
    $pdo->beginTransaction();

    $routeModel = new DocumentRoute();
    $documentModel = new Document();
    $trailModel = new DocumentTrail();

    if (!$routeModel->rejectRoute($documentId, $userId, $reason)) {
        $pdo->rollBack();

        http_response_code(422);

        echo json_encode([
            "success" => false,
            "message" => "This document is already processed or is not assigned to you."
        ]);

        exit;
    }

    $documentModel->updateStatus($documentId, 'Rejected');

    $trailModel->addEntry(
        $documentId,
        $userId,
        null,
        null,
        'Rejected',
        $reason
    );

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Document rejected successfully."
    ]);

} catch (Exception $e) {
    global $pdo;
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log(
        "Member reject error: " . $e->getMessage()
    );

    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Database error while rejecting the document."
    ]);
}