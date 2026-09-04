<?php
/**
 * Fast Comprehensive Live Search API: District, MP, MLA, Mukhiya, Sarpanch, CD Block
 */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config.php';

$query = trim($_GET['q'] ?? '');
if (strlen($query) < 2) {
    echo json_encode([]);
    exit;
}

$results = [];
$pdo = Database::getConnection();
$like = '%' . $query . '%';

// 1. Search Districts (Prioritized for top placement on district queries)
$districts = DataProvider::getDistricts();
foreach ($districts as $d) {
    if (stripos($d['name'], $query) !== false || 
        stripos($d['name_hi'] ?? '', $query) !== false || 
        stripos($d['headquarters'] ?? '', $query) !== false ||
        stripos($d['division'] ?? '', $query) !== false) {
        
        $results[] = [
            'type' => 'district',
            'title' => $d['name'] . ' District' . (!empty($d['name_hi']) ? ' (' . $d['name_hi'] . ')' : ''),
            'subtitle' => ($d['division'] ?? 'Bihar') . ' Division | HQ: ' . ($d['headquarters'] ?? $d['name']),
            'extra' => 'Explore District Profile, MLAs, CD Blocks & Panchayats',
            'slug' => $d['slug'],
            'url' => getDistrictUrl($d['slug'])
        ];
    }
}

// 2. Search MPs (Lok Sabha & Rajya Sabha)
if ($pdo) {
    try {
        // Lok Sabha MPs (Search by PC name, MP name, party, or district)
        $stmtLS = $pdo->prepare("SELECT pc_no, pc_name, mp_name, party FROM mps_loksabha WHERE pc_name LIKE ? OR mp_name LIKE ? OR party LIKE ? LIMIT 6");
        $stmtLS->execute([$like, $like, $like]);
        while ($mp = $stmtLS->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'mp',
                'title' => 'PC ' . $mp['pc_no'] . ' — ' . $mp['pc_name'] . ' (Lok Sabha)',
                'subtitle' => 'MP: ' . $mp['mp_name'] . ' (' . $mp['party'] . ')',
                'extra' => 'Lok Sabha Parliamentarian from Bihar',
                'slug' => slugify($mp['pc_name']),
                'url' => SITE_URL . '/mp'
            ];
        }

        // Rajya Sabha MPs
        $stmtRS = $pdo->prepare("SELECT mp_name, party, tenure FROM mps_rajyasabha WHERE mp_name LIKE ? OR party LIKE ? LIMIT 4");
        $stmtRS->execute([$like, $like]);
        while ($rs = $stmtRS->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [
                'type' => 'mp',
                'title' => $rs['mp_name'] . ' (Rajya Sabha MP)',
                'subtitle' => 'Party: ' . $rs['party'] . ' | Tenure: ' . ($rs['tenure'] ?: 'Current'),
                'extra' => 'Upper House Member representing Bihar',
                'slug' => slugify($rs['mp_name']),
                'url' => SITE_URL . '/rajya-sabha'
            ];
        }
    } catch (Throwable $e) {
        error_log("MP search error: " . $e->getMessage());
    }
}

// 3. Search Constituencies (MLAs)
$acs = DataProvider::getConstituencies();
foreach ($acs as $ac) {
    if (stripos($ac['name'], $query) !== false || 
        stripos($ac['name_hi'] ?? '', $query) !== false || 
        stripos((string)$ac['ac_no'], $query) !== false || 
        stripos($ac['district'], $query) !== false ||
        stripos($ac['current_mla'], $query) !== false ||
        stripos($ac['current_party'] ?? '', $query) !== false) {
        
        $results[] = [
            'type' => 'mla',
            'title' => 'AC ' . $ac['ac_no'] . ' — ' . $ac['name'] . (!empty($ac['name_hi']) ? ' (' . $ac['name_hi'] . ')' : ''),
            'subtitle' => 'MLA: ' . ($ac['current_mla'] ?: 'Vacant') . ' (' . ($ac['current_party'] ?: 'IND') . ')',
            'extra' => 'District: ' . $ac['district'] . ' | Total Electors: ' . number_format($ac['total_electors']),
            'slug' => $ac['slug'],
            'url' => getMlaUrl($ac)
        ];
    }
}

// 4. Search Mukhiyas (Gram Panchayat Leaders)
if ($pdo) {
    try {
        $stmtM = $pdo->prepare("SELECT panchayat_name, panchayat_hi, district, district_slug, block, current_mukhiya, mukhiya_category 
                                FROM panchayats 
                                WHERE (current_mukhiya IS NOT NULL AND current_mukhiya != '' AND current_mukhiya LIKE ?)
                                   OR (panchayat_name LIKE ? AND current_mukhiya IS NOT NULL AND current_mukhiya != '')
                                LIMIT 6");
        $stmtM->execute([$like, $like]);
        while ($m = $stmtM->fetch(PDO::FETCH_ASSOC)) {
            $pName = $m['panchayat_name'];
            $dSlug = $m['district_slug'] ?: slugify($m['district']);
            $bSlug = slugify($m['block']);
            $results[] = [
                'type' => 'mukhiya',
                'title' => 'Mukhiya: ' . ($m['current_mukhiya'] ?: 'Gram Panchayat Head'),
                'subtitle' => $pName . ' Gram Panchayat',
                'extra' => 'Block: ' . $m['block'] . ' | District: ' . $m['district'],
                'slug' => slugify($pName),
                'url' => getPanchayatUrl($dSlug, $bSlug, slugify($pName))
            ];
        }
    } catch (Throwable $e) {
        error_log("Mukhiya search error: " . $e->getMessage());
    }
}

// 5. Search Sarpanchs (Gram Kutchery Judicial Heads)
if ($pdo) {
    try {
        $stmtS = $pdo->prepare("SELECT panchayat_name, panchayat_hi, district, district_slug, block, current_sarpanch, sarpanch_category 
                                FROM panchayats 
                                WHERE current_sarpanch IS NOT NULL AND current_sarpanch != '' AND current_sarpanch LIKE ?
                                LIMIT 6");
        $stmtS->execute([$like]);
        while ($s = $stmtS->fetch(PDO::FETCH_ASSOC)) {
            $pName = $s['panchayat_name'];
            $dSlug = $s['district_slug'] ?: slugify($s['district']);
            $bSlug = slugify($s['block']);
            $results[] = [
                'type' => 'sarpanch',
                'title' => 'Sarpanch: ' . ($s['current_sarpanch'] ?: 'Gram Kutchery Head'),
                'subtitle' => $pName . ' Gram Kutchery',
                'extra' => 'Block: ' . $s['block'] . ' | District: ' . $s['district'],
                'slug' => slugify($pName),
                'url' => getPanchayatUrl($dSlug, $bSlug, slugify($pName))
            ];
        }
    } catch (Throwable $e) {
        error_log("Sarpanch search error: " . $e->getMessage());
    }
}

// 6. Search CD Blocks
if ($pdo) {
    try {
        $stmtB = $pdo->prepare("SELECT sub_district, district_name, district_slug, sub_dist_code 
                                FROM census_subdistricts 
                                WHERE sub_district LIKE ? 
                                LIMIT 6");
        $stmtB->execute([$like]);
        while ($b = $stmtB->fetch(PDO::FETCH_ASSOC)) {
            $bSlug = slugify($b['sub_district']);
            $dSlug = $b['district_slug'] ?: slugify($b['district_name']);
            $results[] = [
                'type' => 'block',
                'title' => $b['sub_district'] . ' CD Block',
                'subtitle' => 'District: ' . $b['district_name'],
                'extra' => 'Sub-district Code: ' . ($b['sub_dist_code'] ?: 'N/A') . ' | Samiti & Panchayats',
                'slug' => $bSlug,
                'url' => getBlockUrl($dSlug, $bSlug)
            ];
        }
    } catch (Throwable $e) {
        error_log("Block search error: " . $e->getMessage());
    }
}

// Return up to 25 results for full rich scrollable dropdown
echo json_encode(array_slice($results, 0, 25));
