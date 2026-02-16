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

$status_name = sanitize($_POST['status_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if (empty($status_name)) {
    echo json_encode(['success' => false, 'message' => 'Status name is required']);
    exit;
}

try {
    $result = insert('call_out_status', [
        'status_name' => $status_name,
        'description' => $description
    ]);

    if ($result) {
        logAction(getCurrentUserId(), 'CREATE', 'call_out_status', $result, "Added call out status: $status_name");
        echo json_encode(['success' => true, 'message' => 'Call out status added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add call out status']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
