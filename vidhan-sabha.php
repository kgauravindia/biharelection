<?php
require_once __DIR__ . '/config.php';

$acParam = $_GET['ac'] ?? $_GET['id'] ?? $_GET['slug'] ?? 118;
$ac = null;

if (is_numeric($acParam)) {
    $ac = DataProvider::getConstituencyByAcNumber((int)$acParam);
} else {
    $ac = DataProvider::getConstituencyBySlug($acParam);
}

if (!$ac) {
    $ac = DataProvider::getConstituencyByAcNumber(118);
}

$allAcs = DataProvider::getConstituencies();
$districtAcs = DataProvider::getConstituenciesByDistrict($ac['district']);

$res2025 = $ac['election_2025'] ?? $ac['election_2020'];
$allCandidates = $ac['all_candidates_2025'] ?? [];
$mla2015 = DataProvider::getMlas2015($ac['ac_no']);

$pageTitle = "AC {$ac['ac_no']} — {$ac['name']} Assembly Constituency Vidhan Sabha 2025 Results, MLA & Voter Demographics";
$pageDescription = "Complete official electoral results of AC {$ac['ac_no']} {$ac['name']} Assembly seat in {$ac['district']} district. Vidhan Sabha 2025 Winner {$res2025['winner']} ({$res2025['winner_party']}), Runner-up {$res2025['runner_up']} ({$res2025['runner_up_party']}), margin {$res2025['margin']} votes, voter demographics and complete candidate-wise polling results.";
$pageKeywords = "AC {$ac['ac_no']} {$ac['name']}, {$ac['name']} Vidhan Sabha 2025 Results, {$ac['name']} MLA {$ac['current_mla']}, {$ac['district']} Election, Bihar Vidhan Sabha {$ac['name']} Result";
$pageCanonical = getMlaUrl($ac);
$activeNav = 'assembly';

require_once __DIR__ . '/header.php';
?>

    <!-- Constituency Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">AC #<?php echo $ac['ac_no']; ?></span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2">
                    <?php echo $ac['reservation'] === 'GEN' ? 'General (GEN)' : ($ac['reservation'] === 'SC' ? 'Scheduled Caste (SC)' : 'Scheduled Tribe (ST)'); ?>
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">District: <?php echo htmlspecialchars($ac['district']); ?></span>
                <?php if (!empty($ac['lok_sabha'])): ?>
                    <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">Lok Sabha: <?php echo htmlspecialchars($ac['lok_sabha']); ?></span>
                <?php endif; ?>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                AC <?php echo $ac['ac_no']; ?> — <?php echo htmlspecialchars($ac['name']); ?> 
                <span style="color: var(--accent-saffron); font-size: 1.6rem;">(<?php echo htmlspecialchars($ac['name_hi']); ?>)</span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem;">
                Elected MLA: <strong class="text-white"><?php echo htmlspecialchars($res2025['winner']); ?></strong> (<?php echo $res2025['winner_party']; ?>) | Electors: <strong class="text-white"><?php echo number_format($ac['total_electors']); ?></strong> | Winning Margin: <strong class="text-white"><?php echo number_format($res2025['margin']); ?></strong> votes
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Bihar Vidhan Sabha AC {$ac['ac_no']} {$ac['name']} official election results & candidate breakdown: " . SITE_URL . "/vidhan-sabha.php?ac=" . $ac['ac_no']); ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp"></i> Share AC Result on WhatsApp
                </a>
                <a href="advertise.php?sponsor_ac=<?php echo $ac['ac_no']; ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-megaphone"></i> Sponsor This Seat (₹4,999/yr)
                </a>
            </div>
        </div>
    </section>

    <!-- Main Data Grid Container -->
    <main class="container my-4 my-lg-5">

        <!-- Top Leaderboard Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <div class="row g-4">
            
            <!-- Left Column: Detailed Election Data (8 Cols) -->
            <div class="col-12 col-lg-8">

                <!-- Quick AC Switcher Select Dropdown -->
                <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-white">
                    <label class="form-label fw-bold small text-muted text-uppercase mb-2">Switch Assembly Constituency (243 Seats):</label>
                    <select class="form-select form-select-lg" onchange="window.location.href=this.value" style="font-size: 1rem;">
                        <?php foreach ($allAcs as $item): ?>
                            <option value="<?php echo getMlaUrl($item); ?>" <?php echo $item['ac_no'] == $ac['ac_no'] ? 'selected' : ''; ?>>
                                AC <?php echo $item['ac_no']; ?> — <?php echo htmlspecialchars($item['name']); ?> (District: <?php echo htmlspecialchars($item['district']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- 2025 Official Election Result Card -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                            🗳️ Bihar Vidhan Sabha 2025 Official Result
                        </h2>
                        <span class="badge bg-success fw-bold px-3 py-2">Turnout: <?php echo $res2025['turnout_percent']; ?>%</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Winner Block -->
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-success h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-success text-uppercase">WINNER (Elected)</span>
                                    <span class="badge-party <?php echo strtolower($res2025['winner_party']); ?>"><?php echo $res2025['winner_party']; ?></span>
                                </div>
                                <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);"><?php echo htmlspecialchars($res2025['winner']); ?></h3>
                                <div class="small text-muted mb-2">
                                    Symbol: <strong><?php echo htmlspecialchars($res2025['winner_symbol'] ?? ''); ?></strong>
                                    <?php if (!empty($res2025['winner_age'])): ?>
                                        | Age: <strong><?php echo $res2025['winner_age']; ?></strong>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="small text-muted">Votes Secured:</span>
                                    <span class="h6 fw-bold text-success mb-0"><?php echo number_format($res2025['winner_votes'] ?? 0); ?><?php if (isset($res2025['winner_vote_share']) && $res2025['winner_vote_share'] !== ''): ?> (<?php echo $res2025['winner_vote_share']; ?>%)<?php endif; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Runner-up Block -->
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-secondary h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary text-uppercase">RUNNER-UP</span>
                                    <span class="badge-party <?php echo strtolower($res2025['runner_up_party'] ?? 'other'); ?>"><?php echo $res2025['runner_up_party'] ?? '-'; ?></span>
                                </div>
                                <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);"><?php echo htmlspecialchars($res2025['runner_up'] ?? '-'); ?></h3>
                                <div class="small text-muted mb-2">Party: <strong><?php echo htmlspecialchars($res2025['runner_up_party'] ?? '-'); ?></strong></div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="small text-muted">Votes Secured:</span>
                                    <span class="h6 fw-bold text-secondary mb-0"><?php echo number_format($res2025['runner_up_votes'] ?? 0); ?><?php if (isset($res2025['runner_up_vote_share']) && $res2025['runner_up_vote_share'] !== ''): ?> (<?php echo $res2025['runner_up_vote_share']; ?>%)<?php endif; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                        <div>
                            <i class="bi bi-trophy-fill text-warning"></i> Victory Margin: <strong class="text-navy"><?php echo number_format($res2025['margin']); ?></strong> votes
                        </div>
                        <div>
                            Total Valid Votes: <strong><?php echo number_format($res2025['valid_votes'] ?? 0); ?></strong> | NOTA: <strong><?php echo number_format($res2025['nota_votes'] ?? 0); ?></strong>
                        </div>
                    </div>
                </div>

                <!-- Mid-Content In-Feed Ad Unit -->
                <?php renderGoogleAd('in_feed', GOOGLE_AD_SLOT_INFEED, 'my-4'); ?>

                <!-- Historical Comparison Table (2020 vs 2015) -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📜 Historical Election Results: 2020 vs 2015
                    </h2>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Election Year</th>
                                    <th>Winner Name</th>
                                    <th>Party</th>
                                    <th>Votes Secured</th>
                                    <th>Runner-up</th>
                                    <th>Margin</th>
                                    <th>Turnout</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-primary">2020 Assembly</span></td>
                                    <td class="fw-bold text-navy"><?php echo htmlspecialchars($ac['election_2020']['winner']); ?></td>
                                    <td><span class="badge-party <?php echo strtolower($ac['election_2020']['winner_party']); ?>"><?php echo $ac['election_2020']['winner_party']; ?></span></td>
                                    <td><?php echo number_format($ac['election_2020']['winner_votes']); ?> (<?php echo $ac['election_2020']['winner_vote_share']; ?>%)</td>
                                    <td><?php echo htmlspecialchars($ac['election_2020']['runner_up']); ?> (<?php echo $ac['election_2020']['runner_up_party']; ?>)</td>
                                    <td class="text-success fw-bold"><?php echo number_format($ac['election_2020']['margin']); ?></td>
                                    <td><?php echo $ac['election_2020']['turnout_percent']; ?>%</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-secondary">2015 Assembly</span></td>
                                    <td>
                                        <div class="fw-bold text-navy"><?php echo htmlspecialchars($mla2015['mla_name'] ?? $ac['election_2015']['winner']); ?></div>
                                        <?php if (!empty($mla2015['mobile'])): ?>
                                            <span class="badge bg-light text-secondary border py-0 px-2 fw-semibold mt-1 d-inline-flex align-items-center gap-1 extra-small" title="Contact Protected">
                                                <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($mla2015['mobile'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-party <?php echo strtolower($mla2015['party'] ?? $ac['election_2015']['winner_party']); ?>"><?php echo $mla2015['party'] ?? $ac['election_2015']['winner_party']; ?></span></td>
                                    <td><?php echo number_format($ac['election_2015']['winner_votes'] ?? 0); ?> <?php if (!empty($ac['election_2015']['winner_vote_share'])): ?>(<?php echo $ac['election_2015']['winner_vote_share']; ?>%)<?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($ac['election_2015']['runner_up'] ?? '-'); ?> <?php if (!empty($ac['election_2015']['runner_up_party'])): ?>(<?php echo $ac['election_2015']['runner_up_party']; ?>)<?php endif; ?></td>
                                    <td class="text-success fw-bold"><?php echo number_format($ac['election_2015']['margin'] ?? 0); ?></td>
                                    <td><?php echo $ac['election_2015']['turnout_percent'] ?? '-'; ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 2026 Expected Candidates & Political Lineup -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                            🎯 Expected Candidates Lineup — Bihar 2026
                        </h2>
                        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">Live Aspirant Roster</span>
                    </div>

                    <div class="row g-3">
                        <?php if (!empty($ac['candidates_2026_expected'])): ?>
                            <?php foreach ($ac['candidates_2026_expected'] as $asp): ?>
                            <div class="col-12 col-sm-6">
                                <div class="p-3 border rounded-3 bg-light h-100">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <h3 class="h6 fw-bold mb-0 text-navy"><?php echo htmlspecialchars($asp['name']); ?></h3>
                                        <span class="badge-party <?php echo strtolower($asp['party']); ?>"><?php echo $asp['party']; ?></span>
                                    </div>
                                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($asp['status']); ?></p>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary small">Alliance: <?php echo htmlspecialchars($asp['alliance']); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-muted small">No verified aspirant profiles announced yet for 2026.</div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Complete 2025 Candidate Results Table -->
                <?php if (!empty($allCandidates)): ?>
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                            📋 All Contesting Candidates & Vote Counts (2025)
                        </h2>
                        <span class="badge bg-light text-dark border"><?php echo count($allCandidates); ?> Candidates</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Candidate Name</th>
                                    <th>Party</th>
                                    <th>Category</th>
                                    <th>EVM Votes</th>
                                    <th>Postal</th>
                                    <th>Total Votes</th>
                                    <th>% Votes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allCandidates as $cand): ?>
                                <tr class="<?php echo $cand['candidate_name'] === $res2025['winner'] ? 'table-success' : ''; ?>">
                                    <td class="fw-bold">
                                        <?php echo htmlspecialchars($cand['candidate_name']); ?>
                                        <?php if ($cand['candidate_name'] === $res2025['winner']): ?>
                                            <span class="badge bg-success ms-1">Winner</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-party <?php echo strtolower($cand['party']); ?>"><?php echo $cand['party']; ?></span></td>
                                    <td><?php echo htmlspecialchars($cand['category'] ?? '-'); ?></td>
                                    <td><?php echo number_format($cand['votes_general']); ?></td>
                                    <td><?php echo number_format($cand['votes_postal']); ?></td>
                                    <td class="fw-bold"><?php echo number_format($cand['votes_total']); ?></td>
                                    <td class="fw-bold"><?php echo $cand['vote_share_valid']; ?>%</td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Demographic & Elector Snapshot -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📊 Voter Demographics & Electoral Profile
                    </h2>
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Total Electors</div>
                                <div class="h5 fw-bold text-navy mb-0"><?php echo number_format($ac['total_electors']); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Male Electors</div>
                                <div class="h5 fw-bold text-primary mb-0"><?php echo number_format($ac['male_electors']); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Female Electors</div>
                                <div class="h5 fw-bold text-danger mb-0"><?php echo number_format($ac['female_electors']); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Turnout %</div>
                                <div class="h5 fw-bold text-success mb-0"><?php echo $res2025['turnout_percent']; ?>%</div>
                            </div>
                        </div>
                    </div>
                    <div class="small text-muted">
                        <strong>Covered Blocks:</strong> <?php echo implode(', ', $ac['blocks']); ?> | <strong>Total Panchayats:</strong> <?php echo $ac['total_panchayats']; ?>
                    </div>
                </div>

                <!-- Key Issues -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        🎯 <?php echo htmlspecialchars($ac['name']); ?> Key Issues & Political Demands
                    </h2>
                    <ul class="mb-0 text-muted small lh-lg">
                        <?php foreach ($ac['key_issues'] as $issue): ?>
                            <li><?php echo htmlspecialchars($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Political History & Context -->
                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📜 Political History & Trend Analysis
                    </h2>
                    <p class="small text-muted mb-0 lh-lg">
                        <?php echo htmlspecialchars($ac['party_history']); ?>
                    </p>
                </div>

            </div>

            <!-- Right Column: Sidebar (4 Cols) -->
            <div class="col-12 col-lg-4">
                
                <!-- Sponsor Card -->
                <div class="card border-0 shadow-sm rounded-3 p-3 p-md-4 text-white mb-4" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
                    <span class="badge bg-warning text-dark fw-bold align-self-start mb-2">Commercial Slot</span>
                    <h3 class="h6 fw-bold mb-1"><?php echo htmlspecialchars($ac['name']); ?> Page Sponsor</h3>
                    <p class="small text-white-50 mb-3">Showcase your coaching academy, medical clinic, legal firm, or business to thousands of active local voters.</p>
                    <div class="h5 fw-bold text-warning mb-3">₹4,999 / year</div>
                    <a href="advertise.php?sponsor_ac=<?php echo $ac['ac_no']; ?>" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
                        Book This AC Slot &rarr;
                    </a>
                </div>

                <!-- District Neighboring Seats -->
                <div class="card border-0 shadow-sm rounded-3 p-3 p-md-4 bg-white mb-4">
                    <h3 class="h6 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📍 Other Seats in <?php echo htmlspecialchars($ac['district']); ?> District
                    </h3>
                    <ul class="list-unstyled mb-0 small">
                        <?php foreach ($districtAcs as $dAc): ?>
                            <?php if ($dAc['ac_no'] != $ac['ac_no']): ?>
                                <li class="mb-2">
                                    <a href="<?php echo getMlaUrl($dAc); ?>" class="text-decoration-none fw-semibold text-navy">
                                        AC <?php echo $dAc['ac_no']; ?> — <?php echo htmlspecialchars($dAc['name']); ?>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Google AdSense Sidebar Rectangle -->
                <?php renderGoogleAd('sidebar', GOOGLE_AD_SLOT_SIDEBAR, 'mb-4'); ?>

            </div>

        </div>

        <!-- Bottom Footer Ad Banner -->
        <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'mt-4'); ?>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
