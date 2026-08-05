<?php
declare(strict_types=1);

/**
 * Create a password hash using PHP's current recommended password algorithm.
 */
function docuflow_hash_password(string $password): string
{
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($passwordHash === false) {
        throw new RuntimeException('The password could not be securely hashed.');
    }

    return $passwordHash;
}

/**
 * Identify hashes created by DOCUFLOW2's previous unsalted SHA-256 scheme.
 */
function docuflow_is_legacy_sha256_hash(string $passwordHash): bool
{
    return preg_match('/^[a-f0-9]{64}$/i', $passwordHash) === 1;
}

/**
 * Verify current PHP password hashes and legacy DOCUFLOW2 SHA-256 hashes.
 *
 * Legacy support exists only so a successful login can transparently replace
 * the old hash. New or changed passwords must always use
 * docuflow_hash_password().
 */
function docuflow_verify_password(string $password, string $passwordHash): bool
{
    if (docuflow_is_legacy_sha256_hash($passwordHash)) {
        return hash_equals(strtolower($passwordHash), hash('sha256', $password));
    }

    return password_verify($password, $passwordHash);
}

/**
 * Determine whether a verified password should be written back with the
 * current PASSWORD_DEFAULT algorithm or cost.
 */
function docuflow_password_needs_rehash(string $passwordHash): bool
{
    if (docuflow_is_legacy_sha256_hash($passwordHash)) {
        return true;
    }

    return password_needs_rehash($passwordHash, PASSWORD_DEFAULT);
}
