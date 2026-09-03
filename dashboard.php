<?php
/**
 * BiharElection.com - Public User Dashboard & Profile Portal
 */
require_once __DIR__ . '/includes/auth_helper.php';
requireUserLogin();

$user = getCurrentUser();
$districts = DataProvider::getDistricts();
if (!empty($districts) && is_array($districts)) {
    usort($districts, function ($a, $b) {
        return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
    });
}

$pageTitle = 'Citizen Dashboard — ' . htmlspecialchars($user['name'] ?? 'User') . ' | Bihar Election';
$pageDescription = 'Manage your constituency preferences, track live delimitation updates and monitor elected representatives.';
$pageCanonical = SITE_URL . '/dashboard.php';
$activeNav = 'dashboard';

$message = '';
$error = '';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $role = trim($_POST['role'] ?? 'voter');
    $district = trim($_POST['district'] ?? '');
    $constituency = trim($_POST['constituency'] ?? '');
    $panchayat = trim($_POST['panchayat'] ?? '');

    if (empty($name)) {
        $error = "Name cannot be empty.";
    } else {
        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE `users` SET `name` = ?, `email` = ?, `role` = ?, `district` = ?, `constituency` = ?, `panchayat` = ? WHERE `id` = ?");
                $stmt->execute([$name, $email, $role, $district, $constituency, $panchayat, $user['id']]);
            } catch (Throwable $e) {
                error_log("Update profile error: " . $e->getMessage());
            }
        }

        $_SESSION['public_user_name'] = $name;
        $_SESSION['public_user_email'] = $email;
        $_SESSION['public_user_role'] = $role;
        $_SESSION['public_user_district'] = $district;
        $_SESSION['public_user_constituency'] = $constituency;
        $_SESSION['public_user_panchayat'] = $panchayat;

        $user = getCurrentUser();
        $message = "Your profile has been updated successfully.";
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="dashboard-wrapper py-5 bg-light">
    <div class="container">
        
        <!-- Welcome Hero Banner -->
        <?php 
        $handle = !empty($user['username_handle']) ? $user['username_handle'] : (string)($user['id'] ?? '1');
        $displayName = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['name']) ? $user['name'] : 'Citizen');
        $publicProfileUrl = SITE_URL . '/user/' . urlencode(ltrim($handle, '@'));
        $comp = getProfileCompletionPercent($user);
        ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-inline-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 text-uppercase">
                            <?php echo htmlspecialchars(strtoupper($user['role'] ?? 'VOTER')); ?>
                        </span>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success fw-bold px-3 py-1">
                            <i class="bi bi-shield-check"></i> Mobile Verified
                        </span>
                        <span class="badge bg-info bg-opacity-25 text-info border border-info fw-bold px-3 py-1">
                            <i class="bi bi-eye-fill"></i> <?php echo number_format($user['counter'] ?? 0); ?> Profile Views
                        </span>
                    </div>
                    <h1 class="display-6 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                        Welcome, <?php echo htmlspecialchars($displayName); ?>!
                    </h1>
                    <p class="text-white-50 mb-0">
                        📱 +91 <?php echo htmlspecialchars(maskMobileNumber($user['mobile'] ?? '')); ?>
                        <?php if (!empty($user['district'])): ?>
                            &bull; <i class="bi bi-geo-alt-fill text-warning"></i> <?php echo htmlspecialchars($user['district']); ?>
                        <?php endif; ?>
                        <?php if (!empty($user['username_handle'])): ?>
                            &bull; <span class="text-warning fw-semibold"><?php echo htmlspecialchars($user['username_handle']); ?></span>
                        <?php endif; ?>
                    </p>
                </div>
                <div class="col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
                    <a href="<?php echo $publicProfileUrl; ?>" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                        <i class="bi bi-person-circle me-1"></i> Public Profile
                    </a>
                    <a href="edit-profile.php" class="btn btn-warning text-dark rounded-pill px-3 py-2 fw-bold">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </a>
                    <a href="logout.php" class="btn btn-outline-light rounded-pill px-3 py-2 fw-bold">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                <div><?php echo htmlspecialchars($message); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                <div><?php echo htmlspecialchars($error); ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            
            <!-- Quick Hub Cards -->
            <div class="col-lg-8">
                
                <h4 class="h5 fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                    <i class="bi bi-grid-fill text-primary me-2"></i> Political Intelligence & Quick Access
                </h4>

                <div class="row g-3 mb-4">
                    
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary fs-3">
                                    🏛️
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Vidhan Sabha Roster</h6>
                                    <p class="small text-muted mb-2">243 Constituencies & MLA contacts</p>
                                    <a href="vidhan-sabha.php" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Explore ACs &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success fs-3">
                                    🌾
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Panchayat Hub</h6>
                                    <p class="small text-muted mb-2">8,053 Mukhiya & Sarpanch records</p>
                                    <a href="panchayat.php?tab=mukhiya" class="btn btn-sm btn-outline-success rounded-pill fw-bold">View Panchayat &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning fs-3">
                                    📊
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">38 Districts Intelligence</h6>
                                    <p class="small text-muted mb-2">Demographics, polling stations & map</p>
                                    <a href="district.php?slug=saran" class="btn btn-sm btn-outline-dark rounded-pill fw-bold">View Districts &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success fs-3">
                                    <i class="bi bi-whatsapp text-success"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Daily 3-Slot WhatsApp</h6>
                                    <p class="small text-muted mb-2">Morning, Afternoon & Evening digests</p>
                                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-sm btn-success rounded-pill fw-bold">Join Broadcast &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Profile Settings Card -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                    <h5 class="fw-bold mb-3 text-dark" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-person-lines-fill text-primary me-2"></i> Profile Settings
                    </h5>

                    <form method="POST" action="dashboard.php">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Full Name</label>
                            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                            <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(maskMobileNumber($user['mobile'] ?? '')); ?>" readonly>
                            <div class="form-text small text-muted">Verified by DLT SMS Gateway</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="Optional">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Profile Role</label>
                            <select name="role" class="form-select">
                                <option value="voter" <?php echo ($user['role'] ?? '') === 'voter' ? 'selected' : ''; ?>>Voter / Citizen</option>
                                <option value="mukhiya" <?php echo ($user['role'] ?? '') === 'mukhiya' ? 'selected' : ''; ?>>Panchayat Representative</option>
                                <option value="candidate" <?php echo ($user['role'] ?? '') === 'candidate' ? 'selected' : ''; ?>>Candidate / Politician</option>
                                <option value="analyst" <?php echo ($user['role'] ?? '') === 'analyst' ? 'selected' : ''; ?>>Analyst / Researcher</option>
                                <option value="journalist" <?php echo ($user['role'] ?? '') === 'journalist' ? 'selected' : ''; ?>>Journalist / Media</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Home District</label>
                            <select name="district" class="form-select">
                                <option value="">Select District</option>
                                <?php if (!empty($districts)): ?>
                                    <?php foreach ($districts as $d): ?>
                                        <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo ($user['district'] ?? '') === $d['name'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($d['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-warning fw-bold text-dark shadow-sm">
                                <i class="bi bi-check-lg me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
