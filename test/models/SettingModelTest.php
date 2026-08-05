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

        $pdo = $this->pdoMock;
    }

    protected function tearDown(): void
    {
        global $pdo;
        $pdo = $this->backupPdo;
    }

    public function testGetReturnsValueAndDefaultFallback(): void
    {
        $getStatement = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('exec');

        $this->pdoMock->expects($this->exactly(3))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls(
                $this->stmtMock,
                $getStatement,
                $getStatement
            );

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $getStatement->expects($this->exactly(2))
            ->method('execute')
            ->withConsecutive(
                [['require_admin_approval']],
                [['nonexistent_key']]
            );

        $getStatement->expects($this->exactly(2))
            ->method('fetchColumn')
            ->willReturnOnConsecutiveCalls('1', false);

        $settingModel = new Setting();

        $val1 = $settingModel->get('require_admin_approval');
        $this->assertEquals('1', $val1);

        $val2 = $settingModel->get('nonexistent_key', 'default_val');
        $this->assertEquals('default_val', $val2);
    }

    public function testSetExecutesUpsert(): void
    {
        $setStatement = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('exec');

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($this->stmtMock, $setStatement);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $setStatement->expects($this->once())
            ->method('execute')
            ->with(['require_admin_approval', '0']);

        $settingModel = new Setting();
        $settingModel->set('require_admin_approval', '0');
    }

    public function testRequiresAdminApprovalReturnsBoolean(): void
    {
        $getStatement = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->once())
            ->method('exec');

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($this->stmtMock, $getStatement);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $getStatement->expects($this->once())
            ->method('execute')
            ->with(['require_admin_approval']);

        $getStatement->expects($this->once())
            ->method('fetchColumn')
            ->willReturn('1');

        $settingModel = new Setting();
        $this->assertTrue($settingModel->requiresAdminApproval());
    }
}
