<?php
/**
 * PARAGON COMMUNICATIONS - Pending Approval Page
 * Shown to users whose accounts are awaiting Head Admin approval
 */

session_start();

$userEmail = $_SESSION['pending_user_email'] ?? 'your email';
$userName = $_SESSION['pending_user_name'] ?? 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Pending Approval - PARAGON</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/tailwind-compat.css">
    <style>
      .material-icons {
        font-family: 'Material Icons';
        font-weight: normal;
        font-style: normal;
        font-size: 24px;
        display: inline-block;
        line-height: 1;
        text-transform: none;
        letter-spacing: normal;
        word-wrap: normal;
        white-space: nowrap;
        direction: ltr;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
        -moz-osx-font-smoothing: grayscale;
        font-feature-settings: 'liga';
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
        
        .pending-container {
            background: white;
            padding: 50px 40px;
            border-radius: 15px;
            box-shadow: 0 10px 50px rgba(0,0,0,0.3);
            max-width: 500px;
            width: 100%;
            text-align: center;
        }
        
        .icon {
            width: 80px;
            height: 80px;
            background: #ffc107;
            border-radius: 50%;
            margin: 0 auto 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
        }
        
        h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 20px;
        }
        
        .message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .info-box {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: left;
        }
        
        .info-box label {
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            font-weight: 600;
            display: block;
            margin-bottom: 5px;
        }
        
        .info-box .value {
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }
        
        .back-btn {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: transform 0.2s;
        }
        
        .back-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            background: #ffc107;
            color: #333;
            border-radius: 20px;
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="pending-container">
        <div class="icon">⏳</div>
        
        <div class="status-badge">PENDING APPROVAL</div>
        
        <h1>Account Under Review</h1>
        
        <p class="message">
            Thank you for registering with PARAGON Communications! Your account has been created successfully 
            and is currently awaiting approval from the Head Administrator.
        </p>
        
        <div class="info-box">
            <label>Registered Email</label>
            <div class="value"><?php echo htmlspecialchars($userEmail); ?></div>
        </div>
        
        <div class="info-box">
            <label>Account Name</label>
            <div class="value"><?php echo htmlspecialchars($userName); ?></div>
        </div>
        
        <p class="message">
            <strong>What's Next?</strong><br>
            The Head Administrator will review your account and approve it shortly. 
            You will be able to access the system once your account is activated.
        </p>
        
        <a href="login.php" class="back-btn">Back to Login</a>
    </div>
</body>
</html>
