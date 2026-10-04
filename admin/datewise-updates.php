<?php
/**
 * BiharElection.com - Admin Datewise Website Updates & Live Election Bulletins
 * Comprehensive editorial timeline, daily changelog, and website activity tracker.
 */
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Handle CSV Export
if (isset($_GET['action']) && $_GET['action'] === 'export_csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bihar_election_updates_' . date('Y-m-d_His') . '.csv');
    $output = fopen('php://output', 'w');
    // UTF-8 BOM for Excel
    fputs($output, "\xEF\xBB\xBF");
    fputcsv($output, ['ID', 'Date', 'Time', 'Title (English)', 'Title (Hindi)', 'Category', 'Target Scope', 'Priority', 'Status', 'Pinned', 'URL', 'Description', 'Author', 'Created At']);
    
    if ($conn) {
        $res = $conn->query("SELECT * FROM `site_updates` ORDER BY `update_date` DESC, `update_time` DESC, `id` DESC");
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['update_date'],
                $row['update_time'],
                $row['title'],
                $row['title_hi'] ?? '',
                $row['category'],
                $row['target_scope'] ?? '',
                $row['priority'],
                $row['status'],
                $row['is_pinned'] ? 'Yes' : 'No',
                $row['url'] ?? '',
                $row['description'] ?? '',
                $row['author'] ?? '',
                $row['created_at']
            ]);
        }
    }
    fclose($output);
    exit();
}

// Handle Add / Edit Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $update_id = isset($_POST['update_id']) ? (int)$_POST['update_id'] : 0;
    $update_date = trim($_POST['update_date'] ?? date('Y-m-d'));
    $update_time = trim($_POST['update_time'] ?? date('H:i:s'));
    $title = trim($_POST['title'] ?? '');
    $title_hi = trim($_POST['title_hi'] ?? '');
    $category = trim($_POST['category'] ?? 'General Update');
    $target_scope = trim($_POST['target_scope'] ?? 'Bihar Statewide');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $priority = in_array($_POST['priority'] ?? '', ['normal', 'important', 'breaking']) ? $_POST['priority'] : 'normal';
    $is_pinned = isset($_POST['is_pinned']) ? 1 : 0;
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    $author = !empty($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin Editorial';

    if (empty($title)) {
        $error = "Please enter a title for the update.";
    } else {
        if ($conn) {
            if ($update_id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE `site_updates` SET `update_date` = ?, `update_time` = ?, `title` = ?, `title_hi` = ?, `category` = ?, `target_scope` = ?, `description` = ?, `url` = ?, `priority` = ?, `is_pinned` = ?, `status` = ?, `author` = ? WHERE `id` = ?");
                if ($stmt) {
                    $stmt->bind_param("sssssssssissi", $update_date, $update_time, $title, $title_hi, $category, $target_scope, $description, $url, $priority, $is_pinned, $status, $author, $update_id);
                    if ($stmt->execute()) {
                        $message = "Website update #{$update_id} successfully modified.";
                    } else {
                        $error = "Error updating record: " . $conn->error;
                    }
                }
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO `site_updates` (`update_date`, `update_time`, `title`, `title_hi`, `category`, `target_scope`, `description`, `url`, `priority`, `is_pinned`, `status`, `author`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sssssssssiss", $update_date, $update_time, $title, $title_hi, $category, $target_scope, $description, $url, $priority, $is_pinned, $status, $author);
                    if ($stmt->execute()) {
                        $message = "New datewise update added successfully for {$update_date}.";
                    } else {
                        $error = "Error creating update: " . $conn->error;
                    }
                }
            }
        }
    }
}

// Handle Delete Update
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($conn && $del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `site_updates` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Update deleted successfully.";
            } else {
                $error = "Error deleting update.";
            }
        }
    }
}

// Handle Toggle Pin
if (isset($_GET['toggle_pin'])) {
    $pin_id = (int)$_GET['toggle_pin'];
    if ($conn && $pin_id > 0) {
        $conn->query("UPDATE `site_updates` SET `is_pinned` = (1 - `is_pinned`) WHERE `id` = $pin_id");
        header("Location: datewise-updates.php?msg=pin_toggled");
        exit();
    }
}

// Filter parameters
$date_filter = trim($_GET['date_preset'] ?? 'all');
$date_from = trim($_GET['date_from'] ?? '');
$date_to = trim($_GET['date_to'] ?? '');
$category_filter = trim($_GET['category'] ?? '');
$priority_filter = trim($_GET['priority'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$where_clauses = ["1=1"];

if ($date_filter === 'today') {
    $today = date('Y-m-d');
    $where_clauses[] = "`update_date` = '$today'";
} elseif ($date_filter === 'yesterday') {
    $yesterday = date('Y-m-d', strtotime('-1 day'));
    $where_clauses[] = "`update_date` = '$yesterday'";
} elseif ($date_filter === 'last7') {
    $last7 = date('Y-m-d', strtotime('-7 days'));
    $where_clauses[] = "`update_date` >= '$last7'";
} elseif ($date_filter === 'this_month') {
    $first_day = date('Y-m-01');
    $where_clauses[] = "`update_date` >= '$first_day'";
} elseif (!empty($date_from) && !empty($date_to)) {
    $safe_from = $conn ? $conn->real_escape_string($date_from) : $date_from;
    $safe_to = $conn ? $conn->real_escape_string($date_to) : $date_to;
    $where_clauses[] = "`update_date` BETWEEN '$safe_from' AND '$safe_to'";
}

if (!empty($category_filter)) {
    $safe_cat = $conn ? $conn->real_escape_string($category_filter) : $category_filter;
    $where_clauses[] = "`category` = '$safe_cat'";
}

if (!empty($priority_filter)) {
    $safe_prio = $conn ? $conn->real_escape_string($priority_filter) : $priority_filter;
    $where_clauses[] = "`priority` = '$safe_prio'";
}

if (!empty($status_filter)) {
    $safe_st = $conn ? $conn->real_escape_string($status_filter) : $status_filter;
    $where_clauses[] = "`status` = '$safe_st'";
}

$where_sql = implode(' AND ', $where_clauses);

// Fetch Overall Stats
$stats = [
    'total' => 0,
    'today' => 0,
    'breaking' => 0,
    'pinned' => 0
];

if ($conn) {
    $r = $conn->query("SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN `update_date` = CURDATE() THEN 1 ELSE 0 END) as today,
        SUM(CASE WHEN `priority` = 'breaking' THEN 1 ELSE 0 END) as breaking,
        SUM(CASE WHEN `is_pinned` = 1 THEN 1 ELSE 0 END) as pinned
    FROM `site_updates`");
    if ($r) {
        $row = $r->fetch_assoc();
        $stats['total'] = (int)($row['total'] ?? 0);
        $stats['today'] = (int)($row['today'] ?? 0);
        $stats['breaking'] = (int)($row['breaking'] ?? 0);
        $stats['pinned'] = (int)($row['pinned'] ?? 0);
    }
}

// Fetch Updates grouped by Date
$all_updates = [];
$dates_grouped = [];
if ($conn) {
    $query = "SELECT * FROM `site_updates` WHERE $where_sql ORDER BY `is_pinned` DESC, `update_date` DESC, `update_time` DESC, `id` DESC";
    $res = $conn->query($query);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $all_updates[] = $row;
            $d = $row['update_date'];
            if (!isset($dates_grouped[$d])) {
                $dates_grouped[$d] = [];
            }
            $dates_grouped[$d][] = $row;
        }
    }
}

// Fetch automated website activity per date for recent dates (Posts, Users, Enquiries)
$daily_activity = [];
if ($conn) {
    // 1. Posts count by date
    $p_res = $conn->query("SELECT DATE(`published_at`) as dt, COUNT(*) as c FROM `posts` GROUP BY dt ORDER BY dt DESC LIMIT 30");
    if ($p_res) {
        while ($pr = $p_res->fetch_assoc()) {
            $dt = $pr['dt'];
            if ($dt) {
                if (!isset($daily_activity[$dt])) $daily_activity[$dt] = ['posts' => 0, 'users' => 0, 'contacts' => 0];
                $daily_activity[$dt]['posts'] = (int)$pr['c'];
            }
        }
    }

    // 2. Citizens count by date
    $u_res = $conn->query("SELECT DATE(`created_at`) as dt, COUNT(*) as c FROM `users` GROUP BY dt ORDER BY dt DESC LIMIT 30");
    if ($u_res) {
        while ($ur = $u_res->fetch_assoc()) {
            $dt = $ur['dt'];
            if ($dt) {
                if (!isset($daily_activity[$dt])) $daily_activity[$dt] = ['posts' => 0, 'users' => 0, 'contacts' => 0];
                $daily_activity[$dt]['users'] = (int)$ur['c'];
            }
        }
    }

    // 3. Contacts / Inquiries count by date
    $c_res = $conn->query("SELECT DATE(`created_at`) as dt, COUNT(*) as c FROM `contacts` GROUP BY dt ORDER BY dt DESC LIMIT 30");
    if ($c_res) {
        while ($cr = $c_res->fetch_assoc()) {
            $dt = $cr['dt'];
            if ($dt) {
                if (!isset($daily_activity[$dt])) $daily_activity[$dt] = ['posts' => 0, 'users' => 0, 'contacts' => 0];
                $daily_activity[$dt]['contacts'] = (int)$cr['c'];
            }
        }
    }
}

// Available Categories for dropdown
$categories_list = [
    'MLC Election 2026',
    'Assembly Election 2025',
    'ECI Gazette & Notification',
    'Constituency & Candidate Data',
    'Voter Registration',
    'Panchayat & Rural Governance',
    'Census & Demographic Data',
    'Breaking News',
    'Website System & Features',
    'General Update'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Datewise Website Updates &amp; Daily Bulletins — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .timeline-date-header {
            position: sticky;
            top: 70px;
            z-index: 5;
            background: rgba(248, 250, 252, 0.95);
            backdrop-filter: blur(8px);
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 1.25rem;
        }
        .update-item-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.25s ease;
            position: relative;
        }
        .update-item-card:hover {
            border-color: #93c5fd;
            box-shadow: 0 8px 24px rgba(37, 99, 235, 0.08);
            transform: translateY(-2px);
        }
        .update-item-card.priority-breaking {
            border-left: 5px solid #ef4444 !important;
            background: linear-gradient(to right, #fef2f2 0%, #ffffff 10%);
        }
        .update-item-card.priority-important {
            border-left: 5px solid #f59e0b !important;
            background: linear-gradient(to right, #fffbeb 0%, #ffffff 10%);
        }
        .update-item-card.is-pinned {
            border-color: #f59e0b;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.12);
        }
        .stat-card-custom {
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 1.25rem;
            transition: all 0.2s ease;
        }
        .stat-card-custom:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .filter-btn-date.active {
            background-color: #1e3a8a !important;
            color: #ffffff !important;
            border-color: #1e3a8a !important;
        }
        .badge-category {
            background: #eff6ff;
            color: #1d4ed8;
            font-weight: 600;
            border: 1px solid #bfdbfe;
            border-radius: 50px;
            padding: 4px 10px;
            font-size: 0.75rem;
        }
        .badge-priority-breaking {
            background: #fee2e2;
            color: #dc2626;
            font-weight: 700;
            border-radius: 50px;
            padding: 3px 10px;
            font-size: 0.72rem;
            animation: pulseBreaking 2s infinite ease-in-out;
        }
        @keyframes pulseBreaking {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }
        .badge-priority-important {
            background: #fef3c7;
            color: #b45309;
            font-weight: 700;
            border-radius: 50px;
            padding: 3px 10px;
            font-size: 0.72rem;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>

    <main class="main-content">
        <?php include 'admin-header.php'; ?>

        <div class="content-wrapper p-3 p-lg-4">
            
            <!-- Breadcrumbs & Header -->
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small">
                            <li class="breadcrumb-item"><a href="index.php">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Datewise Updates</li>
                        </ol>
                    </nav>
                    <h2 class="h4 fw-bold text-dark mb-0">
                        <i class="fas fa-calendar-check text-warning me-2"></i> Datewise Website Updates &amp; Daily Bulletins
                    </h2>
                    <p class="text-muted small mb-0">Editorial daily changelog, ECI announcements, and automated date-by-date website activity log.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="?action=export_csv" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold btn-sm shadow-sm">
                        <i class="fas fa-file-csv me-1 text-success"></i> Export CSV
                    </a>
                    <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold btn-sm shadow-sm d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#updateModal" onclick="openCreateModal()">
                        <i class="fas fa-plus-circle"></i> Add Datewise Update
                    </button>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (!empty($message)): ?>
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm mb-4" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Metric Stat Cards -->
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="stat-card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted extra-small text-uppercase fw-bold">Total Updates Logged</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary p-2 rounded-circle"><i class="fas fa-list-check"></i></span>
                        </div>
                        <h3 class="fw-bold mb-0 text-dark"><?php echo number_format($stats['total']); ?></h3>
                        <small class="text-muted extra-small">All historical entries</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted extra-small text-uppercase fw-bold">Today's Updates</span>
                            <span class="badge bg-success bg-opacity-10 text-success p-2 rounded-circle"><i class="fas fa-clock"></i></span>
                        </div>
                        <h3 class="fw-bold mb-0 text-success"><?php echo number_format($stats['today']); ?></h3>
                        <small class="text-muted extra-small"><?php echo date('d M Y'); ?></small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted extra-small text-uppercase fw-bold">Breaking / Urgent</span>
                            <span class="badge bg-danger bg-opacity-10 text-danger p-2 rounded-circle"><i class="fas fa-bolt"></i></span>
                        </div>
                        <h3 class="fw-bold mb-0 text-danger"><?php echo number_format($stats['breaking']); ?></h3>
                        <small class="text-muted extra-small">High priority alerts</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="stat-card-custom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted extra-small text-uppercase fw-bold">Pinned Bulletins</span>
                            <span class="badge bg-warning bg-opacity-10 text-warning p-2 rounded-circle"><i class="fas fa-thumbtack"></i></span>
                        </div>
                        <h3 class="fw-bold mb-0 text-warning"><?php echo number_format($stats['pinned']); ?></h3>
                        <small class="text-muted extra-small">Featured on top</small>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Toolbar -->
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white p-3 p-md-4">
                <form method="GET" action="datewise-updates.php" class="row g-3 align-items-end">
                    
                    <!-- Preset Date Buttons -->
                    <div class="col-12">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <span class="text-muted small fw-bold me-1"><i class="fas fa-filter text-primary"></i> Date Presets:</span>
                            <a href="datewise-updates.php?date_preset=all" class="btn btn-sm rounded-pill px-3 filter-btn-date <?php echo ($date_filter === 'all' && empty($date_from)) ? 'active' : 'btn-light border'; ?>">All Dates</a>
                            <a href="datewise-updates.php?date_preset=today" class="btn btn-sm rounded-pill px-3 filter-btn-date <?php echo ($date_filter === 'today') ? 'active' : 'btn-light border'; ?>">Today (आज)</a>
                            <a href="datewise-updates.php?date_preset=yesterday" class="btn btn-sm rounded-pill px-3 filter-btn-date <?php echo ($date_filter === 'yesterday') ? 'active' : 'btn-light border'; ?>">Yesterday (कल)</a>
                            <a href="datewise-updates.php?date_preset=last7" class="btn btn-sm rounded-pill px-3 filter-btn-date <?php echo ($date_filter === 'last7') ? 'active' : 'btn-light border'; ?>">Last 7 Days</a>
                            <a href="datewise-updates.php?date_preset=this_month" class="btn btn-sm rounded-pill px-3 filter-btn-date <?php echo ($date_filter === 'this_month') ? 'active' : 'btn-light border'; ?>">This Month</a>
                        </div>
                    </div>

                    <!-- Custom Date From -->
                    <div class="col-6 col-md-2">
                        <label class="form-label extra-small fw-bold text-muted mb-1">Date From</label>
                        <input type="date" name="date_from" class="form-control form-control-sm rounded-pill" value="<?php echo htmlspecialchars($date_from); ?>">
                    </div>

                    <!-- Custom Date To -->
                    <div class="col-6 col-md-2">
                        <label class="form-label extra-small fw-bold text-muted mb-1">Date To</label>
                        <input type="date" name="date_to" class="form-control form-control-sm rounded-pill" value="<?php echo htmlspecialchars($date_to); ?>">
                    </div>

                    <!-- Category Filter -->
                    <div class="col-6 col-md-3">
                        <label class="form-label extra-small fw-bold text-muted mb-1">Category</label>
                        <select name="category" class="form-select form-select-sm rounded-pill">
                            <option value="">-- All Categories --</option>
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo ($category_filter === $cat) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Priority Filter -->
                    <div class="col-6 col-md-2">
                        <label class="form-label extra-small fw-bold text-muted mb-1">Priority</label>
                        <select name="priority" class="form-select form-select-sm rounded-pill">
                            <option value="">-- All Priorities --</option>
                            <option value="breaking" <?php echo ($priority_filter === 'breaking') ? 'selected' : ''; ?>>⚡ Breaking</option>
                            <option value="important" <?php echo ($priority_filter === 'important') ? 'selected' : ''; ?>>★ Important</option>
                            <option value="normal" <?php echo ($priority_filter === 'normal') ? 'selected' : ''; ?>>Normal</option>
                        </select>
                    </div>

                    <!-- Filter Actions & Search -->
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3.5 flex-grow-1 fw-bold">
                            <i class="fas fa-magnifying-glass me-1"></i> Apply Filter
                        </button>
                        <a href="datewise-updates.php" class="btn btn-light border btn-sm rounded-pill px-3" title="Reset Filters">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>
                </form>

                <!-- Instant Search Box -->
                <div class="mt-3 pt-3 border-top">
                    <div class="position-relative">
                        <i class="fas fa-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                        <input type="text" id="liveSearchInput" class="form-control form-control-sm rounded-pill ps-5" placeholder="Instant search by title, district, scope, or description keyword...">
                    </div>
                </div>
            </div>

            <!-- View Switcher -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-bold">
                    Showing <span class="text-dark fw-bold"><?php echo count($all_updates); ?></span> updates across <?php echo count($dates_grouped); ?> active dates
                </span>
                <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group">
                    <button type="button" class="btn btn-sm rounded-pill active fw-bold px-3" id="btnViewTimeline" onclick="switchView('timeline')">
                        <i class="fas fa-timeline me-1"></i> Timeline View
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill fw-bold px-3" id="btnViewTable" onclick="switchView('table')">
                        <i class="fas fa-table-list me-1"></i> Table Grid
                    </button>
                </div>
            </div>

            <!-- 1. Timeline View Mode -->
            <div id="timelineViewContainer">
                <?php if (empty($dates_grouped)): ?>
                    <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white my-4">
                        <i class="fas fa-calendar-xmark text-muted fa-3x mb-3"></i>
                        <h5 class="fw-bold text-dark">No Datewise Updates Found</h5>
                        <p class="text-muted small mb-3">There are no updates matching your current filter criteria.</p>
                        <button class="btn btn-primary rounded-pill px-4 btn-sm mx-auto" data-bs-toggle="modal" data-bs-target="#updateModal" onclick="openCreateModal()">
                            <i class="fas fa-plus-circle me-1"></i> Add First Update
                        </button>
                    </div>
                <?php else: ?>
                    <?php foreach ($dates_grouped as $date_str => $items): 
                        $formatted_date = date('l, d F Y', strtotime($date_str));
                        $is_today = ($date_str === date('Y-m-d'));
                        $is_yesterday = ($date_str === date('Y-m-d', strtotime('-1 day')));
                        $day_label = $is_today ? 'TODAY (आज)' : ($is_yesterday ? 'YESTERDAY (कल)' : '');
                        $act = $daily_activity[$date_str] ?? ['posts' => 0, 'users' => 0, 'contacts' => 0];
                    ?>
                        <div class="date-group-block mb-4" data-date="<?php echo $date_str; ?>">
                            <!-- Sticky Date Header -->
                            <div class="timeline-date-header d-flex flex-wrap justify-content-between align-items-center gap-2 shadow-sm">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-navy text-white px-2.5 py-1.5 rounded-pill fw-bold fs-6">
                                        <i class="fas fa-calendar-day me-1"></i> <?php echo htmlspecialchars($formatted_date); ?>
                                    </span>
                                    <?php if (!empty($day_label)): ?>
                                        <span class="badge bg-danger text-white px-2 py-1 rounded-pill extra-small fw-bold">
                                            <?php echo $day_label; ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Automated Activity Summary for this Date -->
                                <div class="d-flex flex-wrap gap-2 align-items-center">
                                    <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill extra-small">
                                        <i class="fas fa-bullhorn text-primary me-1"></i> <?php echo count($items); ?> Bulletins
                                    </span>
                                    <?php if ($act['posts'] > 0): ?>
                                        <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill extra-small" title="Articles published on this date">
                                            <i class="fas fa-newspaper text-danger me-1"></i> <?php echo $act['posts']; ?> Posts
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($act['users'] > 0): ?>
                                        <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill extra-small" title="Registered Citizens on this date">
                                            <i class="fas fa-user-plus text-success me-1"></i> <?php echo $act['users']; ?> Citizens
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($act['contacts'] > 0): ?>
                                        <span class="badge bg-white text-dark border px-2.5 py-1 rounded-pill extra-small" title="Leads / Inquiries received on this date">
                                            <i class="fas fa-envelope text-info me-1"></i> <?php echo $act['contacts']; ?> Leads
                                        </span>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 extra-small fw-bold" onclick="openCreateModalForDate('<?php echo $date_str; ?>')" data-bs-toggle="modal" data-bs-target="#updateModal">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                </div>
                            </div>

                            <!-- List of update cards for this date -->
                            <div class="date-items-list">
                                <?php foreach ($items as $item): 
                                    $prio_class = ($item['priority'] === 'breaking') ? 'priority-breaking' : (($item['priority'] === 'important') ? 'priority-important' : '');
                                    $pinned_class = $item['is_pinned'] ? 'is-pinned' : '';
                                    $search_blob = mb_strtolower($item['title'] . ' ' . ($item['title_hi'] ?? '') . ' ' . $item['category'] . ' ' . ($item['target_scope'] ?? '') . ' ' . ($item['description'] ?? ''));
                                ?>
                                    <div class="update-item-card <?php echo $prio_class; ?> <?php echo $pinned_class; ?>" data-search="<?php echo htmlspecialchars($search_blob); ?>">
                                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-2 mb-2">
                                            <div>
                                                <div class="d-flex flex-wrap gap-2 align-items-center mb-1.5">
                                                    <?php if ($item['is_pinned']): ?>
                                                        <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill extra-small fw-bold">
                                                            <i class="fas fa-thumbtack me-1"></i> PINNED
                                                        </span>
                                                    <?php endif; ?>

                                                    <span class="badge-category">
                                                        <?php echo htmlspecialchars($item['category']); ?>
                                                    </span>

                                                    <?php if ($item['priority'] === 'breaking'): ?>
                                                        <span class="badge-priority-breaking">
                                                            <i class="fas fa-bolt me-1"></i> BREAKING
                                                        </span>
                                                    <?php elseif ($item['priority'] === 'important'): ?>
                                                        <span class="badge-priority-important">
                                                            <i class="fas fa-star me-1"></i> IMPORTANT
                                                        </span>
                                                    <?php endif; ?>

                                                    <?php if (!empty($item['target_scope'])): ?>
                                                        <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill extra-small">
                                                            <i class="fas fa-location-dot me-1"></i> <?php echo htmlspecialchars($item['target_scope']); ?>
                                                        </span>
                                                    <?php endif; ?>

                                                    <span class="text-muted extra-small">
                                                        <i class="far fa-clock me-1"></i> <?php echo date('h:i A', strtotime($item['update_time'])); ?>
                                                    </span>
                                                </div>

                                                <h5 class="fw-bold text-dark mb-1">
                                                    <?php echo htmlspecialchars($item['title']); ?>
                                                </h5>
                                                <?php if (!empty($item['title_hi'])): ?>
                                                    <div class="text-primary fw-semibold small mb-2" style="font-size: 0.92rem;">
                                                        <?php echo htmlspecialchars($item['title_hi']); ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <!-- Card Quick Actions -->
                                            <div class="d-flex align-items-center gap-1">
                                                <a href="datewise-updates.php?toggle_pin=<?php echo $item['id']; ?>" class="btn btn-sm btn-light border rounded-pill px-2.5 text-warning" title="<?php echo $item['is_pinned'] ? 'Unpin Update' : 'Pin to Top'; ?>">
                                                    <i class="fas fa-thumbtack"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 text-primary" title="Edit Update" onclick='openEditModal(<?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' data-bs-toggle="modal" data-bs-target="#updateModal">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </button>
                                                <a href="datewise-updates.php?delete_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-light border rounded-pill px-2.5 text-danger" title="Delete Update" onclick="return confirm('Are you sure you want to permanently delete this datewise update?');">
                                                    <i class="fas fa-trash-can"></i>
                                                </a>
                                            </div>
                                        </div>

                                        <?php if (!empty($item['description'])): ?>
                                            <p class="text-muted small mb-2.5" style="line-height: 1.55;">
                                                <?php echo nl2br(htmlspecialchars($item['description'])); ?>
                                            </p>
                                        <?php endif; ?>

                                        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 pt-2 border-top extra-small text-muted">
                                            <div>
                                                <span>Author: <strong><?php echo htmlspecialchars($item['author'] ?? 'Editorial'); ?></strong></span>
                                                <span class="mx-1.5">&bull;</span>
                                                <span>Status: <span class="badge <?php echo ($item['status'] === 'published') ? 'bg-success' : 'bg-secondary'; ?> rounded-pill" style="font-size: 0.68rem;"><?php echo ucfirst($item['status']); ?></span></span>
                                            </div>

                                            <?php if (!empty($item['url'])): ?>
                                                <a href="<?php echo (strpos($item['url'], 'http') === 0) ? htmlspecialchars($item['url']) : SITE_URL . htmlspecialchars($item['url']); ?>" target="_blank" class="text-decoration-none fw-bold text-primary d-inline-flex align-items-center gap-1">
                                                    <span>View Target Page</span>
                                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <!-- 2. Table Grid View Mode -->
            <div id="tableViewContainer" class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden d-none">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr class="extra-small text-uppercase text-muted">
                                <th style="width: 50px;">ID</th>
                                <th>Date &amp; Time</th>
                                <th>Title / Details</th>
                                <th>Category &amp; Scope</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th class="text-end" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($all_updates)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">No updates match criteria.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($all_updates as $item): 
                                    $search_blob = mb_strtolower($item['title'] . ' ' . ($item['title_hi'] ?? '') . ' ' . $item['category'] . ' ' . ($item['target_scope'] ?? ''));
                                ?>
                                    <tr class="table-row-item" data-search="<?php echo htmlspecialchars($search_blob); ?>">
                                        <td class="fw-bold text-muted small">#<?php echo $item['id']; ?></td>
                                        <td style="white-space: nowrap;">
                                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($item['update_date']); ?></div>
                                            <div class="text-muted extra-small"><?php echo date('h:i A', strtotime($item['update_time'])); ?></div>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-dark small"><?php echo htmlspecialchars($item['title']); ?></div>
                                            <?php if (!empty($item['title_hi'])): ?>
                                                <div class="text-muted extra-small"><?php echo htmlspecialchars($item['title_hi']); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($item['url'])): ?>
                                                <a href="<?php echo (strpos($item['url'], 'http') === 0) ? htmlspecialchars($item['url']) : SITE_URL . htmlspecialchars($item['url']); ?>" target="_blank" class="extra-small text-primary text-decoration-none">
                                                    <i class="fas fa-link me-1"></i><?php echo htmlspecialchars($item['url']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge-category d-inline-block mb-1"><?php echo htmlspecialchars($item['category']); ?></span>
                                            <div class="text-muted extra-small"><i class="fas fa-location-dot me-1"></i><?php echo htmlspecialchars($item['target_scope'] ?? 'Bihar'); ?></div>
                                        </td>
                                        <td>
                                            <?php if ($item['priority'] === 'breaking'): ?>
                                                <span class="badge-priority-breaking">BREAKING</span>
                                            <?php elseif ($item['priority'] === 'important'): ?>
                                                <span class="badge-priority-important">IMPORTANT</span>
                                            <?php else: ?>
                                                <span class="badge bg-light text-secondary border">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo ($item['status'] === 'published') ? 'bg-success' : 'bg-secondary'; ?> rounded-pill extra-small">
                                                <?php echo ucfirst($item['status']); ?>
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex justify-content-end gap-1">
                                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2 text-primary" onclick='openEditModal(<?php echo json_encode($item, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>)' data-bs-toggle="modal" data-bs-target="#updateModal">
                                                    <i class="fas fa-pen"></i>
                                                </button>
                                                <a href="datewise-updates.php?delete_id=<?php echo $item['id']; ?>" class="btn btn-sm btn-light border rounded-pill px-2 text-danger" onclick="return confirm('Delete this update?');">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>
</div>

<!-- Add / Edit Modal Dialog -->
<div class="modal fade" id="updateModal" tabindex="-1" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form method="POST" action="datewise-updates.php" id="updateForm">
                <input type="hidden" name="save_update" value="1">
                <input type="hidden" name="update_id" id="modal_update_id" value="0">

                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark" id="updateModalLabel">
                        <i class="fas fa-calendar-plus text-primary me-2"></i> Add Datewise Website Update
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Date & Time -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Update Date <span class="text-danger">*</span></label>
                            <input type="date" name="update_date" id="modal_update_date" class="form-control rounded-3" required value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Update Time</label>
                            <input type="time" name="update_time" id="modal_update_time" class="form-control rounded-3" value="<?php echo date('H:i'); ?>">
                        </div>

                        <!-- Title English -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Headline / Title (English) <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="modal_title" class="form-control rounded-3" placeholder="e.g. 6 Graduates & Teachers Constituency Guide Portals Released" required>
                        </div>

                        <!-- Title Hindi -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Headline / Title (Hindi) <span class="text-muted fw-normal">(वैकल्पिक)</span></label>
                            <input type="text" name="title_hi" id="modal_title_hi" class="form-control rounded-3" placeholder="e.g. 6 स्नातक एवं 6 शिक्षक निर्वाचन क्षेत्र डायरेक्टरी गाइड प्रकाशित">
                        </div>

                        <!-- Category & Target Scope -->
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Category</label>
                            <input type="text" name="category" id="modal_category" list="categoryOptions" class="form-control rounded-3" placeholder="Select or type category..." value="MLC Election 2026">
                            <datalist id="categoryOptions">
                                <?php foreach ($categories_list as $cat): ?>
                                    <option value="<?php echo htmlspecialchars($cat); ?>"></option>
                                <?php endforeach; ?>
                            </datalist>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Target Scope / Region</label>
                            <input type="text" name="target_scope" id="modal_target_scope" class="form-control rounded-3" placeholder="e.g. Bihar Statewide, Patna, Tirhut, Saran" value="Bihar Statewide">
                        </div>

                        <!-- Target URL -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Action URL / Related Link</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fas fa-link"></i></span>
                                <input type="text" name="url" id="modal_url" class="form-control rounded-end-3" placeholder="e.g. /graduates-constituency, /vidhan-parishad-election, /mlc">
                            </div>
                            <small class="text-muted extra-small">Can be a relative site path like /teachers-constituency or full URL.</small>
                        </div>

                        <!-- Priority & Status -->
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Priority Level</label>
                            <select name="priority" id="modal_priority" class="form-select rounded-3">
                                <option value="normal">Normal</option>
                                <option value="important">★ Important</option>
                                <option value="breaking">⚡ Breaking Alert</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">Status</label>
                            <select name="status" id="modal_status" class="form-select rounded-3">
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-center pt-3">
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_pinned" id="modal_is_pinned" value="1">
                                <label class="form-check-label small fw-bold text-dark" for="modal_is_pinned">
                                    <i class="fas fa-thumbtack text-warning me-1"></i> Pin on Top
                                </label>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Detailed Description / Bulletin Notes</label>
                            <textarea name="description" id="modal_description" class="form-control rounded-3" rows="4" placeholder="Enter comprehensive update summary, gazette points, or policy notes..."></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="btnSubmitModal">
                        <i class="fas fa-check me-1"></i> Save Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openCreateModal() {
    document.getElementById('updateForm').reset();
    document.getElementById('modal_update_id').value = '0';
    document.getElementById('modal_update_date').value = '<?php echo date('Y-m-d'); ?>';
    document.getElementById('modal_update_time').value = '<?php echo date('H:i'); ?>';
    document.getElementById('updateModalLabel').innerHTML = '<i class="fas fa-calendar-plus text-primary me-2"></i> Add Datewise Website Update';
    document.getElementById('btnSubmitModal').innerHTML = '<i class="fas fa-check me-1"></i> Save Update';
}

function openCreateModalForDate(dateStr) {
    openCreateModal();
    document.getElementById('modal_update_date').value = dateStr;
}

function openEditModal(item) {
    document.getElementById('modal_update_id').value = item.id;
    document.getElementById('modal_update_date').value = item.update_date;
    document.getElementById('modal_update_time').value = item.update_time ? item.update_time.substring(0, 5) : '12:00';
    document.getElementById('modal_title').value = item.title || '';
    document.getElementById('modal_title_hi').value = item.title_hi || '';
    document.getElementById('modal_category').value = item.category || 'General Update';
    document.getElementById('modal_target_scope').value = item.target_scope || 'Bihar Statewide';
    document.getElementById('modal_url').value = item.url || '';
    document.getElementById('modal_priority').value = item.priority || 'normal';
    document.getElementById('modal_status').value = item.status || 'published';
    document.getElementById('modal_is_pinned').checked = (parseInt(item.is_pinned) === 1);
    document.getElementById('modal_description').value = item.description || '';

    document.getElementById('updateModalLabel').innerHTML = `<i class="fas fa-pen-to-square text-primary me-2"></i> Edit Update #${item.id}`;
    document.getElementById('btnSubmitModal').innerHTML = '<i class="fas fa-check me-1"></i> Update Record';
}

// View Switcher (Timeline vs Table)
function switchView(viewType) {
    const timelineContainer = document.getElementById('timelineViewContainer');
    const tableContainer = document.getElementById('tableViewContainer');
    const btnTimeline = document.getElementById('btnViewTimeline');
    const btnTable = document.getElementById('btnViewTable');

    if (viewType === 'table') {
        timelineContainer.classList.add('d-none');
        tableContainer.classList.remove('d-none');
        btnTable.classList.add('active');
        btnTimeline.classList.remove('active');
    } else {
        tableContainer.classList.add('d-none');
        timelineContainer.classList.remove('d-none');
        btnTimeline.classList.add('active');
        btnTable.classList.remove('active');
    }
}

// Live Search Filter
document.getElementById('liveSearchInput')?.addEventListener('input', function() {
    const keyword = this.value.trim().toLowerCase();
    
    // Filter cards in Timeline view
    document.querySelectorAll('.update-item-card').forEach(card => {
        const text = card.getAttribute('data-search') || '';
        if (!keyword || text.includes(keyword)) {
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    });

    // Hide date groups if all children hidden
    document.querySelectorAll('.date-group-block').forEach(group => {
        const visibleCards = group.querySelectorAll('.update-item-card:not(.d-none)');
        if (visibleCards.length === 0 && keyword) {
            group.classList.add('d-none');
        } else {
            group.classList.remove('d-none');
        }
    });

    // Filter rows in Table view
    document.querySelectorAll('.table-row-item').forEach(row => {
        const text = row.getAttribute('data-search') || '';
        if (!keyword || text.includes(keyword)) {
            row.classList.remove('d-none');
        } else {
            row.classList.add('d-none');
        }
    });
});
</script>

<?php include 'admin-footer.php'; ?>
</body>
</html>
