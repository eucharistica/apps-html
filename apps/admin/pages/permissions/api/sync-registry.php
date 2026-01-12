<?php
require_once __DIR__ . '/../../../../config/bootstrap.php';
require_once __DIR__ . '/../../../../auth/rbac.php';

header('Content-Type: application/json');

emr_require_permission_api('admin.access');

$pdo = emr_pdo();

$adminRoot = dirname(__DIR__, 3);             // .../apps/admin
$appsRoot  = dirname($adminRoot, 1);          // .../apps

$registryFiles = [
  $appsRoot . '/registry.php',                // apps/registry.php
  $adminRoot . '/registry.php',               // apps/admin/registry.php
  $appsRoot . '/home/registry.php',           // apps/home/registry.php
  $appsRoot . '/simrs/registry.php',          // apps/simrs/registry.php
];

// helper: normalisasi path jadi string rapi buat disimpan
$normPath = function ($p) {
    $real = realpath($p);
    return $real ? $real : $p;
};

$now = date('Y-m-d H:i:s');

$totalRoutes = 0;
$routeInserted = 0;
$routeUpdated = 0;
$permInserted = 0;
$skippedNoPermission = 0;
$skippedNoFile = 0;
$sample = [];

try {
    $pdo->beginTransaction();

    // prepared statements (lebih cepat & konsisten)
    $stmtPermFind = $pdo->prepare("SELECT id FROM emr_permissions WHERE name = ? LIMIT 1");
    $stmtPermIns = $pdo->prepare("INSERT INTO emr_permissions (name, created_at, updated_at) VALUES (?, ?, ?)");

    $stmtRouteUpsert = $pdo->prepare("
    INSERT INTO emr_registry_routes
      (app_key, route_key, title, url, file_path, permission, registry_path, checksum, is_active, first_seen_at, last_seen_at, synced_at)
    VALUES
      (:app_key, :route_key, :title, :url, :file_path, :permission, :registry_path, :checksum, 1, :first_seen_at, :last_seen_at, :synced_at)
    ON DUPLICATE KEY UPDATE
      title        = VALUES(title),
      url          = VALUES(url),
      file_path    = VALUES(file_path),
      permission   = VALUES(permission),
      registry_path= VALUES(registry_path),
      checksum     = VALUES(checksum),
      is_active    = 1,
      last_seen_at = VALUES(last_seen_at),
      synced_at    = VALUES(synced_at)
  ");

    foreach ($registryFiles as $file) {
        if (!file_exists($file))
            continue;

        // detect app_key dari lokasi
        $appKey = 'apps';
        if (str_contains($file, DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR))
            $appKey = 'admin';
        if (str_contains($file, DIRECTORY_SEPARATOR . 'home' . DIRECTORY_SEPARATOR))
            $appKey = 'home';
        if (str_contains($file, DIRECTORY_SEPARATOR . 'simrs' . DIRECTORY_SEPARATOR))
            $appKey = 'simrs';

        $routes = require $file;
        if (!is_array($routes))
            continue;

        foreach ($routes as $routeKey => $cfg) {
            $totalRoutes++;

            $title = $cfg['title'] ?? null;
            $permission = $cfg['permission'] ?? null;
            $filePath = $cfg['file'] ?? null;
            $url = $cfg['url'] ?? null;

            if (!$permission) {
                $skippedNoPermission++;
                continue;
            }
            if (!$filePath) {
                $skippedNoFile++;
                continue;
            }

            if (count($sample) < 5) {
                $sample[] = [
                    'app_key' => $appKey,
                    'route_key' => $routeKey,
                    'permission' => $permission,
                    'file' => $filePath,
                ];
            }

            // 1) ensure permission exists
            $stmtPermFind->execute([$permission]);
            $exists = $stmtPermFind->fetch(PDO::FETCH_ASSOC);
            if (!$exists) {
                $stmtPermIns->execute([$permission, $now, $now]);
                $permInserted++;
            }

            // 2) upsert route snapshot
            $payload = json_encode([
                'app_key' => $appKey,
                'route_key' => $routeKey,
                'title' => $title,
                'url' => $url,
                'file' => $filePath,
                'permission' => $permission,
            ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            $checksum = hash('sha256', $payload);

            $stmtRouteUpsert->execute([
                ':app_key' => $appKey,
                ':route_key' => $routeKey,
                ':title' => $title,
                ':url' => $url,
                ':file_path' => $filePath,
                ':permission' => $permission,
                ':registry_path' => $normPath($file),
                ':checksum' => $checksum,
                ':first_seen_at' => $now,
                ':last_seen_at' => $now,
                ':synced_at' => $now,
            ]);

            // MySQL rowCount untuk upsert: bisa 1 (insert) atau 2 (update) tergantung driver.
            $rc = $stmtRouteUpsert->rowCount();
            if ($rc === 1)
                $routeInserted++;
            else if ($rc === 2)
                $routeUpdated++;
        }
    }

    // optional: set is_active=0 untuk route yang tidak muncul lagi (tidak dilakukan sekarang biar aman)

    $pdo->commit();

    emr_json_success([
        'message' => 'Sync registry selesai',
        'summary' => [
            'routes_scanned' => $totalRoutes,
            'routes_inserted' => $routeInserted,
            'routes_updated' => $routeUpdated,
            'permissions_inserted' => $permInserted,
            'synced_at' => $now,
        ],
        'skipped' => [
        'no_permission' => $skippedNoPermission,
        'no_file' => $skippedNoFile,
        ],
        'sample' => $sample,
    ]);
} catch (Throwable $e) {
    if ($pdo->inTransaction())
        $pdo->rollBack();
    error_log('sync-registry error: ' . $e->getMessage());
    emr_json_error('Server error', 500);
}
