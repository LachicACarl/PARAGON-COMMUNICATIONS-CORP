<?php
require_once '../config/authenticate.php';
requireLogin();
if (!isHeadAdmin() && !isAdmin()) {
    header('Location: ../dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Installation Fee | Paragon</title>
  <link rel="stylesheet" href="../assets/style.css">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
  <button class="mobile-menu-toggle" onclick="toggleMobileSidebar()">
    <span class="material-icons">menu</span>
  </button>
  <div class="mobile-overlay" onclick="closeMobileSidebar()"></div>
  <div class="container">
  <?php
  $currentPage = basename($_SERVER['PHP_SELF']);
  $userRole = getCurrentRole();
  
  // Define all navigation items with role restrictions
  $allNavItems = [
    '../dashboard.php' => ['icon' => 'dashboard', 'label' => 'Dashboard', 'roles' => ['head_admin', 'admin', 'manager']],
    '../user.php' => ['icon' => 'people', 'label' => 'User', 'roles' => ['head_admin']],
    '../address.php' => ['icon' => 'location_on', 'label' => 'Address', 'roles' => ['head_admin']],
    '../amountpaid.php' => ['icon' => 'checklist', 'label' => 'Amount Paid', 'roles' => ['head_admin']],
    'installation-fee.php' => ['icon' => 'attach_money', 'label' => 'Installation Fee', 'roles' => ['head_admin']],
    'call_out_status.php' => ['icon' => 'call', 'label' => 'Call Out Status', 'roles' => ['head_admin']],
    'pull_out_remarks.php' => ['icon' => 'notes', 'label' => 'Pull Out Remarks', 'roles' => ['head_admin']],
    'status_input.php' => ['icon' => 'input', 'label' => 'Status Input', 'roles' => ['head_admin']],
    'sales_category.php' => ['icon' => 'category', 'label' => 'Sales Category', 'roles' => ['head_admin']],
    'main_remarks.php' => ['icon' => 'edit', 'label' => 'Main Remarks', 'roles' => ['head_admin']],
    'monitoring.php' => ['icon' => 'monitor', 'label' => 'Backend Monitoring', 'roles' => ['admin']],
    'backend-productivity.php' => ['icon' => 'assessment', 'label' => 'Backend Productivity', 'roles' => ['admin']],
    'dormants.php' => ['icon' => 'person_off', 'label' => 'Dormants', 'roles' => ['admin']],
    'recallouts.php' => ['icon' => 'phone_callback', 'label' => 'Recallouts', 'roles' => ['admin']],
    'pull-out.php' => ['icon' => 'content_paste', 'label' => 'Pull Out Report', 'roles' => ['admin', 'manager']],
    's25-report.php' => ['icon' => 'summarize', 'label' => 'S25 Report', 'roles' => ['admin', 'manager']],
    'daily-count.php' => ['icon' => 'today', 'label' => 'Daily Count', 'roles' => ['admin', 'manager']],
    'visit-remarks.php' => ['icon' => 'comment', 'label' => 'Visit Remarks', 'roles' => ['admin', 'manager']],
    '../profile.php' => ['icon' => 'person', 'label' => 'Profile', 'roles' => ['head_admin', 'admin', 'manager']],
    '../logout.php' => ['icon' => 'logout', 'label' => 'Logout', 'roles' => ['head_admin', 'admin', 'manager']],
  ];
  
  // Filter navigation items based on user role
  $navItems = array_filter($allNavItems, function($item) use ($userRole) {
    return in_array($userRole, $item['roles']);
  });
  ?>
  <aside class="sidebar">
    <div class="logo">
      <img src="../assets/image.png" alt="Paragon Logo">
    </div>
    <nav class="nav">
      <?php foreach($navItems as $file => $item): ?>
        <a href="<?php echo $file; ?>" class="<?php echo $currentPage === $file ? 'active' : ''; ?>">
          <span class="material-icons"><?php echo $item['icon']; ?></span>
          <?php echo $item['label']; ?>
        </a>
      <?php endforeach; ?>
    </nav>
  </aside>

    <main class="main-content">
      <header class="header">
        <h1>Installation Fee Management</h1>
        <div class="user-info">
          <span class="material-icons">account_circle</span>
          <span><?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </div>
      </header>

      <section class="content">
        <div class="card">
          <div class="card-header">
            <h2>Installation Fee List</h2>
            <button class="btn-add" onclick="openAddModal()">
              <span class="material-icons">add</span>
              Add Installation Fee
            </button>
          </div>

          <div class="search-bar">
            <span class="material-icons">search</span>
            <input type="text" id="searchInput" placeholder="Search installation fees..." onkeyup="performSearch()">
          </div>

          <div class="table-container">
            <table id="dataTable">
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Fee Name</th>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="tableBody">
                <tr><td colspan="5" class="loading">Loading...</td></tr>
              </tbody>
            </table>
          </div>

          <div class="pagination">
            <button id="prevBtn" onclick="changePage(-1)">Previous</button>
            <span id="pageInfo"></span>
            <button id="nextBtn" onclick="changePage(1)">Next</button>
          </div>
        </div>
      </section>
    </main>
  </div>

  <!-- Add Modal -->
  <div id="addModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add Installation Fee</h2>
        <span class="close" onclick="closeAddModal()">&times;</span>
      </div>
      <form id="addForm" onsubmit="submitAdd(event)">
        <div class="form-group">
          <label for="fee_name">Fee Name *</label>
          <input type="text" id="fee_name" name="fee_name" required>
        </div>
        <div class="form-group">
          <label for="description">Description</label>
          <textarea id="description" name="description" rows="3"></textarea>
        </div>
        <div class="form-group">
          <label for="amount">Amount *</label>
          <input type="number" id="amount" name="amount" step="0.01" required>
        </div>
        <div class="form-actions">
          <button type="button" class="btn-secondary" onclick="closeAddModal()">Cancel</button>
          <button type="submit" class="btn-primary">Submit</button>
        </div>
      </form>
    </div>
  </div>

  <script>
    let currentPage = 1;
    let totalPages = 1;
    let allData = [];
    let filteredData = [];

    async function loadData() {
      try {
        const response = await fetch('../api/fetch-installation-fee.php');
        const result = await response.json();
        
        if (result.success) {
          allData = result.data;
          filteredData = allData;
          displayPage();
        } else {
          document.getElementById('tableBody').innerHTML = `<tr><td colspan="5" class="error">${result.message}</td></tr>`;
        }
      } catch (error) {
        document.getElementById('tableBody').innerHTML = '<tr><td colspan="5" class="error">Error loading data</td></tr>';
      }
    }

    function displayPage() {
      const rowsPerPage = 10;
      totalPages = Math.ceil(filteredData.length / rowsPerPage);
      const start = (currentPage - 1) * rowsPerPage;
      const end = start + rowsPerPage;
      const pageData = filteredData.slice(start, end);

      const tbody = document.getElementById('tableBody');
      if (pageData.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="no-data">No data found</td></tr>';
      } else {
        tbody.innerHTML = pageData.map(item => `
          <tr>
            <td>${item.id}</td>
            <td>${item.fee_name}</td>
            <td>${item.description || '-'}</td>
            <td>₱${parseFloat(item.amount).toFixed(2)}</td>
            <td>
              <button class="btn-delete" onclick="deleteItem(${item.id})">
                <span class="material-icons">delete</span>
              </button>
            </td>
          </tr>
        `).join('');
      }

      document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages || 1}`;
      document.getElementById('prevBtn').disabled = currentPage === 1;
      document.getElementById('nextBtn').disabled = currentPage === totalPages || totalPages === 0;
    }

    function changePage(direction) {
      currentPage += direction;
      displayPage();
    }

    function performSearch() {
      const searchTerm = document.getElementById('searchInput').value.toLowerCase();
      filteredData = allData.filter(item => 
        item.fee_name.toLowerCase().includes(searchTerm) ||
        (item.description && item.description.toLowerCase().includes(searchTerm))
      );
      currentPage = 1;
      displayPage();
    }

    function openAddModal() {
      document.getElementById('addModal').style.display = 'flex';
    }

    function closeAddModal() {
      document.getElementById('addModal').style.display = 'none';
      document.getElementById('addForm').reset();
    }

    async function submitAdd(event) {
      event.preventDefault();
      const formData = new FormData(event.target);

      try {
        const response = await fetch('../api/add-installation-fee.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.success) {
          alert('Installation fee added successfully!');
          closeAddModal();
          loadData();
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        alert('Error adding installation fee');
      }
    }

    async function deleteItem(id) {
      if (!confirm('Are you sure you want to delete this installation fee?')) return;

      try {
        const formData = new FormData();
        formData.append('id', id);

        const response = await fetch('../api/delete-installation-fee.php', {
          method: 'POST',
          body: formData
        });
        const result = await response.json();

        if (result.success) {
          alert('Installation fee deleted successfully!');
          loadData();
        } else {
          alert('Error: ' + result.message);
        }
      } catch (error) {
        alert('Error deleting installation fee');
      }
    }

    document.addEventListener('DOMContentLoaded', loadData);
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
