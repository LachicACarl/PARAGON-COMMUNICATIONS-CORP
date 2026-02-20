<?php
/**
 * PARAGON COMMUNICATIONS - Main Dashboard
 * Role-based dashboard with different views for each user role
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is logged in
requireLogin();

// Get user info
$userId = getCurrentUserId();
$userRole = getCurrentRole();
$username = getCurrentUserName();
$userEmail = getCurrentUserEmail();

// Get user details from database
$userDetails = getUserDetails($pdo, $userId);

$firstInitial = strtoupper(substr($userDetails['first_name'] ?? '', 0, 1));
$lastInitial = strtoupper(substr($userDetails['last_name'] ?? '', 0, 1));
$initials = trim($firstInitial . $lastInitial);
if ($initials === '') {
    $initials = strtoupper(substr($username ?: 'U', 0, 1));
}

// Navigation Setup
$currentPath = $_SERVER['PHP_SELF'];
$allNavItems = [
  'dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['head_admin', 'admin', 'manager']],
  'user.php' => ['icon' => 'people', 'label' => 'User', 'roles' => ['head_admin']],
  'address.php' => ['icon' => 'location_on', 'label' => 'Address', 'roles' => ['head_admin']],
  'amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid', 'roles' => ['head_admin']],
  'admin/installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee', 'roles' => ['head_admin']],
  'admin/call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status', 'roles' => ['head_admin']],
  'admin/pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks', 'roles' => ['head_admin']],
  'admin/status_input.php' => ['icon' => 'input', 'label' => 'Status Input', 'roles' => ['head_admin']],
  'admin/sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category', 'roles' => ['head_admin']],
  'admin/main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks', 'roles' => ['head_admin']],
  'admin/monitoring.php' => ['icon' => 'monitor', 'label' => 'Backend Monitoring', 'roles' => ['admin']],
  'admin/backend-productivity.php' => ['icon' => 'assessment', 'label' => 'Backend Productivity', 'roles' => ['admin']],
  'admin/dormants.php' => ['icon' => 'person_off', 'label' => 'Dormants', 'roles' => ['admin']],
  'admin/recallouts.php' => ['icon' => 'phone_callback', 'label' => 'Recallouts', 'roles' => ['admin']],
  'admin/pull-out.php' => ['icon' => 'content_paste', 'label' => 'Pull Out Report', 'roles' => ['admin', 'manager']],
  'admin/s25-report.php' => ['icon' => 'summarize', 'label' => 'S25 Report', 'roles' => ['admin', 'manager']],
  'admin/daily-count.php' => ['icon' => 'today', 'label' => 'Daily Count', 'roles' => ['admin', 'manager']],
  'admin/visit-remarks.php' => ['icon' => 'comment', 'label' => 'Visit Remarks', 'roles' => ['admin', 'manager']],
  'profile.php' => ['icon' => 'person', 'label' => 'Profile', 'roles' => ['head_admin', 'admin', 'manager']],
  'logout.php' => ['icon' => 'logout', 'label' => 'Logout', 'roles' => ['head_admin', 'admin', 'manager']],
];

// Filter by role
$navItems = array_filter($allNavItems, fn($item) => in_array($userRole, $item['roles']));

// Get statistics based on role
$stats = [];
$pendingApprovals = [];

if (isHeadAdmin()) {
    // Head Admin gets system-wide statistics
    $totalUsersResult = getRow($pdo, "SELECT COUNT(*) as count FROM users");
    $stats['total_users'] = $totalUsersResult['count'];
    
    $totalClientsResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts");
    $stats['total_clients'] = $totalClientsResult['count'];
    
    $totalAmountResult = getRow($pdo, "SELECT SUM(amount_paid) as total FROM client_accounts");
    $stats['total_amount_paid'] = $totalAmountResult['total'] ?? 0;
    
    $dormantResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'dormant'");
    $stats['dormant_accounts'] = $dormantResult['count'];
}
elseif (isAdmin()) {
    // Admin gets their managed clients statistics
    $clientsResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE created_by = ? OR managed_by = ?", [$userId, $userId]);
    $stats['total_clients'] = $clientsResult['count'];
    
    $amountResult = getRow($pdo, "SELECT SUM(amount_paid) as total FROM client_accounts WHERE created_by = ? OR managed_by = ?", [$userId, $userId]);
    $stats['total_amount_paid'] = $amountResult['total'] ?? 0;
}
elseif (isManager()) {
    // Manager gets their assigned clients statistics
    $clientsResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE managed_by = ?", [$userId]);
    $stats['total_clients'] = $clientsResult['count'];
    
    $amountResult = getRow($pdo, "SELECT SUM(amount_paid) as total FROM client_accounts WHERE managed_by = ?", [$userId]);
    $stats['total_amount_paid'] = $amountResult['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Backend Management Monitoring</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    .material-icons {
      font-family: 'Material Icons';
      font-size: 24px;
      line-height: 1;
      -webkit-font-smoothing: antialiased;
    }
  </style>
</head>

<body class="bg-gray-50">

<!-- Mobile Menu Toggle -->
<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2.5 bg-gradient-to-br from-slate-900 to-slate-700 text-white rounded-lg hover:from-slate-800 hover:to-slate-600 hover:shadow-lg hover:shadow-blue-500/20 transition-all border border-slate-700 hover:border-blue-500/30" onclick="toggleMobileSidebar()" title="Toggle Navigation">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay fixed inset-0 bg-black/40 hidden z-40 transition-opacity duration-200" onclick="closeMobileSidebar()"></div>

<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside class="sidebar fixed md:relative left-0 top-0 w-64 h-screen bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white flex flex-col p-0 overflow-y-auto z-50 md:z-auto border-r border-slate-700/50 shadow-2xl md:translate-x-0 transition-transform duration-300">
    <!-- Logo Header -->
    <div class="flex-shrink-0 border-b-2 border-blue-500/20 bg-gradient-to-r from-slate-800/50 to-transparent px-6 py-5 hover:bg-gradient-to-r hover:from-slate-700/70 hover:to-transparent transition-all">
      <a href="<?php echo BASE_URL; ?>dashboard.php" class="flex items-center gap-3 group">
        <img src="<?php echo BASE_URL; ?>assets/image.png" class="w-12 h-12 rounded-lg transition-transform group-hover:scale-105" alt="Paragon Logo">
        <div class="flex flex-col">
          <span class="font-bold tracking-wide text-white text-sm">PARAGON</span>
          <span class="text-xs text-blue-400 font-medium">COMMUNICATIONS CORP</span>
        </div>
      </a>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 py-4 px-3 space-y-1">
      <?php foreach ($navItems as $file => $item): ?>
        <?php
          $fullPath = BASE_URL . $file;
          $isActive = strpos($currentPath, $file) !== false;
        ?>
        <a href="<?php echo $fullPath; ?>"
           class="flex items-center gap-3 px-4 py-3.5 rounded-lg transition-all duration-200 <?php echo $isActive ? 'bg-gradient-to-r from-blue-500/30 to-blue-500/10 border-l-4 border-blue-400 font-semibold text-white' : 'text-slate-300 hover:text-white border-l-4 border-transparent hover:bg-slate-700/40 hover:border-blue-500/50'; ?>"
           title="<?php echo $item['label']; ?>">
          <span class="material-icons flex-shrink-0 text-xl transition-all duration-200 <?php echo $isActive ? 'text-blue-400' : 'text-slate-400 group-hover:text-blue-400'; ?>"><?php echo $item['icon']; ?></span>
          <span class="text-sm font-medium"><?php echo $item['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- MAIN -->
  <main class="flex-1 p-6 bg-gray-100 overflow-auto">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">
        Paragon Backend Management Monitoring
      </h1>

      <!-- PROFILE -->
      <div class="flex items-center gap-3 bg-white px-4 py-2 rounded-xl shadow">
        <img src="<?php echo BASE_URL; ?>assets/avatar.png"
             class="w-10 h-10 rounded-full object-cover">
        <div class="text-sm">
          <p class="font-semibold text-gray-700">Admin User</p>
          <p class="text-gray-500 text-xs">Administrator</p>
        </div>
        <span class="material-icons text-gray-400 cursor-pointer">expand_more</span>
      </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- LEFT -->
      <div class="lg:col-span-2">

        <!-- SYSTEM CARDS -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
          <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500">Admins</p>
            <h2 id="adminCount" class="text-3xl font-bold text-blue-600">0</h2>
          </div>

          <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500">Managers</p>
            <h2 id="managerCount" class="text-3xl font-bold text-indigo-600">0</h2>
          </div>

          <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500">Client Status Types</p>
            <h2 id="statusCount" class="text-3xl font-bold text-purple-600">0</h2>
          </div>

          <div class="bg-white rounded-xl shadow p-5">
            <p class="text-gray-500">Municipalities</p>
            <h2 id="municipalityCount" class="text-3xl font-bold text-teal-600">0</h2>
          </div>
        </div>

        <!-- CHART -->
        <div class="bg-white rounded-xl shadow p-6">

          <!-- CHART HEADER WITH DATE -->
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-gray-700">
              Client Status Overview
            </h3>

            <input
              type="date"
              id="chartDate"
              class="border rounded-lg px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
          </div>

          <div class="h-80">
            <canvas id="statusChart"></canvas>
          </div>
        </div>

      </div>

      <!-- RIGHT -->
      <div class="space-y-6">
        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-gray-500">Active Clients</p>
          <h2 id="activeClients" class="text-4xl font-bold text-green-600 mt-2">0</h2>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <p class="text-gray-500">Dormant Clients</p>
          <h2 id="dormantClients" class="text-4xl font-bold text-red-600 mt-2">0</h2>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
let chartInstance = null;
const dateInput = document.getElementById('chartDate');

// Default date = today
dateInput.value = new Date().toISOString().split('T')[0];

function loadChartData(date = '') {
  fetch(`fetch.php?date=${date}`)
    .then(res => res.json())
    .then(data => {

      document.getElementById('activeClients').textContent = data.active;
      document.getElementById('dormantClients').textContent = data.dormant;
      document.getElementById('adminCount').textContent = data.admins;
      document.getElementById('managerCount').textContent = data.managers;
      document.getElementById('statusCount').textContent = data.status_count;
      document.getElementById('municipalityCount').textContent = data.municipalities;

      const ctx = document.getElementById('statusChart');
      if (chartInstance) chartInstance.destroy();

      chartInstance = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{
            data: data.data,
            backgroundColor: [
              '#3b82f6','#f97316','#eab308',
              '#22c55e','#ef4444','#6b7280'
            ],
            borderRadius: 8
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: { legend: { display: false } },
          scales: {
            y: { beginAtZero: true, ticks: { precision: 0 } }
          }
        }
      });
    });
}

// Initial load
loadChartData(dateInput.value);

// Reload on date change
dateInput.addEventListener('change', () => {
  loadChartData(dateInput.value);
});

function toggleMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.mobile-overlay');
  sidebar.classList.toggle('active');
  overlay.classList.toggle('hidden');
}

function closeMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.mobile-overlay');
  sidebar.classList.remove('active');
  overlay.classList.add('hidden');
}

document.addEventListener('keydown', function(event) {
  if (event.key === 'Escape') {
    closeMobileSidebar();
  }
});
</script>

</body>
</html>
