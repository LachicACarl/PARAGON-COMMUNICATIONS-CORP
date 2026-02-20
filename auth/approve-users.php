<?php
/**
 * PARAGON COMMUNICATIONS - Head Admin: User Approval Management
 * Manage and approve pending user accounts
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Require Head Admin role
requireLogin();
requireRole(['Head Admin']);

$currentPath = $_SERVER['PHP_SELF'];
$userRole = getCurrentRole();
$username = getCurrentUserName();

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        update($pdo, 'users', 
               ['status' => 'active', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $_SESSION['user_id']], 
               ['id' => $userId]);
        
        insert($pdo, 'audit_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'approve_user',
            'description' => 'Approved user ID: ' . $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $_SESSION['success'] = "User account approved successfully!";
    } 
    elseif ($action === 'reject') {
        update($pdo, 'users', 
               ['status' => 'rejected'], 
               ['id' => $userId]);
        
        insert($pdo, 'audit_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'reject_user',
            'description' => 'Rejected user ID: ' . $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $_SESSION['success'] = "User account rejected.";
    }
    elseif ($action === 'change_role') {
        $newRole = $_POST['role'];
        update($pdo, 'users', 
               ['role' => $newRole], 
               ['id' => $userId]);
        
        $_SESSION['success'] = "User role updated to $newRole!";
    }
    
    header("Location: approve-users.php");
    exit();
}

$pendingUsers = getAll($pdo, "SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");
$activeUsers = getAll($pdo, "SELECT * FROM users WHERE status = 'active' ORDER BY created_at DESC");
$successMessage = $_SESSION['success'] ?? '';
unset($_SESSION['success']);

// Navigation Setup
$allNavItems = [
  'dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['head_admin', 'admin', 'manager']],
  'user.php' => ['icon' => 'people', 'label' => 'User', 'roles' => ['head_admin']],
  'address.php' => ['icon' => 'location_on', 'label' => 'Address', 'roles' => ['head_admin']],
  'amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid', 'roles' => ['head_admin']],
  'profile.php' => ['icon' => 'person', 'label' => 'Profile', 'roles' => ['head_admin', 'admin', 'manager']],
  'pending-approval.php' => ['icon' => 'schedule', 'label' => 'Pending Approval', 'roles' => ['head_admin']],
  'approve-users.php' => ['icon' => 'done_all', 'label' => 'Approve Users', 'roles' => ['head_admin']],
  'admin/backend-productivity.php' => ['icon' => 'trending_up', 'label' => 'Backend Productivity', 'roles' => ['admin']],
  'admin/call_out_status.php' => ['icon' => 'phone', 'label' => 'Call Out Status', 'roles' => ['admin', 'manager']],
  'admin/daily-count.php' => ['icon' => 'today', 'label' => 'Daily Count', 'roles' => ['admin', 'manager']],
  'admin/dormants.php' => ['icon' => 'event_busy', 'label' => 'Dormants', 'roles' => ['admin']],
  'admin/installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee', 'roles' => ['admin']],
  'admin/main_remarks.php' => ['icon' => 'edit_note', 'label' => 'Main Remarks', 'roles' => ['admin']],
  'admin/monitoring.php' => ['icon' => 'monitor', 'label' => 'Monitoring', 'roles' => ['admin']],
  'admin/pull-out.php' => ['icon' => 'content_paste', 'label' => 'Pull Out Report', 'roles' => ['admin', 'manager']],
  'admin/pull_out_remarks.php' => ['icon' => 'sticky_note_2', 'label' => 'Pull Out Remarks', 'roles' => ['admin']],
  'admin/recallouts.php' => ['icon' => 'phone_callback', 'label' => 'Recallouts', 'roles' => ['admin']],
  'admin/s25-report.php' => ['icon' => 'summarize', 'label' => 'S25 Report', 'roles' => ['admin', 'manager']],
  'admin/sales_category.php' => ['icon' => 'sell', 'label' => 'Sales Category', 'roles' => ['admin']],
  'admin/status_input.php' => ['icon' => 'input', 'label' => 'Status Input', 'roles' => ['admin']],
  'admin/visit-remarks.php' => ['icon' => 'comment', 'label' => 'Visit Remarks', 'roles' => ['admin', 'manager']],
  'logout.php' => ['icon' => 'logout', 'label' => 'Logout', 'roles' => ['head_admin', 'admin', 'manager']]
];
$navItems = array_filter($allNavItems, fn($item) => in_array($userRole, $item['roles']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users - PARAGON</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
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
    </style>
</head>
<body class="bg-gray-50">
<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay fixed inset-0 bg-black/30 hidden z-40" onclick="closeMobileSidebar()"></div>
<div class="flex min-h-screen">
  <!-- Sidebar -->
  <aside class="sidebar fixed left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col p-6 overflow-y-auto md:relative md:w-64 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <div class="logo mb-8">
      <img src="<?php echo BASE_URL; ?>assets/image.png" alt="Paragon Logo" class="w-32 rounded-lg">
    </div>
    <nav class="nav flex-1 space-y-2">
      <?php foreach ($navItems as $file => $item): ?>
        <?php $isActive = strpos($currentPath, $file) !== false; ?>
        <a href="<?php echo BASE_URL . $file; ?>" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
          <span class="material-icons text-lg"><?php echo $item['icon']; ?></span>
          <span class="text-sm font-medium"><?php echo $item['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="main flex-1 ml-0 md:ml-0 p-6 overflow-auto">
    <!-- PAGE HEADER -->
    <div class="header bg-white rounded-xl shadow-sm p-6 mb-8">
      <h1 class="m-0 text-2xl font-bold text-gray-900">Approve Users</h1>
      <p class="mt-1 text-sm text-gray-600">Manage and approve pending user accounts</p>
    </div>

    <!-- SUCCESS MESSAGE -->
    <?php if ($successMessage): ?>
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6">
      ✓ <?php echo htmlspecialchars($successMessage); ?>
    </div>
    <?php endif; ?>

    <!-- PAGE CONTENT -->
    <div class="space-y-6">
      <!-- Pending Users -->
      <div class="card bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Pending Approvals (<?php echo count($pendingUsers); ?>)</h2>
        
        <?php if (empty($pendingUsers)): ?>
        <div class="text-center py-12 text-gray-500">
          <p class="text-lg">No pending user approvals at this time.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">User</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Email</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Registered</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Status</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($pendingUsers as $user): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" class="w-10 h-10 rounded-full" alt="Profile">
                    <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                    <?php endif; ?>
                    <strong class="text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-6 py-4 text-gray-600"><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                <td class="px-6 py-4">
                  <span class="inline-block px-3 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded-full">PENDING</span>
                </td>
                <td class="px-6 py-4">
                  <form method="POST" class="flex gap-2">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <button type="submit" name="action" value="approve" class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">✓ Approve</button>
                    <button type="submit" name="action" value="reject" class="px-3 py-1 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition">✗ Reject</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>

      <!-- Active Users -->
      <div class="card bg-white rounded-xl shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Active Users (<?php echo count($activeUsers); ?>)</h2>
        
        <?php if (empty($activeUsers)): ?>
        <div class="text-center py-12 text-gray-500">
          <p class="text-lg">No active users yet.</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
          <table class="w-full text-sm">
            <thead class="bg-gray-100">
              <tr>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">User</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Email</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Current Role</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Last Login</th>
                <th class="px-6 py-3 text-left font-semibold text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <?php foreach ($activeUsers as $user): ?>
              <tr class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <div class="flex items-center gap-3">
                    <?php if ($user['profile_picture']): ?>
                    <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" class="w-10 h-10 rounded-full" alt="Profile">
                    <?php else: ?>
                    <div class="w-10 h-10 rounded-full bg-gray-300"></div>
                    <?php endif; ?>
                    <strong class="text-gray-900"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                  </div>
                </td>
                <td class="px-6 py-4 text-gray-600"><?php echo htmlspecialchars($user['email']); ?></td>
                <td class="px-6 py-4">
                  <span class="inline-block px-3 py-1 bg-blue-100 text-blue-800 text-xs font-semibold rounded-full"><?php echo strtoupper($user['role']); ?></span>
                </td>
                <td class="px-6 py-4 text-gray-600"><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                <td class="px-6 py-4">
                  <form method="POST" class="flex gap-2 items-center">
                    <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                    <select name="role" class="px-2 py-1 border border-gray-300 rounded text-xs">
                      <option value="User" <?php echo $user['role'] === 'User' ? 'selected' : ''; ?>>User</option>
                      <option value="Manager" <?php echo $user['role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                      <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                      <option value="Head Admin" <?php echo $user['role'] === 'Head Admin' ? 'selected' : ''; ?>>Head Admin</option>
                    </select>
                    <button type="submit" name="action" value="change_role" class="px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition">Update</button>
                  </form>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </main>
</div>

<script>
function toggleMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.mobile-overlay');
  sidebar.classList.toggle('-translate-x-full');
  overlay.classList.toggle('hidden');
}

function closeMobileSidebar() {
  const sidebar = document.querySelector('.sidebar');
  const overlay = document.querySelector('.mobile-overlay');
  sidebar.classList.add('-translate-x-full');
  overlay.classList.add('hidden');
}

document.querySelectorAll('.sidebar .nav a').forEach(link => {
  link.addEventListener('click', () => {
    if (window.innerWidth <= 768) closeMobileSidebar();
  });
});

window.addEventListener('resize', () => {
  if (window.innerWidth > 768) closeMobileSidebar();
});
</script>
</body>
</html>
