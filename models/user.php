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

    public function approveUser(int $user_id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET is_active = 1, registration_status = 'Approved' 
            WHERE user_id = :id
        ");
        $stmt->execute([':id' => $user_id]);
    }

    public function deactivateUser(int $user_id): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET is_active = 0, registration_status = 'Pending' 
            WHERE user_id = :id
        ");
        $stmt->execute([':id' => $user_id]);
    }

    public function bulkApprove(array $user_ids): void
    {
        if (empty($user_ids)) return;
        $in = implode(',', array_fill(0, count($user_ids), '?'));
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET is_active = 1, registration_status = 'Approved' 
            WHERE user_id IN ($in)
        ");
        $stmt->execute(array_values($user_ids));
    }

    public function bulkUpdateStatus(array $user_ids, string $status): void
    {
        if (empty($user_ids)) return;
        $isActive = ($status === 'Active') ? 1 : 0;
        $regStatus = ($status === 'Active') ? 'Approved' : 'Pending';
        $in = implode(',', array_fill(0, count($user_ids), '?'));
        $params = array_merge([$isActive, $regStatus], array_values($user_ids));
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET is_active = ?, registration_status = ? 
            WHERE user_id IN ($in)
        ");
        $stmt->execute($params);
    }

    public function bulkReassignOffice(array $user_ids, ?int $office_id): void
    {
        if (empty($user_ids)) return;
        $in = implode(',', array_fill(0, count($user_ids), '?'));
        $params = array_merge([$office_id], array_values($user_ids));
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET office_id = ? 
            WHERE user_id IN ($in)
        ");
        $stmt->execute($params);
    }

    public function delete(int $user_id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM users WHERE user_id = :id");
        $stmt->execute([':id' => $user_id]);
    }

    public function getActiveWorkflowsCount(int $user_id): array
    {
        $stmtAssigned = $this->pdo->prepare("SELECT COUNT(*) FROM document_assignments WHERE assigned_to_user_id = ? AND status = 'Pending'");
        $stmtAssigned->execute([$user_id]);
        $assignmentsCount = (int)$stmtAssigned->fetchColumn();

        $stmtRoutes = $this->pdo->prepare("SELECT COUNT(*) FROM document_routes WHERE signatory_user_id = ? AND status IN ('Waiting', 'Received', 'For Signature')");
        $stmtRoutes->execute([$user_id]);
        $routesCount = (int)$stmtRoutes->fetchColumn();

        $stmtSecretary = $this->pdo->prepare("SELECT COUNT(*) FROM office_secretaries WHERE secretary_user_id = ?");
        $stmtSecretary->execute([$user_id]);
        $secretaryCount = (int)$stmtSecretary->fetchColumn();

        $stmtCreated = $this->pdo->prepare("SELECT COUNT(*) FROM documents WHERE creator_id = ? AND status NOT IN ('Completed', 'Rejected', 'Recalled')");
        $stmtCreated->execute([$user_id]);
        $createdCount = (int)$stmtCreated->fetchColumn();

        $total = $assignmentsCount + $routesCount + $secretaryCount + $createdCount;

        return [
            'total' => $total,
            'assignments' => $assignmentsCount,
            'routes' => $routesCount,
            'secretary' => $secretaryCount,
            'created' => $createdCount
        ];
    }

    public function reassignUserWorkflows(int $from_user_id, int $to_user_id): void
    {
        $stmtAssigned = $this->pdo->prepare("UPDATE document_assignments SET assigned_to_user_id = ? WHERE assigned_to_user_id = ? AND status = 'Pending'");
        $stmtAssigned->execute([$to_user_id, $from_user_id]);

        $stmtRoutes = $this->pdo->prepare("UPDATE document_routes SET signatory_user_id = ? WHERE signatory_user_id = ? AND status IN ('Waiting', 'Received', 'For Signature')");
        $stmtRoutes->execute([$to_user_id, $from_user_id]);

        $stmtSecretary = $this->pdo->prepare("UPDATE office_secretaries SET secretary_user_id = ? WHERE secretary_user_id = ?");
        $stmtSecretary->execute([$to_user_id, $from_user_id]);

        $stmtCreated = $this->pdo->prepare("UPDATE documents SET creator_id = ? WHERE creator_id = ? AND status NOT IN ('Completed', 'Rejected', 'Recalled')");
        $stmtCreated->execute([$to_user_id, $from_user_id]);
    }

    public function resetPassword(int $user_id, string $new_password): void
    {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("UPDATE users SET password_hash = :hash WHERE user_id = :id");
        $stmt->execute([':hash' => $password_hash, ':id' => $user_id]);
    }

    public function getUserProfileAndActivity(int $user_id): ?array
    {
        $stmtUser = $this->pdo->prepare("
            SELECT 
                u.user_id,
                u.full_name,
                u.email,
                u.is_active,
                u.registration_status,
                u.created_at,
                r.role_name,
                o.office_name
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN offices o ON u.office_id = o.office_id
            WHERE u.user_id = ?
            LIMIT 1
        ");
        $stmtUser->execute([$user_id]);
        $user = $stmtUser->fetch(PDO::FETCH_ASSOC);

        if (!$user) return null;

        $workflows = $this->getActiveWorkflowsCount($user_id);

        $stmtTotalCreated = $this->pdo->prepare("SELECT COUNT(*) FROM documents WHERE creator_id = ?");
        $stmtTotalCreated->execute([$user_id]);
        $totalCreatedCount = (int)$stmtTotalCreated->fetchColumn();

        $stmtCreatedDocs = $this->pdo->prepare("
            SELECT document_id, tracking_code, title, status, created_at
            FROM documents
            WHERE creator_id = ?
            ORDER BY created_at DESC
            LIMIT 10
        ");
        $stmtCreatedDocs->execute([$user_id]);
        $recentCreated = $stmtCreatedDocs->fetchAll(PDO::FETCH_ASSOC);

        $stmtAssignments = $this->pdo->prepare("
            SELECT da.assignment_id, da.status as assignment_status, da.assigned_at, d.tracking_code, d.title
            FROM document_assignments da
            JOIN documents d ON da.document_id = d.document_id
            WHERE da.assigned_to_user_id = ?
            ORDER BY da.assigned_at DESC
            LIMIT 10
        ");
        $stmtAssignments->execute([$user_id]);
        $recentAssignments = $stmtAssignments->fetchAll(PDO::FETCH_ASSOC);

        return [
            'profile' => [
                'id' => $user['user_id'],
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role_name'],
                'office' => $user['office_name'] ?? 'Unassigned',
                'status' => $user['is_active'] == 1 ? 'Active' : 'Inactive',
                'created_at' => date('M d, Y', strtotime($user['created_at']))
            ],
            'stats' => [
                'total_created' => $totalCreatedCount,
                'pending_assignments' => $workflows['assignments'],
                'routes' => $workflows['routes']
            ],
            'created_documents' => $recentCreated,
            'recent_assignments' => $recentAssignments
        ];
    }

    public function getAllWithRolesAndOffices(): array
    {
        $stmt = $this->pdo->prepare("
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
        $stmt->execute();
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
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 1");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    public function countPendingUsers(): int
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE registration_status = 'Pending'");
        $stmt->execute();
        return (int)$stmt->fetchColumn();
    }

    /**
     * Get user distribution for the dashboard.
     */
    public function getUserDistribution(): array
    {
        $stmtTotal = $this->pdo->prepare("SELECT COUNT(*) FROM users");
        $stmtTotal->execute();
        $totalUsers = $stmtTotal->fetchColumn();
        if ($totalUsers == 0) $totalUsers = 1;

        $stmtRoles = $this->pdo->prepare("
            SELECT r.role_name as label, COUNT(u.user_id) as value
            FROM roles r
            LEFT JOIN users u ON r.role_id = u.role_id AND u.is_active = 1
            GROUP BY r.role_name
            ORDER BY value DESC
        ");
        $stmtRoles->execute();
        $userDistRows = $stmtRoles->fetchAll(PDO::FETCH_ASSOC);

        $stmtInactive = $this->pdo->prepare("SELECT COUNT(*) FROM users WHERE is_active = 0");
        $stmtInactive->execute();
        $inactiveUsers = $stmtInactive->fetchColumn();
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

    /**
     * Get list of non-admin users with pending registration or inactive status for Admin Dashboard.
     */
    public function getPendingRegistrationUsers(): array
    {
        $stmt = $this->pdo->prepare("
            SELECT 
                u.user_id AS id,
                u.full_name AS name,
                u.email,
                COALESCE(o.office_name, 'Unassigned') AS office,
                r.role_name AS role
            FROM users u
            JOIN roles r ON u.role_id = r.role_id
            LEFT JOIN offices o ON u.office_id = o.office_id
            WHERE (u.registration_status = 'Pending' OR u.is_active = 0)
              AND u.role_id != 1
            ORDER BY u.user_id DESC
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Validate password complexity requirements.
     * Returns an error message string if invalid, or null if valid.
     */
    public static function validatePasswordComplexity(string $password): ?string
    {
        if (strlen($password) < 8) {
            return "Password must be at least 8 characters long.";
        }
        if (!preg_match("/[A-Z]/", $password)) {
            return "Password must contain at least one uppercase letter.";
        }
        if (!preg_match("/[a-z]/", $password)) {
            return "Password must contain at least one lowercase letter.";
        }
        if (!preg_match("/[0-9]/", $password)) {
            return "Password must contain at least one number.";
        }
        if (!preg_match("/[\W_]/", $password)) {
            return "Password must contain at least one special character.";
        }
        return null;
    }
}
?>
