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

try {
    $page = intval($_GET['page'] ?? 1);
    $search = trim($_GET['search'] ?? '');
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Get total count
    $countQuery = "SELECT COUNT(*) as total FROM installation_fees";
    $params = [];
    
    if (!empty($search)) {
        $countQuery .= " WHERE fee_name LIKE ? OR description LIKE ?";
        $params = ['%' . $search . '%', '%' . $search . '%'];
    }

    $totalResult = getRow($pdo, $countQuery, $params);
    $total = $totalResult['total'] ?? 0;

    // Get fees
    $query = "SELECT * FROM installation_fees";
    $params = [];
    
    if (!empty($search)) {
        $query .= " WHERE fee_name LIKE ? OR description LIKE ?";
        $params = ['%' . $search . '%', '%' . $search . '%'];
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $fees = getAll($pdo, $query, $params);

    echo json_encode([
        'success' => true,
        'data' => $fees,
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
    error_log('Fetch installation fees error: ' . $e->getMessage());
}
