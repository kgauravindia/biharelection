<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$pdo = Database::getConnection();
$message = '';
$error = '';

// Handle quick sync or reset actions
if (isset($_GET['action']) && $_GET['action'] === 'clearcache') {
    DataCache::delete('provider_mlcs');
    $message = "MLCs cache successfully purged and regenerated!";
}

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_quota = isset($_GET['quota']) ? sanitize($_GET['quota']) : '';
$filter_party = isset($_GET['party']) ? sanitize($_GET['party']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';

$limit = 25;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

// Fetch all MLCs
$all_mlcs = DataProvider::getMlcs();

// Helper to determine quota
function getAdminMlcQuota($constituency) {
    $c = (string)$constituency;
    if (stripos($c, 'Local') !== false || stripos($c, 'प्राधिकार') !== false || stripos($c, 'L.B') !== false) {
        return 'Local';
    } elseif (stripos($c, 'Nominated') !== false || stripos($c, 'मनोनीत') !== false) {
        return 'Nominated';
    } elseif (stripos($c, 'Graduate') !== false || stripos($c, 'स्नातक') !== false) {
        return 'Graduates';
    } elseif (stripos($c, 'Teacher') !== false || stripos($c, 'शिक्षक') !== false) {
        return 'Teachers';
    }
    return 'Assembly';
}

$quotaCounts = [
    'Total' => count($all_mlcs),
    'Assembly' => 0,
    'Local' => 0,
    'Nominated' => 0,
    'Graduates' => 0,
    'Teachers' => 0,
    'Elected2026' => 0,
    'Vacant' => 0
];

$partyList = [];

foreach ($all_mlcs as $m) {
    $qType = getAdminMlcQuota($m['constituency'] ?? '');
    if (isset($quotaCounts[$qType])) {
        $quotaCounts[$qType]++;
    }
    
    $p = trim($m['party'] ?? '');
    if (!empty($p) && !in_array($p, $partyList)) {
        $partyList[] = $p;
    }
    
    $isVacant = strtolower(trim($m['name'] ?? '')) === 'vacant';
    if ($isVacant) {
        $quotaCounts['Vacant']++;
    }

    if (in_array((int)($m['sr_no'] ?? 0), [1, 3, 5, 6, 7, 9, 10, 11])) {
        $quotaCounts['Elected2026']++;
    }
}
sort($partyList);

$filtered = [];
foreach ($all_mlcs as $m) {
    $mName = $m['name'] ?? '';
    $mNameHi = $m['name_hi'] ?? '';
    $mConst = $m['constituency'] ?? '';
    $mParty = $m['party'] ?? '';
    $mQuota = getAdminMlcQuota($mConst);
    $mSr = (string)($m['sr_no'] ?? '');
    $isVacant = strtolower(trim($mName)) === 'vacant';

    // Search filter
    if (!empty($search)) {
        $q = strtolower($search);
        if (strpos(strtolower($mName), $q) === false &&
            strpos(strtolower($mNameHi), $q) === false &&
            strpos(strtolower($mConst), $q) === false &&
            strpos(strtolower($mParty), $q) === false &&
            strpos(strtolower($m['contact'] ?? ''), $q) === false &&
            $mSr !== $q) {
            continue;
        }
    }

    // Quota filter
    if (!empty($filter_quota) && strtolower($filter_quota) !== strtolower($mQuota)) {
        continue;
    }

    // Party filter
    if (!empty($filter_party)) {
        if ($filter_party === 'Vacant' && !$isVacant) continue;
        if ($filter_party !== 'Vacant' && strtolower($mParty) !== strtolower($filter_party)) continue;
    }

    // Status filter
    if (!empty($filter_status)) {
        if ($filter_status === 'vacant' && !$isVacant) continue;
        if ($filter_status === 'active' && $isVacant) continue;
        if ($filter_status === 'elected2026' && !in_array((int)$mSr, [1, 3, 5, 6, 7, 9, 10, 11])) continue;
    }

    $filtered[] = $m;
}

$total_rows = count($filtered);
$total_pages = ceil($total_rows / $limit);
$mlcs = array_slice($filtered, $offset, $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>75 Vidhan Parishad MLCs Directory — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .quota-chip {
            display: inline-block;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .quota-assembly { background: #e0f2fe; color: #0369a1; }
        .quota-local { background: #fef3c7; color: #92400e; }
        .quota-nominated { background: #ede9fe; color: #6d28d9; }
        .quota-graduates { background: #dcfce7; color: #15803d; }
        .quota-teachers { background: #ffedd5; color: #c2410c; }
        
        .party-badge {
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 9px;
            border-radius: 6px;
            display: inline-block;
        }
        .party-jdu { background: #15803d; color: #ffffff; }
        .party-bjp { background: #ea580c; color: #ffffff; }
        .party-rjd { background: #166534; color: #ffffff; }
        .party-jansuraaj { background: #eab308; color: #000000; }
        .party-inc { background: #0284c7; color: #ffffff; }
        .party-cpi { background: #dc2626; color: #ffffff; }
        .party-ind { background: #475569; color: #ffffff; }
        .party-vacant { background: #fee2e2; color: #991b1b; }
        
        .mlc-avatar-sm {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #e2e8f0;
            background: #f8fafc;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #64748b;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1 small">
                        <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Dashboard</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Vidhan Parishad MLCs</li>
                    </ol>
                </nav>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    75 Vidhan Parishad MLCs Management
                </h1>
                <p class="text-muted mb-0">Browse, edit and maintain Bihar Legislative Council (MLC) members, biennial election results & quotas.</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="git-push.php" class="btn btn-outline-danger shadow-sm fw-semibold">
                    <i class="fab fa-git-alt me-1"></i> Git Push Console
                </a>
                <a href="mlcs.php?action=clearcache" class="btn btn-outline-primary shadow-sm fw-semibold">
                    <i class="fas fa-arrows-rotate me-1"></i> Sync & Purge Cache
                </a>
                <a href="../vidhan-parishad-election.php" target="_blank" class="btn btn-warning text-dark shadow-sm fw-bold">
                    <i class="fas fa-trophy me-1"></i> Live 2026 Election Hub
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-circle-check me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Quota & Metrics Bar -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card p-3 h-100 bg-white border rounded-3 shadow-sm">
                    <div class="text-muted small fw-semibold">Total Council Seats</div>
                    <div class="h3 fw-bold text-dark mb-0"><?php echo $quotaCounts['Total']; ?></div>
                    <small class="text-primary fw-semibold"><i class="fas fa-building-columns me-1"></i> Upper House</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card p-3 h-100 bg-white border rounded-3 shadow-sm">
                    <div class="text-muted small fw-semibold">Assembly Quota</div>
                    <div class="h3 fw-bold text-primary mb-0"><?php echo $quotaCounts['Assembly']; ?></div>
                    <small class="text-muted">Elected by MLAs</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card p-3 h-100 bg-white border rounded-3 shadow-sm">
                    <div class="text-muted small fw-semibold">Local Authorities</div>
                    <div class="h3 fw-bold text-warning mb-0"><?php echo $quotaCounts['Local']; ?></div>
                    <small class="text-muted">Panchayats & Civic Bodies</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card p-3 h-100 bg-white border rounded-3 shadow-sm">
                    <div class="text-muted small fw-semibold">Nominated Quota</div>
                    <div class="h3 fw-bold text-purple mb-0" style="color: #7c3aed;"><?php echo $quotaCounts['Nominated']; ?></div>
                    <small class="text-muted">Governor Nominated</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card p-3 h-100 bg-white border rounded-3 shadow-sm">
                    <div class="text-muted small fw-semibold">Graduates & Teachers</div>
                    <div class="h3 fw-bold text-success mb-0"><?php echo ($quotaCounts['Graduates'] + $quotaCounts['Teachers']); ?></div>
                    <small class="text-success fw-semibold"><i class="fas fa-check-circle me-1"></i> 8 Seats 2026 Won</small>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="stat-card p-3 h-100 bg-white border rounded-3 shadow-sm">
                    <div class="text-muted small fw-semibold">Vacant Seats</div>
                    <div class="h3 fw-bold text-danger mb-0"><?php echo $quotaCounts['Vacant']; ?></div>
                    <small class="text-muted">Pending Nomination</small>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="section-card mb-4 bg-white border rounded-3 p-3 shadow-sm">
            <form method="GET" action="mlcs.php" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="q" class="form-control border-start-0" placeholder="Search by name, constituency, party, mobile..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <select name="quota" class="form-select">
                        <option value="">All Quotas (75)</option>
                        <option value="Assembly" <?php echo ($filter_quota === 'Assembly') ? 'selected' : ''; ?>>Assembly Quota (27)</option>
                        <option value="Local" <?php echo ($filter_quota === 'Local') ? 'selected' : ''; ?>>Local Bodies (24)</option>
                        <option value="Nominated" <?php echo ($filter_quota === 'Nominated') ? 'selected' : ''; ?>>Governor Nominated (12)</option>
                        <option value="Graduates" <?php echo ($filter_quota === 'Graduates') ? 'selected' : ''; ?>>Graduates Quota (6)</option>
                        <option value="Teachers" <?php echo ($filter_quota === 'Teachers') ? 'selected' : ''; ?>>Teachers Quota (6)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="party" class="form-select">
                        <option value="">All Parties</option>
                        <?php foreach ($partyList as $p): ?>
                            <option value="<?php echo htmlspecialchars($p); ?>" <?php echo ($filter_party === $p) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($p); ?>
                            </option>
                        <?php endforeach; ?>
                        <option value="Vacant" <?php echo ($filter_party === 'Vacant') ? 'selected' : ''; ?>>Vacant</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="active" <?php echo ($filter_status === 'active') ? 'selected' : ''; ?>>Active / Elected</option>
                        <option value="elected2026" <?php echo ($filter_status === 'elected2026') ? 'selected' : ''; ?>>🏆 2026 Winners (8)</option>
                        <option value="vacant" <?php echo ($filter_status === 'vacant') ? 'selected' : ''; ?>>Vacant (12)</option>
                    </select>
                </div>
                <div class="col-6 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($filter_quota) || !empty($filter_party) || !empty($filter_status)): ?>
                        <a href="mlcs.php" class="btn btn-light border px-2.5" title="Reset Filters">
                            <i class="fas fa-times text-muted"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- MLCs Roster Table -->
        <div class="card border-0 shadow-sm rounded-3 overflow-hidden">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <span class="fw-bold text-dark">
                    Showing <?php echo number_format($total_rows); ?> Legislative Council Members
                </span>
                <span class="text-muted small">
                    Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?>
                </span>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th style="width: 60px;" class="text-center">S.No</th>
                            <th>Member Name & Designation</th>
                            <th>Constituency & Quota</th>
                            <th>Party</th>
                            <th>Tenure</th>
                            <th>Contact</th>
                            <th style="width: 130px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($mlcs)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-magnifying-glass fa-3x mb-3 text-secondary opacity-50"></i>
                                    <h6>No MLC members found matching your filter criteria.</h6>
                                    <p class="small mb-0">Try clearing or adjusting search filters above.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($mlcs as $m): 
                                $sr = (int)($m['sr_no'] ?? 0);
                                $mId = (int)($m['id'] ?? $sr);
                                $mName = $m['name'] ?? '';
                                $mNameHi = $m['name_hi'] ?? '';
                                $const = $m['constituency'] ?? '';
                                $party = $m['party'] ?? '';
                                $tenure = $m['tenure'] ?? '';
                                $contact = $m['contact'] ?? '';
                                $quota = getAdminMlcQuota($const);
                                
                                $isElected2026 = in_array($sr, [1, 3, 5, 6, 7, 9, 10, 11]);
                                $isVacant = strtolower(trim($mName)) === 'vacant';
                                
                                $partyClass = 'party-ind';
                                $pClean = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $party));
                                if ($isVacant) $partyClass = 'party-vacant';
                                elseif (strpos($pClean, 'jdu') !== false) $partyClass = 'party-jdu';
                                elseif (strpos($pClean, 'bjp') !== false) $partyClass = 'party-bjp';
                                elseif (strpos($pClean, 'rjd') !== false) $partyClass = 'party-rjd';
                                elseif (strpos($pClean, 'jansuraaj') !== false) $partyClass = 'party-jansuraaj';
                                elseif (strpos($pClean, 'inc') !== false || strpos($pClean, 'congress') !== false) $partyClass = 'party-inc';
                                elseif (strpos($pClean, 'cpi') !== false) $partyClass = 'party-cpi';
                                
                                $quotaClass = 'quota-assembly';
                                if ($quota === 'Local') $quotaClass = 'quota-local';
                                elseif ($quota === 'Nominated') $quotaClass = 'quota-nominated';
                                elseif ($quota === 'Graduates') $quotaClass = 'quota-graduates';
                                elseif ($quota === 'Teachers') $quotaClass = 'quota-teachers';
                            ?>
                                <tr class="<?php echo $isElected2026 ? 'bg-success bg-opacity-10' : ''; ?>">
                                    <td class="text-center fw-bold text-muted">
                                        #<?php echo $sr ?: $mId; ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <?php if (!empty($m['image'])): ?>
                                                <img src="<?php echo htmlspecialchars($m['image']); ?>" alt="" class="mlc-avatar-sm">
                                            <?php else: ?>
                                                <div class="mlc-avatar-sm">
                                                    <?php echo $isVacant ? '<i class="fas fa-user-slash text-danger"></i>' : strtoupper(substr($mName, 0, 1)); ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($mName); ?>
                                                    <?php if ($isElected2026): ?>
                                                        <span class="badge bg-success ms-1 small" title="Elected in 2026 Biennial Election">
                                                            <i class="fas fa-trophy text-warning me-1"></i> Elected 2026
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <?php if (!empty($mNameHi)): ?>
                                                    <div class="small text-muted"><?php echo htmlspecialchars($mNameHi); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($m['designation'])): ?>
                                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 extra-small">
                                                        <?php echo htmlspecialchars($m['designation']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?php echo htmlspecialchars($const); ?></div>
                                        <span class="quota-chip <?php echo $quotaClass; ?> mt-1">
                                            <?php echo htmlspecialchars($quota); ?> Quota
                                        </span>
                                    </td>
                                    <td>
                                        <span class="party-badge <?php echo $partyClass; ?>">
                                            <?php echo htmlspecialchars($isVacant ? 'Vacant Seat' : ($party ?: 'IND / Other')); ?>
                                        </span>
                                        <?php if (!empty($m['party_full'])): ?>
                                            <div class="extra-small text-muted mt-0.5"><?php echo htmlspecialchars($m['party_full']); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <i class="far fa-calendar-check me-1 text-primary"></i> <?php echo htmlspecialchars($tenure ?: '6-Year Term'); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($contact)): ?>
                                            <a href="tel:<?php echo htmlspecialchars($contact); ?>" class="text-decoration-none text-dark small fw-semibold">
                                                <i class="fas fa-phone text-success me-1"></i> <?php echo htmlspecialchars($contact); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted small">&mdash;</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="edit-mlc.php?id=<?php echo $mId; ?>" class="btn btn-sm btn-outline-primary" title="Edit MLC Details">
                                                <i class="fas fa-pencil"></i> Edit
                                            </a>
                                            <a href="../mlc.php?id=<?php echo $mId; ?>" target="_blank" class="btn btn-sm btn-light border text-muted" title="View Public Page">
                                                <i class="fas fa-arrow-up-right-from-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Page <?php echo $page; ?> of <?php echo $total_pages; ?>
                    </small>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <?php if ($page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page - 1); ?>&q=<?php echo urlencode($search); ?>&quota=<?php echo urlencode($filter_quota); ?>&party=<?php echo urlencode($filter_party); ?>&status=<?php echo urlencode($filter_status); ?>">Previous</a>
                                </li>
                            <?php endif; ?>
                            
                            <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&quota=<?php echo urlencode($filter_quota); ?>&party=<?php echo urlencode($filter_party); ?>&status=<?php echo urlencode($filter_status); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="?page=<?php echo ($page + 1); ?>&q=<?php echo urlencode($search); ?>&quota=<?php echo urlencode($filter_quota); ?>&party=<?php echo urlencode($filter_party); ?>&status=<?php echo urlencode($filter_status); ?>">Next</a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<?php include 'admin-footer.php'; ?>
</body>
</html>

