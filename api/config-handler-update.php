<?php
// Generic UPDATE handler for configuration tables
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

$tableConfigs = [
    'call_out_status' => ['name_field' => 'status_name', 'extra_fields' => ['color_code' => '#3b82f6']],
    'pull_out_remarks' => ['name_field' => 'remark_text', 'desc_field' => 'description', 'extra_fields' => []],
    'status_input_channel' => ['name_field' => 'channel_name', 'desc_field' => 'description', 'extra_fields' => []],
    'sales_category' => ['name_field' => 'category_name', 'desc_field' => 'description', 'extra_fields' => []],
    'main_remarks' => ['name_field' => 'remark_title', 'desc_field' => 'remark_description', 'extra_fields' => []]
];

$script = $_SERVER['SCRIPT_NAME'];
$table = null;
$config = null;

// Convert filenames with hyphens to underscores for table lookup
$filename = basename($script, '.php'); // e.g., "update-call-out-status"
$tableName = str_replace('-', '_', $filename); // e.g., "update_call_out_status"
$tableName = preg_replace('/^update_/', '', $tableName); // e.g., "call_out_status"

foreach ($tableConfigs as $t => $c) {
    if ($tableName === $t || strpos($script, "update-$t") !== false) {
        $table = $t;
        $config = $c;
        break;
    }
}

if (!$table || !$config) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit;
}

try {
    $nameField = $config['name_field'];
    $descField = $config['desc_field'] ?? 'description';
    
    // Accept both 'id' and 'remark_id' or 'status_id' etc. for backwards compatibility with forms
    $id = intval($_POST['id'] ?? $_POST['remark_id'] ?? $_POST['status_id'] ?? $_POST['channel_id'] ?? $_POST['category_id'] ?? 0);
    $name = trim($_POST[$nameField] ?? '');
    $description = trim($_POST[$descField] ?? '');

    if ($id <= 0 || empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID and name are required']);
        exit;
    }

    $existing = getRow($pdo, "SELECT id FROM $table WHERE id = ?", [$id]);
    if (!$existing) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Entry not found']);
        exit;
    }

    // Check if new name is already taken by another entry
    $nameTaken = getRow($pdo, "SELECT id FROM $table WHERE $nameField = ? AND id != ?", [$name, $id]);
    if ($nameTaken) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'This name is already taken']);
        exit;
    }

    $updateFields = [$nameField => $name, $descField => $description];
    
    foreach ($config['extra_fields'] as $field => $defaultValue) {
        $updateFields[$field] = trim($_POST[$field] ?? $defaultValue);
    }

    $setParts = [];
    $params = [];
    foreach ($updateFields as $field => $value) {
        $setParts[] = "$field = ?";
        $params[] = $value;
    }
    $params[] = $id;

    $query = "UPDATE $table SET " . implode(', ', $setParts) . " WHERE id = ?";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);

    echo json_encode(['success' => true, 'message' => 'Entry updated successfully']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Update $table error: " . $e->getMessage());
}
?>
