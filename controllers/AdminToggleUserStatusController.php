<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin-manage-users.php');
    exit;
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$isActiveValue = (string) ($_POST['is_active'] ?? '');

if (!$userId || !in_array($isActiveValue, ['0', '1'], true)) {
    $_SESSION['admin_user_error'] = 'Invalid user account status.';
    header('Location: ../views/admin-manage-users.php');
    exit;
}

if ((int) $userId === (int) $_SESSION['user_id'] && $isActiveValue !== '1') {
    $_SESSION['admin_user_error'] = 'Your current Admin account must remain active.';
    header('Location: ../views/admin-manage-users.php');
    exit;
}

require_once __DIR__ . '/../models/user.php';

try {
    $isActive = $isActiveValue === '1';
    (new User())->setActiveFromAdmin((int) $userId, $isActive);
    $_SESSION['admin_user_success'] = $isActive
        ? 'User account activated successfully.'
        : 'User account deactivated successfully.';
} catch (DomainException $exception) {
    $_SESSION['admin_user_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_user_error'] = 'The user account status could not be updated.';
}

header('Location: ../views/admin-manage-users.php');
exit;
