<?php
/**
 * BiharElection Admin Authentication & System Helper
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config.php';

// Timezone Setup
date_default_timezone_set('Asia/Kolkata');

/**
 * Get MySQLi connection for admin scripts or fallback to PDO wrapper
 */
function getAdminDB() {
    static $conn = null;
    if ($conn !== null) {
        return $conn;
    }

    try {
        $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            // Local fallback attempt if default Laragon credentials
            $conn = @new mysqli('localhost', 'root', '', DB_NAME);
            if ($conn->connect_error) {
                // Try without db to create
                $tmp = @new mysqli('localhost', 'root', '');
                if (!$tmp->connect_error) {
                    $tmp->query("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                    $conn = @new mysqli('localhost', 'root', '', DB_NAME);
                }
            }
        }
        
        if ($conn && !$conn->connect_error) {
            $conn->set_charset(DB_CHARSET);
            $conn->query("SET time_zone = '+05:30'");
            initAdminTables($conn);
            return $conn;
        }
    } catch (Exception $e) {
        error_log("DB connection error in admin: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Ensure necessary admin tables exist
 */
function initAdminTables($conn) {
    if (!$conn) return;

    // Admin Users Table
    $conn->query("CREATE TABLE IF NOT EXISTS `admin_users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `username` VARCHAR(100) NOT NULL UNIQUE,
        `password` VARCHAR(255) NOT NULL,
        `name` VARCHAR(150) NOT NULL,
        `email` VARCHAR(150) DEFAULT NULL,
        `role` VARCHAR(50) DEFAULT 'admin',
        `status` VARCHAR(20) DEFAULT 'ACTIVE',
        `last_login` DATETIME DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Check if default admin exists
    $chk = $conn->query("SELECT id FROM `admin_users` WHERE `username` = 'admin'");
    if ($chk && $chk->num_rows === 0) {
        $init_pass = defined('DEFAULT_ADMIN_PASS') ? DEFAULT_ADMIN_PASS : 'Admin@ChangeMe2026';
        $default_pwd = password_hash($init_pass, PASSWORD_DEFAULT);
        $conn->query("INSERT INTO `admin_users` (`username`, `password`, `name`, `email`, `role`, `status`) 
                      VALUES ('admin', '$default_pwd', 'Bihar Election Admin', 'admin@biharelection.com', 'superadmin', 'ACTIVE')");
    }

    // Contacts / Leads Table
    $conn->query("CREATE TABLE IF NOT EXISTS `contacts` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(150) NOT NULL,
        `mobile` VARCHAR(50) NOT NULL,
        `email` VARCHAR(150) DEFAULT NULL,
        `district` VARCHAR(100) DEFAULT NULL,
        `constituency` VARCHAR(100) DEFAULT NULL,
        `inquiry_type` VARCHAR(100) DEFAULT 'GENERAL',
        `message` TEXT DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'NEW',
        `notes` TEXT DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Advertisements Table
    $conn->query("CREATE TABLE IF NOT EXISTS `advertisements` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `product_type` VARCHAR(100) NOT NULL,
        `client_name` VARCHAR(150) NOT NULL,
        `contact_phone` VARCHAR(50) NOT NULL,
        `contact_email` VARCHAR(150) DEFAULT NULL,
        `target_entity` VARCHAR(100) DEFAULT NULL,
        `amount` DECIMAL(10,2) DEFAULT 0.00,
        `banner_url` VARCHAR(255) DEFAULT NULL,
        `status` VARCHAR(50) DEFAULT 'PENDING',
        `start_date` DATE DEFAULT NULL,
        `end_date` DATE DEFAULT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // WhatsApp Subscribers Table
    $conn->query("CREATE TABLE IF NOT EXISTS `whatsapp_subscribers` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `phone_number` VARCHAR(50) NOT NULL UNIQUE,
        `district` VARCHAR(100) DEFAULT NULL,
        `is_active` TINYINT(1) DEFAULT 1,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Panchayat Samiti (Tier-2 Block Level) Table
    $conn->query("CREATE TABLE IF NOT EXISTS `panchayat_samiti` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `district` VARCHAR(100) NOT NULL,
        `district_slug` VARCHAR(100) NOT NULL,
        `block` VARCHAR(100) NOT NULL,
        `pramukh_name` VARCHAR(150) DEFAULT NULL,
        `up_pramukh_name` VARCHAR(150) DEFAULT NULL,
        `gender` VARCHAR(20) DEFAULT 'Male',
        `category` VARCHAR(50) DEFAULT 'सामान्य वर्ग',
        `mobile` VARCHAR(50) DEFAULT NULL,
        `address` TEXT DEFAULT NULL,
        `tenure` VARCHAR(50) DEFAULT '2021-2026',
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (`district`),
        INDEX (`block`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // Seed panchayat_samiti from panchayat_samiti_2016 if table empty
    $chk_ps = $conn->query("SELECT id FROM `panchayat_samiti` LIMIT 1");
    if ($chk_ps && $chk_ps->num_rows === 0) {
        $conn->query("INSERT INTO `panchayat_samiti` (`district`, `district_slug`, `block`, `pramukh_name`, `up_pramukh_name`, `tenure`)
                      SELECT `district`, `district_slug`, `block`, `pramukh_2016`, `up_pramukh_2016`, '2021-2026' FROM `panchayat_samiti_2016`");
    }
}

// Global sanitization function
function sanitize($data) {
    $conn = getAdminDB();
    if ($conn) {
        return mysqli_real_escape_string($conn, htmlspecialchars(trim((string)$data)));
    }
    return htmlspecialchars(trim((string)$data), ENT_QUOTES, 'UTF-8');
}

// Check if user is admin
function isAdmin() {
    return isset($_SESSION['admin_auth']) && $_SESSION['admin_auth'] === true;
}

// Require admin login
function requireAdmin() {
    if (!isAdmin()) {
        header("Location: login.php");
        exit();
    }
}

// Flash messaging
function flash($name, $message = '', $class = 'alert alert-success') {
    if (!empty($message)) {
        $_SESSION[$name] = $message;
        $_SESSION[$name . '_class'] = $class;
    } elseif (isset($_SESSION[$name])) {
        $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : 'alert alert-success';
        echo '<div class="' . $class . ' alert-dismissible fade show" role="alert">' . 
             htmlspecialchars($_SESSION[$name]) . 
             '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
        unset($_SESSION[$name]);
        unset($_SESSION[$name . '_class']);
    }
}

// Format Currency in INR
function formatINR($amount) {
    return '₹' . number_format((float)$amount, 2);
}
