<?php
require_once __DIR__ . '/includes/functions.php';

$districts = DataProvider::getDistricts();
$pdo = Database::getConnection();

// Input parameters
$districtInput = $_GET['district'] ?? '';
$blockInput = $_GET['block'] ?? '';
$panchayatInput = $_GET['panchayat'] ?? '';
$slugInput = $_GET['slug'] ?? '';

// If single slug passed, check if it is a district slug
if (empty($districtInput) && !empty($slugInput)) {
    $matchedDist = DataProvider::getDistrictBySlug($slugInput);
    if ($matchedDist) {
        $districtInput = $slugInput;
        $slugInput = '';
    }
}

$districtObj = !empty($districtInput) ? DataProvider::getDistrictBySlug($districtInput) : null;
$selectedDistrictSlug = $districtObj['slug'] ?? ($districtInput ? strtolower(trim($districtInput)) : '');

// State / District Variables
$singlePanchayat = null;
$singleBlockObj = null;
$panchayatsInBlock = [];
$panchayatsInSameBlock = [];
$blockSamiti = null;
$panchayatSamitiMembers = [];
$blockSamitiMembers = [];
$panchayatWardMembers = [];
$panchayatPanchMembers = [];
$blockWardCountsByPanchayat = [];
$blockPanchCountsByPanchayat = [];
$blockWardCount = 0;
$blockPanchCount = 0;
$zilaParishadMembers = [];
$isSpecificZilaTerritory = false;
$allDistrictPanchayats = [];
$blockPanchayatsMap = [];
$districtBlocks = [];
$districtSamitiMemberCounts = [];
$districtWardCounts = [];
$districtPanchCounts = [];

if ($districtObj && $pdo) {
    $targetPanchayatQuery = $panchayatInput ?: $slugInput;

    // 1. Fetch all census blocks in this district (cached)
    $districtBlocks = DataCache::remember('census_blocks_' . $selectedDistrictSlug, 86400, function() use ($pdo, $selectedDistrictSlug) {
        try {
            $stmtB = $pdo->prepare("SELECT * FROM census_subdistricts WHERE district_slug = :dslug ORDER BY sub_district ASC");
            $stmtB->execute([':dslug' => $selectedDistrictSlug]);
            return $stmtB->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }) ?: [];
    
    // 2. Fetch all panchayats in this district (cached)
    $allDistrictPanchayats = DataCache::remember('district_panchayats_' . $selectedDistrictSlug, 86400, function() use ($pdo, $selectedDistrictSlug) {
        try {
            $stmtP = $pdo->prepare("SELECT * FROM panchayats WHERE district_slug = :dslug ORDER BY id ASC");
            $stmtP->execute([':dslug' => $selectedDistrictSlug]);
            return $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }) ?: [];

    // Group panchayats by block
    foreach ($districtBlocks as $blk) {
        $bSlug = slugify($blk['sub_district']);
        $blockPanchayatsMap[$bSlug] = [];
        foreach ($allDistrictPanchayats as $p) {
            if (isBlockMatch($p['block'], $blk['sub_district'])) {
                $blockPanchayatsMap[$bSlug][] = $p;
            }
        }
    }

    if (!empty($targetPanchayatQuery)) {
        // A. Check if user requested a specific Panchayat with block specified
        if (!empty($blockInput)) {
            foreach ($allDistrictPanchayats as $p) {
                if (isBlockMatch($p['block'], $blockInput) && isPanchayatMatch($p['panchayat_name'], $targetPanchayatQuery)) {
                    $singlePanchayat = $p;
                    break;
                }
            }
        }

        // B. Fallback: match panchayat name anywhere in the district
        if (!$singlePanchayat) {
            foreach ($allDistrictPanchayats as $p) {
                if (isPanchayatMatch($p['panchayat_name'], $targetPanchayatQuery)) {
                    $singlePanchayat = $p;
                    break;
                }
            }
        }

        // C. Fallback: check if target query was actually a Block name (e.g. /panchayat/saran/amnour)
        if (!$singlePanchayat) {
            $checkBlockQuery = !empty($blockInput) ? $blockInput : $targetPanchayatQuery;
            foreach ($districtBlocks as $blk) {
                if (isBlockMatch($blk['sub_district'], $checkBlockQuery) || slugify($blk['sub_district']) === slugify($checkBlockQuery)) {
                    $singleBlockObj = $blk;
                    $bSlug = slugify($blk['sub_district']);
                    $panchayatsInBlock = $blockPanchayatsMap[$bSlug] ?? [];
                    break;
                }
            }
        }
    } elseif (!empty($blockInput)) {
        // Block was requested without specific panchayat
        foreach ($districtBlocks as $blk) {
            if (isBlockMatch($blk['sub_district'], $blockInput) || slugify($blk['sub_district']) === slugify($blockInput)) {
                $singleBlockObj = $blk;
                $bSlug = slugify($blk['sub_district']);
                $panchayatsInBlock = $blockPanchayatsMap[$bSlug] ?? [];
                break;
            }
        }
    }

    // If single panchayat matched, gather all related Tier-1, Tier-2, and Tier-3 governance data
    if ($singlePanchayat) {
        $currentBlockName = $singlePanchayat['block'];

        // Gather sibling panchayats in the same block
        foreach ($allDistrictPanchayats as $p) {
            if (isBlockMatch($p['block'], $currentBlockName) && $p['id'] !== $singlePanchayat['id']) {
                $panchayatsInSameBlock[] = $p;
            }
        }

        // Tier-2 Panchayat Samiti (Pramukh / Up-Pramukh & Elected Member 2021-2026)
        $panchayatSamitiMembers = [];
        try {
            $stmtSamiti = $pdo->prepare("SELECT * FROM panchayat_samiti_2016 WHERE district_slug = :dslug");
            $stmtSamiti->execute([':dslug' => $selectedDistrictSlug]);
            $samitiRows = $stmtSamiti->fetchAll(PDO::FETCH_ASSOC);
            foreach ($samitiRows as $sr) {
                if (isBlockMatch($sr['block'], $currentBlockName) || (!empty($blockInput) && isBlockMatch($sr['block'], $blockInput))) {
                    $blockSamiti = $sr;
                    break;
                }
            }
        } catch (Throwable $e) {}

        // Elected Panchayat Samiti Member(s) (2021-2026) for this Gram Panchayat
        try {
            $stmtPsm = $pdo->prepare("SELECT * FROM panchayat_samiti_member_2021 WHERE district_slug = :dslug ORDER BY CAST(territory_no AS UNSIGNED) ASC");
            $stmtPsm->execute([':dslug' => $selectedDistrictSlug]);
            $allDistPsm = $stmtPsm->fetchAll(PDO::FETCH_ASSOC);

            $pTargetName = trim($singlePanchayat['panchayat_name'] ?? '');
            $pTargetSlug = slugify($pTargetName);

            foreach ($allDistPsm as $pm) {
                if (isBlockMatch($pm['block'] ?? '', $currentBlockName) || (!empty($blockInput) && isBlockMatch($pm['block'] ?? '', $blockInput))) {
                    $pmPanchayat = trim($pm['panchayat'] ?? '');
                    $pmSlug = slugify($pmPanchayat);
                    if ($pmSlug === $pTargetSlug || strcasecmp($pmPanchayat, $pTargetName) === 0 || stripos($pmPanchayat, $pTargetName) !== false || stripos($pTargetName, $pmPanchayat) !== false) {
                        $panchayatSamitiMembers[] = $pm;
                    }
                }
            }
        } catch (Throwable $e) {}

        // Tier-3 Zila Parishad representation
        // User rule: "If jila parisad not specified show all zila parisad of block"
        $terrNo = trim($singlePanchayat['zila_parishad_territory_no'] ?? '');
        if (!empty($terrNo)) {
            try {
                $stmtZp = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE district_slug = :dslug AND (territory_no = :terr OR ward_no = :terr)");
                $stmtZp->execute([':dslug' => $selectedDistrictSlug, ':terr' => $terrNo]);
                $matchedZp = $stmtZp->fetchAll(PDO::FETCH_ASSOC);
                if (!empty($matchedZp)) {
                    $zilaParishadMembers = $matchedZp;
                    $isSpecificZilaTerritory = true;
                }
            } catch (Throwable $e) {}
        }

        // If not specified or no territory matched, fetch ALL Zila Parishad members representing this block
        if (empty($zilaParishadMembers)) {
            try {
                $stmtZpAll = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE district_slug = :dslug ORDER BY CAST(territory_no AS UNSIGNED) ASC");
                $stmtZpAll->execute([':dslug' => $selectedDistrictSlug]);
                $allZpDist = $stmtZpAll->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allZpDist as $zm) {
                    if (isBlockMatch($zm['block'] ?? '', $currentBlockName) || (!empty($blockInput) && isBlockMatch($zm['block'] ?? '', $blockInput))) {
                        $zilaParishadMembers[] = $zm;
                    }
                }
            } catch (Throwable $e) {}
        }

        // Gram Panchayat Ward Members (2021-2026) from `ward_21_26`
        $panchayatWardMembers = [];
        try {
            $pTargetName = trim($singlePanchayat['panchayat_name'] ?? '');
            $stmtW = $pdo->prepare("SELECT * FROM ward_21_26 WHERE district_slug = :dslug AND (block = :bname OR block LIKE :blike) AND (panchayat = :pname OR panchayat LIKE :plike) ORDER BY CAST(ward_no AS UNSIGNED) ASC, id ASC");
            $stmtW->execute([
                ':dslug' => $selectedDistrictSlug,
                ':bname' => $currentBlockName,
                ':blike' => '%' . trim($currentBlockName) . '%',
                ':pname' => $pTargetName,
                ':plike' => '%' . $pTargetName . '%'
            ]);
            $panchayatWardMembers = $stmtW->fetchAll(PDO::FETCH_ASSOC);

            if (empty($panchayatWardMembers)) {
                $stmtWAll = $pdo->prepare("SELECT * FROM ward_21_26 WHERE district_slug = :dslug ORDER BY CAST(ward_no AS UNSIGNED) ASC, id ASC");
                $stmtWAll->execute([':dslug' => $selectedDistrictSlug]);
                $allW = $stmtWAll->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allW as $wm) {
                    if (isBlockMatch($wm['block'] ?? '', $currentBlockName) && isPanchayatMatch($wm['panchayat'] ?? '', $pTargetName)) {
                        $panchayatWardMembers[] = $wm;
                    }
                }
            }
        } catch (Throwable $e) {}

        // Gram Kutchery Panch Members (2021-2026) from `panch_21_26`
        $panchayatPanchMembers = [];
        try {
            $pTargetName = trim($singlePanchayat['panchayat_name'] ?? '');
            $stmtPanch = $pdo->prepare("SELECT * FROM panch_21_26 WHERE district_slug = :dslug AND (block = :bname OR block LIKE :blike) AND (panchayat = :pname OR panchayat LIKE :plike) ORDER BY CAST(ward_no AS UNSIGNED) ASC, id ASC");
            $stmtPanch->execute([
                ':dslug' => $selectedDistrictSlug,
                ':bname' => $currentBlockName,
                ':blike' => '%' . trim($currentBlockName) . '%',
                ':pname' => $pTargetName,
                ':plike' => '%' . $pTargetName . '%'
            ]);
            $panchayatPanchMembers = $stmtPanch->fetchAll(PDO::FETCH_ASSOC);

            if (empty($panchayatPanchMembers)) {
                $stmtPAll = $pdo->prepare("SELECT * FROM panch_21_26 WHERE district_slug = :dslug ORDER BY CAST(ward_no AS UNSIGNED) ASC, id ASC");
                $stmtPAll->execute([':dslug' => $selectedDistrictSlug]);
                $allP = $stmtPAll->fetchAll(PDO::FETCH_ASSOC);
                foreach ($allP as $pm) {
                    if (isBlockMatch($pm['block'] ?? '', $currentBlockName) && isPanchayatMatch($pm['panchayat'] ?? '', $pTargetName)) {
                        $panchayatPanchMembers[] = $pm;
                    }
                }
            }
        } catch (Throwable $e) {}

        // Fetch Census 2011 Villages belonging to this Gram Panchayat
        $panchayatVillages = [];
        $pNameClean = $singlePanchayat['panchayat_name'];
        $pSlugClean = slugify($pNameClean);
        $bSlugClean = slugify($currentBlockName);

        try {
            $stmtV = $pdo->prepare("SELECT * FROM census_villages_2011 WHERE district_slug = :dslug AND (gram_panchayat_slug = :pslug OR gram_panchayat_name LIKE :pname) ORDER BY population DESC");
            $stmtV->execute([
                ':dslug' => $selectedDistrictSlug,
                ':pslug' => $pSlugClean,
                ':pname' => '%' . $pNameClean . '%'
            ]);
            $panchayatVillages = $stmtV->fetchAll(PDO::FETCH_ASSOC);

            // Fallback: If no exact GP matched, check for villages in this block with same name or related
            if (empty($panchayatVillages)) {
                $stmtV2 = $pdo->prepare("SELECT * FROM census_villages_2011 WHERE district_slug = :dslug AND (sub_district_slug = :bslug OR cd_block_name LIKE :bname) AND (village_slug LIKE :pslug OR village_name LIKE :pname) ORDER BY population DESC LIMIT 8");
                $stmtV2->execute([
                    ':dslug' => $selectedDistrictSlug,
                    ':bslug' => $bSlugClean,
                    ':bname' => '%' . $currentBlockName . '%',
                    ':pslug' => '%' . $pSlugClean . '%',
                    ':pname' => '%' . $pNameClean . '%'
                ]);
                $panchayatVillages = $stmtV2->fetchAll(PDO::FETCH_ASSOC);
            }
        } catch (Throwable $e) {}

        // Fetch Historical 1991 Census Villages for this Gram Panchayat
        $panchayatVillages1991 = [];
        $panchayat1991Stats = [
            'population' => 0,
            'male' => 0,
            'female' => 0,
            'households' => 0,
            'literates' => 0,
            'sc_pop' => 0,
            'st_pop' => 0,
            'workers' => 0,
            'cultivators' => 0,
            'agri_labour' => 0,
            'village_count' => 0
        ];

        try {
            $v2011Slugs = !empty($panchayatVillages) ? array_column($panchayatVillages, 'village_slug') : [$pSlugClean];
            
            if (!empty($v2011Slugs)) {
                $inClause = implode(',', array_fill(0, count($v2011Slugs), '?'));
                $params1991 = array_merge([$selectedDistrictSlug, $bSlugClean, '%' . $currentBlockName . '%'], $v2011Slugs, ['%' . $pNameClean . '%']);
                
                $sql1991 = "SELECT * FROM census_villages_1991 
                            WHERE district_slug = ? 
                            AND (sub_district_slug = ? OR sub_district_name LIKE ?) 
                            AND (village_slug IN ($inClause) OR village_name LIKE ?) 
                            ORDER BY population DESC";
                $stmt91 = $pdo->prepare($sql1991);
                $stmt91->execute($params1991);
                $panchayatVillages1991 = $stmt91->fetchAll(PDO::FETCH_ASSOC);
            }

            if (empty($panchayatVillages1991)) {
                $stmt91_fb = $pdo->prepare("SELECT * FROM census_villages_1991 WHERE district_slug = :dslug AND (village_slug = :pslug OR village_name LIKE :pname) ORDER BY population DESC LIMIT 8");
                $stmt91_fb->execute([
                    ':dslug' => $selectedDistrictSlug,
                    ':pslug' => $pSlugClean,
                    ':pname' => '%' . $pNameClean . '%'
                ]);
                $panchayatVillages1991 = $stmt91_fb->fetchAll(PDO::FETCH_ASSOC);
            }

            if (!empty($panchayatVillages1991)) {
                foreach ($panchayatVillages1991 as $pv91) {
                    $panchayat1991Stats['population'] += (int)($pv91['population'] ?? 0);
                    $panchayat1991Stats['male'] += (int)($pv91['male'] ?? 0);
                    $panchayat1991Stats['female'] += (int)($pv91['female'] ?? 0);
                    $panchayat1991Stats['households'] += (int)($pv91['households'] ?? 0);
                    $panchayat1991Stats['literates'] += (int)($pv91['literates_total'] ?? 0);
                    $panchayat1991Stats['sc_pop'] += (int)($pv91['sc_population'] ?? 0);
                    $panchayat1991Stats['st_pop'] += (int)($pv91['st_population'] ?? 0);
                    $panchayat1991Stats['workers'] += (int)($pv91['workers_total'] ?? 0);
                    $panchayat1991Stats['cultivators'] += (int)(($pv91['m_cultivators'] ?? 0) + ($pv91['f_cultivators'] ?? 0));
                    $panchayat1991Stats['agri_labour'] += (int)(($pv91['m_agri_labour'] ?? 0) + ($pv91['f_agri_labour'] ?? 0));
                }
                $panchayat1991Stats['village_count'] = count($panchayatVillages1991);
            }
        } catch (Throwable $e) {}
    }

    // If single block matched, gather block samiti & ZP representation
    if ($singleBlockObj && !$singlePanchayat) {
        $bName = $singleBlockObj['sub_district'];
        $blockSamitiMembers = [];
        try {
            $stmtSamiti = $pdo->prepare("SELECT * FROM panchayat_samiti_2016 WHERE district_slug = :dslug");
            $stmtSamiti->execute([':dslug' => $selectedDistrictSlug]);
            $samitiRows = $stmtSamiti->fetchAll(PDO::FETCH_ASSOC);
            foreach ($samitiRows as $sr) {
                if (isBlockMatch($sr['block'], $bName)) {
                    $blockSamiti = $sr;
                    break;
                }
            }
        } catch (Throwable $e) {}

        // Gather all Panchayat Samiti members in this block
        try {
            $stmtBlockPsm = $pdo->prepare("SELECT * FROM panchayat_samiti_member_2021 WHERE district_slug = :dslug ORDER BY CAST(territory_no AS UNSIGNED) ASC, panchayat ASC");
            $stmtBlockPsm->execute([':dslug' => $selectedDistrictSlug]);
            $allDistBlockPsm = $stmtBlockPsm->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allDistBlockPsm as $pm) {
                if (isBlockMatch($pm['block'] ?? '', $bName) || (!empty($blockInput) && isBlockMatch($pm['block'] ?? '', $blockInput))) {
                    $blockSamitiMembers[] = $pm;
                }
            }
        } catch (Throwable $e) {}

        try {
            $stmtZpAll = $pdo->prepare("SELECT * FROM zila_parishad_members WHERE district_slug = :dslug ORDER BY CAST(territory_no AS UNSIGNED) ASC");
            $stmtZpAll->execute([':dslug' => $selectedDistrictSlug]);
            $allZpDist = $stmtZpAll->fetchAll(PDO::FETCH_ASSOC);
            foreach ($allZpDist as $zm) {
                if (isBlockMatch($zm['block'] ?? '', $bName)) {
                    $zilaParishadMembers[] = $zm;
                }
            }
        } catch (Throwable $e) {}

        // Block-level Ward and Panch counts from ward_21_26 and panch_21_26
        try {
            $stmtBw = $pdo->prepare("SELECT panchayat, COUNT(*) as cnt FROM ward_21_26 WHERE district_slug = :dslug AND (block = :bname OR block LIKE :blike) GROUP BY panchayat");
            $stmtBw->execute([':dslug' => $selectedDistrictSlug, ':bname' => $bName, ':blike' => '%' . trim($bName) . '%']);
            while ($r = $stmtBw->fetch(PDO::FETCH_ASSOC)) {
                $blockWardCountsByPanchayat[trim($r['panchayat'])] = intval($r['cnt']);
                $blockWardCount += intval($r['cnt']);
            }
        } catch (Throwable $e) {}

        try {
            $stmtBp = $pdo->prepare("SELECT panchayat, COUNT(*) as cnt FROM panch_21_26 WHERE district_slug = :dslug AND (block = :bname OR block LIKE :blike) GROUP BY panchayat");
            $stmtBp->execute([':dslug' => $selectedDistrictSlug, ':bname' => $bName, ':blike' => '%' . trim($bName) . '%']);
            while ($r = $stmtBp->fetch(PDO::FETCH_ASSOC)) {
                $blockPanchCountsByPanchayat[trim($r['panchayat'])] = intval($r['cnt']);
                $blockPanchCount += intval($r['cnt']);
            }
        } catch (Throwable $e) {}
    }
}

// Pre-fetch district-level aggregated statistics if browsing district or state (cached)
$districtStatsBundle = DataCache::remember('panchayat_district_stats_v2', 86400, function() use ($pdo) {
    $census = [];
    $panchayats = [];
    $samiti = [];
    $wards = [];
    $panch = [];
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT district_slug, COUNT(*) as total_blocks, SUM(population) as total_pop, AVG(literacy_rate) as avg_literacy, SUM(households) as total_households FROM census_subdistricts GROUP BY district_slug");
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $census[strtolower($row['district_slug'])] = $row;
            }

            $stmt2 = $pdo->query("SELECT district_slug, COUNT(*) as total_panchayats FROM panchayats GROUP BY district_slug");
            while ($row2 = $stmt2->fetch(PDO::FETCH_ASSOC)) {
                $panchayats[strtolower($row2['district_slug'])] = intval($row2['total_panchayats']);
            }

            $stmt3 = $pdo->query("SELECT district_slug, COUNT(*) as total_samiti_members FROM panchayat_samiti_member_2021 GROUP BY district_slug");
            while ($row3 = $stmt3->fetch(PDO::FETCH_ASSOC)) {
                $samiti[strtolower($row3['district_slug'])] = intval($row3['total_samiti_members']);
            }

            $stmt4 = $pdo->query("SELECT district_slug, COUNT(*) as total_wards FROM ward_21_26 GROUP BY district_slug");
            while ($row4 = $stmt4->fetch(PDO::FETCH_ASSOC)) {
                $wards[strtolower($row4['district_slug'])] = intval($row4['total_wards']);
            }

            $stmt5 = $pdo->query("SELECT district_slug, COUNT(*) as total_panch FROM panch_21_26 GROUP BY district_slug");
            while ($row5 = $stmt5->fetch(PDO::FETCH_ASSOC)) {
                $panch[strtolower($row5['district_slug'])] = intval($row5['total_panch']);
            }
        } catch (Throwable $e) {}
    }
    return ['census' => $census, 'panchayats' => $panchayats, 'samiti' => $samiti, 'wards' => $wards, 'panch' => $panch];
}) ?: ['census' => [], 'panchayats' => [], 'samiti' => [], 'wards' => [], 'panch' => []];

$districtCensusStats = $districtStatsBundle['census'] ?? [];
$districtPanchayatCounts = $districtStatsBundle['panchayats'] ?? [];
$districtSamitiMemberCounts = $districtStatsBundle['samiti'] ?? [];
$districtWardCounts = $districtStatsBundle['wards'] ?? [];
$districtPanchCounts = $districtStatsBundle['panch'] ?? [];

// State Totals
$totalStateBlocks = 534;
$totalStatePanchayats = array_sum($districtPanchayatCounts) ?: 8406;
$totalStateWards = array_sum($districtWardCounts) ?: 101075;
$totalStatePanch = array_sum($districtPanchCounts) ?: 96938;
$totalStatePopulation = 104099452;

// Page Meta & SEO
if ($singlePanchayat && $districtObj) {
    $pName = $singlePanchayat['panchayat_name'];
    $bName = $singlePanchayat['block'];
    $dName = $districtObj['name'];
    $mName = $singlePanchayat['current_mukhiya'] ?: 'निर्वाचित मुखिया';
    $sName = $singlePanchayat['current_sarpanch'] ?: 'Elected Sarpanch';

    $pageTitle = "{$pName} Gram Panchayat Profile, {$bName} Block ({$dName}) - Bihar Panchayati Raj";
    $pageDescription = "Official profile of {$pName} Gram Panchayat in {$bName} Block, {$dName} District. Explore elected Mukhiya ({$mName}), Sarpanch ({$sName}), Panchayat Samiti Pramukh, and Zila Parishad representation.";
    $pageCanonical = getPanchayatUrl($districtObj['slug'], slugify($bName), slugify($pName));
} elseif ($singleBlockObj && $districtObj) {
    $bName = $singleBlockObj['sub_district'];
    $dName = $districtObj['name'];
    $pCount = count($panchayatsInBlock);

    $pageTitle = "{$bName} Block Gram Panchayats Directory ({$pCount} Panchayats, {$dName}) - Bihar Panchayati Raj";
    $pageDescription = "Explore all {$pCount} Gram Panchayats in {$bName} Block, {$dName} District. View elected Mukhiyas, Sarpanchs, demographics, and Panchayat Samiti representatives.";
    $pageCanonical = getBlockUrl($districtObj['slug'], slugify($bName));
} elseif ($districtObj) {
    $pageTitle = "{$districtObj['name']} District CD Blocks & Panchayati Raj Directory (534 Blocks)";
    $pageDescription = "Explore {$districtObj['name']} District Community Development (CD) Blocks, Gram Panchayats distribution, census demographics, literacy rates, and administrative directory.";
    $pageCanonical = getPanchayatUrl($districtObj['slug']);
} else {
    $pageTitle = "Bihar Panchayati Raj & CD Blocks Directory: 38 Districts & 534 Blocks";
    $pageDescription = "Complete administrative directory of Bihar Panchayati Raj: 38 Districts, 534 Community Development (CD) Blocks, and 8,400+ Gram Panchayats with demographics and local governance rosters.";
    $pageCanonical = getPanchayatUrl();
}

// Build Division counts for filter badges
$divisionsList = ['Patna', 'Tirhut', 'Saran', 'Magadh', 'Darbhanga', 'Kosi', 'Purnia', 'Bhagalpur', 'Munger'];
$divisionCounts = [];
foreach ($districts as $d) {
    $dDiv = trim(preg_replace('/\s+division$/i', '', $d['division'] ?? ''));
    if ($dDiv) {
        $divisionCounts[$dDiv] = ($divisionCounts[$dDiv] ?? 0) + 1;
    }
}

$activeNav = 'panchayat';
require_once __DIR__ . '/includes/header.php';
?>

<style>
/* Division Pill Buttons */
.division-pill-btn {
    font-size: 0.85rem;
    font-weight: 600;
    border-radius: 50px;
    padding: 0.4rem 0.9rem;
    border: 1px solid #dee2e6;
    background-color: #fff;
    color: #495057;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
.division-pill-btn:hover {
    background-color: #f8f9fa;
    border-color: var(--accent-saffron, #ff9933);
    color: var(--navy-dark, #0b1a30);
    transform: translateY(-1px);
}
.division-pill-btn.active {
    background: linear-gradient(135deg, #0b1a30 0%, #17345f 100%);
    border-color: #0b1a30;
    color: #fff;
    box-shadow: 0 4px 10px rgba(11, 26, 48, 0.2);
}
.division-pill-btn.active .division-badge-count {
    background-color: var(--accent-saffron, #ff9933);
    color: #000;
}
.division-badge-count {
    font-size: 0.72rem;
    padding: 0.15rem 0.45rem;
    border-radius: 50px;
    background-color: #e9ecef;
    color: #495057;
    font-weight: 700;
}

/* Administrative & Profile Cards */
.admin-card {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #edf2f7;
    box-shadow: 0 4px 14px rgba(0,0,0,0.03);
    transition: all 0.25s cubic-bezier(0.165, 0.84, 0.44, 1);
    position: relative;
    overflow: hidden;
}
.admin-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 28px rgba(11, 26, 48, 0.08);
    border-color: #cbd5e1;
}
.admin-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, #0b1a30 0%, var(--accent-saffron, #ff9933) 100%);
    opacity: 0.85;
}
.admin-card-block::before {
    background: linear-gradient(90deg, var(--accent-saffron, #ff9933) 0%, #198754 100%);
}
.metric-mini-box {
    background: #f8fafc;
    border-radius: 12px;
    padding: 0.75rem 0.65rem;
    border: 1px solid #f1f5f9;
    text-align: center;
}

/* Tier Profile Cards */
.tier-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
    position: relative;
    overflow: hidden;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.tier-card-mukhiya { border-top: 5px solid #ff9933; }
.tier-card-sarpanch { border-top: 5px solid #0d6efd; }
.tier-card-samiti { border-top: 5px solid #198754; }
.tier-card-zila { border-top: 5px solid #6f42c1; }

.tier-header-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.35rem 0.85rem;
    border-radius: 50px;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.tier-avatar-box {
    width: 60px;
    height: 60px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    flex-shrink: 0;
}
.profile-prop-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.45rem 0;
    border-bottom: 1px dashed #e2e8f0;
    font-size: 0.88rem;
}
.profile-prop-row:last-child {
    border-bottom: none;
}
.sibling-panchayat-pill {
    display: inline-block;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    padding: 0.45rem 0.9rem;
    border-radius: 50px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #334155;
    text-decoration: none;
    transition: all 0.2s ease;
}
.sibling-panchayat-pill:hover {
    background: #0b1a30;
    color: #fff;
    border-color: #0b1a30;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(11, 26, 48, 0.15);
}
</style>

<!-- Hero Header Section -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0 bg-white bg-opacity-10 px-3 py-2 rounded-pill d-inline-flex border border-white border-opacity-10">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <?php if ($singlePanchayat && $districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl(); ?>" class="text-white-50 text-decoration-none">Panchayats</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getDistrictUrl($districtObj['slug']); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($singlePanchayat['block']); ?> Block</a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?></li>
                <?php elseif ($singleBlockObj && $districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl(); ?>" class="text-white-50 text-decoration-none">Panchayats</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="text-white-50 text-decoration-none"><?php echo htmlspecialchars($districtObj['name']); ?></a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($singleBlockObj['sub_district']); ?> Block</li>
                <?php elseif ($districtObj): ?>
                    <li class="breadcrumb-item"><a href="<?php echo getPanchayatUrl(); ?>" class="text-white-50 text-decoration-none">Panchayati Raj &amp; CD Blocks</a></li>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page"><?php echo htmlspecialchars($districtObj['name']); ?></li>
                <?php else: ?>
                    <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Panchayati Raj &amp; CD Blocks</li>
                <?php endif; ?>
            </ol>
        </nav>

        <div class="d-flex flex-wrap gap-2 mb-3 align-items-center">
            <?php if ($singlePanchayat && $districtObj): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🌾 ग्राम पंचायत प्रोफाइल
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    📍 <?php echo htmlspecialchars($singlePanchayat['block']); ?> प्रखंड
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🏢 <?php echo htmlspecialchars($districtObj['name']); ?> जिला
                </span>
                <?php if (!empty($panchayatWardMembers)): ?>
                    <span class="badge bg-primary bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                        🗳️ <?php echo count($panchayatWardMembers); ?> वार्ड सदस्य
                    </span>
                <?php endif; ?>
                <?php if (!empty($panchayatPanchMembers)): ?>
                    <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                        ⚖️ <?php echo count($panchayatPanchMembers); ?> कचहरी पंच
                    </span>
                <?php endif; ?>
                <?php if (!empty($singlePanchayat['delimitation_status'])): ?>
                    <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                        ✅ <?php echo htmlspecialchars($singlePanchayat['delimitation_status']); ?>
                    </span>
                <?php endif; ?>
            <?php elseif ($singleBlockObj && $districtObj): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    📍 CD Block Panchayats Hub
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🏢 <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    🌾 <?php echo count($panchayatsInBlock); ?> Gram Panchayats
                </span>
            <?php elseif ($districtObj): ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🏛️ Panchayati Raj &amp; CD Blocks
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    38 Districts
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    534 CD Blocks
                </span>
            <?php else: ?>
                <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                    🏛️ Panchayati Raj &amp; CD Blocks
                </span>
                <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    38 Districts
                </span>
                <span class="badge bg-success bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    534 CD Blocks
                </span>
                <span class="badge bg-info bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                    8,400+ Gram Panchayats
                </span>
            <?php endif; ?>
        </div>

        <h1 class="display-6 fw-extrabold text-white mb-2">
            <?php if ($singlePanchayat && $districtObj): ?>
                <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?> Gram Panchayat <br>
                <span style="color: var(--accent-saffron);">
                    <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block, <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
            <?php elseif ($singleBlockObj && $districtObj): ?>
                <?php echo htmlspecialchars($singleBlockObj['sub_district']); ?> Block Gram Panchayats <br>
                <span style="color: var(--accent-saffron);">
                    <?php echo count($panchayatsInBlock); ?> Panchayats, <?php echo htmlspecialchars($districtObj['name']); ?> District
                </span>
            <?php elseif ($districtObj): ?>
                <?php echo htmlspecialchars($districtObj['name']); ?> District CD Blocks &amp; Panchayats <br>
                <span style="color: var(--accent-saffron);"><?php echo htmlspecialchars($districtObj['name_hi'] ?? ''); ?> Panchayati Raj Directory</span>
            <?php else: ?>
                Bihar Panchayati Raj &amp; CD Blocks Directory <br>
                <span style="color: var(--accent-saffron);">38 Districts &amp; 534 Community Development Blocks</span>
            <?php endif; ?>
        </h1>

        <p class="lead text-white-50 mb-4" style="max-width: 840px; font-size: 1.05rem;">
            <?php if ($singlePanchayat && $districtObj): ?>
                Complete governance dossier of <strong><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?></strong> Gram Panchayat: elected Mukhiya (ग्राम प्रधान), Sarpanch (न्याय पीठ), Block Samiti Pramukh, and Zila Parishad territorial representation in <?php echo htmlspecialchars($districtObj['name']); ?>, Bihar.
            <?php elseif ($singleBlockObj && $districtObj): ?>
                Explore all <strong><?php echo count($panchayatsInBlock); ?> Gram Panchayats</strong> in <?php echo htmlspecialchars($singleBlockObj['sub_district']); ?> Block. View elected Mukhiyas, Sarpanchs, local demographics, and direct links to each Panchayat profile.
            <?php elseif ($districtObj): ?>
                Explore all Community Development (CD) Blocks, Gram Panchayats distribution, Census 2011 demographics, and official district &amp; block portals in <?php echo htmlspecialchars($districtObj['name']); ?> District.
            <?php else: ?>
                Explore Bihar's 3-tier local governance structure across all 38 Districts and 534 CD Blocks. Access block demographics, gram panchayat rosters, census statistics, and representative directories.
            <?php endif; ?>
        </p>

        <div class="d-flex flex-wrap gap-2">
            <?php if ($singlePanchayat && $districtObj): ?>
                <a href="#ward-roster" class="btn btn-primary fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-grid-3x3-gap-fill me-1"></i> वार्ड सदस्य एवं पंच
                </a>
                <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-building-check me-1"></i> सभी <?php echo htmlspecialchars($singlePanchayat['block']); ?> पंचायतें
                </a>
                <a href="<?php echo getBlockUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> <?php echo htmlspecialchars($singlePanchayat['block']); ?> प्रखंड हब
                </a>
                <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($districtObj['slug']); ?>&block=<?php echo urlencode($singlePanchayat['block']); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> समिति सदस्य
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-award-fill me-1"></i> प्रखंड प्रमुख
                </a>
                <a href="<?php echo getZilaParishadUrl($districtObj['slug']); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> जिला परिषद
                </a>
            <?php elseif ($singleBlockObj && $districtObj): ?>
                <a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> सभी <?php echo htmlspecialchars($districtObj['name']); ?> प्रखंड
                </a>
                <a href="<?php echo getBlockUrl($districtObj['slug'], slugify($singleBlockObj['sub_district'])); ?>" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> प्रखंड जनसांख्यिकी
                </a>
                <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($districtObj['slug']); ?>&block=<?php echo urlencode($singleBlockObj['sub_district']); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> समिति सदस्य
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($districtObj['slug'], slugify($singleBlockObj['sub_district'])); ?>" class="btn btn-outline-light fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-award me-1"></i> प्रमुख रोस्टर
                </a>
                <a href="<?php echo getZilaParishadUrl($districtObj['slug']); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> जिला परिषद
                </a>
            <?php elseif ($districtObj): ?>
                <a href="<?php echo SITE_URL; ?>/blocks" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> सभी 534 प्रखंड
                </a>
                <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($selectedDistrictSlug); ?>" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> समिति सदस्य (<?php echo number_format($districtSamitiMemberCounts[$selectedDistrictSlug] ?? 0); ?>)
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($selectedDistrictSlug); ?>" class="btn btn-outline-light fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-award-fill me-1"></i> प्रखंड प्रमुख
                </a>
                <a href="<?php echo getZilaParishadUrl($selectedDistrictSlug); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> जिला परिषद
                </a>
            <?php else: ?>
                <a href="<?php echo SITE_URL; ?>/blocks" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-geo-alt-fill me-1"></i> सभी 534 प्रखंड निर्देशिका
                </a>
                <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members" class="btn btn-success fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-people-fill me-1"></i> समिति सदस्य (8,752)
                </a>
                <a href="<?php echo getPanchayatSamitiUrl($selectedDistrictSlug); ?>" class="btn btn-outline-light fw-bold px-3 py-2 text-white shadow-sm">
                    <i class="bi bi-award-fill me-1"></i> प्रखंड प्रमुख
                </a>
                <a href="<?php echo getZilaParishadUrl($selectedDistrictSlug); ?>" class="btn btn-info fw-bold px-3 py-2 text-dark shadow-sm">
                    <i class="bi bi-bank me-1"></i> जिला परिषद बोर्ड
                </a>
                <a href="<?php echo getCensusUrl($selectedDistrictSlug); ?>" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                    <i class="bi bi-bar-chart-fill me-1"></i> जनगणना विवरण
                </a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Main Content Area -->
<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <?php if ($singlePanchayat && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- 1. COMPLETE DEDICATED GRAM PANCHAYAT PROFILE VIEW             -->
        <!-- ============================================================= -->
        
        <!-- Quick Administrative Overview Ribbon -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-start border-4 border-warning">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-8">
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            🏢 <?php echo htmlspecialchars(trim(preg_replace('/\s+division$/i', '', $districtObj['division'] ?? 'Bihar'))); ?> Division
                        </span>
                        <span class="badge bg-warning-subtle text-dark fw-bold px-2.5 py-1 rounded-pill small">
                            📍 <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block
                        </span>
                        <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                            District HQ: <?php echo htmlspecialchars($districtObj['headquarters'] ?? $districtObj['name']); ?>
                        </span>
                        <?php if (!empty($panchayatWardMembers) || !empty($panchayatPanchMembers)): ?>
                            <a href="#ward-roster" class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small text-decoration-none">
                                🗳️ <?php echo count($panchayatWardMembers); ?> वार्ड सदस्य &bull; <?php echo count($panchayatPanchMembers); ?> पंच
                            </a>
                        <?php endif; ?>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?>
                        <?php if (!empty($singlePanchayat['panchayat_hi']) && $singlePanchayat['panchayat_hi'] !== $singlePanchayat['panchayat_name']): ?>
                            <span class="text-muted fs-4 fw-normal">(<?php echo htmlspecialchars($singlePanchayat['panchayat_hi']); ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted small mb-0">
                        आधिकारिक ग्राम पंचायत प्रोफाइल &bull; त्रिस्तरीय पंचायती राज व्यवस्था (PRIs)
                    </p>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <a href="#ward-roster" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-bold btn-sm shadow-sm">
                            <i class="bi bi-grid-3x3-gap-fill me-1"></i> वार्ड रोस्टर
                        </a>
                        <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-warning rounded-pill px-3 py-1.5 fw-bold btn-sm text-dark shadow-sm">
                            <i class="bi bi-building-check me-1"></i> सभी <?php echo htmlspecialchars($singlePanchayat['block']); ?> पंचायतें
                        </a>
                        <a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                            सभी प्रखंड
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4 Core Governance Pillars: Mukhiya, Sarpanch, Samiti, Zila Parishad -->
        <div class="row g-4 mb-4">
            
            <!-- 1. MUKHIYA CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-mukhiya p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box bg-warning bg-opacity-10 text-warning">
                                👑
                            </div>
                            <div>
                                <span class="tier-header-badge bg-warning bg-opacity-15 text-dark mb-1">
                                    Tier 1 &bull; Village Executive
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Gram Mukhiya (मुखिया)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small">
                            Incumbent
                        </span>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <div class="text-muted small">निर्वाचित मुखिया Name</div>
                                <h5 class="fw-bold text-dark mb-0 fs-5">
                                    <?php echo htmlspecialchars($singlePanchayat['current_mukhiya'] ?: 'Details Available'); ?>
                                </h5>
                            </div>
                            <?php if (!empty($singlePanchayat['mukhiya_mobile'])): ?>
                                <div>
                                    <?php echo renderMaskedPhoneButton($singlePanchayat['mukhiya_mobile'], ($singlePanchayat['current_mukhiya'] ?? 'Mukhiya') . ' (Mukhiya - ' . $singlePanchayat['panchayat_name'] . ')'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($singlePanchayat['mukhiya_address'])): ?>
                            <div class="small text-muted border-top pt-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <strong>Address:</strong> <?php echo htmlspecialchars($singlePanchayat['mukhiya_address']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Mukhiya Profile Attributes -->
                    <div class="px-1 mb-3">
                        <?php if (!empty($singlePanchayat['mukhiya_father_husband'])): ?>
                            <div class="profile-prop-row">
                                <span class="text-muted"><i class="bi bi-person me-1"></i> Father / Husband:</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['mukhiya_father_husband']); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-gender-ambiguous me-1"></i> लिंग:</span>
                            <span class="fw-semibold text-dark">
                                <?php echo htmlspecialchars($singlePanchayat['mukhiya_gender_hi'] ?: $singlePanchayat['mukhiya_gender'] ?: 'N/A'); ?>
                            </span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-tag me-1"></i> Social Category:</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['mukhiya_category'] ?: 'General'); ?></span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-patch-check me-1"></i> Seat Reservation:</span>
                            <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">
                                <?php echo htmlspecialchars($singlePanchayat['mukhiya_reservation'] ?: $singlePanchayat['reservation_2026_mukhiya'] ?: 'General / Unreserved'); ?>
                            </span>
                        </div>
                    </div>

                    <!-- 2016 Ex-Mukhiya Archive Section -->
                    <?php if (!empty($singlePanchayat['mukhiya_2016'])): ?>
                        <div class="mt-auto pt-3 border-top">
                            <div class="p-2.5 bg-white rounded-3 border small">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="badge bg-secondary-subtle text-secondary fw-bold" style="font-size: 0.7rem;">2016–2021 Tenure Record</span>
                                    <?php if (!empty($singlePanchayat['mukhiya_2016_mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($singlePanchayat['mukhiya_2016_mobile'], $singlePanchayat['mukhiya_2016'] . ' (Ex-Mukhiya)'); ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="text-dark">
                                    <strong>Ex-Mukhiya:</strong> <?php echo htmlspecialchars($singlePanchayat['mukhiya_2016']); ?>
                                    <?php if (!empty($singlePanchayat['mukhiya_2016_f_name'])): ?>
                                        <span class="text-muted">(Father: <?php echo htmlspecialchars($singlePanchayat['mukhiya_2016_f_name']); ?>)</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. SARPANCH CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-sarpanch p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box bg-primary bg-opacity-10 text-primary">
                                ⚖️
                            </div>
                            <div>
                                <span class="tier-header-badge bg-primary bg-opacity-15 text-primary mb-1">
                                    Tier 1 &bull; Gram Kutchery Judiciary
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Gram Sarpanch (सरपंच)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            Judicial Head
                        </span>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                            <div>
                                <div class="text-muted small">Elected Sarpanch Name</div>
                                <h5 class="fw-bold text-dark mb-0 fs-5">
                                    <?php echo htmlspecialchars($singlePanchayat['current_sarpanch'] ?: 'Details Available'); ?>
                                </h5>
                            </div>
                            <?php if (!empty($singlePanchayat['sarpanch_mobile'])): ?>
                                <div>
                                    <?php echo renderMaskedPhoneButton($singlePanchayat['sarpanch_mobile'], ($singlePanchayat['current_sarpanch'] ?? 'Sarpanch') . ' (Sarpanch - ' . $singlePanchayat['panchayat_name'] . ')'); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($singlePanchayat['sarpanch_address'])): ?>
                            <div class="small text-muted border-top pt-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                                <strong>Address:</strong> <?php echo htmlspecialchars($singlePanchayat['sarpanch_address']); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Sarpanch Profile Attributes -->
                    <div class="px-1 mb-3">
                        <?php if (!empty($singlePanchayat['sarpanch_father_husband'])): ?>
                            <div class="profile-prop-row">
                                <span class="text-muted"><i class="bi bi-person me-1"></i> Father / Husband:</span>
                                <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['sarpanch_father_husband']); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-gender-ambiguous me-1"></i> लिंग:</span>
                            <span class="fw-semibold text-dark">
                                <?php echo htmlspecialchars($singlePanchayat['sarpanch_gender_hi'] ?: $singlePanchayat['sarpanch_gender'] ?: 'N/A'); ?>
                            </span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-tag me-1"></i> Social Category:</span>
                            <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['sarpanch_category'] ?: 'General'); ?></span>
                        </div>

                        <div class="profile-prop-row">
                            <span class="text-muted"><i class="bi bi-patch-check me-1"></i> Seat Reservation:</span>
                            <span class="badge bg-primary bg-opacity-25 text-navy fw-bold">
                                <?php echo htmlspecialchars($singlePanchayat['sarpanch_reservation'] ?: $singlePanchayat['reservation_2026_sarpanch'] ?: 'General / Unreserved'); ?>
                            </span>
                        </div>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">Presides over Gram Kutchery dispute resolutions</span>
                        <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                            All <?php echo htmlspecialchars($singlePanchayat['block']); ?> Panchayats &rarr;
                        </a>
                    </div>
                </div>
            </div>

            <!-- 3. PANCHAYAT SAMITI CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-samiti p-4 h-100 d-flex flex-column">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box bg-success bg-opacity-10 text-success">
                                🏛️
                            </div>
                            <div>
                                <span class="tier-header-badge bg-success bg-opacity-15 text-success mb-1">
                                    द्वितीय स्तर &bull; पंचायत समिति (2021–2026)
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    पंचायत समिति सदस्य (Panchayat Samiti Member)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2.5 py-1 rounded-pill small">
                            <?php echo htmlspecialchars($singlePanchayat['block']); ?> प्रखंड
                        </span>
                    </div>

                    <p class="text-muted small mb-3">
                        त्रिस्तरीय पंचायती राज व्यवस्था के अंतर्गत <strong><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?> ग्राम पंचायत</strong> का <strong><?php echo htmlspecialchars($singlePanchayat['block']); ?> पंचायत समिति</strong> में प्रतिनिधित्व।
                    </p>

                    <?php if (!empty($panchayatSamitiMembers)): ?>
                        <?php foreach ($panchayatSamitiMembers as $sm): ?>
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <div class="d-flex align-items-center gap-1 mb-1 flex-wrap">
                                            <span class="badge bg-success text-white fw-bold px-2 py-0.5 rounded-pill small">
                                                प्रा० नि० क्षेत्र संख्या: <?php echo htmlspecialchars($sm['territory_no'] ?: '—'); ?>
                                            </span>
                                            <?php if (!empty($sm['reservation_status'])): ?>
                                                <span class="badge bg-warning bg-opacity-25 text-dark fw-bold px-2 py-0.5 rounded-pill small">
                                                    <?php echo htmlspecialchars($sm['reservation_status']); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                        <h5 class="fw-bold text-navy mb-0 fs-5">
                                            <?php echo htmlspecialchars($sm['candidate_name']); ?>
                                        </h5>
                                        <?php if (!empty($sm['father_husband_name'])): ?>
                                            <span class="text-muted small d-block">
                                                पिता / पति: <?php echo htmlspecialchars($sm['father_husband_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($sm['mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($sm['mobile'], $sm['candidate_name'] . ' (पंचायत समिति सदस्य)'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="row g-2 pt-2 border-top small text-muted">
                                    <?php if (!empty($sm['gender'])): ?>
                                        <div class="col-6">
                                            <i class="bi bi-person me-1"></i>
                                            <?php echo htmlspecialchars($sm['gender']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($sm['category'])): ?>
                                        <div class="col-6">
                                            <i class="bi bi-tag me-1"></i> श्रेणी: <?php echo htmlspecialchars($sm['category']); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($sm['address'])): ?>
                                        <div class="col-12 text-truncate">
                                            <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($sm['address']); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <div class="mb-3">
                            <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($districtObj['slug']); ?>&block=<?php echo urlencode($singlePanchayat['block']); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                <i class="bi bi-people me-1"></i> सभी <?php echo htmlspecialchars($singlePanchayat['block']); ?> समिति सदस्य &rarr;
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <span class="text-muted small d-block">पंचायत समिति सदस्य (2021-2026)</span>
                            <div class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?> के निर्वाचित सदस्य</div>
                            <span class="small text-muted d-block mb-2">इस ग्राम पंचायत के समिति सदस्य का रिकॉर्ड अद्यतन किया जा रहा है।</span>
                            <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($districtObj['slug']); ?>&block=<?php echo urlencode($singlePanchayat['block']); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                <i class="bi bi-people me-1"></i> <?php echo htmlspecialchars($singlePanchayat['block']); ?> प्रखंड समिति सदस्य निर्देशिका
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Block Samiti Presiding Officers -->
                    <div class="p-2.5 bg-success bg-opacity-10 rounded-3 mb-3 border border-success border-opacity-25 small">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.72rem;">प्रखंड प्रमुख (BLOCK PRAMUKH)</span>
                                <strong class="text-navy"><?php echo htmlspecialchars($blockSamiti['pramukh_2016'] ?? 'प्रखंड प्रमुख'); ?></strong>
                            </div>
                            <?php if (!empty($blockSamiti['up_pramukh_2016'])): ?>
                                <div class="text-end border-start ps-3">
                                    <span class="text-muted d-block" style="font-size: 0.72rem;">उप-प्रमुख (UP-PRAMUKH)</span>
                                    <strong class="text-dark"><?php echo htmlspecialchars($blockSamiti['up_pramukh_2016']); ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="profile-prop-row">
                        <span class="text-muted"><i class="bi bi-geo me-1"></i> प्रखंड क्षेत्राधिकार:</span>
                        <span class="fw-semibold text-dark"><?php echo htmlspecialchars($singlePanchayat['block']); ?> प्रखंड</span>
                    </div>

                    <div class="profile-prop-row">
                        <span class="text-muted"><i class="bi bi-diagram-3 me-1"></i> प्रशासनिक स्तर:</span>
                        <span class="badge bg-success bg-opacity-25 text-success fw-bold">पंचायत समिति (प्रखंड स्तर)</span>
                    </div>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($districtObj['slug']); ?>&block=<?php echo urlencode($singlePanchayat['block']); ?>" class="btn btn-sm btn-success rounded-pill px-3 text-white fw-semibold">
                            समिति सदस्य &rarr;
                        </a>
                        <a href="<?php echo getBlockUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                            प्रखंड हब देखें
                        </a>
                    </div>
                </div>
            </div>

            <!-- 4. ZILA PARISHAD CARD -->
            <div class="col-12 col-lg-6">
                <div class="tier-card tier-card-zila p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="tier-avatar-box" style="background-color: #f3e8ff; color: #7c3aed;">
                                🏢
                            </div>
                            <div>
                                <span class="tier-header-badge bg-primary-subtle text-primary mb-1">
                                    Tier 3 &bull; Apex District Board
                                </span>
                                <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                                    Zila Parishad (जिला परिषद सदस्य)
                                </h4>
                            </div>
                        </div>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            <?php echo htmlspecialchars($districtObj['name']); ?> ZP
                        </span>
                    </div>

                    <?php if (!empty($zilaParishadMembers)): ?>
                        <div class="mb-3">
                            <div class="small text-muted mb-2">
                                <?php if ($isSpecificZilaTerritory): ?>
                                    Territorial Member representing Territory No. <strong><?php echo htmlspecialchars($terrNo); ?></strong>:
                                <?php else: ?>
                                    Elected Zila Parishad Territorial Members representing <strong><?php echo htmlspecialchars($singlePanchayat['block']); ?> Block</strong>:
                                <?php endif; ?>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <?php foreach ($zilaParishadMembers as $zm): ?>
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-2">
                                            <div>
                                                <div class="fw-bold text-navy fs-6">
                                                    <?php echo htmlspecialchars($zm['candidate_name'] ?? $zm['member_name'] ?? 'Zila Parishad Member'); ?>
                                                </div>
                                                <div class="small text-muted">
                                                    Territory No: <strong><?php echo htmlspecialchars($zm['territory_no'] ?? $zm['ward_no'] ?? 'N/A'); ?></strong>
                                                    &bull; Category: <strong><?php echo htmlspecialchars($zm['category'] ?? 'General'); ?></strong>
                                                    <?php if (!empty($zm['gender'])): ?>
                                                        &bull; <?php echo htmlspecialchars($zm['gender']); ?>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php if (!empty($zm['mobile'])): ?>
                                                <div>
                                                    <?php echo renderMaskedPhoneButton($zm['mobile'], ($zm['candidate_name'] ?? 'ZP Member') . ' (ZP Territory ' . ($zm['territory_no'] ?? '') . ')'); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <?php if (!empty($zm['address'])): ?>
                                            <div class="small text-muted border-top pt-1 mt-1">
                                                <i class="bi bi-geo-alt me-1 text-danger"></i> <?php echo htmlspecialchars($zm['address']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-light rounded-3 mb-3 border text-center py-4">
                            <i class="bi bi-bank text-muted fs-2 d-block mb-1"></i>
                            <div class="fw-bold text-dark mb-1">Zila Parishad Territorial Representative</div>
                            <p class="text-muted small mb-0">Under <?php echo htmlspecialchars($districtObj['name']); ?> Zila Parishad Board jurisdiction.</p>
                        </div>
                    <?php endif; ?>

                    <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center">
                        <span class="small text-muted">बिहार में पंचायती राज की सर्वोच्च संस्था</span>
                        <a href="<?php echo getZilaParishadUrl($districtObj['slug']); ?>" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold shadow-sm">
                            जिला परिषद बोर्ड &rarr;
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php 
        // Build combined ward numbers
        $allWardNumbers = [];
        foreach ($panchayatWardMembers as $w) {
            $wn = intval($w['ward_no']);
            if ($wn > 0) $allWardNumbers[$wn] = true;
        }
        foreach ($panchayatPanchMembers as $p) {
            $wn = intval($p['ward_no']);
            if ($wn > 0) $allWardNumbers[$wn] = true;
        }
        $sortedWards = array_keys($allWardNumbers);
        sort($sortedWards, SORT_NUMERIC);

        // Index for direct lookup
        $wardMemberByWard = [];
        foreach ($panchayatWardMembers as $w) {
            $wn = intval($w['ward_no']);
            if ($wn > 0 && !isset($wardMemberByWard[$wn])) {
                $wardMemberByWard[$wn] = $w;
            }
        }
        $panchByWard = [];
        foreach ($panchayatPanchMembers as $p) {
            $wn = intval($p['ward_no']);
            if ($wn > 0 && !isset($panchByWard[$wn])) {
                $panchByWard[$wn] = $p;
            }
        }
        ?>

        <!-- ============================================================= -->
        <!-- WARD MEMBERS & GRAM KUTCHERY PANCH DIRECTORY (2021-2026)      -->
        <!-- ============================================================= -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-primary" id="ward-roster">
            <div class="d-flex justify-content-between align-items-start mb-3 flex-wrap gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill small">
                            बिहार पंचायती राज चुनाव (2021-2026)
                        </span>
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            <?php echo count($sortedWards) ?: max(count($panchayatWardMembers), count($panchayatPanchMembers)); ?> वार्ड
                        </span>
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small">
                            SEC बिहार प्रमाणित डेटा
                        </span>
                    </div>
                    <h3 class="fw-bold text-navy font-heading mb-1 fs-4">
                        🗳️ वार्ड सदस्य एवं ग्राम कचहरी पंच निर्देशिका
                    </h3>
                    <p class="text-muted small mb-0">
                        ग्राम पंचायत <strong><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?></strong> के सभी निर्वाचित वार्ड सदस्य (वार्ड क्रियान्वयन एवं प्रबंधन समिति - WIMC) और ग्राम कचहरी पंचों की आधिकारिक सूची।
                    </p>
                </div>
                <div class="text-end">
                    <span class="badge bg-light text-muted border px-3 py-2 rounded-pill small">
                        कार्यकाल: 2021 &ndash; 2026
                    </span>
                </div>
            </div>

            <?php if (!empty($sortedWards) || !empty($panchayatWardMembers) || !empty($panchayatPanchMembers)): ?>

            <!-- Controls: Search & Tabs -->
            <div class="row g-3 align-items-center justify-content-between mb-4">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="wardSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="वार्ड संख्या, प्रत्याशी का नाम, श्रेणी खोजें..." onkeyup="filterWardRoster(this.value)">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <ul class="nav nav-pills justify-content-md-end gap-1" id="wardRosterTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-3 py-1.5 fw-semibold small" id="tab-combined-btn" data-bs-toggle="pill" data-bs-target="#tab-combined" type="button" role="tab">
                                <i class="bi bi-grid-3x3-gap-fill me-1"></i> वार्ड-वार तुलना
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-1.5 fw-semibold small" id="tab-wards-btn" data-bs-toggle="pill" data-bs-target="#tab-wards" type="button" role="tab">
                                🗳️ वार्ड सदस्य (<?php echo count($panchayatWardMembers); ?>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-3 py-1.5 fw-semibold small" id="tab-panch-btn" data-bs-toggle="pill" data-bs-target="#tab-panch" type="button" role="tab">
                                ⚖️ कचहरी पंच (<?php echo count($panchayatPanchMembers); ?>)
                            </button>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="tab-content" id="wardRosterTabContent">

                <!-- TAB 1: COMBINED WARD-BY-WARD COMPARISON -->
                <div class="tab-pane fade show active" id="tab-combined" role="tabpanel">
                    <div class="row g-3" id="combinedWardsGrid">
                        <?php foreach ($sortedWards as $wn): 
                            $wm = $wardMemberByWard[$wn] ?? null;
                            $pm = $panchByWard[$wn] ?? null;
                            $searchTerms = strtolower("ward {$wn} वार्ड {$wn} " . 
                                ($wm ? "{$wm['candidate_name']} {$wm['father_husband_name']} {$wm['category']} {$wm['reservation_status']} " : '') . 
                                ($pm ? "{$pm['candidate_name']} {$pm['father_husband_name']} {$pm['category']} {$pm['reservation_status']}" : '')
                            );
                        ?>
                            <div class="col-12 col-lg-6 ward-roster-item" data-search="<?php echo htmlspecialchars($searchTerms, ENT_QUOTES); ?>">
                                <div class="border rounded-4 p-3 bg-white h-100 shadow-sm hover-shadow transition">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white fw-bold px-3 py-1.5 rounded-pill fs-6">
                                                वार्ड <?php echo $wn; ?>
                                            </span>
                                            <span class="text-muted extra-small">ग्राम पंचायत वार्ड क्षेत्र</span>
                                        </div>
                                        <span class="extra-small text-muted fw-semibold">
                                            <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?>
                                        </span>
                                    </div>

                                    <div class="row g-2">
                                        <!-- Ward Member Column -->
                                        <div class="col-12 col-sm-6 border-end-sm">
                                            <div class="p-2.5 rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <span class="badge bg-primary bg-opacity-15 text-primary fw-bold extra-small rounded-pill">
                                                            वार्ड सदस्य (WIMC)
                                                        </span>
                                                        <?php if ($wm && !empty($wm['reservation_status'])): ?>
                                                            <span class="badge bg-white text-muted border extra-small">
                                                                <?php echo htmlspecialchars($wm['reservation_status']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($wm): ?>
                                                        <div class="fw-bold text-navy fs-6 mt-1 mb-0"><?php echo htmlspecialchars($wm['candidate_name']); ?></div>
                                                        <div class="text-muted extra-small mb-1.5">
                                                            पिता/पति: <?php echo htmlspecialchars($wm['father_husband_name'] ?: '—'); ?>
                                                        </div>

                                                        <div class="extra-small text-secondary mb-1">
                                                            <span class="me-2"><i class="bi bi-tag text-primary me-0.5"></i> <?php echo htmlspecialchars($wm['category'] ?: 'सामान्य'); ?></span>
                                                            <?php if (!empty($wm['gender'])): ?>
                                                                <span><i class="bi bi-person me-0.5"></i> <?php echo htmlspecialchars($wm['gender']); ?></span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if (!empty($wm['address'])): ?>
                                                            <div class="extra-small text-muted text-truncate" title="<?php echo htmlspecialchars($wm['address']); ?>">
                                                                <i class="bi bi-geo-alt me-0.5 text-danger"></i> <?php echo htmlspecialchars($wm['address']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-muted small py-3 text-center">
                                                            <i class="bi bi-dash-circle text-muted d-block mb-1"></i>
                                                            विवरण उपलब्ध नहीं
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mt-2 pt-2 border-top text-end">
                                                    <?php if ($wm && !empty($wm['mobile'])): ?>
                                                        <?php echo renderMaskedPhoneButton($wm['mobile'], $wm['candidate_name'] . ' (वार्ड ' . $wn . ' सदस्य)'); ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border extra-small">प्रमाणित</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Gram Kutchery Panch Column -->
                                        <div class="col-12 col-sm-6">
                                            <div class="p-2.5 rounded-3 bg-light h-100 d-flex flex-column justify-content-between">
                                                <div>
                                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                                        <span class="badge bg-success bg-opacity-15 text-success fw-bold extra-small rounded-pill">
                                                            ग्राम कचहरी पंच
                                                        </span>
                                                        <?php if ($pm && !empty($pm['reservation_status'])): ?>
                                                            <span class="badge bg-white text-muted border extra-small">
                                                                <?php echo htmlspecialchars($pm['reservation_status']); ?>
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>

                                                    <?php if ($pm): ?>
                                                        <div class="fw-bold text-navy fs-6 mt-1 mb-0"><?php echo htmlspecialchars($pm['candidate_name']); ?></div>
                                                        <div class="text-muted extra-small mb-1.5">
                                                            पिता/पति: <?php echo htmlspecialchars($pm['father_husband_name'] ?: '—'); ?>
                                                        </div>

                                                        <div class="extra-small text-secondary mb-1">
                                                            <span class="me-2"><i class="bi bi-tag text-success me-0.5"></i> <?php echo htmlspecialchars($pm['category'] ?: 'सामान्य'); ?></span>
                                                            <?php if (!empty($pm['gender'])): ?>
                                                                <span><i class="bi bi-person me-0.5"></i> <?php echo htmlspecialchars($pm['gender']); ?></span>
                                                            <?php endif; ?>
                                                        </div>

                                                        <?php if (!empty($pm['address'])): ?>
                                                            <div class="extra-small text-muted text-truncate" title="<?php echo htmlspecialchars($pm['address']); ?>">
                                                                <i class="bi bi-geo-alt me-0.5 text-danger"></i> <?php echo htmlspecialchars($pm['address']); ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    <?php else: ?>
                                                        <div class="text-muted small py-3 text-center">
                                                            <i class="bi bi-dash-circle text-muted d-block mb-1"></i>
                                                            विवरण उपलब्ध नहीं
                                                        </div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="mt-2 pt-2 border-top text-end">
                                                    <?php if ($pm && !empty($pm['mobile'])): ?>
                                                        <?php echo renderMaskedPhoneButton($pm['mobile'], $pm['candidate_name'] . ' (वार्ड ' . $wn . ' पंच)'); ?>
                                                    <?php else: ?>
                                                        <span class="badge bg-light text-muted border extra-small">प्रमाणित</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TAB 2: GRAM PANCHAYAT WARD MEMBERS (TABLE VIEW) -->
                <div class="tab-pane fade" id="tab-wards" role="tabpanel">
                    <div class="table-responsive border rounded-3 bg-white">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light extra-small text-uppercase text-secondary">
                                <tr>
                                    <th class="ps-3 py-3" style="width: 100px;">वार्ड सं.</th>
                                    <th class="py-3">वार्ड सदस्य का नाम</th>
                                    <th class="py-3">पिता / पति का नाम</th>
                                    <th class="py-3">आरक्षण स्थिति</th>
                                    <th class="py-3">श्रेणी</th>
                                    <th class="py-3">लिंग</th>
                                    <th class="py-3">पता</th>
                                    <th class="py-3 text-end pe-3">सम्पर्क</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($panchayatWardMembers as $w): 
                                    $wSearch = strtolower("ward {$w['ward_no']} वार्ड {$w['ward_no']} {$w['candidate_name']} {$w['father_husband_name']} {$w['category']} {$w['reservation_status']}");
                                ?>
                                    <tr class="ward-roster-item" data-search="<?php echo htmlspecialchars($wSearch, ENT_QUOTES); ?>">
                                        <td class="ps-3">
                                            <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill small">
                                                वार्ड <?php echo htmlspecialchars($w['ward_no']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy fs-6"><?php echo htmlspecialchars($w['candidate_name']); ?></div>
                                            <span class="text-muted extra-small">वार्ड सदस्य (WIMC प्रमुख)</span>
                                        </td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($w['father_husband_name'] ?: '—'); ?></td>
                                        <td>
                                            <span class="badge bg-warning bg-opacity-25 text-dark fw-bold extra-small rounded-pill">
                                                <?php echo htmlspecialchars($w['reservation_status'] ?: 'सामान्य'); ?>
                                            </span>
                                        </td>
                                        <td class="small text-dark"><?php echo htmlspecialchars($w['category'] ?: '—'); ?></td>
                                        <td class="small text-muted">
                                            <?php echo htmlspecialchars($w['gender'] ?? '—'); ?>
                                        </td>
                                        <td class="extra-small text-muted" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($w['address'] ?: '—'); ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if (!empty($w['mobile'])): ?>
                                                <?php echo renderMaskedPhoneButton($w['mobile'], $w['candidate_name'] . ' (वार्ड ' . $w['ward_no'] . ' सदस्य)'); ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border extra-small">प्रमाणित</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- TAB 3: GRAM KUTCHERY PANCH (TABLE VIEW) -->
                <div class="tab-pane fade" id="tab-panch" role="tabpanel">
                    <div class="table-responsive border rounded-3 bg-white">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light extra-small text-uppercase text-secondary">
                                <tr>
                                    <th class="ps-3 py-3" style="width: 100px;">वार्ड सं.</th>
                                    <th class="py-3">ग्राम पंच का नाम</th>
                                    <th class="py-3">पिता / पति का नाम</th>
                                    <th class="py-3">आरक्षण स्थिति</th>
                                    <th class="py-3">श्रेणी</th>
                                    <th class="py-3">लिंग</th>
                                    <th class="py-3">पता</th>
                                    <th class="py-3 text-end pe-3">सम्पर्क</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($panchayatPanchMembers as $p): 
                                    $pSearch = strtolower("ward {$p['ward_no']} वार्ड {$p['ward_no']} {$p['candidate_name']} {$p['father_husband_name']} {$p['category']} {$p['reservation_status']}");
                                ?>
                                    <tr class="ward-roster-item" data-search="<?php echo htmlspecialchars($pSearch, ENT_QUOTES); ?>">
                                        <td class="ps-3">
                                            <span class="badge bg-success text-white fw-bold px-2.5 py-1 rounded-pill small">
                                                वार्ड <?php echo htmlspecialchars($p['ward_no']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-navy fs-6"><?php echo htmlspecialchars($p['candidate_name']); ?></div>
                                            <span class="text-muted extra-small">ग्राम कचहरी पंच</span>
                                        </td>
                                        <td class="small text-muted"><?php echo htmlspecialchars($p['father_husband_name'] ?: '—'); ?></td>
                                        <td>
                                            <span class="badge bg-info bg-opacity-25 text-navy fw-bold extra-small rounded-pill">
                                                <?php echo htmlspecialchars($p['reservation_status'] ?: 'सामान्य'); ?>
                                            </span>
                                        </td>
                                        <td class="small text-dark"><?php echo htmlspecialchars($p['category'] ?: '—'); ?></td>
                                        <td class="small text-muted">
                                            <?php echo htmlspecialchars($p['gender'] ?? '—'); ?>
                                        </td>
                                        <td class="extra-small text-muted" style="max-width: 220px;">
                                            <?php echo htmlspecialchars($p['address'] ?: '—'); ?>
                                        </td>
                                        <td class="text-end pe-3">
                                            <?php if (!empty($p['mobile'])): ?>
                                                <?php echo renderMaskedPhoneButton($p['mobile'], $p['candidate_name'] . ' (वार्ड ' . $p['ward_no'] . ' पंच)'); ?>
                                            <?php else: ?>
                                                <span class="badge bg-light text-muted border extra-small">प्रमाणित</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <script>
            function filterWardRoster(query) {
                const q = (query || '').toLowerCase().trim();
                const items = document.querySelectorAll('.ward-roster-item');
                items.forEach(el => {
                    const search = (el.getAttribute('data-search') || '').toLowerCase();
                    if (!q || search.includes(q)) {
                        el.classList.remove('d-none');
                    } else {
                        el.classList.add('d-none');
                    }
                });
            }
            </script>

            <?php else: ?>
                <div class="p-4 bg-light rounded-4 text-center border">
                    <i class="bi bi-grid-3x3-gap text-muted fs-1 d-block mb-2"></i>
                    <h5 class="fw-bold text-navy mb-1">वार्ड सदस्य एवं पंच निर्देशिका</h5>
                    <p class="text-muted small mb-3" style="max-width: 580px; margin: 0 auto;">
                        ग्राम पंचायत <strong><?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?></strong> के वार्ड सदस्यों और पंचों का आधिकारिक रोस्टर राज्य निर्वाचन आयोग बिहार के रिकॉर्ड से मैप किया जा रहा है।
                    </p>
                    <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        सभी <?php echo htmlspecialchars($singlePanchayat['block']); ?> पंचायतें देखें &rarr;
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <!-- Constituent Census Villages Section -->
        <?php if (!empty($panchayatVillages)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            Census 2011 Data
                        </span>
                        <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                            🏡 Census Villages in <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?> Gram Panchayat (<?php echo count($panchayatVillages); ?>)
                        </h4>
                        <p class="text-muted small mb-0 mt-1">Official Census 2011 constituent revenue villages, demographics, and infrastructure profiles.</p>
                    </div>
                    <a href="<?php echo getVillageUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                        All <?php echo htmlspecialchars($singlePanchayat['block']); ?> Villages &rarr;
                    </a>
                </div>

                <div class="row g-3">
                    <?php foreach ($panchayatVillages as $pv): 
                        $vUrl = getVillageUrl($districtObj['slug'], slugify($pv['sub_district_name'] ?: $singlePanchayat['block']), $pv['village_slug']);
                        $vPop = (int)($pv['population'] ?? 0);
                        $vHh = (int)($pv['households'] ?? 0);
                        $vSexRatio = (int)($pv['sex_ratio'] ?? 0);
                    ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100 d-flex flex-column justify-content-between hover-shadow transition">
                                <div>
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <span class="badge bg-primary-subtle text-primary small font-monospace">Code: <?php echo htmlspecialchars($pv['village_code']); ?></span>
                                        <?php if (!empty($pv['area_hectares'])): ?>
                                            <span class="badge bg-secondary-subtle text-secondary small"><?php echo number_format((float)$pv['area_hectares'], 1); ?> Ha</span>
                                        <?php endif; ?>
                                    </div>
                                    <h5 class="fw-bold text-navy mb-1 fs-6">
                                        <a href="<?php echo htmlspecialchars($vUrl); ?>" class="text-decoration-none text-navy stretched-link-target">
                                            🏡 <?php echo htmlspecialchars($pv['village_name']); ?>
                                        </a>
                                    </h5>
                                    <div class="small text-muted mb-2">
                                        <span>👥 <?php echo number_format($vPop); ?> Pop.</span> • 
                                        <span>🏠 <?php echo number_format($vHh); ?> HH</span> • 
                                        <span>⚖️ <?php echo $vSexRatio; ?> SR</span>
                                    </div>
                                </div>
                                <div class="pt-2 border-top d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Village Census Profile</span>
                                    <a href="<?php echo htmlspecialchars($vUrl); ?>" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 text-xs fw-bold">
                                        View &rarr;
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Historical Census 1991 Data & 20-Year Growth Trajectory Section -->
        <?php if (!empty($panchayatVillages1991)): ?>
            <?php 
                $pop2011Total = 0;
                $hh2011Total = 0;
                if (!empty($panchayatVillages)) {
                    foreach ($panchayatVillages as $pv) {
                        $pop2011Total += (int)($pv['population'] ?? 0);
                        $hh2011Total += (int)($pv['households'] ?? 0);
                    }
                }
                $pop1991Total = $panchayat1991Stats['population'];
                $hh1991Total = $panchayat1991Stats['households'];
                $growthPct = ($pop1991Total > 0 && $pop2011Total > 0) ? round((($pop2011Total - $pop1991Total) / $pop1991Total) * 100, 1) : null;
                $litRate1991 = ($pop1991Total > 0) ? round(($panchayat1991Stats['literates'] / $pop1991Total) * 100, 1) : 0;
            ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            📜 Historical 1991 Census Data
                        </span>
                        <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                            Historical 1991 Census & 20-Year Growth: <?php echo htmlspecialchars($singlePanchayat['panchayat_name']); ?>
                        </h4>
                        <p class="text-muted small mb-0 mt-1">
                            Official Primary Census Abstract (PCA) 1991 rural demographic records & 1991-2011 population shift.
                        </p>
                    </div>
                    <?php if ($growthPct !== null): ?>
                        <span class="badge <?php echo ($growthPct >= 0) ? 'bg-success' : 'bg-danger'; ?> fs-6 px-3 py-2 rounded-pill">
                            <i class="bi bi-graph-up-arrow me-1"></i> <?php echo ($growthPct >= 0 ? '+' : '') . $growthPct; ?>% Growth (1991–2011)
                        </span>
                    <?php endif; ?>
                </div>

                <!-- 1991 Demographic Metric Grid -->
                <div class="row g-3 mb-4">
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small d-block">1991 Population</span>
                            <div class="h4 fw-bold text-navy mb-0"><?php echo number_format($pop1991Total); ?></div>
                            <small class="text-muted"><?php echo number_format($panchayat1991Stats['male']); ?> M / <?php echo number_format($panchayat1991Stats['female']); ?> F</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small d-block">1991 Households</span>
                            <div class="h4 fw-bold text-dark mb-0"><?php echo number_format($hh1991Total); ?></div>
                            <small class="text-muted"><?php echo count($panchayatVillages1991); ?> Revenue Villages</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small d-block">1991 Literates</span>
                            <div class="h4 fw-bold text-primary mb-0"><?php echo number_format($panchayat1991Stats['literates']); ?></div>
                            <small class="text-muted"><?php echo $litRate1991; ?>% Literacy in 1991</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 bg-light rounded-3 border text-center">
                            <span class="text-muted small d-block">1991 Agri Workers</span>
                            <div class="h4 fw-bold text-success mb-0"><?php echo number_format($panchayat1991Stats['cultivators'] + $panchayat1991Stats['agri_labour']); ?></div>
                            <small class="text-muted"><?php echo number_format($panchayat1991Stats['cultivators']); ?> Cult. / <?php echo number_format($panchayat1991Stats['agri_labour']); ?> Labour</small>
                        </div>
                    </div>
                </div>

                <!-- 1991 Villages List Table -->
                <h6 class="fw-bold text-navy mb-2"><i class="bi bi-houses me-1"></i> 1991 Constituent Revenue Villages:</h6>
                <div class="table-responsive">
                    <table class="table table-hover table-sm align-middle mb-0 small">
                        <thead class="table-light">
                            <tr>
                                <th>1991 Village Name</th>
                                <th>Village Code</th>
                                <th>1991 Population</th>
                                <th>Male / Female</th>
                                <th>Households</th>
                                <th>Area (Ha)</th>
                                <th>SC / ST</th>
                                <th>Literates</th>
                                <th>Cultivators & Labourers</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($panchayatVillages1991 as $v91): ?>
                                <tr>
                                    <td class="fw-bold text-dark">
                                        🏡 <?php echo htmlspecialchars($v91['village_name']); ?>
                                    </td>
                                    <td><code><?php echo htmlspecialchars($v91['village_code']); ?></code></td>
                                    <td class="fw-bold text-navy"><?php echo number_format((int)$v91['population']); ?></td>
                                    <td><?php echo number_format((int)$v91['male']); ?> / <?php echo number_format((int)$v91['female']); ?></td>
                                    <td><?php echo number_format((int)$v91['households']); ?></td>
                                    <td><?php echo number_format((float)$v91['area_hectares'], 1); ?></td>
                                    <td><?php echo number_format((int)$v91['sc_population']); ?> / <?php echo number_format((int)$v91['st_population']); ?></td>
                                    <td><?php echo number_format((int)$v91['literates_total']); ?></td>
                                    <td><?php echo number_format((int)$v91['m_cultivators'] + (int)$v91['f_cultivators']); ?> Cult. + <?php echo number_format((int)$v91['m_agri_labour'] + (int)$v91['f_agri_labour']); ?> Lab.</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

        <!-- Sibling Panchayats in this Block Navigation Section -->
        <?php if (!empty($panchayatsInSameBlock)): ?>
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                    <div>
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small mb-1">
                            Block Navigation
                        </span>
                        <h4 class="fw-bold text-navy font-heading mb-0 fs-5">
                            Other Gram Panchayats in <?php echo htmlspecialchars($singlePanchayat['block']); ?> Block (<?php echo count($panchayatsInSameBlock); ?>)
                        </h4>
                    </div>
                    <a href="<?php echo getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block'])); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        View All in <?php echo htmlspecialchars($singlePanchayat['block']); ?> &rarr;
                    </a>
                </div>
                
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($panchayatsInSameBlock as $sb): 
                        $sbUrl = getPanchayatUrl($districtObj['slug'], slugify($singlePanchayat['block']), slugify($sb['panchayat_name']));
                    ?>
                        <a href="<?php echo htmlspecialchars($sbUrl); ?>" class="sibling-panchayat-pill">
                            🌾 <?php echo htmlspecialchars($sb['panchayat_name']); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php elseif ($singleBlockObj && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- 2. DEDICATED BLOCK PANCHAYATS DIRECTORY VIEW                  -->
        <!-- ============================================================= -->
        <?php 
        $bName = $singleBlockObj['sub_district'];
        $bSlug = slugify($bName);
        $bPop = intval($singleBlockObj['population'] ?? 0);
        $bLit = floatval($singleBlockObj['literacy_rate'] ?? 0);
        $bHouseholds = intval($singleBlockObj['households'] ?? 0);
        $bCode = $singleBlockObj['sub_dist_code'] ?? 'N/A';
        ?>

        <!-- Block Header & Summary Card -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">
                            📍 CD Block Directory
                        </span>
                        <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">
                            Code: <?php echo htmlspecialchars($bCode); ?>
                        </span>
                        <span class="badge bg-success-subtle text-success fw-bold px-2.5 py-1 rounded-pill small">
                            🏢 <?php echo htmlspecialchars($districtObj['name']); ?> District
                        </span>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($bName); ?> Block Gram Panchayats
                    </h2>
                    <p class="text-muted small mb-0">
                        Official directory of all <?php echo count($panchayatsInBlock); ?> Gram Panchayats, Mukhiyas, and Sarpanchs in <?php echo htmlspecialchars($bName); ?> Block.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo getPanchayatUrl($districtObj['slug']); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-arrow-left me-1"></i> All <?php echo htmlspecialchars($districtObj['name']); ?> Blocks
                    </a>
                    <a href="<?php echo getBlockUrl($districtObj['slug'], $bSlug); ?>" class="btn btn-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm shadow-sm">
                        <i class="bi bi-geo-alt-fill me-1"></i> Block Demographics
                    </a>
                </div>
            </div>

            <!-- Block Quick Metrics -->
            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-building-check text-success fs-4 d-block mb-1"></i>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block">पंचायतें</span>
                        <span class="fs-5 fw-bold text-success"><?php echo count($panchayatsInBlock); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-grid-3x3-gap-fill text-primary fs-4 d-block mb-1"></i>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block">वार्ड सदस्य</span>
                        <span class="fs-5 fw-bold text-primary"><?php echo $blockWardCount ? number_format($blockWardCount) : 'मैप किया जा रहा'; ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-award-fill text-warning-emphasis fs-4 d-block mb-1"></i>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block">कचहरी पंच</span>
                        <span class="fs-5 fw-bold text-dark"><?php echo $blockPanchCount ? number_format($blockPanchCount) : 'मैप किया जा रहा'; ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-people-fill text-info fs-4 d-block mb-1"></i>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block">कुल आबादी</span>
                        <span class="fs-5 fw-bold text-dark"><?php echo number_format($bPop); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-book-half text-warning fs-4 d-block mb-1"></i>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block">साक्षरता दर</span>
                        <span class="fs-5 fw-bold text-dark"><?php echo $bLit ? "{$bLit}%" : 'N/A'; ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-2">
                    <div class="bg-light rounded-3 p-3 border text-center h-100">
                        <i class="bi bi-house-door-fill text-secondary fs-4 d-block mb-1"></i>
                        <span class="text-muted extra-small fw-bold text-uppercase d-block">कुल परिवार</span>
                        <span class="fs-5 fw-bold text-dark"><?php echo number_format($bHouseholds); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar for Panchayats in this Block -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="panchayatSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Search panchayat, mukhiya, or sarpanch..." onkeyup="filterBlockPanchayats()">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <span class="badge bg-success text-white fw-bold px-3 py-2 rounded-pill fs-6" id="panchayatCountBadge">
                        Showing <?php echo count($panchayatsInBlock); ?> Panchayats
                    </span>
                </div>
            </div>
        </div>

        <!-- Grid of Panchayats in this Block -->
        <div class="row g-3 g-lg-4" id="panchayatsGrid">
            <?php foreach ($panchayatsInBlock as $p): 
                $pSlug = slugify($p['panchayat_name']);
                $pProfileUrl = getPanchayatUrl($districtObj['slug'], $bSlug, $pSlug);
                $pSearch = strtolower(($p['panchayat_name'] ?? '') . ' ' . ($p['current_mukhiya'] ?? '') . ' ' . ($p['current_sarpanch'] ?? '') . ' ' . ($p['mukhiya_category'] ?? ''));
            ?>
                <div class="col-12 col-md-6 col-lg-4 panchayat-card-item" data-search="<?php echo htmlspecialchars($pSearch, ENT_QUOTES); ?>">
                    <div class="admin-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-warning-subtle text-dark fw-bold px-2 py-0.5 rounded small mb-1" style="font-size: 0.72rem;">
                                        🌾 Gram Panchayat
                                    </span>
                                    <h4 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($pProfileUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                            <?php echo htmlspecialchars($p['panchayat_name']); ?>
                                        </a>
                                    </h4>
                                </div>
                                <?php if (!empty($p['reservation_2026_mukhiya'])): ?>
                                    <span class="badge bg-light text-muted border small px-2 py-1" style="font-size: 0.7rem;">
                                        <?php echo htmlspecialchars($p['reservation_2026_mukhiya']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>

                            <!-- Leaders Mini Roster -->
                            <div class="bg-light rounded-3 p-3 my-3 border small">
                                <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">👑 Mukhiya (मुखिया):</span>
                                        <strong class="text-dark fs-6"><?php echo htmlspecialchars($p['current_mukhiya'] ?: 'Details Available'); ?></strong>
                                    </div>
                                    <?php if (!empty($p['mukhiya_mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($p['mukhiya_mobile'], $p['current_mukhiya'] . ' (Mukhiya)'); ?></div>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">⚖️ Sarpanch (सरपंच):</span>
                                        <strong class="text-dark fs-6"><?php echo htmlspecialchars($p['current_sarpanch'] ?: 'Details Available'); ?></strong>
                                    </div>
                                    <?php if (!empty($p['sarpanch_mobile'])): ?>
                                        <div><?php echo renderMaskedPhoneButton($p['sarpanch_mobile'], $p['current_sarpanch'] . ' (Sarpanch)'); ?></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Link -->
                        <div class="mt-auto pt-3 border-top">
                            <?php 
                            $pWCount = $blockWardCountsByPanchayat[trim($p['panchayat_name'])] ?? 0;
                            $pPCount = $blockPanchCountsByPanchayat[trim($p['panchayat_name'])] ?? 0;
                            if ($pWCount > 0 || $pPCount > 0): ?>
                                <div class="d-flex align-items-center gap-1.5 mb-2 extra-small flex-wrap">
                                    <span class="badge bg-primary-subtle text-primary px-2 py-0.5 rounded-pill">
                                        <i class="bi bi-grid-3x3-gap-fill me-1"></i><?php echo $pWCount; ?> वार्ड
                                    </span>
                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 rounded-pill">
                                        <i class="bi bi-award-fill me-1"></i><?php echo $pPCount; ?> पंच
                                    </span>
                                </div>
                            <?php endif; ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="small text-muted">Tier 1 PRI Body</span>
                                <a href="<?php echo htmlspecialchars($pProfileUrl); ?>" class="btn btn-sm btn-primary fw-bold text-white rounded-pill px-3 py-1.5 shadow-sm">
                                    प्रोफाइल देखें <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Block Panchayat Samiti Members Roster (2021-2026) -->
        <?php if (!empty($blockSamitiMembers)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 mt-4 mb-4 bg-white border-top border-4 border-success">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success bg-opacity-15 text-success fw-bold px-2.5 py-1 rounded-pill small">
                            🏛️ द्वितीय स्तर पंचायत समिति
                        </span>
                        <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill small">
                            कार्यकाल: 2021–2026
                        </span>
                    </div>
                    <h3 class="fw-bold font-heading text-navy fs-4 mb-0">
                        <?php echo htmlspecialchars($bName); ?> पंचायत समिति सदस्य (Panchayat Samiti Members)
                    </h3>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success text-white fw-bold px-3 py-2 rounded-pill">
                        <?php echo count($blockSamitiMembers); ?> निर्वाचित सदस्य
                    </span>
                    <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($districtObj['slug']); ?>&block=<?php echo urlencode($bName); ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
                        सम्पूर्ण समिति रोस्टर &rarr;
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-2.5 px-3 text-navy small fw-bold text-uppercase">प्रा० नि० क्षेत्र सं०</th>
                            <th class="py-2.5 px-3 text-navy small fw-bold text-uppercase">ग्राम पंचायत</th>
                            <th class="py-2.5 px-3 text-navy small fw-bold text-uppercase">निर्वाचित सदस्य</th>
                            <th class="py-2.5 px-3 text-navy small fw-bold text-uppercase">पिता / पति</th>
                            <th class="py-2.5 px-3 text-navy small fw-bold text-uppercase">आरक्षण श्रेणी</th>
                            <th class="py-2.5 px-3 text-navy small fw-bold text-uppercase text-center">संपर्क</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blockSamitiMembers as $bm): ?>
                            <tr>
                                <td class="px-3 fw-bold text-success">
                                    <span class="badge bg-success bg-opacity-15 text-success px-2 py-1 rounded-pill">
                                        #<?php echo htmlspecialchars($bm['territory_no'] ?: '—'); ?>
                                    </span>
                                </td>
                                <td class="px-3 fw-semibold text-navy">
                                    <?php 
                                    $gpSlug = slugify($bm['panchayat']);
                                    ?>
                                    <a href="<?php echo getPanchayatUrl($districtObj['slug'], $bSlug, $gpSlug); ?>" class="text-decoration-none text-navy hover-primary">
                                        🌾 <?php echo htmlspecialchars($bm['panchayat']); ?>
                                    </a>
                                </td>
                                <td class="px-3">
                                    <strong class="text-dark d-block"><?php echo htmlspecialchars($bm['candidate_name']); ?></strong>
                                    <small class="text-muted"><?php echo htmlspecialchars($bm['gender'] ?? ''); ?></small>
                                </td>
                                <td class="px-3 text-muted small">
                                    <?php echo htmlspecialchars($bm['father_husband_name'] ?: '—'); ?>
                                </td>
                                <td class="px-3">
                                    <span class="badge bg-light text-dark border small px-2 py-1">
                                        <?php echo htmlspecialchars($bm['reservation_status'] ?: 'सामान्य'); ?>
                                    </span>
                                </td>
                                <td class="px-3 text-center">
                                    <?php if (!empty($bm['mobile'])): ?>
                                        <?php echo renderMaskedPhoneButton($bm['mobile'], $bm['candidate_name'] . ' (पंचायत समिति सदस्य)'); ?>
                                    <?php else: ?>
                                        <span class="text-muted small">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <script>
        function filterBlockPanchayats() {
            const query = document.getElementById('panchayatSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.panchayat-card-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemSearch = item.getAttribute('data-search').toLowerCase();
                if (query === '' || itemSearch.includes(query)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            const badge = document.getElementById('panchayatCountBadge');
            if (badge) {
                badge.innerText = 'Showing ' + visibleCount + ' Panchayats';
            }
        }
        </script>

    <?php elseif (!empty($selectedDistrictSlug) && $districtObj): ?>
        <!-- ============================================================= -->
        <!-- 3. SELECTED DISTRICT: BLOCKS & PANCHAYATI RAJ DIRECTORY       -->
        <!-- ============================================================= -->
        <?php 
        $dStats = $districtCensusStats[$selectedDistrictSlug] ?? [];
        $dTotalBlocks = count($districtBlocks) ?: ($dStats['total_blocks'] ?? 0);
        $dTotalPanchayats = $districtPanchayatCounts[$selectedDistrictSlug] ?? count($allDistrictPanchayats);
        $dPop = intval($dStats['total_pop'] ?? $districtObj['population'] ?? 0);
        $dLit = round(floatval($dStats['avg_literacy'] ?? 0), 1);
        $dHouseholds = intval($dStats['total_households'] ?? 0);
        $cleanDivision = trim(preg_replace('/\s+division$/i', '', $districtObj['division'] ?? 'Bihar'));
        ?>

        <!-- District Header & Summary Statistics Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary fw-bold px-2.5 py-1 rounded-pill small">🏢 <?php echo htmlspecialchars($cleanDivision); ?> Division</span>
                        <?php if (!empty($districtObj['headquarters'])): ?>
                            <span class="badge bg-light text-muted border px-2.5 py-1 rounded-pill small">HQ: <?php echo htmlspecialchars($districtObj['headquarters']); ?></span>
                        <?php endif; ?>
                    </div>
                    <h2 class="fw-bold font-heading text-navy fs-3 mb-1">
                        <?php echo htmlspecialchars($districtObj['name']); ?> District
                        <?php if (!empty($districtObj['name_hi'])): ?>
                            <span class="text-muted fs-4 fw-normal">(<?php echo htmlspecialchars($districtObj['name_hi']); ?>)</span>
                        <?php endif; ?>
                    </h2>
                    <p class="text-muted small mb-0">
                        CD Blocks &amp; Gram Panchayats Administrative Directory of <?php echo htmlspecialchars($districtObj['name']); ?>
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-grid-fill me-1"></i> Browse All 38 Districts
                    </a>
                    <a href="<?php echo getDistrictUrl($selectedDistrictSlug); ?>" class="btn btn-outline-primary rounded-pill px-3 py-1.5 fw-semibold btn-sm">
                        <i class="bi bi-building me-1"></i> Visit District Hub
                    </a>
                </div>
            </div>

            <!-- Key District Demographics & Panchayati Raj Metrics -->
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-geo-alt-fill text-primary fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">CD Blocks</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo number_format($dTotalBlocks); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-building-check text-success fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Gram Panchayats</span>
                        <span class="fs-4 fw-bold text-success"><?php echo $dTotalPanchayats ? number_format($dTotalPanchayats) : 'Mapped'; ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-people-fill text-info fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Population</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo number_format($dPop); ?></span>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="bg-light rounded-3 p-3 border border-light text-center h-100">
                        <i class="bi bi-book-half text-warning fs-4 d-block mb-1"></i>
                        <span class="text-muted small fw-bold text-uppercase d-block">Avg Literacy</span>
                        <span class="fs-4 fw-bold text-dark"><?php echo $dLit ? "{$dLit}%" : "N/A"; ?></span>
                    </div>
                </div>
            </div>

            <?php 
            $distWards = $districtWardCounts[$selectedDistrictSlug] ?? 0;
            $distPanch = $districtPanchCounts[$selectedDistrictSlug] ?? 0;
            ?>
            <div class="row g-2 mt-2 pt-2 border-top">
                <div class="col-6 col-md-4">
                    <span class="text-muted extra-small d-block">निर्वाचित वार्ड सदस्य:</span>
                    <strong class="text-primary small"><?php echo $distWards ? number_format($distWards) : '1,500+'; ?> सदस्य</strong>
                </div>
                <div class="col-6 col-md-4">
                    <span class="text-muted extra-small d-block">ग्राम कचहरी पंच:</span>
                    <strong class="text-success small"><?php echo $distPanch ? number_format($distPanch) : '1,500+'; ?> पंच</strong>
                </div>
                <div class="col-12 col-md-4">
                    <span class="text-muted extra-small d-block">पंचायत समिति सदस्य:</span>
                    <strong class="text-navy small"><?php echo number_format($districtSamitiMemberCounts[$selectedDistrictSlug] ?? 0); ?> सदस्य</strong>
                </div>
            </div>
        </div>

        <!-- Search Bar & Switch District Controls -->
        <div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
            <div class="row g-3 align-items-center justify-content-between">
                <div class="col-12 col-md-6">
                    <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-search text-primary me-1"></i> Search CD Blocks in <?php echo htmlspecialchars($districtObj['name']); ?>:</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="blockSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Type block name (e.g. Chapra, Amnour, Manjhi)..." onkeyup="filterBlocks()">
                    </div>
                </div>
                <div class="col-12 col-md-6 text-md-end">
                    <label class="fw-bold text-navy small mb-1 d-block"><i class="bi bi-arrow-repeat text-primary me-1"></i> Switch District:</label>
                    <select class="form-select form-select-sm rounded-pill d-inline-block w-auto" style="min-width: 240px;" onchange="if(this.value){ location.href = this.value; }">
                        <?php foreach ($districts as $d): ?>
                            <option value="<?php echo getPanchayatUrl($d['slug']); ?>" <?php echo $selectedDistrictSlug === $d['slug'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($d['name']); ?> District
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <!-- Counter Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill small">Sub-District Directory</span>
                <h3 class="fw-bold font-heading text-navy mt-1 fs-4 mb-0">Community Development Blocks in <?php echo htmlspecialchars($districtObj['name']); ?></h3>
            </div>
            <div class="badge bg-primary text-white fw-bold fs-6 px-3 py-2 rounded-pill shadow-sm" id="blockCountBadge">
                Showing <?php echo count($districtBlocks); ?> Blocks
            </div>
        </div>

        <!-- Redesigned Blocks Grid with Direct Panchayat View Options -->
        <div class="row g-3 g-lg-4" id="blocksGrid">
            <?php foreach ($districtBlocks as $blk): 
                $bSlug = slugify($blk['sub_district']);
                $bUrl = getBlockUrl($selectedDistrictSlug, $bSlug);
                $bPop = intval($blk['population'] ?? 0);
                $bMale = intval($blk['male'] ?? 0);
                $bFemale = intval($blk['female'] ?? 0);
                $bLit = floatval($blk['literacy_rate'] ?? 0);
                $bHouseholds = intval($blk['households'] ?? 0);
                $bCode = $blk['sub_dist_code'] ?? 'N/A';

                // Look up count of panchayats in this block
                $panchayatsInThisBlock = $blockPanchayatsMap[$bSlug] ?? [];
                $pCount = count($panchayatsInThisBlock);
                $bBlockPanchayatsUrl = getPanchayatUrl($selectedDistrictSlug, $bSlug);

                $searchStr = strtolower(($blk['sub_district'] ?? '') . ' ' . $bCode . ' ' . $districtObj['name']);
            ?>
                <div class="col-12 col-md-6 col-lg-4 block-stat-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>">
                    <div class="admin-card admin-card-block h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-warning-subtle text-dark fw-bold px-2 py-0.5 rounded small mb-1" style="font-size: 0.72rem;">
                                        📍 CD Block
                                    </span>
                                    <h4 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($bBlockPanchayatsUrl); ?>" class="text-decoration-none text-navy hover-primary" title="View all panchayats in <?php echo htmlspecialchars($blk['sub_district']); ?>">
                                            <?php echo htmlspecialchars($blk['sub_district']); ?>
                                        </a>
                                    </h4>
                                </div>
                                <span class="badge bg-light text-muted border small px-2 py-1">
                                    Code: <?php echo htmlspecialchars($bCode); ?>
                                </span>
                            </div>

                            <!-- Block Statistics Micro Dashboard -->
                            <div class="row g-2 my-3">
                                <div class="col-6">
                                    <a href="<?php echo htmlspecialchars($bBlockPanchayatsUrl); ?>" class="text-decoration-none d-block">
                                        <div class="metric-mini-box bg-success bg-opacity-10 border-success border-opacity-25 hover-shadow">
                                            <span class="text-success fw-bold d-block" style="font-size: 0.72rem;">Gram Panchayats</span>
                                            <strong class="text-success fs-6"><?php echo $pCount ? number_format($pCount) . ' Panchayats' : 'Mapped'; ?></strong>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Population</span>
                                        <strong class="text-dark fs-6"><?php echo number_format($bPop); ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Literacy Rate</span>
                                        <strong class="text-primary fs-6"><?php echo $bLit ? "{$bLit}%" : 'N/A'; ?></strong>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">Households</span>
                                        <strong class="text-dark fs-6"><?php echo number_format($bHouseholds); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Direct Action Links with View Panchayats Option -->
                        <div class="mt-auto pt-3 border-top">
                            <div class="d-flex justify-content-between align-items-center gap-2 flex-wrap mb-2">
                                <a href="<?php echo htmlspecialchars($bBlockPanchayatsUrl); ?>" class="btn btn-sm btn-success rounded-pill fw-bold px-3 py-1.5 small text-white shadow-sm" title="View all <?php echo $pCount; ?> Gram Panchayats">
                                    🌾 View <?php echo $pCount ? $pCount : ''; ?> Panchayats <i class="bi bi-arrow-right ms-1"></i>
                                </a>
                                
                                <div class="d-flex gap-1 flex-wrap">
                                    <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo urlencode($selectedDistrictSlug); ?>&block=<?php echo urlencode($blk['sub_district']); ?>" class="btn btn-sm btn-outline-success rounded-pill fw-semibold px-2 py-1.5 small text-nowrap" title="Panchayat Samiti Members">
                                        <i class="bi bi-people me-1"></i> समिति सदस्य
                                    </a>
                                    <a href="<?php echo getPanchayatSamitiUrl($selectedDistrictSlug, $bSlug); ?>" class="btn btn-sm btn-outline-secondary rounded-pill fw-semibold px-2 py-1.5 small text-nowrap" title="प्रखंड प्रमुख">
                                        प्रमुख
                                    </a>
                                    <a href="<?php echo htmlspecialchars($bUrl); ?>" class="btn btn-sm btn-outline-primary rounded-pill fw-semibold px-2 py-1.5 small text-nowrap" title="प्रखंड हब">
                                        प्रखंड हब
                                    </a>
                                </div>
                            </div>

                            <?php if (!empty($panchayatsInThisBlock)): ?>
                                <!-- Collapsible Quick-View Drawer of Gram Panchayats -->
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small text-muted w-100 text-start mt-1 d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#quickPanchayats_<?php echo $bSlug; ?>" aria-expanded="false">
                                    <span style="font-size: 0.75rem;"><i class="bi bi-grid-3x3-gap-fill text-success me-1"></i> Quick list (<?php echo $pCount; ?> Panchayats)</span>
                                    <i class="bi bi-chevron-down" style="font-size: 0.7rem;"></i>
                                </button>
                                <div class="collapse mt-2 pt-2 border-top" id="quickPanchayats_<?php echo $bSlug; ?>">
                                    <div class="d-flex flex-wrap gap-1" style="max-height: 180px; overflow-y: auto;">
                                        <?php foreach ($panchayatsInThisBlock as $pItem): 
                                            $pItemUrl = getPanchayatUrl($selectedDistrictSlug, $bSlug, slugify($pItem['panchayat_name']));
                                        ?>
                                            <a href="<?php echo htmlspecialchars($pItemUrl); ?>" class="btn btn-sm btn-light border rounded-pill px-2 py-0.5 text-truncate" style="font-size: 0.75rem;" title="<?php echo htmlspecialchars($pItem['panchayat_name']); ?> (Mukhiya: <?php echo htmlspecialchars($pItem['current_mukhiya'] ?: 'N/A'); ?>)">
                                                🌾 <?php echo htmlspecialchars($pItem['panchayat_name']); ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noBlocksAlert" class="alert alert-info rounded-4 text-center py-5 d-none mt-4 shadow-sm">
            <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">No Block found in <?php echo htmlspecialchars($districtObj['name']); ?></h5>
            <p class="text-muted mb-3">Try searching for another block name or sub-district code.</p>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="clearBlockSearch()">Clear Search</button>
        </div>

        <script>
        function filterBlocks() {
            const query = document.getElementById('blockSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.block-stat-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemSearch = item.getAttribute('data-search').toLowerCase();
                if (query === '' || itemSearch.includes(query)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            const badge = document.getElementById('blockCountBadge');
            if (badge) {
                badge.innerText = 'Showing ' + visibleCount + ' Blocks';
            }
            
            const noResults = document.getElementById('noBlocksAlert');
            if (noResults) {
                if (visibleCount === 0) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            }
        }

        function clearBlockSearch() {
            document.getElementById('blockSearchInput').value = '';
            filterBlocks();
        }
        </script>

    <?php else: ?>
        <!-- ============================================================= -->
        <!-- 4. ALL 38 DISTRICTS OVERVIEW & DIRECTORY                      -->
        <!-- ============================================================= -->
        
        <!-- State Overview Highlights -->
        <div class="row g-3 g-lg-4 mb-4">
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">जिले</small>
                        <i class="bi bi-building text-primary fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-navy">38</div>
                    <small class="text-muted extra-small">प्रशासनिक जिले</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-warning">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">प्रखंड</small>
                        <i class="bi bi-geo-alt-fill text-warning fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark"><?php echo number_format($totalStateBlocks); ?></div>
                    <small class="text-muted extra-small">अंचल / ब्लॉक</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">ग्राम पंचायतें</small>
                        <i class="bi bi-building-check text-success fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-success"><?php echo number_format($totalStatePanchayats); ?></div>
                    <small class="text-muted extra-small">प्रथम स्तर निकाय</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-primary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">वार्ड सदस्य</small>
                        <i class="bi bi-grid-3x3-gap-fill text-primary fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-primary"><?php echo number_format($totalStateWards); ?></div>
                    <small class="text-muted extra-small">WIMC प्रमुख</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-info">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">कचहरी पंच</small>
                        <i class="bi bi-award-fill text-info fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark"><?php echo number_format($totalStatePanch); ?></div>
                    <small class="text-muted extra-small">न्यायिक प्रतिनिधि</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-lg-2">
                <div class="card border-0 shadow-sm rounded-4 p-3 h-100 bg-white border-start border-4 border-secondary">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 0.72rem;">कुल आबादी</small>
                        <i class="bi bi-people-fill text-secondary fs-5"></i>
                    </div>
                    <div class="fs-4 fw-bold text-dark">10.41 Cr</div>
                    <small class="text-muted extra-small">जनगणना डेटा</small>
                </div>
            </div>
        </div>

        <!-- Interactive Search & Division Filter Hub -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-primary">
            <div class="row g-3 align-items-center justify-content-between mb-3">
                <div class="col-12 col-md-7">
                    <h4 class="fw-bold text-navy font-heading mb-1 fs-5">
                        <i class="bi bi-funnel-fill text-primary me-1"></i> Browse Districts by Division or Search
                    </h4>
                    <p class="text-muted small mb-0">Select any division below or search by district name, headquarters, or Hindi name.</p>
                </div>
                <div class="col-12 col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" id="districtSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Type district name (e.g. Patna, Saran, Gaya)..." autocomplete="off" onkeyup="filterDistricts()">
                    </div>
                </div>
            </div>

            <!-- Division Quick Filter Pills -->
            <div class="d-flex flex-wrap gap-2 pt-3 border-top align-items-center" id="divisionPillsContainer">
                <span class="fw-bold text-muted small me-1"><i class="bi bi-geo-fill text-primary"></i> Division:</span>
                
                <button type="button" class="division-pill-btn active" data-division="all" onclick="filterDivision('all', this)">
                    All Bihar <span class="division-badge-count">38</span>
                </button>

                <?php foreach ($divisionsList as $div): 
                    $count = $divisionCounts[$div] ?? 0;
                ?>
                    <button type="button" class="division-pill-btn" data-division="<?php echo htmlspecialchars(strtolower($div)); ?>" onclick="filterDivision('<?php echo htmlspecialchars(strtolower($div)); ?>', this)">
                        <?php echo htmlspecialchars($div); ?> <span class="division-badge-count"><?php echo $count; ?></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filter Counter Ribbon -->
        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <span class="text-muted small fw-semibold" id="filterStatusText">Showing all 38 districts across Bihar</span>
            <span class="badge bg-primary-subtle text-primary fw-bold px-3 py-1.5 rounded-pill small" id="districtCountBadge">38 Districts</span>
        </div>

        <!-- Redesigned 38 Districts Grid -->
        <div class="row g-3 g-lg-4" id="districtsGrid">
            <?php foreach ($districts as $d): 
                $dSlug = strtolower($d['slug']);
                $census = $districtCensusStats[$dSlug] ?? [];
                $bCount = intval($census['total_blocks'] ?? 0);
                $pCount = intval($districtPanchayatCounts[$dSlug] ?? 0);
                
                $rawDivision = $d['division'] ?? 'Bihar';
                $cleanDivision = trim(preg_replace('/\s+division$/i', '', $rawDivision));
                $divisionKey = strtolower($cleanDivision);
                
                $searchStr = strtolower($d['name'] . ' ' . ($d['name_hi'] ?? '') . ' ' . ($d['headquarters'] ?? '') . ' ' . $cleanDivision . ' ' . $rawDivision);
                $districtPanchayatUrl = getPanchayatUrl($dSlug);
                $districtHubUrl = getDistrictUrl($dSlug);
                $dPop = intval($d['population'] ?? $census['total_pop'] ?? 0);
            ?>
                <div class="col-12 col-md-6 col-lg-4 district-stat-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>" data-division="<?php echo htmlspecialchars($divisionKey, ENT_QUOTES); ?>">
                    <div class="admin-card h-100 p-4 d-flex flex-column justify-content-between">
                        <div>
                            <!-- Header Info -->
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-0.5 rounded small mb-1" style="font-size: 0.72rem;">
                                        🏛️ <?php echo htmlspecialchars($cleanDivision); ?> Division
                                    </span>
                                    <h5 class="fw-bold mb-0 text-navy font-heading fs-5">
                                        <a href="<?php echo htmlspecialchars($districtPanchayatUrl); ?>" class="text-decoration-none text-navy hover-primary">
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </a>
                                        <?php if (!empty($d['name_hi'])): ?>
                                            <span class="text-muted fs-6 fw-normal ms-1">(<?php echo htmlspecialchars($d['name_hi']); ?>)</span>
                                        <?php endif; ?>
                                    </h5>
                                </div>
                                <span class="badge bg-light text-muted border small px-2 py-1">
                                    HQ: <?php echo htmlspecialchars($d['headquarters'] ?? $d['name']); ?>
                                </span>
                            </div>

                            <!-- Micro-Metrics Dashboard -->
                            <div class="row g-2 my-2">
                                <div class="col-4">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">प्रखंड</span>
                                        <strong class="text-dark fs-6"><?php echo $bCount ? number_format($bCount) : 'मैप'; ?></strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">पंचायतें</span>
                                        <strong class="text-success fs-6"><?php echo $pCount ? number_format($pCount) : 'मैप'; ?></strong>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="metric-mini-box">
                                        <span class="text-muted d-block" style="font-size: 0.72rem;">आबादी</span>
                                        <strong class="text-primary fs-6"><?php echo $dPop ? round($dPop / 100000, 1) . 'L' : 'N/A'; ?></strong>
                                    </div>
                                </div>
                            </div>
                            <?php 
                            $dWards = $districtWardCounts[$dSlug] ?? 0;
                            $dPanch = $districtPanchCounts[$dSlug] ?? 0;
                            if ($dWards > 0 || $dPanch > 0): ?>
                                <div class="d-flex align-items-center justify-content-between extra-small bg-light rounded-3 px-2.5 py-1.5 mb-2 border">
                                    <span class="text-muted"><i class="bi bi-grid-3x3-gap-fill text-primary me-1"></i><strong><?php echo number_format($dWards); ?></strong> वार्ड</span>
                                    <span class="text-muted"><i class="bi bi-award-fill text-success me-1"></i><strong><?php echo number_format($dPanch); ?></strong> पंच</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Action Links -->
                        <div class="mt-auto pt-3 border-top d-flex justify-content-between align-items-center gap-2 flex-wrap">
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="<?php echo SITE_URL; ?>/panchayat-samiti-members?district=<?php echo htmlspecialchars($dSlug); ?>" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 small" title="Panchayat Samiti Members">
                                    <i class="bi bi-people"></i> समिति
                                </a>
                                <a href="<?php echo getZilaParishadUrl($dSlug); ?>" class="btn btn-sm btn-outline-info text-dark rounded-pill px-2.5 py-1 small" title="जिला परिषद">
                                    <i class="bi bi-bank"></i> जिला परिषद
                                </a>
                            </div>
                            <a href="<?php echo htmlspecialchars($districtPanchayatUrl); ?>" class="btn btn-sm btn-primary fw-bold text-white rounded-pill px-3 py-1.5 shadow-sm text-nowrap">
                                प्रखंड देखें <i class="bi bi-arrow-right ms-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div id="noDistrictsAlert" class="alert alert-info rounded-4 text-center py-5 d-none mt-4 shadow-sm">
            <i class="bi bi-search fs-1 text-primary mb-2 d-block"></i>
            <h5 class="fw-bold text-dark mb-1">No District found</h5>
            <p class="text-muted mb-3">Try searching for another district name, headquarters, or reset the division filter.</p>
            <button class="btn btn-primary rounded-pill px-4 py-2 fw-semibold" onclick="clearDistrictSearch()">Reset All Filters</button>
        </div>

        <script>
        let currentDivision = 'all';

        function filterDivision(div, btn) {
            currentDivision = div.toLowerCase().trim();
            document.querySelectorAll('.division-pill-btn').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');
            filterDistricts();
        }

        function filterDistricts() {
            const query = document.getElementById('districtSearchInput').value.toLowerCase().trim();
            const items = document.querySelectorAll('.district-stat-item');
            let visibleCount = 0;

            items.forEach(item => {
                const itemSearch = item.getAttribute('data-search').toLowerCase();
                const itemDiv = (item.getAttribute('data-division') || '').toLowerCase();
                
                const matchQuery = query === '' || itemSearch.includes(query);
                const matchDiv = currentDivision === 'all' || itemDiv === currentDivision || itemDiv.includes(currentDivision);

                if (matchQuery && matchDiv) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });
            
            const badge = document.getElementById('districtCountBadge');
            if (badge) {
                badge.innerText = visibleCount + ' Districts';
            }

            const statusText = document.getElementById('filterStatusText');
            if (statusText) {
                if (currentDivision !== 'all') {
                    const divTitle = currentDivision.charAt(0).toUpperCase() + currentDivision.slice(1);
                    statusText.innerText = 'Showing ' + visibleCount + ' districts in ' + divTitle + ' Division';
                } else if (query !== '') {
                    statusText.innerText = 'Showing ' + visibleCount + ' matching districts';
                } else {
                    statusText.innerText = 'Showing all 38 districts across Bihar';
                }
            }

            const noResults = document.getElementById('noDistrictsAlert');
            if (noResults) {
                if (visibleCount === 0) {
                    noResults.classList.remove('d-none');
                } else {
                    noResults.classList.add('d-none');
                }
            }
        }

        function clearDistrictSearch() {
            document.getElementById('districtSearchInput').value = '';
            currentDivision = 'all';
            document.querySelectorAll('.division-pill-btn').forEach(b => {
                if (b.getAttribute('data-division') === 'all') b.classList.add('active');
                else b.classList.remove('active');
            });
            filterDistricts();
        }
        </script>
    <?php endif; ?>

    <!-- Official Data Sources Attribution Banner -->
    <section class="mt-5 pt-4 border-top">
        <div class="p-4 rounded-4 bg-light border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark font-heading mb-1">Standardized Public &amp; Government Data Sources</h6>
                    <p class="text-muted small mb-0">Local Block boundaries, Gram Panchayat distributions, Census 2011 stats, and administrative rosters reference Census of India, LGD Portal, and SEC Bihar.</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/census" class="btn btn-outline-primary rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-bar-chart-line me-1"></i>View Census Directory
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
