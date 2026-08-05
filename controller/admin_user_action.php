<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

$redirect = '../views/admin-manage-users.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

require_once __DIR__ . '/../helpers/csrf.php';

if (!docuflow_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $_SESSION['admin_user_error'] = 'Your session token expired. Please try again.';
    header('Location: ' . $redirect);
    exit;
}

$action = (string) ($_POST['action'] ?? '');
$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);

if (!$userId) {
    $_SESSION['admin_user_error'] = 'Select a valid user account.';
    header('Location: ' . $redirect);
    exit;
}

require_once __DIR__ . '/../models/user.php';

$userModel = new User();
$targetUser = $userModel->getAdminUser((int) $userId);

if (!$targetUser) {
    $_SESSION['admin_user_error'] = 'The user account could not be found.';
    header('Location: ' . $redirect);
    exit;
}

try {
    if ($action === 'reset_password') {
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (strlen($newPassword) < 8) {
            throw new DomainException('The temporary password must contain at least 8 characters.');
        }

        if (!hash_equals($newPassword, $confirmPassword)) {
            throw new DomainException('The password confirmation does not match.');
        }

        require_once __DIR__ . '/../helpers/passwords.php';
        $userModel->updatePasswordHash(
            (int) $userId,
            docuflow_hash_password($newPassword)
        );
        $_SESSION['admin_user_success'] = 'Password reset successfully for ' . $targetUser['full_name'] . '.';
    } elseif ($action === 'delete') {
        if ((int) $userId === (int) $_SESSION['user_id']) {
            throw new DomainException('You cannot delete your currently signed-in administrator account.');
        }

        $userModel->deleteSafely((int) $userId);
        $_SESSION['admin_user_success'] = 'User account deleted successfully.';
    } else {
        throw new DomainException('The requested user action is invalid.');
    }
} catch (DomainException $exception) {
    $_SESSION['admin_user_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_user_error'] = $action === 'reset_password'
        ? 'The password could not be reset.'
        : 'The user account could not be deleted.';
}

header('Location: ' . $redirect);
exit;
