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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        
        .login-box {
            background: white;
            padding: 50px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            text-align: center;
        }
        
        .logo {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
            object-fit: contain;
        }
        
        .login-box h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .login-box p {
            color: #666;
            margin-bottom: 30px;
            font-size: 14px;
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
            border: 2px solid #667eea;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .google-btn:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .google-icon {
            width: 20px;
            height: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <!-- LOGO -->
            <img src="assets/download.png" class="logo" alt="Paragon Logo">
            
            <h1>PARAGON</h1>
            <p>Management & Communications System</p>
            
            <?php if ($error): ?>
                <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Google OAuth Login Button -->
            <a href="<?php echo htmlspecialchars($googleAuthUrl); ?>" style="text-decoration: none;">
                <button class="google-btn">
                    <svg class="google-icon" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.834 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v 3.293c0 .319.192.694.801.576 4.765-1.559 8.179-6.086 8.179-11.384 0-6.627-5.373-12-12-12z"/>
                    </svg>
                    Sign in with Google
                </button>
            </a>
        </div>
    </div>
</body>
</html>
