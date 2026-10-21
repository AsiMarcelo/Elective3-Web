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

$profilePicPath = null;
try {
    // Handle Profile Picture Update Action for Employer
    if (isset($_POST['action']) && $_POST['action'] === 'update_profile_pic') {
        $csrfSecret = is_string($_COOKIE['user_sig'] ?? null) ? $_COOKIE['user_sig'] : '';
        if (!validSignedCsrfToken($_POST['csrf_token'] ?? null, $csrfSecret)) {
            http_response_code(400);
            exit('Invalid request. Refresh the page and try again.');
        }

        if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
            $fileTmp = $_FILES['profile_pic']['tmp_name'];
            $fileName = basename($_FILES['profile_pic']['name']);
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            if (in_array($fileExt, ['jpg', 'jpeg', 'png', 'webp'])) {
                $uploadSubDir = '/uploads/avatars/';
                $targetDir = __DIR__ . '/../../public' . $uploadSubDir;
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0755, true);
                }
                $newFileName = 'employer_' . $user_id . '_' . time() . '.' . $fileExt;
                $destinationPath = $targetDir . $newFileName;
                $dbPath = $uploadSubDir . $newFileName;
                
                if (move_uploaded_file($fileTmp, $destinationPath)) {
                    $updateStmt = $pdo->prepare('UPDATE public.users SET profile_picture_path = :path WHERE id = :id');
                    $updateStmt->execute(['path' => $dbPath, 'id' => (int)$user_id]);
                    header('Location: /Dashboards/Employer.php?profile=updated');
                    exit();
                }
            }
        }
    }

    $stmt = $pdo->prepare('SELECT e.company_name, e.verification_status, u.profile_picture_path FROM public.employers e JOIN public.users u ON u.id = e.user_id WHERE e.user_id = :user_id LIMIT 1');
    $stmt->execute(['user_id' => (int)$user_id]);
    $employerRecord = $stmt->fetch();
} catch (Throwable $e) {
    $employerRecord = false;
}

$companyName = $employerRecord['company_name'] ?? ($_COOKIE['user_name'] ?? 'Employer Company');
$employerName = $_COOKIE['user_name'] ?? 'Employer Account';
$verificationStatus = ucfirst($employerRecord['verification_status'] ?? 'Verified');
$profilePicPath = $employerRecord['profile_picture_path'] ?? null;

// Load classifications config for filtering
$classificationsConfig = include __DIR__ . '/../../includes/config/classifications.php';
$allFields = $classificationsConfig['list'] ?? [];

// Fetch live list of all applicant profiles with resumes from the database
$applicantsList = [];
try {
    $applicantStmt = $pdo->query(
        "SELECT u.full_name, a.resume_original_name, a.resume_storage_path, a.ai_suggested_fields, a.resume_keywords, a.resume_uploaded_at
         FROM public.applicants a
         JOIN public.users u ON u.id = a.user_id
         WHERE a.resume_uploaded_at IS NOT NULL
         ORDER BY a.resume_uploaded_at DESC"
    );
    $applicantsList = $applicantStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
    $applicantsList = [];
}

function h($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function initials($name){ $w = preg_split('/\s+/', trim($name)); return strtoupper(substr($w[0],0,1) . (count($w)>1 ? substr(end($w),0,1) : '')); }

$csrfSecret = is_string($_COOKIE['user_sig'] ?? null) ? $_COOKIE['user_sig'] : '';
$csrf = signedCsrfToken($csrfSecret);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>4Hire | Employer Dashboard</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="/css/dashboard.css">
<style>
  /* Reduce sidebar width by 12px (252px - 12px = 240px) */
  :root {
    --sidebar-width: 240px;
  }
</style>
</head>
<body>

<aside class="sidebar">
 <div class="sidebar-content" style="padding-top: 20px;">
  <div class="menu-title">Main Menu</div>
  <ul class="menu">
   <li><a href="#" class="menu-item active" aria-current="page" title="Dashboard" data-section="Dashboard"><i class="menu-icon fa-solid fa-chart-pie"></i><span>Dashboard</span></a></li>
   <li><a href="#" class="menu-item" title="Applicants" data-section="Applicants"><i class="menu-icon fa-solid fa-users"></i><span>Applicants</span></a></li>
  </ul>
  <div class="menu-title">Account</div>
  <ul class="menu">
   <li><a href="#" class="menu-item" title="Company Profile" data-section="Company Profile"><i class="menu-icon fa-solid fa-building"></i><span>Company Profile</span></a></li>
   
   <!-- Employer Verification Widget with verified circle to the left of the text -->
   <div style="padding: 12px 14px; margin: 8px 8px; background: rgba(0,0,0,0.25); border-radius: 10px; border: 1px solid var(--border-maroon); font-size: 12px;">
    <div style="color: var(--text-muted); margin-bottom: 8px; line-height: 1.4;">Your account is fully verified to browse applicant profiles and contact candidates.</div>
    <div style="display: flex; align-items: center; gap: 6px; color: #32CD32; font-weight: 600;">
     <div style="width: 8px; height: 8px; border-radius: 50%; background: #32CD32; flex-shrink: 0;"></div>
     <span><?= h($verificationStatus) ?></span>
    </div>
   </div>

   <li><a href="#" class="menu-item" title="Settings" data-section="Settings"><i class="menu-icon fa-solid fa-gear"></i><span>Settings</span></a></li>
  </ul>
 </div>
 <div class="sidebar-user" style="display: flex; justify-content: center; padding: 16px;">
  <a class="logout" href="/Auth/logout.php" aria-label="Log out" style="width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; padding: 10px; background: rgba(255,255,255,0.05); border-radius: 8px; color: var(--text-main); text-decoration: none;"><i class="fa-solid fa-right-from-bracket"></i> <span>Log out</span></a>
 </div>
</aside>

<main class="main">
 <!-- 90px Top Banner with 4Hire logo placeholder -->
 <div style="background-color: var(--border-maroon, #800000); height: 90px; display: flex; align-items: center; justify-content: center; padding: 0 24px; border-radius: 12px; margin-bottom: 24px; color: #fff; font-size: 28px; font-weight: 700; letter-spacing: -0.5px; box-shadow: var(--shadow);">
  <span>4Hire logo</span>
 </div>

 <header class="top-header">
  <div>
   <h1 class="page-title">Employer Dashboard</h1>
   <p class="page-description">Browse and filter live candidate profiles and resumes.</p>
  </div>
  <div style="display: flex; align-items: center; gap: 12px;">
   <!-- Profile Picture beside user name -->
   <div onclick="openProfileModal()" style="cursor: pointer; width: 40px; height: 40px; border-radius: 50%; background: var(--border-maroon); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 2px solid var(--accent-gold);" title="Click to update profile picture">
    <?php if ($profilePicPath): ?>
     <img src="<?= h($profilePicPath) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover;">
    <?php else: ?>
     <span style="font-weight: 600; font-size: 13px; color: var(--text-light);"><?= h(initials($companyName)) ?></span>
    <?php endif; ?>
   </div>
   <div>
    <strong style="font-size: 13px; display: block; line-height: 1.2; color: var(--text-main);"><?= h($companyName) ?></strong>
    <span style="font-size: 11px; color: #32CD32; display: inline-flex; align-items: center; gap: 4px;">
     <span style="width: 6px; height: 6px; border-radius: 50%; background: #32CD32; display: inline-block;"></span> <?= h($verificationStatus) ?>
    </span>
   </div>
  </div>
 </header>

 <!-- Resume Classification Filter Section -->
 <section class="card" style="margin-bottom: 24px;">
  <div class="card-header">
   <h2 class="card-title">Filter Resumes by Classification</h2>
   <p class="card-subtitle">Select a job category to instantly filter candidate profile cards.</p>
  </div>
  <div class="card-body">
   <div class="chips" id="filterChips" role="group" aria-label="Job classification filters">
    <button type="button" class="chip active-filter" data-filter="all" aria-pressed="true">All Classifications</button>
    <?php foreach($allFields as $f): ?>
     <button type="button" class="chip" data-filter="<?= h($f) ?>" aria-pressed="false"><?= h($f) ?></button>
    <?php endforeach; ?>
   </div>
  </div>
 </section>

 <!-- Scrollable Multi-Resume Container -->
 <section class="card">
  <div class="card-header">
   <h2 class="card-title">Live Candidate Resumes</h2>
   <p class="card-subtitle">Scroll horizontally across active applicant profiles retrieved from the database.</p>
  </div>
  <div class="card-body">
   <?php if (!empty($applicantsList)): ?>
    <div id="candidatesScrollContainer" style="display: flex; gap: 20px; overflow-x: auto; padding-bottom: 12px; scroll-snap-type: x mandatory;">
     <?php foreach ($applicantsList as $applicant): ?>
      <?php 
          $fieldsArr = json_decode($applicant['ai_suggested_fields'] ?? '[]', true) ?: [];
          $keywordsArr = json_decode($applicant['resume_keywords'] ?? '[]', true) ?: [];
          $encodedFieldsAttr = htmlspecialchars(json_encode($fieldsArr), ENT_QUOTES, 'UTF-8');
      ?>
      <article class="candidate-card card" data-fields="<?= $encodedFieldsAttr ?>" style="min-width: 340px; max-width: 380px; flex: 0 0 auto; scroll-snap-align: start; margin-bottom: 0; background: var(--bg-card-subtle, var(--card-hover)); border: 1px solid var(--border-maroon);">
       <div style="padding: 20px;">
        <div class="file-row" style="margin-bottom: 14px;">
         <div class="file-icon"><i class="fa-solid fa-user"></i></div>
         <div class="file-info">
          <strong><?= h($applicant['full_name']) ?></strong>
          <small>Uploaded <?= h(date('M d, Y', strtotime($applicant['resume_uploaded_at']))) ?></small>
         </div>
         <span class="status" style="color: #32CD32; border-color: #32CD32;">Verified</span>
        </div>

        <div style="margin-bottom: 14px;">
         <strong style="font-size: 13px; color: var(--text-muted);">Classifications:</strong>
         <div class="chips" style="margin-top: 6px;">
          <?php if (!empty($fieldsArr)): ?>
           <?php foreach($fieldsArr as $field): ?>
            <span class="chip" aria-pressed="true" style="font-size: 11px; padding: 2px 8px;"><?= h($field) ?></span>
           <?php endforeach; ?>
          <?php else: ?>
           <span class="hint">None assigned</span>
          <?php endif; ?>
         </div>
        </div>

        <div style="margin-bottom: 16px;">
         <strong style="font-size: 13px; color: var(--text-muted);">Extracted Keywords:</strong>
         <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 6px; max-height: 90px; overflow-y: auto;">
          <?php if (!empty($keywordsArr)): ?>
           <?php foreach($keywordsArr as $kw): ?>
            <span style="background: var(--bg-black); border: 1px solid var(--border-maroon); padding: 2px 6px; border-radius: 4px; font-size: 11px; color: var(--accent-gold);"><?= h($kw) ?></span>
           <?php endforeach; ?>
          <?php else: ?>
           <span class="hint">No keywords extracted.</span>
          <?php endif; ?>
         </div>
        </div>

        <?php if (!empty($applicant['resume_original_name'])): ?>
        <div class="btn-row" style="margin-top: auto;">
         <a class="btn outline" href="/Auth/Resume.php?action=download" target="_blank" style="width: 100%; justify-content: center;"><i class="fa-solid fa-download"></i>Download Resume</a>
        </div>
        <?php endif; ?>
       </div>
      </article>
     <?php endforeach; ?>
    </div>
    <p id="noResultsMsg" style="display: none; text-align: center; color: var(--text-muted); padding: 20px;">No candidates match the selected classification filter.</p>
   <?php else: ?>
    <p class="empty-note">No live applicant profiles or resumes are currently available in the database.</p>
   <?php endif; ?>
  </div>
 </section>

</main>

<!-- Profile Picture Modal Popup -->
<div id="profileModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center;">
 <div style="background: var(--card-maroon); border: 1px solid var(--border-maroon); border-radius: 16px; padding: 24px; width: 360px; max-width: 90%; position: relative; box-shadow: var(--shadow);">
  <button onclick="closeProfileModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; color: var(--text-light); font-size: 18px; cursor: pointer;" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  <h3 style="font-size: 18px; font-weight: 600; margin-bottom: 16px; color: var(--text-light);">Company Profile Picture</h3>
  
  <div style="display: flex; flex-direction: column; align-items: center; gap: 16px; margin-bottom: 20px;">
   <div style="width: 120px; height: 120px; border-radius: 50%; background: var(--border-maroon); display: flex; align-items: center; justify-content: center; overflow: hidden; border: 3px solid var(--accent-gold);">
    <?php if ($profilePicPath): ?>
     <img src="<?= h($profilePicPath) ?>" alt="Profile Preview" style="width: 100%; height: 100%; object-fit: cover;">
    <?php else: ?>
     <span style="font-size: 32px; font-weight: 700; color: var(--text-light);"><?= h(initials($companyName)) ?></span>
    <?php endif; ?>
   </div>
   <span style="font-size: 13px; color: var(--text-muted); text-align: center;"><?= h($companyName) ?> (User ID: <?= (int)$user_id ?>)</span>
  </div>

  <form method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 12px;">
   <input type="hidden" name="csrf_token" value="<?= h($csrf) ?>">
   <input type="hidden" name="action" value="update_profile_pic">
   <label style="font-size: 13px; font-weight: 500; color: var(--text-muted);">Select new image (JPG, PNG, WebP):</label>
   <input type="file" name="profile_pic" accept="image/png, image/jpeg, image/webp" required style="font-size: 12px; color: var(--text-light);">
   <button type="submit" class="btn" style="width: 100%; justify-content: center; margin-top: 8px;"><i class="fa-solid fa-upload"></i> Upload & Change Picture</button>
  </form>
 </div>
</div>

<div class="toast" id="toast" role="status" aria-live="polite"></div>

<script>
const toast=document.getElementById('toast');let t;
function showToast(m){toast.textContent=m;toast.classList.add('show');clearTimeout(t);t=setTimeout(()=>toast.classList.remove('show'),2800)}

function openProfileModal() {
    document.getElementById('profileModal').style.display = 'flex';
}
function closeProfileModal() {
    document.getElementById('profileModal').style.display = 'none';
}

document.querySelectorAll('.menu-item').forEach(l=>l.addEventListener('click',e=>{e.preventDefault();document.querySelectorAll('.menu-item').forEach(a=>{a.classList.remove('active');a.removeAttribute('aria-current')});l.classList.add('active');l.setAttribute('aria-current','page');if(l.dataset.section!=='Dashboard')showToast(l.dataset.section+' selected — connect this menu to its page or route.')}));

// Interactive Classification Filtering Logic
const filterButtons = document.querySelectorAll('#filterChips .chip');
const candidateCards = document.querySelectorAll('.candidate-card');
const noResultsMsg = document.getElementById('noResultsMsg');

filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        filterButtons.forEach(b => {
            b.classList.remove('active-filter');
            b.setAttribute('aria-pressed', 'false');
        });
        btn.classList.add('active-filter');
        btn.setAttribute('aria-pressed', 'true');

        const selectedFilter = btn.getAttribute('data-filter');
        let visibleCount = 0;

        candidateCards.forEach(card => {
            const cardFields = JSON.parse(card.getAttribute('data-fields') || '[]');
            if (selectedFilter === 'all' || cardFields.includes(selectedFilter)) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResultsMsg) {
            noResultsMsg.style.display = visibleCount === 0 ? 'block' : 'none';
        }
    });
});
</script>
</body>
</html>