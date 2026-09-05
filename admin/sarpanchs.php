<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Standard Bihar Reservation Categories
$reservation_categories = [
    'सामान्य वर्ग' => 'सामान्य वर्ग (General / Unreserved)',
    'पिछड़ा वर्ग अनुसूची-I' => 'पिछड़ा वर्ग अनुसूची-I (EBC / BC-1)',
    'पिछड़ा वर्ग अनुसूची-II' => 'पिछड़ा वर्ग अनुसूची-II (BC / BC-2)',
    'अनुसूचित जाति' => 'अनुसूचित जाति (SC)',
    'अनुसूचित जनजाति' => 'अनुसूचित जनजाति (ST)'
];

// Handle Add POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_sarpanch' && $conn) {
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $age = (int)($_POST['age'] ?? 0);
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $d_slug = slugify($district);

    if (!empty($c_name) && !empty($district) && !empty($panchayat)) {
        $stmt = $conn->prepare("INSERT INTO `sarpanchs` (`candidate_name`, `post`, `district`, `district_slug`, `block`, `panchayat`, `gender`, `category`, `mobile`, `address`, `age`) VALUES (?, 'सरपंच', ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssssi", $c_name, $district, $d_slug, $block, $panchayat, $gender, $category, $mobile, $address, $age);
            if ($stmt->execute()) {
                $message = "New Sarpanch record for '" . htmlspecialchars($c_name) . "' (" . htmlspecialchars($panchayat) . ") added successfully.";
            } else {
                $error = "Error adding Sarpanch: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields (Candidate Name, District, and Panchayat).";
    }
}

// Handle Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_sarpanch' && $conn) {
    $edit_id = (int)($_POST['id'] ?? 0);
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $age = (int)($_POST['age'] ?? 0);
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $d_slug = slugify($district);

    if ($edit_id > 0 && !empty($c_name)) {
        $stmt = $conn->prepare("UPDATE `sarpanchs` SET `candidate_name` = ?, `mobile` = ?, `category` = ?, `gender` = ?, `age` = ?, `district` = ?, `district_slug` = ?, `block` = ?, `panchayat` = ?, `address` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("ssssisssssi", $c_name, $mobile, $category, $gender, $age, $district, $d_slug, $block, $panchayat, $address, $edit_id);
            if ($stmt->execute()) {
                $message = "Sarpanch record for '" . htmlspecialchars($c_name) . "' (" . htmlspecialchars($panchayat) . ") updated successfully.";
            } else {
                $error = "Error updating Sarpanch: " . $conn->error;
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
        $stmt = $conn->prepare("DELETE FROM `sarpanchs` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Sarpanch record deleted successfully.";
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
$filter_category = isset($_GET['category']) ? sanitize($_GET['category']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$sarpanchs = [];
$total_rows = 0;
$total_overall_sarpanchs = 0;
$total_districts_count = 0;
$total_blocks_count = 0;

$district_counts = [];
$block_counts = [];

if ($conn) {
    // 1. Overall Platform Stats
    $stat_res = $conn->query("SELECT COUNT(*) as total_s, COUNT(DISTINCT district) as total_d, COUNT(DISTINCT CONCAT(district, '___', block)) as total_b FROM `sarpanchs`");
    if ($stat_res && $row = $stat_res->fetch_assoc()) {
        $total_overall_sarpanchs = (int)$row['total_s'];
        $total_districts_count = (int)$row['total_d'];
        $total_blocks_count = (int)$row['total_b'];
    }

    // 2. District-Wise Counts
    $d_res = $conn->query("SELECT district, COUNT(*) as sarpanch_count, COUNT(DISTINCT block) as block_count FROM `sarpanchs` WHERE district IS NOT NULL AND district != '' GROUP BY district ORDER BY district ASC");
    if ($d_res) {
        while ($row = $d_res->fetch_assoc()) {
            $district_counts[$row['district']] = $row;
        }
        ksort($district_counts, SORT_NATURAL | SORT_FLAG_CASE);
    }

    // 3. Block-Wise Counts for selected district (if district selected)
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $b_res = $conn->query("SELECT block, COUNT(*) as sarpanch_count, COUNT(DISTINCT panchayat) as panchayat_count FROM `sarpanchs` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND block != '' GROUP BY block ORDER BY block ASC");
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
    if (!empty($filter_category)) {
        $esc_c = $conn->real_escape_string($filter_category);
        $where .= " AND `category` = '$esc_c'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `sarpanchs` WHERE $where");
    if ($c_res) $total_rows = (int)$c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `sarpanchs` WHERE $where ORDER BY `district` ASC, `block` ASC, `panchayat` ASC, `candidate_name` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $sarpanchs[] = $r;
        }
    }
}

$total_pages = ceil($total_rows / $limit);
$districts = DataProvider::getDistricts() ?: [];
usort($districts, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

// Find districts with 0 Sarpanchs
$districts_zero_sarpanchs = [];
if (!empty($districts)) {
    foreach ($districts as $d) {
        $dName = $d['name'];
        if (empty($district_counts[$dName])) {
            $districts_zero_sarpanchs[] = $dName;
        }
    }
    natcasesort($districts_zero_sarpanchs);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gram Kutchery Sarpanchs Directory — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Gram Kutchery Sarpanchs</h1>
                <p class="text-muted mb-0">Elected Sarpanch directory and village justice heads across Bihar Panchayats.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <button type="button" class="btn btn-warning fw-semibold rounded-3 shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#addSarpanchModal">
                    <i class="fas fa-plus-circle me-1"></i> Add New Sarpanch
                </button>
                <a href="bulk-upload.php?type=sarpanchs" class="btn btn-outline-warning fw-semibold rounded-3 shadow-sm bg-white text-dark">
                    <i class="fas fa-cloud-arrow-up me-1"></i> Bulk Upload CSV
                </a>
                <button type="button" class="btn btn-outline-dark fw-semibold rounded-3 shadow-sm bg-white" data-bs-toggle="modal" data-bs-target="#matrixModal">
                    <i class="fas fa-table-cells me-1 text-warning"></i> Full District & Block Matrix
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

        <?php if (!empty($districts_zero_sarpanchs)): ?>
            <!-- Zero Sarpanch Warning Card -->
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-triangle-exclamation text-danger fs-4 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">Data Gap Warning: <?php echo count($districts_zero_sarpanchs); ?> Districts Have 0 Sarpanch Records</h6>
                        <p class="small text-muted mb-2">The following districts currently do not have Sarpanch leadership data seeded in the database:</p>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($districts_zero_sarpanchs as $zd): ?>
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
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Elected Sarpanchs</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_overall_sarpanchs); ?></h3>
                        <small class="text-warning fw-semibold"><i class="fas fa-scale-balanced me-1"></i>Gram Kutchery Heads</small>
                    </div>
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Districts Matrix</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo count($district_counts); ?> / 38</h3>
                        <small class="<?php echo count($district_counts) === 38 ? 'text-success' : 'text-danger'; ?> fw-semibold">
                            <?php echo count($district_counts) === 38 ? '100% District Coverage' : (38 - count($district_counts)) . ' Districts Missing Data'; ?>
                        </small>
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
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
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
                        <i class="fas fa-city me-2 text-warning"></i> District-Wise Sarpanch Count (38 Districts)
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark small">Click to filter by District</span>
                </div>
                <?php if (!empty($filter_district)): ?>
                    <a href="sarpanchs.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-warning text-dark rounded-pill py-0.5 px-2.5 small">
                        <i class="fas fa-times me-1"></i> Clear District Filter
                    </a>
                <?php endif; ?>
            </div>
            <div class="section-card-body p-3">
                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                    <a href="sarpanchs.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" 
                       class="btn btn-sm <?php echo empty($filter_district) ? 'btn-dark shadow-sm fw-bold' : 'btn-light border text-dark'; ?> rounded-pill py-1 px-3 district-pill-btn">
                        <span>All 38 Districts</span>
                        <span class="badge <?php echo empty($filter_district) ? 'bg-white text-dark' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?php echo number_format($total_overall_sarpanchs); ?></span>
                    </a>
                    
                    <?php foreach ($districts as $d): 
                        $dName = $d['name'];
                        $dInfo = $district_counts[$dName] ?? null;
                        $dCount = $dInfo['sarpanch_count'] ?? 0;
                        $isActive = (strcasecmp($filter_district, $dName) === 0);
                        $isZero = ($dCount === 0);
                    ?>
                        <a href="sarpanchs.php?district=<?php echo urlencode($dName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo $isActive ? 'btn-warning text-dark shadow-sm fw-bold' : ($isZero ? 'btn-outline-danger' : 'btn-light border text-dark'); ?> rounded-pill py-1 px-2.5 district-pill-btn"
                           title="<?php echo htmlspecialchars($dName); ?>: <?php echo $dCount; ?> Sarpanchs">
                            <?php if ($isZero): ?><i class="fas fa-triangle-exclamation text-danger me-1"></i><?php endif; ?>
                            <span><?php echo htmlspecialchars($dName); ?></span>
                            <span class="badge <?php echo $isActive ? 'bg-dark text-white' : ($isZero ? 'bg-danger text-white' : 'bg-warning-subtle text-dark border border-warning-subtle'); ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                <?php echo number_format($dCount); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Block-Wise Count Breakdown (Visible when a District is selected) -->
        <?php if (!empty($filter_district)): ?>
            <div class="section-card mb-4 border-warning border-opacity-50 bg-warning bg-opacity-10">
                <div class="section-card-header bg-warning bg-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-cubes me-2 text-warning"></i> Block-Wise Sarpanch Count in <?php echo htmlspecialchars($filter_district); ?> (<?php echo count($block_counts); ?> Blocks)
                        </h6>
                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-0.5">
                            <?php echo number_format($district_counts[$filter_district]['sarpanch_count'] ?? $total_rows); ?> Sarpanchs
                        </span>
                    </div>
                    <?php if (!empty($filter_block)): ?>
                        <a href="sarpanchs.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-dark rounded-pill py-0.5 px-2.5 small bg-white">
                            <i class="fas fa-times me-1"></i> Clear Block Filter
                        </a>
                    <?php endif; ?>
                </div>
                <div class="section-card-body p-3">
                    <?php if (!empty($block_counts)): ?>
                        <div class="d-flex flex-wrap gap-1.5 align-items-center">
                            <a href="sarpanchs.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                               class="btn btn-sm <?php echo empty($filter_block) ? 'btn-dark shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-3 block-pill-btn">
                                <span>All Blocks in <?php echo htmlspecialchars($filter_district); ?></span>
                                <span class="badge <?php echo empty($filter_block) ? 'bg-white text-dark' : 'bg-warning text-dark'; ?> rounded-pill ms-1">
                                    <?php echo number_format($district_counts[$filter_district]['sarpanch_count'] ?? $total_rows); ?>
                                </span>
                            </a>

                            <?php foreach ($block_counts as $bName => $bInfo): ?>
                                <?php $isBlockActive = (strcasecmp($filter_block, $bName) === 0); ?>
                                <a href="sarpanchs.php?district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($bName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                                   class="btn btn-sm <?php echo $isBlockActive ? 'btn-warning text-dark shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-2.5 block-pill-btn">
                                    <span><?php echo htmlspecialchars($bName); ?></span>
                                    <span class="badge <?php echo $isBlockActive ? 'bg-dark text-white' : 'bg-warning-subtle text-dark border border-warning-subtle'; ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                        <?php echo number_format($bInfo['sarpanch_count']); ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-warning py-2 px-3 small mb-0 rounded-3">
                            <i class="fas fa-exclamation-triangle me-1"></i> No Sarpanch records exist for any block in <strong><?php echo htmlspecialchars($filter_district); ?></strong>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Card -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="sarpanchs.php" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search name, panchayat, mobile..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="district" id="districtSelect" class="form-select" onchange="this.form.submit()">
                            <option value="">All 38 Districts (<?php echo number_format($total_overall_sarpanchs); ?>)</option>
                            <?php foreach ($districts as $d): 
                                $dName = $d['name'];
                                $dCount = $district_counts[$dName]['sarpanch_count'] ?? 0;
                            ?>
                                <option value="<?php echo htmlspecialchars($dName); ?>" <?php echo (strcasecmp($filter_district, $dName) === 0) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dName); ?> (<?php echo number_format($dCount); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="block" class="form-select" onchange="this.form.submit()" <?php echo empty($filter_district) ? 'disabled' : ''; ?>>
                            <option value="">
                                <?php echo !empty($filter_district) ? 'All Blocks' : 'Select District First'; ?>
                            </option>
                            <?php if (!empty($block_counts)): ?>
                                <?php foreach ($block_counts as $bName => $bInfo): ?>
                                    <option value="<?php echo htmlspecialchars($bName); ?>" <?php echo (strcasecmp($filter_block, $bName) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bName); ?> (<?php echo number_format($bInfo['sarpanch_count']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="">All Reservation Categories</option>
                            <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                <option value="<?php echo htmlspecialchars($catKey); ?>" <?php echo ($filter_category === $catKey) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($catKey); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="sarpanchs.php" class="btn btn-light border" title="Reset All Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-scale-balanced me-2 text-warning"></i> Sarpanch Directory Records
                    </h6>
                    <?php if (!empty($filter_district) || !empty($filter_block) || !empty($filter_category)): ?>
                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 px-2.5 py-1">
                            <?php 
                            $filterTags = [];
                            if (!empty($filter_district)) $filterTags[] = $filter_district;
                            if (!empty($filter_block)) $filterTags[] = $filter_block;
                            if (!empty($filter_category)) $filterTags[] = $filter_category;
                            echo htmlspecialchars(implode(' &rsaquo; ', $filterTags)); 
                            ?>
                        </span>
                    <?php endif; ?>
                </div>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?> (<?php echo number_format($total_rows); ?> Total)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Sarpanch Name</th>
                            <th>District</th>
                            <th>Block</th>
                            <th>Gram Panchayat</th>
                            <th>Reservation Category</th>
                            <th>Mobile</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sarpanchs)): ?>
                            <?php foreach ($sarpanchs as $s): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($s['candidate_name']); ?></div>
                                        <?php if (!empty($s['age'])): ?>
                                            <small class="text-muted"><i class="fas fa-user-clock me-1 text-muted"></i><?php echo (int)$s['age']; ?> Yrs</small>
                                        <?php endif; ?>
                                        <?php if (!empty($s['address'])): ?>
                                            <div class="text-muted small text-truncate" style="max-width: 200px;"><i class="fas fa-map-marker-alt me-1 text-muted"></i><?php echo htmlspecialchars($s['address']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="sarpanchs.php?district=<?php echo urlencode($s['district']); ?>" class="fw-semibold text-dark text-decoration-none" title="Filter by <?php echo htmlspecialchars($s['district']); ?>">
                                            <?php echo htmlspecialchars($s['district']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="sarpanchs.php?district=<?php echo urlencode($s['district']); ?>&block=<?php echo urlencode($s['block']); ?>" class="badge bg-light text-dark border text-decoration-none" title="Filter by <?php echo htmlspecialchars($s['block']); ?>">
                                            <?php echo htmlspecialchars($s['block']); ?>
                                        </a>
                                    </td>
                                    <td><span class="fw-bold text-warning text-dark"><?php echo htmlspecialchars($s['panchayat']); ?></span></td>
                                    <td>
                                        <?php
                                        $catVal = $s['category'] ?? 'सामान्य वर्ग';
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
                                        <div><small class="text-muted"><i class="fas <?php echo ($s['gender'] === 'Female') ? 'fa-female text-danger' : 'fa-male text-primary'; ?> me-1"></i><?php echo htmlspecialchars($s['gender'] ?? 'Male'); ?></small></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($s['mobile'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($s['mobile']); ?>" class="text-decoration-none fw-semibold text-dark small">
                                                <i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($s['mobile']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="btn btn-sm btn-light border text-warning edit-sarpanch-btn" 
                                                data-id="<?php echo $s['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($s['candidate_name']); ?>"
                                                data-mobile="<?php echo htmlspecialchars($s['mobile'] ?? ''); ?>"
                                                data-category="<?php echo htmlspecialchars($s['category'] ?? 'सामान्य वर्ग'); ?>"
                                                data-gender="<?php echo htmlspecialchars($s['gender'] ?? 'Male'); ?>"
                                                data-age="<?php echo htmlspecialchars($s['age'] ?? ''); ?>"
                                                data-district="<?php echo htmlspecialchars($s['district'] ?? ''); ?>"
                                                data-block="<?php echo htmlspecialchars($s['block'] ?? ''); ?>"
                                                data-panchayat="<?php echo htmlspecialchars($s['panchayat'] ?? ''); ?>"
                                                data-address="<?php echo htmlspecialchars($s['address'] ?? ''); ?>"
                                                title="Edit Sarpanch">
                                                <i class="fas fa-pen-to-square"></i>
                                            </button>
                                            <?php if (!empty($s['mobile'])): ?>
                                                <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $s['mobile']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Contact">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="sarpanchs.php?delete_id=<?php echo $s['id']; ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&category=<?php echo urlencode($filter_category); ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this Sarpanch record?');" title="Delete">
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
                                    <h6>No Sarpanch records found</h6>
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
                    <small class="text-muted">Showing <?php echo count($sarpanchs); ?> of <?php echo number_format($total_rows); ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&category=<?php echo urlencode($filter_category); ?>">Prev</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&category=<?php echo urlencode($filter_category); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&category=<?php echo urlencode($filter_category); ?>">Next</a>
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

<!-- Modal: Add New Sarpanch -->
<div class="modal fade" id="addSarpanchModal" tabindex="-1" aria-labelledby="addSarpanchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="sarpanchs.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">
                <input type="hidden" name="action" value="add_sarpanch">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="addSarpanchModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-plus-circle me-2 text-warning"></i> Add New Sarpanch Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Elected Sarpanch Name <span class="text-danger">*</span></label>
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
                            <select name="district" class="form-select" required>
                                <option value="">Select District</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo (strcasecmp($filter_district, $d['name']) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Block Name <span class="text-danger">*</span></label>
                            <input type="text" name="block" class="form-control" required placeholder="Block name" value="<?php echo htmlspecialchars($filter_block); ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Gram Panchayat <span class="text-danger">*</span></label>
                            <input type="text" name="panchayat" class="form-control" required placeholder="Gram Panchayat name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Age (Years)</label>
                            <input type="number" name="age" class="form-control" placeholder="e.g. 42">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Address / Village</label>
                            <input type="text" name="address" class="form-control" placeholder="Village / Ward details">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-save me-1"></i> Save Sarpanch Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Sarpanch -->
<div class="modal fade" id="editSarpanchModal" tabindex="-1" aria-labelledby="editSarpanchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="sarpanchs.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">
                <input type="hidden" name="action" value="edit_sarpanch">
                <input type="hidden" name="id" id="edit_s_id">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="editSarpanchModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-scale-balanced me-2 text-warning"></i> Edit Sarpanch Record
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Elected Sarpanch Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" id="edit_s_name" class="form-control" required placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" id="edit_s_mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category <span class="text-danger">*</span></label>
                            <select name="category" id="edit_s_category" class="form-select" required>
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="edit_s_gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="edit_s_district" class="form-select" required>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Block Name <span class="text-danger">*</span></label>
                            <input type="text" name="block" id="edit_s_block" class="form-control" required placeholder="Block name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Gram Panchayat <span class="text-danger">*</span></label>
                            <input type="text" name="panchayat" id="edit_s_panchayat" class="form-control" required placeholder="Gram Panchayat name">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Age (Years)</label>
                            <input type="number" name="age" id="edit_s_age" class="form-control" placeholder="e.g. 42">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">Address / Village</label>
                            <input type="text" name="address" id="edit_s_address" class="form-control" placeholder="Village / Ward details">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Complete Bihar District & Block Matrix for Sarpanchs -->
<div class="modal fade" id="matrixModal" tabindex="-1" aria-labelledby="matrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="matrixModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-table-cells me-2 text-warning"></i> Complete Bihar Sarpanchs: District & Block Matrix
                    </h5>
                    <small class="text-muted">Total <?php echo number_format($total_overall_sarpanchs); ?> Sarpanchs across Bihar Panchayats</small>
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
                                <th class="text-center" style="width: 20%;">Total Sarpanchs</th>
                                <th class="text-end" style="width: 30%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; foreach ($districts as $d): 
                                $dName = $d['name'];
                                $dInfo = $district_counts[$dName] ?? null;
                                $sCount = $dInfo['sarpanch_count'] ?? 0;
                                $bCount = $dInfo['block_count'] ?? 0;
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
                                        <span class="badge bg-light text-dark border"><?php echo $bCount; ?> Blocks</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?php echo $isZero ? 'bg-danger' : 'bg-warning text-dark'; ?> rounded-pill px-3 py-1.5 font-monospace fs-7">
                                            <?php echo number_format($sCount); ?> Sarpanchs
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <a href="sarpanchs.php?district=<?php echo urlencode($dName); ?>" class="btn btn-sm <?php echo $isZero ? 'btn-outline-danger' : 'btn-outline-dark'; ?> rounded-pill px-3">
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
document.addEventListener('DOMContentLoaded', function() {
    const editModal = new bootstrap.Modal(document.getElementById('editSarpanchModal'));
    
    document.querySelectorAll('.edit-sarpanch-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_s_id').value = this.dataset.id || '';
            document.getElementById('edit_s_name').value = this.dataset.name || '';
            document.getElementById('edit_s_mobile').value = this.dataset.mobile || '';
            document.getElementById('edit_s_category').value = this.dataset.category || 'सामान्य वर्ग';
            document.getElementById('edit_s_gender').value = this.dataset.gender || 'Male';
            document.getElementById('edit_s_district').value = this.dataset.district || '';
            document.getElementById('edit_s_block').value = this.dataset.block || '';
            document.getElementById('edit_s_panchayat').value = this.dataset.panchayat || '';
            document.getElementById('edit_s_age').value = this.dataset.age || '';
            document.getElementById('edit_s_address').value = this.dataset.address || '';
            
            editModal.show();
        });
    });
});
</script>

</body>
</html>
