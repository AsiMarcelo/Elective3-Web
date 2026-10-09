<?php
session_start();

// Redirect logged-in users to index.php so they don't see sign-up again
if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email     = trim($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $role      = $_POST['role'] ?? '';
    $fullName  = trim($_POST['full_name'] ?? '');

    // 1. Validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters long.";
    }

    // Restrict publicly selectable roles (Admin/Dev should NOT be selectable here)
    $allowedRoles = ['applicant', 'employer'];
    if (!in_array($role, $allowedRoles, true)) {
        $errors[] = "Please select a valid account type.";
    }

    // 2. If validation passes, create user
    if (empty($errors)) {
        // Securely hash the password (Bcrypt / Argon2)
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        /* 
        // Example PDO Database Insertion:
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$fullName, $email, $hashedPassword, $role]);
        $newUserId = $pdo->lastInsertId();
        

        // For demonstration, assume user ID 102 was created:
        $newUserId = 102;

        // Option A: Auto-login after registration
        session_regenerate_id(true);
        $_SESSION['user_id']   = $newUserId;
        $_SESSION['user_role'] = $role;

        // Redirect to central router (index.php) which routes to their dashboard
        header('Location: /index.php');
        exit();

        /* 
        // Option B: Force manual login after registration
        header('Location: /login.php?registered=1');
        exit();
        */
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
    <title>Create an Account</title>
</head>
<body>
    <h2 class="roboto-mono-roboFont">Sign Up</h2>

    <?php if (!empty($errors)): ?>
        <ul style="color: red;">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="SignUp.php">
        <div>
            <label class="roboto-mono-roboFont" for="full_name">Full Name / Company Name:</label><br>
            <input type="text" id="full_name" name="full_name" required value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>">
        </div>
        <br>

        <div>
            <label class="roboto-mono-roboFont" for="email">Email Address:</label><br>
            <input type="email" id="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <br>

        <div>
            <label class="roboto-mono-roboFont" for="password">Password:</label><br>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
        <br>

        <div>
            <label class="roboto-mono-roboFont">I am signing up as:</label><br>
            <input type="radio" id="applicant" name="role" value="applicant" checked>
            <label class="roboto-mono-roboFont" for="applicant">Job Applicant</label>
            <br>
            <input type="radio" id="employer" name="role" value="employer">
            <label class="roboto-mono-roboFont" for="employer">Employer / Recruiter</label>
        </div>
        <br>

        <button type="submit">Create Account</button>
    </form>

    <p class="roboto-mono-roboFont">Already have an account? <a href="/Auth/login.php">Log in here</a>.</p>
</body>
</html>