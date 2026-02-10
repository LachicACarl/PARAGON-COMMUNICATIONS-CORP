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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Backend Management Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/chart.js" rel="stylesheet">
  <style>
    body {
      margin: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f8f9fa;
    }
    .sidebar {
      width: 220px;
      background-color: #1565c0;
      color: white;
      height: 100vh;
      position: fixed;
      display: flex;
      flex-direction: column;
      padding-top: 20px;
    }
    .sidebar h2 {
      text-align: center;
      font-size: 18px;
      margin-bottom: 30px;
    }
    .sidebar a {
      text-decoration: none;
      color: white;
      padding: 10px 20px;
      display: block;
      transition: 0.3s;
    }
    .sidebar a:hover {
      background-color: #0d47a1;
    }
    .main-content {
      margin-left: 220px;
      padding: 20px;
    }
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .cards {
      display: flex;
      gap: 15px;
      margin: 20px 0;
      flex-wrap: wrap;
    }
    .card {
      background-color: #e3f2fd;
      padding: 20px;
      border-radius: 10px;
      flex: 1;
      min-width: 120px;
      text-align: center;
      font-weight: bold;
    }
    .status-card {
      display: flex;
      justify-content: space-between;
      margin-top: 20px;
      gap: 10px;
      flex-wrap: wrap;
    }
    .status-card div {
      flex: 1;
      min-width: 150px;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      font-weight: bold;
      background-color: #fff;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
    canvas {
      background-color: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }
  </style>
</head>
<body>
  <div class="sidebar">
    <h2>Paragon Corp</h2>
    <a href="#">Dashboard</a>
    <hr style="border: 0.5px solid #ffffff30;">
    <a href="#">User</a>
    <a href="#">Address</a>
    <a href="#">Amount Paid</a>
    <a href="#">Installation Fee</a>
    <a href="#">Call Out Status</a>
    <a href="#">Pull Out Remarks</a>
    <a href="#">Status Input Channel</a>
    <a href="#">Sales Category</a>
    <a href="#">Main Remarks</a>
    <hr style="border: 0.5px solid #ffffff30;">
    <a href="#">Profile</a>
    <a href="#">Logout</a>
  </div>

  <div class="main-content">
    <div class="header">
      <h1>Backend Management Monitoring</h1>
      <div>
        <strong>Juan Dela Cruz</strong><br>Admin
      </div>
    </div>

    <p>Overview of client statuses and productivity</p>

    <div class="cards">
      <div class="card">5<br>ADMIN</div>
      <div class="card">3<br>MANAGER</div>
      <div class="card">0<br>STATUS</div>
      <div class="card">5<br>MUNICIPALITY</div>
    </div>

    <div class="status-card">
      <div>
        ACTIVE CLIENTS<br>
        <span style="font-size: 24px; color: green;">49,789</span>
      </div>
      <div>
        DORMANT CLIENTS<br>
        <span style="font-size: 24px; color: red;">29,164</span>
      </div>
    </div>

    <canvas id="statusChart" width="800" height="400"></canvas>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['With Load Balance', '4X Uncontacted', '3X Uncontacted', 'For Callout', 'Client Moved Out', 'Others'],
        datasets: [{
          label: 'Status Daily Flow Thru',
          data: [105, 72, 45, 28, 21, 10],
          backgroundColor: ['#4285F4','#616161','#9E9E9E','#FFB74D','#AB47BC','#EF5350']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  </script>
</body>
</html>
