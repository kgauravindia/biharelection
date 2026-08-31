<?php
/**
 * BiharElection.com - Public Password Reset via OTP
 */
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = 'Reset Password — Bihar Election';
$pageDescription = 'Reset your BiharElection.com account password securely using Mobile SMS OTP verification.';
$pageCanonical = SITE_URL . '/forgot-password.php';
$activeNav = 'login';

$step = $_GET['step'] ?? 'request';
$error = '';
$success = '';

// Step 1: Request OTP
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request_reset_otp') {
    $mobile = trim($_POST['mobile'] ?? '');
    $mobile = preg_replace('/[^0-9]/', '', $mobile);

    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }

    if (strlen($mobile) !== 10) {
        $error = "Please enter a valid 10-digit registered mobile number.";
    } else {
        $pdo = Database::getConnection();
        $userExists = true;

        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT id, name FROM `be_users` WHERE `mobile` = ? LIMIT 1");
                $stmt->execute([$mobile]);
                $user = $stmt->fetch();
                if (!$user) {
                    $userExists = false;
                    $error = "No account found with this mobile number. Please register first.";
                }
            } catch (Throwable $e) {}
        }

        if ($userExists) {
            $name = $user['name'] ?? 'Citizen';
            $res = sendUserOTP($mobile, $name, 'forgot');
            if ($res['status'] === 'success') {
                header("Location: verify-otp.php?mobile=" . urlencode($mobile) . "&purpose=forgot");
                exit();
            } else {
                $error = $res['msg'] ?? 'Failed to send OTP.';
            }
        }
    }
}

// Step 2: Set New Password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'set_new_password') {
    $resetMobile = $_SESSION['password_reset_mobile'] ?? '';
    $newPassword = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($resetMobile)) {
        header("Location: forgot-password.php");
        exit();
    }

    if (strlen($newPassword) < 6) {
        $error = "Password must be at least 6 characters long.";
    } elseif ($newPassword !== $confirmPassword) {
        $error = "Passwords do not match.";
    } else {
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE `be_users` SET `password` = ? WHERE `mobile` = ?");
                $stmt->execute([$hashed, $resetMobile]);
            } catch (Throwable $e) {}
        }

        unset($_SESSION['password_reset_mobile']);
        $success = "Password successfully reset! You can now log in with your new password.";
        $step = 'done';
    }
}

require_once __DIR__ . '/header.php';
?>

<div class="auth-page-wrapper py-5" style="min-height: 80vh; background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);">
    <div class="container py-3">
        <div class="row justify-content-center">
            <div class="col-12 col-md-7 col-lg-5">
                
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden bg-white">
                    
                    <div class="p-4 text-center text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
                        <a href="index.php" class="d-inline-block bg-white p-2 rounded-3 shadow-sm mb-3">
                            <img src="assets/image/logo.png" alt="Bihar Election" height="46" class="d-block">
                        </a>
                        <h2 class="h4 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Reset Password</h2>
                        <p class="small text-white-50 mb-0">Secure reset via verified SMS OTP</p>
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

                        <?php if ($step === 'new_password'): ?>
                            
                            <!-- Set New Password Form -->
                            <form method="POST" action="forgot-password.php" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="set_new_password">

                                <div class="mb-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">New Password</label>
                                    <input type="password" name="password" class="form-control form-control-lg" placeholder="Minimum 6 characters" required autofocus>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Confirm New Password</label>
                                    <input type="password" name="confirm_password" class="form-control form-control-lg" placeholder="Re-enter password" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm py-2">
                                        <i class="bi bi-check-lg me-1"></i> Update Password
                                    </button>
                                </div>
                            </form>

                        <?php elseif ($step === 'done'): ?>
                            
                            <div class="text-center py-3">
                                <a href="login.php?tab=password" class="btn btn-primary btn-lg fw-bold px-4 shadow-sm" style="background: #0b192c; border: none;">
                                    <i class="bi bi-box-arrow-in-right me-1"></i> Proceed to Login
                                </a>
                            </div>

                        <?php else: ?>
                            
                            <!-- Request OTP Form -->
                            <form method="POST" action="forgot-password.php" class="needs-validation" novalidate>
                                <input type="hidden" name="action" value="request_reset_otp">

                                <div class="mb-4">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Registered Mobile Number</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light border-end-0 fw-semibold text-muted">
                                            🇮🇳 +91
                                        </span>
                                        <input type="tel" name="mobile" class="form-control form-control-lg border-start-0 ps-2" placeholder="10-digit mobile" pattern="[0-9]{10}" maxlength="10" required autofocus>
                                    </div>
                                    <div class="form-text small text-muted">We will send an OTP to your phone to verify your identity.</div>
                                </div>

                                <div class="d-grid mb-3">
                                    <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm py-2" style="background: linear-gradient(135deg, #ff9933 0%, #f59e0b 100%); border: none;">
                                        <i class="bi bi-send-fill me-1"></i> Send OTP to Reset
                                    </button>
                                </div>

                                <div class="text-center pt-3 border-top">
                                    <a href="login.php" class="small text-decoration-none text-muted">
                                        <i class="bi bi-arrow-left me-1"></i> Back to Login
                                    </a>
                                </div>
                            </form>

                        <?php endif; ?>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
