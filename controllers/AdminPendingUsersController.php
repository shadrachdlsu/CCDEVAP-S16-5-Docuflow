<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/user.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$userModel = new User();

// Action Handlers
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    try {
        if ($action === 'approve') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $userModel->approveUser($id);
            echo json_encode(['success' => true]);
        } 
        else {
            echo json_encode(['error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Fetch all users and filter for pending/inactive only
$allUsers = $userModel->getAllWithRolesAndOffices();
$pendingUsers = array_filter($allUsers, function($user) {
    return $user['status'] === 'Inactive';
});
?>
