<?php
require_once __DIR__ . '/config.php';

$activeNav = 'mla';
$constituencies = DataProvider::getConstituencies();
$districts = DataProvider::getDistricts();
$mlas2015 = DataProvider::getMlas2015();

// Pre-calculate reservation counts & party breakdown
$scCount = 0;
$stCount = 0;
$genCount = 0;
$partyCounts = [];

foreach ($constituencies as $ac) {
    $res = strtoupper($ac['reservation'] ?? 'GEN');
    if ($res === 'SC') $scCount++;
    elseif ($res === 'ST') $stCount++;
    else $genCount++;

    $party = trim($ac['current_party'] ?? 'Other');
    if ($party) {
        $partyCounts[$party] = ($partyCounts[$party] ?? 0) + 1;
    }
}
arsort($partyCounts);

// Check if historical tab requested
$viewTab = strtolower(trim($_GET['tab'] ?? 'current'));

$pageTitle = "Bihar 243 MLAs & Assembly Constituencies Directory: Vidhan Sabha Roster";
$pageDescription = "Official directory of all 243 Bihar Assembly Constituencies (Vidhan Sabha) and elected MLAs: 203 General seats, 38 SC Reserved, and 2 ST Reserved (Manihari & Katoria) with historical 2015–2020 archive.";
$pageCanonical = SITE_URL . "/mla";

require_once __DIR__ . '/header.php';
?>

<style>
/* Metric Category Cards */
.mla-stat-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #edf2f7;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
    transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    user-select: none;
}
.mla-stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(11, 26, 48, 0.08);
    border-color: #cbd5e1;
}
.mla-stat-card.active {
    border-color: var(--accent-saffron, #ff9933);
    box-shadow: 0 8px 20px rgba(255, 153, 51, 0.25);
    background: #fffcf8;
}
.mla-stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}
.mla-stat-total::before { background: linear-gradient(90deg, #0b1a30, var(--accent-saffron, #ff9933)); }
.mla-stat-gen::before { background: linear-gradient(90deg, #0d6efd, #0dcaf0); }
.mla-stat-sc::before { background: linear-gradient(90deg, #dc3545, #fd7e14); }
.mla-stat-st::before { background: linear-gradient(90deg, #198754, #20c997); }

/* Quick Filter Pills */
.res-pill-btn {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 50px;
    padding: 0.4rem 0.9rem;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #495057;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.res-pill-btn:hover {
    background-color: #f8f9fa;
    border-color: var(--accent-saffron, #ff9933);
    color: var(--navy-dark, #0b1a30);
    transform: translateY(-1px);
}
.res-pill-btn.active {
    background: linear-gradient(135deg, #0b1a30 0%, #17345f 100%);
    border-color: #0b1a30;
    color: #fff;
    box-shadow: 0 4px 10px rgba(11, 26, 48, 0.2);
}
.res-pill-btn.active .res-badge-count {
    background-color: var(--accent-saffron, #ff9933);
    color: #000;
}
.res-badge-count {
    font-size: 0.72rem;
    padding: 0.15rem 0.45rem;
    border-radius: 50px;
    background-color: #e9ecef;
    color: #495057;
    font-weight: 700;
}

/* Party Pills */
.party-pill-btn {
    font-size: 0.8rem;
    font-weight: 600;
    border-radius: 50px;
    padding: 0.25rem 0.75rem;
    border: 1px solid #e2e8f0;
    background-color: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.party-pill-btn:hover, .party-pill-btn.active {
    background-color: #0b1a30;
    color: #ffffff;
    border-color: #0b1a30;
}
.party-pill-btn.active .party-pill-count {
    background-color: var(--accent-saffron, #ff9933);
    color: #000;
}
.party-pill-count {
    font-size: 0.7rem;
    padding: 0.1rem 0.4rem;
    border-radius: 50px;
    background-color: #f1f5f9;
    color: #64748b;
    font-weight: 700;
}
</style>

<!-- Hero Banner -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                🗳️ Bihar Legislative Assembly (विधान सभा)
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                243 Assembly Constituencies
            </span>
            <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                38 Districts Mapped
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">243 MLAs &amp; Vidhan Sabha</li>
            </ol>
        </nav>

        <h1 class="display-6 fw-extrabold text-white mb-2 font-heading">
            Bihar 243 MLAs &amp; Assembly Directory <br>
            <span style="color: var(--accent-saffron);">Vidhan Sabha Constituencies &amp; Elected Members</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
            Explore complete profiling for all 243 Bihar Assembly Constituencies. Access sitting MLAs, party affiliations, reservation categories, elector demographics, and historical 2015–2020 election records.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/mp" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                <i class="bi bi-bank me-1"></i> 40 Lok Sabha MPs
            </a>
            <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                <i class="bi bi-people me-1"></i> 75 MLCs Directory
            </a>
            <a href="<?php echo SITE_URL; ?>/panchayat" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                <i class="bi bi-building-check me-1"></i> Panchayat Directory
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

    <!-- ================================================================= -->
    <!-- 4-METRIC INTERACTIVE CATEGORY CARDS                              -->
    <!-- ================================================================= -->
    <div class="row g-3 g-lg-4 mb-4">
        
        <!-- Total Assembly Seats -->
        <div class="col-6 col-md-3">
            <div class="mla-stat-card mla-stat-total p-3.5 p-lg-4 h-100 active" id="statCardAll" onclick="selectCategoryFilter('', this)" title="Click to view all 243 MLAs">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="text-muted small fw-bold text-uppercase">Total Assembly Seats</span>
                    <span class="fs-4">🗳️</span>
                </div>
                <div class="h3 fw-bold mb-1 text-navy font-heading">243 MLAs</div>
                <div class="small text-muted">Bihar Legislative Assembly</div>
            </div>
        </div>

        <!-- General Seats -->
        <div class="col-6 col-md-3">
            <div class="mla-stat-card mla-stat-gen p-3.5 p-lg-4 h-100" id="statCardGen" onclick="selectCategoryFilter('GEN', this)" title="Click to filter 203 General seats">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="text-muted small fw-bold text-uppercase">General Seats</span>
                    <span class="fs-4">🌐</span>
                </div>
                <div class="h3 fw-bold mb-1 text-primary font-heading">203 Seats</div>
                <div class="small text-muted">Open / Unreserved Categories</div>
            </div>
        </div>

        <!-- SC Reserved -->
        <div class="col-6 col-md-3">
            <div class="mla-stat-card mla-stat-sc p-3.5 p-lg-4 h-100" id="statCardSc" onclick="selectCategoryFilter('SC', this)" title="Click to filter 38 SC reserved seats">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="text-muted small fw-bold text-uppercase">SC Reserved</span>
                    <span class="fs-4">🏛️</span>
                </div>
                <div class="h3 fw-bold mb-1 text-danger font-heading">38 Seats</div>
                <div class="small text-muted">Scheduled Caste Constituencies</div>
            </div>
        </div>

        <!-- ST Reserved -->
        <div class="col-6 col-md-3">
            <div class="mla-stat-card mla-stat-st p-3.5 p-lg-4 h-100" id="statCardSt" onclick="selectCategoryFilter('ST', this)" title="Click to filter 2 ST reserved seats (Manihari & Katoria)">
                <div class="d-flex justify-content-between align-items-start mb-1">
                    <span class="text-muted small fw-bold text-uppercase">ST Reserved</span>
                    <span class="fs-4">🌿</span>
                </div>
                <div class="h3 fw-bold mb-1 text-success font-heading">2 Seats</div>
                <div class="small text-muted">Manihari &amp; Katoria Constituencies</div>
            </div>
        </div>

    </div>

    <!-- Assembly Assembly Switcher Nav: Current 243 MLAs vs Historical 2015-2020 Archive -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <ul class="nav nav-pills p-1.5 bg-white rounded-pill shadow-sm border" id="assemblyViewTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $viewTab !== 'archive' ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-bold" id="current-mlas-tab" data-bs-toggle="pill" data-bs-target="#current-mlas-pane" type="button" role="tab">
                    <i class="bi bi-person-check-fill me-1 text-warning"></i> Current 243 MLAs Roster (17th Assembly)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link <?php echo $viewTab === 'archive' ? 'active' : ''; ?> rounded-pill px-4 py-2 fw-bold" id="archive-mlas-tab" data-bs-toggle="pill" data-bs-target="#archive-mlas-pane" type="button" role="tab">
                    <i class="bi bi-archive-fill me-1 text-primary"></i> 2015–2020 Historical MLAs (16th Assembly)
                </button>
            </li>
        </ul>
    </div>

    <div class="tab-content" id="assemblyTabContent">
        
        <!-- ============================================================= -->
        <!-- PANE 1: CURRENT 243 MLAs (17th LEGISLATIVE ASSEMBLY)          -->
        <!-- ============================================================= -->
        <div class="tab-pane fade <?php echo $viewTab !== 'archive' ? 'show active' : ''; ?>" id="current-mlas-pane" role="tabpanel">
            
            <!-- Filter & Search Hub Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
                
                <!-- Search & Dropdown Controls -->
                <div class="row g-3 align-items-center mb-3">
                    <div class="col-12 col-lg-4">
                        <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-search text-primary me-1"></i> Search AC or MLA:</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill text-muted ps-3">
                                <i class="bi bi-search"></i>
                            </span>
                            <input type="text" id="acSearchInput" class="form-control bg-light border-start-0 rounded-end-pill py-2" placeholder="Type AC number, name, MLA or district..." onkeyup="filterAcTable()">
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg-3">
                        <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-geo-alt text-primary me-1"></i> Filter District:</label>
                        <select id="acDistrictFilter" class="form-select rounded-pill py-2 bg-light" onchange="filterAcTable()">
                            <option value="">All 38 Districts</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?> District</option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-6 col-md-4 col-lg-2">
                        <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-shield-check text-primary me-1"></i> Category:</label>
                        <select id="acResFilter" class="form-select rounded-pill py-2 bg-light" onchange="syncCategoryPills(this.value); filterAcTable();">
                            <option value="">All Categories (243)</option>
                            <option value="GEN">General (203)</option>
                            <option value="SC">SC Reserved (38)</option>
                            <option value="ST">ST Reserved (2)</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-4 col-lg-3">
                        <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-flag text-primary me-1"></i> Filter Party:</label>
                        <select id="acPartyFilter" class="form-select rounded-pill py-2 bg-light" onchange="syncPartyPills(this.value); filterAcTable();">
                            <option value="">All Parties</option>
                            <?php foreach ($partyCounts as $pName => $pNum): ?>
                                <option value="<?php echo htmlspecialchars($pName); ?>"><?php echo htmlspecialchars($pName); ?> (<?php echo $pNum; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Quick Category & Party Pills Ribbon -->
                <div class="d-flex flex-wrap gap-2 pt-3 border-top align-items-center">
                    <span class="fw-bold text-muted small me-1"><i class="bi bi-funnel-fill text-primary"></i> Category:</span>
                    
                    <button type="button" class="res-pill-btn active" data-res="" onclick="selectCategoryFilter('', this)">
                        All Seats <span class="res-badge-count">243</span>
                    </button>
                    <button type="button" class="res-pill-btn" data-res="GEN" onclick="selectCategoryFilter('GEN', this)">
                        General <span class="res-badge-count">203</span>
                    </button>
                    <button type="button" class="res-pill-btn" data-res="SC" onclick="selectCategoryFilter('SC', this)">
                        SC Reserved <span class="res-badge-count">38</span>
                    </button>
                    <button type="button" class="res-pill-btn" data-res="ST" onclick="selectCategoryFilter('ST', this)">
                        ST Reserved <span class="res-badge-count">2</span>
                    </button>

                    <span class="text-muted ms-auto small d-none d-md-inline" id="filterStatusText">Showing all 243 Vidhan Sabha Constituencies</span>
                </div>

                <!-- Party Quick Filter Pills -->
                <div class="d-flex flex-wrap gap-1.5 pt-2 mt-2 border-top align-items-center">
                    <span class="text-muted small me-1 fw-bold"><i class="bi bi-pie-chart text-secondary"></i> Parties:</span>
                    <button type="button" class="party-pill-btn active" data-party="" onclick="selectPartyFilter('', this)">
                        All <span class="party-pill-count">243</span>
                    </button>
                    <?php 
                    $topParties = array_slice($partyCounts, 0, 8, true);
                    foreach ($topParties as $p => $c): 
                    ?>
                        <button type="button" class="party-pill-btn" data-party="<?php echo htmlspecialchars($p); ?>" onclick="selectPartyFilter('<?php echo htmlspecialchars($p); ?>', this)">
                            <?php echo htmlspecialchars($p); ?> <span class="party-pill-count"><?php echo $c; ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>

            </div>

            <!-- 243 Assembly Constituencies Table Card -->
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold text-navy mb-0 font-heading">
                            <i class="bi bi-card-list text-warning me-2"></i> 243 Bihar Vidhan Sabha Constituencies Roster
                        </h5>
                        <small class="text-muted">Click any constituency to explore past election results, demographics, polling stations &amp; MLA profiles</small>
                    </div>
                    <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm" id="acCountBadge">
                        243 Constituencies
                    </span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="assemblyTable">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center" style="width: 85px;">AC No.</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Assembly Constituency</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">District &amp; Lok Sabha</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Current MLA (विधायक)</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Party</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Electors</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($constituencies)): ?>
                                <?php foreach ($constituencies as $ac): 
                                    $acNo = (int)($ac['ac_no'] ?? 0);
                                    $name = (string)($ac['name'] ?? '');
                                    $nameHi = (string)($ac['name_hi'] ?? '');
                                    $dist = (string)($ac['district'] ?? '');
                                    $pc = (string)($ac['lok_sabha'] ?? '');
                                    $mla = (string)($ac['current_mla'] ?? 'Vacant / Not Declared');
                                    $party = (string)($ac['current_party'] ?? '');
                                    $res = strtoupper((string)($ac['reservation'] ?? 'GEN'));
                                    $electors = intval($ac['total_electors'] ?? 0);
                                    $mlaUrl = getMlaUrl($ac);
                                ?>
                                    <tr class="ac-row"
                                        data-acno="<?php echo $acNo; ?>"
                                        data-name="<?php echo htmlspecialchars(strtolower($name . ' ' . $nameHi)); ?>"
                                        data-district="<?php echo htmlspecialchars(strtolower($dist)); ?>"
                                        data-mla="<?php echo htmlspecialchars(strtolower($mla)); ?>"
                                        data-party="<?php echo htmlspecialchars(strtoupper($party)); ?>"
                                        data-reservation="<?php echo htmlspecialchars($res); ?>">
                                        
                                        <!-- AC No -->
                                        <td class="text-center fw-bold" style="min-width: 80px;">
                                            <span class="badge bg-warning bg-opacity-25 text-dark px-2.5 py-1.5 rounded-pill font-monospace">
                                                AC <?php echo $acNo; ?>
                                            </span>
                                        </td>

                                        <!-- Constituency Name -->
                                        <td style="min-width: 170px;">
                                            <a href="<?php echo htmlspecialchars($mlaUrl); ?>" class="fw-bold text-navy hover-primary text-decoration-none fs-6 d-block">
                                                <?php echo htmlspecialchars($name); ?>
                                            </a>
                                            <?php if (!empty($nameHi)): ?>
                                                <small class="text-muted"><?php echo htmlspecialchars($nameHi); ?></small>
                                            <?php endif; ?>
                                            <?php if ($res === 'SC'): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1.5 py-0.5 rounded small ms-1" style="font-size: 0.68rem;">
                                                    SC Reserved
                                                </span>
                                            <?php elseif ($res === 'ST'): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-1.5 py-0.5 rounded small ms-1" style="font-size: 0.68rem;">
                                                    ST Reserved
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5 rounded small ms-1" style="font-size: 0.68rem;">
                                                    General
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- District & PC -->
                                        <td style="min-width: 160px;">
                                            <div class="fw-semibold text-dark small">📍 <?php echo htmlspecialchars($dist); ?></div>
                                            <?php if (!empty($pc)): ?>
                                                <small class="text-muted">PC: <?php echo htmlspecialchars($pc); ?></small>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Current MLA -->
                                        <td style="min-width: 180px;">
                                            <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($mla); ?>
                                            </div>
                                            <small class="text-muted">Incumbent MLA</small>
                                        </td>

                                        <!-- Party -->
                                        <td class="text-center" style="min-width: 110px;">
                                            <span class="badge-party <?php echo strtolower(preg_replace('/[^a-z0-9]/i', '', $party)); ?>">
                                                <?php echo htmlspecialchars($party ?: '-'); ?>
                                            </span>
                                        </td>

                                        <!-- Electors -->
                                        <td class="text-center" style="min-width: 110px;">
                                            <span class="fw-semibold text-dark small">
                                                <?php echo $electors ? number_format($electors) : 'Mapped'; ?>
                                            </span>
                                        </td>

                                        <!-- Action -->
                                        <td class="text-center" style="min-width: 120px;">
                                            <a href="<?php echo htmlspecialchars($mlaUrl); ?>" class="btn btn-sm btn-warning fw-bold text-dark rounded-pill px-3 py-1 shadow-sm text-nowrap">
                                                View AC <i class="bi bi-chevron-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div id="noAcAlert" class="alert alert-info rounded-4 text-center py-5 d-none m-4 shadow-sm">
                    <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
                    <h5 class="fw-bold text-dark mb-1">No Assembly Constituency found</h5>
                    <p class="text-muted mb-3">Try adjusting your search query, category, or party filter.</p>
                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="resetAcFilters()">Reset All Filters</button>
                </div>
            </div>
        </div>

        <!-- ============================================================= -->
        <!-- PANE 2: HISTORICAL 2015–2020 EX-MLAs (16th ASSEMBLY)          -->
        <!-- ============================================================= -->
        <div class="tab-pane fade <?php echo $viewTab === 'archive' ? 'show active' : ''; ?>" id="archive-mlas-pane" role="tabpanel">
            
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5 border-top border-4 border-primary">
                <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="badge bg-secondary text-white fw-bold px-2.5 py-1 rounded-pill small">Historical Archive</span>
                            <span class="badge bg-primary bg-opacity-15 text-primary fw-bold px-2.5 py-1 rounded-pill small">16th Vidhan Sabha (2015–2020)</span>
                        </div>
                        <h4 class="fw-bold text-navy mb-0 font-heading fs-5">
                            <i class="bi bi-archive-fill text-primary me-1"></i> 2015–2020 Bihar Legislative Assembly: All 243 MLAs
                        </h4>
                        <small class="text-muted">Elected MLAs from the 2015 Bihar general assembly election with contact numbers &amp; unmasking</small>
                    </div>

                    <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm" id="archiveCountBadge">
                        243 Ex-MLAs
                    </span>
                </div>

                <!-- Archive Search Box -->
                <div class="p-3.5 bg-light border-bottom">
                    <div class="row g-3 align-items-center">
                        <div class="col-12 col-md-6">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                                <input type="text" id="archiveSearchInput" class="form-control border-start-0 rounded-end-pill py-2" placeholder="Search 2015 Ex-MLA by name, AC number, constituency, or party..." onkeyup="filterArchiveTable()">
                            </div>
                        </div>
                        <div class="col-12 col-md-6 text-md-end">
                            <span class="text-muted small">🔒 Representative contact numbers protected under citizen login fair-usage quota.</span>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="archiveTable">
                        <thead class="table-light">
                            <tr>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center" style="width: 85px;">AC No.</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Assembly Constituency</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase">2015 Elected MLA (पूर्व विधायक)</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Party</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Contact</th>
                                <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($mlas2015)): ?>
                                <?php foreach ($mlas2015 as $ex): 
                                    $exNo = (int)($ex['ac_no'] ?? 0);
                                    $exAc = (string)($ex['ac_name'] ?? $ex['constituency'] ?? '');
                                    $exSlug = (string)($ex['slug'] ?? slugify($exAc));
                                    $exName = (string)($ex['mla_name'] ?? $ex['name'] ?? '');
                                    $exParty = (string)($ex['party'] ?? '');
                                    $exPhone = (string)($ex['mobile'] ?? '');
                                    $acHubUrl = SITE_URL . "/mla/{$exNo}-{$exSlug}";
                                    $searchStr = strtolower($exNo . ' ' . $exAc . ' ' . $exName . ' ' . $exParty);
                                ?>
                                    <tr class="archive-row" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>">
                                        <!-- AC No -->
                                        <td class="text-center fw-bold">
                                            <span class="badge bg-secondary bg-opacity-25 text-dark px-2.5 py-1.5 rounded-pill font-monospace">
                                                AC <?php echo $exNo; ?>
                                            </span>
                                        </td>

                                        <!-- Constituency -->
                                        <td>
                                            <a href="<?php echo htmlspecialchars($acHubUrl); ?>" class="fw-bold text-navy hover-primary text-decoration-none">
                                                <?php echo htmlspecialchars($exAc); ?>
                                            </a>
                                        </td>

                                        <!-- 2015 MLA -->
                                        <td>
                                            <div class="fw-bold text-navy" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($exName); ?>
                                            </div>
                                            <small class="text-muted">Elected 2015–2020 MLA</small>
                                        </td>

                                        <!-- Party -->
                                        <td class="text-center">
                                            <span class="badge-party <?php echo strtolower(preg_replace('/[^a-z0-9]/i', '', $exParty)); ?>">
                                                <?php echo htmlspecialchars($exParty ?: '-'); ?>
                                            </span>
                                        </td>

                                        <!-- Contact -->
                                        <td class="text-center">
                                            <?php if (!empty($exPhone)): ?>
                                                <?php echo renderMaskedPhoneButton($exPhone, $exName); ?>
                                            <?php else: ?>
                                                <span class="text-muted small">Not Disclosed</span>
                                            <?php endif; ?>
                                        </td>

                                        <!-- Action -->
                                        <td class="text-center">
                                            <a href="<?php echo htmlspecialchars($acHubUrl); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm text-nowrap">
                                                View AC Hub <i class="bi bi-chevron-right ms-1"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div id="noArchiveAlert" class="alert alert-info rounded-4 text-center py-5 d-none m-4 shadow-sm">
                    <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
                    <h5 class="fw-bold text-dark mb-1">No 2015 Ex-MLA found</h5>
                    <p class="text-muted mb-3">Try searching for another constituency name or MLA name.</p>
                    <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="document.getElementById('archiveSearchInput').value=''; filterArchiveTable();">Clear Search</button>
                </div>

            </div>

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
                    <h6 class="fw-bold text-dark font-heading mb-1">Standardized Legislative Assembly Data Sources</h6>
                    <p class="text-muted small mb-0">Vidhan Sabha constituencies, voter turnouts, and winning affidavits reference the Election Commission of India (<a href="https://eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">eci.gov.in</a>) and Chief Electoral Officer Bihar (<a href="https://ceobihar.nic.in" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">ceobihar.nic.in</a>).</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/mp" class="btn btn-outline-warning text-dark rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-bank me-1"></i>40 Lok Sabha MPs
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<script>
let currentCategory = '';
let currentParty = '';

function selectCategoryFilter(category, el) {
    currentCategory = category;
    
    // Switch to current MLAs pane if on archive
    const currentTabBtn = document.getElementById('current-mlas-tab');
    if (currentTabBtn && !currentTabBtn.classList.contains('active') && window.bootstrap) {
        const tab = new bootstrap.Tab(currentTabBtn);
        tab.show();
    }

    // Update metric cards active state
    document.querySelectorAll('.mla-stat-card').forEach(c => c.classList.remove('active'));
    if (category === '') document.getElementById('statCardAll')?.classList.add('active');
    else if (category === 'GEN') document.getElementById('statCardGen')?.classList.add('active');
    else if (category === 'SC') document.getElementById('statCardSc')?.classList.add('active');
    else if (category === 'ST') document.getElementById('statCardSt')?.classList.add('active');

    // Update Category Pills
    syncCategoryPills(category);

    // Update Dropdown
    const resSelect = document.getElementById('acResFilter');
    if (resSelect) resSelect.value = category;

    filterAcTable();
}

function syncCategoryPills(category) {
    document.querySelectorAll('.res-pill-btn').forEach(b => {
        if (b.getAttribute('data-res') === category) b.classList.add('active');
        else b.classList.remove('active');
    });
}

function selectPartyFilter(party, el) {
    currentParty = party;
    
    // Update Party Pills
    document.querySelectorAll('.party-pill-btn').forEach(b => {
        if (b.getAttribute('data-party') === party) b.classList.add('active');
        else b.classList.remove('active');
    });

    // Update Dropdown
    const partySelect = document.getElementById('acPartyFilter');
    if (partySelect) partySelect.value = party;

    filterAcTable();
}

function syncPartyPills(party) {
    currentParty = party;
    document.querySelectorAll('.party-pill-btn').forEach(b => {
        if (b.getAttribute('data-party') === party) b.classList.add('active');
        else b.classList.remove('active');
    });
}

function filterAcTable() {
    const query = (document.getElementById('acSearchInput')?.value || '').toLowerCase().trim();
    const dist = (document.getElementById('acDistrictFilter')?.value || '').toLowerCase().trim();
    const resSelect = (document.getElementById('acResFilter')?.value || '').toUpperCase().trim();
    const partySelect = (document.getElementById('acPartyFilter')?.value || '').toUpperCase().trim();
    
    const targetRes = resSelect || currentCategory;
    const targetParty = partySelect || currentParty;
    let visible = 0;

    document.querySelectorAll('#assemblyTable .ac-row').forEach(row => {
        const acNo = row.getAttribute('data-acno') || '';
        const name = row.getAttribute('data-name') || '';
        const rowDist = row.getAttribute('data-district') || '';
        const mla = row.getAttribute('data-mla') || '';
        const pParty = row.getAttribute('data-party') || '';
        const pRes = row.getAttribute('data-reservation') || '';

        const matchQuery = !query || acNo.includes(query) || name.includes(query) || rowDist.includes(query) || mla.includes(query);
        const matchDist = !dist || rowDist === dist;
        const matchRes = !targetRes || pRes === targetRes;
        const matchParty = !targetParty || pParty.includes(targetParty);

        if (matchQuery && matchDist && matchRes && matchParty) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('acCountBadge');
    if (badge) badge.innerText = visible + ' Constituencies';

    const statusText = document.getElementById('filterStatusText');
    if (statusText) {
        if (targetRes === 'SC') statusText.innerText = 'Showing ' + visible + ' SC Reserved Constituencies';
        else if (targetRes === 'ST') statusText.innerText = 'Showing ' + visible + ' ST Reserved Constituencies (Manihari & Katoria)';
        else if (targetRes === 'GEN') statusText.innerText = 'Showing ' + visible + ' General Constituencies';
        else if (query !== '') statusText.innerText = 'Showing ' + visible + ' matching constituencies';
        else statusText.innerText = 'Showing all 243 Vidhan Sabha Constituencies';
    }

    const noResults = document.getElementById('noAcAlert');
    if (noResults) {
        if (visible === 0) noResults.classList.remove('d-none');
        else noResults.classList.add('d-none');
    }
}

function filterArchiveTable() {
    const query = (document.getElementById('archiveSearchInput')?.value || '').toLowerCase().trim();
    let visible = 0;

    document.querySelectorAll('#archiveTable .archive-row').forEach(row => {
        const searchStr = row.getAttribute('data-search') || '';
        if (!query || searchStr.includes(query)) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('archiveCountBadge');
    if (badge) badge.innerText = visible + ' Ex-MLAs';

    const noResults = document.getElementById('noArchiveAlert');
    if (noResults) {
        if (visible === 0) noResults.classList.remove('d-none');
        else noResults.classList.add('d-none');
    }
}

function resetAcFilters() {
    document.getElementById('acSearchInput').value = '';
    document.getElementById('acDistrictFilter').value = '';
    document.getElementById('acResFilter').value = '';
    document.getElementById('acPartyFilter').value = '';
    selectCategoryFilter('', null);
    selectPartyFilter('', null);
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
