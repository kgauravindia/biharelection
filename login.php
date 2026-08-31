<?php
/**
 * BiharElection.com - Public User Login
 * Supports Fast Mobile OTP Login & Password Login
 */
require_once __DIR__ . '/includes/auth_helper.php';

if (isUserLoggedIn()) {
    header("Location: dashboard.php");
    exit();
}

$pageTitle = 'Login — Bihar Election Public Portal & Voter Community';
$pageDescription = 'Login to BiharElection.com to track 2026 assembly data, constituency alerts, panchayat rosters, and bookmark your political representatives.';
$pageKeywords = 'Bihar election login, voter portal, bihar OTP login, assembly dashboard';
$pageCanonical = SITE_URL . '/login.php';
$activeNav = 'login';

$error = '';
$success = '';
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'password' ? 'password' : 'otp';

// Handle OTP Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_otp') {
    $mobile = trim($_POST['mobile'] ?? '');
    $mobile = preg_replace('/[^0-9]/', '', $mobile);

    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }

    if (strlen($mobile) !== 10) {
        $error = "Please enter a valid 10-digit mobile number.";
        $activeTab = 'otp';
    } else {
        $res = sendUserOTP($mobile, 'Citizen', 'login');
        if ($res['status'] === 'success') {
            header("Location: verify-otp.php?mobile=" . urlencode($mobile) . "&purpose=login");
            exit();
        } else {
            $error = $res['msg'] ?? 'Failed to send OTP. Please try again.';
            $activeTab = 'otp';
        }
    }
}

// Handle Password Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'password_login') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($identifier) || empty($password)) {
        $error = "Please enter both your mobile/email and password.";
        $activeTab = 'password';
    } else {
        $authenticated = false;
        $pdo = Database::getConnection();

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE (`mobile` = ? OR `email` = ?) AND `status` = 'ACTIVE' LIMIT 1");
                $stmt->execute([$identifier, $identifier]);
                $user = $stmt->fetch();

                if ($user && (!empty($user['password']) && (password_verify($password, $user['password']) || md5($password) === $user['password'] || $password === $user['password']))) {
                    $authenticated = true;
                    setUserSession($user);

                    // Upgrade legacy MD5 hash to Bcrypt if necessary
                    if (!password_verify($password, $user['password']) && strpos($user['password'], '$2y$') !== 0) {
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $pdo->prepare("UPDATE `be_users` SET `password` = ? WHERE `id` = ?")->execute([$newHash, $user['id']]);
                    }

                    $redirect = $_SESSION['auth_redirect'] ?? 'dashboard.php';
                    unset($_SESSION['auth_redirect']);
                    header("Location: " . $redirect);
                    exit();
                }
            } catch (Throwable $e) {
                error_log("Login error: " . $e->getMessage());
            }
        }

        if (!$authenticated) {
            $error = "Invalid mobile/email or password. If you haven't set a password, use OTP Login above.";
            $activeTab = 'password';
        }
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="auth-page-wrapper py-5" style="min-height: 80vh; background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-8 col-lg-5">
                
                <!-- Auth Card -->
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                    
                    <!-- Card Top Header with Tricolor Accent -->
                    <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
                        <a href="index.php" class="d-inline-block bg-white p-2 rounded-3 shadow-sm mb-3">
                            <img src="assets/image/logo.png" alt="Bihar Election" height="46" class="d-block">
                        </a>
                        <h2 class="h4 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Public Portal Login</h2>
                        <p class="small text-white-50 mb-0">Sign in to access Bihar constituency analytics & citizen tools</p>
                    </div>

                    <div class="p-4 p-md-5">
                        
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 small py-2 px-3 mb-4" role="alert">
                                <i class="bi bi-exclamation-triangle-fill flex-shrink-0 text-danger fs-5"></i>
                                <div><?php echo htmlspecialchars($error); ?></div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 small py-2 px-3 mb-4" role="alert">
                                <i class="bi bi-check-circle-fill flex-shrink-0 text-success fs-5"></i>
                                <div><?php echo htmlspecialchars($success); ?></div>
                                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Tabs for Login Type -->
                        <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-4" id="loginTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-3 fw-bold small py-2 <?php echo $activeTab === 'otp' ? 'active shadow-sm' : 'text-muted'; ?>" id="otp-tab" data-bs-toggle="tab" data-bs-target="#otp-pane" type="button" role="tab" style="<?php echo $activeTab === 'otp' ? 'background: #0b192c; color: #fff;' : ''; ?>">
                                    <i class="bi bi-phone me-1 text-warning"></i> OTP Fast Login
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-3 fw-bold small py-2 <?php echo $activeTab === 'password' ? 'active shadow-sm' : 'text-muted'; ?>" id="password-tab" data-bs-toggle="tab" data-bs-target="#password-pane" type="button" role="tab" style="<?php echo $activeTab === 'password' ? 'background: #0b192c; color: #fff;' : ''; ?>">
                                    <i class="bi bi-shield-lock me-1"></i> Password Login
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="loginTabsContent">
                            
                            <!-- 1. OTP Login Pane -->
                            <div class="tab-pane fade <?php echo $activeTab === 'otp' ? 'show active' : ''; ?>" id="otp-pane" role="tabpanel">
                                <form method="POST" action="login.php" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="request_otp">
                                    
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Mobile Number</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light border-end-0 fw-semibold text-muted">
                                                🇮🇳 +91
                                            </span>
                                            <input type="tel" name="mobile" class="form-control form-control-lg border-start-0 ps-2" placeholder="10-digit mobile number" pattern="[0-9]{10}" maxlength="10" required autofocus>
                                        </div>
                                        <div class="form-text small text-muted">
                                            <i class="bi bi-shield-check text-success me-1"></i> We'll send a 6-digit OTP via official SMS (Sender: <strong>BIHELE</strong>)
                                        </div>
                                    </div>

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm py-2" style="background: linear-gradient(135deg, #ff9933 0%, #f59e0b 100%); border: none;">
                                            <i class="bi bi-send-fill me-1"></i> Send OTP via SMS
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- 2. Password Login Pane -->
                            <div class="tab-pane fade <?php echo $activeTab === 'password' ? 'show active' : ''; ?>" id="password-pane" role="tabpanel">
                                <form method="POST" action="login.php" class="needs-validation" novalidate>
                                    <input type="hidden" name="action" value="password_login">

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-muted text-uppercase">Mobile or Email</label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-person text-muted"></i></span>
                                            <input type="text" name="identifier" class="form-control form-control-lg" placeholder="Enter Mobile Number or Email" required>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label small fw-bold text-muted text-uppercase mb-0">Password</label>
                                            <a href="forgot-password.php" class="small text-decoration-none text-primary fw-semibold">Forgot Password?</a>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light"><i class="bi bi-lock text-muted"></i></span>
                                            <input type="password" name="password" id="loginPasswordInput" class="form-control form-control-lg" placeholder="••••••••" required>
                                            <button class="btn btn-light border" type="button" onclick="togglePasswordVisibility('loginPasswordInput')">
                                                <i class="bi bi-eye" id="toggleIcon"></i>
                                            </button>
                                        </div>
                                    </div>

                                    <div class="d-grid mt-4">
                                        <button type="submit" class="btn btn-primary btn-lg fw-bold shadow-sm py-2" style="background: #0b192c; border-color: #0b192c;">
                                            <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to Account
                                        </button>
                                    </div>
                                </form>
                            </div>

                        </div>

                        <div class="text-center mt-4 pt-3 border-top">
                            <p class="small text-muted mb-0">
                                Don't have an account? 
                                <a href="register.php" class="fw-bold text-decoration-none text-warning">Create Account / Join</a>
                            </p>
                        </div>

                    </div>

                    <!-- Security Footer Badge -->
                    <div class="bg-light px-4 py-3 border-top text-center">
                        <small class="text-muted d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-lock-fill text-success"></i> 256-Bit Encrypted & DLT Certified SMS Gateway
                        </small>
                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('bi-eye');
        icon.classList.add('bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('bi-eye-slash');
        icon.classList.add('bi-eye');
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
