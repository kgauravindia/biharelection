<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();
$pdo = Database::getConnection();

$districtInput = $_GET['district'] ?? '';
$wardInput = isset($_GET['ward']) ? trim((string)$_GET['ward']) : '';
$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrict = $districtObj['slug'] ?? ($districtInput ? strtolower(trim($districtInput)) : '');

// Pre-fetch Zila Parishad Ward counts & Block counts per district
$zilaDistrictCounts = [];
$districtBlockCounts = [];
$zilaChairpersons = [];
$totalWardsBihar = 0;
$totalBlocksBihar = 0;

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT district_slug, COUNT(*) as cnt FROM zila_parishad_members GROUP BY district_slug");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $c = intval($row['cnt']);
            $zilaDistrictCounts[strtolower($row['district_slug'])] = $c;
            $totalWardsBihar += $c;
        }

        $stmt2 = $pdo->query("SELECT district_slug, COUNT(*) as cnt FROM census_subdistricts GROUP BY district_slug");
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $bc = intval($row2['cnt']);
            $districtBlockCounts[strtolower($row2['district_slug'])] = $bc;
            $totalBlocksBihar += $bc;
        }

        $stmt3 = $pdo->query("SELECT * FROM zila_parishad_officials");
        while ($offRow = $stmt3->fetch(PDO::FETCH_ASSOC)) {
            $dKey = strtolower($offRow['district_slug'] ?? '');
            $p = strtolower($offRow['post'] ?? '');
            if (strpos($p, 'vice') === false && (strpos($p, 'chairperson') !== false || strpos($p, 'president') !== false || strpos($p, 'अध्यक्ष') !== false)) {
                $zilaChairpersons[$dKey] = $offRow['candidate_name'];
            }
        }
    } catch (Throwable $e) {}
}

// Fetch members and leadership for the selected district
$zilaMembers = [];
$chairperson = null;
$viceChairperson = null;
$singleMember = null;
$prevWardMember = null;
$nextWardMember = null;

if (!empty($selectedDistrict)) {
    $zilaMembers = DataProvider::getZilaParishadMembers($selectedDistrict);
    
    // Fetch official leadership for this specific district
    $districtOfficials = DataProvider::getZilaParishadOfficials($selectedDistrict);
    foreach ($districtOfficials as $off) {
        $p = strtolower($off['post'] ?? '');
        if (strpos($p, 'vice') !== false || strpos($p, 'उपाध्यक्ष') !== false) {
            $viceChairperson = $off;
        } elseif (strpos($p, 'chairperson') !== false || strpos($p, 'president') !== false || strpos($p, 'अध्यक्ष') !== false) {
            $chairperson = $off;
        }
    }

    // Check if a specific single ward member is requested
    if ($wardInput !== '') {
        $currIdx = null;
        foreach ($zilaMembers as $idx => $zm) {
            $tNo = (string)($zm['territory_no'] ?? $zm['ward_no'] ?? '');
            $zId = (string)($zm['id'] ?? '');
            if ($tNo === $wardInput || $zId === $wardInput || slugify($tNo) === slugify($wardInput)) {
                $singleMember = $zm;
                $currIdx = $idx;
                break;
            }
        }
        if ($currIdx !== null) {
            $prevWardMember = $zilaMembers[$currIdx - 1] ?? null;
            $nextWardMember = $zilaMembers[$currIdx + 1] ?? null;
        }
    }
}

// Dynamic SEO Metadata
if ($singleMember && $districtObj) {
    $mName = $singleMember['candidate_name'] ?: 'Territory Member';
    $mWard = $singleMember['territory_no'] ?: $wardInput;
    $mBlock = $singleMember['block'] ?: $districtObj['name'];
    $pageTitle = "{$mName} - Zila Parishad Member (Territory No. {$mWard}, {$mBlock}) | {$districtObj['name']}, Bihar";
    $pageDescription = "Official profile, contact number, registered address, and territorial jurisdiction of {$mName}, elected Zila Parishad Member for Territory No. {$mWard} ({$mBlock}) in {$districtObj['name']} District Board, Bihar.";
    $pageCanonical = getZilaParishadUrl($selectedDistrict, $mWard);
} elseif ($districtObj) {
    $pageTitle = "{$districtObj['name']} District Zila Parishad: Board President & " . (count($zilaMembers) ?: ($zilaDistrictCounts[$selectedDistrict] ?? 'Territorial')) . " Territorial Members";
    $pageDescription = "Official directory of {$districtObj['name']} District Zila Parishad Board Chairperson (अध्यक्ष), Vice-Chairperson (उपाध्यक्ष), and elected Territorial Members in Bihar.";
    $pageCanonical = getZilaParishadUrl($selectedDistrict);
} else {
    $pageTitle = "Bihar Zila Parishad Directory: 38 District Boards (अध्यक्ष, उपाध्यक्ष व 1,099+ प्रादेशिक सदस्य)";
    $pageDescription = "Official directory of Bihar Zila Parishad Board Chairpersons (अध्यक्ष), Vice-Chairpersons (उपाध्यक्ष), and 38 District Panchayat Boards across Bihar.";
    $pageCanonical = SITE_URL . "/zila-parishad";
}

$activeNav = 'zila-parishad';
require_once __DIR__ . '/header.php';
?>

<style>
/* Custom Zila Parishad UI Enhancements */
.zp-profile-header-card {
    background: linear-gradient(135deg, #ffffff 0%, #fdfbf7 100%);
    border: 1px solid rgba(230, 149, 0, 0.2) !important;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
}
.zp-avatar-badge {
    width: 84px;
    height: 84px;
    background: linear-gradient(135deg, #FF9933 0%, #E65100 100%);
    color: #ffffff;
    font-weight: 800;
    font-size: 2rem;
    box-shadow: 0 6px 16px rgba(230, 81, 0, 0.28);
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
}
.zp-stat-tile {
    background: #ffffff;
    border: 1px solid #eef0f3;
    border-radius: 16px;
    padding: 16px 12px;
    transition: all 0.25s ease;
    text-align: center;
    height: 100%;
}
.zp-stat-tile:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
    border-color: #ffd180;
}
.zp-portal-hero-stat {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 20px;
    padding: 20px;
    color: #ffffff;
    transition: all 0.25s ease;
}
.zp-portal-hero-stat:hover {
    background: rgba(255, 255, 255, 0.14);
    transform: translateY(-3px);
}
.zp-district-card {
    border-radius: 20px !important;
    transition: all 0.25s ease;
    border: 1px solid #edf0f5;
    background: #ffffff;
}
.zp-district-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.08) !important;
    border-color: #ffd180;
}
.zp-ward-pill {
    transition: all 0.2s ease;
    font-size: 0.85rem;
}
.zp-ward-pill:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}
.zp-contact-box {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.06) 0%, rgba(255, 193, 7, 0.06) 100%);
    border: 1.5px dashed rgba(25, 135, 84, 0.35);
    border-radius: 18px;
}
.zp-hierarchy-step {
    position: relative;
    padding-left: 28px;
}
.zp-hierarchy-step::before {
    content: '';
    position: absolute;
    left: 8px;
    top: 6px;
    bottom: -16px;
    width: 2px;
    background: #dee2e6;
}
.zp-hierarchy-step:last-child::before {
    display: none;
}
.zp-hierarchy-dot {
    position: absolute;
    left: 0;
    top: 4px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
}
</style>

<!-- Hero Banner -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                🏛️ Bihar Panchayati Raj Tier 3 (Apex)
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                38 District Boards
            </span>
            <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                1,099+ Territorial Members
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex flex-wrap align-items-center">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none"><i class="bi bi-house-door me-1"></i>Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/panchayat" class="text-white-50 text-decoration-none">Panchayats</a></li>
                <?php if ($singleMember && $districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/zila-parishad" class="text-white-50 text-decoration-none">Zila Parishad</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getZilaParishadUrl($selectedDistrict); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?> Board</a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Territory No. <?php echo htmlspecialchars($singleMember['territory_no'] ?? $wardInput); ?>: <?php echo htmlspecialchars($singleMember['candidate_name'] ?? ''); ?></li>
                <?php elseif ($districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/zila-parishad" class="text-white-50 text-decoration-none">Zila Parishad Directory</a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($districtObj['name']); ?> District</li>
                <?php else: ?>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">38 District Boards</li>
                <?php endif; ?>
            </ol>
        </nav>

        <?php if ($singleMember && $districtObj): 
            $sName = $singleMember['candidate_name'] ?: 'Elected Territory Member';
            $sWard = $singleMember['territory_no'] ?: $wardInput;
            $sBlock = $singleMember['block'] ?: '';
        ?>
            <h1 class="display-6 fw-extrabold text-white mb-2">
                <?php echo htmlspecialchars($sName); ?> <br>
                <span style="color: var(--accent-saffron);">
                    Zila Parishad Member &bull; Territory No. <?php echo htmlspecialchars($sWard); ?> (<?php echo htmlspecialchars($districtObj['name']); ?>)
                </span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                Official representative profile, verified contact details, registered address, and territorial constituency details for <strong><?php echo htmlspecialchars($sName); ?></strong>, representing <strong><?php echo htmlspecialchars($sBlock); ?></strong> block in <strong><?php echo htmlspecialchars($districtObj['name']); ?> District Zila Parishad</strong>.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo getZilaParishadUrl($selectedDistrict); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> All <?php echo htmlspecialchars($districtObj['name']); ?> Territories (<?php echo count($zilaMembers); ?>)
                </a>
                <?php if (!empty($sBlock)): ?>
                    <a href="<?php echo getBlockUrl($selectedDistrict, slugify($sBlock)); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                        <i class="bi bi-geo-alt-fill me-1"></i> <?php echo htmlspecialchars($sBlock); ?> Block Hub
                    </a>
                <?php endif; ?>
                <a href="<?php echo getDistrictUrl($selectedDistrict); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($districtObj['name']); ?> District Hub
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Updates
                </a>
            </div>

        <?php elseif ($districtObj): ?>
            <h1 class="display-6 fw-extrabold text-white mb-2">
                <?php echo htmlspecialchars($districtObj['name']); ?> District Zila Parishad <br>
                <span style="color: var(--accent-saffron);">
                    Board President &amp; <?php echo (count($zilaMembers) ?: ($zilaDistrictCounts[$selectedDistrict] ?? 'Territorial')); ?> Territorial Members
                </span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                Official directory of <?php echo htmlspecialchars($districtObj['name']); ?> District Zila Parishad Chairperson (अध्यक्ष), Vice-Chairperson (उपाध्यक्ष), and elected Territorial Members overseeing rural development.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo getPanchayatUrl($selectedDistrict); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building-check me-1"></i> Gram Panchayats (8,400+)
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($selectedDistrict); ?>" class="btn btn-primary fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-award me-1"></i> Block Samiti Pramukhs
                </a>
                <a href="<?php echo SITE_URL; ?>/blocks" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-geo-alt me-1"></i> 534 Blocks Directory
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Updates
                </a>
            </div>

        <?php else: ?>
            <h1 class="display-6 fw-extrabold text-white mb-2">
                Bihar Zila Parishad Directory <br>
                <span style="color: var(--accent-saffron);">
                    38 District Boards &bull; 1,099+ Territorial Members
                </span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 900px;">
                Official directory of Bihar Zila Parishad Board Chairpersons (अध्यक्ष), Vice-Chairpersons (उपाध्यक्ष), and 1,099+ directly elected Territorial Members overseeing rural infrastructure and District Planning Committees across all 38 districts.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building-check me-1"></i> Gram Panchayats Directory
                </a>
                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="btn btn-primary fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-award me-1"></i> Block Samiti Pramukhs
                </a>
                <a href="<?php echo SITE_URL; ?>/blocks" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-geo-alt me-1"></i> 534 Blocks Directory
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-whatsapp me-1"></i> WhatsApp Updates
                </a>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Main Content Area -->
<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <?php if ($singleMember && $districtObj): 
        // =================================================================
        // SINGLE WARD MEMBER PROFILE VIEW (e.g. /zila-parishad/patna/3)
        // =================================================================
        $mName = $singleMember['candidate_name'] ?: 'Elected Ward Member';
        $mWard = (string)($singleMember['territory_no'] ?? $wardInput);
        $mBlock = (string)($singleMember['block'] ?? '');
        $mGen = (string)($singleMember['gender'] ?? '');
        $mCat = (string)($singleMember['category'] ?? '');
        $mAddr = (string)($singleMember['address'] ?? '');
        $mMob = (string)($singleMember['mobile'] ?? '');
        $mTenure = (string)($singleMember['tenure'] ?? '2021-2026');
        $mDist = $districtObj['name'];
        $cleanPhone = preg_replace('/[^0-9]/', '', $mMob);

        // Name Initial for Avatar
        $nameParts = preg_split('/\s+/u', trim($mName));
        $initial = !empty($nameParts[0]) ? mb_substr($nameParts[0], 0, 1) : 'ZP';
    ?>

        <!-- Top Previous / Next Territory Quick Switcher Toolbar -->
        <div class="card border-0 shadow-sm rounded-4 p-2.5 mb-4 bg-white">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <?php if ($prevWardMember): 
                        $pW = $prevWardMember['territory_no'] ?? $prevWardMember['ward_no'];
                        $pN = $prevWardMember['candidate_name'];
                    ?>
                        <a href="<?php echo getZilaParishadUrl($selectedDistrict, $pW); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                            <i class="bi bi-chevron-left"></i> 
                            <span>Territory <?php echo htmlspecialchars($pW); ?>: <span class="d-none d-md-inline text-muted small"><?php echo htmlspecialchars($pN); ?></span></span>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted disabled small">
                            <i class="bi bi-chevron-left"></i> First Territory
                        </span>
                    <?php endif; ?>
                </div>

                <div class="text-center">
                    <span class="badge bg-warning bg-opacity-20 text-dark fw-bold px-3 py-1.5 rounded-pill font-monospace">
                        Territory No. <?php echo htmlspecialchars($mWard); ?> of <?php echo count($zilaMembers); ?> in <?php echo htmlspecialchars($mDist); ?>
                    </span>
                </div>

                <div>
                    <?php if ($nextWardMember): 
                        $nW = $nextWardMember['territory_no'] ?? $nextWardMember['ward_no'];
                        $nN = $nextWardMember['candidate_name'];
                    ?>
                        <a href="<?php echo getZilaParishadUrl($selectedDistrict, $nW); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1">
                            <span>Territory <?php echo htmlspecialchars($nW); ?>: <span class="d-none d-md-inline text-muted small"><?php echo htmlspecialchars($nN); ?></span></span>
                            <i class="bi bi-chevron-right"></i>
                        </a>
                    <?php else: ?>
                        <span class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted disabled small">
                            Last Territory <i class="bi bi-chevron-right"></i>
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Primary Member Dossier & Bento Layout -->
            <div class="col-12 col-lg-8">
                
                <!-- Main Dossier Card (Glassmorphic Saffron Header) -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-4 mb-4 zp-profile-header-card position-relative overflow-hidden">
                    
                    <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center gap-3.5 pb-4 border-bottom mb-4">
                        <div class="zp-avatar-badge flex-shrink-0">
                            <?php echo htmlspecialchars($initial); ?>
                        </div>

                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-1.5">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill">
                                    🏛️ Territory No. <?php echo htmlspecialchars($mWard); ?>
                                </span>
                                <?php if (!empty($mBlock)): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1 rounded-pill">
                                        📍 प्रखंड: <?php echo htmlspecialchars($mBlock); ?>
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small">
                                    <i class="bi bi-patch-check-fill me-1"></i> SEC Bihar Verified
                                </span>
                            </div>

                            <h2 class="fw-bold font-heading text-navy display-6 fs-2 mb-1">
                                <?php echo htmlspecialchars($mName); ?>
                            </h2>
                            <p class="text-muted small mb-0">
                                जिला परिषद प्रादेशिक निर्वाचन क्षेत्र संख्या <strong><?php echo htmlspecialchars($mWard); ?></strong> &bull; 
                                <strong class="text-navy"><?php echo htmlspecialchars($mDist); ?> District Zila Parishad Board</strong>
                            </p>
                        </div>
                    </div>

                    <!-- 4-Stat Metrics Tiles Grid -->
                    <div class="row g-3 mb-4">
                        <div class="col-6 col-sm-3">
                            <div class="zp-stat-tile">
                                <span class="text-muted small d-block mb-1">प्रादेशिक क्षेत्र (Territory)</span>
                                <strong class="fs-5 text-primary font-monospace">Territory No. <?php echo htmlspecialchars($mWard); ?></strong>
                                <small class="text-muted d-block mt-0.5" style="font-size: 0.72rem;">~50K Population</small>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="zp-stat-tile">
                                <span class="text-muted small d-block mb-1">CD Block (प्रखंड)</span>
                                <strong class="fs-6 text-navy d-block text-truncate" title="<?php echo htmlspecialchars($mBlock); ?>">
                                    <?php echo htmlspecialchars($mBlock ?: 'District Wide'); ?>
                                </strong>
                                <small class="text-muted d-block mt-0.5" style="font-size: 0.72rem;">Sub-district</small>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="zp-stat-tile">
                                <span class="text-muted small d-block mb-1">Gender (लिंग)</span>
                                <?php if (strtolower($mGen) === 'female' || strpos($mGen, 'महिला') !== false): ?>
                                    <strong class="fs-6 text-danger"><i class="bi bi-gender-female"></i> Female</strong>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 0.72rem;">महिला प्रतिनिधि</small>
                                <?php else: ?>
                                    <strong class="fs-6 text-primary"><i class="bi bi-gender-male"></i> Male</strong>
                                    <small class="text-muted d-block mt-0.5" style="font-size: 0.72rem;">पुरुष प्रतिनिधि</small>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="zp-stat-tile">
                                <span class="text-muted small d-block mb-1">आरक्षण (Category)</span>
                                <strong class="small text-dark text-truncate d-block fw-bold" title="<?php echo htmlspecialchars($mCat); ?>">
                                    <?php echo htmlspecialchars($mCat ?: 'General / अनारक्षित'); ?>
                                </strong>
                                <small class="text-muted d-block mt-0.5" style="font-size: 0.72rem;">Reserved Seat</small>
                            </div>
                        </div>
                    </div>

                    <!-- Interactive Verified Contact Hub Box -->
                    <div class="p-3.5 p-md-4 mb-4 zp-contact-box">
                        <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-circle bg-success text-white p-3 d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 52px; height: 52px;">
                                    <i class="bi bi-telephone-outbound-fill fs-4"></i>
                                </div>
                                <div>
                                    <div class="d-flex align-items-center gap-2 mb-0.5">
                                        <span class="badge bg-success text-white fw-bold px-2 py-0.5 rounded small">Official Contact</span>
                                        <span class="text-muted small">&bull; 10 Reveals/Day Available</span>
                                    </div>
                                    <h6 class="fw-bold text-navy mb-0">Representative Mobile Number</h6>
                                    <small class="text-muted">Click reveal button below to view contact number with 1-click calling &amp; WhatsApp</small>
                                </div>
                            </div>
                            <div class="text-sm-end">
                                <?php if (!empty($mMob)): ?>
                                    <?php echo renderMaskedPhoneButton($mMob, $mName, "Zila Parishad Territory No. {$mWard} Member ({$mDist})"); ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill">Number On File Pending</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Registered Address & Local Residence Card -->
                    <div class="p-3.5 rounded-4 bg-light border mb-4">
                        <div class="d-flex align-items-start gap-3">
                            <div class="rounded-circle bg-warning bg-opacity-25 text-dark p-2.5 d-flex align-items-center justify-content-center flex-shrink-0 mt-1" style="width: 44px; height: 44px;">
                                <i class="bi bi-geo-alt-fill fs-5 text-dark"></i>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
                                    <h6 class="fw-bold text-navy mb-0">Registered Residential Address (स्थाई पता)</h6>
                                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5 small" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars(addslashes($mAddr ?: "Territory No. {$mWard}, {$mBlock}, {$mDist}")); ?>'); this.innerText='Copied!';">
                                        <i class="bi bi-clipboard me-1"></i>Copy
                                    </button>
                                </div>
                                <p class="text-dark small mb-1.5 lh-base font-monospace" style="font-size: 0.95rem;">
                                    <?php echo htmlspecialchars($mAddr ?: "Territorial Constituency No. {$mWard}, Block: {$mBlock}, District: {$mDist}, Bihar"); ?>
                                </p>
                                <div class="d-flex flex-wrap gap-2 text-muted small">
                                    <span>CD Block: <strong><?php echo htmlspecialchars($mBlock); ?></strong></span> &bull; 
                                    <span>District: <strong><?php echo htmlspecialchars($mDist); ?></strong></span> &bull; 
                                    <span>State: <strong>Bihar (बिहार)</strong></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action & Social Share Toolbar -->
                    <div class="d-flex flex-wrap gap-2 pt-3 border-top justify-content-between align-items-center">
                        <div class="d-flex gap-2 flex-wrap">
                            <a href="<?php echo getZilaParishadUrl($selectedDistrict); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                                <i class="bi bi-arrow-left me-1"></i> <?php echo htmlspecialchars($districtObj['name']); ?> Zila Parishad Roster
                            </a>
                            <?php if (!empty($mBlock)): ?>
                                <a href="<?php echo getBlockUrl($selectedDistrict, slugify($mBlock)); ?>" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                                    <i class="bi bi-building me-1"></i> <?php echo htmlspecialchars($mBlock); ?> Block Panchayats
                                </a>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2">
                            <?php if (!empty($_SESSION['admin_logged_in'])): ?>
                                <a href="<?php echo SITE_URL; ?>/admin/zila-parishad.php?district=<?php echo urlencode($districtObj['name']); ?>&q=<?php echo urlencode($mName); ?>" target="_blank" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold">
                                    <i class="bi bi-pencil-square me-1"></i> Edit in Admin
                                </a>
                            <?php endif; ?>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 fw-semibold" onclick="window.print()">
                                <i class="bi bi-printer me-1"></i> Print
                            </button>
                            <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Zila Parishad Territory No. {$mWard} Member ({$mDist}): {$mName} - " . getZilaParishadUrl($selectedDistrict, $mWard)); ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold shadow-sm">
                                <i class="bi bi-whatsapp me-1"></i> Share Profile
                            </a>
                        </div>
                    </div>
                </div>

                <!-- 3-Tier Bihar Panchayati Raj Hierarchy Context -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-navy font-heading mb-3">
                        <i class="bi bi-diagram-3-fill text-warning me-2"></i> Bihar 3-Tier Panchayati Raj Hierarchy Position
                    </h5>
                    <div class="p-3 bg-light rounded-4 border">
                        
                        <!-- Tier 3 Apex: Zila Parishad -->
                        <div class="zp-hierarchy-step pb-3">
                            <div class="zp-hierarchy-dot bg-warning border border-2 border-white shadow-sm"></div>
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="fw-bold text-navy mb-0">Tier 3 (Apex): District Zila Parishad (जिला परिषद)</h6>
                                <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">Current Level: Territory No. <?php echo htmlspecialchars($mWard); ?></span>
                            </div>
                            <p class="text-muted small mb-0">Oversees rural infrastructure, District Planning Committee budgets, 15th Finance Commission fund approvals for all <?php echo htmlspecialchars($districtObj['name']); ?> district.</p>
                        </div>

                        <!-- Tier 2 Intermediate: Panchayat Samiti -->
                        <div class="zp-hierarchy-step pb-3">
                            <div class="zp-hierarchy-dot bg-primary border border-2 border-white shadow-sm"></div>
                            <h6 class="fw-bold text-navy mb-0">Tier 2 (Block Level): Panchayat Samiti (पंचायत समिति)</h6>
                            <p class="text-muted small mb-0">Headed by elected Block Pramukhs across <?php echo htmlspecialchars($mBlock); ?> block.</p>
                        </div>

                        <!-- Tier 1 Grassroots: Gram Panchayat -->
                        <div class="zp-hierarchy-step">
                            <div class="zp-hierarchy-dot bg-success border border-2 border-white shadow-sm"></div>
                            <h6 class="fw-bold text-navy mb-0">Tier 1 (Village Level): Gram Panchayats (ग्राम पंचायत)</h6>
                            <p class="text-muted small mb-0">Administered by Mukhiyas and Gram Kutchery Sarpanchs executing village works.</p>
                        </div>

                    </div>
                </div>

                <!-- Statutory Powers & Responsibilities of Zila Parishad Member -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h5 class="fw-bold text-navy font-heading mb-3">
                        <i class="bi bi-shield-check text-primary me-2"></i> Statutory Powers &amp; Responsibilities
                    </h5>
                    <div class="row g-3 small text-dark">
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="fw-bold text-navy mb-1">🏛️ District Planning Committee (DPC)</div>
                                <span class="text-muted">Participate in formulating, debating and passing the annual rural district infrastructure plans and budgetary allocations.</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="fw-bold text-navy mb-1">💧 Rural Infrastructure &amp; Roads</div>
                                <span class="text-muted">Supervise sanctioning and timely execution of PMGSY rural roadways, minor irrigation canals, drinking water and community sheds.</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="fw-bold text-navy mb-1">💰 Grant Disbursement Oversight</div>
                                <span class="text-muted">Review disbursement and quality compliance for Central 15th Finance Commission &amp; State Finance Commission rural grants.</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 border h-100">
                                <div class="fw-bold text-navy mb-1">🤝 Public Grievance Redressal</div>
                                <span class="text-muted">Represent rural citizens of Territory No. <?php echo htmlspecialchars($mWard); ?> before the District Magistrate (DM) and CEO Zila Parishad.</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Neighboring Territories Interactive Matrix with Search -->
                <?php if (!empty($zilaMembers) && count($zilaMembers) > 1): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="fw-bold text-navy font-heading mb-0">
                                    <i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i> All <?php echo htmlspecialchars($districtObj['name']); ?> Territories Matrix
                                </h5>
                                <small class="text-muted">Switch between all <?php echo count($zilaMembers); ?> elected Territorial Members in <?php echo htmlspecialchars($districtObj['name']); ?>:</small>
                            </div>
                            <div style="max-width: 220px;">
                                <input type="text" id="wardFilterInput" class="form-control form-control-sm rounded-pill" placeholder="Filter territory no..." onkeyup="filterWardPills(this.value)">
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2" id="wardPillsContainer">
                            <?php foreach ($zilaMembers as $zm): 
                                $otherWard = (string)($zm['territory_no'] ?? $zm['ward_no'] ?? '');
                                $otherName = (string)($zm['candidate_name'] ?? '');
                                $otherBlock = (string)($zm['block'] ?? '');
                                $isActive = ($otherWard === $mWard);
                                $otherUrl = getZilaParishadUrl($selectedDistrict, $otherWard);
                            ?>
                                <a href="<?php echo htmlspecialchars($otherUrl); ?>" 
                                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold zp-ward-pill ward-item-pill <?php echo $isActive ? 'btn-warning text-dark fw-bold shadow-sm' : 'btn-outline-secondary'; ?>" 
                                   data-ward="<?php echo htmlspecialchars($otherWard); ?>"
                                   data-name="<?php echo htmlspecialchars(strtolower($otherName . ' ' . $otherBlock)); ?>"
                                   title="Territory No. <?php echo htmlspecialchars($otherWard); ?>: <?php echo htmlspecialchars($otherName); ?> (<?php echo htmlspecialchars($otherBlock); ?>)">
                                    Territory <?php echo htmlspecialchars($otherWard); ?>
                                    <?php if ($isActive): ?> <i class="bi bi-check-circle-fill ms-1"></i><?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <script>
                    function filterWardPills(val) {
                        const q = (val || '').toLowerCase().trim();
                        document.querySelectorAll('.ward-item-pill').forEach(pill => {
                            const w = (pill.getAttribute('data-ward') || '').toLowerCase();
                            const n = (pill.getAttribute('data-name') || '').toLowerCase();
                            if (!q || w.includes(q) || n.includes(q)) {
                                pill.classList.remove('d-none');
                            } else {
                                pill.classList.add('d-none');
                            }
                        });
                    }
                    </script>
                <?php endif; ?>

            </div>

            <!-- Right Column: District Board Leadership & Portals -->
            <div class="col-12 col-lg-4">
                
                <!-- District Board Leadership Card -->
                <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white border-top border-4 border-warning">
                    <h6 class="fw-bold text-navy font-heading mb-3">
                        <i class="bi bi-award-fill text-warning me-1"></i> <?php echo htmlspecialchars($districtObj['name']); ?> Board Leadership
                    </h6>

                    <!-- Chairperson -->
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-warning text-dark fw-bold px-2 py-0.5 rounded small">जिला परिषद अध्यक्ष</span>
                            <span class="text-muted small">&bull; Chairperson</span>
                        </div>
                        <h6 class="fw-bold text-navy mb-0">
                            <?php echo htmlspecialchars($chairperson['candidate_name'] ?? 'District Chairperson'); ?>
                        </h6>
                        <small class="text-muted">
                            Reservation: <strong><?php echo htmlspecialchars($chairperson['category'] ?? $chairperson['reservation'] ?? 'General'); ?></strong>
                        </small>
                    </div>

                    <!-- Vice Chairperson -->
                    <div class="p-3 rounded-3 bg-light border mb-3">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-primary text-white fw-bold px-2 py-0.5 rounded small">जिला परिषद उपाध्यक्ष</span>
                            <span class="text-muted small">&bull; Vice-Chairperson</span>
                        </div>
                        <h6 class="fw-bold text-navy mb-0">
                            <?php echo htmlspecialchars($viceChairperson['candidate_name'] ?? 'Vice-Chairperson Elected'); ?>
                        </h6>
                        <small class="text-muted">
                            Reservation: <strong><?php echo htmlspecialchars($viceChairperson['category'] ?? $viceChairperson['reservation'] ?? 'General'); ?></strong>
                        </small>
                    </div>

                    <a href="<?php echo getZilaParishadUrl($selectedDistrict); ?>" class="btn btn-warning btn-sm w-100 fw-bold rounded-pill shadow-sm">
                        View All <?php echo htmlspecialchars($districtObj['name']); ?> Territories Roster &rarr;
                    </a>
                </div>

                <!-- CD Block Directory Quick Portal -->
                <?php if (!empty($mBlock)): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white border-start border-4 border-primary">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary text-white fw-bold px-2 py-0.5 rounded small">Jurisdiction Hub</span>
                        </div>
                        <h6 class="fw-bold text-navy mb-1"><?php echo htmlspecialchars($mBlock); ?> CD Block</h6>
                        <p class="text-muted small mb-3">Explore Gram Panchayats, Mukhiyas, and Block Samiti Pramukh for <?php echo htmlspecialchars($mBlock); ?>.</p>
                        <a href="<?php echo getBlockUrl($selectedDistrict, slugify($mBlock)); ?>" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-semibold">
                            Visit <?php echo htmlspecialchars($mBlock); ?> Block Hub &rarr;
                        </a>
                    </div>
                <?php endif; ?>

                <!-- District Hub & Quick Portals -->
                <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
                    <h6 class="fw-bold text-navy font-heading mb-3">
                        <i class="bi bi-compass-fill text-primary me-1"></i> Quick Panchayati Raj Links
                    </h6>
                    <ul class="list-unstyled mb-0 small">
                        <li class="mb-2.5 pb-2.5 border-bottom">
                            <a href="<?php echo getPanchayatUrl($selectedDistrict); ?>" class="text-decoration-none text-navy hover-primary d-flex align-items-center justify-content-between">
                                <span>🏡 <?php echo htmlspecialchars($districtObj['name']); ?> Gram Panchayats</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                        <li class="mb-2.5 pb-2.5 border-bottom">
                            <a href="<?php echo getPanchayatSamitiUrl($selectedDistrict); ?>" class="text-decoration-none text-navy hover-primary d-flex align-items-center justify-content-between">
                                <span>⏳ Block Samiti Pramukhs</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                        <li class="mb-2.5 pb-2.5 border-bottom">
                            <a href="<?php echo SITE_URL; ?>/blocks?district=<?php echo urlencode($selectedDistrict); ?>" class="text-decoration-none text-navy hover-primary d-flex align-items-center justify-content-between">
                                <span>🏢 CD Blocks Directory</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo getDistrictUrl($selectedDistrict); ?>" class="text-decoration-none text-navy hover-primary d-flex align-items-center justify-content-between">
                                <span>🏛️ <?php echo htmlspecialchars($districtObj['name']); ?> District Portal</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- Official WhatsApp Channel Card -->
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-success bg-opacity-10 border border-success border-opacity-25 text-center">
                    <div class="rounded-circle bg-success text-white p-3 d-inline-flex align-items-center justify-content-center mb-2" style="width: 52px; height: 52px;">
                        <i class="bi bi-whatsapp fs-4"></i>
                    </div>
                    <h6 class="fw-bold text-navy mb-1">Bihar Election WhatsApp</h6>
                    <p class="text-muted small mb-3">Get real-time updates on local body panchayat elections &amp; candidates.</p>
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success btn-sm rounded-pill fw-bold shadow-sm px-4">
                        <i class="bi bi-whatsapp me-1"></i> Join Channel
                    </a>
                </div>

            </div>
        </div>

    <?php elseif (!empty($selectedDistrict) && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- DISTRICT VIEW: SHOW ONLY THE CORRESPONDING DISTRICT'S BOARD   -->
        <!-- ============================================================= -->

        <!-- District Header & Board Leadership Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small">
                            🏛️ District Board Profile
                        </span>
                        <span class="badge bg-light text-muted border px-2 py-1 rounded-pill small">
                            <?php echo htmlspecialchars($districtObj['division'] ?? 'Bihar'); ?> Division
                        </span>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($districtObj['name']); ?> District Zila Parishad Board
                    </h2>
                    <p class="text-muted small mb-0">
                        Headquarters: <strong><?php echo htmlspecialchars($districtObj['headquarters'] ?? $districtObj['name']); ?></strong> &bull; 
                        Territorial Constituencies: <strong class="text-primary"><?php echo count($zilaMembers) ?: ($zilaDistrictCounts[$selectedDistrict] ?? 'Mapped'); ?> Territories</strong>
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo SITE_URL; ?>/zila-parishad" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> All Districts (Change District)
                    </a>
                    <a href="<?php echo getDistrictUrl($selectedDistrict); ?>" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-building me-1"></i> Visit District Hub
                    </a>
                </div>
            </div>

            <!-- Leadership Profiles (Chairperson & Vice-Chairperson) -->
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-25 text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px;">
                            <i class="bi bi-award-fill fs-3 text-warning"></i>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark fw-bold px-2 py-0.5 rounded small">जिला परिषद अध्यक्ष (Chairperson)</span>
                            <h5 class="fw-bold text-navy mb-0 mt-1">
                                <?php echo htmlspecialchars($chairperson['candidate_name'] ?? 'Chairperson Elected'); ?>
                            </h5>
                            <small class="text-muted">
                                Reservation / Category: <strong><?php echo htmlspecialchars($chairperson['category'] ?? $chairperson['reservation'] ?? 'General'); ?></strong>
                                <?php if (!empty($chairperson['address'])): ?>
                                    &bull; <span title="<?php echo htmlspecialchars($chairperson['address']); ?>"><?php echo htmlspecialchars(mb_strimwidth($chairperson['address'], 0, 35, '...')); ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="p-3 rounded-3 bg-light border h-100 d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 54px; height: 54px;">
                            <i class="bi bi-person-badge-fill fs-3 text-primary"></i>
                        </div>
                        <div>
                            <span class="badge bg-primary text-white fw-bold px-2 py-0.5 rounded small">जिला परिषद उपाध्यक्ष (Vice-Chairperson)</span>
                            <h5 class="fw-bold text-navy mb-0 mt-1">
                                <?php echo htmlspecialchars($viceChairperson['candidate_name'] ?? 'Vice-Chairperson Elected'); ?>
                            </h5>
                            <small class="text-muted">
                                Reservation / Category: <strong><?php echo htmlspecialchars($viceChairperson['category'] ?? $viceChairperson['reservation'] ?? 'General'); ?></strong>
                                <?php if (!empty($viceChairperson['address'])): ?>
                                    &bull; <span title="<?php echo htmlspecialchars($viceChairperson['address']); ?>"><?php echo htmlspecialchars(mb_strimwidth($viceChairperson['address'], 0, 35, '...')); ?></span>
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Controls -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-4 bg-white">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-geo-alt-fill text-warning"></i> Switch District:</label>
                    <select class="form-select form-select-sm rounded-pill" onchange="if(this.value){ location.href = this.value; }">
                        <option value="" disabled <?php echo empty($selectedDistrict) ? 'selected' : ''; ?>>-- Switch District --</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo SITE_URL; ?>/zila-parishad/<?php echo urlencode($d['slug']); ?>" <?php echo $selectedDistrict === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?> District (<?php echo $zilaDistrictCounts[$d['slug']] ?? 'Mapped'; ?> Territories)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-md-7">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-search text-primary"></i> Search Territory Number, Member Name or Block:</label>
                    <input type="text" id="zilaSearchInput" class="form-control form-control-sm rounded-pill" placeholder="Type member name, territory no (e.g. 1) or block name...">
                </div>
            </div>
        </div>

        <!-- Territorial Members Roster Table (Simplified with Link to Details) -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-people-fill text-primary me-2"></i> <?php echo htmlspecialchars($districtObj['name']); ?> Territorial Members Roster
                    </h5>
                    <small class="text-muted">Showing Name, Block Name &amp; Territory No. &bull; Click link to view full Address, Mobile &amp; Details</small>
                </div>
                <div class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm" id="totalMembersCount">
                    <?php echo count($zilaMembers); ?> Territorial Members
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="districtZilaTable">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-4 text-navy fw-bold small text-uppercase text-center" style="width: 140px;">Territory No.</th>
                            <th class="py-3 px-4 text-navy fw-bold small text-uppercase">Member Name</th>
                            <th class="py-3 px-4 text-navy fw-bold small text-uppercase">Block Name</th>
                            <th class="py-3 px-4 text-navy fw-bold small text-uppercase text-end" style="width: 240px;">Address, Mobile &amp; Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($zilaMembers)): ?>
                            <?php foreach ($zilaMembers as $zm): 
                                $zWard = (string)($zm['territory_no'] ?? $zm['ward_no'] ?? '');
                                $zName = (string)($zm['candidate_name'] ?? $zm['member_name'] ?? '');
                                $zBlock = (string)($zm['block'] ?? '');
                                $zWardUrl = getZilaParishadUrl($selectedDistrict, $zWard);
                            ?>
                                <tr class="zila-row"
                                    data-name="<?php echo htmlspecialchars(strtolower($zName)); ?>"
                                    data-block="<?php echo htmlspecialchars(strtolower($zBlock)); ?>"
                                    data-ward="<?php echo htmlspecialchars($zWard); ?>">
                                    
                                    <!-- Territory No -->
                                    <td class="text-center fw-bold">
                                        <span class="badge bg-warning bg-opacity-25 text-dark px-3 py-2 rounded-pill font-monospace fs-6">
                                            <?php echo htmlspecialchars($zWard); ?>
                                        </span>
                                    </td>

                                    <!-- Member Name -->
                                    <td class="px-4">
                                        <div class="fw-bold text-navy" style="font-size: 1.02rem;">
                                            <a href="<?php echo htmlspecialchars($zWardUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                                <?php echo htmlspecialchars($zName); ?>
                                            </a>
                                        </div>
                                        <small class="text-muted">Territory No. <?php echo htmlspecialchars($zWard); ?> Representative</small>
                                    </td>

                                    <!-- Block Name -->
                                    <td class="px-4">
                                        <?php if (!empty($zBlock)): ?>
                                            <a href="<?php echo getBlockUrl($selectedDistrict, slugify($zBlock)); ?>" class="text-decoration-none text-dark hover-primary fw-semibold">
                                                📍 <?php echo htmlspecialchars($zBlock); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">District Wide</span>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Action Link for Address, Mobile and Details -->
                                    <td class="text-end px-4">
                                        <a href="<?php echo htmlspecialchars($zWardUrl); ?>" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold text-white shadow-sm text-nowrap">
                                            View Details &rarr;
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-2 d-block mb-2 text-muted"></i>
                                    No Territorial Member records found for <?php echo htmlspecialchars($districtObj['name']); ?> District.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const zSearch = document.getElementById('zilaSearchInput');
            const rows = document.querySelectorAll('#districtZilaTable .zila-row');

            function filterDistrictZila() {
                const query = (zSearch?.value || '').toLowerCase().trim();
                let visible = 0;

                rows.forEach(row => {
                    const name = (row.getAttribute('data-name') || '').toLowerCase();
                    const block = (row.getAttribute('data-block') || '').toLowerCase();
                    const ward = (row.getAttribute('data-ward') || '').toLowerCase();

                    const matchesQuery = !query || name.includes(query) || block.includes(query) || ward.includes(query);

                    if (matchesQuery) {
                        row.style.display = '';
                        visible++;
                    } else {
                        row.style.display = 'none';
                    }
                });

                const countBadge = document.getElementById('totalMembersCount');
                if (countBadge) countBadge.innerText = visible + ' Territorial Members';
            }

            if (zSearch) zSearch.addEventListener('input', filterDistrictZila);
        });
        </script>

    <?php else: ?>
        <!-- ============================================================= -->
        <!-- REDESIGNED MAIN PORTAL: 38 DISTRICT BOARDS SELECTOR GRID      -->
        <!-- ============================================================= -->

        <!-- 4 Stat Summary Highlights Row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-20 text-dark p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                            <i class="bi bi-bank fs-4 text-warning"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">District Boards</span>
                            <h4 class="fw-extrabold text-navy mb-0">38</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                            <i class="bi bi-people-fill fs-4 text-primary"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Territorial Constituencies</span>
                            <h4 class="fw-extrabold text-navy mb-0"><?php echo number_format($totalWardsBihar ?: 1099); ?>+</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                            <i class="bi bi-building fs-4 text-success"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">CD Blocks Covered</span>
                            <h4 class="fw-extrabold text-navy mb-0"><?php echo number_format($totalBlocksBihar ?: 534); ?></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-info">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-info bg-opacity-10 text-dark p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 46px; height: 46px;">
                            <i class="bi bi-award-fill fs-4 text-info"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Apex Governance</span>
                            <h4 class="fw-extrabold text-navy mb-0">Tier 3</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search & Administrative Division Filters Card -->
        <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-lg-5">
                    <h5 class="fw-bold text-navy mb-1 font-heading">
                        <i class="bi bi-grid-fill text-warning me-2"></i> Select a District Board (जिला परिषद)
                    </h5>
                    <p class="text-muted small mb-0">Filter by administrative division or search by district, chairperson &amp; headquarters:</p>
                </div>
                <div class="col-12 col-lg-7">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                            <i class="bi bi-search text-warning"></i>
                        </span>
                        <input type="text" id="districtSearchInput" class="form-control border-start-0 rounded-end-pill py-2" placeholder="Search by district name, chairperson, or headquarters..." onkeyup="filterDistricts()">
                    </div>
                </div>
            </div>

            <!-- Division Filter Pills with Count Indicators -->
            <div class="d-flex flex-wrap gap-1.5 mt-3 pt-3 border-top align-items-center">
                <span class="small fw-bold text-navy me-2"><i class="bi bi-funnel-fill text-warning"></i> Division:</span>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 division-filter-btn active" data-division="All" onclick="filterDivision('All', this)">All (38)</button>
                <?php 
                $divisionsData = [
                    'Patna' => 6,
                    'Tirhut' => 6,
                    'Saran' => 3,
                    'Darbhanga' => 3,
                    'Kosi' => 3,
                    'Purnia' => 4,
                    'Bhagalpur' => 2,
                    'Munger' => 6,
                    'Magadh' => 5
                ];
                foreach ($divisionsData as $div => $dCnt): 
                ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 division-filter-btn" data-division="<?php echo $div; ?>" onclick="filterDivision('<?php echo $div; ?>', this)">
                        <?php echo $div; ?> (<?php echo $dCnt; ?>)
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 38 District Cards Grid (Clean, Simple & Modern Design) -->
        <div class="row g-3" id="districtsGrid">
            <?php foreach ($districts as $d): 
                $dSlug = strtolower($d['slug']);
                $wCount = $zilaDistrictCounts[$dSlug] ?? 0;
                $bCount = $districtBlockCounts[$dSlug] ?? 0;
                $chairName = $zilaChairpersons[$dSlug] ?? null;
                $rawDiv = $d['division'] ?? 'Bihar';
                $cleanDiv = trim(preg_replace('/Division$/i', '', (string)$rawDiv));
                $searchStr = strtolower($d['name'] . ' ' . ($d['name_hi'] ?? '') . ' ' . ($d['headquarters'] ?? '') . ' ' . ($chairName ?? '') . ' ' . $cleanDiv);
                $zilaDistUrl = SITE_URL . "/zila-parishad/" . urlencode($dSlug);
                $districtHubUrl = getDistrictUrl($dSlug);
            ?>
                <div class="col-12 col-md-6 col-lg-4 district-zila-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>" data-division="<?php echo htmlspecialchars($cleanDiv, ENT_QUOTES); ?>">
                    <div class="card h-100 p-3.5 zp-district-card border-0 shadow-sm d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header: Division & Headquarters -->
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="badge bg-warning bg-opacity-15 text-dark fw-semibold px-2.5 py-1 rounded-pill small" style="font-size: 0.74rem;">
                                    🏛️ <?php echo htmlspecialchars($cleanDiv); ?> Division
                                </span>
                                <span class="text-muted small" style="font-size: 0.78rem;">
                                    HQ: <strong><?php echo htmlspecialchars($d['headquarters'] ?? $d['name']); ?></strong>
                                </span>
                            </div>

                            <!-- District Name Title -->
                            <div class="mb-2.5">
                                <h5 class="fw-bold mb-0 text-navy font-heading">
                                    <a href="<?php echo htmlspecialchars($zilaDistUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                        <?php echo htmlspecialchars($d['name']); ?> Zila Parishad
                                    </a>
                                </h5>
                                <div class="text-muted small">
                                    <?php echo htmlspecialchars($d['name_hi'] ?: $d['name']); ?>
                                </div>
                            </div>

                            <!-- Chairperson Row -->
                            <?php if (!empty($chairName)): ?>
                                <div class="p-2.5 rounded-3 bg-light border mb-3 small d-flex align-items-center justify-content-between">
                                    <span class="text-muted"><i class="bi bi-award-fill text-warning me-1"></i>अध्यक्ष:</span>
                                    <strong class="text-navy"><?php echo htmlspecialchars($chairName); ?></strong>
                                </div>
                            <?php else: ?>
                                <div class="p-2 rounded-3 bg-light border mb-3 small text-muted text-center" style="font-size: 0.78rem;">
                                    <i class="bi bi-shield-check text-success me-1"></i>Elected District Board
                                </div>
                            <?php endif; ?>

                            <!-- District 2-Metrics Row -->
                            <div class="d-flex justify-content-around align-items-center p-2.5 bg-light rounded-3 border small mb-2 text-center">
                                <div>
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">Territory Constituencies</span>
                                    <strong class="text-primary fs-6"><?php echo $wCount ? number_format($wCount) . ' Territories' : 'Active Territories'; ?></strong>
                                </div>
                                <div class="border-start ps-3">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">CD Blocks</span>
                                    <strong class="text-dark fs-6"><?php echo $bCount ? number_format($bCount) . ' Blocks' : 'Mapped'; ?></strong>
                                </div>
                            </div>
                        </div>

                        <!-- Action Links Footer -->
                        <div class="mt-2 pt-2.5 border-top d-flex justify-content-between align-items-center gap-2">
                            <a href="<?php echo htmlspecialchars($districtHubUrl); ?>" class="text-decoration-none small text-muted hover-primary fw-semibold" title="Visit District Hub">
                                <i class="bi bi-building me-1"></i>District Hub
                            </a>
                            <a href="<?php echo htmlspecialchars($zilaDistUrl); ?>" class="btn btn-sm btn-warning fw-bold text-dark rounded-pill px-3 py-1.5 shadow-sm text-nowrap">
                                View Territorial Members <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noDistrictsAlert" class="alert alert-info rounded-4 text-center py-5 d-none mt-4 shadow-sm">
            <i class="bi bi-search fs-1 text-warning mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">No District Board found</h5>
            <p class="text-muted mb-3">Try searching for another district name, chairperson or headquarters.</p>
            <button class="btn btn-warning rounded-pill px-4 py-2 fw-semibold" onclick="clearDistrictSearch()">Clear Search</button>
        </div>

        <script>
        let currentDivision = 'All';

        function filterDivision(div, btn) {
            currentDivision = div;
            document.querySelectorAll('.division-filter-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            filterDistricts();
        }

        function filterDistricts() {
            const query = document.getElementById('districtSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.district-zila-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemSearch = item.getAttribute('data-search').toLowerCase();
                const itemDiv = item.getAttribute('data-division') || '';
                
                const matchQuery = query === '' || itemSearch.includes(query);
                const matchDiv = currentDivision === 'All' || itemDiv.toLowerCase().includes(currentDivision.toLowerCase());

                if (matchQuery && matchDiv) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });
            
            const noResults = document.getElementById('noDistrictsAlert');
            if (noResults) {
                if (visibleCount === 0) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            }
        }

        function clearDistrictSearch() {
            document.getElementById('districtSearchInput').value = '';
            currentDivision = 'All';
            document.querySelectorAll('.division-filter-btn').forEach(b => {
                if (b.getAttribute('data-division') === 'All') b.classList.add('active');
                else b.classList.remove('active');
            });
            filterDistricts();
        }
        </script>
    <?php endif; ?>

    <!-- Official Data Sources Attribution Banner -->
    <section class="mt-5 pt-4 border-top">
        <div class="p-4 rounded-4 bg-light border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-15 text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark font-heading mb-1">Standardized Public &amp; Government Data Sources</h6>
                    <p class="text-muted small mb-0">Zila Parishad Board leadership, territorial constituencies, and reservation quotas reference State Election Commission Bihar (<a href="https://sec.bihar.gov.in" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">sec.bihar.gov.in</a>) and Panchayati Raj Dept Bihar.</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/panchayat" class="btn btn-outline-warning text-dark rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-building-check me-1"></i>Panchayat Directory
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
