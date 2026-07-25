<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin-manage-document-types.php');
    exit;
}

$typeName = trim((string) ($_POST['type_name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

$_SESSION['admin_document_type_name'] = $typeName;
$_SESSION['admin_document_type_description'] = $description;

if ($typeName === '' || mb_strlen($typeName) > 50) {
    returnToDocumentTypes('Enter a document type name containing no more than 50 characters.');
}

if (mb_strlen($description) > 1000) {
    returnToDocumentTypes('The description must not exceed 1000 characters.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

try {
    $statement = $conn->prepare(
        "INSERT INTO document_types (type_name, description, is_active)
         VALUES (?, NULLIF(?, ''), 1)"
    );
    $statement->bind_param('ss', $typeName, $description);
    $statement->execute();
    $statement->close();

    unset($_SESSION['admin_document_type_name'], $_SESSION['admin_document_type_description']);
    $_SESSION['admin_document_type_success'] = 'Document type added successfully.';
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    $message = $exception->getCode() === 1062
        ? 'A document type with that name already exists.'
        : 'The document type could not be added.';
    returnToDocumentTypes($message);
}

header('Location: ../views/admin-manage-document-types.php');
exit;

function returnToDocumentTypes(string $message): never
{
    $_SESSION['admin_document_type_error'] = $message;
    header('Location: ../views/admin-manage-document-types.php');
    exit;
}

