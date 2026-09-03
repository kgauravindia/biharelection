<?php
/**
 * Bihar Election - About Platform & Editorial Mission
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = 'About Us — Bihar Election Intelligence & Democratic Data Hub';
$pageDescription = 'BiharElection.com is Bihar’s premier open civic intelligence and electoral analytics portal covering 243 Vidhan Sabha seats, 8,000+ Panchayats, and candidate profiles.';
$pageKeywords = 'About Bihar Election, Bihar Election Portal, OfferPlant Technologies Patna, Bihar Vidhan Sabha Analytics, Panchayat Data Bihar';
$pageCanonical = SITE_URL . '/about/';
$activeNav = 'about';

include __DIR__ . '/header.php';
?>

<main class="py-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container">
        
        <!-- Hero Section -->
        <div class="row align-items-center mb-5 pb-3">
            <div class="col-lg-7">
                <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">Empowering Democracy</span>
                <h1 class="display-5 fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">
                    Bihar's Most Comprehensive <span class="text-danger">Electoral & Civic</span> Intelligence Portal
                </h1>
                <p class="lead text-muted mb-4" style="line-height: 1.7;">
                    <strong>BiharElection.com</strong> (operated by <strong>OfferPlant Technologies Private Limited</strong>) is an independent, non-partisan civic data platform dedicated to delivering transparent, data-driven electoral insights across Bihar’s 38 Districts, 243 Assembly Constituencies, and 8,000+ Gram Panchayats.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="<?php echo SITE_URL; ?>/vidhan-sabha" class="btn btn-danger btn-lg rounded-pill px-4 fw-bold shadow-sm">
                        Explore 243 ACs &rarr;
                    </a>
                    <a href="<?php echo SITE_URL; ?>/panchayats" class="btn btn-outline-dark btn-lg rounded-pill px-4 fw-bold">
                        Panchayat Directory
                    </a>
                </div>
            </div>
            <div class="col-lg-5 mt-4 mt-lg-0 text-center">
                <div class="bg-white p-4 rounded-4 shadow-sm border">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-danger mb-0" style="font-family: 'Outfit', sans-serif;">243</div>
                                <span class="small text-muted fw-semibold">Vidhan Sabha Seats</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-primary mb-0" style="font-family: 'Outfit', sans-serif;">40</div>
                                <span class="small text-muted fw-semibold">Lok Sabha PCs</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-success mb-0" style="font-family: 'Outfit', sans-serif;">8,053+</div>
                                <span class="small text-muted fw-semibold">Gram Panchayats</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="h2 fw-bold text-warning mb-0" style="font-family: 'Outfit', sans-serif;">38</div>
                                <span class="small text-muted fw-semibold">Districts Hubs</span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-top text-start">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-shield-check text-success fs-4"></i>
                            <span class="small text-muted">100% Non-Partisan & Verifiable Election Commission / Census Records</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pillar Highlights -->
        <div class="row g-4 mb-5">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-danger-subtle text-danger p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-newspaper fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">Grassroots Journalism</h5>
                    <p class="text-muted small mb-0">Unbiased reporting on campaign dynamics, voter turnout trends, local issues, and ground-level civic debates from every corner of Bihar.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-primary-subtle text-primary p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-bar-chart-line fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">Data & Demographics</h5>
                    <p class="text-muted small mb-0">Synthesized Census 2011 demographics, block-level metrics, historical MLA/Mukhiya records, and official Election Commission data archives.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                    <div class="bg-success-subtle text-success p-3 rounded-4 d-inline-flex align-items-center justify-content-center mb-3" style="width: 50px; height: 50px;">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">Citizen Empowerment</h5>
                    <p class="text-muted small mb-0">Equipping voters, researchers, civil society organizations, and journalists with instant lookup tools for polling stations, PIN codes, and candidate histories.</p>
                </div>
            </div>
        </div>

        <!-- Corporate & Leadership Overview -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white mb-5">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <h4 class="fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif;">Operated by OfferPlant Technologies Private Limited</h4>
                    <p class="text-muted mb-3" style="line-height: 1.7;">
                        Based out of Patna, Bihar, <strong>OfferPlant Technologies Private Limited</strong> is a leading technology and digital solutions enterprise. Through BiharElection.com, we combine cutting-edge software architecture, high-speed database indices, and deep local journalism to create Bihar’s most authoritative civic knowledge graph.
                    </p>
                    <p class="text-muted mb-0 small">
                        Our digital newsroom and data verification desk operate round-the-clock during election cycles to ensure zero misinformation, instant booth-level fact-checking, and seamless voter literacy dissemination.
                    </p>
                </div>
                <div class="col-lg-4 text-center">
                    <div class="p-4 bg-light rounded-4 border">
                        <i class="bi bi-building-check text-danger display-4 mb-2 d-block"></i>
                        <h6 class="fw-bold text-dark mb-1">Corporate Headquarters</h6>
                        <p class="text-muted small mb-3">Patna, Bihar, India</p>
                        <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">
                            Contact Us &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Social Media Network & Community -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-white overflow-hidden text-center" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
            <div class="max-w-700 mx-auto" style="max-width: 700px;">
                <span class="badge bg-warning text-dark fw-bold text-uppercase px-3 py-2 rounded-pill mb-3">Community Hub</span>
                <h3 class="fw-bold mb-3" style="font-family: 'Outfit', sans-serif;">Join Bihar's Largest Civic Awareness Network</h3>
                <p class="opacity-90 mb-4">Connect with <strong>@BiharElectionAI</strong> across social platforms to get daily news bulletins, candidate debates, and live election result updates.</p>
                
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold rounded-pill px-4 py-2">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp
                    </a>
                    <a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="btn btn-info text-white fw-bold rounded-pill px-4 py-2">
                        <i class="bi bi-telegram me-1"></i> Telegram
                    </a>
                    <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="btn btn-primary fw-bold rounded-pill px-4 py-2">
                        <i class="bi bi-facebook me-1"></i> Facebook
                    </a>
                    <a href="<?php echo TWITTER_URL; ?>" target="_blank" class="btn btn-dark fw-bold rounded-pill px-4 py-2 border">
                        <i class="bi bi-twitter-x me-1"></i> X / Twitter
                    </a>
                    <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="btn btn-danger fw-bold rounded-pill px-4 py-2">
                        <i class="bi bi-instagram me-1"></i> Instagram
                    </a>
                    <a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="btn btn-danger fw-bold rounded-pill px-4 py-2">
                        <i class="bi bi-youtube me-1"></i> YouTube
                    </a>
                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
