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

require_once __DIR__ . '/../models/user.php';

$user = new User();

// Get office from session by default
$officeId = $_GET['office_id'] ?? $_SESSION['office_id'] ?? null;

if (!$officeId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Office ID required.']);
    exit;
}

$members = $user->getMembersByOffice((int)$officeId);

header('Content-Type: application/json');
echo json_encode($members);
