<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Mission & Vision — Bihar Election Data & Civic Intelligence Platform';
$pageDescription = 'Discover the mission, vision, and core civic values driving BiharElection.com: democratizing electoral data, empowering voters, and archiving democratic history across all 38 districts of Bihar.';
$pageKeywords = 'Bihar Election Mission, Bihar Election Vision, biharelection.com About, Bihar Election Intelligence, Bihar Democracy Portal';
$pageCanonical = SITE_URL . '/mission-and-vision';
$activeNav = '';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    🎯 Our Purpose & Roadmap
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    Panchayat to Parliament
                </span>
            </div>

            <h1 class="display-5 fw-extrabold text-white mb-2">
                Our Mission & Vision
            </h1>
            <p class="lead text-white-50 mb-0" style="font-size: 1.1rem; max-width: 850px;">
                Empowering democracy in Bihar through hyper-local transparency, non-partisan electoral statistics, and accessible civic intelligence for 7.64 Crore+ citizens.
            </p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container my-5">

        <!-- Vision & Mission Dual High-Impact Cards -->
        <div class="row g-4 mb-5">
            <!-- Vision Card -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100 bg-white border-top border-4 border-warning">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-warning bg-opacity-10 p-3 rounded-circle text-warning fs-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            🔭
                        </div>
                        <div>
                            <span class="badge bg-warning bg-opacity-25 text-dark fw-bold px-2 py-1 mb-1">Our Ultimate Vision</span>
                            <h2 class="h3 fw-bold mb-0 text-navy">Democratic Transparency for Every Citizen</h2>
                        </div>
                    </div>
                    <p class="text-muted lh-lg mb-3">
                        To become Bihar's most trusted, authoritative, and comprehensive civic intelligence hub—where every voter, candidate, researcher, and community leader has immediate, effortless access to verified electoral data, historical voting trends, and representative rosters.
                    </p>
                    <p class="text-muted lh-lg mb-0">
                        We envision an informed, engaged, and digitally empowered Bihar where political accountability thrives on accessible data from the smallest Gram Panchayat ward to the highest legislative halls.
                    </p>
                </div>
            </div>

            <!-- Mission Card -->
            <div class="col-12 col-lg-6">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 h-100 bg-white border-top border-4 border-primary">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded-circle text-primary fs-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                            🚀
                        </div>
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 mb-1">Our Core Mission</span>
                            <h2 class="h3 fw-bold mb-0 text-navy">Democratizing Electoral Intelligence</h2>
                        </div>
                    </div>
                    <p class="text-muted lh-lg mb-3">
                        To systematically compile, structure, verify, and deliver actionable election data across all 38 Districts, 243 Vidhan Sabha Constituencies, 8,053+ Gram Panchayats, and 40 Lok Sabha seats with zero partisan bias.
                    </p>
                    <p class="text-muted lh-lg mb-0">
                        We bridge the gap between complex government gazettes and the common citizen by transforming dense statistical matrices into intuitive, lightning-fast digital directories and maps.
                    </p>
                </div>
            </div>
        </div>

        <!-- 4 Core Pillars of Bihar Election -->
        <section class="mb-5">
            <div class="text-center mb-4">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-3 py-2 mb-2">Our Foundation</span>
                <h2 class="h3 fw-bold text-navy">The Four Pillars Guiding Our Work</h2>
                <p class="text-muted mx-auto" style="max-width: 650px;">How we curate, structure, and maintain Bihar's largest independent election repository.</p>
            </div>

            <div class="row g-4">
                <!-- Pillar 1 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                        <div class="fs-2 mb-3">🌾</div>
                        <h3 class="h5 fw-bold text-navy mb-2">1. Hyper-Local Focus</h3>
                        <p class="small text-muted mb-0 lh-lg">
                            Covering grassroots Panchayati Raj tiers: 7,346 Mukhiyas, 6,617 Sarpanchs, 1,099+ Zila Parishad Ward Members, and 389 Block Pramukhs alongside state MLAs.
                        </p>
                    </div>
                </div>

                <!-- Pillar 2 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                        <div class="fs-2 mb-3">⚖️</div>
                        <h3 class="h5 fw-bold text-navy mb-2">2. Strict Non-Partisanship</h3>
                        <p class="small text-muted mb-0 lh-lg">
                            We operate with absolute neutrality. We do not endorse candidates or political parties. Every party and candidate is presented with equal, objective statistical parity.
                        </p>
                    </div>
                </div>

                <!-- Pillar 3 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                        <div class="fs-2 mb-3">⚡</div>
                        <h3 class="h5 fw-bold text-navy mb-2">3. Lightning Performance</h3>
                        <p class="small text-muted mb-0 lh-lg">
                            Optimized with zero-latency JSON caching, instant multi-tier pagination, mobile touch-friendly navigation, and sub-millisecond search capabilities.
                        </p>
                    </div>
                </div>

                <!-- Pillar 4 -->
                <div class="col-12 col-md-6 col-lg-3">
                    <div class="card border-0 shadow-sm rounded-4 p-4 h-100 bg-white">
                        <div class="fs-2 mb-3">🔒</div>
                        <h3 class="h5 fw-bold text-navy mb-2">4. Privacy & Integrity</h3>
                        <p class="small text-muted mb-0 lh-lg">
                            Committed to data protection by obfuscating representative contact numbers against spam harvesting while cross-verifying facts against official gazettes.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our 2026-2030 Strategic Roadmap -->
        <section class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-5 bg-white">
            <div class="row align-items-center g-4">
                <div class="col-12 col-lg-5">
                    <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-3 py-2 mb-2">Strategic Roadmap</span>
                    <h2 class="h3 fw-bold text-navy mb-3">Building the Future of Civic Tech in Bihar</h2>
                    <p class="text-muted lh-lg mb-4">
                        From our 2025 foundation to the upcoming 2026 Panchayat Delimitation and 2030 general elections, here is our ongoing commitment to the citizens of Bihar.
                    </p>
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-4 py-2 shadow-sm d-inline-flex align-items-center gap-2">
                        <i class="bi bi-whatsapp"></i> Join WhatsApp Civic Community
                    </a>
                </div>

                <div class="col-12 col-lg-7">
                    <div class="p-3 p-md-4 bg-light rounded-3 border">
                        <div class="d-flex gap-3 mb-3">
                            <div class="badge bg-primary rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">1</div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Complete Delimitation & Reservation Tracking</h3>
                                <p class="small text-muted mb-0">Providing real-time updates on 2026 Panchayat delimitation, Mukhiya roster rotations, and Zila Parishad ward boundaries.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="badge bg-success rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">2</div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Verified Candidate Intelligence Portfolios</h3>
                                <p class="small text-muted mb-0">Enabling political aspirants across 243 ACs to publish verifiable public biographies, asset declarations, and developmental manifestos.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3 mb-3">
                            <div class="badge bg-warning text-dark rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">3</div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Deep Census & Demographic Correlation</h3>
                                <p class="small text-muted mb-0">Integrating 2011 Primary Census Abstracts with voter registration matrices to illuminate local socio-economic dynamics.</p>
                            </div>
                        </div>
                        <div class="d-flex gap-3">
                            <div class="badge bg-info text-dark rounded-circle p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 32px; height: 32px;">4</div>
                            <div>
                                <h3 class="h6 fw-bold text-dark mb-1">Grassroots Voter Education</h3>
                                <p class="small text-muted mb-0">Simplifying electoral literacy and guiding citizens to official ECI voter registration portals without barriers.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 text-center text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
            <h2 class="display-6 fw-bold mb-3">Be a Part of Bihar's Civic Transformation</h2>
            <p class="lead text-white-50 mx-auto mb-4" style="max-width: 700px;">
                Whether you are a voter seeking polling insights, a candidate connecting with constituents, or a researcher analyzing voting trends—Bihar Election is built for you.
            </p>
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="<?php echo SITE_URL; ?>/panchayat" class="btn btn-warning btn-lg fw-bold text-dark px-4 py-2 shadow-sm">
                    🌾 Explore Panchayat Hub
                </a>
                <a href="<?php echo SITE_URL; ?>/mla/118-chapra" class="btn btn-outline-light btn-lg fw-bold px-4 py-2 shadow-sm">
                    🗳️ Browse 243 Assembly Seats
                </a>
            </div>
        </div>

    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
