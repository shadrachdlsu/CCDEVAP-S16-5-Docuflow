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
            $offices = isset($_POST['offices']) ? json_decode($_POST['offices'], true) : [];
            
            if (empty($name)) throw new Exception("Document Type name is required.");

            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO document_types (type_name) VALUES (:name)");
            $stmt->execute([':name' => $name]);
            $type_id = $pdo->lastInsertId();
            
            if (!empty($offices)) {
                $stmt = $pdo->prepare("INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)");
                foreach ($offices as $office_name) {
                    $ostmt = $pdo->prepare("SELECT office_id FROM offices WHERE office_name = :name");
                    $ostmt->execute([':name' => $office_name]);
                    $office_id = $ostmt->fetchColumn();
                    
                    if ($office_id) {
                        $stmt->execute([':type_id' => $type_id, ':office_id' => $office_id]);
                    }
                }
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $offices = isset($_POST['offices']) ? json_decode($_POST['offices'], true) : [];
            
            if (empty($id) || empty($name)) throw new Exception("ID and Document Type name are required.");

            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("UPDATE document_types SET type_name = :name WHERE type_id = :id");
            $stmt->execute([':name' => $name, ':id' => $id]);
            
            $stmt = $pdo->prepare("DELETE FROM document_type_offices WHERE type_id = :id");
            $stmt->execute([':id' => $id]);
            
            if (!empty($offices)) {
                $stmt = $pdo->prepare("INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)");
                foreach ($offices as $office_name) {
                    $ostmt = $pdo->prepare("SELECT office_id FROM offices WHERE office_name = :name");
                    $ostmt->execute([':name' => $office_name]);
                    $office_id = $ostmt->fetchColumn();
                    
                    if ($office_id) {
                        $stmt->execute([':type_id' => $id, ':office_id' => $office_id]);
                    }
                }
            }
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("DELETE FROM document_type_offices WHERE type_id = :id");
            $stmt->execute([':id' => $id]);
            
            $stmt = $pdo->prepare("DELETE FROM document_types WHERE type_id = :id");
            $stmt->execute([':id' => $id]);
            
            $pdo->commit();
            echo json_encode(['success' => true]);
        } 
        else {
            echo json_encode(['error' => 'Invalid action']);
        }
    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        echo json_encode(['error' => $e->getMessage()]);
    }
    exit;
}

// Fetch document types
$stmtTypes = $pdo->query("SELECT type_id as id, type_name as name FROM document_types ORDER BY type_name");
$docTypes = $stmtTypes->fetchAll(PDO::FETCH_ASSOC);

foreach ($docTypes as &$type) {
    $stmtOfficesForType = $pdo->prepare("
        SELECT o.office_name 
        FROM document_type_offices dto
        JOIN offices o ON dto.office_id = o.office_id
        WHERE dto.type_id = :type_id
    ");
    $stmtOfficesForType->execute([':type_id' => $type['id']]);
    $type['offices'] = $stmtOfficesForType->fetchAll(PDO::FETCH_COLUMN);
}

// Fetch all offices for dropdown
$stmtOffices = $pdo->query("SELECT office_id, office_name FROM offices ORDER BY office_name");
$officesList = $stmtOffices->fetchAll(PDO::FETCH_ASSOC);
?>
