<?php
require_once __DIR__ . '/auth_check.php';
requireAdmin();

$districts = DataProvider::getDistricts();
$constituencies = DataProvider::getConstituencies();

// Calculate Division-wise counts
$division_counts = [];
foreach ($districts as $d) {
    $div = $d['division'] ?: 'Other';
    if (!isset($division_counts[$div])) {
        $division_counts[$div] = ['districts' => 0, 'acs' => 0, 'electors' => 0];
    }
    $division_counts[$div]['districts']++;
    $division_counts[$div]['acs'] += (int)($d['total_ac'] ?? 0);
    $division_counts[$div]['electors'] += (int)($d['total_electors'] ?? 0);
}

// Calculate Party distribution
$party_counts = [];
$res_counts = ['GEN' => 0, 'SC' => 0, 'ST' => 0];
foreach ($constituencies as $c) {
    $party = $c['current_party'] ?: 'Other';
    $party_counts[$party] = ($party_counts[$party] ?? 0) + 1;

    $res = strtoupper($c['reservation'] ?? 'GEN');
    if (isset($res_counts[$res])) {
        $res_counts[$res]++;
    } else {
        $res_counts['GEN']++;
    }
}
arsort($party_counts);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electoral Analytics & Insights — Bihar Election Admin</title>
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
        
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">Election Analytics & Trends</h1>
                <p class="text-muted mb-0">Demographic breakdowns, party strength distribution and administrative cluster analysis.</p>
            </div>
            <div class="mt-3 mt-md-0">
                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                    <i class="fas fa-chart-pie me-1"></i> Bihar Vidhan Sabha Intelligence
                </span>
            </div>
        </div>

        <!-- Visual Analytics Grid -->
        <div class="row g-4 mb-4">
            <!-- Party Composition -->
            <div class="col-lg-6">
                <div class="section-card h-100 mb-0">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-chart-pie me-2 text-danger"></i> Vidhan Sabha Seat Share by Party</h6>
                    </div>
                    <div class="section-card-body d-flex flex-column align-items-center">
                        <div style="height: 280px; width: 100%; position: relative;">
                            <canvas id="partyDonutChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Seat Reservation Ratio -->
            <div class="col-lg-6">
                <div class="section-card h-100 mb-0">
                    <div class="section-card-header">
                        <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-scale-balanced me-2 text-primary"></i> Reservation Composition (243 Seats)</h6>
                    </div>
                    <div class="section-card-body d-flex flex-column align-items-center">
                        <div style="height: 280px; width: 100%; position: relative;">
                            <canvas id="reservationChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Administrative Divisions Breakdown -->
        <div class="section-card">
            <div class="section-card-header">
                <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-map-location-dot me-2 text-success"></i> 9 Bihar Administrative Divisions Summary</h6>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Administrative Division</th>
                            <th>Total Districts</th>
                            <th>Total Assembly Constituencies</th>
                            <th>Approximate Electorate</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($division_counts as $div_name => $div_data): ?>
                            <tr>
                                <td><strong class="text-dark"><?php echo htmlspecialchars($div_name); ?> Division</strong></td>
                                <td><span class="badge bg-light text-dark border"><?php echo $div_data['districts']; ?> Districts</span></td>
                                <td><span class="badge bg-danger px-2 py-1"><?php echo $div_data['acs']; ?> ACs</span></td>
                                <td><span class="fw-semibold text-secondary"><?php echo number_format($div_data['electors']); ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </main>

    <?php include 'admin-footer.php'; ?>

<script>
// Party Donut
const partyCtx = document.getElementById('partyDonutChart');
if (partyCtx) {
    const pData = <?php echo json_encode($party_counts); ?>;
    new Chart(partyCtx, {
        type: 'doughnut',
        data: {
            labels: Object.keys(pData),
            datasets: [{
                data: Object.values(pData),
                backgroundColor: [
                    '#16a34a', '#ea580c', '#0284c7', '#0891b2', '#dc2626',
                    '#7c3aed', '#f59e0b', '#10b981', '#64748b'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}

// Reservation Pie
const resCtx = document.getElementById('reservationChart');
if (resCtx) {
    new Chart(resCtx, {
        type: 'pie',
        data: {
            labels: ['General (<?php echo $res_counts['GEN']; ?>)', 'SC Reserved (<?php echo $res_counts['SC']; ?>)', 'ST Reserved (<?php echo $res_counts['ST']; ?>)'],
            datasets: [{
                data: [<?php echo $res_counts['GEN']; ?>, <?php echo $res_counts['SC']; ?>, <?php echo $res_counts['ST']; ?>],
                backgroundColor: ['#3b82f6', '#06b6d4', '#f59e0b']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });
}
</script>
