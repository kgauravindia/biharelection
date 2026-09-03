<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

// Handle Add / Edit Category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_category'])) {
    $cat_id = isset($_POST['cat_id']) ? (int)$_POST['cat_id'] : 0;
    $name = trim($_POST['name'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = "Please enter a category name.";
    } else {
        if (empty($slug)) {
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
            if (empty($slug)) $slug = 'cat-' . time();
        }

        if ($conn) {
            if ($cat_id > 0) {
                // Update
                $stmt = $conn->prepare("UPDATE `categories` SET `name` = ?, `slug` = ?, `description` = ? WHERE `id` = ?");
                if ($stmt) {
                    $stmt->bind_param("sssi", $name, $slug, $description, $cat_id);
                    if ($stmt->execute()) {
                        $message = "Category '{$name}' updated successfully.";
                    } else {
                        $error = "Error updating category: " . $conn->error;
                    }
                }
            } else {
                // Insert
                $stmt = $conn->prepare("INSERT INTO `categories` (`name`, `slug`, `description`) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sss", $name, $slug, $description);
                    if ($stmt->execute()) {
                        $message = "Category '{$name}' created successfully.";
                    } else {
                        $error = "Error creating category: " . $conn->error;
                    }
                }
            }
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($conn && $del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `categories` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Category deleted successfully.";
            } else {
                $error = "Error deleting category.";
            }
        }
    }
}

// Fetch all categories and sync live counts
$categories = [];
if ($conn) {
    // Sync post counts
    $cats_raw = $conn->query("SELECT * FROM `categories` ORDER BY `posts_count` DESC, `name` ASC");
    if ($cats_raw) {
        while ($r = $cats_raw->fetch_assoc()) {
            // Count live published posts
            $safe_name = $conn->real_escape_string($r['name']);
            $cnt_res = $conn->query("SELECT COUNT(*) as c FROM `posts` WHERE `status` = 'published' AND `categories` LIKE '%$safe_name%'");
            $r['live_count'] = $cnt_res ? (int)$cnt_res->fetch_assoc()['c'] : 0;
            $categories[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories Management — Bihar Election Admin</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Categories Directory</h1>
                <p class="text-muted mb-0">Organize articles and reports across Vidhan Sabha, Panchayat, and Lok Sabha topics.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="../category-sitemap.xml" target="_blank" class="btn btn-outline-secondary btn-sm fw-semibold shadow-sm bg-white">
                    <i class="fas fa-sitemap me-1 text-warning"></i> Category XML Sitemap
                </a>
                <button type="button" class="btn btn-danger btn-sm fw-semibold shadow-sm" onclick="openAddModal()">
                    <i class="fas fa-plus me-1"></i> Add New Category
                </button>
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlspecialchars($message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <!-- Left Column: Add / Quick Create Form -->
            <div class="col-lg-4">
                <div class="section-card">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark" id="formHeader">
                            <i class="fas fa-folder-plus me-2 text-danger"></i> Add New Category
                        </h6>
                    </div>
                    <div class="section-card-body">
                        <form method="POST" action="categories.php" id="catForm">
                            <input type="hidden" name="save_category" value="1">
                            <input type="hidden" name="cat_id" id="cat_id" value="0">

                            <div class="mb-3">
                                <label for="name" class="form-label fw-bold small">Category Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control fw-semibold" id="name" name="name" required placeholder="e.g., Vidhan Sabha">
                            </div>

                            <div class="mb-3">
                                <label for="slug" class="form-label fw-bold small text-muted">URL Slug</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted small">/category/</span>
                                    <input type="text" class="form-control font-monospace" id="slug" name="slug" placeholder="e.g., vidhan-sabha">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label fw-bold small">Description / SEO Overview</label>
                                <textarea class="form-control" id="description" name="description" rows="4" placeholder="Brief category description for search engines and archive header..."></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-danger fw-bold" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Save Category
                                </button>
                                <button type="button" class="btn btn-outline-secondary d-none" id="cancelEditBtn" onclick="resetCatForm()">
                                    Cancel Edit
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Right Column: Categories List Table -->
            <div class="col-lg-8">
                <div class="section-card">
                    <div class="section-card-header d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-folder-tree me-2 text-primary"></i> All Categories (<?php echo count($categories); ?>)
                        </h6>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 50px;">#</th>
                                    <th>Name & Slug</th>
                                    <th>Description</th>
                                    <th style="width: 100px;">Posts</th>
                                    <th class="text-end" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($categories)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">No categories created yet.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($categories as $i => $c): ?>
                                        <tr>
                                            <td class="text-muted small"><?php echo $i + 1; ?></td>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['name']); ?></div>
                                                <div class="small text-muted font-monospace">/category/<?php echo htmlspecialchars($c['slug']); ?></div>
                                            </td>
                                            <td>
                                                <div class="small text-muted line-clamp-2" style="max-width: 280px;">
                                                    <?php echo !empty($c['description']) ? htmlspecialchars($c['description']) : '<span class="opacity-50">—</span>'; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <a href="posts.php?category=<?php echo urlencode($c['name']); ?>" class="badge bg-light text-dark border text-decoration-none">
                                                    <?php echo $c['live_count']; ?> posts
                                                </a>
                                            </td>
                                            <td class="text-end">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="<?php echo SITE_URL; ?>/blog?category=<?php echo urlencode($c['name']); ?>" target="_blank" class="btn btn-outline-secondary" title="View Live Archive">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-primary" title="Edit Category" onclick='editCategory(<?php echo json_encode($c, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <a href="categories.php?delete_id=<?php echo $c['id']; ?>" class="btn btn-outline-danger" title="Delete Category" onclick="return confirm('Delete category <?php echo htmlspecialchars(addslashes($c['name'])); ?>?');">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<script>
function editCategory(cat) {
    document.getElementById('cat_id').value = cat.id;
    document.getElementById('name').value = cat.name;
    document.getElementById('slug').value = cat.slug;
    document.getElementById('description').value = cat.description || '';
    
    document.getElementById('formHeader').innerHTML = '<i class="fas fa-edit me-2 text-warning"></i> Edit Category: ' + cat.name;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-1"></i> Update Category';
    document.getElementById('cancelEditBtn').classList.remove('d-none');
    
    document.getElementById('name').focus();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetCatForm() {
    document.getElementById('catForm').reset();
    document.getElementById('cat_id').value = '0';
    document.getElementById('formHeader').innerHTML = '<i class="fas fa-folder-plus me-2 text-danger"></i> Add New Category';
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save me-1"></i> Save Category';
    document.getElementById('cancelEditBtn').classList.add('d-none');
}

function openAddModal() {
    resetCatForm();
    document.getElementById('name').focus();
}

// Auto-generate slug
document.getElementById('name').addEventListener('input', function() {
    const slugInput = document.getElementById('slug');
    if (!slugInput.dataset.touched && document.getElementById('cat_id').value === '0') {
        slugInput.value = this.value
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }
});

document.getElementById('slug').addEventListener('input', function() {
    this.dataset.touched = "true";
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
