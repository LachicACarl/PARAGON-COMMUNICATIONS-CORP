<?php
/**
 * PARAGON COMMUNICATIONS - Amount Paid Data Fetcher
 * Fetches client amount paid data from the database
 */

session_start();
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $page = (int)($_GET['page'] ?? 1);
    $search = $_GET['search'] ?? '';
    $perPage = 10;
    $offset = ($page - 1) * $perPage;
    $userId = getCurrentUserId();
    $userRole = getCurrentRole();
    
    $response = [
        'success' => true,
        'data' => [],
        'pagination' => ['page' => $page, 'perPage' => $perPage, 'total' => 0, 'totalPages' => 0],
        'summary' => ['total_clients' => 0, 'total_paid' => 0, 'total_unpaid' => 0, 'total_amount' => 0]
    ];

    // Build query based on user role
    $query = "SELECT ca.id, ca.client_name, ca.amount_paid, ca.installation_fee, ca.call_out_status, ca.created_at FROM client_accounts ca WHERE 1=1";
    $countQuery = "SELECT COUNT(*) as total FROM client_accounts ca WHERE 1=1";
    $params = [];

    // If not head admin, only show their clients
    if (!isHeadAdmin()) {
        $query .= " AND (ca.created_by = ? OR ca.managed_by = ?)";
        $countQuery .= " AND (ca.created_by = ? OR ca.managed_by = ?)";
        $params[] = $userId;
        $params[] = $userId;
    }

    // Search filter
    if (!empty($search)) {
        $query .= " AND ca.client_name LIKE ?";
        $countQuery .= " AND ca.client_name LIKE ?";
        $params[] = '%' . $search . '%';
    }

    // Get total count
    $countParams = $params;
    $countResult = getRow($pdo, $countQuery, $countParams);
    $total = $countResult['total'] ?? 0;
    
    // Add ordering and pagination
    $query .= " ORDER BY ca.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;

    $records = getAll($pdo, $query, $params);

    foreach ($records as $record) {
        $response['data'][] = [
            'id' => $record['id'],
            'clientName' => $record['client_name'] ?? '',
            'amountPaid' => (float)($record['amount_paid'] ?? 0),
            'installationFee' => (float)($record['installation_fee'] ?? 0),
            'status' => $record['call_out_status'] ?? 'pending',
            'createdAt' => $record['created_at'] ?? null
        ];
    }

    $response['pagination'] = [
        'page' => $page,
        'perPage' => $perPage,
        'total' => $total,
        'totalPages' => ceil($total / $perPage)
    ];

    // Get summary statistics
    $summaryQuery = "SELECT COUNT(*) as total_clients, SUM(amount_paid) as total_paid FROM client_accounts WHERE 1=1";
    $summaryParams = [];

    if (!isHeadAdmin()) {
        $summaryQuery .= " AND (created_by = ? OR managed_by = ?)";
        $summaryParams[] = $userId;
        $summaryParams[] = $userId;
    }

    $summary = getRow($pdo, $summaryQuery, $summaryParams);
    
    if ($summary) {
        $response['summary']['total_clients'] = $summary['total_clients'] ?? 0;
        $response['summary']['total_amount'] = (float)($summary['total_paid'] ?? 0);
        
        // Count paid vs unpaid
        $paidQuery = "SELECT COUNT(*) as count FROM client_accounts WHERE amount_paid > 0";
        $unpaidQuery = "SELECT COUNT(*) as count FROM client_accounts WHERE amount_paid = 0 OR amount_paid IS NULL";
        
        if (!isHeadAdmin()) {
            $paidQuery .= " AND (created_by = ? OR managed_by = ?)";
            $unpaidQuery .= " AND (created_by = ? OR managed_by = ?)";
        }
        
        $paidParams = !isHeadAdmin() ? [$userId, $userId] : [];
        $unpaidParams = !isHeadAdmin() ? [$userId, $userId] : [];
        
        $paid = getRow($pdo, $paidQuery, $paidParams);
        $unpaid = getRow($pdo, $unpaidQuery, $unpaidParams);
        
        $response['summary']['total_paid'] = $paid['count'] ?? 0;
        $response['summary']['total_unpaid'] = $unpaid['count'] ?? 0;
    }

    header('Content-Type: application/json');
    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data']);
    error_log("Amount paid fetch error: " . $e->getMessage());
}
?>
