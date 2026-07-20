<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

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

            $stmt = $pdo->prepare("
                INSERT INTO users (role_id, office_id, full_name, email, password_hash, is_active, registration_status) 
                VALUES (:role_id, :office_id, :name, :email, :password_hash, :is_active, 'Approved')
            ");
            $stmt->execute([
                ':role_id' => $role_id,
                ':office_id' => $office_id,
                ':name' => $name,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':is_active' => $is_active
            ]);
            
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
            
            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET role_id = :role_id, office_id = :office_id, full_name = :name, 
                        email = :email, password_hash = :password_hash, is_active = :is_active 
                    WHERE user_id = :id
                ");
                $stmt->execute([
                    ':role_id' => $role_id,
                    ':office_id' => $office_id,
                    ':name' => $name,
                    ':email' => $email,
                    ':password_hash' => $password_hash,
                    ':is_active' => $is_active,
                    ':id' => $id
                ]);
            } else {
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET role_id = :role_id, office_id = :office_id, full_name = :name, 
                        email = :email, is_active = :is_active 
                    WHERE user_id = :id
                ");
                $stmt->execute([
                    ':role_id' => $role_id,
                    ':office_id' => $office_id,
                    ':name' => $name,
                    ':email' => $email,
                    ':is_active' => $is_active,
                    ':id' => $id
                ]);
            }
            
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = :id");
            $stmt->execute([':id' => $id]);
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
$stmtUsers = $pdo->query("
    SELECT 
        u.user_id as id, 
        u.full_name as name, 
        u.email, 
        r.role_id,
        r.role_name as role,
        o.office_id,
        o.office_name as office,
        IF(u.is_active = 1, 'Active', 'Inactive') as status
    FROM users u
    JOIN roles r ON u.role_id = r.role_id
    LEFT JOIN offices o ON u.office_id = o.office_id
    ORDER BY u.full_name
");
$users = $stmtUsers->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles for dropdowns
$stmtRoles = $pdo->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
$roles = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

// Fetch offices for dropdowns
$stmtOffices = $pdo->query("SELECT office_id, office_name FROM offices ORDER BY office_name");
$offices = $stmtOffices->fetchAll(PDO::FETCH_ASSOC);
?>
