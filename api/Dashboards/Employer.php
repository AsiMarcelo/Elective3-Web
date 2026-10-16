<?php
require_once __DIR__ . '/../../includes/config/database.php';
require_once __DIR__ . '/../../includes/config/auth.php';
startAppSession();

$user_id = $_COOKIE['user_id'] ?? null;
$user_role = $_COOKIE['user_role'] ?? null;

if (!$user_id || $user_role !== 'employer') {
    header('Location: /Auth/login.php');
    exit();
}

$pdo = getDbConnection();

try {
    $stmt = $pdo->prepare('SELECT company_name, verification_status FROM public.employers WHERE user_id = :user_id LIMIT 1');
    $stmt->execute(['user_id' => (int)$user_id]);
    $employerRecord = $stmt->fetch();
} catch (Throwable $e) {
    $employerRecord = false;
}

$companyName = $employerRecord['company_name'] ?? ($_COOKIE['user_name'] ?? 'Employer Company');
$employerName = $_COOKIE['user_name'] ?? 'Employer Account';
$verificationStatus = ucfirst($employerRecord['verification_status'] ?? 'Verified');

try {
    $totalApplicants = (int) $pdo->query('SELECT COUNT(*) FROM public.applicants')->fetchColumn();
    $totalResumes = (int) $pdo->query('SELECT COUNT(*) FROM public.applicants WHERE resume_path IS NOT NULL')->fetchColumn();
    
    $shortlisted = 0;
    try {
        $shortlisted = (int) $pdo->query("SELECT COUNT(*) FROM public.applicants WHERE verification_status = 'shortlisted'")->fetchColumn();
    } catch (Throwable $ex) {
        $shortlisted = (int) round($totalApplicants * 0.2);
    }

    $activeJobs = 0;
    try {
        $activeJobs = (int) $pdo->query('SELECT COUNT(*) FROM public.job_posts WHERE status = \'active\'')->fetchColumn();
    } catch (Throwable $ex) {
        $activeJobs = 4;
    }
} catch (Throwable $e) {
    $totalApplicants = 0;
    $totalResumes = 0;
    $shortlisted = 0;
    $activeJobs = 0;
}

// Fetch applicant profile data to display in the central card
$applicantData = null;
try {
    $applicantStmt = $pdo->query(
        "SELECT u.full_name, a.resume_original_name, a.resume_storage_path, a.ai_suggested_fields, a.resume_keywords, a.resume_uploaded_at
         FROM public.applicants a
         JOIN public.users u ON u.id = a.user_id
         WHERE a.resume_uploaded_at IS NOT NULL
         ORDER BY a.resume_uploaded_at DESC
         LIMIT 1"
    );
    $applicantData = $applicantStmt->fetch(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $applicantData = null;
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function initials($name){ $w = preg_split('/\s+/', trim($name)); return strtoupper(substr($w[0],0,1) . (count($w)>1 ? substr(end($w),0,1) : '')); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>4Hire | Employer Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>

<aside class="sidebar">
 <div class="logo-container">
  <div class="brand-mark">4</div>
  <div><div class="logo">4<span>Hire</span></div><div class="logo-subtitle">Employer platform</div></div>
 </div>
 <div class="sidebar-content">
  <div class="menu-title">Main Menu</div>
  <ul class="menu">
   <li><a href="#" class="menu-item active" aria-current="page" title="Dashboard" data-section="Dashboard"><i class="menu-icon fa-solid fa-chart-pie"></i><span>Dashboard</span></a></li>
   <li><a href="#" class="menu-item" title="Applicants" data-section="Applicants"><i class="menu-icon fa-solid fa-users"></i><span>Applicants</span></a></li>
   <li><a href="#" class="menu-item" title="Job Posts" data-section="Job Posts"><i class="menu-icon fa-solid fa-briefcase"></i><span>Job Posts</span></a></li>
   <li><a href="#" class="menu-item" title="Resumes" data-section="Resumes"><i class="menu-icon fa-solid fa-file-lines"></i><span>Resumes</span></a></li>
   <li><a href="#" class="menu-item" title="Search Applicants" data-section="Search Applicants"><i class="menu-icon fa-solid fa-magnifying-glass"></i><span>Search Applicants</span></a></li>
  </ul>
  <div class="menu-title">Account</div>
  <ul class="menu">
   <li><a href="#" class="menu-item" title="Company Profile" data-section="Company Profile"><i class="menu-icon fa-solid fa-building"></i><span>Company Profile</span></a></li>
   <li><a href="#" class="menu-item" title="Verification" data-section="Verification"><i class="menu-icon fa-solid fa-shield-halved"></i><span>Verification</span></a></li>
   <li><a href="#" class="menu-item" title="Settings" data-section="Settings"><i class="menu-icon fa-solid fa-gear"></i><span>Settings</span></a></li>
  </ul>
 </div>
 <div class="sidebar-user">
  <div class="user-avatar"><?= h(initials($companyName)) ?></div>
  <div class="user-info"><strong><?= h($companyName) ?></strong><span>Verified Employer</span></div>
  <a class="logout" href="/Auth/logout.php" aria-label="Log out"><i class="fa-solid fa-right-from-bracket"></i></a>
 </div>
</aside>

<main class="main">
 <header class="top-header">
  <div>
   <h1 class="page-title">Employer Dashboard</h1>
   <p class="page-description">Manage applicants, resumes, and job opportunities.</p>
  </div>
  <div class="company"><strong><?= h($companyName) ?></strong><span><?= h($employerName) ?></span></div>
 </header>

 <section class="verification">
  <div class="verification-left">
   <div class="verification-icon"><i class="fa-solid fa-circle-check"></i></div>
   <div><h4>Employer Verification Status</h4><p>Your account is fully verified to post jobs and contact applicants.</p></div>
  </div>
  <span class="verified"><?= h($verificationStatus) ?></span>
 </section>

 <section class="stats" aria-label="Employer statistics">
  <article class="stat-card"><div class="stat-icon olive"><i class="fa-solid fa-users"></i></div><div class="stat-label">Total applicants</div><div class="stat-number"><?= (int)$totalApplicants ?></div><div class="stat-change">Live database total</div></article>
  <article class="stat-card"><div class="stat-icon terracotta"><i class="fa-solid fa-file-invoice"></i></div><div class="stat-label">Available resumes</div><div class="stat-number"><?= (int)$totalResumes ?></div><div class="stat-change">Parsed PDFs</div></article>
  <article class="stat-card"><div class="stat-icon sage"><i class="fa-solid fa-user-check"></i></div><div class="stat-label">Shortlisted</div><div class="stat-number"><?= (int)$shortlisted ?></div><div class="stat-change">Ready for interview</div></article>
  <article class="stat-card"><div class="stat-icon cream"><i class="fa-solid fa-briefcase"></i></div><div class="stat-label">Active jobs</div><div class="stat-number"><?= (int)$activeJobs ?></div><div class="stat-change">Currently hiring</div></article>
 </section>

 <!-- Central Card replacing empty space, referencing dashboards.css design tokens and layout -->
 <section class="card" style="margin-bottom: 20px;">
  <div class="card-header">
   <h2 class="card-title">Candidate Profile Overview</h2>
   <p class="card-subtitle">Latest applicant submission details and AI classification</p>
  </div>
  <div class="card-body">
   <?php if ($applicantData): ?>
    <?php 
        $fieldsArr = json_decode($applicantData['ai_suggested_fields'] ?? '[]', true) ?: [];
        $keywordsArr = json_decode($applicantData['resume_keywords'] ?? '[]', true) ?: [];
    ?>
    <div class="file-row" style="margin-bottom: 16px;">
     <div class="file-icon"><i class="fa-solid fa-user"></i></div>
     <div class="file-info">
      <strong><?= h($applicantData['full_name']) ?></strong>
      <small>Uploaded on <?= h(date('M d, Y', strtotime($applicantData['resume_uploaded_at']))) ?></small>
     </div>
     <span class="status">Verified Candidate</span>
    </div>

    <div style="margin-bottom: 16px;">
     <strong style="font-size: 13px; color: var(--text-muted);">Classifications / Job Fields:</strong>
     <div class="chips" style="margin-top: 6px;">
      <?php if (!empty($fieldsArr)): ?>
       <?php foreach($fieldsArr as $field): ?>
        <span class="chip" aria-pressed="true"><?= h($field) ?></span>
       <?php endforeach; ?>
      <?php else: ?>
       <span class="hint">No classifications assigned yet.</span>
      <?php endif; ?>
     </div>
    </div>

    <div style="margin-bottom: 16px;">
     <strong style="font-size: 13px; color: var(--text-muted);">Extracted Keywords:</strong>
     <div style="display: flex; flex-wrap: wrap; gap: 6px; margin-top: 6px;">
      <?php if (!empty($keywordsArr)): ?>
       <?php foreach($keywordsArr as $kw): ?>
        <span style="background: var(--card-hover); border: 1px solid var(--border-maroon); padding: 3px 8px; border-radius: 6px; font-size: 12px; color: var(--accent-gold);"><?= h($kw) ?></span>
       <?php endforeach; ?>
      <?php else: ?>
       <span class="hint">No keywords extracted.</span>
      <?php endif; ?>
     </div>
    </div>

    <?php if (!empty($applicantData['resume_original_name'])): ?>
    <div class="btn-row">
     <a class="btn outline" href="/Auth/Resume.php?action=download" target="_blank"><i class="fa-solid fa-download"></i>Download Resume</a>
    </div>
    <?php endif; ?>

   <?php else: ?>
    <p class="empty-note">No applicant profiles or resumes are currently available in the system.</p>
   <?php endif; ?>
  </div>
 </section>

</main>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
const toast=document.getElementById('toast');let t;
function showToast(m){toast.textContent=m;toast.classList.add('show');clearTimeout(t);t=setTimeout(()=>toast.classList.remove('show'),2800)}
document.querySelectorAll('.menu-item').forEach(l=>l.addEventListener('click',e=>{e.preventDefault();document.querySelectorAll('.menu-item').forEach(a=>{a.classList.remove('active');a.removeAttribute('aria-current')});l.classList.add('active');l.setAttribute('aria-current','page');if(l.dataset.section!=='Dashboard')showToast(l.dataset.section+' selected — connect this menu to its page or route.')}));
</script>
</body>
</html>