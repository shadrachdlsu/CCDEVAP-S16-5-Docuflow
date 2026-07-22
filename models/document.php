<?php
require_once __DIR__ . '/../config/connections.php';

class Document
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get documents assigned to an office, with optional display‑status filter.
     */
    public function getDocumentsForOffice(int $officeId, ?string $displayStatus = null): array
    {
        $sql = "
            SELECT d.document_id,
                   d.tracking_code,
                   d.title,
                   d.status,
                   d.file_path,
                   dt.type_name,
                   u.full_name AS creator_name,
                   o.office_name AS current_office_name,
                   GROUP_CONCAT(DISTINCT assignee.full_name ORDER BY assignee.full_name SEPARATOR ', ') AS assignee_names,
                   GROUP_CONCAT(DISTINCT assignee.email ORDER BY assignee.email SEPARATOR ', ') AS assignee_emails
            FROM documents d
            JOIN document_types dt ON d.type_id = dt.type_id
            JOIN users u ON d.creator_id = u.user_id
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            LEFT JOIN document_assignments da ON d.document_id = da.document_id AND da.status IN ('Pending','Signed')
            LEFT JOIN users assignee ON da.assigned_to_user_id = assignee.user_id
            WHERE d.current_office_id = :office_id
        ";

        if ($displayStatus !== null) {
            switch ($displayStatus) {
                case 'Pending':
                    $sql .= " AND d.status IN ('Created','Pending','Received','Released','For Signature','Rejected')";
                    break;
                case 'Signed':
                    $sql .= " AND d.status = 'Signed'";
                    break;
                case 'Finished':
                    $sql .= " AND d.status IN ('Completed','Recalled')";
                    break;
            }
        }

        $sql .= " GROUP BY d.document_id ORDER BY d.updated_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':office_id' => $officeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get a single document by ID, with details.
     */
    public function getDocumentById(int $docId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, dt.type_name, o.office_name AS current_office_name
            FROM documents d
            JOIN document_types dt ON d.type_id = dt.type_id
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            WHERE d.document_id = :doc_id
        ");
        $stmt->execute([':doc_id' => $docId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Create a new document. Returns the inserted document_id.
     */
    public function create(string $trackingCode, string $title, ?string $filePath, int $typeId, int $creatorId, int $officeId): int
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO documents (tracking_code, title, file_path, type_id, requires_signature, creator_id, current_office_id, status)
            VALUES (:tracking, :title, :file, :type_id, 1, :creator, :office, 'Created')
        ");
        $stmt->execute([
            ':tracking' => $trackingCode,
            ':title'    => $title,
            ':file'     => $filePath,
            ':type_id'  => $typeId,
            ':creator'  => $creatorId,
            ':office'   => $officeId,
        ]);
        
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Assign one or more members to a document
     */
    public function assignSignatory(int $docId, int $userId): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO document_routes (document_id, signatory_user_id, status, created_at)
            VALUES (?, ?, 'Waiting', NOW())
        ");
        $stmt->execute([$docId, $userId]);
    }

    /**
     * Forward a document to another office.
     */
    public function forwardDocument(int $docId, int $targetOfficeId): void
    {
        $stmt = $this->pdo->prepare("UPDATE documents SET current_office_id = :office_id, status = 'Released' WHERE document_id = :doc_id");
        $stmt->execute([':office_id' => $targetOfficeId, ':doc_id' => $docId]);
    }

    /**
     * Update document status.
     */
    public function updateStatus(int $docId, string $status): void
    {
        $stmt = $this->pdo->prepare("UPDATE documents SET status = :status, updated_at = NOW() WHERE document_id = :doc_id");
        $stmt->execute([':status' => $status, ':doc_id' => $docId]);
    }
    
    /**
     * Update file path (for uploaded signed documents).
     */
    public function updateFilePath(int $docId, string $filePath): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE documents
            SET file_path = ?, updated_at = CURRENT_TIMESTAMP
            WHERE document_id = ?
        ");
        $stmt->execute([$filePath, $docId]);
    }

    /**
     * Get all documents with summary details for Admin view.
     */
    public function getAllSummary(): array
    {
        $sql = "
            SELECT d.document_id as id, d.tracking_code as tracking, d.title, 
                   dt.type_name as type, o.office_name as office, d.status, d.created_at
            FROM documents d
            JOIN document_types dt ON d.type_id = dt.type_id
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            ORDER BY d.created_at DESC
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count all documents.
     */
    public function countAll(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM documents")->fetchColumn();
    }

    /**
     * Count documents by status.
     */
    public function countByStatus(string $status): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM documents WHERE status = ?");
        $stmt->execute([$status]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Count documents by creator ID and status (for member finished docs).
     */
    public function countByCreator(int $creatorId, string $status): int
    {
        $sql = "
            SELECT COUNT(*) total
            FROM documents
            WHERE creator_id=?
            AND status=?
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$creatorId, $status]);
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get document counts per month for the current year.
     */
    public function countByMonth(): array
    {
        $sql = "
            SELECT MONTH(created_at) as month, COUNT(*) as count 
            FROM documents 
            WHERE YEAR(created_at) = YEAR(CURRENT_DATE()) 
            GROUP BY MONTH(created_at) 
            ORDER BY month
        ";
        $result = $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        
        $monthlyCounts = array_fill(1, 12, 0);
        foreach ($result as $row) {
            $monthlyCounts[$row['month']] = (int)$row['count'];
        }
        return array_values($monthlyCounts);
    }

    /**
     * Get distribution of document statuses.
     */
    public function getStatusDistribution(): array
    {
        $totalDocs = $this->countAll();
        if ($totalDocs == 0) $totalDocs = 1;

        $docDistRows = $this->pdo->query("
            SELECT status as label, COUNT(document_id) as value 
            FROM documents 
            GROUP BY status 
            ORDER BY value DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $colors = [
            'Pending' => '#ca8a04',
            'Created' => '#ca8a04',
            'Received' => '#0284c7',
            'Released' => '#0284c7',
            'For Signature' => '#ca8a04',
            'Signed' => '#16a34a',
            'Completed' => '#16a34a',
            'Rejected' => '#dc2626',
            'Recalled' => '#dc2626'
        ];

        $formattedDocDistRows = [];
        $gradientStops = [];
        $currentPercent = 0;

        foreach ($docDistRows as $row) {
            $pct = round(($row['value'] / $totalDocs) * 100);
            $color = $colors[$row['label']] ?? '#64748b';
            $formattedDocDistRows[] = [
                'label' => "{$row['label']} - {$pct}%",
                'value' => (string)$row['value'],
                'color' => $color
            ];
            if ($row['value'] > 0) {
                $endPercent = $currentPercent + $pct;
                $gradientStops[] = "{$color} {$currentPercent}% {$endPercent}%";
                $currentPercent = $endPercent;
            }
        }
        
        return [
            'total' => $totalDocs,
            'rows' => $formattedDocDistRows,
            'gradient' => implode(', ', $gradientStops)
        ];
    }
    
    /**
     * Get bottleneck data (offices with most pending documents).
     */
    public function getBottleneckData(): array
    {
        $bottleneck = $this->pdo->query("
            SELECT o.office_name, COUNT(*) as count 
            FROM documents d 
            JOIN offices o ON d.current_office_id = o.office_id 
            WHERE d.status = 'Pending' 
            GROUP BY d.current_office_id 
            ORDER BY count DESC 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC);
        
        $topBottlenecks = $this->pdo->query("
            SELECT o.office_name, COUNT(*) as count 
            FROM documents d 
            JOIN offices o ON d.current_office_id = o.office_id 
            WHERE d.status = 'Pending' 
            GROUP BY d.current_office_id 
            ORDER BY count DESC 
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);

        return [
            'primary' => $bottleneck,
            'list' => $topBottlenecks
        ];
    }

    /**
     * Get type distribution.
     */
    public function getTypeDistribution(): array
    {
        return $this->pdo->query("
            SELECT dt.type_name, COUNT(d.document_id) as count
            FROM document_types dt
            LEFT JOIN documents d ON dt.type_id = d.type_id
            GROUP BY dt.type_id
            ORDER BY count DESC
            LIMIT 5
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getRecentPending(int $limit = 10): array
    {
        return $this->pdo->query("
            SELECT d.title, d.tracking_code as id, o.office_name as office
            FROM documents d
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            WHERE d.status = 'Pending'
            ORDER BY d.created_at DESC
            LIMIT " . $limit . "
        ")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTrendData(int $months = 6): array
    {
        $labels = [];
        $data = [];
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = new DateTime("-{$i} months");
            $labels[] = $date->format('M');
            $month = $date->format('n');
            $year = $date->format('Y');
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM documents WHERE MONTH(created_at) = ? AND YEAR(created_at) = ?");
            $stmt->execute([$month, $year]);
            $data[] = (int)$stmt->fetchColumn();
        }
        return ['labels' => $labels, 'data' => $data];
    }
    
    public function getTopType(): ?array
    {
        return $this->pdo->query("
            SELECT dt.type_name, COUNT(*) as count 
            FROM documents d 
            JOIN document_types dt ON d.type_id = dt.type_id 
            GROUP BY d.type_id 
            ORDER BY count DESC 
            LIMIT 1
        ")->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    public function getMonthlyDocCount(int $month, int $year): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM documents WHERE YEAR(created_at) = ? AND MONTH(created_at) = ?");
        $stmt->execute([$year, $month]);
        return (int)$stmt->fetchColumn();
    }
}
?>
