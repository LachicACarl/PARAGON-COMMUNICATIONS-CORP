<?php
/**
 * PARAGON COMMUNICATIONS - User Registration & Email Verification
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Check if user has Google data
if (!isset($_SESSION['google_user_data'])) {
    header("Location: login.php");
    exit();
}

$googleData = $_SESSION['google_user_data'];
$step = $_GET['step'] ?? 'verify-email';
$message = '';
$error = '';

// Handle email verification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'send-verification') {
        // Send verification email
        $verificationToken = bin2hex(random_bytes(32));
        $tokenExpires = date('Y-m-d H:i:s', time() + 3600); // 1 hour
        
        $_SESSION['google_user_data']['verification_token'] = $verificationToken;
        $_SESSION['google_user_data']['token_expires'] = $tokenExpires;
        
        // TODO: Send email with verification link
        // sendVerificationEmail($googleData['email'], $verificationToken);
        
        $message = "Verification email sent to " . htmlspecialchars($googleData['email']);
        $step = 'confirm-token';
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'confirm-token') {
        // Verify token
        $inputToken = trim($_POST['verification_token'] ?? '');
        $storedToken = $_SESSION['google_user_data']['verification_token'] ?? '';
        $tokenExpires = $_SESSION['google_user_data']['token_expires'] ?? '';
        
        if (empty($inputToken)) {
            $error = "Please enter the verification code.";
        } elseif ($inputToken !== $storedToken) {
            $error = "Invalid verification code.";
        } elseif (strtotime($tokenExpires) < time()) {
            $error = "Verification code has expired.";
        } else {
            // Token is valid - proceed to role selection
            $_SESSION['google_user_data']['email_verified'] = true;
            $step = 'select-role';
            $message = "Email verified successfully!";
        }
    }
    
    elseif (isset($_POST['action']) && $_POST['action'] === 'select-role') {
        // User selected a role - create account
        $selectedRole = $_POST['role'] ?? 'user';
        $validRoles = ['user', 'admin', 'manager'];
        
        if (!in_array($selectedRole, $validRoles)) {
            $error = "Invalid role selected.";
        } else {
            try {
                // Check if user already exists
                $existing = getRow($pdo, "SELECT id FROM users WHERE email = ? OR google_id = ?",
                                 [$googleData['email'], $googleData['google_id']]);
                
                if ($existing) {
                    $error = "User with this email already exists.";
                } else {
                    // Determine initial status based on role
                    $status = ($selectedRole === 'user') ? 'active' : 'inactive'; // Admins need approval
                    
                    // Create user account
                    $userId = insert($pdo, 'users', [
                        'google_id' => $googleData['google_id'],
                        'email' => $googleData['email'],
                        'first_name' => $googleData['first_name'],
                        'last_name' => $googleData['last_name'],
                        'profile_picture' => $googleData['profile_picture'],
                        'role' => $selectedRole,
                        'status' => $status,
                        'email_verified' => 1
                    ]);
                    
                    // Store OAuth session
                    insert($pdo, 'oauth_sessions', [
                        'user_id' => $userId,
                        'access_token' => $googleData['access_token'],
                        'refresh_token' => $googleData['refresh_token'],
                        'token_expires' => $googleData['token_expires']
                    ]);
                    
                    // If admin or manager, create admin_accounts record for approval
                    if ($selectedRole !== 'user') {
                        insert($pdo, 'admin_accounts', [
                            'user_id' => $userId,
                            'approval_status' => 'pending'
                        ]);
                    }
                    
                    // Set session variables
                    $_SESSION['user_id'] = $userId;
                    $_SESSION['email'] = $googleData['email'];
                    $_SESSION['first_name'] = $googleData['first_name'];
                    $_SESSION['last_name'] = $googleData['last_name'];
                    $_SESSION['role'] = $selectedRole;
                    $_SESSION['logged_in'] = true;
                    $_SESSION['username'] = trim($googleData['first_name'] . ' ' . $googleData['last_name']);
                    
                    // Clear Google data from session
                    unset($_SESSION['google_user_data']);
                    
                    // Redirect to dashboard or approval page
                    if ($selectedRole === 'user') {
                        header("Location: dashboard.php");
                    } else {
                        header("Location: dashboard.php?pending-approval=true");
                    }
                    exit();
                }
            } catch (Exception $e) {
                error_log("Registration Error: " . $e->getMessage());
                $error = "Registration failed. Please try again.";
            }
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARAGON - Complete Registration</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .register-box {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }
        
        .register-box h2 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 5px;
            text-align: center;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .user-info p {
            margin: 5px 0;
            color: #555;
        }
        
        .role-options {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .role-option {
            display: flex;
            align-items: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .role-option:hover {
            border-color: #667eea;
            background: #f8f9ff;
        }
        
        .role-option input[type="radio"] {
            width: auto;
            margin-right: 15px;
            cursor: pointer;
        }
        
        .role-option label {
            margin: 0;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <h2>PARAGON - Complete Registration</h2>
            
            <?php if ($message): ?>
                <div class="message success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- User Info Display -->
            <div class="user-info">
                <p><strong>Email:</strong> <?php echo htmlspecialchars($googleData['email']); ?></p>
                <p><strong>Name:</strong> <?php echo htmlspecialchars($googleData['first_name'] . ' ' . $googleData['last_name']); ?></p>
            </div>
            
            <!-- Step 1: Verify Email -->
            <?php if ($step === 'verify-email'): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="send-verification">
                    <p style="text-align: center; color: #666; margin-bottom: 20px;">
                        We'll send a verification code to your email address.
                    </p>
                    <button type="submit" class="btn">Send Verification Code</button>
                </form>
            <?php endif; ?>
            
            <!-- Step 2: Confirm Token -->
            <?php if ($step === 'confirm-token'): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="confirm-token">
                    <div class="form-group">
                        <label for="verification_token">Verification Code</label>
                        <input type="text" id="verification_token" name="verification_token" 
                               placeholder="Enter the code from your email" required>
                    </div>
                    <button type="submit" class="btn">Verify Email</button>
                    <button type="button" class="btn" style="background: #6c757d; margin-top: 5px;" 
                            onclick="location.href='register.php'">Back</button>
                </form>
            <?php endif; ?>
            
            <!-- Step 3: Select Role -->
            <?php if ($step === 'select-role'): ?>
                <form method="POST">
                    <input type="hidden" name="action" value="select-role">
                    <p style="text-align: center; color: #666; margin-bottom: 20px;">
                        Select your role in the PARAGON system:
                    </p>
                    
                    <div class="role-options">
                        <label class="role-option">
                            <input type="radio" name="role" value="user" required>
                            <div>
                                <strong>Regular User</strong>
                                <p style="font-size: 12px; color: #999; margin: 0;">View assigned accounts and data</p>
                            </div>
                        </label>
                        
                        <label class="role-option">
                            <input type="radio" name="role" value="manager" required>
                            <div>
                                <strong>Manager</strong>
                                <p style="font-size: 12px; color: #999; margin: 0;">Manage accounts and generate reports (requires approval)</p>
                            </div>
                        </label>
                        
                        <label class="role-option">
                            <input type="radio" name="role" value="admin" required>
                            <div>
                                <strong>Administrator</strong>
                                <p style="font-size: 12px; color: #999; margin: 0;">Full system access (requires Head Admin approval)</p>
                            </div>
                        </label>
                    </div>
                    
                    <button type="submit" class="btn" style="margin-top: 30px;">Create Account</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
