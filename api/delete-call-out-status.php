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

$id = intval($_POST['id'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

try {
    $result = delete('call_out_status', $id);

    if ($result) {
        logAction(getCurrentUserId(), 'DELETE', 'call_out_status', $id, "Deleted call out status ID: $id");
        echo json_encode(['success' => true, 'message' => 'Call out status deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete call out status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
