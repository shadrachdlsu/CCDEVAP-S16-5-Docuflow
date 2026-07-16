<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/connections.php";

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
    $pdo->beginTransaction();

    /*
    |--------------------------------------------------------------------------
    | SIGN CURRENT USER'S ROUTE
    |--------------------------------------------------------------------------
    */

    $stmt = $pdo->prepare("
        UPDATE document_routes
        SET
            status = 'Signed',
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
        $remarks,
        $documentId,
        $userId
    ]);

    if ($stmt->rowCount() === 0) {
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
            'Signed',
            ?,
            NOW()
        )
    ");

    $trailStmt->execute([
        $documentId,
        $userId,
        $remarks !== "" ? $remarks : "Document signed"
    ]);

    /*
    |--------------------------------------------------------------------------
    | CHECK REMAINING SIGNATURES
    |--------------------------------------------------------------------------
    */

    $check = $pdo->prepare("
        SELECT COUNT(*)
        FROM document_routes
        WHERE document_id = ?
          AND status NOT IN (
              'Signed',
              'Completed',
              'Skipped'
          )
    ");

    $check->execute([$documentId]);

    $remaining = (int) $check->fetchColumn();

    if ($remaining === 0) {
        $update = $pdo->prepare("
            UPDATE documents
            SET
                status = 'Completed',
                updated_at = NOW()
            WHERE document_id = ?
        ");

        $update->execute([$documentId]);
    }

    $pdo->commit();

    echo json_encode([
        "success" => true,
        "message" => "Document signed successfully."
    ]);

} catch (PDOException $e) {
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