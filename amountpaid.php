<?php
/**
 * PARAGON COMMUNICATIONS - Amount Paid Management
 */

session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/helpers.php';

// Check if user is logged in
requireLogin();

// Get user details
$userId = getCurrentUserId();
$userDetails = getUserDetails($pdo, $userId);
$firstInitial = strtoupper(substr($userDetails['first_name'] ?? '', 0, 1));
$lastInitial = strtoupper(substr($userDetails['last_name'] ?? '', 0, 1));
$initials = trim($firstInitial . $lastInitial);
if ($initials === '') {
    $initials = 'U';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Amount Paid</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    /* Material Icons Font Family */
    .material-icons {
      font-family: 'Material Icons';
      font-weight: normal;
      font-style: normal;
      font-size: 24px;
      display: inline-block;
      line-height: 1;
      text-transform: none;
      letter-spacing: normal;
      word-wrap: normal;
      white-space: nowrap;
      direction: ltr;
      -webkit-font-smoothing: antialiased;
      text-rendering: optimizeLegibility;
      -moz-osx-font-smoothing: grayscale;
      font-feature-settings: 'liga';
    }
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f6f9;
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ===== Main Content ===== */
    .content {
      margin-left: 200px;
      padding: 20px;
      width: 100%;
    }

    h1 {
      margin-bottom: 20px;
      color: #333;
    }

    .actions {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      gap: 10px;
    }

    .actions button {
      background: #1976d2;
      color: #fff;
      border: none;
      padding: 10px 14px;
      border-radius: 5px;
      cursor: pointer;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-box {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-box input {
      padding: 8px 12px;
      border-radius: 5px;
      border: 1px solid #ccc;
      width: 250px;
    }

    /* ===== Table ===== */
    table {
      width: 100%;
      border-collapse: collapse;
      background: #fff;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      border-radius: 8px;
      overflow: hidden;
    }

    th, td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    thead {
      background: #1976d2;
      color: #fff;
    }

    tr:hover {
      background: #f1f1f1;
    }

    .entries-info {
      margin-top: 15px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 14px;
      color: #666;
      flex-wrap: wrap;
      gap: 10px;
    }

    .pagination {
      display: flex;
      gap: 8px;
      flex-wrap: wrap;
    }

    .pagination button {
      padding: 8px 14px;
      border: 1px solid #ddd;
      background: #fff;
      cursor: pointer;
      border-radius: 4px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    .pagination button:hover:not(:disabled) {
      background: #1976d2;
      color: #fff;
      border-color: #1976d2;
    }

    .pagination button.active {
      background: #1976d2;
      color: #fff;
      border-color: #1976d2;
    }

    .pagination button:disabled {
      opacity: 0.5;
      cursor: not-allowed;
    }

    .btn-action {
      border: none;
      background: none;
      cursor: pointer;
      font-size: 18px;
      padding: 5px;
    }

    .btn-action.edit {
      color: #1976d2;
    }

    .btn-action.delete {
      color: #e53935;
    }

    /* ===== Modal ===== */
    .modal {
      display: none;
      position: fixed;
      z-index: 999;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }

    .modal-content {
      background: #fff;
      padding: 25px;
      border-radius: 8px;
      width: 450px;
      max-width: 90%;
      position: relative;
    }

    .modal-content h3 {
      margin-top: 0;
      color: #333;
    }

    .modal-content label {
      display: block;
      margin: 12px 0 5px;
      font-weight: bold;
      color: #555;
    }

    .modal-content input {
      width: 100%;
      padding: 8px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      box-sizing: border-box;
    }

    .modal-content button {
      margin-top: 15px;
      padding: 10px 14px;
      background: #1976d2;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      width: 100%;
      font-weight: bold;
    }

    .close-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      background: none;
      border: none;
      font-size: 24px;
      color: #888;
      cursor: pointer;
    }
  </style>
</head>
<body>

<button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
  <span class="material-icons">menu</span>
</button>
<div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
<div class="wrapper">

  <!-- ===== Sidebar ===== -->
  <?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  $userRole = getCurrentRole();
  
  // Define all navigation items with role restrictions
  $allNavItems = [
    'dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['head_admin', 'admin', 'manager']],
    'user.php' => ['icon' => 'people', 'label' => 'User', 'roles' => ['head_admin']],
    'address.php' => ['icon' => 'location_on', 'label' => 'Address', 'roles' => ['head_admin']],
    'amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid', 'roles' => ['head_admin']],
    'admin/installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee', 'roles' => ['head_admin']],
    'admin/call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status', 'roles' => ['head_admin']],
    'admin/pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks', 'roles' => ['head_admin']],
    'admin/status_input.php' => ['icon' => 'input', 'label' => 'Status Input', 'roles' => ['head_admin']],
    'admin/sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category', 'roles' => ['head_admin']],
    'admin/main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks', 'roles' => ['head_admin']],
    'admin/monitoring.php' => ['icon' => 'monitor', 'label' => 'Backend Monitoring', 'roles' => ['admin']],
    'admin/backend-productivity.php' => ['icon' => 'assessment', 'label' => 'Backend Productivity', 'roles' => ['admin']],
    'admin/dormants.php' => ['icon' => 'person_off', 'label' => 'Dormants', 'roles' => ['admin']],
    'admin/recallouts.php' => ['icon' => 'phone_callback', 'label' => 'Recallouts', 'roles' => ['admin']],
    'admin/pull-out.php' => ['icon' => 'content_paste', 'label' => 'Pull Out Report', 'roles' => ['admin', 'manager']],
    'admin/s25-report.php' => ['icon' => 'summarize', 'label' => 'S25 Report', 'roles' => ['admin', 'manager']],
    'admin/daily-count.php' => ['icon' => 'today', 'label' => 'Daily Count', 'roles' => ['admin', 'manager']],
    'admin/visit-remarks.php' => ['icon' => 'comment', 'label' => 'Visit Remarks', 'roles' => ['admin', 'manager']],
    'profile.php' => ['icon' => 'person', 'label' => 'Profile', 'roles' => ['head_admin', 'admin', 'manager']],
    'logout.php' => ['icon' => 'logout', 'label' => 'Logout', 'roles' => ['head_admin', 'admin', 'manager']],
  ];
  
  // Filter navigation items based on user role
  $navItems = array_filter($allNavItems, function($item) use ($userRole) {
    return in_array($userRole, $item['roles']);
  });
  ?>
  <style>
    .sidebar { width: 200px; background: linear-gradient(135deg, #1565c0, #1976d2); color: #fff; padding: 20px; position: fixed; height: 100vh; overflow-y: auto; z-index: 1000; }
    .sidebar .logo { margin-bottom: 30px; }
    .sidebar .logo img { max-width: 100%; height: auto; }
    .sidebar nav { display: flex; flex-direction: column; gap: 5px; }
    .sidebar nav a { display: flex; align-items: center; gap: 15px; padding: 12px 15px; color: rgba(255, 255, 255, 0.8); text-decoration: none; border-radius: 5px; transition: all 0.3s ease; font-size: 14px; }
    .sidebar nav a:hover { background: rgba(255, 255, 255, 0.15); color: #fff; padding-left: 18px; }
    .sidebar nav a.active { background: rgba(255, 255, 255, 0.25); color: #fff; font-weight: bold; border-right: 4px solid #fff; padding-right: 11px; }
    .sidebar nav a .material-icons { font-size: 20px; flex-shrink: 0; }
  </style>
  <aside class="sidebar">
    <div class="logo"><img src="assets/image.png" alt="Paragon Logo"></div>
    <nav class="nav">
      <?php foreach($navItems as $file => $item): ?>
        <a href="<?php echo $file; ?>" class="<?php echo $currentPage === $file ? 'active' : ''; ?>">
          <span class="material-icons"><?php echo $item['icon']; ?></span>
          <?php echo $item['label']; ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

  <!-- ===== Main Content ===== -->
  <main class="content">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
      <div>
        <h1 style="margin: 0 0 5px 0; font-size: 26px; color: #333;">Amount Paid</h1>
        <p style="margin: 0; color: #666; font-size: 14px;">Overview of amount to be paid per plan.</p>
      </div>
      <div style="display: flex; align-items: center; gap: 12px; background: #fff; padding: 10px 16px; border-radius: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">
        <div style="width: 40px; height: 40px; background: #1976d2; color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold;">
          <?php echo htmlspecialchars($initials); ?>
        </div>
        <div>
          <strong><?php echo htmlspecialchars($userDetails['first_name'] . ' ' . $userDetails['last_name']); ?></strong><br>
          <small><?php echo ucfirst(str_replace('_', ' ', getCurrentRole())); ?></small>
        </div>
      </div>
    </div>

    <!-- ===== Actions & Search ===== -->
    <div style="background: #1976d2; color: #fff; padding: 15px 20px; border-radius: 8px 8px 0 0; display: flex; justify-content: space-between; align-items: center;">
      <h3 style="margin: 0; font-size: 16px; font-weight: bold;">MANAGE AMOUNT PAID</h3>
      <button style="background: #fff; color: #1976d2; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 5px;" onclick="openModal()">
        <span class="material-icons" style="font-size: 18px;">add</span> ADD PLAN
      </button>
    </div>

    <div style="background: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #ddd; gap: 10px;">
      <div style="display: flex; align-items: center; gap: 10px;">
        <label>Show</label>
        <select style="padding: 6px 10px; border: 1px solid #ccc; border-radius: 4px;">
          <option>5</option>
          <option>10</option>
          <option>25</option>
          <option>50</option>
        </select>
        <label>Entries</label>
      </div>
      <div class="search-box">
        <input type="text" id="searchInput" placeholder="Search:" onkeyup="performSearch()">
      </div>
    </div>

    <!-- ===== Amount Paid Table ===== -->
    <table id="paymentTable">
      <thead>
        <tr>
          <th>AMOUNT PAID</th>
          <th style="width: 100px;">ACTION</th>
        </tr>
      </thead>
      <tbody id="tableBody">
        <tr><td colspan="2" style="text-align: center;">Loading data...</td></tr>
      </tbody>
    </table>

    <div class="entries-info">
      <span id="entriesInfo">Showing 0 of 0 entries</span>
      <div class="pagination" id="pagination"></div>
    </div>

  </main>
</div>

<!-- ===== Modal ===== -->
<div class="modal" id="addPaymentModal">
  <div class="modal-content">
    <button class="close-btn" onclick="closeModal()">✕</button>
    <h3>Add Payment Plan</h3>
    <label>Client Name</label>
    <input type="text" id="clientName" placeholder="Enter client name">
    <label>Amount to Pay</label>
    <input type="number" id="amountPaid" placeholder="Enter amount" step="0.01" min="0">
    <label>Installation Fee</label>
    <input type="number" id="installationFee" placeholder="Enter installation fee" step="0.01" min="0">
    <button onclick="submitPayment()">Submit Payment</button>
  </div>
</div>

<script>
  let currentPage = 1;
  let totalPages = 1;
  let allData = [];
  let filteredData = [];

  // Initialize page on load
  document.addEventListener('DOMContentLoaded', function() {
    loadData(1);
  });

  // Load payment data from API
  function loadData(page = 1) {
    const searchQuery = document.getElementById('searchInput').value;
    const url = new URL('api/fetch-amount-paid.php', window.location.origin);
    url.searchParams.append('action', 'get');
    url.searchParams.append('page', page);
    if(searchQuery) url.searchParams.append('search', searchQuery);

    fetch(url)
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          allData = data.data;
          totalPages = data.total_pages;
          currentPage = page;
          renderTable(data.data);
          setupPagination(data.total_pages, page);
          updateEntriesInfo(data.total_count, data.data.length);
        }
      })
      .catch(error => console.error('Error loading data:', error));
  }

  // Render table rows from data
  function renderTable(data) {
    const tbody = document.getElementById('tableBody');
    tbody.innerHTML = '';

    if(data.length === 0) {
      tbody.innerHTML = '<tr><td colspan="2" style="text-align: center; padding: 20px;">No records found</td></tr>';
      return;
    }

    data.forEach(item => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>
          <div style="margin-bottom: 5px;"><strong>${item.client_name}</strong></div>
          <div style="font-size: 12px; color: #666;">
            Amount: ₱${parseFloat(item.amount_paid).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})} | 
            Fee: ₱${parseFloat(item.installation_fee).toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2})} | 
            Status: <span style="background: ${item.call_out_status === 'Active' ? '#4caf50' : '#f44336'}; color: #fff; padding: 2px 8px; border-radius: 3px; font-size: 11px;">${item.call_out_status}</span>
          </div>
        </td>
        <td>
          <button title="Edit" onclick="editPayment(${item.id})" style="background: none; border: none; cursor: pointer; color: #1976d2; margin-right: 10px;">
            <span class="material-icons" style="font-size: 18px;">edit</span>
          </button>
          <button title="Delete" onclick="deletePayment(${item.id})" style="background: none; border: none; cursor: pointer; color: #f44336;">
            <span class="material-icons" style="font-size: 18px;">delete</span>
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  // Setup pagination buttons
  function setupPagination(totalPages, currentPage) {
    const pagination = document.getElementById('pagination');
    pagination.innerHTML = '';

    if(totalPages <= 1) return;

    // Previous button
    if(currentPage > 1) {
      const prevBtn = createPageButton('‹', currentPage - 1);
      pagination.appendChild(prevBtn);
    }

    // Page buttons
    for(let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
      const btn = createPageButton(i, i);
      if(i === currentPage) btn.classList.add('active');
      pagination.appendChild(btn);
    }

    // Next button
    if(currentPage < totalPages) {
      const nextBtn = createPageButton('›', currentPage + 1);
      pagination.appendChild(nextBtn);
    }
  }

  function createPageButton(text, page) {
    const btn = document.createElement('button');
    btn.textContent = text;
    btn.onclick = () => loadData(page);
    btn.style.margin = '0 2px';
    btn.style.padding = '6px 10px';
    btn.style.border = '1px solid #ddd';
    btn.style.borderRadius = '4px';
    btn.style.cursor = 'pointer';
    btn.style.background = '#fff';
    if(page === currentPage) {
      btn.style.background = '#1976d2';
      btn.style.color = '#fff';
      btn.style.borderColor = '#1976d2';
    }
    return btn;
  }

  // Perform search
  function performSearch() {
    loadData(1);
  }

  // Update entries info
  function updateEntriesInfo(total, showing) {
    const start = (currentPage - 1) * 10 + 1;
    const end = Math.min(currentPage * 10, total);
    document.getElementById('entriesInfo').textContent = `Showing ${start} to ${end} of ${total} entries`;
  }

  // Modal functions
  function openModal() {
    document.getElementById('addPaymentModal').style.display = 'flex';
  }

  function closeModal() {
    document.getElementById('addPaymentModal').style.display = 'none';
    document.getElementById('clientName').value = '';
    document.getElementById('amountPaid').value = '';
    document.getElementById('installationFee').value = '';
  }

  // Submit payment
  function submitPayment() {
    const clientName = document.getElementById('clientName').value;
    const amountPaid = document.getElementById('amountPaid').value;
    const installationFee = document.getElementById('installationFee').value;

    if(!clientName || !amountPaid) {
      alert('Please fill in all required fields');
      return;
    }

    fetch('api/add-amount-paid.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({
        client_name: clientName,
        amount_paid: parseFloat(amountPaid),
        installation_fee: parseFloat(installationFee) || 0
      })
    })
    .then(response => response.json())
    .then(data => {
      if(data.success) {
        alert('Payment added successfully');
        closeModal();
        loadData(1);
      } else {
        alert('Error: ' + data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
      alert('Error adding payment');
    });
  }

  // Edit payment (placeholder)
  function editPayment(id) {
    alert('Edit functionality coming soon for payment ID: ' + id);
  }

  // Delete payment (placeholder)
  function deletePayment(id) {
    if(confirm('Are you sure you want to delete this payment?')) {
      fetch('api/delete-amount-paid.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id: id})
      })
      .then(response => response.json())
      .then(data => {
        if(data.success) {
          loadData(currentPage);
        } else {
          alert('Error: ' + data.message);
        }
      })
      .catch(error => console.error('Error:', error));
    }
  }
</script>

<script>
  function toggleMobileSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const overlay = document.querySelector('.mobile-overlay');
      sidebar.classList.toggle('active');
      overlay.classList.toggle('active');
  }
  
  function closeMobileSidebar() {
      const sidebar = document.querySelector('.sidebar');
      const overlay = document.querySelector('.mobile-overlay');
      sidebar.classList.remove('active');
      overlay.classList.remove('active');
  }
  
  document.querySelectorAll('.sidebar .nav a').forEach(link => {
      link.addEventListener('click', () => {
          if (window.innerWidth <= 768) {
              closeMobileSidebar();
          }
      });
  });
  
  window.addEventListener('resize', () => {
      if (window.innerWidth > 768) {
          closeMobileSidebar();
      }
  });
</script>

</body>
</html>
