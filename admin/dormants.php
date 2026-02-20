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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/style.css">
    <style>
      .material-icons {
        font-family: 'Material Icons';
        font-weight: normal;
        font-style: normal;
        font-size: 24px;
        display: inline-block;
        line-height: 1;
        text-transform: none;
        letter-spacing: normal;
        word-wrap: normal;
        white-space: nowrap;
        direction: ltr;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
        -moz-osx-font-smoothing: grayscale;
        font-feature-settings: 'liga';
      }
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
    <link rel="stylesheet" href="../assets/tailwind-compat.css">
</head>
<body>
    <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
        <span class="material-icons">menu</span>
    </button>
    <div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
    <div class="container">
    <?php
    $currentPath = $_SERVER['PHP_SELF'];
    $userRole = getCurrentRole();

    // Navigation definition
    $allNavItems = [
      'dashboard.php' => [
        'icon' => 'dashboard',
        'label' => 'Dashboard',
        'roles' => ['head_admin', 'admin', 'manager']
      ],
      'user.php' => [
        'icon' => 'people',
        'label' => 'User',
        'roles' => ['head_admin']
      ],
      'address.php' => [
        'icon' => 'location_on',
        'label' => 'Address',
        'roles' => ['head_admin']
      ],
      'amountpaid.php' => [
        'icon' => 'checklist',
        'label' => 'Amount Paid',
        'roles' => ['head_admin']
      ],

      // ADMIN CONFIG PAGES
      'admin/installation-fee.php' => [
        'icon' => 'attach_money',
        'label' => 'Installation Fee',
        'roles' => ['head_admin']
      ],
      'admin/call_out_status.php' => [
        'icon' => 'call',
        'label' => 'Call Out Status',
        'roles' => ['head_admin']
      ],
      'admin/pull_out_remarks.php' => [
        'icon' => 'notes',
        'label' => 'Pull Out Remarks',
        'roles' => ['head_admin']
      ],
      'admin/status_input.php' => [
        'icon' => 'input',
        'label' => 'Status Input',
        'roles' => ['head_admin']
      ],
      'admin/sales_category.php' => [
        'icon' => 'category',
        'label' => 'Sales Category',
        'roles' => ['head_admin']
      ],
      'admin/main_remarks.php' => [
        'icon' => 'edit',
        'label' => 'Main Remarks',
        'roles' => ['head_admin']
      ],

      // ADMIN REPORTS
      'admin/monitoring.php' => [
        'icon' => 'monitor',
        'label' => 'Backend Monitoring',
        'roles' => ['admin']
      ],
      'admin/backend-productivity.php' => [
        'icon' => 'assessment',
        'label' => 'Backend Productivity',
        'roles' => ['admin']
      ],
      'admin/dormants.php' => [
        'icon' => 'person_off',
        'label' => 'Dormants',
        'roles' => ['admin']
      ],
      'admin/recallouts.php' => [
        'icon' => 'phone_callback',
        'label' => 'Recallouts',
        'roles' => ['admin']
      ],

      // ADMIN / MANAGER SHARED
      'admin/pull-out.php' => [
        'icon' => 'content_paste',
        'label' => 'Pull Out Report',
        'roles' => ['admin', 'manager']
      ],
      'admin/s25-report.php' => [
        'icon' => 'summarize',
        'label' => 'S25 Report',
        'roles' => ['admin', 'manager']
      ],
      'admin/daily-count.php' => [
        'icon' => 'today',
        'label' => 'Daily Count',
        'roles' => ['admin', 'manager']
      ],
      'admin/visit-remarks.php' => [
        'icon' => 'comment',
        'label' => 'Visit Remarks',
        'roles' => ['admin', 'manager']
      ],

      // COMMON
      'profile.php' => [
        'icon' => 'person',
        'label' => 'Profile',
        'roles' => ['head_admin', 'admin', 'manager']
      ],
      'logout.php' => [
        'icon' => 'logout',
        'label' => 'Logout',
        'roles' => ['head_admin', 'admin', 'manager']
      ],
    ];

    // Filter by role
    $navItems = array_filter($allNavItems, fn($item) =>
      in_array($userRole, $item['roles'])
    );
    ?>
    <aside class="sidebar">
      <div class="logo">
        <img src="<?php echo BASE_URL; ?>assets/image.png" alt="Paragon Logo">
      </div>
      <nav class="nav">
        <?php foreach ($navItems as $file => $item): ?>
          <?php
            $fullPath = BASE_URL . $file;
            $isActive = strpos($currentPath, $file) !== false;
          ?>
          <a href="<?php echo $fullPath; ?>" class="<?php echo $isActive ? 'active' : ''; ?>">
            <span class="material-icons"><?php echo $item['icon']; ?></span>
            <?php echo $item['label']; ?>
          </a>
        <?php endforeach; ?>
      </nav>
    </aside>
    
    <main class="main-content">
        
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
