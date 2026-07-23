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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

try {
    $roleStatement = $conn->prepare('SELECT role_name FROM roles WHERE role_id = ? LIMIT 1');
    $roleStatement->bind_param('i', $roleId);
    $roleStatement->execute();
    $selectedRole = $roleStatement->get_result()->fetch_assoc();
    $roleStatement->close();

    if (!$selectedRole) {
        returnToUser($userId, 'The selected role could not be found.');
    }

    if ($userId === (int) $_SESSION['user_id']) {
        if ($selectedRole['role_name'] !== 'Admin') {
            returnToUser($userId, 'You cannot remove the Admin role from your current account.');
        }
    }

    $officeValue = $officeId ?: 0;
    $isActive = $accountStatus === 'Active' ? 1 : 0;
    $registrationStatus = match ($accountStatus) {
        'Pending' => 'Pending',
        'Rejected' => 'Rejected',
        default => 'Approved',
    };
    $statement = $conn->prepare(
        'UPDATE users
         SET full_name = ?, email = ?, role_id = ?, office_id = NULLIF(?, 0),
             is_active = ?, registration_status = ?
         WHERE user_id = ?'
    );
    $statement->bind_param('ssiiisi', $fullName, $email, $roleId, $officeValue, $isActive, $registrationStatus, $userId);
    $statement->execute();
    $statement->close();

    $secretaryAssignmentIsValid = $selectedRole['role_name'] === 'Secretary'
        && $officeValue > 0
        && $accountStatus === 'Active';
    $clearOfficeStatement = $secretaryAssignmentIsValid
        ? $conn->prepare('DELETE FROM office_secretaries WHERE secretary_user_id = ? AND office_id <> ?')
        : $conn->prepare('DELETE FROM office_secretaries WHERE secretary_user_id = ?');

    if ($secretaryAssignmentIsValid) {
        $clearOfficeStatement->bind_param('ii', $userId, $officeValue);
    } else {
        $clearOfficeStatement->bind_param('i', $userId);
    }

    $clearOfficeStatement->execute();
    $clearOfficeStatement->close();

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
