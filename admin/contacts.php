<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Handle Status Update
if (isset($_GET['status_id']) && isset($_GET['set_status']) && $conn) {
    $sid = (int)$_GET['status_id'];
    $st = sanitize($_GET['set_status']);
    $conn->query("UPDATE `be_contacts` SET `status` = '$st' WHERE `id` = $sid");
    header("Location: contacts.php?msg=updated");
    exit();
}

// Handle Delete
if (isset($_GET['delete_id']) && $conn) {
    $did = (int)$_GET['delete_id'];
    $conn->query("DELETE FROM `be_contacts` WHERE `id` = $did");
    $message = "Lead inquiry deleted.";
}

// Filters & Search
$search = isset($_GET['q']) ? sanitize($_GET['q']) : '';
$filter_status = isset($_GET['status']) ? sanitize($_GET['status']) : '';
$filter_type = isset($_GET['type']) ? sanitize($_GET['type']) : '';

$contacts = [];
$total_rows = 0;

if ($conn) {
    $where = "1=1";
    if (!empty($search)) {
        $where .= " AND (`name` LIKE '%$search%' OR `mobile` LIKE '%$search%' OR `message` LIKE '%$search%')";
    }
    if (!empty($filter_status)) {
        $where .= " AND `status` = '$filter_status'";
    }
    if (!empty($filter_type)) {
        $where .= " AND `inquiry_type` = '$filter_type'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `be_contacts` WHERE $where");
    if ($c_res) $total_rows = $c_res->fetch_assoc()['c'];

    $res = $conn->query("SELECT * FROM `be_contacts` WHERE $where ORDER BY `id` DESC LIMIT 50");
    if ($res) {
        while ($row = $res->fetch_assoc()) $contacts[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enquiries & Leads — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Leads & Inquiries</h1>
                <p class="text-muted mb-0">Follow up on advertising bookings, candidate inquiries and public feedback.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-envelope-open-text me-1"></i> <?php echo number_format($total_rows); ?> Inquiries Received
                </span>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Search Bar -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="contacts.php" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by name, phone or message..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Inquiry Types</option>
                            <option value="GENERAL" <?php echo ($filter_type === 'GENERAL') ? 'selected' : ''; ?>>General Feedback</option>
                            <option value="ADVERTISING" <?php echo ($filter_type === 'ADVERTISING') ? 'selected' : ''; ?>>Advertising Campaign</option>
                            <option value="CANDIDATE_REGISTRATION" <?php echo ($filter_type === 'CANDIDATE_REGISTRATION') ? 'selected' : ''; ?>>Candidate Registration</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="NEW" <?php echo ($filter_status === 'NEW') ? 'selected' : ''; ?>>New</option>
                            <option value="CONTACTED" <?php echo ($filter_status === 'CONTACTED') ? 'selected' : ''; ?>>Contacted</option>
                            <option value="CONVERTED" <?php echo ($filter_status === 'CONVERTED') ? 'selected' : ''; ?>>Converted</option>
                            <option value="CLOSED" <?php echo ($filter_status === 'CLOSED') ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-1">
                        <button type="submit" class="btn btn-dark w-100"><i class="fas fa-filter"></i></button>
                        <a href="contacts.php" class="btn btn-light border" title="Reset"><i class="fas fa-undo"></i></a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Inquiries Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-inbox me-2 text-danger"></i> Inbound Lead Inquiries</h6>
                <span class="badge bg-light text-dark border"><?php echo count($contacts); ?> Inquiries</span>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Contact / Sender</th>
                            <th>Inquiry Type</th>
                            <th>District / AC</th>
                            <th>Message / Requirements</th>
                            <th>Status</th>
                            <th>Received Date</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($contacts)): ?>
                            <?php foreach ($contacts as $c): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['name']); ?></div>
                                        <div class="small">
                                            <a href="tel:<?php echo htmlspecialchars($c['mobile']); ?>" class="text-decoration-none text-muted">
                                                <i class="fas fa-phone text-success me-1"></i><?php echo htmlspecialchars($c['mobile']); ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($c['email'])): ?>
                                            <small class="text-muted d-block"><?php echo htmlspecialchars($c['email']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars($c['inquiry_type'] ?? 'GENERAL'); ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small"><?php echo htmlspecialchars($c['district'] ?? '—'); ?></div>
                                        <small class="text-muted"><?php echo htmlspecialchars($c['constituency'] ?? ''); ?></small>
                                    </td>
                                    <td>
                                        <div class="small text-dark" style="max-width: 280px; word-break: break-word;">
                                            <?php echo nl2br(htmlspecialchars($c['message'] ?? '')); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                        $st = $c['status'] ?? 'NEW';
                                        if ($st === 'NEW') echo '<span class="badge badge-soft-danger"><i class="fas fa-bell me-1"></i> New</span>';
                                        elseif ($st === 'CONTACTED') echo '<span class="badge badge-soft-warning"><i class="fas fa-clock me-1"></i> Contacted</span>';
                                        elseif ($st === 'CONVERTED') echo '<span class="badge badge-soft-success"><i class="fas fa-check-double me-1"></i> Converted</span>';
                                        else echo '<span class="badge badge-soft-secondary">Closed</span>';
                                        ?>
                                        
                                        <div class="dropdown d-inline ms-1">
                                            <button class="btn btn-sm btn-link text-muted p-0" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                <li><a class="dropdown-item small" href="contacts.php?status_id=<?php echo $c['id']; ?>&set_status=NEW">Mark as New</a></li>
                                                <li><a class="dropdown-item small" href="contacts.php?status_id=<?php echo $c['id']; ?>&set_status=CONTACTED">Mark as Contacted</a></li>
                                                <li><a class="dropdown-item small" href="contacts.php?status_id=<?php echo $c['id']; ?>&set_status=CONVERTED">Mark as Converted</a></li>
                                                <li><a class="dropdown-item small" href="contacts.php?status_id=<?php echo $c['id']; ?>&set_status=CLOSED">Mark as Closed</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo !empty($c['created_at']) ? date('d M Y, h:i A', strtotime($c['created_at'])) : '—'; ?></small>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $c['mobile']); ?>" target="_blank" class="btn btn-sm btn-light border text-success" title="WhatsApp Sender">
                                                <i class="fab fa-whatsapp"></i>
                                            </a>
                                            <a href="contacts.php?delete_id=<?php echo $c['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this inquiry?');" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block text-muted"></i>
                                    No lead inquiries in database.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
