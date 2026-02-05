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
            background: #f4f6fb;
        }
        
        .sidebar {
            width: 176px;
            background: #1565a0;
            color: #ffffff;
            padding: 18px 12px;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }

        .sidebar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 3px;
        }

        .sidebar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .sidebar-logo {
            width: 140px;
            height: 40px;
            object-fit: contain;
        }

        .menu-toggle {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            border: none;
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 18px;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar li {
            margin-bottom: 10px;
        }
        
        .sidebar a {
            color: #e8f3ff;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .sidebar a.active {
            background: rgba(255, 255, 255, 0.25);
            border-left: 4px solid #4EE9FF;
            padding-left: 11px;
        }
        
        .main {
            margin-left: 176px;
            padding: 25px 30px;
            width: calc(100% - 176px);
        }
        
        .header {
            background: transparent;
            padding: 0 0 20px 0;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-title h1 {
            color: #2573b6;
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .header-title p {
            margin: 3px 0 0;
            color: #6b7a90;
            font-size: 13px;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #ffd29c;
            color: #1b3b5f;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
        }

        .user-info {
            text-align: left;
            font-size: 13px;
        }
        
        .user-info p {
            color: #666;
            margin: 0;
        }
        
        .cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 18px;
            margin-bottom: 28px;
        }
        
        .card {
            background: white;
            padding: 18px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e8eef8;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .card-icon {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            background: #e8f2fc;
            color: #1565a0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            flex-shrink: 0;
            margin-bottom: 10px;
        }
        
        .card h3 {
            color: #555;
            font-size: 12px;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
            line-height: 1.3;
        }
        
        .card .value {
            color: #333;
            font-size: 24px;
            font-weight: bold;
            margin: 8px 0 0;
        }

        .section-title {
            color: #333;
            font-size: 14px;
            font-weight: 600;
            margin: 0 0 18px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        }
        
        .section h2 {
            color: #1b3b5f;
            border-bottom: 2px solid #e6edf5;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .menu-section {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .menu-section:last-child {
            border-bottom: none;
        }
        
        .menu-title {
            font-size: 12px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.7);
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
            background: #1b6fb2;
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
            <div class="sidebar-header">
                <img src="assets/image.png" class="sidebar-logo" alt="Paragon Logo">
                <button class="menu-toggle" type="button">≡</button>
            </div>
            
            <ul>
                <li><a href="dashboard.php" class="active">DASHBOARD</a></li>
                
                <?php if (isHeadAdmin()): ?>
                    <li class="menu-section">
                        <div class="menu-title">HEAD ADMIN</div>
                        <a href="admin/monitoring.php">MONITORING</a>
                        <a href="admin/backend-productivity.php">BACKEND DAILY PRODUCTIVITY</a>
                        <a href="admin/pull-out.php">PULL OUT REPORT</a>
                        <a href="admin/dormants.php">DORMANTS PER AREA</a>
                        <a href="admin/recallouts.php">RECALLOUTS REMARKS</a>
                        <a href="admin/visit-remarks.php">VISIT REMARKS</a>
                        <a href="admin/daily-count.php">DAILY COUNT STATUS</a>
                        <a href="admin/s25-report.php">S25 PLAN REPORT</a>
                    </li>
                <?php endif; ?>
                
                <?php if (isAdmin()): ?>
                    <li class="menu-section">
                        <div class="menu-title">ADMIN</div>
                        <a href="reports/index.php">REPORTING</a>
                        <a href="import/upload.php">MASTERLIST MONITORING</a>
                        <a href="clients/callout.php">CALL OUT DORMANTS</a>
                    </li>
                <?php endif; ?>
                
                <?php if (isManager()): ?>
                    <li class="menu-section">
                        <div class="menu-title">MANAGER</div>
                        <a href="reports/index.php">REPORTING</a>
                    </li>
                <?php endif; ?>
                
                <li>
                    <a href="logout.php" style="color: #ff9999;">LOGOUT</a>
                </li>
            </ul>
        </div>
        
        <!-- MAIN CONTENT -->
        <div class="main">
            <!-- HEADER -->
            <div class="header">
                <div class="header-title">
                    <h1>Backend Management Monitoring System</h1>
                    <p>Welcome Back, <?php echo htmlspecialchars($username); ?>!</p>
                </div>
                <div class="user-profile">
                    <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <div class="user-info">
                        <p><strong><?php echo htmlspecialchars($username); ?></strong></p>
                        <p><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $userRole))); ?></p>
                    </div>
                </div>
            </div>
            
            <!-- STAT CARDS -->
            <h2 class="section-title">Client Statuses</h2>
            <div class="cards">
                <?php if (!empty($stats['total_users'])): ?>
                    <div class="card">
                        <div class="card-icon">👤</div>
                        <div>
                            <h3>Total Users</h3>
                            <p class="value"><?php echo $stats['total_users']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['total_clients'])): ?>
                    <div class="card">
                        <div class="card-icon">📋</div>
                        <div>
                            <h3>Client Accounts</h3>
                            <p class="value"><?php echo $stats['total_clients']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['dormant_accounts'])): ?>
                    <div class="card">
                        <div class="card-icon">💤</div>
                        <div>
                            <h3>Dormant Accounts</h3>
                            <p class="value"><?php echo $stats['dormant_accounts']; ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($stats['total_amount_paid'])): ?>
                    <div class="card">
                        <div class="card-icon">💰</div>
                        <div>
                            <h3>Amount Paid</h3>
                            <p class="value"><?php echo formatCurrency($stats['total_amount_paid']); ?></p>
                        </div>
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
