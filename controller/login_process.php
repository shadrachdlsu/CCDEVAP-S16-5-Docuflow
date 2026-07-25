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

require __DIR__ . '/db.php';

$statement = $conn->prepare(
    'SELECT u.user_id, u.full_name, u.email, u.password_hash, u.office_id,
            u.is_active, u.registration_status, r.role_name
     FROM users AS u
     INNER JOIN roles AS r ON r.role_id = u.role_id
     WHERE u.email = ?
     LIMIT 1'
);

if (!$statement) {
    die('Login query failed: ' . $conn->error);
}

$statement->bind_param('s', $email);
$statement->execute();
$user = $statement->get_result()->fetch_assoc();
$statement->close();

if (!$user || !hash_equals(strtolower((string) $user['password_hash']), hash('sha256', $password))) {
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
