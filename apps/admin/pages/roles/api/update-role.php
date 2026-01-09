<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_login();
emr_require_permission('admin.roles.edit');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emr_json_error('Method not allowed', 405);
}

$pdo = emr_pdo();

$roleId = (int)($_POST['role_id'] ?? 0);
$name   = trim($_POST['role_name'] ?? '');

if ($roleId <= 0) emr_json_error('Invalid role_id', 422);
if ($name === '') emr_json_error('role_name is required', 422);

try {
    // role exists?
    $stmt = $pdo->prepare("SELECT id FROM emr_roles WHERE id = ? LIMIT 1");
    $stmt->execute([$roleId]);
    if (!$stmt->fetch()) {
        emr_json_error('Role not found', 404);
    }

    // optional: prevent duplicate names
    $dup = $pdo->prepare("SELECT id FROM emr_roles WHERE name = ? AND id <> ? LIMIT 1");
    $dup->execute([$name, $roleId]);
    if ($dup->fetch()) {
        emr_json_error('Role name already exists', 409);
    }

    $upd = $pdo->prepare("UPDATE emr_roles SET name = ?, updated_at = NOW() WHERE id = ?");
    $upd->execute([$name, $roleId]);

    emr_json_success(['role_id' => $roleId, 'role_name' => $name], 'Role name updated');
} catch (Throwable $e) {
    error_log('update-role error: ' . $e->getMessage());
    emr_json_error('Server error: ' . $e->getMessage(), 500);
}
