<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// AJAX Endpoint: Dynamic Blocks & Panchayats
if (isset($_GET['ajax']) && $conn) {
    header('Content-Type: application/json');
    $ajaxAction = sanitize($_GET['ajax']);
    $reqDistrict = sanitize($_GET['district'] ?? '');
    $reqBlock = sanitize($_GET['block'] ?? '');
    $dSlug = slugify($reqDistrict);

    if ($ajaxAction === 'get_blocks') {
        if (!empty($reqDistrict)) {
            $stmt = $conn->prepare("
                SELECT DISTINCT block FROM (
                    SELECT block FROM panchayats WHERE (district = ? OR district_slug = ?) AND block != ''
                    UNION
                    SELECT block FROM mukhiyas WHERE (district = ? OR district_slug = ?) AND block != ''
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
                echo json_encode(['success' => true, 'blocks' => $blocks]);
                exit;
            }
        }
        echo json_encode(['success' => true, 'blocks' => []]);
        exit;
    }

    if ($ajaxAction === 'get_panchayats') {
        if (!empty($reqDistrict) && !empty($reqBlock)) {
            $stmt = $conn->prepare("
                SELECT DISTINCT panchayat FROM (
                    SELECT panchayat_name as panchayat FROM panchayats WHERE (district = ? OR district_slug = ?) AND block = ? AND panchayat_name != ''
                    UNION
                    SELECT panchayat FROM mukhiyas WHERE (district = ? OR district_slug = ?) AND block = ? AND panchayat != ''
                ) as u_panchayats ORDER BY panchayat ASC
            ");
            if ($stmt) {
                $stmt->bind_param("ssssss", $reqDistrict, $dSlug, $reqBlock, $reqDistrict, $dSlug, $reqBlock);
                $stmt->execute();
                $res = $stmt->get_result();
                $panchayats = [];
                while ($r = $res->fetch_assoc()) {
                    $panchayats[] = $r['panchayat'];
                }
                echo json_encode(['success' => true, 'panchayats' => $panchayats]);
                exit;
            }
        }
        echo json_encode(['success' => true, 'panchayats' => []]);
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
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_mukhiya' && $conn) {
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $d_slug = slugify($district);

    if (!empty($c_name) && !empty($district) && !empty($panchayat)) {
        $stmt = $conn->prepare("INSERT INTO `mukhiyas` (`candidate_name`, `post`, `district`, `district_slug`, `block`, `panchayat`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, 'मुखिया', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssss", $c_name, $district, $d_slug, $block, $panchayat, $gender, $category, $mobile, $address, $tenure);
            if ($stmt->execute()) {
                $message = "New Mukhiya record for '" . htmlspecialchars($c_name) . "' (" . htmlspecialchars($panchayat) . ") added successfully.";
            } else {
                $error = "Error adding Mukhiya: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields (Candidate Name, District, and Panchayat).";
    }
}

// Handle Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_mukhiya' && $conn) {
    $edit_id = (int)($_POST['id'] ?? 0);
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $d_slug = slugify($district);

    if ($edit_id > 0 && !empty($c_name)) {
        $stmt = $conn->prepare("UPDATE `mukhiyas` SET `candidate_name` = ?, `mobile` = ?, `category` = ?, `gender` = ?, `tenure` = ?, `district` = ?, `district_slug` = ?, `block` = ?, `panchayat` = ?, `address` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssssssi", $c_name, $mobile, $category, $gender, $tenure, $district, $d_slug, $block, $panchayat, $address, $edit_id);
            if ($stmt->execute()) {
                $message = "Mukhiya record for '" . htmlspecialchars($c_name) . "' (" . htmlspecialchars($panchayat) . ") updated successfully.";
            } else {
                $error = "Error updating Mukhiya: " . $conn->error;
            }
        }
    } else {
        $error = "Please enter a valid candidate name.";
    }
}

// Handle Delete
if (isset($_GET['delete_id']) && $conn) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `mukhiyas` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Mukhiya record deleted successfully.";
            } else {
                $error = "Error deleting record.";
            }
        }
    }
}

// Search & Filter Parameters
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';
$filter_panchayat = isset($_GET['panchayat']) ? sanitize($_GET['panchayat']) : '';
$filter_category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$mukhiyas = [];
$total_rows = 0;
$total_overall_mukhiyas = 0;
$total_districts_count = 0;
$total_blocks_count = 0;

$district_counts = [];
$block_counts = [];
$panchayat_filter_list = [];

if ($conn) {
    // 1. Overall Platform Stats
    $stat_res = $conn->query("SELECT COUNT(*) as total_m, COUNT(DISTINCT district) as total_d, COUNT(DISTINCT CONCAT(district, '___', block)) as total_b FROM `mukhiyas`");
    if ($stat_res && $row = $stat_res->fetch_assoc()) {
        $total_overall_mukhiyas = (int)$row['total_m'];
        $total_districts_count = (int)$row['total_d'];
        $total_blocks_count = (int)$row['total_b'];
    }

    // 2. District-Wise Counts (All 38 Districts)
    $d_res = $conn->query("SELECT district, COUNT(*) as mukhiya_count, COUNT(DISTINCT block) as block_count FROM `mukhiyas` WHERE district IS NOT NULL AND district != '' GROUP BY district ORDER BY district ASC");
    if ($d_res) {
        while ($row = $d_res->fetch_assoc()) {
            $district_counts[$row['district']] = $row;
        }
        ksort($district_counts, SORT_NATURAL | SORT_FLAG_CASE);
    }

    // 3. Block-Wise Counts for selected district (if district selected)
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $b_res = $conn->query("SELECT block, COUNT(*) as mukhiya_count, COUNT(DISTINCT panchayat) as panchayat_count FROM `mukhiyas` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND block != '' GROUP BY block ORDER BY block ASC");
        if ($b_res) {
            while ($row = $b_res->fetch_assoc()) {
                $block_counts[$row['block']] = $row;
            }
            ksort($block_counts, SORT_NATURAL | SORT_FLAG_CASE);
        }
    }

    // 3b. Panchayats List for selected district & block (for filter bar)
    if (!empty($filter_district) && !empty($filter_block)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $esc_b = $conn->real_escape_string($filter_block);
        $p_res = $conn->query("
            SELECT DISTINCT panchayat FROM (
                SELECT panchayat_name as panchayat FROM panchayats WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND `block` = '$esc_b' AND `panchayat_name` != ''
                UNION
                SELECT panchayat FROM mukhiyas WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND `block` = '$esc_b' AND `panchayat` != ''
            ) as u_p ORDER BY panchayat ASC
        ");
        if ($p_res) {
            while ($row = $p_res->fetch_assoc()) {
                $panchayat_filter_list[] = $row['panchayat'];
            }
            natcasesort($panchayat_filter_list);
        }
    }

    // 4. Main Query with Dynamic Filtering
    $where = "1=1";
    if (!empty($search)) {
        $esc_q = $conn->real_escape_string($search);
        $where .= " AND (`candidate_name` LIKE '%$esc_q%' OR `panchayat` LIKE '%$esc_q%' OR `block` LIKE '%$esc_q%' OR `mobile` LIKE '%$esc_q%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $where .= " AND (`district` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_block)) {
        $esc_b = $conn->real_escape_string($filter_block);
        $where .= " AND `block` = '$esc_b'";
    }
    if (!empty($filter_panchayat)) {
        $esc_p = $conn->real_escape_string($filter_panchayat);
        $where .= " AND `panchayat` = '$esc_p'";
    }
    if (!empty($filter_category)) {
        $esc_c = $conn->real_escape_string($filter_category);
        $where .= " AND `category` = '$esc_c'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `mukhiyas` WHERE $where");
    if ($c_res) $total_rows = (int)$c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `mukhiyas` WHERE $where ORDER BY `district` ASC, `block` ASC, `panchayat` ASC, `candidate_name` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $mukhiyas[] = $r;
        }
    }
}

$total_pages = ceil($total_rows / $limit);
$districts = DataProvider::getDistricts() ?: [];
usort($districts, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gram Panchayat Mukhiyas Directory — Bihar Election Admin</title>
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
        .custom-scroll-container {
            max-height: 240px;
            overflow-y: auto;
            scrollbar-width: thin;
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Gram Panchayat Mukhiyas</h1>
                <p class="text-muted mb-0">Elected Mukhiya governance directory across all 38 districts and 534 blocks of Bihar.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-danger fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addMukhiyaModal">
                    <i class="fas fa-plus-circle me-1"></i> Add New Mukhiya
                </button>
                <a href="bulk-upload.php?type=mukhiyas" class="btn btn-outline-danger fw-semibold rounded-3 shadow-sm bg-white">
                    <i class="fas fa-cloud-arrow-up me-1"></i> Bulk Upload CSV
                </a>
                <button type="button" class="btn btn-outline-dark fw-semibold rounded-3 shadow-sm bg-white" data-bs-toggle="modal" data-bs-target="#matrixModal">
                    <i class="fas fa-table-cells me-1 text-primary"></i> Full District & Block Matrix
                </button>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-check-circle me-2 fs-5 align-middle"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm rounded-3" role="alert">
                <i class="fas fa-exclamation-triangle me-2 fs-5 align-middle"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Elected Mukhiyas</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_overall_mukhiyas); ?></h3>
                        <small class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>38 Districts Covered</small>
                    </div>
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-users-gear"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Districts Matrix</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo count($district_counts); ?> / 38</h3>
                        <small class="text-primary fw-semibold">100% District Coverage</small>
                    </div>
                    <div class="stat-card-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-map-location-dot"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">CD Blocks Represented</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_blocks_count); ?></h3>
                        <small class="text-muted fw-semibold">Across Bihar State</small>
                    </div>
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-cubes-stacked"></i>
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
                                Filter active (<?php echo htmlspecialchars($filter_district ?: ($filter_block ?: 'Search')); ?>)
                            <?php else: ?>
                                Showing all records
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
                        <i class="fas fa-city me-2 text-danger"></i> District-Wise Mukhiya Count (38 Districts)
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark small">Click to filter by District</span>
                </div>
                <?php if (!empty($filter_district)): ?>
                    <a href="mukhiyas.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small">
                        <i class="fas fa-times me-1"></i> Clear District Filter
                    </a>
                <?php endif; ?>
            </div>
            <div class="section-card-body p-3">
                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                    <a href="mukhiyas.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" 
                       class="btn btn-sm <?php echo empty($filter_district) ? 'btn-danger shadow-sm fw-bold' : 'btn-light border text-dark'; ?> rounded-pill py-1 px-3 district-pill-btn">
                        <span>All 38 Districts</span>
                        <span class="badge <?php echo empty($filter_district) ? 'bg-white text-danger' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?php echo number_format($total_overall_mukhiyas); ?></span>
                    </a>
                    
                    <?php foreach ($district_counts as $dName => $dInfo): ?>
                        <?php $isActive = (strcasecmp($filter_district, $dName) === 0); ?>
                        <a href="mukhiyas.php?district=<?php echo urlencode($dName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo $isActive ? 'btn-danger shadow-sm fw-bold' : 'btn-light border text-dark'; ?> rounded-pill py-1 px-2.5 district-pill-btn"
                           title="<?php echo htmlspecialchars($dName); ?>: <?php echo $dInfo['mukhiya_count']; ?> Mukhiyas across <?php echo $dInfo['block_count']; ?> Blocks">
                            <span><?php echo htmlspecialchars($dName); ?></span>
                            <span class="badge <?php echo $isActive ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                <?php echo number_format($dInfo['mukhiya_count']); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Block-Wise Count Breakdown (Visible when a District is selected) -->
        <?php if (!empty($filter_district) && !empty($block_counts)): ?>
            <div class="section-card mb-4 border-danger border-opacity-25 bg-danger bg-opacity-10 bg-opacity-5">
                <div class="section-card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold mb-0 text-danger">
                            <i class="fas fa-cubes me-2"></i> Block-Wise Mukhiya Count in <?php echo htmlspecialchars($filter_district); ?> (<?php echo count($block_counts); ?> Blocks)
                        </h6>
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5">
                            <?php echo number_format($district_counts[$filter_district]['mukhiya_count'] ?? $total_rows); ?> Mukhiyas
                        </span>
                    </div>
                    <?php if (!empty($filter_block)): ?>
                        <a href="mukhiyas.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small bg-white">
                            <i class="fas fa-times me-1"></i> Clear Block Filter
                        </a>
                    <?php endif; ?>
                </div>
                <div class="section-card-body p-3">
                    <div class="d-flex flex-wrap gap-1.5 align-items-center">
                        <a href="mukhiyas.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo empty($filter_block) ? 'btn-dark shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-3 block-pill-btn">
                            <span>All Blocks in <?php echo htmlspecialchars($filter_district); ?></span>
                            <span class="badge <?php echo empty($filter_block) ? 'bg-white text-dark' : 'bg-danger text-white'; ?> rounded-pill ms-1">
                                <?php echo number_format($district_counts[$filter_district]['mukhiya_count'] ?? $total_rows); ?>
                            </span>
                        </a>

                        <?php foreach ($block_counts as $bName => $bInfo): ?>
                            <?php $isBlockActive = (strcasecmp($filter_block, $bName) === 0); ?>
                            <a href="mukhiyas.php?district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($bName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                               class="btn btn-sm <?php echo $isBlockActive ? 'btn-danger shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-2.5 block-pill-btn">
                                <span><?php echo htmlspecialchars($bName); ?></span>
                                <span class="badge <?php echo $isBlockActive ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                    <?php echo number_format($bInfo['mukhiya_count']); ?>
                                </span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Card -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="mukhiyas.php" class="row g-2 align-items-center">
                    <div class="col-lg-3 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search name, mobile..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <select name="district" id="districtSelect" class="form-select" onchange="this.form.submit()">
                            <option value="">All Districts (<?php echo number_format($total_overall_mukhiyas); ?>)</option>
                            <?php foreach ($district_counts as $dName => $dInfo): ?>
                                <option value="<?php echo htmlspecialchars($dName); ?>" <?php echo (strcasecmp($filter_district, $dName) === 0) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dName); ?> (<?php echo number_format($dInfo['mukhiya_count']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <select name="block" class="form-select" onchange="this.form.submit()" <?php echo empty($filter_district) ? 'disabled' : ''; ?>>
                            <option value="">
                                <?php echo !empty($filter_district) ? 'All Blocks' : 'Select District First'; ?>
                            </option>
                            <?php if (!empty($block_counts)): ?>
                                <?php foreach ($block_counts as $bName => $bInfo): ?>
                                    <option value="<?php echo htmlspecialchars($bName); ?>" <?php echo (strcasecmp($filter_block, $bName) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bName); ?> (<?php echo number_format($bInfo['mukhiya_count']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <select name="panchayat" class="form-select" onchange="this.form.submit()" <?php echo empty($filter_block) ? 'disabled' : ''; ?>>
                            <option value="">
                                <?php echo !empty($filter_block) ? 'All Panchayats (' . count($panchayat_filter_list) . ')' : 'Select Block First'; ?>
                            </option>
                            <?php if (!empty($panchayat_filter_list)): ?>
                                <?php foreach ($panchayat_filter_list as $pName): ?>
                                    <option value="<?php echo htmlspecialchars($pName); ?>" <?php echo (strcasecmp($filter_panchayat, $pName) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pName); ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Categories</option>
                            <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                <option value="<?php echo htmlspecialchars($catKey); ?>" <?php echo ($filter_category === $catKey) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($catKey); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-lg-1 col-md-4 d-flex gap-1">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold" title="Apply Filter"><i class="fas fa-filter"></i></button>
                        <a href="mukhiyas.php" class="btn btn-light border" title="Reset All Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-address-book me-2 text-danger"></i> Mukhiya Directory Records
                    </h6>
                    <?php if (!empty($filter_district) || !empty($filter_block) || !empty($filter_panchayat) || !empty($filter_category)): ?>
                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2.5 py-1">
                            <?php 
                            $filterTags = [];
                            if (!empty($filter_district)) $filterTags[] = $filter_district;
                            if (!empty($filter_block)) $filterTags[] = $filter_block;
                            if (!empty($filter_panchayat)) $filterTags[] = $filter_panchayat;
                            if (!empty($filter_category)) $filterTags[] = $filter_category;
                            echo htmlspecialchars(implode(' &rsaquo; ', $filterTags)); 
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#matrixModal">
                        <i class="fas fa-table-cells me-1"></i> Complete Bihar Matrix
                    </button>
                    <span class="badge bg-light text-muted border px-2.5 py-1.5"><?php echo number_format($total_rows); ?> Total Mukhiyas</span>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Mukhiya Name</th>
                            <th>District</th>
                            <th>Block</th>
                            <th>Gram Panchayat</th>
                            <th>Reservation Category</th>
                            <th>Mobile</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($mukhiyas)): ?>
                            <?php foreach ($mukhiyas as $m): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($m['candidate_name']); ?></div>
                                        <?php if (!empty($m['tenure'])): ?>
                                            <span class="badge bg-success-subtle text-success small" style="font-size: 0.68rem;"><?php echo htmlspecialchars($m['tenure']); ?></span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['address'])): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;"><i class="fas fa-map-marker-alt me-1 text-muted"></i><?php echo htmlspecialchars($m['address']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="mukhiyas.php?district=<?php echo urlencode($m['district']); ?>" class="fw-semibold text-dark text-decoration-none" title="Filter by <?php echo htmlspecialchars($m['district']); ?>">
                                            <?php echo htmlspecialchars($m['district']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="mukhiyas.php?district=<?php echo urlencode($m['district']); ?>&block=<?php echo urlencode($m['block']); ?>" class="badge bg-light text-dark border text-decoration-none" title="Filter by <?php echo htmlspecialchars($m['block']); ?>">
                                            <?php echo htmlspecialchars($m['block']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="mukhiyas.php?district=<?php echo urlencode($m['district']); ?>&block=<?php echo urlencode($m['block']); ?>&panchayat=<?php echo urlencode($m['panchayat']); ?>" class="fw-bold text-primary text-decoration-none" title="Filter by <?php echo htmlspecialchars($m['panchayat']); ?>">
                                            <?php echo htmlspecialchars($m['panchayat']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php
                                        $catVal = $m['category'] ?? 'सामान्य वर्ग';
                                        $catBadgeClass = 'bg-secondary text-white';
                                        if ($catVal === 'सामान्य वर्ग') $catBadgeClass = 'bg-dark text-white';
                                        elseif (str_contains($catVal, 'अनुसूची-I')) $catBadgeClass = 'bg-info text-dark';
                                        elseif (str_contains($catVal, 'अनुसूची-II')) $catBadgeClass = 'bg-primary text-white';
                                        elseif (str_contains($catVal, 'जाति')) $catBadgeClass = 'bg-warning text-dark';
                                        elseif (str_contains($catVal, 'जनजाति')) $catBadgeClass = 'bg-danger text-white';
                                        ?>
                                        <span class="badge <?php echo $catBadgeClass; ?> rounded-pill px-2.5 py-1 mb-1 font-monospace" style="font-size:0.75rem;">
                                            <?php echo htmlspecialchars($catVal); ?>
                                        </span>
                                        <div><small class="text-muted"><i class="fas <?php echo ($m['gender'] === 'Female') ? 'fa-female text-danger' : 'fa-male text-primary'; ?> me-1"></i><?php echo htmlspecialchars($m['gender'] ?? 'Male'); ?></small></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['mobile'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($m['mobile']); ?>" class="text-decoration-none fw-semibold text-dark small">
                                                <i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($m['mobile']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-light border text-primary edit-mukhiya-btn" 
                                                data-id="<?php echo $m['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($m['candidate_name']); ?>"
                                                data-mobile="<?php echo htmlspecialchars($m['mobile'] ?? ''); ?>"
                                                data-category="<?php echo htmlspecialchars($m['category'] ?? 'सामान्य वर्ग'); ?>"
                                                data-gender="<?php echo htmlspecialchars($m['gender'] ?? 'Male'); ?>"
                                                data-tenure="<?php echo htmlspecialchars($m['tenure'] ?? '2021-2026'); ?>"
                                                data-district="<?php echo htmlspecialchars($m['district'] ?? ''); ?>"
                                                data-block="<?php echo htmlspecialchars($m['block'] ?? ''); ?>"
                                                data-panchayat="<?php echo htmlspecialchars($m['panchayat'] ?? ''); ?>"
                                                data-address="<?php echo htmlspecialchars($m['address'] ?? ''); ?>"
                                                title="Edit Mukhiya">
                                                <i class="fas fa-pen-to-square"></i>
                                            </button>
                                            <?php if (!empty($m['mobile'])): ?>
                                                <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $m['mobile']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Contact">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="mukhiyas.php?delete_id=<?php echo $m['id']; ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&panchayat=<?php echo urlencode($filter_panchayat); ?>&category=<?php echo urlencode($filter_category); ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this Mukhiya record?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block text-muted opacity-50"></i>
                                    <h6>No Mukhiya records found</h6>
                                    <p class="small text-muted mb-0">Try changing your search keywords or filters.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">Showing <?php echo count($mukhiyas); ?> of <?php echo number_format($total_rows); ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&panchayat=<?php echo urlencode($filter_panchayat); ?>&category=<?php echo urlencode($filter_category); ?>">Prev</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&panchayat=<?php echo urlencode($filter_panchayat); ?>&category=<?php echo urlencode($filter_category); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&panchayat=<?php echo urlencode($filter_panchayat); ?>&category=<?php echo urlencode($filter_category); ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<!-- Modal: Add New Mukhiya -->
<div class="modal fade" id="addMukhiyaModal" tabindex="-1" aria-labelledby="addMukhiyaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="mukhiyas.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" id="addMukhiyaForm">
                <input type="hidden" name="action" value="add_mukhiya">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="addMukhiyaModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-plus-circle me-2 text-danger"></i> Add New Mukhiya Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Elected Mukhiya Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" class="form-control" required placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" required>
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="add_m_district" class="form-select" required>
                                <option value="">Select District</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo (strcasecmp($filter_district, $d['name']) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">Block Name <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="add_m_block_toggle" onclick="toggleBlockInput('add')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="add_m_block_select_wrap">
                                <select name="block" id="add_m_block" class="form-select" required>
                                    <option value="">Select District First</option>
                                </select>
                            </div>
                            <div id="add_m_block_custom_wrap" class="d-none">
                                <input type="text" id="add_m_block_custom" class="form-control" placeholder="Enter block name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">Gram Panchayat <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="add_m_panchayat_toggle" onclick="togglePanchayatInput('add')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="add_m_panchayat_select_wrap">
                                <select name="panchayat" id="add_m_panchayat" class="form-select" required>
                                    <option value="">Select Block First</option>
                                </select>
                            </div>
                            <div id="add_m_panchayat_custom_wrap" class="d-none">
                                <input type="text" id="add_m_panchayat_custom" class="form-control" placeholder="Enter panchayat name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Address / Village</label>
                            <input type="text" name="address" class="form-control" placeholder="Village / Ward details">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Mukhiya Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Mukhiya -->
<div class="modal fade" id="editMukhiyaModal" tabindex="-1" aria-labelledby="editMukhiyaModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="mukhiyas.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" id="editMukhiyaForm">
                <input type="hidden" name="action" value="edit_mukhiya">
                <input type="hidden" name="id" id="edit_m_id">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="editMukhiyaModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-user-pen me-2 text-danger"></i> Edit Mukhiya Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Elected Mukhiya Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" id="edit_m_name" class="form-control" required placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" id="edit_m_mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category <span class="text-danger">*</span></label>
                            <select name="category" id="edit_m_category" class="form-select" required>
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="edit_m_gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="edit_m_district" class="form-select" required>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">Block Name <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="edit_m_block_toggle" onclick="toggleBlockInput('edit')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="edit_m_block_select_wrap">
                                <select name="block" id="edit_m_block" class="form-select" required>
                                    <option value="">Select District First</option>
                                </select>
                            </div>
                            <div id="edit_m_block_custom_wrap" class="d-none">
                                <input type="text" id="edit_m_block_custom" class="form-control" placeholder="Enter block name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">Gram Panchayat <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="edit_m_panchayat_toggle" onclick="togglePanchayatInput('edit')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="edit_m_panchayat_select_wrap">
                                <select name="panchayat" id="edit_m_panchayat" class="form-select" required>
                                    <option value="">Select Block First</option>
                                </select>
                            </div>
                            <div id="edit_m_panchayat_custom_wrap" class="d-none">
                                <input type="text" id="edit_m_panchayat_custom" class="form-control" placeholder="Enter panchayat name">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" id="edit_m_tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Address / Village</label>
                            <input type="text" name="address" id="edit_m_address" class="form-control" placeholder="Village / Ward details">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Complete Bihar District & Block Matrix -->
<div class="modal fade" id="matrixModal" tabindex="-1" aria-labelledby="matrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="matrixModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-table-cells me-2 text-danger"></i> Complete Bihar Mukhiyas: District & Block Matrix
                    </h5>
                    <small class="text-muted">Total <?php echo number_format($total_overall_mukhiyas); ?> Mukhiyas across 38 Districts and 534 Blocks</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 30%;">District</th>
                                <th class="text-center" style="width: 15%;">Total Blocks</th>
                                <th class="text-center" style="width: 20%;">Total Mukhiyas</th>
                                <th class="text-end" style="width: 30%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; foreach ($district_counts as $dName => $dInfo): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong class="text-dark"><?php echo htmlspecialchars($dName); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?php echo $dInfo['block_count']; ?> Blocks</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger rounded-pill px-3 py-1.5 font-monospace fs-7">
                                            <?php echo number_format($dInfo['mukhiya_count']); ?> Mukhiyas
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="mukhiyas.php?district=<?php echo urlencode($dName); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                            <i class="fas fa-filter me-1"></i> View District & Blocks
                                        </a>
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
// Dynamic Cascading AJAX & Dropdown Managers
function fetchBlocks(district, blockSelectEl, selectedBlock = '', callback = null) {
    if (!blockSelectEl) return;
    blockSelectEl.innerHTML = '<option value="">Loading blocks...</option>';
    blockSelectEl.disabled = true;

    if (!district) {
        blockSelectEl.innerHTML = '<option value="">Select District First</option>';
        blockSelectEl.disabled = false;
        if (callback) callback();
        return;
    }

    fetch(`mukhiyas.php?ajax=get_blocks&district=${encodeURIComponent(district)}`)
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

function fetchPanchayats(district, block, panchayatSelectEl, selectedPanchayat = '', callback = null) {
    if (!panchayatSelectEl) return;
    panchayatSelectEl.innerHTML = '<option value="">Loading panchayats...</option>';
    panchayatSelectEl.disabled = true;

    if (!district || !block) {
        panchayatSelectEl.innerHTML = '<option value="">Select Block First</option>';
        panchayatSelectEl.disabled = false;
        if (callback) callback();
        return;
    }

    fetch(`mukhiyas.php?ajax=get_panchayats&district=${encodeURIComponent(district)}&block=${encodeURIComponent(block)}`)
        .then(res => res.json())
        .then(data => {
            panchayatSelectEl.innerHTML = '<option value="">Select Gram Panchayat</option>';
            if (data.panchayats && data.panchayats.length > 0) {
                let found = false;
                data.panchayats.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    if (selectedPanchayat && p.trim().toLowerCase() === selectedPanchayat.trim().toLowerCase()) {
                        opt.selected = true;
                        found = true;
                    }
                    panchayatSelectEl.appendChild(opt);
                });
                if (selectedPanchayat && !found) {
                    const opt = document.createElement('option');
                    opt.value = selectedPanchayat;
                    opt.textContent = selectedPanchayat + ' (Current)';
                    opt.selected = true;
                    panchayatSelectEl.appendChild(opt);
                }
            } else {
                panchayatSelectEl.innerHTML = '<option value="">No panchayats found</option>';
                if (selectedPanchayat) {
                    const opt = document.createElement('option');
                    opt.value = selectedPanchayat;
                    opt.textContent = selectedPanchayat;
                    opt.selected = true;
                    panchayatSelectEl.appendChild(opt);
                }
            }
            panchayatSelectEl.disabled = false;
            if (callback) callback();
        })
        .catch(err => {
            console.error('Error fetching panchayats:', err);
            panchayatSelectEl.innerHTML = '<option value="">Error loading panchayats</option>';
            panchayatSelectEl.disabled = false;
            if (callback) callback();
        });
}

// Fallback manual input toggles for custom block/panchayat
function toggleBlockInput(prefix) {
    const selWrap = document.getElementById(`${prefix}_m_block_select_wrap`);
    const customWrap = document.getElementById(`${prefix}_m_block_custom_wrap`);
    const sel = document.getElementById(`${prefix}_m_block`);
    const custom = document.getElementById(`${prefix}_m_block_custom`);
    const toggleBtn = document.getElementById(`${prefix}_m_block_toggle`);

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

function togglePanchayatInput(prefix) {
    const selWrap = document.getElementById(`${prefix}_m_panchayat_select_wrap`);
    const customWrap = document.getElementById(`${prefix}_m_panchayat_custom_wrap`);
    const sel = document.getElementById(`${prefix}_m_panchayat`);
    const custom = document.getElementById(`${prefix}_m_panchayat_custom`);
    const toggleBtn = document.getElementById(`${prefix}_m_panchayat_toggle`);

    if (customWrap.classList.contains('d-none')) {
        customWrap.classList.remove('d-none');
        selWrap.classList.add('d-none');
        custom.name = 'panchayat';
        custom.required = true;
        custom.value = sel.value || '';
        sel.removeAttribute('name');
        sel.required = false;
        toggleBtn.innerHTML = '<i class="fas fa-list me-1"></i>Select from list';
    } else {
        customWrap.classList.add('d-none');
        selWrap.classList.remove('d-none');
        sel.name = 'panchayat';
        sel.required = true;
        custom.removeAttribute('name');
        custom.required = false;
        toggleBtn.innerHTML = '<i class="fas fa-pen-to-square me-1"></i>Custom';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editMukhiyaModal'));
    
    // Add Mukhiya Modal Listeners
    const addDistrict = document.getElementById('add_m_district');
    const addBlock = document.getElementById('add_m_block');
    const addPanchayat = document.getElementById('add_m_panchayat');

    if (addDistrict) {
        addDistrict.addEventListener('change', function() {
            fetchBlocks(this.value, addBlock, '', function() {
                fetchPanchayats(addDistrict.value, '', addPanchayat);
            });
        });
        // Initial load if district is preselected
        if (addDistrict.value) {
            fetchBlocks(addDistrict.value, addBlock, '<?php echo addslashes($filter_block); ?>', function() {
                if (addBlock.value) {
                    fetchPanchayats(addDistrict.value, addBlock.value, addPanchayat, '<?php echo addslashes($filter_panchayat); ?>');
                }
            });
        }
    }

    if (addBlock) {
        addBlock.addEventListener('change', function() {
            fetchPanchayats(addDistrict.value, this.value, addPanchayat);
        });
    }

    // Edit Mukhiya Modal Listeners
    const editDistrict = document.getElementById('edit_m_district');
    const editBlock = document.getElementById('edit_m_block');
    const editPanchayat = document.getElementById('edit_m_panchayat');

    if (editDistrict) {
        editDistrict.addEventListener('change', function() {
            fetchBlocks(this.value, editBlock, '', function() {
                fetchPanchayats(editDistrict.value, '', editPanchayat);
            });
        });
    }

    if (editBlock) {
        editBlock.addEventListener('change', function() {
            fetchPanchayats(editDistrict.value, this.value, editPanchayat);
        });
    }

    // Edit Button Click Handling
    document.querySelectorAll('.edit-mukhiya-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_m_id').value = this.dataset.id || '';
            document.getElementById('edit_m_name').value = this.dataset.name || '';
            document.getElementById('edit_m_mobile').value = this.dataset.mobile || '';
            document.getElementById('edit_m_category').value = this.dataset.category || 'सामान्य वर्ग';
            document.getElementById('edit_m_gender').value = this.dataset.gender || 'Male';
            document.getElementById('edit_m_tenure').value = this.dataset.tenure || '2021-2026';
            document.getElementById('edit_m_address').value = this.dataset.address || '';

            const distVal = this.dataset.district || '';
            const blockVal = this.dataset.block || '';
            const panchayatVal = this.dataset.panchayat || '';

            editDistrict.value = distVal;

            // Reset manual wraps back to standard select mode
            const blockCustomWrap = document.getElementById('edit_m_block_custom_wrap');
            if (blockCustomWrap && !blockCustomWrap.classList.contains('d-none')) {
                toggleBlockInput('edit');
            }
            const panchayatCustomWrap = document.getElementById('edit_m_panchayat_custom_wrap');
            if (panchayatCustomWrap && !panchayatCustomWrap.classList.contains('d-none')) {
                togglePanchayatInput('edit');
            }

            fetchBlocks(distVal, editBlock, blockVal, function() {
                fetchPanchayats(distVal, blockVal, editPanchayat, panchayatVal);
            });
            
            editModal.show();
        });
    });
});
</script>

</body>
</html>
