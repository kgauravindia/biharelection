<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// AJAX Endpoint: Dynamic Blocks
if (isset($_GET['ajax']) && $conn) {
    header('Content-Type: application/json');
    $ajaxAction = sanitize($_GET['ajax']);
    $reqDistrict = sanitize($_GET['district'] ?? '');
    $dSlug = slugify($reqDistrict);

    if ($ajaxAction === 'get_blocks') {
        if (!empty($reqDistrict)) {
            $stmt = $conn->prepare("
                SELECT DISTINCT block FROM (
                    SELECT block FROM panchayats WHERE (district = ? OR district_slug = ?) AND block != ''
                    UNION
                    SELECT block FROM panchayat_samiti WHERE (district = ? OR district_slug = ?) AND block != ''
                ) as u_blocks ORDER BY block ASC
            ");
            if ($stmt) {
                $stmt->bind_param("ssss", $reqDistrict, $dSlug, $reqDistrict, $dSlug);
                $stmt->execute();
                $res = $stmt->get_result();
                $blocks = [];
                while ($r = $res->fetch_assoc()) {
                    $blocks[] = $r['block'];
                }
                natcasesort($blocks);
                echo json_encode(['success' => true, 'blocks' => array_values($blocks)]);
                exit;
            }
        }
        echo json_encode(['success' => true, 'blocks' => []]);
        exit;
    }
}

// Standard Bihar Reservation Categories
$reservation_categories = [
    'सामान्य वर्ग' => 'सामान्य वर्ग (General / Unreserved)',
    'पिछड़ा वर्ग अनुसूची-I' => 'पिछड़ा वर्ग अनुसूची-I (EBC / BC-1)',
    'पिछड़ा वर्ग अनुसूची-II' => 'पिछड़ा वर्ग अनुसूची-II (BC / BC-2)',
    'अनुसूचित जाति' => 'अनुसूचित जाति (SC)',
    'अनुसूचित जनजाति' => 'अनुसूचित जनजाति (ST)'
];

// Handle Add POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_samiti' && $conn) {
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $pramukh = sanitize($_POST['pramukh_name'] ?? '');
    $upPramukh = sanitize($_POST['up_pramukh_name'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $dSlug = slugify($district);

    if (!empty($district) && !empty($block) && (!empty($pramukh) || !empty($upPramukh))) {
        $stmt = $conn->prepare("INSERT INTO `panchayat_samiti` (`district`, `district_slug`, `block`, `pramukh_name`, `up_pramukh_name`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssss", $district, $dSlug, $block, $pramukh, $upPramukh, $gender, $category, $mobile, $address, $tenure);
            if ($stmt->execute()) {
                $message = "Panchayat Samiti record for Block '<strong>" . htmlspecialchars($block) . "</strong>' added successfully.";
            } else {
                $error = "Error adding record: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in District, Block, and at least Pramukh or Up-Pramukh name.";
    }
}

// Handle Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_samiti' && $conn) {
    $edit_id = (int)($_POST['id'] ?? 0);
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $pramukh = sanitize($_POST['pramukh_name'] ?? '');
    $upPramukh = sanitize($_POST['up_pramukh_name'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $dSlug = slugify($district);

    if ($edit_id > 0 && !empty($district) && !empty($block)) {
        $stmt = $conn->prepare("UPDATE `panchayat_samiti` SET `district` = ?, `district_slug` = ?, `block` = ?, `pramukh_name` = ?, `up_pramukh_name` = ?, `gender` = ?, `category` = ?, `mobile` = ?, `address` = ?, `tenure` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssssssi", $district, $dSlug, $block, $pramukh, $upPramukh, $gender, $category, $mobile, $address, $tenure, $edit_id);
            if ($stmt->execute()) {
                $message = "Panchayat Samiti record for Block '<strong>" . htmlspecialchars($block) . "</strong>' updated successfully.";
            } else {
                $error = "Error updating record: " . $conn->error;
            }
        }
    } else {
        $error = "Invalid record ID or missing district/block.";
    }
}

// Handle Delete POST
if (isset($_GET['delete_id']) && $conn) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `panchayat_samiti` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Panchayat Samiti record deleted successfully.";
            } else {
                $error = "Error deleting record.";
            }
        }
    }
}

// Filter parameters
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';
$filter_category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$samitis = [];
$total_rows = 0;
$total_overall_samitis = 0;
$total_districts_count = 0;
$total_pramukhs_count = 0;
$total_up_pramukhs_count = 0;

$district_counts = [];
$block_counts = [];

$districts = DataProvider::getDistricts() ?: [];
usort($districts, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

if ($conn) {
    // 1. Overall Platform Stats
    $stat_res = $conn->query("SELECT 
        COUNT(*) as total_s, 
        COUNT(DISTINCT district) as total_d,
        SUM(CASE WHEN pramukh_name IS NOT NULL AND pramukh_name != '' AND pramukh_name != '—' THEN 1 ELSE 0 END) as total_p,
        SUM(CASE WHEN up_pramukh_name IS NOT NULL AND up_pramukh_name != '' AND up_pramukh_name != '—' THEN 1 ELSE 0 END) as total_up
        FROM `panchayat_samiti`");
    if ($stat_res && $row = $stat_res->fetch_assoc()) {
        $total_overall_samitis = (int)$row['total_s'];
        $total_districts_count = (int)$row['total_d'];
        $total_pramukhs_count = (int)$row['total_p'];
        $total_up_pramukhs_count = (int)$row['total_up'];
    }

    // 2. District-Wise Counts
    $d_res = $conn->query("SELECT district, COUNT(*) as samiti_count, 
        SUM(CASE WHEN pramukh_name IS NOT NULL AND pramukh_name != '' AND pramukh_name != '—' THEN 1 ELSE 0 END) as pramukh_count
        FROM `panchayat_samiti` WHERE district IS NOT NULL AND district != '' GROUP BY district ORDER BY district ASC");
    if ($d_res) {
        while ($row = $d_res->fetch_assoc()) {
            $district_counts[$row['district']] = $row;
        }
        ksort($district_counts, SORT_NATURAL | SORT_FLAG_CASE);
    }

    // 3. Block-Wise Counts for selected district
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $b_res = $conn->query("SELECT block, pramukh_name, up_pramukh_name FROM `panchayat_samiti` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND block != '' ORDER BY block ASC");
        if ($b_res) {
            while ($row = $b_res->fetch_assoc()) {
                $block_counts[$row['block']] = $row;
            }
            ksort($block_counts, SORT_NATURAL | SORT_FLAG_CASE);
        }
    }

    // 4. Main Query with Dynamic Filtering
    $where = "1=1";
    if (!empty($search)) {
        $esc_q = $conn->real_escape_string($search);
        $where .= " AND (`pramukh_name` LIKE '%$esc_q%' OR `up_pramukh_name` LIKE '%$esc_q%' OR `block` LIKE '%$esc_q%' OR `district` LIKE '%$esc_q%' OR `mobile` LIKE '%$esc_q%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $where .= " AND (`district` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_block)) {
        $esc_b = $conn->real_escape_string($filter_block);
        $where .= " AND `block` = '$esc_b'";
    }
    if (!empty($filter_category)) {
        $esc_c = $conn->real_escape_string($filter_category);
        $where .= " AND `category` = '$esc_c'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `panchayat_samiti` WHERE $where");
    if ($c_res) $total_rows = (int)$c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `panchayat_samiti` WHERE $where ORDER BY `district` ASC, `block` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $samitis[] = $r;
        }
    }
}

$districts_zero_samitis = [];
foreach ($districts as $d) {
    $dName = $d['name'];
    if (empty($district_counts[$dName])) {
        $districts_zero_samitis[] = $dName;
    }
}
natcasesort($districts_zero_samitis);

$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panchayat Samiti (Block Pramukhs) — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .stat-card-custom {
            background: #fff;
            border-radius: 12px;
            padding: 1.15rem;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 2px 8px rgba(0,0,0,0.03);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .stat-card-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(0,0,0,0.06);
        }
        .stat-card-icon {
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            font-size: 1.25rem;
        }
        .district-pill-btn {
            transition: all 0.15s ease-in-out;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .district-pill-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .block-pill-btn {
            transition: all 0.15s ease-in-out;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .block-pill-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Panchayat Samiti (Tier-2 Block Pramukhs)</h1>
                <p class="text-muted mb-0">Elected Block Panchayat Samiti Pramukhs (प्रखंड प्रमुख) and Up-Pramukhs across Bihar.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-success fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addSamitiModal">
                    <i class="fas fa-plus-circle me-1"></i> Add Block Pramukh
                </button>
                <a href="bulk-upload.php?type=panchayat_samiti" class="btn btn-danger fw-semibold rounded-3 shadow-sm">
                    <i class="fas fa-cloud-arrow-up me-1"></i> Bulk Upload CSV
                </a>
                <button type="button" class="btn btn-outline-dark fw-semibold rounded-3 shadow-sm bg-white" data-bs-toggle="modal" data-bs-target="#matrixModal">
                    <i class="fas fa-table-cells me-1 text-success"></i> Full 38 Districts Matrix
                </button>
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

        <?php if (!empty($districts_zero_samitis)): ?>
            <!-- Zero Samiti Warning Card -->
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-triangle-exclamation text-danger fs-4 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">Data Gap & Missing Report: <?php echo count($districts_zero_samitis); ?> Districts Have 0 Panchayat Samiti Records</h6>
                        <p class="small text-muted mb-2">The following districts currently do not have Panchayat Samiti data seeded:</p>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($districts_zero_samitis as $zd): ?>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                    <i class="fas fa-ban me-1"></i> <?php echo htmlspecialchars($zd); ?> (0)
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Block Samitis</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_overall_samitis); ?></h3>
                        <small class="text-success fw-semibold"><i class="fas fa-award me-1"></i>534 CD Blocks</small>
                    </div>
                    <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-award"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Pramukhs Assigned</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_pramukhs_count); ?></h3>
                        <small class="text-primary fw-semibold"><?php echo number_format($total_up_pramukhs_count); ?> Up-Pramukhs</small>
                    </div>
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-user-tie"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Districts Covered</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo count($district_counts); ?> / 38</h3>
                        <small class="text-muted fw-semibold">Bihar Districts</small>
                    </div>
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Current Filter Results</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_rows); ?></h3>
                        <small class="text-info fw-semibold">
                            <?php if (!empty($filter_district) || !empty($filter_block) || !empty($search)): ?>
                                Filter active
                            <?php else: ?>
                                All Records
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="stat-card-icon bg-info bg-opacity-10 text-info">
                        <i class="fas fa-filter"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- District-Wise Count Breakdown Card -->
        <div class="section-card mb-4">
            <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-city me-2 text-success"></i> District-Wise Panchayat Samiti Count (38 Districts)
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark small">Click to filter</span>
                </div>
                <?php if (!empty($filter_district)): ?>
                    <a href="panchayat-samiti.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small">
                        <i class="fas fa-times me-1"></i> Clear District Filter
                    </a>
                <?php endif; ?>
            </div>
            <div class="section-card-body p-3">
                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                    <a href="panchayat-samiti.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" 
                       class="btn btn-sm <?php echo empty($filter_district) ? 'btn-success shadow-sm fw-bold' : 'btn-light border text-dark'; ?> rounded-pill py-1 px-3 district-pill-btn">
                        <span>All 38 Districts</span>
                        <span class="badge <?php echo empty($filter_district) ? 'bg-white text-success' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?php echo number_format($total_overall_samitis); ?></span>
                    </a>
                    
                    <?php foreach ($districts as $d): 
                        $dName = $d['name'];
                        $dInfo = $district_counts[$dName] ?? null;
                        $dCount = $dInfo['samiti_count'] ?? 0;
                        $isActive = (strcasecmp($filter_district, $dName) === 0);
                        $isZero = ($dCount === 0);
                    ?>
                        <a href="panchayat-samiti.php?district=<?php echo urlencode($dName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo $isActive ? 'btn-success shadow-sm fw-bold' : ($isZero ? 'btn-outline-danger' : 'btn-light border text-dark'); ?> rounded-pill py-1 px-2.5 district-pill-btn"
                           title="<?php echo htmlspecialchars($dName); ?>: <?php echo $dCount; ?> Block Samitis">
                            <?php if ($isZero): ?><i class="fas fa-triangle-exclamation text-danger me-1"></i><?php endif; ?>
                            <span><?php echo htmlspecialchars($dName); ?></span>
                            <span class="badge <?php echo $isActive ? 'bg-white text-success' : ($isZero ? 'bg-danger text-white' : 'bg-success-subtle text-success border border-success-subtle'); ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                <?php echo number_format($dCount); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Block-Wise Count Breakdown (Visible when a District is selected) -->
        <?php if (!empty($filter_district) && !empty($block_counts)): ?>
            <div class="section-card mb-4 border-success border-opacity-25 bg-success bg-opacity-10 bg-opacity-5">
                <div class="section-card-header bg-success bg-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold mb-0 text-success">
                            <i class="fas fa-cubes me-2"></i> Blocks in <?php echo htmlspecialchars($filter_district); ?> (<?php echo count($block_counts); ?> Blocks)
                        </h6>
                    </div>
                    <?php if (!empty($filter_block)): ?>
                        <a href="panchayat-samiti.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-success rounded-pill py-0.5 px-2.5 small bg-white">
                            <i class="fas fa-times me-1"></i> Clear Block Filter
                        </a>
                    <?php endif; ?>
                </div>
                <div class="section-card-body p-3">
                    <div class="d-flex flex-wrap gap-1.5 align-items-center">
                        <a href="panchayat-samiti.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo empty($filter_block) ? 'btn-dark shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-3 block-pill-btn">
                            <span>All Blocks in <?php echo htmlspecialchars($filter_district); ?></span>
                        </a>

                        <?php foreach ($block_counts as $bName => $bInfo): ?>
                            <?php $isBlockActive = (strcasecmp($filter_block, $bName) === 0); ?>
                            <a href="panchayat-samiti.php?district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($bName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                               class="btn btn-sm <?php echo $isBlockActive ? 'btn-success shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-2.5 block-pill-btn">
                                <span><?php echo htmlspecialchars($bName); ?></span>
                                <?php if (!empty($bInfo['pramukh_name'])): ?>
                                    <i class="fas fa-check text-success ms-1 small"></i>
                                <?php else: ?>
                                    <i class="fas fa-clock text-muted ms-1 small"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Card -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="panchayat-samiti.php" class="row g-2 align-items-center">
                    <div class="col-lg-4 col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search Pramukh, Up-Pramukh, Block..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <select name="district" class="form-select" onchange="this.form.submit()">
                            <option value="">All 38 Districts (<?php echo number_format($total_overall_samitis); ?>)</option>
                            <?php foreach ($district_counts as $dName => $dInfo): ?>
                                <option value="<?php echo htmlspecialchars($dName); ?>" <?php echo (strcasecmp($filter_district, $dName) === 0) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dName); ?> (<?php echo number_format($dInfo['samiti_count']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-3 col-md-6">
                        <select name="block" class="form-select" onchange="this.form.submit()" <?php echo empty($filter_district) ? 'disabled' : ''; ?>>
                            <option value="">
                                <?php echo !empty($filter_district) ? 'All Blocks in ' . htmlspecialchars($filter_district) : 'Select District First'; ?>
                            </option>
                            <?php if (!empty($block_counts)): ?>
                                <?php foreach ($block_counts as $bName => $bInfo): ?>
                                    <option value="<?php echo htmlspecialchars($bName); ?>" <?php echo (strcasecmp($filter_block, $bName) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bName); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-6 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="panchayat-samiti.php" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-award me-2 text-success"></i> Block Panchayat Samiti Leadership Roster
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark small"><?php echo number_format($total_rows); ?> Records</span>
                </div>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 25%;">Block & District</th>
                            <th style="width: 22%;">Block Pramukh (प्रमुख)</th>
                            <th style="width: 22%;">Up-Pramukh (उप-प्रमुख)</th>
                            <th style="width: 14%;">Reservation / Gender</th>
                            <th class="text-end" style="width: 12%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($samitis)): ?>
                            <?php $sno = $offset + 1; foreach ($samitis as $s): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong class="text-dark fs-6"><?php echo htmlspecialchars($s['block']); ?></strong>
                                        <div class="small text-muted">
                                            <i class="fas fa-location-dot me-1 text-danger"></i> <?php echo htmlspecialchars($s['district']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['pramukh_name']) && $s['pramukh_name'] !== '—'): ?>
                                            <div class="fw-bold text-dark">
                                                <i class="fas fa-user-tie text-success me-1"></i> <?php echo htmlspecialchars($s['pramukh_name']); ?>
                                            </div>
                                            <?php if (!empty($s['mobile'])): ?>
                                                <small class="text-muted font-monospace"><i class="fas fa-phone text-muted me-1"></i><?php echo htmlspecialchars($s['mobile']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fas fa-triangle-exclamation me-1"></i>Vacant / Unrecorded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['up_pramukh_name']) && $s['up_pramukh_name'] !== '—'): ?>
                                            <div class="fw-semibold text-dark">
                                                <i class="fas fa-user text-primary me-1"></i> <?php echo htmlspecialchars($s['up_pramukh_name']); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted small">Not recorded</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($s['category'] ?: 'सामान्य वर्ग'); ?></span>
                                        <div class="small text-muted mt-0.5"><?php echo htmlspecialchars($s['gender'] ?: 'Male'); ?></div>
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 edit-samiti-btn" 
                                                data-id="<?php echo $s['id']; ?>"
                                                data-district="<?php echo htmlspecialchars($s['district']); ?>"
                                                data-block="<?php echo htmlspecialchars($s['block']); ?>"
                                                data-pramukh="<?php echo htmlspecialchars($s['pramukh_name'] ?? ''); ?>"
                                                data-uppramukh="<?php echo htmlspecialchars($s['up_pramukh_name'] ?? ''); ?>"
                                                data-gender="<?php echo htmlspecialchars($s['gender'] ?? 'Male'); ?>"
                                                data-category="<?php echo htmlspecialchars($s['category'] ?? 'सामान्य वर्ग'); ?>"
                                                data-mobile="<?php echo htmlspecialchars($s['mobile'] ?? ''); ?>"
                                                data-address="<?php echo htmlspecialchars($s['address'] ?? ''); ?>"
                                                data-tenure="<?php echo htmlspecialchars($s['tenure'] ?? '2021-2026'); ?>"
                                                title="Edit Record">
                                            <i class="fas fa-pen-to-square me-1"></i> Edit
                                        </button>
                                        <a href="panchayat-samiti.php?delete_id=<?php echo $s['id']; ?><?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?>" 
                                           class="btn btn-sm btn-outline-danger rounded-pill px-2"
                                           onclick="return confirm('Are you sure you want to delete this Panchayat Samiti record?');"
                                           title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fs-1 d-block mb-2 text-muted"></i>
                                    No Panchayat Samiti records found matching current criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_rows); ?> of <?php echo number_format($total_rows); ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>">Prev</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<!-- Modal: Add Samiti -->
<div class="modal fade" id="addSamitiModal" tabindex="-1" aria-labelledby="addSamitiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="panchayat-samiti.php<?php echo !empty($filter_district) ? '?district=' . urlencode($filter_district) : ''; ?>">
                <input type="hidden" name="action" value="add_samiti">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="addSamitiModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-award me-2 text-success"></i> Add Block Panchayat Samiti Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="add_ps_district" class="form-select" required onchange="fetchPsBlocks(this.value, document.getElementById('add_ps_block'))">
                                <option value="">Select District</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">CD Block <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="add_ps_block_toggle" onclick="togglePsBlockInput('add')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="add_ps_block_select_wrap">
                                <select name="block" id="add_ps_block" class="form-select" required>
                                    <option value="">Select District First</option>
                                </select>
                            </div>
                            <div id="add_ps_block_custom_wrap" class="d-none">
                                <input type="text" id="add_ps_block_custom" class="form-control" placeholder="Enter block name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Block Pramukh Name (प्रखंड प्रमुख)</label>
                            <input type="text" name="pramukh_name" class="form-control" placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Up-Pramukh Name (उप-प्रमुख)</label>
                            <input type="text" name="up_pramukh_name" class="form-control" placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category</label>
                            <select name="category" class="form-select">
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Contact Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Address Details</label>
                            <input type="text" name="address" class="form-control" placeholder="Block office / village address">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Samiti Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Samiti -->
<div class="modal fade" id="editSamitiModal" tabindex="-1" aria-labelledby="editSamitiModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="panchayat-samiti.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" id="editSamitiForm">
                <input type="hidden" name="action" value="edit_samiti">
                <input type="hidden" name="id" id="edit_ps_id">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="editSamitiModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-pen-to-square me-2 text-primary"></i> Edit Panchayat Samiti Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="edit_ps_district" class="form-select" required onchange="fetchPsBlocks(this.value, document.getElementById('edit_ps_block'))">
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">CD Block <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="edit_ps_block_toggle" onclick="togglePsBlockInput('edit')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="edit_ps_block_select_wrap">
                                <select name="block" id="edit_ps_block" class="form-select" required>
                                    <option value="">Select District First</option>
                                </select>
                            </div>
                            <div id="edit_ps_block_custom_wrap" class="d-none">
                                <input type="text" id="edit_ps_block_custom" class="form-control" placeholder="Enter block name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Block Pramukh Name (प्रखंड प्रमुख)</label>
                            <input type="text" name="pramukh_name" id="edit_ps_pramukh" class="form-control" placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Up-Pramukh Name (उप-प्रमुख)</label>
                            <input type="text" name="up_pramukh_name" id="edit_ps_uppramukh" class="form-control" placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category</label>
                            <select name="category" id="edit_ps_category" class="form-select">
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender</label>
                            <select name="gender" id="edit_ps_gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Contact Mobile Number</label>
                            <input type="text" name="mobile" id="edit_ps_mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" id="edit_ps_tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Address Details</label>
                            <input type="text" name="address" id="edit_ps_address" class="form-control" placeholder="Block office / village address">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Complete 38 Districts Matrix -->
<div class="modal fade" id="matrixModal" tabindex="-1" aria-labelledby="matrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="matrixModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-table-cells me-2 text-success"></i> Complete Bihar Panchayat Samiti: 38 District Matrix
                    </h5>
                    <small class="text-muted">Total <?php echo number_format($total_overall_samitis); ?> Block Samitis across Bihar State</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 35%;">District</th>
                                <th class="text-center" style="width: 25%;">Total Block Samitis</th>
                                <th class="text-center" style="width: 20%;">Pramukhs Assigned</th>
                                <th class="text-end" style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; foreach ($districts as $d): 
                                $dName = $d['name'];
                                $dInfo = $district_counts[$dName] ?? null;
                                $sCount = $dInfo['samiti_count'] ?? 0;
                                $pCount = $dInfo['pramukh_count'] ?? 0;
                                $isZero = ($sCount === 0);
                            ?>
                                <tr class="<?php echo $isZero ? 'table-danger table-opacity-10' : ''; ?>">
                                    <td class="text-muted small"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong class="text-dark"><?php echo htmlspecialchars($dName); ?></strong>
                                        <?php if ($isZero): ?>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle ms-2 small">0 Records</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo number_format($sCount); ?> Blocks</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $isZero ? 'bg-danger' : 'bg-success text-white'; ?> rounded-pill px-3 py-1 font-monospace fs-7">
                                            <?php echo number_format($pCount); ?> Assigned
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!$isZero): ?>
                                            <a href="panchayat-samiti.php?district=<?php echo urlencode($dName); ?>" class="btn btn-sm btn-outline-success rounded-pill px-3">
                                                <i class="fas fa-filter me-1"></i> View District
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">Needs Seeding</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
function fetchPsBlocks(district, blockSelectEl, selectedBlock = '', callback = null) {
    if (!blockSelectEl) return;
    blockSelectEl.innerHTML = '<option value="">Loading blocks...</option>';
    blockSelectEl.disabled = true;

    if (!district) {
        blockSelectEl.innerHTML = '<option value="">Select District First</option>';
        blockSelectEl.disabled = false;
        if (callback) callback();
        return;
    }

    fetch(`panchayat-samiti.php?ajax=get_blocks&district=${encodeURIComponent(district)}`)
        .then(res => res.json())
        .then(data => {
            blockSelectEl.innerHTML = '<option value="">Select Block</option>';
            if (data.blocks && data.blocks.length > 0) {
                let found = false;
                data.blocks.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b;
                    opt.textContent = b;
                    if (selectedBlock && b.trim().toLowerCase() === selectedBlock.trim().toLowerCase()) {
                        opt.selected = true;
                        found = true;
                    }
                    blockSelectEl.appendChild(opt);
                });
                if (selectedBlock && !found) {
                    const opt = document.createElement('option');
                    opt.value = selectedBlock;
                    opt.textContent = selectedBlock + ' (Current)';
                    opt.selected = true;
                    blockSelectEl.appendChild(opt);
                }
            } else {
                blockSelectEl.innerHTML = '<option value="">No blocks found</option>';
                if (selectedBlock) {
                    const opt = document.createElement('option');
                    opt.value = selectedBlock;
                    opt.textContent = selectedBlock;
                    opt.selected = true;
                    blockSelectEl.appendChild(opt);
                }
            }
            blockSelectEl.disabled = false;
            if (callback) callback();
        })
        .catch(err => {
            console.error('Error fetching blocks:', err);
            blockSelectEl.innerHTML = '<option value="">Error loading blocks</option>';
            blockSelectEl.disabled = false;
            if (callback) callback();
        });
}

function togglePsBlockInput(prefix) {
    const selWrap = document.getElementById(`${prefix}_ps_block_select_wrap`);
    const customWrap = document.getElementById(`${prefix}_ps_block_custom_wrap`);
    const sel = document.getElementById(`${prefix}_ps_block`);
    const custom = document.getElementById(`${prefix}_ps_block_custom`);
    const toggleBtn = document.getElementById(`${prefix}_ps_block_toggle`);

    if (customWrap.classList.contains('d-none')) {
        customWrap.classList.remove('d-none');
        selWrap.classList.add('d-none');
        custom.name = 'block';
        custom.required = true;
        custom.value = sel.value || '';
        sel.removeAttribute('name');
        sel.required = false;
        toggleBtn.innerHTML = '<i class="fas fa-list me-1"></i>Select from list';
    } else {
        customWrap.classList.add('d-none');
        selWrap.classList.remove('d-none');
        sel.name = 'block';
        sel.required = true;
        custom.removeAttribute('name');
        custom.required = false;
        toggleBtn.innerHTML = '<i class="fas fa-pen-to-square me-1"></i>Custom';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const editModalEl = document.getElementById('editSamitiModal');
    const addDistrictEl = document.getElementById('add_ps_district');
    const addBlockEl = document.getElementById('add_ps_block');

    if (addDistrictEl && addDistrictEl.value && addBlockEl) {
        fetchPsBlocks(addDistrictEl.value, addBlockEl);
    }

    if (editModalEl) {
        const editModal = new bootstrap.Modal(editModalEl);
        document.querySelectorAll('.edit-samiti-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const districtVal = this.dataset.district || '';
                const blockVal = this.dataset.block || '';

                document.getElementById('edit_ps_id').value = this.dataset.id || '';
                document.getElementById('edit_ps_district').value = districtVal;
                document.getElementById('edit_ps_pramukh').value = this.dataset.pramukh || '';
                document.getElementById('edit_ps_uppramukh').value = this.dataset.uppramukh || '';
                document.getElementById('edit_ps_gender').value = this.dataset.gender || 'Male';
                document.getElementById('edit_ps_category').value = this.dataset.category || 'सामान्य वर्ग';
                document.getElementById('edit_ps_mobile').value = this.dataset.mobile || '';
                document.getElementById('edit_ps_address').value = this.dataset.address || '';
                document.getElementById('edit_ps_tenure').value = this.dataset.tenure || '2021-2026';

                const editBlockEl = document.getElementById('edit_ps_block');
                fetchPsBlocks(districtVal, editBlockEl, blockVal);

                editModal.show();
            });
        });
    }
});
</script>

</body>
</html>
