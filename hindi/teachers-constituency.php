<?php
/**
 * BiharElection.com - बिहार विधान परिषद (Vidhan Parishad)
 * 6 शिक्षक निर्वाचन क्षेत्रों की संपूर्ण डायरेक्टरी एवं गाइड
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'बिहार शिक्षक निर्वाचन क्षेत्र (6 सीटें): विधान परिषद एमएलसी चुनावी गाइड, फॉर्म 19 एवं 2026 चुनाव विवरण';
$pageDescription = 'बिहार विधान परिषद के सभी 6 शिक्षक निर्वाचन क्षेत्रों (Teachers Constituencies) की आधिकारिक जानकारी: पटना, तिरहुत, दरभंगा, सारण, गया एवं कोसी। फॉर्म 19 शिक्षक मतदाता पंजीकरण नियम, स्कूल/कॉलेज पात्रता एवं 2026 चुनाव अपडेट।';
$pageKeywords = 'बिहार शिक्षक निर्वाचन क्षेत्र, विधान परिषद शिक्षक एमएलसी, पटना शिक्षक, तिरहुत शिक्षक, दरभंगा शिक्षक, सारण शिक्षक, गया शिक्षक, कोसी शिक्षक, फॉर्म 19 बिहार, अनुच्छेद 171';
$pageCanonical = HINDI_BASE_URL . 'teachers-constituency';
$activeNav = 'mlc';

// 6 Teachers Constituencies Data
$teachersSeats = [
    [
        'id' => 'patna-teachers',
        'name' => 'Patna Teachers',
        'name_hi' => 'पटना शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'पटना',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना प्रमंडल',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'नवल किशोर यादव (भाजपा) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'राजधानी पटना, नालंदा और नवादा के माध्यमिक, उच्च माध्यमिक विद्यालयों, डिग्री कॉलेजों एवं पटना विश्वविद्यालय के शिक्षकों का प्रतिनिधित्व।'
    ],
    [
        'id' => 'tirhut-teachers',
        'name' => 'Tirhut Teachers',
        'name_hi' => 'तिरहुत शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'मुजफ्फरपुर',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'प्रो. संजय कुमार सिंह (भाकपा/जदयू) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'तिरहुत प्रमंडल के 4 जिलों के माध्यमिक-उच्च माध्यमिक विद्यालयों, इंटर कॉलेजों एवं बीआरए बिहार विवि के प्राध्यापकों का निर्वाचन क्षेत्र।'
    ],
    [
        'id' => 'darbhanga-teachers',
        'name' => 'Darbhanga Teachers',
        'name_hi' => 'दरभंगा शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'दरभंगा',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा प्रमंडल',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'डॉ. मदन मोहन झा (कांग्रेस) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'मिथिलांचल के 4 जिलों (एलएनएमयू, केएसडीएसयू दरभंगा, अंगीभूत कॉलेज, संबद्ध कॉलेज एवं उच्च विद्यालय) के शिक्षकों का क्षेत्र।'
    ],
    [
        'id' => 'saran-teachers',
        'name' => 'Saran Teachers',
        'name_hi' => 'सारण शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'छपरा',
        'ro' => 'Divisional Commissioner, Saran (Chapra)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'districts' => ['Saran', 'Siwan', 'Gopalganj', 'East Champaran', 'West Champaran'],
        'districts_hi' => ['सारण', 'सिवान', 'गोपालगंज', 'पूर्वी चंपारण', 'पश्चिमी चंपारण'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'अफाक अहमद / 2026 चुनाव हेतु रिक्त',
        'desc' => 'सारण एवं चंपारण क्षेत्र के 5 जिलों (छपरा, सिवान, गोपालगंज, मोतिहारी, बेतिया) के शिक्षकों, जय प्रकाश विवि के शिक्षकों का निर्वाचन क्षेत्र।'
    ],
    [
        'id' => 'gaya-teachers',
        'name' => 'Gaya Teachers',
        'name_hi' => 'गया शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'गया',
        'ro' => 'Divisional Commissioner, Magadh (Gaya)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, मगध (गया)',
        'districts' => ['Gaya', 'Jehanabad', 'Arwal', 'Aurangabad', 'Rohtas', 'Kaimur', 'Bhojpur', 'Buxar'],
        'districts_hi' => ['गया', 'जहानाबाद', 'अरवल', 'औरंगाबाद', 'रोहतास', 'कैमूर', 'भोजपुर', 'बक्सर'],
        'status' => 'active_term',
        'status_label' => 'सक्रिय कार्यकाल',
        'poll_date' => 'अगला चुनाव 2029',
        'result_date' => '-',
        'incumbent' => 'संजीव श्याम सिंह (जदयू)',
        'desc' => 'दक्षिण बिहार और शाहाबाद के 8 जिलों के शिक्षक मतदाताओं, मगध विवि एवं वीर कुंवर सिंह विवि के प्राध्यापकों का क्षेत्र।'
    ],
    [
        'id' => 'kosi-teachers',
        'name' => 'Kosi Teachers',
        'name_hi' => 'कोसी शिक्षक निर्वाचन क्षेत्र',
        'hq' => 'सहरसा / पूर्णिया',
        'ro' => 'Divisional Commissioner, Kosi / Purnia',
        'ro_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया प्रमंडल',
        'districts' => ['Saharsa', 'Supaul', 'Madhepura', 'Purnia', 'Araria', 'Kishanganj', 'Katihar', 'Bhagalpur', 'Banka', 'Munger', 'Jamui', 'Lakhisarai', 'Sheikhpura', 'Khagaria'],
        'districts_hi' => ['सहरसा', 'सुपौल', 'मधेपुरा', 'पूर्णिया', 'अररिया', 'किशनगंज', 'कटिहार', 'भागलपुर', 'बांका', 'मुंगेर', 'जमुई', 'लखीसराय', 'शेखपुरा', 'खगड़िया'],
        'status' => 'active_term',
        'status_label' => 'सक्रिय कार्यकाल',
        'poll_date' => 'अगला चुनाव 2029',
        'result_date' => '-',
        'incumbent' => 'डॉ. संजीव कुमार सिंह (जदयू)',
        'desc' => 'पूर्वी बिहार, कोसी, सीमांचल, अंग प्रदेश और मुंगेर प्रमंडल के 14 जिलों (टीएमबीयू, बीएनएमयू, पूर्णिया विवि, मुंगेर विवि) के शिक्षकों का विशाल क्षेत्र।'
    ]
];

// All Bihar districts mapped to Teachers' Constituency
$allDistrictsTeachersMap = [
    'अररिया' => 'kosi-teachers',
    'अरवल' => 'gaya-teachers',
    'औरंगाबाद' => 'gaya-teachers',
    'कटिहार' => 'kosi-teachers',
    'किशनगंज' => 'kosi-teachers',
    'कैमूर' => 'gaya-teachers',
    'खगड़िया' => 'kosi-teachers',
    'गया' => 'gaya-teachers',
    'गोपालगंज' => 'saran-teachers',
    'जमुई' => 'kosi-teachers',
    'जहानाबाद' => 'gaya-teachers',
    'दरभंगा' => 'darbhanga-teachers',
    'नवादा' => 'patna-teachers',
    'नालंदा' => 'patna-teachers',
    'पटना' => 'patna-teachers',
    'पश्चिम चंपारण' => 'saran-teachers',
    'पूर्णिया' => 'kosi-teachers',
    'पूर्वी चंपारण' => 'saran-teachers',
    'बक्सर' => 'gaya-teachers',
    'बांका' => 'kosi-teachers',
    'बेगूसराय' => 'darbhanga-teachers',
    'भागलपुर' => 'kosi-teachers',
    'भोजपुर' => 'gaya-teachers',
    'मधेपुरा' => 'kosi-teachers',
    'मधुबनी' => 'darbhanga-teachers',
    'मुंगेर' => 'kosi-teachers',
    'मुजफ्फरपुर' => 'tirhut-teachers',
    'रोहतास' => 'gaya-teachers',
    'लखीसराय' => 'kosi-teachers',
    'वैशाली' => 'tirhut-teachers',
    'शिवहर' => 'tirhut-teachers',
    'शेखपुरा' => 'kosi-teachers',
    'समस्तीपुर' => 'darbhanga-teachers',
    'सहरसा' => 'kosi-teachers',
    'सारण' => 'saran-teachers',
    'सीतामढ़ी' => 'tirhut-teachers',
    'सीवान' => 'saran-teachers',
    'सुपौल' => 'kosi-teachers'
];

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Gradient & Glassmorphism Theme - Emerald/Gold */
.teach-hero {
    background: linear-gradient(135deg, #064e3b 0%, #065f46 50%, #022c22 100%);
    color: #fff;
    padding: 55px 0 45px;
    position: relative;
    overflow: hidden;
}
.teach-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 550px;
    height: 550px;
    background: radial-gradient(circle, rgba(16, 185, 129, 0.25) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.teach-hero::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -10%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(245, 158, 11, 0.15) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

/* Metric Stat Cards */
.stat-box-teach {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 1.1rem 1.25rem;
    color: #ffffff;
    transition: transform 0.2s ease, background 0.2s ease;
}
.stat-box-teach:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.12);
}

/* Constituency Card Styling */
.constituency-card {
    background: #ffffff;
    border-radius: 20px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(11, 25, 44, 0.05);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    overflow: hidden;
}
.constituency-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #059669, #10b981);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.constituency-card.election-active::before {
    background: linear-gradient(90deg, #ef4444, #f59e0b);
    opacity: 1;
}
.constituency-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 36px -6px rgba(16, 185, 129, 0.18);
    border-color: #10b981;
}
.constituency-card:hover::before {
    opacity: 1;
}

/* District Pills */
.district-pill {
    background-color: #f8fafc;
    color: #334155;
    font-size: 0.78rem;
    font-weight: 600;
    padding: 4px 11px;
    border-radius: 50px;
    border: 1px solid #e2e8f0;
    display: inline-block;
    transition: all 0.2s ease;
}
.district-pill:hover {
    background-color: #ecfdf5;
    color: #065f46;
    border-color: #a7f3d0;
}

/* Step & Info Cards */
.info-step-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    padding: 1.5rem;
    transition: all 0.25s ease;
    height: 100%;
}
.info-step-card:hover {
    border-color: #6ee7b7;
    box-shadow: 0 10px 25px rgba(16, 185, 129, 0.09);
    transform: translateY(-2px);
}

/* Pulse animation */
@keyframes pulseGlow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.03); }
}
.blink-anim {
    animation: pulseGlow 1.8s infinite ease-in-out;
}

/* Search and Filter bar */
.filter-btn-teach.active {
    background-color: #064e3b !important;
    color: #ffffff !important;
    border-color: #064e3b !important;
}
.search-input-box-teach {
    border-radius: 50px;
    padding-left: 2.75rem;
    border: 2px solid #e2e8f0;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.search-input-box-teach:focus {
    border-color: #059669;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}

/* FAQ accordion styling */
.faq-accordion .accordion-button {
    font-weight: 700;
    color: #0f172a;
    background-color: #f8fafc;
    border-radius: 12px;
    padding: 1.1rem 1.25rem;
}
.faq-accordion .accordion-button:not(.collapsed) {
    background-color: #ecfdf5;
    color: #065f46;
    box-shadow: none;
}
.faq-accordion .accordion-item {
    border: 1px solid #e2e8f0;
    border-radius: 14px !important;
    overflow: hidden;
    margin-bottom: 0.75rem;
}
</style>

<!-- Hero Section -->
<section class="teach-hero">
    <div class="container text-start position-relative">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-success text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                📚 अनुच्छेद 171(3)(b) &bull; भारतीय संविधान
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                कुल 6 शिक्षक निर्वाचन क्षेत्र
            </span>
            <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill blink-anim shadow-sm">
                <i class="bi bi-broadcast me-1"></i> 4 सीटों पर 2026 चुनाव सक्रिय
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo HINDI_BASE_URL; ?>" class="text-white-50 text-decoration-none">होम</a></li>
                <li class="breadcrumb-item"><a href="<?php echo HINDI_BASE_URL; ?>representatives" class="text-white-50 text-decoration-none">प्रतिनिधि</a></li>
                <li class="breadcrumb-item"><a href="<?php echo HINDI_BASE_URL; ?>mlc" class="text-white-50 text-decoration-none">विधान परिषद (MLCs)</a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">शिक्षक निर्वाचन क्षेत्र</li>
            </ol>
        </nav>

        <h1 class="display-5 fw-extrabold text-white mb-2">
            बिहार शिक्षक निर्वाचन क्षेत्र (Teachers' Constituencies) <br>
            <span style="color: #f59e0b;">विधान परिषद की 6 विशेष शिक्षक सीटें</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.08rem; max-width: 900px;">
            बिहार विधान परिषद के सभी 6 शिक्षक निर्वाचन क्षेत्रों की आधिकारिक मार्गदर्शिका। संवैधानिक प्रावधान, फॉर्म 19 शिक्षक मतदाता पात्रता नियम, मान्यता प्राप्त शिक्षण संस्थान और 2026 के द्विवार्षिक चुनाव की संपूर्ण जानकारी।
        </p>

        <!-- Key Metrics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-box-teach">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">कुल शिक्षक सीटें</div>
                    <div class="fs-4 fw-extrabold text-white">6 सीटें</div>
                    <div class="text-warning extra-small">विधान परिषद का 1/12वां भाग</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box-teach">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">2026 चुनाव वाली सीटें</div>
                    <div class="fs-4 fw-extrabold text-danger blink-anim">4 सीटें</div>
                    <div class="text-white-50 extra-small">पटना, तिरहुत, दरभंगा, सारण</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box-teach">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">मतदान की तिथि</div>
                    <div class="fs-4 fw-extrabold text-white">23 अक्टूबर 2026</div>
                    <div class="text-success extra-small">शुक्रवार &bull; सुबह 8 से शाम 4</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box-teach">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">मतगणना / परिणाम</div>
                    <div class="fs-4 fw-extrabold text-white">27 अक्टूबर 2026</div>
                    <div class="text-info extra-small">वरीयता मत गणना (PR-STV)</div>
                </div>
            </div>
        </div>

        <!-- Action Links & Quick Navigation -->
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-danger fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2 rounded-pill">
                <i class="bi bi-broadcast"></i> 2026 चुनाव हब देखें
            </a>
            <a href="<?php echo HINDI_BASE_URL; ?>graduates-constituency" class="btn btn-primary fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2 rounded-pill">
                <i class="bi bi-mortarboard"></i> 6 स्नातक निर्वाचन क्षेत्र
            </a>
            <a href="<?php echo HINDI_BASE_URL; ?>mlc" class="btn btn-outline-light fw-bold px-3.5 py-2.5 shadow-sm rounded-pill">
                <i class="bi bi-journal-text me-1"></i> 75 एमएलसी डायरेक्टरी
            </a>
            <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="btn btn-sm btn-outline-warning ms-auto rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-translate me-1"></i> In English
            </a>
        </div>
    </div>
</section>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Interactive District Finder Tool -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%); border: 1px solid #a7f3d0 !important;">
        <div class="p-3.5 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success text-white p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">शिक्षक जिला खोजक &bull; अपना शिक्षक क्षेत्र जानें</h5>
                            <small class="text-muted">बिहार के 38 जिलों में से अपना जिला चुनकर संबंधित शिक्षक निर्वाचन क्षेत्र देखें</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <select id="districtFinderSelect" class="form-select form-select-lg rounded-pill border-2 border-success fw-semibold text-navy">
                            <option value="">-- बिहार का जिला चुनें (जैसे पटना, मुजफ्फरपुर, सारण) --</option>
                            <?php 
                            ksort($allDistrictsTeachersMap);
                            foreach ($allDistrictsTeachersMap as $distName => $constId): ?>
                                <option value="<?php echo $constId; ?>"><?php echo htmlspecialchars($distName); ?> जिला</option>
                            <?php endforeach; ?>
                        </select>
                        <button id="btnFindDistrict" class="btn btn-success btn-lg rounded-pill px-4 fw-bold text-nowrap shadow-sm">
                            <i class="bi bi-search me-1"></i> खोजें
                        </button>
                    </div>
                </div>
            </div>
            <!-- Dynamic Result Alert -->
            <div id="districtResultBox" class="mt-3 p-3 bg-white rounded-3 border border-success d-none">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div id="districtResultText" class="fw-bold text-navy"></div>
                    <a id="districtResultLink" href="#patna-teachers" class="btn btn-sm btn-outline-success rounded-pill fw-bold">
                        सीट कार्ड पर जाएं <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active 2026 Election Notice for Teachers -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 text-white overflow-hidden" style="background: linear-gradient(135deg, #065f46 0%, #059669 50%, #047857 100%);">
        <div class="p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-book-half"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill text-uppercase">2026 द्विवार्षिक चुनाव</span>
                        <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-2.5 py-1 rounded-pill">गजट: TCGCGajat2026</span>
                    </div>
                    <h4 class="fw-extrabold text-white mb-1">4 शिक्षक सीटों पर चुनाव प्रक्रिया जारी</h4>
                    <p class="text-white-50 mb-0 small" style="max-width: 820px;">
                        <strong>पटना</strong>, <strong>तिरहुत</strong>, <strong>दरभंगा</strong> एवं <strong>सारण</strong> शिक्षक निर्वाचन क्षेत्रों में कार्यकाल समाप्त होने के कारण चुनाव जारी है। मतदान <strong>23 अक्टूबर 2026</strong> और मतगणना <strong>27 अक्टूबर 2026</strong> को होगी।
                    </p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-warning fw-bold text-dark rounded-pill px-4 py-2.5 shadow-sm text-nowrap d-inline-flex align-items-center gap-2">
                <span>संपूर्ण चुनाव विवरण देखें</span>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    <!-- Filter & Search Controls Bar -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-extrabold text-navy mb-1">
                <i class="bi bi-person-workspace text-success me-2"></i> सभी 6 शिक्षक निर्वाचन क्षेत्र (बिहार)
            </h3>
            <p class="text-muted small mb-0">बिहार विधान मंडल में शिक्षकों एवं प्राध्यापकों का प्रत्यक्ष लोकतांत्रिक प्रतिनिधित्व</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Filter Pills -->
            <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                <button type="button" class="btn btn-sm rounded-pill fw-bold filter-btn-teach active" data-filter="all">सभी 6 सीटें</button>
                <button type="button" class="btn btn-sm rounded-pill fw-bold filter-btn-teach text-danger" data-filter="election">⚡ चुनाव 2026 (4)</button>
                <button type="button" class="btn btn-sm rounded-pill fw-bold filter-btn-teach text-success" data-filter="active">✓ सक्रिय (2)</button>
            </div>

            <!-- Instant Search Box -->
            <div class="position-relative" style="min-width: 220px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="seatSearchInput" class="form-control form-control-sm search-input-box-teach" placeholder="सीट या जिला खोजें...">
            </div>
        </div>
    </div>

    <!-- 6 Teachers' Constituencies Grid -->
    <div class="row g-4 mb-5" id="seatsGrid">
        <?php foreach ($teachersSeats as $seat): 
            $isOngoing = ($seat['status'] === 'election_2026');
            $districtsSearchStr = mb_strtolower(implode(' ', $seat['districts_hi']) . ' ' . implode(' ', $seat['districts']) . ' ' . $seat['name_hi'] . ' ' . $seat['name']);
        ?>
            <div class="col-md-6 col-lg-4 seat-item" id="<?php echo $seat['id']; ?>" data-status="<?php echo $isOngoing ? 'election' : 'active'; ?>" data-search="<?php echo htmlspecialchars($districtsSearchStr); ?>">
                <div class="constituency-card p-4 <?php echo $isOngoing ? 'election-active' : ''; ?>">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill extra-small fw-bold">
                                📚 शिक्षक कोटा
                            </span>
                            <?php if ($isOngoing): ?>
                                <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill extra-small fw-bold blink-anim">
                                    <i class="bi bi-broadcast me-1"></i> मतदान 23 अक्टूबर 2026
                                </span>
                            <?php else: ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1 rounded-pill extra-small fw-bold">
                                    ✓ सक्रिय कार्यकाल
                                </span>
                            <?php endif; ?>
                        </div>

                        <h4 class="fw-extrabold text-navy mb-0.5"><?php echo htmlspecialchars($seat['name_hi']); ?></h4>
                        <div class="text-muted small mb-3"><?php echo htmlspecialchars($seat['name']); ?></div>

                        <p class="text-muted small mb-3"><?php echo htmlspecialchars($seat['desc']); ?></p>

                        <!-- Returning Officer & HQ -->
                        <div class="p-2.5 bg-light rounded-3 border mb-3 small">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted extra-small">मुख्यालय:</span>
                                <strong class="text-dark"><?php echo htmlspecialchars($seat['hq']); ?></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted extra-small">निर्वाचन पदाधिकारी:</span>
                                <span class="text-navy fw-semibold extra-small text-end" style="max-width: 170px;"><?php echo htmlspecialchars($seat['ro_hi']); ?></span>
                            </div>
                        </div>

                        <!-- Districts Included -->
                        <div class="mb-3">
                            <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1.5">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> शामिल जिले (<?php echo count($seat['districts_hi']); ?>):
                            </span>
                            <div class="d-flex flex-wrap gap-1">
                                <?php foreach ($seat['districts_hi'] as $dist): ?>
                                    <span class="district-pill"><?php echo htmlspecialchars($dist); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pt-3 border-top mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted extra-small">वर्तमान सदस्य:</span>
                            <span class="fw-bold text-navy extra-small text-end text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($seat['incumbent']); ?>">
                                <?php echo htmlspecialchars($seat['incumbent']); ?>
                            </span>
                        </div>
                        <?php if ($isOngoing): ?>
                            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election#<?php echo $seat['id']; ?>" class="btn btn-sm btn-danger w-100 rounded-pill fw-semibold py-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-box-arrow-up-right"></i> 2026 चुनाव कार्यक्रम एवं उम्मीदवार
                            </a>
                        <?php else: ?>
                            <a href="<?php echo HINDI_BASE_URL; ?>mlc" class="btn btn-sm btn-outline-success w-100 rounded-pill fw-semibold py-2 d-flex align-items-center justify-content-center gap-1">
                                <i class="bi bi-person-lines-fill"></i> सदस्य प्रोफ़ाइल देखें
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- No results message -->
    <div id="noResultsMsg" class="alert alert-info rounded-4 text-center py-4 d-none">
        <i class="bi bi-search fs-2 d-block mb-2 text-success"></i>
        <h5>कोई सीट या जिला नहीं मिला</h5>
        <p class="text-muted mb-0 small">कृपया सही जिला या शिक्षक सीट का नाम लिखें (उदा. पटना, सारण, तिरहुत)</p>
    </div>

    <!-- Voter Registration & Electoral Mechanism Dossier -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5 mb-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-1.5 rounded-pill mb-1">
                    फॉर्म 19 संपूर्ण गाइड
                </span>
                <h3 class="fw-extrabold text-navy mb-0">
                    <i class="bi bi-mortarboard-fill text-success me-1"></i> शिक्षक मतदाता पात्रता एवं पंजीकरण नियमावली
                </h3>
            </div>
            <a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline-success rounded-pill fw-bold btn-sm px-3 py-1.5">
                <i class="bi bi-box-arrow-up-right me-1"></i> ECI Voters Portal
            </a>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-lg-6">
                <div class="info-step-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-weight: 800;">
                            1
                        </div>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">शिक्षक मतदाता कौन बन सकता है?</h5>
                            <small class="text-muted">पात्रता एवं शैक्षणिक संस्थान</small>
                        </div>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>नागरिकता एवं निवास:</strong> भारत का नागरिक एवं संबंधित शिक्षक निर्वाचन क्षेत्र का साधारण निवासी होना आवश्यक है।</li>
                        <li><strong>3 वर्ष का शिक्षण अनुभव:</strong> विगत 6 वर्षों में से कम से कम <strong>3 वर्ष तक</strong> राज्य के मान्यता प्राप्त शिक्षण संस्थानों में अध्यापन कार्य किया हो।</li>
                        <li><strong>पात्र शिक्षण संस्थान:</strong> माध्यमिक विद्यालय (हाई स्कूल), उच्च माध्यमिक (+2) विद्यालय, इंटर कॉलेज, डिग्री कॉलेज, सरकारी एवं अंगीभूत विश्वविद्यालय।</li>
                        <li><strong>प्राथमिक शिक्षक अपात्र:</strong> संविधान के अनुच्छेद 171(3)(b) के अनुसार प्राथमिक एवं मध्य विद्यालयों (कक्षा 8 तक) के शिक्षक इस कोटे में मतदाता बनने के पात्र नहीं हैं।</li>
                    </ul>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-lg-6">
                <div class="info-step-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-weight: 800;">
                            2
                        </div>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">फॉर्म 19 आवेदन एवं सेवा प्रमाण पत्र</h5>
                            <small class="text-muted">संस्थान प्रधान सत्यापन प्रक्रिया</small>
                        </div>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>आवेदन प्रक्रिया:</strong> <strong>फॉर्म 19</strong> भरकर ऑनलाइन (<a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>) या ऑफलाइन निर्वाचक निबंधन पदाधिकारी (ERO/SDM/BDO) के पास जमा करें।</li>
                        <li><strong>संस्थान प्रधान का प्रमाण पत्र:</strong> शिक्षण संस्थान के प्रधान (प्रधानाध्यापक, प्राचार्य या कुलसचिव) द्वारा 3 वर्ष की संतोषजनक शिक्षण सेवा का हस्ताक्षरित एवं मुहरयुक्त प्रमाण पत्र संलग्न करना अनिवार्य है।</li>
                        <li><strong>मतदाता सूची का नया निर्माण:</strong> प्रत्येक द्विवार्षिक चुनाव से पूर्व शिक्षकों की पूरी मतदाता सूची नए सिरे से तैयार की जाती है।</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Constitutional Provisions & STV System -->
        <div class="mt-4 pt-4 border-top">
            <div class="row g-4 align-items-center">
                <div class="col-lg-7">
                    <h5 class="fw-bold text-navy mb-2">संवैधानिक उद्देश्य एवं वरीयता मत प्रणाली</h5>
                    <p class="text-muted small mb-2">
                        भारतीय संविधान के <strong>अनुच्छेद 171(3)(b)</strong> के अंतर्गत विधान परिषद के 1/12वें सदस्य (बिहार में 6 सदस्य) माध्यमिक व उच्च शिक्षण संस्थानों के शिक्षकों द्वारा निर्वाचित किए जाते हैं।
                    </p>
                    <p class="text-muted small mb-0">
                        मतदान मतपत्र पर <strong>एकल संक्रमणीय मत (PR-STV)</strong> द्वारा संपन्न होता है। मतदाताओं द्वारा उम्मीदवारों के नाम के आगे वरीयता क्रम (1, 2, 3...) अंकित किया जाता है।
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="p-3.5 bg-light rounded-4 border text-start">
                        <div class="fw-bold text-navy small mb-1"><i class="bi bi-award text-success me-1"></i> शिक्षक प्रतिनिधित्व का महत्व:</div>
                        <p class="text-muted extra-small mb-0">
                            यह कोटा शिक्षकों के वेतन, सेवा शर्तों, पेंशन, अतिथि शिक्षकों की समस्याओं, विश्वविद्यालयी स्वायत्तता और शिक्षा नीति पर राज्य विधायिका में मजबूत पक्ष रखने का संवैधानिक मंच है।
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frequently Asked Questions (FAQ) Accordion -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5 mb-5">
        <h3 class="fw-extrabold text-navy mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-question-circle-fill text-success"></i> शिक्षक निर्वाचन क्षेत्र : अक्सर पूछे जाने वाले प्रश्न (FAQs)
        </h3>

        <div class="accordion faq-accordion" id="teachFaqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqTeachHeading1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqTeach1" aria-expanded="true" aria-controls="faqTeach1">
                        1. क्या प्राथमिक विद्यालय (Primary/Middle School) के शिक्षक वोट दे सकते हैं?
                    </button>
                </h2>
                <div id="faqTeach1" class="accordion-collapse collapse show" aria-labelledby="faqTeachHeading1" data-bs-parent="#teachFaqAccordion">
                    <div class="accordion-body text-muted small">
                        <strong>नहीं।</strong> संविधान के अनुच्छेद 171(3)(b) और जन प्रतिनिधित्व अधिनियम के अनुसार केवल माध्यमिक विद्यालय (Secondary School / High School) या उससे उच्च स्तर के शिक्षण संस्थानों (उच्च माध्यमिक, कॉलेज, विश्वविद्यालय) में पढ़ाने वाले शिक्षक ही मतदाता बनने के पात्र हैं।
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faqTeachHeading2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTeach2" aria-expanded="false" aria-controls="faqTeach2">
                        2. 3 वर्ष का शिक्षण अनुभव किस प्रकार गिना जाता है?
                    </button>
                </h2>
                <div id="faqTeach2" class="accordion-collapse collapse" aria-labelledby="faqTeachHeading2" data-bs-parent="#teachFaqAccordion">
                    <div class="accordion-body text-muted small">
                        निर्वाचन आयोग द्वारा घोषित अर्हक तिथि (प्रायः 1 नवंबर) से पूर्व के <strong>विगत 6 वर्षों में कुल मिलाकर कम से कम 3 वर्ष का शिक्षण कार्य</strong> पूरा होना चाहिए। यह अनुभव एक या एक से अधिक मान्यता प्राप्त संस्थानों का संयुक्त रूप से भी हो सकता है।
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faqTeachHeading3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTeach3" aria-expanded="false" aria-controls="faqTeach3">
                        3. क्या निजी (Private) या वित्तपोषित/अंगीभूत कॉलेजों के शिक्षक मतदाता बन सकते हैं?
                    </button>
                </h2>
                <div id="faqTeach3" class="accordion-collapse collapse" aria-labelledby="faqTeachHeading3" data-bs-parent="#teachFaqAccordion">
                    <div class="accordion-body text-muted small">
                        <strong>हाँ।</strong> यदि संबंधित निजी स्कूल (माध्यमिक/उच्च माध्यमिक स्तर) या कॉलेज राज्य सरकार/सीबीएसई/आईसीएसई अथवा यूजीसी द्वारा विधिवत मान्यता प्राप्त है और संस्थान के प्रधान (Principal/Headmaster) द्वारा निर्धारित प्रपत्र में सेवा प्रमाण पत्र जारी किया गया है, तो शिक्षक आवेदन कर सकते हैं।
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faqTeachHeading4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqTeach4" aria-expanded="false" aria-controls="faqTeach4">
                        4. फॉर्म 19 के साथ कौन से दस्तावेज संलग्न करने होंगे?
                    </button>
                </h2>
                <div id="faqTeach4" class="accordion-collapse collapse" aria-labelledby="faqTeachHeading4" data-bs-parent="#teachFaqAccordion">
                    <div class="accordion-body text-muted small">
                        फॉर्म 19 के साथ (i) संस्थान के प्रधान का हस्ताक्षरित एवं सील युक्त सेवा प्रमाण पत्र, (ii) निवास प्रमाण पत्र (आधार कार्ड, वोटर कार्ड या पासपोर्ट), एवं (iii) एक पासपोर्ट आकार का फोटोग्राफ संलग्न करना अनिवार्य है।
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Navigation Links -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo HINDI_BASE_URL; ?>graduates-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-primary"><i class="bi bi-mortarboard-fill"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">स्नातक निर्वाचन क्षेत्र</h6>
                        <small class="text-muted">6 स्नातक एमएलसी सीटों का विवरण</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-danger"><i class="bi bi-broadcast"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">2026 चुनाव हब</h6>
                        <small class="text-muted">गजट अधिसूचना, कार्यक्रम एवं अधिकारी</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo HINDI_BASE_URL; ?>mlc" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-info"><i class="bi bi-building"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">75 एमएलसी डायरेक्टरी</h6>
                        <small class="text-muted">विधान परिषद सदस्यों की पूरी सूची</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // District Finder Logic
    const selectDistrict = document.getElementById('districtFinderSelect');
    const btnFind = document.getElementById('btnFindDistrict');
    const resultBox = document.getElementById('districtResultBox');
    const resultText = document.getElementById('districtResultText');
    const resultLink = document.getElementById('districtResultLink');

    function performDistrictFind() {
        const selectedConstId = selectDistrict.value;
        const selectedText = selectDistrict.options[selectDistrict.selectedIndex]?.text || '';
        
        if (!selectedConstId) {
            resultBox.classList.add('d-none');
            return;
        }

        const seatElement = document.getElementById(selectedConstId);
        const seatName = seatElement ? seatElement.querySelector('h4')?.textContent : selectedConstId;

        resultText.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> <strong>${selectedText}</strong> का शिक्षक क्षेत्र: <span class="text-success">${seatName}</span> है।`;
        resultLink.href = '#' + selectedConstId;
        resultBox.classList.remove('d-none');

        // Smooth scroll to card
        if (seatElement) {
            seatElement.scrollIntoView({ behavior: 'smooth', block: 'center' });
            seatElement.style.transition = 'transform 0.4s ease, box-shadow 0.4s ease';
            seatElement.style.transform = 'scale(1.03)';
            setTimeout(() => {
                seatElement.style.transform = '';
            }, 1200);
        }
    }

    if (btnFind) {
        btnFind.addEventListener('click', performDistrictFind);
    }
    if (selectDistrict) {
        selectDistrict.addEventListener('change', performDistrictFind);
    }

    // Filter Logic (All / Election / Active)
    const filterBtns = document.querySelectorAll('.filter-btn-teach');
    const seatItems = document.querySelectorAll('.seat-item');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const searchInput = document.getElementById('seatSearchInput');

    function applyFilters() {
        const activeFilter = document.querySelector('.filter-btn-teach.active')?.getAttribute('data-filter') || 'all';
        const searchKeyword = (searchInput?.value || '').trim().toLowerCase();
        let visibleCount = 0;

        seatItems.forEach(item => {
            const itemStatus = item.getAttribute('data-status');
            const itemSearch = item.getAttribute('data-search') || '';

            const matchesStatus = (activeFilter === 'all' || itemStatus === activeFilter);
            const matchesSearch = (!searchKeyword || itemSearch.includes(searchKeyword));

            if (matchesStatus && matchesSearch) {
                item.classList.remove('d-none');
                visibleCount++;
            } else {
                item.classList.add('d-none');
            }
        });

        if (visibleCount === 0) {
            noResultsMsg?.classList.remove('d-none');
        } else {
            noResultsMsg?.classList.add('d-none');
        }
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            applyFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
