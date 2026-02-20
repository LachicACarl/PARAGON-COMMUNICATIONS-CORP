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
