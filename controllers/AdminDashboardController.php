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

if (!isset($_SESSION["user_id"]) || ($_SESSION["role"] ?? "") !== "Admin") {
    header("Location: ../views/login.php");
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

    $pending_users = $userModel->getPendingRegistrationUsers();

    /*
    |--------------------------------------------------------------------------
    | STALLED DOCUMENTS (>48 HOURS)
    |--------------------------------------------------------------------------
    */

    $stalled_docs = $documentModel->getStalledDocuments();

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

    $bottleneck_data = $documentModel->getBottleneckWorkloadByOffice();

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

    $trendRows = $documentModel->getDailyVolumeTrend30Days();

    $trendMap = [];

    foreach ($trendRows as $row) {
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

    $raw_status_data = $documentModel->getRawStatusCounts();

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

    $avg_time_data = $documentModel->getAverageProcessingTimePerOffice();

    /*
    |--------------------------------------------------------------------------
    | DOCUMENT BREAKDOWN BY TYPE
    |--------------------------------------------------------------------------
    */

    $type_breakdown_data = $documentTypeModel->getTypeBreakdown();

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