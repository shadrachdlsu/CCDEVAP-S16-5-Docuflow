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
     * @param int $officeId
     * @return array
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
        return $stmt->fetchAll();
    }
}
