<?php
require_once __DIR__ . '/../config/connections.php';
require_once __DIR__ . '/documentTrail.php';

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

    public function getForDocument(int $documentId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT dr.step_no, office.office_name, signatory.full_name AS signatory_name,
                    dr.status, dr.remarks, dr.acted_at
             FROM document_routes AS dr
             LEFT JOIN offices AS office ON office.office_id = dr.office_id
             LEFT JOIN users AS signatory ON signatory.user_id = dr.signatory_user_id
             WHERE dr.document_id = ?
             ORDER BY dr.step_no ASC, office.office_name ASC'
        );
        $statement->execute([$documentId]);
        return $statement->fetchAll();
    }

    public function getOfficePath(int $documentId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT dr.step_no, office.office_name
             FROM document_routes AS dr
             LEFT JOIN offices AS office ON office.office_id = dr.office_id
             WHERE dr.document_id = ?
             ORDER BY dr.step_no ASC, office.office_name ASC'
        );
        $statement->execute([$documentId]);
        return $statement->fetchAll();
    }

    public function getAddressedToUser(int $userId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT d.document_id, d.tracking_code, d.title, d.file_path,
                    d.status AS document_status, d.created_at, dt.type_name,
                    creator.full_name AS creator_name, dr.status AS route_status
             FROM document_routes AS dr
             INNER JOIN documents AS d ON d.document_id = dr.document_id
             INNER JOIN document_types AS dt ON dt.type_id = d.type_id
             INNER JOIN users AS creator ON creator.user_id = d.creator_id
             WHERE dr.signatory_user_id = ?
             ORDER BY d.created_at DESC'
        );
        $statement->execute([$userId]);
        return $statement->fetchAll();
    }

    public function getAddressedDocument(int $documentId, int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            "SELECT dr.route_id, d.tracking_code, d.title, d.file_path,
                    d.status AS document_status, d.created_at,
                    (SELECT MAX(completed_route.acted_at)
                     FROM document_routes AS completed_route
                     WHERE completed_route.document_id = d.document_id
                       AND completed_route.status IN ('Signed', 'Completed')) AS completed_at,
                    dt.type_name, creator.full_name AS creator_name,
                    office.office_name, dr.status AS route_status, dr.step_no, dr.remarks,
                    CASE WHEN dr.step_no = 0 THEN 'Simultaneous' ELSE 'Sequential' END AS sending_method,
                    CASE
                      WHEN dr.step_no = 0 THEN 1
                      WHEN NOT EXISTS (
                        SELECT 1
                        FROM document_routes AS earlier_route
                        WHERE earlier_route.document_id = d.document_id
                          AND earlier_route.step_no > 0
                          AND earlier_route.step_no < dr.step_no
                          AND earlier_route.status IN ('Waiting', 'Received', 'For Signature')
                      ) THEN 1
                      ELSE 0
                    END AS is_actionable
             FROM document_routes AS dr
             INNER JOIN documents AS d ON d.document_id = dr.document_id
             INNER JOIN document_types AS dt ON dt.type_id = d.type_id
             INNER JOIN users AS creator ON creator.user_id = d.creator_id
             LEFT JOIN offices AS office ON office.office_id = dr.office_id
             WHERE d.document_id = ? AND dr.signatory_user_id = ?
             LIMIT 1"
        );
        $statement->execute([$documentId, $userId]);
        return $statement->fetch() ?: null;
    }

    public function getStatusCountsForCreator(int $creatorId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT dr.status, COUNT(*) AS total
             FROM document_routes AS dr
             INNER JOIN documents AS d ON d.document_id = dr.document_id
             WHERE d.creator_id = ?
             GROUP BY dr.status
             ORDER BY dr.status'
        );
        $statement->execute([$creatorId]);
        return $statement->fetchAll();
    }

    public function getForSecretaryOffice(int $officeId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT dr.route_id, dr.step_no, dr.status AS route_status,
                    d.tracking_code, d.title, d.created_at,
                    COALESCE(dt.type_name, "Unspecified") AS type_name,
                    creator.full_name AS creator_name,
                    assignee.full_name AS assignee_name
             FROM document_routes AS dr
             INNER JOIN documents AS d ON d.document_id = dr.document_id
             LEFT JOIN document_types AS dt ON dt.type_id = d.type_id
             INNER JOIN users AS creator ON creator.user_id = d.creator_id
             LEFT JOIN users AS assignee ON assignee.user_id = dr.signatory_user_id
             WHERE dr.office_id = ?
             ORDER BY d.created_at DESC'
        );
        $statement->execute([$officeId]);
        return $statement->fetchAll();
    }

    public function getSecretaryAssignment(int $routeId, int $officeId, int $secretaryUserId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT dr.route_id, dr.step_no, dr.status AS route_status, dr.signatory_user_id,
                    d.document_id, d.tracking_code, d.title, d.file_path, d.status AS document_status, d.created_at,
                    COALESCE(dt.type_name, "Unspecified") AS type_name,
                    creator.full_name AS creator_name, offices.office_name,
                    assignee.full_name AS assignee_name, assignee.email AS assignee_email
             FROM document_routes AS dr
             INNER JOIN office_secretaries ON office_secretaries.office_id = dr.office_id
             INNER JOIN documents AS d ON d.document_id = dr.document_id
             LEFT JOIN document_types AS dt ON dt.type_id = d.type_id
             INNER JOIN users AS creator ON creator.user_id = d.creator_id
             INNER JOIN offices ON offices.office_id = dr.office_id
             LEFT JOIN users AS assignee ON assignee.user_id = dr.signatory_user_id
             WHERE dr.route_id = ?
               AND dr.office_id = ?
               AND office_secretaries.secretary_user_id = ?
             LIMIT 1'
        );
        $statement->execute([$routeId, $officeId, $secretaryUserId]);
        return $statement->fetch() ?: null;
    }

    public function assignForSecretary(
        int $routeId,
        int $officeId,
        int $secretaryUserId,
        int $memberUserId
    ): void {
        $this->pdo->beginTransaction();

        try {
            $routeStatement = $this->pdo->prepare(
                'SELECT dr.status, dr.document_id, dr.signatory_user_id
                 FROM document_routes AS dr
                 INNER JOIN office_secretaries ON office_secretaries.office_id = dr.office_id
                 WHERE dr.route_id = ?
                   AND dr.office_id = ?
                   AND office_secretaries.secretary_user_id = ?
                 FOR UPDATE'
            );
            $routeStatement->execute([$routeId, $officeId, $secretaryUserId]);
            $route = $routeStatement->fetch();

            if (!$route) {
                throw new DomainException('This document is not assigned to your office.');
            }

            if (in_array((string) $route['status'], ['Signed', 'Rejected', 'Released', 'Skipped', 'Completed'], true)) {
                throw new DomainException('A completed office route cannot be reassigned.');
            }

            $memberName = null;

            if ($memberUserId > 0) {
                $memberStatement = $this->pdo->prepare(
                    "SELECT users.user_id, users.full_name
                     FROM users
                     INNER JOIN roles ON roles.role_id = users.role_id
                     WHERE users.user_id = ?
                       AND users.office_id = ?
                       AND (
                           roles.role_name = 'Member'
                           OR (roles.role_name = 'Secretary' AND users.user_id = ?)
                       )
                       AND users.is_active = 1
                       AND users.registration_status = 'Approved'
                     LIMIT 1"
                );
                $memberStatement->execute([$memberUserId, $officeId, $secretaryUserId]);
                $member = $memberStatement->fetch();

                if (!$member) {
                    throw new DomainException('Select yourself or an active member from your office.');
                }

                $memberName = (string) $member['full_name'];
            }

            $updateStatement = $this->pdo->prepare(
                'UPDATE document_routes SET signatory_user_id = NULLIF(?, 0) WHERE route_id = ?'
            );
            $updateStatement->execute([$memberUserId, $routeId]);

            $touchStatement = $this->pdo->prepare(
                'UPDATE documents SET updated_at = CURRENT_TIMESTAMP WHERE document_id = ?'
            );
            $touchStatement->execute([(int) $route['document_id']]);

            (new DocumentTrail())->addEntry(
                (int) $route['document_id'],
                $secretaryUserId,
                $officeId,
                $officeId,
                $memberUserId > 0 ? 'Assigned' : 'Cancelled',
                $memberUserId > 0
                    ? 'Office route assigned to ' . $memberName . '.'
                    : 'Office route assignment removed.'
            );

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function performUserAction(
        int $routeId,
        int $documentId,
        int $userId,
        string $action,
        string $remarks
    ): string {
        $this->pdo->beginTransaction();

        try {
            $documentStatement = $this->pdo->prepare(
                'SELECT document_id FROM documents WHERE document_id = ? FOR UPDATE'
            );
            $documentStatement->execute([$documentId]);

            if (!$documentStatement->fetchColumn()) {
                throw new DomainException('Document not found.');
            }

            $routeStatement = $this->pdo->prepare(
                'SELECT status, step_no, office_id
                 FROM document_routes
                 WHERE route_id = ? AND document_id = ? AND signatory_user_id = ?
                 FOR UPDATE'
            );
            $routeStatement->execute([$routeId, $documentId, $userId]);
            $route = $routeStatement->fetch();

            if (!$route) {
                throw new DomainException('This route is not assigned to your account.');
            }

            $routeStatus = (string) $route['status'];
            $routeStep = (int) $route['step_no'];
            $routeOfficeId = isset($route['office_id']) ? (int) $route['office_id'] : null;

            if (in_array($routeStatus, ['Signed', 'Rejected', 'Released', 'Skipped', 'Completed'], true)) {
                throw new DomainException('This office assignment has already been completed.');
            }

            if ($routeStep > 0) {
                $earlierStatement = $this->pdo->prepare(
                    "SELECT COUNT(*)
                     FROM document_routes
                     WHERE document_id = ? AND step_no > 0 AND step_no < ?
                       AND status IN ('Waiting', 'Received', 'For Signature')"
                );
                $earlierStatement->execute([$documentId, $routeStep]);

                if ((int) $earlierStatement->fetchColumn() > 0) {
                    throw new DomainException('This office must wait for the previous route step to be completed.');
                }
            }

            if ($action === 'receive') {
                if ($routeStatus !== 'Waiting') {
                    throw new DomainException('Only waiting documents can be marked as received.');
                }

                $statement = $this->pdo->prepare(
                    "UPDATE document_routes
                     SET status = 'Received', acted_at = CURRENT_TIMESTAMP
                     WHERE route_id = ?"
                );
                $statement->execute([$routeId]);

                $statement = $this->pdo->prepare(
                    "UPDATE documents
                     SET status = 'Received', current_office_id = ?, updated_at = CURRENT_TIMESTAMP
                     WHERE document_id = ?"
                );
                $statement->execute([$routeOfficeId, $documentId]);
                $message = 'Document marked as received.';
                $trailAction = 'Received';
                $trailRemarks = 'Document received by the assigned office.';
                $documentFinished = false;
            } elseif ($action === 'reject') {
                $statement = $this->pdo->prepare(
                    "UPDATE document_routes
                     SET status = 'Rejected', remarks = NULLIF(?, ''), acted_at = CURRENT_TIMESTAMP
                     WHERE route_id = ?"
                );
                $statement->execute([$remarks, $routeId]);

                $statement = $this->pdo->prepare(
                    "UPDATE document_routes
                     SET status = 'Skipped'
                     WHERE document_id = ? AND route_id <> ?
                       AND status IN ('Waiting', 'Received', 'For Signature')"
                );
                $statement->execute([$documentId, $routeId]);

                $statement = $this->pdo->prepare(
                    "UPDATE documents
                     SET status = 'Rejected', current_office_id = NULL
                     WHERE document_id = ?"
                );
                $statement->execute([$documentId]);
                $message = 'Document rejected.';
                $trailAction = 'Rejected';
                $trailRemarks = $remarks !== '' ? $remarks : 'Document rejected.';
                $documentFinished = false;
            } else {
                $statement = $this->pdo->prepare(
                    "UPDATE document_routes
                     SET status = 'Signed', remarks = NULLIF(?, ''), acted_at = CURRENT_TIMESTAMP
                     WHERE route_id = ?"
                );
                $statement->execute([$remarks, $routeId]);

                $unfinishedStatement = $this->pdo->prepare(
                    'SELECT status
                     FROM document_routes
                     WHERE document_id = ?
                     FOR UPDATE'
                );
                $unfinishedStatement->execute([$documentId]);
                $unfinishedRouteCount = count(array_filter(
                    $unfinishedStatement->fetchAll(),
                    static fn (array $routeRow): bool => in_array(
                        (string) $routeRow['status'],
                        ['Waiting', 'Received', 'For Signature'],
                        true
                    )
                ));

                if ($unfinishedRouteCount > 0) {
                    $statement = $this->pdo->prepare(
                        "UPDATE documents
                         SET current_office_id = NULL, status = 'Pending'
                         WHERE document_id = ?"
                    );
                    $message = $routeStep > 0
                        ? 'Document signed. The next office can now review it.'
                        : 'Document signed. Other offices can continue reviewing it independently.';
                    $documentFinished = false;
                } else {
                    $statement = $this->pdo->prepare(
                        "UPDATE documents
                         SET status = 'Completed', current_office_id = NULL
                         WHERE document_id = ?"
                    );
                    $message = 'Document signed and completed.';
                    $documentFinished = true;
                }
                $statement->execute([$documentId]);
                $trailAction = 'Signed';
                $trailRemarks = $remarks !== '' ? $remarks : 'Document signed.';
            }

            $trailModel = new DocumentTrail();
            $trailModel->addEntry(
                $documentId,
                $userId,
                $routeOfficeId,
                $routeOfficeId,
                $trailAction,
                $trailRemarks
            );

            if ($documentFinished) {
                $trailModel->addEntry(
                    $documentId,
                    $userId,
                    $routeOfficeId,
                    null,
                    'Finished',
                    'All active office routes were completed.'
                );
            }

            $this->pdo->commit();
            return $message;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
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


    /**
     * Return member report totals based on route steps.
     */
    public function getMemberReportStatistics(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COUNT(*) AS total_route_steps,
                COUNT(DISTINCT document_id) AS total_documents,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected,
                SUM(CASE WHEN status IN ('Waiting', 'Received', 'For Signature') THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'Signed' THEN 1 ELSE 0 END) AS signed,
                SUM(CASE WHEN status IN ('Completed', 'Released') THEN 1 ELSE 0 END) AS completed
            FROM document_routes
            WHERE signatory_user_id = ?
        ");

        $stmt->execute([$userId]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'total_route_steps' => (int) ($data['total_route_steps'] ?? 0),
            'total_documents' => (int) ($data['total_documents'] ?? 0),
            'rejected' => (int) ($data['rejected'] ?? 0),
            'pending' => (int) ($data['pending'] ?? 0),
            'signed' => (int) ($data['signed'] ?? 0),
            'completed' => (int) ($data['completed'] ?? 0)
        ];
    }

    /**
     * Count each route status per office for the member line chart.
     */
    public function getOfficeStatusTrends(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT
                COALESCE(o.office_name, 'No Office') AS office_name,
                dr.status AS route_status,
                COUNT(*) AS total
            FROM document_routes dr
            LEFT JOIN offices o ON o.office_id = dr.office_id
            WHERE dr.signatory_user_id = ?
            GROUP BY o.office_id, o.office_name, dr.status
            ORDER BY office_name, dr.status
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Count all current route statuses for the member pie chart.
     */
    public function getRouteStatusDistribution(int $userId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT status AS route_status, COUNT(*) AS total
            FROM document_routes
            WHERE signatory_user_id = ?
            GROUP BY status
            ORDER BY status
        ");

        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
