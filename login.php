<?php
/**
 * PARAGON COMMUNICATIONS - Login Page with Google OAuth
 */

session_start();
require_once __DIR__ . '/config/config.php';

// If already logged in, redirect to dashboard
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

// Build Google OAuth URL
$googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
    'client_id' => GOOGLE_CLIENT_ID,
    'redirect_uri' => GOOGLE_REDIRECT_URI,
    'response_type' => 'code',
    'scope' => 'openid email profile',
    'access_type' => 'offline',
    'state' => bin2hex(random_bytes(16))
]);

$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PARAGON Communications - Login</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #ffffff;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        
        .login-box {
            background: #186bb2;
            padding: 60px 40px;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            text-align: center;
        }
        
        .logo {
            width: 150px;
            height: 46px;
            margin-bottom: 20px;
            object-fit: contain;
        }
        
        .login-box h1 {
            color: #ffffff;
            margin-bottom: 15px;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: 1px;
        }
        
        .login-box p {
            color: #e8f0f7;
            margin-bottom: 35px;
            font-size: 15px;
            line-height: 1.6;
        }
        
        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        
        .google-btn {
            width: 100%;
            padding: 14px;
            background: white;
            color: #333;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        
        .google-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
        
        .google-icon {
            width: 20px;
            height: 20px;
        }
        
        .admin-login {
            margin-bottom: 25px;
        }
        
        .admin-login a {
            color: #4EE9FF;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
            border-bottom: 1px solid rgba(168, 212, 247, 0.5);
            padding-bottom: 2px;
        }
        
        .admin-login a:hover {
            color: #ffffff;
            border-bottom-color: #ffffff;
        }
        
        .signup-section {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 25px;
        }
        
        .signup-section p {
            color: #e8f0f7;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .signup-section a {
            color: #a8d4f7;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }
        
        .signup-section a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <!-- LOGO -->
            <img src="assets/image.png" class="logo" alt="Paragon Logo">
            
            <h1>Login</h1>
            <p>Use your Google account to continue. If you don't have access yet, request it on Sign up.</p>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Google OAuth Login Button -->
            <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" style="text-decoration: none;">
                <button class="google-btn" type="button">
                    <svg class="google-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.49 12.27c0-.81-.07-1.59-.2-2.34H12v4.43h6.44a5.5 5.5 0 0 1-2.39 3.62v3h3.87c2.26-2.08 3.57-5.15 3.57-8.71z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.9l-3.87-3c-1.07.72-2.45 1.15-4.06 1.15-3.12 0-5.76-2.1-6.7-4.94H1.3v3.1A12 12 0 0 0 12 24z"/>
                        <path fill="#FBBC05" d="M5.3 14.31A7.2 7.2 0 0 1 4.92 12c0-.8.14-1.57.38-2.31v-3.1H1.3A12 12 0 0 0 0 12c0 1.94.46 3.77 1.3 5.41l4-3.1z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.36.61 4.62 1.8l3.46-3.46C17.95.86 15.24 0 12 0A12 12 0 0 0 1.3 6.59l4 3.1C6.24 6.85 8.88 4.75 12 4.75z"/>
                    </svg>
                    Continue with Google
                </button>
            </a>
            
            <!-- Admin Login Link -->
            <div class="admin-login">
                <a href="admin-login.php">Login as Head Admin</a>
            </div>
            
            <!-- Sign Up Section -->
            <div class="signup-section">
                <p>Need Access? <a href="register.php">Sign Up</a></p>
                <p style="font-size: 12px; margin-top: 5px;">You may be asked to choose an account.</p>
            </div>
        </div>
    </div>
</body>
</html>
