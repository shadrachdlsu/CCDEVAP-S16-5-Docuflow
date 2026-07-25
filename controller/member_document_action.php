<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user_id']) || !in_array((string) ($_SESSION['role'] ?? ''), ['Member', 'Secretary'], true)) {
    header('Location: ../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../views/member-documents.php');
    exit;
}

$routeId = filter_input(INPUT_POST, 'route_id', FILTER_VALIDATE_INT);
$documentId = filter_input(INPUT_POST, 'document_id', FILTER_VALIDATE_INT);
$action = (string) ($_POST['action'] ?? '');
$remarks = trim((string) ($_POST['remarks'] ?? ''));

if (!$routeId || !$documentId || !in_array($action, ['receive', 'sign', 'reject'], true)) {
    returnToDocument($documentId ?: 0, 'Invalid document action.');
}

if (in_array($action, ['sign', 'reject'], true) && mb_strlen($remarks) > 1000) {
    returnToDocument($documentId, 'Remarks must not exceed 1000 characters.');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
require __DIR__ . '/db.php';

$userId = (int) $_SESSION['user_id'];

try {
    $conn->begin_transaction();

    $documentLockStatement = $conn->prepare(
        'SELECT document_id FROM documents WHERE document_id = ? FOR UPDATE'
    );
    $documentLockStatement->bind_param('i', $documentId);
    $documentLockStatement->execute();
    $lockedDocument = $documentLockStatement->get_result()->fetch_assoc();
    $documentLockStatement->close();

    if (!$lockedDocument) {
        throw new DomainException('Document not found.');
    }

    $routeStatement = $conn->prepare(
        'SELECT dr.status, dr.step_no
         FROM document_routes AS dr
         WHERE dr.route_id = ?
           AND dr.document_id = ?
           AND dr.signatory_user_id = ?
         FOR UPDATE'
    );
    $routeStatement->bind_param('iii', $routeId, $documentId, $userId);
    $routeStatement->execute();
    $route = $routeStatement->get_result()->fetch_assoc();
    $routeStatement->close();

    if (!$route) {
        throw new DomainException('This route is not assigned to your account.');
    }

    $routeStatus = (string) $route['status'];
    $routeStep = (int) $route['step_no'];

    if (in_array($routeStatus, ['Signed', 'Rejected', 'Released', 'Skipped', 'Completed'], true)) {
        throw new DomainException('This office assignment has already been completed.');
    }

    if ($routeStep > 0) {
        $earlierRouteStatement = $conn->prepare(
            "SELECT COUNT(*) AS unfinished_count
             FROM document_routes
             WHERE document_id = ?
               AND step_no > 0
               AND step_no < ?
               AND status IN ('Waiting', 'Received', 'For Signature')"
        );
        $earlierRouteStatement->bind_param('ii', $documentId, $routeStep);
        $earlierRouteStatement->execute();
        $unfinishedEarlierRoutes = (int) $earlierRouteStatement->get_result()->fetch_assoc()['unfinished_count'];
        $earlierRouteStatement->close();

        if ($unfinishedEarlierRoutes > 0) {
            throw new DomainException('This office must wait for the previous route step to be completed.');
        }
    }

    if ($action === 'receive') {
        if ($routeStatus !== 'Waiting') {
            throw new DomainException('Only waiting documents can be marked as received.');
        }

        $updateRoute = $conn->prepare(
            "UPDATE document_routes SET status = 'Received', acted_at = CURRENT_TIMESTAMP WHERE route_id = ?"
        );
        $updateRoute->bind_param('i', $routeId);
        $updateRoute->execute();
        $updateRoute->close();

        $message = 'Document marked as received.';
    } elseif ($action === 'reject') {
        $updateRoute = $conn->prepare(
            "UPDATE document_routes
             SET status = 'Rejected', remarks = NULLIF(?, ''), acted_at = CURRENT_TIMESTAMP
             WHERE route_id = ?"
        );
        $updateRoute->bind_param('si', $remarks, $routeId);
        $updateRoute->execute();
        $updateRoute->close();

        $skipRoutes = $conn->prepare(
            "UPDATE document_routes
             SET status = 'Skipped'
             WHERE document_id = ?
               AND route_id <> ?
               AND status IN ('Waiting', 'Received', 'For Signature')"
        );
        $skipRoutes->bind_param('ii', $documentId, $routeId);
        $skipRoutes->execute();
        $skipRoutes->close();

        $updateDocument = $conn->prepare(
            "UPDATE documents
             SET status = 'Rejected', current_office_id = NULL
             WHERE document_id = ?"
        );
        $updateDocument->bind_param('i', $documentId);
        $updateDocument->execute();
        $updateDocument->close();
        $message = 'Document rejected.';
    } else {
        $updateRoute = $conn->prepare(
            "UPDATE document_routes
             SET status = 'Signed', remarks = NULLIF(?, ''), acted_at = CURRENT_TIMESTAMP
             WHERE route_id = ?"
        );
        $updateRoute->bind_param('si', $remarks, $routeId);
        $updateRoute->execute();
        $updateRoute->close();

        $routeStatusStatement = $conn->prepare(
            'SELECT status
             FROM document_routes
             WHERE document_id = ?
             FOR UPDATE'
        );
        $routeStatusStatement->bind_param('i', $documentId);
        $routeStatusStatement->execute();
        $allRouteStatuses = $routeStatusStatement->get_result()->fetch_all(MYSQLI_ASSOC);
        $routeStatusStatement->close();

        $unfinishedStatuses = ['Waiting', 'Received', 'For Signature'];
        $unfinishedRouteCount = 0;

        foreach ($allRouteStatuses as $routeRow) {
            if (in_array($routeRow['status'], $unfinishedStatuses, true)) {
                $unfinishedRouteCount++;
            }
        }

        if ($unfinishedRouteCount > 0) {
            $updateDocument = $conn->prepare(
                "UPDATE documents
                 SET current_office_id = NULL, status = 'Pending'
                 WHERE document_id = ?"
            );
            $updateDocument->bind_param('i', $documentId);
            $message = $routeStep > 0
                ? 'Document signed. The next office can now review it.'
                : 'Document signed. Other offices can continue reviewing it independently.';
        } else {
            $updateDocument = $conn->prepare(
                "UPDATE documents
                 SET status = 'Completed', current_office_id = NULL
                 WHERE document_id = ?"
            );
            $updateDocument->bind_param('i', $documentId);
            $message = 'Document signed and completed.';
        }

        $updateDocument->execute();
        $updateDocument->close();
    }

    $conn->commit();
    $_SESSION['document_action_success'] = $message;
} catch (DomainException $exception) {
    $conn->rollback();
    $_SESSION['document_action_error'] = $exception->getMessage();
} catch (Throwable $exception) {
    $conn->rollback();
    error_log($exception->getMessage());
    $_SESSION['document_action_error'] = 'The document action could not be completed.';
}

header('Location: ../views/member-document.php?id=' . $documentId);
exit;

function returnToDocument(int $documentId, string $message): never
{
    $_SESSION['document_action_error'] = $message;
    $destination = $documentId > 0
        ? '../views/member-document.php?id=' . $documentId
        : '../views/member-documents.php';
    header('Location: ' . $destination);
    exit;
}
