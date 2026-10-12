<?php
session_start();

// 1. Clear all session data
$_SESSION = [];

// 2. Delete the session cookie in the browser
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// 3. Destroy the session on the server
session_destroy();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <title>4Hire - Logged out</title>
</head>
<body>
    <div class="rounded-rectangle">
    <h2 class="roboto-mono-roboFont">Logged out</h2>
    </div>

    <p>You have been logged out successfully.</p>

    <p><a href="/Auth/login.php">Log in again</a></p>
</body>
</html>