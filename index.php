<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();
// Sort districts alphabetically A-Z
usort($districts, function($a, $b) {
    return strcmp($a['name'] ?? '', $b['name'] ?? '');
});

$constituencies = DataProvider::getConstituencies();
$candidates = DataProvider::getCandidates();
$news = DataProvider::getNews();
$panchayats = DataProvider::getPanchayatData();

$pageTitle = 'Bihar Election 2026: 243 Assembly Data, 38 Districts & Panchayat Delimitation Platform';
$pageDescription = 'Bihar\'s comprehensive non-government election data platform. Explore all 243 Assembly Constituencies, 38 District Hubs (Patna, Muzaffarpur, Gaya, Bhagalpur), 2026 Panchayat Delimitation status & verified MLA profiles.';
$pageKeywords = 'Bihar Election 2026, 243 Bihar Assembly Constituencies, Patna Vidhan Sabha, Bihar Election Results, 38 Districts Bihar, Bihar Panchayat 2026, Bihar MLA list, Bihar Political Data';
$pageCanonical = SITE_URL . '/index.php';
$activeNav = 'home';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Section with Bootstrap 5.3 Responsive Grid -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-center py-2 py-lg-3">
            <div class="hero-badge mb-3">
                <i class="bi bi-shield-check"></i> Non-Government Election Data Platform
            </div>
            <h1 class="hero-title display-5 fw-extrabold mb-3">
                Bihar's Premier <br>
                <span>Election Data &amp; Intelligence Hub</span>
            </h1>
            <p class="hero-subtitle lead text-white-50 mb-4 mx-auto" style="max-width: 820px;">
                Covering Panchayat to Parliament: 38 Districts, 243 Assembly Constituencies, 8,000+ Gram Panchayats, and 2026 Delimitation intelligence with verified historical records across all of Bihar.
            </p>

            <!-- Search Hub Widget with Dynamic Suggestions Dropdown -->
            <div class="search-widget mx-auto" style="max-width: 700px;">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        id="globalSearchInput" 
                        class="search-input" 
                        placeholder="Search MLA, MP, Mukhiya, Sarpanch, District, or AC..."
                        autocomplete="off"
                    >
                    <button class="btn-search" onclick="document.getElementById('globalSearchInput').focus()">
                        <i class="bi bi-search"></i> <span>Search</span>
                    </button>
                </div>

                <!-- Instant Dynamic Suggestions Dropdown -->
                <div id="searchDropdown" class="search-dropdown"></div>
            </div>

            <!-- Quick Pill Links (Touch horizontal scrollable on mobile) -->
            <div class="d-flex flex-nowrap flex-sm-wrap justify-content-start justify-content-sm-center align-items-center gap-2 mt-3 overflow-x-auto pb-2 px-1">
                <span class="small text-white-50 text-nowrap">Popular:</span>
                <a href="<?php echo getDistrictUrl('patna'); ?>" class="pill-link fw-bold text-warning">👑 Patna Hub</a>
                <a href="<?php echo SITE_URL; ?>/mla" class="pill-link">🗳️ 243 MLAs</a>
                <a href="<?php echo SITE_URL; ?>/mp" class="pill-link">🏛️ 40 MPs</a>
                <a href="<?php echo SITE_URL; ?>/mlc" class="pill-link">📜 75 MLCs</a>
                <a href="<?php echo SITE_URL; ?>/mukhiya" class="pill-link">👑 Mukhiya Directory</a>
                <a href="<?php echo SITE_URL; ?>/sarpanch" class="pill-link">⚖️ Sarpanch Directory</a>
                <a href="<?php echo getZilaParishadUrl(); ?>" class="pill-link">Zila Parishad</a>
            </div>
        </div>
    </section>

    <!-- Live 4-Stat Grid Bar -->
    <div class="container" style="margin-top: -24px; position: relative; z-index: 10;">
        <div class="row g-2 g-md-3 g-lg-4">
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                    <div class="stat-icon">🏛️</div>
                    <div>
                        <h3 class="h4 fw-bold mb-0 text-navy">243</h3>
                        <p class="small text-muted mb-0 fw-semibold">Assembly Seats</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                    <div class="stat-icon">📍</div>
                    <div>
                        <h3 class="h4 fw-bold mb-0 text-navy">38</h3>
                        <p class="small text-muted mb-0 fw-semibold">District Hubs</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                    <div class="stat-icon">🌾</div>
                    <div>
                        <h3 class="h4 fw-bold mb-0 text-navy">8,053+</h3>
                        <p class="small text-muted mb-0 fw-semibold">Gram Panchayats</p>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-row align-items-center gap-3">
                    <div class="stat-icon">👥</div>
                    <div>
                        <h3 class="h4 fw-bold mb-0 text-navy">7.64 Cr+</h3>
                        <p class="small text-muted mb-0 fw-semibold">Total Electors</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="container my-5">



        <!-- 38 District Hubs Section (Alphabetical A-Z Order with Patna Highlight) -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">38 Bihar District Election Hubs (A–Z)</h2>
                    <p class="small text-muted mb-0">Vidhan Sabha seats, headquarters, and demographics for all 38 districts across Bihar</p>
                </div>
                <a href="<?php echo getDistrictUrl('patna'); ?>" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    View All 38 Districts &rarr;
                </a>
            </div>

            <div class="row g-3 g-lg-4">
                <?php foreach (array_slice($districts, 0, 8) as $dist): 
                    $isPatna = ($dist['slug'] === 'patna');
                ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-column justify-content-between <?php echo $isPatna ? 'border-top border-4 border-warning bg-light' : ''; ?>">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h6 fw-bold mb-0" style="color: var(--primary-navy);">
                                    <?php echo htmlspecialchars($dist['name']); ?>
                                    <?php if ($isPatna): ?>
                                        <span class="badge bg-warning text-dark extra-small ms-1">Capital</span>
                                    <?php endif; ?>
                                </h3>
                                <span class="badge bg-light text-dark border"><?php echo $dist['total_ac']; ?> ACs</span>
                            </div>
                            <p class="small text-muted mb-2">
                                <strong>HQ:</strong> <?php echo htmlspecialchars($dist['headquarters']); ?> | <strong>Division:</strong> <?php echo htmlspecialchars($dist['division']); ?>
                            </p>
                            <div class="d-flex flex-wrap gap-1 mb-3">
                                <?php if (!empty($dist['ac_list'])): ?>
                                    <?php foreach (array_slice($dist['ac_list'], 0, 3) as $acItem): ?>
                                        <a href="<?php echo getMlaUrl($acItem); ?>" class="ac-tag">
                                            <?php echo $acItem['ac_no']; ?> - <?php echo htmlspecialchars($acItem['name']); ?>
                                        </a>
                                    <?php endforeach; ?>
                                    <?php if (count($dist['ac_list']) > 3): ?>
                                        <span class="ac-tag" style="background: #e2e8f0;">+<?php echo count($dist['ac_list']) - 3; ?> more</span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="pt-2 border-top">
                            <a href="<?php echo getDistrictUrl($dist['slug']); ?>" class="small fw-bold text-decoration-none" style="color: var(--accent-saffron);">
                                Open <?php echo htmlspecialchars($dist['name']); ?> Hub &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>



        <!-- Featured 243 Assembly Constituencies -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Featured Assembly Seats (243 AC Project)</h2>
                    <p class="small text-muted mb-0">Latest 2026 &amp; 2025 Vidhan Sabha winners, victory margins, and historical electoral archive across Bihar</p>
                </div>
                <a href="<?php echo SITE_URL; ?>/vidhan-sabha" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    Explore 243 Seats &rarr;
                </a>
            </div>

            <div class="row g-3 g-lg-4">
                <?php 
                // Select prominent statewide seats (Bankipur, Patna Sahib, Raghopur, Aurai, Gaya Town, Chapra)
                $featuredAcNos = [182, 184, 128, 89, 230, 118];
                $featuredAcs = [];
                foreach ($featuredAcNos as $favNo) {
                    $found = DataProvider::getConstituencyByAcNumber($favNo);
                    if ($found) $featuredAcs[] = $found;
                }
                if (empty($featuredAcs)) {
                    $featuredAcs = array_slice($constituencies, 0, 6);
                }
                foreach ($featuredAcs as $ac): 
                    $acNo = (int)$ac['ac_no'];
                    $byeList = DataProvider::getByeElectionDetailedResults($acNo);
                    $res2025 = DataProvider::getElectionSuccessfulCandidates($acNo, 2025);
                    $res2020 = DataProvider::getElectionSuccessfulCandidates($acNo, 2020) ?: ($ac['election_2020'] ?? []);
                    $hasBye = !empty($byeList);

                    if ($hasBye) {
                        $yearBadge = '<span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="bi bi-lightning-charge-fill me-1"></i>2026 Bye-Poll Winner</span>';
                        $winnerName = $byeList[0]['candidate_name'] ?? $ac['current_mla'];
                        $winnerParty = $byeList[0]['party'] ?? $ac['current_party'];
                        $marginVotes = $byeList[0]['margin'] ?? (($byeList[0]['votes_total'] ?? 0) - ($byeList[1]['votes_total'] ?? 0));
                        $runnerUpName = $byeList[1]['candidate_name'] ?? '';
                        $runnerUpParty = $byeList[1]['party'] ?? '';
                    } elseif (!empty($res2025['winner_name'])) {
                        $yearBadge = '<span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 border border-success border-opacity-25"><i class="bi bi-trophy-fill me-1"></i>2025 Winner</span>';
                        $winnerName = $res2025['winner_name'] ?? $ac['current_mla'];
                        $winnerParty = $res2025['winner_party'] ?? $ac['current_party'];
                        $marginVotes = $res2025['margin'] ?? 0;
                        $runnerUpName = $res2025['runner_up_name'] ?? '';
                        $runnerUpParty = $res2025['runner_up_party'] ?? '';
                    } else {
                        $yearBadge = '<span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 border border-primary border-opacity-25"><i class="bi bi-person-badge me-1"></i>Elected MLA</span>';
                        $winnerName = $ac['current_mla'] ?? $res2020['winner_name'] ?? $res2020['winner'] ?? 'N/A';
                        $winnerParty = $ac['current_party'] ?? $res2020['winner_party'] ?? '';
                        $marginVotes = $res2020['margin'] ?? 0;
                        $runnerUpName = $res2020['runner_up_name'] ?? $res2020['runner_up'] ?? '';
                        $runnerUpParty = $res2020['runner_up_party'] ?? '';
                    }
                    $partyClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $winnerParty));
                ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 h-100 d-flex flex-column justify-content-between" style="background: #ffffff;">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-no-badge">AC #<?php echo $ac['ac_no']; ?></span>
                                <span class="badge-party <?php echo $partyClass; ?>"><?php echo htmlspecialchars($winnerParty); ?></span>
                            </div>
                            <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">
                                <?php echo htmlspecialchars($ac['name']); ?> 
                                <?php if (!empty($ac['name_hi'])): ?>
                                    <span class="small text-muted fw-normal">(<?php echo htmlspecialchars($ac['name_hi']); ?>)</span>
                                <?php endif; ?>
                            </h3>
                            <div class="small text-muted mb-3">
                                District: <strong><?php echo htmlspecialchars($ac['district']); ?></strong>
                                <?php if (!empty($ac['lok_sabha'])): ?>
                                    | Lok Sabha: <strong><?php echo htmlspecialchars($ac['lok_sabha']); ?></strong>
                                <?php endif; ?>
                            </div>

                            <div class="bg-light p-3 rounded-3 mb-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <?php echo $yearBadge; ?>
                                    <?php if ($marginVotes > 0): ?>
                                        <span class="badge bg-white text-dark border px-2 py-1 small fw-bold">
                                            +<?php echo number_format($marginVotes); ?> margin
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <div class="fw-bold fs-6 mb-1" style="color: var(--primary-navy);">
                                    <?php echo htmlspecialchars($winnerName); ?>
                                </div>
                                <div class="small text-muted d-flex justify-content-between">
                                    <span>Party: <strong class="text-dark"><?php echo htmlspecialchars($winnerParty); ?></strong></span>
                                </div>
                                <?php if (!empty($runnerUpName)): ?>
                                    <div class="small text-muted pt-2 mt-2 border-top">
                                        <span class="text-secondary">Defeated:</span> 
                                        <strong><?php echo htmlspecialchars($runnerUpName); ?></strong> 
                                        <?php if (!empty($runnerUpParty)): ?>
                                            (<?php echo htmlspecialchars($runnerUpParty); ?>)
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <a href="<?php echo getMlaUrl($ac); ?>" class="btn btn-outline-primary btn-sm w-100 fw-bold py-2 rounded-3 shadow-none mt-2">
                            Full AC #<?php echo $ac['ac_no']; ?> Report &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Upcoming Election: Bihar Legislative Council (MLC) Section -->
        <section class="mb-5">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 1px solid #e2e8f0 !important;">
                <div class="p-4 p-lg-5">
                    <div class="row align-items-center g-4">
                        <div class="col-12 col-lg-7">
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                <span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill">
                                    <i class="bi bi-calendar-check-fill me-1"></i> Upcoming Election
                                </span>
                                <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill">
                                    🏛️ Bihar Vidhan Parishad
                                </span>
                                <span class="badge bg-secondary text-white fw-bold px-3 py-1.5 rounded-pill">
                                    75 Upper House Seats
                                </span>
                            </div>

                            <h2 class="h3 fw-bold mb-2" style="color: var(--primary-navy); font-family: 'Outfit', sans-serif;">
                                Bihar Legislative Council (MLC) Elections
                            </h2>
                            <p class="text-muted mb-4" style="line-height: 1.6;">
                                Explore full roster, tenure expirations, and quota breakdowns for all <strong>75 Members of Bihar Legislative Council (MLCs)</strong> across Local Authorities, Graduates, Teachers, and Assembly quotas.
                            </p>

                            <div class="row g-2 g-sm-3 mb-4">
                                <div class="col-6 col-sm-4">
                                    <div class="p-3 bg-white rounded-3 shadow-xs border text-center h-100">
                                        <div class="fw-extrabold fs-4 text-primary">24</div>
                                        <div class="small fw-semibold text-dark">Local Authorities</div>
                                        <div class="extra-small text-muted">Panchayat &amp; ULB Electors</div>
                                    </div>
                                </div>
                                <div class="col-6 col-sm-4">
                                    <div class="p-3 bg-white rounded-3 shadow-xs border text-center h-100">
                                        <div class="fw-extrabold fs-4 text-warning" style="color: #d97706 !important;">27</div>
                                        <div class="small fw-semibold text-dark">Assembly Quota</div>
                                        <div class="extra-small text-muted">Elected by 243 MLAs</div>
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4">
                                    <div class="p-3 bg-white rounded-3 shadow-xs border text-center h-100">
                                        <div class="fw-extrabold fs-4 text-success">24</div>
                                        <div class="small fw-semibold text-dark">Graduates &amp; Nominated</div>
                                        <div class="extra-small text-muted">Teachers &amp; Governor Quotas</div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-danger fw-bold px-4 py-2.5 rounded-pill shadow-sm">
                                    <i class="bi bi-list-columns-reverse me-1"></i> See 75 MLCs List &rarr;
                                </a>
                                <a href="<?php echo SITE_URL; ?>/representatives" class="btn btn-outline-secondary fw-bold px-4 py-2.5 rounded-pill">
                                    All Representatives
                                </a>
                            </div>
                        </div>

                        <div class="col-12 col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                    <h5 class="fw-bold mb-0 text-navy fs-6">
                                        <i class="bi bi-person-lines-fill me-1 text-danger"></i> Vidhan Parishad Highlights
                                    </h5>
                                    <span class="badge bg-light text-dark border">75 MLCs</span>
                                </div>

                                <div class="d-flex flex-column gap-2.5">
                                    <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold small text-dark">Permanent Upper Chamber</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">1/3rd Members retire every 2 years (6-yr tenure)</div>
                                        </div>
                                        <span class="badge bg-primary text-white small">Biennial Polls</span>
                                    </div>

                                    <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold small text-dark">Local Body Authority Electors</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Mukhiyas, Ward Members, Panchayat Samiti &amp; ZP</div>
                                        </div>
                                        <span class="badge bg-success text-white small">24 Seats</span>
                                    </div>

                                    <div class="p-2.5 bg-light rounded-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold small text-dark">Teachers &amp; Graduates Quota</div>
                                            <div class="text-muted" style="font-size: 0.75rem;">Patna, Tirhut, Kosi, Saran, Darbhanga, Gaya</div>
                                        </div>
                                        <span class="badge bg-info text-dark small">12 Seats</span>
                                    </div>
                                </div>

                                <div class="mt-3 pt-2 border-top text-center">
                                    <a href="<?php echo SITE_URL; ?>/mlc" class="small fw-bold text-decoration-none text-danger">
                                        Open Bihar MLC Directory &amp; Contact Roster &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <section class="card border-0 rounded-4 p-4 text-white shadow-lg" style="background: linear-gradient(135deg, #0b192c, #1e3e62);">
            <div class="row align-items-center g-3">
                <div class="col-12 col-lg-8">
                    <h3 class="h4 fw-bold mb-2">🚀 Contesting in Bihar 2026 or Operating a Local Service?</h3>
                    <p class="text-white-50 mb-2">Reach 2,00,000+ monthly high-intent voters, political observers, and community leaders across Bihar.</p>
                    <div class="d-flex flex-wrap gap-3 small text-white-50">
                        <span>✓ Verified Candidate Pages (₹2,500+)</span>
                        <span>✓ District Directory Listings (₹1,999/yr)</span>
                        <span>✓ Assembly Page Sponsors</span>
                    </div>
                </div>
                <div class="col-12 col-lg-4 text-lg-end">
                    <a href="<?php echo getAdvertiseUrl(); ?>" class="btn btn-warning fw-bold px-4 py-2 text-dark shadow-sm">
                        View Packages & Rates &rarr;
                    </a>
                </div>
            </div>
        </section>

    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
