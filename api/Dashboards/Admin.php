<?php
require_once __DIR__ . '/../includes/config/database.php';
require_once __DIR__ . '/../includes/config/database.php';
startAppSession();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: /Auth/login.php');
    exit();
}

$pdo = getDbConnection();
$reviewMessage = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employerId = filter_var($_POST['employer_id'] ?? null, FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    $notes = trim($_POST['review_notes'] ?? '');
    if (!validCsrfToken($_POST['csrf_token'] ?? null)) {
        $reviewMessage = 'Your form session expired. Refresh the page and try again.';
    } elseif (!$employerId || !in_array($decision, ['approve', 'reject'], true) || strlen($notes) > 1000) {
        $reviewMessage = 'That verification decision was not valid.';
    } else {
        try {
            $status = $decision === 'approve' ? 'verified' : 'rejected';
            $update = $pdo->prepare(
                'UPDATE public.employers
                 SET verification_status = :status,
                     verification_reviewed_at = CURRENT_TIMESTAMP,
                     verification_reviewed_by = :admin_id,
                     verification_notes = :notes
                 WHERE id = :employer_id'
            );
            $update->execute([
                'status' => $status,
                'admin_id' => (int) $_SESSION['user_id'],
                'notes' => $notes !== '' ? $notes : null,
                'employer_id' => $employerId,
            ]);
            $reviewMessage = $update->rowCount() === 1
                ? 'Employer verification updated: ' . ucfirst($status) . '.'
                : 'Employer record not found.';
        } catch (Throwable $e) {
            error_log('Employer verification review failed: ' . $e->getMessage());
            $reviewMessage = 'Could not save the verification decision. Check that the verification migration has been run.';
        }
    }
}

$applicantCount = (int) $pdo->query('SELECT COUNT(*) FROM public.applicants')->fetchColumn();
$employerCount = (int) $pdo->query('SELECT COUNT(*) FROM public.employers')->fetchColumn();
$pendingCount = (int) $pdo->query("SELECT COUNT(*) FROM public.employers WHERE verification_status = 'pending'")->fetchColumn();
$verifiedCount = (int) $pdo->query("SELECT COUNT(*) FROM public.employers WHERE verification_status = 'verified'")->fetchColumn();
$stats = [
 ['label'=>'Total Applicants','value'=>number_format($applicantCount),'change'=>'From applicant profiles','icon'=>'♙','tone'=>'olive'],
 ['label'=>'Employer Accounts','value'=>number_format($employerCount),'change'=>'From employer profiles','icon'=>'▣','tone'=>'terracotta'],
 ['label'=>'Pending Verification','value'=>number_format($pendingCount),'change'=>'Needs review','icon'=>'◷','tone'=>'cream'],
 ['label'=>'Verified Employers','value'=>number_format($verifiedCount),'change'=>'Approved accounts','icon'=>'✓','tone'=>'sage'],
];
$employerQuery = $pdo->query(
    "SELECT e.id, e.company_name, e.contact_number, e.verification_status,
            e.verification_submitted_at, e.verification_notes, u.email
     FROM public.employers e
     JOIN public.users u ON u.id = e.user_id
     ORDER BY CASE WHEN e.verification_status = 'pending' THEN 0 ELSE 1 END,
              e.verification_submitted_at DESC, e.id DESC"
);
$employers = [];
$colors = ['olive', 'terracotta', 'cream', 'sage'];
foreach ($employerQuery->fetchAll() as $index => $employer) {
    $company = (string) $employer['company_name'];
    $words = preg_split('/\s+/', trim($company)) ?: [];
    $initials = strtoupper(substr($words[0] ?? 'E', 0, 1) . (count($words) > 1 ? substr(end($words), 0, 1) : ''));
    $employers[] = [
        'id' => (int) $employer['id'],
        'company' => $company,
        'email' => (string) $employer['email'],
        'contact' => (string) ($employer['contact_number'] ?? ''),
        'date' => $employer['verification_submitted_at'] ? date('M d, Y', strtotime($employer['verification_submitted_at'])) : '—',
        'status' => ucfirst((string) $employer['verification_status']),
        'notes' => (string) ($employer['verification_notes'] ?? ''),
        'initials' => $initials,
        'color' => $colors[$index % count($colors)],
    ];
}
$adminName = (string) ($_SESSION['user_name'] ?? 'Administrator');
$adminInitials = strtoupper(substr($adminName, 0, 1));
$csrf = csrfToken();
$activityQuery = $pdo->query(
    "SELECT e.company_name, e.verification_status, e.verification_reviewed_at,
            reviewer.full_name AS reviewer_name
     FROM public.employers e
     LEFT JOIN public.users reviewer ON reviewer.id = e.verification_reviewed_by
     WHERE e.verification_reviewed_at IS NOT NULL
     ORDER BY e.verification_reviewed_at DESC
     LIMIT 4"
);
$activities = [];
foreach ($activityQuery->fetchAll() as $activity) {
    $status = (string) $activity['verification_status'];
    $activities[] = [
        'icon' => $status === 'verified' ? '✓' : '×',
        'title' => 'Employer marked ' . ucfirst($status),
        'detail' => $activity['company_name'] . ' · reviewed by ' . ($activity['reviewer_name'] ?: 'administrator'),
        'time' => date('M d, Y H:i', strtotime($activity['verification_reviewed_at'])),
        'tone' => $status === 'verified' ? 'sage' : 'terracotta',
    ];
}
if (!$activities) {
    $activities[] = ['icon'=>'·','title'=>'No verification decisions yet','detail'=>'Reviews you make will appear here.','time'=>'—','tone'=>'cream'];
}
$fields = [['Software Development',42,'olive'],['Web Development',28,'terracotta'],['Networking',18,'sage'],['Other / Unsure',12,'cream']];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>4Hire | Admin Dashboard</title>
<link rel="stylesheet" href="dashboard.css">
</head>
<body class="admin-page"><div class="app">
<aside class="sidebar" id="sidebar">
 <a href="#dashboard" class="brand" aria-label="4Hire dashboard home"><div class="brand-mark">4</div><div><div class="brand-name">4<span>Hire</span></div><div class="brand-sub">Admin workspace</div></div></a>
 <div class="nav-label">WORKSPACE</div>
 <nav class="nav" aria-label="Main navigation">
  <a class="active" href="#dashboard" data-section="Dashboard"><span class="nav-icon">▦</span>Dashboard</a>
  <a href="#employers" data-section="Employer Verification"><span class="nav-icon">♙</span>Employer Verification <span class="nav-count"><?= $pendingCount ?></span></a>
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
  <nav class="nav"><a href="#help" data-section="Help Center"><span class="nav-icon">?</span>Help Center</a><a href="/Auth/logout.php" id="logout"><span class="nav-icon">⇥</span>Log out</a></nav>
  <div class="admin-mini"><div class="avatar"><?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?></div><div><div class="admin-name"><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></div><div class="admin-role">System Administrator</div></div></div>
 </div>
</aside>

<main class="main" id="dashboard">
<header class="topbar">
 <div class="topbar-lead"><button class="icon-btn mobile-menu" id="menuToggle" aria-label="Open navigation">☰</button><div><div class="eyebrow">Overview / Workspace</div><h1>Admin Dashboard</h1><p class="subtitle">Welcome back! Here's what's happening on 4Hire today.</p></div></div>
 <div class="top-actions">
  <div class="search-wrap"><span>⌕</span><input class="search" id="globalSearch" type="search" placeholder="Search companies..." aria-label="Search companies"></div>
  <button class="icon-btn" id="notificationBtn" aria-label="Notifications">♧<i class="notification-dot"></i></button>
  <div class="profile-chip"><div class="avatar"><?= htmlspecialchars($adminInitials, ENT_QUOTES, 'UTF-8') ?></div><div><strong><?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?></strong><small>Administrator</small></div></div>
 </div>
</header>

<section class="welcome-strip"><div><h2>Good day, <?= htmlspecialchars($adminName, ENT_QUOTES, 'UTF-8') ?> 👋</h2><p>Review employer accounts, keep applicant data secure, and monitor platform activity.</p></div><div class="date-pill" id="todayDate">📅</div></section>

<?php if ($reviewMessage !== null): ?>
 <p role="status" class="review-message"><?= htmlspecialchars($reviewMessage, ENT_QUOTES, 'UTF-8') ?></p>
<?php endif; ?>

<section class="stats" aria-label="Platform statistics">
<?php foreach($stats as $s): ?>
 <article class="stat-card"><div class="stat-top"><div class="stat-icon <?= htmlspecialchars($s['tone']) ?>"><?= htmlspecialchars($s['icon']) ?></div><span class="stat-more">···</span></div><div class="stat-label"><?= htmlspecialchars($s['label']) ?></div><div class="stat-value"><?= htmlspecialchars($s['value']) ?></div><div class="stat-foot"><span class="trend"><?= htmlspecialchars($s['change']) ?></span></div></article>
<?php endforeach; ?>
</section>

<section class="content-grid">
 <article class="panel">
  <div class="panel-head"><div><h2 class="panel-title">Platform Activity</h2><p class="panel-desc">Applicant registrations over the last 6 months</p></div><a class="text-link" href="#activity">View activity ↗</a></div>
  <div class="chart-summary"><div><strong><?= number_format($applicantCount) ?></strong><span>Applicant profiles</span></div><div><strong><?= number_format($pendingCount) ?></strong><span>Employer reviews pending</span></div></div>
  <div class="note">These totals are read from the live database. Review individual employer contact details in the verification table below.</div>
 </article>

 <article class="panel" id="fields">
  <div class="panel-head"><div><h2 class="panel-title">Applicant Job Fields</h2><p class="panel-desc">Confirmed fields selected by applicants</p></div><a class="text-link" href="#applicants">Details ↗</a></div>
  <div class="field-list">
  <?php foreach($fields as $f): ?>
   <div class="field-row"><span class="field-name"><?= htmlspecialchars($f[0]) ?></span><span class="field-percent"><?= (int)$f[1] ?>%</span><div class="progress"><span class="<?= htmlspecialchars($f[2]) ?>" style="--value:<?= (int)$f[1] ?>%"></span></div></div>
  <?php endforeach; ?>
  </div>
  <div class="note"><strong>AI classification:</strong> AI provides a suggested job field only. Applicants can confirm or edit it. Employers see and filter by the applicant's confirmed fields—not an AI score or ranking.</div>
 </article>
</section>

<section class="panel table-panel" id="employers">
 <div class="panel-head"><div><h2 class="panel-title">Employer Verification</h2><p class="panel-desc">Review company accounts before granting resume access.</p></div>
  <div class="table-tools"><select class="filter-select" id="statusFilter" aria-label="Filter by status"><option value="All">All statuses</option><option value="Pending">Pending</option><option value="Verified">Verified</option><option value="Rejected">Rejected</option></select><a class="text-link" href="#employers">View all ↗</a></div></div>
 <div class="table-scroll">
  <table id="employerTable"><thead><tr><th>Company</th><th>Contact</th><th>Date submitted</th><th>Status</th><th>Review decision</th></tr></thead><tbody>
  <?php foreach($employers as $e): ?>
   <tr data-status="<?= htmlspecialchars($e['status'], ENT_QUOTES, 'UTF-8') ?>" data-company="<?= htmlspecialchars(strtolower($e['company'].' '.$e['email'].' '.$e['contact']), ENT_QUOTES, 'UTF-8') ?>">
    <td><div class="company-cell"><div class="company-logo <?= htmlspecialchars($e['color'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($e['initials'], ENT_QUOTES, 'UTF-8') ?></div><div><strong><?= htmlspecialchars($e['company'], ENT_QUOTES, 'UTF-8') ?></strong><small><?= htmlspecialchars($e['email'], ENT_QUOTES, 'UTF-8') ?></small></div></div></td>
    <td><?= htmlspecialchars($e['contact'] !== '' ? $e['contact'] : '—', ENT_QUOTES, 'UTF-8') ?></td>
    <td><?= htmlspecialchars($e['date']) ?></td>
    <td><span class="status <?= htmlspecialchars(strtolower($e['status']), ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($e['status'], ENT_QUOTES, 'UTF-8') ?></span><?php if ($e['notes'] !== ''): ?><small class="review-note">Note: <?= htmlspecialchars($e['notes'], ENT_QUOTES, 'UTF-8') ?></small><?php endif; ?></td>
    <td>
     <form method="post" action="/Dashboards/Admin.php#employers" class="review-form" onsubmit="return confirm('Save this verification decision for <?= htmlspecialchars(addslashes($e['company']), ENT_QUOTES, 'UTF-8') ?>?');">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8') ?>">
      <input type="hidden" name="employer_id" value="<?= (int) $e['id'] ?>">
      <textarea name="review_notes" maxlength="1000" placeholder="Optional review note"><?= htmlspecialchars($e['notes'], ENT_QUOTES, 'UTF-8') ?></textarea>
      <div><button class="action-btn review-btn" type="submit" name="decision" value="approve">Approve</button>
      <button class="action-btn" type="submit" name="decision" value="reject">Reject</button></div>
     </form>
    </td>
   </tr>
  <?php endforeach; ?>
  </tbody></table>
  <div class="empty-state<?= $employers ? '' : ' show' ?>" id="emptyState">No employer accounts match your search.</div>
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
<footer class="footer">© 2026 4Hire · Employer verification decisions are saved to the database.</footer>
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
document.addEventListener('click',e=>{if(!sidebar.contains(e.target)&&!e.target.closest('#menuToggle'))sidebar.classList.remove('open')});
document.querySelectorAll('.quick-action').forEach(b=>b.addEventListener('click',()=>showToast(b.dataset.action+' — connect this action to your backend.')));
document.getElementById('notificationBtn').addEventListener('click',()=>showToast('You have <?= (int) $pendingCount ?> pending employer verification requests.'));
document.getElementById('todayDate').textContent='📅 '+new Date().toLocaleDateString('en-US',{month:'short',day:'2-digit',year:'numeric'});
</script>
</body>
</html>