<?php
/**
 * PARAGON COMMUNICATIONS - Recallouts Remarks
 * Track and manage recallout activities and remarks
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();

// Get call-out history
$callOutHistory = getAll($pdo, "
    SELECT 
        ch.*,
        ca.client_name,
        ca.address,
        ca.phone,
        u.first_name,
        u.last_name
    FROM call_out_history ch
    JOIN client_accounts ca ON ch.client_account_id = ca.id
    LEFT JOIN users u ON ch.updated_by = u.id
    ORDER BY ch.call_out_date DESC
    LIMIT 100
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recallouts Remarks - PARAGON</title>
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
            font-size: 13px;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-size: 13px;
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
        
        .remarks-cell {
            max-width: 250px;
            white-space: normal;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="header">
            <h1>📞 Recallouts Remarks</h1>
        </div>
        
        <div class="content-box">
            <h2>Call Out History & Remarks</h2>
            <table>
                <thead>
                    <tr>
                        <th>Call Out Date</th>
                        <th>Client Name</th>
                        <th>Status Before</th>
                        <th>Status After</th>
                        <th>Remarks</th>
                        <th>Updated By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($callOutHistory)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 30px; color: #999;">
                                No call-out history available
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($callOutHistory as $history): ?>
                            <tr>
                                <td><?php echo formatDate($history['call_out_date'], 'M d, Y H:i'); ?></td>
                                <td><strong><?php echo htmlspecialchars($history['client_name']); ?></strong></td>
                                <td>
                                    <span class="status-badge status-<?php echo $history['status_before']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($history['status_before'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $history['status_after']; ?>">
                                        <?php echo htmlspecialchars(ucfirst($history['status_after'])); ?>
                                    </span>
                                </td>
                                <td class="remarks-cell"><?php echo htmlspecialchars($history['remarks'] ?? '-'); ?></td>
                                <td>
                                    <?php 
                                    if ($history['first_name']) {
                                        echo htmlspecialchars($history['first_name'] . ' ' . $history['last_name']);
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
