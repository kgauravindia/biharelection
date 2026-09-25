<?php
/**
 * BiharElection.com - 2022 Bihar Caste-Based Survey (बिहार जाति आधारित गणना)
 * Complete 215+ Caste Code Directory & Demographic Intelligence Hub
 * Source: General Administration Department (GAD), Govt. of Bihar & Prabhat Khabar
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/caste_data.php';

$summary = CasteDataProvider::getSummary();
$castes = CasteDataProvider::getAllCastes();
$socioEconomic = CasteDataProvider::getSocioEconomicData();
$reservationEvolution = CasteDataProvider::getReservationEvolution();
$surveyTimeline = CasteDataProvider::getTimeline();
$govtLinks = CasteDataProvider::getGovtLinks();

// Dynamic Category & Religion Counts
$categoryCounts = [
    'ALL' => count($castes),
    'EBC' => 0,
    'BC' => 0,
    'SC' => 0,
    'ST' => 0,
    'GEN' => 0,
    'OTHER' => 0,
];
$religionCounts = [
    'ALL' => count($castes),
    'HINDU' => 0,
    'MUSLIM' => 0,
    'CHRISTIAN' => 0,
    'SIKH' => 0,
    'JAIN' => 0,
    'BUDDHIST' => 0,
    'OTHER' => 0,
];

foreach ($castes as $c) {
    $cat = strtoupper($c['category'] ?? 'OTHER');
    if (isset($categoryCounts[$cat])) {
        $categoryCounts[$cat]++;
    } else {
        $categoryCounts['OTHER']++;
    }

    $rel = strtoupper($c['religion'] ?? 'OTHER');
    if (strpos($rel, 'HINDU') !== false) {
        $religionCounts['HINDU']++;
    } elseif (strpos($rel, 'MUSLIM') !== false || strpos($rel, 'ISLAM') !== false) {
        $religionCounts['MUSLIM']++;
    } elseif (strpos($rel, 'CHRISTIAN') !== false) {
        $religionCounts['CHRISTIAN']++;
    } elseif (strpos($rel, 'SIKH') !== false) {
        $religionCounts['SIKH']++;
    } elseif (strpos($rel, 'JAIN') !== false) {
        $religionCounts['JAIN']++;
    } elseif (strpos($rel, 'BUDDHIST') !== false) {
        $religionCounts['BUDDHIST']++;
    } else {
        $religionCounts['OTHER']++;
    }
}

// Query param handling for direct search / filter / sort
$selectedCode = isset($_GET['code']) ? intval($_GET['code']) : null;
$selectedCategory = isset($_GET['category']) ? strtoupper(trim($_GET['category'])) : 'ALL';
$selectedReligion = isset($_GET['religion']) ? strtoupper(trim($_GET['religion'])) : 'ALL';
$selectedSort = isset($_GET['sort']) ? trim($_GET['sort']) : 'code_asc';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : (isset($_GET['caste']) ? trim($_GET['caste']) : '');

$pageTitle = '2022 Bihar Caste-Based Survey: Complete 215+ Caste Code List & Demographics (बिहार जाति कोड)';
$pageDescription = 'Official Bihar Caste-Based Survey (जाति आधारित गणना) complete 215+ Caste Codes list with category classification (EBC, BC, SC, ST, General), population percentage, sub-castes, socio-economic profile, and Patna High Court ruling status.';
$pageKeywords = 'Bihar Caste Code, Bihar Caste Survey 2022, Bihar Jati Code List, Bihar Jatigat Janganana, EBC BC SC ST Caste Code Bihar, Yadav Caste Code, Brahmin Caste Code, Rajput Caste Code, Kushwaha Caste Code, Bania Caste Code Bihar, Prabhat Khabar Caste Code, Patna High Court Caste Reservation';
$pageCanonical = getCasteSurveyUrl();
$activeNav = 'census';

require_once __DIR__ . '/header.php';
?>

<style>
/* Modern Styling for Bihar Caste Survey Intelligence Hub */
:root {
    --brand-navy: #0b192c;
    --brand-blue: #1e3a8a;
    --brand-amber: #f59e0b;
    --brand-emerald: #10b981;
    --brand-ruby: #ef4444;
    --brand-purple: #8b5cf6;
    --brand-slate: #0f172a;
}

.caste-hero {
    background: radial-gradient(circle at 10% 20%, rgba(30, 58, 138, 0.9) 0%, rgba(11, 25, 44, 1) 90%);
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.caste-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at 85% 15%, rgba(245, 158, 11, 0.18), transparent 45%),
                radial-gradient(circle at 15% 85%, rgba(16, 185, 129, 0.15), transparent 45%);
    pointer-events: none;
}

/* Glassmorphic KPI Cards */
.hero-kpi-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(255, 255, 255, 0.14);
    border-radius: 14px;
    padding: 16px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.hero-kpi-card:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.12);
    border-color: rgba(245, 158, 11, 0.5);
    box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.35);
}

/* Sticky Section Navigation Bar - Mobile Responsive & Touch-Optimized */
.caste-sticky-nav {
    position: sticky;
    top: 64px;
    z-index: 1020;
    background: rgba(255, 255, 255, 0.97);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid #e2e8f0;
    box-shadow: 0 4px 15px -3px rgba(0, 0, 0, 0.07);
    transition: top 0.2s ease;
}
@media (max-width: 991.98px) {
    .caste-sticky-nav {
        top: 56px;
        padding-top: 6px !important;
        padding-bottom: 6px !important;
    }
}
.caste-nav-wrapper {
    position: relative;
    width: 100%;
}
.caste-nav-pills {
    display: flex;
    align-items: center;
    gap: 8px;
    overflow-x: auto;
    overflow-y: hidden;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none; /* IE/Edge */
    scroll-behavior: smooth;
    padding: 4px 2px;
}
.caste-nav-pills::-webkit-scrollbar {
    display: none; /* Chrome, Safari, Opera */
}
.caste-nav-pills .nav-link {
    font-size: 0.84rem;
    font-weight: 700;
    color: #475569;
    padding: 8px 15px;
    border-radius: 30px;
    white-space: nowrap;
    flex-shrink: 0;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    user-select: none;
    -webkit-user-select: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.caste-nav-pills .nav-link:hover {
    color: #0f172a;
    background: #e2e8f0;
    border-color: #cbd5e1;
}
.caste-nav-pills .nav-link.active {
    background: #0f172a;
    color: #f59e0b;
    border-color: #0f172a;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.25);
}

@media (max-width: 767.98px) {
    .caste-nav-pills {
        gap: 6px;
        padding: 2px 0;
    }
    .caste-nav-pills .nav-link {
        font-size: 0.78rem;
        padding: 6px 11px;
        border-radius: 20px;
        gap: 4px;
    }
}

/* Target Section Anchors Scroll Margins to prevent hiding behind sticky header */
#caste-directory,
#demographics-matrix,
#socio-economic-report,
#reservation-quota-status,
#scanned-documents,
#govt-data-links,
#caste-faqs {
    scroll-margin-top: 130px;
}
@media (max-width: 991.98px) {
    #caste-directory,
    #demographics-matrix,
    #socio-economic-report,
    #reservation-quota-status,
    #scanned-documents,
    #govt-data-links,
    #caste-faqs {
        scroll-margin-top: 105px;
    }
}

/* Caste Code Badges */
.code-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 40px;
    font-size: 1.08rem;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    border-radius: 10px;
    background: #0f172a;
    color: #f59e0b;
    border: 1px solid rgba(245, 158, 11, 0.35);
    box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
}

/* Social Category Pills (Unified with Index.php Color Palette) */
.cat-pill-ebc { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; font-weight: 700; }
.cat-pill-bc  { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; font-weight: 700; }
.cat-pill-sc  { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; font-weight: 700; }
.cat-pill-gen { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; font-weight: 700; }
.cat-pill-st  { background: #faf5ff; color: #9333ea; border: 1px solid #e9d5ff; font-weight: 700; }
.cat-pill-oth { background: #f8fafc; color: #475569; border: 1px solid #e2e8f0; font-weight: 700; }

.btn-outline-purple {
    color: #9333ea;
    border-color: #d8b4fe;
    background-color: transparent;
}
.btn-outline-purple:hover, .btn-outline-purple.active {
    color: #ffffff;
    background-color: #9333ea;
    border-color: #9333ea;
}

.caste-row {
    transition: background-color 0.15s ease, transform 0.15s ease;
}
.caste-row:hover {
    background-color: #f8fafc;
}
.subcaste-badge {
    font-size: 0.76rem;
    background: #f8fafc;
    color: #334155;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-block;
    margin: 2px 3px 2px 0;
    border: 1px solid #e2e8f0;
}

/* Live Search Bar */
.search-box-wrap {
    position: relative;
}
.search-box-wrap i.search-icon {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.25rem;
    color: #64748b;
    pointer-events: none;
}
.search-box-input {
    padding-left: 50px;
    padding-right: 40px;
    height: 54px;
    border-radius: 14px;
    font-size: 1.05rem;
    border: 2px solid #cbd5e1;
    background-color: #ffffff;
    transition: all 0.2s ease;
}
.search-box-input:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 4px rgba(245, 158, 11, 0.18);
}
.clear-search-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    border: none;
    background: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 1.1rem;
    display: none;
}
.clear-search-btn:hover {
    color: #0f172a;
}

/* Filter Buttons & Chips */
.filter-btn-group .btn {
    border-radius: 30px;
    padding: 6px 14px;
    font-size: 0.84rem;
    font-weight: 700;
    transition: all 0.18s ease;
}
.filter-rel-btn {
    border-radius: 24px;
    padding: 5px 12px;
    font-size: 0.8rem;
    font-weight: 600;
    transition: all 0.18s ease;
}
.filter-rel-btn.active {
    background: #0f172a !important;
    color: #f59e0b !important;
    border-color: #0f172a !important;
    box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
}
.quick-chip {
    cursor: pointer;
    border-radius: 20px;
    font-size: 0.8rem;
    padding: 4px 12px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    font-weight: 600;
    transition: all 0.15s ease;
}
.quick-chip:hover, .quick-chip.active {
    background: #f59e0b;
    color: #ffffff;
    border-color: #f59e0b;
    transform: translateY(-1px);
    box-shadow: 0 4px 8px rgba(245, 158, 11, 0.25);
}

.sort-quick-btn {
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 700;
    padding: 4px 12px;
    transition: all 0.18s ease;
}
.sort-quick-btn.active {
    background: #0f172a !important;
    color: #f59e0b !important;
    border-color: #0f172a !important;
}

/* Sortable Table Columns */
.sortable-th {
    cursor: pointer;
    user-select: none;
    transition: background-color 0.15s ease, color 0.15s ease;
}
.sortable-th:hover {
    background-color: #f1f5f9 !important;
    color: #0f172a !important;
}
.sortable-th .sort-icon {
    font-size: 0.82rem;
    opacity: 0.45;
    margin-left: 4px;
    transition: all 0.15s ease;
}
.sortable-th:hover .sort-icon {
    opacity: 0.85;
}
.sortable-th.active-sort {
    color: #0f172a !important;
    background-color: #e2e8f0 !important;
}
.sortable-th.active-sort .sort-icon {
    opacity: 1;
    color: #d97706 !important;
}

.copy-btn {
    cursor: pointer;
    font-size: 0.78rem;
    padding: 4px 10px;
    border-radius: 8px;
    font-weight: 600;
    transition: all 0.2s ease;
}
.copy-btn:hover {
    background: #0f172a;
    color: #f59e0b;
    border-color: #0f172a;
}

/* Visual Representation Bar */
.demographic-segment-bar {
    display: flex;
    height: 18px;
    border-radius: 9px;
    overflow: hidden;
    box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.1);
}
.segment-item {
    transition: opacity 0.2s ease;
}
.segment-item:hover {
    opacity: 0.85;
}

/* Official Scanned Image Cards */
.official-img-card {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    background: #ffffff;
    transition: all 0.25s ease;
}
.official-img-card:hover {
    border-color: #f59e0b;
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -4px rgba(0, 0, 0, 0.12);
}

/* Judicial Alert Box */
.hc-judgment-box {
    background: linear-gradient(135deg, #fff5f5 0%, #fee2e2 100%);
    border: 1px solid #fca5a5;
    border-left: 6px solid #dc2626 !important;
    border-radius: 14px;
}

/* Top 10 Caste Ranking Cards */
.top-caste-rank-badge {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-weight: 800;
    font-size: 0.85rem;
}
</style>

<main class="bg-light pb-5">

    <!-- Hero Header -->
    <section class="caste-hero text-white py-4 py-lg-5">
        <div class="container text-start">
            
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small text-white-50">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white text-decoration-none">Census</a></li>
                    <li class="breadcrumb-item active text-warning" aria-current="page">Bihar Caste Survey 2022</li>
                </ol>
            </nav>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    <i class="bi bi-card-checklist"></i> Official GAD Caste Codes (215+)
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    <i class="bi bi-calendar3"></i> Bihar Survey 2022-2023
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2">
                    <i class="bi bi-people-fill"></i> 13.07 Crore Population
                </span>
                <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2">
                    <i class="bi bi-patch-check-fill"></i> Phase-2 Digital Coding
                </span>
                <span class="badge bg-danger bg-opacity-25 text-white fw-bold px-3 py-2">
                    <i class="bi bi-shield-shaded"></i> High Court Verdict Updated
                </span>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">
                2022 Bihar Caste-Based Survey: Complete 215+ Caste Codes &amp; Demographics
            </h1>
            <p class="h6 text-warning mb-3 fw-semibold" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                बिहार जाति आधारित गणना (जाति कोड सूची): सभी 215 जातियों के आधिकारिक कोड, सामाजिक-आर्थिक स्थिति एवं कोर्ट निर्णय
            </p>
            <p class="text-white-50 mb-4" style="font-size: 1.05rem; max-width: 960px;">
                Complete and authentic directory issued by the General Administration Department (GAD), Government of Bihar for the Caste-Based Survey (जातिगत जनगणना). Search any caste, sub-caste, or code number instantly with category breakdowns (EBC, BC, SC, ST, General), poverty indicators, and verified judicial status.
            </p>

            <!-- State Category Breakdown KPI Grid -->
            <div class="row g-2 g-md-3">
                <!-- EBC -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="hero-kpi-card text-center text-white h-100" style="border-color: rgba(16, 185, 129, 0.4);">
                        <small class="text-uppercase fw-bold d-block" style="font-size: 0.72rem; color: #4ade80 !important;">अत्यंत पिछड़ा (EBC)</small>
                        <span class="fs-4 fw-extrabold" style="color: #4ade80;">36.01%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">4.70 Cr (112 Castes)</small>
                    </div>
                </div>

                <!-- BC / OBC -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="hero-kpi-card text-center text-white h-100" style="border-color: rgba(245, 158, 11, 0.4);">
                        <small class="text-uppercase fw-bold d-block" style="font-size: 0.72rem; color: #fbbf24 !important;">पिछड़ा वर्ग (BC)</small>
                        <span class="fs-4 fw-extrabold text-warning">27.13%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">3.54 Cr (29 Castes)</small>
                    </div>
                </div>

                <!-- SC -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="hero-kpi-card text-center text-white h-100" style="border-color: rgba(239, 68, 68, 0.4);">
                        <small class="text-uppercase fw-bold d-block" style="font-size: 0.72rem; color: #f87171 !important;">अनुसूचित जाति (SC)</small>
                        <span class="fs-4 fw-extrabold" style="color: #f87171;">19.65%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">2.56 Cr (22 Castes)</small>
                    </div>
                </div>

                <!-- General / Unreserved -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="hero-kpi-card text-center text-white h-100" style="border-color: rgba(59, 130, 246, 0.4);">
                        <small class="text-uppercase fw-bold d-block" style="font-size: 0.72rem; color: #60a5fa !important;">अनारक्षित (GEN)</small>
                        <span class="fs-4 fw-extrabold" style="color: #60a5fa;">15.52%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">2.02 Cr (16 Castes)</small>
                    </div>
                </div>

                <!-- ST -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="hero-kpi-card text-center text-white h-100" style="border-color: rgba(147, 51, 234, 0.4);">
                        <small class="text-uppercase fw-bold d-block" style="font-size: 0.72rem; color: #c084fc !important;">अनुसूचित जनजाति (ST)</small>
                        <span class="fs-4 fw-extrabold" style="color: #c084fc;">1.68%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">21.99 Lakh (32 Castes)</small>
                    </div>
                </div>

                <!-- Total Population -->
                <div class="col-6 col-md-4 col-lg">
                    <div class="hero-kpi-card text-center text-white h-100" style="background: rgba(245, 158, 11, 0.18); border-color: rgba(245, 158, 11, 0.5);">
                        <small class="text-warning text-uppercase fw-bold d-block" style="font-size: 0.72rem;">कुल जनसंख्या (Total)</small>
                        <span class="fs-4 fw-extrabold text-white">13.07 Cr</span>
                        <small class="text-warning d-block" style="font-size: 0.75rem;">2.76 Cr Families</small>
                    </div>
                </div>
            </div>

            <!-- Segmented Color Bar -->
            <div class="mt-4">
                <div class="d-flex justify-content-between align-items-center text-white-50 small mb-1">
                    <span><i class="bi bi-pie-chart-fill text-warning me-1"></i> State Social Category Demographic Spectrum:</span>
                    <span class="fw-bold text-white">84.48% Combined Reserved Share</span>
                </div>
                <div class="demographic-segment-bar">
                    <div class="segment-item" style="width: 36.01%; background-color: #10b981;" title="EBC: 36.01%"></div>
                    <div class="segment-item" style="width: 27.13%; background-color: #f59e0b;" title="BC: 27.13%"></div>
                    <div class="segment-item" style="width: 19.65%; background-color: #ef4444;" title="SC: 19.65%"></div>
                    <div class="segment-item" style="width: 15.52%; background-color: #3b82f6;" title="GEN: 15.52%"></div>
                    <div class="segment-item" style="width: 1.68%; background-color: #9333ea;" title="ST: 1.68%"></div>
                </div>
            </div>

        </div>
    </section>

    <!-- Sticky Section Navigation Bar -->
    <div class="caste-sticky-nav py-2">
        <div class="container px-2 px-md-3">
            <div class="caste-nav-wrapper">
                <nav class="caste-nav-pills" aria-label="Caste Survey Sections">
                    <a href="#caste-directory" class="nav-link active"><span>📋</span> <span>216 Caste Directory</span></a>
                    <a href="#demographics-matrix" class="nav-link"><span>📊</span> <span>Demographics &amp; Religion</span></a>
                    <a href="#socio-economic-report" class="nav-link"><span>💼</span> <span>Socio-Economic Report</span></a>
                    <a href="#reservation-quota-status" class="nav-link"><span>⚖️</span> <span>Reservation &amp; HC Ruling</span></a>
                    <a href="#scanned-documents" class="nav-link"><span>📜</span> <span>Official PDF Scans</span></a>
                    <a href="#govt-data-links" class="nav-link"><span>🔗</span> <span>Govt Citations</span></a>
                    <a href="#caste-faqs" class="nav-link"><span>❓</span> <span>Survey FAQs</span></a>
                </nav>
            </div>
        </div>
    </div>

    <!-- Interactive Search, Filter & Master Table Section -->
    <section class="container mt-4" id="caste-directory">
        
        <!-- Search & Control Header Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 align-items-center">
                    <!-- Live Search Input -->
                    <div class="col-lg-7">
                        <div class="search-box-wrap">
                            <i class="bi bi-search search-icon"></i>
                            <input type="text" id="casteSearchInput" class="form-control search-box-input" 
                                   placeholder="Search by caste name (e.g. यादव, ब्राह्मण, Bania, 122, कुशवाहा, अंसारी, Paswan, Kurmi)..." 
                                   value="<?php echo htmlspecialchars($searchQuery ?: ($selectedCode ? (string)$selectedCode : '')); ?>">
                            <button type="button" id="clearSearchBtn" class="clear-search-btn" title="Clear Search">
                                <i class="bi bi-x-circle-fill"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Sort / Options Dropdown -->
                    <div class="col-md-6 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-arrow-down-up text-warning"></i></span>
                            <select id="casteSortSelect" class="form-select border-start-0 py-2">
                                <option value="code_asc" <?php echo $selectedSort === 'code_asc' ? 'selected' : ''; ?>>🔢 Sort by Code (1 to 216)</option>
                                <option value="code_desc" <?php echo $selectedSort === 'code_desc' ? 'selected' : ''; ?>>🔢 Sort by Code (216 to 1)</option>
                                <option value="cat_asc" <?php echo $selectedSort === 'cat_asc' ? 'selected' : ''; ?>>🏷️ Sort by Category (EBC → BC → SC → ST → GEN)</option>
                                <option value="cat_desc" <?php echo $selectedSort === 'cat_desc' ? 'selected' : ''; ?>>🏷️ Sort by Category (GEN → ST → SC → BC → EBC)</option>
                                <option value="rel_asc" <?php echo $selectedSort === 'rel_asc' ? 'selected' : ''; ?>>🕉️ Sort by Religion (Hindu → Muslim → Others)</option>
                                <option value="rel_alpha" <?php echo $selectedSort === 'rel_alpha' ? 'selected' : ''; ?>>🕉️ Sort by Religion (A to Z)</option>
                                <option value="name_asc" <?php echo $selectedSort === 'name_asc' ? 'selected' : ''; ?>>🔤 Sort by Name (A to Z)</option>
                                <option value="name_desc" <?php echo $selectedSort === 'name_desc' ? 'selected' : ''; ?>>🔤 Sort by Name (Z to A)</option>
                                <option value="pop_desc" <?php echo $selectedSort === 'pop_desc' ? 'selected' : ''; ?>>📊 Sort by Pop. Share (Highest first)</option>
                                <option value="pop_asc" <?php echo $selectedSort === 'pop_asc' ? 'selected' : ''; ?>>📊 Sort by Pop. Share (Lowest first)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Export & Print Action Buttons -->
                    <div class="col-md-6 col-lg-2 d-flex gap-2">
                        <button class="btn btn-outline-secondary w-50 py-2 d-flex align-items-center justify-content-center gap-1" onclick="window.print()" title="Print Caste Code Table">
                            <i class="bi bi-printer"></i> <span>Print</span>
                        </button>
                        <button class="btn btn-warning w-50 py-2 d-flex align-items-center justify-content-center gap-1 text-dark fw-bold" id="exportCsvBtn" title="Download Caste Code List as CSV">
                            <i class="bi bi-download"></i> <span>CSV</span>
                        </button>
                    </div>
                </div>

                <!-- Quick Sort Button Bar -->
                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-3 pt-3 border-top">
                    <span class="small fw-bold text-muted text-uppercase me-1" style="font-size: 0.75rem;"><i class="bi bi-arrow-down-up"></i> Quick Sort:</span>
                    <button type="button" class="btn btn-sm btn-outline-dark sort-quick-btn" data-sort="cat_asc">
                        <i class="bi bi-layers-fill text-success me-1"></i> Sort by Cat (कोटि)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark sort-quick-btn" data-sort="rel_asc">
                        <i class="bi bi-moon-stars-fill text-info me-1"></i> Sort by Religion (धर्म)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark sort-quick-btn active" data-sort="code_asc">
                        <i class="bi bi-sort-numeric-down me-1"></i> Sort by Code (1-216)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark sort-quick-btn" data-sort="pop_desc">
                        <i class="bi bi-graph-up-arrow text-warning me-1"></i> Sort by Pop Share
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark sort-quick-btn" data-sort="name_asc">
                        <i class="bi bi-sort-alpha-down me-1"></i> Sort by Name (A-Z)
                    </button>
                </div>

                <!-- Category Filters Bar -->
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2 pt-2 border-top">
                    <span class="small fw-bold text-muted text-uppercase me-1" style="font-size: 0.75rem;"><i class="bi bi-tag-fill text-primary"></i> Category Filter:</span>
                    <div class="filter-btn-group d-flex flex-wrap gap-1">
                        <button class="btn btn-dark active filter-cat-btn" data-category="ALL">
                            All Castes <span class="badge bg-secondary ms-1" id="countAll"><?php echo $categoryCounts['ALL']; ?></span>
                        </button>
                        <button class="btn btn-outline-success filter-cat-btn" data-category="EBC">
                            अत्यंत पिछड़ा (EBC) <span class="badge bg-success ms-1" id="countEbc"><?php echo $categoryCounts['EBC']; ?></span>
                        </button>
                        <button class="btn btn-outline-warning text-dark filter-cat-btn" data-category="BC">
                            पिछड़ा वर्ग (BC) <span class="badge bg-warning text-dark ms-1" id="countBc"><?php echo $categoryCounts['BC']; ?></span>
                        </button>
                        <button class="btn btn-outline-danger filter-cat-btn" data-category="SC">
                            अनुसूचित जाति (SC) <span class="badge bg-danger ms-1" id="countSc"><?php echo $categoryCounts['SC']; ?></span>
                        </button>
                        <button class="btn btn-outline-primary filter-cat-btn" data-category="GEN">
                            सामान्य / अनारक्षित (GEN) <span class="badge bg-primary ms-1" id="countGen"><?php echo $categoryCounts['GEN']; ?></span>
                        </button>
                        <button class="btn btn-outline-purple filter-cat-btn" data-category="ST">
                            अनुसूचित जनजाति (ST) <span class="badge text-white ms-1" style="background: #9333ea;" id="countSt"><?php echo $categoryCounts['ST']; ?></span>
                        </button>
                        <button class="btn btn-outline-secondary filter-cat-btn" data-category="OTHER">
                            अन्य (Other) <span class="badge bg-secondary ms-1" id="countOth"><?php echo $categoryCounts['OTHER']; ?></span>
                        </button>
                    </div>
                </div>

                <!-- Religion Filters Bar -->
                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-2 pt-2 border-top">
                    <span class="small fw-bold text-muted text-uppercase me-1" style="font-size: 0.75rem;"><i class="bi bi-bank text-warning"></i> Religion Filter:</span>
                    <div class="filter-rel-group d-flex flex-wrap gap-1">
                        <button class="btn btn-sm btn-outline-dark filter-rel-btn active" data-religion="ALL">
                            All Religions <span class="badge bg-secondary ms-1"><?php echo $religionCounts['ALL']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-warning text-dark filter-rel-btn" data-religion="HINDU">
                            हिंदू (Hindu) <span class="badge bg-warning text-dark ms-1"><?php echo $religionCounts['HINDU']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-success text-dark filter-rel-btn" data-religion="MUSLIM">
                            मुस्लिम (Muslim / Islam) <span class="badge bg-success text-white ms-1"><?php echo $religionCounts['MUSLIM']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-primary filter-rel-btn" data-religion="CHRISTIAN">
                            ईसाई (Christian) <span class="badge bg-primary text-white ms-1"><?php echo $religionCounts['CHRISTIAN']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-info text-dark filter-rel-btn" data-religion="SIKH">
                            सिख (Sikh) <span class="badge bg-info text-dark ms-1"><?php echo $religionCounts['SIKH']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-danger filter-rel-btn" data-religion="JAIN">
                            जैन (Jain) <span class="badge bg-danger text-white ms-1"><?php echo $religionCounts['JAIN']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-rel-btn" data-religion="BUDDHIST">
                            बौद्ध (Buddhist) <span class="badge bg-secondary text-white ms-1"><?php echo $religionCounts['BUDDHIST']; ?></span>
                        </button>
                        <button class="btn btn-sm btn-outline-secondary filter-rel-btn" data-religion="OTHER">
                            अन्य / सर्वधर्म <span class="badge bg-light text-dark ms-1"><?php echo $religionCounts['OTHER']; ?></span>
                        </button>
                    </div>
                </div>

                <!-- Quick Selection Tags for Major Castes -->
                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-2 pt-2">
                    <span class="small text-muted me-1 fw-semibold" style="font-size: 0.75rem;">Quick Lookup:</span>
                    <span class="quick-chip" data-term="165">यादव (#165)</span>
                    <span class="quick-chip" data-term="126">ब्राह्मण (#126)</span>
                    <span class="quick-chip" data-term="169">राजपूत (#169)</span>
                    <span class="quick-chip" data-term="142">भूमिहार (#142)</span>
                    <span class="quick-chip" data-term="26">कुशवाहा (#26)</span>
                    <span class="quick-chip" data-term="24">कुर्मी (#24)</span>
                    <span class="quick-chip" data-term="122">बनिया (#122)</span>
                    <span class="quick-chip" data-term="83">तेली (#83)</span>
                    <span class="quick-chip" data-term="148">मल्लाह / निषाद (#148)</span>
                    <span class="quick-chip" data-term="60">रविदास / चमार (#60)</span>
                    <span class="quick-chip" data-term="87">दुसाध / पासवान (#87)</span>
                    <span class="quick-chip" data-term="158">मुसहर (#158)</span>
                    <span class="quick-chip" data-term="161">अंसारी / मोमिन (#161)</span>
                    <span class="quick-chip" data-term="181">शेख (#181)</span>
                    <span class="quick-chip" data-term="105">पठान (#105)</span>
                    <span class="quick-chip" data-term="21">कायस्थ (#21)</span>
                    <span class="quick-chip" data-term="187">सुरजापुरी (#187)</span>
                    <span class="quick-chip" data-term="216">अन्य (#216)</span>
                </div>
            </div>
        </div>

        <!-- Caste Table & Results Grid -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-table text-warning me-1"></i> Official Caste Codes Directory (1 - 216)
                    </h2>
                    <span class="badge bg-dark rounded-pill px-3" id="resultsCountBadge">216 Castes Listed</span>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-info-circle text-primary"></i> Click column headers or use sort buttons to order by Category, Religion, Code, or Name
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="casteMasterTable">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-4 py-3 sortable-th active-sort" data-sort-key="code" style="width: 110px;" title="Click to sort by Code">
                                Code (कोड) <i class="bi bi-arrow-down-up sort-icon"></i>
                            </th>
                            <th class="py-3 sortable-th" data-sort-key="name" style="min-width: 220px;" title="Click to sort by Caste Name">
                                Caste Name (जाति का नाम) <i class="bi bi-arrow-down-up sort-icon"></i>
                            </th>
                            <th class="py-3" style="min-width: 260px;">Sub-Castes &amp; Synonyms (उपजातियां / उपनाम)</th>
                            <th class="py-3 sortable-th" data-sort-key="cat" style="width: 170px;" title="Click to sort by Category">
                                Category (कोटि) <i class="bi bi-arrow-down-up sort-icon"></i>
                            </th>
                            <th class="py-3 sortable-th" data-sort-key="rel" style="width: 140px;" title="Click to sort by Religion">
                                Religion (धर्म) <i class="bi bi-arrow-down-up sort-icon"></i>
                            </th>
                            <th class="py-3 text-end sortable-th" data-sort-key="pop" style="width: 160px;" title="Click to sort by Population Share">
                                Survey Pop. Share <i class="bi bi-arrow-down-up sort-icon"></i>
                            </th>
                            <th class="pe-4 py-3 text-center" style="width: 100px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="casteTableBody">
                        <?php foreach ($castes as $c): 
                            $cat = $c['category'];
                            $pillClass = match($cat) {
                                'EBC' => 'cat-pill-ebc',
                                'BC' => 'cat-pill-bc',
                                'SC' => 'cat-pill-sc',
                                'ST' => 'cat-pill-st',
                                'GEN' => 'cat-pill-gen',
                                default => 'cat-pill-oth'
                            };
                            $subcastesList = array_map('trim', explode(',', $c['subcastes']));
                        ?>
                        <tr class="caste-row" 
                            data-code="<?php echo $c['code']; ?>"
                            data-name-hi="<?php echo htmlspecialchars($c['name_hi']); ?>"
                            data-name-en="<?php echo htmlspecialchars($c['name_en']); ?>"
                            data-category="<?php echo $c['category']; ?>"
                            data-religion="<?php echo htmlspecialchars($c['religion']); ?>"
                            data-subcastes="<?php echo htmlspecialchars($c['subcastes']); ?>"
                            data-population="<?php echo $c['population']; ?>"
                            data-percentage="<?php echo $c['percentage']; ?>"
                            id="caste-row-<?php echo $c['code']; ?>">
                            
                            <!-- Code Number -->
                            <td class="ps-4">
                                <div class="code-badge" title="Official Caste Code #<?php echo $c['code']; ?>">
                                    <?php echo sprintf('%02d', $c['code']); ?>
                                </div>
                            </td>

                            <!-- Caste Name (Hindi & English) -->
                            <td>
                                <div class="fw-bold fs-6 text-dark" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                    <?php echo htmlspecialchars($c['name_hi']); ?>
                                </div>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($c['name_en']); ?>
                                </div>
                                <?php if (!empty($c['restriction'])): ?>
                                    <div class="badge bg-light text-danger border border-danger-subtle mt-1 small" style="font-size: 0.7rem;">
                                        <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($c['restriction']); ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <!-- Sub-castes / Synonyms -->
                            <td>
                                <div class="subcastes-container">
                                    <?php 
                                    $displayed = 0;
                                    foreach ($subcastesList as $sub): 
                                        if ($displayed >= 5) {
                                            $remaining = count($subcastesList) - 5;
                                            echo "<span class='subcaste-badge bg-warning-subtle text-dark fw-bold'>+{$remaining} more</span>";
                                            break;
                                        }
                                        if (!empty($sub)):
                                    ?>
                                        <span class="subcaste-badge"><?php echo htmlspecialchars($sub); ?></span>
                                    <?php 
                                        $displayed++;
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </td>

                            <!-- Category Badge -->
                            <td>
                                <span class="badge rounded-pill px-3 py-2 fw-semibold <?php echo $pillClass; ?>">
                                    <?php echo htmlspecialchars($c['category']); ?>
                                </span>
                                <div class="small text-muted mt-1" style="font-size: 0.72rem;">
                                    <?php echo htmlspecialchars($c['category_name_hi']); ?>
                                </div>
                            </td>

                            <!-- Religion -->
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?php echo htmlspecialchars($c['religion']); ?>
                                </span>
                            </td>

                            <!-- Survey Population Share -->
                            <td class="text-end">
                                <?php if ($c['percentage'] > 0): ?>
                                    <span class="fw-bold text-dark"><?php echo number_format($c['percentage'], 2); ?>%</span>
                                    <div class="small text-muted" style="font-size: 0.72rem;">
                                        <?php echo number_format($c['population']); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">N/A</span>
                                <?php endif; ?>
                            </td>

                            <!-- Action -->
                            <td class="pe-4 text-center">
                                <button class="btn btn-sm btn-outline-dark copy-btn" onclick="copyCasteCode(<?php echo $c['code']; ?>, '<?php echo addslashes($c['name_hi']); ?>')" title="Copy Code #<?php echo $c['code']; ?>">
                                    <i class="bi bi-copy"></i> Copy
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Empty State for Search -->
            <div id="noResultsState" class="text-center py-5 d-none">
                <div class="fs-1 text-muted mb-2"><i class="bi bi-search"></i></div>
                <h5 class="fw-bold text-dark">No Caste Matches Found</h5>
                <p class="text-muted small">Try searching with a different spelling in Hindi or English, or search by code number.</p>
                <button class="btn btn-warning btn-sm fw-bold" onclick="resetSearch()">Reset Filters</button>
            </div>
        </div>

        <!-- Demographics & Religion Matrix Section -->
        <div class="row g-4 mb-5" id="demographics-matrix">
            
            <!-- Column 1: Social Category Demographic Analysis -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-pie-chart-fill text-warning me-2"></i> Social Category Demographics (जाति कोटि हिस्सेदारी)
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <p class="small text-muted mb-3">
                            Official demographic breakdown released on October 2, 2023, by the Bihar State Government based on the comprehensive caste survey covering 13.07 Crore people across 38 districts:
                        </p>

                        <!-- EBC -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">अत्यंत पिछड़ा वर्ग (EBC / BC-1)</span>
                                <span class="fw-extrabold text-success">36.01% <small class="text-muted fw-normal">(4,70,80,514)</small></span>
                            </div>
                            <div class="progress progress-bar-custom bg-light" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: 36.01%;" aria-valuenow="36.01" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- BC -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">पिछड़ा वर्ग (BC / BC-2 / OBC)</span>
                                <span class="fw-extrabold text-warning" style="color: #d97706 !important;">27.13% <small class="text-muted fw-normal">(3,54,63,936)</small></span>
                            </div>
                            <div class="progress progress-bar-custom bg-light" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: 27.13%;" aria-valuenow="27.13" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- SC -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">अनुसूचित जाति (SC / Scheduled Castes)</span>
                                <span class="fw-extrabold text-danger">19.65% <small class="text-muted fw-normal">(2,56,89,820)</small></span>
                            </div>
                            <div class="progress progress-bar-custom bg-light" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar bg-danger" role="progressbar" style="width: 19.65%;" aria-valuenow="19.65" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- General -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">अनारक्षित / सामान्य वर्ग (General / Unreserved)</span>
                                <span class="fw-extrabold text-primary">15.52% <small class="text-muted fw-normal">(2,02,91,679)</small></span>
                            </div>
                            <div class="progress progress-bar-custom bg-light" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar bg-primary" role="progressbar" style="width: 15.52%;" aria-valuenow="15.52" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- ST -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-bold text-dark">अनुसूचित जनजाति (ST / Scheduled Tribes)</span>
                                <span class="fw-extrabold" style="color: #9333ea;">1.68% <small class="text-muted fw-normal">(21,99,361)</small></span>
                            </div>
                            <div class="progress progress-bar-custom bg-light" style="height: 10px; border-radius: 6px;">
                                <div class="progress-bar" role="progressbar" style="width: 1.68%; background-color: #9333ea;" aria-valuenow="1.68" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <div class="p-3 bg-light rounded-3 mt-4 border">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-shield-fill-check text-success fs-4"></i>
                                <div>
                                    <div class="fw-bold text-dark">Combined Reserved Categories (EBC + BC + SC + ST):</div>
                                    <div class="fs-5 fw-extrabold text-primary">84.48% (11,04,33,631 Population)</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Column 2: Religious Demographics & Top Castes -->
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-header bg-white py-3 px-4 border-bottom">
                        <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-bar-chart-fill text-warning me-2"></i> Religious Demographics &amp; Major Castes
                        </h3>
                    </div>
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark mb-2">Religious Distribution (धर्मानुसार जनसंख्या)</h6>
                        <div class="row g-2 mb-4">
                            <?php foreach ($summary['religions'] as $rel): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2 border rounded-3 bg-light text-center">
                                    <small class="text-muted d-block text-truncate" style="font-size: 0.72rem;"><?php echo $rel['name_hi']; ?></small>
                                    <span class="fw-bold" style="color: <?php echo $rel['color']; ?>;"><?php echo number_format($rel['percentage'], 2); ?>%</span>
                                    <small class="d-block text-muted" style="font-size: 0.68rem;"><?php echo number_format($rel['population']); ?></small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>

                        <h6 class="fw-bold text-dark mb-2">Top 10 Most Populous Castes in Bihar</h6>
                        <ul class="list-group list-group-flush border-top border-bottom mb-0">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-warning text-dark me-2">1</span> <span class="badge bg-dark me-1">#165</span> <strong>यादव (Yadav)</strong> <span class="badge cat-pill-bc">BC</span></div>
                                <span class="fw-bold text-warning" style="color: #d97706 !important;">14.27% (1.86 Cr)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-secondary text-white me-2">2</span> <span class="badge bg-dark me-1">#87</span> <strong>दुसाध / पासवान (Dusadh)</strong> <span class="badge cat-pill-sc">SC</span></div>
                                <span class="fw-bold text-danger">5.31% (69.43 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-secondary text-white me-2">3</span> <span class="badge bg-dark me-1">#60</span> <strong>रविदास / चमार (Ravidas)</strong> <span class="badge cat-pill-sc">SC</span></div>
                                <span class="fw-bold text-danger">5.25% (68.70 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">4</span> <span class="badge bg-dark me-1">#26</span> <strong>कुशवाहा / कोईरी (Kushwaha)</strong> <span class="badge cat-pill-bc">BC</span></div>
                                <span class="fw-bold text-warning" style="color: #d97706 !important;">4.21% (55.06 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">5</span> <span class="badge bg-dark me-1">#181</span> <strong>शेख (Sheikh Muslim)</strong> <span class="badge cat-pill-gen">GEN</span></div>
                                <span class="fw-bold text-primary">3.82% (49.92 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">6</span> <span class="badge bg-dark me-1">#126</span> <strong>ब्राह्मण (Brahmin)</strong> <span class="badge cat-pill-gen">GEN</span></div>
                                <span class="fw-bold text-primary">3.66% (47.81 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">7</span> <span class="badge bg-dark me-1">#161</span> <strong>मोमिन / अंसारी (Momin)</strong> <span class="badge cat-pill-ebc">EBC</span></div>
                                <span class="fw-bold text-success">3.55% (46.35 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">8</span> <span class="badge bg-dark me-1">#169</span> <strong>राजपूत (Rajput)</strong> <span class="badge cat-pill-gen">GEN</span></div>
                                <span class="fw-bold text-primary">3.45% (45.11 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">9</span> <span class="badge bg-dark me-1">#158</span> <strong>मुसहर (Musahar)</strong> <span class="badge cat-pill-sc">SC</span></div>
                                <span class="fw-bold text-danger">3.09% (40.36 Lakh)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <div><span class="top-caste-rank-badge bg-light text-dark border me-2">10</span> <span class="badge bg-dark me-1">#24</span> <strong>कुर्मी (Kurmi)</strong> <span class="badge cat-pill-bc">BC</span></div>
                                <span class="fw-bold text-warning" style="color: #d97706 !important;">2.87% (37.62 Lakh)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Comprehensive Socio-Economic, Poverty & Employment Report Section (7 Nov 2023 Assembly Report) -->
        <div class="card border-0 shadow-sm rounded-4 mb-5" id="socio-economic-report">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-cash-stack text-success me-2"></i> Socio-Economic, Poverty &amp; Livelihood Profile (सामाजिक-आर्थिक सर्वेक्षण)
                    </h3>
                    <small class="text-muted">Detailed survey findings tabled in the Bihar Legislative Assembly on 7 November 2023 covering 2.76 Crore families</small>
                </div>
                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fw-bold">
                    34.13% Families Live in Poverty (&lt;₹6,000/mo)
                </span>
            </div>
            <div class="card-body p-4">
                
                <!-- Row 1: Monthly Family Income Distribution & Category Poverty -->
                <div class="row g-4 mb-4">
                    
                    <!-- Left: Family Monthly Income Bracket -->
                    <div class="col-lg-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h5 class="fw-bold text-dark fs-6 mb-3 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-wallet2 text-primary me-2"></i> Monthly Family Income Distribution (मासिक पारिवारिक आय)</span>
                                <span class="badge bg-primary">2.76 Cr Families</span>
                            </h5>

                            <div class="space-y-3">
                                <?php foreach ($socioEconomic['income_distribution'] as $inc): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="fw-bold text-dark small"><?php echo $inc['bracket']; ?></span>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;"><?php echo $inc['desc']; ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="fw-extrabold" style="color: <?php echo $inc['color']; ?>;"><?php echo number_format($inc['percentage'], 2); ?>%</span>
                                            <small class="text-muted d-block" style="font-size: 0.72rem;"><?php echo number_format($inc['families']); ?> Families</small>
                                        </div>
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $inc['percentage']; ?>%; background-color: <?php echo $inc['color']; ?>;" aria-valuenow="<?php echo $inc['percentage']; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>

                            <div class="alert alert-warning py-2 px-3 small mt-3 mb-0 border-0 rounded-3">
                                <i class="bi bi-exclamation-triangle-fill me-1 text-warning"></i> <strong>Key Takeaway:</strong> 94.42 lakh families (34.13%) in Bihar survive on less than ₹200/day (<₹6,000/month), representing extreme economic vulnerability.
                            </div>
                        </div>
                    </div>

                    <!-- Right: Category-Wise Poverty Rates -->
                    <div class="col-lg-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h5 class="fw-bold text-dark fs-6 mb-3 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-graph-down-arrow text-danger me-2"></i> Poverty Rate by Social Group (वर्गवार निर्धनता दर)</span>
                                <span class="badge bg-danger">Income &lt; ₹6,000</span>
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered bg-white align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th>Social Category</th>
                                            <th class="text-center">Total Families</th>
                                            <th class="text-center">Poor Families</th>
                                            <th class="text-end">Poverty %</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php foreach ($socioEconomic['category_poverty'] as $cp): ?>
                                        <tr>
                                            <td>
                                                <span class="badge rounded-pill me-1" style="background-color: <?php echo $cp['color']; ?>; color: #fff;">
                                                    <?php echo $cp['category']; ?>
                                                </span>
                                                <strong><?php echo $cp['name_hi']; ?></strong>
                                                <div class="text-muted" style="font-size: 0.68rem;"><?php echo $cp['key_castes']; ?></div>
                                            </td>
                                            <td class="text-center text-muted"><?php echo number_format($cp['total_families']); ?></td>
                                            <td class="text-center fw-semibold text-danger"><?php echo number_format($cp['poor_families']); ?></td>
                                            <td class="text-end">
                                                <span class="fs-6 fw-extrabold" style="color: <?php echo $cp['color']; ?>;"><?php echo number_format($cp['poverty_rate'], 2); ?>%</span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 p-2 bg-white rounded border">
                                <div class="small fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary"></i> Highest Poverty Among Specific Castes:</div>
                                <div class="d-flex flex-wrap gap-1">
                                    <span class="badge bg-light text-danger border">Musahar (54.56%)</span>
                                    <span class="badge bg-light text-danger border">Bhuiya (53.46%)</span>
                                    <span class="badge bg-light text-danger border">Dom (53.10%)</span>
                                    <span class="badge bg-light text-warning text-dark border">Mallah (34.56%)</span>
                                    <span class="badge bg-light text-primary border">Yadav (35.87%)</span>
                                    <span class="badge bg-light text-secondary border">Bhumihar (27.58%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Row 2: Education Attainment & Employment Distribution -->
                <div class="row g-4">
                    
                    <!-- Left: Educational Attainment -->
                    <div class="col-lg-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h5 class="fw-bold text-dark fs-6 mb-3 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-mortarboard-fill text-primary me-2"></i> Educational Attainment (शैक्षणिक स्तर)</span>
                                <span class="badge bg-secondary">13.07 Cr Pop.</span>
                            </h5>

                            <div class="row g-2">
                                <?php foreach ($socioEconomic['education_levels'] as $edu): ?>
                                <div class="col-6">
                                    <div class="p-2.5 bg-white rounded-3 border text-start h-100">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <small class="text-muted fw-bold" style="font-size: 0.72rem;"><?php echo $edu['level_hi']; ?></small>
                                            <i class="<?php echo $edu['icon']; ?> text-warning"></i>
                                        </div>
                                        <div class="fs-5 fw-extrabold text-dark mt-1"><?php echo number_format($edu['percentage'], 2); ?>%</div>
                                        <small class="text-muted d-block" style="font-size: 0.68rem;"><?php echo number_format($edu['population']); ?> persons</small>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Employment & Govt Jobs Share -->
                    <div class="col-lg-6">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <h5 class="fw-bold text-dark fs-6 mb-3 d-flex align-items-center justify-content-between">
                                <span><i class="bi bi-briefcase-fill text-info me-2"></i> Employment &amp; Workforce Share (रोजगार व आजीविका)</span>
                                <span class="badge bg-success">Only 1.57% Govt Jobs</span>
                            </h5>

                            <div class="table-responsive">
                                <table class="table table-sm table-hover bg-white align-middle mb-0">
                                    <thead class="table-light small text-uppercase">
                                        <tr>
                                            <th>Livelihood Sector</th>
                                            <th class="text-center">Count</th>
                                            <th class="text-end">Share %</th>
                                        </tr>
                                    </thead>
                                    <tbody class="small">
                                        <?php foreach ($socioEconomic['employment_distribution'] as $emp): ?>
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark border me-1"><?php echo $emp['badge']; ?></span>
                                                <strong><?php echo $emp['sector']; ?></strong>
                                            </td>
                                            <td class="text-center text-muted"><?php echo number_format($emp['count']); ?></td>
                                            <td class="text-end fw-bold" style="color: <?php echo $emp['color']; ?>;"><?php echo number_format($emp['percentage'], 2); ?>%</td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-3 p-2.5 bg-white rounded border">
                                <div class="small fw-bold text-dark mb-1"><i class="bi bi-award text-success"></i> Government Jobs by Social Group:</div>
                                <div class="small text-muted">
                                    General: <strong>3.19%</strong> (Kayastha 6.68%, Bhumihar 4.99%, Rajput 3.81%, Brahmin 3.60%) &bull; 
                                    BC: <strong>1.75%</strong> (Kurmi 3.11%, Kushwaha 2.04%, Yadav 1.55%) &bull; 
                                    ST: <strong>1.37%</strong> &bull; 
                                    SC: <strong>1.13%</strong> &bull; 
                                    EBC: <strong>0.98%</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- 75% Reservation Quota Evolution & Patna High Court Judgment Section -->
        <div class="card border-0 shadow-sm rounded-4 mb-5" id="reservation-quota-status">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-sliders text-primary me-2"></i> Bihar Reservation Quota &amp; High Court Judgment Status
                    </h3>
                    <small class="text-muted">Evolution from 50% to 65% (+10% EWS = 75%) and subsequent Patna High Court verdict in CWJC No. 16760 of 2023</small>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <span class="badge bg-danger px-3 py-2 fw-bold">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> HC Struck Down 65% Quota (20 June 2024)
                    </span>
                    <span class="badge bg-secondary px-3 py-2">Operative Quota: 50% (+10% EWS = 60%)</span>
                </div>
            </div>
            <div class="card-body p-4">

                <!-- Patna High Court Judgment Alert Callout Box -->
                <div class="alert alert-danger border-0 rounded-3 p-3 p-md-4 mb-4 shadow-sm" style="background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); border-left: 5px solid #dc2626 !important;">
                    <div class="d-flex flex-wrap align-items-start justify-content-between gap-3">
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-danger text-white fw-bold">
                                    <i class="bi bi-shield-shaded"></i> Landmark High Court Verdict
                                </span>
                                <span class="badge bg-white text-danger border border-danger small fw-bold">
                                    CWJC No. 16760 of 2023
                                </span>
                                <span class="small text-muted fw-semibold">Pronounced: 20 June 2024</span>
                            </div>

                            <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                                Patna High Court Quashes Bihar Reservation (Amendment) Acts 2023 (65% State Quota)
                            </h5>

                            <p class="small text-dark mb-2" style="line-height: 1.6;">
                                <strong>Case Citation:</strong> <em>Gaurav Kumar &amp; Ors. vs. The State of Bihar &amp; Ors.</em> (CWJC No. 16760 of 2023 with connected writ petitions).<br>
                                <strong>Coram:</strong> Hon'ble Chief Justice K. Vinod Chandran and Hon'ble Justice Harish Kumar.
                            </p>

                            <p class="small text-muted mb-3" style="line-height: 1.55;">
                                <strong>Key Ruling:</strong> The Division Bench held that increasing state reservation to 65% (and 75% with EWS) breached the mandatory 50% ceiling rule laid down in the 9-Judge Supreme Court bench judgment in <em>Indra Sawhney (1992)</em> and reaffirmed in <em>Jaishri Laxmanrao Patil (2021)</em>. The Court set aside the <em>Bihar Reservation of Vacancies in Posts and Services (Amendment) Act 2023</em> and <em>Bihar (Admission in Educational Institutions) Reservation (Amendment) Act 2023</em> as unconstitutional and ultra vires of Articles 14, 15, and 16.<br>
                                <strong>Supreme Court Status:</strong> The State of Bihar appealed to the Hon'ble Supreme Court of India. On 29 July 2024, the Supreme Court declined to grant an immediate interim stay on the High Court's ruling and listed the matter for final constitutional adjudication. Consequently, the previous <strong>50% state quota (+10% EWS = 60%)</strong> remains in active operation.
                            </p>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="https://patnahighcourt.gov.in/viewjudgment/MTUjMTY3NjAjMjAyMyMxI04=-QTRWlRjIeAA=" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-danger fw-bold d-inline-flex align-items-center gap-1 shadow-sm">
                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                    <span>Read Full Patna High Court Judgment (Official Portal)</span>
                                    <i class="bi bi-box-arrow-up-right small"></i>
                                </a>
                                <a href="#govt-data-links" class="btn btn-sm btn-outline-dark fw-bold">
                                    <i class="bi bi-link-45deg"></i> View Verified Citations
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reservation Matrix Comparison Table -->
                <div class="table-responsive">
                    <table class="table table-hover table-bordered align-middle text-center mb-0">
                        <thead class="table-light text-uppercase small text-muted">
                            <tr>
                                <th class="text-start ps-3" style="min-width: 230px;">Category (आरक्षण कोटि)</th>
                                <th style="width: 130px;">Survey Population Share</th>
                                <th style="width: 130px;">Pre-2023 Quota</th>
                                <th style="width: 140px;" class="table-danger text-danger fw-bold">2023 Act (Quashed by HC)</th>
                                <th style="width: 140px;" class="table-success text-success fw-bold">Current Operative Quota</th>
                                <th style="width: 120px;">Legal Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reservationEvolution['categories'] as $res): ?>
                            <tr>
                                <td class="text-start ps-3">
                                    <div class="fw-bold text-dark"><?php echo $res['category']; ?></div>
                                </td>
                                <td>
                                    <?php if ($res['population_share'] > 0): ?>
                                        <span class="fw-bold" style="color: <?php echo $res['color']; ?>;"><?php echo number_format($res['population_share'], 2); ?>%</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo $res['old_quota']; ?>%</td>
                                <td class="table-danger text-danger text-decoration-line-through fw-bold">
                                    <?php echo $res['enacted_quota']; ?>%
                                </td>
                                <td class="table-success fw-extrabold text-success fs-6">
                                    <?php echo $res['operative_quota']; ?>%
                                </td>
                                <td>
                                    <?php if ($res['category'] === 'Economically Weaker Sections (EWS / सामान्य ईडब्ल्यूएस)'): ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success fw-bold">Operative</span>
                                    <?php elseif ($res['enacted_quota'] > $res['operative_quota']): ?>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger fw-bold">HC Stayed/Quashed</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark border">Restored</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <tr class="table-warning fw-extrabold">
                                <td class="text-start ps-3 text-dark">State Caste Quota Sub-Total (EBC + BC + SC + ST + WBC)</td>
                                <td>84.48%</td>
                                <td>50%</td>
                                <td class="text-danger text-decoration-line-through">65%</td>
                                <td class="text-success fs-5">50%</td>
                                <td><span class="badge bg-secondary">Operative 50%</span></td>
                            </tr>
                            <tr class="table-dark text-white fw-extrabold">
                                <td class="text-start ps-3">Grand Total Reservation (+10% Central EWS Quota)</td>
                                <td>100.00%</td>
                                <td>60%</td>
                                <td class="text-danger text-decoration-line-through text-white-50">75%</td>
                                <td class="text-warning fs-5">60%</td>
                                <td><span class="badge bg-warning text-dark">60% in Effect</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-muted small">
                    <i class="bi bi-info-circle text-primary"></i> <em>Summary of Legal Position:</em> Following the Patna High Court judgment dated 20 June 2024, recruitment and admissions in Bihar are presently conducted under the 50% state quota (EBC 18%, BC 12%, SC 16%, ST 1%, WBC 3%) + 10% EWS = <strong>60% total quota</strong>, subject to final adjudication by the Hon'ble Supreme Court of India.
                </div>
            </div>
        </div>

        <!-- Survey Chronology & Legal Milestones -->
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-clock-history text-warning me-2"></i> Bihar Caste Survey Timeline &amp; Legal Milestones (सर्वेक्षण का घटनाक्रम)
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($surveyTimeline as $idx => $item): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-dark fw-bold"><?php echo $item['date']; ?></span>
                                <span class="small text-muted fw-bold">#<?php echo $idx + 1; ?></span>
                            </div>
                            <p class="small text-dark mb-0 flex-grow-1" style="line-height: 1.5;">
                                <?php echo $item['event']; ?>
                            </p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Official Scanned Documents Reference Gallery -->
        <div class="card border-0 shadow-sm rounded-4 mb-5" id="scanned-documents">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Official Document Pages (सरकारी प्रपत्र एवं अधिसूचना)
                    </h3>
                    <small class="text-muted">High-resolution scans of the official notification as reported by Prabhat Khabar &amp; GAD Bihar</small>
                </div>
                <span class="badge bg-warning text-dark fw-bold">5 Document Pages</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-6 col-md-4 col-lg">
                        <div class="official-img-card text-center p-2 bg-light">
                            <span class="small fw-bold text-dark d-block mb-1">Page 1 (Codes 01 - 32)</span>
                            <a href="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/0fbd9fb5-7ea6-4522-ba7a-8cd00e481177/code1.jpg" target="_blank" rel="noopener">
                                <img src="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/0fbd9fb5-7ea6-4522-ba7a-8cd00e481177/code1.jpg" alt="Bihar Caste Code Page 1" class="img-fluid rounded border" style="max-height: 180px; object-fit: contain;">
                            </a>
                            <small class="text-muted d-block mt-1">अघोरी से कोरकू</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="official-img-card text-center p-2 bg-light">
                            <span class="small fw-bold text-dark d-block mb-1">Page 2 (Codes 33 - 66)</span>
                            <a href="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/b33ae9fd-79cf-4ea0-8876-c70599e2d68a/code2.jpg" target="_blank" rel="noopener">
                                <img src="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/b33ae9fd-79cf-4ea0-8876-c70599e2d68a/code2.jpg" alt="Bihar Caste Code Page 2" class="img-fluid rounded border" style="max-height: 180px; object-fit: contain;">
                            </a>
                            <small class="text-muted d-block mt-1">कोरवा से चौपाल</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="official-img-card text-center p-2 bg-light">
                            <span class="small fw-bold text-dark d-block mb-1">Page 3 (Codes 67 - 99)</span>
                            <a href="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/8dfd91df-35d6-4251-8a4e-a421b747008b/code3.jpg" target="_blank" rel="noopener">
                                <img src="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/8dfd91df-35d6-4251-8a4e-a421b747008b/code3.jpg" alt="Bihar Caste Code Page 3" class="img-fluid rounded border" style="max-height: 180px; object-fit: contain;">
                            </a>
                            <small class="text-muted d-block mt-1">छीपी से नाई</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="official-img-card text-center p-2 bg-light">
                            <span class="small fw-bold text-dark d-block mb-1">Page 4 (Codes 100 - 130)</span>
                            <a href="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/96b2a167-f17d-4a53-a62c-aba32fd3f612/code4.jpg" target="_blank" rel="noopener">
                                <img src="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/96b2a167-f17d-4a53-a62c-aba32fd3f612/code4.jpg" alt="Bihar Caste Code Page 4" class="img-fluid rounded border" style="max-height: 180px; object-fit: contain;">
                            </a>
                            <small class="text-muted d-block mt-1">नागर से बेगा</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4 col-lg">
                        <div class="official-img-card text-center p-2 bg-light">
                            <span class="small fw-bold text-dark d-block mb-1">Page 5 (Codes 131 - 164)</span>
                            <a href="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/dc0037be-ad99-4512-a300-c4081a55e4b6/code5.jpg" target="_blank" rel="noopener">
                                <img src="https://wpmedia.prabhatkhabar.com/uploads/prod-qt-images/2023-04/dc0037be-ad99-4512-a300-c4081a55e4b6/code5.jpg" alt="Bihar Caste Code Page 5" class="img-fluid rounded border" style="max-height: 180px; object-fit: contain;">
                            </a>
                            <small class="text-muted d-block mt-1">बेदिया से मौलिक</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Official Government Data Links & Verified Sources Hub (Official Portals, Assembly & Gazette) -->
        <div class="card border-0 shadow-sm rounded-4 mb-5" id="govt-data-links">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-link-45deg text-primary me-2"></i> Official Government Portals &amp; Verified Data Links (आधिकारिक स्रोत एवं लिंक)
                    </h3>
                    <small class="text-muted">Primary government repositories, court judgments, assembly records, and authentic data sources</small>
                </div>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 fw-bold">
                    <i class="bi bi-patch-check-fill text-primary"></i> Verified References
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($govtLinks as $gLink): ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <span class="badge bg-white text-dark border small fw-bold">
                                        <i class="bi <?php echo $gLink['icon']; ?> text-primary me-1"></i> <?php echo $gLink['type']; ?>
                                    </span>
                                    <span class="badge bg-primary bg-opacity-10 text-primary small"><?php echo $gLink['badge']; ?></span>
                                </div>
                                <h6 class="fw-bold text-dark mb-1 fs-6">
                                    <?php echo $gLink['title']; ?>
                                </h6>
                                <div class="small text-muted mb-2 fw-semibold" style="font-size: 0.76rem;">
                                    <?php echo $gLink['title_hi']; ?>
                                </div>
                                <p class="small text-muted mb-3" style="font-size: 0.82rem; line-height: 1.45;">
                                    <?php echo $gLink['desc']; ?>
                                </p>
                            </div>
                            <div>
                                <a href="<?php echo $gLink['url']; ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary w-100 d-flex align-items-center justify-content-center gap-1 fw-bold">
                                    <span>Visit Official Source</span>
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Frequently Asked Questions (FAQs) Accordion -->
        <div class="card border-0 shadow-sm rounded-4 mb-5" id="caste-faqs">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-question-circle-fill text-warning me-2"></i> Frequently Asked Questions (अक्सर पूछे जाने वाले प्रश्न)
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="accordion" id="casteFaqAccordion">
                    
                    <!-- FAQ 1 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqHeading1">
                            <button class="accordion-button fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1" aria-expanded="true" aria-controls="faqCollapse1">
                                बिहार में जातिगत जनगणना (Caste Survey) के लिए जाति कोड क्यों जारी किए गए थे?
                            </button>
                        </h2>
                        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#casteFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                बिहार सरकार के सामान्य प्रशासन विभाग (GAD) ने 15 अप्रैल 2023 से शुरू हुए द्वितीय चरण के लिए जातियों के नाम की स्पेलिंग और डेटा प्रविष्टि (Digital Data Entry via Bijaga App) में एकरूपता बनाए रखने के लिए 215 जातियों को विशिष्ट संख्यात्मक कोड (1 से 215) आवंटित किए थे। किसी अन्य अनिर्दिष्ट जाति के लिए कोड 216 निर्धारित किया गया।
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqHeading2">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2" aria-expanded="false" aria-controls="faqCollapse2">
                                मुख्य जातियों जैसे यादव, ब्राह्मण, राजपूत, बनिया, कुशवाहा, कुर्मी का कोड क्या है?
                            </button>
                        </h2>
                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#casteFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                मुख्य जातियों के आधिकारिक कोड निम्नलिखित हैं:
                                <ul class="mb-0 mt-2">
                                    <li><strong>यादव (ग्वाला, अहीर):</strong> कोड 165</li>
                                    <li><strong>ब्राह्मण:</strong> कोड 126</li>
                                    <li><strong>राजपूत:</strong> कोड 169</li>
                                    <li><strong>भूमिहार:</strong> कोड 142</li>
                                    <li><strong>कुशवाहा (कोईरी):</strong> कोड 26</li>
                                    <li><strong>कुर्मी:</strong> कोड 24</li>
                                    <li><strong>बनिया (सूढ़ी, रोनियार, मोदी, कलवार, आदि):</strong> कोड 122</li>
                                    <li><strong>दुसाध / पासवान:</strong> कोड 87</li>
                                    <li><strong>रविदास / चमार / मोची:</strong> कोड 60</li>
                                    <li><strong>मोमिन / अंसारी (मुस्लिम):</strong> कोड 161</li>
                                    <li><strong>कायस्थ:</strong> कोड 21</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqHeading3">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3" aria-expanded="false" aria-controls="faqCollapse3">
                                बिहार में सर्वाधिक आबादी किस सामाजिक वर्ग और जाति की है?
                            </button>
                        </h2>
                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#casteFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                2 अक्टूबर 2023 को जारी आधिकारिक रिपोर्ट के अनुसार, सामाजिक वर्गों में <strong>अत्यंत पिछड़ा वर्ग (EBC)</strong> 36.01% के साथ सबसे बड़ा समूह है। व्यक्तिगत जातियों में <strong>यादव (14.27%)</strong> सबसे बड़ी जाति है, जिसके बाद दुसाध/पासवान (5.31%), रविदास/चमार (5.25%), कुशवाहा (4.21%), शेख (3.82%), ब्राह्मण (3.66%), अंसारी (3.55%), राजपूत (3.45%), और मुसहर (3.09%) आते हैं।
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqHeading4">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4" aria-expanded="false" aria-controls="faqCollapse4">
                                बिहार जाति गणना के सामाजिक-आर्थिक और गरीबी आंकड़े क्या दर्शाते हैं?
                            </button>
                        </h2>
                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#casteFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                7 नवंबर 2023 को विधानसभा में पेश रिपोर्ट के अनुसार, बिहार के 34.13% परिवार (94.42 लाख परिवार) ₹6,000 प्रति माह से कम कमाते हैं। अनुसूचित जाति में गरीबी दर सर्वाधिक 42.93% और अनुसूचित जनजाति में 42.70% है। ईबीसी में 33.58%, ओबीसी में 33.16%, और सामान्य वर्ग में 25.09% परिवार गरीब हैं। राज्य की कुल आबादी का मात्र 1.57% (20.49 लाख लोग) ही सरकारी नौकरी में हैं।
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 5 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqHeading5">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5" aria-expanded="false" aria-controls="faqCollapse5">
                                पटना उच्च न्यायालय (Patna High Court) ने 65% आरक्षण कानून पर क्या फैसला दिया है?
                            </button>
                        </h2>
                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#casteFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                माननीय पटना उच्च न्यायालय की मुख्य न्यायाधीश के. विनोद चन्द्रन एवं न्यायमूर्ति हरीश कुमार की खंडपीठ ने 20 जून 2024 को <strong>CWJC संख्या 16760/2023 (गौरव कुमार बनाम बिहार राज्य)</strong> में ऐतिहासिक निर्णय देते हुए बिहार आरक्षण (संशोधन) अधिनियम 2023 को असंवैधानिक घोषित कर रद्द (Quash) कर दिया। न्यायालय ने पाया कि 50% की संवैधानिक सीमा का उल्लंघन बिना किसी असाधारण परिस्थिति के किया गया था। उच्चतम न्यायालय (Supreme Court) ने 29 जुलाई 2024 को इस पर अंतरिम रोक लगाने से इनकार कर दिया। अतः वर्तमान में बिहार में 50% राज्य आरक्षण + 10% EWS = <strong>60% आरक्षण ही प्रभावी</strong> है।
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 6 -->
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="faqHeading6">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse6" aria-expanded="false" aria-controls="faqCollapse6">
                                क्या जाति प्रमाण पत्र या सरकारी प्रपत्रों में इस कोड का उपयोग होता है?
                            </button>
                        </h2>
                        <div id="faqCollapse6" class="accordion-collapse collapse" aria-labelledby="faqHeading6" data-bs-parent="#casteFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                यह कोड मुख्य रूप से 2022-2023 बिहार जाति आधारित गणना के डिजिटल डेटाबेस, एप, और सरकारी पोर्टल प्रविष्टि के लिए तैयार किया गया था। जाति प्रमाण पत्र (RTPS) एवं आरक्षण कोटि निर्धारण के लिए राज्य सरकार द्वारा अधिसूचित EBC (अनुसूची-1), BC (अनुसूची-2), SC, और ST की आधिकारिक सूचियों के साथ इसका प्रत्यक्ष मिलान होता है।
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>

</main>

<!-- Copy Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="copyToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-warning fs-5"></i>
                <div>
                    <strong id="toastTitle">Code Copied!</strong>
                    <div class="small text-white-50" id="toastMessage">Copied to clipboard.</div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Structured Data: JSON-LD Schema (Multi-Entity: Dataset, BreadcrumbList, FAQPage, WebPage, ItemList) -->
<script type="application/ld+json">
[
  {
    "@context": "https://schema.org",
    "@type": "Dataset",
    "name": "2022 Bihar Caste-Based Survey Master Caste Codes & Demographics Directory",
    "alternateName": "बिहार जाति आधारित गणना 2022-2023: जाति कोड सूची एवं जनसांख्यिकी",
    "description": "Comprehensive list of all 215+ official caste codes, socioeconomic indicators, monthly family income distribution, category-wise poverty rates, and social category demographics published by the General Administration Department (GAD), Government of Bihar.",
    "url": "<?php echo getCasteSurveyUrl(); ?>",
    "keywords": [
      "Bihar Caste Census",
      "Bihar Caste Codes",
      "Jati Code List Bihar",
      "EBC BC SC ST Demographics Bihar",
      "2022 Bihar Caste Survey",
      "Patna High Court Caste Reservation Verdict",
      "Yadav Caste Code",
      "Brahmin Caste Code",
      "Rajput Caste Code",
      "Kushwaha Caste Code"
    ],
    "creator": {
      "@type": "Organization",
      "name": "Bihar Election Data Platform",
      "url": "<?php echo SITE_URL; ?>"
    },
    "publisher": {
      "@type": "GovernmentOrganization",
      "name": "General Administration Department (GAD), Government of Bihar (सामान्य प्रशासन विभाग, बिहार सरकार)",
      "url": "https://gad.bihar.gov.in/"
    },
    "temporalCoverage": "2022/2024",
    "spatialCoverage": {
      "@type": "Place",
      "name": "Bihar, India",
      "geo": {
        "@type": "GeoCoordinates",
        "latitude": 25.0961,
        "longitude": 85.3131
      }
    },
    "distribution": [
      {
        "@type": "DataDownload",
        "encodingFormat": "text/csv",
        "contentUrl": "<?php echo getCasteSurveyUrl(); ?>"
      }
    ]
  },
  {
    "@context": "https://schema.org",
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
        "name": "Census & Demographics",
        "item": "<?php echo getCensusUrl(); ?>"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "Bihar Caste Survey 2022 (215+ Caste Codes)",
        "item": "<?php echo getCasteSurveyUrl(); ?>"
      }
    ]
  },
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "बिहार जाति आधारित गणना में जातियों के लिए कोड क्यों निर्धारित किए गए थे?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "सामान्य प्रशासन विभाग (GAD), बिहार सरकार द्वारा दूसरे चरण की डिजिटल गणना में सभी 215 अधिसूचित जातियों और अन्य राज्यों के निवासियों (कोड 216) के लिए विशिष्ट संख्यात्मक कोड (1 से 216) निर्धारित किए गए थे ताकि BIJAGA मोबाइल ऐप और सर्वर प्रविष्टि में कोई मानवीय त्रुटि न हो।"
        }
      },
      {
        "@type": "Question",
        "name": "प्रमुख जातियों के आधिकारिक कोड क्या हैं?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "प्रमुख जातियों के आधिकारिक कोड: यादव (165), ब्राह्मण (126), राजपूत (169), भूमिहार (142), कुशवाहा/कोइरी (26), कुर्मी (24), बनिया (122), तेली (83), मल्लाह/निषाद (148), रविदास/चमार (60), दुसाध/पासवान (87), मुसहर (158), अंसारी/मोमिन (161), शेख (181), पठान (105), कायस्थ (21), सुरजापुरी मुस्लिम (187) और अन्य राज्य निवासी (216)।"
        }
      },
      {
        "@type": "Question",
        "name": "बिहार जाति गणना 2022-2023 के अनुसार राज्य में सर्वाधिक आबादी किस सामाजिक वर्ग की है?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "2 अक्टूबर 2023 को जारी आधिकारिक रिपोर्ट के अनुसार, अत्यंत पिछड़ा वर्ग (EBC) 36.01% के साथ सबसे बड़ा समूह है। इसके बाद पिछड़ा वर्ग (BC) 27.13%, अनुसूचित जाति (SC) 19.65%, सामान्य वर्ग 15.52%, और अनुसूचित जनजाति (ST) 1.68% है। व्यक्तिगत जातियों में यादव (14.27%) सबसे बड़ी जाति है।"
        }
      },
      {
        "@type": "Question",
        "name": "पटना उच्च न्यायालय (Patna High Court) ने 65% आरक्षण कानून पर क्या फैसला दिया है?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "माननीय पटना उच्च न्यायालय की खंडपीठ ने 20 जून 2024 को CWJC संख्या 16760/2023 (गौरव कुमार बनाम बिहार राज्य) में ऐतिहासिक निर्णय देते हुए 65% राज्य आरक्षण अधिनियम 2023 को असंवैधानिक घोषित कर रद्द कर दिया। उच्चतम न्यायालय ने 29 जुलाई 2024 को अंतरिम रोक से इनकार किया। वर्तमान में 50% राज्य आरक्षण + 10% EWS = 60% आरक्षण ही प्रभावी है।"
        }
      },
      {
        "@type": "Question",
        "name": "बिहार जाति गणना के सामाजिक-आर्थिक और गरीबी आंकड़े क्या दर्शाते हैं?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "7 नवंबर 2023 को विधानसभा में पेश रिपोर्ट के अनुसार, बिहार के 34.13% परिवार (94.42 लाख परिवार) ₹6,000 प्रति माह से कम कमाते हैं। अनुसूचित जाति में गरीबी दर सर्वाधिक 42.93% और अनुसूचित जनजाति में 42.70% है। राज्य की कुल आबादी का मात्र 1.57% (20.49 लाख लोग) सरकारी नौकरी में हैं।"
        }
      }
    ]
  },
  {
    "@context": "https://schema.org",
    "@type": "ItemList",
    "name": "Bihar Social Category Population Distribution (2022-2023 Survey)",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Extremely Backward Classes (EBC / BC-1)",
        "description": "36.01% Share, 4,70,80,514 Population (112 Castes)"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Backward Classes (BC / BC-2 / OBC)",
        "description": "27.13% Share, 3,54,63,936 Population (29 Castes)"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "Scheduled Castes (SC)",
        "description": "19.65% Share, 2,56,89,820 Population (22 Castes)"
      },
      {
        "@type": "ListItem",
        "position": 4,
        "name": "General / Unreserved (GEN)",
        "description": "15.52% Share, 2,02,91,079 Population (16 Castes)"
      },
      {
        "@type": "ListItem",
        "position": 5,
        "name": "Scheduled Tribes (ST)",
        "description": "1.68% Share, 21,99,361 Population (32 Castes)"
      }
    ]
  }
]
</script>

<!-- Client-side Interactive Search, Filter & Nav Highlighting Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('casteSearchInput');
    const clearSearchBtn = document.getElementById('clearSearchBtn');
    const sortSelect = document.getElementById('casteSortSelect');
    const catButtons = document.querySelectorAll('.filter-cat-btn');
    const relButtons = document.querySelectorAll('.filter-rel-btn');
    const sortQuickButtons = document.querySelectorAll('.sort-quick-btn');
    const quickChips = document.querySelectorAll('.quick-chip');
    const tableBody = document.getElementById('casteTableBody');
    const rows = Array.from(document.querySelectorAll('.caste-row'));
    const resultsCountBadge = document.getElementById('resultsCountBadge');
    const noResultsState = document.getElementById('noResultsState');
    const masterTable = document.getElementById('casteMasterTable');
    const exportCsvBtn = document.getElementById('exportCsvBtn');
    const sortableHeaders = document.querySelectorAll('.sortable-th');

    let currentCategory = '<?php echo $selectedCategory; ?>' || 'ALL';
    let currentReligion = '<?php echo $selectedReligion; ?>' || 'ALL';
    let currentSearch = (searchInput.value || '').trim().toLowerCase();
    let currentSort = '<?php echo $selectedSort; ?>' || 'code_asc';

    const categoryHierarchy = {
        'EBC': 1,
        'BC': 2,
        'SC': 3,
        'ST': 4,
        'GEN': 5,
        'OTHER': 6
    };

    const religionHierarchy = {
        'hindu': 1,
        'muslim': 2,
        'islam': 2,
        'christian': 3,
        'sikh': 4,
        'jain': 5,
        'buddhist': 6,
        'other': 7
    };

    function updateClearBtn() {
        if (searchInput.value.trim().length > 0) {
            clearSearchBtn.style.display = 'block';
        } else {
            clearSearchBtn.style.display = 'none';
        }
    }

    function filterAndSortRows() {
        let visibleCount = 0;
        updateClearBtn();

        rows.forEach(row => {
            const code = row.dataset.code || '';
            const nameHi = (row.dataset.nameHi || '').toLowerCase();
            const nameEn = (row.dataset.nameEn || '').toLowerCase();
            const subcastes = (row.dataset.subcastes || '').toLowerCase();
            const category = (row.dataset.category || '').toUpperCase();
            const religion = (row.dataset.religion || '').toLowerCase();

            // Category match
            let catMatch = false;
            if (currentCategory === 'ALL') {
                catMatch = true;
            } else if (currentCategory === 'OTHER') {
                catMatch = (category === 'OTHER' || !['EBC', 'BC', 'SC', 'ST', 'GEN'].includes(category));
            } else {
                catMatch = (category === currentCategory);
            }

            // Religion match
            let relMatch = false;
            if (currentReligion === 'ALL') {
                relMatch = true;
            } else if (currentReligion === 'HINDU') {
                relMatch = religion.includes('hindu');
            } else if (currentReligion === 'MUSLIM') {
                relMatch = religion.includes('muslim') || religion.includes('islam');
            } else if (currentReligion === 'CHRISTIAN') {
                relMatch = religion.includes('christian');
            } else if (currentReligion === 'SIKH') {
                relMatch = religion.includes('sikh');
            } else if (currentReligion === 'JAIN') {
                relMatch = religion.includes('jain');
            } else if (currentReligion === 'BUDDHIST') {
                relMatch = religion.includes('buddhist');
            } else if (currentReligion === 'OTHER') {
                relMatch = !religion.includes('hindu') && !religion.includes('muslim') && !religion.includes('islam');
            }

            // Search query match
            let searchMatch = true;
            if (currentSearch) {
                searchMatch = code.includes(currentSearch) ||
                              nameHi.includes(currentSearch) ||
                              nameEn.includes(currentSearch) ||
                              subcastes.includes(currentSearch) ||
                              category.toLowerCase().includes(currentSearch) ||
                              religion.includes(currentSearch);
            }

            if (catMatch && relMatch && searchMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update count badge
        resultsCountBadge.textContent = `${visibleCount} Castes Listed`;

        // Handle empty state
        if (visibleCount === 0) {
            noResultsState.classList.remove('d-none');
            masterTable.classList.add('d-none');
        } else {
            noResultsState.classList.add('d-none');
            masterTable.classList.remove('d-none');
        }
    }

    // Comprehensive Sorting Function
    function sortTable(criteria) {
        currentSort = criteria;
        if (sortSelect.value !== criteria) {
            sortSelect.value = criteria;
        }

        // Update quick sort button active styles
        sortQuickButtons.forEach(btn => {
            if (btn.dataset.sort === criteria) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });

        // Update header active states
        sortableHeaders.forEach(th => {
            const sortKey = th.dataset.sortKey;
            const icon = th.querySelector('.sort-icon');
            if (!icon) return;

            th.classList.remove('active-sort');
            icon.className = 'bi bi-arrow-down-up sort-icon';

            if ((sortKey === 'code' && (criteria === 'code_asc' || criteria === 'code_desc')) ||
                (sortKey === 'name' && (criteria === 'name_asc' || criteria === 'name_desc')) ||
                (sortKey === 'cat' && (criteria === 'cat_asc' || criteria === 'cat_desc')) ||
                (sortKey === 'rel' && (criteria === 'rel_asc' || criteria === 'rel_alpha' || criteria === 'rel_desc')) ||
                (sortKey === 'pop' && (criteria === 'pop_desc' || criteria === 'pop_asc'))) {
                
                th.classList.add('active-sort');
                if (criteria.endsWith('_desc')) {
                    icon.className = 'bi bi-sort-down-alt sort-icon text-warning';
                } else {
                    icon.className = 'bi bi-sort-up sort-icon text-warning';
                }
            }
        });

        const sorted = rows.slice().sort((a, b) => {
            const codeA = parseInt(a.dataset.code) || 0;
            const codeB = parseInt(b.dataset.code) || 0;
            const nameEnA = (a.dataset.nameEn || '').toLowerCase();
            const nameEnB = (b.dataset.nameEn || '').toLowerCase();
            const nameHiA = (a.dataset.nameHi || '').toLowerCase();
            const nameHiB = (b.dataset.nameHi || '').toLowerCase();
            const catA = (a.dataset.category || '').toUpperCase();
            const catB = (b.dataset.category || '').toUpperCase();
            const relA = (a.dataset.religion || '').toLowerCase();
            const relB = (b.dataset.religion || '').toLowerCase();
            const popA = parseFloat(a.dataset.percentage || 0);
            const popB = parseFloat(b.dataset.percentage || 0);

            if (criteria === 'code_asc') {
                return codeA - codeB;
            } else if (criteria === 'code_desc') {
                return codeB - codeA;
            } else if (criteria === 'name_asc') {
                return nameEnA.localeCompare(nameEnB) || nameHiA.localeCompare(nameHiB);
            } else if (criteria === 'name_desc') {
                return nameEnB.localeCompare(nameEnA) || nameHiB.localeCompare(nameHiA);
            } else if (criteria === 'cat_asc') {
                const rankA = categoryHierarchy[catA] || 99;
                const rankB = categoryHierarchy[catB] || 99;
                if (rankA !== rankB) return rankA - rankB;
                return codeA - codeB;
            } else if (criteria === 'cat_desc') {
                const rankA = categoryHierarchy[catA] || 99;
                const rankB = categoryHierarchy[catB] || 99;
                if (rankA !== rankB) return rankB - rankA;
                return codeA - codeB;
            } else if (criteria === 'rel_asc') {
                const getRelRank = (r) => {
                    for (const k in religionHierarchy) {
                        if (r.includes(k)) return religionHierarchy[k];
                    }
                    return 99;
                };
                const rA = getRelRank(relA);
                const rB = getRelRank(relB);
                if (rA !== rB) return rA - rB;
                return codeA - codeB;
            } else if (criteria === 'rel_alpha') {
                return relA.localeCompare(relB) || codeA - codeB;
            } else if (criteria === 'rel_desc') {
                return relB.localeCompare(relA) || codeA - codeB;
            } else if (criteria === 'pop_desc') {
                if (popB !== popA) return popB - popA;
                return codeA - codeB;
            } else if (criteria === 'pop_asc') {
                if (popA !== popB) return popA - popB;
                return codeA - codeB;
            }
            return codeA - codeB;
        });

        sorted.forEach(row => tableBody.appendChild(row));
    }

    // Search event
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.trim().toLowerCase();
        quickChips.forEach(c => c.classList.remove('active'));
        filterAndSortRows();
    });

    // Clear Search button
    clearSearchBtn.addEventListener('click', function() {
        searchInput.value = '';
        currentSearch = '';
        filterAndSortRows();
        searchInput.focus();
    });

    // Category Filter Buttons
    catButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            catButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentCategory = this.dataset.category;
            filterAndSortRows();
        });
    });

    // Religion Filter Buttons
    relButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            relButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentReligion = this.dataset.religion;
            filterAndSortRows();
        });
    });

    // Quick Sort Buttons
    sortQuickButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetSort = this.dataset.sort;
            sortTable(targetSort);
        });
    });

    // Sortable Table Column Headers
    sortableHeaders.forEach(th => {
        th.addEventListener('click', function() {
            const sortKey = this.dataset.sortKey;
            let nextSort = 'code_asc';

            if (sortKey === 'code') {
                nextSort = (currentSort === 'code_asc') ? 'code_desc' : 'code_asc';
            } else if (sortKey === 'name') {
                nextSort = (currentSort === 'name_asc') ? 'name_desc' : 'name_asc';
            } else if (sortKey === 'cat') {
                nextSort = (currentSort === 'cat_asc') ? 'cat_desc' : 'cat_asc';
            } else if (sortKey === 'rel') {
                nextSort = (currentSort === 'rel_asc') ? 'rel_desc' : 'rel_asc';
            } else if (sortKey === 'pop') {
                nextSort = (currentSort === 'pop_desc') ? 'pop_asc' : 'pop_desc';
            }

            sortTable(nextSort);
        });
    });

    // Quick Chips
    quickChips.forEach(chip => {
        chip.addEventListener('click', function() {
            quickChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            searchInput.value = this.dataset.term;
            currentSearch = this.dataset.term.toLowerCase();
            filterAndSortRows();
            
            // Scroll table into view
            masterTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Sort select change event
    sortSelect.addEventListener('change', function() {
        sortTable(this.value);
    });

    // Reset Function
    window.resetSearch = function() {
        searchInput.value = '';
        currentSearch = '';
        currentCategory = 'ALL';
        currentReligion = 'ALL';
        catButtons.forEach(b => b.classList.remove('active'));
        document.querySelector('.filter-cat-btn[data-category="ALL"]').classList.add('active');
        relButtons.forEach(b => b.classList.remove('active'));
        document.querySelector('.filter-rel-btn[data-religion="ALL"]').classList.add('active');
        quickChips.forEach(c => c.classList.remove('active'));
        sortTable('code_asc');
        filterAndSortRows();
    };

    // CSV Export Handler
    exportCsvBtn.addEventListener('click', function() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Code,Caste Name (Hindi),Caste Name (English),Category,Religion,Population Share (%),Population Count,Sub-Castes\n";
        
        rows.forEach(r => {
            if (r.style.display !== 'none') {
                const code = r.dataset.code;
                const nameHi = `"${(r.dataset.nameHi || '').replace(/"/g, '""')}"`;
                const nameEn = `"${(r.dataset.nameEn || '').replace(/"/g, '""')}"`;
                const cat = `"${r.dataset.category}"`;
                const rel = `"${r.dataset.religion}"`;
                const pct = r.dataset.percentage || '0';
                const pop = r.dataset.population || '0';
                const subs = `"${(r.dataset.subcastes || '').replace(/"/g, '""')}"`;

                csvContent += `${code},${nameHi},${nameEn},${cat},${rel},${pct},${pop},${subs}\n`;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "bihar_caste_codes_2022_survey.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Initial run
    updateClearBtn();
    sortTable(currentSort);
    if (currentSearch || currentCategory !== 'ALL' || currentReligion !== 'ALL') {
        filterAndSortRows();
    }

    // Smooth active scrollspy for sticky nav pills with mobile horizontal auto-center
    const navLinks = document.querySelectorAll('.caste-nav-pills .nav-link');
    const navPillsContainer = document.querySelector('.caste-nav-pills');
    const sections = Array.from(navLinks).map(link => {
        const targetId = link.getAttribute('href').replace('#', '');
        return document.getElementById(targetId);
    }).filter(Boolean);

    function centerActiveNavPill(activeLink) {
        if (!navPillsContainer || !activeLink) return;
        const containerRect = navPillsContainer.getBoundingClientRect();
        const linkRect = activeLink.getBoundingClientRect();
        const offset = (linkRect.left - containerRect.left) - (containerRect.width / 2) + (linkRect.width / 2);
        navPillsContainer.scrollBy({ left: offset, behavior: 'smooth' });
    }

    let isUserClickingNav = false;
    let scrollSpyTimer = null;

    window.addEventListener('scroll', () => {
        if (isUserClickingNav) return;
        if (scrollSpyTimer) return;
        
        scrollSpyTimer = setTimeout(() => {
            scrollSpyTimer = null;
            const isMobile = window.innerWidth < 992;
            const scrollPosition = window.scrollY + (isMobile ? 120 : 150);
            let currentSectionId = '';

            sections.forEach(section => {
                if (section.offsetTop <= scrollPosition && (section.offsetTop + section.offsetHeight) > scrollPosition) {
                    currentSectionId = section.id;
                }
            });

            if (currentSectionId) {
                navLinks.forEach(link => {
                    if (link.getAttribute('href') === `#${currentSectionId}`) {
                        if (!link.classList.contains('active')) {
                            link.classList.add('active');
                            centerActiveNavPill(link);
                        }
                    } else {
                        link.classList.remove('active');
                    }
                });
            }
        }, 50);
    }, { passive: true });

    // Center pill immediately upon user click
    navLinks.forEach(link => {
        link.addEventListener('click', function() {
            isUserClickingNav = true;
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');
            centerActiveNavPill(this);
            setTimeout(() => {
                isUserClickingNav = false;
            }, 800);
        });
    });
});

// Copy Caste Code Function
function copyCasteCode(code, name) {
    const textToCopy = `Bihar Caste Code: ${code} - ${name}`;
    navigator.clipboard.writeText(textToCopy).then(function() {
        const toastEl = document.getElementById('copyToast');
        document.getElementById('toastTitle').textContent = `Code #${code} Copied!`;
        document.getElementById('toastMessage').textContent = `${name} (Code: ${code}) copied to clipboard.`;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }).catch(function(err) {
        console.error('Copy failed: ', err);
    });
}
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
