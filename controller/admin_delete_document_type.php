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

$typeId = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
$isActiveValue = (string) ($_POST['is_active'] ?? '');

if (!$typeId || !in_array($isActiveValue, ['0', '1'], true)) {
    $_SESSION['admin_document_type_error'] = 'Invalid document type status.';
    header('Location: ' . $redirectPage);
    exit;
}

require_once __DIR__ . '/../models/documentType.php';

try {
    $isActive = (int) $isActiveValue;
    $documentTypeModel = new DocumentType();

    if ($role === 'Secretary') {
        $officeId = (int) ($_SESSION['office_id'] ?? 0);

        if ($officeId <= 0) {
            throw new DomainException('Your account must be assigned to an office before managing document types.');
        }

        $documentTypeModel->setActiveForOffice((int) $typeId, $officeId, $isActive === 1);
    } else {
        $documentTypeModel->setActive((int) $typeId, $isActive === 1);
    }

    $_SESSION['admin_document_type_success'] = $isActive === 1
        ? 'Document type activated successfully.'
        : 'Document type deactivated successfully.';
} catch (DomainException $exception) {
    $_SESSION['admin_document_type_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_document_type_error'] = 'The document type status could not be updated.';
}

header('Location: ' . $redirectPage);
exit;
