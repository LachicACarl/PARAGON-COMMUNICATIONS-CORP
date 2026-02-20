<?php
/**
 * Main Entry Point
 * Redirects to the appropriate login page
 */

// Start session
session_start();

// Check if user is already logged in
if (isset($_SESSION['user_id'])) {
    // Redirect to dashboard if logged in
    header('Location: pages/dashboard.php');
    exit();
}

// Otherwise redirect to login
header('Location: auth/login.php');
exit();
