<?php
/**
 * PARAGON COMMUNICATIONS - Environment Configuration
 * Create a .env file with your sensitive credentials
 */

// Load environment variables from .env file
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return false;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        
        list($key, $value) = explode('=', $line, 2);
        $key = trim($key);
        $value = trim($value);
        
        // Remove quotes if present
        if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }
        
        putenv("$key=$value");
        $_ENV[$key] = $value;
    }
    
    return true;
}

// Load .env file from config directory
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    loadEnv($envFile);
}

// Application environment
define('ENV', getenv('APP_ENV') ?: 'production');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/PARAGON-COMMUNICATIONS-CORP');
define('BASE_URL', '/PARAGON-COMMUNICATIONS-CORP/');
define('APP_NAME', 'PARAGON Communications');

// Database configuration (loaded from .env)
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'paragon_db');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');

// Google OAuth configuration (loaded from .env)
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');
define('GOOGLE_REDIRECT_URI', getenv('GOOGLE_REDIRECT_URI') ?: APP_URL . '/config/google-callback.php');

// Email configuration for verification
define('MAIL_HOST', getenv('MAIL_HOST') ?: 'smtp.gmail.com');
define('MAIL_PORT', getenv('MAIL_PORT') ?: 587);
define('MAIL_USERNAME', getenv('MAIL_USERNAME') ?: '');
define('MAIL_PASSWORD', getenv('MAIL_PASSWORD') ?: '');
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'noreply@paragon.com');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'PARAGON Communications');

// Session configuration
define('SESSION_LIFETIME', 86400); // 24 hours
define('SESSION_SECURE', getenv('SESSION_SECURE') ?: false);
define('SESSION_HTTPONLY', true);

// Security
define('JWT_SECRET', getenv('JWT_SECRET') ?: 'your-secret-key-here');
define('PASSWORD_MIN_LENGTH', 8);

?>
