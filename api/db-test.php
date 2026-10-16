<?php
require __DIR__ . '/../includes/config/database.php';
try {
    $pdo = getDbConnection();
    echo "Connected. Rows in public.users: ", $pdo->query('SELECT COUNT(*) FROM public.users')->fetchColumn(), "\n";
} catch (Throwable $e) {
    echo get_class($e), ': ', $e->getMessage(), "\n";
    if ($e->getPrevious()) echo 'Cause: ', $e->getPrevious()->getMessage(), "\n";
}