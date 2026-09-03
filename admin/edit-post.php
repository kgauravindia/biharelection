<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

$post_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = ($post_id > 0);

$post = [
    'id' => 0,
    'wp_id' => null,
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
            $error = "Post not found.";
            $is_edit = false;
        }
    }
}

// Load available categories
$categories_list = [];
if ($conn) {
    $c_res = $conn->query("SELECT name FROM `categories` ORDER BY `posts_count` DESC, `name` ASC");
    if ($c_res) {
        while ($cr = $c_res->fetch_assoc()) {
            $categories_list[] = $cr['name'];
        }
    }
}

// Handle Form Submission
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
    $published_at = !empty($_POST['published_at']) ? $_POST['published_at'] : date('Y-m-d H:i:s');

    if (empty($title)) {
        $error = "Please enter an article title.";
    } else {
        // Generate slug if empty
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
            if (empty($slug)) {
                $slug = 'post-' . time();
            }
        }

        if ($conn) {
            if ($is_edit) {
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
                if ($update_stmt) {
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
                        header("Location: posts.php?msg=saved");
                        exit();
                    } else {
                        $error = "Error updating post: " . $conn->error;
                    }
                }
            } else {
                $insert_stmt = $conn->prepare("
                    INSERT INTO `posts` 
                    (`title`, `slug`, `excerpt`, `content`, `featured_image`, `categories`, `tags`, `author_name`, `status`, `published_at`)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                if ($insert_stmt) {
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
                        header("Location: posts.php?msg=saved");
                        exit();
                    } else {
                        $error = "Error creating post: " . $conn->error;
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $is_edit ? 'Edit Article' : 'New Article'; ?> — Bihar Election Admin</title>
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
            border-radius: 8px;
            border: 1px solid #dee2e6;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .note-editor .note-toolbar {
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-top-left-radius: 8px;
            border-top-right-radius: 8px;
            padding: 8px;
        }
        .note-editable {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            font-size: 15px;
            line-height: 1.7;
            color: #1e293b;
            background-color: #fff;
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
            <?php if ($is_edit && !empty($post['slug'])): ?>
                <div class="mt-3 mt-md-0 d-flex gap-2">
                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($post['slug']); ?>" target="_blank" class="btn btn-outline-primary btn-sm fw-semibold shadow-sm bg-white">
                        <i class="fas fa-external-link-alt me-1"></i> View Live Post
                    </a>
                    <a href="posts.php?delete_id=<?php echo $post['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this article permanently?');">
                        <i class="fas fa-trash-alt me-1"></i> Delete
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="save_post" value="1">

            <div class="row g-4">
                <!-- Left Column: Main Editor -->
                <div class="col-lg-8">
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-pen-nib me-2 text-danger"></i> Article Content</h6>
                        </div>
                        <div class="section-card-body">
                            <!-- Title -->
                            <div class="mb-3">
                                <label for="title" class="form-label fw-bold">Article Title <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-lg fw-semibold" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required placeholder="e.g., Bihar Assembly By-Election 2026 Updates">
                            </div>

                            <!-- Slug -->
                            <div class="mb-3">
                                <label for="slug" class="form-label fw-bold small text-muted">URL Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small">/blog/</span>
                                    <input type="text" class="form-control font-monospace" id="slug" name="slug" value="<?php echo htmlspecialchars($post['slug']); ?>" placeholder="auto-generated-from-title">
                                </div>
                                <small class="text-muted">Unique clean URL for indexing and sitemaps.</small>
                            </div>

                            <!-- Excerpt -->
                            <div class="mb-3">
                                <label for="excerpt" class="form-label fw-bold">Summary / Excerpt</label>
                                <textarea class="form-control" id="excerpt" name="excerpt" rows="3" placeholder="Brief 1-2 sentence overview for social shares and search snippets..."><?php echo htmlspecialchars($post['excerpt']); ?></textarea>
                            </div>

                            <!-- Content -->
                            <div class="mb-3">
                                <label for="content" class="form-label fw-bold">Full Body / HTML Content</label>
                                <textarea class="form-control font-monospace" id="content" name="content" rows="16" placeholder="Enter article body HTML / content..."><?php echo htmlspecialchars($post['content']); ?></textarea>
                                <small class="text-muted">Supports HTML tags, tables, headings, blockquotes, and embedded widgets.</small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Metadata & Publishing Controls -->
                <div class="col-lg-4">
                    <!-- Publishing Panel -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-paper-plane me-2 text-primary"></i> Publishing Status</h6>
                        </div>
                        <div class="section-card-body">
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Status</label>
                                <select class="form-select" name="status">
                                    <option value="published" <?php echo $post['status'] === 'published' ? 'selected' : ''; ?>>🟢 Published (Live)</option>
                                    <option value="draft" <?php echo $post['status'] === 'draft' ? 'selected' : ''; ?>>🟡 Draft (Hidden)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Publish Date</label>
                                <input type="datetime-local" class="form-control" name="published_at" value="<?php echo date('Y-m-d\TH:i', strtotime($post['published_at'])); ?>">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold small">Author Name</label>
                                <input type="text" class="form-control" name="author_name" value="<?php echo htmlspecialchars($post['author_name']); ?>">
                            </div>

                            <hr class="my-3">

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger btn-lg fw-bold shadow-sm">
                                    <i class="fas fa-save me-1"></i> Save Article
                                </button>
                                <?php if ($is_edit && !empty($post['slug'])): ?>
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($post['slug']); ?>" target="_blank" class="btn btn-outline-dark fw-semibold">
                                        <i class="fas fa-external-link-alt me-1 text-primary"></i> View Live Article
                                    </a>
                                <?php endif; ?>
                                <a href="posts.php" class="btn btn-outline-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Media & Categorization -->
                    <div class="section-card mb-4">
                        <div class="section-card-header">
                            <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-image me-2 text-success"></i> Featured Image & Taxonomy</h6>
                        </div>
                        <div class="section-card-body">
                            <!-- Featured Image URL -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Featured Image URL</label>
                                <input type="text" class="form-control" id="featured_image" name="featured_image" value="<?php echo htmlspecialchars($post['featured_image']); ?>" placeholder="https://biharelection.com/uploads/banner.jpg" oninput="updateImagePreview(this.value)">
                                <div class="mt-2 text-center bg-light rounded border p-2" id="imagePreviewBox">
                                    <?php if (!empty($post['featured_image'])): ?>
                                        <img src="<?php echo htmlspecialchars($post['featured_image']); ?>" id="previewImg" class="img-fluid rounded" style="max-height: 120px;" onerror="this.style.display='none';">
                                    <?php else: ?>
                                        <span class="text-muted small" id="noPreviewText"><i class="fas fa-image me-1"></i> Image preview will appear here</span>
                                        <img src="" id="previewImg" class="img-fluid rounded d-none" style="max-height: 120px;">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Categories -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label fw-bold small mb-0">Categories</label>
                                    <a href="categories.php" target="_blank" class="small text-decoration-none text-primary"><i class="fas fa-cog me-1"></i> Manage</a>
                                </div>
                                <input type="text" class="form-control" id="categoriesInput" name="categories" value="<?php echo htmlspecialchars($post['categories']); ?>" placeholder="Vidhan Sabha, Election Results, Panchayat">
                                <?php if (!empty($categories_list)): ?>
                                    <div class="mt-2 d-flex flex-wrap gap-1">
                                        <?php foreach ($categories_list as $catName): ?>
                                            <button type="button" class="btn btn-sm btn-light border py-0 px-2 small text-muted" onclick="toggleCategory('<?php echo htmlspecialchars(addslashes($catName)); ?>')">
                                                + <?php echo htmlspecialchars($catName); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                <small class="text-muted d-block mt-1">Click a tag above to add, or type separated by commas.</small>
                            </div>

                            <!-- Tags -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small">Tags</label>
                                <input type="text" class="form-control" name="tags" value="<?php echo htmlspecialchars($post['tags']); ?>" placeholder="Patna, By-Election, 2026, ECI">
                                <small class="text-muted">Comma-separated tags.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<script>
$(document).ready(function() {
    // Initialize Summernote RTF Editor
    $('#content').summernote({
        placeholder: 'Compose your article content, insert images, tables, headings, and embeds here...',
        tabsize: 2,
        height: 480,
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'italic', 'underline', 'strikethrough', 'superscript', 'subscript', 'clear']],
            ['fontname', ['fontname']],
            ['fontsize', ['fontsize']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph', 'height']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video', 'hr']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ]
    });

    // Auto-generate slug from title for new posts
    const isEditMode = <?php echo $is_edit ? 'true' : 'false'; ?>;
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if (!isEditMode && titleInput && slugInput) {
        titleInput.addEventListener('input', function() {
            if (!slugInput.dataset.touched) {
                slugInput.value = this.value
                    .toLowerCase()
                    .trim()
                    .replace(/[^\w\s-]/g, '')
                    .replace(/[\s_-]+/g, '-')
                    .replace(/^-+|-+$/g, '');
            }
        });

        slugInput.addEventListener('input', function() {
            this.dataset.touched = "true";
        });
    }
});

function updateImagePreview(url) {
    const img = document.getElementById('previewImg');
    const txt = document.getElementById('noPreviewText');
    if (url && url.trim()) {
        img.src = url;
        img.classList.remove('d-none');
        img.style.display = 'block';
        if (txt) txt.style.display = 'none';
    } else {
        img.classList.add('d-none');
        img.style.display = 'none';
        if (txt) txt.style.display = 'inline';
    }
}

function toggleCategory(name) {
    const input = document.getElementById('categoriesInput');
    let current = input.value.split(',').map(s => s.trim()).filter(Boolean);
    if (!current.includes(name)) {
        current.push(name);
        input.value = current.join(', ');
    }
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
