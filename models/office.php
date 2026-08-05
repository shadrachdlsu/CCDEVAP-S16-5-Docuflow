<?php
require_once __DIR__ . '/../config/connections.php';

class Office
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->ensureStatusColumn();
    }

    private function ensureStatusColumn(): void
    {
        $statement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND COLUMN_NAME = ?'
        );
        $statement->execute(['offices', 'is_active']);

        if (!(bool) $statement->fetchColumn()) {
            $this->pdo->exec(
                'ALTER TABLE offices
                 ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1'
            );
        }
    }

    /**
     * Get all offices.
     * @return array
     */
    public function getAllOffices(): array
    {
        $stmt = $this->pdo->query("SELECT office_id as id, office_name as name FROM offices ORDER BY office_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAll(): array
    {
        return $this->pdo->query(
            'SELECT office_id, office_name, is_active
             FROM offices
             ORDER BY office_name'
        )->fetchAll();
    }

    public function getActive(): array
    {
        return $this->pdo->query(
            'SELECT office_id, office_name
             FROM offices
             WHERE is_active = 1
             ORDER BY office_name'
        )->fetchAll();
    }

    public function getAdminDirectory(): array
    {
        return $this->pdo->query(
            'SELECT office.office_id, office.office_name, office.is_active,
                    secretary.full_name AS secretary_name,
                    COUNT(DISTINCT office_user.user_id) AS user_count,
                    COUNT(DISTINCT document_routes.route_id) AS route_count
             FROM offices AS office
             LEFT JOIN office_secretaries AS office_secretary ON office_secretary.office_id = office.office_id
             LEFT JOIN users AS secretary ON secretary.user_id = office_secretary.secretary_user_id
             LEFT JOIN users AS office_user ON office_user.office_id = office.office_id
             LEFT JOIN document_routes ON document_routes.office_id = office.office_id
             GROUP BY office.office_id, office.office_name, office.is_active, secretary.full_name
             ORDER BY office.office_name'
        )->fetchAll();
    }

    public function getAdminOffice(int $officeId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT office.office_id, office.office_name, office.is_active,
                    office_secretary.secretary_user_id,
                    COUNT(DISTINCT office_user.user_id) AS user_count,
                    COUNT(DISTINCT document_routes.route_id) AS route_count
             FROM offices AS office
             LEFT JOIN office_secretaries AS office_secretary ON office_secretary.office_id = office.office_id
             LEFT JOIN users AS office_user ON office_user.office_id = office.office_id
             LEFT JOIN document_routes ON document_routes.office_id = office.office_id
             WHERE office.office_id = ?
             GROUP BY office.office_id, office.office_name, office.is_active,
                      office_secretary.secretary_user_id
             LIMIT 1'
        );
        $statement->execute([$officeId]);
        return $statement->fetch() ?: null;
    }

    public function getSecretaryContext(int $officeId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT offices.office_name, office_secretaries.secretary_user_id
             FROM offices
             LEFT JOIN office_secretaries ON office_secretaries.office_id = offices.office_id
             WHERE offices.office_id = ?
             LIMIT 1'
        );
        $statement->execute([$officeId]);
        return $statement->fetch() ?: null;
    }

    public function getManagedSecretaryOffice(int $officeId, int $secretaryUserId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT offices.office_name
             FROM office_secretaries
             INNER JOIN offices ON offices.office_id = office_secretaries.office_id
             WHERE office_secretaries.office_id = ?
               AND office_secretaries.secretary_user_id = ?
             LIMIT 1'
        );
        $statement->execute([$officeId, $secretaryUserId]);
        return $statement->fetch() ?: null;
    }

    public function saveWithSecretary(int $officeId, string $officeName, int $secretaryUserId): int
    {
        $this->pdo->beginTransaction();

        try {
            if ($officeId > 0) {
                $officeStatement = $this->pdo->prepare(
                    'SELECT office_id FROM offices WHERE office_id = ? LIMIT 1'
                );
                $officeStatement->execute([$officeId]);

                if (!$officeStatement->fetchColumn()) {
                    throw new DomainException('The office could not be found.');
                }

                if ($secretaryUserId > 0) {
                    $secretaryStatement = $this->pdo->prepare(
                        "SELECT users.user_id
                         FROM users
                         INNER JOIN roles ON roles.role_id = users.role_id
                         WHERE users.user_id = ?
                           AND users.office_id = ?
                           AND roles.role_name = 'Secretary'
                           AND users.is_active = 1
                           AND users.registration_status = 'Approved'
                         LIMIT 1"
                    );
                    $secretaryStatement->execute([$secretaryUserId, $officeId]);

                    if (!$secretaryStatement->fetchColumn()) {
                        throw new DomainException('Select an active Secretary account from this office.');
                    }
                }

                $statement = $this->pdo->prepare(
                    'UPDATE offices SET office_name = ? WHERE office_id = ?'
                );
                $statement->execute([$officeName, $officeId]);

                if ($secretaryUserId > 0) {
                    $assignmentStatement = $this->pdo->prepare(
                        'INSERT INTO office_secretaries (office_id, secretary_user_id)
                         VALUES (?, ?)
                         ON DUPLICATE KEY UPDATE
                           secretary_user_id = VALUES(secretary_user_id),
                           assigned_at = CURRENT_TIMESTAMP'
                    );
                    $assignmentStatement->execute([$officeId, $secretaryUserId]);
                } else {
                    $assignmentStatement = $this->pdo->prepare(
                        'DELETE FROM office_secretaries WHERE office_id = ?'
                    );
                    $assignmentStatement->execute([$officeId]);
                }
            } else {
                $statement = $this->pdo->prepare(
                    'INSERT INTO offices (office_name) VALUES (?)'
                );
                $statement->execute([$officeName]);
                $officeId = (int) $this->pdo->lastInsertId();
            }

            $this->pdo->commit();
            return $officeId;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function countAllOffices(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM offices")->fetchColumn();
    }

    /**
     * Create a new office.
     */
    public function create(string $name): void
    {
        $stmt = $this->pdo->prepare("INSERT INTO offices (office_name) VALUES (:name)");
        $stmt->execute([':name' => $name]);
    }

    /**
     * Update an office.
     */
    public function update(int $id, string $name): void
    {
        $stmt = $this->pdo->prepare("UPDATE offices SET office_name = :name WHERE office_id = :id");
        $stmt->execute([':name' => $name, ':id' => $id]);
    }

    /**
     * Delete an office.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM offices WHERE office_id = :id");
        $stmt->execute([':id' => $id]);
    }

    public function setActive(int $officeId, bool $isActive): void
    {
        $statement = $this->pdo->prepare(
            'UPDATE offices SET is_active = ? WHERE office_id = ?'
        );
        $statement->execute([$isActive ? 1 : 0, $officeId]);

        if ($statement->rowCount() === 0) {
            $existsStatement = $this->pdo->prepare(
                'SELECT office_id FROM offices WHERE office_id = ? LIMIT 1'
            );
            $existsStatement->execute([$officeId]);

            if (!$existsStatement->fetchColumn()) {
                throw new DomainException('The office could not be found.');
            }
        }
    }

    public function getDeletionDependencies(int $officeId): array
    {
        $references = [
            'users' => ['users', 'office_id'],
            'current_documents' => ['documents', 'current_office_id'],
            'document_routes' => ['document_routes', 'office_id'],
            'document_requests' => ['document_requests', 'office_id'],
            'assignment_records' => ['document_assignments', 'office_id'],
            'history_from_office' => ['document_trails', 'from_office_id'],
            'history_to_office' => ['document_trails', 'to_office_id'],
            'document_types' => ['document_type_offices', 'office_id'],
            'secretary_assignments' => ['office_secretaries', 'office_id'],
        ];
        $dependencies = [];

        foreach ($references as $key => [$table, $column]) {
            $dependencies[$key] = $this->countReferences($table, $column, $officeId);
        }

        $dependencies['total'] = array_sum($dependencies);

        return $dependencies;
    }

    public function deleteSafely(int $officeId): void
    {
        $this->pdo->beginTransaction();

        try {
            $lockStatement = $this->pdo->prepare(
                'SELECT office_id FROM offices WHERE office_id = ? FOR UPDATE'
            );
            $lockStatement->execute([$officeId]);

            if (!$lockStatement->fetchColumn()) {
                throw new DomainException('The office could not be found.');
            }

            $dependencies = $this->getDeletionDependencies($officeId);

            if ($dependencies['total'] > 0) {
                throw new DomainException(
                    'This office is used by users, documents, routes, or history and cannot be deleted. Deactivate it instead.'
                );
            }

            $statement = $this->pdo->prepare(
                'DELETE FROM offices WHERE office_id = ?'
            );
            $statement->execute([$officeId]);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function countReferences(
        string $table,
        string $column,
        int $officeId
    ): int {
        $tableStatement = $this->pdo->prepare(
            'SELECT COUNT(*)
             FROM information_schema.TABLES
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?'
        );
        $tableStatement->execute([$table]);

        if (!(bool) $tableStatement->fetchColumn()) {
            return 0;
        }

        $statement = $this->pdo->prepare(
            "SELECT COUNT(*) FROM `{$table}` WHERE `{$column}` = ?"
        );
        $statement->execute([$officeId]);

        return (int) $statement->fetchColumn();
    }

    /**
     * Get offices with their active document counts.
     */
    public function getOfficesWithDocCounts(): array
    {
        $officeDirectoryRaw = $this->pdo->query("
            SELECT o.office_name as name, COUNT(d.document_id) as doc_count
            FROM offices o
            LEFT JOIN documents d ON o.office_id = d.current_office_id
            GROUP BY o.office_name
            ORDER BY o.office_name
        ")->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function($o) {
            return [
                'name' => $o['name'],
                'detail' => $o['doc_count'] . ' Active Documents'
            ];
        }, $officeDirectoryRaw);
    }
}
?>
