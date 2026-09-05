<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';
$import_report = null;

// Handle Sample CSV Downloads
if (isset($_GET['download']) && !empty($_GET['download'])) {
    $type = sanitize($_GET['download']);
    $filename = "sample_" . $type . "_" . date('Ymd') . ".csv";

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $out = fopen('php://output', 'w');
    // UTF-8 BOM for Excel compatibility
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    switch ($type) {
        case 'mukhiyas':
            fputcsv($out, ['Candidate Name', 'District', 'Block', 'Panchayat', 'Gender', 'Category', 'Mobile', 'Address', 'Tenure']);
            fputcsv($out, ['राम कुमार यादव', 'Patna', 'दानापुर', 'सगौना', 'Male', 'सामान्य वर्ग', '9876543210', 'Gram Saguna, Danapur', '2021-2026']);
            fputcsv($out, ['अनिता देवी', 'Patna', 'बिहटा', 'कनपा', 'Female', 'पिछड़ा वर्ग अनुसूची-II', '9876543211', 'Gram Kanpa, Bihta', '2021-2026']);
            fputcsv($out, ['मो. फिरोज आलम', 'Gaya', 'बोधगया', 'मोराटाल', 'Male', 'सामान्य वर्ग', '9876543212', 'Bodhgaya, Gaya', '2021-2026']);
            break;

        case 'sarpanchs':
            fputcsv($out, ['Candidate Name', 'District', 'Block', 'Panchayat', 'Gender', 'Category', 'Mobile', 'Address', 'Tenure']);
            fputcsv($out, ['सुरेश प्रसाद सिंह', 'Patna', 'पालीगंज', 'चन्दौस', 'Male', 'सामान्य वर्ग', '9876543220', 'Chandhos, Paliganj', '2021-2026']);
            fputcsv($out, ['मीना कुमारी', 'Muzaffarpur', 'कांटी', 'कांटी उत्तरी', 'Female', 'पिछड़ा वर्ग अनुसूची-I', '9876543221', 'Kanti, Muzaffarpur', '2021-2026']);
            fputcsv($out, ['राजेन्द्र पासवान', 'Darbhanga', 'बेनीपुर', 'अयाची नगर', 'Male', 'अनुसूचित जाति', '9876543222', 'Benipur, Darbhanga', '2021-2026']);
            break;

        case 'zp_members':
            fputcsv($out, ['Candidate Name', 'District', 'Block', 'Territory No', 'Gender', 'Category', 'Mobile', 'Address', 'Tenure']);
            fputcsv($out, ['राजेश कुमार वर्मा', 'Patna', 'दानापुर', '1', 'Male', 'सामान्य वर्ग', '9876543230', 'Territory 1, Danapur', '2021-2026']);
            fputcsv($out, ['सुनीता देवी', 'Patna', 'मनेर', '2', 'Female', 'पिछड़ा वर्ग अनुसूची-II', '9876543231', 'Territory 2, Maner', '2021-2026']);
            fputcsv($out, ['अजय कुमार', 'Nalanda', 'बिहारशरीफ', '1', 'Male', 'अनुसूचित जाति', '9876543232', 'Bihar Sharif, Nalanda', '2021-2026']);
            break;

        case 'zp_officials':
            fputcsv($out, ['Official Name', 'Post', 'District', 'Gender', 'Category', 'Mobile', 'Address', 'Tenure']);
            fputcsv($out, ['कुमारी स्तुति', 'अध्यक्ष (Chairperson)', 'Patna', 'Female', 'सामान्य वर्ग', '9876543240', 'Patna Zila Parishad Board', '2021-2026']);
            fputcsv($out, ['अंजनी सिंह', 'उपाध्यक्ष (Vice-Chairperson)', 'Patna', 'Male', 'सामान्य वर्ग', '9876543241', 'Patna Zila Parishad Board', '2021-2026']);
            fputcsv($out, ['पिंकी कुमारी', 'अध्यक्ष (Chairperson)', 'Gaya', 'Female', 'पिछड़ा वर्ग अनुसूची-I', '9876543242', 'Gaya Zila Parishad Board', '2021-2026']);
            break;

        case 'panchayat_samiti':
            fputcsv($out, ['District', 'Block', 'Pramukh Name', 'Up Pramukh Name', 'Gender', 'Category', 'Mobile', 'Address', 'Tenure']);
            fputcsv($out, ['Patna', 'दानापुर', 'सुनीता देवी', 'राम प्रवेश कुमार', 'Female', 'सामान्य वर्ग', '9876543250', 'Block Complex Danapur', '2021-2026']);
            fputcsv($out, ['Patna', 'बिहटा', 'राकेश कुमार', 'प्रियंका सिंह', 'Male', 'पिछड़ा वर्ग अनुसूची-II', '9876543251', 'Block Complex Bihta', '2021-2026']);
            fputcsv($out, ['Gaya', 'बोधगया', 'रंजीत कुमार', 'ममता देवी', 'Male', 'अनुसूचित जाति', '9876543252', 'Bodhgaya Block Samiti', '2021-2026']);
            break;

        default:
            fputcsv($out, ['Invalid Template']);
            break;
    }

    fclose($out);
    exit;
}

// Global Hindi to English District Dictionary
$hindi_district_map = [
    'सारण' => 'Saran', 'शेखपुरा' => 'Sheikhpura', 'पटना' => 'Patna', 'गया' => 'Gaya',
    'मुजफ्फरपुर' => 'Muzaffarpur', 'भागलपुर' => 'Bhagalpur', 'दरभंगा' => 'Darbhanga',
    'वैशाली' => 'Vaishali', 'नालंदा' => 'Nalanda', 'भोजपुर' => 'Bhojpur', 'रोहतास' => 'Rohtas',
    'बक्सर' => 'Buxar', 'कैमूर' => 'Kaimur', 'जहानाबाद' => 'Jehanabad', 'अरवल' => 'Arwal',
    'नवादा' => 'Nawada', 'औरंगाबाद' => 'Aurangabad', 'सीवान' => 'Siwan', 'गोपालगंज' => 'Gopalganj',
    'पूर्वी चंपारण' => 'East Champaran', 'पश्चिम चंपारण' => 'West Champaran', 'सीतामढ़ी' => 'Sitamarhi',
    'शिवहर' => 'Sheohar', 'मधुबनी' => 'Madhubani', 'समस्तीपुर' => 'Samastipur', 'बेगूसराय' => 'Begusarai',
    'खगड़िया' => 'Khagaria', 'मुंगेर' => 'Munger', 'लखीसराय' => 'Lakhisarai', 'जमुई' => 'Jamui',
    'बांका' => 'Banka', 'पूर्णिया' => 'Purnia', 'कटिहार' => 'Katihar', 'अररिया' => 'Araria',
    'किशनगंज' => 'Kishanganj', 'सहरसा' => 'Saharsa', 'सुपौल' => 'Supaul', 'मधेपुरा' => 'Madhepura'
];

function normalizeDistrictName($dist, $map) {
    $t = trim((string)$dist);
    if (isset($map[$t])) return $map[$t];
    return $t;
}

function normalizeGenderVal($g) {
    $t = trim((string)$g);
    if ($t === 'महिला' || strcasecmp($t, 'female') === 0 || strcasecmp($t, 'f') === 0) return 'Female';
    if ($t === 'अन्य' || strcasecmp($t, 'other') === 0) return 'Other';
    return 'Male';
}

// Helper: normalize header key
function normalizeKey($str) {
    $s = mb_strtolower(trim((string)$str), 'UTF-8');
    $s = str_replace([' ', '-', '_', '.', '०', '0', '/', '\\', '(', ')'], '', $s);
    return $s;
}

// Native Excel (.xlsx) & CSV Parser
function parseSpreadsheetRows($filePath, $fileExt) {
    if ($fileExt === 'xlsx') {
        $zip = new ZipArchive();
        if ($zip->open($filePath) !== true) return false;

        $sharedStrings = [];
        if (($index = $zip->locateName('xl/sharedStrings.xml')) !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->si as $si) {
                if (isset($si->t)) {
                    $sharedStrings[] = (string)$si->t;
                } elseif (isset($si->r)) {
                    $text = '';
                    foreach ($si->r as $r) $text .= (string)$r->t;
                    $sharedStrings[] = $text;
                } else {
                    $sharedStrings[] = '';
                }
            }
        }

        $allRows = [];
        $sheetIdx = 1;
        while (($index = $zip->locateName("xl/worksheets/sheet{$sheetIdx}.xml")) !== false) {
            $xml = simplexml_load_string($zip->getFromIndex($index));
            foreach ($xml->sheetData->row as $row) {
                $rowData = [];
                foreach ($row->c as $cell) {
                    $cellType = (string)$cell['t'];
                    $val = (string)$cell->v;
                    if ($cellType === 's') {
                        $val = $sharedStrings[(int)$val] ?? $val;
                    }
                    $rowData[] = $val;
                }
                if (!empty($rowData)) $allRows[] = $rowData;
            }
            $sheetIdx++;
            break; // Parse first sheet
        }
        $zip->close();
        return $allRows;
    } else {
        $handle = fopen($filePath, 'r');
        if (!$handle) return false;
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }
        $allRows = [];
        while (($row = fgetcsv($handle, 8192, ",")) !== false) {
            $allRows[] = $row;
        }
        fclose($handle);
        return $allRows;
    }
}

// Handle Upload POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'bulk_upload' && $conn) {
    $entity = sanitize($_POST['entity'] ?? 'mukhiyas');
    $mode = sanitize($_POST['mode'] ?? 'upsert'); // 'upsert' or 'insert'

    if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
        $error = "Please choose a valid CSV or Excel (.xlsx) file to upload.";
    } else {
        $fileTmp = $_FILES['csv_file']['tmp_name'];
        $fileName = $_FILES['csv_file']['name'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if (!in_array($fileExt, ['csv', 'txt', 'xlsx'])) {
            $error = "Only .csv and .xlsx files are supported. Please upload a standard CSV or Excel file.";
        } else {
            $rawRows = parseSpreadsheetRows($fileTmp, $fileExt);
            if ($rawRows === false || empty($rawRows)) {
                $error = "The uploaded file appears to be empty or could not be read.";
            } else {
                $headerRow = array_shift($rawRows);
                $headerMap = [];
                foreach ($headerRow as $idx => $colName) {
                    $k = normalizeKey($colName);
                    $headerMap[$k] = $idx;
                }

                $totalRows = 0;
                $insertedCount = 0;
                $updatedCount = 0;
                $skippedCount = 0;
                $errorsList = [];

                $rowNum = 1;
                foreach ($rawRows as $row) {
                    $rowNum++;
                    if (count(array_filter($row, fn($v) => trim((string)$v) !== '')) === 0) {
                        continue;
                    }
                    $totalRows++;

                    $getVal = function(...$keys) use ($row, $headerMap) {
                        foreach ($keys as $k) {
                            $norm = normalizeKey($k);
                            if (isset($headerMap[$norm]) && isset($row[$headerMap[$norm]])) {
                                $val = trim((string)$row[$headerMap[$norm]]);
                                if ($val !== '') return $val;
                            }
                        }
                        return '';
                    };

                        if ($entity === 'mukhiyas') {
                            $name = $getVal('candidatename', 'name', 'mukhiyaname', 'candidate', 'अभ्यर्थीकानाम', 'मुखियाकानाम', 'नाम');
                            $rawDist = $getVal('district', 'districtname', 'zila', 'जिला', 'ज़िला');
                            $district = normalizeDistrictName($rawDist, $hindi_district_map);
                            $block = $getVal('block', 'blockname', 'prakhand', 'प्रखंड', 'ब्लॉक');
                            $panchayat = $getVal('panchayat', 'panchayatname', 'grampanchayat', 'पंचायत', 'ग्रामपंचायत');
                            $gender = normalizeGenderVal($getVal('gender', 'sex', 'लिंग'));
                            $category = $getVal('category', 'reservationcategory', 'caste', 'कोटि', 'आरक्षणकोटि', 'आरक्षण') ?: 'सामान्य वर्ग';
                            $mobile = $getVal('mobile', 'mobilenumber', 'phone', 'contact', 'मोबाइल', 'मोबाइलनं०', 'मोबाइलनं0');
                            $address = $getVal('address', 'village', 'location', 'पता', 'ग्राम');
                            $tenure = $getVal('tenure', 'term', 'year') ?: '2021-2026';
                            $dSlug = slugify($district);

                            if (empty($name) || empty($district) || empty($panchayat)) {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Missing required candidate name, district, or panchayat.";
                                continue;
                            }

                            if ($mode === 'upsert') {
                                $stmtChk = $conn->prepare("SELECT id FROM `mukhiyas` WHERE (district = ? OR district_slug = ?) AND block = ? AND panchayat = ? LIMIT 1");
                                $stmtChk->bind_param("ssss", $district, $dSlug, $block, $panchayat);
                                $stmtChk->execute();
                                $resChk = $stmtChk->get_result();
                                if ($resChk && $exRow = $resChk->fetch_assoc()) {
                                    $exId = (int)$exRow['id'];
                                    $stmtUpd = $conn->prepare("UPDATE `mukhiyas` SET `candidate_name` = ?, `gender` = ?, `category` = ?, `mobile` = IF(? != '', ?, `mobile`), `address` = IF(? != '', ?, `address`), `tenure` = ? WHERE `id` = ?");
                                    $stmtUpd->bind_param("sssssssi", $name, $gender, $category, $mobile, $mobile, $address, $address, $tenure, $exId);
                                    if ($stmtUpd->execute()) {
                                        $updatedCount++;
                                    } else {
                                        $skippedCount++;
                                        $errorsList[] = "Row #$rowNum: Database update error: " . $conn->error;
                                    }
                                    continue;
                                }
                            }

                            $stmtIns = $conn->prepare("INSERT INTO `mukhiyas` (`candidate_name`, `post`, `district`, `district_slug`, `block`, `panchayat`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, 'मुखिया', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmtIns->bind_param("ssssssssss", $name, $district, $dSlug, $block, $panchayat, $gender, $category, $mobile, $address, $tenure);
                            if ($stmtIns->execute()) {
                                $insertedCount++;
                            } else {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Database insert error: " . $conn->error;
                            }

                        } elseif ($entity === 'sarpanchs') {
                            $name = $getVal('candidatename', 'name', 'sarpanchname', 'candidate', 'अभ्यर्थीकानाम', 'सरपंचकानाम', 'नाम');
                            $rawDist = $getVal('district', 'districtname', 'zila', 'जिला', 'ज़िला');
                            $district = normalizeDistrictName($rawDist, $hindi_district_map);
                            $block = $getVal('block', 'blockname', 'prakhand', 'प्रखंड', 'ब्लॉक');
                            $panchayat = $getVal('panchayat', 'panchayatname', 'grampanchayat', 'पंचायत', 'ग्रामपंचायत');
                            $gender = normalizeGenderVal($getVal('gender', 'sex', 'लिंग'));
                            $category = $getVal('category', 'reservationcategory', 'caste', 'कोटि', 'आरक्षणकोटि', 'आरक्षण') ?: 'सामान्य वर्ग';
                            $mobile = $getVal('mobile', 'mobilenumber', 'phone', 'contact', 'मोबाइल', 'मोबाइलनं०', 'मोबाइलनं0');
                            $address = $getVal('address', 'village', 'location', 'पता', 'ग्राम');
                            $tenure = $getVal('tenure', 'term', 'year') ?: '2021-2026';
                            $dSlug = slugify($district);

                            if (empty($name) || empty($district) || empty($panchayat)) {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Missing required candidate name, district, or panchayat.";
                                continue;
                            }

                            if ($mode === 'upsert') {
                                $stmtChk = $conn->prepare("SELECT id FROM `sarpanchs` WHERE (district = ? OR district_slug = ?) AND block = ? AND panchayat = ? LIMIT 1");
                                $stmtChk->bind_param("ssss", $district, $dSlug, $block, $panchayat);
                                $stmtChk->execute();
                                $resChk = $stmtChk->get_result();
                                if ($resChk && $exRow = $resChk->fetch_assoc()) {
                                    $exId = (int)$exRow['id'];
                                    $stmtUpd = $conn->prepare("UPDATE `sarpanchs` SET `candidate_name` = ?, `gender` = ?, `category` = ?, `mobile` = IF(? != '', ?, `mobile`), `address` = IF(? != '', ?, `address`), `tenure` = ? WHERE `id` = ?");
                                    $stmtUpd->bind_param("sssssssi", $name, $gender, $category, $mobile, $mobile, $address, $address, $tenure, $exId);
                                    if ($stmtUpd->execute()) {
                                        $updatedCount++;
                                    } else {
                                        $skippedCount++;
                                        $errorsList[] = "Row #$rowNum: Database update error: " . $conn->error;
                                    }
                                    continue;
                                }
                            }

                            $stmtIns = $conn->prepare("INSERT INTO `sarpanchs` (`candidate_name`, `post`, `district`, `district_slug`, `block`, `panchayat`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, 'सरपंच', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmtIns->bind_param("ssssssssss", $name, $district, $dSlug, $block, $panchayat, $gender, $category, $mobile, $address, $tenure);
                            if ($stmtIns->execute()) {
                                $insertedCount++;
                            } else {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Database insert error: " . $conn->error;
                            }

                        } elseif ($entity === 'zp_members') {
                            $name = $getVal('candidatename', 'name', 'membername', 'candidate', 'अभ्यर्थीकानाम', 'सदस्यकानाम', 'नाम');
                            $rawDist = $getVal('district', 'districtname', 'zila', 'जिला', 'ज़िला');
                            $district = normalizeDistrictName($rawDist, $hindi_district_map);
                            $block = $getVal('block', 'blockname', 'prakhand', 'प्रखंड', 'ब्लॉक');
                            $territory = $getVal('territoryno', 'territory', 'wardno', 'ward', 'seatno', 'प्रा०नि०क्षे०सं०', 'प्रा0नि0क्षे0सं0', 'प्रा०नि०क्षे०सं', 'क्षेत्रसंख्या', 'प्रादेशिकनिर्वाचनक्षेत्रसंख्या');
                            $gender = normalizeGenderVal($getVal('gender', 'sex', 'लिंग'));
                            $category = $getVal('category', 'reservationcategory', 'caste', 'कोटि', 'आरक्षणकोटि', 'आरक्षण', 'reservationstatus') ?: 'सामान्य वर्ग';
                            $mobile = $getVal('mobile', 'mobilenumber', 'phone', 'contact', 'मोबाइल', 'मोबाइलनं०', 'मोबाइलनं0');
                            $address = $getVal('address', 'village', 'location', 'पता', 'ग्राम');
                            $tenure = $getVal('tenure', 'term', 'year') ?: '2021-2026';
                            $dSlug = slugify($district);

                            if (empty($name) || empty($district) || empty($territory)) {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Missing required candidate name, district, or territory no.";
                                continue;
                            }

                            if ($mode === 'upsert') {
                                $stmtChk = $conn->prepare("SELECT id FROM `zila_parishad_members` WHERE (district = ? OR district_slug = ?) AND territory_no = ? LIMIT 1");
                                $stmtChk->bind_param("sss", $district, $dSlug, $territory);
                                $stmtChk->execute();
                                $resChk = $stmtChk->get_result();
                                if ($resChk && $exRow = $resChk->fetch_assoc()) {
                                    $exId = (int)$exRow['id'];
                                    $stmtUpd = $conn->prepare("UPDATE `zila_parishad_members` SET `candidate_name` = ?, `district` = ?, `district_slug` = ?, `block` = IF(? != '', ?, `block`), `gender` = ?, `category` = ?, `mobile` = IF(? != '', ?, `mobile`), `address` = IF(? != '', ?, `address`), `tenure` = ? WHERE `id` = ?");
                                    $stmtUpd->bind_param("ssssssssssi", $name, $district, $dSlug, $block, $block, $gender, $category, $mobile, $mobile, $address, $address, $tenure, $exId);
                                    if ($stmtUpd->execute()) {
                                        $updatedCount++;
                                    } else {
                                        $skippedCount++;
                                        $errorsList[] = "Row #$rowNum: Database update error: " . $conn->error;
                                    }
                                    continue;
                                }
                            }

                            $stmtIns = $conn->prepare("INSERT INTO `zila_parishad_members` (`candidate_name`, `district`, `district_slug`, `block`, `territory_no`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmtIns->bind_param("ssssssssss", $name, $district, $dSlug, $block, $territory, $gender, $category, $mobile, $address, $tenure);
                            if ($stmtIns->execute()) {
                                $insertedCount++;
                            } else {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Database insert error: " . $conn->error;
                            }

                        } elseif ($entity === 'zp_officials') {
                            $name = $getVal('officialname', 'candidatename', 'name', 'candidate', 'अभ्यर्थीकानाम', 'नाम', 'पदाधिकारीकानाम');
                            $post = $getVal('post', 'designation', 'position', 'पद') ?: 'अध्यक्ष (Chairperson)';
                            $rawDist = $getVal('district', 'districtname', 'zila', 'जिला', 'ज़िला');
                            $district = normalizeDistrictName($rawDist, $hindi_district_map);
                            $gender = normalizeGenderVal($getVal('gender', 'sex', 'लिंग'));
                            $category = $getVal('category', 'reservationcategory', 'caste', 'कोटि', 'आरक्षणकोटि', 'आरक्षण') ?: 'सामान्य वर्ग';
                            $mobile = $getVal('mobile', 'mobilenumber', 'phone', 'contact', 'मोबाइल', 'मोबाइलनं०', 'मोबाइलनं0');
                            $address = $getVal('address', 'village', 'location', 'पता', 'ग्राम');
                            $tenure = $getVal('tenure', 'term', 'year') ?: '2021-2026';
                            $dSlug = slugify($district);

                            if (empty($name) || empty($district) || empty($post)) {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Missing required official name, district, or post.";
                                continue;
                            }

                            if ($mode === 'upsert') {
                                $stmtChk = $conn->prepare("SELECT id FROM `zila_parishad_officials` WHERE (district = ? OR district_slug = ?) AND post = ? LIMIT 1");
                                $stmtChk->bind_param("sss", $district, $dSlug, $post);
                                $stmtChk->execute();
                                $resChk = $stmtChk->get_result();
                                if ($resChk && $exRow = $resChk->fetch_assoc()) {
                                    $exId = (int)$exRow['id'];
                                    $stmtUpd = $conn->prepare("UPDATE `zila_parishad_officials` SET `candidate_name` = ?, `district` = ?, `district_slug` = ?, `gender` = ?, `category` = ?, `mobile` = IF(? != '', ?, `mobile`), `address` = IF(? != '', ?, `address`), `tenure` = ? WHERE `id` = ?");
                                    $stmtUpd->bind_param("sssssssssi", $name, $district, $dSlug, $gender, $category, $mobile, $mobile, $address, $address, $tenure, $exId);
                                    if ($stmtUpd->execute()) {
                                        $updatedCount++;
                                    } else {
                                        $skippedCount++;
                                        $errorsList[] = "Row #$rowNum: Database update error: " . $conn->error;
                                    }
                                    continue;
                                }
                            }

                            $stmtIns = $conn->prepare("INSERT INTO `zila_parishad_officials` (`candidate_name`, `post`, `district`, `district_slug`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmtIns->bind_param("sssssssss", $name, $post, $district, $dSlug, $gender, $category, $mobile, $address, $tenure);
                            if ($stmtIns->execute()) {
                                $insertedCount++;
                            } else {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Database insert error: " . $conn->error;
                            }

                        } elseif ($entity === 'panchayat_samiti') {
                            $rawDist = $getVal('district', 'districtname', 'zila', 'जिला', 'ज़िला');
                            $district = normalizeDistrictName($rawDist, $hindi_district_map);
                            $block = $getVal('block', 'blockname', 'prakhand', 'प्रखंड', 'ब्लॉक');
                            $pramukh = $getVal('pramukhname', 'pramukh', 'prakhandpramukh', 'प्रमुखकानाम', 'प्रमुख');
                            $upPramukh = $getVal('uppramukhname', 'uppramukh', 'vp', 'उपप्रमुखकानाम', 'उपप्रमुख');
                            $gender = normalizeGenderVal($getVal('gender', 'sex', 'लिंग'));
                            $category = $getVal('category', 'reservationcategory', 'caste', 'कोटि', 'आरक्षणकोटि', 'आरक्षण') ?: 'सामान्य वर्ग';
                            $mobile = $getVal('mobile', 'mobilenumber', 'phone', 'contact', 'मोबाइल', 'मोबाइलनं०', 'मोबाइलनं0');
                            $address = $getVal('address', 'village', 'location', 'पता', 'ग्राम');
                            $tenure = $getVal('tenure', 'term', 'year') ?: '2021-2026';
                            $dSlug = slugify($district);

                            if (empty($district) || empty($block)) {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Missing required district or block name.";
                                continue;
                            }

                            if ($mode === 'upsert') {
                                $stmtChk = $conn->prepare("SELECT id FROM `panchayat_samiti` WHERE (district = ? OR district_slug = ?) AND block = ? LIMIT 1");
                                $stmtChk->bind_param("sss", $district, $dSlug, $block);
                                $stmtChk->execute();
                                $resChk = $stmtChk->get_result();
                                if ($resChk && $exRow = $resChk->fetch_assoc()) {
                                    $exId = (int)$exRow['id'];
                                    $stmtUpd = $conn->prepare("UPDATE `panchayat_samiti` SET `district` = ?, `district_slug` = ?, `pramukh_name` = IF(? != '', ?, `pramukh_name`), `up_pramukh_name` = IF(? != '', ?, `up_pramukh_name`), `gender` = ?, `category` = ?, `mobile` = IF(? != '', ?, `mobile`), `address` = IF(? != '', ?, `address`), `tenure` = ? WHERE `id` = ?");
                                    $stmtUpd->bind_param("sssssssssssi", $district, $dSlug, $pramukh, $pramukh, $upPramukh, $upPramukh, $gender, $category, $mobile, $mobile, $address, $address, $tenure, $exId);
                                    if ($stmtUpd->execute()) {
                                        $updatedCount++;
                                    } else {
                                        $skippedCount++;
                                        $errorsList[] = "Row #$rowNum: Database update error: " . $conn->error;
                                    }
                                    continue;
                                }
                            }

                            $stmtIns = $conn->prepare("INSERT INTO `panchayat_samiti` (`district`, `district_slug`, `block`, `pramukh_name`, `up_pramukh_name`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                            $stmtIns->bind_param("ssssssssss", $district, $dSlug, $block, $pramukh, $upPramukh, $gender, $category, $mobile, $address, $tenure);
                            if ($stmtIns->execute()) {
                                $insertedCount++;
                            } else {
                                $skippedCount++;
                                $errorsList[] = "Row #$rowNum: Database insert error: " . $conn->error;
                            }
                        }
                    }

                    $import_report = [
                        'entity' => $entity,
                        'total' => $totalRows,
                        'inserted' => $insertedCount,
                        'updated' => $updatedCount,
                        'skipped' => $skippedCount,
                        'errors' => $errorsList
                    ];

                    if ($insertedCount > 0 || $updatedCount > 0) {
                        $message = "Batch processing complete: <strong>$insertedCount</strong> inserted, <strong>$updatedCount</strong> updated, <strong>$skippedCount</strong> skipped.";
                    } else {
                        $error = "No records were inserted or updated. Please inspect the error report below.";
                    }
                }
            }
        }
    }

// Current database counts
$counts = [
    'mukhiyas' => 0,
    'sarpanchs' => 0,
    'zp_members' => 0,
    'zp_officials' => 0,
    'panchayat_samiti' => 0
];

if ($conn) {
    $r1 = $conn->query("SELECT COUNT(*) as c FROM `mukhiyas`");
    if ($r1) $counts['mukhiyas'] = (int)$r1->fetch_assoc()['c'];

    $r2 = $conn->query("SELECT COUNT(*) as c FROM `sarpanchs`");
    if ($r2) $counts['sarpanchs'] = (int)$r2->fetch_assoc()['c'];

    $r3 = $conn->query("SELECT COUNT(*) as c FROM `zila_parishad_members`");
    if ($r3) $counts['zp_members'] = (int)$r3->fetch_assoc()['c'];

    $r4 = $conn->query("SELECT COUNT(*) as c FROM `zila_parishad_officials`");
    if ($r4) $counts['zp_officials'] = (int)$r4->fetch_assoc()['c'];

    $r5 = $conn->query("SELECT COUNT(*) as c FROM `panchayat_samiti`");
    if ($r5) $counts['panchayat_samiti'] = (int)$r5->fetch_assoc()['c'];
}

$selected_tab = isset($_GET['type']) ? sanitize($_GET['type']) : 'mukhiyas';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulk Data Upload Hub — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .upload-dropzone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            background: #f8fafc;
            transition: all 0.2s ease-in-out;
            cursor: pointer;
        }
        .upload-dropzone:hover, .upload-dropzone.dragover {
            border-color: #dc2626;
            background: #fff5f5;
        }
        .template-card {
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            padding: 1rem;
            transition: all 0.2s;
        }
        .template-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: #cbd5e1;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <!-- Header Row -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Bulk Data Upload & Import Hub</h1>
                <p class="text-muted mb-0">Fast batch CSV import with automatic validation, upsert/update mode, and sample templates.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="mukhiyas.php" class="btn btn-outline-dark fw-semibold rounded-3 shadow-sm bg-white">
                    <i class="fas fa-arrow-left me-1"></i> Return to Directory
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2 fs-5 align-middle"></i> <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2 fs-5 align-middle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Import Report Card if Generated -->
        <?php if (!empty($import_report)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
                <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0"><i class="fas fa-clipboard-check me-2 text-success"></i> Import Execution Summary Report</h6>
                    <span class="badge bg-secondary rounded-pill font-monospace"><?php echo strtoupper($import_report['entity']); ?></span>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 text-center mb-3">
                        <div class="col-sm-3">
                            <div class="p-3 bg-light rounded-3 border">
                                <small class="text-muted text-uppercase fw-bold">Total Rows Processed</small>
                                <h3 class="fw-bold mb-0 mt-1"><?php echo number_format($import_report['total']); ?></h3>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="p-3 bg-success bg-opacity-10 rounded-3 border border-success border-opacity-25">
                                <small class="text-success text-uppercase fw-bold">New Records Inserted</small>
                                <h3 class="fw-bold mb-0 mt-1 text-success"><?php echo number_format($import_report['inserted']); ?></h3>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="p-3 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25">
                                <small class="text-primary text-uppercase fw-bold">Existing Updated</small>
                                <h3 class="fw-bold mb-0 mt-1 text-primary"><?php echo number_format($import_report['updated']); ?></h3>
                            </div>
                        </div>
                        <div class="col-sm-3">
                            <div class="p-3 bg-danger bg-opacity-10 rounded-3 border border-danger border-opacity-25">
                                <small class="text-danger text-uppercase fw-bold">Skipped / Failed</small>
                                <h3 class="fw-bold mb-0 mt-1 text-danger"><?php echo number_format($import_report['skipped']); ?></h3>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($import_report['errors'])): ?>
                        <div class="mt-3">
                            <h6 class="fw-bold text-danger"><i class="fas fa-triangle-exclamation me-1"></i> Row Errors & Skip Log (First <?php echo min(25, count($import_report['errors'])); ?> entries):</h6>
                            <div class="p-3 bg-light rounded-3 border font-monospace small" style="max-height: 200px; overflow-y: auto;">
                                <?php foreach (array_slice($import_report['errors'], 0, 25) as $err): ?>
                                    <div class="text-danger mb-1">• <?php echo htmlspecialchars($err); ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Step 1: Download Sample Templates -->
        <div class="section-card mb-4">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-file-csv me-2 text-success"></i> 1. Download Pre-Formatted CSV Sample Templates
                </h6>
                <span class="badge bg-success-subtle text-success small">UTF-8 Excel Ready</span>
            </div>
            <div class="section-card-body p-3">
                <p class="small text-muted mb-3">Download the ready-to-use CSV template for your target entity, fill in your records with Hindi or English names, and upload below.</p>
                
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <div class="template-card d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Mukhiya Template</h6>
                                <small class="text-muted">Gram Panchayat Mukhiyas</small>
                            </div>
                            <a href="bulk-upload.php?download=mukhiyas" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-bold">
                                <i class="fas fa-download me-1"></i> CSV
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="template-card d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Sarpanch Template</h6>
                                <small class="text-muted">Gram Kutchery Sarpanchs</small>
                            </div>
                            <a href="bulk-upload.php?download=sarpanchs" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-bold text-dark">
                                <i class="fas fa-download me-1"></i> CSV
                            </a>
                        </div>
                    </div>

                    <div class="col-md-4 col-sm-6">
                        <div class="template-card d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Zila Parishad Members</h6>
                                <small class="text-muted">Territory Members Roster</small>
                            </div>
                            <a href="bulk-upload.php?download=zp_members" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold">
                                <i class="fas fa-download me-1"></i> CSV
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-6">
                        <div class="template-card d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Zila Parishad Officials</h6>
                                <small class="text-muted">Adhyaksh & Upadhyaksh Roster</small>
                            </div>
                            <a href="bulk-upload.php?download=zp_officials" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-bold">
                                <i class="fas fa-download me-1"></i> CSV
                            </a>
                        </div>
                    </div>

                    <div class="col-md-6 col-sm-12">
                        <div class="template-card d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">Panchayat Samiti Template</h6>
                                <small class="text-muted">Block Pramukh & Up-Pramukh Roster</small>
                            </div>
                            <a href="bulk-upload.php?download=panchayat_samiti" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-bold">
                                <i class="fas fa-download me-1"></i> CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Step 2: Upload CSV Form -->
        <div class="section-card mb-4">
            <div class="section-card-header">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-cloud-arrow-up me-2 text-danger"></i> 2. Upload CSV File & Import Records
                </h6>
            </div>
            <div class="section-card-body p-4">
                <form method="POST" action="bulk-upload.php" enctype="multipart/form-data" id="bulkUploadForm">
                    <input type="hidden" name="action" value="bulk_upload">

                    <div class="row g-4 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Select Target Entity / Department <span class="text-danger">*</span></label>
                            <select name="entity" id="entitySelect" class="form-select form-select-lg" required>
                                <option value="mukhiyas" <?php echo ($selected_tab === 'mukhiyas') ? 'selected' : ''; ?>>Gram Panchayat Mukhiyas (Current: <?php echo number_format($counts['mukhiyas']); ?>)</option>
                                <option value="sarpanchs" <?php echo ($selected_tab === 'sarpanchs') ? 'selected' : ''; ?>>Gram Kutchery Sarpanchs (Current: <?php echo number_format($counts['sarpanchs']); ?>)</option>
                                <option value="zp_members" <?php echo ($selected_tab === 'zp_members' || $selected_tab === 'zila_parishad') ? 'selected' : ''; ?>>Zila Parishad Territorial Members (Current: <?php echo number_format($counts['zp_members']); ?>)</option>
                                <option value="zp_officials" <?php echo ($selected_tab === 'zp_officials') ? 'selected' : ''; ?>>Zila Parishad Council Officials / Adhyaksh (Current: <?php echo number_format($counts['zp_officials']); ?>)</option>
                                <option value="panchayat_samiti" <?php echo ($selected_tab === 'panchayat_samiti' || $selected_tab === 'samiti') ? 'selected' : ''; ?>>Panchayat Samiti Block Pramukhs (Current: <?php echo number_format($counts['panchayat_samiti']); ?>)</option>
                            </select>
                            <small class="text-muted">Choose the leadership roster database to update.</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark">Import Mode <span class="text-danger">*</span></label>
                            <div class="d-flex gap-3 mt-1">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode" id="modeUpsert" value="upsert" checked>
                                    <label class="form-check-label fw-semibold text-dark" for="modeUpsert">
                                        <strong>Upsert (Recommended)</strong>
                                        <div class="small text-muted">Updates existing record if matched, otherwise inserts as new.</div>
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="mode" id="modeInsert" value="insert">
                                    <label class="form-check-label fw-semibold text-dark" for="modeInsert">
                                        <strong>Append / Insert Only</strong>
                                        <div class="small text-muted">Inserts all rows directly as new entries.</div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- File Dropzone -->
                    <div class="upload-dropzone mb-4" id="dropzoneEl" onclick="document.getElementById('fileInput').click()">
                        <input type="file" name="csv_file" id="fileInput" class="d-none" accept=".csv, .txt" required onchange="handleFileSelected(this)">
                        <div class="text-danger fs-1 mb-2">
                            <i class="fas fa-cloud-arrow-up"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1" id="dropzoneText">Click or Drag & Drop CSV File Here</h5>
                        <p class="small text-muted mb-0">Supported file formats: <strong>.CSV</strong> (Comma Separated Values) with UTF-8 encoding.</p>
                        <div id="fileInfoBadge" class="mt-2 d-none">
                            <span class="badge bg-success text-white py-2 px-3 rounded-pill fs-7">
                                <i class="fas fa-file-csv me-1"></i> <span id="fileNameDisplay">file.csv</span>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            <i class="fas fa-shield-halved text-success me-1"></i> Database transaction safety enabled. Invalid rows will be logged and skipped gracefully.
                        </small>
                        <button type="submit" class="btn btn-danger btn-lg rounded-pill px-5 fw-bold shadow-sm" id="submitBtn">
                            <i class="fas fa-upload me-2"></i> Start Bulk Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<script>
function handleFileSelected(input) {
    const dropText = document.getElementById('dropzoneText');
    const badge = document.getElementById('fileInfoBadge');
    const nameDisplay = document.getElementById('fileNameDisplay');
    
    if (input.files && input.files[0]) {
        const file = input.files[0];
        nameDisplay.textContent = `${file.name} (${(file.size / 1024).toFixed(1)} KB)`;
        badge.classList.remove('d-none');
        dropText.textContent = "File Selected - Click to change";
    }
}

const dropzone = document.getElementById('dropzoneEl');
if (dropzone) {
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('dragover');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('dragover');
        }, false);
    });

    dropzone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            document.getElementById('fileInput').files = files;
            handleFileSelected(document.getElementById('fileInput'));
        }
    });
}
</script>

</body>
</html>
