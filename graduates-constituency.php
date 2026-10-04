<?php
/**
 * BiharElection.com - Bihar Legislative Council (Vidhan Parishad)
 * Complete Guide to 6 Graduates' Constituencies (स्नातक निर्वाचन क्षेत्र)
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Bihar Graduates Constituencies (6 Seats): Vidhan Parishad MLC Electoral Guide, Voter Form 18 & 2026 Election';
$pageDescription = 'Comprehensive directory and electoral guide to all 6 Graduates Constituencies of Bihar Legislative Council (Vidhan Parishad): Patna, Tirhut, Darbhanga, Kosi, Gaya, and Saran with Form 18 registration rules, districts mapping and 2026 Biennial Election updates.';
$pageKeywords = 'Bihar Graduates Constituency, Bihar Vidhan Parishad Graduate MLC, Patna Graduates Constituency, Tirhut Graduates, Darbhanga Graduates, Kosi Graduates, Gaya Graduates, Saran Graduates, Form 18 Bihar, Article 171 Constitution';
$pageCanonical = SITE_URL . '/graduates-constituency';
$activeNav = 'mlc';

// 6 Graduates Constituencies Data
$graduatesSeats = [
    [
        'id' => 'patna-graduates',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'hq' => 'Patna',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Neeraj Kumar (JD-U) / Vacant for 2026 Poll',
        'desc' => 'Encompasses the state capital region along with Nalanda and Nawada districts with high concentration of university graduates and civil services aspirants.'
    ],
    [
        'id' => 'tirhut-graduates',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'hq' => 'Muzaffarpur',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Devesh Chandra Thakur (JD-U) / Vacant for 2026 Poll',
        'desc' => 'Covers the central-north region of Tirhut division across 4 major districts centered around BRABU university hub in Muzaffarpur.'
    ],
    [
        'id' => 'darbhanga-graduates',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'hq' => 'Darbhanga',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Sarvesh Kumar (Independent) / Vacant for 2026 Poll',
        'desc' => 'Encompasses the Mithila heartland including LNMU Darbhanga and agricultural-industrial belt across Samastipur and Begusarai.'
    ],
    [
        'id' => 'kosi-graduates',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'hq' => 'Saharsa / Purnia',
        'ro' => 'Divisional Commissioner, Kosi / Purnia',
        'ro_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया',
        'districts' => ['Saharsa', 'Supaul', 'Madhepura', 'Purnia', 'Araria', 'Kishanganj', 'Katihar', 'Bhagalpur', 'Banka', 'Munger', 'Jamui', 'Lakhisarai', 'Sheikhpura', 'Khagaria'],
        'districts_hi' => ['सहरसा', 'सुपौल', 'मधेपुरा', 'पूर्णिया', 'अररिया', 'किशनगंज', 'कटिहार', 'भागलपुर', 'बांका', 'मुंगेर', 'जमुई', 'लखीसराय', 'शेखपुरा', 'खगड़िया'],
        'status' => 'election_2026',
        'status_label' => 'Biennial Election 2026 Active',
        'poll_date' => '23 October 2026',
        'result_date' => '27 October 2026',
        'incumbent' => 'Dr. N. K. Yadav (BJP) / Vacant for 2026 Poll',
        'desc' => 'The largest constituency by geographical expanse covering 14 districts across Kosi, Purnia, Bhagalpur, and Munger divisions (BNMU, TMBU, Purnea University).'
    ],
    [
        'id' => 'gaya-graduates',
        'name' => 'Gaya Graduates',
        'name_hi' => 'गया स्नातक निर्वाचन क्षेत्र',
        'hq' => 'Gaya',
        'ro' => 'Divisional Commissioner, Magadh (Gaya)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, मगध (गया)',
        'districts' => ['Gaya', 'Jehanabad', 'Arwal', 'Aurangabad', 'Rohtas', 'Kaimur', 'Bhojpur', 'Buxar'],
        'districts_hi' => ['गया', 'जहानाबाद', 'अरवल', 'औरंगाबाद', 'रोहतास', 'कैमूर', 'भोजपुर', 'बक्सर'],
        'status' => 'active_term',
        'status_label' => 'Active Sitting Member',
        'poll_date' => 'Next in 2029 Cycle',
        'result_date' => '-',
        'incumbent' => 'Awadhesh Narain Singh (BJP)',
        'desc' => 'Covers South Bihar and Shahabad region comprising 8 districts anchored around Magadh University and VKSU Ara.'
    ],
    [
        'id' => 'saran-graduates',
        'name' => 'Saran Graduates',
        'name_hi' => 'सारण स्नातक निर्वाचन क्षेत्र',
        'hq' => 'Chapra',
        'ro' => 'Divisional Commissioner, Saran (Chapra)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'districts' => ['Saran', 'Siwan', 'Gopalganj', 'East Champaran', 'West Champaran'],
        'districts_hi' => ['सारण', 'सिवान', 'गोपालगंज', 'पूर्वी चंपारण', 'पश्चिमी चंपारण'],
        'status' => 'active_term',
        'status_label' => 'Active Sitting Member',
        'poll_date' => 'Next in 2029 Cycle',
        'result_date' => '-',
        'incumbent' => 'Birendra Narayan Yadav (JD-U)',
        'desc' => 'Encompasses Saran division and the Champaran belt across 5 districts along Gandak river with Jai Prakash University Chapra.'
    ]
];

require_once __DIR__ . '/header.php';
?>

<style>
.grad-hero {
    background: linear-gradient(135deg, #091e3a 0%, #1e3a8a 50%, #0f172a 100%);
    color: #fff;
    padding: 60px 0 50px;
    position: relative;
    overflow: hidden;
}
.grad-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, rgba(255,255,255,0) 70%);
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
    flex-column: column;
    justify-content: space-between;
}
.constituency-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 16px 36px -6px rgba(37, 99, 235, 0.16);
    border-color: #3b82f6;
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
    border-color: #93c5fd;
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
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
<section class="grad-hero">
    <div class="container text-start position-relative">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                🎓 Article 171(3)(a) &bull; Constitution of India
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                6 Total Graduates' Seats
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
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Graduates' Constituencies</li>
            </ol>
        </nav>

        <h1 class="display-5 fw-extrabold text-white mb-2">
            Bihar Graduates' Constituencies (स्नातक निर्वाचन क्षेत्र) <br>
            <span style="color: var(--accent-saffron);">6 Legislative Council (Vidhan Parishad) Seats</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.1rem; max-width: 880px;">
            Comprehensive guide to the 6 specialized Graduates' Constituencies of the Bihar Legislative Council. Learn about constitutional provisions, Form 18 voter registration eligibility, district mappings, and live updates on the ongoing 2026 Biennial Elections.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-danger fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-broadcast"></i> 2026 Biennial Election Hub
            </a>
            <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="btn btn-success fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-book"></i> 6 Teachers' Constituencies
            </a>
            <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-outline-light fw-bold px-3.5 py-2.5 shadow-sm">
                <i class="bi bi-journal-text me-1"></i> 75 MLCs Directory
            </a>
        </div>
    </div>
</section>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Active 2026 Election Notice for Graduates -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 text-white overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #1d4ed8 100%);">
        <div class="p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill text-uppercase">2026 Biennial Election</span>
                        <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-2.5 py-1 rounded-pill">Gazette: TCGCGajat2026</span>
                    </div>
                    <h4 class="fw-extrabold text-white mb-1">4 Graduates' Constituencies Undergoing Polls in 2026</h4>
                    <p class="text-white-50 mb-0 small" style="max-width: 820px;">
                        Elections are currently underway for <strong>Patna</strong>, <strong>Tirhut</strong>, <strong>Darbhanga</strong>, and <strong>Kosi</strong> Graduates' Constituencies due to tenure expiry. Polling on <strong>23 October 2026</strong>, Counting on <strong>27 October 2026</strong>.
                    </p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-warning fw-bold text-dark rounded-pill px-4 py-2.5 shadow-sm text-nowrap d-inline-flex align-items-center gap-2">
                <span>View Full Election Hub</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- 6 Graduates' Constituencies Grid -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="fw-extrabold text-navy mb-1">
                <i class="bi bi-mortarboard text-primary me-2"></i> All 6 Bihar Graduates' Constituencies
            </h3>
            <p class="text-muted small mb-0">Covering all 38 districts of Bihar categorized into 6 divisional electoral regions</p>
        </div>
    </div>

    <div class="row g-4 mb-5">
        <?php foreach ($graduatesSeats as $seat): 
            $isOngoing = ($seat['status'] === 'election_2026');
        ?>
            <div class="col-md-6 col-lg-4" id="<?php echo $seat['id']; ?>">
                <div class="constituency-card p-4">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill extra-small fw-bold">
                                🎓 Graduates Quota
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
                            <div class="d-flex justify-content-between">
                                <span class="text-muted extra-small">Returning Officer:</span>
                                <span class="text-navy fw-semibold extra-small text-end" style="max-width: 170px;"><?php echo htmlspecialchars($seat['ro']); ?></span>
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
                        <?php if ($isOngoing): ?>
                            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election#<?php echo $seat['id']; ?>" class="btn btn-sm btn-danger w-100 rounded-pill fw-semibold py-1.5 shadow-sm">
                                <i class="bi bi-box-arrow-up-right me-1"></i> 2026 Election Schedule &amp; Candidates
                            </a>
                        <?php else: ?>
                            <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-semibold py-1.5">
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
        <h3 class="fw-extrabold text-navy mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-file-earmark-check-fill text-primary"></i> Form 18 Voter Eligibility &amp; Enrollment Guide
        </h3>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="info-step-card h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            1
                        </div>
                        <h5 class="fw-bold text-navy mb-0">Who Can Register as a Graduate Voter?</h5>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>Citizenship:</strong> Must be a Citizen of India.</li>
                        <li><strong>Ordinary Residence:</strong> Must be ordinarily resident within the specific Graduates' constituency boundary.</li>
                        <li><strong>3-Year Qualifying Graduation:</strong> Must have graduated from any recognized University in India at least 3 years before the qualifying date (e.g. 1st November of the qualifying year), OR possess an equivalent qualification deemed recognized by the state.</li>
                        <li><strong>De-novo Electoral Roll:</strong> Unlike regular Assembly/Lok Sabha voter lists, the electoral rolls for Legislative Council Graduates' constituencies are <em>prepared afresh</em> before each biennial election cycle. Prior registration does not carry over automatically.</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="info-step-card h-100">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-4 d-flex align-items-center justify-content-center" style="width: 46px; height: 46px;">
                            2
                        </div>
                        <h5 class="fw-bold text-navy mb-0">How to Apply with Form 18 (Online &amp; Offline)</h5>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>Online Application:</strong> Submit online via the Election Commission portal (<a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>) or Chief Electoral Officer Bihar portal.</li>
                        <li><strong>Offline Application:</strong> Fill statutory <strong>Form 18</strong> and submit to the designated Electoral Registration Officer (ERO) / Assistant ERO / Block Development Officer (BDO) / Sub-Divisional Magistrate (SDM).</li>
                        <li><strong>Required Documents:</strong>
                            <ul>
                                <li>Self-attested copy of Degree Certificate or Marksheet.</li>
                                <li>Proof of ordinary residence (Aadhaar, EPIC, Passport, Electricity Bill).</li>
                                <li>Recent passport-sized photograph.</li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Constitutional Provisions & STV System -->
        <div class="mt-4 pt-4 border-top">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h5 class="fw-bold text-navy mb-2">Constitutional Framework &amp; Single Transferable Vote (PR-STV)</h5>
                    <p class="text-muted small mb-2">
                        Under <strong>Article 171(3)(a)</strong> of the Constitution of India, 1/12th of the total members of the Legislative Council (6 out of 75 seats in Bihar) are elected by persons who have held a degree for at least three years.
                    </p>
                    <p class="text-muted small mb-0">
                        Voting is conducted via paper ballot through the <strong>Proportional Representation by means of Single Transferable Vote (PR-STV)</strong>. Electors mark preferences (1, 2, 3...) next to candidate names using the officially provided violet sketch pen.
                    </p>
                </div>
                <div class="col-lg-5 text-lg-end">
                    <div class="p-3.5 bg-light rounded-4 border text-start">
                        <div class="fw-bold text-navy small mb-1"><i class="bi bi-calculator text-primary me-1"></i> Quota Threshold Formula:</div>
                        <code class="d-block p-2 bg-white rounded border text-danger fw-bold text-center mb-2 font-monospace">
                            Quota = [ (Total Valid Votes / (Seats + 1)) + 1 ]
                        </code>
                        <small class="text-muted extra-small d-block">
                            For a single-member vacancy, a candidate securing more than 50% of the valid value of votes (50% + 1) is declared elected on count or elimination rounds.
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Navigation Links -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-success"><i class="bi bi-book-half"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">Teachers' Constituencies</h6>
                        <small class="text-muted">Explore 6 Teachers MLC seats</small>
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
                    <div class="fs-2 text-primary"><i class="bi bi-building"></i></div>
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
