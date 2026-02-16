<?php
require_once '../config/authenticate.php';
require_once '../config/database.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$fee_name = sanitize($_POST['fee_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');
$amount = sanitize($_POST['amount'] ?? '');

if (empty($fee_name) || empty($amount)) {
    echo json_encode(['success' => false, 'message' => 'Fee name and amount are required']);
    exit;
}

try {
    $result = insert('installation_fee', [
        'fee_name' => $fee_name,
        'description' => $description,
        'amount' => $amount
    ]);

    if ($result) {
        logAction(getCurrentUserId(), 'CREATE', 'installation_fee', $result, "Added installation fee: $fee_name");
        echo json_encode(['success' => true, 'message' => 'Installation fee added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add installation fee']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
