<?php
require_once __DIR__ . '/../config/connections.php';

class DocumentType
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get document types accessible by a specific office.
     */
    public function getTypesByOffice(int $officeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT dt.type_id, dt.type_name 
             FROM document_types dt
             JOIN document_type_offices dto ON dt.type_id = dto.type_id
             WHERE dto.office_id = :office_id 
               AND dt.is_active = 1
             ORDER BY dt.type_name"
        );
        $stmt->execute([':office_id' => $officeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all document types.
     */
    public function getAll(): array
    {
        $stmt = $this->pdo->query("SELECT type_id, type_name, is_active FROM document_types ORDER BY type_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get all active document types.
     */
    public function getAllActive(): array
    {
        $sql = "
            SELECT
                type_id,
                type_name
            FROM document_types
            WHERE is_active=1
            ORDER BY type_name
        ";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Check if a document type exists and is active.
     */
    public function typeExists(int $typeId): bool
    {
        $stmt = $this->pdo->prepare("SELECT type_id FROM document_types WHERE type_id = ? AND is_active = 1 LIMIT 1");
        $stmt->execute([$typeId]);
        return (bool)$stmt->fetchColumn();
    }

    /**
     * Get all document types with their assigned offices.
     */
    public function getAllWithOffices(): array
    {
        $stmt = $this->pdo->query("
            SELECT dt.type_id as id, dt.type_name as name, 
                   IF(dt.is_active = 1, 'Active', 'Inactive') as status,
                   GROUP_CONCAT(o.office_name SEPARATOR ', ') as offices
            FROM document_types dt
            LEFT JOIN document_type_offices dto ON dt.type_id = dto.type_id
            LEFT JOIN offices o ON dto.office_id = o.office_id
            GROUP BY dt.type_id
            ORDER BY dt.type_name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create a new document type with assigned offices.
     */
    public function createWithOffices(string $name, array $officeIds, int $isActive = 1): void
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("INSERT INTO document_types (type_name, is_active) VALUES (:name, :is_active)");
            $stmt->execute([':name' => $name, ':is_active' => $isActive]);
            $typeId = $this->pdo->lastInsertId();

            if (!empty($officeIds)) {
                $stmt = $this->pdo->prepare("INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)");
                foreach ($officeIds as $officeId) {
                    $stmt->execute([':type_id' => $typeId, ':office_id' => $officeId]);
                }
            }
            $this->pdo->commit();
        } catch(Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update an existing document type and its assigned offices.
     */
    public function updateWithOffices(int $id, string $name, array $officeIds, int $isActive): void
    {
        try {
            $this->pdo->beginTransaction();
            $stmt = $this->pdo->prepare("UPDATE document_types SET type_name = :name, is_active = :is_active WHERE type_id = :id");
            $stmt->execute([':name' => $name, ':is_active' => $isActive, ':id' => $id]);

            $stmt = $this->pdo->prepare("DELETE FROM document_type_offices WHERE type_id = :id");
            $stmt->execute([':id' => $id]);

            if (!empty($officeIds)) {
                $stmt = $this->pdo->prepare("INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)");
                foreach ($officeIds as $officeId) {
                    $stmt->execute([':type_id' => $id, ':office_id' => $officeId]);
                }
            }
            $this->pdo->commit();
        } catch(Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Delete a document type.
     */
    public function deleteType(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM document_types WHERE type_id = :id");
        $stmt->execute([':id' => $id]);
    }

    /**
     * (Legacy/Secretary) Add a simple type linked to a single office.
     */
    public function addType(string $typeName, int $officeId): void
    {
        try {
            $this->pdo->beginTransaction();
            
            $stmt = $this->pdo->prepare("INSERT INTO document_types (type_name) VALUES (:name)");
            $stmt->execute([':name' => $typeName]);
            $typeId = $this->pdo->lastInsertId();
            
            $stmt = $this->pdo->prepare("INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)");
            $stmt->execute([
                ':type_id'  => $typeId,
                ':office_id' => $officeId
            ]);
            
            $this->pdo->commit();
        } catch(Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * (Legacy/Secretary) Update a simple type name.
     */
    public function updateType(int $typeId, string $typeName): void
    {
        $stmt = $this->pdo->prepare("UPDATE document_types SET type_name = :name WHERE type_id = :type_id");
        $stmt->execute([
            ':name'    => $typeName,
            ':type_id' => $typeId
        ]);
    }
}
?>
