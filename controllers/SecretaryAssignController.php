<?php
/* ==========================================
   SECRETARY ASSIGN ACTION CONTROLLER
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign') {
    $documentModel = new Document();
    
    $docId = $_POST['document_id'] ?? null;
    $memberIds = $_POST['member_ids'] ?? [];
    $userId = $_SESSION['user_id'];
    $officeId = $_SESSION['office_id'];
    
    if (empty($docId) || empty($memberIds)) {
        $_SESSION['error'] = 'Document and Members are required for assignment.';
        header('Location: ../views/secretary-dashboard.php#pending');
        exit;
    }
    
    try {
        global $pdo;
        $pdo->beginTransaction();
        
        $documentModel->updateStatus($docId, 'For Signature');
        
        foreach ($memberIds as $memberId) {
            $documentModel->assignSignatory($docId, $memberId);
        }
        
        $documentModel->addTrailEntry($docId, $userId, $officeId, null, 'Assigned', 'Assigned for signature');
        $pdo->commit();
        
        $_SESSION['success'] = 'Document assigned successfully.';
        header('Location: ../views/secretary-dashboard.php#pending');
        exit;
    } catch(Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'Failed to assign document: ' . $e->getMessage();
        header('Location: ../views/secretary-dashboard.php#pending');
        exit;
    }
} else {
    header('Location: ../views/secretary-dashboard.php');
    exit;
}
?>
