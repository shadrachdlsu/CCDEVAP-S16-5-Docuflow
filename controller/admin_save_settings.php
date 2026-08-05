<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin-settings.php');
    exit;
}

require_once __DIR__ . '/../helpers/csrf.php';

if (!docuflow_verify_csrf_token($_POST['csrf_token'] ?? null)) {
    $_SESSION['admin_settings_error'] = 'Your session token expired. Please try again.';
    header('Location: ../views/admin-settings.php');
    exit;
}

$approvalPolicy = (string) ($_POST['require_admin_approval'] ?? '');

if (!in_array($approvalPolicy, ['0', '1'], true)) {
    $_SESSION['admin_settings_error'] = 'Select a valid registration approval policy.';
    header('Location: ../views/admin-settings.php');
    exit;
}

require_once __DIR__ . '/../models/setting.php';

try {
    (new Setting())->setRequiresAdminApproval($approvalPolicy === '1');
    $_SESSION['admin_settings_success'] = $approvalPolicy === '1'
        ? 'New registrations now require administrator approval.'
        : 'New registrations will now be approved and activated automatically.';
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    $_SESSION['admin_settings_error'] = 'The registration policy could not be saved.';
}

header('Location: ../views/admin-settings.php');
exit;
