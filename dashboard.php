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
    body { margin:0; font-family:'Segoe UI', Tahoma, sans-serif; background:#f4f6f9; }
    .wrapper { display:flex; min-height:100vh; }
    .sidebar { width:200px; background:#1769aa; color:#fff; padding:20px; }
    .sidebar .menu { font-size:26px; cursor:pointer; }
    .logo { text-align:center; margin:40px 0; }
    .logo img { width:140px; }
    .nav a { display:flex; align-items:center; gap:10px; color:#fff; text-decoration:none; padding:10px 12px; border-radius:8px; margin-bottom:5px; font-size:14px; }
    .nav a:hover { background:rgba(255,255,255,0.15); }
    .main { flex:1; padding:25px; }
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
<div class="wrapper">
  <!-- Updated Sidebar with page links -->
  <aside class="sidebar">
    <span class="material-icons menu">menu</span>
    <div class="logo"><img src="assets/image.png" alt="Paragon Logo"></div>
    <nav class="nav">
      <a href="dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
      <a href="user.php"><span class="material-icons">people</span> User</a>
      <a href="address.php"><span class="material-icons">location_on</span> Address</a>
      <a href="amountpaid.php"><span class="material-icons">checklist</span> Amount Paid</a>
      <a href="installation_fee.html"><span class="material-icons">attach_money</span> Installation Fee</a>
      <a href="call_out_status.html"><span class="material-icons">call</span> Call Out Status</a>
      <a href="pull_out_remarks.html"><span class="material-icons">notes</span> Pull Out Remarks</a>
      <a href="status_input.html"><span class="material-icons">input</span> Status Input Channel</a>
      <a href="sales_category.html"><span class="material-icons">category</span> Sales Category</a>
      <a href="main_remarks.html"><span class="material-icons">edit</span> Main Remarks</a>
      <a href="profile.html"><span class="material-icons">person</span> Profile</a>
      <a href="logout.html"><span class="material-icons">logout</span> Logout</a>
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
        <div><strong>Juan Dela Cruz</strong><br><small>Admin</small></div>
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
  document.getElementById('adminCard').innerHTML = `${data.summary.ADMIN}<br>ADMIN`;
  document.getElementById('managerCard').innerHTML = `${data.summary.MANAGER}<br>MANAGER`;

  document.getElementById('activeClients').innerText = data.clients.active.toLocaleString();
  document.getElementById('dormantClients').innerText = data.clients.dormant.toLocaleString();

  const ctx = document.getElementById('statusChart');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: data.chart.labels,
      datasets: [{ data: data.chart.data, backgroundColor: '#4285F4' }]
    },
    options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
  });
})
.catch(err => console.error(err));
</script>
</body>
</html>
