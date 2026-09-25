<?php
/**
 * BiharElection.com - Bihar Census 2011 Village Intelligence & Demographic Directory
 * Complete coverage of all 44,874 Census Villages across 38 Districts & 534 Blocks
 */
require_once __DIR__ . '/config.php';

$pdo = Database::getConnection();

// Input parameters resolution
$codeParam = trim($_GET['code'] ?? '');
$districtParam = trim($_GET['district'] ?? '');
$blockParam = trim($_GET['block'] ?? '');
$villageParam = trim($_GET['village'] ?? '');
$gpParam = trim($_GET['gp'] ?? '');
$searchParam = trim($_GET['q'] ?? '');
$slugParam = trim($_GET['slug'] ?? '');
$sortParam = trim($_GET['sort'] ?? 'pop_desc');
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;

$districtsList = DataProvider::getDistricts();

// Handle generic slug routing
if (empty($codeParam) && empty($villageParam) && !empty($slugParam)) {
    if (is_numeric($slugParam) && strlen($slugParam) >= 5) {
        $codeParam = $slugParam;
    } else {
        $matchedDist = DataProvider::getDistrictBySlug($slugParam);
        if ($matchedDist) {
            $districtParam = $slugParam;
        } else {
            // Check if slug matches a village directly
            $villageParam = $slugParam;
        }
    }
}

$village = null;

// 1. Try to find a specific village
if (!empty($codeParam)) {
    $village = DataProvider::getVillageByCode($codeParam);
} elseif (!empty($villageParam)) {
    if (is_numeric($villageParam) && strlen($villageParam) >= 5) {
        $village = DataProvider::getVillageByCode($villageParam);
    } else {
        $village = DataProvider::getVillageBySlug($districtParam, $blockParam, $villageParam);
    }
}

// 2. If single village is matched, load related governance & demographic data
if ($village) {
    $vName = $village['village_name'];
    $vCode = $village['village_code'];
    $dName = $village['district_name'];
    $dSlug = $village['district_slug'];
    $bName = $village['sub_district_name'] ?: ($village['cd_block_name'] ?: 'Block');
    $bSlug = $village['sub_district_slug'];
    $gpName = $village['gram_panchayat_name'] ?: '';
    $gpSlug = $village['gram_panchayat_slug'] ?: '';
    
    $pop = (int)($village['population'] ?? 0);
    $hh = (int)($village['households'] ?? 0);
    $sr = (int)($village['sex_ratio'] ?? 0);
    $male = (int)($village['male'] ?? 0);
    $female = (int)($village['female'] ?? 0);
    $scPop = (int)($village['sc_population'] ?? 0);
    $stPop = (int)($village['st_population'] ?? 0);
    $scPct = ($pop > 0) ? round(($scPop / $pop) * 100, 2) : 0;
    $stPct = ($pop > 0) ? round(($stPop / $pop) * 100, 2) : 0;
    $genObcPct = max(0, round(100 - ($scPct + $stPct), 2));

    $areaHectares = (float)($village['area_hectares'] ?? 0);
    $areaAcres = $areaHectares * 2.47105;
    $density = ($areaHectares > 0 && $pop > 0) ? round($pop / $areaHectares, 2) : 0;
    $avgFamilySize = ($hh > 0 && $pop > 0) ? round($pop / $hh, 1) : 0;

    $pageTitle = "{$vName} Village Population, Gram Panchayat & Census 2011 Data ({$bName}, {$dName})";
    $pageDescription = "Official 2011 Census demographic data for {$vName} Village (Code: {$vCode}), {$bName} Block, {$dName} District, Bihar. Population: " . number_format($pop) . ", Households: " . number_format($hh) . ", Sex Ratio: {$sr}.";
    $pageKeywords = "{$vName} village, {$vName} census 2011, {$vName} population, {$bName} block villages, {$dName} district village list, Bihar Census 2011 villages";
    $pageCanonical = getVillageUrl($dSlug, $bSlug, $village['village_slug']);

    // Fetch sibling villages in same Gram Panchayat or Block
    $nearbyVillages = DataProvider::getNearbyVillages($dSlug, $bSlug, $gpSlug, $village['id'], 8);

    // Fetch Gram Panchayat Elected Representatives (Mukhiya & Sarpanch)
    $panchayatObj = null;
    $blockSamiti = null;
    $zilaMembers = [];

    if ($pdo && !empty($gpName)) {
        try {
            $stmtP = $pdo->prepare("SELECT * FROM panchayats WHERE LOWER(district_slug) = :dslug AND (panchayat_name LIKE :pname OR slug LIKE :pslug) LIMIT 1");
            $stmtP->execute([':dslug' => $dSlug, ':pname' => '%' . $gpName . '%', ':pslug' => '%' . slugify($gpName) . '%']);
            $panchayatObj = $stmtP->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {}
    }

    // Fetch Block Samiti (Pramukh / Up-Pramukh)
    if ($pdo && !empty($bName)) {
        try {
            $stmtS = $pdo->prepare("SELECT * FROM panchayat_samiti_2016 WHERE LOWER(district_slug) = :dslug AND block LIKE :bname LIMIT 1");
            $stmtS->execute([':dslug' => $dSlug, ':bname' => '%' . $bName . '%']);
            $blockSamiti = $stmtS->fetch(PDO::FETCH_ASSOC) ?: null;
        } catch (Throwable $e) {}
    }
} else {
    // Directory Mode
    $distLabel = !empty($districtParam) ? ucfirst($districtParam) . ' District ' : 'Bihar ';
    $pageTitle = "{$distLabel}Census 2011 Village Directory: 44,874 Villages Population & Demographics";
    $pageDescription = "Explore the complete Census 2011 village directory of Bihar covering all 44,874 villages across 38 districts and 534 blocks. Filter by district, block, population and Gram Panchayat.";
    $pageKeywords = "Bihar village directory, Bihar 44874 villages, Census 2011 village list, Bihar gram panchayat villages, Bihar district census handbook";
    $pageCanonical = !empty($districtParam) ? getVillageUrl($districtParam) : SITE_URL . "/village";

    // Fetch CD Blocks for active district if selected
    $districtBlocks = [];
    $blockGps = [];
    if (!empty($districtParam) && $pdo) {
        try {
            $stmtB = $pdo->prepare("SELECT sub_district_slug, sub_district_name, COUNT(*) as village_count, SUM(population) as total_pop FROM census_villages_2011 WHERE district_slug = :dslug GROUP BY sub_district_slug, sub_district_name ORDER BY sub_district_name ASC");
            $stmtB->execute([':dslug' => strtolower(trim($districtParam))]);
            $districtBlocks = $stmtB->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}

        if (!empty($blockParam)) {
            try {
                $stmtG = $pdo->prepare("SELECT gram_panchayat_slug, gram_panchayat_name, COUNT(*) as village_count FROM census_villages_2011 WHERE district_slug = :dslug AND (sub_district_slug = :bslug OR cd_block_name LIKE :bslug_like) AND gram_panchayat_name IS NOT NULL AND gram_panchayat_name != '' GROUP BY gram_panchayat_slug, gram_panchayat_name ORDER BY gram_panchayat_name ASC");
                $stmtG->execute([
                    ':dslug' => strtolower(trim($districtParam)),
                    ':bslug' => strtolower(trim($blockParam)),
                    ':bslug_like' => '%' . trim($blockParam) . '%'
                ]);
                $blockGps = $stmtG->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {}
        }
    }

    // Build directory query with pagination & sorting
    $where = [];
    $params = [];

    if (!empty($districtParam)) {
        $where[] = "district_slug = :dslug";
        $params[':dslug'] = strtolower(trim($districtParam));
    }
    if (!empty($blockParam)) {
        $where[] = "(sub_district_slug = :bslug OR cd_block_name LIKE :bslug_like)";
        $params[':bslug'] = strtolower(trim($blockParam));
        $params[':bslug_like'] = '%' . trim($blockParam) . '%';
    }
    if (!empty($gpParam)) {
        $where[] = "(gram_panchayat_slug = :gpslug OR gram_panchayat_name LIKE :gp_like)";
        $params[':gpslug'] = strtolower(trim($gpParam));
        $params[':gp_like'] = '%' . trim($gpParam) . '%';
    }
    if (!empty($searchParam)) {
        $where[] = "(village_name LIKE :q OR village_code LIKE :q OR gram_panchayat_name LIKE :q OR cd_block_name LIKE :q OR district_name LIKE :q)";
        $params[':q'] = '%' . trim($searchParam) . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $orderBy = "district_name ASC, sub_district_name ASC, village_name ASC";
    if ($sortParam === 'pop_desc') $orderBy = "population DESC";
    elseif ($sortParam === 'pop_asc') $orderBy = "population ASC";
    elseif ($sortParam === 'area_desc') $orderBy = "area_hectares DESC";
    elseif ($sortParam === 'sex_desc') $orderBy = "sex_ratio DESC";

    $totalCount = 0;
    $villageList = [];

    if ($pdo) {
        try {
            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM census_villages_2011 $whereSql");
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            $offset = max(0, ($currentPage - 1) * $perPage);
            $stmt = $pdo->prepare("SELECT * FROM census_villages_2011 $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset");
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $villageList = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $villageList = [];
        }
    }
}

$activeNav = 'census';
require_once __DIR__ . '/header.php';
?>

<style>
/* Custom Badge & Pill Styling */
.badge-soft-primary { background-color: rgba(13, 110, 253, 0.12); color: #0d6efd; }
.badge-soft-success { background-color: rgba(25, 135, 84, 0.12); color: #198754; }
.badge-soft-warning { background-color: rgba(255, 193, 7, 0.18); color: #856404; }
.badge-soft-danger { background-color: rgba(220, 53, 69, 0.12); color: #dc3545; }
.badge-soft-info { background-color: rgba(13, 202, 240, 0.15); color: #055160; }

.block-pill-btn {
    font-size: 0.82rem;
    font-weight: 600;
    border-radius: 50px;
    padding: 0.35rem 0.85rem;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #495057;
    transition: all 0.2s ease-in-out;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.block-pill-btn:hover {
    background-color: #e9ecef;
    border-color: #0b1a30;
    color: #0b1a30;
    transform: translateY(-1px);
}
.block-pill-btn.active {
    background: linear-gradient(135deg, #0b1a30 0%, #17345f 100%);
    border-color: #0b1a30;
    color: #fff;
    box-shadow: 0 4px 10px rgba(11, 26, 48, 0.2);
}
.block-pill-btn.active .block-badge-count {
    background-color: var(--accent-saffron, #ff9933);
    color: #000;
}
.block-badge-count {
    font-size: 0.7rem;
    padding: 0.1rem 0.4rem;
    border-radius: 50px;
    background-color: #e9ecef;
    color: #495057;
    font-weight: 700;
}

.governance-card {
    border-radius: 16px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    transition: all 0.2s ease-in-out;
}
.governance-card:hover {
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    border-color: #cbd5e1;
}
</style>

<?php if ($village): ?>
    <!-- ========================================================================= -->
    <!-- VIEW 1: SINGLE VILLAGE DEMOGRAPHIC PROFILE                                -->
    <!-- ========================================================================= -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small text-white-50">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white text-decoration-none">Census 2011</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getVillageUrl($dSlug); ?>" class="text-white text-decoration-none"><?php echo htmlspecialchars($dName); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getVillageUrl($dSlug, $bSlug); ?>" class="text-white text-decoration-none"><?php echo htmlspecialchars($bName); ?></a></li>
                    <li class="breadcrumb-item active text-warning" aria-current="page"><?php echo htmlspecialchars($vName); ?></li>
                </ol>
            </nav>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    <i class="bi bi-upc"></i> Census Code: <?php echo htmlspecialchars($vCode); ?>
                </span>
                <?php if (!empty($gpName)): ?>
                    <a href="<?php echo getPanchayatUrl($dSlug, slugify($bName), slugify($gpName)); ?>" class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 text-decoration-none border border-success border-opacity-50">
                        🌾 Gram Panchayat: <?php echo htmlspecialchars($gpName); ?> &rarr;
                    </a>
                <?php endif; ?>
                <a href="<?php echo getBlockUrl($dSlug, $bSlug); ?>" class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 text-decoration-none">
                    🏛️ Block: <?php echo htmlspecialchars($bName); ?>
                </a>
                <a href="<?php echo getDistrictUrl($dSlug); ?>" class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 text-decoration-none">
                    📍 District: <?php echo htmlspecialchars($dName); ?>
                </a>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">
                <?php echo htmlspecialchars($vName); ?> Village Census 2011 Profile
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 880px;">
                Complete Primary Census Abstract (PCA) demographic, household, caste category (SC/ST), area, and infrastructure roster for <strong><?php echo htmlspecialchars($vName); ?></strong> village, <?php echo htmlspecialchars($bName); ?> Block, <?php echo htmlspecialchars($dName); ?> District, Bihar.
            </p>

            <!-- Action Buttons -->
            <div class="d-flex flex-wrap gap-2">
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Explore {$vName} Village (Census Code: {$vCode}) Demographics & Gram Panchayat Data on BiharElection.com: " . $pageCanonical); ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp"></i> Share on WhatsApp
                </a>
                <button type="button" class="btn btn-light fw-bold px-3 py-2 shadow-sm" onclick="navigator.clipboard.writeText(window.location.href); alert('Village Profile link copied to clipboard!');">
                    <i class="bi bi-link-45deg"></i> Copy Link
                </button>
                <a href="<?php echo getVillageUrl($dSlug); ?>" class="btn btn-outline-light fw-bold px-3 py-2">
                    <i class="bi bi-arrow-left"></i> All <?php echo htmlspecialchars($dName); ?> Villages
                </a>
                <a href="<?php echo getCensusUrl(); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark">
                    <i class="bi bi-file-earmark-bar-graph"></i> Bihar Census Hub
                </a>
            </div>
        </div>
    </section>

    <!-- Main Profile Container -->
    <main class="container my-4 my-lg-5">

        <!-- Top Ad Placement -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- 4 Key Stat Metric Cards -->
        <div class="row g-3 g-md-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                    <span class="small text-muted text-uppercase fw-bold d-block">Total Population</span>
                    <h2 class="h3 fw-bold text-dark mb-1 font-monospace"><?php echo number_format($pop); ?></h2>
                    <div class="small text-muted">
                        👨 M: <?php echo number_format($male); ?> | 👩 F: <?php echo number_format($female); ?>
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                    <span class="small text-muted text-uppercase fw-bold d-block">Sex Ratio</span>
                    <h2 class="h3 fw-bold text-success mb-1"><?php echo $sr; ?></h2>
                    <div class="small text-muted">
                        Females per 1000 Males
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                    <span class="small text-muted text-uppercase fw-bold d-block">Total Households</span>
                    <h2 class="h3 fw-bold text-warning mb-1 font-monospace"><?php echo number_format($hh); ?></h2>
                    <div class="small text-muted">
                        Avg. Family: <?php echo $avgFamilySize; ?> members
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-info">
                    <span class="small text-muted text-uppercase fw-bold d-block">Geographical Area</span>
                    <h2 class="h3 fw-bold text-info mb-1 font-monospace"><?php echo number_format($areaHectares, 1); ?> <small class="fs-6 text-muted">Ha</small></h2>
                    <div class="small text-muted">
                        ~<?php echo number_format($areaAcres, 1); ?> Acres (<?php echo $density; ?> ppl/Ha)
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Demographic & Administration Section -->
        <div class="row g-4 mb-4">
            
            <!-- Left Column: Social & Category Matrix -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h3 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-people-fill text-primary me-2"></i> Social Category &amp; Gender Demographics
                    </h3>

                    <div class="table-responsive mb-4">
                        <table class="table table-bordered align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Male</th>
                                    <th class="text-end">Female</th>
                                    <th class="text-center">Share %</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold">Total Population</td>
                                    <td class="text-end fw-bold font-monospace"><?php echo number_format($pop); ?></td>
                                    <td class="text-end text-primary font-monospace"><?php echo number_format($male); ?></td>
                                    <td class="text-end text-danger font-monospace"><?php echo number_format($female); ?></td>
                                    <td class="text-center fw-bold">100%</td>
                                </tr>
                                <tr>
                                    <td>Scheduled Caste (SC)</td>
                                    <td class="text-end font-monospace"><?php echo number_format($scPop); ?></td>
                                    <td class="text-end text-muted font-monospace"><?php echo number_format($village['sc_male'] ?? 0); ?></td>
                                    <td class="text-end text-muted font-monospace"><?php echo number_format($village['sc_female'] ?? 0); ?></td>
                                    <td class="text-center fw-bold text-danger"><?php echo $scPct; ?>%</td>
                                </tr>
                                <tr>
                                    <td>Scheduled Tribe (ST)</td>
                                    <td class="text-end font-monospace"><?php echo number_format($stPop); ?></td>
                                    <td class="text-end text-muted font-monospace"><?php echo number_format($village['st_male'] ?? 0); ?></td>
                                    <td class="text-end text-muted font-monospace"><?php echo number_format($village['st_female'] ?? 0); ?></td>
                                    <td class="text-center fw-bold text-warning"><?php echo $stPct; ?>%</td>
                                </tr>
                                <tr>
                                    <td>General / OBC / Other</td>
                                    <td class="text-end font-monospace"><?php echo number_format(max(0, $pop - ($scPop + $stPop))); ?></td>
                                    <td class="text-end text-muted font-monospace">-</td>
                                    <td class="text-end text-muted font-monospace">-</td>
                                    <td class="text-center fw-bold text-success"><?php echo $genObcPct; ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Category Breakdown Progress Bar -->
                    <h6 class="small fw-bold text-muted text-uppercase mb-2">Social Composition Distribution:</h6>
                    <div class="progress mb-2" style="height: 12px; border-radius: 8px;">
                        <div class="progress-bar bg-success" style="width: <?php echo $genObcPct; ?>%" title="General/OBC: <?php echo $genObcPct; ?>%"></div>
                        <div class="progress-bar bg-danger" style="width: <?php echo $scPct; ?>%" title="SC: <?php echo $scPct; ?>%"></div>
                        <div class="progress-bar bg-warning" style="width: <?php echo $stPct; ?>%" title="ST: <?php echo $stPct; ?>%"></div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted">
                        <span><span class="badge bg-success">&nbsp;</span> General/OBC: <?php echo $genObcPct; ?>%</span>
                        <span><span class="badge bg-danger">&nbsp;</span> SC: <?php echo $scPct; ?>%</span>
                        <span><span class="badge bg-warning">&nbsp;</span> ST: <?php echo $stPct; ?>%</span>
                    </div>
                </div>
            </div>

            <!-- Right Column: Location & Connectivity Matrix -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                    <h3 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i> Administrative &amp; Location Profile
                    </h3>

                    <ul class="list-group list-group-flush small mb-4">
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">State:</span>
                            <strong class="text-dark">Bihar (Code: 10)</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">District:</span>
                            <a href="<?php echo getDistrictUrl($dSlug); ?>" class="text-decoration-none fw-bold text-primary">
                                <?php echo htmlspecialchars($dName); ?> (Code: <?php echo htmlspecialchars($village['district_code']); ?>)
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">CD Block / Sub-District:</span>
                            <a href="<?php echo getBlockUrl($dSlug, $bSlug); ?>" class="text-decoration-none fw-bold text-primary">
                                <?php echo htmlspecialchars($bName); ?>
                            </a>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Gram Panchayat:</span>
                            <?php if (!empty($gpName)): ?>
                                <a href="<?php echo getPanchayatUrl($dSlug, slugify($bName), slugify($gpName)); ?>" class="badge bg-success bg-opacity-10 text-success text-decoration-none fw-bold">
                                    🌾 <?php echo htmlspecialchars($gpName); ?> &rarr;
                                </a>
                            <?php else: ?>
                                <span class="text-muted">Non-Panchayat Area</span>
                            <?php endif; ?>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Nearest Statutory / Census Town:</span>
                            <strong class="text-dark">
                                <?php echo !empty($village['nearest_town_name']) ? htmlspecialchars($village['nearest_town_name']) : 'N/A'; ?>
                                <?php if (!empty($village['nearest_town_distance'])): ?>
                                    <span class="badge bg-light text-dark border ms-1"><?php echo htmlspecialchars($village['nearest_town_distance']); ?> km</span>
                                <?php endif; ?>
                            </strong>
                        </li>
                        <?php if (!empty($village['sub_district_hq_distance'])): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">Sub-District HQ Distance:</span>
                            <strong class="text-dark"><?php echo htmlspecialchars($village['sub_district_hq_distance']); ?> km</strong>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($village['district_hq_distance'])): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                            <span class="text-muted">District HQ Distance:</span>
                            <strong class="text-dark"><?php echo htmlspecialchars($village['district_hq_distance']); ?> km</strong>
                        </li>
                        <?php endif; ?>
                    </ul>

                    <div class="p-3 bg-light rounded-3 text-center">
                        <small class="text-muted d-block mb-1">Census Reference Year:</small>
                        <span class="fw-bold text-dark fs-6">2011 District Census Handbook (DCHB)</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Tier Governance & Representation Grid -->
        <?php if ($panchayatObj || $blockSamiti): ?>
        <section class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
            <h3 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                <i class="bi bi-shield-check text-success me-2"></i> Local Governance &amp; Elected Representation
            </h3>

            <div class="row g-3">
                <?php if ($panchayatObj): ?>
                <div class="col-12 col-md-6">
                    <div class="governance-card p-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold">Gram Panchayat Leadership</span>
                            <span class="small text-muted"><?php echo htmlspecialchars($panchayatObj['panchayat_name']); ?></span>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded-2">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">Elected Mukhiya:</span>
                                    <strong class="text-dark"><?php echo htmlspecialchars($panchayatObj['current_mukhiya'] ?: 'Not Specified'); ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded-2">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">Elected Sarpanch:</span>
                                    <strong class="text-dark"><?php echo htmlspecialchars($panchayatObj['current_sarpanch'] ?: 'Not Specified'); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <a href="<?php echo getPanchayatUrl($dSlug, slugify($bName), slugify($gpName)); ?>" class="small text-primary text-decoration-none fw-semibold">
                                View Full Panchayat Roster &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($blockSamiti): ?>
                <div class="col-12 col-md-6">
                    <div class="governance-card p-3 h-100">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">Block Panchayat Samiti</span>
                            <span class="small text-muted"><?php echo htmlspecialchars($bName); ?></span>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <div class="p-2 bg-light rounded-2">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">Block Pramukh:</span>
                                    <strong class="text-dark"><?php echo htmlspecialchars($blockSamiti['pramukh_name'] ?: 'Not Specified'); ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 bg-light rounded-2">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">Up-Pramukh:</span>
                                    <strong class="text-dark"><?php echo htmlspecialchars($blockSamiti['up_pramukh_name'] ?: 'Not Specified'); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="mt-2 text-end">
                            <a href="<?php echo getPanchayatSamitiUrl($dSlug, slugify($bName)); ?>" class="small text-primary text-decoration-none fw-semibold">
                                View Block Samiti &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Related / Nearby Villages in same Gram Panchayat & Block -->
        <?php if (!empty($nearbyVillages)): ?>
        <section class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">
                        🏡 Nearby Villages in <?php echo !empty($gpName) ? 'Gram Panchayat ' . htmlspecialchars($gpName) : htmlspecialchars($bName) . ' Block'; ?>
                    </h3>
                    <p class="small text-muted mb-0">Demographic overview of other villages in the same local administrative territory</p>
                </div>
                <a href="<?php echo getVillageUrl($dSlug, $bSlug); ?>" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3">
                    View All <?php echo htmlspecialchars($bName); ?> Villages &rarr;
                </a>
            </div>

            <div class="row g-3">
                <?php foreach ($nearbyVillages as $nv): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border rounded-3 p-3 h-100 bg-light bg-opacity-50 hover-shadow transition">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h4 class="h6 fw-bold mb-0 text-dark">
                                <a href="<?php echo getVillageUrl($nv['district_slug'], $nv['sub_district_slug'], $nv['village_slug']); ?>" class="text-decoration-none text-primary">
                                    <?php echo htmlspecialchars($nv['village_name']); ?>
                                </a>
                            </h4>
                            <span class="badge bg-secondary text-white font-monospace" style="font-size: 0.68rem;"><?php echo htmlspecialchars($nv['village_code']); ?></span>
                        </div>
                        <div class="small text-muted mb-2">
                            <div>👥 Population: <strong><?php echo number_format($nv['population'] ?? 0); ?></strong></div>
                            <div>🏠 Households: <?php echo number_format($nv['households'] ?? 0); ?></div>
                        </div>
                        <a href="<?php echo getVillageUrl($nv['district_slug'], $nv['sub_district_slug'], $nv['village_slug']); ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100 py-1 fw-semibold mt-auto" style="font-size: 0.78rem;">
                            View Profile &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
        <?php endif; ?>

        <!-- Bottom Ad Slot -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>

    </main>

    <!-- Schema.org Place & Breadcrumb Markup -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Place",
      "name": "<?php echo addslashes($vName); ?> Village",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "<?php echo addslashes($bName); ?>",
        "addressRegion": "<?php echo addslashes($dName); ?>",
        "addressCountry": "IN"
      },
      "description": "<?php echo addslashes($pageDescription); ?>"
    }
    </script>

<?php else: ?>
    <!-- ========================================================================= -->
    <!-- VIEW 2: 44,874 CENSUS VILLAGES DIRECTORY & SEARCH ENGINE                   -->
    <!-- ========================================================================= -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    <i class="bi bi-file-earmark-bar-graph"></i> Official Census 2011 DCHB
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    44,874 Villages
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2">
                    9.23 Crore Rural Population
                </span>
                <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2">
                    38 Districts &amp; 534 Blocks
                </span>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">
                Bihar Census 2011 Village Directory
                <?php if (!empty($districtParam)): ?>
                    - <?php echo htmlspecialchars(ucfirst($districtParam)); ?> District
                <?php endif; ?>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 880px;">
                Search, filter, and explore official population, sex ratio, households, SC/ST social categories, and connectivity for all <strong>44,874 Census Villages</strong> in Bihar.
            </p>

            <!-- State Summary KPI Row -->
            <div class="row g-2 g-md-3">
                <div class="col-6 col-md-3">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Total Villages</small>
                        <span class="fs-5 fw-bold text-warning">44,874</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Rural Population</small>
                        <span class="fs-5 fw-bold text-info">92,341,436</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Rural Households</small>
                        <span class="fs-5 fw-bold text-success">16,936,300</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Rural Sex Ratio</small>
                        <span class="fs-5 fw-bold text-white">921</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container my-4 my-lg-5">

        <!-- Top Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Quick District Filter Pills -->
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-muted text-uppercase fw-bold"><i class="bi bi-geo-alt text-primary me-1"></i> Quick Filter by District:</span>
                <?php if (!empty($districtParam)): ?>
                    <a href="<?php echo getVillageUrl(); ?>" class="small text-danger text-decoration-none fw-bold"><i class="bi bi-x-circle"></i> Clear District</a>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap gap-1" style="max-height: 120px; overflow-y: auto;">
                <?php foreach ($districtsList as $d): ?>
                    <a href="<?php echo getVillageUrl($d['slug']); ?>" class="btn btn-sm rounded-pill px-2 py-1 <?php echo $districtParam === $d['slug'] ? 'btn-primary fw-bold' : 'btn-outline-secondary'; ?>" style="font-size: 0.78rem;">
                        <?php echo htmlspecialchars($d['name']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- If District is Selected: Show Block Quick Filter Pills -->
        <?php if (!empty($districtBlocks)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="small text-muted text-uppercase fw-bold">
                    <i class="bi bi-diagram-3 text-success me-1"></i> CD Blocks in <?php echo htmlspecialchars(ucfirst($districtParam)); ?> District (<?php echo count($districtBlocks); ?> Blocks):
                </span>
                <?php if (!empty($blockParam)): ?>
                    <a href="<?php echo getVillageUrl($districtParam); ?>" class="small text-danger text-decoration-none fw-bold"><i class="bi bi-x-circle"></i> All Blocks</a>
                <?php endif; ?>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo getVillageUrl($districtParam); ?>" class="block-pill-btn <?php echo empty($blockParam) ? 'active' : ''; ?>">
                    All Blocks
                </a>
                <?php foreach ($districtBlocks as $db): ?>
                    <?php 
                    $isBlkActive = (strtolower($blockParam) === strtolower($db['sub_district_slug']) || strtolower($blockParam) === strtolower($db['sub_district_name']));
                    ?>
                    <a href="<?php echo getVillageUrl($districtParam, $db['sub_district_slug']); ?>" class="block-pill-btn <?php echo $isBlkActive ? 'active' : ''; ?>">
                        <?php echo htmlspecialchars($db['sub_district_name']); ?>
                        <span class="block-badge-count"><?php echo number_format($db['village_count']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Search & Filter Form Card -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
            <h2 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                <i class="bi bi-funnel-fill text-primary me-2"></i> Search &amp; Filter Bihar Villages
            </h2>

            <form method="GET" action="<?php echo getVillageUrl($districtParam, $blockParam); ?>" class="row g-2">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">1. District:</label>
                    <select name="district" class="form-select form-select-sm" onchange="if(this.form.block) this.form.block.value=''; this.form.submit()">
                        <option value="">Select District</option>
                        <?php foreach ($districtsList as $d): ?>
                            <option value="<?php echo htmlspecialchars($d['slug']); ?>" <?php echo $districtParam === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">2. CD Block / Sub-District:</label>
                    <?php if (!empty($districtBlocks)): ?>
                        <select name="block" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Blocks in <?php echo htmlspecialchars(ucfirst($districtParam)); ?> (<?php echo count($districtBlocks); ?>)</option>
                            <?php foreach ($districtBlocks as $db): ?>
                                <?php 
                                $isBSelected = (strtolower($blockParam) === strtolower($db['sub_district_slug']) || strtolower($blockParam) === strtolower($db['sub_district_name']));
                                ?>
                                <option value="<?php echo htmlspecialchars($db['sub_district_slug']); ?>" <?php echo $isBSelected ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($db['sub_district_name']); ?> (<?php echo number_format($db['village_count']); ?> vil.)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="block" class="form-select form-select-sm" disabled>
                            <option value="">Select District First</option>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-muted mb-1">Search Village Name / Code:</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control" placeholder="Village name or code..." value="<?php echo htmlspecialchars($searchParam); ?>">
                    </div>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold text-muted mb-1">Sort By:</label>
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="pop_desc" <?php echo $sortParam === 'pop_desc' ? 'selected' : ''; ?>>Pop: High to Low</option>
                        <option value="pop_asc" <?php echo $sortParam === 'pop_asc' ? 'selected' : ''; ?>>Pop: Low to High</option>
                        <option value="area_desc" <?php echo $sortParam === 'area_desc' ? 'selected' : ''; ?>>Area: Largest First</option>
                        <option value="sex_desc" <?php echo $sortParam === 'sex_desc' ? 'selected' : ''; ?>>Sex Ratio: High to Low</option>
                    </select>
                </div>

                <div class="col-12 col-md-1 d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm fw-bold w-100">
                        <i class="bi bi-search"></i>
                    </button>
                    <?php if (!empty($districtParam) || !empty($blockParam) || !empty($searchParam) || !empty($gpParam)): ?>
                        <a href="<?php echo getVillageUrl(); ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filters">
                            <i class="bi bi-arrow-counterclockwise"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <!-- Result Counter -->
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <span class="small text-muted">
                Found <strong><?php echo number_format($totalCount); ?></strong> villages
                <?php if (!empty($districtParam)): ?> in <strong><?php echo htmlspecialchars(ucfirst($districtParam)); ?></strong><?php endif; ?>
                <?php if (!empty($blockParam)): ?> (Block: <em><?php echo htmlspecialchars($blockParam); ?></em>)<?php endif; ?>
                <?php if (!empty($searchParam)): ?> matching "<em><?php echo htmlspecialchars($searchParam); ?></em>"<?php endif; ?>
            </span>
            <span class="small text-muted">
                Page <strong><?php echo $currentPage; ?></strong> of <strong><?php echo max(1, ceil($totalCount / $perPage)); ?></strong>
            </span>
        </div>

        <!-- Villages Table Card -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small" id="allVillagesTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">#</th>
                            <th class="py-3">Village Name &amp; Code</th>
                            <th class="py-3">Gram Panchayat</th>
                            <th class="py-3">CD Block &amp; District</th>
                            <th class="py-3 text-end">Households</th>
                            <th class="py-3 text-end">Total Population</th>
                            <th class="py-3 text-end">Male / Female</th>
                            <th class="py-3 text-center">Sex Ratio</th>
                            <th class="py-3 text-end">SC / ST</th>
                            <th class="py-3 text-end">Area (Ha)</th>
                            <th class="py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($villageList)): ?>
                            <?php 
                            $vIdx = ($currentPage - 1) * $perPage + 1;
                            foreach ($villageList as $v): 
                                $vLink = getVillageUrl($v['district_slug'], $v['sub_district_slug'], $v['village_slug']);
                            ?>
                            <tr>
                                <td class="text-muted fw-bold"><?php echo $vIdx++; ?></td>
                                <td>
                                    <a href="<?php echo $vLink; ?>" class="fw-bold text-decoration-none text-primary fs-6">
                                        <?php echo htmlspecialchars($v['village_name']); ?>
                                    </a>
                                    <div class="small text-muted font-monospace"><i class="bi bi-upc"></i> Code: <?php echo htmlspecialchars($v['village_code']); ?></div>
                                </td>
                                <td>
                                    <?php if (!empty($v['gram_panchayat_name'])): ?>
                                        <a href="<?php echo getPanchayatUrl($v['district_slug'], slugify($v['sub_district_name'] ?: $v['cd_block_name']), slugify($v['gram_panchayat_name'])); ?>" class="badge bg-light text-dark border text-decoration-none">
                                            🌾 <?php echo htmlspecialchars($v['gram_panchayat_name']); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?php echo htmlspecialchars($v['sub_district_name'] ?: $v['cd_block_name']); ?></div>
                                    <a href="<?php echo getVillageUrl($v['district_slug']); ?>" class="text-decoration-none small text-primary">
                                        <?php echo htmlspecialchars($v['district_name']); ?>
                                    </a>
                                </td>
                                <td class="text-end font-monospace"><?php echo number_format($v['households'] ?? 0); ?></td>
                                <td class="text-end fw-bold text-dark fs-6 font-monospace"><?php echo number_format($v['population'] ?? 0); ?></td>
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
                                <td class="text-center">
                                    <a href="<?php echo $vLink; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 fw-semibold" style="font-size: 0.78rem;">
                                        Profile &rarr;
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    No villages found matching your criteria. Try adjusting your search query or filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Controls -->
            <?php 
            $totalPages = max(1, ceil($totalCount / $perPage));
            if ($totalPages > 1): 
                $pageBaseUrl = getVillageUrl($districtParam, $blockParam);
                $queryParams = $_GET;
                unset($queryParams['page'], $queryParams['district'], $queryParams['block'], $queryParams['slug'], $queryParams['vslug'], $queryParams['subdistrict']);
                $qs = http_build_query($queryParams);
                $baseUrl = $pageBaseUrl . ($qs ? '?' . $qs : '');
                $pageDelim = (strpos($baseUrl, '?') !== false) ? '&page=' : '?page=';
            ?>
            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                <div class="small text-muted">
                    Showing <?php echo number_format(($currentPage - 1) * $perPage + 1); ?> - <?php echo number_format(min($totalCount, $currentPage * $perPage)); ?> of <?php echo number_format($totalCount); ?> villages
                </div>
                <ul class="pagination pagination-sm mb-0">
                    <?php if ($currentPage > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $baseUrl . $pageDelim . ($currentPage - 1); ?>" aria-label="Previous">
                                &laquo; Prev
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php
                    $startP = max(1, $currentPage - 2);
                    $endP = min($totalPages, $currentPage + 2);
                    if ($startP > 1) {
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . $pageDelim . '1">1</a></li>';
                        if ($startP > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                    }
                    for ($p = $startP; $p <= $endP; $p++):
                    ?>
                        <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="<?php echo $baseUrl . $pageDelim . $p; ?>"><?php echo $p; ?></a>
                        </li>
                    <?php endfor; 
                    if ($endP < $totalPages) {
                        if ($endP < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . $pageDelim . $totalPages . '">' . $totalPages . '</a></li>';
                    }
                    ?>

                    <?php if ($currentPage < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link" href="<?php echo $baseUrl . $pageDelim . ($currentPage + 1); ?>" aria-label="Next">
                                Next &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
        </section>

        <!-- Bottom Ad Slot -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>

    </main>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
