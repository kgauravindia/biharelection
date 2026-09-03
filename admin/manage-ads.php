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
        $stmt = $conn->prepare("DELETE FROM `advertisements` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Ad campaign deleted successfully.";
            } else {
                $error = "Error deleting ad campaign.";
            }
        }
    }
}

// Handle Status Change
if (isset($_GET['set_status']) && isset($_GET['id']) && $conn) {
    $set_id = (int)$_GET['id'];
    $set_status = sanitize($_GET['set_status']);
    $conn->query("UPDATE `advertisements` SET `status` = '$set_status' WHERE `id` = $set_id");
    header("Location: manage-ads.php?msg=status_updated");
    exit();
}

$ads = [];
$total_revenue = 0;

if ($conn) {
    $r = $conn->query("SELECT * FROM `advertisements` ORDER BY `id` DESC");
    if ($r) {
        while ($row = $r->fetch_assoc()) {
            $ads[] = $row;
            if (($row['status'] ?? '') === 'ACTIVE') {
                $total_revenue += (float)($row['amount'] ?? 0);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Advertisements — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Campaigns & Advertisements</h1>
                <p class="text-muted mb-0">Manage AC sponsorships, candidate banners, promotional packages & campaign revenue.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="edit-ad.php" class="btn btn-danger fw-semibold px-3 py-2 rounded-3 shadow-sm">
                    <i class="fas fa-plus me-1"></i> New Ad Campaign
                </a>
                <a href="../advertise.php" target="_blank" class="btn btn-outline-dark fw-semibold px-3 py-2 rounded-3 shadow-sm bg-white">
                    <i class="fas fa-bullhorn me-1"></i> View Pricing Page
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Ad Metrics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="stat-card-v2">
                    <span class="stat-label">Total Bookings</span>
                    <div class="stat-value text-dark"><?php echo count($ads); ?></div>
                    <small class="text-muted">Campaign Requests</small>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card-v2">
                    <span class="stat-label">Active Campaigns</span>
                    <div class="stat-value text-success">
                        <?php 
                        $active_count = count(array_filter($ads, function($a) { return ($a['status'] ?? '') === 'ACTIVE'; }));
                        echo $active_count;
                        ?>
                    </div>
                    <small class="text-muted">Currently Displayed</small>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="stat-card-v2">
                    <span class="stat-label">Realized Ad Revenue</span>
                    <div class="stat-value text-danger"><?php echo formatINR($total_revenue); ?></div>
                    <small class="text-muted">Active Placements</small>
                </div>
            </div>
        </div>

        <!-- Advertisements Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-rectangle-ad me-2 text-danger"></i> Advertising Campaigns</h6>
                <span class="badge bg-light text-dark border"><?php echo count($ads); ?> Campaigns</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Client / Contact</th>
                            <th>Package / Placement</th>
                            <th>Target Entity (AC/District)</th>
                            <th>Amount</th>
                            <th>Campaign Validity</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($ads)): ?>
                            <?php foreach ($ads as $ad): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($ad['client_name']); ?></div>
                                        <small class="text-muted">
                                            <a href="tel:<?php echo htmlspecialchars($ad['contact_phone']); ?>" class="text-decoration-none text-muted">
                                                <i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($ad['contact_phone']); ?>
                                            </a>
                                        </small>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($ad['product_type']); ?></span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-secondary"><?php echo htmlspecialchars($ad['target_entity'] ?? 'Statewide'); ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-dark"><?php echo formatINR($ad['amount'] ?? 0); ?></strong>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <?php echo !empty($ad['start_date']) ? date('d M Y', strtotime($ad['start_date'])) : '—'; ?>
                                            to
                                            <?php echo !empty($ad['end_date']) ? date('d M Y', strtotime($ad['end_date'])) : '—'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $st = $ad['status'] ?? 'PENDING';
                                        if ($st === 'ACTIVE') echo '<span class="badge badge-soft-success"><i class="fas fa-circle-check me-1"></i> Active</span>';
                                        elseif ($st === 'PENDING') echo '<span class="badge badge-soft-warning"><i class="fas fa-clock me-1"></i> Pending</span>';
                                        else echo '<span class="badge badge-soft-secondary"><i class="fas fa-ban me-1"></i> ' . htmlspecialchars($st) . '</span>';
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $ad['contact_phone']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Client">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            <a href="edit-ad.php?id=<?php echo $ad['id']; ?>" class="btn btn-sm btn-light border text-primary" title="Edit Campaign">
                                                <i class="fas fa-pen"></i>
                                            </a>
                                            <a href="manage-ads.php?delete_id=<?php echo $ad['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this ad?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-bullhorn fa-3x mb-3 d-block text-muted"></i>
                                    No advertising campaigns booked yet.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
