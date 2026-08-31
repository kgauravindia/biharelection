<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$panchayats = [];
$total_rows = 0;

if ($conn) {
    $where = "1=1";
    if (!empty($search)) {
        $where .= " AND (`panchayat_name` LIKE '%$search%' OR `block` LIKE '%$search%' OR `current_mukhiya` LIKE '%$search%' OR `current_sarpanch` LIKE '%$search%')";
    }
    if (!empty($filter_district)) {
        $where .= " AND (`district` = '$filter_district' OR `district_slug` = '$filter_district')";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `be_panchayats` WHERE $where");
    if ($c_res) $total_rows = $c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `be_panchayats` WHERE $where ORDER BY `district` ASC, `block` ASC, `panchayat_name` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) $panchayats[] = $r;
    }
}

$total_pages = ceil($total_rows / $limit);
$districts = DataProvider::getDistricts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panchayats Directory — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Panchayats Directory</h1>
                <p class="text-muted mb-0">Overview of 8,000+ Bihar Gram Panchayats with Mukhiya & Sarpanch leadership.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-success px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-tree-city me-1"></i> <?php echo number_format($total_rows); ?> Panchayats Recorded
                </span>
            </div>
        </div>

        <!-- Filters -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="panchayats.php" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search panchayat, Mukhiya, Sarpanch or block..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="district" class="form-select">
                            <option value="">All 38 Districts</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                        <a href="panchayats.php" class="btn btn-light border" title="Reset"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-tree-city me-2 text-success"></i> Panchayats Registry
                </h6>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Panchayat</th>
                            <th>Block</th>
                            <th>District</th>
                            <th>Current Mukhiya</th>
                            <th>Current Sarpanch</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($panchayats)): ?>
                            <?php foreach ($panchayats as $p): ?>
                                <tr>
                                    <td><strong class="text-primary"><?php echo htmlspecialchars($p['panchayat_name']); ?></strong></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['block']); ?></span></td>
                                    <td><?php echo htmlspecialchars($p['district']); ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($p['current_mukhiya'] ?? '—'); ?></div>
                                        <?php if (!empty($p['mukhiya_mobile'])): ?>
                                            <small class="text-muted"><i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($p['mukhiya_mobile']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($p['current_sarpanch'] ?? '—'); ?></div>
                                        <?php if (!empty($p['sarpanch_mobile'])): ?>
                                            <small class="text-muted"><i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($p['sarpanch_mobile']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">No panchayat records found matching this criteria.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?php echo count($panchayats); ?> of <?php echo $total_rows; ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>">Prev</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
