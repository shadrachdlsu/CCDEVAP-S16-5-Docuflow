<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/document.php";
require_once __DIR__ . "/../models/documentRoute.php";
require_once __DIR__ . "/../models/documentTrail.php";

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

try {
    global $pdo;
    $pdo->beginTransaction();

    $documentModel = new Document();
    $routeModel = new DocumentRoute();
    $trailModel = new DocumentTrail();

    $documentModel->updateFilePath($documentId, $filePath);

    $userId = (int) $_SESSION["user_id"];

    $routeModel->signRoute($documentId, $userId, 'Signed PDF uploaded');

    $trailModel->addEntry(
        $documentId,
        $userId,
        null,
        null,
        'Signed',
        "Signed PDF uploaded: " . $filename
    );

    $remainingRoutes = $routeModel->countRemainingUnsigned($documentId);

    if($remainingRoutes === 0)
    {
        $documentModel->updateStatus($documentId, 'Completed');
    }

    $pdo->commit();

    echo "Signed document uploaded successfully.";
} catch (Exception $e) {
    global $pdo;
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    exit("Database error while processing the upload.");
}