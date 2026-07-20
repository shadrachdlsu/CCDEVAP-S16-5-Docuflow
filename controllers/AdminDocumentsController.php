<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}


try {
    $stmt = $pdo->query("SELECT tracking_code AS id, title, type_name AS type, current_office AS office, status FROM view_document_summary");
    $documents = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $documents = [];
    error_log("Error fetching documents: " . $e->getMessage());
}
?>