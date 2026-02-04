<?php
/**
 * PARAGON COMMUNICATIONS - Helper Functions
 * Common functions for authentication, authorization, and data operations
 */

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true && isset($_SESSION['user_id']);
}

/**
 * Check if user has specific role
 * @param string|array $requiredRole Role or array of roles
 * @return bool
 */
function hasRole($requiredRole) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($requiredRole)) {
        return in_array($_SESSION['role'] ?? '', $requiredRole);
    }
    
    return ($_SESSION['role'] ?? '') === $requiredRole;
}

/**
 * Check if user has any of specified roles
 */
function hasAnyRole($roles) {
    return hasRole($roles);
}

/**
 * Get current user ID
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current user role
 */
function getCurrentRole() {
    return $_SESSION['role'] ?? 'guest';
}

/**
 * Get current user email
 */
function getCurrentUserEmail() {
    return $_SESSION['email'] ?? null;
}

/**
 * Get current user full name
 */
function getCurrentUserName() {
    return $_SESSION['username'] ?? 'Guest';
}

/**
 * Require login - redirect if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /login.php");
        exit();
    }
}

/**
 * Require specific role - redirect if no permission
 * @param string|array $role Required role(s)
 * @param string $redirectUrl Where to redirect if denied
 */
function requireRole($role, $redirectUrl = '/dashboard.php?error=access_denied') {
    requireLogin();
    
    if (!hasRole($role)) {
        header("Location: " . $redirectUrl);
        exit();
    }
}

/**
 * Check if user is Head Admin
 */
function isHeadAdmin() {
    return hasRole('head_admin');
}

/**
 * Check if user is Admin
 */
function isAdmin() {
    return hasRole(['head_admin', 'admin']);
}

/**
 * Check if user is Manager
 */
function isManager() {
    return hasRole(['head_admin', 'admin', 'manager']);
}

/**
 * Log user action in audit log
 */
function logAction($pdo, $action, $tableName = null, $recordId = null, $oldValues = null, $newValues = null) {
    try {
        $auditData = [
            'user_id' => getCurrentUserId(),
            'action' => $action,
            'table_name' => $tableName,
            'record_id' => $recordId,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
        ];
        
        insert($pdo, 'audit_logs', $auditData);
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}

/**
 * Send email (wrapper function)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $body HTML email body
 * @return bool Success
 */
function sendEmail($to, $subject, $body) {
    require_once __DIR__ . '/config.php';
    
    // Use PHP mail() for simplicity, or implement PHPMailer/SwiftMailer for SMTP
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM_ADDRESS . ">\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * Generate email verification token
 */
function generateVerificationToken() {
    return bin2hex(random_bytes(32));
}

/**
 * Generate verification link
 */
function getVerificationLink($token) {
    return APP_URL . '/verify-email.php?token=' . urlencode($token);
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Hash password securely
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Validate password strength
 */
function isStrongPassword($password) {
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        return false;
    }
    
    // Check for mix of uppercase, lowercase, numbers, special chars
    $hasUpper = preg_match('/[A-Z]/', $password);
    $hasLower = preg_match('/[a-z]/', $password);
    $hasNumber = preg_match('/[0-9]/', $password);
    
    return $hasUpper && $hasLower && $hasNumber;
}

/**
 * Get user full details
 */
function getUserDetails($pdo, $userId) {
    return getRow($pdo, "SELECT * FROM users WHERE id = ?", [$userId]);
}

/**
 * Get admin account details
 */
function getAdminAccount($pdo, $userId) {
    return getRow($pdo, "SELECT * FROM admin_accounts WHERE user_id = ?", [$userId]);
}

/**
 * Get pending approvals (for Head Admin)
 */
function getPendingApprovals($pdo) {
    return getAll($pdo, "
        SELECT aa.*, u.email, u.first_name, u.last_name, u.role
        FROM admin_accounts aa
        JOIN users u ON aa.user_id = u.id
        WHERE aa.approval_status = 'pending'
        ORDER BY aa.created_at DESC
    ");
}

/**
 * Approve admin account
 */
function approveAdminAccount($pdo, $adminAccountId, $headAdminId) {
    try {
        $adminAccount = getRow($pdo, "SELECT user_id FROM admin_accounts WHERE id = ?", [$adminAccountId]);
        
        if (!$adminAccount) {
            return false;
        }
        
        update($pdo, 'admin_accounts', 
               ['approval_status' => 'approved', 'approved_by' => $headAdminId, 'approval_date' => date('Y-m-d H:i:s')],
               ['id' => $adminAccountId]);
        
        update($pdo, 'users',
               ['status' => 'active'],
               ['id' => $adminAccount['user_id']]);
        
        logAction($pdo, 'ACCOUNT_APPROVED', 'admin_accounts', $adminAccountId);
        
        return true;
    } catch (Exception $e) {
        error_log("Approval Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Reject admin account
 */
function rejectAdminAccount($pdo, $adminAccountId, $headAdminId, $reason = '') {
    try {
        $adminAccount = getRow($pdo, "SELECT user_id FROM admin_accounts WHERE id = ?", [$adminAccountId]);
        
        if (!$adminAccount) {
            return false;
        }
        
        update($pdo, 'admin_accounts', 
               ['approval_status' => 'rejected', 'approved_by' => $headAdminId, 'approval_date' => date('Y-m-d H:i:s'), 'notes' => $reason],
               ['id' => $adminAccountId]);
        
        // Delete the user or set to suspended
        update($pdo, 'users',
               ['status' => 'suspended'],
               ['id' => $adminAccount['user_id']]);
        
        logAction($pdo, 'ACCOUNT_REJECTED', 'admin_accounts', $adminAccountId);
        
        return true;
    } catch (Exception $e) {
        error_log("Rejection Error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get client accounts with pagination
 */
function getClientAccounts($pdo, $page = 1, $perPage = 20, $filters = []) {
    $offset = ($page - 1) * $perPage;
    
    $query = "SELECT * FROM client_accounts WHERE 1=1";
    $params = [];
    
    if (!empty($filters['status'])) {
        $query .= " AND call_out_status = ?";
        $params[] = $filters['status'];
    }
    
    if (!empty($filters['category'])) {
        $query .= " AND sales_category = ?";
        $params[] = $filters['category'];
    }
    
    if (!empty($filters['search'])) {
        $query .= " AND (client_name LIKE ? OR email LIKE ?)";
        $searchTerm = '%' . $filters['search'] . '%';
        $params[] = $searchTerm;
        $params[] = $searchTerm;
    }
    
    $query .= " ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    return getAll($pdo, $query, $params);
}

/**
 * Format currency
 */
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) return '-';
    return date($format, strtotime($date));
}

/**
 * Sanitize output
 */
function sanitize($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate CSRF token
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verify CSRF token
 */
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

?>
