<?php
// apps/auth/sign-in.php

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../config/security.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/audit.php';


$username = trim($_POST['username'] ?? '');
$password = (string)($_POST['password'] ?? '');
$token = (string)($_POST['_token'] ?? '');

if ($username === '' || $password === '') {
    header('Location: /?error=empty');
    exit;
}

if (!emr_csrf_validate($token)) {
    header('Location: /?error=csrf');
    exit;
}

try {
    $pdo = emr_pdo();

    // Basic in-session throttling state (no DB yet)
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $keyUnknown = 'login_unknown_' . sha1($ip);
    $keyKnown = 'login_known_' . sha1($ip . '|' . strtolower($username));

    $unknown = $_SESSION[$keyUnknown] ?? ['count' => 0, 'lock_until' => 0];
    $known = $_SESSION[$keyKnown] ?? ['count' => 0];

    if (($unknown['lock_until'] ?? 0) > time()) {
        header('Location: /?error=locked');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id, username, name, email, profile_photo_path, password, status FROM emr_users WHERE username = ? LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        // Lockout only for unknown/random usernames: 5 tries => lock 10 minutes
        $unknown['count'] = (int)($unknown['count'] ?? 0) + 1;
        if ($unknown['count'] >= 5) {
            $unknown['lock_until'] = time() + (10 * 60);
            $unknown['count'] = 0;
            emr_audit('login_failed', 'Locked attempts', ['username' => $username]);
        }
        $_SESSION[$keyUnknown] = $unknown;

        emr_audit('login_failed', 'Invalid credentials', ['username' => $username]);
        header('Location: /?error=invalid');
        exit;
    }

    if (($user['status'] ?? '') !== 'active') {
        emr_audit('login_failed', 'User Inactive', ['username' => $username]);
        header('Location: /?error=inactive');
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        // Known username wrong password: after 10 show "hubungi IT" message
        $known['count'] = (int)($known['count'] ?? 0) + 1;
        $_SESSION[$keyKnown] = $known;

        if ($known['count'] >= 10) {
            emr_audit('login_failed', 'Too many attempts', ['username' => $username]);
            header('Location: /?error=hubungi_it');
            exit;
        }
        emr_audit('login_failed', 'Invalid Password', ['username' => $username]);
        header('Location: /?error=invalid');
        exit;
    }

    // Success: reset throttles
    unset($_SESSION[$keyUnknown], $_SESSION[$keyKnown]);

    // Prevent session fixation
    session_regenerate_id(true);

    // Update last login
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

    emr_audit('login_success', 'User logged in');

    header('Location: ' . EMR_HOME_URL);
    exit;

} catch (Throwable $e) {
    header('Location: /?error=server');
    exit;
}
