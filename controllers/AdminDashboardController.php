<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/connections.php';

if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

// Dashboard Counts
try {
    $stats = [
        'total_docs' => $pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn(),
        'active_users' => $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn(),
        'total_offices' => $pdo->query("SELECT COUNT(*) FROM offices")->fetchColumn(),
        'pending_docs' => $pdo->query("SELECT COUNT(*) FROM documents WHERE status = 'Pending'")->fetchColumn()
    ];

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

    $this_month = $pdo->query("SELECT COUNT(*) FROM documents WHERE YEAR(created_at) = YEAR(CURRENT_DATE) AND MONTH(created_at) = MONTH(CURRENT_DATE)")->fetchColumn();
    $last_month = $pdo->query("SELECT COUNT(*) FROM documents WHERE YEAR(created_at) = YEAR(CURRENT_DATE - INTERVAL 1 MONTH) AND MONTH(created_at) = MONTH(CURRENT_DATE - INTERVAL 1 MONTH)")->fetchColumn();
    if ($last_month > 0) {
        $diff = $this_month - $last_month;
        $pct = round(($diff / $last_month) * 100);
        $stats['trend_text'] = ($pct >= 0 ? "+" : "") . $pct . "% this month";
    } else {
        $stats['trend_text'] = "+" . $this_month . " new this month";
    }

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


// Document Distribution Chart
$sql = "SELECT status, COUNT(*) as count FROM documents GROUP BY status";
$results = $pdo->query($sql)->fetchAll();
$labels = [];
$data = [];
foreach ($results as $row) {
    $labels[] = $row['status'];
    $data[] = (int)$row['count'];
}
$docDistJson = json_encode(['labels' => $labels, 'data' => $data]);


// User Distribution
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
if ($totalUsers == 0) $totalUsers = 1;

$userDistRows = $pdo->query("
    SELECT r.role_name as label, COUNT(u.user_id) as value
    FROM roles r
    LEFT JOIN users u ON r.role_id = u.role_id AND u.is_active = 1
    GROUP BY r.role_name
    ORDER BY value DESC
")->fetchAll(PDO::FETCH_ASSOC);

$inactiveUsers = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();
$userDistRows[] = ['label' => 'Inactive', 'value' => $inactiveUsers];

$colors = [
    'Admin' => '#dc2626',
    'Secretary' => '#0f766e',
    'Member' => '#4c1d95',
    'Inactive' => '#64748b'
];

$formattedUserDistRows = [];
$gradientStops = [];
$currentPercent = 0;

foreach ($userDistRows as $row) {
    $pct = round(($row['value'] / $totalUsers) * 100);
    $color = $colors[$row['label']] ?? '#000000';
    
    $label = $row['label'];
    if ($label !== 'Inactive') $label .= 's';
    $formattedLabel = "{$label} - {$pct}%";
    
    $formattedUserDistRows[] = [
        'label' => $formattedLabel,
        'value' => (string)$row['value'],
        'color' => $color
    ];
    
    if ($row['value'] > 0) {
        $endPercent = $currentPercent + $pct;
        $gradientStops[] = "{$color} {$currentPercent}% {$endPercent}%";
        $currentPercent = $endPercent;
    }
}
$userDistGradient = implode(', ', $gradientStops);
$userDistTotal = $totalUsers;

// Office Directory
$officeDirectoryRaw = $pdo->query("
    SELECT o.office_name as name, COUNT(d.document_id) as doc_count
    FROM offices o
    LEFT JOIN documents d ON o.office_id = d.current_office_id
    GROUP BY o.office_name
    ORDER BY o.office_name
")->fetchAll(PDO::FETCH_ASSOC);

$officeDirectory = array_map(function($o) {
    return [
        'name' => $o['name'],
        'detail' => $o['doc_count'] . ' Active Documents'
    ];
}, $officeDirectoryRaw);


// Pending Documents
$pendingDocsList = $pdo->query("
    SELECT d.title, d.tracking_code as id, o.office_name as office
    FROM documents d
    LEFT JOIN offices o ON d.current_office_id = o.office_id
    WHERE d.status = 'Pending'
    ORDER BY d.created_at DESC
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);


// Bottleneck Chart
$stmt = $pdo->query("
    SELECT o.office_name, COUNT(d.document_id) as count
    FROM offices o
    LEFT JOIN documents d ON o.office_id = d.current_office_id AND d.status = 'Pending'
    GROUP BY o.office_name
    ORDER BY o.office_name
");
$labels = [];
$data = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $labels[] = substr($row['office_name'], 0, 3);
    $data[] = (int)$row['count'];
}
$bottleneckChartJson = json_encode(['labels' => $labels, 'data' => $data]);


// Volume Trends Chart
$labels = [];
$data = [];
for ($i = 5; $i >= 0; $i--) {
    $date = new DateTime("-$i months");
    $labels[] = $date->format('M');
    $month = $date->format('n');
    $year = $date->format('Y');
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
    $stmt->execute([$month, $year]);
    $data[] = (int)$stmt->fetchColumn();
}
$trendsChartJson = json_encode(['labels' => $labels, 'data' => $data]);


// Types Chart
$stmt = $pdo->query("
    SELECT dt.type_name, COUNT(d.document_id) as count
    FROM document_types dt
    LEFT JOIN documents d ON dt.type_id = d.type_id
    GROUP BY dt.type_name
    ORDER BY dt.type_name
");
$labels = [];
$data = [];
foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $words = explode(' ', $row['type_name']);
    $labels[] = $words[0];
    $data[] = (int)$row['count'];
}
$typesChartJson = json_encode(['labels' => $labels, 'data' => $data]);


if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid action']);
    exit;
}
?>