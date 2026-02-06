<?php
/**
 * PARAGON COMMUNICATIONS - Pull Out Report
 * Track and monitor client pull-out activities
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();

// Get clients with pull-out remarks
$pullOutClients = getAll($pdo, "
    SELECT 
        ca.*,
        u1.first_name as created_by_first_name,
        u1.last_name as created_by_last_name,
        u2.first_name as managed_by_first_name,
        u2.last_name as managed_by_last_name
    FROM client_accounts ca
    LEFT JOIN users u1 ON ca.created_by = u1.id
    LEFT JOIN users u2 ON ca.managed_by = u2.id
    WHERE ca.pull_out_remarks IS NOT NULL AND ca.pull_out_remarks != ''
    ORDER BY ca.updated_at DESC
");

$totalPullOuts = count($pullOutClients);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pull Out Report - PARAGON</title>
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
            margin: 0 0 5px 0;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #F44336;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        
        .stat-card .value {
            font-size: 36px;
            font-weight: bold;
            color: #333;
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
        
        .remarks {
            max-width: 300px;
            white-space: normal;
            word-wrap: break-word;
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
            <h1>📤 Pull Out Report</h1>
        </div>
        
        <div class="stat-card">
            <h3>Total Pull Outs</h3>
            <p class="value"><?php echo $totalPullOuts; ?></p>
        </div>
        
        <div class="content-box">
            <h2>Client Pull Out Details</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Managed By</th>
                        <th>Address</th>
                        <th>Pull Out Remarks</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pullOutClients as $client): ?>
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
                            <td style="max-width: 200px;"><?php echo htmlspecialchars($client['address']); ?></td>
                            <td class="remarks"><?php echo htmlspecialchars($client['pull_out_remarks']); ?></td>
                            <td><?php echo formatDate($client['updated_at'], 'M d, Y'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
