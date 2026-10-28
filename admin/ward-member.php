<?php
/**
 * Bihar Election Control Hub — Gram Panchayat Ward Members Directory (2021-2026)
 * Table: ward_21_26
 */
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Standard Bihar Reservation Categories & Statuses
$reservation_categories = [
    'सामान्य वर्ग' => 'सामान्य वर्ग (General / Unreserved)',
    'पिछड़ा वर्ग अनुसूची-I' => 'पिछड़ा वर्ग अनुसूची-I (EBC / BC-1)',
    'पिछड़ा वर्ग अनुसूची-II' => 'पिछड़ा वर्ग अनुसूची-II (BC / BC-2)',
    'अनुसूचित जाति' => 'अनुसूचित जाति (SC)',
    'अनुसूचित जनजाति' => 'अनुसूचित जनजाति (ST)'
];

$reservation_statuses = [
    'अनारक्षित (अन्य)' => 'अनारक्षित (अन्य) - Unreserved (General)',
    'अनारक्षित (महिला)' => 'अनारक्षित (महिला) - Unreserved (Female)',
    'पिछड़ा वर्ग (अन्य)' => 'पिछड़ा वर्ग (अन्य) - BC (General)',
    'पिछड़ा वर्ग (महिला)' => 'पिछड़ा वर्ग (महिला) - BC (Female)',
    'अनुसूचित जाति (अन्य)' => 'अनुसूचित जाति (अन्य) - SC (General)',
    'अनुसूचित जाति (महिला)' => 'अनुसूचित जाति (महिला) - SC (Female)',
    'अनुसूचित जन - जाति (अन्य)' => 'अनुसूचित जन - जाति (अन्य) - ST (General)',
    'अनुसूचित जन - जाति (महिला)' => 'अनुसूचित जन - जाति (महिला) - ST (Female)'
];

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
                    SELECT block FROM ward_21_26 WHERE (district = ? OR district_slug = ? OR district_hi = ?) AND block != ''
                    UNION
                    SELECT block FROM panchayats WHERE (district = ? OR district_slug = ?) AND block != ''
                ) as u_blocks ORDER BY block ASC
            ");
            if ($stmt) {
                $stmt->bind_param("sssss", $reqDistrict, $dSlug, $reqDistrict, $reqDistrict, $dSlug);
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
                    SELECT panchayat FROM ward_21_26 WHERE (district = ? OR district_slug = ? OR district_hi = ?) AND block = ? AND panchayat != ''
                    UNION
                    SELECT panchayat_name as panchayat FROM panchayats WHERE (district = ? OR district_slug = ?) AND block = ? AND panchayat_name != ''
                ) as u_panchayats ORDER BY panchayat ASC
            ");
            if ($stmt) {
                $stmt->bind_param("sssssss", $reqDistrict, $dSlug, $reqDistrict, $reqBlock, $reqDistrict, $dSlug, $reqBlock);
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

// Handle Add POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_ward_member' && $conn) {
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $father_husband = sanitize($_POST['father_husband_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $reservation_status = sanitize($_POST['reservation_status'] ?? 'अनारक्षित (अन्य)');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $age = !empty($_POST['age']) && is_numeric($_POST['age']) ? (int)$_POST['age'] : null;
    $ward_no = sanitize($_POST['ward_no'] ?? '');
    $territory_no = $ward_no;
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $district = sanitize($_POST['district'] ?? '');
    $district_hi = sanitize($_POST['district_hi'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $d_slug = slugify($district);

    if (!empty($c_name) && !empty($district) && !empty($block) && !empty($panchayat)) {
        $stmt = $conn->prepare("INSERT INTO `ward_21_26` 
            (`candidate_name`, `father_husband_name`, `post`, `district`, `district_hi`, `district_slug`, `block`, `panchayat`, `ward_no`, `territory_no`, `reservation_status`, `gender`, `age`, `category`, `mobile`, `address`, `tenure`) 
            VALUES (?, ?, 'ग्राम पंचायत के सदस्य', ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssssssssissss", 
                $c_name, $father_husband, $district, $district_hi, $d_slug, 
                $block, $panchayat, $ward_no, $territory_no, $reservation_status, 
                $gender, $age, $category, $mobile, $address, $tenure);
            if ($stmt->execute()) {
                $message = "Ward Member record for '" . htmlspecialchars($c_name) . "' (Ward " . htmlspecialchars($ward_no) . ", " . htmlspecialchars($panchayat) . ") added successfully.";
            } else {
                $error = "Error adding Ward Member: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields (Candidate Name, District, Block, and Panchayat).";
    }
}

// Handle Edit POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_ward_member' && $conn) {
    $edit_id = (int)($_POST['id'] ?? 0);
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $father_husband = sanitize($_POST['father_husband_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $reservation_status = sanitize($_POST['reservation_status'] ?? 'अनारक्षित (अन्य)');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $age = !empty($_POST['age']) && is_numeric($_POST['age']) ? (int)$_POST['age'] : null;
    $ward_no = sanitize($_POST['ward_no'] ?? '');
    $territory_no = $ward_no;
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $district = sanitize($_POST['district'] ?? '');
    $district_hi = sanitize($_POST['district_hi'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $d_slug = slugify($district);

    if ($edit_id > 0 && !empty($c_name)) {
        $stmt = $conn->prepare("UPDATE `ward_21_26` SET 
            `candidate_name` = ?, `father_husband_name` = ?, `district` = ?, `district_hi` = ?, `district_slug` = ?,
            `block` = ?, `panchayat` = ?, `ward_no` = ?, `territory_no` = ?, `reservation_status` = ?, `gender` = ?, `age` = ?,
            `category` = ?, `mobile` = ?, `address` = ?, `tenure` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("sssssssssssissssi", 
                $c_name, $father_husband, $district, $district_hi, $d_slug, 
                $block, $panchayat, $ward_no, $territory_no, $reservation_status, 
                $gender, $age, $category, $mobile, $address, $tenure, $edit_id);
            if ($stmt->execute()) {
                $message = "Ward Member record (ID: {$edit_id}) updated successfully.";
            } else {
                $error = "Error updating Ward Member: " . $conn->error;
            }
        }
    }
}

// Handle Delete GET
if (isset($_GET['delete_id']) && $conn) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `ward_21_26` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Ward Member record #{$del_id} deleted successfully.";
            } else {
                $error = "Error deleting record: " . $conn->error;
            }
        }
    }
}

// Filters & Search
$search = sanitize($_GET['q'] ?? '');
$filter_district = sanitize($_GET['district'] ?? '');
$filter_block = sanitize($_GET['block'] ?? '');
$filter_panchayat = sanitize($_GET['panchayat'] ?? '');
$filter_category = sanitize($_GET['category'] ?? '');
$filter_ward = sanitize($_GET['ward'] ?? '');
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = in_array((int)($_GET['limit'] ?? 50), [25, 50, 100, 200]) ? (int)$_GET['limit'] : 50;
$offset = ($page - 1) * $limit;

// Where Clauses
$where = ["1=1"];
if (!empty($search)) {
    $esc_q = $conn->real_escape_string($search);
    $where[] = "(`candidate_name` LIKE '%$esc_q%' OR `father_husband_name` LIKE '%$esc_q%' OR `mobile` LIKE '%$esc_q%' OR `address` LIKE '%$esc_q%')";
}
if (!empty($filter_district)) {
    $esc_dist = $conn->real_escape_string($filter_district);
    $where[] = "(`district` = '$esc_dist' OR `district_slug` = '$esc_dist' OR `district_hi` = '$esc_dist')";
}
if (!empty($filter_block)) {
    $esc_block = $conn->real_escape_string($filter_block);
    $where[] = "`block` = '$esc_block'";
}
if (!empty($filter_panchayat)) {
    $esc_pan = $conn->real_escape_string($filter_panchayat);
    $where[] = "`panchayat` = '$esc_pan'";
}
if (!empty($filter_category)) {
    $esc_cat = $conn->real_escape_string($filter_category);
    $where[] = "`category` = '$esc_cat'";
}
if (!empty($filter_ward)) {
    $esc_ward = $conn->real_escape_string($filter_ward);
    $where[] = "(`ward_no` = '$esc_ward' OR `territory_no` = '$esc_ward')";
}

$whereSql = implode(' AND ', $where);

// Aggregations
$total_members = 0;
$total_districts = 0;
$total_blocks = 0;
$total_panchayats = 0;

if ($conn) {
    $agg_res = $conn->query("SELECT 
        COUNT(*) as total_count,
        COUNT(DISTINCT district) as total_districts,
        COUNT(DISTINCT block) as total_blocks,
        COUNT(DISTINCT panchayat) as total_panchayats
        FROM `ward_21_26`");
    if ($agg_res && $row = $agg_res->fetch_assoc()) {
        $total_members = (int)$row['total_count'];
        $total_districts = (int)$row['total_districts'];
        $total_blocks = (int)$row['total_blocks'];
        $total_panchayats = (int)$row['total_panchayats'];
    }
}

// District List for Filters
$districts_list = [];
if ($conn) {
    $d_res = $conn->query("SELECT district, district_hi, district_slug, COUNT(*) as member_count FROM `ward_21_26` WHERE district IS NOT NULL AND district != '' GROUP BY district, district_hi, district_slug ORDER BY district ASC");
    if ($d_res) {
        while ($d = $d_res->fetch_assoc()) {
            $districts_list[] = $d;
        }
    }
}

// Blocks for current district
$blocks_list = [];
if ($conn && !empty($filter_district)) {
    $esc_d = $conn->real_escape_string($filter_district);
    $b_res = $conn->query("SELECT block, COUNT(*) as member_count FROM `ward_21_26` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d' OR `district_hi` = '$esc_d') AND block != '' GROUP BY block ORDER BY block ASC");
    if ($b_res) {
        while ($b = $b_res->fetch_assoc()) {
            $blocks_list[] = $b['block'];
        }
    }
}

// Panchayats for current block
$panchayats_list = [];
if ($conn && !empty($filter_district) && !empty($filter_block)) {
    $esc_d = $conn->real_escape_string($filter_district);
    $esc_b = $conn->real_escape_string($filter_block);
    $p_res = $conn->query("SELECT DISTINCT panchayat FROM `ward_21_26` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d' OR `district_hi` = '$esc_d') AND `block` = '$esc_b' AND panchayat != '' ORDER BY panchayat ASC");
    if ($p_res) {
        while ($p = $p_res->fetch_assoc()) {
            $panchayats_list[] = $p['panchayat'];
        }
    }
}

// Export CSV handler
if (isset($_GET['export']) && $_GET['export'] === 'csv' && $conn) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=ward_members_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM for Excel UTF-8
    
    fputcsv($output, ['ID', 'Ward No', 'Candidate Name', 'Father/Husband Name', 'District', 'Block', 'Panchayat', 'Gender', 'Age', 'Category', 'Reservation', 'Mobile', 'Address', 'Tenure']);
    
    $exp_res = $conn->query("SELECT * FROM `ward_21_26` WHERE $whereSql ORDER BY `district` ASC, `block` ASC, `panchayat` ASC, CAST(`ward_no` AS UNSIGNED) ASC LIMIT 20000");
    if ($exp_res) {
        while ($er = $exp_res->fetch_assoc()) {
            fputcsv($output, [
                $er['id'],
                $er['ward_no'] ?: $er['territory_no'],
                $er['candidate_name'],
                $er['father_husband_name'],
                $er['district'],
                $er['block'],
                $er['panchayat'],
                $er['gender'],
                $er['age'],
                $er['category'],
                $er['reservation_status'],
                $er['mobile'],
                $er['address'],
                $er['tenure']
            ]);
        }
    }
    fclose($output);
    exit;
}

// Count & Fetch Paginated
$filtered_count = 0;
$members = [];
if ($conn) {
    $c_res = $conn->query("SELECT COUNT(*) as c FROM `ward_21_26` WHERE $whereSql");
    if ($c_res && $crow = $c_res->fetch_assoc()) {
        $filtered_count = (int)$crow['c'];
    }

    $q_res = $conn->query("SELECT * FROM `ward_21_26` WHERE $whereSql ORDER BY `district` ASC, `block` ASC, `panchayat` ASC, CAST(`ward_no` AS UNSIGNED) ASC, `candidate_name` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($m = $q_res->fetch_assoc()) {
            $members[] = $m;
        }
    }
}

$total_pages = ceil($filtered_count / $limit);
$page_title = "Gram Panchayat Ward Members (2021-2026)";
require_once __DIR__ . '/admin-header.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> — Bihar Election Control Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=Noto+Sans+Devanagari:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="admin-style.css">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #1e293b; }
        .stat-card { border-radius: 12px; border: none; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); padding: 1.25rem; transition: transform 0.2s; }
        .stat-card:hover { transform: translateY(-2px); }
        .stat-number { font-size: 1.75rem; font-weight: 700; font-family: 'Outfit', sans-serif; line-height: 1.2; }
        .table-responsive { border-radius: 12px; overflow: hidden; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .table th { background-color: #f8fafc; text-transform: uppercase; font-size: 0.72rem; letter-spacing: 0.05em; font-weight: 700; color: #64748b; padding: 0.75rem 1rem; border-bottom: 2px solid #e2e8f0; }
        .table td { padding: 0.85rem 1rem; vertical-align: middle; border-bottom: 1px solid #f1f5f9; font-size: 0.85rem; }
        .member-avatar { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; background: #e2e8f0; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; color: #475569; font-size: 1rem; }
        .pill-filter { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.75rem; border-radius: 9999px; font-size: 0.8rem; font-weight: 500; background: #e2e8f0; color: #334155; margin-right: 0.5rem; margin-bottom: 0.5rem; text-decoration: none; transition: all 0.2s; }
        .pill-filter:hover, .pill-filter.active { background: #0f172a; color: #fff; }
        .pill-filter.active .badge-count { background: #e2e8f0; color: #0f172a; }
        .badge-count { background: #cbd5e1; color: #1e293b; padding: 0.15rem 0.45rem; border-radius: 9999px; font-size: 0.7rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="admin-wrapper">
    <?php require_once __DIR__ . '/admin-menu.php'; ?>
    
    <div class="admin-content">
        <div class="container-fluid py-4 px-md-4">
            
            <!-- Page Title & Actions -->
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
                <div>
                    <h3 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-house-user text-warning me-2"></i>Gram Panchayat Ward Members
                    </h3>
                    <p class="text-muted small mb-0">
                        Bihar Gram Panchayat Ward Members (2021-2026 Tenure) &bull; Table: <code>ward_21_26</code>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="ward-member.php?<?php echo http_build_query(array_merge($_GET, ['export' => 'csv'])); ?>" class="btn btn-outline-success fw-semibold rounded-3 shadow-sm bg-white">
                        <i class="fas fa-file-csv me-1"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-danger fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                        <i class="fas fa-plus me-1"></i> Add Ward Member
                    </button>
                </div>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-check-circle me-2"></i><?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i><?php echo $error; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Metrics Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Total Ward Members</span>
                            <span class="badge bg-warning text-dark"><i class="fas fa-users"></i></span>
                        </div>
                        <div class="stat-number text-dark"><?php echo number_format($total_members); ?></div>
                        <div class="text-muted extra-small mt-1">Across 38 Bihar Districts</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Districts Covered</span>
                            <span class="badge bg-success bg-opacity-10 text-success"><i class="fas fa-map"></i></span>
                        </div>
                        <div class="stat-number text-success"><?php echo $total_districts; ?> <span class="fs-6 text-muted font-monospace">/ 38</span></div>
                        <div class="text-muted extra-small mt-1">100% State Coverage</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Total Panchayats</span>
                            <span class="badge bg-info bg-opacity-10 text-info"><i class="fas fa-landmark"></i></span>
                        </div>
                        <div class="stat-number text-info"><?php echo number_format($total_panchayats); ?></div>
                        <div class="text-muted extra-small mt-1">In <?php echo number_format($total_blocks); ?> Blocks</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-semibold text-uppercase">Filtered Results</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger"><i class="fas fa-filter"></i></span>
                        </div>
                        <div class="stat-number text-danger"><?php echo number_format($filtered_count); ?></div>
                        <div class="text-muted extra-small mt-1">Matching current query</div>
                    </div>
                </div>
            </div>

            <!-- District Pill Navigation -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="fw-bold small text-uppercase text-secondary tracking-wider">
                            <i class="fas fa-map-pin text-danger me-1"></i> Quick District Filter
                        </span>
                        <?php if (!empty($filter_district)): ?>
                            <a href="ward-member.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small">
                                Clear District Filter
                            </a>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex flex-wrap" style="max-height: 120px; overflow-y: auto;">
                        <a href="ward-member.php<?php echo !empty($search) ? '?q=' . urlencode($search) : ''; ?>" 
                           class="pill-filter <?php echo empty($filter_district) ? 'active' : ''; ?>">
                            <span>All Districts</span>
                            <span class="badge-count"><?php echo number_format($total_members); ?></span>
                        </a>
                        <?php foreach ($districts_list as $dl): 
                            $dName = $dl['district'];
                            $isActive = ($filter_district === $dName || $filter_district === $dl['district_slug'] || $filter_district === $dl['district_hi']);
                        ?>
                            <a href="ward-member.php?district=<?php echo urlencode($dName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                               class="pill-filter <?php echo $isActive ? 'active' : ''; ?>">
                                <span><?php echo htmlspecialchars($dName); ?></span>
                                <span class="badge-count"><?php echo number_format($dl['member_count']); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Filters Bar -->
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <form method="GET" action="ward-member.php" class="row g-2 align-items-center">
                        <div class="col-md-3">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                <input type="text" name="q" class="form-control" placeholder="Search name, mobile, father, address..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select name="district" id="filter_district_select" class="form-select form-select-sm" onchange="loadBlocks(this.value)">
                                <option value="">All Districts</option>
                                <?php foreach ($districts_list as $dl): 
                                    $sel = ($filter_district === $dl['district'] || $filter_district === $dl['district_slug'] || $filter_district === $dl['district_hi']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo htmlspecialchars($dl['district']); ?>" <?php echo $sel; ?>>
                                        <?php echo htmlspecialchars($dl['district']); ?> (<?php echo htmlspecialchars($dl['district_hi'] ?: $dl['district']); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="block" id="filter_block_select" class="form-select form-select-sm" onchange="loadPanchayats(document.getElementById('filter_district_select').value, this.value)">
                                <option value="">All Blocks</option>
                                <?php foreach ($blocks_list as $bl): ?>
                                    <option value="<?php echo htmlspecialchars($bl); ?>" <?php echo ($filter_block === $bl) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bl); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="panchayat" id="filter_panchayat_select" class="form-select form-select-sm">
                                <option value="">All Panchayats</option>
                                <?php foreach ($panchayats_list as $pl): ?>
                                    <option value="<?php echo htmlspecialchars($pl); ?>" <?php echo ($filter_panchayat === $pl) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($pl); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>" <?php echo ($filter_category === $catKey) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($catKey); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary w-100 fw-bold">Filter</button>
                            <a href="ward-member.php" class="btn btn-sm btn-light border" title="Reset All Filters"><i class="fas fa-undo"></i></a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table of Ward Members -->
            <div class="table-responsive mb-4">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 80px;">Ward No</th>
                            <th>Candidate Details</th>
                            <th>Panchayat & Block</th>
                            <th>District</th>
                            <th>Category & Reservation</th>
                            <th>Contact / Mobile</th>
                            <th class="text-end" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($members)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    <h5>No Ward Members Found</h5>
                                    <p class="small mb-0">Try adjusting your search criteria or clearing filters.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php 
                            $idx = $offset + 1;
                            foreach ($members as $m): 
                                $initial = mb_substr($m['candidate_name'], 0, 1, 'UTF-8');
                                $wardNum = $m['ward_no'] ?: $m['territory_no'];
                            ?>
                                <tr>
                                    <td class="text-muted small"><?php echo $idx++; ?></td>
                                    <td>
                                        <span class="badge bg-warning text-dark fw-bold px-2 py-1 fs-6">
                                            Ward <?php echo htmlspecialchars($wardNum); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <?php if (!empty($m['photo_url'])): ?>
                                                <img src="<?php echo htmlspecialchars($m['photo_url']); ?>" alt="Photo" class="member-avatar">
                                            <?php else: ?>
                                                <div class="member-avatar">
                                                    <?php echo $initial; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark" style="font-family: 'Noto Sans Devanagari', sans-serif;">
                                                    <?php echo htmlspecialchars($m['candidate_name']); ?>
                                                </div>
                                                <div class="extra-small text-muted">
                                                    <?php if (!empty($m['father_husband_name'])): ?>
                                                        <span>पिता/पति: <?php echo htmlspecialchars($m['father_husband_name']); ?></span> &bull;
                                                    <?php endif; ?>
                                                    <span><?php echo htmlspecialchars($m['gender'] ?? 'N/A'); ?></span>
                                                    <?php if (!empty($m['age'])): ?>
                                                        &bull; <span><?php echo (int)$m['age']; ?> yrs</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <?php echo htmlspecialchars($m['panchayat']); ?>
                                        </div>
                                        <div class="extra-small text-muted">
                                            प्रखंड: <?php echo htmlspecialchars($m['block']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">
                                            <?php echo htmlspecialchars($m['district']); ?>
                                        </div>
                                        <?php if (!empty($m['district_hi'])): ?>
                                            <div class="extra-small text-muted"><?php echo htmlspecialchars($m['district_hi']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($m['category'] ?? 'सामान्य'); ?></div>
                                        <div class="extra-small text-muted"><?php echo htmlspecialchars($m['reservation_status'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($m['mobile'])): ?>
                                            <a href="tel:<?php echo htmlspecialchars($m['mobile']); ?>" class="text-decoration-none small text-dark fw-semibold">
                                                <i class="fas fa-phone-alt text-success me-1"></i><?php echo htmlspecialchars($m['mobile']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted extra-small">Not provided</span>
                                        <?php endif; ?>
                                        <?php if (!empty($m['address'])): ?>
                                            <div class="extra-small text-muted text-truncate" style="max-width: 180px;" title="<?php echo htmlspecialchars($m['address']); ?>">
                                                <i class="fas fa-location-dot me-0.5 text-danger"></i><?php echo htmlspecialchars($m['address']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-light border text-primary" 
                                                    onclick="openEditModal(<?php echo htmlspecialchars(json_encode($m)); ?>)" 
                                                    title="Edit Record">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <a href="ward-member.php?delete_id=<?php echo $m['id']; ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&panchayat=<?php echo urlencode($filter_panchayat); ?>&page=<?php echo $page; ?>" 
                                               class="btn btn-light border text-danger" 
                                               onclick="return confirm('Are you sure you want to delete this Ward Member record?');" 
                                               title="Delete Record">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination Bar -->
            <?php if ($total_pages > 1): ?>
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="small text-muted">
                        Showing <strong><?php echo number_format($offset + 1); ?></strong> to <strong><?php echo number_format(min($offset + $limit, $filtered_count)); ?></strong> of <strong><?php echo number_format($filtered_count); ?></strong> records
                    </div>
                    <nav aria-label="Ward Members Pagination">
                        <ul class="pagination pagination-sm mb-0">
                            <?php
                            $queryParams = $_GET;
                            $startPage = max(1, $page - 3);
                            $endPage = min($total_pages, $page + 3);
                            ?>
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => 1])); ?>">First</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $page - 1])); ?>">&laquo;</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                <li class="page-item <?php echo ($i === $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $i])); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $page + 1])); ?>">&raquo;</a>
                                </li>
                                <li class="page-item">
                                    <a class="page-link" href="?<?php echo http_build_query(array_merge($queryParams, ['page' => $total_pages])); ?>">Last</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Add Ward Member Modal -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-plus text-danger me-2"></i>Add Ward Member (2021-2026)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="ward-member.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>">
                <input type="hidden" name="action" value="add_ward_member">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Candidate Name (Hindi/Eng) <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" class="form-control" required placeholder="e.g. सुनीता देवी">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Father / Husband Name</label>
                            <input type="text" name="father_husband_name" class="form-control" placeholder="e.g. राम बालक राय">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Ward Number <span class="text-danger">*</span></label>
                            <input type="text" name="ward_no" class="form-control" required placeholder="e.g. 1, 2, 3...">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">District <span class="text-danger">*</span></label>
                            <select name="district" id="add_district" class="form-select" required onchange="loadBlocksModal('add', this.value)">
                                <option value="">Select District</option>
                                <?php foreach ($districts_list as $dl): ?>
                                    <option value="<?php echo htmlspecialchars($dl['district']); ?>"><?php echo htmlspecialchars($dl['district']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Block <span class="text-danger">*</span></label>
                            <select name="block" id="add_block" class="form-select" required onchange="loadPanchayatsModal('add', document.getElementById('add_district').value, this.value)">
                                <option value="">Select Block</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Panchayat <span class="text-danger">*</span></label>
                            <select name="panchayat" id="add_panchayat" class="form-select" required>
                                <option value="">Select Panchayat</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Age</label>
                            <input type="number" name="age" class="form-control" placeholder="e.g. 35">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category" class="form-select">
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Reservation Status</label>
                            <select name="reservation_status" class="form-select">
                                <?php foreach ($reservation_statuses as $resKey => $resLabel): ?>
                                    <option value="<?php echo htmlspecialchars($resKey); ?>"><?php echo htmlspecialchars($resLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="10-digit mobile">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tenure</label>
                            <input type="text" name="tenure" class="form-control" value="2021-2026">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address</label>
                            <textarea name="address" class="form-control" rows="2" placeholder="Village, Post, Thana..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Save Ward Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Ward Member Modal -->
<div class="modal fade" id="editMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom">
                <h5 class="modal-title fw-bold"><i class="fas fa-edit text-primary me-2"></i>Edit Ward Member Record</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="ward-member.php<?php echo !empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''; ?>" id="editMemberForm">
                <input type="hidden" name="action" value="edit_ward_member">
                <input type="hidden" name="id" id="edit_id">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Candidate Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" id="edit_candidate_name" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Father / Husband Name</label>
                            <input type="text" name="father_husband_name" id="edit_father_husband" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Ward Number <span class="text-danger">*</span></label>
                            <input type="text" name="ward_no" id="edit_ward_no" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">District <span class="text-danger">*</span></label>
                            <input type="text" name="district" id="edit_district" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Block <span class="text-danger">*</span></label>
                            <input type="text" name="block" id="edit_block" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Panchayat <span class="text-danger">*</span></label>
                            <input type="text" name="panchayat" id="edit_panchayat" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Gender</label>
                            <select name="gender" id="edit_gender" class="form-select">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Age</label>
                            <input type="number" name="age" id="edit_age" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Category</label>
                            <select name="category" id="edit_category" class="form-select">
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Reservation Status</label>
                            <select name="reservation_status" id="edit_reservation" class="form-select">
                                <?php foreach ($reservation_statuses as $resKey => $resLabel): ?>
                                    <option value="<?php echo htmlspecialchars($resKey); ?>"><?php echo htmlspecialchars($resLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Mobile</label>
                            <input type="text" name="mobile" id="edit_mobile" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Tenure</label>
                            <input type="text" name="tenure" id="edit_tenure" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">Address</label>
                            <textarea name="address" id="edit_address" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary fw-bold">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function loadBlocks(district) {
    const sel = document.getElementById('filter_block_select');
    sel.innerHTML = '<option value="">All Blocks</option>';
    if (!district) return;
    
    fetch(`ward-member.php?ajax=get_blocks&district=${encodeURIComponent(district)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.blocks) {
                data.blocks.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b;
                    opt.textContent = b;
                    sel.appendChild(opt);
                });
            }
        });
}

function loadPanchayats(district, block) {
    const sel = document.getElementById('filter_panchayat_select');
    sel.innerHTML = '<option value="">All Panchayats</option>';
    if (!district || !block) return;
    
    fetch(`ward-member.php?ajax=get_panchayats&district=${encodeURIComponent(district)}&block=${encodeURIComponent(block)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.panchayats) {
                data.panchayats.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    sel.appendChild(opt);
                });
            }
        });
}

function loadBlocksModal(prefix, district) {
    const sel = document.getElementById(`${prefix}_block`);
    sel.innerHTML = '<option value="">Select Block</option>';
    if (!district) return;
    
    fetch(`ward-member.php?ajax=get_blocks&district=${encodeURIComponent(district)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.blocks) {
                data.blocks.forEach(b => {
                    const opt = document.createElement('option');
                    opt.value = b;
                    opt.textContent = b;
                    sel.appendChild(opt);
                });
            }
        });
}

function loadPanchayatsModal(prefix, district, block) {
    const sel = document.getElementById(`${prefix}_panchayat`);
    sel.innerHTML = '<option value="">Select Panchayat</option>';
    if (!district || !block) return;
    
    fetch(`ward-member.php?ajax=get_panchayats&district=${encodeURIComponent(district)}&block=${encodeURIComponent(block)}`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.panchayats) {
                data.panchayats.forEach(p => {
                    const opt = document.createElement('option');
                    opt.value = p;
                    opt.textContent = p;
                    sel.appendChild(opt);
                });
            }
        });
}

function openEditModal(m) {
    document.getElementById('edit_id').value = m.id || '';
    document.getElementById('edit_candidate_name').value = m.candidate_name || '';
    document.getElementById('edit_father_husband').value = m.father_husband_name || '';
    document.getElementById('edit_ward_no').value = m.ward_no || m.territory_no || '';
    document.getElementById('edit_district').value = m.district || '';
    document.getElementById('edit_block').value = m.block || '';
    document.getElementById('edit_panchayat').value = m.panchayat || '';
    document.getElementById('edit_gender').value = m.gender || 'Male';
    document.getElementById('edit_age').value = m.age || '';
    document.getElementById('edit_category').value = m.category || 'सामान्य वर्ग';
    document.getElementById('edit_reservation').value = m.reservation_status || 'अनारक्षित (अन्य)';
    document.getElementById('edit_mobile').value = m.mobile || '';
    document.getElementById('edit_tenure').value = m.tenure || '2021-2026';
    document.getElementById('edit_address').value = m.address || '';
    
    const editModal = new bootstrap.Modal(document.getElementById('editMemberModal'));
    editModal.show();
}
</script>
</body>
</html>
