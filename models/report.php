<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connections.php';

class Report
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    public function getAdminSummary(): array
    {
        $row = $this->pdo->query(
            "SELECT
                (SELECT COUNT(*) FROM documents) AS total_documents,
                (SELECT COUNT(*) FROM document_routes) AS total_routes,
                (SELECT COUNT(*) FROM users WHERE is_active = 1) AS active_users,
                (SELECT COUNT(*) FROM offices) AS total_offices,
                (SELECT COUNT(*) FROM users WHERE registration_status = 'Pending') AS pending_registrations"
        )->fetch();
        return $row ?: [];
    }

    public function getDocumentTimeline(): array
    {
        return $this->pdo->query(
            'SELECT status, created_at FROM documents ORDER BY created_at'
        )->fetchAll();
    }

    public function getRouteStatusCounts(): array
    {
        return $this->pdo->query(
            'SELECT status, COUNT(*) AS total
             FROM document_routes
             GROUP BY status
             ORDER BY status'
        )->fetchAll();
    }

    public function getOfficeRouteCounts(): array
    {
        return $this->pdo->query(
            'SELECT offices.office_name, COUNT(document_routes.route_id) AS total
             FROM offices
             LEFT JOIN document_routes ON document_routes.office_id = offices.office_id
             GROUP BY offices.office_id, offices.office_name
             ORDER BY total DESC, offices.office_name'
        )->fetchAll();
    }

    public function getOfficeDocumentTimeline(string $fromDate, string $untilDate): array
    {
        $statement = $this->pdo->prepare(
            "SELECT office.office_name,
                    office_documents.month_key,
                    COALESCE(office_documents.total, 0) AS total
             FROM offices AS office
             LEFT JOIN (
                 SELECT creator.office_id,
                        DATE_FORMAT(document.created_at, '%Y-%m') AS month_key,
                        COUNT(document.document_id) AS total
                 FROM documents AS document
                 INNER JOIN users AS creator ON creator.user_id = document.creator_id
                 WHERE document.created_at >= ?
                   AND document.created_at < ?
                 GROUP BY creator.office_id, month_key
             ) AS office_documents ON office_documents.office_id = office.office_id
             ORDER BY office.office_name, office_documents.month_key"
        );
        $statement->execute([$fromDate, $untilDate]);
        return $statement->fetchAll();
    }

    public function getOfficeCompletionDurations(): array
    {
        return $this->pdo->query(
            "SELECT office.office_name,
                    COUNT(completed_route.route_id) AS completed_steps,
                    AVG(completed_route.duration_seconds) AS average_seconds
             FROM offices AS office
             LEFT JOIN (
                 SELECT dr.route_id, dr.office_id,
                        TIMESTAMPDIFF(
                            SECOND,
                            CASE
                              WHEN dr.step_no > 0 THEN COALESCE(
                                  (SELECT MAX(previous_route.acted_at)
                                   FROM document_routes AS previous_route
                                   WHERE previous_route.document_id = dr.document_id
                                     AND previous_route.step_no > 0
                                     AND previous_route.step_no < dr.step_no
                                     AND previous_route.acted_at IS NOT NULL),
                                  document.created_at
                              )
                              ELSE document.created_at
                            END,
                            dr.acted_at
                        ) AS duration_seconds
                 FROM document_routes AS dr
                 INNER JOIN documents AS document ON document.document_id = dr.document_id
                 WHERE document.status = 'Completed'
                   AND dr.status IN ('Signed', 'Completed')
                   AND dr.acted_at IS NOT NULL
             ) AS completed_route ON completed_route.office_id = office.office_id
             GROUP BY office.office_id, office.office_name
             ORDER BY average_seconds DESC, office.office_name"
        )->fetchAll();
    }

    public function getDocumentCreationByOffice(): array
    {
        return $this->pdo->query(
            "SELECT office.office_name,
                    COUNT(DISTINCT user.user_id) AS user_count,
                    COUNT(document.document_id) AS document_count,
                    COALESCE(SUM(document.status = 'Completed'), 0) AS completed_count
             FROM offices AS office
             LEFT JOIN users AS user ON user.office_id = office.office_id
             LEFT JOIN documents AS document ON document.creator_id = user.user_id
             GROUP BY office.office_id, office.office_name
             ORDER BY document_count DESC, office.office_name"
        )->fetchAll();
    }

    public function getDocumentCreationByUser(): array
    {
        return $this->pdo->query(
            "SELECT office.office_name, user.full_name, user.email, role.role_name,
                    COUNT(document.document_id) AS document_count,
                    COALESCE(SUM(document.status = 'Completed'), 0) AS completed_count,
                    MAX(document.created_at) AS latest_document_at
             FROM offices AS office
             INNER JOIN users AS user ON user.office_id = office.office_id
             INNER JOIN roles AS role ON role.role_id = user.role_id
             LEFT JOIN documents AS document ON document.creator_id = user.user_id
             GROUP BY office.office_id, office.office_name, user.user_id,
                      user.full_name, user.email, role.role_name
             ORDER BY office.office_name, document_count DESC, user.full_name"
        )->fetchAll();
    }
}
