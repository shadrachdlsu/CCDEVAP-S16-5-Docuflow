<?php
require_once __DIR__ . '/../config/connections.php';

class Office
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get all offices.
     * @return array
     */
    public function getAllOffices(): array
    {
        $stmt = $this->pdo->query("SELECT office_id, office_name FROM offices ORDER BY office_name");
        return $stmt->fetchAll();
    }
}
