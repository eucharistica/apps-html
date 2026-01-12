<?php
// apps/auth/sign-in.php
require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/ip_whitelist.php';
require_once __DIR__ . '/app_context.php';

$pdo = emr_pdo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . EMR_LOGIN_URL);
    exit;
}

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$ip = emr_client_ip_public();

if ($username === '' || $password === '') {
    emr_audit('login_failed', 'Missing credentials');
    header('Location: /?error=invalid');
    exit;
}

// Throttle keys
$keyUnknown = 'emr_login_unknown';
$keyKnown   = 'emr_login_known_' . hash('sha256', strtolower($username));

$unknown = $_SESSION[$keyUnknown] ?? ['count' => 0, 'lock_until' => 0];
$known   = $_SESSION[$keyKnown] ?? ['count' => 0];

if (($unknown['lock_until'] ?? 0) > time()) {
    emr_audit('login_failed', 'Locked attempts');
    header('Location: /?error=locked');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, username, name, email, profile_photo_path, password, status, allow_external_login, auth_version
                            FROM emr_users
                            WHERE username = ?
                            LIMIT 1");
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if (!$user) {
        // Unknown username lockout: 5 tries => lock 10 minutes
        $unknown['count'] = (int)($unknown['count'] ?? 0) + 1;

        if ($unknown['count'] >= 5) {
            $unknown['lock_until'] = time() + (10 * 60);
            $unknown['count'] = 0;
            $_SESSION[$keyUnknown] = $unknown;

            emr_audit('login_failed', 'Locked attempts');
            header('Location: /?error=locked');
            exit;
        }

        $_SESSION[$keyUnknown] = $unknown;

        // Jangan bedakan “user tidak ada” vs “password salah” di response
        emr_audit('login_failed', 'Invalid credentials');
        header('Location: /?error=invalid');
        exit;
    }

    if (($user['status'] ?? '') !== 'active') {
        emr_audit('login_failed', 'User inactive', ['username' => $username]);
        header('Location: /?error=inactive');
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        // Known username wrong password: after 10 show “hubungi IT”
        $known['count'] = (int)($known['count'] ?? 0) + 1;
        $_SESSION[$keyKnown] = $known;

        if ($known['count'] >= 10) {
            emr_audit('login_failed', 'Too many attempts', ['username' => $username]);
            header('Location: /?error=hubungi_it');
            exit;
        }

        emr_audit('login_failed', 'Invalid credentials', ['username' => $username]);
        header('Location: /?error=invalid');
        exit;
    }

    // Success: reset throttles
    unset($_SESSION[$keyUnknown], $_SESSION[$keyKnown]);

    if (!emr_can_login_by_access_mode($user)) {
        $ipPub = emr_client_ip_public();
        // Catat audit biar kebaca alasan ditolak
        emr_audit('login_denied', 'External login not allowed', [
            'ip_public' => $ipPub,
            'host' => $_SERVER['HTTP_HOST'] ?? null,
            'remote_addr' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        header('Location: /?error=ip_not_allowed');
        exit;
    }



    // Prevent session fixation (regen after auth)
    session_regenerate_id(true); // recommended practice [web:329]

    // Update last login
    $stmt = $pdo->prepare("UPDATE emr_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $user['id']]);

    // Load roles
    $stmt = $pdo->prepare("SELECT r.id, r.name
                           FROM emr_roles r
                           INNER JOIN emr_user_has_roles ur ON ur.role_id = r.id
                           WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $roles = $stmt->fetchAll();

    // Load permissions (via roles)
    $stmt = $pdo->prepare("SELECT DISTINCT p.name
                       FROM emr_permissions p
                       INNER JOIN emr_role_has_permissions rp ON rp.permission_id = p.id
                       INNER JOIN emr_user_has_roles ur ON ur.role_id = rp.role_id
                       WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $permissions = array_map(fn($row) => $row['name'], $stmt->fetchAll());

    $stmt = $pdo->prepare("SELECT r.type, p.name
                       FROM emr_roles r
                       INNER JOIN emr_user_has_roles ur ON ur.role_id = r.id
                       INNER JOIN emr_role_has_permissions rp ON rp.role_id = r.id
                       INNER JOIN emr_permissions p ON p.id = rp.permission_id
                       WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);

    $permissionsByType = [];
    foreach ($stmt->fetchAll() as $row) {
        $t = (string)$row['type'];
        $n = (string)$row['name'];
        $permissionsByType[$t][$n] = true;
    }

    // normalize jadi array of names
    foreach ($permissionsByType as $t => $set) {
        $permissionsByType[$t] = array_keys($set);
    }

    $_SESSION['emr_user'] = [
        'id' => (int)$user['id'],
        'username' => $user['username'],
        'name' => $user['name'],
        'email' => $user['email'],
        'profile_photo_path' => $user['profile_photo_path'],
        'roles' => $roles,
        'permissions' => $permissions,
        'permissions_by_type' => $permissionsByType,
        'auth_version' => (int)($user['auth_version'] ?? 1),
    ];

    emr_audit('login_success', 'User logged in');

    header('Location: ' . EMR_HOME_URL . '?login=success');
    exit;

} catch (Throwable $e) {
    emr_audit('login_failed', 'Server error');
    header('Location: /?error=server');
    exit;
}
