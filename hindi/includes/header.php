<?php
/**
 * Bihar Election - Global Hindi Header Component (Bootstrap 5.3)
 */
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'बिहार चुनाव 2026: 243 विधानसभा डेटा, 38 जिले एवं पंचायत हब';
$pageDescription = $pageDescription ?? 'बिहार का सबसे विश्वसनीय गैर-सरकारी चुनाव डेटा पोर्टल। 243 विधानसभा क्षेत्र, 38 जिले, 534 प्रखंड और 2026 पंचायत परिसीमन की संपूर्ण जानकारी।';
$pageKeywords = $pageKeywords ?? 'बिहार चुनाव 2026, 243 बिहार विधानसभा क्षेत्र, पटना विधानसभा, बिहार चुनाव परिणाम, बिहार पंचायत 2026, बिहार विधायक सूची, बिहार राजनीतिक हब';
$pageCanonical = $pageCanonical ?? (SITE_URL . '/hindi');
$activeNav = $activeNav ?? 'home';
?>
<!DOCTYPE html>
<html lang="hi" prefix="og: http://ogp.me/ns#">
<head>
    <!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-Z71DH969QS"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'G-Z71DH969QS');
</script>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0, user-scalable=yes">
    <meta name="theme-color" content="#0b192c">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <?php renderSeoMeta($pageTitle, $pageDescription, $pageKeywords, $pageCanonical); ?>
    <?php echo getSiteHreflangTags(getSiteCanonicalUrl($pageCanonical ?? null, true)); ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?php echo SITE_URL; ?>/assets/image/logo.png">

    <!-- Preconnect for Google Web Fonts (Eliminates CLS / FOUT) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Noto+Sans+Devanagari:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800;900&display=swap">
    
    <!-- Bootstrap 5.3 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- Custom Theme Styling -->
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/style.css?v=3.1">
    
    <?php if (defined('GOOGLE_ADS_ENABLED') && GOOGLE_ADS_ENABLED && defined('GOOGLE_ADSENSE_CLIENT') && GOOGLE_ADSENSE_CLIENT !== 'ca-pub-XXXXXXXXXXXXXXXX'): ?>
    <!-- Google AdSense Official Script -->
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=<?php echo htmlspecialchars(GOOGLE_ADSENSE_CLIENT); ?>" crossorigin="anonymous"></script>
    <?php endif; ?>
    
    <!-- Global Schema.org JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "बिहार इलेक्शन",
      "url": "<?php echo SITE_URL; ?>/hindi/",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?php echo SITE_URL; ?>/hindi/vidhan-sabha?q={search_term_string}",
        "query-input": "required name=search_term_string"
      },
      "description": "बिहार का अग्रणी स्वतंत्र गैर-सरकारी चुनावी डेटा एवं राजनीतिक सूचना मंच。"
    }
    </script>
    <!-- Global JS Site URL Definition -->
    <script>
      window.SITE_URL = "<?php echo SITE_URL; ?>";
      window.HINDI_BASE_URL = "<?php echo HINDI_BASE_URL; ?>";
    </script>
</head>
<body>

    <?php if (!empty($_SESSION['impersonated_by_admin'])): ?>
    <!-- Admin Impersonation Notification Bar -->
    <div class="bg-dark text-white py-1.5 px-3 border-bottom border-warning border-3 sticky-top" style="z-index: 1090; font-size: 0.85rem; background: linear-gradient(90deg, #0f172a 0%, #1e293b 100%);">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-warning text-dark fw-bold px-2 py-1"><i class="bi bi-person-badge me-1"></i> एडमिन लॉगिन मोड</span>
                <span>आप वर्तमान में <strong><?php echo htmlspecialchars($_SESSION['public_user_name'] ?? 'नागरिक'); ?></strong> (+91 <?php echo htmlspecialchars($_SESSION['public_user_mobile'] ?? ''); ?>) के रूप में देख रहे हैं</span>
            </div>
            <div class="d-flex gap-2">
                <a href="<?php echo SITE_URL; ?>/admin/citizens.php" class="btn btn-sm btn-outline-light py-0.5 px-2.5 fw-semibold" style="font-size: 0.8rem;">
                    <i class="bi bi-shield-check me-1"></i> एडमिन सीआरएम
                </a>
                <a href="<?php echo SITE_URL; ?>/logout.php?exit_impersonation=1" class="btn btn-sm btn-warning text-dark py-0.5 px-2.5 fw-bold" style="font-size: 0.8rem;">
                    <i class="bi bi-box-arrow-right me-1"></i> मोड से बाहर निकलें
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Top Daily Broadcast Ticker -->
    <div class="top-ticker py-2">
        <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2 overflow-hidden text-truncate">
                <span class="badge-live">लाइव 2026</span>
                <span class="text-truncate small">
                    <strong>बिहार विधान परिषद चुनाव 2026:</strong> 
                    <a href="<?php echo getVidhanParishadElectionUrl(); ?>" class="text-decoration-none text-white fw-bold hover-underline">
                        4 स्नातक एवं 4 शिक्षक सीटों हेतु गजट अधिसूचना जारी (मतदान: 23 अक्टूबर) &rarr;
                    </a>
                </span>
            </div>
            <div>
                <a href="<?php echo hindi_base_url('whatsapp'); ?>" class="small text-decoration-none text-warning fw-semibold">
                    <span>📲 दैनिक व्हाट्सएप बुलेटिन &rarr;</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Main Navigation Bar (Bootstrap 5.3 Responsive Navbar) -->
    <header class="navbar navbar-expand-lg navbar-light bg-white sticky-top border-bottom shadow-sm">
        <div class="container">
            <a href="<?php echo hindi_base_url(); ?>" class="brand-logo text-decoration-none me-lg-4 d-flex align-items-center gap-2 text-nowrap">
                <img src="<?php echo SITE_URL; ?>/assets/image/logo.png" alt="बिहार इलेक्शन लोगो" class="brand-logo-img" width="40" height="40">
                <span class="brand-title h5 mb-0 fw-bold text-nowrap" style="font-family: 'Outfit', sans-serif;">Bihar <span style="color: var(--accent-saffron);">Election</span></span>
            </a>

            <!-- Mobile Search Toggle or Shortcuts -->
            <div class="d-flex align-items-center gap-2 d-lg-none ms-auto me-2">
                <a href="<?php echo hindi_base_url('vidhan-sabha'); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" title="विधानसभा खोजें">
                    <i class="bi bi-search"></i>
                </a>
            </div>

            <!-- Mobile Hamburger Toggle Button -->
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbarNav" aria-controls="mainNavbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Navbar Links -->
            <div class="collapse navbar-collapse mt-3 mt-lg-0" id="mainNavbarNav">
                <ul class="navbar-nav ms-auto align-items-lg-center gap-1 gap-lg-1">
                    <!-- Home -->
                    <li class="nav-item">
                        <a href="<?php echo hindi_base_url(); ?>" class="nav-link px-2 px-lg-3 fw-semibold <?php echo $activeNav === 'home' ? 'active text-warning' : ''; ?>">होम</a>
                    </li>

                    <!-- District & Block Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['districts', 'district', 'blocks', 'block', 'census', 'caste', 'village', 'town']) ? 'active text-warning' : ''; ?>" href="#" id="districtBlockDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            जिला एवं प्रखंड
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="districtBlockDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">प्रशासनिक एवं जनगणना</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo hindi_base_url('district'); ?>">
                                    <span>🏢</span>
                                    <div>
                                        <div class="fw-bold">सभी 38 जिले हब</div>
                                        <small class="text-muted">जिला प्रोफाइल, मुख्यालय एवं जनसांख्यिकी</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getBlockUrl(); ?>">
                                    <span>📍</span>
                                    <div>
                                        <div class="fw-bold text-primary">534 प्रखंड निर्देशिका</div>
                                        <small class="text-muted">अनुमंडल एवं प्रखंड प्रशासनिक डेटा</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getVillageUrl(); ?>">
                                    <span>🏡</span>
                                    <div>
                                        <div class="fw-bold text-success">44,874 गांव निर्देशिका</div>
                                        <small class="text-muted">जनगणना 2011 ग्रामीण प्रोफाइल व आंकड़े</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getTownUrl(); ?>">
                                    <span>🏙️</span>
                                    <div>
                                        <div class="fw-bold text-info">198 नगर निकाय एवं स्लम</div>
                                        <small class="text-muted">शहरी जनगणना 2011 एवं मलिन बस्तियां</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getCensusUrl(); ?>">
                                    <span>📊</span>
                                    <div>
                                        <div class="fw-bold text-primary">जनगणना 2011 एवं जनसांख्यिकी</div>
                                        <small class="text-muted">38 जिले एवं 534 प्रखंड जनगणना हब</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getCasteSurveyUrl(); ?>">
                                    <span>📋</span>
                                    <div>
                                        <div class="fw-bold text-warning">2022 जाति गणना एवं कोड</div>
                                        <small class="text-muted">215+ आधिकारिक जाति कोड निर्देशिका</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- MLA (Vidhan Sabha) Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['assembly', 'mla']) ? 'active text-warning' : ''; ?>" href="#" id="mlaDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            विधान सभा (MLA)
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="mlaDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">बिहार विधान सभा</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo hindi_base_url('mla'); ?>">
                                    <span>🗳️</span>
                                    <div>
                                        <div class="fw-bold">243 विधानसभा क्षेत्र</div>
                                        <small class="text-muted">वर्तमान विधायक, मतदान डेटा एवं परिणाम</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo hindi_base_url('representatives?tab=mla2015'); ?>">
                                    <span>📜</span>
                                    <div>
                                        <div class="fw-bold">ऐतिहासिक 2015–2020 विधायक</div>
                                        <small class="text-muted">सभी 243 पूर्व विधायक एवं संपर्क सूची</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getBiharActsUrl(); ?>">
                                    <span>⚖️</span>
                                    <div>
                                        <div class="fw-bold text-success">बिहार अधिनियम (1937–2026)</div>
                                        <small class="text-muted">1,723+ पारित विधान सभा कानून</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- MP & MLC (Parliament & Council) Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['representatives', 'mp', 'mlc']) ? 'active text-warning' : ''; ?>" href="#" id="repDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            सांसद एवं MLC
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="repDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">संसद एवं विधान परिषद</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getMpUrl(); ?>">
                                    <span>🏛️</span>
                                    <div>
                                        <div class="fw-bold">40 लोकसभा सांसद</div>
                                        <small class="text-muted">संसदीय निर्वाचन क्षेत्र एवं सांसद विवरण</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo hindi_base_url('mp?house=rajyasabha'); ?>">
                                    <span>📜</span>
                                    <div>
                                        <div class="fw-bold">16 राज्यसभा सांसद</div>
                                        <small class="text-muted">बिहार का उच्च सदन प्रतिनिधित्व</small>
                                    </div>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getMlcUrl(); ?>">
                                    <span>🎓</span>
                                    <div>
                                        <div class="fw-bold">75 विधान परिषद सदस्य (MLC)</div>
                                        <small class="text-muted">स्नातक, शिक्षक एवं स्थानीय निकाय क्षेत्र</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2 bg-warning bg-opacity-10" href="<?php echo getVidhanParishadElectionUrl(); ?>">
                                    <span>🗳️</span>
                                    <div>
                                        <div class="fw-bold text-dark">विधान परिषद चुनाव 2026</div>
                                        <small class="text-muted">4 स्नातक + 4 शिक्षक निर्वाचन कार्यक्रम</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Panchayati Raj Dropdown -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['panchayat', 'zila-parishad', 'panchayat-samiti']) ? 'active text-warning' : ''; ?>" href="#" id="panchayatDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            पंचायती राज
                        </a>
                        <ul class="dropdown-menu shadow-sm border-0 mt-2" aria-labelledby="panchayatDropdown">
                            <li><h6 class="dropdown-header text-uppercase small fw-bold">त्रिस्तरीय पंचायती राज</h6></li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getZilaParishadUrl(); ?>">
                                    <span>🏛️</span>
                                    <div>
                                        <div class="fw-bold text-dark">जिला स्तर: जिला परिषद बोर्ड</div>
                                        <small class="text-muted">38 जिला परिषद एवं 1,099+ प्रादेशिक वार्ड</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getPanchayatSamitiUrl(); ?>">
                                    <span>🌾</span>
                                    <div>
                                        <div class="fw-bold text-primary">प्रखंड स्तर: पंचायत समिति एवं प्रमुख</div>
                                        <small class="text-muted">534 प्रखंड एवं समिति नेतृत्व</small>
                                    </div>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="<?php echo getPanchayatUrl(); ?>">
                                    <span>🏡</span>
                                    <div>
                                        <div class="fw-bold text-success">ग्राम स्तर: ग्राम पंचायतें</div>
                                        <small class="text-muted">8,053+ ग्राम पंचायतें (मुखिया एवं सरपंच)</small>
                                    </div>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <!-- Representatives -->
                    <li class="nav-item">
                        <a href="<?php echo hindi_base_url('representatives'); ?>" class="nav-link px-2 px-lg-3 fw-semibold <?php echo in_array($activeNav, ['representatives', 'candidates']) ? 'active text-warning' : ''; ?>">जनप्रतिनिधि</a>
                    </li>

                    <!-- Blog -->
                    <li class="nav-item">
                        <a href="<?php echo getBlogUrl(); ?>" class="nav-link px-2 px-lg-3 fw-semibold <?php echo $activeNav === 'blog' ? 'active text-warning' : ''; ?>">ब्लॉग</a>
                    </li>

                    <?php if (isUserLoggedIn()): 
                        $currUser = getCurrentUser();
                    ?>
                    <!-- Logged In User Dropdown -->
                    <li class="nav-item dropdown ms-lg-2 mt-2 mt-lg-0">
                        <a class="btn btn-outline-dark rounded-pill px-3 py-2 fw-bold dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center" href="#" id="userAccountDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="border-color: #0b192c;">
                            <i class="bi bi-person-circle text-primary"></i> 
                            <span class="text-truncate" style="max-width: 110px;"><?php echo htmlspecialchars($currUser['name'] ?? 'नागरिक'); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 mt-2" aria-labelledby="userAccountDropdown">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold text-dark text-truncate"><?php echo htmlspecialchars($currUser['name'] ?? 'नागरिक'); ?></div>
                                <div class="small text-muted">+91 <?php echo htmlspecialchars(maskMobileNumber($currUser['mobile'] ?? '')); ?></div>
                                <span class="badge bg-warning text-dark small text-uppercase mt-1"><?php echo htmlspecialchars($currUser['role'] ?? 'मतदाता'); ?></span>
                            </li>
                            <li><a class="dropdown-item py-2" href="<?php echo hindi_base_url('dashboard'); ?>"><i class="bi bi-speedometer2 me-2 text-primary"></i> मेरा डैशबोर्ड</a></li>
                            <li><a class="dropdown-item py-2" href="<?php echo hindi_base_url('edit-profile'); ?>"><i class="bi bi-person-gear me-2 text-secondary"></i> प्रोफाइल सेटिंग्स</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 text-danger fw-semibold" href="<?php echo hindi_base_url('logout'); ?>"><i class="bi bi-box-arrow-right me-2"></i> लॉगआउट</a></li>
                        </ul>
                    </li>
                    <?php else: ?>
                    <!-- Public Citizen Login Button -->
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="<?php echo hindi_base_url('login'); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center">
                            <i class="bi bi-person-circle"></i> नागरिक लॉगिन
                        </a>
                    </li>
                    <?php endif; ?>

                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center">
                            <i class="bi bi-whatsapp"></i> व्हाट्सएप
                        </a>
                    </li>

                    <?php
                    if (!isset($langSwitchUrl)) {
                        $currentScript = basename($_SERVER['PHP_SELF'] ?? 'index.php');
                        $queryString = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
                        $cleanScript = str_replace('.php', '', $currentScript);
                        $langSwitchUrl = SITE_URL . '/' . ($cleanScript === 'index' ? '' : $cleanScript) . $queryString;
                    }
                    ?>
                    <li class="nav-item ms-lg-2 mt-2 mt-lg-0">
                        <a href="<?php echo htmlspecialchars($langSwitchUrl); ?>" class="btn btn-outline-primary text-primary border-primary rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-1 shadow-sm w-100 justify-content-center bg-primary bg-opacity-10" title="Switch to English">
                            <i class="bi bi-translate text-primary"></i> <span>English</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </header>
