<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_login();
emr_require_permission('admin.roles.view');

$pdo = emr_pdo();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    emr_json_error('Invalid id', 422);
}

$stmt = $pdo->prepare("SELECT id, name FROM emr_roles WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$role = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$role) {
    emr_json_error('Role not found', 404);
}

$stmt2 = $pdo->prepare("SELECT permission_id FROM emr_role_has_permissions WHERE role_id = ? ORDER BY permission_id ASC");
$stmt2->execute([$id]);
$permIds = $stmt2->fetchAll(PDO::FETCH_COLUMN);

emr_json_success([
    'id' => (int)$role['id'],
    'name' => $role['name'],
    'permission_ids' => array_map('intval', $permIds),
]);
