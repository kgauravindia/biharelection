<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';
$candidate = DataProvider::getCandidateBySlug($slug);

if (!$candidate) {
    header("Location: candidates.php?error=notfound");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($candidate['name']); ?> — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif; color: #0f172a;">
                    <?php echo htmlspecialchars($candidate['name']); ?>
                </h1>
                <p class="text-muted mb-0">Candidate for <?php echo htmlspecialchars($candidate['constituency']); ?> (<?php echo htmlspecialchars($candidate['district']); ?>)</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="edit-candidate.php?slug=<?php echo urlencode($candidate['slug']); ?>" class="btn btn-primary btn-sm px-3 py-2 fw-semibold rounded-3 shadow-sm">
                    <i class="fas fa-pen me-1"></i> Edit Profile
                </a>
                <a href="../candidate.php?slug=<?php echo urlencode($candidate['slug']); ?>" target="_blank" class="btn btn-outline-dark btn-sm px-3 py-2 fw-semibold rounded-3 shadow-sm bg-white">
                    <i class="fas fa-external-link-alt me-1"></i> View Live Profile
                </a>
            </div>
        </div>

        <div class="row g-4">
            <!-- Profile Card -->
            <div class="col-lg-4">
                <div class="section-card text-center p-4 mb-4">
                    <div class="rounded-circle border shadow-sm mx-auto mb-3 d-flex align-items-center justify-content-center bg-light" style="width: 130px; height: 130px; overflow: hidden;">
                        <?php if (!empty($candidate['photo'])): ?>
                            <img src="<?php echo htmlspecialchars($candidate['photo']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                        <?php else: ?>
                            <span class="fs-1 fw-bold text-danger"><?php echo strtoupper(substr($candidate['name'], 0, 1)); ?></span>
                        <?php endif; ?>
                    </div>

                    <h4 class="fw-bold mb-1" style="font-family: 'Outfit', sans-serif;"><?php echo htmlspecialchars($candidate['name']); ?></h4>
                    <p class="text-muted mb-2"><?php echo htmlspecialchars($candidate['name_hi'] ?? ''); ?></p>
                    
                    <div class="d-flex justify-content-center gap-2 mb-3">
                        <span class="badge bg-primary"><?php echo htmlspecialchars($candidate['party_short'] ?? $candidate['party'] ?? 'IND'); ?></span>
                        <?php if (!empty($candidate['verified'])): ?>
                            <span class="badge badge-soft-success"><i class="fas fa-check-circle me-1"></i> Verified</span>
                        <?php endif; ?>
                        <span class="badge bg-warning text-dark"><?php echo htmlspecialchars($candidate['promoted_tier'] ?? 'STANDARD'); ?></span>
                    </div>

                    <p class="small text-muted mb-0"><?php echo htmlspecialchars($candidate['party']); ?></p>
                </div>

                <!-- Contact & Social Details -->
                <div class="section-card p-3 mb-4">
                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-share-nodes text-primary me-2"></i> Social Links</h6>
                    <div class="d-flex flex-column gap-2 small">
                        <div><strong>Facebook:</strong> <?php echo !empty($candidate['social_links']['facebook']) ? '<a href="' . htmlspecialchars($candidate['social_links']['facebook']) . '" target="_blank">' . htmlspecialchars($candidate['social_links']['facebook']) . '</a>' : '<span class="text-muted">Not provided</span>'; ?></div>
                        <div><strong>X (Twitter):</strong> <?php echo !empty($candidate['social_links']['twitter']) ? '<a href="' . htmlspecialchars($candidate['social_links']['twitter']) . '" target="_blank">' . htmlspecialchars($candidate['social_links']['twitter']) . '</a>' : '<span class="text-muted">Not provided</span>'; ?></div>
                        <div><strong>Instagram:</strong> <?php echo !empty($candidate['social_links']['instagram']) ? '<a href="' . htmlspecialchars($candidate['social_links']['instagram']) . '" target="_blank">' . htmlspecialchars($candidate['social_links']['instagram']) . '</a>' : '<span class="text-muted">Not provided</span>'; ?></div>
                    </div>
                </div>
            </div>

            <!-- Details Information -->
            <div class="col-lg-8">
                <div class="section-card mb-4">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-file-lines me-2 text-danger"></i> Electoral Profile Summary</h6>
                    </div>
                    <div class="section-card-body">
                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label class="small text-muted text-uppercase fw-bold">Constituency</label>
                                <p class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($candidate['constituency']); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <label class="small text-muted text-uppercase fw-bold">District</label>
                                <p class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($candidate['district']); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <label class="small text-muted text-uppercase fw-bold">Age</label>
                                <p class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($candidate['age'] ?? 'N/A'); ?> Years</p>
                            </div>
                            <div class="col-sm-6">
                                <label class="small text-muted text-uppercase fw-bold">Education</label>
                                <p class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($candidate['education'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <label class="small text-muted text-uppercase fw-bold">Profession</label>
                                <p class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($candidate['profession'] ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-sm-6">
                                <label class="small text-muted text-uppercase fw-bold">Criminal Cases</label>
                                <p class="fw-bold <?php echo ($candidate['criminal_cases'] > 0) ? 'text-danger' : 'text-success'; ?> mb-0">
                                    <?php echo (int)($candidate['criminal_cases'] ?? 0); ?> Cases Declared
                                </p>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <h6 class="fw-bold text-dark mb-2">Financial Disclosures (Affidavit)</h6>
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-bold">Declared Assets</small>
                                        <span class="fs-5 fw-bold text-success"><?php echo htmlspecialchars($candidate['assets_declared'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <small class="text-muted d-block text-uppercase fw-bold">Declared Liabilities</small>
                                        <span class="fs-5 fw-bold text-danger"><?php echo htmlspecialchars($candidate['liabilities'] ?? 'N/A'); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($candidate['bio'])): ?>
                            <div class="border-top pt-3 mt-3">
                                <h6 class="fw-bold text-dark mb-2">Political Biography</h6>
                                <p class="text-muted mb-0" style="line-height: 1.6;"><?php echo nl2br(htmlspecialchars($candidate['bio'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
