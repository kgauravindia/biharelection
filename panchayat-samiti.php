<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();

$districtInput = $_GET['district'] ?? '';
$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrict = $districtObj['slug'] ?? $districtInput;
$selectedBlock = $_GET['block'] ?? '';

$samiti2016 = DataProvider::getPanchayatSamiti2016($selectedDistrict ?: null);

if (!empty($selectedBlock)) {
    $samiti2016 = array_values(array_filter($samiti2016, function($s) use ($selectedBlock) {
        return isBlockMatch($s['block'] ?? '', $selectedBlock);
    }));
}

if (!empty($selectedBlock) && $districtObj) {
    $distLabel = "({$selectedBlock} Block, {$districtObj['name']} District)";
} elseif ($districtObj) {
    $distLabel = "({$districtObj['name']} District)";
} else {
    $distLabel = "Across 534 Blocks of Bihar";
}

$pageTitle = "Bihar Panchayat Samiti & Block Pramukh Directory {$distLabel}: Block Pramukhs & Up-Pramukhs";
$pageDescription = "Official directory of Bihar Panchayat Samiti Block Pramukhs (प्रखंड प्रमुख) and Up-Pramukhs across Bihar CD blocks {$distLabel}.";
$pageCanonical = getPanchayatSamitiUrl($selectedDistrict, $selectedBlock);

$activeNav = 'samiti';
require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-primary bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🌾 Bihar Panchayati Raj Tier 2 (Block Tier)
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    <?php echo count($samiti2016); ?> Block Pramukhs
                </span>
                <span class="badge bg-warning bg-opacity-25 text-warning fw-bold px-3 py-2 rounded-pill">
                    534 CD Blocks
                </span>
            </div>

            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/panchayat" class="text-white-50 text-decoration-none">Panchayats</a></li>
                    <?php if ($districtObj && !empty($selectedBlock)): ?>
                        <li class="breadcrumb-item"><a href="<?php echo getPanchayatSamitiUrl($selectedDistrict); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($selectedBlock); ?> Samiti</li>
                    <?php elseif ($districtObj): ?>
                        <li class="breadcrumb-item"><a href="<?php echo getDistrictUrl($selectedDistrict); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Panchayat Samiti</li>
                    <?php else: ?>
                        <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Panchayat Samiti Directory</li>
                    <?php endif; ?>
                </ol>
            </nav>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Bihar Panchayat Samiti &amp; Block Pramukh Directory: <br>
                <span style="color: var(--accent-saffron);">
                    <?php if (!empty($selectedBlock) && $districtObj): ?>
                        <?php echo htmlspecialchars($selectedBlock); ?> Block Pramukh &amp; Up-Pramukh
                    <?php elseif ($districtObj): ?>
                        <?php echo htmlspecialchars($districtObj['name']); ?> District Block Samitis
                    <?php else: ?>
                        Block Pramukhs &amp; Up-Pramukhs Roster
                    <?php endif; ?>
                </span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                Official directory of Block Panchayat Samiti leadership across 534 Blocks in Bihar. Explore elected Block Pramukhs (प्रखंड प्रमुख) and Up-Pramukhs (उप-प्रमुख).
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="#samitiTableCard" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-award-fill me-1"></i> Block Pramukh Table (<?php echo count($samiti2016); ?>)
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

        <!-- District Filter Bar -->
        <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white border-top border-4 border-primary">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <label class="fw-bold text-navy small mb-0"><i class="bi bi-filter"></i> District Filter:</label>
                    <select class="form-select form-select-sm rounded-pill" style="min-width: 200px;" onchange="if(this.value){ location.href = this.value; }">
                        <option value="<?php echo SITE_URL; ?>/panchayat-samiti" <?php echo empty($selectedDistrict) ? 'selected' : ''; ?>>All 38 Districts</option>
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo SITE_URL; ?>/panchayat-samiti/<?php echo urlencode($d['slug']); ?>" <?php echo $selectedDistrict === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-grow-1" style="max-width: 320px;">
                    <input type="text" id="samitiSearchInput" class="form-control form-control-sm rounded-pill" placeholder="🔍 Search block or pramukh...">
                </div>
            </div>
        </div>

        <!-- Block Samiti Pramukh Table Card -->
        <div id="samitiTableCard" class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white mb-5">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h5 class="fw-bold text-navy mb-0">
                        <i class="bi bi-award text-primary me-2"></i> Block Pramukh &amp; Up-Pramukh Roster
                    </h5>
                    <small class="text-muted">Panchayat Samiti Tier 2 elected presidents across Bihar blocks</small>
                </div>
                <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill">
                    <?php echo count($samiti2016); ?> Block Samitis
                </span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="samiti2016Table">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3 px-3 text-navy fw-bold small text-uppercase">District</th>
                            <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Block Name</th>
                            <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Block Pramukh (प्रखंड प्रमुख)</th>
                            <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Up-Pramukh (उप प्रमुख)</th>
                            <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Block Hub</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($samiti2016)): ?>
                            <?php foreach ($samiti2016 as $s): 
                                $sDist = (string)($s['district'] ?? '');
                                $sDistSlug = strtolower((string)($s['district_slug'] ?? slugify($sDist)));
                                $sBlock = (string)($s['block'] ?? '');
                                $sBlockSlug = slugify($sBlock);
                                $sPramukh = (string)($s['pramukh_2016'] ?? '');
                                $sUpPramukh = (string)($s['up_pramukh_2016'] ?? '');
                            ?>
                                <tr class="samiti-2016-row"
                                    data-name="<?php echo htmlspecialchars(strtolower($sDist . ' ' . $sBlock . ' ' . $sPramukh . ' ' . $sUpPramukh)); ?>">
                                    <td class="fw-bold">
                                        <a href="<?php echo getDistrictUrl($sDistSlug); ?>" class="text-decoration-none text-navy">
                                            🏢 <?php echo htmlspecialchars($sDist); ?>
                                        </a>
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        <a href="<?php echo getBlockUrl($sDistSlug, $sBlockSlug); ?>" class="text-decoration-none text-dark">
                                            <?php echo htmlspecialchars($sBlock); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-primary">
                                            <?php echo htmlspecialchars($sPramukh ?: 'Elected Pramukh'); ?>
                                        </div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary small px-2 py-0.5">Pramukh</span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <?php echo htmlspecialchars($sUpPramukh ?: 'Elected Up-Pramukh'); ?>
                                        </div>
                                        <span class="badge bg-light text-muted border small px-2 py-0.5">Up-Pramukh</span>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?php echo getBlockUrl($sDistSlug, $sBlockSlug); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                                            Explore Block &rarr;
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    No Panchayat Samiti records found for the selected filter.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
    </main>

    <!-- Search Script -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const samitiSearch = document.getElementById('samitiSearchInput');
        if (samitiSearch) {
            samitiSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                document.querySelectorAll('#samiti2016Table .samiti-2016-row').forEach(row => {
                    const data = row.getAttribute('data-name') || '';
                    row.style.display = (!q || data.includes(q)) ? '' : 'none';
                });
            });
        }
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
