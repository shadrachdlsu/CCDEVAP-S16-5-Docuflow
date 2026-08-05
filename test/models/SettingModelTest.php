<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/setting.php';

/*
|--------------------------------------------------------------------------
| SETTING MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class SettingModelTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private $backupPdo;

    protected function setUp(): void
    {
        global $pdo;
        $this->backupPdo = $pdo;

        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);

        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->willReturn(true);

        $pdo = $this->pdoMock;
    }

    protected function tearDown(): void
    {
        global $pdo;
        $pdo = $this->backupPdo;
    }

    public function testGetSettingReturnsValueAndDefaultFallback(): void
    {
        $this->stmtMock->method('fetchColumn')->willReturnOnConsecutiveCalls(1, '1', false);

        $settingModel = new Setting();
        
        $val1 = $settingModel->getSetting('require_admin_approval');
        $this->assertEquals('1', $val1);

        $val2 = $settingModel->getSetting('nonexistent_key', 'default_val');
        $this->assertEquals('default_val', $val2);
    }

    public function testSetSettingExecutesUpsert(): void
    {
        $settingModel = new Setting();
        $result = $settingModel->setSetting("require_admin_approval", "0");

        $this->assertTrue($result);
    }

    public function testIsRequireAdminApprovalBooleanHelper(): void
    {
        $this->stmtMock->method('fetchColumn')->willReturn('1');

        $settingModel = new Setting();
        $this->assertTrue($settingModel->isRequireAdminApproval());
    }
}
