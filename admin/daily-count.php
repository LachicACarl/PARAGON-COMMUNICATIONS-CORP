<?php
/**
 * PARAGON COMMUNICATIONS - Daily Count Status
 * Daily status count and distribution report
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();
$today = date('Y-m-d');

// Get today's status counts
$todayActive = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'active' AND DATE(updated_at) = ?", [$today])['count'];
$todayDormant = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'dormant' AND DATE(updated_at) = ?", [$today])['count'];
$todayInactive = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'inactive' AND DATE(updated_at) = ?", [$today])['count'];
$todayPending = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'pending' AND DATE(updated_at) = ?", [$today])['count'];

// Get overall status counts
$totalActive = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'active'")['count'];
$totalDormant = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'dormant'")['count'];
$totalInactive = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'inactive'")['count'];
$totalPending = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'pending'")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Count Status - PARAGON</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6fb;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            color: #2573b6;
            margin: 0 0 5px 0;
        }
        
        .header .date {
            color: #666;
            font-size: 14px;
        }
        
        .stats-section {
            margin-bottom: 30px;
        }
        
        .stats-section h2 {
            color: #333;
            margin-bottom: 15px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 13px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
        }
        
        .stat-card.active {
            border-left: 4px solid #4CAF50;
        }
        
        .stat-card.active .value {
            color: #4CAF50;
        }
        
        .stat-card.dormant {
            border-left: 4px solid #FF9800;
        }
        
        .stat-card.dormant .value {
            color: #FF9800;
        }
        
        .stat-card.inactive {
            border-left: 4px solid #F44336;
        }
        
        .stat-card.inactive .value {
            color: #F44336;
        }
        
        .stat-card.pending {
            border-left: 4px solid #2196F3;
        }
        
        .stat-card.pending .value {
            color: #2196F3;
        }
        
        .back-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #2573b6;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 20px;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="header">
            <h1>📊 Daily Count Status</h1>
            <div class="date">Date: <?php echo date('F d, Y'); ?></div>
        </div>
        
        <div class="stats-section">
            <h2>Today's Updates</h2>
            <div class="stats-grid">
                <div class="stat-card active">
                    <h3>Active Today</h3>
                    <p class="value"><?php echo $todayActive; ?></p>
                </div>
                <div class="stat-card dormant">
                    <h3>Dormant Today</h3>
                    <p class="value"><?php echo $todayDormant; ?></p>
                </div>
                <div class="stat-card inactive">
                    <h3>Inactive Today</h3>
                    <p class="value"><?php echo $todayInactive; ?></p>
                </div>
                <div class="stat-card pending">
                    <h3>Pending Today</h3>
                    <p class="value"><?php echo $todayPending; ?></p>
                </div>
            </div>
        </div>
        
        <div class="stats-section">
            <h2>Overall Status Count</h2>
            <div class="stats-grid">
                <div class="stat-card active">
                    <h3>Total Active</h3>
                    <p class="value"><?php echo $totalActive; ?></p>
                </div>
                <div class="stat-card dormant">
                    <h3>Total Dormant</h3>
                    <p class="value"><?php echo $totalDormant; ?></p>
                </div>
                <div class="stat-card inactive">
                    <h3>Total Inactive</h3>
                    <p class="value"><?php echo $totalInactive; ?></p>
                </div>
                <div class="stat-card pending">
                    <h3>Total Pending</h3>
                    <p class="value"><?php echo $totalPending; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
