<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';
$filter_gap = isset($_GET['gap']) ? sanitize($_GET['gap']) : ''; // 'no_mukhiya', 'no_sarpanch', 'no_data', 'complete'

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$panchayats = [];
$total_rows = 0;
$total_overall_panchayats = 0;

$districts_all = DataProvider::getDistricts() ?: [];
usort($districts_all, function($a, $b) {
    return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
});

// Data Health & Gap Analytics
$district_stats = [];
$districts_zero_data = [];
$districts_missing_mukhiya_all = [];
$districts_missing_sarpanch_all = [];
$total_missing_mukhiyas = 0;
$total_missing_sarpanchs = 0;
$block_stats = [];

if ($conn) {
    // 1. Overall stats
    $tot_p_res = $conn->query("SELECT COUNT(*) as c FROM `panchayats`");
    if ($tot_p_res) $total_overall_panchayats = (int)$tot_p_res->fetch_assoc()['c'];

    $tot_m_gap = $conn->query("SELECT COUNT(*) as c FROM `panchayats` WHERE current_mukhiya IS NULL OR current_mukhiya = '' OR current_mukhiya = '—'");
    if ($tot_m_gap) $total_missing_mukhiyas = (int)$tot_m_gap->fetch_assoc()['c'];

    $tot_s_gap = $conn->query("SELECT COUNT(*) as c FROM `panchayats` WHERE current_sarpanch IS NULL OR current_sarpanch = '' OR current_sarpanch = '—'");
    if ($tot_s_gap) $total_missing_sarpanchs = (int)$tot_s_gap->fetch_assoc()['c'];

    // 2. Audit every district from official 38 districts list
    foreach ($districts_all as $d) {
        $dName = $d['name'];
        $dSlug = $d['slug'];
        $esc_d = $conn->real_escape_string($dName);
        $esc_slug = $conn->real_escape_string($dSlug);

        $d_p_res = $conn->query("SELECT 
            COUNT(*) as total_p, 
            COUNT(DISTINCT block) as total_b,
            SUM(CASE WHEN current_mukhiya IS NULL OR current_mukhiya = '' OR current_mukhiya = '—' THEN 1 ELSE 0 END) as no_m,
            SUM(CASE WHEN current_sarpanch IS NULL OR current_sarpanch = '' OR current_sarpanch = '—' THEN 1 ELSE 0 END) as no_s
            FROM `panchayats` WHERE district = '$esc_d' OR district_slug = '$esc_slug'");
        
        $pRow = $d_p_res ? $d_p_res->fetch_assoc() : ['total_p' => 0, 'total_b' => 0, 'no_m' => 0, 'no_s' => 0];
        $totalP = (int)($pRow['total_p'] ?? 0);
        $totalB = (int)($pRow['total_b'] ?? 0);
        $noM = (int)($pRow['no_m'] ?? 0);
        $noS = (int)($pRow['no_s'] ?? 0);

        $status = 'COMPLETE';
        if ($totalP === 0) {
            $status = 'NO_DATA';
            $districts_zero_data[] = $dName;
        } elseif ($noM === $totalP) {
            $status = 'NO_MUKHIYA';
            $districts_missing_mukhiya_all[] = ['name' => $dName, 'count' => $totalP];
        } elseif ($noS === $totalP) {
            $status = 'NO_SARPANCH';
            $districts_missing_sarpanch_all[] = ['name' => $dName, 'count' => $totalP];
        } elseif ($noM > 0 || $noS > 0) {
            $status = 'PARTIAL';
        }

        $district_stats[$dName] = [
            'slug' => $dSlug,
            'total_panchayats' => $totalP,
            'total_blocks' => $totalB,
            'missing_mukhiya' => $noM,
            'missing_sarpanch' => $noS,
            'status' => $status
        ];
    }

    // Sort all district stats and warning arrays alphabetically
    ksort($district_stats, SORT_NATURAL | SORT_FLAG_CASE);
    natcasesort($districts_zero_data);
    usort($districts_missing_mukhiya_all, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });
    usort($districts_missing_sarpanch_all, function($a, $b) {
        return strcasecmp($a['name'], $b['name']);
    });

    // 3. Block-Wise Audit for Selected District
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $b_res = $conn->query("SELECT 
            block, 
            COUNT(*) as total_p,
            SUM(CASE WHEN current_mukhiya IS NULL OR current_mukhiya = '' OR current_mukhiya = '—' THEN 1 ELSE 0 END) as no_m,
            SUM(CASE WHEN current_sarpanch IS NULL OR current_sarpanch = '' OR current_sarpanch = '—' THEN 1 ELSE 0 END) as no_s
            FROM `panchayats` WHERE (`district` = '$esc_d' OR `district_slug` = '$esc_d') AND block != ''
            GROUP BY block ORDER BY block ASC");
        
        if ($b_res) {
            while ($bRow = $b_res->fetch_assoc()) {
                $block_stats[$bRow['block']] = [
                    'total_panchayats' => (int)$bRow['total_p'],
                    'missing_mukhiya' => (int)$bRow['no_m'],
                    'missing_sarpanch' => (int)$bRow['no_s']
                ];
            }
        }
        ksort($block_stats, SORT_NATURAL | SORT_FLAG_CASE);
    }

    // 4. Build Query with Gap & Text Filters
    $where = "1=1";
    if (!empty($search)) {
        $esc_q = $conn->real_escape_string($search);
        $where .= " AND (`panchayat_name` LIKE '%$esc_q%' OR `block` LIKE '%$esc_q%' OR `current_mukhiya` LIKE '%$esc_q%' OR `current_sarpanch` LIKE '%$esc_q%' OR `district` LIKE '%$esc_q%')";
    }
    if (!empty($filter_district)) {
        $esc_d = $conn->real_escape_string($filter_district);
        $where .= " AND (`district` = '$esc_d' OR `district_slug` = '$esc_d')";
    }
    if (!empty($filter_block)) {
        $esc_b = $conn->real_escape_string($filter_block);
        $where .= " AND `block` = '$esc_b'";
    }

    if ($filter_gap === 'no_mukhiya') {
        $where .= " AND (`current_mukhiya` IS NULL OR `current_mukhiya` = '' OR `current_mukhiya` = '—')";
    } elseif ($filter_gap === 'no_sarpanch') {
        $where .= " AND (`current_sarpanch` IS NULL OR `current_sarpanch` = '' OR `current_sarpanch` = '—')";
    } elseif ($filter_gap === 'no_both') {
        $where .= " AND (`current_mukhiya` IS NULL OR `current_mukhiya` = '' OR `current_mukhiya` = '—') AND (`current_sarpanch` IS NULL OR `current_sarpanch` = '' OR `current_sarpanch` = '—')";
    } elseif ($filter_gap === 'complete') {
        $where .= " AND (`current_mukhiya` IS NOT NULL AND `current_mukhiya` != '' AND `current_mukhiya` != '—') AND (`current_sarpanch` IS NOT NULL AND `current_sarpanch` != '' AND `current_sarpanch` != '—')";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `panchayats` WHERE $where");
    if ($c_res) $total_rows = (int)$c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `panchayats` WHERE $where ORDER BY `district` ASC, `block` ASC, `panchayat_name` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $panchayats[] = $r;
        }
    }
}

$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panchayats Directory & Data Health Audit — Bihar Election Admin</title>
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
        .gap-pill-btn {
            transition: all 0.15s ease-in-out;
            font-size: 0.8rem;
            text-decoration: none;
        }
        .gap-pill-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
        }
        .warning-callout {
            border-left: 4px solid #dc3545;
            background: #fff5f5;
            border-radius: 8px;
            padding: 1rem 1.25rem;
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Panchayats Leadership & Data Health Audit</h1>
                <p class="text-muted mb-0">Monitor Gram Panchayat coverage, missing Mukhiya/Sarpanch leadership records, and district data health.</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-outline-danger fw-semibold rounded-3 shadow-sm bg-white" data-bs-toggle="modal" data-bs-target="#auditModal">
                    <i class="fas fa-stethoscope me-1"></i> Full 38 Districts Data Gap Audit
                </button>
            </div>
        </div>

        <!-- Comprehensive Data Quality & Gap Alert Warnings -->
        <?php if (!empty($districts_zero_data) || $total_missing_mukhiyas > 0 || $total_missing_sarpanchs > 0): ?>
            <div class="warning-callout shadow-sm mb-4 border">
                <div class="d-flex align-items-start gap-3 flex-wrap">
                    <div class="text-danger fs-3 mt-1">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold text-danger mb-2 fs-6">
                            Data Health Audit Warnings Detected:
                        </h6>
                        <div class="row g-2 small">
                            <?php if (!empty($districts_zero_data)): ?>
                                <div class="col-lg-6">
                                    <div class="p-2 bg-white rounded border border-danger border-opacity-25">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-danger fw-bold">
                                                <i class="fas fa-circle-xmark me-1"></i> Districts with 0 Panchayats Recorded:
                                            </span>
                                            <span class="badge bg-danger rounded-pill"><?php echo count($districts_zero_data); ?> Districts</span>
                                        </div>
                                        <div class="mt-1">
                                            <?php foreach ($districts_zero_data as $zd): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 me-1">
                                                    <?php echo htmlspecialchars($zd); ?> (0 Records)
                                                </span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($districts_missing_mukhiya_all)): ?>
                                <div class="col-lg-6">
                                    <div class="p-2 bg-white rounded border border-warning border-opacity-50">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="text-dark fw-bold">
                                                <i class="fas fa-user-xmark text-warning me-1"></i> Districts with 0 Mukhiyas Assigned:
                                            </span>
                                            <span class="badge bg-warning text-dark rounded-pill"><?php echo count($districts_missing_mukhiya_all); ?> District</span>
                                        </div>
                                        <div class="mt-1">
                                            <?php foreach ($districts_missing_mukhiya_all as $dmm): ?>
                                                <a href="panchayats.php?district=<?php echo urlencode($dmm['name']); ?>&gap=no_mukhiya" class="badge bg-warning bg-opacity-25 text-dark border border-warning text-decoration-none me-1">
                                                    <?php echo htmlspecialchars($dmm['name']); ?> (All <?php echo $dmm['count']; ?> Panchayats Missing Mukhiya)
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($districts_missing_sarpanch_all)): ?>
                                <div class="col-12">
                                    <div class="p-2 bg-white rounded border border-info border-opacity-50">
                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                                            <span class="text-dark fw-bold">
                                                <i class="fas fa-scale-unbalanced text-info me-1"></i> Districts with 100% Sarpanch Roster Missing:
                                            </span>
                                            <span class="badge bg-info text-dark rounded-pill"><?php echo count($districts_missing_sarpanch_all); ?> Districts</span>
                                        </div>
                                        <div class="mt-1 d-flex flex-wrap gap-1">
                                            <?php foreach ($districts_missing_sarpanch_all as $dms): ?>
                                                <a href="panchayats.php?district=<?php echo urlencode($dms['name']); ?>&gap=no_sarpanch" class="badge bg-light text-dark border text-decoration-none">
                                                    <?php echo htmlspecialchars($dms['name']); ?> <span class="text-danger fw-bold ms-1">(<?php echo $dms['count']; ?>)</span>
                                                </a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- KPI Metrics Grid -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Total Gram Panchayats</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_overall_panchayats); ?></h3>
                        <small class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i>36 / 38 Districts Recorded</small>
                    </div>
                    <div class="stat-card-icon bg-success bg-opacity-10 text-success">
                        <i class="fas fa-tree-city"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Missing Mukhiya Record</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-danger" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_missing_mukhiyas); ?></h3>
                        <a href="panchayats.php?gap=no_mukhiya" class="small text-danger fw-semibold text-decoration-none">
                            <i class="fas fa-filter me-1"></i> Filter Missing Mukhiya
                        </a>
                    </div>
                    <div class="stat-card-icon bg-danger bg-opacity-10 text-danger">
                        <i class="fas fa-user-xmark"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Missing Sarpanch Record</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-warning text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_missing_sarpanchs); ?></h3>
                        <a href="panchayats.php?gap=no_sarpanch" class="small text-warning text-dark fw-semibold text-decoration-none">
                            <i class="fas fa-filter me-1"></i> Filter Missing Sarpanch
                        </a>
                    </div>
                    <div class="stat-card-icon bg-warning bg-opacity-10 text-warning">
                        <i class="fas fa-scale-unbalanced"></i>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-lg-3">
                <div class="stat-card-custom d-flex align-items-center justify-content-between">
                    <div>
                        <small class="text-muted text-uppercase fw-bold" style="font-size:0.72rem;">Current Query Count</small>
                        <h3 class="fw-extrabold mb-0 mt-1 text-dark" style="font-family: 'Outfit', sans-serif;"><?php echo number_format($total_rows); ?></h3>
                        <small class="text-muted fw-semibold">Matching Panchayats</small>
                    </div>
                    <div class="stat-card-icon bg-primary bg-opacity-10 text-primary">
                        <i class="fas fa-filter"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Data Health Filter Tabs -->
        <div class="card border-0 shadow-sm rounded-4 p-2 mb-4 bg-white">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="small fw-bold text-muted ps-2 me-1">Filter by Data Health:</span>
                <a href="panchayats.php<?php echo !empty($filter_district) ? '?district=' . urlencode($filter_district) : ''; ?>" 
                   class="btn btn-sm <?php echo empty($filter_gap) ? 'btn-dark fw-bold' : 'btn-light border'; ?> rounded-pill px-3 py-1">
                    <span>All Records (<?php echo number_format($total_overall_panchayats); ?>)</span>
                </a>
                <a href="panchayats.php?gap=no_mukhiya<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?>" 
                   class="btn btn-sm <?php echo ($filter_gap === 'no_mukhiya') ? 'btn-danger fw-bold' : 'btn-outline-danger'; ?> rounded-pill px-3 py-1">
                    <i class="fas fa-triangle-exclamation me-1"></i> Missing Mukhiya (<?php echo number_format($total_missing_mukhiyas); ?>)
                </a>
                <a href="panchayats.php?gap=no_sarpanch<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?>" 
                   class="btn btn-sm <?php echo ($filter_gap === 'no_sarpanch') ? 'btn-warning text-dark fw-bold' : 'btn-outline-warning text-dark'; ?> rounded-pill px-3 py-1">
                    <i class="fas fa-triangle-exclamation me-1"></i> Missing Sarpanch (<?php echo number_format($total_missing_sarpanchs); ?>)
                </a>
                <a href="panchayats.php?gap=complete<?php echo !empty($filter_district) ? '&district=' . urlencode($filter_district) : ''; ?>" 
                   class="btn btn-sm <?php echo ($filter_gap === 'complete') ? 'btn-success fw-bold' : 'btn-outline-success'; ?> rounded-pill px-3 py-1">
                    <i class="fas fa-check-circle me-1"></i> 100% Complete Data (<?php echo number_format($total_overall_panchayats - max($total_missing_mukhiyas, $total_missing_sarpanchs)); ?>)
                </a>
            </div>
        </div>

        <!-- District-Wise Coverage & Warning Breakdown -->
        <div class="section-card mb-4">
            <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-city me-2 text-danger"></i> 38 District Panchayat Coverage & Warning Indicators
                    </h6>
                    <span class="badge bg-secondary-subtle text-dark small">Red = No Data / Missing</span>
                </div>
                <?php if (!empty($filter_district)): ?>
                    <a href="panchayats.php<?php echo !empty($filter_gap) ? '?gap=' . urlencode($filter_gap) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small">
                        <i class="fas fa-times me-1"></i> Clear District Filter
                    </a>
                <?php endif; ?>
            </div>
            <div class="section-card-body p-3">
                <div class="d-flex flex-wrap gap-1.5 align-items-center">
                    <a href="panchayats.php<?php echo !empty($filter_gap) ? '?gap=' . urlencode($filter_gap) : ''; ?>" 
                       class="btn btn-sm <?php echo empty($filter_district) ? 'btn-danger shadow-sm fw-bold' : 'btn-light border text-dark'; ?> rounded-pill py-1 px-3 gap-pill-btn">
                        <span>All 38 Districts</span>
                        <span class="badge <?php echo empty($filter_district) ? 'bg-white text-danger' : 'bg-secondary text-white'; ?> rounded-pill ms-1"><?php echo number_format($total_overall_panchayats); ?></span>
                    </a>

                    <?php foreach ($district_stats as $dName => $dInfo): ?>
                        <?php 
                        $isActive = (strcasecmp($filter_district, $dName) === 0);
                        $hasNoData = ($dInfo['status'] === 'NO_DATA');
                        $hasMukhiyaGap = ($dInfo['missing_mukhiya'] > 0);
                        $hasSarpanchGap = ($dInfo['missing_sarpanch'] > 0);
                        ?>
                        <a href="panchayats.php?district=<?php echo urlencode($dName); ?><?php echo !empty($filter_gap) ? '&gap=' . urlencode($filter_gap) : ''; ?><?php echo !empty($search) ? '&q=' . urlencode($search) : ''; ?>" 
                           class="btn btn-sm <?php 
                               if ($isActive) echo 'btn-dark shadow-sm fw-bold ';
                               elseif ($hasNoData) echo 'btn-danger text-white fw-bold ';
                               elseif ($hasMukhiyaGap || $hasSarpanchGap) echo 'btn-light border text-dark ';
                               else echo 'btn-light border text-dark ';
                           ?> rounded-pill py-1 px-2.5 gap-pill-btn"
                           title="<?php echo htmlspecialchars($dName); ?>: <?php echo $dInfo['total_panchayats']; ?> Panchayats (Missing M: <?php echo $dInfo['missing_mukhiya']; ?>, Missing S: <?php echo $dInfo['missing_sarpanch']; ?>)">
                            
                            <?php if ($hasNoData): ?>
                                <i class="fas fa-triangle-exclamation text-white me-1"></i>
                            <?php elseif ($hasMukhiyaGap || $hasSarpanchGap): ?>
                                <i class="fas fa-circle-exclamation text-warning me-1"></i>
                            <?php else: ?>
                                <i class="fas fa-circle-check text-success me-1"></i>
                            <?php endif; ?>

                            <span><?php echo htmlspecialchars($dName); ?></span>

                            <span class="badge <?php 
                                if ($isActive) echo 'bg-white text-dark ';
                                elseif ($hasNoData) echo 'bg-white text-danger ';
                                elseif ($hasMukhiyaGap || $hasSarpanchGap) echo 'bg-warning-subtle text-dark border border-warning ';
                                else echo 'bg-success-subtle text-success border border-success-subtle ';
                            ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                <?php echo $hasNoData ? '0 (No Data)' : number_format($dInfo['total_panchayats']); ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Block-Wise Warning & Coverage Breakdown (When District is Selected) -->
        <?php if (!empty($filter_district)): ?>
            <div class="section-card mb-4 border-danger border-opacity-25">
                <div class="section-card-header bg-danger bg-opacity-10 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="fw-bold mb-0 text-danger">
                            <i class="fas fa-cubes me-2"></i> Block Data Coverage in <?php echo htmlspecialchars($filter_district); ?> (<?php echo count($block_stats); ?> Blocks)
                        </h6>
                    </div>
                    <?php if (!empty($filter_block)): ?>
                        <a href="panchayats.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($filter_gap) ? '&gap=' . urlencode($filter_gap) : ''; ?>" class="btn btn-sm btn-outline-danger rounded-pill py-0.5 px-2.5 small bg-white">
                            <i class="fas fa-times me-1"></i> Clear Block Filter
                        </a>
                    <?php endif; ?>
                </div>
                <div class="section-card-body p-3">
                    <?php if (!empty($block_stats)): ?>
                        <div class="d-flex flex-wrap gap-1.5 align-items-center">
                            <a href="panchayats.php?district=<?php echo urlencode($filter_district); ?><?php echo !empty($filter_gap) ? '&gap=' . urlencode($filter_gap) : ''; ?>" 
                               class="btn btn-sm <?php echo empty($filter_block) ? 'btn-danger shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-3 gap-pill-btn">
                                <span>All Blocks in <?php echo htmlspecialchars($filter_district); ?></span>
                            </a>

                            <?php foreach ($block_stats as $bName => $bInfo): ?>
                                <?php 
                                $isBlockActive = (strcasecmp($filter_block, $bName) === 0);
                                $hasGap = ($bInfo['missing_mukhiya'] > 0 || $bInfo['missing_sarpanch'] > 0);
                                ?>
                                <a href="panchayats.php?district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($bName); ?><?php echo !empty($filter_gap) ? '&gap=' . urlencode($filter_gap) : ''; ?>" 
                                   class="btn btn-sm <?php echo $isBlockActive ? 'btn-danger shadow-sm fw-bold' : 'btn-white bg-white border text-dark'; ?> rounded-pill py-1 px-2.5 gap-pill-btn">
                                    <?php if ($hasGap): ?>
                                        <i class="fas fa-triangle-exclamation text-warning me-1"></i>
                                    <?php else: ?>
                                        <i class="fas fa-check text-success me-1"></i>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($bName); ?></span>
                                    <span class="badge <?php echo $isBlockActive ? 'bg-white text-danger' : 'bg-light text-dark border'; ?> rounded-pill ms-1 font-monospace" style="font-size:0.72rem;">
                                        <?php echo number_format($bInfo['total_panchayats']); ?>
                                        <?php if ($bInfo['missing_mukhiya'] > 0): ?>
                                            <span class="text-danger" title="<?php echo $bInfo['missing_mukhiya']; ?> Missing Mukhiya">!M:<?php echo $bInfo['missing_mukhiya']; ?></span>
                                        <?php endif; ?>
                                        <?php if ($bInfo['missing_sarpanch'] > 0): ?>
                                            <span class="text-warning text-dark" title="<?php echo $bInfo['missing_sarpanch']; ?> Missing Sarpanch">!S:<?php echo $bInfo['missing_sarpanch']; ?></span>
                                        <?php endif; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-danger p-2">
                            <i class="fas fa-triangle-exclamation me-1"></i> <strong>Warning:</strong> No Panchayats or Blocks found in <strong><?php echo htmlspecialchars($filter_district); ?></strong>. Data needs to be seeded.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Bar -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="panchayats.php" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search panchayat, Mukhiya, Sarpanch..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="district" class="form-select" onchange="this.form.submit()">
                            <option value="">All 38 Districts (<?php echo number_format($total_overall_panchayats); ?>)</option>
                            <?php foreach ($district_stats as $dName => $dInfo): ?>
                                <option value="<?php echo htmlspecialchars($dName); ?>" <?php echo (strcasecmp($filter_district, $dName) === 0) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($dName); ?> (<?php echo $dInfo['total_panchayats']; ?> Panchayats<?php echo ($dInfo['total_panchayats'] === 0) ? ' - NO DATA' : ''; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="block" class="form-select" onchange="this.form.submit()" <?php echo empty($filter_district) ? 'disabled' : ''; ?>>
                            <option value=""><?php echo !empty($filter_district) ? 'All Blocks in ' . htmlspecialchars($filter_district) : 'Select District First'; ?></option>
                            <?php if (!empty($block_stats)): ?>
                                <?php foreach ($block_stats as $bName => $bInfo): ?>
                                    <option value="<?php echo htmlspecialchars($bName); ?>" <?php echo (strcasecmp($filter_block, $bName) === 0) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($bName); ?> (<?php echo $bInfo['total_panchayats']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <?php if (!empty($filter_gap)): ?>
                            <input type="hidden" name="gap" value="<?php echo htmlspecialchars($filter_gap); ?>">
                        <?php endif; ?>
                        <button type="submit" class="btn btn-dark w-100 fw-semibold"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="panchayats.php" class="btn btn-light border" title="Reset All Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panchayats Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-tree-city me-2 text-success"></i> Panchayats Registry Records
                    </h6>
                    <?php if (!empty($filter_gap)): ?>
                        <span class="badge bg-danger rounded-pill px-2.5 py-1">
                            Gap Filter: <?php echo htmlspecialchars($filter_gap); ?>
                        </span>
                    <?php endif; ?>
                </div>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?> (<?php echo number_format($total_rows); ?> Total)</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 20%;">Gram Panchayat</th>
                            <th style="width: 15%;">Block</th>
                            <th style="width: 15%;">District</th>
                            <th style="width: 25%;">Elected Mukhiya</th>
                            <th style="width: 25%;">Elected Sarpanch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($panchayats)): ?>
                            <?php foreach ($panchayats as $p): ?>
                                <?php 
                                $hasMukhiya = (!empty($p['current_mukhiya']) && $p['current_mukhiya'] !== '—');
                                $hasSarpanch = (!empty($p['current_sarpanch']) && $p['current_sarpanch'] !== '—');
                                ?>
                                <tr>
                                    <td>
                                        <strong class="text-primary fs-6"><?php echo htmlspecialchars($p['panchayat_name']); ?></strong>
                                        <?php if (!$hasMukhiya || !$hasSarpanch): ?>
                                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning d-block mt-1 font-monospace" style="font-size:0.65rem; width:fit-content;">
                                                <i class="fas fa-triangle-exclamation text-warning me-1"></i>Incomplete Leadership
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="panchayats.php?district=<?php echo urlencode($p['district']); ?>&block=<?php echo urlencode($p['block']); ?>" class="badge bg-light text-dark border text-decoration-none">
                                            <?php echo htmlspecialchars($p['block']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="panchayats.php?district=<?php echo urlencode($p['district']); ?>" class="fw-semibold text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($p['district']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php if ($hasMukhiya): ?>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['current_mukhiya']); ?></div>
                                            <?php if (!empty($p['mukhiya_mobile'])): ?>
                                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($p['mukhiya_mobile']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                                <i class="fas fa-triangle-exclamation me-1"></i> Missing Mukhiya
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($hasSarpanch): ?>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($p['current_sarpanch']); ?></div>
                                            <?php if (!empty($p['sarpanch_mobile'])): ?>
                                                <small class="text-muted"><i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($p['sarpanch_mobile']); ?></small>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="badge bg-warning bg-opacity-25 text-dark border border-warning px-2 py-1">
                                                <i class="fas fa-triangle-exclamation text-warning me-1"></i> Missing Sarpanch
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block text-muted opacity-50"></i>
                                    <h6>No Panchayat records found</h6>
                                    <p class="small text-muted mb-0">Try changing your search keywords or district/block filters.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <small class="text-muted">Showing <?php echo count($panchayats); ?> of <?php echo number_format($total_rows); ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&gap=<?php echo urlencode($filter_gap); ?>">Prev</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&gap=<?php echo urlencode($filter_gap); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&block=<?php echo urlencode($filter_block); ?>&gap=<?php echo urlencode($filter_gap); ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<!-- Modal: Complete 38 Districts Data Gap Audit Matrix -->
<div class="modal fade" id="auditModal" tabindex="-1" aria-labelledby="auditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-light">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="auditModalLabel" style="font-family: 'Outfit', sans-serif;">
                        <i class="fas fa-stethoscope me-2 text-danger"></i> Complete 38 Districts Data Health & Warning Matrix
                    </h5>
                    <small class="text-muted">Detailed audit of Panchayats, Mukhiyas, Sarpanchs, and Missing Records across Bihar</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 25%;">District</th>
                                <th class="text-center" style="width: 15%;">Panchayats Count</th>
                                <th class="text-center" style="width: 15%;">Missing Mukhiya</th>
                                <th class="text-center" style="width: 15%;">Missing Sarpanch</th>
                                <th class="text-center" style="width: 10%;">Health Status</th>
                                <th class="text-end" style="width: 15%;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $sno = 1; foreach ($district_stats as $dName => $dInfo): ?>
                                <?php 
                                $isZero = ($dInfo['status'] === 'NO_DATA');
                                $isPartial = ($dInfo['status'] === 'PARTIAL' || $dInfo['status'] === 'NO_MUKHIYA' || $dInfo['status'] === 'NO_SARPANCH');
                                ?>
                                <tr class="<?php echo $isZero ? 'table-danger' : ($isPartial ? 'table-warning' : ''); ?>">
                                    <td class="text-muted small"><?php echo $sno++; ?></td>
                                    <td>
                                        <strong class="text-dark"><?php echo htmlspecialchars($dName); ?></strong>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isZero): ?>
                                            <span class="badge bg-danger rounded-pill">0 (NO DATA)</span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-dark border"><?php echo number_format($dInfo['total_panchayats']); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($dInfo['missing_mukhiya'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill px-2.5 py-1">
                                                <?php echo number_format($dInfo['missing_mukhiya']); ?> Missing
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success">✓ 100% Complete</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($dInfo['missing_sarpanch'] > 0): ?>
                                            <span class="badge bg-warning text-dark border border-warning rounded-pill px-2.5 py-1">
                                                <?php echo number_format($dInfo['missing_sarpanch']); ?> Missing
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success">✓ 100% Complete</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($isZero): ?>
                                            <span class="badge bg-danger">NO DATA</span>
                                        <?php elseif ($dInfo['status'] === 'NO_MUKHIYA'): ?>
                                            <span class="badge bg-danger">NO MUKHIYAS</span>
                                        <?php elseif ($dInfo['status'] === 'NO_SARPANCH'): ?>
                                            <span class="badge bg-warning text-dark">NO SARPANCHS</span>
                                        <?php elseif ($dInfo['status'] === 'PARTIAL'): ?>
                                            <span class="badge bg-warning text-dark">PARTIAL GAPS</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">100% COMPLETE</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <?php if (!$isZero): ?>
                                            <a href="panchayats.php?district=<?php echo urlencode($dName); ?>" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                                <i class="fas fa-filter me-1"></i> Filter District
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

</body>
</html>
