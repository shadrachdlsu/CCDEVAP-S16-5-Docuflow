<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/documentTrail.php';

/*
|--------------------------------------------------------------------------
| DOCUMENT TRAIL MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class DocumentTrailModelTest extends TestCase
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

    public function testAddEntryExecutesInsert(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                ':doc_id'       => 1,
                ':user_id'      => 1,
                ':from_office'  => 1,
                ':to_office'    => 2,
                ':action'       => 'Created',
                ':remarks'      => 'Test remarks'
            ]);

        $trailModel = new DocumentTrail();
        $trailModel->addEntry(1, 1, 1, 2, 'Created', 'Test remarks');
    }

    public function testGetByDocumentReturnsTrailList(): void
    {
        $expected = [
            ['trail_id' => 1, 'action' => 'Created', 'remarks' => 'Initial upload', 'action_by_name' => 'John Doe']
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':doc_id' => 1]);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expected);

        $trailModel = new DocumentTrail();
        $trail = $trailModel->getByDocument(1);

        $this->assertIsArray($trail);
        $this->assertCount(1, $trail);
        $this->assertEquals('Created', $trail[0]['action']);
    }

    public function testGetRecentBindsLimitParameter(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('bindValue')
            ->with(':limit', 5, PDO::PARAM_INT);

        $this->stmtMock->expects($this->once())
            ->method('execute');

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn([]);

        $trailModel = new DocumentTrail();
        $recent = $trailModel->getRecent(5);

        $this->assertIsArray($recent);
    }
}
