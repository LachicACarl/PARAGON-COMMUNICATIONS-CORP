<?php
require_once '../../config/database.php';
require_once '../../config/helpers.php';
require_once '../../config/config.php';

session_start();

if (!isLoggedIn() || getCurrentRole() !== 'head_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$currentPath = $_SERVER['PHP_SELF'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Head Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
    <style>
        .material-icons { font-family: 'Material Icons'; font-size: 24px; line-height: 1; -webkit-font-smoothing: antialiased; }
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-width: 500px; width: 90%; }
        .toast { position: fixed; top: 20px; right: 20px; padding: 16px 24px; border-radius: 8px; color: white; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2000; animation: slideIn 0.3s; }
        @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast.success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .btn-primary { @apply bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2; }
        .btn-secondary { @apply bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-all; }
        .table-row { @apply border-b hover:bg-blue-50 transition-colors; }
        .table-cell { @apply px-4 py-3 text-sm; }
        
        /* Sidebar visual enhancements */
        aside.sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #162e4f 100%);
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.3);
        }
        .status-badge { @apply inline-block px-3 py-1 rounded-full text-xs font-medium; }
        .status-active { @apply bg-green-100 text-green-800; }
        .status-pending { @apply bg-yellow-100 text-yellow-800; }
        .status-rejected { @apply bg-red-100 text-red-800; }
    </style>
</head>
<body class="bg-gray-100">

<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleSidebar()">
    <span class="material-icons">menu</span>
</button>

<div class="flex min-h-screen">
    <?php require_once __DIR__ . '/../../includes/head-admin-sidebar.php'; ?>

    <div class="flex-1">
        <main class="h-screen overflow-auto">
            <div class="bg-white border-b sticky top-0 z-10">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">User Management</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage Admin and Manager accounts</p>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <input type="text" id="searchInput" placeholder="Search users by email or name..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Email</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Role</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Created</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="userTable" class="divide-y">
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="px-4 py-4 flex justify-between items-center border-t bg-gray-50">
                        <span class="text-sm text-gray-600" id="totalCount">Total: 0</span>
                        <div class="flex gap-2">
                            <button class="px-3 py-1 border rounded hover:bg-gray-100 text-sm" onclick="loadData(1)">« First</button>
                            <button class="px-3 py-1 border rounded hover:bg-gray-100 text-sm" id="prevBtn" onclick="loadData(currentPage - 1)">Prev</button>
                            <span class="text-sm text-gray-600" id="pageInfo">Page 1</span>
                            <button class="px-3 py-1 border rounded hover:bg-gray-100 text-sm" id="nextBtn" onclick="loadData(currentPage + 1)">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal" id="approveModal">
    <div class="modal-content">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="material-icons text-green-600">check_circle</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Approve User?</h3>
                    <p class="text-sm text-gray-600" id="approveUserName">User will be activated</p>
                </div>
            </div>
            <div class="flex gap-2 pt-4">
                <button onclick="confirmApprove()" class="flex-1 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 flex items-center gap-2 justify-center"><span class="material-icons">check</span> Approve</button>
                <button onclick="closeApproveModal()" class="flex-1 btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal" id="rejectModal">
    <div class="modal-content">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="material-icons text-red-600">cancel</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Reject User?</h3>
                    <p class="text-sm text-gray-600" id="rejectUserName">User account will be rejected</p>
                </div>
            </div>
            <div class="flex gap-2 pt-4">
                <button onclick="confirmReject()" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 justify-center"><span class="material-icons">delete</span> Reject</button>
                <button onclick="closeRejectModal()" class="flex-1 btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let currentPage = 1;
let approveUserId = null;
let rejectUserId = null;

loadData(1);

document.getElementById('searchInput').addEventListener('input', debounce(() => { currentPage = 1; loadData(1); }, 300));

function debounce(fn, delay) {
    let timeout;
    return () => { clearTimeout(timeout); timeout = setTimeout(fn, delay); };
}

function loadData(page = 1) {
    const search = document.getElementById('searchInput').value;
    fetch(`${BASE_URL}api/fetch-users.php?page=${page}&search=${encodeURIComponent(search)}&filter=admin_manager`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) { showToast('Error loading users', 'error'); return; }
            currentPage = page;
            const tbody = document.getElementById('userTable');
            tbody.innerHTML = data.data.map(user => {
                const statusClass = user.status === 'active' ? 'status-active' : (user.status === 'pending' ? 'status-pending' : 'status-rejected');
                return `
                    <tr class="table-row">
                        <td class="table-cell font-semibold text-gray-900">${user.first_name} ${user.last_name}</td>
                        <td class="table-cell text-gray-600">${user.email}</td>
                        <td class="table-cell"><span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-medium">${user.role}</span></td>
                        <td class="table-cell"><span class="status-badge ${statusClass}">${user.status}</span></td>
                        <td class="table-cell text-gray-600">${new Date(user.created_at).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</td>
                        <td class="table-cell text-center">
                            ${user.status === 'pending' ? `
                                <button class="text-green-600 hover:text-green-800 text-sm font-medium" onclick="openApproveModal(${user.id}, '${user.first_name} ${user.last_name}')">✓ Approve</button>
                                <button class="text-red-600 hover:text-red-800 text-sm font-medium ml-2" onclick="openRejectModal(${user.id}, '${user.first_name} ${user.last_name}')">✕ Reject</button>
                            ` : `<span class="text-gray-500 text-xs">No actions</span>`}
                        </td>
                    </tr>
                `;
            }).join('');
            document.getElementById('totalCount').textContent = `Total: ${data.total}`;
            document.getElementById('pageInfo').textContent = `Page ${page}`;
            document.getElementById('prevBtn').disabled = page === 1;
            document.getElementById('nextBtn').disabled = page >= data.total_pages;
        })
        .catch(err => showToast('Failed to load users', 'error'));
}

function openApproveModal(id, name) {
    approveUserId = id;
    document.getElementById('approveUserName').textContent = `Approve ${name}?`;
    document.getElementById('approveModal').classList.add('show');
}

function closeApproveModal() { document.getElementById('approveModal').classList.remove('show'); }

function openRejectModal(id, name) {
    rejectUserId = id;
    document.getElementById('rejectUserName').textContent = `Reject ${name}?`;
    document.getElementById('rejectModal').classList.add('show');
}

function closeRejectModal() { document.getElementById('rejectModal').classList.remove('show'); }

function confirmApprove() {
    const formData = new FormData();
    formData.append('user_id', approveUserId);
    formData.append('action', 'approve');
    fetch(`${BASE_URL}api/approve-user.php`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message, 'success'); closeApproveModal(); loadData(currentPage); }
            else { showToast(data.message || 'Error', 'error'); }
        })
        .catch(err => showToast('Request failed', 'error'));
}

function confirmReject() {
    const formData = new FormData();
    formData.append('user_id', rejectUserId);
    formData.append('action', 'reject');
    fetch(`${BASE_URL}api/reject-user.php`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message, 'success'); closeRejectModal(); loadData(currentPage); }
            else { showToast(data.message || 'Error', 'error'); }
        })
        .catch(err => showToast('Request failed', 'error'));
}

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>

</body>
</html>
