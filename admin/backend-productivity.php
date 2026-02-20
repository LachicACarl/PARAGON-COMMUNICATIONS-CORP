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
            <h1 class="text-blue-600 text-2xl font-bold mb-1">📈 Backend Daily Productivity</h1>
            <div class="text-gray-600 text-sm">Date: <?php echo date('F d, Y'); ?></div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-8">
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-green-500">
                <h3 class="text-gray-600 text-sm uppercase mb-3">New Clients Today</h3>
                <p class="text-gray-900 text-4xl font-bold"><?php echo $todayClients; ?></p>
            </div>
            <div class="bg-white p-6 rounded-lg shadow border-l-4 border-blue-500">
                <h3 class="text-gray-600 text-sm uppercase mb-3">Updates Today</h3>
                <p class="text-gray-900 text-4xl font-bold"><?php echo $todayUpdates; ?></p>
            </div>
        </div>
        
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Team Productivity</h2>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="px-3 py-3 text-left text-gray-700 font-semibold border-b-2 border-gray-300">Name</th>
                            <th class="px-3 py-3 text-left text-gray-700 font-semibold border-b-2 border-gray-300">Role</th>
                            <th class="px-3 py-3 text-left text-gray-700 font-semibold border-b-2 border-gray-300">Clients Created Today</th>
                            <th class="px-3 py-3 text-left text-gray-700 font-semibold border-b-2 border-gray-300">Clients Updated Today</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productivity as $user): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-3 py-3 text-gray-800 border-b border-gray-200"><strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong></td>
                                <td class="px-3 py-3 text-gray-600 border-b border-gray-200">
                                    <span class="inline-block px-2 py-1 rounded text-xs font-semibold uppercase bg-blue-500 text-white">
                                        <?php echo htmlspecialchars(ucfirst($user['role'])); ?>
                                    </span>
                                </td>
                                <td class="px-3 py-3 text-gray-600 border-b border-gray-200"><?php echo $user['created_today']; ?></td>
                                <td class="px-3 py-3 text-gray-600 border-b border-gray-200"><?php echo $user['updated_today']; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
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
