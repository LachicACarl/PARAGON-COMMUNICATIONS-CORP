# Admin Page Conversion Template

## Pattern Applied to All Admin Pages:

### HEAD Section Changes:
```php
<?php
// Original includes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Add Navigation Setup
$currentPath = $_SERVER['PHP_SELF'];
$userRole = getCurrentRole();
$allNavItems = [...]; // Standard nav items
$navItems = array_filter($allNavItems, fn($item) => in_array($userRole, $item['roles']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>[Page Title]</title>
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
```

### BODY Section Structure:
```html
<body class="bg-gray-50">
<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay fixed inset-0 bg-black/30 hidden z-40" onclick="closeMobileSidebar()"></div>
<div class="flex min-h-screen">
  <!-- Sidebar (include full navigation) -->
  <aside class="sidebar fixed left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col p-6 overflow-y-auto md:relative md:w-64 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <!-- Logo, Nav items -->
  </aside>

  <!-- Main Content -->
  <main class="main flex-1 ml-0 md:ml-0 p-6 overflow-auto">
    <!-- PAGE HEADER -->
    <div class="header bg-white rounded-xl shadow-sm p-6 mb-8">
      <h1 class="m-0 text-2xl font-bold text-gray-900">[PAGE_TITLE]</h1>
      <p class="mt-1 text-sm text-gray-600">[PAGE_DESCRIPTION]</p>
    </div>

    <!-- PAGE CONTENT (Preserve existing PHP/HTML content) -->
    <div class="card bg-white rounded-xl shadow-sm p-6">
      <!-- ORIGINAL PAGE CONTENT HERE -->
    </div>
  </main>
</div>

<script>
  function toggleMobileSidebar() { ... }
  function closeMobileSidebar() { ... }
  // Mobile sidebar JS
</script>
</body>
</html>
```

## Pages to Convert (11):

1. **admin/backend-productivity.php**
   - Title: "Backend Productivity"
   - Description: "Track daily productivity metrics for backend team"

2. **admin/call_out_status.php**
   - Title: "Call Out Status"
   - Description: "Manage and track call-out statuses"

3. **admin/daily-count.php**
   - Title: "Daily Count Status"
   - Description: "Daily status count and distribution report"

4. **admin/dormants.php**
   - Title: "Dormants Per Area"
   - Description: "Track dormant accounts by geographical area"

5. **admin/installation-fee.php**
   - Title: "Installation Fee Management"
   - Description: "Manage installation fees for clients"

6. **admin/main_remarks.php**
   - Title: "Main Remarks Management"
   - Description: "Manage main remarks for client accounts"

7. **admin/monitoring.php**
   - Title: "Backend Monitoring"
   - Description: "Real-time monitoring of all client accounts"

8. **admin/pull-out.php**
   - Title: "Pull Out Report"
   - Description: "Track and monitor client pull-out activities"

9. **admin/pull_out_remarks.php**
   - Title: "Pull Out Remarks"
   - Description: "Manage pull-out remarks for clients"

10. **admin/recallouts.php**
    - Title: "Recallouts Remarks"
    - Description: "Track and manage recallout activities"

11. **admin/s25-report.php**
    - Title: "S25 Plan Report"
    - Description: "Track S25 plan subscriptions"

12-14. **admin/sales_category.php, admin/status_input.php, admin/visit-remarks.php**
    - Apply same pattern

## Key Points:
- All admin pages use relative path `../` for CSS: `../assets/tailwind-compat.css`
- All admin pages use `require_once __DIR__ . '/../config/...` for includes
- Preserve ALL existing PHP logic, database queries, and event handlers
- Only wrap content in new Tailwind dashboard layout
- Add navigation setup PHP code at the top of each file
- Update HEAD section with proper Tailwind CDN and Material Icons
