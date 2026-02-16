<?php
/**
 * PARAGON COMMUNICATIONS - Dashboard Data Fetcher
 * Fetches real data from the database for the dashboard
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

try {
    $userId = getCurrentUserId();
    $userRole = getCurrentRole();
    
    // Initialize response data
    $response = [
        'summary' => [],
        'clients' => [],
        'chart' => [],
        'user' => []
    ];
    
    // Get user details
    $userDetails = getUserDetails($pdo, $userId);
    $response['user'] = [
        'name' => trim(($userDetails['first_name'] ?? '') . ' ' . ($userDetails['last_name'] ?? '')),
        'role' => ucfirst(str_replace('_', ' ', $userRole)),
        'email' => $userDetails['email'] ?? ''
    ];
    
    // Get summary statistics
    if (isHeadAdmin()) {
        // Count admins (users with role='admin')
        $adminCount = getRow($pdo, "SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $response['summary']['ADMIN'] = $adminCount['count'] ?? 0;
        
        // Count managers (users with role='manager')
        $managerCount = getRow($pdo, "SELECT COUNT(*) as count FROM users WHERE role = 'manager'");
        $response['summary']['MANAGER'] = $managerCount['count'] ?? 0;
        
        // Count distinct municipalities (cities)
        $municipalityCount = getRow($pdo, "SELECT COUNT(DISTINCT city) as count FROM client_accounts WHERE city IS NOT NULL AND city != ''");
        $response['summary']['MUNICIPALITY'] = $municipalityCount['count'] ?? 0;
        
        // Count total users (head admin can see this as status)
        $statusCount = getRow($pdo, "SELECT COUNT(*) as count FROM users WHERE status = 'active'");
        $response['summary']['STATUS'] = $statusCount['count'] ?? 0;
        
    } elseif (isAdmin()) {
        // Admin sees their managed clients
        $adminCount = getRow($pdo, "SELECT COUNT(*) as count FROM users WHERE role = 'admin' AND id = ?", [$userId]);
        $response['summary']['ADMIN'] = $adminCount['count'] ?? 0;
        
        $managerCount = getRow($pdo, "SELECT COUNT(DISTINCT managed_by) as count FROM client_accounts WHERE created_by = ?", [$userId]);
        $response['summary']['MANAGER'] = $managerCount['count'] ?? 0;
        
        $municipalityCount = getRow($pdo, "SELECT COUNT(DISTINCT city) as count FROM client_accounts WHERE (created_by = ? OR managed_by = ?) AND city IS NOT NULL", [$userId, $userId]);
        $response['summary']['MUNICIPALITY'] = $municipalityCount['count'] ?? 0;
        
        $response['summary']['STATUS'] = 0;
        
    } elseif (isManager()) {
        // Manager sees their clients' data
        $response['summary']['ADMIN'] = 0;
        $response['summary']['MANAGER'] = 0;
        
        $municipalityCount = getRow($pdo, "SELECT COUNT(DISTINCT city) as count FROM client_accounts WHERE managed_by = ? AND city IS NOT NULL", [$userId]);
        $response['summary']['MUNICIPALITY'] = $municipalityCount['count'] ?? 0;
        
        $response['summary']['STATUS'] = 0;
    }
    
    // Get client status distribution
    if (isHeadAdmin()) {
        $activeClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'active'");
        $response['clients']['active'] = $activeClients['count'] ?? 0;
        
        $dormantClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE call_out_status = 'dormant'");
        $response['clients']['dormant'] = $dormantClients['count'] ?? 0;
        
        // Get status distribution for chart (daily flow)
        $statusDistribution = getAll($pdo, "
            SELECT 
                call_out_status,
                COUNT(*) as count
            FROM client_accounts
            GROUP BY call_out_status
            ORDER BY call_out_status
        ");
        
    } elseif (isAdmin()) {
        $activeClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE (created_by = ? OR managed_by = ?) AND call_out_status = 'active'", [$userId, $userId]);
        $response['clients']['active'] = $activeClients['count'] ?? 0;
        
        $dormantClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE (created_by = ? OR managed_by = ?) AND call_out_status = 'dormant'", [$userId, $userId]);
        $response['clients']['dormant'] = $dormantClients['count'] ?? 0;
        
        $statusDistribution = getAll($pdo, "
            SELECT 
                call_out_status,
                COUNT(*) as count
            FROM client_accounts
            WHERE created_by = ? OR managed_by = ?
            GROUP BY call_out_status
            ORDER BY call_out_status
        ", [$userId, $userId]);
        
    } elseif (isManager()) {
        $activeClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE managed_by = ? AND call_out_status = 'active'", [$userId]);
        $response['clients']['active'] = $activeClients['count'] ?? 0;
        
        $dormantClients = getRow($pdo, "SELECT COUNT(*) as count FROM client_accounts WHERE managed_by = ? AND call_out_status = 'dormant'", [$userId]);
        $response['clients']['dormant'] = $dormantClients['count'] ?? 0;
        
        $statusDistribution = getAll($pdo, "
            SELECT 
                call_out_status,
                COUNT(*) as count
            FROM client_accounts
            WHERE managed_by = ?
            GROUP BY call_out_status
            ORDER BY call_out_status
        ", [$userId]);
    }
    
    // Format chart data
    $response['chart']['labels'] = [];
    $response['chart']['data'] = [];
    
    if (!empty($statusDistribution)) {
        foreach ($statusDistribution as $row) {
            $status = ucfirst($row['call_out_status']);
            $response['chart']['labels'][] = $status;
            $response['chart']['data'][] = $row['count'];
        }
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data']);
    error_log("Dashboard fetch error: " . $e->getMessage());
}
?>
