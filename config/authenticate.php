<?php
/**
 * PARAGON COMMUNICATIONS - User Authentication Handler
 * Handles email/password login with role-based access control
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../login.php");
    exit();
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($email) || empty($password)) {
    $_SESSION['error'] = "Email and password are required.";
    header("Location: ../login.php");
    exit();
}

try {
    // Get user by email
    $user = getRow($pdo, "SELECT * FROM users WHERE email = ?", [$email]);
    
    if (!$user) {
        // In development mode, create user if doesn't exist
        if (ENV === 'development') {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $userId = insert($pdo, 'users', [
                'email' => $email,
                'password' => $hashedPassword,
                'first_name' => explode('@', $email)[0],
                'last_name' => 'User',
                'role' => 'user',
                'status' => 'active',
                'email_verified' => 1
            ]);
            
            $user = getRow($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../login.php");
            exit();
        }
    }
    
    // Verify password
    if (!password_verify($password, $user['password'] ?? '')) {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../login.php");
        exit();
    }
    
    // Check user status
    if ($user['status'] === 'inactive') {
        $_SESSION['error'] = "Your account is pending approval. Please wait for Head Admin confirmation.";
        header("Location: ../login.php");
        exit();
    }
    
    if ($user['status'] === 'suspended') {
        $_SESSION['error'] = "Your account has been suspended. Please contact support.";
        header("Location: ../login.php");
        exit();
    }
    
    // Update last login
    update($pdo, 'users', 
           ['last_login' => date('Y-m-d H:i:s')],
           ['id' => $user['id']]);
    
    // Set session variables
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['first_name'] = $user['first_name'];
    $_SESSION['last_name'] = $user['last_name'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['logged_in'] = true;
    $_SESSION['username'] = trim($user['first_name'] . ' ' . $user['last_name']);
    
    // Redirect to dashboard
    header("Location: ../dashboard.php");
    exit();
    
} catch (Exception $e) {
    error_log("Authentication Error: " . $e->getMessage());
    $_SESSION['error'] = "An error occurred. Please try again.";
    header("Location: ../login.php");
    exit();
}

?>
