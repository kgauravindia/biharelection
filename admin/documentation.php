<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$pageTitle = 'Admin Documentation & Developer Knowledge Base';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Documentation & Knowledge Base — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="p-3 p-md-4">
            <!-- Header Banner -->
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4 pb-2 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">Internal Reference</span>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">Version 2.6 (2026 Production)</span>
                    </div>
                    <h1 class="h3 fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">📘 Bihar Election Admin Documentation & Knowledge Base</h1>
                    <p class="text-muted small mb-0">Comprehensive architectural manual, routing guidelines, security rules, and data operations.</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="sitemap.php" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="fas fa-sitemap me-1"></i> Sitemap Tool
                    </a>
                    <a href="settings.php" class="btn btn-outline-primary btn-sm fw-bold">
                        <i class="fas fa-gear me-1"></i> Site Settings
                    </a>
                </div>
            </div>

            <!-- Quick Navigation Pills -->
            <div class="card border-0 shadow-sm p-3 mb-4 rounded-3 bg-light">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="small fw-bold text-muted text-uppercase me-2"><i class="fas fa-bookmark me-1"></i> Quick Jump:</span>
                    <a href="#section-arch" class="btn btn-sm btn-white border shadow-xs fw-semibold">1. Architecture</a>
                    <a href="#section-seo" class="btn btn-sm btn-white border shadow-xs fw-semibold">2. SEO & Clean URLs</a>
                    <a href="#section-data" class="btn btn-sm btn-white border shadow-xs fw-semibold">3. Data Schema</a>
                    <a href="#section-sms" class="btn btn-sm btn-white border shadow-xs fw-semibold">4. SMS Gateway & Auth</a>
                    <a href="#section-legal" class="btn btn-sm btn-white border shadow-xs fw-semibold">5. Legal & Terms</a>
                    <a href="#section-admin" class="btn btn-sm btn-white border shadow-xs fw-semibold">6. Admin Modules</a>
                    <a href="#section-pagination" class="btn btn-sm btn-white border shadow-xs fw-semibold">7. Table Pagination</a>
                    <a href="#section-maintenance" class="btn btn-sm btn-white border shadow-xs fw-semibold">8. CLI & Maintenance</a>
                </div>
            </div>

            <div class="row g-4">
                <!-- Main Documentation Content (9 Cols) -->
                <div class="col-12 col-xl-9">

                    <!-- SECTION 1: ARCHITECTURE -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-arch">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-primary">🏗️</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">1. Architecture & Technology Stack</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted">
                                Bihar Election is built as a high-performance, mobile-first election intelligence and data platform. It uses a hybrid architecture combining fast cached JSON datasets with MySQL storage for interactive user sessions, candidates, and advertising leads.
                            </p>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-2"><i class="fab fa-php text-primary me-1"></i> Backend Engine</h3>
                                        <ul class="list-unstyled small text-muted mb-0 lh-lg">
                                            <li>• PHP 8.1+ (Object-Oriented + Global Helpers)</li>
                                            <li>• Apache 2.4+ with <code>mod_rewrite</code> & <code>mod_headers</code></li>
                                            <li>• MySQL / MariaDB (Prepared Statements)</li>
                                            <li>• JSON Flat-file caching for 50k+ records</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-2"><i class="fab fa-bootstrap text-purple me-1"></i> Frontend & UI</h3>
                                        <ul class="list-unstyled small text-muted mb-0 lh-lg">
                                            <li>• Bootstrap 5.3.3 responsive grid</li>
                                            <li>• Bootstrap Icons + FontAwesome 6</li>
                                            <li>• Custom Vanilla CSS Design System</li>
                                            <li>• Pure JS Client-side Search & Paginator</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-2"><i class="fas fa-shield-halved text-success me-1"></i> Security & Auth</h3>
                                        <ul class="list-unstyled small text-muted mb-0 lh-lg">
                                            <li>• OTP-based Mobile Authentication (DLT)</li>
                                            <li>• SHA-256 / Bcrypt salted passwords</li>
                                            <li>• XSS Protection & Phone Masking</li>
                                            <li>• Role-Based Access Control (RBAC)</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: SEO & URL ROUTING -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-seo">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-warning">🌐</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">2. SEO & Clean URL Routing Structure</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted">
                                All URLs in the system are designed to be human-readable, canonicalized, and optimized for search engine crawlability without query string dependencies.
                            </p>
                            <div class="table-responsive mb-3">
                                <table class="table table-bordered align-middle small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Feature / Level</th>
                                            <th>Clean SEO URL Format</th>
                                            <th>Underlying Script & Query</th>
                                            <th>Helper Function</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="badge bg-primary">MLA / AC</span></td>
                                            <td><code>/mla/118-chapra</code><br><code>/constituency/118-chapra</code></td>
                                            <td><code>vidhan-sabha.php?ac=118&slug=chapra</code></td>
                                            <td><code>getMlaUrl($ac)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info text-dark">District Hub</span></td>
                                            <td><code>/district/saran</code></td>
                                            <td><code>district.php?slug=saran</code></td>
                                            <td><code>getDistrictUrl($slug)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-success">Mukhiya Directory</span></td>
                                            <td><code>/mukhiya</code><br><code>/mukhiya/saran</code><br><code>/mukhiya/saran/sandha</code></td>
                                            <td><code>panchayat.php?tab=mukhiya&district=saran&panchayat=sandha</code></td>
                                            <td><code>getMukhiyaUrl($dist, $panch)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning text-dark">Sarpanch Directory</span></td>
                                            <td><code>/sarpanch</code><br><code>/sarpanch/saran</code></td>
                                            <td><code>panchayat.php?tab=sarpanch&district=saran</code></td>
                                            <td><code>getSarpanchUrl($dist, $panch)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-dark">Zila Parishad</span></td>
                                            <td><code>/zila-parishad</code><br><code>/zila-parishad/saran</code></td>
                                            <td><code>panchayat.php?tab=zila&district=saran</code></td>
                                            <td><code>getZilaParishadUrl($dist)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-secondary">Block Samiti (2016)</span></td>
                                            <td><code>/panchayat-samiti</code><br><code>/panchayat-samiti/saran/chapra</code></td>
                                            <td><code>panchayat.php?tab=samiti&district=saran&block=chapra</code></td>
                                            <td><code>getPanchayatSamitiUrl($d, $b)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-danger">MP Directory</span></td>
                                            <td><code>/mp</code><br><code>/mp/saran</code><br><code>/rajya-sabha</code></td>
                                            <td><code>representatives.php?tab=loksabha&slug=saran</code></td>
                                            <td><code>getMpUrl($slug)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-primary">MLC Directory</span></td>
                                            <td><code>/mlc</code><br><code>/mlc/saran</code></td>
                                            <td><code>representatives.php?tab=mlc&slug=saran</code></td>
                                            <td><code>getMlcUrl($slug)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-info text-dark">Census 2011 Hub</span></td>
                                            <td><code>/census</code><br><code>/census/saran</code></td>
                                            <td><code>census.php?district=saran</code></td>
                                            <td><code>getCensusUrl($slug)</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-warning text-dark">Candidate Profile</span></td>
                                            <td><code>/candidate/dr-cn-gupta</code></td>
                                            <td><code>candidate.php?slug=dr-cn-gupta</code></td>
                                            <td><code>SITE_URL . '/candidate/' . $slug</code></td>
                                        </tr>
                                        <tr>
                                            <td><span class="badge bg-secondary">Legal / Terms</span></td>
                                            <td><code>/terms-and-conditions</code><br><code>/terms</code></td>
                                            <td><code>terms-and-conditions.php</code></td>
                                            <td><code>SITE_URL . '/terms-and-conditions'</code></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: DATA FILES & SCHEMA -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-data">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-success">📂</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">3. Data Files & Directory Schema</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted">
                                Primary election rosters and demographic statistics are stored under <code>assets/data/</code> as structured JSON files to ensure sub-millisecond response times:
                            </p>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h3 class="h6 fw-bold text-primary mb-2">Legislative & Parliamentary Datasets</h3>
                                        <ul class="list-unstyled small text-muted mb-0">
                                            <li class="mb-2"><code>assets/data/constituencies.json</code> — All 243 Vidhan Sabha constituencies, voter counts, past election results, and demographics.</li>
                                            <li class="mb-2"><code>assets/data/districts.json</code> — 38 administrative districts, HQ, divisions, and AC mappings.</li>
                                            <li class="mb-2"><code>assets/data/mps_loksabha.json</code> — 40 Lok Sabha Members of Parliament.</li>
                                            <li class="mb-2"><code>assets/data/mps_rajyasabha.json</code> — 15 Rajya Sabha Parliamentarians.</li>
                                            <li class="mb-2"><code>assets/data/mlcs.json</code> — 75 Vidhan Parishad MLCs.</li>
                                            <li class="mb-0"><code>assets/data/mlas_2015.json</code> — 243 Historical (2015–2020) MLAs.</li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h3 class="h6 fw-bold text-success mb-2">Panchayati Raj & Census Datasets</h3>
                                        <ul class="list-unstyled small text-muted mb-0">
                                            <li class="mb-2"><code>assets/data/mukhiya_directory.json</code> — 7,346 elected Gram Panchayat Mukhiyas.</li>
                                            <li class="mb-2"><code>assets/data/sarpanch_directory.json</code> — 6,617 elected Gram Katchahry Sarpanchs.</li>
                                            <li class="mb-2"><code>assets/data/zila_parishad_members.json</code> — 1,099+ Territorial Ward Members.</li>
                                            <li class="mb-2"><code>assets/data/zila_parishad_officials.json</code> — 38 Zila Parishad Chairpersons & Vice-Chairpersons.</li>
                                            <li class="mb-2"><code>assets/data/census_bihar.json</code> & <code>census_districts.json</code> — Official Census 2011 Primary Census Abstracts.</li>
                                            <li class="mb-0"><code>assets/data/mukhiyas_2016.json</code> & <code>panchayat_samiti_2016.json</code> — 2016 Historical Archive.</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: SMS GATEWAY & AUTH -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-sms">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-danger">📱</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">4. SMS Gateway & OTP Authentication</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted">
                                Voter registration and password reset rely on TRAI DLT-approved transactional SMS via HTTP REST API.
                            </p>
                            <div class="row g-3 mb-3">
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h3 class="h6 fw-bold text-dark mb-2">SMS Gateway Configuration</h3>
                                        <table class="table table-sm table-borderless small mb-0">
                                            <tr><td class="text-muted" style="width:130px;">Endpoint:</td><td><code>http://msg.morg.in/rest/services/sendSMS/sendGroupSms</code></td></tr>
                                            <tr><td class="text-muted">Auth Key:</td><td><code>b0e99bea1fa7d15e27e1c5fd8e3c868</code></td></tr>
                                            <tr><td class="text-muted">Sender ID:</td><td><code>BIHELE</code></td></tr>
                                            <tr><td class="text-muted">Template ID:</td><td><code>BIHELE_OTP</code></td></tr>
                                            <tr><td class="text-muted">Route:</td><td>Transactional / Service Implicit (Route 4)</td></tr>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h3 class="h6 fw-bold text-dark mb-2">Privacy & Contact Masking</h3>
                                        <p class="small text-muted mb-2">
                                            In compliance with data privacy directives, telephone numbers are masked by default across public tables (e.g. <code>+91 98XXX XX123</code>) using the <code>maskMobileNumber($phone)</code> function defined in <code>config.php</code>.
                                        </p>
                                        <p class="small text-muted mb-0">
                                            Full contact details are accessible exclusively within the authenticated Admin Portal for authorized administrators.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 5: LEGAL & TERMS COMPLIANCE -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-legal">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-warning">⚖️</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">5. Legal Policies & Terms of Service</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted">
                                Public compliance and legal protection are maintained via <a href="../terms-and-conditions.php" target="_blank" class="fw-bold text-primary">terms-and-conditions.php</a>, accessible via <code>https://biharelection.com/terms-and-conditions/</code>:
                            </p>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-danger mb-2">Non-Government Entity Disclaimer</h3>
                                        <p class="small text-muted mb-0">
                                            Mandatory declaration asserting complete independence from the Election Commission of India (ECI) and the State Election Commission (SEC) Bihar to avoid trademark or electoral confusion.
                                        </p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-2">Jurisdiction & Grievance Redressal</h3>
                                        <p class="small text-muted mb-0">
                                            Governed exclusively under the jurisdiction of competent civil courts located in Patna, Bihar, with designated legal contact at <code>contact@biharelection.com</code>.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 6: ADMIN MANAGEMENT MODULES -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-admin">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-primary">⚙️</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">6. Admin Portal Management Modules</h2>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-1"><i class="fas fa-user-tie text-warning me-1"></i> Candidate Management</h3>
                                        <p class="small text-muted mb-2"><code>admin/candidates.php</code></p>
                                        <p class="small text-muted mb-0">Review, verify, edit, and approve candidate profile submissions, assets disclosures, and political biographies.</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-1"><i class="fas fa-rectangle-ad text-danger me-1"></i> Advertisement Management</h3>
                                        <p class="small text-muted mb-2"><code>admin/manage-ads.php</code></p>
                                        <p class="small text-muted mb-0">Track commercial sponsorship bookings for AC pages (₹4,999/yr) and District Hubs (₹1,999/yr). Activate and schedule ad slots.</p>
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h3 class="h6 fw-bold text-dark mb-1"><i class="fas fa-lock text-info me-1"></i> Security & Recovery</h3>
                                        <p class="small text-muted mb-2"><code>admin/forgot-password.php</code></p>
                                        <p class="small text-muted mb-0">Secure admin account recovery mechanism without exposing default credentials on the login screen.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 7: PAGINATION & FILTERING -->
                    <div class="card border-0 shadow-sm rounded-4 mb-4" id="section-pagination">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-info">📑</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">7. Modular JavaScript Table Pagination (TablePaginator)</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted">
                                Large datasets (Mukhiya: 7,346 rows, Sarpanch: 6,617 rows, 2016 Mukhiyas: 8,045 rows) use client-side pagination with the <code>TablePaginator</code> class in <code>panchayat.php</code>.
                            </p>
                            <div class="bg-light p-3 rounded-3 border">
                                <h3 class="h6 fw-bold text-dark mb-2">How to attach TablePaginator to any table:</h3>
                                <pre class="bg-dark text-white p-3 rounded-2 small mb-0"><code>const paginator = new TablePaginator({
    tableId: 'myTableId',
    rowSelector: '#myTableId .my-row-class',
    pageSizeSelectId: 'myPageSize',
    pageInfoId: 'myPageInfo',
    bottomInfoId: 'myBottomInfo',
    paginationContainerId: 'myPagination',
    topPaginationContainerId: 'myTopPagination',
    countBadgeId: 'totalCountBadge',
    unitName: 'Records',
    defaultPageSize: 50
});

// When filtering:
paginator.setFilteredRows(filteredArrayOfTrElements);</code></pre>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 8: MAINTENANCE & CLI -->
                    <div class="card border-0 shadow-sm rounded-4" id="section-maintenance">
                        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                            <span class="fs-5 text-secondary">🛠️</span>
                            <h2 class="h5 fw-bold mb-0 text-dark">8. Maintenance, CLI & Git Commands</h2>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h3 class="h6 fw-bold text-dark mb-2"><i class="fas fa-terminal me-1"></i> PHP CLI Syntax Check</h3>
                                        <pre class="bg-dark text-white p-2 rounded-2 small mb-0"><code>& "D:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe" -l filename.php</code></pre>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <h3 class="h6 fw-bold text-dark mb-2"><i class="fab fa-git-alt me-1"></i> Git Push Routine</h3>
                                        <pre class="bg-dark text-white p-2 rounded-2 small mb-0"><code>git add .
git commit -m "Your descriptive commit message"
git push origin main</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Summary & Quick Links (3 Cols) -->
                <div class="col-12 col-xl-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 bg-white mb-4 position-sticky" style="top: 80px;">
                        <h3 class="h6 fw-bold text-dark mb-3 pb-2 border-bottom">📌 System Highlights</h3>
                        <ul class="list-unstyled small mb-4">
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Districts:</span>
                                <strong class="text-navy">38 Hubs</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Assembly Seats:</span>
                                <strong class="text-navy">243 Vidhan Sabha</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Mukhiya Seats:</span>
                                <strong class="text-success">7,346 Mukhiyas</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Sarpanch Seats:</span>
                                <strong class="text-warning">6,617 Sarpanchs</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Lok Sabha MPs:</span>
                                <strong class="text-navy">40 MPs</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Rajya Sabha MPs:</span>
                                <strong class="text-navy">15 MPs</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Vidhan Parishad:</span>
                                <strong class="text-navy">75 MLCs</strong>
                            </li>
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Zila Parishad:</span>
                                <strong class="text-primary">38 Boards (1,099+ Wards)</strong>
                            </li>
                        </ul>

                        <h3 class="h6 fw-bold text-dark mb-2 pb-1 border-bottom">🔗 Quick Admin Actions</h3>
                        <div class="d-grid gap-2">
                            <a href="constituencies.php" class="btn btn-outline-primary btn-sm text-start"><i class="fas fa-landmark me-2"></i> Manage Constituencies</a>
                            <a href="candidates.php" class="btn btn-outline-warning btn-sm text-start text-dark"><i class="fas fa-user-tie me-2"></i> Manage Candidates</a>
                            <a href="manage-ads.php" class="btn btn-outline-danger btn-sm text-start"><i class="fas fa-rectangle-ad me-2"></i> Manage Advertisements</a>
                            <a href="sitemap.php" class="btn btn-outline-secondary btn-sm text-start"><i class="fas fa-sitemap me-2"></i> Regenerate Sitemap</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <?php include 'admin-footer.php'; ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
