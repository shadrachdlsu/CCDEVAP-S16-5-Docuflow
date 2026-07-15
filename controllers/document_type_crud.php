<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
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

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$action = $input['action'] ?? '';
header('Content-Type: application/json');

try {
    switch ($action) {
        case 'add':
            $name = trim($input['type_name'] ?? '');
            if (empty($name)) throw new Exception("Type name is required.");
            $desc = $input['description'] ?? null;
            $newId = $docType->addType($officeId, $name, $desc);
            echo json_encode(['success' => true, 'message' => 'Type added.', 'type_id' => $newId]);
            break;

        case 'edit':
            $typeId = $input['type_id'] ?? null;
            $name = trim($input['type_name'] ?? '');
            if (!$typeId || empty($name)) throw new Exception("Type ID and name are required.");
            $desc = $input['description'] ?? null;
            $docType->updateType((int)$typeId, $name, $desc);
            echo json_encode(['success' => true, 'message' => 'Type updated.']);
            break;

        case 'delete':
            $typeId = $input['type_id'] ?? null;
            if (!$typeId) throw new Exception("Type ID is required.");
            $docType->deleteType((int)$typeId);
            echo json_encode(['success' => true, 'message' => 'Type deleted.']);
            break;

        default:
            throw new Exception("Invalid action.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
