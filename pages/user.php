<?php
/**
 * PARAGON COMMUNICATIONS - User Accounts Management
 * Head Admin can manage admin and manager accounts here
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Check if user is logged in and is head admin
requireLogin();
if (!isHeadAdmin()) {
    header("Location: dashboard.php?error=access_denied");
    exit;
}

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
  <title>Manage User Accounts</title>
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
    .tab-content {
      animation: fadeIn 0.3s ease;
    }
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
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
        <h1 id="pageTitle" class="m-0 text-2xl font-bold text-gray-900">Manage User Accounts</h1>
        <p class="mt-1 text-sm text-gray-600">Overview of manager and admin accounts</p>
      </div>
    </div>

    <!-- PAGE CONTENT -->
    <div class="card bg-white rounded-xl shadow-sm p-6">

      <!-- Main Tabs for Manager/Admin -->
      <div class="flex gap-4 mb-6 border-b-2 border-gray-200 pb-0">
        <button class="main-tab active px-6 py-3 font-semibold text-gray-600 border-b-2 border-transparent transition-all hover:text-blue-600" data-tab="manager" style="border-color: transparent; position: relative; bottom: auto;">Manager</button>
        <button class="main-tab px-6 py-3 font-semibold text-gray-600 border-b-2 border-transparent transition-all hover:text-blue-600" data-tab="admin" style="border-color: transparent; position: relative; bottom: auto;">Admin</button>
      </div>

      <!-- Manager Tab Content -->
      <div id="manager-tab" class="tab-content">
        <!-- Search -->
        <div class="flex justify-end mb-6">
          <div class="flex items-center gap-2 bg-gray-100 px-4 py-2 rounded-lg border border-gray-200">
            <span class="material-icons text-gray-600">search</span>
            <input type="text" class="manager-search bg-transparent border-none outline-none text-sm" placeholder="Search..." onkeyup="performSearch('manager')">
            <button class="bg-blue-600 text-white border-none rounded px-3 py-1 cursor-pointer hover:bg-blue-700 transition-colors">
              <span class="material-icons text-base">search</span>
            </button>
          </div>
        </div>

        <!-- Manager Table -->
        <div class="overflow-x-auto">
          <table class="w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <thead class="bg-blue-600 text-white">
              <tr>
                <th style="width: 60px;" class="px-4 py-3 text-left">PROFILE</th>
                <th class="px-4 py-3 text-left">FULL NAME</th>
                <th class="px-4 py-3 text-left">CONTACT NUMBER</th>
                <th class="px-4 py-3 text-left">VALIDATION</th>
                <th style="width: 100px;" class="px-4 py-3 text-left">ACTION</th>
              </tr>
            </thead>
            <tbody id="managerTable">
              <tr style="text-align: center;">
                <td colspan="5" class="py-6">Loading data...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Manager Pagination -->
        <div class="flex justify-between items-center mt-6">
          <div class="pagination flex gap-2 flex-wrap" id="managerPagination"></div>
        </div>
      </div>

      <!-- Admin Tab Content -->
      <div id="admin-tab" class="tab-content" style="display: none;">
        <!-- Search -->
        <div class="flex justify-end mb-6">
          <div class="flex items-center gap-2 bg-gray-100 px-4 py-2 rounded-lg border border-gray-200">
            <span class="material-icons text-gray-600">search</span>
            <input type="text" class="admin-search bg-transparent border-none outline-none text-sm" placeholder="Search..." onkeyup="performSearch('admin')">
            <button class="bg-blue-600 text-white border-none rounded px-3 py-1 cursor-pointer hover:bg-blue-700 transition-colors">
              <span class="material-icons text-base">search</span>
            </button>
          </div>
        </div>

        <!-- Admin Table -->
        <div class="overflow-x-auto">
          <table class="w-full border-collapse bg-white rounded-lg overflow-hidden shadow-sm">
            <thead class="bg-blue-600 text-white">
              <tr>
                <th style="width: 60px;" class="px-4 py-3 text-left">PROFILE</th>
                <th class="px-4 py-3 text-left">FULL NAME</th>
                <th class="px-4 py-3 text-left">CONTACT NUMBER</th>
                <th class="px-4 py-3 text-left">VALIDATION</th>
                <th style="width: 100px;" class="px-4 py-3 text-left">ACTION</th>
              </tr>
            </thead>
            <tbody id="adminTable">
              <tr style="text-align: center;">
                <td colspan="5" class="py-6">Loading data...</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Admin Pagination -->
        <div class="flex justify-between items-center mt-6">
          <div class="pagination flex gap-2 flex-wrap" id="adminPagination"></div>
        </div>
      </div>

    </div>
  </main>
</div>

<script>
  let currentTab = 'manager';
  let currentPage = { manager: 1, admin: 1 };
  let searchQuery = { manager: '', admin: '' };
  const perPage = 10;

  // Main tab switching
  document.querySelectorAll('.main-tab').forEach(btn => {
    btn.addEventListener('click', function() {
      document.querySelectorAll('.main-tab').forEach(b => b.classList.remove('active'));
      this.classList.add('active');
      
      document.querySelectorAll('.tab-content').forEach(tab => tab.style.display = 'none');
      
      currentTab = this.dataset.tab;
      document.getElementById(currentTab + '-tab').style.display = 'block';
      
      // Update page title
      const tabTitle = currentTab.charAt(0).toUpperCase() + currentTab.slice(1);
      document.getElementById('pageTitle').textContent = 'Manage ' + tabTitle + ' Accounts';
      
      currentPage[currentTab] = 1;
      loadAccounts(currentTab);
    });
  });

  // Load accounts from API
  async function loadAccounts(role) {
    const tableBody = document.getElementById(role + 'Table');
    const pagination = document.getElementById(role + 'Pagination');
    
    tableBody.innerHTML = '<tr style="text-align: center;"><td colspan="5">Loading data...</td></tr>';

    try {
      const url = `fetch-admin-accounts.php?role=${role}&page=${currentPage[role]}&search=${encodeURIComponent(searchQuery[role])}`;
      const response = await fetch(url);
      const result = await response.json();

      if (!result.success || !result.data) {
        tableBody.innerHTML = '<tr style="text-align: center;"><td colspan="5">No data found.</td></tr>';
        pagination.innerHTML = '';
        return;
      }

      tableBody.innerHTML = '';

      result.data.forEach(account => {
        const row = document.createElement('tr');
        
        let statusText = account.approvalStatus.charAt(0).toUpperCase() + account.approvalStatus.slice(1);
        let badgeClass = 'approval-badge ' + account.approvalStatus;

        row.innerHTML = `
          <td style="text-align: center;">
            <div style="width: 40px; height: 40px; background: #1976d2; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
              <span class="material-icons">person</span>
            </div>
          </td>
          <td><strong>${account.fullName}</strong></td>
          <td>${account.contact}</td>
          <td>
            <div class="${badgeClass}">${statusText}</div>
          </td>
          <td style="text-align: center;">
            <button class="btn edit" title="Edit" onclick="editAccount(${account.id}, '${role}')"><span class="material-icons">edit</span></button>
            <button class="btn delete" title="Delete" onclick="deleteAccount(${account.id}, '${role}')"><span class="material-icons">delete</span></button>
          </td>
        `;
        tableBody.appendChild(row);
      });

      // Setup pagination
      pagination.innerHTML = '';
      
      if (result.pagination.totalPages > 0) {
        const prevBtn = document.createElement('button');
        prevBtn.textContent = 'Previous';
        prevBtn.disabled = currentPage[role] === 1;
        prevBtn.onclick = () => {
          if (currentPage[role] > 1) {
            currentPage[role]--;
            loadAccounts(role);
          }
        };
        pagination.appendChild(prevBtn);

        for (let i = 1; i <= result.pagination.totalPages; i++) {
          const btn = document.createElement('button');
          btn.textContent = i;
          btn.classList.toggle('active', i === currentPage[role]);
          btn.onclick = () => {
            currentPage[role] = i;
            loadAccounts(role);
          };
          btn.style.marginLeft = '5px';
          pagination.appendChild(btn);
        }

        const nextBtn = document.createElement('button');
        nextBtn.textContent = 'Next';
        nextBtn.disabled = currentPage[role] === result.pagination.totalPages;
        nextBtn.onclick = () => {
          if (currentPage[role] < result.pagination.totalPages) {
            currentPage[role]++;
            loadAccounts(role);
          }
        };
        nextBtn.style.marginLeft = '5px';
        pagination.appendChild(nextBtn);
      }
    } catch (error) {
      console.error('Error loading accounts:', error);
      tableBody.innerHTML = '<tr style="text-align: center;"><td colspan="5">Error loading data.</td></tr>';
    }
  }

  function performSearch(role) {
    const searchInput = role === 'manager' 
      ? document.querySelector('.manager-search')
      : document.querySelector('.admin-search');
    
    searchQuery[role] = searchInput ? searchInput.value : '';
    currentPage[role] = 1;
    loadAccounts(role);
  }

  async function editAccount(accountId, role) {
    alert('Edit functionality coming soon. Account ID: ' + accountId + ' (Role: ' + role + ')');
  }

  async function deleteAccount(accountId, role) {
    if (confirm('Are you sure you want to delete this account?')) {
      try {
        alert('Delete functionality coming soon. Account ID: ' + accountId + ' (Role: ' + role + ')');
      } catch (error) {
        console.error('Error:', error);
      }
    }
  }

  // Load initial data
  loadAccounts('manager');
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
