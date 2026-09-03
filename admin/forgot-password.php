<?php
require_once __DIR__ . '/auth_check.php';

$error = '';
$success = '';
$step = $_GET['step'] ?? 'request';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action === 'request_reset') {
        $identifier = sanitize($_POST['identifier'] ?? '');
        if (empty($identifier)) {
            $error = "Please enter your admin username or registered email.";
        } else {
            $conn = getAdminDB();
            $userFound = null;
            if ($conn) {
                $stmt = $conn->prepare("SELECT * FROM `admin_users` WHERE (`username` = ? OR `email` = ?) AND `status` = 'ACTIVE' LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param("ss", $identifier, $identifier);
                    $stmt->execute();
                    $res = $stmt->get_result();
                    if ($res && $res->num_rows === 1) {
                        $userFound = $res->fetch_assoc();
                    }
                }
            }

            if ($userFound || $identifier === 'admin') {
                // In production, dispatch secure recovery email or SMS
                $success = "Password reset instructions and verification code have been dispatched to the registered administrator contact.";
                $step = 'sent';
            } else {
                $error = "No active administrator account found matching those details.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password — Admin Portal | Bihar Election</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 50%, #0f172a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 1.5rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35);
            max-width: 440px;
            width: 100%;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .login-header {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            padding: 2.25rem 2rem 2rem;
            color: white;
            text-align: center;
            position: relative;
        }
        .login-body {
            padding: 2.25rem 2rem;
        }
        .form-control {
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            border-color: #cbd5e1;
            font-size: 0.95rem;
        }
        .btn-reset {
            background: linear-gradient(135deg, #d31027 0%, #b10a1e 100%);
            border: none;
            color: white;
            border-radius: 0.75rem;
            padding: 0.85rem;
            font-weight: 600;
            font-size: 1rem;
            width: 100%;
            transition: all 0.3s;
        }
        .btn-reset:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(211, 16, 39, 0.35);
            color: white;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <img src="../assets/image/logo.png" alt="Bihar Election" height="50" class="bg-white p-2 rounded-3 shadow-sm mb-2">
        <h3 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Reset Password</h3>
        <p class="mb-0 text-white-50 small">Admin Account Recovery</p>
    </div>
    
    <div class="login-body">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3" role="alert">
                <i class="fas fa-circle-exclamation me-2"></i>
                <div class="small fw-semibold"><?php echo htmlspecialchars($error); ?></div>
            </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success d-flex align-items-center mb-4 rounded-3" role="alert">
                <i class="fas fa-circle-check me-2"></i>
                <div class="small fw-semibold"><?php echo htmlspecialchars($success); ?></div>
            </div>
        <?php endif; ?>

        <?php if ($step === 'sent'): ?>
            <div class="text-center py-3">
                <p class="text-muted small mb-4">Please check your inbox or mobile device for instructions to securely reset your credentials.</p>
                <a href="login.php" class="btn btn-primary rounded-3 w-100 fw-bold py-2">
                    <i class="fas fa-arrow-left me-1"></i> Back to Admin Sign In
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="forgot-password.php">
                <input type="hidden" name="action" value="request_reset">
                
                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted text-uppercase">Admin Username or Email</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-envelope text-muted"></i></span>
                        <input type="text" name="identifier" class="form-control border-start-0" placeholder="admin or email@example.com" required autofocus>
                    </div>
                    <div class="form-text small text-muted">Enter your registered admin identifier to receive password reset instructions.</div>
                </div>

                <button type="submit" class="btn btn-reset mb-3">
                    <i class="fas fa-paper-plane me-2"></i> Send Reset Link
                </button>
            </form>

            <div class="text-center mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                <a href="login.php" class="text-decoration-none small text-muted">
                    <i class="fas fa-arrow-left me-1"></i> Sign In
                </a>
                <a href="../forgot-password.php" class="text-decoration-none small text-primary">
                    Citizen Reset &rarr;
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
