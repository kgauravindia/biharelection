<?php
require_once __DIR__ . '/config.php';

$slug = $_GET['slug'] ?? '';
$candidate = !empty($slug) ? DataProvider::getCandidateBySlug($slug) : null;

$allCandidates = DataProvider::getCandidates();
if (!$candidate && !empty($allCandidates)) {
    $candidate = $allCandidates[0];
}

$pageTitle = $candidate 
    ? "{$candidate['name']} — {$candidate['party_short']} Leader Profile: Assets, Education & History"
    : "Candidate Directory & Political Profiles — Bihar Election 2026";
$pageDescription = $candidate 
    ? "Official political biography of {$candidate['name']} ({$candidate['party_short']}) representing {$candidate['constituency']}."
    : "Verified candidate profiles, declared assets, educational backgrounds, and political records for Bihar Assembly Elections.";
$pageKeywords = "Bihar Election Candidates, Bihar 2026 MLA Aspirants, Bihar Political Leaders Profile";
$pageCanonical = SITE_URL . "/candidate.php" . ($candidate ? "?slug=" . $candidate['slug'] : '');
$activeNav = 'candidates';

require_once __DIR__ . '/header.php';
?>

<?php if (!$candidate): ?>
    <!-- Empty Candidate Hub State -->
    <section class="hero-section py-5">
        <div class="container text-center">
            <h1 class="display-5 fw-extrabold text-white mb-2">
                Candidate Directory <span style="color: var(--accent-saffron);">2026</span>
            </h1>
            <p class="lead text-white-50 mb-0" style="max-width: 650px; margin: 0 auto;">
                Verified candidate dossiers, election affidavits, asset disclosures, and historical track records for all 243 constituencies.
            </p>
        </div>
    </section>

    <main class="container my-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center bg-white mx-auto" style="max-width: 750px;">
            <div class="my-4">
                <span class="fs-1">🗳️</span>
                <h2 class="h4 fw-bold mt-3 mb-2" style="color: var(--primary-navy);">Candidate Profiles Being Prepared</h2>
                <p class="text-muted small mb-4">
                    Official candidate profiles and affidavit disclosures will be published here in real-time as candidate nominations and party declarations for the 2026 election are announced.
                </p>
                <div class="d-flex justify-content-center flex-wrap gap-3">
                    <a href="vidhan-sabha.php" class="btn btn-outline-primary fw-bold px-4 py-2">
                        Explore 243 Assembly Seats &rarr;
                    </a>
                    <a href="advertise.php?category=candidate" class="btn btn-warning fw-bold px-4 py-2 text-dark shadow-sm">
                        <i class="bi bi-patch-check me-1"></i> Register / Claim Candidate Profile
                    </a>
                </div>
            </div>
        </div>

        <!-- Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mt-5'); ?>
    </main>
<?php else: ?>

    <!-- Candidate Profile Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    <?php echo htmlspecialchars($candidate['party']); ?>
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">Seat: <?php echo htmlspecialchars($candidate['constituency']); ?></span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">District: <?php echo htmlspecialchars($candidate['district']); ?></span>
                <?php if (!empty($candidate['verified'])): ?>
                    <span class="badge bg-primary text-white fw-bold px-3 py-2"><i class="bi bi-patch-check-fill"></i> ECI Affidavit Verified</span>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-3 gap-md-4 flex-wrap">
                <img src="<?php echo $candidate['photo']; ?>" alt="<?php echo htmlspecialchars($candidate['name']); ?>" class="rounded-circle border border-4 border-white shadow" style="width: 100px; height: 100px; object-fit: cover;">
                <div>
                    <h1 class="display-6 fw-extrabold text-white mb-1">
                        <?php echo htmlspecialchars($candidate['name']); ?> 
                        <span style="color: var(--accent-saffron); font-size: 1.6rem;">(<?php echo htmlspecialchars($candidate['name_hi']); ?>)</span>
                    </h1>
                    <p class="lead text-white-50 mb-0" style="font-size: 1.05rem;">
                        <?php echo htmlspecialchars($candidate['designation']); ?>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Profile Content -->
    <main class="container my-4 my-lg-5">
        <div class="row g-4">
            
            <!-- Left Column: Biography, Disclosures & Track Record (8 Cols) -->
            <div class="col-12 col-lg-8">

                <!-- Candidate Switcher Dropdown -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Switch Candidate Profile:</label>
                    <select class="form-select form-select-lg" onchange="window.location.href='candidate.php?slug=' + this.value" style="font-size: 1rem;">
                        <?php foreach ($allCandidates as $cItem): ?>
                            <option value="<?php echo $cItem['slug']; ?>" <?php echo $cItem['slug'] == $candidate['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cItem['name']); ?> (<?php echo $cItem['party_short']; ?>) — <?php echo htmlspecialchars($cItem['constituency']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Declared Affidavit Summary Grid -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                            📑 Official Affidavit Disclosures
                        </h2>
                        <span class="badge bg-info bg-opacity-25 text-dark fw-bold">ECI Public Record</span>
                    </div>

                    <div class="row g-2 g-md-3">
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Declared Assets</div>
                                <div class="h5 fw-bold text-success mb-0"><?php echo $candidate['assets_declared']; ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Liabilities</div>
                                <div class="h5 fw-bold text-danger mb-0"><?php echo $candidate['liabilities']; ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Criminal Cases</div>
                                <div class="h5 fw-bold <?php echo $candidate['criminal_cases'] > 0 ? 'text-danger' : 'text-success'; ?> mb-0">
                                    <?php echo $candidate['criminal_cases']; ?> Cases
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Education</div>
                                <div class="small fw-bold text-navy text-truncate mt-1" title="<?php echo htmlspecialchars($candidate['education']); ?>">
                                    <?php echo htmlspecialchars($candidate['education']); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Biography -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📖 Biography & Political Journey
                    </h2>
                    <p class="small text-muted mb-0 lh-lg">
                        <?php echo htmlspecialchars($candidate['bio']); ?>
                    </p>
                </div>

                <!-- Official In-Content Ad -->
                <?php renderGoogleAd('in_feed', GOOGLE_AD_SLOT_INFEED, 'my-4'); ?>

                <!-- Election History Table -->
                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        🗳️ Election Track Record
                    </h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Year</th>
                                    <th>Election</th>
                                    <th>Constituency</th>
                                    <th>Result</th>
                                    <th>Votes Polled</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($candidate['election_record'] as $rec): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo $rec['year']; ?></td>
                                    <td><?php echo $rec['election']; ?></td>
                                    <td class="fw-semibold"><?php echo $rec['constituency']; ?></td>
                                    <td>
                                        <span class="badge <?php echo $rec['result'] === 'Won' ? 'bg-success' : 'bg-secondary'; ?>">
                                            <?php echo $rec['result']; ?>
                                        </span>
                                    </td>
                                    <td class="fw-bold"><?php echo number_format($rec['votes']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Right Column: Sidebar (4 Cols) -->
            <div class="col-12 col-lg-4">
                
                <!-- Claim / Premium Upgrade Box -->
                <div class="card border-0 shadow-sm rounded-3 p-3 p-md-4 text-white mb-4" style="background: linear-gradient(135deg, #1e3e62, #0b192c);">
                    <span class="badge bg-warning text-dark fw-bold align-self-start mb-2">Candidate PR Branding</span>
                    <h3 class="h6 fw-bold mb-1">Is this your political profile?</h3>
                    <p class="small text-white-50 mb-3">
                        Publish your complete manifesto, video gallery, and WhatsApp broadcast channels to 10,000+ local voters.
                    </p>
                    <a href="advertise.php?category=candidate&claim=<?php echo $candidate['slug']; ?>" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
                        Upgrade to VIP Profile &rarr;
                    </a>
                </div>

                <!-- Google AdSense Sidebar Rectangle -->
                <?php renderGoogleAd('sidebar', GOOGLE_AD_SLOT_SIDEBAR, 'mb-4'); ?>

                <!-- Connect Social Links -->
                <div class="card border-0 shadow-sm rounded-3 p-3 p-md-4 bg-white">
                    <h3 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        🔗 Official Social Links
                    </h3>
                    <div class="d-flex flex-column gap-2">
                        <?php foreach ($candidate['social_links'] as $platform => $link): ?>
                            <a href="<?php echo htmlspecialchars($link); ?>" target="_blank" class="btn btn-light btn-sm text-start fw-semibold d-flex justify-content-between align-items-center">
                                <span><?php echo ucfirst($platform); ?> Profile</span>
                                <i class="bi bi-box-arrow-up-right small"></i>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            </div>

        </div>
    </main>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
