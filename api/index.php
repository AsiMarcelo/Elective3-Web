<?php
    ob_start();
    require_once __DIR__ . '/../includes/config/auth.php';
    $user_id = $_COOKIE['user_id'] ?? null;
    $user_role = $_COOKIE['user_role'] ?? null;

    if (!$user_id || !$user_role) {
        header('Location: /Auth/login.php');
        exit();
    }

    // Route based on account type
    switch ($user_role) {
        case 'admin':
            header('Location: /Dashboards/Admin.php');
            exit();

        case 'employer':
            header('Location: /Dashboards/Employer.php');
            exit();

        case 'applicant':
            header('Location: /Dashboards/Applicant.php');
            exit();

        default:
            // Clear cookies on invalid role
            setcookie('user_id', '', time() - 3600, '/');
            setcookie('user_role', '', time() - 3600, '/');
            header('Location: /Auth/login.php');
            exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="refresh" content="0; url=/Auth/login.php">
    <title>4Hire</title>
</head>
<body>
    <!-- <a href="Dashboards/register.php">Register here</a></p>
    !-->



</body>
</html>