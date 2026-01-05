<?php
// apps/db/migrate.php
// Simple migration runner untuk pure PHP (tanpa Laravel)

// Load env config
$envFile = __DIR__ . '/../../apps/config/.env.php';
if (!file_exists($envFile)) {
    echo "Error: .env.php not found\n";
    exit(1);
}
$env = require $envFile;

// PDO connection
try {
    $dsn = "mysql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};charset=utf8mb4";
    $pdo = new PDO($dsn, $env['DB_USER'], $env['DB_PASS']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    echo "Error: Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// 1. Create migrations table if not exists
try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS emr_schema_migrations (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            migration VARCHAR(191) NOT NULL,
            batch INT NOT NULL,
            executed_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_migration (migration)
        ) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci
    ");
} catch (Exception $e) {
    echo "Error creating migrations table: " . $e->getMessage() . "\n";
    exit(1);
}

// 2. Get all pending migrations
$migrationsDir = __DIR__ . '/sql/migrations';
if (!is_dir($migrationsDir)) {
    echo "Error: migrations directory not found at $migrationsDir\n";
    exit(1);
}

$files = glob($migrationsDir . '/*.sql');
if (empty($files)) {
    echo "No migrations found.\n";
    exit(0);
}

sort($files);

// Get executed migrations
try {
    $stmt = $pdo->query("SELECT migration FROM emr_schema_migrations ORDER BY batch ASC");
    $executed = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'migration');
} catch (Exception $e) {
    $executed = [];
}

// 3. Get next batch number
try {
    $stmt = $pdo->query("SELECT MAX(batch) as max_batch FROM emr_schema_migrations");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $nextBatch = ($result['max_batch'] ?? 0) + 1;
} catch (Exception $e) {
    $nextBatch = 1;
}

// 4. Run pending migrations
$pending = [];
foreach ($files as $file) {
    $filename = basename($file);
    if (!in_array($filename, $executed)) {
        $pending[] = $file;
    }
}

if (empty($pending)) {
    echo "No pending migrations.\n";
    exit(0);
}

echo "Running " . count($pending) . " migration(s)...\n";
echo "---\n";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

foreach ($pending as $file) {
    $filename = basename($file);
    echo "Executing: $filename\n";

    try {
        // Read SQL file
        $sql = file_get_contents($file);

        // Split by `;` dan execute without transaction
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        foreach ($statements as $statement) {
            if (!empty($statement)) {
                $pdo->exec($statement);
            }
        }

        // Record migration
        $stmt = $pdo->prepare("INSERT INTO emr_schema_migrations (migration, batch) VALUES (?, ?)");
        $stmt->execute([$filename, $nextBatch]);

        echo "  ✓ OK\n";
    } catch (Exception $e) {
        echo "  ✗ ERROR: " . $e->getMessage() . "\n";
        echo "  File: $filename\n";
        exit(1);
    }
}

echo "---\n";
echo "All migrations executed successfully!\n";
exit(0);
