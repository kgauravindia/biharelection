<?php
require_once __DIR__ . '/config.php';

$activeNav = 'mp';
$loksabhaMps = DataProvider::getLokSabhaMps();
$rajyaSabhaMps = DataProvider::getRajyaSabhaMps();

$houseFilter = strtolower($_GET['house'] ?? ($_GET['tab'] ?? 'all'));
if (!in_array($houseFilter, ['all', 'loksabha', 'lok-sabha', 'rajyasabha', 'rajya-sabha'])) {
    $houseFilter = 'all';
}

$pageTitle = "Bihar MPs Directory: 40 Lok Sabha & 15 Rajya Sabha Members of Parliament";
$pageDescription = "Official directory of Bihar Members of Parliament (MPs) across 40 Lok Sabha parliamentary constituencies and 15 Rajya Sabha seats with party affiliation, margin, and contacts.";
$pageCanonical = SITE_URL . "/mp";

require_once __DIR__ . '/header.php';
?>

<!-- Hero Banner -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                🏛️ Parliament of India (Bihar Roster)
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                40 Lok Sabha Seats
            </span>
            <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                15 Rajya Sabha Seats
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/representatives" class="text-white-50 text-decoration-none">Representatives</a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Members of Parliament (MPs)</li>
            </ol>
        </nav>

        <h1 class="display-6 fw-extrabold text-white mb-2">
            Bihar Members of Parliament (MPs) Directory <br>
            <span style="color: var(--accent-saffron);">40 Lok Sabha &amp; 15 Rajya Sabha Parliamentarians</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
            Complete directory of Bihar's parliamentary leadership in the 18th Lok Sabha and Council of States (Rajya Sabha). Explore parliamentary constituencies, victory margins, party strengths, and contact details.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                <i class="bi bi-person-badge me-1"></i> 243 MLAs Directory
            </a>
            <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                <i class="bi bi-people me-1"></i> 75 MLCs Directory
            </a>
            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Updates
            </a>
        </div>
    </div>
</section>

<!-- Main Container -->
<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Highlight Metrics -->
    <div class="row g-3 g-lg-4 mb-4">
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-warning bg-white">
                <span class="text-muted small fw-bold text-uppercase">Lok Sabha (Lower House)</span>
                <div class="h3 fw-bold mb-1 text-navy font-heading">40 MPs</div>
                <div class="small text-muted">Directly elected across all 40 Bihar Parliamentary Constituencies (18th Lok Sabha)</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-primary bg-white">
                <span class="text-muted small fw-bold text-uppercase">Rajya Sabha (Upper House)</span>
                <div class="h3 fw-bold mb-1 text-navy font-heading">15 MPs</div>
                <div class="small text-muted">Council of States members representing Bihar via Legislative Assembly elections</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-success bg-white">
                <span class="text-muted small fw-bold text-uppercase">Total Bihar Strength</span>
                <div class="h3 fw-bold mb-1 text-success font-heading">55 Parliamentarians</div>
                <div class="small text-muted">Combined parliamentary delegation representing 13 Crore+ citizens of Bihar</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-4 bg-white border-top border-4 border-warning">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-12 col-lg-5">
                <div class="btn-group w-100" role="group" aria-label="House Filter">
                    <button type="button" class="btn btn-outline-warning text-dark fw-bold house-btn <?php echo in_array($houseFilter, ['all', '']) ? 'active' : ''; ?>" data-house="all" onclick="setHouseFilter('all', this)">
                        All MPs (55)
                    </button>
                    <button type="button" class="btn btn-outline-warning text-dark fw-bold house-btn <?php echo in_array($houseFilter, ['loksabha', 'lok-sabha']) ? 'active' : ''; ?>" data-house="loksabha" onclick="setHouseFilter('loksabha', this)">
                        🏛️ Lok Sabha (40)
                    </button>
                    <button type="button" class="btn btn-outline-warning text-dark fw-bold house-btn <?php echo in_array($houseFilter, ['rajyasabha', 'rajya-sabha']) ? 'active' : ''; ?>" data-house="rajyasabha" onclick="setHouseFilter('rajyasabha', this)">
                        👑 Rajya Sabha (15)
                    </button>
                </div>
            </div>
            <div class="col-12 col-lg-7">
                <div class="row g-2">
                    <div class="col-12 col-sm-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="mpSearchInput" class="form-control border-start-0 rounded-end-pill py-2" placeholder="Search by MP name, constituency or party..." onkeyup="filterMpRoster()">
                        </div>
                    </div>
                    <div class="col-12 col-sm-5">
                        <select id="mpPartyFilter" class="form-select rounded-pill py-2" onchange="filterMpRoster()">
                            <option value="">All Parties</option>
                            <option value="BJP">BJP</option>
                            <option value="JD(U)">JD(U)</option>
                            <option value="RJD">RJD</option>
                            <option value="INC">INC (Congress)</option>
                            <option value="LJPRV">LJPRV (LJP-RV)</option>
                            <option value="CPI(ML)">CPI(ML) Liberation</option>
                            <option value="HAM">HAM(S)</option>
                            <option value="RLM">RLM</option>
                            <option value="IND">Independent (IND)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Lok Sabha 40 MPs Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5 house-section" id="loksabhaSection">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-navy mb-0">
                    <i class="bi bi-bank text-warning me-2"></i> 18th Lok Sabha Members of Parliament (40 Seats)
                </h5>
                <small class="text-muted">Directly elected parliamentary representatives across Bihar's 40 constituencies</small>
            </div>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm" id="loksabhaCountBadge">
                40 Lok Sabha MPs
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="loksabhaTable">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">PC No.</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Parliamentary Constituency</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Elected Member of Parliament (MP)</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Party</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Victory Margin</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Contact &amp; Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($loksabhaMps)): ?>
                        <?php foreach ($loksabhaMps as $mp): 
                            $pcNo = (string)($mp['pc_no'] ?? '');
                            $pcName = (string)($mp['constituency'] ?? $mp['pc_name'] ?? '');
                            $mpName = (string)($mp['mp_name'] ?? $mp['name'] ?? '');
                            $party = (string)($mp['party'] ?? '');
                            $margin = (int)($mp['margin'] ?? 0);
                            $phone = (string)($mp['mobile'] ?? $mp['phone'] ?? '');
                            $district = (string)($mp['district'] ?? '');
                        ?>
                            <tr class="loksabha-row" 
                                data-name="<?php echo htmlspecialchars(strtolower($mpName)); ?>"
                                data-constituency="<?php echo htmlspecialchars(strtolower($pcName . ' ' . $district)); ?>"
                                data-party="<?php echo htmlspecialchars(strtoupper($party)); ?>"
                                data-pc="<?php echo htmlspecialchars($pcNo); ?>">
                                
                                <!-- PC No -->
                                <td class="text-center fw-bold" style="min-width: 80px;">
                                    <span class="badge bg-warning bg-opacity-25 text-dark px-2.5 py-1.5 rounded-pill font-monospace">
                                        PC <?php echo htmlspecialchars($pcNo); ?>
                                    </span>
                                </td>

                                <!-- Constituency -->
                                <td style="min-width: 170px;">
                                    <div class="fw-bold text-navy fs-6">
                                        <?php echo htmlspecialchars($pcName); ?>
                                    </div>
                                    <?php if (!empty($district)): ?>
                                        <div class="text-muted small">HQ: <?php echo htmlspecialchars($district); ?></div>
                                    <?php endif; ?>
                                </td>

                                <!-- MP Name -->
                                <td style="min-width: 180px;">
                                    <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($mpName); ?>
                                    </div>
                                    <div class="text-muted small">Member of Parliament (18th Lok Sabha)</div>
                                </td>

                                <!-- Party -->
                                <td class="text-center" style="min-width: 110px;">
                                    <span class="badge-party <?php echo strtolower(preg_replace('/[^a-z0-9]/i', '', $party)); ?>">
                                        <?php echo htmlspecialchars($party); ?>
                                    </span>
                                </td>

                                <!-- Margin -->
                                <td class="text-center fw-bold text-success" style="min-width: 110px;">
                                    <?php echo $margin > 0 ? '+' . number_format($margin) : 'Elected'; ?>
                                </td>

                                <!-- Contact & Details -->
                                <td style="min-width: 160px;">
                                    <?php if (!empty($phone)): ?>
                                        <?php echo renderMaskedPhoneButton($phone, $mpName); ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Parliamentary Office</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Rajya Sabha 15 MPs Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5 house-section" id="rajyasabhaSection">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-navy mb-0">
                    <i class="bi bi-award text-primary me-2"></i> Rajya Sabha Parliamentarians from Bihar (15 Seats)
                </h5>
                <small class="text-muted">Members of the Council of States representing Bihar in the Upper House</small>
            </div>
            <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm" id="rajyasabhaCountBadge">
                15 Rajya Sabha MPs
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="rajyasabhaTable">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">S.No.</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Rajya Sabha MP Name</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Party</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Tenure &amp; Term Dates</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Contact &amp; Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($rajyaSabhaMps)): ?>
                        <?php foreach ($rajyaSabhaMps as $rmp): 
                            $sNo = (string)($rmp['sno'] ?? $rmp['id'] ?? '');
                            $rName = (string)($rmp['name'] ?? $rmp['mp_name'] ?? '');
                            $rParty = (string)($rmp['party'] ?? '');
                            $rTenure = (string)($rmp['tenure'] ?? $rmp['term'] ?? '');
                            $rPhone = (string)($rmp['mobile'] ?? $rmp['phone'] ?? '');
                        ?>
                            <tr class="rajyasabha-row"
                                data-name="<?php echo htmlspecialchars(strtolower($rName)); ?>"
                                data-party="<?php echo htmlspecialchars(strtoupper($rParty)); ?>"
                                data-tenure="<?php echo htmlspecialchars(strtolower($rTenure)); ?>">
                                
                                <td class="text-center fw-bold" style="min-width: 70px;">
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-2.5 py-1 rounded-pill">
                                        #<?php echo htmlspecialchars($sNo); ?>
                                    </span>
                                </td>

                                <td style="min-width: 200px;">
                                    <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($rName); ?>
                                    </div>
                                    <div class="text-muted small">Rajya Sabha Representative (Bihar)</div>
                                </td>

                                <td class="text-center" style="min-width: 110px;">
                                    <span class="badge-party <?php echo strtolower(preg_replace('/[^a-z0-9]/i', '', $rParty)); ?>">
                                        <?php echo htmlspecialchars($rParty); ?>
                                    </span>
                                </td>

                                <td style="min-width: 180px;">
                                    <div class="small fw-semibold text-dark"><?php echo htmlspecialchars($rTenure ?: 'Active Term'); ?></div>
                                    <span class="badge bg-light text-muted border small px-2 py-0.5">Council of States</span>
                                </td>

                                <td style="min-width: 160px;">
                                    <?php if (!empty($rPhone)): ?>
                                        <?php echo renderMaskedPhoneButton($rPhone, $rName); ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Official Rajya Sabha Member</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Official Data Sources Attribution Banner -->
    <section class="mt-5 pt-4 border-top">
        <div class="p-4 rounded-4 bg-light border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-15 text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark font-heading mb-1">Standardized Parliamentary Data Sources</h6>
                    <p class="text-muted small mb-0">Parliamentary constituencies, winning margins, and elected representatives reference the Election Commission of India (<a href="https://eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">eci.gov.in</a>), Lok Sabha Secretariat (<a href="https://sansad.in/ls" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">sansad.in/ls</a>), and Rajya Sabha Secretariat.</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-outline-warning text-dark rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-person-badge me-1"></i>243 MLAs Directory
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<script>
let currentHouse = '<?php echo $houseFilter; ?>';

function setHouseFilter(house, btn) {
    currentHouse = house;
    document.querySelectorAll('.house-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    
    const lsSec = document.getElementById('loksabhaSection');
    const rsSec = document.getElementById('rajyasabhaSection');

    if (house === 'loksabha') {
        if (lsSec) lsSec.classList.remove('d-none');
        if (rsSec) rsSec.classList.add('d-none');
    } else if (house === 'rajyasabha') {
        if (lsSec) lsSec.classList.add('d-none');
        if (rsSec) rsSec.classList.remove('d-none');
    } else {
        if (lsSec) lsSec.classList.remove('d-none');
        if (rsSec) rsSec.classList.remove('d-none');
    }
    filterMpRoster();
}

function filterMpRoster() {
    const query = (document.getElementById('mpSearchInput')?.value || '').toLowerCase().trim();
    const party = (document.getElementById('mpPartyFilter')?.value || '').toUpperCase().trim();

    // Filter Lok Sabha
    let lsVisible = 0;
    document.querySelectorAll('#loksabhaTable .loksabha-row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const pc = row.getAttribute('data-constituency') || '';
        const pParty = row.getAttribute('data-party') || '';
        const pcNo = row.getAttribute('data-pc') || '';

        const matchQuery = !query || name.includes(query) || pc.includes(query) || pParty.toLowerCase().includes(query) || pcNo.includes(query);
        const matchParty = !party || pParty.includes(party);

        if (matchQuery && matchParty) {
            row.style.display = '';
            lsVisible++;
        } else {
            row.style.display = 'none';
        }
    });
    const lsBadge = document.getElementById('loksabhaCountBadge');
    if (lsBadge) lsBadge.innerText = lsVisible + ' Lok Sabha MPs';

    // Filter Rajya Sabha
    let rsVisible = 0;
    document.querySelectorAll('#rajyasabhaTable .rajyasabha-row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const pParty = row.getAttribute('data-party') || '';
        const tenure = row.getAttribute('data-tenure') || '';

        const matchQuery = !query || name.includes(query) || pParty.toLowerCase().includes(query) || tenure.includes(query);
        const matchParty = !party || pParty.includes(party);

        if (matchQuery && matchParty) {
            row.style.display = '';
            rsVisible++;
        } else {
            row.style.display = 'none';
        }
    });
    const rsBadge = document.getElementById('rajyasabhaCountBadge');
    if (rsBadge) rsBadge.innerText = rsVisible + ' Rajya Sabha MPs';
}

document.addEventListener('DOMContentLoaded', function() {
    setHouseFilter(currentHouse, document.querySelector(`.house-btn[data-house="${currentHouse}"]`));
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
