<?php
/**
 * BiharElection.com - MLC Constituency & Polling Booths Template Renderer
 * Renders dedicated pages for all 8 Teachers' and Graduates' Constituencies
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mlc_helper.php';

if (!isset($constituencyKey) || empty($constituencyKey)) {
    header("Location: " . SITE_URL . "/teachers-constituency");
    exit;
}

$cData = getMlcConstituency($constituencyKey);
if (!$cData) {
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/../404.php';
    exit;
}

$isTeachers = ($cData['type'] === 'teachers');
$typeTitle = $isTeachers ? "Teachers' Constituency" : "Graduates' Constituency";
$typeTitleHi = $isTeachers ? "शिक्षक निर्वाचन क्षेत्र" : "स्नातक निर्वाचन क्षेत्र";
$article = $isTeachers ? 'Article 171(3)(b)' : 'Article 171(3)(a)';
$formType = $isTeachers ? 'Form 19' : 'Form 18';

// SEO Setup
$pageTitle = htmlspecialchars($cData['name']) . " (" . $cData['name_hi'] . "): " . $cData['booth_count'] . " Polling Booths, Voter Roll & 2026 MLC Election Guide";
$pageDescription = "Official list of all " . $cData['booth_count'] . " Polling Booths (मतदान केंद्र) for " . $cData['name'] . " (" . $cData['name_hi'] . "), updated from CEO Bihar Final Electoral Roll. Check booth names, " . $formType . " voter registration, returning officer, and 2026 Biennial Election updates.";
$pageKeywords = $cData['name'] . ", " . $cData['name_hi'] . ", Bihar MLC Election 2026, " . $cData['name'] . " Booth List, CEO Bihar " . $cData['segment_code'] . ", " . $formType . " Bihar, Vidhan Parishad Election 2026, " . implode(', ', $cData['districts']);
$pageCanonical = SITE_URL . '/' . $cData['slug'];
$activeNav = 'mlc';

// All 8 constituencies for inter-linking
$allConstituencies = getMlcAllConstituenciesData();

require_once __DIR__ . '/../header.php';
?>

<style>
/* Modern Dynamic Styling for MLC Constituency Hub */
:root {
    --mlc-grad-start: <?php echo $isTeachers ? '#064e3b' : '#1e1b4b'; ?>;
    --mlc-grad-mid: <?php echo $isTeachers ? '#047857' : '#1d4ed8'; ?>;
    --mlc-grad-end: <?php echo $isTeachers ? '#0f172a' : '#090d16'; ?>;
    --mlc-accent: <?php echo $isTeachers ? '#10b981' : '#3b82f6'; ?>;
    --mlc-accent-light: <?php echo $isTeachers ? '#d1fae5' : '#dbeafe'; ?>;
}

.mlc-hero {
    background: linear-gradient(135deg, var(--mlc-grad-start) 0%, var(--mlc-grad-mid) 50%, var(--mlc-grad-end) 100%);
    color: #ffffff;
    padding: 60px 0 45px;
    position: relative;
    overflow: hidden;
}

.mlc-hero::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.stat-metric-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(11, 25, 44, 0.04);
    padding: 1.25rem 1.5rem;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    height: 100%;
}

.stat-metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(11, 25, 44, 0.08);
}

.district-tag {
    background: #f1f5f9;
    color: #1e293b;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 50px;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.booth-search-box {
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.75rem 1.4rem;
    font-size: 1.05rem;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}

.booth-search-box:focus {
    border-color: var(--mlc-accent);
    outline: none;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}

.booth-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    padding: 1.1rem 1.3rem;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.booth-card:hover {
    border-color: var(--mlc-accent);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}

.part-badge {
    background: var(--mlc-grad-start);
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 8px;
    letter-spacing: 0.3px;
    display: inline-block;
}

.table-booths th {
    background-color: #0b192c !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border: none;
}

.table-booths td {
    padding: 12px 16px;
    vertical-align: middle;
    font-size: 0.95rem;
    border-bottom: 1px solid #e2e8f0;
}

.table-booths tbody tr:hover {
    background-color: #f8fafc;
}

.nav-con-pill {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.nav-con-pill:hover {
    border-color: var(--mlc-accent);
    color: var(--mlc-accent);
    background: #f8fafc;
}

.nav-con-pill.active {
    background: var(--mlc-grad-start);
    color: #ffffff;
    border-color: var(--mlc-grad-start);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@keyframes pulseGlow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.02); }
}
.blink-anim {
    animation: pulseGlow 1.8s infinite ease-in-out;
}
</style>

<!-- Hero Section -->
<header class="mlc-hero">
    <div class="container text-start position-relative">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-white bg-opacity-20 text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                🏛️ Bihar Legislative Council &bull; विधान परिषद
            </span>
            <span class="badge <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                <?php echo $article; ?>
            </span>
            <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill blink-anim shadow-sm">
                <i class="bi bi-broadcast me-1"></i> Biennial Election 2026 &bull; Segment <?php echo $cData['segment_code']; ?>
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-1.5 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/mlc" class="text-white-50 text-decoration-none">MLC Hub</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/<?php echo $isTeachers ? 'teachers-constituency' : 'graduates-constituency'; ?>" class="text-white-50 text-decoration-none"><?php echo $typeTitle; ?></a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($cData['name']); ?></li>
            </ol>
        </nav>

        <h1 class="display-5 fw-extrabold text-white mb-2">
            <?php echo htmlspecialchars($cData['name']); ?> <br>
            <span style="color: #fbbf24;"><?php echo htmlspecialchars($cData['name_hi']); ?></span>
        </h1>
        
        <p class="lead text-white-50 mb-4" style="font-size: 1.15rem; max-width: 920px;">
            <?php echo htmlspecialchars($cData['desc']); ?> 
            Official directory of all <strong><?php echo $cData['booth_count']; ?> Polling Booths (मतदान केंद्र)</strong>, Returning Officer information, <?php echo $formType; ?> voter registration eligibility, and CEO Bihar Final Roll (06.10.2026).
        </p>

        <div class="d-flex flex-wrap gap-2.5">
            <a href="#booths-section" class="btn btn-warning fw-bold text-dark px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-danger"></i> View All <?php echo $cData['booth_count']; ?> Booths
            </a>
            <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning fw-bold px-3.5 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-search"></i> Search Name in Roll
            </a>
            <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="btn btn-primary fw-bold px-3.5 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Apply Online (<?php echo $formType; ?>)
            </a>
            <a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light fw-bold px-3.5 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i> CEO Bihar Roll (PDF)
            </a>
            <a href="#voter-guide" class="btn btn-light bg-opacity-25 text-white fw-semibold px-3 py-2.5 rounded-pill d-inline-flex align-items-center gap-2">
                <i class="bi bi-card-checklist"></i> <?php echo $formType; ?> Guide
            </a>
        </div>
    </div>
</header>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Switch Constituency Quick Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
            <span class="text-muted small fw-bold text-uppercase">
                <i class="bi bi-shuffle text-primary me-1"></i> Switch MLC Constituency (All 8 Active Seats):
            </span>
            <div class="d-flex gap-2">
                <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="extra-small text-decoration-none fw-semibold text-success">
                    <i class="bi bi-book me-1"></i>All Teachers Seats
                </a>
                <span class="text-muted extra-small">&bull;</span>
                <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="extra-small text-decoration-none fw-semibold text-primary">
                    <i class="bi bi-mortarboard me-1"></i>All Graduates Seats
                </a>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($allConstituencies as $slug => $item): 
                $isActive = ($slug === $cData['slug']);
                $icon = ($item['type'] === 'teachers') ? '📚' : '🎓';
            ?>
                <a href="<?php echo SITE_URL . '/' . $slug; ?>" class="nav-con-pill <?php echo $isActive ? 'active' : ''; ?>">
                    <span><?php echo $icon; ?></span>
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="badge bg-light text-dark rounded-pill extra-small ms-1"><?php echo $item['booth_count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Key Statistics Grid -->
    <div class="row g-3 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">Total Polling Booths</span>
                    <span class="badge <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white rounded-pill extra-small">Official Roll</span>
                </div>
                <div class="display-6 fw-extrabold text-navy"><?php echo $cData['booth_count']; ?></div>
                <small class="text-muted">मतदान केंद्र (Parts in Constituency)</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">Divisional HQ</span>
                    <i class="bi bi-building text-info fs-5"></i>
                </div>
                <div class="h4 fw-bold text-navy mb-1"><?php echo htmlspecialchars($cData['hq']); ?></div>
                <small class="text-muted"><?php echo htmlspecialchars($cData['hq_hi']); ?></small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">Voter Enrollment</span>
                    <span class="badge bg-warning text-dark fw-bold rounded-pill extra-small">Statutory</span>
                </div>
                <div class="h4 fw-bold text-navy mb-1"><?php echo $formType; ?> Registration</div>
                <small class="text-muted"><?php echo $isTeachers ? '3 Years Teaching Exp.' : '3+ Yrs Graduate Degree'; ?></small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">CEO Roll Date</span>
                    <i class="bi bi-calendar-check text-success fs-5"></i>
                </div>
                <div class="h4 fw-bold text-navy mb-1"><?php echo $cData['publication_date']; ?></div>
                <small class="text-muted">Final Published Roll</small>
            </div>
        </div>
    </div>

    <!-- Returning Officer & Covered Districts Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 p-4 bg-white">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6 border-end-lg">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Returning Officer (RO) / निर्वाचन पदाधिकारी</span>
                        <h5 class="fw-bold text-navy mb-1"><?php echo htmlspecialchars($cData['ro']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($cData['ro_hi']); ?></p>
                        <?php if (!empty($cData['ro_url'])): ?>
                            <div class="mt-2">
                                <a href="<?php echo htmlspecialchars($cData['ro_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 extra-small">
                                    <i class="bi bi-building"></i>
                                    <span>Divisional Commissioner Portal</span>
                                    <i class="bi bi-box-arrow-up-right ms-0.5" style="font-size: 10px;"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <span class="text-muted extra-small fw-bold text-uppercase d-block mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Covered Districts (<?php echo count($cData['districts']); ?>):
                </span>
                <div class="d-flex flex-wrap gap-1.5">
                    <?php foreach ($cData['districts'] as $idx => $dist): 
                        $distHi = $cData['districts_hi'][$idx] ?? '';
                    ?>
                        <span class="district-tag">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 0.75rem;"></i>
                            <span><?php echo htmlspecialchars($dist); ?></span>
                            <?php if ($distHi): ?>
                                <small class="text-muted">(<?php echo htmlspecialchars($distHi); ?>)</small>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Official CEO Bihar Portal Notice & Instructions -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 overflow-hidden text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e293b 100%);">
        <div class="p-4 p-lg-4.5 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-danger text-white fw-bold px-2.5 py-1 rounded-pill">Chief Electoral Officer, Bihar</span>
                        <span class="badge bg-white bg-opacity-25 text-white px-2.5 py-1 rounded-pill">Final Roll [06.10.2026]</span>
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">Online Portals</span>
                    </div>
                    <h5 class="fw-bold text-white mb-1">Official CEO Bihar Council Portals: Roll PDF, Elector Search &amp; Online Registration</h5>
                    <p class="text-white-50 mb-0 small" style="max-width: 820px;">
                        Check your polling booth below, search your name in the Council Electoral Roll (<a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-warning text-decoration-none fw-semibold">tcgcelectorsearch</a>), apply online via Form <?php echo $isTeachers ? '19' : '18'; ?> (<a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-warning text-decoration-none fw-semibold">tcgconline</a>), or download the certified polling station PDF from CEO Bihar.
                    </p>
                </div>
            </div>
            <div class="d-flex flex-wrap flex-xl-nowrap gap-2 flex-shrink-0">
                <a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-warning fw-bold text-dark rounded-pill px-3.5 py-2 shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                    <span>Roll PDF</span>
                </a>
                <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning fw-bold text-white rounded-pill px-3.5 py-2 shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-search"></i>
                    <span>Search Name</span>
                </a>
                <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="btn btn-primary fw-bold rounded-pill px-3.5 py-2 shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-pencil-square"></i>
                    <span>Apply Online</span>
                </a>
            </div>
        </div>
    </div>

    <!-- INTERACTIVE POLLING BOOTHS DIRECTORY -->
    <section id="booths-section" class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white fw-bold px-2.5 py-1 rounded-pill">
                            Official Directory
                        </span>
                        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small">
                            <span id="boothCountBadge"><?php echo $cData['booth_count']; ?></span> / <?php echo $cData['booth_count']; ?> Booths
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-navy mt-1 mb-0">
                        <?php echo htmlspecialchars($cData['name']); ?> Polling Booths (मतदान केंद्र)
                    </h3>
                    <p class="text-muted small mb-0">
                        Complete part-wise list of official polling stations designated by Election Commission / CEO Bihar
                    </p>
                </div>

                <!-- Print & Share Controls -->
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print List
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" id="copyListBtn" onclick="copyBoothTable()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
            </div>

            <!-- Live Search Bar -->
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-8 col-lg-9">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5"></i>
                        <input type="text" id="boothSearchInput" class="form-control booth-search-box ps-5" placeholder="Search by Booth No, Block, School, College, Anchal (e.g. 1, Bettiah, Maner, High School)..." autocomplete="off" oninput="filterBooths()">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <button type="button" class="btn btn-outline-danger w-100 rounded-pill py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-2" onclick="resetSearch()">
                        <i class="bi bi-x-circle"></i> Clear Filter
                    </button>
                </div>
            </div>

            <!-- Dynamic Search Feedback -->
            <div id="noResultsMsg" class="alert alert-warning rounded-3 text-center py-4 d-none">
                <i class="bi bi-search fs-3 d-block mb-2 text-warning"></i>
                <h6 class="fw-bold mb-1">No polling booths found matching your search.</h6>
                <p class="small text-muted mb-2">Try searching by part number (e.g. 1, 15, 40) or block name.</p>
                <button class="btn btn-sm btn-dark rounded-pill px-3" onclick="resetSearch()">View All <?php echo $cData['booth_count']; ?> Booths</button>
            </div>

            <!-- Responsive Table View -->
            <div class="table-responsive rounded-3 border">
                <table class="table table-booths mb-0" id="boothsTable">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Part No.</th>
                            <th>Polling Station Name &amp; Building (मतदान केंद्र का नाम / भवन)</th>
                            <th style="width: 180px;" class="text-end">Official PDF</th>
                        </tr>
                    </thead>
                    <tbody id="boothsTbody">
                        <?php foreach ($cData['booths'] as $booth): 
                            $partNo = htmlspecialchars($booth['part_no']);
                            $boothName = htmlspecialchars($booth['booth_name']);
                            $fullLabel = htmlspecialchars($booth['full_label']);
                            $rawVal = htmlspecialchars($booth['val']);
                        ?>
                            <tr class="booth-row" data-part="<?php echo strtolower($partNo); ?>" data-name="<?php echo strtolower($fullLabel); ?>">
                                <td>
                                    <span class="part-badge">Part #<?php echo $partNo; ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-navy mb-0.5">
                                        <?php echo $boothName; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i> Part Label: <?php echo $fullLabel; ?>
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold px-3 py-1 shadow-sm d-inline-flex align-items-center gap-1.5" title="View certified PDF on CEO portal">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        <span>CEO Roll</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 text-muted small">
                <span>Source: Chief Electoral Officer, Bihar (Publication Date: 06.10.2026)</span>
                <span>Total: <strong><?php echo $cData['booth_count']; ?> Booths</strong></span>
            </div>
        </div>
    </section>

    <!-- VOTER REGISTRATION & GUIDELINES DOSSIER -->
    <section id="voter-guide" class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill">
                    Constitutional Voter Registration
                </span>
            </div>
            <h3 class="fw-extrabold text-navy mb-3">
                <?php echo $formType; ?> Electoral Enrollment Guide for <?php echo htmlspecialchars($cData['name']); ?>
            </h3>
            <p class="text-muted mb-4" style="max-width: 880px;">
                Electoral rolls for Legislative Council constituencies are compiled de-novo prior to each election. Being registered on the general assembly electoral roll does NOT automatically include you in the Council voter list. Follow the statutory guidelines below:
            </p>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white p-3 fs-5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                1
                            </div>
                            <h5 class="fw-bold text-navy mb-0">Eligibility Criteria</h5>
                        </div>
                        <?php if ($isTeachers): ?>
                            <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                                <li><strong>Ordinary Residence:</strong> Must ordinarily reside within the territorial jurisdiction of <?php echo htmlspecialchars($cData['name']); ?> (<?php echo implode(', ', $cData['districts']); ?>).</li>
                                <li><strong>3-Year Qualifying Experience:</strong> Must have taught for at least <strong>3 years within the preceding 6 years</strong> before the qualifying date in a recognized educational institution.</li>
                                <li><strong>Recognized Institutions:</strong> High Schools, Higher Secondary (+2) Schools, Intermediate Colleges, Degree Colleges, Government/Constituent Universities, and recognized Technical/Polytechnic institutes.</li>
                                <li><strong>Exclusion:</strong> Elementary / Middle school teachers (classes 1 to 8) are excluded under Article 171(3)(b).</li>
                            </ul>
                        <?php else: ?>
                            <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                                <li><strong>Ordinary Residence:</strong> Must ordinarily reside in <?php echo implode(', ', $cData['districts']); ?>.</li>
                                <li><strong>Graduation Vintage:</strong> Must have graduated at least <strong>3 years prior to the qualifying date</strong> from a recognized university in India (or hold equivalent qualification recognized by the state government).</li>
                                <li><strong>Degree Certificates:</strong> Self-attested copy of Degree Certificate or Marksheet must be submitted with Form 18.</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-warning text-dark p-3 fs-5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                2
                            </div>
                            <h5 class="fw-bold text-navy mb-0">Application &amp; Verification Documents</h5>
                        </div>
                        <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                            <li><strong>Online Filing:</strong> Electors can submit <strong><?php echo $formType; ?></strong> online via Bihar CEO's dedicated portal: <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgconline</a> or via ECI's portal: <a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>.</li>
                            <li><strong>Elector Roll Search:</strong> Verify your enrollment and part number using CEO Bihar's Council Elector Search portal: <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgcelectorsearch</a>.</li>
                            <li><strong>Offline Submission:</strong> Physical forms can be submitted to the Electoral Registration Officer (ERO) / Assistant Electoral Registration Officer (AERO) / Block Development Officer (BDO).</li>
                            <?php if ($isTeachers): ?>
                                <li><strong>Certificate of Employment:</strong> Form 19 MUST be accompanied by an official service certificate in the prescribed format, duly signed and stamped by the Headmaster, Principal, Registrar, or Vice-Chancellor of the educational institution.</li>
                            <?php else: ?>
                                <li><strong>Identity &amp; Address Proof:</strong> Aadhaar Card, Voter ID (EPIC), Passport, or Electricity Bill showing residence in the constituency.</li>
                            <?php endif; ?>
                            <li><strong>Preferential Voting System:</strong> Voting takes place via paper ballot under the Single Transferable Vote (STV) system, where electors mark preferences 1, 2, 3... using the official violet sketch pen.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FREQUENTLY ASKED QUESTIONS (FAQ) -->
    <section class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <h3 class="fw-extrabold text-navy mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-question-circle-fill text-primary"></i> Frequently Asked Questions: <?php echo htmlspecialchars($cData['name']); ?>
            </h3>

            <div class="accordion accordion-flush" id="faqAccordion">
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="faq1">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                            How many polling booths are in <?php echo htmlspecialchars($cData['name']); ?>?
                        </button>
                    </h2>
                    <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            There are exactly <strong><?php echo $cData['booth_count']; ?> designated Polling Booths (मतदान केंद्र)</strong> across <?php echo htmlspecialchars($cData['name']); ?>, covering all constituent blocks, subdivisions, and district headquarters in <?php echo implode(', ', $cData['districts']); ?> as published in the Final Electoral Roll dated 06.10.2026.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="faq2">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                            Who is the Returning Officer for <?php echo htmlspecialchars($cData['name']); ?>?
                        </button>
                    </h2>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            The Returning Officer (RO) for this constituency is the <strong><?php echo htmlspecialchars($cData['ro']); ?></strong> located at <?php echo htmlspecialchars($cData['hq']); ?><?php if (!empty($cData['ro_url'])): ?> (Official Portal: <a href="<?php echo htmlspecialchars($cData['ro_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold"><?php echo parse_url($cData['ro_url'], PHP_URL_HOST); ?></a>)<?php endif; ?>. The RO is assisted by AEROs including District Magistrates (DMs), Sub-Divisional Officers (SDOs), and Block Development Officers (BDOs).
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="faq3">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                            How can I download the official voter list PDF for my polling booth?
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            You can download the official certified PDF directly from the CEO Bihar website (<a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold"><?php echo $cData['source_url']; ?></a>). Select Segment Code <strong><?php echo $cData['segment_code']; ?></strong>, select your Part Number from the list above, pick Mother Roll or Supplement, and click View PDF.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq4">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                            Which statutory form is used to register as a voter in this constituency?
                        </button>
                    </h2>
                    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            Electors must submit <strong><?php echo $formType; ?></strong> under the Registration of Electors Rules, 1960. You can submit it online directly via CEO Bihar's portal (<a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgconline</a>) or ECI (<a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>), search your existing enrollment on <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">tcgcelectorsearch</a>, or submit offline to your designated Block Development Officer or AERO.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RELATED NAVIGATION CARDS -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-danger"><i class="bi bi-broadcast"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">2026 Biennial Election Hub</h6>
                        <small class="text-muted">Live Gazette, Schedule &amp; Results</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-success"><i class="bi bi-book-half"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">All 6 Teachers' Seats</h6>
                        <small class="text-muted">Form 19 Guide &amp; Rules</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-primary"><i class="bi bi-mortarboard-fill"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">All 6 Graduates' Seats</h6>
                        <small class="text-muted">Form 18 Guide &amp; Rules</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<!-- Client-side Live Search & Copy Script -->
<script>
function filterBooths() {
    const query = document.getElementById('boothSearchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#boothsTbody .booth-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const part = row.getAttribute('data-part') || '';
        const name = row.getAttribute('data-name') || '';

        if (!query || part.includes(query) || name.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('boothCountBadge');
    if (badge) badge.innerText = visibleCount;

    const noResults = document.getElementById('noResultsMsg');
    if (noResults) {
        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }
}

function resetSearch() {
    const input = document.getElementById('boothSearchInput');
    if (input) {
        input.value = '';
        filterBooths();
        input.focus();
    }
}

function copyBoothTable() {
    const rows = document.querySelectorAll('#boothsTbody .booth-row');
    let text = "<?php echo addslashes($cData['name']); ?> - Polling Booths List\n";
    text += "Source: CEO Bihar Final Roll (06.10.2026)\n\n";
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const part = row.querySelector('.part-badge')?.innerText.trim() || '';
            const name = row.querySelector('.fw-bold')?.innerText.trim() || '';
            text += part + " : " + name + "\n";
        }
    });

    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyListBtn');
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.replace('btn-outline-primary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.replace('btn-success', 'btn-outline-primary');
            }, 2000);
        }
    });
}
</script>

<!-- Structured JSON-LD Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Home",
          "item": "<?php echo SITE_URL; ?>/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "MLC Directory",
          "item": "<?php echo SITE_URL; ?>/mlc"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?php echo $typeTitle; ?>",
          "item": "<?php echo SITE_URL; ?>/<?php echo $isTeachers ? 'teachers-constituency' : 'graduates-constituency'; ?>"
        },
        {
          "@type": "ListItem",
          "position": 4,
          "name": "<?php echo htmlspecialchars($cData['name']); ?>",
          "item": "<?php echo $pageCanonical; ?>"
        }
      ]
    },
    {
      "@type": "GovernmentOrganization",
      "name": "<?php echo htmlspecialchars($cData['name']); ?>",
      "alternateName": "<?php echo htmlspecialchars($cData['name_hi']); ?>",
      "url": "<?php echo $pageCanonical; ?>",
      "areaServed": "Bihar, India",
      "description": "<?php echo htmlspecialchars($cData['desc']); ?>"
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "How many polling booths are in <?php echo htmlspecialchars($cData['name']); ?>?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "There are <?php echo $cData['booth_count']; ?> designated Polling Booths across <?php echo htmlspecialchars($cData['name']); ?> as published in the Final Electoral Roll dated 06.10.2026."
          }
        },
        {
          "@type": "Question",
          "name": "Who is the Returning Officer for <?php echo htmlspecialchars($cData['name']); ?>?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "The Returning Officer is <?php echo htmlspecialchars($cData['ro']); ?>."
          }
        },
        {
          "@type": "Question",
          "name": "Which form is used for voter registration?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Electors must submit <?php echo $formType; ?> under the Registration of Electors Rules."
          }
        }
      ]
    }
  ]
}
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
