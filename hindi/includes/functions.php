<?php
/**
 * Bihar Election - Hindi Portal Core Functions & Setup
 */
if (!defined('IS_HINDI')) {
    define('IS_HINDI', true);
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/auth_helper.php';

if (!defined('HINDI_BASE_URL')) {
    define('HINDI_BASE_URL', SITE_URL . '/hindi/');
}

/**
 * Universal Hindi Base URL helper function
 */
function hindi_base_url(string $path = ''): string {
    if (empty($path)) {
        return HINDI_BASE_URL;
    }
    return rtrim(HINDI_BASE_URL, '/') . '/' . ltrim($path, '/');
}
