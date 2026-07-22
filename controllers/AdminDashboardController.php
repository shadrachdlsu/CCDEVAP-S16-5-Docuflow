<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/office.php';
require_once __DIR__ . '/../models/document.php';
require_once __DIR__ . '/../models/documentType.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$userModel = new User();
$officeModel = new Office();
$documentModel = new Document();
$documentTypeModel = new DocumentType();

// Dashboard Counts
try {
    $stats = [
        'total_docs' => $documentModel->countAll(),
        'active_users' => $userModel->countActiveUsers(),
        'total_offices' => $officeModel->countAllOffices(),
        'pending_docs' => $documentModel->countByStatus('Pending')
    ];

    $bottleneckData = $documentModel->getBottleneckData();
    $bottleneck = $bottleneckData['primary'];
    $stats['bottleneck_text'] = $bottleneck ? $bottleneck['count'] . " Pending in " . $bottleneck['office_name'] : "0 Pending Documents";

    $currentMonth = (int)date('n');
    $currentYear = (int)date('Y');
    
    $lastMonth = $currentMonth - 1;
    $lastMonthYear = $currentYear;
    if ($lastMonth === 0) {
        $lastMonth = 12;
        $lastMonthYear--;
    }

    $this_month = $documentModel->getMonthlyDocCount($currentMonth, $currentYear);
    $last_month = $documentModel->getMonthlyDocCount($lastMonth, $lastMonthYear);
    
    if ($last_month > 0) {
        $diff = $this_month - $last_month;
        $pct = round(($diff / $last_month) * 100);
        $stats['trend_text'] = ($pct >= 0 ? "+" : "") . $pct . "% this month";
    } else {
        $stats['trend_text'] = "+" . $this_month . " new this month";
    }

    $top_type = $documentModel->getTopType();
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


// Document Distribution Chart
$statusDist = $documentModel->getStatusDistribution();
$docDistLabels = [];
$docDistData = [];
foreach ($statusDist['rows'] as $row) {
    // Extract the status from the label string (e.g. "Pending - 50%")
    $parts = explode(' - ', $row['label']);
    if (count($parts) > 0) {
        $docDistLabels[] = $parts[0];
        $docDistData[] = (int)$row['value'];
    }
}
$docDistJson = json_encode(['labels' => $docDistLabels, 'data' => $docDistData]);


// User Distribution
$userDistData = $userModel->getUserDistribution();
$formattedUserDistRows = $userDistData['rows'];
$userDistGradient = $userDistData['gradient'];
$userDistTotal = $userDistData['total'];

// Office Directory
$officeDirectory = $officeModel->getOfficesWithDocCounts();

// Pending Documents
$pendingDocsList = $documentModel->getRecentPending(10);

// Bottleneck Chart
$bottleneckLabels = [];
$bottleneckDataCounts = [];
foreach ($bottleneckData['list'] as $row) {
    $bottleneckLabels[] = substr($row['office_name'], 0, 3);
    $bottleneckDataCounts[] = (int)$row['count'];
}
$bottleneckChartJson = json_encode(['labels' => $bottleneckLabels, 'data' => $bottleneckDataCounts]);


// Volume Trends Chart
$trendData = $documentModel->getTrendData(6);
$trendsChartJson = json_encode(['labels' => $trendData['labels'], 'data' => $trendData['data']]);


// Types Chart
$typesDist = $documentModel->getTypeDistribution();
$typesLabels = [];
$typesDataCounts = [];
foreach ($typesDist as $row) {
    $words = explode(' ', $row['type_name']);
    $typesLabels[] = $words[0];
    $typesDataCounts[] = (int)$row['count'];
}
$typesChartJson = json_encode(['labels' => $typesLabels, 'data' => $typesDataCounts]);


if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid action']);
    exit;
}
?>