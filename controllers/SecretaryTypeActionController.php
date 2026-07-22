<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/documentType.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 || !isset($_SESSION['office_id'])) {
    header('Location: ../controllers/LogoutController.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $docTypeModel = new DocumentType();
    
    $action = $_POST['action'];
    $typeId = $_POST['type_id'] ?? null;
    $typeName = trim($_POST['type_name'] ?? '');
    $officeId = $_SESSION['office_id'];
    
    try {
        if ($action === 'add') {
            if (empty($typeName)) {
                $_SESSION['error'] = 'Type name is required.';
            } else {
                $docTypeModel->addType($typeName, $officeId);
                $_SESSION['success'] = 'Document type added successfully.';
            }
        } elseif ($action === 'edit') {
            if (empty($typeId) || empty($typeName)) {
                $_SESSION['error'] = 'Type ID and Name are required for editing.';
            } else {
                $docTypeModel->updateType($typeId, $typeName);
                $_SESSION['success'] = 'Document type updated successfully.';
            }
        } elseif ($action === 'delete') {
            if (empty($typeId)) {
                $_SESSION['error'] = 'Type ID is required for deletion.';
            } else {
                $docTypeModel->deleteType($typeId);
                $_SESSION['success'] = 'Document type deleted successfully.';
            }
        } else {
            $_SESSION['error'] = 'Invalid action.';
        }
        
        header('Location: ../views/secretary-dashboard.php#types');
        exit;
    } catch(Exception $e) {
        $_SESSION['error'] = 'Action failed: ' . $e->getMessage();
        header('Location: ../views/secretary-dashboard.php#types');
        exit;
    }
} else {
    header('Location: ../views/secretary-dashboard.php');
    exit;
}
?>
