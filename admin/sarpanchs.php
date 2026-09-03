<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

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

// Search & Filter
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_block = isset($_GET['block']) ? sanitize($_GET['block']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$sarpanchs = [];
$total_rows = 0;

if ($conn) {
    $where = "1=1";
    if (!empty($search)) {
        $where .= " AND (`candidate_name` LIKE '%$search%' OR `panchayat` LIKE '%$search%' OR `block` LIKE '%$search%' OR `mobile` LIKE '%$search%')";
    }
    if (!empty($filter_district)) {
        $where .= " AND (`district` = '$filter_district' OR `district_slug` = '$filter_district')";
    }
    if (!empty($filter_block)) {
        $where .= " AND `block` = '$filter_block'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `sarpanchs` WHERE $where");
    if ($c_res) $total_rows = $c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `sarpanchs` WHERE $where ORDER BY `district` ASC, `block` ASC, `panchayat` ASC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $sarpanchs[] = $r;
        }
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
    <title>Gram Kutchery Sarpanchs — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Gram Kutchery Sarpanchs</h1>
                <p class="text-muted mb-0">Elected Sarpanch directory and village justice heads across Bihar Panchayats.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-warning text-dark px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-scale-balanced me-1"></i> <?php echo number_format($total_rows); ?> Sarpanchs Listed
                </span>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search & Filter Card -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="sarpanchs.php" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by Sarpanch name, panchayat, block, mobile..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <a href="sarpanchs.php" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-scale-balanced me-2 text-warning"></i> Sarpanch Directory Records
                </h6>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Elected Sarpanch</th>
                            <th>Father / Husband</th>
                            <th>District</th>
                            <th>Block</th>
                            <th>Panchayat</th>
                            <th>Category / Gender</th>
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
                                            <small class="text-muted"><?php echo (int)$s['age']; ?> Yrs</small>
                                        <?php endif; ?>
                                    </td>
                                    <td><span class="text-muted small"><?php echo htmlspecialchars($s['father_husband_name'] ?? '—'); ?></span></td>
                                    <td><span class="fw-semibold"><?php echo htmlspecialchars($s['district']); ?></span></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($s['block']); ?></span></td>
                                    <td><span class="fw-bold text-warning text-dark"><?php echo htmlspecialchars($s['panchayat']); ?></span></td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($s['category'] ?? 'GEN'); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($s['gender'] ?? ''); ?></small>
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
                                        <?php if (!empty($s['mobile'])): ?>
                                            <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $s['mobile']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Contact">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="sarpanchs.php?delete_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this Sarpanch record?');" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 d-block text-muted"></i>
                                    No Sarpanch records found. Ensure database is connected or seeded via install.php.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?php echo count($sarpanchs); ?> of <?php echo $total_rows; ?> entries</small>
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
