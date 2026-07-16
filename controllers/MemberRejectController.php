<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/connections.php";

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
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        UPDATE document_routes
        SET
            status = 'Rejected',
            remarks = ?,
            acted_at = NOW()
        WHERE document_id = ?
          AND signatory_user_id = ?
          AND status IN (
              'Waiting',
              'Pending',
              'Received',
              'For Signature'
          )
    ");

    $stmt->execute([
        $reason,
        $documentId,
        $userId
    ]);

    if ($stmt->rowCount() === 0) {
        $pdo->rollBack();

        http_response_code(422);

        echo json_encode([
            "success" => false,
            "message" =>
                "This document is already processed or is not assigned to you."
        ]);

        exit;
    }

    $updateDocument = $pdo->prepare("
        UPDATE documents
        SET
            status = 'Rejected',
            updated_at = NOW()
        WHERE document_id = ?
    ");

    $updateDocument->execute([
        $documentId
    ]);

    $trailStmt = $pdo->prepare("
        INSERT INTO document_trails
        (
            document_id,
            action_by_user_id,
            action_taken,
            remarks,
            created_at
        )
        VALUES
        (
            ?,
            ?,
            'Rejected',
            ?,
            NOW()
        )
    ");

    $trailStmt->execute([
        $documentId,
        $userId,
        $reason
    ]);

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Document rejected successfully."
    ]);

} catch (PDOException $e) {
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