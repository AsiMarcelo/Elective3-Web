<?php

require_once __DIR__ . '/bootstrap.php';

function getDbConnection(): PDO
{
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: '';
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '5432';
    $dbname = $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE') ?: 'postgres';
    $user = $_ENV['DB_USERNAME'] ?? getenv('DB_USERNAME') ?: '';
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

    if ($host === '' || $user === '' || $password === '') {
        throw new RuntimeException('Database configuration is incomplete.');
    }

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";

    try {
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        throw new RuntimeException('Database connection failed.', 0, $e);
    }
}
