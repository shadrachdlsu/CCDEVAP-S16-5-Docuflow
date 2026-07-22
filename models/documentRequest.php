<?php
require_once __DIR__ . '/../config/connections.php';

class DocumentRequest
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Create a new document request.
     */
    public function create(int $userId, int $officeId, int $typeId, string $title, string $description): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO document_requests
            (
                requested_by_id,
                office_id,
                type_id,
                title,
                description,
                status,
                created_at
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                ?,
                'Pending',
                NOW()
            )
        ");

        $stmt->execute([
            $userId,
            $officeId,
            $typeId,
            $title,
            $description
        ]);
    }

    /**
     * Get all requests by a specific user.
     */
    public function getByUser(int $userId): array
    {
        $sql = "
            SELECT
                dr.request_id,
                dr.title,
                dr.status,
                dt.type_name
            FROM document_requests dr
            LEFT JOIN document_types dt
                ON dr.type_id = dt.type_id
            WHERE dr.requested_by_id = ?
            ORDER BY dr.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count total requests made by a specific user.
     */
    public function countByUser(int $userId): int
    {
        $sql = "
            SELECT COUNT(*) total
            FROM document_requests
            WHERE requested_by_id=?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Delete a pending request for a user.
     * Returns true if deleted, false if not found or not pending.
     */
    public function deletePending(int $requestId, int $userId): bool
    {
        $stmt = $this->pdo->prepare("
            DELETE FROM document_requests
            WHERE request_id = ?
              AND requested_by_id = ?
              AND status = 'Pending'
        ");

        $stmt->execute([
            $requestId,
            $userId
        ]);
        
        return $stmt->rowCount() > 0;
    }
}
?>
