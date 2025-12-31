<?php
// apps/auth/sign-in.php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/database.php';

$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');

if ($username === '' || $password === '') {
    header('Location: /?error=empty');
    exit;
}

try {
    $pdo = emr_pdo();

    $stmt = $pdo->prepare("SELECT id, username, name, email, profile_photo_path, password, status FROM emr_users WHERE username = ? LIMIT 1");
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

    // Load permissions (via roles)
    $stmt = $pdo->prepare("SELECT DISTINCT p.name FROM emr_permissions p
                            INNER JOIN emr_role_has_permissions rp ON rp.permission_id = p.id
                            INNER JOIN emr_user_has_roles ur ON ur.role_id = rp.role_id
                            WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $permissions = array_map(fn($row) => $row['name'], $stmt->fetchAll());

    $_SESSION['emr_user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'name' => $user['name'],
        'email' => $user['email'],
        'profile_photo_path' => $user['profile_photo_path'],
        'roles' => $roles,
        'permissions' => $permissions,
    ];

    header('Location: ' . EMR_SIMRS_HOME);
    exit;

} catch (Throwable $e) {
    header('Location: /?error=server');
    exit;
}
