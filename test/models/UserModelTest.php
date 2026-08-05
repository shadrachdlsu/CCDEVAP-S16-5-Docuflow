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

    public function testDecideRegistrationApprovesAndRejectsPendingUsers(): void
    {
        $approveStatement = $this->createMock(PDOStatement::class);
        $rejectStatement = $this->createMock(PDOStatement::class);

        $this->pdoMock->expects($this->exactly(2))
            ->method('prepare')
            ->willReturnOnConsecutiveCalls($approveStatement, $rejectStatement);

        $approveStatement->expects($this->once())
            ->method('execute')
            ->with([1, 'Approved', 1]);
        $approveStatement->method('rowCount')->willReturn(1);

        $rejectStatement->expects($this->once())
            ->method('execute')
            ->with([0, 'Rejected', 2]);
        $rejectStatement->method('rowCount')->willReturn(1);

        $userModel = new User();
        $userModel->decideRegistration(1, 'approve');
        $userModel->decideRegistration(2, 'reject');
    }

    public function testSetActiveFromAdminActivatesUser(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('beginTransaction');
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);
        $this->pdoMock->expects($this->once())
            ->method('commit');

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([1, 1]);
        $this->stmtMock->method('rowCount')->willReturn(1);

        $userModel = new User();
        $userModel->setActiveFromAdmin(1, true);
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

    public function testUpdatePasswordHashExecutesUpdate(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->with('UPDATE users SET password_hash = ? WHERE user_id = ?')
            ->willReturn($this->stmtMock);

        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with(['new-password-hash', 1]);

        $userModel = new User();
        $userModel->updatePasswordHash(1, 'new-password-hash');
    }
}
?>
