<?php
require_once __DIR__ . '/config.php';
$districts = DataProvider::getDistricts();

$pageTitle = 'Bihar Election WhatsApp Channel & Daily Political Bulletin (10,000+ Subscribers)';
$pageDescription = 'Subscribe to Bihar Election 3-Slot Daily WhatsApp Broadcast. Morning Top 5 Updates, Afternoon District Spotlight, and Evening Political Recap directly on your phone.';
$pageKeywords = 'Bihar election WhatsApp group, Bihar politics daily bulletin, Bihar election broadcast channel';
$pageCanonical = SITE_URL . '/whatsapp.php';
$activeNav = 'whatsapp';

require_once __DIR__ . '/header.php';
?>

    <!-- WhatsApp Hero -->
    <section class="hero-section py-4 py-lg-5">
        <div class="container text-center py-2">
            <span class="badge bg-success bg-opacity-25 text-success fw-bold px-3 py-2 mb-3">
                <i class="bi bi-whatsapp"></i> 10,000+ Active Subscribers
            </span>
            <h1 class="hero-title display-5 fw-extrabold mb-3">
                Bihar Election Daily <br>
                <span>WhatsApp Broadcast Network</span>
            </h1>
            <p class="hero-subtitle lead text-white-50 mx-auto mb-4" style="max-width: 800px;">
                Receive curated, unbiased political data, constituency intelligence, and delimitation digests directly to your WhatsApp inbox 3 times a day.
            </p>
            <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>" target="_blank" class="btn btn-success btn-lg fw-bold px-4 py-2 shadow-lg rounded-pill">
                <i class="bi bi-whatsapp"></i> 1-Click Join Official Channel
            </a>
        </div>
    </section>

    <!-- Main 3-Slot Format Details -->
    <main class="container my-4 my-lg-5">

        <div class="mb-4 pb-2 border-bottom">
            <h2 class="h4 fw-bold mb-1" style="color: var(--primary-navy);">Daily 3-Slot Broadcast Schedule</h2>
            <p class="small text-muted mb-0">Time-sensitive, concise, and verifiable political intelligence delivered across the day</p>
        </div>

        <div class="row g-4 mb-5">
            
            <!-- Morning Slot -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 border-top border-4 border-warning bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-warning fw-bold text-uppercase">Slot 1: Morning 8:00 AM</span>
                        <span class="badge bg-warning bg-opacity-25 text-dark fw-bold">Morning Digest</span>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--primary-navy);">Bihar Election Today: 5 Big Updates</h3>
                    <p class="small text-muted mb-3">
                        Key developments, Election Commission notifications, and political alliance movements to start your morning.
                    </p>
                    <div class="bg-light p-2 rounded-2 small text-muted">
                        📌 <em>Sample: "1. 38 Districts Panchayat Delimitation audit... 2. Bankipur civic works... 3. Saran political matrix..."</em>
                    </div>
                </div>
            </div>

            <!-- Afternoon Slot -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 border-top border-4 border-success bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-success fw-bold text-uppercase">Slot 2: Afternoon 2:00 PM</span>
                        <span class="badge bg-success bg-opacity-25 text-success fw-bold">District Focus</span>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--primary-navy);">District Election Spotlight</h3>
                    <p class="small text-muted mb-3">
                        In-depth daily focus on 1 specific district: local issues, MLA scorecards, voter demographics, and Panchayat status.
                    </p>
                    <div class="bg-light p-2 rounded-2 small text-muted">
                        📌 <em>Sample: "Today's Spotlight: Saran (Chhapra) — 10 Assembly seats, 2.6M voters, and Marhaura industrial analysis..."</em>
                    </div>
                </div>
            </div>

            <!-- Evening Slot -->
            <div class="col-12 col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 h-100 border-top border-4 border-primary bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-primary fw-bold text-uppercase">Slot 3: Evening 8:00 PM</span>
                        <span class="badge bg-primary bg-opacity-25 text-primary fw-bold">Evening Recap</span>
                    </div>
                    <h3 class="h5 fw-bold mb-2" style="color: var(--primary-navy);">Bihar Politics: 10 Key Highlights</h3>
                    <p class="small text-muted mb-3">
                        A quick 10-point analytical summary of rallies, political statements, candidate filings, and campaign strategies.
                    </p>
                    <div class="bg-light p-2 rounded-2 small text-muted">
                        📌 <em>Sample: "10-point quick summary of today's political momentum across Bihar..."</em>
                    </div>
                </div>
            </div>

        </div>

        <!-- 38 District WhatsApp Channels Grid -->
        <section class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
            <h2 class="h5 fw-bold mb-3 pb-2 border-bottom" style="color: var(--primary-navy);">
                📍 Join Your District's Dedicated WhatsApp Channel (38 Districts)
            </h2>
            <div class="row g-2 g-md-3">
                <?php foreach ($districts as $d): ?>
                    <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                        <a href="<?php echo WHATSAPP_CHANNEL_URL; ?>?district=<?php echo $d['slug']; ?>" target="_blank" class="btn btn-light btn-sm w-100 text-start d-flex justify-content-between align-items-center py-2 px-3 fw-semibold">
                            <span class="text-truncate"><?php echo htmlspecialchars($d['name']); ?></span>
                            <i class="bi bi-whatsapp text-success"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

<?php require_once __DIR__ . '/footer.php'; ?>
