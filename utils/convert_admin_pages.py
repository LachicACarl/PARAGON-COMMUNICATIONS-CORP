#!/usr/bin/env python3
"""
Batch converter for admin pages from legacy CSS to Tailwind dashboard design
Converts 11 admin pages to match the dashboard.php template pattern
"""

import os
import re
from pathlib import Path

# Admin pages to convert
ADMIN_PAGES = [
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
]

# Navigation items array (shared across all pages)
NAV_ITEMS = """  $currentPath = $_SERVER['PHP_SELF'];
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
    'profile.php' => [
      'icon' => 'person',
      'label' => 'Profile',
      'roles' => ['head_admin', 'admin', 'manager']
    ],
    'pending-approval.php' => [
      'icon' => 'schedule',
      'label' => 'Pending Approval',
      'roles' => ['head_admin']
    ],
    'approve-users.php' => [
      'icon' => 'done_all',
      'label' => 'Approve Users',
      'roles' => ['head_admin']
    ],
    'admin/backend-productivity.php' => [
      'icon' => 'trending_up',
      'label' => 'Backend Productivity',
      'roles' => ['admin']
    ],
    'admin/call_out_status.php' => [
      'icon' => 'phone',
      'label' => 'Call Out Status',
      'roles' => ['admin', 'manager']
    ],
    'admin/daily-count.php' => [
      'icon' => 'today',
      'label' => 'Daily Count',
      'roles' => ['admin', 'manager']
    ],
    'admin/dormants.php' => [
      'icon' => 'event_busy',
      'label' => 'Dormants',
      'roles' => ['admin']
    ],
    'admin/installation-fee.php' => [
      'icon' => 'attach_money',
      'label' => 'Installation Fee',
      'roles' => ['admin']
    ],
    'admin/main_remarks.php' => [
      'icon' => 'edit_note',
      'label' => 'Main Remarks',
      'roles' => ['admin']
    ],
    'admin/monitoring.php' => [
      'icon' => 'monitor',
      'label' => 'Monitoring',
      'roles' => ['admin']
    ],
    'admin/pull-out.php' => [
      'icon' => 'content_paste',
      'label' => 'Pull Out Report',
      'roles' => ['admin', 'manager']
    ],
    'admin/pull_out_remarks.php' => [
      'icon' => 'sticky_note_2',
      'label' => 'Pull Out Remarks',
      'roles' => ['admin']
    ],
    'admin/recallouts.php' => [
      'icon' => 'phone_callback',
      'label' => 'Recallouts',
      'roles' => ['admin']
    ],
    'admin/s25-report.php' => [
      'icon' => 'summarize',
      'label' => 'S25 Report',
      'roles' => ['admin', 'manager']
    ],
    'admin/sales_category.php' => [
      'icon' => 'sell',
      'label' => 'Sales Category',
      'roles' => ['admin']
    ],
    'admin/status_input.php' => [
      'icon' => 'input',
      'label' => 'Status Input',
      'roles' => ['admin']
    ],
    'admin/visit-remarks.php' => [
      'icon' => 'comment',
      'label' => 'Visit Remarks',
      'roles' => ['admin', 'manager']
    ],
    'logout.php' => [
      'icon' => 'logout',
      'label' => 'Logout',
      'roles' => ['head_admin', 'admin', 'manager']
    ]
  ];

  // Filter by role
  $navItems = array_filter($allNavItems, fn($item) =>
    in_array($userRole, $item['roles'])
  );
  ?>"""

SIDEBAR_HTML = """  <aside class="sidebar fixed left-0 top-0 w-64 h-screen bg-gray-900 text-white flex flex-col p-6 overflow-y-auto md:relative md:w-64 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300">
    <div class="logo mb-8">
      <img src="<?php echo BASE_URL; ?>assets/image.png" alt="Paragon Logo" class="w-32 rounded-lg">
    </div>
    <nav class="nav flex-1 space-y-2">
      <?php foreach ($navItems as $file => $item): ?>
        <?php
          $fullPath = BASE_URL . $file;
          $isActive = strpos($currentPath, $file) !== false;
        ?>
        <a href="<?php echo $fullPath; ?>" class="nav-item flex items-center gap-3 px-4 py-3 rounded-lg transition-all <?php echo $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-800'; ?>">
          <span class="material-icons text-lg"><?php echo $item['icon']; ?></span>
          <span class="text-sm font-medium"><?php echo $item['label']; ?></span>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>"""

HEAD_SECTION_NEW = """  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
  </style>"""

JS_FOOTER = """
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
</script>"""

def convert_admin_page(filepath, page_name):
    """Convert a single admin page to dashboard design"""
    try:
        with open(filepath, 'r', encoding='utf-8') as f:
            content = f.read()
        
        # Extract PHP header (before <!DOCTYPE)
        php_match = re.match(r'(<\?php.*?\?>\s*)', content, re.DOTALL)
        php_header = php_match.group(1) if php_match else '<?php ?>'
        rest = content[len(php_header):]
        
        # Extract title from existing <title> tag or use page name
        title_match = re.search(r'<title>(.*?)</title>', rest, re.IGNORECASE)
        title = title_match.group(1) if title_match else page_name.replace('-', ' ').title()
        
        # Build new HTML structure
        new_content = php_header + '\n'
        new_content += '<!DOCTYPE html>\n'
        new_content += '<html lang="en">\n'
        new_content += '<head>\n'
        new_content += HEAD_SECTION_NEW + '\n'
        new_content += '</head>\n'
        new_content += '<body class="bg-gray-50">\n'
        new_content += '<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleMobileSidebar()">\n'
        new_content += '  <span class="material-icons">menu</span>\n'
        new_content += '</button>\n'
        new_content += '<div class="mobile-overlay fixed inset-0 bg-black/30 hidden z-40" onclick="closeMobileSidebar()"></div>\n'
        new_content += '<div class="flex min-h-screen">\n'
        new_content += NAV_ITEMS + '\n'
        new_content += SIDEBAR_HTML + '\n'
        new_content += '\n  <!-- Main Content -->\n'
        new_content += '  <main class="main flex-1 ml-0 md:ml-0 p-6 overflow-auto">\n'
        new_content += '    <!-- PAGE HEADER -->\n'
        new_content += '    <div class="header bg-white rounded-xl shadow-sm p-6 mb-8">\n'
        new_content += f'      <h1 class="m-0 text-2xl font-bold text-gray-900">{title}</h1>\n'
        new_content += '      <p class="mt-1 text-sm text-gray-600">Manage and monitor system data</p>\n'
        new_content += '    </div>\n'
        new_content += '\n    <!-- PAGE CONTENT -->\n'
        new_content += '    <div class="card bg-white rounded-xl shadow-sm p-6">\n'
        
        # Extract existing body content (between <body> and </body>)
        body_match = re.search(r'<body[^>]*>(.*?)</body>', rest, re.DOTALL | re.IGNORECASE)
        if body_match:
            body_content = body_match.group(1)
            # Remove old header/footer elements, keep main content
            body_content = re.sub(r'<script[^>]*>.*?</script>', '', body_content, flags=re.DOTALL | re.IGNORECASE)
            body_content = re.sub(r'<.*?sidebar.*?</aside>', '', body_content, flags=re.DOTALL | re.IGNORECASE)
            body_content = re.sub(r'<button.*?toggleMobile.*?</button>', '', body_content, flags=re.DOTALL | re.IGNORECASE)
            body_content = re.sub(r'<div.*?mobile-overlay.*?</div>', '', body_content, flags=re.DOTALL | re.IGNORECASE)
            body_content = re.sub(r'<div.*?container.*?>', '', body_content, flags=re.IGNORECASE)
            # Clean up content
            body_content = body_content.strip()
            new_content += body_content + '\n'
        
        new_content += '    </div>\n'
        new_content += '  </main>\n'
        new_content += '</div>\n'
        new_content += JS_FOOTER + '\n'
        new_content += '</body>\n'
        new_content += '</html>\n'
        
        # Write back
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        
        return True, f"Converted {page_name}"
    except Exception as e:
        return False, f"Error converting {page_name}: {str(e)}"

def main():
    admin_dir = Path(__file__).parent / 'admin'
    
    results = []
    for page in ADMIN_PAGES:
        page_path = admin_dir / page
        if page_path.exists():
            success, message = convert_admin_page(str(page_path), page)
            results.append(message)
            print(f"{'✓' if success else '✗'} {message}")
        else:
            results.append(f"File not found: {page}")
            print(f"✗ File not found: {page}")
    
    print(f"\n{'='*60}")
    print(f"Conversion complete! {sum(1 for r in results if 'Converted' in r)}/{len(ADMIN_PAGES)} pages converted")

if __name__ == '__main__':
    main()
