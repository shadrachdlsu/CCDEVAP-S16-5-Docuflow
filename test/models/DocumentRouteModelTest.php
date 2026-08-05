<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/documentRoute.php';

/*
|--------------------------------------------------------------------------
| DOCUMENT ROUTE MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class DocumentRouteModelTest extends TestCase
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

    public function testCountBySignatoryAndStatusReturnsInteger(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([1, 'Waiting']);

        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(2);

        $routeModel = new DocumentRoute();
        $count = $routeModel->countBySignatoryAndStatus(1, 'Waiting');

        $this->assertEquals(2, $count);
    }

    public function testGetPendingForSignatoryReturnsRows(): void
    {
        $expected = [
            ['document_id' => 1, 'tracking_code' => 'DOC-001', 'title' => 'Test Clearance', 'status' => 'Waiting']
        ];

        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with([1]);
        $this->stmtMock->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn($expected);

        $routeModel = new DocumentRoute();
        $pending = $routeModel->getPendingForSignatory(1);

        $this->assertIsArray($pending);
        $this->assertCount(1, $pending);
        $this->assertEquals('DOC-001', $pending[0]['tracking_code']);
    }

    public function testSignRouteReturnsTrueWhenRowsUpdated(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with(['Approved remarks', 1, 1]);
        $this->stmtMock->method('rowCount')->willReturn(1);

        $routeModel = new DocumentRoute();
        $signed = $routeModel->signRoute(1, 1, 'Approved remarks');

        $this->assertTrue($signed);
    }

    public function testSignRouteReturnsFalseWhenNoRowsUpdated(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute');
        $this->stmtMock->method('rowCount')->willReturn(0);

        $routeModel = new DocumentRoute();
        $signed = $routeModel->signRoute(9999, 1, 'Nonexistent doc');

        $this->assertFalse($signed);
    }
}
