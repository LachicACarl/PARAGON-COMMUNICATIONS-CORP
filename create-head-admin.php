<?php
/**
 * PARAGON COMMUNICATIONS - Create First Head Admin Account
 * Run this script ONCE to create your first Head Admin account
 * 
 * IMPORTANT: Delete this file after creating your Head Admin account!
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

// Check if Head Admin already exists
$existingHeadAdmin = getRow($pdo, "SELECT id FROM users WHERE role = 'Head Admin' LIMIT 1");

if ($existingHeadAdmin) {
    die("<h2>❌ Error: Head Admin account already exists!</h2><p>Please delete this file (create-head-admin.php) for security.</p>");
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $googleId = trim($_POST['google_id']);
    
    if (empty($email) || empty($firstName) || empty($lastName)) {
        $error = "All fields are required!";
    } else {
        // Create Head Admin account
        $userId = insert($pdo, 'users', [
            'google_id' => $googleId,
            'email' => $email,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'role' => 'Head Admin',
            'status' => 'active',
            'email_verified' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'approved_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($userId) {
            $success = true;
        } else {
            $error = "Failed to create Head Admin account.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Head Admin - PARAGON</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
        }
        
        h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .warning strong {
            color: #856404;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
        }
        
        input:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .error {
            background: #f44336;
            color: white;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .success {
            background: #4caf50;
            color: white;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        
        .success h2 {
            margin-bottom: 10px;
        }
        
        .success a {
            display: inline-block;
            margin-top: 15px;
            padding: 10px 20px;
            background: white;
            color: #4caf50;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
        }
        
        .help-text {
            font-size: 12px;
            color: #999;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($success) && $success): ?>
        <div class="success">
            <h2>✓ Head Admin Created Successfully!</h2>
            <p>Your Head Admin account has been created.</p>
            <a href="login.php">Go to Login</a>
            <p style="margin-top: 20px; font-size: 12px;">
                <strong>IMPORTANT:</strong> Delete the create-head-admin.php file now for security!
            </p>
        </div>
        <?php else: ?>
        <h1>Create Head Admin</h1>
        <p class="subtitle">Set up the first administrator account</p>
        
        <div class="warning">
            <strong>⚠️ Security Notice:</strong><br>
            This script should only be run ONCE to create the first Head Admin account. 
            Delete this file immediately after use!
        </div>
        
        <?php if (isset($error)): ?>
        <div class="error">❌ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" required placeholder="admin@paragon.com">
                <div class="help-text">Use your Google email address</div>
            </div>
            
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" required placeholder="John">
            </div>
            
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" required placeholder="Doe">
            </div>
            
            <div class="form-group">
                <label>Google ID (Optional)</label>
                <input type="text" name="google_id" placeholder="Leave blank if unknown">
                <div class="help-text">Can be added later after Google sign-in</div>
            </div>
            
            <button type="submit" class="btn">Create Head Admin Account</button>
        </form>
        <?php endif; ?>
    </div>
</body>
</html>
