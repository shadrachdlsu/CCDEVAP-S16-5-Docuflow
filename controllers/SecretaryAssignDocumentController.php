<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Secretary') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/secretary-assign-documents.php');
    exit;
}

$routeId = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
$memberUserId = filter_input(INPUT_POST, 'member_user_id', FILTER_VALIDATE_INT) ?: 0;

if (!$routeId) {
    returnToAssignment(0, 'Invalid document assignment.');
}

require_once __DIR__ . '/../models/documentRoute.php';

$secretaryUserId = (int) $_SESSION['user_id'];
$officeId = (int) ($_SESSION['office_id'] ?? 0);

try {
    (new DocumentRoute())->assignForSecretary(
        (int) $routeId,
        $officeId,
        $secretaryUserId,
        $memberUserId
    );
    $_SESSION['secretary_assignment_success'] = $memberUserId > 0
        ? 'Document assigned successfully.'
        : 'Document assignment removed.';
} catch (DomainException $exception) {
    $_SESSION['secretary_assignment_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['secretary_assignment_error'] = 'The document assignment could not be saved.';
}

header('Location: ../views/secretary-assign-document.php?id=' . $routeId);
exit;

function returnToAssignment(int $routeId, string $message): never
{
    $_SESSION['secretary_assignment_error'] = $message;
    $destination = $routeId > 0
        ? '../views/secretary-assign-document.php?id=' . $routeId
        : '../views/secretary-assign-documents.php';
    header('Location: ' . $destination);
    exit;
}
