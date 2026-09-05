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
                    SELECT block FROM zila_parishad_members WHERE (district = ? OR district_slug = ?) AND block != ''
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

// Handle Add Member POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_zp_member' && $conn) {
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $territory_no = sanitize($_POST['territory_no'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $d_slug = slugify($district);

    if (!empty($c_name) && !empty($district) && !empty($territory_no)) {
        $stmt = $conn->prepare("INSERT INTO `zila_parishad_members` (`candidate_name`, `district`, `district_slug`, `block`, `territory_no`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("ssssssssss", $c_name, $district, $d_slug, $block, $territory_no, $gender, $category, $mobile, $address, $tenure);
            if ($stmt->execute()) {
                $message = "New Zila Parishad Territorial Member '" . htmlspecialchars($c_name) . "' (Territory No. " . htmlspecialchars($territory_no) . ") added successfully.";
            } else {
                $error = "Error adding member: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields (Member Name, District, and Territory No).";
    }
}

// Handle Edit Member POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_zp_member' && $conn) {
    $edit_id = (int)($_POST['id'] ?? 0);
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $territory_no = sanitize($_POST['territory_no'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $block = sanitize($_POST['block'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $d_slug = slugify($district);

    if ($edit_id > 0 && !empty($c_name)) {
        $stmt = $conn->prepare("UPDATE `zila_parishad_members` SET `candidate_name` = ?, `district` = ?, `district_slug` = ?, `block` = ?, `territory_no` = ?, `gender` = ?, `category` = ?, `mobile` = ?, `address` = ?, `tenure` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("ssssssssssi", $c_name, $district, $d_slug, $block, $territory_no, $gender, $category, $mobile, $address, $tenure, $edit_id);
            if ($stmt->execute()) {
                $message = "Zila Parishad Member record for '" . htmlspecialchars($c_name) . "' (Territory No. " . htmlspecialchars($territory_no) . ") updated successfully.";
            } else {
                $error = "Error updating member: " . $conn->error;
            }
        }
    } else {
        $error = "Please enter a valid member name.";
    }
}

// Handle Add Official POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_zp_official' && $conn) {
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $post = sanitize($_POST['post'] ?? 'अध्यक्ष (Chairperson)');
    $district = sanitize($_POST['district'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $d_slug = slugify($district);

    if (!empty($c_name) && !empty($district) && !empty($post)) {
        $stmt = $conn->prepare("INSERT INTO `zila_parishad_officials` (`candidate_name`, `post`, `district`, `district_slug`, `gender`, `category`, `mobile`, `address`, `tenure`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssssssss", $c_name, $post, $district, $d_slug, $gender, $category, $mobile, $address, $tenure);
            if ($stmt->execute()) {
                $message = "New Zila Parishad Official '" . htmlspecialchars($c_name) . "' (" . htmlspecialchars($post) . ") added successfully.";
            } else {
                $error = "Error adding official: " . $conn->error;
            }
        }
    } else {
        $error = "Please fill in all required fields (Official Name, Post, and District).";
    }
}

// Handle Edit Official POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_zp_official' && $conn) {
    $edit_id = (int)($_POST['id'] ?? 0);
    $c_name = sanitize($_POST['candidate_name'] ?? '');
    $post = sanitize($_POST['post'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $gender = sanitize($_POST['gender'] ?? 'Male');
    $category = sanitize($_POST['category'] ?? 'सामान्य वर्ग');
    $mobile = sanitize($_POST['mobile'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '2021-2026');
    $d_slug = slugify($district);

    if ($edit_id > 0 && !empty($c_name)) {
        $stmt = $conn->prepare("UPDATE `zila_parishad_officials` SET `candidate_name` = ?, `post` = ?, `district` = ?, `district_slug` = ?, `gender` = ?, `category` = ?, `mobile` = ?, `address` = ?, `tenure` = ? WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("sssssssssi", $c_name, $post, $district, $d_slug, $gender, $category, $mobile, $address, $tenure, $edit_id);
            if ($stmt->execute()) {
                $message = "Zila Parishad Official record for '" . htmlspecialchars($c_name) . "' (" . htmlspecialchars($post) . ") updated successfully.";
            } else {
                $error = "Error updating official: " . $conn->error;
            }
        }
    } else {
        $error = "Please enter a valid official name.";
    }
}

// Handle Delete Member
if (isset($_GET['delete_member_id']) && $conn) {
    $del_id = (int)$_GET['delete_member_id'];
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `zila_parishad_members` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Zila Parishad member record deleted successfully.";
            } else {
                $error = "Error deleting record.";
            }
        }
    }
}

// Handle Delete Official
if (isset($_GET['delete_official_id']) && $conn) {
    $del_id = (int)$_GET['delete_official_id'];
    if ($del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `zila_parishad_officials` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Zila Parishad official record deleted successfully.";
            } else {
                $error = "Error deleting record.";
            }
        }
    }
}

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';
$filter_category = isset($_GET['category']) ? sanitize($_GET['category']) : '';
$tab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'members';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$members = [];
$officials = [];
$total_members = 0;
$total_officials = 0;

$total_overall_members = 0;
$total_overall_officials = 0;
$total_districts_count = 0;
$total_blocks_count = 0;

$district_counts = [];
$block_counts = [];

$districts = DataProvider::getDistricts() ?: [];

if ($conn) {
    // 1. Overall Platform Stats
    $stat_m = $conn->query("SELECT COUNT(*) as total_m, COUNT(DISTINCT district) as total_d, COUNT(DISTINCT CONCAT(district, '___', block)) as total_b FROM `zila_parishad_members`");
    if ($stat_m && $row = $stat_m->fetch_assoc()) {
        $total_overall_members = (int)$row['total_m'];
        $total_districts_count = (int)$row['total_d'];
        $total_blocks_count = (int)$row['total_b'];
    }

    $stat_o = $conn->query("SELECT COUNT(*) as total_o FROM `zila_parishad_officials`");
    if ($stat_o && $row = $stat_o->fetch_assoc()) {
        $total_overall_officials = (int)$row['total_o'];
    }

    // 2. District-Wise Member Counts (All Districts)
    $d_res = $conn->query("SELECT district, COUNT(*) as member_count, COUNT(DISTINCT block) as block_count FROM `zila_parishad_members` WHERE district IS NOT NULL AND district != '' GROUP BY district ORDER BY district ASC");
    if ($d_res) {
        while ($row = $d_res->fetch_assoc()) {
            $district_counts[$row['district']] = $row;
        }
        ksort($district_counts, SORT_NATURAL | SORT_FLAG_CASE);
    }

    // 3. Block-Wise Counts for selected district (if district selected)
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $b_res = $conn->query("SELECT block, COUNT(*) as member_count, COUNT(DISTINCT territory_no) as territory_count FROM `zila_parishad_members` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND block != '' GROUP BY block ORDER BY block ASC");
        if ($b_res) {
            while ($row = $b_res->fetch_assoc()) {
                $block_counts[$row['block']] = $row;
            }
            ksort($block_counts, SORT_NATURAL | SORT_FLAG_CASE);
        }
    }

    // 4. Members Query
    $m_where = "1=1";
    if (!empty($search)) {
        $esc_q = $conn->real_escape_string($search);
        $m_where .= " AND (`candidate_name` LIKE '%$esc_q%' OR `block` LIKE '%$esc_q%' OR `territory_no` LIKE '%$esc_q%' OR `mobile` LIKE '%$esc_q%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $m_where .= " AND (`district` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_block)) {
        $esc_b = $conn->real_escape_string($filter_block);
        $m_where .= " AND `block` = '$esc_b'";
    }
    if (!empty($filter_category)) {
        $esc_c = $conn->real_escape_string($filter_category);
        $m_where .= " AND `category` = '$esc_c'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `zila_parishad_members` WHERE $m_where");
    if ($c_res) $total_members = (int)$c_res->fetch_assoc()['c'];

    $m_res = $conn->query("SELECT * FROM `zila_parishad_members` WHERE $m_where ORDER BY `district` ASC, `block` ASC, CAST(`territory_no` AS UNSIGNED) ASC, `territory_no` ASC, `candidate_name` ASC LIMIT $offset, $limit");
    if ($m_res) {
        while ($r = $m_res->fetch_assoc()) $members[] = $r;
    }

    // 5. Officials Query
    $o_where = "1=1";
    if (!empty($search)) {
        $esc_q = $conn->real_escape_string($search);
        $o_where .= " AND (`candidate_name` LIKE '%$esc_q%' OR `post` LIKE '%$esc_q%' OR `district` LIKE '%$esc_q%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $o_where .= " AND (`district` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_category)) {
        $esc_c = $conn->real_escape_string($filter_category);
        $o_where .= " AND `category` = '$esc_c'";
    }
    $o_res = $conn->query("SELECT * FROM `zila_parishad_officials` WHERE $o_where ORDER BY `district` ASC, `post` ASC, `candidate_name` ASC");
    if ($o_res) {
        while ($r = $o_res->fetch_assoc()) $officials[] = $r;
        $total_officials = count($officials);
    }
}

$total_pages = ceil($total_members / $limit);
$districts = DataProvider::getDistricts() ?: [];
usort($districts, function($a, $b) {
    return strcasecmp($a['name'], $b['name']);
});

$districts_zero_members = [];
foreach ($districts as $d) {
    $dName = $d['name'];
    if (empty($district_counts[$dName])) {
        $districts_zero_members[] = $dName;
    }
}
natcasesort($districts_zero_members);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Zila Parishad Directory — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Zila Parishad Councils</h1>
                <p class="text-muted mb-0">Elected Zila Parishad Territorial Members, Adhyaksh & Upadhyaksh across Bihar.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <?php if ($tab === 'members'): ?>
                    <button type="button" class="btn btn-danger fw-semibold rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addZpMemberModal">
                        <i class="fas fa-plus-circle me-1"></i> Add Territorial Member
                    </button>
                    <a href="bulk-upload.php?type=zp_members" class="btn btn-outline-danger fw-semibold rounded-3 shadow-sm bg-white">
                        <i class="fas fa-cloud-arrow-up me-1"></i> Bulk Upload CSV
                    </a>
                <?php else: ?>
                    <button type="button" class="btn btn-warning fw-semibold rounded-3 shadow-sm text-dark" data-bs-toggle="modal" data-bs-target="#addZpOfficialModal">
                        <i class="fas fa-plus-circle me-1"></i> Add Council Official
                    </button>
                    <a href="bulk-upload.php?type=zp_officials" class="btn btn-outline-warning fw-semibold rounded-3 shadow-sm bg-white text-dark">
                        <i class="fas fa-cloud-arrow-up me-1"></i> Bulk Upload CSV
                    </a>
                <?php endif; ?>
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

        <?php if (!empty($districts_zero_members)): ?>
            <!-- Zero Zila Parishad Member Data Gap Warning Card -->
            <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 p-3" role="alert">
                <div class="d-flex align-items-start gap-2">
                    <i class="fas fa-triangle-exclamation text-danger fs-4 mt-1"></i>
                    <div>
                        <h6 class="fw-bold mb-1 text-danger">Data Gap & Missing Report: <?php echo count($districts_zero_members); ?> Districts Have 0 Territorial Member Records</h6>
                        <p class="small text-muted mb-2">The following districts currently do not have Zila Parishad Territorial Member data seeded in the database:</p>
                        <div class="d-flex flex-wrap gap-1">
                            <?php foreach ($districts_zero_members as $zd): ?>
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
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Territorial Members</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_overall_members); ?></h3>
                        <small class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i><?php echo $total_overall_officials; ?> Council Officials</small>
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
                        <small class="text-primary fw-semibold">38 District Councils</small>
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
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($tab === 'members' ? $total_members : $total_officials); ?></h3>
                        <small class="text-info fw-semibold">
                            <?php if (!empty($filter_district) || !empty($filter_block) || !empty($search) || !empty($filter_category)): ?>
                                Filter active (<?php echo htmlspecialchars($filter_district ?: ($filter_block ?: 'Search')); ?>)
                            <?php else: ?>
                                Showing all <?php echo $tab === 'members' ? 'members' : 'officials'; ?>
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
                        <i class="fas fa-city me-2 text-danger"></i> District-Wise Territorial Member Count (38 Districts)
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark small">Click to filter by District</span>
                </div>
                <?php if (!empty($filter_district)): ?>
                    <a href="zila-parishad.php?tab=<?php echo $tab; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small">
                        <i class="fas fa-times me-1"></i> Clear District Filter
                    </a>
                <?php endif; ?>
            </div>
            <div class="section-card-body p-3">
                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                    <a href="zila-parishad.php?tab=<?php echo $tab; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                       class="btn btn-sm <?php echo empty($filter_district) ? 'btn-danger shadow-sm fw-bold' : 'btn-light border text-dark'; ?> rounded-pill py-1 px-3 district-pill-btn">
                        <span>All 38 Districts</span>
                        <span class="badge <?php echo empty($filter_district) ? 'bg-white text-danger' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?php echo number_format($total_overall_members); ?></span>
                    </a>
                    
                    <?php foreach ($districts as $d): 
                        $dName = $d['name'];
                        $dInfo = $district_counts[$dName] ?? null;
                        $dCount = $dInfo['member_count'] ?? 0;
                        $isActive = (strcasecmp($filter_district, $dName) === 0);
                        $isZero = ($dCount === 0);
                    ?>
                        <a href="zila-parishad.php?tab=<?php echo $tab; ?>&district=<?php echo urlencode($dName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo $isActive ? 'btn-danger shadow-sm fw-bold' : ($isZero ? 'btn-outline-danger' : 'btn-light border text-dark'); ?> rounded-pill py-1 px-2.5 district-pill-btn"
                           title="<?php echo htmlspecialchars($dName); ?>: <?php echo $dCount; ?> Territorial Members across <?php echo $dInfo['block_count'] ?? 0; ?> Blocks">
                            <?php if ($isZero): ?><i class="fas fa-triangle-exclamation text-danger me-1"></i><?php endif; ?>
                            <span><?php echo htmlspecialchars($dName); ?></span>
                            <span class="badge <?php echo $isActive ? 'bg-white text-danger' : ($isZero ? 'bg-danger text-white' : 'bg-danger-subtle text-danger border border-danger-subtle'); ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                <?php echo number_format($dCount); ?>
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
                            <i class="fas fa-cubes me-2"></i> Block-Wise Member Count in <?php echo htmlspecialchars($filter_district); ?> (<?php echo count($block_counts); ?> Blocks)
                        </h6>
                        <span class="badge bg-danger text-white rounded-pill px-2.5 py-0.5">
                            <?php echo number_format($district_counts[$filter_district]['member_count'] ?? $total_members); ?> Members
                        </span>
                    </div>
                    <?php if (!empty($filter_block)): ?>
                        <a href="zila-parishad.php?tab=<?php echo $tab; ?>&district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small bg-white">
                            <i class="fas fa-times me-1"></i> Clear Block Filter
                        </a>
                    <?php endif; ?>
                </div>
                <div class="section-card-body p-3">
                    <div class="d-flex flex-wrap gap-1.5 align-items-center">
                        <a href="zila-parishad.php?tab=<?php echo $tab; ?>&district=<?php echo urlencode($filter_district); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php echo empty($filter_block) ? 'btn-dark shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-3 block-pill-btn">
                            <span>All Blocks in <?php echo htmlspecialchars($filter_district); ?></span>
                            <span class="badge <?php echo empty($filter_block) ? 'bg-white text-dark' : 'bg-danger text-white'; ?> rounded-pill ms-1">
                                <?php echo number_format($district_counts[$filter_district]['member_count'] ?? $total_members); ?>
                            </span>
                        </a>

                        <?php foreach ($block_counts as $bName => $bInfo): ?>
                            <?php $isBlockActive = (strcasecmp($filter_block, $bName) === 0); ?>
                            <a href="zila-parishad.php?tab=<?php echo $tab; ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($bName); ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                               class="btn btn-sm <?php echo $isBlockActive ? 'btn-danger shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-2.5 block-pill-btn">
                                <span><?php echo htmlspecialchars($bName); ?></span>
                                <span class="badge <?php echo $isBlockActive ? 'bg-white text-danger' : 'bg-danger-subtle text-danger border border-danger-subtle'; ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                    <?php echo number_format($bInfo['member_count']); ?>
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
                <form method="GET" action="zila-parishad.php" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                    <div class="col-lg-3 col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search member, post or territory..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <select name="district" class="form-select" onchange="this.form.submit()">
                            <option value="">All Districts (<?php echo number_format($total_overall_members); ?>)</option>
                            <?php foreach ($district_counts as $dName => $dInfo): ?>
                                <option value="<?php echo htmlspecialchars($dName); ?>" <?php echo (strcasecmp($filter_district, $dName) === 0) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dName); ?> (<?php echo number_format($dInfo['member_count']); ?>)
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
                                        <?php echo htmlspecialchars($bName); ?> (<?php echo number_format($bInfo['member_count']); ?>)
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
                    <div class="col-lg-2 col-md-4 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="zila-parishad.php?tab=<?php echo $tab; ?>" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-pills mb-4 gap-2">
            <li class="nav-item">
                <a class="nav-link px-4 py-2 fw-semibold rounded-3 <?php echo ($tab === 'members') ? 'active bg-danger' : 'bg-white border text-dark'; ?>" href="zila-parishad.php?tab=members<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($filter_block) ? '&block=' . urlencode($filter_block) : ''; ?><?php echo !empty($filter_category) ? '&category=' . urlencode($filter_category) : ''; ?>">
                    <i class="fas fa-users me-1"></i> Territorial Members (<?php echo number_format($total_members); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-2 fw-semibold rounded-3 <?php echo ($tab === 'officials') ? 'active bg-danger' : 'bg-white border text-dark'; ?>" href="zila-parishad.php?tab=officials<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($filter_category) ? '&category=' . urlencode($filter_category) : ''; ?>">
                    <i class="fas fa-crown me-1"></i> Council Officials & Adhyaksh (<?php echo count($officials); ?>)
                </a>
            </li>
        </ul>

        <?php if ($tab === 'members'): ?>
            <!-- Members Table -->
            <div class="section-card">
                <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users me-2 text-danger"></i> Territorial Members Roster</h6>
                    <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?> (<?php echo number_format($total_members); ?> Total)</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>District</th>
                                <th>Block</th>
                                <th>Territory No.</th>
                                <th>Reservation Category</th>
                                <th>Mobile</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($m['candidate_name']); ?></div>
                                            <?php if (!empty($m['address'])): ?>
                                                <small class="text-muted text-truncate d-block" style="max-width: 200px;"><i class="fas fa-map-marker-alt me-1"></i><?php echo htmlspecialchars($m['address']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="fw-semibold"><?php echo htmlspecialchars($m['district']); ?></span></td>
                                        <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($m['block'] ?? '—'); ?></span></td>
                                        <td><span class="badge bg-danger">Territory No. <?php echo htmlspecialchars($m['territory_no']); ?></span></td>
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
                                                <a href="tel:<?php echo htmlspecialchars($m['mobile']); ?>" class="text-decoration-none small fw-semibold text-dark">
                                                    <i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($m['mobile']); ?>
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-light border text-primary edit-zp-member-btn"
                                                    data-id="<?php echo $m['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($m['candidate_name']); ?>"
                                                    data-mobile="<?php echo htmlspecialchars($m['mobile'] ?? ''); ?>"
                                                    data-category="<?php echo htmlspecialchars($m['category'] ?? 'सामान्य वर्ग'); ?>"
                                                    data-gender="<?php echo htmlspecialchars($m['gender'] ?? 'Male'); ?>"
                                                    data-territory="<?php echo htmlspecialchars($m['territory_no'] ?? ''); ?>"
                                                    data-district="<?php echo htmlspecialchars($m['district'] ?? ''); ?>"
                                                    data-block="<?php echo htmlspecialchars($m['block'] ?? ''); ?>"
                                                    data-address="<?php echo htmlspecialchars($m['address'] ?? ''); ?>"
                                                    data-tenure="<?php echo htmlspecialchars($m['tenure'] ?? '2021-2026'); ?>"
                                                    title="Edit Member">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </button>
                                                <?php if (!empty($m['mobile'])): ?>
                                                    <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $m['mobile']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Contact">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="zila-parishad.php?tab=members&delete_member_id=<?php echo $m['id']; ?>&district=<?php echo urlencode($filter_district); ?>&page=<?php echo $page; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this Zila Parishad member record?');" title="Delete">
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
                                        No Zila Parishad members found for this filter.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small class="text-muted">Showing <?php echo count($members); ?> of <?php echo number_format($total_members); ?> entries</small>
                        <nav>
                            <ul class="pagination pagination-sm mb-0">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?tab=members&page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>">Prev</a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?tab=members&page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>

                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?tab=members&page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>">Next</a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <!-- Officials Table -->
            <div class="section-card">
                <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-crown me-2 text-warning"></i> Zila Parishad Adhyaksh & Upadhyaksh</h6>
                    <span class="badge bg-light text-muted border"><?php echo count($officials); ?> Total Officials</span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Official Name</th>
                                <th>Post / Designation</th>
                                <th>District</th>
                                <th>Reservation Category</th>
                                <th>Address</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($officials)): ?>
                                <?php foreach ($officials as $o): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($o['candidate_name']); ?></div>
                                            <?php if (!empty($o['mobile'])): ?>
                                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($o['mobile']); ?></small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark px-2.5 py-1 fw-bold">
                                                <?php echo htmlspecialchars($o['post']); ?>
                                            </span>
                                        </td>
                                        <td><span class="fw-bold"><?php echo htmlspecialchars($o['district']); ?></span></td>
                                        <td>
                                            <?php
                                            $catVal = $o['category'] ?? 'सामान्य वर्ग';
                                            $catBadgeClass = 'bg-secondary text-white';
                                            if ($catVal === 'सामान्य वर्ग') $catBadgeClass = 'bg-dark text-white';
                                            elseif (str_contains($catVal, 'अनुसूची-I')) $catBadgeClass = 'bg-info text-dark';
                                            elseif (str_contains($catVal, 'अनुसूची-II')) $catBadgeClass = 'bg-primary text-white';
                                            elseif (str_contains($catVal, 'जाति')) $catBadgeClass = 'bg-warning text-dark';
                                            elseif (str_contains($catVal, 'जनजाति')) $catBadgeClass = 'bg-danger text-white';
                                            ?>
                                            <span class="badge <?php echo $catBadgeClass; ?> rounded-pill px-2.5 py-1 font-monospace" style="font-size:0.75rem;">
                                                <?php echo htmlspecialchars($catVal ?: 'सामान्य वर्ग'); ?>
                                            </span>
                                        </td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($o['address'] ?? '—'); ?></small></td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-light border text-primary edit-zp-official-btn"
                                                    data-id="<?php echo $o['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($o['candidate_name']); ?>"
                                                    data-post="<?php echo htmlspecialchars($o['post'] ?? ''); ?>"
                                                    data-district="<?php echo htmlspecialchars($o['district'] ?? ''); ?>"
                                                    data-category="<?php echo htmlspecialchars($o['category'] ?? 'सामान्य वर्ग'); ?>"
                                                    data-gender="<?php echo htmlspecialchars($o['gender'] ?? 'Male'); ?>"
                                                    data-mobile="<?php echo htmlspecialchars($o['mobile'] ?? ''); ?>"
                                                    data-address="<?php echo htmlspecialchars($o['address'] ?? ''); ?>"
                                                    data-tenure="<?php echo htmlspecialchars($o['tenure'] ?? '2021-2026'); ?>"
                                                    title="Edit Official">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </button>
                                                <?php if (!empty($o['mobile'])): ?>
                                                    <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $o['mobile']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Contact">
                                                        <i class="fab fa-whatsapp"></i>
                                                    </a>
                                                <?php endif; ?>
                                                <a href="zila-parishad.php?tab=officials&delete_official_id=<?php echo $o['id']; ?>&district=<?php echo urlencode($filter_district); ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this Zila Parishad official record?');" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-folder-open fa-3x mb-3 d-block text-muted opacity-50"></i>
                                        No Zila Parishad officials listed yet.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<!-- Modal: Add New ZP Member -->
<div class="modal fade" id="addZpMemberModal" tabindex="-1" aria-labelledby="addZpMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="zila-parishad.php?tab=members<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>">
                <input type="hidden" name="action" value="add_zp_member">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="addZpMemberModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-plus-circle me-2 text-danger"></i> Add Zila Parishad Territorial Member
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Member Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" class="form-control" required placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Territory No. <span class="text-danger">*</span></label>
                            <input type="text" name="territory_no" class="form-control" required placeholder="e.g. 1, 2, 3">
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
                            <select name="district" id="add_zpm_district" class="form-select" required onchange="fetchZpBlocks(this.value, document.getElementById('add_zpm_block'))">
                                <option value="">Select District</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">CD Block <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="add_zpm_block_toggle" onclick="toggleZpBlockInput('add')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="add_zpm_block_select_wrap">
                                <select name="block" id="add_zpm_block" class="form-select" required>
                                    <option value="">Select District First</option>
                                </select>
                            </div>
                            <div id="add_zpm_block_custom_wrap" class="d-none">
                                <input type="text" id="add_zpm_block_custom" class="form-control" placeholder="Enter block name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Registered Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Village, Post, Block, District">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">
                        <i class="fas fa-save me-1"></i> Save Member Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit ZP Member -->
<div class="modal fade" id="editZpMemberModal" tabindex="-1" aria-labelledby="editZpMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="zila-parishad.php?tab=members<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>">
                <input type="hidden" name="action" value="edit_zp_member">
                <input type="hidden" name="id" id="edit_zpm_id">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="editZpMemberModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-user-pen me-2 text-danger"></i> Edit Zila Parishad Territorial Member
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Member Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" id="edit_zpm_name" class="form-control" required placeholder="Full candidate name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Territory No. <span class="text-danger">*</span></label>
                            <input type="text" name="territory_no" id="edit_zpm_territory" class="form-control" required placeholder="e.g. 1, 2, 3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category <span class="text-danger">*</span></label>
                            <select name="category" id="edit_zpm_category" class="form-select" required>
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="edit_zpm_gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="edit_zpm_district" class="form-select" required onchange="fetchZpBlocks(this.value, document.getElementById('edit_zpm_block'))">
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-bold text-dark mb-0">CD Block <span class="text-danger">*</span></label>
                                <a href="javascript:void(0)" class="text-decoration-none small text-muted" id="edit_zpm_block_toggle" onclick="toggleZpBlockInput('edit')">
                                    <i class="fas fa-pen-to-square me-1"></i>Custom
                                </a>
                            </div>
                            <div id="edit_zpm_block_select_wrap">
                                <select name="block" id="edit_zpm_block" class="form-select" required>
                                    <option value="">Select District First</option>
                                </select>
                            </div>
                            <div id="edit_zpm_block_custom_wrap" class="d-none">
                                <input type="text" id="edit_zpm_block_custom" class="form-control" placeholder="Enter block name">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" id="edit_zpm_mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" id="edit_zpm_tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Registered Address</label>
                            <input type="text" name="address" id="edit_zpm_address" class="form-control" placeholder="Village, Post, Block, District">
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

<!-- Modal: Add New ZP Official -->
<div class="modal fade" id="addZpOfficialModal" tabindex="-1" aria-labelledby="addZpOfficialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="zila-parishad.php?tab=officials<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>">
                <input type="hidden" name="action" value="add_zp_official">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="addZpOfficialModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-crown me-2 text-warning"></i> Add Zila Parishad Official (Adhyaksh / Upadhyaksh)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Official Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" class="form-control" required placeholder="Full official name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Post / Designation <span class="text-danger">*</span></label>
                            <select name="post" class="form-select" required>
                                <option value="अध्यक्ष (Chairperson)">अध्यक्ष (Chairperson)</option>
                                <option value="उपाध्यक्ष (Vice-Chairperson)">उपाध्यक्ष (Vice-Chairperson)</option>
                            </select>
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
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" class="form-select" required>
                                <option value="">Select District</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Residential Address</label>
                            <input type="text" name="address" class="form-control" placeholder="Address details">
                        </div>
                    </div>
                </div>
                
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark">
                        <i class="fas fa-save me-1"></i> Save Official Record
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit ZP Official -->
<div class="modal fade" id="editZpOfficialModal" tabindex="-1" aria-labelledby="editZpOfficialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="zila-parishad.php?tab=officials<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>">
                <input type="hidden" name="action" value="edit_zp_official">
                <input type="hidden" name="id" id="edit_zpo_id">
                
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-dark" id="editZpOfficialModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-crown me-2 text-warning"></i> Edit Zila Parishad Official (Adhyaksh / Upadhyaksh)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Official Name <span class="text-danger">*</span></label>
                            <input type="text" name="candidate_name" id="edit_zpo_name" class="form-control" required placeholder="Full official name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Post / Designation <span class="text-danger">*</span></label>
                            <select name="post" id="edit_zpo_post" class="form-select" required>
                                <option value="अध्यक्ष (Chairperson)">अध्यक्ष (Chairperson)</option>
                                <option value="उपाध्यक्ष (Vice-Chairperson)">उपाध्यक्ष (Vice-Chairperson)</option>
                                <option value="Chairperson">Chairperson</option>
                                <option value="Vice-Chairperson">Vice-Chairperson</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Reservation Category <span class="text-danger">*</span></label>
                            <select name="category" id="edit_zpo_category" class="form-select" required>
                                <?php foreach ($reservation_categories as $catKey => $catLabel): ?>
                                    <option value="<?php echo htmlspecialchars($catKey); ?>"><?php echo htmlspecialchars($catLabel); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Gender <span class="text-danger">*</span></label>
                            <select name="gender" id="edit_zpo_gender" class="form-select">
                                <option value="Male">Male (पुरुष)</option>
                                <option value="Female">Female (महिला)</option>
                                <option value="Other">Other (अन्य)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">District <span class="text-danger">*</span></label>
                            <select name="district" id="edit_zpo_district" class="form-select" required>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Mobile Number</label>
                            <input type="text" name="mobile" id="edit_zpo_mobile" class="form-control" placeholder="10-digit mobile number">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Tenure / Term</label>
                            <input type="text" name="tenure" id="edit_zpo_tenure" class="form-control" value="2021-2026" placeholder="e.g. 2021-2026">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Residential Address</label>
                            <input type="text" name="address" id="edit_zpo_address" class="form-control" placeholder="Address details">
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

<!-- Modal: Complete Bihar District & Block Matrix -->
<div class="modal fade" id="matrixModal" tabindex="-1" aria-labelledby="matrixModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="matrixModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-table-cells me-2 text-danger"></i> Complete Bihar Zila Parishad: District & Block Matrix
                    </h5>
                    <small class="text-muted">Total <?php echo number_format($total_overall_members); ?> Territorial Members across 38 Districts and <?php echo number_format($total_blocks_count); ?> Blocks</small>
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
                                <th class="text-center" style="width: 20%;">Territorial Members</th>
                                <th class="text-end" style="width: 30%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; foreach ($districts as $d): 
                                $dName = $d['name'];
                                $dInfo = $district_counts[$dName] ?? null;
                                $mCount = $dInfo['member_count'] ?? 0;
                                $bCount = $dInfo['block_count'] ?? 0;
                                $isZero = ($mCount === 0);
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
                                        <span class="badge <?php echo $isZero ? 'bg-danger text-white' : 'bg-danger rounded-pill px-3 py-1.5 font-monospace fs-7'; ?>">
                                            <?php echo number_format($mCount); ?> Members
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!$isZero): ?>
                                            <a href="zila-parishad.php?tab=members&district=<?php echo urlencode($dName); ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                                <i class="fas fa-filter me-1"></i> View District & Members
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
// Dynamic Cascading AJAX & Dropdown Managers
function fetchZpBlocks(district, blockSelectEl, selectedBlock = '', callback = null) {
    if (!blockSelectEl) return;
    blockSelectEl.innerHTML = '<option value="">Loading blocks...</option>';
    blockSelectEl.disabled = true;

    if (!district) {
        blockSelectEl.innerHTML = '<option value="">Select District First</option>';
        blockSelectEl.disabled = false;
        if (callback) callback();
        return;
    }

    fetch(`zila-parishad.php?ajax=get_blocks&district=${encodeURIComponent(district)}`)
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

function toggleZpBlockInput(prefix) {
    const selWrap = document.getElementById(`${prefix}_zpm_block_select_wrap`);
    const customWrap = document.getElementById(`${prefix}_zpm_block_custom_wrap`);
    const sel = document.getElementById(`${prefix}_zpm_block`);
    const custom = document.getElementById(`${prefix}_zpm_block_custom`);
    const toggleBtn = document.getElementById(`${prefix}_zpm_block_toggle`);

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
    const editMemberModalEl = document.getElementById('editZpMemberModal');
    const editOfficialModalEl = document.getElementById('editZpOfficialModal');
    const addMemberDistrictEl = document.getElementById('add_zpm_district');
    const addMemberBlockEl = document.getElementById('add_zpm_block');
    
    // Pre-populate Add Member Block if District is selected
    if (addMemberDistrictEl && addMemberDistrictEl.value && addMemberBlockEl) {
        fetchZpBlocks(addMemberDistrictEl.value, addMemberBlockEl);
    }

    if (editMemberModalEl) {
        const editMemberModal = new bootstrap.Modal(editMemberModalEl);
        document.querySelectorAll('.edit-zp-member-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const districtVal = this.dataset.district || '';
                const blockVal = this.dataset.block || '';

                document.getElementById('edit_zpm_id').value = this.dataset.id || '';
                document.getElementById('edit_zpm_name').value = this.dataset.name || '';
                document.getElementById('edit_zpm_mobile').value = this.dataset.mobile || '';
                document.getElementById('edit_zpm_category').value = this.dataset.category || 'सामान्य वर्ग';
                document.getElementById('edit_zpm_gender').value = this.dataset.gender || 'Male';
                document.getElementById('edit_zpm_territory').value = this.dataset.territory || '';
                document.getElementById('edit_zpm_district').value = districtVal;
                document.getElementById('edit_zpm_address').value = this.dataset.address || '';
                document.getElementById('edit_zpm_tenure').value = this.dataset.tenure || '2021-2026';
                
                // Fetch blocks and set selected block
                const editBlockEl = document.getElementById('edit_zpm_block');
                fetchZpBlocks(districtVal, editBlockEl, blockVal);

                editMemberModal.show();
            });
        });
    }

    if (editOfficialModalEl) {
        const editOfficialModal = new bootstrap.Modal(editOfficialModalEl);
        document.querySelectorAll('.edit-zp-official-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.getElementById('edit_zpo_id').value = this.dataset.id || '';
                document.getElementById('edit_zpo_name').value = this.dataset.name || '';
                document.getElementById('edit_zpo_post').value = this.dataset.post || 'अध्यक्ष (Chairperson)';
                document.getElementById('edit_zpo_district').value = this.dataset.district || '';
                document.getElementById('edit_zpo_category').value = this.dataset.category || 'सामान्य वर्ग';
                document.getElementById('edit_zpo_gender').value = this.dataset.gender || 'Male';
                document.getElementById('edit_zpo_mobile').value = this.dataset.mobile || '';
                document.getElementById('edit_zpo_address').value = this.dataset.address || '';
                document.getElementById('edit_zpo_tenure').value = this.dataset.tenure || '2021-2026';
                
                editOfficialModal.show();
            });
        });
    }
});
</script>

</body>
</html>
