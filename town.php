<?php
/**
 * BiharElection.com - Bihar Census 2011 Town Intelligence & Slum Demographic Directory
 * Complete coverage of all 198 Statutory & Census Towns and 670 Slum Settlements across 38 Districts
 */
require_once __DIR__ . '/config.php';

$pdo = Database::getConnection();

// Input parameters resolution
$codeParam = trim($_GET['code'] ?? '');
$districtParam = trim($_GET['district'] ?? '');
$townParam = trim($_GET['town'] ?? '');
$civicParam = trim($_GET['civic'] ?? '');
$slumOnlyParam = !empty($_GET['slum_only']) && $_GET['slum_only'] !== '0';
$searchParam = trim($_GET['q'] ?? '');
$slugParam = trim($_GET['slug'] ?? '');
$sortParam = trim($_GET['sort'] ?? 'pop_desc');
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;

$districtsList = DataProvider::getDistricts();

// Handle generic slug routing
if (empty($codeParam) && empty($townParam) && !empty($slugParam)) {
    if (is_numeric($slugParam) && strlen($slugParam) >= 5) {
        $codeParam = $slugParam;
    } else {
        $matchedDist = DataProvider::getDistrictBySlug($slugParam);
        if ($matchedDist) {
            $districtParam = $slugParam;
        } else {
            // Check if slug matches a town directly
            $townParam = $slugParam;
        }
    }
}

$town = null;

// 1. Try to find a specific town
if (!empty($codeParam)) {
    $town = DataProvider::getTownByCode($codeParam);
} elseif (!empty($townParam)) {
    if (is_numeric($townParam) && strlen($townParam) >= 5) {
        $town = DataProvider::getTownByCode($townParam);
    } else {
        $town = DataProvider::getTownBySlug($districtParam, $townParam);
    }
}

// 2. If single town is matched, load related slum & demographic data
if ($town) {
    $tName = $town['town_name'];
    $tCode = $town['town_code'];
    $dName = $town['district_name'];
    $dSlug = $town['district_slug'];
    $bName = $town['cd_block_name'] ?: ($town['sub_district_name'] ?: 'Block');
    $bSlug = $town['sub_district_slug'];
    $civicStatus = $town['civic_status'] ?: 'Town';
    $townClass = $town['town_class'] ?: 'N/A';
    
    $pop = (int)($town['population'] ?? 0);
    $hh = (int)($town['households'] ?? 0);
    $sr = (int)($town['sex_ratio'] ?? 0);
    $male = (int)($town['male'] ?? 0);
    $female = (int)($town['female'] ?? 0);
    $scPop = (int)($town['sc_population'] ?? 0);
    $stPop = (int)($town['st_population'] ?? 0);
    $scPct = ($pop > 0) ? round(($scPop / $pop) * 100, 2) : 0;
    $stPct = ($pop > 0) ? round(($stPop / $pop) * 100, 2) : 0;
    $genObcPct = max(0, round(100 - ($scPct + $stPct), 2));

    $areaSqKm = (float)($town['area_sq_km'] ?? 0);
    $density = ($areaSqKm > 0 && $pop > 0) ? round($pop / $areaSqKm, 1) : 0;
    $avgFamilySize = ($hh > 0 && $pop > 0) ? round($pop / $hh, 1) : 0;

    // Fetch Slums for this town
    $slums = DataProvider::getTownSlums($tCode);
    $totalSlums = count($slums);
    $totalSlumPop = 0;
    $totalSlumHh = 0;
    foreach ($slums as $s) {
        $totalSlumPop += (int)($s['slum_population'] ?? 0);
        $totalSlumHh += (int)($s['households'] ?? 0);
    }
    $slumPopPct = ($pop > 0 && $totalSlumPop > 0) ? round(($totalSlumPop / $pop) * 100, 1) : 0;

    // Banking & Industry
    $natBanks = (int)($town['nationalised_banks'] ?? 0);
    $comBanks = (int)($town['commercial_banks'] ?? 0);
    $coopBanks = (int)($town['cooperative_banks'] ?? 0);
    $totalBanks = $natBanks + $comBanks + $coopBanks;

    $manuf1 = trim($town['manufactured_1'] ?? '');
    $manuf2 = trim($town['manufactured_2'] ?? '');
    $manuf3 = trim($town['manufactured_3'] ?? '');
    $manufacturedItems = array_filter([$manuf1, $manuf2, $manuf3]);

    $pageTitle = "{$tName} Town Population, Civic Status, Slums & Census 2011 Data ({$dName})";
    $pageDescription = "Official 2011 Census urban data for {$tName} ({$civicStatus}, Code: {$tCode}), {$dName} District, Bihar. Population: " . number_format($pop) . ", Households: " . number_format($hh) . ", Sex Ratio: {$sr}, Slums: {$totalSlums}.";
    $pageKeywords = "{$tName} town, {$tName} census 2011, {$tName} population, {$tName} slums, {$civicStatus} {$tName}, {$dName} district urban towns, Bihar Census 2011 towns";
    $pageCanonical = getTownUrl($dSlug, $town['town_slug']);

    // Fetch sibling towns in same district
    $nearbyTowns = DataProvider::getNearbyTowns($dSlug, $town['id'], 6);

} else {
    // Directory Mode
    $distLabel = !empty($districtParam) ? ucfirst($districtParam) . ' District ' : 'Bihar ';
    $civicLabel = !empty($civicParam) ? " ({$civicParam})" : '';
    $pageTitle = "{$distLabel}Census 2011 Towns & Slums Directory: 198 Urban Centers Population & Demographics";
    $pageDescription = "Explore the complete Census 2011 town and slum directory of Bihar covering all 198 statutory towns, municipal corporations, nagar parishads, census towns and 670 slum areas across 38 districts.";
    $pageKeywords = "Bihar towns directory, Bihar 198 towns, Bihar census towns list, Bihar municipal corporations, Bihar slum population 2011, Bihar urban census 2011";
    $pageCanonical = !empty($districtParam) ? getTownUrl($districtParam) : SITE_URL . "/town";

    // Build directory query with pagination & sorting
    $where = [];
    $params = [];

    if (!empty($districtParam)) {
        $where[] = "district_slug = :dslug";
        $params[':dslug'] = strtolower(trim($districtParam));
    }
    if (!empty($civicParam)) {
        $where[] = "civic_status = :civic";
        $params[':civic'] = trim($civicParam);
    }
    if ($slumOnlyParam) {
        $where[] = "slum_count > 0";
    }
    if (!empty($searchParam)) {
        $where[] = "(town_name LIKE :q OR town_code LIKE :q OR district_name LIKE :q OR cd_block_name LIKE :q OR civic_status LIKE :q)";
        $params[':q'] = '%' . trim($searchParam) . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $orderBy = "population DESC";
    if ($sortParam === 'name_asc') {
        $orderBy = "town_name ASC";
    } elseif ($sortParam === 'name_desc') {
        $orderBy = "town_name DESC";
    } elseif ($sortParam === 'pop_asc') {
        $orderBy = "population ASC";
    } elseif ($sortParam === 'slum_desc') {
        $orderBy = "slum_population DESC, slum_count DESC";
    } elseif ($sortParam === 'area_desc') {
        $orderBy = "area_sq_km DESC";
    }

    $totalCount = 0;
    $townsList = [];
    $totalUrbanPop = 0;
    $totalTownCount = 0;
    $totalSlumTowns = 0;
    $totalSlumPopAll = 0;

    if ($pdo) {
        try {
            // Aggregate summary for Bihar Urban
            $agg = $pdo->query("SELECT COUNT(*) as total_towns, SUM(population) as total_pop, SUM(households) as total_hh, SUM(slum_count) as total_slums, SUM(slum_population) as total_slum_pop FROM census_towns_2011")->fetch(PDO::FETCH_ASSOC);
            $totalUrbanPop = (int)($agg['total_pop'] ?? 0);
            $totalTownCount = (int)($agg['total_towns'] ?? 0);
            $totalSlumPopAll = (int)($agg['total_slum_pop'] ?? 0);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM census_towns_2011 $whereSql");
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            $offset = max(0, ($currentPage - 1) * $perPage);
            $stmt = $pdo->prepare("SELECT * FROM census_towns_2011 $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset");
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $townsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            error_log("Towns query error: " . $e->getMessage());
        }
    }

    // Fetch distinct civic statuses for dropdown
    $civicStatuses = DataProvider::getCensusTownCivicStatuses();
}

require_once __DIR__ . '/header.php';
?>

<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/village.css">
<style>
.town-hero-gradient {
    background: linear-gradient(135deg, #0d233a 0%, #173b6c 50%, #1e5799 100%);
    position: relative;
    overflow: hidden;
}
.town-hero-gradient::before {
    content: "";
    position: absolute;
    top: -50%;
    left: -20%;
    width: 140%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 60%);
    pointer-events: none;
}
.civic-badge-mcorp { background-color: #7928ca; color: #fff; }
.civic-badge-nparishad { background-color: #0070f3; color: #fff; }
.civic-badge-np { background-color: #00a86b; color: #fff; }
.civic-badge-ct { background-color: #f5a623; color: #000; }
.civic-badge-cb { background-color: #e00; color: #fff; }
.civic-badge-og { background-color: #6c757d; color: #fff; }

.slum-card {
    border-left: 4px solid #f59e0b;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.slum-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.table-slum th {
    background-color: #f8fafc;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
</style>

<?php if ($town): ?>
    <!-- ========================================================================= -->
    <!-- 1. SINGLE TOWN PROFILE VIEW                                               -->
    <!-- ========================================================================= -->

    <!-- Breadcrumb Header -->
    <section class="town-hero-gradient text-white py-4 py-md-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white-50 text-decoration-none">Census 2011</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl(); ?>" class="text-white-50 text-decoration-none">Towns Directory</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl($dSlug); ?>" class="text-white text-decoration-none"><?php echo htmlspecialchars($dName); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($tName); ?></li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-buildings me-1"></i> Town Code: <?php echo htmlspecialchars($tCode); ?>
                        </span>
                        <span class="badge bg-info text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            Class <?php echo htmlspecialchars($townClass); ?>
                        </span>
                        <span class="badge bg-light text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <?php echo htmlspecialchars($civicStatus); ?>
                        </span>
                        <?php if ($totalSlums > 0): ?>
                            <span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm animate-pulse">
                                🛖 <?php echo $totalSlums; ?> Slum Settlements
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-6 fw-bold mb-2 font-heading text-white">
                        🏙️ <?php echo htmlspecialchars($tName); ?>
                    </h1>
                    
                    <p class="lead mb-0 text-white-75 fs-6">
                        Statutory Urban Center located in <strong><?php echo htmlspecialchars($bName); ?></strong> Sub-District / Block, 
                        <strong><?php echo htmlspecialchars($dName); ?></strong> District, Bihar (Census 2011).
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Check {$tName} Town Census Demographics & Slum Data on BiharElection: " . $pageCanonical); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-whatsapp me-1"></i> Share
                        </a>
                        <a href="<?php echo getDistrictUrl($dSlug); ?>" class="btn btn-outline-light fw-bold rounded-pill px-3 py-2">
                            <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($dName); ?> District
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <main class="container py-4">

        <!-- Top Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Quick Demographic KPI Tiles -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-primary">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Population</span>
                    <h3 class="fw-bold text-navy mb-0 fs-4"><?php echo number_format($pop); ?></h3>
                    <span class="text-xs text-muted">Persons (2011)</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-info">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Households</span>
                    <h3 class="fw-bold text-info mb-0 fs-4"><?php echo number_format($hh); ?></h3>
                    <span class="text-xs text-muted">~<?php echo $avgFamilySize; ?> / Family</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-danger">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Sex Ratio</span>
                    <h3 class="fw-bold text-danger mb-0 fs-4"><?php echo $sr; ?></h3>
                    <span class="text-xs text-muted">Females / 1k Males</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-success">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Urban Area</span>
                    <h3 class="fw-bold text-success mb-0 fs-4"><?php echo number_format($areaSqKm, 2); ?></h3>
                    <span class="text-xs text-muted">Sq. Kilometers</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-warning">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Pop. Density</span>
                    <h3 class="fw-bold text-dark mb-0 fs-4"><?php echo $density > 0 ? number_format($density) : 'N/A'; ?></h3>
                    <span class="text-xs text-muted">Persons / Sq Km</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-purple" style="border-top-color: #7928ca !important;">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slum Pct</span>
                    <h3 class="fw-bold mb-0 fs-4" style="color: #7928ca;"><?php echo $slumPopPct; ?>%</h3>
                    <span class="text-xs text-muted"><?php echo number_format($totalSlumPop); ?> in Slums</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Detailed Demographics -->
            <div class="col-lg-7">
                <!-- Population & Gender Breakdown -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-pie-chart-fill text-primary me-2"></i>Population &amp; Gender Demographics</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold small">Census 2011</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-primary-subtle text-primary border border-primary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">👨 Male Population</span>
                                    <span class="badge bg-primary text-white"><?php echo $pop > 0 ? round(($male/$pop)*100, 1) : 0; ?>%</span>
                                </div>
                                <div class="fs-4 fw-bold"><?php echo number_format($male); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-danger-subtle text-danger border border-danger-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">👩 Female Population</span>
                                    <span class="badge bg-danger text-white"><?php echo $pop > 0 ? round(($female/$pop)*100, 1) : 0; ?>%</span>
                                </div>
                                <div class="fs-4 fw-bold"><?php echo number_format($female); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Gender Ratio Visual Progress Bar -->
                    <div class="mb-4">
                        <div class="d-flex justify-content-between text-xs text-muted mb-1">
                            <span>Male (<?php echo $pop > 0 ? round(($male/$pop)*100, 1) : 0; ?>%)</span>
                            <span>Female (<?php echo $pop > 0 ? round(($female/$pop)*100, 1) : 0; ?>%)</span>
                        </div>
                        <div class="progress" style="height: 12px; border-radius: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $pop > 0 ? ($male/$pop)*100 : 50; ?>%"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $pop > 0 ? ($female/$pop)*100 : 50; ?>%"></div>
                        </div>
                    </div>

                    <!-- Social Category Distribution -->
                    <h5 class="fw-bold text-navy fs-6 mb-3 border-top pt-3">
                        <i class="bi bi-people-fill text-warning me-2"></i>Social Category Breakdown
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Male</th>
                                    <th class="text-end">Female</th>
                                    <th class="text-end">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-navy">Scheduled Caste (SC)</td>
                                    <td class="text-end fw-bold"><?php echo number_format($scPop); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['sc_male'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['sc_female'] ?? 0)); ?></td>
                                    <td class="text-end text-primary fw-semibold"><?php echo $scPct; ?>%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">Scheduled Tribe (ST)</td>
                                    <td class="text-end fw-bold"><?php echo number_format($stPop); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['st_male'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['st_female'] ?? 0)); ?></td>
                                    <td class="text-end text-success fw-semibold"><?php echo $stPct; ?>%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">General / OBC / Others</td>
                                    <td class="text-end fw-bold"><?php echo number_format(max(0, $pop - ($scPop + $stPop))); ?></td>
                                    <td class="text-end"><?php echo number_format(max(0, $male - ((int)($town['sc_male'] ?? 0) + (int)($town['st_male'] ?? 0)))); ?></td>
                                    <td class="text-end"><?php echo number_format(max(0, $female - ((int)($town['sc_female'] ?? 0) + (int)($town['st_female'] ?? 0)))); ?></td>
                                    <td class="text-end text-dark fw-semibold"><?php echo $genObcPct; ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Economic, Banking & Manufacturing Infrastructure -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-cash-coin text-success me-2"></i>Economy &amp; Financial Infrastructure</span>
                        <span class="badge bg-success-subtle text-success fw-semibold small">Town Facilities</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Nationalised Banks</span>
                                <h4 class="fw-bold text-primary mb-0"><?php echo $natBanks; ?></h4>
                                <span class="text-xs text-muted">Branches</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Commercial Banks</span>
                                <h4 class="fw-bold text-info mb-0"><?php echo $comBanks; ?></h4>
                                <span class="text-xs text-muted">Branches</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Cooperative Banks</span>
                                <h4 class="fw-bold text-warning mb-0"><?php echo $coopBanks; ?></h4>
                                <span class="text-xs text-muted">Branches</span>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($manufacturedItems)): ?>
                        <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle">
                            <div class="fw-bold text-dark mb-2">
                                <i class="bi bi-gear-wide-connected me-1 text-warning"></i> Major Manufactured Commodities in <?php echo htmlspecialchars($tName); ?>:
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($manufacturedItems as $idx => $mItem): ?>
                                    <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs fs-7">
                                        🏭 <?php echo htmlspecialchars($mItem); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Right Column: Administrative & Slum Summary -->
            <div class="col-lg-5">
                <!-- Administrative Jurisdiction Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>Administrative Hierarchy
                    </h4>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">State:</span>
                            <span class="fw-bold text-navy">Bihar (Code: 10)</span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">District:</span>
                            <a href="<?php echo getDistrictUrl($dSlug); ?>" class="fw-bold text-primary text-decoration-none">
                                <?php echo htmlspecialchars($dName); ?> &rarr;
                            </a>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">CD Block / Sub-District:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($bName); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Civic Administrative Status:</span>
                            <span class="badge bg-primary text-white"><?php echo htmlspecialchars($civicStatus); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Town Size Classification:</span>
                            <span class="badge bg-secondary text-white">Class <?php echo htmlspecialchars($townClass); ?></span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex gap-2">
                        <a href="<?php echo getCensusUrl($dSlug); ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-semibold">
                            <?php echo htmlspecialchars($dName); ?> Census Hub
                        </a>
                        <a href="<?php echo getVillageUrl($dSlug); ?>" class="btn btn-sm btn-outline-success rounded-pill w-100 fw-semibold">
                            District Villages
                        </a>
                    </div>
                </div>

                <!-- Slum Summary Box -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-top border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                                Slum Directory
                            </span>
                            <h4 class="fw-bold text-navy font-heading fs-5 mb-0">
                                🛖 Slum Demographics
                            </h4>
                        </div>
                        <span class="fs-4">📊</span>
                    </div>

                    <?php if ($totalSlums > 0): ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-2 text-center">
                                <div class="col-4 border-end">
                                    <span class="text-xs text-muted d-block">Slums</span>
                                    <div class="fw-bold text-navy fs-5"><?php echo $totalSlums; ?></div>
                                </div>
                                <div class="col-4 border-end">
                                    <span class="text-xs text-muted d-block">Population</span>
                                    <div class="fw-bold text-danger fs-5"><?php echo number_format($totalSlumPop); ?></div>
                                </div>
                                <div class="col-4">
                                    <span class="text-xs text-muted d-block">Households</span>
                                    <div class="fw-bold text-info fs-5"><?php echo number_format($totalSlumHh); ?></div>
                                </div>
                            </div>
                        </div>
                        <p class="small text-muted mb-0">
                            <strong><?php echo $slumPopPct; ?>%</strong> of the total population in <?php echo htmlspecialchars($tName); ?> resides in designated slum settlements (Release 1000).
                        </p>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-3 text-center py-4 border text-muted">
                            <i class="bi bi-shield-check text-success fs-1 d-block mb-1"></i>
                            <div class="fw-bold text-dark">No Slum Clusters Reported</div>
                            <span class="text-xs">No notified or non-notified slum clusters in official Census 2011 Slum_1000 release for this town.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- DETAILED SLUMS ANALYTICS & INFRASTRUCTURE SECTION                         -->
        <!-- ========================================================================= -->
        <?php if (!empty($slums)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            DH 2011 DCHB Town Release 1000
                        </span>
                        <h3 class="fw-bold text-navy font-heading mb-0 fs-5">
                            🛖 Slum Settlements &amp; Infrastructure in <?php echo htmlspecialchars($tName); ?> (<?php echo count($slums); ?>)
                        </h3>
                        <p class="text-muted small mb-0 mt-1">Detailed sanitation, drainage, water supply, electricity, and road connectivity across all slums.</p>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-slum align-middle mb-0">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Slum Name</th>
                                <th>Status</th>
                                <th class="text-end">Households</th>
                                <th class="text-end">Population</th>
                                <th>Paved Roads</th>
                                <th>Drainage</th>
                                <th>Sanitation / Latrines</th>
                                <th>Tap Water</th>
                                <th>Domestic Elec.</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slums as $idx => $s): 
                                $sName = $s['slum_name'];
                                $isNotified = ((int)$s['is_notified'] === 1);
                                $sHh = (int)($s['households'] ?? 0);
                                $sPop = (int)($s['slum_population'] ?? 0);
                                $pavedKm = (float)($s['paved_roads_km'] ?? 0);
                                $drain = $s['drainage_system'] ?: 'N/A';
                                $tapWater = (int)($s['tap_points_water'] ?? 0);
                                $elecDom = (int)($s['electricity_domestic'] ?? 0);

                                $totalLatrines = (int)$s['latrines_pit'] + (int)$s['latrines_flush'] + (int)$s['latrines_service'] + (int)$s['latrines_others'] + (int)$s['latrines_community'];
                            ?>
                                <tr>
                                    <td class="text-muted small"><?php echo $idx + 1; ?></td>
                                    <td>
                                        <div class="fw-bold text-navy"><?php echo htmlspecialchars($sName); ?></div>
                                    </td>
                                    <td>
                                        <?php if ($isNotified): ?>
                                            <span class="badge bg-success-subtle text-success small">Notified</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary small">Non-Notified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-semibold"><?php echo number_format($sHh); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($sPop); ?></td>
                                    <td>
                                        <?php if ($pavedKm > 0): ?>
                                            <span class="badge bg-light text-dark border"><?php echo $pavedKm; ?> km</span>
                                        <?php else: ?>
                                            <span class="text-muted small">0 km</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border small"><?php echo htmlspecialchars($drain); ?></span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="fw-semibold text-dark"><?php echo $totalLatrines; ?> Total</span>
                                            <?php if ((int)$s['latrines_flush'] > 0): ?>
                                                <span class="text-xs text-muted d-block">Flush: <?php echo (int)$s['latrines_flush']; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tapWater > 0): ?>
                                            <span class="badge bg-info-subtle text-info small">🚰 <?php echo $tapWater; ?> Taps</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($elecDom > 0): ?>
                                            <span class="badge bg-warning-subtle text-dark small">⚡ <?php echo $elecDom; ?> Conn.</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sibling Towns in Same District Navigation -->
        <?php if (!empty($nearbyTowns)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            District Urban Centers
                        </span>
                        <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                            Other Towns in <?php echo htmlspecialchars($dName); ?> District (<?php echo count($nearbyTowns); ?>)
                        </h4>
                    </div>
                    <a href="<?php echo getTownUrl($dSlug); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        All <?php echo htmlspecialchars($dName); ?> Towns &rarr;
                    </a>
                </div>

                <div class="row g-3">
                    <?php foreach ($nearbyTowns as $nt): 
                        $ntUrl = getTownUrl($nt['district_slug'], $nt['town_slug']);
                        $ntPop = (int)($nt['population'] ?? 0);
                        $ntCivic = $nt['civic_status'] ?? 'Town';
                        $ntSlums = (int)($nt['slum_count'] ?? 0);
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between hover-shadow transition">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-primary-subtle text-primary small font-monospace">Code: <?php echo htmlspecialchars($nt['town_code']); ?></span>
                                        <span class="badge bg-secondary-subtle text-secondary small"><?php echo htmlspecialchars($ntCivic); ?></span>
                                    </div>
                                    <h5 class="fw-bold text-navy mb-1 fs-6">
                                        <a href="<?php echo htmlspecialchars($ntUrl); ?>" class="text-decoration-none text-navy stretched-link-target">
                                            🏙️ <?php echo htmlspecialchars($nt['town_name']); ?>
                                        </a>
                                    </h5>
                                    <div class="small text-muted mb-2">
                                        <span>👥 <?php echo number_format($ntPop); ?> Pop.</span> • 
                                        <span>Class <?php echo htmlspecialchars($nt['town_class'] ?: '-'); ?></span>
                                        <?php if ($ntSlums > 0): ?>
                                            • <span class="text-danger fw-semibold">🛖 <?php echo $ntSlums; ?> Slums</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Urban Profile</span>
                                    <a href="<?php echo htmlspecialchars($ntUrl); ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs fw-bold">
                                        View &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Bottom Ad Slot -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>

    </main>

<?php else: ?>
    <!-- ========================================================================= -->
    <!-- 2. BIHAR TOWNS & SLUMS DIRECTORY LIST VIEW                                -->
    <!-- ========================================================================= -->

    <!-- Directory Hero Banner -->
    <section class="town-hero-gradient text-white py-4 py-md-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
                            <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white-50 text-decoration-none">Census 2011</a></li>
                            <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Towns &amp; Slums Directory</li>
                        </ol>
                    </nav>

                    <h1 class="display-6 fw-bold mb-2 font-heading text-white">
                        🏙️ <?php echo !empty($districtParam) ? htmlspecialchars(ucfirst($districtParam)) . ' District' : 'Bihar'; ?> Census Towns &amp; Slums Directory
                    </h1>
                    
                    <p class="lead mb-0 text-white-75 fs-6">
                        Complete Census 2011 directory of all 198 Statutory &amp; Census Towns and 670 Slum Settlements across 38 Districts of Bihar.
                    </p>
                </div>

                <div class="col-lg-4">
                    <div class="p-3 bg-white bg-opacity-10 backdrop-blur rounded-4 border border-white border-opacity-20 text-white">
                        <div class="row g-2 text-center">
                            <div class="col-6 border-end border-white border-opacity-20 pb-2">
                                <span class="text-xs text-white-50 d-block">Total Towns</span>
                                <div class="fw-bold fs-4"><?php echo number_format($totalTownCount); ?></div>
                            </div>
                            <div class="col-6 pb-2">
                                <span class="text-xs text-white-50 d-block">Urban Pop.</span>
                                <div class="fw-bold fs-4"><?php echo number_format($totalUrbanPop); ?></div>
                            </div>
                            <div class="col-6 border-end border-white border-opacity-20 pt-2 border-top">
                                <span class="text-xs text-white-50 d-block">Slum Clusters</span>
                                <div class="fw-bold fs-4 text-warning">670</div>
                            </div>
                            <div class="col-6 pt-2 border-top">
                                <span class="text-xs text-white-50 d-block">Slum Pop.</span>
                                <div class="fw-bold fs-4 text-warning"><?php echo number_format($totalSlumPopAll); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <main class="container py-4">

        <!-- Top Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Search & Filter Controls -->
        <section class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <form method="GET" action="town.php" id="townFilterForm" class="row g-3 align-items-end">
                <!-- Search Input -->
                <div class="col-md-4 col-lg-3">
                    <label for="townSearchInput" class="form-label small fw-bold text-navy mb-1">
                        <i class="bi bi-search me-1"></i>Search Town Name / Code
                    </label>
                    <input type="text" class="form-control rounded-pill" id="townSearchInput" name="q" 
                           placeholder="e.g. Patna, Danapur, 801278..." value="<?php echo htmlspecialchars($searchParam); ?>">
                </div>

                <!-- District Filter (No All District Button) -->
                <div class="col-md-3 col-lg-3">
                    <label for="districtSelect" class="form-label small fw-bold text-navy mb-1">
                        <i class="bi bi-geo-alt me-1"></i>District
                    </label>
                    <select class="form-select rounded-pill" id="districtSelect" name="district" onchange="this.form.submit()">
                        <option value="">-- Select District (38 Districts) --</option>
                        <?php foreach ($districtsList as $d): 
                            $dSlug = $d['slug'] ?? slugify($d['name']);
                            $isSelected = (strtolower($districtParam) === strtolower($dSlug) || strtolower($districtParam) === strtolower($d['name']));
                        ?>
                            <option value="<?php echo htmlspecialchars($dSlug); ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Civic Status Filter -->
                <div class="col-md-3 col-lg-2">
                    <label for="civicSelect" class="form-label small fw-bold text-navy mb-1">
                        <i class="bi bi-building me-1"></i>Civic Status
                    </label>
                    <select class="form-select rounded-pill" id="civicSelect" name="civic" onchange="this.form.submit()">
                        <option value="">All Civic Types</option>
                        <?php foreach ($civicStatuses as $cs): 
                            $cVal = $cs['civic_status'];
                            if (empty($cVal)) continue;
                            $isSelected = ($civicParam === $cVal);
                        ?>
                            <option value="<?php echo htmlspecialchars($cVal); ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cVal); ?> (<?php echo $cs['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sort Filter -->
                <div class="col-md-2 col-lg-2">
                    <label for="sortSelect" class="form-label small fw-bold text-navy mb-1">
                        <i class="bi bi-sort-down me-1"></i>Sort By
                    </label>
                    <select class="form-select rounded-pill" id="sortSelect" name="sort" onchange="this.form.submit()">
                        <option value="pop_desc" <?php echo $sortParam === 'pop_desc' ? 'selected' : ''; ?>>Pop: High to Low</option>
                        <option value="pop_asc" <?php echo $sortParam === 'pop_asc' ? 'selected' : ''; ?>>Pop: Low to High</option>
                        <option value="slum_desc" <?php echo $sortParam === 'slum_desc' ? 'selected' : ''; ?>>Slums Pop: High</option>
                        <option value="name_asc" <?php echo $sortParam === 'name_asc' ? 'selected' : ''; ?>>Name: A to Z</option>
                        <option value="area_desc" <?php echo $sortParam === 'area_desc' ? 'selected' : ''; ?>>Area: Largest</option>
                    </select>
                </div>

                <!-- Submit / Reset Buttons -->
                <div class="col-md-12 col-lg-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold">
                        Filter
                    </button>
                    <a href="town.php" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filters">
                        <i class="bi bi-arrow-clockwise"></i>
                    </a>
                </div>

                <!-- Slum Only Checkbox Toggle -->
                <div class="col-12 pt-2 border-top">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="slumOnlySwitch" name="slum_only" value="1" <?php echo $slumOnlyParam ? 'checked' : ''; ?> onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold text-dark" for="slumOnlySwitch">
                            🛖 Show only towns with designated Slum Settlements (670 Slum Areas in Bihar)
                        </label>
                    </div>
                </div>
            </form>
        </section>

        <!-- Directory Results Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h2 class="fs-5 fw-bold text-navy mb-0">
                    Towns &amp; Urban Centers Listing 
                    <span class="badge bg-primary-subtle text-primary rounded-pill small ms-1"><?php echo number_format($totalCount); ?> Results</span>
                </h2>
                <span class="small text-muted">
                    Page <?php echo $currentPage; ?> of <?php echo max(1, ceil($totalCount / $perPage)); ?>
                </span>
            </div>
            <div class="small text-muted">
                Official 2011 Census Urban Data &amp; Release 1000 Slums
            </div>
        </div>

        <!-- Towns Data Table -->
        <section class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Town Name</th>
                            <th>Code</th>
                            <th>District</th>
                            <th>Block / Sub-District</th>
                            <th>Civic Status</th>
                            <th>Class</th>
                            <th class="text-end">Population</th>
                            <th class="text-end">Households</th>
                            <th class="text-end">Sex Ratio</th>
                            <th class="text-center">Slums</th>
                            <th class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($townsList)): ?>
                            <?php foreach ($townsList as $idx => $t): 
                                $rowNum = ($currentPage - 1) * $perPage + $idx + 1;
                                $tLink = getTownUrl($t['district_slug'], $t['town_slug']);
                                $tPop = (int)($t['population'] ?? 0);
                                $tHh = (int)($t['households'] ?? 0);
                                $tSr = (int)($t['sex_ratio'] ?? 0);
                                $tSlums = (int)($t['slum_count'] ?? 0);
                                $tCivic = $t['civic_status'] ?: 'Town';
                                $tClass = $t['town_class'] ?: '-';
                            ?>
                                <tr>
                                    <td class="ps-3 text-muted small"><?php echo $rowNum; ?></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($tLink); ?>" class="fw-bold text-navy text-decoration-none hover-primary">
                                            🏙️ <?php echo htmlspecialchars($t['town_name']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-monospace border small"><?php echo htmlspecialchars($t['town_code']); ?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo getTownUrl($t['district_slug']); ?>" class="text-decoration-none small text-primary">
                                            <?php echo htmlspecialchars($t['district_name']); ?>
                                        </a>
                                    </td>
                                    <td class="small text-muted">
                                        <?php echo htmlspecialchars($t['cd_block_name'] ?: $t['sub_district_name']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary small"><?php echo htmlspecialchars($tCivic); ?></span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-secondary small">Class <?php echo htmlspecialchars($tClass); ?></span>
                                    </td>
                                    <td class="text-end fw-bold text-dark"><?php echo number_format($tPop); ?></td>
                                    <td class="text-end text-muted small"><?php echo number_format($tHh); ?></td>
                                    <td class="text-end text-muted small"><?php echo $tSr; ?></td>
                                    <td class="text-center">
                                        <?php if ($tSlums > 0): ?>
                                            <span class="badge bg-warning-subtle text-dark fw-bold small">
                                                🛖 <?php echo $tSlums; ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center pe-3">
                                        <a href="<?php echo htmlspecialchars($tLink); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 text-xs fw-semibold">
                                            View &rarr;
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-50"></i>
                                    No towns found matching your criteria. Try adjusting your search query or filters.
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
                $queryParams = $_GET;
                unset($queryParams['page']);
                $baseUrl = 'town.php?' . http_build_query($queryParams);
            ?>
            <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-top">
                <div class="small text-muted">
                    Showing <?php echo number_format(($currentPage - 1) * $perPage + 1); ?> - <?php echo number_format(min($totalCount, $currentPage * $perPage)); ?> of <?php echo number_format($totalCount); ?> towns
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

        <!-- Bottom Ad Slot -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>

    </main>

<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
