<?php
require_once __DIR__ . '/../config/connections.php';

class Role
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get all roles.
     * @return array
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT role_id, role_name FROM roles ORDER BY role_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findIdByName(string $roleName): ?int
    {
        $statement = $this->pdo->prepare(
            'SELECT role_id FROM roles WHERE role_name = ? LIMIT 1'
        );
        $statement->execute([$roleName]);
        $roleId = $statement->fetchColumn();
        return $roleId === false ? null : (int) $roleId;
    }

    public function findNameById(int $roleId): ?string
    {
        $statement = $this->pdo->prepare(
            'SELECT role_name FROM roles WHERE role_id = ? LIMIT 1'
        );
        $statement->execute([$roleId]);
        $roleName = $statement->fetchColumn();
        return $roleName === false ? null : (string) $roleName;
    }
}
?>
