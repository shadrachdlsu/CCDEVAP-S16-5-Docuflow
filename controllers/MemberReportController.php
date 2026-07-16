<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/../config/connections.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../views/login.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];

$action = $_GET["action"] ?? null;

if ($action !== null) {

    header("Content-Type: application/json; charset=utf-8");

    if ($action === "reports") {

        $stmt = $pdo->prepare("
            SELECT DISTINCT
                d.document_id,
                d.tracking_code,
                d.title,
                dt.type_name,
                COALESCE(o.office_name, 'No Office') AS office_name,
                d.created_at,
                d.file_path,
                CASE
                    WHEN dr.status IN ('Waiting', 'Received', 'For Signature')
                        THEN 'Pending'
                    WHEN dr.status = 'Signed'
                        THEN 'Signed'
                    WHEN dr.status = 'Completed'
                        OR d.status = 'Completed'
                        THEN 'Finished'
                    WHEN dr.status = 'Rejected'
                        THEN 'Rejected'
                    ELSE dr.status
                END AS status
            FROM document_routes dr
            INNER JOIN documents d
                ON dr.document_id = d.document_id
            INNER JOIN document_types dt
                ON d.type_id = dt.type_id
            LEFT JOIN offices o
                ON dr.office_id = o.office_id
            WHERE dr.signatory_user_id = ?
            ORDER BY d.created_at DESC
        ");

        $stmt->execute([$userId]);

        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($action === "statistics") {

        $stmt = $pdo->prepare("
            SELECT
                COUNT(DISTINCT d.document_id) AS total,

                COUNT(DISTINCT CASE
                    WHEN dr.status IN (
                        'Waiting',
                        'Received',
                        'For Signature'
                    )
                    THEN d.document_id
                END) AS pending,

                COUNT(DISTINCT CASE
                    WHEN dr.status = 'Signed'
                    THEN d.document_id
                END) AS signed,

                COUNT(DISTINCT CASE
                    WHEN dr.status = 'Completed'
                        OR d.status = 'Completed'
                    THEN d.document_id
                END) AS finished

            FROM document_routes dr

            INNER JOIN documents d
                ON dr.document_id = d.document_id

            WHERE dr.signatory_user_id = ?
        ");

        $stmt->execute([$userId]);

        echo json_encode($stmt->fetch());
        exit;
    }

    if ($action === "types") {

        $stmt = $pdo->query("
            SELECT
                type_id,
                type_name
            FROM document_types
            WHERE is_active = 1
            ORDER BY type_name
        ");

        echo json_encode($stmt->fetchAll());
        exit;
    }

    http_response_code(400);

    echo json_encode([
        "success" => false,
        "message" => "Invalid report action."
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| MEMBER INFORMATION
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        u.user_id,
        u.full_name,
        u.email,
        r.role_name,
        o.office_name
    FROM users u
    INNER JOIN roles r
        ON u.role_id = r.role_id
    LEFT JOIN offices o
        ON u.office_id = o.office_id
    WHERE u.user_id = ?
    LIMIT 1
");

$stmt->execute([$userId]);

$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: ../views/login.php");
    exit;
}

/*
|--------------------------------------------------------------------------
| REPORT DOCUMENTS
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT DISTINCT
        d.document_id,
        d.tracking_code,
        d.title,
        dt.type_name,
        COALESCE(o.office_name, 'No Office') AS office_name,
        d.created_at,
        d.file_path,
        dr.status AS route_status,
        d.status AS document_status
    FROM document_routes dr
    INNER JOIN documents d
        ON dr.document_id = d.document_id
    INNER JOIN document_types dt
        ON d.type_id = dt.type_id
    LEFT JOIN offices o
        ON dr.office_id = o.office_id
    WHERE dr.signatory_user_id = ?
    ORDER BY d.created_at DESC
");

$stmt->execute([$userId]);

$reportDocuments = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalDocuments = count($reportDocuments);
$pendingDocuments = 0;
$signedDocuments = 0;
$finishedDocuments = 0;

foreach ($reportDocuments as $document) {
    $routeStatus = $document["route_status"];
    $documentStatus = $document["document_status"];

    if (
        $routeStatus === "Waiting" ||
        $routeStatus === "Received" ||
        $routeStatus === "For Signature"
    ) {
        $pendingDocuments++;
    }

    if ($routeStatus === "Signed") {
        $signedDocuments++;
    }

    if (
        $routeStatus === "Completed" ||
        $documentStatus === "Completed"
    ) {
        $finishedDocuments++;
    }
}

$chartData = [
    $pendingDocuments,
    $signedDocuments,
    $finishedDocuments
];