<?php
/**
 * BiharElection.com - Public User Dashboard & Profile Portal
 */
require_once __DIR__ . '/includes/auth_helper.php';
requireUserLogin();

$user = getCurrentUser();

$pageTitle = 'Citizen Dashboard — ' . htmlspecialchars($user['name'] ?? 'User') . ' | Bihar Election';
$pageDescription = 'Track daily contact views, monitor elected representatives, and access civic directories across Bihar.';
$pageCanonical = SITE_URL . '/dashboard.php';
$activeNav = 'dashboard';

// Fetch Phone Reveals Statistics
$userId = (int)($user['id'] ?? 0);
$todayReveals = getUserPhoneReveals($userId, 100, true);
$allReveals = getUserPhoneReveals($userId, 250, false);
$todayCount = count($todayReveals);
$totalCount = count($allReveals);
$dailyLimit = 10;
$todayRemaining = max(0, $dailyLimit - $todayCount);
$usagePercent = min(100, round(($todayCount / $dailyLimit) * 100));

require_once __DIR__ . '/header.php';
?>

<div class="dashboard-wrapper py-4 py-lg-5 bg-light">
    <div class="container">
        
        <!-- Welcome Hero Banner -->
        <?php 
        $handle = !empty($user['username_handle']) ? $user['username_handle'] : (string)($user['id'] ?? '1');
        $displayName = !empty($user['full_name']) ? $user['full_name'] : (!empty($user['name']) ? $user['name'] : 'Citizen');
        $publicProfileUrl = SITE_URL . '/user/' . urlencode(ltrim($handle, '@'));
        $comp = getProfileCompletionPercent($user);
        ?>
        <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0b1a30 0%, #17345f 100%);">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-inline-flex align-items-center gap-2 mb-2 flex-wrap">
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
                    <h1 class="display-6 fw-bold mb-1 font-heading">
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
                    <a href="edit-profile.php" class="btn btn-warning text-dark rounded-pill px-3 py-2 fw-bold shadow-sm">
                        <i class="bi bi-pencil-square me-1"></i> Edit Profile
                    </a>
                    <a href="logout.php" class="btn btn-outline-light rounded-pill px-3 py-2 fw-bold" title="Logout">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Daily Phone Reveal Quota Banner -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white border-top border-4 border-warning">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-7">
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-warning bg-opacity-15 text-dark fw-bold px-2.5 py-1 rounded-pill small">
                            🔒 Daily Contact Quota
                        </span>
                        <span class="text-muted small">Max 10 Numbers / Day</span>
                    </div>
                    <h4 class="fw-bold font-heading text-navy mb-1 fs-5">
                        Today's Contact Numbers Viewed: <span class="text-primary"><?php echo $todayCount; ?> / <?php echo $dailyLimit; ?></span>
                    </h4>
                    <p class="text-muted small mb-2">
                        <?php if ($todayRemaining > 0): ?>
                            You have <strong class="text-success"><?php echo $todayRemaining; ?> contact views remaining</strong> today. Your quota resets at 12:00 AM midnight.
                        <?php else: ?>
                            <strong class="text-danger">Daily quota reached (10/10).</strong> You can view your previously unlocked contacts below or wait for quota reset at midnight.
                        <?php endif; ?>
                    </p>
                    <div class="progress rounded-pill bg-light" style="height: 10px;">
                        <div class="progress-bar <?php echo $todayRemaining === 0 ? 'bg-danger' : ($todayCount > 7 ? 'bg-warning' : 'bg-success'); ?> progress-bar-striped progress-bar-animated" 
                             role="progressbar" 
                             style="width: <?php echo $usagePercent; ?>%;" 
                             aria-valuenow="<?php echo $usagePercent; ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="col-12 col-md-5 text-md-end">
                    <div class="d-inline-flex flex-column align-items-md-end gap-1">
                        <div class="d-flex align-items-center gap-2">
                            <div class="text-center p-2.5 bg-light rounded-3 border px-3">
                                <span class="text-muted d-block small" style="font-size: 0.72rem;">Today's Seen</span>
                                <strong class="fs-5 text-dark"><?php echo $todayCount; ?></strong>
                            </div>
                            <div class="text-center p-2.5 bg-light rounded-3 border px-3">
                                <span class="text-muted d-block small" style="font-size: 0.72rem;">All-Time Seen</span>
                                <strong class="fs-5 text-primary"><?php echo $totalCount; ?></strong>
                            </div>
                            <div class="text-center p-2.5 bg-light rounded-3 border px-3">
                                <span class="text-muted d-block small" style="font-size: 0.72rem;">Remaining</span>
                                <strong class="fs-5 <?php echo $todayRemaining > 0 ? 'text-success' : 'text-danger'; ?>"><?php echo $todayRemaining; ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            
            <!-- Left Main Column: Contacts Seen + Quick Directory Hub -->
            <div class="col-lg-8">
                
                <!-- ========================================================= -->
                <!-- TODAY'S SEEN & ALL SEEN CONTACTS DIRECTORY WIDGET        -->
                <!-- ========================================================= -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white" id="seenContactsCard">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill small">
                                    <i class="bi bi-telephone-fill"></i> Unlocked Contacts
                                </span>
                            </div>
                            <h4 class="fw-bold font-heading text-navy fs-5 mb-0">Representative Contacts You've Seen</h4>
                        </div>

                        <!-- Tab Switcher: Today's Seen vs All Seen -->
                        <ul class="nav nav-pills gap-1 bg-light p-1 rounded-pill border" id="seenContactsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill px-3 py-1 small fw-bold" id="today-seen-tab" data-bs-toggle="pill" data-bs-target="#today-seen-pane" type="button" role="tab" aria-controls="today-seen-pane" aria-selected="true">
                                    Today's Seen <span class="badge bg-primary rounded-pill ms-1"><?php echo $todayCount; ?></span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill px-3 py-1 small fw-bold" id="all-seen-tab" data-bs-toggle="pill" data-bs-target="#all-seen-pane" type="button" role="tab" aria-controls="all-seen-pane" aria-selected="false">
                                    All Seen <span class="badge bg-secondary rounded-pill ms-1"><?php echo $totalCount; ?></span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Search Filter in Seen Contacts -->
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0 rounded-start-pill ps-3"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" id="seenContactsSearchInput" class="form-control bg-light border-start-0 rounded-end-pill px-2" placeholder="Search by representative name, phone, or designation..." onkeyup="filterSeenContacts()">
                        </div>
                    </div>

                    <!-- Tab Contents -->
                    <div class="tab-content" id="seenContactsTabContent">
                        
                        <!-- TAB 1: TODAY'S SEEN CONTACTS -->
                        <div class="tab-pane fade show active" id="today-seen-pane" role="tabpanel" aria-labelledby="today-seen-tab" tabindex="0">
                            <?php if (empty($todayReveals)): ?>
                                <div class="text-center py-5 bg-light rounded-4 border border-light">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-telephone-x fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">No Contacts Viewed Today</h6>
                                    <p class="text-muted small mb-3" style="max-width: 440px; margin: 0 auto;">
                                        You haven't unmasked any representative contact numbers today. You have all <strong>10 views available</strong>.
                                    </p>
                                    <div class="d-flex justify-content-center flex-wrap gap-2">
                                        <a href="mla.php" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">Browse MLAs</a>
                                        <a href="mp.php" class="btn btn-sm btn-warning text-dark rounded-pill px-3 fw-bold">Browse MPs</a>
                                        <a href="<?php echo getPanchayatUrl(); ?>" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">Browse Panchayats</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="row g-2" id="todaySeenGrid">
                                    <?php foreach ($todayReveals as $rev): 
                                        $phoneClean = preg_replace('/[^0-9]/', '', (string)$rev['phone_number']);
                                        $timeFormatted = !empty($rev['created_at']) ? date('h:i A', strtotime($rev['created_at'])) : 'Today';
                                        $searchStr = strtolower(($rev['target_name'] ?? '') . ' ' . $phoneClean);
                                    ?>
                                        <div class="col-12 seen-contact-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>">
                                            <div class="card border rounded-4 p-3 bg-white hover-shadow transition-all d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                                        <i class="bi bi-telephone-check-fill fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-navy">
                                                            <?php echo htmlspecialchars($rev['target_name'] ?: 'Public Representative'); ?>
                                                        </h6>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap mt-0.5">
                                                            <span class="font-monospace fw-bold text-success small">+91 <?php echo htmlspecialchars($phoneClean); ?></span>
                                                            <span class="text-muted small">&bull; Seen at <?php echo htmlspecialchars($timeFormatted); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="tel:+91<?php echo htmlspecialchars($phoneClean); ?>" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                                                        <i class="bi bi-telephone-outbound-fill"></i> Call
                                                    </a>
                                                    <a href="https://wa.me/91<?php echo htmlspecialchars($phoneClean); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" title="WhatsApp Message">
                                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- TAB 2: ALL SEEN CONTACTS HISTORY -->
                        <div class="tab-pane fade" id="all-seen-pane" role="tabpanel" aria-labelledby="all-seen-tab" tabindex="0">
                            <?php if (empty($allReveals)): ?>
                                <div class="text-center py-5 bg-light rounded-4 border border-light">
                                    <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                                        <i class="bi bi-clock-history fs-3"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">No Contact History Found</h6>
                                    <p class="text-muted small mb-0">Your unlocked contact numbers will appear here permanently for quick access.</p>
                                </div>
                            <?php else: ?>
                                <div class="row g-2" id="allSeenGrid">
                                    <?php foreach ($allReveals as $rev): 
                                        $phoneClean = preg_replace('/[^0-9]/', '', (string)$rev['phone_number']);
                                        $dateFormatted = !empty($rev['created_at']) ? date('d M Y, h:i A', strtotime($rev['created_at'])) : ($rev['revealed_date'] ?? 'Recent');
                                        $searchStr = strtolower(($rev['target_name'] ?? '') . ' ' . $phoneClean);
                                    ?>
                                        <div class="col-12 seen-contact-item" data-search="<?php echo htmlspecialchars($searchStr, ENT_QUOTES); ?>">
                                            <div class="card border rounded-4 p-3 bg-white hover-shadow transition-all d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 42px; height: 42px;">
                                                        <i class="bi bi-person-lines-fill fs-5"></i>
                                                    </div>
                                                    <div>
                                                        <h6 class="fw-bold mb-0 text-navy">
                                                            <?php echo htmlspecialchars($rev['target_name'] ?: 'Public Representative'); ?>
                                                        </h6>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap mt-0.5">
                                                            <span class="font-monospace fw-bold text-success small">+91 <?php echo htmlspecialchars($phoneClean); ?></span>
                                                            <span class="text-muted small">&bull; <?php echo htmlspecialchars($dateFormatted); ?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="tel:+91<?php echo htmlspecialchars($phoneClean); ?>" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                                                        <i class="bi bi-telephone-outbound-fill"></i> Call
                                                    </a>
                                                    <a href="https://wa.me/91<?php echo htmlspecialchars($phoneClean); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" title="WhatsApp Message">
                                                        <i class="bi bi-whatsapp"></i> WhatsApp
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>

                <script>
                function filterSeenContacts() {
                    const query = document.getElementById('seenContactsSearchInput').value.toLowerCase().trim();
                    const items = document.querySelectorAll('.seen-contact-item');

                    items.forEach(item => {
                        const itemSearch = item.getAttribute('data-search').toLowerCase();
                        if (query === '' || itemSearch.includes(query)) {
                            item.classList.remove('d-none');
                        } else {
                            item.classList.add('d-none');
                        }
                    });
                }
                </script>

                <!-- Quick Access Directories -->
                <h4 class="h5 fw-bold mb-3 text-dark font-heading">
                    <i class="bi bi-grid-fill text-primary me-2"></i> Civic Intelligence &amp; Directories
                </h4>

                <div class="row g-3 mb-4">
                    
                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-primary bg-opacity-10 p-3 text-primary fs-3">
                                    🗳️
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Vidhan Sabha (MLA)</h6>
                                    <p class="small text-muted mb-2">243 Constituencies &amp; MLA roster</p>
                                    <a href="mla.php" class="btn btn-sm btn-outline-primary rounded-pill fw-bold">Explore MLAs &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-warning bg-opacity-10 p-3 text-warning fs-3">
                                    🏛️
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Lok Sabha &amp; Rajya Sabha (MP)</h6>
                                    <p class="small text-muted mb-2">40 LS MPs &amp; 15 RS Members</p>
                                    <a href="mp.php" class="btn btn-sm btn-outline-dark rounded-pill fw-bold">Explore MPs &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-success bg-opacity-10 p-3 text-success fs-3">
                                    🌾
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Panchayati Raj Hub</h6>
                                    <p class="small text-muted mb-2">8,400+ Mukhiya &amp; Sarpanch records</p>
                                    <a href="panchayat.php" class="btn btn-sm btn-outline-success rounded-pill fw-bold">View Panchayats &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white hover-shadow transition">
                            <div class="d-flex align-items-center gap-3">
                                <div class="rounded-3 bg-info bg-opacity-10 p-3 text-info fs-3">
                                    🏢
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-1">Zila Parishad Boards</h6>
                                    <p class="small text-muted mb-2">38 District Boards &amp; Ward Members</p>
                                    <a href="zila-parishad.php" class="btn btn-sm btn-outline-info text-dark rounded-pill fw-bold">View Boards &rarr;</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- Right Sidebar Column: Citizen Profile Summary & Broadcast -->
            <div class="col-lg-4">
                
                <!-- Citizen Account Summary Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-dark font-heading">
                            <i class="bi bi-person-badge text-primary me-2"></i> Citizen Account
                        </h5>
                        <a href="edit-profile.php" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-semibold small">
                            <i class="bi bi-pencil"></i> Edit
                        </a>
                    </div>

                    <div class="bg-light rounded-3 p-3 mb-3 border border-light">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Name:</span>
                            <strong class="text-navy small"><?php echo htmlspecialchars($displayName); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Mobile:</span>
                            <span class="font-monospace fw-bold text-dark small">+91 <?php echo htmlspecialchars(maskMobileNumber($user['mobile'] ?? '')); ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Citizen Role:</span>
                            <span class="badge bg-primary-subtle text-primary fw-bold text-uppercase" style="font-size: 0.72rem;">
                                <?php echo htmlspecialchars($user['role'] ?? 'Voter'); ?>
                            </span>
                        </div>
                        <?php if (!empty($user['district'])): ?>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="text-muted small">Home District:</span>
                                <strong class="text-dark small"><?php echo htmlspecialchars($user['district']); ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($user['email'])): ?>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Email:</span>
                                <span class="text-truncate text-muted small" style="max-width: 150px;"><?php echo htmlspecialchars($user['email']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="edit-profile.php" class="btn btn-warning text-dark fw-bold rounded-pill shadow-sm">
                            <i class="bi bi-pencil-square me-1"></i> Update Profile Details
                        </a>
                        <a href="<?php echo $publicProfileUrl; ?>" class="btn btn-outline-secondary rounded-pill fw-semibold">
                            <i class="bi bi-box-arrow-up-right me-1"></i> View Public Card
                        </a>
                    </div>
                </div>

                <!-- Civic Resource Quick Links -->
                <div class="card border-0 shadow-sm rounded-4 p-4 bg-white mb-4">
                    <h6 class="fw-bold text-navy font-heading mb-3">
                        <i class="bi bi-link-45deg text-primary me-1"></i> Civic Quick Tools
                    </h6>
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2 small">
                        <li>
                            <a href="<?php echo SITE_URL; ?>/census" class="text-decoration-none text-dark d-flex align-items-center justify-content-between p-2 rounded-3 bg-light hover-bg">
                                <span><i class="bi bi-bar-chart-fill text-primary me-2"></i> Bihar Census Hub</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/mlc" class="text-decoration-none text-dark d-flex align-items-center justify-content-between p-2 rounded-3 bg-light hover-bg">
                                <span><i class="bi bi-award-fill text-warning me-2"></i> Vidhan Parishad (75 MLCs)</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                        <li>
                            <a href="<?php echo SITE_URL; ?>/panchayat-samiti" class="text-decoration-none text-dark d-flex align-items-center justify-content-between p-2 rounded-3 bg-light hover-bg">
                                <span><i class="bi bi-people-fill text-success me-2"></i> Block Pramukh &amp; Samiti</span>
                                <i class="bi bi-chevron-right text-muted"></i>
                            </a>
                        </li>
                    </ul>
                </div>

                <!-- WhatsApp Broadcast Widget -->
                <div class="card border-0 shadow-sm rounded-4 p-4 text-white" style="background: linear-gradient(135deg, #128c7e 0%, #075e54 100%);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="bi bi-whatsapp fs-3 text-warning"></i>
                        <h6 class="fw-bold mb-0">Daily WhatsApp Broadcast</h6>
                    </div>
                    <p class="small text-white-50 mb-3">
                        Get election rosters, candidate affidavits &amp; voter digests 3 times a day.
                    </p>
                    <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-light btn-sm rounded-pill fw-bold text-dark w-100 shadow-sm">
                        <i class="bi bi-whatsapp text-success me-1"></i> Join WhatsApp Channel
                    </a>
                </div>
            </div>

        </div>

    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
