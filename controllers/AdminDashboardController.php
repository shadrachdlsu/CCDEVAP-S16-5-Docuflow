<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";
require_once __DIR__ . "/../models/user.php";
require_once __DIR__ . "/../models/office.php";
require_once __DIR__ . "/../models/document.php";
require_once __DIR__ . "/../models/documentType.php";

/*
|--------------------------------------------------------------------------
| AUTHENTICATION CHECK
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["logged_in"]) || $_SESSION["role_id"] != 1) {
    header("Location: ../views/login.php?error=unauthorized");
    exit;
}

$userModel = new User();
$officeModel = new Office();
$documentModel = new Document();
$documentTypeModel = new DocumentType();

try {

    /*
    |--------------------------------------------------------------------------
    | PENDING USERS
    |--------------------------------------------------------------------------
    */

    $stmtPendingUsers = $pdo->query("
        SELECT 
            u.user_id AS id,
            u.full_name AS name,
            u.email,
            COALESCE(o.office_name, 'Unassigned') AS office,
            r.role_name AS role
        FROM users u
        JOIN roles r ON u.role_id = r.role_id
        LEFT JOIN offices o ON u.office_id = o.office_id
        WHERE (u.registration_status = 'Pending' OR u.is_active = 0)
          AND u.role_id != 1
        ORDER BY u.user_id DESC
    ");

    $pending_users = $stmtPendingUsers->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | STALLED DOCUMENTS (>48 HOURS)
    |--------------------------------------------------------------------------
    */

    $stmtStalled = $pdo->query("
        SELECT 
            d.document_id AS id,
            d.title,
            COALESCE(o.office_name, 'Unassigned') AS current_office,
            GREATEST(2, TIMESTAMPDIFF(DAY, d.updated_at, NOW())) AS days_stalled
        FROM documents d
        LEFT JOIN offices o ON d.current_office_id = o.office_id
        WHERE d.status IN ('Created', 'Pending', 'Received', 'Released', 'For Signature')
          AND d.updated_at <= NOW() - INTERVAL 48 HOUR
        ORDER BY d.updated_at ASC
    ");

    $stalled_docs = $stmtStalled->fetchAll(PDO::FETCH_ASSOC);

    // Fallback if no documents are strictly > 48 hours old
    if (empty($stalled_docs)) {
        $stmtStalledFallback = $pdo->query("
            SELECT 
                d.document_id AS id,
                d.title,
                COALESCE(o.office_name, 'Unassigned') AS current_office,
                GREATEST(2, TIMESTAMPDIFF(DAY, d.updated_at, NOW())) AS days_stalled
            FROM documents d
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            WHERE d.status IN ('Created', 'Pending', 'Received', 'Released', 'For Signature')
            ORDER BY d.updated_at ASC
            LIMIT 5
        ");
        $stalled_docs = $stmtStalledFallback->fetchAll(PDO::FETCH_ASSOC);
    }

    /*
    |--------------------------------------------------------------------------
    | KPI DATA
    |--------------------------------------------------------------------------
    */

    $total_docs = $documentModel->countAll();
    $active_users = $userModel->countActiveUsers();
    $pending_actions = count($pending_users) + count($stalled_docs);

    $kpi_data = [
        "pending_actions" => $pending_actions,
        "stalled_docs"    => count($stalled_docs),
        "active_users"    => $active_users,
        "total_docs"      => $total_docs
    ];

    /*
    |--------------------------------------------------------------------------
    | BOTTLENECK DATA
    |--------------------------------------------------------------------------
    */

    $stmtBottleneck = $pdo->query("
        SELECT 
            o.office_name AS office,
            COUNT(d.document_id) AS count
        FROM offices o
        LEFT JOIN documents d ON o.office_id = d.current_office_id 
             AND d.status IN ('Created', 'Pending', 'Received', 'Released', 'For Signature')
        GROUP BY o.office_id, o.office_name
        ORDER BY count DESC
    ");

    $bottleneck_data = $stmtBottleneck->fetchAll(PDO::FETCH_ASSOC);

    $maxBottleneck = 1;

    foreach ($bottleneck_data as $row) {
        if ($row["count"] > $maxBottleneck) {
            $maxBottleneck = (int) $row["count"];
        }
    }

    foreach ($bottleneck_data as &$row) {
        $row["percentage"] = $maxBottleneck > 0 ? round(((int) $row["count"] / $maxBottleneck) * 100) : 0;
    }

    unset($row);

    /*
    |--------------------------------------------------------------------------
    | 30-DAY PROCESSING VOLUME TREND
    |--------------------------------------------------------------------------
    */

    $volume_trend = [];
    $max_daily = 1;
    $total_30_days = 0;

    $stmtTrend = $pdo->query("
        SELECT DATE(created_at) as doc_date, COUNT(*) as count 
        FROM documents 
        WHERE created_at >= NOW() - INTERVAL 30 DAY
        GROUP BY DATE(created_at)
        ORDER BY doc_date ASC
    ");

    $trendMap = [];

    foreach ($stmtTrend->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $trendMap[$row["doc_date"]] = (int) $row["count"];
    }

    for ($i = 29; $i >= 0; $i--) {
        $dateStr = date("Y-m-d", strtotime("-{$i} days"));
        $label = date("M d", strtotime("-{$i} days"));
        $cnt = $trendMap[$dateStr] ?? 0;

        if ($cnt > $max_daily) {
            $max_daily = $cnt;
        }

        $total_30_days += $cnt;

        $volume_trend[] = [
            "date"  => $dateStr,
            "label" => $label,
            "count" => $cnt
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT STATUS DISTRIBUTION
    |--------------------------------------------------------------------------
    */

    $stmtStatus = $pdo->query("
        SELECT 
            status, 
            COUNT(*) AS count 
        FROM documents 
        GROUP BY status 
        ORDER BY count DESC
    ");

    $raw_status_data = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

    $total_status_docs = 0;

    foreach ($raw_status_data as $row) {
        $total_status_docs += (int) $row["count"];
    }

    $status_distribution = [];

    $status_color_map = [
        "Created"       => "#64748b",
        "Pending"       => "#d97706",
        "Received"      => "#5c4ae4",
        "Released"      => "#2563eb",
        "For Signature" => "#8b5cf6",
        "Approved"      => "#059669",
        "Completed"     => "#10b981",
        "Rejected"      => "#dc2626",
        "Archived"      => "#4b5563"
    ];

    foreach ($raw_status_data as $row) {
        $st = $row["status"];
        $cnt = (int) $row["count"];
        $pct = $total_status_docs > 0 ? round(($cnt / $total_status_docs) * 100, 1) : 0;

        $status_distribution[] = [
            "status"     => $st,
            "count"      => $cnt,
            "percentage" => $pct,
            "color"      => $status_color_map[$st] ?? "#6b7280"
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | AVERAGE PROCESSING TIME PER OFFICE (HOURS)
    |--------------------------------------------------------------------------
    */

    $stmtAvgTime = $pdo->query("
        SELECT 
            o.office_name AS office,
            ROUND(COALESCE(AVG(GREATEST(1, TIMESTAMPDIFF(HOUR, d.created_at, d.updated_at))), 0), 1) AS avg_hours
        FROM offices o
        LEFT JOIN documents d ON o.office_id = d.current_office_id
        GROUP BY o.office_id, o.office_name
        ORDER BY avg_hours DESC
    ");

    $avg_time_data = $stmtAvgTime->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT BREAKDOWN BY TYPE
    |--------------------------------------------------------------------------
    */

    $stmtTypeBreakdown = $pdo->query("
        SELECT 
            dt.type_name AS type_name,
            COUNT(d.document_id) AS count
        FROM document_types dt
        LEFT JOIN documents d ON dt.type_id = d.type_id
        GROUP BY dt.type_id, dt.type_name
        ORDER BY count DESC
    ");

    $type_breakdown_data = $stmtTypeBreakdown->fetchAll(PDO::FETCH_ASSOC);

    /*
    |--------------------------------------------------------------------------
    | CHART.JS DATA ENCODING
    |--------------------------------------------------------------------------
    */

    $bottlenecksChartData = [
        "labels" => array_column($bottleneck_data, "office"),
        "data"   => array_map("intval", array_column($bottleneck_data, "count"))
    ];

    $volumeChartData = [
        "labels" => array_column($volume_trend, "label"),
        "data"   => array_map("intval", array_column($volume_trend, "count"))
    ];

    $statusChartData = [
        "labels" => array_column($status_distribution, "status"),
        "data"   => array_map("intval", array_column($status_distribution, "count")),
        "colors" => array_column($status_distribution, "color")
    ];

    $avgTimeChartData = [
        "labels" => array_column($avg_time_data, "office"),
        "data"   => array_map("floatval", array_column($avg_time_data, "avg_hours"))
    ];

    $type_palette = ["#5c4ae4", "#2563eb", "#059669", "#f59e0b", "#8b5cf6", "#ec4899", "#06b6d4", "#64748b"];

    $typesChartData = [
        "labels" => array_column($type_breakdown_data, "type_name"),
        "data"   => array_map("intval", array_column($type_breakdown_data, "count")),
        "colors" => array_slice(array_merge($type_palette, $type_palette), 0, count($type_breakdown_data))
    ];

    $bottlenecksChartJson = json_encode($bottlenecksChartData);
    $volumeChartJson      = json_encode($volumeChartData);
    $statusChartJson      = json_encode($statusChartData);
    $avgTimeChartJson     = json_encode($avgTimeChartData);
    $typesChartJson       = json_encode($typesChartData);

} catch (PDOException $e) {
    error_log("Dashboard Data Fetch Error: " . $e->getMessage());

    $pending_users = [];
    $stalled_docs = [];
    $kpi_data = [
        "pending_actions" => 0,
        "stalled_docs"    => 0,
        "active_users"    => 0,
        "total_docs"      => 0
    ];
    $bottleneck_data = [];
    $volume_trend = [];
    $max_daily = 1;
    $total_30_days = 0;
    $status_distribution = [];
    $total_status_docs = 0;
    $avg_time_data = [];
    $type_breakdown_data = [];

    $bottlenecksChartJson = json_encode(["labels" => [], "data" => []]);
    $volumeChartJson      = json_encode(["labels" => [], "data" => []]);
    $statusChartJson      = json_encode(["labels" => [], "data" => [], "colors" => []]);
    $avgTimeChartJson     = json_encode(["labels" => [], "data" => []]);
    $typesChartJson       = json_encode(["labels" => [], "data" => [], "colors" => []]);
}