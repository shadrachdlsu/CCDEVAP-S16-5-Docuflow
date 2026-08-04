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
        $stmt = $this->pdo->prepare("SELECT role_id, role_name FROM roles ORDER BY role_name");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
