<?php
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? 'saran';
$district = DataProvider::getDistrictBySlug($slug);

if (!$district) {
    $district = DataProvider::getDistrictBySlug('saran');
}

$allDistricts = DataProvider::getDistricts();
$districtAcs = DataProvider::getConstituenciesByDistrict($district['name']);
$zilaSummary = DataProvider::getZilaParishadSummary($district['slug']);
$zilaMembers = DataProvider::getZilaParishadMembers($district['slug']);
$zilaOfficials = DataProvider::getZilaParishadOfficials($district['slug']);
$panchayatSummary = DataProvider::getPanchayatSummary($district['slug']);
$zila2016 = DataProvider::getZilaParishad2016($district['slug']);
$samiti2016 = DataProvider::getPanchayatSamiti2016($district['slug']);
$mukhiyas2016 = DataProvider::getMukhiyas2016($district['slug']);
$districtCensus = DataProvider::getCensusDistrict($district['slug']);
$subDistricts = DataProvider::getCensusSubDistricts($district['slug']);
$biharCensus = DataProvider::getCensusBiharSummary();
$cTot = $districtCensus['total'] ?? ($district['census_2011'] ?? []);
$cRur = $districtCensus['rural'] ?? [];
$cUrb = $districtCensus['urban'] ?? [];

$pageTitle = "{$district['name']} District Election Hub 2026: All Assembly Constituencies, MLA List & Panchayat Delimitation";
$pageDescription = "{$district['name']} District Bihar Election 2026 data hub: Headquarters in {$district['headquarters']}, {$district['total_ac']} Assembly Constituencies, voter demographics, MLA list, and 2026 Panchayat Delimitation updates.";
$pageKeywords = "{$district['name']} District Election 2026, {$district['name']} Vidhan Sabha Seats, Chhapra MLA, Saran election result, Bihar district election hub";
$pageCanonical = getDistrictUrl($district['slug']);
$activeNav = 'districts';

require_once __DIR__ . '/header.php';
?>

    <!-- District Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">Division: <?php echo htmlspecialchars($district['division']); ?></span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">Headquarters: <?php echo htmlspecialchars($district['headquarters']); ?></span>
                <span class="badge bg-warning bg-opacity-25 text-warning fw-bold px-3 py-2">
                    Total Assembly Seats: <?php echo $district['total_ac']; ?>
                </span>
                <?php if (!empty($panchayatSummary['total_panchayats'])): ?>
                    <a href="<?php echo getPanchayatUrl($district['slug']); ?>" class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 text-decoration-none">
                        🌾 <?php echo $panchayatSummary['total_panchayats']; ?> Gram Panchayats (Mukhiya & Sarpanch) &rarr;
                    </a>
                <?php endif; ?>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                <?php echo htmlspecialchars($district['name']); ?> District Election Hub
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                <?php echo htmlspecialchars($district['description']); ?>
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp"></i> Join <?php echo htmlspecialchars($district['name']); ?> WhatsApp Channel
                </a>
                <a href="advertise.php?sponsor_district=<?php echo $district['slug']; ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-megaphone"></i> List Business in <?php echo htmlspecialchars($district['name']); ?> (₹1,999/yr)
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <main class="container my-4 my-lg-5">

        <!-- District Switcher Card -->
        <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
            <label class="form-label fw-bold small text-muted text-uppercase mb-2">Switch Bihar District Hub:</label>
            <select class="form-select form-select-lg" onchange="window.location.href=this.value" style="font-size: 1rem;">
                <?php foreach ($allDistricts as $d): ?>
                    <option value="<?php echo getDistrictUrl($d['slug']); ?>" <?php echo $d['slug'] == $district['slug'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($d['name']); ?> — <?php echo $d['total_ac']; ?> Assembly Seats [HQ: <?php echo htmlspecialchars($d['headquarters']); ?>]
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Top Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Assembly Constituencies inside this District -->
        <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
            <div>
                <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                    Assembly Seats in <?php echo htmlspecialchars($district['name']); ?> District (<?php echo $district['total_ac']; ?> Seats)
                </h2>
                <p class="small text-muted mb-0">Electoral statistics, incumbent MLA data, and historical voting profiles</p>
            </div>
        </div>

        <div class="row g-3 g-lg-4 mb-5">
            <?php if (!empty($district['ac_list'])): ?>
                <?php foreach ($district['ac_list'] as $acItem): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="ac-no-badge">AC #<?php echo $acItem['ac_no']; ?></span>
                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($district['name']); ?></span>
                                </div>
                                <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">
                                    <?php echo htmlspecialchars($acItem['name']); ?> 
                                    <span class="small text-muted fw-normal">(<?php echo htmlspecialchars($acItem['name_hi']); ?>)</span>
                                </h3>
                                <p class="small text-muted mb-3">
                                    Current MLA: <strong><?php echo htmlspecialchars($acItem['current_mla']); ?></strong>
                                </p>
                            </div>
                            <a href="<?php echo getMlaUrl($acItem); ?>" class="btn btn-primary btn-sm w-100 fw-bold py-2" style="background: var(--secondary-navy); border: none;">
                                View Seat Profile & Data &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm p-4 text-center text-muted">
                        Constituency profiling for this district is being synchronized.
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Census 2011 Demographics & Social Matrix -->
        <?php if (!empty($cTot)): ?>
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Primary Census Abstract</span>
                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold px-2 py-1">Census 2011</span>
                    </div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        📊 <?php echo htmlspecialchars($district['name']); ?> Population Demographics & Social Matrix
                    </h2>
                    <p class="small text-muted mb-0">Official Government of India Census 2011 demographic data, sex ratio, literacy, and sub-district profile.</p>
                </div>
            </div>

            <!-- 4 Metric Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card border-0 bg-light p-3 rounded-3 h-100 border-start border-4 border-primary">
                        <span class="small text-muted text-uppercase fw-bold">Total Population</span>
                        <h3 class="h4 fw-bold text-dark mb-1"><?php echo number_format($cTot['population'] ?? 0); ?></h3>
                        <div class="small text-muted">
                            👨 <?php echo number_format($cTot['male'] ?? ($cTot['male_population'] ?? 0)); ?> | 👩 <?php echo number_format($cTot['female'] ?? ($cTot['female_population'] ?? 0)); ?>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 bg-light p-3 rounded-3 h-100 border-start border-4 border-success">
                        <span class="small text-muted text-uppercase fw-bold">Sex Ratio</span>
                        <h3 class="h4 fw-bold text-success mb-1"><?php echo $cTot['sex_ratio'] ?? 0; ?></h3>
                        <div class="small text-muted">
                            Females per 1000 Males (Bihar Avg: 918)
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 bg-light p-3 rounded-3 h-100 border-start border-4 border-warning">
                        <span class="small text-muted text-uppercase fw-bold">Literacy Rate</span>
                        <h3 class="h4 fw-bold text-warning mb-1"><?php echo $cTot['literacy_rate'] ?? 0; ?>%</h3>
                        <div class="small text-muted">
                            <?php echo number_format($cTot['literates'] ?? ($cTot['literates_total'] ?? 0)); ?> Literate Citizens
                        </div>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <div class="card border-0 bg-light p-3 rounded-3 h-100 border-start border-4 border-danger">
                        <span class="small text-muted text-uppercase fw-bold">Total Households</span>
                        <h3 class="h4 fw-bold text-dark mb-1"><?php echo number_format($cTot['households'] ?? 0); ?></h3>
                        <div class="small text-muted">
                            Across <?php echo count($subDistricts); ?> Sub-Districts / Blocks
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Distribution & Workforce Breakdown -->
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-pie-chart text-primary me-2"></i> Social & Urban-Rural Profile</h6>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Rural Population (<?php echo number_format($cRur['population'] ?? 0); ?>)</span>
                                <strong><?php echo !empty($cTot['population']) ? round((($cRur['population'] ?? 0) / $cTot['population']) * 100, 1) : 0; ?>%</strong>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-success" style="width: <?php echo !empty($cTot['population']) ? round((($cRur['population'] ?? 0) / $cTot['population']) * 100, 1) : 0; ?>%"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between small mb-1">
                                <span>Urban Population (<?php echo number_format($cUrb['population'] ?? 0); ?>)</span>
                                <strong><?php echo !empty($cTot['population']) ? round((($cUrb['population'] ?? 0) / $cTot['population']) * 100, 1) : 0; ?>%</strong>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-info" style="width: <?php echo !empty($cTot['population']) ? round((($cUrb['population'] ?? 0) / $cTot['population']) * 100, 1) : 0; ?>%"></div>
                            </div>
                        </div>

                        <div class="row g-2 pt-2 border-top small text-muted">
                            <div class="col-6">
                                <span class="d-block text-uppercase fw-bold text-dark">SC Population</span>
                                <span><?php echo number_format($cTot['sc_population'] ?? 0); ?> (<?php echo $cTot['sc_percentage'] ?? 0; ?>%)</span>
                            </div>
                            <div class="col-6">
                                <span class="d-block text-uppercase fw-bold text-dark">ST Population</span>
                                <span><?php echo number_format($cTot['st_population'] ?? 0); ?> (<?php echo $cTot['st_percentage'] ?? 0; ?>%)</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="p-3 border rounded-3 bg-white h-100">
                        <h6 class="fw-bold mb-3 text-dark"><i class="bi bi-briefcase text-success me-2"></i> Economic & Workforce Matrix</h6>
                        <div class="row g-2 mb-3 small">
                            <div class="col-6">
                                <div class="bg-light p-2 rounded-2">
                                    <span class="text-muted d-block">Total Working Population</span>
                                    <strong class="text-dark fs-6"><?php echo number_format($cTot['total_workers'] ?? 0); ?></strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="bg-light p-2 rounded-2">
                                    <span class="text-muted d-block">Non-Workers</span>
                                    <strong class="text-dark fs-6"><?php echo number_format($cTot['non_workers'] ?? 0); ?></strong>
                                </div>
                            </div>
                        </div>
                        <div class="small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>🌾 Cultivators (कृषक)</span>
                                <strong><?php echo number_format($cTot['cultivators'] ?? 0); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span>🚜 Agricultural Labourers (खेतिहर मजदूर)</span>
                                <strong><?php echo number_format($cTot['agricultural_labourers'] ?? 0); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span>💼 Main Workers (मुख्य श्रमिक)</span>
                                <strong><?php echo number_format($cTot['main_workers'] ?? 0); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sub-District (Block) Breakdown Table -->
            <?php if (!empty($subDistricts)): ?>
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <h5 class="h6 fw-bold mb-0 text-dark">
                        🏘️ Sub-District & Block-Level Census Data (<?php echo count($subDistricts); ?> Blocks)
                    </h5>
                    <div class="input-group input-group-sm" style="max-width: 260px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="censusSubSearch" class="form-control border-start-0 bg-light" placeholder="Search block name...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" id="censusSubTable">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2">Sub-District / Block</th>
                                <th class="py-2 text-end">Households</th>
                                <th class="py-2 text-end">Population</th>
                                <th class="py-2 text-end">Male</th>
                                <th class="py-2 text-end">Female</th>
                                <th class="py-2 text-center">Sex Ratio</th>
                                <th class="py-2 text-center">Literacy %</th>
                                <th class="py-2 text-end">SC Pop.</th>
                                <th class="py-2 text-end">Total Workers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($subDistricts as $sb): ?>
                            <tr class="census-sub-row" data-name="<?php echo htmlspecialchars(strtolower($sb['sub_district'])); ?>">
                                <td class="fw-bold text-primary">
                                    <?php echo htmlspecialchars($sb['sub_district']); ?>
                                </td>
                                <td class="text-end"><?php echo number_format($sb['households'] ?? 0); ?></td>
                                <td class="text-end fw-bold"><?php echo number_format($sb['population'] ?? 0); ?></td>
                                <td class="text-end text-muted"><?php echo number_format($sb['male'] ?? 0); ?></td>
                                <td class="text-end text-muted"><?php echo number_format($sb['female'] ?? 0); ?></td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border"><?php echo $sb['sex_ratio'] ?? 0; ?></span>
                                </td>
                                <td class="text-center fw-bold text-success">
                                    <?php echo $sb['literacy_rate'] ?? 0; ?>%
                                </td>
                                <td class="text-end"><?php echo number_format($sb['sc_population'] ?? 0); ?></td>
                                <td class="text-end"><?php echo number_format($sb['total_workers'] ?? 0); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </section>
        <?php endif; ?>

        <!-- Zila Parishad Leadership & Territorial Constituencies -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">Panchayati Raj 2021–2026</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1"><?php echo count($zilaMembers); ?> Territorial Constituencies</span>
                    </div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        🏛️ <?php echo htmlspecialchars($district['name']); ?> Zila Parishad (जिला परिषद्) Directory
                    </h2>
                    <p class="small text-muted mb-0">Official roster of Chairperson (अध्यक्ष), Vice-Chairperson (उपाध्यक्ष), and elected territorial ward members.</p>
                </div>
                <div>
                    <a href="panchayat.php" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                        View All 38 Districts &rarr;
                    </a>
                </div>
            </div>

            <!-- Leadership Row (Chairperson & Vice-Chairperson) -->
            <div class="row g-3 mb-4">
                <?php 
                $ch = $zilaSummary['chairman'] ?? null;
                $vch = $zilaSummary['vice_chairman'] ?? null;
                ?>
                <!-- Chairperson Card -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-warning bg-light">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 mb-1">अध्यक्ष / Chairperson</span>
                                <h3 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                                    <?php echo !empty($ch['candidate_name']) ? htmlspecialchars($ch['candidate_name']) : 'N/A'; ?>
                                </h3>
                                <?php if (!empty($ch['father_husband_name'])): ?>
                                    <p class="small text-muted mb-0">S/o or W/o: <?php echo htmlspecialchars($ch['father_husband_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.3rem;">
                                👑
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top small text-muted">
                            <div class="d-flex flex-wrap gap-2 mb-1">
                                <?php if (!empty($ch['reservation'])): ?>
                                    <span class="badge bg-white text-dark border"><?php echo htmlspecialchars($ch['reservation']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($ch['category'])): ?>
                                    <span class="badge bg-white text-secondary border"><?php echo htmlspecialchars($ch['category']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($ch['gender_hi'])): ?>
                                    <span class="badge bg-white text-dark border"><?php echo htmlspecialchars($ch['gender_hi']); ?> (<?php echo $ch['age'] ?? ''; ?> yrs)</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($ch['address'])): ?>
                                <p class="mb-0 text-truncate"><i class="bi bi-geo-alt text-danger"></i> <?php echo htmlspecialchars($ch['address']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Vice Chairperson Card -->
                <div class="col-12 col-md-6">
                    <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-info bg-light">
                        <div class="d-flex align-items-start justify-content-between mb-2">
                            <div>
                                <span class="badge bg-info text-dark fw-bold px-2 py-1 mb-1">उपाध्यक्ष / Vice-Chairperson</span>
                                <h3 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                                    <?php echo !empty($vch['candidate_name']) ? htmlspecialchars($vch['candidate_name']) : 'N/A'; ?>
                                </h3>
                                <?php if (!empty($vch['father_husband_name'])): ?>
                                    <p class="small text-muted mb-0">S/o or W/o: <?php echo htmlspecialchars($vch['father_husband_name']); ?></p>
                                <?php endif; ?>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-25 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; font-size: 1.3rem;">
                                🎖️
                            </div>
                        </div>
                        <div class="mt-2 pt-2 border-top small text-muted">
                            <div class="d-flex flex-wrap gap-2 mb-1">
                                <?php if (!empty($vch['category'])): ?>
                                    <span class="badge bg-white text-secondary border"><?php echo htmlspecialchars($vch['category']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($vch['gender_hi'])): ?>
                                    <span class="badge bg-white text-dark border"><?php echo htmlspecialchars($vch['gender_hi']); ?> (<?php echo $vch['age'] ?? ''; ?> yrs)</span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($vch['address'])): ?>
                                <p class="mb-0 text-truncate"><i class="bi bi-geo-alt text-danger"></i> <?php echo htmlspecialchars($vch['address']); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Controls for District Members -->
            <div class="row g-2 mb-3">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="zilaMemberSearch" class="form-control border-start-0 bg-light" placeholder="Search by member name, block, ward no...">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select id="zilaGenderFilter" class="form-select bg-light">
                        <option value="">All Genders</option>
                        <option value="Female">महिला (Women)</option>
                        <option value="Male">पुरूष (Men)</option>
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <select id="zilaBlockFilter" class="form-select bg-light">
                        <option value="">All Blocks</option>
                        <?php 
                        $districtBlocks = array_unique(array_filter(array_column($zilaMembers, 'block')));
                        sort($districtBlocks);
                        foreach ($districtBlocks as $blk): ?>
                            <option value="<?php echo htmlspecialchars($blk); ?>"><?php echo htmlspecialchars($blk); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <!-- Members Table -->
            <?php if (!empty($zilaMembers)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small" id="districtZilaTable">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3">Ward / क्षे० सं०</th>
                                <th class="py-3">Block / प्रखंड</th>
                                <th class="py-3">Elected Member</th>
                                <th class="py-3">Gender / Category</th>
                                <th class="py-3">Reservation Status</th>
                                <th class="py-3">Contact / Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($zilaMembers as $m): 
                                $candName = $m['candidate_name'] ?? '';
                                $fatherName = $m['father_husband_name'] ?? '';
                                $blockName = $m['block'] ?? '';
                                $wardNo = $m['territory_no'] ?? '';
                                $gender = $m['gender'] ?? '';
                                $genderHi = !empty($m['gender_hi']) ? $m['gender_hi'] : $gender;
                                $age = $m['age'] ?? '';
                                $category = $m['category'] ?? '';
                                $reservation = !empty($m['reservation']) ? $m['reservation'] : 'General';
                                $mobile = $m['mobile'] ?? '';
                                $address = $m['address'] ?? '';
                            ?>
                                <tr class="zila-row" 
                                    data-name="<?php echo htmlspecialchars(strtolower(trim($candName . ' ' . $fatherName))); ?>"
                                    data-block="<?php echo htmlspecialchars($blockName); ?>"
                                    data-ward="<?php echo htmlspecialchars($wardNo); ?>"
                                    data-gender="<?php echo htmlspecialchars($gender); ?>">
                                    <td class="fw-bold text-center" style="width: 90px;">
                                        <span class="badge bg-secondary rounded-pill px-2 py-1">
                                            #<?php echo htmlspecialchars($wardNo); ?>
                                        </span>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <?php echo htmlspecialchars($blockName); ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary" style="font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($candName); ?>
                                        </div>
                                        <?php if (!empty($fatherName)): ?>
                                            <div class="text-muted small">W/o or S/o: <?php echo htmlspecialchars($fatherName); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="badge <?php echo $gender === 'Female' ? 'bg-danger bg-opacity-10 text-danger' : 'bg-primary bg-opacity-10 text-primary'; ?> fw-semibold">
                                                <?php echo htmlspecialchars($genderHi); ?> <?php echo !empty($age) ? "({$age} yrs)" : ''; ?>
                                            </span>
                                        </div>
                                        <?php if (!empty($category)): ?>
                                            <div class="text-muted small mt-1"><?php echo htmlspecialchars($category); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?php echo htmlspecialchars($reservation); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($mobile)): ?>
                                            <span class="badge bg-light text-secondary border py-1 px-2 fw-semibold mb-1 d-inline-flex align-items-center gap-1" title="Contact Protected">
                                                <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($mobile)); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($address)): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 220px;" title="<?php echo htmlspecialchars($address); ?>">
                                                <?php echo htmlspecialchars($address); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    No territorial member records available for this district.
                </div>
            <?php endif; ?>
        </section>

        <!-- 2016 Panchayat & Block Samiti Historical Archive -->
        <?php if (!empty($samiti2016) || !empty($zila2016)): ?>
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-secondary text-white fw-bold px-2 py-1">Historical Archive</span>
                        <span class="badge bg-warning text-dark fw-bold px-2 py-1">2016 Election</span>
                    </div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        ⏳ 2016 <?php echo htmlspecialchars($district['name']); ?> Block Panchayat Samiti & Mukhiya Archive
                    </h2>
                    <p class="small text-muted mb-0">Elected Block Pramukhs, Up-Pramukhs, and <?php echo count($mukhiyas2016); ?> Gram Panchayat Mukhiyas from 2016 General Panchayat Elections</p>
                </div>
                <a href="panchayat.php?tab=archive2016&district=<?php echo $district['slug']; ?>" class="btn btn-outline-secondary btn-sm fw-bold rounded-pill px-3">
                    View All <?php echo count($mukhiyas2016); ?> Mukhiyas (2016) &rarr;
                </a>
            </div>

            <?php if (!empty($zila2016[0]['adhyaksh_2016']) || !empty($zila2016[0]['upadhyaksh_2016'])): ?>
                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 bg-light border-start border-4 border-warning">
                            <div class="small text-muted fw-semibold">2016 Zila Parishad Adhyaksh (अध्यक्ष)</div>
                            <div class="h6 fw-bold text-navy mb-0 mt-1">
                                <?php echo htmlspecialchars($zila2016[0]['adhyaksh_2016'] ?: 'Not Disclosed'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="p-3 rounded-3 bg-light border-start border-4 border-primary">
                            <div class="small text-muted fw-semibold">2016 Zila Parishad Upadhyaksh (उपाध्यक्ष)</div>
                            <div class="h6 fw-bold text-navy mb-0 mt-1">
                                <?php echo htmlspecialchars($zila2016[0]['upadhyaksh_2016'] ?: 'Not Disclosed'); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($samiti2016)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th class="py-2">#</th>
                                <th class="py-2">Block (प्रखंड)</th>
                                <th class="py-2">Pramukh 2016 (प्रमुख पदधारक)</th>
                                <th class="py-2">Up-Pramukh 2016 (उप प्रमुख पदधारक)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $bIndex = 1; foreach ($samiti2016 as $bItem): ?>
                                <tr>
                                    <td class="text-muted fw-bold"><?php echo $bIndex++; ?></td>
                                    <td class="fw-bold" style="color: var(--primary-navy);">
                                        <?php echo htmlspecialchars($bItem['block_hi'] ?: $bItem['block']); ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-success">
                                            <i class="bi bi-person-check-fill me-1"></i>
                                            <?php echo htmlspecialchars($bItem['pramukh_2016'] ?: 'Not Disclosed'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">
                                            <?php echo htmlspecialchars($bItem['up_pramukh_2016'] ?: 'Not Disclosed'); ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- District Local Business Directory -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                        🏢 <?php echo htmlspecialchars($district['name']); ?> Local Services & Business Directory
                    </h2>
                    <p class="small text-muted mb-0">Legal counsels, coaching academies, medical clinics, and election campaign vendors</p>
                </div>
                <a href="advertise.php?sponsor_district=<?php echo $district['slug']; ?>" class="btn btn-warning btn-sm fw-bold text-dark rounded-pill px-3 shadow-sm">
                    + List Your Business (₹1,999/yr)
                </a>
            </div>

            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="card border-0 bg-light p-3 rounded-3 text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 mb-2">⚖️</div>
                            <h3 class="h6 fw-bold mb-1" style="color: var(--primary-navy);">Advocates & Legal Directory</h3>
                            <p class="small text-muted mb-3">Civil court, land revenue, and election law consultants</p>
                        </div>
                        <a href="advertise.php" class="small fw-bold text-decoration-none" style="color: var(--accent-saffron);">Book Listing Slot &rarr;</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 bg-light p-3 rounded-3 text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 mb-2">🎓</div>
                            <h3 class="h6 fw-bold mb-1" style="color: var(--primary-navy);">Coaching & Academies</h3>
                            <p class="small text-muted mb-3">BPSC, SSC, Railway, NEET, and IIT-JEE coaching institutes</p>
                        </div>
                        <a href="advertise.php" class="small fw-bold text-decoration-none" style="color: var(--accent-saffron);">Book Listing Slot &rarr;</a>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card border-0 bg-light p-3 rounded-3 text-center h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="fs-1 mb-2">🖨️</div>
                            <h3 class="h6 fw-bold mb-1" style="color: var(--primary-navy);">Printing & Campaign Vendors</h3>
                            <p class="small text-muted mb-3">Flex banners, hoardings, audio systems, and digital PR agencies</p>
                        </div>
                        <a href="advertise.php" class="small fw-bold text-decoration-none" style="color: var(--accent-saffron);">Book Listing Slot &rarr;</a>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('zilaMemberSearch');
        const genderFilter = document.getElementById('zilaGenderFilter');
        const blockFilter = document.getElementById('zilaBlockFilter');
        const rows = document.querySelectorAll('#districtZilaTable .zila-row');

        function filterZilaRows() {
            const query = (searchInput?.value || '').toLowerCase().trim();
            const selectedGender = genderFilter?.value || '';
            const selectedBlock = (blockFilter?.value || '').toLowerCase().trim();

            rows.forEach(row => {
                const name = (row.getAttribute('data-name') || '').toLowerCase();
                const block = (row.getAttribute('data-block') || '').toLowerCase();
                const ward = (row.getAttribute('data-ward') || '').toLowerCase();
                const gender = row.getAttribute('data-gender') || '';

                const matchesQuery = !query || name.includes(query) || block.includes(query) || ward.includes(query);
                const matchesGender = !selectedGender || gender === selectedGender;
                const matchesBlock = !selectedBlock || block === selectedBlock;

                if (matchesQuery && matchesGender && matchesBlock) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        if (searchInput) searchInput.addEventListener('input', filterZilaRows);
        if (genderFilter) genderFilter.addEventListener('change', filterZilaRows);
        if (blockFilter) blockFilter.addEventListener('change', filterZilaRows);

        // Census Sub-District Search
        const censusSearch = document.getElementById('censusSubSearch');
        const censusRows = document.querySelectorAll('#censusSubTable .census-sub-row');
        if (censusSearch) {
            censusSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                censusRows.forEach(r => {
                    const name = (r.getAttribute('data-name') || '').toLowerCase();
                    r.style.display = (!q || name.includes(q)) ? '' : 'none';
                });
            });
        }
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
