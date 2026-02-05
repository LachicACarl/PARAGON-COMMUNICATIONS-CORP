<?php
/**
 * PARAGON COMMUNICATIONS - Head Admin Login
 */

session_start();
$error = $_SESSION['error'] ?? '';
unset($_SESSION['error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Head Admin Login - PARAGON</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f3f6fb;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-card {
            background: #1b6fb2;
            width: 100%;
            max-width: 520px;
            padding: 55px 40px 40px;
            border-radius: 22px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.25);
            text-align: center;
            color: #ffffff;
        }

        .logo {
            width: 150px;
            height: 46px;
            object-fit: contain;
            margin-bottom: 20px;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 25px;
            color: #ffffff;
        }

        .field {
            margin-bottom: 18px;
            text-align: left;
        }

        .field input {
            width: 100%;
            padding: 12px 14px;
            border-radius: 8px;
            border: 1px solid #dfe7ef;
            font-size: 14px;
            background: #ffffff;
        }

        .field input:focus {
            outline: none;
            border-color: #4EE9FF;
            box-shadow: 0 0 0 3px rgba(78, 233, 255, 0.25);
        }

        .password-wrap {
            position: relative;
        }

        .password-wrap svg {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            fill: #2c3e50;
            opacity: 0.7;
            pointer-events: none;
        }

        .forgot {
            display: block;
            text-align: right;
            font-size: 13px;
            color: #4EE9FF;
            text-decoration: none;
            margin-top: -8px;
            margin-bottom: 20px;
        }

        .forgot:hover {
            color: #ffffff;
        }

        .login-btn {
            width: 100%;
            padding: 12px 16px;
            background: #4EE9FF;
            color: #06364a;
            border: none;
            border-radius: 8px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
        }

        .signup {
            margin: 20px 0 10px;
            color: #e7f0f8;
            font-size: 14px;
        }

        .signup a {
            color: #4EE9FF;
            text-decoration: none;
            font-weight: 600;
        }

        .signup a:hover {
            color: #ffffff;
        }

        .divider {
            height: 1px;
            background: rgba(255, 255, 255, 0.2);
            margin: 20px 0;
        }

        .footer {
            font-size: 12px;
            color: #d6e6f5;
        }

        .error-message {
            background: #f8d7da;
            color: #721c24;
            padding: 10px 12px;
            border-radius: 8px;
            margin-bottom: 16px;
            border: 1px solid #f5c6cb;
            text-align: center;
            font-size: 13px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="assets/image.png" class="logo" alt="Paragon Logo">
        <h1>Head Admin Login</h1>

        <?php if ($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="config/authenticate.php">
            <div class="field">
                <input type="email" name="email" placeholder="Email Address" required>
            </div>
            <div class="field password-wrap">
                <input type="password" name="password" placeholder="Password" required>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7zm0 12a5 5 0 1 1 0-10 5 5 0 0 1 0 10zm0-8a3 3 0 1 0 0 6 3 3 0 0 0 0-6z"/>
                </svg>
            </div>
            <a href="#" class="forgot">Forgot Password?</a>
            <button type="submit" class="login-btn">Log In</button>
        </form>

        <div class="signup">Don’t have an account? <a href="register.php">Sign Up</a></div>
        <div class="divider"></div>
        <div class="footer">Powered By CONVERGE<br>© 2022 Paragon Communication Corp.</div>
    </div>
</body>
</html>
