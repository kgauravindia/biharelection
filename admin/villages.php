<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// AJAX endpoint to get blocks for a district
if (isset($_GET['action']) && $_GET['action'] === 'get_blocks') {
    header('Content-Type: application/json');
    $district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
    if (!$conn || empty($district)) {
        echo json_encode(['success' => false, 'blocks' => []]);
        exit;
    }
    
    $esc_d = $conn->real_escape_string($district);
    $res = $conn->query("SELECT DISTINCT sub_district FROM `census_subdistricts` WHERE `district_name` = '$esc_d' OR `district_slug` = '$esc_d' ORDER BY sub_district ASC");
    $blocks = [];
    if ($res && $res->num_rows > 0) {
        while ($row = $res->fetch_assoc()) {
            $blocks[] = $row['sub_district'];
        }
    } else {
        // Fallback from census_villages_2011
        $res2 = $conn->query("SELECT DISTINCT COALESCE(NULLIF(sub_district_name, ''), cd_block_name) as b FROM `census_villages_2011` WHERE (`district_name` = '$esc_d' OR `district_slug` = '$esc_d') AND (sub_district_name != '' OR cd_block_name != '') ORDER BY b ASC");
        if ($res2) {
            while ($r2 = $res2->fetch_assoc()) {
                if (!empty($r2['b'])) $blocks[] = $r2['b'];
            }
        }
    }
    echo json_encode(['success' => true, 'blocks' => $blocks]);
    exit;
}

// Handle Export to CSV (with limit safety)
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    $search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
    $filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
    $filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';
    $filter_gp = isset($_GET['gp']) ? sanitize($_GET['gp']) : '';
    $filter_pop = isset($_GET['pop_range']) ? sanitize($_GET['pop_range']) : '';

    $where = ["1=1"];
    if (!empty($search)) {
        $esc = $conn->real_escape_string($search);
        $where[] = "(`village_name` LIKE '%$esc%' OR `village_code` LIKE '%$esc%' OR `gram_panchayat_name` LIKE '%$esc%' OR `cd_block_name` LIKE '%$esc%' OR `sub_district_name` LIKE '%$esc%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $where[] = "(`district_name` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_block)) {
        $esc_b = $conn->real_escape_string($filter_block);
        $where[] = "(`sub_district_name` = '$esc_b' OR `sub_district_slug` = '$esc_b' OR `cd_block_name` LIKE '%$esc_b%')";
    }
    if (!empty($filter_gp)) {
        $esc_gp = $conn->real_escape_string($filter_gp);
        $where[] = "(`gram_panchayat_name` LIKE '%$esc_gp%' OR `gram_panchayat_slug` = '$esc_gp')";
    }
    if ($filter_pop === '10k_plus') {
        $where[] = "`population` >= 10000";
    } elseif ($filter_pop === '5k_10k') {
        $where[] = "`population` >= 5000 AND `population` < 10000";
    } elseif ($filter_pop === '1k_5k') {
        $where[] = "`population` >= 1000 AND `population` < 5000";
    } elseif ($filter_pop === 'under_1k') {
        $where[] = "`population` < 1000";
    }

    $whereSql = implode(' AND ', $where);
    $q_res = $conn->query("SELECT * FROM `census_villages_2011` WHERE $whereSql ORDER BY `district_name` ASC, `sub_district_name` ASC, `village_name` ASC LIMIT 10000");
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bihar_census_villages_' . date('Ymd_His') . '.csv');
    $output = fopen('php://output', 'w');
    
    fputcsv($output, [
        'ID', 'Village Code', 'Village Name', 'District', 'Sub-District (Block)', 'Gram Panchayat',
        'Area (Hectares)', 'Households', 'Total Population', 'Male', 'Female', 'Sex Ratio',
        'SC Population', 'ST Population', 'Block HQ Distance (km)', 'District HQ Distance (km)', 'Nearest Town Distance (km)'
    ]);

    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            fputcsv($output, [
                $r['id'] ?? '',
                $r['village_code'] ?? '',
                $r['village_name'] ?? '',
                $r['district_name'] ?? '',
                $r['sub_district_name'] ?: ($r['cd_block_name'] ?? ''),
                $r['gram_panchayat_name'] ?? '',
                $r['area_hectares'] ?? '',
                $r['households'] ?? '',
                $r['population'] ?? '',
                $r['male'] ?? '',
                $r['female'] ?? '',
                $r['sex_ratio'] ?? '',
                $r['sc_population'] ?? '',
                $r['st_population'] ?? '',
                $r['sub_district_hq_distance'] ?? '',
                $r['district_hq_distance'] ?? '',
                $r['nearest_town_distance'] ?? ''
            ]);
        }
    }
    fclose($output);
    exit;
}

// Handle Delete Village
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($conn && $del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `census_villages_2011` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Village record deleted successfully.";
            } else {
                $error = "Error deleting village: " . $conn->error;
            }
        }
    }
}

// Handle Add / Edit Village Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_village'])) {
    $village_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $village_name = trim($_POST['village_name'] ?? '');
    $village_code = trim($_POST['village_code'] ?? '');
    $district_name = trim($_POST['district_name'] ?? '');
    $sub_district_name = trim($_POST['sub_district_name'] ?? '');
    $gram_panchayat_name = trim($_POST['gram_panchayat_name'] ?? '');
    $area_hectares = (float)($_POST['area_hectares'] ?? 0);
    $households = (int)($_POST['households'] ?? 0);
    $population = (int)($_POST['population'] ?? 0);
    $male = (int)($_POST['male'] ?? 0);
    $female = (int)($_POST['female'] ?? 0);
    $sc_population = (int)($_POST['sc_population'] ?? 0);
    $st_population = (int)($_POST['st_population'] ?? 0);
    $sub_district_hq_distance = trim($_POST['sub_district_hq_distance'] ?? '');
    $district_hq_distance = trim($_POST['district_hq_distance'] ?? '');
    $nearest_town_name = trim($_POST['nearest_town_name'] ?? '');
    $nearest_town_distance = trim($_POST['nearest_town_distance'] ?? '');

    if (empty($village_name) || empty($district_name)) {
        $error = "Village Name and District Name are required.";
    } else {
        $village_slug = slugify($village_name);
        $district_slug = slugify($district_name);
        $sub_district_slug = slugify($sub_district_name);
        $gram_panchayat_slug = slugify($gram_panchayat_name);
        $sex_ratio = ($male > 0) ? round(($female / $male) * 1000) : 0;
        if ($population <= 0 && ($male > 0 || $female > 0)) {
            $population = $male + $female;
        }

        if ($village_id > 0) {
            // Update
            $sql = "UPDATE `census_villages_2011` SET 
                `village_name` = ?, `village_code` = ?, `village_slug` = ?, 
                `district_name` = ?, `district_slug` = ?, 
                `sub_district_name` = ?, `sub_district_slug` = ?, `cd_block_name` = ?,
                `gram_panchayat_name` = ?, `gram_panchayat_slug` = ?,
                `area_hectares` = ?, `households` = ?, `population` = ?, 
                `male` = ?, `female` = ?, `sex_ratio` = ?,
                `sc_population` = ?, `st_population` = ?,
                `sub_district_hq_distance` = ?, `district_hq_distance` = ?, 
                `nearest_town_name` = ?, `nearest_town_distance` = ?
                WHERE `id` = ?";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("sssssssssdiiiiiiissssi",
                    $village_name, $village_code, $village_slug,
                    $district_name, $district_slug,
                    $sub_district_name, $sub_district_slug, $sub_district_name,
                    $gram_panchayat_name, $gram_panchayat_slug,
                    $area_hectares, $households, $population,
                    $male, $female, $sex_ratio,
                    $sc_population, $st_population,
                    $sub_district_hq_distance, $district_hq_distance,
                    $nearest_town_name, $nearest_town_distance,
                    $village_id
                );
                if ($stmt->execute()) {
                    $message = "Village '{$village_name}' updated successfully.";
                } else {
                    $error = "Error updating village: " . $conn->error;
                }
            }
        } else {
            // Insert
            $state_code = '10';
            $state_name = 'BIHAR';
            $sql = "INSERT INTO `census_villages_2011` (
                `state_code`, `state_name`, `district_name`, `district_slug`,
                `sub_district_name`, `sub_district_slug`, `cd_block_name`,
                `village_name`, `village_slug`, `village_code`,
                `gram_panchayat_name`, `gram_panchayat_slug`,
                `area_hectares`, `households`, `population`, `male`, `female`, `sex_ratio`,
                `sc_population`, `st_population`,
                `sub_district_hq_distance`, `district_hq_distance`, `nearest_town_name`, `nearest_town_distance`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            if ($stmt) {
                $stmt->bind_param("ssssssssssssdiiiiiiissss",
                    $state_code, $state_name, $district_name, $district_slug,
                    $sub_district_name, $sub_district_slug, $sub_district_name,
                    $village_name, $village_slug, $village_code,
                    $gram_panchayat_name, $gram_panchayat_slug,
                    $area_hectares, $households, $population, $male, $female, $sex_ratio,
                    $sc_population, $st_population,
                    $sub_district_hq_distance, $district_hq_distance, $nearest_town_name, $nearest_town_distance
                );
                if ($stmt->execute()) {
                    $message = "New Village '{$village_name}' added successfully.";
                } else {
                    $error = "Error adding village: " . $conn->error;
                }
            }
        }
    }
}

// Global Summary Statistics
$stats = [
    'total_villages' => 44874,
    'total_districts' => 38,
    'total_blocks' => 534,
    'total_population' => 0,
    'total_households' => 0,
    'total_sc_pop' => 0,
    'total_st_pop' => 0
];

if ($conn) {
    $stat_res = $conn->query("SELECT 
        COUNT(*) as total_v,
        COUNT(DISTINCT district_name) as total_d,
        COUNT(DISTINCT sub_district_slug) as total_b,
        SUM(population) as total_pop,
        SUM(households) as total_hh,
        SUM(sc_population) as total_sc,
        SUM(st_population) as total_st
        FROM `census_villages_2011`");
    
    if ($stat_res && $row = $stat_res->fetch_assoc()) {
        $stats['total_villages'] = (int)($row['total_v'] ?? 44874);
        $stats['total_districts'] = (int)($row['total_d'] ?? 38);
        $stats['total_blocks'] = (int)($row['total_b'] ?? 534);
        $stats['total_population'] = (int)($row['total_pop'] ?? 0);
        $stats['total_households'] = (int)($row['total_hh'] ?? 0);
        $stats['total_sc_pop'] = (int)($row['total_sc'] ?? 0);
        $stats['total_st_pop'] = (int)($row['total_st'] ?? 0);
    }
}

// Filters & Pagination
$limit_options = [25, 50, 100];
$limit = isset($_GET['limit']) && in_array((int)$_GET['limit'], $limit_options) ? (int)$_GET['limit'] : 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';
$filter_gp = isset($_GET['gp']) ? sanitize($_GET['gp']) : '';
$filter_pop = isset($_GET['pop_range']) ? sanitize($_GET['pop_range']) : '';
$sort_by = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'name_asc';

$villages = [];
$total_rows = 0;

$where = ["1=1"];
if (!empty($search)) {
    $esc = $conn->real_escape_string($search);
    $where[] = "(`village_name` LIKE '%$esc%' OR `village_code` LIKE '%$esc%' OR `gram_panchayat_name` LIKE '%$esc%' OR `cd_block_name` LIKE '%$esc%' OR `sub_district_name` LIKE '%$esc%')";
}
if (!empty($filter_district)) {
    $esc_d = $conn->real_escape_string($filter_district);
    $where[] = "(`district_name` = '$esc_d' OR `district_slug` = '$esc_d')";
}
if (!empty($filter_block)) {
    $esc_b = $conn->real_escape_string($filter_block);
    $where[] = "(`sub_district_name` = '$esc_b' OR `sub_district_slug` = '$esc_b' OR `cd_block_name` LIKE '%$esc_b%')";
}
if (!empty($filter_gp)) {
    $esc_gp = $conn->real_escape_string($filter_gp);
    $where[] = "(`gram_panchayat_name` LIKE '%$esc_gp%' OR `gram_panchayat_slug` = '$esc_gp')";
}
if ($filter_pop === '10k_plus') {
    $where[] = "`population` >= 10000";
} elseif ($filter_pop === '5k_10k') {
    $where[] = "`population` >= 5000 AND `population` < 10000";
} elseif ($filter_pop === '1k_5k') {
    $where[] = "`population` >= 1000 AND `population` < 5000";
} elseif ($filter_pop === 'under_1k') {
    $where[] = "`population` < 1000";
}

$orderBy = "district_name ASC, sub_district_name ASC, village_name ASC";
if ($sort_by === 'name_asc') {
    $orderBy = "village_name ASC";
} elseif ($sort_by === 'pop_desc') {
    $orderBy = "population DESC";
} elseif ($sort_by === 'pop_asc') {
    $orderBy = "population ASC";
} elseif ($sort_by === 'hh_desc') {
    $orderBy = "households DESC";
} elseif ($sort_by === 'area_desc') {
    $orderBy = "area_hectares DESC";
} elseif ($sort_by === 'code_asc') {
    $orderBy = "village_code ASC";
}

$whereSql = implode(' AND ', $where);

if ($conn) {
    $count_res = $conn->query("SELECT COUNT(*) as c FROM `census_villages_2011` WHERE $whereSql");
    if ($count_res) {
        $total_rows = (int)$count_res->fetch_assoc()['c'];
    }

    $q_res = $conn->query("SELECT * FROM `census_villages_2011` WHERE $whereSql ORDER BY $orderBy LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $villages[] = $r;
        }
    }
}

$total_pages = ceil($total_rows / $limit);
$districts_all = DataProvider::getDistricts() ?: [];
usort($districts_all, function($a, $b) {
    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
});

// Preload blocks if district is filtered
$district_blocks = [];
if (!empty($filter_district) && $conn) {
    $esc_d = $conn->real_escape_string($filter_district);
    $b_res = $conn->query("SELECT DISTINCT sub_district FROM `census_subdistricts` WHERE `district_name` = '$esc_d' OR `district_slug` = '$esc_d' ORDER BY sub_district ASC");
    if ($b_res && $b_res->num_rows > 0) {
        while ($brow = $b_res->fetch_assoc()) {
            $district_blocks[] = $brow['sub_district'];
        }
    } else {
        $b_res2 = $conn->query("SELECT DISTINCT COALESCE(NULLIF(sub_district_name, ''), cd_block_name) as b FROM `census_villages_2011` WHERE (`district_name` = '$esc_d' OR `district_slug` = '$esc_d') AND (sub_district_name != '' OR cd_block_name != '') ORDER BY b ASC");
        if ($b_res2) {
            while ($brow2 = $b_res2->fetch_assoc()) {
                if (!empty($brow2['b'])) $district_blocks[] = $brow2['b'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Census 2011 Villages Directory — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .code-badge { font-family: monospace; font-size: 0.8rem; background: #e2e8f0; color: #334155; padding: 2px 6px; border-radius: 4px; font-weight: 600; }
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
                        <i class="fas fa-tree-city text-success me-2"></i> Bihar Rural Villages (Census 2011)
                    </h1>
                    <p class="text-muted small mb-0">
                        Manage 44,874 Census Villages across 38 Districts, 534 Blocks & 8,053+ Gram Panchayats.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="villages.php?action=export_csv<?php echo !empty($_SERVER['QUERY_STRING']) ? '&' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" class="btn btn-outline-success btn-sm px-3 shadow-sm fw-semibold">
                        <i class="fas fa-file-excel me-1"></i> Export Filtered CSV
                    </a>
                    <button type="button" class="btn btn-success btn-sm px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#villageModal" onclick="openAddVillageModal()">
                        <i class="fas fa-plus-circle me-1"></i> Add New Village
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
                                <div class="text-muted small fw-semibold">Census Villages</div>
                                <div class="fs-4 fw-bold text-success mt-1"><?php echo number_format($stats['total_villages']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-success bg-opacity-10 text-success">
                                <i class="fas fa-house-chimney-window"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Districts / Blocks</div>
                                <div class="fs-4 fw-bold text-primary mt-1"><?php echo $stats['total_districts']; ?> / <?php echo $stats['total_blocks']; ?></div>
                            </div>
                            <div class="stat-icon-box bg-primary bg-opacity-10 text-primary">
                                <i class="fas fa-map-location-dot"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Rural Population</div>
                                <div class="fs-4 fw-bold text-dark mt-1"><?php echo number_format($stats['total_population']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-secondary bg-opacity-10 text-dark">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">Rural Households</div>
                                <div class="fs-4 fw-bold text-indigo mt-1" style="color: #4f46e5;"><?php echo number_format($stats['total_households']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-indigo bg-opacity-10" style="background:#e0e7ff; color:#4f46e5;">
                                <i class="fas fa-people-roof"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">SC Population</div>
                                <div class="fs-4 fw-bold text-warning mt-1"><?php echo number_format($stats['total_sc_pop']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-warning bg-opacity-10 text-warning">
                                <i class="fas fa-user-group"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <div class="stat-card p-3 h-100 bg-white shadow-sm border rounded-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="text-muted small fw-semibold">ST Population</div>
                                <div class="fs-4 fw-bold text-info mt-1"><?php echo number_format($stats['total_st_pop']); ?></div>
                            </div>
                            <div class="stat-icon-box bg-info bg-opacity-10 text-info">
                                <i class="fas fa-mountain-sun"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="villages.php" class="row g-2 align-items-center" id="filterForm">
                        <div class="col-12 col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Search Village, Code (e.g. 231456), GP, Block..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="district" id="filter_district_select" class="form-select form-select-sm" onchange="loadDistrictBlocks(this.value)">
                                <option value="">All 38 Districts</option>
                                <?php foreach ($districts_all as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name'] || $filter_district === $d['slug']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <select name="block" id="filter_block_select" class="form-select form-select-sm">
                                <option value="">All Blocks</option>
                                <?php foreach ($district_blocks as $blk): ?>
                                    <option value="<?php echo htmlspecialchars($blk); ?>" <?php echo ($filter_block === $blk) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($blk); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <input type="text" name="gp" class="form-control form-control-sm" placeholder="Gram Panchayat..." value="<?php echo htmlspecialchars($filter_gp); ?>">
                        </div>
                        <div class="col-6 col-md-1">
                            <select name="pop_range" class="form-select form-select-sm">
                                <option value="">Pop: All</option>
                                <option value="10k_plus" <?php echo ($filter_pop === '10k_plus') ? 'selected' : ''; ?>>10k+</option>
                                <option value="5k_10k" <?php echo ($filter_pop === '5k_10k') ? 'selected' : ''; ?>>5k-10k</option>
                                <option value="1k_5k" <?php echo ($filter_pop === '1k_5k') ? 'selected' : ''; ?>>1k-5k</option>
                                <option value="under_1k" <?php echo ($filter_pop === 'under_1k') ? 'selected' : ''; ?>>< 1k</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-1">
                            <select name="sort" class="form-select form-select-sm">
                                <option value="name_asc" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Name A-Z</option>
                                <option value="pop_desc" <?php echo ($sort_by === 'pop_desc') ? 'selected' : ''; ?>>Pop ↓</option>
                                <option value="pop_asc" <?php echo ($sort_by === 'pop_asc') ? 'selected' : ''; ?>>Pop ↑</option>
                                <option value="hh_desc" <?php echo ($sort_by === 'hh_desc') ? 'selected' : ''; ?>>HH ↓</option>
                                <option value="area_desc" <?php echo ($sort_by === 'area_desc') ? 'selected' : ''; ?>>Area ↓</option>
                                <option value="code_asc" <?php echo ($sort_by === 'code_asc') ? 'selected' : ''; ?>>Code ↑</option>
                            </select>
                        </div>
                        <div class="col-6 col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-success btn-sm w-100 fw-semibold">Filter</button>
                            <a href="villages.php" class="btn btn-light btn-sm border" title="Reset Filters"><i class="fas fa-rotate-left"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <span class="fw-bold text-dark">
                        Villages Directory <span class="badge bg-light text-dark border ms-1"><?php echo number_format($total_rows); ?></span>
                    </span>
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center gap-1 small text-muted">
                            <span>Per page:</span>
                            <select class="form-select form-select-sm py-0 px-2" style="width: auto;" onchange="changeLimit(this.value)">
                                <option value="25" <?php echo ($limit === 25) ? 'selected' : ''; ?>>25</option>
                                <option value="50" <?php echo ($limit === 50) ? 'selected' : ''; ?>>50</option>
                                <option value="100" <?php echo ($limit === 100) ? 'selected' : ''; ?>>100</option>
                            </select>
                        </div>
                        <span class="text-muted small">
                            Showing <?php echo $total_rows > 0 ? ($offset + 1) : 0; ?> - <?php echo min($offset + $limit, $total_rows); ?> of <?php echo number_format($total_rows); ?>
                        </span>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th>Village Name & Code</th>
                                <th>District & Block</th>
                                <th>Gram Panchayat</th>
                                <th>Population & Sex Ratio</th>
                                <th>Households & Area</th>
                                <th>Distances (HQ / Town)</th>
                                <th class="text-end" style="width: 140px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($villages)): ?>
                                <?php foreach ($villages as $idx => $v): ?>
                                    <?php 
                                        $dSlug = $v['district_slug'] ?: slugify($v['district_name']);
                                        $bSlug = $v['sub_district_slug'] ?: slugify($v['sub_district_name'] ?: $v['cd_block_name']);
                                        $vSlug = $v['village_slug'] ?: ($v['village_code'] ?: slugify($v['village_name']));
                                        $liveUrl = getVillageUrl($dSlug, $bSlug, $vSlug);
                                    ?>
                                    <tr>
                                        <td class="text-muted small"><?php echo $offset + $idx + 1; ?></td>
                                        <td>
                                            <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                                <span><?php echo htmlspecialchars($v['village_name']); ?></span>
                                                <a href="<?php echo $liveUrl; ?>" target="_blank" class="text-muted ms-1 small" title="View Public Page">
                                                    <i class="fas fa-arrow-up-right-from-square" style="font-size: 0.75rem;"></i>
                                                </a>
                                            </div>
                                            <div class="mt-1">
                                                <span class="code-badge">Code: <?php echo htmlspecialchars($v['village_code'] ?: '—'); ?></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark"><?php echo htmlspecialchars($v['district_name']); ?></div>
                                            <div class="text-muted small"><?php echo htmlspecialchars($v['sub_district_name'] ?: ($v['cd_block_name'] ?: '—')); ?> Block</div>
                                        </td>
                                        <td>
                                            <?php if (!empty($v['gram_panchayat_name'])): ?>
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                                    <i class="fas fa-leaf me-1"></i> <?php echo htmlspecialchars($v['gram_panchayat_name']); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo number_format((int)$v['population']); ?></div>
                                            <div class="text-muted small" style="font-size: 0.75rem;">
                                                ♂ <?php echo number_format((int)$v['male']); ?> | ♀ <?php echo number_format((int)$v['female']); ?>
                                                <span class="ms-1 text-primary fw-semibold">(SR: <?php echo $v['sex_ratio'] ?: '—'; ?>)</span>
                                            </div>
                                            <?php if ((int)$v['sc_population'] > 0 || (int)$v['st_population'] > 0): ?>
                                                <div class="text-muted" style="font-size: 0.7rem;">
                                                    SC: <?php echo number_format((int)$v['sc_population']); ?> | ST: <?php echo number_format((int)$v['st_population']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold"><?php echo number_format((int)$v['households']); ?> HH</div>
                                            <div class="text-muted small" style="font-size: 0.75rem;"><?php echo $v['area_hectares'] > 0 ? number_format((float)$v['area_hectares'], 2) . ' Ha' : '—'; ?></div>
                                        </td>
                                        <td>
                                            <div class="small text-muted" style="font-size: 0.75rem;">
                                                <div>Block HQ: <strong><?php echo $v['sub_district_hq_distance'] ? $v['sub_district_hq_distance'] . ' km' : '—'; ?></strong></div>
                                                <div>District HQ: <strong><?php echo $v['district_hq_distance'] ? $v['district_hq_distance'] . ' km' : '—'; ?></strong></div>
                                                <div>Nearest Town: <strong><?php echo $v['nearest_town_distance'] ? $v['nearest_town_distance'] . ' km' : '—'; ?></strong></div>
                                            </div>
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-info" title="View Details" onclick='viewVillageDetails(<?php echo json_encode($v); ?>)'>
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-primary" title="Edit Village" onclick='editVillage(<?php echo json_encode($v); ?>)'>
                                                    <i class="fas fa-pencil"></i>
                                                </button>
                                                <a href="villages.php?delete_id=<?php echo $v['id']; ?>" class="btn btn-outline-danger" title="Delete Village" onclick="return confirm('Are you sure you want to delete village <?php echo htmlspecialchars(addslashes($v['village_name'])); ?>?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fas fa-tree-city fs-2 mb-2 d-block opacity-25"></i>
                                        No villages found matching your filter criteria.
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
                                        return 'villages.php?' . http_build_query($query_params);
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

<!-- Add / Edit Village Modal -->
<div class="modal fade" id="villageModal" tabindex="-1" aria-labelledby="villageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <form method="POST" action="villages.php">
                <input type="hidden" name="save_village" value="1">
                <input type="hidden" name="id" id="form_village_id" value="0">
                
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="villageModalLabel">
                        <i class="fas fa-tree-city me-2"></i> <span id="v_modal_title_action">Add New Village</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Village Name <span class="text-danger">*</span></label>
                            <input type="text" name="village_name" id="vf_village_name" class="form-control" required placeholder="e.g. Rampur, Belaganj, Dumri">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Village Code (Census 2011)</label>
                            <input type="text" name="village_code" id="vf_village_code" class="form-control" placeholder="e.g. 256489">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-semibold">District Name <span class="text-danger">*</span></label>
                            <select name="district_name" id="vf_district_name" class="form-select" required>
                                <option value="">Select District</option>
                                <?php foreach ($districts_all as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sub-District / CD Block</label>
                            <input type="text" name="sub_district_name" id="vf_sub_district_name" class="form-control" placeholder="e.g. Manpur">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Gram Panchayat Name</label>
                            <input type="text" name="gram_panchayat_name" id="vf_gram_panchayat_name" class="form-control" placeholder="e.g. Bairagi Gram Panchayat">
                        </div>

                        <hr class="my-2 text-muted">
                        <div class="col-12"><h6 class="fw-bold text-success mb-0"><i class="fas fa-users me-2"></i>Demographics (Census 2011)</h6></div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Households</label>
                            <input type="number" name="households" id="vf_households" class="form-control form-control-sm" placeholder="e.g. 450">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Total Population</label>
                            <input type="number" name="population" id="vf_population" class="form-control form-control-sm" placeholder="e.g. 2400">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Male</label>
                            <input type="number" name="male" id="vf_male" class="form-control form-control-sm" placeholder="e.g. 1250">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Female</label>
                            <input type="number" name="female" id="vf_female" class="form-control form-control-sm" placeholder="e.g. 1150">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Area (Hectares)</label>
                            <input type="number" step="0.01" name="area_hectares" id="vf_area_hectares" class="form-control form-control-sm" placeholder="e.g. 350.50">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">SC Population</label>
                            <input type="number" name="sc_population" id="vf_sc_population" class="form-control form-control-sm" placeholder="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">ST Population</label>
                            <input type="number" name="st_population" id="vf_st_population" class="form-control form-control-sm" placeholder="0">
                        </div>

                        <hr class="my-2 text-muted">
                        <div class="col-12"><h6 class="fw-bold text-primary mb-0"><i class="fas fa-location-crosshairs me-2"></i>Distances & Nearest Town</h6></div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Block HQ Distance (km)</label>
                            <input type="text" name="sub_district_hq_distance" id="vf_sub_district_hq_distance" class="form-control form-control-sm" placeholder="e.g. 12">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">District HQ Distance (km)</label>
                            <input type="text" name="district_hq_distance" id="vf_district_hq_distance" class="form-control form-control-sm" placeholder="e.g. 35">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nearest Town Name</label>
                            <input type="text" name="nearest_town_name" id="vf_nearest_town_name" class="form-control form-control-sm" placeholder="e.g. Gaya">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold">Nearest Town Distance (km)</label>
                            <input type="text" name="nearest_town_distance" id="vf_nearest_town_distance" class="form-control form-control-sm" placeholder="e.g. 15">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success fw-semibold px-4">Save Village</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- View Village Details Modal -->
<div class="modal fade" id="villageDetailsModal" tabindex="-1" aria-labelledby="villageDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="villageDetailsModalLabel">
                    <i class="fas fa-tree-city me-2"></i> Village Detailed Profile
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="village_details_body">
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
function changeLimit(val) {
    const url = new URL(window.location.href);
    url.searchParams.set('limit', val);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

function loadDistrictBlocks(districtName) {
    const blockSelect = document.getElementById('filter_block_select');
    blockSelect.innerHTML = '<option value="">Loading blocks...</option>';
    
    if (!districtName) {
        blockSelect.innerHTML = '<option value="">All Blocks</option>';
        return;
    }

    fetch(`villages.php?action=get_blocks&district=${encodeURIComponent(districtName)}`)
        .then(res => res.json())
        .then(data => {
            let html = '<option value="">All Blocks</option>';
            if (data.success && data.blocks) {
                data.blocks.forEach(b => {
                    html += `<option value="${b}">${b}</option>`;
                });
            }
            blockSelect.innerHTML = html;
        })
        .catch(() => {
            blockSelect.innerHTML = '<option value="">All Blocks</option>';
        });
}

function openAddVillageModal() {
    document.getElementById('form_village_id').value = '0';
    document.getElementById('v_modal_title_action').innerText = 'Add New Village';
    document.getElementById('vf_village_name').value = '';
    document.getElementById('vf_village_code').value = '';
    document.getElementById('vf_district_name').value = '';
    document.getElementById('vf_sub_district_name').value = '';
    document.getElementById('vf_gram_panchayat_name').value = '';
    document.getElementById('vf_households').value = '';
    document.getElementById('vf_population').value = '';
    document.getElementById('vf_male').value = '';
    document.getElementById('vf_female').value = '';
    document.getElementById('vf_area_hectares').value = '';
    document.getElementById('vf_sc_population').value = '';
    document.getElementById('vf_st_population').value = '';
    document.getElementById('vf_sub_district_hq_distance').value = '';
    document.getElementById('vf_district_hq_distance').value = '';
    document.getElementById('vf_nearest_town_name').value = '';
    document.getElementById('vf_nearest_town_distance').value = '';
}

function editVillage(v) {
    document.getElementById('form_village_id').value = v.id || '0';
    document.getElementById('v_modal_title_action').innerText = 'Edit Village: ' + (v.village_name || '');
    document.getElementById('vf_village_name').value = v.village_name || '';
    document.getElementById('vf_village_code').value = v.village_code || '';
    document.getElementById('vf_district_name').value = v.district_name || '';
    document.getElementById('vf_sub_district_name').value = v.sub_district_name || v.cd_block_name || '';
    document.getElementById('vf_gram_panchayat_name').value = v.gram_panchayat_name || '';
    document.getElementById('vf_households').value = v.households || '';
    document.getElementById('vf_population').value = v.population || '';
    document.getElementById('vf_male').value = v.male || '';
    document.getElementById('vf_female').value = v.female || '';
    document.getElementById('vf_area_hectares').value = v.area_hectares || '';
    document.getElementById('vf_sc_population').value = v.sc_population || '';
    document.getElementById('vf_st_population').value = v.st_population || '';
    document.getElementById('vf_sub_district_hq_distance').value = v.sub_district_hq_distance || '';
    document.getElementById('vf_district_hq_distance').value = v.district_hq_distance || '';
    document.getElementById('vf_nearest_town_name').value = v.nearest_town_name || '';
    document.getElementById('vf_nearest_town_distance').value = v.nearest_town_distance || '';

    const modal = new bootstrap.Modal(document.getElementById('villageModal'));
    modal.show();
}

function viewVillageDetails(v) {
    const body = document.getElementById('village_details_body');
    const sexRatio = v.sex_ratio || (v.male > 0 ? Math.round((v.female / v.male) * 1000) : '—');
    
    let html = `
        <div class="row g-3">
            <div class="col-12 pb-2 border-bottom">
                <h4 class="fw-bold text-dark mb-1">${v.village_name}</h4>
                <div class="text-muted small">
                    District: <strong>${v.district_name}</strong> | Block: <strong>${v.sub_district_name || v.cd_block_name || '—'}</strong> | GP: <strong>${v.gram_panchayat_name || '—'}</strong> | Code: <strong>${v.village_code || '—'}</strong>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 text-center">
                    <div class="text-muted small">Total Population</div>
                    <div class="fs-4 fw-bold text-success">${Number(v.population || 0).toLocaleString()}</div>
                    <div class="small text-muted">♂ ${Number(v.male || 0).toLocaleString()} | ♀ ${Number(v.female || 0).toLocaleString()}</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 text-center">
                    <div class="text-muted small">Sex Ratio & Households</div>
                    <div class="fs-4 fw-bold text-primary">${sexRatio} ♀ / 1k ♂</div>
                    <div class="small text-muted">${Number(v.households || 0).toLocaleString()} Households</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="p-3 bg-light rounded-3 text-center">
                    <div class="text-muted small">Area (Hectares)</div>
                    <div class="fs-4 fw-bold text-dark">${v.area_hectares > 0 ? Number(v.area_hectares).toLocaleString() + ' Ha' : '—'}</div>
                    <div class="small text-muted">Census 2011 Land Area</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="card border p-3 h-100">
                    <h6 class="fw-bold text-warning mb-2"><i class="fas fa-users-line me-1"></i> Caste Demographics</h6>
                    <ul class="list-unstyled mb-0 small">
                        <li><strong>Scheduled Caste (SC):</strong> ${Number(v.sc_population || 0).toLocaleString()}</li>
                        <li><strong>Scheduled Tribe (ST):</strong> ${Number(v.st_population || 0).toLocaleString()}</li>
                    </ul>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border p-3 h-100">
                    <h6 class="fw-bold text-primary mb-2"><i class="fas fa-location-dot me-1"></i> Connectivity & Distances</h6>
                    <ul class="list-unstyled mb-0 small">
                        <li><strong>Block Headquarter:</strong> ${v.sub_district_hq_distance ? v.sub_district_hq_distance + ' km' : '—'}</li>
                        <li><strong>District Headquarter:</strong> ${v.district_hq_distance ? v.district_hq_distance + ' km' : '—'}</li>
                        <li><strong>Nearest Town (${v.nearest_town_name || 'Town'}):</strong> ${v.nearest_town_distance ? v.nearest_town_distance + ' km' : '—'}</li>
                    </ul>
                </div>
            </div>
        </div>
    `;
    body.innerHTML = html;
    const modal = new bootstrap.Modal(document.getElementById('villageDetailsModal'));
    modal.show();
}
</script>

</body>
</html>
