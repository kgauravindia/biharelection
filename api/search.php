<?php
/**
 * Fast JSON Live Search Endpoint
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

// 1. Search Constituencies
$acs = DataProvider::getConstituencies();
foreach ($acs as $ac) {
    $match = false;
    if (stripos($ac['name'], $query) !== false || 
        stripos($ac['name_hi'], $query) !== false || 
        stripos((string)$ac['ac_no'], $query) !== false || 
        stripos($ac['district'], $query) !== false ||
        stripos($ac['current_mla'], $query) !== false) {
        $match = true;
    }

    if ($match) {
        $results[] = [
            'type' => 'constituency',
            'title' => 'AC ' . $ac['ac_no'] . ' — ' . $ac['name'],
            'subtitle' => 'District: ' . $ac['district'],
            'extra' => 'MLA: ' . $ac['current_mla'] . ' | ' . $ac['current_party'] . ' (Electors: ' . number_format($ac['total_electors']) . ')',
            'ac_no' => $ac['ac_no'],
            'slug' => $ac['slug'],
            'url' => getMlaUrl($ac)
        ];
    }
}

// 2. Search Districts
$districts = DataProvider::getDistricts();
foreach ($districts as $d) {
    if (stripos($d['name'], $query) !== false || stripos($d['name_hi'], $query) !== false || stripos($d['headquarters'], $query) !== false) {
        $results[] = [
            'type' => 'district',
            'title' => $d['name'] . ' District Hub',
            'subtitle' => $d['division'],
            'extra' => $d['total_ac'] . ' Assembly Constituencies | HQ: ' . $d['headquarters'],
            'slug' => $d['slug'],
            'url' => getDistrictUrl($d['slug'])
        ];
    }
}

// 3. Search Candidates
$candidates = DataProvider::getCandidates();
foreach ($candidates as $c) {
    if (stripos($c['name'], $query) !== false || stripos($c['party'], $query) !== false || stripos($c['constituency'], $query) !== false) {
        $results[] = [
            'type' => 'candidate',
            'title' => $c['name'],
            'subtitle' => $c['party_short'],
            'extra' => $c['designation'] . ' — ' . $c['constituency'],
            'slug' => $c['slug'],
            'url' => SITE_URL . '/candidate/' . $c['slug']
        ];
    }
}

// Limit results to top 8 items
echo json_encode(array_slice($results, 0, 8));
