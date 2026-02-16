<?php
/**
 * PARAGON COMMUNICATIONS - Admin Accounts Data Fetcher
 * Fetches real admin and manager account data from the database
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is head admin
if (!isHeadAdmin()) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $role = $_GET['role'] ?? 'all';
    $page = (int)($_GET['page'] ?? 1);
    $search = $_GET['search'] ?? '';
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    
    // Build query based on role filter
    $query = "
        SELECT 
            aa.id,
            u.id as user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            u.role,
            u.status as user_status,
            aa.department,
            aa.phone as admin_phone,
            aa.approval_status,
            aa.created_at
        FROM admin_accounts aa
        JOIN users u ON aa.user_id = u.id
        WHERE 1=1
    ";
    
    $params = [];
    
    // Filter by role if specified
    if ($role !== 'all') {
        $query .= " AND u.role = ?";
        $params[] = strtolower($role);
    }
    
    // Search filter
    if (!empty($search)) {
        $query .= " AND (u.first_name LIKE ? OR u.last_name LIKE ? OR u.email LIKE ?)";
        $searchTerm = '%' . $search . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    // Get total count for pagination
    $countQuery = str_replace(
        ['SELECT 
            aa.id,
            u.id as user_id,
            u.first_name,
            u.last_name,
            u.email,
            u.phone,
            u.role,
            u.status as user_status,
            aa.department,
            aa.phone as admin_phone,
            aa.approval_status,
            aa.created_at', 'FROM'],
        ['SELECT COUNT(*) as total FROM', 'FROM'],
        $query
    );
    
    $countResult = getRow($pdo, $countQuery, $params);
    $totalRows = $countResult['total'] ?? 0;
    
    // Add ordering and pagination
    $query .= " ORDER BY aa.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $accounts = getAll($pdo, $query, $params);
    
    // Format response
    $response = [
        'success' => true,
        'data' => [],
        'pagination' => [
            'page' => $page,
            'perPage' => $perPage,
            'total' => $totalRows,
            'totalPages' => ceil($totalRows / $perPage)
        ]
    ];
    
    foreach ($accounts as $account) {
        $response['data'][] = [
            'id' => $account['id'],
            'user_id' => $account['user_id'],
            'fullName' => trim($account['first_name'] . ' ' . $account['last_name']),
            'firstName' => $account['first_name'],
            'lastName' => $account['last_name'],
            'email' => $account['email'],
            'contact' => $account['admin_phone'] ?? $account['phone'] ?? '',
            'role' => ucfirst($account['role']),
            'roleValue' => strtoupper($account['role']),
            'department' => $account['department'] ?? '',
            'approvalStatus' => $account['approval_status'],
            'userStatus' => $account['user_status'],
            'createdAt' => $account['created_at']
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data', 'message' => $e->getMessage()]);
    error_log("Admin accounts fetch error: " . $e->getMessage());
}
?>
