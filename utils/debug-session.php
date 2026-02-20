<?php
session_start();
require_once 'config/helpers.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Session Debug</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #f5f5f5; }
        .debug-box { background: white; padding: 15px; border-radius: 5px; border: 1px solid #ddd; }
        .key { color: #0066cc; font-weight: bold; }
        .value { color: #009900; }
        h2 { color: #333; }
    </style>
</head>
<body>
    <h2>📋 Session Debug Information</h2>
    <div class="debug-box">
        <p><span class="key">Session ID:</span> <span class="value"><?php echo session_id(); ?></span></p>
        <hr>
        
        <p><span class="key">Logged In:</span> <span class="value"><?php echo isLoggedIn() ? 'YES ✓' : 'NO ✗'; ?></span></p>
        <p><span class="key">Current Role:</span> <span class="value"><?php echo getCurrentRole() ?: 'NOT SET'; ?></span></p>
        <p><span class="key">User Email:</span> <span class="value"><?php echo getCurrentUserEmail() ?: 'NOT SET'; ?></span></p>
        <p><span class="key">User Name:</span> <span class="value"><?php echo getCurrentUserName() ?: 'NOT SET'; ?></span></p>
        <hr>
        
        <p><span class="key">Is Head Admin:</span> <span class="value"><?php echo isHeadAdmin() ? 'YES ✓' : 'NO ✗'; ?></span></p>
        <p><span class="key">Is Admin:</span> <span class="value"><?php echo isAdmin() ? 'YES ✓' : 'NO ✗'; ?></span></p>
        <p><span class="key">Is Manager:</span> <span class="value"><?php echo isManager() ? 'YES ✓' : 'NO ✗'; ?></span></p>
        <hr>
        
        <h3>Full SESSION Array:</h3>
        <pre style="background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto;">
<?php print_r($_SESSION); ?>
        </pre>
    </div>
</body>
</html>
