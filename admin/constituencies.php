<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_district = isset($_GET['district']) ? sanitize($_GET['district']) : '';
$filter_party = isset($_GET['party']) ? sanitize($_GET['party']) : '';
$filter_res = isset($_GET['reservation']) ? sanitize($_GET['reservation']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$all_constituencies = DataProvider::getConstituencies();
$filtered = [];

foreach ($all_constituencies as $c) {
    if (!empty($search)) {
        $q = strtolower($search);
        if (strpos(strtolower($c['name']), $q) === false &&
            strpos(strtolower($c['name_hi'] ?? ''), $q) === false &&
            strpos((string)$c['ac_no'], $q) === false &&
            strpos(strtolower($c['current_mla'] ?? ''), $q) === false) {
            continue;
        }
    }
    if (!empty($filter_district) && strtolower($c['district']) !== strtolower($filter_district)) {
        continue;
    }
    if (!empty($filter_party) && strtolower($c['current_party'] ?? '') !== strtolower($filter_party)) {
        continue;
    }
    if (!empty($filter_res) && strtolower($c['reservation'] ?? 'GEN') !== strtolower($filter_res)) {
        continue;
    }
    $filtered[] = $c;
}

$total_rows = count($filtered);
$total_pages = ceil($total_rows / $limit);
$constituencies = array_slice($filtered, $offset, $limit);
$districts = DataProvider::getDistricts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>243 Assembly Constituencies — Bihar Election</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">243 Assembly Constituencies</h1>
                <p class="text-muted mb-0">Browse and manage Bihar Vidhan Sabha seats, incumbent MLAs, electors & key issues.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-landmark me-1"></i> 243 Seats in Bihar
                </span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="constituencies.php" class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by AC number, name or MLA..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="district" class="form-select">
                            <option value="">All 38 Districts</option>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo (strtolower($filter_district) === strtolower($d['name'])) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?> (<?php echo $d['total_ac']; ?> ACs)
                                </option>
                            <?php endforeach; ?>
                        </select>
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
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="reservation" class="form-select">
                            <option value="">All Categories</option>
                            <option value="GEN" <?php echo ($filter_res === 'GEN') ? 'selected' : ''; ?>>General</option>
                            <option value="SC" <?php echo ($filter_res === 'SC') ? 'selected' : ''; ?>>SC Reserved</option>
                            <option value="ST" <?php echo ($filter_res === 'ST') ? 'selected' : ''; ?>>ST Reserved</option>
                        </select>
                    </div>
                    <div class="col-md-1 d-flex gap-1">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i></button>
                        <a href="constituencies.php" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Card -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-list me-2 text-danger"></i> Vidhan Sabha Seats (<?php echo $total_rows; ?> Shown)
                </h6>
                <span class="badge bg-light text-muted border">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th style="width: 80px;">AC No.</th>
                            <th>Constituency Name</th>
                            <th>District</th>
                            <th>Reservation</th>
                            <th>Current MLA</th>
                            <th>Party</th>
                            <th>Total Electors</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($constituencies)): ?>
                            <?php foreach ($constituencies as $ac): ?>
                                <tr>
                                    <td>
                                        <span class="badge bg-dark text-white fw-bold px-2 py-1">
                                            #<?php echo $ac['ac_no']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="../vidhan-sabha.php?slug=<?php echo urlencode($ac['slug']); ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($ac['name']); ?>
                                        </a>
                                        <div class="small text-muted"><?php echo htmlspecialchars($ac['name_hi'] ?? ''); ?></div>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($ac['district']); ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $res = $ac['reservation'] ?? 'GEN';
                                        if ($res === 'SC') echo '<span class="badge bg-info text-white">SC</span>';
                                        elseif ($res === 'ST') echo '<span class="badge bg-warning text-dark">ST</span>';
                                        else echo '<span class="badge bg-light text-dark border">GEN</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($ac['current_mla'] ?? 'Vacant / To be updated'); ?></div>
                                    </td>
                                    <td>
                                        <?php if (!empty($ac['current_party'])): ?>
                                            <span class="badge bg-primary"><?php echo htmlspecialchars($ac['current_party']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="small fw-semibold"><?php echo number_format($ac['total_electors'] ?? 0); ?></span>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="../vidhan-sabha.php?slug=<?php echo urlencode($ac['slug']); ?>" target="_blank" class="btn btn-sm btn-light border" title="Public Page">
                                                <i class="fas fa-external-link-alt text-muted"></i>
                                            </a>
                                            <a href="edit-constituency.php?ac_no=<?php echo $ac['ac_no']; ?>" class="btn btn-sm btn-light border text-primary" title="Edit Constituency">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">No constituencies match your filter query.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">Showing <?php echo count($constituencies); ?> of <?php echo $total_rows; ?> entries</small>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&party=<?php echo urlencode($filter_party); ?>&reservation=<?php echo urlencode($filter_res); ?>">Prev</a></li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&party=<?php echo urlencode($filter_party); ?>&reservation=<?php echo urlencode($filter_res); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>

                            <?php if ($page < $total_pages): ?>
                                <li class="page-item"><a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&district=<?php echo urlencode($filter_district); ?>&party=<?php echo urlencode($filter_party); ?>&reservation=<?php echo urlencode($filter_res); ?>">Next</a></li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
