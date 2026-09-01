<?php
/**
 * BiharElection.com - Global Platform Configuration & Database Provider
 */

define('SITE_NAME', 'Bihar Election');
define('SITE_TAGLINE', 'Bihar\'s Premier Election Data & Political Intelligence Platform');

// =========================================================================
// Environment & Dynamic Base URL (Online Production vs Offline Localhost)
// =========================================================================
$serverHost = $_SERVER['HTTP_HOST'] ?? 'localhost';
$isLocalEnv = in_array($serverHost, ['localhost', '127.0.0.1', '::1']) 
    || (function_exists('str_ends_with') && (str_ends_with($serverHost, '.test') || str_ends_with($serverHost, '.local') || str_ends_with($serverHost, '.laragon')))
    || (substr($serverHost, -5) === '.test' || substr($serverHost, -6) === '.local');

define('IS_LOCAL', $isLocalEnv);

if ($isLocalEnv) {
    // Offline / Localhost / Laragon Environment
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
        || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) 
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https://' : 'http://';
    
    $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
    $reqUri = str_replace('\\', '/', $_SERVER['REQUEST_URI'] ?? '');
    
    $basePath = '';
    // If accessed via localhost or 127.0.0.1, default subfolder is /biharelection
    if (in_array($serverHost, ['localhost', '127.0.0.1', '::1']) || strpos($scriptDir, '/biharelection') !== false || strpos($reqUri, '/biharelection') !== false) {
        if (!str_ends_with($serverHost, '.test') && !str_ends_with($serverHost, '.local') && !str_ends_with($serverHost, '.laragon')) {
            $basePath = '/biharelection';
        }
    }
    $computedBase = rtrim($protocol . $serverHost . $basePath, '/');
} else {
    // Online / Live Production Environment
    $computedBase = 'https://biharelection.com';
}

if (!defined('SITE_URL')) {
    define('SITE_URL', $computedBase);
}
if (!defined('BASE_URL')) {
    define('BASE_URL', SITE_URL);
}

/**
 * Universal Base URL helper function
 * Usage: base_url('assets/image/logo.png') -> http://localhost/biharelection/assets/image/logo.png
 *
 * @param string $path
 * @return string
 */
function base_url(string $path = ''): string {
    if (empty($path)) {
        return SITE_URL;
    }
    return SITE_URL . '/' . ltrim($path, '/');
}

// Official Channels & Social Media
define('WHATSAPP_CHANNEL_URL', 'https://whatsapp.com/channel/0029VaoSYQlBadmZzfwQMy0q');
define('INSTAGRAM_URL', 'https://instagram.com/BiharElectionAI');
define('FACEBOOK_URL', 'https://facebook.com/BiharElectionAI');
define('TWITTER_URL', 'https://x.com/BiharElectionAI');
define('YOUTUBE_URL', 'https://youtube.com/@BiharElectionAI');

define('CONTACT_PHONE', '+91 98765 43210');
define('CONTACT_EMAIL', 'contact@biharelection.com');

// Google AdSense Configuration (Insert your AdSense Publisher ID & Slot IDs here)
define('GOOGLE_ADS_ENABLED', true);
define('GOOGLE_ADSENSE_CLIENT', 'ca-pub-XXXXXXXXXXXXXXXX'); // e.g. ca-pub-1234567890123456
define('GOOGLE_AD_SLOT_HEADER', '1001001001');
define('GOOGLE_AD_SLOT_INFEED', '1002002002');
define('GOOGLE_AD_SLOT_SIDEBAR', '1003003003');
define('GOOGLE_AD_SLOT_TABLE', '1004004004');
define('GOOGLE_AD_SLOT_FOOTER', '1005005005');

// SMS Gateway & DLT Template Configuration (OfferPlant Engine)
define('SMS_AUTH_KEY', 'b0e99bea1fa7d15e27e1c5fd8e3c868');
define('SMS_SENDER_ID', 'BIHELE');
define('SMS_TEMPLATE_NAME', 'BIHELE_OTP');
define('SMS_API_URL', 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms');
define('SMS_OTP_TEMPLATE', "Dear {#var#},\nYour OTP / EVC / Password is: {#var#}\nVisit https://biharelection.com\n  \nRegards\nBIHELE\nOfferPlant");

// Production & Local Database Credentials
define('DB_HOST', 'localhost');
define('DB_NAME', 'u305984835_biharelection');
define('DB_USER', 'u305984835_biharelection');
define('DB_PASS', 'Election@@2026');
define('DB_CHARSET', 'utf8mb4');

// Load SMS helper
if (file_exists(__DIR__ . '/includes/sms_helper.php')) {
    require_once __DIR__ . '/includes/sms_helper.php';
}

class Database {
    private static $pdo = null;
    private static $connectionAttempted = false;

    public static function getConnection() {
        if (self::$pdo !== null) {
            return self::$pdo;
        }

        if (self::$connectionAttempted) {
            return null;
        }

        self::$connectionAttempted = true;

        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            return self::$pdo;
        } catch (Throwable $e) {
            // Local fallback attempt
            try {
                $dsn = "mysql:host=localhost;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                self::$pdo = new PDO($dsn, 'root', '', [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
                return self::$pdo;
            } catch (Throwable $e2) {
                error_log('Database connection unavailable: ' . $e->getMessage());
                return null;
            }
        }
    }
}

class DataProvider {
    private static $districts = null;
    private static $constituencies = null;
    private static $candidates = null;
    private static $panchayats = null;
    private static $news = null;

    public static function getDistricts() {
        if (self::$districts === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_districts ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            foreach ($rows as &$r) {
                                if (isset($r['ac_list']) && is_string($r['ac_list'])) {
                                    $r['ac_list'] = json_decode($r['ac_list'], true) ?: [];
                                }
                            }
                            self::$districts = $rows;
                            return self::$districts;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }

            $json = file_get_contents(__DIR__ . '/assets/data/districts.json');
            self::$districts = json_decode($json, true) ?: [];
        }
        return self::$districts;
    }

    public static function getDistrictBySlug($slug) {
        if (empty($slug)) return null;
        $slugClean = strtolower(trim((string)$slug));
        $slugClean = preg_replace('/^ac-/', '', $slugClean);
        $districts = self::getDistricts();
        foreach ($districts as $d) {
            $dSlug = strtolower($d['slug'] ?? '');
            $dName = strtolower(str_replace(' ', '-', $d['name'] ?? ''));
            $dHq = strtolower(str_replace(' ', '-', $d['headquarters'] ?? ''));
            if ($dSlug === $slugClean || $dName === $slugClean || $dHq === $slugClean) {
                return $d;
            }
        }
        // Aliases & HQ mapping (e.g. chapra / chhapra -> saran)
        if ($slugClean === 'chapra' || $slugClean === 'chhapra') {
            foreach ($districts as $d) {
                if (($d['slug'] ?? '') === 'saran') return $d;
            }
        }
        return null;
    }

    public static function getConstituencies() {
        if (self::$constituencies === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_constituencies ORDER BY ac_no ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            foreach ($rows as &$r) {
                                if (isset($r['election_2020']) && is_string($r['election_2020'])) $r['election_2020'] = json_decode($r['election_2020'], true);
                                if (isset($r['election_2015']) && is_string($r['election_2015'])) $r['election_2015'] = json_decode($r['election_2015'], true);
                                if (isset($r['key_issues']) && is_string($r['key_issues'])) $r['key_issues'] = json_decode($r['key_issues'], true);
                                if (isset($r['blocks']) && is_string($r['blocks'])) $r['blocks'] = json_decode($r['blocks'], true);
                                if (isset($r['candidates_2026_expected']) && is_string($r['candidates_2026_expected'])) $r['candidates_2026_expected'] = json_decode($r['candidates_2026_expected'], true);
                            }
                            self::$constituencies = $rows;
                            return self::$constituencies;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }

            $jsonPath = __DIR__ . '/assets/data/constituencies.json';
            $json = file_get_contents($jsonPath);
            self::$constituencies = json_decode($json, true) ?: [];
        }
        return self::$constituencies;
    }

    public static function getConstituencyByAcNumber($acNumber) {
        $acs = self::getConstituencies();
        foreach ($acs as $ac) {
            if ((int)$ac['ac_no'] === (int)$acNumber) {
                return $ac;
            }
        }
        return null;
    }

    public static function getConstituencyBySlug($slug) {
        if (empty($slug)) return null;
        $slugClean = strtolower(trim((string)$slug));
        $slugClean = preg_replace('/^ac-/', '', $slugClean);

        // Check if starts with numeric AC number e.g. "118-chapra"
        if (preg_match('/^(\d+)(?:-.*)?$/', $slugClean, $matches)) {
            $ac = self::getConstituencyByAcNumber((int)$matches[1]);
            if ($ac) return $ac;
        }

        $acs = self::getConstituencies();
        // Exact slug / name match
        foreach ($acs as $ac) {
            $acSlug = strtolower($ac['slug'] ?? '');
            $acName = strtolower(str_replace(' ', '-', $ac['name'] ?? ''));
            if ($acSlug === $slugClean || $acName === $slugClean || (string)($ac['ac_no'] ?? '') === $slugClean) {
                return $ac;
            }
        }
        // Substring / alias check
        foreach ($acs as $ac) {
            $acSlug = strtolower($ac['slug'] ?? '');
            if (strpos($slugClean, $acSlug) !== false || strpos($acSlug, $slugClean) !== false) {
                return $ac;
            }
        }
        return null;
    }

    public static function getConstituenciesByDistrict($districtName) {
        $acs = self::getConstituencies();
        $filtered = [];
        foreach ($acs as $ac) {
            if (strcasecmp($ac['district'], $districtName) === 0) {
                $filtered[] = $ac;
            }
        }
        return $filtered;
    }

    public static function getCandidates() {
        if (self::$candidates === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_candidates ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            foreach ($rows as &$r) {
                                if (isset($r['social_links']) && is_string($r['social_links'])) $r['social_links'] = json_decode($r['social_links'], true);
                                if (isset($r['election_record']) && is_string($r['election_record'])) $r['election_record'] = json_decode($r['election_record'], true);
                            }
                            self::$candidates = $rows;
                            return self::$candidates;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }

            $jsonPath = __DIR__ . '/assets/data/candidates.json';
            self::$candidates = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?: []) : [];
        }
        return self::$candidates;
    }

    public static function getCandidateBySlug($slug) {
        $candidates = self::getCandidates();
        foreach ($candidates as $c) {
            if (strtolower($c['slug']) === strtolower($slug)) {
                return $c;
            }
        }
        return null;
    }

    private static $mukhiya = null;
    private static $sarpanch = null;
    private static $panchayatSummary = null;

    public static function getPanchayatData($districtSlugOrName = null, $blockName = null) {
        if (self::$panchayats === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_panchayats ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        // Only use DB rows if it has the updated full master dataset
                        if (!empty($rows) && count($rows) > 100 && isset($rows[0]['district_slug'])) {
                            self::$panchayats = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }

            if (self::$panchayats === null) {
                $json = file_get_contents(__DIR__ . '/assets/data/panchayats.json');
                self::$panchayats = json_decode($json, true) ?: [];
            }
        }

        $results = self::$panchayats;
        if ($districtSlugOrName !== null) {
            $needle = strtolower(trim($districtSlugOrName));
            $results = array_values(array_filter($results, function($p) use ($needle) {
                return strtolower($p['district_slug'] ?? '') === $needle
                    || strtolower($p['district'] ?? '') === $needle
                    || strtolower($p['district_hi'] ?? '') === $needle;
            }));
        }

        if ($blockName !== null) {
            $bNeedle = strtolower(trim($blockName));
            $results = array_values(array_filter($results, function($p) use ($bNeedle) {
                return strtolower($p['block'] ?? '') === $bNeedle;
            }));
        }

        return $results;
    }

    public static function getMukhiyaData($districtSlugOrName = null) {
        if (self::$mukhiya === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_mukhiyas ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows) && count($rows) > 100) {
                            self::$mukhiya = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }

            if (self::$mukhiya === null) {
                $jsonPath = __DIR__ . '/assets/data/mukhiya_directory.json';
                if (file_exists($jsonPath)) {
                    self::$mukhiya = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$mukhiya = [];
                }
            }
        }

        if ($districtSlugOrName === null) {
            return self::$mukhiya;
        }

        $needle = strtolower(trim($districtSlugOrName));
        return array_values(array_filter(self::$mukhiya, function($m) use ($needle) {
            return strtolower($m['district_slug'] ?? '') === $needle
                || strtolower($m['district'] ?? '') === $needle
                || strtolower($m['district_hi'] ?? '') === $needle;
        }));
    }

    public static function getSarpanchData($districtSlugOrName = null) {
        if (self::$sarpanch === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_sarpanchs ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows) && count($rows) > 100) {
                            self::$sarpanch = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }

            if (self::$sarpanch === null) {
                $jsonPath = __DIR__ . '/assets/data/sarpanch_directory.json';
                if (file_exists($jsonPath)) {
                    self::$sarpanch = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$sarpanch = [];
                }
            }
        }

        if ($districtSlugOrName === null) {
            return self::$sarpanch;
        }

        $needle = strtolower(trim($districtSlugOrName));
        return array_values(array_filter(self::$sarpanch, function($s) use ($needle) {
            return strtolower($s['district_slug'] ?? '') === $needle
                || strtolower($s['district'] ?? '') === $needle
                || strtolower($s['district_hi'] ?? '') === $needle;
        }));
    }

    public static function getPanchayatSummary($districtSlug = null) {
        if (self::$panchayatSummary === null) {
            $jsonPath = __DIR__ . '/assets/data/panchayat_summary.json';
            if (file_exists($jsonPath)) {
                self::$panchayatSummary = json_decode(file_get_contents($jsonPath), true) ?: [];
            } else {
                self::$panchayatSummary = [];
            }
        }

        if ($districtSlug === null) {
            return self::$panchayatSummary;
        }

        $slug = strtolower(trim($districtSlug));
        return self::$panchayatSummary[$slug] ?? null;
    }

    private static $zilaMembers = null;
    private static $zilaOfficials = null;
    private static $zilaSummary = null;

    public static function getZilaParishadMembers($districtSlugOrName = null) {
        if (self::$zilaMembers === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_zila_parishad_members ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$zilaMembers = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }
            if (self::$zilaMembers === null) {
                $jsonPath = __DIR__ . '/assets/data/zila_parishad_members.json';
                if (file_exists($jsonPath)) {
                    self::$zilaMembers = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$zilaMembers = [];
                }
            }
        }

        if ($districtSlugOrName === null) {
            return self::$zilaMembers;
        }

        $needle = strtolower(trim($districtSlugOrName));
        return array_values(array_filter(self::$zilaMembers, function($m) use ($needle) {
            return strtolower($m['district_slug'] ?? '') === $needle
                || strtolower($m['district'] ?? '') === $needle
                || strtolower($m['district_hi'] ?? '') === $needle;
        }));
    }

    public static function getZilaParishadOfficials($districtSlugOrName = null) {
        if (self::$zilaOfficials === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_zila_parishad_officials ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$zilaOfficials = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }
            if (self::$zilaOfficials === null) {
                $jsonPath = __DIR__ . '/assets/data/zila_parishad_officials.json';
                if (file_exists($jsonPath)) {
                    self::$zilaOfficials = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$zilaOfficials = [];
                }
            }
        }

        if ($districtSlugOrName === null) {
            return self::$zilaOfficials;
        }

        $needle = strtolower(trim($districtSlugOrName));
        return array_values(array_filter(self::$zilaOfficials, function($o) use ($needle) {
            return strtolower($o['district_slug'] ?? '') === $needle
                || strtolower($o['district'] ?? '') === $needle
                || strtolower($o['district_hi'] ?? '') === $needle;
        }));
    }

    public static function getZilaParishadSummary($districtSlug = null) {
        if (self::$zilaSummary === null) {
            $jsonPath = __DIR__ . '/assets/data/zila_parishad_summary.json';
            if (file_exists($jsonPath)) {
                self::$zilaSummary = json_decode(file_get_contents($jsonPath), true) ?: [];
            } else {
                self::$zilaSummary = [];
            }
        }

        if ($districtSlug === null) {
            return self::$zilaSummary;
        }

        $slug = strtolower(trim($districtSlug));
        return self::$zilaSummary[$slug] ?? null;
    }

    private static $zila2016 = null;
    private static $panchayatSamiti2016 = null;

    public static function getZilaParishad2016($districtSlugOrName = null) {
        if (self::$zila2016 === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_zila_parishad_2016 ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$zila2016 = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }
            if (self::$zila2016 === null) {
                $jsonPath = __DIR__ . '/assets/data/zila_parishad_2016.json';
                if (file_exists($jsonPath)) {
                    self::$zila2016 = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$zila2016 = [];
                }
            }
        }

        if ($districtSlugOrName === null) {
            return self::$zila2016;
        }

        $needle = strtolower(trim($districtSlugOrName));
        return array_values(array_filter(self::$zila2016, function($z) use ($needle) {
            return strtolower($z['district_slug'] ?? '') === $needle
                || strtolower($z['district'] ?? '') === $needle
                || strtolower($z['district_hi'] ?? '') === $needle;
        }));
    }

    public static function getPanchayatSamiti2016($districtSlugOrName = null) {
        if (self::$panchayatSamiti2016 === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_panchayat_samiti_2016 ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$panchayatSamiti2016 = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }
            if (self::$panchayatSamiti2016 === null) {
                $jsonPath = __DIR__ . '/assets/data/panchayat_samiti_2016.json';
                if (file_exists($jsonPath)) {
                    self::$panchayatSamiti2016 = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$panchayatSamiti2016 = [];
                }
            }
        }

        if ($districtSlugOrName === null) {
            return self::$panchayatSamiti2016;
        }

        $needle = strtolower(trim($districtSlugOrName));
        return array_values(array_filter(self::$panchayatSamiti2016, function($b) use ($needle) {
            return strtolower($b['district_slug'] ?? '') === $needle
                || strtolower($b['district'] ?? '') === $needle
                || strtolower($b['district_hi'] ?? '') === $needle;
        }));
    }

    private static $mukhiyas2016 = null;

    public static function getMukhiyas2016($districtSlugOrName = null, $block = null) {
        if (self::$mukhiyas2016 === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_mukhiyas_2016 ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows) && count($rows) > 100) {
                            self::$mukhiyas2016 = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Fallback to JSON
                }
            }
            if (self::$mukhiyas2016 === null) {
                $jsonPath = __DIR__ . '/assets/data/mukhiyas_2016.json';
                if (file_exists($jsonPath)) {
                    self::$mukhiyas2016 = json_decode(file_get_contents($jsonPath), true) ?: [];
                } else {
                    self::$mukhiyas2016 = [];
                }
            }
        }

        if ($districtSlugOrName === null && $block === null) {
            return self::$mukhiyas2016;
        }

        $needle = $districtSlugOrName ? strtolower(trim($districtSlugOrName)) : null;
        $needleBlock = $block ? strtolower(trim($block)) : null;

        return array_values(array_filter(self::$mukhiyas2016, function($m) use ($needle, $needleBlock) {
            $matchD = true;
            if ($needle !== null) {
                $matchD = strtolower($m['district_slug'] ?? '') === $needle
                    || strtolower($m['district'] ?? '') === $needle
                    || strtolower($m['district_hi'] ?? '') === $needle;
            }

            $matchB = true;
            if ($needleBlock !== null) {
                $matchB = strtolower($m['block'] ?? '') === $needleBlock;
            }

            return $matchD && $matchB;
        }));
    }

    private static $mpsLokSabha = null;
    public static function getLokSabhaMps() {
        if (self::$mpsLokSabha === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_mps_loksabha ORDER BY pc_no ASC");
                    if ($stmt) self::$mpsLokSabha = $stmt->fetchAll();
                } catch (Throwable $e) {}
            }
            if (self::$mpsLokSabha === null) {
                $json = file_get_contents(__DIR__ . '/assets/data/mps_loksabha.json');
                self::$mpsLokSabha = json_decode($json, true) ?: [];
            }
        }
        return self::$mpsLokSabha;
    }

    private static $mpsRajyaSabha = null;
    public static function getRajyaSabhaMps() {
        if (self::$mpsRajyaSabha === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_mps_rajyasabha ORDER BY sno ASC");
                    if ($stmt) self::$mpsRajyaSabha = $stmt->fetchAll();
                } catch (Throwable $e) {}
            }
            if (self::$mpsRajyaSabha === null) {
                $json = file_get_contents(__DIR__ . '/assets/data/mps_rajyasabha.json');
                self::$mpsRajyaSabha = json_decode($json, true) ?: [];
            }
        }
        return self::$mpsRajyaSabha;
    }

    private static $mlcs = null;
    public static function getMlcs() {
        if (self::$mlcs === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_mlcs ORDER BY id ASC");
                    if ($stmt) self::$mlcs = $stmt->fetchAll();
                } catch (Throwable $e) {}
            }
            if (self::$mlcs === null) {
                $json = file_get_contents(__DIR__ . '/assets/data/mlcs.json');
                self::$mlcs = json_decode($json, true) ?: [];
            }
        }
        return self::$mlcs;
    }

    private static $mlas2015 = null;
    public static function getMlas2015($acNoOrSlug = null) {
        if (self::$mlas2015 === null) {
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_mlas_2015 ORDER BY ac_no ASC");
                    if ($stmt) self::$mlas2015 = $stmt->fetchAll();
                } catch (Throwable $e) {}
            }
            if (self::$mlas2015 === null) {
                $json = file_get_contents(__DIR__ . '/assets/data/mlas_2015.json');
                self::$mlas2015 = json_decode($json, true) ?: [];
            }
        }

        if ($acNoOrSlug === null) {
            return self::$mlas2015;
        }

        $needle = strtolower(trim((string)$acNoOrSlug));
        foreach (self::$mlas2015 as $m) {
            if ((string)$m['ac_no'] === $needle || strtolower($m['slug'] ?? '') === $needle) {
                return $m;
            }
        }
        return null;
    }

    public static function getNews() {
        if (self::$news === null) {
            $json = file_get_contents(__DIR__ . '/assets/data/news.json');
            self::$news = json_decode($json, true) ?: [];
        }
        return self::$news;
    }

    private static $censusBihar = null;
    private static $censusDistricts = null;
    private static $censusSubDistricts = null;

    public static function getCensusBiharSummary() {
        if (self::$censusBihar === null) {
            $jsonPath = __DIR__ . '/assets/data/census_bihar.json';
            self::$censusBihar = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?: []) : [];
        }
        return self::$censusBihar;
    }

    public static function getCensusDistricts() {
        if (self::$censusDistricts === null) {
            $jsonPath = __DIR__ . '/assets/data/census_districts.json';
            self::$censusDistricts = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?: []) : [];
        }
        return self::$censusDistricts;
    }

    public static function getCensusDistrict($districtSlugOrName) {
        $districts = self::getCensusDistricts();
        $needle = strtolower(trim((string)$districtSlugOrName));
        foreach ($districts as $slug => $data) {
            if ($slug === $needle || strtolower($data['name'] ?? '') === $needle) {
                return $data;
            }
        }
        return null;
    }

    public static function getCensusSubDistricts($districtSlugOrName = null) {
        if (self::$censusSubDistricts === null) {
            $jsonPath = __DIR__ . '/assets/data/census_subdistricts.json';
            self::$censusSubDistricts = file_exists($jsonPath) ? (json_decode(file_get_contents($jsonPath), true) ?: []) : [];
        }
        if ($districtSlugOrName === null) {
            return self::$censusSubDistricts;
        }
        $needle = strtolower(trim((string)$districtSlugOrName));
        return self::$censusSubDistricts[$needle] ?? [];
    }
}

// Meta helper for SEO
function renderSeoMeta($title, $description, $keywords = '', $canonical = '', $ogImage = '') {
    $title = htmlspecialchars($title . ' | ' . SITE_NAME);
    $description = htmlspecialchars($description);
    $keywords = htmlspecialchars($keywords ?: 'Bihar Election 2026, Bihar Assembly Election, 243 Constituencies, Bihar Panchayat Election, Bihar MLA list, Bihar Political Data');
    $canonical = $canonical ?: SITE_URL;
    $ogImage = $ogImage ?: SITE_URL . '/assets/img/og-biharelection.jpg';

    echo <<<HTML
    <title>{$title}</title>
    <meta name="description" content="{$description}">
    <meta name="keywords" content="{$keywords}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{$canonical}">
    
    <!-- Open Graph / Social Media -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{$canonical}">
    <meta property="og:title" content="{$title}">
    <meta property="og:description" content="{$description}">
    <meta property="og:image" content="{$ogImage}">
    
    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{$title}">
    <meta name="twitter:description" content="{$description}">
    <meta name="twitter:image" content="{$ogImage}">
HTML;
}

/**
 * Render Responsive Google AdSense Placement Slot
 * 
 * @param string $slotType 'leaderboard'|'in_feed'|'sidebar'|'table_banner'|'footer_banner'
 * @param string $slotId Custom slot ID if overriding
 * @param string $customClass Additional CSS class
 */
function renderGoogleAd($slotType = 'leaderboard', $slotId = '', $customClass = '') {
    if (!defined('GOOGLE_ADS_ENABLED') || !GOOGLE_ADS_ENABLED) {
        return;
    }
    
    $client = defined('GOOGLE_ADSENSE_CLIENT') ? GOOGLE_ADSENSE_CLIENT : '';
    $isLive = (!empty($client) && $client !== 'ca-pub-XXXXXXXXXXXXXXXX');

    $styles = [
        'leaderboard' => [
            'class' => 'ad-leaderboard-slot',
            'label' => 'Advertisement / विज्ञापन',
            'style' => 'display:block; min-height: 90px; text-align: center;',
            'format' => 'auto',
            'dims' => '728 × 90 Responsive Leaderboard'
        ],
        'in_feed' => [
            'class' => 'ad-infeed-slot',
            'label' => 'Sponsored / विज्ञापन',
            'style' => 'display:block; min-height: 100px; text-align: center;',
            'format' => 'fluid',
            'dims' => 'Responsive In-Feed Native Ad Unit'
        ],
        'sidebar' => [
            'class' => 'ad-sidebar-slot',
            'label' => 'Advertisement',
            'style' => 'display:block; min-height: 250px; text-align: center;',
            'format' => 'rectangle',
            'dims' => '300 × 250 Medium Rectangle / Skyscraper'
        ],
        'table_banner' => [
            'class' => 'ad-table-slot',
            'label' => 'Advertisement / विज्ञापन',
            'style' => 'display:block; min-height: 90px; text-align: center;',
            'format' => 'horizontal',
            'dims' => 'Responsive Table Roster Banner'
        ],
        'footer_banner' => [
            'class' => 'ad-footer-slot',
            'label' => 'Advertisement',
            'style' => 'display:block; min-height: 90px; text-align: center;',
            'format' => 'auto',
            'dims' => '728 × 90 Responsive Footer Banner'
        ],
    ];

    $cfg = $styles[$slotType] ?? $styles['leaderboard'];
    $slotId = $slotId ?: (defined('GOOGLE_AD_SLOT_' . strtoupper($slotType)) ? constant('GOOGLE_AD_SLOT_' . strtoupper($slotType)) : '1001001001');

    echo '<div class="ad-slot-wrapper ' . htmlspecialchars($cfg['class']) . ' ' . htmlspecialchars($customClass) . '">';
    echo '<div class="ad-slot-header"><span class="ad-badge">' . htmlspecialchars($cfg['label']) . '</span></div>';
    echo '<div class="ad-slot-inner">';
    
    if ($isLive) {
        echo '<ins class="adsbygoogle"
                 style="' . $cfg['style'] . '"
                 data-ad-client="' . htmlspecialchars($client) . '"
                 data-ad-slot="' . htmlspecialchars($slotId) . '"
                 data-ad-format="' . htmlspecialchars($cfg['format']) . '"
                 data-full-width-responsive="true"></ins>';
        echo '<script>(adsbygoogle = window.adsbygoogle || []).push({});</script>';
    } else {
        echo '<div class="ad-placeholder-box">
                <div class="ad-placeholder-content">
                    <div class="ad-icon"><i class="bi bi-badge-ad text-warning fs-4"></i></div>
                    <div class="ad-text-wrap">
                        <span class="ad-title">Google AdSense Space</span>
                        <span class="ad-dims">' . htmlspecialchars($cfg['dims']) . '</span>
                    </div>
                </div>
              </div>';
    }
    
    echo '</div>';
    echo '</div>';
}

/**
 * Masks a mobile number for privacy (e.g. 9876543210 -> 98******10)
 */
function maskMobileNumber($phone) {
    if (empty($phone)) return '';
    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
    $len = strlen($clean);
    if ($len >= 10) {
        $last10 = substr($clean, -10);
        return substr($last10, 0, 2) . '******' . substr($last10, -2);
    } elseif ($len >= 6) {
        return substr($clean, 0, 2) . str_repeat('*', $len - 4) . substr($clean, -2);
    } elseif ($len > 0) {
        return substr($clean, 0, 1) . str_repeat('*', max(1, $len - 1));
    }
    return '';
}

/**
 * Clean SEO URL Routing Helpers
 */
function getMlaUrl($ac) {
    if (is_array($ac)) {
        $acNo = (int)($ac['ac_no'] ?? 0);
        $slug = $ac['slug'] ?? strtolower(str_replace(' ', '-', $ac['name'] ?? ''));
        return SITE_URL . "/mla/{$acNo}-{$slug}";
    } elseif (is_numeric($ac)) {
        $acObj = DataProvider::getConstituencyByAcNumber($ac);
        if ($acObj) {
            $slug = $acObj['slug'] ?? strtolower(str_replace(' ', '-', $acObj['name'] ?? ''));
            return SITE_URL . "/mla/{$ac}-{$slug}";
        }
        return SITE_URL . "/mla/{$ac}";
    }
    return SITE_URL . "/mla/" . trim((string)$ac, '/');
}

function getConstituencyUrl($ac) {
    return getMlaUrl($ac);
}

function getMpUrl($slug = '') {
    return $slug ? SITE_URL . "/mp/{$slug}" : SITE_URL . "/mp";
}

function getMlcUrl($slug = '') {
    return $slug ? SITE_URL . "/mlc/{$slug}" : SITE_URL . "/mlc";
}

function getDistrictUrl($slug, $subpath = '') {
    $slugClean = strtolower(trim((string)$slug));
    if ($subpath) {
        $subClean = strtolower(trim((string)$subpath));
        return SITE_URL . "/district/{$slugClean}/{$subClean}";
    }
    return SITE_URL . "/district/{$slugClean}";
}

function getPanchayatUrl($districtSlug = '', $panchayatSlug = '') {
    if ($districtSlug && $panchayatSlug) {
        return SITE_URL . "/panchayat/{$districtSlug}/{$panchayatSlug}";
    } elseif ($districtSlug) {
        return SITE_URL . "/panchayat/{$districtSlug}";
    }
    return SITE_URL . "/panchayat";
}

function getMukhiyaUrl($districtSlug = '', $panchayatSlug = '') {
    if ($districtSlug && $panchayatSlug) {
        return SITE_URL . "/mukhiya/{$districtSlug}/{$panchayatSlug}";
    } elseif ($districtSlug) {
        return SITE_URL . "/mukhiya/{$districtSlug}";
    }
    return SITE_URL . "/mukhiya";
}

function getSarpanchUrl($districtSlug = '', $panchayatSlug = '') {
    if ($districtSlug && $panchayatSlug) {
        return SITE_URL . "/sarpanch/{$districtSlug}/{$panchayatSlug}";
    } elseif ($districtSlug) {
        return SITE_URL . "/sarpanch/{$districtSlug}";
    }
    return SITE_URL . "/sarpanch";
}

function getZilaParishadUrl($districtSlug = '') {
    return $districtSlug ? SITE_URL . "/zila-parishad/{$districtSlug}" : SITE_URL . "/zila-parishad";
}

function getPanchayatSamitiUrl($districtSlug = '', $blockSlug = '') {
    if ($districtSlug && $blockSlug) {
        return SITE_URL . "/panchayat-samiti/{$districtSlug}/{$blockSlug}";
    } elseif ($districtSlug) {
        return SITE_URL . "/panchayat-samiti/{$districtSlug}";
    }
    return SITE_URL . "/panchayat-samiti";
}

function getCensusUrl($districtSlug = '', $subdistrictSlug = '') {
    if ($districtSlug && $subdistrictSlug) {
        return SITE_URL . "/census/{$districtSlug}/{$subdistrictSlug}";
    } elseif ($districtSlug) {
        return SITE_URL . "/census/{$districtSlug}";
    }
    return SITE_URL . "/census";
}

function getAdvertiseUrl($params = []) {
    $baseUrl = SITE_URL . "/advertise";
    if (!empty($params)) {
        if (is_array($params)) {
            return $baseUrl . '?' . http_build_query($params);
        } elseif (is_string($params) || is_numeric($params)) {
            return $baseUrl . '?' . ltrim((string)$params, '?');
        }
    }
    return $baseUrl;
}

function getBlogUrl($slug = '') {
    return $slug ? SITE_URL . "/blog/" . ltrim($slug, '/') : SITE_URL . "/blog/";
}




