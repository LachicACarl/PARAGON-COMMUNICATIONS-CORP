<?php
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in and is head admin
if(!isLoggedIn() || !isHeadAdmin()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if(!isset($data['client_name']) || !isset($data['amount_paid'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing required fields']);
    exit;
}

try {
    $clientName = sanitize($data['client_name']);
    $amountPaid = floatval($data['amount_paid']);
    $installationFee = floatval($data['installation_fee'] ?? 0);
    $userId = getCurrentUserId();

    // Insert into client_accounts or update existing
    $query = "INSERT INTO client_accounts (client_name, amount_paid, installation_fee, call_out_status, created_by, created_at)
              VALUES (:client_name, :amount_paid, :installation_fee, 'Active', :created_by, NOW())
              ON DUPLICATE KEY UPDATE amount_paid = VALUES(amount_paid), installation_fee = VALUES(installation_fee), updated_at = NOW()";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':client_name' => $clientName,
        ':amount_paid' => $amountPaid,
        ':installation_fee' => $installationFee,
        ':created_by' => $userId
    ]);

    // Log the action
    logAction('INSERT', 'client_accounts', null, "Added payment for $clientName");

    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Payment added successfully']);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
