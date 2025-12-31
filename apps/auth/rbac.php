<?php
// apps/auth/rbac.php

require_once __DIR__ . '/../config/bootstrap.php';

function emr_roles(): array
{
    $roles = $_SESSION['emr_user']['roles'] ?? [];
    $names = [];
    foreach ($roles as $r) {
        if (is_array($r) && isset($r['name'])) {
            $names[] = $r['name'];
        }
    }
    return array_values(array_unique($names));
}

function emr_has_role(string $roleName): bool
{
    return in_array($roleName, emr_roles(), true);
}

function emr_permissions(): array
{
    return $_SESSION['emr_user']['permissions'] ?? [];
}

function emr_can(string $permissionName): bool
{
    return in_array($permissionName, emr_permissions(), true);
}

function emr_require_role(array $roleNames): void
{
    foreach ($roleNames as $r) {
        if (emr_has_role($r)) {
            return;
        }
    }
    header('Location: ' . EMR_ERROR_403);
    exit;
}

function emr_require_permission(string $permissionName): void
{
    if (!emr_can($permissionName)) {
        header('Location: ' . EMR_ERROR_403);
        exit;
    }
}
