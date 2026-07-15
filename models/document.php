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
     * Get documents assigned to an office.
     * @param int    $officeId
     * @param string|null $displayStatus  'Pending', 'Signed', 'Finished', or null for all
     * @return array
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
                   GROUP_CONCAT(DISTINCT ur.full_name ORDER BY ur.full_name SEPARATOR ', ') AS assignee_names,
                   GROUP_CONCAT(DISTINCT ur.email ORDER BY ur.email SEPARATOR ', ') AS assignee_emails
            FROM documents d
            JOIN document_types dt ON d.type_id = dt.type_id
            JOIN users u ON d.creator_id = u.user_id
            LEFT JOIN offices o ON d.current_office_id = o.office_id
            LEFT JOIN document_routes dr ON d.document_id = dr.document_id AND dr.signatory_user_id IS NOT NULL
            LEFT JOIN users ur ON dr.signatory_user_id = ur.user_id
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
     * Get a single document by ID, with all details.
     * @param int $docId
     * @return array|null
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
     * Assign one or more members to a document.
     * @param int   $docId
     * @param int   $assignedByUserId   (secretary user ID)
     * @param array $assigneeUserIds
     * @return void
     */
    public function assignDocument(int $docId, int $assignedByUserId, array $assigneeUserIds): void
    {
        $this->pdo->beginTransaction();
        try {
            $doc = $this->getDocumentById($docId);
            if (!$doc) throw new Exception("Document not found");

            $officeId = $doc['current_office_id'];

            $stepNo = $this->getNextStepNo($docId);
            foreach ($assigneeUserIds as $userId) {
                $stmt = $this->pdo->prepare("
                    INSERT INTO document_routes (document_id, step_no, office_id, recipient_scope, signatory_user_id, status)
                    VALUES (:doc_id, :step_no, :office_id, 'Individual', :user_id, 'Waiting')
                ");
                $stmt->execute([
                    ':doc_id'    => $docId,
                    ':step_no'   => $stepNo,
                    ':office_id' => $officeId,
                    ':user_id'   => $userId,
                ]);
                $stepNo++;
            }

            if ($doc['status'] !== 'Pending') {
                $this->updateDocumentStatus($docId, 'Pending');
            }

            $assigneeNames = $this->getUserNamesByIds($assigneeUserIds);
            $this->addTrailEntry($docId, $assignedByUserId, 'Assigned', 'Assigned to ' . implode(', ', $assigneeNames));

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Forward a document to another office.
     * @param int $docId
     * @param int $userId       (secretary)
     * @param int $targetOfficeId
     * @return void
     */
    public function forwardDocument(int $docId, int $userId, int $targetOfficeId): void
    {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("UPDATE documents SET current_office_id = :office_id WHERE document_id = :doc_id");
            $stmt->execute([':office_id' => $targetOfficeId, ':doc_id' => $docId]);

            $this->updateDocumentStatus($docId, 'Released');

            $officeName = $this->getOfficeNameById($targetOfficeId);
            $this->addTrailEntry($docId, $userId, 'Forwarded', "Forwarded to {$officeName}");

            $this->pdo->commit();
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Mark document as Finished (Completed).
     * @param int $docId
     * @param int $userId
     * @return void
     */
    public function finishDocument(int $docId, int $userId): void
    {
        $this->updateDocumentStatus($docId, 'Completed');
        $this->addTrailEntry($docId, $userId, 'Finished', 'Marked as Finished by Secretary');
    }

    /**
     * Cancel a document.
     * @param int $docId
     * @param int $userId
     * @return void
     */
    public function cancelDocument(int $docId, int $userId): void
    {
        $this->updateDocumentStatus($docId, 'Recalled');
        $this->addTrailEntry($docId, $userId, 'Cancelled', 'Document cancelled by Secretary');
    }

    /**
     * Get paper trail for a document.
     * @param int $docId
     * @return array
     */
    public function getTrail(int $docId): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.trail_id, t.action, t.remarks, t.created_at, u.full_name AS action_by_name
            FROM document_trails t
            JOIN users u ON t.action_by_id = u.user_id
            WHERE t.document_id = :doc_id
            ORDER BY t.created_at ASC
        ");
        $stmt->execute([':doc_id' => $docId]);
        return $stmt->fetchAll();
    }

    //  HELPER METHODS

    private function updateDocumentStatus(int $docId, string $status): void
    {
        $stmt = $this->pdo->prepare("UPDATE documents SET status = :status WHERE document_id = :doc_id");
        $stmt->execute([':status' => $status, ':doc_id' => $docId]);
    }

    public function addTrailEntry(int $docId, int $userId, string $action, ?string $remarks = null): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO document_trails (document_id, action_by_id, action, remarks)
            VALUES (:doc_id, :user_id, :action, :remarks)
        ");
        $stmt->execute([
            ':doc_id'  => $docId,
            ':user_id' => $userId,
            ':action'  => $action,
            ':remarks' => $remarks,
        ]);
    }

    private function getNextStepNo(int $docId): int
    {
        $stmt = $this->pdo->prepare("SELECT MAX(step_no) FROM document_routes WHERE document_id = :doc_id");
        $stmt->execute([':doc_id' => $docId]);
        $max = $stmt->fetchColumn();
        return ($max === false) ? 1 : $max + 1;
    }

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
