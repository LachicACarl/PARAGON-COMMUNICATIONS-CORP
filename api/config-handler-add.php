<?php
// Generic ADD handler for configuration tables
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

// Map tables to their field structures
$tableConfigs = [
    'call_out_status' => ['name_field' => 'status_name', 'extra_fields' => ['color_code' => '#3b82f6']],
    'pull_out_remarks' => ['name_field' => 'remark_text', 'extra_fields' => []],
    'status_input_channel' => ['name_field' => 'channel_name', 'extra_fields' => []],
    'sales_category' => ['name_field' => 'category_name', 'extra_fields' => []],
    'main_remarks' => ['name_field' => 'remark_title', 'desc_field' => 'remark_description', 'extra_fields' => []]
];

$script = $_SERVER['SCRIPT_NAME'];
$table = null;
$config = null;

// Convert filenames with hyphens to underscores for table lookup
$filename = basename($script, '.php'); // e.g., "add-call-out-status"
$tableName = str_replace('-', '_', $filename); // e.g., "add_call_out_status"
$tableName = preg_replace('/^add_/', '', $tableName); // e.g., "call_out_status"

foreach ($tableConfigs as $t => $c) {
    if ($tableName === $t || strpos($script, "add-$t") !== false) {
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
    $name = trim($_POST[$nameField] ?? '');
    $description = trim($_POST[$descField] ?? '');
    
    if (empty($name)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => ucfirst($nameField) . ' is required']);
        exit;
    }

    // Check if already exists
    $existing = getRow($pdo, "SELECT id FROM $table WHERE $nameField = ?", [$name]);
    if ($existing) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'This entry already exists']);
        exit;
    }

    $insertFields = [$nameField => $name, $descField => $description];
    
    // Add extra fields
    foreach ($config['extra_fields'] as $field => $defaultValue) {
        $insertFields[$field] = trim($_POST[$field] ?? $defaultValue);
    }
    
    $insertFields['created_by'] = getCurrentUserId();

    $columns = implode(', ', array_keys($insertFields));
    $placeholders = implode(', ', array_fill(0, count($insertFields), '?'));
    $query = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    
    $stmt = $pdo->prepare($query);
    $stmt->execute(array_values($insertFields));
    $id = $pdo->lastInsertId();

    echo json_encode(['success' => true, 'message' => 'Entry added successfully', 'id' => $id]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Add $table error: " . $e->getMessage());
}
?>
