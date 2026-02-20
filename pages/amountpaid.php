<?php
/**
 * PARAGON COMMUNICATIONS - Amount Paid Management
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
  <title>Manage Amount Paid</title>
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
        <h1 class="m-0 text-2xl font-bold text-gray-900">Manage Amount Paid</h1>
        <p class="mt-1 text-sm text-gray-600">Overview of amount to be paid per plan</p>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="card bg-white rounded-xl shadow-sm p-6">
      <!-- Actions & Search -->
      <div class="actions flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 bg-blue-600 text-white -m-6 mb-6 px-6 py-4 rounded-t-xl">
        <h3 class="m-0 text-lg font-bold">MANAGE AMOUNT PAID</h3>
        <button class="bg-white text-blue-600 border-none px-4 py-2 rounded-full cursor-pointer font-bold flex items-center gap-2 hover:bg-gray-100 transition-colors" onclick="openModal()">
          <span class="material-icons text-base">add</span> ADD PLAN
        </button>
      </div>

      <div class="bg-white px-6 py-4 border-b border-gray-200 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div class="flex items-center gap-3">
          <label class="text-sm font-semibold text-gray-700">Show</label>
          <select class="px-3 py-2 border border-gray-300 rounded text-sm">
            <option>5</option>
            <option>10</option>
            <option>25</option>
            <option>50</option>
          </select>
          <label class="text-sm font-semibold text-gray-700">Entries</label>
        </div>
        <div class="search-box flex items-center gap-2 bg-gray-100 px-4 py-2 rounded-lg border border-gray-200">
          <input type="text" id="searchInput" class="bg-transparent border-none outline-none text-sm" placeholder="Search:" onkeyup="performSearch()">
        </div>
      </div>

      <!-- Amount Paid Table -->
      <div class="overflow-x-auto">
        <table id="paymentTable" class="w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
          <thead class="bg-blue-600 text-white">
            <tr>
              <th class="px-4 py-3 text-left">AMOUNT PAID</th>
              <th style="width: 100px;" class="px-4 py-3 text-left">ACTION</th>
            </tr>
          </thead>
          <tbody id="tableBody">
            <tr><td colspan="2" class="py-6 text-center">Loading data...</td></tr>
          </tbody>
        </table>
      </div>

      <div class="entries-info flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mt-6 text-sm text-gray-600">
        <span id="entriesInfo">Showing 0 of 0 entries</span>
        <div class="pagination flex gap-2" id="pagination"></div>
      </div>
    </div>
  </main>
</div>

<!-- Modal -->
<div class="modal fixed inset-0 bg-black/50 hidden justify-center items-center z-50" id="addPaymentModal" style="display: none;">
  <div class="modal-content bg-white p-6 rounded-lg w-full max-w-sm mx-4 relative">
    <button class="close-btn absolute top-4 right-4 bg-none border-none text-2xl text-gray-500 cursor-pointer" onclick="closeModal()">✕</button>
    <h3 class="mt-0 text-gray-900 font-bold">Add Payment Plan</h3>
    <label class="block mt-3 font-semibold text-gray-700">Client Name</label>
    <input type="text" id="clientName" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter client name">
    <label class="block mt-3 font-semibold text-gray-700">Amount to Pay</label>
    <input type="number" id="amountPaid" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter amount" step="0.01" min="0">
    <label class="block mt-3 font-semibold text-gray-700">Installation Fee</label>
    <input type="number" id="installationFee" class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-lg" placeholder="Enter installation fee" step="0.01" min="0">
    <button class="mt-4 w-full bg-blue-600 text-white border-none px-4 py-2 rounded-lg cursor-pointer font-bold hover:bg-blue-700 transition-colors" onclick="submitPayment()">Submit Payment</button>
  </div>
</div>

<script>
  let currentPage = 1;
  let totalPages = 1;
  let allData = [];
  let filteredData = [];

  // Initialize page on load
  document.addEventListener('DOMContentLoaded', function() {
    loadData(1);
  });

  // Load payment data from API
  function loadData(page = 1) {
    const searchQuery = document.getElementById('searchInput').value;
    const url = new URL('api/fetch-amount-paid.php', window.location.origin);
    url.searchParams.append('action', 'get');
    url.searchParams.append('page', page);
    if(searchQuery) url.searchParams.append('search', searchQuery);

    fetch(url)
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          allData = data.data;
          totalPages = data.total_pages;
          currentPage = page;
          renderTable(data.data);
          setupPagination(data.total_pages, page);
          updateEntriesInfo(data.total_count, data.data.length);
        }
      })
      .catch(error => console.error('Error loading data:', error));
  }

  // Render table rows from data
  function renderTable(data) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    if(data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2" style="text-align: center; padding: 20px;">No records found</td></tr>';
      return;
    }

    data.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>
          <div style="margin-bottom: 5px;"><strong>${item.client_name}</strong></div>
          <div style="font-size: 12px; color: #666;">
            Amount: ₱${parseFloat(item.amount_paid).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})} | 
            Fee: ₱${parseFloat(item.installation_fee).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})} | 
            Status: <span style="background: ${item.call_out_status === 'Active' ? '#4caf50' : '#f44336'}; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px;">${item.call_out_status}</span>
          </div>
        </td>
        <td>
          <button title="Edit" onclick="editPayment(${item.id})" style="background: none; border: none; cursor: pointer; color: #1976d2; margin-right: 10px;">
            <span class="material-icons" style="font-size: 18px;">edit</span>
          </button>
          <button title="Delete" onclick="deletePayment(${item.id})" style="background: none; border: none; cursor: pointer; color: #f44336;">
            <span class="material-icons" style="font-size: 18px;">delete</span>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Setup pagination buttons
  function setupPagination(totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if(totalPages <= 1) return;

    // Previous button
    if(currentPage > 1) {
      const prevBtn = createPageButton('‹', currentPage - 1);
      pagination.appendChild(prevBtn);
    }

    // Page buttons
    for(let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
      const btn = createPageButton(i, i);
      if(i === currentPage) btn.classList.add('active');
      pagination.appendChild(btn);
    }

    // Next button
    if(currentPage < totalPages) {
      const nextBtn = createPageButton('›', currentPage + 1);
      pagination.appendChild(nextBtn);
    }
  }

  function createPageButton(text, page) {
    const btn = document.createElement('button');
    btn.textContent = text;
    btn.onclick = () => loadData(page);
    btn.style.margin = '0 2px';
    btn.style.padding = '6px 10px';
    btn.style.border = '1px solid #ddd';
    btn.style.borderRadius = '4px';
    btn.style.cursor = 'pointer';
    btn.style.background = '#fff';
    if(page === currentPage) {
      btn.style.background = '#1976d2';
      btn.style.color = '#fff';
      btn.style.borderColor = '#1976d2';
    }
    return btn;
  }

  // Perform search
  function performSearch() {
    loadData(1);
  }

  // Update entries info
  function updateEntriesInfo(total, showing) {
    const start = (currentPage - 1) * 10 + 1;
    const end = Math.min(currentPage * 10, total);
    document.getElementById('entriesInfo').textContent = `Showing ${start} to ${end} of ${total} entries`;
  }

  // Modal functions
  function openModal() {
    document.getElementById('addPaymentModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('addPaymentModal').style.display = 'none';
    document.getElementById('clientName').value = '';
    document.getElementById('amountPaid').value = '';
    document.getElementById('installationFee').value = '';
  }

  // Submit payment
  function submitPayment() {
    const clientName = document.getElementById('clientName').value;
    const amountPaid = document.getElementById('amountPaid').value;
    const installationFee = document.getElementById('installationFee').value;

    if(!clientName || !amountPaid) {
      alert('Please fill in all required fields');
      return;
    }

    fetch('api/add-amount-paid.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        client_name: clientName,
        amount_paid: parseFloat(amountPaid),
        installation_fee: parseFloat(installationFee) || 0
      })
    })
    .then(response => response.json())
    .then(data => {
      if(data.success) {
        alert('Payment added successfully');
        closeModal();
        loadData(1);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error adding payment');
    });
  }

  // Edit payment (placeholder)
  function editPayment(id) {
    alert('Edit functionality coming soon for payment ID: ' + id);
  }

  // Delete payment (placeholder)
  function deletePayment(id) {
    if(confirm('Are you sure you want to delete this payment?')) {
      fetch('api/delete-amount-paid.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          loadData(currentPage);
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => console.error('Error:', error));
    }
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
