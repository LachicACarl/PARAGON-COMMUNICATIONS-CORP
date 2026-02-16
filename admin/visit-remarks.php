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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
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
