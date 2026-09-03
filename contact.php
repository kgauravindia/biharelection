<?php
/**
 * Bihar Election - Official Contact & Grievance Desk
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pageTitle = 'Contact Us — Bihar Election Intelligence & Editorial Desk';
$pageDescription = 'Get in touch with Bihar Election team for editorial inquiries, candidate profile updates, advertisement bookings, and civic partnership.';
$pageKeywords = 'Contact Bihar Election, Bihar Election Office, Candidate Profile Verification, OfferPlant Technologies Patna, Election Helpline Bihar';
$pageCanonical = SITE_URL . '/contact/';
$activeNav = 'contact';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_contact'])) {
    $name = trim($_POST['name'] ?? '');
    $mobile = trim($_POST['mobile'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? 'General Inquiry');
    $message = trim($_POST['message'] ?? '');
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '';

    if (empty($name) || empty($message)) {
        $error = 'Please fill in your name and message.';
    } elseif (empty($mobile) && empty($email)) {
        $error = 'Please provide either a mobile number or email address so we can respond.';
    } else {
        $pdo = Database::getConnection();
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("
                    INSERT INTO `contacts` (`name`, `mobile`, `email`, `subject`, `message`, `status`, `ip_address`, `created_at`)
                    VALUES (:name, :mobile, :email, :subject, :message, 'NEW', :ip, NOW())
                ");
                $stmt->execute([
                    ':name' => $name,
                    ':mobile' => $mobile,
                    ':email' => $email,
                    ':subject' => $subject,
                    ':message' => $message,
                    ':ip' => $ip_address
                ]);
                $success = 'Thank you for reaching out! Your message has been received by our editorial and verification desk. We will get back to you shortly.';
            } catch (Exception $e) {
                $error = 'Unable to submit your message at this moment. Please try again or reach us via email.';
            }
        }
    }
}

include __DIR__ . '/header.php';
?>

<main class="py-5" style="background: #f8fafc; min-height: 85vh;">
    <div class="container">
        
        <!-- Hero Header -->
        <div class="text-center mb-5">
            <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Connect With Us</span>
            <h1 class="display-6 fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">Official Editorial & Support Desk</h1>
            <p class="text-muted mx-auto" style="max-width: 650px;">
                Have questions regarding constituency analytics, candidate profile updates, or advertising on Bihar's premier electoral portal? Our civic team is here to assist you.
            </p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 p-4 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-2 text-success me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">Message Sent Successfully!</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($success); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 p-4 shadow-sm mb-4" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-exclamation-triangle-fill fs-2 text-danger me-3"></i>
                    <div>
                        <h5 class="alert-heading fw-bold mb-1">Submission Error</h5>
                        <p class="mb-0"><?php echo htmlspecialchars($error); ?></p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Contact Form -->
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <h4 class="fw-bold text-dark mb-1" style="font-family: 'Outfit', sans-serif;">Send Us a Message</h4>
                    <p class="text-muted small mb-4">Fill in the details below and our team will get back to you within 24 business hours.</p>

                    <form method="POST" action="<?php echo SITE_URL; ?>/contact" id="contactForm">
                        <input type="hidden" name="submit_contact" value="1">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label for="name" class="form-label fw-bold small text-dark">Full Name <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-person text-muted"></i></span>
                                    <input type="text" class="form-control border-start-0" id="name" name="name" required placeholder="e.g. Ramesh Kumar">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="mobile" class="form-label fw-bold small text-dark">Mobile Number</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-telephone text-muted"></i></span>
                                    <input type="tel" class="form-control border-start-0" id="mobile" name="mobile" placeholder="e.g. 9876543210">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label for="email" class="form-label fw-bold small text-dark">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                                    <input type="email" class="form-control border-start-0" id="email" name="email" placeholder="name@example.com">
                                </div>
                            </div>

                            <div class="col-md-12">
                                <label for="subject" class="form-label fw-bold small text-dark">Inquiry Topic</label>
                                <select class="form-select" id="subject" name="subject">
                                    <option value="General Inquiry">General Civic Inquiry</option>
                                    <option value="Candidate Profile Update">Candidate / Leader Profile Claim & Verification</option>
                                    <option value="Panchayat / Mukhiya Record Correction">Panchayat / Mukhiya Record Update</option>
                                    <option value="Advertisement & Sponsorship">Advertisement & Sponsored Campaigns</option>
                                    <option value="Editorial & News Submission">Editorial Tip / Press Release</option>
                                    <option value="Technical Support">Website Feedback / Bug Report</option>
                                </select>
                            </div>

                            <div class="col-md-12">
                                <label for="message" class="form-label fw-bold small text-dark">Your Message / Request <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Please describe your query with relevant details (Constituency, District, or Candidate Name)..."></textarea>
                            </div>

                            <div class="col-md-12 mt-4">
                                <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold rounded-pill shadow-sm">
                                    <i class="bi bi-send-fill me-2"></i> Submit Message
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Column: Corporate & Civic Contact Hub -->
            <div class="col-lg-5">
                <div class="d-flex flex-column gap-4">
                    
                    <!-- Direct Contact Card -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-building me-2 text-danger"></i> Office & Communication
                        </h5>
                        
                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="bg-danger-subtle text-danger p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-envelope-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-semibold">Official Email</span>
                                <a href="mailto:biharelection.com@gmail.com" class="text-dark fw-bold text-decoration-none">biharelection.com@gmail.com</a>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3 mb-3">
                            <div class="bg-success-subtle text-success p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-whatsapp fs-5"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-semibold">WhatsApp Channel</span>
                                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="text-success fw-bold text-decoration-none">Join @BiharElection Alerts &rarr;</a>
                            </div>
                        </div>

                        <div class="d-flex align-items-start gap-3">
                            <div class="bg-primary-subtle text-primary p-3 rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                <i class="bi bi-geo-alt-fill fs-5"></i>
                            </div>
                            <div>
                                <span class="d-block text-muted small fw-semibold">Corporate Operator</span>
                                <p class="text-dark fw-bold mb-0 small">OfferPlant Technologies Private Limited</p>
                                <span class="text-muted small">Patna, Bihar, India</span>
                            </div>
                        </div>
                    </div>

                    <!-- Social Network Channels -->
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-share me-2 text-primary"></i> Follow Official Channels
                        </h5>
                        <p class="text-muted small mb-3">Join over 100,000+ voters and citizens receiving authentic daily electoral intelligence.</p>

                        <div class="row g-2">
                            <div class="col-6">
                                <a href="<?php echo FACEBOOK_URL; ?>" target="_blank" class="btn btn-outline-primary w-100 btn-sm text-start py-2 rounded-3 text-truncate">
                                    <i class="bi bi-facebook me-1"></i> Facebook
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo TWITTER_URL; ?>" target="_blank" class="btn btn-outline-dark w-100 btn-sm text-start py-2 rounded-3 text-truncate">
                                    <i class="bi bi-twitter-x me-1"></i> X / Twitter
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo INSTAGRAM_URL; ?>" target="_blank" class="btn btn-outline-danger w-100 btn-sm text-start py-2 rounded-3 text-truncate">
                                    <i class="bi bi-instagram me-1"></i> Instagram
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo YOUTUBE_URL; ?>" target="_blank" class="btn btn-outline-danger w-100 btn-sm text-start py-2 rounded-3 text-truncate">
                                    <i class="bi bi-youtube me-1"></i> YouTube
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-outline-success w-100 btn-sm text-start py-2 rounded-3 text-truncate">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                            <div class="col-6">
                                <a href="<?php echo TELEGRAM_URL; ?>" target="_blank" class="btn btn-outline-info w-100 btn-sm text-start py-2 rounded-3 text-truncate">
                                    <i class="bi bi-telegram me-1"></i> Telegram
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
