<?php
session_start();
require_once '../config/connections.php';

//  Only allow Admins (role_id = 1)
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

//Fetch stats
try {
    $stats = [
        // Total Documents
        'total_docs' => $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn(),
        // Active Users
        'active_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
        // Total Offices
        'total_offices' => $pdo->query("SELECT COUNT(*) FROM offices")->fetchColumn(),
        // Pending Documents
        'pending_docs' => $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'Pending'")->fetchColumn()
    ];
} catch (PDOException $e) {
    error_log("Dashboard Stats Error: " . $e->getMessage());
    $stats = ['total_docs' => 0, 'active_users' => 0, 'total_offices' => 0, 'pending_docs' => 0];
}
?>