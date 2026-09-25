<?php
/**
 * BiharElection.com - Comprehensive Census 2011 Towns & Slums XML Sitemap Generator
 * Generates valid XML sitemap for all 199 Statutory/Census Towns and 670 Slum Settlements/Wards.
 */
require_once __DIR__ . '/config.php';

function generateTownSitemap($base_url = 'https://biharelection.com') {
    $pdo = Database::getConnection();
    if (!$pdo) {
        die("Database connection failed.\n");
    }

    $today = date('Y-m-d');
    $filePath = __DIR__ . '/sitemap-towns.xml';
    
    // Open file handle for writing
    $fp = fopen($filePath, 'w');
    if (!$fp) {
        die("ERROR: Could not open {$filePath} for writing.\n");
    }

    // Helper for canonical sitemap URLs
    $toSitemapUrl = function($url) use ($base_url) {
        $clean = preg_replace('#^https?://[^/]+(/biharelection)?#', $base_url, (string)$url);
        if (strpos($clean, 'http') !== 0) {
            $clean = $base_url . '/' . ltrim($clean, '/');
        }
        if (strlen($clean) > strlen($base_url) + 1) {
            $clean = rtrim($clean, '/');
        }
        return $clean;
    };

    fwrite($fp, '<?xml version="1.0" encoding="UTF-8"?>' . "\n");
    fwrite($fp, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n");

    $totalUrls = 0;

    // 1. Root Town Directory Hub
    fwrite($fp, "    <url>\n");
    fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl('/town')) . "</loc>\n");
    fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
    fwrite($fp, "        <changefreq>daily</changefreq>\n");
    fwrite($fp, "        <priority>0.90</priority>\n");
    fwrite($fp, "    </url>\n");
    $totalUrls++;

    // 2. 38 District Urban Hubs
    $districts = DataProvider::getDistricts();
    foreach ($districts as $d) {
        $dSlug = strtolower(trim($d['slug'] ?? ''));
        if (!$dSlug) continue;

        fwrite($fp, "    <url>\n");
        fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl(getTownUrl($dSlug))) . "</loc>\n");
        fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
        fwrite($fp, "        <changefreq>weekly</changefreq>\n");
        fwrite($fp, "        <priority>0.85</priority>\n");
        fwrite($fp, "    </url>\n");
        $totalUrls++;
    }

    // 3. All 199 Towns (Municipal Corporations, Nagar Parishads, Nagar Panchayats, Census Towns)
    $stmtTowns = $pdo->query("SELECT district_slug, town_slug, town_code FROM census_towns_2011 WHERE town_slug != '' ORDER BY population DESC");
    while ($tRow = $stmtTowns->fetch(PDO::FETCH_ASSOC)) {
        $dSlug = strtolower(trim($tRow['district_slug']));
        $tSlug = strtolower(trim($tRow['town_slug']));
        if (!$dSlug || !$tSlug) continue;

        $tUrl = getTownUrl($dSlug, $tSlug);

        fwrite($fp, "    <url>\n");
        fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl($tUrl)) . "</loc>\n");
        fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
        fwrite($fp, "        <changefreq>weekly</changefreq>\n");
        fwrite($fp, "        <priority>0.85</priority>\n");
        fwrite($fp, "    </url>\n");
        $totalUrls++;
    }

    // 4. All 670 Slum Settlements / Town Wards
    $stmtSlums = $pdo->query("SELECT district_slug, town_slug, slum_slug, id FROM census_town_slums_2011 ORDER BY id ASC");
    while ($sRow = $stmtSlums->fetch(PDO::FETCH_ASSOC)) {
        $dSlug = strtolower(trim($sRow['district_slug']));
        $tSlug = strtolower(trim($sRow['town_slug']));
        $sSlug = strtolower(trim($sRow['slum_slug'] ?: (string)$sRow['id']));
        if (!$dSlug || !$tSlug || !$sSlug) continue;

        $sUrl = getSlumUrl($dSlug, $tSlug, $sSlug);

        fwrite($fp, "    <url>\n");
        fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl($sUrl)) . "</loc>\n");
        fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
        fwrite($fp, "        <changefreq>monthly</changefreq>\n");
        fwrite($fp, "        <priority>0.75</priority>\n");
        fwrite($fp, "    </url>\n");
        $totalUrls++;
    }

    fwrite($fp, '</urlset>');
    fclose($fp);

    $sizeKb = round(filesize($filePath) / 1024, 1);
    echo "SUCCESS: Generated sitemap-towns.xml with {$totalUrls} URLs ({$sizeKb} KB).\n";
    return $totalUrls;
}

// Execute if run directly from CLI
if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
    generateTownSitemap();
}
