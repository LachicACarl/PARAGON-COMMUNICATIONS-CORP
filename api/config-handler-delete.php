<?php
// Generic DELETE handler for configuration tables
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

$script = $_SERVER['SCRIPT_NAME'];
$table = null;

$tables = ['call_out_status', 'pull_out_remarks', 'status_input_channel', 'sales_category', 'main_remarks'];

// Convert filenames with hyphens to underscores for table lookup
$filename = basename($script, '.php'); // e.g., "delete-call-out-status"
$tableName = str_replace('-', '_', $filename); // e.g., "delete_call_out_status"
$tableName = preg_replace('/^delete_/', '', $tableName); // e.g., "call_out_status"

foreach ($tables as $t) {
    if ($tableName === $t || strpos($script, "delete-$t") !== false) {
        $table = $t;
        break;
    }
}

if (!$table) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit;
}

try {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Valid ID is required']);
        exit;
    }

    $existing = getRow($pdo, "SELECT id FROM $table WHERE id = ?", [$id]);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Entry not found']);
        exit;
    }

    $stmt = $pdo->prepare("DELETE FROM $table WHERE id = ?");
    $stmt->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'Entry deleted successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Delete $table error: " . $e->getMessage());
}
?>
