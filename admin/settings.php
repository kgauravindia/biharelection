<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$message = '';
$error = '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Platform Settings — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Platform Configurations</h1>
                <p class="text-muted mb-0">Global site constants, contact information & official social channels.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sliders me-2 text-danger"></i> System Identity & URLs</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Platform Name</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(SITE_NAME); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Base URL</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(SITE_URL); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">Platform Tagline</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(SITE_TAGLINE); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-share-nodes me-2 text-primary"></i> Official Channels</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase"><i class="fab fa-whatsapp text-success me-1"></i> WhatsApp Channel</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(WHATSAPP_CHANNEL_URL); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase"><i class="fab fa-x-twitter me-1"></i> Twitter / X</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(TWITTER_URL); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase"><i class="fab fa-instagram text-danger me-1"></i> Instagram</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(INSTAGRAM_URL); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase"><i class="fab fa-youtube text-danger me-1"></i> YouTube Channel</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(YOUTUBE_URL); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card mb-4">
                    <div class="section-card-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-ad me-2 text-warning"></i> Google AdSense Monitization Slots</h6>
                        <span class="badge <?php echo (defined('GOOGLE_ADS_ENABLED') && GOOGLE_ADS_ENABLED) ? 'bg-success' : 'bg-secondary'; ?>">
                            <?php echo (defined('GOOGLE_ADS_ENABLED') && GOOGLE_ADS_ENABLED) ? 'Enabled' : 'Disabled'; ?>
                        </span>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">AdSense Publisher ID</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(defined('GOOGLE_ADSENSE_CLIENT') ? GOOGLE_ADSENSE_CLIENT : ''); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Header Leaderboard Slot</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(defined('GOOGLE_AD_SLOT_HEADER') ? GOOGLE_AD_SLOT_HEADER : ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">In-Feed Native Slot</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(defined('GOOGLE_AD_SLOT_INFEED') ? GOOGLE_AD_SLOT_INFEED : ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Sidebar Rectangle Slot</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(defined('GOOGLE_AD_SLOT_SIDEBAR') ? GOOGLE_AD_SLOT_SIDEBAR : ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-muted text-uppercase">Footer Banner Slot</label>
                                <input type="text" class="form-control bg-light" value="<?php echo htmlspecialchars(defined('GOOGLE_AD_SLOT_FOOTER') ? GOOGLE_AD_SLOT_FOOTER : ''); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <p class="small text-muted mb-0">
                                    <i class="fas fa-info-circle me-1 text-primary"></i>
                                    To update your live Google AdSense Publisher ID or Slot IDs, modify the <code>GOOGLE_ADSENSE_CLIENT</code> constants in <code>config.php</code>.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="section-card mb-4">
                    <div class="section-card-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-sms me-2 text-info"></i> SMS Gateway & DLT Template (OfferPlant)</h6>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Sender ID (DLT Header)</label>
                                <input type="text" class="form-control bg-light fw-bold" value="<?php echo htmlspecialchars(defined('SMS_SENDER_ID') ? SMS_SENDER_ID : 'BIHELE'); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">DLT Template Name</label>
                                <input type="text" class="form-control bg-light fw-bold" value="<?php echo htmlspecialchars(defined('SMS_TEMPLATE_NAME') ? SMS_TEMPLATE_NAME : 'BIHELE_OTP'); ?>" readonly>
                            </div>
                            <div class="col-12">
                                <label class="form-label small fw-bold text-muted text-uppercase">DLT Approved SMS Template Text</label>
                                <textarea class="form-control bg-light font-monospace small" rows="5" readonly><?php echo htmlspecialchars(defined('SMS_OTP_TEMPLATE') ? SMS_OTP_TEMPLATE : "Dear {#var#},\nYour OTP / EVC / Password is: {#var#}\nVisit https://biharelection.com\n  \nRegards\nBIHELE\nOfferPlant"); ?></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Gateway API Endpoint</label>
                                <input type="text" class="form-control bg-light small" value="<?php echo htmlspecialchars(defined('SMS_API_URL') ? SMS_API_URL : 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms'); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold text-muted text-uppercase">Gateway Auth Key</label>
                                <input type="text" class="form-control bg-light font-monospace" value="<?php echo htmlspecialchars(defined('SMS_AUTH_KEY') ? substr(SMS_AUTH_KEY, 0, 8) . '...' . substr(SMS_AUTH_KEY, -6) : 'b0e99bea...3c868'); ?>" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-database me-2 text-warning"></i> Database Environment</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="mb-3">
                            <span class="small text-muted text-uppercase fw-bold d-block">Host</span>
                            <span class="fw-bold text-dark"><?php echo htmlspecialchars(DB_HOST); ?></span>
                        </div>
                        <div class="mb-3">
                            <span class="small text-muted text-uppercase fw-bold d-block">Database Name</span>
                            <code><?php echo htmlspecialchars(DB_NAME); ?></code>
                        </div>
                        <div class="mb-3">
                            <span class="small text-muted text-uppercase fw-bold d-block">Database User</span>
                            <code><?php echo htmlspecialchars(DB_USER); ?></code>
                        </div>
                        <div class="border-top pt-3">
                            <a href="../install.php" target="_blank" class="btn btn-outline-danger btn-sm w-100 fw-bold">
                                <i class="fas fa-database me-1"></i> Run Migration / Seeder
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
