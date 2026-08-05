<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/office.php';

/*
|--------------------------------------------------------------------------
| OFFICE MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class OfficeModelTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private $schemaStmtMock;
    private $backupPdo;
    private array $preparedSql = [];

    protected function setUp(): void
    {
        global $pdo;
        $this->backupPdo = $pdo;

        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        $this->schemaStmtMock = $this->createMock(PDOStatement::class);
        $this->preparedSql = [];

        $this->schemaStmtMock->expects($this->once())
            ->method('execute')
            ->with(['offices', 'is_active']);
        $this->schemaStmtMock->method('fetchColumn')->willReturn(1);

        $this->pdoMock->method('prepare')
            ->willReturnCallback(function (string $sql): PDOStatement {
                $this->preparedSql[] = $sql;

                if (str_contains($sql, 'information_schema.COLUMNS')) {
                    return $this->schemaStmtMock;
                }

                return $this->stmtMock;
            });

        $pdo = $this->pdoMock;
    }

    protected function tearDown(): void
    {
        global $pdo;
        $pdo = $this->backupPdo;
    }

    public function testGetAdminDirectoryReturnsRows(): void
    {
        $expectedOffices = [
            [
                'office_id' => 1,
                'office_name' => 'Registrar',
                'is_active' => 1,
                'secretary_name' => 'Jane Secretary',
                'user_count' => 5,
                'route_count' => 2,
            ],
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('FROM offices AS office'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->method('fetchAll')
            ->willReturn($expectedOffices);

        $officeModel = new Office();
        $offices = $officeModel->getAdminDirectory();

        $this->assertIsArray($offices);
        $this->assertCount(1, $offices);
        $this->assertEquals('Registrar', $offices[0]['office_name']);
    }

    public function testCountAllOfficesReturnsInteger(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT COUNT(*) FROM offices')
            ->willReturn($this->stmtMock);

        $this->stmtMock->method('fetchColumn')->willReturn(4);

        $officeModel = new Office();
        $count = $officeModel->countAllOffices();

        $this->assertEquals(4, $count);
    }

    public function testCreateExecutesInsert(): void
    {
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':name' => 'Finance']);

        $officeModel = new Office();
        $officeModel->create('Finance');

        $this->assertContains(
            'INSERT INTO offices (office_name) VALUES (:name)',
            $this->preparedSql
        );
    }

    public function testSetActiveExecutesUpdate(): void
    {
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([0, 1]);

        $this->stmtMock->method('rowCount')->willReturn(1);

        $officeModel = new Office();
        $officeModel->setActive(1, false);

        $this->assertContains(
            'UPDATE offices SET is_active = ? WHERE office_id = ?',
            $this->preparedSql
        );
    }

    public function testDeleteOfficeExecutesDelete(): void
    {
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':id' => 1]);

        $officeModel = new Office();
        $officeModel->delete(1);

        $this->assertContains(
            'DELETE FROM offices WHERE office_id = :id',
            $this->preparedSql
        );
    }
}
