<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_login();
emr_require_permission('admin.roles.create');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    emr_json_error('Method not allowed', 405);
}

$pdo = emr_pdo();

$name = trim($_POST['role_name'] ?? '');
$permissionIdsJson = $_POST['permission_ids'] ?? '[]';
$permissionIds = json_decode($permissionIdsJson, true);

if ($name === '') {
    emr_json_error('role_name is required', 422);
}
if (!is_array($permissionIds)) $permissionIds = [];

$now = date('Y-m-d H:i:s');

try {
    $pdo->beginTransaction();

    // insert role
    $stmt = $pdo->prepare("INSERT INTO emr_roles (name, created_at, updated_at) VALUES (?, ?, ?)");
    $stmt->execute([$name, $now, $now]);
    $roleId = (int)$pdo->lastInsertId();

    // assign permissions
    if (count($permissionIds) > 0) {
        $stmtIns = $pdo->prepare("INSERT IGNORE INTO emr_role_has_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissionIds as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) $stmtIns->execute([$roleId, $pid]);
        }
    }

    $pdo->commit();
    emr_json_success(['role_id' => $roleId], 'Role created');

} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('create-role error: ' . $e->getMessage());
    emr_json_error('Server error: ' . $e->getMessage(), 500);
}
