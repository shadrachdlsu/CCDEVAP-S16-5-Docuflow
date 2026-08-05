<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin-manage-offices.php');
    exit;
}

$officeId = filter_input(INPUT_POST, 'office_id', FILTER_VALIDATE_INT) ?: 0;
$officeName = trim((string) ($_POST['office_name'] ?? ''));
$secretaryUserId = filter_input(INPUT_POST, 'secretary_user_id', FILTER_VALIDATE_INT) ?: 0;

if ($officeName === '' || mb_strlen($officeName) > 100) {
    returnToOffice($officeId, $officeName, 'Enter an office name containing no more than 100 characters.');
}

require_once __DIR__ . '/../models/office.php';

try {
    (new Office())->saveWithSecretary($officeId, $officeName, $secretaryUserId);
    $_SESSION['admin_office_success'] = $officeId > 0
        ? 'Office updated successfully.'
        : 'Office added successfully.';
} catch (DomainException $exception) {
    returnToOffice($officeId, $officeName, $exception->getMessage());
} catch (PDOException $exception) {
    error_log($exception->getMessage());
    $message = 'The office could not be saved.';

    if ((int) ($exception->errorInfo[1] ?? 0) === 1062) {
        $message = str_contains($exception->getMessage(), 'secretary_user_id')
            ? 'That secretary is already in charge of another office.'
            : 'An office with that name already exists.';
    }

    returnToOffice($officeId, $officeName, $message);
}

$destination = $officeId > 0
    ? '../views/admin-office.php?id=' . $officeId
    : '../views/admin-manage-offices.php';
header('Location: ' . $destination);
exit;

function returnToOffice(int $officeId, string $officeName, string $message): never
{
    $_SESSION['admin_office_error'] = $message;

    if ($officeId > 0) {
        header('Location: ../views/admin-office.php?id=' . $officeId);
    } else {
        $_SESSION['admin_office_name'] = $officeName;
        header('Location: ../views/admin-manage-offices.php');
    }

    exit;
}
