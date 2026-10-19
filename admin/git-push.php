<?php
/**
 * Bihar Election Control Hub — Git Push & Deployment Console
 * Cross-platform, fail-safe architecture with GitHub Personal Access Token (PAT) authentication.
 */
if (isset($_GET['debug']) && $_GET['debug'] === '1') {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require_once __DIR__ . '/auth_check.php';
requireAdmin();

// Polyfills for PHP < 8.0 compatibility
if (!function_exists('str_contains')) {
    function str_contains(string $haystack, string $needle): bool {
        return '' === $needle || false !== strpos($haystack, $needle);
    }
}
if (!function_exists('str_starts_with')) {
    function str_starts_with(string $haystack, string $needle): bool {
        return 0 === strncmp($haystack, $needle, strlen($needle));
    }
}
if (!function_exists('str_ends_with')) {
    function str_ends_with(string $haystack, string $needle): bool {
        return '' === $needle || substr($haystack, -strlen($needle)) === $needle;
    }
}

$repoDir = realpath(__DIR__ . '/..');
$hasGitDir = is_dir($repoDir . '/.git');

/**
 * Mask sensitive tokens in logs and output
 */
function maskGitSecrets($str) {
    if (empty($str)) return $str;
    $str = preg_replace('/https:\/\/[^@]+@github\.com/i', 'https://oauth2:***@github.com', $str);
    $str = preg_replace('/ghp_[a-zA-Z0-9]{20,}/', 'ghp_••••••••', $str);
    $str = preg_replace('/github_pat_[a-zA-Z0-9_]{20,}/', 'github_pat_••••••••', $str);
    return $str;
}

/**
 * Check if exec() is allowed and not listed in disable_functions
 */
function isExecCallable() {
    if (!function_exists('exec')) {
        return false;
    }
    $disabled = ini_get('disable_functions');
    if (!empty($disabled)) {
        $disabledArr = array_map('trim', explode(',', strtolower($disabled)));
        if (in_array('exec', $disabledArr, true)) {
            return false;
        }
    }
    return true;
}

/**
 * Locate git executable cross-platform
 */
function getGitExecutable() {
    static $cachedGit = null;
    if ($cachedGit !== null) return $cachedGit;

    if (PHP_OS_FAMILY === 'Windows') {
        return $cachedGit = 'git';
    }

    $candidates = [
        'git',
        '/usr/bin/git',
        '/usr/local/bin/git',
        '/bin/git',
        '/opt/cpanel/ea-git/bin/git',
        '/usr/local/cpanel/3rdparty/bin/git'
    ];
    if (isExecCallable()) {
        foreach ($candidates as $cand) {
            $testOut = [];
            $testCode = 0;
            @exec("$cand --version 2>&1", $testOut, $testCode);
            if ($testCode === 0) {
                return $cachedGit = $cand;
            }
        }
    }
    return $cachedGit = 'git';
}

/**
 * Safely execute git commands without fatal errors
 */
function runGitCmd($cmd, $repoDir) {
    if (!isExecCallable()) {
        return [
            'cmd' => $cmd,
            'output' => "Shell execution is disabled in PHP configuration (disable_functions).",
            'code' => -1,
            'success' => false,
            'exec_disabled' => true
        ];
    }

    $gitBin = getGitExecutable();
    
    // Cross-platform execution
    if (str_starts_with(trim($cmd), 'git ')) {
        $subCmd = substr(trim($cmd), 4);
        $fullCmd = escapeshellcmd($gitBin) . ' -C ' . escapeshellarg($repoDir) . ' ' . $subCmd;
    } else {
        if (PHP_OS_FAMILY === 'Windows') {
            $fullCmd = 'cd /d ' . escapeshellarg($repoDir) . ' && ' . $cmd;
        } else {
            $fullCmd = 'cd ' . escapeshellarg($repoDir) . ' && ' . $cmd;
        }
    }

    $output = [];
    $returnVar = 0;
    try {
        @exec($fullCmd . ' 2>&1', $output, $returnVar);
    } catch (\Throwable $e) {
        return [
            'cmd' => maskGitSecrets($cmd),
            'output' => "Execution Error: " . $e->getMessage(),
            'code' => -1,
            'success' => false
        ];
    }

    return [
        'cmd' => maskGitSecrets($cmd),
        'output' => maskGitSecrets(implode("\n", $output)),
        'code' => $returnVar,
        'success' => ($returnVar === 0)
    ];
}

// Generate CSRF token if not set
if (empty($_SESSION['git_csrf_token'])) {
    $_SESSION['git_csrf_token'] = bin2hex(random_bytes(32));
}

// Load remembered GitHub token from constant or session
$savedGithubToken = defined('GITHUB_PAT') ? GITHUB_PAT : ($_SESSION['github_token'] ?? '');

$execAvailable = isExecCallable();
$lastAction = null;
$actionResult = null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['git_csrf_token'], $submittedToken)) {
        $actionResult = [
            'cmd' => 'Security Verification',
            'output' => 'Invalid or expired CSRF security token. Please refresh and try again.',
            'code' => 1,
            'success' => false
        ];
    } elseif (!$execAvailable) {
        $actionResult = [
            'cmd' => 'Server Configuration Check',
            'output' => "Cannot execute Git command: 'exec()' is currently in disable_functions.\nRemove 'exec' from disable_functions in Hostinger hPanel / cPanel PHP options.",
            'code' => 1,
            'success' => false
        ];
    } else {
        $action = $_POST['action'] ?? '';
        $branch = trim($_POST['branch'] ?? 'main');
        if (empty($branch) || !preg_match('/^[a-zA-Z0-9_\-\/]+$/', $branch)) {
            $branch = 'main';
        }

        // GitHub Token from form or saved
        $inputToken = trim($_POST['github_token'] ?? '');
        $githubToken = !empty($inputToken) ? $inputToken : $savedGithubToken;

        // Remember token in session if provided
        if (!empty($githubToken) && !empty($_POST['save_token'])) {
            $_SESSION['github_token'] = $githubToken;
            $savedGithubToken = $githubToken;
        }

        // Ensure Git Author identity is configured on repo so commit never fails
        runGitCmd('git config user.name "Bihar Election Admin"', $repoDir);
        runGitCmd('git config user.email "contact@biharelection.com"', $repoDir);

        // Determine remote target
        $pushTarget = 'origin';
        if (!empty($githubToken)) {
            $pushTarget = 'https://oauth2:' . rawurlencode($githubToken) . '@github.com/kgauravindia/biharelection.git';
            // Optionally update remote URL permanently if user checked remember
            if (!empty($_POST['save_token'])) {
                runGitCmd('git remote set-url origin ' . escapeshellarg($pushTarget), $repoDir);
                $pushTarget = 'origin';
            }
        }
        
        if ($action === 'push') {
            $lastAction = "Git Push (origin {$branch})";
            $actionResult = runGitCmd("git push " . escapeshellarg($pushTarget) . " " . escapeshellarg($branch), $repoDir);
        } elseif ($action === 'commit_and_push') {
            $commitMsg = trim($_POST['commit_message'] ?? '');
            if (empty($commitMsg)) {
                $commitMsg = 'Admin platform update — ' . date('Y-m-d H:i:s');
            }
            
            $stageAll = !empty($_POST['stage_all']);
            $stageCmd = $stageAll ? "git add -A" : "git add -u";
            
            // 1. Stage
            $r1 = runGitCmd($stageCmd, $repoDir);
            
            // 2. Commit with inline author config
            $commitCmd = 'git -c user.name="Bihar Election Admin" -c user.email="contact@biharelection.com" commit -m ' . escapeshellarg($commitMsg);
            $r2 = runGitCmd($commitCmd, $repoDir);
            
            // 3. Push with token if provided
            $r3 = runGitCmd("git push " . escapeshellarg($pushTarget) . " " . escapeshellarg($branch), $repoDir);
            
            $combinedOutput = "=== 1. STAGING FILES ===\n" . ($r1['output'] ?: 'Files staged.') . "\n\n";
            $combinedOutput .= "=== 2. CREATING COMMIT ===\n" . ($r2['output'] ?: 'No new changes to commit.') . "\n\n";
            $combinedOutput .= "=== 3. PUSHING TO GITHUB ===\n" . ($r3['output'] ?: 'Push completed.');
            
            $lastAction = "Stage, Commit & Push";
            $actionResult = [
                'cmd' => "$stageCmd && git commit -m ... && git push origin $branch",
                'output' => $combinedOutput,
                'code' => $r3['code'],
                'success' => $r3['success']
            ];
        } elseif ($action === 'pull') {
            $lastAction = "Git Pull (origin {$branch})";
            $pullTarget = !empty($githubToken) ? 'https://oauth2:' . rawurlencode($githubToken) . '@github.com/kgauravindia/biharelection.git' : 'origin';
            $actionResult = runGitCmd("git pull " . escapeshellarg($pullTarget) . " " . escapeshellarg($branch), $repoDir);
        } elseif ($action === 'fetch') {
            $lastAction = "Git Fetch";
            $fetchTarget = !empty($githubToken) ? 'https://oauth2:' . rawurlencode($githubToken) . '@github.com/kgauravindia/biharelection.git' : 'origin';
            $actionResult = runGitCmd("git fetch " . escapeshellarg($fetchTarget), $repoDir);
        }
    }
}

// Default metadata values
$currentBranch = 'main';
$remoteUrl = 'https://github.com/kgauravindia/biharelection.git';
$lastCommit = [
    'hash' => 'N/A',
    'author' => 'Unknown',
    'date' => 'Unknown',
    'message' => 'No commit history'
];
$changedFiles = [];
$aheadCount = 0;
$behindCount = 0;
$recentCommits = [];
$gitVersionStr = 'Unavailable';

// Query Git only if exec is allowed and .git exists
if ($execAvailable && $hasGitDir) {
    try {
        $vRes = runGitCmd("git --version", $repoDir);
        if ($vRes['success']) {
            $gitVersionStr = trim($vRes['output']);
        }
        
        $branchResult = runGitCmd("git rev-parse --abbrev-ref HEAD", $repoDir);
        if ($branchResult['success'] && !empty(trim($branchResult['output']))) {
            $currentBranch = trim($branchResult['output']);
        }

        $remoteResult = runGitCmd("git config --get remote.origin.url", $repoDir);
        if ($remoteResult['success'] && !empty(trim($remoteResult['output']))) {
            $remoteUrl = maskGitSecrets(trim($remoteResult['output']));
        }

        $lastCommitResult = runGitCmd('git log -1 --pretty=format:"%h|||%an|||%ar|||%s"', $repoDir);
        if ($lastCommitResult['success'] && !empty(trim($lastCommitResult['output']))) {
            $commitParts = explode('|||', trim($lastCommitResult['output']));
            $lastCommit = [
                'hash' => $commitParts[0] ?? 'N/A',
                'author' => $commitParts[1] ?? 'Unknown',
                'date' => $commitParts[2] ?? 'Unknown',
                'message' => $commitParts[3] ?? 'No commit history'
            ];
        }

        $statusPorcelain = runGitCmd("git status --porcelain", $repoDir);
        if ($statusPorcelain['success'] && !empty(trim($statusPorcelain['output']))) {
            $lines = explode("\n", trim($statusPorcelain['output']));
            foreach ($lines as $line) {
                if (strlen($line) >= 3) {
                    $changedFiles[] = [
                        'status' => substr($line, 0, 2),
                        'file' => trim(substr($line, 2))
                    ];
                }
            }
        }

        $aheadBehind = runGitCmd("git rev-list --left-right --count origin/{$currentBranch}...HEAD", $repoDir);
        if ($aheadBehind['success'] && !empty(trim($aheadBehind['output']))) {
            $counts = preg_split('/\s+/', trim($aheadBehind['output']));
            $behindCount = (int)($counts[0] ?? 0);
            $aheadCount = (int)($counts[1] ?? 0);
        }

        $logResult = runGitCmd('git log -5 --pretty=format:"%h|||%an|||%ar|||%s"', $repoDir);
        if ($logResult['success'] && !empty(trim($logResult['output']))) {
            foreach (explode("\n", trim($logResult['output'])) as $cLine) {
                $p = explode('|||', $cLine);
                if (count($p) >= 4) {
                    $recentCommits[] = [
                        'hash' => $p[0],
                        'author' => $p[1],
                        'date' => $p[2],
                        'message' => $p[3]
                    ];
                }
            }
        }
    } catch (\Throwable $ex) {
        error_log("Git metadata query error: " . $ex->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Git Push & Remote Deployment — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <style>
        .terminal-window {
            background-color: #0b1120;
            border-radius: 12px;
            color: #38bdf8;
            font-family: 'JetBrains Mono', Consolas, monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }
        .terminal-header {
            background: #1e293b;
            padding: 8px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }
        .terminal-body {
            padding: 16px;
            max-height: 360px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .file-status-badge {
            width: 26px;
            height: 22px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.75rem;
            border-radius: 4px;
            font-family: monospace;
        }
        .status-M { background: #e0f2fe; color: #0284c7; }
        .status-A { background: #dcfce7; color: #16a34a; }
        .status-D { background: #fee2e2; color: #dc2626; }
        .status-U { background: #fef3c7; color: #d97706; }
        .stat-card-git {
            background: white;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 1.25rem;
            transition: all 0.2s ease;
        }
        .stat-card-git:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px -5px rgba(0,0,0,0.06);
        }
        .token-input-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 10px;
            padding: 1rem;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h1 class="h3 fw-bold mb-0" style="font-family: 'Outfit', sans-serif;">Git Push & Deployment Hub</h1>
                    <span class="badge bg-danger text-white rounded-pill px-3 py-1"><i class="fab fa-git-alt me-1"></i> Git Control</span>
                </div>
                <p class="text-muted mb-0">Push codebase updates, sync data tables, and manage GitHub deployment directly from admin.</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="git-push.php" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold">
                    <i class="fas fa-arrows-rotate me-1"></i> Refresh Status
                </a>
                <a href="https://github.com/kgauravindia/biharelection" target="_blank" class="btn btn-dark rounded-pill px-3 fw-semibold">
                    <i class="fab fa-github me-1"></i> Open GitHub
                </a>
            </div>
        </div>

        <?php if ($actionResult !== null): ?>
            <div class="alert <?php echo $actionResult['success'] ? 'alert-success border-success' : 'alert-danger border-danger'; ?> rounded-4 shadow-sm mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold fs-6">
                        <i class="<?php echo $actionResult['success'] ? 'fas fa-circle-check text-success' : 'fas fa-circle-exclamation text-danger'; ?> me-2"></i>
                        Action Executed: <?php echo htmlspecialchars($lastAction ?: 'Command'); ?>
                    </div>
                    <span class="badge <?php echo $actionResult['success'] ? 'bg-success' : 'bg-danger'; ?> rounded-pill">
                        Exit Code: <?php echo $actionResult['code']; ?>
                    </span>
                </div>
                <div class="terminal-window mt-2">
                    <div class="terminal-header">
                        <span class="text-light small"><i class="fas fa-terminal me-1"></i> Command: <?php echo htmlspecialchars($actionResult['cmd']); ?></span>
                    </div>
                    <div class="terminal-body text-light"><?php echo htmlspecialchars($actionResult['output'] ?: 'Command finished with no output.'); ?></div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Quick Status Metrics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="stat-card-git shadow-sm">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="fas fa-code-branch text-primary me-1"></i> Active Branch</div>
                    <div class="fs-4 fw-bold text-dark font-monospace"><?php echo htmlspecialchars($currentBranch); ?></div>
                    <small class="text-muted">Target: origin/<?php echo htmlspecialchars($currentBranch); ?></small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-git shadow-sm">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="fas fa-clock-rotate-left text-warning me-1"></i> Last Commit</div>
                    <div class="fs-5 fw-bold text-dark font-monospace"><?php echo htmlspecialchars($lastCommit['hash']); ?></div>
                    <small class="text-muted text-truncate d-block" title="<?php echo htmlspecialchars($lastCommit['message']); ?>">
                        <?php echo htmlspecialchars($lastCommit['message']); ?>
                    </small>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-git shadow-sm">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="fas fa-cloud-arrow-up text-success me-1"></i> Sync Status</div>
                    <?php if ($aheadCount > 0): ?>
                        <div class="fs-5 fw-bold text-danger"><?php echo $aheadCount; ?> Commits Ahead</div>
                        <small class="text-danger fw-semibold">Ready to push to remote</small>
                    <?php elseif ($behindCount > 0): ?>
                        <div class="fs-5 fw-bold text-warning"><?php echo $behindCount; ?> Commits Behind</div>
                        <small class="text-warning fw-semibold">Pull recommended</small>
                    <?php else: ?>
                        <div class="fs-5 fw-bold text-success">Up to Date</div>
                        <small class="text-muted">Synced with origin/<?php echo htmlspecialchars($currentBranch); ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card-git shadow-sm">
                    <div class="text-muted small fw-bold text-uppercase mb-1"><i class="fas fa-file-pen text-info me-1"></i> Working Tree</div>
                    <div class="fs-4 fw-bold <?php echo count($changedFiles) > 0 ? 'text-warning' : 'text-success'; ?>">
                        <?php echo count($changedFiles); ?> <span class="fs-6 text-muted">files changed</span>
                    </div>
                    <small class="text-muted"><?php echo count($changedFiles) > 0 ? 'Uncommitted changes detected' : 'Working directory clean'; ?></small>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Left Column: Push / Commit Form -->
            <div class="col-lg-7">
                <!-- Action 1: Commit & Push All Changes -->
                <div class="section-card mb-4">
                    <div class="section-card-header bg-white border-bottom py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold mb-0 text-dark">
                                <i class="fas fa-cloud-arrow-up me-2 text-danger"></i> Stage, Commit & Push to GitHub
                            </h6>
                            <span class="badge bg-light text-dark border">Automated Workflow</span>
                        </div>
                    </div>
                    <div class="section-card-body p-4">
                        <form method="POST" action="git-push.php" onsubmit="return confirmPush(this);">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['git_csrf_token']); ?>">
                            <input type="hidden" name="action" value="commit_and_push">
                            <input type="hidden" name="branch" value="<?php echo htmlspecialchars($currentBranch); ?>">
                            
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted text-uppercase">Commit Message</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-quote-left text-muted"></i></span>
                                    <input type="text" name="commit_message" class="form-control" 
                                           placeholder="e.g. Update Panchayat Samiti Data & schema" 
                                           value="<?php echo 'Update platform data & files — ' . date('Y-m-d H:i'); ?>" required>
                                </div>
                                <div class="form-text">Describe the changes being committed to the repository.</div>
                            </div>

                            <!-- GitHub Token Box -->
                            <div class="token-input-box mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fab fa-github me-1 text-dark"></i> GitHub Personal Access Token (PAT)
                                    </label>
                                    <a href="https://github.com/settings/tokens/new?scopes=repo&description=BiharElection+Admin+Deploy" target="_blank" class="small fw-semibold text-danger text-decoration-none">
                                        <i class="fas fa-arrow-up-right-from-square me-1"></i> Generate Token (1-Click)
                                    </a>
                                </div>
                                <input type="password" name="github_token" class="form-control font-monospace" 
                                       placeholder="ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx" 
                                       value="<?php echo htmlspecialchars($savedGithubToken); ?>" autocomplete="new-password">
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <div class="form-check form-check-inline mb-0">
                                        <input class="form-check-input" type="checkbox" id="saveToken" name="save_token" value="1" <?php echo !empty($savedGithubToken) ? 'checked' : ''; ?>>
                                        <label class="form-check-label small text-muted" for="saveToken">Remember token on server</label>
                                    </div>
                                    <small class="text-muted"><i class="fas fa-lock me-1"></i>Required for HTTPS push</small>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="stageAll" name="stage_all" value="1" checked>
                                    <label class="form-check-label fw-semibold small" for="stageAll">
                                        Stage all modified & untracked files (<code class="small">git add -A</code>)
                                    </label>
                                </div>
                            </div>

                            <div class="p-3 bg-light rounded-3 mb-4 border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="small fw-bold text-dark">Destination Remote</div>
                                        <div class="small text-muted font-monospace"><?php echo htmlspecialchars($remoteUrl); ?></div>
                                    </div>
                                    <span class="badge bg-dark rounded-pill px-3 font-monospace"><?php echo htmlspecialchars($currentBranch); ?></span>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-danger btn-lg w-100 fw-bold rounded-pill shadow-sm py-2">
                                <i class="fas fa-arrow-up-from-bracket me-2"></i> Commit & Git Push Now
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Action 2: Quick Push Only (Already Committed) -->
                <div class="section-card mb-4">
                    <div class="section-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fab fa-git-alt me-2 text-primary"></i> Push Pre-Committed Commits
                        </h6>
                        <span class="badge bg-primary text-white"><?php echo $aheadCount; ?> commits pending</span>
                    </div>
                    <div class="section-card-body p-4">
                        <p class="text-muted small mb-3">Push already committed changes or pull recent updates from GitHub:</p>
                        <form method="POST" action="git-push.php">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['git_csrf_token']); ?>">
                            <input type="hidden" name="branch" value="<?php echo htmlspecialchars($currentBranch); ?>">

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" name="action" value="push" class="btn btn-outline-danger fw-bold rounded-pill px-4">
                                    <i class="fas fa-paper-plane me-1"></i> Push to origin/<?php echo htmlspecialchars($currentBranch); ?>
                                </button>
                                
                                <button type="submit" name="action" value="pull" class="btn btn-outline-secondary fw-semibold rounded-pill px-3">
                                    <i class="fas fa-cloud-arrow-down me-1"></i> Git Pull
                                </button>

                                <button type="submit" name="action" value="fetch" class="btn btn-outline-secondary fw-semibold rounded-pill px-3">
                                    <i class="fas fa-rotate me-1"></i> Git Fetch
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Working Tree Changes & Recent History -->
            <div class="col-lg-5">
                <!-- Working Tree Status -->
                <div class="section-card mb-4">
                    <div class="section-card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-check me-2 text-warning"></i> Working Tree Files
                        </h6>
                        <span class="badge bg-secondary"><?php echo count($changedFiles); ?></span>
                    </div>
                    <div class="section-card-body p-0">
                        <?php if (empty($changedFiles)): ?>
                            <div class="text-center py-5">
                                <div class="text-success fs-1 mb-2"><i class="fas fa-circle-check"></i></div>
                                <h6 class="fw-bold text-dark">Working Tree is Clean</h6>
                                <p class="text-muted small mb-0">No uncommitted file modifications detected.</p>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                                <?php foreach ($changedFiles as $cf): 
                                    $rawStatus = trim($cf['status']);
                                    $badgeClass = 'status-M';
                                    $badgeLetter = 'M';
                                    if (strpos($rawStatus, '?') !== false) {
                                        $badgeClass = 'status-U';
                                        $badgeLetter = '?';
                                    } elseif (strpos($rawStatus, 'A') !== false) {
                                        $badgeClass = 'status-A';
                                        $badgeLetter = 'A';
                                    } elseif (strpos($rawStatus, 'D') !== false) {
                                        $badgeClass = 'status-D';
                                        $badgeLetter = 'D';
                                    }
                                ?>
                                    <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                                        <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 80%;">
                                            <span class="file-status-badge <?php echo $badgeClass; ?>"><?php echo $badgeLetter; ?></span>
                                            <span class="small font-monospace text-dark text-truncate" title="<?php echo htmlspecialchars($cf['file']); ?>">
                                                <?php echo htmlspecialchars($cf['file']); ?>
                                            </span>
                                        </div>
                                        <span class="small text-muted"><?php echo htmlspecialchars($rawStatus); ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Git Commits -->
                <div class="section-card mb-4">
                    <div class="section-card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-history me-2 text-info"></i> Recent Commits
                        </h6>
                    </div>
                    <div class="section-card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php if (empty($recentCommits)): ?>
                                <div class="p-3 text-muted small text-center">No recent commit history available.</div>
                            <?php else: ?>
                                <?php foreach ($recentCommits as $rc): ?>
                                    <div class="list-group-item py-3 px-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <span class="badge bg-dark font-monospace text-light px-2 py-1"><?php echo htmlspecialchars($rc['hash']); ?></span>
                                            <small class="text-muted"><i class="far fa-clock me-1"></i><?php echo htmlspecialchars($rc['date']); ?></small>
                                        </div>
                                        <div class="small fw-semibold text-dark text-truncate mb-1" title="<?php echo htmlspecialchars($rc['message']); ?>">
                                            <?php echo htmlspecialchars($rc['message']); ?>
                                        </div>
                                        <small class="text-muted"><i class="fas fa-user-pen me-1"></i><?php echo htmlspecialchars($rc['author']); ?></small>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function confirmPush(form) {
    var btn = form.querySelector('button[type="submit"]');
    if (!confirm("Are you sure you want to commit and push changes to origin/<?php echo htmlspecialchars($currentBranch); ?> on GitHub?")) {
        return false;
    }
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Pushing to GitHub... Please wait...';
    return true;
}
</script>
</body>
</html>
