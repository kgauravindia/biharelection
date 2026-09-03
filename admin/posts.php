<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();
$message = '';
$error = '';

if (isset($_GET['msg']) && $_GET['msg'] === 'saved') {
    $message = "Post saved and published successfully.";
}

// Handle Delete Post
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    if ($conn && $del_id > 0) {
        $stmt = $conn->prepare("DELETE FROM `posts` WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $del_id);
            if ($stmt->execute()) {
                $message = "Post #{$del_id} deleted successfully.";
            } else {
                $error = "Error deleting post.";
            }
        }
    }
}

// Handle Status Toggle (published <-> draft)
if (isset($_GET['toggle_status'])) {
    $t_id = (int)$_GET['toggle_status'];
    if ($conn && $t_id > 0) {
        $stmt = $conn->prepare("UPDATE `posts` SET `status` = IF(`status` = 'published', 'draft', 'published') WHERE `id` = ?");
        if ($stmt) {
            $stmt->bind_param("i", $t_id);
            $stmt->execute();
            header("Location: posts.php?msg=saved");
            exit();
        }
    }
}

// Filters & Pagination
$limit = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$filter_category = isset($_GET['category']) ? trim($_GET['category']) : '';
$filter_status = isset($_GET['status']) ? trim($_GET['status']) : '';

$posts = [];
$total_rows = 0;
$total_published = 0;
$total_drafts = 0;
$categories_list = [];

if ($conn) {
    // Stats
    $stat_pub = $conn->query("SELECT COUNT(*) as c FROM `posts` WHERE `status` = 'published'");
    if ($stat_pub) $total_published = (int)$stat_pub->fetch_assoc()['c'];

    $stat_drf = $conn->query("SELECT COUNT(*) as c FROM `posts` WHERE `status` = 'draft'");
    if ($stat_drf) $total_drafts = (int)$stat_drf->fetch_assoc()['c'];

    // Categories for filter
    $cat_res = $conn->query("SELECT DISTINCT categories FROM `posts` WHERE categories IS NOT NULL AND categories != ''");
    if ($cat_res) {
        while ($cr = $cat_res->fetch_assoc()) {
            $split_cats = array_map('trim', explode(',', $cr['categories']));
            foreach ($split_cats as $sc) {
                if (!empty($sc) && !in_array($sc, $categories_list)) {
                    $categories_list[] = $sc;
                }
            }
        }
        sort($categories_list);
    }

    // Build Query
    $where = "1=1";
    if (!empty($search)) {
        $safe_search = $conn->real_escape_string($search);
        $where .= " AND (`title` LIKE '%$safe_search%' OR `slug` LIKE '%$safe_search%' OR `content` LIKE '%$safe_search%' OR `tags` LIKE '%$safe_search%')";
    }
    if (!empty($filter_category)) {
        $safe_cat = $conn->real_escape_string($filter_category);
        $where .= " AND `categories` LIKE '%$safe_cat%'";
    }
    if (!empty($filter_status)) {
        $safe_status = $conn->real_escape_string($filter_status);
        $where .= " AND `status` = '$safe_status'";
    }

    $c_res = $conn->query("SELECT COUNT(*) as c FROM `posts` WHERE $where");
    if ($c_res) $total_rows = (int)$c_res->fetch_assoc()['c'];

    $q_res = $conn->query("SELECT id, wp_id, title, slug, featured_image, categories, tags, author_name, status, views_count, published_at FROM `posts` WHERE $where ORDER BY `published_at` DESC, `id` DESC LIMIT $offset, $limit");
    if ($q_res) {
        while ($r = $q_res->fetch_assoc()) {
            $posts[] = $r;
        }
    }
}

$total_pages = ceil($total_rows / $limit);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog & Editorial Posts — Bihar Election Admin</title>
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
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Blog & Editorial Articles</h1>
                <p class="text-muted mb-0">Manage and publish news reports, Vidhan Sabha analysis, and election guides.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="../post-sitemap.xml" target="_blank" class="btn btn-outline-secondary fw-semibold px-3 py-2 rounded-3 shadow-sm bg-white">
                    <i class="fas fa-sitemap me-1 text-warning"></i> XML Post Sitemap
                </a>
                <a href="edit-post.php" class="btn btn-danger fw-semibold px-3 py-2 rounded-3 shadow-sm">
                    <i class="fas fa-plus me-1"></i> Add New Article
                </a>
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

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-danger-subtle text-danger">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div>
                        <div class="stat-label">Total Articles</div>
                        <div class="stat-value"><?php echo number_format($total_published + $total_drafts); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fas fa-globe"></i>
                    </div>
                    <div>
                        <div class="stat-label">Published Live</div>
                        <div class="stat-value"><?php echo number_format($total_published); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-warning-subtle text-warning">
                        <i class="fas fa-pen-ruler"></i>
                    </div>
                    <div>
                        <div class="stat-label">Drafts</div>
                        <div class="stat-value"><?php echo number_format($total_drafts); ?></div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card">
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fas fa-folder-tree"></i>
                    </div>
                    <div>
                        <div class="stat-label">Categories</div>
                        <div class="stat-value"><?php echo count($categories_list); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters & Search Toolbar -->
        <div class="section-card mb-4">
            <div class="section-card-body p-3">
                <form method="GET" action="posts.php" class="row g-2 align-items-center">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search by title, slug, tag, or content..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories_list as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $filter_category === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="published" <?php echo $filter_status === 'published' ? 'selected' : ''; ?>>Published</option>
                            <option value="draft" <?php echo $filter_status === 'draft' ? 'selected' : ''; ?>>Draft</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-dark w-100 fw-semibold"><i class="fas fa-filter me-1"></i> Filter</button>
                        <?php if (!empty($search) || !empty($filter_category) || !empty($filter_status)): ?>
                            <a href="posts.php" class="btn btn-outline-secondary" title="Clear Filters"><i class="fas fa-times"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Posts Table -->
        <div class="section-card">
            <div class="section-card-header d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fas fa-list me-2 text-danger"></i> Articles & Reports (<?php echo number_format($total_rows); ?> Total)
                </h6>
                <span class="small text-muted">Page <?php echo $page; ?> of <?php echo max(1, $total_pages); ?></span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th style="width: 80px;">Cover</th>
                            <th>Title & URL Slug</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Status</th>
                            <th>Published</th>
                            <th class="text-end" style="width: 140px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 text-secondary opacity-50 d-block"></i>
                                    No articles found matching your criteria.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($posts as $idx => $p): ?>
                                <tr>
                                    <td class="text-muted small"><?php echo $offset + $idx + 1; ?></td>
                                    <td>
                                        <?php if (!empty($p['featured_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($p['featured_image']); ?>" alt="Cover" class="rounded object-fit-cover shadow-sm border" width="60" height="40" onerror="this.onerror=null; this.src='../assets/image/logo.png';">
                                        <?php else: ?>
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width: 60px; height: 40px; font-size: 10px;">
                                                <i class="fas fa-image"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark text-truncate" style="max-width: 380px;">
                                            <a href="edit-post.php?id=<?php echo $p['id']; ?>" class="text-dark text-decoration-none hover-primary">
                                                <?php echo htmlspecialchars($p['title']); ?>
                                            </a>
                                        </div>
                                        <div class="small text-muted text-truncate font-monospace" style="max-width: 380px;">
                                            /blog/<?php echo htmlspecialchars(urldecode($p['slug'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if (!empty($p['categories'])): ?>
                                            <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($p['categories']); ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary-subtle text-secondary">General</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small text-muted">
                                        <?php echo htmlspecialchars($p['author_name'] ?: 'Editorial Team'); ?>
                                    </td>
                                    <td>
                                        <a href="posts.php?toggle_status=<?php echo $p['id']; ?>" class="text-decoration-none" title="Click to toggle status">
                                            <?php if ($p['status'] === 'published'): ?>
                                                <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i> Published</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i> Draft</span>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="small text-muted">
                                        <?php echo date('d M Y', strtotime($p['published_at'])); ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group btn-group-sm">
                                            <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($p['slug']); ?>" target="_blank" class="btn btn-outline-secondary" title="View Live Post">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="edit-post.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-primary" title="Edit Article">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="posts.php?delete_id=<?php echo $p['id']; ?>" class="btn btn-outline-danger" title="Delete Article" onclick="return confirm('Are you sure you want to permanently delete this article?');">
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

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="section-card-footer d-flex justify-content-between align-items-center p-3">
                    <span class="small text-muted">Showing <?php echo $offset + 1; ?> to <?php echo min($offset + $limit, $total_rows); ?> of <?php echo number_format($total_rows); ?> entries</span>
                    <nav>
                        <ul class="pagination pagination-sm mb-0">
                            <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($filter_category); ?>&status=<?php echo urlencode($filter_status); ?>">&laquo;</a>
                            </li>
                            <?php
                            $start_page = max(1, $page - 2);
                            $end_page = min($total_pages, $page + 2);
                            for ($i = $start_page; $i <= $end_page; $i++):
                            ?>
                                <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($filter_category); ?>&status=<?php echo urlencode($filter_status); ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                            <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($filter_category); ?>&status=<?php echo urlencode($filter_status); ?>">&raquo;</a>
                            </li>
                        </ul>
                    </nav>
                </div>
            <?php endif; ?>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
