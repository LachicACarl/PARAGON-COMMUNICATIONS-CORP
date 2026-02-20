<?php
/**
 * BULK ADMIN PAGE CONVERTER
 * This script helps convert all remaining admin pages to the dashboard pattern
 * 
 * Usage: Run this in your workspace to identify needed conversions
 * Patterns are applied in the replace_string_in_file calls below
 */

$adminPagesToConvert = [
    'admin/backend-productivity.php' => 'Backend Productivity',
    'admin/call_out_status.php' => 'Call Out Status',
    'admin/daily-count.php' => 'Daily Count Status',
    'admin/dormants.php' => 'Dormants Per Area',
    'admin/installation-fee.php' => 'Installation Fee Management',
    'admin/main_remarks.php' => 'Main Remarks Management',
    'admin/monitoring.php' => 'Backend Monitoring',
    'admin/pull-out.php' => 'Pull Out Report',
    'admin/pull_out_remarks.php' => 'Pull Out Remarks',
    'admin/recallouts.php' => 'Recallouts Remarks',
    'admin/s25-report.php' => 'S25 Plan Report',
    'admin/sales_category.php' => 'Sales Category Management',
    'admin/status_input.php' => 'Status Input Management',
    'admin/visit-remarks.php' => 'Visit Remarks',
];

echo "ADMIN PAGE CONVERSION CHECKLIST\n";
echo "===============================\n\n";

foreach ($adminPagesToConvert as $file => $title) {
    echo "[ ] $file - $title\n";
}

echo "\n\nCONVERSION INSTRUCTIONS:\n";
echo "========================\n";
echo "1. For each admin page, replace the entire DOCTYPE...html structure\n";
echo "2. Keep all PHP logic at the top (requireLogin, data fetching, etc)\n";
echo "3. Add standard navigation setup code\n";
echo "4. Wrap body in dashboard sidebar + main content structure\n";  
echo "5. Use ../assets/tailwind-compat.css for paths (admin subfolder)\n";
echo "6. Update page title and description in header\n";
?>
