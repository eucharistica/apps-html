<?php
// apps/auth/rbac.php

require_once __DIR__ . '/../config/bootstrap.php';

function emr_request_path(): string
{
    return (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH); // path-only [web:580]
}

function emr_current_app_type(): string
{
    $p = emr_request_path();

    if (strpos($p, '/apps/admin/') === 0) return 'admin';
    if (strpos($p, '/apps/simrs/') === 0) return 'simrs';
    return 'home';
}

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
 * Return permissions for current app context.
 * - /apps/admin/* => permissions_by_type['admin']
 * - /apps/simrs/* => permissions_by_type['simrs']
 * - /apps/home/* (launcher) => all permissions (union)
 */
function emr_permissions(): array
{
    $u = $_SESSION['emr_user'] ?? null;
    if (!$u) return [];

    $app = emr_current_app_type();

    if ($app === 'home') {
    return $u['permissions'] ?? [];
    }

    // Allow superuser cross-app: jika punya admin.superuser (atau role tertentu), pakai union.
    $all = $u['permissions'] ?? [];
    if (in_array('admin.superuser', $all, true) || emr_has_role('superuser')) {
        return $all;
    }

    $byType = $u['permissions_by_type'] ?? [];
    $scoped = $byType[$app] ?? null;

    if (!is_array($scoped) || count($scoped) === 0) {
        return $u['permissions'] ?? [];
    }

    return $scoped;
}

function emr_can(string $permissionName): bool
{
    $u = $_SESSION['emr_user'] ?? null;
    if (!$u) return false;

    $all = $u['permissions'] ?? [];
    if (in_array('system.superuser', $all, true)) return true;

    return in_array($permissionName, emr_permissions(), true);
}

function emr_require_role(array $roleNames): void
{
    foreach ($roleNames as $r) {
        if (emr_has_role($r)) return;
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

function emr_require_permission_api(string $permissionName): void
{
    if (!emr_can($permissionName)) {
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Forbidden']);
        exit;
    }
}

