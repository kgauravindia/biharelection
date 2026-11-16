<?php
/**
 * BiharElection - Application Version Information
 */
require_once __DIR__ . '/config.php';

$versionInfo = [
    'app_name' => SITE_NAME,
    'version' => defined('APP_VERSION') ? APP_VERSION : '3.2',
    'full_version' => trim(@file_get_contents(__DIR__ . '/VERSION') ?: '3.2.0'),
    'release_date' => '2026-11-16',
    'environment' => IS_LOCAL ? 'development' : 'production',
    'status' => 'stable'
];

if (isset($_GET['format']) && $_GET['format'] === 'json') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($versionInfo, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    exit;
}

if (!defined('HEADER_INCLUDED')) {
    header('Content-Type: text/plain; charset=utf-8');
    echo "BiharElection Platform v" . $versionInfo['version'] . " (" . $versionInfo['release_date'] . ")\n";
    exit;
}
