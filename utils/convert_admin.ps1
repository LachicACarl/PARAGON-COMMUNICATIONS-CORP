# PowerShell script to convert admin pages to Tailwind dashboard design
# This script processes all admin pages and applies the dashboard template

$adminDir = "c:\xampp\htdocs\paragon\PARAGON-COMMUNICATIONS-CORP\admin"
$adminPages = @(
    "monitoring.php",
    "backend-productivity.php",
    "dormants.php",
    "recallouts.php",
    "installation-fee.php",
    "call_out_status.php",
    "pull_out_remarks.php",
    "status_input.php",
    "sales_category.php",
    "main_remarks.php",
    "pull-out.php",
    "s25-report.php",
    "daily-count.php",
    "visit-remarks.php"
)

# Navigation items array
$navItemsCode = @'
  $currentPath = $_SERVER['PHP_SELF'];
  $userRole = getCurrentRole();
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
'@

foreach ($page in $adminPages) {
    $filepath = Join-Path $adminDir $page
    if (-Not (Test-Path $filepath)) {
        Write-Host "✗ File not found: $page"
        continue
    }

    try {
        $content = Get-Content $filepath -Raw -Encoding UTF8
        
        # Extract PHP header
        $phpMatch = $content | Select-String '(<\?php.*?\?>)' -AllMatches
        $phpHeader = if ($phpMatch.Matches) { $phpMatch.Matches[0].Value } else { "<?php ?>" }
        $restContent = $content.Substring($phpHeader.Length)
        
        # Extract title
        $titleMatch = $restContent | Select-String '<title>(.*?)</title>' -AllMatches
        $title = if ($titleMatch.Matches) { $titleMatch.Matches[0].Groups[1].Value } else { $page.Replace('.php', '').Replace('-', ' ') }
        
        # Build new content
        $newContent = @"
$phpHeader
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>$title - PARAGON</title>
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
  <?php
  $navItemsCode
  ?>
  <aside class="sidebar fixed left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col p-6 overflow-y-auto md:relative md:w-64 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <div class="logo mb-8">
      <img src="<?php echo BASE_URL; ?>assets/image.png" alt="Paragon Logo" class="w-32 rounded-lg">
    </div>
    <nav class="nav flex-1 space-y-2">
      <?php foreach (\$navItems as \$file => \$item): ?>
        <?php \$isActive = strpos(\$currentPath, \$file) !== false; ?>
        <a href="<?php echo BASE_URL . \$file; ?>" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo \$isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
          <span class="material-icons text-lg"><?php echo \$item['icon']; ?></span>
          <span class="text-sm font-medium"><?php echo \$item['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <main class="main flex-1 ml-0 md:ml-0 p-6 overflow-auto">
    <div class="header bg-white rounded-xl shadow-sm p-6 mb-8">
      <h1 class="m-0 text-2xl font-bold text-gray-900">$title</h1>
      <p class="mt-1 text-sm text-gray-600">Manage and monitor system data</p>
    </div>

    <div class="card bg-white rounded-xl shadow-sm p-6">
"@

        # Extract body content (between body tags)
        $bodyMatch = $restContent | Select-String '<body[^>]*>(.*?)</body>' -AllMatches
        if ($bodyMatch.Matches) {
            $bodyContent = $bodyMatch.Matches[0].Groups[1].Value
            # Remove nested scripts, sidebars, buttons, overlays
            $bodyContent = $bodyContent -replace '<script[^>]*>.*?</script>', '' -replace '<\s*aside\b.*?</aside>', '' -replace '<button[^>]*toggle[^>]*>.*?</button>', '' -replace '<div[^>]*mobile-overlay[^>]*></div>', '' -replace '<div[^>]*container[^>]*>', ''
            $newContent += $bodyContent
        }

        $newContent += @"

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
"@

        Set-Content -Path $filepath -Value $newContent -Encoding UTF8
        Write-Host "✓ Converted $page"
    } catch {
        Write-Host "✗ Error converting $page : $_"
    }
}

Write-Host "`n========================================"
Write-Host "Conversion complete!"
