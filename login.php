<?php
session_start();

// Process POST request on form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // TODO: Verify credentials against your database table.
    // Example authenticated user data:
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
    <meta charset="UTF-8">
    <title>Login</title>
</head>
<body>
    <?php if (isset($error)): ?>
        <p style="color: red;"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label>Email:</label>
        <input type="email" name="email" required>
        
        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Log In</button>
    </form>
</body>
</html>