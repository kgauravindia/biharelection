<?php
/**
 * BiharElection.com - Comprehensive Census 2011 Villages XML Sitemap Generator
 * Generates valid XML sitemap for all 44,874 Census Villages across all 38 districts & 534 CD Blocks.
 */
require_once __DIR__ . '/config.php';

function generateVillageSitemap($base_url = 'https://biharelection.com') {
    $pdo = Database::getConnection();
    if (!$pdo) {
        die("Database connection failed.\n");
    }

    $today = date('Y-m-d');
    $filePath = __DIR__ . '/sitemap-villages.xml';
    
    // Open file handle for streaming write (memory efficient for 45k+ URLs)
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

    // 1. Root Village Directory Hub
    fwrite($fp, "    <url>\n");
    fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl('/village')) . "</loc>\n");
    fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
    fwrite($fp, "        <changefreq>daily</changefreq>\n");
    fwrite($fp, "        <priority>0.90</priority>\n");
    fwrite($fp, "    </url>\n");
    $totalUrls++;

    // 2. 38 District Hubs & 534 Block Hubs
    $districts = DataProvider::getDistricts();
    foreach ($districts as $d) {
        $dSlug = strtolower(trim($d['slug'] ?? ''));
        if (!$dSlug) continue;

        fwrite($fp, "    <url>\n");
        fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl(getVillageUrl($dSlug))) . "</loc>\n");
        fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
        fwrite($fp, "        <changefreq>weekly</changefreq>\n");
        fwrite($fp, "        <priority>0.85</priority>\n");
        fwrite($fp, "    </url>\n");
        $totalUrls++;
    }

    // 3. Sub-district / CD Block Level Hubs
    $stmtBlocks = $pdo->query("SELECT DISTINCT district_slug, sub_district_slug FROM census_villages_2011 WHERE district_slug != '' AND sub_district_slug != '' ORDER BY district_slug, sub_district_slug");
    while ($bRow = $stmtBlocks->fetch(PDO::FETCH_ASSOC)) {
        $dSlug = strtolower(trim($bRow['district_slug']));
        $bSlug = strtolower(trim($bRow['sub_district_slug']));
        if (!$dSlug || !$bSlug) continue;

        fwrite($fp, "    <url>\n");
        fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl(getVillageUrl($dSlug, $bSlug))) . "</loc>\n");
        fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
        fwrite($fp, "        <changefreq>weekly</changefreq>\n");
        fwrite($fp, "        <priority>0.80</priority>\n");
        fwrite($fp, "    </url>\n");
        $totalUrls++;
    }

    // 4. All 44,874 Census Villages
    $stmtVillages = $pdo->query("SELECT district_slug, sub_district_slug, village_slug, village_code FROM census_villages_2011 WHERE village_slug != '' ORDER BY id ASC");
    while ($vRow = $stmtVillages->fetch(PDO::FETCH_ASSOC)) {
        $dSlug = strtolower(trim($vRow['district_slug']));
        $bSlug = strtolower(trim($vRow['sub_district_slug']));
        $vSlug = strtolower(trim($vRow['village_slug']));
        
        if (!$dSlug || !$bSlug || !$vSlug) continue;

        $vUrl = getVillageUrl($dSlug, $bSlug, $vSlug);

        fwrite($fp, "    <url>\n");
        fwrite($fp, "        <loc>" . htmlspecialchars($toSitemapUrl($vUrl)) . "</loc>\n");
        fwrite($fp, "        <lastmod>{$today}</lastmod>\n");
        fwrite($fp, "        <changefreq>monthly</changefreq>\n");
        fwrite($fp, "        <priority>0.70</priority>\n");
        fwrite($fp, "    </url>\n");
        $totalUrls++;
    }

    fwrite($fp, '</urlset>');
    fclose($fp);

    $sizeMb = round(filesize($filePath) / (1024 * 1024), 2);
    echo "SUCCESS: Generated sitemap-villages.xml with {$totalUrls} URLs ({$sizeMb} MB).\n";
    return $totalUrls;
}

// Execute if run directly from CLI
if (php_sapi_name() === 'cli' || !isset($_SERVER['HTTP_HOST'])) {
    generateVillageSitemap();
}
