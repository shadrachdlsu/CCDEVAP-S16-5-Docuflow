<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/register.php');
    exit;
}

$fullName = trim((string) ($_POST['full_name'] ?? ''));
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');
$confirmPassword = (string) ($_POST['confirm_password'] ?? '');
$officeId = filter_input(INPUT_POST, 'office_id', FILTER_VALIDATE_INT);

$_SESSION['registration_full_name'] = $fullName;
$_SESSION['registration_email'] = $email;
$_SESSION['registration_office_id'] = $officeId ?: 0;

if ($fullName === '' || mb_strlen($fullName) > 100) {
    returnToRegistration('Enter a valid full name.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 100) {
    returnToRegistration('Enter a valid email address.');
}

if (strlen($password) < 8) {
    returnToRegistration('Your password must be at least 8 characters long.');
}

if (!hash_equals($password, $confirmPassword)) {
    returnToRegistration('The passwords do not match.');
}

if (!$officeId) {
    returnToRegistration('Select your office.');
}

require_once __DIR__ . '/../models/role.php';
require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../helpers/passwords.php';

try {
    $roleId = (new Role())->findIdByName('Member');

    if ($roleId === null) {
        throw new RuntimeException('The Member role is not configured.');
    }

    $passwordHash = docuflow_hash_password($password);
    $requiresAdminApproval = (new Setting())->requiresAdminApproval();
    $isActive = $requiresAdminApproval ? 0 : 1;
    $registrationStatus = $requiresAdminApproval ? 'Pending' : 'Approved';

    (new User())->create(
        $roleId,
        (int) $officeId,
        $fullName,
        $email,
        $passwordHash,
        $isActive,
        $registrationStatus
    );

    unset(
        $_SESSION['registration_full_name'],
        $_SESSION['registration_email'],
        $_SESSION['registration_office_id']
    );
    $_SESSION['registration_success'] = $requiresAdminApproval
        ? 'Registration submitted. An administrator must approve your account before you can log in.'
        : 'Registration complete. Your account is active and you can now log in.';
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['registration_error'] = 'Registration failed. That email may already be registered.';
}

header('Location: ../views/register.php');
exit;

function returnToRegistration(string $message): never
{
    $_SESSION['registration_error'] = $message;
    header('Location: ../views/register.php');
    exit;
}
