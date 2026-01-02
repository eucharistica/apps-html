<?php
// apps/simrs/menu.php
require_once __DIR__ . '/../auth/rbac.php';

function emr_simrs_menu(array $registry): array
{
    // Filter by permission
    $items = [];
    foreach ($registry as $key => $r) {
        $perm = $r['permission'] ?? null;
        if ($perm && !emr_can($perm)) {
            continue;
        }
        $items[$key] = $r + ['key' => $key];
    }

    // Group
    $grouped = [];
    foreach ($items as $key => $r) {
        $group = $r['group'] ?? 'Lainnya';
        $grouped[$group][] = $r;
    }

    // Sort items per group by order then title
    foreach ($grouped as $group => &$list) {
        usort($list, function ($a, $b) {
            $oa = (int)($a['order'] ?? 9999);
            $ob = (int)($b['order'] ?? 9999);
            if ($oa === $ob) {
                return strcmp((string)($a['title'] ?? ''), (string)($b['title'] ?? ''));
            }
            return $oa <=> $ob;
        });
    }
    unset($list);

    // Sort groups by min(order)
    uksort($grouped, function ($ga, $gb) use ($grouped) {
        $mina = min(array_map(fn($x) => (int)($x['order'] ?? 9999), $grouped[$ga]));
        $minb = min(array_map(fn($x) => (int)($x['order'] ?? 9999), $grouped[$gb]));
        return $mina <=> $minb;
    });

    return $grouped;
}
