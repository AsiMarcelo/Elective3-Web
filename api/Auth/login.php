<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

startAppSession();
$error = null;
$registered = isset($_GET['registered']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        $error = 'Your form session expired. Refresh the page and try again.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $error = 'Invalid email or password.';
    } else {
        try {
            $pdo = getDbConnection();
            $stmt = $pdo->prepare(
                'SELECT id, full_name, email, password_hash, role
                 FROM public.users
                 WHERE lower(email) = :email
                 LIMIT 1'
            );
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])
                && in_array($user['role'], ['admin', 'applicant', 'employer'], true)) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['full_name'];
                unset($_SESSION['csrf_token']);

                header('Location: /index.php');
                exit();
            }

            $error = 'Invalid email or password.';
        } catch (Throwable $e) {
            error_log('Login failed because the database was unavailable: ' . $e->getMessage());
            $error = 'Login is temporarily unavailable. Please try again later.';
        }
    }
}

$csrf = csrfToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4Hire - Login</title>
</head>
<body style="background-color:black">
    <?php if ($registered): ?>
        <p role="status">Account created. You can now log in.</p>
    <?php endif; ?>
    <?php if ($error !== null): ?>
        <p role="alert" style="color:red"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <h2 id="logForm" class="roboto-mono-roboFont">Login</h2>
    <card class="loginCard">
        <form class="logForm" method="POST" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
            <label class="roboto-mono-roboFont" for="email">Email:</label>
            <input id="email" type="email" name="email" placeholder="Enter your email" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <label class="roboto-mono-roboFont" for="password"><br>Password:</label>
            <input id="password" type="password" name="password" placeholder="Enter your password" required autocomplete="current-password">
            <button type="submit">Log In</button>
            <p class="roboto-mono-roboFont">Don't have an account? <a href="SignUp.php">Register here</a></p>
        </form>
    </card>
</body>
</html>
