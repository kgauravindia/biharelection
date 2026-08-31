<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Official Disclaimer & Non-Government Declaration — Bihar Election';
$pageDescription = 'Official disclaimer, non-affiliation notice, and data aggregation policy for biharelection.com independent election platform.';
$pageKeywords = 'Bihar Election Disclaimer, Non Government Entity, ECI SEC Bihar Non Affiliation, biharelection.com disclaimer';
$pageCanonical = SITE_URL . '/disclaimer';
$activeNav = '';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-danger bg-opacity-25 text-white fw-bold px-3 py-2 border border-danger border-opacity-50">
                    ⚠️ Official Non-Affiliation Declaration
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    Updated: August 2026
                </span>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Platform Disclaimer
            </h1>
            <p class="lead text-white-50 mb-0" style="font-size: 1.05rem; max-width: 850px;">
                Transparency, non-governmental declaration, data sources, and non-partisan neutrality statement for <strong>Bihar Election (biharelection.com)</strong>.
            </p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container my-5">
        <div class="row g-4">
            
            <!-- Quick Navigation Sidebar (4 Cols) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 bg-white position-sticky" style="top: 90px;">
                    <h2 class="h6 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-shield-exclamation text-danger me-2"></i> Disclaimer Sections
                    </h2>
                    <ul class="list-unstyled small mb-0 lh-lg">
                        <li><a href="#section-non-govt" class="text-decoration-none text-danger fw-bold">1. Non-Government Entity Notice</a></li>
                        <li><a href="#section-sources" class="text-decoration-none text-navy fw-semibold">2. Data Sources & Compilation</a></li>
                        <li><a href="#section-neutrality" class="text-decoration-none text-navy fw-semibold">3. Non-Partisan & No Endorsement</a></li>
                        <li><a href="#section-electoral" class="text-decoration-none text-navy fw-semibold">4. Official Voting Portals</a></li>
                        <li><a href="#section-privacy" class="text-decoration-none text-navy fw-semibold">5. Contact Obfuscation & Privacy</a></li>
                        <li><a href="#section-liability" class="text-decoration-none text-navy fw-semibold">6. Limitation of Liability</a></li>
                        <li><a href="#section-contact" class="text-decoration-none text-navy fw-semibold">7. Corrections & Redressal</a></li>
                    </ul>

                    <hr class="my-3">
                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <div class="fw-bold text-dark mb-1">Official Election Authorities:</div>
                        <ul class="list-unstyled mb-0">
                            <li class="mb-1"><a href="https://eci.gov.in" target="_blank" rel="noopener" class="text-primary text-decoration-none"><i class="bi bi-box-arrow-up-right me-1"></i> ECI (eci.gov.in)</a></li>
                            <li><a href="http://sec.bihar.gov.in" target="_blank" rel="noopener" class="text-primary text-decoration-none"><i class="bi bi-box-arrow-up-right me-1"></i> SEC Bihar (sec.bihar.gov.in)</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Detailed Disclaimer Content (8 Cols) -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">

                    <!-- Section 1: Non-Govt Entity -->
                    <div class="mb-5" id="section-non-govt">
                        <div class="d-flex align-items-center gap-2 mb-3">
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1">Notice 1</span>
                            <h2 class="h4 fw-bold mb-0 text-danger">1. Non-Government Entity Declaration</h2>
                        </div>
                        
                        <div class="p-4 bg-danger bg-opacity-10 border-start border-4 border-danger rounded-end mb-3">
                            <h3 class="h6 fw-bold text-danger mb-2">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> EXPLICIT NON-AFFILIATION NOTICE:
                            </h3>
                            <p class="small text-danger mb-0 fw-semibold lh-base">
                                <strong>Bihar Election (biharelection.com)</strong> is an independent, non-governmental civic intelligence, electoral statistical archive, and information directory platform. We are <strong>NOT</strong> an official government website, agency, statutory body, or affiliate of the <strong>Election Commission of India (ECI)</strong>, the <strong>State Election Commission (SEC) Bihar</strong>, the <strong>Chief Electoral Officer (CEO) Bihar</strong>, or the <strong>Government of Bihar / Government of India</strong>.
                            </p>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            The use of terms such as "Bihar Election", "Vidhan Sabha", "Lok Sabha", "Mukhiya", "Sarpanch", "Zila Parishad", or administrative territorial names is done strictly in an educational, informative, and civic news-archiving capacity.
                        </p>
                    </div>

                    <!-- Section 2: Data Sources -->
                    <div class="mb-5" id="section-sources">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Notice 2</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">2. Data Sources & Compilation Methodology</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            The electoral data, past election results (2025, 2020, 2015), voter demographics, Census 2011 Primary Census Abstracts, and directory rosters (7,346 Mukhiyas, 6,617 Sarpanchs, 1,099+ Zila Parishad Ward Members, MLAs, MLCs, and MPs) published on this Website are aggregated from public domain sources, including:
                        </p>
                        <ul class="text-muted small lh-lg mb-3">
                            <li>Official statistical reports published by the Election Commission of India (ECI).</li>
                            <li>Public Panchayati Raj representative gazettes released by SEC Bihar.</li>
                            <li>Certified Form 26 candidate affidavits submitted during nomination filings.</li>
                            <li>Registrar General & Census Commissioner of India (Office of the Census Commissioner, 2011).</li>
                            <li>Reputable press releases, regional media coverage, and verified candidate submissions.</li>
                        </ul>
                        <p class="text-muted small lh-lg mb-0">
                            While substantial effort is devoted to ensuring cross-verified accuracy and algorithmic deduplication, biharelection.com does not warrant that all records are completely exhaustive or error-free.
                        </p>
                    </div>

                    <!-- Section 3: Non-Partisan -->
                    <div class="mb-5" id="section-neutrality">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Notice 3</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">3. Non-Partisan & No Political Endorsement</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            Bihar Election operates with strict journalistic and data-driven neutrality. The presentation of candidate profiles, political parties (such as RJD, BJP, JD(U), INC, CPI-ML, HAM(S), VIP, AIMIM, and Independents), victory margins, and historical voting trends is purely objective and non-partisan.
                        </p>
                        <p class="text-muted small lh-lg mb-0">
                            The presence of paid commercial advertisements or local business sponsorships on constituency or district pages does not imply any recommendation, sponsorship, or political affiliation by the operators of this platform.
                        </p>
                    </div>

                    <!-- Section 4: Official Voting Portals -->
                    <div class="mb-5" id="section-electoral">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Notice 4</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">4. Official Voting Portals & Voter Services</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            This website does <strong>NOT</strong> collect votes, register voters onto electoral rolls, issue Voter ID Cards (EPIC), accept statutory candidate nomination forms, or conduct online polling. For statutory election services, citizens must exclusively visit the official government portals:
                        </p>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fw-bold text-dark mb-1">Voters' Services Portal (ECI)</div>
                                    <p class="small text-muted mb-2">For Form 6 (New Voter), Form 7, Form 8, EPIC download & voter search.</p>
                                    <a href="https://voters.eci.gov.in" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm fw-bold">
                                        Visit voters.eci.gov.in &rarr;
                                    </a>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="fw-bold text-dark mb-1">State Election Commission Bihar</div>
                                    <p class="small text-muted mb-2">For Panchayat and Municipal local body election rules & notifications.</p>
                                    <a href="http://sec.bihar.gov.in" target="_blank" rel="noopener" class="btn btn-outline-primary btn-sm fw-bold">
                                        Visit sec.bihar.gov.in &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 5: Privacy & Obfuscation -->
                    <div class="mb-5" id="section-privacy">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Notice 5</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">5. Contact Obfuscation & Data Protection</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            In accordance with data privacy norms and the Information Technology Act, 2000, telephone numbers in public directories are masked (e.g. <code>+91 98XXX XX123</code>) to protect elected representatives and citizens against automated marketing calls, bulk spam, and phishing. If an elected representative wishes to update or redact their contact details, they may contact our support desk.
                        </p>
                    </div>

                    <!-- Section 6: Limitation of Liability -->
                    <div class="mb-5" id="section-liability">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Notice 6</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">6. Limitation of Liability</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            Users rely on the information provided on this platform at their own risk. Under no circumstances will biharelection.com, its maintainers, developers, or content creators be held liable for any direct, indirect, special, incidental, or consequential damages resulting from the use of, or inability to use, the information on this website.
                        </p>
                    </div>

                    <!-- Section 7: Corrections & Redressal -->
                    <div id="section-contact">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Notice 7</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">7. Data Correction, Feedback & Grievances</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            We welcome corrections, updates, and feedback from citizens, candidates, and election researchers. If you spot any discrepancy in election results, misspelled candidate names, or outdated representative profiles, please notify us:
                        </p>
                        <div class="p-3 bg-light rounded-3 border small">
                            <div class="fw-bold text-dark">Data Verification Desk — Bihar Election</div>
                            <div class="text-muted">Email: <a href="mailto:contact@biharelection.com" class="text-primary text-decoration-none">contact@biharelection.com</a></div>
                            <div class="text-muted">Website: <a href="https://biharelection.com" class="text-primary text-decoration-none">https://biharelection.com</a></div>
                            <div class="text-muted">Location: Patna / Saran, Bihar, India</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
