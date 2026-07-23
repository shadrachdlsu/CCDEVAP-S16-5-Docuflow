<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'Admin') {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/admin-manage-offices.php');
    exit;
}

$officeId = filter_input(INPUT_POST, 'office_id', FILTER_VALIDATE_INT) ?: 0;
$officeName = trim((string) ($_POST['office_name'] ?? ''));
$secretaryUserId = filter_input(INPUT_POST, 'secretary_user_id', FILTER_VALIDATE_INT) ?: 0;

if ($officeName === '' || mb_strlen($officeName) > 100) {
    returnToOffice($officeId, $officeName, 'Enter an office name containing no more than 100 characters.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

try {
    $conn->begin_transaction();

    if ($officeId > 0) {
        $existingStatement = $conn->prepare('SELECT office_id FROM offices WHERE office_id = ? LIMIT 1');
        $existingStatement->bind_param('i', $officeId);
        $existingStatement->execute();
        $officeExists = (bool) $existingStatement->get_result()->fetch_assoc();
        $existingStatement->close();

        if (!$officeExists) {
            returnToOffice(0, '', 'The office could not be found.');
        }

        if ($secretaryUserId > 0) {
            $secretaryStatement = $conn->prepare(
                "SELECT users.user_id
                 FROM users
                 INNER JOIN roles ON roles.role_id = users.role_id
                 WHERE users.user_id = ?
                   AND users.office_id = ?
                   AND roles.role_name = 'Secretary'
                   AND users.is_active = 1
                   AND users.registration_status = 'Approved'
                 LIMIT 1"
            );
            $secretaryStatement->bind_param('ii', $secretaryUserId, $officeId);
            $secretaryStatement->execute();
            $validSecretary = (bool) $secretaryStatement->get_result()->fetch_assoc();
            $secretaryStatement->close();

            if (!$validSecretary) {
                returnToOffice($officeId, $officeName, 'Select an active Secretary account from this office.');
            }
        }

        $statement = $conn->prepare('UPDATE offices SET office_name = ? WHERE office_id = ?');
        $statement->bind_param('si', $officeName, $officeId);
        $statement->execute();
        $statement->close();

        if ($secretaryUserId > 0) {
            $assignmentStatement = $conn->prepare(
                'INSERT INTO office_secretaries (office_id, secretary_user_id)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE
                   secretary_user_id = VALUES(secretary_user_id),
                   assigned_at = CURRENT_TIMESTAMP'
            );
            $assignmentStatement->bind_param('ii', $officeId, $secretaryUserId);
        } else {
            $assignmentStatement = $conn->prepare('DELETE FROM office_secretaries WHERE office_id = ?');
            $assignmentStatement->bind_param('i', $officeId);
        }

        $assignmentStatement->execute();
        $assignmentStatement->close();
        $_SESSION['admin_office_success'] = 'Office updated successfully.';
    } else {
        $statement = $conn->prepare('INSERT INTO offices (office_name) VALUES (?)');
        $statement->bind_param('s', $officeName);
        $statement->execute();
        $statement->close();
        $_SESSION['admin_office_success'] = 'Office added successfully.';
    }

    $conn->commit();
} catch (mysqli_sql_exception $exception) {
    $conn->rollback();
    error_log($exception->getMessage());
    $message = 'The office could not be saved.';

    if ($exception->getCode() === 1062) {
        $message = str_contains($exception->getMessage(), 'secretary_user_id')
            ? 'That secretary is already in charge of another office.'
            : 'An office with that name already exists.';
    }

    returnToOffice($officeId, $officeName, $message);
}

$destination = $officeId > 0
    ? '../views/admin-office.php?id=' . $officeId
    : '../views/admin-manage-offices.php';
header('Location: ' . $destination);
exit;

function returnToOffice(int $officeId, string $officeName, string $message): never
{
    $_SESSION['admin_office_error'] = $message;

    if ($officeId > 0) {
        header('Location: ../views/admin-office.php?id=' . $officeId);
    } else {
        $_SESSION['admin_office_name'] = $officeName;
        header('Location: ../views/admin-manage-offices.php');
    }

    exit;
}
