<?php
declare(strict_types=1);

session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/login.php');
    exit;
}

$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
    returnToLogin('Please enter a valid email address and password.', $email);
}

require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../helpers/passwords.php';

$userModel = new User();
$user = $userModel->findLoginAccount($email);
$storedPasswordHash = (string) ($user['password_hash'] ?? '');

if (!$user || !docuflow_verify_password($password, $storedPasswordHash)) {
    returnToLogin('Invalid email or password.', $email);
}

if ($user['registration_status'] === 'Pending') {
    returnToLogin('Your registration is pending administrator approval.', $email);
}

if ($user['registration_status'] === 'Rejected') {
    returnToLogin('Your registration was rejected. Please contact an administrator.', $email);
}

if (!(bool) $user['is_active']) {
    returnToLogin('Your account is inactive. Please contact an administrator.', $email);
}

$destinations = [
    'Admin' => '../views/admin-dashboard.php',
    'Secretary' => '../views/secretary-dashboard.php',
    'Member' => '../views/member-dashboard.php',
];

if (!isset($destinations[$user['role_name']])) {
    returnToLogin('Your account does not have a valid role.', $email);
}

if (docuflow_password_needs_rehash($storedPasswordHash)) {
    try {
        $userModel->updatePasswordHash(
            (int) $user['user_id'],
            docuflow_hash_password($password)
        );
    } catch (Throwable $exception) {
        // Do not lock out a user whose valid legacy password could not be
        // upgraded because of a temporary database error.
        error_log('Password hash migration failed: ' . $exception->getMessage());
    }
}

session_regenerate_id(true);
$_SESSION['user_id'] = (int) $user['user_id'];
$_SESSION['full_name'] = $user['full_name'];
$_SESSION['email'] = $user['email'];
$_SESSION['office_id'] = $user['office_id'] === null ? null : (int) $user['office_id'];
$_SESSION['role'] = $user['role_name'];

header('Location: ' . $destinations[$user['role_name']]);
exit;

function returnToLogin(string $message, string $email): never
{
    $_SESSION['login_error'] = $message;
    $_SESSION['login_email'] = $email;
    header('Location: ../views/login.php');
    exit;
}
