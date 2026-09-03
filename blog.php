<?php
/**
 * BiharElection.com - Blog & Editorial News Portal
 * Handles both /blog (archive/listing) and /blog/[slug] (single article viewer)
 */
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/auth_helper.php';

$pdo = Database::getConnection();
$raw_slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';
$slug = trim($raw_slug, '/');
$is_single = !empty($slug);

$article = null;
$related_posts = [];

if ($is_single && $pdo) {
    $decoded_slug = urldecode($slug);
    $encoded_slug = urlencode($decoded_slug);

    // 1. Fetch single article by exact or decoded slug
    $stmt = $pdo->prepare("SELECT * FROM `posts` WHERE (`slug` = :s1 OR `slug` = :s2 OR `slug` = :s3) AND `status` = 'published' LIMIT 1");
    $stmt->execute([
        ':s1' => $slug,
        ':s2' => $decoded_slug,
        ':s3' => $encoded_slug
    ]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Fallback search with LIKE
    if (!$article) {
        $stmt = $pdo->prepare("SELECT * FROM `posts` WHERE (`slug` LIKE :f1 OR `slug` LIKE :f2) AND `status` = 'published' LIMIT 1");
        $stmt->execute([
            ':f1' => '%' . $decoded_slug . '%',
            ':f2' => '%' . str_replace('-', '%', $decoded_slug) . '%'
        ]);
        $article = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if ($article) {
        // Increment views count
        $updateViews = $pdo->prepare("UPDATE `posts` SET `views_count` = `views_count` + 1 WHERE `id` = :id");
        $updateViews->execute([':id' => $article['id']]);

        // Fetch related articles
        $relStmt = $pdo->prepare("SELECT id, title, slug, featured_image, categories, published_at FROM `posts` WHERE `id` != :id AND `status` = 'published' ORDER BY `published_at` DESC LIMIT 4");
        $relStmt->execute([':id' => $article['id']]);
        $related_posts = $relStmt->fetchAll(PDO::FETCH_ASSOC);

        // SEO Meta
        $pageTitle = htmlspecialchars($article['title']) . ' — Bihar Election News';
        $pageDescription = !empty($article['excerpt']) ? htmlspecialchars(strip_tags($article['excerpt'])) : htmlspecialchars(substr(strip_tags($article['content']), 0, 160));
        $pageCanonical = SITE_URL . '/blog/' . urlencode($article['slug']);
        $activeNav = 'blog';
    } else {
        // 404 Not Found
        header("HTTP/1.1 404 Not Found");
        $pageTitle = 'Article Not Found — Bihar Election';
        $pageDescription = 'The requested blog article could not be found.';
        $activeNav = 'blog';
    }
} else {
    // 2. Listing / Archive Page
    $limit = 12;
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $search = isset($_GET['q']) ? trim($_GET['q']) : '';
    $category_filter = isset($_GET['category']) ? trim($_GET['category']) : '';

    $posts = [];
    $total_posts = 0;
    $categories = [];

    if ($pdo) {
        // Categories list
        $catStmt = $pdo->query("SELECT DISTINCT categories FROM `posts` WHERE `status` = 'published' AND categories IS NOT NULL AND categories != ''");
        while ($cr = $catStmt->fetch(PDO::FETCH_ASSOC)) {
            $split = array_map('trim', explode(',', $cr['categories']));
            foreach ($split as $sc) {
                if (!empty($sc) && !in_array($sc, $categories)) {
                    $categories[] = $sc;
                }
            }
        }
        sort($categories);

        // Query posts
        $whereSql = "`status` = 'published'";
        $params = [];

        if (!empty($search)) {
            $whereSql .= " AND (`title` LIKE :search OR `content` LIKE :search OR `tags` LIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        if (!empty($category_filter)) {
            $whereSql .= " AND `categories` LIKE :cat";
            $params[':cat'] = '%' . $category_filter . '%';
        }

        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM `posts` WHERE $whereSql");
        $countStmt->execute($params);
        $total_posts = (int)$countStmt->fetchColumn();

        $fetchStmt = $pdo->prepare("SELECT id, title, slug, excerpt, content, featured_image, categories, tags, author_name, published_at FROM `posts` WHERE $whereSql ORDER BY `published_at` DESC LIMIT :offset, :limit");
        foreach ($params as $k => $v) {
            $fetchStmt->bindValue($k, $v);
        }
        $fetchStmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $fetchStmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $fetchStmt->execute();
        $posts = $fetchStmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $total_pages = ceil($total_posts / $limit);
    $pageTitle = 'Bihar Election News, Analysis & Blog Articles 2026';
    $pageDescription = 'Read the latest Bihar election news, constituency insights, Panchayat delimitation reports, candidate lists, and political analysis.';
    $pageCanonical = SITE_URL . '/blog/';
    $activeNav = 'blog';
}

include __DIR__ . '/header.php';
?>

<main class="py-4 py-lg-5" style="background: #f8fafc; min-height: 80vh;">
    <div class="container">

        <?php if ($is_single && $article): ?>
            <!-- ================= SINGLE ARTICLE VIEW ================= -->
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb small">
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/blog" class="text-decoration-none">Blog & News</a></li>
                    <?php if (!empty($article['categories'])): ?>
                        <li class="breadcrumb-item"><a href="<?php echo SITE_URL; ?>/blog?category=<?php echo urlencode(trim(explode(',', $article['categories'])[0])); ?>" class="text-decoration-none"><?php echo htmlspecialchars(trim(explode(',', $article['categories'])[0])); ?></a></li>
                    <?php endif; ?>
                    <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;"><?php echo htmlspecialchars($article['title']); ?></li>
                </ol>
            </nav>

            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="bg-white p-4 p-md-5 rounded-4 shadow-sm border">
                        <!-- Category Badge -->
                        <?php if (!empty($article['categories'])): ?>
                            <div class="mb-3">
                                <?php foreach (explode(',', $article['categories']) as $c): ?>
                                    <a href="<?php echo SITE_URL; ?>/blog?category=<?php echo urlencode(trim($c)); ?>" class="badge bg-danger text-white text-decoration-none px-3 py-2 rounded-pill me-1 mb-1">
                                        <?php echo htmlspecialchars(trim($c)); ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Title -->
                        <h1 class="h2 fw-bold text-dark mb-3" style="font-family: 'Outfit', sans-serif; line-height: 1.3;">
                            <?php echo htmlspecialchars($article['title']); ?>
                        </h1>

                        <!-- Meta bar -->
                        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small pb-3 mb-4 border-bottom">
                            <span><i class="bi bi-person-circle me-1 text-danger"></i> <?php echo htmlspecialchars($article['author_name'] ?: 'Bihar Election Editorial Team'); ?></span>
                            <span><i class="bi bi-calendar3 me-1 text-primary"></i> <?php echo date('d M Y, h:i A', strtotime($article['published_at'])); ?></span>
                            <?php if (!empty($article['views_count'])): ?>
                                <span><i class="bi bi-eye me-1 text-success"></i> <?php echo number_format($article['views_count']); ?> views</span>
                            <?php endif; ?>
                        </div>

                        <!-- Featured Image -->
                        <?php if (!empty($article['featured_image'])): ?>
                            <div class="mb-4 text-center">
                                <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="img-fluid rounded-4 shadow-sm border w-100 object-fit-cover" style="max-height: 440px;" onerror="this.style.display='none';">
                            </div>
                        <?php endif; ?>

                        <!-- Excerpt Box -->
                        <?php if (!empty($article['excerpt'])): ?>
                            <div class="lead bg-light p-3 rounded-3 border-start border-danger border-4 mb-4 text-secondary">
                                <?php echo nl2br(htmlspecialchars($article['excerpt'])); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Article Content (Clean HTML) -->
                        <div class="article-content fs-6 text-dark" style="line-height: 1.8;">
                            <?php 
                                // Output raw HTML content
                                echo $article['content'];
                            ?>
                        </div>

                        <!-- Tags -->
                        <?php if (!empty($article['tags'])): ?>
                            <div class="pt-4 mt-4 border-top">
                                <h6 class="fw-bold small text-muted text-uppercase mb-2"><i class="bi bi-tags me-1"></i> Tags:</h6>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach (explode(',', $article['tags']) as $tag): ?>
                                        <?php if (!empty(trim($tag))): ?>
                                            <a href="<?php echo SITE_URL; ?>/blog?q=<?php echo urlencode(trim($tag)); ?>" class="badge bg-light text-dark border text-decoration-none px-2 py-1">
                                                #<?php echo htmlspecialchars(trim($tag)); ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Social Share -->
                        <div class="pt-4 mt-4 border-top d-flex flex-wrap align-items-center justify-content-between gap-3 bg-light p-3 rounded-3">
                            <span class="fw-bold text-dark small"><i class="bi bi-share-fill me-1 text-danger"></i> Share this update:</span>
                            <div class="d-flex gap-2">
                                <a href="https://api.whatsapp.com/send?text=<?php echo urlencode($article['title'] . ' ' . SITE_URL . '/blog/' . urlencode($article['slug'])); ?>" target="_blank" class="btn btn-success btn-sm rounded-pill px-3">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                                <a href="https://twitter.com/intent/tweet?text=<?php echo urlencode($article['title']); ?>&url=<?php echo urlencode(SITE_URL . '/blog/' . urlencode($article['slug'])); ?>" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                                    <i class="bi bi-twitter-x me-1"></i> X
                                </a>
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(SITE_URL . '/blog/' . urlencode($article['slug'])); ?>" target="_blank" class="btn btn-primary btn-sm rounded-pill px-3">
                                    <i class="bi bi-facebook me-1"></i> Facebook
                                </a>
                            </div>
                        </div>
                    </article>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Related Articles Widget -->
                    <div class="bg-white p-4 rounded-4 shadow-sm border mb-4">
                        <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom" style="font-family: 'Outfit', sans-serif;">
                            <i class="bi bi-newspaper me-2 text-danger"></i> Recent Updates
                        </h5>
                        <?php if (empty($related_posts)): ?>
                            <p class="text-muted small">No other recent updates.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($related_posts as $rp): ?>
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($rp['slug']); ?>" class="text-decoration-none d-flex gap-3 group-hover">
                                        <?php if (!empty($rp['featured_image'])): ?>
                                            <img src="<?php echo htmlspecialchars($rp['featured_image']); ?>" alt="" class="rounded-3 object-fit-cover flex-shrink-0" width="70" height="55" onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/image/logo.png';">
                                        <?php else: ?>
                                            <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted flex-shrink-0 border" style="width: 70px; height: 55px;">
                                                <i class="bi bi-image"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <h6 class="text-dark fw-bold mb-1 small line-clamp-2" style="line-height: 1.4;"><?php echo htmlspecialchars($rp['title']); ?></h6>
                                            <span class="text-muted" style="font-size: 11px;"><?php echo date('d M Y', strtotime($rp['published_at'])); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- WhatsApp Channel Widget -->
                    <div class="card border-0 shadow-sm rounded-4 text-white overflow-hidden" style="background: linear-gradient(135deg, #075e54, #128c7e);">
                        <div class="card-body p-4 text-center">
                            <i class="bi bi-whatsapp display-4 mb-2 d-block text-warning"></i>
                            <h5 class="fw-bold mb-2">Bihar Election WhatsApp Alerts</h5>
                            <p class="small opacity-90 mb-3">Join 25,000+ citizens getting daily candidate lists, booth data, and live results.</p>
                            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-warning fw-bold px-4 py-2 rounded-pill text-dark shadow-sm">
                                Join WhatsApp Channel &rarr;
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($is_single && !$article): ?>
            <!-- ================= ARTICLE NOT FOUND ================= -->
            <div class="text-center py-5 bg-white rounded-4 shadow-sm border p-5">
                <i class="bi bi-exclamation-octagon display-1 text-warning mb-3 d-block"></i>
                <h2 class="fw-bold text-dark">Article Not Found</h2>
                <p class="text-muted">The article you are looking for has been moved or updated.</p>
                <a href="<?php echo SITE_URL; ?>/blog" class="btn btn-danger fw-bold rounded-pill px-4 mt-2">
                    <i class="bi bi-arrow-left me-1"></i> Browse All Articles
                </a>
            </div>

        <?php else: ?>
            <!-- ================= BLOG ARCHIVE & LISTING VIEW ================= -->
            <div class="text-center mb-5">
                <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-3 py-2 rounded-pill mb-2">Editorial Desk</span>
                <h1 class="h2 fw-bold text-dark mb-2" style="font-family: 'Outfit', sans-serif;">Bihar Election News & Political Intelligence</h1>
                <p class="text-muted mx-auto" style="max-width: 650px;">Complete coverage of 243 Vidhan Sabha constituencies, Bihar Panchayat 2026 delimitation, candidate profiles, and official election results.</p>
            </div>

            <!-- Filters & Search Toolbar -->
            <div class="bg-white p-3 p-md-4 rounded-4 shadow-sm border mb-4">
                <form method="GET" action="<?php echo SITE_URL; ?>/blog" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" name="q" class="form-control border-start-0" placeholder="Search election news, candidates, seats..." value="<?php echo htmlspecialchars($search); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category_filter === $cat ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-danger w-100 fw-bold">Filter</button>
                        <?php if (!empty($search) || !empty($category_filter)): ?>
                            <a href="<?php echo SITE_URL; ?>/blog" class="btn btn-outline-secondary" title="Clear Filters"><i class="bi bi-x-lg"></i></a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Posts Grid -->
            <?php if (empty($posts)): ?>
                <div class="text-center py-5 bg-white rounded-4 shadow-sm border">
                    <i class="bi bi-journal-x display-4 text-muted mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark">No Articles Found</h5>
                    <p class="text-muted">Try adjusting your search keyword or selected category.</p>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($posts as $p): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden bg-white transition hover-elevate">
                                <?php if (!empty($p['featured_image'])): ?>
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($p['slug']); ?>">
                                        <img src="<?php echo htmlspecialchars($p['featured_image']); ?>" class="card-img-top object-fit-cover" height="200" alt="<?php echo htmlspecialchars($p['title']); ?>" onerror="this.onerror=null; this.src='<?php echo SITE_URL; ?>/assets/image/logo.png';">
                                    </a>
                                <?php else: ?>
                                    <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($p['slug']); ?>" class="bg-light d-flex align-items-center justify-content-center text-muted" style="height: 200px;">
                                        <img src="<?php echo SITE_URL; ?>/assets/image/logo.png" height="50" alt="Logo" class="opacity-75">
                                    </a>
                                <?php endif; ?>

                                <div class="card-body p-4 d-flex flex-column">
                                    <?php if (!empty($p['categories'])): ?>
                                        <div class="mb-2">
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1 small"><?php echo htmlspecialchars(trim(explode(',', $p['categories'])[0])); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <h5 class="card-title fw-bold mb-2">
                                        <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($p['slug']); ?>" class="text-dark text-decoration-none hover-primary">
                                            <?php echo htmlspecialchars($p['title']); ?>
                                        </a>
                                    </h5>

                                    <p class="card-text text-muted small flex-grow-1" style="display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                        <?php 
                                            if (!empty($p['excerpt'])) {
                                                echo htmlspecialchars(strip_tags($p['excerpt']));
                                            } else {
                                                echo htmlspecialchars(substr(strip_tags($p['content']), 0, 140)) . '...';
                                            }
                                        ?>
                                    </p>

                                    <div class="d-flex justify-content-between align-items-center pt-3 mt-2 border-top text-muted small">
                                        <span><i class="bi bi-calendar3 me-1"></i> <?php echo date('d M Y', strtotime($p['published_at'])); ?></span>
                                        <a href="<?php echo SITE_URL; ?>/blog/<?php echo htmlspecialchars($p['slug']); ?>" class="text-danger fw-bold text-decoration-none">
                                            Read More &rarr;
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <div class="d-flex justify-content-center mt-5">
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-md shadow-sm">
                                <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo SITE_URL; ?>/blog?page=<?php echo $page - 1; ?>&q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>">&laquo; Previous</a>
                                </li>
                                <?php
                                $start_page = max(1, $page - 2);
                                $end_page = min($total_pages, $page + 2);
                                for ($i = $start_page; $i <= $end_page; $i++):
                                ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="<?php echo SITE_URL; ?>/blog?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <li class="page-item <?php echo ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                    <a class="page-link" href="<?php echo SITE_URL; ?>/blog?page=<?php echo $page + 1; ?>&q=<?php echo urlencode($search); ?>&category=<?php echo urlencode($category_filter); ?>">Next &raquo;</a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

        <?php endif; ?>

    </div>
</main>

<?php include __DIR__ . '/footer.php'; ?>
