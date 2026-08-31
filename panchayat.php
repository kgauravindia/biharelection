<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();

$districtInput = $_GET['district'] ?? '';
$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrict = $districtObj['slug'] ?? $districtInput;
$selectedPanchayat = $_GET['panchayat'] ?? '';
$selectedBlock = $_GET['block'] ?? '';

$rawTab = strtolower($_GET['tab'] ?? 'mukhiya');
if (in_array($rawTab, ['zila', 'jila', 'zila-parishad', 'jila-parishad', 'jila-parisad', 'members'])) {
    $selectedTab = 'zila';
} elseif (in_array($rawTab, ['samiti', 'panchayat-samiti', 'archive', 'archive2016'])) {
    $selectedTab = 'archive2016';
} elseif (in_array($rawTab, ['officials', 'chairperson', 'chairpersons'])) {
    $selectedTab = 'officials';
} elseif ($rawTab === 'sarpanch') {
    $selectedTab = 'sarpanch';
} else {
    $selectedTab = 'mukhiya';
}

$mukhiyas = DataProvider::getMukhiyaData($selectedDistrict ?: null);
$sarpanchs = DataProvider::getSarpanchData($selectedDistrict ?: null);
$zilaMembers = DataProvider::getZilaParishadMembers($selectedDistrict ?: null);
$zilaOfficials = DataProvider::getZilaParishadOfficials();
$zilaSummary = DataProvider::getZilaParishadSummary();
$panchayatSummary = DataProvider::getPanchayatSummary();
$samiti2016 = DataProvider::getPanchayatSamiti2016($selectedDistrict ?: null);
$zila2016 = DataProvider::getZilaParishad2016($selectedDistrict ?: null);
$mukhiyas2016 = DataProvider::getMukhiyas2016($selectedDistrict ?: null);

$distLabel = $districtObj ? "({$districtObj['name']} District)" : "Across 38 Districts";

if ($selectedTab === 'sarpanch') {
    $pageTitle = "Bihar Sarpanch Directory {$distLabel}: 6,617 Gram Katchahry Heads";
    $pageDescription = "Official directory of elected Gram Katchahry Sarpanchs in Bihar {$distLabel}. Complete roster of 6,617 Sarpanchs with category, block, and contact details.";
    $pageCanonical = getSarpanchUrl($selectedDistrict, $selectedPanchayat);
} elseif ($selectedTab === 'zila') {
    $pageTitle = "Bihar Zila Parishad Directory {$distLabel}: 38 District Boards & 1,100+ Ward Members";
    $pageDescription = "Official directory of Bihar Zila Parishad Board Chairpersons, Vice-Chairpersons, and 1,099+ Territorial Ward Members {$distLabel}.";
    $pageCanonical = getZilaParishadUrl($selectedDistrict);
} elseif ($selectedTab === 'archive2016') {
    $pageTitle = "Bihar Panchayat Samiti & 2016 Archive {$distLabel}: 389 Block Pramukhs & Mukhiyas";
    $pageDescription = "Historical 2016 Bihar Panchayat Election archive including 389 Block Pramukhs, Up-Pramukhs, and 8,045 Mukhiyas {$distLabel}.";
    $pageCanonical = getPanchayatSamitiUrl($selectedDistrict, $selectedBlock);
} else {
    $pageTitle = "Bihar Mukhiya Directory {$distLabel}: 7,346 Elected Gram Panchayat Heads";
    $pageDescription = "Official Bihar Mukhiya directory with separate tables for 7,346 elected Gram Panchayat Mukhiyas across 38 districts with block, age, and reservation details.";
    $pageCanonical = getMukhiyaUrl($selectedDistrict, $selectedPanchayat);
}

$activeNav = 'panchayat';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2">
                    🌾 Bihar Panchayati Raj Representative Directory
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">7,346 Mukhiyas</span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">6,617 Sarpanchs</span>
                <span class="badge bg-warning bg-opacity-25 text-warning fw-bold px-3 py-2">38 Zila Parishads</span>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Bihar Panchayati Raj Directory: <br>
                <span style="color: var(--accent-saffron);">Separate Mukhiya & Sarpanch Roster Tables</span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                Complete database of elected village heads: <strong>7,346 Mukhiyas (ग्राम पंचायत)</strong>, <strong>6,617 Sarpanchs (ग्राम कचहरी)</strong>, <strong>38 District Board Presidents</strong>, and <strong>1,100+ Zila Parishad Ward Members</strong>.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="#mukhiya-pane" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-person-badge"></i> Explore Mukhiya Table (7,346)
                </a>
                <a href="#sarpanch-pane" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-hammer"></i> Explore Sarpanch Table (6,617)
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp"></i> Get WhatsApp Alerts
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container my-4 my-lg-5">

        <!-- Top Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- 3 Feature Highlight Cards -->
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 border-start border-4 border-success bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 fw-bold mb-0" style="color: var(--primary-navy);">1. Mukhiya Directory</h3>
                        <span class="fs-4">🌾</span>
                    </div>
                    <p class="small text-muted mb-0">
                        <strong>7,346 Elected Mukhiyas</strong> leading village development, road connectivity, water supply, and rural welfare administration.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 border-start border-4 border-info bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 fw-bold mb-0" style="color: var(--primary-navy);">2. Sarpanch Directory</h3>
                        <span class="fs-4">⚖️</span>
                    </div>
                    <p class="small text-muted mb-0">
                        <strong>6,617 Elected Sarpanchs</strong> presiding over Gram Katchahry judicial dispute resolution courts across Bihar.
                    </p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 border-start border-4 border-primary bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 fw-bold mb-0" style="color: var(--primary-navy);">3. Zila Parishad Tier</h3>
                        <span class="fs-4">🏛️</span>
                    </div>
                    <p class="small text-muted mb-0">
                        <strong>38 District Boards</strong> with 1,100+ Territorial Ward Members and District Chairpersons/Vice-Chairpersons.
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs: Separate Mukhiya Table & Sarpanch Table -->
        <ul class="nav nav-pills nav-fill mb-4 p-1 bg-light rounded-3 border" id="panchayatTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'mukhiya' ? 'active' : ''; ?> fw-bold py-2" id="mukhiya-nav-tab" data-bs-toggle="pill" data-bs-target="#mukhiya-pane" type="button" role="tab">
                    🌾 Table 1: Mukhiya Directory (मुखिया - 7,346 Seats)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'sarpanch' ? 'active' : ''; ?> fw-bold py-2" id="sarpanch-nav-tab" data-bs-toggle="pill" data-bs-target="#sarpanch-pane" type="button" role="tab">
                    ⚖️ Table 2: Sarpanch Directory (सरपंच - 6,617 Seats)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'zila' ? 'active' : ''; ?> fw-bold py-2" id="zila-nav-tab" data-bs-toggle="pill" data-bs-target="#zila-pane" type="button" role="tab">
                    🏛️ Table 3: Zila Parishad Members (1,099 Seats)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'officials' ? 'active' : ''; ?> fw-bold py-2" id="officials-nav-tab" data-bs-toggle="pill" data-bs-target="#officials-pane" type="button" role="tab">
                    👑 Table 4: Zila Parishad Chairpersons (38 Districts)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'archive2016' ? 'active' : ''; ?> fw-bold py-2" id="archive2016-nav-tab" data-bs-toggle="pill" data-bs-target="#archive2016-pane" type="button" role="tab">
                    ⏳ Table 5: 2016 Block Pramukh Archive (389 Blocks)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="panchayatTabContent">

            <!-- ========================================================= -->
            <!-- TAB 1: SEPARATE MUKHIYA DIRECTORY TABLE                   -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'mukhiya' ? 'show active' : ''; ?>" id="mukhiya-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">Gram Panchayat Executive Head</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Tenure: 2021–2026</span>
                            </div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                🌾 Bihar Mukhiya Directory Table (ग्राम पंचायत मुखिया सूची)
                            </h2>
                            <p class="small text-muted mb-0">Separate dedicated roster of all 7,346 elected Mukhiyas across 38 districts and 534 blocks</p>
                        </div>
                        <div>
                            <span class="badge bg-success rounded-pill px-3 py-2" id="totalMukhiyaBadge">
                                <?php echo count($mukhiyas); ?> Mukhiyas Loaded
                            </span>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-12 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1">Search Mukhiya / Panchayat / Block:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="mukhiyaSearch" class="form-control border-start-0" placeholder="Search by name, panchayat, block...">
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1">Filter District:</label>
                            <select id="mukhiyaDistrictFilter" class="form-select bg-white" onchange="if(this.value){ window.location.href='<?php echo SITE_URL; ?>/mukhiya/'+this.value; } else { window.location.href='<?php echo SITE_URL; ?>/mukhiya'; }">
                                <option value="">All 38 Districts</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars((string)($d['slug'] ?? '')); ?>" <?php echo $selectedDistrict === ($d['slug'] ?? '') ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)($d['name'] ?? '')); ?> (<?php echo htmlspecialchars((string)($d['name_hi'] ?? '')); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label small fw-bold text-muted mb-1">Gender:</label>
                            <select id="mukhiyaGenderFilter" class="form-select bg-white">
                                <option value="">All Genders</option>
                                <option value="Female">महिला (Women)</option>
                                <option value="Male">पुरूष (Men)</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1">Category / Quota:</label>
                            <select id="mukhiyaCategoryFilter" class="form-select bg-white">
                                <option value="">All Categories</option>
                                <option value="महिला">Women Reserved (महिला)</option>
                                <option value="पिछड़ा">OBC / EBC (पिछड़ा वर्ग)</option>
                                <option value="अनुसूचित जाति">SC (अनुसूचित जाति)</option>
                                <option value="अनुसूचित जनजाति">ST (अनुसूचित जनजाति)</option>
                                <option value="अनारक्षित">Unreserved (सामान्य)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table Pagination & Page Size Toolbar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted fw-bold mb-0">Show</label>
                            <select id="mukhiyaPageSize" class="form-select form-select-sm" style="width: 85px;">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                            <label class="small text-muted mb-0">per page</label>
                        </div>
                        <div class="small text-muted" id="mukhiyaPageInfo">
                            Loading Mukhiyas...
                        </div>
                        <div id="mukhiyaTopPagination"></div>
                    </div>

                    <!-- Mukhiya Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small" id="mukhiyaTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">District</th>
                                    <th class="py-3">Block / प्रखंड</th>
                                    <th class="py-3">Gram Panchayat</th>
                                    <th class="py-3">Elected Mukhiya (मुखिया का नाम)</th>
                                    <th class="py-3">Gender & Age</th>
                                    <th class="py-3">Reservation & Category</th>
                                    <th class="py-3">Contact & Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($mukhiyas as $m): 
                                    $mDistSlug = (string)($m['district_slug'] ?? '');
                                    $mDist = (string)($m['district'] ?? '');
                                    $mBlock = (string)($m['block'] ?? '');
                                    $mPanchayat = (string)($m['panchayat'] ?? '');
                                    $mName = (string)($m['candidate_name'] ?? '');
                                    $mFh = (string)($m['father_husband_name'] ?? '');
                                    $mGen = (string)($m['gender'] ?? '');
                                    $mGenHi = (string)($m['gender_hi'] ?? '');
                                    $mAge = $m['age'] ?? null;
                                    $mCat = (string)($m['category'] ?? '');
                                    $mRes = (string)($m['reservation'] ?? '');
                                    $mAddr = (string)($m['address'] ?? '');
                                    $mMob = (string)($m['mobile'] ?? '');
                                ?>
                                    <tr class="mukhiya-row"
                                        data-district="<?php echo htmlspecialchars($mDistSlug); ?>"
                                        data-district-name="<?php echo htmlspecialchars(strtolower($mDist)); ?>"
                                        data-block="<?php echo htmlspecialchars(strtolower($mBlock)); ?>"
                                        data-panchayat="<?php echo htmlspecialchars(strtolower($mPanchayat)); ?>"
                                        data-name="<?php echo htmlspecialchars(strtolower($mName . ' ' . $mFh)); ?>"
                                        data-gender="<?php echo htmlspecialchars($mGen); ?>"
                                        data-category="<?php echo htmlspecialchars(strtolower($mCat . ' ' . $mRes)); ?>">
                                        
                                        <!-- District -->
                                        <td class="fw-bold" style="min-width: 120px;">
                                            <a href="district.php?slug=<?php echo htmlspecialchars($mDistSlug); ?>" class="text-decoration-none" style="color: var(--primary-navy);">
                                                <?php echo htmlspecialchars($mDist); ?>
                                            </a>
                                        </td>

                                        <!-- Block -->
                                        <td class="fw-semibold text-dark" style="min-width: 120px;">
                                            <?php echo htmlspecialchars($mBlock); ?>
                                        </td>

                                        <!-- Panchayat -->
                                        <td style="min-width: 160px;">
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                🌾 <?php echo htmlspecialchars($mPanchayat); ?>
                                            </span>
                                        </td>

                                        <!-- Mukhiya Candidate Name -->
                                        <td style="min-width: 200px;">
                                            <div class="fw-bold text-success" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($mName); ?>
                                            </div>
                                            <?php if (!empty($mFh)): ?>
                                                <div class="text-muted small">W/o or S/o: <?php echo htmlspecialchars($mFh); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Gender & Age -->
                                        <td style="min-width: 120px;">
                                            <span class="badge <?php echo $mGen === 'Female' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'; ?> fw-semibold">
                                                <?php echo htmlspecialchars($mGenHi ?: $mGen); ?> <?php echo $mAge ? "({$mAge} yrs)" : ''; ?>
                                            </span>
                                        </td>

                                        <!-- Reservation & Category -->
                                        <td style="min-width: 160px;">
                                            <?php if (!empty($mRes)): ?>
                                                <div><span class="badge bg-light text-dark border extra-small"><?php echo htmlspecialchars($mRes); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($mCat)): ?>
                                                <div class="text-muted small mt-1"><?php echo htmlspecialchars($mCat); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Contact & Address -->
                                        <td style="min-width: 180px;">
                                            <?php if (!empty($mMob)): ?>
                                                <span class="badge bg-light text-secondary border py-1 px-2 extra-small d-inline-flex align-items-center gap-1 mb-1" title="Contact Protected">
                                                    <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($mMob)); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($mAddr)): ?>
                                                <div class="text-muted small text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($mAddr); ?>">
                                                    <?php echo htmlspecialchars($mAddr); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Pagination Bar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <div class="small text-muted" id="mukhiyaBottomInfo"></div>
                        <div id="mukhiyaPagination"></div>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 2: SEPARATE SARPANCH DIRECTORY TABLE                  -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'sarpanch' ? 'show active' : ''; ?>" id="sarpanch-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1">Gram Katchahry Judicial Head</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Tenure: 2021–2026</span>
                            </div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                ⚖️ Bihar Sarpanch Directory Table (ग्राम कचहरी सरपंच सूची)
                            </h2>
                            <p class="small text-muted mb-0">Separate dedicated roster of all 6,617 elected Sarpanchs across 38 districts and 534 blocks</p>
                        </div>
                        <div>
                            <span class="badge bg-info rounded-pill px-3 py-2 text-dark" id="totalSarpanchBadge">
                                <?php echo count($sarpanchs); ?> Sarpanchs Loaded
                            </span>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-12 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1">Search Sarpanch / Panchayat / Block:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="sarpanchSearch" class="form-control border-start-0" placeholder="Search by name, panchayat, block...">
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1">Filter District:</label>
                            <select id="sarpanchDistrictFilter" class="form-select bg-white" onchange="if(this.value){ window.location.href='<?php echo SITE_URL; ?>/sarpanch/'+this.value; } else { window.location.href='<?php echo SITE_URL; ?>/sarpanch'; }">
                                <option value="">All 38 Districts</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars((string)($d['slug'] ?? '')); ?>" <?php echo $selectedDistrict === ($d['slug'] ?? '') ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars((string)($d['name'] ?? '')); ?> (<?php echo htmlspecialchars((string)($d['name_hi'] ?? '')); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label small fw-bold text-muted mb-1">Gender:</label>
                            <select id="sarpanchGenderFilter" class="form-select bg-white">
                                <option value="">All Genders</option>
                                <option value="Female">महिला (Women)</option>
                                <option value="Male">पुरूष (Men)</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1">Category / Quota:</label>
                            <select id="sarpanchCategoryFilter" class="form-select bg-white">
                                <option value="">All Categories</option>
                                <option value="महिला">Women Reserved (महिला)</option>
                                <option value="पिछड़ा">OBC / EBC (पिछड़ा वर्ग)</option>
                                <option value="अनुसूचित जाति">SC (अनुसूचित जाति)</option>
                                <option value="अनुसूचित जनजाति">ST (अनुसूचित जनजाति)</option>
                                <option value="अनारक्षित">Unreserved (सामान्य)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table Pagination & Page Size Toolbar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted fw-bold mb-0">Show</label>
                            <select id="sarpanchPageSize" class="form-select form-select-sm" style="width: 85px;">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                            <label class="small text-muted mb-0">per page</label>
                        </div>
                        <div class="small text-muted" id="sarpanchPageInfo">
                            Loading Sarpanchs...
                        </div>
                        <div id="sarpanchTopPagination"></div>
                    </div>

                    <!-- Sarpanch Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small" id="sarpanchTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">District</th>
                                    <th class="py-3">Block / प्रखंड</th>
                                    <th class="py-3">Gram Panchayat</th>
                                    <th class="py-3">Elected Sarpanch (सरपंच का नाम)</th>
                                    <th class="py-3">Gender & Age</th>
                                    <th class="py-3">Reservation & Category</th>
                                    <th class="py-3">Contact & Address</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                foreach ($sarpanchs as $s): 
                                    $sDistSlug = (string)($s['district_slug'] ?? '');
                                    $sDist = (string)($s['district'] ?? '');
                                    $sBlock = (string)($s['block'] ?? '');
                                    $sPanchayat = (string)($s['panchayat'] ?? '');
                                    $sName = (string)($s['candidate_name'] ?? '');
                                    $sFh = (string)($s['father_husband_name'] ?? '');
                                    $sGen = (string)($s['gender'] ?? '');
                                    $sGenHi = (string)($s['gender_hi'] ?? '');
                                    $sAge = $s['age'] ?? null;
                                    $sCat = (string)($s['category'] ?? '');
                                    $sRes = (string)($s['reservation'] ?? '');
                                    $sAddr = (string)($s['address'] ?? '');
                                    $sMob = (string)($s['mobile'] ?? '');
                                ?>
                                    <tr class="sarpanch-row"
                                        data-district="<?php echo htmlspecialchars($sDistSlug); ?>"
                                        data-district-name="<?php echo htmlspecialchars(strtolower($sDist)); ?>"
                                        data-block="<?php echo htmlspecialchars(strtolower($sBlock)); ?>"
                                        data-panchayat="<?php echo htmlspecialchars(strtolower($sPanchayat)); ?>"
                                        data-name="<?php echo htmlspecialchars(strtolower($sName . ' ' . $sFh)); ?>"
                                        data-gender="<?php echo htmlspecialchars($sGen); ?>"
                                        data-category="<?php echo htmlspecialchars(strtolower($sCat . ' ' . $sRes)); ?>">
                                        
                                        <!-- District -->
                                        <td class="fw-bold" style="min-width: 120px;">
                                            <a href="district.php?slug=<?php echo htmlspecialchars($sDistSlug); ?>" class="text-decoration-none" style="color: var(--primary-navy);">
                                                <?php echo htmlspecialchars($sDist); ?>
                                            </a>
                                        </td>

                                        <!-- Block -->
                                        <td class="fw-semibold text-dark" style="min-width: 120px;">
                                            <?php echo htmlspecialchars($sBlock); ?>
                                        </td>

                                        <!-- Panchayat -->
                                        <td style="min-width: 160px;">
                                            <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                ⚖️ <?php echo htmlspecialchars($sPanchayat); ?>
                                            </span>
                                        </td>

                                        <!-- Sarpanch Candidate Name -->
                                        <td style="min-width: 200px;">
                                            <div class="fw-bold text-primary" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($sName); ?>
                                            </div>
                                            <?php if (!empty($sFh)): ?>
                                                <div class="text-muted small">W/o or S/o: <?php echo htmlspecialchars($sFh); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Gender & Age -->
                                        <td style="min-width: 120px;">
                                            <span class="badge <?php echo $sGen === 'Female' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'; ?> fw-semibold">
                                                <?php echo htmlspecialchars($sGenHi ?: $sGen); ?> <?php echo $sAge ? "({$sAge} yrs)" : ''; ?>
                                            </span>
                                        </td>

                                        <!-- Reservation & Category -->
                                        <td style="min-width: 160px;">
                                            <?php if (!empty($sRes)): ?>
                                                <div><span class="badge bg-light text-dark border extra-small"><?php echo htmlspecialchars($sRes); ?></span></div>
                                            <?php endif; ?>
                                            <?php if (!empty($sCat)): ?>
                                                <div class="text-muted small mt-1"><?php echo htmlspecialchars($sCat); ?></div>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Contact & Address -->
                                        <td style="min-width: 180px;">
                                            <?php if (!empty($sMob)): ?>
                                                <span class="badge bg-light text-secondary border py-1 px-2 extra-small d-inline-flex align-items-center gap-1 mb-1" title="Contact Protected">
                                                    <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($sMob)); ?>
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($sAddr)): ?>
                                                <div class="text-muted small text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($sAddr); ?>">
                                                    <?php echo htmlspecialchars($sAddr); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>

                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Pagination Bar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <div class="small text-muted" id="sarpanchBottomInfo"></div>
                        <div id="sarpanchPagination"></div>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 3: ZILA PARISHAD MEMBERS DIRECTORY                    -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'zila' ? 'show active' : ''; ?>" id="zila-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white" id="zila-directory">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-warning bg-opacity-10 text-warning-emphasis fw-bold px-2 py-1">District Apex Tier</span>
                                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Tenure: 2021–2026</span>
                            </div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                🏛️ Bihar Zila Parishad Members Table (जिला परिषद् सदस्य सूची)
                            </h2>
                            <p class="small text-muted mb-0">Searchable database of all 1,099 elected territorial ward members across all 38 districts</p>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill px-3 py-2" id="totalMembersCount">
                                <?php echo count($zilaMembers); ?> Members Loaded
                            </span>
                        </div>
                    </div>

                    <!-- Search & Filter Controls -->
                    <div class="row g-2 mb-4 bg-light p-3 rounded-3 border">
                        <div class="col-12 col-lg-4">
                            <label class="form-label small fw-bold text-muted mb-1">Search Ward Member / Block:</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="globalZilaSearch" class="form-control border-start-0" placeholder="Search by member name, block, ward no...">
                            </div>
                        </div>
                        <div class="col-6 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1">Filter District:</label>
                            <select id="globalDistrictFilter" class="form-select bg-white">
                                <option value="">All 38 Districts</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars((string)($d['slug'] ?? '')); ?>">
                                        <?php echo htmlspecialchars((string)($d['name'] ?? '')); ?> (<?php echo htmlspecialchars((string)($d['name_hi'] ?? '')); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-lg-2">
                            <label class="form-label small fw-bold text-muted mb-1">Gender:</label>
                            <select id="globalGenderFilter" class="form-select bg-white">
                                <option value="">All Genders</option>
                                <option value="Female">महिला (Women)</option>
                                <option value="Male">पुरूष (Men)</option>
                            </select>
                        </div>
                        <div class="col-12 col-lg-3">
                            <label class="form-label small fw-bold text-muted mb-1">Reservation / Category:</label>
                            <select id="globalCategoryFilter" class="form-select bg-white">
                                <option value="">All Categories</option>
                                <option value="महिला">Women Reserved</option>
                                <option value="पिछड़ा">OBC / EBC (पिछड़ा वर्ग)</option>
                                <option value="अनुसूचित जाति">SC (अनुसूचित जाति)</option>
                                <option value="अनुसूचित जनजाति">ST (अनुसूचित जनजाति)</option>
                                <option value="अनारक्षित">Unreserved (सामान्य)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table Pagination & Page Size Toolbar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <label class="small text-muted fw-bold mb-0">Show</label>
                            <select id="zilaPageSize" class="form-select form-select-sm" style="width: 85px;">
                                <option value="25">25</option>
                                <option value="50" selected>50</option>
                                <option value="100">100</option>
                                <option value="250">250</option>
                            </select>
                            <label class="small text-muted mb-0">per page</label>
                        </div>
                        <div class="small text-muted" id="zilaPageInfo">
                            Loading Members...
                        </div>
                        <div id="zilaTopPagination"></div>
                    </div>

                    <!-- Responsive Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small" id="allZilaTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">District</th>
                                    <th class="py-3">Ward / क्षे० सं०</th>
                                    <th class="py-3">Block / प्रखंड</th>
                                    <th class="py-3">Elected Member</th>
                                    <th class="py-3">Gender / Category</th>
                                    <th class="py-3">Reservation Status</th>
                                    <th class="py-3">Contact / Address</th>
                                </tr>
                            </thead>
                            <tbody id="zilaTableBody">
                                <?php foreach ($zilaMembers as $m): 
                                    $zmDistSlug = (string)($m['district_slug'] ?? '');
                                    $zmDist = (string)($m['district'] ?? '');
                                    $zmBlock = (string)($m['block'] ?? '');
                                    $zmWard = (string)($m['territory_no'] ?? '');
                                    $zmName = (string)($m['candidate_name'] ?? '');
                                    $zmFh = (string)($m['father_husband_name'] ?? '');
                                    $zmGen = (string)($m['gender'] ?? '');
                                    $zmGenHi = (string)($m['gender_hi'] ?? '');
                                    $zmAge = $m['age'] ?? null;
                                    $zmCat = (string)($m['category'] ?? '');
                                    $zmRes = (string)($m['reservation'] ?? '');
                                    $zmAddr = (string)($m['address'] ?? '');
                                    $zmMob = (string)($m['mobile'] ?? '');
                                ?>
                                    <tr class="global-zila-row"
                                        data-name="<?php echo htmlspecialchars(strtolower($zmName . ' ' . $zmFh)); ?>"
                                        data-district="<?php echo htmlspecialchars($zmDistSlug); ?>"
                                        data-district-name="<?php echo htmlspecialchars(strtolower($zmDist)); ?>"
                                        data-block="<?php echo htmlspecialchars(strtolower($zmBlock)); ?>"
                                        data-ward="<?php echo htmlspecialchars($zmWard); ?>"
                                        data-gender="<?php echo htmlspecialchars($zmGen); ?>"
                                        data-category="<?php echo htmlspecialchars(strtolower($zmCat . ' ' . $zmRes)); ?>">
                                        <td class="fw-bold">
                                            <a href="district.php?slug=<?php echo htmlspecialchars($zmDistSlug); ?>" class="text-decoration-none" style="color: var(--primary-navy);">
                                                <?php echo htmlspecialchars($zmDist); ?>
                                            </a>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary rounded-pill px-2 py-1">
                                                #<?php echo htmlspecialchars($zmWard); ?>
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">
                                            <?php echo htmlspecialchars($zmBlock); ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-primary" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($zmName); ?>
                                            </div>
                                            <?php if (!empty($zmFh)): ?>
                                                <div class="text-muted small">W/o or S/o: <?php echo htmlspecialchars($zmFh); ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div>
                                                <span class="badge <?php echo $zmGen === 'Female' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'; ?> fw-semibold">
                                                    <?php echo htmlspecialchars($zmGenHi ?: $zmGen); ?> <?php echo $zmAge ? "({$zmAge} yrs)" : ''; ?>
                                                </span>
                                            </div>
                                            <div class="text-muted small mt-1"><?php echo htmlspecialchars($zmCat); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($zmRes ?: 'General'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($zmMob)): ?>
                                                <span class="badge bg-light text-secondary border py-1 px-2 fw-semibold mb-1 d-inline-flex align-items-center gap-1" title="Contact Protected">
                                                    <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($zmMob)); ?>
                                                </span>
                                            <?php endif; ?>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($zmAddr); ?>">
                                                <?php echo htmlspecialchars($zmAddr); ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom Pagination Bar -->
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                        <div class="small text-muted" id="zilaBottomInfo"></div>
                        <div id="zilaPagination"></div>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 4: DISTRICT CHAIRPERSONS & VICE-CHAIRPERSONS          -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'officials' ? 'show active' : ''; ?>" id="officials-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                👑 Bihar Zila Parishad Chairpersons & Vice-Chairpersons (All 38 Districts)
                            </h2>
                            <p class="small text-muted mb-0">District Panchayat Board Heads (अध्यक्ष) and Deputy Heads (उपाध्यक्ष)</p>
                        </div>
                    </div>

                    <div class="row g-3">
                        <?php foreach ($districts as $d): 
                            $slug = $d['slug'] ?? '';
                            $summary = $zilaSummary[$slug] ?? null;
                            $ch = $summary['chairman'] ?? null;
                            $vch = $summary['vice_chairman'] ?? null;
                        ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 bg-light border-top border-4 border-warning">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h3 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                                            <a href="district.php?slug=<?php echo htmlspecialchars((string)$slug); ?>" class="text-decoration-none text-dark">
                                                <?php echo htmlspecialchars((string)($d['name'] ?? '')); ?>
                                            </a>
                                        </h3>
                                        <span class="badge bg-secondary rounded-pill"><?php echo $summary['total_wards'] ?? 0; ?> Wards</span>
                                    </div>
                                    
                                    <!-- Chairman -->
                                    <div class="bg-white p-2 rounded-2 mb-2 border">
                                        <div class="small fw-bold text-warning-emphasis">अध्यक्ष / Chairperson</div>
                                        <div class="fw-bold text-primary"><?php echo !empty($ch['candidate_name']) ? htmlspecialchars((string)$ch['candidate_name']) : 'N/A'; ?></div>
                                        <?php if (!empty($ch['reservation'])): ?>
                                            <span class="badge bg-light text-dark border extra-small"><?php echo htmlspecialchars((string)$ch['reservation']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($ch['gender_hi'])): ?>
                                            <span class="badge bg-light text-secondary border extra-small"><?php echo htmlspecialchars((string)$ch['gender_hi']); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Vice Chairman -->
                                    <div class="bg-white p-2 rounded-2 border">
                                        <div class="small fw-bold text-info-emphasis">उपाध्यक्ष / Vice-Chairperson</div>
                                        <div class="fw-bold text-dark"><?php echo !empty($vch['candidate_name']) ? htmlspecialchars((string)$vch['candidate_name']) : 'N/A'; ?></div>
                                        <?php if (!empty($vch['category'])): ?>
                                            <span class="badge bg-light text-dark border extra-small"><?php echo htmlspecialchars((string)$vch['category']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($vch['gender_hi'])): ?>
                                            <span class="badge bg-light text-secondary border extra-small"><?php echo htmlspecialchars((string)$vch['gender_hi']); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="mt-3 text-end">
                                        <a href="district.php?slug=<?php echo htmlspecialchars((string)$slug); ?>" class="small fw-bold text-decoration-none" style="color: var(--secondary-navy);">
                                            View <?php echo htmlspecialchars((string)($d['name'] ?? '')); ?> Ward Roster &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 5: 2016 PANCHAYAT ELECTION HISTORICAL ARCHIVE         -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'archive2016' ? 'show active' : ''; ?>" id="archive2016-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary text-white fw-bold px-2 py-1">Historical Election Archive</span>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1">2016–2021 Tenure</span>
                            </div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                ⏳ 2016 Bihar Panchayat Election Master Archive
                            </h2>
                            <p class="small text-muted mb-0">Official elected representatives from the 2016 Bihar Panchayat Elections: <strong>8,045 Gram Panchayat Mukhiyas</strong> and <strong>389 Block Panchayat Samiti Pramukhs</strong></p>
                        </div>
                        <div>
                            <span class="badge bg-secondary rounded-pill px-3 py-2">
                                <?php echo count($mukhiyas2016); ?> Mukhiyas | <?php echo count($samiti2016); ?> Blocks
                            </span>
                        </div>
                    </div>

                    <!-- Inner Archive Navigation Pills -->
                    <ul class="nav nav-pills mb-3" id="archiveSubTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold py-1 px-3" id="archive-mukhiya-tab" data-bs-toggle="pill" data-bs-target="#archive-mukhiya-subpane" type="button" role="tab">
                                🌾 2016 Mukhiya Directory (8,045 Panchayats)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold py-1 px-3" id="archive-samiti-tab" data-bs-toggle="pill" data-bs-target="#archive-samiti-subpane" type="button" role="tab">
                                🏢 2016 Block Pramukh & Samiti (389 Blocks)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="archiveSubTabContent">

                        <!-- SUBTAB 1: 2016 MUKHIYAS DIRECTORY -->
                        <div class="tab-pane fade show active" id="archive-mukhiya-subpane" role="tabpanel">
                            
                            <!-- Search & Filter Controls -->
                            <div class="row g-2 mb-3 bg-light p-3 rounded-3 border">
                                <div class="col-12 col-lg-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Search 2016 Mukhiya / Panchayat / Block:</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" id="mukhiya2016Search" class="form-control border-start-0" placeholder="Search by name, panchayat, block...">
                                    </div>
                                </div>
                                <div class="col-12 col-lg-6">
                                    <label class="form-label small fw-bold text-muted mb-1">Filter District:</label>
                                    <select id="mukhiya2016DistrictFilter" class="form-select bg-white" onchange="if(this.value){ window.location.href='<?php echo SITE_URL; ?>/panchayat-samiti/'+this.value; } else { window.location.href='<?php echo SITE_URL; ?>/panchayat-samiti'; }">
                                        <option value="">All 38 Districts</option>
                                        <?php foreach ($districts as $d): ?>
                                            <option value="<?php echo htmlspecialchars((string)($d['slug'] ?? '')); ?>" <?php echo $selectedDistrict === ($d['slug'] ?? '') ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars((string)($d['name'] ?? '')); ?> (<?php echo htmlspecialchars((string)($d['name_hi'] ?? '')); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Table Pagination & Page Size Toolbar -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="small text-muted fw-bold mb-0">Show</label>
                                    <select id="mukhiya2016PageSize" class="form-select form-select-sm" style="width: 85px;">
                                        <option value="25">25</option>
                                        <option value="50" selected>50</option>
                                        <option value="100">100</option>
                                        <option value="250">250</option>
                                    </select>
                                    <label class="small text-muted mb-0">per page</label>
                                </div>
                                <div class="small text-muted" id="mukhiya2016PageInfo">
                                    Loading 2016 Mukhiyas...
                                </div>
                                <div id="mukhiya2016TopPagination"></div>
                            </div>

                            <!-- 2016 Mukhiya Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small" id="mukhiya2016Table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-3">#</th>
                                            <th class="py-3">District</th>
                                            <th class="py-3">Block / प्रखंड</th>
                                            <th class="py-3">Gram Panchayat</th>
                                            <th class="py-3">2016 Elected Mukhiya</th>
                                            <th class="py-3">Age & Education</th>
                                            <th class="py-3">Contact & Address</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $m16Idx = 1;
                                        foreach ($mukhiyas2016 as $m16): 
                                            $m16Dist = (string)($m16['district'] ?? '');
                                            $m16DistSlug = (string)($m16['district_slug'] ?? '');
                                            $m16Block = (string)($m16['block'] ?? '');
                                            $m16Panch = (string)($m16['panchayat'] ?? '');
                                            $m16Name = (string)($m16['candidate_name'] ?? '');
                                            $m16Fh = (string)($m16['father_husband_name'] ?? '');
                                            $m16Mob = (string)($m16['mobile'] ?? '');
                                            $m16Age = $m16['age'] ?? null;
                                            $m16Qual = (string)($m16['qualification'] ?? '');
                                            $m16Addr = (string)($m16['address'] ?? '');
                                        ?>
                                            <tr class="mukhiya-2016-row"
                                                data-district="<?php echo htmlspecialchars($m16DistSlug); ?>"
                                                data-name="<?php echo htmlspecialchars(strtolower($m16Name . ' ' . $m16Fh . ' ' . $m16Panch . ' ' . $m16Block)); ?>">
                                                <td class="text-muted fw-bold"><?php echo $m16Idx++; ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo htmlspecialchars($m16Dist); ?>
                                                    </span>
                                                </td>
                                                <td class="fw-semibold text-dark">
                                                    <?php echo htmlspecialchars($m16Block); ?>
                                                </td>
                                                <td>
                                                    <span class="fw-bold" style="color: var(--primary-navy);">
                                                        🌾 <?php echo htmlspecialchars($m16Panch); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-success" style="font-size: 0.95rem;">
                                                        <i class="bi bi-person-badge-fill me-1"></i>
                                                        <?php echo htmlspecialchars($m16Name); ?>
                                                    </div>
                                                    <?php if (!empty($m16Fh)): ?>
                                                        <div class="text-muted extra-small">W/o or S/o: <?php echo htmlspecialchars($m16Fh); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if ($m16Age): ?>
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-semibold"><?php echo $m16Age; ?> yrs</span>
                                                    <?php endif; ?>
                                                    <?php if (!empty($m16Qual)): ?>
                                                        <div class="text-muted extra-small mt-1"><?php echo htmlspecialchars($m16Qual); ?></div>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($m16Mob)): ?>
                                                        <span class="badge bg-light text-secondary border py-1 px-2 extra-small d-inline-flex align-items-center gap-1 mb-1" title="Contact Protected">
                                                            <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($m16Mob)); ?>
                                                        </span>
                                                    <?php endif; ?>
                                                    <div class="text-muted extra-small text-truncate" style="max-width: 200px;" title="<?php echo htmlspecialchars($m16Addr); ?>">
                                                        <?php echo htmlspecialchars($m16Addr); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bottom Pagination Bar -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                                <div class="small text-muted" id="mukhiya2016BottomInfo"></div>
                                <div id="mukhiya2016Pagination"></div>
                            </div>
                        </div>

                        <!-- SUBTAB 2: 2016 BLOCK SAMITI PRAMUKHS -->
                        <div class="tab-pane fade" id="archive-samiti-subpane" role="tabpanel">
                            
                            <!-- Search Controls -->
                            <div class="row g-2 mb-3 bg-light p-3 rounded-3 border">
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted mb-1">Search Block Pramukh / Block Name:</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" id="samiti2016Search" class="form-control border-start-0" placeholder="Search block name, pramukh name...">
                                    </div>
                                </div>
                            </div>

                            <!-- Table Pagination & Page Size Toolbar -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <label class="small text-muted fw-bold mb-0">Show</label>
                                    <select id="samiti2016PageSize" class="form-select form-select-sm" style="width: 85px;">
                                        <option value="25">25</option>
                                        <option value="50" selected>50</option>
                                        <option value="100">100</option>
                                    </select>
                                    <label class="small text-muted mb-0">per page</label>
                                </div>
                                <div class="small text-muted" id="samiti2016PageInfo">
                                    Loading Blocks...
                                </div>
                                <div id="samiti2016TopPagination"></div>
                            </div>

                            <!-- 2016 Block Samiti Table -->
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small" id="samiti2016Table">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="py-3">#</th>
                                            <th class="py-3">District (जिला)</th>
                                            <th class="py-3">Block (प्रखंड)</th>
                                            <th class="py-3">Pramukh 2016 (प्रमुख पदधारक)</th>
                                            <th class="py-3">Up-Pramukh 2016 (उप प्रमुख पदधारक)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $sIndex = 1; foreach ($samiti2016 as $sb): ?>
                                            <tr class="samiti-2016-row"
                                                data-name="<?php echo htmlspecialchars(strtolower($sb['block_hi'] . ' ' . $sb['block'] . ' ' . $sb['pramukh_2016'] . ' ' . $sb['up_pramukh_2016'])); ?>"
                                                data-district="<?php echo htmlspecialchars($sb['district_slug']); ?>">
                                                <td class="text-muted fw-bold"><?php echo $sIndex++; ?></td>
                                                <td>
                                                    <span class="badge bg-light text-dark border">
                                                        <?php echo htmlspecialchars($sb['district']); ?> (<?php echo htmlspecialchars($sb['district_hi']); ?>)
                                                    </span>
                                                </td>
                                                <td class="fw-bold" style="color: var(--primary-navy); font-size: 0.95rem;">
                                                    <?php echo htmlspecialchars($sb['block_hi'] ?: $sb['block']); ?>
                                                </td>
                                                <td>
                                                    <div class="fw-bold text-success" style="font-size: 0.95rem;">
                                                        <i class="bi bi-person-check-fill me-1"></i>
                                                        <?php echo htmlspecialchars($sb['pramukh_2016'] ?: 'Not Disclosed'); ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="fw-semibold text-primary">
                                                        <?php echo htmlspecialchars($sb['up_pramukh_2016'] ?: 'Not Disclosed'); ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Bottom Pagination Bar -->
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-3 pt-3 border-top">
                                <div class="small text-muted" id="samiti2016BottomInfo"></div>
                                <div id="samiti2016Pagination"></div>
                            </div>
                        </div>

                    </div>
                </section>
            </div>

        </div>

        <!-- FAQ Section -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mt-4">
            <h2 class="h4 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                ❓ Bihar Panchayati Raj: Frequently Asked Questions (FAQ)
            </h2>
            <div class="d-flex flex-column gap-3">
                <div class="bg-light p-3 rounded-3">
                    <h3 class="h6 fw-bold mb-1" style="color: var(--primary-navy);">What is the difference between Mukhiya and Sarpanch in Bihar?</h3>
                    <p class="small text-muted mb-0">
                        In Bihar Panchayati Raj, <strong>Mukhiya</strong> is the executive head of the Gram Panchayat responsible for local governance, village welfare schemes, rural roads, water, and development funds. <strong>Sarpanch</strong> is the judicial head of the Gram Katchahry (ग्राम कचहरी) empowered to resolve minor civil disputes, property boundary conflicts, and petty criminal complaints through consensual arbitration.
                    </p>
                </div>
                <div class="bg-light p-3 rounded-3">
                    <h3 class="h6 fw-bold mb-1" style="color: var(--primary-navy);">What is the term of Bihar Mukhiya, Sarpanch, and Zila Parishad members?</h3>
                    <p class="small text-muted mb-0">
                        All representatives in Bihar Panchayati Raj are elected for a tenure of 5 years. The current board tenure runs from 2021 through 2026.
                    </p>
                </div>
                <div class="bg-light p-3 rounded-3">
                    <h3 class="h6 fw-bold mb-1" style="color: var(--primary-navy);">How is women reservation implemented in Bihar Panchayat Elections?</h3>
                    <p class="small text-muted mb-0">
                        Bihar provides statutory 50% horizontal quota across all categories (Scheduled Castes, Scheduled Tribes, Extremely Backward Classes, and General) for women candidates contesting Mukhiya, Sarpanch, Ward Member, Panchayat Samiti, and Zila Parishad posts.
                    </p>
                </div>
            </div>
        </section>

        <!-- Bottom Footer Ad Banner -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'mt-4'); ?>

    </main>

    <!-- Client-side Fast Filter & High-Performance Pagination JS -->
    <script>
    class TablePaginator {
        constructor(config) {
            this.tableId = config.tableId;
            this.rowSelector = config.rowSelector;
            this.pageSizeSelectId = config.pageSizeSelectId;
            this.pageInfoId = config.pageInfoId;
            this.bottomInfoId = config.bottomInfoId;
            this.paginationContainerId = config.paginationContainerId;
            this.topPaginationContainerId = config.topPaginationContainerId;
            this.countBadgeId = config.countBadgeId;
            this.unitName = config.unitName || 'Entries';
            this.pageSize = config.defaultPageSize || 50;
            this.currentPage = 1;
            this.allRows = Array.from(document.querySelectorAll(this.rowSelector));
            this.filteredRows = [...this.allRows];

            this.init();
        }

        init() {
            const pageSizeSelect = document.getElementById(this.pageSizeSelectId);
            if (pageSizeSelect) {
                pageSizeSelect.addEventListener('change', (e) => {
                    this.pageSize = parseInt(e.target.value, 10) || 50;
                    this.currentPage = 1;
                    this.render();
                });
            }
            this.render();
        }

        setFilteredRows(rows) {
            this.filteredRows = rows;
            this.currentPage = 1;
            this.render();
        }

        goToPage(page) {
            const totalPages = Math.ceil(this.filteredRows.length / this.pageSize) || 1;
            if (page < 1) page = 1;
            if (page > totalPages) page = totalPages;
            this.currentPage = page;
            this.render();

            const table = document.getElementById(this.tableId);
            if (table) {
                const rect = table.getBoundingClientRect();
                if (rect.top < 80) {
                    table.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        }

        render() {
            const totalItems = this.filteredRows.length;
            const totalPages = Math.ceil(totalItems / this.pageSize) || 1;
            if (this.currentPage > totalPages) this.currentPage = totalPages;

            const startIndex = (this.currentPage - 1) * this.pageSize;
            const endIndex = Math.min(startIndex + this.pageSize, totalItems);

            // Hide all rows
            this.allRows.forEach(r => r.style.display = 'none');

            // Show current page items
            for (let i = startIndex; i < endIndex; i++) {
                if (this.filteredRows[i]) {
                    this.filteredRows[i].style.display = '';
                }
            }

            const infoHtml = totalItems === 0 
                ? `No matching ${this.unitName.toLowerCase()} found`
                : `Showing <strong>${(startIndex + 1).toLocaleString()}</strong> to <strong>${endIndex.toLocaleString()}</strong> of <strong>${totalItems.toLocaleString()}</strong> ${this.unitName}`;

            const pageInfo = document.getElementById(this.pageInfoId);
            if (pageInfo) pageInfo.innerHTML = infoHtml;

            const bottomInfo = document.getElementById(this.bottomInfoId);
            if (bottomInfo) bottomInfo.innerHTML = infoHtml + (totalPages > 1 ? ` (Page ${this.currentPage} of ${totalPages})` : '');

            if (this.countBadgeId) {
                const badge = document.getElementById(this.countBadgeId);
                if (badge) badge.textContent = `${totalItems.toLocaleString()} ${this.unitName}`;
            }

            this.renderPaginationButtons(this.paginationContainerId, totalPages);
            if (this.topPaginationContainerId) {
                this.renderTopPaginationButtons(this.topPaginationContainerId, totalPages);
            }
        }

        renderPaginationButtons(containerId, totalPages) {
            const container = document.getElementById(containerId);
            if (!container) return;

            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = '<ul class="pagination pagination-sm mb-0 flex-wrap justify-content-center justify-content-md-end">';

            html += `<li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(1)" title="First Page">« First</a>
            </li>`;
            html += `<li class="page-item ${this.currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(${this.currentPage - 1})" title="Previous Page">‹ Prev</a>
            </li>`;

            const maxPages = 5;
            let start = Math.max(1, this.currentPage - Math.floor(maxPages / 2));
            let end = Math.min(totalPages, start + maxPages - 1);
            if (end - start + 1 < maxPages) {
                start = Math.max(1, end - maxPages + 1);
            }

            if (start > 1) {
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(1)">1</a></li>`;
                if (start > 2) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
            }

            for (let p = start; p <= end; p++) {
                html += `<li class="page-item ${p === this.currentPage ? 'active' : ''}">
                    <a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(${p})">${p}</a>
                </li>`;
            }

            if (end < totalPages) {
                if (end < totalPages - 1) html += `<li class="page-item disabled"><span class="page-link">...</span></li>`;
                html += `<li class="page-item"><a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(${totalPages})">${totalPages}</a></li>`;
            }

            html += `<li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(${this.currentPage + 1})" title="Next Page">Next ›</a>
            </li>`;
            html += `<li class="page-item ${this.currentPage === totalPages ? 'disabled' : ''}">
                <a class="page-link" href="javascript:void(0)" onclick="window['${this.tableId}_paginator'].goToPage(${totalPages})" title="Last Page">Last »</a>
            </li>`;

            html += '</ul>';
            container.innerHTML = html;
        }

        renderTopPaginationButtons(containerId, totalPages) {
            const container = document.getElementById(containerId);
            if (!container) return;

            if (totalPages <= 1) {
                container.innerHTML = '';
                return;
            }

            let html = `<div class="btn-group btn-group-sm">
                <button class="btn btn-outline-secondary" ${this.currentPage === 1 ? 'disabled' : ''} onclick="window['${this.tableId}_paginator'].goToPage(${this.currentPage - 1})" title="Previous Page">‹</button>
                <span class="btn btn-light text-dark fw-bold border" style="cursor: default; font-size: 0.8rem;">Page ${this.currentPage} / ${totalPages}</span>
                <button class="btn btn-outline-secondary" ${this.currentPage === totalPages ? 'disabled' : ''} onclick="window['${this.tableId}_paginator'].goToPage(${this.currentPage + 1})" title="Next Page">›</button>
            </div>`;
            container.innerHTML = html;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Mukhiya Paginator & Filter
        window['mukhiyaTable_paginator'] = new TablePaginator({
            tableId: 'mukhiyaTable',
            rowSelector: '#mukhiyaTable .mukhiya-row',
            pageSizeSelectId: 'mukhiyaPageSize',
            pageInfoId: 'mukhiyaPageInfo',
            bottomInfoId: 'mukhiyaBottomInfo',
            paginationContainerId: 'mukhiyaPagination',
            topPaginationContainerId: 'mukhiyaTopPagination',
            countBadgeId: 'totalMukhiyaBadge',
            unitName: 'Mukhiyas',
            defaultPageSize: 50
        });

        const mSearch = document.getElementById('mukhiyaSearch');
        const mGenderFilter = document.getElementById('mukhiyaGenderFilter');
        const mCategoryFilter = document.getElementById('mukhiyaCategoryFilter');

        function filterMukhiyaRows() {
            const query = (mSearch?.value || '').toLowerCase().trim();
            const selectedGender = mGenderFilter?.value || '';
            const selectedCat = (mCategoryFilter?.value || '').toLowerCase().trim();

            const matched = window['mukhiyaTable_paginator'].allRows.filter(row => {
                const districtName = (row.getAttribute('data-district-name') || '');
                const block = (row.getAttribute('data-block') || '');
                const panchayat = (row.getAttribute('data-panchayat') || '');
                const name = (row.getAttribute('data-name') || '');
                const gender = (row.getAttribute('data-gender') || '');
                const category = (row.getAttribute('data-category') || '');

                const matchesQuery = !query || districtName.includes(query) || block.includes(query) || panchayat.includes(query) || name.includes(query);
                const matchesGender = !selectedGender || gender === selectedGender;
                const matchesCat = !selectedCat || category.includes(selectedCat);

                return matchesQuery && matchesGender && matchesCat;
            });

            window['mukhiyaTable_paginator'].setFilteredRows(matched);
        }

        if (mSearch) mSearch.addEventListener('input', filterMukhiyaRows);
        if (mGenderFilter) mGenderFilter.addEventListener('change', filterMukhiyaRows);
        if (mCategoryFilter) mCategoryFilter.addEventListener('change', filterMukhiyaRows);

        // 2. Sarpanch Paginator & Filter
        window['sarpanchTable_paginator'] = new TablePaginator({
            tableId: 'sarpanchTable',
            rowSelector: '#sarpanchTable .sarpanch-row',
            pageSizeSelectId: 'sarpanchPageSize',
            pageInfoId: 'sarpanchPageInfo',
            bottomInfoId: 'sarpanchBottomInfo',
            paginationContainerId: 'sarpanchPagination',
            topPaginationContainerId: 'sarpanchTopPagination',
            countBadgeId: 'totalSarpanchBadge',
            unitName: 'Sarpanchs',
            defaultPageSize: 50
        });

        const sSearch = document.getElementById('sarpanchSearch');
        const sGenderFilter = document.getElementById('sarpanchGenderFilter');
        const sCategoryFilter = document.getElementById('sarpanchCategoryFilter');

        function filterSarpanchRows() {
            const query = (sSearch?.value || '').toLowerCase().trim();
            const selectedGender = sGenderFilter?.value || '';
            const selectedCat = (sCategoryFilter?.value || '').toLowerCase().trim();

            const matched = window['sarpanchTable_paginator'].allRows.filter(row => {
                const districtName = (row.getAttribute('data-district-name') || '');
                const block = (row.getAttribute('data-block') || '');
                const panchayat = (row.getAttribute('data-panchayat') || '');
                const name = (row.getAttribute('data-name') || '');
                const gender = (row.getAttribute('data-gender') || '');
                const category = (row.getAttribute('data-category') || '');

                const matchesQuery = !query || districtName.includes(query) || block.includes(query) || panchayat.includes(query) || name.includes(query);
                const matchesGender = !selectedGender || gender === selectedGender;
                const matchesCat = !selectedCat || category.includes(selectedCat);

                return matchesQuery && matchesGender && matchesCat;
            });

            window['sarpanchTable_paginator'].setFilteredRows(matched);
        }

        if (sSearch) sSearch.addEventListener('input', filterSarpanchRows);
        if (sGenderFilter) sGenderFilter.addEventListener('change', filterSarpanchRows);
        if (sCategoryFilter) sCategoryFilter.addEventListener('change', filterSarpanchRows);

        // 3. Zila Parishad Paginator & Filter
        window['allZilaTable_paginator'] = new TablePaginator({
            tableId: 'allZilaTable',
            rowSelector: '#allZilaTable .global-zila-row',
            pageSizeSelectId: 'zilaPageSize',
            pageInfoId: 'zilaPageInfo',
            bottomInfoId: 'zilaBottomInfo',
            paginationContainerId: 'zilaPagination',
            topPaginationContainerId: 'zilaTopPagination',
            countBadgeId: 'totalMembersCount',
            unitName: 'Members',
            defaultPageSize: 50
        });

        const zSearch = document.getElementById('globalZilaSearch');
        const zDistrictFilter = document.getElementById('globalDistrictFilter');
        const zGenderFilter = document.getElementById('globalGenderFilter');
        const zCategoryFilter = document.getElementById('globalCategoryFilter');

        function filterAllZilaRows() {
            const query = (zSearch?.value || '').toLowerCase().trim();
            const selectedDistrict = (zDistrictFilter?.value || '').toLowerCase().trim();
            const selectedGender = zGenderFilter?.value || '';
            const selectedCat = (zCategoryFilter?.value || '').toLowerCase().trim();

            const matched = window['allZilaTable_paginator'].allRows.filter(row => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                const district = (row.getAttribute('data-district') || '').toLowerCase();
                const districtName = (row.getAttribute('data-district-name') || '').toLowerCase();
                const block = (row.getAttribute('data-block') || '').toLowerCase();
                const ward = (row.getAttribute('data-ward') || '').toLowerCase();
                const gender = row.getAttribute('data-gender') || '';
                const category = (row.getAttribute('data-category') || '').toLowerCase();

                const matchesQuery = !query || name.includes(query) || block.includes(query) || ward.includes(query) || districtName.includes(query);
                const matchesDistrict = !selectedDistrict || district === selectedDistrict;
                const matchesGender = !selectedGender || gender === selectedGender;
                const matchesCat = !selectedCat || category.includes(selectedCat);

                return matchesQuery && matchesDistrict && matchesGender && matchesCat;
            });

            window['allZilaTable_paginator'].setFilteredRows(matched);
        }

        if (zSearch) zSearch.addEventListener('input', filterAllZilaRows);
        if (zDistrictFilter) zDistrictFilter.addEventListener('change', filterAllZilaRows);
        if (zGenderFilter) zGenderFilter.addEventListener('change', filterAllZilaRows);
        if (zCategoryFilter) zCategoryFilter.addEventListener('change', filterAllZilaRows);

        // 4. 2016 Mukhiya Paginator & Filter
        window['mukhiya2016Table_paginator'] = new TablePaginator({
            tableId: 'mukhiya2016Table',
            rowSelector: '#mukhiya2016Table .mukhiya-2016-row',
            pageSizeSelectId: 'mukhiya2016PageSize',
            pageInfoId: 'mukhiya2016PageInfo',
            bottomInfoId: 'mukhiya2016BottomInfo',
            paginationContainerId: 'mukhiya2016Pagination',
            topPaginationContainerId: 'mukhiya2016TopPagination',
            unitName: '2016 Mukhiyas',
            defaultPageSize: 50
        });

        const m16Search = document.getElementById('mukhiya2016Search');
        if (m16Search) {
            m16Search.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const matched = window['mukhiya2016Table_paginator'].allRows.filter(row => {
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    return !query || name.includes(query);
                });
                window['mukhiya2016Table_paginator'].setFilteredRows(matched);
            });
        }

        // 5. 2016 Block Samiti Paginator & Filter
        window['samiti2016Table_paginator'] = new TablePaginator({
            tableId: 'samiti2016Table',
            rowSelector: '#samiti2016Table .samiti-2016-row',
            pageSizeSelectId: 'samiti2016PageSize',
            pageInfoId: 'samiti2016PageInfo',
            bottomInfoId: 'samiti2016BottomInfo',
            paginationContainerId: 'samiti2016Pagination',
            topPaginationContainerId: 'samiti2016TopPagination',
            unitName: 'Block Samitis',
            defaultPageSize: 50
        });

        const samiti2016Search = document.getElementById('samiti2016Search');
        if (samiti2016Search) {
            samiti2016Search.addEventListener('input', function() {
                const query = this.value.toLowerCase().trim();
                const matched = window['samiti2016Table_paginator'].allRows.filter(row => {
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    return !query || name.includes(query);
                });
                window['samiti2016Table_paginator'].setFilteredRows(matched);
            });
        // Auto pre-filter if specific panchayat or block is requested via clean URL
        <?php if (!empty($selectedPanchayat)): ?>
        const initPanchayatQuery = <?php echo json_encode($selectedPanchayat); ?>;
        if (mSearch) { mSearch.value = initPanchayatQuery; filterMukhiyaRows(); }
        if (sSearch) { sSearch.value = initPanchayatQuery; filterSarpanchRows(); }
        <?php endif; ?>

        <?php if (!empty($selectedBlock)): ?>
        const initBlockQuery = <?php echo json_encode($selectedBlock); ?>;
        if (samiti2016Search) { samiti2016Search.value = initBlockQuery; }
        <?php endif; ?>
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
