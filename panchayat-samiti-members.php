<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();

$districtInput = trim($_GET['district'] ?? '');
$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrict = $districtObj['slug'] ?? $districtInput;
$selectedBlock = trim($_GET['block'] ?? '');
$searchQuery = trim($_GET['q'] ?? '');
$page = max(1, intval($_GET['page'] ?? 1));
$limit = 50;

$pdo = Database::getConnection();

// Get total count in entire DB and per district (cached)
$samitiCounts = DataCache::get('ps_member_counts_summary', function() use ($pdo) {
    $total = 8752;
    $counts = [];
    try {
        if ($pdo) {
            $cStmt = $pdo->query("SELECT COUNT(*) FROM panchayat_samiti_member_2021");
            if ($cStmt) $total = intval($cStmt->fetchColumn());

            $dcStmt = $pdo->query("SELECT district_slug, COUNT(*) as cnt FROM panchayat_samiti_member_2021 GROUP BY district_slug");
            while ($dr = $dcStmt->fetch(PDO::FETCH_ASSOC)) {
                $counts[strtolower($dr['district_slug'])] = intval($dr['cnt']);
            }
        }
    } catch (Throwable $e) {}
    return ['total' => $total, 'counts' => $counts];
}, 86400);

$totalDbMembers = $samitiCounts['total'] ?? 8752;
$districtMemberCounts = $samitiCounts['counts'] ?? [];

$isDistrictSelected = !empty($selectedDistrict);
$isDistrictAvailable = $isDistrictSelected && (!empty($districtObj) || !empty($districtMemberCounts[strtolower($selectedDistrict)] ?? 0));

// Build filtered query ONLY if a district is selected
$where = [];
$params = [];
$totalMatched = 0;
$totalPages = 1;
$members = [];
$availableBlocks = [];

if ($isDistrictSelected) {
    $where[] = "(district_slug = :dslug OR district = :dname)";
    $params[':dslug'] = strtolower($selectedDistrict);
    $params[':dname'] = strtolower($selectedDistrict);

    if (!empty($selectedBlock)) {
        $where[] = "(LOWER(block) = LOWER(:bname) OR block LIKE :blike)";
        $params[':bname'] = $selectedBlock;
        $params[':blike'] = '%' . $selectedBlock . '%';
    }

    if (!empty($searchQuery)) {
        $where[] = "(candidate_name LIKE :q OR father_husband_name LIKE :q OR panchayat LIKE :q OR block LIKE :q OR district LIKE :q OR territory_no = :qexact OR reservation_status LIKE :q)";
        $params[':q'] = '%' . $searchQuery . '%';
        $params[':qexact'] = $searchQuery;
    }

    $whereSql = implode(" AND ", $where);

    // Count matching
    try {
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM panchayat_samiti_member_2021 WHERE {$whereSql}");
        $countStmt->execute($params);
        $totalMatched = intval($countStmt->fetchColumn());
    } catch (Throwable $e) {}

    $totalPages = max(1, ceil($totalMatched / $limit));
    if ($page > $totalPages) $page = $totalPages;
    $offset = ($page - 1) * $limit;

    // Fetch rows
    try {
        $stmt = $pdo->prepare("SELECT * FROM panchayat_samiti_member_2021 WHERE {$whereSql} ORDER BY block ASC, CAST(territory_no AS UNSIGNED) ASC, panchayat ASC LIMIT {$limit} OFFSET {$offset}");
        $stmt->execute($params);
        $members = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {}

    // Available blocks for selected district
    try {
        $cleanSlug = strtolower($selectedDistrict);
        $availableBlocks = DataCache::get("ps_blocks_{$cleanSlug}", function() use ($pdo, $cleanSlug) {
            $bStmt = $pdo->prepare("SELECT DISTINCT block FROM panchayat_samiti_member_2021 WHERE district_slug = :dslug ORDER BY block ASC");
            $bStmt->execute([':dslug' => $cleanSlug]);
            return $bStmt->fetchAll(PDO::FETCH_COLUMN);
        }, 86400);
    } catch (Throwable $e) {}
}

if (!empty($selectedBlock) && $districtObj) {
    $distLabel = "({$selectedBlock} Block, {$districtObj['name']} District)";
} elseif ($districtObj) {
    $distLabel = "({$districtObj['name']} District)";
} else {
    $distLabel = "38 Districts Directory";
}

$pageTitle = "Bihar Panchayat Samiti Members Directory 2021–2026 {$distLabel}: 8,752 Elected Members";
$pageDescription = "Official directory of elected Bihar Panchayat Samiti Members (पंचायत समिति सदस्य 2021-2026) across 534 blocks {$distLabel}. Explore candidates, territory constituencies, reservation, and contact details.";
$pageCanonical = SITE_URL . '/panchayat-samiti-members' . ($selectedDistrict ? '?district=' . urlencode($selectedDistrict) : '');

$activeNav = 'samiti';
require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-primary bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🏛️ Bihar Panchayati Raj Tier 2 (Block Level)
                </span>
                <span class="badge bg-warning bg-opacity-25 text-warning fw-bold px-3 py-2 rounded-pill">
                    Tenure: 2021–2026
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    <?php echo number_format($totalDbMembers); ?> Total Elected Members
                </span>
            </div>

            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/panchayat" class="text-white-50 text-decoration-none">Panchayats</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/panchayat-samiti" class="text-white-50 text-decoration-none">Panchayat Samiti</a></li>
                    <?php if ($districtObj && !empty($selectedBlock)): ?>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($selectedDistrict); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($selectedBlock); ?></li>
                    <?php elseif ($districtObj): ?>
                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($districtObj['name']); ?></li>
                    <?php else: ?>
                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Samiti Members</li>
                    <?php endif; ?>
                </ol>
            </nav>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Bihar Panchayat Samiti Members Directory: <br>
                <span style="color: var(--accent-saffron);">
                    <?php if (!empty($selectedBlock) && $districtObj): ?>
                        <?php echo htmlspecialchars($selectedBlock); ?> Block Samiti Members (पंचायत समिति सदस्य)
                    <?php elseif ($districtObj): ?>
                        <?php echo htmlspecialchars($districtObj['name']); ?> District Samiti Members
                    <?php else: ?>
                        Elected Panchayat Samiti Members (2021–2026)
                    <?php endif; ?>
                </span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                Official directory of 8,752 elected Panchayat Samiti Members (पंचायत समिति सदस्य) representing Gram Panchayats across all 534 Blocks and 38 Districts in Bihar.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo SITE_URL; ?>/panchayat-samiti" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-award-fill me-1"></i> Block Pramukh &amp; Up-Pramukh Roster
                </a>
                <a href="<?php echo getPanchayatUrl($selectedDistrict); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-building-check me-1"></i> Gram Panchayats Directory
                </a>
                <a href="<?php echo getZilaParishadUrl($selectedDistrict); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-bank me-1"></i> Zila Parishad Boards
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                    <i class="bi bi-whatsapp"></i> WhatsApp Updates
                </a>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="container my-4 my-lg-5">
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Filter Bar -->
        <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white border-top border-4 border-success">
            <form method="GET" action="<?php echo SITE_URL; ?>/panchayat-samiti-members" class="row g-2 align-items-center">
                <!-- District Dropdown -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-geo-alt"></i> Select District:</label>
                    <select name="district" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                        <option value="" disabled <?php echo empty($selectedDistrict) ? 'selected' : ''; ?>>-- Select District --</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo htmlspecialchars($d['slug']); ?>" <?php echo $selectedDistrict === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?> (<?php echo htmlspecialchars($d['name_hi'] ?? ''); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Block Dropdown (if district selected) -->
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-diagram-3"></i> Select Block:</label>
                    <select name="block" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()" <?php echo empty($availableBlocks) ? 'disabled' : ''; ?>>
                        <option value="">All Blocks</option>
                        <?php foreach ($availableBlocks as $b): ?>
                            <option value="<?php echo htmlspecialchars($b); ?>" <?php echo $selectedBlock === $b ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($b); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-search"></i> Search Member / GP / Ward:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control rounded-start-pill" placeholder="Name, Panchayat, or Territory #" value="<?php echo htmlspecialchars($searchQuery); ?>" <?php echo !$isDistrictSelected ? 'disabled' : ''; ?>>
                        <button type="submit" class="btn btn-success rounded-end-pill px-3" <?php echo !$isDistrictSelected ? 'disabled' : ''; ?>>Search</button>
                    </div>
                </div>

                <!-- Reset Button -->
                <div class="col-12 col-md-2 text-md-end pt-md-3">
                    <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members" class="btn btn-sm btn-outline-secondary rounded-pill px-3 w-100">
                        <i class="bi bi-arrow-repeat me-1"></i> Change District
                    </a>
                </div>
            </form>
        </div>

        <?php if ($isDistrictSelected && !$isDistrictAvailable): ?>
            <!-- District Not Available Alert -->
            <div class="card border-0 shadow-sm rounded-4 p-5 mb-4 bg-white text-center border-top border-4 border-warning">
                <div class="mb-3">
                    <span class="badge bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-exclamation-triangle fs-2"></i>
                    </span>
                </div>
                <h4 class="fw-bold text-navy mb-2">District Data Not Available</h4>
                <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">
                    Elected Panchayat Samiti Member records are currently not available or record update is in progress for 
                    <strong><?php echo htmlspecialchars($districtObj['name'] ?? $selectedDistrict); ?></strong>. 
                    Please select another district from the list below.
                </p>
                <div>
                    <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-arrow-left me-1"></i> Browse Available Districts
                    </a>
                </div>
            </div>
        <?php elseif (!$isDistrictSelected): ?>
            <!-- Prompt & 38 Districts Selection Grid -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-success">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-3 border-bottom">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small">
                                🏛️ District-Wise Roster
                            </span>
                            <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill small">
                                38 Districts of Bihar
                            </span>
                        </div>
                        <h3 class="fw-bold text-navy font-heading fs-4 mb-1">
                            Select a District to View Elected Panchayat Samiti Members
                        </h3>
                        <p class="text-muted small mb-0">
                            Please select a district from the dropdown above or choose from the 38 districts below to explore block-wise Panchayat Samiti Members (2021–2026).
                        </p>
                    </div>
                    <div class="badge bg-success text-white fw-bold px-3 py-2 rounded-pill fs-6 shadow-sm">
                        <?php echo number_format($totalDbMembers); ?> Total Members
                    </div>
                </div>

                <div class="row g-3">
                    <?php foreach ($districts as $d): 
                        $dSlug = strtolower($d['slug']);
                        $mCount = $districtMemberCounts[$dSlug] ?? 0;
                        $dUrl = SITE_URL . "/panchayat-samiti-members?district=" . urlencode($dSlug);
                    ?>
                        <div class="col-12 col-sm-6 col-md-4 col-lg-3">
                            <a href="<?php echo htmlspecialchars($dUrl); ?>" class="card h-100 p-3 rounded-4 border text-decoration-none bg-light bg-opacity-50 hover-shadow transition">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <h6 class="fw-bold text-navy mb-0">
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </h6>
                                    <span class="badge bg-success bg-opacity-15 text-success fw-bold rounded-pill small">
                                        <?php echo $mCount ? number_format($mCount) : 'Update Soon'; ?>
                                    </span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center small text-muted">
                                    <span><?php echo htmlspecialchars($d['name_hi'] ?? ''); ?></span>
                                    <span class="text-success fw-semibold">View &rarr;</span>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <!-- Members Table Card (when district is selected) -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-people text-success me-2"></i> 
                            <?php echo htmlspecialchars($districtObj['name'] ?? ucfirst($selectedDistrict)); ?> District: Elected Panchayat Samiti Members (2021–2026)
                        </h5>
                        <small class="text-muted">
                            <?php if (!empty($selectedBlock)): ?>
                                Filtered by <strong><?php echo htmlspecialchars($selectedBlock); ?> Block</strong> &bull; 
                            <?php endif; ?>
                            Showing <?php echo number_format($totalMatched); ?> elected members
                        </small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 rounded-pill">
                            Page <?php echo $page; ?> of <?php echo $totalPages; ?>
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="samitiMembersTable">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Territory #</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Gram Panchayat</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Block &amp; District</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Elected Member</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Father/Husband</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Reservation</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Contact</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $m): 
                                    $dSlug = strtolower($m['district_slug'] ?: slugify($m['district']));
                                    $bSlug = slugify($m['block']);
                                    $pSlug = slugify($m['panchayat']);
                                    $gpUrl = getPanchayatUrl($dSlug, $bSlug, $pSlug);
                                ?>
                                    <tr>
                                        <td class="px-3">
                                            <span class="badge bg-success bg-opacity-15 text-success fw-bold px-2 py-1 rounded-pill">
                                                #<?php echo htmlspecialchars($m['territory_no'] ?: '—'); ?>
                                            </span>
                                        </td>
                                        <td class="px-3 fw-bold">
                                            <a href="<?php echo htmlspecialchars($gpUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                                🌾 <?php echo htmlspecialchars($m['panchayat']); ?>
                                            </a>
                                        </td>
                                        <td class="px-3">
                                            <span class="fw-semibold text-dark d-block"><?php echo htmlspecialchars($m['block']); ?></span>
                                            <small class="text-muted">🏢 <?php echo htmlspecialchars($m['district']); ?></small>
                                        </td>
                                        <td class="px-3">
                                            <strong class="text-navy d-block fs-6"><?php echo htmlspecialchars($m['candidate_name']); ?></strong>
                                            <small class="text-muted">
                                                <?php echo htmlspecialchars($m['gender'] ?? ''); ?>
                                            </small>
                                        </td>
                                        <td class="px-3 text-muted small">
                                            <?php echo htmlspecialchars($m['father_husband_name'] ?: '—'); ?>
                                        </td>
                                        <td class="px-3">
                                            <span class="badge bg-light text-dark border small px-2 py-1">
                                                <?php echo htmlspecialchars($m['reservation_status'] ?: 'सामान्य'); ?>
                                            </span>
                                        </td>
                                        <td class="px-3 text-center">
                                            <?php if (!empty($m['mobile'])): ?>
                                                <?php echo renderMaskedPhoneButton($m['mobile'], $m['candidate_name'] . ' (Panchayat Samiti Member)'); ?>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="bi bi-info-circle fs-3 d-block mb-2 text-warning"></i>
                                        <h6 class="fw-bold text-dark">No Members Found for this Filter</h6>
                                        <p class="small text-muted mb-3">
                                            No elected Panchayat Samiti Members matched your query in <strong><?php echo htmlspecialchars($districtObj['name'] ?? $selectedDistrict); ?></strong>.
                                        </p>
                                        <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($selectedDistrict); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                            Clear Filters
                                        </a>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="card-footer bg-white py-3 px-4 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <span class="small text-muted">
                            Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $limit, $totalMatched); ?> of <?php echo number_format($totalMatched); ?> members
                        </span>

                        <nav aria-label="Pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <?php 
                                $queryBase = "?district=" . urlencode($selectedDistrict) . "&block=" . urlencode($selectedBlock) . "&q=" . urlencode($searchQuery) . "&page=";
                                ?>
                                <li class="page-item <?php echo $page <= 1 ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $queryBase . ($page - 1); ?>">&laquo; Prev</a>
                                </li>

                                <?php 
                                $startP = max(1, $page - 2);
                                $endP = min($totalPages, $page + 2);
                                for ($p = $startP; $p <= $endP; $p++): 
                                ?>
                                    <li class="page-item <?php echo $p == $page ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $queryBase . $p; ?>"><?php echo $p; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <li class="page-item <?php echo $page >= $totalPages ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo $queryBase . ($page + 1); ?>">Next &raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
