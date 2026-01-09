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
$permissionIdsJson = $_POST['permission_ids'] ?? '[]';
$permissionIds = json_decode($permissionIdsJson, true);

if ($roleId <= 0) emr_json_error('Invalid role_id', 422);
if (!is_array($permissionIds)) $permissionIds = [];

try {
    $pdo->beginTransaction();

    $stmtRole = $pdo->prepare("SELECT id FROM emr_roles WHERE id = ? LIMIT 1");
    $stmtRole->execute([$roleId]);
    if (!$stmtRole->fetch()) {
        $pdo->rollBack();
        emr_json_error('Role not found', 404);
    }

    $stmtDel = $pdo->prepare("DELETE FROM emr_role_has_permissions WHERE role_id = ?");
    $stmtDel->execute([$roleId]);

    if (count($permissionIds) > 0) {
        $stmtIns = $pdo->prepare("INSERT INTO emr_role_has_permissions (role_id, permission_id) VALUES (?, ?)");
        foreach ($permissionIds as $pid) {
            $pid = (int)$pid;
            if ($pid > 0) $stmtIns->execute([$roleId, $pid]);
        }
    }

    $pdo->commit();

    // emr_json_success() di project kamu tidak boleh null
    emr_json_success(['role_id' => $roleId], 'Role permissions updated');
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('update-role-permissions error: ' . $e->getMessage());
    emr_json_error('Server error: ' . $e->getMessage(), 500);
}
