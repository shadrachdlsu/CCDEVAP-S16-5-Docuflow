<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/documentType.php';
require_once __DIR__ . '/../models/office.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    if (isset($_POST['action'])) {
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$documentTypeModel = new DocumentType();
$officeModel = new Office();

// Cache offices for name->id mapping
$officesList = $officeModel->getAllOffices();
$officeNameToId = [];
foreach ($officesList as $off) {
    $officeNameToId[$off['name']] = $off['id'];
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

            // Convert office names to IDs
            $officeIds = [];
            foreach ($offices as $officeName) {
                if (isset($officeNameToId[$officeName])) {
                    $officeIds[] = $officeNameToId[$officeName];
                }
            }

            $documentTypeModel->createWithOffices($name, $officeIds, 1);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'update') {
            $id = $_POST['id'] ?? 0;
            $name = $_POST['name'] ?? '';
            $offices = isset($_POST['offices']) ? json_decode($_POST['offices'], true) : [];
            
            if (empty($id) || empty($name)) throw new Exception("ID and Document Type name are required.");

            // Convert office names to IDs
            $officeIds = [];
            foreach ($offices as $officeName) {
                if (isset($officeNameToId[$officeName])) {
                    $officeIds[] = $officeNameToId[$officeName];
                }
            }

            $documentTypeModel->updateWithOffices($id, $name, $officeIds, 1);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if (empty($id)) throw new Exception("ID is required.");

            $documentTypeModel->deleteType($id);
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

// Fetch document types
$docTypesRaw = $documentTypeModel->getAllWithOffices();

// The raw results have offices as a comma-separated string 'Office A, Office B'.
// The view expects $type['offices'] to be an array of names.
$docTypes = [];
foreach ($docTypesRaw as $type) {
    $docTypes[] = [
        'id' => $type['id'],
        'name' => $type['name'],
        'offices' => !empty($type['offices']) ? explode(', ', $type['offices']) : []
    ];
}
?>
