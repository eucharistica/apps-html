<?php
// apps/auth/sign-out.php

require_once __DIR__ . '/../config/bootstrap.php';
emr_require_login();
emr_audit('logout', 'User logged out');

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params['path'], $params['domain'], $params['secure'], $params['httponly']
    );
}

session_destroy();
header('Location: ' . EMR_LOGIN_URL);
exit;
