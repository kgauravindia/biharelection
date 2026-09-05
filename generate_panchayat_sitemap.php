<?php
/**
 * BiharElection.com - Comprehensive Panchayat XML Sitemap Generator
 * Generates valid XML sitemap for every single Gram Panchayat across all 38 districts of Bihar.
 */
require_once __DIR__ . '/config.php';

function generatePanchayatSitemap() {
    $base_url = 'https://biharelection.com';
    $pdo = Database::getConnection();
    if (!$pdo) {
        die("Database connection failed.\n");
    }

    $districts = DataProvider::getDistricts();
    $today = date('Y-m-d');

    // Collect all unique panchayats
    $panchayatMap = [];

    // 1. Current Panchayats table
    $stmt1 = $pdo->query("SELECT id, district_slug, district, block, panchayat_name FROM panchayats WHERE panchayat_name IS NOT NULL AND panchayat_name != ''");
    while ($row = $stmt1->fetch(PDO::FETCH_ASSOC)) {
        $dSlug = strtolower(trim($row['district_slug']));
        $pSlug = slugify($row['panchayat_name']);
        if ($dSlug && $pSlug) {
            $key = "{$dSlug}/{$pSlug}";
            if (!isset($panchayatMap[$key])) {
                $panchayatMap[$key] = [
                    'district' => $dSlug,
                    'panchayat_slug' => $pSlug,
                    'name' => $row['panchayat_name'],
                    'has_mukhiya' => true,
                    'has_sarpanch' => true
                ];
            }
        }
    }

    // 2. 2016 Mukhiyas archive table
    $stmt2 = $pdo->query("SELECT id, district_slug, district, block, panchayat FROM mukhiyas_2016 WHERE panchayat IS NOT NULL AND panchayat != ''");
    while ($row = $stmt2->fetch(PDO::FETCH_ASSOC)) {
        $dSlug = strtolower(trim($row['district_slug']));
        $pSlug = slugify($row['panchayat']);
        if ($dSlug && $pSlug) {
            $key = "{$dSlug}/{$pSlug}";
            if (!isset($panchayatMap[$key])) {
                $panchayatMap[$key] = [
                    'district' => $dSlug,
                    'panchayat_slug' => $pSlug,
                    'name' => $row['panchayat'],
                    'has_mukhiya' => true,
                    'has_sarpanch' => false
                ];
            }
        }
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
    $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

    $totalUrls = 0;

    // 1. District Level Hubs (38 Districts x 3 Hubs = 114 URLs)
    foreach ($districts as $d) {
        $dSlug = strtolower($d['slug']);

        $tierUrls = [
            ['url' => "{$base_url}/panchayat/{$dSlug}", 'priority' => '0.85', 'freq' => 'weekly'],
            ['url' => "{$base_url}/zila-parishad/{$dSlug}", 'priority' => '0.80', 'freq' => 'weekly'],
            ['url' => "{$base_url}/panchayat-samiti/{$dSlug}", 'priority' => '0.80', 'freq' => 'weekly'],
        ];

        foreach ($tierUrls as $tu) {
            $xml .= "    <url>\n";
            $xml .= "        <loc>" . htmlspecialchars($tu['url']) . "</loc>\n";
            $xml .= "        <lastmod>{$today}</lastmod>\n";
            $xml .= "        <changefreq>{$tu['freq']}</changefreq>\n";
            $xml .= "        <priority>{$tu['priority']}</priority>\n";
            $xml .= "    </url>\n";
            $totalUrls++;
        }
    }

    // 2. Individual Panchayat Profile URLs
    foreach ($panchayatMap as $p) {
        $dSlug = $p['district'];
        $pSlug = $p['panchayat_slug'];

        // Canonical Panchayat URL
        $xml .= "    <url>\n";
        $xml .= "        <loc>" . htmlspecialchars("{$base_url}/panchayat/{$dSlug}/{$pSlug}") . "</loc>\n";
        $xml .= "        <lastmod>{$today}</lastmod>\n";
        $xml .= "        <changefreq>weekly</changefreq>\n";
        $xml .= "        <priority>0.80</priority>\n";
        $xml .= "    </url>\n";
        $totalUrls++;
    }

    $xml .= '</urlset>';

    $filePath = __DIR__ . '/sitemap-panchayats.xml';
    if (file_put_contents($filePath, $xml)) {
        $sizeKb = round(filesize($filePath) / 1024, 1);
        echo "SUCCESS: Generated sitemap-panchayats.xml with {$totalUrls} URLs ({$sizeKb} KB).\n";
        echo "Total unique panchayats indexed: " . count($panchayatMap) . "\n";
    } else {
        echo "ERROR: Failed to write sitemap-panchayats.xml.\n";
    }
}

// Execute if run directly from CLI
if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
    generatePanchayatSitemap();
}
