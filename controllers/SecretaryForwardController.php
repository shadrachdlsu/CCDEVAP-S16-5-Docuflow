<?php
/* ==========================================
   SECRETARY FORWARD ACTION CONTROLLER
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'forward') {
    $documentModel = new Document();
    
    $docId = $_POST['document_id'] ?? null;
    $targetOfficeId = $_POST['office_id'] ?? null;
    $userId = $_SESSION['user_id'];
    $currentOfficeId = $_SESSION['office_id'];
    
    if (empty($docId) || empty($targetOfficeId)) {
        $_SESSION['error'] = 'Document and Target Office are required.';
        header('Location: ../views/secretary-dashboard.php#release');
        exit;
    }
    
    try {
        global $pdo;
        $pdo->beginTransaction();
        
        $documentModel->forwardDocument($docId, $targetOfficeId);
        
        $documentModel->addTrailEntry($docId, $userId, $currentOfficeId, $targetOfficeId, 'Forwarded', 'Document forwarded');
        $pdo->commit();
        
        $_SESSION['success'] = 'Document forwarded successfully.';
        header('Location: ../views/secretary-dashboard.php#release');
        exit;
    } catch(Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'Failed to forward document: ' . $e->getMessage();
        header('Location: ../views/secretary-dashboard.php#release');
        exit;
    }
} else {
    header('Location: ../views/secretary-dashboard.php');
    exit;
}
?>
