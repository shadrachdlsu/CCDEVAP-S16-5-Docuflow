<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/setting.php';
require_once __DIR__ . '/../models/user.php';
require_once __DIR__ . '/../models/document.php';

function assertAdminFeature(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$settingModel = new Setting();
$settingBefore = $settingModel->requiresAdminApproval();
$pdo->beginTransaction();
$settingModel->setRequiresAdminApproval(!$settingBefore);
assertAdminFeature(
    $settingModel->requiresAdminApproval() !== $settingBefore,
    'The registration setting should update inside a transaction.'
);
$pdo->rollBack();
assertAdminFeature(
    $settingModel->requiresAdminApproval() === $settingBefore,
    'The registration setting rollback should restore its original value.'
);

$userModel = new User();
$pendingUserId = $pdo->query(
    "SELECT user_id FROM users WHERE registration_status = 'Pending' LIMIT 1"
)->fetchColumn();

if ($pendingUserId !== false) {
    $pdo->beginTransaction();
    $userModel->decideRegistration((int) $pendingUserId, 'approve');
    $decisionStatus = $pdo->query(
        'SELECT registration_status FROM users WHERE user_id = ' . (int) $pendingUserId
    )->fetchColumn();
    assertAdminFeature(
        $decisionStatus === 'Approved',
        'Approving a pending registration should mark it Approved.'
    );
    $pdo->rollBack();
    $restoredStatus = $pdo->query(
        'SELECT registration_status FROM users WHERE user_id = ' . (int) $pendingUserId
    )->fetchColumn();
    assertAdminFeature(
        $restoredStatus === 'Pending',
        'The registration decision rollback should restore Pending.'
    );
}

$stalledDocuments = (new Document())->getStalledDocuments(5);
assertAdminFeature(
    count($stalledDocuments) <= 5,
    'The dashboard must display no more than five stalled documents.'
);

foreach ($stalledDocuments as $document) {
    assertAdminFeature(
        (int) $document['hours_stalled'] >= 48,
        'Every stalled document must be unresolved for at least 48 hours.'
    );
}

echo "Admin feature tests passed.\n";
