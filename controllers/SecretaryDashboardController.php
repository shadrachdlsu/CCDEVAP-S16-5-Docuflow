<?php
/* ==========================================
   SECRETARY DASHBOARD CONTROLLER (Monolithic)
   CCDEVAP-S16-5-Docuflow
========================================== */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/document.php';
require_once __DIR__ . '/../models/documentType.php';
require_once __DIR__ . '/../models/office.php';

// Auth Check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2 || !isset($_SESSION['office_id'])) {
    header('Location: ../controllers/LogoutController.php');
    exit;
}

$documentModel = new Document();
$docTypeModel  = new DocumentType();
$officeModel   = new Office();

$officeId      = $_SESSION['office_id'];
$officeName    = $_SESSION['office_name'] ?? 'My Office';
$userEmail     = $_SESSION['email'] ?? 'secretary@docuflow.local';
$userFullName  = $_SESSION['full_name'] ?? 'Secretary';
$userId        = $_SESSION['user_id'];

// 1. DASHBOARD STATS & DOCUMENTS
$allDocs    = $documentModel->getDocumentsForOffice($officeId);
$pending    = count(array_filter($allDocs, fn($d) => in_array($d['status'], ['Created','Pending','Received','Released','For Signature','Rejected'])));
$signed     = count(array_filter($allDocs, fn($d) => $d['status'] === 'Signed'));
$finished   = count(array_filter($allDocs, fn($d) => in_array($d['status'], ['Completed','Recalled'])));

$stats = [
    'total'    => count($allDocs),
    'pending'  => $pending,
    'signed'   => $signed,
    'finished' => $finished,
];

// 2. DOCUMENT TYPES
$documentTypes = $docTypeModel->getTypesByOffice($officeId);

// 3. MEMBERS (For Assignment)
global $pdo;
$stmt = $pdo->prepare("SELECT user_id, full_name, email FROM users WHERE role_id = 3 AND office_id = :office_id AND is_active = 1");
$stmt->execute([':office_id' => $officeId]);
$members = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 4. OFFICES (For Forwarding)
$allOffices = $officeModel->getAllOffices();
$forwardableOffices = array_filter($allOffices, fn($o) => $o['office_id'] != $officeId);

// AJAX HANDLER (For lightweight GET requests if needed by JS, e.g., Paper Trail)
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    if ($_GET['action'] === 'trail') {
        $docId = $_GET['document_id'] ?? null;
        if ($docId) {
            $trail = $documentModel->getTrail((int)$docId);
            foreach ($trail as &$t) {
                $t['action_date'] = date('M d, Y h:i A', strtotime($t['created_at']));
            }
            echo json_encode(['success' => true, 'trail' => $trail]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing document ID']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}
?>
