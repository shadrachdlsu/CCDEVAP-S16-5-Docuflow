<?php

require_once __DIR__ . "/../config/connections.php";

/*
|--------------------------------------------------------------------------
| OFFICE MODEL
|--------------------------------------------------------------------------
| Handles all database operations for office departments, secretary
| assignments, member rosters, and document workload tracking.
*/

class Office
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->ensureSchemaUpdated();
    }

    /*
    |--------------------------------------------------------------------------
    | SCHEMA MIGRATION / INITIALIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Automatically ensures missing columns exist on the offices table.
     */
    public function ensureSchemaUpdated(): void
    {
        try {
            $columns = $this->pdo->query("SHOW COLUMNS FROM offices")->fetchAll(PDO::FETCH_COLUMN);

            if (!in_array("office_code", $columns)) {
                $this->pdo->exec("ALTER TABLE offices ADD COLUMN office_code VARCHAR(20) DEFAULT NULL AFTER office_name");
            }

            if (!in_array("location", $columns)) {
                $this->pdo->exec("ALTER TABLE offices ADD COLUMN location VARCHAR(150) DEFAULT NULL AFTER office_code");
            }

            if (!in_array("contact_email", $columns)) {
                $this->pdo->exec("ALTER TABLE offices ADD COLUMN contact_email VARCHAR(100) DEFAULT NULL AFTER location");
            }

            if (!in_array("is_active", $columns)) {
                $this->pdo->exec("ALTER TABLE offices ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER contact_email");
            }

            // Populate default office codes if missing
            $this->pdo->exec("UPDATE offices SET office_code = 'REG' WHERE office_name LIKE '%Registrar%' AND (office_code IS NULL OR office_code = '')");
            $this->pdo->exec("UPDATE offices SET office_code = 'FIN' WHERE office_name LIKE '%Finance%' AND (office_code IS NULL OR office_code = '')");
            $this->pdo->exec("UPDATE offices SET office_code = 'DEAN' WHERE office_name LIKE '%Dean%' AND (office_code IS NULL OR office_code = '')");
            $this->pdo->exec("UPDATE offices SET office_code = 'ITS' WHERE (office_name LIKE '%IT%' OR office_name LIKE '%Tech%') AND (office_code IS NULL OR office_code = '')");
        } catch (Exception $e) {
            // Silently swallow schema check error if table issues exist
        }
    }

    /*
    |--------------------------------------------------------------------------
    | DATA RETRIEVAL METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Get basic office list (legacy compatibility).
     */
    public function getAllOffices(): array
    {
        $stmt = $this->pdo->query("SELECT office_id as id, office_name as name FROM offices ORDER BY office_name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get count of total offices.
     */
    public function countAllOffices(): int
    {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM offices")->fetchColumn();
    }

    /**
     * Get all offices with comprehensive metadata, secretary info, and live metrics.
     */
    public function getAllOfficesDetailed(): array
    {
        $sql = "
            SELECT 
                o.office_id as id,
                o.office_name as name,
                COALESCE(o.office_code, UPPER(LEFT(o.office_name, 4))) as code,
                COALESCE(o.location, 'Main Campus') as location,
                COALESCE(o.contact_email, 'N/A') as contact_email,
                COALESCE(o.is_active, 1) as is_active,
                u.user_id as secretary_id,
                u.full_name as secretary_name,
                u.email as secretary_email,
                (SELECT COUNT(*) FROM users u2 WHERE u2.office_id = o.office_id AND u2.is_active = 1) as member_count,
                (SELECT COUNT(*) FROM documents d WHERE d.current_office_id = o.office_id AND d.status NOT IN ('Approved', 'Completed', 'Archived')) as active_doc_count
            FROM offices o
            LEFT JOIN office_secretaries os ON o.office_id = os.office_id
            LEFT JOIN users u ON os.secretary_user_id = u.user_id
            ORDER BY o.office_name ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get list of users with Secretary role (role_id = 2) for assignment options.
     */
    public function getAvailableSecretaries(): array
    {
        $sql = "
            SELECT 
                u.user_id, 
                u.full_name, 
                u.email,
                o.office_name as current_office
            FROM users u
            LEFT JOIN office_secretaries os ON u.user_id = os.secretary_user_id
            LEFT JOIN offices o ON os.office_id = o.office_id
            WHERE u.role_id = 2 AND u.is_active = 1
            ORDER BY u.full_name ASC
        ";

        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get active document count summary across all offices.
     */
    public function getTotalActiveDocuments(): int
    {
        return (int)$this->pdo->query("
            SELECT COUNT(*) 
            FROM documents 
            WHERE status NOT IN ('Approved', 'Completed', 'Archived')
        ")->fetchColumn();
    }

    /**
     * Legacy method for dashboard card metrics.
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

        return array_map(function ($o) {
            return [
                "name" => $o["name"],
                "detail" => $o["doc_count"] . " Active Documents"
            ];
        }, $officeDirectoryRaw);
    }

    /*
    |--------------------------------------------------------------------------
    | WRITE / MUTATION METHODS
    |--------------------------------------------------------------------------
    */

    /**
     * Create a new office record.
     */
    public function createOffice(
        string $name,
        ?string $code = null,
        ?string $location = null,
        ?string $contactEmail = null,
        int $isActive = 1
    ): void {
        if (empty($code)) {
            $code = strtoupper(substr(preg_replace("/[^A-Za-z0-9]/", "", $name), 0, 4));
        }

        $stmt = $this->pdo->prepare("
            INSERT INTO offices (office_name, office_code, location, contact_email, is_active)
            VALUES (:name, :code, :location, :email, :is_active)
        ");

        $stmt->execute([
            ":name" => $name,
            ":code" => strtoupper($code),
            ":location" => $location,
            ":email" => $contactEmail,
            ":is_active" => $isActive
        ]);
    }

    /**
     * Legacy create compatibility.
     */
    public function create(string $name): void
    {
        $this->createOffice($name);
    }

    /**
     * Update existing office record.
     */
    public function updateOffice(
        int $id,
        string $name,
        ?string $code = null,
        ?string $location = null,
        ?string $contactEmail = null,
        int $isActive = 1
    ): void {
        if (empty($code)) {
            $code = strtoupper(substr(preg_replace("/[^A-Za-z0-9]/", "", $name), 0, 4));
        }

        $stmt = $this->pdo->prepare("
            UPDATE offices 
            SET office_name = :name,
                office_code = :code,
                location = :location,
                contact_email = :email,
                is_active = :is_active
            WHERE office_id = :id
        ");

        $stmt->execute([
            ":name" => $name,
            ":code" => strtoupper($code),
            ":location" => $location,
            ":email" => $contactEmail,
            ":is_active" => $isActive,
            ":id" => $id
        ]);
    }

    /**
     * Legacy update compatibility.
     */
    public function update(int $id, string $name): void
    {
        $stmt = $this->pdo->prepare("UPDATE offices SET office_name = :name WHERE office_id = :id");
        $stmt->execute([":name" => $name, ":id" => $id]);
    }

    /**
     * Toggle active/inactive status of an office.
     */
    public function toggleStatus(int $id, int $isActive): void
    {
        $stmt = $this->pdo->prepare("UPDATE offices SET is_active = :status WHERE office_id = :id");
        $stmt->execute([":status" => $isActive, ":id" => $id]);
    }

    /**
     * Assign or reassign a primary secretary to an office.
     */
    public function assignSecretary(int $officeId, int $secretaryUserId): void
    {
        // First remove existing secretary link for this office if any
        $stmtDel = $this->pdo->prepare("DELETE FROM office_secretaries WHERE office_id = :office_id OR secretary_user_id = :user_id");
        $stmtDel->execute([
            ":office_id" => $officeId,
            ":user_id" => $secretaryUserId
        ]);

        // Insert new secretary link
        $stmtIns = $this->pdo->prepare("INSERT INTO office_secretaries (office_id, secretary_user_id) VALUES (:office_id, :user_id)");
        $stmtIns->execute([
            ":office_id" => $officeId,
            ":user_id" => $secretaryUserId
        ]);

        // Also sync the user's office_id field in users table
        $stmtUser = $this->pdo->prepare("UPDATE users SET office_id = :office_id WHERE user_id = :user_id");
        $stmtUser->execute([
            ":office_id" => $officeId,
            ":user_id" => $secretaryUserId
        ]);
    }

    /**
     * Check dependency counts (users & documents) before deletion.
     */
    public function checkDependencies(int $id): array
    {
        $userCount = (int)$this->pdo->query("SELECT COUNT(*) FROM users WHERE office_id = {$id}")->fetchColumn();
        $docCount = (int)$this->pdo->query("SELECT COUNT(*) FROM documents WHERE current_office_id = {$id}")->fetchColumn();

        return [
            "user_count" => $userCount,
            "doc_count" => $docCount
        ];
    }

    /**
     * Delete an office.
     */
    public function delete(int $id): void
    {
        $stmt = $this->pdo->prepare("DELETE FROM offices WHERE office_id = :id");
        $stmt->execute([":id" => $id]);
    }
}
?>
