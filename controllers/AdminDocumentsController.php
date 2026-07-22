<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/document.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$documentModel = new Document();

try {
    $documents = $documentModel->getAllSummary();
} catch (PDOException $e) {
    $documents = [];
    error_log("Error fetching documents: " . $e->getMessage());
}
?>