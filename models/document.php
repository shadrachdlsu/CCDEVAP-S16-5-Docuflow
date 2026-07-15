<?php
require_once __DIR__ . '/../config/connections.php';

class Document
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    /**
     * Get documents assigned to an office, with optional display‑status filter.
     */
    public function getDocumentsForOffice(int $officeId, ?string $displayStatus = null): array
    {
        $sql = "
            SELECT d.document_id,
                   d.tracking_code,
                   d.title,
                   d.status,
                   d.file_path,
                   dt.type_name,
                   u.full_name AS creator_name,
                   o.office_name AS current_office_name,
                   GROUP_CONCAT(DISTINCT assignee.full_name ORDER BY assignee.full_name SEPARATOR ', ') AS assignee_names,
                   GROUP_CONCAT(DISTINCT assignee.email ORDER BY assignee.email SEPARATOR ', ') AS assignee_emails
            FROM documents d
            JOIN document_types dt ON d.type_id = dt.type_id
            JOIN users u ON d.creator_id = u.user_id
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            LEFT JOIN document_assignments da ON d.document_id = da.document_id AND da.status IN ('Pending','Signed')
            LEFT JOIN users assignee ON da.assigned_to_user_id = assignee.user_id
            WHERE d.current_office_id = :office_id
        ";

        if ($displayStatus !== null) {
            switch ($displayStatus) {
                case 'Pending':
                    $sql .= " AND d.status IN ('Created','Pending','Received','Released','For Signature','Rejected')";
                    break;
                case 'Signed':
                    $sql .= " AND d.status = 'Signed'";
                    break;
                case 'Finished':
                    $sql .= " AND d.status IN ('Completed','Recalled')";
                    break;
            }
        }

        $sql .= " GROUP BY d.document_id ORDER BY d.updated_at DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':office_id' => $officeId]);
        return $stmt->fetchAll();
    }

    /**
     * Get a single document by ID, with details.
     */
    public function getDocumentById(int $docId): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT d.*, dt.type_name, o.office_name AS current_office_name
            FROM documents d
            JOIN document_types dt ON d.type_id = dt.type_id
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            WHERE d.document_id = :doc_id
        ");
        $stmt->execute([':doc_id' => $docId]);
        return $stmt->fetch() ?: null;
    }

    /**
     * Assign one or more members to a document (uses document_assignments).
     */
    public function assignDocument(int $docId, int $assignedByUserId, array $assigneeUserIds): void
    {
        $this->pdo->beginTransaction();
        try {
            $doc = $this->getDocumentById($docId);
            if (!$doc) throw new Exception("Document not found");

            $officeId = $doc['current_office_id'];

            foreach ($assigneeUserIds as $userId) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO document_assignments
                    (document_id, assigned_to_user_id, assigned_by_user_id, office_id, status, assigned_at)
                    VALUES (:doc_id, :to_user, :by_user, :office_id, 'Pending', NOW())
                ");
                $stmt->execute([
                    ':doc_id'   => $docId,
                    ':to_user'  => $userId,
                    ':by_user'  => $assignedByUserId,
                    ':office_id'=> $officeId,
                ]);
            }

            // Update document status to Pending if not already
            if (!in_array($doc['status'], ['Pending', 'For Signature', 'Received', 'Released'])) {
                $this->updateDocumentStatus($docId, 'Pending');
            }

            // Log trail
            $assigneeNames = $this->getUserNamesByIds($assigneeUserIds);
            $this->addTrailEntry($docId, $assignedByUserId, $officeId, null, 'Assigned', 'Assigned to ' . implode(', ', $assigneeNames));

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Forward a document to another office.
     */
    public function forwardDocument(int $docId, int $userId, int $targetOfficeId): void
    {
        $this->pdo->beginTransaction();
        try {
            $doc = $this->getDocumentById($docId);
            $fromOffice = $doc['current_office_id'];

            $stmt = $this->pdo->prepare("UPDATE documents SET current_office_id = :office_id WHERE document_id = :doc_id");
            $stmt->execute([':office_id' => $targetOfficeId, ':doc_id' => $docId]);

            $this->updateDocumentStatus($docId, 'Released');

            $this->addTrailEntry($docId, $userId, $fromOffice, $targetOfficeId, 'Forwarded', "Forwarded to " . $this->getOfficeNameById($targetOfficeId));

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Mark document as Finished (Completed).
     */
    public function finishDocument(int $docId, int $userId): void
    {
        $doc = $this->getDocumentById($docId);
        $this->updateDocumentStatus($docId, 'Completed');
        $this->addTrailEntry($docId, $userId, $doc['current_office_id'], null, 'Finished', 'Marked as Finished by Secretary');
    }

    /**
     * Cancel a document.
     */
    public function cancelDocument(int $docId, int $userId): void
    {
        $doc = $this->getDocumentById($docId);
        $this->updateDocumentStatus($docId, 'Recalled');
        $this->addTrailEntry($docId, $userId, $doc['current_office_id'], null, 'Cancelled', 'Document cancelled by Secretary');
    }

    /**
     * Get paper trail for a document.
     */
    public function getTrail(int $docId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.trail_id,
                   t.action_taken AS action,
                   t.remarks,
                   t.created_at,
                   u.full_name AS action_by_name,
                   from_off.office_name AS from_office,
                   to_off.office_name AS to_office
            FROM document_trails t
            JOIN users u ON t.action_by_user_id = u.user_id
            LEFT JOIN offices from_off ON t.from_office_id = from_off.office_id
            LEFT JOIN offices to_off ON t.to_office_id = to_off.office_id
            WHERE t.document_id = :doc_id
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([':doc_id' => $docId]);
        return $stmt->fetchAll();
    }

    //  PRIVATE / PUBLIC HELPERS

    private function updateDocumentStatus(int $docId, string $status): void
    {
        $stmt = $this->pdo->prepare("UPDATE documents SET status = :status WHERE document_id = :doc_id");
        $stmt->execute([':status' => $status, ':doc_id' => $docId]);
    }

    /**
     * Insert a trail entry.
     */
    public function addTrailEntry(int $docId, int $userId, ?int $fromOfficeId, ?int $toOfficeId, string $action, ?string $remarks = null): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO document_trails
            (document_id, action_by_user_id, from_office_id, to_office_id, action_taken, remarks, created_at)
            VALUES (:doc_id, :user_id, :from_office, :to_office, :action, :remarks, NOW())
        ");
        $stmt->execute([
            ':doc_id'       => $docId,
            ':user_id'      => $userId,
            ':from_office'  => $fromOfficeId,
            ':to_office'    => $toOfficeId,
            ':action'       => $action,
            ':remarks'      => $remarks,
        ]);
    }

    private function getNextStepNo(int $docId): int { /* unused now, kept for compatibility */ return 1; }

    private function getUserNamesByIds(array $userIds): array
    {
        if (empty($userIds)) return [];
        $placeholders = implode(',', array_fill(0, count($userIds), '?'));
        $stmt = $this->pdo->prepare("SELECT full_name FROM users WHERE user_id IN ($placeholders)");
        $stmt->execute($userIds);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    private function getOfficeNameById(int $officeId): string
    {
        $stmt = $this->pdo->prepare("SELECT office_name FROM offices WHERE office_id = :id");
        $stmt->execute([':id' => $officeId]);
        return $stmt->fetchColumn() ?: 'Unknown Office';
    }
}
