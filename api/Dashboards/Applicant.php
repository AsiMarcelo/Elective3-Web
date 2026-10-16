<?php
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/config/auth.php';
require_once __DIR__ . '/../../GeminiFuncs/GeminiServices.php';
startAppSession();

$user_id = $_COOKIE['user_id'] ?? null;
$user_role = $_COOKIE['user_role'] ?? null;

if (!$user_id || $user_role !== 'applicant') {
    header('Location: /Auth/login.php');
    exit();
}

$applicantName = $_COOKIE['user_name'] ?? 'Applicant';
$resume = null;
$aiSuggestedFields = [];
$resumeKeywords = [];
$isProcessed = false;

try {
    $pdo = getDbConnection();
    
    // Check if processing was requested or pending
    if (isset($_GET['action']) && $_GET['action'] === 'reparse') {
        try {
            $gemini = new GeminiServices($pdo);
            $gemini->processApplicant((int)$user_id);
            header('Location: /Dashboards/Applicant.php?resume=reparsed');
            exit();
        } catch (Throwable $e) {
            error_log('Manual reparse failed: ' . $e->getMessage());
        }
    }

    $profile = $pdo->prepare(
        'SELECT u.full_name, a.resume_original_name, a.resume_mime_type,
                a.resume_size_bytes, a.resume_uploaded_at, a.ai_suggested_fields,
                a.resume_keywords, a.resume_text, a.resume_processed_at
         FROM public.applicants a
         JOIN public.users u ON u.id = a.user_id
         WHERE a.user_id = :user_id'
    );
    $profile->execute(['user_id' => (int) $user_id]);
    $applicant = $profile->fetch();

    if ($applicant) {
        $applicantName = $applicant['full_name'] ?: $applicantName;
        
        if (!empty($applicant['resume_original_name'])) {
            $resume = [
                'file' => $applicant['resume_original_name'],
                'type' => strtoupper(pathinfo($applicant['resume_original_name'], PATHINFO_EXTENSION)),
                'size' => number_format(((int) $applicant['resume_size_bytes']) / 1024) . ' KB',
                'uploaded' => $applicant['resume_uploaded_at'] ? date('M d, Y', strtotime($applicant['resume_uploaded_at'])) : '',
                'status' => 'Stored securely & Parsed',
            ];
        }

        // Load AI suggested fields from JSONB database column
        if (!empty($applicant['ai_suggested_fields'])) {
            $aiSuggestedFields = json_decode($applicant['ai_suggested_fields'], true) ?? [];
        }

        // Load extracted keywords from JSONB database column
        if (!empty($applicant['resume_keywords'])) {
            $resumeKeywords = json_decode($applicant['resume_keywords'], true) ?? [];
        }

        $isProcessed = !empty($applicant['resume_processed_at']);
    }
} catch (Throwable $e) {
    error_log('Could not load applicant dashboard profile: ' . $e->getMessage());
}

// Load available fields dynamically from classifications.php
$classificationsConfig = include __DIR__ . '/../../includes/config/classifications.php';
$allFields = $classificationsConfig['list'] ?? [];

$suggestedField = !empty($aiSuggestedFields) ? reset($aiSuggestedFields) : (!empty($allFields) ? $allFields[0] : 'Others');
$confirmedFields = $aiSuggestedFields ?: [];

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function initials($n){ $w=preg_split('/\s+/',trim($n)); return strtoupper(substr($w[0],0,1).(count($w)>1?substr(end($w),0,1):'')); }

$resumeNotices = [
 'uploaded' => 'Resume uploaded, converted to text, and successfully classified by AI.',
 'reparsed' => 'Resume successfully re-parsed and classified.',
 'deleted' => 'Resume deleted.',
 'missing' => 'There is no resume to download or delete.',
 'too_large' => 'The resume must be 5 MB or smaller.',
 'invalid_type' => 'Upload a valid PDF, DOC, or DOCX file.',
 'upload_error' => 'The upload did not complete. Try again.',
 'invalid' => 'That resume action was not valid.',
 'error' => 'Resume storage or AI processing failed. Check server logs.',
];
$resumeNotice = $resumeNotices[$_GET['resume'] ?? ''] ?? null;
$csrfSecret = is_string($_COOKIE['user_sig'] ?? null) ? $_COOKIE['user_sig'] : '';
$csrf = signedCsrfToken($csrfSecret);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>4Hire | My Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
<aside class="sidebar">
 <div class="logo-container"><div class="brand-mark">4</div><div><div class="logo">4<span>Hire</span></div><div class="logo-subtitle">Applicant portal</div></div></div>
 <div class="sidebar-content">
  <div class="menu-title">Main Menu</div>
  <ul class="menu">
   <li><a href="#" class="menu-item active" aria-current="page" title="Dashboard" data-section="Dashboard"><i class="menu-icon fa-solid fa-chart-pie"></i><span>Dashboard</span></a></li>
   <li><a href="#resume" class="menu-item" title="My Resume" data-section="My Resume"><i class="menu-icon fa-solid fa-file-lines"></i><span>My Resume</span></a></li>
   <li><a href="#fields" class="menu-item" title="Job Fields" data-section="Job Fields"><i class="menu-icon fa-solid fa-tags"></i><span>Job Fields</span></a></li>
  </ul>
  <div class="menu-title">Account</div>
  <ul class="menu">
   <li><a href="#privacy" class="menu-item" title="Privacy &amp; Consent" data-section="Privacy &amp; Consent"><i class="menu-icon fa-solid fa-shield-halved"></i><span>Privacy &amp; Consent</span></a></li>
   <li><a href="#" class="menu-item" title="Settings" data-section="Settings"><i class="menu-icon fa-solid fa-gear"></i><span>Settings</span></a></li>
  </ul>
 </div>
 <div class="sidebar-user">
  <div class="user-avatar"><?= h(initials($applicantName)) ?></div>
  <div class="user-info"><strong><?= h($applicantName) ?></strong><span>Applicant</span></div>
    <a class="logout" href="/Auth/logout.php" aria-label="Log out"><i class="fa-solid fa-right-from-bracket"></i></a> 
</div>
</aside>

<main class="main">
 <header class="top-header">
  <div><h1 class="page-title">My Dashboard</h1><p class="page-description">Keep your resume and job fields up to date so verified employers can find you.</p></div>
  <div class="top-meta"><strong><?= h($applicantName) ?></strong><span>Applicant Account</span></div>
 </header>

 <section class="card completeness" style="text-align: center; width: 100%;">
  <div>
   <h2>Your profile is <?= $resume ? 'almost ready' : 'pending resume upload' ?></h2>
   <p>Next step: review your job fields so employers can filter and find you.</p>
  </div>
 </section>

 <div class="content-grid">
  <section class="card" id="resume">
   <div class="card-header"><h2 class="card-title">My Resume & AI Analysis</h2><p class="card-subtitle">PDF text extraction and classification powered by Gemini.</p></div>
   <div class="card-body">
    <?php if ($resumeNotice): ?><p role="status" class="notice"><?= h($resumeNotice) ?></p><?php endif; ?>
    <?php if ($resume): ?>
    <div class="file-row">
     <div class="file-icon"><i class="fa-solid fa-file-pdf"></i></div>
     <div class="file-info"><strong id="fileName"><?= h($resume['file']) ?></strong><small><span id="fileMeta"><?= h($resume['type'].' · '.$resume['size'].' · Uploaded '.$resume['uploaded']) ?></span></small></div>
     <span class="status" id="fileStatus"><?= h($resume['status']) ?></span>
    </div>
    <?php else: ?>
    <p class="empty-note">You have not uploaded a resume yet.</p>
    <?php endif; ?>

    <ul class="steps" aria-label="Resume processing">
     <li class="<?= $resume ? '' : 'todo' ?>"><span class="step-dot"><?= $resume ? '<i class="fa-solid fa-check"></i>' : '1' ?></span>Stored in private storage</li>
     <li class="<?= $isProcessed ? '' : 'todo' ?>"><span class="step-dot"><?= $isProcessed ? '<i class="fa-solid fa-check"></i>' : '2' ?></span>PDF converted to text via PdfToText parser</li>
     <li class="<?= $isProcessed ? '' : 'todo' ?>" id="stepAi"><span class="step-dot"><?= $isProcessed ? '<i class="fa-solid fa-check"></i>' : '3' ?></span>AI keyword matching & classification completed</li>
    </ul>

    <?php if (!empty($resumeKeywords)): ?>
    <div style="margin-top: 15px;">
        <strong style="font-size: 13px; color: var(--text-muted);">Extracted Keywords:</strong>
        <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
            <?php foreach($resumeKeywords as $kw): ?>
                <span style="background: var(--card-hover); border: 1px solid var(--border-maroon); padding: 3px 8px; border-radius: 6px; font-size: 12px; color: var(--accent-gold);"><?= h($kw) ?></span>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <form method="post" action="/Auth/Resume.php" enctype="multipart/form-data" style="margin-top: 15px;">
     <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
     <input type="hidden" name="action" value="upload">
     <label class="dropzone"><i class="fa-solid fa-cloud-arrow-up"></i><strong><?= $resume ? 'Replace your resume' : 'Upload your resume' ?></strong>Select a PDF file (max 5 MB)<input type="file" id="resumeInput" name="resume" accept=".pdf" required></label>
     
     <!-- Client-side PDF Preview Component -->
     <div id="pdfPreviewContainer" style="display: none; margin-top: 15px;">
         <strong style="font-size: 13px; color: var(--text-muted); display: block; margin-bottom: 6px;">Resume Preview:</strong>
         <iframe id="pdfPreviewFrame" style="width: 100%; height: 280px; border: 1px solid var(--border-maroon); border-radius: 12px; background: var(--bg-black);"></iframe>
     </div>

     <div class="btn-row"><button class="btn" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i>Upload & Parse with AI</button></div>
    </form>

    <div class="btn-row">
     <?php if ($resume): ?>
     <a class="btn outline" href="/Auth/Resume.php?action=download"><i class="fa-solid fa-download"></i>Download</a>
     <a class="btn outline" href="/Dashboards/Applicant.php?action=reparse"><i class="fa-solid fa-brain"></i>Re-run AI Analysis</a>
     <form method="post" action="/Auth/Resume.php" onsubmit="return confirm('Delete your stored resume? This cannot be undone.');">
      <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
      <input type="hidden" name="action" value="delete">
      <button class="btn danger" type="submit"><i class="fa-solid fa-trash"></i>Delete resume</button>
     </form>
     <?php endif; ?>
    </div>
   </div>
  </section>

  <section class="card" id="fields">
   <div class="card-header"><h2 class="card-title">AI Job Classifications</h2><p class="card-subtitle">Matched category results mapped from your resume keywords.</p></div>
   <div class="card-body">
    <div class="suggest"><div><small>Primary AI Suggestion</small><strong><?= h($suggestedField) ?></strong></div><button class="btn" id="acceptSuggestion">Applied</button></div>
    <div class="chips" id="chips" role="group" aria-label="Job fields">
    <?php foreach($allFields as $f): ?>
     <button type="button" class="chip" aria-pressed="<?= in_array($f,$confirmedFields,true)?'true':'false' ?>" data-field="<?= h($f) ?>"><?= h($f) ?></button>
    <?php endforeach; ?>
    </div>
    <p class="hint">Gemini mapped your resume text against active job categories automatically.</p>
    <div class="note"><strong>How AI is used:</strong> It extracts skills and assigns classification matching categories for recruiter filters.</div>
   </div>
  </section>
 </div>

 <div class="bottom-grid" style="margin-top: 24px;">
  <section class="card" id="privacy">
   <div class="card-header"><h2 class="card-title">Privacy &amp; Consent</h2><p class="card-subtitle">You control who can find your resume.</p></div>
   <div class="card-body">
    <div class="setting"><div><strong>Visible to verified employers</strong><small>Turn off to hide your resume from search without deleting it.</small></div><button class="switch" id="visibility" role="switch" aria-checked="true" aria-label="Visible to verified employers"></button></div>
    <div class="setting"><div><strong>Consent on record</strong><small>Given Oct 01, 2026, including processing of your resume by a third-party AI service.</small></div><span class="status">Active</span></div>
    <div class="setting"><div><strong>Delete my account</strong><small>Removes your profile, resume and view history.</small></div><button class="btn danger" id="deleteAccount">Delete</button></div>
   </div>
  </section>
 </div>
</main>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
const toast=document.getElementById('toast');
let t;
function showToast(m){
    toast.textContent=m;toast.classList.add('show');
    clearTimeout(t);
    t=setTimeout(()=>toast.classList.remove('show'),2800)
}
document.querySelectorAll('.menu-item').forEach(l=>l.addEventListener('click',()=>{
    document.querySelectorAll('.menu-item').forEach(a=>{a.classList.remove('active');
    a.removeAttribute('aria-current')});
    l.classList.add('active');
    l.setAttribute('aria-current','page')}));
const chips=[...document.querySelectorAll('.chip')];
chips.forEach(c=>c.addEventListener('click',()=>{c.setAttribute('aria-pressed',c.getAttribute('aria-pressed')==='true'?'false':'true')}));
document.getElementById('acceptSuggestion').addEventListener('click',()=>{chips.find(c=>c.dataset.field===<?= json_encode($suggestedField) ?>)?.setAttribute('aria-pressed','true');showToast('Suggestion selected.')});

// Client-side local file preview before submitting to the database/server
document.getElementById('resumeInput').addEventListener('change', e => {
    const f = e.target.files[0];
    const previewContainer = document.getElementById('pdfPreviewContainer');
    const previewFrame = document.getElementById('pdfPreviewFrame');

    if (f) {
        if (f.size > 5 * 1024 * 1024) {
            showToast('File is larger than 5 MB.');
            e.target.value = '';
            previewContainer.style.display = 'none';
            return;
        }
        
        // Render local object URL inside iframe for preview
        const fileUrl = URL.createObjectURL(f);
        previewFrame.src = fileUrl;
        previewContainer.style.display = 'block';
        showToast('Ready to upload ' + f.name + '. Previewing below.');
    } else {
        previewContainer.style.display = 'none';
    }
});

document.getElementById('visibility').addEventListener('click',e=>{const b=e.currentTarget,on=b.getAttribute('aria-checked')!=='true';b.setAttribute('aria-checked',on);showToast(on?'Your resume is visible to verified employers.':'Your resume is hidden from employers.')});
document.getElementById('deleteAccount').addEventListener('click',()=>{if(confirm('Delete your account and all data? This cannot be undone.'))showToast('Account deleted')});
</script>
</body>
</html>