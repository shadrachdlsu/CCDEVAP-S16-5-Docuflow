<?php
session_start();

// Only allow GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Check secretary session
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

require_once __DIR__ . '/../models/document.php';

$document = new Document();
$officeId = $_SESSION['office_id'] ?? null;

if (!$officeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Office not assigned.']);
    exit;
}

//filter by display status
$status = $_GET['status'] ?? null; 

$documents = $document->getDocumentsForOffice($officeId, $status);

header('Content-Type: application/json');
echo json_encode($documents);
