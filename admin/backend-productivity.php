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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
    <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
        <span class="material-icons">menu</span>
    </button>
    <div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
    <div class="container">
    <?php
    $currentPage = basename($_SERVER['PHP_SELF']);
    $userRole = getCurrentRole();
    
    // Define all navigation items with role restrictions
    $allNavItems = [
      '../dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['head_admin', 'admin', 'manager']],
      '../user.php' => ['icon' => 'people', 'label' => 'User', 'roles' => ['head_admin']],
      '../address.php' => ['icon' => 'location_on', 'label' => 'Address', 'roles' => ['head_admin']],
      '../amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid', 'roles' => ['head_admin']],
      'installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee', 'roles' => ['head_admin']],
      'call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status', 'roles' => ['head_admin']],
      'pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks', 'roles' => ['head_admin']],
      'status_input.php' => ['icon' => 'input', 'label' => 'Status Input', 'roles' => ['head_admin']],
      'sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category', 'roles' => ['head_admin']],
      'main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks', 'roles' => ['head_admin']],
      'monitoring.php' => ['icon' => 'monitor', 'label' => 'Backend Monitoring', 'roles' => ['admin']],
      'backend-productivity.php' => ['icon' => 'assessment', 'label' => 'Backend Productivity', 'roles' => ['admin']],
      'dormants.php' => ['icon' => 'person_off', 'label' => 'Dormants', 'roles' => ['admin']],
      'recallouts.php' => ['icon' => 'phone_callback', 'label' => 'Recallouts', 'roles' => ['admin']],
      'pull-out.php' => ['icon' => 'content_paste', 'label' => 'Pull Out Report', 'roles' => ['admin', 'manager']],
      's25-report.php' => ['icon' => 'summarize', 'label' => 'S25 Report', 'roles' => ['admin', 'manager']],
      'daily-count.php' => ['icon' => 'today', 'label' => 'Daily Count', 'roles' => ['admin', 'manager']],
      'visit-remarks.php' => ['icon' => 'comment', 'label' => 'Visit Remarks', 'roles' => ['admin', 'manager']],
      '../profile.php' => ['icon' => 'person', 'label' => 'Profile', 'roles' => ['head_admin', 'admin', 'manager']],
      '../logout.php' => ['icon' => 'logout', 'label' => 'Logout', 'roles' => ['head_admin', 'admin', 'manager']],
    ];
    
    // Filter navigation items based on user role
    $navItems = array_filter($allNavItems, function($item) use ($userRole) {
      return in_array($userRole, $item['roles']);
    });
    ?>
    <aside class="sidebar">
      <div class="logo">
        <img src="../assets/image.png" alt="Paragon Logo">
      </div>
      <nav class="nav">
        <?php foreach($navItems as $file => $item): ?>
          <a href="<?php echo $file; ?>" class="<?php echo $currentPage === basename($file) ? 'active' : ''; ?>">
            <span class="material-icons"><?php echo $item['icon']; ?></span>
            <?php echo $item['label']; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>
    
    <main class="main-content">
        
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
    </main>
  </div>
  
  <script>
    function toggleMobileSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    }
    
    function closeMobileSidebar() {
        const sidebar = document.querySelector('.sidebar');
        const overlay = document.querySelector('.mobile-overlay');
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    }
    
    document.querySelectorAll('.sidebar .nav a').forEach(link => {
        link.addEventListener('click', () => {
            if (window.innerWidth <= 768) {
                closeMobileSidebar();
            }
        });
    });
    
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileSidebar();
        }
    });
  </script>
</body>
</html>
