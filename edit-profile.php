<?php
/**
 * BiharElection.com - Comprehensive User & Representative Profile Editor
 * Modeled after the rich profile management system in Saran Index.
 */
require_once __DIR__ . '/includes/auth_helper.php';
requireUserLogin();

$user = getCurrentUser();
if (!$user) {
    logoutUser();
    header('Location: login.php');
    exit;
}

$districts = DataProvider::getDistricts();
if (!empty($districts) && is_array($districts)) {
    usort($districts, function ($a, $b) {
        return strcasecmp($a['name'] ?? '', $b['name'] ?? '');
    });
}

$msg = '';
$msgType = '';

// Handle Profile Update POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_profile') {
    $fullName = trim($_POST['full_name'] ?? '');
    $name = !empty($fullName) ? $fullName : trim($_POST['name'] ?? '');

    $postData = [
        'name'                => $name,
        'full_name'           => $fullName,
        'username_handle'     => trim($_POST['username_handle'] ?? ''),
        'email'               => trim($_POST['email'] ?? ''),
        'whatsapp'            => trim($_POST['whatsapp'] ?? ''),
        'role'                => trim($_POST['role'] ?? 'voter'),
        'district'            => trim($_POST['district'] ?? ''),
        'constituency'        => trim($_POST['constituency'] ?? ''),
        'panchayat'           => trim($_POST['panchayat'] ?? ''),
        'business_name'       => trim($_POST['business_name'] ?? ''),
        'designation'         => trim($_POST['designation'] ?? ''),
        'profession_category' => trim($_POST['profession_category'] ?? ''),
        'specialization'      => trim($_POST['specialization'] ?? ''),
        'education'           => trim($_POST['education'] ?? ''),
        'gender'              => trim($_POST['gender'] ?? ''),
        'dob'                 => !empty($_POST['dob']) ? $_POST['dob'] : null,
        'languages'           => trim($_POST['languages'] ?? ''),
        'experience_years'    => trim($_POST['experience_years'] ?? ''),
        'office_hours'        => trim($_POST['office_hours'] ?? ''),
        'address'             => trim($_POST['address'] ?? ''),
        'pincode'             => trim($_POST['pincode'] ?? ''),
        'bio'                 => trim($_POST['bio'] ?? ''),
        'about'               => trim($_POST['about'] ?? ''),
        'profile_visibility'  => trim($_POST['profile_visibility'] ?? 'PUBLIC'),
        'mobile_visibility'   => trim($_POST['mobile_visibility'] ?? 'PUBLIC'),
        'email_visibility'    => trim($_POST['email_visibility'] ?? 'PUBLIC'),
        'address_visibility'  => trim($_POST['address_visibility'] ?? 'PUBLIC'),
        'linkedin'            => trim($_POST['linkedin'] ?? ''),
        'twitter'             => trim($_POST['twitter'] ?? ''),
        'facebook'            => trim($_POST['facebook'] ?? ''),
        'instagram'           => trim($_POST['instagram'] ?? ''),
        'google_maps_link'    => trim($_POST['google_maps_link'] ?? '')
    ];

    // Handle Photo Upload
    if (!empty($_FILES['profile_image_file']['tmp_name'])) {
        $uploaded = uploadUserProfilePhoto($_FILES['profile_image_file'], $user['id']);
        if ($uploaded) {
            $postData['profile_image'] = $uploaded;
            $postData['profile_photo'] = $uploaded;
            $postData['photo'] = $uploaded;
        }
    }

    if (updateUserProfile($user['id'], $postData)) {
        $msg = "Your profile has been updated successfully!";
        $msgType = 'success';
        
        // Refresh session data
        $_SESSION['public_user_name']         = $name;
        $_SESSION['public_user_email']        = $postData['email'];
        $_SESSION['public_user_role']         = $postData['role'];
        $_SESSION['public_user_district']     = $postData['district'];
        $_SESSION['public_user_constituency'] = $postData['constituency'];
        $_SESSION['public_user_panchayat']    = $postData['panchayat'];

        $user = getCurrentUser(); // Reload fresh user data
    } else {
        $msg = "Failed to update profile. Please try again.";
        $msgType = 'danger';
    }
}

$pageTitle = 'Edit Profile & Account Settings | Bihar Election';
$pageDescription = 'Update your personal, electoral, and professional details on BiharElection.com.';
$pageCanonical = SITE_URL . '/edit-profile.php';
$activeNav = 'profile';

$profileHandle = !empty($user['username_handle']) ? $user['username_handle'] : (string)$user['id'];
$publicProfileUrl = SITE_URL . '/user/' . urlencode(ltrim($profileHandle, '@'));

require_once __DIR__ . '/header.php';
?>

<div class="container py-4 py-lg-5">
    
    <!-- Hero Banner -->
    <div class="card border-0 rounded-4 shadow-sm p-4 p-md-5 mb-4 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0b192c 100%);">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 position-relative z-1">
            <div>
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-2 small shadow-xs">
                    <i class="bi bi-person-gear me-1"></i> Account Settings &amp; Public Profile
                </span>
                <h1 class="display-6 fw-bold text-white mb-1" style="font-family: 'Outfit', sans-serif;">Edit Your Profile</h1>
                <p class="text-white-50 lead mb-0" style="font-size: 1.05rem;">
                    Update your electoral constituency, public contact channels, and privacy visibility settings.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="<?php echo $publicProfileUrl; ?>" class="btn btn-outline-light rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="bi bi-eye-fill me-1.5"></i> View Public Profile
                </a>
                <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-warning text-dark rounded-pill px-3.5 py-2 fw-bold">
                    <i class="bi bi-speedometer2 me-1.5"></i> Dashboard
                </a>
            </div>
        </div>
    </div>

    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?php echo $msgType; ?> alert-dismissible fade show d-flex align-items-center gap-2 rounded-3 shadow-sm mb-4" role="alert">
            <i class="bi bi-<?php echo $msgType === 'success' ? 'check-circle-fill text-success' : 'exclamation-triangle-fill text-danger'; ?> fs-5"></i>
            <div class="fw-semibold"><?php echo htmlspecialchars($msg); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
        <input type="hidden" name="action" value="update_profile">

        <div class="row g-4">
            
            <!-- Left Form Column (8 cols) -->
            <div class="col-12 col-lg-8">
                
                <!-- Section 1: Personal & Identity -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                        <i class="bi bi-person-badge-fill text-primary me-2"></i> 1. Personal &amp; Identity Details
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Full Name / पूरा नाम <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control form-control-lg rounded-3 fs-6" value="<?php echo htmlspecialchars(($user['full_name'] ?? '') ?: ($user['name'] ?? '')); ?>" placeholder="e.g. Rahul Kumar" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Public Username Handle</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">@</span>
                                <input type="text" name="username_handle" class="form-control form-control-lg rounded-end-3 fs-6" value="<?php echo htmlspecialchars(ltrim($user['username_handle'] ?? '', '@')); ?>" placeholder="rahulkumar">
                            </div>
                            <div class="form-text small text-muted">Used for your shareable public URL: <code>biharelection.com/user/@handle</code></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Profile Photo / Avatar</label>
                            <input type="file" name="profile_image_file" class="form-control rounded-3" accept="image/*">
                            <div class="form-text small text-muted">JPG, PNG, or WEBP. Max 5MB.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Gender / लिंग</label>
                            <select name="gender" class="form-select rounded-3">
                                <option value="">Select Gender</option>
                                <option value="Male" <?php echo ($user['gender'] ?? '') === 'Male' ? 'selected' : ''; ?>>Male / पुरुष</option>
                                <option value="Female" <?php echo ($user['gender'] ?? '') === 'Female' ? 'selected' : ''; ?>>Female / महिला</option>
                                <option value="Other" <?php echo ($user['gender'] ?? '') === 'Other' ? 'selected' : ''; ?>>Other / अन्य</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Educational Qualification / शिक्षा</label>
                            <input type="text" name="education" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['education'] ?? ''); ?>" placeholder="e.g. Graduate, Post Graduate, LLB, MBBS">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Languages Spoken / भाषाएं</label>
                            <input type="text" name="languages" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['languages'] ?? ''); ?>" placeholder="e.g. Hindi, Bhojpuri, Maithili, English">
                        </div>
                    </div>
                </div>

                <!-- Section 2: Electoral & Location Mapping -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                        <i class="bi bi-geo-alt-fill text-primary me-2"></i> 2. Electoral &amp; Geographic Location
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Home District / जिला <span class="text-danger">*</span></label>
                            <select name="district" class="form-select rounded-3" required>
                                <option value="">Select District</option>
                                <?php foreach ($districts as $d): ?>
                                    <option value="<?php echo htmlspecialchars($d['name']); ?>" <?php echo strcasecmp($user['district'] ?? '', $d['name']) === 0 ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($d['name']); ?> (<?php echo htmlspecialchars($d['name_hi'] ?? ''); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Assembly Constituency (Vidhan Sabha)</label>
                            <input type="text" name="constituency" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['constituency'] ?? ''); ?>" placeholder="e.g. 182 Bankipur">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Gram Panchayat / Local Ward</label>
                            <input type="text" name="panchayat" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['panchayat'] ?? ''); ?>" placeholder="e.g. Sandha, Ward 12">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label fw-bold small text-secondary">Full Postal Address / पता</label>
                            <input type="text" name="address" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['address'] ?? ''); ?>" placeholder="e.g. Main Road, Near Block Office, Chapra">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold small text-secondary">Pincode / पिन कोड</label>
                            <input type="text" name="pincode" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['pincode'] ?? ''); ?>" placeholder="e.g. 841301" maxlength="6">
                        </div>
                    </div>
                </div>

                <!-- Section 3: Professional & Public Role -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                        <i class="bi bi-briefcase-fill text-primary me-2"></i> 3. Public Role &amp; Professional Qualifications
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Platform Role / स्तर</label>
                            <select name="role" class="form-select rounded-3">
                                <option value="voter" <?php echo ($user['role'] ?? '') === 'voter' ? 'selected' : ''; ?>>Citizen / Voter (नागरिक / मतदाता)</option>
                                <option value="candidate" <?php echo ($user['role'] ?? '') === 'candidate' ? 'selected' : ''; ?>>Candidate / Leader (प्रत्याशी / नेता)</option>
                                <option value="representative" <?php echo ($user['role'] ?? '') === 'representative' ? 'selected' : ''; ?>>Elected Representative (जनप्रतिनिधि)</option>
                                <option value="mukhiya" <?php echo ($user['role'] ?? '') === 'mukhiya' ? 'selected' : ''; ?>>Mukhiya (मुखिया)</option>
                                <option value="sarpanch" <?php echo ($user['role'] ?? '') === 'sarpanch' ? 'selected' : ''; ?>>Sarpanch (सरपंच)</option>
                                <option value="mla" <?php echo ($user['role'] ?? '') === 'mla' ? 'selected' : ''; ?>>MLA / Vidhan Sabha Member (विधायक)</option>
                                <option value="mp" <?php echo ($user['role'] ?? '') === 'mp' ? 'selected' : ''; ?>>MP / Lok Sabha / Rajya Sabha (सांसद)</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Official Designation / पद</label>
                            <input type="text" name="designation" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['designation'] ?? ''); ?>" placeholder="e.g. Social Worker, Advocate, Mukhiya, Youth Leader">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Organization / Political Party / Business Name</label>
                            <input type="text" name="business_name" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['business_name'] ?? ''); ?>" placeholder="e.g. Independent / NGO / Foundation">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Profession Category</label>
                            <input type="text" name="profession_category" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['profession_category'] ?? ''); ?>" placeholder="e.g. Politics, Legal, Healthcare, Agriculture, Business">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Specialization / Focus Area</label>
                            <input type="text" name="specialization" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['specialization'] ?? ''); ?>" placeholder="e.g. Rural Development, Youth Employment, Panchayati Raj">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Experience (Years)</label>
                            <input type="number" name="experience_years" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['experience_years'] ?? ''); ?>" placeholder="e.g. 5" min="0" max="60">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold small text-secondary">Office / Meeting Hours</label>
                            <input type="text" name="office_hours" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['office_hours'] ?? ''); ?>" placeholder="e.g. 10 AM - 5 PM">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-secondary">About &amp; Public Biography / परिचय</label>
                            <textarea name="bio" rows="4" class="form-control rounded-3" placeholder="Share your public background, public welfare initiatives, vision for your constituency or panchayat..."><?php echo htmlspecialchars($user['bio'] ?: ($user['about'] ?? '')); ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Contact & Social Links -->
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                    <h5 class="fw-bold text-dark mb-4 border-bottom pb-2">
                        <i class="bi bi-globe text-primary me-2"></i> 4. Contact Details &amp; Social Links
                    </h5>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">WhatsApp Number</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light">+91</span>
                                <input type="text" name="whatsapp" class="form-control rounded-end-3" value="<?php echo htmlspecialchars($user['whatsapp'] ?: ($user['mobile'] ?? '')); ?>" placeholder="10-digit number">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary">Email Address / ईमेल</label>
                            <input type="email" name="email" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" placeholder="contact@example.com">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary"><i class="bi bi-twitter-x me-1"></i> Twitter / X Profile URL</label>
                            <input type="url" name="twitter" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['twitter'] ?? ''); ?>" placeholder="https://x.com/username">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary"><i class="bi bi-facebook me-1 text-primary"></i> Facebook Profile URL</label>
                            <input type="url" name="facebook" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['facebook'] ?? ''); ?>" placeholder="https://facebook.com/username">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary"><i class="bi bi-instagram me-1 text-danger"></i> Instagram Profile URL</label>
                            <input type="url" name="instagram" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['instagram'] ?? ''); ?>" placeholder="https://instagram.com/username">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-secondary"><i class="bi bi-linkedin me-1 text-primary"></i> LinkedIn Profile URL</label>
                            <input type="url" name="linkedin" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['linkedin'] ?? ''); ?>" placeholder="https://linkedin.com/in/username">
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold small text-secondary"><i class="bi bi-geo-alt-fill me-1 text-success"></i> Google Maps Office Location Link</label>
                            <input type="url" name="google_maps_link" class="form-control rounded-3" value="<?php echo htmlspecialchars($user['google_maps_link'] ?? ''); ?>" placeholder="https://maps.app.goo.gl/...">
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 fw-bold shadow-sm">
                        <i class="bi bi-check2-circle me-1.5"></i> Save &amp; Update Profile
                    </button>
                    <a href="<?php echo SITE_URL; ?>/dashboard.php" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                        Cancel
                    </a>
                </div>

            </div>

            <!-- Right Sidebar: Privacy & Summary (4 cols) -->
            <div class="col-12 col-lg-4">
                
                <!-- Privacy Settings Box -->
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="bi bi-shield-lock-fill text-warning me-2"></i> 5. Privacy Controls
                    </h5>
                    <p class="small text-muted mb-4">Control what information is visible to other citizens and public visitors:</p>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Profile Visibility</label>
                        <select name="profile_visibility" class="form-select rounded-3">
                            <option value="PUBLIC" <?php echo ($user['profile_visibility'] ?? 'PUBLIC') === 'PUBLIC' ? 'selected' : ''; ?>>🌐 Public (Anyone can view)</option>
                            <option value="PRIVATE" <?php echo ($user['profile_visibility'] ?? 'PUBLIC') === 'PRIVATE' ? 'selected' : ''; ?>>🔒 Private (Hidden from public)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Phone Number Visibility</label>
                        <select name="mobile_visibility" class="form-select rounded-3">
                            <option value="PUBLIC" <?php echo ($user['mobile_visibility'] ?? 'PUBLIC') === 'PUBLIC' ? 'selected' : ''; ?>>Show Phone &amp; Call Button</option>
                            <option value="PRIVATE" <?php echo ($user['mobile_visibility'] ?? 'PUBLIC') === 'PRIVATE' ? 'selected' : ''; ?>>Hide Phone Number</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-secondary">Email Visibility</label>
                        <select name="email_visibility" class="form-select rounded-3">
                            <option value="PUBLIC" <?php echo ($user['email_visibility'] ?? 'PUBLIC') === 'PUBLIC' ? 'selected' : ''; ?>>Show Email &amp; Contact Button</option>
                            <option value="PRIVATE" <?php echo ($user['email_visibility'] ?? 'PUBLIC') === 'PRIVATE' ? 'selected' : ''; ?>>Hide Email Address</option>
                        </select>
                    </div>

                    <div>
                        <label class="form-label fw-bold small text-secondary">Postal Address Visibility</label>
                        <select name="address_visibility" class="form-select rounded-3">
                            <option value="PUBLIC" <?php echo ($user['address_visibility'] ?? 'PUBLIC') === 'PUBLIC' ? 'selected' : ''; ?>>Show Full Postal Address</option>
                            <option value="PRIVATE" <?php echo ($user['address_visibility'] ?? 'PUBLIC') === 'PRIVATE' ? 'selected' : ''; ?>>Hide Full Address</option>
                        </select>
                    </div>
                </div>

                <!-- Account Status Card -->
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-4">
                    <div class="mb-3">
                        <?php 
                        $photoPath = !empty($user['profile_image']) ? $user['profile_image'] : (!empty($user['profile_photo']) ? $user['profile_photo'] : ($user['photo'] ?? ''));
                        if (!empty($photoPath) && file_exists(__DIR__ . '/' . $photoPath)): ?>
                            <img src="<?php echo SITE_URL . '/' . htmlspecialchars($photoPath); ?>" alt="Avatar" class="rounded-circle img-thumbnail shadow-sm mx-auto" style="width: 90px; height: 90px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-2 shadow-sm mx-auto" style="width: 90px; height: 90px;">
                                <?php $dispName = ($user['full_name'] ?? '') ?: ($user['name'] ?? 'Citizen'); ?>
                                <?php echo strtoupper(substr($dispName, 0, 1)); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h6 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($dispName); ?></h6>
                    <p class="small text-muted mb-2">📱 +91 <?php echo htmlspecialchars($user['mobile']); ?></p>
                    <div class="badge bg-success bg-opacity-25 text-success border border-success fw-bold px-3 py-1 mb-3">
                        <i class="bi bi-shield-check me-1"></i> DLT Verified Citizen
                    </div>

                    <?php $comp = getProfileCompletionPercent($user); ?>
                    <div class="w-100 text-start pt-2 border-top">
                        <div class="d-flex justify-content-between small fw-bold mb-1">
                            <span class="text-secondary">Profile Strength</span>
                            <span class="text-primary"><?php echo $comp; ?>%</span>
                        </div>
                        <div class="progress" style="height: 6px;">
                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $comp; ?>%;" aria-valuenow="<?php echo $comp; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </form>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
