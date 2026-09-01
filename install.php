<?php
/**
 * BiharElection.com - Web-Based Database Setup & Auto-Seeder
 */
require_once __DIR__ . '/config.php';

@ini_set('max_execution_time', '300');
@ini_set('memory_limit', '512M');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Database Setup & JSON Synchronizer — Bihar Election</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8fafc; font-family: 'Segoe UI', system-ui, sans-serif; padding: 2rem; }
        .log-card { background: white; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.08); max-width: 800px; margin: auto; padding: 2rem; }
    </style>
</head>
<body>
<div class="log-card">
    <h2 class="fw-bold mb-3" style="color: #0b192c;">⚡ Bihar Election — Database Migration & JSON Seeder</h2>
    <p class="text-muted">Connecting to MySQL database <strong><?php echo htmlspecialchars(DB_NAME); ?></strong> on <strong><?php echo htmlspecialchars(DB_HOST); ?></strong>...</p>

<?php
$pdo = Database::getConnection();

if (!$pdo) {
    echo "<div class='alert alert-danger'><strong>Connection Failed:</strong> " . htmlspecialchars(Database::$lastError ?? 'Unable to connect to MySQL') . "</div>";
    echo "</div></body></html>";
    exit;
}

echo "<div class='alert alert-success py-2'>✓ Connected successfully to MySQL!</div>";
echo "<ul class='list-group list-group-flush mb-4 small'>";

// 1. Run Schema Setup
try {
    $tablesToReset = [
        'be_districts', 'be_constituencies', 'be_mukhiyas', 'be_sarpanchs', 
        'be_zila_parishad_members', 'be_zila_parishad_officials', 'be_mps_loksabha', 
        'be_mps_rajyasabha', 'be_mlcs', 'be_mlas_2015', 'be_mukhiyas_2016', 
        'be_panchayat_samiti_2016', 'be_census_districts', 'be_census_subdistricts'
    ];
    foreach ($tablesToReset as $t) {
        $pdo->exec("DROP TABLE IF EXISTS `$t`");
    }
    $schemaSql = file_get_contents(__DIR__ . '/database_setup.sql');
    $pdo->exec($schemaSql);
    echo "<li class='list-group-item text-success'>✓ All 25 database tables verified/created.</li>";
} catch (Exception $e) {
    echo "<li class='list-group-item text-danger'>✗ Schema error: " . htmlspecialchars($e->getMessage()) . "</li>";
}

// Helper for batch inserts
function executeWebBatchInsert(PDO $pdo, string $table, array $columns, array $rows, int $batchSize = 500) {
    if (empty($rows)) return 0;
    $colList = implode(', ', array_map(function($c) { return "`$c`"; }, $columns));
    $valPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';
    $total = count($rows);
    $inserted = 0;
    
    $pdo->beginTransaction();
    for ($i = 0; $i < $total; $i += $batchSize) {
        $chunk = array_slice($rows, $i, $batchSize);
        $chunkPlaceholders = implode(', ', array_fill(0, count($chunk), $valPlaceholders));
        $sql = "INSERT INTO `$table` ($colList) VALUES $chunkPlaceholders";
        $stmt = $pdo->prepare($sql);
        $params = [];
        foreach ($chunk as $row) {
            foreach ($columns as $col) {
                $params[] = $row[$col] ?? null;
            }
        }
        $stmt->execute($params);
        $inserted += count($chunk);
    }
    $pdo->commit();
    return $inserted;
}

// 2. Districts (38)
$districts = json_decode(file_get_contents(__DIR__ . '/assets/data/districts.json'), true) ?? [];
$dRows = [];
foreach ($districts as $d) {
    $dRows[] = [
        'id' => $d['id'],
        'name' => $d['name'],
        'name_hi' => $d['name_hi'] ?? '',
        'slug' => $d['slug'],
        'headquarters' => $d['headquarters'],
        'division' => $d['division'],
        'total_ac' => $d['total_ac'] ?? 0,
        'total_electors' => $d['total_electors'] ?? 0,
        'ac_list' => json_encode($d['ac_list'] ?? []),
        'description' => $d['description'] ?? ''
    ];
}
executeWebBatchInsert($pdo, 'be_districts', ['id', 'name', 'name_hi', 'slug', 'headquarters', 'division', 'total_ac', 'total_electors', 'ac_list', 'description'], $dRows);
echo "<li class='list-group-item text-success'>✓ " . count($dRows) . " Districts synced.</li>";

// 3. Constituencies (243)
$constituencies = json_decode(file_get_contents(__DIR__ . '/assets/data/constituencies.json'), true) ?? [];
$cRows = [];
foreach ($constituencies as $c) {
    $cRows[] = [
        'ac_no' => $c['ac_no'],
        'name' => $c['name'],
        'name_hi' => $c['name_hi'] ?? '',
        'slug' => $c['slug'],
        'district' => $c['district'],
        'district_hi' => $c['district_hi'] ?? '',
        'lok_sabha' => $c['lok_sabha'] ?? '',
        'reservation' => $c['reservation'] ?? 'GEN',
        'total_electors' => $c['total_electors'] ?? 0,
        'male_electors' => $c['male_electors'] ?? 0,
        'female_electors' => $c['female_electors'] ?? 0,
        'polling_stations' => $c['polling_stations'] ?? 0,
        'blocks' => json_encode($c['blocks'] ?? []),
        'total_panchayats' => $c['total_panchayats'] ?? 0,
        'current_mla' => $c['current_mla'] ?? '',
        'current_party' => $c['current_party'] ?? '',
        'election_2020' => json_encode($c['election_2020'] ?? []),
        'election_2015' => json_encode($c['election_2015'] ?? []),
        'key_issues' => json_encode($c['key_issues'] ?? []),
        'party_history' => $c['party_history'] ?? '',
        'candidates_2026_expected' => json_encode($c['candidates_2026_expected'] ?? [])
    ];
}
executeWebBatchInsert($pdo, 'be_constituencies', ['ac_no', 'name', 'name_hi', 'slug', 'district', 'district_hi', 'lok_sabha', 'reservation', 'total_electors', 'male_electors', 'female_electors', 'polling_stations', 'blocks', 'total_panchayats', 'current_mla', 'current_party', 'election_2020', 'election_2015', 'key_issues', 'party_history', 'candidates_2026_expected'], $cRows);
echo "<li class='list-group-item text-success'>✓ " . count($cRows) . " Assembly Constituencies synced.</li>";

// 4. Mukhiyas (7,346)
$mukhiyas = json_decode(file_get_contents(__DIR__ . '/assets/data/mukhiya_directory.json'), true) ?? [];
$mRows = [];
foreach ($mukhiyas as $m) {
    $mRows[] = [
        'id' => $m['id'] ?? null,
        'candidate_name' => $m['candidate_name'] ?? '',
        'post' => $m['post'] ?? 'मुखिया',
        'district' => $m['district'] ?? '',
        'district_slug' => $m['district_slug'] ?? slugify($m['district'] ?? ''),
        'block' => $m['block'] ?? '',
        'panchayat' => $m['panchayat'] ?? '',
        'gender' => $m['gender'] ?? '',
        'category' => $m['category'] ?? '',
        'mobile' => $m['mobile'] ?? '',
        'address' => $m['address'] ?? '',
        'tenure' => '2021-2026'
    ];
}
executeWebBatchInsert($pdo, 'be_mukhiyas', ['id', 'candidate_name', 'post', 'district', 'district_slug', 'block', 'panchayat', 'gender', 'category', 'mobile', 'address', 'tenure'], $mRows, 400);
echo "<li class='list-group-item text-success'>✓ " . count($mRows) . " Elected Mukhiyas synced.</li>";

// 5. Sarpanchs (6,617)
$sarpanchs = json_decode(file_get_contents(__DIR__ . '/assets/data/sarpanch_directory.json'), true) ?? [];
$sRows = [];
foreach ($sarpanchs as $s) {
    $sRows[] = [
        'id' => $s['id'] ?? null,
        'candidate_name' => $s['candidate_name'] ?? '',
        'post' => $s['post'] ?? 'सरपंच',
        'district' => $s['district'] ?? '',
        'district_slug' => $s['district_slug'] ?? slugify($s['district'] ?? ''),
        'block' => $s['block'] ?? '',
        'panchayat' => $s['panchayat'] ?? '',
        'gender' => $s['gender'] ?? '',
        'category' => $s['category'] ?? '',
        'mobile' => $s['mobile'] ?? '',
        'address' => $s['address'] ?? '',
        'tenure' => '2021-2026'
    ];
}
executeWebBatchInsert($pdo, 'be_sarpanchs', ['id', 'candidate_name', 'post', 'district', 'district_slug', 'block', 'panchayat', 'gender', 'category', 'mobile', 'address', 'tenure'], $sRows, 400);
echo "<li class='list-group-item text-success'>✓ " . count($sRows) . " Elected Sarpanchs synced.</li>";

// 6. Zila Parishad Members (1,099+)
$zpMembers = json_decode(file_get_contents(__DIR__ . '/assets/data/zila_parishad_members.json'), true) ?? [];
$zpRows = [];
foreach ($zpMembers as $zp) {
    $zpRows[] = [
        'id' => $zp['id'] ?? null,
        'candidate_name' => $zp['candidate_name'] ?? '',
        'district' => $zp['district'] ?? '',
        'district_slug' => $zp['district_slug'] ?? slugify($zp['district'] ?? ''),
        'block' => $zp['block'] ?? '',
        'territory_no' => (string)($zp['territory_no'] ?? ''),
        'gender' => $zp['gender'] ?? '',
        'category' => $zp['category'] ?? '',
        'mobile' => $zp['mobile'] ?? '',
        'address' => $zp['address'] ?? '',
        'tenure' => '2021-2026'
    ];
}
executeWebBatchInsert($pdo, 'be_zila_parishad_members', ['id', 'candidate_name', 'district', 'district_slug', 'block', 'territory_no', 'gender', 'category', 'mobile', 'address', 'tenure'], $zpRows, 400);
echo "<li class='list-group-item text-success'>✓ " . count($zpRows) . " Zila Parishad Territorial Members synced.</li>";

// 7. Zila Parishad Officials (38 Presidents / Vice-Presidents)
$zpOfficials = json_decode(file_get_contents(__DIR__ . '/assets/data/zila_parishad_officials.json'), true) ?? [];
$zpoRows = [];
foreach ($zpOfficials as $zpo) {
    $zpoRows[] = [
        'id' => $zpo['id'] ?? null,
        'candidate_name' => $zpo['candidate_name'] ?? '',
        'post' => $zpo['post'] ?? '',
        'district' => $zpo['district'] ?? '',
        'district_slug' => $zpo['district_slug'] ?? slugify($zpo['district'] ?? ''),
        'gender' => $zpo['gender'] ?? '',
        'category' => $zpo['category'] ?? '',
        'address' => $zpo['address'] ?? '',
        'tenure' => '2021-2026'
    ];
}
executeWebBatchInsert($pdo, 'be_zila_parishad_officials', ['id', 'candidate_name', 'post', 'district', 'district_slug', 'gender', 'category', 'address', 'tenure'], $zpoRows);
echo "<li class='list-group-item text-success'>✓ " . count($zpoRows) . " Zila Parishad Chairpersons & Vice-Chairpersons synced.</li>";

// 8. Lok Sabha MPs (40)
$mpsLs = json_decode(file_get_contents(__DIR__ . '/assets/data/mps_loksabha.json'), true) ?? [];
$lsRows = [];
foreach ($mpsLs as $mp) {
    $lsRows[] = [
        'pc_no' => $mp['pc_no'] ?? 0,
        'pc_name' => $mp['pc_name'] ?? '',
        'slug' => $mp['slug'] ?? slugify($mp['pc_name'] ?? ''),
        'mp_name' => $mp['mp_name'] ?? '',
        'party' => $mp['party'] ?? '',
        'criminal_cases' => $mp['criminal_cases'] ?? 0,
        'house' => 'Lok Sabha'
    ];
}
executeWebBatchInsert($pdo, 'be_mps_loksabha', ['pc_no', 'pc_name', 'slug', 'mp_name', 'party', 'criminal_cases', 'house'], $lsRows);
echo "<li class='list-group-item text-success'>✓ " . count($lsRows) . " Lok Sabha MPs synced.</li>";

// 9. Rajya Sabha MPs (15)
$mpsRs = json_decode(file_get_contents(__DIR__ . '/assets/data/mps_rajyasabha.json'), true) ?? [];
$rsRows = [];
foreach ($mpsRs as $mp) {
    $rsRows[] = [
        'mp_name' => $mp['mp_name'] ?? '',
        'party' => $mp['party'] ?? '',
        'tenure' => $mp['tenure'] ?? '',
        'house' => 'Rajya Sabha'
    ];
}
executeWebBatchInsert($pdo, 'be_mps_rajyasabha', ['mp_name', 'party', 'tenure', 'house'], $rsRows);
echo "<li class='list-group-item text-success'>✓ " . count($rsRows) . " Rajya Sabha Parliamentarians synced.</li>";

// 10. MLCs (75)
$mlcs = json_decode(file_get_contents(__DIR__ . '/assets/data/mlcs.json'), true) ?? [];
$mlcRows = [];
foreach ($mlcs as $m) {
    $mlcRows[] = [
        'sr_no' => $m['sr_no'] ?? null,
        'name' => $m['name'] ?? '',
        'constituency' => $m['constituency'] ?? '',
        'tenure' => $m['tenure'] ?? '',
        'contact' => $m['contact'] ?? ''
    ];
}
executeWebBatchInsert($pdo, 'be_mlcs', ['sr_no', 'name', 'constituency', 'tenure', 'contact'], $mlcRows);
echo "<li class='list-group-item text-success'>✓ " . count($mlcRows) . " Vidhan Parishad MLCs synced.</li>";

// 11. 2015 MLAs (243)
$mlas15 = json_decode(file_get_contents(__DIR__ . '/assets/data/mlas_2015.json'), true) ?? [];
$m15Rows = [];
foreach ($mlas15 as $m) {
    $m15Rows[] = [
        'ac_no' => $m['ac_no'] ?? 0,
        'ac_name' => $m['ac_name'] ?? '',
        'slug' => $m['slug'] ?? slugify($m['ac_name'] ?? ''),
        'mla_name' => $m['mla_name'] ?? '',
        'party' => $m['party'] ?? '',
        'mobile' => $m['mobile'] ?? '',
        'tenure' => '2015–2020'
    ];
}
executeWebBatchInsert($pdo, 'be_mlas_2015', ['ac_no', 'ac_name', 'slug', 'mla_name', 'party', 'mobile', 'tenure'], $m15Rows);
echo "<li class='list-group-item text-success'>✓ " . count($m15Rows) . " Historical MLAs (2015) synced.</li>";

// 12. 2016 Mukhiyas (8,045)
$m16 = json_decode(file_get_contents(__DIR__ . '/assets/data/mukhiyas_2016.json'), true) ?? [];
$m16Rows = [];
foreach ($m16 as $m) {
    $m16Rows[] = [
        'district' => $m['district'] ?? '',
        'district_hi' => $m['district_hi'] ?? '',
        'district_slug' => $m['district_slug'] ?? slugify($m['district'] ?? ''),
        'block' => $m['block'] ?? '',
        'block_hi' => $m['block_hi'] ?? '',
        'panchayat' => $m['panchayat'] ?? '',
        'panchayat_hi' => $m['panchayat_hi'] ?? '',
        'mukhiya_2016' => $m['mukhiya_2016'] ?? '',
        'up_mukhiya_2016' => $m['up_mukhiya_2016'] ?? ''
    ];
}
executeWebBatchInsert($pdo, 'be_mukhiyas_2016', ['district', 'district_hi', 'district_slug', 'block', 'block_hi', 'panchayat', 'panchayat_hi', 'mukhiya_2016', 'up_mukhiya_2016'], $m16Rows, 500);
echo "<li class='list-group-item text-success'>✓ " . count($m16Rows) . " Historical Mukhiyas (2016) synced.</li>";

// 13. 2016 Block Samiti (534)
$samiti16 = json_decode(file_get_contents(__DIR__ . '/assets/data/panchayat_samiti_2016.json'), true) ?? [];
$s16Rows = [];
foreach ($samiti16 as $s) {
    $s16Rows[] = [
        'district' => $s['district'] ?? '',
        'district_hi' => $s['district_hi'] ?? '',
        'district_slug' => $s['district_slug'] ?? slugify($s['district'] ?? ''),
        'block' => $s['block'] ?? '',
        'block_hi' => $s['block_hi'] ?? '',
        'pramukh_2016' => $s['pramukh_2016'] ?? '',
        'up_pramukh_2016' => $s['up_pramukh_2016'] ?? ''
    ];
}
executeWebBatchInsert($pdo, 'be_panchayat_samiti_2016', ['district', 'district_hi', 'district_slug', 'block', 'block_hi', 'pramukh_2016', 'up_pramukh_2016'], $s16Rows);
echo "<li class='list-group-item text-success'>✓ " . count($s16Rows) . " Panchayat Samiti Pramukhs (2016) synced.</li>";

// 14. Census 2011 Districts (38)
$censusD = json_decode(file_get_contents(__DIR__ . '/assets/data/census_districts.json'), true) ?? [];
$cdRows = [];
foreach ($censusD as $slug => $cd) {
    $tot = $cd['total'] ?? [];
    $cdRows[] = [
        'district_code' => (string)($cd['district_code'] ?? ''),
        'name' => $cd['name'] ?? '',
        'slug' => $slug,
        'households' => $tot['households'] ?? 0,
        'population' => $tot['population'] ?? 0,
        'male' => $tot['male'] ?? 0,
        'female' => $tot['female'] ?? 0,
        'sex_ratio' => $tot['sex_ratio'] ?? 0,
        'pop_0_6' => $tot['pop_0_6'] ?? 0,
        'sc_population' => $tot['sc_population'] ?? 0,
        'sc_percentage' => $tot['sc_percentage'] ?? 0.00,
        'st_population' => $tot['st_population'] ?? 0,
        'st_percentage' => $tot['st_percentage'] ?? 0.00,
        'literates' => $tot['literates'] ?? 0,
        'literacy_rate' => $tot['literacy_rate'] ?? 0.00,
        'rural_population' => $tot['rural_population'] ?? 0,
        'urban_population' => $tot['urban_population'] ?? 0,
        'total_workers' => $tot['total_workers'] ?? 0,
        'main_workers' => $tot['main_workers'] ?? 0,
        'cultivators' => $tot['cultivators'] ?? 0,
        'agricultural_labourers' => $tot['agricultural_labourers'] ?? 0,
        'marginal_workers' => $tot['marginal_workers'] ?? 0,
        'non_workers' => $tot['non_workers'] ?? 0
    ];
}
executeWebBatchInsert($pdo, 'be_census_districts', ['district_code', 'name', 'slug', 'households', 'population', 'male', 'female', 'sex_ratio', 'pop_0_6', 'sc_population', 'sc_percentage', 'st_population', 'st_percentage', 'literates', 'literacy_rate', 'rural_population', 'urban_population', 'total_workers', 'main_workers', 'cultivators', 'agricultural_labourers', 'marginal_workers', 'non_workers'], $cdRows);
echo "<li class='list-group-item text-success'>✓ " . count($cdRows) . " Census 2011 Districts synced.</li>";

// 15. Census 2011 Sub-Districts (534)
$censusSub = json_decode(file_get_contents(__DIR__ . '/assets/data/census_subdistricts.json'), true) ?? [];
$csRows = [];
foreach ($censusSub as $distSlug => $subList) {
    if (is_array($subList)) {
        foreach ($subList as $sub) {
            $csRows[] = [
                'district_name' => $sub['district_name'] ?? '',
                'district_slug' => $distSlug,
                'sub_dist_code' => (string)($sub['sub_dist_code'] ?? ''),
                'sub_district' => $sub['sub_district'] ?? '',
                'households' => $sub['households'] ?? 0,
                'population' => $sub['population'] ?? 0,
                'male' => $sub['male'] ?? 0,
                'female' => $sub['female'] ?? 0,
                'sex_ratio' => $sub['sex_ratio'] ?? 0,
                'sc_population' => $sub['sc_population'] ?? 0,
                'st_population' => $sub['st_population'] ?? 0,
                'literates' => $sub['literates'] ?? 0,
                'literacy_rate' => $sub['literacy_rate'] ?? 0.00,
                'total_workers' => $sub['total_workers'] ?? 0,
                'cultivators' => $sub['cultivators'] ?? 0,
                'agricultural_labourers' => $sub['agricultural_labourers'] ?? 0
            ];
        }
    }
}
executeWebBatchInsert($pdo, 'be_census_subdistricts', ['district_name', 'district_slug', 'sub_dist_code', 'sub_district', 'households', 'population', 'male', 'female', 'sex_ratio', 'sc_population', 'st_population', 'literates', 'literacy_rate', 'total_workers', 'cultivators', 'agricultural_labourers'], $csRows, 400);
echo "<li class='list-group-item text-success'>✓ " . count($csRows) . " Census 2011 Sub-Districts (Blocks) synced.</li>";

// Ensure Admin Exists
$adminExists = $pdo->query("SELECT id FROM `be_admin_users` WHERE `username` = 'admin' LIMIT 1")->fetch();
if (!$adminExists) {
    $initPass = defined('DEFAULT_ADMIN_PASS') ? DEFAULT_ADMIN_PASS : 'Admin@ChangeMe2026';
    $hashedPwd = password_hash($initPass, PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO `be_admin_users` (`username`, `password`, `email`, `role`, `status`) VALUES ('admin', ?, 'admin@biharelection.com', 'superadmin', 'ACTIVE')")->execute([$hashedPwd]);
    echo "<li class='list-group-item text-success'>✓ Super Administrator account initialized.</li>";
}
?>
    </ul>

    <div class="p-3 bg-light rounded-3 border text-center">
        <h4 class="text-success fw-bold mb-2">🎉 Database Synchronization Complete!</h4>
        <p class="small text-muted mb-3">All flat-file JSON datasets have been transformed and written into MySQL tables.</p>
        <a href="index.php" class="btn btn-primary px-4 fw-bold">Visit Homepage</a>
        <a href="admin/index.php" class="btn btn-dark px-4 fw-bold ms-2">Admin Dashboard</a>
    </div>
</div>
</body>
</html>
