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

    // office bottleneck
    $bottleneck = $pdo->query("
        SELECT o.office_name, COUNT(*) as count 
        FROM documents d 
        JOIN offices o ON d.current_office_id = o.office_id 
        WHERE d.status = 'Pending' 
        GROUP BY d.current_office_id 
        ORDER BY count DESC 
        LIMIT 1
    ")->fetch();
    $stats['bottleneck_text'] = $bottleneck ? $bottleneck['count'] . " Pending in " . $bottleneck['office_name'] : "0 Pending Documents";

    // 2. volume trend
    $this_month = $pdo->query("SELECT COUNT(*) FROM documents WHERE YEAR(created_at) = YEAR(CURRENT_DATE) AND MONTH(created_at) = MONTH(CURRENT_DATE)")->fetchColumn();
    $last_month = $pdo->query("SELECT COUNT(*) FROM documents WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)")->fetchColumn();
    if ($last_month > 0) {
        $diff = $this_month - $last_month;
        $pct = round(($diff / $last_month) * 100);
        $stats['trend_text'] = ($pct >= 0 ? "+" : "") . $pct . "% this month";
    } else {
        $stats['trend_text'] = "+" . $this_month . " new this month";
    }

    // 3. document type distribution
    $top_type = $pdo->query("
        SELECT dt.type_name, COUNT(*) as count 
        FROM documents d 
        JOIN document_types dt ON d.type_id = dt.type_id 
        GROUP BY d.type_id 
        ORDER BY count DESC 
        LIMIT 1
    ")->fetch();
    $total_docs = $stats['total_docs'];
    if ($top_type && $total_docs > 0) {
        $pct = round(($top_type['count'] / $total_docs) * 100);
        $typeName = $top_type['type_name'];
        if (substr(strtolower($typeName), -1) !== 's') {
            $typeName .= 's';
        }
        $stats['types_text'] = $pct . "% " . $typeName;
    } else {
        $stats['types_text'] = "No document types";
    }

} catch (PDOException $e) {
    error_log("Dashboard Stats Error: " . $e->getMessage());
    $stats = [
        'total_docs' => 0, 
        'active_users' => 0, 
        'total_offices' => 0, 
        'pending_docs' => 0,
        'bottleneck_text' => '0 Pending Documents',
        'trend_text' => '0% this month',
        'types_text' => 'No document types'
    ];
}
?>