<?php
/**
 * PARAGON COMMUNICATIONS - Google OAuth Authentication Handler
 * Handles Google login and user registration/verification
 */

session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Check if authorization code is present
if (!isset($_GET['code'])) {
    header("Location: " . GOOGLE_REDIRECT_URI . "?error=no_code");
    exit();
}

$authCode = $_GET['code'];

try {
    // Exchange authorization code for access token
    $tokenUrl = 'https://oauth2.googleapis.com/token';
    
    $postData = [
        'code' => $authCode,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];
    
    $ch = curl_init($tokenUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        throw new Exception("Failed to get access token: " . $response);
    }
    
    $tokenData = json_decode($response, true);
    $accessToken = $tokenData['access_token'];
    
    // Get user info from Google
    $userInfoUrl = 'https://www.googleapis.com/oauth2/v2/userinfo?access_token=' . $accessToken;
    
    $ch = curl_init($userInfoUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken,
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    $response = curl_exec($ch);
    curl_close($ch);
    
    $userInfo = json_decode($response, true);
    
    if (!isset($userInfo['id']) || !isset($userInfo['email'])) {
        throw new Exception("Failed to get user information from Google");
    }
    
    // Check if user already exists
    $existingUser = getRow($pdo, "SELECT * FROM users WHERE google_id = ? OR email = ?", 
                          [$userInfo['id'], $userInfo['email']]);
    
    if ($existingUser) {
        // User exists - update last login and store OAuth token
        update($pdo, 'users', 
               ['last_login' => date('Y-m-d H:i:s')],
               ['id' => $existingUser['id']]);
        
        // Store/update OAuth session
        $existingSession = getRow($pdo, "SELECT id FROM oauth_sessions WHERE user_id = ?", 
                                 [$existingUser['id']]);
        
        if ($existingSession) {
            update($pdo, 'oauth_sessions',
                   ['access_token' => $accessToken, 
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'token_expires' => date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600))],
                   ['id' => $existingSession['id']]);
        } else {
            insert($pdo, 'oauth_sessions',
                   ['user_id' => $existingUser['id'],
                    'access_token' => $accessToken,
                    'refresh_token' => $tokenData['refresh_token'] ?? null,
                    'token_expires' => date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600))]);
        }
        
        // Set session variables
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['email'] = $existingUser['email'];
        $_SESSION['first_name'] = $existingUser['first_name'];
        $_SESSION['last_name'] = $existingUser['last_name'];
        $_SESSION['role'] = $existingUser['role'];
        $_SESSION['logged_in'] = true;
        $_SESSION['username'] = trim($existingUser['first_name'] . ' ' . $existingUser['last_name']);
        
        // Redirect to dashboard
        header("Location: " . APP_URL . "/dashboard.php");
        exit();
    }
    
    // New user - redirect to registration for verification
    $_SESSION['google_user_data'] = [
        'google_id' => $userInfo['id'],
        'email' => $userInfo['email'],
        'first_name' => $userInfo['given_name'] ?? '',
        'last_name' => $userInfo['family_name'] ?? '',
        'profile_picture' => $userInfo['picture'] ?? '',
        'access_token' => $accessToken,
        'refresh_token' => $tokenData['refresh_token'] ?? null,
        'token_expires' => date('Y-m-d H:i:s', time() + ($tokenData['expires_in'] ?? 3600))
    ];
    
    // Redirect to registration/confirmation page
    header("Location: " . APP_URL . "/register.php?step=verify-email");
    exit();
    
} catch (Exception $e) {
    error_log("Google OAuth Error: " . $e->getMessage());
    
    $_SESSION['error'] = "Authentication failed. Please try again.";
    header("Location: " . APP_URL . "/login.php");
    exit();
}

?>
