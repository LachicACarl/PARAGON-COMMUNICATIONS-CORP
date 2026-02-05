<?php
/**
 * PARAGON COMMUNICATIONS - User Registration with Role Selection
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';

$error = '';
$selectedRole = $_POST['role'] ?? 'Admin';

// Handle role selection and Google OAuth redirect
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['role'])) {
    // Store selected role in session
    $_SESSION['selected_role'] = $_POST['role'];
    
    // Build Google OAuth URL with state
    $googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => GOOGLE_CLIENT_ID,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'response_type' => 'code',
        'scope' => 'openid email profile',
        'access_type' => 'offline',
        'state' => bin2hex(random_bytes(16))
    ]);
    
    // Redirect to Google OAuth
    header("Location: " . $googleAuthUrl);
    exit();
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
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f6fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100%;
        }
        
        .register-box {
            background: #1b6fb2;
            padding: 55px 40px;
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            width: 100%;
            max-width: 520px;
            text-align: center;
            color: #ffffff;
        }

        .logo {
            width: 150px;
            height: 46px;
            margin: 0 auto 20px;
            object-fit: contain;
            display: block;
        }
        
        .register-box h2 {
            text-align: center;
            color: #ffffff;
            margin-bottom: 10px;
            font-size: 28px;
            font-weight: 700;
        }

        .subtitle {
            color: #e7f0f8;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #e7f0f8;
            font-weight: 600;
            text-align: left;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px 14px;
            border: 1px solid #dfe7ef;
            border-radius: 8px;
            font-size: 14px;
            box-sizing: border-box;
            background: #ffffff;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #4EE9FF;
            box-shadow: 0 0 0 3px rgba(78, 233, 255, 0.25);
        }
        
        .primary-btn {
            width: 100%;
            padding: 12px 16px;
            background: #ffffff;
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
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
            margin-top: 20px;
        }

        .primary-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
        }

        .google-btn {
            margin-top: 15px;
        }
        
        .message {
            padding: 12px;
            margin-bottom: 20px;
            border-radius: 8px;
            text-align: center;
            font-size: 14px;
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
            background: rgba(255, 255, 255, 0.12);
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: left;
        }
        
        .user-info p {
            margin: 4px 0;
            color: #e7f0f8;
            font-size: 13px;
        }

        .google-icon {
            width: 20px;
            height: 20px;
        }

        .signin-link {
            margin-top: 22px;
            color: #e7f0f8;
            font-size: 14px;
        }

        .signin-link a {
            color: #4EE9FF;
            text-decoration: none;
            font-weight: 600;
        }

        .signin-link a:hover {
            color: #ffffff;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-box">
            <img src="assets/image.png" class="logo" alt="Paragon Logo">
            <h2>Request Access</h2>
            <p class="subtitle">Choose your role and continue with Google. An admin will approve your access.</p>
            
            <?php if ($error): ?>
                <div class="message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <!-- Role Selection Form -->
            <form method="POST">
                <div class="form-group">
                    <label for="role" style="text-align: center; margin-bottom: 12px;">Request Access as</label>
                    <select id="role" name="role" required>
                        <option value="Admin">Admin</option>
                        <option value="Manager">Manager</option>
                    </select>
                </div>
                
                <button type="submit" class="primary-btn google-btn">
                    <svg class="google-icon" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M23.49 12.27c0-.81-.07-1.59-.2-2.34H12v4.43h6.44a5.5 5.5 0 0 1-2.39 3.62v3h3.87c2.26-2.08 3.57-5.15 3.57-8.71z"/>
                        <path fill="#34A853" d="M12 24c3.24 0 5.95-1.07 7.93-2.9l-3.87-3c-1.07.72-2.45 1.15-4.06 1.15-3.12 0-5.76-2.1-6.7-4.94H1.3v3.1A12 12 0 0 0 12 24z"/>
                        <path fill="#FBBC05" d="M5.3 14.31A7.2 7.2 0 0 1 4.92 12c0-.8.14-1.57.38-2.31v-3.1H1.3A12 12 0 0 0 0 12c0 1.94.46 3.77 1.3 5.41l4-3.1z"/>
                        <path fill="#EA4335" d="M12 4.75c1.77 0 3.36.61 4.62 1.8l3.46-3.46C17.95.86 15.24 0 12 0A12 12 0 0 0 1.3 6.59l4 3.1C6.24 6.85 8.88 4.75 12 4.75z"/>
                    </svg>
                    Continue with Google
                </button>
            </form>

            <div class="signin-link">Already have access? <a href="login.php">Log In</a></div>
        </div>
    </div>
</body>
</html>
