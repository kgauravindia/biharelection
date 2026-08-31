<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

$candidate = [
    'id' => 0,
    'name' => '',
    'name_hi' => '',
    'slug' => '',
    'party' => 'Bharatiya Janata Party',
    'party_short' => 'BJP',
    'constituency' => '',
    'district' => 'Patna',
    'age' => '',
    'education' => 'Graduate',
    'profession' => 'Social Worker & Politician',
    'assets_declared' => '₹ 2.50 Cr',
    'liabilities' => '₹ 15.00 Lakh',
    'criminal_cases' => 0,
    'verified' => 1,
    'promoted_tier' => 'STANDARD',
    'photo' => '',
    'bio' => '',
    'social_links' => []
];

// Fetch Candidate if editing
if ($id > 0 && $conn) {
    $stmt = $conn->prepare("SELECT * FROM `be_candidates` WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $res->num_rows === 1) {
            $candidate = $res->fetch_assoc();
            if (is_string($candidate['social_links'] ?? null)) {
                $candidate['social_links'] = json_decode($candidate['social_links'], true) ?: [];
            }
        }
    }
} elseif (!empty($slug)) {
    $c_data = DataProvider::getCandidateBySlug($slug);
    if ($c_data) {
        $candidate = array_merge($candidate, $c_data);
    }
}

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $name_hi = sanitize($_POST['name_hi'] ?? '');
    $post_slug = sanitize($_POST['slug'] ?? '');
    if (empty($post_slug)) {
        $post_slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }

    $party = sanitize($_POST['party'] ?? '');
    $party_short = sanitize($_POST['party_short'] ?? '');
    $constituency = sanitize($_POST['constituency'] ?? '');
    $district = sanitize($_POST['district'] ?? '');
    $age = (int)($_POST['age'] ?? 0);
    $education = sanitize($_POST['education'] ?? '');
    $profession = sanitize($_POST['profession'] ?? '');
    $assets = sanitize($_POST['assets_declared'] ?? '');
    $liabilities = sanitize($_POST['liabilities'] ?? '');
    $criminal_cases = (int)($_POST['criminal_cases'] ?? 0);
    $verified = isset($_POST['verified']) ? 1 : 0;
    $tier = sanitize($_POST['promoted_tier'] ?? 'STANDARD');
    $photo = sanitize($_POST['photo'] ?? '');
    $bio = sanitize($_POST['bio'] ?? '');

    $social_links = [
        'facebook' => sanitize($_POST['facebook'] ?? ''),
        'twitter' => sanitize($_POST['twitter'] ?? ''),
        'instagram' => sanitize($_POST['instagram'] ?? ''),
        'youtube' => sanitize($_POST['youtube'] ?? '')
    ];
    $social_json = json_encode($social_links);

    // Handle photo file upload if provided
    if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = __DIR__ . '/../uploads/candidates/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $ext = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $new_filename = 'cand_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $upload_dir . $new_filename)) {
                $photo = 'uploads/candidates/' . $new_filename;
            }
        }
    }

    if (empty($name) || empty($constituency)) {
        $error = "Candidate name and constituency are required.";
    } elseif ($conn) {
        if ($id > 0) {
            $stmt = $conn->prepare("UPDATE `be_candidates` SET `name`=?, `name_hi`=?, `slug`=?, `party`=?, `party_short`=?, `constituency`=?, `district`=?, `age`=?, `education`=?, `profession`=?, `assets_declared`=?, `liabilities`=?, `criminal_cases`=?, `verified`=?, `promoted_tier`=?, `photo`=?, `bio`=?, `social_links`=? WHERE `id`=?");
            if ($stmt) {
                $stmt->bind_param("ssssssisssssisssssi", $name, $name_hi, $post_slug, $party, $party_short, $constituency, $district, $age, $education, $profession, $assets, $liabilities, $criminal_cases, $verified, $tier, $photo, $bio, $social_json, $id);
                if ($stmt->execute()) {
                    $message = "Candidate profile updated successfully!";
                } else {
                    $error = "Error updating candidate: " . $conn->error;
                }
            }
        } else {
            $stmt = $conn->prepare("INSERT INTO `be_candidates` (`name`, `name_hi`, `slug`, `party`, `party_short`, `constituency`, `district`, `age`, `education`, `profession`, `assets_declared`, `liabilities`, `criminal_cases`, `verified`, `promoted_tier`, `photo`, `bio`, `social_links`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
            if ($stmt) {
                $stmt->bind_param("ssssssisssssisssss", $name, $name_hi, $post_slug, $party, $party_short, $constituency, $district, $age, $education, $profession, $assets, $liabilities, $criminal_cases, $verified, $tier, $photo, $bio, $social_json);
                if ($stmt->execute()) {
                    $id = $stmt->insert_id;
                    $message = "Candidate profile created successfully!";
                } else {
                    $error = "Error adding candidate: " . $conn->error;
                }
            }
        }
    } else {
        $message = "Saved locally (Database connection offline).";
    }

    // Refresh candidate record
    $candidate = array_merge($candidate, [
        'name' => $name, 'name_hi' => $name_hi, 'slug' => $post_slug,
        'party' => $party, 'party_short' => $party_short, 'constituency' => $constituency,
        'district' => $district, 'age' => $age, 'education' => $education,
        'profession' => $profession, 'assets_declared' => $assets, 'liabilities' => $liabilities,
        'criminal_cases' => $criminal_cases, 'verified' => $verified, 'promoted_tier' => $tier,
        'photo' => $photo, 'bio' => $bio, 'social_links' => $social_links
    ]);
}

$districts = DataProvider::getDistricts();
$constituencies = DataProvider::getConstituencies();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo ($id > 0 || !empty($slug)) ? 'Edit Candidate' : 'Add Candidate'; ?> — Bihar Election</title>
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
                <a href="candidates.php" class="text-decoration-none text-muted small mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to Candidates
                </a>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    <?php echo ($id > 0 || !empty($candidate['name'])) ? 'Edit Candidate Profile' : 'Add New Candidate Profile'; ?>
                </h1>
                <p class="text-muted mb-0">Fill in all electoral background, financial disclosures & party ticket details.</p>
            </div>
            <?php if (!empty($candidate['slug'])): ?>
                <div class="mt-3 mt-md-0">
                    <a href="../candidate.php?slug=<?php echo urlencode($candidate['slug']); ?>" target="_blank" class="btn btn-outline-dark btn-sm rounded-3 shadow-sm bg-white">
                        <i class="fas fa-external-link-alt me-1"></i> Preview Public Profile
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="" enctype="multipart/form-data">
            <div class="row g-4">
                <!-- Basic Information -->
                <div class="col-lg-8">
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-circle me-2 text-danger"></i> Candidate Profile & Identity</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Full Name (English) *</label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($candidate['name'] ?? ''); ?>" required placeholder="e.g. Nitish Kumar">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Full Name (Hindi)</label>
                                    <input type="text" name="name_hi" class="form-control" value="<?php echo htmlspecialchars($candidate['name_hi'] ?? ''); ?>" placeholder="e.g. नीतीश कुमार">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Profile Slug (URL)</label>
                                    <input type="text" name="slug" class="form-control" value="<?php echo htmlspecialchars($candidate['slug'] ?? ''); ?>" placeholder="nitish-kumar">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Age</label>
                                    <input type="number" name="age" class="form-control" value="<?php echo htmlspecialchars($candidate['age'] ?? ''); ?>" placeholder="52">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Criminal Cases</label>
                                    <input type="number" name="criminal_cases" class="form-control" value="<?php echo (int)($candidate['criminal_cases'] ?? 0); ?>">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Political Party *</label>
                                    <input type="text" name="party" class="form-control" value="<?php echo htmlspecialchars($candidate['party'] ?? ''); ?>" placeholder="e.g. Bharatiya Janata Party">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Party Acronym / Short</label>
                                    <input type="text" name="party_short" class="form-control" value="<?php echo htmlspecialchars($candidate['party_short'] ?? ''); ?>" placeholder="BJP / RJD / JDU / INC">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">District *</label>
                                    <select name="district" class="form-select" required>
                                        <?php foreach ($districts as $d): ?>
                                            <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo (($candidate['district'] ?? '') === $d['name']) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($d['name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Constituency *</label>
                                    <select name="constituency" class="form-select" required>
                                        <?php foreach ($constituencies as $ac): ?>
                                            <option value="<?php echo htmlspecialchars($ac['name']); ?>" <?php echo (($candidate['constituency'] ?? '') === $ac['name']) ? 'selected' : ''; ?>>
                                                AC <?php echo $ac['ac_no']; ?> — <?php echo htmlspecialchars($ac['name']); ?> (<?php echo htmlspecialchars($ac['district']); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="col-12">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Biography & Political Background</label>
                                    <textarea name="bio" class="form-control" rows="4" placeholder="Brief political overview, legislative history, and achievements..."><?php echo htmlspecialchars($candidate['bio'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Financial & Educational Disclosures -->
                    <div class="section-card">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-invoice-dollar me-2 text-success"></i> Affidavit Disclosures & Background</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Educational Qualification</label>
                                    <input type="text" name="education" class="form-control" value="<?php echo htmlspecialchars($candidate['education'] ?? ''); ?>" placeholder="e.g. B.Tech / Post Graduate">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Profession / Occupation</label>
                                    <input type="text" name="profession" class="form-control" value="<?php echo htmlspecialchars($candidate['profession'] ?? ''); ?>" placeholder="e.g. Agriculture & Business">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Declared Assets</label>
                                    <input type="text" name="assets_declared" class="form-control" value="<?php echo htmlspecialchars($candidate['assets_declared'] ?? ''); ?>" placeholder="₹ 3.25 Cr">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-muted text-uppercase">Declared Liabilities</label>
                                    <input type="text" name="liabilities" class="form-control" value="<?php echo htmlspecialchars($candidate['liabilities'] ?? ''); ?>" placeholder="₹ 20.00 Lakh">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar / Settings & Media -->
                <div class="col-lg-4">
                    <!-- Photo & Media -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-camera me-2 text-primary"></i> Candidate Photo</h6>
                        </div>
                        <div class="section-card-body text-center">
                            <div class="mb-3 mx-auto rounded-circle border shadow-sm d-flex align-items-center justify-content-center bg-light" style="width: 120px; height: 120px; overflow: hidden;">
                                <?php if (!empty($candidate['photo'])): ?>
                                    <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user fa-3x text-muted"></i>
                                <?php endif; ?>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Upload New Photo</label>
                                <input type="file" name="photo_file" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted text-uppercase">Or Image URL</label>
                                <input type="text" name="photo" class="form-control form-control-sm" value="<?php echo htmlspecialchars($candidate['photo'] ?? ''); ?>" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <!-- Platform Tier & Verification -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-certificate me-2 text-warning"></i> Tier & Status</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Promotion Tier</label>
                                <select name="promoted_tier" class="form-select">
                                    <option value="STANDARD" <?php echo (($candidate['promoted_tier'] ?? '') === 'STANDARD') ? 'selected' : ''; ?>>Standard Profile</option>
                                    <option value="FEATURED" <?php echo (($candidate['promoted_tier'] ?? '') === 'FEATURED') ? 'selected' : ''; ?>>Featured Top Tier</option>
                                    <option value="VIP" <?php echo (($candidate['promoted_tier'] ?? '') === 'VIP') ? 'selected' : ''; ?>>VIP Leader Tier</option>
                                </select>
                            </div>
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" name="verified" value="1" id="verifiedSwitch" <?php echo !empty($candidate['verified']) ? 'checked' : ''; ?>>
                                <label class="form-check-label fw-bold small" for="verifiedSwitch">
                                    Verified Candidate Profile
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Social Handles -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-share-nodes me-2 text-info"></i> Social Links</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fab fa-facebook text-primary"></i></span>
                                    <input type="text" name="facebook" class="form-control" placeholder="Facebook URL" value="<?php echo htmlspecialchars($candidate['social_links']['facebook'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fab fa-x-twitter"></i></span>
                                    <input type="text" name="twitter" class="form-control" placeholder="X / Twitter URL" value="<?php echo htmlspecialchars($candidate['social_links']['twitter'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fab fa-instagram text-danger"></i></span>
                                    <input type="text" name="instagram" class="form-control" placeholder="Instagram URL" value="<?php echo htmlspecialchars($candidate['social_links']['instagram'] ?? ''); ?>">
                                </div>
                            </div>
                            <div class="mb-2">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fab fa-youtube text-danger"></i></span>
                                    <input type="text" name="youtube" class="form-control" placeholder="YouTube URL" value="<?php echo htmlspecialchars($candidate['social_links']['youtube'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Save Actions -->
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm rounded-3">
                            <i class="fas fa-save me-2"></i> Save Candidate Record
                        </button>
                        <a href="candidates.php" class="btn btn-light border btn-sm">Cancel</a>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <?php include 'admin-footer.php'; ?>
