<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Handle Add User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user']) && $conn) {
    $username = sanitize($_POST['username'] ?? '');
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = sanitize($_POST['role'] ?? 'editor');

    if (empty($username) || empty($password) || empty($name)) {
        $error = "Username, Name and Password are required.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO `admin_users` (`username`, `name`, `email`, `password`, `role`, `status`) VALUES (?, ?, ?, ?, ?, 'ACTIVE')");
        if ($stmt) {
            $stmt->bind_param("sssss", $username, $name, $email, $hashed, $role);
            if ($stmt->execute()) {
                $message = "Admin user {$username} created successfully!";
            } else {
                $error = "Error adding user (username may already exist): " . $conn->error;
            }
        }
    }
}

// Handle Delete User
if (isset($_GET['delete_id']) && $conn) {
    $del_id = (int)$_GET['delete_id'];
    if ($del_id > 1) {
        $conn->query("DELETE FROM `admin_users` WHERE `id` = $del_id");
        $message = "User deleted successfully.";
    } else {
        $error = "Cannot delete the primary root administrator account.";
    }
}

$users = [];
if ($conn) {
    $res = $conn->query("SELECT * FROM `admin_users` ORDER BY `id` ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) $users[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Users — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">System Administrators & Staff</h1>
                <p class="text-muted mb-0">Manage authorized users, editor roles and authentication credentials.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <button class="btn btn-danger fw-semibold px-3 py-2 rounded-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                    <i class="fas fa-user-plus me-1"></i> Add Admin User
                </button>
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

        <!-- Users Table -->
        <div class="section-card">
            <div class="section-card-header">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-shield me-2 text-primary"></i> Authorized Administrative Accounts</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Last Login</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($users)): ?>
                            <?php foreach ($users as $u): ?>
                                <tr>
                                    <td><strong class="text-dark"><?php echo htmlspecialchars($u['name']); ?></strong></td>
                                    <td><code><?php echo htmlspecialchars($u['username']); ?></code></td>
                                    <td><?php echo htmlspecialchars($u['email'] ?? '—'); ?></td>
                                    <td>
                                        <span class="badge <?php echo ($u['role'] === 'admin') ? 'bg-danger' : 'bg-secondary'; ?>">
                                            <?php echo strtoupper(htmlspecialchars($u['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-soft-success"><?php echo htmlspecialchars($u['status']); ?></span>
                                    </td>
                                    <td>
                                        <small class="text-muted"><?php echo !empty($u['last_login']) ? date('d M Y, h:i A', strtotime($u['last_login'])) : 'Never'; ?></small>
                                    </td>
                                    <td class="text-end">
                                        <?php if ((int)$u['id'] !== 1): ?>
                                            <a href="users.php?delete_id=<?php echo $u['id']; ?>" class="btn btn-sm btn-light border text-danger" onclick="return confirm('Delete this user?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge bg-light text-muted border">Primary</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No database users found. Root admin fallback active.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <div class="modal fade" id="addUserModal" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header bg-danger text-white rounded-top-4">
                        <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Add Admin User</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" action="">
                        <input type="hidden" name="add_user" value="1">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Full Name *</label>
                                <input type="text" name="name" class="form-control" placeholder="Rahul Verma" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Username *</label>
                                <input type="text" name="username" class="form-control" placeholder="rahul_editor" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="rahul@biharelection.com">
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Password *</label>
                                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Role</label>
                                <select name="role" class="form-select">
                                    <option value="editor">Editor (Can edit candidates & data)</option>
                                    <option value="admin">Administrator (Full Access)</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-3 pt-0">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger fw-bold px-4">Create Account</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
