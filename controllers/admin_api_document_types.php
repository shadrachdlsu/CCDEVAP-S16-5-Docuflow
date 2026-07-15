<?php
session_start();
header('Content-Type: application/json');

require_once '../config/connections.php';

// Allow only admins (role_id = 1)
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($action === 'list') {
        // Fetch document types
        $stmt = $pdo->query("SELECT type_id as id, type_name as name FROM document_types ORDER BY type_name");
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Fetch their associated offices
        foreach ($types as &$type) {
            $stmt = $pdo->prepare("
                SELECT o.office_name 
                FROM document_type_offices dto
                JOIN offices o ON dto.office_id = o.office_id
                WHERE dto.type_id = :type_id
            ");
            $stmt->execute([':type_id' => $type['id']]);
            $offices = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $type['offices'] = $offices;
        }
        
        echo json_encode($types);
    } 
    elseif ($action === 'get_offices') {
        $stmt = $pdo->query("SELECT office_id, office_name FROM offices ORDER BY office_name");
        echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    }
    elseif ($action === 'create') {
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
                // Find office_id by name
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
        
        // Update type name
        $stmt = $pdo->prepare("UPDATE document_types SET type_name = :name WHERE type_id = :id");
        $stmt->execute([':name' => $name, ':id' => $id]);
        
        // Delete old office mappings
        $stmt = $pdo->prepare("DELETE FROM document_type_offices WHERE type_id = :id");
        $stmt->execute([':id' => $id]);
        
        // Insert new office mappings
        if (!empty($offices)) {
            $stmt = $pdo->prepare("INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)");
            foreach ($offices as $office_name) {
                // Find office_id by name
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
        
        // Delete mappings first (FK constraint)
        $stmt = $pdo->prepare("DELETE FROM document_type_offices WHERE type_id = :id");
        $stmt->execute([':id' => $id]);
        
        // Delete the type itself
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
?>
