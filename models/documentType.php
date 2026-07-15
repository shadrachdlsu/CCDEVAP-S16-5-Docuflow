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
     * Get all document types available to a specific office.
     * (uses the junction table document_type_offices)
     */
    public function getTypesByOffice(int $officeId): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT dt.type_id, dt.type_name, dt.description, dt.is_active
             FROM document_types dt
             JOIN document_type_offices dto ON dt.type_id = dto.type_id
             WHERE dto.office_id = :office_id
             ORDER BY dt.type_name"
        );
        $stmt->execute([':office_id' => $officeId]);
        return $stmt->fetchAll();
    }

    /**
     * Add a new office‑exclusive document type.
     * Inserts into document_types and links to the office via document_type_offices.
     */
    public function addType(int $officeId, string $typeName, ?string $description = null): int
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO document_types (type_name, description, is_active)
                 VALUES (:type_name, :description, 1)"
            );
            $stmt->execute([
                ':type_name'   => $typeName,
                ':description' => $description,
            ]);
            $typeId = (int) $this->pdo->lastInsertId();

            $stmt2 = $this->pdo->prepare(
                "INSERT INTO document_type_offices (type_id, office_id) VALUES (:type_id, :office_id)"
            );
            $stmt2->execute([':type_id' => $typeId, ':office_id' => $officeId]);

            $this->pdo->commit();
            return $typeId;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Update a document type’s name / description.
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
     * Delete a document type and its office links.
     */
    public function deleteType(int $typeId): bool
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("DELETE FROM document_type_offices WHERE type_id = :type_id");
            $stmt->execute([':type_id' => $typeId]);
            $stmt2 = $this->pdo->prepare("DELETE FROM document_types WHERE type_id = :type_id");
            $stmt2->execute([':type_id' => $typeId]);
            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
