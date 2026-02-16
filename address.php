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
<title>Address Management</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
/* Material Icons Font Family */
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
body { margin:0; font-family: Arial,sans-serif; background:#f4f6f9; }
.wrapper { display:flex; min-height:100vh; }
.content { margin-left:200px; padding:20px; width:100%; }
h1 { margin-bottom:20px; color:#333; }

.summary { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:15px; margin-bottom:20px; }
.summary-card { background:#fff; border-radius:8px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.08); display:flex; align-items:center; gap:15px; cursor:pointer; transition: transform 0.2s, box-shadow 0.2s; }
.summary-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.summary-card .icon { font-size:36px; color:#1976d2; }
.summary-card h3 { margin:0; font-size:14px; color:#666; font-weight: 600; }
.summary-card p { margin:4px 0 0; font-size:22px; font-weight:bold; color:#333; }

.actions { display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; gap:10px; }
.actions button { background:#1976d2; color:#fff; border:none; padding:10px 14px; border-radius:5px; cursor:pointer; font-size:14px; display:flex; align-items:center; gap:5px; }
.search-box { display:flex; align-items:center; gap:5px; }
.search-box input { padding:8px 12px; border-radius:5px; border:1px solid #ccc; width:250px; }

table { width:100%; border-collapse:collapse; background:#fff; box-shadow:0 2px 8px rgba(0,0,0,0.08); border-radius:8px; overflow:hidden; }
th,td { padding:12px; border-bottom:1px solid #ddd; text-align:left; }
thead { background:#1976d2; color:#fff; }
tr:hover { background:#f1f1f1; }

.entries-info { margin-top:15px; display:flex; justify-content:space-between; align-items:center; font-size:14px; color:#666; flex-wrap:wrap; gap:10px; }
.entries-info select { padding:6px 10px; border-radius:5px; border:1px solid #ccc; }

.btn-action { border:none; background:none; cursor:pointer; font-size:18px; padding:5px; }
.btn-action.edit { color:#1976d2; }
.btn-action.delete { color:#e53935; }

.modal { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); justify-content:center; align-items:center; z-index:1000; }
.modal-content { background:#fff; padding:25px; border-radius:8px; width:450px; max-width:90%; position:relative; }
.modal-content h2 { margin-top:0; color:#333; }
.modal-content label { display:block; margin-top:12px; font-weight:bold; color:#555; }
.modal-content select, .modal-content input { width:100%; padding:8px 10px; margin-top:4px; border-radius:5px; border:1px solid #ccc; box-sizing:border-box; }
.modal-content button { margin-top:15px; background:#1976d2; color:#fff; border:none; padding:10px 14px; border-radius:5px; cursor:pointer; width:100%; font-weight:bold; }
.close-btn { position:absolute; top:10px; right:10px; cursor:pointer; font-size:24px; color:#888; }
</style>
</head>
<body>
<button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
<div class="wrapper">
  <!-- Embedded Sidebar -->
  <?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  $userRole = getCurrentRole();
  
  // Define all navigation items with role restrictions
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
  
  // Filter navigation items based on user role
  $navItems = array_filter($allNavItems, function($item) use ($userRole) {
    return in_array($userRole, $item['roles']);
  });
  ?>
  <style>
    .sidebar {
      width: 200px;
      background: linear-gradient(135deg, #1565c0, #1976d2);
      color: #fff;
      padding: 20px;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
      z-index: 1000;
    }
    .sidebar .logo { margin-bottom: 30px; }
    .sidebar .logo img { max-width: 100%; height: auto; }
    .sidebar nav { display: flex; flex-direction: column; gap: 5px; }
    .sidebar nav a {
      display: flex; align-items: center; gap: 15px; padding: 12px 15px;
      color: rgba(255, 255, 255, 0.8); text-decoration: none; border-radius: 5px;
      transition: all 0.3s ease; font-size: 14px;
    }
    .sidebar nav a:hover { background: rgba(255, 255, 255, 0.15); color: #fff; padding-left: 18px; }
    .sidebar nav a.active { background: rgba(255, 255, 255, 0.25); color: #fff; font-weight: bold; border-right: 4px solid #fff; padding-right: 11px; }
    .sidebar nav a .material-icons { font-size: 20px; flex-shrink: 0; }
  </style>
  <aside class="sidebar">
    <div class="logo"><img src="assets/image.png" alt="Paragon Logo"></div>
    <nav class="nav">
      <?php foreach($navItems as $file => $item): ?>
        <a href="<?php echo $file; ?>" class="<?php echo $currentPage === $file ? 'active' : ''; ?>">
          <span class="material-icons"><?php echo $item['icon']; ?></span>
          <?php echo $item['label']; ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

<main class="content">
<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
  <div>
    <h1 style="margin: 0 0 5px 0; font-size: 26px; color: #333;">Address</h1>
    <p style="margin: 0; color: #666; font-size: 14px;">Overview of client's address.</p>
  </div>
  <div style="display: flex; align-items: center; gap: 12px; background: #fff; padding: 10px 16px; border-radius: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
    <div style="width: 40px; height: 40px; background: #1976d2; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
      <?php echo htmlspecialchars($initials); ?>
    </div>
    <div>
      <strong><?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?></strong><br>
      <small><?php echo ucfirst(str_replace('_', ' ', getCurrentRole())); ?></small>
    </div>
  </div>
</div>

<div class="summary">
  <div class="summary-card" onclick="showLevel('region')">
    <span class="material-icons icon">public</span><div><h3>REGION</h3><p id="regionCount">0</p></div>
  </div>
  <div class="summary-card" onclick="showLevel('province')">
    <span class="material-icons icon">map</span><div><h3>PROVINCE</h3><p id="provinceCount">0</p></div>
  </div>
  <div class="summary-card" onclick="showLevel('municipality')">
    <span class="material-icons icon">location_city</span><div><h3>MUNICIPALITY</h3><p id="municipalityCount">0</p></div>
  </div>
</div>

<div class="actions">
  <button id="manageBtn" onclick="openModal()"> <span class="material-icons">add</span> <span id="manageBtnText">ADD REGION</span> </button>
  <div class="search-box"><input type="text" id="searchInput" placeholder="Search..." onkeyup="performSearch()"></div>
</div>

<table id="dynamicTable">
<thead><tr id="tableHeader"></tr></thead>
<tbody id="tableBody"><tr><td colspan="5" style="text-align: center;">Loading data...</td></tr></tbody>
</table>

<div class="entries-info">
  <span id="entriesInfo">Showing 0 of 0 entries</span>
  <div style="display: flex; gap: 10px;">
    <select id="filterSelect" onchange="changeFilter()">
      <option value="">Select Region</option>
    </select>
    <select id="filterSelect2" onchange="changeFilter()" style="display:none;">
      <option value="">Select Province</option>
    </select>
  </div>
</div>
</main>
</div>

<!-- Modal -->
<div class="modal" id="addressModal">
<div class="modal-content">
  <span class="close-btn" onclick="closeModal()">&times;</span>
  <h2 id="modalTitle">Add Region</h2>

  <div id="regionDiv">
    <label for="regionInput">Region Name</label>
    <input type="text" id="regionInput" placeholder="Enter region name">
  </div>

  <div id="provinceDiv" style="display:none">
    <label for="regionSelect">Region</label>
    <select id="regionSelect"></select>
    <label for="provinceInput">Province Name</label>
    <input type="text" id="provinceInput" placeholder="Enter province name">
  </div>

  <div id="municipalityDiv" style="display:none">
    <label for="regionSelectMun">Region</label>
    <select id="regionSelectMun" onchange="updateProvincesMun()"></select>
    <label for="provinceSelectMun">Province</label>
    <select id="provinceSelectMun"></select>
    <label for="municipalityInput">Municipality Name</label>
    <input type="text" id="municipalityInput" placeholder="Enter municipality name">
  </div>

  <button onclick="submitAddress()">Submit</button>
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
      const overlay = document.querySelector('.mobile-overlay');
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
  }
  
  function closeMobileSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const overlay = document.querySelector('.mobile-overlay');
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
  }
  
  document.querySelectorAll('.sidebar .nav a').forEach(link => {
      link.addEventListener('click', () => {
          if (window.innerWidth <= 768) {
              closeMobileSidebar();
          }
      });
  });
  
  window.addEventListener('resize', () => {
      if (window.innerWidth > 768) {
          closeMobileSidebar();
      }
  });
</script>
</body>
</html>
