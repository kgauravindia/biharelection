<?php
/**
 * BiharElection.com - Gram Kutchery Panch Members (2021-2026) Import Utility
 * Imports Gram Kutchery Panch from `District_Files/Panch_*.xlsx` files
 * into the database table `panch_21_26`.
 */
require_once __DIR__ . '/config.php';

// Allow long-running execution for large dataset
set_time_limit(600);
ini_set('memory_limit', '1024M');

$pdo = Database::getConnection();
if (!$pdo) {
    die("Database connection failed!\n");
}

echo "Connected to database: " . DB_NAME . "\n";

// 1. Create table `panch_21_26` if not exists
$createTableSql = "
CREATE TABLE IF NOT EXISTS `panch_21_26` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sl_no` INT DEFAULT NULL,
  `post` VARCHAR(100) DEFAULT 'ग्राम कचहरी के पंच',
  `district` VARCHAR(100) NOT NULL,
  `district_hi` VARCHAR(100) DEFAULT NULL,
  `district_slug` VARCHAR(100) DEFAULT NULL,
  `block` VARCHAR(100) NOT NULL,
  `panchayat` VARCHAR(150) NOT NULL,
  `ward_no` VARCHAR(50) DEFAULT NULL,
  `territory_no` VARCHAR(50) DEFAULT NULL,
  `reservation_status` VARCHAR(100) DEFAULT NULL,
  `candidate_name` VARCHAR(150) NOT NULL,
  `father_husband_name` VARCHAR(150) DEFAULT NULL,
  `gender` VARCHAR(20) DEFAULT NULL,
  `age` INT DEFAULT NULL,
  `category` VARCHAR(100) DEFAULT NULL,
  `address` TEXT DEFAULT NULL,
  `mobile` VARCHAR(50) DEFAULT NULL,
  `photo_url` VARCHAR(255) DEFAULT NULL,
  `affidavit_url` VARCHAR(255) DEFAULT NULL,
  `tenure` VARCHAR(50) DEFAULT '2021-2026',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_p2126_district` (`district`),
  INDEX `idx_p2126_district_slug` (`district_slug`),
  INDEX `idx_p2126_block` (`block`),
  INDEX `idx_p2126_panchayat` (`panchayat`),
  INDEX `idx_p2126_ward_no` (`ward_no`),
  INDEX `idx_p2126_candidate` (`candidate_name`),
  INDEX `idx_p2126_territory` (`territory_no`),
  INDEX `idx_p2126_dist_block_pan` (`district_slug`, `block`, `panchayat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
";

$pdo->exec($createTableSql);
echo "Table `panch_21_26` verified/created successfully.\n";

// Clear previous data for clean idempotent import
$pdo->exec("TRUNCATE TABLE `panch_21_26`");
echo "Table `panch_21_26` truncated for clean re-import.\n";

// 2. Load district mapping dictionary
$stmtDist = $pdo->query("SELECT id, name, name_hi, slug FROM districts");
$dbDistricts = $stmtDist->fetchAll(PDO::FETCH_ASSOC);

$districtMap = [];
foreach ($dbDistricts as $d) {
    $cleanHi = preg_replace('/\s*\(.*?\)/', '', $d['name_hi']);
    $districtMap[trim($cleanHi)] = ['name' => $d['name'], 'slug' => $d['slug'], 'hi' => $d['name_hi']];
    $districtMap[trim($d['name_hi'])] = ['name' => $d['name'], 'slug' => $d['slug'], 'hi' => $d['name_hi']];
    $districtMap[trim($d['name'])] = ['name' => $d['name'], 'slug' => $d['slug'], 'hi' => $d['name_hi']];
    $districtMap[strtolower(trim($d['slug']))] = ['name' => $d['name'], 'slug' => $d['slug'], 'hi' => $d['name_hi']];
}

// Common aliases & specific file name spellings
$districtMap['पूर्णियां'] = ['name' => 'Purnia', 'slug' => 'purnia', 'hi' => 'पूर्णिया'];
$districtMap['नालन्दा'] = ['name' => 'Nalanda', 'slug' => 'nalanda', 'hi' => 'नालंदा (बिहारशरीफ)'];
$districtMap['पश्चिम चम्पारण'] = ['name' => 'West Champaran', 'slug' => 'west-champaran', 'hi' => 'पश्चिमी चंपारण (बेतिया)'];
$districtMap['पश्चिमी चम्पारण'] = ['name' => 'West Champaran', 'slug' => 'west-champaran', 'hi' => 'पश्चिमी चंपारण (बेतिया)'];
$districtMap['पूर्वी चम्पारण'] = ['name' => 'East Champaran', 'slug' => 'east-champaran', 'hi' => 'पूर्वी चंपारण (मोतिहारी)'];
$districtMap['कैमूर (भभुआ)'] = ['name' => 'Kaimur', 'slug' => 'kaimur', 'hi' => 'कैमूर (भभुआ)'];
$districtMap['कैमूर'] = ['name' => 'Kaimur', 'slug' => 'kaimur', 'hi' => 'कैमूर (भभुआ)'];
$districtMap['सीवान'] = ['name' => 'Siwan', 'slug' => 'siwan', 'hi' => 'सीवान'];
$districtMap['सिवान'] = ['name' => 'Siwan', 'slug' => 'siwan', 'hi' => 'सीवान'];
$districtMap['वैशाली_'] = ['name' => 'Vaishali', 'slug' => 'vaishali', 'hi' => 'वैशाली (हाजीपुर)'];
$districtMap['वैशाली'] = ['name' => 'Vaishali', 'slug' => 'vaishali', 'hi' => 'वैशाली (हाजीपुर)'];

$dir = __DIR__ . '/District_Files';
$files = glob($dir . '/Panch_*.xlsx');
echo "Found " . count($files) . " Excel files matching Panch_*.xlsx in District_Files.\n";

$totalInserted = 0;
$startTime = microtime(true);
$BATCH_SIZE = 500;
$batch = [];

function flushPanchBatch(PDO $pdo, array &$batch): int {
    if (empty($batch)) return 0;
    
    $cols = [
        '`sl_no`', '`post`', '`district`', '`district_hi`', '`district_slug`',
        '`block`', '`panchayat`', '`ward_no`', '`territory_no`', '`reservation_status`',
        '`candidate_name`', '`father_husband_name`', '`gender`', '`age`',
        '`category`', '`address`', '`mobile`', '`photo_url`', '`affidavit_url`', '`tenure`'
    ];
    
    $colCount = count($cols);
    $rowPlaceholder = '(' . implode(',', array_fill(0, $colCount, '?')) . ')';
    $placeholders = implode(',', array_fill(0, count($batch), $rowPlaceholder));
    
    $sql = "INSERT INTO `panch_21_26` (" . implode(',', $cols) . ") VALUES " . $placeholders;
    
    $flatValues = [];
    foreach ($batch as $row) {
        foreach ($row as $val) {
            $flatValues[] = $val;
        }
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($flatValues);
    $count = count($batch);
    $batch = [];
    return $count;
}

foreach ($files as $filePath) {
    $fileName = basename($filePath);
    
    $dNameFromFile = preg_replace('/^Panch_|\.xlsx$/u', '', $fileName);
    $dNameFromFileClean = rtrim($dNameFromFile, '_');
    $fileDistInfo = $districtMap[$dNameFromFile] ?? ($districtMap[$dNameFromFileClean] ?? null);
    
    $zip = new ZipArchive();
    if ($zip->open($filePath) !== TRUE) {
        echo "Error opening: $fileName\n";
        continue;
    }
    
    // Read shared strings if present
    $sharedStrings = [];
    if (($xmlIndex = $zip->locateName('xl/sharedStrings.xml')) !== false) {
        $xmlReader = new XMLReader();
        $xmlReader->xml($zip->getFromIndex($xmlIndex));
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName == 'si') {
                $node = new SimpleXMLElement($xmlReader->readOuterXml());
                if (isset($node->t)) {
                    $sharedStrings[] = (string)$node->t;
                } elseif (isset($node->r)) {
                    $t = '';
                    foreach ($node->r as $r) {
                        $t .= (string)$r->t;
                    }
                    $sharedStrings[] = $t;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }
        $xmlReader->close();
    }
    
    // Read sheet mappings from workbook
    $sheetMap = [];
    if (($wbIndex = $zip->locateName('xl/workbook.xml')) !== false) {
        $wbXml = simplexml_load_string($zip->getFromIndex($wbIndex));
        $rels = [];
        if (($wbRelsIndex = $zip->locateName('xl/_rels/workbook.xml.rels')) !== false) {
            $wbRelsXml = simplexml_load_string($zip->getFromIndex($wbRelsIndex));
            foreach ($wbRelsXml->Relationship as $rel) {
                $rels[(string)$rel['Id']] = (string)$rel['Target'];
            }
        }
        foreach ($wbXml->sheets->sheet as $s) {
            $rId = (string)$s->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
            $sName = (string)$s['name'];
            $target = $rels[$rId] ?? '';
            if ($target) {
                $target = ltrim($target, '/');
                if (!str_starts_with($target, 'xl/')) {
                    $target = 'xl/' . $target;
                }
                $sheetMap[$sName] = $target;
            }
        }
    }
    
    $fileInserted = 0;
    $pdo->beginTransaction();
    
    foreach ($sheetMap as $sheetName => $sheetXmlPath) {
        if (($sIdx = $zip->locateName($sheetXmlPath)) === false) continue;
        
        // Parse block and panchayat from sheet name "{block}_{panchayat}"
        $fallbackBlock = '';
        $fallbackPanchayat = '';
        if (strpos($sheetName, '_') !== false) {
            $parts = explode('_', $sheetName, 2);
            $fallbackBlock = trim($parts[0]);
            $fallbackPanchayat = trim($parts[1]);
        }
        
        $sheetXmlStr = $zip->getFromIndex($sIdx);
        $xmlReader = new XMLReader();
        $xmlReader->xml($sheetXmlStr);
        
        while ($xmlReader->read()) {
            if ($xmlReader->nodeType == XMLReader::ELEMENT && $xmlReader->localName == 'row') {
                $rNum = (int)$xmlReader->getAttribute('r');
                if ($rNum <= 1) continue; // Skip header
                
                $rowXml = new SimpleXMLElement($xmlReader->readOuterXml());
                $rowCells = [];
                foreach ($rowXml->c as $c) {
                    $r = (string)$c['r'];
                    $col = preg_replace('/[0-9]/', '', $r);
                    $val = (string)$c->v;
                    $type = (string)$c['t'];
                    if ($type === 's' && isset($sharedStrings[(int)$val])) {
                        $val = $sharedStrings[(int)$val];
                    } elseif (isset($c->is->t)) {
                        $val = (string)$c->is->t;
                    }
                    $rowCells[$col] = trim($val);
                }
                
                $candName = $rowCells['H'] ?? '';
                if (empty($candName)) continue;
                
                $slNo = !empty($rowCells['A']) && is_numeric($rowCells['A']) ? (int)$rowCells['A'] : null;
                $post = !empty($rowCells['B']) ? $rowCells['B'] : 'ग्राम कचहरी के पंच';
                $distRaw = !empty($rowCells['C']) ? $rowCells['C'] : $dNameFromFileClean;
                $blockRaw = !empty($rowCells['D']) ? $rowCells['D'] : $fallbackBlock;
                $panchayat = !empty($rowCells['E']) ? $rowCells['E'] : $fallbackPanchayat;
                $wardNo = !empty($rowCells['F']) ? $rowCells['F'] : null;
                $territoryNo = $wardNo;
                $reservation = $rowCells['G'] ?? null;
                $fatherHusband = $rowCells['I'] ?? null;
                
                // Standardize gender
                $genderRaw = $rowCells['J'] ?? '';
                $gender = null;
                if (str_contains($genderRaw, 'पुरूष') || str_contains($genderRaw, 'पुरुष') || stripos($genderRaw, 'male') !== false) {
                    $gender = 'Male';
                } elseif (str_contains($genderRaw, 'महिला') || stripos($genderRaw, 'female') !== false) {
                    $gender = 'Female';
                } else {
                    $gender = $genderRaw ?: null;
                }
                
                $age = !empty($rowCells['K']) && is_numeric($rowCells['K']) ? (int)$rowCells['K'] : null;
                $category = $rowCells['L'] ?? null;
                $address = $rowCells['M'] ?? null;
                $mobile = $rowCells['N'] ?? null;
                $photo = !empty($rowCells['O']) ? $rowCells['O'] : null;
                $affidavit = !empty($rowCells['P']) && $rowCells['P'] !== 'Affidavit' ? $rowCells['P'] : null;
                $tenure = '2021-2026';
                
                // Lookup district
                $distInfo = $districtMap[$distRaw] ?? null;
                if (!$distInfo) {
                    $cleanD = preg_replace('/\s*\(.*?\)/', '', $distRaw);
                    $distInfo = $districtMap[$cleanD] ?? $fileDistInfo;
                }
                
                $distEng = $distInfo['name'] ?? $distRaw;
                $distSlug = $distInfo['slug'] ?? strtolower(str_replace(' ', '-', $distEng));
                $distHi = $distInfo['hi'] ?? $distRaw;
                
                $batch[] = [
                    $slNo,
                    $post,
                    $distEng,
                    $distHi,
                    $distSlug,
                    $blockRaw,
                    $panchayat,
                    $wardNo,
                    $territoryNo,
                    $reservation,
                    $candName,
                    $fatherHusband,
                    $gender,
                    $age,
                    $category,
                    $address,
                    $mobile,
                    $photo,
                    $affidavit,
                    $tenure
                ];
                
                if (count($batch) >= $BATCH_SIZE) {
                    $inserted = flushPanchBatch($pdo, $batch);
                    $fileInserted += $inserted;
                    $totalInserted += $inserted;
                }
            }
        }
        $xmlReader->close();
    }
    
    // Flush any remaining records for this file
    if (!empty($batch)) {
        $inserted = flushPanchBatch($pdo, $batch);
        $fileInserted += $inserted;
        $totalInserted += $inserted;
    }
    
    $pdo->commit();
    $zip->close();
    echo "Imported: {$fileName} -> {$fileInserted} Panch members\n";
}

$elapsed = round(microtime(true) - $startTime, 2);
echo "\n============================================\n";
echo "SUCCESS! Total Panch members imported into `panch_21_26`: {$totalInserted} in {$elapsed}s\n";
