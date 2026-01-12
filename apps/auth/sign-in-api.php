<?php
// apps/auth/sign-in-api.php
require_once __DIR__ . '/../config/bootstrap.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emr_json_error('Method not allowed', 405);
    exit;
}

$pdo = emr_pdo();

$username = trim((string)($_POST['username'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$token = $_POST['_token'] ?? null;

if (!emr_csrf_validate($token)) {
    emr_json_error('Invalid CSRF token', 419);
    exit;
}

if ($username === '' || $password === '') {
    emr_audit('login_failed', 'Missing credentials');
    emr_json_error('Missing credentials', 422);
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
        emr_audit('login_failed', 'Invalid user credentials');
        emr_json_error('Sorry, the email or password is incorrect, please try again.', 401);
        exit;
    }

    if (($user['status'] ?? '') !== 'active') {
        emr_audit('login_failed', 'User inactive', ['username' => $username]);
        emr_json_error('Sorry, the account is inactive, please contact the IT.', 403);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        emr_audit('login_failed', 'Invalid password credentials', ['username' => $username]);
        emr_json_error('Sorry, the email or password is incorrect, please try again.', 401);
        exit;
    }

    session_regenerate_id(true); 

    // Load roles
    $stmt = $pdo->prepare("SELECT r.id, r.name, r.type
                           FROM emr_roles r
                           INNER JOIN emr_user_has_roles ur ON ur.role_id = r.id
                           WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $roles = $stmt->fetchAll();

    // All permissions
    $stmt = $pdo->prepare("SELECT DISTINCT p.name
                           FROM emr_permissions p
                           INNER JOIN emr_role_has_permissions rp ON rp.permission_id = p.id
                           INNER JOIN emr_user_has_roles ur ON ur.role_id = rp.role_id
                           WHERE ur.user_id = ?");
    $stmt->execute([$user['id']]);
    $permissions = array_map(fn($row) => $row['name'], $stmt->fetchAll());

    // Permissions by type
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

    // Update last login (pakai ip publik kalau kamu sudah pakai helper itu)
    $ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $stmt = $pdo->prepare("UPDATE emr_users SET last_login_at = NOW(), last_login_ip = ? WHERE id = ?");
    $stmt->execute([$ip, $user['id']]);

    emr_audit('login_success', 'User logged in');

    echo json_encode([
        'success' => true,
        'message' => 'You have successfully logged in!',
        'data' => ['redirect' => EMR_HOME_URL],
    ]);
    exit;

} catch (Throwable $e) {
    emr_audit('login_failed', 'Server error');
    emr_json_error('Server error', 500);
    exit;
}
