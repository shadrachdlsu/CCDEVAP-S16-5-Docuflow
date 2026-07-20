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
            if (empty($name)) throw new Exception("Office name is required.");

            $stmt = $pdo->prepare("INSERT INTO offices (office_name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            if (empty($id) || empty($name)) throw new Exception("ID and Office name are required.");

            $stmt = $pdo->prepare("UPDATE offices SET office_name = :name WHERE office_id = :id");
            $stmt->execute([':name' => $name, ':id' => $id]);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $stmt = $pdo->prepare("DELETE FROM offices WHERE office_id = :id");
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

$stmtOffices = $pdo->query("SELECT office_id as id, office_name as name FROM offices ORDER BY office_name");
$offices = $stmtOffices->fetchAll(PDO::FETCH_ASSOC);
?>
