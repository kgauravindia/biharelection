<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$ad = [
    'id' => 0,
    'product_type' => 'AC_EXCLUSIVE',
    'client_name' => '',
    'contact_phone' => '',
    'contact_email' => '',
    'target_entity' => 'Patna Sahib',
    'amount' => '15000',
    'banner_url' => '',
    'status' => 'ACTIVE',
    'start_date' => date('Y-m-d'),
    'end_date' => date('Y-m-d', strtotime('+30 days'))
];

if ($id > 0 && $conn) {
    $stmt = $conn->prepare("SELECT * FROM `advertisements` WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $ad = $res->fetch_assoc();
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_type = sanitize($_POST['product_type'] ?? '');
    $client_name = sanitize($_POST['client_name'] ?? '');
    $contact_phone = sanitize($_POST['contact_phone'] ?? '');
    $contact_email = sanitize($_POST['contact_email'] ?? '');
    $target_entity = sanitize($_POST['target_entity'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $banner_url = sanitize($_POST['banner_url'] ?? '');
    $status = sanitize($_POST['status'] ?? 'PENDING');
    $start_date = sanitize($_POST['start_date'] ?? date('Y-m-d'));
    $end_date = sanitize($_POST['end_date'] ?? date('Y-m-d', strtotime('+30 days')));

    // Handle banner file upload
    if (isset($_FILES['banner_file']) && $_FILES['banner_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/ads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['banner_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $new_filename = 'ad_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['banner_file']['tmp_name'], $upload_dir . $new_filename)) {
                $banner_url = 'uploads/ads/' . $new_filename;
            }
        }
    }

    if (empty($client_name) || empty($contact_phone)) {
        $error = "Client Name and Contact Phone are required.";
    } elseif ($conn) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE `advertisements` SET `product_type`=?, `client_name`=?, `contact_phone`=?, `contact_email`=?, `target_entity`=?, `amount`=?, `banner_url`=?, `status`=?, `start_date`=?, `end_date`=? WHERE `id`=?");
            if ($stmt) {
                $stmt->bind_param("sssssdssssi", $product_type, $client_name, $contact_phone, $contact_email, $target_entity, $amount, $banner_url, $status, $start_date, $end_date, $id);
                if ($stmt->execute()) {
                    $message = "Ad campaign updated successfully!";
                } else {
                    $error = "Error updating ad: " . $conn->error;
                }
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO `advertisements` (`product_type`, `client_name`, `contact_phone`, `contact_email`, `target_entity`, `amount`, `banner_url`, `status`, `start_date`, `end_date`) VALUES (?,?,?,?,?,?,?,?,?,?)");
            if ($stmt) {
                $stmt->bind_param("sssssdssss", $product_type, $client_name, $contact_phone, $contact_email, $target_entity, $amount, $banner_url, $status, $start_date, $end_date);
                if ($stmt->execute()) {
                    $id = $stmt->insert_id;
                    $message = "Ad campaign created successfully!";
                } else {
                    $error = "Error creating ad: " . $conn->error;
                }
            }
        }
    }

    $ad = array_merge($ad, [
        'product_type' => $product_type, 'client_name' => $client_name,
        'contact_phone' => $contact_phone, 'contact_email' => $contact_email,
        'target_entity' => $target_entity, 'amount' => $amount,
        'banner_url' => $banner_url, 'status' => $status,
        'start_date' => $start_date, 'end_date' => $end_date
    ]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($id > 0) ? 'Edit Campaign' : 'Create Ad Campaign'; ?> — Bihar Election</title>
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
                <a href="manage-ads.php" class="text-decoration-none text-muted small mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to Advertisements
                </a>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    <?php echo ($id > 0) ? 'Edit Campaign Booking' : 'New Campaign Booking'; ?>
                </h1>
                <p class="text-muted mb-0">Set up campaign client, target entity, banner creative and active duration.</p>
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

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-rectangle-ad me-2 text-danger"></i> Campaign Details</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Client / Candidate Name *</label>
                                    <input type="text" name="client_name" class="form-control" value="<?php echo htmlspecialchars($ad['client_name']); ?>" required placeholder="e.g. Bihar Jan Jagriti Morcha">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Contact Mobile Phone *</label>
                                    <input type="text" name="contact_phone" class="form-control" value="<?php echo htmlspecialchars($ad['contact_phone']); ?>" required placeholder="+91 9876543210">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Contact Email</label>
                                    <input type="email" name="contact_email" class="form-control" value="<?php echo htmlspecialchars($ad['contact_email'] ?? ''); ?>" placeholder="client@domain.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Target AC / District</label>
                                    <input type="text" name="target_entity" class="form-control" value="<?php echo htmlspecialchars($ad['target_entity'] ?? ''); ?>" placeholder="e.g. Danapur or Patna">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Product / Placement Type</label>
                                    <select name="product_type" class="form-select">
                                        <option value="AC_EXCLUSIVE" <?php echo ($ad['product_type'] === 'AC_EXCLUSIVE') ? 'selected' : ''; ?>>AC Exclusive Sponsorship</option>
                                        <option value="DISTRICT_BANNER" <?php echo ($ad['product_type'] === 'DISTRICT_BANNER') ? 'selected' : ''; ?>>District Header Banner</option>
                                        <option value="CANDIDATE_FEATURED" <?php echo ($ad['product_type'] === 'CANDIDATE_FEATURED') ? 'selected' : ''; ?>>Featured Candidate Showcase</option>
                                        <option value="HOMEPAGE_TOP" <?php echo ($ad['product_type'] === 'HOMEPAGE_TOP') ? 'selected' : ''; ?>>Homepage Top Banner</option>
                                        <option value="WHATSAPP_BROADCAST" <?php echo ($ad['product_type'] === 'WHATSAPP_BROADCAST') ? 'selected' : ''; ?>>WhatsApp Broadcast Sponsor</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Campaign Amount (₹)</label>
                                    <input type="number" step="0.01" name="amount" class="form-control" value="<?php echo htmlspecialchars($ad['amount']); ?>" placeholder="15000">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Campaign Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($ad['start_date'] ?? date('Y-m-d')); ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Campaign End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($ad['end_date'] ?? date('Y-m-d', strtotime('+30 days'))); ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <!-- Banner Creative -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-image me-2 text-primary"></i> Banner Creative</h6>
                        </div>
                        <div class="section-card-body">
                            <?php if (!empty($ad['banner_url'])): ?>
                                <div class="mb-3 rounded overflow-hidden border shadow-sm">
                                    <img src="../<?php echo htmlspecialchars($ad['banner_url']); ?>" alt="Banner" style="width: 100%; height: auto; max-height: 180px; object-fit: cover;">
                                </div>
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Upload Banner Image</label>
                                <input type="file" name="banner_file" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Or Creative URL</label>
                                <input type="text" name="banner_url" class="form-control form-control-sm" value="<?php echo htmlspecialchars($ad['banner_url'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-toggle-on me-2 text-success"></i> Campaign Status</h6>
                        </div>
                        <div class="section-card-body">
                            <select name="status" class="form-select">
                                <option value="ACTIVE" <?php echo (($ad['status'] ?? '') === 'ACTIVE') ? 'selected' : ''; ?>>Active (Live)</option>
                                <option value="PENDING" <?php echo (($ad['status'] ?? '') === 'PENDING') ? 'selected' : ''; ?>>Pending Approval / Payment</option>
                                <option value="EXPIRED" <?php echo (($ad['status'] ?? '') === 'EXPIRED') ? 'selected' : ''; ?>>Expired / Completed</option>
                                <option value="CANCELLED" <?php echo (($ad['status'] ?? '') === 'CANCELLED') ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm rounded-3">
                            <i class="fas fa-save me-2"></i> Save Campaign
                        </button>
                        <a href="manage-ads.php" class="btn btn-light border btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <?php include 'admin-footer.php'; ?>
