<?php
/**
 * BiharElection.com - Public OTP Verification Page
 */
require_once __DIR__ . '/includes/auth_helper.php';

$mobile = trim($_GET['mobile'] ?? ($_POST['mobile'] ?? ''));
$purpose = trim($_GET['purpose'] ?? ($_POST['purpose'] ?? 'login'));

$mobile = preg_replace('/[^0-9]/', '', $mobile);
if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
    $mobile = substr($mobile, 2);
}

if (empty($mobile) && isset($_SESSION['pending_otp']['mobile'])) {
    $mobile = $_SESSION['pending_otp']['mobile'];
    $purpose = $_SESSION['pending_otp']['purpose'] ?? 'login';
}

if (empty($mobile) || strlen($mobile) !== 10) {
    header("Location: login.php");
    exit();
}

$pageTitle = 'Verify OTP — Bihar Election';
$pageDescription = 'Verify your 6-digit security OTP sent via SMS to complete login to BiharElection.com.';
$pageCanonical = SITE_URL . '/verify-otp.php';
$activeNav = 'login';

$error = '';
$success = '';

// Handle Resend OTP
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    $name = $_SESSION['pending_otp']['name'] ?? 'Citizen';
    $res = sendUserOTP($mobile, $name, $purpose);
    if ($res['status'] === 'success') {
        $success = "A fresh 6-digit OTP has been sent to +91 " . htmlspecialchars($mobile) . ".";
    } else {
        $error = $res['msg'] ?? 'Failed to resend OTP.';
    }
}

// Handle OTP Verification Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'verify_otp') {
    $otp = trim($_POST['otp'] ?? '');

    if (empty($otp) || strlen($otp) < 4) {
        $error = "Please enter the valid OTP code received on your mobile.";
    } else {
        $verifyRes = verifyUserOTP($mobile, $otp);

        if ($verifyRes['status'] === 'success') {
            $user = $verifyRes['user'];

            if ($purpose === 'forgot') {
                $_SESSION['password_reset_mobile'] = $mobile;
                header("Location: forgot-password.php?step=new_password");
                exit();
            }

            // Normal login or registration verification
            setUserSession($user);

            $redirect = $_SESSION['auth_redirect'] ?? 'dashboard.php';
            unset($_SESSION['auth_redirect']);
            header("Location: " . $redirect);
            exit();
        } else {
            $error = $verifyRes['msg'] ?? 'Invalid or expired OTP code.';
        }
    }
}

$maskedMobile = substr($mobile, 0, 2) . '******' . substr($mobile, -2);

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
                        <h2 class="h4 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Verify Security OTP</h2>
                        <p class="small text-white-50 mb-0">Enter the 6-digit code sent via SMS to your mobile</p>
                    </div>

                    <div class="p-4 p-md-5">
                        
                        <div class="text-center mb-4">
                            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill fs-6 fw-semibold">
                                📱 +91 <?php echo htmlspecialchars($maskedMobile); ?>
                            </span>
                            <div class="small text-muted mt-2">
                                Sent via Sender ID: <strong class="text-dark">BIHELE</strong>
                            </div>
                        </div>

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

                        <form method="POST" action="verify-otp.php" class="needs-validation" novalidate>
                            <input type="hidden" name="action" value="verify_otp">
                            <input type="hidden" name="mobile" value="<?php echo htmlspecialchars($mobile); ?>">
                            <input type="hidden" name="purpose" value="<?php echo htmlspecialchars($purpose); ?>">

                            <div class="mb-4 text-center">
                                <label class="form-label small fw-bold text-muted text-uppercase d-block mb-2">Enter 6-Digit OTP</label>
                                <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold fs-3 tracking-wider font-monospace" placeholder="••••••" maxlength="6" pattern="[0-9]{4,6}" autocomplete="one-time-code" required autofocus style="letter-spacing: 0.35em;">
                                <div class="form-text small text-muted mt-2">
                                    Valid for 10 minutes.
                                </div>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-warning btn-lg fw-bold text-dark shadow-sm py-2" style="background: linear-gradient(135deg, #ff9933 0%, #f59e0b 100%); border: none;">
                                    <i class="bi bi-check-circle-fill me-1"></i> Verify & Continue
                                </button>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top small">
                                <a href="login.php" class="text-muted text-decoration-none">
                                    <i class="bi bi-arrow-left me-1"></i> Change Mobile
                                </a>

                                <div>
                                    <span id="timerContainer" class="text-muted">
                                        Resend in <span id="countdown" class="fw-bold text-dark">45</span>s
                                    </span>
                                    <a href="verify-otp.php?mobile=<?php echo urlencode($mobile); ?>&purpose=<?php echo urlencode($purpose); ?>&resend=1" id="resendBtn" class="text-primary fw-bold text-decoration-none d-none">
                                        <i class="bi bi-arrow-repeat me-1"></i> Resend OTP
                                    </a>
                                </div>
                            </div>

                        </form>

                    </div>

                </div>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    let timeLeft = 45;
    const countdownEl = document.getElementById("countdown");
    const timerContainer = document.getElementById("timerContainer");
    const resendBtn = document.getElementById("resendBtn");

    const timer = setInterval(() => {
        timeLeft--;
        if (countdownEl) countdownEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(timer);
            if (timerContainer) timerContainer.classList.add("d-none");
            if (resendBtn) resendBtn.classList.remove("d-none");
        }
    }, 1000);
});
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
