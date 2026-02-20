<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

session_start();

// Check if user is logged in
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'auth/login.php');
    exit;
}

// Check if user is admin
if (getCurrentRole() !== 'admin') {
    header('Location: ' . BASE_URL . 'dashboard.php');
    exit;
}

$currentPath = $_SERVER['PHP_SELF'];

// Navigation items
$navItems = [
    'admin/backend-productivity.php' => ['icon' => 'trending_up', 'label' => 'Productivity'],
    'admin/daily-count.php' => ['icon' => 'bar_chart', 'label' => 'Daily Count'],
    'admin/monitoring.php' => ['icon' => 'monitor_heart', 'label' => 'Monitoring'],
    'admin/dormants.php' => ['icon' => 'person_off', 'label' => 'Dormants'],
    'admin/pull-out.php' => ['icon' => 'logout', 'label' => 'Pull Out'],
    'admin/recallouts.php' => ['icon' => 'phone_in_talk', 'label' => 'Recallouts'],
    'admin/s25-report.php' => ['icon' => 'assessment', 'label' => 'S25 Report'],
    'profile.php' => ['icon' => 'account_circle', 'label' => 'Profile'],
    'logout.php' => ['icon' => 'exit_to_app', 'label' => 'Logout'],
];
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

<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay fixed inset-0 bg-black/30 hidden z-40" onclick="closeMobileSidebar()"></div>

<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside class="sidebar fixed left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col p-6 overflow-y-auto md:relative z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <div class="logo mb-8">
      <img src="<?php echo BASE_URL; ?>assets/image.png" class="w-32 rounded-lg">
    </div>

    <nav class="nav flex-1 space-y-2">
      <?php foreach ($navItems as $file => $item): ?>
        <?php
          $fullPath = BASE_URL . $file;
          $isActive = strpos($currentPath, $file) !== false;
        ?>
        <a href="<?php echo $fullPath; ?>"
           class="flex items-center gap-3 px-4 py-3 rounded-lg transition-all
           <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
          <span class="material-icons"><?php echo $item['icon']; ?></span>
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
  document.querySelector('.sidebar').classList.toggle('-translate-x-full');
}
function closeMobileSidebar() {
  document.querySelector('.sidebar').classList.add('-translate-x-full');
}
</script>

</body>
</html>
