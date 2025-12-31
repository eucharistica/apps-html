<?php
// apps/auth/rbac.php

require_once __DIR__ . '/../config/bootstrap.php';

/**
 * Return array of role names for current session user.
 */
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

/**
 * Return array of permission names for current session user.
 */
function emr_permissions(): array
{
    return $_SESSION['emr_user']['permissions'] ?? [];
}

function emr_can(string $permissionName): bool
{
    return in_array($permissionName, emr_permissions(), true);
}

/**
 * Enforce that current user has at least one role.
 */
function emr_require_role(array $roleNames): void
{
    foreach ($roleNames as $r) {
        if (emr_has_role($r)) {
            return;
        }
    }
    header('Location: ' . EMR_SIMRS_HOME . '?error=forbidden');
    exit;
}

/**
 * Enforce permission.
 */
function emr_require_permission(string $permissionName): void
{
    if (!emr_can($permissionName)) {
        header('Location: ' . EMR_SIMRS_HOME . '?error=forbidden');
        exit;
    }
}
