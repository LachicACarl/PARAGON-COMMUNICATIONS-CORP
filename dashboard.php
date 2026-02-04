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
    <title>PARAGON Dashboard</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .dashboard {
            display: flex;
            min-height: 100vh;
            background: #f5f5f5;
        }
        
        .sidebar {
            width: 250px;
            background: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }
        
        .sidebar .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
            color: #667eea;
        }
        
        .sidebar ul {
            list-style: none;
        }
        
        .sidebar li {
            margin-bottom: 10px;
        }
        
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar a:hover {
            background: #667eea;
            padding-left: 20px;
        }
        
        .sidebar a.active {
            background: #667eea;
            border-left: 4px solid #fff;
            padding-left: 11px;
        }
        
        .main {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            color: #333;
            margin: 0;
        }
        
        .user-info {
            text-align: right;
            font-size: 14px;
        }
        
        .user-info p {
            color: #666;
            margin: 0;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #667eea;
        }
        
        .card h3 {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .card .value {
            color: #333;
            font-size: 28px;
            font-weight: bold;
            margin: 0;
        }
        
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .menu-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #444;
        }
        
        .menu-section:last-child {
            border-bottom: none;
        }
        
        .menu-title {
            font-size: 12px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 1px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            color: #333;
            font-weight: 600;
            border-bottom: 2px solid #ddd;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
        }
        
        table tr:hover {
            background: #f9f9f9;
        }
        
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 600;
            background: #667eea;
            color: white;
            text-transform: uppercase;
        }
        
        .no-data {
            text-align: center;
            color: #999;
            padding: 20px;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <!-- SIDEBAR -->
        <div class="sidebar">
            <div class="logo">PARAGON</div>
            
            <ul>
                <li><a href="dashboard.php" class="active">Dashboard</a></li>
                
                <?php if (isHeadAdmin()): ?>
                    <li class="menu-section">
                        <div class="menu-title">Administration</div>
                        <a href="admin/users.php">User Management</a>
                        <a href="admin/approvals.php">Account Approvals</a>
                        <a href="admin/system-settings.php">System Settings</a>
                    </li>
                <?php endif; ?>
                
                <?php if (isAdmin()): ?>
                    <li class="menu-section">
                        <div class="menu-title">Management</div>
                        <a href="clients/list.php">Client Accounts</a>
                        <a href="import/upload.php">Import Masterlist</a>
                        <a href="clients/callout.php">Call Out Tracking</a>
                    </li>
                <?php endif; ?>
                
                <?php if (isManager()): ?>
                    <li class="menu-section">
                        <div class="menu-title">Operations</div>
                        <a href="clients/assigned.php">My Accounts</a>
                        <a href="clients/callout.php">Call Out Status</a>
                    </li>
                <?php endif; ?>
                
                <li class="menu-section">
                    <div class="menu-title">Reporting</div>
                    <a href="reports/index.php">Reports</a>
                    <a href="reports/audit.php">Audit Log</a>
                </li>
                
                <li>
                    <a href="logout.php" style="color: #ff6b6b;">Logout</a>
                </li>
            </ul>
        </div>
        
        <!-- MAIN CONTENT -->
        <div class="main">
            <!-- HEADER -->
            <div class="header">
                <div>
                    <h1>Welcome, <?php echo htmlspecialchars($username); ?></h1>
                </div>
                <div class="user-info">
                    <p><?php echo htmlspecialchars($userEmail); ?></p>
                    <p><span class="role-badge"><?php echo ucfirst(str_replace('_', ' ', $userRole)); ?></span></p>
                </div>
            </div>
            
            <!-- STAT CARDS -->
            <div class="cards">
                <?php if (!empty($stats['total_users'])): ?>
                    <div class="card">
                        <h3>Total Users</h3>
                        <p class="value"><?php echo $stats['total_users']; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['total_clients'])): ?>
                    <div class="card">
                        <h3>Client Accounts</h3>
                        <p class="value"><?php echo $stats['total_clients']; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['dormant_accounts'])): ?>
                    <div class="card">
                        <h3>Dormant Accounts</h3>
                        <p class="value"><?php echo $stats['dormant_accounts']; ?></p>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['total_amount_paid'])): ?>
                    <div class="card">
                        <h3>Amount Paid</h3>
                        <p class="value"><?php echo formatCurrency($stats['total_amount_paid']); ?></p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- RECENT ACTIVITIES -->
            <div class="section">
                <h2>Recent System Activities</h2>
                <?php if (!empty($recentActivities)): ?>
                    <table>
                        <tr>
                            <th>Action</th>
                            <th>User</th>
                            <th>Table</th>
                            <th>Time</th>
                        </tr>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($activity['action']); ?></strong></td>
                                <td><?php echo htmlspecialchars($activity['email'] ?? 'System'); ?></td>
                                <td><?php echo htmlspecialchars($activity['table_name'] ?? '-'); ?></td>
                                <td><?php echo formatDate($activity['created_at'], 'M d, Y H:i'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php else: ?>
                    <p class="no-data">No recent activities</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
