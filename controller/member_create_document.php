<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !in_array((string) ($_SESSION['role'] ?? ''), ['Member', 'Secretary'], true)) {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/member-create-document.php');
    exit;
}

$title = trim((string) ($_POST['title'] ?? ''));
$typeId = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
$routingMode = (string) ($_POST['routing_mode'] ?? '');
$submittedOfficeIds = array_values(array_filter(
    array_map('intval', (array) ($_POST['office_ids'] ?? [])),
    static fn (int $officeId): bool => $officeId > 0
));
$officeIds = array_values(array_unique($submittedOfficeIds));
$_SESSION['document_title'] = $title;
$_SESSION['document_routing_mode'] = $routingMode;

if (
    $title === ''
    || mb_strlen($title) > 255
    || !$typeId
    || $officeIds === []
    || !in_array($routingMode, ['sequential', 'simultaneous'], true)
) {
    returnToCreateForm('Complete all document fields before submitting.');
}

if (count($submittedOfficeIds) !== count($officeIds)) {
    returnToCreateForm('Each office can only appear once in the document route.');
}

if (count($officeIds) > 5) {
    returnToCreateForm('A document route can contain a maximum of 5 offices.');
}

$file = $_FILES['document_file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    returnToCreateForm('Select a PDF file to upload.');
}

if ((int) $file['size'] > 10 * 1024 * 1024) {
    returnToCreateForm('The PDF must not exceed 10 MB.');
}

$mimeType = (new finfo(FILEINFO_MIME_TYPE))->file((string) $file['tmp_name']);

if ($mimeType !== 'application/pdf') {
    returnToCreateForm('Only valid PDF files can be uploaded.');
}

$uploadDirectory = dirname(__DIR__) . '/uploads';
$fileName = bin2hex(random_bytes(16)) . '.pdf';
$absoluteFilePath = $uploadDirectory . '/' . $fileName;

if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true)) {
    returnToCreateForm('The upload folder could not be created.');
}

if (!move_uploaded_file((string) $file['tmp_name'], $absoluteFilePath)) {
    returnToCreateForm('The document could not be uploaded.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

$trackingCode = 'DOC-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(4)));
$projectFolder = rawurlencode(basename(dirname(__DIR__)));
$storedFilePath = '/' . $projectFolder . '/uploads/' . rawurlencode($fileName);
$creatorId = (int) $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    $documentStatement = $conn->prepare(
        "INSERT INTO documents
            (tracking_code, title, file_path, type_id, creator_id, current_office_id, status)
         VALUES (?, ?, ?, ?, ?, NULL, 'Pending')"
    );
    $documentStatement->bind_param(
        'sssii',
        $trackingCode,
        $title,
        $storedFilePath,
        $typeId,
        $creatorId
    );
    $documentStatement->execute();
    $documentId = $conn->insert_id;
    $documentStatement->close();

    $routeStatement = $conn->prepare(
        "INSERT INTO document_routes
            (document_id, step_no, office_id, signatory_user_id, status)
         VALUES (?, ?, ?, NULL, 'Waiting')"
    );

    foreach ($officeIds as $index => $officeId) {
        $stepNumber = $routingMode === 'sequential' ? $index + 1 : 0;
        $routeStatement->bind_param('iii', $documentId, $stepNumber, $officeId);
        $routeStatement->execute();
    }

    $routeStatement->close();

    $conn->commit();
} catch (Throwable $exception) {
    $conn->rollback();
    unlink($absoluteFilePath);
    error_log($exception->getMessage());
    returnToCreateForm('The document could not be created. Please try again.');
}

unset($_SESSION['document_title'], $_SESSION['document_routing_mode']);
$sendDescription = $routingMode === 'sequential' ? 'sequentially' : 'simultaneously';
$_SESSION['document_success'] = "Document {$trackingCode} was created and sent {$sendDescription}.";
header('Location: ../views/member-create-document.php');
exit;

function returnToCreateForm(string $message): never
{
    $_SESSION['document_error'] = $message;
    header('Location: ../views/member-create-document.php');
    exit;
}
