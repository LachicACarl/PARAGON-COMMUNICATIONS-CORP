<?php
/**
 * PARAGON COMMUNICATIONS - S25 Plan Report
 * Track S25 plan subscriptions and status
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();

// Get clients with S25 in sales category or remarks
$s25Clients = getAll($pdo, "
    SELECT 
        ca.*,
        u1.first_name as created_by_first_name,
        u1.last_name as created_by_last_name,
        u2.first_name as managed_by_first_name,
        u2.last_name as managed_by_last_name
    FROM client_accounts ca
    LEFT JOIN users u1 ON ca.created_by = u1.id
    LEFT JOIN users u2 ON ca.managed_by = u2.id
    WHERE ca.sales_category LIKE '%S25%' 
       OR ca.sales_category LIKE '%s25%'
       OR ca.main_remarks LIKE '%S25%'
       OR ca.main_remarks LIKE '%s25%'
    ORDER BY ca.created_at DESC
");

$totalS25 = count($s25Clients);
$totalS25Amount = array_sum(array_column($s25Clients, 'amount_paid'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>S25 Plan Report - PARAGON</title>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f6fb;
            margin: 0;
            padding: 20px;
        }
        
        .container {
            max-width: 1400px;
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
            margin: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border-left: 4px solid #673AB7;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #673AB7;
        }
        
        .content-box {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
        
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .status-active { background: #d4edda; color: #155724; }
        .status-dormant { background: #fff3cd; color: #856404; }
        .status-inactive { background: #f8d7da; color: #721c24; }
        
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
            <h1>📱 S25 Plan Report</h1>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total S25 Subscribers</h3>
                <p class="value"><?php echo $totalS25; ?></p>
            </div>
            <div class="stat-card">
                <h3>Total S25 Revenue</h3>
                <p class="value"><?php echo formatCurrency($totalS25Amount); ?></p>
            </div>
        </div>
        
        <div class="content-box">
            <h2>S25 Plan Subscribers</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Amount Paid</th>
                        <th>Sales Category</th>
                        <th>Managed By</th>
                        <th>Created Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($s25Clients)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px; color: #999;">
                                No S25 plan subscribers found
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($s25Clients as $client): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($client['client_name']); ?></strong></td>
                                <td style="max-width: 200px;"><?php echo htmlspecialchars($client['address']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $client['call_out_status']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($client['call_out_status'])); ?>
                                    </span>
                                </td>
                                <td><?php echo formatCurrency($client['amount_paid']); ?></td>
                                <td><?php echo htmlspecialchars($client['sales_category'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    if ($client['managed_by_first_name']) {
                                        echo htmlspecialchars($client['managed_by_first_name'] . ' ' . $client['managed_by_last_name']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                                <td><?php echo formatDate($client['created_at'], 'M d, Y'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
