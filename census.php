<?php
/**
 * BiharElection.com - Bihar Census 2011 & Demographics Platform
 * Covers All 38 Districts & 534 Sub-Districts (Blocks)
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Bihar Census 2011: 38 Districts & 534 Blocks Population Demographics';
$pageDescription = 'Complete Bihar Census 2011 Primary Census Abstract (PCA). Population data, sex ratio, literacy rates, SC/ST demographics, and workforce profiles for all 38 districts and 534 blocks.';
$pageKeywords = 'Bihar Census 2011, Bihar district population, Bihar literacy rate 2011, Bihar sex ratio, Bihar SC ST population, Bihar block census data';
$pageCanonical = SITE_URL . '/census.php';
$activeNav = 'census';

$biharCensus = DataProvider::getCensusBiharSummary();
$districtsCensus = DataProvider::getCensusDistricts();
$subDistrictsAll = DataProvider::getCensusSubDistricts();
$districtsList = DataProvider::getDistricts();

$cTot = $biharCensus['total'] ?? [];
$cRur = $biharCensus['rural'] ?? [];
$cUrb = $biharCensus['urban'] ?? [];

$activeTab = $_GET['tab'] ?? 'districts';
$selectedDistrictFilter = $_GET['district'] ?? '';

require_once __DIR__ . '/header.php';
?>

    <!-- Census Hero Header -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    <i class="bi bi-file-earmark-bar-graph"></i> Official Census 2011
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    38 Districts
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    534 Sub-Districts / Blocks
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2">
                    10.41 Crore Population
                </span>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">
                Bihar Census 2011 & Demographic Intelligence Hub
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 880px;">
                Comprehensive demographic, literacy, social category (SC/ST), and workforce data from the Primary Census Abstract (PCA) across all 38 districts and 534 blocks in Bihar.
            </p>

            <!-- State Summary KPI Row -->
            <div class="row g-2 g-md-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Total Population</small>
                        <span class="fs-5 fw-bold text-warning"><?php echo number_format($cTot['population'] ?? 104099452); ?></span>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Sex Ratio</small>
                        <span class="fs-5 fw-bold text-info"><?php echo $cTot['sex_ratio'] ?? 918; ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">F / 1000 M</small>
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
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">Rural Share</small>
                        <span class="fs-5 fw-bold text-warning">88.7%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">9.23 Cr Rural</small>
                    </div>
                </div>

                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-white bg-opacity-10 p-3 rounded-3 border border-white border-opacity-10 text-white text-center">
                        <small class="text-white-50 d-block text-uppercase fw-bold" style="font-size: 0.72rem;">SC Representation</small>
                        <span class="fs-5 fw-bold text-danger"><?php echo $cTot['sc_percentage'] ?? 15.91; ?>%</span>
                        <small class="text-white-50 d-block" style="font-size: 0.7rem;">1.65 Cr SC Pop.</small>
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
            <ul class="nav nav-pills nav-fill gap-2" id="censusTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=districts" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'districts' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-geo-alt-fill me-1 text-danger"></i> All 38 Districts Census Matrix
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=blocks" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'blocks' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-diagram-3-fill me-1 text-warning"></i> 534 Sub-Districts / Blocks Directory
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a href="census.php?tab=social" class="nav-link rounded-3 py-2 fw-bold <?php echo $activeTab === 'social' ? 'active bg-primary' : 'text-dark bg-light'; ?>">
                        <i class="bi bi-pie-chart-fill me-1 text-success"></i> State Social & Workforce Matrix
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
                            <th class="py-3 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $dIdx = 1;
                        foreach ($districtsCensus as $slug => $d): 
                            $tot = $d['total'] ?? [];
                            $rur = $d['rural'] ?? [];
                            $urb = $d['urban'] ?? [];
                            $rPct = !empty($tot['population']) ? round((($rur['population'] ?? 0) / $tot['population']) * 100, 1) : 0;
                            $uPct = !empty($tot['population']) ? round((($urb['population'] ?? 0) / $tot['population']) * 100, 1) : 0;
                        ?>
                        <tr class="district-census-row" data-name="<?php echo htmlspecialchars(strtolower($d['name'])); ?>">
                            <td class="text-muted fw-bold"><?php echo $dIdx++; ?></td>
                            <td>
                                <a href="district.php?slug=<?php echo $slug; ?>" class="fw-bold text-decoration-none text-primary fs-6">
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </a>
                                <div class="small text-muted"><?php echo count($d['sub_districts'] ?? []); ?> Sub-Districts</div>
                            </td>
                            <td class="text-end"><?php echo number_format($tot['households'] ?? 0); ?></td>
                            <td class="text-end fw-bold text-dark fs-6">
                                <?php echo number_format($tot['population'] ?? 0); ?>
                            </td>
                            <td class="text-end small">
                                <span class="text-primary">M: <?php echo number_format($tot['male'] ?? 0); ?></span><br>
                                <span class="text-danger">F: <?php echo number_format($tot['female'] ?? 0); ?></span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?php echo ($tot['sex_ratio'] ?? 0) >= 918 ? 'bg-success' : 'bg-warning text-dark'; ?> rounded-pill px-2 py-1">
                                    <?php echo $tot['sex_ratio'] ?? 0; ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold <?php echo ($tot['literacy_rate'] ?? 0) >= 61.8 ? 'text-success' : 'text-danger'; ?>">
                                    <?php echo $tot['literacy_rate'] ?? 0; ?>%
                                </span>
                            </td>
                            <td class="text-end">
                                <div><?php echo number_format($tot['sc_population'] ?? 0); ?></div>
                                <small class="text-muted">(<?php echo $tot['sc_percentage'] ?? 0; ?>%)</small>
                            </td>
                            <td class="text-center small" style="min-width: 120px;">
                                <div class="d-flex justify-content-between text-muted mb-1" style="font-size: 0.72rem;">
                                    <span><?php echo $rPct; ?>% R</span>
                                    <span><?php echo $uPct; ?>% U</span>
                                </div>
                                <div class="progress" style="height: 4px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $rPct; ?>%"></div>
                                    <div class="progress-bar bg-info" style="width: <?php echo $uPct; ?>%"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="district.php?slug=<?php echo $slug; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-2 fw-semibold">
                                    Hub &rarr;
                                </a>
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
                            <th class="py-3 text-end">Total Workers</th>
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
                            <td class="text-end"><?php echo number_format($sb['total_workers'] ?? 0); ?></td>
                        </tr>
                        <?php endforeach; endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <?php elseif ($activeTab === 'social'): ?>
        <!-- ========================================================================= -->
        <!-- TAB 3: STATE SOCIAL & WORKFORCE MATRIX                                    -->
        <!-- ========================================================================= -->
        <section class="mb-5">
            <div class="row g-4">
                
                <!-- Card 1: Gender & Child Profile -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-people-fill text-primary me-2"></i> Gender & Age Demographics (0-6 Years)
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
                                    <tr>
                                        <td>Child Population (0–6 Yrs)</td>
                                        <td class="text-end"><?php echo number_format($cTot['pop_0_6'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cRur['pop_0_6'] ?? 0); ?></td>
                                        <td class="text-end"><?php echo number_format($cUrb['pop_0_6'] ?? 0); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Literacy & Education Gap -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white h-100">
                        <h4 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-mortarboard-fill text-success me-2"></i> Literacy Rate & Gender Disparity
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
                            <i class="bi bi-briefcase-fill text-warning me-2"></i> Bihar Employment & Workforce Classification
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
                                    <small class="text-muted d-block">Students, dependents & seniors</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <?php endif; ?>

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
