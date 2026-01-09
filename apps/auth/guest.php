<?php
define('EMR_ROOT', realpath(__DIR__ . '/../../')); // repo root

define('EMR_APP', EMR_ROOT . '/apps');

define('EMR_BASE_URL', '/');

define('EMR_HOME_URL', EMR_BASE_URL . 'apps/home');

define('EMR_LOGIN_URL', EMR_BASE_URL);

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

function emr_require_login(): void
{
    if (!emr_is_logged_in()) {
        header('Location: ' . EMR_LOGIN_URL);
        exit;
    }
}

function emr_redirect_if_logged_in(): void
{
    if (emr_is_logged_in()) {
        header('Location: ' . EMR_HOME_URL);
        exit;
    }
}

require_once __DIR__ . '/../config/security.php';