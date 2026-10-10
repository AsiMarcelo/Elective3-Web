<?php
require_once __DIR__ . '/../config/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/storage.php';

startAppSession();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'applicant') {
    http_response_code(403);
    exit('Applicant access required.');
}

$userId = (int) $_SESSION['user_id'];
$bucket = 'applicant-resumes';

function resumeRedirect(string $result): never
{
    header('Location: /Dashboards/Applicant.php?resume=' . rawurlencode($result));
    exit();
}

function applicantResumePath(PDO $pdo, int $userId): ?string
{
    $query = $pdo->prepare('SELECT resume_storage_path FROM public.applicants WHERE user_id = :user_id');
    $query->execute(['user_id' => $userId]);
    $path = $query->fetchColumn();
    return is_string($path) && $path !== '' ? $path : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'download') {
    try {
        $pdo = getDbConnection();
        $path = applicantResumePath($pdo, $userId);
        if ($path === null) {
            resumeRedirect('missing');
        }
        $signed = storageRequest(
            'POST',
            'object/sign/' . storageObjectPath($bucket, $path),
            json_encode(['expiresIn' => 60], JSON_THROW_ON_ERROR),
            ['Content-Type: application/json']
        );
        $signedUrl = $signed['signedURL'] ?? '';
        if (!is_string($signedUrl) || $signedUrl === '') {
            throw new RuntimeException('Storage did not return a download link.');
        }
        if (str_starts_with($signedUrl, '/')) {
            [$supabaseUrl] = storageConfig();
            $signedUrl = $supabaseUrl . '/storage/v1' . $signedUrl;
        }
        header('Location: ' . $signedUrl, true, 302);
        exit();
    } catch (Throwable $e) {
        error_log('Resume download failed: ' . $e->getMessage());
        resumeRedirect('error');
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !validCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('Invalid request. Refresh the page and try again.');
}

try {
    $pdo = getDbConnection();
    $oldPath = applicantResumePath($pdo, $userId);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        if ($oldPath === null) {
            resumeRedirect('missing');
        }
        $update = $pdo->prepare(
            'UPDATE public.applicants
             SET resume_storage_path = NULL, resume_original_name = NULL,
                 resume_mime_type = NULL, resume_size_bytes = NULL, resume_uploaded_at = NULL
             WHERE user_id = :user_id'
        );
        $update->execute(['user_id' => $userId]);
        try {
            storageRequest('DELETE', 'object/' . rawurlencode($bucket), json_encode(['prefixes' => [$oldPath]], JSON_THROW_ON_ERROR), ['Content-Type: application/json']);
        } catch (Throwable $e) {
            error_log('Deleted resume remains in storage and needs cleanup: ' . $e->getMessage());
        }
        resumeRedirect('deleted');
    }

    if ($action !== 'upload' || !isset($_FILES['resume'])) {
        resumeRedirect('invalid');
    }

    $file = $_FILES['resume'];
    if ($file['error'] !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        resumeRedirect('upload_error');
    }
    if ((int) $file['size'] < 1 || (int) $file['size'] > 5 * 1024 * 1024) {
        resumeRedirect('too_large');
    }

    $originalName = basename((string) $file['name']);
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    // Check file signatures directly so uploads work even if PHP's optional
    // Fileinfo extension is disabled. The extension alone is not trusted.
    $signature = file_get_contents($file['tmp_name'], false, null, 0, 8);
    $validSignature = match ($extension) {
        'pdf' => is_string($signature) && str_starts_with($signature, '%PDF-'),
        'doc' => is_string($signature) && str_starts_with($signature, "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1"),
        'docx' => is_string($signature) && str_starts_with($signature, "PK\x03\x04"),
        default => false,
    };
    if (!$validSignature) {
        resumeRedirect('invalid_type');
    }
    $storageMimeType = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ][$extension];

    $newPath = $userId . '/' . bin2hex(random_bytes(16)) . '.' . $extension;
    $contents = file_get_contents($file['tmp_name']);
    if ($contents === false) {
        throw new RuntimeException('Could not read the uploaded resume.');
    }

    storageRequest('POST', 'object/' . storageObjectPath($bucket, $newPath), $contents, [
        'Content-Type: ' . $storageMimeType,
        'x-upsert: false',
    ]);

    try {
        $update = $pdo->prepare(
            'UPDATE public.applicants
             SET resume_storage_path = :path, resume_original_name = :name,
                 resume_mime_type = :mime, resume_size_bytes = :size,
                 resume_uploaded_at = CURRENT_TIMESTAMP
             WHERE user_id = :user_id'
        );
        $update->execute([
            'path' => $newPath,
            'name' => $originalName,
            'mime' => $storageMimeType,
            'size' => (int) $file['size'],
            'user_id' => $userId,
        ]);
        if ($update->rowCount() !== 1) {
            throw new RuntimeException('Applicant profile was not found.');
        }
    } catch (Throwable $e) {
        try {
            storageRequest('DELETE', 'object/' . rawurlencode($bucket), json_encode(['prefixes' => [$newPath]], JSON_THROW_ON_ERROR), ['Content-Type: application/json']);
        } catch (Throwable $cleanupError) {
            error_log('Failed to clean up a resume after database failure: ' . $cleanupError->getMessage());
        }
        throw $e;
    }

    if ($oldPath !== null) {
        try {
            storageRequest('DELETE', 'object/' . rawurlencode($bucket), json_encode(['prefixes' => [$oldPath]], JSON_THROW_ON_ERROR), ['Content-Type: application/json']);
        } catch (Throwable $e) {
            error_log('Old resume remains in storage and needs cleanup: ' . $e->getMessage());
        }
    }

    resumeRedirect('uploaded');
} catch (Throwable $e) {
    error_log('Resume operation failed: ' . $e->getMessage());
    resumeRedirect('error');
}
