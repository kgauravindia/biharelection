<?php
/**
 * BiharElection.com - Bihar Census 2011 Town, Slum & Ward Intelligence Directory
 * Complete coverage of 198 Statutory & Census Towns and 670 Slum Settlements / Town Wards across 38 Districts
 */
require_once __DIR__ . '/config.php';

$pdo = Database::getConnection();

// Input parameters resolution
$codeParam = trim($_GET['code'] ?? '');
$districtParam = trim($_GET['district'] ?? '');
$blockParam = trim($_GET['block'] ?? '');
$townParam = trim($_GET['town'] ?? '');
$slumParam = trim($_GET['slum'] ?? '');
$slumIdParam = (int)($_GET['slum_id'] ?? 0);
$civicParam = trim($_GET['civic'] ?? '');
$slumOnlyParam = !empty($_GET['slum_only']) && $_GET['slum_only'] !== '0';
$searchParam = trim($_GET['q'] ?? '');
$slugParam = trim($_GET['slug'] ?? '');
$sortParam = trim($_GET['sort'] ?? 'pop_desc');
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;

$districtsList = DataProvider::getDistricts();

// Handle generic slug routing
if (empty($codeParam) && empty($townParam) && empty($slumIdParam) && !empty($slugParam)) {
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

$slumObj = null;
$town = null;

// 1. Try to find a specific Slum / Ward Profile
if ($slumIdParam > 0) {
    $slumObj = DataProvider::getSlumById($slumIdParam);
    if ($slumObj) {
        $town = DataProvider::getTownByCode($slumObj['town_code']);
    }
} elseif (!empty($slumParam) && !empty($townParam)) {
    $slumObj = DataProvider::getSlumBySlug($districtParam, $townParam, $slumParam);
    if ($slumObj) {
        $town = DataProvider::getTownByCode($slumObj['town_code']);
    }
}

// 2. If not a slum profile, try to find a specific Town
if (!$slumObj) {
    if (!empty($codeParam)) {
        $town = DataProvider::getTownByCode($codeParam);
    } elseif (!empty($townParam)) {
        if (is_numeric($townParam) && strlen($townParam) >= 5) {
            $town = DataProvider::getTownByCode($townParam);
        } else {
            $town = DataProvider::getTownBySlug($districtParam, $townParam);
        }
    }
}

// 3. Set up metadata for Slum Profile vs Town Profile vs Directory
if ($slumObj) {
    // =========================================================================
    // SLUM / TOWN WARD PROFILE METADATA
    // =========================================================================
    $sName = $slumObj['slum_name'];
    $sId = $slumObj['id'];
    $tName = $slumObj['town_name'];
    $tCode = $slumObj['town_code'];
    $tSlug = $slumObj['town_slug'];
    $dName = $slumObj['district_name'];
    $dSlug = $slumObj['district_slug'];
    $bName = $slumObj['sub_district_name'] ?: ($town['cd_block_name'] ?? 'Block');
    $bSlug = $slumObj['sub_district_slug'];
    $isNotified = ((int)$slumObj['is_notified'] === 1);
    
    $sPop = (int)($slumObj['slum_population'] ?? 0);
    $sHh = (int)($slumObj['households'] ?? 0);
    $sPavedKm = (float)($slumObj['paved_roads_km'] ?? 0);
    $sDrainage = $slumObj['drainage_system'] ?: 'Not Specified';
    
    $latPit = (int)($slumObj['latrines_pit'] ?? 0);
    $latFlush = (int)($slumObj['latrines_flush'] ?? 0);
    $latService = (int)($slumObj['latrines_service'] ?? 0);
    $latOthers = (int)($slumObj['latrines_others'] ?? 0);
    $latComm = (int)($slumObj['latrines_community'] ?? 0);
    $totalLatrines = $latPit + $latFlush + $latService + $latOthers + $latComm;

    $tapPoints = (int)($slumObj['tap_points_water'] ?? 0);
    $elecDom = (int)($slumObj['electricity_domestic'] ?? 0);
    $elecRoad = (int)($slumObj['electricity_road_light'] ?? 0);
    $elecOthers = (int)($slumObj['electricity_others'] ?? 0);

    $pageTitle = "{$sName} Slum & Ward Profile: Population, Sanitation & Demographics ({$tName}, {$dName})";
    $pageDescription = "Official Census 2011 Slum Release 1000 profile for {$sName} in {$tName}, {$dName} District, Bihar. Population: " . number_format($sPop) . ", Households: " . number_format($sHh) . ", Drainage: {$sDrainage}, Tap Water: {$tapPoints}.";
    $pageKeywords = "{$sName} slum, {$sName} {$tName}, {$tName} slums, Bihar town wards, Census 2011 Slum 1000 Bihar";
    $pageCanonical = getSlumUrl($dSlug, $tSlug, $slumObj['slum_slug'] ?: $sId);

    // Fetch other slums in this town
    $siblingSlums = [];
    if ($pdo) {
        try {
            $stmtSS = $pdo->prepare("SELECT * FROM census_town_slums_2011 WHERE town_code = :code AND id != :sid ORDER BY slum_population DESC");
            $stmtSS->execute([':code' => $tCode, ':sid' => $sId]);
            $siblingSlums = $stmtSS->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}
    }

} elseif ($town) {
    // =========================================================================
    // SINGLE TOWN PROFILE METADATA
    // =========================================================================
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
    // =========================================================================
    // DIRECTORY MODE
    // =========================================================================
    $distLabel = !empty($districtParam) ? ucfirst($districtParam) . ' District ' : 'Bihar ';
    $pageTitle = "{$distLabel}Census 2011 Towns & Slums Directory: 198 Urban Centers Population & Demographics";
    $pageDescription = "Explore the complete Census 2011 town and slum directory of Bihar covering all 198 statutory towns, municipal corporations, nagar parishads, census towns and 670 slum areas across 38 districts.";
    $pageKeywords = "Bihar towns directory, Bihar 198 towns, Bihar census towns list, Bihar municipal corporations, Bihar slum population 2011, Bihar urban census 2011";
    $pageCanonical = !empty($districtParam) ? getTownUrl($districtParam) : SITE_URL . "/town";

    // Fetch CD Blocks for active district if selected
    $districtBlocks = [];
    if (!empty($districtParam) && $pdo) {
        try {
            $stmtB = $pdo->prepare("SELECT sub_district_slug, sub_district_name, cd_block_name, COUNT(*) as town_count, SUM(population) as total_pop FROM census_towns_2011 WHERE district_slug = :dslug GROUP BY sub_district_slug, sub_district_name, cd_block_name ORDER BY sub_district_name ASC");
            $stmtB->execute([':dslug' => strtolower(trim($districtParam))]);
            $districtBlocks = $stmtB->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}
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

$activeNav = 'census';
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
.slum-hero-gradient {
    background: linear-gradient(135deg, #2d1300 0%, #78350f 50%, #b45309 100%);
    position: relative;
    overflow: hidden;
}
.table-slum th {
    background-color: #f8fafc;
    font-size: 0.82rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.hover-primary:hover {
    color: var(--primary-navy, #0b1a30) !important;
    text-decoration: underline !important;
}
.slum-hover-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.slum-hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
@media print {
    .town-hero-gradient, .slum-hero-gradient, nav, .btn, form, footer, .ad-container {
        display: none !important;
    }
}
</style>

<?php if ($slumObj): ?>
    <!-- ========================================================================= -->
    <!-- 1. DEDICATED SLUM / TOWN WARD PROFILE VIEW                                -->
    <!-- ========================================================================= -->

    <!-- Schema.org JSON-LD Structured Data for Slum Settlement -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Place",
      "name": "<?php echo addslashes($sName); ?>",
      "description": "Census 2011 Slum 1000 settlement profile for <?php echo addslashes($sName); ?> in <?php echo addslashes($tName); ?>, <?php echo addslashes($dName); ?> District, Bihar.",
      "containedInPlace": {
        "@type": "City",
        "name": "<?php echo addslashes($tName); ?>"
      },
      "identifier": "<?php echo $sId; ?>"
    }
    </script>

    <!-- Slum Header Hero Banner -->
    <section class="slum-hero-gradient text-white py-4 py-md-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-white-50 text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white-50 text-decoration-none">Census 2011</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl(); ?>" class="text-white-50 text-decoration-none">Towns Directory</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl($dSlug); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($dName); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($tName); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($sName); ?></li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-geo-alt-fill me-1"></i> Slum ID: #<?php echo $sId; ?>
                        </span>
                        <span class="badge bg-light text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            Town Code: <?php echo htmlspecialchars($tCode); ?>
                        </span>
                        <?php if ($isNotified): ?>
                            <span class="badge bg-success text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                                <i class="bi bi-check-circle-fill me-1"></i> Notified by Government
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                                Non-Notified Settlement
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-6 fw-bold mb-2 font-heading text-white">
                        🛖 <?php echo htmlspecialchars($sName); ?>
                    </h1>
                    
                    <p class="lead mb-0 text-white-75 fs-6">
                        Designated Slum / Urban Ward in <strong><?php echo htmlspecialchars($tName); ?></strong>, 
                        <strong><?php echo htmlspecialchars($dName); ?></strong> District, Bihar (Census 2011 Slum_1000).
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Check {$sName} Slum & Ward Demographics in {$tName}: " . $pageCanonical); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-whatsapp me-1"></i> Share
                        </a>
                        <a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="btn btn-outline-light fw-bold rounded-pill px-3 py-2">
                            <i class="bi bi-arrow-left me-1"></i> <?php echo htmlspecialchars($tName); ?> Town Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Slum Main Container -->
    <main class="container py-4">

        <!-- Top Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Quick Slum KPI Tiles -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-warning">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slum Population</span>
                    <h3 class="fw-bold text-danger mb-0 fs-4"><?php echo number_format($sPop); ?></h3>
                    <span class="text-xs text-muted">Approximate (2011)</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-info">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Households</span>
                    <h3 class="fw-bold text-info mb-0 fs-4"><?php echo number_format($sHh); ?></h3>
                    <span class="text-xs text-muted">Families</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-success">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Paved Roads</span>
                    <h3 class="fw-bold text-success mb-0 fs-4"><?php echo $sPavedKm; ?></h3>
                    <span class="text-xs text-muted">Kilometers</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-primary">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Latrines</span>
                    <h3 class="fw-bold text-primary mb-0 fs-4"><?php echo $totalLatrines; ?></h3>
                    <span class="text-xs text-muted">Sanitation Units</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-info">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Public Tap Water</span>
                    <h3 class="fw-bold text-info mb-0 fs-4"><?php echo $tapPoints; ?></h3>
                    <span class="text-xs text-muted">Hydrant / Taps</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-warning">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Domestic Elec.</span>
                    <h3 class="fw-bold text-dark mb-0 fs-4"><?php echo $elecDom; ?></h3>
                    <span class="text-xs text-muted">Connections</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Sanitation, Drainage & Electricity Infrastructure -->
            <div class="col-lg-7">
                <!-- Sanitation & Latrines Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-droplet-fill text-primary me-2"></i>Sanitation &amp; Latrine Facilities</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold small">Census 2011</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Flush / Pour Flush</span>
                                <h4 class="fw-bold text-success mb-0"><?php echo $latFlush; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Pit Latrines</span>
                                <h4 class="fw-bold text-primary mb-0"><?php echo $latPit; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Community Latrines</span>
                                <h4 class="fw-bold text-info mb-0"><?php echo $latComm; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Service Latrines</span>
                                <h4 class="fw-bold text-secondary mb-0"><?php echo $latService; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Other Latrine Types</span>
                                <h4 class="fw-bold text-dark mb-0"><?php echo $latOthers; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                    </div>

                    <!-- Drainage Status -->
                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="text-muted small d-block">System of Drainage:</span>
                            <span class="fw-bold text-navy fs-6"><?php echo htmlspecialchars($sDrainage); ?></span>
                        </div>
                        <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill">
                            <?php echo htmlspecialchars($sDrainage); ?>
                        </span>
                    </div>
                </div>

                <!-- Water Supply & Power Grid -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Water Supply &amp; Electricity Grid</span>
                        <span class="badge bg-warning-subtle text-dark fw-semibold small">Civic Amenities</span>
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-info-subtle border border-info-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">🚰 Protected Water Supply</span>
                                </div>
                                <div class="fs-4 fw-bold text-navy mb-1"><?php echo $tapPoints; ?> Tap Points</div>
                                <p class="small text-muted mb-0">Public hydrants and protected water tap points installed for this slum cluster.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">⚡ Electricity Connections</span>
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Domestic Connections:</span>
                                        <strong><?php echo $elecDom; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Road/Street Lighting:</span>
                                        <strong><?php echo $elecRoad; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Other Connections:</span>
                                        <strong><?php echo $elecOthers; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Parent Town Summary & Navigation -->
            <div class="col-lg-5">
                <!-- Parent Town Summary Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-top border-4 border-primary">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3">
                        <i class="bi bi-buildings-fill text-primary me-2"></i>Parent Town Information
                    </h4>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Town Name:</span>
                            <a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="fw-bold text-primary text-decoration-none">
                                <?php echo htmlspecialchars($tName); ?> &rarr;
                            </a>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Town Code:</span>
                            <span class="font-monospace fw-bold"><?php echo htmlspecialchars($tCode); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">District:</span>
                            <span class="fw-bold text-navy"><?php echo htmlspecialchars($dName); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Town Population:</span>
                            <span class="fw-bold text-dark"><?php echo number_format((int)($town['population'] ?? 0)); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Civic Status:</span>
                            <span class="badge bg-primary text-white"><?php echo htmlspecialchars($town['civic_status'] ?? 'Town'); ?></span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex flex-column gap-2">
                        <a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm">
                            🏙️ Full <?php echo htmlspecialchars($tName); ?> Profile &rarr;
                        </a>
                        <a href="<?php echo getVillageUrl($dSlug, $bSlug); ?>" class="btn btn-outline-success rounded-pill w-100 fw-semibold">
                            🏡 Go to <?php echo htmlspecialchars($bName); ?> Village Data &rarr;
                        </a>
                    </div>
                </div>

                <!-- Sibling Slums in same town -->
                <?php if (!empty($siblingSlums)): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-navy font-heading fs-6 mb-0">
                                🛖 Other Slums in <?php echo htmlspecialchars($tName); ?> (<?php echo count($siblingSlums); ?>)
                            </h5>
                        </div>

                        <div class="list-group list-group-flush small" style="max-height: 380px; overflow-y: auto;">
                            <?php foreach ($siblingSlums as $ss): 
                                $ssUrl = getSlumUrl($dSlug, $tSlug, $ss['slum_slug'] ?: $ss['id']);
                                $ssPop = (int)($ss['slum_population'] ?? 0);
                                $ssHh = (int)($ss['households'] ?? 0);
                            ?>
                                <a href="<?php echo htmlspecialchars($ssUrl); ?>" class="list-group-item list-group-item-action px-2 py-2.5 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-navy"><?php echo htmlspecialchars($ss['slum_name']); ?></div>
                                        <span class="text-xs text-muted">👥 <?php echo number_format($ssPop); ?> Pop. • 🏠 <?php echo number_format($ssHh); ?> HH</span>
                                    </div>
                                    <span class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5 text-xs">View &rarr;</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Bottom Ad Slot -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>

    </main>

<?php elseif ($town): ?>
    <!-- ========================================================================= -->
    <!-- 2. SINGLE TOWN PROFILE VIEW                                               -->
    <!-- ========================================================================= -->

    <!-- Schema.org JSON-LD Structured Data for Urban Entity -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "City",
      "name": "<?php echo addslashes($tName); ?>",
      "description": "Official 2011 Census urban demographic data for <?php echo addslashes($tName); ?>, <?php echo addslashes($dName); ?> District, Bihar.",
      "containedInPlace": {
        "@type": "AdministrativeArea",
        "name": "<?php echo addslashes($dName); ?> District, Bihar"
      },
      "identifier": "<?php echo $tCode; ?>"
    }
    </script>

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
                            <a href="#slums-section" class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm text-decoration-none hover-shadow animate-pulse">
                                🛖 <?php echo $totalSlums; ?> Slum Settlements &darr;
                            </a>
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
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');" class="btn btn-outline-light fw-bold rounded-pill px-3 py-2">
                            <i class="bi bi-link-45deg me-1"></i> Copy Link
                        </button>
                        <a href="<?php echo getDistrictUrl($dSlug); ?>" class="btn btn-warning fw-bold text-dark rounded-pill px-3 py-2">
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
                <?php if ($totalSlums > 0): ?>
                    <a href="#slums-section" class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 text-decoration-none hover-shadow" style="border-top-color: #7928ca !important;">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slums (<?php echo $totalSlums; ?>)</span>
                        <h3 class="fw-bold mb-0 fs-4" style="color: #7928ca;"><?php echo $slumPopPct; ?>%</h3>
                        <span class="text-xs text-primary fw-semibold"><?php echo number_format($totalSlumPop); ?> in Slums &darr;</span>
                    </a>
                <?php else: ?>
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 text-center border-top border-4 border-secondary">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slums</span>
                        <h3 class="fw-bold text-muted mb-0 fs-4">0</h3>
                        <span class="text-xs text-muted">No Slum Areas</span>
                    </div>
                <?php endif; ?>
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

                    <div class="mt-3 pt-3 border-top d-flex flex-column gap-2">
                        <a href="<?php echo getPanchayatUrl($dSlug, $bSlug); ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-semibold">
                            🌾 <?php echo htmlspecialchars($bName); ?> Gram Panchayats &rarr;
                        </a>
                        <a href="<?php echo getZilaParishadUrl($dSlug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill w-100 fw-semibold">
                            🏛️ <?php echo htmlspecialchars($dName); ?> Zila Parishad Board
                        </a>
                    </div>
                </div>

                <!-- Dedicated Go to Village Data Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-top border-4 border-success">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success-subtle text-success p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                            🏡
                        </div>
                        <div>
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-0.5 rounded-pill small">Rural Directory</span>
                            <h4 class="fw-bold text-navy fs-5 mb-0">Go to Village Data</h4>
                        </div>
                    </div>
                    <p class="small text-muted mb-3">
                        Explore all rural Census 2011 villages, population, households, and Gram Panchayats in <strong><?php echo htmlspecialchars($bName); ?></strong> and <strong><?php echo htmlspecialchars($dName); ?></strong> district.
                    </p>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?php echo getVillageUrl($dSlug, $bSlug); ?>" class="btn btn-success rounded-pill fw-bold py-2 shadow-sm">
                            🏡 <?php echo htmlspecialchars($bName); ?> Villages &rarr;
                        </a>
                        <a href="<?php echo getVillageUrl($dSlug); ?>" class="btn btn-outline-success rounded-pill fw-semibold btn-sm py-1.5">
                            All <?php echo htmlspecialchars($dName); ?> District Villages &rarr;
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
        <?php if (!empty($slums)): 
            $notifiedCount = 0;
            $nonNotifiedCount = 0;
            $withWaterCount = 0;
            $withRoadsCount = 0;
            foreach ($slums as $s) {
                if ((int)$s['is_notified'] === 1) $notifiedCount++;
                else $nonNotifiedCount++;
                if ((int)$s['tap_points_water'] > 0) $withWaterCount++;
                if ((float)$s['paved_roads_km'] > 0) $withRoadsCount++;
            }
        ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning" id="slums-section" style="scroll-margin-top: 80px;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            DH 2011 DCHB Town Release 1000
                        </span>
                        <h3 class="fw-bold text-navy font-heading mb-0 fs-5">
                            🛖 Slum Settlements &amp; Wards in <?php echo htmlspecialchars($tName); ?> (<?php echo count($slums); ?>)
                        </h3>
                        <p class="text-muted small mb-0 mt-1">Click on any of the <?php echo count($slums); ?> slums below to view full micro-sanitation, water, road and electrical metrics.</p>
                    </div>
                    <?php if (count($slums) > 3): ?>
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" id="slumTableFilter" class="form-control form-control-sm" placeholder="Search among <?php echo count($slums); ?> slums / wards...">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Filter Chips -->
                <?php if (count($slums) > 5): ?>
                    <div class="d-flex flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <button type="button" class="btn btn-xs btn-outline-dark rounded-pill active slum-filter-btn" data-filter="all">
                            All Slums (<?php echo count($slums); ?>)
                        </button>
                        <?php if ($notifiedCount > 0): ?>
                            <button type="button" class="btn btn-xs btn-outline-success rounded-pill slum-filter-btn" data-filter="notified">
                                Notified (<?php echo $notifiedCount; ?>)
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill slum-filter-btn" data-filter="non-notified">
                            Non-Notified (<?php echo $nonNotifiedCount; ?>)
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-info rounded-pill slum-filter-btn" data-filter="water">
                            🚰 With Tap Water (<?php echo $withWaterCount; ?>)
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-warning rounded-pill text-dark slum-filter-btn" data-filter="roads">
                            🛣️ With Paved Roads (<?php echo $withRoadsCount; ?>)
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-slum align-middle mb-0" id="slumsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Slum / Ward Name</th>
                                <th>Status</th>
                                <th class="text-end">Households</th>
                                <th class="text-end">Population</th>
                                <th>Paved Roads</th>
                                <th>Drainage</th>
                                <th>Sanitation / Latrines</th>
                                <th>Tap Water</th>
                                <th>Domestic Elec.</th>
                                <th class="text-center">Action</th>
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
                                $slumProfileUrl = getSlumUrl($dSlug, $town['town_slug'], $s['slum_slug'] ?: $s['id']);

                                $latPit = (int)($s['latrines_pit'] ?? 0);
                                $latFlush = (int)($s['latrines_flush'] ?? 0);
                                $latService = (int)($s['latrines_service'] ?? 0);
                                $latOthers = (int)($s['latrines_others'] ?? 0);
                                $latComm = (int)($s['latrines_community'] ?? 0);
                                $totalLatrines = $latPit + $latFlush + $latService + $latOthers + $latComm;

                                $jsonData = htmlspecialchars(json_encode([
                                    'id' => $s['id'],
                                    'name' => $sName,
                                    'town' => $tName,
                                    'district' => $dName,
                                    'status' => $isNotified ? 'Notified by Government' : 'Non-Notified Settlement',
                                    'is_notified' => $isNotified,
                                    'pop' => number_format($sPop),
                                    'hh' => number_format($sHh),
                                    'roads' => $pavedKm,
                                    'drainage' => $drain,
                                    'lat_flush' => $latFlush,
                                    'lat_pit' => $latPit,
                                    'lat_comm' => $latComm,
                                    'lat_service' => $latService,
                                    'lat_others' => $latOthers,
                                    'lat_total' => $totalLatrines,
                                    'water' => $tapWater,
                                    'elec_dom' => $elecDom,
                                    'elec_road' => (int)($s['electricity_road_light'] ?? 0),
                                    'elec_others' => (int)($s['electricity_others'] ?? 0),
                                    'url' => $slumProfileUrl
                                ]), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr class="slum-row" 
                                    data-notified="<?php echo $isNotified ? '1' : '0'; ?>"
                                    data-water="<?php echo $tapWater > 0 ? '1' : '0'; ?>"
                                    data-roads="<?php echo $pavedKm > 0 ? '1' : '0'; ?>"
                                    data-slum='<?php echo $jsonData; ?>'
                                    style="cursor: pointer;">
                                    <td class="text-muted small"><?php echo $idx + 1; ?></td>
                                    <td>
                                        <div class="fw-bold text-navy slum-name-cell">
                                            🛖 <?php echo htmlspecialchars($sName); ?>
                                        </div>
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
                                            <?php if ($latFlush > 0): ?>
                                                <span class="text-xs text-muted d-block">Flush: <?php echo $latFlush; ?></span>
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
                                    <td class="text-center" onclick="event.stopPropagation();">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 text-xs fw-semibold open-slum-modal-btn">
                                                Info &rarr;
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Interactive Slum Detail Modal -->
            <div class="modal fade" id="slumDetailModal" tabindex="-1" aria-labelledby="slumModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header bg-warning bg-opacity-25 border-bottom border-warning">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-0.5 rounded-pill small mb-1" id="mSlumStatus">Notified</span>
                                <h5 class="modal-title fw-bold text-navy" id="slumModalTitle">Slum / Ward Details</h5>
                                <span class="text-xs text-muted" id="mSlumParent">Parent Town</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Quick stats row -->
                            <div class="row g-3 text-center mb-4">
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <span class="text-xs text-muted d-block mb-1">Slum Population</span>
                                        <div class="fw-bold fs-4 text-danger" id="mSlumPop">0</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <span class="text-xs text-muted d-block mb-1">Households</span>
                                        <div class="fw-bold fs-4 text-info" id="mSlumHh">0</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <span class="text-xs text-muted d-block mb-1">Paved Roads</span>
                                        <div class="fw-bold fs-4 text-success" id="mSlumRoads">0 km</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Infrastructure -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h6 class="fw-bold text-navy mb-2"><i class="bi bi-droplet-fill text-primary me-1"></i> Sanitation &amp; Latrines</h6>
                                        <div class="small">
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Flush Latrines:</span>
                                                <strong id="mLatFlush">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Pit Latrines:</span>
                                                <strong id="mLatPit">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Community Latrines:</span>
                                                <strong id="mLatComm">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Service Latrines:</span>
                                                <strong id="mLatService">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1">
                                                <span class="text-muted">Total Latrines:</span>
                                                <strong class="text-primary" id="mLatTotal">0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h6 class="fw-bold text-navy mb-2"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Water &amp; Electricity</h6>
                                        <div class="small">
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Drainage System:</span>
                                                <strong id="mDrainage">None</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Public Water Taps:</span>
                                                <strong class="text-info" id="mWater">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Domestic Electricity:</span>
                                                <strong id="mElecDom">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1">
                                                <span class="text-muted">Street Lighting:</span>
                                                <strong id="mElecRoad">0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <a href="#" id="mSlumProfileLink" class="btn btn-primary rounded-pill px-4 fw-bold">
                                Open Dedicated Profile &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Table Search
                var filterInput = document.getElementById('slumTableFilter');
                if (filterInput) {
                    filterInput.addEventListener('input', function() {
                        var q = this.value.toLowerCase().trim();
                        var rows = document.querySelectorAll('.slum-row');
                        rows.forEach(function(row) {
                            var text = row.querySelector('.slum-name-cell').textContent.toLowerCase();
                            if (text.includes(q)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                }

                // Filter Buttons
                var filterBtns = document.querySelectorAll('.slum-filter-btn');
                filterBtns.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        filterBtns.forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        var fType = this.getAttribute('data-filter');
                        var rows = document.querySelectorAll('.slum-row');
                        rows.forEach(function(row) {
                            if (fType === 'all') {
                                row.style.display = '';
                            } else if (fType === 'notified') {
                                row.style.display = (row.getAttribute('data-notified') === '1') ? '' : 'none';
                            } else if (fType === 'non-notified') {
                                row.style.display = (row.getAttribute('data-notified') === '0') ? '' : 'none';
                            } else if (fType === 'water') {
                                row.style.display = (row.getAttribute('data-water') === '1') ? '' : 'none';
                            } else if (fType === 'roads') {
                                row.style.display = (row.getAttribute('data-roads') === '1') ? '' : 'none';
                            }
                        });
                    });
                });

                // Modal Popup on Row Click
                var slumModalEl = document.getElementById('slumDetailModal');
                var slumModal = slumModalEl ? new bootstrap.Modal(slumModalEl) : null;

                document.querySelectorAll('.slum-row').forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        var rawData = this.getAttribute('data-slum');
                        if (!rawData) return;
                        var d = JSON.parse(rawData);

                        document.getElementById('slumModalTitle').textContent = '🛖 ' + d.name;
                        document.getElementById('mSlumStatus').textContent = d.status;
                        document.getElementById('mSlumStatus').className = 'badge ' + (d.is_notified ? 'bg-success text-white' : 'bg-secondary text-white') + ' fw-bold px-2 py-0.5 rounded-pill small mb-1';
                        document.getElementById('mSlumParent').textContent = d.town + ', ' + d.district + ' District';
                        document.getElementById('mSlumPop').textContent = d.pop;
                        document.getElementById('mSlumHh').textContent = d.hh;
                        document.getElementById('mSlumRoads').textContent = d.roads + ' km';
                        document.getElementById('mLatFlush').textContent = d.lat_flush;
                        document.getElementById('mLatPit').textContent = d.lat_pit;
                        document.getElementById('mLatComm').textContent = d.lat_comm;
                        document.getElementById('mLatService').textContent = d.lat_service;
                        document.getElementById('mLatTotal').textContent = d.lat_total;
                        document.getElementById('mDrainage').textContent = d.drainage;
                        document.getElementById('mWater').textContent = d.water + ' Taps';
                        document.getElementById('mElecDom').textContent = d.elec_dom;
                        document.getElementById('mElecRoad').textContent = d.elec_road;
                        document.getElementById('mSlumProfileLink').setAttribute('href', d.url);

                        if (slumModal) slumModal.show();
                    });
                });
            });
            </script>
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
    <!-- 3. BIHAR TOWNS & SLUMS DIRECTORY LIST VIEW                                -->
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

        <!-- Cross-Navigation Link to Village Data -->
        <div class="alert alert-success border-0 shadow-sm rounded-4 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div class="d-flex align-items-center gap-3">
                <span class="fs-2">🏡</span>
                <div>
                    <div class="fw-bold text-success fs-6">Looking for Rural &amp; Village Census Data?</div>
                    <small class="text-muted">Explore complete population and demographic data for all 44,874 Census Villages across 38 Districts and 534 Blocks in Bihar.</small>
                </div>
            </div>
            <a href="<?php echo !empty($districtParam) ? getVillageUrl($districtParam) : getVillageUrl(); ?>" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm">
                Go to Village Directory &rarr;
            </a>
        </div>

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

                <!-- CD Block Filter (Activated when District Selected) -->
                <?php if (!empty($districtBlocks)): ?>
                <div class="col-md-3 col-lg-2">
                    <label for="blockSelect" class="form-label small fw-bold text-navy mb-1">
                        <i class="bi bi-diagram-3 me-1"></i>CD Block
                    </label>
                    <select class="form-select rounded-pill" id="blockSelect" name="block" onchange="this.form.submit()">
                        <option value="">All CD Blocks</option>
                        <?php foreach ($districtBlocks as $b): 
                            $bSlug = $b['sub_district_slug'] ?: slugify($b['cd_block_name']);
                            $bName = $b['sub_district_name'] ?: $b['cd_block_name'];
                            $isSelected = (strtolower($blockParam) === strtolower($bSlug) || strtolower($blockParam) === strtolower($bName));
                        ?>
                            <option value="<?php echo htmlspecialchars($bSlug); ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($bName); ?> (<?php echo $b['town_count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <?php endif; ?>

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
                <div class="col-md-12 col-lg-<?php echo !empty($districtBlocks) ? '12' : '2'; ?> d-flex gap-2">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        Filter Towns
                    </button>
                    <a href="town.php" class="btn btn-outline-secondary rounded-pill px-3" title="Reset Filters">
                        <i class="bi bi-arrow-clockwise"></i> Reset
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
