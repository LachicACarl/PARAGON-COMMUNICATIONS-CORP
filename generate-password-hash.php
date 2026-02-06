<?php
// Generate proper password hash for admin123
$password = 'admin123';
$hash = password_hash($password, PASSWORD_BCRYPT);

echo "Password: " . $password . "\n";
echo "Hash: " . $hash . "\n";
echo "\n";
echo "Copy this hash to the SQL file:\n";
echo $hash;
?>
