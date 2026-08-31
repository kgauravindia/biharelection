<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Handle CSV Export
if (isset($_GET['export_csv']) && $conn) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bihar_election_whatsapp_subscribers_' . date('Y-m-d') . '.csv');
    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Phone Number', 'District', 'Is Active', 'Subscribed At']);

    $res = $conn->query("SELECT * FROM `be_whatsapp_subscribers` ORDER BY `id` DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [$row['id'], $row['phone_number'], $row['district'], $row['is_active'], $row['created_at']]);
        }
    }
    fclose($output);
    exit();
}

// Handle Delete
if (isset($_GET['delete_id']) && $conn) {
    $del_id = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM `be_whatsapp_subscribers` WHERE `id` = $del_id");
    $message = "Subscriber removed.";
}

// Handle Add Subscriber
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_subscriber']) && $conn) {
    $phone = sanitize($_POST['phone_number'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    if (!empty($phone)) {
        $stmt = $conn->prepare("INSERT INTO `be_whatsapp_subscribers` (`phone_number`, `district`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `district` = VALUES(`district`), `is_active` = 1");
        if ($stmt) {
            $stmt->bind_param("ss", $phone, $district);
            $stmt->execute();
            $message = "Subscriber added successfully!";
        }
    }
}

$subscribers = [];
$total_subscribers = 0;
if ($conn) {
    $r = $conn->query("SELECT * FROM `be_whatsapp_subscribers` ORDER BY `id` DESC LIMIT 100");
    if ($r) {
        while ($row = $r->fetch_assoc()) $subscribers[] = $row;
    }
    $c = $conn->query("SELECT COUNT(*) as c FROM `be_whatsapp_subscribers`");
    if ($c) $total_subscribers = $c->fetch_assoc()['c'];
}

$districts = DataProvider::getDistricts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>WhatsApp Alerts & Subscribers — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">WhatsApp Subscribers & Broadcasts</h1>
                <p class="text-muted mb-0">Manage mobile voter alert network, export contact lists and craft broadcast updates.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="whatsapp-subscribers.php?export_csv=1" class="btn btn-success fw-semibold px-3 py-2 rounded-3 shadow-sm">
                    <i class="fas fa-file-csv me-1"></i> Export CSV
                </a>
                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-outline-success fw-semibold px-3 py-2 rounded-3 shadow-sm bg-white">
                    <i class="fab fa-whatsapp me-1"></i> Open Channel
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4 mb-4">
            <!-- Add Subscriber Form -->
            <div class="col-lg-4">
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-plus me-2 text-success"></i> Add Subscriber</h6>
                    </div>
                    <div class="section-card-body">
                        <form method="POST" action="">
                            <input type="hidden" name="add_subscriber" value="1">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number *</label>
                                <input type="text" name="phone_number" class="form-control" placeholder="+91 98765 43210" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">District</label>
                                <select name="district" class="form-select">
                                    <option value="">Statewide / All Bihar</option>
                                    <?php foreach ($districts as $d): ?>
                                        <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-success w-100 fw-bold rounded-3">
                                <i class="fab fa-whatsapp me-1"></i> Add to Broadcast List
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Broadcast Helper -->
                <div class="section-card">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-bullhorn me-2 text-primary"></i> Broadcast Template Composer</h6>
                    </div>
                    <div class="section-card-body">
                        <textarea id="broadcastText" class="form-control mb-2 small" rows="5">🔴 *Bihar Election 2025/2026 Alert* 🗳️

Check the latest candidate nominations, constituency ground report & VIP candidate profiles live on BiharElection.com:
🔗 https://biharelection.com/

Stay updated on every Assembly Constituency!</textarea>
                        <button class="btn btn-dark btn-sm w-100" onclick="copyBroadcast()"><i class="fas fa-copy me-1"></i> Copy Alert Message</button>
                    </div>
                </div>
            </div>

            <!-- Subscribers List -->
            <div class="col-lg-8">
                <div class="section-card">
                    <div class="section-card-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fab fa-whatsapp me-2 text-success"></i> WhatsApp Subscribers Registry</h6>
                        <span class="badge bg-light text-dark border"><?php echo number_format($total_subscribers); ?> Total</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Mobile Number</th>
                                    <th>District</th>
                                    <th>Joined At</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($subscribers)): ?>
                                    <?php foreach ($subscribers as $s): ?>
                                        <tr>
                                            <td>
                                                <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $s['phone_number']); ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                                    <i class="fab fa-whatsapp text-success me-1"></i><?php echo htmlspecialchars($s['phone_number']); ?>
                                                </a>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($s['district'] ?? 'Statewide'); ?></span></td>
                                            <td><small class="text-muted"><?php echo !empty($s['created_at']) ? date('d M Y, h:i A', strtotime($s['created_at'])) : '—'; ?></small></td>
                                            <td>
                                                <?php if (!empty($s['is_active'])): ?>
                                                    <span class="badge badge-soft-success">Active</span>
                                                <?php else: ?>
                                                    <span class="badge badge-soft-secondary">Inactive</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="whatsapp-subscribers.php?delete_id=<?php echo $s['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Remove subscriber?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="fab fa-whatsapp fa-3x mb-3 d-block text-success"></i>
                                            No WhatsApp alert subscribers registered yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>

<script>
function copyBroadcast() {
    const el = document.getElementById('broadcastText');
    el.select();
    navigator.clipboard.writeText(el.value);
    alert('Broadcast text copied to clipboard!');
}
</script>
