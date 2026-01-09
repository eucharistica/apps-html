<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_login();
emr_require_permission('admin.roles.view');

$pdo = emr_pdo();

$rows = $pdo->query("
    SELECT
        r.id,
        r.name,
        (SELECT COUNT(*) FROM emr_user_has_roles uhr WHERE uhr.role_id = r.id) AS user_count,
        (SELECT COUNT(*) FROM emr_role_has_permissions rhp WHERE rhp.role_id = r.id) AS perm_count
    FROM emr_roles r
    ORDER BY r.id ASC
")->fetchAll(PDO::FETCH_ASSOC);

emr_json_success(['roles' => $rows]);
