<?php
// apps/admin/pages/users/api/update-user.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

// CSRF
$token = $_POST['_token'] ?? null;
if (!emr_csrf_validate($token)) {
    emr_json_error('Invalid CSRF token', 403);
    exit;
}

if (!emr_can('admin.users.edit')) {
    return_json_error('Forbidden', 403);
}

$user_id = $_POST['user_id'] ?? null;
$name = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$status = $_POST['status'] ?? 'active';

// NEW: role_ids[]
$role_ids = $_POST['role_ids'] ?? [];
if (!is_array($role_ids)) $role_ids = [];

$role_ids = array_values(array_filter(array_map('intval', $role_ids), fn($x) => $x > 0));

if (!$user_id || !$name || !$username) {
    return_json_error('Missing required fields', 422);
}

$pdo = emr_pdo();

try {
    $pdo->beginTransaction();

    // update user row
    $updates = ['name = ?', 'username = ?', 'email = ?', 'status = ?', 'updated_at = NOW()'];
    $params = [$name, $username, $email ?: null, $status];

    if ($password) {
        $updates[] = 'password = ?';
        $params[] = password_hash($password, PASSWORD_BCRYPT);
    }

    $params[] = $user_id;

    $stmt = $pdo->prepare("UPDATE emr_users SET " . implode(', ', $updates) . " WHERE id = ?");
    $stmt->execute($params);

    // sync roles (delete all then insert)
    $stmt = $pdo->prepare("DELETE FROM emr_user_has_roles WHERE user_id = ?");
    $stmt->execute([$user_id]);

    if (!empty($role_ids)) {
        $stmt = $pdo->prepare("INSERT INTO emr_user_has_roles (user_id, role_id) VALUES (?, ?)");
        foreach ($role_ids as $rid) {
            $stmt->execute([$user_id, $rid]);
        }
    }

    $pdo->commit();
    $stmt = $pdo->prepare("UPDATE emr_users SET auth_version = auth_version + 1 WHERE id = ?");
    $stmt->execute([$user_id]);

    emr_audit('admin.user_updated', 'Updated user', [
    'user_id' => (int)$user_id,
    ]);
    emr_json_success([], 'User updated');

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return_json_error($e->getMessage(), 500);
}
