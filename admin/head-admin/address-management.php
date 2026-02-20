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
    <title>Address Management - Head Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/tailwind-compat.css">
    <style>
        .material-icons { font-family: 'Material Icons'; font-size: 24px; line-height: 1; -webkit-font-smoothing: antialiased; }
        .modal { display: none; position: fixed; z-index: 1000; inset: 0; background: rgba(0,0,0,0.5); }
        .modal.show { display: flex; align-items: center; justify-content: center; }
        .modal-content { background: white; border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,0.2); max-width: 600px; width: 90%; max-height: 90vh; overflow-y-auto; }
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
                        <h1 class="text-2xl font-bold text-gray-900">Address Management</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage regions, provinces, and municipalities</p>
                    </div>
                    <button class="btn-primary" onclick="openModal()">
                        <span class="material-icons">add</span> Add Address
                    </button>
                </div>
            </div>

            <div class="p-6">
                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <input type="text" id="searchInput" placeholder="Search addresses..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">ID</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Region</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Province</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Municipality</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Created</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="addressTable" class="divide-y">
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

<!-- Add/Edit Modal -->
<div class="modal" id="addressModal">
    <div class="modal-content">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900" id="modalTitle">Add Address</h2>
            <button class="text-gray-400 hover:text-gray-600 text-2xl" onclick="closeModal()">✕</button>
        </div>
        <form id="addressForm" class="p-6 space-y-4">
            <input type="hidden" id="addressId">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Region *</label>
                <input type="text" id="region" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="e.g., Region IV-A">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Province *</label>
                <input type="text" id="province" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="e.g., Laguna">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Municipality</label>
                <input type="text" id="municipality" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="e.g., Santa Maria">
            </div>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="flex-1 btn-primary justify-center"><span class="material-icons">save</span> Save Address</button>
                <button type="button" class="flex-1 btn-secondary" onclick="closeModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal" id="deleteModal">
    <div class="modal-content">
        <div class="p-6">
            <div class="flex items-center gap-4 mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                    <span class="material-icons text-red-600">warning</span>
                </div>
                <div><h3 class="text-lg font-bold text-gray-900">Delete Address?</h3><p class="text-sm text-gray-600">This action cannot be undone.</p></div>
            </div>
            <div class="flex gap-2 pt-4">
                <button onclick="confirmDelete()" class="flex-1 bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700 flex items-center gap-2 justify-center"><span class="material-icons">delete</span> Delete</button>
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

document.getElementById('searchInput').addEventListener('input', debounce(() => { currentPage = 1; loadData(1); }, 300));

function debounce(fn, delay) {
    let timeout;
    return () => { clearTimeout(timeout); timeout = setTimeout(fn, delay); };
}

function loadData(page = 1) {
    const search = document.getElementById('searchInput').value;
    fetch(`${BASE_URL}fetch-address.php?page=${page}&search=${encodeURIComponent(search)}&json=1`)
        .then(res => res.json())
        .then(data => {
            if (!data.success && !data.data) { showToast('Error loading addresses', 'error'); return; }
            currentPage = page;
            const tbody = document.getElementById('addressTable');
            const items = data.data || data || [];
            tbody.innerHTML = items.map(addr => `
                <tr class="table-row">
                    <td class="table-cell font-mono text-gray-600">${addr.id || '—'}</td>
                    <td class="table-cell font-semibold text-gray-900">${addr.region || '—'}</td>
                    <td class="table-cell text-gray-600">${addr.province || '—'}</td>
                    <td class="table-cell text-gray-600">${addr.municipality || '—'}</td>
                    <td class="table-cell text-gray-600">${addr.created_at ? new Date(addr.created_at).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'}) : '—'}</td>
                    <td class="table-cell text-center">
                        <button class="text-blue-600 hover:text-blue-800 text-sm font-medium" onclick="editAddress(${addr.id}, '${(addr.region || '').replace(/'/g, "\\'")}', '${(addr.province || '').replace(/'/g, "\\'")}', '${(addr.municipality || '').replace(/'/g, "\\'")}')" >✏️ Edit</button>
                        <button class="text-red-600 hover:text-red-800 text-sm font-medium ml-2" onclick="openDeleteModal(${addr.id})">🗑️ Delete</button>
                    </td>
                </tr>
            `).join('');
            document.getElementById('totalCount').textContent = `Total: ${items.length || 0}`;
            document.getElementById('pageInfo').textContent = `Page ${page}`;
        })
        .catch(err => showToast('Failed to load addresses', 'error'));
}

function openModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Address';
    document.getElementById('addressForm').reset();
    document.getElementById('addressId').value = '';
    document.getElementById('addressModal').classList.add('show');
}

function editAddress(id, region, province, municipality) {
    editingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Address';
    document.getElementById('addressId').value = id;
    document.getElementById('region').value = region;
    document.getElementById('province').value = province;
    document.getElementById('municipality').value = municipality;
    document.getElementById('addressModal').classList.add('show');
}

function closeModal() { document.getElementById('addressModal').classList.remove('show'); }
function openDeleteModal(id) { deleteId = id; document.getElementById('deleteModal').classList.add('show'); }
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); }

document.getElementById('addressForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('region', document.getElementById('region').value);
    formData.append('province', document.getElementById('province').value);
    formData.append('municipality', document.getElementById('municipality').value);
    const url = editingId ? `${BASE_URL}api/update-address.php` : `${BASE_URL}api/add-address.php`;
    if (editingId) formData.append('id', editingId);
    fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message || 'Address saved', 'success'); closeModal(); loadData(currentPage); }
            else { showToast(data.message || 'Error', 'error'); }
        })
        .catch(err => showToast('Request failed', 'error'));
});

function confirmDelete() {
    const formData = new FormData();
    formData.append('id', deleteId);
    fetch(`${BASE_URL}api/delete-address.php`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message || 'Address deleted', 'success'); closeDeleteModal(); loadData(currentPage); }
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
