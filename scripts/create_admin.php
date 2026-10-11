<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit('Not found.');
}

require_once __DIR__ . '/../config/database.php';

function prompt(string $label): string
{
    fwrite(STDOUT, $label);
    return trim((string) fgets(STDIN));
}

$fullName = prompt('Admin full name: ');
$email = strtolower(prompt('Admin email: '));

if ($fullName === '' || strlen($fullName) > 200 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Enter a name (max 200 characters) and a valid email address.\n");
    exit(1);
}

// Generate a strong one-time password locally so no password is shared in chat
// or written in the command history. The script is CLI-only.
$initialPassword = bin2hex(random_bytes(18));
$passwordHash = password_hash($initialPassword, PASSWORD_DEFAULT);

try {
    $pdo = getDbConnection();
    $insert = $pdo->prepare(
        'INSERT INTO public.users (full_name, email, password_hash, role)
         VALUES (:full_name, :email, :password_hash, \'admin\')'
    );
    $insert->execute([
        'full_name' => $fullName,
        'email' => $email,
        'password_hash' => $passwordHash,
    ]);
} catch (PDOException $e) {
    if ($e->getCode() === '23505') {
        fwrite(STDERR, "That email is already registered. No admin account was created.\n");
        exit(1);
    }
    fwrite(STDERR, "Could not create the admin account. Check the database connection and users table.\n");
    error_log('Admin provisioning failed: ' . $e->getMessage());
    exit(1);
} catch (Throwable $e) {
    fwrite(STDERR, "Could not create the admin account. Check the database connection.\n");
    error_log('Admin provisioning failed: ' . $e->getMessage());
    exit(1);
}

fwrite(STDOUT, "\nAdmin account created. Save this one-time password now; it will not be shown again:\n");
fwrite(STDOUT, $initialPassword . "\n");
fwrite(STDOUT, "Sign in at /Auth/login.php using {$email}.\n");
