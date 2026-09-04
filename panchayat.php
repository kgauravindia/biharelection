<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();
$pdo = Database::getConnection();

// Input parameters
$districtInput = $_GET['district'] ?? '';
$blockInput = $_GET['block'] ?? '';
$panchayatInput = $_GET['panchayat'] ?? '';
$slugInput = $_GET['slug'] ?? '';

// If single slug passed, check if it is a district slug
if (empty($districtInput) && !empty($slugInput)) {
    $matchedDist = DataProvider::getDistrictBySlug($slugInput);
    if ($matchedDist) {
        $districtInput = $slugInput;
        $slugInput = '';
    }
}

$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrictSlug = $districtObj['slug'] ?? ($districtInput ? strtolower(trim($districtInput)) : '');

// State / District Variables
$singlePanchayat = null;
$singleBlockObj = null;
$panchayatsInBlock = [];
$panchayatsInSameBlock = [];
$blockSamiti = null;
$zilaParishadMembers = [];
$isSpecificZilaTerritory = false;
$allDistrictPanchayats = [];
$blockPanchayatsMap = [];
$districtBlocks = [];

if ($districtObj && $pdo) {
    $targetPanchayatQuery = $panchayatInput ?: $slugInput;

    // 1. Fetch all census blocks in this district
    try {
        $stmtB = $pdo->prepare("SELECT * FROM census_subdistricts WHERE LOWER(district_slug) = :dslug ORDER BY sub_district ASC");
        $stmtB->execute([':dslug' => $selectedDistrictSlug]);
        $districtBlocks = $stmtB->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}
    
    // 2. Fetch all panchayats in this district
    try {
        $stmtP = $pdo->prepare("SELECT * FROM panchayats WHERE LOWER(district_slug) = :dslug ORDER BY id ASC");
        $stmtP->execute([':dslug' => $selectedDistrictSlug]);
        $allDistrictPanchayats = $stmtP->fetchAll(PDO::FETCH_ASSOC);

        // Group panchayats by block
        foreach ($districtBlocks as $blk) {
            $bSlug = slugify($blk['sub_district']);
            $blockPanchayatsMap[$bSlug] = [];
            foreach ($allDistrictPanchayats as $p) {
                if (isBlockMatch($p['block'], $blk['sub_district'])) {
                    $blockPanchayatsMap[$bSlug][] = $p;
                }
            }
        }
    } catch (Throwable $e) {
        $allDistrictPanchayats = [];
    }

    if (!empty($targetPanchayatQuery)) {
        // A. Check if user requested a specific Panchayat with block specified
        if (!empty($blockInput)) {
            foreach ($allDistrictPanchayats as $p) {
                if (isBlockMatch($p['block'], $blockInput) && isPanchayatMatch($p['panchayat_name'], $targetPanchayatQuery)) {
                    $singlePanchayat = $p;
                    break;
                }
            }
        }

        // B. Fallback: match panchayat name anywhere in the district
        if (!$singlePanchayat) {
            foreach ($allDistrictPanchayats as $p) {
                if (isPanchayatMatch($p['panchayat_name'], $targetPanchayatQuery)) {
                    $singlePanchayat = $p;
                    break;
                }
            }
        }

        // C. Fallback: check if target query was actually a Block name (e.g. /panchayat/saran/amnour)
        if (!$singlePanchayat) {
            $checkBlockQuery = !empty($blockInput) ? $blockInput : $targetPanchayatQuery;
            foreach ($districtBlocks as $blk) {
                if (isBlockMatch($blk['sub_district'], $checkBlockQuery) || slugify($blk['sub_district']) === slugify($checkBlockQuery)) {
                    $singleBlockObj = $blk;
                    $bSlug = slugify($blk['sub_district']);
                    $panchayatsInBlock = $blockPanchayatsMap[$bSlug] ?? [];
                    break;
                }
            }
        }
    } elseif (!empty($blockInput)) {
        // Block was requested without specific panchayat
        foreach ($districtBlocks as $blk) {
            if (isBlockMatch($blk['sub_district'], $blockInput) || slugify($blk['sub_district']) === slugify($blockInput)) {
                $singleBlockObj = $blk;
                $bSlug = slugify($blk['sub_district']);
                $panchayatsInBlock = $blockPanchayatsMap[$bSlug] ?? [];
                break;
            }
        }
    }

    // If single panchayat matched, gather all related Tier-1, Tier-2, and Tier-3 governance data
    if ($singlePanchayat) {
        $currentBlockName = $singlePanchayat['block'];

        // Gather sibling panchayats in the same block
        foreach ($allDistrictPanchayats as $p) {
            if (isBlockMatch($p['block'], $currentBlockName) && $p['id'] !== $singlePanchayat['id']) {
                $panchayatsInSameBlock[] = $p;
            }
        }

        // Tier-2 Panchayat Samiti (Pramukh / Up-Pramukh) for this block
        try {
            $stmtSamiti = $pdo->prepare("SELECT * FROM panchayat_samiti_2016 WHERE LOWER(district_slug) = :dslug");
            $stmtSamiti->execute([':dslug' => $selectedDistrictSlug]);
            $samitiRows = $stmtSamiti->fetchAll(PDO::FETCH_ASSOC);
            foreach ($samitiRows as $sr) {
                if (isBlockMatch($sr['block'], $currentBlockName) || (!empty($blockInput) && isBlockMatch($sr['block'], $blockInput))) {
                    $blockSamiti = $sr;
                    break;
                }
            }
        } catch (Throwable $e) {}

        // Tier-3 Zila Parishad representation
        // User rule: "If jila parisad not specified show all zila parisad of block"
        $terrNo = trim($singlePanchayat['zila_parishad_territory_no'] ?? '');
        if (!empty($terrNo)) {
            try {
                $stmtZp = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE LOWER(district_slug) = :dslug AND (territory_no = :terr OR ward_no = :terr)");
                $stmtZp->execute([':dslug' => $selectedDistrictSlug, ':terr' => $terrNo]);
                $matchedZp = $stmtZp->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($matchedZp)) {
                    $zilaParishadMembers = $matchedZp;
                    $isSpecificZilaTerritory = true;
                }
            } catch (Throwable $e) {}
        }

        // If not specified or no territory matched, fetch ALL Zila Parishad members representing this block
        if (empty($zilaParishadMembers)) {
            try {
                $stmtZpAll = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE LOWER(district_slug) = :dslug ORDER BY CAST(territory_no AS UNSIGNED) ASC");
                $stmtZpAll->execute([':dslug' => $selectedDistrictSlug]);
                $allZpDist = $stmtZpAll->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allZpDist as $zm) {
                    if (isBlockMatch($zm['block'] ?? '', $currentBlockName) || (!empty($blockInput) && isBlockMatch($zm['block'] ?? '', $blockInput))) {
                        $zilaParishadMembers[] = $zm;
                    }
                }
            } catch (Throwable $e) {}
        }
    }

    // If single block matched, gather block samiti & ZP representation
    if ($singleBlockObj && !$singlePanchayat) {
        $bName = $singleBlockObj['sub_district'];
        try {
            $stmtSamiti = $pdo->prepare("SELECT * FROM panchayat_samiti_2016 WHERE LOWER(district_slug) = :dslug");
            $stmtSamiti->execute([':dslug' => $selectedDistrictSlug]);
            $samitiRows = $stmtSamiti->fetchAll(PDO::FETCH_ASSOC);
            foreach ($samitiRows as $sr) {
                if (isBlockMatch($sr['block'], $bName)) {
                    $blockSamiti = $sr;
                    break;
                }
            }
        } catch (Throwable $e) {}

        try {
            $stmtZpAll = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE LOWER(district_slug) = :dslug ORDER BY CAST(territory_no AS UNSIGNED) ASC");
            $stmtZpAll->execute([':dslug' => $selectedDistrictSlug]);
            $allZpDist = $stmtZpAll->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allZpDist as $zm) {
                if (isBlockMatch($zm['block'] ?? '', $bName)) {
                    $zilaParishadMembers[] = $zm;
                }
            }
        } catch (Throwable $e) {}
    }
}

// Pre-fetch district-level aggregated statistics if browsing district or state
$districtCensusStats = [];
$districtPanchayatCounts = [];

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT district_slug, COUNT(*) as total_blocks, SUM(population) as total_pop, AVG(literacy_rate) as avg_literacy, SUM(households) as total_households FROM census_subdistricts GROUP BY district_slug");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $districtCensusStats[strtolower($row['district_slug'])] = $row;
        }

        $stmt2 = $pdo->query("SELECT district_slug, COUNT(*) as total_panchayats FROM panchayats GROUP BY district_slug");
        while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
            $districtPanchayatCounts[strtolower($row2['district_slug'])] = intval($row2['total_panchayats']);
        }
    } catch (Throwable $e) {}
}

// State Totals
$totalStateBlocks = 534;
$totalStatePanchayats = array_sum($districtPanchayatCounts) ?: 8406;
$totalStatePopulation = 104099452;

// Page Meta & SEO
if ($singlePanchayat && $districtObj) {
    $pName = $singlePanchayat['panchayat_name'];
    $bName = $singlePanchayat['block'];
    $dName = $districtObj['name'];
    $mName = $singlePanchayat['current_mukhiya'] ?: 'Elected Mukhiya';
    $sName = $singlePanchayat['current_sarpanch'] ?: 'Elected Sarpanch';

    $pageTitle = "{$pName} Gram Panchayat Profile, {$bName} Block ({$dName}) - Bihar Panchayati Raj";
    $pageDescription = "Official profile of {$pName} Gram Panchayat in {$bName} Block, {$dName} District. Explore elected Mukhiya ({$mName}), Sarpanch ({$sName}), Panchayat Samiti Pramukh, and Zila Parishad representation.";
    $pageCanonical = getPanchayatUrl($districtObj['slug'], slugify($bName), slugify($pName));
} elseif ($singleBlockObj && $districtObj) {
    $bName = $singleBlockObj['sub_district'];
    $dName = $districtObj['name'];
    $pCount = count($panchayatsInBlock);

    $pageTitle = "{$bName} Block Gram Panchayats Directory ({$pCount} Panchayats, {$dName}) - Bihar Panchayati Raj";
    $pageDescription = "Explore all {$pCount} Gram Panchayats in {$bName} Block, {$dName} District. View elected Mukhiyas, Sarpanchs, demographics, and Panchayat Samiti representatives.";
    $pageCanonical = getBlockUrl($districtObj['slug'], slugify($bName));
} elseif ($districtObj) {
    $pageTitle = "{$districtObj['name']} District CD Blocks & Panchayati Raj Directory (534 Blocks)";
    $pageDescription = "Explore {$districtObj['name']} District Community Development (CD) Blocks, Gram Panchayats distribution, census demographics, literacy rates, and administrative directory.";
    $pageCanonical = getPanchayatUrl($districtObj['slug']);
} else {
    $pageTitle = "Bihar Panchayati Raj & CD Blocks Directory: 38 Districts & 534 Blocks";
    $pageDescription = "Complete administrative directory of Bihar Panchayati Raj: 38 Districts, 534 Community Development (CD) Blocks, and 8,400+ Gram Panchayats with demographics and local governance rosters.";
    $pageCanonical = getPanchayatUrl();
}

// Build Division counts for filter badges
$divisionsList = ['Patna', 'Tirhut', 'Saran', 'Magadh', 'Darbhanga', 'Kosi', 'Purnia', 'Bhagalpur', 'Munger'];
$divisionCounts = [];
foreach ($districts as $d) {
    $dDiv = trim(preg_replace('/\s+division$/i', '', $d['division'] ?? ''));
    if ($dDiv) {
        $divisionCounts[$dDiv] = ($divisionCounts[$dDiv] ?? 0) + 1;
    }
}

$activeNav = 'panchayat';
require_once __DIR__ . '/header.php';
?>

<style>
/* Division Pill Buttons */
.division-pill-btn {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 50px;
    padding: 0.4rem 0.9rem;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #495057;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.division-pill-btn:hover {
    background-color: #f8f9fa;
    border-color: var(--accent-saffron, #ff9933);
    color: var(--navy-dark, #0b1a30);
    transform: translateY(-1px);
}
.division-pill-btn.active {
    background: linear-gradient(135deg, #0b1a30 0%, #17345f 100%);
    border-color: #0b1a30;
    color: #fff;
    box-shadow: 0 4px 10px rgba(11, 26, 48, 0.2);
}
.division-pill-btn.active .division-badge-count {
    background-color: var(--accent-saffron, #ff9933);
    color: #000;
}
.division-badge-count {
    font-size: 0.72rem;
    padding: 0.15rem 0.45rem;
    border-radius: 50px;
    background-color: #e9ecef;
    color: #495057;
    font-weight: 700;
}

/* Administrative & Profile Cards */
.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
    transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    overflow: hidden;
}
.admin-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(11, 26, 48, 0.08);
    border-color: #cbd5e1;
}
.admin-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0b1a30 0%, var(--accent-saffron, #ff9933) 100%);
    opacity: 0.85;
}
.admin-card-block::before {
    background: linear-gradient(90deg, var(--accent-saffron, #ff9933) 0%, #198754 100%);
}
.metric-mini-box {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.75rem 0.65rem;
    border: 1px solid #f1f5f9;
    text-align: center;
}

/* Tier Profile Cards */
.tier-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.tier-card-mukhiya { border-top: 5px solid #ff9933; }
.tier-card-sarpanch { border-top: 5px solid #0d6efd; }
.tier-card-samiti { border-top: 5px solid #198754; }
.tier-card-zila { border-top: 5px solid #6f42c1; }

.tier-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.85rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tier-avatar-box {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}
.profile-prop-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.45rem 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 0.88rem;
}
.profile-prop-row:last-child {
    border-bottom: none;
}
.sibling-panchayat-pill {
    display: inline-block;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.45rem 0.9rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all 0.2s ease;
}
.sibling-panchayat-pill:hover {
    background: #0b1a30;
    color: #fff;
    border-color: #0b1a30;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(11, 26, 48, 0.15);
}
</style>

<!-- Hero Header Section -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0 bg-white bg-opacity-10 px-3 py-2 rounded-pill d-inline-flex border border-white border-opacity-10">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <?php if ($singlePanchayat && $districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl(); ?>" class="text-white-50 text-decoration-none">Panchayats</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getDistrictUrl($districtObj['slug']); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($singlePanchayat['block']); ?> Block</a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?></li>
                <?php elseif ($singleBlockObj && $districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl(); ?>" class="text-white-50 text-decoration-none">Panchayats</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($singleBlockObj['sub_district']); ?> Block</li>
                <?php elseif ($districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl(); ?>" class="text-white-50 text-decoration-none">Panchayati Raj &amp; CD Blocks</a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($districtObj['name']); ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Panchayati Raj &amp; CD Blocks</li>
                <?php endif; ?>
            </ol>
        </nav>

        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            <?php if ($singlePanchayat && $districtObj): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🌾 Gram Panchayat Profile
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    📍 <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🏢 <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
                <?php if (!empty($singlePanchayat['delimitation_status'])): ?>
                    <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                        ✅ <?php echo htmlspecialchars($singlePanchayat['delimitation_status']); ?>
                    </span>
                <?php endif; ?>
            <?php elseif ($singleBlockObj && $districtObj): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    📍 CD Block Panchayats Hub
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🏢 <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🌾 <?php echo count($panchayatsInBlock); ?> Gram Panchayats
                </span>
            <?php elseif ($districtObj): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🏛️ Panchayati Raj &amp; CD Blocks
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    38 Districts
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    534 CD Blocks
                </span>
            <?php else: ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🏛️ Panchayati Raj &amp; CD Blocks
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    38 Districts
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    534 CD Blocks
                </span>
                <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    8,400+ Gram Panchayats
                </span>
            <?php endif; ?>
        </div>

        <h1 class="display-6 fw-extrabold text-white mb-2">
            <?php if ($singlePanchayat && $districtObj): ?>
                <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?> Gram Panchayat <br>
                <span style="color: var(--accent-saffron);">
                    <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block, <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
            <?php elseif ($singleBlockObj && $districtObj): ?>
                <?php echo htmlspecialchars($singleBlockObj['sub_district']); ?> Block Gram Panchayats <br>
                <span style="color: var(--accent-saffron);">
                    <?php echo count($panchayatsInBlock); ?> Panchayats, <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
            <?php elseif ($districtObj): ?>
                <?php echo htmlspecialchars($districtObj['name']); ?> District CD Blocks &amp; Panchayats <br>
                <span style="color: var(--accent-saffron);"><?php echo htmlspecialchars($districtObj['name_hi'] ?? ''); ?> Panchayati Raj Directory</span>
            <?php else: ?>
                Bihar Panchayati Raj &amp; CD Blocks Directory <br>
                <span style="color: var(--accent-saffron);">38 Districts &amp; 534 Community Development Blocks</span>
            <?php endif; ?>
        </h1>

        <p class="lead text-white-50 mb-4" style="max-width: 840px; font-size: 1.05rem;">
            <?php if ($singlePanchayat && $districtObj): ?>
                Complete governance dossier of <strong><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?></strong> Gram Panchayat: elected Mukhiya (ग्राम प्रधान), Sarpanch (न्याय पीठ), Block Samiti Pramukh, and Zila Parishad territorial representation in <?php echo htmlspecialchars($districtObj['name']); ?>, Bihar.
            <?php elseif ($singleBlockObj && $districtObj): ?>
                Explore all <strong><?php echo count($panchayatsInBlock); ?> Gram Panchayats</strong> in <?php echo htmlspecialchars($singleBlockObj['sub_district']); ?> Block. View elected Mukhiyas, Sarpanchs, local demographics, and direct links to each Panchayat profile.
            <?php elseif ($districtObj): ?>
                Explore all Community Development (CD) Blocks, Gram Panchayats distribution, Census 2011 demographics, and official district &amp; block portals in <?php echo htmlspecialchars($districtObj['name']); ?> District.
            <?php else: ?>
                Explore Bihar's 3-tier local governance structure across all 38 Districts and 534 CD Blocks. Access block demographics, gram panchayat rosters, census statistics, and representative directories.
            <?php endif; ?>
        </p>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($singlePanchayat && $districtObj): ?>
                <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building-check me-1"></i> All <?php echo htmlspecialchars($singlePanchayat['block']); ?> Panchayats
                </a>
                <a href="<?php echo getBlockUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block Hub
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> Block Samiti
                </a>
                <a href="<?php echo getZilaParishadUrl($districtObj['slug']); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> Zila Parishad
                </a>
            <?php elseif ($singleBlockObj && $districtObj): ?>
                <a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> All <?php echo htmlspecialchars($districtObj['name']); ?> Blocks
                </a>
                <a href="<?php echo getBlockUrl($districtObj['slug'], slugify($singleBlockObj['sub_district'])); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> Block Demographics
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($districtObj['slug'], slugify($singleBlockObj['sub_district'])); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people me-1"></i> Samiti Pramukh
                </a>
                <a href="<?php echo getZilaParishadUrl($districtObj['slug']); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> Zila Parishad
                </a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/blocks" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> All 534 Blocks Directory
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($selectedDistrictSlug); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> Panchayat Samiti
                </a>
                <a href="<?php echo getZilaParishadUrl($selectedDistrictSlug); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> Zila Parishad Boards
                </a>
                <a href="<?php echo getCensusUrl($selectedDistrictSlug); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-bar-chart-fill me-1"></i> Census Demographics
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Main Content Area -->
<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <?php if ($singlePanchayat && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- 1. COMPLETE DEDICATED GRAM PANCHAYAT PROFILE VIEW             -->
        <!-- ============================================================= -->
        
        <!-- Quick Administrative Overview Ribbon -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-start border-4 border-warning">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            🏢 <?php echo htmlspecialchars(trim(preg_replace('/\s+division$/i', '', $districtObj['division'] ?? 'Bihar'))); ?> Division
                        </span>
                        <span class="badge bg-warning-subtle text-dark fw-bold px-2.5 py-1 rounded-pill small">
                            📍 <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block
                        </span>
                        <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                            District HQ: <?php echo htmlspecialchars($districtObj['headquarters'] ?? $districtObj['name']); ?>
                        </span>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?>
                        <?php if (!empty($singlePanchayat['panchayat_hi']) && $singlePanchayat['panchayat_hi'] !== $singlePanchayat['panchayat_name']): ?>
                            <span class="text-muted fs-4 fw-normal">(<?php echo htmlspecialchars($singlePanchayat['panchayat_hi']); ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted small mb-0">
                        Official Gram Panchayat Profile &bull; 3-Tier Local Self Governance Structure (PRIs)
                    </p>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-warning rounded-pill px-3 py-1.5 fw-bold btn-sm text-dark shadow-sm">
                            <i class="bi bi-building-check me-1"></i> All <?php echo htmlspecialchars($singlePanchayat['block']); ?> Panchayats
                        </a>
                        <a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                            All Blocks
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Core Governance Pillars: Mukhiya, Sarpanch, Samiti, Zila Parishad -->
        <div class="row g-4 mb-4">
            
            <!-- 1. MUKHIYA CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-mukhiya p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box bg-warning bg-opacity-10 text-warning">
                                👑
                            </div>
                            <div>
                                <span class="tier-header-badge bg-warning bg-opacity-15 text-dark mb-1">
                                    Tier 1 &bull; Village Executive
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Gram Mukhiya (मुखिया)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small">
                            Incumbent
                        </span>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <div class="text-muted small">Elected Mukhiya Name</div>
                                <h5 class="fw-bold text-dark mb-0 fs-5">
                                    <?php echo htmlspecialchars($singlePanchayat['current_mukhiya'] ?: 'Details Available'); ?>
                                </h5>
                            </div>
                            <?php if (!empty($singlePanchayat['mukhiya_mobile'])): ?>
                                <div>
                                    <?php echo renderMaskedPhoneButton($singlePanchayat['mukhiya_mobile'], ($singlePanchayat['current_mukhiya'] ?? 'Mukhiya') . ' (Mukhiya - ' . $singlePanchayat['panchayat_name'] . ')'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($singlePanchayat['mukhiya_address'])): ?>
                            <div class="small text-muted border-top pt-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <strong>Address:</strong> <?php echo htmlspecialchars($singlePanchayat['mukhiya_address']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mukhiya Profile Attributes -->
                    <div class="px-1 mb-3">
                        <?php if (!empty($singlePanchayat['mukhiya_father_husband'])): ?>
                            <div class="profile-prop-row">
                                <span class="text-muted"><i class="bi bi-person me-1"></i> Father / Husband:</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['mukhiya_father_husband']); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-gender-ambiguous me-1"></i> Gender &amp; Age:</span>
                            <span class="fw-semibold text-dark">
                                <?php 
                                $mGen = $singlePanchayat['mukhiya_gender_hi'] ?: $singlePanchayat['mukhiya_gender'] ?: 'N/A';
                                $mAge = !empty($singlePanchayat['mukhiya_age']) ? "{$singlePanchayat['mukhiya_age']} Years" : '';
                                echo htmlspecialchars(trim("{$mGen} " . ($mAge ? "({$mAge})" : ''))); 
                                ?>
                            </span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-tag me-1"></i> Social Category:</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['mukhiya_category'] ?: 'General'); ?></span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-patch-check me-1"></i> Seat Reservation:</span>
                            <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">
                                <?php echo htmlspecialchars($singlePanchayat['mukhiya_reservation'] ?: $singlePanchayat['reservation_2026_mukhiya'] ?: 'General / Unreserved'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 2016 Ex-Mukhiya Archive Section -->
                    <?php if (!empty($singlePanchayat['mukhiya_2016'])): ?>
                        <div class="mt-auto pt-3 border-top">
                            <div class="p-2.5 bg-white rounded-3 border small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary-subtle text-secondary fw-bold" style="font-size: 0.7rem;">2016–2021 Tenure Record</span>
                                    <?php if (!empty($singlePanchayat['mukhiya_2016_mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($singlePanchayat['mukhiya_2016_mobile'], $singlePanchayat['mukhiya_2016'] . ' (Ex-Mukhiya)'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-dark">
                                    <strong>Ex-Mukhiya:</strong> <?php echo htmlspecialchars($singlePanchayat['mukhiya_2016']); ?>
                                    <?php if (!empty($singlePanchayat['mukhiya_2016_f_name'])): ?>
                                        <span class="text-muted">(Father: <?php echo htmlspecialchars($singlePanchayat['mukhiya_2016_f_name']); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. SARPANCH CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-sarpanch p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box bg-primary bg-opacity-10 text-primary">
                                ⚖️
                            </div>
                            <div>
                                <span class="tier-header-badge bg-primary bg-opacity-15 text-primary mb-1">
                                    Tier 1 &bull; Gram Kutchery Judiciary
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Gram Sarpanch (सरपंच)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            Judicial Head
                        </span>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <div class="text-muted small">Elected Sarpanch Name</div>
                                <h5 class="fw-bold text-dark mb-0 fs-5">
                                    <?php echo htmlspecialchars($singlePanchayat['current_sarpanch'] ?: 'Details Available'); ?>
                                </h5>
                            </div>
                            <?php if (!empty($singlePanchayat['sarpanch_mobile'])): ?>
                                <div>
                                    <?php echo renderMaskedPhoneButton($singlePanchayat['sarpanch_mobile'], ($singlePanchayat['current_sarpanch'] ?? 'Sarpanch') . ' (Sarpanch - ' . $singlePanchayat['panchayat_name'] . ')'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($singlePanchayat['sarpanch_address'])): ?>
                            <div class="small text-muted border-top pt-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <strong>Address:</strong> <?php echo htmlspecialchars($singlePanchayat['sarpanch_address']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sarpanch Profile Attributes -->
                    <div class="px-1 mb-3">
                        <?php if (!empty($singlePanchayat['sarpanch_father_husband'])): ?>
                            <div class="profile-prop-row">
                                <span class="text-muted"><i class="bi bi-person me-1"></i> Father / Husband:</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['sarpanch_father_husband']); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-gender-ambiguous me-1"></i> Gender &amp; Age:</span>
                            <span class="fw-semibold text-dark">
                                <?php 
                                $sGen = $singlePanchayat['sarpanch_gender_hi'] ?: $singlePanchayat['sarpanch_gender'] ?: 'N/A';
                                $sAge = !empty($singlePanchayat['sarpanch_age']) ? "{$singlePanchayat['sarpanch_age']} Years" : '';
                                echo htmlspecialchars(trim("{$sGen} " . ($sAge ? "({$sAge})" : ''))); 
                                ?>
                            </span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-tag me-1"></i> Social Category:</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['sarpanch_category'] ?: 'General'); ?></span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-patch-check me-1"></i> Seat Reservation:</span>
                            <span class="badge bg-primary bg-opacity-25 text-navy fw-bold">
                                <?php echo htmlspecialchars($singlePanchayat['sarpanch_reservation'] ?: $singlePanchayat['reservation_2026_sarpanch'] ?: 'General / Unreserved'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Presides over Gram Kutchery dispute resolutions</span>
                        <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                            All <?php echo htmlspecialchars($singlePanchayat['block']); ?> Panchayats &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. PANCHAYAT SAMITI CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-samiti p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box bg-success bg-opacity-10 text-success">
                                🏛️
                            </div>
                            <div>
                                <span class="tier-header-badge bg-success bg-opacity-15 text-success mb-1">
                                    Tier 2 &bull; Block Level Samiti
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Panchayat Samiti (प्रखंड प्रमुख)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small">
                            <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block
                        </span>
                    </div>

                    <p class="text-muted small mb-3">
                        Intermediate Tier-2 governance unit coordinating Gram Panchayats across <strong><?php echo htmlspecialchars($singlePanchayat['block']); ?> CD Block</strong>.
                    </p>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="mb-2">
                            <span class="text-muted small d-block">Block Pramukh (प्रखंड प्रमुख)</span>
                            <h5 class="fw-bold text-navy mb-0 fs-5">
                                <?php echo htmlspecialchars($blockSamiti['pramukh_2016'] ?? 'Pramukh Appointed'); ?>
                            </h5>
                        </div>

                        <?php if (!empty($blockSamiti['up_pramukh_2016'])): ?>
                            <div class="border-top pt-2 mt-2">
                                <span class="text-muted small d-block">Up-Pramukh (उप प्रमुख)</span>
                                <div class="fw-semibold text-dark">
                                    <?php echo htmlspecialchars($blockSamiti['up_pramukh_2016']); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="profile-prop-row">
                        <span class="text-muted"><i class="bi bi-geo me-1"></i> Block Jurisdiction:</span>
                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['block']); ?> Sub-District</span>
                    </div>

                    <div class="profile-prop-row">
                        <span class="text-muted"><i class="bi bi-diagram-3 me-1"></i> Governance Level:</span>
                        <span class="badge bg-success bg-opacity-25 text-success fw-bold">Panchayat Samiti (प्रखंड स्तर)</span>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="<?php echo getPanchayatSamitiUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-success rounded-pill px-3 text-white fw-semibold">
                            Samiti Details &rarr;
                        </a>
                        <a href="<?php echo getBlockUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                            Visit Block Hub
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. ZILA PARISHAD CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-zila p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box" style="background-color: #f3e8ff; color: #7c3aed;">
                                🏢
                            </div>
                            <div>
                                <span class="tier-header-badge bg-primary-subtle text-primary mb-1">
                                    Tier 3 &bull; Apex District Board
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Zila Parishad (जिला परिषद सदस्य)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            <?php echo htmlspecialchars($districtObj['name']); ?> ZP
                        </span>
                    </div>

                    <?php if (!empty($zilaParishadMembers)): ?>
                        <div class="mb-3">
                            <div class="small text-muted mb-2">
                                <?php if ($isSpecificZilaTerritory): ?>
                                    Territorial Member representing Territory No. <strong><?php echo htmlspecialchars($terrNo); ?></strong>:
                                <?php else: ?>
                                    Elected Zila Parishad Territorial Members representing <strong><?php echo htmlspecialchars($singlePanchayat['block']); ?> Block</strong>:
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($zilaParishadMembers as $zm): ?>
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                            <div>
                                                <div class="fw-bold text-navy fs-6">
                                                    <?php echo htmlspecialchars($zm['candidate_name'] ?? $zm['member_name'] ?? 'Zila Parishad Member'); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    Territory No: <strong><?php echo htmlspecialchars($zm['territory_no'] ?? $zm['ward_no'] ?? 'N/A'); ?></strong>
                                                    &bull; Category: <strong><?php echo htmlspecialchars($zm['category'] ?? 'General'); ?></strong>
                                                    <?php if (!empty($zm['gender'])): ?>
                                                        &bull; <?php echo htmlspecialchars($zm['gender']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($zm['mobile'])): ?>
                                                <div>
                                                    <?php echo renderMaskedPhoneButton($zm['mobile'], ($zm['candidate_name'] ?? 'ZP Member') . ' (ZP Territory ' . ($zm['territory_no'] ?? '') . ')'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($zm['address'])): ?>
                                            <div class="small text-muted border-top pt-1 mt-1">
                                                <i class="bi bi-geo-alt me-1 text-danger"></i> <?php echo htmlspecialchars($zm['address']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border text-center py-4">
                            <i class="bi bi-bank text-muted fs-2 d-block mb-1"></i>
                            <div class="fw-bold text-dark mb-1">Zila Parishad Territorial Representative</div>
                            <p class="text-muted small mb-0">Under <?php echo htmlspecialchars($districtObj['name']); ?> Zila Parishad Board jurisdiction.</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Highest tier of Panchayati Raj in Bihar</span>
                        <a href="<?php echo getZilaParishadUrl($districtObj['slug']); ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                            District ZP Board &rarr;
                        </a>
                    </div>
                </div>
            </div>

        </div>

        <!-- Sibling Panchayats in this Block Navigation Section -->
        <?php if (!empty($panchayatsInSameBlock)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            Block Navigation
                        </span>
                        <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                            Other Gram Panchayats in <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block (<?php echo count($panchayatsInSameBlock); ?>)
                        </h4>
                    </div>
                    <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        View All in <?php echo htmlspecialchars($singlePanchayat['block']); ?> &rarr;
                    </a>
                </div>
                
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($panchayatsInSameBlock as $sb): 
                        $sbUrl = getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block']), slugify($sb['panchayat_name']));
                    ?>
                        <a href="<?php echo htmlspecialchars($sbUrl); ?>" class="sibling-panchayat-pill">
                            🌾 <?php echo htmlspecialchars($sb['panchayat_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($singleBlockObj && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- 2. DEDICATED BLOCK PANCHAYATS DIRECTORY VIEW                  -->
        <!-- ============================================================= -->
        <?php 
        $bName = $singleBlockObj['sub_district'];
        $bSlug = slugify($bName);
        $bPop = intval($singleBlockObj['population'] ?? 0);
        $bLit = floatval($singleBlockObj['literacy_rate'] ?? 0);
        $bHouseholds = intval($singleBlockObj['households'] ?? 0);
        $bCode = $singleBlockObj['sub_dist_code'] ?? 'N/A';
        ?>

        <!-- Block Header & Summary Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            📍 CD Block Directory
                        </span>
                        <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                            Code: <?php echo htmlspecialchars($bCode); ?>
                        </span>
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small">
                            🏢 <?php echo htmlspecialchars($districtObj['name']); ?> District
                        </span>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($bName); ?> Block Gram Panchayats
                    </h2>
                    <p class="text-muted small mb-0">
                        Official directory of all <?php echo count($panchayatsInBlock); ?> Gram Panchayats, Mukhiyas, and Sarpanchs in <?php echo htmlspecialchars($bName); ?> Block.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> All <?php echo htmlspecialchars($districtObj['name']); ?> Blocks
                    </a>
                    <a href="<?php echo getBlockUrl($districtObj['slug'], $bSlug); ?>" class="btn btn-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm shadow-sm">
                        <i class="bi bi-geo-alt-fill me-1"></i> Block Demographics
                    </a>
                </div>
            </div>

            <!-- Block Quick Metrics -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-building-check text-success fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Gram Panchayats</span>
                        <span class="fs-4 fw-bold text-success"><?php echo count($panchayatsInBlock); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-people-fill text-primary fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Population</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo number_format($bPop); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-book-half text-warning fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Literacy Rate</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo $bLit ? "{$bLit}%" : 'N/A'; ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-house-door-fill text-info fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Households</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo number_format($bHouseholds); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar for Panchayats in this Block -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="panchayatSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Search panchayat, mukhiya, or sarpanch..." onkeyup="filterBlockPanchayats()">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <span class="badge bg-success text-white fw-bold px-3 py-2 rounded-pill fs-6" id="panchayatCountBadge">
                        Showing <?php echo count($panchayatsInBlock); ?> Panchayats
                    </span>
                </div>
            </div>
        </div>

        <!-- Grid of Panchayats in this Block -->
        <div class="row g-3 g-lg-4" id="panchayatsGrid">
            <?php foreach ($panchayatsInBlock as $p): 
                $pSlug = slugify($p['panchayat_name']);
                $pProfileUrl = getPanchayatUrl($districtObj['slug'], $bSlug, $pSlug);
                $pSearch = strtolower(($p['panchayat_name'] ?? '') . ' ' . ($p['current_mukhiya'] ?? '') . ' ' . ($p['current_sarpanch'] ?? '') . ' ' . ($p['mukhiya_category'] ?? ''));
            ?>
                <div class="col-12 col-md-6 col-lg-4 panchayat-card-item" data-search="<?php echo htmlspecialchars($pSearch, ENT_QUOTES); ?>">
                    <div class="admin-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-warning-subtle text-dark fw-bold px-2 py-0.5 rounded small mb-1" style="font-size: 0.72rem;">
                                        🌾 Gram Panchayat
                                    </span>
                                    <h4 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($pProfileUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                            <?php echo htmlspecialchars($p['panchayat_name']); ?>
                                        </a>
                                    </h4>
                                </div>
                                <?php if (!empty($p['reservation_2026_mukhiya'])): ?>
                                    <span class="badge bg-light text-muted border small px-2 py-1" style="font-size: 0.7rem;">
                                        <?php echo htmlspecialchars($p['reservation_2026_mukhiya']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Leaders Mini Roster -->
                            <div class="bg-light rounded-3 p-3 my-3 border small">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">👑 Mukhiya (मुखिया):</span>
                                        <strong class="text-dark fs-6"><?php echo htmlspecialchars($p['current_mukhiya'] ?: 'Details Available'); ?></strong>
                                    </div>
                                    <?php if (!empty($p['mukhiya_mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($p['mukhiya_mobile'], $p['current_mukhiya'] . ' (Mukhiya)'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">⚖️ Sarpanch (सरपंच):</span>
                                        <strong class="text-dark fs-6"><?php echo htmlspecialchars($p['current_sarpanch'] ?: 'Details Available'); ?></strong>
                                    </div>
                                    <?php if (!empty($p['sarpanch_mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($p['sarpanch_mobile'], $p['current_sarpanch'] . ' (Sarpanch)'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Link -->
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Tier 1 PRI Body</span>
                            <a href="<?php echo htmlspecialchars($pProfileUrl); ?>" class="btn btn-sm btn-primary fw-bold text-white rounded-pill px-3 py-1.5 shadow-sm">
                                View Profile <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <script>
        function filterBlockPanchayats() {
            const query = document.getElementById('panchayatSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.panchayat-card-item');
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

            const badge = document.getElementById('panchayatCountBadge');
            if (badge) {
                badge.innerText = 'Showing ' + visibleCount + ' Panchayats';
            }
        }
        </script>

    <?php elseif (!empty($selectedDistrictSlug) && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- 3. SELECTED DISTRICT: BLOCKS & PANCHAYATI RAJ DIRECTORY       -->
        <!-- ============================================================= -->
        <?php 
        $dStats = $districtCensusStats[$selectedDistrictSlug] ?? [];
        $dTotalBlocks = count($districtBlocks) ?: ($dStats['total_blocks'] ?? 0);
        $dTotalPanchayats = $districtPanchayatCounts[$selectedDistrictSlug] ?? count($allDistrictPanchayats);
        $dPop = intval($dStats['total_pop'] ?? $districtObj['population'] ?? 0);
        $dLit = round(floatval($dStats['avg_literacy'] ?? 0), 1);
        $dHouseholds = intval($dStats['total_households'] ?? 0);
        $cleanDivision = trim(preg_replace('/\s+division$/i', '', $districtObj['division'] ?? 'Bihar'));
        ?>

        <!-- District Header & Summary Statistics Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">🏢 <?php echo htmlspecialchars($cleanDivision); ?> Division</span>
                        <?php if (!empty($districtObj['headquarters'])): ?>
                            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">HQ: <?php echo htmlspecialchars($districtObj['headquarters']); ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($districtObj['name']); ?> District
                        <?php if (!empty($districtObj['name_hi'])): ?>
                            <span class="text-muted fs-4 fw-normal">(<?php echo htmlspecialchars($districtObj['name_hi']); ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted small mb-0">
                        CD Blocks &amp; Gram Panchayats Administrative Directory of <?php echo htmlspecialchars($districtObj['name']); ?>
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-grid-fill me-1"></i> Browse All 38 Districts
                    </a>
                    <a href="<?php echo getDistrictUrl($selectedDistrictSlug); ?>" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-building me-1"></i> Visit District Hub
                    </a>
                </div>
            </div>

            <!-- Key District Demographics & Panchayati Raj Metrics -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-geo-alt-fill text-primary fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">CD Blocks</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo number_format($dTotalBlocks); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-building-check text-success fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Gram Panchayats</span>
                        <span class="fs-4 fw-bold text-success"><?php echo $dTotalPanchayats ? number_format($dTotalPanchayats) : 'Mapped'; ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-people-fill text-info fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Population</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo number_format($dPop); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-book-half text-warning fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Avg Literacy</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo $dLit ? "{$dLit}%" : "N/A"; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar & Switch District Controls -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-6">
                    <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-search text-primary me-1"></i> Search CD Blocks in <?php echo htmlspecialchars($districtObj['name']); ?>:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="blockSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Type block name (e.g. Chapra, Amnour, Manjhi)..." onkeyup="filterBlocks()">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-arrow-repeat text-primary me-1"></i> Switch District:</label>
                    <select class="form-select form-select-sm rounded-pill d-inline-block w-auto" style="min-width: 240px;" onchange="if(this.value){ location.href = this.value; }">
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo getPanchayatUrl($d['slug']); ?>" <?php echo $selectedDistrictSlug === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?> District
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Counter Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill small">Sub-District Directory</span>
                <h3 class="fw-bold font-heading text-navy mt-1 fs-4 mb-0">Community Development Blocks in <?php echo htmlspecialchars($districtObj['name']); ?></h3>
            </div>
            <div class="badge bg-primary text-white fw-bold fs-6 px-3 py-2 rounded-pill shadow-sm" id="blockCountBadge">
                Showing <?php echo count($districtBlocks); ?> Blocks
            </div>
        </div>

        <!-- Redesigned Blocks Grid with Direct Panchayat View Options -->
        <div class="row g-3 g-lg-4" id="blocksGrid">
            <?php foreach ($districtBlocks as $blk): 
                $bSlug = slugify($blk['sub_district']);
                $bUrl = getBlockUrl($selectedDistrictSlug, $bSlug);
                $bPop = intval($blk['population'] ?? 0);
                $bMale = intval($blk['male'] ?? 0);
                $bFemale = intval($blk['female'] ?? 0);
                $bLit = floatval($blk['literacy_rate'] ?? 0);
                $bHouseholds = intval($blk['households'] ?? 0);
                $bCode = $blk['sub_dist_code'] ?? 'N/A';

                // Look up count of panchayats in this block
                $panchayatsInThisBlock = $blockPanchayatsMap[$bSlug] ?? [];
                $pCount = count($panchayatsInThisBlock);
                $bBlockPanchayatsUrl = getPanchayatUrl($selectedDistrictSlug, $bSlug);

                $searchStr = strtolower(($blk['sub_district'] ?? '') . ' ' . $bCode . ' ' . $districtObj['name']);
            ?>
                <div class="col-12 col-md-6 col-lg-4 block-stat-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>">
                    <div class="admin-card admin-card-block h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-warning-subtle text-dark fw-bold px-2 py-0.5 rounded small mb-1" style="font-size: 0.72rem;">
                                        📍 CD Block
                                    </span>
                                    <h4 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($bBlockPanchayatsUrl); ?>" class="text-decoration-none text-navy hover-primary" title="View all panchayats in <?php echo htmlspecialchars($blk['sub_district']); ?>">
                                            <?php echo htmlspecialchars($blk['sub_district']); ?>
                                        </a>
                                    </h4>
                                </div>
                                <span class="badge bg-light text-muted border small px-2 py-1">
                                    Code: <?php echo htmlspecialchars($bCode); ?>
                                </span>
                            </div>

                            <!-- Block Statistics Micro Dashboard -->
                            <div class="row g-2 my-3">
                                <div class="col-6">
                                    <a href="<?php echo htmlspecialchars($bBlockPanchayatsUrl); ?>" class="text-decoration-none d-block">
                                        <div class="metric-mini-box bg-success bg-opacity-10 border-success border-opacity-25 hover-shadow">
                                            <span class="text-success fw-bold d-block" style="font-size: 0.72rem;">Gram Panchayats</span>
                                            <strong class="text-success fs-6"><?php echo $pCount ? number_format($pCount) . ' Panchayats' : 'Mapped'; ?></strong>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Population</span>
                                        <strong class="text-dark fs-6"><?php echo number_format($bPop); ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Literacy Rate</span>
                                        <strong class="text-primary fs-6"><?php echo $bLit ? "{$bLit}%" : 'N/A'; ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Households</span>
                                        <strong class="text-dark fs-6"><?php echo number_format($bHouseholds); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Action Links with View Panchayats Option -->
                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-2">
                                <a href="<?php echo htmlspecialchars($bBlockPanchayatsUrl); ?>" class="btn btn-sm btn-success rounded-pill fw-bold px-3 py-1.5 small text-white shadow-sm" title="View all <?php echo $pCount; ?> Gram Panchayats">
                                    🌾 View <?php echo $pCount ? $pCount : ''; ?> Panchayats <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                                
                                <div class="d-flex gap-1">
                                    <a href="<?php echo getPanchayatSamitiUrl($selectedDistrictSlug, $bSlug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-2.5 py-1.5 small text-truncate" title="Panchayat Samiti Pramukh">
                                        <i class="bi bi-people me-1"></i> Samiti
                                    </a>
                                    <a href="<?php echo htmlspecialchars($bUrl); ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-2.5 py-1.5 small text-nowrap" title="CD Block Overview">
                                        Block Hub
                                    </a>
                                </div>
                            </div>

                            <?php if (!empty($panchayatsInThisBlock)): ?>
                                <!-- Collapsible Quick-View Drawer of Gram Panchayats -->
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-muted w-100 text-start mt-1 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#quickPanchayats_<?php echo $bSlug; ?>" aria-expanded="false">
                                    <span style="font-size: 0.75rem;"><i class="bi bi-grid-3x3-gap-fill text-success me-1"></i> Quick list (<?php echo $pCount; ?> Panchayats)</span>
                                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                                </button>
                                <div class="collapse mt-2 pt-2 border-top" id="quickPanchayats_<?php echo $bSlug; ?>">
                                    <div class="d-flex flex-wrap gap-1" style="max-height: 180px; overflow-y: auto;">
                                        <?php foreach ($panchayatsInThisBlock as $pItem): 
                                            $pItemUrl = getPanchayatUrl($selectedDistrictSlug, $bSlug, slugify($pItem['panchayat_name']));
                                        ?>
                                            <a href="<?php echo htmlspecialchars($pItemUrl); ?>" class="btn btn-sm btn-light border rounded-pill px-2 py-0.5 text-truncate" style="font-size: 0.75rem;" title="<?php echo htmlspecialchars($pItem['panchayat_name']); ?> (Mukhiya: <?php echo htmlspecialchars($pItem['current_mukhiya'] ?: 'N/A'); ?>)">
                                                🌾 <?php echo htmlspecialchars($pItem['panchayat_name']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noBlocksAlert" class="alert alert-info rounded-4 text-center py-5 d-none mt-4 shadow-sm">
            <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">No Block found in <?php echo htmlspecialchars($districtObj['name']); ?></h5>
            <p class="text-muted mb-3">Try searching for another block name or sub-district code.</p>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="clearBlockSearch()">Clear Search</button>
        </div>

        <script>
        function filterBlocks() {
            const query = document.getElementById('blockSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.block-stat-item');
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

    <?php else: ?>
        <!-- ============================================================= -->
        <!-- 4. ALL 38 DISTRICTS OVERVIEW & DIRECTORY                      -->
        <!-- ============================================================= -->
        
        <!-- State Overview Highlights -->
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Districts</small>
                        <i class="bi bi-building text-primary fs-4"></i>
                    </div>
                    <div class="fs-3 fw-bold text-navy">38</div>
                    <small class="text-muted">Administrative Districts</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">CD Blocks</small>
                        <i class="bi bi-geo-alt-fill text-warning fs-4"></i>
                    </div>
                    <div class="fs-3 fw-bold text-dark"><?php echo number_format($totalStateBlocks); ?></div>
                    <small class="text-muted">Sub-District Units</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Gram Panchayats</small>
                        <i class="bi bi-building-check text-success fs-4"></i>
                    </div>
                    <div class="fs-3 fw-bold text-success"><?php echo number_format($totalStatePanchayats); ?></div>
                    <small class="text-muted">Local Village Bodies</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase">Total Population</small>
                        <i class="bi bi-people-fill text-info fs-4"></i>
                    </div>
                    <div class="fs-3 fw-bold text-dark">10.41 Cr</div>
                    <small class="text-muted">Census Demographics</small>
                </div>
            </div>
        </div>

        <!-- Interactive Search & Division Filter Hub -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-primary">
            <div class="row g-3 align-items-center justify-content-between mb-3">
                <div class="col-12 col-md-7">
                    <h4 class="fw-bold text-navy font-heading mb-1 fs-5">
                        <i class="bi bi-funnel-fill text-primary me-1"></i> Browse Districts by Division or Search
                    </h4>
                    <p class="text-muted small mb-0">Select any division below or search by district name, headquarters, or Hindi name.</p>
                </div>
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="districtSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Type district name (e.g. Patna, Saran, Gaya)..." autocomplete="off" onkeyup="filterDistricts()">
                    </div>
                </div>
            </div>

            <!-- Division Quick Filter Pills -->
            <div class="d-flex flex-wrap gap-2 pt-3 border-top align-items-center" id="divisionPillsContainer">
                <span class="fw-bold text-muted small me-1"><i class="bi bi-geo-fill text-primary"></i> Division:</span>
                
                <button type="button" class="division-pill-btn active" data-division="all" onclick="filterDivision('all', this)">
                    All Bihar <span class="division-badge-count">38</span>
                </button>

                <?php foreach ($divisionsList as $div): 
                    $count = $divisionCounts[$div] ?? 0;
                ?>
                    <button type="button" class="division-pill-btn" data-division="<?php echo htmlspecialchars(strtolower($div)); ?>" onclick="filterDivision('<?php echo htmlspecialchars(strtolower($div)); ?>', this)">
                        <?php echo htmlspecialchars($div); ?> <span class="division-badge-count"><?php echo $count; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filter Counter Ribbon -->
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <span class="text-muted small fw-semibold" id="filterStatusText">Showing all 38 districts across Bihar</span>
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill small" id="districtCountBadge">38 Districts</span>
        </div>

        <!-- Redesigned 38 Districts Grid -->
        <div class="row g-3 g-lg-4" id="districtsGrid">
            <?php foreach ($districts as $d): 
                $dSlug = strtolower($d['slug']);
                $census = $districtCensusStats[$dSlug] ?? [];
                $bCount = intval($census['total_blocks'] ?? 0);
                $pCount = intval($districtPanchayatCounts[$dSlug] ?? 0);
                
                $rawDivision = $d['division'] ?? 'Bihar';
                $cleanDivision = trim(preg_replace('/\s+division$/i', '', $rawDivision));
                $divisionKey = strtolower($cleanDivision);
                
                $searchStr = strtolower($d['name'] . ' ' . ($d['name_hi'] ?? '') . ' ' . ($d['headquarters'] ?? '') . ' ' . $cleanDivision . ' ' . $rawDivision);
                $districtPanchayatUrl = getPanchayatUrl($dSlug);
                $districtHubUrl = getDistrictUrl($dSlug);
                $dPop = intval($d['population'] ?? $census['total_pop'] ?? 0);
            ?>
                <div class="col-12 col-md-6 col-lg-4 district-stat-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>" data-division="<?php echo htmlspecialchars($divisionKey, ENT_QUOTES); ?>">
                    <div class="admin-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header Info -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-0.5 rounded small mb-1" style="font-size: 0.72rem;">
                                        🏛️ <?php echo htmlspecialchars($cleanDivision); ?> Division
                                    </span>
                                    <h5 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($districtPanchayatUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </a>
                                        <?php if (!empty($d['name_hi'])): ?>
                                            <span class="text-muted fs-6 fw-normal ms-1">(<?php echo htmlspecialchars($d['name_hi']); ?>)</span>
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <span class="badge bg-light text-muted border small px-2 py-1">
                                    HQ: <?php echo htmlspecialchars($d['headquarters'] ?? $d['name']); ?>
                                </span>
                            </div>

                            <!-- Micro-Metrics Dashboard -->
                            <div class="row g-2 my-3">
                                <div class="col-4">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">CD Blocks</span>
                                        <strong class="text-dark fs-6"><?php echo $bCount ? number_format($bCount) : 'Mapped'; ?></strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Panchayats</span>
                                        <strong class="text-success fs-6"><?php echo $pCount ? number_format($pCount) : 'Mapped'; ?></strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Population</span>
                                        <strong class="text-primary fs-6"><?php echo $dPop ? round($dPop / 100000, 1) . 'L' : 'N/A'; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Links -->
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center gap-2">
                            <div class="d-flex gap-2">
                                <a href="<?php echo htmlspecialchars($districtHubUrl); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1 small" title="District Hub">
                                    <i class="bi bi-building"></i> Hub
                                </a>
                                <a href="<?php echo getZilaParishadUrl($dSlug); ?>" class="btn btn-sm btn-outline-info text-dark rounded-pill px-2.5 py-1 small" title="Zila Parishad">
                                    <i class="bi bi-bank"></i> Zila Parishad
                                </a>
                            </div>
                            <a href="<?php echo htmlspecialchars($districtPanchayatUrl); ?>" class="btn btn-sm btn-primary fw-bold text-white rounded-pill px-3 py-1.5 shadow-sm text-nowrap">
                                View Blocks <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noDistrictsAlert" class="alert alert-info rounded-4 text-center py-5 d-none mt-4 shadow-sm">
            <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">No District found</h5>
            <p class="text-muted mb-3">Try searching for another district name, headquarters, or reset the division filter.</p>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="clearDistrictSearch()">Reset All Filters</button>
        </div>

        <script>
        let currentDivision = 'all';

        function filterDivision(div, btn) {
            currentDivision = div.toLowerCase().trim();
            document.querySelectorAll('.division-pill-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            filterDistricts();
        }

        function filterDistricts() {
            const query = document.getElementById('districtSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.district-stat-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemSearch = item.getAttribute('data-search').toLowerCase();
                const itemDiv = (item.getAttribute('data-division') || '').toLowerCase();
                
                const matchQuery = query === '' || itemSearch.includes(query);
                const matchDiv = currentDivision === 'all' || itemDiv === currentDivision || itemDiv.includes(currentDivision);

                if (matchQuery && matchDiv) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });
            
            const badge = document.getElementById('districtCountBadge');
            if (badge) {
                badge.innerText = visibleCount + ' Districts';
            }

            const statusText = document.getElementById('filterStatusText');
            if (statusText) {
                if (currentDivision !== 'all') {
                    const divTitle = currentDivision.charAt(0).toUpperCase() + currentDivision.slice(1);
                    statusText.innerText = 'Showing ' + visibleCount + ' districts in ' + divTitle + ' Division';
                } else if (query !== '') {
                    statusText.innerText = 'Showing ' + visibleCount + ' matching districts';
                } else {
                    statusText.innerText = 'Showing all 38 districts across Bihar';
                }
            }

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
            currentDivision = 'all';
            document.querySelectorAll('.division-pill-btn').forEach(b => {
                if (b.getAttribute('data-division') === 'all') b.classList.add('active');
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
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark font-heading mb-1">Standardized Public &amp; Government Data Sources</h6>
                    <p class="text-muted small mb-0">Local Block boundaries, Gram Panchayat distributions, Census 2011 stats, and administrative rosters reference Census of India, LGD Portal, and SEC Bihar.</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/census" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-bar-chart-line me-1"></i>View Census Directory
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
