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
$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$roleId = filter_input(INPUT_POST, 'role_id', FILTER_VALIDATE_INT);
$officeId = filter_input(INPUT_POST, 'office_id', FILTER_VALIDATE_INT);
$accountStatus = (string) ($_POST['account_status'] ?? '');

if (!$userId || $fullName === '' || mb_strlen($fullName) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || !$roleId || !in_array($accountStatus, ['Pending', 'Active', 'Inactive', 'Rejected'], true)) {
    returnToUser($userId ?: 0, 'Complete all user fields with valid values.');
}

if ($userId === (int) $_SESSION['user_id'] && $accountStatus !== 'Active') {
    returnToUser($userId, 'Your current Admin account must remain active.');
}

require_once __DIR__ . '/../models/role.php';
require_once __DIR__ . '/../models/user.php';

try {
    $selectedRoleName = (new Role())->findNameById((int) $roleId);

    if ($selectedRoleName === null) {
        returnToUser($userId, 'The selected role could not be found.');
    }

    if ($userId === (int) $_SESSION['user_id']) {
        if ($selectedRoleName !== 'Admin') {
            returnToUser($userId, 'You cannot remove the Admin role from your current account.');
        }
    }

    (new User())->updateFromAdmin(
        (int) $userId,
        $fullName,
        $email,
        (int) $roleId,
        $officeId ? (int) $officeId : null,
        $accountStatus
    );

    if ($userId === (int) $_SESSION['user_id']) {
        $_SESSION['full_name'] = $fullName;
        $_SESSION['email'] = $email;
    }

    $_SESSION['admin_user_success'] = 'User details updated successfully.';
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_user_error'] = 'The user could not be updated. The email may already be in use.';
}

header('Location: ../views/admin-user.php?id=' . $userId);
exit;

function returnToUser(int $userId, string $message): never
{
    $_SESSION['admin_user_error'] = $message;
    $destination = $userId > 0
        ? '../views/admin-user.php?id=' . $userId
        : '../views/admin-manage-users.php';
    header('Location: ' . $destination);
    exit;
}
