<?php
if (!isset($currentPath)) {
    $currentPath = $_SERVER['PHP_SELF'];
}

$headAdminNavItems = [
    'management' => [
        'label' => 'MANAGEMENT',
        'items' => [
            'pages/user.php' => ['icon' => 'people', 'label' => 'User'],
            'pages/address.php' => ['icon' => 'location_on', 'label' => 'Address'],
            'pages/amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid'],
            'admin/head-admin/installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee'],
            'admin/head-admin/call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status'],
            'admin/head-admin/pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks'],
            'admin/head-admin/status_input.php' => ['icon' => 'input', 'label' => 'Status Input'],
            'admin/head-admin/sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category'],
            'admin/head-admin/main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks'],
            'pages/profile.php' => ['icon' => 'person', 'label' => 'Profile'],
        ]
    ]
];
?>

<!-- Mobile Menu Toggle Button -->
<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2.5 bg-gradient-to-br from-slate-900 to-slate-700 text-white rounded-lg hover:from-slate-800 hover:to-slate-600 hover:shadow-lg hover:shadow-blue-500/20 transition-all border border-slate-700 hover:border-blue-500/30" id="sidebarToggle" onclick="toggleSidebar()" title="Toggle Navigation">
    <span class="material-icons">menu</span>
</button>

<!-- Mobile Overlay -->
<div class="mobile-overlay fixed inset-0 bg-black/40 hidden z-40 transition-opacity duration-200" id="mobileOverlay" onclick="closeSidebar()"></div>

<!-- Sidebar Navigation -->
<aside class="sidebar fixed md:relative left-0 top-0 w-64 h-screen bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col p-0 overflow-y-auto z-50 md:z-auto border-r border-slate-700/50 shadow-2xl">
    <!-- Sidebar Header -->
    <div class="flex-shrink-0 border-b-2 border-blue-500/20 bg-gradient-to-r from-slate-800/50 to-transparent px-6 py-5 hover:bg-gradient-to-r hover:from-slate-700/70 hover:to-transparent transition-all">
        <a href="<?php echo BASE_URL; ?>pages/dashboard.php" class="flex items-center gap-3 text-xl font-bold text-white hover:text-blue-400 transition-colors duration-200">
            <span class="material-icons text-2xl">dashboard</span>
            <span class="hidden sm:inline font-bold tracking-wide">PARAGON</span>
        </a>
        <p class="text-xs text-slate-400 mt-2 pl-10 hidden sm:block">Head Admin Dashboard</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 py-4 space-y-0 px-2">
        <div class="text-xs uppercase font-black text-slate-400 tracking-widest px-4 py-3">
            Management
        </div>
        <div class="flex flex-col space-y-1">
            <?php foreach ($headAdminNavItems['management']['items'] as $file => $item): 
                $fullPath = BASE_URL . $file;
                $isActive = strpos($currentPath, basename($file)) !== false;
            ?>
                <a href="<?php echo $fullPath; ?>" 
                   class="flex items-center gap-3 px-4 py-3 mx-1 rounded-lg transition-all duration-200 relative overflow-hidden group <?php echo $isActive ? 'active bg-gradient-to-r from-blue-500/30 to-blue-500/10 border-l-4 border-blue-400 font-bold text-white' : 'text-slate-300 hover:text-white border-l-4 border-transparent hover:bg-slate-700/40 hover:border-blue-500'; ?>"
                   onclick="window.innerWidth < 768 && closeSidebar()"
                   title="<?php echo $item['label']; ?>">
                    <span class="material-icons flex-shrink-0 transition-all duration-200 <?php echo $isActive ? 'text-blue-400 scale-110' : 'text-slate-400 group-hover:text-blue-400 group-hover:scale-115'; ?>"><?php echo $item['icon']; ?></span>
                    <span class="text-sm font-medium flex-1"><?php echo $item['label']; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </nav>

    <!-- User Profile Card -->
    <div class="flex-shrink-0 border-t border-slate-700/50 bg-gradient-to-r from-slate-800 to-slate-900 p-4">
        <div class="flex items-center gap-3 bg-gradient-to-r from-slate-700/40 to-slate-800/40 hover:from-slate-700/60 hover:to-slate-800/60 p-3 rounded-lg transition-all duration-200 border border-slate-600/30 hover:border-blue-500/30 cursor-pointer group">
            <img src="<?php echo BASE_URL; ?>assets/avatar.png" class="w-10 h-10 rounded-full object-cover flex-shrink-0 ring-2 ring-blue-500/20 group-hover:ring-blue-400/40 transition-all" alt="Avatar">
            <div class="text-xs flex-1 min-w-0">
                <p class="font-bold text-white truncate"><?php echo htmlspecialchars(getCurrentUserName()); ?></p>
                <p class="text-slate-400 text-xs">Head Admin</p>
            </div>
            <span class="material-icons text-slate-400 text-sm group-hover:text-blue-400 transition-colors">chevron_right</span>
        </div>
    </div>
</aside>

<script>
function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobileOverlay');
    sidebar.classList.toggle('active');
    overlay.classList.toggle('active');
}

function closeSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.getElementById('mobileOverlay');
    sidebar.classList.remove('active');
    overlay.classList.remove('active');
}

// Close sidebar when pressing Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeSidebar();
    }
});
</script>
