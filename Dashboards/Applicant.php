<?php

$applicantName = "Juan Dela Cruz";
$completeness = 80;
$resume = ["file"=>"JuanDelaCruz_Resume.pdf","type"=>"PDF","size"=>"284 KB","uploaded"=>"Oct 04, 2026","status"=>"Analysis complete"];
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>4Hire | My Dashboard</title>
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

/* Main */
.main{margin-left:var(--sidebar-width);padding:28px 32px 40px}
.card,.top-header,.verification,.stat-card,.quick-action{background:var(--white);border:1px solid var(--sage);box-shadow:var(--shadow)}
.top-header{border-radius:15px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;margin-bottom:20px}
.page-title{font-size:29px;font-weight:850;letter-spacing:-1px;line-height:1.15}
.page-description{margin-top:8px;font-size:13px;color:var(--muted-text)}

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

/* Recent applicants */

/* Quick actions */
.toast{position:fixed;right:22px;bottom:22px;background:var(--dark-text);color:var(--white);padding:13px 17px;border-radius:10px;border-left:4px solid var(--terracotta);box-shadow:var(--shadow);z-index:200;opacity:0;transform:translateY(10px);transition:.2s;pointer-events:none}
.toast.show{opacity:1;transform:none}


/* Applicant-specific */
.top-meta{text-align:right}.top-meta strong{display:block;font-size:12px}.top-meta span{display:block;margin-top:3px;color:var(--muted-text);font-size:11px}
.logout{margin-left:auto;background:none;border:0;color:var(--muted-text);font-size:15px;padding:6px;border-radius:8px}.logout:hover{background:var(--sage-soft);color:var(--dark-text)}
.completeness{border-radius:15px;padding:18px 21px;margin-bottom:20px;display:flex;align-items:center;gap:22px;background:var(--sage);border:1px solid var(--soft-olive);box-shadow:none}
.completeness h2{font-size:16px;font-weight:800;margin-bottom:5px}.completeness p{font-size:12px;color:var(--muted-text)}
.meter{flex:1;max-width:340px}.meter-top{display:flex;justify-content:space-between;font-size:11px;font-weight:800;margin-bottom:7px}
.progress{height:8px;background:var(--white);border-radius:20px;overflow:hidden}.progress span{display:block;height:100%;background:var(--terracotta);border-radius:20px}
.card-body{padding:19px 21px 21px}
.file-row{display:flex;align-items:center;gap:14px;padding:14px;border:1px solid var(--border-color);border-radius:12px;background:var(--light-cream)}
.file-icon{width:44px;height:44px;flex:0 0 44px;border-radius:12px;background:var(--terracotta);display:grid;place-items:center;font-size:18px}
.file-info{min-width:0;flex:1}.file-info strong{display:block;font-size:13px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.file-info small{display:block;font-size:11px;color:var(--muted-text);margin-top:4px}
.status{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:30px;font-size:10px;font-weight:800;background:var(--sage);white-space:nowrap}
.status:before{content:"";width:6px;height:6px;border-radius:50%;background:var(--dark-text)}.status.working{background:var(--soft-olive)}
.btn-row{display:flex;flex-wrap:wrap;gap:10px;margin-top:16px}
.btn{border:1px solid var(--terracotta);background:var(--terracotta);color:var(--dark-text);padding:9px 14px;border-radius:9px;font-size:11px;font-weight:750;transition:.2s;display:inline-flex;align-items:center;gap:7px}
.btn:hover{background:color-mix(in srgb,var(--terracotta) 85%,var(--dark-text))}
.btn.outline{background:var(--white);border-color:var(--soft-olive)}.btn.outline:hover{background:var(--soft-olive)}
.btn.danger{background:var(--white);border-color:var(--terracotta)}.btn.danger:hover{background:var(--terra-soft)}
.dropzone{margin-top:16px;border:2px dashed var(--soft-olive);border-radius:12px;padding:22px;text-align:center;font-size:12px;color:var(--muted-text);display:block;cursor:pointer;transition:.2s}
.dropzone:hover,.dropzone:focus-within{background:var(--sage-soft);border-color:var(--terracotta)}.dropzone strong{display:block;color:var(--dark-text);font-size:13px;margin:6px 0 3px}.dropzone i{font-size:20px;color:var(--terracotta)}
.dropzone input{position:absolute;opacity:0;width:1px;height:1px}
.steps{list-style:none;display:grid;gap:11px;margin-top:18px}.steps li{display:flex;align-items:center;gap:10px;font-size:12px}
.step-dot{width:22px;height:22px;border-radius:50%;display:grid;place-items:center;font-size:10px;background:var(--soft-olive)}.steps li.todo{color:var(--muted-text)}.steps li.todo .step-dot{background:var(--white);border:1px solid var(--soft-olive)}
.suggest{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:14px;border-radius:12px;background:var(--terra-soft);border:1px solid var(--terracotta);margin-bottom:16px}
.suggest small{display:block;font-size:11px;color:var(--muted-text);margin-bottom:4px}.suggest strong{font-size:14px}
.chips{display:flex;flex-wrap:wrap;gap:9px}
.chip{border:1px solid var(--soft-olive);background:var(--white);color:var(--dark-text);padding:9px 13px;border-radius:30px;font-size:12px;font-weight:650;transition:.2s}
.chip:hover{background:var(--sage-soft)}.chip[aria-pressed=true]{background:var(--soft-olive)}.chip[aria-pressed=true]:before{content:"\2713\00a0"}
.hint{font-size:11px;color:var(--muted-text);margin:12px 0 0;line-height:1.55}
.note{margin-top:16px;padding:12px;background:var(--light-cream);border:1px solid var(--border-color);border-radius:10px;color:var(--muted-text);font-size:11px;line-height:1.55}.note strong{color:var(--dark-text)}
.bottom-grid{display:grid;grid-template-columns:minmax(0,1.3fr) minmax(300px,1fr);gap:20px}
.view-item{display:flex;align-items:center;gap:12px;padding:12px 0;border-bottom:1px solid var(--border-color)}.view-item:last-child{border-bottom:0;padding-bottom:0}
.view-logo{width:35px;height:35px;flex:0 0 35px;border-radius:10px;display:grid;place-items:center;font-size:11px;font-weight:850}
.view-text{flex:1;min-width:0}.view-text strong{display:block;font-size:12px;font-weight:750}.view-text small{display:block;font-size:10px;color:var(--muted-text);margin-top:4px}
.view-time{font-size:10px;color:var(--muted-text);white-space:nowrap}
.setting{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:14px 0;border-bottom:1px solid var(--border-color)}.setting:first-child{padding-top:0}
.setting strong{display:block;font-size:12px}.setting small{display:block;font-size:11px;color:var(--muted-text);margin-top:4px;line-height:1.5}
.switch{width:44px;height:26px;flex:0 0 44px;border-radius:20px;border:0;background:var(--sage);position:relative;transition:.2s}
.switch:after{content:"";position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:var(--white);box-shadow:0 1px 3px rgba(45,58,47,.25);transition:.2s}
.switch[aria-checked=true]{background:var(--terracotta)}.switch[aria-checked=true]:after{left:21px}

/* Responsive */
@media(max-width:1100px){:root{--sidebar-width:220px}.stats{grid-template-columns:repeat(2,minmax(0,1fr))}.content-grid,.bottom-grid{grid-template-columns:1fr}}
@media(max-width:750px){:root{--sidebar-width:68px}.logo-container{padding:20px 0;justify-content:center}.logo-container>div:last-child,.menu-title,.menu-item span,.user-info,.logout{display:none}.sidebar-content{padding:6px 8px}.menu-item{justify-content:center;padding:12px 8px}.sidebar-user{justify-content:center;margin:0 8px 14px;padding:14px 0}.main{padding:18px 15px 28px}.top-header{padding:16px}.page-title{font-size:20px}.top-meta{display:none}.completeness{flex-direction:column;align-items:stretch;gap:14px}.meter{max-width:none}.suggest{flex-direction:column;align-items:flex-start}}
@media(max-width:480px){.stats{grid-template-columns:1fr}.file-row{flex-wrap:wrap}}
@media(prefers-reduced-motion:reduce){*{transition:none!important;scroll-behavior:auto!important}}
</style>
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
  <button class="logout" id="logout" aria-label="Log out"><i class="fa-solid fa-right-from-bracket"></i></button>
 </div>
</aside>

<main class="main">
 <header class="top-header">
  <div><h1 class="page-title">My Dashboard</h1><p class="page-description">Keep your resume and job fields up to date so verified employers can find you.</p></div>
  <div class="top-meta"><strong><?= h($applicantName) ?></strong><span>Applicant Account</span></div>
 </header>

 <section class="card completeness">
  <div><h2>Your profile is almost ready</h2><p>Next step: review your job fields so employers can filter and find you.</p></div>
  <div class="meter"><div class="meter-top"><span>Profile completeness</span><span><?= (int)$completeness ?>%</span></div><div class="progress"><span style="width:<?= (int)$completeness ?>%"></span></div></div>
 </section>

 <section class="stats" aria-label="Your summary">
  <article class="stat-card"><div class="stat-icon olive"><i class="fa-solid fa-file-circle-check"></i></div><div class="stat-label">Resume</div><div class="stat-number" style="font-size:22px;margin-top:9px">Uploaded</div><div class="stat-change">Last updated <?= h($resume['uploaded']) ?></div></article>
  <article class="stat-card"><div class="stat-icon terracotta"><i class="fa-solid fa-eye"></i></div><div class="stat-label">Employer views</div><div class="stat-number"><?= count($views) ?></div><div class="stat-change">Verified employers only</div></article>
  <article class="stat-card"><div class="stat-icon sage"><i class="fa-solid fa-tags"></i></div><div class="stat-label">Confirmed fields</div><div class="stat-number" id="confirmedCount"><?= count($confirmedFields) ?></div><div class="stat-change">Shown to employers</div></article>
  <article class="stat-card"><div class="stat-icon cream"><i class="fa-solid fa-user-check"></i></div><div class="stat-label">Profile</div><div class="stat-number"><?= (int)$completeness ?>%</div><div class="stat-change">Complete</div></article>
 </section>

 <div class="content-grid">
  <section class="card" id="resume">
   <div class="card-header"><h2 class="card-title">My Resume</h2><p class="card-subtitle">One resume at a time. Stored privately; only verified employers can open it.</p></div>
   <div class="card-body">
    <div class="file-row">
     <div class="file-icon"><i class="fa-solid fa-file-pdf"></i></div>
     <div class="file-info"><strong id="fileName"><?= h($resume['file']) ?></strong><small><span id="fileMeta"><?= h($resume['type'].' · '.$resume['size'].' · Uploaded '.$resume['uploaded']) ?></span></small></div>
     <span class="status" id="fileStatus"><?= h($resume['status']) ?></span>
    </div>
    <ul class="steps" aria-label="Resume processing">
     <li><span class="step-dot"><i class="fa-solid fa-check"></i></span>Uploaded to private storage</li>
     <li><span class="step-dot"><i class="fa-solid fa-check"></i></span>Text read from your resume</li>
     <li id="stepAi"><span class="step-dot"><i class="fa-solid fa-check"></i></span>Job field suggested</li>
    </ul>
    <label class="dropzone"><i class="fa-solid fa-cloud-arrow-up"></i><strong>Replace your resume</strong>Drop a PDF or DOCX here, or click to browse (max 5 MB)<input type="file" id="resumeInput" accept=".pdf,.doc,.docx"></label>
    <div class="btn-row">
     <button class="btn outline" data-action="Download your resume"><i class="fa-solid fa-download"></i>Download</button>
     <button class="btn danger" id="deleteResume"><i class="fa-solid fa-trash"></i>Delete resume</button>
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
const toast=document.getElementById('toast');let t;
function showToast(m){toast.textContent=m;toast.classList.add('show');clearTimeout(t);t=setTimeout(()=>toast.classList.remove('show'),2800)}
document.querySelectorAll('.menu-item').forEach(l=>l.addEventListener('click',()=>{document.querySelectorAll('.menu-item').forEach(a=>{a.classList.remove('active');a.removeAttribute('aria-current')});l.classList.add('active');l.setAttribute('aria-current','page')}));
document.querySelector('.menu-item[href="#"]:not(.active)')?.addEventListener('click',e=>{e.preventDefault();showToast('Connect this menu to its page or route.')});
const chips=[...document.querySelectorAll('.chip')];
function updateCount(){document.getElementById('confirmedCount').textContent=chips.filter(c=>c.getAttribute('aria-pressed')==='true').length}
chips.forEach(c=>c.addEventListener('click',()=>{c.setAttribute('aria-pressed',c.getAttribute('aria-pressed')==='true'?'false':'true')}));
document.getElementById('acceptSuggestion').addEventListener('click',()=>{chips.find(c=>c.dataset.field===<?= json_encode($suggestedField) ?>)?.setAttribute('aria-pressed','true');showToast('Suggestion selected. Confirm your fields to save.')});
document.getElementById('saveFields').addEventListener('click',()=>{const n=chips.filter(c=>c.getAttribute('aria-pressed')==='true').length;if(!n){showToast('Select at least one field, or choose Other / Unsure.');return}updateCount();showToast('Fields confirmed (demo only).')});
document.getElementById('resumeInput').addEventListener('change',e=>{const f=e.target.files[0];if(!f)return;if(f.size>5*1024*1024){showToast('File is larger than 5 MB.');e.target.value='';return}
 document.getElementById('fileName').textContent=f.name;document.getElementById('fileMeta').textContent=Math.max(1,Math.round(f.size/1024))+' KB · Uploaded just now';
 const s=document.getElementById('fileStatus');s.textContent='Analyzing…';s.classList.add('working');showToast('Resume uploaded. Suggesting a job field…');
 setTimeout(()=>{s.textContent='Analysis complete';s.classList.remove('working');showToast('New field suggestion ready (demo only).')},1800)});
document.getElementById('visibility').addEventListener('click',e=>{const b=e.currentTarget,on=b.getAttribute('aria-checked')!=='true';b.setAttribute('aria-checked',on);showToast(on?'Your resume is visible to verified employers.':'Your resume is hidden from employers.')});
document.querySelectorAll('[data-action]').forEach(b=>b.addEventListener('click',()=>showToast(b.dataset.action+' — connect this to your backend.')));
document.getElementById('deleteResume').addEventListener('click',()=>{if(confirm('Delete your resume? Employers will no longer be able to find you.'))showToast('Resume deleted (demo only).')});
document.getElementById('deleteAccount').addEventListener('click',()=>{if(confirm('Delete your account and all data? This cannot be undone.'))showToast('Account deleted (demo only).')});
document.getElementById('logout').addEventListener('click',()=>showToast('Demo logout — connect this to your logout route.'));
</script>
</body>
</html>
