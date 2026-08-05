<?php
declare(strict_types=1);

require_once __DIR__ . '/../helpers/passwords.php';

function assertPasswordTest(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$password = 'A-secure-test-password!';
$currentHash = docuflow_hash_password($password);
$secondCurrentHash = docuflow_hash_password($password);
$legacyHash = hash('sha256', $password);
$compatibleBcryptHash = '$2a$' . substr($currentHash, 4);

assertPasswordTest(
    password_get_info($currentHash)['algoName'] === 'bcrypt',
    'PASSWORD_DEFAULT should use bcrypt in the supported PHP environment.'
);
assertPasswordTest(
    $currentHash !== $secondCurrentHash,
    'Current password hashes must use unique salts.'
);
assertPasswordTest(
    docuflow_verify_password($password, $currentHash),
    'A current password hash should verify the correct password.'
);
assertPasswordTest(
    !docuflow_verify_password('incorrect-password', $currentHash),
    'A current password hash must reject an incorrect password.'
);
assertPasswordTest(
    docuflow_is_legacy_sha256_hash($legacyHash),
    'A legacy DOCUFLOW2 SHA-256 hash should be recognized.'
);
assertPasswordTest(
    docuflow_verify_password($password, $legacyHash),
    'A legacy hash should verify once so it can be migrated.'
);
assertPasswordTest(
    !docuflow_verify_password('incorrect-password', $legacyHash),
    'A legacy hash must reject an incorrect password.'
);
assertPasswordTest(
    docuflow_password_needs_rehash($legacyHash),
    'A legacy hash should always be marked for migration.'
);
assertPasswordTest(
    !docuflow_password_needs_rehash($currentHash),
    'A newly-created current hash should not need rehashing.'
);
assertPasswordTest(
    docuflow_verify_password($password, $compatibleBcryptHash),
    'An existing compatible bcrypt hash should remain valid.'
);
assertPasswordTest(
    docuflow_password_needs_rehash($compatibleBcryptHash),
    'A compatible bcrypt variant should be normalized to PASSWORD_DEFAULT after login.'
);

echo "Password migration tests passed.\n";
