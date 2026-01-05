<?php
// apps/app_registry.php
require_once __DIR__ . '/auth/rbac.php';

function emr_apps_registry(): array
{
    $root = defined('EMR_ROOT') ? EMR_ROOT : realpath(__DIR__ . '/..');
    $apps = require $root . '/apps/registry.php';

    $filtered = [];
    foreach ($apps as $key => $app) {
        $perm = $app['permission'] ?? null;
        if ($perm && !emr_can($perm)) continue;
        $filtered[$key] = $app + ['key' => $key];
    }

    usort($filtered, fn($a,$b) => ((int)($a['order'] ?? 9999)) <=> ((int)($b['order'] ?? 9999)));
    return $filtered;
}

function emr_current_app(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    if (preg_match('~/apps/([^/]+)/~', $uri, $m)) return $m[1];
    return 'root';
}
