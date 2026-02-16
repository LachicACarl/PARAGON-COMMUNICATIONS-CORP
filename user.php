<?php
/**
 * PARAGON COMMUNICATIONS - User Accounts Management
 * Head Admin can manage admin and manager accounts here
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

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
  <title>Manage Accounts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Material Icons -->
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
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f6f9;
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ===== Main Content ===== */
    .content {
      margin-left: 200px;
      padding: 20px;
      width: 100%;
    }

    h1 {
      margin-bottom: 15px;
      color: #333;
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }
    .top-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      gap: 10px;
      flex-wrap: wrap;
    }

    .role-tab {
      padding: 10px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      background: #e0e0e0;
      color: #333;
      transition: all 0.3s ease;
    }

    .role-tab.active {
      background: #1976d2;
      color: #fff;
    }

    .main-tab {
      padding: 12px 24px;
      border: none;
      border-bottom: 3px solid transparent;
      background: none;
      cursor: pointer;
      font-weight: 600;
      color: #666;
      font-size: 15px;
      transition: all 0.3s ease;
      position: relative;
      bottom: -2px;
    }

    .main-tab.active {
      color: #1976d2;
      border-bottom-color: #1976d2;
    }

    .main-tab:hover {
      color: #1976d2;
    }

    .tab-content {
      animation: fadeIn 0.3s ease;
    }

    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }

    .search-box {
      display: flex;
      align-items: center;
      gap: 8px;
      background: #f5f5f5;
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ddd;
    }

    .search-box input {
      padding: 6px 8px;
      border: none;
      background: transparent;
      width: 220px;
      font-size: 14px;
      outline: none;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table thead {
      background: #1976d2;
      color: #fff;
    }

    table th, table td {
      padding: 14px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    table tbody tr {
      background: #fff;
      transition: background 0.2s ease;
    }

    table tbody tr:hover {
      background: #f9f9f9;
    }

    .approval-badge {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 20px;
      font-weight: bold;
      font-size: 12px;
      text-align: center;
      min-width: 80px;
    }

    .approval-badge.approved {
      background: #e8f5e9;
      color: #2e7d32;
    }

    .approval-badge.pending {
      background: #fff3e0;
      color: #f57c00;
    }

    .approval-badge.declined {
      background: #ffebee;
      color: #c62828;
    }

    .btn {
      border: none;
      background: none;
      cursor: pointer;
      padding: 8px;
      display: inline-flex;
      align-items: center;
      transition: transform 0.2s ease;
    }

    .btn:hover {
      transform: scale(1.2);
    }

    .btn.edit {
      color: #1976d2;
    }

    .btn.delete {
      color: #e53935;
    }

    /* ===== Pagination ===== */
    .pagination {
      display: flex;
      justify-content: flex-start;
      gap: 8px;
      flex-wrap: wrap;
    }

    .pagination button {
      padding: 8px 14px;
      border: 1px solid #ddd;
      background: #fff;
      cursor: pointer;
      border-radius: 4px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .pagination button:hover:not(:disabled) {
      background: #1976d2;
      color: #fff;
      border-color: #1976d2;
    }

    .pagination button.active {
      background: #1976d2;
      color: #fff;
      border-color: #1976d2;
    }

    .pagination button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }
  </style>
</head>
<body>

<button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
<div class="wrapper">

  <!-- Sidebar -->
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
    .sidebar { width: 200px; background: linear-gradient(135deg, #1565c0, #1976d2); color: #fff; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; }
    .sidebar .logo { margin-bottom: 30px; }
    .sidebar .logo img { max-width: 100%; height: auto; }
    .sidebar nav { display: flex; flex-direction: column; gap: 5px; }
    .sidebar nav a { display: flex; align-items: center; gap: 15px; padding: 12px 15px; color: rgba(255, 255, 255, 0.8); text-decoration: none; border-radius: 5px; transition: all 0.3s ease; font-size: 14px; }
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

  <!-- Main -->
  <main class="content">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <div>
        <h1 id="pageTitle" style="margin: 0 0 5px 0; font-size: 26px; color: #333;">Manage Manager Accounts</h1>
        <p style="margin: 0; color: #666; font-size: 14px;">Overview of manager accounts to manage.</p>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; background: #fff; padding: 10px 16px; border-radius: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div style="width: 40px; height: 40px; background: #1976d2; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
          <?php echo htmlspecialchars($initials); ?>
        </div>
        <div>
          <strong><?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?></strong><br>
          <small>Head Admin</small>
        </div>
      </div>
    </div>

    <div class="card">

      <!-- Main Tabs for Manager/Admin -->
      <div style="display: flex; gap: 10px; margin-bottom: 20px; border-bottom: 2px solid #e0e0e0; padding-bottom: 0;">
        <button class="main-tab active" data-tab="manager">Manager</button>
        <button class="main-tab" data-tab="admin">Admin</button>
      </div>

      <!-- Manager Tab Content -->
      <div id="manager-tab" class="tab-content">
        <!-- Search -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
          <div class="search-box">
            <span class="material-icons">search</span>
            <input type="text" class="manager-search" placeholder="Search..." onkeyup="performSearch('manager')">
            <button style="background: #1976d2; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; cursor: pointer;">
              <span class="material-icons" style="font-size: 20px;">search</span>
            </button>
          </div>
        </div>

        <!-- Manager Table -->
        <table>
          <thead>
            <tr>
              <th style="width: 60px;">PROFILE</th>
              <th>FULL NAME</th>
              <th>CONTACT NUMBER</th>
              <th>VALIDATION</th>
              <th style="width: 100px;">ACTION</th>
            </tr>
          </thead>
          <tbody id="managerTable">
            <tr style="text-align: center;">
              <td colspan="5">Loading data...</td>
            </tr>
          </tbody>
        </table>

        <!-- Manager Pagination -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
          <div class="pagination" id="managerPagination"></div>
        </div>
      </div>

      <!-- Admin Tab Content -->
      <div id="admin-tab" class="tab-content" style="display: none;">
        <!-- Search -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
          <div class="search-box">
            <span class="material-icons">search</span>
            <input type="text" class="admin-search" placeholder="Search..." onkeyup="performSearch('admin')">
            <button style="background: #1976d2; color: #fff; border: none; border-radius: 5px; padding: 6px 12px; cursor: pointer;">
              <span class="material-icons" style="font-size: 20px;">search</span>
            </button>
          </div>
        </div>

        <!-- Admin Table -->
        <table>
          <thead>
            <tr>
              <th style="width: 60px;">PROFILE</th>
              <th>FULL NAME</th>
              <th>CONTACT NUMBER</th>
              <th>VALIDATION</th>
              <th style="width: 100px;">ACTION</th>
            </tr>
          </thead>
          <tbody id="adminTable">
            <tr style="text-align: center;">
              <td colspan="5">Loading data...</td>
            </tr>
          </tbody>
        </table>

        <!-- Admin Pagination -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px;">
          <div class="pagination" id="adminPagination"></div>
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
