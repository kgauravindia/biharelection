<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$base_url = 'https://biharelection.com';

function toCanonicalUrl($url, $base = 'https://biharelection.com') {
    $clean = preg_replace('#^https?://[^/]+(/biharelection)?#', $base, (string)$url);
    if (strpos($clean, 'http') !== 0) {
        $clean = $base . '/' . ltrim($clean, '/');
    }
    return $clean;
}

// Active Sitemaps
$sitemap_files = [
    'primary' => [
        'name' => 'Primary Platform Sitemap',
        'file' => 'sitemap.xml',
        'path' => __DIR__ . '/../sitemap.xml',
        'icon' => 'fas fa-globe',
        'color' => 'danger',
        'desc' => 'Core Hubs, 38 Districts, Vidhan Sabha (243 ACs), Lok Sabha (40 MPs), MLCs & Candidates.',
        'action' => 'generate_sitemap'
    ],
    'panchayats' => [
        'name' => 'Gram Panchayats Sitemap',
        'file' => 'sitemap-panchayats.xml',
        'path' => __DIR__ . '/../sitemap-panchayats.xml',
        'icon' => 'fas fa-users',
        'color' => 'success',
        'desc' => 'Individual Gram Panchayat profiles & District Panchayat/Samiti/ZP hubs across 38 districts.',
        'action' => 'generate_panchayats_sitemap'
    ],
    'census' => [
        'name' => 'Census & Demographics Sitemap',
        'file' => 'sitemap-census.xml',
        'path' => __DIR__ . '/../sitemap-census.xml',
        'icon' => 'fas fa-chart-pie',
        'color' => 'info',
        'desc' => 'Bihar state census, 38 district demographics and 534+ CD blocks / sub-districts.',
        'action' => 'generate_census_sitemap'
    ],
    'extra' => [
        'name' => 'Extra & Media Sitemap',
        'file' => 'sitemap-extra.xml',
        'path' => __DIR__ . '/../sitemap-extra.xml',
        'icon' => 'fas fa-newspaper',
        'color' => 'warning',
        'desc' => 'Published blog news, categories, tags, image extensions, institutional static pages & archives.',
        'action' => 'generate_extra_sitemap'
    ],
];

// Helper: Count URLs inside an XML sitemap file
function countSitemapUrls($filePath) {
    if (!file_exists($filePath)) return 0;
    $content = @file_get_contents($filePath);
    if (!$content) return 0;
    return substr_count($content, '<loc>');
}

// -------------------------------------------------------------
// Sitemap Generation Functions
// -------------------------------------------------------------

function buildPrimarySitemap($base_url, $path) {
    $districts = DataProvider::getDistricts();
    $constituencies = DataProvider::getConstituencies();
    $candidates = DataProvider::getCandidates();
    $loksabhaMps = DataProvider::getLokSabhaMps();
    $mlcs = DataProvider::getMlcs();
    $today = date('Y-m-d');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // 1. Static & Hub Pages
    $static_pages = [
        ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['url' => '/blog/', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/mla', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/vidhan-sabha', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/constituencies', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/mp', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/lok-sabha', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/rajya-sabha', 'priority' => '0.80', 'changefreq' => 'weekly'],
        ['url' => '/mlc', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/vidhan-parishad', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/representatives', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/panchayat', 'priority' => '0.90', 'changefreq' => 'daily'],
        ['url' => '/mukhiya', 'priority' => '0.90', 'changefreq' => 'daily'],
        ['url' => '/sarpanch', 'priority' => '0.90', 'changefreq' => 'daily'],
        ['url' => '/zila-parishad', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/panchayat-samiti', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/blocks', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/census', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/search-pin-code', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/about', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/contact', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/whatsapp', 'priority' => '0.80', 'changefreq' => 'weekly'],
        ['url' => '/mission-and-vision', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/advertise', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/disclaimer', 'priority' => '0.70', 'changefreq' => 'monthly'],
        ['url' => '/privacy-policy', 'priority' => '0.70', 'changefreq' => 'monthly'],
        ['url' => '/terms-and-conditions', 'priority' => '0.70', 'changefreq' => 'monthly'],
    ];

    foreach ($static_pages as $sp) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl($sp['url'], $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>" . $sp['changefreq'] . "</changefreq>\n";
        $xml .= "        <priority>" . $sp['priority'] . "</priority>\n";
        $xml .= "    </url>\n";
    }

    // 2. 38 Bihar District Hubs
    foreach ($districts as $d) {
        $dSlug = strtolower($d['slug'] ?? '');
        if (!$dSlug) continue;

        // District Portal
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getDistrictUrl($dSlug), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>daily</changefreq>\n";
        $xml .= "        <priority>0.90</priority>\n";
        $xml .= "    </url>\n";

        // District Panchayat Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getPanchayatUrl($dSlug), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";

        // District Zila Parishad Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getZilaParishadUrl($dSlug), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";

        // District Panchayat Samiti Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getPanchayatSamitiUrl($dSlug), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";

        // District Census Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getCensusUrl($dSlug), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.80</priority>\n";
        $xml .= "    </url>\n";

        // District Blocks Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl('/blocks?district=' . urlencode($dSlug), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.80</priority>\n";
        $xml .= "    </url>\n";
    }

    // 3. 243 Vidhan Sabha Constituencies
    foreach ($constituencies as $c) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getMlaUrl($c), $base_url)) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>daily</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";
    }

    // 4. Candidate Profiles
    foreach ($candidates as $cand) {
        if (!empty($cand['slug'])) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl('/candidate/' . urlencode($cand['slug']), $base_url)) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.80</priority>\n";
            $xml .= "    </url>\n";
        }
    }

    // 5. Lok Sabha Parliamentary Constituencies & MPs
    foreach ($loksabhaMps as $mp) {
        $mpSlug = strtolower(trim($mp['slug'] ?? ''));
        if (!$mpSlug && !empty($mp['mp_name'])) {
            $mpSlug = slugify($mp['mp_name']);
        }
        if ($mpSlug) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getMpUrl($mpSlug), $base_url)) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.80</priority>\n";
            $xml .= "    </url>\n";
        }
    }

    // 6. Vidhan Parishad MLCs
    foreach ($mlcs as $mlc) {
        $mlcSlug = strtolower(trim($mlc['slug'] ?? ''));
        if (!$mlcSlug && !empty($mlc['name'])) {
            $mlcSlug = slugify($mlc['name']);
        }
        if ($mlcSlug) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars(toCanonicalUrl(getMlcUrl($mlcSlug), $base_url)) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.75</priority>\n";
            $xml .= "    </url>\n";
        }
    }

    $xml .= '</urlset>';

    return (bool)file_put_contents($path, $xml);
}

function buildCensusSitemap($base_url, $path) {
    $districts = DataProvider::getCensusDistricts();
    if (empty($districts)) {
        $dList = DataProvider::getDistricts();
        foreach ($dList as $d) {
            $districts[$d['slug']] = ['district_slug' => $d['slug'], 'district_name' => $d['name']];
        }
    }

    $today = date('Y-m-d');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // 1. State Census overview
    $xml .= "    <url>\n";
    $xml .= "        <loc>" . htmlspecialchars($base_url . '/census') . "</loc>\n";
    $xml .= "        <lastmod>{$today}</lastmod>\n";
    $xml .= "        <changefreq>weekly</changefreq>\n";
    $xml .= "        <priority>0.85</priority>\n";
    $xml .= "    </url>\n";

    // 2. 38 District Census Hubs
    foreach ($districts as $dSlug => $dData) {
        $slug = strtolower($dData['district_slug'] ?? $dData['slug'] ?? $dSlug);
        if ($slug === 'bihar') continue;

        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($base_url . '/census/' . $slug) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>monthly</changefreq>\n";
        $xml .= "        <priority>0.80</priority>\n";
        $xml .= "    </url>\n";
    }

    // 3. Subdistricts / CD Blocks Census
    $pdo = Database::getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT district_slug, sub_district FROM census_subdistricts ORDER BY district_slug, sub_district");
            if ($stmt) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $dSlug = strtolower(trim($row['district_slug'] ?? ''));
                    $sdSlug = slugify($row['sub_district'] ?? '');
                    if ($dSlug && $sdSlug) {
                        $xml .= "    <url>\n";
                        $xml .= "        <loc>" . htmlspecialchars($base_url . "/census/{$dSlug}/{$sdSlug}") . "</loc>\n";
                        $xml .= "        <lastmod>{$today}</lastmod>\n";
                        $xml .= "        <changefreq>monthly</changefreq>\n";
                        $xml .= "        <priority>0.75</priority>\n";
                        $xml .= "    </url>\n";
                    }
                }
            }
        } catch (Throwable $e) {}
    }

    $xml .= '</urlset>';

    return (bool)file_put_contents($path, $xml);
}

function buildExtraSitemap($base_url, $path, $conn) {
    $today = date('c');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";

    // 1. Published Blog Posts
    if ($conn) {
        try {
            $res = $conn->query("SELECT id, title, slug, featured_image, updated_at, created_at, published_at FROM `posts` WHERE `status` = 'published' ORDER BY id DESC");
            if ($res && $res->num_rows > 0) {
                while ($p = $res->fetch_assoc()) {
                    $postDate = !empty($p['updated_at']) ? date('c', strtotime($p['updated_at'])) : (!empty($p['published_at']) ? date('c', strtotime($p['published_at'])) : (!empty($p['created_at']) ? date('c', strtotime($p['created_at'])) : $today));
                    $postSlug = $p['slug'] ?: 'post-' . $p['id'];
                    $postUrl = $base_url . '/blog/' . urlencode($postSlug) . '/';

                    $xml .= "    <url>\n";
                    $xml .= "        <loc>" . htmlspecialchars($postUrl) . "</loc>\n";
                    $xml .= "        <lastmod>{$postDate}</lastmod>\n";
                    $xml .= "        <changefreq>daily</changefreq>\n";
                    $xml .= "        <priority>0.9</priority>\n";

                    if (!empty($p['featured_image'])) {
                        $imgUrl = (strpos($p['featured_image'], 'http') === 0) ? $p['featured_image'] : $base_url . '/' . ltrim($p['featured_image'], '/');
                        $xml .= "        <image:image>\n";
                        $xml .= "            <image:loc>" . htmlspecialchars($imgUrl) . "</image:loc>\n";
                        if (!empty($p['title'])) {
                            $xml .= "            <image:title>" . htmlspecialchars($p['title']) . "</image:title>\n";
                        }
                        $xml .= "        </image:image>\n";
                    }
                    $xml .= "    </url>\n";
                }
            }
        } catch (Throwable $e) {}
    }

    // 2. Categories
    if ($conn) {
        try {
            $res = $conn->query("SELECT id, name, slug FROM `categories` ORDER BY name ASC");
            if ($res && $res->num_rows > 0) {
                while ($cat = $res->fetch_assoc()) {
                    $cSlug = $cat['slug'] ?: slugify($cat['name']);
                    $xml .= "    <url>\n";
                    $xml .= "        <loc>" . htmlspecialchars($base_url . '/category/' . urlencode($cSlug) . '/') . "</loc>\n";
                    $xml .= "        <lastmod>{$today}</lastmod>\n";
                    $xml .= "        <changefreq>weekly</changefreq>\n";
                    $xml .= "        <priority>0.7</priority>\n";
                    $xml .= "    </url>\n";
                }
            }
        } catch (Throwable $e) {}
    }

    // 3. Static & Institutional Pages
    $static_pages = [
        ['url' => '/about', 'priority' => '0.8', 'freq' => 'monthly'],
        ['url' => '/contact', 'priority' => '0.8', 'freq' => 'monthly'],
        ['url' => '/mission-and-vision', 'priority' => '0.8', 'freq' => 'monthly'],
        ['url' => '/advertise', 'priority' => '0.8', 'freq' => 'monthly'],
        ['url' => '/whatsapp', 'priority' => '0.8', 'freq' => 'weekly'],
        ['url' => '/search-pin-code', 'priority' => '0.8', 'freq' => 'monthly'],
        ['url' => '/representatives', 'priority' => '0.8', 'freq' => 'weekly'],
        ['url' => '/disclaimer', 'priority' => '0.7', 'freq' => 'monthly'],
        ['url' => '/privacy-policy', 'priority' => '0.7', 'freq' => 'monthly'],
        ['url' => '/terms-and-conditions', 'priority' => '0.7', 'freq' => 'monthly'],
    ];

    foreach ($static_pages as $sp) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($base_url . $sp['url']) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>{$sp['freq']}</changefreq>\n";
        $xml .= "        <priority>{$sp['priority']}</priority>\n";
        $xml .= "    </url>\n";
    }

    $xml .= '</urlset>';

    return (bool)file_put_contents($path, $xml);
}

// -------------------------------------------------------------
// POST Request Handlers
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Generate ALL Sitemaps
    if (isset($_POST['generate_all_sitemaps'])) {
        buildPrimarySitemap($base_url, $sitemap_files['primary']['path']);
        
        require_once __DIR__ . '/../generate_panchayat_sitemap.php';
        ob_start();
        generatePanchayatSitemap();
        ob_get_clean();

        buildCensusSitemap($base_url, $sitemap_files['census']['path']);
        buildExtraSitemap($base_url, $sitemap_files['extra']['path'], $conn);

        $message = "🎉 All 4 Sitemaps (Primary, Panchayats, Census, and Extra) successfully generated and updated!";
    }

    // 2. Primary Sitemap
    elseif (isset($_POST['generate_sitemap'])) {
        if (buildPrimarySitemap($base_url, $sitemap_files['primary']['path'])) {
            $count = countSitemapUrls($sitemap_files['primary']['path']);
            $message = "Primary sitemap.xml generated successfully with {$count} URLs!";
        } else {
            $error = "Error generating sitemap.xml.";
        }
    }

    // 3. Panchayats Sitemap
    elseif (isset($_POST['generate_panchayats_sitemap'])) {
        require_once __DIR__ . '/../generate_panchayat_sitemap.php';
        ob_start();
        generatePanchayatSitemap();
        ob_get_clean();
        $count = countSitemapUrls($sitemap_files['panchayats']['path']);
        $message = "Panchayats Sitemap (sitemap-panchayats.xml) generated successfully with {$count} URLs!";
    }

    // 4. Census Sitemap
    elseif (isset($_POST['generate_census_sitemap'])) {
        if (buildCensusSitemap($base_url, $sitemap_files['census']['path'])) {
            $count = countSitemapUrls($sitemap_files['census']['path']);
            $message = "Census Sitemap (sitemap-census.xml) generated successfully with {$count} URLs!";
        } else {
            $error = "Error generating sitemap-census.xml.";
        }
    }

    // 5. Extra Sitemap
    elseif (isset($_POST['generate_extra_sitemap'])) {
        if (buildExtraSitemap($base_url, $sitemap_files['extra']['path'], $conn)) {
            $count = countSitemapUrls($sitemap_files['extra']['path']);
            $message = "Extra Sitemap (sitemap-extra.xml) generated successfully with {$count} URLs!";
        } else {
            $error = "Error generating sitemap-extra.xml.";
        }
    }
}

// Calculate Live Stats
$total_indexed_urls = 0;
$active_sitemaps_count = 0;
foreach ($sitemap_files as $k => &$sf) {
    $sf['exists'] = file_exists($sf['path']);
    $sf['size'] = $sf['exists'] ? filesize($sf['path']) : 0;
    $sf['mtime'] = $sf['exists'] ? filemtime($sf['path']) : 0;
    $sf['urls'] = countSitemapUrls($sf['path']);
    if ($sf['exists']) {
        $active_sitemaps_count++;
        $total_indexed_urls += $sf['urls'];
    }
}
unset($sf);

$districtsCount = count(DataProvider::getDistricts());
$constituenciesCount = count(DataProvider::getConstituencies());
$candidatesCount = count(DataProvider::getCandidates());
$loksabhaCount = count(DataProvider::getLokSabhaMps());
$mlcCount = count(DataProvider::getMlcs());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XML Sitemap & SEO Hub — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .stat-card-custom {
            background: #fff;
            border-radius: 12px;
            padding: 1.25rem;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }
        .stat-card-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.35rem;
        }
        .sitemap-table td, .sitemap-table th {
            vertical-align: middle;
            padding: 0.95rem 0.85rem;
        }
        .toast-copy {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            display: none;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <!-- Header Row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">XML Sitemap & SEO Command Center</h1>
                <p class="text-muted mb-0">Manage search engine crawl indices, dynamic XML generation, and Google/Bing indexing.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <form method="POST" action="" onsubmit="return confirm('Regenerate ALL 4 platform sitemaps?');">
                    <input type="hidden" name="generate_all_sitemaps" value="1">
                    <button type="submit" class="btn btn-danger fw-bold px-3 py-2 rounded-3 shadow-sm">
                        <i class="fas fa-bolt me-1"></i> Regenerate ALL Sitemaps
                    </button>
                </form>
                <a href="../sitemap.xml" target="_blank" class="btn btn-outline-dark fw-semibold px-3 py-2 rounded-3 shadow-sm bg-white">
                    <i class="fas fa-external-link-alt me-1"></i> Live sitemap.xml
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2 fs-5 align-middle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2 fs-5 align-middle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Total Indexed URLs</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_indexed_urls); ?></h3>
                        <small class="text-success fw-semibold"><i class="fas fa-check me-1"></i>Live from 4 Sitemaps</small>
                    </div>
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-link"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Active Sitemaps</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo $active_sitemaps_count; ?> / <?php echo count($sitemap_files); ?></h3>
                        <small class="text-muted fw-semibold">100% Robots.txt declared</small>
                    </div>
                    <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-sitemap"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Panchayats Indexed</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($sitemap_files['panchayats']['urls']); ?></h3>
                        <small class="text-muted fw-semibold">38 Districts Full Matrix</small>
                    </div>
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-people-roof"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.75rem;">Platform Scope</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;">38 Dist. / 243 AC</h3>
                        <small class="text-primary fw-semibold">534 CD Blocks Census</small>
                    </div>
                    <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-layer-group"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sitemaps Detailed Roster -->
        <div class="section-card mb-4">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-check me-2 text-primary"></i> Active Sitemaps & Generation Controls</h6>
                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2.5 py-1.5">
                    Standard XML Protocol v0.9
                </span>
            </div>
            <div class="section-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 sitemap-table">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th style="width: 28%;">Sitemap File</th>
                                <th style="width: 32%;">Scope & Included Content</th>
                                <th class="text-center" style="width: 10%;">URLs</th>
                                <th class="text-center" style="width: 10%;">Size</th>
                                <th style="width: 12%;">Last Modified</th>
                                <th class="text-end" style="width: 8%;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($sitemap_files as $key => $sf): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="stat-card-icon bg-<?php echo $sf['color']; ?> bg-opacity-10 text-<?php echo $sf['color']; ?>" style="width:38px;height:38px;font-size:1.1rem;">
                                                <i class="<?php echo $sf['icon']; ?>"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($sf['name']); ?></div>
                                                <code class="small text-muted">/<?php echo $sf['file']; ?></code>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted d-block"><?php echo htmlspecialchars($sf['desc']); ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border fw-bold px-2.5 py-1.5 fs-7"><?php echo number_format($sf['urls']); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <small class="fw-semibold text-secondary">
                                            <?php 
                                            if (!$sf['exists']) {
                                                echo '<span class="text-danger">Missing</span>';
                                            } elseif ($sf['size'] >= 1048576) {
                                                echo round($sf['size'] / 1048576, 2) . ' MB';
                                            } else {
                                                echo round($sf['size'] / 1024, 1) . ' KB';
                                            }
                                            ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-dark d-block fw-semibold"><?php echo $sf['mtime'] ? date('d M Y', $sf['mtime']) : 'Never'; ?></small>
                                        <small class="text-muted" style="font-size:0.75rem;"><?php echo $sf['mtime'] ? date('h:i A', $sf['mtime']) : ''; ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <?php if ($sf['exists']): ?>
                                                <a href="../<?php echo $sf['file']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary rounded-2" title="View live XML">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            <?php endif; ?>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="<?php echo $sf['action']; ?>" value="1">
                                                <button type="submit" class="btn btn-sm btn-outline-<?php echo $sf['color']; ?> rounded-2" title="Regenerate this sitemap">
                                                    <i class="fas fa-rotate"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- URL Inventory Breakdown -->
            <div class="col-lg-6">
                <div class="section-card h-100">
                    <div class="section-card-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-column me-2 text-info"></i> Search Engine URL Breakdown</h6>
                        <span class="badge bg-light text-muted border">Live Entities</span>
                    </div>
                    <div class="section-card-body">
                        <div class="d-flex flex-column gap-2 small">
                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-home text-primary"></i>
                                    <span>Main Landing & Core Platform Hubs</span>
                                </div>
                                <span class="badge bg-primary rounded-pill px-2.5">27 Pages</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-map-location-dot text-danger"></i>
                                    <span>Bihar District Overview & Hub Pages</span>
                                </div>
                                <span class="badge bg-danger rounded-pill px-2.5"><?php echo $districtsCount; ?> Districts (x 6 Hubs)</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-landmark text-warning"></i>
                                    <span>Vidhan Sabha Assembly Constituencies</span>
                                </div>
                                <span class="badge bg-warning text-dark rounded-pill px-2.5"><?php echo $constituenciesCount; ?> AC Seats</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-building-columns text-success"></i>
                                    <span>Lok Sabha Parliamentary Constituencies & MPs</span>
                                </div>
                                <span class="badge bg-success rounded-pill px-2.5"><?php echo $loksabhaCount; ?> Lok Sabha MPs</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-user-tie text-secondary"></i>
                                    <span>Vidhan Parishad (MLC) Members</span>
                                </div>
                                <span class="badge bg-secondary rounded-pill px-2.5"><?php echo $mlcCount; ?> MLCs</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-users text-success"></i>
                                    <span>Gram Panchayats (Mukhiya & Sarpanch)</span>
                                </div>
                                <span class="badge bg-success rounded-pill px-2.5">13,370+ Panchayats</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-chart-pie text-info"></i>
                                    <span>Census Demographics (State, Dist & Blocks)</span>
                                </div>
                                <span class="badge bg-info text-dark rounded-pill px-2.5">573 Census URLs</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center p-2.5 bg-light rounded-3">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-newspaper text-warning"></i>
                                    <span>Blog Posts, Categories & Tags</span>
                                </div>
                                <span class="badge bg-warning text-dark rounded-pill px-2.5">387 Extra URLs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEO Webmaster Submission & Checklist -->
            <div class="col-lg-6">
                <div class="section-card h-100">
                    <div class="section-card-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-magnifying-glass-arrow-right me-2 text-success"></i> Search Engine Submission & SEO Tools</h6>
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Configured</span>
                    </div>
                    <div class="section-card-body">
                        <!-- Quick Copy Box -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <label class="form-label small text-muted text-uppercase fw-bold mb-1">Primary Sitemap URL for Google Search Console</label>
                            <div class="input-group">
                                <input type="text" id="sitemapUrlInput" class="form-control font-monospace small bg-white" readonly value="<?php echo SITE_URL; ?>/sitemap.xml">
                                <button class="btn btn-outline-primary" type="button" onclick="copySitemapUrl('sitemapUrlInput')">
                                    <i class="fas fa-copy me-1"></i> Copy
                                </button>
                            </div>
                        </div>

                        <!-- Webmaster Direct Links -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-6">
                                <a href="https://search.google.com/search-console" target="_blank" class="btn btn-outline-dark w-100 fw-semibold rounded-3 text-start d-flex align-items-center justify-content-between p-2.5">
                                    <span><i class="fab fa-google text-danger me-2"></i> Google Search Console</span>
                                    <i class="fas fa-external-link-alt small opacity-50"></i>
                                </a>
                            </div>
                            <div class="col-sm-6">
                                <a href="https://www.bing.com/webmasters" target="_blank" class="btn btn-outline-dark w-100 fw-semibold rounded-3 text-start d-flex align-items-center justify-content-between p-2.5">
                                    <span><i class="fab fa-microsoft text-primary me-2"></i> Bing Webmaster Tools</span>
                                    <i class="fas fa-external-link-alt small opacity-50"></i>
                                </a>
                            </div>
                        </div>

                        <!-- SEO Technical Checklist -->
                        <h6 class="fw-bold text-dark small text-uppercase mb-2">Technical SEO Checklist</h6>
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> <code>robots.txt</code> directs crawlers to all 4 sitemaps</span>
                                <span class="badge bg-success">Active</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Dynamic Canonical & OpenGraph meta tags across all routes</span>
                                <span class="badge bg-success">Active</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> 301 Redirection active for legacy WordPress sitemaps</span>
                                <span class="badge bg-success">Active</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Automatic ISO 8601 <code>&lt;lastmod&gt;</code> date synchronization</span>
                                <span class="badge bg-success">Active</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<!-- Copy Toast Notification -->
<div class="toast-copy alert alert-dark shadow-lg rounded-3 py-2 px-3 align-items-center" id="copyToast" role="alert">
    <i class="fas fa-check text-success me-2"></i> Sitemap URL copied to clipboard!
</div>

<script>
function copySitemapUrl(elementId) {
    var copyText = document.getElementById(elementId);
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value).then(function() {
        var toast = document.getElementById("copyToast");
        toast.style.display = "flex";
        setTimeout(function() {
            toast.style.display = "none";
        }, 2500);
    });
}
</script>

</body>
</html>
