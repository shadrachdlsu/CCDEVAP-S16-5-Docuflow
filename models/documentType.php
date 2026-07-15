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
     * Get all document types belonging to a specific office.
     * @param int $officeId
     * @return array
     */
    public function getTypesByOffice(int $officeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT type_id, type_name, description, is_active 
             FROM document_types 
             WHERE office_id = :office_id 
             ORDER BY type_name"
        );
        $stmt->execute([':office_id' => $officeId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a new office‑exclusive document type.
     * @param int    $officeId
     * @param string $typeName
     * @param string|null $description
     * @return int  The ID of the newly inserted type.
     */
    public function addType(int $officeId, string $typeName, ?string $description = null): int
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO document_types (type_name, description, is_active, office_id) 
             VALUES (:type_name, :description, 1, :office_id)"
        );
        $stmt->execute([
            ':type_name'   => $typeName,
            ':description' => $description,
            ':office_id'   => $officeId,
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Update an existing document type.
     * @param int    $typeId
     * @param string $typeName
     * @param string|null $description
     * @return bool
     */
    public function updateType(int $typeId, string $typeName, ?string $description = null): bool
    {
        $stmt = $this->pdo->prepare(
            "UPDATE document_types 
             SET type_name = :type_name, description = :description 
             WHERE type_id = :type_id"
        );
        return $stmt->execute([
            ':type_name'   => $typeName,
            ':description' => $description,
            ':type_id'     => $typeId,
        ]);
    }

    /**
     * Delete a document type.
     * @param int $typeId
     * @return bool
     */
    public function deleteType(int $typeId): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM document_types WHERE type_id = :type_id");
        return $stmt->execute([':type_id' => $typeId]);
    }
}
