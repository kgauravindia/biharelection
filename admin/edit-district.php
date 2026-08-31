<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
$district = DataProvider::getDistrictBySlug($slug);

if (!$district) {
    header("Location: districts.php?error=notfound");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $name_hi = sanitize($_POST['name_hi'] ?? '');
    $headquarters = sanitize($_POST['headquarters'] ?? '');
    $division = sanitize($_POST['division'] ?? '');
    $total_ac = (int)($_POST['total_ac'] ?? 0);
    $total_electors = (int)($_POST['total_electors'] ?? 0);
    $description = sanitize($_POST['description'] ?? '');

    if ($conn) {
        $stmt = $conn->prepare("UPDATE `be_districts` SET `name`=?, `name_hi`=?, `headquarters`=?, `division`=?, `total_ac`=?, `total_electors`=?, `description`=? WHERE `slug`=?");
        if ($stmt) {
            $stmt->bind_param("ssssiiss", $name, $name_hi, $headquarters, $division, $total_ac, $total_electors, $description, $slug);
            if ($stmt->execute()) {
                $message = "District {$name} updated successfully!";
            } else {
                $error = "Error updating database: " . $conn->error;
            }
        }
    } else {
        $message = "District updated (local cache active).";
    }

    $district = array_merge($district, [
        'name' => $name, 'name_hi' => $name_hi, 'headquarters' => $headquarters,
        'division' => $division, 'total_ac' => $total_ac, 'total_electors' => $total_electors,
        'description' => $description
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit District — <?php echo htmlspecialchars($district['name']); ?> Admin</title>
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
                <a href="districts.php" class="text-decoration-none text-muted small mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to 38 Districts
                </a>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    Edit District — <?php echo htmlspecialchars($district['name']); ?>
                </h1>
                <p class="text-muted mb-0">Headquarters: <?php echo htmlspecialchars($district['headquarters']); ?> | Division: <?php echo htmlspecialchars($district['division']); ?></p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="../district.php?slug=<?php echo urlencode($district['slug']); ?>" target="_blank" class="btn btn-outline-dark btn-sm rounded-3 shadow-sm bg-white">
                    <i class="fas fa-external-link-alt me-1"></i> View Live District Page
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
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-map-location-dot me-2 text-success"></i> District Metadata</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">District Name (English) *</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($district['name']); ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">District Name (Hindi)</label>
                                    <input type="text" name="name_hi" class="form-control" value="<?php echo htmlspecialchars($district['name_hi'] ?? ''); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Headquarters City</label>
                                    <input type="text" name="headquarters" class="form-control" value="<?php echo htmlspecialchars($district['headquarters']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Administrative Division</label>
                                    <input type="text" name="division" class="form-control" value="<?php echo htmlspecialchars($district['division']); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Total Assembly Constituencies</label>
                                    <input type="number" name="total_ac" class="form-control" value="<?php echo (int)$district['total_ac']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Total Electors (Approx)</label>
                                    <input type="number" name="total_electors" class="form-control" value="<?php echo (int)($district['total_electors'] ?? 0); ?>">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">District Overview & Description</label>
                                    <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($district['description'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-list-ol me-2 text-danger"></i> Associated Constituencies</h6>
                        </div>
                        <div class="section-card-body">
                            <?php if (!empty($district['ac_list']) && is_array($district['ac_list'])): ?>
                                <div class="d-flex flex-column gap-2">
                                    <?php foreach ($district['ac_list'] as $ac_item): ?>
                                        <div class="p-2 bg-light rounded border d-flex justify-content-between align-items-center">
                                            <span class="fw-semibold text-dark small"><?php echo htmlspecialchars($ac_item); ?></span>
                                            <span class="badge bg-danger">AC</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small mb-0">No AC list mapping loaded.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm rounded-3">
                            <i class="fas fa-save me-2"></i> Save District Details
                        </button>
                        <a href="districts.php" class="btn btn-light border btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <?php include 'admin-footer.php'; ?>
