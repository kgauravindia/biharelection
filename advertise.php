<?php
require_once __DIR__ . '/config.php';

$pageTitle = 'Advertise & Listing Packages | Candidate Profiles, District Listings & Banners on Bihar Election';
$pageDescription = 'Explore advertising and political listing packages on Bihar Election. Political candidate profiles (₹2,500 - ₹25,000), District Hub sponsors (₹1,999/yr), and banner advertising reaching 2-4 lakh monthly Bihar voters.';
$pageKeywords = 'Bihar Election Advertising, Political candidate profile Bihar, District sponsor listing Bihar, Bihar political PR packages';
$pageCanonical = SITE_URL . '/advertise.php';
$activeNav = 'advertise';

require_once __DIR__ . '/header.php';
?>

    <!-- Monetization Hero -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-center py-2">
            <span class="hero-badge mb-3">💼 Official Promotion & Partner Solutions</span>
            <h1 class="hero-title display-5 fw-extrabold mb-3">
                Candidate Promotion & <br>
                <span>Commercial Advertising Packages</span>
            </h1>
            <p class="hero-subtitle lead text-white-50 mx-auto" style="max-width: 800px;">
                Establish high visibility among active voters, political analysts, business owners, and grassroots leaders across all 38 districts and 243 constituencies of Bihar.
            </p>
        </div>
    </section>

    <!-- Packages Pricing Grid -->
    <main class="container my-4 my-lg-5">

        <!-- Product Tier 1 & 2: Political Candidate Solutions -->
        <div class="mb-4 pb-2 border-bottom">
            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Political Products & Candidate Packages</h2>
            <p class="small text-muted mb-0">Tailored digital profiles, manifesto hosting, and constituent outreach for MLAs, MLCs, and 2026 aspirants</p>
        </div>

        <div class="row g-4 mb-5">
            
            <!-- Basic Political Profile -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between bg-white">
                    <div>
                        <span class="badge bg-light text-dark border mb-2">Product 1</span>
                        <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">Political Profile (Basic)</h3>
                        <div class="h4 fw-extrabold text-warning mb-3">₹2,500 – ₹10,000</div>
                        <p class="small text-muted mb-3">
                            Dedicated individual web profile for Panchayat, Municipal, and emerging Assembly aspirants.
                        </p>
                        <ul class="list-unstyled small text-muted lh-lg mb-4">
                            <li>✓ Permanent dedicated profile URL</li>
                            <li>✓ Photo, biography & contact information</li>
                            <li>✓ Search engine indexing on Google</li>
                            <li>✓ Integrated social channel links</li>
                        </ul>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background: var(--secondary-navy); border: none;">
                        Book Package &rarr;
                    </a>
                </div>
            </div>

            <!-- Premium Political Candidate Profile (VIP) -->
            <div class="col-12 col-lg-4">
                <div class="card border border-2 border-warning shadow-lg rounded-4 p-4 h-100 d-flex flex-column justify-content-between bg-white position-relative">
                    <span class="badge bg-warning text-dark fw-bold position-absolute top-0 end-0 m-3 px-3 py-1 text-uppercase">Recommended</span>
                    <div>
                        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold mb-2">Product 2</span>
                        <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">VIP Candidate Profile</h3>
                        <div class="h4 fw-extrabold text-warning mb-3">₹10,000 – ₹25,000</div>
                        <p class="small text-muted mb-3">
                            Full-scale digital PR and constituent engagement package for Vidhan Sabha candidates.
                        </p>
                        <ul class="list-unstyled small text-muted lh-lg mb-4">
                            <li>✓ <strong>Verified Badge</strong> profile page</li>
                            <li>✓ Detailed manifesto, photo & video gallery</li>
                            <li>✓ Promotion to opt-in digital subscribers</li>
                            <li>✓ Top featured slot on your Assembly Constituency page</li>
                        </ul>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-warning w-100 fw-bold py-2 text-dark shadow-sm">
                        Activate VIP Package &rarr;
                    </a>
                </div>
            </div>

            <!-- District & Assembly Business Sponsor -->
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 d-flex flex-column justify-content-between bg-white">
                    <div>
                        <span class="badge bg-light text-dark border mb-2">Product 3</span>
                        <h3 class="h5 fw-bold mb-1" style="color: var(--primary-navy);">District / Business Listing</h3>
                        <div class="h4 fw-extrabold text-navy mb-3">₹1,999 – ₹4,999 / yr</div>
                        <p class="small text-muted mb-3">
                            For coaching academies, advocates, hospitals, real estate, printing presses, and campaign vendors.
                        </p>
                        <ul class="list-unstyled small text-muted lh-lg mb-4">
                            <li>✓ 1-Year Listing in your District Election Hub</li>
                            <li>✓ Sponsored banner on target Assembly pages</li>
                            <li>✓ Direct call and lead inquiry button</li>
                            <li>✓ Prominent local Google search presence</li>
                        </ul>
                    </div>
                    <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-outline-secondary w-100 fw-bold py-2">
                        Add Business Listing &rarr;
                    </a>
                </div>
            </div>

        </div>

        <!-- Banner & Media Rate Card -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 mb-5 bg-white">
            <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                📊 Media &amp; Banner Advertising Rate Card
            </h2>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Advertising Product</th>
                            <th class="py-3">Placement</th>
                            <th class="py-3">Suggested Price</th>
                            <th class="py-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">District Directory Listing</td>
                            <td>38 District Hubs</td>
                            <td class="fw-bold text-warning">₹1,999 / year</td>
                            <td><a href="<?php echo SITE_URL; ?>/contact" class="text-warning fw-bold text-decoration-none">Book Now &rarr;</a></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Constituency Page Sponsor</td>
                            <td>243 Dedicated Assembly Pages</td>
                            <td class="fw-bold text-warning">₹4,999 / year</td>
                            <td><a href="<?php echo SITE_URL; ?>/contact" class="text-warning fw-bold text-decoration-none">Book Now &rarr;</a></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Homepage Banner</td>
                            <td>Bihar Election Main Portal</td>
                            <td class="fw-bold text-warning">₹10,000 / month</td>
                            <td><a href="<?php echo SITE_URL; ?>/contact" class="text-warning fw-bold text-decoration-none">Book Now &rarr;</a></td>
                        </tr>
                        <tr>
                            <td class="fw-bold">Premium Header Banner</td>
                            <td>All 1,500+ Platform Pages</td>
                            <td class="fw-bold text-warning">₹15,000 / month</td>
                            <td><a href="<?php echo SITE_URL; ?>/contact" class="text-warning fw-bold text-decoration-none">Book Now &rarr;</a></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Audience & Reach Benefits Section -->
        <section class="card border-0 rounded-4 p-4 p-lg-5 text-white shadow-lg" style="background: linear-gradient(135deg, #0b192c, #1e3e62);">
            <div class="text-center mb-4">
                <h2 class="h3 fw-bold mb-2">🎯 Why Partner with Bihar Election?</h2>
                <p class="text-white-50 small mb-0">Connecting your political vision or local services directly with high-intent Bihar voters.</p>
            </div>

            <div class="row g-3 g-lg-4 mb-4">
                <div class="col-12 col-md-4">
                    <div class="bg-white bg-opacity-10 p-3 p-lg-4 rounded-3 h-100">
                        <div class="fs-2 mb-2">👥</div>
                        <h3 class="h6 fw-bold mb-1">Hyper-Targeted Readership</h3>
                        <p class="small text-white-50 mb-0">Visitors search specifically for their local Vidhan Sabha, block, panchayat, or MLA records.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bg-white bg-opacity-10 p-3 p-lg-4 rounded-3 h-100">
                        <div class="fs-2 mb-2">📲</div>
                        <h3 class="h6 fw-bold mb-1">Direct Civic Network</h3>
                        <p class="small text-white-50 mb-0">Deliver updates and candidate manifestos directly to engaged community members.</p>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="bg-white bg-opacity-10 p-3 p-lg-4 rounded-3 h-100">
                        <div class="fs-2 mb-2">🔍</div>
                        <h3 class="h6 fw-bold mb-1">Search Engine Authority</h3>
                        <p class="small text-white-50 mb-0">Permanent high-ranking Google pages for 243 constituencies and 38 district hubs.</p>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <a href="<?php echo SITE_URL; ?>/contact" class="btn btn-warning btn-lg fw-bold px-4 text-dark shadow-sm">
                    <i class="bi bi-envelope-fill me-1"></i> Contact Advertising Desk &rarr;
                </a>
            </div>
        </section>

    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
