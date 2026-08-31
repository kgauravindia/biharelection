<?php
/**
 * BiharElection.com - Public User Registration
 * Connects Bihar Voters, Candidates & Representatives
 */
require_once __DIR__ . '/includes/auth_helper.php';

if (isUserLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$pageTitle = 'Join Bihar Election — Create Voter & Citizen Profile';
$pageDescription = 'Register on BiharElection.com to track local constituency developments, receive election bulletins, and engage with elected representatives.';
$pageCanonical = SITE_URL . '/register.php';
$activeNav = 'register';

$districts = DataProvider::getDistricts();
if (!empty($districts) && is_array($districts)) {
    usort($districts, function ($a, $b) {
        return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
    });
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $role = trim($_POST['role'] ?? 'voter');
    $district = trim($_POST['district'] ?? '');
    $constituency = trim($_POST['constituency'] ?? '');

    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }

    if (empty($name) || strlen($name) < 2) {
        $error = "Please enter your full name.";
    } elseif (strlen($mobile) !== 10) {
        $error = "Please provide a valid 10-digit mobile number.";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "Password must be at least 6 characters long.";
    } else {
        $pdo = Database::getConnection();
        $isUnique = true;

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id FROM `be_users` WHERE `mobile` = ? LIMIT 1");
                $stmt->execute([$mobile]);
                if ($stmt->fetch()) {
                    $isUnique = false;
                    $error = "This mobile number is already registered. Please log in instead.";
                }
            } catch (Throwable $e) {
                error_log("Registration check error: " . $e->getMessage());
            }
        }

        if ($isUnique) {
            $hashedPassword = !empty($password) ? password_hash($password, PASSWORD_DEFAULT) : null;
            $otp = (string)random_int(100000, 999999);
            $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

            $_SESSION['pending_otp'] = [
                'mobile'       => $mobile,
                'name'         => $name,
                'email'        => $email,
                'password'     => $hashedPassword,
                'role'         => $role,
                'district'     => $district,
                'constituency' => $constituency,
                'otp'          => $otp,
                'expiry'       => time() + 600,
                'purpose'      => 'register'
            ];

            if ($pdo) {
                try {
                    $insertStmt = $pdo->prepare("INSERT INTO `be_users` (`name`, `mobile`, `email`, `password`, `role`, `district`, `constituency`, `otp_code`, `otp_expiry`, `is_mobile_verified`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
                    $insertStmt->execute([$name, $mobile, $email, $hashedPassword, $role, $district, $constituency, $otp, $expiry]);
                } catch (Throwable $e) {
                    error_log("Registration insert error: " . $e->getMessage());
                }
            }

            // Send official SMS OTP
            sendOTP($mobile, $name, $otp);

            header("Location: verify-otp.php?mobile=" . urlencode($mobile) . "&purpose=register");
            exit();
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="auth-page-wrapper py-5" style="min-height: 80vh; background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-9 col-lg-6">
                
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                    
                    <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
                        <a href="index.php" class="d-inline-block bg-white p-2 rounded-3 shadow-sm mb-3">
                            <img src="assets/image/logo.png" alt="Bihar Election" height="46" class="d-block">
                        </a>
                        <h2 class="h4 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Create Free Account</h2>
                        <p class="small text-white-50 mb-0">Join 100,000+ Bihar citizens, voters & leaders</p>
                    </div>

                    <div class="p-4 p-md-5">
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 small py-2 px-3 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 text-danger fs-5"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="register.php" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="register">

                            <div class="row g-3">
                                
                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-lg" placeholder="e.g. Ramesh Kumar" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 fw-semibold text-muted">
                                            🇮🇳 +91
                                        </span>
                                        <input type="tel" name="mobile" class="form-control form-control-lg border-start-0 ps-2" placeholder="10-digit mobile" pattern="[0-9]{10}" maxlength="10" required value="<?php echo htmlspecialchars($_POST['mobile'] ?? ''); ?>">
                                    </div>
                                    <div class="form-text small text-muted">SMS OTP will be sent for verification</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Email Address <span class="text-muted small">(Optional)</span></label>
                                    <input type="email" name="email" class="form-control form-control-lg" placeholder="name@example.com" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">I Am A</label>
                                    <select name="role" class="form-select form-select-lg">
                                        <option value="voter" selected>Voter / Citizen (नागरिक/मतदाता)</option>
                                        <option value="mukhiya">Panchayat Representative (मुखिया/सरपंच)</option>
                                        <option value="candidate">Political Candidate / Aspirant</option>
                                        <option value="analyst">Political Analyst / Researcher</option>
                                        <option value="journalist">Journalist / Media</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Home District</label>
                                    <select name="district" class="form-select form-select-lg">
                                        <option value="">Select District</option>
                                        <?php if (!empty($districts)): ?>
                                            <?php foreach ($districts as $d): ?>
                                                <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo (isset($_POST['district']) && $_POST['district'] === $d['name']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($d['name']); ?> (<?php echo htmlspecialchars($d['name_hi'] ?? ''); ?>)
                                                </option>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Create Password <span class="text-muted small">(Optional)</span></label>
                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Minimum 6 characters">
                                    <div class="form-text small text-muted">You can also log in anytime using 1-Click Mobile OTP</div>
                                </div>

                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm py-2" style="background: linear-gradient(135deg, #ff9933 0%, #f59e0b 100%); border: none;">
                                    <i class="bi bi-shield-check me-1"></i> Register & Verify via OTP
                                </button>
                            </div>

                            <div class="text-center mt-4 pt-3 border-top">
                                <p class="small text-muted mb-0">
                                    Already have an account? 
                                    <a href="login.php" class="fw-bold text-decoration-none text-primary">Log In Here</a>
                                </p>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
