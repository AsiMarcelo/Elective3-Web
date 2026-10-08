<?php
session_start();
require_once '../config/database.php'; // Include your database configuration

// Process POST request on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';


    $user = [
        'id' => 101,
        'email' => $email,
        'role' => 'employer' // 'admin', 'applicant', or 'employer'
    ];

    if ($user) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];

        // Redirect to central router
        header('Location: /index.php');
        exit();
    } else {
        $error = "Invalid email or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Mono:ital,wght@0,100..700;1,100..700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body style= "background-color:black">
    <title>4Hire-Login</title>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <h2 id="logForm" class="roboto-mono-roboFont">Login</h2>
    <card class="loginCard">
    <form class="logForm" method="POST" action="login.php">
        <label class="roboto-mono-roboFont">Email:</label>
        <input type="email" name="email" placeholder="Enter your email" required>
        
        <label class="roboto-mono-roboFont"><br>Password:</label>
        <input type="password" name="password" placeholder="Enter your password" required>
        <button type="submit">Log In</button>
        <p class="roboto-mono-roboFont">Don't have an account? <a href="SignUp.php">Register here</a></p>
    </form>

    </card>
    
</body>
</html>