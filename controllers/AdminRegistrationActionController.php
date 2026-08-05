<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

$redirect = '../views/admin-dashboard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . $redirect);
    exit;
}

$userId = filter_input(INPUT_POST, 'user_id', FILTER_VALIDATE_INT);
$decision = (string) ($_POST['decision'] ?? '');

try {
    if (!$userId || !in_array($decision, ['approve', 'reject'], true)) {
        throw new DomainException('Select a valid pending registration action.');
    }

    require_once __DIR__ . '/../models/user.php';
    (new User())->decideRegistration((int) $userId, $decision);
    $_SESSION['admin_dashboard_success'] = $decision === 'approve'
        ? 'Registration approved. The user can now sign in.'
        : 'Registration rejected.';
} catch (DomainException | InvalidArgumentException $exception) {
    $_SESSION['admin_dashboard_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_dashboard_error'] = 'The registration could not be updated.';
}

header('Location: ' . $redirect);
exit;
