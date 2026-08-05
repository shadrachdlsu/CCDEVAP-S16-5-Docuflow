<?php
declare(strict_types=1);

function docuflow_csrf_token(): string
{
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function docuflow_verify_csrf_token(?string $token): bool
{
    $sessionToken = $_SESSION['csrf_token'] ?? null;

    return is_string($sessionToken)
        && is_string($token)
        && $token !== ''
        && hash_equals($sessionToken, $token);
}
