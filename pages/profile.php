<?php
session_start();

// Check if user is logged in
require_once 'config/authenticate.php';
require_once 'config/database.php';
require_once 'config/helpers.php';

if (!isLoggedIn()) {
  header("Location: login.php");
  exit;
}

// Get user details
$userId = getCurrentUserId();
$userDetails = getRow("SELECT * FROM users WHERE id = ?", [$userId]);
$userRole = getCurrentRole();
$initials = strtoupper(substr($userDetails['first_name'], 0, 1) . substr($userDetails['last_name'], 0, 1));

// Navigation Setup
$currentPath = $_SERVER['PHP_SELF'];
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
  <title>My Profile - Paragon Communications</title>
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
      <h1 class="m-0 text-2xl font-bold text-gray-900">My Profile</h1>
      <p class="mt-1 text-sm text-gray-600">Manage your account information and settings.</p>
    </div>

    <!-- PAGE CONTENT -->
    <div class="card bg-white rounded-xl shadow-sm p-6">
      <!-- Profile Header -->
      <div class="flex gap-6 mb-8 pb-8 border-b border-gray-200">
        <div class="w-24 h-24 bg-blue-600 text-white rounded-full flex items-center justify-center flex-shrink-0 text-3xl font-bold">
          <?php echo htmlspecialchars($initials); ?>
        </div>
        <div>
          <h2 class="m-0 text-xl font-semibold text-gray-900"><?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?></h2>
          <p class="mt-1 text-gray-600"><?php echo htmlspecialchars($userDetails['email']); ?></p>
          <span class="inline-block mt-3 px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
            <?php echo ucfirst(str_replace('_', ' ', $userDetails['role'])); ?>
          </span>
        </div>
      </div>

      <!-- Personal Information -->
      <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-3 border-b-2 border-blue-600 uppercase tracking-wide">Personal Information</h3>
        <div class="space-y-4">
          <div class="flex items-center py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-700 w-32">First Name:</span>
            <span class="text-gray-600"><?php echo htmlspecialchars($userDetails['first_name']); ?></span>
          </div>
          <div class="flex items-center py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-700 w-32">Last Name:</span>
            <span class="text-gray-600"><?php echo htmlspecialchars($userDetails['last_name']); ?></span>
          </div>
          <div class="flex items-center py-3">
            <span class="font-semibold text-gray-700 w-32">Email:</span>
            <span class="text-gray-600"><?php echo htmlspecialchars($userDetails['email']); ?></span>
          </div>
        </div>
      </div>

      <!-- Account Information -->
      <div class="mb-8">
        <h3 class="text-sm font-semibold text-gray-900 mb-4 pb-3 border-b-2 border-blue-600 uppercase tracking-wide">Account Information</h3>
        <div class="space-y-4">
          <div class="flex items-center py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-700 w-32">Role:</span>
            <span class="inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded-full">
              <?php echo ucfirst(str_replace('_', ' ', $userDetails['role'])); ?>
            </span>
          </div>
          <div class="flex items-center py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-700 w-32">Account ID:</span>
            <span class="text-gray-600"><?php echo htmlspecialchars($userDetails['id']); ?></span>
          </div>
          <div class="flex items-center py-3 border-b border-gray-100">
            <span class="font-semibold text-gray-700 w-32">Status:</span>
            <span class="inline-block px-3 py-1 bg-green-600 text-white text-xs font-semibold rounded-full">Active</span>
          </div>
          <div class="flex items-center py-3">
            <span class="font-semibold text-gray-700 w-32">Member Since:</span>
            <span class="text-gray-600"><?php echo date('F d, Y', strtotime($userDetails['created_at'])); ?></span>
          </div>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="flex gap-4 pt-8 border-t border-gray-200">
        <button class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors" onclick="openEditModal()">
          <span class="material-icons text-lg">edit</span> Edit Profile
        </button>
        <button class="flex-1 flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 text-gray-900 font-semibold rounded-lg hover:bg-gray-300 transition-colors" onclick="goToDashboard()">
          <span class="material-icons text-lg">dashboard</span> Back to Dashboard
        </button>
      </div>
    </div>
  </main>
</div>

<!-- Edit Profile Modal -->
<div class="modal fixed inset-0 bg-black/50 z-50 hidden justify-center items-center" id="editModal">
  <div class="modal-content bg-white rounded-xl p-8 w-full max-w-md max-h-screen overflow-y-auto relative">
    <button class="close-btn absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl" onclick="closeModal()">✕</button>
    <h3 class="mt-0 text-xl font-semibold text-gray-900 mb-6">Edit Profile</h3>
    
    <form onsubmit="saveProfile(event)">
      <div class="mb-5">
        <label class="block mb-2 font-semibold text-gray-700 text-sm">First Name</label>
        <input type="text" id="firstName" value="<?php echo htmlspecialchars($userDetails['first_name']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
      </div>

      <div class="mb-5">
        <label class="block mb-2 font-semibold text-gray-700 text-sm">Last Name</label>
        <input type="text" id="lastName" value="<?php echo htmlspecialchars($userDetails['last_name']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
      </div>

      <div class="mb-6">
        <label class="block mb-2 font-semibold text-gray-700 text-sm">Email</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($userDetails['email']); ?>" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
      </div>

      <div class="flex gap-3">
        <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition-colors">Save Changes</button>
        <button type="button" class="flex-1 px-6 py-2 bg-gray-200 text-gray-900 font-semibold rounded-lg hover:bg-gray-300 transition-colors" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEditModal() {
    document.getElementById('editModal').classList.remove('hidden');
    document.getElementById('editModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editModal').style.display = 'none';
  }

  function saveProfile(e) {
    e.preventDefault();
    const firstName = document.getElementById('firstName').value;
    const lastName = document.getElementById('lastName').value;
    const email = document.getElementById('email').value;

    fetch('api/update-profile.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        first_name: firstName,
        last_name: lastName,
        email: email
      })
    })
    .then(response => response.json())
    .then(data => {
      if(data.success) {
        alert('Profile updated successfully');
        closeModal();
        location.reload();
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error updating profile');
    });
  }

  function goToDashboard() {
    window.location.href = 'dashboard.php';
  }

  document.getElementById('editModal').addEventListener('click', function(e) {
    if(e.target === this) {
      closeModal();
    }
  });

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
