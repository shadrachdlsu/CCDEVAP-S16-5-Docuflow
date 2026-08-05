<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/document.php';

/*
|--------------------------------------------------------------------------
| DOCUMENT MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class DocumentModelTest extends TestCase
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

    public function testCountAllAndByStatus(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute');
        $this->stmtMock->expects($this->exactly(2))
            ->method('fetchColumn')
            ->willReturnOnConsecutiveCalls(10, 3);

        $docModel = new Document();
        $this->assertEquals(10, $docModel->countAll());
        $this->assertEquals(3, $docModel->countByStatus('Pending'));
    }

    public function testGetDocumentsForOffice(): void
    {
        $expected = [
            ['document_id' => 1, 'title' => 'Test Clearance', 'status' => 'Pending']
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':office_id' => 1]);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $docModel = new Document();
        $docs = $docModel->getDocumentsForOffice(1, 'Pending');

        $this->assertIsArray($docs);
        $this->assertCount(1, $docs);
    }

    public function testGetStatusDistribution(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute');
        $this->stmtMock->method('fetchColumn')->willReturn(10);
        $this->stmtMock->method('fetchAll')->willReturn([
            ['label' => 'Pending', 'value' => 5],
            ['label' => 'Completed', 'value' => 5]
        ]);

        $docModel = new Document();
        $dist = $docModel->getStatusDistribution();

        $this->assertIsArray($dist);
    }
}
