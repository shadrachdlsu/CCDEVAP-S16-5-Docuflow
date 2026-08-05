<?php
require_once __DIR__ . '/../config/connections.php';

class User
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get all members belonging to a specific office
     */
    public function getMembersByOffice(int $officeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT user_id, full_name, email 
             FROM users 
             WHERE role_id = 3 
               AND office_id = :office_id 
               AND is_active = 1
             ORDER BY full_name"
        );
        $stmt->execute([':office_id' => $officeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare("SELECT u.user_id, u.role_id, u.full_name, u.email, u.password_hash, u.is_active, u.registration_status, u.office_id, o.office_name FROM users u LEFT JOIN offices o ON u.office_id = o.office_id WHERE u.email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findLoginAccount(string $email): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT u.user_id, u.full_name, u.email, u.password_hash, u.office_id,
                    u.is_active, u.registration_status, r.role_name
             FROM users AS u
             INNER JOIN roles AS r ON r.role_id = u.role_id
             WHERE u.email = ?
             LIMIT 1'
        );
        $statement->execute([$email]);
        return $statement->fetch() ?: null;
    }

    public function getAdminList(): array
    {
        return $this->pdo->query(
            'SELECT u.user_id, u.full_name, u.email, u.is_active, u.registration_status, u.created_at,
                    role.role_name, office.office_name
             FROM users AS u
             INNER JOIN roles AS role ON role.role_id = u.role_id
             LEFT JOIN offices AS office ON office.office_id = u.office_id
             ORDER BY u.full_name'
        )->fetchAll();
    }

    public function getPendingRegistrations(int $limit = 8): array
    {
        $limit = max(1, min($limit, 50));

        return $this->pdo->query(
            'SELECT u.user_id, u.full_name, u.email, u.created_at,
                    role.role_name,
                    COALESCE(office.office_name, "Unassigned") AS office_name
             FROM users AS u
             INNER JOIN roles AS role ON role.role_id = u.role_id
             LEFT JOIN offices AS office ON office.office_id = u.office_id
             WHERE u.registration_status = "Pending"
               AND role.role_name <> "Admin"
             ORDER BY u.created_at ASC
             LIMIT ' . $limit
        )->fetchAll();
    }

    public function countPendingRegistrations(): int
    {
        return (int) $this->pdo->query(
            'SELECT COUNT(*)
             FROM users AS u
             INNER JOIN roles AS role ON role.role_id = u.role_id
             WHERE u.registration_status = "Pending"
               AND role.role_name <> "Admin"'
        )->fetchColumn();
    }

    public function decideRegistration(int $userId, string $decision): void
    {
        if (!in_array($decision, ['approve', 'reject'], true)) {
            throw new InvalidArgumentException('The registration decision is invalid.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE users AS u
             INNER JOIN roles AS role ON role.role_id = u.role_id
             SET u.is_active = ?, u.registration_status = ?
             WHERE u.user_id = ?
               AND u.registration_status = "Pending"
               AND role.role_name <> "Admin"'
        );
        $statement->execute([
            $decision === 'approve' ? 1 : 0,
            $decision === 'approve' ? 'Approved' : 'Rejected',
            $userId,
        ]);

        if ($statement->rowCount() === 0) {
            throw new DomainException('This registration is no longer pending.');
        }
    }

    public function getAdminUser(int $userId): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT user_id, full_name, email, role_id, office_id,
                    is_active, registration_status, created_at
             FROM users
             WHERE user_id = ?
             LIMIT 1'
        );
        $statement->execute([$userId]);
        return $statement->fetch() ?: null;
    }

    public function getApprovedSecretariesByOffice(int $officeId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT users.user_id, users.full_name, users.email
             FROM users
             INNER JOIN roles ON roles.role_id = users.role_id
             WHERE users.office_id = ?
               AND roles.role_name = 'Secretary'
               AND users.is_active = 1
               AND users.registration_status = 'Approved'
             ORDER BY users.full_name"
        );
        $statement->execute([$officeId]);
        return $statement->fetchAll();
    }

    public function getAssignableOfficeMembers(int $officeId, int $secretaryUserId): array
    {
        $statement = $this->pdo->prepare(
            "SELECT users.user_id, users.full_name, users.email
             FROM users
             INNER JOIN roles ON roles.role_id = users.role_id
             WHERE users.office_id = ?
               AND (roles.role_name = 'Member' OR users.user_id = ?)
               AND users.is_active = 1
               AND users.registration_status = 'Approved'
             ORDER BY users.full_name"
        );
        $statement->execute([$officeId, $secretaryUserId]);
        return $statement->fetchAll();
    }

    public function updateFromAdmin(
        int $userId,
        string $fullName,
        string $email,
        int $roleId,
        ?int $officeId,
        string $accountStatus
    ): void {
        $isActive = $accountStatus === 'Active' ? 1 : 0;
        $registrationStatus = match ($accountStatus) {
            'Pending' => 'Pending',
            'Rejected' => 'Rejected',
            default => 'Approved',
        };

        $this->pdo->beginTransaction();

        try {
            $roleStatement = $this->pdo->prepare(
                'SELECT role_name FROM roles WHERE role_id = ? LIMIT 1'
            );
            $roleStatement->execute([$roleId]);
            $roleName = $roleStatement->fetchColumn();

            if (!$roleName) {
                throw new DomainException('The selected role could not be found.');
            }

            $statement = $this->pdo->prepare(
                'UPDATE users
                 SET full_name = ?, email = ?, role_id = ?, office_id = ?,
                     is_active = ?, registration_status = ?
                 WHERE user_id = ?'
            );
            $statement->execute([
                $fullName,
                $email,
                $roleId,
                $officeId,
                $isActive,
                $registrationStatus,
                $userId,
            ]);

            $secretaryAssignmentIsValid = $roleName === 'Secretary'
                && $officeId !== null
                && $accountStatus === 'Active';

            if ($secretaryAssignmentIsValid) {
                $clearStatement = $this->pdo->prepare(
                    'DELETE FROM office_secretaries
                     WHERE secretary_user_id = ? AND office_id <> ?'
                );
                $clearStatement->execute([$userId, $officeId]);
            } else {
                $clearStatement = $this->pdo->prepare(
                    'DELETE FROM office_secretaries WHERE secretary_user_id = ?'
                );
                $clearStatement->execute([$userId]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function setActiveFromAdmin(int $userId, bool $isActive): void
    {
        $this->pdo->beginTransaction();

        try {
            $statement = $this->pdo->prepare(
                "UPDATE users
                 SET is_active = ?, registration_status = 'Approved'
                 WHERE user_id = ?"
            );
            $statement->execute([$isActive ? 1 : 0, $userId]);

            if ($statement->rowCount() === 0) {
                $existsStatement = $this->pdo->prepare(
                    'SELECT user_id FROM users WHERE user_id = ? LIMIT 1'
                );
                $existsStatement->execute([$userId]);

                if (!$existsStatement->fetchColumn()) {
                    throw new DomainException('The user account could not be found.');
                }
            }

            if (!$isActive) {
                $assignmentStatement = $this->pdo->prepare(
                    'DELETE FROM office_secretaries WHERE secretary_user_id = ?'
                );
                $assignmentStatement->execute([$userId]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->pdo->prepare("SELECT user_id FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        return (bool) $stmt->fetchColumn();
    }

    public function create(int $role_id, ?int $office_id, string $full_name, string $email, string $password_hash, int $is_active = 0, string $registration_status = 'Pending'): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (role_id, office_id, full_name, email, password_hash, is_active, registration_status) 
            VALUES (:role_id, :office_id, :full_name, :email, :password_hash, :is_active, :registration_status)
        ");
        $stmt->execute([
            'role_id' => $role_id,
            'office_id' => $office_id,
            'full_name' => $full_name,
            'email' => $email,
            'password_hash' => $password_hash,
            'is_active' => $is_active,
            'registration_status' => $registration_status
        ]);
    }

    public function updatePasswordHash(int $userId, string $passwordHash): void
    {
        if ($passwordHash === '' || strlen($passwordHash) > 255) {
            throw new InvalidArgumentException('The password hash is invalid.');
        }

        $statement = $this->pdo->prepare(
            'UPDATE users SET password_hash = ? WHERE user_id = ?'
        );
        $statement->execute([$passwordHash, $userId]);
    }

    public function getDeletionDependencies(int $userId): array
    {
        $references = [
            'created_documents' => ['documents', 'creator_id'],
            'document_requests' => ['document_requests', 'requested_by_id'],
            'route_assignments' => ['document_routes', 'signatory_user_id'],
            'document_history' => ['document_trails', 'action_by_user_id'],
            'secretary_assignments' => ['office_secretaries', 'secretary_user_id'],
            'assignments_received' => ['document_assignments', 'assigned_to_user_id'],
            'assignments_created' => ['document_assignments', 'assigned_by_user_id'],
        ];
        $dependencies = [];

        foreach ($references as $key => [$table, $column]) {
            $dependencies[$key] = $this->countReferences($table, $column, $userId);
        }

        $dependencies['total'] = array_sum($dependencies);

        return $dependencies;
    }

    public function deleteSafely(int $userId): void
    {
        $this->pdo->beginTransaction();

        try {
            $lockStatement = $this->pdo->prepare(
                'SELECT users.user_id, users.is_active, roles.role_name
                 FROM users
                 INNER JOIN roles ON roles.role_id = users.role_id
                 WHERE users.user_id = ?
                 FOR UPDATE'
            );
            $lockStatement->execute([$userId]);
            $targetUser = $lockStatement->fetch();

            if (!$targetUser) {
                throw new DomainException('The user account could not be found.');
            }

            if ($targetUser['role_name'] === 'Admin' && (bool) $targetUser['is_active']) {
                $adminCount = (int) $this->pdo->query(
                    'SELECT COUNT(*)
                     FROM users
                     INNER JOIN roles ON roles.role_id = users.role_id
                     WHERE roles.role_name = "Admin"
                       AND users.is_active = 1
                       AND users.registration_status = "Approved"'
                )->fetchColumn();

                if ($adminCount <= 1) {
                    throw new DomainException('The last active administrator account cannot be deleted.');
                }
            }

            $dependencies = $this->getDeletionDependencies($userId);

            if ($dependencies['total'] > 0) {
                throw new DomainException(
                    'This user has document or office activity and cannot be deleted. Deactivate the account instead.'
                );
            }

            $statement = $this->pdo->prepare(
                'DELETE FROM users WHERE user_id = ?'
            );
            $statement->execute([$userId]);
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
        int $userId
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
        $statement->execute([$userId]);

        return (int) $statement->fetchColumn();
    }

    public function update(int $user_id, int $role_id, ?int $office_id, string $full_name, string $email, ?string $password_hash, int $is_active): void
    {
        $registration_status = ($is_active == 1) ? 'Approved' : 'Pending';

        if ($password_hash !== null) {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET role_id = :role_id, office_id = :office_id, full_name = :name, 
                    email = :email, password_hash = :password_hash, is_active = :is_active, registration_status = :registration_status
                WHERE user_id = :id
            ");
            $stmt->execute([
                ':role_id' => $role_id,
                ':office_id' => $office_id,
                ':name' => $full_name,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':is_active' => $is_active,
                ':registration_status' => $registration_status,
                ':id' => $user_id
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET role_id = :role_id, office_id = :office_id, full_name = :name, 
                    email = :email, is_active = :is_active, registration_status = :registration_status
                WHERE user_id = :id
            ");
            $stmt->execute([
                ':role_id' => $role_id,
                ':office_id' => $office_id,
                ':name' => $full_name,
                ':email' => $email,
                ':is_active' => $is_active,
                ':registration_status' => $registration_status,
                ':id' => $user_id
            ]);
        }
    }

    public function delete(int $user_id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);
    }

    public function getAllWithRolesAndOffices(): array
    {
        $stmt = $this->pdo->query("
            SELECT 
                u.user_id as id, 
                u.full_name as name, 
                u.email, 
                r.role_id,
                r.role_name as role,
                o.office_id,
                o.office_name as office,
                IF(u.is_active = 1, 'Active', 'Inactive') as status
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN offices o ON u.office_id = o.office_id
            ORDER BY u.full_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $user_id): ?array
    {
        $sql = "
        SELECT
            u.user_id,
            u.full_name,
            u.email,
            o.office_name,
            r.role_name
        FROM users u
        LEFT JOIN offices o
            ON u.office_id = o.office_id
        INNER JOIN roles r
            ON u.role_id = r.role_id
        WHERE u.user_id = ?
        LIMIT 1
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserOfficeId(int $user_id): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT office_id
            FROM users
            WHERE user_id = ?
              AND is_active = 1
              AND registration_status = 'Approved'
            LIMIT 1
        ");
        $stmt->execute([$user_id]);
        $officeId = $stmt->fetchColumn();
        return $officeId ? (int)$officeId : null;
    }

    public function findSecretaryByEmail(string $email): ?int
    {
        $stmt = $this->pdo->prepare("
            SELECT u.user_id
            FROM users u
            INNER JOIN roles r
                ON u.role_id = r.role_id
            WHERE u.email = ?
              AND r.role_name = 'Secretary'
              AND u.is_active = 1
              AND u.registration_status = 'Approved'
            LIMIT 1
        ");
        $stmt->execute([$email]);
        $secretaryId = $stmt->fetchColumn();
        return $secretaryId ? (int)$secretaryId : null;
    }

    public function countActiveUsers(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 1")->fetchColumn();
    }

    /**
     * Get user distribution for the dashboard.
     */
    public function getUserDistribution(): array
    {
        $totalUsers = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($totalUsers == 0) $totalUsers = 1;

        $userDistRows = $this->pdo->query("
            SELECT r.role_name as label, COUNT(u.user_id) as value
            FROM roles r
            LEFT JOIN users u ON r.role_id = u.role_id AND u.is_active = 1
            GROUP BY r.role_name
            ORDER BY value DESC
        ")->fetchAll(PDO::FETCH_ASSOC);

        $inactiveUsers = $this->pdo->query("SELECT COUNT(*) FROM users WHERE is_active = 0")->fetchColumn();
        $userDistRows[] = ['label' => 'Inactive', 'value' => $inactiveUsers];

        $colors = [
            'Admin' => '#dc2626',
            'Secretary' => '#0f766e',
            'Member' => '#4c1d95',
            'Inactive' => '#64748b'
        ];

        $formattedUserDistRows = [];
        $gradientStops = [];
        $currentPercent = 0;

        foreach ($userDistRows as $row) {
            $pct = round(($row['value'] / $totalUsers) * 100);
            $color = $colors[$row['label']] ?? '#000000';
            
            $label = $row['label'];
            if ($label !== 'Inactive') $label .= 's';
            $formattedLabel = "{$label} - {$pct}%";
            
            $formattedUserDistRows[] = [
                'label' => $formattedLabel,
                'value' => (string)$row['value'],
                'color' => $color
            ];
            
            if ($row['value'] > 0) {
                $endPercent = $currentPercent + $pct;
                $gradientStops[] = "{$color} {$currentPercent}% {$endPercent}%";
                $currentPercent = $endPercent;
            }
        }
        $userDistGradient = implode(', ', $gradientStops);
        
        return [
            'total' => $totalUsers,
            'rows' => $formattedUserDistRows,
            'gradient' => $userDistGradient
        ];
    }
}
?>
