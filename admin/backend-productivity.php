<?php
/**
 * PARAGON COMMUNICATIONS - Backend Daily Productivity Report
 * Track daily productivity metrics for backend team
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

requireLogin();
requireRole('head_admin');

$username = getCurrentUserName();

// Get today's date
$today = date('Y-m-d');

// Get daily statistics
$todayClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE DATE(created_at) = ?", [$today])['count'];
$todayUpdates = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE DATE(updated_at) = ?", [$today])['count'];

// Get productivity by user
$productivity = getAll($pdo, "
    SELECT 
        u.first_name,
        u.last_name,
        u.role,
        COUNT(CASE WHEN DATE(ca.created_at) = ? THEN 1 END) as created_today,
        COUNT(CASE WHEN DATE(ca.updated_at) = ? THEN 1 END) as updated_today
    FROM users u
    LEFT JOIN client_accounts ca ON u.id = ca.created_by OR u.id = ca.managed_by
    WHERE u.role IN ('admin', 'manager')
    GROUP BY u.id
    ORDER BY created_today DESC, updated_today DESC
", [$today, $today]);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Backend Daily Productivity - PARAGON</title>
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
            font-size: 24px;
        }
        
        .header .date {
            color: #666;
            font-size: 14px;
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
            border-left: 4px solid #4CAF50;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 14px;
            margin: 0 0 10px 0;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            color: #333;
            font-size: 36px;
            font-weight: bold;
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
        
        .role-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: 600;
            background: #2196F3;
            color: white;
            text-transform: uppercase;
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
            <h1>📈 Backend Daily Productivity</h1>
            <div class="date">Date: <?php echo date('F d, Y'); ?></div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>New Clients Today</h3>
                <p class="value"><?php echo $todayClients; ?></p>
            </div>
            <div class="stat-card">
                <h3>Updates Today</h3>
                <p class="value"><?php echo $todayUpdates; ?></p>
            </div>
        </div>
        
        <div class="content-box">
            <h2>Team Productivity</h2>
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Clients Created Today</th>
                        <th>Clients Updated Today</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productivity as $user): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                            <td><span class="role-badge"><?php echo htmlspecialchars(ucfirst($user['role'])); ?></span></td>
                            <td><?php echo $user['created_today']; ?></td>
                            <td><?php echo $user['updated_today']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
