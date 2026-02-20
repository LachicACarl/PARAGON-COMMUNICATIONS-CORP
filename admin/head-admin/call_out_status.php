<?php
require_once '../../config/database.php';
require_once '../../config/helpers.php';
require_once '../../config/config.php';

session_start();

// Strict head_admin only access
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (getCurrentRole() !== 'head_admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied. Head Admin only.']);
    exit;
}

$currentPath = $_SERVER['PHP_SELF'];
$role = getCurrentRole();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Call Out Status Management - Paragon Head Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
    <style>
        .material-icons { font-family: 'Material Icons'; font-size: 24px; line-height: 1; -webkit-font-smoothing: antialiased; }
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-width: 500px; width: 90%; }
        .toast { position: fixed; top: 20px; right: 20px; padding: 16px 24px; border-radius: 8px; color: white; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.15); z-index: 2000; animation: slideIn 0.3s ease-in-out; }
        @keyframes slideIn { from { transform: translateX(400px); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        .toast.success { background: linear-gradient(135deg, #10b981, #059669); }
        .toast.error { background: linear-gradient(135deg, #ef4444, #dc2626); }
        .toast.info { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .btn-primary { @apply bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-all flex items-center gap-2 }
        .btn-secondary { @apply bg-gray-300 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-400 transition-all }
        .btn-danger { @apply bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition-all text-sm }
        .btn-edit { @apply text-blue-600 hover:text-blue-800 cursor-pointer text-sm font-medium }
        
        /* Sidebar visual enhancements */
        aside.sidebar {
            background: linear-gradient(180deg, #0f172a 0%, #162e4f 100%);
            box-shadow: 2px 0 12px rgba(0, 0, 0, 0.3);
        }
        
        .table-row { @apply border-b hover:bg-blue-50 transition-colors }
        .table-cell { @apply px-4 py-3 text-sm }
        .status-badge { @apply inline-block px-3 py-1 rounded-full text-xs font-medium }
        .color-swatch { @apply w-8 h-8 rounded border-2 border-gray-300 }
    </style>
</head>
<body class="bg-gray-100">

<button class="mobile-menu-toggle md:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg" onclick="toggleSidebar()">
    <span class="material-icons">menu</span>
</button>

<div class="flex min-h-screen">
    <?php require_once __DIR__ . '/../../includes/head-admin-sidebar.php'; ?>

    <div class="flex-1 md:ml-0 ml-0 overflow-hidden">
        <!-- Main Content -->
        <main class="h-screen overflow-auto">
            <!-- Header -->
            <div class="bg-white border-b sticky top-0 z-10">
                <div class="px-6 py-4 flex justify-between items-center">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Call Out Status Management</h1>
                        <p class="text-sm text-gray-600 mt-1">Create and manage call-out status types</p>
                    </div>
                    <button class="btn-primary" onclick="openModal()">
                        <span class="material-icons">add</span> Add Status
                    </button>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <!-- Search and Filter -->
                <div class="bg-white rounded-lg shadow mb-6">
                    <div class="p-4 border-b flex gap-4 flex-wrap">
                        <input type="text" id="searchInput" placeholder="Search statuses..." class="flex-1 min-w-64 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select id="filterColor" class="px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">All Colors</option>
                            <option value="#10b981">Green</option>
                            <option value="#ef4444">Red</option>
                            <option value="#f59e0b">Amber</option>
                            <option value="#3b82f6">Blue</option>
                        </select>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Color</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Description</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Created</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="statusTable" class="divide-y">
                                <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Loading...</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
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

<!-- Add/Edit Modal -->
<div class="modal" id="statusModal">
    <div class="modal-content">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900" id="modalTitle">Add Call Out Status</h2>
            <button class="text-gray-400 hover:text-gray-600 text-2xl" onclick="closeModal()">✕</button>
        </div>
        <form id="statusForm" class="p-6 space-y-4">
            <input type="hidden" id="statusId">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Status Name *</label>
                <input type="text" id="statusName" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="e.g., Active, Pending, Completed">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Color Code</label>
                <div class="flex gap-4 items-center">
                    <input type="color" id="colorCode" value="#3b82f6" class="w-16 h-12 rounded border-2 border-gray-300 cursor-pointer">
                    <span class="text-sm text-gray-600">Click to choose a color</span>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
                <textarea id="statusDescription" rows="3" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Optional description"></textarea>
            </div>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="flex-1 btn-primary justify-center">
                    <span class="material-icons">save</span> Save Status
                </button>
                <button type="button" class="flex-1 btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="material-icons text-red-600">warning</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Delete Status?</h3>
                    <p class="text-sm text-gray-600">This action cannot be undone.</p>
                </div>
            </div>
            <div class="flex gap-2 pt-4">
                <button onclick="confirmDelete()" class="flex-1 btn-danger bg-red-600 justify-center">
                    <span class="material-icons">delete</span> Delete
                </button>
                <button onclick="closeDeleteModal()" class="flex-1 btn-secondary">Cancel</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo BASE_URL; ?>';
let currentPage = 1;
let editingId = null;
let deleteId = null;

loadData(1);

document.getElementById('searchInput').addEventListener('input', debounce(() => {
    currentPage = 1;
    loadData(1);
}, 300));

function debounce(fn, delay) {
    let timeout;
    return function() {
        clearTimeout(timeout);
        timeout = setTimeout(fn, delay);
    };
}

function loadData(page = 1) {
    const search = document.getElementById('searchInput').value;
    fetch(`${BASE_URL}api/fetch-call-out-status.php?page=${page}&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success) { showToast('Error loading statuses', 'error'); return; }
            currentPage = page;
            
            const tbody = document.getElementById('statusTable');
            tbody.innerHTML = data.data.map(status => `
                <tr class="table-row">
                    <td class="table-cell font-mono text-gray-600">${status.id}</td>
                    <td class="table-cell font-semibold text-gray-900">${status.status_name}</td>
                    <td class="table-cell">
                        <div class="color-swatch" style="background-color: ${status.color_code}; border-color: ${status.color_code};"></div>
                    </td>
                    <td class="table-cell text-gray-600">${status.description ? status.description.substring(0, 50) : '—'}</td>
                    <td class="table-cell text-gray-600">${new Date(status.created_at).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'})}</td>
                    <td class="table-cell text-center">
                        <button class="btn-edit" onclick="editStatus(${status.id}, '${status.status_name.replace(/'/g, "\\'")}', '${status.color_code}', '${(status.description || '').replace(/'/g, "\\'").replace(/\n/g, "\\n")}')">✏️ Edit</button>
                        <button class="btn-edit text-red-600 hover:text-red-800 ml-2" onclick="openDeleteModal(${status.id})">🗑️ Delete</button>
                    </td>
                </tr>
            `).join('');

            document.getElementById('totalCount').textContent = `Total: ${data.total}`;
            document.getElementById('pageInfo').textContent = `Page ${page}`;
            document.getElementById('prevBtn').disabled = page === 1;
            document.getElementById('nextBtn').disabled = page >= data.total_pages;
        })
        .catch(err => showToast('Failed to load statuses', 'error'));
}

function openModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Call Out Status';
    document.getElementById('statusForm').reset();
    document.getElementById('statusId').value = '';
    document.getElementById('statusModal').classList.add('show');
}

function editStatus(id, name, color, desc) {
    editingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Call Out Status';
    document.getElementById('statusId').value = id;
    document.getElementById('statusName').value = name;
    document.getElementById('colorCode').value = color;
    document.getElementById('statusDescription').value = desc;
    document.getElementById('statusModal').classList.add('show');
}

function closeModal() {
    document.getElementById('statusModal').classList.remove('show');
}

function openDeleteModal(id) {
    deleteId = id;
    document.getElementById('deleteModal').classList.add('show');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('show');
}

document.getElementById('statusForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('status_name', document.getElementById('statusName').value);
    formData.append('color_code', document.getElementById('colorCode').value);
    formData.append('description', document.getElementById('statusDescription').value);
    
    const url = editingId ? `${BASE_URL}api/update-call-out-status.php` : `${BASE_URL}api/add-call-out-status.php`;
    if (editingId) formData.append('id', editingId);

    fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeModal();
                loadData(currentPage);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
        })
        .catch(err => showToast('Request failed', 'error'));
});

function confirmDelete() {
    const formData = new FormData();
    formData.append('id', deleteId);

    fetch(`${BASE_URL}api/delete-call-out-status.php`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                closeDeleteModal();
                loadData(currentPage);
            } else {
                showToast(data.message || 'An error occurred', 'error');
            }
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
