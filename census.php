<?php
/**
 * BiharElection.com - Bihar Census 2011 & Demographics Platform
 * Covers All 38 Districts, 534 Blocks, 198 Towns & 44,874 Villages
 */
require_once __DIR__ . '/config.php';

$activeTab = $_GET['tab'] ?? 'districts';
$selectedDistrictFilter = $_GET['district'] ?? '';
$selectedSubdistrict = $_GET['subdistrict'] ?? '';
$selectedCivic = $_GET['civic'] ?? '';
$searchQuery = trim($_GET['q'] ?? '');
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;

// Dynamic SEO titles based on tab
if ($activeTab === 'villages') {
    $distLabel = !empty($selectedDistrictFilter) ? ucfirst($selectedDistrictFilter) . ' District ' : 'Bihar ';
    $pageTitle = "{$distLabel}Census 2011 Village Directory: 44,874 Villages Demographic & Population Table";
    $pageDescription = "Official 2011 District Census Handbook (DCHB) demographic directory of all 44,874 villages in Bihar across 38 districts and 534 blocks. Population, sex ratio, SC/ST, and Gram Panchayat data.";
} elseif ($activeTab === 'towns') {
    $distLabel = !empty($selectedDistrictFilter) ? ucfirst($selectedDistrictFilter) . ' District ' : 'Bihar ';
    $pageTitle = "{$distLabel}Census 2011 Towns Directory: 198 Nagar Nigam, Parishad & Census Towns Table";
    $pageDescription = "Census 2011 demographic matrix of all 198 Statutory and Census Towns (CT) in Bihar. Nagar Nigam, Nagar Parishad, Nagar Panchayat population, banks, and economic data.";
} elseif ($activeTab === 'blocks') {
    $pageTitle = 'Bihar Census 2011: 534 Sub-Districts & Blocks Demographics Matrix';
    $pageDescription = 'Sub-district / Block-level Census 2011 population data, literacy rates, household counts, and SC/ST profiles across all 38 districts of Bihar.';
} elseif ($activeTab === 'social') {
    $pageTitle = 'Bihar Demographic & Workforce Intelligence Matrix (Census 2011)';
    $pageDescription = 'In-depth analysis of Bihar population, literacy gender gaps, child sex ratio, and workforce classification from Census 2011 Primary Census Abstract.';
} else {
    $pageTitle = 'Bihar Census 2011: 38 Districts, 534 Blocks, 198 Towns & 44,874 Villages Matrix';
    $pageDescription = 'Complete Bihar Census 2011 Primary Census Abstract (PCA) and District Census Handbook (DCHB). Population data, sex ratio, literacy rates, SC/ST demographics for 38 districts, 534 blocks, 198 towns, and 44,874 villages.';
}

if (!empty($selectedDistrictFilter) && !empty($selectedSubdistrict)) {
    $pageCanonical = getCensusUrl($selectedDistrictFilter, $selectedSubdistrict);
} elseif (!empty($selectedDistrictFilter)) {
    $pageCanonical = getCensusUrl($selectedDistrictFilter);
} else {
    $pageCanonical = getCensusUrl();
}

$biharCensus = DataProvider::getCensusBiharSummary();
$districtsCensus = DataProvider::getCensusDistricts();
$subDistrictsAll = DataProvider::getCensusSubDistricts();
$districtsList = DataProvider::getDistricts();

$cTot = [
    'population' => 104099452,
    'male' => 54278157,
    'female' => 49821295,
    'sex_ratio' => 918,
    'households' => 18913565,
    'literates' => 52504553,
    'literacy_rate' => 61.80,
    'sc_population' => 16567325,
    'sc_percentage' => 15.91,
    'st_population' => 1336573,
    'st_percentage' => 1.28,
    'total_workers' => 34725175,
    'cultivators' => 7048742,
    'agricultural_labourers' => 18345649,
    'non_workers' => 69374277,
    'pop_0_6' => 0
];

$cRur = [
    'population' => 92341436,
    'male' => 48073850,
    'female' => 44267586,
    'sex_ratio' => 921,
    'households' => 16936300,
    'literates' => 44100000,
    'literacy_rate' => 59.78,
    'sc_population' => 15300000,
    'st_population' => 1250000,
    'pop_0_6' => 0
];

$cUrb = [
    'population' => 11758016,
    'male' => 6204307,
    'female' => 5553709,
    'sex_ratio' => 895,
    'households' => 1977265,
    'literates' => 8404553,
    'literacy_rate' => 76.86,
    'sc_population' => 1267325,
    'st_population' => 86573,
    'pop_0_6' => 0
];

// Tab-specific data fetching
$villageResult = ['total' => 0, 'data' => []];
$townResult = ['total' => 0, 'data' => []];
$civicStatuses = [];

if ($activeTab === 'villages') {
    $villageResult = DataProvider::getCensusVillagesList($selectedDistrictFilter, $selectedSubdistrict, null, $searchQuery, $currentPage, $perPage);
} elseif ($activeTab === 'towns') {
    $townResult = DataProvider::getCensusTownsList($selectedDistrictFilter, $selectedCivic, $searchQuery, $currentPage, $perPage);
    $civicStatuses = DataProvider::getCensusTownCivicStatuses();
}

require_once __DIR__ . '/header.php';
?>

    <!-- Census Hero Header -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    <i class="bi bi-file-earmark-bar-graph"></i> Official Census 2011 (PCA &amp; DCHB)
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    38 Districts
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    534 Blocks
                </span>
                <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2">
                    198 Towns
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2">
                    44,874 Villages
                </span>
                <span class="badge bg-danger bg-opacity-25 text-white fw-bold px-3 py-2">
                    10.41 Crore Population
                </span>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">
                Bihar Census 2011 &amp; Demographic Intelligence Hub
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 920px;">
                Comprehensive demographic, literacy, caste category (SC/ST), household amenities, and infrastructure directory covering all <strong>38 Districts</strong>, <strong>534 Sub-Districts</strong>, <strong>198 Towns</strong>, and <strong>44,874 Census Villages</strong> in Bihar.
            </p>

            <!-- State Summary KPI Row -->
            <div class="row g-2 g-md-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Total Population</small>
                        <span class="fs-5 fw-bold text-warning"><?php echo number_format($cTot['population'] ?? 104099452); ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">10.41 Crore</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Sex Ratio</small>
                        <span class="fs-5 fw-bold text-info"><?php echo $cTot['sex_ratio'] ?? 918; ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">Females / 1000 Males</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Literacy Rate</small>
                        <span class="fs-5 fw-bold text-success"><?php echo $cTot['literacy_rate'] ?? 61.80; ?>%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">5.25 Cr Literates</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Total Households</small>
                        <span class="fs-5 fw-bold text-white"><?php echo number_format($cTot['households'] ?? 18913565); ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">1.89 Crore Homes</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Rural Share</small>
                        <span class="fs-5 fw-bold text-warning">88.7%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">44,874 Villages</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Urban Share</small>
                        <span class="fs-5 fw-bold text-info">11.3%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">198 Towns</small>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container my-4 my-lg-5">

        <!-- Top Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Nav Navigation Tabs -->
        <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
            <ul class="nav nav-pills nav-fill gap-2 flex-wrap" id="censusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=districts" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'districts' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-geo-alt-fill me-1 text-danger"></i> 38 Districts Matrix
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=blocks" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'blocks' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-diagram-3-fill me-1 text-warning"></i> 534 Blocks Directory
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=villages<?php echo !empty($selectedDistrictFilter) ? '&district=' . urlencode($selectedDistrictFilter) : ''; ?>" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'villages' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-house-door-fill me-1 text-success"></i> 🏡 44,874 Villages Directory
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=towns<?php echo !empty($selectedDistrictFilter) ? '&district=' . urlencode($selectedDistrictFilter) : ''; ?>" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'towns' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-buildings-fill me-1 text-info"></i> 🏙️ 198 Towns Directory
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=social" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'social' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-pie-chart-fill me-1 text-primary"></i> State Social Matrix
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="<?php echo getCasteSurveyUrl(); ?>" class="nav-link rounded-3 py-2 fw-bold text-dark bg-warning bg-opacity-25 border border-warning">
                        <i class="bi bi-card-checklist me-1 text-dark"></i> 2022 Caste Survey <span class="badge bg-danger ms-1">New</span>
                    </a>
                </li>
            </ul>
        </div>

        <?php if ($activeTab === 'districts'): ?>
        <!-- ========================================================================= -->
        <!-- TAB 1: 38 DISTRICTS CENSUS MATRIX                                        -->
        <!-- ========================================================================= -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        All 38 Bihar Districts Census 2011 Demographic Table
                    </h2>
                    <p class="small text-muted mb-0">Search, compare and explore district population, literacy, and gender parity</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="districtTableSearch" class="form-control border-start-0 bg-light" placeholder="Search district name...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="districtsCensusTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">District</th>
                            <th class="py-3 text-end">Households</th>
                            <th class="py-3 text-end">Total Population</th>
                            <th class="py-3 text-end">Male / Female</th>
                            <th class="py-3 text-center">Sex Ratio</th>
                            <th class="py-3 text-center">Literacy %</th>
                            <th class="py-3 text-end">SC Pop. (%)</th>
                            <th class="py-3 text-center">Rural / Urban</th>
                            <th class="py-3 text-center">Explore</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $dIdx = 1;
                        foreach ($districtsCensus as $slug => $d): 
                            $totPop = (int)($d['population'] ?? 0);
                            $rurPop = (int)($d['rural_population'] ?? 0);
                            $urbPop = (int)($d['urban_population'] ?? 0);
                            $rPct = !empty($totPop) ? round(($rurPop / $totPop) * 100, 1) : 0;
                            $uPct = !empty($totPop) ? round(($urbPop / $totPop) * 100, 1) : 0;
                            $hh = (int)($d['households'] ?? 0);
                            $male = (int)($d['male'] ?? 0);
                            $female = (int)($d['female'] ?? 0);
                            $sr = (int)($d['sex_ratio'] ?? 0);
                            $litRate = (float)($d['literacy_rate'] ?? 0);
                            $scPop = (int)($d['sc_population'] ?? 0);
                            $scPct = (float)($d['sc_percentage'] ?? 0);
                            $subCount = count($subDistrictsAll[$slug] ?? []);
                        ?>
                        <tr class="district-census-row" data-name="<?php echo htmlspecialchars(strtolower($d['name'])); ?>">
                            <td class="text-muted fw-bold"><?php echo $dIdx++; ?></td>
                            <td>
                                <a href="district.php?slug=<?php echo $slug; ?>" class="fw-bold text-decoration-none text-primary fs-6">
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </a>
                                <div class="small text-muted"><?php echo $subCount > 0 ? $subCount . ' Blocks' : 'District Hub'; ?></div>
                            </td>
                            <td class="text-end font-monospace"><?php echo number_format($hh); ?></td>
                            <td class="text-end fw-bold text-dark fs-6 font-monospace">
                                <?php echo number_format($totPop); ?>
                            </td>
                            <td class="text-end small">
                                <span class="text-primary">M: <?php echo number_format($male); ?></span><br>
                                <span class="text-danger">F: <?php echo number_format($female); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo $sr >= 918 ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill px-2 py-1">
                                    <?php echo $sr; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold <?php echo $litRate >= 61.8 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo number_format($litRate, 2); ?>%
                                </span>
                            </td>
                            <td class="text-end">
                                <div><?php echo number_format($scPop); ?></div>
                                <small class="text-muted">(<?php echo number_format($scPct, 2); ?>%)</small>
                            </td>
                            <td class="text-center small" style="min-width: 120px;">
                                <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.72rem;">
                                    <span><?php echo $rPct; ?>% R</span>
                                    <span><?php echo $uPct; ?>% U</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $rPct; ?>%" title="Rural: <?php echo number_format($rurPop); ?>"></div>
                                    <div class="progress-bar bg-info" style="width: <?php echo $uPct; ?>%" title="Urban: <?php echo number_format($urbPop); ?>"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="census.php?tab=villages&district=<?php echo $slug; ?>" class="btn btn-outline-success btn-sm rounded-pill px-2 me-1" title="View Villages in <?php echo htmlspecialchars($d['name']); ?>">
                                        🏡 Villages
                                    </a>
                                    <a href="census.php?tab=towns&district=<?php echo $slug; ?>" class="btn btn-outline-info btn-sm rounded-pill px-2" title="View Towns in <?php echo htmlspecialchars($d['name']); ?>">
                                        🏙️ Towns
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php elseif ($activeTab === 'blocks'): ?>
        <!-- ========================================================================= -->
        <!-- TAB 2: 534 SUB-DISTRICTS / BLOCKS DIRECTORY                               -->
        <!-- ========================================================================= -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        All 534 Bihar Sub-Districts / Blocks Census Directory
                    </h2>
                    <p class="small text-muted mb-0">Sub-district level population, households, literacy, and SC/ST metrics</p>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <select id="districtFilterSelect" class="form-select form-select-sm" style="max-width: 200px;">
                        <option value="">All 38 Districts</option>
                        <?php foreach ($districtsCensus as $dSlug => $dVal): ?>
                            <option value="<?php echo htmlspecialchars($dSlug); ?>" <?php echo $selectedDistrictFilter === $dSlug ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($dVal['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="input-group input-group-sm" style="max-width: 220px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="subDistTableSearch" class="form-control border-start-0 bg-light" placeholder="Search block name...">
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="subDistrictsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Sub-District / Block</th>
                            <th class="py-3">District</th>
                            <th class="py-3 text-end">Households</th>
                            <th class="py-3 text-end">Population</th>
                            <th class="py-3 text-end">Male</th>
                            <th class="py-3 text-end">Female</th>
                            <th class="py-3 text-center">Sex Ratio</th>
                            <th class="py-3 text-center">Literacy %</th>
                            <th class="py-3 text-end">SC Pop.</th>
                            <th class="py-3 text-end">ST Pop.</th>
                            <th class="py-3 text-center">Villages</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $sIdx = 1;
                        foreach ($subDistrictsAll as $dSlug => $subs):
                            foreach ($subs as $sb):
                        ?>
                        <tr class="sub-dist-row" 
                            data-district="<?php echo htmlspecialchars($dSlug); ?>" 
                            data-name="<?php echo htmlspecialchars(strtolower($sb['sub_district'] . ' ' . $sb['district_name'])); ?>">
                            <td class="text-muted fw-bold"><?php echo $sIdx++; ?></td>
                            <td class="fw-bold text-dark">
                                <?php echo htmlspecialchars($sb['sub_district']); ?>
                            </td>
                            <td>
                                <a href="district.php?slug=<?php echo $dSlug; ?>" class="text-decoration-none text-primary">
                                    <?php echo htmlspecialchars($sb['district_name']); ?>
                                </a>
                            </td>
                            <td class="text-end"><?php echo number_format($sb['households'] ?? 0); ?></td>
                            <td class="text-end fw-bold"><?php echo number_format($sb['population'] ?? 0); ?></td>
                            <td class="text-end text-muted"><?php echo number_format($sb['male'] ?? 0); ?></td>
                            <td class="text-end text-muted"><?php echo number_format($sb['female'] ?? 0); ?></td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border"><?php echo $sb['sex_ratio'] ?? 0; ?></span>
                            </td>
                            <td class="text-center fw-bold <?php echo ($sb['literacy_rate'] ?? 0) >= 61.8 ? 'text-success' : 'text-danger'; ?>">
                                <?php echo $sb['literacy_rate'] ?? 0; ?>%
                            </td>
                            <td class="text-end"><?php echo number_format($sb['sc_population'] ?? 0); ?></td>
                            <td class="text-end"><?php echo number_format($sb['st_population'] ?? 0); ?></td>
                            <td class="text-center">
                                <a href="census.php?tab=villages&district=<?php echo $dSlug; ?>&subdistrict=<?php echo urlencode($sb['sub_district']); ?>" class="btn btn-outline-success btn-sm rounded-pill px-2 py-0 fw-semibold" style="font-size: 0.78rem;">
                                    View &rarr;
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php elseif ($activeTab === 'villages'): ?>
        <!-- ========================================================================= -->
        <!-- TAB 3: 44,874 CENSUS VILLAGES DIRECTORY                                   -->
        <!-- ========================================================================= -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">District Census Handbook (DCHB)</span>
                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold px-2 py-1">Total: 44,874 Villages</span>
                    </div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        🏡 Bihar Census 2011 Village Directory
                        <?php if (!empty($selectedDistrictFilter)): ?>
                            <span class="text-primary">- <?php echo htmlspecialchars(ucfirst($selectedDistrictFilter)); ?> District</span>
                        <?php endif; ?>
                    </h2>
                    <p class="small text-muted mb-0">Explore complete demographic, household, SC/ST, area, and connectivity data for all census villages in Bihar</p>
                </div>
            </div>

            <!-- Village Filter & Search Bar -->
            <form method="GET" action="census.php" class="row g-2 mb-3 p-3 bg-light rounded-3 align-items-center">
                <input type="hidden" name="tab" value="villages">
                
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Filter by District:</label>
                    <select name="district" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All 38 Districts</option>
                        <?php foreach ($districtsList as $d): ?>
                            <option value="<?php echo htmlspecialchars($d['slug']); ?>" <?php echo $selectedDistrictFilter === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Sub-District / CD Block:</label>
                    <input type="text" name="subdistrict" class="form-control form-control-sm" placeholder="e.g. Chapra, Amnour, Danapur" value="<?php echo htmlspecialchars($selectedSubdistrict); ?>">
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Search Village / GP / Code:</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Village name, code or Gram Panchayat..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-1 align-items-end mt-md-4">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <?php if (!empty($selectedDistrictFilter) || !empty($selectedSubdistrict) || !empty($searchQuery)): ?>
                        <a href="census.php?tab=villages" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Result Counter -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="small text-muted">
                    Found <strong><?php echo number_format($villageResult['total']); ?></strong> villages
                    <?php if (!empty($selectedDistrictFilter)): ?> in <strong><?php echo htmlspecialchars(ucfirst($selectedDistrictFilter)); ?></strong><?php endif; ?>
                    <?php if (!empty($searchQuery)): ?> matching "<em><?php echo htmlspecialchars($searchQuery); ?></em>"<?php endif; ?>
                </span>
                <span class="small text-muted">
                    Page <strong><?php echo $currentPage; ?></strong> of <strong><?php echo max(1, ceil($villageResult['total'] / $perPage)); ?></strong>
                </span>
            </div>

            <!-- Villages Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="villagesCensusTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Village Name &amp; Census Code</th>
                            <th class="py-3">Gram Panchayat</th>
                            <th class="py-3">CD Block &amp; District</th>
                            <th class="py-3 text-end">Households</th>
                            <th class="py-3 text-end">Total Population</th>
                            <th class="py-3 text-end">Male / Female</th>
                            <th class="py-3 text-center">Sex Ratio</th>
                            <th class="py-3 text-end">SC / ST</th>
                            <th class="py-3 text-end">Area (Ha)</th>
                            <th class="py-3 text-start">Nearest Town</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($villageResult['data'])): ?>
                            <?php 
                            $vIdx = ($currentPage - 1) * $perPage + 1;
                            foreach ($villageResult['data'] as $v): 
                            ?>
                            <tr>
                                <td class="text-muted fw-bold"><?php echo $vIdx++; ?></td>
                                <td>
                                    <?php 
                                    $vUrl = getVillageUrl($v['district_slug'], slugify($v['sub_district_name'] ?: $v['cd_block_name']), $v['village_slug']);
                                    ?>
                                    <a href="<?php echo htmlspecialchars($vUrl); ?>" class="fw-bold text-navy text-decoration-none hover-primary fs-6">
                                        🏡 <?php echo htmlspecialchars($v['village_name']); ?>
                                    </a>
                                    <div><small class="text-muted font-monospace"><i class="bi bi-upc"></i> Code: <?php echo htmlspecialchars($v['village_code']); ?></small></div>
                                </td>
                                <td>
                                    <?php if (!empty($v['gram_panchayat_name'])): ?>
                                        <span class="badge bg-light text-dark border">
                                            🌾 <?php echo htmlspecialchars($v['gram_panchayat_name']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-secondary"><?php echo htmlspecialchars($v['sub_district_name'] ?: $v['cd_block_name']); ?></div>
                                    <a href="district.php?slug=<?php echo htmlspecialchars($v['district_slug']); ?>" class="text-decoration-none small text-primary">
                                        <?php echo htmlspecialchars($v['district_name']); ?>
                                    </a>
                                </td>
                                <td class="text-end"><?php echo number_format($v['households'] ?? 0); ?></td>
                                <td class="text-end fw-bold text-dark fs-6"><?php echo number_format($v['population'] ?? 0); ?></td>
                                <td class="text-end small">
                                    <span class="text-primary">M: <?php echo number_format($v['male'] ?? 0); ?></span><br>
                                    <span class="text-danger">F: <?php echo number_format($v['female'] ?? 0); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo ($v['sex_ratio'] ?? 0) >= 918 ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill px-2 py-1">
                                        <?php echo $v['sex_ratio'] ?? 0; ?>
                                    </span>
                                </td>
                                <td class="text-end small">
                                    <div>SC: <strong><?php echo number_format($v['sc_population'] ?? 0); ?></strong></div>
                                    <div class="text-muted">ST: <?php echo number_format($v['st_population'] ?? 0); ?></div>
                                </td>
                                <td class="text-end font-monospace"><?php echo number_format($v['area_hectares'] ?? 0, 1); ?></td>
                                <td class="text-start small">
                                    <?php if (!empty($v['nearest_town_name'])): ?>
                                        <div><i class="bi bi-geo-alt text-danger"></i> <?php echo htmlspecialchars((string)$v['nearest_town_name']); ?></div>
                                        <?php 
                                        $dist = $v['nearest_town_distance'] ?? null;
                                        if ($dist !== null && $dist !== ''): 
                                        ?>
                                            <small class="text-muted"><?php echo htmlspecialchars((string)$dist); ?> km away</small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                    No census villages found matching your filter criteria. Try adjusting the district or search keyword.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php 
            $totalPages = max(1, ceil($villageResult['total'] / $perPage));
            if ($totalPages > 1): 
                $queryParams = $_GET;
                unset($queryParams['page']);
                $baseUrl = 'census.php?' . http_build_query($queryParams);
            ?>
            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                <div class="small text-muted">
                    Showing <?php echo number_format(($currentPage - 1) * $perPage + 1); ?> - <?php echo number_format(min($villageResult['total'], $currentPage * $perPage)); ?> of <?php echo number_format($villageResult['total']); ?> villages
                </div>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . ($currentPage - 1); ?>" aria-label="Previous">
                                &laquo; Prev
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startP = max(1, $currentPage - 2);
                    $endP = min($totalPages, $currentPage + 2);
                    if ($startP > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
                        if ($startP > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    for ($p = $startP; $p <= $endP; $p++):
                    ?>
                        <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . $p; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; 
                    if ($endP < $totalPages) {
                        if ($endP < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . ($currentPage + 1); ?>" aria-label="Next">
                                Next &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </section>

        <?php elseif ($activeTab === 'towns'): ?>
        <!-- ========================================================================= -->
        <!-- TAB 4: 198 STATUTORY & CENSUS TOWNS DIRECTORY                             -->
        <!-- ========================================================================= -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-info bg-opacity-10 text-info fw-bold px-2 py-1">Statutory &amp; Census Towns</span>
                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold px-2 py-1">Total: 198 Towns</span>
                    </div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        🏙️ Bihar Census 2011 Towns &amp; Urban Directory
                        <?php if (!empty($selectedDistrictFilter)): ?>
                            <span class="text-primary">- <?php echo htmlspecialchars(ucfirst($selectedDistrictFilter)); ?> District</span>
                        <?php endif; ?>
                    </h2>
                    <p class="small text-muted mb-0">Comprehensive urban demographic metrics, civic status (Nagar Nigam / Parishad / Panchayat / CT), banking institutions &amp; manufactured commodities</p>
                </div>
            </div>

            <!-- Town Filter & Search Bar -->
            <form method="GET" action="census.php" class="row g-2 mb-3 p-3 bg-light rounded-3 align-items-center">
                <input type="hidden" name="tab" value="towns">
                
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Filter by District:</label>
                    <select name="district" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All 38 Districts</option>
                        <?php foreach ($districtsList as $d): ?>
                            <option value="<?php echo htmlspecialchars($d['slug']); ?>" <?php echo $selectedDistrictFilter === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Civic Status:</label>
                    <select name="civic" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All Civic Statuses</option>
                        <?php foreach ($civicStatuses as $cs): ?>
                            <option value="<?php echo htmlspecialchars($cs['civic_status']); ?>" <?php echo $selectedCivic === $cs['civic_status'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cs['civic_status']); ?> (<?php echo $cs['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-muted mb-1">Search Town Name / Code:</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Search town name, class or block..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                    </div>
                </div>

                <div class="col-12 col-md-2 d-flex gap-1 align-items-end mt-md-4">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="bi bi-funnel-fill"></i> Filter
                    </button>
                    <?php if (!empty($selectedDistrictFilter) || !empty($selectedCivic) || !empty($searchQuery)): ?>
                        <a href="census.php?tab=towns" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>

            <!-- Result Counter -->
            <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                <span class="small text-muted">
                    Found <strong><?php echo number_format($townResult['total']); ?></strong> towns
                    <?php if (!empty($selectedDistrictFilter)): ?> in <strong><?php echo htmlspecialchars(ucfirst($selectedDistrictFilter)); ?></strong><?php endif; ?>
                    <?php if (!empty($selectedCivic)): ?> (Civic: <em><?php echo htmlspecialchars($selectedCivic); ?></em>)<?php endif; ?>
                </span>
                <span class="small text-muted">
                    Page <strong><?php echo $currentPage; ?></strong> of <strong><?php echo max(1, ceil($townResult['total'] / $perPage)); ?></strong>
                </span>
            </div>

            <!-- Towns Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="townsCensusTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Town Name &amp; Code</th>
                            <th class="py-3">District &amp; Block</th>
                            <th class="py-3 text-center">Civic Status</th>
                            <th class="py-3 text-center">Class</th>
                            <th class="py-3 text-end">Households</th>
                            <th class="py-3 text-end">Total Population</th>
                            <th class="py-3 text-end">Male / Female</th>
                            <th class="py-3 text-center">Sex Ratio</th>
                            <th class="py-3 text-end">SC / ST</th>
                            <th class="py-3 text-end">Area (sq km)</th>
                            <th class="py-3 text-center">Banks</th>
                            <th class="py-3">Key Manufactured Commodities</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($townResult['data'])): ?>
                            <?php 
                            $tIdx = ($currentPage - 1) * $perPage + 1;
                            foreach ($townResult['data'] as $t): 
                                $civicBadge = 'bg-secondary';
                                if (stripos($t['civic_status'], 'Nigam') !== false || stripos($t['civic_status'], 'M.Corp') !== false) {
                                    $civicBadge = 'bg-danger text-white';
                                } elseif (stripos($t['civic_status'], 'Parishad') !== false || stripos($t['civic_status'], 'N.P.') !== false || stripos($t['civic_status'], 'M') !== false) {
                                    $civicBadge = 'bg-primary text-white';
                                } elseif (stripos($t['civic_status'], 'Panchayat') !== false || stripos($t['civic_status'], 'N.P') !== false) {
                                    $civicBadge = 'bg-warning text-dark';
                                } elseif (stripos($t['civic_status'], 'CT') !== false) {
                                    $civicBadge = 'bg-info text-dark';
                                }
                            ?>
                            <tr>
                                <td class="text-muted fw-bold"><?php echo $tIdx++; ?></td>
                                <td>
                                    <?php 
                                    $tUrl = getTownUrl($t['district_slug'], $t['town_slug']);
                                    $slumsCount = (int)($t['slum_count'] ?? 0);
                                    ?>
                                    <a href="<?php echo htmlspecialchars($tUrl); ?>" class="fw-bold text-navy text-decoration-none hover-primary fs-6">
                                        🏙️ <?php echo htmlspecialchars($t['town_name']); ?>
                                    </a>
                                    <div class="d-flex align-items-center gap-1 mt-0.5">
                                        <small class="text-muted font-monospace"><i class="bi bi-upc"></i> Code: <?php echo htmlspecialchars($t['town_code']); ?></small>
                                        <?php if ($slumsCount > 0): ?>
                                            <span class="badge bg-warning-subtle text-dark small ms-1">🛖 <?php echo $slumsCount; ?> Slums</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <a href="district.php?slug=<?php echo htmlspecialchars($t['district_slug']); ?>" class="text-decoration-none fw-semibold text-primary">
                                        <?php echo htmlspecialchars($t['district_name']); ?>
                                    </a>
                                    <?php if (!empty($t['cd_block_name'])): ?>
                                        <div class="small text-muted"><?php echo htmlspecialchars($t['cd_block_name']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo $civicBadge; ?> rounded-pill px-2 py-1">
                                        <?php echo htmlspecialchars($t['civic_status']); ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border">
                                        Class <?php echo htmlspecialchars($t['town_class'] ?: 'IV'); ?>
                                    </span>
                                </td>
                                <td class="text-end"><?php echo number_format($t['households'] ?? 0); ?></td>
                                <td class="text-end fw-bold text-dark fs-6"><?php echo number_format($t['population'] ?? 0); ?></td>
                                <td class="text-end small">
                                    <span class="text-primary">M: <?php echo number_format($t['male'] ?? 0); ?></span><br>
                                    <span class="text-danger">F: <?php echo number_format($t['female'] ?? 0); ?></span>
                                </td>
                                <td class="text-center">
                                    <span class="badge <?php echo ($t['sex_ratio'] ?? 0) >= 918 ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill px-2 py-1">
                                        <?php echo $t['sex_ratio'] ?? 0; ?>
                                    </span>
                                </td>
                                <td class="text-end small">
                                    <div>SC: <strong><?php echo number_format($t['sc_population'] ?? 0); ?></strong></div>
                                    <div class="text-muted">ST: <?php echo number_format($t['st_population'] ?? 0); ?></div>
                                </td>
                                <td class="text-end font-monospace"><?php echo number_format($t['area_sq_km'] ?? 0, 2); ?></td>
                                <td class="text-center small">
                                    <div title="Nationalised / Commercial / Cooperative">
                                        🏦 <strong><?php echo (int)$t['nationalised_banks'] + (int)$t['commercial_banks'] + (int)$t['cooperative_banks']; ?></strong>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.7rem;">
                                        Nat:<?php echo (int)$t['nationalised_banks']; ?> | Comm:<?php echo (int)$t['commercial_banks']; ?>
                                    </small>
                                </td>
                                <td class="small">
                                    <?php 
                                    $items = array_filter([$t['manufactured_1'], $t['manufactured_2'], $t['manufactured_3']]);
                                    if (!empty($items)):
                                    ?>
                                        <div class="text-secondary"><?php echo htmlspecialchars(implode(', ', $items)); ?></div>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="13" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 text-muted opacity-50"></i>
                                    No census towns found matching your filter criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php 
            $totalPages = max(1, ceil($townResult['total'] / $perPage));
            if ($totalPages > 1): 
                $queryParams = $_GET;
                unset($queryParams['page']);
                $baseUrl = 'census.php?' . http_build_query($queryParams);
            ?>
            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                <div class="small text-muted">
                    Showing <?php echo number_format(($currentPage - 1) * $perPage + 1); ?> - <?php echo number_format(min($townResult['total'], $currentPage * $perPage)); ?> of <?php echo number_format($townResult['total']); ?> towns
                </div>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . ($currentPage - 1); ?>" aria-label="Previous">
                                &laquo; Prev
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startP = max(1, $currentPage - 2);
                    $endP = min($totalPages, $currentPage + 2);
                    if ($startP > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
                        if ($startP > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    for ($p = $startP; $p <= $endP; $p++):
                    ?>
                        <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . $p; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; 
                    if ($endP < $totalPages) {
                        if ($endP < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $baseUrl . '&page=' . ($currentPage + 1); ?>" aria-label="Next">
                                Next &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </section>

        <?php elseif ($activeTab === 'social'): ?>
        <!-- ========================================================================= -->
        <!-- TAB 5: STATE SOCIAL & WORKFORCE MATRIX                                    -->
        <!-- ========================================================================= -->
        <section class="mb-5">
            <div class="row g-4">
                
                <!-- Card 1: Gender & Demographic Profile -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-people-fill text-primary me-2"></i> Gender &amp; Demographic Profile
                        </h4>
                        
                        <div class="table-responsive">
                            <table class="table table-bordered mb-3 small align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Metric</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-end">Rural</th>
                                        <th class="text-end">Urban</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Total Population</td>
                                        <td class="text-end fw-bold"><?php echo number_format($cTot['population'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cRur['population'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cUrb['population'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Male Population</td>
                                        <td class="text-end text-primary"><?php echo number_format($cTot['male'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cRur['male'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cUrb['male'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Female Population</td>
                                        <td class="text-end text-danger"><?php echo number_format($cTot['female'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cRur['female'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cUrb['female'] ?? 0); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Overall Sex Ratio</td>
                                        <td class="text-end fw-bold text-success"><?php echo $cTot['sex_ratio'] ?? 0; ?></td>
                                        <td class="text-end"><?php echo $cRur['sex_ratio'] ?? 0; ?></td>
                                        <td class="text-end"><?php echo $cUrb['sex_ratio'] ?? 0; ?></td>
                                    </tr>
                                    <?php if (!empty($cTot['pop_0_6']) || !empty($cRur['pop_0_6']) || !empty($cUrb['pop_0_6'])): ?>
                                    <tr>
                                        <td>Child Population (0–6 Yrs)</td>
                                        <td class="text-end"><?php echo number_format($cTot['pop_0_6'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cRur['pop_0_6'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cUrb['pop_0_6'] ?? 0); ?></td>
                                    </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Literacy & Education Gap -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-mortarboard-fill text-success me-2"></i> Literacy Rate &amp; Gender Disparity
                        </h4>
                        
                        <div class="mb-4">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Overall Bihar Literacy</span>
                                <strong><?php echo $cTot['literacy_rate'] ?? 61.80; ?>%</strong>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: <?php echo $cTot['literacy_rate'] ?? 61.80; ?>%"></div>
                            </div>

                            <div class="d-flex justify-content-between small mb-1">
                                <span>Male Literacy Rate</span>
                                <strong class="text-primary">71.20%</strong>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-primary" style="width: 71.20%"></div>
                            </div>

                            <div class="d-flex justify-content-between small mb-1">
                                <span>Female Literacy Rate</span>
                                <strong class="text-danger">51.50%</strong>
                            </div>
                            <div class="progress mb-3" style="height: 8px;">
                                <div class="progress-bar bg-danger" style="width: 51.50%"></div>
                            </div>
                        </div>

                        <div class="row g-2 pt-2 border-top text-center small">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded-2">
                                    <span class="text-muted d-block">Total Literates</span>
                                    <strong class="text-dark fs-6"><?php echo number_format($cTot['literates'] ?? 0); ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded-2">
                                    <span class="text-muted d-block">Total Illiterates</span>
                                    <strong class="text-dark fs-6"><?php echo number_format($cTot['illiterates'] ?? 0); ?></strong>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Card 3: Economic & Workforce Profile -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h4 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-briefcase-fill text-warning me-2"></i> Bihar Employment &amp; Workforce Classification
                        </h4>

                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <span class="small text-muted text-uppercase fw-bold d-block">Total Workers</span>
                                    <span class="fs-5 fw-bold text-dark"><?php echo number_format($cTot['total_workers'] ?? 0); ?></span>
                                    <small class="text-muted d-block">33.36% of Population</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <span class="small text-muted text-uppercase fw-bold d-block">Cultivators (कृषक)</span>
                                    <span class="fs-5 fw-bold text-success"><?php echo number_format($cTot['cultivators'] ?? 0); ?></span>
                                    <small class="text-muted d-block">Main agricultural landholders</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <span class="small text-muted text-uppercase fw-bold d-block">Agri Labourers (मजदूर)</span>
                                    <span class="fs-5 fw-bold text-warning"><?php echo number_format($cTot['agricultural_labourers'] ?? 0); ?></span>
                                    <small class="text-muted d-block">Farm workforce</small>
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="p-3 bg-light rounded-3 text-center">
                                    <span class="small text-muted text-uppercase fw-bold d-block">Non-Workers</span>
                                    <span class="fs-5 fw-bold text-danger"><?php echo number_format($cTot['non_workers'] ?? 0); ?></span>
                                    <small class="text-muted d-block">Students, dependents &amp; seniors</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <?php endif; ?>

        <!-- Bottom Leaderboard Ad Slot -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // District search in Tab 1
        const dSearch = document.getElementById('districtTableSearch');
        const dRows = document.querySelectorAll('#districtsCensusTable .district-census-row');
        if (dSearch) {
            dSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                dRows.forEach(r => {
                    const name = (r.getAttribute('data-name') || '').toLowerCase();
                    r.style.display = (!q || name.includes(q)) ? '' : 'none';
                });
            });
        }

        // Sub-district filter in Tab 2
        const sFilter = document.getElementById('districtFilterSelect');
        const sSearch = document.getElementById('subDistTableSearch');
        const sRows = document.querySelectorAll('#subDistrictsTable .sub-dist-row');

        function filterSubRows() {
            const selectedDist = sFilter ? sFilter.value : '';
            const q = sSearch ? sSearch.value.toLowerCase().trim() : '';

            sRows.forEach(r => {
                const dist = r.getAttribute('data-district') || '';
                const name = (r.getAttribute('data-name') || '').toLowerCase();
                const matchDist = !selectedDist || dist === selectedDist;
                const matchQuery = !q || name.includes(q);

                r.style.display = (matchDist && matchQuery) ? '' : 'none';
            });
        }

        if (sFilter) sFilter.addEventListener('change', filterSubRows);
        if (sSearch) sSearch.addEventListener('input', filterSubRows);
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
