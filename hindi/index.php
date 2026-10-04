<?php
require_once __DIR__ . '/includes/functions.php';

$districts = DataProvider::getDistricts();
// Sort districts alphabetically A-Z
usort($districts, function($a, $b) {
    return strcmp($a['name'] ?? '', $b['name'] ?? '');
});

$constituencies = DataProvider::getConstituencies();
$candidates = DataProvider::getCandidates();
$news = DataProvider::getNews();
$panchayats = DataProvider::getPanchayatData();

// Fetch latest blog article
$latest_post = null;
$pdo_home = Database::getConnection();
if ($pdo_home) {
    try {
        $stmt_post = $pdo_home->query("SELECT id, title, slug, published_at, categories FROM `posts` WHERE `status` = 'published' ORDER BY `published_at` DESC, `id` DESC LIMIT 1");
        if ($stmt_post) {
            $latest_post = $stmt_post->fetch(PDO::FETCH_ASSOC);
        }
    } catch (Throwable $e) {
        // fallback
    }
}

$pageTitle = 'बिहार चुनाव 2026: 243 विधानसभा, 38 जिले एवं 8,053 पंचायत डेटा हब';
$pageDescription = 'बिहार का सबसे व्यापक गैर-सरकारी चुनावी डेटा पोर्टल। 243 विधानसभा क्षेत्र, 38 जिले (पटना, मुजफ्फरपुर, गया, भागलपुर), 534 प्रखंड एवं त्रिस्तरीय पंचायती राज का संपूर्ण डेटा।';
$pageKeywords = 'बिहार चुनाव 2026, 243 बिहार विधानसभा क्षेत्र, पटना विधानसभा, बिहार चुनाव परिणाम, 38 जिले बिहार, बिहार पंचायत 2026, बिहार विधायक सूची, बिहार राजनीतिक डेटा';
$pageCanonical = hindi_base_url();
$activeNav = 'home';

require_once __DIR__ . '/includes/header.php';
?>

    <!-- Hero Section with Bootstrap 5.3 Responsive Grid -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-center py-2 py-lg-3">
            <div class="hero-badge mb-3">
                <i class="bi bi-shield-check"></i> स्वतंत्र गैर-सरकारी चुनावी डेटा एवं विश्लेषण मंच
            </div>
            <h1 class="hero-title display-5 fw-extrabold mb-3">
                बिहार का अग्रणी <br>
                <span>चुनावी डेटा एवं राजनीतिक सूचना हब</span>
            </h1>
            <p class="hero-subtitle lead text-white-50 mb-4 mx-auto" style="max-width: 820px;">
                पंचायत से संसद तक: 38 जिले, 243 विधानसभा निर्वाचन क्षेत्र, 8,053+ ग्राम पंचायतें और 2026 चुनावी परिसीमन का सत्यापित ऐतिहासिक एवं राजनीतिक रिकॉर्ड।
            </p>

            <!-- Search Hub Widget with Dynamic Suggestions Dropdown -->
            <div class="search-widget mx-auto" style="max-width: 680px;">
                <div class="search-input-group">
                    <input 
                        type="text" 
                        id="globalSearchInput" 
                        class="search-input" 
                        placeholder="विधायक, सांसद, मुखिया, सरपंच, जिला या विधानसभा खोजें..."
                        autocomplete="off"
                        aria-label="बिहार के जनप्रतिनिधियों और प्रशासनिक इकाइयों को खोजें"
                    >
                    <button class="btn-search" type="button" onclick="document.getElementById('globalSearchInput').focus()">
                        <i class="bi bi-search"></i> <span class="d-none d-sm-inline">खोजें</span>
                    </button>
                </div>

                <!-- Instant Dynamic Suggestions Dropdown -->
                <div id="searchDropdown" class="search-dropdown"></div>
            </div>

            <!-- Quick Pill Links -->
            <div class="d-flex flex-wrap justify-content-center align-items-center gap-1.5 gap-sm-2 mt-3 pt-1 hero-quick-pills">
                <span class="small text-white-50 me-1 fw-semibold"><i class="bi bi-fire text-warning"></i> प्रमुख लिंक:</span>
                <a href="<?php echo getDistrictUrl('patna'); ?>" class="pill-link fw-bold text-warning">👑 पटना हब</a>
                <a href="<?php echo getVillageUrl(); ?>" class="pill-link fw-bold text-info">🏘️ 44,874 गांव</a>
                <a href="<?php echo getTownUrl(); ?>" class="pill-link fw-bold text-primary">🏙️ 198 शहर व स्लम</a>
                <a href="<?php echo getCasteSurveyUrl(); ?>" class="pill-link fw-bold text-warning">📋 2022 जाति गणना</a>
                <a href="<?php echo getBiharActsUrl(); ?>" class="pill-link fw-bold text-success">⚖️ बिहार अधिनियम (1937–2026)</a>
                <a href="<?php echo hindi_base_url('mla'); ?>" class="pill-link">🗳️ 243 विधायक</a>
                <a href="<?php echo getMpUrl(); ?>" class="pill-link">🏛️ 40 सांसद</a>
                <a href="<?php echo getMlcUrl(); ?>" class="pill-link">📜 75 एमएलसी</a>
                <a href="<?php echo getZilaParishadUrl(); ?>" class="pill-link">🏛️ जिला परिषद</a>
                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="pill-link">🏢 534 प्रखंड प्रमुख</a>
                <a href="<?php echo getPanchayatUrl(); ?>" class="pill-link">🌾 8,053+ मुखिया</a>
                <a href="<?php echo getPanchayatUrl(); ?>" class="pill-link">⚖️ 8,053+ सरपंच</a>
            </div>
        </div>
    </section>

    <!-- Latest Blog & Editorial Single Line Headline Strip -->
    <?php if (!empty($latest_post)): ?>
    <div class="container py-2" style="position: relative; z-index: 15; margin-top: -12px; margin-bottom: 6px;">
        <div class="bg-white border rounded-pill py-1.5 px-3 shadow-sm d-flex align-items-center justify-content-between gap-2 overflow-hidden">
            <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate flex-grow-1">
                <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 text-uppercase fw-bold text-nowrap" style="font-size: 11px;">
                    <i class="bi bi-newspaper me-1"></i> नवीनतम लेख
                </span>
                <a href="<?php echo getBlogUrl($latest_post['slug']); ?>" class="text-dark fw-bold text-truncate text-decoration-none hover-primary small mb-0 d-block" title="<?php echo htmlspecialchars($latest_post['title']); ?>">
                    <?php echo htmlspecialchars($latest_post['title']); ?>
                </a>
            </div>
            <a href="<?php echo getBlogUrl(); ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3 py-0.5 text-nowrap fw-semibold d-none d-sm-inline-flex align-items-center gap-1" style="font-size: 12px;">
                ब्लॉग देखें &rarr;
            </a>
        </div>
    </div>
    <?php endif; ?>

    <!-- Live Governance & Electoral Stat Grid Bar -->
    <div class="container governance-stat-container" style="<?php echo !empty($latest_post) ? 'margin-top: -8px;' : 'margin-top: -28px;'; ?> position: relative; z-index: 10;">
        <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-4 row-cols-xl-4 row-cols-xxl-8">
            <!-- 1: Assembly MLAs -->
            <div class="col">
                <a href="<?php echo hindi_base_url('mla'); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-mla">
                            🗳️
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            विधान सभा
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">243</div>
                        <div class="fw-bold text-dark stat-title mt-1">विधायक (MLAs)</div>
                        <div class="stat-subtitle text-muted">243 निर्वाचन क्षेत्र</div>
                    </div>
                </a>
            </div>

            <!-- 2: Lok Sabha MPs -->
            <div class="col">
                <a href="<?php echo getMpUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-mp">
                            🏛️
                        </div>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            लोक सभा
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">40</div>
                        <div class="fw-bold text-dark stat-title mt-1">सांसद (MPs)</div>
                        <div class="stat-subtitle text-muted">40 संसदीय सीटें</div>
                    </div>
                </a>
            </div>

            <!-- 3: Zila Parishad Adhyaksh -->
            <div class="col">
                <a href="<?php echo getZilaParishadUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-zp">
                            📍
                        </div>
                        <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill" style="color: #7c3aed; background: rgba(124,58,237,0.1); border-color: rgba(124,58,237,0.25) !important;">
                            1,153+ वार्ड
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">38</div>
                        <div class="fw-bold text-dark stat-title mt-1">जिला परिषद अध्यक्ष</div>
                        <div class="stat-subtitle text-muted">38 जिला बोर्ड</div>
                    </div>
                </a>
            </div>

            <!-- 4: Pramukh / Up-Pramukh -->
            <div class="col">
                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-ps">
                            🏢
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            534 प्रखंड
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">534</div>
                        <div class="fw-bold text-dark stat-title mt-1">प्रखंड प्रमुख</div>
                        <div class="stat-subtitle text-muted">पंचायत समितियां</div>
                    </div>
                </a>
            </div>

            <!-- 5: Mukhiya & Sarpanch -->
            <div class="col">
                <a href="<?php echo getPanchayatUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-gp">
                            🌾
                        </div>
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            ग्राम पंचायत
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">8,053+</div>
                        <div class="fw-bold text-dark stat-title mt-1">मुखिया एवं सरपंच</div>
                        <div class="stat-subtitle text-muted">ग्राम प्रधान नेतृत्व</div>
                    </div>
                </a>
            </div>

            <!-- 6: Statutory Towns & Slums -->
            <div class="col">
                <a href="<?php echo getTownUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-town">
                            🏙️
                        </div>
                        <span class="badge bg-indigo bg-opacity-10 text-indigo border border-indigo border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill" style="color: #4338ca; background: #e0e7ff;">
                            नगर निकाय
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">198</div>
                        <div class="fw-bold text-dark stat-title mt-1">शहर एवं स्लम</div>
                        <div class="stat-subtitle text-muted">670 मलिन बस्तियां</div>
                    </div>
                </a>
            </div>

            <!-- 7: Census Villages -->
            <div class="col">
                <a href="<?php echo getVillageUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-village">
                            🏘️
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            जनगणना 2011
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">44,874</div>
                        <div class="fw-bold text-dark stat-title mt-1">ग्रामीण गांव</div>
                        <div class="stat-subtitle text-muted">38 जिले मैट्रिक्स</div>
                    </div>
                </a>
            </div>

            <!-- 8: Total Electors -->
            <div class="col">
                <a href="<?php echo getCensusUrl(); ?>" class="governance-stat-card p-2.5 p-sm-3 h-100 d-flex flex-column justify-content-between text-decoration-none text-reset">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="stat-icon-wrapper stat-icon-voters">
                            👥
                        </div>
                        <span class="badge bg-info bg-opacity-10 text-dark border border-info border-opacity-25 stat-badge fw-bold px-2 py-0.5 rounded-pill">
                            मतदाता
                        </span>
                    </div>
                    <div>
                        <div class="stat-number fw-extrabold text-navy mb-0 lh-1">7.64 Cr+</div>
                        <div class="fw-bold text-dark stat-title mt-1">कुल मतदाता</div>
                        <div class="stat-subtitle text-muted">बिहार निर्वाचक मंडल</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="container my-5">

        <!-- Bihar 3-Tier Panchayati Raj Leadership & Local Governance Structure -->
        <section class="my-5 py-2">
            <!-- Section Header with Clear Spacing -->
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill small shadow-sm">
                            <i class="bi bi-diagram-3-fill me-1"></i> त्रिस्तरीय पंचायती राज व्यवस्था
                        </span>
                    </div>
                    <h2 class="h3 fw-bold mb-1" style="color: var(--primary-navy); font-family: var(--font-heading);">
                        बिहार पंचायती राज नेतृत्व निर्देशिका
                    </h2>
                    <p class="text-muted mb-0 small lh-base">
                        जिला (जिला परिषद) से प्रखंड (पंचायत समिति) और ग्राम (ग्राम पंचायत एवं कचहरी) तक पूर्ण निर्वाचित नेतृत्व संरचना।
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo getZilaParishadUrl(); ?>" class="btn btn-outline-primary btn-sm fw-bold rounded-pill px-3 py-1.5 shadow-sm">
                        <i class="bi bi-shield-shaded me-1"></i> जिला परिषद &rarr;
                    </a>
                    <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="btn btn-outline-success btn-sm fw-bold rounded-pill px-3 py-1.5 shadow-sm">
                        <i class="bi bi-building-gear me-1"></i> पंचायत समिति &rarr;
                    </a>
                </div>
            </div>

            <!-- 3-Tier Leadership Cards Grid -->
            <div class="row g-4">
                <!-- Tier 1: Zila Parishad (District Level) -->
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="background: linear-gradient(180deg, #ffffff 0%, #f8faff 100%); border-top: 4px solid #4338ca !important;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-indigo text-white fw-bold px-2.5 py-1 rounded-pill small" style="background-color: #4338ca;">
                                        Tier 1: जिला स्तर
                                    </span>
                                    <span class="text-muted small fw-semibold"><i class="bi bi-geo-alt me-1"></i>38 जिले</span>
                                </div>
                                <h3 class="h4 fw-bold mb-2 text-dark">
                                    जिला परिषद अध्यक्ष
                                </h3>
                                <p class="text-muted small mb-3 lh-sm">
                                    जिला परिषद के अध्यक्ष जिले की ग्रामीण विकास परियोजनाओं, निधि आवंटन एवं जिला प्रादेशिक निर्वाचन क्षेत्रों का नेतृत्व करते हैं।
                                </p>
                                <div class="bg-white p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">कुल जिला परिषद:</span>
                                        <span class="fw-bold text-dark">38 बोर्ड</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">प्रादेशिक निर्वाचन क्षेत्र:</span>
                                        <span class="fw-bold text-dark">1,153+ वार्ड</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">उप-अध्यक्ष एवं सदस्य:</span>
                                        <span class="badge bg-success-subtle text-success fw-bold">सत्यापित डेटा</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <a href="<?php echo getZilaParishadUrl(); ?>" class="btn btn-primary w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm" style="background-color: #4338ca; border-color: #4338ca;">
                                    <span>जिला परिषद निर्देशिका देखें</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier 2: Panchayat Samiti (Block Level) -->
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="background: linear-gradient(180deg, #ffffff 0%, #f0fdf4 100%); border-top: 4px solid #16a34a !important;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill small">
                                        Tier 2: प्रखंड स्तर
                                    </span>
                                    <span class="text-muted small fw-semibold"><i class="bi bi-buildings me-1"></i>534 प्रखंड</span>
                                </div>
                                <h3 class="h4 fw-bold mb-2 text-dark">
                                    प्रखंड प्रमुख (पंचायत समिति)
                                </h3>
                                <p class="text-muted small mb-3 lh-sm">
                                    प्रखंड प्रमुख एवं उप-प्रमुख पंचायत समिति की बैठकें संचालित करते हैं तथा प्रखंड विकास पदाधिकारी (BDO) के साथ ग्रामीण विकास का समन्वय करते हैं।
                                </p>
                                <div class="bg-white p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">कुल प्रखंड प्रमुख:</span>
                                        <span class="fw-bold text-dark">534 पद</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">पंचायत समिति सदस्य:</span>
                                        <span class="fw-bold text-dark">11,497+ सदस्य</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">कार्यपालक पदाधिकारी:</span>
                                        <span class="badge bg-primary-subtle text-primary fw-bold">प्रखंड बीडीओ</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <a href="<?php echo getPanchayatSamitiUrl(); ?>" class="btn btn-success w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <span>प्रखंड प्रमुख निर्देशिका देखें</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tier 3: Gram Panchayat (Village Level) -->
                <div class="col-12 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative" style="background: linear-gradient(180deg, #ffffff 0%, #fffbeb 100%); border-top: 4px solid #d97706 !important;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small">
                                        Tier 3: ग्राम स्तर
                                    </span>
                                    <span class="text-muted small fw-semibold"><i class="bi bi-tree me-1"></i>8,053+ पंचायतें</span>
                                </div>
                                <h3 class="h4 fw-bold mb-2 text-dark">
                                    मुखिया एवं सरपंच (ग्राम पंचायत)
                                </h3>
                                <p class="text-muted small mb-3 lh-sm">
                                    मुखिया ग्राम पंचायत के कार्यकारी प्रमुख हैं तथा सरपंच ग्राम कचहरी के न्यायिक प्रमुख हैं। साथ में उप-मुखिया, उप-सरपंच एवं वार्ड सदस्य कार्यरत हैं।
                                </p>
                                <div class="bg-white p-3 rounded-3 border mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">कुल ग्राम पंचायतें:</span>
                                        <span class="fw-bold text-dark">8,053+</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small">वार्ड सदस्य एवं पंच:</span>
                                        <span class="fw-bold text-dark">1,14,000+</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">ग्राम कचहरी सरपंच:</span>
                                        <span class="badge bg-warning-subtle text-dark fw-bold">8,053+ न्यायिक पीठ</span>
                                    </div>
                                </div>
                            </div>
                            <div class="pt-2">
                                <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-warning w-100 text-dark rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <span>ग्राम पंचायत निर्देशिका देखें</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bihar 38 Districts Grid Section -->
        <section class="my-5">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
                <div>
                    <h2 class="h3 fw-bold mb-1" style="color: var(--primary-navy); font-family: var(--font-heading);">
                        बिहार के सभी 38 जिले
                    </h2>
                    <p class="text-muted mb-0 small">
                        प्रशासनिक विवरण, मुख्यालय, प्रखंड, विधानसभा सीटें एवं 2026 चुनावी आंकड़े।
                    </p>
                </div>
                <a href="<?php echo hindi_base_url('district'); ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
                    सभी 38 जिले हब &rarr;
                </a>
            </div>

            <!-- District Cards Grid -->
            <div class="row g-3 row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-4">
                <?php foreach ($districts as $d): 
                    $dSlug = $d['slug'] ?? slugify($d['name']);
                ?>
                <div class="col">
                    <a href="<?php echo getDistrictUrl($dSlug); ?>" class="card h-100 text-decoration-none border shadow-sm rounded-3 p-3 hover-shadow-lg transition-all" style="background: #fff;">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 rounded-pill small">
                                <?php echo htmlspecialchars($d['division'] ?? 'प्रमंडल'); ?>
                            </span>
                            <span class="small text-muted fw-semibold">
                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($d['headquarters'] ?? $d['name']); ?>
                            </span>
                        </div>
                        <h4 class="h5 fw-bold text-dark mb-1">
                            <?php echo htmlspecialchars($d['name_hi'] ?? $d['name']); ?>
                        </h4>
                        <div class="small text-muted mb-2">
                            जिला मुख्यालय: <strong><?php echo htmlspecialchars($d['headquarters'] ?? $d['name']); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top text-muted small mt-auto">
                            <span><i class="bi bi-building"></i> <?php echo (int)($d['blocks_count'] ?? 10); ?> प्रखंड</span>
                            <span><i class="bi bi-check2-circle text-success"></i> प्रोफाइल &rarr;</span>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <!-- 243 Vidhan Sabha Constituencies Quick Access -->
        <section class="my-5 py-4 bg-light rounded-4 px-3 px-md-4 border">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
                <div>
                    <h2 class="h3 fw-bold mb-1" style="color: var(--primary-navy); font-family: var(--font-heading);">
                        243 विधानसभा निर्वाचन क्षेत्र
                    </h2>
                    <p class="text-muted mb-0 small">
                        बिहार विधानसभा के सभी 243 निर्वाचन क्षेत्र, वर्तमान विधायक, दलवार आंकड़े एवं पिछले चुनाव परिणाम।
                    </p>
                </div>
                <a href="<?php echo hindi_base_url('mla'); ?>" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
                    संपूर्ण 243 विधायक सूची &rarr;
                </a>
            </div>

            <div class="row g-2 g-md-3 row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6">
                <?php 
                $sampleACs = array_slice($constituencies, 0, 18);
                foreach ($sampleACs as $ac): 
                ?>
                <div class="col">
                    <a href="<?php echo getMlaUrl($ac); ?>" class="btn btn-white w-100 border text-start p-2.5 rounded-3 shadow-none hover-shadow text-truncate d-block text-decoration-none">
                        <span class="badge bg-secondary bg-opacity-10 text-dark small mb-1 d-inline-block">
                            AC #<?php echo (int)($ac['ac_no'] ?? 0); ?>
                        </span>
                        <div class="fw-bold text-dark text-truncate small">
                            <?php echo htmlspecialchars($ac['name_hi'] ?? $ac['name']); ?>
                        </div>
                        <div class="text-muted" style="font-size: 11px;">
                            <?php echo htmlspecialchars($ac['district_name_hi'] ?? $ac['district_name'] ?? ''); ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="text-center mt-4">
                <a href="<?php echo hindi_base_url('mla'); ?>" class="btn btn-outline-dark rounded-pill px-4 py-2 fw-bold">
                    सभी 243 विधानसभा क्षेत्र देखें &rarr;
                </a>
            </div>
        </section>

    </main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
