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
        if ($password_hash !== null) {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET role_id = :role_id, office_id = :office_id, full_name = :name, 
                    email = :email, password_hash = :password_hash, is_active = :is_active 
                WHERE user_id = :id
            ");
            $stmt->execute([
                ':role_id' => $role_id,
                ':office_id' => $office_id,
                ':name' => $full_name,
                ':email' => $email,
                ':password_hash' => $password_hash,
                ':is_active' => $is_active,
                ':id' => $user_id
            ]);
        } else {
            $stmt = $this->pdo->prepare("
                UPDATE users 
                SET role_id = :role_id, office_id = :office_id, full_name = :name, 
                    email = :email, is_active = :is_active 
                WHERE user_id = :id
            ");
            $stmt->execute([
                ':role_id' => $role_id,
                ':office_id' => $office_id,
                ':name' => $full_name,
                ':email' => $email,
                ':is_active' => $is_active,
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
