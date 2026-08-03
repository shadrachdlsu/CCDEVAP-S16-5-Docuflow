<?php

require_once __DIR__ . "/../config/connections.php";

/*
|--------------------------------------------------------------------------
| SETTING MODEL
|--------------------------------------------------------------------------
| Handles system-wide configuration settings stored in the system_settings
| key-value database table.
*/

class Setting
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
    | SCHEMA INITIALIZATION
    |--------------------------------------------------------------------------
    */

    /**
     * Ensures system_settings table exists and is populated with default configuration
     */
    private function ensureSchemaUpdated(): void
    {
        try {
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS system_settings (
                    setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
                    setting_value VARCHAR(255) NOT NULL,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Seed default require_admin_approval setting if not exists
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM system_settings WHERE setting_key = 'require_admin_approval'");
            $stmt->execute();

            if ($stmt->fetchColumn() == 0) {
                $insertStmt = $this->pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES ('require_admin_approval', '1')");
                $insertStmt->execute();
            }
        } catch (PDOException $e) {
            error_log("Setting schema update error: " . $e->getMessage());
        }
    }

    /*
    |--------------------------------------------------------------------------
    | SETTING GETTERS & SETTERS
    |--------------------------------------------------------------------------
    */

    /**
     * Get a setting value by key
     */
    public function getSetting(string $key, string $default = "1"): string
    {
        try {
            $stmt = $this->pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = :key LIMIT 1");
            $stmt->execute([":key" => $key]);
            $val = $stmt->fetchColumn();

            return ($val !== false) ? (string)$val : $default;
        } catch (PDOException $e) {
            error_log("Error fetching setting '$key': " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Set or update a setting value by key
     */
    public function setSetting(string $key, string $value): bool
    {
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO system_settings (setting_key, setting_value) 
                VALUES (:key, :value) 
                ON DUPLICATE KEY UPDATE setting_value = :value_update
            ");

            return $stmt->execute([
                ":key"          => $key,
                ":value"        => $value,
                ":value_update" => $value
            ]);
        } catch (PDOException $e) {
            error_log("Error saving setting '$key': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if new user registrations require admin approval
     */
    public function isRequireAdminApproval(): bool
    {
        return $this->getSetting("require_admin_approval", "1") === "1";
    }

    /**
     * Enable or disable requiring admin approval for new user registrations
     */
    public function setRequireAdminApproval(bool $enabled): bool
    {
        return $this->setSetting("require_admin_approval", $enabled ? "1" : "0");
    }
}
