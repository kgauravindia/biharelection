<?php
/**
 * BiharElection.com - MLC Constituency & Polling Booths Template Renderer
 * Renders dedicated pages for all 8 Teachers' and Graduates' Constituencies
 */
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/mlc_helper.php';

if (!isset($constituencyKey) || empty($constituencyKey)) {
    header("Location: " . SITE_URL . "/teachers-constituency");
    exit;
}

$cData = getMlcConstituency($constituencyKey);
if (!$cData) {
    header("HTTP/1.0 404 Not Found");
    require_once __DIR__ . '/../404.php';
    exit;
}

$isTeachers = ($cData['type'] === 'teachers');
$typeTitle = $isTeachers ? "Teachers' Constituency" : "Graduates' Constituency";
$typeTitleHi = $isTeachers ? "शिक्षक निर्वाचन क्षेत्र" : "स्नातक निर्वाचन क्षेत्र";
$article = $isTeachers ? 'Article 171(3)(b)' : 'Article 171(3)(a)';
$formType = $isTeachers ? 'Form 19' : 'Form 18';

// SEO Setup
$candCount = !empty($cData['candidates']) ? count($cData['candidates']) : 0;
$candText = ($candCount > 0) ? "{$candCount} candidates, " : "";
$pageTitle = htmlspecialchars($cData['name']) . " (" . $cData['name_hi'] . "): " . $cData['booth_count'] . " Polling Booths, Voter Roll & 2026 MLC Election Guide";
$pageDescription = "{$cData['name']} ({$cData['name_hi']}) 2026 MLC guide: {$candText}{$cData['booth_count']} booths, {$formType} voter roll & CEO Bihar updates.";
$pageKeywords = $cData['name'] . ", " . $cData['name_hi'] . ", Bihar MLC Election 2026, " . $cData['name'] . " Booth List, CEO Bihar " . $cData['segment_code'] . ", " . $formType . " Bihar, Vidhan Parishad Election 2026, " . implode(', ', $cData['districts']);
$pageCanonical = SITE_URL . '/' . $cData['slug'];
$pageImage = '';
if (!empty($cData['candidates'])) {
    foreach ($cData['candidates'] as $cand) {
        if (!empty($cand['photo']) && file_exists(__DIR__ . '/../' . ltrim($cand['photo'], '/'))) {
            $pageImage = SITE_URL . '/' . ltrim($cand['photo'], '/');
            break;
        }
    }
}
$activeNav = 'mlc';

// Official 2026 Biennial Election Poll & Turnout Data (CEO Bihar Final VTR & Press Note 23-10-2026)
$officialPollStats = [
    'patna-graduate-constituency' => [
        'turnout_final' => 57.56,
        'turnout_interim_4pm' => 36.19,
        'candidates_count' => 18,
        'electors' => ['male' => 66462, 'female' => 21117, 'third' => 4, 'total' => 87583],
        'booths_total' => 134,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Neeraj Kumar',
        'winner_name_hi' => 'नीरज कुमार',
        'party' => 'JD(U)',
        'alliance' => 'NDA',
        'verdict' => 'Retained seat for 4th consecutive term, defeating Dilip Patel (RJD) & Dr. Anuj Singh (Jan Suraaj supported).',
        'verdict_hi' => 'लगातार चौथी बार ऐतिहासिक जीत दर्ज की; राजद के दिलीप पटेल और जन सुराज समर्थित अनुज सिंह को पछाड़ा।'
    ],
    'tirhut-graduate-constituency' => [
        'turnout_final' => 48.51,
        'turnout_interim_4pm' => 42.15,
        'candidates_count' => 17,
        'electors' => ['male' => 97998, 'female' => 45970, 'third' => 20, 'total' => 143988],
        'booths_total' => 145,
        'result_status' => 'COUNTING IN PROGRESS (अंतिम कोटा चक्र)',
        'result_badge' => 'bg-warning text-dark blink-anim',
        'winner_name' => 'Banshidhar Brajwasi',
        'winner_name_hi' => 'वंशीधर ब्रजवासी',
        'party' => 'IND',
        'alliance' => 'Independent (Incumbent)',
        'verdict' => 'Sitting MLC leading towards statutory quota over Abhishek Jha (JD[U]) and Dr. Rashmi Gautam (RJD).',
        'verdict_hi' => 'निवर्तमान निर्दलीय एमएलसी वंशीधर ब्रजवासी जदयू के अभिषेक झा और राजद की डॉ. रश्मि गौतम के खिलाफ अंतिम कोटा चक्र में मजबूत बढ़त पर।'
    ],
    'darbhanga-graduate-constituency' => [
        'turnout_final' => 54.73,
        'turnout_interim_4pm' => 42.00,
        'candidates_count' => 40,
        'electors' => ['male' => 90006, 'female' => 36938, 'third' => 7, 'total' => 126951],
        'booths_total' => 138,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Dr. Sarvesh Kumar',
        'winner_name_hi' => 'डॉ. सर्वेश कुमार',
        'party' => 'BJP',
        'alliance' => 'NDA',
        'verdict' => 'Retained seat by 237 votes margin over challenger Anil Kumar Jha (RJD) after exhaustive elimination rounds.',
        'verdict_hi' => 'कांटे के मुकाबले में राजद के अनिल कुमार झा को 237 मतों से हराकर दरभंगा स्नातक सीट जीती।'
    ],
    'kosi-graduate-constituency' => [
        'turnout_final' => 54.73,
        'turnout_interim_4pm' => 52.51,
        'candidates_count' => 15,
        'electors' => ['male' => 101056, 'female' => 33013, 'third' => 3, 'total' => 134072],
        'booths_total' => 181,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Dr. N.K. Yadav',
        'winner_name_hi' => 'डॉ. एनके यादव',
        'party' => 'BJP',
        'alliance' => 'NDA',
        'verdict' => 'Secured hat-trick 3rd consecutive term victory across 14 districts of Kosi, Purnia, Bhagalpur, and Munger.',
        'verdict_hi' => 'लगातार तीसरी बार हैट्रिक जीत दर्ज की; राजद के नितेश कुमार और जन सुराज के रजनीश रंजन को पराजित किया।'
    ],
    'patna-teacher-constituency' => [
        'turnout_final' => 74.75,
        'turnout_interim_4pm' => 56.89,
        'candidates_count' => 18,
        'electors' => ['male' => 8413, 'female' => 3306, 'third' => 0, 'total' => 11719],
        'booths_total' => 80,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Dr. Sanjeev Kumar',
        'winner_name_hi' => 'डॉ. संजीव कुमार (सिंह)',
        'party' => 'RJD',
        'alliance' => 'Mahagathbandhan',
        'verdict' => 'Historic upset victory by 800+ votes, unseating 5-term BJP veteran Nawal Kishore Yadav.',
        'verdict_hi' => 'बड़ा उलटफेर: राजद के डॉ. संजीव कुमार ने 5 बार के भाजपा एमएलसी नवल किशोर यादव को 800+ मतों से हराकर जीत हासिल की।'
    ],
    'tirhut-teacher-constituency' => [
        'turnout_final' => 82.99,
        'turnout_interim_4pm' => 78.10,
        'candidates_count' => 11,
        'electors' => ['male' => 6507, 'female' => 2250, 'third' => 3, 'total' => 8760],
        'booths_total' => 58,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Dr. Narendra Prasad Singh',
        'winner_name_hi' => 'डॉ. नरेंद्र प्रसाद सिंह',
        'party' => 'BJP',
        'alliance' => 'NDA',
        'verdict' => 'Declared elected by 594 votes margin (2,945 votes) over Independent Bhushan Jha (2,351 votes).',
        'verdict_hi' => 'भाजपा के डॉ. नरेंद्र प्रसाद सिंह 594 मतों से विजयी घोषित (निर्दलीय भूषण झा को हराया)।'
    ],
    'darbhanga-teacher-constituency' => [
        'turnout_final' => 85.87,
        'turnout_interim_4pm' => 79.00,
        'candidates_count' => 13,
        'electors' => ['male' => 6958, 'female' => 2068, 'third' => 0, 'total' => 9026],
        'booths_total' => 99,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Dr. Suresh Prasad Rai',
        'winner_name_hi' => 'डॉ. सुरेश प्रसाद राय',
        'party' => 'Jan Suraaj',
        'alliance' => 'Jan Suraaj Party',
        'verdict' => 'Landmark first legislative council win for Jan Suraaj Party, defeating Congress heavyweight Dr. Madan Mohan Jha.',
        'verdict_hi' => 'जन सुराज पार्टी की पहली ऐतिहासिक जीत: 4 बार के एमएलसी व पूर्व मंत्री डॉ. मदन मोहन झा को पराजित किया।'
    ],
    'saran-teacher-constituency' => [
        'turnout_final' => 89.05,
        'turnout_interim_4pm' => 87.16,
        'candidates_count' => 18,
        'electors' => ['male' => 8799, 'female' => 1949, 'third' => 0, 'total' => 10748],
        'booths_total' => 98,
        'result_status' => 'DECLARED WON (विजेता घोषित)',
        'result_badge' => 'bg-success text-white',
        'winner_name' => 'Rahul Raj',
        'winner_name_hi' => 'राहुल राज',
        'party' => 'IND',
        'alliance' => 'Independent (Rebel)',
        'verdict' => 'Independent Rahul Raj secured historic victory by 800+ votes in state-record 89.05% turnout poll, defeating Afaq Ahmad & Anand Pushkar.',
        'verdict_hi' => 'राज्य में रिकॉर्ड 89.05% मतदान के बीच निर्दलीय राहुल राज 800+ मतों से विजयी; अफाक अहमद व आनंद पुष्कर पराजित।'
    ]
];
$currPoll = $officialPollStats[$cData['slug']] ?? null;

// All 8 constituencies for inter-linking
$allConstituencies = getMlcAllConstituenciesData();

require_once __DIR__ . '/../header.php';
?>

<style>
/* Modern Dynamic Styling for MLC Constituency Hub */
:root {
    --mlc-grad-start: <?php echo $isTeachers ? '#064e3b' : '#1e1b4b'; ?>;
    --mlc-grad-mid: <?php echo $isTeachers ? '#047857' : '#1d4ed8'; ?>;
    --mlc-grad-end: <?php echo $isTeachers ? '#0f172a' : '#090d16'; ?>;
    --mlc-accent: <?php echo $isTeachers ? '#10b981' : '#3b82f6'; ?>;
    --mlc-accent-light: <?php echo $isTeachers ? '#d1fae5' : '#dbeafe'; ?>;
}

.mlc-hero {
    background: linear-gradient(135deg, var(--mlc-grad-start) 0%, var(--mlc-grad-mid) 50%, var(--mlc-grad-end) 100%);
    color: #ffffff;
    padding: 60px 0 45px;
    position: relative;
    overflow: hidden;
}

.mlc-hero::after {
    content: '';
    position: absolute;
    top: -50px;
    right: -50px;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.12) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%;
    pointer-events: none;
}

.stat-metric-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 18px rgba(11, 25, 44, 0.04);
    padding: 1.25rem 1.5rem;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    height: 100%;
}

.stat-metric-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(11, 25, 44, 0.08);
}

.district-tag {
    background: #f1f5f9;
    color: #1e293b;
    font-size: 0.82rem;
    font-weight: 600;
    padding: 4px 12px;
    border-radius: 50px;
    border: 1px solid #cbd5e1;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.booth-search-box {
    background: #ffffff;
    border: 2px solid #cbd5e1;
    border-radius: 50px;
    padding: 0.75rem 1.4rem;
    font-size: 1.05rem;
    transition: all 0.2s ease;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
}

.booth-search-box:focus {
    border-color: var(--mlc-accent);
    outline: none;
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}

.booth-card {
    background: #ffffff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    padding: 1.1rem 1.3rem;
    transition: all 0.2s ease;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

.booth-card:hover {
    border-color: var(--mlc-accent);
    box-shadow: 0 8px 24px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}

.part-badge {
    background: var(--mlc-grad-start);
    color: #ffffff;
    font-size: 0.85rem;
    font-weight: 800;
    padding: 4px 12px;
    border-radius: 8px;
    letter-spacing: 0.3px;
    display: inline-block;
}

.table-booths th {
    background-color: #0b192c !important;
    color: #ffffff !important;
    font-weight: 700;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border: none;
}

.table-booths td {
    padding: 12px 16px;
    vertical-align: middle;
    font-size: 0.95rem;
    border-bottom: 1px solid #e2e8f0;
}

.table-booths tbody tr:hover {
    background-color: #f8fafc;
}

.nav-con-pill {
    padding: 8px 16px;
    border-radius: 50px;
    font-size: 0.88rem;
    font-weight: 600;
    text-decoration: none;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.nav-con-pill:hover {
    border-color: var(--mlc-accent);
    color: var(--mlc-accent);
    background: #f8fafc;
}

.nav-con-pill.active {
    background: var(--mlc-grad-start);
    color: #ffffff;
    border-color: var(--mlc-grad-start);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}

@keyframes pulseGlow {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.85; transform: scale(1.02); }
}
.blink-anim {
    animation: pulseGlow 1.8s infinite ease-in-out;
}

.cand-avatar {
    width: 200px;
    height: 200px;
    max-width: 200px;
    object-fit: cover;
    object-position: top center;
    border-radius: 16px;
    border: 3px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    background: #f1f5f9;
    transition: transform 0.25s ease, box-shadow 0.25s ease, border-color 0.25s ease;
    display: inline-block;
}

.cand-avatar:hover {
    transform: scale(1.03);
    box-shadow: 0 10px 24px rgba(11, 25, 44, 0.18);
    border-color: #0d6efd;
}

.cand-party-badge {
    font-size: 0.82rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.affidavit-btn {
    font-size: 0.82rem;
    font-weight: 600;
    padding: 5px 12px;
    border-radius: 50px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
    transition: all 0.2s ease;
}

.affidavit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
}

.party-filter-pill {
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.84rem;
    font-weight: 600;
    border: 1px solid #cbd5e1;
    background: #ffffff;
    color: #475569;
    cursor: pointer;
    transition: all 0.2s ease;
}

.party-filter-pill:hover, .party-filter-pill.active {
    background: #0f172a;
    color: #ffffff;
    border-color: #0f172a;
}

.party-filter-scroll {
    overflow-x: auto;
    white-space: nowrap;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 4px;
}

.cand-mobile-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    box-shadow: 0 4px 14px rgba(11, 25, 44, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cand-mobile-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(11, 25, 44, 0.1);
}
</style>

<!-- Hero Section -->
<header class="mlc-hero">
    <div class="container text-start position-relative">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-white bg-opacity-20 text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                🏛️ Bihar Legislative Council &bull; विधान परिषद
            </span>
            <span class="badge <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white fw-bold px-3 py-2 rounded-pill shadow-sm">
                <?php echo $article; ?>
            </span>
            <span class="badge bg-danger text-white fw-bold px-3 py-2 rounded-pill blink-anim shadow-sm">
                <i class="bi bi-broadcast me-1"></i> Biennial Election 2026 &bull; Segment <?php echo $cData['segment_code']; ?>
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-1.5 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/mlc" class="text-white-50 text-decoration-none">MLC Hub</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/<?php echo $isTeachers ? 'teachers-constituency' : 'graduates-constituency'; ?>" class="text-white-50 text-decoration-none"><?php echo $typeTitle; ?></a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($cData['name']); ?></li>
            </ol>
        </nav>

        <h1 class="display-5 fw-extrabold text-white mb-2">
            <?php echo htmlspecialchars($cData['name']); ?> <br>
            <span style="color: #fbbf24;"><?php echo htmlspecialchars($cData['name_hi']); ?></span>
        </h1>
        
        <p class="lead text-white-50 mb-4" style="font-size: 1.15rem; max-width: 920px;">
            <?php echo htmlspecialchars($cData['desc']); ?> 
            Official directory of all <strong><?php echo $cData['booth_count']; ?> Polling Booths (मतदान केंद्र)</strong>, Returning Officer information, <?php echo $formType; ?> voter registration eligibility, and CEO Bihar Final Roll (06.10.2026).
        </p>

        <div class="d-flex flex-wrap gap-2.5">
            <?php if (!empty($cData['candidates'])): ?>
                <a href="#nominations-section" class="btn btn-warning fw-bold text-dark px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-danger"></i> Candidates &amp; Nominations (<?php echo count($cData['candidates']); ?>)
                </a>
            <?php endif; ?>
            <a href="#booths-section" class="btn <?php echo !empty($cData['candidates']) ? 'btn-outline-light' : 'btn-warning text-dark'; ?> fw-bold px-4 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-geo-alt-fill text-danger"></i> View All <?php echo $cData['booth_count']; ?> Booths
            </a>
            <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning fw-bold px-3.5 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-search"></i> Search Name in Roll
            </a>
            <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="btn btn-primary fw-bold px-3.5 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-pencil-square"></i> Apply Online (<?php echo $formType; ?>)
            </a>
            <a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-light fw-bold px-3.5 py-2.5 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-file-earmark-pdf-fill text-danger"></i> CEO Bihar Roll (PDF)
            </a>
            <a href="#voter-guide" class="btn btn-light bg-opacity-25 text-white fw-semibold px-3 py-2.5 rounded-pill d-inline-flex align-items-center gap-2">
                <i class="bi bi-card-checklist"></i> <?php echo $formType; ?> Guide
            </a>
        </div>
    </div>
</header>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Switch Constituency Quick Bar -->
    <div class="card border-0 shadow-sm rounded-4 p-3.5 mb-4 bg-white">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2 mb-2">
            <span class="text-muted small fw-bold text-uppercase">
                <i class="bi bi-shuffle text-primary me-1"></i> Switch MLC Constituency (All 8 Active Seats):
            </span>
            <div class="d-flex gap-2">
                <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="extra-small text-decoration-none fw-semibold text-success">
                    <i class="bi bi-book me-1"></i>All Teachers Seats
                </a>
                <span class="text-muted extra-small">&bull;</span>
                <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="extra-small text-decoration-none fw-semibold text-primary">
                    <i class="bi bi-mortarboard me-1"></i>All Graduates Seats
                </a>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php foreach ($allConstituencies as $slug => $item): 
                $isActive = ($slug === $cData['slug']);
                $icon = ($item['type'] === 'teachers') ? '📚' : '🎓';
            ?>
                <a href="<?php echo SITE_URL . '/' . $slug; ?>" class="nav-con-pill <?php echo $isActive ? 'active' : ''; ?>">
                    <span><?php echo $icon; ?></span>
                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                    <span class="badge bg-light text-dark rounded-pill extra-small ms-1"><?php echo $item['booth_count']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Key Statistics Grid -->
    <div class="row g-3 mb-5">
        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">Total Polling Booths</span>
                    <span class="badge <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white rounded-pill extra-small">Official Roll</span>
                </div>
                <div class="display-6 fw-extrabold text-navy"><?php echo $cData['booth_count']; ?></div>
                <small class="text-muted">मतदान केंद्र (Parts in Constituency)</small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">Divisional HQ</span>
                    <i class="bi bi-building text-info fs-5"></i>
                </div>
                <div class="h4 fw-bold text-navy mb-1"><?php echo htmlspecialchars($cData['hq']); ?></div>
                <small class="text-muted"><?php echo htmlspecialchars($cData['hq_hi']); ?></small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">Voter Enrollment</span>
                    <span class="badge bg-warning text-dark fw-bold rounded-pill extra-small">Statutory</span>
                </div>
                <div class="h4 fw-bold text-navy mb-1"><?php echo $formType; ?> Registration</div>
                <small class="text-muted"><?php echo $isTeachers ? '3 Years Teaching Exp.' : '3+ Yrs Graduate Degree'; ?></small>
            </div>
        </div>

        <div class="col-sm-6 col-lg-3">
            <div class="stat-metric-card">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted extra-small fw-bold text-uppercase">CEO Roll Date</span>
                    <i class="bi bi-calendar-check text-success fs-5"></i>
                </div>
                <div class="h4 fw-bold text-navy mb-1"><?php echo $cData['publication_date']; ?></div>
                <small class="text-muted">Final Published Roll</small>
            </div>
        </div>
    </div>

    <?php if ($currPoll): ?>
    <!-- Official 2026 Poll Turnout, Electorate & Election Status (CEO Bihar Final VTR & Press Note) -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 overflow-hidden" style="border: 2px solid #cbd5e1;">
        <div class="p-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 50%, #0f172a 100%); color: #ffffff;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill text-uppercase">
                        <i class="bi bi-patch-check-fill text-dark me-1"></i> CEO Bihar Official Turnout Report
                    </span>
                    <span class="badge <?php echo $currPoll['result_badge']; ?> fw-bold px-3 py-1.5 rounded-pill">
                        <?php echo $currPoll['result_status']; ?>
                    </span>
                    <span class="badge bg-white bg-opacity-20 text-white fw-semibold px-2.5 py-1 rounded-pill extra-small">
                        Gazette: TCGCGajat2026
                    </span>
                </div>
                <div class="text-white-50 extra-small">
                    Source: CEO Bihar Final VTR &amp; Press Note (23-10-2026)
                </div>
            </div>

            <div class="row g-4 align-items-center">
                <div class="col-lg-4 text-center text-lg-start border-end-lg border-white border-opacity-10 pe-lg-4">
                    <span class="text-white-50 extra-small fw-bold text-uppercase d-block mb-1">
                        Final Official Voter Turnout (मतदान प्रतिशत)
                    </span>
                    <div class="display-4 fw-extrabold text-warning mb-1">
                        <?php echo number_format($currPoll['turnout_final'], 2); ?>%
                    </div>
                    <div class="text-white-50 small mb-2">
                        <span class="badge bg-success bg-opacity-25 text-white border border-success border-opacity-50 px-2 py-0.5 rounded-pill extra-small me-1">
                            Final VTR
                        </span>
                        Interim 4 PM Poll: <?php echo number_format($currPoll['turnout_interim_4pm'], 2); ?>%
                    </div>
                    <div class="progress bg-white bg-opacity-20 rounded-pill" style="height: 10px;">
                        <div class="progress-bar bg-warning progress-bar-striped progress-bar-animated" role="progressbar" style="width: <?php echo min(100, $currPoll['turnout_final']); ?>%;"></div>
                    </div>
                </div>

                <div class="col-lg-5 border-end-lg border-white border-opacity-10 px-lg-4">
                    <span class="text-white-50 extra-small fw-bold text-uppercase d-block mb-2">
                        Official Gender-wise Electorate (मतदाता सांख्यिकी)
                    </span>
                    <div class="row g-2 mb-2 text-center text-sm-start">
                        <div class="col-6 col-sm-3">
                            <div class="p-2 rounded bg-white bg-opacity-10">
                                <span class="d-block extra-small text-white-50">Male (पुरुष)</span>
                                <strong class="fs-6 text-white"><?php echo number_format($currPoll['electors']['male']); ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 rounded bg-white bg-opacity-10">
                                <span class="d-block extra-small text-white-50">Female (महिला)</span>
                                <strong class="fs-6 text-white"><?php echo number_format($currPoll['electors']['female']); ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 rounded bg-white bg-opacity-10">
                                <span class="d-block extra-small text-white-50">Third Gender</span>
                                <strong class="fs-6 text-white"><?php echo number_format($currPoll['electors']['third']); ?></strong>
                            </div>
                        </div>
                        <div class="col-6 col-sm-3">
                            <div class="p-2 rounded bg-warning bg-opacity-25 border border-warning border-opacity-50">
                                <span class="d-block extra-small text-warning fw-bold">Total (कुल)</span>
                                <strong class="fs-6 text-warning"><?php echo number_format($currPoll['electors']['total']); ?></strong>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex flex-wrap gap-2 text-white-50 extra-small">
                        <span><i class="bi bi-person-lines-fill text-warning me-1"></i><strong><?php echo $currPoll['candidates_count']; ?> Candidates</strong> Nominated</span>
                        <span>&bull;</span>
                        <span><i class="bi bi-geo-alt-fill text-warning me-1"></i><strong><?php echo $currPoll['booths_total']; ?> Polling Booths</strong></span>
                    </div>
                </div>

                <div class="col-lg-3 ps-lg-4">
                    <span class="text-white-50 extra-small fw-bold text-uppercase d-block mb-1">
                        Elected MLC / Verdict Status
                    </span>
                    <h5 class="fw-bold text-white mb-0.5">
                        <?php echo htmlspecialchars($currPoll['winner_name']); ?>
                    </h5>
                    <div class="text-warning small mb-1">
                        <?php echo htmlspecialchars($currPoll['winner_name_hi']); ?> 
                        <span class="badge bg-primary text-white rounded-pill extra-small ms-1"><?php echo htmlspecialchars($currPoll['party']); ?></span>
                    </div>
                    <p class="extra-small text-white-50 mb-3" style="line-height: 1.4;">
                        <?php echo htmlspecialchars($currPoll['verdict']); ?>
                    </p>
                    <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election#<?php echo str_replace('-constituency', '', $cData['slug']); ?>" class="btn btn-warning btn-sm fw-bold text-dark rounded-pill px-3 py-1.5 d-inline-flex align-items-center gap-1.5 shadow-sm">
                        <span>View Live Results Table</span>
                        <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
        <div class="bg-light px-4 py-2.5 d-flex flex-wrap align-items-center justify-content-between gap-2 border-top small">
            <div class="text-muted extra-small">
                <i class="bi bi-shield-check text-success me-1"></i> Data officially certified under Form 21C / 21D &amp; CEO Bihar Statistical Reports.
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/VTR/Final%20VTR%20in%20TC%20GC%20Election%202026.pdf" target="_blank" rel="noopener noreferrer" class="btn btn-outline-danger btn-sm rounded-pill py-0.5 px-2.5 extra-small fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-file-earmark-pdf-fill"></i> Final VTR PDF
                </a>
                <a href="https://ceoelection.bihar.gov.in/PDF/Year_2026/PressRelease2026/Press%20Note%2023-10-2026.pdf" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm rounded-pill py-0.5 px-2.5 extra-small fw-semibold d-inline-flex align-items-center gap-1">
                    <i class="bi bi-newspaper"></i> Press Note 23-10-2026 PDF
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Returning Officer & Covered Districts Banner -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 p-4 bg-white">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6 border-end-lg">
                <div class="d-flex align-items-start gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 fs-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block mb-1">Returning Officer (RO) / निर्वाचन पदाधिकारी</span>
                        <h5 class="fw-bold text-navy mb-1"><?php echo htmlspecialchars($cData['ro']); ?></h5>
                        <p class="text-muted small mb-0"><?php echo htmlspecialchars($cData['ro_hi']); ?></p>
                        <?php if (!empty($cData['ro_url'])): ?>
                            <div class="mt-2">
                                <a href="<?php echo htmlspecialchars($cData['ro_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 extra-small">
                                    <i class="bi bi-building"></i>
                                    <span>Divisional Commissioner Portal</span>
                                    <i class="bi bi-box-arrow-up-right ms-0.5" style="font-size: 10px;"></i>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <span class="text-muted extra-small fw-bold text-uppercase d-block mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> Covered Districts (<?php echo count($cData['districts']); ?>):
                </span>
                <div class="d-flex flex-wrap gap-1.5">
                    <?php foreach ($cData['districts'] as $idx => $dist): 
                        $distHi = $cData['districts_hi'][$idx] ?? '';
                    ?>
                        <span class="district-tag">
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 0.75rem;"></i>
                            <span><?php echo htmlspecialchars($dist); ?></span>
                            <?php if ($distHi): ?>
                                <small class="text-muted">(<?php echo htmlspecialchars($distHi); ?>)</small>
                            <?php endif; ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Official CEO Bihar Portal Notice & Instructions -->
    <div class="card border-0 rounded-4 shadow-sm mb-5 overflow-hidden text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e293b 100%);">
        <div class="p-4 p-lg-4.5 d-flex flex-column flex-xl-row align-items-xl-center justify-content-between gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0 shadow-sm" style="width: 50px; height: 50px; font-size: 1.5rem;">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <span class="badge bg-danger text-white fw-bold px-2.5 py-1 rounded-pill">Chief Electoral Officer, Bihar</span>
                        <span class="badge bg-white bg-opacity-25 text-white px-2.5 py-1 rounded-pill">Final Roll [06.10.2026]</span>
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill">Online Portals</span>
                    </div>
                    <h5 class="fw-bold text-white mb-1">Official CEO Bihar Council Portals: Roll PDF, Elector Search &amp; Online Registration</h5>
                    <p class="text-white-50 mb-0 small" style="max-width: 820px;">
                        Check your polling booth below, search your name in the Council Electoral Roll (<a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-warning text-decoration-none fw-semibold">tcgcelectorsearch</a>), apply online via Form <?php echo $isTeachers ? '19' : '18'; ?> (<a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-warning text-decoration-none fw-semibold">tcgconline</a>), or download the certified polling station PDF from CEO Bihar.
                    </p>
                </div>
            </div>
            <div class="d-flex flex-wrap flex-xl-nowrap gap-2 flex-shrink-0">
                <a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-warning fw-bold text-dark rounded-pill px-3.5 py-2 shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                    <span>Roll PDF</span>
                </a>
                <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="btn btn-outline-warning fw-bold text-white rounded-pill px-3.5 py-2 shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-search"></i>
                    <span>Search Name</span>
                </a>
                <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="btn btn-primary fw-bold rounded-pill px-3.5 py-2 shadow-sm text-nowrap d-inline-flex align-items-center gap-1.5">
                    <i class="bi bi-pencil-square"></i>
                    <span>Apply Online</span>
                </a>
            </div>
        </div>
    </div>

    <!-- CANDIDATE NOMINATIONS & AFFIDAVITS DIRECTORY -->
    <?php if (!empty($cData['candidates'])): 
        $candList = $cData['candidates'];
        $candCount = count($candList);
        $totalSetsCount = 0;
        foreach ($candList as $cand) {
            $totalSetsCount += !empty($cand['sets']) ? count($cand['sets']) : 1;
        }
        $isTeacherSeat = (stripos($cData['id'] ?? '', 'teacher') !== false || stripos($cData['name'] ?? '', 'teacher') !== false);
        $seatTypeDisplay = $isTeacherSeat ? 'Teacher Constituency' : 'Graduate Constituency';
    ?>
    <section id="nominations-section" class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <!-- Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-start gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-2">
                        <span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-award-fill me-1"></i> Official Candidate Nominations
                        </span>
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-file-earmark-check-fill text-danger me-1"></i> Affidavits (शपथ पत्र)
                        </span>
                        <span class="badge bg-dark text-white rounded-pill px-3 py-1.5 small">
                            <span id="candidateCountBadge"><?php echo $candCount; ?></span> Unique Candidates
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-navy mt-1 mb-1">
                        Affidavit of Bye/Biennial Election to the Bihar Legislative Council
                    </h3>
                    <p class="text-muted small mb-0">
                        Official directory of nominated candidates for <strong><?php echo htmlspecialchars($cData['name']); ?> (<?php echo htmlspecialchars($cData['name_hi'] ?? ''); ?>)</strong> &bull; <?php echo $seatTypeDisplay; ?> 2026. Each candidate is listed once with all filed sets and sworn affidavits.
                    </p>
                </div>

                <div class="d-flex flex-column align-items-md-end gap-1 flex-shrink-0">
                    <span class="small text-muted fw-semibold">
                        source: <a href="https://ele.bihar.gov.in/mlc-nomination" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none fw-bold">ele.bihar.gov.in/mlc-nomination</a>
                    </span>
                </div>
            </div>

            <!-- Election Context Alert Banner -->
            <div class="rounded-3 p-3 mb-4 border" style="background: #f8fafc; border-color: #e2e8f0;">
                <div class="row g-2 align-items-center extra-small text-secondary">
                    <div class="col-sm-6 col-lg-3">
                        <span class="text-muted fw-bold d-block text-uppercase">Election Type:</span>
                        <span class="fw-bold text-navy"><i class="bi bi-check2-circle text-success me-1"></i> <?php echo $seatTypeDisplay; ?></span>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <span class="text-muted fw-bold d-block text-uppercase">Election Year:</span>
                        <span class="fw-bold text-navy"><i class="bi bi-calendar3 text-primary me-1"></i> Biennial Election 2026</span>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <span class="text-muted fw-bold d-block text-uppercase">Constituency:</span>
                        <span class="fw-bold text-navy"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($cData['name']); ?></span>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <span class="text-muted fw-bold d-block text-uppercase">Total Filings Recorded:</span>
                        <span class="fw-bold text-danger"><i class="bi bi-file-earmark-text text-danger me-1"></i> <?php echo $totalSetsCount; ?> Nomination Sets (<?php echo $candCount; ?> Unique Candidates)</span>
                    </div>
                </div>
            </div>

            <!-- Live Filter & Party Chips -->
            <div class="row g-3 mb-3 align-items-center">
                <div class="col-md-5 col-lg-6">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="candSearchInput" class="form-control booth-search-box ps-5" placeholder="Search candidate by name, Hindi name, party, or date..." autocomplete="off" oninput="filterCandidateList()">
                    </div>
                </div>
                <div class="col-md-7 col-lg-6 text-md-end">
                    <div class="d-flex party-filter-scroll gap-1.5 justify-content-start justify-content-md-end">
                        <button type="button" class="party-filter-pill active" data-party="ALL" onclick="setPartyFilter('ALL', this)">All (<?php echo count($cData['candidates']); ?>)</button>
                        <?php 
                        $parties = array_unique(array_column($cData['candidates'], 'party'));
                        foreach ($parties as $pty):
                            $pCount = count(array_filter($cData['candidates'], fn($x) => $x['party'] === $pty));
                            $shortName = ($pty === 'Janata Dal (United)') ? 'JD(U)' : (($pty === 'Rashtriya Janata Dal') ? 'RJD' : (($pty === 'Jan Suraaj Party') ? 'Jan Suraaj' : (strlen($pty) > 12 ? substr($pty, 0, 11) . '.' : $pty)));
                        ?>
                            <button type="button" class="party-filter-pill" data-party="<?php echo htmlspecialchars($pty); ?>" onclick="setPartyFilter('<?php echo htmlspecialchars(addslashes($pty)); ?>', this)">
                                <?php echo htmlspecialchars($shortName); ?> (<?php echo $pCount; ?>)
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- No Results Notice -->
            <div id="noCandResults" class="alert alert-warning rounded-3 text-center py-4 d-none">
                <i class="bi bi-person-x fs-2 d-block mb-2 text-warning"></i>
                <h6 class="fw-bold mb-1">No candidate found matching your search.</h6>
                <p class="small text-muted mb-0">Try clearing your search query or clicking "All".</p>
            </div>

            <!-- Desktop Candidates Table (md and up) -->
            <div class="table-responsive rounded-3 border d-none d-md-block" id="candTableContainer">
                <table class="table table-hover align-middle mb-0" id="candidatesTable">
                    <thead style="background: #0F3E5F; color: #ffffff;">
                        <tr class="small text-uppercase">
                            <th scope="col" style="width: 5%; text-align: center;">SL NO.</th>
                            <th scope="col" style="width: 220px; text-align: center;">PHOTO</th>
                            <th scope="col" style="width: 26%;">CANDIDATE NAME</th>
                            <th scope="col" style="width: 8%; text-align: center;">GENDER</th>
                            <th scope="col" style="width: 17%;">PARTY</th>
                            <th scope="col" style="width: 14%;">NOMINATION DATE</th>
                            <th scope="col" style="width: 20%; text-align: center;">AFFIDAVIT (शपथ पत्र)</th>
                        </tr>
                    </thead>
                    <tbody id="candidatesTbody">
                        <?php foreach ($cData['candidates'] as $cand): 
                            $badgeColor = ($cand['party_short'] === 'JD(U)') ? 'bg-success' : (($cand['party_short'] === 'RJD') ? 'bg-success' : (($cand['party_short'] === 'JSP') ? 'bg-warning text-dark' : 'bg-secondary'));
                        ?>
                        <tr class="candidate-row" 
                            data-name="<?php echo strtolower($cand['name'] . ' ' . $cand['name_raw'] . ' ' . $cand['name_hi']); ?>"
                            data-party="<?php echo htmlspecialchars($cand['party']); ?>"
                            data-date="<?php echo htmlspecialchars($cand['nomination_date']); ?>">
                            <td class="text-center fw-bold text-muted"><?php echo $cand['sl_no']; ?></td>
                            <td class="text-center" style="width: 220px; min-width: 215px; padding: 12px 8px;">
                                <a href="<?php echo htmlspecialchars($cand['photo']); ?>" target="_blank" title="View Full Photo of <?php echo htmlspecialchars($cand['name']); ?>">
                                    <img src="<?php echo htmlspecialchars($cand['photo']); ?>" alt="<?php echo htmlspecialchars($cand['name']); ?>" class="cand-avatar" width="200" height="200" onerror="this.src='assets/images/placeholder-candidate.png'">
                                </a>
                            </td>
                            <td>
                                <div class="fw-bold text-navy" style="font-size: 1.02rem;">
                                    <?php echo htmlspecialchars($cand['name']); ?>
                                </div>
                                <div class="text-muted extra-small">
                                    <?php echo htmlspecialchars($cand['name_hi']); ?>
                                </div>
                                <?php if ($cand['filings_count'] > 1): ?>
                                    <span class="badge bg-light text-dark border extra-small mt-1">
                                        <i class="bi bi-layers-fill text-primary me-1"></i><?php echo $cand['filings_count']; ?> Sets Filed
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border extra-small">
                                    <?php echo htmlspecialchars($cand['gender']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $badgeColor; ?> cand-party-badge">
                                    <?php echo htmlspecialchars($cand['party']); ?>
                                </span>
                                <?php if (!empty($cand['party_hi']) && $cand['party_hi'] !== $cand['party']): ?>
                                    <div class="text-muted extra-small mt-0.5"><?php echo htmlspecialchars($cand['party_hi']); ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark small">
                                    <i class="bi bi-calendar-event text-primary me-1"></i><?php echo htmlspecialchars($cand['nomination_date']); ?>
                                </div>
                                <?php if ($cand['filings_count'] > 1): ?>
                                    <div class="extra-small text-muted">
                                        <?php 
                                        $dates = array_unique(array_column($cand['sets'], 'date'));
                                        if (count($dates) > 1) {
                                            echo "Also filed on: " . implode(', ', array_slice($dates, 1));
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($cand['filings_count'] === 1): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger rounded-pill affidavit-btn shadow-sm"
                                            data-bs-toggle="modal"
                                            data-bs-target="#affidavitRedirectModal"
                                            data-url="<?php echo htmlspecialchars($cand['affidavit_url']); ?>"
                                            data-name="<?php echo htmlspecialchars($cand['name']); ?>"
                                            data-party="<?php echo htmlspecialchars($cand['party']); ?>"
                                            data-doc="Sworn Affidavit PDF"
                                            onclick="openAffidavitModal('<?php echo htmlspecialchars(addslashes($cand['affidavit_url'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['party'])); ?>', 'Sworn Affidavit PDF')">
                                        <i class="bi bi-file-earmark-pdf-fill text-danger fs-6"></i>
                                        <span>View Affidavit</span>
                                    </button>
                                <?php else: ?>
                                    <div class="d-flex flex-wrap gap-1 justify-content-center">
                                        <?php foreach ($cand['sets'] as $set): ?>
                                            <button type="button" 
                                                    class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 extra-small fw-semibold d-inline-flex align-items-center gap-1 shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#affidavitRedirectModal"
                                                    data-url="<?php echo htmlspecialchars($set['affidavit_url']); ?>"
                                                    data-name="<?php echo htmlspecialchars($cand['name']); ?>"
                                                    data-party="<?php echo htmlspecialchars($cand['party']); ?>"
                                                    data-doc="Nomination Set <?php echo $set['set_no']; ?> (Filed: <?php echo htmlspecialchars($set['date']); ?>)"
                                                    onclick="openAffidavitModal('<?php echo htmlspecialchars(addslashes($set['affidavit_url'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['party'])); ?>', 'Nomination Set <?php echo $set['set_no']; ?> (Filed: <?php echo htmlspecialchars($set['date']); ?>)')"
                                                    title="Filing Date: <?php echo htmlspecialchars($set['date']); ?>">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                <span>Set <?php echo $set['set_no']; ?></span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Candidate Cards Feed (xs / sm screens) -->
            <div class="d-md-none" id="candMobileContainer">
                <div class="row g-3">
                    <?php foreach ($cData['candidates'] as $cand): 
                        $badgeColor = ($cand['party_short'] === 'JD(U)') ? 'bg-success' : (($cand['party_short'] === 'RJD') ? 'bg-success' : (($cand['party_short'] === 'JSP') ? 'bg-warning text-dark' : 'bg-secondary'));
                    ?>
                    <div class="col-12 candidate-mobile-item"
                        data-name="<?php echo strtolower($cand['name'] . ' ' . $cand['name_raw'] . ' ' . $cand['name_hi']); ?>"
                        data-party="<?php echo htmlspecialchars($cand['party']); ?>"
                        data-date="<?php echo htmlspecialchars($cand['nomination_date']); ?>">
                        <div class="cand-mobile-card p-3.5">
                            <div class="d-flex justify-content-between align-items-center mb-2.5">
                                <span class="badge bg-light text-dark border fw-bold px-2.5 py-1">#<?php echo $cand['sl_no']; ?></span>
                                <span class="badge <?php echo $badgeColor; ?> cand-party-badge"><?php echo htmlspecialchars($cand['party']); ?></span>
                                <span class="badge bg-light text-muted border extra-small"><?php echo htmlspecialchars($cand['gender']); ?></span>
                            </div>
                            
                            <div class="text-center my-2">
                                <a href="<?php echo htmlspecialchars($cand['photo']); ?>" target="_blank" title="View Full Photo of <?php echo htmlspecialchars($cand['name']); ?>">
                                    <img src="<?php echo htmlspecialchars($cand['photo']); ?>" alt="<?php echo htmlspecialchars($cand['name']); ?>" class="cand-avatar" width="200" height="200" onerror="this.src='assets/images/placeholder-candidate.png'">
                                </a>
                            </div>
                            
                            <h5 class="fw-bold text-navy mb-0.5 text-center mt-2"><?php echo htmlspecialchars($cand['name']); ?></h5>
                            <div class="text-muted extra-small text-center mb-2.5"><?php echo htmlspecialchars($cand['name_hi']); ?></div>
                            
                            <div class="bg-light p-2 rounded-3 small mb-3 d-flex justify-content-between align-items-center border">
                                <span class="text-muted extra-small"><i class="bi bi-calendar-event me-1"></i>Nomination:</span>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($cand['nomination_date']); ?></span>
                            </div>
                            
                            <?php if ($cand['filings_count'] === 1): ?>
                                <button type="button" 
                                        class="btn btn-outline-danger w-100 rounded-pill py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#affidavitRedirectModal"
                                        data-url="<?php echo htmlspecialchars($cand['affidavit_url']); ?>"
                                        data-name="<?php echo htmlspecialchars($cand['name']); ?>"
                                        data-party="<?php echo htmlspecialchars($cand['party']); ?>"
                                        data-doc="Sworn Affidavit PDF"
                                        onclick="openAffidavitModal('<?php echo htmlspecialchars(addslashes($cand['affidavit_url'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['party'])); ?>', 'Sworn Affidavit PDF')">
                                    <i class="bi bi-file-earmark-pdf-fill text-danger fs-5"></i>
                                    <span>View Affidavit (PDF)</span>
                                </button>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-1.5">
                                    <div class="text-muted extra-small text-center mb-1">
                                        <i class="bi bi-layers-fill text-primary me-1"></i><?php echo $cand['filings_count']; ?> Nomination Sets Filed
                                    </div>
                                    <div class="d-flex flex-wrap gap-2 justify-content-center">
                                        <?php foreach ($cand['sets'] as $set): ?>
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-danger flex-grow-1 rounded-pill py-2 fw-semibold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#affidavitRedirectModal"
                                                    data-url="<?php echo htmlspecialchars($set['affidavit_url']); ?>"
                                                    data-name="<?php echo htmlspecialchars($cand['name']); ?>"
                                                    data-party="<?php echo htmlspecialchars($cand['party']); ?>"
                                                    data-doc="Nomination Set <?php echo $set['set_no']; ?> (Filed: <?php echo htmlspecialchars($set['date']); ?>)"
                                                    onclick="openAffidavitModal('<?php echo htmlspecialchars(addslashes($set['affidavit_url'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['name'])); ?>', '<?php echo htmlspecialchars(addslashes($cand['party'])); ?>', 'Nomination Set <?php echo $set['set_no']; ?> (Filed: <?php echo htmlspecialchars($set['date']); ?>)')">
                                                <i class="bi bi-file-earmark-pdf-fill text-danger"></i>
                                                <span>Set <?php echo $set['set_no']; ?> (PDF)</span>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Footer Note -->
            <div class="mt-3 pt-3 border-top small text-muted">
                <span>
                    source: <a href="https://ele.bihar.gov.in/mlc-nomination" target="_blank" rel="noopener noreferrer" class="text-primary text-decoration-none fw-bold">ele.bihar.gov.in/mlc-nomination</a>
                </span>
            </div>
        </div>
    </section>

    <!-- External Site Notice Modal for Affidavits -->
    <div class="modal fade" id="affidavitRedirectModal" tabindex="-1" aria-labelledby="affidavitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                <div class="modal-header border-0 pb-0 pt-4 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-warning bg-opacity-20 text-warning d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                            <i class="bi bi-box-arrow-up-right fs-5 text-dark"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold text-navy mb-0" id="affidavitModalLabel">External Official Website Notice</h5>
                            <small class="text-muted">बाहरी आधिकारिक पोर्टल सूचना</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 py-3">
                    <div class="alert alert-warning border-0 rounded-3 mb-3 d-flex align-items-start gap-2.5">
                        <i class="bi bi-shield-exclamation text-warning fs-4 flex-shrink-0"></i>
                        <div>
                            <strong class="d-block text-dark mb-1">You are leaving BiharElection.com</strong>
                            <p class="small text-dark mb-0">
                                You are now being redirected to the official Chief Electoral Officer (CEO) Bihar portal (<strong>ele.bihar.gov.in</strong>) to show the candidate's sworn affidavit.
                            </p>
                            <p class="extra-small text-muted mb-0 mt-1">
                                (आप biharelection.com से बाहर आधिकारिक मुख्य निर्वाचन पदाधिकारी, बिहार के पोर्टल पर शपथ पत्र देखने जा रहे हैं।)
                            </p>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 border extra-small">
                        <div class="mb-1"><strong class="text-muted">Candidate:</strong> <span id="modalCandName" class="fw-bold text-navy"></span></div>
                        <div class="mb-1"><strong class="text-muted">Party:</strong> <span id="modalCandParty" class="badge bg-secondary"></span></div>
                        <div class="mb-1"><strong class="text-muted">Document:</strong> <span id="modalAffidavitDoc" class="text-danger fw-semibold"></span></div>
                        <div class="mt-2 pt-2 border-top text-truncate"><strong class="text-muted">Official URL:</strong> <span id="modalTargetUrl" class="text-primary font-monospace"></span></div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light px-4 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Cancel / रद्द करें
                    </button>
                    <a id="modalConfirmBtn" href="#" target="_blank" rel="noopener noreferrer" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" onclick="closeAffidavitModal()">
                        <span>Proceed to Show Affidavit</span>
                        <i class="bi bi-arrow-right-circle-fill"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- INTERACTIVE POLLING BOOTHS DIRECTORY -->
    <section id="booths-section" class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white fw-bold px-2.5 py-1 rounded-pill">
                            Official Directory
                        </span>
                        <span class="badge bg-light text-dark border px-2.5 py-1 rounded-pill small">
                            <span id="boothCountBadge"><?php echo $cData['booth_count']; ?></span> / <?php echo $cData['booth_count']; ?> Booths
                        </span>
                    </div>
                    <h3 class="fw-extrabold text-navy mt-1 mb-0">
                        <?php echo htmlspecialchars($cData['name']); ?> Polling Booths (मतदान केंद्र)
                    </h3>
                    <p class="text-muted small mb-0">
                        Complete part-wise list of official polling stations designated by Election Commission / CEO Bihar
                    </p>
                </div>

                <!-- Print & Share Controls -->
                <div class="d-flex gap-2">
                    <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="window.print()">
                        <i class="bi bi-printer"></i> Print List
                    </button>
                    <button class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" id="copyListBtn" onclick="copyBoothTable()">
                        <i class="bi bi-clipboard"></i> Copy
                    </button>
                </div>
            </div>

            <!-- Live Search Bar -->
            <div class="row g-3 mb-4 align-items-center">
                <div class="col-md-8 col-lg-9">
                    <div class="position-relative">
                        <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted fs-5"></i>
                        <input type="text" id="boothSearchInput" class="form-control booth-search-box ps-5" placeholder="Search by Booth No, Block, School, College, Anchal (e.g. 1, Bettiah, Maner, High School)..." autocomplete="off" oninput="filterBooths()">
                    </div>
                </div>
                <div class="col-md-4 col-lg-3">
                    <button type="button" class="btn btn-outline-danger w-100 rounded-pill py-2.5 fw-semibold d-inline-flex align-items-center justify-content-center gap-2" onclick="resetSearch()">
                        <i class="bi bi-x-circle"></i> Clear Filter
                    </button>
                </div>
            </div>

            <!-- Dynamic Search Feedback -->
            <div id="noResultsMsg" class="alert alert-warning rounded-3 text-center py-4 d-none">
                <i class="bi bi-search fs-3 d-block mb-2 text-warning"></i>
                <h6 class="fw-bold mb-1">No polling booths found matching your search.</h6>
                <p class="small text-muted mb-2">Try searching by part number (e.g. 1, 15, 40) or block name.</p>
                <button class="btn btn-sm btn-dark rounded-pill px-3" onclick="resetSearch()">View All <?php echo $cData['booth_count']; ?> Booths</button>
            </div>

            <!-- Responsive Table View -->
            <div class="table-responsive rounded-3 border">
                <table class="table table-booths mb-0" id="boothsTable">
                    <thead>
                        <tr>
                            <th style="width: 130px;">Part No.</th>
                            <th>Polling Station Name &amp; Building (मतदान केंद्र का नाम / भवन)</th>
                            <th style="width: 180px;" class="text-end">Official PDF</th>
                        </tr>
                    </thead>
                    <tbody id="boothsTbody">
                        <?php foreach ($cData['booths'] as $booth): 
                            $partNo = htmlspecialchars($booth['part_no']);
                            $boothName = htmlspecialchars($booth['booth_name']);
                            $fullLabel = htmlspecialchars($booth['full_label']);
                            $rawVal = htmlspecialchars($booth['val']);
                        ?>
                            <tr class="booth-row" data-part="<?php echo strtolower($partNo); ?>" data-name="<?php echo strtolower($fullLabel); ?>">
                                <td>
                                    <span class="part-badge">Part #<?php echo $partNo; ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-navy mb-0.5">
                                        <?php echo $boothName; ?>
                                    </div>
                                    <small class="text-muted">
                                        <i class="bi bi-tag me-1"></i> Part Label: <?php echo $fullLabel; ?>
                                    </small>
                                </td>
                                <td class="text-end">
                                    <a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline-danger rounded-pill fw-semibold px-3 py-1 shadow-sm d-inline-flex align-items-center gap-1.5" title="View certified PDF on CEO portal">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                        <span>CEO Roll</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3 pt-2 text-muted small">
                <span>Source: Chief Electoral Officer, Bihar (Publication Date: 06.10.2026)</span>
                <span>Total: <strong><?php echo $cData['booth_count']; ?> Booths</strong></span>
            </div>
        </div>
    </section>

    <!-- VOTER REGISTRATION & GUIDELINES DOSSIER -->
    <section id="voter-guide" class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <div class="d-flex align-items-center gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill">
                    Constitutional Voter Registration
                </span>
            </div>
            <h3 class="fw-extrabold text-navy mb-3">
                <?php echo $formType; ?> Electoral Enrollment Guide for <?php echo htmlspecialchars($cData['name']); ?>
            </h3>
            <p class="text-muted mb-4" style="max-width: 880px;">
                Electoral rolls for Legislative Council constituencies are compiled de-novo prior to each election. Being registered on the general assembly electoral roll does NOT automatically include you in the Council voter list. Follow the statutory guidelines below:
            </p>

            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle <?php echo $isTeachers ? 'bg-success' : 'bg-primary'; ?> text-white p-3 fs-5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                1
                            </div>
                            <h5 class="fw-bold text-navy mb-0">Eligibility Criteria</h5>
                        </div>
                        <?php if ($isTeachers): ?>
                            <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                                <li><strong>Ordinary Residence:</strong> Must ordinarily reside within the territorial jurisdiction of <?php echo htmlspecialchars($cData['name']); ?> (<?php echo implode(', ', $cData['districts']); ?>).</li>
                                <li><strong>3-Year Qualifying Experience:</strong> Must have taught for at least <strong>3 years within the preceding 6 years</strong> before the qualifying date in a recognized educational institution.</li>
                                <li><strong>Recognized Institutions:</strong> High Schools, Higher Secondary (+2) Schools, Intermediate Colleges, Degree Colleges, Government/Constituent Universities, and recognized Technical/Polytechnic institutes.</li>
                                <li><strong>Exclusion:</strong> Elementary / Middle school teachers (classes 1 to 8) are excluded under Article 171(3)(b).</li>
                            </ul>
                        <?php else: ?>
                            <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                                <li><strong>Ordinary Residence:</strong> Must ordinarily reside in <?php echo implode(', ', $cData['districts']); ?>.</li>
                                <li><strong>Graduation Vintage:</strong> Must have graduated at least <strong>3 years prior to the qualifying date</strong> from a recognized university in India (or hold equivalent qualification recognized by the state government).</li>
                                <li><strong>Degree Certificates:</strong> Self-attested copy of Degree Certificate or Marksheet must be submitted with Form 18.</li>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="p-4 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rounded-circle bg-warning text-dark p-3 fs-5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                2
                            </div>
                            <h5 class="fw-bold text-navy mb-0">Application &amp; Verification Documents</h5>
                        </div>
                        <ul class="text-muted small ps-3 mb-0 d-flex flex-column gap-2">
                            <li><strong>Online Filing:</strong> Electors can submit <strong><?php echo $formType; ?></strong> online via Bihar CEO's dedicated portal: <a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgconline</a> or via ECI's portal: <a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>.</li>
                            <li><strong>Elector Roll Search:</strong> Verify your enrollment and part number using CEO Bihar's Council Elector Search portal: <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgcelectorsearch</a>.</li>
                            <li><strong>Offline Submission:</strong> Physical forms can be submitted to the Electoral Registration Officer (ERO) / Assistant Electoral Registration Officer (AERO) / Block Development Officer (BDO).</li>
                            <?php if ($isTeachers): ?>
                                <li><strong>Certificate of Employment:</strong> Form 19 MUST be accompanied by an official service certificate in the prescribed format, duly signed and stamped by the Headmaster, Principal, Registrar, or Vice-Chancellor of the educational institution.</li>
                            <?php else: ?>
                                <li><strong>Identity &amp; Address Proof:</strong> Aadhaar Card, Voter ID (EPIC), Passport, or Electricity Bill showing residence in the constituency.</li>
                            <?php endif; ?>
                            <li><strong>Preferential Voting System:</strong> Voting takes place via paper ballot under the Single Transferable Vote (STV) system, where electors mark preferences 1, 2, 3... using the official violet sketch pen.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FREQUENTLY ASKED QUESTIONS (FAQ) -->
    <section class="mb-5">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-lg-5 bg-white">
            <h3 class="fw-extrabold text-navy mb-4 d-flex align-items-center gap-2">
                <i class="bi bi-question-circle-fill text-primary"></i> Frequently Asked Questions: <?php echo htmlspecialchars($cData['name']); ?>
            </h3>

            <div class="accordion accordion-flush" id="faqAccordion">
                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="faq1">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                            How many polling booths are in <?php echo htmlspecialchars($cData['name']); ?>?
                        </button>
                    </h2>
                    <div id="collapse1" class="accordion-collapse collapse" aria-labelledby="faq1" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            There are exactly <strong><?php echo $cData['booth_count']; ?> designated Polling Booths (मतदान केंद्र)</strong> across <?php echo htmlspecialchars($cData['name']); ?>, covering all constituent blocks, subdivisions, and district headquarters in <?php echo implode(', ', $cData['districts']); ?> as published in the Final Electoral Roll dated 06.10.2026.
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="faq2">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                            Who is the Returning Officer for <?php echo htmlspecialchars($cData['name']); ?>?
                        </button>
                    </h2>
                    <div id="collapse2" class="accordion-collapse collapse" aria-labelledby="faq2" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            The Returning Officer (RO) for this constituency is the <strong><?php echo htmlspecialchars($cData['ro']); ?></strong> located at <?php echo htmlspecialchars($cData['hq']); ?><?php if (!empty($cData['ro_url'])): ?> (Official Portal: <a href="<?php echo htmlspecialchars($cData['ro_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold"><?php echo parse_url($cData['ro_url'], PHP_URL_HOST); ?></a>)<?php endif; ?>. The RO is assisted by AEROs including District Magistrates (DMs), Sub-Divisional Officers (SDOs), and Block Development Officers (BDOs).
                        </div>
                    </div>
                </div>

                <div class="accordion-item border-bottom">
                    <h2 class="accordion-header" id="faq3">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                            How can I download the official voter list PDF for my polling booth?
                        </button>
                    </h2>
                    <div id="collapse3" class="accordion-collapse collapse" aria-labelledby="faq3" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            You can download the official certified PDF directly from the CEO Bihar website (<a href="<?php echo htmlspecialchars($cData['source_url']); ?>" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold"><?php echo $cData['source_url']; ?></a>). Select Segment Code <strong><?php echo $cData['segment_code']; ?></strong>, select your Part Number from the list above, pick Mother Roll or Supplement, and click View PDF.
                        </div>
                    </div>
                </div>

                <div class="accordion-item">
                    <h2 class="accordion-header" id="faq4">
                        <button class="accordion-button collapsed fw-bold text-navy" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                            Which statutory form is used to register as a voter in this constituency?
                        </button>
                    </h2>
                    <div id="collapse4" class="accordion-collapse collapse" aria-labelledby="faq4" data-bs-parent="#faqAccordion">
                        <div class="accordion-body text-muted small">
                            Electors must submit <strong><?php echo $formType; ?></strong> under the Registration of Electors Rules, 1960. You can submit it online directly via CEO Bihar's portal (<a href="https://ceo.bihar.gov.in/tcgconline/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">ceo.bihar.gov.in/tcgconline</a>) or ECI (<a href="https://voters.eci.gov.in" target="_blank" rel="noopener noreferrer" class="text-primary fw-semibold">voters.eci.gov.in</a>), search your existing enrollment on <a href="https://ceo.bihar.gov.in/tcgcelectorsearch/" target="_blank" rel="noopener noreferrer" class="text-primary fw-bold">tcgcelectorsearch</a>, or submit offline to your designated Block Development Officer or AERO.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- RELATED NAVIGATION CARDS -->
    <div class="row g-3">
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/vidhan-parishad-election" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-danger"><i class="bi bi-broadcast"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">2026 Biennial Election Hub</h6>
                        <small class="text-muted">Live Gazette, Schedule &amp; Results</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/teachers-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-success"><i class="bi bi-book-half"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">All 6 Teachers' Seats</h6>
                        <small class="text-muted">Form 19 Guide &amp; Rules</small>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-4">
            <a href="<?php echo SITE_URL; ?>/graduates-constituency" class="card border-0 shadow-sm rounded-4 p-3.5 text-decoration-none h-100 hover-card bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-2 text-primary"><i class="bi bi-mortarboard-fill"></i></div>
                    <div>
                        <h6 class="fw-bold text-navy mb-0.5">All 6 Graduates' Seats</h6>
                        <small class="text-muted">Form 18 Guide &amp; Rules</small>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<!-- Client-side Live Search & Copy Script -->
<script>
function filterBooths() {
    const query = document.getElementById('boothSearchInput').value.trim().toLowerCase();
    const rows = document.querySelectorAll('#boothsTbody .booth-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const part = row.getAttribute('data-part') || '';
        const name = row.getAttribute('data-name') || '';

        if (!query || part.includes(query) || name.includes(query)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('boothCountBadge');
    if (badge) badge.innerText = visibleCount;

    const noResults = document.getElementById('noResultsMsg');
    if (noResults) {
        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
        } else {
            noResults.classList.add('d-none');
        }
    }
}

function resetSearch() {
    const input = document.getElementById('boothSearchInput');
    if (input) {
        input.value = '';
        filterBooths();
        input.focus();
    }
}

function copyBoothTable() {
    const rows = document.querySelectorAll('#boothsTbody .booth-row');
    let text = "<?php echo addslashes($cData['name']); ?> - Polling Booths List\n";
    text += "Source: CEO Bihar Final Roll (06.10.2026)\n\n";
    rows.forEach(row => {
        if (row.style.display !== 'none') {
            const part = row.querySelector('.part-badge')?.innerText.trim() || '';
            const name = row.querySelector('.fw-bold')?.innerText.trim() || '';
            text += part + " : " + name + "\n";
        }
    });

    navigator.clipboard.writeText(text).then(() => {
        const btn = document.getElementById('copyListBtn');
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="bi bi-check2"></i> Copied!';
            btn.classList.replace('btn-outline-primary', 'btn-success');
            setTimeout(() => {
                btn.innerHTML = orig;
                btn.classList.replace('btn-success', 'btn-outline-primary');
            }, 2000);
        }
    });
}

// Client-side Candidate Search & Filter
let currentPartyFilter = 'ALL';

function filterCandidateList() {
    const query = (document.getElementById('candSearchInput')?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('.candidate-row, .candidate-mobile-item');
    let visibleCount = 0;

    rows.forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const party = row.getAttribute('data-party') || '';
        const date = row.getAttribute('data-date') || '';

        const matchesParty = (currentPartyFilter === 'ALL' || party === currentPartyFilter);
        const matchesQuery = (!query || name.includes(query) || party.toLowerCase().includes(query) || date.includes(query));

        if (matchesParty && matchesQuery) {
            row.style.display = '';
            if (row.classList.contains('candidate-row')) visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('candidateCountBadge');
    if (badge) badge.innerText = visibleCount;

    const noResults = document.getElementById('noCandResults');
    const tableCont = document.getElementById('candTableContainer');
    const mobileCont = document.getElementById('candMobileContainer');
    if (noResults) {
        if (visibleCount === 0) {
            noResults.classList.remove('d-none');
            if (tableCont) tableCont.classList.add('d-none');
            if (mobileCont) mobileCont.classList.add('d-none');
        } else {
            noResults.classList.add('d-none');
            if (tableCont) tableCont.classList.remove('d-none');
            if (mobileCont) mobileCont.classList.remove('d-none');
        }
    }
}

function setPartyFilter(party, btn) {
    currentPartyFilter = party;
    document.querySelectorAll('.party-filter-pill').forEach(p => p.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterCandidateList();
}

// External Affidavit Redirect Popup Modal
function openAffidavitModal(url, name, party, doc) {
    const nameEl = document.getElementById('modalCandName');
    const partyEl = document.getElementById('modalCandParty');
    const docEl = document.getElementById('modalAffidavitDoc');
    const urlEl = document.getElementById('modalTargetUrl');
    const confirmBtn = document.getElementById('modalConfirmBtn');

    if (nameEl) nameEl.innerText = name || '';
    if (partyEl) partyEl.innerText = party || '';
    if (docEl) docEl.innerText = doc || 'Certified Sworn Affidavit PDF';
    if (urlEl) urlEl.innerText = url || '';
    if (confirmBtn) confirmBtn.href = url || '#';

    const modalEl = document.getElementById('affidavitRedirectModal');
    if (window.bootstrap && bootstrap.Modal) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    } else if (window.jQuery && typeof $(modalEl).modal === 'function') {
        $(modalEl).modal('show');
    } else {
        // Fallback popup if bootstrap modal JS is unavailable
        if (confirm("You are now going to the official CEO Bihar portal (ele.bihar.gov.in) to show the affidavit of " + name + ".\n\nProceed to official portal?")) {
            window.open(url, '_blank', 'noopener,noreferrer');
        }
    }
}

function closeAffidavitModal() {
    const modalEl = document.getElementById('affidavitRedirectModal');
    if (window.bootstrap && bootstrap.Modal) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    } else if (window.jQuery && typeof $(modalEl).modal === 'function') {
        $(modalEl).modal('hide');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const modalEl = document.getElementById('affidavitRedirectModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            if (btn) {
                const url = btn.getAttribute('data-url');
                const name = btn.getAttribute('data-name');
                const party = btn.getAttribute('data-party');
                const doc = btn.getAttribute('data-doc');
                if (url) {
                    const nameEl = document.getElementById('modalCandName');
                    const partyEl = document.getElementById('modalCandParty');
                    const docEl = document.getElementById('modalAffidavitDoc');
                    const urlEl = document.getElementById('modalTargetUrl');
                    const confirmBtn = document.getElementById('modalConfirmBtn');
                    if (nameEl) nameEl.innerText = name || '';
                    if (partyEl) partyEl.innerText = party || '';
                    if (docEl) docEl.innerText = doc || 'Certified Sworn Affidavit PDF';
                    if (urlEl) urlEl.innerText = url || '';
                    if (confirmBtn) confirmBtn.href = url || '#';
                }
            }
        });
    }
});
</script>

<!-- Structured JSON-LD Data -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
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
          "name": "MLC Directory",
          "item": "<?php echo SITE_URL; ?>/mlc"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?php echo $typeTitle; ?>",
          "item": "<?php echo SITE_URL; ?>/<?php echo $isTeachers ? 'teachers-constituency' : 'graduates-constituency'; ?>"
        },
        {
          "@type": "ListItem",
          "position": 4,
          "name": "<?php echo htmlspecialchars($cData['name']); ?>",
          "item": "<?php echo $pageCanonical; ?>"
        }
      ]
    },
    {
      "@type": "GovernmentOrganization",
      "name": "<?php echo htmlspecialchars($cData['name']); ?>",
      "alternateName": "<?php echo htmlspecialchars($cData['name_hi']); ?>",
      "url": "<?php echo $pageCanonical; ?>",
      "areaServed": "Bihar, India",
      "description": "<?php echo htmlspecialchars($cData['desc']); ?>"
    },
    {
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "How many polling booths are in <?php echo htmlspecialchars($cData['name']); ?>?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "There are <?php echo $cData['booth_count']; ?> designated Polling Booths across <?php echo htmlspecialchars($cData['name']); ?> as published in the Final Electoral Roll dated 06.10.2026."
          }
        },
        {
          "@type": "Question",
          "name": "Who is the Returning Officer for <?php echo htmlspecialchars($cData['name']); ?>?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "The Returning Officer is <?php echo htmlspecialchars($cData['ro']); ?>."
          }
        },
        {
          "@type": "Question",
          "name": "Which form is used for voter registration?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Electors must submit <?php echo $formType; ?> under the Registration of Electors Rules."
          }
        }
      ]
    }
  ]
}
</script>

<?php require_once __DIR__ . '/../footer.php'; ?>
