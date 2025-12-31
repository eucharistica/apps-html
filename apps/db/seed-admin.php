<?php
// apps/db/seed-admin.php
// Run once from CLI or browser (then delete/lock down).

require_once __DIR__ . '/../config/database.php';

$pdo = emr_pdo();

$username = 'admin';
$passwordPlain = 'admin';
$hash = password_hash($passwordPlain, PASSWORD_BCRYPT);

// Ensure role exists
$pdo->exec("INSERT INTO emr_roles (name, created_at, updated_at)
            SELECT 'admin', NOW(), NOW()
            WHERE NOT EXISTS (SELECT 1 FROM emr_roles WHERE name='admin')");

$roleId = $pdo->query("SELECT id FROM emr_roles WHERE name='admin' LIMIT 1")->fetchColumn();

// Ensure user exists
$stmt = $pdo->prepare("SELECT id FROM emr_users WHERE username=? LIMIT 1");
$stmt->execute([$username]);
$userId = $stmt->fetchColumn();

if (!$userId) {
    $stmt = $pdo->prepare("INSERT INTO emr_users (username, password, status, created_at, updated_at)
                           VALUES (?, ?, 'active', NOW(), NOW())");
    $stmt->execute([$username, $hash]);
    $userId = $pdo->lastInsertId();
}

// Attach role
$stmt = $pdo->prepare("INSERT IGNORE INTO emr_user_has_roles (user_id, role_id) VALUES (?, ?)");
$stmt->execute([$userId, $roleId]);

echo "Seed admin OK. username=admin password=admin\n";
