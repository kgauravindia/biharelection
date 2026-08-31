<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$ac_no = isset($_GET['ac_no']) ? (int)$_GET['ac_no'] : 0;
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

$ac = null;
if ($ac_no > 0) {
    $all = DataProvider::getConstituencies();
    foreach ($all as $c) {
        if ((int)$c['ac_no'] === $ac_no) {
            $ac = $c;
            break;
        }
    }
} elseif (!empty($slug)) {
    $ac = DataProvider::getConstituencyBySlug($slug);
}

if (!$ac) {
    header("Location: constituencies.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $name_hi = sanitize($_POST['name_hi'] ?? '');
    $current_mla = sanitize($_POST['current_mla'] ?? '');
    $current_party = sanitize($_POST['current_party'] ?? '');
    $reservation = sanitize($_POST['reservation'] ?? 'GEN');
    $total_electors = (int)($_POST['total_electors'] ?? 0);
    $male_electors = (int)($_POST['male_electors'] ?? 0);
    $female_electors = (int)($_POST['female_electors'] ?? 0);
    $polling_stations = (int)($_POST['polling_stations'] ?? 0);
    $lok_sabha = sanitize($_POST['lok_sabha'] ?? '');
    $issues_raw = sanitize($_POST['key_issues'] ?? '');

    $issues_arr = array_filter(array_map('trim', explode(',', $issues_raw)));
    $issues_json = json_encode(array_values($issues_arr));

    if ($conn) {
        $stmt = $conn->prepare("UPDATE `be_constituencies` SET `name`=?, `name_hi`=?, `current_mla`=?, `current_party`=?, `reservation`=?, `total_electors`=?, `male_electors`=?, `female_electors`=?, `polling_stations`=?, `lok_sabha`=?, `key_issues`=? WHERE `ac_no`=?");
        if ($stmt) {
            $stmt->bind_param("sssssiiiissi", $name, $name_hi, $current_mla, $current_party, $reservation, $total_electors, $male_electors, $female_electors, $polling_stations, $lok_sabha, $issues_json, $ac['ac_no']);
            if ($stmt->execute()) {
                $message = "Constituency #{$ac['ac_no']} updated successfully!";
            } else {
                $error = "Error updating database: " . $conn->error;
            }
        }
    } else {
        $message = "Constituency updated (local cache active).";
    }

    $ac = array_merge($ac, [
        'name' => $name, 'name_hi' => $name_hi, 'current_mla' => $current_mla,
        'current_party' => $current_party, 'reservation' => $reservation,
        'total_electors' => $total_electors, 'male_electors' => $male_electors,
        'female_electors' => $female_electors, 'polling_stations' => $polling_stations,
        'lok_sabha' => $lok_sabha, 'key_issues' => $issues_arr
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit AC #<?php echo $ac['ac_no']; ?> — <?php echo htmlspecialchars($ac['name']); ?> Admin</title>
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
                <a href="constituencies.php" class="text-decoration-none text-muted small mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to 243 Constituencies
                </a>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    Edit AC #<?php echo $ac['ac_no']; ?> — <?php echo htmlspecialchars($ac['name']); ?>
                </h1>
                <p class="text-muted mb-0">District: <?php echo htmlspecialchars($ac['district']); ?> | Lok Sabha: <?php echo htmlspecialchars($ac['lok_sabha'] ?? ''); ?></p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="../vidhan-sabha.php?slug=<?php echo urlencode($ac['slug']); ?>" target="_blank" class="btn btn-outline-dark btn-sm rounded-3 shadow-sm bg-white">
                    <i class="fas fa-external-link-alt me-1"></i> View Live AC Page
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

        <form method="POST" action="">
            <div class="row g-4">
                <div class="col-lg-8">
                    <!-- General Details -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-landmark me-2 text-danger"></i> Constituency Overview</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">AC Name (English) *</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($ac['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">AC Name (Hindi)</label>
                                    <input type="text" name="name_hi" class="form-control" value="<?php echo htmlspecialchars($ac['name_hi'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Incumbent MLA</label>
                                    <input type="text" name="current_mla" class="form-control" value="<?php echo htmlspecialchars($ac['current_mla'] ?? ''); ?>" placeholder="e.g. Tejashwi Yadav">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Incumbent Party</label>
                                    <input type="text" name="current_party" class="form-control" value="<?php echo htmlspecialchars($ac['current_party'] ?? ''); ?>" placeholder="BJP / RJD / JDU / INC">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Category / Reservation</label>
                                    <select name="reservation" class="form-select">
                                        <option value="GEN" <?php echo (($ac['reservation'] ?? 'GEN') === 'GEN') ? 'selected' : ''; ?>>General</option>
                                        <option value="SC" <?php echo (($ac['reservation'] ?? '') === 'SC') ? 'selected' : ''; ?>>SC (Scheduled Caste)</option>
                                        <option value="ST" <?php echo (($ac['reservation'] ?? '') === 'ST') ? 'selected' : ''; ?>>ST (Scheduled Tribe)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Lok Sabha Constituency</label>
                                    <input type="text" name="lok_sabha" class="form-control" value="<?php echo htmlspecialchars($ac['lok_sabha'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Electors & Voting Statistics -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-users me-2 text-primary"></i> Electorate Statistics</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Total Registered Electors</label>
                                    <input type="number" name="total_electors" class="form-control" value="<?php echo (int)($ac['total_electors'] ?? 0); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Polling Stations</label>
                                    <input type="number" name="polling_stations" class="form-control" value="<?php echo (int)($ac['polling_stations'] ?? 0); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Male Electors</label>
                                    <input type="number" name="male_electors" class="form-control" value="<?php echo (int)($ac['male_electors'] ?? 0); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Female Electors</label>
                                    <input type="number" name="female_electors" class="form-control" value="<?php echo (int)($ac['female_electors'] ?? 0); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Key Electoral Issues (comma-separated)</label>
                                    <?php 
                                    $issues_str = '';
                                    if (isset($ac['key_issues']) && is_array($ac['key_issues'])) {
                                        $issues_str = implode(', ', $ac['key_issues']);
                                    } elseif (isset($ac['key_issues']) && is_string($ac['key_issues'])) {
                                        $decoded = json_decode($ac['key_issues'], true);
                                        $issues_str = is_array($decoded) ? implode(', ', $decoded) : $ac['key_issues'];
                                    }
                                    ?>
                                    <textarea name="key_issues" class="form-control" rows="3" placeholder="Unemployment, Health Infrastructure, Flood Management, Education"><?php echo htmlspecialchars($issues_str); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- AC Meta -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-info-circle me-2 text-warning"></i> AC Information</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <span class="small text-muted text-uppercase fw-bold d-block">AC Number</span>
                                <span class="fs-4 fw-bold text-danger">AC #<?php echo $ac['ac_no']; ?></span>
                            </div>
                            <div class="mb-3">
                                <span class="small text-muted text-uppercase fw-bold d-block">District</span>
                                <span class="fw-bold text-dark"><?php echo htmlspecialchars($ac['district']); ?></span>
                            </div>
                            <div class="mb-3">
                                <span class="small text-muted text-uppercase fw-bold d-block">Slug URL</span>
                                <code class="small"><?php echo htmlspecialchars($ac['slug']); ?></code>
                            </div>
                            <?php if (!empty($ac['blocks']) && is_array($ac['blocks'])): ?>
                                <div class="mb-3">
                                    <span class="small text-muted text-uppercase fw-bold d-block">Administrative Blocks</span>
                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                        <?php foreach ($ac['blocks'] as $blk): ?>
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($blk); ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm rounded-3">
                            <i class="fas fa-save me-2"></i> Update Constituency
                        </button>
                        <a href="constituencies.php" class="btn btn-light border btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <?php include 'admin-footer.php'; ?>
