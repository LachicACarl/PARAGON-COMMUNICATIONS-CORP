<?php
/**
 * PARAGON COMMUNICATIONS - Visit Remarks
 * Track field visit remarks and activities
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();

// Get clients with main remarks (visit remarks)
$visitRemarks = getAll($pdo, "
    SELECT 
        ca.*,
        u1.first_name as created_by_first_name,
        u1.last_name as created_by_last_name,
        u2.first_name as managed_by_first_name,
        u2.last_name as managed_by_last_name
    FROM client_accounts ca
    LEFT JOIN users u1 ON ca.created_by = u1.id
    LEFT JOIN users u2 ON ca.managed_by = u2.id
    WHERE ca.main_remarks IS NOT NULL AND ca.main_remarks != ''
    ORDER BY ca.updated_at DESC
    LIMIT 100
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visit Remarks - PARAGON</title>
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
        
        .remarks-cell {
            max-width: 350px;
            white-space: normal;
            line-height: 1.5;
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
            <h1>📝 Visit Remarks</h1>
        </div>
        
        <div class="content-box">
            <h2>Field Visit Remarks & Notes</h2>
            <table>
                <thead>
                    <tr>
                        <th>Client Name</th>
                        <th>Address</th>
                        <th>Status</th>
                        <th>Managed By</th>
                        <th>Visit Remarks</th>
                        <th>Last Updated</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visitRemarks as $client): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($client['client_name']); ?></strong></td>
                            <td style="max-width: 200px;"><?php echo htmlspecialchars($client['address']); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $client['call_out_status']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($client['call_out_status'])); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                if ($client['managed_by_first_name']) {
                                    echo htmlspecialchars($client['managed_by_first_name'] . ' ' . $client['managed_by_last_name']);
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td class="remarks-cell"><?php echo htmlspecialchars($client['main_remarks']); ?></td>
                            <td><?php echo formatDate($client['updated_at'], 'M d, Y'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
