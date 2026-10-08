<?php

function getDbConnection(): PDO
{
    $host = $_ENV['SUPABASE_DB_HOST'] ?? getenv('SUPABASE_DB_HOST');
    $port = $_ENV['SUPABASE_DB_PORT'] ?? getenv('SUPABASE_DB_PORT') ?: '5432';
    $dbname = $_ENV['SUPABASE_DB_NAME'] ?? getenv('SUPABASE_DB_NAME') ?: 'postgres';
    $user = $_ENV['SUPABASE_DB_USER'] ?? getenv('SUPABASE_DB_USER');
    $password = $_ENV['SUPABASE_DB_PASSWORD'] ?? getenv('SUPABASE_DB_PASSWORD');

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname}";

    try {
        return new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
    }
}