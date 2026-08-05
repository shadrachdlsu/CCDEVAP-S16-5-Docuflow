<?php
declare(strict_types=1);

session_start();

$role = (string) ($_SESSION['role'] ?? '');

if (!isset($_SESSION['user_id']) || !in_array($role, ['Admin', 'Secretary'], true)) {
    header('Location: ../views/login.php');
    exit;
}

$redirectPage = '../views/admin-manage-document-types.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirectPage);
    exit;
}

$typeName = trim((string) ($_POST['type_name'] ?? ''));
$description = trim((string) ($_POST['description'] ?? ''));

$_SESSION['admin_document_type_name'] = $typeName;
$_SESSION['admin_document_type_description'] = $description;

if ($typeName === '' || mb_strlen($typeName) > 50) {
    returnToDocumentTypes('Enter a document type name containing no more than 50 characters.', $redirectPage);
}

if (mb_strlen($description) > 1000) {
    returnToDocumentTypes('The description must not exceed 1000 characters.', $redirectPage);
}

require_once __DIR__ . '/../models/documentType.php';

try {
    $documentTypeModel = new DocumentType();

    if ($role === 'Secretary') {
        $officeId = (int) ($_SESSION['office_id'] ?? 0);

        if ($officeId <= 0) {
            returnToDocumentTypes('Your account must be assigned to an office before adding document types.', $redirectPage);
        }

        $documentTypeModel->createActiveForOffice($typeName, $description, $officeId);
    } else {
        $documentTypeModel->createActive($typeName, $description);
    }

    unset($_SESSION['admin_document_type_name'], $_SESSION['admin_document_type_description']);
    $_SESSION['admin_document_type_success'] = 'Document type added successfully.';
} catch (DomainException $exception) {
    returnToDocumentTypes($exception->getMessage(), $redirectPage);
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $message = (int) ($exception->errorInfo[1] ?? 0) === 1062
        ? 'A document type with that name already exists.'
        : 'The document type could not be added.';
    returnToDocumentTypes($message, $redirectPage);
}

header('Location: ' . $redirectPage);
exit;

function returnToDocumentTypes(string $message, string $redirectPage): never
{
    $_SESSION['admin_document_type_error'] = $message;
    header('Location: ' . $redirectPage);
    exit;
}
