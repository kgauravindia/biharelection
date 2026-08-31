<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Terms & Conditions — Bihar Election Information Platform';
$pageDescription = 'Read the official Terms and Conditions, Non-Government Disclaimer, and usage policies governing biharelection.com election data portal.';
$pageKeywords = 'Bihar Election Terms and Conditions, Non Government Disclaimer, biharelection.com terms, Bihar Political Data Usage Policy';
$pageCanonical = SITE_URL . '/terms-and-conditions';
$activeNav = '';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    📜 Legal Agreement & Policy
                </span>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    Last Updated: August 2026
                </span>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Terms & Conditions
            </h1>
            <p class="lead text-white-50 mb-0" style="font-size: 1.05rem; max-width: 850px;">
                Please read these Terms and Conditions carefully before browsing or utilizing the data, rosters, directories, and services offered by <strong>Bihar Election (biharelection.com)</strong>.
            </p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container my-5">
        <div class="row g-4">
            
            <!-- Quick Navigation Index (4 Cols on desktop) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 bg-white position-sticky" style="top: 90px;">
                    <h2 class="h6 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-list-nested text-primary me-2"></i> Document Navigation
                    </h2>
                    <ul class="list-unstyled small mb-0 lh-lg">
                        <li><a href="#section-acceptance" class="text-decoration-none text-navy fw-semibold">1. Acceptance of Terms</a></li>
                        <li><a href="#section-disclaimer" class="text-decoration-none text-danger fw-bold">2. Non-Government Disclaimer</a></li>
                        <li><a href="#section-sources" class="text-decoration-none text-navy fw-semibold">3. Data Accuracy & Sources</a></li>
                        <li><a href="#section-use" class="text-decoration-none text-navy fw-semibold">4. Permitted Use & IP</a></li>
                        <li><a href="#section-accounts" class="text-decoration-none text-navy fw-semibold">5. User Accounts & OTP Auth</a></li>
                        <li><a href="#section-candidates" class="text-decoration-none text-navy fw-semibold">6. Candidate Profiles & Listings</a></li>
                        <li><a href="#section-advertising" class="text-decoration-none text-navy fw-semibold">7. Advertising & Sponsorships</a></li>
                        <li><a href="#section-privacy" class="text-decoration-none text-navy fw-semibold">8. Privacy & Number Masking</a></li>
                        <li><a href="#section-liability" class="text-decoration-none text-navy fw-semibold">9. Limitation of Liability</a></li>
                        <li><a href="#section-jurisdiction" class="text-decoration-none text-navy fw-semibold">10. Governing Law & Jurisdiction</a></li>
                        <li><a href="#section-contact" class="text-decoration-none text-navy fw-semibold">11. Grievance & Contact Info</a></li>
                    </ul>

                    <hr class="my-3">
                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <div class="fw-bold text-dark mb-1">Need help or clarification?</div>
                        <div>Contact our legal desk at <a href="mailto:contact@biharelection.com" class="text-primary text-decoration-none fw-semibold">contact@biharelection.com</a></div>
                    </div>
                </div>
            </div>

            <!-- Detailed Policy Terms (8 Cols) -->
            <div class="col-12 col-lg-8">
                
                <!-- Card Container -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">

                    <!-- Section 1: Acceptance -->
                    <div class="mb-5" id="section-acceptance">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 1</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">1. Acceptance of Terms</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            By accessing, browsing, scraping, or utilizing the web application <strong>biharelection.com</strong> (hereinafter referred to as the "Platform", "Website", "We", "Us", or "Our"), you acknowledge that you have read, understood, and unequivocally agree to be bound by these Terms and Conditions ("Terms") and our Privacy Policy. If you do not agree with any part of these Terms, you must immediately cease accessing or utilizing this Website.
                        </p>
                    </div>

                    <!-- Section 2: Non-Government Disclaimer -->
                    <div class="mb-5" id="section-disclaimer">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1">Clause 2</span>
                            <h2 class="h5 fw-bold mb-0 text-danger">2. Non-Government Entity Disclaimer (Critical Notice)</h2>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 border-start border-4 border-danger rounded-end mb-3">
                            <p class="small text-danger mb-0 fw-semibold lh-base">
                                <strong>IMPORTANT NOTICE:</strong> Bihar Election (biharelection.com) is an autonomous, private, and independent civic intelligence and election data archiving platform. We are NOT associated, affiliated, endorsed by, or connected in any capacity with the Election Commission of India (ECI), the Bihar State Election Commission (SEC), the Government of Bihar, or the Government of India.
                            </p>
                        </div>
                        <p class="text-muted small lh-lg">
                            All official voting procedures, formal candidate nominations, voter registration (Form 6/7/8), EVM/VVPAT management, and statutory election notifications remain under the exclusive constitutional jurisdiction of the Election Commission of India (<a href="https://eci.gov.in" target="_blank" rel="noopener" class="text-primary text-decoration-none">eci.gov.in</a>) and the Bihar State Election Commission (<a href="http://sec.bihar.gov.in" target="_blank" rel="noopener" class="text-primary text-decoration-none">sec.bihar.gov.in</a>).
                        </p>
                    </div>

                    <!-- Section 3: Data Accuracy -->
                    <div class="mb-5" id="section-sources">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 3</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">3. Data Accuracy, Compilation & Sources</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            Electoral data, past election results (2025, 2020, 2015), Census 2011 Primary Census Abstracts, voter turnout statistics, and representative rosters (Mukhiya, Sarpanch, Zila Parishad, Samiti Pramukh, MLAs, MLCs, and MPs) displayed on this platform are aggregated from publicly available government records, gazette notifications, statistical releases, and certified candidate affidavits submitted under the Representation of the People Act, 1951.
                        </p>
                        <p class="text-muted small lh-lg mb-0">
                            While we implement rigorous algorithmic and manual verification methodologies, we make no express or implied representations regarding the absolute completeness, error-free nature, or real-time synchronization of all records. Users are advised to cross-verify vital legal decisions with primary gazettes.
                        </p>
                    </div>

                    <!-- Section 4: Permitted Use & Intellectual Property -->
                    <div class="mb-5" id="section-use">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 4</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">4. Permitted Use & Intellectual Property Rights</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            All custom algorithms, analytical charts, curated database formats, proprietary code, visual interfaces, stylesheets, and graphic assets created by Bihar Election are the intellectual property of biharelection.com.
                        </p>
                        <ul class="text-muted small lh-lg mb-0">
                            <li><strong>Permitted Use:</strong> You are granted a limited, revocable, non-exclusive license to access, view, and cite data for academic research, journalistic reporting, civic education, and personal reference, provided proper attribution to <code>biharelection.com</code> is included.</li>
                            <li><strong>Prohibited Actions:</strong> Bulk scraping without written consent, automated harvesting of masked contact details, reverse engineering, unauthorized frame-embedding, and commercial redistribution of compiled dataset dumps are strictly prohibited.</li>
                        </ul>
                    </div>

                    <!-- Section 5: User Accounts & OTP Auth -->
                    <div class="mb-5" id="section-accounts">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 5</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">5. User Registration & Mobile OTP Authentication</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            Users opting to create a Citizen Account, submit candidate details, or post community notices agree to:
                        </p>
                        <ul class="text-muted small lh-lg mb-0">
                            <li>Provide accurate, current, and verifiable Indian 10-digit mobile telephone numbers during registration.</li>
                            <li>Authenticate their identity via the TRAI DLT-approved One Time Password (OTP) dispatched to their registered mobile.</li>
                            <li>Maintain strict confidentiality over account credentials and notify us immediately of any unauthorized account access.</li>
                            <li>Refrain from impersonating any individual, public representative, or election officer.</li>
                        </ul>
                    </div>

                    <!-- Section 6: Candidate Profiles & Listings -->
                    <div class="mb-5" id="section-candidates">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 6</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">6. Candidate Profiles & Political Aspirant Submissions</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            Prospective election candidates, party workers, and incumbents who submit political biographies, declared educational qualifications, or social links acknowledge that all submitted data must be true, verifiable, and free of defamatory, unconstitutional, or inciteful statements.
                        </p>
                        <p class="text-muted small lh-lg mb-0">
                            The Platform reserves the right to review, edit, redact, or reject any profile submission that does not conform with our verification guidelines or the Model Code of Conduct (MCC).
                        </p>
                    </div>

                    <!-- Section 7: Advertising & Sponsorships -->
                    <div class="mb-5" id="section-advertising">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 7</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">7. Commercial Advertisements & Page Sponsorships</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            Local business listings, educational academies, medical clinics, and legal chambers booking commercial sponsorship banner slots (e.g. ₹4,999/year for Assembly Constituency pages or ₹1,999/year for District Hubs) are subject to our advertisement policy:
                        </p>
                        <ul class="text-muted small lh-lg mb-0">
                            <li>All advertisements must adhere to the Advertising Standards Council of India (ASCI) guidelines.</li>
                            <li>Advertisements promoting hate speech, illegal substances, gambling, or discriminatory matter are strictly disallowed.</li>
                            <li>Display of commercial sponsorship does not constitute an endorsement by Bihar Election of the advertised product or political entity.</li>
                        </ul>
                    </div>

                    <!-- Section 8: Privacy & Number Masking -->
                    <div class="mb-5" id="section-privacy">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 8</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">8. Privacy & Data Masking Policy</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            In conformity with data protection principles and the Information Technology Act, 2000, personal mobile telephone numbers published in public representative directory tables are obfuscated and masked (e.g. <code>+91 98XXX XX123</code>) to protect individual privacy against automated telemarketing and phishing spam. Full contact details are managed securely within administrative controls.
                        </p>
                    </div>

                    <!-- Section 9: Limitation of Liability -->
                    <div class="mb-5" id="section-liability">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 9</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">9. Limitation of Liability & Disclaimer of Warranties</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            The Website and all contained materials are provided on an "AS IS" and "AS AVAILABLE" basis without warranties of any kind. Under no circumstances shall Bihar Election, its operators, creators, or affiliates be liable for any direct, indirect, incidental, consequential, special, or punitive damages arising from the use of, or inability to use, this Website or any reliance placed upon the data contained herein.
                        </p>
                    </div>

                    <!-- Section 10: Governing Law & Jurisdiction -->
                    <div class="mb-5" id="section-jurisdiction">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 10</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">10. Governing Law & Dispute Resolution</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            These Terms and Conditions shall be governed by, construed, and enforced in accordance with the laws of the Republic of India. Any disputes, actions, claims, or controversies arising out of or relating to this Platform shall be subject to the exclusive jurisdiction of the competent civil courts situated in <strong>Patna, Bihar, India</strong>.
                        </p>
                    </div>

                    <!-- Section 11: Grievance & Contact -->
                    <div id="section-contact">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 11</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">11. Grievance Redressal & Contact Information</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            If you have questions, feedback, copyright concerns, or data correction requests regarding these Terms and Conditions, please reach out to our Grievance Officer:
                        </p>
                        <div class="p-3 bg-light rounded-3 border small">
                            <div class="fw-bold text-dark">Grievance & Legal Officer</div>
                            <div class="text-muted">Bihar Election Data & Intelligence Platform</div>
                            <div class="text-muted">Website: <a href="https://biharelection.com" class="text-primary text-decoration-none">https://biharelection.com</a></div>
                            <div class="text-muted">Email: <a href="mailto:contact@biharelection.com" class="text-primary text-decoration-none">contact@biharelection.com</a></div>
                            <div class="text-muted">Location: Patna / Saran, Bihar, India</div>
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
