<?php
session_start();
require_once '../config/connections.php';

// Set header to JSON so JavaScript knows how to parse it
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("SELECT tracking_code AS id, title, type_name AS type, current_office AS office, status FROM view_document_summary");
    $documents = $stmt->fetchAll();
    
    // Output the JS array from the backend
    echo json_encode($documents);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>