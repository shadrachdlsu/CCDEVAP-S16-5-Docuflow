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
        $stmt = $this->pdo->query("SELECT office_id as id, office_name as name FROM offices ORDER BY office_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
