<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/documentRequest.php';

/*
|--------------------------------------------------------------------------
| DOCUMENT REQUEST MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class DocumentRequestModelTest extends TestCase
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

    public function testCreateExecutesInsert(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([1, 1, 1, 'Clearance Request', 'Request description']);

        $reqModel = new DocumentRequest();
        $reqModel->create(1, 1, 1, 'Clearance Request', 'Request description');
    }

    public function testGetByUserReturnsRequestRows(): void
    {
        $expected = [
            ['request_id' => 1, 'title' => 'Clearance Request', 'status' => 'Pending', 'type_name' => 'Clearance Form']
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([1]);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $reqModel = new DocumentRequest();
        $requests = $reqModel->getByUser(1);

        $this->assertIsArray($requests);
        $this->assertCount(1, $requests);
        $this->assertEquals('Clearance Request', $requests[0]['title']);
    }

    public function testCountByUserReturnsInteger(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with([1]);
        $this->stmtMock->method('fetchColumn')->willReturn(3);

        $reqModel = new DocumentRequest();
        $count = $reqModel->countByUser(1);

        $this->assertEquals(3, $count);
    }

    public function testDeletePendingReturnsBoolean(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute')->with([1, 1]);
        $this->stmtMock->method('rowCount')->willReturn(1);

        $reqModel = new DocumentRequest();
        $deleted = $reqModel->deletePending(1, 1);

        $this->assertTrue($deleted);
    }
}
