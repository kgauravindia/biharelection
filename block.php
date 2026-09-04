<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();
$pdo = Database::getConnection();

$slug = $_GET['slug'] ?? '';
$districtInput = $_GET['district'] ?? '';
$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrictSlug = $districtObj['slug'] ?? $districtInput;

$block = null;
$panchayatsInBlock = [];
$allSubdistricts = [];

// Fetch all subdistricts/blocks for filtering
if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM census_subdistricts ORDER BY district_name ASC, sub_district ASC");
        $allSubdistricts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
}

// Find single block if requested
if (!empty($slug)) {
    $sNeedle = slugify($slug);
    $sRaw = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '', $slug)));
    
    foreach ($allSubdistricts as $sd) {
        $sdSlug = slugify($sd['sub_district']);
        $sdRaw = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '', $sd['sub_district'])));
        $distMatch = empty($selectedDistrictSlug) || strtolower($sd['district_slug']) === strtolower($selectedDistrictSlug);

        if ($distMatch && ($sdSlug === $sNeedle || $sdRaw === $sRaw || strcasecmp($sd['sub_district'], $slug) === 0)) {
            $block = $sd;
            break;
        }
    }

    // Fallback: search without district filter if not found
    if (!$block && !empty($selectedDistrictSlug)) {
        foreach ($allSubdistricts as $sd) {
            $sdSlug = slugify($sd['sub_district']);
            $sdRaw = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '', $sd['sub_district'])));
            if ($sdSlug === $sNeedle || $sdRaw === $sRaw || strcasecmp($sd['sub_district'], $slug) === 0) {
                $block = $sd;
                break;
            }
        }
    }
}

// Helper function for fuzzy and phonetic block matching across Hindi/English transliterations
if (!function_exists('isBlockMatch')) {
    function isBlockMatch($b1, $b2) {
        if (empty($b1) || empty($b2)) return false;
        $s1 = str_replace('ph', 'f', slugify($b1));
        $s2 = str_replace('ph', 'f', slugify($b2));
        if ($s1 === $s2) return true;
        if (strcasecmp($b1, $b2) === 0) return true;
        if (strpos($s1, $s2) !== false || strpos($s2, $s1) !== false) return true;
        
        // Remove vowels to match consonant skeletal phonetics (e.g. athamalagola vs athmalgola, fulelapura vs fulelpur)
        $c1 = preg_replace('/[aeiou\-_]+/', '', $s1);
        $c2 = preg_replace('/[aeiou\-_]+/', '', $s2);
        if ($c1 === $c2 && strlen($c1) >= 3) return true;
        if (strlen($c1) >= 3 && strlen($c2) >= 3 && (strpos($c1, $c2) !== false || strpos($c2, $c1) !== false)) return true;
        
        // Check hyphenated parts (e.g. Dinapur in Dinapur-Cum-Khagaul)
        $parts1 = explode('-', $s1);
        $parts2 = explode('-', $s2);
        foreach ($parts1 as $p1) {
            foreach ($parts2 as $p2) {
                if (strlen($p1) >= 3 && strlen($p2) >= 3) {
                    if ($p1 === $p2 || strpos($p1, $p2) !== false || strpos($p2, $p1) !== false) return true;
                    $cp1 = preg_replace('/[aeiou\-_]+/', '', $p1);
                    $cp2 = preg_replace('/[aeiou\-_]+/', '', $p2);
                    if ($cp1 === $cp2 && strlen($cp1) >= 3) return true;
                }
            }
        }

        // Levenshtein on slugs
        if (levenshtein($s1, $s2) <= 2) return true;
        
        return false;
    }
}

// If block found, fetch its Gram Panchayats, Samiti Pramukh, and Zila Parishad Members
$blockSamitiPramukh = null;
$zilaParishadRep = [];

if ($block) {
    $bName = $block['sub_district'];
    $bDistSlug = strtolower($block['district_slug']);
    
    // Fetch Panchayats in this Block from primary panchayats table
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM panchayats WHERE district_slug = :dslug ORDER BY panchayat_name ASC");
            $stmt->execute([':dslug' => $bDistSlug]);
            $allDistPanchayats = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($allDistPanchayats as $p) {
                if (isBlockMatch($p['block'], $bName)) {
                    $panchayatsInBlock[] = $p;
                }
            }
        } catch (Throwable $e) {}

        // Fallback: Check mukhiyas_2016 if additional panchayats exist
        try {
            $stmt16 = $pdo->prepare("SELECT * FROM mukhiyas_2016 WHERE district_slug = :dslug ORDER BY panchayat ASC");
            $stmt16->execute([':dslug' => $bDistSlug]);
            $rows16 = $stmt16->fetchAll(PDO::FETCH_ASSOC);

            foreach ($rows16 as $r16) {
                if (isBlockMatch($r16['block'], $bName)) {
                    $alreadyExists = false;
                    foreach ($panchayatsInBlock as $pExisting) {
                        if (isBlockMatch($r16['panchayat'], $pExisting['panchayat_name'])) {
                            $alreadyExists = true;
                            break;
                        }
                    }

                    if (!$alreadyExists) {
                        $panchayatsInBlock[] = [
                            'id' => $r16['id'],
                            'district' => $r16['district'],
                            'district_slug' => $r16['district_slug'],
                            'block' => $r16['block'],
                            'panchayat_name' => $r16['panchayat'],
                            'current_mukhiya' => $r16['mukhiya_2016'] ?? '',
                            'current_sarpanch' => '',
                            'reservation_2026_mukhiya' => '',
                            'reservation_2026_sarpanch' => '',
                        ];
                    }
                }
            }
        } catch (Throwable $e) {}

        // Fetch Block Samiti Pramukh
        try {
            $stmt = $pdo->prepare("SELECT * FROM panchayat_samiti_2016 WHERE district_slug = :dslug ORDER BY id ASC");
            $stmt->execute([':dslug' => $bDistSlug]);
            $samitiRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($samitiRows as $sr) {
                if (isBlockMatch($sr['block'], $bName)) {
                    $blockSamitiPramukh = $sr;
                    break;
                }
            }
        } catch (Throwable $e) {}

        // Fetch Zila Parishad Member for this district
        try {
            $stmt = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE district_slug = :dslug ORDER BY ward_no ASC");
            $stmt->execute([':dslug' => $bDistSlug]);
            $zilaRows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach ($zilaRows as $zr) {
                if (isBlockMatch($zr['block'] ?? '', $bName)) {
                    $zilaParishadRep[] = $zr;
                }
            }
        } catch (Throwable $e) {}
    }

    $popTotal = intval($block['population'] ?? 0);
    $popMale = intval($block['male'] ?? 0);
    $popFemale = intval($block['female'] ?? 0);
    $households = intval($block['households'] ?? 0);
    $literates = intval($block['literates'] ?? 0);
    $litRate = floatval($block['literacy_rate'] ?? 0);
    $totWork = intval($block['total_workers'] ?? 0);
    $cultivators = intval($block['cultivators'] ?? 0);
    $agLabourers = intval($block['agricultural_labourers'] ?? 0);

    $pageTitle = "{$block['sub_district']} Block ({$block['district_name']} District): Gram Panchayats, Mukhiya & Census Data";
    $pageDescription = "Official directory of {$block['sub_district']} Block in {$block['district_name']} District, Bihar. Explore " . count($panchayatsInBlock) . " Gram Panchayats, elected Mukhiyas, Sarpanchs, Census demographics (Pop: " . number_format($popTotal) . ") & local governance.";
    $pageCanonical = getBlockUrl($bDistSlug, slugify($bName));
} else {
    $distLabel = $districtObj ? "in {$districtObj['name']} District" : "Across 38 Districts of Bihar";
    $pageTitle = "Bihar CD Blocks Directory {$distLabel}: 534 Sub-Districts & Block Samitis";
    $pageDescription = "Official directory of all 534 Community Development (CD) Blocks across 38 districts of Bihar. Explore Block Samitis, Pramukhs, demographics, and Gram Panchayats.";
    $pageCanonical = $selectedDistrictSlug ? SITE_URL . "/blocks?district={$selectedDistrictSlug}" : SITE_URL . "/blocks";
}

$activeNav = 'blocks';
require_once __DIR__ . '/header.php';
?>

<?php if ($block): ?>
    <!-- Single Block Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    <i class="bi bi-patch-check-fill me-1"></i> CD Block / Sub-District
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🏢 <?php echo htmlspecialchars($block['district_name']); ?> District
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🌾 <?php echo count($panchayatsInBlock); ?> Gram Panchayats
                </span>
            </div>

            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/blocks" class="text-white-50 text-decoration-none">Blocks</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getDistrictUrl($bDistSlug); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($block['district_name']); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($block['sub_district']); ?></li>
                </ol>
            </nav>

            <h1 class="display-5 fw-extrabold text-white mb-2">
                <?php echo htmlspecialchars($block['sub_district']); ?> Block
            </h1>
            <p class="lead text-white-50 mb-4" style="max-width: 800px; font-size: 1.05rem;">
                Sub-district Code: <strong><?php echo htmlspecialchars($block['sub_dist_code'] ?? 'N/A'); ?></strong> &bull; 
                Total Population: <strong><?php echo number_format($popTotal); ?></strong> &bull; 
                Literacy Rate: <strong><?php echo $litRate; ?>%</strong> &bull; 
                Households: <strong><?php echo number_format($households); ?></strong>
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="#gramPanchayatsSection" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building-check me-1"></i> View <?php echo count($panchayatsInBlock); ?> Gram Panchayats
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($bDistSlug, slugify($block['sub_district'])); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> Block Samiti Pramukh
                </a>
                <a href="<?php echo getZilaParishadUrl($bDistSlug); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-bank me-1"></i> Zila Parishad
                </a>
                <a href="<?php echo getCensusUrl($bDistSlug, slugify($block['sub_district'])); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bar-chart-fill me-1"></i> Full Census Demographics
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container my-4 my-lg-5">
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Key Demographic Summary Cards -->
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Population</small>
                        <i class="bi bi-people-fill text-primary fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark"><?php echo number_format($popTotal); ?></div>
                    <div class="small text-muted mt-1">Male: <?php echo number_format($popMale); ?> | Female: <?php echo number_format($popFemale); ?></div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 bg-white border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Gram Panchayats</small>
                        <i class="bi bi-building-check text-success fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark"><?php echo count($panchayatsInBlock); ?></div>
                    <div class="small text-muted mt-1">Under <?php echo htmlspecialchars($block['sub_district']); ?> Block</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Literacy Rate</small>
                        <i class="bi bi-book-fill text-warning fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark"><?php echo $litRate; ?>%</div>
                    <div class="small text-muted mt-1"><?php echo number_format($literates); ?> Literate Persons</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 bg-white border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Total Workers</small>
                        <i class="bi bi-briefcase-fill text-info fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark"><?php echo number_format($totWork); ?></div>
                    <div class="small text-muted mt-1">Cultivators: <?php echo number_format($cultivators); ?></div>
                </div>
            </div>
        </div>

        <!-- Block Samiti & Zila Parishad Representation Section -->
        <?php if ($blockSamitiPramukh || !empty($zilaParishadRep)): ?>
            <div class="row g-4 mb-4">
                <?php if ($blockSamitiPramukh): ?>
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100 border-top border-4 border-primary">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-primary text-white rounded-pill px-2 py-1">Tier 2</span>
                                <h5 class="fw-bold text-navy mb-0">Block Samiti Leadership (प्रखंड प्रमुख)</h5>
                            </div>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                    <i class="bi bi-award-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="fw-bold fs-5 text-dark"><?php echo htmlspecialchars($blockSamitiPramukh['pramukh_2016'] ?: 'Pramukh Appointed'); ?></div>
                                    <small class="text-muted">Block Pramukh (प्रखंड प्रमुख) &bull; <?php echo htmlspecialchars($block['sub_district']); ?></small>
                                </div>
                            </div>
                            <?php if (!empty($blockSamitiPramukh['up_pramukh_2016'])): ?>
                                <div class="small text-muted mt-2 border-top pt-2">
                                    <strong>Up-Pramukh (उप प्रमुख):</strong> <?php echo htmlspecialchars($blockSamitiPramukh['up_pramukh_2016']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($zilaParishadRep)): ?>
                    <div class="col-12 col-md-6">
                        <div class="card border-0 shadow-sm rounded-3 p-4 bg-white h-100 border-top border-4 border-warning">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge bg-warning text-dark rounded-pill px-2 py-1">Tier 3</span>
                                <h5 class="fw-bold text-navy mb-0">Zila Parishad Territorial Members</h5>
                            </div>
                            <?php foreach ($zilaParishadRep as $zm): ?>
                                <div class="d-flex align-items-center gap-3 mb-2">
                                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                        <i class="bi bi-bank fs-4"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($zm['member_name']); ?></div>
                                        <small class="text-muted">Ward No. <?php echo htmlspecialchars($zm['ward_no']); ?> &bull; <?php echo htmlspecialchars($zm['category'] ?? 'General'); ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Gram Panchayats Grid Section -->
        <div id="gramPanchayatsSection" class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-building-check text-success me-2"></i> Gram Panchayats under <?php echo htmlspecialchars($block['sub_district']); ?> Block
                    </h5>
                    <small class="text-muted">Explore elected Mukhiyas, Sarpanchs, wards, and village demographics</small>
                </div>
                <div class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">
                    <?php echo count($panchayatsInBlock); ?> Panchayats
                </div>
            </div>
            <div class="card-body p-4">
                <?php if (!empty($panchayatsInBlock)): ?>
                    <div class="row g-3">
                        <?php foreach ($panchayatsInBlock as $p): 
                            $pSlug = slugify($p['panchayat_name']);
                            $pUrl = getPanchayatUrl($bDistSlug, slugify($block['sub_district']), $pSlug);
                        ?>
                            <div class="col-12 col-md-6 col-lg-4">
                                <div class="card h-100 border shadow-none rounded-3 p-3 hover-shadow transition-all bg-light d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="fw-bold mb-0">
                                                <a href="<?php echo htmlspecialchars($pUrl); ?>" class="text-decoration-none text-navy">
                                                    🌾 <?php echo htmlspecialchars($p['panchayat_name']); ?>
                                                </a>
                                            </h6>
                                            <?php if (!empty($p['reservation_2026_mukhiya'])): ?>
                                                <span class="badge bg-white text-dark border small px-2 py-1" style="font-size: 0.72rem;">
                                                    <?php echo htmlspecialchars($p['reservation_2026_mukhiya']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="small text-muted mb-2">
                                            <div><strong>Mukhiya:</strong> <span class="text-success fw-semibold"><?php echo htmlspecialchars($p['current_mukhiya'] ?: 'Details Available'); ?></span></div>
                                            <?php if (!empty($p['current_sarpanch'])): ?>
                                                <div><strong>Sarpanch:</strong> <span class="text-primary fw-semibold"><?php echo htmlspecialchars($p['current_sarpanch']); ?></span></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>

                                    <div class="mt-auto pt-2 border-top d-flex justify-content-between align-items-center small">
                                        <span class="text-muted">Gram Panchayat</span>
                                        <a href="<?php echo htmlspecialchars($pUrl); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-bold">
                                            View Panchayat &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-muted mb-2">No individual panchayat entries mapped directly yet for this block in the database.</p>
                        <a href="<?php echo getPanchayatUrl($bDistSlug); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                            Browse All <?php echo htmlspecialchars($block['district_name']); ?> Panchayats
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
    </main>

<?php else: ?>
    <!-- All 534 Blocks Directory View -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🏢 Geographic & Administrative Directory
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    534 CD Blocks
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    38 Districts
                </span>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Bihar CD Blocks Directory (534 Sub-Districts) <br>
                <span style="color: var(--accent-saffron);"><?php echo htmlspecialchars($distLabel); ?></span>
            </h1>
            <p class="lead text-white-50 mb-4" style="max-width: 800px; font-size: 1.05rem;">
                Official directory of all 534 Community Development (CD) Blocks across 38 districts of Bihar. Explore Block Samitis, Pramukhs, Census 2011 demographics, and Gram Panchayats.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building-check me-1"></i> Explore All Gram Panchayats
                </a>
                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-award me-1"></i> Block Samiti Pramukhs
                </a>
                <a href="<?php echo SITE_URL; ?>/census" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-bar-chart-fill me-1"></i> Bihar Census Hub
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container my-4 my-lg-5">
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- District Filter Bar & Live Search -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-5 col-lg-4">
                    <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-funnel-fill text-primary me-1"></i> Filter by District:</label>
                    <select id="districtFilterSelect" class="form-select rounded-pill" onchange="if(this.value){ location.href = this.value; }">
                        <option value="<?php echo SITE_URL; ?>/blocks">All 38 Districts (534 Blocks)</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo SITE_URL; ?>/blocks?district=<?php echo urlencode($d['slug']); ?>" <?php echo $selectedDistrictSlug === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?> District
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-7 col-lg-6">
                    <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-search text-primary me-1"></i> Search Block:</label>
                    <div class="input-group">
                        <input type="text" id="blockSearchInput" class="form-control rounded-start-pill ps-3" placeholder="Search block by name, district, or PIN/Code..." autocomplete="off" onkeyup="filterBlocks()">
                        <button type="button" class="btn btn-warning rounded-end-pill px-3 fw-bold text-dark" onclick="filterBlocks()">
                            <i class="bi bi-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Counter Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-2 rounded-pill uppercase tracking-wider small">Administrative Roster</span>
                <h2 class="fw-bold font-heading text-navy mt-2 fs-3 mb-0">Community Development Blocks</h2>
            </div>
            <?php 
            $filteredSubdistricts = $allSubdistricts;
            if (!empty($selectedDistrictSlug)) {
                $filteredSubdistricts = array_values(array_filter($allSubdistricts, function($sd) use ($selectedDistrictSlug) {
                    return strtolower($sd['district_slug']) === strtolower($selectedDistrictSlug);
                }));
            }

            // Pagination when viewing all blocks
            $page = max(1, intval($_GET['page'] ?? 1));
            $perPage = !empty($selectedDistrictSlug) ? 100 : 21; // show all in district, or 21 per page for all
            $totalFiltered = count($filteredSubdistricts);
            $totalPages = max(1, ceil($totalFiltered / $perPage));
            $page = min($page, $totalPages);
            $offset = ($page - 1) * $perPage;
            $displaySubdistricts = array_slice($filteredSubdistricts, $offset, $perPage);
            ?>
            <div class="badge bg-primary text-white fw-bold fs-6 px-3 py-2 rounded-pill shadow-sm" id="blockCountBadge">
                Showing <?php echo count($displaySubdistricts); ?> of <?php echo $totalFiltered; ?> Blocks <?php echo $totalPages > 1 ? "(Page {$page}/{$totalPages})" : ""; ?>
            </div>
        </div>

        <!-- Blocks Grid -->
        <div class="row g-3 g-lg-4" id="blocksGrid">
            <?php 
            foreach ($displaySubdistricts as $sd): 
                $bSlug = slugify($sd['sub_district']);
                $dSlug = strtolower($sd['district_slug']);
                $bUrl = getBlockUrl($dSlug, $bSlug);
                $bSearchStr = strtolower(($sd['sub_district'] ?? '') . ' ' . ($sd['district_name'] ?? '') . ' ' . ($sd['sub_dist_code'] ?? ''));
            ?>
                <div class="col-12 col-md-6 col-lg-4 block-card-item" data-search="<?php echo htmlspecialchars($bSearchStr, ENT_QUOTES); ?>">
                    <div class="card h-100 border-0 shadow-sm rounded-4 p-4 bg-white hover-shadow transition-all border-start border-4 border-warning d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-warning bg-opacity-15 text-dark fw-bold px-2 py-1 rounded small mb-1">
                                        🏢 <?php echo htmlspecialchars($sd['district_name']); ?> District
                                    </span>
                                    <h4 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($bUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                            <?php echo htmlspecialchars($sd['sub_district']); ?> Block
                                        </a>
                                    </h4>
                                </div>
                                <span class="badge bg-light text-muted border small px-2 py-1">
                                    Code: <?php echo htmlspecialchars($sd['sub_dist_code']); ?>
                                </span>
                            </div>

                            <!-- Census 2011 Summary Badges -->
                            <div class="bg-light rounded-3 p-3 my-3 border border-light small">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="text-muted"><i class="bi bi-people-fill me-1 text-primary"></i> Population:</span>
                                    <span class="fw-bold text-dark"><?php echo number_format(intval($sd['population'])); ?></span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <span class="text-muted"><i class="bi bi-book-half me-1 text-warning"></i> Literacy Rate:</span>
                                    <span class="fw-bold text-dark"><?php echo floatval($sd['literacy_rate']); ?>%</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted"><i class="bi bi-house-door-fill me-1 text-secondary"></i> Households:</span>
                                    <span class="fw-bold text-dark"><?php echo number_format(intval($sd['households'])); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                            <a href="<?php echo htmlspecialchars($bUrl); ?>#gramPanchayatsSection" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-3 py-1.5">
                                Panchayats
                            </a>
                            <a href="<?php echo htmlspecialchars($bUrl); ?>" class="btn btn-sm btn-primary rounded-pill fw-semibold px-3 py-1.5 shadow-sm">
                                View Block <i class="bi bi-chevron-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
            <nav aria-label="Blocks Page Navigation" class="my-4">
                <ul class="pagination justify-content-center flex-wrap gap-1">
                    <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link rounded-pill px-3 shadow-none fw-semibold" href="<?php echo SITE_URL; ?>/blocks?page=<?php echo ($page - 1); ?><?php echo !empty($selectedDistrictSlug) ? '&district=' . urlencode($selectedDistrictSlug) : ''; ?>">
                                &laquo; Prev
                            </a>
                        </li>
                    <?php endif; ?>

                    <?php 
                    $startP = max(1, $page - 3);
                    $endP = min($totalPages, $page + 3);
                    for ($p = $startP; $p <= $endP; $p++): 
                    ?>
                        <li class="page-item <?php echo $p === $page ? 'active' : ''; ?>">
                            <a class="page-link rounded-pill px-3 shadow-none fw-semibold" href="<?php echo SITE_URL; ?>/blocks?page=<?php echo $p; ?><?php echo !empty($selectedDistrictSlug) ? '&district=' . urlencode($selectedDistrictSlug) : ''; ?>">
                                <?php echo $p; ?>
                            </a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <li class="page-item">
                            <a class="page-link rounded-pill px-3 shadow-none fw-semibold" href="<?php echo SITE_URL; ?>/blocks?page=<?php echo ($page + 1); ?><?php echo !empty($selectedDistrictSlug) ? '&district=' . urlencode($selectedDistrictSlug) : ''; ?>">
                                Next &raquo;
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>

        <div id="noBlocksAlert" class="alert alert-info rounded-4 text-center py-5 d-none mt-4 shadow-sm">
            <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">No Block found</h5>
            <p class="text-muted mb-3">Try searching for another block name or district.</p>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="clearBlockSearch()">Clear Search</button>
        </div>

        <script>
        function filterBlocks() {
            const query = document.getElementById('blockSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.block-card-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemSearch = item.getAttribute('data-search').toLowerCase();
                if (query === '' || itemSearch.includes(query)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            const badge = document.getElementById('blockCountBadge');
            if (badge) {
                badge.innerText = 'Showing ' + visibleCount + ' Blocks';
            }
            
            const noResults = document.getElementById('noBlocksAlert');
            if (noResults) {
                if (visibleCount === 0) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            }
        }

        function clearBlockSearch() {
            document.getElementById('blockSearchInput').value = '';
            filterBlocks();
        }
        </script>

        <!-- Official Data Sources Attribution Banner -->
        <section class="mt-5 pt-4 border-top">
            <div class="p-4 rounded-4 bg-light border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="bi bi-shield-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-dark font-heading mb-1">Standardized Public & Government Data Sources</h6>
                        <p class="text-muted small mb-0">Local Block boundaries, Gram Panchayat lists, Census 2011 stats, and administrative roster reference Census of India, LGD Portal, and SEC Bihar.</p>
                    </div>
                </div>
                <a href="<?php echo SITE_URL; ?>/census" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold text-nowrap">
                    <i class="bi bi-bar-chart-line me-1"></i>View Census Directory
                </a>
            </div>
        </section>

        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
    </main>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
