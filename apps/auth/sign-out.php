<?php
// apps/auth/sign-out.php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/audit.php';

emr_require_login();

// Audit dulu (masih ada $_SESSION['emr_user'])
emr_audit('logout', 'User logged out');

// Lalu hancurkan session
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
