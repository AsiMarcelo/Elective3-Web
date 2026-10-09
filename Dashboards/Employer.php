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
<style>
:root{
  --soft-olive:#bcc590;      /* Active badges, highlights, buttons */
  --light-cream:#f6f4d2b9;   /* Main background */
  --sage:#CBDFBD;            /* Sidebar/Card borders & soft bg accents */
  --terracotta:#F19C79;      /* Primary CTA, notifications, logo accent */
  --dark-text:#2D3A2F;
  --muted-text:#5A695C;
  --white:#FFFFFF;
  --border-color:#DDE7D3;
  --shadow:0 5px 20px rgba(45,58,47,.05);
  --sidebar-width:252px;
  --olive-soft:color-mix(in srgb,var(--soft-olive) 35%,var(--white));
  --terra-soft:color-mix(in srgb,var(--terracotta) 25%,var(--white));
  --sage-soft:color-mix(in srgb,var(--sage) 45%,var(--white));
}
*{margin:0;padding:0;box-sizing:border-box}
html{background:var(--white);scroll-behavior:smooth}
body{font-family:Inter,"Segoe UI",Arial,sans-serif;font-size:14px;background:var(--light-cream);color:var(--dark-text);min-height:100vh}
button,input,select{font:inherit}button{cursor:pointer}a{color:inherit;text-decoration:none}
:focus-visible{outline:2px solid var(--terracotta);outline-offset:2px}
.olive{background:var(--soft-olive)}.terracotta{background:var(--terracotta)}.sage{background:var(--sage)}.cream{background:var(--light-cream);box-shadow:inset 0 0 0 1px var(--soft-olive)}

/* Sidebar (same look as admin) */
.sidebar{position:fixed;inset:0 auto 0 0;width:var(--sidebar-width);background:var(--white);border-right:1px solid var(--sage);display:flex;flex-direction:column;z-index:100}
.logo-container{padding:24px 26px 20px;display:flex;align-items:center;gap:10px}
.brand-mark{height:39px;width:39px;display:grid;place-items:center;background:var(--terracotta);color:var(--dark-text);border-radius:12px;font-weight:900;font-size:19px;flex:0 0 39px}
.logo{font-size:23px;font-weight:900;letter-spacing:-.8px}.logo span{color:var(--terracotta)}
.logo-subtitle{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted-text);margin-top:2px}
.sidebar-content{padding:6px 16px;flex:1;overflow-y:auto}
.menu-title{font-size:10px;font-weight:800;color:var(--muted-text);letter-spacing:1.5px;text-transform:uppercase;padding:12px 12px 8px}
.menu{list-style:none;display:grid;gap:5px;margin-bottom:10px}
.menu-item{display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--muted-text);font-weight:650;font-size:14px;transition:.2s}
.menu-item:hover{background:var(--sage-soft);color:var(--dark-text)}
.menu-item.active{background:var(--soft-olive);color:var(--dark-text);box-shadow:inset 3px 0 var(--terracotta)}
.menu-icon{width:20px;text-align:center;font-size:17px;flex-shrink:0}
.sidebar-user{padding:14px 16px;margin:0 16px 14px;border-top:1px solid var(--border-color);display:flex;align-items:center;gap:10px}
.user-avatar{width:38px;height:38px;flex:0 0 38px;border-radius:12px;background:var(--sage);display:grid;place-items:center;font-size:14px;font-weight:800}
.user-info{min-width:0}.user-info strong{display:block;font-size:13px}.user-info span{display:block;color:var(--muted-text);font-size:11px;margin-top:3px}
.logout{margin-left:auto;background:none;border:0;color:var(--muted-text);font-size:15px;padding:6px;border-radius:8px}
.logout:hover{background:var(--sage-soft);color:var(--dark-text)}

/* Main */
.main{margin-left:var(--sidebar-width);padding:28px 32px 40px}
.card,.top-header,.verification,.stat-card,.quick-action{background:var(--white);border:1px solid var(--sage);box-shadow:var(--shadow)}
.top-header{border-radius:15px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}
.page-title{font-size:29px;font-weight:850;letter-spacing:-1px;line-height:1.15}
.page-description{margin-top:8px;font-size:13px;color:var(--muted-text)}
.company{text-align:right}.company strong{display:block;font-size:12px}.company span{display:block;margin-top:3px;color:var(--muted-text);font-size:11px}
.verification{border-radius:15px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;gap:14px;margin-bottom:20px}
.verification-left{display:flex;align-items:center;gap:12px}
.verification-icon{width:40px;height:40px;border-radius:12px;background:var(--soft-olive);display:grid;place-items:center;font-size:16px}
.verification h4{font-size:16px;font-weight:800;margin-bottom:5px}.verification p{font-size:12px;color:var(--muted-text)}
.verified{display:inline-flex;align-items:center;gap:6px;background:var(--sage);padding:6px 10px;border-radius:30px;font-size:10px;font-weight:800}
.verified:before{content:"";width:6px;height:6px;border-radius:50%;background:var(--dark-text)}

/* Stats */
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:20px}
.stat-card{border-radius:15px;padding:19px;min-width:0}
.stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:20px;font-weight:800;color:var(--dark-text)}
.stat-label{margin-top:16px;color:var(--muted-text);font-size:12px;font-weight:650}
.stat-number{margin-top:5px;font-size:29px;font-weight:850;letter-spacing:-1px}
.stat-change{margin-top:8px;font-size:11px;color:var(--muted-text);font-weight:700}

/* Cards */
.content-grid{display:grid;grid-template-columns:minmax(0,1.6fr) minmax(300px,1fr);gap:20px;margin-bottom:20px}
.card{border-radius:15px;min-width:0}
.card-header{padding:21px 21px 0}
.card-title{font-size:16px;font-weight:850;letter-spacing:-.3px}
.card-subtitle{font-size:12px;color:var(--muted-text);margin-top:6px}
.chart{height:240px;margin:18px 21px 0;display:flex}
.chart-column{flex:1;min-width:0;display:flex;flex-direction:column}
.bar-wrapper{flex:1;min-height:0;display:flex;align-items:flex-end;justify-content:center;border-bottom:1px solid var(--border-color);background:linear-gradient(to bottom,var(--border-color) 1px,transparent 1px) 0 0/100% 25%}
.bar{width:55%;max-width:34px;min-height:15px;background:linear-gradient(180deg,var(--sage),var(--soft-olive));border-radius:6px 6px 2px 2px;transition:.25s}
.bar:hover{background:var(--terracotta);transform:translateY(-3px)}
.month{margin-top:8px;text-align:center;font-size:10px;color:var(--muted-text)}
.chart-card{padding-bottom:21px}
.fields{padding:20px 21px 4px;display:grid;gap:19px}
.field-info{display:flex;justify-content:space-between;margin-bottom:8px}
.field-name{font-size:12px;font-weight:700}.field-percent{font-size:12px;font-weight:800;color:var(--muted-text)}
.progress{height:8px;background:var(--sage-soft);border-radius:20px;overflow:hidden}
.progress-fill{height:100%;border-radius:20px}
.ai-note{margin:16px 21px 21px;padding:12px;background:var(--light-cream);border:1px solid var(--border-color);border-radius:10px;font-size:11px;line-height:1.55;color:var(--muted-text)}
.ai-note strong{color:var(--dark-text)}

/* Recent applicants */
.recent-card{overflow:hidden}
.recent-header{padding:21px;display:flex;justify-content:space-between;align-items:center;gap:12px}
.view-all{font-size:11px;font-weight:800;padding:8px 12px;border-radius:9px;background:var(--soft-olive);transition:.2s}
.view-all:hover{background:var(--terracotta)}
.table-header,.applicant-row{display:grid;grid-template-columns:1.4fr 1.1fr 1.1fr .8fr 110px;gap:12px;align-items:center;padding:14px 21px}
.table-header{background:var(--sage-soft);border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);padding-top:12px;padding-bottom:12px}
.table-header span{font-size:10px;font-weight:800;text-transform:uppercase;color:var(--muted-text);letter-spacing:.8px}
.applicant-row{border-bottom:1px solid var(--border-color);transition:.2s}
.applicant-row:hover{background:var(--light-cream)}.applicant-row:last-child{border-bottom:0}
.applicant-name{display:flex;align-items:center;gap:10px;font-size:12px;font-weight:750;min-width:0}
.mini-avatar{width:35px;height:35px;flex:0 0 35px;border-radius:10px;background:var(--sage);display:grid;place-items:center;font-size:11px;font-weight:800}
.applicant-field,.position,.date{font-size:12px;color:var(--muted-text)}
.action-btn{border:1px solid var(--terracotta);background:var(--terracotta);color:var(--dark-text);padding:8px 12px;border-radius:8px;font-size:11px;font-weight:750;transition:.2s}
.action-btn:hover{background:color-mix(in srgb,var(--terracotta) 85%,var(--dark-text))}

/* Quick actions */
.quick-actions{margin-top:20px;display:grid;grid-template-columns:repeat(3,1fr);gap:16px}
.quick-action{border-radius:12px;padding:15px;display:flex;align-items:center;gap:12px;text-align:left;color:var(--dark-text);transition:.18s}
.quick-action:hover{transform:translateY(-2px);background:var(--light-cream);border-color:var(--soft-olive)}
.quick-icon{width:38px;height:38px;flex:0 0 38px;border-radius:10px;display:grid;place-items:center;font-size:17px;color:var(--dark-text)}
.quick-action strong{display:block;font-size:11px}.quick-action small{display:block;font-size:10px;color:var(--muted-text);margin-top:3px}
.toast{position:fixed;right:22px;bottom:22px;background:var(--dark-text);color:var(--white);padding:13px 17px;border-radius:10px;border-left:4px solid var(--terracotta);box-shadow:var(--shadow);z-index:200;opacity:0;transform:translateY(10px);transition:.2s;pointer-events:none}
.toast.show{opacity:1;transform:none}

/* Responsive */
@media(max-width:1100px){:root{--sidebar-width:220px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.content-grid{grid-template-columns:1fr}.table-header,.applicant-row{grid-template-columns:1.4fr 1fr 1fr 110px}.table-header span:nth-child(4),.applicant-row .date{display:none}}
@media(max-width:750px){:root{--sidebar-width:68px}.logo-container{padding:20px 0;justify-content:center}.logo-container>div:last-child,.menu-title,.menu-item span,.user-info,.logout{display:none}.sidebar-content{padding:6px 8px}.menu-item{justify-content:center;padding:12px 8px}.sidebar-user{justify-content:center;margin:0 8px 14px;padding:14px 0}.main{padding:18px 15px 28px}.top-header{padding:16px}.page-title{font-size:20px}.company{display:none}.verification{align-items:flex-start;flex-direction:column}.quick-actions{grid-template-columns:1fr}.table-header{display:none}.applicant-row{grid-template-columns:1fr auto;gap:6px 10px;padding:14px 16px}.applicant-field,.position{display:none}.bar{width:65%}}
@media(max-width:480px){.stats{grid-template-columns:1fr}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
</style>
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
  <button class="logout" id="logout" aria-label="Log out"><i class="fa-solid fa-right-from-bracket"></i></button>
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
    <div class="chart-column"><div class="bar-wrapper"><div class="bar" style="height:<?= (int)$pct ?>%"></div></div><span class="month"><?= h($m) ?></span></div>
   <?php endforeach; ?>
   </div>
  </section>

  <section class="card">
   <div class="card-header"><h2 class="card-title">Top Fields</h2><p class="card-subtitle">Distribution by expertise</p></div>
   <div class="fields">
   <?php $i=0; foreach($fieldData as $field=>$pct): ?>
    <div class="field"><div class="field-info"><span class="field-name"><?= h($field) ?></span><span class="field-percent"><?= (int)$pct ?>%</span></div><div class="progress"><div class="progress-fill <?= h($fieldTones[$i++ % 4]) ?>" style="width:<?= (int)$pct ?>%"></div></div></div>
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
document.getElementById('logout').addEventListener('click',()=>showToast('Demo logout — connect this to your logout route.'));
</script>
</body>
</html>
