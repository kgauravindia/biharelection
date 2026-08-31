    <div class="sidebar-backdrop" id="sidebarBackdrop" onclick="toggleMobileSidebar()"></div>
</div><!-- /.admin-layout -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Sidebar Desktop Collapse
const sidebarCollapseBtn = document.getElementById('sidebarCollapseBtn');
const adminLayout = document.querySelector('.admin-layout');

if (sidebarCollapseBtn && adminLayout) {
    // Check saved state
    if (localStorage.getItem('sidebar_collapsed') === 'true') {
        adminLayout.classList.add('sidebar-collapsed');
    }

    sidebarCollapseBtn.addEventListener('click', function() {
        adminLayout.classList.toggle('sidebar-collapsed');
        localStorage.setItem('sidebar_collapsed', adminLayout.classList.contains('sidebar-collapsed'));
    });
}

// Mobile Sidebar Drawer Toggle
function toggleMobileSidebar() {
    const sidebar = document.getElementById('adminSidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    if (sidebar && backdrop) {
        sidebar.classList.toggle('show');
        backdrop.classList.toggle('show');
    }
}
</script>
</body>
</html>
