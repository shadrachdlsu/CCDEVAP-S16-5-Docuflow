<?php
use PHPUnit\Framework\TestCase;

global $pdo;
$pdo = null; 

require_once __DIR__ . '/../../../models/role.php';

class RoleTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;

    protected function setUp(): void
    {
        // Create a mock for PDO
        $this->pdoMock = $this->createMock(PDO::class);
        // Create a mock for PDOStatement
        $this->stmtMock = $this->createMock(PDOStatement::class);

        // Inject the mock into the global variable that Role uses
        global $pdo;
        $pdo = $this->pdoMock;
    }

    public function testGetAllReturnsArray(): void
    {
        // Arrange
        $expectedData = [
            ['role_id' => 1, 'role_name' => 'Admin'],
            ['role_id' => 2, 'role_name' => 'Secretary'],
            ['role_id' => 3, 'role_name' => 'Member'],
        ];

        // Mock the prepare method to return statement mock
        $this->pdoMock
            ->expects($this->once())
            ->method('prepare')
            ->with('SELECT role_id, role_name FROM roles ORDER BY role_name')
            ->willReturn($this->stmtMock);

        // Mock execute 
        $this->stmtMock
            ->expects($this->once())
            ->method('execute');

        // Mock fetchAll to return data
        $this->stmtMock
            ->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedData);

        // Act
        $roleModel = new Role();
        $result = $roleModel->getAll();

        // Assert
        $this->assertIsArray($result);
        $this->assertCount(3, $result);
        $this->assertEquals('Admin', $result[0]['role_name']);
    }

    public function testGetAllReturnsEmptyArrayWhenNoRoles(): void
    {
        // Arrange
        $this->pdoMock
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->method('execute');
        $this->stmtMock
            ->method('fetchAll')
            ->willReturn([]);

        // Act
        $roleModel = new Role();
        $result = $roleModel->getAll();

        // Assert
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }
}

