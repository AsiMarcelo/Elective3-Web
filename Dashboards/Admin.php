<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /Auth/login.php');
    exit();
}

$stats = [
 ['label'=>'Total Applicants','value'=>'1,284','change'=>'+12.8%','icon'=>'♙','tone'=>'olive'],
 ['label'=>'Employer Accounts','value'=>'86','change'=>'+6 this week','icon'=>'▣','tone'=>'terracotta'],
 ['label'=>'Pending Verification','value'=>'12','change'=>'Needs review','icon'=>'◷','tone'=>'cream'],
 ['label'=>'Active Job Posts','value'=>'143','change'=>'Across all employers','icon'=>'▤','tone'=>'sage'],
];
$employers = [
 ['company'=>'Northstar Digital Solutions','email'=>'hr@northstardigital.ph','date'=>'Oct 08, 2026','status'=>'Pending','initials'=>'ND','color'=>'olive'],
 ['company'=>'BrightPath Technologies','email'=>'careers@brightpath.ph','date'=>'Oct 07, 2026','status'=>'Pending','initials'=>'BT','color'=>'terracotta'],
 ['company'=>'Cloudline Systems Inc.','email'=>'people@cloudline.ph','date'=>'Oct 06, 2026','status'=>'Verified','initials'=>'CS','color'=>'cream'],
 ['company'=>'Vertex Network Services','email'=>'jobs@vertexnet.ph','date'=>'Oct 05, 2026','status'=>'Rejected','initials'=>'VN','color'=>'sage'],
];
$activities = [
 ['icon'=>'✓','title'=>'Employer account verified','detail'=>'Cloudline Systems Inc.','time'=>'12 min ago','tone'=>'sage'],
 ['icon'=>'↑','title'=>'New resume uploaded','detail'=>'Applicant ID #A-1048','time'=>'38 min ago','tone'=>'olive'],
 ['icon'=>'▤','title'=>'Job post reported','detail'=>'Junior Web Developer · Post #J-219','time'=>'1 hour ago','tone'=>'terracotta'],
 ['icon'=>'◈','title'=>'AI field suggestion confirmed','detail'=>'Applicant ID #A-1032','time'=>'2 hours ago','tone'=>'cream'],
];
$fields = [['Software Development',42,'olive'],['Web Development',28,'terracotta'],['Networking',18,'sage'],['Other / Unsure',12,'cream']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>4Hire | Admin Dashboard</title>
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
  /* tints derived only from the palette */
  --olive-soft:color-mix(in srgb,var(--soft-olive) 35%,var(--white));
  --terra-soft:color-mix(in srgb,var(--terracotta) 25%,var(--white));
  --sage-soft:color-mix(in srgb,var(--sage) 45%,var(--white));
}
*{box-sizing:border-box}
html{background:var(--white)}
body{margin:0;background:var(--light-cream);color:var(--dark-text);font-family:Inter,"Segoe UI",Arial,sans-serif;font-size:14px}
button,input,select{font:inherit}button{cursor:pointer}a{color:inherit;text-decoration:none}
:focus-visible{outline:2px solid var(--terracotta);outline-offset:2px}

/* Sidebar */
.sidebar{position:fixed;inset:0 auto 0 0;width:var(--sidebar-width);background:var(--white);border-right:1px solid var(--sage);padding:24px 16px 16px;display:flex;flex-direction:column;overflow-y:auto;z-index:20;transition:transform .25s}
.brand{display:flex;align-items:center;gap:10px;padding:0 10px 28px}
.brand-mark{height:39px;width:39px;display:grid;place-items:center;background:var(--terracotta);color:var(--dark-text);border-radius:12px;font-weight:900;font-size:19px}
.brand-name{font-weight:900;letter-spacing:-.8px;font-size:23px}.brand-name span{color:var(--terracotta)}
.brand-sub{font-size:10px;letter-spacing:1.5px;text-transform:uppercase;color:var(--muted-text);margin-top:2px}
.nav-label{font-size:10px;letter-spacing:1.5px;font-weight:800;color:var(--muted-text);padding:12px 12px 8px}
.nav{display:grid;gap:5px}
.nav a{display:flex;align-items:center;gap:12px;padding:12px;border-radius:10px;color:var(--muted-text);font-weight:650;transition:.2s}
.nav a:hover{background:var(--sage-soft);color:var(--dark-text)}
.nav a.active{background:var(--soft-olive);color:var(--dark-text);box-shadow:inset 3px 0 var(--terracotta)}
.nav-icon{width:20px;text-align:center;font-size:17px}
.nav-count{margin-left:auto;background:var(--terracotta);color:var(--dark-text);min-width:22px;padding:3px 6px;border-radius:20px;text-align:center;font-size:10px;font-weight:800}
.sidebar-bottom{margin-top:auto}
.admin-mini{display:flex;align-items:center;gap:10px;padding:14px 9px;border-top:1px solid var(--border-color);margin-top:18px}
.avatar{height:38px;width:38px;flex:0 0 38px;border-radius:12px;background:var(--sage);display:grid;place-items:center;font-weight:800}
.admin-name{font-weight:750;font-size:13px}.admin-role{font-size:11px;color:var(--muted-text);margin-top:3px}

/* Layout + topbar */
.main{margin-left:var(--sidebar-width);padding:28px 32px 40px}
.topbar{display:flex;align-items:center;justify-content:space-between;gap:18px;margin-bottom:27px}
.eyebrow{font-size:11px;color:var(--muted-text);font-weight:750;letter-spacing:1.3px;text-transform:uppercase;margin-bottom:7px}
h1{font-size:29px;line-height:1.15;letter-spacing:-1px;margin:0;font-weight:850}
.subtitle{color:var(--muted-text);margin:8px 0 0;font-size:13px}
.top-actions{display:flex;align-items:center;gap:12px}
.search-wrap{position:relative}.search-wrap span{position:absolute;left:13px;top:10px;color:var(--muted-text)}
.search{width:230px;padding:11px 13px 11px 36px;border:1px solid var(--sage);background:var(--white);border-radius:11px;outline:none;color:var(--dark-text)}
.search:focus{border-color:var(--soft-olive);box-shadow:0 0 0 3px color-mix(in srgb,var(--soft-olive) 40%,transparent)}
.icon-btn{width:40px;height:40px;border:1px solid var(--sage);background:var(--white);border-radius:11px;position:relative;color:var(--dark-text)}
.notification-dot{position:absolute;right:8px;top:7px;width:8px;height:8px;background:var(--terracotta);border:1px solid var(--white);border-radius:50%}
.profile-chip{display:flex;align-items:center;gap:9px;padding-left:4px}
.profile-chip .avatar{height:39px;width:39px;flex-basis:39px;border-radius:50%;background:var(--soft-olive)}
.profile-chip strong{display:block;font-size:12px}.profile-chip small{display:block;color:var(--muted-text);font-size:11px;margin-top:3px}

/* Welcome + stats */
.welcome-strip{display:flex;align-items:center;justify-content:space-between;gap:18px;background:var(--sage);border:1px solid var(--soft-olive);border-radius:15px;padding:18px 21px;margin-bottom:22px}
.welcome-strip h2{font-size:16px;margin:0 0 5px;font-weight:800}.welcome-strip p{margin:0;color:var(--muted-text);font-size:12px}
.date-pill{border:1px solid var(--soft-olive);background:var(--white);padding:9px 12px;border-radius:9px;font-size:11px;font-weight:750;white-space:nowrap}
.stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin-bottom:22px}
.stat-card,.panel{background:var(--white);border:1px solid var(--sage);border-radius:15px;box-shadow:var(--shadow);min-width:0}
.stat-card{padding:19px}
.stat-top{display:flex;justify-content:space-between;align-items:center;gap:10px}
.stat-icon{width:42px;height:42px;border-radius:12px;display:grid;place-items:center;font-size:20px;font-weight:800;color:var(--dark-text)}
.olive{background:var(--soft-olive)}.terracotta{background:var(--terracotta)}.sage{background:var(--sage)}.cream{background:var(--light-cream);box-shadow:inset 0 0 0 1px var(--soft-olive)}
.stat-label{font-size:12px;color:var(--muted-text);font-weight:650;margin-top:17px}
.stat-value{font-size:29px;letter-spacing:-1px;font-weight:850;margin-top:5px}
.stat-foot{font-size:11px;color:var(--muted-text);margin-top:8px}.trend{color:var(--dark-text);font-weight:800}

/* Panels + chart */
.content-grid{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(300px,1fr);gap:20px;margin-bottom:22px}
.panel{padding:21px}
.panel-head{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;margin-bottom:19px}
.panel-title{font-size:16px;font-weight:850;letter-spacing:-.3px;margin:0}
.panel-desc{color:var(--muted-text);font-size:12px;margin:6px 0 0}
.text-link{font-size:11px;font-weight:800;color:var(--muted-text);white-space:nowrap;border-bottom:2px solid var(--soft-olive)}
.text-link:hover{color:var(--dark-text);border-color:var(--terracotta)}
.chart-summary{display:flex;gap:20px;margin:4px 0 10px}.chart-summary strong{font-size:21px;display:block}.chart-summary span{font-size:11px;color:var(--muted-text)}
.chart{width:100%;height:190px;display:block}
.chart-grid{stroke:var(--border-color);stroke-width:1}.chart-label{fill:var(--muted-text);font-size:10px}
.chart-line{fill:none;stroke:var(--terracotta);stroke-width:3;stroke-linecap:round;stroke-linejoin:round}
.chart-dot{fill:var(--terracotta);stroke:var(--white);stroke-width:2}
.field-list{display:grid;gap:19px}
.field-row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center}
.field-name{font-size:12px;font-weight:700}.field-percent{font-size:12px;color:var(--muted-text);font-weight:800}
.progress{grid-column:1/-1;height:8px;background:var(--sage-soft);border-radius:20px;overflow:hidden}
.progress span{display:block;height:100%;border-radius:20px}
.note{margin-top:20px;padding:12px;background:var(--light-cream);border:1px solid var(--border-color);border-radius:10px;color:var(--muted-text);font-size:11px;line-height:1.55}
.note strong{color:var(--dark-text)}

/* Table */
.table-panel{padding:0;overflow:hidden}.table-panel .panel-head{padding:21px 21px 0}
.table-tools{display:flex;align-items:center;gap:9px}
.filter-select{padding:9px 11px;border:1px solid var(--sage);border-radius:9px;background:var(--white);color:var(--muted-text);font-size:11px;outline:none}
.table-scroll{overflow-x:auto}
table{width:100%;border-collapse:collapse;white-space:nowrap}
thead{background:var(--sage-soft)}
th{padding:13px 20px;text-align:left;font-size:10px;letter-spacing:.8px;text-transform:uppercase;color:var(--muted-text);font-weight:800;border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color)}
td{padding:15px 20px;border-bottom:1px solid var(--border-color);font-size:12px}
tbody tr:last-child td{border-bottom:0}tbody tr:hover{background:var(--light-cream)}
.company-cell{display:flex;align-items:center;gap:10px}
.company-logo{height:35px;width:35px;border-radius:10px;display:grid;place-items:center;font-size:11px;font-weight:850;color:var(--dark-text)}
.company-cell strong{display:block;font-size:12px;font-weight:750}.company-cell small{display:block;color:var(--muted-text);font-size:10px;margin-top:4px}
.status{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:30px;font-size:10px;font-weight:800;color:var(--dark-text)}
.status:before{content:"";width:6px;height:6px;border-radius:50%;background:var(--dark-text)}
.status.pending{background:var(--soft-olive)}.status.verified{background:var(--sage)}.status.rejected{background:var(--terra-soft);box-shadow:inset 0 0 0 1px var(--terracotta)}
.action-btn{border:1px solid var(--soft-olive);background:var(--white);border-radius:8px;padding:7px 12px;color:var(--dark-text);font-size:11px;font-weight:750}
.action-btn:hover{background:var(--soft-olive)}
.action-btn.review-btn{background:var(--terracotta);border-color:var(--terracotta)}
.action-btn.review-btn:hover{background:color-mix(in srgb,var(--terracotta) 85%,var(--dark-text))}

/* Bottom */
.bottom-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:22px}
.activity-item{display:flex;gap:12px;padding:12px 0;border-bottom:1px solid var(--border-color)}
.activity-item:last-child{border-bottom:0;padding-bottom:0}
.activity-icon{height:34px;width:34px;flex:0 0 34px;border-radius:10px;display:grid;place-items:center;font-weight:900;color:var(--dark-text)}
.activity-text{min-width:0;flex:1}.activity-text strong{display:block;font-size:12px}.activity-text p{font-size:11px;color:var(--muted-text);margin:4px 0 0}
.activity-time{font-size:10px;color:var(--muted-text);white-space:nowrap}
.quick-actions{display:grid;grid-template-columns:1fr 1fr;gap:11px}
.quick-action{display:flex;align-items:center;gap:11px;text-align:left;padding:14px;border:1px solid var(--sage);border-radius:11px;background:var(--white);color:var(--dark-text);transition:.18s}
.quick-action:hover{transform:translateY(-2px);box-shadow:var(--shadow);background:var(--light-cream);border-color:var(--soft-olive)}
.quick-icon{height:36px;width:36px;border-radius:10px;display:grid;place-items:center;background:var(--soft-olive);font-size:17px}
.quick-action:first-child .quick-icon{background:var(--terracotta)}
.quick-action strong{display:block;font-size:11px}.quick-action small{display:block;color:var(--muted-text);font-size:10px;margin-top:4px}
.footer{padding:23px 0 0;text-align:center;color:var(--muted-text);font-size:10px}
.mobile-menu{display:none}
.empty-state{padding:24px;text-align:center;color:var(--muted-text);font-size:12px;display:none}
.toast{position:fixed;right:22px;bottom:22px;background:var(--dark-text);color:var(--white);padding:13px 17px;border-radius:10px;border-left:4px solid var(--terracotta);box-shadow:var(--shadow);z-index:50;opacity:0;transform:translateY(10px);transition:.2s;pointer-events:none}
.toast.show{opacity:1;transform:none}

@media(max-width:1200px){.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.content-grid{grid-template-columns:1fr}.main{padding:24px}}
@media(max-width:760px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:none;box-shadow:10px 0 30px rgba(45,58,47,.15)}.main{margin-left:0;padding:19px 15px 28px}.mobile-menu{display:inline-grid;place-items:center}.topbar{align-items:flex-start}.top-actions{gap:7px}.search-wrap{display:none}.profile-chip>div:last-child{display:none}h1{font-size:24px}.welcome-strip{align-items:flex-start;flex-direction:column}.stats{gap:11px}.stat-card{padding:15px}.stat-value{font-size:25px}.bottom-grid{grid-template-columns:1fr}.panel{padding:16px}.table-panel{padding:0}.table-panel .panel-head{padding:17px 16px 0}.chart{height:170px}}
@media(max-width:400px){.quick-actions{grid-template-columns:1fr}.top-actions .icon-btn{display:none}}
@media(prefers-reduced-motion:reduce){*{transition:none!important}}
</style></head>
<body><div class="app">
<aside class="sidebar" id="sidebar">
 <a href="#dashboard" class="brand" aria-label="4Hire dashboard home"><div class="brand-mark">4</div><div><div class="brand-name">4<span>Hire</span></div><div class="brand-sub">Admin workspace</div></div></a>
 <div class="nav-label">WORKSPACE</div>
 <nav class="nav" aria-label="Main navigation">
  <a class="active" href="#dashboard" data-section="Dashboard"><span class="nav-icon">▦</span>Dashboard</a>
  <a href="#employers" data-section="Employer Verification"><span class="nav-icon">♙</span>Employer Verification <span class="nav-count">12</span></a>
  <a href="#applicants" data-section="Applicants"><span class="nav-icon">♧</span>Applicants</a>
  <a href="#jobs" data-section="Job Posts"><span class="nav-icon">▤</span>Job Posts</a>
  <a href="#resumes" data-section="Resume Management"><span class="nav-icon">▧</span>Resume Management</a>
  <a href="#fields" data-section="AI Field Classification"><span class="nav-icon">◈</span>AI Field Classification</a>
 </nav>
 <div class="nav-label">ADMINISTRATION</div>
 <nav class="nav" aria-label="Administration navigation">
  <a href="#activity" data-section="Activity Logs"><span class="nav-icon">◷</span>Activity Logs</a>
  <a href="#privacy" data-section="Privacy &amp; Security"><span class="nav-icon">♢</span>Privacy &amp; Security</a>
  <a href="#settings" data-section="Settings"><span class="nav-icon">⚙</span>Settings</a>
 </nav>
 <div class="sidebar-bottom">
  <nav class="nav"><a href="#help" data-section="Help Center"><span class="nav-icon">?</span>Help Center</a><a href="#logout" id="logout"><span class="nav-icon">⇥</span>Log out</a></nav>
  <div class="admin-mini"><div class="avatar">AD</div><div><div class="admin-name">Admin User</div><div class="admin-role">System Administrator</div></div></div>
 </div>
</aside>

<main class="main" id="dashboard">
<header class="topbar">
 <div style="display:flex;align-items:flex-start;gap:12px"><button class="icon-btn mobile-menu" id="menuToggle" aria-label="Open navigation">☰</button><div><div class="eyebrow">Overview / Workspace</div><h1>Admin Dashboard</h1><p class="subtitle">Welcome back! Here's what's happening on 4Hire today.</p></div></div>
 <div class="top-actions">
  <div class="search-wrap"><span>⌕</span><input class="search" id="globalSearch" type="search" placeholder="Search companies..." aria-label="Search companies"></div>
  <button class="icon-btn" id="notificationBtn" aria-label="Notifications">♧<i class="notification-dot"></i></button>
  <div class="profile-chip"><div class="avatar">AD</div><div><strong>Admin User</strong><small>Administrator</small></div></div>
 </div>
</header>

<section class="welcome-strip"><div><h2>Good day, Admin 👋</h2><p>Review employer accounts, keep applicant data secure, and monitor platform activity.</p></div><div class="date-pill" id="todayDate">📅 October 09, 2026</div></section>

<section class="stats" aria-label="Platform statistics">
<?php foreach($stats as $s): ?>
 <article class="stat-card"><div class="stat-top"><div class="stat-icon <?= htmlspecialchars($s['tone']) ?>"><?= htmlspecialchars($s['icon']) ?></div><span style="color:var(--muted-text);font-size:18px">···</span></div><div class="stat-label"><?= htmlspecialchars($s['label']) ?></div><div class="stat-value"><?= htmlspecialchars($s['value']) ?></div><div class="stat-foot"><span class="trend"><?= htmlspecialchars($s['change']) ?></span></div></article>
<?php endforeach; ?>
</section>

<section class="content-grid">
 <article class="panel">
  <div class="panel-head"><div><h2 class="panel-title">Platform Activity</h2><p class="panel-desc">Applicant registrations over the last 6 months</p></div><a class="text-link" href="#activity">View activity ↗</a></div>
  <div class="chart-summary"><div><strong>1,284</strong><span>Total applicants</span></div><div><strong>+18.6%</strong><span>Compared with previous period</span></div></div>
  <svg class="chart" viewBox="0 0 620 205" role="img" aria-label="Line chart showing applicant registrations increasing from May to October">
   <defs><linearGradient id="areaFill" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#bcc590" stop-opacity=".5"/><stop offset="100%" stop-color="#bcc590" stop-opacity=".03"/></linearGradient></defs>
   <line class="chart-grid" x1="40" y1="25" x2="605" y2="25"/><line class="chart-grid" x1="40" y1="65" x2="605" y2="65"/><line class="chart-grid" x1="40" y1="105" x2="605" y2="105"/><line class="chart-grid" x1="40" y1="145" x2="605" y2="145"/><line class="chart-grid" x1="40" y1="175" x2="605" y2="175"/>
   <text class="chart-label" x="7" y="29">300</text><text class="chart-label" x="7" y="69">225</text><text class="chart-label" x="7" y="109">150</text><text class="chart-label" x="13" y="149">75</text><text class="chart-label" x="20" y="179">0</text>
   <path fill="url(#areaFill)" d="M45 150 L150 125 L255 135 L360 90 L465 72 L570 35 L570 175 L45 175 Z"/>
   <path class="chart-line" d="M45 150 L150 125 L255 135 L360 90 L465 72 L570 35"/>
   <circle class="chart-dot" cx="45" cy="150" r="5"/><circle class="chart-dot" cx="150" cy="125" r="5"/><circle class="chart-dot" cx="255" cy="135" r="5"/><circle class="chart-dot" cx="360" cy="90" r="5"/><circle class="chart-dot" cx="465" cy="72" r="5"/><circle class="chart-dot" cx="570" cy="35" r="5"/>
   <text class="chart-label" x="35" y="197">May</text><text class="chart-label" x="136" y="197">Jun</text><text class="chart-label" x="241" y="197">Jul</text><text class="chart-label" x="345" y="197">Aug</text><text class="chart-label" x="450" y="197">Sep</text><text class="chart-label" x="555" y="197">Oct</text>
  </svg>
 </article>

 <article class="panel" id="fields">
  <div class="panel-head"><div><h2 class="panel-title">Applicant Job Fields</h2><p class="panel-desc">Confirmed fields selected by applicants</p></div><a class="text-link" href="#applicants">Details ↗</a></div>
  <div class="field-list">
  <?php foreach($fields as $f): ?>
   <div class="field-row"><span class="field-name"><?= htmlspecialchars($f[0]) ?></span><span class="field-percent"><?= (int)$f[1] ?>%</span><div class="progress"><span class="<?= htmlspecialchars($f[2]) ?>" style="width:<?= (int)$f[1] ?>%"></span></div></div>
  <?php endforeach; ?>
  </div>
  <div class="note"><strong>AI classification:</strong> AI provides a suggested job field only. Applicants can confirm or edit it. Employers see and filter by the applicant's confirmed fields—not an AI score or ranking.</div>
 </article>
</section>

<section class="panel table-panel" id="employers">
 <div class="panel-head"><div><h2 class="panel-title">Employer Verification</h2><p class="panel-desc">Review company accounts before granting resume access.</p></div>
  <div class="table-tools"><select class="filter-select" id="statusFilter" aria-label="Filter by status"><option value="All">All statuses</option><option value="Pending">Pending</option><option value="Verified">Verified</option><option value="Rejected">Rejected</option></select><a class="text-link" href="#employers">View all ↗</a></div></div>
 <div class="table-scroll">
  <table id="employerTable"><thead><tr><th>Company</th><th>Date submitted</th><th>Status</th><th>Action</th></tr></thead><tbody>
  <?php foreach($employers as $e): ?>
   <tr data-status="<?= htmlspecialchars($e['status']) ?>" data-company="<?= htmlspecialchars(strtolower($e['company'].' '.$e['email'])) ?>">
    <td><div class="company-cell"><div class="company-logo <?= htmlspecialchars($e['color']) ?>"><?= htmlspecialchars($e['initials']) ?></div><div><strong><?= htmlspecialchars($e['company']) ?></strong><small><?= htmlspecialchars($e['email']) ?></small></div></div></td>
    <td><?= htmlspecialchars($e['date']) ?></td>
    <td><span class="status <?= strtolower($e['status']) ?>"><?= htmlspecialchars($e['status']) ?></span></td>
    <td><?php if($e['status']==='Pending'): ?><button class="action-btn review-btn" data-company-name="<?= htmlspecialchars($e['company']) ?>">Review</button><?php else: ?><button class="action-btn details-btn" data-company-name="<?= htmlspecialchars($e['company']) ?>">Details</button><?php endif; ?></td>
   </tr>
  <?php endforeach; ?>
  </tbody></table>
  <div class="empty-state" id="emptyState">No employer accounts match your search.</div>
 </div>
</section>

<section class="bottom-grid">
 <article class="panel" id="activity">
  <div class="panel-head"><div><h2 class="panel-title">Recent Activity</h2><p class="panel-desc">Latest actions across the platform</p></div><a class="text-link" href="#activity">View logs ↗</a></div>
  <div class="activity-list">
  <?php foreach($activities as $a): ?>
   <div class="activity-item"><div class="activity-icon <?= htmlspecialchars($a['tone']) ?>"><?= htmlspecialchars($a['icon']) ?></div><div class="activity-text"><strong><?= htmlspecialchars($a['title']) ?></strong><p><?= htmlspecialchars($a['detail']) ?></p></div><span class="activity-time"><?= htmlspecialchars($a['time']) ?></span></div>
  <?php endforeach; ?>
  </div>
 </article>
 <article class="panel">
  <div class="panel-head"><div><h2 class="panel-title">Quick Actions</h2><p class="panel-desc">Common administrator tasks</p></div></div>
  <div class="quick-actions">
   <button class="quick-action" data-action="Review pending employer verification requests"><span class="quick-icon">♙</span><span><strong>Review Employers</strong><small>12 requests waiting</small></span></button>
   <button class="quick-action" data-action="Open applicant management"><span class="quick-icon">♧</span><span><strong>Manage Applicants</strong><small>View applicant accounts</small></span></button>
   <button class="quick-action" data-action="Open job post moderation"><span class="quick-icon">▤</span><span><strong>Moderate Job Posts</strong><small>Check reported listings</small></span></button>
   <button class="quick-action" data-action="Open privacy and security audit"><span class="quick-icon">♢</span><span><strong>Privacy Audit</strong><small>Review access logs</small></span></button>
  </div>
  <div class="note"><strong>Privacy reminder:</strong> Resume access should be limited to verified employers. Log resume views and downloads, use short-lived signed links, and apply retention limits.</div>
 </article>
</section>
<footer class="footer">© 2026 4Hire · Admin Workspace · Sample dashboard data for frontend demonstration</footer>
</main></div>
<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
const sidebar=document.getElementById('sidebar'),toast=document.getElementById('toast');let toastTimer;
function showToast(m){toast.textContent=m;toast.classList.add('show');clearTimeout(toastTimer);toastTimer=setTimeout(()=>toast.classList.remove('show'),2800)}
document.getElementById('menuToggle')?.addEventListener('click',()=>sidebar.classList.toggle('open'));
document.querySelectorAll('.nav a[data-section]').forEach(l=>l.addEventListener('click',()=>{document.querySelectorAll('.nav a').forEach(a=>a.classList.remove('active'));l.classList.add('active');sidebar.classList.remove('open');if(l.dataset.section!=='Dashboard')showToast(l.dataset.section+' selected — connect this menu to its page or route.')}));
document.getElementById('globalSearch').addEventListener('input',filterEmployers);
document.getElementById('statusFilter').addEventListener('change',filterEmployers);
function filterEmployers(){
 const q=document.getElementById('globalSearch').value.trim().toLowerCase(),s=document.getElementById('statusFilter').value;let v=0;
 document.querySelectorAll('#employerTable tbody tr').forEach(r=>{const show=r.dataset.company.includes(q)&&(s==='All'||r.dataset.status===s);r.style.display=show?'':'none';if(show)v++});
 document.getElementById('emptyState').style.display=v?'none':'block'}
document.getElementById('employerTable').addEventListener('click',e=>{
 const b=e.target.closest('button');if(!b)return;
 const company=b.dataset.companyName;
 if(b.classList.contains('review-btn')){
  const choice=prompt('Review '+company+'\nType VERIFIED to approve or REJECTED to reject.\n\nDemo only — this does not save to a database.');
  if(!choice)return;const n=choice.trim().toLowerCase();
  if(!['verified','rejected'].includes(n)){showToast('Please enter VERIFIED or REJECTED.');return}
  const row=b.closest('tr'),next=n==='verified'?'Verified':'Rejected';row.dataset.status=next;
  const c=row.querySelector('.status');c.className='status '+n;c.textContent=next;
  b.textContent='Details';b.classList.replace('review-btn','details-btn');
  filterEmployers();showToast(company+' marked '+next+' (demo only).');
 }else showToast('Viewing '+company+' — connect to the employer details page.');
});
document.addEventListener('click',e=>{if(!sidebar.contains(e.target)&&!e.target.closest('#menuToggle'))sidebar.classList.remove('open')});
document.querySelectorAll('.quick-action').forEach(b=>b.addEventListener('click',()=>showToast(b.dataset.action+' — connect this action to your backend.')));
document.getElementById('notificationBtn').addEventListener('click',()=>showToast('You have 12 pending employer verification requests.'));
document.getElementById('logout').addEventListener('click',e=>{e.preventDefault();showToast('Demo logout — connect this to your Laravel logout route.')});
document.getElementById('todayDate').textContent='📅 '+new Date().toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'});
</script>
</body>
</html>
