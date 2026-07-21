<?php
/* ==========================================
   SECRETARY CREATE ACTION CONTROLLER
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $documentModel = new Document();
    
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
        $stmt = $pdo->prepare("
            INSERT INTO documents (tracking_code, title, file_path, type_id, requires_signature, creator_id, current_office_id, status)
            VALUES (:tracking, :title, :file, :type_id, 1, :creator, :office, 'Created')
        ");
        $stmt->execute([
            ':tracking' => $trackingCode,
            ':title'    => $title,
            ':file'     => $filePath,
            ':type_id'  => $typeId,
            ':creator'  => $userId,
            ':office'   => $officeId,
        ]);
        
        $docId = $pdo->lastInsertId();
        $documentModel->addTrailEntry($docId, $userId, $officeId, null, 'Created', 'Document created');
        
        $_SESSION['success'] = 'Document created successfully.';
        header('Location: ../views/secretary-dashboard.php');
        exit;
    } catch(Exception $e) {
        $_SESSION['error'] = 'Failed to create document: ' . $e->getMessage();
        header('Location: ../views/secretary-dashboard.php');
        exit;
    }
} else {
    header('Location: ../views/secretary-dashboard.php');
    exit;
}
?>
