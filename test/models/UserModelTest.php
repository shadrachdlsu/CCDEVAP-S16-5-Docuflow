<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../config/connections.php';
require_once __DIR__ . '/../../models/user.php';

/*
|--------------------------------------------------------------------------
| USER MODEL MOCK-BASED UNIT TEST
|--------------------------------------------------------------------------
*/

class UserModelTest extends TestCase
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

    public function testFindByEmailReturnsUserData(): void
    {
        $expectedUser = [
            'user_id' => 1,
            'role_id' => 3,
            'full_name' => 'John Doe',
            'email' => 'john@docuflow.local',
            'password_hash' => 'hash123',
            'is_active' => 1,
            'registration_status' => 'Approved',
            'office_id' => 1,
            'office_name' => 'Registrar'
        ];

        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with(['email' => 'john@docuflow.local']);

        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->with(PDO::FETCH_ASSOC)
            ->willReturn($expectedUser);

        $userModel = new User();
        $user = $userModel->findByEmail('john@docuflow.local');

        $this->assertIsArray($user);
        $this->assertEquals('John Doe', $user['full_name']);
        $this->assertEquals('john@docuflow.local', $user['email']);
    }

    public function testFindByEmailReturnsNullWhenNotFound(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute');
        $this->stmtMock->method('fetch')->willReturn(false);

        $userModel = new User();
        $user = $userModel->findByEmail('nonexistent@docuflow.local');

        $this->assertNull($user);
    }

    public function testEmailExistsReturnsTrueAndFalse(): void
    {
        $this->pdoMock->method('prepare')->willReturn($this->stmtMock);
        $this->stmtMock->method('execute');
        
        $this->stmtMock->expects($this->exactly(2))
            ->method('fetchColumn')
            ->willReturnOnConsecutiveCalls(1, false);

        $userModel = new User();
        $this->assertTrue($userModel->emailExists('exists@docuflow.local'));
        $this->assertFalse($userModel->emailExists('notexists@docuflow.local'));
    }

    public function testCreateUserExecutesInsertStatement(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([
                'role_id' => 3,
                'office_id' => 1,
                'full_name' => 'New User',
                'email' => 'new@docuflow.local',
                'password_hash' => 'hash123',
                'is_active' => 0,
                'registration_status' => 'Pending'
            ]);

        $userModel = new User();
        $userModel->create(3, 1, 'New User', 'new@docuflow.local', 'hash123', 0, 'Pending');
    }

    public function testApproveAndDeactivateUserStatements(): void
    {
        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->exactly(2))
            ->method('execute');

        $userModel = new User();
        $userModel->approveUser(1);
        $userModel->deactivateUser(1);
    }

    public function testDeleteUserExecutesDeleteStatement(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([':id' => 1]);

        $userModel = new User();
        $userModel->delete(1);
    }

    public function testValidatePasswordComplexity(): void
    {
        // Valid password
        $this->assertNull(User::validatePasswordComplexity("Docuflow2026!"));

        // Too short (< 8 chars)
        $this->assertEquals("Password must be at least 8 characters long.", User::validatePasswordComplexity("Pass1!"));

        // Missing uppercase
        $this->assertEquals("Password must contain at least one uppercase letter.", User::validatePasswordComplexity("docuflow2026!"));

        // Missing lowercase
        $this->assertEquals("Password must contain at least one lowercase letter.", User::validatePasswordComplexity("DOCUFLOW2026!"));

        // Missing number
        $this->assertEquals("Password must contain at least one number.", User::validatePasswordComplexity("DocuflowPass!"));

        // Missing special character
        $this->assertEquals("Password must contain at least one special character.", User::validatePasswordComplexity("Docuflow2026"));
    }
}
?>
