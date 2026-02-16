<?php
/**
 * Sidebar Navigation Component
 * Dynamically highlights the active page based on the current file
 */

// Get the current page filename
$currentPage = basename($_SERVER['PHP_SELF']);

// Define navigation items
$navItems = [
    'dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard'],
    'user.php' => ['icon' => 'people', 'label' => 'User'],
    'address.php' => ['icon' => 'location_on', 'label' => 'Address'],
    'amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid'],
    'admin/installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee'],
    'admin/call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status'],
    'admin/pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks'],
    'admin/status_input.php' => ['icon' => 'input', 'label' => 'Status Input'],
    'admin/sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category'],
    'admin/main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks'],
    'profile.php' => ['icon' => 'person', 'label' => 'Profile'],
    'logout.php' => ['icon' => 'logout', 'label' => 'Logout'],
];
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

    .sidebar .logo {
      margin-bottom: 30px;
    }

    .sidebar .logo img {
      max-width: 100%;
      height: auto;
    }

    .sidebar nav {
      display: flex;
      flex-direction: column;
      gap: 5px;
    }

    .sidebar nav a {
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 12px 15px;
      color: rgba(255, 255, 255, 0.8);
      text-decoration: none;
      border-radius: 5px;
      transition: all 0.3s ease;
      font-size: 14px;
    }

    .sidebar nav a:hover {
      background: rgba(255, 255, 255, 0.15);
      color: #fff;
      padding-left: 18px;
    }

    .sidebar nav a.active {
      background: rgba(255, 255, 255, 0.25);
      color: #fff;
      font-weight: bold;
      border-right: 4px solid #fff;
      padding-right: 11px;
    }

    .sidebar nav a .material-icons {
      font-size: 20px;
      flex-shrink: 0;
    }
</style>

<aside class="sidebar">
    <div class="logo">
        <img src="assets/image.png" alt="Paragon Logo">
    </div>

    <nav class="nav">
        <?php foreach($navItems as $file => $item): ?>
            <a href="<?php echo $file; ?>" class="<?php echo $currentPage === $file ? 'active' : ''; ?>">
                <span class="material-icons"><?php echo $item['icon']; ?></span>
                <?php echo $item['label']; ?>
            </a>
        <?php endforeach; ?>
    </nav>
</aside>
