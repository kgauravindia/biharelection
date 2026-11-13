<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$pdo = Database::getConnection();
$message = '';
$error = '';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$sr_no = isset($_GET['sr_no']) ? (int)$_GET['sr_no'] : 0;

$all_mlcs = DataProvider::getMlcs();
$mlc = null;

if ($id > 0) {
    foreach ($all_mlcs as $m) {
        if ((int)($m['id'] ?? 0) === $id) {
            $mlc = $m;
            break;
        }
    }
}
if (!$mlc && $sr_no > 0) {
    foreach ($all_mlcs as $m) {
        if ((int)($m['sr_no'] ?? 0) === $sr_no) {
            $mlc = $m;
            break;
        }
    }
}
if (!$mlc && $id > 0) {
    // Try matching sr_no if id was passed as index
    foreach ($all_mlcs as $m) {
        if ((int)($m['sr_no'] ?? 0) === $id) {
            $mlc = $m;
            break;
        }
    }
}

if (!$mlc) {
    header("Location: mlcs.php?error=notfound");
    exit();
}

$mId = (int)($mlc['id'] ?? 0);
$mSr = (int)($mlc['sr_no'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $name_hi = sanitize($_POST['name_hi'] ?? '');
    $constituency = sanitize($_POST['constituency'] ?? '');
    $party = sanitize($_POST['party'] ?? '');
    $party_full = sanitize($_POST['party_full'] ?? '');
    $designation = sanitize($_POST['designation'] ?? '');
    $tenure = sanitize($_POST['tenure'] ?? '');
    $contact = sanitize($_POST['contact'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $image = sanitize($_POST['image'] ?? '');
    $address = sanitize($_POST['address'] ?? '');

    if (empty($name)) {
        $error = "Member Name cannot be empty.";
    } elseif (empty($constituency)) {
        $error = "Constituency cannot be empty.";
    } else {
        // 1. Update in Database
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("UPDATE `mlcs` SET 
                    `name` = :name, 
                    `name_hi` = :name_hi, 
                    `constituency` = :constituency, 
                    `party` = :party, 
                    `party_full` = :party_full, 
                    `designation` = :designation, 
                    `tenure` = :tenure, 
                    `contact` = :contact, 
                    `email` = :email, 
                    `image` = :image, 
                    `address` = :address 
                    WHERE `id` = :id OR `sr_no` = :sr_no");
                
                $stmt->execute([
                    ':name' => $name,
                    ':name_hi' => $name_hi,
                    ':constituency' => $constituency,
                    ':party' => $party,
                    ':party_full' => $party_full,
                    ':designation' => $designation,
                    ':tenure' => $tenure,
                    ':contact' => $contact,
                    ':email' => $email,
                    ':image' => $image,
                    ':address' => $address,
                    ':id' => $mId,
                    ':sr_no' => $mSr
                ]);
            } catch (Throwable $e) {
                $error = "Database update error: " . $e->getMessage();
            }
        } elseif ($conn) {
            $stmt = $conn->prepare("UPDATE `mlcs` SET `name`=?, `name_hi`=?, `constituency`=?, `party`=?, `party_full`=?, `designation`=?, `tenure`=?, `contact`=?, `email`=?, `image`=?, `address`=? WHERE `id`=? OR `sr_no`=?");
            if ($stmt) {
                $stmt->bind_param("sssssssssssii", $name, $name_hi, $constituency, $party, $party_full, $designation, $tenure, $contact, $email, $image, $address, $mId, $mSr);
                $stmt->execute();
            }
        }

        // 2. Sync with assets/data/mlcs.json
        $jsonFile = __DIR__ . '/../assets/data/mlcs.json';
        if (file_exists($jsonFile)) {
            $rawJson = @file_get_contents($jsonFile);
            $jsonData = json_decode($rawJson, true);
            if (is_array($jsonData)) {
                $updatedJson = false;
                foreach ($jsonData as &$item) {
                    if ((int)($item['sr_no'] ?? 0) === $mSr || (int)($item['id'] ?? 0) === $mId) {
                        $item['name'] = $name;
                        if (!empty($name_hi)) $item['name_hi'] = $name_hi;
                        $item['constituency'] = $constituency;
                        $item['party'] = $party;
                        $item['party_full'] = $party_full;
                        if (!empty($designation)) $item['designation'] = $designation;
                        $item['tenure'] = $tenure;
                        $item['contact'] = $contact;
                        if (!empty($email)) $item['email'] = $email;
                        if (!empty($image)) $item['image'] = $image;
                        if (!empty($address)) $item['address'] = $address;
                        $updatedJson = true;
                        break;
                    }
                }
                if ($updatedJson) {
                    @file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
            }
        }

        // 3. Invalidate Cache
        DataCache::delete('provider_mlcs');

        if (empty($error)) {
            $message = "MLC Member '{$name}' updated successfully! Database and JSON synchronized.";
            // Refresh local object
            $mlc = array_merge($mlc, [
                'name' => $name,
                'name_hi' => $name_hi,
                'constituency' => $constituency,
                'party' => $party,
                'party_full' => $party_full,
                'designation' => $designation,
                'tenure' => $tenure,
                'contact' => $contact,
                'email' => $email,
                'image' => $image,
                'address' => $address
            ]);
        }
    }
}

$mlcSlug = !empty($mlc['slug']) ? $mlc['slug'] : slugify($mlc['name']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit MLC Member #<?php echo $mSr ?: $mId; ?> — Bihar Election Admin</title>
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
                <a href="mlcs.php" class="text-decoration-none text-muted small mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to 75 MLCs Directory
                </a>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                    Edit MLC: <?php echo htmlspecialchars($mlc['name']); ?>
                </h1>
                <p class="text-muted mb-0">Seat #<?php echo $mSr ?: $mId; ?> | Constituency: <?php echo htmlspecialchars($mlc['constituency']); ?></p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="../mlc.php?id=<?php echo $mId ?: $mSr; ?>" target="_blank" class="btn btn-outline-primary shadow-sm fw-semibold">
                    <i class="fas fa-globe me-1"></i> View Live Profile
                </a>
                <a href="git-push.php" class="btn btn-outline-danger shadow-sm fw-semibold">
                    <i class="fab fa-git-alt me-1"></i> Version Control / Deploy
                </a>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-circle-check me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-triangle-exclamation me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit-mlc.php?id=<?php echo $mId; ?>&sr_no=<?php echo $mSr; ?>">
            <div class="row g-4">
                <!-- Left Column: Core Political Details -->
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fas fa-scroll text-primary me-2"></i> Member Profile & Legislative Identification
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <label class="form-label fw-bold small text-muted">Full Member Name (English) <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($mlc['name'] ?? ''); ?>" required placeholder="e.g. Shri Neeraj Kumar">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-muted">Member Name (Hindi / देवनागरी)</label>
                                    <input type="text" name="name_hi" class="form-control" value="<?php echo htmlspecialchars($mlc['name_hi'] ?? ''); ?>" placeholder="उदा. श्री नीरज कुमार">
                                </div>

                                <div class="col-md-7">
                                    <label class="form-label fw-bold small text-muted">Constituency / क्षेत्र <span class="text-danger">*</span></label>
                                    <input type="text" name="constituency" class="form-control" value="<?php echo htmlspecialchars($mlc['constituency'] ?? ''); ?>" required placeholder="e.g. Graduate, Patna / Local Bodies, Saran / Vidhan Sabha">
                                    <small class="text-muted">Must include quota keyword: Graduate, Teacher, Local Bodies, Nominated, or Vidhan Sabha</small>
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small text-muted">Designation / Role</label>
                                    <input type="text" name="designation" class="form-control" value="<?php echo htmlspecialchars($mlc['designation'] ?? ''); ?>" placeholder="e.g. Minister, Deputy Leader, MLC">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Political Party (Abbreviation)</label>
                                    <input type="text" name="party" class="form-control" value="<?php echo htmlspecialchars($mlc['party'] ?? ''); ?>" placeholder="e.g. JD(U), BJP, RJD, Jan Suraaj, IND">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Party Full Name</label>
                                    <input type="text" name="party_full" class="form-control" value="<?php echo htmlspecialchars($mlc['party_full'] ?? ''); ?>" placeholder="e.g. Janata Dal (United)">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Tenure / कार्यकाल (Term Duration)</label>
                                    <input type="text" name="tenure" class="form-control" value="<?php echo htmlspecialchars($mlc['tenure'] ?? ''); ?>" placeholder="e.g. 2026–2032 or 29.06.2020- 28.06.2026">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Contact Mobile Number</label>
                                    <input type="text" name="contact" class="form-control" value="<?php echo htmlspecialchars($mlc['contact'] ?? ''); ?>" placeholder="e.g. 9431018868">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Official Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($mlc['email'] ?? ''); ?>" placeholder="e.g. member@vidhanparishad.bih.nic.in">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-bold small text-muted">Profile Photo Image URL</label>
                                    <input type="text" name="image" class="form-control" value="<?php echo htmlspecialchars($mlc['image'] ?? ''); ?>" placeholder="https://... or /assets/image/...">
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold small text-muted">Official / Residential Address</label>
                                    <textarea name="address" rows="3" class="form-control" placeholder="Patna / Constituency address..."><?php echo htmlspecialchars($mlc['address'] ?? ''); ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Metadata & Actions -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-3 mb-4">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fas fa-floppy-disk text-success me-2"></i> Save & Synchronization
                            </h5>
                        </div>
                        <div class="card-body p-4">
                            <p class="text-muted small mb-3">
                                Saving will automatically update the <strong>MySQL <code>mlcs</code> table</strong>, synchronize the resilient <strong><code>assets/data/mlcs.json</code></strong> fallback file, and flush the cache.
                            </p>
                            <button type="submit" class="btn btn-success w-100 py-2.5 fw-bold shadow-sm mb-2">
                                <i class="fas fa-check me-1"></i> Save MLC Member Details
                            </button>
                            <a href="mlcs.php" class="btn btn-outline-secondary w-100 py-2">
                                Cancel & Return
                            </a>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white py-3 border-bottom">
                            <h5 class="card-title mb-0 fw-bold text-dark">
                                <i class="fas fa-circle-info text-info me-2"></i> Seat Reference
                            </h5>
                        </div>
                        <div class="card-body p-4 small">
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Serial No / ID:</span>
                                <strong>#<?php echo $mSr ?: $mId; ?> (ID: <?php echo $mId; ?>)</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">House:</span>
                                <strong>Bihar Vidhan Parishad</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1 border-bottom">
                                <span class="text-muted">Total Council Seats:</span>
                                <strong>75 Members</strong>
                            </div>
                            <div class="d-flex justify-content-between py-1">
                                <span class="text-muted">Live Public URL:</span>
                                <a href="../mlc.php?id=<?php echo $mId ?: $mSr; ?>" target="_blank" class="text-primary text-truncate ms-2" style="max-width: 150px;">
                                    /mlc/<?php echo $mId; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>
</div>

<?php include 'admin-footer.php'; ?>
</body>
</html>
