<?php
// apps/admin/pages/users/api/create-user.php

require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

// Validasi CSRF
$token = $_POST['_csrf'] ?? null;
if (!emr_csrf_validate($token)) {
    return_json_error('Invalid CSRF token', 403);
}


// Hak akses
if (!emr_can('admin.users.create')) {
    return_json_error('Forbidden', 403);
}

// Ambil input
$name     = trim($_POST['name'] ?? '');
$username = trim($_POST['username'] ?? '');
$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$role_id  = $_POST['role_id'] ?? null;

// Validasi required
if (!$name || !$username || !$password) {
    return_json_error('Name, username, and password are required');
}

$pdo = emr_pdo();
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

try {
    // Mulai transaction
    $pdo->beginTransaction();

    // Insert user
    $stmt = $pdo->prepare("
        INSERT INTO emr_users (username, name, email, password, status, created_at, updated_at)
        VALUES (?, ?, ?, ?, 'active', NOW(), NOW())
    ");
    $stmt->execute([$username, $name, $email ?: null, $hashedPassword]);
    $userId = $pdo->lastInsertId();

    // Assign role jika ada
    if ($role_id) {
        $stmt = $pdo->prepare("INSERT INTO emr_user_has_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->execute([$userId, $role_id]);
    }

    $pdo->commit();

    emr_audit('admin.user_created', 'Created user: ' . $username);

    emr_json_success([], 'User berhasil dibuat');

} catch (Exception $e) {
    $pdo->rollBack();
    return_json_error('Failed to create user: ' . $e->getMessage());
}
