<?php
/**
 * BiharElection.com - Public User & Representative Profile
 * Modeled with comprehensive personal, electoral, and professional details.
 */
require_once __DIR__ . '/includes/auth_helper.php';

$handle = $_GET['handle'] ?? ($_GET['u'] ?? ($_GET['id'] ?? ''));

if (empty($handle)) {
    // If logged in, redirect to own profile or dashboard
    if (isUserLoggedIn()) {
        $currentUser = getCurrentUser();
        $handle = !empty($currentUser['username_handle']) ? $currentUser['username_handle'] : (string)$currentUser['id'];
    } else {
        header("Location: " . SITE_URL);
        exit;
    }
}

$user = getUserByHandle($handle);

if (!$user) {
    require_once __DIR__ . '/404.php';
    exit;
}

// Check Profile Visibility Permissions
$isLoggedIn = isUserLoggedIn();
$loggedInUser = $isLoggedIn ? getCurrentUser() : null;
$isOwner = $isLoggedIn && ((int)($loggedInUser['id'] ?? 0) === (int)$user['id']);
$isPrivate = ($user['profile_visibility'] ?? 'PUBLIC') === 'PRIVATE';

if ($isPrivate && !$isOwner) {
    $privateName = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['name']) ? $user['name'] : 'Citizen');
    $pageTitle = htmlspecialchars($privateName) . " — Private Profile | Bihar Election";
    require_once __DIR__ . '/header.php';
    echo '<div class="container py-5 text-center">
            <div class="card border-0 shadow-sm p-5 rounded-4 mx-auto my-5" style="max-width: 540px;">
                <div class="mb-3"><i class="bi bi-lock-fill display-4 text-warning"></i></div>
                <h3 class="fw-bold text-dark">' . htmlspecialchars($privateName) . '</h3>
                <p class="text-muted">This profile has been set to private by the owner.</p>
                <div><a href="' . SITE_URL . '" class="btn btn-primary rounded-pill px-4 fw-bold"><i class="bi bi-arrow-left me-1"></i> Back to BiharElection.com</a></div>
            </div>
          </div>';
    require_once __DIR__ . '/footer.php';
    exit;
}

// Increment Profile View Count
incrementUserProfileViews($user['id']);
$user['counter'] = (int)($user['counter'] ?? 0) + 1;

$displayName = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['name']) ? $user['name'] : 'Citizen');
$displayHandle = !empty($user['username_handle']) ? $user['username_handle'] : ('@' . slugify($displayName));
if (!str_starts_with($displayHandle, '@')) {
    $displayHandle = '@' . $displayHandle;
}

$pageTitle = htmlspecialchars($displayName) . " (" . htmlspecialchars($displayHandle) . ") — Public Profile | Bihar Election";
$pageDescription = "Official public profile of " . htmlspecialchars($displayName) . (!empty($user['designation']) ? " (" . htmlspecialchars($user['designation']) . ")" : "") . (!empty($user['district']) ? " in {$user['district']} District, Bihar." : " on BiharElection.com.");
$pageCanonical = SITE_URL . '/user/' . urlencode(ltrim($displayHandle, '@'));
$activeNav = 'profile';

require_once __DIR__ . '/header.php';
?>

<!-- Breadcrumb -->
<div class="bg-light py-3 border-bottom">
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>" class="text-decoration-none text-muted">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/representatives" class="text-decoration-none text-muted">People &amp; Representatives</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page"><?php echo htmlspecialchars($displayName); ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-4 py-lg-5">
    <div class="row g-4">
        
        <!-- Main Column (8 cols) -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
                
                <!-- Profile Banner Header -->
                <div class="p-4 p-md-5 text-white position-relative" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0b192c 100%);">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4 position-relative z-1">
                        
                        <!-- Avatar / Photo -->
                        <div class="flex-shrink-0 text-center">
                            <?php 
                            $photoPath = !empty($user['profile_image']) ? $user['profile_image'] : (!empty($user['profile_photo']) ? $user['profile_photo'] : ($user['photo'] ?? ''));
                            if (!empty($photoPath) && file_exists(__DIR__ . '/' . $photoPath)): ?>
                                <img src="<?php echo SITE_URL . '/' . htmlspecialchars($photoPath); ?>" alt="<?php echo htmlspecialchars($displayName); ?>" class="rounded-circle img-thumbnail shadow border-3 border-white" style="width: 120px; height: 120px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow border border-3 border-white mx-auto" style="width: 120px; height: 120px; font-size: 2.75rem; text-shadow: 1px 1px 3px rgba(0,0,0,0.3);">
                                    <?php echo strtoupper(substr($displayName, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Info & Badges -->
                        <div class="text-center text-md-start flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                                <h1 class="h2 fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif;"><?php echo htmlspecialchars($displayName); ?></h1>
                                
                                <?php if (!empty($user['role']) && in_array(strtolower($user['role']), ['representative', 'mla', 'mp', 'mukhiya', 'sarpanch', 'candidate', 'admin'])): ?>
                                    <span class="badge bg-warning text-dark fw-bold rounded-pill px-3 py-1 small">
                                        <i class="bi bi-award-fill me-1"></i> <?php echo htmlspecialchars(strtoupper($user['role'])); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-75 text-white rounded-pill px-2.5 py-1 small">
                                        <i class="bi bi-person-check-fill me-1"></i> <?php echo htmlspecialchars(ucfirst($user['role'] ?? 'Citizen')); ?>
                                    </span>
                                <?php endif; ?>

                                <?php if (($user['is_mobile_verified'] ?? 0) == 1 || ($user['mobile_status'] ?? '') === 'VERIFIED'): ?>
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success rounded-pill px-2.5 py-1 small" title="DLT Verified Mobile Number">
                                        <i class="bi bi-patch-check-fill me-1"></i> Verified
                                    </span>
                                <?php endif; ?>
                            </div>

                            <div class="text-warning fw-semibold fs-6 mb-2">
                                <?php echo htmlspecialchars($displayHandle); ?>
                            </div>

                            <?php if (!empty($user['designation'])): ?>
                                <div class="fs-6 fw-medium text-white-50 mb-1">
                                    <i class="bi bi-briefcase-fill me-1 text-warning"></i><?php echo htmlspecialchars($user['designation']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($user['business_name'])): ?>
                                <div class="small text-white-50 mb-2">
                                    <i class="bi bi-building me-1"></i><?php echo htmlspecialchars($user['business_name']); ?>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 mt-3 pt-2 border-top border-secondary border-opacity-25">
                                <?php if (!empty($user['district'])): ?>
                                    <div class="small text-white-50">
                                        <i class="bi bi-geo-alt-fill text-warning me-1"></i><?php echo htmlspecialchars($user['district']); ?><?php echo !empty($user['constituency']) ? ' (' . htmlspecialchars($user['constituency']) . ')' : ''; ?>, Bihar
                                    </div>
                                <?php endif; ?>
                                <div class="small text-white-50">
                                    <i class="bi bi-eye-fill text-info me-1"></i><?php echo number_format($user['counter']); ?> Profile Views
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Quick Action Contact Bar -->
                <div class="card-body p-3 p-md-4 bg-white border-bottom">
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        
                        <?php if (($user['mobile_visibility'] ?? 'PUBLIC') === 'PUBLIC' && !empty($user['mobile'])): ?>
                            <?php if ($isLoggedIn): ?>
                                <a href="tel:+91<?php echo htmlspecialchars($user['mobile']); ?>" class="btn btn-primary fw-bold rounded-pill px-3.5 py-2">
                                    <i class="bi bi-telephone-fill me-1.5"></i> Call +91 <?php echo htmlspecialchars($user['mobile']); ?>
                                </a>
                                
                                <?php $waNum = !empty($user['whatsapp']) ? $user['whatsapp'] : $user['mobile']; ?>
                                <a href="https://wa.me/91<?php echo htmlspecialchars($waNum); ?>?text=Hello%20<?php echo urlencode($displayName); ?>%2C%20I%20found%20your%20profile%20on%20BiharElection.com." target="_blank" class="btn btn-success fw-bold rounded-pill px-3.5 py-2">
                                    <i class="bi bi-whatsapp me-1.5"></i> WhatsApp
                                </a>
                            <?php else: ?>
                                <a href="<?php echo SITE_URL; ?>/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI'] ?? ''); ?>" class="btn btn-primary fw-bold rounded-pill px-3.5 py-2 shadow-sm">
                                    <i class="bi bi-lock-fill me-1.5"></i> Login to View Mobile Number
                                </a>
                            <?php endif; ?>
                        <?php endif; ?>

                        <?php if (($user['email_visibility'] ?? 'PUBLIC') === 'PUBLIC' && !empty($user['email'])): ?>
                            <a href="mailto:<?php echo htmlspecialchars($user['email']); ?>" class="btn btn-outline-secondary fw-semibold rounded-pill px-3.5 py-2">
                                <i class="bi bi-envelope-fill me-1.5"></i> Email
                            </a>
                        <?php endif; ?>

                        <button onclick="shareProfile()" class="btn btn-outline-primary fw-semibold rounded-pill px-3.5 py-2">
                            <i class="bi bi-share-fill me-1.5"></i> Share
                        </button>

                        <?php if ($isOwner): ?>
                            <a href="<?php echo SITE_URL; ?>/edit-profile.php" class="btn btn-warning text-dark fw-bold rounded-pill px-3.5 py-2 ms-md-auto">
                                <i class="bi bi-pencil-square me-1.5"></i> Edit Profile
                            </a>
                        <?php endif; ?>

                    </div>
                </div>

                <!-- Details & Bio Body -->
                <div class="card-body p-4 p-md-5">
                    
                    <!-- Bio & About Section -->
                    <?php if (!empty($user['bio']) || !empty($user['about'])): ?>
                        <div class="mb-5">
                            <h4 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                <i class="bi bi-person-lines-fill text-primary me-2"></i> About &amp; Biography
                            </h4>
                            <div class="text-secondary lead fs-6" style="line-height: 1.8;">
                                <?php echo nl2br(htmlspecialchars($user['bio'] ?: $user['about'])); ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Electoral & Regional Representation -->
                    <div class="mb-5">
                        <h4 class="fw-bold text-dark mb-3 border-bottom pb-2">
                            <i class="bi bi-geo-alt-fill text-primary me-2"></i> Electoral &amp; Geographic Location
                        </h4>
                        <div class="row g-3">
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">District / जिला</small>
                                    <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['district'] ?: 'Bihar (All Districts)'); ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Assembly (Vidhan Sabha)</small>
                                    <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['constituency'] ?: 'Not Specified'); ?></div>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="p-3 bg-light rounded-3 border">
                                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Panchayat / Local Ward</small>
                                    <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['panchayat'] ?: 'General Area'); ?></div>
                                </div>
                            </div>
                            <?php if (($user['address_visibility'] ?? 'PUBLIC') === 'PUBLIC' && (!empty($user['address']) || !empty($user['pincode']))): ?>
                                <div class="col-12">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Address / Office Location</small>
                                        <div class="fw-bold text-dark fs-6 mt-1">
                                            <?php echo htmlspecialchars($user['address'] ?? ''); ?>
                                            <?php echo !empty($user['pincode']) ? ' — PIN: ' . htmlspecialchars($user['pincode']) : ''; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Professional & Public Qualifications -->
                    <div class="mb-5">
                        <h4 class="fw-bold text-dark mb-3 border-bottom pb-2">
                            <i class="bi bi-briefcase-fill text-primary me-2"></i> Role &amp; Qualifications
                        </h4>
                        <div class="row g-3">
                            <?php if (!empty($user['profession_category'])): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Profession Category</small>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['profession_category']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['specialization'])): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Specialization / Focus Area</small>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['specialization']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['education'])): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Educational Qualification</small>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['education']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['experience_years'])): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Public / Work Experience</small>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['experience_years']); ?> Years</div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['languages'])): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Languages</small>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['languages']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($user['office_hours'])): ?>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.72rem;">Public Meeting / Office Hours</small>
                                        <div class="fw-bold text-dark fs-6 mt-1"><?php echo htmlspecialchars($user['office_hours']); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Social Profiles & Official Links -->
                    <?php if (!empty($user['twitter']) || !empty($user['facebook']) || !empty($user['instagram']) || !empty($user['linkedin']) || !empty($user['google_maps_link'])): ?>
                        <div>
                            <h4 class="fw-bold text-dark mb-3 border-bottom pb-2">
                                <i class="bi bi-globe text-primary me-2"></i> Verified Social Profiles
                            </h4>
                            <div class="d-flex flex-wrap gap-2 pt-1">
                                <?php if (!empty($user['twitter'])): ?>
                                    <a href="<?php echo htmlspecialchars($user['twitter']); ?>" target="_blank" class="btn btn-outline-dark rounded-pill px-3 py-1.5 small">
                                        <i class="bi bi-twitter-x me-1"></i> Twitter / X
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($user['facebook'])): ?>
                                    <a href="<?php echo htmlspecialchars($user['facebook']); ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-3 py-1.5 small">
                                        <i class="bi bi-facebook me-1"></i> Facebook
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($user['instagram'])): ?>
                                    <a href="<?php echo htmlspecialchars($user['instagram']); ?>" target="_blank" class="btn btn-outline-danger rounded-pill px-3 py-1.5 small">
                                        <i class="bi bi-instagram me-1"></i> Instagram
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($user['linkedin'])): ?>
                                    <a href="<?php echo htmlspecialchars($user['linkedin']); ?>" target="_blank" class="btn btn-outline-primary rounded-pill px-3 py-1.5 small">
                                        <i class="bi bi-linkedin me-1"></i> LinkedIn
                                    </a>
                                <?php endif; ?>
                                <?php if (!empty($user['google_maps_link'])): ?>
                                    <a href="<?php echo htmlspecialchars($user['google_maps_link']); ?>" target="_blank" class="btn btn-outline-success rounded-pill px-3 py-1.5 small">
                                        <i class="bi bi-geo-alt-fill me-1"></i> Google Maps Office
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

            </div>
        </div>

        <!-- Sidebar Column (4 cols) -->
        <div class="col-12 col-lg-4">
            
            <!-- Quick Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                <h5 class="fw-bold text-dark mb-3">
                    <i class="bi bi-shield-check text-success me-2"></i> Account Verification
                </h5>
                <ul class="list-unstyled mb-0 d-flex flex-column gap-3 small">
                    <li class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-fingerprint me-1.5 text-primary"></i> Profile ID</span>
                        <span class="fw-bold text-dark">#BE-<?php echo str_pad((string)$user['id'], 5, '0', STR_PAD_LEFT); ?></span>
                    </li>
                    <li class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-calendar3 me-1.5 text-primary"></i> Registered Since</span>
                        <span class="fw-semibold text-dark"><?php echo date('M Y', strtotime($user['created_at'] ?? 'now')); ?></span>
                    </li>
                    <li class="d-flex align-items-center justify-content-between pb-2 border-bottom">
                        <span class="text-muted"><i class="bi bi-patch-check-fill me-1.5 text-success"></i> Mobile Status</span>
                        <span class="badge bg-success bg-opacity-25 text-success fw-bold">DLT Verified</span>
                    </li>
                    <li class="d-flex align-items-center justify-content-between">
                        <span class="text-muted"><i class="bi bi-eye-fill me-1.5 text-info"></i> Public Visibility</span>
                        <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($user['profile_visibility'] ?? 'PUBLIC'); ?></span>
                    </li>
                </ul>
            </div>

            <!-- Regional Intelligence Links -->
            <?php if (!empty($user['district'])): ?>
                <?php $dObj = DataProvider::getDistrictBySlug($user['district']); ?>
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-dark text-white" style="background: linear-gradient(135deg, #0b192c 0%, #1e3a8a 100%);">
                    <h5 class="fw-bold text-white mb-2">
                        <i class="bi bi-building me-2 text-warning"></i> Regional Hubs
                    </h5>
                    <p class="small text-white-50 mb-3">Explore official electoral data, local representatives, and rosters for this area:</p>
                    <div class="d-flex flex-column gap-2">
                        <a href="<?php echo getDistrictUrl($user['district']); ?>" class="btn btn-outline-light btn-sm rounded-pill text-start px-3 py-2">
                            <i class="bi bi-geo-alt-fill me-2 text-warning"></i> <?php echo htmlspecialchars($user['district']); ?> District Hub
                        </a>
                        <a href="<?php echo SITE_URL; ?>/mukhiya/<?php echo slugify($user['district']); ?>" class="btn btn-outline-light btn-sm rounded-pill text-start px-3 py-2">
                            <i class="bi bi-people-fill me-2 text-info"></i> Mukhiya Directory
                        </a>
                        <a href="<?php echo SITE_URL; ?>/sarpanch/<?php echo slugify($user['district']); ?>" class="btn btn-outline-light btn-sm rounded-pill text-start px-3 py-2">
                            <i class="bi bi-hammer me-2 text-success"></i> Sarpanch Directory
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Share & QR Action Card -->
            <div class="card border-0 shadow-sm rounded-4 p-4 text-center">
                <div class="mb-2"><i class="bi bi-qr-code text-primary fs-1"></i></div>
                <h6 class="fw-bold text-dark mb-1">Share Public Profile</h6>
                <p class="small text-muted mb-3">Copy your direct public link to share on WhatsApp or Social Media.</p>
                <div class="input-group mb-2">
                    <input type="text" class="form-control form-control-sm text-center bg-light" id="shareUrlInput" value="<?php echo $pageCanonical; ?>" readonly>
                    <button class="btn btn-primary btn-sm" onclick="copyShareUrl()">
                        <i class="bi bi-clipboard me-1"></i> Copy
                    </button>
                </div>
                <div id="copySuccessMsg" class="small text-success fw-bold d-none">Link copied to clipboard!</div>
            </div>

        </div>

    </div>
</div>

<script>
function shareProfile() {
    const shareData = {
        title: <?php echo json_encode($pageTitle); ?>,
        text: <?php echo json_encode("Check out the official profile of " . $displayName . " on BiharElection.com"); ?>,
        url: <?php echo json_encode($pageCanonical); ?>
    };
    if (navigator.share) {
        navigator.share(shareData).catch(console.error);
    } else {
        copyShareUrl();
    }
}

function copyShareUrl() {
    const copyText = document.getElementById("shareUrlInput");
    copyText.select();
    copyText.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(copyText.value).then(() => {
        const msg = document.getElementById("copySuccessMsg");
        msg.classList.remove("d-none");
        setTimeout(() => msg.classList.add("d-none"), 3000);
    });
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
