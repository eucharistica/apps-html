<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

if (!emr_can('admin.permissions.view')) {
  http_response_code(403);
  echo json_encode(['success' => false, 'message' => 'Forbidden']);
  exit;
}

$pdo = emr_pdo();

$sql = "
  SELECT
    p.id,
    p.name,
    p.created_at,
    r.id AS role_id,
    r.name AS role_name
  FROM emr_permissions p
  LEFT JOIN emr_role_has_permissions rp ON rp.permission_id = p.id
  LEFT JOIN emr_roles r ON r.id = rp.role_id
  ORDER BY p.id DESC
";

$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$map = [];

foreach ($rows as $row) {
  $pid = (int)$row['id'];

  if (!isset($map[$pid])) {
    $createdAt = $row['created_at'] ?? null;

    $map[$pid] = [
      'id' => $pid,
      'name' => $row['name'],
      'created_at' => $createdAt,
      'created_label' => $createdAt ? date('d M Y, h:i a', strtotime($createdAt)) : '',
      'roles' => [],
    ];
  }

  if (!empty($row['role_id'])) {
    $map[$pid]['roles'][] = [
      'id' => (int)$row['role_id'],
      'name' => $row['role_name'],
    ];
  }
}

emr_json_success(array_values($map));
