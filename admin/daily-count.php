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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/tailwind-compat.css">
</head>
<body class="bg-gray-100 font-['Segoe_UI']">
    <button class="md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg shadow-lg" onclick="toggleMobileSidebar()">
        <span class="material-icons">menu</span>
    </button>
    <div class="hidden md:hidden fixed inset-0 bg-black bg-opacity-50 z-40" id="mobile-overlay" onclick="closeMobileSidebar()"></div>
    <div class="max-w-7xl mx-auto p-5">
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
    
    <main class="flex-1">
        
        <div class="bg-white p-5 rounded-lg shadow mb-5">
            <h1 class="text-blue-600 text-2xl font-bold mb-1">📊 Daily Count Status</h1>
            <div class="text-gray-600 text-sm">Date: <?php echo date('F d, Y'); ?></div>
        </div>
        
        <div class="mb-8">
            <h2 class="text-gray-900 text-xl font-bold mb-4">Today's Updates</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-green-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Active Today</h3>
                    <p class="text-green-500 text-4xl font-bold"><?php echo $todayActive; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-orange-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Dormant Today</h3>
                    <p class="text-orange-500 text-4xl font-bold"><?php echo $todayDormant; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-red-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Inactive Today</h3>
                    <p class="text-red-500 text-4xl font-bold"><?php echo $todayInactive; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-blue-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Pending Today</h3>
                    <p class="text-blue-500 text-4xl font-bold"><?php echo $todayPending; ?></p>
                </div>
            </div>
        </div>
        
        <div class="mb-8">
            <h2 class="text-gray-900 text-xl font-bold mb-4">Overall Status Count</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-green-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Total Active</h3>
                    <p class="text-green-500 text-4xl font-bold"><?php echo $totalActive; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-orange-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Total Dormant</h3>
                    <p class="text-orange-500 text-4xl font-bold"><?php echo $totalDormant; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-red-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Total Inactive</h3>
                    <p class="text-red-500 text-4xl font-bold"><?php echo $totalInactive; ?></p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow text-center border-l-4 border-blue-500">
                    <h3 class="text-gray-600 text-xs uppercase mb-3">Total Pending</h3>
                    <p class="text-blue-500 text-4xl font-bold"><?php echo $totalPending; ?></p>
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
