<?php
/**
 * BiharElection.com - Environment & Configuration Sample
 * 
 * Instructions:
 * 1. Duplicate / Copy this file to `config.local.php`
 * 2. Fill in your real database credentials, SMS gateway keys, and AdSense IDs.
 * 3. `config.local.php` is ignored by .gitignore and will never be committed to Git.
 */

// =========================================================================
// 1. Database Configuration (MySQL / MariaDB)
// =========================================================================
define('DB_HOST', 'localhost');                  // Database Host (e.g., localhost or 127.0.0.1)
define('DB_NAME', 'your_database_name');         // Database Name
define('DB_USER', 'your_database_user');         // Database Username
define('DB_PASS', 'your_database_password');     // Database Password
define('DB_CHARSET', 'utf8mb4');

// =========================================================================
// 2. SMS Gateway & DLT Template Configuration (OfferPlant Engine)
// =========================================================================
define('SMS_AUTH_KEY', 'your_sms_gateway_auth_key_here'); // Gateway API Authorization Key
define('SMS_SENDER_ID', 'BIHELE');                        // 6-Character DLT Approved Header
define('SMS_TEMPLATE_NAME', 'BIHELE_OTP');                // DLT Template Identifier
define('SMS_API_URL', 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms');

// =========================================================================
// 3. Google AdSense Configuration
// =========================================================================
define('GOOGLE_ADS_ENABLED', false);                      // Set true to enable ads
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-0000000000000000'); // Publisher ID
define('GOOGLE_AD_SLOT_HEADER', '0000000001');            // Top Leaderboard Slot ID
define('GOOGLE_AD_SLOT_INFEED', '0000000002');            // In-Feed Native Slot ID
define('GOOGLE_AD_SLOT_SIDEBAR', '0000000003');           // Sidebar Rectangle Slot ID
define('GOOGLE_AD_SLOT_TABLE', '0000000004');             // In-Table Banner Slot ID
define('GOOGLE_AD_SLOT_FOOTER', '0000000005');            // Footer Banner Slot ID

// =========================================================================
// 4. Admin Security & Initial Setup (Optional)
// =========================================================================
define('DEFAULT_ADMIN_PASS', 'YourSecureAdminPassword2026!'); // Used only during first-time seeder initialization
