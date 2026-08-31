<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Privacy Policy — Bihar Election Information Platform';
$pageDescription = 'Official Privacy Policy of biharelection.com. Learn how we protect citizen data, obfuscate contact details, and adhere to India DPDP Act 2023 standards.';
$pageKeywords = 'Bihar Election Privacy Policy, biharelection.com privacy, DPDP Act 2023 Bihar, Election Data Protection';
$pageCanonical = SITE_URL . '/privacy-policy';
$activeNav = '';

require_once __DIR__ . '/header.php';
?>

    <!-- Hero Banner -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-start">
            <div class="d-flex flex-wrap gap-2 mb-3">
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2">
                    🔒 Data Protection & Privacy
                </span>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2">
                    Compliance: DPDP Act 2023 & IT Act 2000
                </span>
            </div>

            <h1 class="display-6 fw-extrabold text-white mb-2">
                Privacy Policy
            </h1>
            <p class="lead text-white-50 mb-0" style="font-size: 1.05rem; max-width: 850px;">
                How <strong>Bihar Election (biharelection.com)</strong> collects, protects, obfuscates, and manages citizen and representative data across our platform.
            </p>
        </div>
    </section>

    <!-- Main Content Container -->
    <main class="container my-5">
        <div class="row g-4">
            
            <!-- Quick Navigation Index (4 Cols) -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 bg-white position-sticky" style="top: 90px;">
                    <h2 class="h6 fw-bold text-dark mb-3 pb-2 border-bottom">
                        <i class="bi bi-shield-lock text-primary me-2"></i> Privacy Clauses
                    </h2>
                    <ul class="list-unstyled small mb-0 lh-lg">
                        <li><a href="#section-overview" class="text-decoration-none text-navy fw-semibold">1. Overview & Scope</a></li>
                        <li><a href="#section-collect" class="text-decoration-none text-navy fw-semibold">2. Information We Collect</a></li>
                        <li><a href="#section-masking" class="text-decoration-none text-danger fw-bold">3. Contact Masking Policy</a></li>
                        <li><a href="#section-usage" class="text-decoration-none text-navy fw-semibold">4. How We Use Information</a></li>
                        <li><a href="#section-cookies" class="text-decoration-none text-navy fw-semibold">5. Cookies & Google AdSense</a></li>
                        <li><a href="#section-sharing" class="text-decoration-none text-navy fw-semibold">6. Data Sharing & Third Parties</a></li>
                        <li><a href="#section-security" class="text-decoration-none text-navy fw-semibold">7. Data Security & Storage</a></li>
                        <li><a href="#section-rights" class="text-decoration-none text-navy fw-semibold">8. User Data Rights</a></li>
                        <li><a href="#section-officer" class="text-decoration-none text-navy fw-semibold">9. Grievance Officer</a></li>
                    </ul>

                    <hr class="my-3">
                    <div class="p-3 bg-light rounded-3 text-muted small">
                        <div class="fw-bold text-dark mb-1">Privacy or Data Inquiries?</div>
                        <div>Reach out to our Data Protection Desk at <a href="mailto:contact@biharelection.com" class="text-primary text-decoration-none fw-semibold">contact@biharelection.com</a></div>
                    </div>
                </div>
            </div>

            <!-- Detailed Privacy Clauses (8 Cols) -->
            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">

                    <!-- Section 1: Overview -->
                    <div class="mb-5" id="section-overview">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 1</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">1. Overview & Scope</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            This Privacy Policy explains how <strong>Bihar Election (biharelection.com)</strong> handles personal and non-personal data when you access our civic intelligence portal. We are committed to safeguarding your privacy in strict accordance with the <strong>Digital Personal Data Protection (DPDP) Act, 2023</strong> and the <strong>Information Technology (Reasonable Security Practices and Procedures and Sensitive Personal Data or Information) Rules, 2011</strong>.
                        </p>
                    </div>

                    <!-- Section 2: Information We Collect -->
                    <div class="mb-5" id="section-collect">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 2</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">2. Information We Collect</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            We collect information in the following categories:
                        </p>
                        <ul class="text-muted small lh-lg mb-0">
                            <li><strong>Public Domain Electoral Records:</strong> Official candidate affidavits, past polling statistics (2015–2025), Census 2011 abstracts, and public gazette representative rosters (Mukhiya, Sarpanch, Zila Parishad, Samiti Pramukh, MLAs, MLCs, and MPs).</li>
                            <li><strong>User Provided Information:</strong> When you register for a Citizen Account, submit candidate details, or opt for WhatsApp updates, we collect your name, 10-digit mobile number, email address (optional), and preferred constituency/district.</li>
                            <li><strong>Automated Technical Data:</strong> Browser user-agent, operating system, approximate geographic location (city level), referral URLs, and access timestamps to ensure system security and optimize mobile layout delivery.</li>
                        </ul>
                    </div>

                    <!-- Section 3: Contact Masking Policy -->
                    <div class="mb-5" id="section-masking">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1">Clause 3</span>
                            <h2 class="h5 fw-bold mb-0 text-danger">3. Public Directory Phone Masking & Anti-Scraping Policy</h2>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 border-start border-4 border-danger rounded-end mb-3">
                            <p class="small text-danger mb-0 fw-semibold lh-base">
                                <strong>ANTI-HARASSMENT DIRECTIVE:</strong> To protect public representatives and local officials from bulk automated telemarketing, SMS spam bots, and malicious scraping, all personal telephone numbers published across public tables (Mukhiya, Sarpanch, Zila Parishad, Block Samiti) are automatically masked (e.g. <code>+91 98XXX XX123</code>).
                            </p>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            Automated harvesting or de-anonymization of masked telephone numbers is strictly prohibited and constitutes a direct breach of our Terms and Conditions.
                        </p>
                    </div>

                    <!-- Section 4: How We Use Information -->
                    <div class="mb-5" id="section-usage">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 4</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">4. How We Use Your Information</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            We use your data solely for lawful, legitimate civic and technical purposes:
                        </p>
                        <ul class="text-muted small lh-lg mb-0">
                            <li>To authenticate registered users and process password recovery requests via TRAI DLT-approved transactional SMS OTPs.</li>
                            <li>To verify candidate profile submissions, asset disclosures, and developmental achievements before public publishing.</li>
                            <li>To deliver requested WhatsApp community broadcasts and district-specific election news alerts.</li>
                            <li>To prevent fraudulent activity, mitigate DDoS attacks, and protect platform integrity.</li>
                        </ul>
                    </div>

                    <!-- Section 5: Cookies & Google AdSense -->
                    <div class="mb-5" id="section-cookies">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 5</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">5. Cookies, Analytics & Google AdSense</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            Our Website utilizes essential session cookies to maintain your login state and remember your chosen district/tab filters.
                        </p>
                        <p class="text-muted small lh-lg mb-0">
                            We use <strong>Google AdSense</strong> to display non-intrusive advertisements. Google, as a third-party vendor, uses cookies (including the DoubleClick cookie) to serve ads based on prior visits. Users may customize or opt out of personalized advertising by visiting <a href="https://www.google.com/settings/ads" target="_blank" rel="noopener" class="text-primary text-decoration-none">Google Ads Settings</a> or <a href="https://www.aboutads.info" target="_blank" rel="noopener" class="text-primary text-decoration-none">aboutads.info</a>.
                        </p>
                    </div>

                    <!-- Section 6: Data Sharing & Third Parties -->
                    <div class="mb-5" id="section-sharing">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 6</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">6. Data Sharing & Third-Party Processors</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            <strong>We do not sell, rent, or trade your personal information</strong> to any third-party political consulting agencies, data brokers, or commercial marketing enterprises.
                        </p>
                        <p class="text-muted small lh-lg mb-0">
                            We share data only with essential infrastructural service providers (such as authorized TRAI DLT SMS gateway providers for OTP transmission and encrypted database hosting providers) bound by strict confidentiality agreements.
                        </p>
                    </div>

                    <!-- Section 7: Security & Data Retention -->
                    <div class="mb-5" id="section-security">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 7</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">7. Data Security & Storage</h2>
                        </div>
                        <p class="text-muted small lh-lg mb-0">
                            All user passwords are encrypted using cryptographic hashing algorithms (Bcrypt/Argon2 with individual salts). We implement SSL/TLS 256-bit encryption for all data in transit, strict database connection parameters with prepared statements to prevent SQL injections, and firewalled hosting infrastructure.
                        </p>
                    </div>

                    <!-- Section 8: User Data Rights -->
                    <div class="mb-5" id="section-rights">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 8</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">8. User Data Rights (DPDP Act 2023)</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            In accordance with applicable data privacy laws, you possess the right to:
                        </p>
                        <ul class="text-muted small lh-lg mb-0">
                            <li><strong>Access & Review:</strong> Review the personal information associated with your account on your dashboard.</li>
                            <li><strong>Rectification:</strong> Request correction of inaccurate or outdated profile details.</li>
                            <li><strong>Erasure / Account Deletion:</strong> Request permanent removal of your account and registered mobile number from our active databases.</li>
                        </ul>
                    </div>

                    <!-- Section 9: Grievance Redressal -->
                    <div id="section-officer">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Clause 9</span>
                            <h2 class="h5 fw-bold mb-0 text-navy">9. Grievance Officer & Contact Information</h2>
                        </div>
                        <p class="text-muted small lh-lg">
                            For any privacy inquiries, data deletion requests, or grievances regarding our compliance with the Information Technology Act and DPDP Act 2023, please contact our designated Grievance Officer:
                        </p>
                        <div class="p-3 bg-light rounded-3 border small">
                            <div class="fw-bold text-dark">Data Protection & Grievance Officer</div>
                            <div class="text-muted">Bihar Election Information Platform</div>
                            <div class="text-muted">Email: <a href="mailto:contact@biharelection.com" class="text-primary text-decoration-none">contact@biharelection.com</a></div>
                            <div class="text-muted">Website: <a href="https://biharelection.com" class="text-primary text-decoration-none">https://biharelection.com</a></div>
                            <div class="text-muted">Jurisdiction: Patna / Saran, Bihar, India</div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
