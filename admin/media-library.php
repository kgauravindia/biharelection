<?php
/**
 * BiharElection - Admin Media Library & Upload API (WordPress style)
 * Handles image listing, AJAX uploads, and image reuse for blog posts and editorial content.
 */
require_once __DIR__ . '/auth_check.php';
requireAdmin();

header('Content-Type: application/json; charset=utf-8');

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$upload_base_dir = realpath(__DIR__ . '/../uploads');
if (!$upload_base_dir) {
    $upload_base_dir = __DIR__ . '/../uploads';
    if (!is_dir($upload_base_dir)) {
        mkdir($upload_base_dir, 0777, true);
    }
}
$posts_upload_dir = $upload_base_dir . DIRECTORY_SEPARATOR . 'posts';
if (!is_dir($posts_upload_dir)) {
    mkdir($posts_upload_dir, 0777, true);
}

// --------------------------------------------------------------------------
// 1. ACTION: UPLOAD FILE(S)
// --------------------------------------------------------------------------
if ($action === 'upload') {
    $uploaded_files = [];
    $errors = [];

    // Normalizing $_FILES for single and multiple files
    $files_to_process = [];
    if (isset($_FILES['file'])) {
        $files_to_process[] = $_FILES['file'];
    } elseif (isset($_FILES['files'])) {
        if (is_array($_FILES['files']['name'])) {
            $count = count($_FILES['files']['name']);
            for ($i = 0; $i < $count; $i++) {
                $files_to_process[] = [
                    'name' => $_FILES['files']['name'][$i],
                    'type' => $_FILES['files']['type'][$i],
                    'tmp_name' => $_FILES['files']['tmp_name'][$i],
                    'error' => $_FILES['files']['error'][$i],
                    'size' => $_FILES['files']['size'][$i]
                ];
            }
        } else {
            $files_to_process[] = $_FILES['files'];
        }
    }

    if (empty($files_to_process)) {
        echo json_encode(['success' => false, 'error' => 'No file was uploaded.']);
        exit();
    }

    $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
    $allowed_mimes = [
        'image/jpeg', 'image/pjpeg', 'image/png', 'image/webp',
        'image/gif', 'image/svg+xml'
    ];

    foreach ($files_to_process as $file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Upload error code: " . $file['error'];
            continue;
        }

        if ($file['size'] > 15 * 1024 * 1024) { // 15MB max
            $errors[] = "File '{$file['name']}' exceeds the maximum allowed size of 15MB.";
            continue;
        }

        $raw_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($raw_ext, $allowed_exts)) {
            $errors[] = "File extension '{$raw_ext}' is not allowed. Supported formats: JPG, PNG, WEBP, GIF, SVG.";
            continue;
        }

        // Clean sanitized name
        $orig_name = pathinfo($file['name'], PATHINFO_FILENAME);
        $clean_name = preg_replace('/[^a-zA-Z0-9_-]+/', '-', strtolower($orig_name));
        $clean_name = trim($clean_name, '-');
        if (empty($clean_name)) {
            $clean_name = 'media';
        }

        $unique_filename = $clean_name . '-' . date('Ymd-His') . '-' . substr(uniqid(), -4) . '.' . $raw_ext;
        $target_filepath = $posts_upload_dir . DIRECTORY_SEPARATOR . $unique_filename;

        if (move_uploaded_file($file['tmp_name'], $target_filepath)) {
            // Determine relative web URL
            $web_url = 'uploads/posts/' . $unique_filename;
            
            // Get dimensions if possible
            $width = 0;
            $height = 0;
            $img_info = @getimagesize($target_filepath);
            if ($img_info) {
                $width = $img_info[0];
                $height = $img_info[1];
            }

            $uploaded_files[] = [
                'name' => $unique_filename,
                'url' => $web_url,
                'size' => formatBytes(filesize($target_filepath)),
                'width' => $width,
                'height' => $height,
                'date' => date('d M Y, h:i A')
            ];
        } else {
            $errors[] = "Could not save uploaded file '{$file['name']}' to server.";
        }
    }

    if (!empty($uploaded_files)) {
        echo json_encode([
            'success' => true,
            'files' => $uploaded_files,
            'file' => $uploaded_files[0], // for single upload compatibility
            'url' => $uploaded_files[0]['url'],
            'errors' => $errors
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => implode(' | ', $errors) ?: 'Upload failed.'
        ]);
    }
    exit();
}

// --------------------------------------------------------------------------
// 2. ACTION: LIST MEDIA FILES (Images from uploads & DB)
// --------------------------------------------------------------------------
if ($action === 'list') {
    $search = trim($_GET['q'] ?? '');
    $items = [];
    $seen_urls = [];

    // Helper to format bytes
    function formatBytes($bytes, $precision = 2) {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    // Helper to scan directory for images
    $scanDirectory = function($dir, $web_prefix) use (&$items, &$seen_urls, $search) {
        if (!is_dir($dir)) return;
        $allowed_exts = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'svg'];
        
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isFile()) {
                    $ext = strtolower($fileinfo->getExtension());
                    if (in_array($ext, $allowed_exts)) {
                        $filename = $fileinfo->getFilename();
                        if (!empty($search) && stripos($filename, $search) === false) {
                            continue;
                        }

                        $relative_path = str_replace('\\', '/', substr($fileinfo->getPathname(), strlen(realpath(__DIR__ . '/..')) + 1));
                        $relative_path = ltrim($relative_path, '/');

                        if (isset($seen_urls[$relative_path])) {
                            continue;
                        }
                        $seen_urls[$relative_path] = true;

                        $mtime = $fileinfo->getMTime();
                        $filesize = $fileinfo->getSize();

                        $items[] = [
                            'name' => $filename,
                            'url' => $relative_path,
                            'size' => formatBytes($filesize),
                            'bytes' => $filesize,
                            'timestamp' => $mtime,
                            'date' => date('d M Y, h:i A', $mtime),
                            'ext' => strtoupper($ext)
                        ];
                    }
                }
            }
        } catch (Exception $e) {
            // Silently handle read permission error on directory
        }
    };

    // Scan uploads directory and subdirectories
    $scanDirectory(__DIR__ . '/../uploads', 'uploads');

    // Also scan assets/image if exists
    if (is_dir(__DIR__ . '/../assets/image')) {
        $scanDirectory(__DIR__ . '/../assets/image', 'assets/image');
    }

    // Also check past featured images stored in DB
    $conn = getAdminDB();
    if ($conn) {
        $p_res = $conn->query("SELECT DISTINCT featured_image FROM `posts` WHERE featured_image IS NOT NULL AND featured_image != ''");
        if ($p_res) {
            while ($row = $p_res->fetch_assoc()) {
                $img_url = trim($row['featured_image']);
                if (empty($img_url)) continue;

                // Normalize url
                $clean_url = ltrim(parse_url($img_url, PHP_URL_PATH) ?: $img_url, '/');
                if (!isset($seen_urls[$img_url]) && !isset($seen_urls[$clean_url])) {
                    $seen_urls[$img_url] = true;
                    $filename = basename($img_url);
                    if (!empty($search) && stripos($filename, $search) === false && stripos($img_url, $search) === false) {
                        continue;
                    }
                    $items[] = [
                        'name' => $filename,
                        'url' => $img_url,
                        'size' => 'External / Stored',
                        'bytes' => 0,
                        'timestamp' => time() - 3600,
                        'date' => 'Post Image',
                        'ext' => strtoupper(pathinfo($filename, PATHINFO_EXTENSION) ?: 'IMG')
                    ];
                }
            }
        }
    }

    // Sort by timestamp DESC (newest first)
    usort($items, function($a, $b) {
        return $b['timestamp'] <=> $a['timestamp'];
    });

    echo json_encode([
        'success' => true,
        'total' => count($items),
        'items' => array_values($items)
    ]);
    exit();
}

// --------------------------------------------------------------------------
// 3. ACTION: DELETE MEDIA FILE
// --------------------------------------------------------------------------
if ($action === 'delete') {
    $file_url = trim($_POST['url'] ?? '');
    if (empty($file_url)) {
        echo json_encode(['success' => false, 'error' => 'No file specified for deletion.']);
        exit();
    }

    // Resolve path safely inside root uploads folder
    $root_dir = realpath(__DIR__ . '/..');
    $safe_rel = str_replace(['../', '..\\'], '', $file_url);
    $full_path = realpath($root_dir . DIRECTORY_SEPARATOR . $safe_rel);

    // Verify it's inside uploads or assets
    if ($full_path && file_exists($full_path) && strpos($full_path, $upload_base_dir) === 0) {
        if (@unlink($full_path)) {
            echo json_encode(['success' => true, 'message' => 'File deleted successfully.']);
            exit();
        } else {
            echo json_encode(['success' => false, 'error' => 'Permission denied: Could not delete file.']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'File not found or protected.']);
        exit();
    }
}

echo json_encode(['success' => false, 'error' => 'Invalid action.']);
exit();
