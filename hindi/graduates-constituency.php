<?php
/**
 * BiharElection.com - बिहार विधान परिषद (Vidhan Parishad)
 * 6 स्नातक निर्वाचन क्षेत्रों की संपूर्ण डायरेक्टरी एवं गाइड
 */
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'बिहार स्नातक निर्वाचन क्षेत्र (6 सीटें): विधान परिषद एमएलसी चुनावी गाइड, फॉर्म 18 एवं 2026 चुनाव विवरण';
$pageDescription = 'बिहार विधान परिषद के सभी 6 स्नातक निर्वाचन क्षेत्रों (Graduates Constituencies) की आधिकारिक जानकारी: पटना, तिरहुत, दरभंगा, कोसी, गया एवं सारण। फॉर्म 18 मतदाता पंजीकरण नियम, जिलेवार सूची एवं 2026 द्विवार्षिक चुनाव अपडेट।';
$pageKeywords = 'बिहार स्नातक निर्वाचन क्षेत्र, विधान परिषद स्नातक एमएलसी, पटना स्नातक, तिरहुत स्नातक, दरभंगा स्नातक, कोसी स्नातक, गया स्नातक, सारण स्नातक, फॉर्म 18 बिहार, अनुच्छेद 171';
$pageCanonical = HINDI_BASE_URL . 'graduates-constituency';
$activeNav = 'mlc';

// 6 Graduates Constituencies Data
$graduatesSeats = [
    [
        'id' => 'patna-graduates',
        'name' => 'Patna Graduates',
        'name_hi' => 'पटना स्नातक निर्वाचन क्षेत्र',
        'hq' => 'पटना',
        'ro' => 'Divisional Commissioner, Patna',
        'ro_hi' => 'प्रमंडलीय आयुक्त, पटना प्रमंडल',
        'districts' => ['Patna', 'Nalanda', 'Nawada'],
        'districts_hi' => ['पटना', 'नालंदा', 'नवादा'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'नीरज कुमार (जदयू) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'राज्य की राजधानी पटना सहित नालंदा और नवादा जिलों के स्नातक मतदाताओं का प्रतिनिधित्व। सिविल सेवा अभ्यर्थियों और विश्वविद्यालय स्नातकों का प्रमुख गढ़।'
    ],
    [
        'id' => 'tirhut-graduates',
        'name' => 'Tirhut Graduates',
        'name_hi' => 'तिरहुत स्नातक निर्वाचन क्षेत्र',
        'hq' => 'मुजफ्फरपुर',
        'ro' => 'Divisional Commissioner, Tirhut (Muzaffarpur)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, तिरहुत (मुजफ्फरपुर)',
        'districts' => ['Muzaffarpur', 'Vaishali', 'Sitamarhi', 'Sheohar'],
        'districts_hi' => ['मुजफ्फरपुर', 'वैशाली', 'सीतामढ़ी', 'शिवहर'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'देवेश चंद्र ठाकुर (जदयू) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'बीआरए बिहार विश्वविद्यालय मुजफ्फरपुर के अंतर्गत 4 प्रमुख उत्तर बिहार जिलों (मुजफ्फरपुर, वैशाली, सीतामढ़ी, शिवहर) का विस्तृत शैक्षणिक क्षेत्र।'
    ],
    [
        'id' => 'darbhanga-graduates',
        'name' => 'Darbhanga Graduates',
        'name_hi' => 'दरभंगा स्नातक निर्वाचन क्षेत्र',
        'hq' => 'दरभंगा',
        'ro' => 'Divisional Commissioner, Darbhanga',
        'ro_hi' => 'प्रमंडलीय आयुक्त, दरभंगा प्रमंडल',
        'districts' => ['Darbhanga', 'Madhubani', 'Samastipur', 'Begusarai'],
        'districts_hi' => ['दरभंगा', 'मधुबनी', 'समस्तीपुर', 'बेगूसराय'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'सर्वेश कुमार (निर्दलीय) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'मिथिलांचल के 4 जिलों (दरभंगा, मधुबनी, समस्तीपुर, बेगूसराय) का प्रतिनिधित्व। ललित नारायण मिथिला विवि एवं औद्योगिक बेल्ट बेगूसराय का संयुक्त क्षेत्र।'
    ],
    [
        'id' => 'kosi-graduates',
        'name' => 'Kosi Graduates',
        'name_hi' => 'कोसी स्नातक निर्वाचन क्षेत्र',
        'hq' => 'सहरसा / पूर्णिया',
        'ro' => 'Divisional Commissioner, Kosi / Purnia',
        'ro_hi' => 'प्रमंडलीय आयुक्त, कोसी / पूर्णिया प्रमंडल',
        'districts' => ['Saharsa', 'Supaul', 'Madhepura', 'Purnia', 'Araria', 'Kishanganj', 'Katihar', 'Bhagalpur', 'Banka', 'Munger', 'Jamui', 'Lakhisarai', 'Sheikhpura', 'Khagaria'],
        'districts_hi' => ['सहरसा', 'सुपौल', 'मधेपुरा', 'पूर्णिया', 'अररिया', 'किशनगंज', 'कटिहार', 'भागलपुर', 'बांका', 'मुंगेर', 'जमुई', 'लखीसराय', 'शेखपुरा', 'खगड़िया'],
        'status' => 'election_2026',
        'status_label' => 'द्विवार्षिक चुनाव 2026 सक्रिय',
        'poll_date' => '23 अक्टूबर 2026',
        'result_date' => '27 अक्टूबर 2026',
        'incumbent' => 'डॉ. एन. के. यादव (भाजपा) / 2026 चुनाव हेतु रिक्त',
        'desc' => 'भौगोलिक दृष्टि से सबसे बड़ा निर्वाचन क्षेत्र, जिसमें 14 जिले (कोसी, पूर्णिया, भागलपुर, मुंगेर प्रमंडल) शामिल हैं। बीएनएमयू, टीएमबीयू और पूर्णिया विवि का क्षेत्र।'
    ],
    [
        'id' => 'gaya-graduates',
        'name' => 'Gaya Graduates',
        'name_hi' => 'गया स्नातक निर्वाचन क्षेत्र',
        'hq' => 'गया',
        'ro' => 'Divisional Commissioner, Magadh (Gaya)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, मगध (गया)',
        'districts' => ['Gaya', 'Jehanabad', 'Arwal', 'Aurangabad', 'Rohtas', 'Kaimur', 'Bhojpur', 'Buxar'],
        'districts_hi' => ['गया', 'जहानाबाद', 'अरवल', 'औरंगाबाद', 'रोहतास', 'कैमूर', 'भोजपुर', 'बक्सर'],
        'status' => 'active_term',
        'status_label' => 'सक्रिय कार्यकाल',
        'poll_date' => 'अगला चुनाव 2029',
        'result_date' => '-',
        'incumbent' => 'अवधेश नारायण सिंह (भाजपा)',
        'desc' => 'मगध एवं शाहाबाद प्रमंडल के 8 जिलों (गया, जहानाबाद, अरवल, औरंगाबाद, रोहतास, कैमूर, भोजपुर, बक्सर) का क्षेत्र। मगध विवि एवं वीकेएसयू आरा का क्षेत्र।'
    ],
    [
        'id' => 'saran-graduates',
        'name' => 'Saran Graduates',
        'name_hi' => 'सारण स्नातक निर्वाचन क्षेत्र',
        'hq' => 'छपरा',
        'ro' => 'Divisional Commissioner, Saran (Chapra)',
        'ro_hi' => 'प्रमंडलीय आयुक्त, सारण (छपरा)',
        'districts' => ['Saran', 'Siwan', 'Gopalganj', 'East Champaran', 'West Champaran'],
        'districts_hi' => ['सारण', 'सिवान', 'गोपालगंज', 'पूर्वी चंपारण', 'पश्चिमी चंपारण'],
        'status' => 'active_term',
        'status_label' => 'सक्रिय कार्यकाल',
        'poll_date' => 'अगला चुनाव 2029',
        'result_date' => '-',
        'incumbent' => 'बीरेंद्र नारायण यादव (जदयू)',
        'desc' => 'सारण एवं चंपारण क्षेत्र के 5 जिलों (छपरा, सिवान, गोपालगंज, मोतिहारी, बेतिया) का प्रतिनिधित्व। जय प्रकाश विश्वविद्यालय छपरा का मुख्य कार्यक्षेत्र।'
    ]
];

// All Bihar districts mapped to Graduates Constituency
$allDistrictsMap = [
    'अररिया' => 'kosi-graduates',
    'अरवल' => 'gaya-graduates',
    'औरंगाबाद' => 'gaya-graduates',
    'कटिहार' => 'kosi-graduates',
    'किशनगंज' => 'kosi-graduates',
    'कैमूर' => 'gaya-graduates',
    'खगड़िया' => 'kosi-graduates',
    'गया' => 'gaya-graduates',
    'गोपालगंज' => 'saran-graduates',
    'जमुई' => 'kosi-graduates',
    'जहानाबाद' => 'gaya-graduates',
    'दरभंगा' => 'darbhanga-graduates',
    'नवादा' => 'patna-graduates',
    'नालंदा' => 'patna-graduates',
    'पटना' => 'patna-graduates',
    'पश्चिम चंपारण' => 'saran-graduates',
    'पूर्णिया' => 'kosi-graduates',
    'पूर्वी चंपारण' => 'saran-graduates',
    'बक्सर' => 'gaya-graduates',
    'बांका' => 'kosi-graduates',
    'बेगूसराय' => 'darbhanga-graduates',
    'भागलपुर' => 'kosi-graduates',
    'भोजपुर' => 'gaya-graduates',
    'मधेपुरा' => 'kosi-graduates',
    'मधुबनी' => 'darbhanga-graduates',
    'मुंगेर' => 'kosi-graduates',
    'मुजफ्फरपुर' => 'tirhut-graduates',
    'रोहतास' => 'gaya-graduates',
    'लखीसराय' => 'kosi-graduates',
    'वैशाली' => 'tirhut-graduates',
    'शिवहर' => 'tirhut-graduates',
    'शेखपुरा' => 'kosi-graduates',
    'समस्तीपुर' => 'darbhanga-graduates',
    'सहरसा' => 'kosi-graduates',
    'सारण' => 'saran-graduates',
    'सीतामढ़ी' => 'tirhut-graduates',
    'सीवान' => 'saran-graduates',
    'सुपौल' => 'kosi-graduates'
];

require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Gradient & Glassmorphism Theme */
.grad-hero {
    background: linear-gradient(135deg, #091e3a 0%, #1e3a8a 50%, #0a1128 100%);
    color: #fff;
    padding: 55px 0 45px;
    position: relative;
    overflow: hidden;
}
.grad-hero::before {
    content: '';
    position: absolute;
    top: -40%;
    right: -10%;
    width: 550px;
    height: 550px;
    background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}
.grad-hero::after {
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
.stat-box {
    background: rgba(255, 255, 255, 0.08);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    border-radius: 16px;
    padding: 1.1rem 1.25rem;
    color: #ffffff;
    transition: transform 0.2s ease, background 0.2s ease;
}
.stat-box:hover {
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
    background: linear-gradient(90deg, #2563eb, #3b82f6);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.constituency-card.election-active::before {
    background: linear-gradient(90deg, #ef4444, #f59e0b);
    opacity: 1;
}
.constituency-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 18px 36px -6px rgba(37, 99, 235, 0.18);
    border-color: #3b82f6;
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
    background-color: #eff6ff;
    color: #1d4ed8;
    border-color: #bfdbfe;
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
    border-color: #93c5fd;
    box-shadow: 0 10px 25px rgba(37, 99, 235, 0.09);
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
.filter-btn.active {
    background-color: #1e3a8a !important;
    color: #ffffff !important;
    border-color: #1e3a8a !important;
}
.search-input-box {
    border-radius: 50px;
    padding-left: 2.75rem;
    border: 2px solid #e2e8f0;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.search-input-box:focus {
    border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
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
    background-color: #eff6ff;
    color: #1e40af;
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
<section class="grad-hero">
    <div class="container text-start position-relative">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                🎓 अनुच्छेद 171(3)(a) &bull; भारतीय संविधान
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                कुल 6 स्नातक निर्वाचन क्षेत्र
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
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">स्नातक निर्वाचन क्षेत्र</li>
            </ol>
        </nav>

        <h1 class="display-5 fw-extrabold text-white mb-2">
            बिहार स्नातक निर्वाचन क्षेत्र (Graduates' Constituencies) <br>
            <span style="color: #f59e0b;">विधान परिषद की 6 विशेष स्नातक सीटें</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.08rem; max-width: 900px;">
            बिहार विधान परिषद के सभी 6 स्नातक निर्वाचन क्षेत्रों की आधिकारिक मार्गदर्शिका। संवैधानिक प्रावधान, फॉर्म 18 मतदाता पंजीकरण प्रक्रिया, जिलेवार सीमाएं और 2026 के द्विवार्षिक चुनाव की संपूर्ण जानकारी।
        </p>

        <!-- Key Metrics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">कुल स्नातक सीटें</div>
                    <div class="fs-4 fw-extrabold text-white">6 सीटें</div>
                    <div class="text-warning extra-small">विधान परिषद का 1/12वां भाग</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">2026 चुनाव वाली सीटें</div>
                    <div class="fs-4 fw-extrabold text-danger blink-anim">4 सीटें</div>
                    <div class="text-white-50 extra-small">पटना, तिरहुत, दरभंगा, कोसी</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">मतदान की तिथि</div>
                    <div class="fs-4 fw-extrabold text-white">23 अक्टूबर 2026</div>
                    <div class="text-success extra-small">शुक्रवार &bull; सुबह 8 से शाम 4</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-box">
                    <div class="text-white-50 extra-small fw-bold text-uppercase">मतगणना / परिणाम</div>
                    <div class="fs-4 fw-extrabold text-white">27 अक्टूबर 2026</div>
                    <div class="text-info extra-small">वरीयता मत गणना (PR-STV)</div>
                </div>
            </div>
        </div>

        <!-- Action Links & Quick District Finder -->
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="btn btn-danger fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2 rounded-pill">
                <i class="bi bi-broadcast"></i> 2026 चुनाव हब देखें
            </a>
            <a href="<?php echo HINDI_BASE_URL; ?>teachers-constituency" class="btn btn-success fw-bold px-3.5 py-2.5 shadow-sm d-inline-flex align-items-center gap-2 rounded-pill">
                <i class="bi bi-book"></i> 6 शिक्षक निर्वाचन क्षेत्र
            </a>
            <a href="<?php echo HINDI_BASE_URL; ?>mlc" class="btn btn-outline-light fw-bold px-3.5 py-2.5 shadow-sm rounded-pill">
                <i class="bi bi-journal-text me-1"></i> 75 एमएलसी डायरेक्टरी
            </a>
            <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="btn btn-sm btn-outline-warning ms-auto rounded-pill px-3 py-2 fw-semibold">
                <i class="bi bi-translate me-1"></i> In English
            </a>
        </div>
    </div>
</section>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Interactive District Finder Tool -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #f0fdf4 0%, #e0f2fe 100%); border: 1px solid #bae6fd !important;">
        <div class="p-3.5 p-md-4">
            <div class="row align-items-center g-3">
                <div class="col-lg-5">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white p-2 rounded-circle fs-5 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="bi bi-geo-alt-fill"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">अपना जिला चुनें &bull; तुरंत अपना स्नातक क्षेत्र जानें</h5>
                            <small class="text-muted">बिहार के 38 जिलों में से अपना जिला चुनकर निर्वाचन क्षेत्र और विवरण देखें</small>
                        </div>
                    </div>
                </div>
                <div class="col-lg-7">
                    <div class="d-flex flex-column flex-sm-row gap-2">
                        <select id="districtFinderSelect" class="form-select form-select-lg rounded-pill border-2 border-primary fw-semibold text-navy">
                            <option value="">-- बिहार का जिला चुनें (जैसे पटना, मुजफ्फरपुर, गया) --</option>
                            <?php 
                            ksort($allDistrictsMap);
                            foreach ($allDistrictsMap as $distName => $constId): ?>
                                <option value="<?php echo $constId; ?>"><?php echo htmlspecialchars($distName); ?> जिला</option>
                            <?php endforeach; ?>
                        </select>
                        <button id="btnFindDistrict" class="btn btn-primary btn-lg rounded-pill px-4 fw-bold text-nowrap shadow-sm">
                            <i class="bi bi-search me-1"></i> खोजें
                        </button>
                    </div>
                </div>
            </div>
            <!-- Dynamic Result Alert -->
            <div id="districtResultBox" class="mt-3 p-3 bg-white rounded-3 border border-primary d-none">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div id="districtResultText" class="fw-bold text-navy"></div>
                    <a id="districtResultLink" href="#patna-graduates" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">
                        सीट कार्ड पर जाएं <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Active 2026 Election Notice for Graduates -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 text-white overflow-hidden" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 50%, #1d4ed8 100%);">
        <div class="p-4 d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-award-fill"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill text-uppercase">2026 द्विवार्षिक चुनाव</span>
                        <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-2.5 py-1 rounded-pill">गजट: TCGCGajat2026</span>
                    </div>
                    <h4 class="fw-extrabold text-white mb-1">4 स्नातक सीटों पर चुनाव प्रक्रिया जारी</h4>
                    <p class="text-white-50 mb-0 small" style="max-width: 820px;">
                        <strong>पटना</strong>, <strong>तिरहुत</strong>, <strong>दरभंगा</strong> एवं <strong>कोसी</strong> स्नातक निर्वाचन क्षेत्रों में वर्तमान सदस्यों का कार्यकाल पूर्ण होने के कारण चुनाव जारी है। मतदान <strong>23 अक्टूबर 2026</strong> को और मतगणना <strong>27 अक्टूबर 2026</strong> को होगी।
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
                <i class="bi bi-mortarboard text-primary me-2"></i> सभी 6 स्नातक निर्वाचन क्षेत्र (बिहार)
            </h3>
            <p class="text-muted small mb-0">बिहार के सभी 38 जिलों को 6 प्रमंडलीय स्नातक निर्वाचन क्षेत्रों में विभाजित किया गया है</p>
        </div>

        <div class="d-flex flex-wrap gap-2 align-items-center">
            <!-- Filter Pills -->
            <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                <button type="button" class="btn btn-sm rounded-pill fw-bold filter-btn active" data-filter="all">सभी 6 सीटें</button>
                <button type="button" class="btn btn-sm rounded-pill fw-bold filter-btn text-danger" data-filter="election">⚡ चुनाव 2026 (4)</button>
                <button type="button" class="btn btn-sm rounded-pill fw-bold filter-btn text-success" data-filter="active">✓ सक्रिय (2)</button>
            </div>

            <!-- Instant Search Box -->
            <div class="position-relative" style="min-width: 220px;">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="seatSearchInput" class="form-control form-control-sm search-input-box" placeholder="सीट या जिला खोजें...">
            </div>
        </div>
    </div>

    <!-- 6 Graduates' Constituencies Grid -->
    <div class="row g-4 mb-5" id="seatsGrid">
        <?php foreach ($graduatesSeats as $seat): 
            $isOngoing = ($seat['status'] === 'election_2026');
            $districtsSearchStr = mb_strtolower(implode(' ', $seat['districts_hi']) . ' ' . implode(' ', $seat['districts']) . ' ' . $seat['name_hi'] . ' ' . $seat['name']);
        ?>
            <div class="col-md-6 col-lg-4 seat-item" id="<?php echo $seat['id']; ?>" data-status="<?php echo $isOngoing ? 'election' : 'active'; ?>" data-search="<?php echo htmlspecialchars($districtsSearchStr); ?>">
                <div class="constituency-card p-4 <?php echo $isOngoing ? 'election-active' : ''; ?>">
                    <div>
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1 rounded-pill extra-small fw-bold">
                                🎓 स्नातक कोटा
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
                            <a href="<?php echo HINDI_BASE_URL; ?>mlc" class="btn btn-sm btn-outline-primary w-100 rounded-pill fw-semibold py-2 d-flex align-items-center justify-content-center gap-1">
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
        <i class="bi bi-search fs-2 d-block mb-2 text-primary"></i>
        <h5>कोई सीट या जिला नहीं मिला</h5>
        <p class="text-muted mb-0 small">कृपया सही जिला या सीट का नाम लिखें (उदा. पटना, तिरहुत, मुजफ्फरपुर)</p>
    </div>

    <!-- Voter Registration & Electoral Mechanism Dossier -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5 mb-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <div>
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-1.5 rounded-pill mb-1">
                    फॉर्म 18 संपूर्ण गाइड
                </span>
                <h3 class="fw-extrabold text-navy mb-0">
                    <i class="bi bi-file-earmark-check-fill text-primary me-1"></i> स्नातक मतदाता पात्रता एवं पंजीकरण नियमावली
                </h3>
            </div>
            <a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary rounded-pill fw-bold btn-sm px-3 py-1.5">
                <i class="bi bi-box-arrow-up-right me-1"></i> ECI Voters Portal
            </a>
        </div>

        <div class="row g-4">
            <!-- Step 1 -->
            <div class="col-lg-6">
                <div class="info-step-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-weight: 800;">
                            1
                        </div>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">स्नातक मतदाता कौन बन सकता है?</h5>
                            <small class="text-muted">योग्यता एवं संवैधानिक शर्तें</small>
                        </div>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>नागरिकता:</strong> भारत का नागरिक होना अनिवार्य है।</li>
                        <li><strong>साधारण निवास:</strong> संबंधित स्नातक निर्वाचन क्षेत्र के अंतर्गत सामान्य रूप से निवासी होना चाहिए।</li>
                        <li><strong>3 वर्ष पूर्व स्नातक:</strong> अर्हक तिथि (1 नवंबर) से कम से कम 3 वर्ष पूर्व किसी मान्यता प्राप्त विश्वविद्यालय से स्नातक उपाधि या समकक्ष डिग्री प्राप्त की हो।</li>
                        <li><strong>पुनः मतदाता सूची निर्माण:</strong> विधान परिषद के स्नातक चुनावों के लिए प्रत्येक चुनाव से पूर्व मतदाता सूची नए सिरे से (De-novo) तैयार की जाती है। सामान्य वोटर कार्ड स्वतः मान्य नहीं होता।</li>
                    </ul>
                </div>
            </div>

            <!-- Step 2 -->
            <div class="col-lg-6">
                <div class="info-step-card">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; font-weight: 800;">
                            2
                        </div>
                        <div>
                            <h5 class="fw-bold text-navy mb-0">फॉर्म 18 द्वारा आवेदन कैसे करें?</h5>
                            <small class="text-muted">ऑनलाइन एवं ऑफलाइन प्रक्रिया</small>
                        </div>
                    </div>
                    <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                        <li><strong>ऑनलाइन आवेदन:</strong> भारत निर्वाचन आयोग के पोर्टल (<a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>) अथवा मुख्य निर्वाचन पदाधिकारी, बिहार के पोर्टल पर करें।</li>
                        <li><strong>ऑफलाइन आवेदन:</strong> <strong>फॉर्म 18</strong> भरकर प्रखंड विकास पदाधिकारी (BDO) / अनुमंडल दंडाधिकारी (SDM) / निर्वाचक निबंधन पदाधिकारी (ERO) के पास जमा करें।</li>
                        <li><strong>आवश्यक दस्तावेज:</strong>
                            <ul class="mt-1">
                                <li>स्नातक डिग्री / अंकपत्र की स्वप्रमाणित प्रति।</li>
                                <li>निवास प्रमाण पत्र (आधार कार्ड, मतदाता पहचान पत्र, पासपोर्ट)।</li>
                                <li>हालिया पासपोर्ट साइज फोटो।</li>
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
                    <h5 class="fw-bold text-navy mb-2">संवैधानिक ढांचा एवं एकल संक्रमणीय मत (PR-STV) प्रणाली</h5>
                    <p class="text-muted small mb-2">
                        भारतीय संविधान के <strong>अनुच्छेद 171(3)(a)</strong> के तहत राज्य विधान परिषद के कुल सदस्यों का 1/12वां भाग (बिहार में 75 में से 6 सीटें) स्नातक मतदाताओं द्वारा चुना जाता है।
                    </p>
                    <p class="text-muted small mb-0">
                        मतदान मतपत्र (Ballot Paper) पर <strong>एकल संक्रमणीय मत द्वारा आनुपातिक प्रतिनिधित्व</strong> पद्धति से होता है, जिसमें मतदाता आधिकारिक बैंगनी स्केच पेन से उम्मीदवारों के आगे वरीयता (1, 2, 3...) अंकित करते हैं।
                    </p>
                </div>
                <div class="col-lg-5">
                    <div class="p-3.5 bg-light rounded-4 border text-start">
                        <div class="fw-bold text-navy small mb-1"><i class="bi bi-calculator text-primary me-1"></i> कोटा (जीतने हेतु आवश्यक मत मूल्य):</div>
                        <code class="d-block p-2 bg-white rounded border text-danger fw-bold text-center mb-2 font-monospace">
                            कोटा = [ (कुल वैध मत / (सीट + 1)) + 1 ]
                        </code>
                        <small class="text-muted extra-small d-block">
                            एकल रिक्ति वाले चुनाव में 50% से अधिक वैध मत मूल्य (50% + 1) प्राप्त करने वाले उम्मीदवार को विजयी घोषित किया जाता है।
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Frequently Asked Questions (FAQ) Accordion -->
    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-lg-5 mb-5">
        <h3 class="fw-extrabold text-navy mb-4 d-flex align-items-center gap-2">
            <i class="bi bi-question-circle-fill text-warning"></i> स्नातक निर्वाचन क्षेत्र : अक्सर पूछे जाने वाले प्रश्न (FAQs)
        </h3>

        <div class="accordion faq-accordion" id="gradFaqAccordion">
            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading1">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1" aria-expanded="true" aria-controls="faq1">
                        1. क्या सामान्य विधानसभा वोटर कार्ड होने पर स्नातक चुनाव में वोट दिया जा सकता है?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#gradFaqAccordion">
                    <div class="accordion-body text-muted small">
                        <strong>नहीं।</strong> विधान परिषद स्नातक चुनाव के लिए प्रत्येक द्विवार्षिक चुनाव से पूर्व नए सिरे से (De-novo) मतदाता सूची तैयार की जाती है। यदि आपके पास सामान्य वोटर कार्ड है, तब भी आपको <strong>फॉर्म 18</strong> भरकर स्नातक मतदाता के रूप में पंजीकरण कराना अनिवार्य है।
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading2">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2" aria-expanded="false" aria-controls="faq2">
                        2. स्नातक डिग्री कितने वर्ष पुरानी होनी चाहिए?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#gradFaqAccordion">
                    <div class="accordion-body text-muted small">
                        निर्वाचन आयोग द्वारा निर्धारित अर्हक तिथि (Qualifying Date - प्रायः 1 नवंबर) से कम से कम <strong>3 वर्ष पूर्व</strong> स्नातक की डिग्री उत्तीर्ण होनी चाहिए। उदाहरण के लिए, 2026 के चुनाव के लिए 1 नवंबर 2022 या उससे पूर्व की डिग्री मान्य होगी।
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading3">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3" aria-expanded="false" aria-controls="faq3">
                        3. यदि मैंने दूसरे राज्य के विश्वविद्यालय से स्नातक किया है, तो क्या मैं बिहार में वोट दे सकता हूँ?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#gradFaqAccordion">
                    <div class="accordion-body text-muted small">
                        <strong>हाँ।</strong> यदि आपकी डिग्री भारत के किसी भी यूजीसी (UGC) अथवा सरकार द्वारा मान्यता प्राप्त विश्वविद्यालय से है, और आप वर्तमान में बिहार के संबंधित स्नातक निर्वाचन क्षेत्र के साधारण निवासी हैं, तो आप फॉर्म 18 भरकर मतदाता बन सकते हैं।
                    </div>
                </div>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="faqHeading4">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4" aria-expanded="false" aria-controls="faq4">
                        4. मतदान मतपत्र (Ballot Paper) से होता है या ईवीएम (EVM) से?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#gradFaqAccordion">
                    <div class="accordion-body text-muted small">
                        विधान परिषद स्नातक सीटों पर चुनाव <strong>मतपत्र (Ballot Paper)</strong> और एकल संक्रमणीय मत (PR-STV) प्रणाली द्वारा होता है। इसमें मतदाता उम्मीदवारों के नाम के आगे 1, 2, 3 जैसी वरीयता अंकित करते हैं। इसके लिए मतदान केंद्र पर आयोग द्वारा विशेष बैंगनी स्केच पेन उपलब्ध कराया जाता है।
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Navigation Links -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo HINDI_BASE_URL; ?>teachers-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-success"><i class="bi bi-book-half"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">शिक्षक निर्वाचन क्षेत्र</h6>
                        <small class="text-muted">6 शिक्षक एमएलसी सीटों का विवरण</small>
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
                    <div class="fs-2 text-primary"><i class="bi bi-building"></i></div>
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

        resultText.innerHTML = `<i class="bi bi-check-circle-fill text-success me-1"></i> <strong>${selectedText}</strong> का स्नातक क्षेत्र: <span class="text-primary">${seatName}</span> है।`;
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
    const filterBtns = document.querySelectorAll('.filter-btn');
    const seatItems = document.querySelectorAll('.seat-item');
    const noResultsMsg = document.getElementById('noResultsMsg');
    const searchInput = document.getElementById('seatSearchInput');

    function applyFilters() {
        const activeFilter = document.querySelector('.filter-btn.active')?.getAttribute('data-filter') || 'all';
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
