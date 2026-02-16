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
    $result = delete('pull_out_remarks', $id);

    if ($result) {
        logAction(getCurrentUserId(), 'DELETE', 'pull_out_remarks', $id, "Deleted pull out remark ID: $id");
        echo json_encode(['success' => true, 'message' => 'Pull out remark deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete pull out remark']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
