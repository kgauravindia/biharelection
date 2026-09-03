<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$tab = isset($_GET['tab']) ? sanitize($_GET['tab']) : 'members';

$members = [];
$officials = [];
$total_members = 0;
$total_officials = 0;

$districts = DataProvider::getDistricts();

if ($conn) {
    // Members Query
    $m_where = "1=1";
    if (!empty($search)) {
        $m_where .= " AND (`candidate_name` LIKE '%$search%' OR `block` LIKE '%$search%' OR `territory_no` LIKE '%$search%')";
    }
    if (!empty($filter_district)) {
        $m_where .= " AND (`district` = '$filter_district' OR `district_slug` = '$filter_district')";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `zila_parishad_members` WHERE $m_where");
    if ($c_res) $total_members = $c_res->fetch_assoc()['c'];

    $m_res = $conn->query("SELECT * FROM `zila_parishad_members` WHERE $m_where ORDER BY `district` ASC, `territory_no` ASC LIMIT 50");
    if ($m_res) {
        while ($r = $m_res->fetch_assoc()) $members[] = $r;
    }

    // Officials Query
    $o_where = "1=1";
    if (!empty($search)) {
        $o_where .= " AND (`candidate_name` LIKE '%$search%' OR `post` LIKE '%$search%')";
    }
    if (!empty($filter_district)) {
        $o_where .= " AND (`district` = '$filter_district' OR `district_slug` = '$filter_district')";
    }
    $o_res = $conn->query("SELECT * FROM `zila_parishad_officials` WHERE $o_where ORDER BY `district` ASC, `post` ASC");
    if ($o_res) {
        while ($r = $o_res->fetch_assoc()) $officials[] = $r;
        $total_officials = count($officials);
    }
}
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
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Zila Parishad Councils</h1>
                <p class="text-muted mb-0">Elected Zila Parishad Territorial Members, Adhyaksh & Upadhyaksh across Bihar.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-building-columns me-1"></i> 38 District Councils
                </span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="zila-parishad.php" class="row g-2 align-items-center">
                    <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search member, post or territory..." value="<?php echo htmlspecialchars($search); ?>">
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
                        <a href="zila-parishad.php?tab=<?php echo $tab; ?>" class="btn btn-light border" title="Reset"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabs -->
        <ul class="nav nav-pills mb-4 gap-2">
            <li class="nav-item">
                <a class="nav-link px-4 py-2 fw-semibold rounded-3 <?php echo ($tab === 'members') ? 'active bg-danger' : 'bg-white border text-dark'; ?>" href="zila-parishad.php?tab=members&district=<?php echo urlencode($filter_district); ?>">
                    <i class="fas fa-users me-1"></i> Territorial Ward Members (<?php echo number_format($total_members); ?>)
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link px-4 py-2 fw-semibold rounded-3 <?php echo ($tab === 'officials') ? 'active bg-danger' : 'bg-white border text-dark'; ?>" href="zila-parishad.php?tab=officials&district=<?php echo urlencode($filter_district); ?>">
                    <i class="fas fa-crown me-1"></i> Council Officials & Adhyaksh (<?php echo count($officials); ?>)
                </a>
            </li>
        </ul>

        <?php if ($tab === 'members'): ?>
            <!-- Members Table -->
            <div class="section-card">
                <div class="section-card-header">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users me-2 text-danger"></i> Territorial Ward Members</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Member Name</th>
                                <th>District</th>
                                <th>Block</th>
                                <th>Territory / Ward No.</th>
                                <th>Reservation / Category</th>
                                <th>Mobile</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($members)): ?>
                                <?php foreach ($members as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($m['candidate_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($m['father_husband_name'] ?? ''); ?></small>
                                        </td>
                                        <td><span class="fw-semibold"><?php echo htmlspecialchars($m['district']); ?></span></td>
                                        <td><?php echo htmlspecialchars($m['block'] ?? '—'); ?></td>
                                        <td><span class="badge bg-danger">Ward <?php echo htmlspecialchars($m['territory_no']); ?></span></td>
                                        <td>
                                            <div class="small fw-semibold"><?php echo htmlspecialchars($m['reservation'] ?? $m['category'] ?? 'GEN'); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($m['gender'] ?? ''); ?></small>
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
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">No Zila Parishad members found for this filter.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php else: ?>
            <!-- Officials Table -->
            <div class="section-card">
                <div class="section-card-header">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-crown me-2 text-warning"></i> Zila Parishad Adhyaksh & Upadhyaksh</h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Official Name</th>
                                <th>Post / Designation</th>
                                <th>District</th>
                                <th>Category / Reservation</th>
                                <th>Address</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($officials)): ?>
                                <?php foreach ($officials as $o): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark"><?php echo htmlspecialchars($o['candidate_name']); ?></div>
                                            <small class="text-muted"><?php echo htmlspecialchars($o['father_husband_name'] ?? ''); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-warning text-dark px-2 py-1 fw-bold">
                                                <?php echo htmlspecialchars($o['post']); ?>
                                            </span>
                                        </td>
                                        <td><span class="fw-bold"><?php echo htmlspecialchars($o['district']); ?></span></td>
                                        <td><?php echo htmlspecialchars($o['reservation'] ?? $o['category'] ?? 'GEN'); ?></td>
                                        <td><small class="text-muted"><?php echo htmlspecialchars($o['address'] ?? '—'); ?></small></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">No Zila Parishad officials listed yet.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </main>

    <?php include 'admin-footer.php'; ?>
