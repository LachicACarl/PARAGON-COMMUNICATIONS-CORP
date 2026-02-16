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

$category_name = sanitize($_POST['category_name'] ?? '');
$description = sanitize($_POST['description'] ?? '');

if (empty($category_name)) {
    echo json_encode(['success' => false, 'message' => 'Category name is required']);
    exit;
}

try {
    $result = insert('sales_category', [
        'category_name' => $category_name,
        'description' => $description
    ]);

    if ($result) {
        logAction(getCurrentUserId(), 'CREATE', 'sales_category', $result, "Added sales category: $category_name");
        echo json_encode(['success' => true, 'message' => 'Sales category added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add sales category']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
