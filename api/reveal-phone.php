<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config.php';

// Ensure session is active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$isLoggedIn = (function_exists('isUserLoggedIn') && isUserLoggedIn())
           || !empty($_SESSION['public_user_id'])
           || !empty($_SESSION['admin_logged_in']);

if (!$isLoggedIn) {
    echo json_encode([
        'success' => false,
        'require_login' => true,
        'message' => 'Please login with your mobile number to view contact numbers.'
    ]);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true) ?: $_POST;

$phoneRaw = $data['phone'] ?? $data['phone_token'] ?? '';
$targetName = trim($data['name'] ?? '');

if (empty($phoneRaw)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid phone request.'
    ]);
    exit;
}

// Decode if base64 encoded token
$clean = preg_replace('/[^0-9]/', '', (string)$phoneRaw);
if (strlen($clean) < 8 && base64_decode($phoneRaw, true) !== false) {
    $decoded = base64_decode($phoneRaw);
    $clean = preg_replace('/[^0-9]/', '', (string)$decoded);
}

if (empty($clean)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid phone number format.'
    ]);
    exit;
}

$phone10 = strlen($clean) >= 10 ? substr($clean, -10) : $clean;
$userId = $_SESSION['public_user_id'] ?? null;
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Max daily quota: 10 reveals per day
$maxDaily = 10;
$today = date('Y-m-d');
$pdo = Database::getConnection();

$revealsToday = 0;
$alreadyRevealed = false;

if ($pdo) {
    try {
        // Check if this specific number was already revealed by this user today
        if ($userId) {
            $stmtCheck = $pdo->prepare("SELECT id FROM phone_reveals WHERE user_id = :uid AND phone_number = :phone AND revealed_date = :dt LIMIT 1");
            $stmtCheck->execute([':uid' => $userId, ':phone' => $phone10, ':dt' => $today]);
            $alreadyRevealed = (bool)$stmtCheck->fetch();

            // Count total distinct reveals today for this user
            $stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT phone_number) as total FROM phone_reveals WHERE user_id = :uid AND revealed_date = :dt");
            $stmtCount->execute([':uid' => $userId, ':dt' => $today]);
            $revealsToday = intval($stmtCount->fetchColumn() ?: 0);
        } else {
            $stmtCheck = $pdo->prepare("SELECT id FROM phone_reveals WHERE ip_address = :ip AND phone_number = :phone AND revealed_date = :dt LIMIT 1");
            $stmtCheck->execute([':ip' => $ip, ':phone' => $phone10, ':dt' => $today]);
            $alreadyRevealed = (bool)$stmtCheck->fetch();

            $stmtCount = $pdo->prepare("SELECT COUNT(DISTINCT phone_number) as total FROM phone_reveals WHERE ip_address = :ip AND revealed_date = :dt");
            $stmtCount->execute([':ip' => $ip, ':dt' => $today]);
            $revealsToday = intval($stmtCount->fetchColumn() ?: 0);
        }
    } catch (Throwable $e) {
        error_log("Phone reveal check error: " . $e->getMessage());
    }
} else {
    // Session fallback
    if (!isset($_SESSION['phone_reveals_cache'][$today])) {
        $_SESSION['phone_reveals_cache'][$today] = [];
    }
    $revealedList = $_SESSION['phone_reveals_cache'][$today];
    $alreadyRevealed = in_array($phone10, $revealedList);
    $revealsToday = count($revealedList);
}

// If not already revealed and quota exhausted (>= 10)
if (!$alreadyRevealed && $revealsToday >= $maxDaily) {
    echo json_encode([
        'success' => false,
        'limit_reached' => true,
        'max_daily' => $maxDaily,
        'reveals_today' => $revealsToday,
        'reveals_remaining' => 0,
        'message' => "Daily contact limit of {$maxDaily} numbers reached. Quota resets tomorrow at 12:00 AM."
    ]);
    exit;
}

// Record the reveal if new
if (!$alreadyRevealed) {
    if ($pdo) {
        try {
            $stmtInsert = $pdo->prepare("INSERT INTO phone_reveals (user_id, ip_address, phone_number, target_name, revealed_date) VALUES (:uid, :ip, :phone, :tname, :dt)");
            $stmtInsert->execute([
                ':uid' => $userId,
                ':ip' => $ip,
                ':phone' => $phone10,
                ':tname' => $targetName ?: 'Representative',
                ':dt' => $today
            ]);
            $revealsToday++;
        } catch (Throwable $e) {
            error_log("Phone reveal record error: " . $e->getMessage());
        }
    }
    if (!isset($_SESSION['phone_reveals_cache'][$today])) {
        $_SESSION['phone_reveals_cache'][$today] = [];
    }
    if (!in_array($phone10, $_SESSION['phone_reveals_cache'][$today])) {
        $_SESSION['phone_reveals_cache'][$today][] = $phone10;
    }
}

$remaining = max(0, $maxDaily - $revealsToday);

echo json_encode([
    'success' => true,
    'raw_phone' => $phone10,
    'formatted_phone' => '+91 ' . $phone10,
    'target_name' => $targetName,
    'reveals_today' => $revealsToday,
    'reveals_remaining' => $remaining,
    'max_daily' => $maxDaily,
    'message' => $remaining > 0 ? "Revealed ({$revealsToday}/{$maxDaily} daily views used)" : "Daily quota reached (10/10)"
]);
