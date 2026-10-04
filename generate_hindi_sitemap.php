<?php
/**
 * BiharElection.com - Comprehensive Hindi XML Sitemap Generator
 * Generates valid XML sitemap for all Hindi platform hubs, 38 districts, 243 Vidhan Sabha ACs, 40 Lok Sabha MPs, 75 MLCs, and 534 Blocks.
 */
require_once __DIR__ . '/config.php';

function generateHindiSitemap($base_url = 'https://biharelection.com') {
    $today = date('Y-m-d');
    $districts = DataProvider::getDistricts();
    $constituencies = DataProvider::getConstituencies();
    $candidates = DataProvider::getCandidates();
    $loksabhaMps = DataProvider::getLokSabhaMps();
    $mlcs = DataProvider::getMlcs();

    $toCanonical = function($url) use ($base_url) {
        $clean = preg_replace('#^https?://[^/]+(/biharelection)?#', $base_url, (string)$url);
        if (strpos($clean, 'http') !== 0) {
            $clean = $base_url . '/' . ltrim($clean, '/');
        }
        if (strlen($clean) > strlen($base_url) + 1) {
            $clean = rtrim($clean, '/');
        }
        return $clean;
    };

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    // 1. Static & Primary Hindi Hub Pages
    $static_pages = [
        ['url' => '/hindi', 'priority' => '1.0', 'changefreq' => 'daily'],
        ['url' => '/hindi/vidhan-parishad-election', 'priority' => '0.95', 'changefreq' => 'daily'],
        ['url' => '/hindi/blog', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/hindi/mla', 'priority' => '0.9', 'changefreq' => 'daily'],
        ['url' => '/hindi/mp', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/hindi/mlc', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/hindi/panchayat', 'priority' => '0.90', 'changefreq' => 'daily'],
        ['url' => '/hindi/village', 'priority' => '0.90', 'changefreq' => 'daily'],
        ['url' => '/hindi/town', 'priority' => '0.90', 'changefreq' => 'daily'],
        ['url' => '/hindi/zila-parishad', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/hindi/panchayat-samiti', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/hindi/block', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/hindi/census', 'priority' => '0.85', 'changefreq' => 'weekly'],
        ['url' => '/hindi/caste-survey', 'priority' => '0.90', 'changefreq' => 'weekly'],
        ['url' => '/hindi/bihar-acts', 'priority' => '0.90', 'changefreq' => 'weekly'],
        ['url' => '/hindi/search-pin-code', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/hindi/about', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/hindi/contact', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/hindi/whatsapp', 'priority' => '0.80', 'changefreq' => 'weekly'],
        ['url' => '/hindi/mission-and-vision', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/hindi/advertise', 'priority' => '0.80', 'changefreq' => 'monthly'],
        ['url' => '/hindi/disclaimer', 'priority' => '0.70', 'changefreq' => 'monthly'],
        ['url' => '/hindi/privacy-policy', 'priority' => '0.70', 'changefreq' => 'monthly'],
        ['url' => '/hindi/terms-and-conditions', 'priority' => '0.70', 'changefreq' => 'monthly'],
    ];

    foreach ($static_pages as $sp) {
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($toCanonical($sp['url'])) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>" . $sp['changefreq'] . "</changefreq>\n";
        $xml .= "        <priority>" . $sp['priority'] . "</priority>\n";
        $xml .= "    </url>\n";
    }

    // 2. 38 Bihar District Hubs (Hindi)
    foreach ($districts as $d) {
        $dSlug = strtolower($d['slug'] ?? '');
        if (!$dSlug) continue;

        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($toCanonical('/hindi/district/' . urlencode($dSlug))) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>daily</changefreq>\n";
        $xml .= "        <priority>0.90</priority>\n";
        $xml .= "    </url>\n";
    }

    // 3. 243 Vidhan Sabha Constituencies (Hindi)
    foreach ($constituencies as $c) {
        $cSlug = !empty($c['slug']) ? $c['slug'] : (string)($c['ac_no'] ?? '');
        if (!$cSlug) continue;
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars($toCanonical('/hindi/mla/' . urlencode($cSlug))) . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>daily</changefreq>\n";
        $xml .= "        <priority>0.85</priority>\n";
        $xml .= "    </url>\n";
    }

    // 4. Lok Sabha Parliamentary Constituencies & MPs (Hindi)
    foreach ($loksabhaMps as $mp) {
        $mpSlug = strtolower(trim($mp['slug'] ?? ''));
        if (!$mpSlug && !empty($mp['mp_name'])) {
            $mpSlug = slugify($mp['mp_name']);
        }
        if ($mpSlug) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($toCanonical('/hindi/mp/' . urlencode($mpSlug))) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.80</priority>\n";
            $xml .= "    </url>\n";
        }
    }

    // 5. Vidhan Parishad MLCs (Hindi)
    foreach ($mlcs as $mlc) {
        $mlcId = (string)($mlc['id'] ?? '');
        if ($mlcId) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($toCanonical('/hindi/mlc?id=' . urlencode($mlcId))) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>weekly</changefreq>\n";
            $xml .= "        <priority>0.75</priority>\n";
            $xml .= "    </url>\n";
        }
    }

    // 6. 534 Blocks (Hindi)
    $pdo = Database::getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT id, district_slug, name, slug FROM blocks ORDER BY district_slug, id");
            if ($stmt) {
                while ($b = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $bSlug = $b['slug'] ?? slugify($b['name'] ?? '');
                    $dSlug = strtolower(trim($b['district_slug'] ?? ''));
                    $bId = $b['id'] ?? '';
                    if ($dSlug && $bSlug) {
                        $xml .= "    <url>\n";
                        $xml .= "        <loc>" . htmlspecialchars($toCanonical("/hindi/block?id=" . urlencode($bId))) . "</loc>\n";
                        $xml .= "        <lastmod>{$today}</lastmod>\n";
                        $xml .= "        <changefreq>weekly</changefreq>\n";
                        $xml .= "        <priority>0.75</priority>\n";
                        $xml .= "    </url>\n";
                    }
                }
            }
        } catch (Throwable $e) {}
    }

    $xml .= '</urlset>';

    $filePath = __DIR__ . '/sitemap-hindi.xml';
    file_put_contents($filePath, $xml);
    return substr_count($xml, '<loc>');
}

if (php_sapi_name() === 'cli') {
    $count = generateHindiSitemap();
    echo "Successfully generated sitemap-hindi.xml with {$count} URLs.\n";
}
