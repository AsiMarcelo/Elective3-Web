<?php
// ============================================================
// 4HIRE - EMPLOYER DASHBOARD
// Resume Platform for Employers
// ============================================================

// Sample employer information
$companyName = "ABC Company";
$employerName = "Employer Account";
$verificationStatus = "Verified";

// Dashboard statistics
$totalApplicants = 128;
$totalResumes = 96;
$shortlisted = 24;
$activeJobs = 8;

// Applicant field distribution
$fieldData = [
    "Software Development" => 42,
    "Web Development"      => 28,
    "Networking"           => 18,
    "Other / Unsure"       => 12
];

// Recent applicants
$applicants = [
    [
        "name"     => "Juan Dela Cruz",
        "field"    => "Software Development",
        "position" => "Web Developer",
        "date"     => "Oct 04, 2026"
    ],
    [
        "name"     => "Maria Santos",
        "field"    => "Web Development",
        "position" => "Frontend Developer",
        "date"     => "Oct 03, 2026"
    ],
    [
        "name"     => "Alex Reyes",
        "field"    => "Networking",
        "position" => "Network Engineer",
        "date"     => "Oct 02, 2026"
    ],
    [
        "name"     => "Sofia Garcia",
        "field"    => "Software Development",
        "position" => "Backend Developer",
        "date"     => "Oct 01, 2026"
    ]
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>4Hire | Employer Dashboard</title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>
        /* ======================================================
           COLOR PALETTE (NEW PALETTE APPLIED)
        ====================================================== */
        :root {
            --soft-olive: #bcc590;      /* Active badges, highlights, buttons */
            --light-cream: #f6f4d2b9;     /* Main background */
            --sage: #CBDFBD;            /* Sidebar/Card borders & soft bg accents */
            --terracotta: #F19C79;      /* Primary CTA, notifications, logo accent */

            /* Extended Neutral Tones */
            --dark-text: #2D3A2F;
            --muted-text: #5A695C;
            --white: #FFFFFF;
            --border-color: #DDE7D3;

            --shadow: 0 5px 20px rgba(45, 58, 47, 0.05);
        }

        /* ======================================================
           RESET
        ====================================================== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: "DM Sans", sans-serif;
            background: var(--light-cream);
            color: var(--dark-text);
            min-height: 100vh;
        }

        button, input, select {
            font-family: inherit;
        }

        button {
            cursor: pointer;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        /* ======================================================
           SIDEBAR
        ====================================================== */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 245px;
            height: 100vh;
            background: var(--dark-text);
            display: flex;
            flex-direction: column;
            z-index: 100;
            box-shadow: 4px 0 18px rgba(0, 0, 0, 0.06);
        }

        .logo-container {
            height: 78px;
            display: flex;
            align-items: center;
            padding: 0 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .logo {
            font-family: "Manrope", sans-serif;
            font-size: 24px;
            font-weight: 800;
            color: var(--white);
            letter-spacing: -1px;
        }

        .logo span {
            color: var(--terracotta);
        }

        .logo-subtitle {
            font-size: 9px;
            color: var(--soft-olive);
            margin-top: 2px;
            letter-spacing: .5px;
        }

        .sidebar-content {
            padding: 22px 14px;
            flex: 1;
            overflow-y: auto;
        }

        .menu-title {
            font-size: 9px;
            font-weight: 700;
            color: var(--sage);
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 0 12px;
            margin-bottom: 9px;
        }

        .menu {
            list-style: none;
            margin-bottom: 25px;
        }

        .menu li {
            margin-bottom: 4px;
        }

        .menu-item {
            min-height: 43px;
            padding: 9px 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-radius: 8px;
            color: #E2E8DE;
            font-size: 11px;
            font-weight: 600;
            transition: all .2s ease;
        }

        .menu-item:hover {
            background: rgba(203, 223, 189, 0.15);
            color: var(--white);
            transform: translateX(2px);
        }

        .menu-item.active {
            background: var(--soft-olive);
            color: var(--dark-text);
            box-shadow: 0 5px 12px rgba(0, 0, 0, .08);
        }

        .menu-icon {
            width: 25px;
            height: 25px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 6px;
            background: rgba(255, 255, 255, .10);
            color: #E2E8DE;
            font-size: 11px;
            flex-shrink: 0;
        }

        .menu-item.active .menu-icon {
            background: var(--white);
            color: var(--dark-text);
        }

        .sidebar-user {
            padding: 15px 17px;
            border-top: 1px solid rgba(255, 255, 255, .09);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: var(--terracotta);
            color: var(--white);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 800;
        }

        .user-info {
            min-width: 0;
        }

        .user-info strong {
            display: block;
            color: var(--white);
            font-size: 10px;
        }

        .user-info span {
            display: block;
            color: var(--soft-olive);
            font-size: 8px;
            margin-top: 2px;
        }

        .logout {
            margin-left: auto;
            color: #E2E8DE;
            font-size: 12px;
            cursor: pointer;
        }

        /* ======================================================
           MAIN LAYOUT
        ====================================================== */
        .main {
            margin-left: 245px;
            min-height: 100vh;
            padding: 25px 30px 40px;
        }

        .top-header {
            background: var(--white);
            border: 1px solid var(--sage);
            border-radius: 12px;
            min-height: 82px;
            padding: 17px 23px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
            margin-bottom: 18px;
        }

        .page-title {
            font-family: "Manrope", sans-serif;
            color: var(--dark-text);
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -.6px;
        }

        .page-title span {
            color: var(--terracotta);
        }

        .page-description {
            margin-top: 4px;
            font-size: 10px;
            color: var(--muted-text);
        }

        .company {
            text-align: right;
        }

        .company strong {
            display: block;
            font-size: 11px;
            color: var(--dark-text);
        }

        .company span {
            display: block;
            margin-top: 3px;
            color: var(--muted-text);
            font-size: 8px;
        }

        /* VERIFICATION BAR */
        .verification {
            background: var(--white);
            border: 1px solid var(--sage);
            border-radius: 10px;
            padding: 14px 17px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 18px;
            box-shadow: var(--shadow);
        }

        .verification-left {
            display: flex;
            align-items: center;
            gap: 11px;
        }

        .verification-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: var(--soft-olive);
            color: var(--dark-text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
        }

        .verification h4 {
            font-size: 10px;
            color: var(--dark-text);
            margin-bottom: 3px;
        }

        .verification p {
            font-size: 8px;
            color: var(--muted-text);
        }

        .verified {
            background: var(--soft-olive);
            color: var(--dark-text);
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 8px;
            font-weight: 800;
        }

        /* STAT CARDS */
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }

        .stat-card {
            background: var(--white);
            border: 1px solid var(--sage);
            border-radius: 10px;
            padding: 17px;
            box-shadow: var(--shadow);
            position: relative;
            overflow: hidden;
        }

        .stat-card::after {
            content: "";
            position: absolute;
            right: -25px;
            bottom: -30px;
            width: 80px;
            height: 80px;
            border-radius: 50%;
            opacity: .3;
            background: var(--sage);
        }

        .stat-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .icon-olive { background: rgba(212, 224, 155, 0.4); color: var(--dark-text); }
        .icon-terracotta { background: rgba(241, 156, 121, 0.3); color: var(--terracotta); }
        .icon-sage { background: rgba(203, 223, 189, 0.4); color: var(--dark-text); }

        .stat-label {
            margin-top: 12px;
            color: var(--muted-text);
            font-size: 8px;
            font-weight: 600;
        }

        .stat-number {
            margin-top: 2px;
            font-family: "Manrope", sans-serif;
            font-size: 24px;
            color: var(--dark-text);
            font-weight: 800;
        }

        .stat-change {
            margin-top: 4px;
            font-size: 7px;
            color: var(--terracotta);
            font-weight: 700;
        }

        /* GRID CONTENT */
        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1.7fr) minmax(280px, 1fr);
            gap: 16px;
            margin-bottom: 18px;
        }

        .card {
            background: var(--white);
            border: 1px solid var(--sage);
            border-radius: 11px;
            box-shadow: var(--shadow);
        }

        .card-header {
            padding: 17px 19px 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            font-family: "Manrope", sans-serif;
            font-size: 15px;
            font-weight: 800;
            color: var(--dark-text);
        }

        .card-subtitle {
            font-size: 8px;
            color: var(--muted-text);
            margin-top: 4px;
        }

        /* CHART CARD */
        .chart-card {
            min-height: 330px;
        }

        .chart {
            height: 230px;
            margin: 8px 19px 17px;
            display: flex;
            align-items: flex-end;
            gap: 13px;
            padding: 15px 10px 0;
            border-bottom: 1px solid var(--border-color);
            background: repeating-linear-gradient(
                to bottom,
                transparent 0,
                transparent 44px,
                #F4F8F1 45px
            );
        }

        .chart-column {
            flex: 1;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
        }

        .bar-wrapper {
            width: 100%;
            height: 185px;
            display: flex;
            align-items: flex-end;
            justify-content: center;
        }

        .bar {
            width: 55%;
            max-width: 31px;
            min-height: 15px;
            background: linear-gradient(180deg, var(--sage), var(--soft-olive));
            border-radius: 6px 6px 2px 2px;
            transition: all .25s ease;
        }

        .bar:hover {
            background: var(--terracotta);
            transform: translateY(-3px);
            box-shadow: 0 5px 10px rgba(241, 156, 121, 0.3);
        }

        .month {
            margin-top: 8px;
            font-size: 7px;
            color: var(--muted-text);
        }

        /* FIELDS CARD */
        .fields-card {
            min-height: 330px;
            padding-bottom: 15px;
        }

        .fields {
            padding: 10px 19px;
        }

        .field {
            margin-bottom: 19px;
        }

        .field-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 7px;
        }

        .field-name {
            font-size: 8px;
            font-weight: 600;
            color: var(--dark-text);
        }

        .field-percent {
            font-size: 8px;
            font-weight: 700;
            color: var(--dark-text);
        }

        .progress {
            width: 100%;
            height: 7px;
            background: var(--light-cream);
            border: 1px solid var(--sage);
            border-radius: 20px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            border-radius: 20px;
        }

        .field:nth-child(1) .progress-fill { background: var(--terracotta); }
        .field:nth-child(2) .progress-fill { background: var(--soft-olive); }
        .field:nth-child(3) .progress-fill { background: var(--sage); }
        .field:nth-child(4) .progress-fill { background: var(--dark-text); }

        .ai-note {
            margin: 5px 19px 0;
            padding: 10px;
            background: rgba(203, 223, 189, 0.25);
            border-left: 3px solid var(--terracotta);
            border-radius: 5px;
            font-size: 7px;
            line-height: 1.5;
            color: var(--muted-text);
        }

        .ai-note strong {
            color: var(--dark-text);
        }

        /* RECENT APPLICANTS TABLE */
        .recent-card {
            overflow: hidden;
        }

        .recent-header {
            padding: 18px 20px;
            border-bottom: 1px solid var(--sage);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .view-all {
            font-size: 8px;
            font-weight: 700;
            color: var(--dark-text);
            padding: 6px 10px;
            border-radius: 6px;
            background: var(--soft-olive);
            transition: .2s;
        }

        .view-all:hover {
            background: var(--terracotta);
            color: var(--white);
        }

        .table-header {
            display: grid;
            grid-template-columns: 1.3fr 1.1fr 1.1fr .8fr 90px;
            gap: 10px;
            padding: 11px 20px;
            background: var(--light-cream);
            border-bottom: 1px solid var(--sage);
        }

        .table-header span {
            font-size: 7px;
            font-weight: 800;
            text-transform: uppercase;
            color: var(--muted-text);
            letter-spacing: .5px;
        }

        .applicant-row {
            display: grid;
            grid-template-columns: 1.3fr 1.1fr 1.1fr .8fr 90px;
            gap: 10px;
            align-items: center;
            padding: 14px 20px;
            border-bottom: 1px solid var(--border-color);
            transition: .2s;
        }

        .applicant-row:hover {
            background: rgba(246, 244, 210, 0.4);
        }

        .applicant-row:last-child {
            border-bottom: none;
        }

        .applicant-name {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 9px;
            color: var(--dark-text);
            font-weight: 700;
        }

        .mini-avatar {
            width: 27px;
            height: 27px;
            border-radius: 7px;
            background: var(--sage);
            color: var(--dark-text);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8px;
            font-weight: 800;
        }

        .applicant-field {
            font-size: 7px;
            color: var(--dark-text);
        }

        .position {
            font-size: 8px;
            color: var(--muted-text);
            font-weight: 600;
        }

        .date {
            font-size: 7px;
            color: var(--muted-text);
        }

        .action-btn {
            border: 1px solid var(--terracotta);
            background: var(--white);
            color: var(--terracotta);
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 7px;
            font-weight: 700;
            transition: .2s;
        }

        .action-btn:hover {
            background: var(--terracotta);
            color: var(--white);
        }

        /* QUICK ACTIONS */
        .quick-actions {
            margin-top: 18px;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 14px;
        }

        .quick-action {
            background: var(--white);
            border: 1px solid var(--sage);
            border-radius: 10px;
            padding: 15px;
            display: flex;
            align-items: center;
            gap: 11px;
            box-shadow: var(--shadow);
            cursor: pointer;
            transition: .2s;
        }

        .quick-action:hover {
            transform: translateY(-2px);
            border-color: var(--terracotta);
        }

        .quick-icon {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
        }

        .quick-action:nth-child(1) .quick-icon { background: var(--terracotta); color: var(--white); }
        .quick-action:nth-child(2) .quick-icon { background: var(--soft-olive); color: var(--dark-text); }
        .quick-action:nth-child(3) .quick-icon { background: var(--sage); color: var(--dark-text); }

        .quick-action strong {
            display: block;
            font-size: 9px;
            color: var(--dark-text);
        }

        .quick-action span {
            display: block;
            font-size: 7px;
            color: var(--muted-text);
            margin-top: 3px;
        }

        /* ======================================================
           RESPONSIVE BREAKPOINTS
        ====================================================== */
        @media (max-width: 1100px) {
            .sidebar { width: 210px; }
            .main { margin-left: 210px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
            .content-grid { grid-template-columns: 1fr; }
            .table-header, .applicant-row { grid-template-columns: 1.4fr 1fr 1fr 80px; }
            .table-header span:nth-child(4), .applicant-row .date { display: none; }
        }

        @media (max-width: 750px) {
            .sidebar { width: 65px; }
            .logo-container { padding: 0; justify-content: center; }
            .logo { font-size: 0; }
            .logo::after { content: "4"; font-size: 23px; color: var(--terracotta); }
            .logo-subtitle, .menu-title, .menu-item span, .user-info, .logout { display: none; }
            .sidebar-content { padding: 18px 8px; }
            .menu-item { justify-content: center; padding: 9px; }
            .menu-icon { margin: 0; }
            .sidebar-user { justify-content: center; padding: 13px 8px; }
            .main { margin-left: 65px; padding: 18px; }
            .top-header { padding: 15px; }
            .page-title { font-size: 18px; }
            .company { display: none; }
            .stats { grid-template-columns: 1fr 1fr; }
            .quick-actions { grid-template-columns: 1fr; }
            .table-header { display: none; }
            .applicant-row { grid-template-columns: 1fr auto; gap: 5px 10px; padding: 14px; }
            .applicant-field, .position { display: none; }
            .date { text-align: right; }
        }

        @media (max-width: 480px) {
            .stats { grid-template-columns: 1fr; }
            .verification { align-items: flex-start; gap: 10px; }
            .chart { gap: 5px; }
        }
    </style>
</head>

<body>

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="logo-container">
            <div>
                <div class="logo"><span>4</span>Hire</div>
                <div class="logo-subtitle">EMPLOYER PLATFORM</div>
            </div>
        </div>

        <div class="sidebar-content">
            <div class="menu-title">Main Menu</div>
            <ul class="menu">
                <li>
                    <a href="#" class="menu-item active">
                        <div class="menu-icon"><i class="fa-solid fa-chart-pie"></i></div>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-users"></i></div>
                        <span>Applicants</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-briefcase"></i></div>
                        <span>Job Posts</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-file-lines"></i></div>
                        <span>Resumes</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                        <span>Search Applicants</span>
                    </a>
                </li>
            </ul>

            <div class="menu-title">Account</div>
            <ul class="menu">
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-building"></i></div>
                        <span>Company Profile</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-shield-halved"></i></div>
                        <span>Verification</span>
                    </a>
                </li>
                <li>
                    <a href="#" class="menu-item">
                        <div class="menu-icon"><i class="fa-solid fa-gear"></i></div>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
        </div>

        <div class="sidebar-user">
            <div class="user-avatar">AB</div>
            <div class="user-info">
                <strong><?php echo htmlspecialchars($companyName); ?></strong>
                <span>Verified Employer</span>
            </div>
            <div class="logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </div>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="main">

        <!-- HEADER -->
        <header class="top-header">
            <div>
                <h1 class="page-title">4Hire <span>Employer Dashboard</span></h1>
                <p class="page-description">Manage applicants, resumes, and job opportunities.</p>
            </div>
            <div class="company">
                <strong><?php echo htmlspecialchars($companyName); ?></strong>
                <span><?php echo htmlspecialchars($employerName); ?></span>
            </div>
        </header>

        <!-- VERIFICATION -->
        <section class="verification">
            <div class="verification-left">
                <div class="verification-icon">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div>
                    <h4>Employer Verification Status</h4>
                    <p>Your account is fully verified to post jobs and contact applicants.</p>
                </div>
            </div>
            <span class="verified"><?php echo htmlspecialchars($verificationStatus); ?></span>
        </section>

        <!-- STAT CARDS -->
        <section class="stats">
            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon icon-olive"><i class="fa-solid fa-users"></i></div>
                </div>
                <div class="stat-label">TOTAL APPLICANTS</div>
                <div class="stat-number"><?php echo $totalApplicants; ?></div>
                <div class="stat-change">+12% from last month</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon icon-terracotta"><i class="fa-solid fa-file-invoice"></i></div>
                </div>
                <div class="stat-label">AVAILABLE RESUMES</div>
                <div class="stat-number"><?php echo $totalResumes; ?></div>
                <div class="stat-change">+8% new submissions</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon icon-sage"><i class="fa-solid fa-user-check"></i></div>
                </div>
                <div class="stat-label">SHORTLISTED</div>
                <div class="stat-number"><?php echo $shortlisted; ?></div>
                <div class="stat-change">Ready for interview</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <div class="stat-icon icon-olive"><i class="fa-solid fa-briefcase"></i></div>
                </div>
                <div class="stat-label">ACTIVE JOBS</div>
                <div class="stat-number"><?php echo $activeJobs; ?></div>
                <div class="stat-change">Currently hiring</div>
            </div>
        </section>

        <!-- CONTENT GRID -->
        <div class="content-grid">
            
            <!-- APPLICANT ACTIVITY CHART -->
            <div class="card chart-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Applicant Activity</h2>
                        <p class="card-subtitle">Monthly resume submissions overview</p>
                    </div>
                </div>
                <div class="chart">
                    <div class="chart-column">
                        <div class="bar-wrapper"><div class="bar" style="height: 40%;"></div></div>
                        <span class="month">May</span>
                    </div>
                    <div class="chart-column">
                        <div class="bar-wrapper"><div class="bar" style="height: 55%;"></div></div>
                        <span class="month">Jun</span>
                    </div>
                    <div class="chart-column">
                        <div class="bar-wrapper"><div class="bar" style="height: 70%;"></div></div>
                        <span class="month">Jul</span>
                    </div>
                    <div class="chart-column">
                        <div class="bar-wrapper"><div class="bar" style="height: 60%;"></div></div>
                        <span class="month">Aug</span>
                    </div>
                    <div class="chart-column">
                        <div class="bar-wrapper"><div class="bar" style="height: 85%;"></div></div>
                        <span class="month">Sep</span>
                    </div>
                    <div class="chart-column">
                        <div class="bar-wrapper"><div class="bar" style="height: 100%;"></div></div>
                        <span class="month">Oct</span>
                    </div>
                </div>
            </div>

            <!-- APPLICANT FIELDS -->
            <div class="card fields-card">
                <div class="card-header">
                    <div>
                        <h2 class="card-title">Top Fields</h2>
                        <p class="card-subtitle">Distribution by expertise</p>
                    </div>
                </div>
                <div class="fields">
                    <?php foreach ($fieldData as $field => $percentage): ?>
                        <div class="field">
                            <div class="field-info">
                                <span class="field-name"><?php echo $field; ?></span>
                                <span class="field-percent"><?php echo $percentage; ?>%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="ai-note">
                    <strong>System Note:</strong> Most applicants in your pool specialize in Software and Web Development.
                </div>
            </div>

        </div>

        <!-- RECENT APPLICANTS -->
        <div class="card recent-card">
            <div class="recent-header">
                <div>
                    <h2 class="card-title">Recent Applicants</h2>
                    <p class="card-subtitle">Latest resume submissions</p>
                </div>
                <a href="#" class="view-all">View All</a>
            </div>

            <div class="table-header">
                <span>Applicant</span>
                <span>Field</span>
                <span>Position</span>
                <span>Date</span>
                <span>Action</span>
            </div>

            <?php foreach ($applicants as $applicant): ?>
                <div class="applicant-row">
                    <div class="applicant-name">
                        <div class="mini-avatar">
                            <?php 
                                $words = explode(' ', $applicant['name']);
                                echo strtoupper(substr($words[0], 0, 1) . substr(end($words), 0, 1));
                            ?>
                        </div>
                        <span><?php echo htmlspecialchars($applicant['name']); ?></span>
                    </div>
                    <div class="applicant-field"><?php echo htmlspecialchars($applicant['field']); ?></div>
                    <div class="position"><?php echo htmlspecialchars($applicant['position']); ?></div>
                    <div class="date"><?php echo htmlspecialchars($applicant['date']); ?></div>
                    <div>
                        <button class="action-btn">View Profile</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- QUICK ACTIONS -->
        <div class="quick-actions">
            <div class="quick-action">
                <div class="quick-icon"><i class="fa-solid fa-plus"></i></div>
                <div>
                    <strong>Post New Job</strong>
                    <span>Create a new job listing</span>
                </div>
            </div>
            <div class="quick-action">
                <div class="quick-icon"><i class="fa-solid fa-magnifying-glass"></i></div>
                <div>
                    <strong>Search Resumes</strong>
                    <span>Filter candidate profiles</span>
                </div>
            </div>
            <div class="quick-action">
                <div class="quick-icon"><i class="fa-solid fa-sliders"></i></div>
                <div>
                    <strong>Manage Jobs</strong>
                    <span>Edit existing listings</span>
                </div>
            </div>
        </div>

    </main>

</body>

</html>
