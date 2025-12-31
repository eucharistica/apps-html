<?php
// apps/auth/sign-in.php

session_start();

require_once __DIR__ . '/../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: /?error=empty');
    exit;
}

try {
    $pdo = emr_pdo();

    $stmt = $pdo->prepare("SELECT id, username, password, status FROM emr_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        header('Location: /?error=invalid');
        exit;
    }

    if (($user['status'] ?? '') !== 'active') {
        header('Location: /?error=inactive');
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        header('Location: /?error=invalid');
        exit;
    }

    // Update last login
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("UPDATE emr_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $user['id']]);

    // Load roles (multi-role)
    $stmt = $pdo->prepare("SELECT r.id, r.name FROM emr_roles r
                            INNER JOIN emr_user_has_roles ur ON ur.role_id = r.id
                            WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $roles = $stmt->fetchAll();

    $_SESSION['emr_user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'roles' => $roles,
    ];

    header('Location: /apps/simrs/home/index.php');
    exit;

} catch (Throwable $e) {
    // Avoid leaking details
    header('Location: /?error=server');
    exit;
}
