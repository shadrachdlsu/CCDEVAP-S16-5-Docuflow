<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

$redirect = '../views/admin-manage-offices.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$officeId = filter_input(INPUT_POST, 'office_id', FILTER_VALIDATE_INT);

if (!$officeId) {
    $_SESSION['admin_office_error'] = 'Select a valid office.';
    header('Location: ' . $redirect);
    exit;
}

require_once __DIR__ . '/../models/office.php';

try {
    $officeModel = new Office();

    if ($action === 'toggle_status') {
        $isActiveValue = (string) ($_POST['is_active'] ?? '');

        if (!in_array($isActiveValue, ['0', '1'], true)) {
            throw new DomainException('Select a valid office status.');
        }

        $isActive = $isActiveValue === '1';
        $officeModel->setActive((int) $officeId, $isActive);
        $_SESSION['admin_office_success'] = $isActive
            ? 'Office activated successfully.'
            : 'Office deactivated successfully.';
    } elseif ($action === 'delete') {
        $officeModel->deleteSafely((int) $officeId);
        $_SESSION['admin_office_success'] = 'Office deleted successfully.';
    } else {
        throw new DomainException('The requested office action is invalid.');
    }
} catch (DomainException $exception) {
    $_SESSION['admin_office_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_office_error'] = $action === 'delete'
        ? 'The office could not be deleted.'
        : 'The office status could not be updated.';
}

header('Location: ' . $redirect);
exit;
