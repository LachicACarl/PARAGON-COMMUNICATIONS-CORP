<?php
/**
 * PARAGON COMMUNICATIONS - Monitoring Dashboard
 * Real-time monitoring of all client accounts and system activities
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Check if user is logged in and is Head Admin
requireLogin();
requireRole('head_admin');

// Get user info
$userId = getCurrentUserId();
$username = getCurrentUserName();
$userRole = getCurrentRole();

// Get monitoring statistics
$totalClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts")['count'];
$activeClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'active'")['count'];
$dormantClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'dormant'")['count'];
$inactiveClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'inactive'")['count'];

// Get all client accounts for monitoring
$clients = getAll($pdo, "
    SELECT 
        ca.*,
        u1.first_name as created_by_first_name,
        u1.last_name as created_by_last_name,
        u2.first_name as managed_by_first_name,
        u2.last_name as managed_by_last_name
    FROM client_accounts ca
    LEFT JOIN users u1 ON ca.created_by = u1.id
    LEFT JOIN users u2 ON ca.managed_by = u2.id
    ORDER BY ca.updated_at DESC
    LIMIT 50
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring - PARAGON</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideIn {
            from { transform: translateX(-30px); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            animation: fadeIn 0.6s ease-out;
        }
        
        .header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            color: white;
            animation: slideIn 0.5s ease-out;
        }
        
        .header h1 {
            color: white;
            margin: 0 0 8px 0;
            font-size: 32px;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }
        
        .header .breadcrumb {
            color: rgba(255,255,255,0.9);
            font-size: 14px;
        }
        
        .header .breadcrumb a {
            color: white;
            text-decoration: none;
            font-weight: 600;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 25px;
            margin-bottom: 35px;
        }
        
        .stat-card {
            background: white;
            padding: 30px 25px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            text-align: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            animation: fadeIn 0.6s ease-out forwards;
            opacity: 0;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, transparent, currentColor, transparent);
            animation: pulse 2s ease-in-out infinite;
        }
        
        .stat-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.25);
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 13px;
            margin: 0 0 15px 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        
        .stat-card .value {
            font-size: 42px;
            font-weight: 800;
            margin: 0;
        }
        
        .stat-card.total { color: #2196F3; }
        .stat-card.total .value { color: #2196F3; }
        .stat-card.active { color: #4CAF50; }
        .stat-card.active .value { color: #4CAF50; }
        .stat-card.dormant { color: #FF9800; }
        .stat-card.dormant .value { color: #FF9800; }
        .stat-card.inactive { color: #F44336; }
        .stat-card.inactive .value { color: #F44336; }
        
        .content-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
            animation: fadeIn 0.8s ease-out 0.5s backwards;
        }
        
        .content-box h2 {
            margin: 0 0 25px 0;
            color: #333;
            font-size: 22px;
            font-weight: 700;
            position: relative;
            padding-bottom: 12px;
        }
        
        .content-box h2::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 2px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 15px 12px;
            text-align: left;
            color: white;
            font-weight: 600;
            font-size: 13px;
            border: none;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        table th:first-child {
            border-top-left-radius: 10px;
        }
        
        table th:last-child {
            border-top-right-radius: 10px;
        }
        
        table td {
            padding: 15px 12px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-size: 13px;
        }
        
        table tbody tr {
            transition: all 0.2s ease;
        }
        
        table tr:hover {
            background: linear-gradient(90deg, #f0f7ff, #fff);
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .status-active { 
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
        }
        .status-dormant { 
            background: linear-gradient(135deg, #FF9800, #FB8C00);
            color: white;
            box-shadow: 0 2px 8px rgba(255, 152, 0, 0.3);
        }
        .status-inactive { 
            background: linear-gradient(135deg, #F44336, #E53935);
            color: white;
            box-shadow: 0 2px 8px rgba(244, 67, 54, 0.3);
        }
        .status-pending { 
            background: linear-gradient(135deg, #2196F3, #1976D2);
            color: white;
            box-shadow: 0 2px 8px rgba(33, 150, 243, 0.3);
        }
        
        .back-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            margin-bottom: 25px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .back-btn::before {
            content: '\u2190';
            margin-right: 8px;
            font-size: 16px;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .back-btn:active {
            transform: translateY(0);
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-btn">Back to Dashboard</a>
        
        <div class="header">
            <h1>📊 Monitoring Dashboard</h1>
            <div class="breadcrumb">
                <a href="../dashboard.php">Dashboard</a> / Monitoring
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card total">
                <h3>Total Clients</h3>
                <p class="value"><?php echo $totalClients; ?></p>
            </div>
            <div class="stat-card active">
                <h3>Active</h3>
                <p class="value"><?php echo $activeClients; ?></p>
            </div>
            <div class="stat-card dormant">
                <h3>Dormant</h3>
                <p class="value"><?php echo $dormantClients; ?></p>
            </div>
            <div class="stat-card inactive">
                <h3>Inactive</h3>
                <p class="value"><?php echo $inactiveClients; ?></p>
            </div>
        </div>
        
        <div class="content-box">
            <h2>Recent Client Activities (Last 50)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Managed By</th>
                        <th>Status</th>
                        <th>Amount Paid</th>
                        <th>Sales Category</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clients as $client): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($client['client_name']); ?></strong></td>
                            <td>
                                <?php 
                                if ($client['managed_by_first_name']) {
                                    echo htmlspecialchars($client['managed_by_first_name'] . ' ' . $client['managed_by_last_name']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td>
                                <span class="status-badge status-<?php echo $client['call_out_status']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($client['call_out_status'])); ?>
                                </span>
                            </td>
                            <td><?php echo formatCurrency($client['amount_paid']); ?></td>
                            <td><?php echo htmlspecialchars($client['sales_category'] ?? '-'); ?></td>
                            <td><?php echo formatDate($client['updated_at'], 'M d, Y H:i'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
