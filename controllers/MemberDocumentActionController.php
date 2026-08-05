<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !in_array((string) ($_SESSION['role'] ?? ''), ['Member', 'Secretary'], true)) {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/member-documents.php');
    exit;
}

$routeId = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
$documentId = filter_input(INPUT_POST, 'document_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
$remarks = trim((string) ($_POST['remarks'] ?? ''));

if (!$routeId || !$documentId || !in_array($action, ['receive', 'sign', 'reject'], true)) {
    returnToDocument($documentId ?: 0, 'Invalid document action.');
}

if (in_array($action, ['sign', 'reject'], true) && mb_strlen($remarks) > 1000) {
    returnToDocument($documentId, 'Remarks must not exceed 1000 characters.');
}

require_once __DIR__ . '/../models/documentRoute.php';

$userId = (int) $_SESSION['user_id'];

try {
    $message = (new DocumentRoute())->performUserAction(
        (int) $routeId,
        (int) $documentId,
        $userId,
        $action,
        $remarks
    );
    $_SESSION['document_action_success'] = $message;
} catch (DomainException $exception) {
    $_SESSION['document_action_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['document_action_error'] = 'The document action could not be completed.';
}

header('Location: ../views/member-document.php?id=' . $documentId);
exit;

function returnToDocument(int $documentId, string $message): never
{
    $_SESSION['document_action_error'] = $message;
    $destination = $documentId > 0
        ? '../views/member-document.php?id=' . $documentId
        : '../views/member-documents.php';
    header('Location: ' . $destination);
    exit;
}
