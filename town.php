<?php
/**
 * BiharElection.com - Bihar Census 2011 Town, Slum & Ward Intelligence Directory
 * Complete coverage of 198 Statutory & Census Towns and 670 Slum Settlements / Town Wards across 38 Districts
 */
require_once __DIR__ . '/config.php';

$pdo = Database::getConnection();

// Input parameters resolution
$codeParam = trim($_GET['code'] ?? '');
$districtParam = trim($_GET['district'] ?? '');
$blockParam = trim($_GET['block'] ?? '');
$townParam = trim($_GET['town'] ?? '');
$slumParam = trim($_GET['slum'] ?? '');
$slumIdParam = (int)($_GET['slum_id'] ?? 0);
$civicParam = trim($_GET['civic'] ?? '');
$slumOnlyParam = !empty($_GET['slum_only']) && $_GET['slum_only'] !== '0';
$searchParam = trim($_GET['q'] ?? '');
$slugParam = trim($_GET['slug'] ?? '');
$sortParam = trim($_GET['sort'] ?? 'pop_desc');
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$perPage = 30;

$districtsList = DataProvider::getDistricts();
usort($districtsList, function($a, $b) {
    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
});

// Handle generic slug routing and parameter normalization
if (empty($codeParam) && empty($townParam) && empty($slumIdParam) && !empty($slugParam)) {
    if (is_numeric($slugParam) && strlen($slugParam) >= 5) {
        $codeParam = $slugParam;
    } else {
        $matchedDist = DataProvider::getDistrictBySlug($slugParam);
        if ($matchedDist) {
            $districtParam = $slugParam;
        } else {
            // Check if slug matches a town directly
            $townParam = $slugParam;
        }
    }
}

// When a single parameter is passed as district (e.g. /town/patna-m-corp-og or /town/801373)
if (empty($codeParam) && empty($townParam) && empty($slumIdParam) && !empty($districtParam)) {
    if (is_numeric($districtParam) && strlen($districtParam) >= 5) {
        $codeParam = $districtParam;
        $districtParam = '';
    } else {
        $matchedDist = DataProvider::getDistrictBySlug($districtParam);
        if (!$matchedDist) {
            // It's not a district; check if it matches a town
            $potentialTown = DataProvider::getTownBySlug('', $districtParam);
            if ($potentialTown) {
                $town = $potentialTown;
                $districtParam = $potentialTown['district_slug'];
                $townParam = $potentialTown['town_slug'];
            }
        }
    }
}

$slumObj = null;
$town = null;

// 1. Try to find a specific Slum / Ward Profile
if ($slumIdParam > 0) {
    $slumObj = DataProvider::getSlumById($slumIdParam);
    if ($slumObj) {
        $town = DataProvider::getTownByCode($slumObj['town_code']);
    }
} elseif (!empty($slumParam) && (!empty($townParam) || !empty($districtParam))) {
    $slumObj = DataProvider::getSlumBySlug($districtParam, $townParam, $slumParam);
    if ($slumObj) {
        $town = DataProvider::getTownByCode($slumObj['town_code']);
    }
}

// 2. If not a slum profile, try to find a specific Town
if (!$slumObj && !$town) {
    if (!empty($codeParam)) {
        $town = DataProvider::getTownByCode($codeParam);
    } elseif (!empty($townParam)) {
        if (is_numeric($townParam) && strlen($townParam) >= 5) {
            $town = DataProvider::getTownByCode($townParam);
        } else {
            $town = DataProvider::getTownBySlug($districtParam, $townParam);
        }
    } elseif (!empty($districtParam) && !DataProvider::getDistrictBySlug($districtParam)) {
        $town = DataProvider::getTownBySlug('', $districtParam);
    }
}

// 3. Set up metadata for Slum Profile vs Town Profile vs Directory
if ($slumObj) {
    // =========================================================================
    // SLUM / TOWN WARD PROFILE METADATA
    // =========================================================================
    $sName = $slumObj['slum_name'];
    $sId = $slumObj['id'];
    $tName = $slumObj['town_name'] ?: ($town['town_name'] ?? '');
    $tCode = $slumObj['town_code'] ?: ($town['town_code'] ?? '');
    $tSlug = $town['town_slug'] ?? ($slumObj['town_slug'] ?: slugify($tName));
    $dName = $slumObj['district_name'] ?: ($town['district_name'] ?? '');
    $dSlug = $town['district_slug'] ?? ($slumObj['district_slug'] ?: slugify($dName));
    $bName = $slumObj['sub_district_name'] ?: ($town['cd_block_name'] ?? 'Block');
    $bSlug = $slumObj['sub_district_slug'];
    $isNotified = ((int)$slumObj['is_notified'] === 1);
    
    $sPop = (int)($slumObj['slum_population'] ?? 0);
    $sHh = (int)($slumObj['households'] ?? 0);
    $sPavedKm = (float)($slumObj['paved_roads_km'] ?? 0);
    $sDrainage = $slumObj['drainage_system'] ?: 'Not Specified';
    
    $latPit = (int)($slumObj['latrines_pit'] ?? 0);
    $latFlush = (int)($slumObj['latrines_flush'] ?? 0);
    $latService = (int)($slumObj['latrines_service'] ?? 0);
    $latOthers = (int)($slumObj['latrines_others'] ?? 0);
    $latComm = (int)($slumObj['latrines_community'] ?? 0);
    $totalLatrines = $latPit + $latFlush + $latService + $latOthers + $latComm;

    $tapPoints = (int)($slumObj['tap_points_water'] ?? 0);
    $elecDom = (int)($slumObj['electricity_domestic'] ?? 0);
    $elecRoad = (int)($slumObj['electricity_road_light'] ?? 0);
    $elecOthers = (int)($slumObj['electricity_others'] ?? 0);

    $pageTitle = "{$sName} Slum & Ward Profile: Population, Sanitation & Demographics ({$tName}, {$dName})";
    $pageDescription = "Official Census 2011 Slum Release 1000 profile for {$sName} in {$tName}, {$dName} District, Bihar. Population: " . number_format($sPop) . ", Households: " . number_format($sHh) . ", Drainage: {$sDrainage}, Tap Water: {$tapPoints}.";
    $pageKeywords = "{$sName} slum, {$sName} {$tName}, {$tName} slums, Bihar town wards, Census 2011 Slum 1000 Bihar";
    $pageCanonical = getSlumUrl($dSlug, $tSlug, $slumObj['slum_slug'] ?: $sId);

    // Fetch other slums in this town
    $siblingSlums = [];
    if ($pdo) {
        try {
            $stmtSS = $pdo->prepare("SELECT * FROM census_town_slums_2011 WHERE town_code = :code AND id != :sid ORDER BY slum_population DESC");
            $stmtSS->execute([':code' => $tCode, ':sid' => $sId]);
            $siblingSlums = $stmtSS->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}
    }

} elseif ($town) {
    // =========================================================================
    // SINGLE TOWN PROFILE METADATA
    // =========================================================================
    $tName = $town['town_name'];
    $tCode = $town['town_code'];
    $dName = $town['district_name'];
    $dSlug = $town['district_slug'];
    $bName = $town['cd_block_name'] ?: ($town['sub_district_name'] ?: 'Block');
    $bSlug = $town['sub_district_slug'];
    $civicStatus = $town['civic_status'] ?: 'Town';
    $townClass = $town['town_class'] ?: 'N/A';
    
    $pop = (int)($town['population'] ?? 0);
    $hh = (int)($town['households'] ?? 0);
    $sr = (int)($town['sex_ratio'] ?? 0);
    $male = (int)($town['male'] ?? 0);
    $female = (int)($town['female'] ?? 0);
    $scPop = (int)($town['sc_population'] ?? 0);
    $stPop = (int)($town['st_population'] ?? 0);
    $scPct = ($pop > 0) ? round(($scPop / $pop) * 100, 2) : 0;
    $stPct = ($pop > 0) ? round(($stPop / $pop) * 100, 2) : 0;
    $genObcPct = max(0, round(100 - ($scPct + $stPct), 2));

    $areaSqKm = (float)($town['area_sq_km'] ?? 0);
    $density = ($areaSqKm > 0 && $pop > 0) ? round($pop / $areaSqKm, 1) : 0;
    $avgFamilySize = ($hh > 0 && $pop > 0) ? round($pop / $hh, 1) : 0;

    // Fetch Slums for this town
    $slums = DataProvider::getTownSlums($tCode);
    $totalSlums = count($slums);
    $totalSlumPop = 0;
    $totalSlumHh = 0;
    foreach ($slums as $s) {
        $totalSlumPop += (int)($s['slum_population'] ?? 0);
        $totalSlumHh += (int)($s['households'] ?? 0);
    }
    $slumPopPct = ($pop > 0 && $totalSlumPop > 0) ? round(($totalSlumPop / $pop) * 100, 1) : 0;

    // Parse detailed 429 attributes from Census Release 1000
    $rawAttrs = json_decode($town['raw_attributes'] ?? '{}', true) ?: [];

    // Historical Population Progression (1901 - 2011)
    $histYears = [1901, 1911, 1921, 1931, 1941, 1951, 1961, 1971, 1981, 1991, 2001, 2011];
    $historicalPop = [];
    foreach ($histYears as $y) {
        $popVal = $rawAttrs["Town Population (Census $y)"] ?? null;
        $grVal = $rawAttrs["Growth Rate Town (Census $y)"] ?? null;
        if ($popVal !== null && $popVal !== '' && $popVal != '0') {
            $historicalPop[] = [
                'year' => $y,
                'population' => (int)$popVal,
                'growth_rate' => ($grVal !== null && $grVal !== '') ? (float)$grVal : null
            ];
        }
    }

    // Education Facilities
    $eduGovtMedCol = (int)($rawAttrs['Govt.-Medical College (Numbers))'] ?? 0);
    $eduPvtMedCol = (int)($rawAttrs['Private-Medical College (Numbers)'] ?? 0);
    $eduGovtEngCol = (int)($rawAttrs['Govt.-Engineering College (Numbers))'] ?? 0);
    $eduPvtEngCol = (int)($rawAttrs['Private-Engineering College (Numbers)'] ?? 0);
    $eduGovtPoly = (int)($rawAttrs['Govt.-Polytechnic (Numbers))'] ?? 0);
    $eduPvtPoly = (int)($rawAttrs['Private-Polytechnic (Numbers)'] ?? 0);
    $eduGovtMgmt = (int)($rawAttrs['Govt.-Management Institute (Numbers))'] ?? 0);
    $eduGovtLaw = (int)($rawAttrs['Govt. Degree College-Law (Numbers))'] ?? 0);
    $eduGovtDeg = (int)($rawAttrs['Govt. Degree College-Art,Science and Commerce (Numbers))'] ?? 0);
    $eduPvtDeg = (int)($rawAttrs['Private Degree College-Art,Science and Commerce (Numbers)'] ?? 0);
    $eduGovtSrSec = (int)($rawAttrs['Govt. Senior Secondary School (Numbers)'] ?? 0);
    $eduPvtSrSec = (int)($rawAttrs['Private Senior Secondary School (Numbers) '] ?? $rawAttrs['Private Senior Secondary School (Numbers)'] ?? 0);
    $eduGovtSec = (int)($rawAttrs['Govt. Secondary School (Numbers)'] ?? 0);
    $eduPvtSec = (int)($rawAttrs['Private Secondary School (Numbers)'] ?? 0);
    $eduGovtMid = (int)($rawAttrs['Govt. Middle School (Numbers)'] ?? 0);
    $eduPvtMid = (int)($rawAttrs['Private Middle School (Numbers)'] ?? 0);
    $eduGovtPri = (int)($rawAttrs['Govt. Primary School (Numbers)'] ?? 0);
    $eduPvtPri = (int)($rawAttrs['Private Primary School (Numbers)'] ?? 0);
    $eduGovtDis = (int)($rawAttrs['Govt.-Special School for Disabled (Numbers))'] ?? 0);
    $eduPvtDis = (int)($rawAttrs['Private-Special School for Disabled (Numbers)'] ?? 0);

    // Healthcare Facilities
    $medAlloHosp = (int)($rawAttrs['Hospital Allopathic (Numbers)'] ?? 0);
    $medAlloBeds = (int)($rawAttrs['Hospital Allopathic Beds (Numbers)'] ?? 0);
    $medAlloDocs = (int)($rawAttrs['Hospital Allopathic Doctors-In Position (Numbers)'] ?? 0);
    $medAlloStaff = (int)($rawAttrs['Hospital Allopathic Para Medical Staff-In Postion (Numbers)'] ?? 0);
    $medAltHosp = (int)($rawAttrs['Hospital Alternative Medicine (Numbers)'] ?? 0);
    $medAltBeds = (int)($rawAttrs['Hospital Alternative Medicine Beds (Numbers)'] ?? 0);
    $medAltDocs = (int)($rawAttrs['Hospital Alternative Medicine Doctors-In Position (Numbers)'] ?? 0);
    $medAltStaff = (int)($rawAttrs['Hospital Alternative Medicine Para Medical Staff-In Position (Numbers)'] ?? 0);
    $medNursing = (int)($rawAttrs['Nursing Home (Numbers) '] ?? $rawAttrs['Nursing Home (Numbers)'] ?? 0);
    $medNursingBeds = (int)($rawAttrs['Nursing Home Beds (Numbers)'] ?? 0);
    $medTbHosp = (int)($rawAttrs['T.B. Hospital/ Clinic (Numbers)'] ?? 0);
    $medTbBeds = (int)($rawAttrs['T.B. Hospital/ Clinic Beds (Numbers)'] ?? 0);
    $medVetHosp = (int)($rawAttrs['Veterinary Hospital (Numbers)'] ?? 0);
    $medCharitable = (int)($rawAttrs['Non-Government Charitable-Hospital/Nursing Home (Numbers)'] ?? 0);
    $medShops = (int)($rawAttrs['Non-Government Medicine Shop (Numbers)'] ?? 0);

    // Public Amenities & Culture
    $recStadium = (int)($rawAttrs['Govt.-Stadium (Numbers))'] ?? 0);
    $recCinema = (int)($rawAttrs['Private-Cinema Theatre (Numbers)'] ?? 0);
    $recAuditorium = (int)($rawAttrs['Govt.-Auditorium/Community Hall (Numbers))'] ?? 0);
    $recLibrary = (int)($rawAttrs['Govt.-Public Library (Numbers))'] ?? 0);
    $recReadingRoom = (int)($rawAttrs['Govt.-Public Reading Room (Numbers))'] ?? 0);

    // Credit Societies
    $agrCreditSoc = (int)($rawAttrs['Agricultural Credit Society (Numbers)'] ?? 0);
    $nonAgrCreditSoc = (int)($rawAttrs['Non-Agricultural Credit Society (Numbers)'] ?? 0);

    // Climate & Drainage
    $rainfallMm = $rawAttrs['Rainfall (mm.)'] ?? '';
    $tempMax = $rawAttrs['Maximum Temperature (in centigrade)'] ?? '';
    $tempMin = $rawAttrs['Minimum Temperature (in centigrade)'] ?? '';

    // Banking & Industry
    $natBanks = (int)($town['nationalised_banks'] ?? ($rawAttrs['Nationalised Bank (Numbers)'] ?? 0));
    $comBanks = (int)($town['commercial_banks'] ?? ($rawAttrs['Private Commercial Bank (Numbers)'] ?? 0));
    $coopBanks = (int)($town['cooperative_banks'] ?? ($rawAttrs['Co-operative Bank (Numbers)'] ?? 0));
    $totalBanks = $natBanks + $comBanks + $coopBanks;

    $manuf1 = trim($town['manufactured_1'] ?? ($rawAttrs["Manufactured Commodity (First)\n"] ?? $rawAttrs['Manufactured Commodity (First)'] ?? ''));
    $manuf2 = trim($town['manufactured_2'] ?? ($rawAttrs['Manufactured Commodity (Second)'] ?? ''));
    $manuf3 = trim($town['manufactured_3'] ?? ($rawAttrs['Manufactured Commodity (Third)'] ?? ''));
    $manufacturedItems = array_filter([$manuf1, $manuf2, $manuf3]);

    $pageTitle = "{$tName} Town Population, Civic Status, Slums & Census 2011 Data ({$dName})";
    $pageDescription = "Official 2011 Census urban data for {$tName} ({$civicStatus}, Code: {$tCode}), {$dName} District, Bihar. Population: " . number_format($pop) . ", Households: " . number_format($hh) . ", Sex Ratio: {$sr}, Slums: {$totalSlums}.";
    $pageKeywords = "{$tName} town, {$tName} census 2011, {$tName} population, {$tName} slums, {$civicStatus} {$tName}, {$dName} district urban towns, Bihar Census 2011 towns";
    $pageCanonical = getTownUrl($dSlug, $town['town_slug']);

    // Fetch sibling towns in same district
    $nearbyTowns = DataProvider::getNearbyTowns($dSlug, $town['id'], 6);

    // Fetch Historical 1991 Census urban profile for this town
    $town1991 = null;
    if ($pdo) {
        $town1991 = DataProvider::getTown1991BySlug($dSlug, $town['town_slug']);
        if (!$town1991 && !empty($tName)) {
            $town1991 = DataProvider::getTown1991BySlug($dSlug, $tName);
        }
    }

} else {
    // =========================================================================
    // DIRECTORY MODE
    // =========================================================================
    $distLabel = !empty($districtParam) ? ucfirst($districtParam) . ' District ' : 'Bihar ';
    $pageTitle = "{$distLabel}Census 2011 Towns & Slums Directory: 198 Urban Centers Population & Demographics";
    $pageDescription = "Explore the complete Census 2011 town and slum directory of Bihar covering all 198 statutory towns, municipal corporations, nagar parishads, census towns and 670 slum areas across 38 districts.";
    $pageKeywords = "Bihar towns directory, Bihar 198 towns, Bihar census towns list, Bihar municipal corporations, Bihar slum population 2011, Bihar urban census 2011";
    $pageCanonical = !empty($districtParam) ? getTownUrl($districtParam) : SITE_URL . "/town";

    // Fetch CD Blocks for active district if selected
    $districtBlocks = [];
    if (!empty($districtParam) && $pdo) {
        try {
            $stmtB = $pdo->prepare("SELECT sub_district_slug, sub_district_name, cd_block_name, COUNT(*) as town_count, SUM(population) as total_pop FROM census_towns_2011 WHERE district_slug = :dslug GROUP BY sub_district_slug, sub_district_name, cd_block_name ORDER BY sub_district_name ASC");
            $stmtB->execute([':dslug' => strtolower(trim($districtParam))]);
            $districtBlocks = $stmtB->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {}
    }

    // Build directory query with pagination & sorting
    $where = [];
    $params = [];

    if (!empty($districtParam)) {
        $where[] = "district_slug = :dslug";
        $params[':dslug'] = strtolower(trim($districtParam));
    }
    if (!empty($blockParam)) {
        $where[] = "(sub_district_slug = :bslug OR cd_block_name LIKE :bslug_like)";
        $params[':bslug'] = strtolower(trim($blockParam));
        $params[':bslug_like'] = '%' . trim($blockParam) . '%';
    }
    if (!empty($civicParam)) {
        $where[] = "civic_status = :civic";
        $params[':civic'] = trim($civicParam);
    }
    if ($slumOnlyParam) {
        $where[] = "slum_count > 0";
    }
    if (!empty($searchParam)) {
        $where[] = "(town_name LIKE :q OR town_code LIKE :q OR district_name LIKE :q OR cd_block_name LIKE :q OR civic_status LIKE :q)";
        $params[':q'] = '%' . trim($searchParam) . '%';
    }

    $whereSql = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

    $orderBy = "population DESC";
    if ($sortParam === 'name_asc') {
        $orderBy = "town_name ASC";
    } elseif ($sortParam === 'name_desc') {
        $orderBy = "town_name DESC";
    } elseif ($sortParam === 'pop_asc') {
        $orderBy = "population ASC";
    } elseif ($sortParam === 'slum_desc') {
        $orderBy = "slum_population DESC, slum_count DESC";
    } elseif ($sortParam === 'area_desc') {
        $orderBy = "area_sq_km DESC";
    }

    $totalCount = 0;
    $townsList = [];
    $totalUrbanPop = 0;
    $totalTownCount = 0;
    $totalSlumPopAll = 0;

    if ($pdo) {
        try {
            // Aggregate summary for Bihar Urban
            $agg = $pdo->query("SELECT COUNT(*) as total_towns, SUM(population) as total_pop, SUM(households) as total_hh, SUM(slum_count) as total_slums, SUM(slum_population) as total_slum_pop FROM census_towns_2011")->fetch(PDO::FETCH_ASSOC);
            $totalUrbanPop = (int)($agg['total_pop'] ?? 0);
            $totalTownCount = (int)($agg['total_towns'] ?? 0);
            $totalSlumPopAll = (int)($agg['total_slum_pop'] ?? 0);

            $countStmt = $pdo->prepare("SELECT COUNT(*) FROM census_towns_2011 $whereSql");
            $countStmt->execute($params);
            $totalCount = (int)$countStmt->fetchColumn();

            $offset = max(0, ($currentPage - 1) * $perPage);
            $stmt = $pdo->prepare("SELECT * FROM census_towns_2011 $whereSql ORDER BY $orderBy LIMIT :limit OFFSET :offset");
            foreach ($params as $k => $v) {
                $stmt->bindValue($k, $v);
            }
            $stmt->bindValue(':limit', (int)$perPage, PDO::PARAM_INT);
            $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
            $stmt->execute();
            $townsList = $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (Throwable $e) {
            error_log("Towns query error: " . $e->getMessage());
        }
    }

    // Fetch distinct civic statuses for dropdown
    $civicStatuses = DataProvider::getCensusTownCivicStatuses();
}

$activeNav = 'census';
require_once __DIR__ . '/header.php';
?>

<style>
/* Modern Premium Redesign Tokens for Town & Slums */
:root {
    --town-navy-dark: #081225;
    --town-navy-card: #0f203c;
    --town-cyan: #06b6d4;
    --town-indigo: #6366f1;
    --town-purple: #8b5cf6;
    --town-amber: #f59e0b;
    --town-emerald: #10b981;
    --town-rose: #f43f5e;
}

/* Glassmorphism & Hero Mesh Gradients */
.town-hero-gradient {
    background: radial-gradient(at 15% 15%, rgba(99, 102, 241, 0.28) 0px, transparent 60%),
                radial-gradient(at 85% 85%, rgba(6, 182, 212, 0.22) 0px, transparent 60%),
                linear-gradient(135deg, #091833 0%, #0d284f 50%, #15396b 100%);
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}
.town-hero-gradient::after {
    content: "";
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(90deg, transparent, rgba(6, 182, 212, 0.5), rgba(245, 158, 11, 0.5), transparent);
}
.slum-hero-gradient {
    background: radial-gradient(at 15% 15%, rgba(245, 158, 11, 0.3) 0px, transparent 60%),
                radial-gradient(at 85% 85%, rgba(244, 63, 94, 0.25) 0px, transparent 60%),
                linear-gradient(135deg, #2a1104 0%, #52230a 50%, #7c330c 100%);
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

/* Cards & Shadows */
.town-glass-card {
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.85);
    border-radius: 1.25rem;
    box-shadow: 0 4px 20px -4px rgba(15, 23, 42, 0.05);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.town-glass-card:hover {
    box-shadow: 0 16px 32px -8px rgba(15, 23, 42, 0.12);
    transform: translateY(-3px);
}
.town-kpi-card {
    background: #ffffff;
    border-radius: 1.15rem;
    border: 1px solid rgba(226, 232, 240, 0.9);
    box-shadow: 0 4px 16px -2px rgba(15, 23, 42, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.town-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.08);
}

/* Pill Badges */
.badge-glass-dark {
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #ffffff;
}
.badge-glass-accent {
    background: rgba(245, 158, 11, 0.2);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(245, 158, 11, 0.4);
    color: #fbbf24;
}
.badge-civic {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    padding: 0.35rem 0.75rem;
    border-radius: 9999px;
}

/* Table Styling */
.table-slum th {
    background-color: #f8fafc;
    font-size: 0.78rem;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #475569;
    font-weight: 700;
    border-bottom: 2px solid #e2e8f0;
}
.table-slum tbody tr {
    transition: background-color 0.15s ease, transform 0.15s ease;
}
.table-slum tbody tr:hover {
    background-color: #f1f5f9 !important;
}

/* Filter Buttons */
.slum-chip-btn {
    border-radius: 9999px;
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.35rem 0.9rem;
    transition: all 0.2s ease;
}
.slum-chip-btn.active {
    background: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.15);
}

@media print {
    .town-hero-gradient, .slum-hero-gradient, nav, .btn, form, footer, .ad-container, #slumDetailModal {
        display: none !important;
    }
}
</style>

<?php if ($slumObj): ?>
    <!-- ========================================================================= -->
    <!-- 1. DEDICATED SLUM / TOWN WARD PROFILE VIEW                                -->
    <!-- ========================================================================= -->

    <!-- Schema.org JSON-LD Structured Data for Slum Settlement -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Place",
      "name": "<?php echo addslashes($sName); ?>",
      "description": "Census 2011 Slum 1000 settlement profile for <?php echo addslashes($sName); ?> in <?php echo addslashes($tName); ?>, <?php echo addslashes($dName); ?> District, Bihar.",
      "containedInPlace": {
        "@type": "City",
        "name": "<?php echo addslashes($tName); ?>"
      },
      "identifier": "<?php echo $sId; ?>"
    }
    </script>

    <!-- Slum Header Hero Banner -->
    <section class="slum-hero-gradient text-white py-4 py-md-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white-50 text-decoration-none">Census 2011</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl(); ?>" class="text-white-50 text-decoration-none">Towns Directory</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl($dSlug); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($dName); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($tName); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($sName); ?></li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-geo-alt-fill me-1"></i> Slum ID: #<?php echo $sId; ?>
                        </span>
                        <span class="badge badge-glass-dark px-3 py-1.5 rounded-pill shadow-sm">
                            Town Code: <?php echo htmlspecialchars($tCode); ?>
                        </span>
                        <?php if ($isNotified): ?>
                            <span class="badge bg-success text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                                <i class="bi bi-check-circle-fill me-1"></i> Notified by Government
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                                Non-Notified Settlement
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-6 fw-bold mb-2 font-heading text-white">
                        🛖 <?php echo htmlspecialchars($sName); ?>
                    </h1>
                    
                    <p class="lead mb-0 text-white-75 fs-6">
                        Designated Slum / Urban Ward in <strong><?php echo htmlspecialchars($tName); ?></strong>, 
                        <strong><?php echo htmlspecialchars($dName); ?></strong> District, Bihar (Census 2011 Slum_1000).
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Check {$sName} Slum & Ward Demographics in {$tName}: " . $pageCanonical); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-whatsapp me-1"></i> Share
                        </a>
                        <a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="btn btn-outline-light fw-bold rounded-pill px-3 py-2">
                            <i class="bi bi-arrow-left me-1"></i> <?php echo htmlspecialchars($tName); ?> Town Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Slum Main Container -->
    <main class="container py-4">

        <!-- Top Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Quick Slum KPI Tiles -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-warning">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slum Population</span>
                    <h3 class="fw-bold text-danger mb-0 fs-4"><?php echo number_format($sPop); ?></h3>
                    <span class="text-xs text-muted">Persons (2011)</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-info">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Households</span>
                    <h3 class="fw-bold text-info mb-0 fs-4"><?php echo number_format($sHh); ?></h3>
                    <span class="text-xs text-muted">Families</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-success">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Paved Roads</span>
                    <h3 class="fw-bold text-success mb-0 fs-4"><?php echo $sPavedKm; ?></h3>
                    <span class="text-xs text-muted">Kilometers</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-primary">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Latrines</span>
                    <h3 class="fw-bold text-primary mb-0 fs-4"><?php echo $totalLatrines; ?></h3>
                    <span class="text-xs text-muted">Sanitation Units</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-info">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Public Tap Water</span>
                    <h3 class="fw-bold text-info mb-0 fs-4"><?php echo $tapPoints; ?></h3>
                    <span class="text-xs text-muted">Hydrant / Taps</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-warning">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Domestic Elec.</span>
                    <h3 class="fw-bold text-dark mb-0 fs-4"><?php echo $elecDom; ?></h3>
                    <span class="text-xs text-muted">Connections</span>
                </div>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Sanitation, Drainage & Electricity Infrastructure -->
            <div class="col-lg-7">
                <!-- Sanitation & Latrines Card -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-droplet-fill text-primary me-2"></i>Sanitation &amp; Latrine Facilities</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold small">Census 2011</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Flush / Pour Flush</span>
                                <h4 class="fw-bold text-success mb-0"><?php echo $latFlush; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Pit Latrines</span>
                                <h4 class="fw-bold text-primary mb-0"><?php echo $latPit; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Community Latrines</span>
                                <h4 class="fw-bold text-info mb-0"><?php echo $latComm; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Service Latrines</span>
                                <h4 class="fw-bold text-secondary mb-0"><?php echo $latService; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <div class="p-3 bg-light rounded-3 text-center border">
                                <span class="text-muted small d-block mb-1">Other Latrine Types</span>
                                <h4 class="fw-bold text-dark mb-0"><?php echo $latOthers; ?></h4>
                                <span class="text-xs text-muted">Units</span>
                            </div>
                        </div>
                    </div>

                    <!-- Drainage Status -->
                    <div class="p-3 rounded-3 bg-light border d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <span class="text-muted small d-block">System of Drainage:</span>
                            <span class="fw-bold text-navy fs-6"><?php echo htmlspecialchars($sDrainage); ?></span>
                        </div>
                        <span class="badge bg-primary text-white px-3 py-1.5 rounded-pill">
                            <?php echo htmlspecialchars($sDrainage); ?>
                        </span>
                    </div>
                </div>

                <!-- Water Supply & Power Grid -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-lightning-charge-fill text-warning me-2"></i>Water Supply &amp; Electricity Grid</span>
                        <span class="badge bg-warning-subtle text-dark fw-semibold small">Civic Amenities</span>
                    </h4>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-info-subtle border border-info-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">🚰 Protected Water Supply</span>
                                </div>
                                <div class="fs-4 fw-bold text-navy mb-1"><?php echo $tapPoints; ?> Tap Points</div>
                                <p class="small text-muted mb-0">Public hydrants and protected water tap points installed for this slum cluster.</p>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle h-100">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-dark">⚡ Electricity Connections</span>
                                </div>
                                <div class="small">
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Domestic Connections:</span>
                                        <strong><?php echo $elecDom; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1 border-bottom">
                                        <span class="text-muted">Road/Street Lighting:</span>
                                        <strong><?php echo $elecRoad; ?></strong>
                                    </div>
                                    <div class="d-flex justify-content-between py-1">
                                        <span class="text-muted">Other Connections:</span>
                                        <strong><?php echo $elecOthers; ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Parent Town Summary & Navigation -->
            <div class="col-lg-5">
                <!-- Parent Town Summary Card -->
                <div class="card town-glass-card p-4 mb-4 border-top border-4 border-primary">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3">
                        <i class="bi bi-buildings-fill text-primary me-2"></i>Parent Town Information
                    </h4>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Town Name:</span>
                            <a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="fw-bold text-primary text-decoration-none">
                                <?php echo htmlspecialchars($tName); ?> &rarr;
                            </a>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Town Code:</span>
                            <span class="font-monospace fw-bold"><?php echo htmlspecialchars($tCode); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">District:</span>
                            <span class="fw-bold text-navy"><?php echo htmlspecialchars($dName); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Total Town Population:</span>
                            <span class="fw-bold text-dark"><?php echo number_format((int)($town['population'] ?? 0)); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Civic Status:</span>
                            <span class="badge bg-primary text-white"><?php echo htmlspecialchars($town['civic_status'] ?? 'Town'); ?></span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex flex-column gap-2">
                        <a href="<?php echo getTownUrl($dSlug, $tSlug); ?>" class="btn btn-primary rounded-pill w-100 fw-bold shadow-sm">
                            🏙️ Full <?php echo htmlspecialchars($tName); ?> Profile &rarr;
                        </a>
                        <a href="<?php echo getTownUrl($dSlug); ?>" class="btn btn-outline-primary rounded-pill w-100 fw-semibold">
                            🏙️ All <?php echo htmlspecialchars($dName); ?> Urban Towns &rarr;
                        </a>
                    </div>
                </div>

                <!-- Sibling Slums in same town -->
                <?php if (!empty($siblingSlums)): ?>
                    <div class="card town-glass-card p-4 mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-navy font-heading fs-6 mb-0">
                                🛖 Other Slums in <?php echo htmlspecialchars($tName); ?> (<?php echo count($siblingSlums); ?>)
                            </h5>
                        </div>

                        <div class="list-group list-group-flush small" style="max-height: 380px; overflow-y: auto;">
                            <?php foreach ($siblingSlums as $ss): 
                                $ssUrl = getSlumUrl($dSlug, $tSlug, $ss['slum_slug'] ?: $ss['id']);
                                $ssPop = (int)($ss['slum_population'] ?? 0);
                                $ssHh = (int)($ss['households'] ?? 0);
                            ?>
                                <a href="<?php echo htmlspecialchars($ssUrl); ?>" class="list-group-item list-group-item-action px-2 py-2.5 d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold text-navy"><?php echo htmlspecialchars($ss['slum_name']); ?></div>
                                        <span class="text-xs text-muted">👥 <?php echo number_format($ssPop); ?> Pop. • 🏠 <?php echo number_format($ssHh); ?> HH</span>
                                    </div>
                                    <span class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5 text-xs">View &rarr;</span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </main>

<?php elseif ($town): ?>
    <!-- ========================================================================= -->
    <!-- 2. SINGLE TOWN PROFILE VIEW                                               -->
    <!-- ========================================================================= -->

    <!-- Schema.org JSON-LD Structured Data for Town -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "City",
      "name": "<?php echo addslashes($tName); ?>",
      "description": "Census 2011 demographic and infrastructure profile for <?php echo addslashes($tName); ?> (<?php echo addslashes($civicStatus); ?>), <?php echo addslashes($dName); ?> District, Bihar.",
      "address": {
        "@type": "PostalAddress",
        "addressRegion": "Bihar",
        "addressCountry": "IN"
      },
      "identifier": "<?php echo $tCode; ?>"
    }
    </script>

    <!-- Town Header Hero Banner -->
    <section class="town-hero-gradient text-white py-4 py-md-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white-50 text-decoration-none">Census 2011</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl(); ?>" class="text-white-50 text-decoration-none">Towns Directory</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getTownUrl($dSlug); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($dName); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($tName); ?></li>
                </ol>
            </nav>

            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-buildings-fill me-1"></i> Town Code: <?php echo htmlspecialchars($tCode); ?>
                        </span>
                        <span class="badge badge-glass-dark px-3 py-1.5 rounded-pill shadow-sm">
                            <?php echo htmlspecialchars($civicStatus); ?>
                        </span>
                        <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            Class <?php echo htmlspecialchars($townClass); ?> Town
                        </span>
                        <?php if ($totalSlums > 0): ?>
                            <a href="#slums-section" class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm text-decoration-none hover-shadow">
                                🛖 <?php echo $totalSlums; ?> Slum Settlements &darr;
                            </a>
                        <?php endif; ?>
                    </div>

                    <h1 class="display-6 fw-bold mb-2 font-heading text-white">
                        🏙️ <?php echo htmlspecialchars($tName); ?>
                    </h1>
                    
                    <p class="lead mb-0 text-white-75 fs-6">
                        Statutory Urban Center located in <strong><?php echo htmlspecialchars($bName); ?></strong> Sub-District / Block, 
                        <strong><?php echo htmlspecialchars($dName); ?></strong> District, Bihar (Census 2011).
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-lg-end">
                        <a href="https://api.whatsapp.com/send?text=<?php echo urlencode("Check {$tName} Town Census Demographics & Slum Data on BiharElection: " . $pageCanonical); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-success fw-bold rounded-pill px-3 py-2 shadow-sm">
                            <i class="bi bi-whatsapp me-1"></i> Share
                        </a>
                        <button onclick="navigator.clipboard.writeText(window.location.href); alert('Link copied to clipboard!');" class="btn btn-outline-light fw-bold rounded-pill px-3 py-2">
                            <i class="bi bi-link-45deg me-1"></i> Copy Link
                        </button>
                        <a href="<?php echo getDistrictUrl($dSlug); ?>" class="btn btn-warning fw-bold text-dark rounded-pill px-3 py-2">
                            <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($dName); ?> District
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content Grid -->
    <main class="container py-4">

        <!-- Top Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Quick Demographic KPI Tiles -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-primary">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Population</span>
                    <h3 class="fw-bold text-navy mb-0 fs-4"><?php echo number_format($pop); ?></h3>
                    <span class="text-xs text-muted">Persons (2011)</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-info">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Households</span>
                    <h3 class="fw-bold text-info mb-0 fs-4"><?php echo number_format($hh); ?></h3>
                    <span class="text-xs text-muted">~<?php echo $avgFamilySize; ?> / Family</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-danger">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Sex Ratio</span>
                    <h3 class="fw-bold text-danger mb-0 fs-4"><?php echo $sr; ?></h3>
                    <span class="text-xs text-muted">Females / 1k Males</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-success">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Urban Area</span>
                    <h3 class="fw-bold text-success mb-0 fs-4"><?php echo number_format($areaSqKm, 2); ?></h3>
                    <span class="text-xs text-muted">Sq. Kilometers</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-warning">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Pop. Density</span>
                    <h3 class="fw-bold text-dark mb-0 fs-4"><?php echo $density > 0 ? number_format($density) : 'N/A'; ?></h3>
                    <span class="text-xs text-muted">Persons / Sq Km</span>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <?php if ($totalSlums > 0): ?>
                    <a href="#slums-section" class="card town-kpi-card p-3 h-100 text-center border-top border-4 text-decoration-none hover-shadow" style="border-top-color: #7928ca !important;">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slums (<?php echo $totalSlums; ?>)</span>
                        <h3 class="fw-bold mb-0 fs-4" style="color: #7928ca;"><?php echo $slumPopPct; ?>%</h3>
                        <span class="text-xs text-primary fw-semibold"><?php echo number_format($totalSlumPop); ?> in Slums &darr;</span>
                    </a>
                <?php else: ?>
                    <div class="card town-kpi-card p-3 h-100 text-center border-top border-4 border-secondary">
                        <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Slums</span>
                        <h3 class="fw-bold text-muted mb-0 fs-4">0</h3>
                        <span class="text-xs text-muted">No Slum Areas</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row g-4 mb-4">
            <!-- Left Column: Detailed Demographics -->
            <div class="col-lg-7">
                <!-- Population & Gender Breakdown -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-pie-chart-fill text-primary me-2"></i>Population &amp; Gender Demographics</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold small">Census 2011</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-primary-subtle text-primary border border-primary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">👨 Male Population</span>
                                    <span class="badge bg-primary text-white"><?php echo $pop > 0 ? round(($male/$pop)*100, 1) : 0; ?>%</span>
                                </div>
                                <div class="fs-4 fw-bold"><?php echo number_format($male); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded-3 bg-danger-subtle text-danger border border-danger-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold">👩 Female Population</span>
                                    <span class="badge bg-danger text-white"><?php echo $pop > 0 ? round(($female/$pop)*100, 1) : 0; ?>%</span>
                                </div>
                                <div class="fs-4 fw-bold"><?php echo number_format($female); ?></div>
                            </div>
                        </div>
                    </div>

                    <!-- Visual Progress Bar for Male/Female Balance -->
                    <div class="mb-2">
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Male Share (<?php echo $pop > 0 ? round(($male/$pop)*100, 1) : 0; ?>%)</span>
                            <span>Female Share (<?php echo $pop > 0 ? round(($female/$pop)*100, 1) : 0; ?>%)</span>
                        </div>
                        <div class="progress rounded-pill" style="height: 10px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: <?php echo $pop > 0 ? ($male/$pop)*100 : 50; ?>%" aria-valuenow="<?php echo $male; ?>" aria-valuemin="0" aria-valuemax="<?php echo $pop; ?>"></div>
                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?php echo $pop > 0 ? ($female/$pop)*100 : 50; ?>%" aria-valuenow="<?php echo $female; ?>" aria-valuemin="0" aria-valuemax="<?php echo $pop; ?>"></div>
                        </div>
                    </div>
                </div>

                <!-- Social & Caste Composition -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-people-fill text-warning me-2"></i>Social &amp; Caste Composition</span>
                        <span class="badge bg-warning-subtle text-dark fw-semibold small">SC / ST Matrix</span>
                    </h4>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>Category</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-end">Male</th>
                                    <th class="text-end">Female</th>
                                    <th class="text-end">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-semibold text-navy">Scheduled Caste (SC)</td>
                                    <td class="text-end fw-bold"><?php echo number_format($scPop); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['sc_male'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['sc_female'] ?? 0)); ?></td>
                                    <td class="text-end text-primary fw-semibold"><?php echo $scPct; ?>%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">Scheduled Tribe (ST)</td>
                                    <td class="text-end fw-bold"><?php echo number_format($stPop); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['st_male'] ?? 0)); ?></td>
                                    <td class="text-end"><?php echo number_format((int)($town['st_female'] ?? 0)); ?></td>
                                    <td class="text-end text-success fw-semibold"><?php echo $stPct; ?>%</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">General / OBC / Others</td>
                                    <td class="text-end fw-bold"><?php echo number_format(max(0, $pop - ($scPop + $stPop))); ?></td>
                                    <td class="text-end"><?php echo number_format(max(0, $male - ((int)($town['sc_male'] ?? 0) + (int)($town['st_male'] ?? 0)))); ?></td>
                                    <td class="text-end"><?php echo number_format(max(0, $female - ((int)($town['sc_female'] ?? 0) + (int)($town['st_female'] ?? 0)))); ?></td>
                                    <td class="text-end text-dark fw-semibold"><?php echo $genObcPct; ?>%</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Education & Academic Institutions -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-mortarboard-fill text-primary me-2"></i>Education &amp; Higher Learning</span>
                        <span class="badge bg-primary-subtle text-primary fw-semibold small">Census 2011</span>
                    </h4>

                    <!-- Higher Learning & Professional Colleges Badges -->
                    <div class="row g-2 mb-3">
                        <?php if ($eduGovtMedCol > 0 || $eduPvtMedCol > 0): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-danger bg-opacity-10 border border-danger-subtle rounded-3 text-center">
                                    <span class="text-xs text-muted d-block">Medical College</span>
                                    <strong class="text-danger fs-6">🏥 <?php echo $eduGovtMedCol + $eduPvtMedCol; ?> College</strong>
                                    <span class="text-xs text-muted d-block"><?php echo $eduGovtMedCol > 0 ? 'Govt.' : 'Private'; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($eduGovtEngCol > 0 || $eduPvtEngCol > 0): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-primary bg-opacity-10 border border-primary-subtle rounded-3 text-center">
                                    <span class="text-xs text-muted d-block">Engineering College</span>
                                    <strong class="text-primary fs-6">⚙️ <?php echo $eduGovtEngCol + $eduPvtEngCol; ?> College</strong>
                                    <span class="text-xs text-muted d-block"><?php echo $eduGovtEngCol > 0 ? 'Govt.' : 'Private'; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($eduGovtPoly > 0 || $eduPvtPoly > 0): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-info bg-opacity-10 border border-info-subtle rounded-3 text-center">
                                    <span class="text-xs text-muted d-block">Polytechnic College</span>
                                    <strong class="text-info fs-6">📐 <?php echo $eduGovtPoly + $eduPvtPoly; ?> Institute</strong>
                                    <span class="text-xs text-muted d-block"><?php echo $eduGovtPoly > 0 ? 'Govt.' : 'Private'; ?></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($eduGovtLaw > 0): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-warning bg-opacity-10 border border-warning-subtle rounded-3 text-center">
                                    <span class="text-xs text-muted d-block">Law College</span>
                                    <strong class="text-dark fs-6">⚖️ <?php echo $eduGovtLaw; ?> Govt. College</strong>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($eduGovtMgmt > 0): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-success bg-opacity-10 border border-success-subtle rounded-3 text-center">
                                    <span class="text-xs text-muted d-block">Management Institute</span>
                                    <strong class="text-success fs-6">📊 <?php echo $eduGovtMgmt; ?> Govt. Institute</strong>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($eduGovtDeg > 0 || $eduPvtDeg > 0): ?>
                            <div class="col-6 col-md-4">
                                <div class="p-2.5 bg-secondary bg-opacity-10 border border-secondary-subtle rounded-3 text-center">
                                    <span class="text-xs text-muted d-block">Degree Colleges</span>
                                    <strong class="text-navy fs-6">🎓 <?php echo $eduGovtDeg + $eduPvtDeg; ?> Colleges</strong>
                                    <span class="text-xs text-muted d-block"><?php echo $eduGovtDeg; ?> Govt • <?php echo $eduPvtDeg; ?> Pvt</span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Schools Breakdown Table -->
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered align-middle mb-0">
                            <thead class="table-light text-muted small">
                                <tr>
                                    <th>School Level</th>
                                    <th class="text-center">Government</th>
                                    <th class="text-center">Private</th>
                                    <th class="text-end">Total Schools</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td class="fw-semibold text-navy">Senior Secondary (10+2)</td>
                                    <td class="text-center"><?php echo $eduGovtSrSec; ?></td>
                                    <td class="text-center"><?php echo $eduPvtSrSec; ?></td>
                                    <td class="text-end fw-bold text-primary"><?php echo $eduGovtSrSec + $eduPvtSrSec; ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">Secondary (High School)</td>
                                    <td class="text-center"><?php echo $eduGovtSec; ?></td>
                                    <td class="text-center"><?php echo $eduPvtSec; ?></td>
                                    <td class="text-end fw-bold text-primary"><?php echo $eduGovtSec + $eduPvtSec; ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">Middle Schools</td>
                                    <td class="text-center"><?php echo $eduGovtMid; ?></td>
                                    <td class="text-center"><?php echo $eduPvtMid; ?></td>
                                    <td class="text-end fw-bold text-primary"><?php echo $eduGovtMid + $eduPvtMid; ?></td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold text-navy">Primary Schools</td>
                                    <td class="text-center"><?php echo $eduGovtPri; ?></td>
                                    <td class="text-center"><?php echo $eduPvtPri; ?></td>
                                    <td class="text-end fw-bold text-primary"><?php echo $eduGovtPri + $eduPvtPri; ?></td>
                                </tr>
                                <?php if ($eduGovtDis > 0 || $eduPvtDis > 0): ?>
                                    <tr>
                                        <td class="fw-semibold text-success">Special School for Disabled</td>
                                        <td class="text-center"><?php echo $eduGovtDis; ?></td>
                                        <td class="text-center"><?php echo $eduPvtDis; ?></td>
                                        <td class="text-end fw-bold text-success"><?php echo $eduGovtDis + $eduPvtDis; ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Healthcare & Hospitals Infrastructure -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-heart-pulse-fill text-danger me-2"></i>Healthcare &amp; Hospital Facilities</span>
                        <span class="badge bg-danger-subtle text-danger fw-semibold small">Medical Grid</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Allopathic Hospitals</span>
                                <h4 class="fw-bold text-danger mb-0"><?php echo $medAlloHosp; ?></h4>
                                <span class="text-xs text-muted"><?php echo $medAlloBeds; ?> Total Beds</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Alternative Medicine</span>
                                <h4 class="fw-bold text-success mb-0"><?php echo $medAltHosp; ?></h4>
                                <span class="text-xs text-muted"><?php echo $medAltBeds; ?> Total Beds</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Nursing Homes</span>
                                <h4 class="fw-bold text-primary mb-0"><?php echo $medNursing; ?></h4>
                                <span class="text-xs text-muted"><?php echo $medNursingBeds; ?> Total Beds</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Medical Staff</span>
                                <h4 class="fw-bold text-dark mb-0"><?php echo $medAlloDocs + $medAltDocs; ?></h4>
                                <span class="text-xs text-muted">Doctors In Position</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Para-Medical Staff</span>
                                <h4 class="fw-bold text-info mb-0"><?php echo $medAlloStaff + $medAltStaff; ?></h4>
                                <span class="text-xs text-muted">Nurses &amp; Technicians</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Medicine Shops</span>
                                <h4 class="fw-bold text-success mb-0"><?php echo number_format($medShops); ?></h4>
                                <span class="text-xs text-muted">Licensed Pharmacies</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($medTbHosp > 0 || $medVetHosp > 0 || $medCharitable > 0): ?>
                        <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                            <?php if ($medTbHosp > 0): ?>
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 small">
                                    🫁 <?php echo $medTbHosp; ?> T.B. Clinic (<?php echo $medTbBeds; ?> Beds)
                                </span>
                            <?php endif; ?>
                            <?php if ($medVetHosp > 0): ?>
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 small">
                                    🐾 <?php echo $medVetHosp; ?> Veterinary Hospital
                                </span>
                            <?php endif; ?>
                            <?php if ($medCharitable > 0): ?>
                                <span class="badge bg-light text-dark border px-2.5 py-1.5 small">
                                    🤝 <?php echo $medCharitable; ?> Charitable Hospitals
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Historical Population Progression (1901 - 2011) -->
                <?php if (!empty($historicalPop)): ?>
                    <div class="card town-glass-card p-4 mb-4">
                        <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-graph-up-arrow text-info me-2"></i>Historical Population Progression (1901–2011)</span>
                            <span class="badge bg-info-subtle text-info fw-semibold small">110-Year Trend</span>
                        </h4>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover align-middle mb-0 text-center">
                                <thead class="table-light text-muted small">
                                    <tr>
                                        <th>Census Year</th>
                                        <th class="text-end">Population</th>
                                        <th class="text-end">Decadal Growth</th>
                                        <th>Trend</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <?php foreach ($historicalPop as $hp): 
                                        $gr = $hp['growth_rate'];
                                    ?>
                                        <tr>
                                            <td class="fw-bold text-navy"><?php echo $hp['year']; ?></td>
                                            <td class="text-end fw-semibold"><?php echo number_format($hp['population']); ?></td>
                                            <td class="text-end">
                                                <?php if ($gr !== null): ?>
                                                    <span class="fw-bold <?php echo $gr >= 0 ? 'text-success' : 'text-danger'; ?>">
                                                        <?php echo ($gr >= 0 ? '+' : '') . $gr . '%'; ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($gr !== null): ?>
                                                    <?php if ($gr >= 25): ?>
                                                        <span class="badge bg-success-subtle text-success small">High Surge 🚀</span>
                                                    <?php elseif ($gr > 0): ?>
                                                        <span class="badge bg-primary-subtle text-primary small">Steady Growth 📈</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger-subtle text-danger small">Decline 📉</span>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <span class="badge bg-light text-muted small">Base Year</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Economic, Banking, Credit Societies & Manufacturing -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                        <span><i class="bi bi-cash-coin text-success me-2"></i>Economy, Banking &amp; Credit Societies</span>
                        <span class="badge bg-success-subtle text-success fw-semibold small">Financial Network</span>
                    </h4>

                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Nationalised Banks</span>
                                <h4 class="fw-bold text-primary mb-0"><?php echo $natBanks; ?></h4>
                                <span class="text-xs text-muted">Branches</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Commercial Banks</span>
                                <h4 class="fw-bold text-info mb-0"><?php echo $comBanks; ?></h4>
                                <span class="text-xs text-muted">Branches</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3 text-center border h-100">
                                <span class="text-muted small d-block mb-1">Cooperative Banks</span>
                                <h4 class="fw-bold text-warning mb-0"><?php echo $coopBanks; ?></h4>
                                <span class="text-xs text-muted">Branches</span>
                            </div>
                        </div>
                    </div>

                    <?php if ($agrCreditSoc > 0 || $nonAgrCreditSoc > 0): ?>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-2.5 rounded-3 bg-light border text-center">
                                    <span class="text-xs text-muted d-block">Agricultural Credit Societies</span>
                                    <strong class="text-navy fs-6">🌾 <?php echo $agrCreditSoc; ?> Societies</strong>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2.5 rounded-3 bg-light border text-center">
                                    <span class="text-xs text-muted d-block">Non-Agricultural Credit Societies</span>
                                    <strong class="text-navy fs-6">💼 <?php echo $nonAgrCreditSoc; ?> Societies</strong>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($manufacturedItems)): ?>
                        <div class="p-3 rounded-3 bg-warning-subtle border border-warning-subtle">
                            <div class="fw-bold text-dark mb-2">
                                <i class="bi bi-gear-wide-connected me-1 text-warning"></i> Famous Manufactured Commodities &amp; Specialties in <?php echo htmlspecialchars($tName); ?>:
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($manufacturedItems as $idx => $mItem): ?>
                                    <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-xs fs-7">
                                        🏭 <?php echo htmlspecialchars($mItem); ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Civic Amenities, Culture & Recreation -->
                <?php if ($recStadium > 0 || $recCinema > 0 || $recAuditorium > 0 || $recLibrary > 0 || $recReadingRoom > 0 || !empty($rainfallMm)): ?>
                    <div class="card town-glass-card p-4 mb-4">
                        <h4 class="fw-bold text-navy font-heading fs-5 mb-3 d-flex align-items-center justify-content-between">
                            <span><i class="bi bi-palette-fill text-warning me-2"></i>Civic Amenities, Culture &amp; Climate</span>
                            <span class="badge bg-warning-subtle text-dark fw-semibold small">Urban Living</span>
                        </h4>

                        <div class="row g-3">
                            <?php if ($recStadium > 0): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-2.5 bg-light rounded-3 text-center border">
                                        <span class="text-xs text-muted d-block">Sports Stadium</span>
                                        <strong class="text-success fs-6">🏟️ <?php echo $recStadium; ?> Stadium</strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($recCinema > 0): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-2.5 bg-light rounded-3 text-center border">
                                        <span class="text-xs text-muted d-block">Cinema Theatres</span>
                                        <strong class="text-primary fs-6">🎬 <?php echo $recCinema; ?> Theatres</strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($recAuditorium > 0): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-2.5 bg-light rounded-3 text-center border">
                                        <span class="text-xs text-muted d-block">Community Halls</span>
                                        <strong class="text-navy fs-6">🏛️ <?php echo $recAuditorium; ?> Halls</strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($recLibrary > 0): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-2.5 bg-light rounded-3 text-center border">
                                        <span class="text-xs text-muted d-block">Public Library</span>
                                        <strong class="text-info fs-6">📚 <?php echo $recLibrary; ?> Library</strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($recReadingRoom > 0): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-2.5 bg-light rounded-3 text-center border">
                                        <span class="text-xs text-muted d-block">Public Reading Room</span>
                                        <strong class="text-dark fs-6">📖 <?php echo $recReadingRoom; ?> Room</strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($rainfallMm)): ?>
                                <div class="col-6 col-md-4">
                                    <div class="p-2.5 bg-light rounded-3 text-center border">
                                        <span class="text-xs text-muted d-block">Annual Rainfall</span>
                                        <strong class="text-info fs-6">🌧️ <?php echo $rainfallMm; ?> mm</strong>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right Column: Administrative & Slum Summary -->
            <div class="col-lg-5">
                <!-- Administrative Jurisdiction Card -->
                <div class="card town-glass-card p-4 mb-4">
                    <h4 class="fw-bold text-navy font-heading fs-5 mb-3">
                        <i class="bi bi-geo-alt-fill text-danger me-2"></i>Administrative Hierarchy
                    </h4>

                    <div class="list-group list-group-flush small">
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">State:</span>
                            <span class="fw-bold text-navy">Bihar (Code: 10)</span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">District:</span>
                            <a href="<?php echo getDistrictUrl($dSlug); ?>" class="fw-bold text-primary text-decoration-none">
                                <?php echo htmlspecialchars($dName); ?> &rarr;
                            </a>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">CD Block / Sub-District:</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars($bName); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Civic Administrative Status:</span>
                            <span class="badge bg-primary text-white"><?php echo htmlspecialchars($civicStatus); ?></span>
                        </div>
                        <div class="list-group-item px-0 py-2.5 d-flex justify-content-between align-items-center">
                            <span class="text-muted">Town Size Classification:</span>
                            <span class="badge bg-secondary text-white">Class <?php echo htmlspecialchars($townClass); ?></span>
                        </div>
                    </div>

                    <div class="mt-3 pt-3 border-top d-flex flex-column gap-2">
                        <a href="<?php echo getTownUrl($dSlug); ?>" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-semibold">
                            🏙️ All <?php echo htmlspecialchars($dName); ?> Urban Towns &rarr;
                        </a>
                        <a href="<?php echo getCensusUrl($dSlug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill w-100 fw-semibold">
                            📊 <?php echo htmlspecialchars($dName); ?> District Census Hub
                        </a>
                    </div>
                </div>

                <!-- Slum Summary Box -->
                <div class="card town-glass-card p-4 mb-4 border-top border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                                Slum Directory
                            </span>
                            <h4 class="fw-bold text-navy font-heading fs-5 mb-0">
                                🛖 Slum Demographics
                            </h4>
                        </div>
                        <span class="fs-4">📊</span>
                    </div>

                    <?php if ($totalSlums > 0): ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-2 text-center">
                                <div class="col-4 border-end">
                                    <span class="text-xs text-muted d-block">Slums</span>
                                    <div class="fw-bold text-navy fs-5"><?php echo $totalSlums; ?></div>
                                </div>
                                <div class="col-4 border-end">
                                    <span class="text-xs text-muted d-block">Population</span>
                                    <div class="fw-bold text-danger fs-5"><?php echo number_format($totalSlumPop); ?></div>
                                </div>
                                <div class="col-4">
                                    <span class="text-xs text-muted d-block">Households</span>
                                    <div class="fw-bold text-info fs-5"><?php echo number_format($totalSlumHh); ?></div>
                                </div>
                            </div>
                        </div>
                        <p class="small text-muted mb-0">
                            <strong><?php echo $slumPopPct; ?>%</strong> of the total population in <?php echo htmlspecialchars($tName); ?> resides in designated slum settlements (Release 1000).
                        </p>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-3 text-center py-4 border text-muted">
                            <i class="bi bi-shield-check text-success fs-1 d-block mb-1"></i>
                            <div class="fw-bold text-dark">No Slum Clusters Reported</div>
                            <span class="text-xs">No notified or non-notified slum clusters in official Census 2011 Slum_1000 release for this town.</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- DETAILED SLUMS ANALYTICS & INFRASTRUCTURE SECTION                         -->
        <!-- ========================================================================= -->
        <?php if (!empty($slums)): 
            $notifiedCount = 0;
            $nonNotifiedCount = 0;
            $withWaterCount = 0;
            $withRoadsCount = 0;
            foreach ($slums as $s) {
                if ((int)$s['is_notified'] === 1) $notifiedCount++;
                else $nonNotifiedCount++;
                if ((int)$s['tap_points_water'] > 0) $withWaterCount++;
                if ((float)$s['paved_roads_km'] > 0) $withRoadsCount++;
            }
        ?>
            <div class="card town-glass-card p-4 mb-4 border-top border-4 border-warning" id="slums-section" style="scroll-margin-top: 80px;">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            DH 2011 DCHB Town Release 1000
                        </span>
                        <h3 class="fw-bold text-navy font-heading mb-0 fs-5">
                            🛖 Slum Settlements &amp; Wards in <?php echo htmlspecialchars($tName); ?> (<?php echo count($slums); ?>)
                        </h3>
                        <p class="text-muted small mb-0 mt-1">Click on any of the <?php echo count($slums); ?> slums below to view full micro-sanitation, water, road and electrical metrics.</p>
                    </div>
                    <?php if (count($slums) > 3): ?>
                        <div class="input-group input-group-sm" style="max-width: 280px;">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" id="slumTableFilter" class="form-control form-control-sm" placeholder="Search among <?php echo count($slums); ?> slums / wards...">
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Quick Filter Chips -->
                <?php if (count($slums) > 5): ?>
                    <div class="d-flex flex-wrap gap-2 mb-3 pb-2 border-bottom">
                        <button type="button" class="btn btn-xs btn-outline-dark slum-chip-btn active slum-filter-btn" data-filter="all">
                            All Slums (<?php echo count($slums); ?>)
                        </button>
                        <?php if ($notifiedCount > 0): ?>
                            <button type="button" class="btn btn-xs btn-outline-success slum-chip-btn slum-filter-btn" data-filter="notified">
                                Notified (<?php echo $notifiedCount; ?>)
                            </button>
                        <?php endif; ?>
                        <button type="button" class="btn btn-xs btn-outline-secondary slum-chip-btn slum-filter-btn" data-filter="non-notified">
                            Non-Notified (<?php echo $nonNotifiedCount; ?>)
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-info slum-chip-btn slum-filter-btn" data-filter="water">
                            🚰 With Tap Water (<?php echo $withWaterCount; ?>)
                        </button>
                        <button type="button" class="btn btn-xs btn-outline-warning slum-chip-btn text-dark slum-filter-btn" data-filter="roads">
                            🛣️ With Paved Roads (<?php echo $withRoadsCount; ?>)
                        </button>
                    </div>
                <?php endif; ?>

                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-slum align-middle mb-0" id="slumsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Slum / Ward Name</th>
                                <th>Status</th>
                                <th class="text-end">Households</th>
                                <th class="text-end">Population</th>
                                <th>Paved Roads</th>
                                <th>Drainage</th>
                                <th>Sanitation / Latrines</th>
                                <th>Tap Water</th>
                                <th>Domestic Elec.</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($slums as $idx => $s): 
                                $sName = $s['slum_name'];
                                $isNotified = ((int)$s['is_notified'] === 1);
                                $sHh = (int)($s['households'] ?? 0);
                                $sPop = (int)($s['slum_population'] ?? 0);
                                $pavedKm = (float)($s['paved_roads_km'] ?? 0);
                                $drain = $s['drainage_system'] ?: 'N/A';
                                $tapWater = (int)($s['tap_points_water'] ?? 0);
                                $elecDom = (int)($s['electricity_domestic'] ?? 0);
                                $slumProfileUrl = getSlumUrl($dSlug, $town['town_slug'], $s['slum_slug'] ?: $s['id']);

                                $latPit = (int)($s['latrines_pit'] ?? 0);
                                $latFlush = (int)($s['latrines_flush'] ?? 0);
                                $latService = (int)($s['latrines_service'] ?? 0);
                                $latOthers = (int)($s['latrines_others'] ?? 0);
                                $latComm = (int)($s['latrines_community'] ?? 0);
                                $totalLatrines = $latPit + $latFlush + $latService + $latOthers + $latComm;

                                $jsonData = htmlspecialchars(json_encode([
                                    'id' => $s['id'],
                                    'name' => $sName,
                                    'town' => $tName,
                                    'district' => $dName,
                                    'status' => $isNotified ? 'Notified by Government' : 'Non-Notified Settlement',
                                    'is_notified' => $isNotified,
                                    'pop' => number_format($sPop),
                                    'hh' => number_format($sHh),
                                    'roads' => $pavedKm,
                                    'drainage' => $drain,
                                    'lat_flush' => $latFlush,
                                    'lat_pit' => $latPit,
                                    'lat_comm' => $latComm,
                                    'lat_service' => $latService,
                                    'lat_others' => $latOthers,
                                    'lat_total' => $totalLatrines,
                                    'water' => $tapWater,
                                    'elec_dom' => $elecDom,
                                    'elec_road' => (int)($s['electricity_road_light'] ?? 0),
                                    'elec_others' => (int)($s['electricity_others'] ?? 0),
                                    'url' => $slumProfileUrl
                                ]), ENT_QUOTES, 'UTF-8');
                            ?>
                                <tr class="slum-row" 
                                    data-notified="<?php echo $isNotified ? '1' : '0'; ?>"
                                    data-water="<?php echo $tapWater > 0 ? '1' : '0'; ?>"
                                    data-roads="<?php echo $pavedKm > 0 ? '1' : '0'; ?>"
                                    data-slum='<?php echo $jsonData; ?>'
                                    style="cursor: pointer;">
                                    <td class="text-muted small"><?php echo $idx + 1; ?></td>
                                    <td>
                                        <div class="fw-bold text-navy slum-name-cell">
                                            🛖 <?php echo htmlspecialchars($sName); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($isNotified): ?>
                                            <span class="badge bg-success-subtle text-success small">Notified</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary small">Non-Notified</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end fw-semibold"><?php echo number_format($sHh); ?></td>
                                    <td class="text-end fw-bold text-danger"><?php echo number_format($sPop); ?></td>
                                    <td>
                                        <?php if ($pavedKm > 0): ?>
                                            <span class="badge bg-light text-dark border"><?php echo $pavedKm; ?> km</span>
                                        <?php else: ?>
                                            <span class="text-muted small">0 km</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border small"><?php echo htmlspecialchars($drain); ?></span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="fw-semibold text-dark"><?php echo $totalLatrines; ?> Total</span>
                                            <?php if ($latFlush > 0): ?>
                                                <span class="text-xs text-muted d-block">Flush: <?php echo $latFlush; ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($tapWater > 0): ?>
                                            <span class="badge bg-info-subtle text-info small">🚰 <?php echo $tapWater; ?> Taps</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($elecDom > 0): ?>
                                            <span class="badge bg-warning-subtle text-dark small">⚡ <?php echo $elecDom; ?> Conn.</span>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 text-xs fw-semibold open-slum-modal-btn" title="View Quick Details">
                                                <i class="bi bi-eye-fill me-1"></i>Info
                                            </button>
                                            <a href="<?php echo htmlspecialchars($slumProfileUrl); ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-1 text-xs" title="Open Dedicated Ward Profile Page" onclick="event.stopPropagation();">
                                                &rarr;
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Interactive Slum Detail Modal -->
            <div class="modal fade" id="slumDetailModal" tabindex="-1" aria-labelledby="slumModalTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content rounded-4 border-0 shadow">
                        <div class="modal-header bg-warning bg-opacity-25 border-bottom border-warning">
                            <div>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-0.5 rounded-pill small mb-1" id="mSlumStatus">Notified</span>
                                <h5 class="modal-title fw-bold text-navy font-heading" id="slumModalTitle">Slum / Ward Details</h5>
                                <span class="text-xs text-muted" id="mSlumParent">Parent Town</span>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-4">
                            <!-- Quick stats row -->
                            <div class="row g-3 text-center mb-4">
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <span class="text-xs text-muted d-block mb-1">Slum Population</span>
                                        <div class="fw-bold fs-4 text-danger" id="mSlumPop">0</div>
                                        <span class="text-xs text-muted">Persons</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <span class="text-xs text-muted d-block mb-1">Households</span>
                                        <div class="fw-bold fs-4 text-info" id="mSlumHh">0</div>
                                        <span class="text-xs text-muted">Families</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <span class="text-xs text-muted d-block mb-1">Paved Roads</span>
                                        <div class="fw-bold fs-4 text-success" id="mSlumRoads">0 km</div>
                                        <span class="text-xs text-muted">Internal Connectivity</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Detailed Infrastructure -->
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h6 class="fw-bold text-navy mb-2"><i class="bi bi-droplet-fill text-primary me-1"></i> Sanitation &amp; Latrines</h6>
                                        <div class="small">
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Flush Latrines:</span>
                                                <strong id="mLatFlush">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Pit Latrines:</span>
                                                <strong id="mLatPit">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Community Latrines:</span>
                                                <strong id="mLatComm">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Service Latrines:</span>
                                                <strong id="mLatService">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1">
                                                <span class="text-muted">Total Latrines:</span>
                                                <strong class="text-primary fs-6" id="mLatTotal">0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 border h-100">
                                        <h6 class="fw-bold text-navy mb-2"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Water &amp; Electricity</h6>
                                        <div class="small">
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Drainage System:</span>
                                                <strong id="mDrainage">None</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Public Tap Water Points:</span>
                                                <strong class="text-info fs-6" id="mWater">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1 border-bottom">
                                                <span class="text-muted">Domestic Power:</span>
                                                <strong id="mElecDom">0</strong>
                                            </div>
                                            <div class="d-flex justify-content-between py-1">
                                                <span class="text-muted">Street Lighting:</span>
                                                <strong id="mElecRoad">0</strong>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                            <a href="#" id="mSlumProfileLink" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                Open Dedicated Profile &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            function initSlumInteractivity() {
                // Table Live Search
                var filterInput = document.getElementById('slumTableFilter');
                if (filterInput) {
                    filterInput.addEventListener('input', function() {
                        var q = this.value.toLowerCase().trim();
                        var rows = document.querySelectorAll('.slum-row');
                        rows.forEach(function(row) {
                            var text = row.querySelector('.slum-name-cell').textContent.toLowerCase();
                            if (text.includes(q)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    });
                }

                // Filter Buttons
                var filterBtns = document.querySelectorAll('.slum-filter-btn');
                filterBtns.forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        filterBtns.forEach(function(b) { b.classList.remove('active'); });
                        this.classList.add('active');
                        var fType = this.getAttribute('data-filter');
                        var rows = document.querySelectorAll('.slum-row');
                        rows.forEach(function(row) {
                            if (fType === 'all') {
                                row.style.display = '';
                            } else if (fType === 'notified') {
                                row.style.display = (row.getAttribute('data-notified') === '1') ? '' : 'none';
                            } else if (fType === 'non-notified') {
                                row.style.display = (row.getAttribute('data-notified') === '0') ? '' : 'none';
                            } else if (fType === 'water') {
                                row.style.display = (row.getAttribute('data-water') === '1') ? '' : 'none';
                            } else if (fType === 'roads') {
                                row.style.display = (row.getAttribute('data-roads') === '1') ? '' : 'none';
                            }
                        });
                    });
                });

                // Function to open Modal with slum data
                window.showSlumModalData = function(slumElement) {
                    var rawData = slumElement.getAttribute('data-slum');
                    if (!rawData) return;
                    var d = JSON.parse(rawData);

                    document.getElementById('slumModalTitle').textContent = '🛖 ' + d.name;
                    document.getElementById('mSlumStatus').textContent = d.status;
                    document.getElementById('mSlumStatus').className = 'badge ' + (d.is_notified ? 'bg-success text-white' : 'bg-secondary text-white') + ' fw-bold px-2 py-0.5 rounded-pill small mb-1';
                    document.getElementById('mSlumParent').textContent = d.town + ', ' + d.district + ' District';
                    document.getElementById('mSlumPop').textContent = d.pop;
                    document.getElementById('mSlumHh').textContent = d.hh;
                    document.getElementById('mSlumRoads').textContent = d.roads + ' km';
                    document.getElementById('mLatFlush').textContent = d.lat_flush;
                    document.getElementById('mLatPit').textContent = d.lat_pit;
                    document.getElementById('mLatComm').textContent = d.lat_comm;
                    document.getElementById('mLatService').textContent = d.lat_service;
                    document.getElementById('mLatTotal').textContent = d.lat_total;
                    document.getElementById('mDrainage').textContent = d.drainage;
                    document.getElementById('mWater').textContent = d.water + ' Taps';
                    document.getElementById('mElecDom').textContent = d.elec_dom;
                    document.getElementById('mElecRoad').textContent = d.elec_road;
                    document.getElementById('mSlumProfileLink').setAttribute('href', d.url);

                    var modalEl = document.getElementById('slumDetailModal');
                    if (window.bootstrap && bootstrap.Modal) {
                        var modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl);
                        modalInstance.show();
                    }
                };

                // Row click listeners
                document.querySelectorAll('.slum-row').forEach(function(row) {
                    row.addEventListener('click', function(e) {
                        // If user clicked on a link or button directly, don't double trigger
                        if (e.target.closest('a')) return;
                        window.showSlumModalData(this);
                    });
                });

                // Button click listeners
                document.querySelectorAll('.open-slum-modal-btn').forEach(function(btn) {
                    btn.addEventListener('click', function(e) {
                        e.stopPropagation();
                        var row = this.closest('.slum-row');
                        if (row) {
                            window.showSlumModalData(row);
                        }
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initSlumInteractivity);
            } else {
                initSlumInteractivity();
            }
            </script>
        <?php endif; ?>

        <!-- Historical 1991 Urban Census vs 2011 Trajectory Section -->
        <?php if ($town1991): 
            $pop91 = (int)($town1991['population'] ?? 0);
            $hh91 = (int)($town1991['households'] ?? 0);
            $male91 = (int)($town1991['male'] ?? 0);
            $female91 = (int)($town1991['female'] ?? 0);
            $sr91 = ($male91 > 0) ? round(($female91 / $male91) * 1000) : 0;
            $lit91 = (int)($town1991['literates'] ?? 0);
            $litPct91 = ($pop91 > 0) ? round(($lit91 / $pop91) * 100, 1) : 0;
            $sc91 = (int)($town1991['sc_population'] ?? 0);
            $st91 = (int)($town1991['st_population'] ?? 0);
            $growthPop = ($pop91 > 0 && $pop > 0) ? round((($pop - $pop91) / $pop91) * 100, 1) : null;
            $workers91 = (int)($town1991['workers'] ?? 0);
            $trade91 = (int)($town1991['trade'] ?? 0);
            $trans91 = (int)($town1991['transport'] ?? 0);
            $ind91 = (int)($town1991['household_ind'] + $town1991['non_household_ind']);
        ?>
        <section class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4 border-top border-4 border-warning">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                <div>
                    <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                        📜 Historical Urban Census 1991 Archive
                    </span>
                    <h3 class="h5 fw-bold mb-0 text-navy font-heading">
                        1991 vs 2011 Urban Trajectory: <?php echo htmlspecialchars($tName); ?> (<?php echo htmlspecialchars($town1991['civic_status'] ?: 'Urban'); ?>)
                    </h3>
                    <p class="small text-muted mb-0 mt-1">
                        Official Primary Census Abstract (PCA Urban 1991) baseline demographic, ward breakdown (<?php echo $town1991['ward_count']; ?> Wards), &amp; occupational comparison.
                    </p>
                </div>
                <?php if ($growthPop !== null): ?>
                    <span class="badge <?php echo ($growthPop >= 0) ? 'bg-success' : 'bg-danger'; ?> fs-6 px-3 py-2 rounded-pill shadow-sm">
                        <i class="bi bi-graph-up-arrow me-1"></i> <?php echo ($growthPop >= 0 ? '+' : '') . $growthPop; ?>% Growth (1991–2011)
                    </span>
                <?php endif; ?>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <span class="small text-muted d-block">1991 Population</span>
                        <h4 class="h5 fw-bold text-navy mb-0 font-monospace"><?php echo number_format($pop91); ?></h4>
                        <small class="text-muted">M: <?php echo number_format($male91); ?> | F: <?php echo number_format($female91); ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <span class="small text-muted d-block">1991 Households</span>
                        <h4 class="h5 fw-bold text-warning mb-0 font-monospace"><?php echo number_format($hh91); ?></h4>
                        <small class="text-muted"><?php echo $town1991['ward_count']; ?> Wards / OG</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <span class="small text-muted d-block">1991 Literacy Rate</span>
                        <h4 class="h5 fw-bold text-primary mb-0"><?php echo $litPct91; ?>%</h4>
                        <small class="text-muted"><?php echo number_format($lit91); ?> Literates</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 text-center border">
                        <span class="small text-muted d-block">1991 Sex Ratio</span>
                        <h4 class="h5 fw-bold text-success mb-0"><?php echo $sr91; ?></h4>
                        <small class="text-muted">Females / 1000 Males</small>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="table-responsive mb-3">
                <table class="table table-bordered align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Demographic &amp; Economic Indicator</th>
                            <th class="text-end">1991 Urban Census</th>
                            <th class="text-end">2011 Urban Census</th>
                            <th class="text-center">20-Year Shift</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="fw-bold">Total Population</td>
                            <td class="text-end font-monospace"><?php echo number_format($pop91); ?></td>
                            <td class="text-end font-monospace fw-bold text-primary"><?php echo number_format($pop); ?></td>
                            <td class="text-center fw-bold <?php echo ($growthPop >= 0) ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($growthPop !== null) ? (($growthPop >= 0 ? '+' : '') . $growthPop . '%') : 'N/A'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Total Households</td>
                            <td class="text-end font-monospace"><?php echo number_format($hh91); ?></td>
                            <td class="text-end font-monospace"><?php echo number_format($hh); ?></td>
                            <td class="text-center text-muted font-monospace">
                                <?php echo ($hh91 > 0) ? (($hh >= $hh91 ? '+' : '') . number_format($hh - $hh91)) : '-'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Scheduled Caste (SC)</td>
                            <td class="text-end font-monospace"><?php echo number_format($sc91); ?></td>
                            <td class="text-end font-monospace"><?php echo number_format($scPop); ?></td>
                            <td class="text-center text-muted">
                                <?php echo ($pop91 > 0) ? round(($sc91 / $pop91) * 100, 1) . '% &rarr; ' . $scPct . '%' : '-'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>Scheduled Tribe (ST)</td>
                            <td class="text-end font-monospace"><?php echo number_format($st91); ?></td>
                            <td class="text-end font-monospace"><?php echo number_format($stPop); ?></td>
                            <td class="text-center text-muted">
                                <?php echo ($pop91 > 0) ? round(($st91 / $pop91) * 100, 1) . '% &rarr; ' . $stPct . '%' : '-'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td>1991 Key Urban Economic Sectors</td>
                            <td class="text-end font-monospace" colspan="2">
                                Trade &amp; Commerce: <strong><?php echo number_format($trade91); ?></strong> | Transport &amp; Comm.: <strong><?php echo number_format($trans91); ?></strong> | Manufacturing: <strong><?php echo number_format($ind91); ?></strong>
                            </td>
                            <td class="text-center text-muted">Total Workers: <?php echo number_format($workers91); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- 1991 Wards Roster -->
            <?php if (!empty($town1991['wards']) && count($town1991['wards']) > 1): ?>
                <details class="mt-3">
                    <summary class="fw-bold text-navy small cursor-pointer py-1">
                        <i class="bi bi-chevron-down me-1"></i> View All <?php echo count($town1991['wards']); ?> Wards in 1991 Census Roster
                    </summary>
                    <div class="table-responsive mt-2" style="max-height: 320px; overflow-y: auto;">
                        <table class="table table-hover table-sm align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>Ward / Area Name</th>
                                    <th>Ward Code</th>
                                    <th class="text-end">Population</th>
                                    <th class="text-end">Male / Female</th>
                                    <th class="text-end">Households</th>
                                    <th class="text-end">SC / ST</th>
                                    <th class="text-end">Literates</th>
                                    <th class="text-end">Workers</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($town1991['wards'] as $tw): ?>
                                    <tr>
                                        <td class="fw-bold text-dark"><?php echo htmlspecialchars($tw['name']); ?></td>
                                        <td><code><?php echo htmlspecialchars($tw['ward_code']); ?></code></td>
                                        <td class="text-end fw-semibold text-navy"><?php echo number_format((int)$tw['population']); ?></td>
                                        <td class="text-end"><?php echo number_format((int)$tw['male']); ?> / <?php echo number_format((int)$tw['female']); ?></td>
                                        <td class="text-end"><?php echo number_format((int)$tw['households']); ?></td>
                                        <td class="text-end"><?php echo number_format((int)$tw['sc_population']); ?> / <?php echo number_format((int)$tw['st_population']); ?></td>
                                        <td class="text-end"><?php echo number_format((int)$tw['literates_total']); ?></td>
                                        <td class="text-end"><?php echo number_format((int)$tw['workers_total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </details>
            <?php endif; ?>
        </section>
        <?php endif; ?>

        <!-- Sibling Towns in Same District Navigation -->
        <?php if (!empty($nearbyTowns)): ?>
            <div class="card town-glass-card p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            District Urban Centers
                        </span>
                        <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                            Other Towns in <?php echo htmlspecialchars($dName); ?> District (<?php echo count($nearbyTowns); ?>)
                        </h4>
                    </div>
                    <a href="<?php echo getTownUrl($dSlug); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        All <?php echo htmlspecialchars($dName); ?> Towns &rarr;
                    </a>
                </div>

                <div class="row g-3">
                    <?php foreach ($nearbyTowns as $nt): 
                        $ntUrl = getTownUrl($nt['district_slug'], $nt['town_slug']);
                        $ntPop = (int)($nt['population'] ?? 0);
                        $ntCivic = $nt['civic_status'] ?? 'Town';
                        $ntSlums = (int)($nt['slum_count'] ?? 0);
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between hover-shadow transition">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-primary-subtle text-primary small font-monospace">Code: <?php echo htmlspecialchars($nt['town_code']); ?></span>
                                        <span class="badge bg-secondary-subtle text-secondary small"><?php echo htmlspecialchars($ntCivic); ?></span>
                                    </div>
                                    <h5 class="fw-bold text-navy mb-1 fs-6">
                                        <a href="<?php echo htmlspecialchars($ntUrl); ?>" class="text-decoration-none text-navy stretched-link-target">
                                            🏙️ <?php echo htmlspecialchars($nt['town_name']); ?>
                                        </a>
                                    </h5>
                                    <div class="small text-muted mb-2">
                                        <span>👥 <?php echo number_format($ntPop); ?> Pop.</span> • 
                                        <span>Class <?php echo htmlspecialchars($nt['town_class'] ?: '-'); ?></span>
                                        <?php if ($ntSlums > 0): ?>
                                            • <span class="text-danger fw-semibold">🛖 <?php echo $ntSlums; ?> Slums</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="pt-2 border-top">
                                    <a href="<?php echo htmlspecialchars($ntUrl); ?>" class="btn btn-sm btn-primary rounded-pill w-100 fw-semibold">
                                        View Town Profile &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    </main>

<?php else: ?>
    <!-- ========================================================================= -->
    <!-- 3. BIHAR CENSUS TOWNS & SLUMS DIRECTORY VIEW                              -->
    <!-- ========================================================================= -->

    <!-- Directory Hero Banner -->
    <section class="town-hero-gradient text-white py-4 py-md-5">
        <div class="container">
            <div class="row align-items-center g-4">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb mb-0">
                            <li class="breadcrumb-item"><a href="<?php echo getCensusUrl(); ?>" class="text-white-50 text-decoration-none">Census 2011</a></li>
                            <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Towns &amp; Slums Directory</li>
                        </ol>
                    </nav>

                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <i class="bi bi-buildings-fill me-1"></i> 198 Statutory &amp; Census Towns
                        </span>
                        <span class="badge badge-glass-dark px-3 py-1.5 rounded-pill shadow-sm">
                            670 Slum Settlements (Release 1000)
                        </span>
                        <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            38 Districts Matrix
                        </span>
                    </div>

                    <h1 class="display-6 fw-bold mb-2 font-heading text-white">
                        🏙️ Bihar Census 2011 Towns &amp; Slum Settlements Directory
                    </h1>
                    
                    <p class="lead mb-0 text-white-75 fs-6">
                        Complete demographic, civic status, ward sanitation, and infrastructure matrix of all <strong>198 statutory municipal corporations, nagar parishads, nagar panchayats, census towns</strong> and <strong>670 slum clusters</strong> in Bihar.
                    </p>
                </div>

                <div class="col-lg-4 text-lg-end">
                    <div class="d-flex flex-column flex-sm-row flex-lg-column gap-2 justify-content-lg-end">
                        <a href="<?php echo getVillageUrl(); ?>" class="btn btn-success rounded-pill fw-bold px-3 py-2 shadow-sm">
                            🏡 Go to 44,874 Villages Directory &rarr;
                        </a>
                        <a href="<?php echo getCensusUrl(); ?>" class="btn btn-outline-light rounded-pill fw-bold px-3 py-2">
                            📊 Complete Bihar Census Hub &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- Aggregate Urban Bihar Stats Bar -->
            <div class="row g-2 g-md-3 mt-4 pt-3 border-top border-white border-opacity-10 text-center">
                <div class="col-6 col-md-3">
                    <div class="p-2.5 rounded-3 badge-glass-dark">
                        <span class="text-white-50 text-xs d-block">Total Urban Centers</span>
                        <span class="fw-bold text-warning fs-5">198 Towns</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2.5 rounded-3 badge-glass-dark">
                        <span class="text-white-50 text-xs d-block">Urban Population</span>
                        <span class="fw-bold text-white fs-5"><?php echo number_format($totalUrbanPop ?: 11758016); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2.5 rounded-3 badge-glass-dark">
                        <span class="text-white-50 text-xs d-block">Slum Settlements</span>
                        <span class="fw-bold text-danger fs-5">670 Slums</span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-2.5 rounded-3 badge-glass-dark">
                        <span class="text-white-50 text-xs d-block">Slum Population</span>
                        <span class="fw-bold text-info fs-5"><?php echo number_format($totalSlumPopAll ?: 647412); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Directory Main Content -->
    <main class="container py-4">

        <!-- Top Ad Slot -->
        <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

        <!-- Search & Filter Controls -->
        <div class="card town-glass-card p-4 mb-4">
            <form method="GET" action="<?php echo getTownUrl(); ?>" id="townFilterForm" class="row g-3">
                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-geo-alt-fill text-danger me-1"></i> District</label>
                    <select name="district" id="districtSelect" class="form-select form-select-sm" onchange="document.getElementById('townFilterForm').submit()">
                        <option value="">All 38 Districts</option>
                        <?php foreach ($districtsList as $d): ?>
                            <option value="<?php echo htmlspecialchars($d['slug']); ?>" <?php echo $districtParam === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-3">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-diagram-3-fill text-primary me-1"></i> Sub-District / Block</label>
                    <select name="block" id="blockSelect" class="form-select form-select-sm" <?php echo empty($districtBlocks) ? 'disabled' : ''; ?> onchange="document.getElementById('townFilterForm').submit()">
                        <option value="">All CD Blocks / Sub-Districts</option>
                        <?php if (!empty($districtBlocks)): ?>
                            <?php foreach ($districtBlocks as $db): 
                                $bVal = $db['sub_district_slug'] ?: slugify($db['sub_district_name'] ?: $db['cd_block_name']);
                                $bTitle = $db['sub_district_name'] ?: ($db['cd_block_name'] ?: 'Block');
                            ?>
                                <option value="<?php echo htmlspecialchars($bVal); ?>" <?php echo ($blockParam === $bVal || $blockParam === $db['sub_district_slug']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($bTitle); ?> (<?php echo $db['town_count']; ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-12 col-md-4 col-lg-2">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-building text-info me-1"></i> Civic Status</label>
                    <select name="civic" class="form-select form-select-sm" onchange="document.getElementById('townFilterForm').submit()">
                        <option value="">All Statuses</option>
                        <?php foreach ($civicStatuses as $cs): ?>
                            <option value="<?php echo htmlspecialchars($cs['civic_status']); ?>" <?php echo $civicParam === $cs['civic_status'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cs['civic_status']); ?> (<?php echo $cs['count']; ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-2">
                    <label class="form-label small fw-bold text-navy mb-1"><i class="bi bi-sort-numeric-down text-warning me-1"></i> Sort By</label>
                    <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('townFilterForm').submit()">
                        <option value="pop_desc" <?php echo $sortParam === 'pop_desc' ? 'selected' : ''; ?>>Population (High to Low)</option>
                        <option value="slum_desc" <?php echo $sortParam === 'slum_desc' ? 'selected' : ''; ?>>Slum Population (High to Low)</option>
                        <option value="pop_asc" <?php echo $sortParam === 'pop_asc' ? 'selected' : ''; ?>>Population (Low to High)</option>
                        <option value="name_asc" <?php echo $sortParam === 'name_asc' ? 'selected' : ''; ?>>Town Name (A to Z)</option>
                        <option value="area_desc" <?php echo $sortParam === 'area_desc' ? 'selected' : ''; ?>>Urban Area (Largest)</option>
                    </select>
                </div>

                <div class="col-12 col-md-6 col-lg-2 d-flex align-items-end">
                    <div class="form-check form-switch mb-1">
                        <input class="form-check-input" type="checkbox" name="slum_only" value="1" id="slumOnlySwitch" <?php echo $slumOnlyParam ? 'checked' : ''; ?> onchange="document.getElementById('townFilterForm').submit()">
                        <label class="form-check-label small fw-bold text-navy" for="slumOnlySwitch">
                            🛖 Slums Only
                        </label>
                    </div>
                </div>

                <div class="col-12">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control form-control-sm" placeholder="Search by town name, code, district, block..." value="<?php echo htmlspecialchars($searchParam); ?>">
                        <button type="submit" class="btn btn-primary px-3 fw-bold">Search Towns</button>
                        <?php if (!empty($districtParam) || !empty($blockParam) || !empty($civicParam) || !empty($searchParam) || $slumOnlyParam): ?>
                            <a href="<?php echo getTownUrl(); ?>" class="btn btn-outline-secondary" title="Reset Filters">
                                <i class="bi bi-arrow-counterclockwise"></i> Reset
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>

        <!-- Result Summary Bar -->
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <span class="text-navy fw-bold fs-6">
                    Showing <strong><?php echo number_format($totalCount); ?></strong> Urban Centers
                </span>
                <?php if (!empty($districtParam)): ?>
                    <span class="text-muted small">in <?php echo htmlspecialchars(ucfirst($districtParam)); ?> District</span>
                <?php endif; ?>
            </div>
            <div class="small text-muted">
                Page <?php echo $currentPage; ?> of <?php echo max(1, ceil($totalCount / $perPage)); ?>
            </div>
        </div>

        <!-- Towns Grid -->
        <?php if (!empty($townsList)): ?>
            <div class="row g-3 mb-4">
                <?php foreach ($townsList as $t): 
                    $tUrl = getTownUrl($t['district_slug'], $t['town_slug']);
                    $tPop = (int)($t['population'] ?? 0);
                    $tHh = (int)($t['households'] ?? 0);
                    $tSr = (int)($t['sex_ratio'] ?? 0);
                    $tSlums = (int)($t['slum_count'] ?? 0);
                    $tSlumPop = (int)($t['slum_population'] ?? 0);
                    $tCivic = $t['civic_status'] ?? 'Town';
                ?>
                    <div class="col-md-6 col-lg-4">
                        <div class="card town-glass-card p-3 h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-primary-subtle text-primary font-monospace small">Code: <?php echo htmlspecialchars($t['town_code']); ?></span>
                                    <span class="badge bg-secondary-subtle text-secondary small"><?php echo htmlspecialchars($tCivic); ?></span>
                                </div>

                                <h5 class="fw-bold text-navy mb-1 fs-6">
                                    <a href="<?php echo htmlspecialchars($tUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                        🏙️ <?php echo htmlspecialchars($t['town_name']); ?>
                                    </a>
                                </h5>

                                <p class="text-muted small mb-2">
                                    <i class="bi bi-geo-alt text-danger me-1"></i> <?php echo htmlspecialchars($t['district_name']); ?> District • <?php echo htmlspecialchars($t['sub_district_name'] ?: ($t['cd_block_name'] ?: 'Block')); ?>
                                </p>

                                <div class="p-2.5 bg-light rounded-3 mb-3 border">
                                    <div class="row g-1 text-center small">
                                        <div class="col-4 border-end">
                                            <span class="text-xs text-muted d-block">Population</span>
                                            <span class="fw-bold text-navy"><?php echo number_format($tPop); ?></span>
                                        </div>
                                        <div class="col-4 border-end">
                                            <span class="text-xs text-muted d-block">Households</span>
                                            <span class="fw-bold text-info"><?php echo number_format($tHh); ?></span>
                                        </div>
                                        <div class="col-4">
                                            <span class="text-xs text-muted d-block">Sex Ratio</span>
                                            <span class="fw-bold text-danger"><?php echo $tSr; ?></span>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($tSlums > 0): ?>
                                    <div class="p-2 rounded-3 bg-warning-subtle text-dark border border-warning-subtle small mb-3 d-flex justify-content-between align-items-center">
                                        <span>🛖 <strong><?php echo $tSlums; ?></strong> Slum Settlements</span>
                                        <span class="badge bg-warning text-dark"><?php echo number_format($tSlumPop); ?> Pop.</span>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="pt-2 border-top d-flex gap-2">
                                <a href="<?php echo htmlspecialchars($tUrl); ?>" class="btn btn-primary rounded-pill w-100 btn-sm fw-semibold">
                                    View Full Town Profile &rarr;
                                </a>
                                <?php if ($tSlums > 0): ?>
                                    <a href="<?php echo htmlspecialchars($tUrl); ?>#slums-section" class="btn btn-outline-warning rounded-pill btn-sm text-dark" title="View <?php echo $tSlums; ?> Slums">
                                        🛖
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination Bar -->
            <?php 
            $totalPages = max(1, ceil($totalCount / $perPage));
            if ($totalPages > 1): 
                $queryParams = $_GET;
                unset($queryParams['page']);
                $baseUrl = getTownUrl() . '?' . http_build_query($queryParams);
            ?>
                <nav class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-4 pt-3 border-top">
                    <div class="small text-muted">
                        Showing <?php echo number_format(($currentPage - 1) * $perPage + 1); ?> - <?php echo number_format(min($totalCount, $currentPage * $perPage)); ?> of <?php echo number_format($totalCount); ?> towns
                    </div>
                    <ul class="pagination pagination-sm mb-0">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $baseUrl . '&page=' . ($currentPage - 1); ?>" aria-label="Previous">
                                    &laquo; Prev
                                </a>
                            </li>
                        <?php endif; ?>

                        <?php
                        $startP = max(1, $currentPage - 2);
                        $endP = min($totalPages, $currentPage + 2);
                        if ($startP > 1) {
                            echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=1">1</a></li>';
                            if ($startP > 2) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                        }
                        for ($p = $startP; $p <= $endP; $p++):
                        ?>
                            <li class="page-item <?php echo $p === $currentPage ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $baseUrl . '&page=' . $p; ?>"><?php echo $p; ?></a>
                            </li>
                        <?php endfor; 
                        if ($endP < $totalPages) {
                            if ($endP < $totalPages - 1) echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
                            echo '<li class="page-item"><a class="page-link" href="' . $baseUrl . '&page=' . $totalPages . '">' . $totalPages . '</a></li>';
                        }
                        ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="<?php echo $baseUrl . '&page=' . ($currentPage + 1); ?>" aria-label="Next">
                                    Next &raquo;
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="card town-glass-card p-5 text-center my-4">
                <i class="bi bi-buildings text-muted fs-1 mb-2"></i>
                <h4 class="fw-bold text-navy mb-1">No Urban Centers Found</h4>
                <p class="text-muted small mb-3">Try adjusting your filters, selecting a different district, or clearing the search query.</p>
                <div>
                    <a href="<?php echo getTownUrl(); ?>" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        View All Bihar Towns
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </main>

<?php endif; ?>

<?php
require_once __DIR__ . '/footer.php';
