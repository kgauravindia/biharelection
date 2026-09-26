<?php
/**
 * BiharElection Admin — Registered Users & Citizens Management Hub
 * Complete CRM & Directory for Registered Voters, Citizens, Representatives & Candidates
 */
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Load Districts for dropdowns
$districts = DataProvider::getDistricts();
if (!empty($districts) && is_array($districts)) {
    usort($districts, function ($a, $b) {
        return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
    });
}

// Handle Impersonation Session Exit message
if (isset($_GET['msg']) && $_GET['msg'] === 'impersonation_ended') {
    $message = "You have exited the citizen impersonation session and returned safely to the Admin CRM.";
}

// -------------------------------------------------------------
// 0. Handle Login As / Impersonate Citizen
// -------------------------------------------------------------
if (isset($_GET['login_as']) && $conn) {
    $target_id = (int)$_GET['login_as'];
    if ($target_id > 0) {
        $stmt_target = $conn->prepare("SELECT * FROM `users` WHERE `id` = ? LIMIT 1");
        $stmt_target->bind_param("i", $target_id);
        $stmt_target->execute();
        $res_target = $stmt_target->get_result();
        if ($res_target && $res_target->num_rows > 0) {
            $u = $res_target->fetch_assoc();
            
            // Set public user session
            $_SESSION['public_user_id']           = (int)$u['id'];
            $_SESSION['public_user_name']         = !empty($u['name']) ? $u['name'] : (!empty($u['full_name']) ? $u['full_name'] : 'Citizen');
            $_SESSION['public_user_mobile']       = $u['mobile'] ?? '';
            $_SESSION['public_user_email']        = $u['email'] ?? '';
            $_SESSION['public_user_role']         = $u['role'] ?? 'voter';
            $_SESSION['public_user_district']     = $u['district'] ?? '';
            $_SESSION['public_user_constituency'] = $u['constituency'] ?? '';
            $_SESSION['public_user_panchayat']    = $u['panchayat'] ?? '';
            $_SESSION['public_user_handle']       = $u['username_handle'] ?? '';
            $_SESSION['public_user_avatar']       = $u['profile_photo'] ?? ($u['profile_image'] ?? ($u['photo'] ?? ''));
            $_SESSION['impersonated_by_admin']   = true;
            $_SESSION['impersonator_admin_name'] = $_SESSION['admin_user'] ?? 'Administrator';

            // Direct redirect to public user dashboard
            header("Location: ../dashboard.php");
            exit();
        } else {
            $error = "Target citizen account not found.";
        }
    }
}

// -------------------------------------------------------------
// 1. Handle CSV Export
// -------------------------------------------------------------
if (isset($_GET['export_csv']) && $conn) {
    $search = sanitize($_GET['search'] ?? '');
    $filter_role = sanitize($_GET['role'] ?? '');
    $filter_district = sanitize($_GET['district'] ?? '');
    $filter_status = sanitize($_GET['status'] ?? '');
    $filter_verified = sanitize($_GET['verified'] ?? '');

    $where = ["1=1"];
    if (!empty($search)) {
        $w = "%{$search}%";
        $where[] = "(`name` LIKE '{$w}' OR `full_name` LIKE '{$w}' OR `mobile` LIKE '{$w}' OR `whatsapp` LIKE '{$w}' OR `email` LIKE '{$w}' OR `username_handle` LIKE '{$w}' OR `panchayat` LIKE '{$w}' OR `constituency` LIKE '{$w}')";
    }
    if (!empty($filter_role)) {
        $where[] = "`role` = '{$filter_role}'";
    }
    if (!empty($filter_district)) {
        $where[] = "`district` = '{$filter_district}'";
    }
    if (!empty($filter_status)) {
        $where[] = "`status` = '{$filter_status}'";
    }
    if ($filter_verified !== '') {
        $v_val = (int)$filter_verified;
        $where[] = "`is_mobile_verified` = {$v_val}";
    }
    $where_sql = implode(' AND ', $where);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bihar_citizens_users_' . date('Y-m-d_His') . '.csv');
    $output = fopen('php://output', 'w');
    
    // Output UTF-8 BOM for proper Excel rendering of Hindi/Devanagari characters
    fputs($output, "\xEF\xBB\xBF");

    fputcsv($output, [
        'User ID', 'Full Name', 'Mobile Number', 'WhatsApp', 'Email', 'Role / Category',
        'District', 'Constituency', 'Panchayat', 'Pincode', 'Profession / Designation',
        'Mobile Verified', 'Account Status', 'Username Handle', 'Bio / Notes', 'Registered On', 'Last Login'
    ]);

    $res = $conn->query("SELECT * FROM `users` WHERE {$where_sql} ORDER BY `id` DESC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            fputcsv($output, [
                $row['id'],
                $row['name'] ?: ($row['full_name'] ?: 'N/A'),
                $row['mobile'],
                $row['whatsapp'] ?: '',
                $row['email'] ?: '',
                ucfirst($row['role'] ?? 'voter'),
                $row['district'] ?: '',
                $row['constituency'] ?: '',
                $row['panchayat'] ?: '',
                $row['pincode'] ?: '',
                $row['designation'] ?: ($row['profession_category'] ?: ''),
                ($row['is_mobile_verified'] == 1) ? 'YES' : 'NO',
                $row['status'] ?: 'ACTIVE',
                $row['username_handle'] ?: '',
                $row['bio'] ?: ($row['about'] ?: ''),
                $row['created_at'] ?: '',
                $row['last_login'] ?: 'Never'
            ]);
        }
    }
    fclose($output);
    exit();
}

// -------------------------------------------------------------
// 2. Handle Add New Citizen / User
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_citizen' && $conn) {
    $name = sanitize($_POST['name'] ?? '');
    $mobile = preg_replace('/[^0-9]/', '', sanitize($_POST['mobile'] ?? ''));
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }
    $whatsapp = preg_replace('/[^0-9]/', '', sanitize($_POST['whatsapp'] ?? ''));
    if (strlen($whatsapp) === 12 && substr($whatsapp, 0, 2) === '91') {
        $whatsapp = substr($whatsapp, 2);
    }
    $email = sanitize($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? 'voter');
    $district = sanitize($_POST['district'] ?? '');
    $constituency = sanitize($_POST['constituency'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $designation = sanitize($_POST['designation'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    $status = sanitize($_POST['status'] ?? 'ACTIVE');
    $is_mobile_verified = isset($_POST['is_mobile_verified']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');

    if (empty($name) || strlen($mobile) !== 10) {
        $error = "Valid Full Name and 10-digit Mobile Number are required.";
    } else {
        // Check uniqueness of mobile
        $stmt_check = $conn->prepare("SELECT `id` FROM `users` WHERE `mobile` = ? LIMIT 1");
        $stmt_check->bind_param("s", $mobile);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check && $res_check->num_rows > 0) {
            $error = "A citizen with mobile number +91 {$mobile} is already registered.";
        } else {
            $pwd_hash = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
            $mobile_status = ($is_mobile_verified === 1) ? 'VERIFIED' : 'UNVERIFIED';

            $stmt_ins = $conn->prepare("INSERT INTO `users` (
                `name`, `full_name`, `mobile`, `whatsapp`, `email`, `password`, `password_hash`,
                `role`, `district`, `constituency`, `panchayat`, `pincode`, `designation`,
                `gender`, `address`, `bio`, `status`, `is_mobile_verified`, `mobile_status`
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

            if ($stmt_ins) {
                $stmt_ins->bind_param(
                    "sssssssssssssssssis",
                    $name, $name, $mobile, $whatsapp, $email, $pwd_hash, $pwd_hash,
                    $role, $district, $constituency, $panchayat, $pincode, $designation,
                    $gender, $address, $bio, $status, $is_mobile_verified, $mobile_status
                );
                if ($stmt_ins->execute()) {
                    $message = "Citizen '{$name}' (+91 {$mobile}) added successfully!";
                } else {
                    $error = "Database Error adding user: " . $conn->error;
                }
            }
        }
    }
}

// -------------------------------------------------------------
// 3. Handle Edit Citizen / User
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_citizen' && $conn) {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $name = sanitize($_POST['name'] ?? '');
    $mobile = preg_replace('/[^0-9]/', '', sanitize($_POST['mobile'] ?? ''));
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }
    $whatsapp = preg_replace('/[^0-9]/', '', sanitize($_POST['whatsapp'] ?? ''));
    if (strlen($whatsapp) === 12 && substr($whatsapp, 0, 2) === '91') {
        $whatsapp = substr($whatsapp, 2);
    }
    $email = sanitize($_POST['email'] ?? '');
    $role = sanitize($_POST['role'] ?? 'voter');
    $district = sanitize($_POST['district'] ?? '');
    $constituency = sanitize($_POST['constituency'] ?? '');
    $panchayat = sanitize($_POST['panchayat'] ?? '');
    $pincode = sanitize($_POST['pincode'] ?? '');
    $designation = sanitize($_POST['designation'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $address = sanitize($_POST['address'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');
    $status = sanitize($_POST['status'] ?? 'ACTIVE');
    $is_mobile_verified = isset($_POST['is_mobile_verified']) ? 1 : 0;
    $password = trim($_POST['password'] ?? '');

    if ($user_id <= 0 || empty($name) || strlen($mobile) !== 10) {
        $error = "Valid User ID, Full Name, and 10-digit Mobile Number are required.";
    } else {
        // Check duplicate mobile for another user
        $stmt_check = $conn->prepare("SELECT `id` FROM `users` WHERE `mobile` = ? AND `id` != ? LIMIT 1");
        $stmt_check->bind_param("si", $mobile, $user_id);
        $stmt_check->execute();
        $res_check = $stmt_check->get_result();

        if ($res_check && $res_check->num_rows > 0) {
            $error = "Another citizen is already registered with mobile +91 {$mobile}.";
        } else {
            $mobile_status = ($is_mobile_verified === 1) ? 'VERIFIED' : 'UNVERIFIED';

            if (!empty($password)) {
                $pwd_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt_upd = $conn->prepare("UPDATE `users` SET 
                    `name` = ?, `full_name` = ?, `mobile` = ?, `whatsapp` = ?, `email` = ?,
                    `password` = ?, `password_hash` = ?, `role` = ?, `district` = ?,
                    `constituency` = ?, `panchayat` = ?, `pincode` = ?, `designation` = ?,
                    `gender` = ?, `address` = ?, `bio` = ?, `status` = ?, `is_mobile_verified` = ?,
                    `mobile_status` = ?
                    WHERE `id` = ?");
                $stmt_upd->bind_param(
                    "sssssssssssssssssisi",
                    $name, $name, $mobile, $whatsapp, $email,
                    $pwd_hash, $pwd_hash, $role, $district,
                    $constituency, $panchayat, $pincode, $designation,
                    $gender, $address, $bio, $status, $is_mobile_verified,
                    $mobile_status, $user_id
                );
            } else {
                $stmt_upd = $conn->prepare("UPDATE `users` SET 
                    `name` = ?, `full_name` = ?, `mobile` = ?, `whatsapp` = ?, `email` = ?,
                    `role` = ?, `district` = ?, `constituency` = ?, `panchayat` = ?,
                    `pincode` = ?, `designation` = ?, `gender` = ?, `address` = ?,
                    `bio` = ?, `status` = ?, `is_mobile_verified` = ?, `mobile_status` = ?
                    WHERE `id` = ?");
                $stmt_upd->bind_param(
                    "ssssssssssssssisi",
                    $name, $name, $mobile, $whatsapp, $email,
                    $role, $district, $constituency, $panchayat,
                    $pincode, $designation, $gender, $address,
                    $bio, $status, $is_mobile_verified, $mobile_status,
                    $user_id
                );
            }

            if ($stmt_upd && $stmt_upd->execute()) {
                $message = "Citizen details for '{$name}' updated successfully.";
            } else {
                $error = "Error updating user: " . ($conn ? $conn->error : 'Database unavailable');
            }
        }
    }
}

// -------------------------------------------------------------
// 4. Quick Actions: Toggle Status, Toggle Verify, Delete
// -------------------------------------------------------------
if (isset($_GET['toggle_status']) && isset($_GET['id']) && $conn) {
    $uid = (int)$_GET['id'];
    $new_st = sanitize($_GET['toggle_status']);
    if (in_array($new_st, ['ACTIVE', 'INACTIVE', 'SUSPENDED', 'PENDING'])) {
        $stmt_tg = $conn->prepare("UPDATE `users` SET `status` = ? WHERE `id` = ?");
        $stmt_tg->bind_param("si", $new_st, $uid);
        $stmt_tg->execute();
        $message = "User status updated to '{$new_st}'.";
    }
}

if (isset($_GET['toggle_verify']) && isset($_GET['id']) && $conn) {
    $uid = (int)$_GET['id'];
    $v_flag = ((int)$_GET['toggle_verify'] === 1) ? 1 : 0;
    $m_stat = ($v_flag === 1) ? 'VERIFIED' : 'UNVERIFIED';
    $stmt_vf = $conn->prepare("UPDATE `users` SET `is_mobile_verified` = ?, `mobile_status` = ? WHERE `id` = ?");
    $stmt_vf->bind_param("isi", $v_flag, $m_stat, $uid);
    $stmt_vf->execute();
    $message = "Mobile verification status updated to " . ($v_flag ? "Verified" : "Unverified") . ".";
}

if (isset($_GET['delete_id']) && $conn) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 0) {
        $stmt_del = $conn->prepare("DELETE FROM `users` WHERE `id` = ?");
        $stmt_del->bind_param("i", $del_id);
        if ($stmt_del->execute()) {
            $message = "Citizen record #{$del_id} deleted permanently.";
        } else {
            $error = "Failed to delete user: " . $conn->error;
        }
    }
}

// -------------------------------------------------------------
// 5. Query Filters & Pagination
// -------------------------------------------------------------
$search = sanitize($_GET['search'] ?? '');
$filter_role = sanitize($_GET['role'] ?? '');
$filter_district = sanitize($_GET['district'] ?? '');
$filter_status = sanitize($_GET['status'] ?? '');
$filter_verified = sanitize($_GET['verified'] ?? '');
$sort_by = sanitize($_GET['sort'] ?? 'newest');

$where = ["1=1"];
if (!empty($search)) {
    $w = "%{$search}%";
    $where[] = "(`name` LIKE '{$w}' OR `full_name` LIKE '{$w}' OR `mobile` LIKE '{$w}' OR `whatsapp` LIKE '{$w}' OR `email` LIKE '{$w}' OR `username_handle` LIKE '{$w}' OR `panchayat` LIKE '{$w}' OR `constituency` LIKE '{$w}')";
}
if (!empty($filter_role)) {
    $where[] = "`role` = '{$filter_role}'";
}
if (!empty($filter_district)) {
    $where[] = "`district` = '{$filter_district}'";
}
if (!empty($filter_status)) {
    $where[] = "`status` = '{$filter_status}'";
}
if ($filter_verified !== '') {
    $v_val = (int)$filter_verified;
    $where[] = "`is_mobile_verified` = {$v_val}";
}
$where_sql = implode(' AND ', $where);

// Sorting
$order_sql = "ORDER BY `id` DESC";
if ($sort_by === 'oldest') $order_sql = "ORDER BY `id` ASC";
elseif ($sort_by === 'name_asc') $order_sql = "ORDER BY `name` ASC, `full_name` ASC";
elseif ($sort_by === 'name_desc') $order_sql = "ORDER BY `name` DESC, `full_name` DESC";
elseif ($sort_by === 'last_login') $order_sql = "ORDER BY `last_login` DESC";

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = max(10, min(100, (int)($_GET['limit'] ?? 25)));
$offset = ($page - 1) * $per_page;

$total_records = 0;
$users = [];

// Overall Statistics
$stats = [
    'total' => 0,
    'verified' => 0,
    'today' => 0,
    'voters' => 0,
    'reps' => 0
];

if ($conn) {
    // Total matching filter
    $c_res = $conn->query("SELECT COUNT(*) as c FROM `users` WHERE {$where_sql}");
    if ($c_res) $total_records = (int)$c_res->fetch_assoc()['c'];

    // Overall KPI Stats
    $s_res = $conn->query("SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN `is_mobile_verified` = 1 THEN 1 ELSE 0 END) as verified_count,
        SUM(CASE WHEN DATE(`created_at`) = CURDATE() THEN 1 ELSE 0 END) as today_count,
        SUM(CASE WHEN `role` = 'voter' OR `role` IS NULL OR `role` = '' THEN 1 ELSE 0 END) as voter_count,
        SUM(CASE WHEN `role` IN ('mukhiya', 'sarpanch', 'candidate', 'representative', 'panchayat_samiti', 'zila_parishad') THEN 1 ELSE 0 END) as rep_count
        FROM `users`");
    if ($s_res) {
        $s_row = $s_res->fetch_assoc();
        $stats['total'] = (int)($s_row['total_users'] ?? 0);
        $stats['verified'] = (int)($s_row['verified_count'] ?? 0);
        $stats['today'] = (int)($s_row['today_count'] ?? 0);
        $stats['voters'] = (int)($s_row['voter_count'] ?? 0);
        $stats['reps'] = (int)($s_row['rep_count'] ?? 0);
    }

    // Fetch Paginated Citizens
    $q_users = $conn->query("SELECT * FROM `users` WHERE {$where_sql} {$order_sql} LIMIT {$offset}, {$per_page}");
    if ($q_users) {
        while ($row = $q_users->fetch_assoc()) {
            $users[] = $row;
        }
    }
}

$total_pages = ceil($total_records / $per_page);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registered Citizens & Voter Network — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .citizen-avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.95rem;
            color: #fff;
            background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);
            box-shadow: 0 2px 6px rgba(0,0,0,0.12);
            flex-shrink: 0;
            object-fit: cover;
        }
        .stat-card-kpi {
            background: #fff;
            border-radius: 1rem;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            transition: all 0.25s ease;
        }
        .stat-card-kpi:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .filter-panel {
            background: #fff;
            border-radius: 1rem;
            border: 1px solid rgba(0,0,0,0.06);
            padding: 1.25rem;
            box-shadow: 0 4px 12px rgba(0,0,0,0.02);
        }
        .badge-soft-primary { background: #e0f2fe; color: #0284c7; }
        .badge-soft-success { background: #dcfce7; color: #16a34a; }
        .badge-soft-warning { background: #fef3c7; color: #d97706; }
        .badge-soft-danger { background: #fee2e2; color: #dc2626; }
        .badge-soft-secondary { background: #f1f5f9; color: #475569; }
        .badge-soft-purple { background: #f3e8ff; color: #7e22ce; }
        .table th {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 0.9rem 0.75rem;
        }
        .table td {
            padding: 0.9rem 0.75rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .action-btn-circle {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            transition: all 0.2s;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <!-- Header & Action Bar -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
            <div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-danger text-white px-2.5 py-1.5 rounded-pill small fw-bold">Live Community CRM</span>
                    <span class="text-muted small">Bihar Public Registry</span>
                </div>
                <h1 class="h3 fw-bold mb-1 mt-1" style="font-family: 'Outfit', sans-serif;">Registered Citizens & Voters</h1>
                <p class="text-muted mb-0">Browse, filter, verify, and manage all registered Bihar voters, political candidates & representatives.</p>
            </div>
            
            <div class="d-flex flex-wrap gap-2">
                <a href="citizens.php?export_csv=1&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($filter_role); ?>&district=<?php echo urlencode($filter_district); ?>&status=<?php echo urlencode($filter_status); ?>&verified=<?php echo urlencode($filter_verified); ?>" class="btn btn-outline-success fw-semibold px-3 py-2 rounded-3 shadow-sm bg-white">
                    <i class="fas fa-file-excel me-1 text-success"></i> Export CSV
                </a>
                <button class="btn btn-primary fw-semibold px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addCitizenModal">
                    <i class="fas fa-user-plus me-1"></i> Add Citizen
                </button>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-check-circle fs-5 text-success"></i>
                <div><?php echo htmlspecialchars($message); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2" role="alert">
                <i class="fas fa-exclamation-triangle fs-5 text-danger"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- KPI Summary Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="stat-card-kpi d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 fs-4">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Total Citizens</div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo number_format($stats['total']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-kpi d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-3 p-3 fs-4">
                        <i class="fas fa-mobile-screen-button"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Verified Mobile</div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo number_format($stats['verified']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-kpi d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning rounded-3 p-3 fs-4">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Voters & Public</div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo number_format($stats['voters']); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="stat-card-kpi d-flex align-items-center gap-3">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-3 fs-4">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold text-uppercase">Candidates & Reps</div>
                        <div class="h3 fw-bold mb-0 text-dark"><?php echo number_format($stats['reps']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="filter-panel mb-4">
            <form method="GET" action="citizens.php" class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Name, mobile, email, area..." value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                </div>
                
                <div class="col-6 col-md-2">
                    <select name="district" class="form-select text-truncate">
                        <option value="">All 38 Districts</option>
                        <?php if (!empty($districts)): ?>
                            <?php foreach ($districts as $d): ?>
                                <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($filter_district === $d['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($d['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="role" class="form-select">
                        <option value="">All Roles</option>
                        <option value="voter" <?php echo ($filter_role === 'voter') ? 'selected' : ''; ?>>Voter / Citizen</option>
                        <option value="mukhiya" <?php echo ($filter_role === 'mukhiya') ? 'selected' : ''; ?>>Mukhiya / Sarpanch</option>
                        <option value="candidate" <?php echo ($filter_role === 'candidate') ? 'selected' : ''; ?>>Candidate / Aspirant</option>
                        <option value="representative" <?php echo ($filter_role === 'representative') ? 'selected' : ''; ?>>Elected Representative</option>
                        <option value="analyst" <?php echo ($filter_role === 'analyst') ? 'selected' : ''; ?>>Political Analyst</option>
                        <option value="journalist" <?php echo ($filter_role === 'journalist') ? 'selected' : ''; ?>>Journalist / Media</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="verified" class="form-select">
                        <option value="">Mobile Status</option>
                        <option value="1" <?php echo ($filter_verified === '1') ? 'selected' : ''; ?>>Verified Only</option>
                        <option value="0" <?php echo ($filter_verified === '0') ? 'selected' : ''; ?>>Unverified Only</option>
                    </select>
                </div>

                <div class="col-6 col-md-1">
                    <select name="status" class="form-select">
                        <option value="">Status</option>
                        <option value="ACTIVE" <?php echo ($filter_status === 'ACTIVE') ? 'selected' : ''; ?>>Active</option>
                        <option value="INACTIVE" <?php echo ($filter_status === 'INACTIVE') ? 'selected' : ''; ?>>Inactive</option>
                        <option value="SUSPENDED" <?php echo ($filter_status === 'SUSPENDED') ? 'selected' : ''; ?>>Suspended</option>
                    </select>
                </div>

                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100 fw-semibold">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <?php if (!empty($search) || !empty($filter_role) || !empty($filter_district) || !empty($filter_status) || $filter_verified !== ''): ?>
                        <a href="citizens.php" class="btn btn-light border text-muted px-3" title="Clear Filters">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Citizens Table Card -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-address-book me-2 text-primary"></i> Citizen Database Records</h6>
                    <span class="badge bg-light text-dark border px-2.5 py-1">Showing <?php echo number_format(count($users)); ?> of <?php echo number_format($total_records); ?></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <label class="text-muted small me-1 d-none d-sm-inline">Sort:</label>
                    <select class="form-select form-select-sm w-auto" onchange="location.href=this.value;">
                        <option value="citizens.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'newest'])); ?>" <?php echo ($sort_by === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="citizens.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'oldest'])); ?>" <?php echo ($sort_by === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="citizens.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'name_asc'])); ?>" <?php echo ($sort_by === 'name_asc') ? 'selected' : ''; ?>>Name (A-Z)</option>
                        <option value="citizens.php?<?php echo http_build_query(array_merge($_GET, ['sort' => 'last_login'])); ?>" <?php echo ($sort_by === 'last_login') ? 'selected' : ''; ?>>Recent Login</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover">
                    <thead>
                        <tr>
                            <th>Citizen Details</th>
                            <th>Contact Info</th>
                            <th>Location / Constituency</th>
                            <th>Role & Designation</th>
                            <th>Verification & Status</th>
                            <th>Registration</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <?php 
                                    $displayName = !empty($u['name']) ? $u['name'] : (!empty($u['full_name']) ? $u['full_name'] : 'Unnamed Citizen');
                                    $initials = strtoupper(substr($displayName, 0, 1));
                                    $role = strtolower($u['role'] ?? 'voter');
                                    $roleBadgeClass = 'badge-soft-secondary';
                                    if ($role === 'voter') $roleBadgeClass = 'badge-soft-primary';
                                    elseif (in_array($role, ['mukhiya', 'sarpanch', 'panchayat_samiti', 'zila_parishad'])) $roleBadgeClass = 'badge-soft-success';
                                    elseif ($role === 'candidate') $roleBadgeClass = 'badge-soft-warning';
                                    elseif (in_array($role, ['analyst', 'journalist'])) $roleBadgeClass = 'badge-soft-purple';

                                    $statusBadgeClass = 'badge-soft-success';
                                    if ($u['status'] === 'INACTIVE') $statusBadgeClass = 'badge-soft-warning';
                                    elseif ($u['status'] === 'SUSPENDED') $statusBadgeClass = 'badge-soft-danger';

                                    $cleanPhone = preg_replace('/[^0-9]/', '', $u['mobile'] ?? '');
                                    $cleanWa = !empty($u['whatsapp']) ? preg_replace('/[^0-9]/', '', $u['whatsapp']) : $cleanPhone;
                                ?>
                                <tr>
                                    <!-- Citizen Details -->
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <?php if (!empty($u['profile_photo']) || !empty($u['photo']) || !empty($u['profile_image'])): ?>
                                                <?php $pimg = $u['profile_photo'] ?: ($u['photo'] ?: $u['profile_image']); ?>
                                                <img src="<?php echo htmlspecialchars($pimg); ?>" alt="Avatar" class="citizen-avatar" onerror="this.outerHTML='<div class=\'citizen-avatar\'><?php echo $initials; ?></div>'">
                                            <?php else: ?>
                                                <div class="citizen-avatar">
                                                    <?php echo $initials; ?>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="fw-bold text-dark mb-0">
                                                    <a href="javascript:void(0)" class="text-decoration-none text-dark hover-primary" onclick="viewCitizen(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                        <?php echo htmlspecialchars($displayName); ?>
                                                    </a>
                                                    <?php if (!empty($u['gender'])): ?>
                                                        <span class="text-muted small fw-normal ms-1">(<?php echo htmlspecialchars($u['gender']); ?>)</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="small text-muted">
                                                    ID: #<?php echo $u['id']; ?>
                                                    <?php if (!empty($u['username_handle'])): ?>
                                                        • <span class="text-primary">@<?php echo htmlspecialchars($u['username_handle']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Contact Info -->
                                    <td>
                                        <div>
                                            <a href="tel:+91<?php echo $cleanPhone; ?>" class="fw-semibold text-decoration-none text-dark font-monospace">
                                                <i class="fas fa-phone-alt text-muted me-1 small"></i>+91 <?php echo htmlspecialchars($u['mobile']); ?>
                                            </a>
                                        </div>
                                        <?php if (!empty($u['email'])): ?>
                                            <div class="small text-muted text-truncate" style="max-width: 170px;">
                                                <a href="mailto:<?php echo htmlspecialchars($u['email']); ?>" class="text-muted text-decoration-none">
                                                    <i class="fas fa-envelope me-1 small"></i><?php echo htmlspecialchars($u['email']); ?>
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                        <div class="mt-1 d-flex gap-1">
                                            <a href="https://wa.me/91<?php echo $cleanWa; ?>?text=Hello%20<?php echo urlencode($displayName); ?>%20from%20BiharElection.com" target="_blank" class="badge bg-success text-white text-decoration-none" title="Open WhatsApp Chat">
                                                <i class="fab fa-whatsapp me-1"></i>Chat
                                            </a>
                                            <a href="tel:+91<?php echo $cleanPhone; ?>" class="badge bg-light text-dark border text-decoration-none" title="Call Citizen">
                                                <i class="fas fa-phone me-1"></i>Call
                                            </a>
                                        </div>
                                    </td>

                                    <!-- Location / Area -->
                                    <td>
                                        <div class="fw-semibold text-dark">
                                            <i class="fas fa-location-dot text-danger me-1 small"></i><?php echo htmlspecialchars($u['district'] ?: 'Bihar (Statewide)'); ?>
                                        </div>
                                        <?php if (!empty($u['constituency'])): ?>
                                            <div class="small text-muted"><i class="fas fa-landmark me-1 small"></i><?php echo htmlspecialchars($u['constituency']); ?></div>
                                        <?php endif; ?>
                                        <?php if (!empty($u['panchayat'])): ?>
                                            <div class="small text-muted"><i class="fas fa-house-chimney me-1 small"></i><?php echo htmlspecialchars($u['panchayat']); ?></div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Role & Profession -->
                                    <td>
                                        <span class="badge <?php echo $roleBadgeClass; ?> px-2.5 py-1.5 rounded-pill mb-1">
                                            <?php echo strtoupper(htmlspecialchars($role)); ?>
                                        </span>
                                        <?php if (!empty($u['designation']) || !empty($u['profession_category'])): ?>
                                            <div class="small text-dark fw-medium text-truncate" style="max-width: 160px;">
                                                <?php echo htmlspecialchars($u['designation'] ?: $u['profession_category']); ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>

                                    <!-- Verification & Status -->
                                    <td>
                                        <div class="mb-1">
                                            <?php if ((int)($u['is_mobile_verified'] ?? 0) === 1): ?>
                                                <span class="badge badge-soft-success" title="Verified via Mobile OTP">
                                                    <i class="fas fa-shield-check me-1"></i>Verified
                                                </span>
                                            <?php else: ?>
                                                <a href="citizens.php?toggle_verify=1&id=<?php echo $u['id']; ?>" class="badge badge-soft-danger text-decoration-none" title="Click to verify this citizen">
                                                    <i class="fas fa-shield-halved me-1"></i>Unverified
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <span class="badge <?php echo $statusBadgeClass; ?>">
                                                <?php echo htmlspecialchars($u['status'] ?? 'ACTIVE'); ?>
                                            </span>
                                        </div>
                                    </td>

                                    <!-- Registration & Activity -->
                                    <td>
                                        <small class="d-block text-dark fw-medium">
                                            <?php echo !empty($u['created_at']) ? date('d M Y', strtotime($u['created_at'])) : '—'; ?>
                                        </small>
                                        <small class="text-muted" title="Last Active / Login">
                                            <?php echo !empty($u['last_login']) ? 'Active: ' . date('d M, h:i A', strtotime($u['last_login'])) : 'No login yet'; ?>
                                        </small>
                                    </td>

                                    <!-- Actions -->
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <a href="citizens.php?login_as=<?php echo $u['id']; ?>" class="btn btn-sm btn-light border text-success action-btn-circle" title="Login As <?php echo htmlspecialchars($displayName); ?> (Admin Impersonate)" target="_blank">
                                                <i class="fas fa-right-to-bracket"></i>
                                            </a>
                                            <button class="btn btn-sm btn-light border text-primary action-btn-circle" title="View Full Citizen Profile" onclick="viewCitizen(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <button class="btn btn-sm btn-light border text-dark action-btn-circle" title="Edit Citizen" onclick="editCitizen(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                <i class="fas fa-pen-to-square"></i>
                                            </button>
                                            
                                            <!-- Dropdown for more actions -->
                                            <div class="dropdown d-inline-block">
                                                <button class="btn btn-sm btn-light border action-btn-circle text-muted" data-bs-toggle="dropdown" aria-expanded="false">
                                                    <i class="fas fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 small">
                                                    <li>
                                                        <a class="dropdown-item text-success fw-semibold" href="citizens.php?login_as=<?php echo $u['id']; ?>" target="_blank">
                                                            <i class="fas fa-right-to-bracket text-success me-2"></i> Login As This Citizen
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)" onclick="viewCitizen(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                            <i class="fas fa-id-card text-primary me-2"></i> View Profile
                                                        </a>
                                                    </li>
                                                    <li>
                                                        <a class="dropdown-item" href="javascript:void(0)" onclick="editCitizen(<?php echo htmlspecialchars(json_encode($u)); ?>)">
                                                            <i class="fas fa-edit text-warning me-2"></i> Edit Information
                                                        </a>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <?php if ((int)$u['is_mobile_verified'] === 1): ?>
                                                        <li>
                                                            <a class="dropdown-item text-secondary" href="citizens.php?toggle_verify=0&id=<?php echo $u['id']; ?>">
                                                                <i class="fas fa-ban me-2"></i> Mark Unverified
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="citizens.php?toggle_verify=1&id=<?php echo $u['id']; ?>">
                                                                <i class="fas fa-check-circle me-2"></i> Mark Mobile Verified
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>

                                                    <?php if ($u['status'] === 'ACTIVE'): ?>
                                                        <li>
                                                            <a class="dropdown-item text-warning" href="citizens.php?toggle_status=SUSPENDED&id=<?php echo $u['id']; ?>">
                                                                <i class="fas fa-pause-circle me-2"></i> Suspend Account
                                                            </a>
                                                        </li>
                                                    <?php else: ?>
                                                        <li>
                                                            <a class="dropdown-item text-success" href="citizens.php?toggle_status=ACTIVE&id=<?php echo $u['id']; ?>">
                                                                <i class="fas fa-play-circle me-2"></i> Activate Account
                                                            </a>
                                                        </li>
                                                    <?php endif; ?>

                                                    <li><hr class="dropdown-divider"></li>
                                                    <li>
                                                        <a class="dropdown-item text-danger" href="citizens.php?delete_id=<?php echo $u['id']; ?>" onclick="return confirm('Are you sure you want to permanently delete citizen <?php echo addslashes($displayName); ?>?');">
                                                            <i class="fas fa-trash-alt me-2"></i> Delete Citizen
                                                        </a>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <div class="mb-3"><i class="fas fa-users-slash fs-1 text-muted opacity-50"></i></div>
                                    <h6 class="fw-bold">No registered citizens or voters found.</h6>
                                    <p class="small text-muted mb-3">Try adjusting your search criteria or register a new citizen profile.</p>
                                    <button class="btn btn-primary btn-sm fw-semibold rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#addCitizenModal">
                                        <i class="fas fa-plus me-1"></i> Add First Citizen
                                    </button>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="card-footer bg-white border-top py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="small text-muted">
                        Page <strong class="text-dark"><?php echo $page; ?></strong> of <strong class="text-dark"><?php echo $total_pages; ?></strong> (Total <?php echo number_format($total_records); ?> records)
                    </div>
                    <nav aria-label="Page navigation">
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="citizens.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page - 1])); ?>"><i class="fas fa-chevron-left"></i></a>
                            </li>
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            for ($p = $start_page; $p <= $end_page; $p++):
                            ?>
                                <li class="page-item <?php echo ($p == $page) ? 'active' : ''; ?>">
                                    <a class="page-link" href="citizens.php?<?php echo http_build_query(array_merge($_GET, ['page' => $p])); ?>"><?php echo $p; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="citizens.php?<?php echo http_build_query(array_merge($_GET, ['page' => $page + 1])); ?>"><i class="fas fa-chevron-right"></i></a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

        <!-- ============================================================== -->
        <!-- VIEW CITIZEN MODAL -->
        <!-- ============================================================== -->
        <div class="modal fade" id="viewCitizenModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                    <div class="modal-header text-white p-4" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
                        <div class="d-flex align-items-center gap-3">
                            <div id="v_avatar" class="citizen-avatar fs-4" style="width: 54px; height: 54px; background: rgba(255,255,255,0.2);"></div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0 text-white" id="v_name">Citizen Profile</h5>
                                <div class="small text-white-50 mt-0.5">
                                    <span id="v_role_badge" class="badge bg-warning text-dark me-1">VOTER</span>
                                    <span id="v_verified_badge" class="badge bg-success">VERIFIED</span>
                                    <span class="ms-1" id="v_id">ID: #0</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body p-4">
                        <!-- Quick Action Contact Strip -->
                        <div class="d-flex flex-wrap gap-2 mb-4 p-3 bg-light rounded-3 align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-3">
                                <div>
                                    <div class="text-muted small">Registered Mobile</div>
                                    <div class="fw-bold font-monospace" id="v_mobile">+91 —</div>
                                </div>
                                <div class="vr"></div>
                                <div>
                                    <div class="text-muted small">Email</div>
                                    <div class="fw-bold" id="v_email">—</div>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="#" id="v_wa_link" target="_blank" class="btn btn-success btn-sm fw-semibold">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp
                                </a>
                                <a href="#" id="v_call_link" class="btn btn-primary btn-sm fw-semibold">
                                    <i class="fas fa-phone me-1"></i> Direct Call
                                </a>
                            </div>
                        </div>

                        <div class="row g-4">
                            <!-- Location Details -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                    <i class="fas fa-map-location-dot text-danger me-2"></i> Territorial / Voting Location
                                </h6>
                                <table class="table table-sm table-borderless small mb-0">
                                    <tr>
                                        <td class="text-muted fw-semibold" style="width: 38%;">District:</td>
                                        <td class="fw-bold text-dark" id="v_district">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Constituency:</td>
                                        <td class="fw-bold text-dark" id="v_constituency">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Gram Panchayat:</td>
                                        <td class="text-dark" id="v_panchayat">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">PIN Code:</td>
                                        <td class="text-dark font-monospace" id="v_pincode">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Full Address:</td>
                                        <td class="text-dark" id="v_address">—</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Professional & Background -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                    <i class="fas fa-briefcase text-primary me-2"></i> Professional & Personal Details
                                </h6>
                                <table class="table table-sm table-borderless small mb-0">
                                    <tr>
                                        <td class="text-muted fw-semibold" style="width: 38%;">Designation:</td>
                                        <td class="fw-bold text-dark" id="v_designation">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Gender:</td>
                                        <td class="text-dark" id="v_gender">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Status:</td>
                                        <td class="text-dark" id="v_status">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Registered On:</td>
                                        <td class="text-dark" id="v_created">—</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted fw-semibold">Last Login:</td>
                                        <td class="text-dark" id="v_last_login">—</td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Bio & About -->
                            <div class="col-12" id="v_bio_wrapper">
                                <h6 class="fw-bold text-dark mb-2 border-bottom pb-2">
                                    <i class="fas fa-quote-left text-warning me-2"></i> Citizen Bio / Aspirations
                                </h6>
                                <p class="small text-muted bg-light p-3 rounded-3 mb-0" id="v_bio">No bio or background statement recorded.</p>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-light p-3 d-flex justify-content-between align-items-center">
                        <div>
                            <a href="#" id="v_login_as_btn" target="_blank" class="btn btn-success btn-sm fw-bold px-3">
                                <i class="fas fa-right-to-bracket me-1"></i> Login As This Citizen
                            </a>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary btn-sm fw-semibold" id="v_btn_edit">
                                <i class="fas fa-pen me-1"></i> Edit This Profile
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- ADD CITIZEN MODAL -->
        <!-- ============================================================== -->
        <div class="modal fade" id="addCitizenModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header bg-primary text-white rounded-top-4 p-3 px-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Add Citizen / Voter Profile</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="citizens.php">
                        <input type="hidden" name="action" value="add_citizen">
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" placeholder="e.g. Ramesh Kumar" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number (10 Digits) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">+91</span>
                                        <input type="tel" name="mobile" class="form-control" placeholder="9876543210" pattern="[0-9]{10}" maxlength="10" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">WhatsApp Number (Optional)</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">+91</span>
                                        <input type="tel" name="whatsapp" class="form-control" placeholder="9876543210" pattern="[0-9]{10}" maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Email Address (Optional)</label>
                                    <input type="email" name="email" class="form-control" placeholder="ramesh@example.com">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Role / Citizen Type</label>
                                    <select name="role" class="form-select">
                                        <option value="voter" selected>Voter / Citizen</option>
                                        <option value="mukhiya">Panchayat Mukhiya / Sarpanch</option>
                                        <option value="candidate">Political Candidate / Aspirant</option>
                                        <option value="representative">Elected Representative</option>
                                        <option value="analyst">Political Analyst</option>
                                        <option value="journalist">Journalist / Press</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">District</label>
                                    <select name="district" class="form-select">
                                        <option value="">Select District</option>
                                        <?php if (!empty($districts)): ?>
                                            <?php foreach ($districts as $d): ?>
                                                <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Constituency / Area</label>
                                    <input type="text" name="constituency" class="form-control" placeholder="e.g. Patna Sahib">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Panchayat / Block</label>
                                    <input type="text" name="panchayat" class="form-control" placeholder="e.g. Rampur">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Designation / Profession</label>
                                    <input type="text" name="designation" class="form-control" placeholder="e.g. Social Worker / Teacher">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">PIN Code</label>
                                    <input type="text" name="pincode" class="form-control" placeholder="800001" maxlength="6">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Account Password (Optional)</label>
                                    <input type="password" name="password" class="form-control" placeholder="Set initial password">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Address</label>
                                    <textarea name="address" class="form-control" rows="2" placeholder="Full residential / office address"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Bio / Notes</label>
                                    <textarea name="bio" class="form-control" rows="2" placeholder="Key background or political notes"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_mobile_verified" id="add_verified" value="1" checked>
                                        <label class="form-check-label fw-semibold small" for="add_verified">Mark Mobile as Verified</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                    <select name="status" class="form-select">
                                        <option value="ACTIVE" selected>ACTIVE</option>
                                        <option value="INACTIVE">INACTIVE</option>
                                        <option value="SUSPENDED">SUSPENDED</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary fw-bold px-4">Register Citizen</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================== -->
        <!-- EDIT CITIZEN MODAL -->
        <!-- ============================================================== -->
        <div class="modal fade" id="editCitizenModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white rounded-top-4 p-3 px-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-pen me-2 text-warning"></i> Edit Citizen Profile</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="citizens.php">
                        <input type="hidden" name="action" value="edit_citizen">
                        <input type="hidden" name="user_id" id="e_user_id" value="">
                        
                        <div class="modal-body p-4">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" id="e_name" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number (10 Digits) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">+91</span>
                                        <input type="tel" name="mobile" id="e_mobile" class="form-control" pattern="[0-9]{10}" maxlength="10" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">WhatsApp Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted">+91</span>
                                        <input type="tel" name="whatsapp" id="e_whatsapp" class="form-control" pattern="[0-9]{10}" maxlength="10">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                                    <input type="email" name="email" id="e_email" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Role / Citizen Type</label>
                                    <select name="role" id="e_role" class="form-select">
                                        <option value="voter">Voter / Citizen</option>
                                        <option value="mukhiya">Panchayat Mukhiya / Sarpanch</option>
                                        <option value="candidate">Political Candidate / Aspirant</option>
                                        <option value="representative">Elected Representative</option>
                                        <option value="analyst">Political Analyst</option>
                                        <option value="journalist">Journalist / Press</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">District</label>
                                    <select name="district" id="e_district" class="form-select">
                                        <option value="">Select District</option>
                                        <?php if (!empty($districts)): ?>
                                            <?php foreach ($districts as $d): ?>
                                                <option value="<?php echo htmlspecialchars($d['name']); ?>"><?php echo htmlspecialchars($d['name']); ?></option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Constituency / Area</label>
                                    <input type="text" name="constituency" id="e_constituency" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Panchayat / Block</label>
                                    <input type="text" name="panchayat" id="e_panchayat" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Designation / Profession</label>
                                    <input type="text" name="designation" id="e_designation" class="form-control">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Gender</label>
                                    <select name="gender" id="e_gender" class="form-select">
                                        <option value="">Select</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                        <option value="Other">Other</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">PIN Code</label>
                                    <input type="text" name="pincode" id="e_pincode" class="form-control" maxlength="6">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Reset Password <span class="text-muted small">(Leave empty to keep current)</span></label>
                                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Address</label>
                                    <textarea name="address" id="e_address" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Bio / Notes</label>
                                    <textarea name="bio" id="e_bio" class="form-control" rows="2"></textarea>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_mobile_verified" id="e_is_mobile_verified" value="1">
                                        <label class="form-check-label fw-semibold small" for="e_is_mobile_verified">Mobile Verified Status</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Status</label>
                                    <select name="status" id="e_status" class="form-select">
                                        <option value="ACTIVE">ACTIVE</option>
                                        <option value="INACTIVE">INACTIVE</option>
                                        <option value="SUSPENDED">SUSPENDED</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-dark fw-bold px-4">Update Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<script>
let currentCitizen = null;

function viewCitizen(data) {
    currentCitizen = data;
    const name = data.name || data.full_name || 'Unnamed Citizen';
    const initials = name.charAt(0).toUpperCase();
    
    document.getElementById('v_id').innerText = 'ID: #' + data.id;
    document.getElementById('v_name').innerText = name;
    document.getElementById('v_mobile').innerText = data.mobile ? '+91 ' + data.mobile : '—';
    document.getElementById('v_email').innerText = data.email || 'None registered';
    document.getElementById('v_district').innerText = data.district || 'Bihar (Statewide)';
    document.getElementById('v_constituency').innerText = data.constituency || '—';
    document.getElementById('v_panchayat').innerText = data.panchayat || '—';
    document.getElementById('v_pincode').innerText = data.pincode || '—';
    document.getElementById('v_address').innerText = data.address || '—';
    document.getElementById('v_designation').innerText = data.designation || data.profession_category || 'General Citizen / Voter';
    document.getElementById('v_gender').innerText = data.gender || 'Not specified';
    document.getElementById('v_status').innerText = data.status || 'ACTIVE';
    document.getElementById('v_created').innerText = data.created_at ? new Date(data.created_at).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) : '—';
    document.getElementById('v_last_login').innerText = data.last_login ? new Date(data.last_login).toLocaleString('en-GB') : 'Never';
    document.getElementById('v_bio').innerText = data.bio || data.about || 'No bio or background statement recorded.';
    
    // Avatar
    const avatarEl = document.getElementById('v_avatar');
    if (data.profile_photo || data.photo || data.profile_image) {
        const imgUrl = data.profile_photo || data.photo || data.profile_image;
        avatarEl.innerHTML = `<img src="${imgUrl}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;" onerror="this.outerHTML='${initials}'">`;
    } else {
        avatarEl.innerText = initials;
    }

    // Badges
    const roleBadge = document.getElementById('v_role_badge');
    roleBadge.innerText = (data.role || 'VOTER').toUpperCase();

    const verBadge = document.getElementById('v_verified_badge');
    if (parseInt(data.is_mobile_verified) === 1) {
        verBadge.className = 'badge bg-success';
        verBadge.innerText = 'MOBILE VERIFIED';
    } else {
        verBadge.className = 'badge bg-danger';
        verBadge.innerText = 'UNVERIFIED';
    }

    // Direct Links
    const cleanPhone = (data.mobile || '').replace(/[^0-9]/g, '');
    const cleanWa = (data.whatsapp || data.mobile || '').replace(/[^0-9]/g, '');
    document.getElementById('v_call_link').href = 'tel:+91' + cleanPhone;
    document.getElementById('v_wa_link').href = `https://wa.me/91${cleanWa}?text=Hello%20${encodeURIComponent(name)}%20from%20BiharElection.com`;
    document.getElementById('v_login_as_btn').href = 'citizens.php?login_as=' + data.id;

    document.getElementById('v_btn_edit').onclick = function() {
        const viewModalEl = document.getElementById('viewCitizenModal');
        const viewModal = bootstrap.Modal.getInstance(viewModalEl);
        if (viewModal) viewModal.hide();
        setTimeout(() => editCitizen(data), 300);
    };

    const modal = new bootstrap.Modal(document.getElementById('viewCitizenModal'));
    modal.show();
}

function editCitizen(data) {
    document.getElementById('e_user_id').value = data.id || '';
    document.getElementById('e_name').value = data.name || data.full_name || '';
    document.getElementById('e_mobile').value = (data.mobile || '').replace(/^91/, '');
    document.getElementById('e_whatsapp').value = (data.whatsapp || '').replace(/^91/, '');
    document.getElementById('e_email').value = data.email || '';
    document.getElementById('e_role').value = data.role || 'voter';
    document.getElementById('e_district').value = data.district || '';
    document.getElementById('e_constituency').value = data.constituency || '';
    document.getElementById('e_panchayat').value = data.panchayat || '';
    document.getElementById('e_pincode').value = data.pincode || '';
    document.getElementById('e_designation').value = data.designation || '';
    document.getElementById('e_gender').value = data.gender || '';
    document.getElementById('e_address').value = data.address || '';
    document.getElementById('e_bio').value = data.bio || data.about || '';
    document.getElementById('e_status').value = data.status || 'ACTIVE';
    document.getElementById('e_is_mobile_verified').checked = parseInt(data.is_mobile_verified) === 1;

    const modal = new bootstrap.Modal(document.getElementById('editCitizenModal'));
    modal.show();
}
</script>
</body>
</html>
