<?php
/**
 * BiharElection.com - Public User Authentication & Session Helper
 * Supports Dual Login: Instant Mobile OTP & Password Login
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/sms_helper.php';

/**
 * Initialize public user database tables and ensure all profile columns exist
 */
function initUserTables() {
    $pdo = Database::getConnection();
    if (!$pdo) return;

    try {
        $sql = "CREATE TABLE IF NOT EXISTS `be_users` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(150) DEFAULT NULL,
            `full_name` VARCHAR(100) DEFAULT NULL,
            `mobile` VARCHAR(20) NOT NULL UNIQUE,
            `whatsapp` VARCHAR(20) DEFAULT NULL,
            `email` VARCHAR(100) DEFAULT NULL,
            `password` VARCHAR(255) DEFAULT NULL,
            `password_hash` VARCHAR(255) DEFAULT NULL,
            `role` VARCHAR(50) DEFAULT 'voter',
            `type` VARCHAR(50) DEFAULT 'USER',
            `district` VARCHAR(100) DEFAULT NULL,
            `constituency` VARCHAR(100) DEFAULT NULL,
            `panchayat` VARCHAR(150) DEFAULT NULL,
            `business_name` VARCHAR(150) DEFAULT NULL,
            `designation` VARCHAR(100) DEFAULT NULL,
            `profession_category` VARCHAR(100) DEFAULT NULL,
            `specialization` VARCHAR(255) DEFAULT NULL,
            `education` VARCHAR(150) DEFAULT NULL,
            `gender` VARCHAR(20) DEFAULT NULL,
            `dob` DATE DEFAULT NULL,
            `languages` VARCHAR(255) DEFAULT NULL,
            `experience_years` VARCHAR(50) DEFAULT NULL,
            `office_hours` VARCHAR(150) DEFAULT NULL,
            `address` TEXT DEFAULT NULL,
            `pincode` VARCHAR(10) DEFAULT NULL,
            `bio` TEXT DEFAULT NULL,
            `about` TEXT DEFAULT NULL,
            `profile_image` VARCHAR(255) DEFAULT NULL,
            `profile_photo` VARCHAR(255) DEFAULT NULL,
            `photo` VARCHAR(255) DEFAULT NULL,
            `username_handle` VARCHAR(50) DEFAULT NULL,
            `public_url` VARCHAR(255) DEFAULT NULL,
            `profile_visibility` VARCHAR(20) DEFAULT 'PUBLIC',
            `mobile_visibility` VARCHAR(20) DEFAULT 'PUBLIC',
            `email_visibility` VARCHAR(20) DEFAULT 'PUBLIC',
            `address_visibility` VARCHAR(20) DEFAULT 'PUBLIC',
            `linkedin` VARCHAR(255) DEFAULT NULL,
            `twitter` VARCHAR(255) DEFAULT NULL,
            `facebook` VARCHAR(255) DEFAULT NULL,
            `instagram` VARCHAR(255) DEFAULT NULL,
            `google_maps_link` TEXT DEFAULT NULL,
            `counter` INT DEFAULT 0,
            `status` VARCHAR(20) DEFAULT 'ACTIVE',
            `otp_code` VARCHAR(20) DEFAULT NULL,
            `otp_expiry` DATETIME DEFAULT NULL,
            `is_mobile_verified` TINYINT(1) DEFAULT 0,
            `mobile_status` VARCHAR(20) DEFAULT 'UNVERIFIED',
            `email_status` VARCHAR(20) DEFAULT 'UNVERIFIED',
            `last_login` TIMESTAMP NULL DEFAULT NULL,
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (`mobile`),
            INDEX (`email`),
            INDEX (`username_handle`),
            INDEX (`role`),
            INDEX (`district`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        $pdo->exec($sql);

        // Auto-add any missing columns on existing tables
        $cols = [
            'full_name' => "VARCHAR(100) DEFAULT NULL",
            'whatsapp' => "VARCHAR(20) DEFAULT NULL",
            'username_handle' => "VARCHAR(50) DEFAULT NULL",
            'business_name' => "VARCHAR(150) DEFAULT NULL",
            'designation' => "VARCHAR(100) DEFAULT NULL",
            'profession_category' => "VARCHAR(100) DEFAULT NULL",
            'specialization' => "VARCHAR(255) DEFAULT NULL",
            'education' => "VARCHAR(150) DEFAULT NULL",
            'gender' => "VARCHAR(20) DEFAULT NULL",
            'dob' => "DATE DEFAULT NULL",
            'languages' => "VARCHAR(255) DEFAULT NULL",
            'experience_years' => "VARCHAR(50) DEFAULT NULL",
            'office_hours' => "VARCHAR(150) DEFAULT NULL",
            'address' => "TEXT DEFAULT NULL",
            'pincode' => "VARCHAR(10) DEFAULT NULL",
            'bio' => "TEXT DEFAULT NULL",
            'about' => "TEXT DEFAULT NULL",
            'profile_image' => "VARCHAR(255) DEFAULT NULL",
            'photo' => "VARCHAR(255) DEFAULT NULL",
            'public_url' => "VARCHAR(255) DEFAULT NULL",
            'profile_visibility' => "VARCHAR(20) DEFAULT 'PUBLIC'",
            'mobile_visibility' => "VARCHAR(20) DEFAULT 'PUBLIC'",
            'email_visibility' => "VARCHAR(20) DEFAULT 'PUBLIC'",
            'address_visibility' => "VARCHAR(20) DEFAULT 'PUBLIC'",
            'linkedin' => "VARCHAR(255) DEFAULT NULL",
            'twitter' => "VARCHAR(255) DEFAULT NULL",
            'facebook' => "VARCHAR(255) DEFAULT NULL",
            'instagram' => "VARCHAR(255) DEFAULT NULL",
            'google_maps_link' => "TEXT DEFAULT NULL",
            'counter' => "INT DEFAULT 0",
            'mobile_status' => "VARCHAR(20) DEFAULT 'UNVERIFIED'",
            'email_status' => "VARCHAR(20) DEFAULT 'UNVERIFIED'"
        ];
        foreach ($cols as $col => $type) {
            try {
                $pdo->exec("ALTER TABLE `be_users` ADD COLUMN IF NOT EXISTS `$col` $type");
            } catch (Throwable $e) {}
        }
    } catch (Throwable $e) {
        error_log("Failed to initialize be_users table: " . $e->getMessage());
    }
}

// Auto-run table init
initUserTables();

/**
 * Check if a public user is currently logged in
 */
function isUserLoggedIn() {
    return !empty($_SESSION['public_user_id']) && !empty($_SESSION['public_user_mobile']);
}

/**
 * Get the currently logged-in user data
 */
function getCurrentUser() {
    if (!isUserLoggedIn()) {
        return null;
    }

    $userId = $_SESSION['public_user_id'];
    $pdo = Database::getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE `id` = ? LIMIT 1");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            if ($user) {
                return $user;
            }
        } catch (Throwable $e) {
            error_log("Error fetching user: " . $e->getMessage());
        }
    }

    // Fallback to session cache
    return [
        'id'           => $_SESSION['public_user_id'] ?? 0,
        'name'         => $_SESSION['public_user_name'] ?? 'Citizen',
        'mobile'       => $_SESSION['public_user_mobile'] ?? '',
        'email'        => $_SESSION['public_user_email'] ?? '',
        'role'         => $_SESSION['public_user_role'] ?? 'voter',
        'district'     => $_SESSION['public_user_district'] ?? '',
        'constituency' => $_SESSION['public_user_constituency'] ?? '',
        'panchayat'    => $_SESSION['public_user_panchayat'] ?? '',
    ];
}

/**
 * Require public user login or redirect
 */
function requireUserLogin($redirectUrl = 'login.php') {
    if (!isUserLoggedIn()) {
        $currentUri = $_SERVER['REQUEST_URI'] ?? '';
        if (!empty($currentUri)) {
            $_SESSION['auth_redirect'] = $currentUri;
        }
        header("Location: " . $redirectUrl);
        exit();
    }
}

/**
 * Set user session upon successful login
 */
function setUserSession($user) {
    $_SESSION['public_user_id']           = $user['id'];
    $_SESSION['public_user_name']         = $user['name'];
    $_SESSION['public_user_mobile']       = $user['mobile'];
    $_SESSION['public_user_email']        = $user['email'] ?? '';
    $_SESSION['public_user_role']         = $user['role'] ?? 'voter';
    $_SESSION['public_user_district']     = $user['district'] ?? '';
    $_SESSION['public_user_constituency'] = $user['constituency'] ?? '';
    $_SESSION['public_user_panchayat']    = $user['panchayat'] ?? '';

    // Update last login
    $pdo = Database::getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE `be_users` SET `last_login` = NOW() WHERE `id` = ?");
            $stmt->execute([$user['id']]);
        } catch (Throwable $e) {}
    }
}

/**
 * Generate and send OTP via SMS (DLT Template: BIHELE_OTP)
 *
 * @param string $mobile 10-digit mobile number
 * @param string $name User name
 * @param string $purpose 'login' | 'register' | 'forgot'
 * @return array
 */
function sendUserOTP($mobile, $name = 'Citizen', $purpose = 'login') {
    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }
    if (strlen($mobile) !== 10) {
        return ['status' => 'error', 'msg' => 'Please enter a valid 10-digit mobile number.'];
    }

    $otp = (string)random_int(100000, 999999);
    $expiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Save in session as immediate fallback
    $_SESSION['pending_otp'] = [
        'mobile'  => $mobile,
        'name'    => $name,
        'otp'     => $otp,
        'expiry'  => time() + 600,
        'purpose' => $purpose
    ];

    $pdo = Database::getConnection();
    if ($pdo) {
        try {
            // Check if user already exists
            $stmt = $pdo->prepare("SELECT id, name FROM `be_users` WHERE `mobile` = ? LIMIT 1");
            $stmt->execute([$mobile]);
            $existing = $stmt->fetch();

            if ($existing) {
                $name = $existing['name'] ?: $name;
                $updateStmt = $pdo->prepare("UPDATE `be_users` SET `otp_code` = ?, `otp_expiry` = ? WHERE `id` = ?");
                $updateStmt->execute([$otp, $expiry, $existing['id']]);
            } elseif ($purpose === 'login') {
                // Auto-create basic user account for passwordless OTP login
                $insertStmt = $pdo->prepare("INSERT INTO `be_users` (`name`, `mobile`, `otp_code`, `otp_expiry`, `is_mobile_verified`) VALUES (?, ?, ?, ?, 1)");
                $insertStmt->execute([$name ?: 'Voter', $mobile, $otp, $expiry]);
            }
        } catch (Throwable $e) {
            error_log("OTP DB error: " . $e->getMessage());
        }
    }

    // Send SMS via official Gateway
    $smsRes = sendOTP($mobile, $name ?: 'Citizen', $otp);

    return [
        'status' => 'success',
        'msg'    => 'OTP sent successfully to +91 ' . $mobile,
        'mobile' => $mobile,
        'sms'    => $smsRes
    ];
}

/**
 * Verify OTP entered by user
 */
function verifyUserOTP($mobile, $enteredOtp) {
    $mobile = preg_replace('/[^0-9]/', '', $mobile);
    if (strlen($mobile) === 12 && substr($mobile, 0, 2) === '91') {
        $mobile = substr($mobile, 2);
    }
    $enteredOtp = trim($enteredOtp);

    // 1. Session verification
    if (isset($_SESSION['pending_otp'])) {
        $pending = $_SESSION['pending_otp'];
        if ($pending['mobile'] === $mobile && $pending['otp'] === $enteredOtp && time() <= $pending['expiry']) {
            unset($_SESSION['pending_otp']);
            
            // Fetch or create user
            $pdo = Database::getConnection();
            if ($pdo) {
                $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE `mobile` = ? LIMIT 1");
                $stmt->execute([$mobile]);
                $user = $stmt->fetch();
                if ($user) {
                    $pdo->prepare("UPDATE `be_users` SET `is_mobile_verified` = 1, `otp_code` = NULL WHERE `id` = ?")->execute([$user['id']]);
                    return ['status' => 'success', 'user' => $user];
                }
            }
            return [
                'status' => 'success',
                'user'   => [
                    'id'           => 1,
                    'name'         => $pending['name'] ?? 'Citizen',
                    'mobile'       => $mobile,
                    'role'         => 'voter',
                    'district'     => '',
                    'constituency' => ''
                ]
            ];
        }
    }

    // 2. Database verification
    $pdo = Database::getConnection();
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE `mobile` = ? AND `otp_code` = ? AND `otp_expiry` >= NOW() LIMIT 1");
            $stmt->execute([$mobile, $enteredOtp]);
            $user = $stmt->fetch();

            if ($user) {
                // Clear used OTP
                $pdo->prepare("UPDATE `be_users` SET `is_mobile_verified` = 1, `otp_code` = NULL WHERE `id` = ?")->execute([$user['id']]);
                return ['status' => 'success', 'user' => $user];
            }
        } catch (Throwable $e) {
            error_log("OTP Verification DB error: " . $e->getMessage());
        }
    }

    return ['status' => 'error', 'msg' => 'Invalid or expired OTP. Please try again.'];
}

/**
 * Public User Logout
 */
function logoutUser() {
    unset(
        $_SESSION['public_user_id'],
        $_SESSION['public_user_name'],
        $_SESSION['public_user_mobile'],
        $_SESSION['public_user_email'],
        $_SESSION['public_user_role'],
        $_SESSION['public_user_district'],
        $_SESSION['public_user_constituency'],
        $_SESSION['public_user_panchayat'],
        $_SESSION['pending_otp']
    );
}

/**
 * Alias for getCurrentUser()
 */
function getLoggedInUser() {
    return getCurrentUser();
}

/**
 * Fetch a user by public handle, username, ID, or slug
 */
function getUserByHandle($handle) {
    if (empty($handle)) return null;
    $clean = trim($handle);
    $clean = ltrim($clean, '@');

    $pdo = Database::getConnection();
    if (!$pdo) return null;

    try {
        // 1. By username_handle
        $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE LOWER(`username_handle`) = LOWER(?) OR LOWER(`username_handle`) = LOWER(?) LIMIT 1");
        $stmt->execute([$clean, '@' . $clean]);
        $user = $stmt->fetch();
        if ($user) return $user;

        // 2. By public_url
        $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE LOWER(`public_url`) = LOWER(?) LIMIT 1");
        $stmt->execute([$clean]);
        $user = $stmt->fetch();
        if ($user) return $user;

        // 3. By numeric ID
        if (is_numeric($clean)) {
            $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE `id` = ? LIMIT 1");
            $stmt->execute([(int)$clean]);
            $user = $stmt->fetch();
            if ($user) return $user;
        }

        // 4. By exact name or slug
        $stmt = $pdo->prepare("SELECT * FROM `be_users` WHERE LOWER(REPLACE(`name`, ' ', '-')) = LOWER(?) OR LOWER(REPLACE(`full_name`, ' ', '-')) = LOWER(?) LIMIT 1");
        $stmt->execute([$clean, $clean]);
        $user = $stmt->fetch();
        if ($user) return $user;

    } catch (Throwable $e) {
        error_log("getUserByHandle error: " . $e->getMessage());
    }

    return null;
}

/**
 * Increment profile view counter
 */
function incrementUserProfileViews($userId) {
    $pdo = Database::getConnection();
    if ($pdo && (int)$userId > 0) {
        try {
            $stmt = $pdo->prepare("UPDATE `be_users` SET `counter` = COALESCE(`counter`, 0) + 1 WHERE `id` = ?");
            $stmt->execute([(int)$userId]);
        } catch (Throwable $e) {}
    }
}

/**
 * Update comprehensive user profile
 */
function updateUserProfile($userId, array $data) {
    $pdo = Database::getConnection();
    if (!$pdo || (int)$userId <= 0) return false;

    // Allowed updatable columns
    $allowed = [
        'name', 'full_name', 'username_handle', 'email', 'whatsapp', 'district',
        'constituency', 'panchayat', 'business_name', 'designation', 'profession_category',
        'specialization', 'education', 'gender', 'dob', 'languages', 'experience_years',
        'office_hours', 'address', 'pincode', 'bio', 'about', 'profile_image',
        'profile_photo', 'photo', 'public_url', 'profile_visibility', 'mobile_visibility',
        'email_visibility', 'address_visibility', 'linkedin', 'twitter', 'facebook',
        'instagram', 'google_maps_link'
    ];

    $updates = [];
    $params = [];

    foreach ($allowed as $field) {
        if (array_key_exists($field, $data)) {
            $val = $data[$field];
            if ($field === 'username_handle' && !empty($val)) {
                $val = '@' . ltrim(trim($val), '@');
            }
            $updates[] = "`$field` = ?";
            $params[] = ($val === '' ? null : $val);
        }
    }

    if (empty($updates)) return false;

    $params[] = (int)$userId;
    $sql = "UPDATE `be_users` SET " . implode(', ', $updates) . " WHERE `id` = ?";

    try {
        $stmt = $pdo->prepare($sql);
        return $stmt->execute($params);
    } catch (Throwable $e) {
        error_log("updateUserProfile error: " . $e->getMessage());
        return false;
    }
}

/**
 * Upload User Profile Avatar
 */
function uploadUserProfilePhoto($file, $userId) {
    if (empty($file) || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($fileInfo, $file['tmp_name']);
    finfo_close($fileInfo);

    if (!in_array($mime, $allowedMimes)) {
        return null;
    }

    $ext = match($mime) {
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
        default      => 'jpg'
    };

    $uploadDir = __DIR__ . '/../uploads/profiles/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $fileName = 'user_' . (int)$userId . '_' . time() . '.' . $ext;
    $targetPath = $uploadDir . $fileName;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return 'uploads/profiles/' . $fileName;
    }

    return null;
}

/**
 * Calculate user profile completion score
 */
function getProfileCompletionPercent($user) {
    if (empty($user)) return 0;
    $fields = [
        'name', 'mobile', 'email', 'district', 'constituency', 'panchayat',
        'designation', 'bio', 'education', 'profile_image', 'whatsapp'
    ];
    $completed = 0;
    foreach ($fields as $f) {
        if (!empty($user[$f])) $completed++;
    }
    return (int)round(($completed / count($fields)) * 100);
}

