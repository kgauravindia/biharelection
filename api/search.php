<?php
/**
 * BiharElection.com - Comprehensive Unified Real-Time Search API
 * Supports: Villages to Politicians (Mukhiya, Sarpanch, MLA, MP), Vidhan Parishad MLCs,
 *           Lok Sabha & Rajya Sabha MPs, MLAs, Gram Panchayats, Blocks & Districts.
 */
if (!headers_sent()) {
    header('Content-Type: application/json; charset=utf-8');
    header('Access-Control-Allow-Origin: *');
}

require_once __DIR__ . '/../config.php';

$rawQuery = trim($_GET['q'] ?? '');
if (strlen($rawQuery) < 2) {
    echo json_encode([]);
    exit;
}

$pdo = Database::getConnection();

// --- 1. INTENT DETECTION & QUERY NORMALIZATION ---
$queryLower = strtolower($rawQuery);

$intent = 'general';
if (preg_match('/\b(mukhiya|pradhan|मुखिया|प्रधान)\b/iu', $queryLower)) {
    $intent = 'mukhiya';
} elseif (preg_match('/\b(sarpanch|सरपंच)\b/iu', $queryLower)) {
    $intent = 'sarpanch';
} elseif (preg_match('/\b(mla|vidhayak|विधायक)\b/iu', $queryLower)) {
    $intent = 'mla';
} elseif (preg_match('/\b(mp|sansad|सांसद)\b/iu', $queryLower)) {
    $intent = 'mp';
} elseif (preg_match('/\b(mlc|vidhan\s*parishad|parishad|परिषद|एमएलसी|विधान\s*पार्षद)\b/iu', $queryLower)) {
    $intent = 'mlc';
} elseif (preg_match('/\b(village|gaon|gao|gram|गाँव|गांव|ग्राम)\b/iu', $queryLower)) {
    $intent = 'village';
} elseif (preg_match('/\b(panchayat|pnachayat|पंचायत)\b/iu', $queryLower)) {
    $intent = 'panchayat';
}

// Clean query: strip out filler/intent keywords, punctuation, stop words
$cleanQuery = preg_replace('/(?:\b(mukhiya|sarpanch|pradhan|mla|mp|mlc|vidhayak|sansad|parishad|vidhan\s*parishad|village|gaon|gao|gram|panchayat|pnachayat|block|prakhand|district|zila|of|in|the|at|ka|ki|ke|me|mein|to|and|मुखिया|सरपंच|विधायक|सांसद|विधान\s*पार्षद|गांव|गाँव|ग्राम|पंचायत|प्रखंड|जिला|का|की|के|में)\b|[\/,.\-\(\)]+)/iu', ' ', $rawQuery);
$cleanQuery = trim(preg_replace('/\s+/', ' ', $cleanQuery));

if (strlen($cleanQuery) < 2) {
    $cleanQuery = $rawQuery;
}

// Generate phonetic and common Bihar place spelling variants (e.g. mukharera <-> mukrera, mukhrera, etc.)
if (!function_exists('getPhoneticPlaceVariants')) {
    function getPhoneticPlaceVariants($word) {
        $variants = [];
        $w = strtolower(trim($word));
        $variants[] = $w;
        
        // mukharera -> mukrera, mukhrera, mukhra
        if (strpos($w, 'mukhar') !== false) {
            $variants[] = str_replace('mukhar', 'mukr', $w);
            $variants[] = str_replace('mukhar', 'mukhr', $w);
            $variants[] = str_replace('mukhar', 'mukh', $w);
        }
        if (strpos($w, 'mukr') !== false) {
            $variants[] = str_replace('mukr', 'mukhar', $w);
            $variants[] = str_replace('mukr', 'mukhr', $w);
        }
        if (strpos($w, 'mukhr') !== false) {
            $variants[] = str_replace('mukhr', 'mukhar', $w);
            $variants[] = str_replace('mukhr', 'mukr', $w);
        }
        // Collapsing 'har' / 'ar'
        if (strpos($w, 'har') !== false) {
            $variants[] = str_replace('har', '', $w);
            $variants[] = str_replace('har', 'hr', $w);
        }
        // Collapsing 'era' / 'ra'
        if (substr($w, -3) === 'era') {
            $variants[] = substr($w, 0, -3) . 'ra';
        }
        // Collapsing double vowels
        $variants[] = preg_replace('/([aeiou])\1+/', '$1', $w);
        
        return array_values(array_unique(array_filter($variants)));
    }
}

// Location-specific search terms generated from cleaned core query
$locationTerms = getPhoneticPlaceVariants($cleanQuery);

// General terms for text matching
$allTerms = array_values(array_unique(array_filter(array_merge([$cleanQuery, $rawQuery], $locationTerms))));

$results = [
    'district' => [],
    'mla' => [],
    'mp' => [],
    'mlc' => [],
    'mukhiya' => [],
    'sarpanch' => [],
    'village' => [],
    'block' => [],
    'town' => []
];

// Helper: check if term matches
if (!function_exists('termMatches')) {
    function termMatches($haystack, $needles) {
        if (empty($haystack)) return false;
        foreach ($needles as $n) {
            if (stripos($haystack, $n) !== false) return true;
        }
        return false;
    }
}

// Helper: cached district panchayats fetcher
if (!function_exists('getDistrictPanchayatsCached')) {
    function getDistrictPanchayatsCached($pdo, $dSlug) {
        static $districtPanchayatCache = [];
        if (isset($districtPanchayatCache[$dSlug])) {
            return $districtPanchayatCache[$dSlug];
        }
        $districtPanchayatCache[$dSlug] = DataCache::remember('district_panchayats_' . $dSlug, 86400, function() use ($pdo, $dSlug) {
            if (!$pdo) return [];
            try {
                $stmtP = $pdo->prepare("SELECT * FROM panchayats WHERE district_slug = :dslug ORDER BY id ASC");
                $stmtP->execute([':dslug' => $dSlug]);
                return $stmtP->fetchAll(PDO::FETCH_ASSOC) ?: [];
            } catch (Throwable $e) {
                return [];
            }
        });
        return $districtPanchayatCache[$dSlug] ?: [];
    }
}

// -------------------------------------------------------------
// 1. Search Districts
// -------------------------------------------------------------
$districts = DataProvider::getDistricts();
foreach ($districts as $d) {
    if (termMatches($d['name'], $allTerms) || 
        termMatches($d['name_hi'] ?? '', $allTerms) || 
        termMatches($d['headquarters'] ?? '', $allTerms) ||
        termMatches($d['division'] ?? '', $allTerms)) {
        
        $results['district'][] = [
            'type' => 'district',
            'title' => $d['name'] . ' District' . (!empty($d['name_hi']) ? ' (' . $d['name_hi'] . ')' : ''),
            'subtitle' => ($d['division'] ?? 'Bihar') . ' Division | HQ: ' . ($d['headquarters'] ?? $d['name']),
            'extra' => 'Explore District Profile, MLAs, CD Blocks & Panchayats',
            'slug' => $d['slug'],
            'url' => getDistrictUrl($d['slug'])
        ];
    }
}

// -------------------------------------------------------------
// 2. Search MPs (Lok Sabha & Rajya Sabha)
// -------------------------------------------------------------
if ($pdo) {
    try {
        $mpSql = "SELECT pc_no, pc_name, mp_name, party FROM mps_loksabha WHERE ";
        $mpClauses = [];
        $mpParams = [];
        foreach (array_slice($allTerms, 0, 3) as $t) {
            $mpClauses[] = "(pc_name LIKE ? OR mp_name LIKE ? OR party LIKE ?)";
            $tLike = "%$t%";
            $mpParams[] = $tLike;
            $mpParams[] = $tLike;
            $mpParams[] = $tLike;
        }
        $mpSql .= implode(' OR ', $mpClauses) . " LIMIT 6";
        $stmtLS = $pdo->prepare($mpSql);
        $stmtLS->execute($mpParams);
        while ($mp = $stmtLS->fetch(PDO::FETCH_ASSOC)) {
            $results['mp'][] = [
                'type' => 'mp',
                'title' => 'PC ' . $mp['pc_no'] . ' — ' . $mp['pc_name'] . ' (Lok Sabha)',
                'subtitle' => 'MP: ' . $mp['mp_name'] . ' (' . $mp['party'] . ')',
                'extra' => 'Lok Sabha Parliamentarian from Bihar',
                'slug' => slugify($mp['pc_name']),
                'url' => SITE_URL . '/mp'
            ];
        }

        // Rajya Sabha
        $rsSql = "SELECT mp_name, party, tenure FROM mps_rajyasabha WHERE ";
        $rsClauses = [];
        $rsParams = [];
        foreach (array_slice($allTerms, 0, 3) as $t) {
            $rsClauses[] = "(mp_name LIKE ? OR party LIKE ?)";
            $tLike = "%$t%";
            $rsParams[] = $tLike;
            $rsParams[] = $tLike;
        }
        $rsSql .= implode(' OR ', $rsClauses) . " LIMIT 4";
        $stmtRS = $pdo->prepare($rsSql);
        $stmtRS->execute($rsParams);
        while ($rs = $stmtRS->fetch(PDO::FETCH_ASSOC)) {
            $results['mp'][] = [
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

// -------------------------------------------------------------
// 3. Search MLAs (Vidhan Sabha Constituencies)
// -------------------------------------------------------------
$acs = DataProvider::getConstituencies();
foreach ($acs as $ac) {
    if (termMatches($ac['name'], $allTerms) || 
        termMatches($ac['name_hi'] ?? '', $allTerms) || 
        termMatches((string)$ac['ac_no'], $allTerms) || 
        termMatches($ac['district'], $allTerms) ||
        termMatches($ac['current_mla'], $allTerms) ||
        termMatches($ac['current_party'] ?? '', $allTerms)) {
        
        $results['mla'][] = [
            'type' => 'mla',
            'title' => 'AC ' . $ac['ac_no'] . ' — ' . $ac['name'] . (!empty($ac['name_hi']) ? ' (' . $ac['name_hi'] . ')' : ''),
            'subtitle' => 'MLA: ' . ($ac['current_mla'] ?: 'Vacant') . ' (' . ($ac['current_party'] ?: 'IND') . ')',
            'extra' => 'District: ' . $ac['district'] . ' | Total Electors: ' . number_format($ac['total_electors']),
            'slug' => $ac['slug'],
            'url' => getMlaUrl($ac)
        ];
    }
}

// -------------------------------------------------------------
// 4. Search MLCs (Bihar Vidhan Parishad Elected Members)
// -------------------------------------------------------------
$mlcs = DataProvider::getMlcs();
$isMlcQuery = preg_match('/\b(mlc|vidhan\s*parishad|parishad|परिषद|एमएलसी|विधान\s*पार्षद)\b/iu', $rawQuery);
foreach ($mlcs as $m) {
    $mName = $m['name'] ?? '';
    if (strtolower(trim($mName)) === 'vacant') continue;

    $const = $m['constituency'] ?? '';
    $party = $m['party'] ?? '';
    $mTenure = $m['tenure'] ?? '';

    $matchesMlc = false;
    if ($isMlcQuery) {
        $matchesMlc = true;
    } else {
        // Direct term match
        if (termMatches($mName, $allTerms) || termMatches($const, $allTerms) || (!empty($party) && termMatches($party, $allTerms))) {
            $matchesMlc = true;
        } else {
            // Multi-word token match (e.g. "Patna Graduates" vs "Graduate, Patna")
            $words = preg_split('/\s+/', strtolower($cleanQuery));
            if (count($words) >= 2) {
                // Normalize Graduate/Graduates and Teacher/Teachers
                $targetStr = strtolower($mName . ' ' . $const . ' ' . $party);
                if (stripos($const, 'graduate') !== false || stripos($const, 'स्नातक') !== false) {
                    $targetStr .= ' graduate graduates स्नातक';
                }
                if (stripos($const, 'teacher') !== false || stripos($const, 'शिक्षक') !== false) {
                    $targetStr .= ' teacher teachers शिक्षक';
                }
                $tokenOk = true;
                foreach ($words as $w) {
                    $wNorm = rtrim($w, 's');
                    if (strlen($wNorm) >= 3 && strpos($targetStr, $wNorm) === false) {
                        $tokenOk = false;
                        break;
                    }
                }
                if ($tokenOk) {
                    $matchesMlc = true;
                }
            }
        }
    }

    if ($matchesMlc) {
        $mlcSlug = !empty($m['slug']) ? $m['slug'] : slugify($mName);
        $results['mlc'][] = [
            'type' => 'mlc',
            'title' => '📜 ' . $mName . ' (Vidhan Parishad MLC)',
            'subtitle' => 'Constituency: ' . $const . (!empty($party) ? ' | Party: ' . $party : ''),
            'extra' => 'Bihar Legislative Council' . (!empty($mTenure) ? ' | Tenure: ' . $mTenure : '') . ' | Mobile: ' . ($m['contact'] ?: 'N/A'),
            'slug' => $mlcSlug,
            'url' => SITE_URL . '/mlc/' . $mlcSlug
        ];
        if (count($results['mlc']) >= 8) break;
    }
}

// -------------------------------------------------------------
// 5. Search Census Villages & Connect to Politicians (Village to Mukhiya, Sarpanch, MLA, MP)
// -------------------------------------------------------------
$matchedVillages = [];
$matchedPanchayatNames = [];

if ($pdo) {
    try {
        $vClauses = [];
        $vParams = [];
        // Use clean location terms and phonetic variations for precise village matching
        foreach (array_slice($locationTerms, 0, 5) as $t) {
            $vClauses[] = "(village_name LIKE ? OR gram_panchayat_name LIKE ? OR village_code = ?)";
            $tLike = "%$t%";
            $vParams[] = $tLike;
            $vParams[] = $tLike;
            $vParams[] = $t;
        }
        $vSql = "SELECT village_name, village_code, village_slug, district_name, district_slug, sub_district_name, cd_block_name, gram_panchayat_name, population 
                 FROM census_villages_2011 
                 WHERE " . implode(' OR ', $vClauses) . " LIMIT 15";
        $stmtV = $pdo->prepare($vSql);
        $stmtV->execute($vParams);
        $matchedVillages = $stmtV->fetchAll(PDO::FETCH_ASSOC);

        // Pre-fetch panchayat politician leadership for matched villages
        $villagePoliticians = [];
        foreach ($matchedVillages as $v) {
            $dSlug = $v['district_slug'] ?: slugify($v['district_name']);
            $bName = $v['sub_district_name'] ?: $v['cd_block_name'];
            $gpName = $v['gram_panchayat_name'];

            if (!empty($gpName)) {
                $matchedPanchayatNames[] = $gpName;
            }

            $cacheKey = $dSlug . '|' . slugify($bName) . '|' . slugify($gpName ?: $v['village_name']);
            if (!isset($villagePoliticians[$cacheKey])) {
                // Find matched panchayat in district using cached block and panchayat matching
                $districtPanchayats = getDistrictPanchayatsCached($pdo, $dSlug);
                $foundP = null;
                foreach ($districtPanchayats as $pr) {
                    if (isBlockMatch($pr['block'], $bName) && 
                        (isPanchayatMatch($pr['panchayat_name'], $gpName) || isPanchayatMatch($pr['panchayat_name'], $v['village_name']))) {
                        $foundP = $pr;
                        break;
                    }
                }
                // Fallback: match by panchayat name anywhere in the district
                if (!$foundP && !empty($gpName)) {
                    foreach ($districtPanchayats as $pr) {
                        if (isPanchayatMatch($pr['panchayat_name'], $gpName)) {
                            $foundP = $pr;
                            break;
                        }
                    }
                }
                $villagePoliticians[$cacheKey] = $foundP;
            }

            $pInfo = $villagePoliticians[$cacheKey];
            $bSlug = slugify($bName);
            $vSlug = $v['village_slug'] ?: ($v['village_code'] ?: slugify($v['village_name']));
            $pSlug = slugify($gpName ?: ($pInfo['panchayat_name'] ?? $v['village_name']));

            $mukhiyaName = $pInfo['current_mukhiya'] ?? '';
            $sarpanchName = $pInfo['current_sarpanch'] ?? '';
            $panchayatDisplay = !empty($pInfo['panchayat_name']) ? $pInfo['panchayat_name'] : ($gpName ?: 'Gram Panchayat');

            // 5a. If user is searching Mukhiya / Leader or general search, add MUKHIYA representation card
            if (!empty($mukhiyaName) && ($intent === 'mukhiya' || $intent === 'general' || $intent === 'panchayat')) {
                // Check if already in results
                $alreadyAdded = false;
                foreach ($results['mukhiya'] as $existing) {
                    if (stripos($existing['title'], $mukhiyaName) !== false && stripos($existing['title'], $panchayatDisplay) !== false) {
                        $alreadyAdded = true;
                        break;
                    }
                }
                if (!$alreadyAdded) {
                    $results['mukhiya'][] = [
                        'type' => 'mukhiya',
                        'title' => '👑 Mukhiya: ' . $mukhiyaName . ' — ' . $panchayatDisplay . ' Gram Panchayat',
                        'subtitle' => 'Village: ' . $v['village_name'] . ' | Block: ' . $bName,
                        'extra' => 'District: ' . $v['district_name'] . ' | Elected Mukhiya representing ' . $v['village_name'] . ' Village' . (!empty($sarpanchName) ? ' | Sarpanch: ' . $sarpanchName : ''),
                        'slug' => $pSlug,
                        'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                    ];
                }
            }

            // 5b. If user is searching Sarpanch or general search, add SARPANCH card
            if (!empty($sarpanchName) && ($intent === 'sarpanch' || $intent === 'general')) {
                $alreadyAddedS = false;
                foreach ($results['sarpanch'] as $existing) {
                    if (stripos($existing['title'], $sarpanchName) !== false && stripos($existing['title'], $panchayatDisplay) !== false) {
                        $alreadyAddedS = true;
                        break;
                    }
                }
                if (!$alreadyAddedS) {
                    $results['sarpanch'][] = [
                        'type' => 'sarpanch',
                        'title' => '⚖️ Sarpanch: ' . $sarpanchName . ' — ' . $panchayatDisplay . ' Gram Kutchery',
                        'subtitle' => 'Village: ' . $v['village_name'] . ' | Block: ' . $bName,
                        'extra' => 'District: ' . $v['district_name'] . ' | Judicial Head representing ' . $v['village_name'] . (!empty($mukhiyaName) ? ' | Mukhiya: ' . $mukhiyaName : ''),
                        'slug' => $pSlug,
                        'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                    ];
                }
            }

            // 5c. Village card enriched with elected leadership
            $gpSubtitle = 'GP: ' . $panchayatDisplay;
            if (!empty($mukhiyaName)) {
                $gpSubtitle .= ' | Mukhiya: ' . $mukhiyaName;
            }
            if (!empty($sarpanchName)) {
                $gpSubtitle .= ' | Sarpanch: ' . $sarpanchName;
            }

            $results['village'][] = [
                'type' => 'village',
                'title' => '🏘️ ' . $v['village_name'] . ' (Village)',
                'subtitle' => $gpSubtitle,
                'extra' => 'Block: ' . $bName . ' | District: ' . $v['district_name'] . ' | Pop: ' . number_format((int)$v['population']) . ' | Census Code: ' . $v['village_code'],
                'slug' => $vSlug,
                'url' => getVillageUrl($dSlug, $bSlug, $vSlug)
            ];
        }
    } catch (Throwable $e) {
        error_log("Village search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 6. Direct Search in Mukhiyas Directory & Panchayats (Mukhiyas & Sarpanchs)
// -------------------------------------------------------------
if ($pdo) {
    try {
        $mukhiyaWhereClauses = [];
        $mukhiyaParams = [];
        
        $isMobileQuery = (bool)preg_match('/^[0-9]{4,10}$/', trim($cleanQuery)) || (bool)preg_match('/^[0-9]{4,10}$/', trim($rawQuery));
        $isJustMukhiya = ($intent === 'mukhiya' && (empty($cleanQuery) || strlen($cleanQuery) < 2));

        if ($isJustMukhiya) {
            // Return top Mukhiyas across Bihar
            $stmtJustM = $pdo->query("SELECT candidate_name, candidate_name_en, post, district, district_slug, block, block_en, panchayat, panchayat_en, category, mobile, tenure 
                                      FROM mukhiyas 
                                      WHERE candidate_name IS NOT NULL AND candidate_name != '' 
                                      ORDER BY id ASC LIMIT 8");
            while ($m = $stmtJustM->fetch(PDO::FETCH_ASSOC)) {
                $pName = $m['panchayat'] . (!empty($m['panchayat_en']) ? ' (' . $m['panchayat_en'] . ')' : '');
                $dSlug = $m['district_slug'] ?: slugify($m['district']);
                $bSlug = slugify($m['block_en'] ?: $m['block']);
                $pSlug = slugify($m['panchayat_en'] ?: $m['panchayat']);
                $nameDisplay = $m['candidate_name'] . (!empty($m['candidate_name_en']) ? ' (' . $m['candidate_name_en'] . ')' : '');

                $results['mukhiya'][] = [
                    'type' => 'mukhiya',
                    'title' => '👑 Mukhiya: ' . $nameDisplay,
                    'subtitle' => '🌾 ' . $pName . ' Gram Panchayat',
                    'extra' => 'Block: ' . ($m['block_en'] ?: $m['block']) . ' | District: ' . $m['district'] . (!empty($m['mobile']) ? ' | 📞 ' . $m['mobile'] : '') . (!empty($m['category']) ? ' | ' . $m['category'] : ''),
                    'slug' => $pSlug,
                    'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                ];
            }
        } elseif ($isMobileQuery) {
            $mNum = preg_replace('/[^0-9]/', '', $cleanQuery ?: $rawQuery);
            $stmtMob = $pdo->prepare("SELECT candidate_name, candidate_name_en, post, district, district_slug, block, block_en, panchayat, panchayat_en, category, mobile, tenure 
                                      FROM mukhiyas 
                                      WHERE mobile LIKE ? LIMIT 8");
            $stmtMob->execute(["%$mNum%"]);
            while ($m = $stmtMob->fetch(PDO::FETCH_ASSOC)) {
                $pName = $m['panchayat'] . (!empty($m['panchayat_en']) ? ' (' . $m['panchayat_en'] . ')' : '');
                $dSlug = $m['district_slug'] ?: slugify($m['district']);
                $bSlug = slugify($m['block_en'] ?: $m['block']);
                $pSlug = slugify($m['panchayat_en'] ?: $m['panchayat']);
                $nameDisplay = $m['candidate_name'] . (!empty($m['candidate_name_en']) ? ' (' . $m['candidate_name_en'] . ')' : '');

                $results['mukhiya'][] = [
                    'type' => 'mukhiya',
                    'title' => '👑 Mukhiya: ' . $nameDisplay,
                    'subtitle' => '🌾 ' . $pName . ' Gram Panchayat',
                    'extra' => '📞 Mobile: ' . $m['mobile'] . ' | Block: ' . ($m['block_en'] ?: $m['block']) . ' | District: ' . $m['district'],
                    'slug' => $pSlug,
                    'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                ];
            }
        } else {
            // General & Bilingual Name/Place search in mukhiyas table
            $combinedTerms = array_values(array_unique(array_filter(array_merge([$cleanQuery, $rawQuery], $locationTerms, $allTerms, $matchedPanchayatNames))));
            $mClauses = [];
            $mParams = [];
            foreach (array_slice($combinedTerms, 0, 5) as $t) {
                if (strlen($t) < 2) continue;
                $tLike = "%$t%";
                $mClauses[] = "(candidate_name LIKE ? OR candidate_name_en LIKE ? OR panchayat LIKE ? OR panchayat_en LIKE ? OR block LIKE ? OR block_en LIKE ? OR district LIKE ? OR mobile LIKE ?)";
                for ($i = 0; $i < 8; $i++) {
                    $mParams[] = $tLike;
                }
            }

            if (!empty($mClauses)) {
                $mSql = "SELECT candidate_name, candidate_name_en, post, district, district_slug, block, block_en, panchayat, panchayat_en, category, mobile, tenure 
                         FROM mukhiyas 
                         WHERE " . implode(' OR ', $mClauses) . " 
                         ORDER BY (candidate_name_en LIKE ? OR candidate_name LIKE ?) DESC, 
                                  (panchayat_en LIKE ? OR panchayat LIKE ?) DESC 
                         LIMIT 10";
                $leadTerm = "%" . ($cleanQuery ?: $rawQuery) . "%";
                $mParamsOrder = array_merge($mParams, [$leadTerm, $leadTerm, $leadTerm, $leadTerm]);
                $stmtMukh = $pdo->prepare($mSql);
                $stmtMukh->execute($mParamsOrder);
                while ($m = $stmtMukh->fetch(PDO::FETCH_ASSOC)) {
                    $pName = $m['panchayat'] . (!empty($m['panchayat_en']) && $m['panchayat_en'] !== $m['panchayat'] ? ' (' . $m['panchayat_en'] . ')' : '');
                    $dSlug = $m['district_slug'] ?: slugify($m['district']);
                    $bSlug = slugify($m['block_en'] ?: $m['block']);
                    $pSlug = slugify($m['panchayat_en'] ?: $m['panchayat']);
                    $nameDisplay = $m['candidate_name'] . (!empty($m['candidate_name_en']) ? ' (' . $m['candidate_name_en'] . ')' : '');

                    // Avoid duplicate cards
                    $alreadyAdded = false;
                    foreach ($results['mukhiya'] as $existing) {
                        if (stripos($existing['title'], $m['candidate_name']) !== false && stripos($existing['title'], $m['panchayat']) !== false) {
                            $alreadyAdded = true;
                            break;
                        }
                    }

                    if (!$alreadyAdded) {
                        $results['mukhiya'][] = [
                            'type' => 'mukhiya',
                            'title' => '👑 Mukhiya: ' . $nameDisplay,
                            'subtitle' => '🌾 ' . $pName . ' Gram Panchayat',
                            'extra' => 'Block: ' . ($m['block_en'] ?: $m['block']) . ' | District: ' . $m['district'] . (!empty($m['mobile']) ? ' | 📞 ' . $m['mobile'] : '') . (!empty($m['category']) ? ' | ' . $m['category'] : ''),
                            'slug' => $pSlug,
                            'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                        ];
                    }
                }
            }

            // Also check Panchayats table for Sarpanchs and additional Mukhiya records
            $pClauses = [];
            $pParams = [];
            foreach (array_slice($combinedTerms, 0, 4) as $t) {
                if (strlen($t) < 2) continue;
                $tLike = "%$t%";
                $pClauses[] = "(current_mukhiya LIKE ? OR current_sarpanch LIKE ? OR panchayat_name LIKE ? OR panchayat_hi LIKE ?)";
                $pParams[] = $tLike;
                $pParams[] = $tLike;
                $pParams[] = $tLike;
                $pParams[] = $tLike;
            }

            if (!empty($pClauses)) {
                $stmtM = $pdo->prepare("SELECT panchayat_name, panchayat_hi, district, district_slug, block, current_mukhiya, current_sarpanch, mukhiya_category 
                                        FROM panchayats 
                                        WHERE (" . implode(' OR ', $pClauses) . ") 
                                          AND (current_mukhiya IS NOT NULL AND current_mukhiya != '' OR current_sarpanch IS NOT NULL AND current_sarpanch != '') 
                                        LIMIT 8");
                $stmtM->execute($pParams);
                while ($m = $stmtM->fetch(PDO::FETCH_ASSOC)) {
                    $pName = $m['panchayat_name'];
                    $dSlug = $m['district_slug'] ?: slugify($m['district']);
                    $bSlug = slugify($m['block']);
                    $pSlug = slugify($pName);

                    // Avoid duplicate mukhiya cards
                    if (!empty($m['current_mukhiya'])) {
                        $alreadyAdded = false;
                        foreach ($results['mukhiya'] as $existing) {
                            if (stripos($existing['title'], $m['current_mukhiya']) !== false && stripos($existing['title'], $pName) !== false) {
                                $alreadyAdded = true;
                                break;
                            }
                        }

                        if (!$alreadyAdded) {
                            $results['mukhiya'][] = [
                                'type' => 'mukhiya',
                                'title' => '👑 Mukhiya: ' . $m['current_mukhiya'],
                                'subtitle' => $pName . ' Gram Panchayat',
                                'extra' => 'Block: ' . $m['block'] . ' | District: ' . $m['district'] . (!empty($m['current_sarpanch']) ? ' | Sarpanch: ' . $m['current_sarpanch'] : ''),
                                'slug' => $pSlug,
                                'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                            ];
                        }
                    }

                    if (!empty($m['current_sarpanch'])) {
                        $alreadyAddedS = false;
                        foreach ($results['sarpanch'] as $existing) {
                            if (stripos($existing['title'], $m['current_sarpanch']) !== false && stripos($existing['title'], $pName) !== false) {
                                $alreadyAddedS = true;
                                break;
                            }
                        }
                        if (!$alreadyAddedS) {
                            $results['sarpanch'][] = [
                                'type' => 'sarpanch',
                                'title' => '⚖️ Sarpanch: ' . $m['current_sarpanch'],
                                'subtitle' => $pName . ' Gram Kutchery',
                                'extra' => 'Block: ' . $m['block'] . ' | District: ' . $m['district'] . (!empty($m['current_mukhiya']) ? ' | Mukhiya: ' . $m['current_mukhiya'] : ''),
                                'slug' => $pSlug,
                                'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                            ];
                        }
                    }
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Mukhiya search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 7. Search CD Blocks
// -------------------------------------------------------------
if ($pdo) {
    try {
        $bClauses = [];
        $bParams = [];
        foreach (array_slice($allTerms, 0, 3) as $t) {
            $bClauses[] = "sub_district LIKE ?";
            $bParams[] = "%$t%";
        }
        $stmtB = $pdo->prepare("SELECT sub_district, district_name, district_slug, sub_dist_code 
                                FROM census_subdistricts 
                                WHERE " . implode(' OR ', $bClauses) . " LIMIT 4");
        $stmtB->execute($bParams);
        while ($b = $stmtB->fetch(PDO::FETCH_ASSOC)) {
            $bSlug = slugify($b['sub_district']);
            $dSlug = $b['district_slug'] ?: slugify($b['district_name']);
            $results['block'][] = [
                'type' => 'block',
                'title' => '📍 ' . $b['sub_district'] . ' CD Block',
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

// -------------------------------------------------------------
// 8. Search Towns & Slums
// -------------------------------------------------------------
if ($pdo) {
    try {
        $tClauses = [];
        $tParams = [];
        foreach (array_slice($allTerms, 0, 3) as $t) {
            $tClauses[] = "(town_name LIKE ? OR town_code LIKE ?)";
            $tParams[] = "%$t%";
            $tParams[] = "%$t%";
        }
        $stmtT = $pdo->prepare("SELECT town_name, town_code, town_slug, district_name, district_slug, civic_status, population, slum_count 
                                FROM census_towns_2011 
                                WHERE " . implode(' OR ', $tClauses) . " LIMIT 4");
        $stmtT->execute($tParams);
        while ($t = $stmtT->fetch(PDO::FETCH_ASSOC)) {
            $tSlug = $t['town_slug'] ?: slugify($t['town_name']);
            $dSlug = $t['district_slug'] ?: slugify($t['district_name']);
            $results['town'][] = [
                'type' => 'town',
                'title' => '🏙️ ' . $t['town_name'] . ' (' . ($t['civic_status'] ?: 'Town') . ')',
                'subtitle' => 'District: ' . $t['district_name'] . ' | Pop: ' . number_format((int)$t['population']),
                'extra' => 'Census Code: ' . ($t['town_code'] ?: '—') . ($t['slum_count'] > 0 ? ' | ' . $t['slum_count'] . ' Slum Wards' : ''),
                'slug' => $tSlug,
                'url' => getTownUrl($dSlug, $tSlug)
            ];
        }
    } catch (Throwable $e) {
        error_log("Town search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 9. SMART ORDERING ACCORDING TO USER INTENT
// -------------------------------------------------------------
$final = [];

// Determine priority group order based on intent
if ($intent === 'mukhiya') {
    $order = ['mukhiya', 'village', 'sarpanch', 'mla', 'mp', 'mlc', 'block', 'district', 'town'];
} elseif ($intent === 'sarpanch') {
    $order = ['sarpanch', 'village', 'mukhiya', 'mla', 'mp', 'mlc', 'block', 'district', 'town'];
} elseif ($intent === 'village') {
    $order = ['village', 'mukhiya', 'sarpanch', 'block', 'district', 'mla', 'mp', 'mlc', 'town'];
} elseif ($intent === 'panchayat') {
    $order = ['mukhiya', 'sarpanch', 'village', 'block', 'district', 'mla', 'mp', 'mlc', 'town'];
} elseif ($intent === 'mlc') {
    $order = ['mlc', 'mla', 'mp', 'district', 'mukhiya', 'village', 'block', 'town'];
} elseif ($intent === 'mla') {
    $order = ['mla', 'mp', 'mlc', 'district', 'mukhiya', 'village', 'block', 'town'];
} elseif ($intent === 'mp') {
    $order = ['mp', 'mla', 'mlc', 'district', 'mukhiya', 'village', 'block', 'town'];
} else {
    // General search: Balanced view
    $order = ['district', 'mp', 'mla', 'mlc', 'mukhiya', 'sarpanch', 'village', 'block', 'town'];
}

foreach ($order as $category) {
    if (!empty($results[$category])) {
        foreach ($results[$category] as $item) {
            $final[] = $item;
        }
    }
}

// Return up to 35 results for full rich scrollable dropdown
echo json_encode(array_slice($final, 0, 35));
