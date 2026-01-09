<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_login();
emr_require_permission('admin.roles.view');

$pdo = emr_pdo();

$rows = $pdo->query("
    SELECT id, name
    FROM emr_permissions
    ORDER BY name ASC
")->fetchAll(PDO::FETCH_ASSOC);

emr_json_success(['permissions' => $rows]);
