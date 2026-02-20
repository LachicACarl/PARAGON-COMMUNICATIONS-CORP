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

// Configuration for different tables
$tables = [
    'call_out_status' => ['search_field' => 'status_name'],
    'pull_out_remarks' => ['search_field' => 'remark_text'],
    'status_input_channel' => ['search_field' => 'channel_name'],
    'sales_category' => ['search_field' => 'category_name'],
    'main_remarks' => ['search_field' => 'remark_title']
];

// Determine which table we're fetching from based on URL
$script = $_SERVER['SCRIPT_NAME'];
$table = null;

// Remove directory and extension, then convert hyphens to underscores
$filename = basename($script, '.php'); // e.g., "fetch-call-out-status"
$tableName = str_replace('-', '_', $filename); // e.g., "fetch_call_out_status"
$tableName = preg_replace('/^fetch_/', '', $tableName); // e.g., "call_out_status"

foreach ($tables as $t => $config) {
    if ($tableName === $t || strpos($script, $t) !== false) {
        $table = $t;
        $searchField = $config['search_field'];
        break;
    }
}

if (!$table) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid table']);
    exit;
}

try {
    $page = intval($_GET['page'] ?? 1);
    $search = trim($_GET['search'] ?? '');
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    $countQuery = "SELECT COUNT(*) as total FROM $table";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " WHERE $searchField LIKE ?";
        $params = ['%' . $search . '%'];
    }

    $totalResult = getRow($pdo, $countQuery, $params);
    $total = $totalResult['total'] ?? 0;

    $query = "SELECT * FROM $table";
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE $searchField LIKE ?";
        $params = ['%' . $search . '%'];
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $data = getAll($pdo, $query, $params);

    echo json_encode([
        'success' => true,
        'data' => $data,
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => ceil($total / $perPage)
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log("Fetch $table error: " . $e->getMessage());
}
?>
