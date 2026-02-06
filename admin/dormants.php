<?php
/**
 * PARAGON COMMUNICATIONS - Dormants Per Area Report
 * Track dormant accounts by geographical area
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();

// Get dormant accounts grouped by city/area
$dormantsByArea = getAll($pdo, "
    SELECT 
        COALESCE(city, 'Unknown') as area,
        COUNT(*) as dormant_count,
        GROUP_CONCAT(client_name SEPARATOR ', ') as client_names
    FROM client_accounts
    WHERE call_out_status = 'dormant'
    GROUP BY city
    ORDER BY dormant_count DESC
");

$totalDormants = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'dormant'")['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dormants Per Area - PARAGON</title>
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
            margin: 0;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            border-left: 4px solid #FF9800;
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
        
        .count-badge {
            display: inline-block;
            padding: 5px 12px;
            background: #FF9800;
            color: white;
            border-radius: 20px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-btn">← Back to Dashboard</a>
        
        <div class="header">
            <h1>💤 Dormants Per Area</h1>
        </div>
        
        <div class="stat-card">
            <h3>Total Dormant Accounts</h3>
            <p class="value"><?php echo $totalDormants; ?></p>
        </div>
        
        <div class="content-box">
            <h2>Dormant Accounts by Area</h2>
            <table>
                <thead>
                    <tr>
                        <th>Area/City</th>
                        <th>Dormant Count</th>
                        <th>Percentage</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dormantsByArea as $area): ?>
                        <?php $percentage = $totalDormants > 0 ? round(($area['dormant_count'] / $totalDormants) * 100, 1) : 0; ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($area['area']); ?></strong></td>
                            <td><span class="count-badge"><?php echo $area['dormant_count']; ?></span></td>
                            <td><?php echo $percentage; ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
