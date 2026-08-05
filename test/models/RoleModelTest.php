<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/role.php';

/*
|--------------------------------------------------------------------------
| ROLE MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class RoleModelTest extends TestCase
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

    public function testGetAllReturnsRolesArray(): void
    {
        $expectedData = [
            ['role_id' => 1, 'role_name' => 'Admin'],
            ['role_id' => 2, 'role_name' => 'Secretary'],
            ['role_id' => 3, 'role_name' => 'Member'],
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT role_id, role_name FROM roles ORDER BY role_name')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        $roleModel = new Role();
        $result = $roleModel->getAll();

        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('Admin', $result[0]['role_name']);
    }

    public function testGetAllReturnsEmptyArrayWhenNoRoles(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT role_id, role_name FROM roles ORDER BY role_name')
            ->willReturn($this->stmtMock);

        $this->stmtMock->method('fetchAll')->willReturn([]);

        $roleModel = new Role();
        $result = $roleModel->getAll();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}
