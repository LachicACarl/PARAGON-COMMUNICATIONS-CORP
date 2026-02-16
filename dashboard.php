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
    
    // Get pending approvals
    $pendingApprovals = getPendingApprovals($pdo);
    
    // Get all client accounts with user details for Head Admin
    $clientAccounts = getAll($pdo, "
        SELECT 
            ca.*,
            u1.first_name as created_by_first_name,
            u1.last_name as created_by_last_name,
            u1.role as created_by_role,
            u2.first_name as managed_by_first_name,
            u2.last_name as managed_by_last_name,
            u2.role as managed_by_role
        FROM client_accounts ca
        LEFT JOIN users u1 ON ca.created_by = u1.id
        LEFT JOIN users u2 ON ca.managed_by = u2.id
        ORDER BY ca.created_at DESC
        LIMIT 100
    ");
}
elseif (isAdmin()) {
    // Admin gets their managed clients statistics
    $clientsResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE created_by = ? OR managed_by = ?", [$userId, $userId]);
    $stats['total_clients'] = $clientsResult['count'];
    
    $amountResult = getRow($pdo, "SELECT SUM(amount_paid) as total FROM client_accounts WHERE created_by = ? OR managed_by = ?", [$userId, $userId]);
    $stats['total_amount_paid'] = $amountResult['total'] ?? 0;
    
    $dormantResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE (created_by = ? OR managed_by = ?) AND call_out_status = 'dormant'", [$userId, $userId]);
    $stats['dormant_accounts'] = $dormantResult['count'];
}
elseif (isManager()) {
    // Manager gets their assigned clients statistics
    $clientsResult = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE managed_by = ?", [$userId]);
    $stats['total_clients'] = $clientsResult['count'];
    
    $amountResult = getRow($pdo, "SELECT SUM(amount_paid) as total FROM client_accounts WHERE managed_by = ?", [$userId]);
    $stats['total_amount_paid'] = $amountResult['total'] ?? 0;
}

// Get recent activities
$recentActivities = getAll($pdo, "
    SELECT aa.*, u.email, action 
    FROM audit_logs aa
    LEFT JOIN users u ON aa.user_id = u.id
    ORDER BY aa.created_at DESC
    LIMIT 10
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Backend Management Monitoring</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    body { margin:0; font-family:'Segoe UI', Tahoma, sans-serif; background:#f4f6f9; }
    .wrapper { display:flex; min-height:100vh; }
    .main { flex:1; padding:25px; margin-left:200px; }
    .header { background:#fff; padding:20px 25px; border-radius:12px; display:flex; justify-content:space-between; align-items:center; box-shadow:0 2px 6px rgba(0,0,0,0.08); }
    .header h1 { margin:0; font-size:22px; }
    .header p { margin:5px 0 0; font-size:14px; color:#666; }
    .profile { display:flex; align-items:center; gap:12px; background:#fff; padding:8px 15px; border-radius:30px; box-shadow:0 2px 5px rgba(0,0,0,0.1); }
    .profile .icon { width:38px; height:38px; background:#1769aa; color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .cards { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:15px; margin:25px 0; }
    .card { background:#fff; padding:18px; border-radius:12px; box-shadow:0 2px 5px rgba(0,0,0,0.08); text-align:center; font-weight:600; }
    .chart-section { display:flex; gap:20px; margin-top:20px; align-items:stretch; }
    .chart-box { flex:3; background:#fff; padding:20px; border-radius:12px; box-shadow:0 2px 5px rgba(0,0,0,0.08); }
    .status-box { flex:1; display:flex; flex-direction:column; gap:15px; }
    .status-card { background:#fff; padding:25px; border-radius:12px; text-align:center; font-weight:600; box-shadow:0 2px 5px rgba(0,0,0,0.08); }
    .status-card span { font-size:28px; display:block; margin-bottom:5px; }
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

  <main class="main">
    <div class="header">
      <div>
        <h1>Backend Management Monitoring</h1>
        <p>Overview of client statuses and productivity</p>
      </div>
      <div class="profile">
        <div class="icon"><span class="material-icons">person</span></div>
        <div><strong id="userName">Loading...</strong><br><small id="userRole">Loading...</small></div>
      </div>
    </div>

    <div class="cards">
      <div class="card" id="adminCard"></div>
      <div class="card" id="managerCard"></div>
      <div class="card" id="statusCard"></div>
      <div class="card" id="municipalityCard"></div>
    </div>

    <div class="chart-section">
      <div class="chart-box"><canvas id="statusChart" height="140"></canvas></div>
      <div class="status-box">
        <div class="status-card"><span id="activeClients" style="color:#2e7d32;"></span> ACTIVE CLIENTS</div>
        <div class="status-card"><span id="dormantClients" style="color:#c62828;"></span> DORMANT CLIENTS</div>
      </div>
    </div>
  </main>
</div>

<script>
fetch('fetch.php')
.then(res => res.json())
.then(data => {
  // Populate user info
  document.getElementById('userName').innerText = data.user.name || 'User';
  document.getElementById('userRole').innerText = data.user.role || 'User';
  
  // Populate summary cards
  document.getElementById('adminCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">${data.summary.ADMIN || 0}</span><br>ADMIN`;
  document.getElementById('managerCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">${data.summary.MANAGER || 0}</span><br>MANAGER`;
  document.getElementById('statusCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">${data.summary.STATUS || 0}</span><br>STATUS`;
  document.getElementById('municipalityCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">${data.summary.MUNICIPALITY || 0}</span><br>MUNICIPALITY`;

  // Populate client counts
  document.getElementById('activeClients').innerText = (data.clients.active || 0).toLocaleString();
  document.getElementById('dormantClients').innerText = (data.clients.dormant || 0).toLocaleString();

  // Create chart with real data
  const ctx = document.getElementById('statusChart');
  if (ctx && data.chart.labels.length > 0) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: data.chart.labels,
        datasets: [{
          label: 'Status Flow',
          data: data.chart.data,
          backgroundColor: '#4285F4',
          borderColor: '#1769aa',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        }
      }
    });
  }
})
.catch(err => {
  console.error('Error fetching dashboard data:', err);
  // Fallback to show default values
  document.getElementById('adminCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">0</span><br>ADMIN`;
  document.getElementById('managerCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">0</span><br>MANAGER`;
  document.getElementById('statusCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">0</span><br>STATUS`;
  document.getElementById('municipalityCard').innerHTML = `<span style="font-size:28px; color:#1769aa; font-weight:bold;">0</span><br>MUNICIPALITY`;
  document.getElementById('activeClients').innerText = '0';
  document.getElementById('dormantClients').innerText = '0';
});
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
