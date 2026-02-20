<?php
require_once 'config/database.php';
require_once 'config/helpers.php';

session_start();

// Check if user is logged in
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

header('Content-Type: application/json');

try {
    $userId = getCurrentUserId();
    $userRole = getCurrentRole();
    
    $statuses = [
      'With Load Balance',
      '4X Uncontacted',
      '3X Uncontacted',
      'For Callout',
      'Client Moved Out',
      'Others'
    ];

    $statusCounts = [];

    foreach ($statuses as $status) {
      $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM client_accounts WHERE status_input = ?"
      );
      $stmt->execute([$status]);
      $statusCounts[] = (int) $stmt->fetchColumn();
    }

    // Active & Dormant
    $active = $pdo->query(
      "SELECT COUNT(*) FROM client_accounts WHERE call_out_status='active'"
    )->fetchColumn();

    $dormant = $pdo->query(
      "SELECT COUNT(*) FROM client_accounts WHERE call_out_status='dormant'"
    )->fetchColumn();

    // Admin counts
    $admins = $pdo->query(
      "SELECT COUNT(*) FROM admin_accounts WHERE role='admin'"
    )->fetchColumn();

    $managers = $pdo->query(
      "SELECT COUNT(*) FROM admin_accounts WHERE role='manager'"
    )->fetchColumn();

    // Status types count
    $statusTypeCount = count($statuses);

    // Municipalities count
    $municipalities = $pdo->query(
      "SELECT COUNT(DISTINCT municipality) FROM client_accounts WHERE municipality IS NOT NULL AND municipality != ''"
    )->fetchColumn();

    echo json_encode([
      'active' => (int)$active,
      'dormant' => (int)$dormant,
      'admins' => (int)$admins,
      'managers' => (int)$managers,
      'status_count' => (int)$statusTypeCount,
      'municipalities' => (int)$municipalities,
      'labels' => $statuses,
      'data' => $statusCounts
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to fetch data']);
    error_log("Dashboard fetch error: " . $e->getMessage());
}
?>
