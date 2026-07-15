<?php
// API endpoint
header('Content-Type: application/json'); // JSON for browser

// db connection
require_once '../config/connections.php';

try {
    // count documents grouped by status
    $sql = "SELECT status, COUNT(*) as count FROM documents GROUP BY status";
    
    // prepare and execute
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    
    // fetch all results
    $results = $stmt->fetchAll();

    // separate results to label and data
    $labels = [];
    $data = [];

    // populate arrays
    foreach ($results as $row) {
        $labels[] = $row['status'];
        $data[] = (int)$row['count'];
    }

    // Output JSON
    echo json_encode([
        'labels' => $labels,
        'data' => $data
    ]);

} catch (PDOException $e) {
    // error message JSON
    echo json_encode([
        'error' => true,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
