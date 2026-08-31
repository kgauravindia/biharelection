<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_division = isset($_GET['division']) ? sanitize($_GET['division']) : '';

$all_districts = DataProvider::getDistricts();
$districts = [];

foreach ($all_districts as $d) {
    if (!empty($search)) {
        $q = strtolower($search);
        if (strpos(strtolower($d['name']), $q) === false &&
            strpos(strtolower($d['name_hi'] ?? ''), $q) === false &&
            strpos(strtolower($d['headquarters'] ?? ''), $q) === false) {
            continue;
        }
    }
    if (!empty($filter_division) && strtolower($d['division']) !== strtolower($filter_division)) {
        continue;
    }
    $districts[] = $d;
}

$divisions = array_unique(array_filter(array_column($all_districts, 'division')));
sort($divisions);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>38 Bihar Districts — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">38 Bihar Districts</h1>
                <p class="text-muted mb-0">Manage district electoral profiles, headquarters, division mapping and AC rosters.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-success px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-map-location-dot me-1"></i> 38 Administrative Districts
                </span>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="districts.php" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by district name or headquarters..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="division" class="form-select">
                            <option value="">All Divisions</option>
                            <?php foreach ($divisions as $div): ?>
                                <option value="<?php echo htmlspecialchars($div); ?>" <?php echo ($filter_division === $div) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($div); ?> Division
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i> Filter</button>
                        <a href="districts.php" class="btn btn-light border" title="Reset Filters"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Districts Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-map me-2 text-danger"></i> Districts Registry (<?php echo count($districts); ?> Total)
                </h6>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>District Name</th>
                            <th>Hindi Name</th>
                            <th>Headquarters</th>
                            <th>Division</th>
                            <th>Total ACs</th>
                            <th>Total Electors</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($districts)): ?>
                            <?php foreach ($districts as $d): ?>
                                <tr>
                                    <td>
                                        <a href="../district.php?slug=<?php echo urlencode($d['slug']); ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </a>
                                    </td>
                                    <td><span class="text-muted"><?php echo htmlspecialchars($d['name_hi'] ?? ''); ?></span></td>
                                    <td><?php echo htmlspecialchars($d['headquarters']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($d['division']); ?></span></td>
                                    <td>
                                        <span class="badge bg-danger px-2 py-1"><?php echo $d['total_ac']; ?> ACs</span>
                                    </td>
                                    <td><span class="fw-semibold"><?php echo number_format($d['total_electors'] ?? 0); ?></span></td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="../district.php?slug=<?php echo urlencode($d['slug']); ?>" target="_blank" class="btn btn-sm btn-light border" title="View Public Page">
                                                <i class="fas fa-external-link-alt text-muted"></i>
                                            </a>
                                            <a href="edit-district.php?slug=<?php echo urlencode($d['slug']); ?>" class="btn btn-sm btn-light border text-primary" title="Edit District">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">No districts matched your search.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
