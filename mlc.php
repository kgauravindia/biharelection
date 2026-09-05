<?php
require_once __DIR__ . '/config.php';

$requestedSlug = trim($_GET['slug'] ?? $_GET['id'] ?? '');
$singleMlc = null;

if (!empty($requestedSlug)) {
    $pdo = Database::getConnection();
    if ($pdo) {
        $stmt = $pdo->prepare("
            SELECT * FROM mlcs 
            WHERE id = :id_val 
               OR sr_no = :sr_val 
               OR profile_url LIKE :prof_val
            ORDER BY id ASC 
            LIMIT 1
        ");
        $stmt->execute([
            ':id_val' => is_numeric($requestedSlug) ? (int)$requestedSlug : 0,
            ':sr_val' => is_numeric($requestedSlug) ? (int)$requestedSlug : 0,
            ':prof_val' => '%/Profile/' . $requestedSlug
        ]);
        $singleMlc = $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

// If slug is provided but not found, redirect to main MLC list
if (!empty($requestedSlug) && !$singleMlc) {
    header("Location: " . SITE_URL . "/mlc", true, 302);
    exit;
}

function getMlcQuotaType($constituency) {
    $const = (string)$constituency;
    if (stripos($const, 'Local') !== false || stripos($const, 'प्राधिकार') !== false || stripos($const, 'L.B') !== false) {
        return 'Local';
    } elseif (stripos($const, 'Nominated') !== false || stripos($const, 'मनोनीत') !== false) {
        return 'Nominated';
    } elseif (stripos($const, 'Graduate') !== false || stripos($const, 'स्नातक') !== false) {
        return 'Graduates';
    } elseif (stripos($const, 'Teacher') !== false || stripos($const, 'शिक्षक') !== false) {
        return 'Teachers';
    }
    return 'Assembly';
}

function getMlcQuotaLabel($quotaType) {
    switch ($quotaType) {
        case 'Local': return 'Local Authorities (स्थानीय प्राधिकार)';
        case 'Assembly': return 'Assembly Quota (विधान सभा)';
        case 'Nominated': return 'Governor Nominated (मनोनीत)';
        case 'Graduates': return 'Graduates (स्नातक)';
        case 'Teachers': return 'Teachers (शिक्षक)';
        default: return 'Legislative Council';
    }
}

$activeNav = 'mlc';
$mlcs = DataProvider::getMlcs();

// Pre-calculate quota counts & party breakdown
$quotaCounts = [
    'All' => count($mlcs),
    'Local' => 0,
    'Assembly' => 0,
    'Nominated' => 0,
    'Graduates' => 0,
    'Teachers' => 0
];
$partyCounts = [];

foreach ($mlcs as $m) {
    $qType = getMlcQuotaType($m['constituency'] ?? '');
    $quotaCounts[$qType]++;

    $party = trim($m['party'] ?? 'Other');
    if ($party) {
        $partyCounts[$party] = ($partyCounts[$party] ?? 0) + 1;
    }
}
arsort($partyCounts);

if ($singleMlc) {
    $mName = (string)($singleMlc['name'] ?? '');
    $mNameHi = (string)($singleMlc['name_hi'] ?? '');
    $const = (string)($singleMlc['constituency'] ?? '');
    $party = (string)($singleMlc['party'] ?? '');
    $partyFull = (string)($singleMlc['party_full'] ?? '');
    $desig = (string)($singleMlc['designation'] ?? '');
    $phone = (string)($singleMlc['contact'] ?? '');
    $email = (string)($singleMlc['email'] ?? '');
    $image = (string)($singleMlc['image'] ?? '');
    $profileUrl = (string)($singleMlc['profile_url'] ?? '');
    $dob = (string)($singleMlc['dob'] ?? '');
    $address = (string)($singleMlc['address'] ?? '');
    $term = (string)($singleMlc['tenure'] ?? 'Active 6-Year Term');

    $quotaType = getMlcQuotaType($const);
    $quotaLabel = getMlcQuotaLabel($quotaType);
    $partyCleanClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $party));

    $pageTitle = "{$mName}" . ($mNameHi ? " ({$mNameHi})" : "") . " MLC Profile - Bihar Vidhan Parishad";
    $pageDescription = "{$mName} is a Member of the Bihar Legislative Council (MLC) representing {$const} ({$party}). View constituency, quota, party, verified contact details, and official Vidhan Parishad secretariat profile.";
    $pageCanonical = SITE_URL . "/mlc/" . ($singleMlc['id'] ?? $singleMlc['sr_no']);
} else {
    $pageTitle = "Bihar 75 Vidhan Parishad MLCs Directory: Legislative Council Members Official Roster";
    $pageDescription = "Official directory of all 75 Bihar Legislative Council (Vidhan Parishad) Members (MLCs): Local Authorities (24), Assembly Quota (27), Governor Nominated (12), Graduates (6), and Teachers (6) with verified contact details.";
    $pageCanonical = SITE_URL . "/mlc";
}

require_once __DIR__ . '/header.php';
?>

<style>
.mlc-avatar-img {
    width: 50px;
    height: 50px;
    object-fit: cover;
    border-radius: 50%;
    border: 2px solid #e2e8f0;
    background-color: #f8fafc;
    flex-shrink: 0;
}
.mlc-profile-photo {
    width: 140px;
    height: 140px;
    object-fit: cover;
    border-radius: 50%;
    border: 4px solid #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    background-color: #f8fafc;
}
.quota-metric-card {
    background: #ffffff;
    border-radius: 18px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 4px 14px rgba(11, 25, 44, 0.05);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    cursor: pointer;
    text-decoration: none !important;
}
.quota-metric-card:hover, .quota-metric-card.active {
    transform: translateY(-5px);
    box-shadow: 0 16px 32px -6px rgba(30, 64, 175, 0.16);
    border-color: #3b82f6;
}
.quota-btn {
    border-radius: 50px !important;
    font-weight: 600;
    font-size: 0.825rem;
    padding: 6px 16px;
    transition: all 0.2s ease;
}
.quota-btn.active {
    background: linear-gradient(135deg, #2563eb, #1d4ed8) !important;
    color: #ffffff !important;
    border-color: #2563eb !important;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
}
.profile-detail-card {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 1.25rem;
    transition: all 0.2s ease;
}
.profile-detail-card:hover {
    background-color: #ffffff;
    border-color: #93c5fd;
    box-shadow: 0 8px 20px rgba(37, 99, 235, 0.08);
}
</style>

<?php if ($singleMlc): ?>
<!-- ========================================================================= -->
<!-- SINGLE MLC PROFILE VIEW                                                  -->
<!-- ========================================================================= -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/representatives" class="text-white-50 text-decoration-none">Representatives</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/mlc" class="text-white-50 text-decoration-none">Vidhan Parishad (MLCs)</a></li>
                <li class="breadcrumb-item active text-warning fw-bold text-truncate" style="max-width: 200px;" aria-current="page"><?php echo htmlspecialchars($mName); ?></li>
            </ol>
        </nav>

        <div class="d-flex flex-column flex-md-row align-items-md-center gap-4">
            <div class="flex-shrink-0">
                <?php if (!empty($image)): ?>
                    <img src="<?php echo htmlspecialchars($image); ?>" 
                         alt="<?php echo htmlspecialchars($mName); ?>" 
                         class="mlc-profile-photo"
                         loading="eager"
                         onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($mName); ?>&background=0b1a30&color=fff&size=200';">
                <?php else: ?>
                    <div class="mlc-profile-photo d-flex align-items-center justify-content-center text-secondary fw-bold bg-white fs-1">
                        <i class="bi bi-person-fill"></i>
                    </div>
                <?php endif; ?>
            </div>

            <div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge bg-info text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                        📜 Bihar Vidhan Parishad
                    </span>
                    <span class="badge bg-white bg-opacity-25 text-white fw-semibold px-3 py-1.5 rounded-pill">
                        <?php echo htmlspecialchars($quotaType); ?> Quota
                    </span>
                    <?php if (!empty($desig)): ?>
                        <span class="badge bg-danger text-white fw-bold px-3 py-1.5 rounded-pill shadow-sm">
                            <?php echo htmlspecialchars($desig); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <h1 class="display-6 fw-extrabold text-white mb-1">
                    <?php echo htmlspecialchars($mName); ?>
                </h1>
                <?php if (!empty($mNameHi)): ?>
                    <div class="h4 text-warning fw-bold mb-2">
                        <?php echo htmlspecialchars($mNameHi); ?>
                    </div>
                <?php endif; ?>
                <p class="lead text-white-50 mb-0" style="font-size: 1.05rem;">
                    Member of Legislative Council (MLC) &bull; <strong><?php echo htmlspecialchars($const); ?></strong>
                </p>
            </div>
        </div>
    </div>
</section>

<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <div class="row g-4">
        <!-- Main Details Column -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h4 class="fw-bold text-navy mb-4 d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary"></i> Member Electoral &amp; Personal Details
                </h4>

                <div class="row g-3">
                    <!-- Constituency / क्षेत्र -->
                    <div class="col-md-6">
                        <div class="profile-detail-card h-100">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> Constituency / क्षेत्र
                            </span>
                            <div class="h5 fw-bold text-navy mb-1"><?php echo htmlspecialchars($const); ?></div>
                            <span class="badge bg-info bg-opacity-15 text-dark border small px-2.5 py-1">
                                <?php echo htmlspecialchars($quotaType); ?> Quota
                            </span>
                        </div>
                    </div>

                    <!-- Political Party / दल -->
                    <div class="col-md-6">
                        <div class="profile-detail-card h-100">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                <i class="bi bi-flag-fill text-primary me-1"></i> Political Party / दल
                            </span>
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span class="badge-party <?php echo $partyCleanClass; ?> fs-6 px-3 py-1">
                                    <?php echo htmlspecialchars($party); ?>
                                </span>
                            </div>
                            <?php if (!empty($partyFull)): ?>
                                <small class="text-muted"><?php echo htmlspecialchars($partyFull); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Date of Birth / जन्म तिथि -->
                    <div class="col-md-6">
                        <div class="profile-detail-card h-100">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                <i class="bi bi-calendar-event text-warning me-1"></i> Date of Birth / जन्म तिथि
                            </span>
                            <div class="h6 fw-bold text-navy mb-0">
                                <?php echo !empty($dob) ? htmlspecialchars($dob) : '<span class="text-muted fw-normal">Not recorded</span>'; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Email Address / ईमेल -->
                    <div class="col-md-6">
                        <div class="profile-detail-card h-100">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                <i class="bi bi-envelope-fill text-success me-1"></i> Email Address / ईमेल
                            </span>
                            <div class="h6 fw-bold text-navy font-monospace mb-0">
                                <?php if (!empty($email)): ?>
                                    <span><?php echo htmlspecialchars(maskEmailAddress($email)); ?></span>
                                <?php else: ?>
                                    <span class="text-muted fw-normal">Not specified</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Number / संपर्क नंबर -->
                    <div class="col-md-6">
                        <div class="profile-detail-card h-100">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                <i class="bi bi-telephone-fill text-primary me-1"></i> Contact Number / संपर्क
                            </span>
                            <div class="mt-1">
                                <?php if (!empty($phone)): ?>
                                    <?php echo renderMaskedPhoneButton($phone, $mName); ?>
                                <?php else: ?>
                                    <span class="text-muted small">Vidhan Parishad Secretariat</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Tenure & House / सदन -->
                    <div class="col-md-6">
                        <div class="profile-detail-card h-100">
                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                <i class="bi bi-bank text-info me-1"></i> Chamber &amp; Term
                            </span>
                            <div class="h6 fw-bold text-navy mb-0"><?php echo htmlspecialchars($term); ?></div>
                            <small class="text-muted">Bihar Legislative Council (Permanent House)</small>
                        </div>
                    </div>

                    <!-- Official Address / आवास पता -->
                    <?php if (!empty($address)): ?>
                        <div class="col-12">
                            <div class="profile-detail-card">
                                <span class="text-muted small fw-bold text-uppercase d-block mb-1">
                                    <i class="bi bi-house-door-fill text-secondary me-1"></i> Residential / Official Address
                                </span>
                                <div class="fw-semibold text-dark"><?php echo htmlspecialchars($address); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Official Vidhan Parishad Comprehensive Legislative Dossier Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <div>
                        <h5 class="fw-bold text-navy mb-0">
                            <i class="bi bi-file-earmark-person-fill text-primary me-2"></i> Legislative Dossier &amp; House Activity
                        </h5>
                        <small class="text-muted">Know more about official legislative performance &amp; council disclosures</small>
                    </div>
                    <span class="badge bg-info bg-opacity-10 text-dark border px-3 py-1.5 rounded-pill small">
                        Bihar Vidhan Parishad Portal
                    </span>
                </div>

                <p class="text-muted small mb-4">
                    To access verified official records including <strong>Personal Information</strong>, <strong>Questions Asked</strong>, <strong>Notices Moved</strong>, <strong>Asset Disclosures</strong>, <strong>Committee Memberships</strong>, <strong>House Debates</strong>, and <strong>Bills Introduced</strong>, visit the official Bihar Legislative Council member record.
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-primary mb-1"><i class="bi bi-person-vcard"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">Personal Information</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">Education &amp; Background</small>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-success mb-1"><i class="bi bi-question-circle"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">Questions</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">Starred &amp; Unstarred Questions</small>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-warning mb-1"><i class="bi bi-megaphone"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">Notices</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">Calling Attention &amp; Motions</small>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-info mb-1"><i class="bi bi-cash-stack"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">Asset Disclosures</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">Property &amp; Liabilities Record</small>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-danger mb-1"><i class="bi bi-people"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">Committees</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">House &amp; Standing Committees</small>
                        </div>
                    </div>

                    <div class="col-sm-6 col-md-4">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-secondary mb-1"><i class="bi bi-mic"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">Debates &amp; Speeches</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">Official Floor Transcripts</small>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-light border text-center h-100 d-flex flex-column justify-content-center">
                            <div class="fs-4 text-primary mb-1"><i class="bi bi-journal-text"></i></div>
                            <div class="fw-bold text-navy small mb-0.5">BILLS &amp; Legislation</div>
                            <small class="text-muted extra-small" style="font-size: 0.72rem;">Statutory Bills, Resolutions &amp; Voting Record</small>
                        </div>
                    </div>
                </div>

                <!-- External Portal CTA Button -->
                <?php if (!empty($profileUrl)): ?>
                    <div class="p-3.5 rounded-4 bg-gradient text-white d-flex flex-column flex-md-row align-items-center justify-content-between gap-3" style="background: linear-gradient(135deg, #0b1a30 0%, #1e3a8a 100%);">
                        <div>
                            <div class="fw-bold fs-6 mb-0.5">Know More on Official Vidhan Parishad Portal</div>
                            <small class="text-white-50">View complete personal background, questions, notices, assets, committees, debates, and bills.</small>
                        </div>
                        <a href="<?php echo htmlspecialchars($profileUrl); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-warning btn-lg fw-bold rounded-pill px-4 py-2.5 shadow text-dark text-nowrap d-inline-flex align-items-center gap-2">
                            <span>Visit Official Portal</span>
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Column -->
        <div class="col-lg-4">
            <!-- Council Summary Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h5 class="fw-bold text-navy mb-3">
                    <i class="bi bi-building text-primary me-2"></i> Bihar Legislative Council
                </h5>
                <ul class="list-unstyled small mb-4">
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Total Strength</span>
                        <strong class="text-navy">75 Members</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Local Authorities</span>
                        <strong class="text-navy">24 Seats</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Elected by MLAs</span>
                        <strong class="text-navy">27 Seats</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Governor Nominated</span>
                        <strong class="text-navy">12 Seats</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Graduates Quota</span>
                        <strong class="text-navy">6 Seats</strong>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted">Teachers Quota</span>
                        <strong class="text-navy">6 Seats</strong>
                    </li>
                </ul>

                <a href="<?php echo SITE_URL; ?>/mlc" class="btn btn-outline-primary rounded-pill w-100 fw-bold py-2 mb-2">
                    <i class="bi bi-arrow-left me-1"></i> View All 75 MLCs
                </a>
                <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-light border rounded-pill w-100 fw-semibold py-2">
                    <i class="bi bi-person-badge me-1"></i> 243 MLAs Directory
                </a>
            </div>

            <!-- Other Council Members -->
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                <h6 class="fw-bold text-navy mb-3">
                    <i class="bi bi-people-fill text-info me-2"></i> Other Council Members
                </h6>
                <div class="d-flex flex-column gap-2.5">
                    <?php 
                    $otherMlcs = array_filter($mlcs, fn($x) => ($x['id'] ?? $x['sr_no']) != ($singleMlc['id'] ?? $singleMlc['sr_no']));
                    $otherSample = array_slice($otherMlcs, 0, 5);
                    foreach ($otherSample as $om):
                        $omId = $om['id'] ?? $om['sr_no'];
                        $omName = $om['name'] ?? '';
                        $omConst = $om['constituency'] ?? '';
                        $omParty = $om['party'] ?? '';
                        $omImg = $om['image'] ?? '';
                    ?>
                        <a href="<?php echo SITE_URL . '/mlc/' . $omId; ?>" class="text-decoration-none text-dark p-2 rounded-3 hover-bg-light d-flex align-items-center gap-2.5 border border-light">
                            <?php if (!empty($omImg)): ?>
                                <img src="<?php echo htmlspecialchars($omImg); ?>" alt="<?php echo htmlspecialchars($omName); ?>" class="rounded-circle" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($omName); ?>';">
                            <?php else: ?>
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="bi bi-person text-secondary"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-grow-1 text-truncate">
                                <div class="fw-bold text-navy text-truncate small"><?php echo htmlspecialchars($omName); ?></div>
                                <div class="text-muted extra-small text-truncate" style="font-size: 0.72rem;"><?php echo htmlspecialchars($omConst); ?> &bull; <?php echo htmlspecialchars($omParty); ?></div>
                            </div>
                            <i class="bi bi-chevron-right text-muted small"></i>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php renderGoogleAd('sidebar', GOOGLE_AD_SLOT_SIDEBAR, 'mb-4'); ?>
        </div>
    </div>
</main>

<?php else: ?>
<!-- ========================================================================= -->
<!-- ALL 75 MLCs DIRECTORY ROSTER TABLE VIEW                                  -->
<!-- ========================================================================= -->
<!-- Hero Banner -->
<section class="hero-section py-4 py-lg-5">
    <div class="container text-start">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <span class="badge bg-info text-dark fw-bold px-3 py-2 rounded-pill shadow-sm">
                📜 Bihar Legislative Council (विधान परिषद)
            </span>
            <span class="badge bg-white bg-opacity-25 text-white fw-bold px-3 py-2 rounded-pill">
                75 Total MLCs
            </span>
            <span class="badge bg-warning bg-opacity-25 text-warning fw-bold px-3 py-2 rounded-pill">
                Permanent Upper Chamber
            </span>
        </div>

        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb bg-white bg-opacity-10 px-3 py-2 rounded-pill mb-0 small border border-white border-opacity-10 d-inline-flex">
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-white-50 text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/representatives" class="text-white-50 text-decoration-none">Representatives</a></li>
                <li class="breadcrumb-item active text-warning fw-bold" aria-current="page">Vidhan Parishad (MLCs)</li>
            </ol>
        </nav>

        <h1 class="display-6 fw-extrabold text-white mb-2">
            Bihar Vidhan Parishad (MLCs) Directory <br>
            <span style="color: var(--accent-saffron);">75 Members of Bihar Legislative Council</span>
        </h1>
        <p class="lead text-white-50 mb-4" style="font-size: 1.05rem; max-width: 850px;">
            Official directory of all 75 Members of the Bihar Legislative Council (विधान परिषद) sourced from the official Secretariat records. Covers Local Authorities, Graduates, Teachers, Assembly-elected, and Governor-nominated quotas.
        </p>

        <div class="d-flex flex-wrap gap-2">
            <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-warning fw-bold px-3 py-2 text-dark shadow-sm">
                <i class="bi bi-person-badge me-1"></i> 243 MLAs Directory
            </a>
            <a href="<?php echo SITE_URL; ?>/mp" class="btn btn-primary fw-bold px-3 py-2 shadow-sm">
                <i class="bi bi-bank me-1"></i> 40 Lok Sabha MPs
            </a>
            <a href="https://vidhanparishad.bihar.gov.in/Member_Details_List" target="_blank" class="btn btn-outline-light fw-bold px-3 py-2 shadow-sm">
                <i class="bi bi-box-arrow-up-right me-1"></i> Official Portal
            </a>
            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success fw-bold px-3 py-2 d-inline-flex align-items-center gap-1 shadow-sm">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp Alerts
            </a>
        </div>
    </div>
</section>

<!-- Main Container -->
<main class="container my-4 my-lg-5">
    <?php renderGoogleAd('leaderboard', GOOGLE_AD_SLOT_HEADER, 'mb-4'); ?>

    <!-- Highlight 5-Quota Command Metrics -->
    <div class="row g-2 g-md-3 row-cols-2 row-cols-md-3 row-cols-xl-5 mb-4">
        <!-- 1: Local Authorities -->
        <div class="col">
            <div class="quota-metric-card p-3 h-100 d-flex flex-column justify-content-between" onclick="filterQuota('Local', document.querySelector('[data-quota=\'Local\']'))">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="stat-icon-wrapper stat-icon-zp">
                        🏛️
                    </div>
                    <span class="badge bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25 extra-small fw-bold px-2 py-0.5 rounded-pill" style="color: #7c3aed; background: rgba(124,58,237,0.1);">
                        Local Bodies
                    </span>
                </div>
                <div>
                    <div class="fs-4 fw-extrabold text-navy mb-0 lh-1"><?php echo $quotaCounts['Local']; ?> MLCs</div>
                    <div class="fw-bold text-dark small mt-1 text-truncate">Local Authorities</div>
                    <div class="extra-small text-muted text-truncate">Panchayat &amp; ULB Electors</div>
                </div>
            </div>
        </div>

        <!-- 2: Assembly Quota -->
        <div class="col">
            <div class="quota-metric-card p-3 h-100 d-flex flex-column justify-content-between" onclick="filterQuota('Assembly', document.querySelector('[data-quota=\'Assembly\']'))">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="stat-icon-wrapper stat-icon-mla">
                        🗳️
                    </div>
                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 extra-small fw-bold px-2 py-0.5 rounded-pill">
                        243 MLAs
                    </span>
                </div>
                <div>
                    <div class="fs-4 fw-extrabold text-navy mb-0 lh-1"><?php echo $quotaCounts['Assembly']; ?> MLCs</div>
                    <div class="fw-bold text-dark small mt-1 text-truncate">Assembly Quota</div>
                    <div class="extra-small text-muted text-truncate">Elected by Bihar MLAs</div>
                </div>
            </div>
        </div>

        <!-- 3: Governor Nominated -->
        <div class="col">
            <div class="quota-metric-card p-3 h-100 d-flex flex-column justify-content-between" onclick="filterQuota('Nominated', document.querySelector('[data-quota=\'Nominated\']'))">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="stat-icon-wrapper stat-icon-gp">
                        🎖️
                    </div>
                    <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50 extra-small fw-bold px-2 py-0.5 rounded-pill">
                        Governor
                    </span>
                </div>
                <div>
                    <div class="fs-4 fw-extrabold text-navy mb-0 lh-1"><?php echo $quotaCounts['Nominated']; ?> MLCs</div>
                    <div class="fw-bold text-dark small mt-1 text-truncate">Nominated Quota</div>
                    <div class="extra-small text-muted text-truncate">Governor-Nominated Luminaries</div>
                </div>
            </div>
        </div>

        <!-- 4: Graduates Quota -->
        <div class="col">
            <div class="quota-metric-card p-3 h-100 d-flex flex-column justify-content-between" onclick="filterQuota('Graduates', document.querySelector('[data-quota=\'Graduates\']'))">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="stat-icon-wrapper stat-icon-ps">
                        🎓
                    </div>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 extra-small fw-bold px-2 py-0.5 rounded-pill">
                        Divisions
                    </span>
                </div>
                <div>
                    <div class="fs-4 fw-extrabold text-navy mb-0 lh-1"><?php echo $quotaCounts['Graduates']; ?> MLCs</div>
                    <div class="fw-bold text-dark small mt-1 text-truncate">Graduates Quota</div>
                    <div class="extra-small text-muted text-truncate">Graduate Degree Electors</div>
                </div>
            </div>
        </div>

        <!-- 5: Teachers Quota -->
        <div class="col">
            <div class="quota-metric-card p-3 h-100 d-flex flex-column justify-content-between" onclick="filterQuota('Teachers', document.querySelector('[data-quota=\'Teachers\']'))">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="stat-icon-wrapper stat-icon-gk">
                        📚
                    </div>
                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 extra-small fw-bold px-2 py-0.5 rounded-pill">
                        Teaching
                    </span>
                </div>
                <div>
                    <div class="fs-4 fw-extrabold text-navy mb-0 lh-1"><?php echo $quotaCounts['Teachers']; ?> MLCs</div>
                    <div class="fw-bold text-dark small mt-1 text-truncate">Teachers Quota</div>
                    <div class="extra-small text-muted text-truncate">Secondary/Higher Teachers</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="card border-0 shadow-sm rounded-4 p-3 p-lg-4 mb-4 bg-white">
        <div class="row g-3 align-items-center">
            <div class="col-lg-6">
                <label for="mlcSearchInput" class="form-label small fw-bold text-navy mb-1">
                    <i class="bi bi-search text-primary me-1"></i> Search MLC by Name (English/Hindi) or Constituency
                </label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" id="mlcSearchInput" class="form-control bg-light border-start-0 ps-0" placeholder="e.g. Nitish Kumar, मंगल पाण्डेय, Patna, Graduate, Teacher..." onkeyup="filterMlcRoster()">
                </div>
            </div>

            <div class="col-lg-6">
                <label for="mlcPartyFilter" class="form-label small fw-bold text-navy mb-1">
                    <i class="bi bi-flag text-primary me-1"></i> Filter by Political Party
                </label>
                <select id="mlcPartyFilter" class="form-select bg-light" onchange="filterMlcRoster()">
                    <option value="">All Political Parties (<?php echo count($mlcs); ?>)</option>
                    <?php foreach ($partyCounts as $pName => $pNum): ?>
                        <option value="<?php echo htmlspecialchars($pName); ?>"><?php echo htmlspecialchars($pName); ?> (<?php echo $pNum; ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Quota Filter Pills -->
        <div class="d-flex flex-wrap gap-1.5 mt-3 pt-3 border-top align-items-center">
            <span class="small fw-bold text-navy me-2"><i class="bi bi-funnel-fill text-primary"></i> Quota Filter:</span>
            <button type="button" class="btn btn-sm btn-outline-secondary quota-btn active" data-quota="All" onclick="filterQuota('All', this)">All (<?php echo $quotaCounts['All']; ?>)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quota-btn" data-quota="Local" onclick="filterQuota('Local', this)">🏛️ Local Authorities (<?php echo $quotaCounts['Local']; ?>)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quota-btn" data-quota="Assembly" onclick="filterQuota('Assembly', this)">🗳️ Assembly Quota (<?php echo $quotaCounts['Assembly']; ?>)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quota-btn" data-quota="Nominated" onclick="filterQuota('Nominated', this)">🎖️ Governor Nominated (<?php echo $quotaCounts['Nominated']; ?>)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quota-btn" data-quota="Graduates" onclick="filterQuota('Graduates', this)">🎓 Graduates (<?php echo $quotaCounts['Graduates']; ?>)</button>
            <button type="button" class="btn btn-sm btn-outline-secondary quota-btn" data-quota="Teachers" onclick="filterQuota('Teachers', this)">📚 Teachers (<?php echo $quotaCounts['Teachers']; ?>)</button>
        </div>
    </div>

    <!-- MLCs Roster Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-5">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="fw-bold text-navy mb-0">
                    <i class="bi bi-journal-bookmark-fill text-primary me-2"></i> 75 Legislative Council Members (MLCs)
                </h5>
                <small class="text-muted">Updated from official Vidhan Parishad portal &bull; Photo, designation, party &amp; contact records</small>
            </div>
            <span class="badge bg-primary text-white fw-bold px-3 py-2 rounded-pill shadow-sm" id="mlcCountBadge">
                <?php echo count($mlcs); ?> MLCs
            </span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="mlcTable">
                <thead class="table-light">
                    <tr>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">#</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Member Name (MLC)</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Constituency / Quota</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Party</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase">Contact &amp; Details</th>
                        <th class="py-3 px-3 text-navy fw-bold small text-uppercase text-center">Profile</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($mlcs)): ?>
                        <?php $idx = 1; foreach ($mlcs as $mlc): 
                            $mName = (string)($mlc['name'] ?? '');
                            $mNameHi = (string)($mlc['name_hi'] ?? '');
                            $const = (string)($mlc['constituency'] ?? '');
                            $party = (string)($mlc['party'] ?? '');
                            $desig = (string)($mlc['designation'] ?? '');
                            $phone = (string)($mlc['contact'] ?? '');
                            $email = (string)($mlc['email'] ?? '');
                            $image = (string)($mlc['image'] ?? '');
                            $profileUrl = (string)($mlc['profile_url'] ?? '');
                            $dob = (string)($mlc['dob'] ?? '');
                            $address = (string)($mlc['address'] ?? '');

                            $quotaType = getMlcQuotaType($const);
                            $partyCleanClass = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $party));

                            $quotaBadgeClass = 'bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25';
                            $quotaIcon = '🗳️';
                            if ($quotaType === 'Local') {
                                $quotaBadgeClass = 'bg-purple bg-opacity-10 text-purple border border-purple border-opacity-25';
                                $quotaIcon = '🏛️';
                            } elseif ($quotaType === 'Nominated') {
                                $quotaBadgeClass = 'bg-warning bg-opacity-10 text-dark border border-warning border-opacity-50';
                                $quotaIcon = '🎖️';
                            } elseif ($quotaType === 'Graduates') {
                                $quotaBadgeClass = 'bg-success bg-opacity-10 text-success border border-success border-opacity-25';
                                $quotaIcon = '🎓';
                            } elseif ($quotaType === 'Teachers') {
                                $quotaBadgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25';
                                $quotaIcon = '📚';
                            }
                        ?>
                            <tr class="mlc-row"
                                data-name="<?php echo htmlspecialchars(strtolower($mName . ' ' . $mNameHi . ' ' . $desig)); ?>"
                                data-constituency="<?php echo htmlspecialchars(strtolower($const)); ?>"
                                data-party="<?php echo htmlspecialchars(strtoupper($party)); ?>"
                                data-quota="<?php echo htmlspecialchars($quotaType); ?>">
                                
                                <td class="text-center fw-bold" style="min-width: 55px;">
                                    <span class="badge bg-light text-muted border px-2 py-1 rounded-pill">
                                        <?php echo $idx++; ?>
                                    </span>
                                </td>

                                <td style="min-width: 240px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <?php if (!empty($image)): ?>
                                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                                 alt="<?php echo htmlspecialchars($mName); ?>" 
                                                 class="mlc-avatar-img shadow-xs" 
                                                 loading="lazy" 
                                                 onerror="this.onerror=null; this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($mName); ?>&background=0f172a&color=fff';">
                                        <?php else: ?>
                                            <div class="mlc-avatar-img d-flex align-items-center justify-content-center text-secondary fw-bold">
                                                <i class="bi bi-person-fill fs-5"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <a href="<?php echo SITE_URL . '/mlc/' . htmlspecialchars($mlc['id'] ?? $mlc['sr_no']); ?>" class="fw-bold text-navy text-decoration-none hover-primary" style="font-size: 0.95rem;">
                                                <?php echo htmlspecialchars($mName); ?>
                                            </a>
                                            <?php if (!empty($mNameHi)): ?>
                                                <div class="text-muted small"><?php echo htmlspecialchars($mNameHi); ?></div>
                                            <?php endif; ?>
                                            <?php if (!empty($desig)): ?>
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-1.5 py-0.5 rounded extra-small mt-0.5" style="font-size: 0.7rem;">
                                                    <?php echo htmlspecialchars($desig); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td style="min-width: 190px;">
                                    <div class="fw-semibold text-dark small mb-1"><?php echo htmlspecialchars($const); ?></div>
                                    <span class="badge <?php echo $quotaBadgeClass; ?> extra-small px-2 py-0.5 rounded-pill">
                                        <?php echo $quotaIcon . ' ' . htmlspecialchars($quotaType); ?> Quota
                                    </span>
                                </td>

                                <td class="text-center" style="min-width: 110px;">
                                    <span class="badge-party <?php echo $partyCleanClass; ?>">
                                        <?php echo htmlspecialchars($party); ?>
                                    </span>
                                </td>

                                <td style="min-width: 180px;">
                                    <?php if (!empty($phone)): ?>
                                        <div class="mb-1">
                                            <?php echo renderMaskedPhoneButton($phone, $mName); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($email)): ?>
                                        <div class="small text-muted text-truncate font-monospace" style="max-width: 180px;" title="<?php echo htmlspecialchars(maskEmailAddress($email)); ?>">
                                            <i class="bi bi-envelope me-1"></i><?php echo htmlspecialchars(maskEmailAddress($email)); ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($dob)): ?>
                                        <div class="extra-small text-muted mt-0.5" style="font-size: 0.72rem;">
                                            <i class="bi bi-cake2 me-1"></i>DOB: <?php echo htmlspecialchars($dob); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="text-center" style="min-width: 110px;">
                                    <?php 
                                        $mlcInternalId = !empty($mlc['id']) ? $mlc['id'] : ($mlc['sr_no'] ?? '');
                                    ?>
                                    <?php if (!empty($mlcInternalId)): ?>
                                        <a href="<?php echo SITE_URL . '/mlc/' . htmlspecialchars($mlcInternalId); ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 shadow-sm text-nowrap">
                                            View Profile <i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Official Data Sources Attribution Banner -->
    <section class="mt-5 pt-4 border-top">
        <div class="p-4 rounded-4 bg-light border d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-15 text-dark p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;">
                    <i class="bi bi-shield-check fs-4"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark font-heading mb-1">Official Legislative Secretariat Records</h6>
                    <p class="text-muted small mb-0">Directly synchronized with the Bihar Legislative Council (<a href="https://vidhanparishad.bihar.gov.in/Member_Details_List" target="_blank" rel="noopener noreferrer" class="text-dark fw-semibold">vidhanparishad.bihar.gov.in</a>). All 75 member profiles, quota allocations, and secretarial details verified.</p>
                </div>
            </div>
            <a href="<?php echo SITE_URL; ?>/mla" class="btn btn-outline-info text-dark rounded-pill px-4 py-2 fw-semibold text-nowrap">
                <i class="bi bi-person-badge me-1"></i>243 MLAs Directory
            </a>
        </div>
    </section>

    <?php renderGoogleAd('footer_banner', GOOGLE_AD_SLOT_FOOTER, 'my-4'); ?>
</main>

<script>
let currentQuota = 'All';

function filterQuota(quota, btn) {
    currentQuota = quota;
    document.querySelectorAll('.quota-btn').forEach(b => b.classList.remove('active'));
    if (btn) btn.classList.add('active');
    filterMlcRoster();
}

function filterMlcRoster() {
    const query = (document.getElementById('mlcSearchInput')?.value || '').toLowerCase().trim();
    const party = (document.getElementById('mlcPartyFilter')?.value || '').toUpperCase().trim();
    let visible = 0;

    document.querySelectorAll('#mlcTable .mlc-row').forEach(row => {
        const name = row.getAttribute('data-name') || '';
        const constName = row.getAttribute('data-constituency') || '';
        const pParty = row.getAttribute('data-party') || '';
        const quota = row.getAttribute('data-quota') || '';

        const matchQuery = !query || name.includes(query) || constName.includes(query) || pParty.toLowerCase().includes(query);
        const matchParty = !party || pParty.includes(party);
        const matchQuota = currentQuota === 'All' || quota.toLowerCase().includes(currentQuota.toLowerCase());

        if (matchQuery && matchParty && matchQuota) {
            row.style.display = '';
            visible++;
        } else {
            row.style.display = 'none';
        }
    });

    const badge = document.getElementById('mlcCountBadge');
    if (badge) badge.innerText = visible + ' MLCs';
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
