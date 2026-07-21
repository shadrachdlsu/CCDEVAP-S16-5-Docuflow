<?php
/* ==========================================
   SECRETARY STATUS ACTION CONTROLLER
   CCDEVAP-S16-5-Docuflow
========================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/document.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 || !isset($_SESSION['office_id'])) {
    header('Location: ../controllers/LogoutController.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $documentModel = new Document();
    
    $docId = $_POST['document_id'] ?? null;
    $action = $_POST['action'];
    $userId = $_SESSION['user_id'];
    $officeId = $_SESSION['office_id'];
    
    if (empty($docId)) {
        $_SESSION['error'] = 'Document ID is required.';
        header('Location: ../views/secretary-dashboard.php#pending');
        exit;
    }
    
    try {
        global $pdo;
        
        if ($action === 'finish') {
            $documentModel->updateStatus($docId, 'Completed');
            $documentModel->addTrailEntry($docId, $userId, $officeId, null, 'Finished', 'Marked as Finished');
            $_SESSION['success'] = 'Document marked as Finished.';
        } elseif ($action === 'cancel') {
            $documentModel->updateStatus($docId, 'Recalled');
            $documentModel->addTrailEntry($docId, $userId, $officeId, null, 'Cancelled', 'Document Cancelled');
            $_SESSION['success'] = 'Document Cancelled.';
        } else {
            $_SESSION['error'] = 'Invalid status action.';
        }
        
        header('Location: ../views/secretary-dashboard.php#pending');
        exit;
    } catch(Exception $e) {
        $_SESSION['error'] = 'Failed to update status: ' . $e->getMessage();
        header('Location: ../views/secretary-dashboard.php#pending');
        exit;
    }
} else {
    header('Location: ../views/secretary-dashboard.php');
    exit;
}
?>
