<?php
require_once '../config/database.php';
require_once '../config/helpers.php';
require_once '../config/config.php';

session_start();

if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'login.php');
    exit;
}

// Admin only - not head_admin
if (getCurrentRole() === 'head_admin') {
    header('Location: ' . BASE_URL . 'admin/head-admin/call_out_status.php');
    exit;
}

if (getCurrentRole() !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$currentPath = $_SERVER['PHP_SELF'];
$navItems = [
    'reporting' => [
        'label' => '📊 REPORTING',
        'items' => [
            'backend-productivity.php' => ['icon' => 'trending_up', 'label' => 'Productivity'],
            'daily-count.php' => ['icon' => 'bar_chart', 'label' => 'Daily Count'],
            'monitoring.php' => ['icon' => 'monitor_heart', 'label' => 'Monitoring'],
        ]
    ],
    'reference' => [
        'label' => '📚 REFERENCE DATA',
        'items' => [
            'call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status'],
            'pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks'],
            'status_input.php' => ['icon' => 'input', 'label' => 'Status Input'],
            'sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category'],
            'main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks'],
        ]
    ],
    'account' => [
        'label' => '👤 ACCOUNT',
        'items' => [
            'profile.php' => ['icon' => 'account_circle', 'label' => 'Profile'],
            'logout.php' => ['icon' => 'exit_to_app', 'label' => 'Logout'],
        ]
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Out Status - Reference Data</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
    <style>
        .material-icons { font-family: 'Material Icons'; font-size: 24px; line-height: 1; -webkit-font-smoothing: antialiased; }
        .toast { position: fixed; top: 20px; right: 20px; padding: 16px 24px; border-radius: 8px; color: white; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2000; animation: slideIn 0.3s ease-in-out; }
        @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .table-row { @apply border-b hover:bg-blue-50 transition-colors; }
        .table-cell { @apply px-4 py-3 text-sm; }
        .nav-section { @apply mb-6; }
        .nav-section-title { @apply text-xs font-bold text-gray-500 uppercase tracking-wider px-4 py-2; }
        .nav-link { @apply flex items-center gap-3 px-4 py-3 rounded-lg transition-all text-sm; }
        .nav-link.active { @apply bg-blue-600 text-white; }
        .nav-link:not(.active) { @apply text-gray-300 hover:bg-gray-800; }
        .color-swatch { @apply w-8 h-8 rounded border-2 border-gray-300; }
        .badge-info { @apply inline-block px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800; }
    </style>
</head>
<body class="bg-gray-100">

<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleSidebar()">
    <span class="material-icons">menu</span>
</button>

<div class="flex min-h-screen">
    <aside class="sidebar fixed md:relative left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col overflow-y-auto z-40 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
        <div class="p-6 border-b border-gray-800">
            <a href="<?php echo BASE_URL; ?>dashboard.php" class="text-xl font-bold block">🏢 PARAGON</a>
            <p class="text-xs text-gray-400 mt-2">Admin - Read Only</p>
        </div>
        
        <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
            <?php foreach ($navItems as $section => $sectionData): ?>
                <div class="nav-section">
                    <div class="nav-section-title"><?php echo $sectionData['label']; ?></div>
                    <?php foreach ($sectionData['items'] as $file => $item): 
                        $fullPath = BASE_URL . 'admin/' . $file;
                        $isActive = strpos($currentPath, $file) !== false;
                    ?>
                        <a href="<?php echo $fullPath; ?>" class="nav-link <?php echo $isActive ? 'active' : ''; ?>">
                            <span class="material-icons"><?php echo $item['icon']; ?></span>
                            <span><?php echo $item['label']; ?></span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </nav>
        
        <div class="p-4 border-t border-gray-800">
            <div class="flex items-center gap-3 bg-gray-800 p-3 rounded-lg">
                <img src="<?php echo BASE_URL; ?>assets/avatar.png" class="w-10 h-10 rounded-full object-cover">
                <div class="text-xs">
                    <p class="font-semibold"><?php echo getCurrentUserName(); ?></p>
                    <p class="text-gray-400">Admin</p>
                </div>
            </div>
        </div>
    </aside>

    <div class="flex-1">
        <main class="h-screen overflow-auto">
            <!-- Header -->
            <div class="bg-white border-b sticky top-0 z-10">
                <div class="px-6 py-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900">Call Out Status Reference</h1>
                            <p class="text-sm text-gray-600 mt-1">View system status types (Read-only)</p>
                        </div>
                        <span class="badge-info">📖 Reference Data</span>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <input type="text" id="searchInput" placeholder="Search statuses..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Color</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Created</th>
                                </tr>
                            </thead>
                            <tbody id="statusTable" class="divide-y">
                                <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-4 flex justify-between items-center border-t bg-gray-50">
                        <span class="text-sm text-gray-600" id="totalCount">Total: 0</span>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 border rounded hover:bg-gray-100 text-sm" onclick="loadData(1)">« First</button>
                            <button class="px-3 py-1 border rounded hover:bg-gray-100 text-sm" id="prevBtn" onclick="loadData(currentPage - 1)">Prev</button>
                            <span class="text-sm text-gray-600" id="pageInfo">Page 1</span>
                            <button class="px-3 py-1 border rounded hover:bg-gray-100 text-sm" id="nextBtn" onclick="loadData(currentPage + 1)">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let currentPage = 1;

loadData(1);

function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('-translate-x-full');
}

document.getElementById('searchInput').addEventListener('input', debounce(() => {
    currentPage = 1;
    loadData(1);
}, 300));

function debounce(fn, delay) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(fn, delay);
    };
}

function loadData(page = 1) {
    const search = document.getElementById('searchInput').value;
    fetch(`${BASE_URL}api/fetch-call-out-status.php?page=${page}&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) { showToast('Error loading statuses', 'info'); return; }
            currentPage = page;
            
            const tbody = document.getElementById('statusTable');
            tbody.innerHTML = data.data.map(status => `
                <tr class="table-row">
                    <td class="table-cell font-mono text-gray-600">${status.id}</td>
                    <td class="table-cell font-semibold text-gray-900">${status.status_name}</td>
                    <td class="table-cell">
                        <div class="color-swatch" style="background-color: ${status.color_code};"></div>
                    </td>
                    <td class="table-cell text-gray-600">${status.description ? status.description.substring(0, 50) : '—'}</td>
                    <td class="table-cell text-gray-600">${new Date(status.created_at).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</td>
                </tr>
            `).join('');

            document.getElementById('totalCount').textContent = `Total: ${data.total}`;
            document.getElementById('pageInfo').textContent = `Page ${page}`;
            document.getElementById('prevBtn').disabled = page === 1;
            document.getElementById('nextBtn').disabled = page >= data.total_pages;
        })
        .catch(err => showToast('Failed to load statuses', 'info'));
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

</body>
</html>
