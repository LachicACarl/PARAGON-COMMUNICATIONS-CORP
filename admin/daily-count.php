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
