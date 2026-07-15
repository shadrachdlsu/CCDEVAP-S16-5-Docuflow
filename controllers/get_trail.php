<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../models/document.php';

$docId = $_GET['document_id'] ?? null;
if (!$docId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Document ID required.']);
    exit;
}

$document = new Document();
$trail = $document->getTrail((int)$docId);

header('Content-Type: application/json');
echo json_encode($trail);
