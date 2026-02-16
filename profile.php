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
$initials = strtoupper(substr($userDetails['first_name'], 0, 1) . substr($userDetails['last_name'], 0, 1));
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile - Paragon Communications</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
  <style>
    /* Material Icons Font Family */
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
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f5f5f5;
    }

    .wrapper {
      display: flex;
    }

    .content {
      margin-left: 200px;
      flex: 1;
      padding: 30px;
    }

    .profile-container {
      max-width: 600px;
      background: #fff;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      padding: 30px;
    }

    .profile-header {
      display: flex;
      align-items: center;
      gap: 20px;
      margin-bottom: 30px;
      padding-bottom: 30px;
      border-bottom: 1px solid #ddd;
    }

    .avatar {
      width: 80px;
      height: 80px;
      background: #1976d2;
      color: #fff;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      font-weight: bold;
    }

    .profile-info h2 {
      margin: 0 0 5px 0;
      color: #333;
      font-size: 20px;
    }

    .profile-info p {
      margin: 5px 0;
      color: #666;
      font-size: 14px;
    }

    .profile-section {
      margin-bottom: 25px;
    }

    .profile-section h3 {
      color: #333;
      font-size: 14px;
      font-weight: bold;
      margin-bottom: 12px;
      text-transform: uppercase;
      border-bottom: 2px solid #1976d2;
      padding-bottom: 8px;
    }

    .profile-row {
      display: flex;
      align-items: center;
      padding: 12px 0;
      border-bottom: 1px solid #f0f0f0;
    }

    .profile-row:last-child {
      border-bottom: none;
    }

    .profile-label {
      color: #666;
      font-weight: 600;
      width: 120px;
      font-size: 13px;
    }

    .profile-value {
      color: #333;
      flex: 1;
      font-size: 14px;
    }

    .badge {
      display: inline-block;
      padding: 6px 12px;
      background: #1976d2;
      color: #fff;
      border-radius: 20px;
      font-size: 12px;
      font-weight: bold;
      text-transform: uppercase;
    }

    .badge.success {
      background: #4caf50;
    }

    .badge.warning {
      background: #ff9800;
    }

    .action-buttons {
      display: flex;
      gap: 10px;
      margin-top: 30px;
      padding-top: 30px;
      border-top: 1px solid #ddd;
    }

    button {
      flex: 1;
      padding: 12px 20px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      font-weight: bold;
      transition: all 0.3s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }

    .btn-primary {
      background: #1976d2;
      color: #fff;
    }

    .btn-primary:hover {
      background: #1565c0;
    }

    .btn-secondary {
      background: #f1f1f1;
      color: #333;
      border: 1px solid #ddd;
    }

    .btn-secondary:hover {
      background: #e8e8e8;
    }

    .modal {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.5);
      z-index: 1000;
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      width: 450px;
      max-width: 90%;
      position: relative;
    }

    .modal-content h3 {
      margin-top: 0;
      color: #333;
      margin-bottom: 20px;
    }

    .form-group {
      margin-bottom: 15px;
    }

    .form-group label {
      display: block;
      margin-bottom: 5px;
      font-weight: bold;
      color: #555;
      font-size: 13px;
    }

    .form-group input {
      width: 100%;
      padding: 10px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-sizing: border-box;
      font-size: 14px;
    }

    .form-group input:focus {
      outline: none;
      border-color: #1976d2;
      box-shadow: 0 0 5px rgba(25, 118, 210, 0.2);
    }

    .modal-buttons {
      display: flex;
      gap: 10px;
      margin-top: 20px;
    }

    .modal-buttons button {
      flex: 1;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: none;
      border: none;
      font-size: 24px;
      color: #888;
      cursor: pointer;
      padding: 0;
    }
  </style>
</head>
<body>

<button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
<div class="wrapper">

  <!-- ===== Sidebar ===== -->
  <?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  $userRole = getCurrentRole();
  
  // Define all navigation items with role restrictions
  $allNavItems = [
    'dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['head_admin', 'admin', 'manager']],
    'user.php' => ['icon' => 'people', 'label' => 'User', 'roles' => ['head_admin']],
    'address.php' => ['icon' => 'location_on', 'label' => 'Address', 'roles' => ['head_admin']],
    'amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid', 'roles' => ['head_admin']],
    'admin/installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee', 'roles' => ['head_admin']],
    'admin/call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status', 'roles' => ['head_admin']],
    'admin/pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks', 'roles' => ['head_admin']],
    'admin/status_input.php' => ['icon' => 'input', 'label' => 'Status Input', 'roles' => ['head_admin']],
    'admin/sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category', 'roles' => ['head_admin']],
    'admin/main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks', 'roles' => ['head_admin']],
    'admin/monitoring.php' => ['icon' => 'monitor', 'label' => 'Backend Monitoring', 'roles' => ['admin']],
    'admin/backend-productivity.php' => ['icon' => 'assessment', 'label' => 'Backend Productivity', 'roles' => ['admin']],
    'admin/dormants.php' => ['icon' => 'person_off', 'label' => 'Dormants', 'roles' => ['admin']],
    'admin/recallouts.php' => ['icon' => 'phone_callback', 'label' => 'Recallouts', 'roles' => ['admin']],
    'admin/pull-out.php' => ['icon' => 'content_paste', 'label' => 'Pull Out Report', 'roles' => ['admin', 'manager']],
    'admin/s25-report.php' => ['icon' => 'summarize', 'label' => 'S25 Report', 'roles' => ['admin', 'manager']],
    'admin/daily-count.php' => ['icon' => 'today', 'label' => 'Daily Count', 'roles' => ['admin', 'manager']],
    'admin/visit-remarks.php' => ['icon' => 'comment', 'label' => 'Visit Remarks', 'roles' => ['admin', 'manager']],
    'profile.php' => ['icon' => 'person', 'label' => 'Profile', 'roles' => ['head_admin', 'admin', 'manager']],
    'logout.php' => ['icon' => 'logout', 'label' => 'Logout', 'roles' => ['head_admin', 'admin', 'manager']],
  ];
  
  // Filter navigation items based on user role
  $navItems = array_filter($allNavItems, function($item) use ($userRole) {
    return in_array($userRole, $item['roles']);
  });
  ?>
  <style>
    .sidebar {
      width: 200px;
      background: linear-gradient(135deg, #1565c0, #1976d2);
      color: #fff;
      padding: 20px;
      position: fixed;
      height: 100vh;
      overflow-y: auto;
      z-index: 1000;
    }
    .sidebar .logo { margin-bottom: 30px; }
    .sidebar .logo img { max-width: 100%; height: auto; }
    .sidebar nav { display: flex; flex-direction: column; gap: 5px; }
    .sidebar nav a {
      display: flex; align-items: center; gap: 15px; padding: 12px 15px;
      color: rgba(255, 255, 255, 0.8); text-decoration: none; border-radius: 5px;
      transition: all 0.3s ease; font-size: 14px;
    }
    .sidebar nav a:hover { background: rgba(255, 255, 255, 0.15); color: #fff; padding-left: 18px; }
    .sidebar nav a.active { background: rgba(255, 255, 255, 0.25); color: #fff; font-weight: bold; border-right: 4px solid #fff; padding-right: 11px; }
    .sidebar nav a .material-icons { font-size: 20px; flex-shrink: 0; }
  </style>
  <aside class="sidebar">
    <div class="logo"><img src="assets/image.png" alt="Paragon Logo"></div>
    <nav class="nav">
      <?php foreach($navItems as $file => $item): ?>
        <a href="<?php echo $file; ?>" class="<?php echo $currentPage === $file ? 'active' : ''; ?>">
          <span class="material-icons"><?php echo $item['icon']; ?></span>
          <?php echo $item['label']; ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- ===== Main Content ===== -->
  <main class="content">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
      <div>
        <h1 style="margin: 0 0 5px 0; font-size: 26px; color: #333;">My Profile</h1>
        <p style="margin: 0; color: #666; font-size: 14px;">Manage your account information and settings.</p>
      </div>
    </div>

    <!-- Profile Container -->
    <div class="profile-container">

      <!-- Profile Header -->
      <div class="profile-header">
        <div class="avatar"><?php echo htmlspecialchars($initials); ?></div>
        <div class="profile-info">
          <h2><?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?></h2>
          <p><?php echo htmlspecialchars($userDetails['email']); ?></p>
          <span class="badge"><?php echo ucfirst(str_replace('_', ' ', $userDetails['role'])); ?></span>
        </div>
      </div>

      <!-- Personal Information -->
      <div class="profile-section">
        <h3>Personal Information</h3>
        <div class="profile-row">
          <span class="profile-label">First Name:</span>
          <span class="profile-value"><?php echo htmlspecialchars($userDetails['first_name']); ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-label">Last Name:</span>
          <span class="profile-value"><?php echo htmlspecialchars($userDetails['last_name']); ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-label">Email:</span>
          <span class="profile-value"><?php echo htmlspecialchars($userDetails['email']); ?></span>
        </div>
      </div>

      <!-- Account Information -->
      <div class="profile-section">
        <h3>Account Information</h3>
        <div class="profile-row">
          <span class="profile-label">Role:</span>
          <span class="profile-value">
            <span class="badge"><?php echo ucfirst(str_replace('_', ' ', $userDetails['role'])); ?></span>
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-label">Account ID:</span>
          <span class="profile-value"><?php echo htmlspecialchars($userDetails['id']); ?></span>
        </div>
        <div class="profile-row">
          <span class="profile-label">Status:</span>
          <span class="profile-value">
            <span class="badge success">Active</span>
          </span>
        </div>
        <div class="profile-row">
          <span class="profile-label">Member Since:</span>
          <span class="profile-value"><?php echo date('F d, Y', strtotime($userDetails['created_at'])); ?></span>
        </div>
      </div>

      <!-- Action Buttons -->
      <div class="action-buttons">
        <button class="btn-primary" onclick="openEditModal()">
          <span class="material-icons">edit</span> Edit Profile
        </button>
        <button class="btn-secondary" onclick="goToDashboard()">
          <span class="material-icons">dashboard</span> Back to Dashboard
        </button>
      </div>

    </div>

  </main>
</div>

<!-- ===== Edit Profile Modal ===== -->
<div class="modal" id="editModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">✕</button>
    <h3>Edit Profile</h3>
    
    <form onsubmit="saveProfile(event)">
      <div class="form-group">
        <label>First Name</label>
        <input type="text" id="firstName" value="<?php echo htmlspecialchars($userDetails['first_name']); ?>" required>
      </div>

      <div class="form-group">
        <label>Last Name</label>
        <input type="text" id="lastName" value="<?php echo htmlspecialchars($userDetails['last_name']); ?>" required>
      </div>

      <div class="form-group">
        <label>Email</label>
        <input type="email" id="email" value="<?php echo htmlspecialchars($userDetails['email']); ?>" required>
      </div>

      <div class="modal-buttons">
        <button type="submit" class="btn-primary">Save Changes</button>
        <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
  function openEditModal() {
    document.getElementById('editModal').style.display = 'flex';
  }

  function closeModal() {
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

  // Close modal when clicking outside
  document.getElementById('editModal').addEventListener('click', function(e) {
    if(e.target === this) {
      closeModal();
    }
  });
</script>

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
