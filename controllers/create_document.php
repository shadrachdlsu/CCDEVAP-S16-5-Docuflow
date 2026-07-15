<?php
ob_start();
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    http_response_code(401);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/document.php';

$userId   = $_SESSION['user_id'];
$officeId = $_SESSION['office_id'];

$title    = $_POST['title'] ?? '';
$typeId   = $_POST['type_id'] ?? null;

if (empty($title) || !$typeId) {
    http_response_code(400);
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'Title and type are required.']);
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
        http_response_code(500);
        ob_clean();
        echo json_encode(['success' => false, 'message' => 'File upload failed.']);
        exit;
    }
}

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

$document = new Document();
$document->addTrailEntry($docId, $userId, 'Created', 'Document created');

ob_clean();
header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Document created.', 'document_id' => $docId]);
