<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();
// Sort districts alphabetically A-Z
usort($districts, function($a, $b) {
    return strcmp($a['name'] ?? '', $b['name'] ?? '');
});

$constituencies = DataProvider::getConstituencies();
$candidates = DataProvider::getCandidates();
$news = DataProvider::getNews();
$panchayats = DataProvider::getPanchayatData();

$pageTitle = 'Bihar Election 2026: 243 Assembly Data, 38 Districts & Panchayat Delimitation Platform';
$pageDescription = 'Bihar\'s comprehensive non-government election data platform. Explore all 243 Assembly Constituencies, 38 District Hubs (Patna, Muzaffarpur, Gaya, Bhagalpur), 2026 Panchayat Delimitation status & verified MLA profiles.';
$pageKeywords = 'Bihar Election 2026, 243 Bihar Assembly Constituencies, Patna Vidhan Sabha, Bihar Election Results, 38 Districts Bihar, Bihar Panchayat 2026, Bihar MLA list, Bihar Political Data';
$pageCanonical = SITE_URL . '/';
$activeNav = 'home';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Section with Bootstrap 5.3 Responsive Grid -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-center py-2 py-lg-3">
            <div class="hero-badge mb-3">
                <i class="bi bi-shield-check"></i> Non-Government Election Data Platform
            </div>
            <h1 class="hero-title display-5 fw-extrabold mb-3">
                Bihar's Premier <br>
                <span>Election Data &amp; Intelligence Hub</span>
            </h1>
            <p class="hero-subtitle lead text-white-50 mb-4 mx-auto" style="max-width: 820px;">
                Covering Panchayat to Parliament: 38 Districts, 243 Assembly Constituencies, 8,000+ Gram Panchayats, and 2026 Delimitation intelligence with verified historical records across all of Bihar.
            </p>

            <!-- Search Hub Widget with Dynamic Suggestions Dropdown -->
            <div class="search-widget mx-auto" style="max-width: 680px;">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        id="globalSearchInput" 
                        class="search-input" 
                        placeholder="Search MLA, MP, Mukhiya, Sarpanch, District, AC..."
                        autocomplete="off"
                        aria-label="Search Bihar election leaders and administrative units"
                    >
                    <button class="btn-search" type="button" onclick="document.getElementById('globalSearchInput').focus()">
                        <i class="bi bi-search"></i> <span class="d-none d-sm-inline">Search</span>
                    </button>
                </div>

                <!-- Instant Dynamic Suggestions Dropdown -->
                <div id="searchDropdown" class="search-dropdown"></div>
            </div>

            <!-- Quick Pill Links (Mobile-first responsive chips) -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-1.5 gap-sm-2 mt-3 pt-1 hero-quick-pills">
                <span class="small text-white-50 me-1 fw-semibold"><i class="bi bi-fire text-warning"></i> Popular:</span>
                <a href="<?php echo getDistrictUrl('patna'); ?>" class="pill-link fw-bold text-warning">👑 Patna Hub</a>
                <a href="<?php echo getVillageUrl(); ?>" class="pill-link fw-bold text-info">🏘️ 44,874 Villages</a>
                <a href="<?php echo getTownUrl(); ?>" class="pill-link fw-bold text-primary">🏙️ 199 Towns &amp; Slums</a>
                <a href="<?php echo getCasteSurveyUrl(); ?>" class="pill-link fw-bold text-warning">📋 2022 Caste Survey</a>
                <a href="<?php echo getBiharActsUrl(); ?>" class="pill-link fw-bold text-success">⚖️ Bihar Acts (1937–2026)</a>
                <a href="<?php echo SITE_URL; ?>/mla" class="pill-link">🗳️ 243 MLAs</a>
                <a href="<?php echo SITE_URL; ?>/mp" class="pill-link">🏛️ 40 MPs</a>
                <a href="<?php echo SITE_URL; ?>/mlc" class="pill-link">📜 75 MLCs</a>
                <a href="<?php echo getZilaParishadUrl(); ?>" class="pill-link">🏛️ Zila Parishad</a>
                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="pill-link">🏢 534 Pramukhs</a>
                <a href="<?php echo SITE_URL; ?>/mukhiya" class="pill-link">🌾 8,053+ Mukhiyas</a>
                <a href="<?php echo SITE_URL; ?>/sarpanch" class="pill-link">⚖️ 8,053+ Sarpanchs</a>
            </div>
        </div>
    </section>

    <!-- Live Governance & Electoral Stat Grid Bar (Mobile-first 2-col / 4-col / 8-col Grid) -->
    <div class="container governance-stat-container" style="margin-top: -28px; position: relative; z-index: 10;">
        <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-4 row-cols-xl-4 row-cols-xxl-8">
            <!-- 1: Assembly MLAs -->
            <div class="col">
                <a href="<?php echo SITE_URL; ?>/mla" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-mla">
                            🗳️
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            Vidhan Sabha
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">243</div>
                        <div class="fw-bold text-dark stat-title mt-1">Assembly MLAs</div>
                        <div class="stat-subtitle text-muted">243 Constituencies</div>
                    </div>
                </a>
            </div>

            <!-- 2: Lok Sabha MPs -->
            <div class="col">
                <a href="<?php echo SITE_URL; ?>/mp" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-mp">
                            🏛️
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            Lok Sabha
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">40</div>
                        <div class="fw-bold text-dark stat-title mt-1">Parliament MPs</div>
                        <div class="stat-subtitle text-muted">40 Parliamentary Seats</div>
                    </div>
                </a>
            </div>

            <!-- 3: Zila Parishad Adhyaksh -->
            <div class="col">
                <a href="<?php echo getZilaParishadUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-zp">
                            📍
                        </div>
                        <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill" style="color: #7c3aed; background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.25) !important;">
                            1,153+ ZP
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">38</div>
                        <div class="fw-bold text-dark stat-title mt-1">ZP Adhyaksh</div>
                        <div class="stat-subtitle text-muted">38 District Boards</div>
                    </div>
                </a>
            </div>

            <!-- 4: Pramukh / Up-Pramukh -->
            <div class="col">
                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-ps">
                            🏢
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            534 Blocks
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">534</div>
                        <div class="fw-bold text-dark stat-title mt-1">Block Pramukhs</div>
                        <div class="stat-subtitle text-muted">Panchayat Samitis</div>
                    </div>
                </a>
            </div>

            <!-- 5: Mukhiya & Sarpanch -->
            <div class="col">
                <a href="<?php echo SITE_URL; ?>/mukhiya" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-gp">
                            🌾
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            Gram Panchayat
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">8,053+</div>
                        <div class="fw-bold text-dark stat-title mt-1">Mukhiya &amp; Sarpanch</div>
                        <div class="stat-subtitle text-muted">Village Council Heads</div>
                    </div>
                </a>
            </div>

            <!-- 6: Statutory Towns & Slums -->
            <div class="col">
                <a href="<?php echo getTownUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-town">
                            🏙️
                        </div>
                        <span class="badge bg-indigo bg-opacity-10 text-indigo border border-indigo border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill" style="color: #4338ca; background: #e0e7ff;">
                            Urban ULBs
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">199</div>
                        <div class="fw-bold text-dark stat-title mt-1">Towns &amp; Slums</div>
                        <div class="stat-subtitle text-muted">670 Slum Settlements</div>
                    </div>
                </a>
            </div>

            <!-- 7: Census Villages -->
            <div class="col">
                <a href="<?php echo getVillageUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-village">
                            🏘️
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            Census 2011
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">44,874</div>
                        <div class="fw-bold text-dark stat-title mt-1">Rural Villages</div>
                        <div class="stat-subtitle text-muted">38 Districts Matrix</div>
                    </div>
                </a>
            </div>

            <!-- 8: Total Electors -->
            <div class="col">
                <a href="<?php echo SITE_URL; ?>/census" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-voters">
                            👥
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            Electorate
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">7.64 Cr+</div>
                        <div class="fw-bold text-dark stat-title mt-1">Total Electors</div>
                        <div class="stat-subtitle text-muted">Bihar Voters</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="container my-5">

        <!-- Bihar 3-Tier Panchayati Raj Leadership & Local Governance Structure -->
        <section class="my-5 py-2">
            <!-- Section Header with Clear Spacing -->
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill small shadow-sm">
                            <i class="bi bi-diagram-3-fill me-1"></i> त्रिस्तरीय पंचायती राज व्यवस्था
                        </span>
                    </div>
                    <h2 class="h3 fw-bold mb-1" style="color: var(--primary-navy); font-family: var(--font-heading);">
                        Bihar Panchayati Raj Leadership Directory
                    </h2>
                    <p class="text-muted mb-0 small lh-base">
                        Complete elected leadership structure from District (Zila Parishad) to Block (Panchayat Samiti) and Village (Gram Panchayat &amp; Katchahry)
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo getZilaParishadUrl(); ?>" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3 py-1.5 shadow-sm">
                        <i class="bi bi-shield-shaded me-1"></i> Zila Parishad &rarr;
                    </a>
                    <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="btn btn-outline-success btn-sm fw-bold rounded-pill px-3 py-1.5 shadow-sm">
                        <i class="bi bi-building-gear me-1"></i> Panchayat Samiti &rarr;
                    </a>
                </div>
            </div>

            <!-- 3-Tier Leadership Cards Grid -->
            <div class="row g-4">
                
                <!-- Tier 1: Zila Parishad (District Level) -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between border-top border-4 border-primary bg-white position-relative hover-lift">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1.5 rounded-pill border border-primary border-opacity-25 small">
                                    🏛️ District Level (शीर्ष स्तर)
                                </span>
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small fw-semibold">38 Districts</span>
                            </div>

                            <h3 class="h5 fw-bold mb-2 text-navy" style="font-family: var(--font-heading);">
                                जिला परिषद (Zila Parishad)
                            </h3>
                            <p class="small text-muted mb-4 lh-base">
                                Highest tier of local governance across all 38 districts of Bihar with apex administrative leadership.
                            </p>

                            <!-- Breakdown Table / Stats Box -->
                            <div class="bg-light bg-opacity-70 p-3 rounded-3 mb-4 border">
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small text-secondary fw-semibold">अध्यक्ष (Adhyaksh):</span>
                                    <span class="badge bg-white text-primary border border-primary border-opacity-25 fw-bold px-2.5 py-1 shadow-2xs">38 Chairperson Seats</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small text-secondary fw-semibold">उपाध्यक्ष (Upadhyaksh):</span>
                                    <span class="badge bg-white text-dark border fw-bold px-2.5 py-1 shadow-2xs">38 Vice-Chairperson Seats</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2">
                                    <span class="small text-secondary fw-semibold">क्षेत्रीय सदस्य (Territory Members):</span>
                                    <span class="badge text-white fw-bold px-2.5 py-1 shadow-2xs" style="background: #7c3aed;">1,153+ Wards</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action CTA -->
                        <div class="pt-3 border-top">
                            <a href="<?php echo getZilaParishadUrl(); ?>" class="btn btn-primary btn-sm w-100 fw-bold py-2 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-shield-shaded"></i> Explore 38 District Adhyaksh &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Panchayat Samiti (Block Level) -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between border-top border-4 border-success bg-white position-relative hover-lift">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1.5 rounded-pill border border-success border-opacity-25 small">
                                    🏢 Block Level (मध्यवर्ती स्तर)
                                </span>
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small fw-semibold">534 Blocks</span>
                            </div>

                            <h3 class="h5 fw-bold mb-2 text-navy" style="font-family: var(--font-heading);">
                                पंचायत समिति (Panchayat Samiti)
                            </h3>
                            <p class="small text-muted mb-4 lh-base">
                                Intermediate executive tier operating across all 534 Community Development Blocks (प्रखंड).
                            </p>

                            <!-- Breakdown Table / Stats Box -->
                            <div class="bg-light bg-opacity-70 p-3 rounded-3 mb-4 border">
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small text-secondary fw-semibold">प्रमुख (Pramukh):</span>
                                    <span class="badge bg-white text-success border border-success border-opacity-25 fw-bold px-2.5 py-1 shadow-2xs">534 Block Pramukhs</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small text-secondary fw-semibold">उप-प्रमुख (Up-Pramukh):</span>
                                    <span class="badge bg-white text-dark border fw-bold px-2.5 py-1 shadow-2xs">534 Vice-Pramukhs</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2">
                                    <span class="small text-secondary fw-semibold">समिति सदस्य (Samiti Members):</span>
                                    <span class="badge bg-success text-white fw-bold px-2.5 py-1 shadow-2xs">11,000+ Members</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action CTA -->
                        <div class="pt-3 border-top">
                            <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="btn btn-success btn-sm w-100 fw-bold py-2 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-1 text-white">
                                <i class="bi bi-building-gear"></i> Explore 534 Block Pramukh Hub &rarr;
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Tier 3: Gram Panchayat & Gram Katchahry (Village Level) -->
                <div class="col-12 col-md-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between border-top border-4 border-warning bg-white position-relative hover-lift">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="badge bg-warning bg-opacity-15 text-dark fw-bold px-3 py-1.5 rounded-pill border border-warning border-opacity-40 small">
                                    🌾 Village Level (ग्राम स्तर)
                                </span>
                                <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small fw-semibold">8,053+ Panchayats</span>
                            </div>

                            <h3 class="h5 fw-bold mb-2 text-navy" style="font-family: var(--font-heading);">
                                ग्राम पंचायत एवं कचहरी
                            </h3>
                            <p class="small text-muted mb-4 lh-base">
                                Grassroots administrative &amp; judicial local self-governance across all rural villages in Bihar.
                            </p>

                            <!-- Breakdown Table / Stats Box -->
                            <div class="bg-light bg-opacity-70 p-3 rounded-3 mb-4 border">
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small text-secondary fw-semibold">मुखिया एवं उप-मुखिया:</span>
                                    <span class="badge bg-white text-dark border border-warning border-opacity-50 fw-bold px-2.5 py-1 shadow-2xs">8,053+ Mukhiya / Up-Mukhiya</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                    <span class="small text-secondary fw-semibold">सरपंच एवं उप-सरपंच:</span>
                                    <span class="badge bg-white text-danger border border-danger border-opacity-25 fw-bold px-2.5 py-1 shadow-2xs">8,053+ Sarpanch / Up-Sarpanch</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2">
                                    <span class="small text-secondary fw-semibold">वार्ड सदस्य एवं पंच:</span>
                                    <span class="badge bg-dark text-white fw-bold px-2.5 py-1 shadow-2xs">1,14,000+ Wards</span>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action CTA (Dual Buttons) -->
                        <div class="pt-3 border-top d-flex gap-2">
                            <a href="<?php echo SITE_URL; ?>/mukhiya" class="btn btn-outline-warning text-dark btn-sm flex-fill fw-bold py-2 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-1">
                                🌾 Mukhiyas &rarr;
                            </a>
                            <a href="<?php echo SITE_URL; ?>/sarpanch" class="btn btn-outline-danger btn-sm flex-fill fw-bold py-2 rounded-pill shadow-sm d-flex align-items-center justify-content-center gap-1">
                                ⚖️ Sarpanchs &rarr;
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Bihar Census 2011 Urban & Rural Administrative Matrix -->
        <section class="mb-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff;">
                <div class="p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-7">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-success text-white fw-bold px-3 py-1.5 rounded-pill">
                                    <i class="bi bi-patch-check-fill me-1"></i> Census 2011 Data Release 1000
                                </span>
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill">
                                    Full 38 Districts Matrix
                                </span>
                            </div>
                            <h2 class="h3 fw-bold mb-2 text-white" style="font-family: 'Outfit', sans-serif;">
                                Bihar Urban &amp; Rural Demographic Directories
                            </h2>
                            <p class="text-white-50 mb-4" style="line-height: 1.6;">
                                Search, explore, and analyze micro-demographic statistics, sex ratios, literacy, civic amenities, commodities, and slums across all statutory towns and rural census villages in Bihar.
                            </p>
                            <div class="d-flex flex-wrap gap-3">
                                <a href="<?php echo getVillageUrl(); ?>" class="btn btn-success fw-bold px-4 py-2.5 rounded-pill shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-tree-fill"></i> 44,874 Census Villages &rarr;
                                </a>
                                <a href="<?php echo getTownUrl(); ?>" class="btn btn-outline-light fw-bold px-4 py-2.5 rounded-pill shadow-sm d-flex align-items-center gap-2">
                                    <i class="bi bi-buildings-fill"></i> 199 Towns &amp; 670 Slums &rarr;
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="<?php echo getVillageUrl(); ?>" class="p-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-3 text-decoration-none text-white d-block h-100 hover-lift">
                                        <div class="fs-4 fw-extrabold text-success">44,874</div>
                                        <div class="small fw-bold">Census Villages</div>
                                        <div class="extra-small text-white-50">534 CD Blocks covered</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?php echo getTownUrl(); ?>" class="p-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-3 text-decoration-none text-white d-block h-100 hover-lift">
                                        <div class="fs-4 fw-extrabold text-info">199</div>
                                        <div class="small fw-bold">Statutory &amp; CT Towns</div>
                                        <div class="extra-small text-white-50">M.Corp / NP / CT</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?php echo getTownUrl(); ?>" class="p-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-3 text-decoration-none text-white d-block h-100 hover-lift">
                                        <div class="fs-4 fw-extrabold text-warning">670</div>
                                        <div class="small fw-bold">Slum Settlements</div>
                                        <div class="extra-small text-white-50">Micro-ward demographics</div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="<?php echo SITE_URL; ?>/census" class="p-3 bg-white bg-opacity-10 border border-white border-opacity-20 rounded-3 text-decoration-none text-white d-block h-100 hover-lift">
                                        <div class="fs-4 fw-extrabold text-primary" style="color: #93c5fd !important;">10.41 Cr</div>
                                        <div class="small fw-bold">Bihar Census Pop.</div>
                                        <div class="extra-small text-white-50">Complete demographic breakdown</div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bihar Caste-Based Survey (Jati Ganana 2022-23) Brief & Category Intelligence -->
        <section class="mb-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white border border-light">
                <div class="p-4 p-lg-5">
                    <!-- Section Header -->
                    <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 pb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill small shadow-xs">
                                    <i class="bi bi-pie-chart-fill me-1"></i> बिहार जाति आधारित गणना 2022-23
                                </span>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 fw-bold px-2.5 py-1 rounded-pill small">
                                    215 Official Caste Codes (GAD)
                                </span>
                            </div>
                            <h2 class="h3 fw-bold mb-1 text-navy" style="font-family: var(--font-heading);">
                                Bihar Caste Survey Demographics &amp; Reservation Matrix
                            </h2>
                            <p class="text-muted mb-0 small lh-base" style="max-width: 820px;">
                                Official Government of Bihar survey encompassing <strong>13.07 Crore surveyed citizens</strong>, <strong>2.76 Crore households</strong>, and <strong>215 recognized caste groups</strong> across all 38 districts.
                            </p>
                        </div>
                        <a href="<?php echo getCasteSurveyUrl(); ?>" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2 shadow-sm d-flex align-items-center gap-1.5">
                            <i class="bi bi-search"></i> Explore All 215+ Caste Codes &rarr;
                        </a>
                    </div>

                    <!-- 5 Major Category Cards Grid -->
                    <div class="row g-3 mb-4">
                        <!-- 1: EBC -->
                        <div class="col-12 col-sm-6 col-lg">
                            <a href="<?php echo getCasteSurveyUrl(); ?>?category=EBC" class="card p-3 h-100 border-0 rounded-3 text-decoration-none text-reset hover-lift" style="background: #f0fdf4; border: 1px solid #bbf7d0 !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill small">अत्यंत पिछड़ा वर्ग</span>
                                    <span class="small fw-bold text-success">112 Castes</span>
                                </div>
                                <div class="fs-3 fw-extrabold text-success mb-0 lh-1">36.01%</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.9rem;">EBC Category</div>
                                <div class="small text-muted">4,70,80,514 Persons</div>
                            </a>
                        </div>

                        <!-- 2: BC -->
                        <div class="col-12 col-sm-6 col-lg">
                            <a href="<?php echo getCasteSurveyUrl(); ?>?category=BC" class="card p-3 h-100 border-0 rounded-3 text-decoration-none text-reset hover-lift" style="background: #fffbeb; border: 1px solid #fde68a !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small">पिछड़ा वर्ग</span>
                                    <span class="small fw-bold text-warning" style="color: #b45309 !important;">30 Castes</span>
                                </div>
                                <div class="fs-3 fw-extrabold text-warning mb-0 lh-1" style="color: #d97706 !important;">27.12%</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.9rem;">BC Category</div>
                                <div class="small text-muted">3,54,63,936 Persons</div>
                            </a>
                        </div>

                        <!-- 3: SC -->
                        <div class="col-12 col-sm-6 col-lg">
                            <a href="<?php echo getCasteSurveyUrl(); ?>?category=SC" class="card p-3 h-100 border-0 rounded-3 text-decoration-none text-reset hover-lift" style="background: #fef2f2; border: 1px solid #fecaca !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-danger text-white fw-bold px-2.5 py-1 rounded-pill small">अनुसूचित जाति</span>
                                    <span class="small fw-bold text-danger">22 Castes</span>
                                </div>
                                <div class="fs-3 fw-extrabold text-danger mb-0 lh-1">19.65%</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.9rem;">SC Category</div>
                                <div class="small text-muted">2,56,89,820 Persons</div>
                            </a>
                        </div>

                        <!-- 4: General / Unreserved -->
                        <div class="col-12 col-sm-6 col-lg">
                            <a href="<?php echo getCasteSurveyUrl(); ?>?category=GEN" class="card p-3 h-100 border-0 rounded-3 text-decoration-none text-reset hover-lift" style="background: #eff6ff; border: 1px solid #bfdbfe !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill small">अनारक्षित / सामान्य</span>
                                    <span class="small fw-bold text-primary">17 Castes</span>
                                </div>
                                <div class="fs-3 fw-extrabold text-primary mb-0 lh-1">15.52%</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.9rem;">General / UC</div>
                                <div class="small text-muted">2,02,91,679 Persons</div>
                            </a>
                        </div>

                        <!-- 5: ST -->
                        <div class="col-12 col-sm-6 col-lg">
                            <a href="<?php echo getCasteSurveyUrl(); ?>?category=ST" class="card p-3 h-100 border-0 rounded-3 text-decoration-none text-reset hover-lift" style="background: #faf5ff; border: 1px solid #e9d5ff !important;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge text-white fw-bold px-2.5 py-1 rounded-pill small" style="background: #9333ea;">अनुसूचित जनजाति</span>
                                    <span class="small fw-bold" style="color: #7e22ce;">32 Castes</span>
                                </div>
                                <div class="fs-3 fw-extrabold mb-0 lh-1" style="color: #9333ea;">1.68%</div>
                                <div class="fw-bold text-dark mt-1" style="font-size: 0.9rem;">ST Category</div>
                                <div class="small text-muted">21,99,361 Persons</div>
                            </a>
                        </div>
                    </div>

                    <!-- Proportional Multi-Segment Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between small text-muted fw-semibold mb-1.5">
                            <span>Population Proportions (Total: 13.07 Crore)</span>
                            <span>EBC 36.01% | BC 27.12% | SC 19.65% | GEN 15.52% | ST 1.68%</span>
                        </div>
                        <div class="progress rounded-pill shadow-xs" style="height: 14px; background: #e2e8f0;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: 36.01%" title="EBC: 36.01%"></div>
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 27.12%" title="BC: 27.12%"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: 19.65%" title="SC: 19.65%"></div>
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 15.52%" title="General: 15.52%"></div>
                            <div class="progress-bar" role="progressbar" style="width: 1.68%; background: #9333ea;" title="ST: 1.68%"></div>
                        </div>
                    </div>

                    <!-- Major Caste Demographics Quick Snapshot Tags & Religious Ratio -->
                    <div class="row g-3 align-items-center pt-3 border-top">
                        <div class="col-12 col-xl-8">
                            <div class="small fw-bold text-dark mb-2">
                                <i class="bi bi-tags-fill text-warning me-1"></i> Key Caste Groups &amp; Official Codes (बिहार प्रमुख जातियां):
                            </div>
                            <div class="d-flex flex-wrap gap-1.5">
                                <a href="<?php echo getCasteSurveyUrl(167); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>यादव (Yadav)</strong>: 14.26% <span class="text-muted ms-1">(Code 167)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(182); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>रविदास/चमार (Ravidas)</strong>: 5.25% <span class="text-muted ms-1">(Code 182)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(27); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>कुशवाहा/कोइरी (Kushwaha)</strong>: 4.21% <span class="text-muted ms-1">(Code 027)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(126); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>ब्राह्मण (Brahmin)</strong>: 3.65% <span class="text-muted ms-1">(Code 126)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(169); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>राजपूत (Rajput)</strong>: 3.45% <span class="text-muted ms-1">(Code 169)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(160); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>मुसहर (Musahar)</strong>: 3.08% <span class="text-muted ms-1">(Code 160)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(24); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>कुर्मी (Kurmi)</strong>: 2.87% <span class="text-muted ms-1">(Code 024)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(144); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>भूमिहार (Bhumihar)</strong>: 2.86% <span class="text-muted ms-1">(Code 144)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(117); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>तेली (Teli)</strong>: 2.81% <span class="text-muted ms-1">(Code 117)</span>
                                </a>
                                <a href="<?php echo getCasteSurveyUrl(124); ?>" class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill text-decoration-none hover-shadow">
                                    <strong>बनिया (Bania)</strong>: 2.31% <span class="text-muted ms-1">(Code 124)</span>
                                </a>
                            </div>
                        </div>
                        <div class="col-12 col-xl-4 text-xl-end">
                            <div class="d-inline-flex align-items-center gap-2 p-2 bg-light rounded-3 border">
                                <div class="text-start">
                                    <div class="extra-small text-muted fw-semibold">Religious Breakdown:</div>
                                    <div class="small fw-bold text-dark">Hindu: 81.99% | Muslim: 17.70%</div>
                                </div>
                                <a href="<?php echo getCasteSurveyUrl(); ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3">
                                    Full Report &rarr;
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- 38 District Hubs Section (Alphabetical A-Z Order with Patna Highlight) -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">38 Bihar District Election Hubs (A–Z)</h2>
                    <p class="small text-muted mb-0">Vidhan Sabha seats, headquarters, and demographics for all 38 districts across Bihar</p>
                </div>
                <a href="<?php echo getDistrictUrl('patna'); ?>" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    View All 38 Districts &rarr;
                </a>
            </div>

            <div class="row g-3 g-lg-4">
                <?php foreach (array_slice($districts, 0, 8) as $dist): 
                    $isPatna = ($dist['slug'] === 'patna');
                ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-column justify-content-between <?php echo $isPatna ? 'border-top border-4 border-warning bg-light' : ''; ?>">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h6 fw-bold mb-0" style="color: var(--primary-navy);">
                                    <?php echo htmlspecialchars($dist['name']); ?>
                                    <?php if ($isPatna): ?>
                                        <span class="badge bg-warning text-dark extra-small ms-1">Capital</span>
                                    <?php endif; ?>
                                </h3>
                                <span class="badge bg-light text-dark border"><?php echo $dist['total_ac']; ?> ACs</span>
                            </div>
                            <p class="small text-muted mb-2">
                                <strong>HQ:</strong> <?php echo htmlspecialchars($dist['headquarters']); ?> | <strong>Division:</strong> <?php echo htmlspecialchars($dist['division']); ?>
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if (!empty($dist['ac_list'])): ?>
                                    <?php foreach (array_slice($dist['ac_list'], 0, 3) as $acItem): ?>
                                        <a href="<?php echo getMlaUrl($acItem); ?>" class="ac-tag">
                                            <?php echo $acItem['ac_no']; ?> - <?php echo htmlspecialchars($acItem['name']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (count($dist['ac_list']) > 3): ?>
                                        <span class="ac-tag" style="background: #e2e8f0;">+<?php echo count($dist['ac_list']) - 3; ?> more</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="pt-2 border-top">
                            <a href="<?php echo getDistrictUrl($dist['slug']); ?>" class="small fw-bold text-decoration-none" style="color: var(--accent-saffron);">
                                Open <?php echo htmlspecialchars($dist['name']); ?> Hub &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>



        <!-- Featured 243 Assembly Constituencies -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Featured Assembly Seats (243 AC Project)</h2>
                    <p class="small text-muted mb-0">Latest 2026 &amp; 2025 Vidhan Sabha winners, victory margins, and historical electoral archive across Bihar</p>
                </div>
                <a href="<?php echo SITE_URL; ?>/vidhan-sabha" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    Explore 243 Seats &rarr;
                </a>
            </div>

            <div class="row g-3 g-lg-4">
                <?php 
                // Select prominent statewide seats (Bankipur, Patna Sahib, Raghopur, Aurai, Gaya Town, Chapra)
                $featuredAcNos = [182, 184, 128, 89, 230, 118];
                $featuredAcs = [];
                foreach ($featuredAcNos as $favNo) {
                    $found = DataProvider::getConstituencyByAcNumber($favNo);
                    if ($found) $featuredAcs[] = $found;
                }
                if (empty($featuredAcs)) {
                    $featuredAcs = array_slice($constituencies, 0, 6);
                }
                foreach ($featuredAcs as $ac): 
                    $acNo = (int)$ac['ac_no'];
                    $byeList = DataProvider::getByeElectionDetailedResults($acNo);
                    $res2025 = DataProvider::getElectionSuccessfulCandidates($acNo, 2025);
                    $res2020 = DataProvider::getElectionSuccessfulCandidates($acNo, 2020) ?: ($ac['election_2020'] ?? []);
                    $hasBye = !empty($byeList);

                    if ($hasBye) {
                        $yearBadge = '<span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="bi bi-lightning-charge-fill me-1"></i>2026 Bye-Poll Winner</span>';
                        $winnerName = $byeList[0]['candidate_name'] ?? $ac['current_mla'];
                        $winnerParty = $byeList[0]['party'] ?? $ac['current_party'];
                        $marginVotes = $byeList[0]['margin'] ?? (($byeList[0]['votes_total'] ?? 0) - ($byeList[1]['votes_total'] ?? 0));
                        $runnerUpName = $byeList[1]['candidate_name'] ?? '';
                        $runnerUpParty = $byeList[1]['party'] ?? '';
                    } elseif (!empty($res2025['winner_name'])) {
                        $yearBadge = '<span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 border border-success border-opacity-25"><i class="bi bi-trophy-fill me-1"></i>2025 Winner</span>';
                        $winnerName = $res2025['winner_name'] ?? $ac['current_mla'];
                        $winnerParty = $res2025['winner_party'] ?? $ac['current_party'];
                        $marginVotes = $res2025['margin'] ?? 0;
                        $runnerUpName = $res2025['runner_up_name'] ?? '';
                        $runnerUpParty = $res2025['runner_up_party'] ?? '';
                    } else {
                        $yearBadge = '<span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 border border-primary border-opacity-25"><i class="bi bi-person-badge me-1"></i>Elected MLA</span>';
                        $winnerName = $ac['current_mla'] ?? $res2020['winner_name'] ?? $res2020['winner'] ?? 'N/A';
                        $winnerParty = $ac['current_party'] ?? $res2020['winner_party'] ?? '';
                        $marginVotes = $res2020['margin'] ?? 0;
                        $runnerUpName = $res2020['runner_up_name'] ?? $res2020['runner_up'] ?? '';
                        $runnerUpParty = $res2020['runner_up_party'] ?? '';
                    }
                    $partyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $winnerParty));
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 h-100 d-flex flex-column justify-content-between" style="background: #ffffff;">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-no-badge">AC #<?php echo $ac['ac_no']; ?></span>
                                <span class="badge-party <?php echo $partyClass; ?>"><?php echo htmlspecialchars($winnerParty); ?></span>
                            </div>
                            <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">
                                <?php echo htmlspecialchars($ac['name']); ?> 
                                <?php if (!empty($ac['name_hi'])): ?>
                                    <span class="small text-muted fw-normal">(<?php echo htmlspecialchars($ac['name_hi']); ?>)</span>
                                <?php endif; ?>
                            </h3>
                            <div class="small text-muted mb-3">
                                District: <strong><?php echo htmlspecialchars($ac['district']); ?></strong>
                                <?php if (!empty($ac['lok_sabha'])): ?>
                                    | Lok Sabha: <strong><?php echo htmlspecialchars($ac['lok_sabha']); ?></strong>
                                <?php endif; ?>
                            </div>

                            <div class="bg-light p-3 rounded-3 mb-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <?php echo $yearBadge; ?>
                                    <?php if ($marginVotes > 0): ?>
                                        <span class="badge bg-white text-dark border px-2 py-1 small fw-bold">
                                            +<?php echo number_format($marginVotes); ?> margin
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="fw-bold fs-6 mb-1" style="color: var(--primary-navy);">
                                    <?php echo htmlspecialchars($winnerName); ?>
                                </div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span>Party: <strong class="text-dark"><?php echo htmlspecialchars($winnerParty); ?></strong></span>
                                </div>
                                <?php if (!empty($runnerUpName)): ?>
                                    <div class="small text-muted pt-2 mt-2 border-top">
                                        <span class="text-secondary">Defeated:</span> 
                                        <strong><?php echo htmlspecialchars($runnerUpName); ?></strong> 
                                        <?php if (!empty($runnerUpParty)): ?>
                                            (<?php echo htmlspecialchars($runnerUpParty); ?>)
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="<?php echo getMlaUrl($ac); ?>" class="btn btn-outline-primary btn-sm w-100 fw-bold py-2 rounded-3 shadow-none mt-2">
                            Full AC #<?php echo $ac['ac_no']; ?> Report &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Upcoming Election: Bihar Legislative Council (MLC) Section -->
        <section class="mb-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
                <div class="p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-7">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill">
                                    <i class="bi bi-calendar-check-fill me-1"></i> Upcoming Election
                                </span>
                                <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill">
                                    🏛️ Bihar Vidhan Parishad
                                </span>
                                <span class="badge bg-secondary text-white fw-bold px-3 py-1.5 rounded-pill">
                                    75 Upper House Seats
                                </span>
                            </div>

                            <h2 class="h3 fw-bold mb-2" style="color: var(--primary-navy); font-family: 'Outfit', sans-serif;">
                                Bihar Legislative Council (MLC) Elections
                            </h2>
                            <p class="text-muted mb-4" style="line-height: 1.6;">
                                Explore full roster, tenure expirations, and quota breakdowns for all <strong>75 Members of Bihar Legislative Council (MLCs)</strong> across Local Authorities, Graduates, Teachers, and Assembly quotas.
                            </p>

                            <div class="row g-2 g-sm-3 mb-4">
                                <div class="col-6 col-sm-4">
                                    <div class="p-3 bg-white rounded-3 shadow-xs border text-center h-100">
                                        <div class="fw-extrabold fs-4 text-primary">24</div>
                                        <div class="small fw-semibold text-dark">Local Authorities</div>
                                        <div class="extra-small text-muted">Panchayat &amp; ULB Electors</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="p-3 bg-white rounded-3 shadow-xs border text-center h-100">
                                        <div class="fw-extrabold fs-4 text-warning" style="color: #d97706 !important;">27</div>
                                        <div class="small fw-semibold text-dark">Assembly Quota</div>
                                        <div class="extra-small text-muted">Elected by 243 MLAs</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="p-3 bg-white rounded-3 shadow-xs border text-center h-100">
                                        <div class="fw-extrabold fs-4 text-success">24</div>
                                        <div class="small fw-semibold text-dark">Graduates &amp; Nominated</div>
                                        <div class="extra-small text-muted">Teachers &amp; Governor Quotas</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-danger fw-bold px-4 py-2.5 rounded-pill shadow-sm">
                                    <i class="bi bi-list-columns-reverse me-1"></i> See 75 MLCs List &rarr;
                                </a>
                                <a href="<?php echo SITE_URL; ?>/representatives" class="btn btn-outline-secondary fw-bold px-4 py-2.5 rounded-pill">
                                    All Representatives
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h5 class="fw-bold mb-0 text-navy fs-6">
                                        <i class="bi bi-person-lines-fill me-1 text-danger"></i> Vidhan Parishad Highlights
                                    </h5>
                                    <span class="badge bg-light text-dark border">75 MLCs</span>
                                </div>

                                <div class="d-flex flex-column gap-2.5">
                                    <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold small text-dark">Permanent Upper Chamber</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">1/3rd Members retire every 2 years (6-yr tenure)</div>
                                        </div>
                                        <span class="badge bg-primary text-white small">Biennial Polls</span>
                                    </div>

                                    <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold small text-dark">Local Body Authority Electors</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Mukhiyas, Ward Members, Panchayat Samiti &amp; ZP</div>
                                        </div>
                                        <span class="badge bg-success text-white small">24 Seats</span>
                                    </div>

                                    <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold small text-dark">Teachers &amp; Graduates Quota</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Patna, Tirhut, Kosi, Saran, Darbhanga, Gaya</div>
                                        </div>
                                        <span class="badge bg-info text-dark small">12 Seats</span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-top text-center">
                                    <a href="<?php echo SITE_URL; ?>/mlc" class="small fw-bold text-decoration-none text-danger">
                                        Open Bihar MLC Directory &amp; Contact Roster &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="card border-0 rounded-4 p-4 text-white shadow-lg" style="background: linear-gradient(135deg, #0b192c, #1e3e62);">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-8">
                    <h3 class="h4 fw-bold mb-2">🚀 Contesting in Bihar 2026 or Operating a Local Service?</h3>
                    <p class="text-white-50 mb-2">Reach 2,00,000+ monthly high-intent voters, political observers, and community leaders across Bihar.</p>
                    <div class="d-flex flex-wrap gap-3 small text-white-50">
                        <span>✓ Verified Candidate Pages (₹2,500+)</span>
                        <span>✓ District Directory Listings (₹1,999/yr)</span>
                        <span>✓ Assembly Page Sponsors</span>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <a href="<?php echo getAdvertiseUrl(); ?>" class="btn btn-warning fw-bold px-4 py-2 text-dark shadow-sm">
                        View Packages & Rates &rarr;
                    </a>
                </div>
            </div>
        </section>

    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
