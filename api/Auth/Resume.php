<?php
$projectRoot = dirname(__DIR__, 2);
require_once $projectRoot . '/includes/config/auth.php';
require_once $projectRoot . '/includes/config/database.php';
require_once $projectRoot . '/includes/config/storage.php';

startAppSession();

$userId = filter_var($_COOKIE['user_id'] ?? null, FILTER_VALIDATE_INT);
$userRoleCookie = $_COOKIE['user_role'] ?? '';

if (!$userId || !in_array($userRoleCookie, ['applicant', 'employer'], true) || !is_string($_COOKIE['user_sig'] ?? null)) {
    http_response_code(403);
    exit('Access denied.');
}

try {
    $authPdo = getDbConnection();
    $authQuery = $authPdo->prepare(
        "SELECT id, password_hash, role FROM public.users WHERE id = :user_id LIMIT 1"
    );
    $authQuery->execute(['user_id' => $userId]);
    $authUser = $authQuery->fetch(PDO::FETCH_ASSOC);
    $expectedSignature = $authUser
        ? hash_hmac('sha256', $authUser['id'] . '|' . $authUser['role'], $authUser['password_hash'])
        : '';
    if (!$authUser || !hash_equals($expectedSignature, $_COOKIE['user_sig'])) {
        http_response_code(403);
        exit('Access denied.');
    }
} catch (Throwable $e) {
    error_log('Resume authorization failed: ' . $e->getMessage());
    http_response_code(503);
    exit('Unable to verify your account right now. Please try again.');
}

$userRole = $authUser['role'];
$bucket = 'applicant-resumes';

function resumeRedirect(string $result): never
{
    header('Location: /Dashboards/Applicant.php?resume=' . rawurlencode($result));
    exit();
}

function applicantResumePath(PDO $pdo, int $targetUserId): ?string
{
    $query = $pdo->prepare('SELECT resume_storage_path FROM public.applicants WHERE user_id = :user_id');
    $query->execute(['user_id' => $targetUserId]);
    $path = $query->fetchColumn();
    return is_string($path) && $path !== '' ? $path : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['action'] ?? '') === 'download') {
    try {
        $pdo = getDbConnection();
        
        // Determine whose resume to download based on user role
        if ($userRole === 'applicant') {
            $targetUserId = (int) $authUser['id'];
        } elseif ($userRole === 'employer') {
            $targetUserId = filter_var($_GET['applicant_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$targetUserId) {
                http_response_code(400);
                exit('Invalid applicant specified.');
            }
        } else {
            http_response_code(403);
            exit('Unauthorized access.');
        }

        $path = applicantResumePath($pdo, $targetUserId);
        if ($path === null) {
            if ($userRole === 'employer') {
                http_response_code(404);
                exit('Resume not found.');
            }
            resumeRedirect('missing');
        }

        // Generate a short-lived internal signed URL for server-side retrieval
        $signed = storageRequest(
            'POST',
            'object/sign/' . storageObjectPath($bucket, $path),
            json_encode(['expiresIn' => 30], JSON_THROW_ON_ERROR),
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

        // Fetch file metadata for proper headers
        $metaQuery = $pdo->prepare('SELECT resume_original_name, resume_mime_type FROM public.applicants WHERE user_id = :user_id');
        $metaQuery->execute(['user_id' => $targetUserId]);
        $meta = $metaQuery->fetch(PDO::FETCH_ASSOC);
        
        $mimeType = $meta['resume_mime_type'] ?? 'application/pdf';
        $originalName = $meta['resume_original_name'] ?? 'resume.pdf';

        // Secure Server-Side Proxy Streaming (Prevents direct public URL exposure)
        $fileContent = @file_get_contents($signedUrl);
        if ($fileContent === false) {
            throw new RuntimeException('Could not retrieve file content from storage.');
        }

        header('Content-Type: ' . $mimeType);
        header('Content-Disposition: inline; filename="' . basename($originalName) . '"');
        header('Content-Length: ' . strlen($fileContent));
        echo $fileContent;
        exit();

    } catch (Throwable $e) {
        error_log('Resume download failed: ' . $e->getMessage());
        if ($userRole === 'employer') {
            http_response_code(500);
            exit('Failed to stream resume file.');
        }
        resumeRedirect('error');
    }
}

// For applicant-only POST actions (upload/delete)
if ($userRole !== 'applicant') {
    http_response_code(403);
    exit('Applicant access required.');
}

$targetUserId = (int) $authUser['id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST'
    || !validSignedCsrfToken($_POST['csrf_token'] ?? null, $_COOKIE['user_sig'])) {
    http_response_code(400);
    exit('Invalid request. Refresh the page and try again.');
}

try {
    $pdo = getDbConnection();
    $oldPath = applicantResumePath($pdo, $targetUserId);
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        if ($oldPath === null) {
            resumeRedirect('missing');
        }
        $update = $pdo->prepare(
            'UPDATE public.applicants
             SET resume_storage_path = NULL, resume_original_name = NULL,
                 resume_mime_type = NULL, resume_size_bytes = NULL, resume_uploaded_at = NULL,
                 resume_path = NULL, resume_text = NULL, resume_keywords = NULL, ai_suggested_fields = NULL, resume_processed_at = NULL
             WHERE user_id = :user_id'
        );
        $update->execute(['user_id' => $targetUserId]);
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

    $newPath = $targetUserId . '/' . bin2hex(random_bytes(16)) . '.' . $extension;
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
            'user_id' => $targetUserId,
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

    // --- GEMINI AI PIPELINE INTEGRATION (Vercel Serverless /tmp Compatible) ---
    if ($extension === 'pdf') {
        try {
            $localDir = '/tmp/resumes/' . $targetUserId;
            if (!is_dir($localDir)) {
                @mkdir($localDir, 0755, true);
            }
            $localFileName = basename($newPath);
            $localFilePath = $localDir . '/' . $localFileName;
            copy($file['tmp_name'], $localFilePath);

            $relativePath = $targetUserId . '/' . $localFileName;
            $pathUpdate = $pdo->prepare('UPDATE public.applicants SET resume_path = :path WHERE user_id = :user_id');
           $pathUpdate = $pdo->prepare('UPDATE public.applicants SET resume_path = :path WHERE user_id = :user_id');
            if ($pathUpdate !== false) {
                $pathUpdate->execute(['path' => $relativePath, 'user_id' => $targetUserId]);
                }

            require_once dirname(__DIR__, 2) . '/GeminiFuncs/GeminiServices.php';
            $gemini = new GeminiServices($pdo);
            $gemini->processApplicant($targetUserId);
        } catch (Throwable $aiError) {
            error_log('AI Resume processing failed: ' . $aiError->getMessage());
        }
    }
    // -------------------------------------------------------------------------

    resumeRedirect('uploaded');
} catch (Throwable $e) {
    error_log('Resume operation failed: ' . $e->getMessage());
    resumeRedirect('error');
}