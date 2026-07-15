<?php
// Start session to access logged-in user
session_start();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
    exit;
}

// Check if secretary is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) { // role_id 2 = Secretary
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Load dependencies
require_once __DIR__ . '/../models/document.php';
require_once __DIR__ . '/../models/office.php';

$document = new Document();
$office   = new Office();

// Get raw POST data or fallback to $_POST
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$action = $input['action'] ?? '';
$docId  = $input['document_id'] ?? null;
$userId = $_SESSION['user_id']; // logged-in secretary

header('Content-Type: application/json');

try {
    switch ($action) {
        case 'assign':
            if (!$docId) throw new Exception("Missing document_id");
            $memberIds = $input['member_ids'] ?? [];
            if (empty($memberIds)) throw new Exception("At least one member is required.");
            $memberIds = array_map('intval', $memberIds);
            $document->assignDocument($docId, $userId, $memberIds);
            echo json_encode(['success' => true, 'message' => 'Document assigned successfully.']);
            break;

        case 'forward':
            if (!$docId) throw new Exception("Missing document_id");
            $targetOfficeId = $input['office_id'] ?? null;
            if (!$targetOfficeId) throw new Exception("Target office is required.");
            $document->forwardDocument($docId, $userId, (int)$targetOfficeId);
            echo json_encode(['success' => true, 'message' => 'Document forwarded.']);
            break;

        case 'finish':
            if (!$docId) throw new Exception("Missing document_id");
            $document->finishDocument($docId, $userId);
            echo json_encode(['success' => true, 'message' => 'Document marked as Finished.']);
            break;

        case 'cancel':
            if (!$docId) throw new Exception("Missing document_id");
            $document->cancelDocument($docId, $userId);
            echo json_encode(['success' => true, 'message' => 'Document cancelled.']);
            break;

        default:
            throw new Exception("Invalid action.");
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
