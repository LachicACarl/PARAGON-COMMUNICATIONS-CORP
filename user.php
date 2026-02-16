<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>User Accounts</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Material Icons -->
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    body {
      margin: 0;
      font-family: Arial, sans-serif;
      background: #f4f6f9;
    }

    .wrapper {
      display: flex;
      min-height: 100vh;
    }

    /* ===== Sidebar ===== */
    .sidebar {
      width: 240px;
      background: #1e1e2f;
      color: #fff;
      padding: 20px 0;
      position: fixed;
      height: 100%;
    }

    .logo {
      text-align: center;
      margin-bottom: 20px;
    }

    .logo img {
      width: 120px;
    }

    .nav a {
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 12px 20px;
      color: #fff;
      text-decoration: none;
      font-size: 14px;
    }

    .nav a:hover,
    .nav a.active {
      background: #1976d2;
    }

    /* ===== Main Content ===== */
    .content {
      margin-left: 240px;
      padding: 20px;
      width: 100%;
    }

    h1 {
      margin-bottom: 15px;
      color: #333;
    }

    .card {
      background: #fff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
    }

    /* ===== Top Controls ===== */
    .top-controls {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 15px;
      gap: 10px;
      flex-wrap: wrap;
    }

    .filter-buttons button {
      padding: 8px 14px;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-weight: bold;
      background: #e0e0e0;
    }

    .filter-buttons button.active {
      background: #1976d2;
      color: #fff;
    }

    .search-box {
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-box input {
      padding: 8px 10px;
      border-radius: 5px;
      border: 1px solid #ccc;
      width: 220px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
    }

    table thead {
      background: #1976d2;
      color: #fff;
    }

    table th, table td {
      padding: 12px;
      border-bottom: 1px solid #ddd;
      text-align: left;
    }

    /* ===== Validation Dropdown ===== */
    select.validation {
      padding: 6px 10px;
      border-radius: 4px;
      border: 1px solid #ccc;
      cursor: pointer;
    }

    .approved { background: #e8f5e9; color: #2e7d32; }
    .declined { background: #ffebee; color: #c62828; }
    .pending { background: #fffde7; color: #f9a825; }

    .btn {
      border: none;
      background: none;
      cursor: pointer;
    }

    .btn.edit { color: #1976d2; }
    .btn.delete { color: #e53935; }

    /* ===== Pagination (LEFT) ===== */
    .pagination {
      margin-top: 15px;
      display: flex;
      justify-content: flex-start;
      gap: 6px;
    }

    .pagination button {
      padding: 6px 12px;
      border: none;
      background: #e0e0e0;
      cursor: pointer;
      border-radius: 4px;
    }

    .pagination button.active {
      background: #1976d2;
      color: #fff;
    }
  </style>
</head>
<body>

<div class="wrapper">

  <!-- Sidebar -->
   <aside class="sidebar">
    <span class="material-icons menu">menu</span>
    <div class="logo"><img src="assets/image.png" alt="Paragon Logo"></div>
    <nav class="nav">
      <a href="dashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
      <a href="user.php"><span class="material-icons">people</span> User</a>
      <a href="address.php"><span class="material-icons">location_on</span> Address</a>
      <a href="amountpaid.php"><span class="material-icons">checklist</span> Amount Paid</a>
      <a href="installation_fee.html"><span class="material-icons">attach_money</span> Installation Fee</a>
      <a href="call_out_status.html"><span class="material-icons">call</span> Call Out Status</a>
      <a href="pull_out_remarks.html"><span class="material-icons">notes</span> Pull Out Remarks</a>
      <a href="status_input.html"><span class="material-icons">input</span> Status Input Channel</a>
      <a href="sales_category.html"><span class="material-icons">category</span> Sales Category</a>
      <a href="main_remarks.html"><span class="material-icons">edit</span> Main Remarks</a>
      <a href="profile.html"><span class="material-icons">person</span> Profile</a>
      <a href="logout.html"><span class="material-icons">logout</span> Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="content">
    <h1>User Accounts</h1>

    <div class="card">

      <!-- TOP CONTROLS -->
      <div class="top-controls">
        <div class="filter-buttons">
          <button class="active" onclick="filterRole('ALL', this)">ALL</button>
          <button onclick="filterRole('ADMIN', this)">ADMIN</button>
          <button onclick="filterRole('MANAGER', this)">MANAGER</button>
        </div>

        <div class="search-box">
          <span class="material-icons">search</span>
          <input type="text" id="searchInput" placeholder="Search user..." onkeyup="searchUser()">
        </div>
      </div>

      <!-- TABLE -->
      <table>
        <thead>
          <tr>
            <th>Profile</th>
            <th>Full Name</th>
            <th>Contact</th>
            <th>Validation</th>
            <th>Actions</th>
          </tr>
        </thead>

        <tbody id="userTable">
          <tr data-role="ADMIN">
            <td><span class="material-icons">person</span></td>
            <td>Juan Dela Cruz</td>
            <td>0917-123-4567</td>
            <td>
              <select class="validation pending" onchange="this.className='validation ' + this.value">
                <option value="pending" selected>Pending</option>
                <option value="approved">Approved</option>
                <option value="declined">Declined</option>
              </select>
            </td>
            <td>
              <button class="btn edit"><span class="material-icons">edit</span></button>
              <button class="btn delete"><span class="material-icons">delete</span></button>
            </td>
          </tr>

          <tr data-role="MANAGER">
            <td><span class="material-icons">person</span></td>
            <td>Maria Santos</td>
            <td>0920-555-1234</td>
            <td>
              <select class="validation approved" onchange="this.className='validation ' + this.value">
                <option value="pending">Pending</option>
                <option value="approved" selected>Approved</option>
                <option value="declined">Declined</option>
              </select>
            </td>
            <td>
              <button class="btn edit"><span class="material-icons">edit</span></button>
              <button class="btn delete"><span class="material-icons">delete</span></button>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div class="pagination" id="pagination"></div>

    </div>
  </main>
</div>

<script>
  const rowsPerPage = 5;
  const tableBody = document.getElementById("userTable");
  let rows = Array.from(tableBody.rows);
  let filteredRows = rows;
  let currentPage = 1;

  function displayRows() {
    tableBody.innerHTML = "";
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    filteredRows.slice(start, end).forEach(row => tableBody.appendChild(row));
    setupPagination();
  }

  function setupPagination() {
    const pagination = document.getElementById("pagination");
    pagination.innerHTML = "";
    const pageCount = Math.ceil(filteredRows.length / rowsPerPage);

    for (let i = 1; i <= pageCount; i++) {
      const btn = document.createElement("button");
      btn.textContent = i;
      btn.classList.toggle("active", i === currentPage);
      btn.onclick = () => {
        currentPage = i;
        displayRows();
      };
      pagination.appendChild(btn);
    }
  }

  function filterRole(role, btn) {
    document.querySelectorAll(".filter-buttons button").forEach(b => b.classList.remove("active"));
    btn.classList.add("active");

    filteredRows = role === "ALL"
      ? rows
      : rows.filter(r => r.dataset.role === role);

    currentPage = 1;
    displayRows();
  }

  function searchUser() {
    const value = document.getElementById("searchInput").value.toLowerCase();
    filteredRows = rows.filter(r => r.innerText.toLowerCase().includes(value));
    currentPage = 1;
    displayRows();
  }

  displayRows();
</script>

</body>
</html>
