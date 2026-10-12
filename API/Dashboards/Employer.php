<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'employer') {
    header('Location: /Auth/login.php');
    exit();
}

$companyName = "ABC Company";
$employerName = "Employer Account";
$verificationStatus = "Verified";

$totalApplicants = 128; $totalResumes = 96; $shortlisted = 24; $activeJobs = 8;

$fieldData = ["Software Development"=>42,"Web Development"=>28,"Networking"=>18,"Other / Unsure"=>12];
$fieldTones = ['olive','terracotta','sage','cream'];

$months = ['May'=>40,'Jun'=>55,'Jul'=>70,'Aug'=>60,'Sep'=>85,'Oct'=>100];

$applicants = [
 ["name"=>"Juan Dela Cruz","field"=>"Software Development","position"=>"Web Developer","date"=>"Oct 04, 2026"],
 ["name"=>"Maria Santos","field"=>"Web Development","position"=>"Frontend Developer","date"=>"Oct 03, 2026"],
 ["name"=>"Alex Reyes","field"=>"Networking","position"=>"Network Engineer","date"=>"Oct 02, 2026"],
 ["name"=>"Sofia Garcia","field"=>"Software Development","position"=>"Backend Developer","date"=>"Oct 01, 2026"],
];

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
<link rel="stylesheet" href="dashboard.css">
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
  <article class="stat-card"><div class="stat-icon olive"><i class="fa-solid fa-users"></i></div><div class="stat-label">Total applicants</div><div class="stat-number"><?= (int)$totalApplicants ?></div><div class="stat-change">+12% from last month</div></article>
  <article class="stat-card"><div class="stat-icon terracotta"><i class="fa-solid fa-file-invoice"></i></div><div class="stat-label">Available resumes</div><div class="stat-number"><?= (int)$totalResumes ?></div><div class="stat-change">+8% new submissions</div></article>
  <article class="stat-card"><div class="stat-icon sage"><i class="fa-solid fa-user-check"></i></div><div class="stat-label">Shortlisted</div><div class="stat-number"><?= (int)$shortlisted ?></div><div class="stat-change">Ready for interview</div></article>
  <article class="stat-card"><div class="stat-icon cream"><i class="fa-solid fa-briefcase"></i></div><div class="stat-label">Active jobs</div><div class="stat-number"><?= (int)$activeJobs ?></div><div class="stat-change">Currently hiring</div></article>
 </section>

 <div class="content-grid">
  <section class="card chart-card">
   <div class="card-header"><h2 class="card-title">Applicant Activity</h2><p class="card-subtitle">Monthly resume submissions overview</p></div>
   <div class="chart" role="img" aria-label="Bar chart of monthly resume submissions, rising from May to October">
   <?php foreach($months as $m=>$pct): ?>
    <div class="chart-column"><div class="bar-wrapper"><div class="bar" style="--value:<?= (int)$pct ?>%"></div></div><span class="month"><?= h($m) ?></span></div>
   <?php endforeach; ?>
   </div>
  </section>

  <section class="card">
   <div class="card-header"><h2 class="card-title">Top Fields</h2><p class="card-subtitle">Distribution by expertise</p></div>
   <div class="fields">
   <?php $i=0; foreach($fieldData as $field=>$pct): ?>
    <div class="field"><div class="field-info"><span class="field-name"><?= h($field) ?></span><span class="field-percent"><?= (int)$pct ?>%</span></div><div class="progress"><div class="progress-fill <?= h($fieldTones[$i++ % 4]) ?>" style="--value:<?= (int)$pct ?>%"></div></div></div>
   <?php endforeach; ?>
   </div>
   <div class="ai-note"><strong>System note:</strong> Most applicants in your pool specialize in Software and Web Development. Fields shown are the ones applicants confirmed themselves.</div>
  </section>
 </div>

 <section class="card recent-card">
  <div class="recent-header"><div><h2 class="card-title">Recent Applicants</h2><p class="card-subtitle">Latest resume submissions</p></div><a href="#" class="view-all">View all</a></div>
  <div class="table-header"><span>Applicant</span><span>Field</span><span>Position</span><span>Date</span><span>Action</span></div>
  <?php foreach($applicants as $a): ?>
   <div class="applicant-row">
    <div class="applicant-name"><div class="mini-avatar"><?= h(initials($a['name'])) ?></div><span><?= h($a['name']) ?></span></div>
    <div class="applicant-field"><?= h($a['field']) ?></div>
    <div class="position"><?= h($a['position']) ?></div>
    <div class="date"><?= h($a['date']) ?></div>
    <div><button class="action-btn" data-name="<?= h($a['name']) ?>">View profile</button></div>
   </div>
  <?php endforeach; ?>
 </section>

 <div class="quick-actions">
  <button class="quick-action" data-action="Post new job"><span class="quick-icon terracotta"><i class="fa-solid fa-plus"></i></span><span><strong>Post New Job</strong><small>Create a new job listing</small></span></button>
  <button class="quick-action" data-action="Search resumes"><span class="quick-icon olive"><i class="fa-solid fa-magnifying-glass"></i></span><span><strong>Search Resumes</strong><small>Filter candidate profiles</small></span></button>
  <button class="quick-action" data-action="Manage jobs"><span class="quick-icon sage"><i class="fa-solid fa-sliders"></i></span><span><strong>Manage Jobs</strong><small>Edit existing listings</small></span></button>
 </div>
</main>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
const toast=document.getElementById('toast');let t;
function showToast(m){toast.textContent=m;toast.classList.add('show');clearTimeout(t);t=setTimeout(()=>toast.classList.remove('show'),2800)}
document.querySelectorAll('.menu-item').forEach(l=>l.addEventListener('click',e=>{e.preventDefault();document.querySelectorAll('.menu-item').forEach(a=>{a.classList.remove('active');a.removeAttribute('aria-current')});l.classList.add('active');l.setAttribute('aria-current','page');if(l.dataset.section!=='Dashboard')showToast(l.dataset.section+' selected — connect this menu to its page or route.')}));
document.querySelectorAll('.action-btn').forEach(b=>b.addEventListener('click',()=>showToast('Viewing '+b.dataset.name+' — connect to the applicant profile page.')));
document.querySelectorAll('.quick-action').forEach(b=>b.addEventListener('click',()=>showToast(b.dataset.action+' — connect this action to your backend.')));
document.querySelector('.view-all').addEventListener('click',e=>{e.preventDefault();showToast('Connect to the full applicants list.')});
</script>
</body>
</html>