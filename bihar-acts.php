<?php
/**
 * BiharElection.com - Bihar Legislative Assembly: Enacted Acts & Laws Directory (1937–2026)
 * Complete 1,723+ Acts Directory from Bihar Vidhan Sabha
 * Source: Bihar Legislative Assembly (विधान सभा) Official Records:
 * https://vidhansabha.bihar.gov.in/pdf/enacted%20Bill/Act%20List%20from%201937.pdf
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/acts_data.php';

$summary = BiharActsDataProvider::getSummary();
$allActs = BiharActsDataProvider::getAllActs();

// Dynamic Act Counts by Decade and Year
$decadeCounts = [
    'ALL' => count($allActs),
    '2020s' => 0,
    '2010s' => 0,
    '2000s' => 0,
    '1990s' => 0,
    '1980s' => 0,
    '1970s' => 0,
    '1960s' => 0,
    '1950s' => 0,
    '1930-40s' => 0,
];
$yearCounts = [];

foreach ($allActs as $act) {
    $yr = intval($act['year']);
    $yearCounts[$yr] = ($yearCounts[$yr] ?? 0) + 1;

    if ($yr >= 2020) {
        $decadeCounts['2020s']++;
    } elseif ($yr >= 2010) {
        $decadeCounts['2010s']++;
    } elseif ($yr >= 2000) {
        $decadeCounts['2000s']++;
    } elseif ($yr >= 1990) {
        $decadeCounts['1990s']++;
    } elseif ($yr >= 1980) {
        $decadeCounts['1980s']++;
    } elseif ($yr >= 1970) {
        $decadeCounts['1970s']++;
    } elseif ($yr >= 1960) {
        $decadeCounts['1960s']++;
    } elseif ($yr >= 1950) {
        $decadeCounts['1950s']++;
    } else {
        $decadeCounts['1930-40s']++;
    }
}

// Query param filters
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : null;
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : '';
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

$pageTitle = 'Bihar Acts & Enacted Laws (1937–2026): 1,723+ Vidhan Sabha Acts Directory (बिहार विधान सभा अधिनियम)';
$pageDescription = 'Official and complete directory of all 1,723+ Acts & statutory laws enacted by Bihar Legislative Assembly (विधान सभा) from 1937 to 2026. Search by year, category, landmark acts, and download official Gazette PDF.';
$pageKeywords = 'Bihar Acts, Bihar Vidhan Sabha Acts, Bihar Legislative Assembly Laws, Bihar Enacted Bills, Bihar Acts 1937 to 2026, Bihar Land Reforms Act 1950, Bihar Panchayati Raj Act 2006, Bihar Prohibition Act 2016, Bihar Right to Public Services Act 2011, Bihar Reservation Act, Bihar Vidhan Sabha PDF';
$pageCanonical = getBiharActsUrl($selectedYear, $selectedCategory);
$activeNav = 'assembly';

require_once __DIR__ . '/header.php';
?>

<style>
/* Custom Styling for Bihar Acts Directory */
.acts-hero {
    background: linear-gradient(135deg, #06192e 0%, #0f3460 50%, #16213e 100%);
    position: relative;
    overflow: hidden;
}
.acts-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at 85% 15%, rgba(16, 185, 129, 0.15), transparent 45%),
                radial-gradient(circle at 15% 85%, rgba(59, 130, 246, 0.15), transparent 45%);
    pointer-events: none;
}
.kpi-act-card {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 12px;
    transition: all 0.2s ease;
}
.kpi-act-card:hover {
    transform: translateY(-3px);
    border-color: rgba(16, 185, 129, 0.5);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
}
.act-year-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 10px;
    font-size: 0.95rem;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    border-radius: 8px;
    background: #0f172a;
    color: #10b981;
    border: 1px solid rgba(16, 185, 129, 0.3);
}
.act-num-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 32px;
    font-size: 0.88rem;
    font-weight: 700;
    font-family: 'Outfit', sans-serif;
    border-radius: 6px;
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #cbd5e1;
}
.cat-pill {
    font-size: 0.76rem;
    font-weight: 600;
    padding: 3px 8px;
    border-radius: 6px;
    display: inline-block;
}
.search-act-box {
    position: relative;
}
.search-act-box i {
    position: absolute;
    left: 16px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 1.25rem;
    color: #64748b;
}
.search-act-input {
    padding-left: 48px;
    height: 52px;
    border-radius: 12px;
    font-size: 1.02rem;
    border: 2px solid #cbd5e1;
    transition: all 0.2s ease;
}
.search-act-input:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}
.decade-chip, .quick-act-chip {
    cursor: pointer;
    border-radius: 20px;
    font-size: 0.8rem;
    padding: 5px 12px;
    background: #ffffff;
    border: 1px solid #cbd5e1;
    color: #1e293b;
    transition: all 0.15s ease;
}
.decade-chip:hover, .decade-chip.active, .quick-act-chip:hover, .quick-act-chip.active {
    background: #10b981;
    color: #ffffff;
    border-color: #10b981;
    transform: translateY(-1px);
}
.act-row {
    transition: background-color 0.15s ease;
}
.act-row:hover {
    background-color: #f8fafc;
}
.copy-act-btn {
    cursor: pointer;
    font-size: 0.76rem;
    padding: 3px 8px;
    border-radius: 6px;
    transition: all 0.2s ease;
}
.copy-act-btn:hover {
    background: #0f172a;
    color: #10b981;
}
.official-source-box {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 1px solid #86efac;
    border-radius: 12px;
}
</style>

<main class="bg-light pb-5">

    <!-- Hero Header -->
    <section class="acts-hero text-white py-4 py-lg-5">
        <div class="container text-start">
            
            <!-- Breadcrumbs -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0 small text-white-50">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/mla" class="text-white text-decoration-none">Vidhan Sabha</a></li>
                    <li class="breadcrumb-item active text-success" aria-current="page">Bihar Acts (1937–2026)</li>
                </ol>
            </nav>

            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-success text-white fw-bold px-3 py-2">
                    <i class="bi bi-bank"></i> Bihar Legislative Assembly (विधान सभा)
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    <i class="bi bi-clock-history"></i> 1937 to 2026 (89 Years)
                </span>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    <i class="bi bi-journal-check"></i> 1,723+ Enacted Acts
                </span>
                <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2">
                    <i class="bi bi-file-earmark-pdf"></i> Official PDF Records
                </span>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2" style="font-family: 'Outfit', sans-serif;">
                Bihar Legislative Assembly: Enacted Acts &amp; Laws Directory (1937–2026)
            </h1>
            <p class="h6 text-warning mb-3 fw-semibold" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                बिहार विधान-मंडल द्वारा पारित बिहार और उड़ीसा तथा बिहार अधिनियमों की सम्पूर्ण ऐतिहासिक सूची (वर्ष 1937 से अब तक)
            </p>
            <p class="text-white-50 mb-4" style="font-size: 1.05rem; max-width: 980px;">
                Complete and authentic directory of all 1,723+ statutory Acts passed by the Bihar Legislature across 89 years. Search by law name, amendment, fiscal year, subject area (Land reforms, Education, Taxation, Social Welfare, Panchayati Raj, Public Safety), or download the official state assembly document.
            </p>

            <!-- State Summary KPI Row -->
            <div class="row g-2 g-md-3">
                <div class="col-6 col-md-3 col-lg">
                    <div class="kpi-act-card p-3 text-center text-white h-100">
                        <small class="text-white-50 text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Total Enacted Acts</small>
                        <span class="fs-4 fw-extrabold text-success"><?php echo number_format($summary['total_acts']); ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">1937 – 2026</small>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <div class="kpi-act-card p-3 text-center text-white h-100">
                        <small class="text-white-50 text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Years of Legislation</small>
                        <span class="fs-4 fw-extrabold text-info"><?php echo $summary['total_years']; ?> Years</span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">From 1st Assembly</small>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <div class="kpi-act-card p-3 text-center text-white h-100">
                        <small class="text-white-50 text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Finance &amp; Budget</small>
                        <span class="fs-4 fw-extrabold text-primary"><?php echo number_format($summary['categories']['Finance, Budget & Taxation'] ?? 0); ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">Appropriation &amp; Taxes</small>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <div class="kpi-act-card p-3 text-center text-white h-100">
                        <small class="text-white-50 text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Land &amp; Revenue</small>
                        <span class="fs-4 fw-extrabold text-warning"><?php echo number_format($summary['categories']['Land Reforms & Revenue'] ?? 0); ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">Tenancy &amp; Reforms</small>
                    </div>
                </div>

                <div class="col-6 col-md-3 col-lg">
                    <div class="kpi-act-card p-3 text-center text-white h-100">
                        <small class="text-white-50 text-uppercase fw-bold d-block" style="font-size: 0.72rem;">Education &amp; Univ.</small>
                        <span class="fs-4 fw-extrabold text-white"><?php echo number_format($summary['categories']['Education & Universities'] ?? 0); ?></span>
                        <small class="text-white-50 d-block" style="font-size: 0.75rem;">Schools &amp; Higher Ed</small>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Main Content Container -->
    <section class="container mt-4">
        
        <!-- Search & Interactive Control Center Card -->
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-body p-3 p-md-4">
                
                <div class="row g-3 align-items-center">
                    
                    <!-- Search Input -->
                    <div class="col-lg-6">
                        <div class="search-act-box">
                            <i class="bi bi-search"></i>
                            <input type="text" id="actsSearchInput" class="form-control search-act-input" 
                                   placeholder="Search by law name (e.g. भूमि सुधार, पंचायत, 2024, GST, मदरसा, मद्यनिषेध, विनियोग)..." 
                                   value="<?php echo htmlspecialchars($searchQuery ?: ($selectedYear ? (string)$selectedYear : '')); ?>">
                        </div>
                    </div>

                    <!-- Year Select Dropdown -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-calendar3"></i></span>
                            <select id="actsYearSelect" class="form-select border-start-0 py-2">
                                <option value="ALL">All Years (1937–2026) — <?php echo number_format($summary['total_acts']); ?> Acts</option>
                                <?php 
                                $revYears = array_reverse($summary['years_list']);
                                foreach ($revYears as $yr): 
                                    $cnt = $yearCounts[$yr] ?? 0;
                                ?>
                                <option value="<?php echo $yr; ?>" <?php echo $selectedYear == $yr ? 'selected' : ''; ?>>
                                    Year <?php echo $yr; ?> (<?php echo $cnt; ?> <?php echo $cnt === 1 ? 'Act' : 'Acts'; ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Category Select Dropdown -->
                    <div class="col-sm-6 col-lg-3">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-funnel"></i></span>
                            <select id="actsCategorySelect" class="form-select border-start-0 py-2">
                                <option value="ALL">All Categories (<?php echo number_format($summary['total_acts']); ?> Acts)</option>
                                <?php foreach ($summary['categories'] as $catName => $catCount): ?>
                                <option value="<?php echo htmlspecialchars($catName); ?>" <?php echo $selectedCategory === $catName ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($catName); ?> (<?php echo number_format($catCount); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                </div>

                <!-- Decade Fast Navigation Filter Chips with Exact Act Counts -->
                <div class="d-flex flex-wrap align-items-center gap-1.5 mt-3 pt-3 border-top">
                    <span class="small fw-bold text-muted text-uppercase me-1" style="font-size: 0.75rem;"><i class="bi bi-hourglass-split"></i> Decade Count:</span>
                    <span class="decade-chip active" data-decade="ALL">All Eras <span class="badge bg-secondary ms-1"><?php echo number_format($decadeCounts['ALL']); ?></span></span>
                    <span class="decade-chip" data-decade="2020s">2020–2026 <span class="badge bg-success ms-1"><?php echo $decadeCounts['2020s']; ?></span></span>
                    <span class="decade-chip" data-decade="2010s">2010–2019 <span class="badge bg-primary ms-1"><?php echo $decadeCounts['2010s']; ?></span></span>
                    <span class="decade-chip" data-decade="2000s">2000–2009 <span class="badge bg-info text-dark ms-1"><?php echo $decadeCounts['2000s']; ?></span></span>
                    <span class="decade-chip" data-decade="1990s">1990–1999 <span class="badge bg-secondary ms-1"><?php echo $decadeCounts['1990s']; ?></span></span>
                    <span class="decade-chip" data-decade="1980s">1980–1989 <span class="badge bg-secondary ms-1"><?php echo $decadeCounts['1980s']; ?></span></span>
                    <span class="decade-chip" data-decade="1970s">1970–1979 <span class="badge bg-secondary ms-1"><?php echo $decadeCounts['1970s']; ?></span></span>
                    <span class="decade-chip" data-decade="1960s">1960–1969 <span class="badge bg-secondary ms-1"><?php echo $decadeCounts['1960s']; ?></span></span>
                    <span class="decade-chip" data-decade="1950s">1950–1959 <span class="badge bg-secondary ms-1"><?php echo $decadeCounts['1950s']; ?></span></span>
                    <span class="decade-chip" data-decade="1930-40s">1937–1949 <span class="badge bg-secondary ms-1"><?php echo $decadeCounts['1930-40s']; ?></span></span>
                </div>

                <!-- Quick Filter Chips for Major Famous Acts -->
                <div class="d-flex flex-wrap align-items-center gap-1 mt-2 pt-2">
                    <span class="small text-muted me-1" style="font-size: 0.75rem;">Landmark Laws:</span>
                    <span class="quick-act-chip" data-term="पंचायत">बिहार पंचायत राज</span>
                    <span class="quick-act-chip" data-term="भूमि सुधार">भूमि सुधार</span>
                    <span class="quick-act-chip" data-term="मद्यनिषेध">मद्यनिषेध (शराबबंदी)</span>
                    <span class="quick-act-chip" data-term="आरक्षण">आरक्षण संशोधन</span>
                    <span class="quick-act-chip" data-term="माल और सेवा कर">जीएसटी (GST)</span>
                    <span class="quick-act-chip" data-term="मदरसा">मदरसा बोर्ड</span>
                    <span class="quick-act-chip" data-term="विश्वविद्यालय">विश्वविद्यालय</span>
                    <span class="quick-act-chip" data-term="अपराध नियंत्रण">अपराध नियंत्रण</span>
                    <span class="quick-act-chip" data-term="दाखिल-खारिज">दाखिल-खारिज</span>
                    <span class="quick-act-chip" data-term="गिग कामगार">गिग कामगार (2025)</span>
                    <span class="quick-act-chip" data-term="कर्पूरी ठाकुर">कर्पूरी ठाकुर कौशल वि०</span>
                </div>

            </div>
        </div>

        <!-- Master Acts Table & Filter Results Card -->
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-5">
            <div class="card-header bg-white py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h2 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-table text-success me-1"></i> Bihar Enacted Acts Directory (अधिनियम तालिका)
                    </h2>
                    <span class="badge bg-success rounded-pill px-3" id="actsCountBadge"><?php echo number_format($summary['total_acts']); ?> Acts Listed</span>
                </div>
                
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" onclick="window.print()" title="Print Acts Table">
                        <i class="bi bi-printer"></i> <span>Print</span>
                    </button>
                    <button class="btn btn-sm btn-success d-flex align-items-center gap-1 fw-bold" id="exportActsCsvBtn" title="Download Acts List as CSV">
                        <i class="bi bi-download"></i> <span>Export CSV</span>
                    </button>
                </div>
            </div>

            <!-- Table Responsive Container -->
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="actsMasterTable">
                    <thead class="table-light text-uppercase small text-muted">
                        <tr>
                            <th class="ps-4 py-3" style="width: 100px;">Year (वर्ष)</th>
                            <th class="py-3 text-center" style="width: 80px;">Act No.</th>
                            <th class="py-3" style="min-width: 380px;">Enacted Act Title (अधिनियम का नाम)</th>
                            <th class="py-3" style="min-width: 200px;">Legal Subject / Category</th>
                            <th class="pe-4 py-3 text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody id="actsTableBody">
                        <?php foreach ($allActs as $act): 
                            $yr = $act['year'];
                            $cat = $act['category'];
                            $color = $summary['category_colors'][$cat] ?? '#334155';
                            $icon = $summary['category_icons'][$cat] ?? 'bi-file-text';
                        ?>
                        <tr class="act-row"
                            data-year="<?php echo $yr; ?>"
                            data-actno="<?php echo $act['act_no']; ?>"
                            data-title="<?php echo htmlspecialchars($act['title_hi']); ?>"
                            data-category="<?php echo htmlspecialchars($cat); ?>"
                            data-title-kd="<?php echo htmlspecialchars($act['title_kd']); ?>">
                            
                            <!-- Year Badge -->
                            <td class="ps-4">
                                <span class="act-year-badge">
                                    <?php echo $yr; ?>
                                </span>
                            </td>

                            <!-- Act Number -->
                            <td class="text-center">
                                <span class="act-num-badge">
                                    #<?php echo $act['act_no']; ?>
                                </span>
                            </td>

                            <!-- Title (Devanagari Unicode) -->
                            <td>
                                <div class="fw-bold text-dark fs-6" style="font-family: 'Noto Sans Devanagari', sans-serif; line-height: 1.45;">
                                    <?php echo htmlspecialchars($act['title_hi']); ?>
                                </div>
                            </td>

                            <!-- Category Badge -->
                            <td>
                                <span class="badge border" style="background-color: <?php echo $color; ?>15; color: <?php echo $color; ?>; border-color: <?php echo $color; ?>40;">
                                    <i class="bi <?php echo $icon; ?> me-1"></i> <?php echo htmlspecialchars($cat); ?>
                                </span>
                            </td>

                            <!-- Action -->
                            <td class="pe-4 text-center">
                                <button class="btn btn-sm btn-outline-dark copy-act-btn" onclick="copyActTitle('<?php echo addslashes($act['title_hi']); ?>', <?php echo $yr; ?>, <?php echo $act['act_no']; ?>)" title="Copy Act Title">
                                    <i class="bi bi-copy"></i> Copy
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Empty Search State -->
            <div id="noActsState" class="text-center py-5 d-none">
                <div class="fs-1 text-muted mb-2"><i class="bi bi-search"></i></div>
                <h5 class="fw-bold text-dark">No Matching Acts Found</h5>
                <p class="text-muted small">Try searching with a different keyword, category, or clear the year filter.</p>
                <button class="btn btn-success btn-sm fw-bold" onclick="resetActsFilters()">Reset Filters</button>
            </div>

        </div>

        <!-- Official Bihar Vidhan Sabha Source Card -->
        <div class="official-source-box p-4 p-md-5 mb-5 shadow-sm">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-success text-white fw-bold">Primary Government Document</span>
                        <span class="badge bg-white text-dark border">43-Page Gazette Catalog</span>
                    </div>
                    <h3 class="h4 fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                        Official Enacted Bills &amp; Acts PDF (Bihar Legislative Assembly)
                    </h3>
                    <p class="text-muted mb-3" style="line-height: 1.55;">
                        This comprehensive repository is transcribed and verified from the official document issued by the Bihar Vidhan Sabha Secretariat: <em>"बिहार विधान-मंडल द्वारा पारित बिहार और उड़ीसा तथा बिहार अधिनियमों की सूची (वर्ष 1937 से अब तक)"</em>.
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="https://vidhansabha.bihar.gov.in/pdf/enacted%20Bill/Act%20List%20from%201937.pdf" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            <span>Download Official Vidhan Sabha PDF (1.1 MB)</span>
                            <i class="bi bi-box-arrow-up-right small"></i>
                        </a>
                        <a href="https://vidhansabha.bihar.gov.in/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-dark fw-bold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-bank"></i>
                            <span>Visit Bihar Vidhan Sabha Portal</span>
                        </a>
                    </div>
                </div>

                <div class="col-lg-4 text-center text-lg-end">
                    <div class="p-3 bg-white rounded-3 border d-inline-block text-start shadow-sm" style="max-width: 280px;">
                        <div class="small fw-bold text-dark mb-1"><i class="bi bi-patch-check-fill text-success"></i> Document Meta:</div>
                        <ul class="list-unstyled small text-muted mb-0 space-y-1">
                            <li><strong>Publisher:</strong> Bihar Vidhan Sabha</li>
                            <li><strong>Timeframe:</strong> 1937 – 2026</li>
                            <li><strong>Acts Count:</strong> 1,723 Enactments</li>
                            <li><strong>Language:</strong> Hindi (Official)</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Legislative History & Eras Breakdown -->
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-hourglass-split text-success me-2"></i> Bihar Legislative Eras &amp; Major Historical Milestones
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    
                    <!-- Era 1 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <div class="badge bg-dark mb-2">1937 – 1947</div>
                            <h6 class="fw-bold text-dark mb-1">Provincial Legislature Era</h6>
                            <small class="text-muted d-block mb-2">Government of India Act 1935</small>
                            <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.45;">
                                Bihar Ministers' Salaries Act 1937, Sugar Factories Control Act 1937, Agricultural Income Tax 1938, and wartime famine relief funds under 1st Premier Sri Krishna Sinha.
                            </p>
                        </div>
                    </div>

                    <!-- Era 2 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <div class="badge bg-primary mb-2">1947 – 1970</div>
                            <h6 class="fw-bold text-dark mb-1">Abolition &amp; Land Reforms</h6>
                            <small class="text-muted d-block mb-2">Post-Independence Nation Building</small>
                            <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.45;">
                                Bihar Land Reforms Act 1950 (Abolition of Zamindari), Bihar Tenancy Amendments, State University charters (Patna, Bihar, Bhagalpur, Magadh), and early irrigation codes.
                            </p>
                        </div>
                    </div>

                    <!-- Era 3 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <div class="badge bg-warning text-dark mb-2">1970 – 2000</div>
                            <h6 class="fw-bold text-dark mb-1">Welfare &amp; Decentralization</h6>
                            <small class="text-muted d-block mb-2">Social Justice &amp; Local Self-Govt</small>
                            <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.45;">
                                Bihar State Madrasa Board Act 1981, Sanskrit Board Act 1981, Bihar Reservation (SC/ST/OBC) Acts 1991, Motor Vehicles taxation codes, and industrial development boards.
                            </p>
                        </div>
                    </div>

                    <!-- Era 4 -->
                    <div class="col-md-6 col-lg-3">
                        <div class="p-3 bg-light rounded-3 border h-100">
                            <div class="badge bg-success mb-2">2000 – 2026</div>
                            <h6 class="fw-bold text-dark mb-1">Modern Era &amp; Governance</h6>
                            <small class="text-muted d-block mb-2">Post-Bifurcation &amp; Digital Bihar</small>
                            <p class="small text-muted mb-0" style="font-size: 0.8rem; line-height: 1.45;">
                                Bihar Panchayati Raj Act 2006 (50% Women Quota), Bihar Prohibition &amp; Excise Act 2016, 75% Reservation Amendment 2023, and Gig Workers Welfare Act 2025.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Frequently Asked Questions (FAQs) Accordion -->
        <div class="card border-0 shadow-sm rounded-4 mb-5">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h3 class="h5 mb-0 fw-bold text-dark" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-question-circle-fill text-success me-2"></i> Frequently Asked Questions (अक्सर पूछे जाने वाले प्रश्न)
                </h3>
            </div>
            <div class="card-body p-4">
                <div class="accordion" id="actsFaqAccordion">
                    
                    <!-- FAQ 1 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqActHeading1">
                            <button class="accordion-button fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqActCollapse1" aria-expanded="true" aria-controls="faqActCollapse1">
                                बिहार विधान सभा द्वारा वर्ष 1937 से अब तक कुल कितने अधिनियम पारित किए गए हैं?
                            </button>
                        </h2>
                        <div id="faqActCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqActHeading1" data-bs-parent="#actsFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                बिहार विधान सभा सचिवालय द्वारा प्रकाशित आधिकारिक दस्तावेज के अनुसार, वर्ष 1937 में प्रथम विधानमंडल से लेकर वर्ष 2026 तक 89 वर्षों में कुल <strong>1,723 से अधिक अधिनियम (Acts)</strong> पारित किए गए हैं। इनमें वित्त, भूमि सुधार, शिक्षा, पंचायती राज, आरक्षण और मद्यनिषेध जैसे कानून शामिल हैं।
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 2 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqActHeading2">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqActCollapse2" aria-expanded="false" aria-controls="faqActCollapse2">
                                वर्ष 1937 में बिहार विधानमंडल द्वारा पारित पहला अधिनियम कौन सा था?
                            </button>
                        </h2>
                        <div id="faqActCollapse2" class="accordion-collapse collapse" aria-labelledby="faqActHeading2" data-bs-parent="#actsFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                वर्ष 1937 का पहला अधिनियम <strong>"बिहार मंत्रियों का वेतन अधिनियम, 1937" (Bihar Ministers' Salaries Act, 1937)</strong> था। इसके बाद बिहार विधानमंडल अधिकारियों का वेतन अधिनियम और दुर्भिक्ष राहत कोष अधिनियम पारित किए गए थे।
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 3 -->
                    <div class="accordion-item border-0 border-bottom">
                        <h2 class="accordion-header" id="faqActHeading3">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqActCollapse3" aria-expanded="false" aria-controls="faqActCollapse3">
                                क्या इस सूची में भूमि सुधार, शराबबंदी और आरक्षण जैसे प्रमुख कानून शामिल हैं?
                            </button>
                        </h2>
                        <div id="faqActCollapse3" class="accordion-collapse collapse" aria-labelledby="faqActHeading3" data-bs-parent="#actsFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                हाँ, इस सूची में बिहार के सभी ऐतिहासिक और युगांतरकारी कानून शामिल हैं, जैसे:
                                <ul class="mb-0 mt-2">
                                    <li><strong>बिहार भूमि सुधार अधिनियम, 1950 (जमींदारी उन्मूलन)</strong></li>
                                    <li><strong>बिहार पंचायती राज अधिनियम, 2006 (महिलाओं को 50% आरक्षण)</strong></li>
                                    <li><strong>बिहार मद्यनिषेध और उत्पाद अधिनियम, 2016 (शराबबंदी)</strong></li>
                                    <li><strong>बिहार आरक्षण (संशोधन) अधिनियम, 2023 (65% + 10% EWS = 75% आरक्षण)</strong></li>
                                    <li><strong>बिहार प्लेटफॉर्म आधारित गिग कामगार अधिनियम, 2025</strong></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- FAQ 4 -->
                    <div class="accordion-item border-0">
                        <h2 class="accordion-header" id="faqActHeading4">
                            <button class="accordion-button collapsed fw-bold text-dark bg-white shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#faqActCollapse4" aria-expanded="false" aria-controls="faqActCollapse4">
                                क्या इस आधिकारिक सूची का मूल सरकारी पीडीएफ दस्तावेज डाउनलोड किया जा सकता है?
                            </button>
                        </h2>
                        <div id="faqActCollapse4" class="accordion-collapse collapse" aria-labelledby="faqActHeading4" data-bs-parent="#actsFaqAccordion">
                            <div class="accordion-body text-muted pt-0">
                                हाँ, बिहार विधान सभा की आधिकारिक वेबसाइट पर उपलब्ध 43 पृष्ठों की मूल पीडीएफ फाइल को इस पृष्ठ पर दिए गए "Download Official Vidhan Sabha PDF" बटन पर क्लिक करके सीधे डाउनलोड किया जा सकता है।
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </section>

</main>

<!-- Copy Toast Notification -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1080;">
    <div id="actCopyToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div>
                    <strong id="actToastTitle">Act Copied!</strong>
                    <div class="small text-white-50" id="actToastMessage">Copied to clipboard.</div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

<!-- Structured Data: JSON-LD Schema (Multi-Entity: Legislation, Dataset, BreadcrumbList, FAQPage) -->
<script type="application/ld+json">
[
  {
    "@context": "https://schema.org",
    "@type": "Legislation",
    "name": "Bihar Legislative Assembly Enacted Acts (1937–2026)",
    "alternateName": "बिहार विधान सभा अधिनियम सूची (1937–2026)",
    "description": "Complete official repository of 1,723+ statutory Acts passed by the Bihar Legislative Assembly from 1937 to 2026 across 89 years.",
    "legislationType": "Statute / Enacted Act",
    "legislationJurisdiction": "Bihar, India",
    "url": "<?php echo getBiharActsUrl(); ?>",
    "publisher": {
      "@type": "GovernmentOrganization",
      "name": "Bihar Legislative Assembly (बिहार विधान सभा)",
      "url": "https://vidhansabha.bihar.gov.in/"
    }
  },
  {
    "@context": "https://schema.org",
    "@type": "Dataset",
    "name": "1,723+ Bihar Legislative Assembly Enacted Acts Dataset (1937-2026)",
    "description": "Historical and contemporary legal repository containing 1,723 enactments categorized by year, subject, and act number.",
    "url": "<?php echo getBiharActsUrl(); ?>",
    "keywords": [
      "Bihar Acts",
      "Bihar Vidhan Sabha Acts",
      "Bihar Laws 1937 to 2026",
      "Bihar Land Reforms Act",
      "Bihar Panchayati Raj Act",
      "Bihar Prohibition Act"
    ],
    "creator": {
      "@type": "Organization",
      "name": "Bihar Election Data Platform",
      "url": "<?php echo SITE_URL; ?>"
    },
    "temporalCoverage": "1937/2026",
    "spatialCoverage": {
      "@type": "Place",
      "name": "Bihar, India"
    }
  },
  {
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
      {
        "@type": "ListItem",
        "position": 1,
        "name": "Home",
        "item": "<?php echo SITE_URL; ?>/"
      },
      {
        "@type": "ListItem",
        "position": 2,
        "name": "Vidhan Sabha",
        "item": "<?php echo SITE_URL; ?>/mla"
      },
      {
        "@type": "ListItem",
        "position": 3,
        "name": "Bihar Acts (1937–2026)",
        "item": "<?php echo getBiharActsUrl(); ?>"
      }
    ]
  },
  {
    "@context": "https://schema.org",
    "@type": "FAQPage",
    "mainEntity": [
      {
        "@type": "Question",
        "name": "बिहार विधान सभा द्वारा वर्ष 1937 से अब तक कुल कितने अधिनियम पारित किए गए हैं?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "बिहार विधान सभा सचिवालय द्वारा प्रकाशित आधिकारिक दस्तावेज के अनुसार, वर्ष 1937 में प्रथम विधानमंडल से लेकर वर्ष 2026 तक 89 वर्षों में कुल 1,723 से अधिक अधिनियम (Acts) पारित किए गए हैं। इनमें वित्त, भूमि सुधार, शिक्षा, पंचायती राज, आरक्षण और मद्यनिषेध जैसे कानून शामिल हैं।"
        }
      },
      {
        "@type": "Question",
        "name": "वर्ष 1937 में बिहार विधानमंडल द्वारा पारित पहला अधिनियम कौन सा था?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "वर्ष 1937 का पहला अधिनियम 'बिहार मंत्रियों का वेतन अधिनियम, 1937' (Bihar Ministers' Salaries Act, 1937) था। इसके बाद बिहार विधानमंडल अधिकारियों का वेतन अधिनियम और दुर्भिक्ष राहत कोष अधिनियम पारित किए गए थे।"
        }
      },
      {
        "@type": "Question",
        "name": "क्या इस सूची में भूमि सुधार, शराबबंदी और आरक्षण जैसे प्रमुख कानून शामिल हैं?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "हाँ, इस सूची में बिहार भूमि सुधार अधिनियम 1950 (जमींदारी उन्मूलन), बिहार पंचायती राज अधिनियम 2006 (महिलाओं को 50% आरक्षण), बिहार मद्यनिषेध और उत्पाद अधिनियम 2016 (शराबबंदी), और बिहार आरक्षण संशोधन अधिनियम 2023 जैसे ऐतिहासिक कानून शामिल हैं।"
        }
      },
      {
        "@type": "Question",
        "name": "क्या इस आधिकारिक सूची का मूल सरकारी पीडीएफ दस्तावेज डाउनलोड किया जा सकता है?",
        "acceptedAnswer": {
          "@type": "Answer",
          "text": "हाँ, बिहार विधान सभा की आधिकारिक वेबसाइट पर उपलब्ध 43 पृष्ठों की मूल पीडीएफ फाइल को इस पृष्ठ पर दिए गए 'Download Official Vidhan Sabha PDF' बटन पर क्लिक करके सीधे डाउनलोड किया जा सकता है।"
        }
      }
    ]
  }
]
</script>

<!-- Client-side Interactive Search & Filtering Logic -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('actsSearchInput');
    const yearSelect = document.getElementById('actsYearSelect');
    const catSelect = document.getElementById('actsCategorySelect');
    const decadeChips = document.querySelectorAll('.decade-chip');
    const quickChips = document.querySelectorAll('.quick-act-chip');
    const tableBody = document.getElementById('actsTableBody');
    const rows = Array.from(document.querySelectorAll('.act-row'));
    const actsCountBadge = document.getElementById('actsCountBadge');
    const noActsState = document.getElementById('noActsState');
    const masterTable = document.getElementById('actsMasterTable');
    const exportCsvBtn = document.getElementById('exportActsCsvBtn');

    let currentYear = yearSelect.value || 'ALL';
    let currentCategory = catSelect.value || 'ALL';
    let currentDecade = 'ALL';
    let currentSearch = (searchInput.value || '').trim().toLowerCase();

    function matchDecade(year, decade) {
        if (decade === 'ALL') return true;
        const y = parseInt(year);
        if (decade === '2020s') return y >= 2020 && y <= 2029;
        if (decade === '2010s') return y >= 2010 && y <= 2019;
        if (decade === '2000s') return y >= 2000 && y <= 2009;
        if (decade === '1990s') return y >= 1990 && y <= 1999;
        if (decade === '1980s') return y >= 1980 && y <= 1989;
        if (decade === '1970s') return y >= 1970 && y <= 1979;
        if (decade === '1960s') return y >= 1960 && y <= 1969;
        if (decade === '1950s') return y >= 1950 && y <= 1959;
        if (decade === '1930-40s') return y >= 1930 && y <= 1949;
        return true;
    }

    function filterActs() {
        let visibleCount = 0;

        rows.forEach(row => {
            const y = row.dataset.year || '';
            const actNo = row.dataset.actno || '';
            const title = (row.dataset.title || '').toLowerCase();
            const category = row.dataset.category || '';
            const titleKd = (row.dataset.titleKd || '').toLowerCase();

            // Year match
            const yearMatch = (currentYear === 'ALL' || y === currentYear);

            // Category match
            const catMatch = (currentCategory === 'ALL' || category === currentCategory);

            // Decade match
            const decadeMatch = matchDecade(y, currentDecade);

            // Search query match
            let searchMatch = true;
            if (currentSearch) {
                searchMatch = y.includes(currentSearch) ||
                              actNo === currentSearch ||
                              title.includes(currentSearch) ||
                              category.toLowerCase().includes(currentSearch) ||
                              titleKd.includes(currentSearch);
            }

            if (yearMatch && catMatch && decadeMatch && searchMatch) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Update badge
        actsCountBadge.textContent = `${visibleCount.toLocaleString()} Acts Listed`;

        // Handle empty state
        if (visibleCount === 0) {
            noActsState.classList.remove('d-none');
            masterTable.classList.add('d-none');
        } else {
            noActsState.classList.add('d-none');
            masterTable.classList.remove('d-none');
        }
    }

    // Search Input Event
    searchInput.addEventListener('input', function() {
        currentSearch = this.value.trim().toLowerCase();
        decadeChips.forEach(c => c.classList.remove('active'));
        quickChips.forEach(c => c.classList.remove('active'));
        filterActs();
    });

    // Year Dropdown
    yearSelect.addEventListener('change', function() {
        currentYear = this.value;
        filterActs();
    });

    // Category Dropdown
    catSelect.addEventListener('change', function() {
        currentCategory = this.value;
        filterActs();
    });

    // Decade Chips
    decadeChips.forEach(chip => {
        chip.addEventListener('click', function() {
            decadeChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            currentDecade = this.dataset.decade;
            filterActs();
        });
    });

    // Quick Chips for Landmark Laws
    quickChips.forEach(chip => {
        chip.addEventListener('click', function() {
            quickChips.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            searchInput.value = this.dataset.term;
            currentSearch = this.dataset.term.toLowerCase();
            filterActs();
            masterTable.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // Reset Filters Function
    window.resetActsFilters = function() {
        searchInput.value = '';
        currentSearch = '';
        currentYear = 'ALL';
        currentCategory = 'ALL';
        currentDecade = 'ALL';
        yearSelect.value = 'ALL';
        catSelect.value = 'ALL';
        decadeChips.forEach(c => c.classList.remove('active'));
        document.querySelector('.decade-chip[data-decade="ALL"]').classList.add('active');
        quickChips.forEach(c => c.classList.remove('active'));
        filterActs();
    };

    // CSV Export Handler
    exportCsvBtn.addEventListener('click', function() {
        let csvContent = "data:text/csv;charset=utf-8,";
        csvContent += "Year,Act Number,Act Title (Hindi),Category\n";

        rows.forEach(r => {
            if (r.style.display !== 'none') {
                const yr = r.dataset.year;
                const actNo = r.dataset.actno;
                const title = `"${(r.dataset.title || '').replace(/"/g, '""')}"`;
                const cat = `"${(r.dataset.category || '').replace(/"/g, '""')}"`;
                csvContent += `${yr},${actNo},${title},${cat}\n`;
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "bihar_legislative_assembly_acts_1937_2026.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Initial filter run if pre-filled
    if (currentSearch || currentYear !== 'ALL' || currentCategory !== 'ALL') {
        filterActs();
    }
});

// Copy Act Title Function
function copyActTitle(title, year, actNo) {
    const textToCopy = `${title} (Act #${actNo} of ${year})`;
    navigator.clipboard.writeText(textToCopy).then(function() {
        const toastEl = document.getElementById('actCopyToast');
        document.getElementById('actToastTitle').textContent = `Act #${actNo} of ${year} Copied!`;
        document.getElementById('actToastMessage').textContent = `${title} copied to clipboard.`;
        const toast = new bootstrap.Toast(toastEl);
        toast.show();
    }).catch(function(err) {
        console.error('Copy failed: ', err);
    });
}
</script>

<?php
require_once __DIR__ . '/footer.php';
?>
