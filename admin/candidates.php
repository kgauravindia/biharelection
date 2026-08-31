<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Handle Delete
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($conn && $del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `be_candidates` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Candidate profile deleted successfully.";
            } else {
                $error = "Error deleting candidate profile.";
            }
        }
    }
}

// Handle Toggle Verification
if (isset($_GET['toggle_verify'])) {
    $t_id = (int)$_GET['toggle_verify'];
    if ($conn && $t_id > 0) {
        $conn->query("UPDATE `be_candidates` SET `verified` = IF(`verified` = 1, 0, 1) WHERE `id` = $t_id");
        header("Location: candidates.php?msg=updated");
        exit();
    }
}

// Filters & Pagination
$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_party = isset($_GET['party']) ? sanitize($_GET['party']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_tier = isset($_GET['tier']) ? sanitize($_GET['tier']) : '';

$candidates = [];
$total_rows = 0;

if ($conn) {
    $where = "1=1";
    if (!empty($search)) {
        $where .= " AND (`name` LIKE '%$search%' OR `name_hi` LIKE '%$search%' OR `constituency` LIKE '%$search%')";
    }
    if (!empty($filter_party)) {
        $where .= " AND (`party_short` = '$filter_party' OR `party` = '$filter_party')";
    }
    if (!empty($filter_district)) {
        $where .= " AND `district` = '$filter_district'";
    }
    if (!empty($filter_tier)) {
        $where .= " AND `promoted_tier` = '$filter_tier'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `be_candidates` WHERE $where");
    if ($c_res) $total_rows = $c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT * FROM `be_candidates` WHERE $where ORDER BY `id` DESC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $candidates[] = $r;
        }
    }
}

// Fallback to DataProvider if DB candidates table is empty
if (empty($candidates) && empty($search) && empty($filter_party) && empty($filter_district)) {
    $all_c = DataProvider::getCandidates();
    $total_rows = count($all_c);
    $candidates = array_slice($all_c, $offset, $limit);
}

$total_pages = ceil($total_rows / $limit);
$districts = DataProvider::getDistricts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates Management — Bihar Election</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Candidates Directory</h1>
                <p class="text-muted mb-0">Manage MLA candidate profiles, affidavit data, party tickets & verification.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="edit-candidate.php" class="btn btn-danger fw-semibold px-3 py-2 rounded-3 shadow-sm">
                    <i class="fas fa-plus me-1"></i> Add Candidate
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search and Filters Card -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="candidates.php" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by name or AC..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <select name="party" class="form-select">
                            <option value="">All Parties</option>
                            <option value="BJP" <?php echo ($filter_party === 'BJP') ? 'selected' : ''; ?>>BJP</option>
                            <option value="RJD" <?php echo ($filter_party === 'RJD') ? 'selected' : ''; ?>>RJD</option>
                            <option value="JD(U)" <?php echo ($filter_party === 'JD(U)') ? 'selected' : ''; ?>>JD(U)</option>
                            <option value="INC" <?php echo ($filter_party === 'INC') ? 'selected' : ''; ?>>INC</option>
                            <option value="CPI-ML" <?php echo ($filter_party === 'CPI-ML') ? 'selected' : ''; ?>>CPI-ML</option>
                            <option value="VIP" <?php echo ($filter_party === 'VIP') ? 'selected' : ''; ?>>VIP</option>
                            <option value="HAM(S)" <?php echo ($filter_party === 'HAM(S)') ? 'selected' : ''; ?>>HAM(S)</option>
                            <option value="IND" <?php echo ($filter_party === 'IND') ? 'selected' : ''; ?>>Independent</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="district" class="form-select">
                            <option value="">All 38 Districts</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="tier" class="form-select">
                            <option value="">All Tiers</option>
                            <option value="STANDARD" <?php echo ($filter_tier === 'STANDARD') ? 'selected' : ''; ?>>Standard</option>
                            <option value="FEATURED" <?php echo ($filter_tier === 'FEATURED') ? 'selected' : ''; ?>>Featured</option>
                            <option value="VIP" <?php echo ($filter_tier === 'VIP') ? 'selected' : ''; ?>>VIP</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i></button>
                        <a href="candidates.php" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Candidate Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-user-tie me-2 text-danger"></i> Candidate Profiles (<?php echo number_format($total_rows); ?> Total)
                </h6>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Candidate</th>
                            <th>Party</th>
                            <th>Constituency / District</th>
                            <th>Education & Profession</th>
                            <th>Verification</th>
                            <th>Tier</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($candidates)): ?>
                            <?php foreach ($candidates as $c): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center fw-bold text-danger" style="width: 40px; height: 40px; overflow: hidden;">
                                                <?php if (!empty($c['photo'])): ?>
                                                    <img src="<?php echo htmlspecialchars($c['photo']); ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
                                                <?php else: ?>
                                                    <?php echo strtoupper(substr($c['name'] ?? 'C', 0, 1)); ?>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <a href="view-candidate.php?slug=<?php echo urlencode($c['slug'] ?? ''); ?>" class="fw-bold text-dark text-decoration-none">
                                                    <?php echo htmlspecialchars($c['name']); ?>
                                                </a>
                                                <div class="small text-muted"><?php echo htmlspecialchars($c['name_hi'] ?? ''); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary text-white"><?php echo htmlspecialchars($c['party_short'] ?? $c['party'] ?? 'IND'); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($c['constituency']); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($c['district']); ?></small>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold"><?php echo htmlspecialchars($c['education'] ?? 'N/A'); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($c['profession'] ?? 'Politician'); ?></small>
                                    </td>
                                    <td>
                                        <?php if (!empty($c['verified'])): ?>
                                            <a href="candidates.php?toggle_verify=<?php echo $c['id'] ?? 0; ?>" class="badge badge-soft-success text-decoration-none" title="Click to unverify">
                                                <i class="fas fa-check-circle me-1"></i> Verified
                                            </a>
                                        <?php else: ?>
                                            <a href="candidates.php?toggle_verify=<?php echo $c['id'] ?? 0; ?>" class="badge badge-soft-secondary text-decoration-none" title="Click to verify">
                                                <i class="fas fa-clock me-1"></i> Pending
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $tier = $c['promoted_tier'] ?? 'STANDARD';
                                        if ($tier === 'VIP') echo '<span class="badge bg-warning text-dark"><i class="fas fa-crown me-1"></i> VIP</span>';
                                        elseif ($tier === 'FEATURED') echo '<span class="badge bg-info text-white"><i class="fas fa-star me-1"></i> Featured</span>';
                                        else echo '<span class="badge bg-light text-muted border">Standard</span>';
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="view-candidate.php?slug=<?php echo urlencode($c['slug'] ?? ''); ?>" class="btn btn-sm btn-light border" title="View Profile">
                                                <i class="fas fa-eye text-muted"></i>
                                            </a>
                                            <a href="edit-candidate.php?id=<?php echo $c['id'] ?? 0; ?>&slug=<?php echo urlencode($c['slug'] ?? ''); ?>" class="btn btn-sm btn-light border" title="Edit Profile">
                                                <i class="fas fa-pen text-primary"></i>
                                            </a>
                                            <?php if (isset($c['id']) && (int)$c['id'] > 0): ?>
                                                <a href="candidates.php?delete_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-light border text-danger" title="Delete Profile" onclick="return confirm('Are you sure you want to delete this candidate profile?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-user-slash fa-3x mb-3 d-block text-muted"></i>
                                    No candidates matched your search criteria.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?php echo count($candidates); ?> of <?php echo $total_rows; ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&party=<?php echo urlencode($filter_party); ?>&district=<?php echo urlencode($filter_district); ?>">Prev</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&party=<?php echo urlencode($filter_party); ?>&district=<?php echo urlencode($filter_district); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&party=<?php echo urlencode($filter_party); ?>&district=<?php echo urlencode($filter_district); ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
