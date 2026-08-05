<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/connections.php';

class Setting
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS system_settings (
                setting_key VARCHAR(100) NOT NULL PRIMARY KEY,
                setting_value VARCHAR(255) NOT NULL,
                updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                    ON UPDATE CURRENT_TIMESTAMP
             ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $statement = $this->pdo->prepare(
            "INSERT IGNORE INTO system_settings (setting_key, setting_value)
             VALUES ('require_admin_approval', '1')"
        );
        $statement->execute();
    }

    public function get(string $key, string $default = ''): string
    {
        $statement = $this->pdo->prepare(
            'SELECT setting_value
             FROM system_settings
             WHERE setting_key = ?
             LIMIT 1'
        );
        $statement->execute([$key]);
        $value = $statement->fetchColumn();

        return $value === false ? $default : (string) $value;
    }

    public function set(string $key, string $value): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO system_settings (setting_key, setting_value)
             VALUES (?, ?)
             ON DUPLICATE KEY UPDATE
               setting_value = VALUES(setting_value),
               updated_at = CURRENT_TIMESTAMP'
        );
        $statement->execute([$key, $value]);
    }

    public function requiresAdminApproval(): bool
    {
        return $this->get('require_admin_approval', '1') === '1';
    }

    public function setRequiresAdminApproval(bool $required): void
    {
        $this->set('require_admin_approval', $required ? '1' : '0');
    }
}
