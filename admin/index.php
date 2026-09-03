<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$conn = getAdminDB();

// Initialize stats
$stats = [
    'districts' => 38,
    'constituencies' => 243,
    'candidates' => 0,
    'mukhiyas' => 0,
    'sarpanchs' => 0,
    'zila_parishad' => 0,
    'whatsapp_subscribers' => 0,
    'ads_count' => 0,
    'ads_revenue' => 0,
    'contacts_count' => 0,
    'new_contacts' => 0
];

if ($conn) {
    // Candidates
    $r = $conn->query("SELECT COUNT(*) as c FROM `candidates`");
    if ($r) $stats['candidates'] = $r->fetch_assoc()['c'];

    // Mukhiyas
    $r = $conn->query("SELECT COUNT(*) as c FROM `mukhiyas`");
    if ($r) $stats['mukhiyas'] = $r->fetch_assoc()['c'];

    // Sarpanchs
    $r = $conn->query("SELECT COUNT(*) as c FROM `sarpanchs`");
    if ($r) $stats['sarpanchs'] = $r->fetch_assoc()['c'];

    // Zila Parishad
    $r = $conn->query("SELECT COUNT(*) as c FROM `zila_parishad_members`");
    if ($r) $stats['zila_parishad'] = $r->fetch_assoc()['c'];

    // WhatsApp Subscribers
    $r = $conn->query("SELECT COUNT(*) as c FROM `whatsapp_subscribers`");
    if ($r) $stats['whatsapp_subscribers'] = $r->fetch_assoc()['c'];

    // Ads
    $r = $conn->query("SELECT COUNT(*) as c, SUM(amount) as s FROM `advertisements`");
    if ($r) {
        $row = $r->fetch_assoc();
        $stats['ads_count'] = $row['c'] ?? 0;
        $stats['ads_revenue'] = $row['s'] ?? 0;
    }

    // Contacts
    $r = $conn->query("SELECT COUNT(*) as c, SUM(CASE WHEN `status` = 'NEW' THEN 1 ELSE 0 END) as nw FROM `contacts`");
    if ($r) {
        $row = $r->fetch_assoc();
        $stats['contacts_count'] = $row['c'] ?? 0;
        $stats['new_contacts'] = $row['nw'] ?? 0;
    }

    // Blog Posts
    $r = $conn->query("SELECT COUNT(*) as c FROM `posts` WHERE `status` = 'published'");
    if ($r) $stats['posts_count'] = $r->fetch_assoc()['c'] ?? 0;
}

// Fallback counts from JSON if DB was empty for candidates/mukhiyas
if ($stats['candidates'] === 0) {
    $c_list = DataProvider::getCandidates();
    $stats['candidates'] = count($c_list);
}

// Fetch party distribution for constituencies
$party_counts = [
    'RJD' => 75,
    'BJP' => 74,
    'JD(U)' => 43,
    'INC' => 19,
    'CPI-ML' => 12,
    'HAM(S)' => 4,
    'VIP' => 4,
    'AIMIM' => 1,
    'Others' => 11
];

if ($conn) {
    $pr = $conn->query("SELECT `current_party`, COUNT(*) as c FROM `constituencies` WHERE `current_party` IS NOT NULL AND `current_party` != '' GROUP BY `current_party` ORDER BY c DESC LIMIT 8");
    if ($pr && $pr->num_rows > 0) {
        $party_counts = [];
        while ($prow = $pr->fetch_assoc()) {
            $party_counts[$prow['current_party']] = (int)$prow['c'];
        }
    }
}

// Recent Contacts
$recent_contacts = [];
if ($conn) {
    $cr = $conn->query("SELECT * FROM `contacts` ORDER BY `created_at` DESC LIMIT 5");
    if ($cr) {
        while ($crow = $cr->fetch_assoc()) {
            $recent_contacts[] = $crow;
        }
    }
}

// Recent Candidates
$recent_candidates = [];
if ($conn) {
    $cand_r = $conn->query("SELECT * FROM `candidates` ORDER BY `id` DESC LIMIT 5");
    if ($cand_r && $cand_r->num_rows > 0) {
        while ($cand_row = $cand_r->fetch_assoc()) {
            $recent_candidates[] = $cand_row;
        }
    }
}
if (empty($recent_candidates)) {
    $all_c = DataProvider::getCandidates();
    $recent_candidates = array_slice($all_c, 0, 5);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bihar Election — Admin Control Hub</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="admin-layout">
    <?php include 'admin-menu.php'; ?>
    
    <main class="main-content">
        <?php include 'admin-header.php'; ?>
        
        <!-- Welcome Hero Banner -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-2">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif; color: #0f172a;">Executive Control Hub</h1>
                <p class="text-muted mb-0">Overview of 243 Bihar Assembly Constituencies, Candidate Aspirants & Local Bodies.</p>
            </div>
            <div class="mt-3 mt-md-0 d-flex gap-2">
                <a href="edit-candidate.php" class="btn btn-danger btn-sm px-3 py-2 fw-semibold rounded-3 shadow-sm">
                    <i class="fas fa-user-plus me-1"></i> Add Candidate
                </a>
                <a href="sitemap.php" class="btn btn-outline-dark btn-sm px-3 py-2 fw-semibold rounded-3 shadow-sm bg-white">
                    <i class="fas fa-sitemap me-1"></i> Generate Sitemap
                </a>
            </div>
        </div>

        <!-- Primary KPI Metrics Grid -->
        <div class="row g-3 mb-4">
            <!-- Constituencies -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="stat-label">Vidhan Sabha ACs</span>
                            <div class="stat-value text-danger"><?php echo $stats['constituencies']; ?></div>
                        </div>
                        <div class="stat-icon" style="background: #fee2e2; color: #d31027;">
                            <i class="fas fa-landmark"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-check-circle text-success me-1"></i> 38 Districts Covered
                    </div>
                </div>
            </div>

            <!-- Candidates -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="stat-label">Candidate Aspirants</span>
                            <div class="stat-value text-primary"><?php echo number_format($stats['candidates']); ?></div>
                        </div>
                        <div class="stat-icon" style="background: #e0e7ff; color: #4338ca;">
                            <i class="fas fa-user-tie"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <a href="candidates.php" class="text-decoration-none fw-semibold text-primary">Manage Profiles &rarr;</a>
                    </div>
                </div>
            </div>

            <!-- Mukhiyas & Local Bodies -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="stat-label">Mukhiya & Sarpanch</span>
                            <div class="stat-value text-success"><?php echo number_format($stats['mukhiyas'] + $stats['sarpanchs']); ?></div>
                        </div>
                        <div class="stat-icon" style="background: #dcfce7; color: #15803d;">
                            <i class="fas fa-users-gear"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <span class="badge bg-light text-dark border"><?php echo number_format($stats['mukhiyas']); ?> Mukhiyas</span>
                        <span class="badge bg-light text-dark border ms-1"><?php echo number_format($stats['sarpanchs']); ?> Sarpanchs</span>
                    </div>
                </div>
            </div>

            <!-- WhatsApp & Leads -->
            <div class="col-sm-6 col-xl-3">
                <div class="stat-card-v2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <span class="stat-label">WhatsApp Alerts</span>
                            <div class="stat-value text-warning" style="color: #d97706 !important;"><?php echo number_format($stats['whatsapp_subscribers']); ?></div>
                        </div>
                        <div class="stat-icon" style="background: #fef3c7; color: #b45309;">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                    </div>
                    <div class="mt-2 text-muted small">
                        <i class="fas fa-envelope text-info me-1"></i> <?php echo $stats['contacts_count']; ?> Total Leads
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Access Navigation Cards -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg">
                <a href="posts.php" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 bg-white hover-elevate transition text-dark text-center h-100">
                    <div class="mb-2"><i class="fas fa-newspaper fa-2x text-danger"></i></div>
                    <h6 class="fw-bold mb-1">Blog Articles</h6>
                    <small class="text-muted"><?php echo number_format($stats['posts_count'] ?? 167); ?> Published</small>
                </a>
            </div>
            <div class="col-6 col-lg">
                <a href="candidates.php" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 bg-white hover-elevate transition text-dark text-center h-100">
                    <div class="mb-2"><i class="fas fa-id-card fa-2x text-primary"></i></div>
                    <h6 class="fw-bold mb-1">Candidates</h6>
                    <small class="text-muted">Edit & Verify</small>
                </a>
            </div>
            <div class="col-6 col-lg">
                <a href="constituencies.php" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 bg-white hover-elevate transition text-dark text-center h-100">
                    <div class="mb-2"><i class="fas fa-landmark-dome fa-2x text-info"></i></div>
                    <h6 class="fw-bold mb-1">Constituencies</h6>
                    <small class="text-muted">243 Vidhan Sabha</small>
                </a>
            </div>
            <div class="col-6 col-lg">
                <a href="mukhiyas.php" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 bg-white hover-elevate transition text-dark text-center h-100">
                    <div class="mb-2"><i class="fas fa-address-book fa-2x text-success"></i></div>
                    <h6 class="fw-bold mb-1">Panchayats</h6>
                    <small class="text-muted">Local Bodies</small>
                </a>
            </div>
            <div class="col-6 col-lg">
                <a href="manage-ads.php" class="card text-decoration-none border-0 shadow-sm rounded-3 p-3 bg-white hover-elevate transition text-dark text-center h-100">
                    <div class="mb-2"><i class="fas fa-bullhorn fa-2x text-warning"></i></div>
                    <h6 class="fw-bold mb-1">Ad Campaigns</h6>
                    <small class="text-muted">Monetization</small>
                </a>
            </div>
        </div>

        <!-- Charts and Breakdown Section -->
        <div class="row g-4 mb-4">
            <!-- Party Distribution Chart -->
            <div class="col-lg-7">
                <div class="section-card h-100 mb-0">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-bar me-2 text-danger"></i> Vidhan Sabha Party Representation</h6>
                        <span class="badge bg-light text-dark border">243 Total Seats</span>
                    </div>
                    <div class="section-card-body">
                        <div style="height: 280px; position: relative;">
                            <canvas id="partyChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Platform Health & Summary -->
            <div class="col-lg-5">
                <div class="section-card h-100 mb-0">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-server me-2 text-primary"></i> Platform Core Data Summary</h6>
                        <span class="badge bg-success text-white">Live System</span>
                    </div>
                    <div class="section-card-body">
                        <ul class="list-group list-group-flush small">
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-map text-secondary me-2"></i> Bihar Districts</span>
                                <span class="fw-bold">38 Districts</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-vote-yea text-secondary me-2"></i> Assembly Constituencies</span>
                                <span class="fw-bold">243 Seats (AC 1-243)</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-city text-secondary me-2"></i> Lok Sabha Parliamentary Seats</span>
                                <span class="fw-bold">40 Lok Sabha Seats</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-building-flag text-secondary me-2"></i> Zila Parishads</span>
                                <span class="fw-bold">38 Councils</span>
                            </li>
                            <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                                <span><i class="fas fa-coins text-secondary me-2"></i> Ad Campaigns Active</span>
                                <span class="badge bg-warning text-dark"><?php echo $stats['ads_count']; ?> Bookings</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Inquiries & Candidates Grid -->
        <div class="row g-4">
            <!-- Latest Inquiries -->
            <div class="col-lg-6">
                <div class="section-card mb-0">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-envelope-open-text me-2 text-info"></i> Recent Leads & Inquiries</h6>
                        <a href="contacts.php" class="small text-decoration-none fw-semibold">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Name / Contact</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_contacts)): ?>
                                    <?php foreach ($recent_contacts as $c): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($c['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($c['mobile']); ?></small>
                                            </td>
                                            <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($c['inquiry_type'] ?? 'GENERAL'); ?></span></td>
                                            <td>
                                                <?php if (($c['status'] ?? 'NEW') === 'NEW'): ?>
                                                    <span class="badge bg-danger">New</span>
                                                <?php else: ?>
                                                    <span class="badge bg-success">Handled</span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end">
                                                <a href="https://wa.me/91<?php echo preg_replace('/[^0-9]/', '', $c['mobile']); ?>" target="_blank" class="btn btn-sm btn-outline-success rounded-circle" title="WhatsApp">
                                                    <i class="fab fa-whatsapp"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2 d-block text-muted"></i>
                                            No inquiries recorded yet.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Recent Candidates -->
            <div class="col-lg-6">
                <div class="section-card mb-0">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-user-tie me-2 text-warning"></i> Recent Candidate Aspirants</h6>
                        <a href="candidates.php" class="small text-decoration-none fw-semibold">View All &rarr;</a>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Candidate</th>
                                    <th>Party</th>
                                    <th>Constituency</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($recent_candidates)): ?>
                                    <?php foreach ($recent_candidates as $cand): ?>
                                        <tr>
                                            <td>
                                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($cand['name']); ?></div>
                                                <small class="text-muted"><?php echo htmlspecialchars($cand['name_hi'] ?? ''); ?></small>
                                            </td>
                                            <td><span class="badge bg-primary text-white"><?php echo htmlspecialchars($cand['party_short'] ?? $cand['party'] ?? 'IND'); ?></span></td>
                                            <td><?php echo htmlspecialchars($cand['constituency']); ?></td>
                                            <td class="text-end">
                                                <a href="edit-candidate.php?id=<?php echo $cand['id'] ?? 0; ?>&slug=<?php echo urlencode($cand['slug'] ?? ''); ?>" class="btn btn-sm btn-outline-dark rounded-circle" title="Edit Profile">
                                                    <i class="fas fa-pen"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">No candidates listed yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>

<script>
// Render Party Chart
const ctx = document.getElementById('partyChart');
if (ctx) {
    const partyData = <?php echo json_encode($party_counts); ?>;
    const labels = Object.keys(partyData);
    const data = Object.values(partyData);

    const colors = [
        '#16a34a', // RJD Green
        '#ea580c', // BJP Saffron
        '#0284c7', // JDU Blue
        '#0891b2', // INC
        '#dc2626', // CPI-ML Red
        '#7c3aed', // HAM
        '#f59e0b', // VIP
        '#64748b'  // Others
    ];

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Seats in Vidhan Sabha',
                data: data,
                backgroundColor: colors.slice(0, labels.length),
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });
}
</script>
