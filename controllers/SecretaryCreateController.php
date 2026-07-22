<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/document.php';
require_once __DIR__ . '/../models/documentTrail.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 || !isset($_SESSION['office_id'])) {
    header('Location: ../controllers/LogoutController.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentModel = new Document();
    $trailModel    = new DocumentTrail();
    
    $title  = trim($_POST['title'] ?? '');
    $typeId = $_POST['type_id'] ?? null;
    $userId = $_SESSION['user_id'];
    $officeId = $_SESSION['office_id'];
    
    if (empty($title) || !$typeId) {
        $_SESSION['error'] = 'Title and type are required.';
        header('Location: ../views/secretary-dashboard.php');
        exit;
    }
    
    $trackingCode = 'DOC-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 8));
    $filePath = null;
    
    if (!empty($_FILES['document_file']) && $_FILES['document_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }
        $ext = pathinfo($_FILES['document_file']['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $destination = $uploadDir . $filename;
        if (move_uploaded_file($_FILES['document_file']['tmp_name'], $destination)) {
            $filePath = '/uploads/' . $filename;
        } else {
            $_SESSION['error'] = 'File upload failed.';
            header('Location: ../views/secretary-dashboard.php');
            exit;
        }
    }
    
    try {
        global $pdo;
        $pdo->beginTransaction();
        
        $docId = $documentModel->createDocument(
            $trackingCode,
            $title,
            $filePath,
            $typeId,
            1, // requires_signature
            $userId,
            $officeId,
            'Created'
        );
        
        $trailModel->addEntry($docId, $userId, $officeId, null, 'Created', 'Document created');
        
        $pdo->commit();
        
        $_SESSION['success'] = 'Document created successfully.';
        header('Location: ../views/secretary-dashboard.php');
        exit;
    } catch(Exception $e) {
        global $pdo;
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $_SESSION['error'] = 'Failed to create document: ' . $e->getMessage();
        header('Location: ../views/secretary-dashboard.php');
        exit;
    }
} else {
    header('Location: ../views/secretary-dashboard.php');
    exit;
}
?>
