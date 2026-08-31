<?php
require_once __DIR__ . '/config.php';

$activeNav = 'representatives';
$rawTab = strtolower($_GET['tab'] ?? 'loksabha');
if (in_array($rawTab, ['mp', 'loksabha', 'lok-sabha'])) {
    $selectedTab = 'loksabha';
} elseif (in_array($rawTab, ['rajyasabha', 'rajya-sabha'])) {
    $selectedTab = 'rajyasabha';
} elseif (in_array($rawTab, ['mlc', 'vidhanparishad', 'vidhan-parishad'])) {
    $selectedTab = 'mlc';
} elseif (in_array($rawTab, ['mla2015', 'ex-mla', 'mla'])) {
    $selectedTab = 'mla2015';
} else {
    $selectedTab = 'loksabha';
}

$selectedSlug = $_GET['slug'] ?? '';

$loksabhaMps = DataProvider::getLokSabhaMps();
$rajyaSabhaMps = DataProvider::getRajyaSabhaMps();
$mlcs = DataProvider::getMlcs();
$mlas2015 = DataProvider::getMlas2015();

if ($selectedTab === 'loksabha') {
    $pageTitle = 'Bihar 40 Lok Sabha MPs: Parliamentary Constituencies Roster';
    $pageDescription = 'Official directory of 40 elected Lok Sabha Members of Parliament (MPs) across Bihar with victory margin, party affiliation, and contact details.';
    $pageCanonical = getMpUrl($selectedSlug);
} elseif ($selectedTab === 'mlc') {
    $pageTitle = 'Bihar 75 Vidhan Parishad MLCs: Legislative Council Members Directory';
    $pageDescription = 'Official directory of 75 Bihar Legislative Council (Vidhan Parishad) MLCs across Graduate, Teacher, Local Authorities, and Assembly quotas.';
    $pageCanonical = getMlcUrl($selectedSlug);
} else {
    $pageTitle = 'Bihar MPs, MLCs & Ex-MLAs Directory: Lok Sabha, Rajya Sabha & Vidhan Parishad';
    $pageDescription = 'Official directory of Bihar political representatives: 40 Lok Sabha MPs, 15 Rajya Sabha MPs, 75 Vidhan Parishad MLCs, and 243 Historical 2015-2020 MLAs.';
    $pageCanonical = SITE_URL . '/representatives';
}

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    🏛️ Bihar Parliamentary & Legislative Directory
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">40 Lok Sabha MPs</span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">15 Rajya Sabha MPs</span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">75 Vidhan Parishad MLCs</span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">243 Historical MLAs</span>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Bihar Political Representatives: <br>
                <span style="color: var(--accent-saffron);">MPs, MLCs & Historical MLAs Directory</span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
                Explore complete parliamentary rosters of <strong>40 Lok Sabha MPs</strong>, <strong>15 Rajya Sabha MPs</strong>, <strong>75 Legislative Council Members (विधान परिषद)</strong>, and <strong>243 Historical MLAs (2015–2020)</strong>.
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="?tab=loksabha#repTabContent" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank"></i> 40 Lok Sabha MPs
                </a>
                <a href="?tab=mlc#repTabContent" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-people"></i> 75 Vidhan Parishad MLCs
                </a>
                <a href="?tab=mla2015#repTabContent" class="btn btn-light fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-journal-text"></i> 243 Historical MLAs (2015)
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp"></i> Get Live Updates
                </a>
            </div>
        </div>
    </section>

    <!-- Main Container -->
    <main class="container my-4 my-lg-5">

        <!-- Top Google Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Highlight Metric Cards -->
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-warning bg-white">
                    <div class="text-muted small fw-bold">LOK SABHA</div>
                    <div class="h3 fw-bold mb-1" style="color: var(--primary-navy);">40 MPs</div>
                    <div class="small text-muted">Directly elected across all 40 Bihar Parliamentary Constituencies</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-primary bg-white">
                    <div class="text-muted small fw-bold">RAJYA SABHA</div>
                    <div class="h3 fw-bold mb-1" style="color: var(--primary-navy);">15 MPs</div>
                    <div class="small text-muted">Upper house representatives elected by Bihar Legislative Assembly</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-success bg-white">
                    <div class="text-muted small fw-bold">VIDHAN PARISHAD</div>
                    <div class="h3 fw-bold mb-1" style="color: var(--primary-navy);">75 MLCs</div>
                    <div class="small text-muted">Bihar Legislative Council (Graduate, Teacher, Local Bodies & Assembly)</div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 border-start border-4 border-secondary bg-white">
                    <div class="text-muted small fw-bold">HISTORICAL MLAs</div>
                    <div class="h3 fw-bold mb-1" style="color: var(--primary-navy);">243 MLAs</div>
                    <div class="small text-muted">2015–2020 Assembly election roster with contact numbers</div>
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <ul class="nav nav-pills custom-pills mb-4 flex-nowrap overflow-auto pb-2" id="repPillsTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'loksabha' ? 'active' : ''; ?> fw-bold py-2" id="loksabha-tab" data-bs-toggle="pill" data-bs-target="#loksabha-pane" type="button" role="tab">
                    🏛️ 1. Lok Sabha MPs (40 Seats)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'rajyasabha' ? 'active' : ''; ?> fw-bold py-2" id="rajyasabha-tab" data-bs-toggle="pill" data-bs-target="#rajyasabha-pane" type="button" role="tab">
                    👑 2. Rajya Sabha MPs (15 Members)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'mlc' ? 'active' : ''; ?> fw-bold py-2" id="mlc-tab" data-bs-toggle="pill" data-bs-target="#mlc-pane" type="button" role="tab">
                    📜 3. Bihar MLCs - Vidhan Parishad (75 Members)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $selectedTab === 'mla2015' ? 'active' : ''; ?> fw-bold py-2" id="mla2015-tab" data-bs-toggle="pill" data-bs-target="#mla2015-pane" type="button" role="tab">
                    🗳️ 4. 2015–2020 Historical MLAs (243 Seats)
                </button>
            </li>
        </ul>

        <div class="tab-content" id="repTabContent">

            <!-- ========================================================= -->
            <!-- TAB 1: LOK SABHA MPs                                      -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'loksabha' ? 'show active' : ''; ?>" id="loksabha-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                🏛️ Bihar Members of Parliament (Lok Sabha - 40 Constituencies)
                            </h2>
                            <p class="small text-muted mb-0">Elected Lok Sabha representatives from all 40 Bihar Parliamentary seats</p>
                        </div>
                        <span class="badge bg-warning text-dark px-3 py-2 fw-bold">40 Seats Active</span>
                    </div>

                    <!-- Search Box -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="lsSearch" class="form-control border-start-0 bg-light" placeholder="Search by MP Name, Constituency, Party...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small" id="lsTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">PC #</th>
                                    <th class="py-3">Constituency</th>
                                    <th class="py-3">Elected MP (सांसद)</th>
                                    <th class="py-3">Political Party</th>
                                    <th class="py-3">Declared Cases</th>
                                    <th class="py-3">House</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($loksabhaMps as $mp): ?>
                                    <tr class="ls-row" data-name="<?php echo htmlspecialchars(strtolower($mp['mp_name'] . ' ' . $mp['pc_name'] . ' ' . $mp['party'])); ?>">
                                        <td class="fw-bold text-center">
                                            <span class="badge bg-secondary rounded-pill px-2 py-1">#<?php echo $mp['pc_no']; ?></span>
                                        </td>
                                        <td class="fw-bold" style="color: var(--primary-navy); font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($mp['pc_name']); ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                <i class="bi bi-person-circle text-primary me-1"></i>
                                                <?php echo htmlspecialchars($mp['mp_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge party-badge party-badge-<?php echo strtolower(preg_replace('/[^a-z0-9]/', '', $mp['party'])); ?> fw-bold">
                                                <?php echo htmlspecialchars($mp['party']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($mp['criminal_cases'] > 0): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger fw-semibold">
                                                    <?php echo $mp['criminal_cases']; ?> Cases
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success bg-opacity-10 text-success fw-semibold">0 Cases</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-secondary border">Lok Sabha</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 2: RAJYA SABHA MPs                                    -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'rajyasabha' ? 'show active' : ''; ?>" id="rajyasabha-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                👑 Bihar Members of Parliament (Rajya Sabha - Upper House)
                            </h2>
                            <p class="small text-muted mb-0">15 Rajya Sabha members elected from Bihar representing the state in Parliament</p>
                        </div>
                        <span class="badge bg-primary text-white px-3 py-2 fw-bold">15 Upper House Seats</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="py-3">S.No.</th>
                                    <th class="py-3">Rajya Sabha MP Name</th>
                                    <th class="py-3">Political Party</th>
                                    <th class="py-3">State Represented</th>
                                    <th class="py-3">House</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rajyaSabhaMps as $rs): ?>
                                    <tr>
                                        <td class="fw-bold text-center">
                                            <span class="badge bg-secondary rounded-pill px-2 py-1"><?php echo $rs['sno']; ?></span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                                <i class="bi bi-award-fill text-warning me-1"></i>
                                                <?php echo htmlspecialchars($rs['mp_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge party-badge party-badge-<?php echo strtolower(preg_replace('/[^a-z0-9]/', '', $rs['party'])); ?> fw-bold">
                                                <?php echo htmlspecialchars($rs['party']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">Bihar</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold">Rajya Sabha</span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 3: VIDHAN PARISHAD MLCs                               -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'mlc' ? 'show active' : ''; ?>" id="mlc-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                📜 Bihar Vidhan Parishad Members (Legislative Council - 75 MLCs)
                            </h2>
                            <p class="small text-muted mb-0">Elected and nominated members representing Local Authorities, Graduates, Teachers, and Vidhan Sabha</p>
                        </div>
                        <span class="badge bg-success text-white px-3 py-2 fw-bold"><?php echo count($mlcs); ?> MLCs Active</span>
                    </div>

                    <!-- Search Box -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="mlcSearch" class="form-control border-start-0 bg-light" placeholder="Search by MLC Name, Constituency, Quota...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 small" id="mlcTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="py-3">#</th>
                                    <th class="py-3">MLC Member Name (विधान पार्षद)</th>
                                    <th class="py-3">Constituency / Electorate Quota</th>
                                    <th class="py-3">Tenure Period</th>
                                    <th class="py-3">Official Contact</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mlcs as $mlc): ?>
                                    <tr class="mlc-row" data-name="<?php echo htmlspecialchars(strtolower($mlc['name'] . ' ' . $mlc['constituency'])); ?>">
                                        <td class="text-muted fw-bold"><?php echo $mlc['sr_no']; ?></td>
                                        <td>
                                            <div class="fw-bold" style="color: var(--primary-navy); font-size: 0.95rem;">
                                                <i class="bi bi-person-fill text-success me-1"></i>
                                                <?php echo htmlspecialchars($mlc['name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                <?php echo htmlspecialchars($mlc['constituency']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="small text-muted">
                                                <i class="bi bi-calendar-event me-1"></i>
                                                <?php echo htmlspecialchars($mlc['tenure'] ?: 'Regular Tenure'); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if (!empty($mlc['contact'])): ?>
                                                <a href="tel:<?php echo htmlspecialchars($mlc['contact']); ?>" class="btn btn-sm btn-outline-success py-0 px-2 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($mlc['contact']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Not Disclosed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- ========================================================= -->
            <!-- TAB 4: 2015–2020 HISTORICAL MLAs                          -->
            <!-- ========================================================= -->
            <div class="tab-pane fade <?php echo $selectedTab === 'mla2015' ? 'show active' : ''; ?>" id="mla2015-pane" role="tabpanel">
                <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-secondary text-white fw-bold px-2 py-1">Historical Assembly Archive</span>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1">16th Vidhan Sabha (2015–2020)</span>
                            </div>
                            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">
                                🗳️ 2015–2020 Bihar Legislative Assembly: All 243 MLAs
                            </h2>
                            <p class="small text-muted mb-0">Elected MLAs from the 2015 general assembly elections across all 243 constituencies</p>
                        </div>
                        <span class="badge bg-secondary rounded-pill px-3 py-2">243 Constituencies</span>
                    </div>

                    <!-- Search Box -->
                    <div class="row mb-3">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="mlaSearch" class="form-control border-start-0 bg-light" placeholder="Search by AC No., Constituency, MLA Name, Party...">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive" style="max-height: 700px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 small" id="mlaTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="py-3">AC #</th>
                                    <th class="py-3">Assembly Constituency</th>
                                    <th class="py-3">Elected 2015 MLA (पूर्व विधायक)</th>
                                    <th class="py-3">Winning Party</th>
                                    <th class="py-3">Contact</th>
                                    <th class="py-3">View Current AC Hub</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($mlas2015 as $mla): ?>
                                    <tr class="mla-row" data-name="<?php echo htmlspecialchars(strtolower($mla['ac_no'] . ' ' . $mla['ac_name'] . ' ' . $mla['mla_name'] . ' ' . $mla['party'])); ?>">
                                        <td class="fw-bold text-center">
                                            <span class="badge bg-secondary rounded-pill px-2 py-1">#<?php echo $mla['ac_no']; ?></span>
                                        </td>
                                        <td class="fw-bold" style="color: var(--primary-navy); font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($mla['ac_name']); ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($mla['mla_name']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge party-badge party-badge-<?php echo strtolower(preg_replace('/[^a-z0-9]/', '', $mla['party'])); ?> fw-bold">
                                                <?php echo htmlspecialchars($mla['party']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($mla['mobile'])): ?>
                                                <span class="badge bg-light text-secondary border py-1 px-2 fw-semibold d-inline-flex align-items-center gap-1" title="Contact Protected">
                                                    <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($mla['mobile'])); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="vidhan-sabha.php?ac=<?php echo $mla['ac_no']; ?>" class="btn btn-sm btn-outline-primary py-0 px-2 fw-bold">
                                                2025 AC Profile &rarr;
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

        </div>

        <!-- Bottom Footer Ad Banner -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'mt-4'); ?>

    </main>

    <!-- Client-side Search Filters -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Lok Sabha Search
        const lsSearch = document.getElementById('lsSearch');
        const lsRows = document.querySelectorAll('#lsTable .ls-row');
        if (lsSearch) {
            lsSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                lsRows.forEach(r => {
                    const txt = r.getAttribute('data-name') || '';
                    r.style.display = (!q || txt.includes(q)) ? '' : 'none';
                });
            });
        }

        // MLC Search
        const mlcSearch = document.getElementById('mlcSearch');
        const mlcRows = document.querySelectorAll('#mlcTable .mlc-row');
        if (mlcSearch) {
            mlcSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                mlcRows.forEach(r => {
                    const txt = r.getAttribute('data-name') || '';
                    r.style.display = (!q || txt.includes(q)) ? '' : 'none';
                });
            });
        }

        // MLA 2015 Search
        const mlaSearch = document.getElementById('mlaSearch');
        const mlaRows = document.querySelectorAll('#mlaTable .mla-row');
        if (mlaSearch) {
            mlaSearch.addEventListener('input', function() {
                const q = this.value.toLowerCase().trim();
                mlaRows.forEach(r => {
                    const txt = r.getAttribute('data-name') || '';
                    r.style.display = (!q || txt.includes(q)) ? '' : 'none';
                });
            });
        }
    });
    </script>

<?php require_once __DIR__ . '/footer.php'; ?>
