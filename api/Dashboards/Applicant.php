<?php
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/config/auth.php';
startAppSession();

$user_id = $_COOKIE['user_id'] ?? null;
$user_role = $_COOKIE['user_role'] ?? null;

if (!$user_id || $user_role !== 'applicant') {
    header('Location: /Auth/login.php');
    exit();
}

$applicantName = $_COOKIE['user_name'] ?? 'Applicant';
$resume = null;
try {
    $pdo = getDbConnection();
    $profile = $pdo->prepare(
        'SELECT u.full_name, a.resume_original_name, a.resume_mime_type,
                a.resume_size_bytes, a.resume_uploaded_at
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
                'status' => 'Stored securely',
            ];
        }
    }
} catch (Throwable $e) {
    error_log('Could not load applicant dashboard profile: ' . $e->getMessage());
}
$completeness = 80;
$allFields = ["Software Development","Web Development","Networking","Other / Unsure"];
$suggestedField = "Web Development";           // stored separately from the confirmed fields
$confirmedFields = ["Software Development","Web Development"];
$views = [
 ["company"=>"Cloudline Systems Inc.","date"=>"Oct 08, 2026 · 2:14 PM","color"=>"sage"],
 ["company"=>"Northstar Digital Solutions","date"=>"Oct 07, 2026 · 9:40 AM","color"=>"olive"],
 ["company"=>"BrightPath Technologies","date"=>"Oct 05, 2026 · 4:02 PM","color"=>"terracotta"],
];
function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function initials($n){ $w=preg_split('/\s+/',trim($n)); return strtoupper(substr($w[0],0,1).(count($w)>1?substr(end($w),0,1):'')); }
$resumeNotices = [
 'uploaded' => 'Resume uploaded and linked to your applicant profile.',
 'deleted' => 'Resume deleted.',
 'missing' => 'There is no resume to download or delete.',
 'too_large' => 'The resume must be 5 MB or smaller.',
 'invalid_type' => 'Upload a valid PDF, DOC, or DOCX file.',
 'upload_error' => 'The upload did not complete. Try again.',
 'invalid' => 'That resume action was not valid.',
 'error' => 'Resume storage is not ready or the request failed. Check server configuration and logs.',
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
   <li><a href="#views" class="menu-item" title="Who Viewed Me" data-section="Who Viewed Me"><i class="menu-icon fa-solid fa-eye"></i><span>Who Viewed Me</span></a></li>
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

 <section class="card completeness">
  <div><h2>Your profile is almost ready</h2><p>Next step: review your job fields so employers can filter and find you.</p></div>
  <div class="meter"><div class="meter-top"><span>Profile completeness</span><span><?= (int)$completeness ?>%</span></div><div class="progress"><span style="--value:<?= (int)$completeness ?>%"></span></div></div>
 </section>

 <section class="stats" aria-label="Your summary">
  <article class="stat-card"><div class="stat-icon olive"><i class="fa-solid fa-file-circle-check"></i></div><div class="stat-label">Resume</div><div class="stat-number stat-text"><?= $resume ? 'Uploaded' : 'Not uploaded' ?></div><div class="stat-change"><?= $resume ? 'Last updated ' . h($resume['uploaded']) : 'Upload a PDF, DOC, or DOCX' ?></div></article>
  <article class="stat-card"><div class="stat-icon terracotta"><i class="fa-solid fa-eye"></i></div><div class="stat-label">Employer views</div><div class="stat-number"><?= count($views) ?></div><div class="stat-change">Verified employers only</div></article>
  <article class="stat-card"><div class="stat-icon sage"><i class="fa-solid fa-tags"></i></div><div class="stat-label">Confirmed fields</div><div class="stat-number" id="confirmedCount"><?= count($confirmedFields) ?></div><div class="stat-change">Shown to employers</div></article>
  <article class="stat-card"><div class="stat-icon cream"><i class="fa-solid fa-user-check"></i></div><div class="stat-label">Profile</div><div class="stat-number"><?= (int)$completeness ?>%</div><div class="stat-change">Complete</div></article>
 </section>

 <div class="content-grid">
  <section class="card" id="resume">
   <div class="card-header"><h2 class="card-title">My Resume</h2><p class="card-subtitle">One resume at a time. Stored privately; only verified employers can open it.</p></div>
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
     <li class="<?= $resume ? '' : 'todo' ?>"><span class="step-dot"><?= $resume ? '<i class="fa-solid fa-check"></i>' : '1' ?></span>Stored in private resume storage</li>
     <li class="todo"><span class="step-dot">2</span>Text extraction is not connected yet</li>
     <li class="todo" id="stepAi"><span class="step-dot">3</span>AI field suggestion is not connected yet</li>
    </ul>
    <form method="post" action="/Auth/Resume.php" enctype="multipart/form-data">
     <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
     <input type="hidden" name="action" value="upload">
     <label class="dropzone"><i class="fa-solid fa-cloud-arrow-up"></i><strong><?= $resume ? 'Replace your resume' : 'Upload your resume' ?></strong>Select a PDF, DOC, or DOCX file (max 5 MB)<input type="file" id="resumeInput" name="resume" accept=".pdf,.doc,.docx" required></label>
     <div class="btn-row"><button class="btn" type="submit"><i class="fa-solid fa-cloud-arrow-up"></i>Save resume</button></div>
    </form>
    <div class="btn-row">
     <?php if ($resume): ?>
     <a class="btn outline" href="/Auth/Resume.php?action=download"><i class="fa-solid fa-download"></i>Download</a>
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
   <div class="card-header"><h2 class="card-title">My Job Fields</h2><p class="card-subtitle">Choose every field that fits you. You can pick more than one.</p></div>
   <div class="card-body">
    <div class="suggest"><div><small>Suggested from your resume</small><strong><?= h($suggestedField) ?></strong></div><button class="btn" id="acceptSuggestion">Use suggestion</button></div>
    <div class="chips" id="chips" role="group" aria-label="Job fields">
    <?php foreach($allFields as $f): ?>
     <button type="button" class="chip" aria-pressed="<?= in_array($f,$confirmedFields,true)?'true':'false' ?>" data-field="<?= h($f) ?>"><?= h($f) ?></button>
    <?php endforeach; ?>
    </div>
    <p class="hint">The suggestion is only a starting point. Employers see and filter by the fields you confirm here.</p>
    <div class="btn-row"><button class="btn" id="saveFields"><i class="fa-solid fa-check"></i>Confirm fields</button></div>
    <div class="note"><strong>How AI is used:</strong> it only suggests a job field. It does not score, compare, or rank you against other applicants.</div>
   </div>
  </section>
 </div>

 <div class="bottom-grid">
  <section class="card" id="views">
   <div class="card-header"><h2 class="card-title">Who Viewed My Resume</h2><p class="card-subtitle">Every view by a verified employer is recorded here.</p></div>
   <div class="card-body">
   <?php foreach($views as $v): ?>
    <div class="view-item"><div class="view-logo <?= h($v['color']) ?>"><?= h(initials($v['company'])) ?></div><div class="view-text"><strong><?= h($v['company']) ?></strong><small>Viewed your resume</small></div><span class="view-time"><?= h($v['date']) ?></span></div>
   <?php endforeach; ?>
   </div>
  </section>

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
document.querySelector('.menu-item[href="#"]:not(.active)')?.addEventListener('click',e=>{e.preventDefault();
showToast('Connect this menu to its page or route.')});
const chips=[...document.querySelectorAll('.chip')];
function updateCount(){
    document.getElementById('confirmedCount').textContent=chips.filter(c=>c.getAttribute('aria-pressed')==='true').length
}
chips.forEach(c=>c.addEventListener('click',()=>{c.setAttribute('aria-pressed',c.getAttribute('aria-pressed')==='true'?'false':'true')}));
document.getElementById('acceptSuggestion').addEventListener('click',()=>{chips.find(c=>c.dataset.field===<?= json_encode($suggestedField) ?>)?.setAttribute('aria-pressed','true');showToast('Suggestion selected. Confirm your fields to save.')});
document.getElementById('saveFields').addEventListener('click',()=>{const n=chips.filter(c=>c.getAttribute('aria-pressed')==='true').length;if(!n){showToast('Select at least one field, or choose Other / Unsure.');return}updateCount();showToast('Fields confirmed (demo only).')});
document.getElementById('resumeInput').addEventListener('change',e=>{const f=e.target.files[0];if(f&&f.size>5*1024*1024){showToast('File is larger than 5 MB.');e.target.value='';return}if(f)showToast('Ready to upload '+f.name+'. Press Save resume to store it.');});
document.getElementById('visibility').addEventListener('click',e=>{const b=e.currentTarget,on=b.getAttribute('aria-checked')!=='true';b.setAttribute('aria-checked',on);showToast(on?'Your resume is visible to verified employers.':'Your resume is hidden from employers.')});
document.querySelectorAll('[data-action]').forEach(b=>b.addEventListener('click',()=>showToast(b.dataset.action+' — connect this to your backend.')));
document.getElementById('deleteAccount').addEventListener('click',()=>{if(confirm('Delete your account and all data? This cannot be undone.'))showToast('Account deleted')});
</script>
</body>
</html>
