<?php
require_once '../config/database.php';
require_once '../config/helpers.php';

header('Content-Type: application/json');
session_start();

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

try {
    $fee_id = intval($_POST['fee_id'] ?? 0);
    $fee_name = trim($_POST['fee_name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount = floatval($_POST['amount'] ?? 0);

    if ($fee_id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid fee ID is required']);
        exit;
    }

    if (empty($fee_name) || $amount <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Fee name and amount are required']);
        exit;
    }

    // Check if fee exists
    $fee = getRow($pdo, "SELECT id FROM installation_fees WHERE id = ?", [$fee_id]);
    if (!$fee) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Installation fee not found']);
        exit;
    }

    // Check if new name is already taken by another fee
    $existingFee = getRow($pdo, "SELECT id FROM installation_fees WHERE fee_name = ? AND id != ?", [$fee_name, $fee_id]);
    if ($existingFee) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Installation fee with this name already exists']);
        exit;
    }

    // Update the fee
    $stmt = $pdo->prepare("UPDATE installation_fees SET fee_name = ?, amount = ?, description = ? WHERE id = ?");
    $stmt->execute([$fee_name, $amount, $description, $fee_id]);

    echo json_encode(['success' => true, 'message' => 'Installation fee updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log('Update installation fee error: ' . $e->getMessage());
}
?>
