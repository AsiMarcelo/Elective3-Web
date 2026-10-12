<?php

function storageConfig(): array
{
    $url = rtrim($_ENV['SUPABASE_URL'] ?? getenv('SUPABASE_URL') ?: '', '/');
    $key = $_ENV['SUPABASE_SERVICE_ROLE_KEY'] ?? getenv('SUPABASE_SERVICE_ROLE_KEY') ?: '';

    if ($url === '' || $key === '') {
        throw new RuntimeException('Supabase Storage is not configured.');
    }

    return [$url, $key];
}

function storageRequest(string $method, string $path, ?string $body = null, array $headers = []): array
{
    [$url, $key] = storageConfig();
    $requestHeaders = array_merge([
        'Authorization: Bearer ' . $key,
        'apikey: ' . $key,
    ], $headers);
    $httpOptions = [
        'method' => $method,
        'header' => implode("\r\n", $requestHeaders),
        'timeout' => 60,
        'ignore_errors' => true,
    ];
    if ($body !== null) {
        $httpOptions['content'] = $body;
    }
    $context = stream_context_create([
        'http' => $httpOptions,
        'ssl' => [
            'verify_peer' => true,
            'verify_peer_name' => true,
        ],
    ]);
    $response = @file_get_contents($url . '/storage/v1/' . ltrim($path, '/'), false, $context);
    $responseHeaders = $http_response_header ?? [];
    $status = 0;
    if (isset($responseHeaders[0]) && preg_match('/\s(\d{3})\s/', $responseHeaders[0], $matches)) {
        $status = (int) $matches[1];
    }
    if ($response === false) {
        throw new RuntimeException('Could not connect to Supabase Storage. Check PHP OpenSSL and network settings.');
    }
    if ($status < 200 || $status >= 300) {
        throw new RuntimeException('Storage returned HTTP ' . $status . ': ' . substr($response, 0, 500));
    }

    $decoded = json_decode($response, true);
    return is_array($decoded) ? $decoded : [];
}

function storageObjectPath(string $bucket, string $path): string
{
    $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));
    return rawurlencode($bucket) . '/' . $encodedPath;
}
