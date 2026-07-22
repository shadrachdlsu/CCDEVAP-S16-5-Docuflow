<?php
require_once __DIR__ . '/../config/connections.php';

class DocumentTrail
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Insert a trail entry.
     */
    public function addEntry(int $docId, int $userId, ?int $fromOfficeId, ?int $toOfficeId, string $action, ?string $remarks = null): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO document_trails
            (document_id, action_by_user_id, from_office_id, to_office_id, action_taken, remarks, created_at)
            VALUES (:doc_id, :user_id, :from_office, :to_office, :action, :remarks, NOW())
        ");
        $stmt->execute([
            ':doc_id'       => $docId,
            ':user_id'      => $userId,
            ':from_office'  => $fromOfficeId,
            ':to_office'    => $toOfficeId,
            ':action'       => $action,
            ':remarks'      => $remarks,
        ]);
    }

    /**
     * Get paper trail for a document.
     */
    public function getByDocument(int $docId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.trail_id,
                   t.action_taken AS action,
                   t.remarks,
                   t.created_at,
                   u.full_name AS action_by_name,
                   from_off.office_name AS from_office,
                   to_off.office_name AS to_office
            FROM document_trails t
            JOIN users u ON t.action_by_user_id = u.user_id
            LEFT JOIN offices from_off ON t.from_office_id = from_off.office_id
            LEFT JOIN offices to_off ON t.to_office_id = to_off.office_id
            WHERE t.document_id = :doc_id
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([':doc_id' => $docId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get recent trail entries (for dashboard view).
     */
    public function getRecent(int $limit = 20): array
    {
        $sql = "
            SELECT
                dt.created_at,
                dt.action_taken,
                u.full_name,
                d.status
            FROM document_trails dt
            INNER JOIN users u
                ON dt.action_by_user_id = u.user_id
            INNER JOIN documents d
                ON dt.document_id = d.document_id
            ORDER BY dt.created_at DESC
            LIMIT " . (int)$limit . "
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
