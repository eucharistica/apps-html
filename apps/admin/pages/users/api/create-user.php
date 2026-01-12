<?php
// apps/admin/pages/users/api/create-user.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

// CSRF
$token = $_POST['_token'] ?? null;
if (!emr_csrf_validate($token)) {
    emr_json_error('Invalid CSRF token', 403);
    exit;
}

if (!emr_can('admin.users.create')) {
    return_json_error('Forbidden', 403);
}

$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// NEW: role_ids[]
$role_ids = $_POST['role_ids'] ?? [];
if (!is_array($role_ids)) $role_ids = [];
$role_ids = array_values(array_filter(array_map('intval', $role_ids), fn($x) => $x > 0));

if (!$name || !$username || !$password) {
    return_json_error('Name, username, and password are required', 422);
}

$pdo = emr_pdo();
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("
        INSERT INTO emr_users (username, name, email, password, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    $stmt->execute([$username, $name, $email ?: null, $hashedPassword]);
    $userId = (int)$pdo->lastInsertId();

    if (!empty($role_ids)) {
        $stmt = $pdo->prepare("INSERT INTO emr_user_has_roles (user_id, role_id) VALUES (?, ?)");
        foreach ($role_ids as $rid) {
            $stmt->execute([$userId, $rid]);
        }
    }

    $pdo->commit();

    emr_audit('admin.user_created', 'Created user: ' . $username);
    emr_json_success([], 'User berhasil dibuat');

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    return_json_error('Failed to create user: ' . $e->getMessage(), 500);
}
