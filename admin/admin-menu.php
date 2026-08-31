<?php
$current_page = basename($_SERVER['PHP_SELF']);

// Quick counts for badges if available
$db_menu = getAdminDB();
$unread_contacts = 0;
$pending_ads = 0;
if ($db_menu) {
    $c_res = $db_menu->query("SELECT COUNT(*) as c FROM `be_contacts` WHERE `status` = 'NEW'");
    if ($c_res) $unread_contacts = $c_res->fetch_assoc()['c'] ?? 0;
    
    $a_res = $db_menu->query("SELECT COUNT(*) as c FROM `be_advertisements` WHERE `status` = 'PENDING'");
    if ($a_res) $pending_ads = $a_res->fetch_assoc()['c'] ?? 0;
}
?>
<aside class="sidebar" id="adminSidebar">
    <div class="sidebar-header">
        <a href="index.php" class="sidebar-brand d-flex align-items-center gap-2">
            <img src="../assets/image/logo.png" alt="Bihar Election" height="32" class="bg-white p-1 rounded-2">
            <span>Bihar Election</span>
        </a>
        <button class="sidebar-toggle-btn d-none d-lg-flex" id="sidebarCollapseBtn" title="Toggle Sidebar">
            <i class="fas fa-bars-staggered"></i>
        </button>
        <button class="btn-close btn-close-white d-lg-none" id="sidebarCloseBtn" onclick="toggleMobileSidebar()" aria-label="Close"></button>
    </div>
    
    <nav class="flex-grow-1">
        <div class="nav-section-title">Overview</div>
        <a href="index.php" class="nav-link-custom <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>" title="Dashboard Overview">
            <i class="fas fa-chart-line text-primary"></i> <span>Dashboard</span>
        </a>
        <a href="analytics.php" class="nav-link-custom <?php echo ($current_page == 'analytics.php') ? 'active' : ''; ?>" title="Election & Visitor Analytics">
            <i class="fas fa-chart-pie text-info"></i> <span>Analytics</span>
        </a>

        <div class="nav-section-title">Assembly & Candidates</div>
        <a href="candidates.php" class="nav-link-custom <?php echo in_array($current_page, ['candidates.php', 'edit-candidate.php', 'view-candidate.php']) ? 'active' : ''; ?>" title="Candidate Aspirants & Profiles">
            <i class="fas fa-user-tie text-warning"></i> <span>Candidates</span>
        </a>
        <a href="constituencies.php" class="nav-link-custom <?php echo in_array($current_page, ['constituencies.php', 'edit-constituency.php']) ? 'active' : ''; ?>" title="243 Assembly Constituencies">
            <i class="fas fa-landmark text-danger"></i> <span>Constituencies</span>
        </a>
        <a href="districts.php" class="nav-link-custom <?php echo in_array($current_page, ['districts.php', 'edit-district.php']) ? 'active' : ''; ?>" title="38 Bihar Districts">
            <i class="fas fa-map-location-dot text-success"></i> <span>38 Districts</span>
        </a>

        <div class="nav-section-title">Panchayat & Local Bodies</div>
        <a href="mukhiyas.php" class="nav-link-custom <?php echo ($current_page == 'mukhiyas.php') ? 'active' : ''; ?>" title="Gram Panchayat Mukhiyas">
            <i class="fas fa-users-gear text-info"></i> <span>Mukhiya Directory</span>
        </a>
        <a href="sarpanchs.php" class="nav-link-custom <?php echo ($current_page == 'sarpanchs.php') ? 'active' : ''; ?>" title="Gram Kutchery Sarpanchs">
            <i class="fas fa-scale-balanced text-warning"></i> <span>Sarpanch Directory</span>
        </a>
        <a href="zila-parishad.php" class="nav-link-custom <?php echo ($current_page == 'zila-parishad.php') ? 'active' : ''; ?>" title="Zila Parishad Members & Chiefs">
            <i class="fas fa-building-columns text-primary"></i> <span>Zila Parishad</span>
        </a>
        <a href="panchayats.php" class="nav-link-custom <?php echo ($current_page == 'panchayats.php') ? 'active' : ''; ?>" title="Panchayat Directory Overview">
            <i class="fas fa-tree-city text-success"></i> <span>Panchayats</span>
        </a>

        <div class="nav-section-title">Monetization & Leads</div>
        <a href="manage-ads.php" class="nav-link-custom <?php echo in_array($current_page, ['manage-ads.php', 'edit-ad.php']) ? 'active' : ''; ?>" title="Ad Banners & Campaigns">
            <i class="fas fa-rectangle-ad text-danger"></i> 
            <span>Advertisements</span>
            <?php if ($pending_ads > 0): ?>
                <span class="badge rounded-pill bg-warning text-dark"><?php echo $pending_ads; ?></span>
            <?php endif; ?>
        </a>
        <a href="whatsapp-subscribers.php" class="nav-link-custom <?php echo ($current_page == 'whatsapp-subscribers.php') ? 'active' : ''; ?>" title="WhatsApp Subscribers & Broadcasts">
            <i class="fab fa-whatsapp text-success"></i> <span>WhatsApp Alerts</span>
        </a>
        <a href="contacts.php" class="nav-link-custom <?php echo ($current_page == 'contacts.php') ? 'active' : ''; ?>" title="Enquiry Leads & Requests">
            <i class="fas fa-envelope-open-text text-info"></i> 
            <span>Leads & Enquiries</span>
            <?php if ($unread_contacts > 0): ?>
                <span class="badge rounded-pill bg-danger"><?php echo $unread_contacts; ?></span>
            <?php endif; ?>
        </a>

        <div class="nav-section-title">System & SEO</div>
        <a href="sitemap.php" class="nav-link-custom <?php echo ($current_page == 'sitemap.php') ? 'active' : ''; ?>" title="XML Sitemap & SEO Tools">
            <i class="fas fa-sitemap text-warning"></i> <span>Sitemap & SEO</span>
        </a>
        <a href="users.php" class="nav-link-custom <?php echo ($current_page == 'users.php') ? 'active' : ''; ?>" title="Admin Users & Roles">
            <i class="fas fa-user-shield text-secondary"></i> <span>Admin Users</span>
        </a>
        <a href="settings.php" class="nav-link-custom <?php echo ($current_page == 'settings.php') ? 'active' : ''; ?>" title="Site Configurations">
            <i class="fas fa-gear text-secondary"></i> <span>Site Settings</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <a href="logout.php" class="nav-link-custom text-danger" style="margin:0;">
            <i class="fas fa-power-off"></i> <span>Log Out</span>
        </a>
    </div>
</aside>
