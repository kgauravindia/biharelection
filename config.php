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
define('TELEGRAM_URL', 'https://t.me/BiharElectionAI');

define('CONTACT_PHONE', '+91 98765 43210');
define('CONTACT_EMAIL', 'contact@biharelection.com');

// =========================================================================
// Load Local / Environment Configuration Overrides (if present)
// =========================================================================
if (file_exists(__DIR__ . '/config.local.php')) {
    require_once __DIR__ . '/config.local.php';
}

// Google AdSense Configuration (Default placeholders)
if (!defined('GOOGLE_ADS_ENABLED')) define('GOOGLE_ADS_ENABLED', false);
if (!defined('GOOGLE_ADSENSE_CLIENT')) define('GOOGLE_ADSENSE_CLIENT', getenv('GOOGLE_ADSENSE_CLIENT') ?: 'ca-pub-XXXXXXXXXXXXXXXX');
if (!defined('GOOGLE_AD_SLOT_HEADER')) define('GOOGLE_AD_SLOT_HEADER', getenv('GOOGLE_AD_SLOT_HEADER') ?: '1001001001');
if (!defined('GOOGLE_AD_SLOT_INFEED')) define('GOOGLE_AD_SLOT_INFEED', getenv('GOOGLE_AD_SLOT_INFEED') ?: '1002002002');
if (!defined('GOOGLE_AD_SLOT_SIDEBAR')) define('GOOGLE_AD_SLOT_SIDEBAR', getenv('GOOGLE_AD_SLOT_SIDEBAR') ?: '1003003003');
if (!defined('GOOGLE_AD_SLOT_TABLE')) define('GOOGLE_AD_SLOT_TABLE', getenv('GOOGLE_AD_SLOT_TABLE') ?: '1004004004');
if (!defined('GOOGLE_AD_SLOT_FOOTER')) define('GOOGLE_AD_SLOT_FOOTER', getenv('GOOGLE_AD_SLOT_FOOTER') ?: '1005005005');

// SMS Gateway & DLT Template Configuration (OfferPlant Engine)
if (!defined('SMS_AUTH_KEY')) define('SMS_AUTH_KEY', getenv('SMS_AUTH_KEY') ?: 'b0e99bea1fa7d15e27e1c5fd8e3c868');
if (!defined('SMS_SENDER_ID')) define('SMS_SENDER_ID', getenv('SMS_SENDER_ID') ?: 'BIHELE');
if (!defined('SMS_TEMPLATE_NAME')) define('SMS_TEMPLATE_NAME', getenv('SMS_TEMPLATE_NAME') ?: 'BIHELE_OTP');
if (!defined('SMS_API_URL')) define('SMS_API_URL', getenv('SMS_API_URL') ?: 'http://msg.morg.in/rest/services/sendSMS/sendGroupSms');
if (!defined('SMS_OTP_TEMPLATE')) define('SMS_OTP_TEMPLATE', getenv('SMS_OTP_TEMPLATE') ?: "Dear {#var#},\nYour OTP / EVC / Password is: {#var#}\nVisit https://biharelection.com\n  \nRegards\nBIHELE\nOfferPlant");

// Database Credentials
if (!defined('IS_LOCAL') || !IS_LOCAL) {
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'u216129624_biharelection');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'u216129624_biharelection');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : 'Election@@2026');
    if (!defined('DB_CHARSET')) define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
} else {
    if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'biharelection_db');
    if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
    if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
    if (!defined('DB_CHARSET')) define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');
}

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
            $host = defined('DB_HOST') ? DB_HOST : 'localhost';
            $dbname = defined('DB_NAME') ? DB_NAME : '';
            $user = defined('DB_USER') ? DB_USER : 'root';
            $pass = defined('DB_PASS') ? DB_PASS : '';
            $charset = defined('DB_CHARSET') ? DB_CHARSET : 'utf8mb4';

            if (empty($dbname)) {
                return null;
            }

            $dsn = "mysql:host=" . $host . ";dbname=" . $dbname . ";charset=" . $charset;
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];
            self::$pdo = new PDO($dsn, $user, $pass, $options);
            return self::$pdo;
        } catch (Throwable $e) {
            // Local fallback attempt if on local machine
            if (defined('IS_LOCAL') && IS_LOCAL) {
                try {
                    $dsn = "mysql:host=localhost;dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
                    self::$pdo = new PDO($dsn, 'root', '', [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false,
                    ]);
                    return self::$pdo;
                } catch (Throwable $e2) {}
            }
            error_log('Database connection error: ' . $e->getMessage());
            return null;
        }
    }
}

class DataProvider {
    private static $districts = null;
    private static $constituencies = null;
    private static $candidates = null;
    private static $panchayats = null;
    private static $mukhiya = null;
    private static $sarpanch = null;
    private static $panchayatSummary = null;
    private static $zilaMembers = null;
    private static $zilaOfficials = null;
    private static $zilaSummary = null;
    private static $zila2016 = null;
    private static $panchayatSamiti2016 = null;
    private static $mukhiyas2016 = null;
    private static $mpsLokSabha = null;
    private static $mpsRajyaSabha = null;
    private static $mlcs = null;
    private static $mlas2015 = null;
    private static $news = null;
    private static $censusBihar = null;
    private static $censusDistricts = null;
    private static $censusSubDistricts = null;

    public static function getDistricts() {
        if (self::$districts === null) {
            self::$districts = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM districts ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            foreach ($rows as &$r) {
                                if (isset($r['ac_list']) && is_string($r['ac_list'])) {
                                    $r['ac_list'] = json_decode($r['ac_list'], true) ?: [];
                                }
                            }
                            self::$districts = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching districts: " . $e->getMessage());
                }
            }
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
            self::$constituencies = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM constituencies ORDER BY ac_no ASC");
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
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching constituencies: " . $e->getMessage());
                }
            }
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
            self::$candidates = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM candidates ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            foreach ($rows as &$r) {
                                if (isset($r['social_links']) && is_string($r['social_links'])) $r['social_links'] = json_decode($r['social_links'], true);
                                if (isset($r['election_record']) && is_string($r['election_record'])) $r['election_record'] = json_decode($r['election_record'], true);
                            }
                            self::$candidates = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching candidates: " . $e->getMessage());
                }
            }
        }
        return self::$candidates;
    }

    public static function getCandidateBySlug($slug) {
        if (empty($slug)) return null;
        $slugClean = strtolower(trim((string)$slug));
        $candidates = self::getCandidates();
        foreach ($candidates as $c) {
            if (strtolower($c['slug'] ?? '') === $slugClean) {
                return $c;
            }
        }
        return null;
    }

    public static function getByeElectionDetailedResults($acNo = null, $year = null) {
        $pdo = Database::getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT * FROM bye_election_detailed_results WHERE 1=1";
            $params = [];
            if ($acNo !== null) {
                $sql .= " AND ac_no = :ac_no";
                $params[':ac_no'] = (int)$acNo;
            }
            if ($year !== null) {
                $sql .= " AND year = :year";
                $params[':year'] = (int)$year;
            }
            $sql .= " ORDER BY year DESC, votes_total DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching bye_election_detailed_results: " . $e->getMessage());
            return [];
        }
    }

    public static function getElectionDetailedResults($acNo = null, $year = null) {
        $pdo = Database::getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT * FROM election_detailed_results WHERE 1=1";
            $params = [];
            if ($acNo !== null) {
                $sql .= " AND ac_no = :ac_no";
                $params[':ac_no'] = (int)$acNo;
            }
            if ($year !== null) {
                $sql .= " AND year = :year";
                $params[':year'] = (int)$year;
            }
            $sql .= " ORDER BY year DESC, votes_total DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching election_detailed_results: " . $e->getMessage());
            return [];
        }
    }

    public static function getElectionSuccessfulCandidates($acNo = null, $year = null) {
        $pdo = Database::getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT * FROM election_successful_candidates WHERE 1=1";
            $params = [];
            if ($acNo !== null) {
                $sql .= " AND ac_no = :ac_no";
                $params[':ac_no'] = (int)$acNo;
            }
            if ($year !== null) {
                $sql .= " AND year = :year";
                $params[':year'] = (int)$year;
            }
            $sql .= " ORDER BY year DESC, ac_no ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $acNo !== null ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching election_successful_candidates: " . $e->getMessage());
            return [];
        }
    }

    public static function getElectionAcSummary($acNo = null, $year = null) {
        $pdo = Database::getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT * FROM election_ac_summary WHERE 1=1";
            $params = [];
            if ($acNo !== null) {
                $sql .= " AND ac_no = :ac_no";
                $params[':ac_no'] = (int)$acNo;
            }
            if ($year !== null) {
                $sql .= " AND year = :year";
                $params[':year'] = (int)$year;
            }
            $sql .= " ORDER BY year DESC, ac_no ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $acNo !== null ? $stmt->fetch(PDO::FETCH_ASSOC) : $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching election_ac_summary: " . $e->getMessage());
            return [];
        }
    }

    public static function getElectionPartyPerformance($year = null) {
        $pdo = Database::getConnection();
        if (!$pdo) return [];
        try {
            $sql = "SELECT * FROM election_party_performance WHERE 1=1";
            $params = [];
            if ($year !== null) {
                $sql .= " AND year = :year";
                $params[':year'] = (int)$year;
            }
            $sql .= " ORDER BY year DESC, won DESC, votes_polled DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            error_log("Error fetching election_party_performance: " . $e->getMessage());
            return [];
        }
    }

    public static function getPanchayats($districtSlug = null) {
        if (self::$panchayats === null) {
            self::$panchayats = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM panchayats ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$panchayats = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching panchayats: " . $e->getMessage());
                }
            }
        }

        if ($districtSlug === null) {
            return self::$panchayats;
        }

        $needle = strtolower(trim($districtSlug));
        return array_values(array_filter(self::$panchayats, function($p) use ($needle) {
            return strtolower($p['district_slug'] ?? '') === $needle
                || strtolower($p['district'] ?? '') === $needle;
        }));
    }

    public static function getPanchayatData($districtSlugOrName = null, $blockName = null) {
        $results = self::getPanchayats($districtSlugOrName);
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
            self::$mukhiya = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM mukhiyas ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$mukhiya = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Try alternative table name be_mukhiya
                    try {
                        $stmt = $pdo->query("SELECT * FROM be_mukhiya ORDER BY id ASC");
                        if ($stmt) self::$mukhiya = $stmt->fetchAll() ?: [];
                    } catch (Throwable $e2) {}
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
            self::$sarpanch = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM sarpanchs ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$sarpanch = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    // Try alternative table name be_sarpanch
                    try {
                        $stmt = $pdo->query("SELECT * FROM be_sarpanch ORDER BY id ASC");
                        if ($stmt) self::$sarpanch = $stmt->fetchAll() ?: [];
                    } catch (Throwable $e2) {}
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
            self::$panchayatSummary = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_panchayat_summary");
                    if ($stmt) {
                        while ($r = $stmt->fetch()) {
                            $slugKey = strtolower($r['district_slug'] ?? $r['slug'] ?? '');
                            if ($slugKey) self::$panchayatSummary[$slugKey] = $r;
                        }
                    }
                } catch (Throwable $e) {}
            }
        }

        if ($districtSlug === null) {
            return self::$panchayatSummary;
        }

        $slug = strtolower(trim($districtSlug));
        return self::$panchayatSummary[$slug] ?? null;
    }

    public static function getZilaParishadMembers($districtSlugOrName = null) {
        if (self::$zilaMembers === null) {
            self::$zilaMembers = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM zila_parishad_members ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$zilaMembers = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching ZP members: " . $e->getMessage());
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
            self::$zilaOfficials = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM zila_parishad_officials ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$zilaOfficials = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching ZP officials: " . $e->getMessage());
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
            self::$zilaSummary = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_zila_parishad_summary");
                    if ($stmt) {
                        while ($r = $stmt->fetch()) {
                            $slugKey = strtolower($r['district_slug'] ?? $r['slug'] ?? '');
                            if ($slugKey) self::$zilaSummary[$slugKey] = $r;
                        }
                    }
                } catch (Throwable $e) {}
            }
        }

        if ($districtSlug === null) {
            return self::$zilaSummary;
        }

        $slug = strtolower(trim($districtSlug));
        return self::$zilaSummary[$slug] ?? null;
    }

    public static function getZilaParishad2016($districtSlugOrName = null) {
        if (self::$zila2016 === null) {
            self::$zila2016 = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM zila_parishad_2016 ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$zila2016 = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching ZP 2016: " . $e->getMessage());
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
            self::$panchayatSamiti2016 = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM panchayat_samiti_2016 ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$panchayatSamiti2016 = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching Samiti 2016: " . $e->getMessage());
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

    public static function getMukhiyas2016($districtSlugOrName = null, $block = null) {
        if (self::$mukhiyas2016 === null) {
            self::$mukhiyas2016 = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM mukhiyas_2016 ORDER BY id ASC");
                    if ($stmt) {
                        $rows = $stmt->fetchAll();
                        if (!empty($rows)) {
                            self::$mukhiyas2016 = $rows;
                        }
                    }
                } catch (Throwable $e) {
                    error_log("Error fetching Mukhiyas 2016: " . $e->getMessage());
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

    public static function getLokSabhaMps() {
        if (self::$mpsLokSabha === null) {
            self::$mpsLokSabha = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM mps_loksabha ORDER BY pc_no ASC");
                    if ($stmt) self::$mpsLokSabha = $stmt->fetchAll() ?: [];
                } catch (Throwable $e) {}
            }
        }
        return self::$mpsLokSabha;
    }

    public static function getRajyaSabhaMps() {
        if (self::$mpsRajyaSabha === null) {
            self::$mpsRajyaSabha = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM mps_rajyasabha ORDER BY sno ASC");
                    if ($stmt) self::$mpsRajyaSabha = $stmt->fetchAll() ?: [];
                } catch (Throwable $e) {}
            }
        }
        return self::$mpsRajyaSabha;
    }

    public static function getMlcs() {
        if (self::$mlcs === null) {
            self::$mlcs = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM mlcs ORDER BY id ASC");
                    if ($stmt) self::$mlcs = $stmt->fetchAll() ?: [];
                } catch (Throwable $e) {}
            }
        }
        return self::$mlcs;
    }

    public static function getMlas2015($acNoOrSlug = null) {
        if (self::$mlas2015 === null) {
            self::$mlas2015 = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM mlas_2015 ORDER BY ac_no ASC");
                    if ($stmt) self::$mlas2015 = $stmt->fetchAll() ?: [];
                } catch (Throwable $e) {}
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
            self::$news = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM be_news ORDER BY published_date DESC LIMIT 50");
                    if ($stmt) self::$news = $stmt->fetchAll() ?: [];
                } catch (Throwable $e) {}
            }
        }
        return self::$news;
    }

    public static function getCensusBiharSummary() {
        if (self::$censusBihar === null) {
            self::$censusBihar = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM census_districts WHERE district_slug = 'bihar' OR slug = 'bihar' LIMIT 1");
                    if ($stmt) self::$censusBihar = $stmt->fetch() ?: [];
                } catch (Throwable $e) {}
            }
        }
        return self::$censusBihar;
    }

    public static function getCensusDistricts() {
        if (self::$censusDistricts === null) {
            self::$censusDistricts = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM census_districts");
                    if ($stmt) {
                        while ($r = $stmt->fetch()) {
                            $k = strtolower($r['district_slug'] ?? $r['slug'] ?? '');
                            if ($k) self::$censusDistricts[$k] = $r;
                        }
                    }
                } catch (Throwable $e) {}
            }
        }
        return self::$censusDistricts;
    }

    public static function getCensusDistrict($districtSlugOrName) {
        $districts = self::getCensusDistricts();
        $needle = strtolower(trim((string)$districtSlugOrName));
        foreach ($districts as $slug => $data) {
            if ($slug === $needle || strtolower($data['name'] ?? $data['district_name'] ?? '') === $needle) {
                return $data;
            }
        }
        return null;
    }

    public static function getCensusSubDistricts($districtSlugOrName = null) {
        if (self::$censusSubDistricts === null) {
            self::$censusSubDistricts = [];
            $pdo = Database::getConnection();
            if ($pdo) {
                try {
                    $stmt = $pdo->query("SELECT * FROM census_subdistricts ORDER BY sub_district ASC");
                    if ($stmt) {
                        while ($r = $stmt->fetch()) {
                            $dSlug = strtolower($r['district_slug'] ?? '');
                            if ($dSlug) {
                                self::$censusSubDistricts[$dSlug][] = $r;
                            }
                        }
                    }
                } catch (Throwable $e) {}
            }
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

    // On live/production environment, never render placeholder boxes if real AdSense client is not set
    if (!$isLive && (!defined('IS_LOCAL') || !IS_LOCAL)) {
        return;
    }

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
 * Unconditionally masks all mobile numbers everywhere across the platform (e.g. 98******10)
 */
function maskMobileNumber($phone, $forceMask = true) {
    if (empty($phone)) return '';
    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
    if (empty($clean)) return '';

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
 * Masks an email address for privacy (e.g. nitish.kumar@bihar.gov.in -> ni*****r@bihar.gov.in)
 */
function maskEmailAddress($email) {
    $email = trim((string)$email);
    if (empty($email)) return '';
    if (strpos($email, '@') === false) return '******';
    
    list($user, $domain) = explode('@', $email, 2);
    $uLen = strlen($user);
    if ($uLen <= 2) {
        $maskedUser = substr($user, 0, 1) . '***';
    } elseif ($uLen <= 4) {
        $maskedUser = substr($user, 0, 1) . '**' . substr($user, -1);
    } else {
        $maskedUser = substr($user, 0, 2) . str_repeat('*', min(6, $uLen - 3)) . substr($user, -1);
    }
    return $maskedUser . '@' . $domain;
}

/**
 * Renders interactive phone reveal badge:
 * - When guest clicks: Prompts Citizen Login Modal
 * - When logged-in citizen clicks: Instantly reveals full contact number & enables 1-click calling
 */
function renderMaskedPhoneButton($phone, $targetName = '') {
    if (empty($phone)) return '';
    $clean = preg_replace('/[^0-9]/', '', (string)$phone);
    if (empty($clean)) return '';
    
    $masked = maskMobileNumber($clean);
    $isLoggedIn = (function_exists('isUserLoggedIn') && isUserLoggedIn())
               || !empty($_SESSION['public_user_id'])
               || !empty($_SESSION['admin_logged_in']);

    $loginUrl = SITE_URL . "/login?redirect=" . urlencode($_SERVER['REQUEST_URI'] ?? '');
    
    if ($isLoggedIn) {
        $fullNumber = strlen($clean) >= 10 ? substr($clean, -10) : $clean;
        return '
        <div class="contact-reveal-container d-inline-block">
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5 small d-inline-flex align-items-center gap-1 reveal-phone-btn shadow-none" 
                    data-phone="' . htmlspecialchars($fullNumber, ENT_QUOTES) . '" 
                    data-name="' . htmlspecialchars($targetName, ENT_QUOTES) . '"
                    onclick="revealPhoneNumber(this)" 
                    title="Click to reveal full phone number">
                <i class="bi bi-telephone-fill"></i>
                <span class="phone-text font-monospace">' . htmlspecialchars($masked) . '</span>
                <i class="bi bi-eye-fill ms-1 opacity-75"></i>
            </button>
        </div>';
    } else {
        return '
        <div class="contact-reveal-container d-inline-block">
            <a href="' . htmlspecialchars($loginUrl, ENT_QUOTES) . '" 
               class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 small text-secondary d-inline-flex align-items-center gap-1 text-decoration-none guest-phone-btn" 
               data-bs-toggle="modal" 
               data-bs-target="#citizenLoginPhoneModal"
               data-target-name="' . htmlspecialchars($targetName, ENT_QUOTES) . '"
               title="Login to view full contact number">
                <i class="bi bi-lock-fill text-warning"></i>
                <span class="phone-text font-monospace text-muted">' . htmlspecialchars($masked) . '</span>
                <span class="badge bg-warning bg-opacity-25 text-dark" style="font-size: 0.65rem;">Login</span>
            </a>
        </div>';
    }
}

/**
 * Fetch contacts seen/revealed by a citizen user
 */
function getUserPhoneReveals($userId, $limit = 100, $onlyToday = false) {
    $pdo = Database::getConnection();
    if (!$pdo || empty($userId)) return [];
    try {
        if ($onlyToday) {
            $stmt = $pdo->prepare("SELECT * FROM phone_reveals WHERE user_id = :uid AND revealed_date = CURDATE() ORDER BY id DESC LIMIT :lim");
        } else {
            $stmt = $pdo->prepare("SELECT * FROM phone_reveals WHERE user_id = :uid ORDER BY id DESC LIMIT :lim");
        }
        $stmt->bindValue(':uid', (int)$userId, PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log("Error fetching user phone reveals: " . $e->getMessage());
        return [];
    }
}

/**
 * Count distinct phone numbers revealed today by a user
 */
function getUserTodayRevealCount($userId) {
    $pdo = Database::getConnection();
    if (!$pdo || empty($userId)) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT phone_number) FROM phone_reveals WHERE user_id = :uid AND revealed_date = CURDATE()");
        $stmt->execute([':uid' => (int)$userId]);
        return intval($stmt->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return 0;
    }
}

/**
 * Count total distinct phone numbers revealed by a user across all time
 */
function getUserTotalRevealCount($userId) {
    $pdo = Database::getConnection();
    if (!$pdo || empty($userId)) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(DISTINCT phone_number) FROM phone_reveals WHERE user_id = :uid");
        $stmt->execute([':uid' => (int)$userId]);
        return intval($stmt->fetchColumn() ?: 0);
    } catch (Throwable $e) {
        return 0;
    }
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

/**
 * Helper function to slugify text including Hindi/Devanagari scripts
 */
function slugify($text) {
    if (empty($text)) return '';

    // If string is already plain ASCII / alphanumeric with spaces/hyphens
    if (preg_match('/^[a-zA-Z0-9\s_-]+$/', (string)$text)) {
        return strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', (string)$text), '-'));
    }

    static $vowels = [
        'अ' => 'a', 'आ' => 'aa', 'इ' => 'i', 'ई' => 'ee', 'उ' => 'u', 'ऊ' => 'oo',
        'ऋ' => 'ri', 'ए' => 'e', 'ऐ' => 'ai', 'ओ' => 'o', 'औ' => 'au',
        'अं' => 'an', 'अः' => 'ah', 'ऑ' => 'o', 'ऍ' => 'e'
    ];
    static $matras = [
        'ा' => 'a', 'ि' => 'i', 'ी' => 'i', 'ु' => 'u', 'ू' => 'u', 'ृ' => 'ri',
        'े' => 'e', 'ै' => 'ai', 'ो' => 'o', 'ौ' => 'au', 'ं' => 'n', 'ँ' => 'n',
        'ः' => 'h', '्' => '', 'ॉ' => 'o', 'ॅ' => 'e', '़' => ''
    ];
    static $consonants = [
        'क' => 'k', 'ख' => 'kh', 'ग' => 'g', 'घ' => 'gh', 'ङ' => 'ng',
        'च' => 'ch', 'छ' => 'chh', 'ज' => 'j', 'झ' => 'jh', 'ञ' => 'ny',
        'ट' => 't', 'ठ' => 'th', 'ड' => 'd', 'ढ' => 'dh', 'ण' => 'n',
        'त' => 't', 'थ' => 'th', 'द' => 'd', 'ध' => 'dh', 'न' => 'n',
        'प' => 'p', 'फ' => 'ph', 'ब' => 'b', 'भ' => 'bh', 'म' => 'm',
        'य' => 'y', 'र' => 'r', 'ल' => 'l', 'व' => 'v', 'श' => 'sh',
        'ष' => 'sh', 'स' => 's', 'ह' => 'h', 'क्ष' => 'ksh', 'त्र' => 'tr', 'ज्ञ' => 'gy',
        'ड़' => 'r', 'ढ़' => 'rh', 'फ़' => 'f', 'फ़' => 'f', 'ज़' => 'z', 'ज़' => 'z', 
        'क़' => 'q', 'ख़' => 'kh', 'ग़' => 'gh', 'ड़' => 'r', 'ढ़' => 'rh'
    ];

    $chars = preg_split('//u', (string)$text, -1, PREG_SPLIT_NO_EMPTY);
    $out = '';
    $len = count($chars);

    for ($i = 0; $i < $len; $i++) {
        $c = $chars[$i];
        if (isset($vowels[$c])) {
            $out .= $vowels[$c];
        } elseif (isset($consonants[$c])) {
            $next = $chars[$i + 1] ?? '';
            $out .= $consonants[$c];
            if (!isset($matras[$next]) && $next !== '्' && $i + 1 < $len && $next !== ' ' && $next !== '-' && $next !== '_' && $next !== '(' && $next !== ')' && $next !== '.' && $next !== '/') {
                $out .= 'a';
            }
        } elseif (isset($matras[$c])) {
            $out .= $matras[$c];
        } elseif (preg_match('/[a-zA-Z0-9]/', $c)) {
            $out .= strtolower($c);
        } elseif ($c === ' ' || $c === '-' || $c === '_' || $c === '/' || $c === ',' || $c === '.') {
            $out .= '-';
        }
    }

    return strtolower(trim(preg_replace('/-+/', '-', $out), '-'));
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

function getPanchayatUrl($districtSlug = '', $blockSlugOrPanchayat = '', $panchayatSlug = '') {
    $d = slugify($districtSlug);
    if ($panchayatSlug) {
        $b = slugify($blockSlugOrPanchayat);
        $p = slugify($panchayatSlug);
        if ($d && $b && $p) {
            return SITE_URL . "/{$d}/{$b}/{$p}";
        }
    }
    $p = slugify($blockSlugOrPanchayat);
    if ($d && $p) {
        return SITE_URL . "/panchayat/{$d}/{$p}";
    } elseif ($d) {
        return SITE_URL . "/panchayat/{$d}";
    }
    return SITE_URL . "/panchayat";
}

function getMukhiyaUrl($districtSlug = '', $panchayatSlug = '') {
    return getPanchayatUrl($districtSlug, $panchayatSlug);
}

function getSarpanchUrl($districtSlug = '', $panchayatSlug = '') {
    return getPanchayatUrl($districtSlug, $panchayatSlug);
}

function getZilaParishadUrl($districtSlug = '', $wardNo = '') {
    $d = slugify($districtSlug);
    $w = trim((string)$wardNo);
    if ($d && $w !== '') {
        return SITE_URL . "/zila-parishad/{$d}/{$w}";
    } elseif ($d) {
        return SITE_URL . "/zila-parishad/{$d}";
    }
    return SITE_URL . "/zila-parishad";
}

function getPanchayatSamitiUrl($districtSlug = '', $blockSlug = '') {
    $d = slugify($districtSlug);
    $b = slugify($blockSlug);
    if ($d && $b) {
        return SITE_URL . "/panchayat-samiti/{$d}/{$b}";
    } elseif ($d) {
        return SITE_URL . "/panchayat-samiti/{$d}";
    }
    return SITE_URL . "/panchayat-samiti";
}

function getBlockUrl($districtSlug = '', $blockSlug = '') {
    $d = slugify($districtSlug);
    $b = slugify($blockSlug);
    if ($d && $b) {
        return SITE_URL . "/block/{$d}/{$b}";
    } elseif ($b) {
        return SITE_URL . "/block/{$b}";
    } elseif ($d) {
        return SITE_URL . "/blocks?district={$d}";
    }
    return SITE_URL . "/blocks";
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

/**
 * Fuzzy and phonetic block/subdistrict matching across Hindi/English transliterations
 */
if (!function_exists('isBlockMatch')) {
    function isBlockMatch($b1, $b2) {
        if (empty($b1) || empty($b2)) return false;

        static $aliases = [
            'fatwah' => 'phatuha', 'daniawan' => 'daniyova', 'phulwari' => 'phulavari',
            'dinapur' => 'danapur', 'arrah' => 'ara', 'biharsharif' => 'bihar',
            'sasaram' => 'sahasaram', 'deori' => 'devari', 'marhaura' => 'madhaura',
            'rohtas' => 'rohatas', 'bagaha' => 'bagaha', 'narkatiaganj' => 'narakatiyaganj'
        ];

        $s1 = str_replace(['ph', 'aa', 'ee', 'oo', 'w', 'v', 'chh', 'dh', 'rh', 'sh'], ['f', 'a', 'i', 'u', 'v', 'v', 'ch', 'd', 'r', 's'], slugify($b1));
        $s2 = str_replace(['ph', 'aa', 'ee', 'oo', 'w', 'v', 'chh', 'dh', 'rh', 'sh'], ['f', 'a', 'i', 'u', 'v', 'v', 'ch', 'd', 'r', 's'], slugify($b2));
        
        if ($s1 === $s2) return true;
        if (strcasecmp($b1, $b2) === 0) return true;

        // Check aliases
        $a1 = $aliases[slugify($b1)] ?? $aliases[$s1] ?? null;
        $a2 = $aliases[slugify($b2)] ?? $aliases[$s2] ?? null;
        if (($a1 && ($a1 === $s2 || strpos($s2, $a1) !== false || strpos($a1, $s2) !== false)) ||
            ($a2 && ($a2 === $s1 || strpos($s1, $a2) !== false || strpos($a2, $s1) !== false))) {
            return true;
        }
        
        // Substring match
        if (strlen($s1) >= 4 && strlen($s2) >= 4 && (strpos($s1, $s2) !== false || strpos($s2, $s1) !== false)) return true;
        
        // Consonant skeletal exact match (also normalize w/v and silent h/y)
        $c1 = preg_replace('/[aeiouwyh\-_]+/', '', $s1);
        $c2 = preg_replace('/[aeiouwyh\-_]+/', '', $s2);
        if ($c1 === $c2 && strlen($c1) >= 2) return true;
        
        // Hyphenated parts (e.g. Dinapur in Dinapur-Cum-Khagaul)
        $parts1 = explode('-', $s1);
        $parts2 = explode('-', $s2);
        foreach ($parts1 as $p1) {
            foreach ($parts2 as $p2) {
                if (strlen($p1) >= 4 && strlen($p2) >= 4) {
                    if ($p1 === $p2 || strpos($p1, $p2) !== false || strpos($p2, $p1) !== false) return true;
                    $cp1 = preg_replace('/[aeiouwyh\-_]+/', '', $p1);
                    $cp2 = preg_replace('/[aeiouwyh\-_]+/', '', $p2);
                    if ($cp1 === $cp2 && strlen($cp1) >= 2) return true;
                }
            }
        }

        if (strlen($s1) >= 5 && strlen($s2) >= 5 && levenshtein($s1, $s2) <= 1) return true;
        return false;
    }
}

/**
 * Fuzzy and phonetic panchayat name matching across Hindi/English transliterations
 */
if (!function_exists('isPanchayatMatch')) {
    function isPanchayatMatch($p1, $p2) {
        if (empty($p1) || empty($p2)) return false;
        $s1 = str_replace(['ph', 'aa', 'ee', 'oo', 'w', 'v', 'chh', 'dh', 'rh', 'sh'], ['f', 'a', 'i', 'u', 'v', 'v', 'ch', 'd', 'r', 's'], slugify($p1));
        $s2 = str_replace(['ph', 'aa', 'ee', 'oo', 'w', 'v', 'chh', 'dh', 'rh', 'sh'], ['f', 'a', 'i', 'u', 'v', 'v', 'ch', 'd', 'r', 's'], slugify($p2));
        if ($s1 === $s2) return true;
        if (strcasecmp($p1, $p2) === 0) return true;
        if (strpos($s1, $s2) !== false || strpos($s2, $s1) !== false) return true;
        
        $c1 = preg_replace('/[aeiou\-_]+/', '', $s1);
        $c2 = preg_replace('/[aeiou\-_]+/', '', $s2);
        if ($c1 === $c2 && strlen($c1) >= 2) return true;
        if (strlen($c1) >= 3 && strlen($c2) >= 3 && (strpos($c1, $c2) !== false || strpos($c2, $c1) !== false)) return true;
        
        if (levenshtein($s1, $s2) <= 2) return true;
        if (strlen($c1) >= 3 && strlen($c2) >= 3 && levenshtein($c1, $c2) <= 1) return true;
        return false;
    }
}




