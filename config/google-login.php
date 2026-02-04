<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Simulate successful Google login
$_SESSION['logged_in'] = true;
$_SESSION['username'] = "Head Admin";
$_SESSION['email'] = "admin@paragon.com";

// Redirect to dashboard (go up one directory level)
header("Location: dashboard.php");
exit();
?>