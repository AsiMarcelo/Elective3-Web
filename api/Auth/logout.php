<?php
require_once __DIR__ . '/../../includes/config/auth.php';
startAppSession();

$_SESSION = [];

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

session_destroy();

// 4. Clear the application authentication cookies
setcookie('user_id', '', time() - 3600, '/');
setcookie('user_role', '', time() - 3600, '/');
setcookie('user_name', '', time() - 3600, '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/styles.css">
    <title>4Hire - Logged out</title>
</head>
<body style="background-color:black">
    <h2 id="logForm" class="roboto-mono-roboFont">Logged out</h2>

    <card class="loginCard">
        <div style="display: flex; flex-direction: column; width: 100%; align-items: center; text-align: center;">
            <p class="roboto-mono-roboFont" style="color: #f4f0f0; margin-bottom: 15px;">You have been logged out successfully.</p>
            <p class="roboto-mono-roboFont"><a href="/Auth/login.php">Log in again</a></p>
        </div>
    </card>
</body>
</html>