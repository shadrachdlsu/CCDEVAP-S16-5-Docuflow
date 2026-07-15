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

require_once __DIR__ . '/../models/documentType.php';

$docType = new DocumentType();
$officeId = $_SESSION['office_id'] ?? null;

if (!$officeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Office not assigned.']);
    exit;
}

$types = $docType->getTypesByOffice($officeId);

header('Content-Type: application/json');
echo json_encode($types);
