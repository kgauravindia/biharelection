<?php
require_once __DIR__ . '/config.php';

$activeNav = 'mlc';
$mlcs = DataProvider::getMlcs();

// Pre-calculate quota counts
$quotaCounts = [
    'All' => count($mlcs),
    'Local Authorities' => 0,
    'Elected by MLAs' => 0,
    'Governor Nominated' => 0,
    'Graduates' => 0,
    'Teachers' => 0
];

foreach ($mlcs as $m) {
    $const = $m['constituency'] ?? $m['quota'] ?? '';
    if (stripos($const, 'Local') !== false || stripos($const, 'प्राधिकार') !== false) {
        $quotaCounts['Local Authorities']++;
    } elseif (stripos($const, 'Nominated') !== false || stripos($const, 'मनोनीत') !== false) {
        $quotaCounts['Governor Nominated']++;
    } elseif (stripos($const, 'Graduates') !== false || stripos($const, 'स्नातक') !== false) {
        $quotaCounts['Graduates']++;
    } elseif (stripos($const, 'Teachers') !== false || stripos($const, 'शिक्षक') !== false) {
        $quotaCounts['Teachers']++;
    } else {
        $quotaCounts['Elected by MLAs']++;
    }
}

$pageTitle = "Bihar 75 Vidhan Parishad MLCs Directory: Legislative Council Members Roster";
$pageDescription = "Official directory of 75 Bihar Legislative Council (Vidhan Parishad) Members (MLCs) across Local Authorities, Graduates, Teachers, Assembly, and Nominated quotas.";
$pageCanonical = SITE_URL . "/mlc";

require_once __DIR__ . '/header.php';
?>

<!-- Hero Banner -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-info text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                📜 Bihar Legislative Council (विधान परिषद)
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                75 Total MLCs
            </span>
            <span class="badge bg-warning bg-opacity-25 text-warning fw-bold px-3 py-2 rounded-pill">
                Permanent Upper Chamber
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/representatives" class="text-white-50 text-decoration-none">Representatives</a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Vidhan Parishad (MLCs)</li>
            </ol>
        </nav>

        <h1 class="display-6 fw-extrabold text-white mb-2">
            Bihar Vidhan Parishad (MLCs) Directory <br>
            <span style="color: var(--accent-saffron);">75 Members of Bihar Legislative Council</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
            Official directory of all 75 Members of the Bihar Legislative Council (विधान परिषद) across Local Authorities, Graduates, Teachers, Assembly-elected, and Governor-nominated quotas.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                <i class="bi bi-person-badge me-1"></i> 243 MLAs Directory
            </a>
            <a href="<?php echo SITE_URL; ?>/mp" class="btn btn-primary fw-bold px-3 py-2 shadow-sm">
                <i class="bi bi-bank me-1"></i> 40 Lok Sabha MPs
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
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-info bg-white">
                <span class="text-muted small fw-bold text-uppercase">Local Authorities</span>
                <div class="h3 fw-bold mb-1 text-navy font-heading">24 MLCs</div>
                <div class="small text-muted">Elected by Panchayat &amp; Urban Local Body representatives</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-warning bg-white">
                <span class="text-muted small fw-bold text-uppercase">Assembly Elected</span>
                <div class="h3 fw-bold mb-1 text-navy font-heading">27 MLCs</div>
                <div class="small text-muted">Elected by MLAs in Bihar Legislative Assembly</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-primary bg-white">
                <span class="text-muted small fw-bold text-uppercase">Governor Nominated</span>
                <div class="h3 fw-bold mb-1 text-navy font-heading">12 MLCs</div>
                <div class="small text-muted">Nominated for distinguished service in arts, science &amp; social service</div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 h-100 border-start border-4 border-success bg-white">
                <span class="text-muted small fw-bold text-uppercase">Graduates &amp; Teachers</span>
                <div class="h3 fw-bold mb-1 text-success font-heading">12 MLCs</div>
                <div class="small text-muted">6 Graduates' Constituencies &bull; 6 Teachers' Constituencies</div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Controls -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-4 bg-white border-top border-4 border-info">
        <div class="row g-3 align-items-center justify-content-between">
            <div class="col-12 col-lg-5">
                <h5 class="fw-bold text-navy mb-1">
                    <i class="bi bi-people-fill text-info me-2"></i> Bihar Legislative Council Roster
                </h5>
                <p class="text-muted small mb-0">Search and filter across 75 sitting Vidhan Parishad MLCs</p>
            </div>
            <div class="col-12 col-lg-7">
                <div class="row g-2">
                    <div class="col-12 col-sm-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="mlcSearchInput" class="form-control border-start-0 rounded-end-pill py-2" placeholder="Search by MLC name, constituency or party..." onkeyup="filterMlcRoster()">
                        </div>
                    </div>
                    <div class="col-12 col-sm-5">
                        <select id="mlcPartyFilter" class="form-select rounded-pill py-2" onchange="filterMlcRoster()">
                            <option value="">All Parties</option>
                            <option value="BJP">BJP</option>
                            <option value="JD(U)">JD(U)</option>
                            <option value="RJD">RJD</option>
                            <option value="INC">INC (Congress)</option>
                            <option value="CPI(ML)">CPI(ML) Liberation</option>
                            <option value="HAM">HAM(S)</option>
                            <option value="IND">Independent (IND)</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quota Filter Pills -->
        <div class="d-flex flex-wrap gap-1.5 mt-3 pt-3 border-top align-items-center">
            <span class="small fw-bold text-navy me-2"><i class="bi bi-funnel-fill text-info"></i> Quota:</span>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 quota-btn active" data-quota="All" onclick="filterQuota('All', this)">All (75)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 quota-btn" data-quota="Local" onclick="filterQuota('Local', this)">Local Authorities (24)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 quota-btn" data-quota="Assembly" onclick="filterQuota('Assembly', this)">Elected by MLAs (27)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 quota-btn" data-quota="Nominated" onclick="filterQuota('Nominated', this)">Governor Nominated (12)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 quota-btn" data-quota="Graduates" onclick="filterQuota('Graduates', this)">Graduates (6)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 quota-btn" data-quota="Teachers" onclick="filterQuota('Teachers', this)">Teachers (6)</button>
        </div>
    </div>

    <!-- MLCs Roster Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-navy mb-0">
                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i> 75 Legislative Council Members (MLCs)
                </h5>
                <small class="text-muted">Complete roster with constituency quota, party affiliation, and tenure</small>
            </div>
            <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm" id="mlcCountBadge">
                <?php echo count($mlcs); ?> MLCs
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="mlcTable">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">#</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Member Name (MLC)</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Constituency / Quota</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Party</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Term &amp; Expiry</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Contact &amp; Details</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mlcs)): ?>
                        <?php $idx = 1; foreach ($mlcs as $mlc): 
                            $mName = (string)($mlc['name'] ?? $mlc['member_name'] ?? '');
                            $const = (string)($mlc['constituency'] ?? $mlc['quota'] ?? '');
                            $party = (string)($mlc['party'] ?? '');
                            $term = (string)($mlc['term'] ?? $mlc['tenure'] ?? '');
                            $phone = (string)($mlc['mobile'] ?? $mlc['phone'] ?? '');

                            // Categorize quota tag for search
                            $quotaType = 'Assembly';
                            if (stripos($const, 'Local') !== false || stripos($const, 'प्राधिकार') !== false) {
                                $quotaType = 'Local';
                            } elseif (stripos($const, 'Nominated') !== false || stripos($const, 'मनोनीत') !== false) {
                                $quotaType = 'Nominated';
                            } elseif (stripos($const, 'Graduates') !== false || stripos($const, 'स्नातक') !== false) {
                                $quotaType = 'Graduates';
                            } elseif (stripos($const, 'Teachers') !== false || stripos($const, 'शिक्षक') !== false) {
                                $quotaType = 'Teachers';
                            }
                        ?>
                            <tr class="mlc-row"
                                data-name="<?php echo htmlspecialchars(strtolower($mName)); ?>"
                                data-constituency="<?php echo htmlspecialchars(strtolower($const)); ?>"
                                data-party="<?php echo htmlspecialchars(strtoupper($party)); ?>"
                                data-quota="<?php echo htmlspecialchars($quotaType); ?>">
                                
                                <td class="text-center fw-bold" style="min-width: 60px;">
                                    <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">
                                        <?php echo $idx++; ?>
                                    </span>
                                </td>

                                <td style="min-width: 200px;">
                                    <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                        <?php echo htmlspecialchars($mName); ?>
                                    </div>
                                    <div class="text-muted small">Member of Legislative Council</div>
                                </td>

                                <td style="min-width: 190px;">
                                    <div class="fw-semibold text-dark small mb-0.5"><?php echo htmlspecialchars($const); ?></div>
                                    <span class="badge bg-info bg-opacity-10 text-dark border small px-2 py-0.5">
                                        <?php echo htmlspecialchars($quotaType); ?> Quota
                                    </span>
                                </td>

                                <td class="text-center" style="min-width: 110px;">
                                    <span class="badge-party <?php echo strtolower(preg_replace('/[^a-z0-9]/i', '', $party)); ?>">
                                        <?php echo htmlspecialchars($party); ?>
                                    </span>
                                </td>

                                <td style="min-width: 160px;">
                                    <div class="small fw-semibold text-dark"><?php echo htmlspecialchars($term ?: 'Active 6-Year Term'); ?></div>
                                    <small class="text-muted">Vidhan Parishad</small>
                                </td>

                                <td style="min-width: 160px;">
                                    <?php if (!empty($phone)): ?>
                                        <?php echo renderMaskedPhoneButton($phone, $mName); ?>
                                    <?php else: ?>
                                        <span class="text-muted small">Council Secretariat</span>
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
                <div class="rounded-circle bg-info bg-opacity-15 text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark font-heading mb-1">Standardized Legislative Data Sources</h6>
                    <p class="text-muted small mb-0">Bihar Legislative Council (Vidhan Parishad) roster, quotas, and tenure data reference the Bihar Vidhan Parishad Secretariat (<a href="https://biharvidhanparishad.gov.in" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">biharvidhanparishad.gov.in</a>) and the Election Commission of India.</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-outline-info text-dark rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-person-badge me-1"></i>243 MLAs Directory
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<script>
let currentQuota = 'All';

function filterQuota(quota, btn) {
    currentQuota = quota;
    document.querySelectorAll('.quota-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterMlcRoster();
}

function filterMlcRoster() {
    const query = (document.getElementById('mlcSearchInput')?.value || '').toLowerCase().trim();
    const party = (document.getElementById('mlcPartyFilter')?.value || '').toUpperCase().trim();
    let visible = 0;

    document.querySelectorAll('#mlcTable .mlc-row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const constName = row.getAttribute('data-constituency') || '';
        const pParty = row.getAttribute('data-party') || '';
        const quota = row.getAttribute('data-quota') || '';

        const matchQuery = !query || name.includes(query) || constName.includes(query) || pParty.toLowerCase().includes(query);
        const matchParty = !party || pParty.includes(party);
        const matchQuota = currentQuota === 'All' || quota.toLowerCase().includes(currentQuota.toLowerCase());

        if (matchQuery && matchParty && matchQuota) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('mlcCountBadge');
    if (badge) badge.innerText = visible + ' MLCs';
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
