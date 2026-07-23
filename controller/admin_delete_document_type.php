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

$typeId = filter_input(INPUT_POST, 'type_id', FILTER_VALIDATE_INT);
$isActiveValue = (string) ($_POST['is_active'] ?? '');

if (!$typeId || !in_array($isActiveValue, ['0', '1'], true)) {
    $_SESSION['admin_document_type_error'] = 'Invalid document type status.';
    header('Location: ../views/admin-manage-document-types.php');
    exit;
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

try {
    $conn->begin_transaction();

    $typeStatement = $conn->prepare(
        'SELECT type_name, is_active FROM document_types WHERE type_id = ? FOR UPDATE'
    );
    $typeStatement->bind_param('i', $typeId);
    $typeStatement->execute();
    $documentType = $typeStatement->get_result()->fetch_assoc();
    $typeStatement->close();

    if (!$documentType) {
        throw new DomainException('The document type could not be found.');
    }

    $isActive = (int) $isActiveValue;
    $updateStatement = $conn->prepare('UPDATE document_types SET is_active = ? WHERE type_id = ?');
    $updateStatement->bind_param('ii', $isActive, $typeId);
    $updateStatement->execute();
    $updateStatement->close();

    $conn->commit();
    $_SESSION['admin_document_type_success'] = $isActive === 1
        ? 'Document type activated successfully.'
        : 'Document type deactivated successfully.';
} catch (DomainException $exception) {
    $conn->rollback();
    $_SESSION['admin_document_type_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    $conn->rollback();
    error_log($exception->getMessage());
    $_SESSION['admin_document_type_error'] = 'The document type status could not be updated.';
}

header('Location: ../views/admin-manage-document-types.php');
exit;
