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
    <title>Amount Paid Management - Head Admin</title>
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
        .stat-card { @apply bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200; }
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
                        <h1 class="text-2xl font-bold text-gray-900">Amount Paid Management</h1>
                        <p class="text-sm text-gray-600 mt-1">Manage client payment records</p>
                    </div>
                    <button class="btn-primary" onclick="openModal()">
                        <span class="material-icons">add</span> Add Record
                    </button>
                </div>
            </div>

            <div class="p-6">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="stat-card">
                        <div class="text-sm font-medium text-gray-700">Total Clients</div>
                        <div class="text-3xl font-bold text-blue-600 mt-2" id="totalClients">0</div>
                    </div>
                    <div class="stat-card bg-gradient-to-br from-green-50 to-green-100 border-green-200">
                        <div class="text-sm font-medium text-gray-700">Total Amount Paid</div>
                        <div class="text-3xl font-bold text-green-600 mt-2" id="totalAmount">₱0.00</div>
                    </div>
                    <div class="stat-card bg-gradient-to-br from-orange-50 to-orange-100 border-orange-200">
                        <div class="text-sm font-medium text-gray-700">Unpaid Clients</div>
                        <div class="text-3xl font-bold text-orange-600 mt-2" id="unpaidCount">0</div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow">
                    <div class="p-4 border-b">
                        <input type="text" id="searchInput" placeholder="Search by client name or email..." class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Client Name</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Email</th>
                                    <th class="px-4 py-3 text-right text-xs font-bold text-gray-700 uppercase">Amount Paid</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Status</th>
                                    <th class="px-4 py-3 text-left text-xs font-bold text-gray-700 uppercase">Updated</th>
                                    <th class="px-4 py-3 text-center text-xs font-bold text-gray-700 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="amountTable" class="divide-y">
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
<div class="modal" id="amountModal">
    <div class="modal-content">
        <div class="p-6 border-b flex justify-between items-center">
            <h2 class="text-xl font-bold text-gray-900" id="modalTitle">Add Amount Paid Record</h2>
            <button class="text-gray-400 hover:text-gray-600 text-2xl" onclick="closeModal()">✕</button>
        </div>
        <form id="amountForm" class="p-6 space-y-4">
            <input type="hidden" id="recordId">
            
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Client Name *</label>
                <input type="text" id="clientName" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="Client name">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                <input type="email" id="clientEmail" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="client@example.com">
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Amount Paid *</label>
                <input type="number" id="amountPaid" step="0.01" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required placeholder="0.00">
            </div>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="flex-1 btn-primary justify-center"><span class="material-icons">save</span> Save Record</button>
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
                <div><h3 class="text-lg font-bold text-gray-900">Delete Record?</h3><p class="text-sm text-gray-600">This action cannot be undone.</p></div>
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
loadStats();

document.getElementById('searchInput').addEventListener('input', debounce(() => { currentPage = 1; loadData(1); }, 300));

function debounce(fn, delay) {
    let timeout;
    return () => { clearTimeout(timeout); timeout = setTimeout(fn, delay); };
}

function loadStats() {
    fetch(`${BASE_URL}fetch-amount-paid.php?action=summary`)
        .then(res => res.json())
        .then(data => {
            if (data.summary) {
                document.getElementById('totalClients').textContent = data.summary.total_clients || 0;
                document.getElementById('totalAmount').textContent = '₱' + (parseFloat(data.summary.total_amount || 0).toFixed(2));
                document.getElementById('unpaidCount').textContent = data.summary.total_unpaid || 0;
            }
        })
        .catch(err => {});
}

function loadData(page = 1) {
    const search = document.getElementById('searchInput').value;
    fetch(`${BASE_URL}fetch-amount-paid.php?page=${page}&search=${encodeURIComponent(search)}`)
        .then(res => res.json())
        .then(data => {
            if (!data.success && !data.data) { showToast('Error loading records', 'error'); return; }
            currentPage = page;
            const tbody = document.getElementById('amountTable');
            const items = data.data || data || [];
            tbody.innerHTML = items.map(record => {
                const paid = parseFloat(record.amount_paid || 0);
                const status = paid > 0 ? '✓ Paid' : '✕ Unpaid';
                const statusColor = paid > 0 ? 'text-green-600' : 'text-orange-600';
                return `
                    <tr class="table-row">
                        <td class="table-cell font-semibold text-gray-900">${record.client_name || '—'}</td>
                        <td class="table-cell text-gray-600">${record.email || '—'}</td>
                        <td class="table-cell text-right font-semibold text-green-600">₱${paid.toFixed(2)}</td>
                        <td class="table-cell"><span class="${statusColor} font-medium">${status}</span></td>
                        <td class="table-cell text-gray-600">${record.updated_at ? new Date(record.updated_at).toLocaleDateString('en-US', {year: 'numeric', month: 'short', day: 'numeric'}) : '—'}</td>
                        <td class="table-cell text-center">
                            <button class="text-blue-600 hover:text-blue-800 text-sm font-medium" onclick="editRecord(${record.id}, '${(record.client_name || '').replace(/'/g, "\\'")}', '${(record.email || '').replace(/'/g, "\\'")}', ${record.amount_paid || 0})">✏️ Edit</button>
                            <button class="text-red-600 hover:text-red-800 text-sm font-medium ml-2" onclick="openDeleteModal(${record.id})">🗑️ Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
            document.getElementById('totalCount').textContent = `Total: ${items.length || 0}`;
            document.getElementById('pageInfo').textContent = `Page ${page}`;
        })
        .catch(err => showToast('Failed to load records', 'error'));
}

function openModal() {
    editingId = null;
    document.getElementById('modalTitle').textContent = 'Add Amount Paid Record';
    document.getElementById('amountForm').reset();
    document.getElementById('recordId').value = '';
    document.getElementById('amountModal').classList.add('show');
}

function editRecord(id, clientName, email, amount) {
    editingId = id;
    document.getElementById('modalTitle').textContent = 'Edit Amount Paid Record';
    document.getElementById('recordId').value = id;
    document.getElementById('clientName').value = clientName;
    document.getElementById('clientEmail').value = email;
    document.getElementById('amountPaid').value = amount;
    document.getElementById('amountModal').classList.add('show');
}

function closeModal() { document.getElementById('amountModal').classList.remove('show'); }
function openDeleteModal(id) { deleteId = id; document.getElementById('deleteModal').classList.add('show'); }
function closeDeleteModal() { document.getElementById('deleteModal').classList.remove('show'); }

document.getElementById('amountForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData();
    formData.append('client_name', document.getElementById('clientName').value);
    formData.append('email', document.getElementById('clientEmail').value);
    formData.append('amount_paid', document.getElementById('amountPaid').value);
    const url = editingId ? `${BASE_URL}api/update-amount-paid.php` : `${BASE_URL}api/add-amount-paid.php`;
    if (editingId) formData.append('id', editingId);
    fetch(url, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message || 'Record saved', 'success'); closeModal(); loadData(currentPage); loadStats(); }
            else { showToast(data.message || 'Error', 'error'); }
        })
        .catch(err => showToast('Request failed', 'error'));
});

function confirmDelete() {
    const formData = new FormData();
    formData.append('id', deleteId);
    fetch(`${BASE_URL}api/delete-amount-paid.php`, { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) { showToast(data.message || 'Record deleted', 'success'); closeDeleteModal(); loadData(currentPage); loadStats(); }
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
