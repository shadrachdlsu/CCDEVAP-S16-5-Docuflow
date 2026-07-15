<?php
session_start();
header('Content-Type: application/json');

require_once '../config/connections.php';

// Allow only admins
if (!isset($_SESSION['logged_in']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? '';

try {
    if ($action === 'user_distribution') {
        // Get total users first to calculate percentages
        $total = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($total == 0) $total = 1; // avoid division by zero
        
        // Count active users by role
        $stmt = $pdo->query("
            SELECT r.role_name as label, COUNT(u.user_id) as value
            FROM roles r
            LEFT JOIN users u ON r.role_id = u.role_id AND u.is_active = 1
            GROUP BY r.role_name
            ORDER BY value DESC
        ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Add inactive users as a separate category
        $inactive = $pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();
        $rows[] = ['label' => 'Inactive', 'value' => $inactive];
        
        // Colors mapping
        $colors = [
            'Admin' => '#dc2626', // red
            'Secretary' => '#0f766e', // teal
            'Member' => '#4c1d95', // purple
            'Inactive' => '#64748b' // gray
        ];
        
        // Format for JS and calculate percentages
        $formattedRows = [];
        $gradientStops = [];
        $currentPercent = 0;
        
        foreach ($rows as $row) {
            $pct = round(($row['value'] / $total) * 100);
            $color = $colors[$row['label']] ?? '#000000';
            
            // "Members - 52%"
            $label = $row['label'];
            if ($label !== 'Inactive') $label .= 's'; // Admins, Secretaries, Members
            $formattedLabel = "{$label} - {$pct}%";
            
            $formattedRows[] = [
                'label' => $formattedLabel,
                'value' => (string)$row['value'],
                'color' => $color
            ];
            
            // Build conic gradient string
            if ($row['value'] > 0) {
                $endPercent = $currentPercent + $pct;
                // e.g. "#4c1d95 0 52%"
                $gradientStops[] = "{$color} {$currentPercent}% {$endPercent}%";
                $currentPercent = $endPercent;
            }
        }
        
        echo json_encode([
            'title' => 'User Distribution',
            'description' => 'Percentage of users by role.',
            'total' => (string)$total,
            'gradient' => implode(', ', $gradientStops),
            'rows' => $formattedRows
        ]);
    }
    elseif ($action === 'office_directory') {
        $stmt = $pdo->query("
            SELECT o.office_name as name, COUNT(d.document_id) as doc_count
            FROM offices o
            LEFT JOIN documents d ON o.office_id = d.current_office_id
            GROUP BY o.office_name
            ORDER BY o.office_name
        ");
        $offices = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $offices[] = [
                'name' => $row['name'],
                'detail' => $row['doc_count'] . " documents assigned"
            ];
        }
        echo json_encode([
            'title' => 'Office Directory',
            'description' => 'Registered offices and assigned document load.',
            'offices' => $offices
        ]);
    }
    elseif ($action === 'pending_documents') {
        $stmt = $pdo->query("
            SELECT d.title, d.tracking_code as id, o.office_name as office
            FROM documents d
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            WHERE d.status = 'Pending'
            ORDER BY d.created_at DESC
            LIMIT 10
        ");
        $docs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode([
            'title' => 'Pending Documents',
            'description' => 'Documents waiting for action across offices.',
            'documents' => $docs
        ]);
    }
    elseif ($action === 'bottleneck_chart') {
        // Pending docs by office
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
            // Shorten name to first 3 chars for the mini chart
            $labels[] = substr($row['office_name'], 0, 3);
            $data[] = (int)$row['count'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
    }
    elseif ($action === 'volume_trends') {
        // Last 6 months
        $labels = [];
        $data = [];
        for ($i = 5; $i >= 0; $i--) {
            // Calculate date for this iteration
            $date = new DateTime("-$i months");
            $labels[] = $date->format('M'); // Jan, Feb
            
            $month = $date->format('n');
            $year = $date->format('Y');
            
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM documents WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
            $stmt->execute([$month, $year]);
            $data[] = (int)$stmt->fetchColumn();
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
    }
    elseif ($action === 'types_chart') {
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
            // Use short name, e.g. first word
            $words = explode(' ', $row['type_name']);
            $labels[] = $words[0];
            $data[] = (int)$row['count'];
        }
        echo json_encode(['labels' => $labels, 'data' => $data]);
    }
    else {
        echo json_encode(['error' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
