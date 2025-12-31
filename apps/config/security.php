<?php
// apps/config/security.php

require_once __DIR__ . '/bootstrap.php';

/**
 * Generate (or return existing) CSRF token for current session.
 */
function emr_csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf_token'];
}

/**
 * Validate CSRF token from POST.
 */
function emr_csrf_validate(?string $token): bool
{
    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    if ($token === null || $token === '' || $sessionToken === '') {
        return false;
    }
    return hash_equals($sessionToken, $token);
}

/**
 * Clear CSRF token (optional).
 */
function emr_csrf_clear(): void
{
    unset($_SESSION['_csrf_token']);
}
