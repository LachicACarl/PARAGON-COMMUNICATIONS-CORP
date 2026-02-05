<?php
/**
 * PARAGON COMMUNICATIONS - Head Admin: User Approval Management
 * Manage and approve pending user accounts
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Require Head Admin role
requireLogin();
requireRole(['Head Admin']);

// Handle approval/rejection actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $userId = intval($_POST['user_id']);
    $action = $_POST['action'];
    
    if ($action === 'approve') {
        update($pdo, 'users', 
               ['status' => 'active', 'approved_at' => date('Y-m-d H:i:s'), 'approved_by' => $_SESSION['user_id']], 
               ['id' => $userId]);
        
        // Log action
        insert($pdo, 'audit_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'approve_user',
            'description' => 'Approved user ID: ' . $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $_SESSION['success'] = "User account approved successfully!";
    } 
    elseif ($action === 'reject') {
        update($pdo, 'users', 
               ['status' => 'rejected'], 
               ['id' => $userId]);
        
        // Log action
        insert($pdo, 'audit_logs', [
            'user_id' => $_SESSION['user_id'],
            'action' => 'reject_user',
            'description' => 'Rejected user ID: ' . $userId,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        $_SESSION['success'] = "User account rejected.";
    }
    elseif ($action === 'change_role') {
        $newRole = $_POST['role'];
        update($pdo, 'users', 
               ['role' => $newRole], 
               ['id' => $userId]);
        
        $_SESSION['success'] = "User role updated to $newRole!";
    }
    
    header("Location: approve-users.php");
    exit();
}

// Get all pending users
$pendingUsers = getAll($pdo, "SELECT * FROM users WHERE status = 'pending' ORDER BY created_at DESC");

// Get all active users
$activeUsers = getAll($pdo, "SELECT * FROM users WHERE status = 'active' ORDER BY created_at DESC");

$successMessage = $_SESSION['success'] ?? '';
unset($_SESSION['success']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Users - PARAGON</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f6fa;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 24px;
        }
        
        .header a {
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 5px;
        }
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .success-message {
            background: #4caf50;
            color: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .section {
            background: white;
            border-radius: 10px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        
        .user-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .user-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            border-bottom: 2px solid #dee2e6;
        }
        
        .user-table td {
            padding: 15px 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        .user-table tr:hover {
            background: #f8f9fa;
        }
        
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
            font-size: 14px;
            margin-right: 5px;
            transition: all 0.2s;
        }
        
        .btn-approve {
            background: #4caf50;
            color: white;
        }
        
        .btn-approve:hover {
            background: #45a049;
        }
        
        .btn-reject {
            background: #f44336;
            color: white;
        }
        
        .btn-reject:hover {
            background: #da190b;
        }
        
        .badge {
            padding: 5px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #333;
        }
        
        .badge-active {
            background: #4caf50;
            color: white;
        }
        
        .badge-head-admin {
            background: #e91e63;
            color: white;
        }
        
        .badge-admin {
            background: #9c27b0;
            color: white;
        }
        
        .badge-manager {
            background: #2196f3;
            color: white;
        }
        
        .badge-user {
            background: #607d8b;
            color: white;
        }
        
        .role-selector {
            padding: 6px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            margin-right: 5px;
        }
        
        .no-users {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>👤 User Approval Management</h1>
        <a href="dashboard.php">← Back to Dashboard</a>
    </div>
    
    <div class="container">
        <?php if ($successMessage): ?>
        <div class="success-message">
            ✓ <?php echo htmlspecialchars($successMessage); ?>
        </div>
        <?php endif; ?>
        
        <!-- Pending Users Section -->
        <div class="section">
            <h2>⏳ Pending Approvals (<?php echo count($pendingUsers); ?>)</h2>
            
            <?php if (empty($pendingUsers)): ?>
            <div class="no-users">No pending user approvals at this time.</div>
            <?php else: ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Registered</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingUsers as $user): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if ($user['profile_picture']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     class="profile-pic" alt="Profile">
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                        <td><span class="badge badge-pending">PENDING</span></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-approve">
                                    ✓ Approve
                                </button>
                                <button type="submit" name="action" value="reject" class="btn btn-reject">
                                    ✗ Reject
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        
        <!-- Active Users Section -->
        <div class="section">
            <h2>✓ Active Users (<?php echo count($activeUsers); ?>)</h2>
            
            <?php if (empty($activeUsers)): ?>
            <div class="no-users">No active users yet.</div>
            <?php else: ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Current Role</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($activeUsers as $user): ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <?php if ($user['profile_picture']): ?>
                                <img src="<?php echo htmlspecialchars($user['profile_picture']); ?>" 
                                     class="profile-pic" alt="Profile">
                                <?php endif; ?>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            </div>
                        </td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <span class="badge badge-<?php echo strtolower(str_replace(' ', '-', $user['role'])); ?>">
                                <?php echo strtoupper($user['role']); ?>
                            </span>
                        </td>
                        <td><?php echo $user['last_login'] ? date('M d, Y', strtotime($user['last_login'])) : 'Never'; ?></td>
                        <td>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <select name="role" class="role-selector">
                                    <option value="User" <?php echo $user['role'] === 'User' ? 'selected' : ''; ?>>User</option>
                                    <option value="Manager" <?php echo $user['role'] === 'Manager' ? 'selected' : ''; ?>>Manager</option>
                                    <option value="Admin" <?php echo $user['role'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                                    <option value="Head Admin" <?php echo $user['role'] === 'Head Admin' ? 'selected' : ''; ?>>Head Admin</option>
                                </select>
                                <button type="submit" name="action" value="change_role" class="btn btn-approve">
                                    Update Role
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
