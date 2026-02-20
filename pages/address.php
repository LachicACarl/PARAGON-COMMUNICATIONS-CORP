<?php
/**
 * PARAGON COMMUNICATIONS - Address Management
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is logged in
requireLogin();

// Get user details
$userId = getCurrentUserId();
$userDetails = getUserDetails($pdo, $userId);
$firstInitial = strtoupper(substr($userDetails['first_name'] ?? '', 0, 1));
$lastInitial = strtoupper(substr($userDetails['last_name'] ?? '', 0, 1));
$initials = trim($firstInitial . $lastInitial);
if ($initials === '') {
    $initials = 'U';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Manage Address</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
  <style>
    .material-icons {
      font-family: 'Material Icons';
      font-weight: normal;
      font-style: normal;
      font-size: 24px;
      display: inline-block;
      line-height: 1;
      text-transform: none;
      letter-spacing: normal;
      word-wrap: normal;
      white-space: nowrap;
      direction: ltr;
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      -moz-osx-font-smoothing: grayscale;
      font-feature-settings: 'liga';
    }
    .summary-card {
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .summary-card:hover {
      transform: translateY(-3px);
    }
  </style>
</head>
<body class="bg-gray-50">
<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay fixed inset-0 bg-black/30 hidden z-40" onclick="closeMobileSidebar()"></div>
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <?php
  $currentPath = $_SERVER['PHP_SELF'];
  $userRole = getCurrentRole();

  // Navigation definition
  $allNavItems = [
    'dashboard.php' => [
      'icon' => 'dashboard',
      'label' => 'Dashboard',
      'roles' => ['head_admin', 'admin', 'manager']
    ],
    'user.php' => [
      'icon' => 'people',
      'label' => 'User',
      'roles' => ['head_admin']
    ],
    'address.php' => [
      'icon' => 'location_on',
      'label' => 'Address',
      'roles' => ['head_admin']
    ],
    'amountpaid.php' => [
      'icon' => 'checklist',
      'label' => 'Amount Paid',
      'roles' => ['head_admin']
    ],

    // ADMIN CONFIG PAGES
    'admin/installation-fee.php' => [
      'icon' => 'attach_money',
      'label' => 'Installation Fee',
      'roles' => ['head_admin']
    ],
    'admin/call_out_status.php' => [
      'icon' => 'call',
      'label' => 'Call Out Status',
      'roles' => ['head_admin']
    ],
    'admin/pull_out_remarks.php' => [
      'icon' => 'notes',
      'label' => 'Pull Out Remarks',
      'roles' => ['head_admin']
    ],
    'admin/status_input.php' => [
      'icon' => 'input',
      'label' => 'Status Input',
      'roles' => ['head_admin']
    ],
    'admin/sales_category.php' => [
      'icon' => 'category',
      'label' => 'Sales Category',
      'roles' => ['head_admin']
    ],
    'admin/main_remarks.php' => [
      'icon' => 'edit',
      'label' => 'Main Remarks',
      'roles' => ['head_admin']
    ],

    // ADMIN REPORTS
    'admin/monitoring.php' => [
      'icon' => 'monitor',
      'label' => 'Backend Monitoring',
      'roles' => ['admin']
    ],
    'admin/backend-productivity.php' => [
      'icon' => 'assessment',
      'label' => 'Backend Productivity',
      'roles' => ['admin']
    ],
    'admin/dormants.php' => [
      'icon' => 'person_off',
      'label' => 'Dormants',
      'roles' => ['admin']
    ],
    'admin/recallouts.php' => [
      'icon' => 'phone_callback',
      'label' => 'Recallouts',
      'roles' => ['admin']
    ],

    // ADMIN / MANAGER SHARED
    'admin/pull-out.php' => [
      'icon' => 'content_paste',
      'label' => 'Pull Out Report',
      'roles' => ['admin', 'manager']
    ],
    'admin/s25-report.php' => [
      'icon' => 'summarize',
      'label' => 'S25 Report',
      'roles' => ['admin', 'manager']
    ],
    'admin/daily-count.php' => [
      'icon' => 'today',
      'label' => 'Daily Count',
      'roles' => ['admin', 'manager']
    ],
    'admin/visit-remarks.php' => [
      'icon' => 'comment',
      'label' => 'Visit Remarks',
      'roles' => ['admin', 'manager']
    ],

    // COMMON
    'profile.php' => [
      'icon' => 'person',
      'label' => 'Profile',
      'roles' => ['head_admin', 'admin', 'manager']
    ],
    'logout.php' => [
      'icon' => 'logout',
      'label' => 'Logout',
      'roles' => ['head_admin', 'admin', 'manager']
    ],
  ];

  // Filter by role
  $navItems = array_filter($allNavItems, fn($item) =>
    in_array($userRole, $item['roles'])
  );
  ?>
  <aside class="sidebar fixed left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col p-6 overflow-y-auto md:relative md:w-64 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <div class="logo mb-8">
      <img src="<?php echo BASE_URL; ?>assets/image.png" alt="Paragon Logo" class="w-32 rounded-lg">
    </div>
    <nav class="nav flex-1 space-y-2">
      <?php foreach ($navItems as $file => $item): ?>
        <?php
          $fullPath = BASE_URL . $file;
          $isActive = strpos($currentPath, $file) !== false;
        ?>
        <a href="<?php echo $fullPath; ?>" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
          <span class="material-icons text-lg"><?php echo $item['icon']; ?></span>
          <span class="text-sm font-medium"><?php echo $item['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main flex-1 ml-0 md:ml-0 p-6 overflow-auto">
    <!-- PAGE HEADER -->
    <div class="header bg-white rounded-xl shadow-sm p-6 mb-8 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
      <div>
        <h1 class="m-0 text-2xl font-bold text-gray-900">Manage Address</h1>
        <p class="mt-1 text-sm text-gray-600">Overview of client's address hierarchy</p>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="card bg-white rounded-xl shadow-sm p-6">
      <!-- Summary Cards -->
      <div class="summary grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="summary-card bg-white rounded-lg p-4 cursor-pointer hover:shadow-lg" onclick="showLevel('region')">
        <span class="material-icons text-blue-600 text-4xl">public</span>
        <div>
          <h3 class="m-0 text-sm text-gray-600 font-semibold">REGION</h3>
          <p id="regionCount" class="m-0 mt-1 text-2xl font-bold text-gray-900">0</p>
        </div>
      </div>
      <div class="summary-card bg-white rounded-lg p-4 cursor-pointer hover:shadow-lg" onclick="showLevel('province')">
        <span class="material-icons text-blue-600 text-4xl">map</span>
        <div>
          <h3 class="m-0 text-sm text-gray-600 font-semibold">PROVINCE</h3>
          <p id="provinceCount" class="m-0 mt-1 text-2xl font-bold text-gray-900">0</p>
        </div>
      </div>
      <div class="summary-card bg-white rounded-lg p-4 cursor-pointer hover:shadow-lg" onclick="showLevel('municipality')">
        <span class="material-icons text-blue-600 text-4xl">location_city</span>
        <div>
          <h3 class="m-0 text-sm text-gray-600 font-semibold">MUNICIPALITY</h3>
          <p id="municipalityCount" class="m-0 mt-1 text-2xl font-bold text-gray-900">0</p>
        </div>
      </div>
    </div>

    <!-- Actions and Search -->
    <div class="actions flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
      <button id="manageBtn" class="bg-blue-600 text-white border-none px-4 py-2 rounded-lg cursor-pointer font-semibold flex items-center gap-2 hover:bg-blue-700 transition-colors" onclick="openModal()">
        <span class="material-icons">add</span>
        <span id="manageBtnText">ADD REGION</span>
      </button>
      <div class="search-box flex items-center gap-2 bg-gray-100 px-4 py-2 rounded-lg border border-gray-200">
        <input type="text" id="searchInput" class="bg-transparent border-none outline-none text-sm" placeholder="Search..." onkeyup="performSearch()">
      </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto mb-6">
      <table id="dynamicTable" class="w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
        <thead class="bg-blue-600 text-white">
          <tr id="tableHeader"></tr>
        </thead>
        <tbody id="tableBody">
          <tr><td colspan="5" class="py-6 text-center">Loading data...</td></tr>
        </tbody>
      </table>
    </div>

    <!-- Entries Info and Pagination -->
    <div class="entries-info flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
      <span id="entriesInfo" class="text-sm text-gray-600">Showing 0 of 0 entries</span>
      <div class="flex gap-3">
        <select id="filterSelect" class="px-3 py-2 border border-gray-300 rounded-lg" onchange="changeFilter()">
          <option value="">Select Region</option>
        </select>
        <select id="filterSelect2" class="px-3 py-2 border border-gray-300 rounded-lg" style="display:none;" onchange="changeFilter()">
          <option value="">Select Province</option>
        </select>
      </div>
    </div>
    </div>
  </main>
</div>

<!-- Modal -->
<div class="modal fixed inset-0 bg-black/50 hidden justify-center items-center z-50" id="addressModal" style="display: none;">
  <div class="modal-content bg-white p-6 rounded-lg w-full max-w-sm mx-4 relative">
    <button class="close-btn absolute top-4 right-4 bg-none border-none text-2xl text-gray-500 cursor-pointer" onclick="closeModal()">✕</button>
    <h2 id="modalTitle" class="mt-0 text-gray-900 font-bold text-lg">Add Region</h2>

    <div id="regionDiv">
      <label for="regionInput" class="block mt-3 font-semibold text-gray-700">Region Name</label>
      <input type="text" id="regionInput" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter region name">
    </div>

    <div id="provinceDiv" style="display:none">
      <label for="regionSelect" class="block mt-3 font-semibold text-gray-700">Region</label>
      <select id="regionSelect" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg"></select>
      <label for="provinceInput" class="block mt-3 font-semibold text-gray-700">Province Name</label>
      <input type="text" id="provinceInput" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter province name">
    </div>

    <div id="municipalityDiv" style="display:none">
      <label for="regionSelectMun" class="block mt-3 font-semibold text-gray-700">Region</label>
      <select id="regionSelectMun" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" onchange="updateProvincesMun()"></select>
      <label for="provinceSelectMun" class="block mt-3 font-semibold text-gray-700">Province</label>
      <select id="provinceSelectMun" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg"></select>
      <label for="municipalityInput" class="block mt-3 font-semibold text-gray-700">Municipality Name</label>
      <input type="text" id="municipalityInput" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter municipality name">
    </div>

    <button class="mt-4 w-full bg-blue-600 text-white border-none px-4 py-2 rounded-lg cursor-pointer font-bold hover:bg-blue-700 transition-colors" onclick="submitAddress()">Submit</button>
  </div>
</div>

<script>
let currentLevel = 'region';
let currentPage = 1;
let searchQuery = '';
let allCounts = {};

// Load data on page load
document.addEventListener('DOMContentLoaded', () => {
  loadCounts();
  loadData('region', 1);
});

async function loadCounts() {
  try {
    const response = await fetch('fetch-address.php?action=get&type=region&page=1');
    const result = await response.json();
    
    if (result.counts) {
      document.getElementById('regionCount').textContent = result.counts.regions || 0;
      document.getElementById('provinceCount').textContent = result.counts.provinces || 0;
      document.getElementById('municipalityCount').textContent = result.counts.municipalities || 0;
      allCounts = result.counts;
    }
  } catch (error) {
    console.error('Error loading counts:', error);
  }
}

async function loadData(type, page = 1) {
  currentLevel = type;
  currentPage = page;
  
  const tableBody = document.getElementById('tableBody');
  const manageBtnText = document.getElementById('manageBtnText');
  
  tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Loading data...</td></tr>';
  manageBtnText.textContent = 'ADD ' + type.toUpperCase();

  try {
    const response = await fetch(`fetch-address.php?action=get&type=${type}&page=${page}&search=${encodeURIComponent(searchQuery)}`);
    const result = await response.json();
    
    tableBody.innerHTML = '';
    
    if (!result.data || result.data.length === 0) {
      tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">No data found.</td></tr>';
      document.getElementById('entriesInfo').textContent = 'Showing 0 of 0 entries';
      renderPagination(result.pagination);
      return;
    }

    // Render table header and rows based on type
    renderTable(result.data, type);
    
    // Update entries info
    const showing = result.data.length;
    const total = result.pagination.total;
    document.getElementById('entriesInfo').textContent = `Showing ${(page - 1) * 10 + 1} to ${Math.min(page * 10, total)} of ${total} entries`;
    
    // Load regions for dropdown if needed
    if (type === 'province' || type === 'municipality') {
      loadRegionsForSelect();
    }
  } catch (error) {
    console.error('Error loading data:', error);
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align: center;">Error loading data.</td></tr>';
  }
}

function renderTable(data, type) {
  const tableHead = document.getElementById('tableHeader');
  const tableBody = document.getElementById('tableBody');
  
  tableBody.innerHTML = '';
  
  if (type === 'region') {
    tableHead.innerHTML = '<th>REGION</th><th style="width:100px;">ACTION</th>';
    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td><strong>${item.name}</strong></td>
        <td>
          <button class="btn-action edit" onclick="editItem(${item.id}, '${type}')" title="Edit"><span class="material-icons">edit</span></button>
          <button class="btn-action delete" onclick="deleteItem(${item.id}, '${type}')" title="Delete"><span class="material-icons">delete</span></button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  } else if (type === 'province') {
    tableHead.innerHTML = '<th>REGION</th><th>PROVINCE</th><th style="width:100px;">ACTION</th>';
    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.regionName}</td>
        <td><strong>${item.name}</strong></td>
        <td>
          <button class="btn-action edit" onclick="editItem(${item.id}, '${type}')" title="Edit"><span class="material-icons">edit</span></button>
          <button class="btn-action delete" onclick="deleteItem(${item.id}, '${type}')" title="Delete"><span class="material-icons">delete</span></button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  } else if (type === 'municipality') {
    tableHead.innerHTML = '<th>REGION</th><th>PROVINCE</th><th>MUNICIPALITY</th><th style="width:100px;">ACTION</th>';
    data.forEach(item => {
      const row = document.createElement('tr');
      row.innerHTML = `
        <td>${item.regionName}</td>
        <td>${item.provinceName}</td>
        <td><strong>${item.name}</strong></td>
        <td>
          <button class="btn-action edit" onclick="editItem(${item.id}, '${type}')" title="Edit"><span class="material-icons">edit</span></button>
          <button class="btn-action delete" onclick="deleteItem(${item.id}, '${type}')" title="Delete"><span class="material-icons">delete</span></button>
        </td>
      `;
      tableBody.appendChild(row);
    });
  }
}

async function loadRegionsForSelect() {
  try {
    const response = await fetch('fetch-address.php?action=regions');
    const result = await response.json();
    
    if (result.data) {
      const regionSelect = document.getElementById('regionSelect');
      const regionSelectMun = document.getElementById('regionSelectMun');
      const filterSelect = document.getElementById('filterSelect');
      
      regionSelect.innerHTML = '<option value="">Select Region</option>';
      regionSelectMun.innerHTML = '<option value="">Select Region</option>';
      filterSelect.innerHTML = '<option value="">Select Region</option>';
      
      result.data.forEach(region => {
        regionSelect.innerHTML += `<option value="${region.id}">${region.name}</option>`;
        regionSelectMun.innerHTML += `<option value="${region.id}">${region.name}</option>`;
        filterSelect.innerHTML += `<option value="${region.id}">${region.name}</option>`;
      });
    }
  } catch (error) {
    console.error('Error loading regions:', error);
  }
}

async function updateProvincesMun() {
  const regionId = document.getElementById('regionSelectMun').value;
  const provinceSelectMun = document.getElementById('provinceSelectMun');
  
  if (!regionId) {
    provinceSelectMun.innerHTML = '<option value="">Select Province</option>';
    return;
  }
  
  try {
    const response = await fetch(`fetch-address.php?action=provinces&regionId=${regionId}`);
    const result = await response.json();
    
    provinceSelectMun.innerHTML = '<option value="">Select Province</option>';
    if (result.data) {
      result.data.forEach(province => {
        provinceSelectMun.innerHTML += `<option value="${province.id}">${province.name}</option>`;
      });
    }
  } catch (error) {
    console.error('Error loading provinces:', error);
  }
}

function showLevel(level) {
  loadData(level, 1);
}

function openModal() {
  const regionDiv = document.getElementById('regionDiv');
  const provinceDiv = document.getElementById('provinceDiv');
  const municipalityDiv = document.getElementById('municipalityDiv');
  const modalTitle = document.getElementById('modalTitle');
  
  regionDiv.style.display = 'none';
  provinceDiv.style.display = 'none';
  municipalityDiv.style.display = 'none';
  
  if (currentLevel === 'region') {
    regionDiv.style.display = 'block';
    modalTitle.textContent = 'Add Region';
    document.getElementById('regionInput').value = '';
  } else if (currentLevel === 'province') {
    provinceDiv.style.display = 'block';
    modalTitle.textContent = 'Add Province';
    loadRegionsForSelect();
    document.getElementById('provinceInput').value = '';
  } else if (currentLevel === 'municipality') {
    municipalityDiv.style.display = 'block';
    modalTitle.textContent = 'Add Municipality';
    loadRegionsForSelect();
    document.getElementById('municipalityInput').value = '';
  }
  
  document.getElementById('addressModal').style.display = 'flex';
}

function closeModal() {
  document.getElementById('addressModal').style.display = 'none';
}

async function submitAddress() {
  const regionInput = document.getElementById('regionInput');
  const regionSelect = document.getElementById('regionSelect');
  const provinceInput = document.getElementById('provinceInput');
  const regionSelectMun = document.getElementById('regionSelectMun');
  const provinceSelectMun = document.getElementById('provinceSelectMun');
  const municipalityInput = document.getElementById('municipalityInput');
  
  let endpoint = 'add-address.php';
  let data = {};
  
  if (currentLevel === 'region') {
    const name = regionInput.value.trim();
    if (!name) { alert('Enter region name'); return; }
    data = { type: 'region', name };
  } else if (currentLevel === 'province') {
    const regionId = regionSelect.value;
    const name = provinceInput.value.trim();
    if (!regionId || !name) { alert('Select region and enter province name'); return; }
    data = { type: 'province', regionId, name };
  } else if (currentLevel === 'municipality') {
    const regionId = regionSelectMun.value;
    const provinceId = provinceSelectMun.value;
    const name = municipalityInput.value.trim();
    if (!regionId || !provinceId || !name) { alert('Fill all fields'); return; }
    data = { type: 'municipality', regionId, provinceId, name };
  }
  
  try {
    const response = await fetch(endpoint, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data)
    });
    const result = await response.json();
    
    if (result.success) {
      alert('Added successfully!');
      closeModal();
      loadCounts();
      loadData(currentLevel, 1);
    } else {
      alert('Error: ' + (result.error || 'Failed to add'));
    }
  } catch (error) {
    console.error('Error:', error);
    alert('Error adding item');
  }
}

function editItem(id, type) {
  alert('Edit functionality coming soon');
}

function deleteItem(id, type) {
  if (confirm('Are you sure you want to delete this item?')) {
    alert('Delete functionality coming soon');
  }
}

function performSearch() {
  searchQuery = document.getElementById('searchInput').value;
  loadData(currentLevel, 1);
}

function changeFilter() {
  performSearch();
}

window.onclick = (e) => {
  const modal = document.getElementById('addressModal');
  if (e.target === modal) closeModal();
}
</script>

<script>
  function toggleMobileSidebar() {
      const sidebar = document.querySelector('.sidebar');
      sidebar.classList.toggle('-translate-x-full');
  }
  
  function closeMobileSidebar() {
      const sidebar = document.querySelector('.sidebar');
      sidebar.classList.add('-translate-x-full');
  }
  
  document.querySelectorAll('.sidebar .nav a').forEach(link => {
      link.addEventListener('click', () => {
          if (window.innerWidth <= 768) closeMobileSidebar();
      });
  });
  
  window.addEventListener('resize', () => {
      if (window.innerWidth > 768) closeMobileSidebar();
  });
</script>
</body>
</html>
