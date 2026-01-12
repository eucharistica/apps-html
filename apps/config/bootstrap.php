<?php
// apps/config/bootstrap.php

// Central place for path constants + session init + shared helpers.

define('EMR_ROOT', realpath(__DIR__ . '/../../')); // repo root

define('EMR_APP', EMR_ROOT . '/apps');

define('EMR_BASE_URL', '/');

define('EMR_HOME_URL', EMR_BASE_URL . 'apps/home');

define('EMR_SIMRS_HOME', EMR_BASE_URL . 'apps/simrs/index.php');

define('EMR_LOGIN_URL', EMR_BASE_URL);
define('EMR_ERROR_SESSION_UPDATED', EMR_BASE_URL . '?error=session_updated');

define('EMR_ERROR_403', EMR_BASE_URL . 'errors/403.php');
define('EMR_ERROR_404', EMR_BASE_URL . 'errors/404.php');
define('EMR_ERROR_500', EMR_BASE_URL . 'errors/500.php');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

function emr_is_logged_in(): bool
{
    return !empty($_SESSION['emr_user']['id']);
}

function emr_logout_and_redirect(string $to): void
{
    // clear session data
    $_SESSION = [];

    // destroy session cookie (best-effort)
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'],
            $params['secure'], $params['httponly']
        );
    }

    session_destroy();
    header('Location: ' . $to);
    exit;
}

function emr_require_login(): void
{
    if (!emr_is_logged_in()) {
        header('Location: ' . EMR_LOGIN_URL);
        exit;
    }

    // Auth version check: kalau role/permission user berubah, paksa login ulang
    $userId = (int)($_SESSION['emr_user']['id'] ?? 0);
    $sessionVer = (int)($_SESSION['emr_user']['auth_version'] ?? 1);

    try {
        $pdo = emr_pdo();
        $stmt = $pdo->prepare("SELECT auth_version FROM emr_users WHERE id = ? LIMIT 1");
        $stmt->execute([$userId]);
        $dbVer = (int)($stmt->fetchColumn() ?? 0);

        // kalau user sudah tidak ada / versi berubah => logout
        if ($dbVer <= 0 || $dbVer !== $sessionVer) {
            emr_audit('session_revoked', 'Auth changed; re-login required', [
                'user_id' => $userId,
                'session_ver' => $sessionVer,
                'db_ver' => $dbVer,
            ]);
            emr_logout_and_redirect(EMR_ERROR_SESSION_UPDATED);
        }
    } catch (Throwable $e) {
        // kalau DB error, jangan diam-diam bypass security
        emr_logout_and_redirect(EMR_LOGIN_URL . '?error=server');
    }
}

function emr_redirect_if_logged_in(): void
{
    if (emr_is_logged_in()) {
        header('Location: ' . EMR_HOME_URL);
        exit;
    }
}

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/response.php';
require_once __DIR__ . '/audit.php';