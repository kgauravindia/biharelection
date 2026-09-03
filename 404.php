<?php
/**
 * Bihar Election - 404 Error Page & Intelligent 301 SEO Redirection Engine
 * Automatically intercepts legacy URLs (e.g. /nalanda-lok-sabha-constituency/) and 301 redirects to /blog/[slug]
 */
require_once __DIR__ . '/config.php';

// Check if requested URL belongs to a migrated blog article
$raw_req = $_GET['request_uri'] ?? $_SERVER['REDIRECT_URL'] ?? $_SERVER['REQUEST_URI'] ?? '';
$req_path = parse_url($raw_req, PHP_URL_PATH);

// Remove subfolder if in localhost environment
if (defined('IS_LOCAL') && IS_LOCAL) {
    $req_path = preg_replace('#^/biharelection#i', '', $req_path);
}

$req_slug = trim($req_path, '/');

if (!empty($req_slug) && !in_array($req_slug, ['404', '404.php', 'index.php', 'blog', 'blog.php'])) {
    $pdo = Database::getConnection();
    if ($pdo) {
        $decoded_slug = urldecode($req_slug);
        $clean_slug = preg_replace('/^blog\//i', '', $decoded_slug);

        // 1. Direct slug match
        $stmt = $pdo->prepare("SELECT `slug` FROM `posts` WHERE (`slug` = :s1 OR `slug` = :s2 OR `slug` = :s3) AND `status` = 'published' LIMIT 1");
        $stmt->execute([
            ':s1' => $req_slug,
            ':s2' => $decoded_slug,
            ':s3' => $clean_slug
        ]);
        $found = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($found && !empty($found['slug'])) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . SITE_URL . "/blog/" . urlencode($found['slug']), true, 301);
            exit();
        }

        // 2. Fuzzy slug match
        $stmt2 = $pdo->prepare("SELECT `slug` FROM `posts` WHERE (`slug` LIKE :f1 OR `slug` LIKE :f2) AND `status` = 'published' LIMIT 1");
        $stmt2->execute([
            ':f1' => '%' . $clean_slug . '%',
            ':f2' => '%' . str_replace('-', '%', $clean_slug) . '%'
        ]);
        $found_fuzzy = $stmt2->fetch(PDO::FETCH_ASSOC);

        if ($found_fuzzy && !empty($found_fuzzy['slug'])) {
            header("HTTP/1.1 301 Moved Permanently");
            header("Location: " . SITE_URL . "/blog/" . urlencode($found_fuzzy['slug']), true, 301);
            exit();
        }
    }
}

// If no matching post was found, return standard 404 response
http_response_code(404);

$pageTitle = '404 — Page Not Found | Bihar Election Intelligence Portal';
$pageDescription = 'The requested page or electoral record could not be found. Search across 243 Bihar Assembly Constituencies, 38 Districts, 8,000+ Gram Panchayats, and Census data.';
$pageKeywords = '404 Bihar Election, Bihar Election 404, Page Not Found Bihar, Vidhan Sabha Search, Bihar District Hub';
$pageCanonical = SITE_URL . '/404';
$activeNav = '';

// Load top districts and constituencies for smart suggestions
$topDistricts = [
    ['name' => 'Patna', 'slug' => 'patna', 'acs' => 14, 'division' => 'Patna Division', 'icon' => '👑'],
    ['name' => 'Gaya', 'slug' => 'gaya', 'acs' => 10, 'division' => 'Magadh Division', 'icon' => '🛕'],
    ['name' => 'Muzaffarpur', 'slug' => 'muzaffarpur', 'acs' => 11, 'division' => 'Tirhut Division', 'icon' => '🏙️'],
    ['name' => 'Saran (Chhapra)', 'slug' => 'saran', 'acs' => 10, 'division' => 'Saran Division', 'icon' => '🌊'],
    ['name' => 'Bhagalpur', 'slug' => 'bhagalpur', 'acs' => 7, 'division' => 'Bhagalpur Division', 'icon' => '🏛️'],
    ['name' => 'Darbhanga', 'slug' => 'darbhanga', 'acs' => 10, 'division' => 'Darbhanga Division', 'icon' => '📚'],
    ['name' => 'Purnia', 'slug' => 'purnia', 'acs' => 7, 'division' => 'Purnia Division', 'icon' => '🌾'],
    ['name' => 'Begusarai', 'slug' => 'begusarai', 'acs' => 7, 'division' => 'Munger Division', 'icon' => '🏭']
];

$popularAcs = [
    ['no' => 182, 'name' => 'Bankipur', 'district' => 'Patna'],
    ['no' => 183, 'name' => 'Kumhrar', 'district' => 'Patna'],
    ['no' => 184, 'name' => 'Patna Sahib', 'district' => 'Patna'],
    ['no' => 186, 'name' => 'Danapur', 'district' => 'Patna'],
    ['no' => 118, 'name' => 'Chapra', 'district' => 'Saran'],
    ['no' => 94,  'name' => 'Muzaffarpur', 'district' => 'Muzaffarpur'],
    ['no' => 230, 'name' => 'Gaya Town', 'district' => 'Gaya'],
    ['no' => 83,  'name' => 'Darbhanga', 'district' => 'Darbhanga']
];

require_once __DIR__ . '/header.php';
?>

<style>
/* Custom 404 Specific Styling */
.error-hero {
    background: radial-gradient(circle at 50% 20%, #1e3e62 0%, #0b192c 85%);
    position: relative;
    overflow: hidden;
}

.error-hero::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: radial-gradient(circle at 80% 80%, rgba(255, 101, 0, 0.12) 0%, transparent 60%),
                radial-gradient(circle at 20% 20%, rgba(0, 135, 90, 0.12) 0%, transparent 60%);
    pointer-events: none;
}

.error-number-display {
    font-size: clamp(5.5rem, 14vw, 9.5rem);
    font-weight: 900;
    line-height: 1;
    letter-spacing: -2px;
    background: linear-gradient(135deg, #ffffff 30%, #ff9d5c 70%, #ff6500 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    text-shadow: 0 10px 30px rgba(0,0,0,0.35);
    display: inline-block;
    user-select: none;
}

.error-badge-pulse {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(239, 68, 68, 0.15);
    border: 1px solid rgba(239, 68, 68, 0.4);
    color: #fca5a5;
    padding: 6px 16px;
    border-radius: 9999px;
    font-size: 0.85rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}

.hub-card {
    transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
    background: #ffffff;
    border: 1px solid #e2e8f0;
}

.hub-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 16px 32px rgba(11, 25, 44, 0.08) !important;
    border-color: var(--accent-saffron);
}

.hub-icon-wrapper {
    width: 58px;
    height: 58px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
    transition: transform 0.25s ease;
}

.hub-card:hover .hub-icon-wrapper {
    transform: scale(1.1) rotate(4deg);
}

.quick-pill {
    background: #f1f5f9;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-radius: 9999px;
    padding: 6px 14px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.quick-pill:hover {
    background: var(--primary-navy);
    color: #ffffff;
    border-color: var(--primary-navy);
    transform: translateY(-2px);
}
</style>

<!-- 404 Hero Section -->
<section class="error-hero py-5 text-center text-white">
    <div class="container py-3 py-lg-4 position-relative">
        
        <!-- Status Indicator -->
        <div class="mb-3">
            <span class="error-badge-pulse">
                <span class="spinner-grow spinner-grow-sm text-danger" role="status" aria-hidden="true"></span>
                Error 404 &bull; Page Not Found
            </span>
        </div>

        <!-- 404 Big Visual -->
        <div class="error-number-display mb-2">
            404
        </div>

        <h1 class="display-6 fw-extrabold text-white mb-2">
            Lost in the Electoral Map?
        </h1>
        <p class="h6 text-warning mb-3 fw-semibold" style="font-family: var(--font-hi), sans-serif;">
            यह पेज उपलब्ध नहीं है या इसका पता (URL) बदल दिया गया है
        </p>

        <p class="lead text-white-50 mx-auto mb-4" style="max-width: 680px; font-size: 1.05rem;">
            The constituency, candidate, district data, or page URL you are looking for may have been moved, renamed, or is temporarily unavailable. Use our search engine or explore key sections below.
        </p>

        <!-- Search Bar with Live Suggestions Dropdown -->
        <div class="search-widget mx-auto mb-4" style="max-width: 660px;">
            <div class="search-input-group shadow-lg">
                <input 
                    type="text" 
                    id="globalSearchInput" 
                    class="search-input" 
                    placeholder="Search 243 ACs (e.g. 182 Bankipur), 38 Districts, MLAs, or Panchayats..."
                    autocomplete="off"
                >
                <button class="btn-search" onclick="document.getElementById('globalSearchInput').focus()">
                    <i class="bi bi-search"></i> <span>Search</span>
                </button>
            </div>
            <!-- Instant Dynamic Suggestions Dropdown -->
            <div id="searchDropdown" class="search-dropdown text-start"></div>
        </div>

        <!-- Primary Quick Action Buttons -->
        <div class="d-flex flex-wrap justify-content-center align-items-center gap-3">
            <a href="<?php echo SITE_URL; ?>/" class="btn btn-warning text-dark fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-house-door-fill"></i> Return to Homepage
            </a>
            <a href="<?php echo SITE_URL; ?>/vidhan-sabha.php" class="btn btn-outline-light fw-bold px-4 py-2 rounded-pill d-inline-flex align-items-center gap-2">
                <i class="bi bi-diagram-3-fill"></i> Browse 243 ACs (MLA)
            </a>
            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-4 py-2 rounded-pill shadow-sm d-inline-flex align-items-center gap-2">
                <i class="bi bi-whatsapp"></i> WhatsApp Updates
            </a>
        </div>

    </div>
</section>

<!-- Main Helpful Portals Grid -->
<main class="container my-5">

    <!-- Section Title -->
    <div class="text-center mb-5">
        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold px-3 py-2 mb-2">Explore Civic Intelligence</span>
        <h2 class="h3 fw-extrabold text-navy mb-1">Where would you like to go next?</h2>
        <p class="text-muted small mb-0">Direct access to Bihar's non-governmental electoral data archives</p>
    </div>

    <!-- 6 Core Navigation Cards -->
    <div class="row g-4 mb-5">
        
        <!-- Card 1: Vidhan Sabha (Assembly) -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 p-4 shadow-sm hub-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hub-icon-wrapper bg-warning bg-opacity-10 text-warning">
                        🗳️
                    </div>
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 mb-1">243 Constituencies</span>
                        <h3 class="h5 fw-bold mb-0 text-navy">Vidhan Sabha (MLA)</h3>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Explore election results, voter turnouts, candidate rosters, winning margins, and historical trends for all 243 Bihar Assembly seats.
                </p>
                <div class="mt-auto pt-2 border-top">
                    <a href="<?php echo SITE_URL; ?>/vidhan-sabha.php" class="text-decoration-none fw-bold text-navy small d-inline-flex align-items-center gap-1">
                        View Assembly Directory &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 2: 38 Districts Portal -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 p-4 shadow-sm hub-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hub-icon-wrapper bg-success bg-opacity-10 text-success">
                        📍
                    </div>
                    <div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1 mb-1">38 Districts</span>
                        <h3 class="h5 fw-bold mb-0 text-navy">District Hubs</h3>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Comprehensive district profiles: administrative divisions, subdivisions, blocks, AC mappings, and headquarters demographic data.
                </p>
                <div class="mt-auto pt-2 border-top">
                    <a href="<?php echo getDistrictUrl('patna'); ?>" class="text-decoration-none fw-bold text-navy small d-inline-flex align-items-center gap-1">
                        Explore Patna &amp; 37 Districts &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 3: Bihar Gram Panchayat Hub -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 p-4 shadow-sm hub-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hub-icon-wrapper bg-info bg-opacity-10 text-info">
                        🌾
                    </div>
                    <div>
                        <span class="badge bg-info bg-opacity-10 text-dark fw-bold px-2 py-1 mb-1">Local Bodies</span>
                        <h3 class="h5 fw-bold mb-0 text-navy">Gram Panchayat Hub</h3>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Verified representative directory for 8,000+ Mukhiyas, Sarpanches, Zila Parishad Councillors, and Block Pramukhs across Bihar.
                </p>
                <div class="mt-auto pt-2 border-top">
                    <a href="<?php echo getPanchayatUrl(); ?>" class="text-decoration-none fw-bold text-navy small d-inline-flex align-items-center gap-1">
                        Open Panchayat Portal &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 4: Lok Sabha & Rajya Sabha (MP) -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 p-4 shadow-sm hub-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hub-icon-wrapper bg-danger bg-opacity-10 text-danger">
                        🇮🇳
                    </div>
                    <div>
                        <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1 mb-1">40 LS + 16 RS</span>
                        <h3 class="h5 fw-bold mb-0 text-navy">Parliamentary (MP)</h3>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Profiles and voting statistics for Bihar's 40 Lok Sabha seats, Rajya Sabha representatives, and Bihar Legislative Council (MLC) members.
                </p>
                <div class="mt-auto pt-2 border-top">
                    <a href="<?php echo getMpUrl(); ?>" class="text-decoration-none fw-bold text-navy small d-inline-flex align-items-center gap-1">
                        View MP Roster &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 5: Census 2011 Hub -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 p-4 shadow-sm hub-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hub-icon-wrapper bg-primary bg-opacity-10 text-primary">
                        📊
                    </div>
                    <div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1 mb-1">Official Census</span>
                        <h3 class="h5 fw-bold mb-0 text-navy">Census 2011 Hub</h3>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Detailed demographic breakdown: total population, sex ratio, literacy rate, rural vs urban distribution across 38 districts &amp; 534 blocks.
                </p>
                <div class="mt-auto pt-2 border-top">
                    <a href="<?php echo getCensusUrl(); ?>" class="text-decoration-none fw-bold text-navy small d-inline-flex align-items-center gap-1">
                        Explore Census Data &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Card 6: Mission & Civic Intelligence -->
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 rounded-4 p-4 shadow-sm hub-card">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="hub-icon-wrapper bg-secondary bg-opacity-10 text-secondary">
                        🎯
                    </div>
                    <div>
                        <span class="badge bg-secondary bg-opacity-10 text-secondary fw-bold px-2 py-1 mb-1">About Us</span>
                        <h3 class="h5 fw-bold mb-0 text-navy">Mission &amp; Vision</h3>
                    </div>
                </div>
                <p class="text-muted small mb-3">
                    Learn about our non-partisan initiative to digitize, archive, and democratize Bihar electoral statistics for 7.64 Crore+ voters.
                </p>
                <div class="mt-auto pt-2 border-top">
                    <a href="<?php echo SITE_URL; ?>/mission-and-vision" class="text-decoration-none fw-bold text-navy small d-inline-flex align-items-center gap-1">
                        Read Our Story &rarr;
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Quick Access Pills: Popular Districts & Assembly Seats -->
    <div class="bg-white rounded-4 p-4 p-lg-5 shadow-sm border mb-5">
        <h3 class="h5 fw-bold text-navy mb-3 d-flex align-items-center gap-2">
            <span>⚡</span> Popular Districts &amp; Assembly Constituencies
        </h3>
        
        <!-- Popular Districts -->
        <div class="mb-4">
            <div class="small text-muted fw-bold text-uppercase mb-2">Key District Hubs:</div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($topDistricts as $td): ?>
                    <a href="<?php echo getDistrictUrl($td['slug']); ?>" class="quick-pill">
                        <span><?php echo $td['icon']; ?></span>
                        <span><?php echo htmlspecialchars($td['name']); ?></span>
                        <span class="badge bg-secondary bg-opacity-25 text-dark ms-1"><?php echo $td['acs']; ?> ACs</span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Popular Assembly Seats -->
        <div>
            <div class="small text-muted fw-bold text-uppercase mb-2">Featured Vidhan Sabha Seats:</div>
            <div class="d-flex flex-wrap gap-2">
                <?php foreach ($popularAcs as $pac): ?>
                    <a href="<?php echo getMlaUrl($pac['no']); ?>" class="quick-pill">
                        <span class="text-warning">🗳️</span>
                        <span>AC #<?php echo $pac['no']; ?> <?php echo htmlspecialchars($pac['name']); ?></span>
                        <span class="badge bg-warning bg-opacity-25 text-dark ms-1"><?php echo htmlspecialchars($pac['district']); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Helpdesk & Report Broken Link Banner -->
    <div class="card border-0 rounded-4 shadow-sm bg-light p-4 text-center">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8 text-md-start">
                <h4 class="h6 fw-bold text-navy mb-1">
                    <i class="bi bi-flag-fill text-warning me-1"></i> Were you expecting to find a specific page or record?
                </h4>
                <p class="text-muted small mb-0">
                    If you arrived here from an external link or found a missing candidate/constituency record, let us know and our data team will verify it.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-outline-success fw-bold btn-sm px-3 py-2 me-2">
                    <i class="bi bi-whatsapp"></i> Report via WhatsApp
                </a>
                <a href="<?php echo SITE_URL; ?>/advertise" class="btn btn-outline-secondary fw-bold btn-sm px-3 py-2">
                    <i class="bi bi-envelope"></i> Contact Us
                </a>
            </div>
        </div>
    </div>

</main>

<?php
require_once __DIR__ . '/footer.php';
?>
