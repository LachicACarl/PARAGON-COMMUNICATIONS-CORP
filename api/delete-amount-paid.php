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

// Validate required field
if(!isset($data['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing payment ID']);
    exit;
}

try {
    $paymentId = intval($data['id']);

    // Check if payment exists and get details
    $checkQuery = "SELECT client_name FROM client_accounts WHERE id = :id";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->execute([':id' => $paymentId]);
    $payment = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if(!$payment) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Payment not found']);
        exit;
    }

    // Delete the payment
    $query = "DELETE FROM client_accounts WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $paymentId]);

    // Log the action
    logAction('DELETE', 'client_accounts', $paymentId, "Deleted payment for {$payment['client_name']}");

    echo json_encode(['success' => true, 'message' => 'Payment deleted successfully']);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
