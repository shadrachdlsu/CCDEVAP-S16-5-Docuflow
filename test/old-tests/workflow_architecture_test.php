<?php
declare(strict_types=1);

require_once __DIR__ . '/../models/documentTrail.php';

function assertWorkflow(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$documentRow = $pdo->query(
    'SELECT document_id, creator_id FROM documents ORDER BY document_id LIMIT 1'
)->fetch();

if ($documentRow) {
    $documentId = (int) $documentRow['document_id'];
    $creatorId = (int) $documentRow['creator_id'];
    $before = (int) $pdo->query(
        'SELECT COUNT(*) FROM document_trails WHERE document_id = ' . $documentId
    )->fetchColumn();

    $pdo->beginTransaction();
    (new DocumentTrail())->addEntry(
        $documentId,
        $creatorId,
        null,
        null,
        'Created',
        'Rollback-only workflow architecture test.'
    );
    $during = (int) $pdo->query(
        'SELECT COUNT(*) FROM document_trails WHERE document_id = ' . $documentId
    )->fetchColumn();
    assertWorkflow($during === $before + 1, 'A trail event should be inserted.');
    $pdo->rollBack();

    $after = (int) $pdo->query(
        'SELECT COUNT(*) FROM document_trails WHERE document_id = ' . $documentId
    )->fetchColumn();
    assertWorkflow($after === $before, 'The audit-trail test must leave no database changes.');
}

$documentSource = file_get_contents(__DIR__ . '/../models/document.php');
$routeSource = file_get_contents(__DIR__ . '/../models/documentRoute.php');
$installerSource = file_get_contents(__DIR__ . '/../docuflow_db FINAL.sql');

assertWorkflow(
    str_contains($documentSource, 'new DocumentTrail()'),
    'Document creation must write to the audit trail.'
);
assertWorkflow(
    str_contains($routeSource, 'new DocumentTrail()'),
    'Route actions must write to the audit trail.'
);
assertWorkflow(
    str_contains($installerSource, 'CREATE TABLE `document_assignments`')
        && str_contains($installerSource, 'CREATE TABLE `document_trails`'),
    'The DOCUFLOW2 installer must include assignment and trail tables.'
);

$requestWorkflowReferences = [];

foreach (['controllers', 'views', 'js'] as $directory) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(
            __DIR__ . '/../' . $directory,
            FilesystemIterator::SKIP_DOTS
        )
    );

    foreach ($iterator as $file) {
        if (!$file->isFile()) {
            continue;
        }

        $source = file_get_contents($file->getPathname());
        if (
            str_contains($source, 'DocumentRequest')
            || str_contains($source, 'document_requests')
        ) {
            $requestWorkflowReferences[] = $file->getPathname();
        }
    }
}

assertWorkflow(
    $requestWorkflowReferences === [],
    'Document requests must remain outside the DOCUFLOW2 application workflow.'
);

echo "Workflow architecture tests passed.\n";
