<?php
/**
 * Create Head Admin with proper password hash
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Delete existing head admin
$pdo->exec("DELETE FROM users WHERE role = 'head_admin'");

// Create new head admin with proper password hash
$email = 'admin@paragon.com';
$password = 'admin123';
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$userId = insert($pdo, 'users', [
    'email' => $email,
    'first_name' => 'Head',
    'last_name' => 'Admin',
    'password' => $hashedPassword,
    'role' => 'head_admin',
    'status' => 'active',
    'email_verified' => 1,
    'created_at' => date('Y-m-d H:i:s')
]);

if ($userId) {
    echo "✅ Head Admin created successfully!\n\n";
    echo "Login Credentials:\n";
    echo "==================\n";
    echo "Email: " . $email . "\n";
    echo "Password: " . $password . "\n\n";
    echo "Login URL: http://localhost/paragon/PARAGON-COMMUNICATIONS-CORP/admin-login.php\n";
} else {
    echo "❌ Failed to create Head Admin\n";
}
?>
