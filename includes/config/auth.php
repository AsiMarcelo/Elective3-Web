<?php

function startAppSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // ... (keep your existing session settings)
    ini_set('session.use_strict_mode', '1');
    session_save_path('/tmp'); 
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/* --- ADD THESE MISSING FUNCTIONS BELOW --- */

function signedCsrfToken(string $secret): string
{
    if ($secret === '') {
        return '';
    }

    $payload = (string) (time() + 1800) . '.' . bin2hex(random_bytes(16));
    return $payload . '.' . hash_hmac('sha256', $payload, $secret);
}

function validSignedCsrfToken(mixed $token, string $secret): bool
{
    if ($secret === '' || !is_string($token)
        || !preg_match('/^(\d{10})\.([a-f0-9]{32})\.([a-f0-9]{64})$/D', $token, $matches)) {
        return false;
    }

    $expiresAt = (int) $matches[1];
    if ($expiresAt < time() || $expiresAt > time() + 1800) {
        return false;
    }

    $payload = $matches[1] . '.' . $matches[2];
    return hash_equals(hash_hmac('sha256', $payload, $secret), $matches[3]);
}