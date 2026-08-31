<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$message = '';
$error = '';
$sitemap_path = __DIR__ . '/../sitemap.xml';

// Handle Sitemap Regeneration
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['generate_sitemap'])) {
    $base_url = rtrim(SITE_URL, '/');
    $districts = DataProvider::getDistricts();
    $constituencies = DataProvider::getConstituencies();
    $candidates = DataProvider::getCandidates();

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // 1. Static Pages
    $static_pages = [
        ['url' => '/', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['url' => '/panchayat', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/mukhiya', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/sarpanch', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/zila-parishad', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/panchayat-samiti', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/mp', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/mlc', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/rajya-sabha', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['url' => '/census', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/advertise', 'priority' => '0.8', 'changefreq' => 'monthly'],
        ['url' => '/whatsapp', 'priority' => '0.8', 'changefreq' => 'weekly'],
    ];
    foreach ($static_pages as $sp) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($base_url . $sp['url']) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>" . $sp['changefreq'] . "</changefreq>\n";
        $xml .= "        <priority>" . $sp['priority'] . "</priority>\n";
        $xml .= "    </url>\n";
    }

    // 2. 38 Districts
    foreach ($districts as $d) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(getDistrictUrl($d['slug'])) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>daily</changefreq>\n";
        $xml .= "        <priority>0.9</priority>\n";
        $xml .= "    </url>\n";

        // District Mukhiya Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(getMukhiyaUrl($d['slug'])) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";

        // District Sarpanch Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(getSarpanchUrl($d['slug'])) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";

        // District Census Hub
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(getCensusUrl($d['slug'])) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.8</priority>\n";
        $xml .= "    </url>\n";
    }

    // 3. 243 Constituencies
    foreach ($constituencies as $c) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars(getMlaUrl($c)) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>daily</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";
    }

    // 4. Candidate Profiles
    foreach ($candidates as $cand) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($base_url . '/candidate/' . urlencode($cand['slug'])) . "</loc>\n";
        $xml .= "        <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.8</priority>\n";
        $xml .= "    </url>\n";
    }

    $xml .= '</urlset>';

    if (file_put_contents($sitemap_path, $xml)) {
        $total_links = count($static_pages) + (count($districts) * 4) + count($constituencies) + count($candidates);
        $message = "Sitemap XML generated successfully with {$total_links} total indexed URLs!";
    } else {
        $error = "Error writing sitemap.xml file. Check file write permissions.";
    }
}

$sitemap_exists = file_exists($sitemap_path);
$sitemap_size = $sitemap_exists ? filesize($sitemap_path) : 0;
$sitemap_mtime = $sitemap_exists ? filemtime($sitemap_path) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>XML Sitemap & SEO Tools — Bihar Election Admin</title>
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
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">XML Sitemap & SEO Generator</h1>
                <p class="text-muted mb-0">Automate Google search engine crawling for 38 Districts, 243 Constituencies & Candidates.</p>
            </div>
            <?php if ($sitemap_exists): ?>
                <div class="mt-3 mt-md-0">
                    <a href="../sitemap.xml" target="_blank" class="btn btn-outline-dark fw-semibold px-3 py-2 rounded-3 shadow-sm bg-white">
                        <i class="fas fa-external-link-alt me-1"></i> View Live sitemap.xml
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-rotate me-2 text-danger"></i> Sitemap Generation Engine</h6>
                    </div>
                    <div class="section-card-body">
                        <p class="text-muted small">
                            Clicking the button below dynamically scans all 38 districts, 243 Vidhan Sabha constituency profiles, and candidate aspirant entries to generate a 100% compliant XML sitemap.
                        </p>

                        <form method="POST" action="">
                            <input type="hidden" name="generate_sitemap" value="1">
                            <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold shadow-sm rounded-3">
                                <i class="fas fa-bolt me-2"></i> Generate & Publish XML Sitemap
                            </button>
                        </form>
                    </div>
                </div>

                <!-- SEO Recommendations -->
                <div class="section-card">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-search me-2 text-primary"></i> Search Engine Indexing Checklist</h6>
                    </div>
                    <div class="section-card-body">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> <code>robots.txt</code> points to sitemap</span>
                                <span class="badge bg-success">Configured</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Clean canonical SEO URLs</span>
                                <span class="badge bg-success">Active</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-check-circle text-success me-2"></i> Dynamic AC OpenGraph tags</span>
                                <span class="badge bg-success">Active</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="section-card">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-code me-2 text-warning"></i> Current Sitemap Status</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <small class="text-muted text-uppercase fw-bold d-block">File Location</small>
                                <code class="small">/sitemap.xml</code>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase fw-bold d-block">Status</small>
                                <span class="badge <?php echo $sitemap_exists ? 'bg-success' : 'bg-danger'; ?>">
                                    <?php echo $sitemap_exists ? 'Found & Active' : 'Not Found'; ?>
                                </span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase fw-bold d-block">Last Generated</small>
                                <span class="fw-bold text-dark"><?php echo $sitemap_mtime ? date('d M Y, h:i A', $sitemap_mtime) : 'Never'; ?></span>
                            </div>
                            <div class="col-6">
                                <small class="text-muted text-uppercase fw-bold d-block">File Size</small>
                                <span class="fw-bold text-dark"><?php echo $sitemap_size ? round($sitemap_size / 1024, 1) . ' KB' : '0 KB'; ?></span>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <h6 class="fw-bold text-dark mb-2">URLs Included in Sitemap:</h6>
                            <div class="d-flex flex-column gap-2 small">
                                <div class="d-flex justify-content-between p-2 bg-light rounded">
                                    <span>Main Landing & Static Pages</span>
                                    <span class="fw-bold">4 Pages</span>
                                </div>
                                <div class="d-flex justify-content-between p-2 bg-light rounded">
                                    <span>Bihar District Overview Pages</span>
                                    <span class="fw-bold">38 Pages</span>
                                </div>
                                <div class="d-flex justify-content-between p-2 bg-light rounded">
                                    <span>Vidhan Sabha AC Constituencies</span>
                                    <span class="fw-bold">243 Pages</span>
                                </div>
                                <div class="d-flex justify-content-between p-2 bg-light rounded">
                                    <span>Candidate Aspirant Profiles</span>
                                    <span class="fw-bold">5+ Pages</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
