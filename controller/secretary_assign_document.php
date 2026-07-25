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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

$secretaryUserId = (int) $_SESSION['user_id'];
$officeId = (int) ($_SESSION['office_id'] ?? 0);

try {
    $conn->begin_transaction();

    $routeStatement = $conn->prepare(
        'SELECT dr.status
         FROM document_routes AS dr
         INNER JOIN office_secretaries ON office_secretaries.office_id = dr.office_id
         WHERE dr.route_id = ?
           AND dr.office_id = ?
           AND office_secretaries.secretary_user_id = ?
         FOR UPDATE'
    );
    $routeStatement->bind_param('iii', $routeId, $officeId, $secretaryUserId);
    $routeStatement->execute();
    $route = $routeStatement->get_result()->fetch_assoc();
    $routeStatement->close();

    if (!$route) {
        throw new DomainException('This document is not assigned to your office.');
    }

    if (in_array((string) $route['status'], ['Signed', 'Rejected', 'Released', 'Skipped', 'Completed'], true)) {
        throw new DomainException('A completed office route cannot be reassigned.');
    }

    if ($memberUserId > 0) {
        $memberStatement = $conn->prepare(
            "SELECT users.user_id
             FROM users
             INNER JOIN roles ON roles.role_id = users.role_id
             WHERE users.user_id = ?
               AND users.office_id = ?
               AND (
                   roles.role_name = 'Member'
                   OR (roles.role_name = 'Secretary' AND users.user_id = ?)
               )
               AND users.is_active = 1
               AND users.registration_status = 'Approved'
             LIMIT 1"
        );
        $memberStatement->bind_param('iii', $memberUserId, $officeId, $secretaryUserId);
        $memberStatement->execute();
        $validMember = (bool) $memberStatement->get_result()->fetch_assoc();
        $memberStatement->close();

        if (!$validMember) {
            throw new DomainException('Select yourself or an active member from your office.');
        }
    }

    $updateStatement = $conn->prepare('UPDATE document_routes SET signatory_user_id = NULLIF(?, 0) WHERE route_id = ?');
    $updateStatement->bind_param('ii', $memberUserId, $routeId);
    $updateStatement->execute();
    $updateStatement->close();

    $conn->commit();
    $_SESSION['secretary_assignment_success'] = $memberUserId > 0
        ? 'Document assigned successfully.'
        : 'Document assignment removed.';
} catch (DomainException $exception) {
    $conn->rollback();
    $_SESSION['secretary_assignment_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    $conn->rollback();
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
