<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/documentType.php';

/*
|--------------------------------------------------------------------------
| DOCUMENT TYPE MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class DocumentTypeModelTest extends TestCase
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

    public function testGetAllActiveReturnsDocumentTypes(): void
    {
        $expectedTypes = [
            ['type_id' => 1, 'type_name' => 'Clearance Form'],
            ['type_id' => 2, 'type_name' => 'Travel Order']
        ];

        $this->pdoMock->expects($this->once())
            ->method('query')
            ->with($this->stringContains('WHERE is_active=1'))
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedTypes);

        $docTypeModel = new DocumentType();
        $types = $docTypeModel->getAllActive();

        $this->assertIsArray($types);
        $this->assertCount(2, $types);
        $this->assertEquals('Clearance Form', $types[0]['type_name']);
    }

    public function testTypeExistsReturnsBoolean(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute');

        $this->stmtMock->expects($this->exactly(2))
            ->method('fetchColumn')
            ->willReturnOnConsecutiveCalls(1, false);

        $docTypeModel = new DocumentType();
        $this->assertTrue($docTypeModel->typeExists(1));
        $this->assertFalse($docTypeModel->typeExists(9999));
    }

    public function testCreateWithOfficesHandlesTransaction(): void
    {
        $this->pdoMock->expects($this->once())->method('beginTransaction');
        $this->pdoMock->expects($this->exactly(2))->method('prepare')->willReturn($this->stmtMock);
        $this->pdoMock->expects($this->once())->method('lastInsertId')->willReturn('5');
        $this->pdoMock->expects($this->once())->method('commit');

        $this->stmtMock->expects($this->exactly(2))->method('execute');

        $docTypeModel = new DocumentType();
        $docTypeModel->createWithOffices("New Type", [1], 1);
    }

    public function testDeleteTypeExecutesDelete(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('DELETE FROM document_types WHERE type_id = :id')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':id' => 1]);

        $docTypeModel = new DocumentType();
        $docTypeModel->deleteType(1);
    }
}
