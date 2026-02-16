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

$remark_name = sanitize($_POST['remark_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if (empty($remark_name)) {
    echo json_encode(['success' => false, 'message' => 'Remark name is required']);
    exit;
}

try {
    $result = insert('pull_out_remarks', [
        'remark_name' => $remark_name,
        'description' => $description
    ]);

    if ($result) {
        logAction(getCurrentUserId(), 'CREATE', 'pull_out_remarks', $result, "Added pull out remark: $remark_name");
        echo json_encode(['success' => true, 'message' => 'Pull out remark added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add pull out remark']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
