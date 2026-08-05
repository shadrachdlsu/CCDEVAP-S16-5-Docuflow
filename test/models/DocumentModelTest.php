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
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with('SELECT COUNT(*) FROM documents')
            ->willReturn($this->stmtMock);

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('SELECT COUNT(*) FROM documents WHERE status = ?')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with(['Pending']);

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
        $this->pdoMock->expects($this->exactly(2))
            ->method('query')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchColumn')
            ->willReturn(10);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([
                ['label' => 'Pending', 'value' => 5],
                ['label' => 'Completed', 'value' => 5],
            ]);

        $docModel = new Document();
        $dist = $docModel->getStatusDistribution();

        $this->assertSame(10, $dist['total']);
        $this->assertCount(2, $dist['rows']);
        $this->assertSame('Pending - 50%', $dist['rows'][0]['label']);
    }
}
