<?php

// Local PHP server router that mirrors the public URL rewrites in vercel.json.
$requestPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/');

// Let the built-in server serve project assets (CSS, images, and similar files).
$staticPath = realpath(__DIR__ . DIRECTORY_SEPARATOR . ltrim($requestPath, '/\\'));
if ($requestPath !== '/' && $staticPath !== false && str_starts_with($staticPath, __DIR__ . DIRECTORY_SEPARATOR)
    && is_file($staticPath) && strtolower(pathinfo($staticPath, PATHINFO_EXTENSION)) !== 'php') {
    return false;
}

// Vercel's public /path maps to the physical api/path function. Also accept /api/path locally.
$functionPath = preg_replace('#^/api(?=/|$)#i', '', $requestPath);
$functionPath = $functionPath === '/' ? '/index.php' : $functionPath;
$functionRoot = realpath(__DIR__ . '/api');
$functionFile = $functionRoot === false
    ? false
    : realpath($functionRoot . DIRECTORY_SEPARATOR . ltrim($functionPath, '/\\'));

if ($functionRoot === false || $functionFile === false
    || !str_starts_with($functionFile, $functionRoot . DIRECTORY_SEPARATOR)
    || !is_file($functionFile) || strtolower(pathinfo($functionFile, PATHINFO_EXTENSION)) !== 'php') {
    http_response_code(404);
    echo 'Not Found';
    return true;
}

$_SERVER['SCRIPT_FILENAME'] = $functionFile;
require $functionFile;
return true;
