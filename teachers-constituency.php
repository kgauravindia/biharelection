<?php
/**
 * BiharElection.com - Bihar Legislative Council (Vidhan Parishad)
 * Complete Guide to 6 Teachers' Constituencies (शिक्षक निर्वाचन क्षेत्र)
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Bihar Teachers Constituencies (6 Seats): Vidhan Parishad MLC Electoral Guide, Voter Form 19 & 2026 Election';
$pageDescription = 'Guide to Bihar MLC Teachers\' Constituencies (Vidhan Parishad): Patna, Tirhut, Saran & Darbhanga seats, candidate lists, Form 19 voter roll & 2026 election.';
$pageKeywords = 'Bihar Teachers Constituency, Bihar Vidhan Parishad Teacher MLC, Patna Teachers Constituency, Tirhut Teachers, Darbhanga Teachers, Saran Teachers, Gaya Teachers, Kosi Teachers, Form 19 Bihar, Article 171 Constitution';
$pageCanonical = SITE_URL . '/teachers-constituency';
$activeNav = 'mlc';

// 6 Teachers Constituencies Data
$teachersSeats = [
    [
        'id' => 'patna-teachers',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'Patna',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'ro_url' => 'https://patnadivision.bih.nic.in/',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Nawal Kishore Yadav (BJP) / Vacant for 2026 Poll',
        'desc' => 'Representing secondary, higher secondary, and collegiate faculty across the state capital Patna, Nalanda, and Nawada.'
    ],
    [
        'id' => 'tirhut-teachers',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'Muzaffarpur',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'ro_url' => 'https://tirhutdivision.bih.nic.in/',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Prof. Sanjay Kumar Singh (CPI/JD-U) / Vacant for 2026 Poll',
        'desc' => 'Representing teachers and university professors across secondary schools and constituent colleges in the Tirhut division.'
    ],
    [
        'id' => 'darbhanga-teachers',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'Darbhanga',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'ro_url' => 'https://darbhangadivision.bih.nic.in/',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Dr. Madan Mohan Jha (INC) / Vacant for 2026 Poll',
        'desc' => 'Covering educator faculty in Mithila region (LNMU, KSDSU, colleges, and high schools across Darbhanga, Madhubani, Samastipur, Begusarai).'
    ],
    [
        'id' => 'saran-teachers',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'Chapra',
        'ro' => 'Divisional Commissioner, Saran (Chapra)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'ro_url' => 'https://sarandivision.bih.nic.in/',
        'districts' => ['Saran', 'Siwan', 'Gopalganj', 'East Champaran', 'West Champaran'],
        'districts_hi' => ['सारण', 'सिवान', 'गोपालगंज', 'पूर्वी चंपारण', 'पश्चिमी चंपारण'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Afaque Ahmad / Vacant for 2026 Poll',
        'desc' => 'Encompassing teachers and professors in the Saran and Champaran belt (Chapra, Siwan, Gopalganj, Motihari, Bettiah).'
    ],
    [
        'id' => 'gaya-teachers',
        'name' => 'Gaya Teachers',
        'name_hi' => 'गया शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'Gaya',
        'ro' => 'Divisional Commissioner, Magadh (Gaya)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, मगध (गया)',
        'ro_url' => 'https://magadhdivision.bih.nic.in/',
        'districts' => ['Gaya', 'Jehanabad', 'Arwal', 'Aurangabad', 'Rohtas', 'Kaimur', 'Bhojpur', 'Buxar'],
        'districts_hi' => ['गया', 'जहानाबाद', 'अरवल', 'औरंगाबाद', 'रोहतास', 'कैमूर', 'भोजपुर', 'बक्सर'],
        'status' => 'active_term',
        'status_label' => 'Active Sitting Member',
        'poll_date' => 'Next in 2029 Cycle',
        'result_date' => '-',
        'incumbent' => 'Sanjeev Shyam Singh (JD-U)',
        'desc' => 'Covers educator electors across 8 South Bihar districts under Magadh and Shahabad educational divisions.'
    ],
    [
        'id' => 'kosi-teachers',
        'name' => 'Kosi Teachers',
        'name_hi' => 'कोसी शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'Saharsa / Purnia',
        'ro' => 'Divisional Commissioner, Kosi / Purnia',
        'ro_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया',
        'ro_url' => 'https://kosidivision.bih.nic.in/',
        'districts' => ['Saharsa', 'Supaul', 'Madhepura', 'Purnia', 'Araria', 'Kishanganj', 'Katihar', 'Bhagalpur', 'Banka', 'Munger', 'Jamui', 'Lakhisarai', 'Sheikhpura', 'Khagaria'],
        'districts_hi' => ['सहरसा', 'सुपौल', 'मधेपुरा', 'पूर्णिया', 'अररिया', 'किशनगंज', 'कटिहार', 'भागलपुर', 'बांका', 'मुंगेर', 'जमुई', 'लखीसराय', 'शेखपुरा', 'खगड़िया'],
        'status' => 'active_term',
        'status_label' => 'Active Sitting Member',
        'poll_date' => 'Next in 2029 Cycle',
        'result_date' => '-',
        'incumbent' => 'Dr. Sanjeev Kumar Singh (JD-U)',
        'desc' => 'Spans 14 districts across Eastern Bihar, Kosi, Seemanchal, Bhagalpur, and Munger educational zones.'
    ]
];

require_once __DIR__ . '/header.php';
?>

<style>
.teach-hero {
    background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #0f172a 100%);
    color: #fff;
    padding: 60px 0 50px;
    position: relative;
    overflow: hidden;
}
.teach-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.constituency-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 16px rgba(11, 25, 44, 0.04);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.constituency-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px -6px rgba(16, 185, 129, 0.18);
    border-color: #10b981;
}
.district-pill {
    background-color: #f1f5f9;
    color: #334155;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 3px 10px;
    border-radius: 50px;
    border: 1px solid #e2e8f0;
    display: inline-block;
}
.info-step-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e2e8f0;
    padding: 1.5rem;
    transition: all 0.25s ease;
}
.info-step-card:hover {
    border-color: #6ee7b7;
    box-shadow: 0 8px 24px rgba(16, 185, 129, 0.08);
}
@keyframes pulseGlow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.02); }
}
.blink-anim {
    animation: pulseGlow 1.8s infinite ease-in-out;
}
</style>

<!-- Hero Section -->
<section class="teach-hero">
    <div class="container text-start position-relative">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-success text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                📚 Article 171(3)(b) &bull; Constitution of India
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                6 Total Teachers' Seats
            </span>
            <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill blink-anim shadow-sm">
                <i class="bi bi-broadcast me-1"></i> 4 Seats in 2026 Biennial Election
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/representatives" class="text-white-50 text-decoration-none">Representatives</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/mlc" class="text-white-50 text-decoration-none">Vidhan Parishad (MLCs)</a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Teachers' Constituencies</li>
            </ol>
        </nav>

        <h1 class="display-5 fw-extrabold text-white mb-2">
            Bihar Teachers' Constituencies (शिक्षक निर्वाचन क्षेत्र) <br>
            <span style="color: var(--accent-saffron);">6 Legislative Council (Vidhan Parishad) Seats</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.1rem; max-width: 880px;">
            Comprehensive guide to the 6 dedicated Teachers' Constituencies of the Bihar Legislative Council. Learn about constitutional representation, Form 19 voter registration eligibility for secondary school &amp; college faculty, and live updates on the ongoing 2026 Biennial Elections.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-danger fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-broadcast"></i> 2026 Biennial Election Hub
            </a>
            <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="btn btn-primary fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-mortarboard"></i> 6 Graduates' Constituencies
            </a>
            <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-outline-light fw-bold px-3.5 py-2.5 shadow-sm">
                <i class="bi bi-journal-text me-1"></i> 75 MLCs Directory
            </a>
        </div>
    </div>
</section>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Active 2026 Election Notice & Official Poll Turnout for Teachers -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 text-white overflow-hidden" style="background: linear-gradient(135deg, #064e3b 0%, #059669 50%, #047857 100%);">
        <div class="p-4">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3 pb-3 border-bottom border-white border-opacity-15">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                        <i class="bi bi-book-half"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill text-uppercase">2026 Biennial Election</span>
                            <span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill">Official Turnout Declared: 82.85%</span>
                            <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-2.5 py-1 rounded-pill">Gazette: TCGCGajat2026</span>
                        </div>
                        <h4 class="fw-extrabold text-white mb-1">4 Teachers' Constituencies Official Poll Turnout &amp; Declared Winners</h4>
                        <p class="text-white-50 mb-0 small" style="max-width: 820px;">
                            Official polling concluded on <strong>23 October 2026</strong> across 335 polling booths. Total registered teacher electorate: <strong>40,253</strong> (Male: 30,677 | Female: 9,573 | Third: 3). Final official voting turnout: <strong>82.85%</strong>. All 4 seats declared.
                        </p>
                    </div>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-warning fw-bold text-dark rounded-pill px-4 py-2.5 shadow-sm text-nowrap d-inline-flex align-items-center gap-2">
                        <span>View Live Results Hub</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>

            <!-- 4 Seats Poll Metrics Grid -->
            <div class="row g-3">
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 extra-small fw-bold text-uppercase">Saran Teachers</span>
                            <span class="badge bg-success text-white extra-small">IND WON</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-extrabold text-warning">89.05%</span>
                            <span class="text-white-50 extra-small">(State Record)</span>
                        </div>
                        <small class="text-white-50 d-block extra-small mb-1">Electorate: 10,748 &bull; 98 Booths &bull; 18 Candidates</small>
                        <div class="fw-semibold text-white extra-small text-truncate">Elected: Rahul Raj (IND)</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 extra-small fw-bold text-uppercase">Darbhanga Teachers</span>
                            <span class="badge bg-success text-white extra-small">JAN SURAAJ WON</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-extrabold text-warning">85.87%</span>
                            <span class="text-white-50 extra-small">(High Turnout)</span>
                        </div>
                        <small class="text-white-50 d-block extra-small mb-1">Electorate: 9,026 &bull; 99 Booths &bull; 13 Candidates</small>
                        <div class="fw-semibold text-white extra-small text-truncate">Elected: Dr. Suresh Prasad Rai (JSP)</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 extra-small fw-bold text-uppercase">Tirhut Teachers</span>
                            <span class="badge bg-success text-white extra-small">BJP WON</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-extrabold text-warning">82.99%</span>
                            <span class="text-white-50 extra-small">(High Turnout)</span>
                        </div>
                        <small class="text-white-50 d-block extra-small mb-1">Electorate: 8,760 &bull; 58 Booths &bull; 11 Candidates</small>
                        <div class="fw-semibold text-white extra-small text-truncate">Elected: Dr. Narendra Prasad Singh (BJP)</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="p-3 rounded-3 bg-white bg-opacity-10 border border-white border-opacity-10 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-white-50 extra-small fw-bold text-uppercase">Patna Teachers</span>
                            <span class="badge bg-success text-white extra-small">RJD WON</span>
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-1">
                            <span class="fs-4 fw-extrabold text-warning">74.75%</span>
                            <span class="text-white-50 extra-small">(Upset Win)</span>
                        </div>
                        <small class="text-white-50 d-block extra-small mb-1">Electorate: 11,719 &bull; 80 Booths &bull; 18 Candidates</small>
                        <div class="fw-semibold text-white extra-small text-truncate">Elected: Dr. Sanjeev Kumar (RJD)</div>
                    </div>
                </div>
            </div>

            <!-- Official PDF Citations -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-3 mt-3 border-top border-white border-opacity-10 extra-small">
                <span class="text-white-50">
                    <i class="bi bi-file-earmark-check-fill text-warning me-1"></i> Official Documents from Chief Electoral Officer (CEO), Bihar:
                </span>
                <div class="d-flex gap-2">
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/VTR/Final%20VTR%20in%20TC%20GC%20Election%202026.pdf" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-none fw-semibold">
                        <i class="bi bi-file-pdf text-danger me-0.5"></i> Final VTR Report (PDF)
                    </a>
                    <span class="text-white-50">&bull;</span>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/PressRelease2026/Press%20Note%2023-10-2026.pdf" target="_blank" rel="noopener noreferrer" class="text-white text-decoration-none fw-semibold">
                        <i class="bi bi-file-earmark-text text-info me-0.5"></i> Press Note 23-10-2026 (PDF)
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 6 Teachers' Constituencies Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-extrabold text-navy mb-1">
                <i class="bi bi-person-workspace text-success me-2"></i> All 6 Bihar Teachers' Constituencies
            </h3>
            <p class="text-muted small mb-0">Direct democratic representation for Bihar educators in the State Legislature</p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php foreach ($teachersSeats as $seat): 
            $isOngoing = ($seat['status'] === 'election_2026');
        ?>
            <div class="col-md-6 col-lg-4" id="<?php echo $seat['id']; ?>">
                <div class="constituency-card p-4">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill extra-small fw-bold">
                                📚 Teachers Quota
                            </span>
                            <?php if ($isOngoing): ?>
                                <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill extra-small fw-bold blink-anim">
                                    <i class="bi bi-broadcast me-1"></i> Poll Oct 2026
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill extra-small fw-bold">
                                    ✓ Active Term
                                </span>
                            <?php endif; ?>
                        </div>

                        <h4 class="fw-extrabold text-navy mb-0.5"><?php echo htmlspecialchars($seat['name']); ?></h4>
                        <div class="text-warning fw-bold small mb-3"><?php echo htmlspecialchars($seat['name_hi']); ?></div>

                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($seat['desc']); ?></p>

                        <!-- Returning Officer & HQ -->
                        <div class="p-2.5 bg-light rounded-3 border mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted extra-small">HQ / Div:</span>
                                <strong class="text-dark"><?php echo htmlspecialchars($seat['hq']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted extra-small">Returning Officer:</span>
                                <span class="text-navy fw-semibold extra-small text-end" style="max-width: 180px;">
                                    <?php if (!empty($seat['ro_url'])): ?>
                                        <a href="<?php echo htmlspecialchars($seat['ro_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-primary hover-underline text-decoration-none">
                                            <?php echo htmlspecialchars($seat['ro']); ?> <i class="bi bi-box-arrow-up-right ms-0.5" style="font-size: 9px;"></i>
                                        </a>
                                    <?php else: ?>
                                        <?php echo htmlspecialchars($seat['ro']); ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>

                        <!-- Districts Included -->
                        <div class="mb-3">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1.5">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Covered Districts (<?php echo count($seat['districts']); ?>):
                            </span>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($seat['districts'] as $dist): ?>
                                    <span class="district-pill"><?php echo htmlspecialchars($dist); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted extra-small">Sitting Member:</span>
                            <span class="fw-bold text-navy extra-small text-end text-truncate" style="max-width: 180px;">
                                <?php echo htmlspecialchars($seat['incumbent']); ?>
                            </span>
                        </div>
                        <?php 
                            $conSlug = rtrim($seat['id'], 's') . '-constituency';
                            $hasBoothPage = file_exists(__DIR__ . '/' . $conSlug . '.php');
                        ?>
                        <?php if ($hasBoothPage): ?>
                            <a href="<?php echo SITE_URL . '/' . $conSlug; ?>" class="btn btn-sm btn-success w-100 rounded-pill fw-semibold py-1.5 mb-2 shadow-sm d-inline-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-geo-alt-fill"></i> View Polling Booths (मतदान केंद्र)
                            </a>
                        <?php endif; ?>
                        <?php if ($isOngoing): ?>
                            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election#<?php echo $seat['id']; ?>" class="btn btn-sm btn-outline-danger w-100 rounded-pill fw-semibold py-1.5">
                                <i class="bi bi-box-arrow-up-right me-1"></i> 2026 Election Schedule &amp; Candidates
                            </a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-sm btn-outline-secondary w-100 rounded-pill fw-semibold py-1.5">
                                <i class="bi bi-person-lines-fill me-1"></i> View Member Profile
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Voter Registration & Electoral Mechanism Dossier -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5 mb-5">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1.5 rounded-pill mb-1">
                    Form 19 Guide
                </span>
                <h3 class="fw-extrabold text-navy mb-0">
                    <i class="bi bi-mortarboard-fill text-success"></i> Form 19 Voter Eligibility &amp; Teacher Enrollment Rules
                </h3>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill fw-bold btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm">
                    <i class="bi bi-pencil-square"></i> Apply Online (tcgconline)
                </a>
                <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning text-dark rounded-pill fw-bold btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-search"></i> Search Council Roll
                </a>
                <a href="https://ceo.bihar.gov.in/GCTCPDFVIEW/TCPDF.ASPX" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger rounded-pill fw-bold btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-pdf"></i> CEO PDF Roll
                </a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="info-step-card h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            1
                        </div>
                        <h5 class="fw-bold text-navy mb-0">Who Can Register as a Teacher Voter?</h5>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>Citizenship &amp; Residence:</strong> Indian citizen ordinarily resident in the constituency.</li>
                        <li><strong>3-Year Teaching Experience:</strong> Must have been engaged in teaching for at least <strong>3 years within the preceding 6 years</strong> in recognized educational institutions within Bihar.</li>
                        <li><strong>Eligible Educational Institutions:</strong> Secondary Schools (High Schools), Higher Secondary (+2) Schools, Intermediate Colleges, Degree Colleges, Government &amp; Constituent Universities, and Technical Institutions designated by the State Government.</li>
                        <li><strong>Primary Teachers:</strong> Teachers of elementary or middle schools (below secondary level) are not eligible as per Article 171(3)(b).</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="info-step-card h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            2
                        </div>
                        <h5 class="fw-bold text-navy mb-0">Form 19 Application &amp; Employment Certificate</h5>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>Statutory Application:</strong> Apply online via CEO Bihar portal (<a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgconline</a>) or ECI (<a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>), or offline with Form 19.</li>
                        <li><strong>Service Verification Certificate:</strong> Must attach a signed and stamped Certificate of Employment / Service from the Head of the Educational Institution (Principal, Headmaster, Registrar, or Dean) certifying the required 3-year tenure.</li>
                        <li><strong>Elector Search &amp; Verification:</strong> Verify your existing registration status using <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgcelectorsearch</a>.</li>
                        <li><strong>De-novo Preparation:</strong> A fresh electoral roll is compiled prior to every biennial election. Existing registration in general assembly rolls does not automatically confer voting rights in council elections.</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Constitutional Provisions & STV System -->
        <div class="mt-4 pt-4 border-top">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h5 class="fw-bold text-navy mb-2">Constitutional Mandate &amp; PR-STV Ballot System</h5>
                    <p class="text-muted small mb-2">
                        Under <strong>Article 171(3)(b)</strong> of the Constitution of India, 1/12th of the total members of the Legislative Council (6 seats in Bihar) are elected by the electorate consisting of secondary school, collegiate, and university teachers.
                    </p>
                    <p class="text-muted small mb-0">
                        The voting process follows the <strong>Single Transferable Vote (STV)</strong> preferential voting system on paper ballot. Voters mark preferences (1, 2, 3...) next to candidate names using the official violet sketch pen.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="p-3.5 bg-light rounded-4 border text-start">
                        <div class="fw-bold text-navy small mb-1"><i class="bi bi-award text-success me-1"></i> Purpose of Teachers' Quota:</div>
                        <p class="text-muted extra-small mb-0">
                            Ensures direct legislative voice for teachers, professors, educational infrastructure reforms, service conditions, pension entitlements, and higher education policy inside the State Parliament.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Navigation Links -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-primary"><i class="bi bi-mortarboard-fill"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">Graduates' Constituencies</h6>
                        <small class="text-muted">Explore 6 Graduates MLC seats</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-danger"><i class="bi bi-broadcast"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">2026 Biennial Election Hub</h6>
                        <small class="text-muted">Live Gazette, Schedule &amp; ROs</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/mlc" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-info"><i class="bi bi-building"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">75 MLCs Directory</h6>
                        <small class="text-muted">Full Council Members Roster</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<?php require_once __DIR__ . '/footer.php'; ?>
