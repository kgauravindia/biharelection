<?php
/**
 * BiharElection.com - Bihar Legislative Council (Vidhan Parishad) Biennial Election 2026
 * Dedicated hub for the ongoing 4 Graduates' & 4 Teachers' Constituencies Biennial Elections.
 * Reference: CEO Bihar Notification No. M2–03(TC&GC)/2026-5369 / ECI TCGCGajat2026.pdf
 */
require_once __DIR__ . '/config.php';

$pageTitle = 'Bihar Vidhan Parishad Election 2026: 8 Graduates & Teachers Seats Gazette, Schedule & Voter Guide';
$pageDescription = 'Official schedule, gazette notification (TCGCGajat2026.pdf), district-wise constituency mapping, Form 18/19 eligibility and polling data for the Bihar Legislative Council Biennial Election 2026.';
$pageKeywords = 'Bihar Vidhan Parishad Election 2026, Bihar Legislative Council Election, Graduates Constituency Bihar, Teachers Constituency Bihar, Patna Graduate MLC, Tirhut Graduate MLC, Darbhanga Graduate, Kosi Graduate, Saran Teacher, Form 18 Bihar, Form 19 Bihar, CEO Bihar TCGCGajat2026';
$pageCanonical = SITE_URL . '/vidhan-parishad-election';
$activeNav = 'mlc';

include 'header.php';

// 8 Constituencies Data
$seats = [
    [
        'id' => 'patna-graduates',
        'type' => 'Graduates',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'hq' => 'Patna',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'Covers the state capital Patna along with Nalanda and Nawada districts.'
    ],
    [
        'id' => 'tirhut-graduates',
        'type' => 'Graduates',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'hq' => 'Muzaffarpur',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'Covers the central-north region of Tirhut division across 4 major districts.'
    ],
    [
        'id' => 'darbhanga-graduates',
        'type' => 'Graduates',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'hq' => 'Darbhanga',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'Encompasses the Mithila heartland including Darbhanga, Madhubani, Samastipur, and Begusarai.'
    ],
    [
        'id' => 'kosi-graduates',
        'type' => 'Graduates',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Kosi / Purnia',
        'ro_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया',
        'hq' => 'Saharsa / Purnia',
        'districts' => ['Saharsa', 'Supaul', 'Madhepura', 'Purnia', 'Araria', 'Kishanganj', 'Katihar', 'Bhagalpur', 'Banka', 'Munger', 'Jamui', 'Lakhisarai', 'Sheikhpura', 'Khagaria'],
        'districts_hi' => ['सहरसा', 'सुपौल', 'मधेपुरा', 'पूर्णिया', 'अररिया', 'किशनगंज', 'कटिहार', 'भागलपुर', 'बांका', 'मुंगेर', 'जमुई', 'लखीसराय', 'शेखपुरा', 'खगड़िया'],
        'color' => 'primary',
        'term_expiry' => '16 November 2026',
        'description' => 'The largest constituency by geographical expanse covering 14 districts across Kosi, Purnia, Bhagalpur, and Munger divisions.'
    ],
    [
        'id' => 'patna-teachers',
        'type' => 'Teachers',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना',
        'hq' => 'Patna',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Representing secondary and collegiate educators across Patna, Nalanda, and Nawada.'
    ],
    [
        'id' => 'tirhut-teachers',
        'type' => 'Teachers',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'hq' => 'Muzaffarpur',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Representing teaching faculty across secondary schools and colleges in Tirhut division.'
    ],
    [
        'id' => 'darbhanga-teachers',
        'type' => 'Teachers',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा',
        'hq' => 'Darbhanga',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Covering the educator community in Mithila region (Darbhanga, Madhubani, Samastipur, Begusarai).'
    ],
    [
        'id' => 'saran-teachers',
        'type' => 'Teachers',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'ro' => 'Divisional Commissioner, Saran (Chapra)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'hq' => 'Chapra',
        'districts' => ['Saran', 'Siwan', 'Gopalganj', 'East Champaran', 'West Champaran'],
        'districts_hi' => ['सारण (छपरा)', 'सिवान', 'गोपालगंज', 'पूर्वी चंपारण (मोतिहारी)', 'पश्चिमी चंपारण (बेतिया)'],
        'color' => 'success',
        'term_expiry' => '16 November 2026',
        'description' => 'Encompassing teachers and professors in Saran and Champaran belt (Chapra, Siwan, Gopalganj, Motihari, Bettiah).'
    ]
];

// Schedule Timeline Events
$timeline = [
    [
        'step' => '1',
        'date' => '29 September 2026',
        'day' => 'Tuesday',
        'title' => 'Issue of Gazette Notification',
        'title_hi' => 'निर्वाचन की अधिसूचना जारी',
        'status' => 'completed',
        'desc' => 'Statutory notification published in the Official Bihar Gazette (Notification No. M2–03(TC&GC)/2026-5369).'
    ],
    [
        'step' => '2',
        'date' => '06 October 2026',
        'day' => 'Tuesday',
        'title' => 'Last Date for Making Nominations',
        'title_hi' => 'नामनिर्देशन करने की अंतिम तिथि',
        'status' => 'current',
        'desc' => 'Candidates submit nomination papers along with required security deposit to the designated Returning Officer by 3:00 PM.'
    ],
    [
        'step' => '3',
        'date' => '07 October 2026',
        'day' => 'Wednesday',
        'title' => 'Scrutiny of Nominations',
        'title_hi' => 'नामनिर्देशनों की संवीक्षा',
        'status' => 'upcoming',
        'desc' => 'Returning Officers examine validity of submitted nomination papers.'
    ],
    [
        'step' => '4',
        'date' => '09 October 2026',
        'day' => 'Friday',
        'title' => 'Last Date for Withdrawal of Candidatures',
        'title_hi' => 'उम्मीदवारी वापस लेने की अंतिम तिथि',
        'status' => 'upcoming',
        'desc' => 'Final list of contesting candidates (Form 7B) published with allotted ballot positions.'
    ],
    [
        'step' => '5',
        'date' => '23 October 2026',
        'day' => 'Friday',
        'title' => 'Date of Poll (Voting Day)',
        'title_hi' => 'मतदान की तिथि (सुबह 8:00 से शाम 4:00)',
        'status' => 'upcoming',
        'desc' => 'Polling held across designated polling stations via ballot paper (PR-STV system). Voting hours: 8:00 AM to 4:00 PM.'
    ],
    [
        'step' => '6',
        'date' => '27 October 2026',
        'day' => 'Tuesday',
        'title' => 'Counting of Votes & Declaration of Results',
        'title_hi' => 'मतगणना एवं परिणाम घोषणा',
        'status' => 'upcoming',
        'desc' => 'Counting of preferential votes and quota calculation conducted at divisional headquarters.'
    ],
    [
        'step' => '7',
        'date' => '31 October 2026',
        'day' => 'Saturday',
        'title' => 'Completion of Election Process',
        'title_hi' => 'निर्वाचन प्रक्रिया पूर्ण होने की तिथि',
        'status' => 'upcoming',
        'desc' => 'Formal completion of election process before the term of sitting MLCs expires on 16 November 2026.'
    ]
];
?>

<style>
/* Vidhan Parishad Election Hub Custom Styles */
.hero-parishad {
    background: linear-gradient(135deg, #091e3a 0%, #1e3a8a 50%, #0f172a 100%);
    color: #fff;
    padding: 60px 0 50px;
    position: relative;
    overflow: hidden;
}
.hero-parishad::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 500px;
    height: 500px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.timeline-card {
    position: relative;
    border-left: 3px solid #cbd5e1;
    padding-left: 28px;
    padding-bottom: 30px;
    transition: all 0.2s ease;
}
.timeline-card:last-child {
    border-left-color: transparent;
    padding-bottom: 0;
}
.timeline-badge {
    position: absolute;
    left: -17px;
    top: 0;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #fff;
    border: 3px solid #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 13px;
    color: #475569;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}
.timeline-card.active .timeline-badge {
    background: #2563eb;
    border-color: #93c5fd;
    color: #fff;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.2);
}
.timeline-card.completed .timeline-badge {
    background: #16a34a;
    border-color: #86efac;
    color: #fff;
}
.countdown-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 12px;
    padding: 14px 20px;
    text-align: center;
}
.countdown-num {
    font-size: 2rem;
    font-weight: 800;
    font-family: 'Outfit', sans-serif;
    line-height: 1;
    color: #fbbf24;
}
.constituency-card {
    border-radius: 14px;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e2e8f0;
}
.constituency-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px -10px rgba(0, 0, 0, 0.12);
    border-color: #94a3b8;
}
.district-tag {
    background: #f1f5f9;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
    display: inline-block;
    transition: all 0.15s ease;
}
.district-tag:hover {
    background: #e2e8f0;
}
.gazette-btn {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: #000;
    font-weight: 700;
    border: none;
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.35);
    transition: all 0.2s ease;
}
.gazette-btn:hover {
    background: linear-gradient(135deg, #fbbf24 0%, #b45309 100%);
    color: #000;
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.45);
}
</style>

<!-- ========================================================================= -->
<!-- 1. HERO SECTION & LIVE COUNTDOWN -->
<!-- ========================================================================= -->
<section class="hero-parishad shadow-sm">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-8">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                        <i class="bi bi-broadcast me-1 text-danger"></i> LIVE ELECTION NOTIFICATION
                    </span>
                    <span class="badge bg-light text-dark px-3 py-1.5 rounded-pill fw-semibold">
                        Gazette Ref: M2–03(TC&GC)/2026-5369
                    </span>
                    <span class="badge bg-danger text-white px-3 py-1.5 rounded-pill fw-semibold">
                        8 Seats (4 Graduates + 4 Teachers)
                    </span>
                </div>

                <h1 class="display-6 fw-bold mb-2" style="font-family: 'Outfit', sans-serif;">
                    Bihar Vidhan Parishad Biennial Election 2026
                </h1>
                <h2 class="h5 fw-normal text-light opacity-90 mb-3" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                    बिहार विधान परिषद् द्विवार्षिक निर्वाचन 2026 — 4 स्नातक एवं 4 शिक्षक निर्वाचन क्षेत्र
                </h2>
                
                <p class="lead fs-6 text-light opacity-90 mb-4" style="max-width: 720px; line-height: 1.6;">
                    Official election timeline, gazette notifications, constituency coverage across all 38 Bihar districts, Returning Officers, and voter guidelines for the 2026 Legislative Council elections.
                </p>

                <div class="d-flex flex-wrap gap-2 pt-1">
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" rel="noopener" class="btn gazette-btn px-4 py-2.5 rounded-3 fw-bold">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> View Official Gazette PDF (TCGCGajat2026.pdf) &rarr;
                    </a>
                    <a href="#schedule" class="btn btn-outline-light px-3 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-calendar-event me-1"></i> Full Schedule
                    </a>
                    <a href="#district-finder" class="btn btn-outline-warning px-3 py-2.5 rounded-3 fw-semibold">
                        <i class="bi bi-search me-1"></i> Find Your Constituency
                    </a>
                </div>
            </div>

            <!-- Countdown Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="background: rgba(15, 23, 42, 0.85); backdrop-filter: blur(12px); border: 1px solid rgba(255,255,255,0.15);">
                    <div class="card-header bg-transparent border-bottom border-secondary border-opacity-25 py-3 text-center">
                        <h6 class="text-uppercase small fw-bold text-warning mb-0">
                            <i class="bi bi-stopwatch me-1"></i> Countdown to Polling Day
                        </h6>
                        <span class="text-white small fw-bold">Friday, 23 October 2026 (8 AM – 4 PM)</span>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="row g-2 text-center" id="pollingCountdown">
                            <div class="col-3">
                                <div class="countdown-box">
                                    <div class="countdown-num" id="cdDays">--</div>
                                    <small class="text-light opacity-75 d-block text-uppercase" style="font-size: 10px;">Days</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="countdown-box">
                                    <div class="countdown-num" id="cdHours">--</div>
                                    <small class="text-light opacity-75 d-block text-uppercase" style="font-size: 10px;">Hours</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="countdown-box">
                                    <div class="countdown-num" id="cdMinutes">--</div>
                                    <small class="text-light opacity-75 d-block text-uppercase" style="font-size: 10px;">Mins</small>
                                </div>
                            </div>
                            <div class="col-3">
                                <div class="countdown-box">
                                    <div class="countdown-num" id="cdSeconds">--</div>
                                    <small class="text-light opacity-75 d-block text-uppercase" style="font-size: 10px;">Secs</small>
                                </div>
                            </div>
                        </div>

                        <hr class="border-secondary border-opacity-25 my-3">

                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90">
                            <div>
                                <i class="bi bi-calendar-check text-success me-1"></i> <strong>Counting Day:</strong>
                            </div>
                            <span class="badge bg-success-subtle text-success fw-bold px-2 py-1">27 Oct 2026</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center text-start small text-light opacity-90 mt-2">
                            <div>
                                <i class="bi bi-hourglass-bottom text-warning me-1"></i> <strong>Term Expiry of 8 MLCs:</strong>
                            </div>
                            <span class="text-light">16 Nov 2026</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ========================================================================= -->
<!-- 2. QUICK STATS OVERVIEW -->
<!-- ========================================================================= -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-primary fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">8</div>
                    <div class="fw-semibold text-dark small">Total MLC Seats</div>
                    <small class="text-muted">4 Graduates + 4 Teachers</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-success fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">38</div>
                    <div class="fw-semibold text-dark small">Districts Covered</div>
                    <small class="text-muted">100% of Bihar State</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-warning fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">5</div>
                    <div class="fw-semibold text-dark small">Divisional Commissioners</div>
                    <small class="text-muted">Designated Returning Officers</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="bg-white p-3 rounded-3 shadow-sm border h-100">
                    <div class="text-danger fw-bold display-6 mb-1" style="font-family: 'Outfit', sans-serif;">STV</div>
                    <div class="fw-semibold text-dark small">Voting System</div>
                    <small class="text-muted">Single Transferable Vote</small>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container py-5">
    <div class="row g-5">
        <!-- Main Content Area (8 Cols) -->
        <div class="col-lg-8">

            <!-- ============================================================= -->
            <!-- 3. OFFICIAL ELECTION SCHEDULE & TIMELINE -->
            <!-- ============================================================= -->
            <div id="schedule" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase px-2.5 py-1 mb-1">Official ECI Schedule</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Election Schedule &amp; Key Deadlines
                        </h3>
                    </div>
                    <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" class="btn btn-outline-danger btn-sm fw-semibold">
                        <i class="bi bi-filetype-pdf me-1"></i> Official Notification
                    </a>
                </div>

                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <div class="timeline-container">
                        <?php foreach ($timeline as $t): ?>
                            <div class="timeline-card <?php echo $t['status']; ?>">
                                <div class="timeline-badge">
                                    <?php if ($t['status'] === 'completed'): ?>
                                        <i class="bi bi-check-lg"></i>
                                    <?php else: ?>
                                        <?php echo $t['step']; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-1">
                                    <h5 class="fw-bold mb-0 text-dark">
                                        <?php echo htmlspecialchars($t['title']); ?>
                                    </h5>
                                    <span class="badge <?php echo $t['status'] === 'completed' ? 'bg-success' : ($t['status'] === 'current' ? 'bg-primary' : 'bg-light text-dark border'); ?> fw-bold px-2.5 py-1">
                                        <?php echo htmlspecialchars($t['date']); ?> (<?php echo htmlspecialchars($t['day']); ?>)
                                    </span>
                                </div>
                                <div class="text-muted small fw-semibold mb-1" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                    <?php echo htmlspecialchars($t['title_hi']); ?>
                                </div>
                                <p class="text-secondary small mb-0">
                                    <?php echo htmlspecialchars($t['desc']); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 4. ALL 8 CONSTITUENCIES DETAILS -->
            <!-- ============================================================= -->
            <div id="constituencies" class="mb-5">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                    <div>
                        <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-2.5 py-1 mb-1">8 Seats to Poll</span>
                        <h3 class="fw-bold mb-0 text-dark" style="font-family: 'Outfit', sans-serif;">
                            Constituencies Going to Polls
                        </h3>
                    </div>
                    <div class="btn-group btn-group-sm" role="group">
                        <button type="button" class="btn btn-outline-dark active fw-semibold" onclick="filterSeats('all', this)">All (8)</button>
                        <button type="button" class="btn btn-outline-primary fw-semibold" onclick="filterSeats('Graduates', this)">Graduates (4)</button>
                        <button type="button" class="btn btn-outline-success fw-semibold" onclick="filterSeats('Teachers', this)">Teachers (4)</button>
                    </div>
                </div>

                <div class="row g-3" id="seatsGrid">
                    <?php foreach ($seats as $seat): ?>
                        <div class="col-md-6 seat-card-item" data-type="<?php echo $seat['type']; ?>" data-districts="<?php echo htmlspecialchars(implode(' ', $seat['districts'])); ?>">
                            <div class="card h-100 constituency-card shadow-sm p-3 bg-white">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge <?php echo $seat['type'] === 'Graduates' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success'; ?> fw-bold px-2.5 py-1 rounded-pill">
                                        <i class="bi <?php echo $seat['type'] === 'Graduates' ? 'bi-mortarboard-fill' : 'bi-person-video3'; ?> me-1"></i>
                                        <?php echo $seat['type']; ?> Quota
                                    </span>
                                    <span class="small text-muted font-monospace"><i class="bi bi-clock me-1"></i> Term Expiry: Nov 2026</span>
                                </div>

                                <h5 class="fw-bold text-dark mb-1">
                                    <?php echo htmlspecialchars($seat['name']); ?>
                                </h5>
                                <div class="text-muted small mb-2 fw-semibold" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                    <?php echo htmlspecialchars($seat['name_hi']); ?>
                                </div>

                                <p class="text-secondary small mb-3">
                                    <?php echo htmlspecialchars($seat['description']); ?>
                                </p>

                                <div class="bg-light p-2.5 rounded-3 mb-3 small">
                                    <div class="mb-1">
                                        <strong class="text-dark"><i class="bi bi-building me-1 text-secondary"></i> Returning Officer:</strong>
                                        <div class="text-muted"><?php echo htmlspecialchars($seat['ro']); ?></div>
                                    </div>
                                    <div>
                                        <strong class="text-dark"><i class="bi bi-geo-alt me-1 text-danger"></i> HQ:</strong> <?php echo htmlspecialchars($seat['hq']); ?>
                                    </div>
                                </div>

                                <div class="mt-auto">
                                    <div class="small fw-bold text-muted mb-1.5 text-uppercase" style="font-size: 11px;">
                                        <i class="bi bi-map me-1"></i> Districts in this Constituency (<?php echo count($seat['districts']); ?>):
                                    </div>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($seat['districts'] as $dist): ?>
                                            <span class="district-tag"><?php echo htmlspecialchars($dist); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 5. INTERACTIVE DISTRICT LOOKUP FINDER -->
            <!-- ============================================================= -->
            <div id="district-finder" class="mb-5">
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                    <div class="card-body p-4 p-md-5">
                        <div class="row align-items-center g-4">
                            <div class="col-md-6">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill">VOTER UTILITY</span>
                                <h3 class="fw-bold mb-2 text-white" style="font-family: 'Outfit', sans-serif;">
                                    Find Your Vidhan Parishad Constituency
                                </h3>
                                <p class="text-light opacity-90 small mb-0">
                                    Select or type your district to discover which <strong>Graduates'</strong> and <strong>Teachers'</strong> constituencies you belong to and who your Returning Officer is.
                                </p>
                            </div>
                            <div class="col-md-6">
                                <div class="bg-white p-3 rounded-4 shadow text-dark">
                                    <label class="form-label fw-bold small text-muted text-uppercase mb-1">Select Your District (38 Districts):</label>
                                    <select class="form-select form-select-lg mb-3 fw-semibold" id="districtSelect" onchange="lookupDistrictConstituency(this.value)">
                                        <option value="">-- Choose Your District --</option>
                                        <option value="Patna">Patna (पटना)</option>
                                        <option value="Nalanda">Nalanda (नालंदा)</option>
                                        <option value="Nawada">Nawada (नवादा)</option>
                                        <option value="Muzaffarpur">Muzaffarpur (मुजफ्फरपुर)</option>
                                        <option value="Vaishali">Vaishali (वैशाली)</option>
                                        <option value="Sitamarhi">Sitamarhi (सीतामढ़ी)</option>
                                        <option value="Sheohar">Sheohar (शिवहर)</option>
                                        <option value="Darbhanga">Darbhanga (दरभंगा)</option>
                                        <option value="Madhubani">Madhubani (मधुबनी)</option>
                                        <option value="Samastipur">Samastipur (समस्तीपुर)</option>
                                        <option value="Begusarai">Begusarai (बेगूसराय)</option>
                                        <option value="Saharsa">Saharsa (सहरसा)</option>
                                        <option value="Supaul">Supaul (सुपौल)</option>
                                        <option value="Madhepura">Madhepura (मधेपुरा)</option>
                                        <option value="Purnia">Purnia (पूर्णिया)</option>
                                        <option value="Araria">Araria (अररिया)</option>
                                        <option value="Kishanganj">Kishanganj (किशनगंज)</option>
                                        <option value="Katihar">Katihar (कटिहार)</option>
                                        <option value="Bhagalpur">Bhagalpur (भागलपुर)</option>
                                        <option value="Banka">Banka (बांका)</option>
                                        <option value="Munger">Munger (मुंगेर)</option>
                                        <option value="Jamui">Jamui (जमुई)</option>
                                        <option value="Lakhisarai">Lakhisarai (लखीसराय)</option>
                                        <option value="Sheikhpura">Sheikhpura (शेखपुरा)</option>
                                        <option value="Khagaria">Khagaria (खगड़िया)</option>
                                        <option value="Saran">Saran / Chapra (सारण / छपरा)</option>
                                        <option value="Siwan">Siwan (सिवान)</option>
                                        <option value="Gopalganj">Gopalganj (गोपालगंज)</option>
                                        <option value="East Champaran">East Champaran / Motihari (पूर्वी चंपारण)</option>
                                        <option value="West Champaran">West Champaran / Bettiah (पश्चिमी चंपारण)</option>
                                        <option value="Gaya">Gaya (गया)</option>
                                        <option value="Jehanabad">Jehanabad (जहानाबाद)</option>
                                        <option value="Arwal">Arwal (अरवल)</option>
                                        <option value="Aurangabad">Aurangabad (औरंगाबाद)</option>
                                        <option value="Rohtas">Rohtas / Sasaram (रोहतास)</option>
                                        <option value="Kaimur">Kaimur / Bhabhua (कैमूर)</option>
                                        <option value="Bhojpur">Bhojpur / Ara (भोजपुर)</option>
                                        <option value="Buxar">Buxar (बक्सर)</option>
                                    </select>

                                    <!-- Lookup Result Card -->
                                    <div id="lookupResultBox" class="p-3 bg-light rounded-3 border d-none">
                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                            <span class="fw-bold text-dark" id="resDistrictName">Patna District</span>
                                            <span class="badge bg-success">Active 2026 Election</span>
                                        </div>
                                        <div class="row g-2 small">
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded border">
                                                    <span class="text-primary fw-bold"><i class="bi bi-mortarboard-fill me-1"></i> Graduates Seat:</span>
                                                    <div class="fw-semibold text-dark" id="resGradSeat">-</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded border">
                                                    <span class="text-success fw-bold"><i class="bi bi-person-video3 me-1"></i> Teachers Seat:</span>
                                                    <div class="fw-semibold text-dark" id="resTeachSeat">-</div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="p-2 bg-white rounded border text-muted">
                                                    <strong>Returning Officer:</strong> <span id="resRO">-</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 6. VOTER REGISTRATION & FORM 18 / 19 RULES -->
            <!-- ============================================================= -->
            <div id="voter-guide" class="mb-5">
                <span class="badge bg-success-subtle text-success fw-bold text-uppercase px-2.5 py-1 mb-1">Electoral Roll Guide</span>
                <h3 class="fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                    Voter Eligibility &amp; Registration (Form 18 &amp; 19)
                </h3>

                <div class="row g-3">
                    <!-- Form 18 (Graduates) -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0 rounded-4 p-4 bg-white border-top border-primary border-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-mortarboard-fill fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Graduates Voter (Form 18)</h5>
                                    <small class="text-muted">स्नातक मतदाता पंजीकरण</small>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Eligibility Criteria:</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>Citizen of India ordinarily resident in the constituency.</li>
                                <li>Graduated at least <strong>3 years prior</strong> to qualifying date from a university recognized in India.</li>
                                <li>Or possesses equivalent educational qualification recognized by the state.</li>
                            </ul>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Required Documents:</h6>
                            <ul class="text-secondary small ps-3 mb-4" style="line-height: 1.6;">
                                <li>Degree Certificate / Provisional Degree / Final Marksheet.</li>
                                <li>EPIC (Voter ID) or Aadhaar Card as proof of identity.</li>
                                <li>Proof of ordinary residence in the constituency.</li>
                            </ul>

                            <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold mt-auto">
                                <i class="bi bi-download me-1"></i> Download Form 18
                            </a>
                        </div>
                    </div>

                    <!-- Form 19 (Teachers) -->
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0 rounded-4 p-4 bg-white border-top border-success border-4">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="bi bi-person-video3 fs-5"></i>
                                </div>
                                <div>
                                    <h5 class="fw-bold mb-0 text-dark">Teachers Voter (Form 19)</h5>
                                    <small class="text-muted">शिक्षक मतदाता पंजीकरण</small>
                                </div>
                            </div>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Eligibility Criteria:</h6>
                            <ul class="text-secondary small ps-3 mb-3" style="line-height: 1.6;">
                                <li>Citizen of India ordinarily resident in the constituency.</li>
                                <li>Engaged in teaching for a total of at least <strong>3 years within the preceding 6 years</strong>.</li>
                                <li>In educational institutions not lower in standard than a secondary school.</li>
                            </ul>

                            <h6 class="fw-bold text-dark small text-uppercase mb-2">Required Documents:</h6>
                            <ul class="text-secondary small ps-3 mb-4" style="line-height: 1.6;">
                                <li>Service Certificate issued by the Head of Institution.</li>
                                <li>Government recognition/affiliation document of institution.</li>
                                <li>EPIC or Aadhaar Card & proof of residence.</li>
                            </ul>

                            <a href="https://ceoelection.bihar.gov.in/" target="_blank" class="btn btn-outline-success btn-sm rounded-pill fw-semibold mt-auto">
                                <i class="bi bi-download me-1"></i> Download Form 19
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================= -->
            <!-- 7. VOTING METHOD: PREFERENTIAL PROPORTIONAL REPRESENTATION (STV) -->
            <!-- ============================================================= -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-5">
                <h4 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-ui-checks-grid text-warning me-2"></i> How Voting &amp; Counting Works (PR-STV)
                </h4>
                <p class="text-secondary small mb-3">
                    Elections to the Bihar Legislative Council from Graduates' and Teachers' constituencies are held under the system of <strong>Proportional Representation by means of the Single Transferable Vote (PR-STV)</strong> via paper ballots.
                </p>

                <div class="row g-3 small">
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-1-circle-fill text-primary me-1"></i> Preference Marking</h6>
                            <p class="text-muted mb-0">Voters mark their preferences (1, 2, 3...) next to candidate names in order of choice using Indian numerals (1, 2, 3) or Roman/Devanagari numerals.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-pen-fill text-danger me-1"></i> Violet Sketch Pen</h6>
                            <p class="text-muted mb-0">Preferences MUST be recorded only with the official violet sketch pen supplied by the polling officer. Any other pen invalidates the ballot.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 bg-light rounded-3 h-100 border">
                            <h6 class="fw-bold text-dark mb-1"><i class="bi bi-calculator-fill text-success me-1"></i> Winning Quota</h6>
                            <p class="text-muted mb-0">A candidate must secure the quota: <code>(Total Valid Votes / (Seats + 1)) + 1</code>. If no candidate reaches quota on 1st preferences, lowest candidates are eliminated and votes transferred.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Sidebar (4 Cols) -->
        <div class="col-lg-4">
            
            <!-- Gazette Notification Download Widget -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger me-2"></i> Official Gazette Document
                </h5>
                <p class="small text-secondary mb-3">
                    Download and inspect the formal statutory notification published by the Chief Electoral Officer, Bihar:
                </p>
                <div class="p-3 bg-light rounded-3 border mb-3 small">
                    <div><strong>Document:</strong> TCGCGajat2026.pdf</div>
                    <div><strong>File No:</strong> M2–03(TC&GC)/2026-5369</div>
                    <div><strong>Issue Date:</strong> 29 September 2026</div>
                    <div><strong>Authority:</strong> CEO Bihar / ECI</div>
                </div>
                <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/ImportantInstructionsAndLetters/TCGCGajat2026.pdf" target="_blank" rel="noopener" class="btn btn-danger w-100 fw-bold py-2 shadow-sm rounded-3">
                    <i class="bi bi-download me-1"></i> View / Download Gazette PDF
                </a>
            </div>

            <!-- Returning Officers Directory -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-person-badge-fill text-primary me-2"></i> Returning Officers (ROs)
                </h5>
                <div class="d-flex flex-column gap-3 small">
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Patna Division Commissioner</strong>
                        <div class="text-muted">Patna Graduates &amp; Patna Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Patna, Nalanda, Nawada</span>
                    </div>
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Tirhut Division Commissioner</strong>
                        <div class="text-muted">Tirhut Graduates &amp; Tirhut Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Muzaffarpur, Vaishali, Sitamarhi, Sheohar</span>
                    </div>
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Darbhanga Division Commissioner</strong>
                        <div class="text-muted">Darbhanga Graduates &amp; Darbhanga Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Darbhanga, Madhubani, Samastipur, Begusarai</span>
                    </div>
                    <div class="border-bottom pb-2">
                        <strong class="text-dark">Kosi / Purnia Division Commissioner</strong>
                        <div class="text-muted">Kosi Graduates</div>
                        <span class="badge bg-light text-dark border mt-1">14 Eastern Bihar Districts</span>
                    </div>
                    <div>
                        <strong class="text-dark">Saran Division Commissioner</strong>
                        <div class="text-muted">Saran Teachers</div>
                        <span class="badge bg-light text-dark border mt-1">Saran, Siwan, Gopalganj, Champaran</span>
                    </div>
                </div>
            </div>

            <!-- All 75 MLCs Directory Link -->
            <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-people-fill text-warning me-2"></i> Bihar Legislative Council
                </h5>
                <p class="small text-secondary mb-3">
                    Explore all 75 current sitting MLCs across Assembly Quota, Local Authorities, Governor Nominated, Graduates, and Teachers quotas.
                </p>
                <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-outline-dark btn-sm fw-bold w-100 rounded-3">
                    <i class="bi bi-card-list me-1"></i> Browse 75 MLCs Roster &rarr;
                </a>
            </div>

            <!-- WhatsApp Alert Channel -->
            <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #075e54, #128c7e);">
                <div class="card-body p-4 text-center">
                    <i class="bi bi-whatsapp display-4 mb-2 d-block text-warning"></i>
                    <h5 class="fw-bold mb-2">Vidhan Parishad WhatsApp Alerts</h5>
                    <p class="small opacity-90 mb-3">Get live candidate nominations, polling booth locators, and election results on your phone.</p>
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-warning fw-bold px-4 py-2 rounded-pill text-dark shadow-sm">
                        Join WhatsApp Alerts &rarr;
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
// Countdown Timer to 23 October 2026, 08:00 AM IST
function updateCountdown() {
    const pollDate = new Date('2026-10-23T08:00:00+05:30').getTime();
    const now = new Date().getTime();
    const distance = pollDate - now;

    if (distance < 0) {
        document.getElementById('cdDays').innerText = '00';
        document.getElementById('cdHours').innerText = '00';
        document.getElementById('cdMinutes').innerText = '00';
        document.getElementById('cdSeconds').innerText = '00';
        return;
    }

    const days = Math.floor(distance / (1000 * 60 * 60 * 24));
    const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);

    document.getElementById('cdDays').innerText = days < 10 ? '0' + days : days;
    document.getElementById('cdHours').innerText = hours < 10 ? '0' + hours : hours;
    document.getElementById('cdMinutes').innerText = minutes < 10 ? '0' + minutes : minutes;
    document.getElementById('cdSeconds').innerText = seconds < 10 ? '0' + seconds : seconds;
}
setInterval(updateCountdown, 1000);
updateCountdown();

// Filter Constituency Cards by Quota Type
function filterSeats(type, btn) {
    document.querySelectorAll('#seatsGrid .seat-card-item').forEach(el => {
        if (type === 'all' || el.dataset.type === type) {
            el.style.display = 'block';
        } else {
            el.style.display = 'none';
        }
    });

    if (btn) {
        btn.parentElement.querySelectorAll('.btn').forEach(b => b.classList.remove('active', 'btn-dark', 'btn-primary', 'btn-success'));
        btn.classList.add('active');
        if (type === 'all') btn.classList.add('btn-dark');
        else if (type === 'Graduates') btn.classList.add('btn-primary');
        else if (type === 'Teachers') btn.classList.add('btn-success');
    }
}

// District to Constituency Mapping Data
const districtMapping = {
    "Patna": { grad: "Patna Graduates (पटना स्नातक)", teach: "Patna Teachers (पटना शिक्षक)", ro: "Divisional Commissioner, Patna" },
    "Nalanda": { grad: "Patna Graduates (पटना स्नातक)", teach: "Patna Teachers (पटना शिक्षक)", ro: "Divisional Commissioner, Patna" },
    "Nawada": { grad: "Patna Graduates (पटना स्नातक)", teach: "Patna Teachers (पटना शिक्षक)", ro: "Divisional Commissioner, Patna" },
    "Muzaffarpur": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)" },
    "Vaishali": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)" },
    "Sitamarhi": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)" },
    "Sheohar": { grad: "Tirhut Graduates (तिरहुत स्नातक)", teach: "Tirhut Teachers (तिरहुत शिक्षक)", ro: "Divisional Commissioner, Tirhut (Muzaffarpur)" },
    "Darbhanga": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga" },
    "Madhubani": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga" },
    "Samastipur": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga" },
    "Begusarai": { grad: "Darbhanga Graduates (दरभंगा स्नातक)", teach: "Darbhanga Teachers (दरभंगा शिक्षक)", ro: "Divisional Commissioner, Darbhanga" },
    "Saharsa": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Kosi / Eastern Quota", ro: "Divisional Commissioner, Kosi / Purnia" },
    "Supaul": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Kosi / Eastern Quota", ro: "Divisional Commissioner, Kosi / Purnia" },
    "Madhepura": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Kosi / Eastern Quota", ro: "Divisional Commissioner, Kosi / Purnia" },
    "Purnia": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Purnia Division", ro: "Divisional Commissioner, Purnia" },
    "Araria": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Purnia Division", ro: "Divisional Commissioner, Purnia" },
    "Kishanganj": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Purnia Division", ro: "Divisional Commissioner, Purnia" },
    "Katihar": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Purnia Division", ro: "Divisional Commissioner, Purnia" },
    "Bhagalpur": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Bhagalpur Division", ro: "Divisional Commissioner, Bhagalpur" },
    "Banka": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Bhagalpur Division", ro: "Divisional Commissioner, Bhagalpur" },
    "Munger": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Munger Division", ro: "Divisional Commissioner, Munger" },
    "Jamui": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Munger Division", ro: "Divisional Commissioner, Munger" },
    "Lakhisarai": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Munger Division", ro: "Divisional Commissioner, Munger" },
    "Sheikhpura": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Munger Division", ro: "Divisional Commissioner, Munger" },
    "Khagaria": { grad: "Kosi Graduates (कोसी स्नातक)", teach: "Munger Division", ro: "Divisional Commissioner, Munger" },
    "Saran": { grad: "Saran / Western Belt", teach: "Saran Teachers (सारण शिक्षक)", ro: "Divisional Commissioner, Saran (Chapra)" },
    "Siwan": { grad: "Saran / Western Belt", teach: "Saran Teachers (सारण शिक्षक)", ro: "Divisional Commissioner, Saran (Chapra)" },
    "Gopalganj": { grad: "Saran / Western Belt", teach: "Saran Teachers (सारण शिक्षक)", ro: "Divisional Commissioner, Saran (Chapra)" },
    "East Champaran": { grad: "Tirhut / Champaran", teach: "Saran Teachers (सारण शिक्षक)", ro: "Divisional Commissioner, Saran (Chapra)" },
    "West Champaran": { grad: "Tirhut / Champaran", teach: "Saran Teachers (सारण शिक्षक)", ro: "Divisional Commissioner, Saran (Chapra)" },
    "Gaya": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh" },
    "Jehanabad": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh" },
    "Arwal": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh" },
    "Aurangabad": { grad: "Gaya Graduates", teach: "Gaya Teachers", ro: "Divisional Commissioner, Magadh" },
    "Rohtas": { grad: "South Bihar Quota", teach: "South Bihar Quota", ro: "Divisional Commissioner, Patna/Magadh" },
    "Kaimur": { grad: "South Bihar Quota", teach: "South Bihar Quota", ro: "Divisional Commissioner, Patna/Magadh" },
    "Bhojpur": { grad: "Patna/Shahabad Quota", teach: "Patna/Shahabad Quota", ro: "Divisional Commissioner, Patna" },
    "Buxar": { grad: "Patna/Shahabad Quota", teach: "Patna/Shahabad Quota", ro: "Divisional Commissioner, Patna" }
};

function lookupDistrictConstituency(district) {
    const box = document.getElementById('lookupResultBox');
    if (!district || !districtMapping[district]) {
        box.classList.add('d-none');
        return;
    }

    const info = districtMapping[district];
    document.getElementById('resDistrictName').innerText = district + ' District (जिला)';
    document.getElementById('resGradSeat').innerText = info.grad;
    document.getElementById('resTeachSeat').innerText = info.teach;
    document.getElementById('resRO').innerText = info.ro;
    box.classList.remove('d-none');
}
</script>

<?php include 'footer.php'; ?>
