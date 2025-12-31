<?php
// apps/config/database.php

function emr_env(): array
{
    $envFile = __DIR__ . '/.env.php';
    $exampleFile = __DIR__ . '/.env.example.php';

    if (!file_exists($envFile)) {
        // Provide a clear hint if env is missing
        throw new RuntimeException("Missing env file: {$envFile}. Create it by copying {$exampleFile} to .env.php");
    }

    $env = require $envFile;
    if (!is_array($env)) {
        throw new RuntimeException('Env file must return an array');
    }

    return $env;
}

function emr_pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $env = emr_env();

    $host = $env['DB_HOST'] ?? '127.0.0.1';
    $port = $env['DB_PORT'] ?? '3306';
    $db   = $env['DB_NAME'] ?? '';
    $user = $env['DB_USER'] ?? '';
    $pass = $env['DB_PASS'] ?? '';
    $charset = $env['DB_CHARSET'] ?? 'utf8mb4';

    $dsn = "mysql:host={$host};port={$port};dbname={$db};charset={$charset}";

    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}
