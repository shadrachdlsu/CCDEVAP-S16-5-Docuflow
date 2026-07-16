<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Invalid request method.");
}

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    exit("You are not logged in.");
}

$documentId = (int) ($_POST["document_id"] ?? 0);

if ($documentId <= 0) {
    http_response_code(400);
    exit("Invalid document.");
}

if (!isset($_FILES["signed_file"])) {
    http_response_code(400);
    exit("No PDF file was uploaded.");
}

$file = $_FILES["signed_file"];

if ($file["error"] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    exit("Upload error.");
}

$extension = strtolower(
    pathinfo($file["name"], PATHINFO_EXTENSION)
);

if ($extension !== "pdf") {
    http_response_code(400);
    exit("Only PDF files are allowed.");
}

$uploadFolder =
    __DIR__ . "/../pdfs/";

if (!is_dir($uploadFolder)) {
    mkdir($uploadFolder, 0777, true);
}

$filename =
    time() . "_" .
    preg_replace(
        "/[^a-zA-Z0-9._-]/",
        "_",
        basename($file["name"])
    );

$destination =
    $uploadFolder . $filename;

if (!move_uploaded_file(
    $file["tmp_name"],
    $destination
)) {
    http_response_code(500);
    exit("Unable to save the uploaded file.");
}

$filePath =
    "/CCDEVAP-MP1/pdfs/" . $filename;

$stmt = $pdo->prepare("
    UPDATE documents
    SET
        file_path = ?,
        updated_at = CURRENT_TIMESTAMP
    WHERE document_id = ?
");

$stmt->execute([
    $filePath,
    $documentId
]);

$userId = (int) $_SESSION["user_id"];

$routeStmt = $pdo->prepare("
    UPDATE document_routes
    SET
        status = 'Signed',
        acted_at = NOW(),
        remarks = 'Signed PDF uploaded'
    WHERE document_id = ?
      AND signatory_user_id = ?
      AND status IN (
          'Waiting',
          'Received',
          'For Signature'
      )
");

$routeStmt->execute([
    $documentId,
    $userId
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
        'Signed',
        ?,
        NOW()
    )
");

$trailStmt->execute([
    $documentId,
    $userId,
    "Signed PDF uploaded: " . $filename
]);

$checkStmt = $pdo->prepare("
    SELECT COUNT(*)
    FROM document_routes
    WHERE document_id = ?
      AND status NOT IN (
          'Signed',
          'Skipped',
          'Completed'
      )
");

$checkStmt->execute([
    $documentId
]);

$remainingRoutes =
    (int) $checkStmt->fetchColumn();

if($remainingRoutes === 0)
{
    $documentStmt = $pdo->prepare("
        UPDATE documents
        SET
            status = 'Completed',
            updated_at = NOW()
        WHERE document_id = ?
    ");

    $documentStmt->execute([
        $documentId
    ]);
}

echo "Signed document uploaded successfully.";