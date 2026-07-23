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

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

try {
    $roleStatement = $conn->prepare("SELECT role_id FROM roles WHERE role_name = 'Member' LIMIT 1");
    $roleStatement->execute();
    $memberRole = $roleStatement->get_result()->fetch_assoc();
    $roleStatement->close();

    if (!$memberRole) {
        throw new RuntimeException('The Member role is not configured.');
    }

    $roleId = (int) $memberRole['role_id'];
    $passwordHash = hash('sha256', $password);
    $statement = $conn->prepare(
        "INSERT INTO users
         (role_id, office_id, full_name, email, password_hash, is_active, registration_status)
         VALUES (?, ?, ?, ?, ?, 0, 'Pending')"
    );
    $statement->bind_param('iisss', $roleId, $officeId, $fullName, $email, $passwordHash);
    $statement->execute();
    $statement->close();

    unset(
        $_SESSION['registration_full_name'],
        $_SESSION['registration_email'],
        $_SESSION['registration_office_id']
    );
    $_SESSION['registration_success'] = 'Registration submitted. An administrator must approve your account before you can log in.';
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
