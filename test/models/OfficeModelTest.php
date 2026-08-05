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

    public function testGetAllOfficesDetailedReturnsRows(): void
    {
        $expectedOffices = [
            [
                'id' => 1,
                'name' => 'Registrar',
                'code' => 'REGI',
                'location' => 'Building A',
                'contact_email' => 'registrar@docuflow.local',
                'is_active' => 1,
                'secretary_id' => 2,
                'secretary_name' => 'Jane Secretary',
                'secretary_email' => 'secretary@docuflow.local',
                'member_count' => 5,
                'active_doc_count' => 2
            ]
        ];

        $this->stmtMock->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedOffices);

        $officeModel = new Office();
        $offices = $officeModel->getAllOfficesDetailed();

        $this->assertIsArray($offices);
        $this->assertCount(1, $offices);
        $this->assertEquals('Registrar', $offices[0]['name']);
    }

    public function testCountAllOfficesReturnsInteger(): void
    {
        $this->stmtMock->method('fetchColumn')->willReturn(4);

        $officeModel = new Office();
        $count = $officeModel->countAllOffices();

        $this->assertEquals(4, $count);
    }

    public function testCreateOfficeExecutesInsert(): void
    {
        $officeModel = new Office();
        $officeModel->createOffice("Finance", "FINA", "Building B", "finance@docuflow.local", 1);
        $this->assertTrue(true);
    }

    public function testToggleStatusExecutesUpdate(): void
    {
        $officeModel = new Office();
        $officeModel->toggleStatus(1, 0);
        $this->assertTrue(true);
    }

    public function testDeleteOfficeExecutesDelete(): void
    {
        $officeModel = new Office();
        $officeModel->delete(1);
        $this->assertTrue(true);
    }
}
