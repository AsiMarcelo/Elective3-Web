<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';

startAppSession();

if (isset($_SESSION['user_id'])) {
    header('Location: /index.php');
    exit();
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';
    $role = $_POST['role'] ?? '';
    $fullName = trim($_POST['full_name'] ?? '');
    $contactNumber = trim($_POST['contact_number'] ?? '');

    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your form session expired. Refresh the page and try again.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }
    $nameLength = preg_match_all('/./us', $fullName);
    if ($fullName === '' || $nameLength === false || $nameLength > 200) {
        $errors[] = 'Please enter a name of 1 to 200 characters.';
    }
    if (strlen($contactNumber) > 50) {
        $errors[] = 'Contact number must be 50 characters or fewer.';
    }
    if (strlen($password) < 8 || strlen($password) > 72) {
        $errors[] = 'Password must be between 8 and 72 bytes.';
    }
    if (!in_array($role, ['applicant', 'employer'], true)) {
        $errors[] = 'Please select a valid account type.';
    }

    if (!$errors) {
        try {
            $pdo = getDbConnection();
            $pdo->beginTransaction();
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO public.users (full_name, email, password_hash, role)
                 VALUES (:full_name, :email, :password_hash, :role)
                 RETURNING id'
            );
            $stmt->execute([
                'full_name' => $fullName,
                'email' => $email,
                'password_hash' => $passwordHash,
                'role' => $role,
            ]);
            $userId = (int) $stmt->fetchColumn();

            if ($role === 'applicant') {
                $profile = $pdo->prepare(
                    'INSERT INTO public.applicants (user_id, contact_number)
                     VALUES (:user_id, :contact_number)'
                );
                $profile->execute([
                    'user_id' => $userId,
                    'contact_number' => $contactNumber !== '' ? $contactNumber : null,
                ]);
            } else {
                $profile = $pdo->prepare(
                    'INSERT INTO public.employers (user_id, company_name, contact_number)
                     VALUES (:user_id, :company_name, :contact_number)'
                );
                $profile->execute([
                    'user_id' => $userId,
                    'company_name' => $fullName,
                    'contact_number' => $contactNumber !== '' ? $contactNumber : null,
                ]);
            }

            $pdo->commit();

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = $role;
            $_SESSION['user_name'] = $fullName;
            unset($_SESSION['csrf_token']);

            header('Location: /index.php');
            exit();
        } catch (PDOException $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            if ($e->getCode() === '23505') {
                $errors[] = 'An account with that email already exists.';
            } else {
                error_log('Signup database error: ' . $e->getMessage());
                $errors[] = 'Account creation is temporarily unavailable. Please try again later.';
            }
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Signup failed because the database was unavailable: ' . $e->getMessage());
            $errors[] = 'Account creation is temporarily unavailable. Please try again later.';
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
    <title>Create an Account</title>
</head>
<body>
    <h2 class="logForm">Sign Up</h2>

    <?php if ($errors): ?>
        <ul role="alert" style="color:red">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <form method="POST" action="SignUp.php">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
        <div>
            <label class="roboto-mono-roboFont" for="full_name">Full Name / Company Name:</label><br>
            <input type="text" id="full_name" name="full_name" maxlength="200" required value="<?= htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div><br>
        <div>
            <label class="roboto-mono-roboFont" for="email">Email Address:</label><br>
            <input type="email" id="email" name="email" required autocomplete="email" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div><br>
        <div>
            <label class="roboto-mono-roboFont" for="contact_number">Contact Number (optional):</label><br>
            <input type="tel" id="contact_number" name="contact_number" maxlength="50" autocomplete="tel" value="<?= htmlspecialchars($_POST['contact_number'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div><br>
        <div>
            <label class="roboto-mono-roboFont" for="password">Password:</label><br>
            <input type="password" id="password" name="password" required minlength="8" maxlength="72" autocomplete="new-password">
        </div><br>
        <div>
            <label class="roboto-mono-roboFont">I am signing up as:</label><br>
            <input type="radio" id="applicant" name="role" value="applicant" checked>
            <label class="roboto-mono-roboFont" for="applicant">Job Applicant</label><br>
            <input type="radio" id="employer" name="role" value="employer">
            <label class="roboto-mono-roboFont" for="employer">Employer / Recruiter</label>
        </div><br>
        <button type="submit">Create Account</button>
    </form>

    <p class="roboto-mono-roboFont">Already have an account? <a href="/Auth/login.php">Log in here</a>.</p>
</body>
</html>
