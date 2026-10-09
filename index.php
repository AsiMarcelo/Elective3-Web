    <?php
    session_start();

    // Redirect unauthenticated users back to login
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role'])) {
        header('Location: Auth/login.php');
        exit();
    }
    //Route based on account type
    switch ($_SESSION['user_role']) {
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
        // Handle unexpected role or log out
            session_destroy();
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