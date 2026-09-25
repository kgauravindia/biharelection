<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// AJAX endpoint to fetch slums for a given town code or town ID
if (isset($_GET['action']) && $_GET['action'] === 'get_slums') {
    header('Content-Type: application/json');
    $town_code = isset($_GET['town_code']) ? sanitize($_GET['town_code']) : '';
    $town_slug = isset($_GET['town_slug']) ? sanitize($_GET['town_slug']) : '';
    
    if (!$conn || (empty($town_code) && empty($town_slug))) {
        echo json_encode(['success' => false, 'slums' => []]);
        exit;
    }
    
    $where = [];
    if (!empty($town_code)) {
        $esc_c = $conn->real_escape_string($town_code);
        $where[] = "`town_code` = '$esc_c'";
    }
    if (!empty($town_slug)) {
        $esc_s = $conn->real_escape_string($town_slug);
        $where[] = "`town_slug` = '$esc_s'";
    }
    $where_clause = implode(' OR ', $where);
    
    $res = $conn->query("SELECT * FROM `census_town_slums_2011` WHERE $where_clause ORDER BY `slum_population` DESC, `slum_name` ASC");
    $slums = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $slums[] = $row;
        }
    }
    echo json_encode(['success' => true, 'slums' => $slums]);
    exit;
}

// Handle Export to CSV
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
    $filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
    $filter_civic = isset($_GET['civic']) ? sanitize($_GET['civic']) : '';
    $filter_class = isset($_GET['town_class']) ? sanitize($_GET['town_class']) : '';
    $filter_slum = isset($_GET['slum']) ? sanitize($_GET['slum']) : '';

    $where = ["1=1"];
    if (!empty($search)) {
        $esc = $conn->real_escape_string($search);
        $where[] = "(`town_name` LIKE '%$esc%' OR `town_code` LIKE '%$esc%' OR `district_name` LIKE '%$esc%' OR `sub_district_name` LIKE '%$esc%' OR `cd_block_name` LIKE '%$esc%' OR `manufactured_1` LIKE '%$esc%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $where[] = "(`district_name` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_civic)) {
        $esc_civic = $conn->real_escape_string($filter_civic);
        $where[] = "`civic_status` = '$esc_civic'";
    }
    if (!empty($filter_class)) {
        $esc_class = $conn->real_escape_string($filter_class);
        $where[] = "`town_class` = '$esc_class'";
    }
    if ($filter_slum === 'yes') {
        $where[] = "`slum_count` > 0";
    } elseif ($filter_slum === 'no') {
        $where[] = "(`slum_count` = 0 OR `slum_count` IS NULL)";
    }

    $whereSql = implode(' AND ', $where);
    $q_res = $conn->query("SELECT * FROM `census_towns_2011` WHERE $whereSql ORDER BY `district_name` ASC, `town_name` ASC");
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bihar_census_towns_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'ID', 'Town Code', 'Town Name', 'District', 'Sub-District / Block', 'Civic Status', 'Town Class',
        'Area (sq km)', 'Households', 'Total Population', 'Male', 'Female', 'Sex Ratio',
        'SC Population', 'ST Population', 'Slum Count', 'Slum Households', 'Slum Population',
        'Nationalised Banks', 'Commercial Banks', 'Coop Banks',
        'Manufactured Item 1', 'Manufactured Item 2', 'Manufactured Item 3'
    ]);

    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            fputcsv($output, [
                $r['id'] ?? '',
                $r['town_code'] ?? '',
                $r['town_name'] ?? '',
                $r['district_name'] ?? '',
                $r['sub_district_name'] ?: ($r['cd_block_name'] ?? ''),
                $r['civic_status'] ?? '',
                $r['town_class'] ?? '',
                $r['area_sq_km'] ?? '',
                $r['households'] ?? '',
                $r['population'] ?? '',
                $r['male'] ?? '',
                $r['female'] ?? '',
                $r['sex_ratio'] ?? '',
                $r['sc_population'] ?? '',
                $r['st_population'] ?? '',
                $r['slum_count'] ?? '0',
                $r['slum_households'] ?? '0',
                $r['slum_population'] ?? '0',
                $r['nationalised_banks'] ?? '0',
                $r['commercial_banks'] ?? '0',
                $r['cooperative_banks'] ?? '0',
                $r['manufactured_1'] ?? '',
                $r['manufactured_2'] ?? '',
                $r['manufactured_3'] ?? ''
            ]);
        }
    }
    fclose($output);
    exit;
}

// Handle Delete Town
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($conn && $del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `census_towns_2011` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Town record deleted successfully.";
            } else {
                $error = "Error deleting town: " . $conn->error;
            }
        }
    }
}

// Handle Add / Edit Town Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_town'])) {
    $town_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $town_name = trim($_POST['town_name'] ?? '');
    $town_code = trim($_POST['town_code'] ?? '');
    $district_name = trim($_POST['district_name'] ?? '');
    $sub_district_name = trim($_POST['sub_district_name'] ?? '');
    $civic_status = trim($_POST['civic_status'] ?? 'NP');
    $town_class = trim($_POST['town_class'] ?? 'Class III');
    $area_sq_km = (float)($_POST['area_sq_km'] ?? 0);
    $households = (int)($_POST['households'] ?? 0);
    $population = (int)($_POST['population'] ?? 0);
    $male = (int)($_POST['male'] ?? 0);
    $female = (int)($_POST['female'] ?? 0);
    $sc_population = (int)($_POST['sc_population'] ?? 0);
    $st_population = (int)($_POST['st_population'] ?? 0);
    $slum_count = (int)($_POST['slum_count'] ?? 0);
    $slum_households = (int)($_POST['slum_households'] ?? 0);
    $slum_population = (int)($_POST['slum_population'] ?? 0);
    $nationalised_banks = (int)($_POST['nationalised_banks'] ?? 0);
    $commercial_banks = (int)($_POST['commercial_banks'] ?? 0);
    $cooperative_banks = (int)($_POST['cooperative_banks'] ?? 0);
    $manufactured_1 = trim($_POST['manufactured_1'] ?? '');
    $manufactured_2 = trim($_POST['manufactured_2'] ?? '');
    $manufactured_3 = trim($_POST['manufactured_3'] ?? '');

    if (empty($town_name) || empty($district_name)) {
        $error = "Town Name and District are required.";
    } else {
        $town_slug = slugify($town_name);
        $district_slug = slugify($district_name);
        $sub_district_slug = slugify($sub_district_name);
        
        $sex_ratio = ($male > 0) ? round(($female / $male) * 1000) : 0;
        if ($population <= 0 && ($male > 0 || $female > 0)) {
            $population = $male + $female;
        }

        if ($town_id > 0) {
            // Update
            $sql = "UPDATE `census_towns_2011` SET 
                `town_name` = ?, `town_code` = ?, `town_slug` = ?, 
                `district_name` = ?, `district_slug` = ?, 
                `sub_district_name` = ?, `sub_district_slug` = ?, `cd_block_name` = ?,
                `civic_status` = ?, `town_class` = ?, `area_sq_km` = ?, 
                `households` = ?, `population` = ?, `male` = ?, `female` = ?, `sex_ratio` = ?,
                `sc_population` = ?, `st_population` = ?,
                `slum_count` = ?, `slum_households` = ?, `slum_population` = ?,
                `nationalised_banks` = ?, `commercial_banks` = ?, `cooperative_banks` = ?,
                `manufactured_1` = ?, `manufactured_2` = ?, `manufactured_3` = ?
                WHERE `id` = ?";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssssssssdiiiiiiiiiiiiisssi",
                    $town_name, $town_code, $town_slug,
                    $district_name, $district_slug,
                    $sub_district_name, $sub_district_slug, $sub_district_name,
                    $civic_status, $town_class, $area_sq_km,
                    $households, $population, $male, $female, $sex_ratio,
                    $sc_population, $st_population,
                    $slum_count, $slum_households, $slum_population,
                    $nationalised_banks, $commercial_banks, $cooperative_banks,
                    $manufactured_1, $manufactured_2, $manufactured_3,
                    $town_id
                );
                if ($stmt->execute()) {
                    $message = "Town '{$town_name}' updated successfully.";
                } else {
                    $error = "Error updating town: " . $conn->error;
                }
            }
        } else {
            // Insert
            $state_code = '10';
            $state_name = 'BIHAR';
            $sql = "INSERT INTO `census_towns_2011` (
                `state_code`, `state_name`, `district_name`, `district_slug`,
                `sub_district_name`, `sub_district_slug`, `cd_block_name`,
                `town_name`, `town_slug`, `town_code`,
                `civic_status`, `town_class`, `area_sq_km`,
                `households`, `population`, `male`, `female`, `sex_ratio`,
                `sc_population`, `st_population`,
                `slum_count`, `slum_households`, `slum_population`,
                `nationalised_banks`, `commercial_banks`, `cooperative_banks`,
                `manufactured_1`, `manufactured_2`, `manufactured_3`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssssssssssssdiiiiiiiiiiiiisss",
                    $state_code, $state_name, $district_name, $district_slug,
                    $sub_district_name, $sub_district_slug, $sub_district_name,
                    $town_name, $town_slug, $town_code,
                    $civic_status, $town_class, $area_sq_km,
                    $households, $population, $male, $female, $sex_ratio,
                    $sc_population, $st_population,
                    $slum_count, $slum_households, $slum_population,
                    $nationalised_banks, $commercial_banks, $cooperative_banks,
                    $manufactured_1, $manufactured_2, $manufactured_3
                );
                if ($stmt->execute()) {
                    $message = "New Town '{$town_name}' added successfully.";
                } else {
                    $error = "Error inserting town: " . $conn->error;
                }
            }
        }
    }
}

// Fetch Global Statistics
$stats = [
    'total_towns' => 0,
    'total_population' => 0,
    'total_households' => 0,
    'total_slums' => 0,
    'total_slum_pop' => 0,
    'm_corp_count' => 0,
    'nagar_parishad_count' => 0,
    'nagar_panchayat_count' => 0,
    'census_town_count' => 0,
    'total_nationalised_banks' => 0
];

if ($conn) {
    $stat_res = $conn->query("SELECT 
        COUNT(*) as total_towns,
        SUM(population) as total_pop,
        SUM(households) as total_hh,
        SUM(slum_count) as total_slums,
        SUM(slum_population) as total_slum_pop,
        SUM(CASE WHEN civic_status LIKE '%Corp%' THEN 1 ELSE 0 END) as m_corp,
        SUM(CASE WHEN civic_status LIKE '%Parishad%' OR civic_status = 'NPP' THEN 1 ELSE 0 END) as n_parishad,
        SUM(CASE WHEN civic_status = 'NP' OR civic_status LIKE '%Panchayat%' THEN 1 ELSE 0 END) as n_panchayat,
        SUM(CASE WHEN civic_status = 'CT' THEN 1 ELSE 0 END) as c_town,
        SUM(nationalised_banks) as nat_banks
        FROM `census_towns_2011`");
    
    if ($stat_res && $row = $stat_res->fetch_assoc()) {
        $stats['total_towns'] = (int)($row['total_towns'] ?? 0);
        $stats['total_population'] = (int)($row['total_pop'] ?? 0);
        $stats['total_households'] = (int)($row['total_hh'] ?? 0);
        $stats['total_slums'] = (int)($row['total_slums'] ?? 0);
        $stats['total_slum_pop'] = (int)($row['total_slum_pop'] ?? 0);
        $stats['m_corp_count'] = (int)($row['m_corp'] ?? 0);
        $stats['nagar_parishad_count'] = (int)($row['n_parishad'] ?? 0);
        $stats['nagar_panchayat_count'] = (int)($row['n_panchayat'] ?? 0);
        $stats['census_town_count'] = (int)($row['c_town'] ?? 0);
        $stats['total_nationalised_banks'] = (int)($row['nat_banks'] ?? 0);
    }
}

// Filters & Pagination
$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_civic = isset($_GET['civic']) ? sanitize($_GET['civic']) : '';
$filter_class = isset($_GET['town_class']) ? sanitize($_GET['town_class']) : '';
$filter_slum = isset($_GET['slum']) ? sanitize($_GET['slum']) : '';
$sort_by = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'pop_desc';

$towns = [];
$total_rows = 0;

$where = ["1=1"];
if (!empty($search)) {
    $esc = $conn->real_escape_string($search);
    $where[] = "(`town_name` LIKE '%$esc%' OR `town_code` LIKE '%$esc%' OR `district_name` LIKE '%$esc%' OR `sub_district_name` LIKE '%$esc%' OR `cd_block_name` LIKE '%$esc%' OR `manufactured_1` LIKE '%$esc%')";
}
if (!empty($filter_district)) {
    $esc_d = $conn->real_escape_string($filter_district);
    $where[] = "(`district_name` = '$esc_d' OR `district_slug` = '$esc_d')";
}
if (!empty($filter_civic)) {
    $esc_civic = $conn->real_escape_string($filter_civic);
    $where[] = "`civic_status` = '$esc_civic'";
}
if (!empty($filter_class)) {
    $esc_class = $conn->real_escape_string($filter_class);
    $where[] = "`town_class` = '$esc_class'";
}
if ($filter_slum === 'yes') {
    $where[] = "`slum_count` > 0";
} elseif ($filter_slum === 'no') {
    $where[] = "(`slum_count` = 0 OR `slum_count` IS NULL)";
}

$orderBy = "population DESC, town_name ASC";
if ($sort_by === 'name_asc') {
    $orderBy = "town_name ASC";
} elseif ($sort_by === 'district_asc') {
    $orderBy = "district_name ASC, town_name ASC";
} elseif ($sort_by === 'slum_desc') {
    $orderBy = "slum_count DESC, population DESC";
} elseif ($sort_by === 'area_desc') {
    $orderBy = "area_sq_km DESC";
}

$whereSql = implode(' AND ', $where);

if ($conn) {
    $count_res = $conn->query("SELECT COUNT(*) as c FROM `census_towns_2011` WHERE $whereSql");
    if ($count_res) {
        $total_rows = (int)$count_res->fetch_assoc()['c'];
    }

    $q_res = $conn->query("SELECT * FROM `census_towns_2011` WHERE $whereSql ORDER BY $orderBy LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $towns[] = $r;
        }
    }
}

$total_pages = ceil($total_rows / $limit);
$districts_all = DataProvider::getDistricts() ?: [];
usort($districts_all, function($a, $b) {
    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
});

// Civic Status options
$civic_statuses = ['M Corp.', 'Nagar Parishad', 'NP', 'CT', 'CB'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census 2011 Towns & Urban Local Bodies — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .badge-mcorp { background: #fee2e2; color: #991b1b; font-weight: 700; border: 1px solid #fecaca; }
        .badge-nparishad { background: #e0e7ff; color: #3730a3; font-weight: 600; border: 1px solid #c7d2fe; }
        .badge-npanchayat { background: #ecfdf5; color: #065f46; font-weight: 600; border: 1px solid #a7f3d0; }
        .badge-ctown { background: #fef3c7; color: #92400e; font-weight: 600; border: 1px solid #fde68a; }
        .badge-class { font-size: 0.75rem; padding: 2px 8px; border-radius: 6px; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 600; }
        .slum-badge { background: #fff1f2; color: #e11d48; border: 1px solid #ffe4e6; font-weight: 700; padding: 3px 8px; border-radius: 6px; cursor: pointer; transition: all 0.2s; }
        .slum-badge:hover { background: #ffe4e6; transform: scale(1.03); }
        .stat-icon-box { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="content-container">
            <!-- Header Breadcrumb & Actions -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                <div>
                    <h1 class="page-title mb-1">
                        <i class="fas fa-city text-primary me-2"></i> Bihar Urban Towns (Census 2011)
                    </h1>
                    <p class="text-muted small mb-0">
                        Manage 199 Statutory Towns, Municipal Corporations, Nagar Parishads, Nagar Panchayats, Census Towns and 670 Slum Wards across Bihar.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="towns.php?action=export_csv<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" class="btn btn-outline-success btn-sm px-3 shadow-sm fw-semibold">
                        <i class="fas fa-file-excel me-1"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#townModal" onclick="openAddTownModal()">
                        <i class="fas fa-plus-circle me-1"></i> Add New Town
                    </button>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                    <i class="fas fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- High-Level Metrics Row -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Total Towns</div>
                                <div class="fs-4 fw-bold text-dark mt-1"><?php echo number_format($stats['total_towns']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-building"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Urban Population</div>
                                <div class="fs-4 fw-bold text-success mt-1"><?php echo number_format($stats['total_population']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">M. Corporations</div>
                                <div class="fs-4 fw-bold text-danger mt-1"><?php echo number_format($stats['m_corp_count']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-landmark-dome"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Nagar Parishads</div>
                                <div class="fs-4 fw-bold text-indigo mt-1" style="color: #4f46e5;"><?php echo number_format($stats['nagar_parishad_count']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-indigo bg-opacity-10" style="background:#e0e7ff; color:#4f46e5;">
                                <i class="fas fa-archway"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Census Towns</div>
                                <div class="fs-4 fw-bold text-warning mt-1"><?php echo number_format($stats['census_town_count']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-tree-city"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Slum Settlements</div>
                                <div class="fs-4 fw-bold text-danger mt-1"><?php echo number_format($stats['total_slums']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-danger bg-opacity-10 text-danger">
                                <i class="fas fa-hand-holding-heart"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="towns.php" class="row g-2 align-items-center">
                        <div class="col-12 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Search Town, District, Block, Code..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="district" class="form-select form-select-sm">
                                <option value="">All 38 Districts</option>
                                <?php foreach ($districts_all as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name'] || $filter_district === $d['slug']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="civic" class="form-select form-select-sm">
                                <option value="">All Civic Statuses</option>
                                <option value="M Corp." <?php echo ($filter_civic === 'M Corp.') ? 'selected' : ''; ?>>Municipal Corporation (M Corp.)</option>
                                <option value="Nagar Parishad" <?php echo ($filter_civic === 'Nagar Parishad') ? 'selected' : ''; ?>>Nagar Parishad</option>
                                <option value="NP" <?php echo ($filter_civic === 'NP') ? 'selected' : ''; ?>>Nagar Panchayat (NP)</option>
                                <option value="CT" <?php echo ($filter_civic === 'CT') ? 'selected' : ''; ?>>Census Town (CT)</option>
                                <option value="CB" <?php echo ($filter_civic === 'CB') ? 'selected' : ''; ?>>Cantonment Board (CB)</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="town_class" class="form-select form-select-sm">
                                <option value="">All Town Classes</option>
                                <option value="Class I" <?php echo ($filter_class === 'Class I') ? 'selected' : ''; ?>>Class I (1,00,000+)</option>
                                <option value="Class II" <?php echo ($filter_class === 'Class II') ? 'selected' : ''; ?>>Class II (50k - 1 Lakh)</option>
                                <option value="Class III" <?php echo ($filter_class === 'Class III') ? 'selected' : ''; ?>>Class III (20k - 50k)</option>
                                <option value="Class IV" <?php echo ($filter_class === 'Class IV') ? 'selected' : ''; ?>>Class IV (10k - 20k)</option>
                                <option value="Class V" <?php echo ($filter_class === 'Class V') ? 'selected' : ''; ?>>Class V (5k - 10k)</option>
                                <option value="Class VI" <?php echo ($filter_class === 'Class VI') ? 'selected' : ''; ?>>Class VI (< 5k)</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-1">
                            <select name="slum" class="form-select form-select-sm">
                                <option value="">Slums: All</option>
                                <option value="yes" <?php echo ($filter_slum === 'yes') ? 'selected' : ''; ?>>With Slums</option>
                                <option value="no" <?php echo ($filter_slum === 'no') ? 'selected' : ''; ?>>No Slums</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-1">
                            <select name="sort" class="form-select form-select-sm">
                                <option value="pop_desc" <?php echo ($sort_by === 'pop_desc') ? 'selected' : ''; ?>>Pop ↓</option>
                                <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Name A-Z</option>
                                <option value="district_asc" <?php echo ($sort_by === 'district_asc') ? 'selected' : ''; ?>>District</option>
                                <option value="slum_desc" <?php echo ($sort_by === 'slum_desc') ? 'selected' : ''; ?>>Slums ↓</option>
                                <option value="area_desc" <?php echo ($sort_by === 'area_desc') ? 'selected' : ''; ?>>Area ↓</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">Filter</button>
                            <a href="towns.php" class="btn btn-light btn-sm border" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Towns Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        Towns Directory <span class="badge bg-light text-dark border ms-1"><?php echo number_format($total_rows); ?></span>
                    </span>
                    <span class="text-muted small">
                        Showing <?php echo $total_rows > 0 ? ($offset + 1) : 0; ?> - <?php echo min($offset + $limit, $total_rows); ?> of <?php echo number_format($total_rows); ?>
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Town Name & Code</th>
                                <th>District & Sub-District</th>
                                <th>Civic Status</th>
                                <th>Population & Sex Ratio</th>
                                <th>Households & Area</th>
                                <th>Slums</th>
                                <th>Amenities & Banks</th>
                                <th class="text-end" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($towns)): ?>
                                <?php foreach ($towns as $idx => $t): ?>
                                    <?php 
                                        $dSlug = $t['district_slug'] ?: slugify($t['district_name']);
                                        $tSlug = $t['town_slug'] ?: slugify($t['town_name']);
                                        $liveUrl = getTownUrl($dSlug, $tSlug);
                                        $civicBadge = 'badge-npanchayat';
                                        if (stripos($t['civic_status'], 'Corp') !== false) $civicBadge = 'badge-mcorp';
                                        elseif (stripos($t['civic_status'], 'Parishad') !== false || $t['civic_status'] === 'NPP') $civicBadge = 'badge-nparishad';
                                        elseif ($t['civic_status'] === 'CT') $civicBadge = 'badge-ctown';
                                    ?>
                                    <tr>
                                        <td class="text-muted small"><?php echo $offset + $idx + 1; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                                <span><?php echo htmlspecialchars($t['town_name']); ?></span>
                                                <a href="<?php echo $liveUrl; ?>" target="_blank" class="text-muted ms-1 small" title="View Public Page">
                                                    <i class="fas fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>
                                                </a>
                                            </div>
                                            <div class="text-muted small font-monospace" style="font-size: 0.75rem;">
                                                Code: <?php echo htmlspecialchars($t['town_code'] ?: '—'); ?> | Slug: <?php echo htmlspecialchars($t['town_slug']); ?>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($t['district_name']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($t['sub_district_name'] ?: ($t['cd_block_name'] ?: '—')); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $civicBadge; ?> px-2 py-1"><?php echo htmlspecialchars($t['civic_status']); ?></span>
                                            <?php if (!empty($t['town_class'])): ?>
                                                <div class="mt-1"><span class="badge-class"><?php echo htmlspecialchars($t['town_class']); ?></span></div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo number_format((int)$t['population']); ?></div>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                ♂ <?php echo number_format((int)$t['male']); ?> | ♀ <?php echo number_format((int)$t['female']); ?>
                                                <span class="ms-1 text-primary fw-semibold">(SR: <?php echo $t['sex_ratio'] ?: '—'; ?>)</span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?php echo number_format((int)$t['households']); ?> HH</div>
                                            <div class="text-muted small" style="font-size: 0.75rem;"><?php echo $t['area_sq_km'] > 0 ? number_format((float)$t['area_sq_km'], 2) . ' km²' : '—'; ?></div>
                                        </td>
                                        <td>
                                            <?php if ((int)$t['slum_count'] > 0): ?>
                                                <button type="button" class="btn p-0 border-0 slum-badge" onclick="viewTownSlums('<?php echo htmlspecialchars(addslashes($t['town_name'])); ?>', '<?php echo htmlspecialchars($t['town_code']); ?>', '<?php echo htmlspecialchars($t['town_slug']); ?>', <?php echo (int)$t['slum_count']; ?>)" title="Click to view all <?php echo (int)$t['slum_count']; ?> slum settlements">
                                                    <i class="fas fa-triangle-exclamation me-1"></i> <?php echo (int)$t['slum_count']; ?> Slums
                                                    <div style="font-size:0.7rem; font-weight:normal;"><?php echo number_format((int)$t['slum_population']); ?> Pop</div>
                                                </button>
                                            <?php else: ?>
                                                <span class="text-muted small">None</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small">
                                                <i class="fas fa-building-columns text-primary me-1" title="Banks"></i>
                                                <?php echo ((int)$t['nationalised_banks'] + (int)$t['commercial_banks'] + (int)$t['cooperative_banks']); ?> Banks
                                                <span class="text-muted" style="font-size: 0.75rem;">(Nat: <?php echo (int)$t['nationalised_banks']; ?>)</span>
                                            </div>
                                            <?php if (!empty($t['manufactured_1'])): ?>
                                                <div class="text-muted small text-truncate" style="max-width: 130px; font-size: 0.72rem;" title="<?php echo htmlspecialchars($t['manufactured_1'] . ($t['manufactured_2'] ? ', ' . $t['manufactured_2'] : '')); ?>">
                                                    <i class="fas fa-boxes-stacked me-1 text-secondary"></i> <?php echo htmlspecialchars($t['manufactured_1']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" title="View Full Details" onclick='viewTownDetails(<?php echo json_encode($t); ?>)'>
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" title="Edit Town" onclick='editTown(<?php echo json_encode($t); ?>)'>
                                                    <i class="fas fa-pencil"></i>
                                                </button>
                                                <a href="towns.php?delete_id=<?php echo $t['id']; ?>" class="btn btn-outline-danger" title="Delete Town" onclick="return confirm('Are you sure you want to delete <?php echo htmlspecialchars(addslashes($t['town_name'])); ?>?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center py-5 text-muted">
                                        <i class="fas fa-city fs-2 mb-2 d-block opacity-25"></i>
                                        No towns found matching your filter criteria.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="small text-muted">
                            Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                <?php
                                    $query_params = $_GET;
                                    $build_page_url = function($p) use ($query_params) {
                                        $query_params['page'] = $p;
                                        return 'towns.php?' . http_build_query($query_params);
                                    };
                                ?>
                                <?php if ($page > 1): ?>
                                    <li class="page-item"><a class="page-link" href="<?php echo $build_page_url(1); ?>">&laquo; First</a></li>
                                    <li class="page-item"><a class="page-link" href="<?php echo $build_page_url($page - 1); ?>">Prev</a></li>
                                <?php endif; ?>

                                <?php
                                    $start_p = max(1, $page - 3);
                                    $end_p = min($total_pages, $page + 3);
                                    for ($p = $start_p; $p <= $end_p; $p++):
                                ?>
                                    <li class="page-item <?php echo ($p == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo $build_page_url($p); ?>"><?php echo $p; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item"><a class="page-link" href="<?php echo $build_page_url($page + 1); ?>">Next</a></li>
                                    <li class="page-item"><a class="page-link" href="<?php echo $build_page_url($total_pages); ?>">Last &raquo;</a></li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<!-- Add / Edit Town Modal -->
<div class="modal fade" id="townModal" tabindex="-1" aria-labelledby="townModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="towns.php">
                <input type="hidden" name="save_town" value="1">
                <input type="hidden" name="id" id="form_town_id" value="0">
                
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold" id="townModalLabel">
                        <i class="fas fa-city me-2"></i> <span id="modal_title_action">Add New Town</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Town Name <span class="text-danger">*</span></label>
                            <input type="text" name="town_name" id="f_town_name" class="form-control" required placeholder="e.g. Gaya, Muzaffarpur, Rajgir">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Town Code (Census 2011)</label>
                            <input type="text" name="town_code" id="f_town_code" class="form-control" placeholder="e.g. 801452">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">District Name <span class="text-danger">*</span></label>
                            <select name="district_name" id="f_district_name" class="form-select" required>
                                <option value="">Select District</option>
                                <?php foreach ($districts_all as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Sub-District / CD Block</label>
                            <input type="text" name="sub_district_name" id="f_sub_district_name" class="form-control" placeholder="e.g. Gaya Town C.D. Block">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Civic Status</label>
                            <select name="civic_status" id="f_civic_status" class="form-select">
                                <option value="NP">Nagar Panchayat (NP)</option>
                                <option value="Nagar Parishad">Nagar Parishad (NPP)</option>
                                <option value="M Corp.">Municipal Corporation (M Corp.)</option>
                                <option value="CT">Census Town (CT)</option>
                                <option value="CB">Cantonment Board (CB)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Town Class</label>
                            <select name="town_class" id="f_town_class" class="form-select">
                                <option value="Class I">Class I (1,00,000+)</option>
                                <option value="Class II">Class II (50k - 1 Lakh)</option>
                                <option value="Class III">Class III (20k - 50k)</option>
                                <option value="Class IV">Class IV (10k - 20k)</option>
                                <option value="Class V">Class V (5k - 10k)</option>
                                <option value="Class VI">Class VI (< 5k)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Area (sq. km)</label>
                            <input type="number" step="0.01" name="area_sq_km" id="f_area_sq_km" class="form-control" placeholder="e.g. 50.17">
                        </div>

                        <hr class="my-2 text-muted">
                        <div class="col-12"><h6 class="fw-bold text-primary mb-0"><i class="fas fa-users me-2"></i>Demographics & Population (Census 2011)</h6></div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Households</label>
                            <input type="number" name="households" id="f_households" class="form-control form-control-sm" placeholder="e.g. 15000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Total Population</label>
                            <input type="number" name="population" id="f_population" class="form-control form-control-sm" placeholder="e.g. 85000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Male</label>
                            <input type="number" name="male" id="f_male" class="form-control form-control-sm" placeholder="e.g. 45000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Female</label>
                            <input type="number" name="female" id="f_female" class="form-control form-control-sm" placeholder="e.g. 40000">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">SC Population</label>
                            <input type="number" name="sc_population" id="f_sc_population" class="form-control form-control-sm" placeholder="e.g. 12000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">ST Population</label>
                            <input type="number" name="st_population" id="f_st_population" class="form-control form-control-sm" placeholder="e.g. 500">
                        </div>

                        <hr class="my-2 text-muted">
                        <div class="col-12"><h6 class="fw-bold text-danger mb-0"><i class="fas fa-triangle-exclamation me-2"></i>Slum Settlement Profile</h6></div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Slum Settlements Count</label>
                            <input type="number" name="slum_count" id="f_slum_count" class="form-control form-control-sm" placeholder="e.g. 12">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Slum Households</label>
                            <input type="number" name="slum_households" id="f_slum_households" class="form-control form-control-sm" placeholder="e.g. 2400">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Slum Population</label>
                            <input type="number" name="slum_population" id="f_slum_population" class="form-control form-control-sm" placeholder="e.g. 14000">
                        </div>

                        <hr class="my-2 text-muted">
                        <div class="col-12"><h6 class="fw-bold text-success mb-0"><i class="fas fa-building-columns me-2"></i>Banking & Economic Commodities</h6></div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Nationalised Banks</label>
                            <input type="number" name="nationalised_banks" id="f_nationalised_banks" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Commercial Banks</label>
                            <input type="number" name="commercial_banks" id="f_commercial_banks" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Cooperative Banks</label>
                            <input type="number" name="cooperative_banks" id="f_cooperative_banks" class="form-control form-control-sm" placeholder="0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Commodity 1</label>
                            <input type="text" name="manufactured_1" id="f_manufactured_1" class="form-control form-control-sm" placeholder="e.g. Incense Sticks">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Commodity 2</label>
                            <input type="text" name="manufactured_2" id="f_manufactured_2" class="form-control form-control-sm" placeholder="e.g. Stone Carving">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Commodity 3</label>
                            <input type="text" name="manufactured_3" id="f_manufactured_3" class="form-control form-control-sm" placeholder="e.g. Sweetmeat (Tilkut)">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-semibold px-4" id="btn_save_town">Save Town</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Town Slums Modal -->
<div class="modal fade" id="slumsModal" tabindex="-1" aria-labelledby="slumsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold" id="slumsModalLabel">
                    <i class="fas fa-hand-holding-heart me-2"></i> Slum Settlements in <span id="slum_town_name"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div id="slums_loading" class="text-center py-5">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="mt-2 text-muted">Loading slum settlements...</p>
                </div>
                <div id="slums_content" class="d-none">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="fw-bold text-dark">
                            Total Wards/Settlements: <span id="slums_count_badge" class="badge bg-danger">0</span>
                        </div>
                        <div class="small text-muted">Official Census 2011 Slum Primary Abstract</div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Slum Settlement / Ward Name</th>
                                    <th>Status</th>
                                    <th>Households</th>
                                    <th>Population</th>
                                    <th>Paved Roads</th>
                                    <th>Drainage</th>
                                    <th>Toilets & Latrines</th>
                                    <th>Tap Water Points</th>
                                    <th>Road Lights</th>
                                </tr>
                            </thead>
                            <tbody id="slums_table_body">
                            </tbody>
                        </table>
                    </div>
                </div>
                <div id="slums_empty" class="d-none text-center py-5 text-muted">
                    <i class="fas fa-info-circle fs-3 mb-2 d-block opacity-50"></i>
                    No detailed slum ward records found for this town in the database.
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- View Town Details Modal -->
<div class="modal fade" id="townDetailsModal" tabindex="-1" aria-labelledby="townDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-info text-dark">
                <h5 class="modal-title fw-bold" id="townDetailsModalLabel">
                    <i class="fas fa-city me-2"></i> Town Full Profile
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="town_details_body">
                <!-- Dynamically populated -->
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openAddTownModal() {
    document.getElementById('form_town_id').value = '0';
    document.getElementById('modal_title_action').innerText = 'Add New Town';
    document.getElementById('f_town_name').value = '';
    document.getElementById('f_town_code').value = '';
    document.getElementById('f_district_name').value = '';
    document.getElementById('f_sub_district_name').value = '';
    document.getElementById('f_civic_status').value = 'NP';
    document.getElementById('f_town_class').value = 'Class III';
    document.getElementById('f_area_sq_km').value = '';
    document.getElementById('f_households').value = '';
    document.getElementById('f_population').value = '';
    document.getElementById('f_male').value = '';
    document.getElementById('f_female').value = '';
    document.getElementById('f_sc_population').value = '';
    document.getElementById('f_st_population').value = '';
    document.getElementById('f_slum_count').value = '0';
    document.getElementById('f_slum_households').value = '0';
    document.getElementById('f_slum_population').value = '0';
    document.getElementById('f_nationalised_banks').value = '0';
    document.getElementById('f_commercial_banks').value = '0';
    document.getElementById('f_cooperative_banks').value = '0';
    document.getElementById('f_manufactured_1').value = '';
    document.getElementById('f_manufactured_2').value = '';
    document.getElementById('f_manufactured_3').value = '';
}

function editTown(t) {
    document.getElementById('form_town_id').value = t.id || '0';
    document.getElementById('modal_title_action').innerText = 'Edit Town: ' + (t.town_name || '');
    document.getElementById('f_town_name').value = t.town_name || '';
    document.getElementById('f_town_code').value = t.town_code || '';
    document.getElementById('f_district_name').value = t.district_name || '';
    document.getElementById('f_sub_district_name').value = t.sub_district_name || t.cd_block_name || '';
    document.getElementById('f_civic_status').value = t.civic_status || 'NP';
    document.getElementById('f_town_class').value = t.town_class || 'Class III';
    document.getElementById('f_area_sq_km').value = t.area_sq_km || '';
    document.getElementById('f_households').value = t.households || '';
    document.getElementById('f_population').value = t.population || '';
    document.getElementById('f_male').value = t.male || '';
    document.getElementById('f_female').value = t.female || '';
    document.getElementById('f_sc_population').value = t.sc_population || '';
    document.getElementById('f_st_population').value = t.st_population || '';
    document.getElementById('f_slum_count').value = t.slum_count || '0';
    document.getElementById('f_slum_households').value = t.slum_households || '0';
    document.getElementById('f_slum_population').value = t.slum_population || '0';
    document.getElementById('f_nationalised_banks').value = t.nationalised_banks || '0';
    document.getElementById('f_commercial_banks').value = t.commercial_banks || '0';
    document.getElementById('f_cooperative_banks').value = t.cooperative_banks || '0';
    document.getElementById('f_manufactured_1').value = t.manufactured_1 || '';
    document.getElementById('f_manufactured_2').value = t.manufactured_2 || '';
    document.getElementById('f_manufactured_3').value = t.manufactured_3 || '';

    const modal = new bootstrap.Modal(document.getElementById('townModal'));
    modal.show();
}

function viewTownDetails(t) {
    const body = document.getElementById('town_details_body');
    const sexRatio = t.sex_ratio || (t.male > 0 ? Math.round((t.female / t.male) * 1000) : '—');
    
    let html = `
        <div class="row g-3">
            <div class="col-12 pb-2 border-bottom">
                <h4 class="fw-bold text-dark mb-1">${t.town_name} (${t.civic_status || 'Town'})</h4>
                <div class="text-muted small">
                    District: <strong>${t.district_name}</strong> | Block: <strong>${t.sub_district_name || t.cd_block_name || '—'}</strong> | Code: <strong>${t.town_code || '—'}</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 text-center">
                    <div class="text-muted small">Total Population</div>
                    <div class="fs-4 fw-bold text-primary">${Number(t.population || 0).toLocaleString()}</div>
                    <div class="small text-muted">♂ ${Number(t.male || 0).toLocaleString()} | ♀ ${Number(t.female || 0).toLocaleString()}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 text-center">
                    <div class="text-muted small">Sex Ratio & Households</div>
                    <div class="fs-4 fw-bold text-success">${sexRatio} ♀ / 1k ♂</div>
                    <div class="small text-muted">${Number(t.households || 0).toLocaleString()} Households</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 text-center">
                    <div class="text-muted small">Area & Town Class</div>
                    <div class="fs-4 fw-bold text-warning">${t.town_class || 'Class III'}</div>
                    <div class="small text-muted">${t.area_sq_km > 0 ? t.area_sq_km + ' sq. km' : '—'}</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border p-3 h-100">
                    <h6 class="fw-bold text-danger mb-2"><i class="fas fa-triangle-exclamation me-1"></i> Slum Profile</h6>
                    <ul class="list-unstyled mb-0 small">
                        <li><strong>Slum Settlements:</strong> ${t.slum_count || 0}</li>
                        <li><strong>Slum Population:</strong> ${Number(t.slum_population || 0).toLocaleString()}</li>
                        <li><strong>Slum Households:</strong> ${Number(t.slum_households || 0).toLocaleString()}</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border p-3 h-100">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-building-columns me-1"></i> Banking & Commerce</h6>
                    <ul class="list-unstyled mb-0 small">
                        <li><strong>Nationalised Banks:</strong> ${t.nationalised_banks || 0}</li>
                        <li><strong>Commercial Banks:</strong> ${t.commercial_banks || 0}</li>
                        <li><strong>Cooperative Banks:</strong> ${t.cooperative_banks || 0}</li>
                        <li><strong>Main Commodity:</strong> ${t.manufactured_1 || '—'}</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    body.innerHTML = html;
    const modal = new bootstrap.Modal(document.getElementById('townDetailsModal'));
    modal.show();
}

function viewTownSlums(townName, townCode, townSlug, count) {
    document.getElementById('slum_town_name').innerText = townName;
    document.getElementById('slums_loading').classList.remove('d-none');
    document.getElementById('slums_content').classList.add('d-none');
    document.getElementById('slums_empty').classList.add('d-none');
    
    const modal = new bootstrap.Modal(document.getElementById('slumsModal'));
    modal.show();

    fetch(`towns.php?action=get_slums&town_code=${encodeURIComponent(townCode)}&town_slug=${encodeURIComponent(townSlug)}`)
        .then(res => res.json())
        .then(data => {
            document.getElementById('slums_loading').classList.add('d-none');
            if (data.success && data.slums && data.slums.length > 0) {
                document.getElementById('slums_count_badge').innerText = data.slums.length;
                let rowsHtml = '';
                data.slums.forEach((s, idx) => {
                    rowsHtml += `
                        <tr>
                            <td>${idx + 1}</td>
                            <td class="fw-semibold text-dark">${s.slum_name || '—'}</td>
                            <td><span class="badge ${s.is_notified ? 'bg-success' : 'bg-secondary'}">${s.is_notified ? 'Notified' : 'Non-Notified'}</span></td>
                            <td>${Number(s.households || 0).toLocaleString()}</td>
                            <td class="fw-bold">${Number(s.slum_population || 0).toLocaleString()}</td>
                            <td>${s.paved_roads_km > 0 ? s.paved_roads_km + ' km' : '—'}</td>
                            <td>${s.drainage_system || '—'}</td>
                            <td>Flush: ${s.latrines_flush || 0}, Pit: ${s.latrines_pit || 0}</td>
                            <td>${s.tap_points_water || '—'}</td>
                            <td>${s.electricity_road_light ? 'Available' : '—'}</td>
                        </tr>
                    `;
                });
                document.getElementById('slums_table_body').innerHTML = rowsHtml;
                document.getElementById('slums_content').classList.remove('d-none');
            } else {
                document.getElementById('slums_empty').classList.remove('d-none');
            }
        })
        .catch(err => {
            document.getElementById('slums_loading').classList.add('d-none');
            document.getElementById('slums_empty').classList.remove('d-none');
        });
}
</script>

</body>
</html>
