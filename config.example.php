<?php
/**
 * BiharElection.com - Example Configuration File
 * Copy this file to `config.local.php` and set your real credentials.
 * DO NOT commit `config.local.php` to Git.
 */

// Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'biharelection_db');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// SMS Gateway & DLT Template Configuration (OfferPlant Engine)
define('SMS_AUTH_KEY', 'YOUR_SMS_GATEWAY_AUTH_KEY');
define('SMS_SENDER_ID', 'BIHELE');
define('SMS_TEMPLATE_NAME', 'BIHELE_OTP');
define('SMS_API_URL', 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms');

// Google AdSense Configuration
define('GOOGLE_ADS_ENABLED', false);
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-XXXXXXXXXXXXXXXX');
define('GOOGLE_AD_SLOT_HEADER', '1001001001');
define('GOOGLE_AD_SLOT_INFEED', '1002002002');
define('GOOGLE_AD_SLOT_SIDEBAR', '1003003003');
define('GOOGLE_AD_SLOT_TABLE', '1004004004');
define('GOOGLE_AD_SLOT_FOOTER', '1005005005');
