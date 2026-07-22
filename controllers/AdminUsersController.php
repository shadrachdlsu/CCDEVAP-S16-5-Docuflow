<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/office.php';
require_once __DIR__ . '/../models/role.php';

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
$officeModel = new Office();
$roleModel = new Role();

// Action Handlers
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];

    try {
        if ($action === 'create') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role_id = $_POST['role_id'] ?? 0;
            $office_id = $_POST['office_id'] ?? null;
            $status = $_POST['status'] ?? 'Active';
            
            if (empty($name) || empty($email) || empty($password) || empty($role_id)) {
                throw new Exception("Name, Email, Password, and Role are required.");
            }
            
            $is_active = ($status === 'Active') ? 1 : 0;
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            if (empty($office_id)) $office_id = null;

            $userModel->create(
                $role_id,
                $office_id,
                $name,
                $email,
                $password_hash,
                $is_active,
                'Approved'
            );
            
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';
            $role_id = $_POST['role_id'] ?? 0;
            $office_id = $_POST['office_id'] ?? null;
            $status = $_POST['status'] ?? 'Active';
            
            if (empty($id) || empty($name) || empty($email) || empty($role_id)) {
                throw new Exception("ID, Name, Email, and Role are required.");
            }
            
            $is_active = ($status === 'Active') ? 1 : 0;
            if (empty($office_id)) $office_id = null;
            
            $password_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
            
            $userModel->update(
                $id,
                $role_id,
                $office_id,
                $name,
                $email,
                $password_hash,
                $is_active
            );
            
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $userModel->delete($id);
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

// Fetch all users
$users = $userModel->getAllWithRolesAndOffices();

// Fetch roles for dropdowns
$roles = $roleModel->getAll();

// Fetch offices for dropdowns
$offices = $officeModel->getAllOffices();
?>
