<?php
require_once __DIR__ . '/../config/connections.php';

class DocumentRoute
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Count routes for a signatory by status.
     */
    public function countBySignatoryAndStatus(int $userId, string $status): int
    {
        $sql = "
            SELECT COUNT(*) total
            FROM document_routes
            WHERE signatory_user_id = ?
            AND status = ?
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId, $status]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Get pending documents for a signatory.
     */
    public function getPendingForSignatory(int $userId): array
    {
        $sql = "
            SELECT
                d.document_id,
                d.tracking_code,
                d.title,
                dt.type_name,
                o.office_name,
                dr.status,
                d.file_path
            FROM document_routes dr
            INNER JOIN documents d
                ON dr.document_id = d.document_id
            INNER JOIN document_types dt
                ON d.type_id = dt.type_id
            LEFT JOIN offices o
                ON d.current_office_id = o.office_id
            WHERE dr.signatory_user_id = ?
            AND dr.status = 'Waiting'
            ORDER BY d.created_at DESC
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Mark a route as Signed. Returns true if updated, false if not eligible.
     */
    public function signRoute(int $documentId, int $userId, string $remarks): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE document_routes
            SET
                status = 'Signed',
                remarks = ?,
                acted_at = NOW()
            WHERE document_id = ?
              AND signatory_user_id = ?
              AND status IN (
                  'Waiting',
                  'Pending',
                  'Received',
                  'For Signature'
              )
        ");

        $stmt->execute([
            $remarks,
            $documentId,
            $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Mark a route as Rejected. Returns true if updated, false if not eligible.
     */
    public function rejectRoute(int $documentId, int $userId, string $remarks): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE document_routes
            SET
                status = 'Rejected',
                remarks = ?,
                acted_at = NOW()
            WHERE document_id = ?
              AND signatory_user_id = ?
              AND status IN (
                  'Waiting',
                  'Pending',
                  'Received',
                  'For Signature'
              )
        ");

        $stmt->execute([
            $remarks,
            $documentId,
            $userId
        ]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Check how many unsigned routes remain for a document.
     */
    public function countRemainingUnsigned(int $documentId): int
    {
        $check = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM document_routes
            WHERE document_id = ?
              AND status NOT IN (
                  'Signed',
                  'Completed',
                  'Skipped'
              )
        ");

        $check->execute([$documentId]);
        return (int) $check->fetchColumn();
    }

    /**
     * Get all routes (reports) for a signatory.
     */
    public function getRoutesForSignatory(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT DISTINCT
                d.document_id,
                d.tracking_code,
                d.title,
                dt.type_name,
                COALESCE(o.office_name, 'No Office') AS office_name,
                d.created_at,
                d.file_path,
                dr.status AS route_status,
                d.status AS document_status,
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
                END AS computed_status
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
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get aggregated statistics for a signatory's routes.
     */
    public function getStatisticsForSignatory(int $userId): array
    {
        $stmt = $this->pdo->prepare("
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
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total' => 0, 'pending' => 0, 'signed' => 0, 'finished' => 0
        ];
    }
}
?>
