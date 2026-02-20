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

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    // Check if fee exists
    $fee = getRow($pdo, "SELECT id FROM installation_fees WHERE id = ?", [$id]);
    if (!$fee) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Installation fee not found']);
        exit;
    }

    // Delete the fee
    $stmt = $pdo->prepare("DELETE FROM installation_fees WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Installation fee deleted successfully']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log('Delete installation fee error: ' . $e->getMessage());
}
