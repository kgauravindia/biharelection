<?php
/**
 * BiharElection - Blog & Editorial Article Editor
 * Supports WYSIWYG Summernote editing, WordPress-style Media Library / Image Reuse,
 * Interactive Tag Suggestions, Real-time Image Preview, and Post Management.
 */
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$post_id = isset($_POST['post_id']) ? (int)$_POST['post_id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);
$is_edit = ($post_id > 0);

if (isset($_GET['msg']) && $_GET['msg'] === 'saved') {
    $message = "Article saved successfully!";
}

$post = [
    'id' => 0,
    'title' => '',
    'slug' => '',
    'excerpt' => '',
    'content' => '',
    'featured_image' => '',
    'categories' => 'Vidhan Sabha',
    'tags' => '',
    'author_name' => 'Bihar Election Editorial Team',
    'status' => 'published',
    'published_at' => date('Y-m-d H:i:s')
];

// Load existing post if edit mode
if ($is_edit && $conn) {
    $stmt = $conn->prepare("SELECT * FROM `posts` WHERE `id` = ?");
    if ($stmt) {
        $stmt->bind_param("i", $post_id);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res && $row = $res->fetch_assoc()) {
            $post = $row;
        } else {
            $error = "Post #{$post_id} not found.";
            $is_edit = false;
            $post_id = 0;
        }
    }
}

// Load all categories for suggestions
$categories_list = [];
if ($conn) {
    $c_res = $conn->query("SELECT name FROM `categories` ORDER BY `posts_count` DESC, `name` ASC");
    if ($c_res) {
        while ($cr = $c_res->fetch_assoc()) {
            if (!empty($cr['name']) && !in_array($cr['name'], $categories_list)) {
                $categories_list[] = $cr['name'];
            }
        }
    }
}
if (empty($categories_list)) {
    $categories_list = ['Vidhan Sabha', 'Lok Sabha', 'Panchayat', 'Analysis', 'Election Commission', 'Opinion', 'Ground Report', 'Candidate Profile'];
}

// Load all distinct tags from database for Tag Suggestions
$existing_tags_count = [];
$default_tags = ['Bihar Election 2026', 'Vidhan Sabha', 'Lok Sabha', 'ECI Guidelines', 'Patna', 'Mahagathbandhan', 'NDA', 'Jan Suraaj', 'RJD', 'BJP', 'JDU', 'Congress', 'Panchayat Chunav', 'Voter Slip', 'By-Election', 'Caste Census', 'Exit Poll', 'Results'];
foreach ($default_tags as $dt) {
    $existing_tags_count[$dt] = 1;
}

if ($conn) {
    $t_res = $conn->query("SELECT tags FROM `posts` WHERE tags IS NOT NULL AND tags != ''");
    if ($t_res) {
        while ($tr = $t_res->fetch_assoc()) {
            $split_tags = array_map('trim', explode(',', $tr['tags']));
            foreach ($split_tags as $st) {
                if (!empty($st)) {
                    $existing_tags_count[$st] = ($existing_tags_count[$st] ?? 0) + 1;
                }
            }
        }
    }
}
arsort($existing_tags_count);
$popular_tags = array_keys($existing_tags_count);

// Handle Form Submission (Create or Edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_post'])) {
    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $featured_image = trim($_POST['featured_image'] ?? '');
    $categories = trim($_POST['categories'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $author_name = trim($_POST['author_name'] ?? 'Bihar Election Editorial Team');
    $status = in_array($_POST['status'] ?? '', ['published', 'draft']) ? $_POST['status'] : 'published';
    
    // Normalize published_at
    $raw_pub_date = trim($_POST['published_at'] ?? '');
    if (!empty($raw_pub_date)) {
        $published_at = date('Y-m-d H:i:s', strtotime($raw_pub_date));
    } else {
        $published_at = date('Y-m-d H:i:s');
    }

    // Keep form inputs preserved in case of error
    $post['title'] = $title;
    $post['slug'] = $slug;
    $post['excerpt'] = $excerpt;
    $post['content'] = $content;
    $post['featured_image'] = $featured_image;
    $post['categories'] = $categories;
    $post['tags'] = $tags;
    $post['author_name'] = $author_name;
    $post['status'] = $status;
    $post['published_at'] = $published_at;

    if (empty($title)) {
        $error = "Please enter an article title.";
    } else {
        // Generate clean URL slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            if (empty($slug)) {
                $slug = 'post-' . time();
            }
        } else {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $slug), '-'));
        }
        $post['slug'] = $slug;

        if ($conn) {
            if ($is_edit) {
                // UPDATE POST
                $update_stmt = $conn->prepare("
                    UPDATE `posts` SET 
                    `title` = ?, 
                    `slug` = ?, 
                    `excerpt` = ?, 
                    `content` = ?, 
                    `featured_image` = ?, 
                    `categories` = ?, 
                    `tags` = ?, 
                    `author_name` = ?, 
                    `status` = ?, 
                    `published_at` = ?
                    WHERE `id` = ?
                ");
                
                if (!$update_stmt) {
                    $error = "Database prepare error: " . $conn->error;
                } else {
                    $update_stmt->bind_param(
                        "ssssssssssi",
                        $title,
                        $slug,
                        $excerpt,
                        $content,
                        $featured_image,
                        $categories,
                        $tags,
                        $author_name,
                        $status,
                        $published_at,
                        $post_id
                    );
                    if ($update_stmt->execute()) {
                        header("Location: edit-post.php?id=" . $post_id . "&msg=saved");
                        exit();
                    } else {
                        $error = "Error updating article: " . $update_stmt->error;
                    }
                }
            } else {
                // INSERT NEW POST
                $insert_stmt = $conn->prepare("
                    INSERT INTO `posts` 
                    (`title`, `slug`, `excerpt`, `content`, `featured_image`, `categories`, `tags`, `author_name`, `status`, `published_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if (!$insert_stmt) {
                    $error = "Database prepare error: " . $conn->error;
                } else {
                    $insert_stmt->bind_param(
                        "ssssssssss",
                        $title,
                        $slug,
                        $excerpt,
                        $content,
                        $featured_image,
                        $categories,
                        $tags,
                        $author_name,
                        $status,
                        $published_at
                    );
                    if ($insert_stmt->execute()) {
                        $new_id = $conn->insert_id;
                        header("Location: edit-post.php?id=" . $new_id . "&msg=saved");
                        exit();
                    } else {
                        $error = "Error creating article: " . $insert_stmt->error;
                    }
                }
            }
        } else {
            $error = "Database connection unavailable.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Article: ' . htmlspecialchars($post['title']) : 'Create New Article'; ?> — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">

    <!-- jQuery & Summernote RTF Editor -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <style>
        .note-editor.note-frame {
            border-radius: 10px;
            border: 1px solid #dee2e6;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .note-editor .note-toolbar {
            background-color: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 10px;
            display: flex;
            flex-wrap: wrap;
            gap: 4px;
        }
        .note-editable {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 15px;
            line-height: 1.75;
            color: #1e293b;
            background-color: #fff;
            min-height: 480px;
            padding: 20px;
        }
        .tag-pill {
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
        .tag-pill:hover {
            background-color: #e2e8f0 !important;
            transform: translateY(-1px);
        }
        .tag-pill.active {
            background-color: #dc2626 !important;
            color: #fff !important;
            border-color: #dc2626 !important;
        }
        .cat-pill {
            cursor: pointer;
            transition: all 0.2s ease;
            user-select: none;
        }
        .cat-pill:hover {
            background-color: #e0e7ff !important;
        }
        .cat-pill.active {
            background-color: #2563eb !important;
            color: #fff !important;
            border-color: #2563eb !important;
        }
        /* Media Library Grid */
        .media-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
            gap: 12px;
            max-height: 480px;
            overflow-y: auto;
            padding: 10px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .media-item {
            position: relative;
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            background: #fff;
            padding: 4px;
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .media-item:hover {
            border-color: #94a3b8;
            transform: scale(1.02);
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }
        .media-item.selected {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3);
        }
        .media-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        .media-item .check-badge {
            display: none;
            position: absolute;
            top: 6px;
            right: 6px;
            background: #2563eb;
            color: #fff;
            border-radius: 50%;
            width: 22px;
            height: 22px;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .media-item.selected .check-badge {
            display: flex;
        }
        .upload-dropzone {
            border: 2px dashed #94a3b8;
            border-radius: 12px;
            background-color: #f8fafc;
            padding: 40px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .upload-dropzone:hover, .upload-dropzone.dragover {
            border-color: #2563eb;
            background-color: #eff6ff;
        }
        .preview-box {
            position: relative;
            border-radius: 8px;
            overflow: hidden;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            min-height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .preview-box img {
            max-height: 200px;
            width: 100%;
            object-fit: cover;
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
                <a href="posts.php" class="text-decoration-none text-muted small fw-semibold">
                    <i class="fas fa-arrow-left me-1"></i> Back to All Articles
                </a>
                <h1 class="h3 fw-bold mb-1 mt-1" style="font-family: 'Outfit', sans-serif;">
                    <?php echo $is_edit ? 'Edit Article' : 'Create New Article'; ?>
                </h1>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <?php if ($is_edit && !empty($post['slug'])): ?>
                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($post['slug']); ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-semibold shadow-sm bg-white">
                        <i class="fas fa-external-link-alt me-1"></i> View Live Post
                    </a>
                    <a href="posts.php?delete_id=<?php echo $post['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this article permanently?');">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-primary btn-sm fw-semibold shadow-sm" onclick="openMediaModal('editor')">
                    <i class="fas fa-photo-video me-1"></i> Media Library
                </button>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form id="postForm" method="POST" action="edit-post.php<?php echo $is_edit ? '?id=' . $post_id : ''; ?>">
            <input type="hidden" name="save_post" value="1">
            <input type="hidden" name="post_id" id="post_id" value="<?php echo (int)$post_id; ?>">

            <div class="row g-4">
                <!-- Left Column: Main Editor -->
                <div class="col-lg-8">
                    <div class="section-card mb-4">
                        <div class="section-card-header d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-pen-nib me-2 text-danger"></i> Article Content</h6>
                            <span class="badge bg-light text-secondary border">Rich HTML / Visual Editor</span>
                        </div>
                        <div class="section-card-body">
                            <!-- Title -->
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Article Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg fw-semibold" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required placeholder="e.g., Bihar Assembly By-Election 2026: Constituency Wise Key Contests">
                            </div>

                            <!-- Slug -->
                            <div class="mb-3">
                                <label for="slug" class="form-label fw-bold small text-muted">URL Slug (SEO Permalink)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small">/blog/</span>
                                    <input type="text" class="form-control font-monospace" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" placeholder="auto-generated-from-title">
                                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="regenerateSlug()" title="Regenerate slug from title">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Unique clean URL for search engines and social sharing.</small>
                            </div>

                            <!-- Excerpt -->
                            <div class="mb-3">
                                <label for="excerpt" class="form-label fw-bold">Summary / Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Brief 1-2 sentence overview for social meta cards, Google snippets, and blog cards..."><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                            </div>

                            <!-- Content -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label for="content" class="form-label fw-bold mb-0">Full Body Content</label>
                                    <button type="button" class="btn btn-outline-primary btn-sm py-0 px-2 fw-semibold" onclick="openMediaModal('editor')">
                                        <i class="fas fa-images me-1"></i> Add Media to Article
                                    </button>
                                </div>
                                <textarea class="form-control" id="content" name="content"><?php echo htmlspecialchars($post['content']); ?></textarea>
                                <small class="text-muted mt-1 d-block">
                                    <i class="fas fa-info-circle me-1"></i> Tip: You can drag & drop images directly into the editor or click "Add Media to Article" to reuse existing media.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Metadata & Publishing Controls -->
                <div class="col-lg-4">
                    <!-- Publishing Panel -->
                    <div class="section-card mb-4 shadow-sm">
                        <div class="section-card-header bg-light">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-paper-plane me-2 text-primary"></i> Publishing Status</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Status</label>
                                <select class="form-select fw-semibold" name="status" id="status">
                                    <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>🟢 Published (Live & Indexed)</option>
                                    <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>🟡 Draft (Hidden)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Publish Date</label>
                                <input type="datetime-local" class="form-control" name="published_at" value="<?php echo date('Y-m-d\TH:i', strtotime($post['published_at'])); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Author Name</label>
                                <input type="text" class="form-control" name="author_name" value="<?php echo htmlspecialchars($post['author_name']); ?>" placeholder="Editorial Team / Correspondent">
                            </div>

                            <hr class="my-3">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm">
                                    <i class="fas fa-save me-1"></i> <?php echo $is_edit ? 'Update Article' : 'Publish Article'; ?>
                                </button>
                                <?php if ($is_edit && !empty($post['slug'])): ?>
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($post['slug']); ?>" target="_blank" class="btn btn-outline-dark fw-semibold">
                                        <i class="fas fa-external-link-alt me-1 text-primary"></i> View Live Article
                                    </a>
                                <?php endif; ?>
                                <a href="posts.php" class="btn btn-outline-secondary">
                                    Cancel & Return
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Featured Image & Media Reuse -->
                    <div class="section-card mb-4 shadow-sm">
                        <div class="section-card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-image me-2 text-success"></i> Featured Image</h6>
                        </div>
                        <div class="section-card-body">
                            <!-- Featured Image URL input + Browse Button -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Image URL / Path</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="featured_image" name="featured_image" value="<?php echo htmlspecialchars($post['featured_image']); ?>" placeholder="uploads/posts/banner.jpg" oninput="updateImagePreview(this.value)">
                                    <button type="button" class="btn btn-outline-secondary" onclick="clearFeaturedImage()" title="Clear Image">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm flex-fill fw-semibold" onclick="openMediaModal('featured')">
                                        <i class="fas fa-folder-open me-1"></i> Media Library
                                    </button>
                                    <button type="button" class="btn btn-primary btn-sm flex-fill fw-semibold" onclick="openMediaModal('featured', true)">
                                        <i class="fas fa-cloud-upload-alt me-1"></i> Upload
                                    </button>
                                </div>
                            </div>

                            <!-- Real-time Image Preview Box -->
                            <div class="mb-2">
                                <label class="form-label fw-bold small text-muted">Preview</label>
                                <div class="preview-box p-2 text-center" id="imagePreviewBox">
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <img src="<?php echo (strpos($post['featured_image'], 'http') === 0 ? '' : '../') . htmlspecialchars($post['featured_image']); ?>" id="previewImg" class="rounded shadow-sm" alt="Featured Image Preview" onerror="onPreviewError(this)">
                                        <div id="noPreviewText" class="text-muted small d-none"><i class="fas fa-image me-1"></i> No image selected</div>
                                    <?php else: ?>
                                        <div id="noPreviewText" class="text-muted small py-4"><i class="fas fa-image fa-2x d-block mb-2 opacity-50"></i> Image preview will appear here</div>
                                        <img src="" id="previewImg" class="rounded shadow-sm d-none" alt="Featured Image Preview" onerror="onPreviewError(this)">
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Categories & Taxonomy -->
                    <div class="section-card mb-4 shadow-sm">
                        <div class="section-card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-folder me-2 text-warning"></i> Categories</h6>
                            <a href="categories.php" target="_blank" class="small text-decoration-none text-primary fw-semibold"><i class="fas fa-plus me-1"></i> Manage</a>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <input type="text" class="form-control mb-2" id="categoriesInput" name="categories" value="<?php echo htmlspecialchars($post['categories']); ?>" placeholder="Vidhan Sabha, Election Results, Analysis">
                                <div class="small text-muted mb-2">Click to select / toggle category:</div>
                                <div class="d-flex flex-wrap gap-1" id="categoryPillsContainer">
                                    <?php 
                                    $current_cats = array_map('trim', explode(',', $post['categories']));
                                    foreach ($categories_list as $catName): 
                                        $isSelected = in_array($catName, $current_cats);
                                    ?>
                                        <button type="button" class="btn btn-sm cat-pill border py-1 px-2 small <?php echo $isSelected ? 'btn-primary active text-white' : 'btn-light text-dark'; ?>" onclick="toggleCategory('<?php echo htmlspecialchars(addslashes($catName)); ?>', this)">
                                            <?php echo $isSelected ? '✓ ' : '+ '; ?><?php echo htmlspecialchars($catName); ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tags & Tag Suggestions -->
                    <div class="section-card mb-4 shadow-sm">
                        <div class="section-card-header bg-light d-flex justify-content-between align-items-center">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-tags me-2 text-info"></i> Tags & Suggestions</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Article Tags (Comma separated)</label>
                                <div class="input-group mb-2">
                                    <input type="text" class="form-control" id="tagsInput" name="tags" list="tagsDatalist" value="<?php echo htmlspecialchars($post['tags']); ?>" placeholder="Patna, Vidhan Sabha 2026, ECI, NDA">
                                    <datalist id="tagsDatalist">
                                        <?php foreach ($popular_tags as $tName): ?>
                                            <option value="<?php echo htmlspecialchars($tName); ?>"></option>
                                        <?php endforeach; ?>
                                    </datalist>
                                </div>
                                <small class="text-muted d-block mb-2">Type or click from suggested popular tags below:</small>
                                
                                <div class="p-2 bg-light rounded border" style="max-height: 180px; overflow-y: auto;">
                                    <div class="d-flex flex-wrap gap-1" id="tagPillsContainer">
                                        <?php 
                                        $current_tags_arr = array_map('trim', explode(',', $post['tags']));
                                        foreach ($popular_tags as $sTag): 
                                            if (empty($sTag)) continue;
                                            $isTagActive = in_array($sTag, $current_tags_arr);
                                        ?>
                                            <span class="badge border tag-pill py-1 px-2 <?php echo $isTagActive ? 'active bg-danger text-white' : 'bg-white text-dark'; ?>" onclick="toggleTag('<?php echo htmlspecialchars(addslashes($sTag)); ?>', this)">
                                                <?php echo $isTagActive ? '✓ ' : '+ '; ?><?php echo htmlspecialchars($sTag); ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<!-- ========================================================================= -->
<!-- WORDPRESS-STYLE MEDIA LIBRARY & UPLOAD MODAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-labelledby="mediaLibraryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-light border-bottom">
                <div class="d-flex align-items-center gap-3">
                    <h5 class="modal-title fw-bold mb-0 text-dark" id="mediaLibraryModalLabel">
                        <i class="fas fa-photo-video text-primary me-2"></i> Media Library & Reuse
                    </h5>
                    <ul class="nav nav-pills" id="mediaTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-3 fw-semibold small" id="browse-tab" data-bs-toggle="pill" data-bs-target="#media-browse-pane" type="button" role="tab">
                                <i class="fas fa-images me-1"></i> Media Library
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-3 fw-semibold small" id="upload-tab" data-bs-toggle="pill" data-bs-target="#media-upload-pane" type="button" role="tab">
                                <i class="fas fa-cloud-upload-alt me-1"></i> Upload Files
                            </button>
                        </li>
                    </ul>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-3">
                <div class="tab-content" id="mediaTabContent">
                    <!-- Tab 1: Browse Existing Library -->
                    <div class="tab-pane fade show active" id="media-browse-pane" role="tabpanel">
                        <div class="row g-3">
                            <!-- Left: Search and Grid -->
                            <div class="col-md-8 col-lg-9">
                                <div class="d-flex justify-content-between align-items-center mb-3 gap-2">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" class="form-control" id="mediaSearchInput" placeholder="Filter media by filename..." oninput="filterMediaGrid(this.value)">
                                    </div>
                                    <button type="button" class="btn btn-outline-secondary btn-sm text-nowrap" onclick="loadMediaLibrary(true)">
                                        <i class="fas fa-sync-alt me-1"></i> Refresh
                                    </button>
                                </div>

                                <div id="mediaLoadingSpinner" class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status"></div>
                                    <p class="text-muted small mt-2">Loading images...</p>
                                </div>

                                <div class="media-grid" id="mediaGridContainer" style="display: none;">
                                    <!-- Dynamic Image Tiles injected here -->
                                </div>

                                <div id="mediaEmptyMsg" class="text-center py-5 text-muted d-none">
                                    <i class="fas fa-folder-open fa-3x mb-2 opacity-50"></i>
                                    <p class="mb-0">No images found. Upload a new image using the Upload Files tab.</p>
                                </div>
                            </div>

                            <!-- Right: Image Details & Selection Sidebar -->
                            <div class="col-md-4 col-lg-3">
                                <div class="bg-light rounded p-3 border h-100 d-flex flex-column justify-content-between" id="mediaDetailsPane">
                                    <div id="mediaDetailsContent" class="d-none">
                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2">Attachment Details</h6>
                                        <div class="text-center mb-3 bg-white p-2 rounded border">
                                            <img src="" id="detailsThumb" class="img-fluid rounded shadow-sm" style="max-height: 140px;" alt="Selected Thumbnail">
                                        </div>
                                        <div class="small mb-1"><strong>File:</strong> <span id="detailsName" class="text-muted text-break"></span></div>
                                        <div class="small mb-1"><strong>Size:</strong> <span id="detailsSize" class="text-muted"></span></div>
                                        <div class="small mb-1"><strong>Date:</strong> <span id="detailsDate" class="text-muted"></span></div>
                                        <div class="small mb-3">
                                            <strong>URL:</strong>
                                            <input type="text" class="form-control form-control-sm mt-1 font-monospace" id="detailsUrl" readonly>
                                        </div>
                                    </div>
                                    <div id="mediaNoSelectionMsg" class="text-center py-5 text-muted">
                                        <i class="fas fa-mouse-pointer fa-2x mb-2 opacity-40"></i>
                                        <p class="small mb-0">Click any image on the left to select and view details.</p>
                                    </div>
                                    
                                    <div class="mt-3 pt-2 border-top">
                                        <div class="d-grid gap-2">
                                            <button type="button" class="btn btn-primary fw-bold" id="btnSelectMedia" disabled onclick="applyMediaSelection()">
                                                <i class="fas fa-check me-1"></i> Choose Image
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: Upload Files -->
                    <div class="tab-pane fade" id="media-upload-pane" role="tabpanel">
                        <div class="upload-dropzone my-4" id="dropZone" onclick="document.getElementById('fileUploadInput').click()">
                            <input type="file" id="fileUploadInput" multiple accept="image/jpeg,image/png,image/webp,image/gif,image/svg+xml" class="d-none" onchange="handleFileInputChange(this.files)">
                            <i class="fas fa-cloud-upload-alt fa-4x text-primary mb-3"></i>
                            <h5 class="fw-bold">Drop files here to upload</h5>
                            <p class="text-muted mb-2">or click to browse from your computer</p>
                            <span class="badge bg-light text-dark border">Allowed: JPG, PNG, WEBP, GIF, SVG (Max 15MB)</span>
                        </div>
                        
                        <div id="uploadProgressBox" class="d-none mb-3">
                            <div class="progress" style="height: 12px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%"></div>
                            </div>
                            <small class="text-muted mt-1 d-block text-center" id="uploadStatusText">Uploading image...</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-top py-2">
                <span class="small text-muted me-auto"><i class="fas fa-recycle me-1 text-success"></i> Images can be reused across all blog posts and articles.</span>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
// State variables for media library
let currentMediaTarget = 'featured'; // 'featured' or 'editor'
let mediaLibraryLoaded = false;
let mediaItems = [];
let selectedMediaItem = null;

$(document).ready(function() {
    // Custom Summernote Button for Media Library
    const MediaLibraryButton = function (context) {
        const ui = $.summernote.ui;
        const button = ui.button({
            contents: '<i class="fas fa-images text-primary"></i> <span class="fw-semibold">Media Library</span>',
            tooltip: 'Insert Image from Media Library / Upload',
            click: function () {
                openMediaModal('editor');
            }
        });
        return button.render();
    };

    // Initialize Summernote RTF Editor
    $('#content').summernote({
        placeholder: 'Compose your article content, insert images, tables, headings, and embeds here...',
        tabsize: 2,
        height: 520,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'mediaLib', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        buttons: {
            mediaLib: MediaLibraryButton
        },
        callbacks: {
            onImageUpload: function(files) {
                // Automatically upload drag-dropped or pasted images to server
                for (let i = 0; i < files.length; i++) {
                    uploadImageToServer(files[i], function(imageUrl) {
                        $('#content').summernote('insertImage', imageUrl);
                    });
                }
            }
        }
    });

    // Handle Form Submit to ensure Summernote sync
    $('#postForm').on('submit', function() {
        if ($('#content').summernote('codeview.isActivated')) {
            $('#content').summernote('codeview.deactivate');
        }
        $('#content').val($('#content').summernote('code'));
    });

    // Auto-generate slug from title for new posts
    const isEditMode = <?php echo $is_edit ? 'true' : 'false'; ?>;
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (!isEditMode && titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.dataset.touched) {
                slugInput.value = generateSlug(this.value);
            }
        });

        slugInput.addEventListener('input', function() {
            this.dataset.touched = "true";
        });
    }

    // Drag & drop listeners for Media Library Dropzone
    const dropZone = document.getElementById('dropZone');
    if (dropZone) {
        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('dragover');
            }, false);
        });

        dropZone.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            handleFileInputChange(files);
        }, false);
    }
});

// Helper to generate SEO friendly slug
function generateSlug(text) {
    return text.toString().toLowerCase().trim()
        .replace(/[^\w\s-]/g, '')
        .replace(/[\s_-]+/g, '-')
        .replace(/^-+|-+$/g, '');
}

function regenerateSlug() {
    const titleVal = document.getElementById('title').value;
    const slugInput = document.getElementById('slug');
    if (titleVal.trim()) {
        slugInput.value = generateSlug(titleVal);
    }
}

// Real-time Featured Image Preview
function updateImagePreview(url) {
    const img = document.getElementById('previewImg');
    const txt = document.getElementById('noPreviewText');
    if (url && url.trim()) {
        // Fix relative paths for admin viewer
        let src = url.trim();
        if (!src.startsWith('http://') && !src.startsWith('https://') && !src.startsWith('/')) {
            src = '../' + src;
        }
        img.src = src;
        img.classList.remove('d-none');
        img.style.display = 'block';
        if (txt) txt.classList.add('d-none');
    } else {
        img.classList.add('d-none');
        img.style.display = 'none';
        if (txt) txt.classList.remove('d-none');
    }
}

function onPreviewError(imgEl) {
    imgEl.style.display = 'none';
    const txt = document.getElementById('noPreviewText');
    if (txt) {
        txt.classList.remove('d-none');
        txt.innerHTML = '<i class="fas fa-exclamation-triangle text-warning fa-2x d-block mb-1"></i> <span class="text-danger small">Invalid image URL or image not accessible</span>';
    }
}

function clearFeaturedImage() {
    document.getElementById('featured_image').value = '';
    updateImagePreview('');
}

// Category toggling logic
function toggleCategory(name, btn) {
    const input = document.getElementById('categoriesInput');
    let current = input.value.split(',').map(s => s.trim()).filter(Boolean);
    const index = current.indexOf(name);
    
    if (index > -1) {
        current.splice(index, 1);
        if (btn) {
            btn.classList.remove('btn-primary', 'active', 'text-white');
            btn.classList.add('btn-light', 'text-dark');
            btn.innerText = '+ ' + name;
        }
    } else {
        current.push(name);
        if (btn) {
            btn.classList.remove('btn-light', 'text-dark');
            btn.classList.add('btn-primary', 'active', 'text-white');
            btn.innerText = '✓ ' + name;
        }
    }
    input.value = current.join(', ');
}

// Tag Suggestions & Toggling Logic
function toggleTag(name, pill) {
    const input = document.getElementById('tagsInput');
    let current = input.value.split(',').map(s => s.trim()).filter(Boolean);
    const index = current.indexOf(name);

    if (index > -1) {
        // Remove tag
        current.splice(index, 1);
        if (pill) {
            pill.classList.remove('active', 'bg-danger', 'text-white');
            pill.classList.add('bg-white', 'text-dark');
            pill.innerText = '+ ' + name;
        }
    } else {
        // Add tag
        current.push(name);
        if (pill) {
            pill.classList.remove('bg-white', 'text-dark');
            pill.classList.add('active', 'bg-danger', 'text-white');
            pill.innerText = '✓ ' + name;
        }
    }
    input.value = current.join(', ');
}

// =========================================================================
// MEDIA LIBRARY MODAL & AJAX FUNCTIONS
// =========================================================================
function openMediaModal(target = 'featured', openUploadTab = false) {
    currentMediaTarget = target;
    const modalEl = document.getElementById('mediaLibraryModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    
    // Update button text depending on target
    const btnSelect = document.getElementById('btnSelectMedia');
    if (target === 'editor') {
        btnSelect.innerHTML = '<i class="fas fa-plus me-1"></i> Insert into Article';
    } else {
        btnSelect.innerHTML = '<i class="fas fa-check me-1"></i> Set as Featured Image';
    }

    if (openUploadTab) {
        const uploadTabBtn = document.getElementById('upload-tab');
        bootstrap.Tab.getOrCreateInstance(uploadTabBtn).show();
    } else {
        const browseTabBtn = document.getElementById('browse-tab');
        bootstrap.Tab.getOrCreateInstance(browseTabBtn).show();
    }

    modal.show();
    loadMediaLibrary();
}

function loadMediaLibrary(forceRefresh = false) {
    if (mediaLibraryLoaded && !forceRefresh) {
        return;
    }

    const spinner = document.getElementById('mediaLoadingSpinner');
    const grid = document.getElementById('mediaGridContainer');
    const empty = document.getElementById('mediaEmptyMsg');

    spinner.style.display = 'block';
    grid.style.display = 'none';
    empty.classList.add('d-none');

    fetch('media-library.php?action=list')
        .then(response => response.json())
        .then(data => {
            spinner.style.display = 'none';
            if (data.success && data.items && data.items.length > 0) {
                mediaItems = data.items;
                mediaLibraryLoaded = true;
                renderMediaGrid(mediaItems);
                grid.style.display = 'grid';
            } else {
                mediaItems = [];
                empty.classList.remove('d-none');
            }
        })
        .catch(err => {
            spinner.style.display = 'none';
            empty.classList.remove('d-none');
            console.error('Error loading media library:', err);
        });
}

function renderMediaGrid(items) {
    const grid = document.getElementById('mediaGridContainer');
    grid.innerHTML = '';

    items.forEach((item, index) => {
        const div = document.createElement('div');
        div.className = 'media-item';
        div.dataset.index = index;
        div.dataset.url = item.url;
        div.dataset.name = item.name;

        // Clean relative URL for admin preview
        let previewSrc = item.url;
        if (!previewSrc.startsWith('http://') && !previewSrc.startsWith('https://') && !previewSrc.startsWith('/')) {
            previewSrc = '../' + previewSrc;
        }

        div.innerHTML = `
            <img src="${previewSrc}" alt="${item.name}" loading="lazy" onerror="this.src='../assets/image/logo.png'">
            <div class="check-badge"><i class="fas fa-check"></i></div>
        `;

        div.addEventListener('click', () => selectMediaItem(item, div));
        grid.appendChild(div);
    });
}

function filterMediaGrid(keyword) {
    keyword = keyword.toLowerCase().trim();
    const filtered = mediaItems.filter(item => item.name.toLowerCase().includes(keyword) || item.url.toLowerCase().includes(keyword));
    renderMediaGrid(filtered);
    const empty = document.getElementById('mediaEmptyMsg');
    const grid = document.getElementById('mediaGridContainer');
    if (filtered.length === 0) {
        empty.classList.remove('d-none');
        grid.style.display = 'none';
    } else {
        empty.classList.add('d-none');
        grid.style.display = 'grid';
    }
}

function selectMediaItem(item, el) {
    // Remove selected class from all items
    document.querySelectorAll('.media-item').forEach(i => i.classList.remove('selected'));
    
    // Select this item
    el.classList.add('selected');
    selectedMediaItem = item;

    // Show details in sidebar
    document.getElementById('mediaNoSelectionMsg').classList.add('d-none');
    const detailsContent = document.getElementById('mediaDetailsContent');
    detailsContent.classList.remove('d-none');

    let previewSrc = item.url;
    if (!previewSrc.startsWith('http://') && !previewSrc.startsWith('https://') && !previewSrc.startsWith('/')) {
        previewSrc = '../' + previewSrc;
    }

    document.getElementById('detailsThumb').src = previewSrc;
    document.getElementById('detailsName').innerText = item.name;
    document.getElementById('detailsSize').innerText = item.size || 'N/A';
    document.getElementById('detailsDate').innerText = item.date || 'N/A';
    document.getElementById('detailsUrl').value = item.url;

    document.getElementById('btnSelectMedia').disabled = false;
}

function applyMediaSelection() {
    if (!selectedMediaItem) return;

    if (currentMediaTarget === 'featured') {
        document.getElementById('featured_image').value = selectedMediaItem.url;
        updateImagePreview(selectedMediaItem.url);
    } else if (currentMediaTarget === 'editor') {
        let insertUrl = selectedMediaItem.url;
        if (!insertUrl.startsWith('http://') && !insertUrl.startsWith('https://') && !insertUrl.startsWith('/')) {
            insertUrl = '/' + insertUrl;
        }
        $('#content').summernote('insertImage', insertUrl, selectedMediaItem.name);
    }

    const modalEl = document.getElementById('mediaLibraryModal');
    const modal = bootstrap.Modal.getInstance(modalEl);
    if (modal) modal.hide();
}

function handleFileInputChange(files) {
    if (!files || files.length === 0) return;

    const progressBox = document.getElementById('uploadProgressBox');
    const progressBar = document.getElementById('uploadProgressBar');
    const statusText = document.getElementById('uploadStatusText');

    progressBox.classList.remove('d-none');
    progressBar.style.width = '30%';
    statusText.innerText = 'Uploading ' + files.length + ' image(s)...';

    const formData = new FormData();
    formData.append('action', 'upload');
    for (let i = 0; i < files.length; i++) {
        formData.append('files[]', files[i]);
    }

    fetch('media-library.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        progressBar.style.width = '100%';
        if (data.success) {
            statusText.innerText = 'Upload successful!';
            setTimeout(() => {
                progressBox.classList.add('d-none');
                progressBar.style.width = '0%';
                
                // Switch back to Browse Tab and reload
                const browseTabBtn = document.getElementById('browse-tab');
                bootstrap.Tab.getOrCreateInstance(browseTabBtn).show();
                loadMediaLibrary(true);
            }, 600);
        } else {
            statusText.innerText = 'Upload failed: ' + (data.error || 'Unknown error');
            progressBar.classList.remove('bg-primary');
            progressBar.classList.add('bg-danger');
        }
    })
    .catch(err => {
        statusText.innerText = 'Network upload error occurred.';
        progressBar.classList.remove('bg-primary');
        progressBar.classList.add('bg-danger');
        console.error('Upload Error:', err);
    });
}

function uploadImageToServer(file, callback) {
    const formData = new FormData();
    formData.append('action', 'upload');
    formData.append('file', file);

    fetch('media-library.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.url) {
            let fullUrl = data.url;
            if (!fullUrl.startsWith('http://') && !fullUrl.startsWith('https://') && !fullUrl.startsWith('/')) {
                fullUrl = '/' + fullUrl;
            }
            callback(fullUrl);
        } else {
            alert('Image upload failed: ' + (data.error || 'Server error'));
        }
    })
    .catch(err => {
        console.error('Summernote upload error:', err);
    });
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
