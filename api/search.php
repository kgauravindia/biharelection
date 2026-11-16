<?php
/**
 * BiharElection.com - Comprehensive Unified Real-Time Search API
 * Full Multi-Tier Governance & Electoral Search:
 * 1. MLC (Bihar Vidhan Parishad - 75 Members)
 * 2. MLA (Bihar Vidhan Sabha - 243 Constituencies & MLAs)
 * 3. MP (Lok Sabha 40 & Rajya Sabha 15 Parliamentarians)
 * 4. Mukhiya (7,346+ Gram Mukhiyas)
 * 5. Sarpanch (8,053+ Gram Kutchery Heads)
 * 6. Ward Member (101,075+ WIMC Ward Representatives)
 * 7. Gram Panch (96,938+ Judicial Gram Kutchery Panchs)
 * 8. Zila Parishad Member (1,153+ District Board Members)
 * 9. Panchayat Samiti Member & Pramukh (8,752+ Block Representatives)
 * 10. Village (44,874 Census 2011 Rural Profiles)
 * 11. Gram Panchayat (8,400+ Gram Panchayats)
 * 12. CD Block (534 Sub-districts & Administrative Blocks)
 * 13. District (38 Bihar Districts)
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
} elseif (preg_match('/\b(ward\s*member|ward|वार्ड\s*सदस्य|वार्ड)\b/iu', $queryLower)) {
    $intent = 'ward';
} elseif (preg_match('/\b(panch\s*member|gram\s*panch|panch|ग्राम\s*कचहरी\s*पंच|कचहरी\s*पंच|पंच)\b/iu', $queryLower)) {
    $intent = 'panch';
} elseif (preg_match('/\b(zila\s*parishad|jila\s*parisad|jila\s*parishad|zila\s*parisad|zp|जिला\s*परिषद|जिला\s*पार्षद)\b/iu', $queryLower)) {
    $intent = 'zila_parishad';
} elseif (preg_match('/\b(panchayat\s*samiti|samiti|bdc|pramukh|प्रखंड\s*प्रमुख|पंचायत\s*समिति)\b/iu', $queryLower)) {
    $intent = 'panchayat_samiti';
} elseif (preg_match('/\b(mlc|vidhan\s*parishad|parishad|graduate|graduates|teacher|teachers|परिषद|एमएलसी|विधान\s*पार्षद|स्नातक|शिक्षक)\b/iu', $queryLower)) {
    $intent = 'mlc';
} elseif (preg_match('/\b(mla|vidhayak|विधायक|विधानसभा|vidhan\s*sabha)\b/iu', $queryLower)) {
    $intent = 'mla';
} elseif (preg_match('/\b(mp|sansad|सांसद|lok\s*sabha|rajya\s*sabha|संसद|लोकसभा|राज्यसभा)\b/iu', $queryLower)) {
    $intent = 'mp';
} elseif (preg_match('/\b(village|gaon|gao|gram|गाँव|गांव|ग्राम)\b/iu', $queryLower)) {
    $intent = 'village';
} elseif (preg_match('/\b(panchayat|pnachayat|पंचायत)\b/iu', $queryLower)) {
    $intent = 'panchayat';
} elseif (preg_match('/\b(block|prakhand|प्रखंड|ब्लॉक)\b/iu', $queryLower)) {
    $intent = 'block';
} elseif (preg_match('/\b(district|zila|जिला)\b/iu', $queryLower)) {
    $intent = 'district';
}

// Extract Ward Number if user entered e.g. "Ward 5", "वार्ड 3"
$extractedWardNo = null;
if (preg_match('/\b(?:ward|वार्ड)\s*#?([0-9]{1,3})\b/iu', $rawQuery, $wM)) {
    $extractedWardNo = (int)$wM[1];
}

// Extract Territory Number if user entered e.g. "Territory 12", "क्षे० सं० 4"
$extractedTerritoryNo = null;
if (preg_match('/\b(?:territory|terr|क्षेत्र|क्षे०\s*सं०?)\s*#?([0-9]{1,3})\b/iu', $rawQuery, $tM)) {
    $extractedTerritoryNo = (int)$tM[1];
}

// Clean query: strip out filler/intent keywords, punctuation, stop words
$cleanQuery = preg_replace('/(?:\b(mukhiya|sarpanch|pradhan|ward\s*member|ward|panch\s*member|gram\s*panch|panch|zila\s*parishad|jila\s*parisad|jila\s*parishad|zila\s*parisad|zp|panchayat\s*samiti|samiti|bdc|pramukh|mla|mp|mlc|vidhayak|sansad|parishad|vidhan\s*parishad|lok\s*sabha|rajya\s*sabha|village|gaon|gao|gram|panchayat|pnachayat|block|prakhand|district|zila|of|in|the|at|ka|ki|ke|me|mein|to|and|मुखिया|सरपंच|वार्ड\s*सदस्य|वार्ड|ग्राम\s*कचहरी\s*पंच|कचहरी\s*पंच|पंच|जिला\s*परिषद|जिला\s*पार्षद|पंचायत\s*समिति|प्रखंड\s*प्रमुख|विधायक|सांसद|विधान\s*पार्षद|लोकसभा|राज्यसभा|संसद|गांव|गाँव|ग्राम|पंचायत|प्रखंड|जिला|का|की|के|में)\b|[\/,.\-\(\)]+)/iu', ' ', $rawQuery);
$cleanQuery = trim(preg_replace('/\s+/', ' ', $cleanQuery));

$isQueryEmptyOrIntentOnly = (empty($cleanQuery) || strlen($cleanQuery) < 2);

// Bilingual Hindi-English name mapping helper
if (!function_exists('getBilingualTokens')) {
    function getBilingualTokens($text) {
        static $dict = [
            'rajesh' => 'राजेश', 'manoj' => 'मनोज', 'suresh' => 'सुरेश', 'dinesh' => 'दिनेश',
            'ramesh' => 'रमेश', 'mukesh' => 'मुकेश', 'mahesh' => 'महेश', 'vinod' => 'विनोद',
            'pramod' => 'प्रमोद', 'santosh' => 'संतोष', 'sunil' => 'सुनील', 'anil' => 'अनिल',
            'ashok' => 'अशोक', 'alok' => 'आलोक', 'sanjay' => 'संजय', 'ajay' => 'अजय',
            'vijay' => 'विजय', 'vikas' => 'विकास', 'vikash' => 'विकास', 'vivek' => 'विवेक',
            'vishal' => 'विशाल', 'pankaj' => 'पंकज', 'deepak' => 'दीपक', 'amit' => 'अमित',
            'sumit' => 'सुमित', 'rahul' => 'राहुल', 'rohit' => 'रोहित', 'neeraj' => 'नीरज',
            'dheeraj' => 'धीरज', 'pawan' => 'पवन', 'chandan' => 'चंदन', 'kundan' => 'कुंदन',
            'sonu' => 'सोनू', 'monu' => 'मोनू', 'pintu' => 'पिंटू', 'rinku' => 'रिंकू',
            'bablu' => 'बबलू', 'munna' => 'मुन्ना', 'savita' => 'सविता', 'sunita' => 'सुनीता',
            'anita' => 'अनिता', 'kavita' => 'कविता', 'geeta' => 'गीता', 'seeta' => 'सीता',
            'reeta' => 'रीता', 'meena' => 'मीना', 'rekha' => 'रेखा', 'manju' => 'मंजू',
            'pushpa' => 'पुष्पा', 'shanti' => 'शांति', 'shobha' => 'शोभा', 'kiran' => 'किरण',
            'asha' => 'आशा', 'urmila' => 'उर्मिला', 'prabhavati' => 'प्रभावती', 'rani' => 'रानी',
            'pooja' => 'पूजा', 'puja' => 'पूजा', 'aarti' => 'आरती', 'priyanka' => 'प्रियंका',
            'singh' => 'सिंह', 'kumar' => 'कुमार', 'kumari' => 'कुमारी', 'devi' => 'देवी',
            'prasad' => 'प्रसाद', 'yadav' => 'यादव', 'sharma' => 'शर्मा', 'varma' => 'वर्मा',
            'mishra' => 'मिश्रा', 'jha' => 'झा', 'thakur' => 'ठाकुर', 'choudhary' => 'चौधरी',
            'chaudhary' => 'चौधरी', 'gupta' => 'गुप्ता', 'shah' => 'शाह', 'sah' => 'साह',
            'sahu' => 'साहू', 'rai' => 'राय', 'mandal' => 'मंडल', 'manjhi' => 'मांझी',
            'paswan' => 'पासवान', 'alam' => 'आलम', 'ansari' => 'अंसारी', 'khatoon' => 'खातून',
            'khatun' => 'खातून', 'begum' => 'बेगम', 'ahmad' => 'अहमद', 'ahmed' => 'अहमद',
            'hussain' => 'हुसैन', 'ali' => 'अली', 'khan' => 'खान', 'tiwari' => 'तिवारी',
            'pandey' => 'पांडेय', 'dubey' => 'दुबे', 'ojha' => 'ओझा', 'tripathi' => 'त्रिपाठी',
            'sinha' => 'सिन्हा', 'mahto' => 'महतो', 'sahni' => 'सहनी', 'kushwaha' => 'कुशवाहा',
            'kurmi' => 'कुर्मी', 'patel' => 'पटेल', 'sada' => 'सदा', 'rajak' => 'रजक'
        ];

        $tokens = preg_split('/[\s\-_,\/]+/u', strtolower(trim($text)));
        $hiTokens = [];
        foreach ($tokens as $tok) {
            if (isset($dict[$tok])) {
                $hiTokens[] = $dict[$tok];
            }
        }
        return $hiTokens;
    }
}

// Generate phonetic and common Bihar place spelling variants
if (!function_exists('getPhoneticPlaceVariants')) {
    function getPhoneticPlaceVariants($word) {
        $variants = [];
        $w = strtolower(trim($word));
        $variants[] = $w;
        
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
        if (strpos($w, 'har') !== false) {
            $variants[] = str_replace('har', '', $w);
            $variants[] = str_replace('har', 'hr', $w);
        }
        if (substr($w, -3) === 'era') {
            $variants[] = substr($w, 0, -3) . 'ra';
        }
        $variants[] = preg_replace('/([aeiou])\1+/', '$1', $w);
        
        return array_values(array_unique(array_filter($variants)));
    }
}

$locationTerms = getPhoneticPlaceVariants($cleanQuery);
$bilingualHindiTokens = getBilingualTokens($cleanQuery);

$allTerms = array_values(array_unique(array_filter(array_merge([$cleanQuery, $rawQuery], $locationTerms, $bilingualHindiTokens))));

// Check if numeric mobile search (4-10 digits)
$isMobileQuery = (bool)preg_match('/^[0-9]{4,10}$/', trim($cleanQuery)) || (bool)preg_match('/^[0-9]{4,10}$/', trim($rawQuery));
$cleanMobileDigits = preg_replace('/[^0-9]/', '', $cleanQuery ?: $rawQuery);

$results = [
    'district' => [],
    'mla' => [],
    'mp' => [],
    'mlc' => [],
    'mukhiya' => [],
    'sarpanch' => [],
    'zila_parishad' => [],
    'panchayat_samiti' => [],
    'ward' => [],
    'panch' => [],
    'village' => [],
    'panchayat' => [],
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
// 1. Search Districts (38 Districts)
// -------------------------------------------------------------
$districts = DataProvider::getDistricts();
if ($intent === 'district' && $isQueryEmptyOrIntentOnly) {
    // Return major/featured districts
    foreach (array_slice($districts, 0, 8) as $d) {
        $results['district'][] = [
            'type' => 'district',
            'title' => '🏢 ' . $d['name'] . ' District' . (!empty($d['name_hi']) ? ' (' . $d['name_hi'] . ')' : ''),
            'subtitle' => ($d['division'] ?? 'Bihar') . ' Division | HQ: ' . ($d['headquarters'] ?? $d['name']),
            'extra' => 'Explore District Profile, MLAs, CD Blocks & Panchayats',
            'slug' => $d['slug'],
            'url' => getDistrictUrl($d['slug'])
        ];
    }
} else {
    foreach ($districts as $d) {
        if (termMatches($d['name'], $allTerms) || 
            termMatches($d['name_hi'] ?? '', $allTerms) || 
            termMatches($d['headquarters'] ?? '', $allTerms) ||
            termMatches($d['division'] ?? '', $allTerms)) {
            
            $results['district'][] = [
                'type' => 'district',
                'title' => '🏢 ' . $d['name'] . ' District' . (!empty($d['name_hi']) ? ' (' . $d['name_hi'] . ')' : ''),
                'subtitle' => ($d['division'] ?? 'Bihar') . ' Division | HQ: ' . ($d['headquarters'] ?? $d['name']),
                'extra' => 'Explore District Profile, MLAs, CD Blocks & Panchayats',
                'slug' => $d['slug'],
                'url' => getDistrictUrl($d['slug'])
            ];
        }
    }
}

// -------------------------------------------------------------
// 2. Search MPs (Lok Sabha 40 & Rajya Sabha 15)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'mp' && $isQueryEmptyOrIntentOnly) {
            // Return top Lok Sabha and Rajya Sabha MPs
            $stmtDefLS = $pdo->query("SELECT pc_no, pc_name, mp_name, party FROM mps_loksabha ORDER BY CAST(pc_no AS UNSIGNED) ASC LIMIT 6");
            while ($mp = $stmtDefLS->fetch(PDO::FETCH_ASSOC)) {
                $results['mp'][] = [
                    'type' => 'mp',
                    'title' => '🏛️ PC ' . $mp['pc_no'] . ' — ' . $mp['pc_name'] . ' (Lok Sabha)',
                    'subtitle' => 'MP: ' . $mp['mp_name'] . ' (' . $mp['party'] . ')',
                    'extra' => 'Lok Sabha Parliamentarian from Bihar',
                    'slug' => slugify($mp['pc_name']),
                    'url' => SITE_URL . '/mp'
                ];
            }
        } else {
            $mpSql = "SELECT pc_no, pc_name, mp_name, party FROM mps_loksabha WHERE ";
            $mpClauses = [];
            $mpParams = [];
            foreach (array_slice($allTerms, 0, 3) as $t) {
                if (strlen($t) < 2) continue;
                $mpClauses[] = "(pc_name LIKE ? OR mp_name LIKE ? OR party LIKE ?)";
                $tLike = "%$t%";
                $mpParams[] = $tLike;
                $mpParams[] = $tLike;
                $mpParams[] = $tLike;
            }
            if (!empty($mpClauses)) {
                $mpSql .= implode(' OR ', $mpClauses) . " LIMIT 6";
                $stmtLS = $pdo->prepare($mpSql);
                $stmtLS->execute($mpParams);
                while ($mp = $stmtLS->fetch(PDO::FETCH_ASSOC)) {
                    $results['mp'][] = [
                        'type' => 'mp',
                        'title' => '🏛️ PC ' . $mp['pc_no'] . ' — ' . $mp['pc_name'] . ' (Lok Sabha)',
                        'subtitle' => 'MP: ' . $mp['mp_name'] . ' (' . $mp['party'] . ')',
                        'extra' => 'Lok Sabha Parliamentarian from Bihar',
                        'slug' => slugify($mp['pc_name']),
                        'url' => SITE_URL . '/mp'
                    ];
                }
            }

            // Rajya Sabha
            $rsSql = "SELECT mp_name, party, tenure FROM mps_rajyasabha WHERE ";
            $rsClauses = [];
            $rsParams = [];
            foreach (array_slice($allTerms, 0, 3) as $t) {
                if (strlen($t) < 2) continue;
                $rsClauses[] = "(mp_name LIKE ? OR party LIKE ?)";
                $tLike = "%$t%";
                $rsParams[] = $tLike;
                $rsParams[] = $tLike;
            }
            if (!empty($rsClauses)) {
                $rsSql .= implode(' OR ', $rsClauses) . " LIMIT 4";
                $stmtRS = $pdo->prepare($rsSql);
                $stmtRS->execute($rsParams);
                while ($rs = $stmtRS->fetch(PDO::FETCH_ASSOC)) {
                    $results['mp'][] = [
                        'type' => 'mp',
                        'title' => '🏛️ ' . $rs['mp_name'] . ' (Rajya Sabha MP)',
                        'subtitle' => 'Party: ' . $rs['party'] . ' | Tenure: ' . ($rs['tenure'] ?: 'Current'),
                        'extra' => 'Upper House Member representing Bihar',
                        'slug' => slugify($rs['mp_name']),
                        'url' => SITE_URL . '/rajya-sabha'
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        error_log("MP search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 3. Search MLAs (Vidhan Sabha 243 Constituencies & MLAs)
// -------------------------------------------------------------
$acs = DataProvider::getConstituencies();
if ($intent === 'mla' && $isQueryEmptyOrIntentOnly) {
    foreach (array_slice($acs, 0, 8) as $ac) {
        $results['mla'][] = [
            'type' => 'mla',
            'title' => '🗳️ AC ' . $ac['ac_no'] . ' — ' . $ac['name'] . (!empty($ac['name_hi']) ? ' (' . $ac['name_hi'] . ')' : ''),
            'subtitle' => 'MLA: ' . ($ac['current_mla'] ?: 'Vacant') . ' (' . ($ac['current_party'] ?: 'IND') . ')',
            'extra' => 'District: ' . $ac['district'] . ' | Total Electors: ' . number_format($ac['total_electors']),
            'slug' => $ac['slug'],
            'url' => getMlaUrl($ac)
        ];
    }
} else {
    foreach ($acs as $ac) {
        if (termMatches($ac['name'], $allTerms) || 
            termMatches($ac['name_hi'] ?? '', $allTerms) || 
            termMatches((string)$ac['ac_no'], $allTerms) || 
            termMatches($ac['district'], $allTerms) ||
            termMatches($ac['current_mla'], $allTerms) ||
            termMatches($ac['current_party'] ?? '', $allTerms)) {
            
            $results['mla'][] = [
                'type' => 'mla',
                'title' => '🗳️ AC ' . $ac['ac_no'] . ' — ' . $ac['name'] . (!empty($ac['name_hi']) ? ' (' . $ac['name_hi'] . ')' : ''),
                'subtitle' => 'MLA: ' . ($ac['current_mla'] ?: 'Vacant') . ' (' . ($ac['current_party'] ?: 'IND') . ')',
                'extra' => 'District: ' . $ac['district'] . ' | Total Electors: ' . number_format($ac['total_electors']),
                'slug' => $ac['slug'],
                'url' => getMlaUrl($ac)
            ];
        }
    }
}

// -------------------------------------------------------------
// 4. Search MLCs (Bihar Vidhan Parishad 75 Members)
// -------------------------------------------------------------
$mlcs = DataProvider::getMlcs();
$isMlcQuery = ($intent === 'mlc') || preg_match('/\b(mlc|vidhan\s*parishad|parishad|graduate|graduates|teacher|teachers|परिषद|एमएलसी|विधान\s*पार्षद|स्नातक|शिक्षक)\b/iu', $rawQuery);

if ($intent === 'mlc' && $isQueryEmptyOrIntentOnly) {
    foreach (array_slice($mlcs, 0, 8) as $m) {
        $mName = $m['name'] ?? '';
        if (strtolower(trim($mName)) === 'vacant') continue;
        $mlcSlug = !empty($m['slug']) ? $m['slug'] : slugify($mName);
        $results['mlc'][] = [
            'type' => 'mlc',
            'title' => '📜 ' . $mName . ' (Vidhan Parishad MLC)',
            'subtitle' => 'Constituency: ' . ($m['constituency'] ?? '') . (!empty($m['party']) ? ' | Party: ' . $m['party'] : ''),
            'extra' => 'Bihar Legislative Council' . (!empty($m['tenure']) ? ' | Tenure: ' . $m['tenure'] : ''),
            'slug' => $mlcSlug,
            'url' => SITE_URL . '/mlc/' . $mlcSlug
        ];
    }
} else {
    foreach ($mlcs as $m) {
        $mName = $m['name'] ?? '';
        if (strtolower(trim($mName)) === 'vacant') continue;

        $const = $m['constituency'] ?? '';
        $party = $m['party'] ?? '';
        $mTenure = $m['tenure'] ?? '';

        $matchesMlc = false;
        if (termMatches($mName, $allTerms) || 
            termMatches($m['name_hi'] ?? '', $allTerms) || 
            termMatches($const, $allTerms) || 
            termMatches($party, $allTerms)) {
            $matchesMlc = true;
        }

        // Multi-word token matching for queries like "Patna Graduates", "Saran Teachers", "Neeraj Kumar MLC"
        if (!$matchesMlc) {
            $cLower = strtolower($const);
            $extraTokens = [];
            if (strpos($cLower, 'graduate') !== false) {
                $extraTokens[] = 'graduates';
                $extraTokens[] = 'graduate';
            }
            if (strpos($cLower, 'teacher') !== false) {
                $extraTokens[] = 'teachers';
                $extraTokens[] = 'teacher';
            }
            $targetStr = strtolower($mName . ' ' . ($m['name_hi'] ?? '') . ' ' . $const . ' ' . $party . ' ' . implode(' ', $extraTokens));

            // Check if every non-stopword in rawQuery or cleanQuery matches
            $rawWords = preg_split('/[\s\-_,\/]+/u', strtolower($rawQuery));
            $meaningfulWords = array_filter($rawWords, fn($w) => strlen($w) >= 3 && !in_array($w, ['the', 'and', 'for', 'mlc', 'bihar']));
            if (!empty($meaningfulWords)) {
                $allWordsOk = true;
                foreach ($meaningfulWords as $w) {
                    $wNorm = rtrim($w, 's');
                    if (strpos($targetStr, $wNorm) === false) {
                        $allWordsOk = false;
                        break;
                    }
                }
                if ($allWordsOk) {
                    $matchesMlc = true;
                }
            }
        }

        if ($matchesMlc) {
            $mlcSlug = !empty($m['slug']) ? $m['slug'] : slugify($mName);
            $results['mlc'][] = [
                'type' => 'mlc',
                'title' => '📜 ' . $mName . ' (Vidhan Parishad MLC)',
                'subtitle' => 'Constituency: ' . $const . (!empty($party) ? ' | Party: ' . $party : ''),
                'extra' => 'Bihar Legislative Council' . (!empty($mTenure) ? ' | Tenure: ' . $mTenure : '') . (!empty($m['contact']) ? ' | Mobile: ' . $m['contact'] : ''),
                'slug' => $mlcSlug,
                'url' => SITE_URL . '/mlc/' . $mlcSlug
            ];
            if (count($results['mlc']) >= 8) break;
        }
    }
}

// -------------------------------------------------------------
// 5. Search Census Villages & Connect to Politicians (Village -> Mukhiya, Sarpanch, MLA, MP)
// -------------------------------------------------------------
$matchedVillages = [];
$matchedPanchayatNames = [];

if ($pdo) {
    try {
        if ($intent === 'village' && $isQueryEmptyOrIntentOnly) {
            // Return top largest villages in Bihar
            $stmtDefV = $pdo->query("SELECT village_name, village_code, village_slug, district_name, district_slug, sub_district_name, cd_block_name, gram_panchayat_name, population 
                                     FROM census_villages_2011 
                                     ORDER BY population DESC LIMIT 8");
            $matchedVillages = $stmtDefV->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $vClauses = [];
            $vParams = [];
            foreach (array_slice($locationTerms, 0, 5) as $t) {
                if (strlen($t) < 2) continue;
                $vClauses[] = "(village_name LIKE ? OR gram_panchayat_name LIKE ? OR village_code = ?)";
                $tLike = "%$t%";
                $vParams[] = $tLike;
                $vParams[] = $tLike;
                $vParams[] = $t;
            }
            if (!empty($vClauses)) {
                $vSql = "SELECT village_name, village_code, village_slug, district_name, district_slug, sub_district_name, cd_block_name, gram_panchayat_name, population 
                         FROM census_villages_2011 
                         WHERE " . implode(' OR ', $vClauses) . " LIMIT 12";
                $stmtV = $pdo->prepare($vSql);
                $stmtV->execute($vParams);
                $matchedVillages = $stmtV->fetchAll(PDO::FETCH_ASSOC);
            }
        }

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
                $districtPanchayats = getDistrictPanchayatsCached($pdo, $dSlug);
                $foundP = null;
                foreach ($districtPanchayats as $pr) {
                    if (isBlockMatch($pr['block'], $bName) && 
                        (isPanchayatMatch($pr['panchayat_name'], $gpName) || isPanchayatMatch($pr['panchayat_name'], $v['village_name']))) {
                        $foundP = $pr;
                        break;
                    }
                }
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

            if (!empty($mukhiyaName) && ($intent === 'mukhiya' || $intent === 'general' || $intent === 'panchayat')) {
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

            $gpSubtitle = 'GP: ' . $panchayatDisplay;
            if (!empty($mukhiyaName)) $gpSubtitle .= ' | Mukhiya: ' . $mukhiyaName;
            if (!empty($sarpanchName)) $gpSubtitle .= ' | Sarpanch: ' . $sarpanchName;

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
// 6. Direct Search in Mukhiyas Directory (7,346 Mukhiyas)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'mukhiya' && $isQueryEmptyOrIntentOnly) {
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
            $stmtMob = $pdo->prepare("SELECT candidate_name, candidate_name_en, post, district, district_slug, block, block_en, panchayat, panchayat_en, category, mobile, tenure 
                                      FROM mukhiyas 
                                      WHERE mobile LIKE ? LIMIT 6");
            $stmtMob->execute(["%$cleanMobileDigits%"]);
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
            $mClauses = [];
            $mParams = [];
            foreach (array_slice($allTerms, 0, 5) as $t) {
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
                         LIMIT 8";
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
        }
    } catch (Throwable $e) {
        error_log("Mukhiya search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 7. Search Sarpanchs & Gram Panchayats (`panchayats` table)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'sarpanch' && $isQueryEmptyOrIntentOnly) {
            // Return top Sarpanchs from panchayats
            $stmtDefSarp = $pdo->query("SELECT panchayat_name, panchayat_hi, district, district_slug, block, current_sarpanch, sarpanch_mobile, sarpanch_category 
                                       FROM panchayats 
                                       WHERE current_sarpanch IS NOT NULL AND current_sarpanch != '' 
                                       ORDER BY id ASC LIMIT 8");
            while ($m = $stmtDefSarp->fetch(PDO::FETCH_ASSOC)) {
                $pName = $m['panchayat_name'] . (!empty($m['panchayat_hi']) && $m['panchayat_hi'] !== $m['panchayat_name'] ? ' (' . $m['panchayat_hi'] . ')' : '');
                $dSlug = $m['district_slug'] ?: slugify($m['district']);
                $bSlug = slugify($m['block']);
                $pSlug = slugify($m['panchayat_name']);
                $results['sarpanch'][] = [
                    'type' => 'sarpanch',
                    'title' => '⚖️ Sarpanch: ' . $m['current_sarpanch'],
                    'subtitle' => '🌾 ' . $pName . ' Gram Kutchery',
                    'extra' => 'Block: ' . $m['block'] . ' | District: ' . $m['district'] . (!empty($m['sarpanch_mobile']) ? ' | 📞 ' . $m['sarpanch_mobile'] : '') . (!empty($m['sarpanch_category']) ? ' | ' . $m['sarpanch_category'] : ''),
                    'slug' => $pSlug,
                    'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                ];
            }
        } elseif ($intent === 'panchayat' && $isQueryEmptyOrIntentOnly) {
            // Return top Gram Panchayats
            $stmtDefP = $pdo->query("SELECT panchayat_name, panchayat_hi, district, district_slug, block, current_mukhiya, total_wards 
                                    FROM panchayats 
                                    ORDER BY id ASC LIMIT 8");
            while ($m = $stmtDefP->fetch(PDO::FETCH_ASSOC)) {
                $pName = $m['panchayat_name'] . (!empty($m['panchayat_hi']) && $m['panchayat_hi'] !== $m['panchayat_name'] ? ' (' . $m['panchayat_hi'] . ')' : '');
                $dSlug = $m['district_slug'] ?: slugify($m['district']);
                $bSlug = slugify($m['block']);
                $pSlug = slugify($m['panchayat_name']);
                $results['panchayat'][] = [
                    'type' => 'panchayat',
                    'title' => '🏡 ' . $pName . ' Gram Panchayat',
                    'subtitle' => 'Local Self Government Profile &bull; ' . $m['block'] . ' Block',
                    'extra' => 'Block: ' . $m['block'] . ' | District: ' . $m['district'] . (!empty($m['current_mukhiya']) ? ' | Mukhiya: ' . $m['current_mukhiya'] : ''),
                    'slug' => $pSlug,
                    'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                ];
            }
        } else {
            $pClauses = [];
            $pParams = [];
            $combinedTerms = array_values(array_unique(array_filter(array_merge([$cleanQuery, $rawQuery], $locationTerms, $allTerms, $matchedPanchayatNames))));

            foreach (array_slice($combinedTerms, 0, 4) as $t) {
                if (strlen($t) < 2) continue;
                $tLike = "%$t%";
                $pClauses[] = "(current_sarpanch LIKE ? OR current_mukhiya LIKE ? OR panchayat_name LIKE ? OR panchayat_hi LIKE ? OR sarpanch_mobile LIKE ?)";
                for ($i = 0; $i < 5; $i++) {
                    $pParams[] = $tLike;
                }
            }

            if (!empty($pClauses)) {
                $stmtM = $pdo->prepare("SELECT panchayat_name, panchayat_hi, district, district_slug, block, current_mukhiya, current_sarpanch, sarpanch_mobile, mukhiya_category, sarpanch_category, total_wards 
                                        FROM panchayats 
                                        WHERE (" . implode(' OR ', $pClauses) . ") LIMIT 8");
                $stmtM->execute($pParams);
                while ($m = $stmtM->fetch(PDO::FETCH_ASSOC)) {
                    $pName = $m['panchayat_name'] . (!empty($m['panchayat_hi']) && $m['panchayat_hi'] !== $m['panchayat_name'] ? ' (' . $m['panchayat_hi'] . ')' : '');
                    $dSlug = $m['district_slug'] ?: slugify($m['district']);
                    $bSlug = slugify($m['block']);
                    $pSlug = slugify($m['panchayat_name']);

                    // Sarpanch Card
                    if (!empty($m['current_sarpanch'])) {
                        $alreadyAddedS = false;
                        foreach ($results['sarpanch'] as $existing) {
                            if (stripos($existing['title'], $m['current_sarpanch']) !== false && stripos($existing['title'], $m['panchayat_name']) !== false) {
                                $alreadyAddedS = true;
                                break;
                            }
                        }
                        if (!$alreadyAddedS) {
                            $results['sarpanch'][] = [
                                'type' => 'sarpanch',
                                'title' => '⚖️ Sarpanch: ' . $m['current_sarpanch'],
                                'subtitle' => '🌾 ' . $pName . ' Gram Kutchery',
                                'extra' => 'Block: ' . $m['block'] . ' | District: ' . $m['district'] . (!empty($m['sarpanch_mobile']) ? ' | 📞 ' . $m['sarpanch_mobile'] : '') . (!empty($m['sarpanch_category']) ? ' | ' . $m['sarpanch_category'] : ''),
                                'slug' => $pSlug,
                                'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                            ];
                        }
                    }

                    // Dedicated Gram Panchayat Card
                    if ($intent === 'panchayat' || $intent === 'general') {
                        $alreadyAddedP = false;
                        foreach ($results['panchayat'] as $existing) {
                            if (stripos($existing['title'], $m['panchayat_name']) !== false && stripos($existing['extra'], $m['district']) !== false) {
                                $alreadyAddedP = true;
                                break;
                            }
                        }
                        if (!$alreadyAddedP) {
                            $gpExtra = 'Block: ' . $m['block'] . ' | District: ' . $m['district'];
                            if (!empty($m['total_wards'])) $gpExtra .= ' | ' . $m['total_wards'] . ' Wards';
                            if (!empty($m['current_mukhiya'])) $gpExtra .= ' | Mukhiya: ' . $m['current_mukhiya'];

                            $results['panchayat'][] = [
                                'type' => 'panchayat',
                                'title' => '🏡 ' . $pName . ' Gram Panchayat',
                                'subtitle' => 'Local Self Government Profile &bull; ' . $m['block'] . ' Block',
                                'extra' => $gpExtra,
                                'slug' => $pSlug,
                                'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug)
                            ];
                        }
                    }
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Panchayat search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 8. Search Ward Members (`ward_21_26` table, 101,075 members)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'ward' && $isQueryEmptyOrIntentOnly && $extractedWardNo === null) {
            // Return top Ward Members
            $stmtDefW = $pdo->query("SELECT candidate_name, ward_no, panchayat, block, district, district_slug, mobile, reservation_status, category 
                                     FROM ward_21_26 
                                     WHERE candidate_name IS NOT NULL AND candidate_name != '' 
                                     ORDER BY id ASC LIMIT 8");
            while ($w = $stmtDefW->fetch(PDO::FETCH_ASSOC)) {
                $dSlug = $w['district_slug'] ?: slugify($w['district']);
                $bSlug = slugify($w['block']);
                $pSlug = slugify($w['panchayat']);
                $results['ward'][] = [
                    'type' => 'ward',
                    'title' => '🗳️ Ward ' . $w['ward_no'] . ' Member: ' . $w['candidate_name'],
                    'subtitle' => '🌾 ' . $w['panchayat'] . ' Gram Panchayat (Ward ' . $w['ward_no'] . ')',
                    'extra' => 'Block: ' . $w['block'] . ' | District: ' . $w['district'] . (!empty($w['mobile']) ? ' | 📞 ' . $w['mobile'] : '') . (!empty($w['reservation_status']) ? ' | ' . $w['reservation_status'] : ''),
                    'slug' => $pSlug,
                    'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug) . '#tab-wards'
                ];
            }
        } else {
            $wClauses = [];
            $wParams = [];

            if ($isMobileQuery) {
                $wClauses[] = "mobile LIKE ?";
                $wParams[] = "%$cleanMobileDigits%";
            } elseif ($extractedWardNo !== null) {
                $wClauses[] = "ward_no = ?";
                $wParams[] = $extractedWardNo;
                if (!empty($cleanQuery) && strlen($cleanQuery) >= 2) {
                    $wClauses[] = "(candidate_name LIKE ? OR panchayat LIKE ? OR block LIKE ? OR district LIKE ?)";
                    $qLike = "%$cleanQuery%";
                    $wParams[] = $qLike;
                    $wParams[] = $qLike;
                    $wParams[] = $qLike;
                    $wParams[] = $qLike;
                }
            } else {
                foreach (array_slice($allTerms, 0, 3) as $t) {
                    if (strlen($t) < 2) continue;
                    $tLike = "%$t%";
                    $wClauses[] = "(candidate_name LIKE ? OR panchayat LIKE ? OR block LIKE ? OR district LIKE ? OR mobile LIKE ?)";
                    for ($i = 0; $i < 5; $i++) {
                        $wParams[] = $tLike;
                    }
                }
            }

            if (!empty($wClauses)) {
                $wSql = "SELECT candidate_name, ward_no, panchayat, block, district, district_slug, mobile, reservation_status, category 
                         FROM ward_21_26 
                         WHERE " . implode(' OR ', $wClauses) . " 
                         LIMIT 6";
                $stmtW = $pdo->prepare($wSql);
                $stmtW->execute($wParams);
                while ($w = $stmtW->fetch(PDO::FETCH_ASSOC)) {
                    $dSlug = $w['district_slug'] ?: slugify($w['district']);
                    $bSlug = slugify($w['block']);
                    $pSlug = slugify($w['panchayat']);

                    $results['ward'][] = [
                        'type' => 'ward',
                        'title' => '🗳️ Ward ' . $w['ward_no'] . ' Member: ' . $w['candidate_name'],
                        'subtitle' => '🌾 ' . $w['panchayat'] . ' Gram Panchayat (Ward ' . $w['ward_no'] . ')',
                        'extra' => 'Block: ' . $w['block'] . ' | District: ' . $w['district'] . (!empty($w['mobile']) ? ' | 📞 ' . $w['mobile'] : '') . (!empty($w['reservation_status']) ? ' | ' . $w['reservation_status'] : ''),
                        'slug' => $pSlug,
                        'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug) . '#tab-wards'
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Ward search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 9. Search Gram Panch (`panch_21_26` table, 96,938 members)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'panch' && $isQueryEmptyOrIntentOnly && $extractedWardNo === null) {
            // Return top Gram Panchs
            $stmtDefPch = $pdo->query("SELECT candidate_name, ward_no, panchayat, block, district, district_slug, mobile, reservation_status, category 
                                      FROM panch_21_26 
                                      WHERE candidate_name IS NOT NULL AND candidate_name != '' 
                                      ORDER BY id ASC LIMIT 8");
            while ($p = $stmtDefPch->fetch(PDO::FETCH_ASSOC)) {
                $dSlug = $p['district_slug'] ?: slugify($p['district']);
                $bSlug = slugify($p['block']);
                $pSlug = slugify($p['panchayat']);
                $results['panch'][] = [
                    'type' => 'panch',
                    'title' => '⚖️ Gram Panch: ' . $p['candidate_name'] . ' (Ward ' . $p['ward_no'] . ')',
                    'subtitle' => '🌾 ' . $p['panchayat'] . ' Gram Kutchery (Ward ' . $p['ward_no'] . ')',
                    'extra' => 'Block: ' . $p['block'] . ' | District: ' . $p['district'] . (!empty($p['mobile']) ? ' | 📞 ' . $p['mobile'] : '') . ' | Judicial Ward Panch',
                    'slug' => $pSlug,
                    'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug) . '#tab-panch'
                ];
            }
        } else {
            $pClauses = [];
            $pParams = [];

            if ($isMobileQuery) {
                $pClauses[] = "mobile LIKE ?";
                $pParams[] = "%$cleanMobileDigits%";
            } elseif ($extractedWardNo !== null) {
                $pClauses[] = "ward_no = ?";
                $pParams[] = $extractedWardNo;
                if (!empty($cleanQuery) && strlen($cleanQuery) >= 2) {
                    $pClauses[] = "(candidate_name LIKE ? OR panchayat LIKE ? OR block LIKE ? OR district LIKE ?)";
                    $qLike = "%$cleanQuery%";
                    $pParams[] = $qLike;
                    $pParams[] = $qLike;
                    $pParams[] = $qLike;
                    $pParams[] = $qLike;
                }
            } else {
                foreach (array_slice($allTerms, 0, 3) as $t) {
                    if (strlen($t) < 2) continue;
                    $tLike = "%$t%";
                    $pClauses[] = "(candidate_name LIKE ? OR panchayat LIKE ? OR block LIKE ? OR district LIKE ? OR mobile LIKE ?)";
                    for ($i = 0; $i < 5; $i++) {
                        $pParams[] = $tLike;
                    }
                }
            }

            if (!empty($pClauses)) {
                $pSql = "SELECT candidate_name, ward_no, panchayat, block, district, district_slug, mobile, reservation_status, category 
                         FROM panch_21_26 
                         WHERE " . implode(' OR ', $pClauses) . " 
                         LIMIT 6";
                $stmtP = $pdo->prepare($pSql);
                $stmtP->execute($pParams);
                while ($p = $stmtP->fetch(PDO::FETCH_ASSOC)) {
                    $dSlug = $p['district_slug'] ?: slugify($p['district']);
                    $bSlug = slugify($p['block']);
                    $pSlug = slugify($p['panchayat']);

                    $results['panch'][] = [
                        'type' => 'panch',
                        'title' => '⚖️ Gram Panch: ' . $p['candidate_name'] . ' (Ward ' . $p['ward_no'] . ')',
                        'subtitle' => '🌾 ' . $p['panchayat'] . ' Gram Kutchery (Ward ' . $p['ward_no'] . ')',
                        'extra' => 'Block: ' . $p['block'] . ' | District: ' . $p['district'] . (!empty($p['mobile']) ? ' | 📞 ' . $p['mobile'] : '') . ' | Judicial Ward Panch',
                        'slug' => $pSlug,
                        'url' => getPanchayatUrl($dSlug, $bSlug, $pSlug) . '#tab-panch'
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Panch search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 10. Search Zila Parishad Members (`zila_parishad_members` table, 1,153 members)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'zila_parishad' && $isQueryEmptyOrIntentOnly && $extractedTerritoryNo === null) {
            // Return top Zila Parishad members
            $stmtDefZp = $pdo->query("SELECT candidate_name, territory_no, block, district, district_slug, mobile, category 
                                      FROM zila_parishad_members 
                                      ORDER BY id ASC LIMIT 8");
            while ($zm = $stmtDefZp->fetch(PDO::FETCH_ASSOC)) {
                $dSlug = $zm['district_slug'] ?: slugify($zm['district']);
                $terr = $zm['territory_no'] ?: '1';
                $results['zila_parishad'][] = [
                    'type' => 'zila_parishad',
                    'title' => '🏛️ Zila Parishad Member: ' . $zm['candidate_name'],
                    'subtitle' => '📍 Territory #' . $terr . ' &bull; ' . $zm['district'] . ' Zila Parishad',
                    'extra' => 'Block: ' . $zm['block'] . ' | District: ' . $zm['district'] . (!empty($zm['mobile']) ? ' | 📞 ' . $zm['mobile'] : '') . (!empty($zm['category']) ? ' | ' . $zm['category'] : ''),
                    'slug' => $dSlug . '-' . $terr,
                    'url' => SITE_URL . '/zila-parishad/' . $dSlug . '/' . $terr
                ];
            }
        } else {
            $zpClauses = [];
            $zpParams = [];

            if ($isMobileQuery) {
                $zpClauses[] = "mobile LIKE ?";
                $zpParams[] = "%$cleanMobileDigits%";
            } elseif ($extractedTerritoryNo !== null) {
                $zpClauses[] = "(territory_no = ? OR ward_no = ?)";
                $zpParams[] = $extractedTerritoryNo;
                $zpParams[] = $extractedTerritoryNo;
            } else {
                foreach (array_slice($allTerms, 0, 4) as $t) {
                    if (strlen($t) < 2) continue;
                    $tLike = "%$t%";
                    $zpClauses[] = "(candidate_name LIKE ? OR block LIKE ? OR district LIKE ? OR mobile LIKE ?)";
                    for ($i = 0; $i < 4; $i++) {
                        $zpParams[] = $tLike;
                    }
                }
            }

            if (!empty($zpClauses)) {
                $zpSql = "SELECT candidate_name, territory_no, block, district, district_slug, mobile, category, tenure 
                          FROM zila_parishad_members 
                          WHERE " . implode(' OR ', $zpClauses) . " 
                          LIMIT 6";
                $stmtZp = $pdo->prepare($zpSql);
                $stmtZp->execute($zpParams);
                while ($zm = $stmtZp->fetch(PDO::FETCH_ASSOC)) {
                    $dSlug = $zm['district_slug'] ?: slugify($zm['district']);
                    $terr = $zm['territory_no'] ?: '1';

                    $results['zila_parishad'][] = [
                        'type' => 'zila_parishad',
                        'title' => '🏛️ Zila Parishad Member: ' . $zm['candidate_name'],
                        'subtitle' => '📍 Territory #' . $terr . ' &bull; ' . $zm['district'] . ' Zila Parishad',
                        'extra' => 'Block: ' . $zm['block'] . ' | District: ' . $zm['district'] . (!empty($zm['mobile']) ? ' | 📞 ' . $zm['mobile'] : '') . (!empty($zm['category']) ? ' | ' . $zm['category'] : ''),
                        'slug' => $dSlug . '-' . $terr,
                        'url' => SITE_URL . '/zila-parishad/' . $dSlug . '/' . $terr
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Zila Parishad search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 11. Search Panchayat Samiti Members & Pramukhs (`panchayat_samiti_member_2021` & `panchayat_samiti_2016`)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'panchayat_samiti' && $isQueryEmptyOrIntentOnly && $extractedTerritoryNo === null) {
            // Return top Panchayat Samiti members & Pramukhs
            $stmtDefSm = $pdo->query("SELECT candidate_name, territory_no, panchayat, block, district, district_slug, mobile, reservation_status 
                                     FROM panchayat_samiti_member_2021 
                                     ORDER BY id ASC LIMIT 8");
            while ($sm = $stmtDefSm->fetch(PDO::FETCH_ASSOC)) {
                $dSlug = $sm['district_slug'] ?: slugify($sm['district']);
                $bSlug = slugify($sm['block']);
                $results['panchayat_samiti'][] = [
                    'type' => 'panchayat_samiti',
                    'title' => '🌾 Panchayat Samiti Member: ' . $sm['candidate_name'],
                    'subtitle' => '📍 Territory #' . ($sm['territory_no'] ?: '—') . ' &bull; ' . $sm['panchayat'] . ', ' . $sm['block'] . ' Block',
                    'extra' => 'Block: ' . $sm['block'] . ' | District: ' . $sm['district'] . (!empty($sm['mobile']) ? ' | 📞 ' . $sm['mobile'] : '') . (!empty($sm['reservation_status']) ? ' | ' . $sm['reservation_status'] : ''),
                    'slug' => $dSlug . '-' . $bSlug,
                    'url' => SITE_URL . '/panchayat-samiti-members?district=' . urlencode($dSlug) . '&block=' . urlencode($sm['block'])
                ];
            }
        } else {
            $psmClauses = [];
            $psmParams = [];

            if ($isMobileQuery) {
                $psmClauses[] = "mobile LIKE ?";
                $psmParams[] = "%$cleanMobileDigits%";
            } elseif ($extractedTerritoryNo !== null) {
                $psmClauses[] = "territory_no = ?";
                $psmParams[] = $extractedTerritoryNo;
            } else {
                foreach (array_slice($allTerms, 0, 4) as $t) {
                    if (strlen($t) < 2) continue;
                    $tLike = "%$t%";
                    $psmClauses[] = "(candidate_name LIKE ? OR panchayat LIKE ? OR block LIKE ? OR district LIKE ? OR mobile LIKE ?)";
                    for ($i = 0; $i < 5; $i++) {
                        $psmParams[] = $tLike;
                    }
                }
            }

            if (!empty($psmClauses)) {
                $psmSql = "SELECT candidate_name, territory_no, panchayat, block, district, district_slug, mobile, reservation_status, category 
                           FROM panchayat_samiti_member_2021 
                           WHERE " . implode(' OR ', $psmClauses) . " 
                           LIMIT 6";
                $stmtPsm = $pdo->prepare($psmSql);
                $stmtPsm->execute($psmParams);
                while ($sm = $stmtPsm->fetch(PDO::FETCH_ASSOC)) {
                    $dSlug = $sm['district_slug'] ?: slugify($sm['district']);
                    $bSlug = slugify($sm['block']);

                    $results['panchayat_samiti'][] = [
                        'type' => 'panchayat_samiti',
                        'title' => '🌾 Panchayat Samiti Member: ' . $sm['candidate_name'],
                        'subtitle' => '📍 Territory #' . ($sm['territory_no'] ?: '—') . ' &bull; ' . $sm['panchayat'] . ', ' . $sm['block'] . ' Block',
                        'extra' => 'Block: ' . $sm['block'] . ' | District: ' . $sm['district'] . (!empty($sm['mobile']) ? ' | 📞 ' . $sm['mobile'] : '') . (!empty($sm['reservation_status']) ? ' | ' . $sm['reservation_status'] : ''),
                        'slug' => $dSlug . '-' . $bSlug,
                        'url' => SITE_URL . '/panchayat-samiti-members?district=' . urlencode($dSlug) . '&block=' . urlencode($sm['block'])
                    ];
                }
            }

            // Also check Pramukhs in panchayat_samiti_2016
            if ($intent === 'panchayat_samiti' || $intent === 'general') {
                $pramukhClauses = [];
                $pramukhParams = [];
                foreach (array_slice($allTerms, 0, 3) as $t) {
                    if (strlen($t) < 2) continue;
                    $tLike = "%$t%";
                    $pramukhClauses[] = "(pramukh_name LIKE ? OR up_pramukh_name LIKE ? OR block LIKE ? OR district LIKE ?)";
                    for ($i = 0; $i < 4; $i++) {
                        $pramukhParams[] = $tLike;
                    }
                }
                if (!empty($pramukhClauses)) {
                    $prSql = "SELECT pramukh_name, up_pramukh_name, block, district, district_slug, mobile 
                              FROM panchayat_samiti_2016 
                              WHERE " . implode(' OR ', $pramukhClauses) . " 
                              LIMIT 4";
                    $stmtPr = $pdo->prepare($prSql);
                    $stmtPr->execute($pramukhParams);
                    while ($pr = $stmtPr->fetch(PDO::FETCH_ASSOC)) {
                        $dSlug = $pr['district_slug'] ?: slugify($pr['district']);
                        $bSlug = slugify($pr['block']);
                        $results['panchayat_samiti'][] = [
                            'type' => 'panchayat_samiti',
                            'title' => '🌾 Block Pramukh: ' . $pr['pramukh_name'],
                            'subtitle' => '🏢 ' . $pr['block'] . ' Block Panchayat Samiti',
                            'extra' => 'District: ' . $pr['district'] . (!empty($pr['up_pramukh_name']) ? ' | Up-Pramukh: ' . $pr['up_pramukh_name'] : '') . (!empty($pr['mobile']) ? ' | 📞 ' . $pr['mobile'] : ''),
                            'slug' => $dSlug . '-' . $bSlug,
                            'url' => getPanchayatSamitiUrl($dSlug, $bSlug)
                        ];
                    }
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Panchayat Samiti search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 12. Search CD Blocks (534 Sub-districts / CD Blocks)
// -------------------------------------------------------------
if ($pdo) {
    try {
        if ($intent === 'block' && $isQueryEmptyOrIntentOnly) {
            $stmtDefB = $pdo->query("SELECT sub_district, district_name, district_slug, sub_dist_code, population, households 
                                     FROM census_subdistricts 
                                     ORDER BY population DESC LIMIT 8");
            while ($b = $stmtDefB->fetch(PDO::FETCH_ASSOC)) {
                $bSlug = slugify($b['sub_district']);
                $dSlug = $b['district_slug'] ?: slugify($b['district_name']);
                $results['block'][] = [
                    'type' => 'block',
                    'title' => '📍 ' . $b['sub_district'] . ' CD Block',
                    'subtitle' => 'District: ' . $b['district_name'] . ' | Pop: ' . number_format((int)$b['population']),
                    'extra' => 'Households: ' . number_format((int)$b['households']) . ' | 3-Tier Panchayati Raj & Demographics',
                    'slug' => $bSlug,
                    'url' => getBlockUrl($dSlug, $bSlug)
                ];
            }
        } else {
            $bClauses = [];
            $bParams = [];
            foreach (array_slice($allTerms, 0, 4) as $t) {
                if (strlen($t) < 2) continue;
                $bClauses[] = "(sub_district LIKE ? OR district_name LIKE ?)";
                $bParams[] = "%$t%";
                $bParams[] = "%$t%";
            }
            if (!empty($bClauses)) {
                $stmtB = $pdo->prepare("SELECT sub_district, district_name, district_slug, sub_dist_code, population, households 
                                        FROM census_subdistricts 
                                        WHERE " . implode(' OR ', $bClauses) . " LIMIT 6");
                $stmtB->execute($bParams);
                while ($b = $stmtB->fetch(PDO::FETCH_ASSOC)) {
                    $bSlug = slugify($b['sub_district']);
                    $dSlug = $b['district_slug'] ?: slugify($b['district_name']);
                    $results['block'][] = [
                        'type' => 'block',
                        'title' => '📍 ' . $b['sub_district'] . ' CD Block',
                        'subtitle' => 'District: ' . $b['district_name'] . ' | Pop: ' . number_format((int)$b['population']),
                        'extra' => 'Households: ' . number_format((int)$b['households']) . ' | 3-Tier Panchayati Raj & Demographics',
                        'slug' => $bSlug,
                        'url' => getBlockUrl($dSlug, $bSlug)
                    ];
                }
            }
        }
    } catch (Throwable $e) {
        error_log("Block search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 13. Search Towns & Slums
// -------------------------------------------------------------
if ($pdo) {
    try {
        $tClauses = [];
        $tParams = [];
        foreach (array_slice($allTerms, 0, 3) as $t) {
            if (strlen($t) < 2) continue;
            $tClauses[] = "(town_name LIKE ? OR town_code LIKE ?)";
            $tParams[] = "%$t%";
            $tParams[] = "%$t%";
        }
        if (!empty($tClauses)) {
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
        }
    } catch (Throwable $e) {
        error_log("Town search error: " . $e->getMessage());
    }
}

// -------------------------------------------------------------
// 14. SMART ORDERING ACCORDING TO USER INTENT
// -------------------------------------------------------------
$final = [];

if ($intent === 'mlc') {
    $order = ['mlc', 'mla', 'mp', 'district', 'block', 'panchayat', 'mukhiya', 'village'];
} elseif ($intent === 'mla') {
    $order = ['mla', 'mp', 'mlc', 'district', 'block', 'panchayat', 'mukhiya', 'village'];
} elseif ($intent === 'mp') {
    $order = ['mp', 'mla', 'mlc', 'district', 'block', 'panchayat', 'mukhiya', 'village'];
} elseif ($intent === 'mukhiya') {
    $order = ['mukhiya', 'village', 'panchayat', 'sarpanch', 'ward', 'panch', 'zila_parishad', 'panchayat_samiti', 'block', 'district'];
} elseif ($intent === 'sarpanch') {
    $order = ['sarpanch', 'panch', 'village', 'panchayat', 'mukhiya', 'ward', 'zila_parishad', 'panchayat_samiti', 'block', 'district'];
} elseif ($intent === 'ward') {
    $order = ['ward', 'panch', 'mukhiya', 'sarpanch', 'panchayat', 'village', 'block', 'district'];
} elseif ($intent === 'panch') {
    $order = ['panch', 'ward', 'sarpanch', 'mukhiya', 'panchayat', 'village', 'block', 'district'];
} elseif ($intent === 'zila_parishad') {
    $order = ['zila_parishad', 'panchayat_samiti', 'mukhiya', 'district', 'block', 'panchayat', 'village'];
} elseif ($intent === 'panchayat_samiti') {
    $order = ['panchayat_samiti', 'zila_parishad', 'mukhiya', 'block', 'district', 'panchayat', 'village'];
} elseif ($intent === 'village') {
    $order = ['village', 'mukhiya', 'sarpanch', 'panchayat', 'ward', 'panch', 'block', 'district', 'mla', 'mp', 'mlc'];
} elseif ($intent === 'panchayat') {
    $order = ['panchayat', 'mukhiya', 'sarpanch', 'village', 'ward', 'panch', 'panchayat_samiti', 'block', 'district'];
} elseif ($intent === 'block') {
    $order = ['block', 'panchayat_samiti', 'district', 'panchayat', 'mukhiya', 'village', 'mla'];
} elseif ($intent === 'district') {
    $order = ['district', 'zila_parishad', 'block', 'mp', 'mla', 'mlc', 'panchayat', 'mukhiya'];
} else {
    // General search: Balanced view across all 12 tiers
    $order = [
        'district', 'mp', 'mla', 'mlc', 
        'mukhiya', 'sarpanch', 'village', 'panchayat', 
        'zila_parishad', 'panchayat_samiti', 
        'ward', 'panch', 'block', 'town'
    ];
}

// Assemble final ordered results and eliminate duplicates by unique URL + title
$seenKeys = [];
foreach ($order as $category) {
    if (!empty($results[$category])) {
        foreach ($results[$category] as $item) {
            $dedupKey = ($item['type'] ?? '') . '|' . ($item['title'] ?? '') . '|' . ($item['url'] ?? '');
            if (!isset($seenKeys[$dedupKey])) {
                $seenKeys[$dedupKey] = true;
                $final[] = $item;
            }
        }
    }
}

// Return up to 40 rich results for smooth scrollable dropdown
echo json_encode(array_slice($final, 0, 40));
