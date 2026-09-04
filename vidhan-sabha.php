<?php
require_once __DIR__ . '/config.php';

$acParam = $_GET['ac'] ?? $_GET['id'] ?? $_GET['slug'] ?? 182;
$ac = null;

if (is_numeric($acParam)) {
    $ac = DataProvider::getConstituencyByAcNumber((int)$acParam);
} else {
    $ac = DataProvider::getConstituencyBySlug($acParam);
}

if (!$ac) {
    $ac = DataProvider::getConstituencyByAcNumber(182);
}
if (!$ac) {
    $ac = [
        'id' => 182,
        'ac_no' => 182,
        'name' => 'Bankipur',
        'name_hi' => 'बांकीपुर',
        'slug' => 'bankipur',
        'district' => 'Patna',
        'district_hi' => 'पटना',
        'lok_sabha' => 'Patna Sahib',
        'reservation' => 'GEN',
        'current_mla' => 'Prashant Kishor',
        'current_party' => 'Jan Suraaj Party',
        'total_electors' => 379402,
        'male_electors' => 200212,
        'female_electors' => 179166,
        'polling_stations' => 300
    ];
}

$allAcs = DataProvider::getConstituencies();
$districtAcs = DataProvider::getConstituenciesByDistrict($ac['district']);

// Multi-Year Election Data
$acNo = (int)$ac['ac_no'];
$res2025 = DataProvider::getElectionSuccessfulCandidates($acNo, 2025) ?: ($ac['election_2025'] ?? $ac['election_2020'] ?? []);
$res2020 = DataProvider::getElectionSuccessfulCandidates($acNo, 2020) ?: ($ac['election_2020'] ?? []);
$sum2020 = DataProvider::getElectionAcSummary($acNo, 2020) ?: [];
$res2015 = DataProvider::getElectionSuccessfulCandidates($acNo, 2015) ?: ($ac['election_2015']['summary'] ?? []);
$sum2015 = DataProvider::getElectionAcSummary($acNo, 2015) ?: [];
$cands2025 = DataProvider::getElectionDetailedResults($acNo, 2025);
$cands2020 = DataProvider::getElectionDetailedResults($acNo, 2020);
$cands2015 = DataProvider::getElectionDetailedResults($acNo, 2015);
$byeElections = DataProvider::getByeElectionDetailedResults($acNo);
$mla2015 = DataProvider::getMlas2015($acNo);

// Determine active winner for banner
$hasByeElection = !empty($byeElections);
$displayWinner = $hasByeElection ? ($byeElections[0]['candidate_name'] ?? $ac['current_mla']) : ($res2025['winner_name'] ?? $ac['current_mla'] ?? $res2020['winner_name'] ?? 'Elected MLA');
$displayParty = $hasByeElection ? ($byeElections[0]['party'] ?? $ac['current_party']) : ($res2025['winner_party'] ?? $ac['current_party'] ?? $res2020['winner_party'] ?? '-');
$displayMargin = $hasByeElection ? ($byeElections[0]['margin'] ?? $res2025['margin'] ?? $res2020['margin'] ?? 0) : ($res2025['margin'] ?? $res2020['margin'] ?? 0);

$totalElectors = !empty($ac['total_electors']) ? $ac['total_electors'] : ($sum2020['total_electors'] ?? 0);

$pageTitle = "AC {$ac['ac_no']} — {$ac['name']} Assembly Constituency Results, MLA {$displayWinner} & Voter Demographics";
$pageDescription = "Complete official electoral results of AC {$ac['ac_no']} {$ac['name']} Assembly seat in {$ac['district']} district. Vidhan Sabha Winner {$displayWinner} ({$displayParty}), winning margin " . number_format($displayMargin) . " votes, multi-year 2025, 2020 & 2015 candidate breakdown and voter demographics.";
$pageKeywords = "AC {$ac['ac_no']} {$ac['name']}, {$ac['name']} Vidhan Sabha Results, {$ac['name']} MLA {$displayWinner}, {$ac['district']} Election, Bihar Vidhan Sabha {$ac['name']} Result, Narpatganj Election";
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
                    <?php echo ($ac['reservation'] ?? 'GEN') === 'GEN' ? 'General (GEN)' : (($ac['reservation'] ?? '') === 'SC' ? 'Scheduled Caste (SC)' : 'Scheduled Tribe (ST)'); ?>
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">District: <?php echo htmlspecialchars($ac['district']); ?></span>
                <?php if (!empty($ac['lok_sabha'])): ?>
                    <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">Lok Sabha: <?php echo htmlspecialchars($ac['lok_sabha']); ?></span>
                <?php endif; ?>
                <?php if ($hasByeElection): ?>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2"><i class="bi bi-lightning-charge-fill"></i> Bye-Election Seat</span>
                <?php endif; ?>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                AC <?php echo $ac['ac_no']; ?> — <?php echo htmlspecialchars($ac['name']); ?> 
                <span style="color: var(--accent-saffron); font-size: 1.6rem;">(<?php echo htmlspecialchars($ac['name_hi'] ?? $ac['name']); ?>)</span>
            </h1>
            <p class="lead text-white-50 mb-4" style="font-size: 1.05rem;">
                Elected MLA: <strong class="text-white"><?php echo htmlspecialchars($displayWinner); ?></strong> (<?php echo htmlspecialchars($displayParty); ?>) | Total Electors: <strong class="text-white"><?php echo number_format($totalElectors); ?></strong> | Victory Margin: <strong class="text-white"><?php echo number_format($displayMargin); ?></strong> votes
            </p>

            <div class="d-flex flex-wrap gap-2">
                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Bihar Vidhan Sabha AC {$ac['ac_no']} {$ac['name']} official election results & candidate breakdown: " . SITE_URL . "/mla/" . $ac['ac_no'] . "-" . $ac['slug']); ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-2 shadow-sm">
                    <i class="bi bi-whatsapp"></i> Share AC Result on WhatsApp
                </a>
                <a href="<?php echo getAdvertiseUrl(['sponsor_ac' => $ac['ac_no']]); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
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

                <!-- Bye-Election Special Alert Banner (If Any) -->
                <?php if ($hasByeElection): ?>
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-warning text-dark fw-bold"><i class="bi bi-star-fill me-1"></i> Latest Bye-Election Results (2026)</span>
                        <span class="badge bg-success fw-bold">EVM Counting 32/32 Completed</span>
                    </div>
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-7">
                            <h3 class="h5 fw-bold mb-1 text-navy"><?php echo htmlspecialchars($byeElections[0]['candidate_name']); ?></h3>
                            <div class="small text-muted mb-1">
                                Party: <span class="badge-party <?php echo strtolower($byeElections[0]['party']); ?>"><?php echo htmlspecialchars($byeElections[0]['party']); ?></span>
                                | Votes: <strong class="text-success"><?php echo number_format($byeElections[0]['votes_total']); ?></strong> (<?php echo $byeElections[0]['vote_share_valid']; ?>%)
                            </div>
                            <div class="small text-muted">
                                Defeated <strong><?php echo htmlspecialchars($byeElections[1]['candidate_name'] ?? ''); ?></strong> (<?php echo htmlspecialchars($byeElections[1]['party'] ?? ''); ?>) by <strong>+<?php echo number_format($byeElections[0]['margin']); ?></strong> votes.
                            </div>
                        </div>
                        <div class="col-12 col-md-5 text-md-end">
                            <div class="p-2 bg-light rounded-3 d-inline-block text-start small">
                                <div>Total Polled Votes: <strong><?php echo number_format(array_sum(array_column($byeElections, 'votes_total'))); ?></strong></div>
                                <div>NOTA Votes: <strong><?php echo number_format(end($byeElections)['votes_total'] ?? 0); ?></strong></div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- 2025 Official Election Result Card -->
                <?php if (!empty($res2025['winner_name']) || !empty($res2025['winner'])): ?>
                <?php 
                    $wName25 = $res2025['winner_name'] ?? $res2025['winner'] ?? '';
                    $wParty25 = $res2025['winner_party'] ?? '';
                    $wVotes25 = $res2025['winner_votes'] ?? 0;
                    $rName25 = $res2025['runner_up_name'] ?? $res2025['runner_up'] ?? '';
                    $rParty25 = $res2025['runner_up_party'] ?? '';
                    $rVotes25 = $res2025['runner_up_votes'] ?? 0;
                    $margin25 = $res2025['margin'] ?? 0;
                    $turnout25 = $res2025['turnout_percent'] ?? ($sum2020['turnout_percent'] ?? '-');
                ?>
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                            🗳️ Bihar Vidhan Sabha 2025 Official Result
                        </h2>
                        <span class="badge bg-success fw-bold px-3 py-2">Turnout: <?php echo $turnout25; ?>%</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <!-- Winner Block -->
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-success h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-success text-uppercase">WINNER (Elected)</span>
                                    <span class="badge-party <?php echo strtolower($wParty25); ?>"><?php echo htmlspecialchars($wParty25); ?></span>
                                </div>
                                <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);"><?php echo htmlspecialchars($wName25); ?></h3>
                                <div class="small text-muted mb-2">
                                    Symbol: <strong><?php echo htmlspecialchars($res2025['winner_symbol'] ?? ''); ?></strong>
                                    <?php if (!empty($res2025['winner_age'])): ?>
                                        | Age: <strong><?php echo $res2025['winner_age']; ?></strong>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="small text-muted">Votes Secured:</span>
                                    <span class="h6 fw-bold text-success mb-0"><?php echo number_format($wVotes25); ?><?php if (!empty($res2025['winner_vote_share'])): ?> (<?php echo $res2025['winner_vote_share']; ?>%)<?php endif; ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Runner-up Block -->
                        <div class="col-12 col-md-6">
                            <div class="bg-light p-3 rounded-3 border-start border-4 border-secondary h-100">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary text-uppercase">RUNNER-UP</span>
                                    <span class="badge-party <?php echo strtolower($rParty25 ?: 'other'); ?>"><?php echo htmlspecialchars($rParty25 ?: '-'); ?></span>
                                </div>
                                <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);"><?php echo htmlspecialchars($rName25 ?: '-'); ?></h3>
                                <div class="small text-muted mb-2">Party: <strong><?php echo htmlspecialchars($rParty25 ?: '-'); ?></strong></div>
                                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                    <span class="small text-muted">Votes Secured:</span>
                                    <span class="h6 fw-bold text-secondary mb-0"><?php echo number_format($rVotes25); ?><?php if (!empty($res2025['runner_up_vote_share'])): ?> (<?php echo $res2025['runner_up_vote_share']; ?>%)<?php endif; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 d-flex flex-wrap justify-content-between align-items-center gap-2 small">
                        <div>
                            <i class="bi bi-trophy-fill text-warning"></i> Victory Margin: <strong class="text-navy"><?php echo number_format($margin25); ?></strong> votes
                        </div>
                        <div>
                            Status: <span class="badge bg-primary">Declared Official</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Mid-Content In-Feed Ad Unit -->
                <?php renderGoogleAd('in_feed', GOOGLE_AD_SLOT_INFEED, 'my-4'); ?>

                <!-- Interactive Multi-Year Candidate Results Section with Tabs -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <h2 class="h5 fw-bold mb-0" style="color: var(--primary-navy);">
                            📋 Contesting Candidates & Complete Vote Counts
                        </h2>
                        <ul class="nav nav-pills small" id="electionTab" role="tablist">
                            <?php if (!empty($cands2025)): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-bold py-1 px-3" id="tab-2025-btn" data-bs-toggle="pill" data-bs-target="#tab-2025" type="button" role="tab">
                                    2025 Assembly (<?php echo count($cands2025); ?>)
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($cands2020)): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link <?php echo empty($cands2025) ? 'active' : ''; ?> fw-bold py-1 px-3" id="tab-2020-btn" data-bs-toggle="pill" data-bs-target="#tab-2020" type="button" role="tab">
                                    2020 Assembly (<?php echo count($cands2020); ?>)
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if (!empty($cands2015)): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold py-1 px-3" id="tab-2015-btn" data-bs-toggle="pill" data-bs-target="#tab-2015" type="button" role="tab">
                                    2015 Assembly (<?php echo count($cands2015); ?>)
                                </button>
                            </li>
                            <?php endif; ?>
                            <?php if ($hasByeElection): ?>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-bold py-1 px-3 text-warning-emphasis" id="tab-bye-btn" data-bs-toggle="pill" data-bs-target="#tab-bye" type="button" role="tab">
                                    2026 Bye-Election (<?php echo count($byeElections); ?>)
                                </button>
                            </li>
                            <?php endif; ?>
                        </ul>
                    </div>

                    <div class="tab-content" id="electionTabContent">
                        
                        <!-- 2025 Candidates Table Pane -->
                        <?php if (!empty($cands2025)): ?>
                        <div class="tab-pane fade show active" id="tab-2025" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
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
                                        <?php $idx25 = 1; foreach ($cands2025 as $cand): ?>
                                        <tr class="<?php echo $idx25 === 1 ? 'table-success' : ''; ?>">
                                             <td class="text-muted"><?php echo $idx25; ?></td>
                                            <td class="fw-bold">
                                                <?php echo htmlspecialchars($cand['candidate_name']); ?>
                                                <?php if ($idx25 === 1): ?>
                                                    <span class="badge bg-success ms-1">Winner</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge-party <?php echo strtolower($cand['party']); ?>"><?php echo htmlspecialchars($cand['party']); ?></span></td>
                                            <td><?php echo htmlspecialchars($cand['category'] ?? '-'); ?></td>
                                            <td><?php echo number_format($cand['votes_general']); ?></td>
                                            <td><?php echo number_format($cand['votes_postal']); ?></td>
                                            <td class="fw-bold"><?php echo number_format($cand['votes_total']); ?></td>
                                            <td class="fw-bold text-success"><?php echo $cand['vote_share_valid']; ?>%</td>
                                        </tr>
                                        <?php $idx25++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 2020 Candidates Table Pane -->
                        <?php if (!empty($cands2020)): ?>
                        <div class="tab-pane fade <?php echo empty($cands2025) ? 'show active' : ''; ?>" id="tab-2020" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
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
                                        <?php $idx20 = 1; foreach ($cands2020 as $cand): ?>
                                        <tr class="<?php echo $idx20 === 1 ? 'table-success' : ''; ?>">
                                            <td class="text-muted"><?php echo $idx20; ?></td>
                                            <td class="fw-bold">
                                                <?php echo htmlspecialchars($cand['candidate_name']); ?>
                                                <?php if ($idx20 === 1): ?>
                                                    <span class="badge bg-success ms-1">Winner</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge-party <?php echo strtolower($cand['party']); ?>"><?php echo htmlspecialchars($cand['party']); ?></span></td>
                                            <td><?php echo htmlspecialchars($cand['category'] ?? '-'); ?></td>
                                            <td><?php echo number_format($cand['votes_general']); ?></td>
                                            <td><?php echo number_format($cand['votes_postal']); ?></td>
                                            <td class="fw-bold"><?php echo number_format($cand['votes_total']); ?></td>
                                            <td class="fw-bold text-primary"><?php echo $cand['vote_share_valid']; ?>%</td>
                                        </tr>
                                        <?php $idx20++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- 2015 Candidates Table Pane -->
                        <?php if (!empty($cands2015)): ?>
                        <div class="tab-pane fade" id="tab-2015" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
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
                                        <?php $idx15 = 1; foreach ($cands2015 as $cand): ?>
                                        <tr class="<?php echo $idx15 === 1 ? 'table-success' : ''; ?>">
                                            <td class="text-muted"><?php echo $idx15; ?></td>
                                            <td class="fw-bold">
                                                <?php echo htmlspecialchars($cand['candidate_name']); ?>
                                                <?php if ($idx15 === 1): ?>
                                                    <span class="badge bg-success ms-1">Winner</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge-party <?php echo strtolower($cand['party']); ?>"><?php echo htmlspecialchars($cand['party']); ?></span></td>
                                            <td><?php echo htmlspecialchars($cand['category'] ?? '-'); ?></td>
                                            <td><?php echo number_format($cand['votes_general']); ?></td>
                                            <td><?php echo number_format($cand['votes_postal']); ?></td>
                                            <td class="fw-bold"><?php echo number_format($cand['votes_total']); ?></td>
                                            <td class="fw-bold text-secondary"><?php echo $cand['vote_share_valid']; ?>%</td>
                                        </tr>
                                        <?php $idx15++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Bye-Election Table Pane -->
                        <?php if ($hasByeElection): ?>
                        <div class="tab-pane fade" id="tab-bye" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>#</th>
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
                                        <?php $idxBye = 1; foreach ($byeElections as $cand): ?>
                                        <tr class="<?php echo $idxBye === 1 ? 'table-success' : ''; ?>">
                                            <td class="text-muted"><?php echo $idxBye; ?></td>
                                            <td class="fw-bold">
                                                <?php echo htmlspecialchars($cand['candidate_name']); ?>
                                                <?php if ($idxBye === 1): ?>
                                                    <span class="badge bg-success ms-1">Elected MLA</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge-party <?php echo strtolower($cand['party']); ?>"><?php echo htmlspecialchars($cand['party']); ?></span></td>
                                            <td><?php echo htmlspecialchars($cand['category'] ?? '-'); ?></td>
                                            <td><?php echo number_format($cand['votes_general']); ?></td>
                                            <td><?php echo number_format($cand['votes_postal']); ?></td>
                                            <td class="fw-bold"><?php echo number_format($cand['votes_total']); ?></td>
                                            <td class="fw-bold text-success"><?php echo $cand['vote_share_valid']; ?>%</td>
                                        </tr>
                                        <?php $idxBye++; endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Historical Comparison Table (2025 vs 2020 vs 2015) -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📜 Historical Assembly Elections (2025 vs 2020 vs 2015)
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
                                    <th>Victory Margin</th>
                                    <th>Turnout</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($res2025['winner_name']) || !empty($res2025['winner'])): ?>
                                <tr>
                                    <td><span class="badge bg-success">2025 Assembly</span></td>
                                    <td class="fw-bold text-navy"><?php echo htmlspecialchars($res2025['winner_name'] ?? $res2025['winner']); ?></td>
                                    <td><span class="badge-party <?php echo strtolower($res2025['winner_party'] ?? ''); ?>"><?php echo htmlspecialchars($res2025['winner_party'] ?? '-'); ?></span></td>
                                    <td><?php echo number_format($res2025['winner_votes'] ?? 0); ?><?php if (!empty($res2025['winner_vote_share'])): ?> (<?php echo $res2025['winner_vote_share']; ?>%)<?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($res2025['runner_up_name'] ?? $res2025['runner_up'] ?? '-'); ?> (<?php echo htmlspecialchars($res2025['runner_up_party'] ?? '-'); ?>)</td>
                                    <td class="text-success fw-bold"><?php echo number_format($res2025['margin'] ?? 0); ?></td>
                                    <td><?php echo $res2025['turnout_percent'] ?? ($sum2020['turnout_percent'] ?? '-'); ?>%</td>
                                </tr>
                                <?php endif; ?>

                                <?php if (!empty($res2020['winner_name']) || !empty($res2020['winner'])): ?>
                                <tr>
                                    <td><span class="badge bg-primary">2020 Assembly</span></td>
                                    <td class="fw-bold text-navy"><?php echo htmlspecialchars($res2020['winner_name'] ?? $res2020['winner']); ?></td>
                                    <td><span class="badge-party <?php echo strtolower($res2020['winner_party'] ?? ''); ?>"><?php echo htmlspecialchars($res2020['winner_party'] ?? '-'); ?></span></td>
                                    <td><?php echo number_format($res2020['winner_votes'] ?? 0); ?><?php if (!empty($res2020['winner_vote_share'])): ?> (<?php echo $res2020['winner_vote_share']; ?>%)<?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($res2020['runner_up_name'] ?? $res2020['runner_up'] ?? '-'); ?> (<?php echo htmlspecialchars($res2020['runner_up_party'] ?? '-'); ?>)</td>
                                    <td class="text-success fw-bold"><?php echo number_format($res2020['margin'] ?? 0); ?></td>
                                    <td><?php echo $sum2020['turnout_percent'] ?? ($res2020['turnout_percent'] ?? '-'); ?>%</td>
                                </tr>
                                <?php endif; ?>

                                <?php if (!empty($res2015['winner_name']) || !empty($res2015['winner'])): ?>
                                <tr>
                                    <td><span class="badge bg-secondary">2015 Assembly</span></td>
                                    <td>
                                        <div class="fw-bold text-navy"><?php echo htmlspecialchars($res2015['winner_name'] ?? $res2015['winner']); ?></div>
                                        <?php if (!empty($mla2015['mobile'])): ?>
                                            <span class="badge bg-light text-secondary border py-0 px-2 fw-semibold mt-1 d-inline-flex align-items-center gap-1 extra-small" title="Contact Protected">
                                                <i class="bi bi-telephone text-success"></i> <?php echo htmlspecialchars(maskMobileNumber($mla2015['mobile'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="badge-party <?php echo strtolower($res2015['winner_party'] ?? ($ac['election_2015']['summary']['winner_party'] ?? 'other')); ?>"><?php echo htmlspecialchars($res2015['winner_party'] ?? ($ac['election_2015']['summary']['winner_party'] ?? '-')); ?></span></td>
                                    <td><?php echo number_format($res2015['winner_votes'] ?? 0); ?><?php if (!empty($res2015['winner_vote_share'])): ?> (<?php echo $res2015['winner_vote_share']; ?>%)<?php endif; ?></td>
                                    <td><?php echo htmlspecialchars($res2015['runner_up_name'] ?? $res2015['runner_up'] ?? '-'); ?> (<?php echo htmlspecialchars($res2015['runner_up_party'] ?? '-'); ?>)</td>
                                    <td class="text-success fw-bold"><?php echo number_format($res2015['margin'] ?? 0); ?></td>
                                    <td><?php echo $sum2015['turnout_percent'] ?? ($res2015['turnout_percent'] ?? ($ac['election_2015']['summary']['turnout'] ?? '-')); ?>%</td>
                                </tr>
                                <?php endif; ?>
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
                                        <span class="badge-party <?php echo strtolower($asp['party']); ?>"><?php echo htmlspecialchars($asp['party']); ?></span>
                                    </div>
                                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($asp['status']); ?></p>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary small">Alliance: <?php echo htmlspecialchars($asp['alliance']); ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-muted small">
                                Verified candidate aspirant filings and public declaration profiles will be updated continuously for this constituency.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Demographic & Elector Snapshot -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📊 Voter Demographics & Electoral Profile
                    </h2>
                    <div class="row g-2 g-md-3 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Total Electors</div>
                                <div class="h5 fw-bold text-navy mb-0"><?php echo number_format($totalElectors); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Male Electors</div>
                                <div class="h5 fw-bold text-primary mb-0"><?php echo number_format($sum2020['electors_male'] ?? ($ac['male_electors'] ?? 0)); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Female Electors</div>
                                <div class="h5 fw-bold text-danger mb-0"><?php echo number_format($sum2020['electors_female'] ?? ($ac['female_electors'] ?? 0)); ?></div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="bg-light p-3 rounded-3 text-center">
                                <div class="small text-muted">Turnout %</div>
                                <div class="h5 fw-bold text-success mb-0"><?php echo $sum2020['turnout_percent'] ?? ($res2025['turnout_percent'] ?? 0); ?>%</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded-3 small text-muted d-flex flex-wrap justify-content-between gap-2">
                        <div><strong>Polling Stations:</strong> <?php echo number_format($sum2020['polling_stations'] ?? ($ac['polling_stations'] ?? 0)); ?></div>
                        <div><strong>Valid Votes (2020):</strong> <?php echo number_format($sum2020['valid_votes'] ?? 0); ?></div>
                        <div><strong>NOTA Votes (2020):</strong> <?php echo number_format($sum2020['nota_votes'] ?? 0); ?></div>
                    </div>
                </div>

                <!-- Key Issues -->
                <div class="card border-0 shadow-sm p-3 p-md-4 mb-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        🎯 <?php echo htmlspecialchars($ac['name']); ?> Key Issues & Political Demands
                    </h2>
                    <ul class="mb-0 text-muted small lh-lg">
                        <?php if (!empty($ac['key_issues'])): ?>
                            <?php foreach ((array)$ac['key_issues'] as $issue): ?>
                                <li><?php echo htmlspecialchars($issue); ?></li>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <li>Local road, bridge, and rural transportation infrastructure upgrades.</li>
                            <li>Irrigation facility maintenance, flood protection, and agricultural power supply.</li>
                            <li>Primary health center upgrades and government hospital staffing.</li>
                            <li>Quality education, higher secondary schools, and youth employment generation.</li>
                        <?php endif; ?>
                    </ul>
                </div>

                <!-- Political History & Context -->
                <div class="card border-0 shadow-sm p-3 p-md-4 rounded-3 bg-white">
                    <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                        📜 Political History & Trend Analysis
                    </h2>
                    <p class="small text-muted mb-0 lh-lg">
                        <?php echo htmlspecialchars($ac['party_history'] ?? "{$ac['name']} (AC {$ac['ac_no']}) is a vital assembly constituency in {$ac['district']} district of Bihar."); ?>
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
                    <a href="<?php echo getAdvertiseUrl(['sponsor_ac' => $ac['ac_no']]); ?>" class="btn btn-warning btn-sm w-100 fw-bold text-dark">
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
