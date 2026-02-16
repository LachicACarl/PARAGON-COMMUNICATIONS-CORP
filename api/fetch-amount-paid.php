<?php
header('Content-Type: application/json');
session_start();

require_once '../config/database.php';
require_once '../config/helpers.php';

// Check if user is logged in
if(!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$action = $_GET['action'] ?? 'get';
$userId = getCurrentUserId();
$currentRole = getCurrentRole();

if($action === 'summary') {
    // Return summary statistics
    try {
        // Determine query based on role
        if(isHeadAdmin()) {
            $query = "SELECT COUNT(*) as total_clients, SUM(CASE WHEN amount_paid > 0 THEN 1 ELSE 0 END) as total_paid, 
                             SUM(CASE WHEN amount_paid = 0 OR amount_paid IS NULL THEN 1 ELSE 0 END) as total_unpaid,
                             SUM(amount_paid) as total_amount
                      FROM client_accounts";
            $stmt = $pdo->prepare($query);
        } else if(isAdmin() || isManager()) {
            // Show only clients created by or managed by this user
            $query = "SELECT COUNT(*) as total_clients, SUM(CASE WHEN amount_paid > 0 THEN 1 ELSE 0 END) as total_paid, 
                             SUM(CASE WHEN amount_paid = 0 OR amount_paid IS NULL THEN 1 ELSE 0 END) as total_unpaid,
                             SUM(amount_paid) as total_amount
                      FROM client_accounts 
                      WHERE created_by = :userId OR managed_by = :userId";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':userId' => $userId]);
        } else {
            throw new Exception('Invalid role');
        }

        if(!isset($stmt)) {
            $stmt->execute();
        }

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'total_clients' => intval($result['total_clients'] ?? 0),
            'total_paid' => intval($result['total_paid'] ?? 0),
            'total_unpaid' => intval($result['total_unpaid'] ?? 0),
            'total_amount' => floatval($result['total_amount'] ?? 0)
        ]);

    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} else if($action === 'get') {
    // Return paginated payment data
    try {
        $page = max(1, intval($_GET['page'] ?? 1));
        $search = sanitize($_GET['search'] ?? '');
        $itemsPerPage = 10;
        $offset = ($page - 1) * $itemsPerPage;

        // Build base query
        if(isHeadAdmin()) {
            $baseQuery = "FROM client_accounts WHERE 1=1";
        } else if(isAdmin() || isManager()) {
            $baseQuery = "FROM client_accounts WHERE (created_by = :userId OR managed_by = :userId)";
        } else {
            throw new Exception('Invalid role');
        }

        // Add search filter
        if($search) {
            $baseQuery .= " AND client_name LIKE :search";
        }

        // Get total count
        $countQuery = "SELECT COUNT(*) as total $baseQuery";
        $countStmt = $pdo->prepare($countQuery);
        
        if(isHeadAdmin()) {
            if($search) $countStmt->bindParam(':search', $search_param = "%$search%");
        } else {
            $countStmt->bindParam(':userId', $userId);
            if($search) $countStmt->bindParam(':search', $search_param = "%$search%");
        }
        $countStmt->execute();
        $totalCount = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];

        // Get paginated data
        $query = "SELECT id, client_name, amount_paid, installation_fee, call_out_status, created_at $baseQuery 
                  ORDER BY created_at DESC 
                  LIMIT :limit OFFSET :offset";
        $stmt = $pdo->prepare($query);

        if(isHeadAdmin()) {
            if($search) $stmt->bindParam(':search', $search_param = "%$search%");
        } else {
            $stmt->bindParam(':userId', $userId);
            if($search) $stmt->bindParam(':search', $search_param = "%$search%");
        }
        $stmt->bindParam(':limit', $itemsPerPage, PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Format data
        foreach($data as &$row) {
            $row['amount_paid'] = floatval($row['amount_paid']);
            $row['installation_fee'] = floatval($row['installation_fee']);
        }

        $totalPages = ceil($totalCount / $itemsPerPage);

        echo json_encode([
            'success' => true,
            'data' => $data,
            'total_count' => $totalCount,
            'total_pages' => max(1, $totalPages),
            'current_page' => $page,
            'items_per_page' => $itemsPerPage
        ]);

    } catch(Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    }

} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
