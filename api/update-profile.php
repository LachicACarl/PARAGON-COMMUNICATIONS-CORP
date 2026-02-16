<?php
session_start();

require_once 'config/authenticate.php';
require_once 'config/database.php';
require_once 'config/helpers.php';

if (!isLoggedIn()) {
  http_response_code(401);
  echo json_encode(['success' => false, 'message' => 'Unauthorized']);
  exit;
}

$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if(!isset($data['first_name']) || empty($data['first_name'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'First name is required']);
  exit;
}

if(!isset($data['last_name']) || empty($data['last_name'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Last name is required']);
  exit;
}

if(!isset($data['email']) || empty($data['email'])) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Email is required']);
  exit;
}

// Validate email format
if(!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Invalid email format']);
  exit;
}

$userId = getCurrentUserId();
$first_name = sanitize($data['first_name']);
$last_name = sanitize($data['last_name']);
$email = sanitize($data['email']);

// Check if email is already used by another user
$existingEmail = getRow("SELECT id FROM users WHERE email = ? AND id != ?", [$email, $userId]);
if($existingEmail) {
  http_response_code(400);
  echo json_encode(['success' => false, 'message' => 'Email is already in use']);
  exit;
}

// Update user information
try {
  $stmt = $GLOBALS['pdo']->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
  $result = $stmt->execute([$first_name, $last_name, $email, $userId]);
  
  if($result) {
    logAction($userId, 'Updated profile information');
    echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
  } else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error updating profile']);
  }
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
