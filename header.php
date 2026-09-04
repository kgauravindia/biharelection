<?php
/**
 * Bihar Election - Global Header Component (Bootstrap 5.3)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = $pageTitle ?? 'Bihar Election 2026: 243 Assembly Data, 38 Districts & Panchayat Hub';
$pageDescription = $pageDescription ?? 'Bihar\'s comprehensive non-government election data platform covering 243 Assembly Constituencies, 38 Districts, and 2026 Panchayat Delimitation.';
$pageKeywords = $pageKeywords ?? 'Bihar Election 2026, 243 Bihar Assembly Constituencies, Patna Vidhan Sabha, Bihar Election Results, Bihar Panchayat 2026, Bihar MLA list, Bihar Political Hub';
$pageCanonical = $pageCanonical ?? SITE_URL;
$activeNav = $activeNav ?? 'home';
?>
<!DOCTYPE html>
<html lang="en" prefix="og: http://ogp.me/ns#">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0b192c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <?php renderSeoMeta($pageTitle, $pageDescription, $pageKeywords, $pageCanonical); ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/image/logo.png">
    
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Theme Styling -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=2.6">
    
    <?php if (defined('GOOGLE_ADS_ENABLED') && GOOGLE_ADS_ENABLED && defined('GOOGLE_ADSENSE_CLIENT') && GOOGLE_ADSENSE_CLIENT !== 'ca-pub-XXXXXXXXXXXXXXXX'): ?>
    <!-- Google AdSense Official Script -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo htmlspecialchars(GOOGLE_ADSENSE_CLIENT); ?>" crossorigin="anonymous"></script>
    <?php endif; ?>
    
    <!-- Global Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "Bihar Election",
      "url": "http://localhost/biharelection",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "http://localhost/biharelection/vidhan-sabha.php?q={search_term_string}",
        "query-input": "required name=search_term_string"
      },
      "description": "Bihar's Premier Non-Government Election Data & Political Intelligence Platform covering Panchayat to Parliament."
    }
    </script>
    <!-- Global JS Site URL Definition -->
    <script>
      window.SITE_URL = "<?php echo SITE_URL; ?>";
    </script>
</head>
<body>

    <!-- Top Daily Broadcast Ticker -->
    <div class="top-ticker py-2">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate">
                <span class="badge-live">Live 2026</span>
                <span class="text-truncate small"><strong>Bihar Election Update:</strong> 2026 Panchayat Delimitation & 243 AC Profiling</span>
            </div>
            <div>
                <a href="<?php echo SITE_URL; ?>/whatsapp" class="small text-decoration-none">
                    <span>📲 Daily WhatsApp Digest &rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Bootstrap 5.3 Responsive Navbar) -->
    <header class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom shadow-sm">
        <div class="container">
            <a href="<?php echo SITE_URL; ?>/" class="brand-logo text-decoration-none me-lg-4 d-flex align-items-center gap-2 text-nowrap">
                <img src="<?php echo SITE_URL; ?>/assets/image/logo.png" alt="Bihar Election Logo" class="brand-logo-img" height="40">
                <span class="brand-title h5 mb-0 fw-bold text-nowrap" style="font-family: 'Outfit', sans-serif;">Bihar <span style="color: var(--accent-saffron);">Election</span></span>
            </a>

            <!-- Mobile Hamburger Toggle Button -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarNav" aria-controls="mainNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse mt-3 mt-lg-0" id="mainNavbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1 gap-lg-1">
                    <!-- Home -->
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/" class="nav-link px-2 px-lg-3 fw-semibold <?php echo $activeNav === 'home' ? 'active text-warning' : ''; ?>">Home</a>
                    </li>

                    <!-- District & Block Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['districts', 'district', 'blocks', 'block', 'census']) ? 'active text-warning' : ''; ?>" href="#" id="districtBlockDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            District &amp; Block
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="districtBlockDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Administrative Units</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>/district">
                                    <span>🏢</span>
                                    <div>
                                        <div class="fw-bold">All 38 Districts Hub</div>
                                        <small class="text-muted">District profile, HQ &amp; demographics</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getBlockUrl(); ?>">
                                    <span>📍</span>
                                    <div>
                                        <div class="fw-bold text-primary">534 CD Blocks Directory</div>
                                        <small class="text-muted">Sub-districts, Samitis &amp; Panchayats</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getPanchayatSamitiUrl(); ?>">
                                    <span>🌾</span>
                                    <div>
                                        <div class="fw-bold">Block Samiti &amp; Pramukh</div>
                                        <small class="text-muted">389 Blocks &amp; Samiti leadership</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getCensusUrl(); ?>">
                                    <span>📊</span>
                                    <div>
                                        <div class="fw-bold text-primary">Census 2011 &amp; Demographics</div>
                                        <small class="text-muted">38 Districts &amp; 534 Blocks Census Hub</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getZilaParishadUrl(); ?>">
                                    <span>🏛️</span>
                                    <div>
                                        <div class="fw-bold">Zila Parishad Boards</div>
                                        <small class="text-muted">38 District Boards &amp; Ward Members</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- MLA (Vidhan Sabha) Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['assembly', 'mla']) ? 'active text-warning' : ''; ?>" href="#" id="mlaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            MLA
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="mlaDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Vidhan Sabha (विधान सभा)</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>/mla">
                                    <span>🗳️</span>
                                    <div>
                                        <div class="fw-bold">243 Assembly Constituencies</div>
                                        <small class="text-muted">Current MLAs, polling data &amp; results</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>/representatives?tab=mla2015">
                                    <span>📜</span>
                                    <div>
                                        <div class="fw-bold">Historical 2015–2020 MLAs</div>
                                        <small class="text-muted">All 243 Ex-MLAs &amp; contact roster</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- MP & MLC (Parliament & Council) Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['representatives', 'mp', 'mlc']) ? 'active text-warning' : ''; ?>" href="#" id="repDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            MP &amp; MLC
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="repDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Parliament &amp; State Council</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getMpUrl(); ?>">
                                    <span>🏛️</span>
                                    <div>
                                        <div class="fw-bold">40 Lok Sabha MPs</div>
                                        <small class="text-muted">Parliamentary constituency MPs</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>/rajya-sabha">
                                    <span>👑</span>
                                    <div>
                                        <div class="fw-bold">15 Rajya Sabha MPs</div>
                                        <small class="text-muted">Upper house members from Bihar</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getMlcUrl(); ?>">
                                    <span>📜</span>
                                    <div>
                                        <div class="fw-bold">75 Vidhan Parishad MLCs</div>
                                        <small class="text-muted">Legislative Council members</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Panchayat Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['panchayat', 'mukhiya', 'sarpanch', 'zila-parishad', 'samiti', 'panchayat-samiti']) ? 'active text-warning' : ''; ?>" href="#" id="panchayatDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Panchayat
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="panchayatDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">Panchayati Raj Directory</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getPanchayatUrl(); ?>">
                                    <span>🏡</span>
                                    <div>
                                        <div class="fw-bold text-primary">Gram Panchayat Directory</div>
                                        <small class="text-muted">8,400+ Gram Panchayats (Mukhiya &amp; Sarpanch)</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo SITE_URL; ?>/blocks">
                                    <span>🏢</span>
                                    <div>
                                        <div class="fw-bold">534 CD Blocks Directory</div>
                                        <small class="text-muted">All Bihar Blocks &amp; Sub-districts</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getZilaParishadUrl(); ?>">
                                    <span>🏛️</span>
                                    <div>
                                        <div class="fw-bold">Zila Parishad Ward Members</div>
                                        <small class="text-muted">38 District Boards &amp; 1,099+ Wards</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getPanchayatSamitiUrl(); ?>">
                                    <span>⏳</span>
                                    <div>
                                        <div class="fw-bold">Block Samiti &amp; Pramukh</div>
                                        <small class="text-muted">389 Block Pramukhs &amp; Up-Pramukhs</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Representatives / Candidates -->
                    <li class="nav-item">
                        <a href="<?php echo SITE_URL; ?>/representatives" class="nav-link px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['representatives', 'candidates']) ? 'active text-warning' : ''; ?>">Representatives</a>
                    </li>

                    <!-- Blog -->
                    <li class="nav-item">
                        <a href="<?php echo getBlogUrl(); ?>" class="nav-link px-2 px-lg-3 fw-semibold <?php echo $activeNav === 'blog' ? 'active text-warning' : ''; ?>">Blog</a>
                    </li>


                    <?php if (isUserLoggedIn()): 
                        $currUser = getCurrentUser();
                    ?>
                    <!-- Logged In User Dropdown -->
                    <li class="nav-item dropdown ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-outline-dark rounded-pill px-3 py-2 fw-bold dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-color: #0b192c;">
                            <i class="bi bi-person-circle text-primary"></i> 
                            <span class="text-truncate" style="max-width: 110px;"><?php echo htmlspecialchars($currUser['name'] ?? 'Citizen'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userAccountDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark text-truncate"><?php echo htmlspecialchars($currUser['name'] ?? 'Citizen'); ?></div>
                                <div class="small text-muted">+91 <?php echo htmlspecialchars(maskMobileNumber($currUser['mobile'] ?? '')); ?></div>
                                <span class="badge bg-warning text-dark small text-uppercase mt-1"><?php echo htmlspecialchars($currUser['role'] ?? 'VOTER'); ?></span>
                            </li>
                            <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>/dashboard"><i class="bi bi-speedometer2 me-2 text-primary"></i> My Dashboard</a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo SITE_URL; ?>/dashboard"><i class="bi bi-person-gear me-2 text-secondary"></i> Profile Settings</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-semibold" href="<?php echo SITE_URL; ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> Sign Out</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <!-- Public Citizen Login Button -->
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="<?php echo SITE_URL; ?>/login" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center">
                            <i class="bi bi-person-circle"></i> Citizen Login
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center">
                            <i class="bi bi-whatsapp"></i> WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
