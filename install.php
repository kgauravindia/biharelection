<?php
/**
 * BiharElection.com - Database Setup & Auto-Seeder
 */
require_once __DIR__ . '/config.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Bihar Election — Database Migration & Seeder</h2>";
echo "<p>Connecting to database <strong>" . DB_NAME . "</strong> on <strong>" . DB_HOST . "</strong> as <strong>" . DB_USER . "</strong>...</p>";

$pdo = Database::getConnection();

if (!$pdo) {
    echo "<p style='color: red;'><strong>Database Connection Error:</strong> Unable to connect to MySQL with the provided credentials.</p>";
    echo "<p><em>Note: If you are running locally on Laragon, please ensure the user <code>u305984835_biharelection</code> and database <code>u305984835_biharelection</code> have been created in your MySQL (or run this on your live cPanel / hosting server).</em></p>";
    echo "<p>The website is currently functioning smoothly using the resilient JSON dataset fallback.</p>";
    exit;
}

echo "<p style='color: green;'><strong>Connected successfully to MySQL!</strong></p>";

// 1. Run Schema
try {
    $sql = file_get_contents(__DIR__ . '/database_setup.sql');
    $pdo->exec($sql);
    echo "<p>✓ All database tables created successfully.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error creating tables: " . $e->getMessage() . "</p>";
}

// 2. Seed Districts
try {
    $districts = json_decode(file_get_contents(__DIR__ . '/assets/data/districts.json'), true);
    $stmt = $pdo->prepare("INSERT INTO be_districts (id, name, name_hi, slug, headquarters, division, total_ac, total_electors, ac_list, description) 
                           VALUES (:id, :name, :name_hi, :slug, :headquarters, :division, :total_ac, :total_electors, :ac_list, :description)
                           ON DUPLICATE KEY UPDATE name=VALUES(name), name_hi=VALUES(name_hi), total_ac=VALUES(total_ac), total_electors=VALUES(total_electors), ac_list=VALUES(ac_list), description=VALUES(description)");
    
    foreach ($districts as $d) {
        $stmt->execute([
            ':id' => $d['id'],
            ':name' => $d['name'],
            ':name_hi' => $d['name_hi'] ?? '',
            ':slug' => $d['slug'],
            ':headquarters' => $d['headquarters'],
            ':division' => $d['division'],
            ':total_ac' => $d['total_ac'],
            ':total_electors' => $d['total_electors'] ?? 0,
            ':ac_list' => json_encode($d['ac_list'] ?? []),
            ':description' => $d['description'] ?? ''
        ]);
    }
    echo "<p>✓ 38 Districts seeded successfully.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error seeding districts: " . $e->getMessage() . "</p>";
}

// 3. Seed Constituencies
try {
    $constituencies = json_decode(file_get_contents(__DIR__ . '/assets/data/constituencies.json'), true);
    $stmt = $pdo->prepare("INSERT INTO be_constituencies (ac_no, name, name_hi, slug, district, district_hi, lok_sabha, reservation, total_electors, male_electors, female_electors, polling_stations, blocks, total_panchayats, current_mla, current_party, election_2020, election_2015, key_issues, party_history, candidates_2026_expected) 
                           VALUES (:ac_no, :name, :name_hi, :slug, :district, :district_hi, :lok_sabha, :reservation, :total_electors, :male_electors, :female_electors, :polling_stations, :blocks, :total_panchayats, :current_mla, :current_party, :election_2020, :election_2015, :key_issues, :party_history, :candidates_2026_expected)
                           ON DUPLICATE KEY UPDATE name=VALUES(name), district=VALUES(district), current_mla=VALUES(current_mla), current_party=VALUES(current_party), election_2020=VALUES(election_2020), election_2015=VALUES(election_2015), key_issues=VALUES(key_issues)");
    
    foreach ($constituencies as $c) {
        $stmt->execute([
            ':ac_no' => $c['ac_no'],
            ':name' => $c['name'],
            ':name_hi' => $c['name_hi'] ?? '',
            ':slug' => $c['slug'],
            ':district' => $c['district'],
            ':district_hi' => $c['district_hi'] ?? '',
            ':lok_sabha' => $c['lok_sabha'] ?? '',
            ':reservation' => $c['reservation'] ?? 'GEN',
            ':total_electors' => $c['total_electors'] ?? 0,
            ':male_electors' => $c['male_electors'] ?? 0,
            ':female_electors' => $c['female_electors'] ?? 0,
            ':polling_stations' => $c['polling_stations'] ?? 0,
            ':blocks' => json_encode($c['blocks'] ?? []),
            ':total_panchayats' => $c['total_panchayats'] ?? 0,
            ':current_mla' => $c['current_mla'] ?? '',
            ':current_party' => $c['current_party'] ?? '',
            ':election_2020' => json_encode($c['election_2020'] ?? []),
            ':election_2015' => json_encode($c['election_2015'] ?? []),
            ':key_issues' => json_encode($c['key_issues'] ?? []),
            ':party_history' => $c['party_history'] ?? '',
            ':candidates_2026_expected' => json_encode($c['candidates_2026_expected'] ?? [])
        ]);
    }
    $countSeeded = count($constituencies);
    echo "<p>✓ All {$countSeeded} Assembly Constituencies seeded successfully into database.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error seeding constituencies: " . $e->getMessage() . "</p>";
}

// 4. Seed Candidates
try {
    $candidates = json_decode(file_get_contents(__DIR__ . '/assets/data/candidates.json'), true);
    $stmt = $pdo->prepare("INSERT INTO be_candidates (id, name, name_hi, slug, party, party_short, constituency, district, designation, age, education, profession, assets_declared, liabilities, criminal_cases, verified, photo, bio, social_links, election_record) 
                           VALUES (:id, :name, :name_hi, :slug, :party, :party_short, :constituency, :district, :designation, :age, :education, :profession, :assets_declared, :liabilities, :criminal_cases, :verified, :photo, :bio, :social_links, :election_record)
                           ON DUPLICATE KEY UPDATE name=VALUES(name), designation=VALUES(designation), assets_declared=VALUES(assets_declared), criminal_cases=VALUES(criminal_cases), verified=VALUES(verified)");
    
    foreach ($candidates as $cand) {
        $stmt->execute([
            ':id' => $cand['id'],
            ':name' => $cand['name'],
            ':name_hi' => $cand['name_hi'] ?? '',
            ':slug' => $cand['slug'],
            ':party' => $cand['party'],
            ':party_short' => $cand['party_short'],
            ':constituency' => $cand['constituency'],
            ':district' => $cand['district'],
            ':designation' => $cand['designation'] ?? '',
            ':age' => $cand['age'] ?? null,
            ':education' => $cand['education'] ?? '',
            ':profession' => $cand['profession'] ?? '',
            ':assets_declared' => $cand['assets_declared'] ?? '',
            ':liabilities' => $cand['liabilities'] ?? '',
            ':criminal_cases' => $cand['criminal_cases'] ?? 0,
            ':verified' => $cand['verified'] ? 1 : 0,
            ':photo' => $cand['photo'] ?? '',
            ':bio' => $cand['bio'] ?? '',
            ':social_links' => json_encode($cand['social_links'] ?? []),
            ':election_record' => json_encode($cand['election_record'] ?? [])
        ]);
    }
    echo "<p>✓ Candidates seeded successfully.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error seeding candidates: " . $e->getMessage() . "</p>";
}

// 5. Seed Panchayats
try {
    $panchayats = json_decode(file_get_contents(__DIR__ . '/assets/data/panchayats.json'), true);
    $stmt = $pdo->prepare("INSERT INTO be_panchayats (id, district, district_hi, block, block_hi, panchayat_name, panchayat_hi, total_wards, total_voters, current_mukhiya, current_sarpanch, reservation_2026_mukhiya, reservation_2026_sarpanch, zila_parishad_territory_no, delimitation_status, key_issues) 
                           VALUES (:id, :district, :district_hi, :block, :block_hi, :panchayat_name, :panchayat_hi, :total_wards, :total_voters, :current_mukhiya, :current_sarpanch, :reservation_2026_mukhiya, :reservation_2026_sarpanch, :zila_parishad_territory_no, :delimitation_status, :key_issues)
                           ON DUPLICATE KEY UPDATE current_mukhiya=VALUES(current_mukhiya), reservation_2026_mukhiya=VALUES(reservation_2026_mukhiya), delimitation_status=VALUES(delimitation_status)");
    
    foreach ($panchayats as $p) {
        $stmt->execute([
            ':id' => $p['id'],
            ':district' => $p['district'],
            ':district_hi' => $p['district_hi'] ?? '',
            ':block' => $p['block'],
            ':block_hi' => $p['block_hi'] ?? '',
            ':panchayat_name' => $p['panchayat_name'],
            ':panchayat_hi' => $p['panchayat_hi'] ?? '',
            ':total_wards' => $p['total_wards'] ?? 0,
            ':total_voters' => $p['total_voters'] ?? 0,
            ':current_mukhiya' => $p['current_mukhiya'] ?? '',
            ':current_sarpanch' => $p['current_sarpanch'] ?? '',
            ':reservation_2026_mukhiya' => $p['reservation_2026_mukhiya'] ?? '',
            ':reservation_2026_sarpanch' => $p['reservation_2026_sarpanch'] ?? '',
            ':zila_parishad_territory_no' => $p['zila_parishad_territory_no'] ?? '',
            ':delimitation_status' => $p['delimitation_status'] ?? '',
            ':key_issues' => json_encode($p['key_issues'] ?? [])
        ]);
    }
    echo "<p>✓ Panchayats seeded successfully.</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>Error seeding panchayats: " . $e->getMessage() . "</p>";
}

echo "<hr><p style='color: green; font-weight: bold;'>🎉 Database initialization complete! You can now access all features on <a href='index.php'>Bihar Election Homepage</a>.</p>";
