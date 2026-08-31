<?php
require_once __DIR__ . '/config.php';

$districts = DataProvider::getDistricts();
$constituencies = DataProvider::getConstituencies();
$candidates = DataProvider::getCandidates();
$news = DataProvider::getNews();
$panchayats = DataProvider::getPanchayatData();

$pageTitle = 'Bihar Election 2026: 243 Assembly Data, 38 Districts & Panchayat Delimitation Platform';
$pageDescription = 'Bihar\'s comprehensive non-government election data platform. Explore 243 Assembly Constituencies (Chapra, Bankipur, Raghopur), 38 District Hubs, 2026 Panchayat Delimitation status, reservation matrix & verified MLA profiles.';
$pageKeywords = 'Bihar Election 2026, 243 Bihar Assembly Constituencies, Chapra Vidhan Sabha, Saran Election, Bihar Panchayat 2026, Bihar MLA list, Bihar Political Data';
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
                <span>Election Data & Intelligence Hub</span>
            </h1>
            <p class="hero-subtitle lead text-white-50 mb-4 mx-auto" style="max-width: 820px;">
                Covering Panchayat to Parliament: 38 Districts, 243 Assembly Constituencies, 8,000+ Gram Panchayats, and 2026 Delimitation intelligence with verified historical records.
            </p>

            <!-- Search Hub Widget with Dynamic Suggestions Dropdown -->
            <div class="search-widget mx-auto" style="max-width: 700px;">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        id="globalSearchInput" 
                        class="search-input" 
                        placeholder="Search AC (e.g. 118 Chapra, Bankipur), District, or MLA..."
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
                <a href="<?php echo SITE_URL; ?>/mla/118-chapra" class="pill-link">AC 118 — Chapra</a>
                <a href="<?php echo SITE_URL; ?>/mla/182-bankipur" class="pill-link">AC 182 — Bankipur</a>
                <a href="<?php echo SITE_URL; ?>/mla/128-raghopur" class="pill-link">AC 128 — Raghopur</a>
                <a href="<?php echo getDistrictUrl('saran'); ?>" class="pill-link">Saran District Hub</a>
                <a href="<?php echo getPanchayatUrl(); ?>" class="pill-link">Panchayat Delimitation 2026</a>
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

        <!-- Top Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-5'); ?>

        <!-- 38 District Hubs Section -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">38 Bihar District Election Hubs</h2>
                    <p class="small text-muted mb-0">Vidhan Sabha seats, headquarters, and demographics for all 38 districts</p>
                </div>
                <a href="district.php?slug=saran" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    View All 38 Districts &rarr;
                </a>
            </div>

            <div class="row g-3 g-lg-4">
                <?php foreach (array_slice($districts, 0, 8) as $dist): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 p-3 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h6 fw-bold mb-0" style="color: var(--primary-navy);"><?php echo htmlspecialchars($dist['name']); ?></h3>
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
                                Open District Hub &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Mid-Feed In-Article Ad Slot -->
        <?php renderGoogleAd('in_feed', GOOGLE_AD_SLOT_INFEED, 'my-5'); ?>

        <!-- Featured 243 Assembly Constituencies -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Featured Assembly Seats (243 AC Project)</h2>
                    <p class="small text-muted mb-0">Historical election results (2020 vs 2015), victory margins, and voter turnout</p>
                </div>
                <a href="<?php echo SITE_URL; ?>/mla/118-chapra" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    Explore 243 Seats &rarr;
                </a>
            </div>

            <div class="row g-3 g-lg-4">
                <?php foreach (array_slice($constituencies, 0, 3) as $ac): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 p-3 p-lg-4 h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="ac-no-badge">AC #<?php echo $ac['ac_no']; ?></span>
                                <span class="badge-party <?php echo strtolower($ac['current_party']); ?>"><?php echo $ac['current_party']; ?></span>
                            </div>
                            <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">
                                <?php echo htmlspecialchars($ac['name']); ?> 
                                <span class="small text-muted fw-normal">(<?php echo htmlspecialchars($ac['name_hi']); ?>)</span>
                            </h3>
                            <div class="small text-muted mb-3">
                                District: <strong><?php echo htmlspecialchars($ac['district']); ?></strong> | Lok Sabha: <strong><?php echo htmlspecialchars($ac['lok_sabha']); ?></strong>
                            </div>

                            <div class="bg-light p-3 rounded-2 small mb-3">
                                <div class="fw-bold text-muted text-uppercase mb-1" style="font-size: 0.75rem;">2020 Result Summary</div>
                                <div>Winner: <strong style="color: var(--primary-navy);"><?php echo htmlspecialchars($ac['election_2020']['winner']); ?></strong> (<?php echo $ac['election_2020']['winner_party']; ?>)</div>
                                <div class="text-muted">Margin: <strong><?php echo number_format($ac['election_2020']['margin']); ?></strong> votes (Turnout: <?php echo $ac['election_2020']['turnout_percent']; ?>%)</div>
                            </div>
                        </div>

                        <a href="<?php echo getMlaUrl($ac); ?>" class="btn btn-primary btn-sm w-100 fw-bold py-2 shadow-sm" style="background: var(--secondary-navy); border: none;">
                            Full Constituency Report &rarr;
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- Bihar Panchayat Election 2026 Delimitation Tracker -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5 bg-white">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3 pb-2 border-bottom">
                <div>
                    <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 mb-1">🌾 Upcoming Election 2026</span>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Bihar Panchayat Election 2026: Delimitation & Reservation Tracker</h2>
                    <p class="small text-muted mb-0">Mukhiya reservation rosters, Sarpanch categories, and Zila Parishad territories</p>
                </div>
                <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-success btn-sm fw-bold rounded-pill px-3">
                    Explore Panchayat Hub &rarr;
                </a>
            </div>

            <!-- Responsive Table -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">District</th>
                            <th class="py-3">Block</th>
                            <th class="py-3">Gram Panchayat</th>
                            <th class="py-3">Incumbent Mukhiya</th>
                            <th class="py-3">2026 Reservation</th>
                            <th class="py-3">Delimitation Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($panchayats, 0, 8) as $p): ?>
                        <tr>
                            <td class="fw-bold" style="color: var(--primary-navy);"><?php echo htmlspecialchars($p['district'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($p['block'] ?? ''); ?></td>
                            <td class="fw-bold" style="color: var(--secondary-navy);"><?php echo htmlspecialchars($p['panchayat_name'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars(!empty($p['current_mukhiya']) ? $p['current_mukhiya'] : 'Vacant / Not Declared'); ?></td>
                            <td><span class="badge bg-warning bg-opacity-25 text-dark fw-bold"><?php echo htmlspecialchars($p['reservation_2026_mukhiya'] ?? (!empty($p['mukhiya_reservation']) ? $p['mukhiya_reservation'] : 'General / Open')); ?></span></td>
                            <td><span class="text-success fw-bold">✓ <?php echo htmlspecialchars($p['delimitation_status'] ?? 'Delimitation Finalized'); ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- In-Table Ad Slot -->
            <div class="pt-3">
                <?php renderGoogleAd('table_banner', GOOGLE_AD_SLOT_TABLE); ?>
            </div>
        </section>

        <!-- Candidate & Political Leaders Directory -->
        <section class="mb-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4 pb-2 border-bottom">
                <div>
                    <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Candidate & Political Leaders Directory</h2>
                    <p class="small text-muted mb-0">Verified biographies, declared assets, criminal record disclosures, and election histories</p>
                </div>
                <a href="advertise.php?category=candidate" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3">
                    Submit Candidate Profile &rarr;
                </a>
            </div>

            <?php if (!empty($candidates)): ?>
            <div class="row g-3 g-lg-4">
                <?php foreach ($candidates as $cand): ?>
                <div class="col-12 col-sm-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden h-100 d-flex flex-column justify-content-between text-center">
                        <div>
                            <div class="candidate-card-header"></div>
                            <div class="px-3 pb-3">
                                <img src="<?php echo $cand['photo']; ?>" alt="<?php echo htmlspecialchars($cand['name']); ?>" class="candidate-card-avatar">
                                <h3 class="h6 fw-bold mb-0 text-navy d-flex align-items-center justify-content-center gap-1">
                                    <?php echo htmlspecialchars($cand['name']); ?>
                                    <?php if (!empty($cand['verified'])): ?>
                                        <i class="bi bi-check-circle-fill text-primary" title="ECI Verified"></i>
                                    <?php endif; ?>
                                </h3>
                                <p class="small text-muted mb-2"><?php echo htmlspecialchars($cand['designation'] ?? ''); ?></p>

                                <div class="bg-light p-2 rounded-2 small text-start mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Party:</span> <strong><?php echo htmlspecialchars($cand['party_short'] ?? ''); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Assets:</span> <strong class="text-success"><?php echo htmlspecialchars($cand['assets_declared'] ?? 'Disclosed'); ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted">Cases:</span> <strong><?php echo htmlspecialchars($cand['criminal_cases'] ?? '0'); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 pt-0">
                            <a href="candidate.php?slug=<?php echo $cand['slug']; ?>" class="btn btn-outline-secondary btn-sm w-100 fw-bold">
                                View Profile & Affidavit &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center bg-white">
                <div class="my-3">
                    <span class="fs-1">🗳️</span>
                    <h3 class="h5 fw-bold mt-2" style="color: var(--primary-navy);">Candidate Directory: Bihar 2026 Nominations</h3>
                    <p class="small text-muted mx-auto mb-3" style="max-width: 600px;">
                        Candidate profiles, affidavits, and asset disclosures are published in real-time as nominations and verified party lists are finalized.
                    </p>
                    <a href="advertise.php?category=candidate" class="btn btn-primary btn-sm fw-bold px-4 py-2" style="background: var(--secondary-navy); border: none;">
                        <i class="bi bi-patch-check me-1"></i> Register Verified Candidate Profile
                    </a>
                </div>
            </div>
            <?php endif; ?>
        </section>

        <!-- Bottom Footer Ad Banner -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'mb-5'); ?>

        <!-- Partner & Commercial Callout Banner -->
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
                    <a href="advertise.php" class="btn btn-warning fw-bold px-4 py-2 text-dark shadow-sm">
                        View Packages & Rates &rarr;
                    </a>
                </div>
            </div>
        </section>

    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
